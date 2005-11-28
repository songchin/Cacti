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

require_once(CACTI_BASE_PATH . "/include/log/log_arrays.php");
require_once(CACTI_BASE_PATH . "/include/log/log_constants.php");

/* 
 * Logging Actions
 */

/* api_log_log - logs a string to Cacti's log database, syslog, both, or stdout
   @arg $message - string value to log
   @arg $severity - integer value severity level
   @arg $poller_id - integer value poller id, if applicable
   @arg $host_id - integer value host_id, if applicable
   @arg $user_id - integer value user_id, optional, if not passed, figured out.
   @arg $output - (bool) whether to output the log line to the STDOUT using print()
   @arg $facility - integer value facility, if applicable, default FACIL_CMDPHP
   Note: Constants are defined for Severity and Facility, please reference include/global_constants.php */
#function api_log_log($message, $severity = SEV_INFO, $poller_id = 1, $host_id = 0, $user_id = 0, $facility = FACIL_CMDPHP, $plugin = "", $output = false) {
function api_log_log($message, $severity = SEV_INFO, $facility = FACIL_WEBUI, $plugin = "", $poller_id = 0, $host_id = 0, $user_id = 0, $output = false) {
	global $cnn_id;

	/* fill in the current date for printing in the log */
	$logdate = date("Y-m-d H:i:s");

	/* determine how to log data */
	$syslog_destination = log_read_config_option("syslog_destination");
	$syslog_level = log_read_config_option("syslog_level");

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
	if ((($syslog_destination == LOG_CACTI) || ($syslog_destination == LOG_BOTH))
		&& (log_read_config_option("syslog_status") != "suspended") && ($severity >= $syslog_level)) {
		$sql = "insert into log
			(logdate,facility,severity,poller_id,host_id,user_id,username,source,plugin,message) values
			(SYSDATE(), " . $facility . "," . $severity . "," . $poller_id . "," .$host_id . "," . $user_id . ",'" . $username . "','" . $source . "','" . $plugin . "','". sql_sanitize($message) . "');";
		/* DO NOT USE db_execute, function looping can occur when in SEV_DEV mode */
		$cnn_id->Execute($sql);
	}

	/* Log to System Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if ((($syslog_destination == LOG_BOTH) || ($syslog_destination == LOG_SYSTEM))
		&& ($severity >= $syslog_level)) {
		openlog("cacti", LOG_NDELAY | LOG_PID, log_read_config_option("syslog_facility"));
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
	$syslog_size = read_config_option("log_size");
	$syslog_control = read_config_option("log_control");
	$syslog_maxdays = read_config_option("log_maxdays");
	$total_records = db_fetch_cell("SELECT count(*) FROM log");

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
			db_execute("DELETE FROM log ORDER BY logdate LIMIT " . $records_to_delete);
			api_log_log(_("Log control removed " . $records_to_delete . " log entires."), SEV_NOTICE, FACIL_POLLER, "", 0, 0, 0, $print_data_to_stdout);
			break;
		case SYSLOG_MNG_DAYSOLD:
			db_execute("delete from log where logdate <= '" . date("Y-m-d H:i:s", strtotime("-" . $syslog_maxdays * 24 * 3600 . " Seconds"))."'");
			api_log_log(_("Log control removed log entries older than " . $syslog_maxdays . " days."), SEV_NOTICE, FACIL_POLLER, "", 0, 0, 0, $print_data_to_stdout);

			break;
		case SYSLOG_MNG_STOPLOG:
			if (read_config_option("log_status") != "suspended") {
				api_log__log(_("Log control suspended logging due to the log being full.  Please purge your logs manually."), SEV_CRITICAL,FACIL_POLLER, "", 0, 0, 0, $print_data_to_stdout);
				db_execute("REPLACE INTO settings (name,value) VALUES('log_status','suspended')");
			}

			break;
		case SYSLOG_MNG_NONE:
			api_log_log(_("The cacti log control mechanism is set to None.  This is not recommended, please purge your logs on a manual basis."), SEV_WARNING, FACIL_POLLER, "", 0, 0, 0, $print_data_to_stdout);
			break;
		}
	}

}


/* api_log_truncate - empties the log.
   @arg none */
function api_log_truncate() {
	db_execute("TRUNCATE TABLE log");
	db_execute("REPLACE INTO settings (name,value) VALUES('log_status','active')");
}



/*
 * Log Translation Functions
 */

/* syslog_read_config_option - finds the current value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns - the current value of the configuration option */
function log_read_config_option($config_name) {
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
		case FACIL_EMAIL:
			return "EMAIL";
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
