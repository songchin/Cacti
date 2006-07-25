<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

/* get_data_query_field_list_from_graph - returns an array containing data query information for a given graph
   @arg $graph_id - the ID of the graph to retrieve information for
   @returns - (array) an array that looks like:
	Array
	(
	   [data_query_index] = 3
	   [data_query_field_name] => ifDescr
	   [data_query_field_value] => eth0
	   [data_query_id] => 42
	) */
function get_data_query_field_list_from_graph($graph_id) {
	/* pick the FIRST data query data source referenced by the graph. if there is more than one, things
	 * might act unexpectedly */
	$data_source = db_fetch_row("select
		data_source.id,
		from graph_item,data_source_item,data_source
		where graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and data_source.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
		and graph_item.graph_id = $graph_id
		group by data_source.id
		limit 1");

	$field_list = array();

	if (sizeof($data_source) == 1) {
		$field_list = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = ". $data_source["id"] . " and (name = 'data_query_id' or name = 'data_query_index' or name = 'data_query_field_name' or name = 'data_query_field_value')"), "name", "value");
	}

	return $field_list;
}

/* encode_data_query_index - encodes a data query index value so that it can be included
	inside of a form
   @arg $index - the index name to encode
   @returns - the encoded data query index */
function encode_data_query_index($index) {
	return md5($index);
}

/* decode_data_query_index - decodes a data query index value so that it can be read from
	a form
   @arg $encoded_index - the index that was encoded with encode_data_query_index()
   @arg $decoded_index_list - in order to cut down on unnecessary sql queries, the a list of data query indexes
	should be passed into the function for lookup
   @arg $data_query_id - the id of the data query that this index belongs to
   @arg $encoded_index - the id of the host that this index belongs to
   @returns - the decoded data query index */
function decode_data_query_index($encoded_index, &$decoded_index_list) {
	if (sizeof($decoded_index_list) > 0) {
		foreach ($decoded_index_list as $index) {
			if (encode_data_query_index($index) == $encoded_index) {
				return $index;
			}
		}
	}
}

/* get_formatted_data_query_indexes - obtains a list of indexes for a host/data query that
	is sorted by the chosen index field and formatted using the data query index title
	format
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to retrieve a list of indexes for
   @returns - an array formatted like the following:
	$arr[snmp_index] = "formatted data query index string" */
function get_formatted_data_query_indexes($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/sort.php");

	/* sanity checks */
	validate_id_die($host_id, "host_id");

	if (empty($data_query_id)) {
		return array("" => _("Unknown Index"));
	}

	$sort_cache = db_fetch_row("select sort_field,title_format from host_data_query where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));

	/* get a list of data query indexes and the field value that we are supposed
	to sort */
	$sort_field_data = array_rekey(db_fetch_assoc("select
		host_data_query_cache.index_value,
		host_data_query_cache.field_value
		from host_data_query_cache
		where host_data_query_cache.data_query_id = " . sql_sanitize($data_query_id) . "
		and host_data_query_cache.host_id = " . sql_sanitize($host_id) . "
		and host_data_query_cache.field_name='" . sql_sanitize($sort_cache["sort_field"]) . "'
		group by host_data_query_cache.index_value"), "index_value", "field_value");

	/* sort the data using the "data query index" sort algorithm */
	uasort($sort_field_data, "usort_data_query_index");

	$sorted_results = array();

	while (list($data_query_index, $sort_field_value) = each($sort_field_data)) {
		$sorted_results[$data_query_index] = substitute_data_query_variables($sort_cache["title_format"], $host_id, $data_query_id, $data_query_index);
	}

	return $sorted_results;
}

/* get_formatted_data_query_index - obtains a single index for a host/data query/data query
	index that is formatted using the data query index title format
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query which contains the data query index
   @arg $data_query_index - the index to retrieve the formatted name for
   @returns - a string containing the formatted name for the given data query index */
function get_formatted_data_query_index($host_id, $data_query_id, $data_query_index) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	/* sanity checks */
	validate_id_die($host_id, "host_id");
	validate_id_die($data_query_id, "data_query_id");

	$sort_cache = db_fetch_row("select sort_field,title_format from host_data_query where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));

	return substitute_data_query_variables($sort_cache["title_format"], $host_id, $data_query_id, $data_query_index);
}

/* get_ordered_index_type_list - builds an ordered list of data query index types that are
	valid given a list of data query indexes that will be checked against the data query
	cache
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to build the type list from
   @arg $data_query_index_array - an array containing each data query index to use when checking
	each data query type for validity. a valid data query type will contain no empty or duplicate
	values for each row in the cache that matches one of the $data_query_index_array
   @returns - an array of data query types either ordered or unordered depending on whether
	the xml file has a manual ordering preference specified */
function get_ordered_index_type_list($data_query_id, $host_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");

	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	/* retrieve information about this data query */
	$data_query = api_data_query_get($data_query_id);

	/* get a list of all input fields for this data query */
	$data_query_fields = array_rekey(api_data_query_field_list($data_query_id, DATA_QUERY_FIELD_TYPE_INPUT), "name", "name_desc");

	$valid_index_fields = array();
	if (sizeof($data_query_fields) > 0) {
		foreach ($data_query_fields as $data_query_field_name => $data_query_field_description) {
			/* create a list of all values for this index */
			$field_values = db_fetch_assoc("select field_value from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id) . " and field_name = '" . sql_sanitize($data_query_field_name) . "'");

			/* aggregate the above list so there are no duplicates */
			$field_values_nodups = array_rekey($field_values, "field_value", "field_value");

			/* fields that contain duplicate or empty values are not suitable to index off of */
			if (!((sizeof($field_values_nodups) < sizeof($field_values)) || (in_array("", $field_values_nodups) == true) || (sizeof($field_values_nodups) == 0))) {
				array_push($valid_index_fields, $data_query_field_name);
			}
		}
	}

	$return_array = array();

	/* the xml file contains an ordered list of "indexable" fields */
	if (ereg("^([a-zA-Z0-9_-]:?)+$", $data_query["index_order"])) {
		$index_order_array = explode(":", $data_query["index_order"]);

		for ($i=0; $i<count($index_order_array); $i++) {
			if (in_array($index_order_array[$i], $valid_index_fields)) {
				$return_array{$index_order_array[$i]} = $index_order_array[$i] . " (" . $data_query_fields{$index_order_array[$i]} . ")";
			}
		}
	/* the xml file does not contain a field list, ignore the order */
	}else{
		for ($i=0; $i<count($valid_index_fields); $i++) {
			$return_array{$valid_index_fields[$i]} = $valid_index_fields[$i] . " (" . $data_query_fields{$index_order_array[$i]} . ")";
		}
	}

	return $return_array;
}

/* get_best_data_query_index_type - returns the best available data query index type using the
	sort cache
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to fetch the best data query index type for
   @returns - a string containing containing best data query index type. this will be one of the
	valid input field names as specified in the data query xml file */
function get_best_data_query_index_type($host_id, $data_query_id) {
	/* sanity checks */
	validate_id_die($host_id, "host_id");
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_cell("select sort_field from host_data_query where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));
}

/* get_script_query_path - builds the complete script query executable path
   @arg $args - the variable that contains any arguments to be appended to the argument
	list (variables will be substituted in this function)
   @arg $script_path - the path on the disk to the script file
   @arg $host_id - the id of the host that this script query belongs to
   @returns - a full path to the script query script containing all arguments */
function api_data_query_script_path_format($script_path) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	/* get a complete path for out target script */
	return substitute_path_variables($script_path);
}

/* data_query_index - returns an array containing the data query ID and index value given
	a data query index type/value combination and a host ID
   @arg $index_type - the name of the index to match
   @arg $index_value - the value of the index to match
   @arg $host_id - (int) the host ID to match
   @arg $data_query_id - (int) the data query ID to match
   @returns - (array) the data query ID and index that matches the three arguments */
function get_data_query_row_index($data_query_id, $host_id, $index_type, $field_value) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return db_fetch_cell("select
		host_data_query_cache.index_value
		from host_data_query_cache
		where host_data_query_cache.field_name = '" . sql_sanitize($index_type) . "'
		and host_data_query_cache.field_value = '" . sql_sanitize($field_value) . "'
		and host_data_query_cache.host_id = " . sql_sanitize($host_id) . "
		and host_data_query_cache.data_query_id = " . sql_sanitize($data_query_id));
}

function get_data_query_row_value($data_query_id, $host_id, $index_type, $index_value) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return db_fetch_cell("select
		host_data_query_cache.field_value
		from host_data_query_cache
		where host_data_query_cache.field_name = '" . sql_sanitize($index_type) . "'
		and host_data_query_cache.index_value = '" . sql_sanitize($index_value) . "'
		and host_data_query_cache.host_id = " . sql_sanitize($host_id) . "
		and host_data_query_cache.data_query_id = " . sql_sanitize($data_query_id));
}

function get_data_query_indexes($data_query_id, $host_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return array_rekey(db_fetch_assoc("select distinct index_value from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id)), "", "index_value");
}

function api_data_query_get($data_query_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_row("select * from data_query where id = " . sql_sanitize($data_query_id));
}

function api_data_query_name_get($data_query_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_cell("select name from data_query where id = " . sql_sanitize($data_query_id));
}

function api_data_query_list() {
	return db_fetch_assoc("select * from data_query where package_id = 0 order by name");
}

function api_data_query_field_list($data_query_id, $input_type = "") {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");

	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_assoc("select * from data_query_field where data_query_id = " . sql_sanitize($data_query_id) . ((($input_type == DATA_QUERY_FIELD_TYPE_INPUT) || ($input_type == DATA_QUERY_FIELD_TYPE_OUTPUT)) ? " and type = $input_type" : "") . " order by name");
}

function api_data_query_field_get($data_query_field_id) {
	/* sanity checks */
	validate_id_die($data_query_field_id, "data_query_field_id");

	return db_fetch_row("select * from data_query_field where id = " . sql_sanitize($data_query_field_id));
}

function api_data_query_field_get_by_name($data_query_id, $field_name) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_row("select * from data_query_field where data_query_id = " . sql_sanitize($data_query_id) . " and name = '" . sql_sanitize($field_name) . "'");
}

function api_data_query_device_unassigned_list($host_id) {
	/* sanity checks */
	validate_id_die($host_id, "host_id");

	return db_fetch_assoc("select
		data_query.id,
		data_query.name,
		data_query.index_order_type,
		data_query.index_field_id,
		host_data_query.sort_field,
		host_data_query.title_format,
		host_data_query.reindex_method
		from data_query left join host_data_query
		on (data_query.id = host_data_query.data_query_id and host_data_query.host_id = " . sql_sanitize($host_id) . ")
		where host_data_query.data_query_id is null");
}

function api_data_query_device_assigned_list($host_id) {
	/* sanity checks */
	validate_id_die($host_id, "host_id");

	return db_fetch_assoc("select
		data_query.id,
		data_query.name,
		data_query.index_order_type,
		data_query.index_field_id,
		host_data_query.sort_field,
		host_data_query.title_format,
		host_data_query.reindex_method
		from data_query,host_data_query
		where data_query.id = host_data_query.data_query_id
		and host_data_query.host_id = " . sql_sanitize($host_id));
}

function api_data_query_cache_field_get($data_query_id, $host_id, $field_name) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return db_fetch_assoc("select index_value,field_value,oid from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id) . " and field_name = '" . sql_sanitize($field_name) . "'");
}

function api_data_query_cache_num_items_get($data_query_id, $host_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return db_fetch_cell("select count(*) from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));
}

function api_data_query_cache_num_rows_get($data_query_id, $host_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");
	validate_id_die($host_id, "host_id");

	return sizeof(db_fetch_assoc("select distinct index_value from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id)));
}

function api_data_query_attached_graphs_list($data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	return db_fetch_assoc("select distinct
		graph_template.id,
		graph_template.template_name
		from graph_template,graph_template_item,data_template_item,data_template,data_template_field
		where graph_template.id=graph_template_item.graph_template_id
		and graph_template_item.data_template_item_id=data_template_item.id
		and data_template_item.data_template_id=data_template.id
		and data_template.id=data_template_field.data_template_id
		and data_template.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
		and (data_template_field.name = 'data_query_id' and data_template_field.value = '" . sql_sanitize($data_query_id) . "')");
}

function api_data_query_graphed_indexes_list($graph_template_id, $host_id) {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");
	validate_id_die($host_id, "host_id");

	return db_fetch_assoc("select distinct
		data_source_field.value as data_query_index
		from graph,graph_item,data_source_item,data_source,data_source_field
		where graph.id=graph_item.graph_id
		and graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and data_source.id=data_source_field.data_source_id
		and graph.graph_template_id = " . sql_sanitize($graph_template_id) . "
		and graph.host_id = " . sql_sanitize($host_id) . "
		and data_source.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
		and data_source_field.name = 'data_query_index'");
}

function &api_data_query_input_type_list() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

	return $data_query_input_types;
}

function &api_data_query_index_sort_type_list() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

	return $data_query_index_sort_types;
}

function &api_data_query_form_list() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_form.php");

	return $fields_data_query;
}

function &api_data_query_field_form_list() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_form.php");

	return $fields_data_query_fields;
}

?>