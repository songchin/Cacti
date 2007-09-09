<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

define("RRDTOOL_PIPE_CHILD_READ", 0);
define("RRDTOOL_PIPE_CHILD_WRITE", 1);
define("RRDTOOL_PIPE_STDERR_WRITE", 2);

define("RRDTOOL_OUTPUT_NULL", 0);
define("RRDTOOL_OUTPUT_STDOUT", 1);
define("RRDTOOL_OUTPUT_STDERR", 2);
define("RRDTOOL_OUTPUT_GRAPH_DATA", 3);

define("POLLER_ACTION_SNMP", 0);
define("POLLER_ACTION_SCRIPT", 1);
define("POLLER_ACTION_SCRIPT_PHP", 2);
define("POLLER_ACTION_INTERNAL", 3);

define("POLLER_COMMAND_REINDEX", 1);
define("POLLER_COMMAND_RRDPURGE", 2);

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
define("GT_DAY_SHIFT", 20);
define("GT_THIS_DAY", 21);
define("GT_THIS_WEEK", 22);
define("GT_THIS_MONTH", 23);
define("GT_THIS_YEAR", 24);
define("GT_PREV_DAY", 25);
define("GT_PREV_WEEK", 26);
define("GT_PREV_MONTH", 27);
define("GT_PREV_YEAR", 28);

define("DEFAULT_TIMESPAN", 86400);

/* graph timeshifts */
define("GTS_CUSTOM", 0);
define("GTS_HALF_HOUR", 1);
define("GTS_1_HOUR", 2);
define("GTS_2_HOURS", 3);
define("GTS_4_HOURS", 4);
define("GTS_6_HOURS", 5);
define("GTS_12_HOURS", 6);
define("GTS_1_DAY", 7);
define("GTS_2_DAYS", 8);
define("GTS_3_DAYS", 9);
define("GTS_4_DAYS", 10);
define("GTS_1_WEEK", 11);
define("GTS_2_WEEKS", 12);
define("GTS_1_MONTH", 13);
define("GTS_2_MONTHS", 14);
define("GTS_3_MONTHS", 15);
define("GTS_4_MONTHS", 16);
define("GTS_6_MONTHS", 17);
define("GTS_1_YEAR", 18);
define("GTS_2_YEARS", 19);

define("DEFAULT_TIMESHIFT", 86400);

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

define("HTML_BOX_SEARCH_NONE", 1);
define("HTML_BOX_SEARCH_ACTIVE", 2);
define("HTML_BOX_SEARCH_INACTIVE", 3);
define("HTML_BOX_SEARCH_NO_ICON", 4);

define("FORM_VALIDATE_CHECKBOX", chr(22));

define("DB_TYPE_STRING", 1);
define("DB_TYPE_INTEGER", 2);
define("DB_TYPE_NULL", 3);
define("DB_TYPE_FUNC_NOW", 4);
define("DB_TYPE_FUNC_MD5", 5);
define("DB_TYPE_HTML_CHECKBOX", 6);
define("DB_TYPE_BLOB", 7);
define("DB_TYPE_NUMBER", 8);


?>
