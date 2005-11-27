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

include_once(CACTI_BASE_PATH . "/include/log/log_constants.php");

$syslog_options = array(
	SYSLOG_CACTI => _("Cacti Syslog Only"),
	SYSLOG_BOTH => _("Cacti Syslog and System Syslog/Eventlog"),
	SYSLOG_SYSTEM => _("System Syslog/Eventlog Only"));

$syslog_control_options = array(
	SYSLOG_MNG_ASNEEDED => _("Overwrite events as needed"),
	SYSLOG_MNG_DAYSOLD => _("Overwrite events older than the maximum days"),
	SYSLOG_MNG_STOPLOG => _("Stop logging if maximum log size is exceeded"),
	SYSLOG_MNG_NONE => _("None (Not Recommended)")
);

$syslog_level = array(
	SEV_DEV => _("(-1) Developer Debug"),
	SEV_DEBUG => _("(0) Debug"),
	SEV_INFO => _("(1) Informational"),
	SEV_NOTICE => _("(2) Notice "),
	SEV_WARNING => _("(3) Warning"),
	SEV_ERROR => _("(4) Error"),
	SEV_CRITICAL => _("(5) Critical"),
	SEV_ALERT => _("(6) Alert"),
	SEV_EMERGENCY => _("(7) Emergency")
	);

if (CACTI_SERVER_OS == "unix") {
	$syslog_facility = array(
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
	$syslog_facility = array(
		LOG_USER => "USER"
	);
}


?>
