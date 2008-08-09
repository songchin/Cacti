<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

require_once(CACTI_BASE_PATH . "/include/log/constants.php");
require_once(CACTI_BASE_PATH . "/include/log/arrays.php");

/*
 * Logging Actions
 */


/**
 * Logs a message to the configured logging system
 *
 * This function is designed to handle logging for the cacti system.
 *
 * @param string $message the message your would like to log
 * @param int $severity the severity you would like to log at, check logging constants for values, Default = SEV_INFO
 * @param int $facility the facility you would like to log in, check logging constants for values. Default = FACIL_INTERFACE
 * @return bool true
 */
function log_save ($message, $severity = SEV_INFO, $facility = FACIL_INTERFACE, $parameters = array() ) {
#function log_save ($message, $severity = SEV_INFO, $facility = FACIL_INTERFACE, $plugin = "", $poller_id = 0, $host_id = 0, $output = false) {
	global $cnn_id;

	/* setup parameters array */
	$parameters = array (
		"" => "",


	);

	/* fill in the current date for printing in the log */
	$logdate = date("Y-m-d H:i:s");

	/* Get variables */
	$log_severity = log_read_config_option("log_severity");

	/* get username */
	if ($severity == SEV_DEV) {
		$username = "DEV";
	}else{
		if (isset($_SESSION["sess_user_id"])) {
			# --> FIX ME FOR NEW AUTH SYSTEM <--
			#$user_info = user_info(array("id" => $_SESSION["sess_user_id"]));
			#$username = $user_info["username"];
			$username = "admin";
		}else{
			$username = "SYSTEM";
		}
	}

	/* set the IP Address */
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$source = $_SERVER["REMOTE_ADDR"];
	}else {
		$source = "System";
	}

	/* Format message for developer if SEV_DEV is allowed */
	if (($severity >= $log_severity) && ($severity == SEV_DEV)) {
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

	/* Log to Cacti System Log */
	if ((log_read_config_option("log_dest_cacti") == "on") && (log_read_config_option("log_status") != "suspended") && ($severity >= $log_severity)) {
		$sql = "insert into log
			(logdate,facility,severity,poller_id,host_id,username,source,plugin,message) values
			(SYSDATE(), " . $facility . "," . $severity . "," . $poller_id . "," .$host_id . ",'" . $username . "','" . $source . "','" . $plugin . "','". sql_sanitize($message) . "');";
		/* DO NOT USE db_execute, function looping can occur when in SEV_DEV mode */
		$cnn_id->Execute($sql);
	}

	/* Log to System Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if ((log_read_config_option("log_dest_system") == "on") && ($severity >= $log_severity)) {
		openlog("cacti", LOG_NDELAY | LOG_PID, log_read_config_option("log_system_facility"));
		syslog(log_get_system_severity($severity), log_get_severity($severity) . ": " . log_get_facility($facility) . ": " . $message);
		closelog();
	}

	/* Log to Syslog Server */
	if ((log_read_config_option("log_dest_syslog") == "on") && ($severity >= $log_severity)) {
		log_save_syslog(log_read_config_option("log_syslog_server"), log_read_config_option("log_syslog_port"), log_read_config_option("log_syslog_facility"), log_get_severity_syslog($severity), log_get_severity($severity) . ": " . log_get_facility($facility) . ": " . $message);
	}


	/* print output to standard out if required, only for use in command line scripts */
	if (($output == true) && ($severity >= $log_severity)) {
		print $logdate . " - " . log_get_severity($severity) . ": " . log_get_facility($facility) . ": " . $message . "\n";
	}

	return true;

}


/**
 * Manages the cacti system log
 *
 * Maintains the cacti system log based on system settings
 *
 * @param bool $print_data_to_stdout display log message to stdout
 * @return bool true
 */
function log_maintain ($print_data_to_stdout) {
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
			log_save(_("Log control removed " . $records_to_delete . " log entires."), SEV_NOTICE, FACIL_POLLER, "", 0, 0, $print_data_to_stdout);
			break;
		case SYSLOG_MNG_DAYSOLD:
			db_execute("delete from log where logdate <= '" . date("Y-m-d H:i:s", strtotime("-" . $syslog_maxdays * 24 * 3600 . " Seconds"))."'");
			log_save(_("Log control removed log entries older than " . $syslog_maxdays . " days."), SEV_NOTICE, FACIL_POLLER, "", 0, 0, $print_data_to_stdout);

			break;
		case SYSLOG_MNG_STOPLOG:
			if (read_config_option("log_status") != "suspended") {
				log_save(_("Log control suspended logging due to the log being full.  Please purge your logs manually."), SEV_CRITICAL,FACIL_POLLER, "", 0, 0, 0, $print_data_to_stdout);
				db_execute("REPLACE INTO settings (name,value) VALUES('log_status','suspended')");
			}

			break;
		case SYSLOG_MNG_NONE:
			log_save(_("The cacti log control mechanism is set to None.  This is not recommended, please purge your logs on a manual basis."), SEV_WARNING, FACIL_POLLER, "", 0, 0, $print_data_to_stdout);
			break;
		}
	}

	return true;

}


/**
 * Truncates the cacti system log
 *
 * Truncates the cacti system log and logs that it occured
 *
 * @return bool true
 */
function log_clear () {
	db_execute("TRUNCATE TABLE log");
	db_execute("REPLACE INTO settings (name,value) VALUES('log_status','active')");
	log_save("Log truncated", SEV_NOTICE, FACIL_WEBUI);

	return true;

}



/*
 * Log Translation Functions
 */

/**
 * Reads cacti configuration settings, without this Developer debug can cause database looping
 *
 * Finds the current value of a cacti configuration setting
 *
 * @param string $config_name configuration variable to retrieve value
 * @return bool true
 */
function log_read_config_option ($config_name) {
	global $cnn_id, $log_config_options;

	if (isset($log_config_options[$config_name])) {
		/* Prefer global var for speed */
		$value = $log_config_options[$config_name];
	}else{
		if (isset($_SESSION["sess_config_array"][$config_name])) {
			/* Use session if exists */
			$value = $_SESSION["sess_config_array"][$config_name];
		}else{
			/* Go to the database */
			$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
			$query = $cnn_id->Execute("select value from settings where name='" . $config_name . "'");

			if ($query) {
				if (! $query->EOF) {
					$db_setting = $query->fields;
				}
			}

			if (isset($db_setting["value"])) {
				$value = $db_setting["value"];
			}else{
				/* Read default if nothing else set */
				$value = read_default_config_option($config_name);
			}
		}
	}

	/* Set session config if sessions active */
	if (isset($_SESSION["sess_config_array"])) {
		$_SESSION["sess_config_array"][$config_name] = $value;
	}
	/* Set value in global array */
	$log_config_options[$config_name] = $value;

	return $value;

}


/**
 * Returns the system (syslog/eventlog) severity level
 *
 * Given a Severity Level constant, return the php syslog constant
 *
 * @param int $severity cacti severity level
 * @return int php syslog severity level
 */
function log_get_system_severity ($severity) {
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


/**
 * Returns human readable facility text
 *
 * Given a facility constant, return human readable text
 *
 * @param int $facility cacti facility constant
 * @return string cacti facility in human readable text
 */
function log_get_facility ($facility) {
	switch ($facility) {
		case FACIL_CMDPHP:
			return "CMDPHP";
			break;
		case FACIL_SPINE:
			return "SPINE";
			break;
		case FACIL_POLLER:
			return "POLLER";
			break;
		case FACIL_SCPTSVR:
			return "SCPTSVR";
			break;
		case FACIL_INTERFACE:
			return "INTERFACE";
			break;
		case FACIL_EXPORT:
			return "EXPORT";
			break;
		case FACIL_AUTH:
			return "AUTH";
			break;
		case FACIL_EVENT:
			return "EVENT";
			break;
		default:
			return "UNKNOWN";
			break;
	}
}


/**
 * Returns human readable severity text
 *
 * Given a severity constant, return human readable text
 *
 * @param int $severity cacti severity constant
 * @return string cacti severity in human readable text
 */
function log_get_severity ($severity) {
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


/**
 * Returns syslog severity value
 *
 * Given a severity constant, return syslog severity value
 *
 * @param int $severity cacti severity constant
 * @return int syslog severity value
 */
function log_get_severity_syslog ($severity) {

	switch ($severity) {
		case SEV_EMERGENCY:
			return SYSLOG_EMERGENCY;
			break;
		case SEV_ALERT:
			return SYSLOG_ALERT;
			break;
		case SEV_CRITICAL:
			return SYSLOG_CRITICAL;
			break;
		case SEV_ERROR:
			return SYSLOG_ERROR;
			break;
		case SEV_WARNING:
			return SYSLOG_WARNING;
			break;
		case SEV_NOTICE:
			return SYSLOG_NOTICE;
			break;
		case SEV_INFO:
			return SYSLOG_INFO;
			break;
		case SEV_DEBUG:
			return SYSLOG_DEBUG;
			break;
		case SEV_DEV:
			return SYSLOG_DEBUG;
			break;
		default:
			return SYSLOG_INFO;
			break;
	}

}


/**
 * Send syslog message to a syslog server
 *
 * Generates and sends a syslog packet to a syslog server
 *
 * @param string $syslog_server Server to send syslog messages to
 * @param int $syslog_server_port Port to send to on syslog server
 * @param int $syslog_facility Syslog facility value, refer to syslog log constants
 * @param int $syslog_severity Syslog severity value, refer to syslog log constants
 * @param string $syslog_message message to send to syslog server
 * @return bool true on sent, false on error
 */
function log_save_syslog ($syslog_server, $syslog_server_port, $syslog_facility, $syslog_severity, $syslog_message) {
	global $cnn_id;

	/* Set syslog tag */
	$syslog_tag = "cacti";

	/* Get the pid */
	$pid = getmypid();

	/* Set syslog server */
	if (strtolower(substr($syslog_server, 0, 5)) == "udp://") {
		$syslog_server = strtolower($syslog_server);
	} elseif (strtolower(substr($syslog_server, 0, 5)) == "udp://") {
		$syslog_server = strtolower($syslog_server);
	}else{
		$syslog_server = "udp://" . $syslog_server;
	}

	/* Check facility */
	if (empty($syslog_facility)) {
		$syslog_facility = SYSLOG_LOCAL0;
	}
	if (($syslog_facility > 23) || ($syslog_facility < 0)) {
		$syslog_facility = SYSLOG_LOCAL0;
	}

	/* Check severity */
	if (empty($syslog_severity)) {
		$syslog_severity = SYSLOG_INFO;
	}
	if (($syslog_severity > 7) || ($syslog_severity < 0)) {
		$syslog_severity = SYSLOG_INFO;
	}

	/* Make syslog packet */
	$host = $_SERVER["SERVER_NAME"];
	$time = time();
	if (strlen(date("j", $time)) < 2) {
		$time = date("M  j H H:i:s", $time);
	}else{
		$time = date("M j H H:i:s", $time);
	}
	$priority = ($syslog_facility * 8) + $syslog_severity;
	#$packet = "<" . $priority . ">" . $time . " " . $host . " " . $syslog_tag . "[" . $pid  . "]:" . $syslog_message;
	$packet = "<" . $priority . ">" . $syslog_tag . "[" . $pid  . "]: " . $syslog_message;
	if (strlen($packet) > 1024) {
		$packet = substr($packet, 0, 1024);
	}

	/* Send the syslog message */
	$socket = @fsockopen($syslog_server, $syslog_server_port, $error_number, $error_string);
	if ($socket) {
		@fwrite($socket, $packet);
		@fclose($socket);
		return true;
	}else{
		/* socket error - log to database */
		$sql = "insert into log
			(logdate,facility,severity,poller_id,host_id,username,source,plugin,message) values
			(SYSDATE(), " . FACIL_WEBUI . "," . SEV_ERROR . ",0,0,'SYSTEM','SYSLOG','N/A','". sql_sanitize("Syslog error[" . $error_number ."]: " . $error_string) . "');";
		/* DO NOT USE db_execute, function looping can occur when in SEV_DEV mode */
		$cnn_id->Execute($sql);
		return false;
	}

	return true;

}


/**
 * Returns the name of the function *before* the calling function
 *
 * Returns the name of the function *before* the calling function. This is useful in
 * situations where you have a generic library and want to log the name of the function
 * that called it.
 *
 * @return string the function name from the call stack
 */
function log_get_last_function () {
	$backtrace = debug_backtrace();
	if (sizeof($backtrace) < 3) {
		return $backtrace[1]["function"];
	}else{
		return $backtrace[2]["function"];
	}
}

?>
