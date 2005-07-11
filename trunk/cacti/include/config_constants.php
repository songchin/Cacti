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

define("HOST_GROUPING_GRAPH_TEMPLATE", 1);
define("HOST_GROUPING_DATA_QUERY_INDEX", 2);

define("TREE_ORDERING_NONE", 1);
define("TREE_ORDERING_ALPHABETIC", 2);
define("TREE_ORDERING_NUMERIC", 3);

define("TREE_ITEM_TYPE_HEADER", 1);
define("TREE_ITEM_TYPE_GRAPH", 2);
define("TREE_ITEM_TYPE_HOST", 3);

define("RRDTOOL_PIPE_CHILD_READ", 0);
define("RRDTOOL_PIPE_CHILD_WRITE", 1);
define("RRDTOOL_PIPE_STDERR_WRITE", 2);

define("RRDTOOL_OUTPUT_NULL", 0);
define("RRDTOOL_OUTPUT_STDOUT", 1);
define("RRDTOOL_OUTPUT_STDERR", 2);
define("RRDTOOL_OUTPUT_GRAPH_DATA", 3);

define("SCRIPT_FIELD_INPUT_CUSTOM", 1);
define("SCRIPT_FIELD_INPUT_DEVICE", 2);

define("SCRIPT_INPUT_TYPE_SCRIPT", 1);
define("SCRIPT_INPUT_TYPE_PHP_SCRIPT_SERVER", 2);

define("POLLER_ACTION_SNMP", 0);
define("POLLER_ACTION_SCRIPT", 1);
define("POLLER_ACTION_SCRIPT_PHP", 2);
define("POLLER_ACTION_INTERNAL", 3);

define("POLLER_COMMAND_REINDEX", 1);

define("POLLER_VERBOSITY_NONE", 1);
define("POLLER_VERBOSITY_LOW", 2);
define("POLLER_VERBOSITY_MEDIUM", 3);
define("POLLER_VERBOSITY_HIGH", 4);
define("POLLER_VERBOSITY_DEBUG", 5);

define("POLLER_UNKNOWN", 0);
define("POLLER_DOWN", 1);
define("POLLER_RECOVERING", 2);
define("POLLER_UP", 3);

define("AVAIL_SNMP_AND_PING", 1);
define("AVAIL_SNMP", 2);
define("AVAIL_PING", 3);
define("AVAIL_NONE",4);

define("PING_ICMP", 1);
define("PING_UDP", 2);
define("PING_NONE", 3);

define("HOST_UNKNOWN", 0);
define("HOST_DOWN", 1);
define("HOST_RECOVERING", 2);
define("HOST_UP", 3);

define("GT_CUSTOM", 0);
define("GT_LAST_HALF_HOUR", 1);
define("GT_LAST_HOUR", 2);
define("GT_LAST_2_HOURS", 3);
define("GT_LAST_4_HOURS", 4);
define("GT_LAST_6_HOURS", 5);
define("GT_LAST_12_HOURS", 6);
define("GT_LAST_DAY", 7);
define("GT_LAST_2_DAYS", 8);
define("GT_LAST_3_DAYS", 9);
define("GT_LAST_4_DAYS", 10);
define("GT_LAST_WEEK", 11);
define("GT_LAST_2_WEEKS", 12);
define("GT_LAST_MONTH", 13);
define("GT_LAST_2_MONTHS", 14);
define("GT_LAST_3_MONTHS", 15);
define("GT_LAST_4_MONTHS", 16);
define("GT_LAST_6_MONTHS", 17);
define("GT_LAST_YEAR", 18);
define("GT_LAST_2_YEARS", 19);

define("DEFAULT_TIMESPAN", 86400);

define("GD_MO_D_Y", 0);
define("GD_MN_D_Y", 1);
define("GD_D_MO_Y", 2);
define("GD_D_MN_Y", 3);
define("GD_Y_MO_D", 4);
define("GD_Y_MN_D", 5);

define("GDC_HYPHEN", 0);
define("GDC_SLASH", 1);

define("SNMP_POLLER", 0);
define("SNMP_CMDPHP", 1);
define("SNMP_WEBUI", 2);

define("SNMPV3_PP_DES", 0);
define("SNMPV3_PP_AES128", 1);
define("SNMPV3_PP_AES192", 2);
define("SNMPV3_PP_AES256", 3);
define("SNMPV3_PP_AES", 4);

define("SNMPV3_AP_MD5", 0);
define("SNMPV3_AP_SHA", 1);

define("SEV_EMERGENCY", 7);
define("SEV_ALERT", 6);
define("SEV_CRITICAL", 5);
define("SEV_ERROR", 4);
define("SEV_WARNING", 3);
define("SEV_NOTICE", 2);
define("SEV_INFO", 1);
define("SEV_DEBUG", 0);
define("SEV_DEV", -1);

define("FACIL_UNKNOWN",0);
define("FACIL_POLLER",1);
define("FACIL_CMDPHP",2);
define("FACIL_CACTID",3);
define("FACIL_SCPTSVR",4);
define("FACIL_AUTH",5);
define("FACIL_WEBUI",6);
define("FACIL_EXPORT",7);
define("FACIL_SMTP",8);

define("SYSLOG_CACTI",1);
define("SYSLOG_BOTH",2);
define("SYSLOG_SYSTEM",3);

define("SYSLOG_MNG_NONE",0);
define("SYSLOG_MNG_ASNEEDED",1);
define("SYSLOG_MNG_DAYSOLD",2);
define("SYSLOG_MNG_STOPLOG",3);

define("FORM_VALIDATE_CHECKBOX", chr(22));

define("DB_TYPE_STRING", 1);
define("DB_TYPE_NUMBER", 2);
define("DB_TYPE_NULL", 3);
define("DB_TYPE_FUNC_NOW", 4);
define("DB_TYPE_FUNC_MD5", 5);
define("DB_TYPE_HTML_CHECKBOX", 6);

/* Define syslog variables for php */
define_syslog_variables();

?>
