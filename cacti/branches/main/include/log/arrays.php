<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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
	CACTI_LOG_CLEANUP_ASNEEDED => __("Overwrite events as needed"),
	CACTI_LOG_CLEANUP_DAYSOLD  => __("Overwrite events older than the maximum days"),
	CACTI_LOG_CLEANUP_STOPLOG  => __("Stop logging if maximum log size is exceeded"),
	CACTI_LOG_CLEANUP_NONE     => __("None (Not Recommended)")
);

$log_level = array(
	CACTI_LOG_SEV_DEV       => "(0) " . __("Developer Debug"),
	CACTI_LOG_SEV_DEBUG     => "(1) " . __("Debug"),
	CACTI_LOG_SEV_INFO      => "(2) " . __("Informational"),
	CACTI_LOG_SEV_NOTICE    => "(3) " . __("Notice "),
	CACTI_LOG_SEV_WARNING   => "(4) " . __("Warning"),
	CACTI_LOG_SEV_ERROR     => "(5) " . __("Error"),
	CACTI_LOG_SEV_CRITICAL  => "(6) " . __("Critical"),
	CACTI_LOG_SEV_ALERT     => "(7) " . __("Alert"),
	CACTI_LOG_SEV_EMERGENCY	=> "(8) " . __("Emergency")
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
		LOG_LOCAL7 => "LOCAL7"
	);
}
$log_system_facility[LOG_USER] = "USER";

if (CACTI_SERVER_OS == "unix") {
	$log_syslog_facility = array(
		LOG_LOCAL0 => "LOCAL0",
		LOG_LOCAL1 => "LOCAL1",
		LOG_LOCAL2 => "LOCAL2",
		LOG_LOCAL3 => "LOCAL3",
		LOG_LOCAL4 => "LOCAL4",
		LOG_LOCAL5 => "LOCAL5",
		LOG_LOCAL6 => "LOCAL6",
		LOG_LOCAL7 => "LOCAL7"
	);
}
$log_syslog_facility[LOG_USER] = "USER";
