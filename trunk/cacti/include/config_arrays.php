<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

$messages = array(
	1  => array(
		"message" => 'Save Successful.',
		"type" => "info"),
	2  => array(
		"message" => 'Save Failed.',
		"type" => "error"),
	3  => array(
		"message" => 'Save Failed: Field Input Error (Check Red Fields)',
		"type" => "error"),
	4  => array(
		"message" => 'Passwords do not match, please retype.',
		"type" => "error"),
	5  => array(
		"message" => 'You must select at least one field.',
		"type" => "error"),
	6  => array(
		"message" => 'You must have authentication turned on to use this feature.',
		"type" => "error"),
	7  => array(
		"message" => 'XML parse error.',
		"type" => "error"),
	8  => array(
		"message" => 'Authentication Failure.',
		"type" => "error"),
	9  => array(
		"message" => 'Unable to change password.',
		"type" => "error"),
	10 => array(
		"message" => 'You must fill in required values.',
		"type" => "error"),
	11 => array(
		"message" => 'Password changed successfully.',
		"type" => "info"),
	12 => array(
		"message" => 'Username already in use.',
		"type" => "error"),
	13 => array(
		"message" => 'SNMP v3 authentication passwords do not match.',
		"type" => "error"),
	14 => array(
		"message" => 'SNMP v3 privacy passphrases do not match.',
		"type" => "error"),
	15 => array(
		"message" => 'XML: Cacti version does not exist.',
		"type" => "error"),
	16 => array(
		"message" => 'XML: Hash version does not exist.',
		"type" => "error"),
	17 => array(
		"message" => 'XML: Generated with a newer version of Cacti.',
		"type" => "error"),
	18 => array(
		"message" => 'XML: Cannot locate type code.',
		"type" => "error")
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
	DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER => "Script Query - Script Server"
	);

$script_types = array(
	DATA_INPUT_TYPE_SCRIPT => "Script/Command",
	DATA_INPUT_TYPE_PHP_SCRIPT_SERVER => "Script - Script Server (PHP)"
	);

$reindex_types = array(
	DATA_QUERY_AUTOINDEX_NONE => "None",
	DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME => "Uptime Goes Backwards",
	DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE => "Index Count Changed",
	DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION => "Verify All Fields"
	);

$snmp_query_field_actions = array(1 =>
	"SNMP Field Name (Dropdown)",
	"SNMP Field Value (From User)",
	"SNMP Output Type (Dropdown)");

$consolidation_functions = array(1 =>
	"AVERAGE",
	"MIN",
	"MAX",
	"LAST");

$data_source_types = array(1 =>
	"GAUGE",
	"COUNTER",
	"DERIVE",
	"ABSOLUTE");

$graph_item_types = array(
	GRAPH_ITEM_TYPE_COMMENT => "COMMENT",
	GRAPH_ITEM_TYPE_HRULE => "HRULE",
	GRAPH_ITEM_TYPE_VRULE => "VRULE",
	GRAPH_ITEM_TYPE_LINE1 => "LINE1",
	GRAPH_ITEM_TYPE_LINE2 => "LINE2",
	GRAPH_ITEM_TYPE_LINE3 => "LINE3",
	GRAPH_ITEM_TYPE_AREA => "AREA",
	GRAPH_ITEM_TYPE_STACK => "STACK",
	GRAPH_ITEM_TYPE_GPRINT => "GPRINT",
	GRAPH_ITEM_TYPE_LEGEND => "LEGEND");

$image_types = array(1 =>
	"PNG",
	"GIF");

$snmp_versions = array(1 =>
	"Version 1",
	"Version 2",
	"Version 3");

$snmpv3_security_level = array(
	"authNoPriv" => "No Privacy Protocol",
	"authPriv" => "Privacy Protocol");

$snmpv3_auth_protocol = array(
	"MD5" => "MD5 (default)",
	"SHA" => "SHA");

$snmpv3_priv_protocol = array(
	"[None]" => "[None]",
	"DES" => "DES (default)",
	"AES128" => "AES128",
	"AES192" => "AES192",
	"AES256" => "AES256");

$logfile_options = array(1 =>
	"Logfile Only",
	"Logfile and Syslog/Eventlog",
	"Syslog/Eventlog Only");

$availability_options = array(
	AVAIL_SNMP_AND_PING => "Ping and SNMP - Most Recommended",
	AVAIL_SNMP => "SNMP - Reliable",
	AVAIL_PING => "Ping - Faster Option with Risk",
	AVAIL_NONE => "None - Don't Precheck Device Status");

$ping_methods = array(
	PING_ICMP => "ICMP Ping",
	PING_UDP => "UDP Ping",
	PING_NONE => "Not Applicable");

$logfile_verbosity = array(
	POLLER_VERBOSITY_NONE => "NONE - Syslog Only if Selected",
	POLLER_VERBOSITY_LOW => "LOW - Statistics and Errors",
	POLLER_VERBOSITY_MEDIUM => "MEDIUM - Statistics, Errors and Results",
	POLLER_VERBOSITY_HIGH => "HIGH - Statistics, Errors, Results and Major I/O Events",
	POLLER_VERBOSITY_DEBUG => "DEBUG - Statistics, Errors, Results, I/O and Program Flow");

$poller_options = array(1 =>
	"cmd.php",
	"cactid");

$registered_cacti_names = array(
	"path_cacti");

$graph_views = array(1 =>
	"Tree View",
	"List View",
	"Preview View");

$graph_tree_views = array(1 =>
	"Single Pane",
	"Dual Pane");

$auth_realms = array(0 =>
	"Local",
	"LDAP");

$auth_methods = array(
	0 => "None",
	1 => "Builtin Authentication",
	2 => "Web Basic Authentication",
	3 => "LDAP Authentication");

$user_password_expire_intervals = array(
	0 => "Never",
	30 => "30 Days",
	60 => "60 Days",
	90 => "90 Days",
	120 => "120 Days",
	150 => "150 Days",
	180 => "180 Days",
	210 => "210 Days",
	240 => "240 Days",
	270 => "270 Days",
	300 => "300 Days",
	330 => "330 Days",
	360 => "360 Days"
	);

$graph_perms_type_array = array(
	"graph" => "1",
	"tree" => "2",
	"host" => "3",
	"graph_template" => "4"
	);

$ldap_versions = array(
	2 => "Version 2",
	3 => "Version 3"
);

$ldap_encryption = array(
	0 => "None",
	1 => "SSL",
	2 => "TLS",
);

$ldap_modes = array(
	0 => "No Searching",
	1 => "Anonymous Searching",
	2 => "Specific Searching"
);

$snmp_implimentations = array(
	"ucd-snmp" => "UCD-SNMP 4.x",
	"net-snmp" => "NET-SNMP 5.x");

$cdef_item_types = array(
	1 => "Function",
	2 => "Operator",
	4 => "Special Data Source",
	5 => "Another CDEF",
	6 => "Custom String");

$tree_sort_types = array(
	TREE_ORDERING_NONE => "Manual Ordering (No Sorting)",
	TREE_ORDERING_ALPHABETIC => "Alphabetic Ordering",
	TREE_ORDERING_NUMERIC => "Numeric Ordering"
	);

$custom_data_source_types = array(
	"CURRENT_DATA_SOURCE" => "Current Graph Item Data Source",
	"ALL_DATA_SOURCES_NODUPS" => "All Data Sources (Don't Include Duplicates)",
	"ALL_DATA_SOURCES_DUPS" => "All Data Sources (Include Duplicates)",
	"CURRENT_DS_MINIMUM_VALUE" => "Current Data Source Item: Minimum Value",
	"SIMILAR_DATA_SOURCES_NODUPS" => "All Similar Data Sources (Don't Include Duplicates)",
	"CURRENT_DS_MAXIMUM_VALUE" => "Current Data Source Item: Maximum Value",
	"CURRENT_GRAPH_MINIMUM_VALUE" => "Graph: Lower Limit",
	"CURRENT_GRAPH_MAXIMUM_VALUE" => "Graph: Upper Limit");

$menu = array(
	"Create" => array(
		"graphs_new.php" => "New Graphs"
		),
	"Management" => array(
		"tree.php" => "Trees",
		"graphs.php" => array(
			"graphs.php" => "Graphs",
			"cdef.php" => "CDEFs",
			"color.php" => "Colors",
			"gprint_presets.php" => "GPRINT Presets"
			),
		"host.php" => "Devices",
		"data_sources.php" => array(
			"data_sources.php" => "Data Sources",
			"rra.php" => "RRAs"
			)
		),
	"Data Collection" => array(
		"data_pollers.php" => "Pollers",
		"data_input.php" => "Scripts",
		"data_queries.php" => "Queries"
		),
	//"Event Management" => array(
	//	"smtp_servers.php" => "Mail Servers",
	//	"email_templates.php" => "Email Templates",
	//	"event_queue.php" => "Event Queue",
	//	"smtp_queue.php" => "Mail Queue"
	//	),
	"Templates" => array(
		"graph_templates.php" => "Graph Templates",
		"host_templates.php" => "Host Templates",
		"data_templates.php" => "Data Templates"
		),
	"Import/Export" => array(
		"templates_import.php" => "Import Templates",
		"templates_export.php" => "Export Templates"
		),
	"Configuration"  => array(
		"settings.php" => "System Settings",
		"user_settings.php" => "User Settings",
		"settings_wizard.php" => "Setup Wizard"
		),
	"Utilities" => array(
		"utilities.php" => "System Utilities",
		"user_admin.php" => "User Management",
		"user_changepassword.php" => "Change Password",
		"logout.php" => "Logout User"
		)
	);

$user_auth_realms = array(
	1 => "User Administration",
	2 => "Data Input",
	3 => "Update Data Sources",
	4 => "Update Graph Trees",
	5 => "Update Graphs",
	7 => "View Graphs",
	8 => "Console Access",
	9 => "Update Round Robin Archives",
	10 => "Update Graph Templates",
	11 => "Update Data Templates",
	12 => "Update Host Templates",
	13 => "Data Queries",
	14 => "Update CDEF's",
	15 => "Global Settings",
	16 => "Export Data",
	17 => "Import Data",
	18 => "Change Password",
	19 => "User Settings"
	);

$user_auth_realm_filenames = array(
	"about.php" => 8,
	"cdef.php" => 14,
	"color.php" => 5,
	"data_input.php" => 2,
	"data_pollers.php" => 2,
	"data_sources.php" => 3,
	"data_templates.php" => 11,
	"gprint_presets.php" => 5,
	"graph.php" => 7,
	"graph_image.php" => 7,
	"graph_settings.php" => 7,
	"graph_templates.php" => 10,
	"graph_templates_inputs.php" => 10,
	"graph_templates_items.php" => 10,
	"graph_view.php" => 7,
	"graphs.php" => 5,
	"graphs_items.php" => 5,
	"graphs_new.php" => 5,
	"host.php" => 3,
	"host_templates.php" => 12,
	"index.php" => 8,
	"rra.php" => 9,
	"settings.php" => 15,
	"data_queries.php" => 13,
	"templates_export.php" => 16,
	"templates_import.php" => 17,
	"tree.php" => 4,
	"user_admin.php" => 1,
	"utilities.php" => 15,
	"smtp_servers.php" => 8,
	"email_templates.php" => 8,
	"event_queue.php" => 8,
	"smtp_queue.php" => 8,
	"user_changepassword.php" => "18",
	"settings_wizard.php" => "15",
	"user_settings.php" => "19"
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
	"0.8.4" => "0000",
	"0.8.5" => "0001",
	"0.8.5a" => "0002",
	"0.8.6" => "0003",
	"0.8.6a" => "0004",
	"0.8.6b" => "0005",
	"0.8.7" => "0006"
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
	"snmp_community",
	"snmp_version",
	"snmpv3_auth_username",
	"snmpv3_auth_password",
	"snmpv3_auth_protocol",
	"snmpv3_priv_passphrase",
	"snmpv3_priv_protocol",
	"snmp_port",
	"snmp_timeout",
	"availability_method",
	"ping_method",
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
	GT_LAST_HALF_HOUR => "Last Half Hour",
	GT_LAST_HOUR => "Last Hour",
	GT_LAST_2_HOURS => "Last 2 Hours",
	GT_LAST_4_HOURS => "Last 4 Hours",
	GT_LAST_6_HOURS =>"Last 6 Hours",
	GT_LAST_12_HOURS =>"Last 12 Hours",
	GT_LAST_DAY =>"Last Day",
	GT_LAST_2_DAYS =>"Last 2 Days",
	GT_LAST_3_DAYS =>"Last 3 Days",
	GT_LAST_4_DAYS =>"Last 4 Days",
	GT_LAST_WEEK =>"Last Week",
	GT_LAST_2_WEEKS =>"Last 2 Weeks",
	GT_LAST_MONTH =>"Last Month",
	GT_LAST_2_MONTHS =>"Last 2 Months",
	GT_LAST_3_MONTHS =>"Last 3 Months",
	GT_LAST_4_MONTHS =>"Last 4 Months",
	GT_LAST_6_MONTHS =>"Last 6 Months",
	GT_LAST_YEAR =>"Last Year",
	GT_LAST_2_YEARS =>"Last 2 Years"
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
	GDC_SLASH => "/"
	);

$themes = array(
	"aq" => "Soft New Look",
	"classic" => "Traditional Cacti Theme",
	"black" => "Dark and Distinguished",
	"blue" => "Don't Know Yet",
	"bulix" => "Nostalgic Look",
	"kde" => "Matches the KDE Desktop",
	"metal" => "Wrapped in Chains",
	"witendoxp" => "Windows XP Default"
	);
?>
