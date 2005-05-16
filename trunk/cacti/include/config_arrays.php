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

$messages = array(
	1  => array(
		"message" => _('Save Successful.'),
		"type" => "info"),
	2  => array(
		"message" => _('Save Failed.'),
		"type" => "error"),
	3  => array(
		"message" => _('Save Failed: Field Input Error (Check Red Fields)'),
		"type" => "error"),
	4  => array(
		"message" => _('Passwords do not match, please retype.'),
		"type" => "error"),
	5  => array(
		"message" => _('You must select at least one field.'),
		"type" => "error"),
	6  => array(
		"message" => _('You must have authentication turned on to use this feature.'),
		"type" => "error"),
	7  => array(
		"message" => _('XML parse error.'),
		"type" => "error"),
	8  => array(
		"message" => _('Authentication Failure.'),
		"type" => "error"),
	9  => array(
		"message" => _('Unable to change password.'),
		"type" => "error"),
	10 => array(
		"message" => _('You must fill in required values.'),
		"type" => "error"),
	11 => array(
		"message" => _('Password changed successfully.'),
		"type" => "info"),
	12 => array(
		"message" => _('Username already in use.'),
		"type" => "error"),
	13 => array(
		"message" => _('SNMP v3 authentication passwords do not match.'),
		"type" => "error"),
	14 => array(
		"message" => _('SNMP v3 privacy passphrases do not match.'),
		"type" => "error"),
	15 => array(
		"message" => _('XML: Cacti version does not exist.'),
		"type" => "error"),
	16 => array(
		"message" => _('XML: Generated with an unknown (probably newer) version of Cacti.'),
		"type" => "error"),
	17 => array(
		"message" => _('XML: Generated with a newer version of Cacti.'),
		"type" => "error"),
	18 => array(
		"message" => _('XML: Cannot locate type code.'),
		"type" => "error")
		);

$data_input_field_inputs = array(
	1 => _("Custom Value"),
	2 => _("Device Field Value")
	);

$script_types = array(
	SCRIPT_INPUT_TYPE_SCRIPT => _("Script/Command"),
	SCRIPT_INPUT_TYPE_PHP_SCRIPT_SERVER => _("Script - Script Server (PHP)")
	);

$snmp_query_field_actions = array(
	1 => _("SNMP Field Name (Dropdown)"),
	2 => _("SNMP Field Value (From User)"),
	3 => _("SNMP Output Type (Dropdown)")
	);

$snmp_versions = array(
	1 => _("Version 1"),
	2 => _("Version 2"),
	3 => _("Version 3")
	);

$snmpv3_security_level = array(
	"authNoPriv" => _("No Privacy Protocol"),
	"authPriv" => _("Privacy Protocol")
	);

$snmpv3_auth_protocol = array(
	"MD5" => _("MD5 (default)"),
	"SHA" => _("SHA")
	);

$snmpv3_priv_protocol = array(
	"[None]" => "[None]",
	"DES" => "DES (default)",
	"AES128" => "AES");

$syslog_options = array(
	SYSLOG_CACTI => _("Cacti Syslog Only"),
	SYSLOG_BOTH => _("Cacti Syslog and System Syslog/Eventlog"),
	SYSLOG_SYSTEM => _("System Syslog/Eventlog Only"));

$availability_options = array(
	AVAIL_SNMP_AND_PING => _("Ping and SNMP - Most Recommended"),
	AVAIL_SNMP => _("SNMP - Reliable"),
	AVAIL_PING => _("Ping - Faster Option with Risk"),
	AVAIL_NONE => _("None - Don't Precheck Device Status")
	);

$ping_methods = array(
	PING_ICMP => _("ICMP Ping"),
	PING_UDP => _("UDP Ping"),
	PING_NONE => _("Not Applicable")
	);

$syslog_verbosity = array(
	POLLER_VERBOSITY_NONE => "NONE - Syslog Only if Selected",
	POLLER_VERBOSITY_LOW => "LOW - Statistics and Errors",
	POLLER_VERBOSITY_MEDIUM => "MEDIUM - Statistics, Errors and Results",
	POLLER_VERBOSITY_HIGH => "HIGH - Statistics, Errors, Results and Major I/O Events",
	POLLER_VERBOSITY_DEBUG => "DEBUG - Statistics, Errors, Results, I/O and Program Flow"
	);

$poller_options = array(
	1 => "cmd.php",
	2 => "cactid"
	);

$registered_cacti_names = array(
	1 => "path_cacti"
	);

$graph_views = array(
	1 => "Tree View",
	2 => "List View",
	3 => "Preview View"
	);

$graph_tree_views = array(
	1 => "Single Pane",
	2 => "Dual Pane"
	);

$auth_realms = array(
	0 => "Local",
	1 => "LDAP"
	);

$auth_methods = array(
	0 => "None",
	1 => "Builtin Authentication",
	2 => "Web Basic Authentication",
	3 => "LDAP Authentication"
	);

$user_password_expire_intervals = array(
	0 => _("Never"),
	30 => _("30 Days"),
	60 => _("60 Days"),
	90 => _("90 Days"),
	120 => _("120 Days"),
	150 => _("150 Days"),
	180 => _("180 Days"),
	210 => _("210 Days"),
	240 => _("240 Days"),
	270 => _("270 Days"),
	300 => _("300 Days"),
	330 => _("330 Days"),
	360 => _("360 Days")
	);

$graph_perms_type_array = array(
	"graph" => "1",
	"tree" => "2",
	"host" => "3",
	"graph_template" => "4"
	);
$ldap_versions = array(
	2 => _("Version 2"),
	3 => _("Version 3")
	);

$ldap_encryption = array(
	0 => "None",
	1 => "SSL",
	2 => "TLS",
	);

$ldap_modes = array(
	0 => _("No Searching"),
	1 => _("Anonymous Searching"),
	2 => _("Specific Searching")
	);

$syslog_control_options = array(
	SYSLOG_MNG_ASNEEDED => _("Overwrite events as needed"),
	SYSLOG_MNG_DAYSOLD => _("Overwrite events older than the maximum days"),
	SYSLOG_MNG_STOPLOG => _("Stop logging if maximum log size is exceeded")
);

$snmp_implementations = array(
	"ucd-snmp" => "UCD-SNMP 4.x",
	"net-snmp" => "NET-SNMP");

$rrdtool_versions = array(
	"rrd-1.0.x" => "RRDTool 1.0.x",
	"rrd-1.2.x" => "RRDTool 1.2.x");

$cdef_item_types = array(
	1 => _("Function"),
	2 => _("Operator"),
	4 => _("Special Data Source"),
	5 => _("Another CDEF"),
	6 => _("Custom String")
	);

$tree_sort_types = array(
	TREE_ORDERING_NONE => _("Manual Ordering (No Sorting)"),
	TREE_ORDERING_ALPHABETIC => _("Alphabetic Ordering"),
	TREE_ORDERING_NUMERIC => _("Numeric Ordering")
	);

$custom_data_source_types = array(
	"CURRENT_DATA_SOURCE" => _("Current Graph Item Data Source"),
	"ALL_DATA_SOURCES_NODUPS" => _("All Data Sources (Don't Include Duplicates)"),
	"ALL_DATA_SOURCES_DUPS" => _("All Data Sources (Include Duplicates)"),
	"CURRENT_DS_MINIMUM_VALUE" => _("Current Data Source Item: Minimum Value"),
	"SIMILAR_DATA_SOURCES_NODUPS" => _("All Similar Data Sources (Don't Include Duplicates)"),
	"CURRENT_DS_MAXIMUM_VALUE" => _("Current Data Source Item: Maximum Value"),
	"CURRENT_GRAPH_MINIMUM_VALUE" => _("Graph: Lower Limit"),
	"CURRENT_GRAPH_MAXIMUM_VALUE" => _("Graph: Upper Limit"));

$menu = array(
	_("Create") => array(
		"graphs_new.php" => _("New Graphs")
		),
	_("Management") => array(
		"tree.php" => _("Trees"),
		"graphs.php" => _("Graphs"),
		"host.php" => _("Devices"),
		"data_sources.php" => array(
			"data_sources.php" => _("Data Sources"),
			"rra.php" => _("RRAs")
			)
		),
	_("Data Collection") => array(
		"data_pollers.php" => _("Pollers"),
		"data_input.php" => _("Scripts"),
		"data_queries.php" => _("Queries")
		),
	_("Templates") => array(
		"graph_templates.php" => _("Graph Templates"),
		"host_templates.php" => _("Host Templates"),
		"data_templates.php" => _("Data Templates")
		),
	_("Import/Export") => array(
		"templates_import.php" => _("Import Templates"),
		"templates_export.php" => _("Export Templates")
		),
	_("Configuration")  => array(
		"settings.php" => _("System Settings"),
		"user_settings.php" => _("User Settings"),
		"settings_wizard.php" => _("Setup Wizard"),
		"presets.php?action=view_cdef" => _("Data Presets")
		),
	_("Utilities") => array(
		"utilities.php" => _("System Utilities"),
		"user_admin.php" => _("User Management"),
		"user_changepassword.php" => _("Change Password"),
		"logout.php" => _("Logout User")
		)
	);

$user_auth_realms = array(
	1 => _("User Administration"),
	2 => _("Data Input"),
	3 => _("Update Data Sources"),
	4 => _("Update Graph Trees"),
	5 => _("Update Graphs"),
	7 => _("View Graphs"),
	8 => _("Console Access"),
	9 => _("Update Round Robin Archives"),
	10 => _("Update Graph Templates"),
	11 => _("Update Data Templates"),
	12 => _("Update Host Templates"),
	13 => _("Data Queries"),
	14 => _("Update Data Presets"),
	15 => _("Global Settings"),
	16 => _("Export Data"),
	17 => _("Import Data"),
	18 => _("Change Password"),
	19 => _("User Settings")
	);

$user_auth_realm_filenames = array(
	"about.php" => 8,
	"presets.php" => 14,
	"presets_cdef.php" => 14,
	"presets_color.php" => 14,
	"presets_gprint.php" => 14,
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
	"cdef" => _("CDEF"),
	"cdef_item" => _("CDEF Item"),
	"gprint_preset" => _("GPRINT Preset"),
	"data_input_method" => _("Data Input Method"),
	"data_input_field" => _("Data Input Field"),
	"data_template" => _("Data Template"),
	"data_template_item" => _("Data Template Item"),
	"graph_template" => _("Graph Template"),
	"graph_template_item" => _("Graph Template Item"),
	"graph_template_input" => _("Graph Template Input"),
	"data_query" => _("Data Query"),
	"host_template" => _("Host Template"),
	"round_robin_archive" => _("Round Robin Archive")
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

$graph_dateformats = array(
	GD_MO_D_Y => _("Month Number, Day, Year"),
	GD_MN_D_Y => _("Month Name, Day, Year"),
	GD_D_MO_Y => _("Day, Month Number, Year"),
	GD_D_MN_Y => _("Day, Month Name, Year"),
	GD_Y_MO_D => _("Year, Month Number, Day"),
	GD_Y_MN_D => _("Year, Month Name, Day")
	);

$graph_datechar = array(
	GDC_HYPHEN => "-",
	GDC_SLASH => "/"
	);

$themes = array(
	"aq" => _("Soft New Look"),
	"classic" => _("Traditional Cacti Theme"),
	"black" => _("Dark and Distinguished"),
	"blue" => _("Don't Know Yet"),
	"bulix" => _("Nostalgic Look"),
	"kde" => _("Matches the KDE Desktop"),
	"metal" => _("Wrapped in Chains"),
	"witendoxp" => _("Windows XP Default")
	);
?>
