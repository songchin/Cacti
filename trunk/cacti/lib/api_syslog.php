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
   @arg $output - (bool) whether to output the log line to the browser using pring() or not
   @arg $facility - integer value facility, if applicable, default FACIL_CMDPHP
   Note: Constants are defined for Severity and Facility, please reference include/config_constants.php*/
function api_syslog_cacti_log($message, $severity = SEV_INFO, $poller_id = 1, $host_id = 0, $user_id = 0, $output = false, $facility = FACIL_CMDPHP) {
	global $config;

	/* fill in the current date for printing in the log */
	$logdate = date("Y-m-d H:i:s");

	/* determine how to log data */
	$syslog_destination = read_config_option("log_destination");

	/* format the message */
	$textmessage = "$logdate - " . api_syslog_get_severity($severity) . ": " . api_syslog_get_facility($facility) . ": " . $message . "\n";

	/* get username */
	if ($user_id) {
	    $user_info = api_user_info(array("id" => $user_id));
	    $username = $user_info["username"];
	    //"select username from user_auth where user_id=$user_id");
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
		$source = "SYSTEM";
	}

	/* Log to Cacti Syslog */
	if (((read_config_option("syslog_destination") == SYSLOG_CACTI) || (read_config_option("syslog_destination") == SYSLOG_BOTH)) &&
		(read_config_option("log_verbosity") != POLLER_VERBOSITY_NONE) &&
		(read_config_option("syslog_status") != "suspended"))	{
		/* echo the data to the log (append) */
		db_execute("insert into syslog
			(logdate,facility,severity,poller_id,host_id,user_id,username,source,message) values
			('$logdate', '" . api_syslog_get_facility($facility) . "', '" . api_syslog_get_severity($severity) . "', '$poller_id', '$host_id', '$user_id', '$username', '$source', '". addslashes($message) . "');");
	}

	/* Log to System Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if ((read_config_option("syslog_destination") == SYSLOG_BOTH) || (read_config_option("syslog_destination") == SYSLOG_SYSTEM)) {
		if ($severity <= SEV_WARNING) {
			define_syslog_variables();

			if ($config["cacti_server_os"] == "win32")
				openlog("Cacti Logging", LOG_NDELAY | LOG_PID, LOG_USER);
			else
				openlog("Cacti Logging", LOG_NDELAY | LOG_PID, LOG_SYSLOG);

			if (($severity <= SEV_ERROR) && (read_config_option("log_perror"))) {
				syslog(LOG_CRIT, $textmessage);
			}

			if (($severity == SEV_WARNING) && (read_config_option("log_pwarn"))) {
				syslog(LOG_WARNING, $textmessage);
			}

			if ((($severity == SEV_NOTICE) || ($severity == SEV_INFO)) && (read_config_option("log_pstat"))) {
				syslog(LOG_INFO, $textmessage);
			}

			closelog();
		}
	}

	/* print output to standard out if required */
	if ($output == true) {
		print $textmessage;
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
			return "EMERGENCY";
			break;
		case SEV_ALERT:
			return "ALERT";
			break;
		case SEV_CRITICAL:
			return "CRITICAL";
			break;
		case SEV_ERROR:
			return "ERROR";
			break;
		case SEV_WARNING:
			return "WARNING";
			break;
		case SEV_NOTICE:
			return "NOTICE";
			break;
		case SEV_INFO:
			return "INFO";
			break;
		case SEV_DEBUG:
			return "DEBUG";
			break;
		default:
			return "UNKNOWN";
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

	if (eregi("k", $syslog_size)) {
		$syslog_size = eregi_replace("k", "", $syslog_size);
		$syslog_size = $syslog_size * 1024;
	}else if (eregi("m", $syslog_size)) {
		$syslog_size = eregi_replace("m", "", $syslog_size);
		$syslog_size = $syslog_size * 1024 * 1024;
	}

	if ($total_records >= $syslog_size) {
		switch ($syslog_control) {
		case SYSLOG_MNG_ASNEEDED:
			$records_to_delete = $total_records - $syslog_size;
			db_execute("DELETE FROM syslog ORDER BY logdate LIMIT " . $records_to_delete);

			break;
		case SYSLOG_MNG_DAYSOLD:
			db_execute("delete from syslog where logdate <= '" . date("Y-m-d H:i:s", strtotime("-" . $syslog_maxdays . " Days"))."'");

			break;
		case SYSLOG_MNG_STOPLOG:
			if (read_config_option("syslog_status") != "suspended") {
				api_syslog_cacti_log("The Cacti log is filled and can not receive additional records", SEV_CRITICAL, 0, 0, 0, $print_data_to_stdout, FACIL_POLLER);
				db_execute("REPLACE INTO settings (name,value) VALUES('syslog_status','suspended')");
			}

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

/* api_syslog_export - exports the syslog to a file of the users choosing in CSV format.
   @arg none */
function api_syslog_export() {
}

?>