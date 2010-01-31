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

define("CHECKED", "on");
define("ACTION_NONE", "-1");

define("HOST_GROUPING_GRAPH_TEMPLATE", 1);
define("HOST_GROUPING_DATA_QUERY_INDEX", 2);

define("TREE_ORDERING_NONE", 1);
define("TREE_ORDERING_ALPHABETIC", 2);
define("TREE_ORDERING_NUMERIC", 3);
define("TREE_ORDERING_NATURAL", 4);

define("TREE_TYPE_TREE", 1);
define("TREE_TYPE_NODE", 2);

define("TREE_ITEM_TYPE_HEADER", 1);
define("TREE_ITEM_TYPE_GRAPH", 2);
define("TREE_ITEM_TYPE_DEVICE", 3);

define("RRDTOOL_PIPE_CHILD_READ", 0);
define("RRDTOOL_PIPE_CHILD_WRITE", 1);
define("RRDTOOL_PIPE_STDERR_WRITE", 2);

define("RRDTOOL_OUTPUT_NULL", 0);
define("RRDTOOL_OUTPUT_STDOUT", 1);
define("RRDTOOL_OUTPUT_STDERR", 2);
define("RRDTOOL_OUTPUT_GRAPH_DATA", 3);

define("RRD_VERSION_1_0",	"rrd-1.0.x");
define("RRD_VERSION_1_2",	"rrd-1.2.x");
define("RRD_VERSION_1_3",	"rrd-1.3.x");
define("RRD_VERSION_1_4",	"rrd-1.4.x");

define("RRD_FONT_RENDER_NORMAL", "normal");
define("RRD_FONT_RENDER_LIGHT","light");
define("RRD_FONT_RENDER_MONO", "mono");

define("RRD_GRAPH_RENDER_NORMAL", "normal");
define("RRD_GRAPH_RENDER_MONO", "mono");

define("COLORTAGS_GLOBAL",		1);
define("COLORTAGS_USER",		2);
define("COLORTAGS_TEMPLATE",	3);
define("COLORTAGS_UTG",			4);
define("COLORTAGS_TUG",			5);

define("RRD_LEGEND_POS_NORTH",	"north");
define("RRD_LEGEND_POS_SOUTH",	"south");
define("RRD_LEGEND_POS_WEST",	"west");
define("RRD_LEGEND_POS_EAST",	"east");

define("RRD_ALIGN_LEFT", 		"left");
define("RRD_ALIGN_RIGHT",		"right");
define("RRD_ALIGN_JUSTIFIED",	"justified");
define("RRD_ALIGN_CENTER",		"center");

define("RRD_LEGEND_DIR_TOPDOWN",	"topdown");
define("RRD_LEGEND_DIR_BOTTOMUP",	"bottomup");

define("DATA_QUERY_AUTOINDEX_NONE", 0);
define("DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME", 1);
define("DATA_QUERY_AUTOINDEX_INDEX_COUNT_CHANGE", 2);
define("DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION", 3);
define("DATA_QUERY_AUTOINDEX_VALUE_CHANGE", 4);

define("DATA_INPUT_TYPE_SCRIPT", 1);
define("DATA_INPUT_TYPE_SNMP", 2);
define("DATA_INPUT_TYPE_SNMP_QUERY", 3);
define("DATA_INPUT_TYPE_SCRIPT_QUERY", 4);
define("DATA_INPUT_TYPE_PHP_SCRIPT_SERVER", 5);
define("DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER", 6);

define("DATA_SOURCE_TYPE_GAUGE", 1);
define("DATA_SOURCE_TYPE_COUNTER", 2);
define("DATA_SOURCE_TYPE_DERIVE", 3);
define("DATA_SOURCE_TYPE_ABSOLUTE", 4);
define("DATA_SOURCE_TYPE_COMPUTE", 5);

define("GRAPH_TREE_VIEW", 1);
define("GRAPH_LIST_VIEW", 2);
define("GRAPH_PREVIEW_VIEW", 3);

define("GRAPH_ACTION_DELETE", "0");
define("GRAPH_ACTION_CHANGE_TEMPLATE", "1");
define("GRAPH_ACTION_DUPLICATE", "2");
define("GRAPH_ACTION_CONVERT_TO_TEMPLATE", "3");
define("GRAPH_ACTION_CHANGE_HOST", "4");
define("GRAPH_ACTION_REAPPLY_SUGGESTED_NAMES", "5");
define("GRAPH_ACTION_RESIZE", "6");
define("GRAPH_ACTION_ENABLE_EXPORT", "7");
define("GRAPH_ACTION_DISABLE_EXPORT", "8");

define("GRAPH_ALT_AUTOSCALE",			1);
define("GRAPH_ALT_AUTOSCALE_MIN",		2);
define("GRAPH_ALT_AUTOSCALE_MAX",		3);
define("GRAPH_ALT_AUTOSCALE_LIMITS",	4);

define("DEVICE_ACTION_DELETE", "0");
define("DEVICE_ACTION_ENABLE", "1");
define("DEVICE_ACTION_DISABLE", "2");
define("DEVICE_ACTION_CHANGE_SNMP_OPTIONS", "3");
define("DEVICE_ACTION_CLEAR_STATISTICS", "4");
define("DEVICE_ACTION_CHANGE_AVAILABILITY_OPTIONS", "5");
define("DEVICE_ACTION_CHANGE_POLLER", "6");
define("DEVICE_ACTION_CHANGE_SITE", "7");

define("DS_ACTION_DELETE", "0");
define("DS_ACTION_CHANGE_TEMPLATE", "1");
define("DS_ACTION_CHANGE_HOST", "2");
define("DS_ACTION_DUPLICATE", "3");
define("DS_ACTION_CONVERT_TO_TEMPLATE", "4");
define("DS_ACTION_ENABLE", "5");
define("DS_ACTION_DISABLE", "6");
define("DS_ACTION_REAPPLY_SUGGESTED_NAMES", "7");

define("GRAPH_ITEM_TYPE_COMMENT",		1);
define("GRAPH_ITEM_TYPE_HRULE",			2);
define("GRAPH_ITEM_TYPE_VRULE",			3);
define("GRAPH_ITEM_TYPE_LINE1",			4);
define("GRAPH_ITEM_TYPE_LINE2",			5);
define("GRAPH_ITEM_TYPE_LINE3",			6);
define("GRAPH_ITEM_TYPE_AREA",			7);
define("GRAPH_ITEM_TYPE_AREASTACK",		8);
define("GRAPH_ITEM_TYPE_GPRINT",		9);
define("GRAPH_ITEM_TYPE_LINESTACK",		10);
define("GRAPH_ITEM_TYPE_TICK",			11);
define("GRAPH_ITEM_TYPE_TEXTALIGN",		12);
define("GRAPH_ITEM_TYPE_LEGEND", 		98);
define("GRAPH_ITEM_TYPE_CUSTOM_LEGEND",	99);

define("IMAGE_TYPE_PNG", 1);
define("IMAGE_TYPE_GIF", 2);
define("IMAGE_TYPE_SVG", 3);

define("SNMP_AUTH_PROTOCOL_NONE", 	'');
define("SNMP_AUTH_PROTOCOL_MD5", 	'MD5');
define("SNMP_AUTH_PROTOCOL_SHA", 	'SHA');

define("SNMP_PRIV_PROTOCOL_NONE", 	'');
define("SNMP_PRIV_PROTOCOL_DES", 	'DES');
define("SNMP_PRIV_PROTOCOL_AES128", 'AES');

define("POLLER_ACTION_SNMP", 0);
define("POLLER_ACTION_SCRIPT", 1);
define("POLLER_ACTION_SCRIPT_PHP", 2);

define("POLLER_COMMAND_REINDEX", 1);
define("POLLER_COMMAND_RRDPURGE", 2);

define("POLLER_VERBOSITY_NONE", 1);
define("POLLER_VERBOSITY_LOW", 2);
define("POLLER_VERBOSITY_MEDIUM", 3);
define("POLLER_VERBOSITY_HIGH", 4);
define("POLLER_VERBOSITY_DEBUG", 5);
define("POLLER_VERBOSITY_DEVDBG", 6);

define("AVAIL_NONE", 0);
define("AVAIL_SNMP_AND_PING", 1);
define("AVAIL_SNMP", 2);
define("AVAIL_PING", 3);
define("AVAIL_SNMP_OR_PING", 4);

define("PING_ICMP", 1);
define("PING_UDP", 2);
define("PING_TCP", 3);

define("DEVICE_UNKNOWN", 0);
define("DEVICE_DOWN", 1);
define("DEVICE_RECOVERING", 2);
define("DEVICE_UP", 3);
define("DEVICE_ERROR", 4);

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

# graph timeshifts
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

# weekdays according to date("w") builtin function
define("WD_SUNDAY", 	date("w",strtotime("sunday")));
define("WD_MONDAY", 	date("w",strtotime("monday")));
define("WD_TUESDAY", 	date("w",strtotime("tuesday")));
define("WD_WEDNESDAY", 	date("w",strtotime("wednesday")));
define("WD_THURSDAY", 	date("w",strtotime("thursday")));
define("WD_FRIDAY", 	date("w",strtotime("friday")));
define("WD_SATURDAY", 	date("w",strtotime("saturday")));

define("GD_MO_D_Y", 0);
define("GD_MN_D_Y", 1);
define("GD_D_MO_Y", 2);
define("GD_D_MN_Y", 3);
define("GD_Y_MO_D", 4);
define("GD_Y_MN_D", 5);

define("GDC_HYPHEN", 0);
define("GDC_SLASH", 1);
define("GDC_DOT", 2);

define("SNMP_POLLER", 0);
define("SNMP_CMDPHP", 1);
define("SNMP_WEBUI", 2);

define("PERM_GRAPHS", 1);
define("PERM_TREES", 2);
define("PERM_DEVICES", 3);
define("PERM_GRAPH_TEMPLATES", 4);

define("POLICY_ALLOW", 1);
define("POLICY_DENY", 2);

define('OPER_MODE_NATIVE', 0);
define('OPER_MODE_RESKIN', 1);
define('OPER_MODE_IFRAME_NONAV', 2);

define("CHARS_PER_TIER", 3);
define("MAX_TREE_DEPTH", 30);

define("SORT_TYPE_TREE", 1);
define("SORT_TYPE_TREE_ITEM", 2);
