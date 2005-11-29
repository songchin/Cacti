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

function api_device_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/device/device_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_device_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_device_fields_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		host.id,
		host.disabled,
		host.status,
		host.hostname,
		host.description,
		host.min_time,
		host.max_time,
		host.cur_time,
		host.avg_time,
		host.availability
		from host
		$sql_where
		order by host.description
		$sql_limit");
}

function api_device_total_get($filter_array = "") {
	require_once(CACTI_BASE_PATH . "/lib/device/device_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_device_fields_validate(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_device_fields_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from host $sql_where");
}

function api_device_get($device_id) {
	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		api_log_log("Invalid input '$device_id' for 'device_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	return db_fetch_row("select * from host where id = " . sql_sanitize($device_id));
}

function api_device_data_query_get($device_id, $data_query_id) {
	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_log_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		api_log_log("Invalid input '$device_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	return db_fetch_row("select * from host_data_query where host_id = " . sql_sanitize($device_id) . " and data_query_id = " . sql_sanitize($data_query_id));
}

function &api_device_fields_list() {
	require(CACTI_BASE_PATH . "/include/device/device_form.php");

	return $fields_device;
}

function &api_device_status_types_list() {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

	return $host_status_types;
}

?>
