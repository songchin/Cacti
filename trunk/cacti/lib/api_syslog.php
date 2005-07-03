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

/* api_syslog_cacti_log - logs a string to Cacti's log file or optionally to the browser
   @arg $message - string value to log
   @arg $severity - integer value severity level
   @arg $poller_id - integer value poller id, if applicable
   @arg $host_id - integer value host_id, if applicable
   @arg $user_id - integer value user_id, optional, if not passed, figured out.
   @arg $output - (bool) whether to output the log line to the STDOUT using print()
   @arg $facility - integer value facility, if applicable, default FACIL_CMDPHP
   Note: Constants are defined for Severity and Facility, please reference include/config_constants.php */
function api_syslog_cacti_log($message, $severity = SEV_INFO, $poller_id = 1, $host_id = 0, $user_id = 0, $output = false, $facility = FACIL_CMDPHP) {
	global $config;

	/* fill in the current date for printing in the log */
	$logdate = date("Y-m-d H:i:s");

	/* determine how to log data */
	$syslog_destination = read_config_option("syslog_destination");
	$syslog_level = read_config_option("syslog_level");

	/* get username */
	if ($user_id) {
		$user_info = api_user_info(array("id" => $user_id));
		if ($user_info) {
	 		$username = $user_info["username"];
		} else {
			$usernmae = _("Unknown");
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

	/* Log to Cacti Syslog */
	if ((($syslog_destination == SYSLOG_CACTI) || ($syslog_destination == SYSLOG_BOTH)) 
		&& (read_config_option("syslog_status") != "suspended") && ($severity >= $syslog_level)) {
		/* echo the data to the log (append) */
		db_execute("insert into syslog 
			(logdate,facility,severity,poller_id,host_id,user_id,username,source,message) values
			(SYSDATE(), " . $facility . "," . $severity . "," . $poller_id . "," .$host_id . "," . $user_id . ",'" . $username . "','" . $source . "','". addslashes($message) . "');");
	}

	/* Log to System Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if ((($syslog_destination == SYSLOG_BOTH) || ($syslog_destination == SYSLOG_SYSTEM)) 
		&& ($severity != SEV_DEV) && ($severity >= $syslog_level)) {
		openlog("cacti", LOG_NDELAY | LOG_PID, read_config_option("syslog_facility"));
		syslog(api_syslog_get_syslog_severity($severity), api_syslog_get_severity($severity) . ": " . api_syslog_get_facility($facility) . ": " . $message);
		closelog();
	}

	/* print output to standard out if required */
	if (($output == true) &&($severity >= $syslog_level)) {
		print $logdate . " - " . api_syslog_get_severity($severity) . ": " . api_syslog_get_facility($facility) . ": " . $message . "\n";
	}

}

/* api_syslog_get_syslog_severity - returns the syslog severity level
   @arg $severity - the severity integer value */
function api_syslog_get_syslog_severity($severity) {
	global $config;

	if ($config["cacti_server_os"] == "win32") {
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

/* api_syslog_get_facility - returns the text version of the facility name
   @arg $facility - the facility integer value */
function api_syslog_get_facility($facility) {
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

/* api_syslog_get_severity - returns the text version of the message severity
   @arg $severity - the severity integer value */
function api_syslog_get_severity($severity) {
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

/* api_syslog_manage_cacti_log - determines if any action is required on the
   Cacti Syslog due to size constraints.  Clear or set a bit based upon status.
   @arg $print_data_to_stdout = wether or not to output log output to standard output. */
function api_syslog_manage_cacti_log($print_data_to_stdout) {
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

/* api_syslog_clear - empties the Cacti Syslog.
   @arg none */
function api_syslog_clear() {
	db_execute("TRUNCATE TABLE syslog");
	db_execute("REPLACE INTO settings (name,value) VALUES('syslog_status','active')");
}

/* api_syslog_color - Set's the foreground and background color of the syslog entries.
   @arg $severity - The log item severity. */
function api_syslog_color($severity) {
	global $colors	;

	switch ($severity) {
		case "EMERGENCY":
			return print "<tr bgcolor='#" . $colors["syslog_emergency_background"] . "'>\n";
			break;
		case "ALERT":
			return print "<tr bgcolor='#" . $colors["syslog_alert_background"] . "'>\n";
			break;
		case "CRITICAL":
			return print "<tr bgcolor='#" . $colors["syslog_critical_background"] . "'>\n";
			break;
		case "ERROR":
			return print "<tr bgcolor='#" . $colors["syslog_error_background"] . "'>\n";
			break;
		case "WARNING":
			return print "<tr bgcolor='#" . $colors["syslog_warning_background"] . "'>\n";
			break;
		case "NOTICE":
			return print "<tr bgcolor='#" . $colors["syslog_notice_background"] . "'>\n";
			break;
		case "INFO":
			return print "<tr bgcolor='#" . $colors["syslog_info_background"] . "'>\n";
			break;
		case "DEBUG":
			return print "<tr bgcolor='#" . $colors["syslog_debug_background"] . "'>\n";
			break;
		case "DEV":
			return print "<tr bgcolor='#" . $colors["syslog_dev_background"] . "'>\n";
			break;
		default:
			return print "<tr bgcolor='#" . $colors["syslog_info_background"] . "'>\n";
			break;
	}
}

/* api_syslog_export - exports the syslog to a file of the users choosing in CSV format.
   @arg none */
function api_syslog_export() {
}

?>
