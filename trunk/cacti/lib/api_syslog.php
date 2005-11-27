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


function api_log_total_num ($filter_array) {



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
print_a($sql_where);
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






/* 
 * Logging Actions
 */

/* api_syslog_cacti_log - logs a string to Cacti's log file or optionally to the browser
   @arg $message - string value to log
   @arg $severity - integer value severity level
   @arg $poller_id - integer value poller id, if applicable
   @arg $host_id - integer value host_id, if applicable
   @arg $user_id - integer value user_id, optional, if not passed, figured out.
   @arg $output - (bool) whether to output the log line to the STDOUT using print()
   @arg $facility - integer value facility, if applicable, default FACIL_CMDPHP
   Note: Constants are defined for Severity and Facility, please reference include/global_constants.php */
function api_syslog_cacti_log($message, $severity = SEV_INFO, $poller_id = 1, $host_id = 0, $user_id = 0, $output = false, $facility = FACIL_CMDPHP, $plugin = "") {
	global $cnn_id;

	/* fill in the current date for printing in the log */
	$logdate = date("Y-m-d H:i:s");

	/* determine how to log data */
	$syslog_destination = syslog_read_config_option("syslog_destination");
	$syslog_level = syslog_read_config_option("syslog_level");

	/* get username */
	if ($severity == SEV_DEV) {
		$user_id = 0;
		$username = "DEV";
	} elseif ($user_id) {
		$user_info = api_user_info(array("id" => $user_id));
		if ($user_info) {
	 		$username = $user_info["username"];
		} else {
			$username = _("Unknown");
		}
	}else{
		if (isset($_SESSION["sess_user_id"])) {
			$user_info = api_user_info(array("id" => $_SESSION["sess_user_id"]));
			$user_id = $_SESSION["sess_user_id"];
			$username = $user_info["username"];
		}else{
			$username = "SYSTEM";
		}
	}

	/* set the IP Address */
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$source = $_SERVER["REMOTE_ADDR"];
	}else {
		$source = _("System");
	}

	/* Format message for developer if SEV_DEV is allowed */
	if (($severity >= $syslog_level) && ($severity == SEV_DEV)) {
		/* get a backtrace so we can derive the current filename/line#/function */
		$backtrace = debug_backtrace();
		if (sizeof($backtrace) == 1) {
			$function_name = $backtrace[0]["function"];
			$filename = $backtrace[0]["file"];
			$line_number = $backtrace[0]["line"];
		} else {
			$function_name = $backtrace[1]["function"];
			$filename = $backtrace[0]["file"];
			$line_number = $backtrace[0]["line"];
		}
		$message = str_replace(CACTI_BASE_PATH, "", $filename) . ":$line_number in " . ($function_name == "" ? "main" : $function_name) . "(): $message";
	}

	/* Log to Cacti Syslog */
	if ((($syslog_destination == SYSLOG_CACTI) || ($syslog_destination == SYSLOG_BOTH))
		&& (syslog_read_config_option("syslog_status") != "suspended") && ($severity >= $syslog_level)) {
		$sql = "insert into syslog
			(logdate,facility,severity,poller_id,host_id,user_id,username,source,message) values
			(SYSDATE(), " . $facility . "," . $severity . "," . $poller_id . "," .$host_id . "," . $user_id . ",'" . $username . "','" . $source . "','". sql_sanitize($message) . "');";
		/* DO NOT USE db_execute, function looping can occur when in SEV_DEV mode */
		$cnn_id->Execute($sql);
	}

	/* Log to System Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if ((($syslog_destination == SYSLOG_BOTH) || ($syslog_destination == SYSLOG_SYSTEM))
		&& ($severity >= $syslog_level)) {
		openlog("cacti", LOG_NDELAY | LOG_PID, syslog_read_config_option("syslog_facility"));
		syslog(api_log_syslog_severity_get($severity), api_log_severity_get($severity) . ": " . api_log_facility_get($facility) . ": " . $message);
		closelog();
	}

	/* print output to standard out if required */
	if (($output == true) &&($severity >= $syslog_level)) {
		print $logdate . " - " . api_log_severity_get($severity) . ": " . api_log_facility_get($facility) . ": " . $message . "\n";
	}

}


/* api_log_maintain - determines if any action is required on the
   Cacti Syslog due to size constraints.  Clear or set a bit based upon status.
   @arg $print_data_to_stdout = wether or not to output log output to standard output. */
function api_log_maintain($print_data_to_stdout) {
	/* read current configuration options */
	$syslog_size = read_config_option("syslog_size");
	$syslog_control = read_config_option("syslog_control");
	$syslog_maxdays = read_config_option("syslog_maxdays");
	$total_records = db_fetch_cell("SELECT count(*) FROM syslog");

	/* Input validation */
	if (! is_numeric($syslog_maxdays)) {
		$syslog_maxdays = 7;
	}
	if (! is_numeric($syslog_size)) {
		$syslog_size = 1000000;
	}

	if ($total_records >= $syslog_size) {
		switch ($syslog_control) {
		case SYSLOG_MNG_ASNEEDED:
			$records_to_delete = $total_records - $syslog_size;
			db_execute("DELETE FROM syslog ORDER BY logdate LIMIT " . $records_to_delete);
			api_syslog_cacti_log(_("Log control removed " . $records_to_delete . " log entires."), SEV_NOTICE, 0, 0, 0, $print_data_to_stdout, FACIL_POLLER);
			break;
		case SYSLOG_MNG_DAYSOLD:
			db_execute("delete from syslog where logdate <= '" . date("Y-m-d H:i:s", strtotime("-" . $syslog_maxdays * 24 * 3600 . " Seconds"))."'");
			api_syslog_cacti_log(_("Log control removed log entries older than " . $syslog_maxdays . " days."), SEV_NOTICE, 0, 0, 0, $print_data_to_stdout, FACIL_POLLER);

			break;
		case SYSLOG_MNG_STOPLOG:
			if (read_config_option("syslog_status") != "suspended") {
				api_syslog_cacti_log(_("Log control suspended logging due to the log being full.  Please purge your logs manually."), SEV_CRITICAL, 0, 0, 0, $print_data_to_stdout, FACIL_POLLER);
				db_execute("REPLACE INTO settings (name,value) VALUES('syslog_status','suspended')");
			}

			break;
		case SYSLOG_MNG_NONE:
			api_syslog_cacti_log(_("The cacti log control mechanism is set to None.  This is not recommended, please purge your logs on a manual basis."), SEV_WARNING, 0, 0, 0, $print_data_to_stdout, FACIL_POLLER);
			break;
		}
	}

}


/* api_syslog_clear - empties the log.
   @arg none */
function api_log_truncate() {
	db_execute("TRUNCATE TABLE syslog");
	db_execute("REPLACE INTO settings (name,value) VALUES('syslog_status','active')");
}



/*
 * Log Translation Functions
 */

/* syslog_read_config_option - finds the current value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns - the current value of the configuration option */
function syslog_read_config_option($config_name) {
	global $cnn_id;

	$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
	$query = $cnn_id->Execute("select value from settings where name='" . $config_name . "'");
	if ($query) {
		if (! $query->EOF) {
		        $db_setting = $query->fields;
		}
	}

	if (isset($db_setting["value"])) {
		return $db_setting["value"];
	}else{
		return read_default_config_option($config_name);
	}

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


/* api_log_syslog_severity_get - returns the syslog severity level
   @arg $severity - the severity integer value */
function api_log_syslog_severity_get($severity) {
	if (CACTI_SERVER_OS == "win32") {
		return LOG_WARNING;
	} else {
		switch ($severity) {
			case SEV_EMERGENCY:
				return LOG_EMERG;
				break;
			case SEV_ALERT:
				return LOG_ALERT;
				break;
			case SEV_CRITICAL:
				return LOG_CRIT;
				break;
			case SEV_ERROR:
				return LOG_ERR;
				break;
			case SEV_WARNING:
				return LOG_WARNING;
				break;
			case SEV_NOTICE:
				return LOG_NOTICE;
				break;
			case SEV_INFO:
				return LOG_INFO;
				break;
			case SEV_DEBUG:
				return LOG_DEBUG;
				break;
			case SEV_DEV:
				return LOG_DEBUG;
				break;
			default:
				return LOG_INFO;
				break;
		}
	}
}


/* api_log_facility_get - returns the text version of the facility name
   @arg $facility - the facility integer value */
function api_log_facility_get($facility) {
	switch ($facility) {
		case FACIL_CMDPHP:
			return "CMDPHP";
			break;
		case FACIL_CACTID:
			return "CACTID";
			break;
		case FACIL_POLLER:
			return "POLLER";
			break;
		case FACIL_SCPTSVR:
			return "SCPTSVR";
			break;
		case FACIL_WEBUI:
			return "WEBUI";
			break;
		case FACIL_EXPORT:
			return "EXPORT";
			break;
		case FACIL_AUTH:
			return "AUTH";
			break;
		case FACIL_SMTP:
			return "SMTP";
			break;
		default:
			return "UNKNOWN";
			break;
	}
}


/* api_severity_get - returns the text version of the message severity
   @arg $severity - the severity integer value */
function api_log_severity_get($severity) {
	switch ($severity) {
		case SEV_EMERGENCY:
			return _("EMERGENCY");
			break;
		case SEV_ALERT:
			return _("ALERT");
			break;
		case SEV_CRITICAL:
			return _("CRITICAL");
			break;
		case SEV_ERROR:
			return _("ERROR");
			break;
		case SEV_WARNING:
			return _("WARNING");
			break;
		case SEV_NOTICE:
			return _("NOTICE");
			break;
		case SEV_INFO:
			return _("INFO");
			break;
		case SEV_DEBUG:
			return _("DEBUG");
			break;
		case SEV_DEV:
			return _("DEV");
			break;
		default:
			return _("UNKNOWN");
			break;
	}
}

?>
