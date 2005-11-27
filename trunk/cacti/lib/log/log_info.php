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

/* 
 * Log viewing actions
 */


function api_log_total_get ($filter_array = "") {

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_log_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_log_fields_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from syslog $sql_where");

}


function api_log_list ($filter_array,$limit = -1,$offset = -1) {
	
	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_log_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_log_fields_list(), true);
		}
	}

	$sql_limit = "";

        return db_fetch_assoc("SELECT
                syslog.id,
                syslog.logdate,
                syslog.facility,
                syslog.severity,
                poller.name as poller_name,
                poller.id as poller_id,
                host.description as host,
                syslog.username,
                syslog.message
                FROM (syslog LEFT JOIN host ON syslog.host_id = host.id)
                LEFT JOIN poller ON syslog.poller_id = poller.id
                $sql_where
                order by syslog.logdate",$limit,$offset);

}


function api_log_fields_list() {
	require(CACTI_BASE_PATH . "/include/log/log_form.php");

	return $fields_log;

}

function validate_log_fields(&$_fields_log, $log_field_name_format = "|field|") {

	if (sizeof($_fields_log) == 0) {

		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_device = api_log_fields_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_device)) {
		if ((isset($_fields_log[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $log_field_name_format);

			if (!form_input_validate($_fields_log[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

?>
