<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

function api_data_source_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$_sv_arr = array();
		$field_errors = api_data_source_fields_validate(sql_filter_array_to_field_array($filter_array), $_sv_arr);

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_data_source_form_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		data_source.id,
		data_source.name_cache,
		data_source.active,
		data_source.data_input_type,
		data_template.template_name as data_template_name,
		data_source.host_id
		from data_source
		left join data_template
		on (data_source.data_template_id=data_template.id)
		$sql_where
		order by data_source.name_cache,data_source.host_id
		$sql_limit");
}

function api_data_source_total_get($filter_array = "") {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$_sv_arr = array();
		$field_errors = api_data_source_fields_validate(sql_filter_array_to_field_array($filter_array), $_sv_arr);

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_data_source_form_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from data_source $sql_where");
}

function api_data_source_get($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	return db_fetch_row("select * from data_source where id = " . sql_sanitize($data_source_id));
}

function api_data_source_rra_item_list($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	return db_fetch_assoc("select * from data_source_rra_item where data_source_id = " . sql_sanitize($data_source_id) . " order by consolidation_function,steps");
}

function api_data_source_rra_item_get($data_source_rra_item_id) {
	/* sanity checks */
	validate_id_die($data_source_rra_item_id, "data_source_rra_item_id");

	return db_fetch_row("select * from data_source_rra_item where id = " . sql_sanitize($data_source_rra_item_id));
}

function api_data_source_item_list($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	return db_fetch_assoc("select
		data_source_item.rrd_heartbeat,
		data_source_item.rrd_minimum,
		data_source_item.rrd_maximum,
		data_source_item.data_source_name,
		data_source_item.data_source_type
		from data_source_item
		where data_source_item.data_source_id = " . sql_sanitize($data_source_id));
}

/* get_data_source_title - returns the title of a data source without using the title cache unless the title ends up empty.
   @arg $data_source_id - (int) the ID of the data source to get a title for
   @returns - the data source title */
function api_data_source_title_get($data_source_id, $remove_unsubstituted_variables = false) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	$data_source = db_fetch_row("select host_id,name,name_cache from data_source where id = $data_source_id");

	$title = $data_source["name"];

	if ((strstr($data_source["name"], "|host_")) && (!empty($data_source["host_id"]))) {
		$title = substitute_host_variables($title, $data_source["host_id"]);
	}

	if ((strstr($data_source["name"], "|query_")) && (!empty($data_source["host_id"]))) {
		$data_query = array_rekey(db_fetch_assoc("select
			data_source_field.name,
			data_source_field.value
			from data_source_field,data_source
			where data_source.id=data_source_field.data_source_id
			and data_source.id = $data_source_id"), "name", "value");

		if ((isset($data_query["data_query_id"])) && (isset($data_query["data_query_index"]))) {
			$title = substitute_data_query_variables($title, $data_source["host_id"], $data_query["data_query_id"], $data_query["data_query_index"], read_config_option("max_data_query_field_length"));
		}
	}

	if ($remove_unsubstituted_variables == true) {
		$title = remove_variables($title);
	}

	if (((empty($title)) || (substr_count($title,"|"))) && (!empty($data_source["name_cache"]))) {
		$title = $data_source["name_cache"];
	}

	return $title;
}

/* api_data_source_path_get - returns the full path to the .rrd file associated with a given data source
   @arg $data_source_id - (int) the ID of the data source
   @arg $expand_paths - (bool) whether to expand the <path_rra> variable into its full path or not
   @returns - the full path to the data source or an empty string for an error */
function api_data_source_path_get($data_source_id, $expand_paths) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");

	$current_path = db_fetch_cell("select rrd_path from data_source where id = $data_source_id");

	/* generate a new path if needed */
	if ($current_path == "") {
		$current_path = api_data_source_path_get_update($data_source_id);
	}

	if ($expand_paths == true) {
		return substitute_path_variables($current_path);
	}else{
		return $current_path;
	}
}

function &api_data_source_form_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");

	return $fields_data_source;
}

function &api_data_source_item_form_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");

	return $fields_data_source_item;
}

function &api_data_source_input_type_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	return $data_input_types;
}

function &api_data_source_type_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	return $data_source_types;
}

function &api_data_source_polling_interval_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	return $data_source_polling_intervals;
}

?>