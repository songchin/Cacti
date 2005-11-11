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

function api_device_get($device_id) {
	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		api_syslog_cacti_log("Invalid input '$device_id' for 'device_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_row("select * from host where id = " . sql_sanitize($device_id));
}

function api_device_data_query_get($device_id, $data_query_id) {
	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_syslog_cacti_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	/* sanity check for $device_id */
	if ((!is_numeric($device_id)) || (empty($device_id))) {
		api_syslog_cacti_log("Invalid input '$device_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}

	return db_fetch_row("select * from host_data_query where host_id = " . sql_sanitize($device_id) . " and data_query_id = " . sql_sanitize($data_query_id));
}

function &api_device_status_types_list() {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

	return $host_status_types;
}

?>
