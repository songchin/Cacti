<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

	if (empty($data_query_id)) {
		return array("" => _("Unknown Index"));
	}

	/* from the xml; cached in 'host_snmp_query' */
	$sort_cache = db_fetch_row("select sort_field,title_format from host_snmp_query where host_id='$host_id' and snmp_query_id='$data_query_id'");

	/* get a list of data query indexes and the field value that we are supposed
	to sort */
	$sort_field_data = array_rekey(db_fetch_assoc("select
		host_snmp_cache.snmp_index,
		host_snmp_cache.field_value
		from host_snmp_cache
		where host_snmp_cache.snmp_query_id=$data_query_id
		and host_snmp_cache.host_id=$host_id
		and host_snmp_cache.field_name='" . $sort_cache["sort_field"] . "'
		group by host_snmp_cache.snmp_index"), "snmp_index", "field_value");

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

	/* from the xml; cached in 'host_snmp_query' */
	$sort_cache = db_fetch_row("select sort_field,title_format from host_snmp_query where host_id='$host_id' and snmp_query_id='$data_query_id'");

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
function get_ordered_index_type_list($host_id, $data_query_id, $data_query_index_array = array()) {
	$raw_xml = get_data_query_array($data_query_id);

	$xml_outputs = array();

	/* create an SQL string that contains each index in this snmp_index_id */
	$sql_or = array_to_sql_or($data_query_index_array, "snmp_index");

	/* list each of the input fields for this snmp query */
	while (list($field_name, $field_array) = each($raw_xml["fields"])) {
		if ($field_array["direction"] == "input") {
			/* create a list of all values for this index */
			if (sizeof($data_query_index_array) == 0) {
				$field_values = db_fetch_assoc("select field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='$field_name'");
			}else{
				$field_values = db_fetch_assoc("select field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='$field_name' and $sql_or");
			}

			/* aggregate the above list so there is no duplicates */
			$aggregate_field_values = array_rekey($field_values, "field_value", "field_value");

			/* fields that contain duplicate or empty values are not suitable to index off of */
			if (!((sizeof($aggregate_field_values) < sizeof($field_values)) || (in_array("", $aggregate_field_values) == true) || (sizeof($aggregate_field_values) == 0))) {
				array_push($xml_outputs, $field_name);
			}
		}
	}

	$return_array = array();

	/* the xml file contains an ordered list of "indexable" fields */
	if (isset($raw_xml["index_order"])) {
		$index_order_array = explode(":", $raw_xml["index_order"]);

		for ($i=0; $i<count($index_order_array); $i++) {
			if (in_array($index_order_array[$i], $xml_outputs)) {
				$return_array{$index_order_array[$i]} = $index_order_array[$i] . " (" . $raw_xml["fields"]{$index_order_array[$i]}["name"] . ")";
			}
		}
	/* the xml file does not contain a field list, ignore the order */
	}else{
		for ($i=0; $i<count($xml_outputs); $i++) {
			$return_array{$xml_outputs[$i]} = $xml_outputs[$i] . " (" . $raw_xml["fields"]{$xml_outputs[$i]}["name"] . ")";
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
	return db_fetch_cell("select sort_field from host_snmp_query where host_id = '$host_id' and snmp_query_id = '$data_query_id'");
}

/* get_script_query_path - builds the complete script query executable path
   @arg $args - the variable that contains any arguments to be appended to the argument
	list (variables will be substituted in this function)
   @arg $script_path - the path on the disk to the script file
   @arg $host_id - the id of the host that this script query belongs to
   @returns - a full path to the script query script containing all arguments */
function get_script_query_path($args, $script_path, $host_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	/* get any extra arguments that need to be passed to the script */
	if (!empty($args)) {
		$extra_arguments = substitute_host_variables($args, $host_id);
	}else{
		$extra_arguments = "";
	}

	/* get a complete path for out target script */
	return substitute_path_variables($script_path) . " $extra_arguments";
}

function get_data_query_array($snmp_query_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/xml.php");

	$xml_file_path = db_fetch_cell("select xml_path from snmp_query where id=$snmp_query_id");
	$xml_file_path = str_replace("<path_cacti>", CACTI_BASE_PATH, $xml_file_path);

	if (!file_exists($xml_file_path)) {
		debug_log_insert("data_query", sprintf(_("Could not find data query XML file at '%s'"), $xml_file_path));
		return false;
	}

	debug_log_insert("data_query", sprintf(_("Found data query XML file at '%s'"), $xml_file_path));

	$data = implode("",file($xml_file_path));
	return xml2array($data);
}

/* data_query_index - returns an array containing the data query ID and index value given
	a data query index type/value combination and a host ID
   @arg $index_type - the name of the index to match
   @arg $index_value - the value of the index to match
   @arg $host_id - (int) the host ID to match
   @arg $data_query_id - (int) the data query ID to match
   @returns - (array) the data query ID and index that matches the three arguments */
function get_data_query_row_index($index_type, $field_value, $host_id, $data_query_id) {
	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		api_syslog_cacti_log("Invalid input '$host_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_cell("select
		host_snmp_cache.snmp_index
		from host_snmp_cache
		where host_snmp_cache.field_name = '" . sql_sanitize($index_type) . "'
		and host_snmp_cache.field_value = '" . sql_sanitize($field_value) . "'
		and host_snmp_cache.host_id = " . sql_sanitize($host_id) . "
		and host_snmp_cache.snmp_query_id = " . sql_sanitize($data_query_id));
}

function get_data_query_row_value($index_type, $index_value, $host_id, $data_query_id) {
	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		api_syslog_cacti_log("Invalid input '$host_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_cell("select
		host_snmp_cache.field_value
		from host_snmp_cache
		where host_snmp_cache.field_name = '" . sql_sanitize($index_type) . "'
		and host_snmp_cache.snmp_index = '" . sql_sanitize($index_value) . "'
		and host_snmp_cache.host_id = " . sql_sanitize($host_id) . "
		and host_snmp_cache.snmp_query_id = " . sql_sanitize($data_query_id));
}

function get_data_query_indexes($host_id, $data_query_id) {
	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		api_syslog_cacti_log("Invalid input '$host_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return array_rekey(db_fetch_assoc("select distinct snmp_index from host_snmp_cache where host_id = " . sql_sanitize($host_id) . " and snmp_query_id = " . sql_sanitize($data_query_id)), "", "snmp_index");
}

function api_data_query_get($data_query_id) {
	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_row("select * from data_query where id = " . sql_sanitize($data_query_id));
}

function api_data_query_list() {
	return db_fetch_assoc("select * from data_query order by name");
}

function api_data_query_fields_list($data_query_id, $input_type = "") {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");

	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_assoc("select * from data_query_field where data_query_id = " . sql_sanitize($data_query_id) . ((($input_type == DATA_QUERY_FIELD_TYPE_INPUT) || ($input_type == DATA_QUERY_FIELD_TYPE_OUTPUT)) ? " and type = $input_type" : "") . " order by name");
}

function api_data_query_field_get($data_query_field_id) {
	/* sanity check for $data_query_field_id */
	if ((!is_numeric($data_query_field_id)) || (empty($data_query_field_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_field_id' for 'data_query_field_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_row("select * from data_query_field where id = " . sql_sanitize($data_query_field_id));
}

?>