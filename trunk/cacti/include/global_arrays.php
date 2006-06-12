<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

$snmp_query_field_actions = array(
	1 => _("SNMP Field Name (Dropdown)"),
	2 => _("SNMP Field Value (From User)"),
	3 => _("SNMP Output Type (Dropdown)")
	);

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

$poller_options = array(
	1 => "cmd.php",
	2 => "cactid"
	);

$poller_intervals = array(
	60 => _("Every Minute"),
	300 => _("Every 5 Minutes"));

$rra_polling_frequency = array(
	60 => _("Every Minute"),
	120 => _("Every 2 Minutes"),
	300 => _("Every 5 Minutes"),
	900 => _("Every 15 Minutes"),
	1200 => _("Every 30 Minutes"),
	3600 => _("Every Hour"),
	14400 => _("Every 4 Hours"),
	86400 => _("Every Day")
	);

$rra_storage_durations = array(
	86400 => _("1 Day"),
	172800 => _("2 Days"),
	604800 => _("1 Week"),
	1209600 => _("2 Weeks"),
	1814400 => _("3 Weeks"),
	2678400 => _("1 Month"),
	5356800 => _("2 Months"),
	10713600 => _("4 Months"),
	33053184 => _("1 Year"),
	66106368 => _("2 Years")
	);

$registered_cacti_names = array(
	1 => "path_cacti"
	);

$graph_views = array(
	1 => _("Tree View"),
	2 => _("List View"),
	3 => _("Preview View")
	);

$graph_tree_views = array(
	1 => _("Single Pane"),
	2 => _("Dual Pane")
	);

$auth_realms = array(
	0 => _("Local"),
	1 => _("LDAP")
	);

$auth_methods = array(
	0 => _("None"),
	1 => _("Builtin Authentication"),
	2 => _("Web Basic Authentication"),
	3 => _("LDAP Authentication")
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
	0 => _("None"),
	1 => _("SSL"),
	2 => _("TLS"),
	);

$ldap_modes = array(
	0 => _("No Searching"),
	1 => _("Anonymous Searching"),
	2 => _("Specific Searching")
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
		"graph_trees.php" => _("Trees"),
		"graphs.php" => _("Graphs"),
		"devices.php" => _("Devices"),
		"data_sources.php" => _("Data Sources")
		),
	_("Data Collection") => array(
		"pollers.php" => _("Pollers"),
		"scripts.php" => _("Scripts"),
		"data_queries.php" => _("Queries")
		),
	_("Templates") => array(
		"graph_templates.php" => _("Graph Templates"),
		"device_templates.php" => _("Device Templates"),
		"data_templates.php" => _("Data Templates"),
		"packages.php" => _("Packages")
		),
	_("Configuration")  => array(
		"settings.php" => _("System Settings"),
		"plugins.php" => _("Plugins"),
		"user_settings.php" => _("User Settings"),
		"presets.php?action=view_cdef" => _("Data Presets")
		),
	_("Access Controls") => array(
		"auth_user.php" => _("Users"),
		"auth_group.php" => _("Groups")
		),
	_("Utilities") => array(
		"utilities.php" => _("System Utilities"),
		"logs.php" => _("Log Management"),
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
	12 => _("Update Device Templates"),
	13 => _("Data Queries"),
	14 => _("Update Data Presets"),
	15 => _("Global Settings"),
	16 => _("Export Data"),
	17 => _("Import Data"),
	18 => _("Change Password"),
	19 => _("User Settings"),
	20 => _("View Graph Properties")
	);

$user_auth_realm_filenames = array(
	"about.php" => 8,
	"presets.php" => 14,
	"presets_cdef.php" => 14,
	"presets_color.php" => 14,
	"presets_gprint.php" => 14,
	"presets_rra.php" => 14,
	"color.php" => 5,
	"scripts.php" => 2,
	"scripts_fields.php" => 2,
	"pollers.php" => 2,
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
	"devices.php" => 3,
	"device_templates.php" => 12,
	"index.php" => 8,
	"settings.php" => 15,
	"data_queries.php" => 13,
	"packages.php" => 17,
	"graph_trees.php" => 4,
	"user_admin.php" => 1,
	"utilities.php" => 15,
	"smtp_servers.php" => 8,
	"email_templates.php" => 8,
	"event_queue.php" => 8,
	"smtp_queue.php" => 8,
	"user_changepassword.php" => "18",
	"settings_wizard.php" => "15",
	"user_settings.php" => "19",
	"php_info.php" => 15,
	"logs.php" => "15",
	"plugins.php" => "15",
	"auth_user.php" => "15",
	"auth_group.php" => "15"
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
	"host_template" => "02",
	"rra_template" => "16"
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
	"host_template" => _("Device Template"),
	"round_robin_archive" => _("Round Robin Archive"),
	"rra_template" => _("Round Robin Archive Template")
	);

$host_struc = array(
	"id",
	"poller_id",
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
	"cur_pkt_loss",
	"avg_pkt_loss",
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