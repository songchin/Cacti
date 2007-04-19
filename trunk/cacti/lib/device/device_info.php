<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/**
 * Returns a list of all available devices
 *
 * @param array $filter_array specifies fields to filter the database output with
 * @param string $order_column the database field to sort on
 * @param string $order_direction whether to sort in ascending or descending order
 * @param int $limit_start the row number to begin at
 * @param int $limit_length the number of rows to return
 * @return array an associative array with one key for each row
 */
function api_device_list($filter_array = "", $order_column = "description", $order_direction = "asc", $limit_start = 0, $limit_length = 0)
{
	if (!db_column_name_validate($order_column)) return false;
	if (!db_order_direction_validate($order_direction)) return false;
	if (!db_integer_validate($limit_start, true)) return false;
	if (!db_integer_validate($limit_length, true)) return false;

	$sql_where = "";
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		$sql_where = db_filter_array_to_where_string(sql_filter_array_add_types($filter_array, api_device_form_list()), true);
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
		order by $order_column $order_direction
		" . ($limit_length > 0 ? "limit $limit_start,$limit_length" : ""));
}

function api_device_total_get($filter_array = "") {
	require_once(CACTI_BASE_PATH . "/lib/device/device_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_device_field_validate(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_device_form_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from host $sql_where");
}

function api_device_get($device_id) {
	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		log_save("Invalid input '$device_id' for 'device_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	return db_fetch_row("select * from host where id = " . sql_sanitize($device_id));
}

function api_device_data_query_get($device_id, $data_query_id) {
	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		log_save("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		log_save("Invalid input '$device_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	return db_fetch_row("select * from host_data_query where host_id = " . sql_sanitize($device_id) . " and data_query_id = " . sql_sanitize($data_query_id));
}

function api_device_package_list($device_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");

	return db_fetch_assoc("select
		package.id,
		package.name
		from package,host_package
		where package.id=host_package.package_id
		and host_package.host_id = $device_id
		order by package.name");
}

function api_device_graph_template_used_get($device_id, $graph_template_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");
	validate_id_die($graph_template_id, "graph_template_id");

	$graph_template_id = db_fetch_cell("select id from graph where graph_template_id = $graph_template_id and host_id = $device_id, limit 0,1");

	return empty($graph_template_id) ? false : $graph_template_id;
}

function &api_device_form_list() {
	require(CACTI_BASE_PATH . "/include/device/device_form.php");

	return $fields_device;
}

function &api_device_status_type_list() {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

	return $host_status_types;
}

?>
