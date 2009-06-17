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

$graph_actions = array(
	GRAPH_ACTION_DELETE => __("Delete"),
	GRAPH_ACTION_CHANGE_TEMPLATE => __("Change Graph Template"),
	GRAPH_ACTION_DUPLICATE => __("Duplicate"),
	GRAPH_ACTION_CONVERT_TO_TEMPLATE => __("Convert to Graph Template"),
	GRAPH_ACTION_CHANGE_HOST => __("Change Host"),
	GRAPH_ACTION_REAPPLY_SUGGESTED_NAMES => __("Reapply Suggested Names"),
	GRAPH_ACTION_RESIZE => __("Resize Graphs"),
	GRAPH_ACTION_ENABLE_EXPORT => __("Enable Graph Export"),
	GRAPH_ACTION_DISABLE_EXPORT => __("Disable Graph Export"),
	);

$device_actions = array(
	DEVICE_ACTION_DELETE => __("Delete"),
	DEVICE_ACTION_ENABLE => __("Enable"),
	DEVICE_ACTION_DISABLE => __("Disable"),
	DEVICE_ACTION_CHANGE_SNMP_OPTIONS => __("Change SNMP Options"),
	DEVICE_ACTION_CLEAR_STATISTICS => __("Clear Statistics"),
	DEVICE_ACTION_CHANGE_AVAILABILITY_OPTIONS => __("Change Availability Options"),
	DEVICE_ACTION_CHANGE_POLLER => __("Change Poller"),
	DEVICE_ACTION_CHANGE_SITE => __("Change Site"),
	);

$ds_actions = array(
	DS_ACTION_DELETE => __("Delete"),
	DS_ACTION_CHANGE_TEMPLATE => __("Change Data Template"),
	DS_ACTION_DUPLICATE => __("Duplicate"),
	DS_ACTION_CONVERT_TO_TEMPLATE => __("Convert to Data Template"),
	DS_ACTION_CHANGE_HOST => __("Change Host"),
	DS_ACTION_REAPPLY_SUGGESTED_NAMES => __("Reapply Suggested Names"),
	DS_ACTION_ENABLE => __("Enable"),
	DS_ACTION_DISABLE => __("Disable"),
	);

$messages = array(
	1  => array(
		"message" => __('Save Successful.'),
		"type" => "info"),
	2  => array(
		"message" => __('Save Failed.'),
		"type" => "error"),
	3  => array(
		"message" => __('Save Failed: Field Input Error (Check Red Fields).'),
		"type" => "error"),
	4  => array(
		"message" => __('Passwords do not match, please retype.'),
		"type" => "error"),
	5  => array(
		"message" => __('You must select at least one field.'),
		"type" => "error"),
	6  => array(
		"message" => __('You must have built in user authentication turned on to use this feature.'),
		"type" => "error"),
	7  => array(
		"message" => __('XML parse error.'),
		"type" => "error"),
	12 => array(
		"message" => __('Username already in use.'),
		"type" => "error"),
	15 => array(
		"message" => __('XML: Cacti version does not exist.'),
		"type" => "error"),
	16 => array(
		"message" => __('XML: Hash version does not exist.'),
		"type" => "error"),
	17 => array(
		"message" => __('XML: Generated with a newer version of Cacti.'),
		"type" => "error"),
	18 => array(
		"message" => __('XML: Cannot locate type code.'),
		"type" => "error"),
	19 => array(
		"message" => __('Username already exists.'),
		"type" => "error"),
	20 => array(
		"message" => __('Username change not permitted for designated template or guest user.'),
		"type" => "error"),
	21 => array(
		"message" => __('User delete not permitted for designated template or guest user.'),
		"type" => "error"),
	22 => array(
		"message" => __('User delete not permitted for designated graph export user.'),
		"type" => "error"),
	);

$cdef_operators = array(1 =>
	"+",
	"-",
	"*",
	"/",
	"%");

$cdef_functions = array(1 =>
	"SIN",
	"COS",
	"LOG",
	"EXP",
	"FLOOR",
	"CEIL",
	"LT",
	"LE",
	"GT",
	"GE",
	"EQ",
	"IF",
	"MIN",
	"MAX",
	"LIMIT",
	"DUP",
	"EXC",
	"POP",
	"UN",
	"UNKN",
	"PREV",
	"INF",
	"NEGINF",
	"NOW",
	"TIME",
	"LTIME");

$input_types = array(
	DATA_INPUT_TYPE_SNMP => "SNMP", // Action 0:
	DATA_INPUT_TYPE_SNMP_QUERY => "SNMP Query",
	DATA_INPUT_TYPE_SCRIPT => "Script/Command",  // Action 1:
	DATA_INPUT_TYPE_SCRIPT_QUERY => "Script Query", // Action 1:
	DATA_INPUT_TYPE_PHP_SCRIPT_SERVER => "Script - Script Server (PHP)",
	DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER => "Script Query - Script Server",
	);

$reindex_types = array(
	DATA_QUERY_AUTOINDEX_NONE => __("None"),
	DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME => __("Uptime Goes Backwards"),
	DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE => __("Index Count Changed"),
	DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION => __("Verify All Fields"),
	);

$snmp_query_field_actions = array(1 =>
	__("SNMP Field Name (Dropdown)"),
	__("SNMP Field Value (From User)"),
	__("SNMP Output Type (Dropdown)"),
	);

$consolidation_functions = array(1 =>
	"AVERAGE",
	"MIN",
	"MAX",
	"LAST");

$data_source_types = array(
	DATA_SOURCE_TYPE_GAUGE		=> "GAUGE",
	DATA_SOURCE_TYPE_COUNTER	=> "COUNTER",
	DATA_SOURCE_TYPE_DERIVE		=> "DERIVE",
	DATA_SOURCE_TYPE_ABSOLUTE	=> "ABSOLUTE",
	DATA_SOURCE_TYPE_COMPUTE	=> "COMPUTE");

$graph_item_types = array(
	GRAPH_ITEM_TYPE_COMMENT => "COMMENT",
	GRAPH_ITEM_TYPE_HRULE   => "HRULE",
	GRAPH_ITEM_TYPE_VRULE   => "VRULE",
	GRAPH_ITEM_TYPE_LINE1   => "LINE1",
	GRAPH_ITEM_TYPE_LINE2   => "LINE2",
	GRAPH_ITEM_TYPE_LINE3   => "LINE3",
	GRAPH_ITEM_TYPE_AREA    => "AREA",
	GRAPH_ITEM_TYPE_STACK   => "STACK",
	GRAPH_ITEM_TYPE_GPRINT  => "GPRINT",
	GRAPH_ITEM_TYPE_LEGEND  => "LEGEND",
	);

$image_types = array(1 =>
	"PNG",
	"GIF",
	"SVG");

$snmp_versions = array(0 =>
	__("Not In Use"),
	__("Version 1"),
	__("Version 2"),
	__("Version 3"),
	);

$snmp_auth_protocols = array(
	"MD5" => __("MD5 (default)"),
	"SHA" => __("SHA"),
	);

$snmp_priv_protocols = array(
	"[None]" => __("[None]"),
	"DES" => __("DES (default)"),
	"AES128" => __("AES"),
	);

$logfile_options = array(1 =>
	__("Logfile Only"),
	__("Logfile and Syslog/Eventlog"),
	__("Syslog/Eventlog Only"),
	);

$availability_options = array(
	AVAIL_NONE => __("None"),
	AVAIL_SNMP_AND_PING => __("Ping and SNMP"),
	AVAIL_SNMP_OR_PING => __("Ping or SNMP"),
	AVAIL_SNMP => __("SNMP"),
	AVAIL_PING => __("Ping"),
	);

$ping_methods = array(
	PING_ICMP => __("ICMP Ping"),
	PING_TCP => __("TCP Ping"),
	PING_UDP => __("UDP Ping"),
	);

$logfile_verbosity = array(
	POLLER_VERBOSITY_NONE 	=> __("NONE - Syslog Only if Selected"),
	POLLER_VERBOSITY_LOW 	=> __("LOW - Statistics and Errors"),
	POLLER_VERBOSITY_MEDIUM => __("MEDIUM - Statistics, Errors and Results"),
	POLLER_VERBOSITY_HIGH 	=> __("HIGH - Statistics, Errors, Results and Major I/O Events"),
	POLLER_VERBOSITY_DEBUG 	=> __("DEBUG - Statistics, Errors, Results, I/O and Program Flow"),
	POLLER_VERBOSITY_DEVDBG => __("DEVEL - Developer DEBUG Level"),
	);

$poller_options = array(1 =>
	"cmd.php",
	"spine");

$poller_intervals = array(
	10 => __("Every 10 Seconds"),
	15 => __("Every 15 Seconds"),
	20 => __("Every 20 Seconds"),
	30 => __("Every 30 Seconds"),
	60 => __("Every Minute"),
	300 => __("Every 5 Minutes"),
	);

$cron_intervals = array(
	60 => __("Every Minute"),
	300 => __("Every 5 Minutes"),
	);

$registered_cacti_names = array(
	"path_cacti");

$graph_views = array(1 =>
	__("Tree View"),
	__("List View"),
	__("Preview View"),
	);

$graph_tree_views = array(1 =>
	__("Single Pane"),
	__("Dual Pane"),
	);

$auth_methods = array(
	0 => __("None"),
	1 => __("Builtin Authentication"),
	2 => __("Web Basic Authentication"),
	);
if (function_exists("ldap_connect")) {
	$auth_methods[3] = __("LDAP Authentication");
}

$auth_realms = array(0 =>
	__("Local"),
	__("LDAP"),
	__("Web Basic"),
	);

$ldap_versions = array(
	2 => __("Version 2"),
	3 => __("Version 3"),
	);

$ldap_encryption = array(
	0 => __("None"),
	1 => __("SSL"),
	2 => __("TLS"),
	);

$ldap_modes = array(
	0 => __("No Searching"),
	1 => __("Anonymous Searching"),
	2 => __("Specific Searching"),
	);

$snmp_implimentations = array(
	"ucd-snmp" => __("UCD-SNMP 4.x"),
	"net-snmp" => __("NET-SNMP 5.x"),
	);

$rrdtool_versions = array(
	"rrd-1.0.x" => "RRDTool 1.0.x",
	"rrd-1.2.x" => "RRDTool 1.2.x",
	"rrd-1.3.x" => "RRDTool 1.3.x");

$i18n_modes = array(
	0 => __("disabled"),
	1 => __("enabled"),
	2 => __("enabled (strict mode)"),
	);

$cdef_item_types = array(
	1 => __("Function"),
	2 => __("Operator"),
	4 => __("Special Data Source"),
	5 => __("Another CDEF"),
	6 => __("Custom String"),
	);

$graph_color_alpha = array(
		"00" => "  0%",
		"19" => " 10%",
		"33" => " 20%",
		"4C" => " 30%",
		"66" => " 40%",
		"7F" => " 50%",
		"99" => " 60%",
		"B2" => " 70%",
		"CC" => " 80%",
		"E5" => " 90%",
		"FF" => "100%"
		);

$tree_sort_types = array(
	TREE_ORDERING_NONE => __("Manual Ordering (No Sorting)"),
	TREE_ORDERING_ALPHABETIC => __("Alphabetic Ordering"),
	TREE_ORDERING_NATURAL => __("Natural Ordering"),
	TREE_ORDERING_NUMERIC => __("Numeric Ordering"),
	);

$tree_item_types = array(
	TREE_ITEM_TYPE_HEADER => __("Header"),
	TREE_ITEM_TYPE_GRAPH => __("Graph"),
	TREE_ITEM_TYPE_HOST => __("Host"),
	);

$host_group_types = array(
	HOST_GROUPING_GRAPH_TEMPLATE => __("Graph Template"),
	HOST_GROUPING_DATA_QUERY_INDEX => __("Data Query Index"),
	);

$custom_data_source_types = array(
	"CURRENT_DATA_SOURCE"				=> __("Current Graph Item Data Source"),
	"ALL_DATA_SOURCES_NODUPS"			=> __("All Data Sources (Don't Include Duplicates)"),
	"ALL_DATA_SOURCES_DUPS"				=> __("All Data Sources (Include Duplicates)"),
	"SIMILAR_DATA_SOURCES_NODUPS"		=> __("All Similar Data Sources (Don't Include Duplicates)"),
	"SIMILAR_DATA_SOURCES_DUPS"			=> __("All Similar Data Sources (Include Duplicates)"),
	"CURRENT_DS_MINIMUM_VALUE"			=> __("Current Data Source Item: Minimum Value"),
	"CURRENT_DS_MAXIMUM_VALUE"			=> __("Current Data Source Item: Maximum Value"),
	"CURRENT_GRAPH_MINIMUM_VALUE"		=> __("Graph: Lower Limit"),
	"CURRENT_GRAPH_MAXIMUM_VALUE"		=> __("Graph: Upper Limit"),
	"COUNT_ALL_DS_NODUPS"				=> __("Count of All Data Sources (Don't Include Duplicates)"),
	"COUNT_ALL_DS_DUPS"					=> __("Count of All Data Sources (Include Duplicates)"),
	"COUNT_SIMILAR_DS_NODUPS"			=> __("Count of All Similar Data Sources (Don't Include Duplicates)"),
	"COUNT_SIMILAR_DS_DUPS"		 		=> __("Count of All Similar Data Sources (Include Duplicates)"),
	);

$menu = array(
	"Management" => array(
		"graphs.php" => __("Graph Management"),
		"tree.php" => __("Graph Trees"),
		"data_sources.php" => __("Data Sources"),
		"sites.php" => __("Sites"),
		"host.php" => __("Devices"),
		"pollers.php" => __("Pollers"),
		),
	"Data Collection" => array(
		"data_queries.php" => __("Data Queries"),
		"data_input.php" => __("Data Input Methods"),
		),
	"Templates" => array(
		"graph_templates.php" => __("Graph Templates"),
		"host_templates.php" => __("Host Templates"),
		"data_templates.php" => __("Data Templates"),
		),
	"Presets" => array(
		"cdef.php" => __("CDEFs"),
		"color.php" => __("Colors"),
		"gprint_presets.php" => __("GPRINT Presets"),
		"rra.php" => __("RRAs"),
		),
	"Import/Export" => array(
		"templates_import.php" => __("Import Templates"),
		"templates_export.php" => __("Export Templates"),
		),
	"Configuration"  => array(
		"settings.php" => __("Settings"),
		),
	"Utilities" => array(
		"utilities.php" => __("System Utilities"),
		"user_admin.php" => __("User Management"),
		"logout.php" => __("Logout User"),
	));

$log_tail_lines = array(
	-1	=> __("All Lines"),
	10 => __("10 Lines"),
	15 => __("15 Lines"),
	20 => __("20 Lines"),
	50 => __("50 Lines"),
	100 => __("100 Lines"),
	200 => __("200 Lines"),
	500 => __("500 Lines"),
	1000 => __("1000 Lines"),
	2000 => __("2000 Lines"),
	3000 => __("3000 Lines"),
	5000 => __("5000 Lines"),
	10000 => __("10000 Lines"),
	);

$item_rows = array(
	10   => __("10 Rows"),
	15   => __("15 Rows"),
	20   => __("20 Rows"),
	25   => __("25 Rows"),
	30   => __("30 Rows"),
	40   => __("40 Rows"),
	50   => __("50 Rows"),
	100  => __("100 Rows"),
	250  => __("250 Rows"),
	500  => __("500 Rows"),
	1000 => __("1000 Rows"),
	2000 => __("2000 Rows"),
	5000 => __("5000 Rows"),
	);

$graphs_per_page = array(
	4    => __("4 Graphs"),
	6    => __("6 Graphs"),
	8    => __("8 Graphs"),
	10   => __("10 Graphs"),
	14   => __("14 Graphs"),
	20   => __("20 Graphs"),
	24   => __("24 Graphs"),
	30   => __("30 Graphs"),
	40   => __("40 Graphs"),
	50   => __("50 Graphs"),
	);

$page_refresh_interval = array(
	5 => __("5 Seconds"),
	10 => __("10 Seconds"),
	20 => __("20 Seconds"),
	30 => __("30 Seconds"),
	60 => __("1 Minute"),
	300 => __("5 Minutes"),
	600 => __("10 Minutes"),
	9999999 => __("Never"),
	);

$user_auth_realms = array(
	1 => __("User Administration"),
	2 => __("Data Input"),
	3 => __("Update Data Sources"),
	4 => __("Update Graph Trees"),
	5 => __("Update Graphs"),
	7 => __("View Graphs"),
	8 => __("Console Access"),
	9 => __("Update Round Robin Archives"),
	10 => __("Update Graph Templates"),
	11 => __("Update Data Templates"),
	12 => __("Update Host Templates"),
	13 => __("Data Queries"),
	14 => __("Update CDEF's"),
	15 => __("Global Settings"),
	16 => __("Export Data"),
	17 => __("Import Data"),
	);

$user_auth_realm_filenames = array(
	"about.php" => 8,
	"cdef.php" => 14,
	"cdef.ajax.php" => 14,
	"color.php" => 5,
	"data_input.php" => 2,
	"data_sources.php" => 3,
	"data_templates.php" => 11,
	"gprint_presets.php" => 5,
	"graph.php" => 7,
	"graph_image.php" => 7,
	"graph_xport.php" => 7,
	"graph_settings.php" => 7,
	"graph_templates.php" => 10,
	"graph_templates_inputs.php" => 10,
	"graph_templates_items.php" => 10,
	"graph_templates_item.ajax.php" => 10,
	"graph_view.php" => 7,
	"graphs.php" => 5,
	"graphs_items.php" => 5,
	"graphs_item.ajax.php" => 5,
	"graphs_new.php" => 5,
	"host.php" => 3,
	"sites.php" => 3,
	"pollers.php" => 3,
	"host_templates.php" => 12,
	"index.php" => 8,
	"rra.php" => 9,
	"settings.php" => 15,
	"data_queries.php" => 13,
	"data_query_dt_sv.ajax.php" => 13,
	"data_query_gt_sv.ajax.php" => 13,
	"templates_export.php" => 16,
	"templates_import.php" => 17,
	"tree.php" => 4,
	"user_admin.php" => 1,
	"utilities.php" => 15,
	"smtp_servers.php" => 8,
	"email_templates.php" => 8,
	"event_queue.php" => 8,
	"smtp_queue.php" => 8,
	"logout.php" => 7,
	);

$hash_type_codes = array(
	"round_robin_archive" => "15",
	"cdef" => "05",
	"cdef_item" => "14",
	"gprint_preset" => "06",
	"data_input_method" => "03",
	"data_input_field" => "07",
	"data_template" => "01",
	"data_template_item" => "08",
	"graph_template" => "00",
	"graph_template_item" => "10",
	"graph_template_input" => "09",
	"data_query" => "04",
	"data_query_graph" => "11",
	"data_query_sv_graph" => "12",
	"data_query_sv_data_source" => "13",
	"host_template" => "02"
	);

$hash_version_codes = array(
	"0.8.4"  => "0000",
	"0.8.5"  => "0001",
	"0.8.5a" => "0002",
	"0.8.6"  => "0003",
	"0.8.6a" => "0004",
	"0.8.6b" => "0005",
	"0.8.6c" => "0006",
	"0.8.6d" => "0007",
	"0.8.6e" => "0008",
	"0.8.6f" => "0009",
	"0.8.6g" => "0010",
	"0.8.6h" => "0011",
	"0.8.6i" => "0012",
	"0.8.6j" => "0013",
	"0.8.7"  => "0014",
	"0.8.7a" => "0015",
	"0.8.7b" => "0016",
	"0.8.7c" => "0017"
	);

$hash_type_names = array(
	"cdef" => "CDEF",
	"cdef_item" => "CDEF Item",
	"gprint_preset" => "GPRINT Preset",
	"data_input_method" => "Data Input Method",
	"data_input_field" => "Data Input Field",
	"data_template" => "Data Template",
	"data_template_item" => "Data Template Item",
	"graph_template" => "Graph Template",
	"graph_template_item" => "Graph Template Item",
	"graph_template_input" => "Graph Template Input",
	"data_query" => "Data Query",
	"host_template" => "Host Template",
	"round_robin_archive" => "Round Robin Archive"
	);

$host_struc = array(
	"host_template_id",
	"description",
	"hostname",
	"notes",
	"snmp_community",
	"snmp_version",
	"snmp_username",
	"snmp_password",
	"snmp_auth_protocol",
	"snmp_priv_passphrase",
	"snmp_priv_protocol",
	"snmp_context",
	"snmp_port",
	"snmp_timeout",
	"max_oids",
	"availability_method",
	"ping_method",
	"ping_port",
	"ping_timeout",
	"ping_retries",
	"disabled",
	"status",
	"status_event_count",
	"status_fail_date",
	"status_rec_date",
	"status_last_error",
	"min_time",
	"max_time",
	"cur_time",
	"avg_time",
	"total_polls",
	"failed_polls",
	"availability"
	);

$graph_timespans = array(
	GT_LAST_HALF_HOUR 	=> __("Last Half Hour"),
	GT_LAST_HOUR 		=> __("Last Hour"),
	GT_LAST_2_HOURS 	=> __("Last 2 Hours"),
	GT_LAST_4_HOURS 	=> __("Last 4 Hours"),
	GT_LAST_6_HOURS 	=> __("Last 6 Hours"),
	GT_LAST_12_HOURS 	=> __("Last 12 Hours"),
	GT_LAST_DAY 		=> __("Last Day"),
	GT_LAST_2_DAYS 		=> __("Last 2 Days"),
	GT_LAST_3_DAYS 		=> __("Last 3 Days"),
	GT_LAST_4_DAYS 		=> __("Last 4 Days"),
	GT_LAST_WEEK 		=> __("Last Week"),
	GT_LAST_2_WEEKS 	=> __("Last 2 Weeks"),
	GT_LAST_MONTH 		=> __("Last Month"),
	GT_LAST_2_MONTHS 	=> __("Last 2 Months"),
	GT_LAST_3_MONTHS 	=> __("Last 3 Months"),
	GT_LAST_4_MONTHS 	=> __("Last 4 Months"),
	GT_LAST_6_MONTHS 	=> __("Last 6 Months"),
	GT_LAST_YEAR 		=> __("Last Year"),
	GT_LAST_2_YEARS 	=> __("Last 2 Years"),
	GT_DAY_SHIFT 		=> __("Day Shift"),
	GT_THIS_DAY 		=> __("This Day"),
	GT_THIS_WEEK 		=> __("This Week"),
	GT_THIS_MONTH 		=> __("This Month"),
	GT_THIS_YEAR 		=> __("This Year"),
	GT_PREV_DAY 		=> __("Previous Day"),
	GT_PREV_WEEK 		=> __("Previous Week"),
	GT_PREV_MONTH 		=> __("Previous Month"),
	GT_PREV_YEAR 		=> __("Previous Year"),
	);

$graph_timeshifts = array(
	GTS_HALF_HOUR 	=> __("30 Min"),
	GTS_1_HOUR 		=> __("1 Hour"),
	GTS_2_HOURS 	=> __("2 Hours"),
	GTS_4_HOURS 	=> __("4 Hours"),
	GTS_6_HOURS 	=> __("6 Hours"),
	GTS_12_HOURS 	=> __("12 Hours"),
	GTS_1_DAY 		=> __("1 Day"),
	GTS_2_DAYS 		=> __("2 Days"),
	GTS_3_DAYS 		=> __("3 Days"),
	GTS_4_DAYS 		=> __("4 Days"),
	GTS_1_WEEK 		=> __("1 Week"),
	GTS_2_WEEKS 	=> __("2 Weeks"),
	GTS_1_MONTH 	=> __("1 Month"),
	GTS_2_MONTHS 	=> __("2 Months"),
	GTS_3_MONTHS 	=> __("3 Months"),
	GTS_4_MONTHS 	=> __("4 Months"),
	GTS_6_MONTHS 	=> __("6 Months"),
	GTS_1_YEAR 		=> __("1 Year"),
	GTS_2_YEARS 	=> __("2 Years"),
	);

$graph_weekdays = array(
	WD_SUNDAY	 	=> date("l", strtotime("Sunday")),
	WD_MONDAY 		=> date("l", strtotime("Monday")),
	WD_TUESDAY	 	=> date("l", strtotime("Tuesday")),
	WD_WEDNESDAY 	=> date("l", strtotime("Wednesday")),
	WD_THURSDAY 	=> date("l", strtotime("Thursday")),
	WD_FRIDAY	 	=> date("l", strtotime("Friday")),
	WD_SATURDAY		=> date("l", strtotime("Saturday"))
	);

$graph_dateformats = array(
	GD_MO_D_Y =>"Month Number, Day, Year",
	GD_MN_D_Y =>"Month Name, Day, Year",
	GD_D_MO_Y =>"Day, Month Number, Year",
	GD_D_MN_Y =>"Day, Month Name, Year",
	GD_Y_MO_D =>"Year, Month Number, Day",
	GD_Y_MN_D =>"Year, Month Name, Day"
	);

$graph_datechar = array(
	GDC_HYPHEN => "-",
	GDC_SLASH => "/",
	GDC_DOT => "."
	);

?>
