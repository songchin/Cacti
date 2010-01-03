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

define("CACTI_LOG_SEV_EMERGENCY", 8);
define("CACTI_LOG_SEV_ALERT",     7);
define("CACTI_LOG_SEV_CRITICAL",  6);
define("CACTI_LOG_SEV_ERROR",     5);
define("CACTI_LOG_SEV_WARNING",   4);
define("CACTI_LOG_SEV_NOTICE",    3);
define("CACTI_LOG_SEV_INFO",      2);
define("CACTI_LOG_SEV_DEBUG",     1);
define("CACTI_LOG_SEV_DEV",       0);

define("CACTI_LOG_FAC_SYSTEM",    0);
define("CACTI_LOG_FAC_POLLER",    1);
define("CACTI_LOG_FAC_CMDPHP",    2);
define("CACTI_LOG_FAC_SPINE",     3);
define("CACTI_LOG_FAC_SCPTSVR",   4);
define("CACTI_LOG_FAC_AUTH",      5);
define("CACTI_LOG_FAC_INTERFACE", 6);
define("CACTI_LOG_FAC_EXPORT",    7);
define("CACTI_LOG_FAC_EVENT",     8);
define("CACTI_LOG_FAC_PLUGIN",    9);

define("CACTI_LOG_CLEANUP_NONE",     0);
define("CACTI_LOG_CLEANUP_ASNEEDED", 1);
define("CACTI_LOG_CLEANUP_DAYSOLD",  2);
define("CACTI_LOG_CLEANUP_STOPLOG",  3);

/* RFC3164 spec constants */
define("SYSLOG_FAC_LOCAL0", 16);
define("SYSLOG_FAC_LOCAL1", 17);
define("SYSLOG_FAC_LOCAL2", 18);
define("SYSLOG_FAC_LOCAL3", 19);
define("SYSLOG_FAC_LOCAL4", 20);
define("SYSLOG_FAC_LOCAL5", 21);
define("SYSLOG_FAC_LOCAL6", 22);
define("SYSLOG_FAC_LOCAL7", 23);
define("SYSLOG_FAC_USER",   1);

define("SYSLOG_LEVEL_EMERG",   7);
define("SYSLOG_LEVEL_ALERT",   6);
define("SYSLOG_LEVEL_CRIT",    5);
define("SYSLOG_LEVEL_ERR",     4);
define("SYSLOG_LEVEL_WARNING", 3);
define("SYSLOG_LEVEL_NOTICE",  2);
define("SYSLOG_LEVEL_INFO",    1);
define("SYSLOG_LEVEL_DEBUG",   0);

?>
