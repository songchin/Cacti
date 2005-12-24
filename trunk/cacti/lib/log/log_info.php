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

	return db_fetch_cell("select count(*) from log $sql_where");

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
                log.id,
                log.logdate,
                log.facility,
                log.severity,
                poller.name as poller_name,
                poller.id as poller_id,
                host.description as host,
                log.username,
		log.plugin,
		log.source,
                log.message
                FROM (log LEFT JOIN host ON log.host_id = host.id)
                LEFT JOIN poller ON log.poller_id = poller.id
                $sql_where
                order by log.logdate",$limit,$offset);

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

function api_log_username_list() {

	$user = array();
	$users = db_fetch_assoc("select username from user_auth order by username");
	
	$user["SYSTEM"] = "SYSTEM";
	while (list($id,$user_record) = each($users)) {
		$user[$user_record["username"]] = $user_record["username"];
	}

	return $user;

}

function api_log_plugin_list() {

	$plugin = array();

	$plugins = db_fetch_assoc("select distinct plugin,plugin from log where plugin != 'N/A' order by plugin");

	if (sizeof($plugins) > 0) {
		while (list($id,$plugin_record) = each($plugins)) {
			$plugin[$plugin_record["plugin"]] = $plugin_record["plugin"];
		}
	}

	return $plugin;

}


function api_log_poller_list() {

	$poller = array();

	$pollers = db_fetch_assoc("select id, hostname from poller order by hostname");
	
	$poller["0"] = "SYSTEM";
	while (list($id,$poller_record) = each($pollers)) {
		$poller[$poller_record["id"]] = $poller_record["hostname"];
	}

	return $poller;

}

function api_log_host_list() {

	$host = array();

	$hosts = db_fetch_assoc("select id, hostname from host order by hostname");
	
	$host["0"] = "SYSTEM";
	while (list($id,$hostname) = each($hosts)) {
		$host[$hostname["id"]] = $hostname["hostname"];
	}

	return $host;

}


function api_log_facility_list() {
	
	$facility = array();
	$facility[FACIL_CMDPHP] = "CMDPHP";
	$facility[FACIL_CACTID] = "CACTID";
	$facility[FACIL_POLLER] = "POLLER";
	$facility[FACIL_SCPTSVR] = "SCPTSVR";
	$facility[FACIL_WEBUI] = "WEBUI";
	$facility[FACIL_EXPORT] = "EXPORT";
	$facility[FACIL_AUTH] = "AUTH";
	$facility[FACIL_EMAIL] = "EMAIL";
	
	return $facility;	
}


function api_log_severity_list() {
	
	$severity = array();
	$severity[SEV_EMERGENCY] = "EMERGENCY";
	$severity[SEV_ALERT] = "ALERT";
	$severity[SEV_CRITICAL] = "CRITICAL";
	$severity[SEV_ERROR] = "ERROR";
	$severity[SEV_WARNING] = "WARNING";
	$severity[SEV_NOTICE] = "NOTICE";
	$severity[SEV_INFO] = "INFO";
	$severity[SEV_DEBUG] = "DEBUG";
	$severity[SEV_DEV] = "DEV";
	
	return $severity;	
}


/* api_log_html_css_class - Set's the CSS class for the log entry.
   @arg $severity - The log item severity. */
function api_log_html_css_class($severity) {

	switch ($severity) {
		case "EMERGENCY":
			return "log_row_emergency";
			break;
		case "ALERT":
			return "log_row_alert";
			break;
		case "CRITICAL":
			return "log_row_crit";
			break;
		case "ERROR":
			return "log_row_error";
			break;
		case "WARNING":
			return "log_row_warning";
			break;
		case "NOTICE":
			return "log_row_notice";
			break;
		case "DEBUG":
			return "log_row_debug";
			break;
		case "DEV":
			return "log_row_dev";
			break;
		default: /* Also INFO */
			return "log_row_info";
			break;
	}
}


?>
