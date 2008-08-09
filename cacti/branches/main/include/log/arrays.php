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

$log_control_options = array(
	LOG_MSG_ASNEEDED => "Overwrite events as needed",
	LOG_MSG_DAYSOLD => "Overwrite events older than the maximum days",
	LOG_MSG_STOPLOG => "Stop logging if maximum log size is exceeded",
	LOG_MSG_NONE => "None (Not Recommended)"
);

$log_level = array(
	SEV_DEV => "(0) Developer Debug",
	SEV_DEBUG => "(1) Debug",
	SEV_INFO => "(2) Informational",
	SEV_NOTICE => "(3) Notice ",
	SEV_WARNING => "(4) Warning",
	SEV_ERROR => "(5) Error",
	SEV_CRITICAL => "(6) Critical",
	SEV_ALERT => "(7) Alert",
	SEV_EMERGENCY => "(8) Emergency"
	);

if (CACTI_SERVER_OS == "unix") {
	$log_system_facility = array(
		LOG_LOCAL0 => "LOCAL0",
		LOG_LOCAL1 => "LOCAL1",
		LOG_LOCAL2 => "LOCAL2",
		LOG_LOCAL3 => "LOCAL3",
		LOG_LOCAL4 => "LOCAL4",
		LOG_LOCAL5 => "LOCAL5",
		LOG_LOCAL6 => "LOCAL6",
		LOG_LOCAL7 => "LOCAL7",
		LOG_USER => "USER"
	);
}else{
	$log_system_facility = array(
		LOG_USER => "USER"
	);
}

$log_syslog_facility = array(
	SYSLOG_LOCAL0 => "LOCAL0",
	SYSLOG_LOCAL1 => "LOCAL1",
	SYSLOG_LOCAL2 => "LOCAL2",
	SYSLOG_LOCAL3 => "LOCAL3",
	SYSLOG_LOCAL4 => "LOCAL4",
	SYSLOG_LOCAL5 => "LOCAL5",
	SYSLOG_LOCAL6 => "LOCAL6",
	SYSLOG_LOCAL7 => "LOCAL7",
	SYSLOG_USER => "USER"
);


?>
