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

/* tab information */
$tabs = array(
	"general"        => __("General"),
	"snmp"           => __("SNMP"),
	"avail"          => __("Availability"),
	"poller"         => __("Poller"),
	"path"           => __("Paths"),
	"export"         => __("Graph Export"),
	"visual"         => __("Visual"),
	"authentication" => __("Authentication"),
	"logging"        => __("Logging")
);

$tabs_graphs = array(
	"general"   => __("General"),
	"thumbnail" => __("Graph Thumbnails"),
	"tree"      => __("Tree View Mode"),
	"preview"   => __("Preview Mode"),
	"list"      => __("List View Mode"),
	"fonts"     => __("Graph Fonts"),
	"colortags" => __("Graph Color Tags")
);

/* setting information */
$settings = array(
	"path" => array(
		"dependent_header" => array(
			"friendly_name" => __("Required Tool Paths"),
			"method" => "spacer",
			),
		"path_snmpwalk" => array(
			"friendly_name" => __("snmpwalk Binary Path"),
			"description" => __("The path to your snmpwalk binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"path_snmpget" => array(
			"friendly_name" => __("snmpget Binary Path"),
			"description" => __("The path to your snmpget binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"path_snmpbulkwalk" => array(
			"friendly_name" => __("snmpbulkwalk Binary Path"),
			"description" => __("The path to your snmpbulkwalk binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"path_snmpgetnext" => array(
			"friendly_name" => __("snmpgetnext Binary Path"),
			"description" => __("The path to your snmpgetnext binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"path_rrdtool" => array(
			"friendly_name" => __("RRDTool Binary Path"),
			"description" => __("The path to the rrdtool binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"path_rrdtool_default_font" => array(
			"friendly_name" => __("RRDTool Default Font Path"),
			"description" => __("For RRDtool 1.2, the path to the True Type Font. For RRDtool 1.3 and above, the font name conforming to the fontconfig naming convention."),
			"method" => "font",
			"max_length" => "255"
			),
		"path_php_binary" => array(
			"friendly_name" => __("PHP Binary Path"),
			"description" => __("The path to your PHP binary file (may require a php recompile to get this file)."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"logging_header" => array(
			"friendly_name" => __("Logging"),
			"method" => "spacer",
			),
		"path_cactilog" => array(
			"friendly_name" => __("Cacti Log File Path"),
			"description" => __("The path to your Cacti log file (if blank, defaults to &lt;path_cacti&gt;/log/cacti.log)"),
			"method" => "filepath",
			"default" => CACTI_BASE_PATH . "/log/cacti.log",
			"max_length" => "255"
			),
		"pollerpaths_header" => array(
			"friendly_name" => __("Alternate Poller Path"),
			"method" => "spacer",
			),
		"path_spine" => array(
			"friendly_name" => __("Spine Poller File Path"),
			"description" => __("The path to Spine binary."),
			"method" => "filepath",
			"max_length" => "255"
			),
		"extendedpaths_header" => array(
			"friendly_name" => __("Structured RRD Path"),
			"method" => "spacer",
			),
		"extended_paths" => array(
			"friendly_name" => __("Structured RRA Path (/device_id/local_data_id.rrd)"),
			"description" => __("Use a seperate subfolder for each devices RRD files."),
			"method" => "checkbox"
 			)
		),
	"general" => array(
		"logging_header" => array(
			"friendly_name" => __("Event Logging"),
			"method" => "spacer",
			),
		"log_destination" => array(
			"friendly_name" => __("Log File Destination"),
			"description" => __("How will Cacti handle event logging."),
			"method" => "drop_array",
			"default" => 1,
			"array" => $logfile_options,
			),
		"web_log" => array(
			"friendly_name" => __("Web Events"),
			"description" => __("What Cacti website messages should be placed in the log."),
			"method" => "checkbox_group",
			"tab" => __("general"),
			"items" => array(
				"log_snmp" => array(
					"friendly_name" => __("Web SNMP Messages"),
					"default" => ""
					),
				"log_graph" => array(
					"friendly_name" => __("Web RRD Graph Syntax"),
					"default" => ""
					),
				"log_export" => array(
					"friendly_name" => __("Graph Export Messages"),
					"default" => ""
					)
				),
			),
		"poller_header" => array(
			"friendly_name" => __("Poller Specific Logging"),
			"method" => "spacer",
			),
		"log_verbosity" => array(
			"friendly_name" => __("Poller Logging Level"),
			"description" => __("What level of detail do you want sent to the log file.  WARNING: Leaving in any other status than NONE or LOW can exaust your disk space rapidly."),
			"method" => "drop_array",
			"default" => POLLER_VERBOSITY_LOW,
			"array" => $logfile_verbosity,
			),
		"poller_log" => array(
			"friendly_name" => __("Poller Syslog/Eventlog Selection"),
			"description" => __("If you are using the Syslog/Eventlog, What Cacti poller messages should be placed in the Syslog/Eventlog."),
			"method" => "checkbox_group",
			"tab" => __("poller"),
			"items" => array(
				"log_pstats" => array(
					"friendly_name" => __("Poller Statistics"),
					"default" => ""
					),
				"log_pwarn" => array(
					"friendly_name" => __("Poller Warnings"),
					"default" => ""
					),
				"log_perror" => array(
					"friendly_name" => __("Poller Errors"),
					"default" => CHECKED
					)
				),
			),
		"versions_header" => array(
			"friendly_name" => __("Required Tool Versions"),
			"method" => "spacer",
			),
		"snmp_version" => array(
			"friendly_name" => __("SNMP Utility Version"),
			"description" => __("The type of SNMP you have installed.  Required if you are using SNMP v2c or don't have embedded SNMP support in PHP."),
			"method" => "drop_array",
			"default" => "net-snmp",
			"array" => $snmp_implementations,
			),
		"rrdtool_version" => array(
			"friendly_name" => __("RRDTool Utility Version"),
			"description" => __("The version of RRDTool that you have installed."),
			"method" => "drop_array",
			"default" => RRD_VERSION_1_0,
			"array" => $rrdtool_versions,
			),
		"other_header" => array(
			"friendly_name" => __("Other Defaults"),
			"method" => "spacer",
			),
        "datetime_setting" => array(
			"friendly_name" => __("Date/Time setting for logs"),
			"description" => __("The date/time setting for logs"),
			"method" => "drop_array",
			"default" => "m/d/Y h:i:s A",
			"array" => $datetime_format,
			),
    
        "reindex_method" => array(
			"friendly_name" => __("Reindex Method for Data Queries"),
			"description" => __("The default reindex method to use for all Data Queries."),
			"method" => "drop_array",
			"default" => "1",
			"array" => $reindex_types,
			),
		"require_ssl" => array(
			"friendly_name" => __("Require SSL Encryption"),
			"description" => __("Redirect all HTTP connections to HTTPS."),
			"default" => "off",
			"method" => "checkbox"
			),
		"deletion_verification" => array(
			"friendly_name" => __("Deletion Verification"),
			"description" => __("Prompt user before item deletion."),
			"default" => CHECKED,
			"method" => "checkbox"
			),
		"i18n_support" => array(
			"friendly_name" => __("Language Support"),
			"description" => __("Choose \"enabled\" to allow the localization of Cacti. The strict mode requires that the requested language will also be supported by all plugins being installed at your system. If that's not the fact everything will be displayed in English."),
			"method" => "drop_array",
			"default" => "0",
			"array" => $i18n_modes
			),
		"i18n_default_language" => array(
			"friendly_name" => __("Default language"),
			"description" => __("Default language for this system."),
			"method" => "drop_array",
			"default" => "us",
			"array" => get_installed_locales()
			),
		"i18n_auto_detection" => array(
			"friendly_name" => __("Auto detection"),
			"description" => __("Allow to automatically determine the \"default\" language of the user and provide it at login time if that language is supported by Cacti. If disabled, the default language will be in force until the user elects another language. "),
			"method" => "drop_array",
			"default" => "0",
			"array" => array( "0" => __("Disabled"), "1" => __("Enabled"))
			)
		),
	"snmp" => array(
		"snmp_header" => array(
			"friendly_name" => __("SNMP Defaults"),
			"method" => "spacer",
			),
		"snmp_ver" => array(
			"friendly_name" => __("SNMP Version"),
			"description" => __("Default SNMP version for all new devices."),
			"method" => "drop_array",
			"default" => "1",
			"array" => $snmp_versions,
			),
		"snmp_community" => array(
			"friendly_name" => __("SNMP Community"),
			"description" => __("Default SNMP read community for all new devices."),
			"method" => "textbox",
			"default" => "public",
			"max_length" => "100",
			),
		"snmp_username" => array(
			"friendly_name" => __("SNMP Username (v3)"),
			"description" => __("The SNMP v3 Username for polling devices."),
			"method" => "textbox",
			"default" => "",
			"max_length" => "100",
			),
		"snmp_password" => array(
			"friendly_name" => __("SNMP Password (v3)"),
			"description" => __("The SNMP v3 Password for polling devices."),
			"method" => "textbox_password",
			"default" => "",
			"max_length" => "100",
			),
		"snmp_auth_protocol" => array(
			"method" => "drop_array",
			"friendly_name" => __("SNMP Auth Protocol (v3)"),
			"description" => __("Choose the SNMPv3 Authorization Protocol."),
			"default" => SNMP_AUTH_PROTOCOL_MD5,
			"array" => $snmp_auth_protocols,
			),
		"snmp_priv_passphrase" => array(
			"method" => "textbox",
			"friendly_name" => __("SNMP Privacy Passphrase (v3)"),
			"description" => __("Choose the SNMPv3 Privacy Passphrase."),
			"default" => "",
			"max_length" => "200"
			),
		"snmp_priv_protocol" => array(
			"method" => "drop_array",
			"friendly_name" => __("SNMP Privacy Protocol (v3)"),
			"description" => __("Choose the SNMPv3 Privacy Protocol."),
			"default" => SNMP_PRIV_PROTOCOL_DES,
			"array" => $snmp_priv_protocols,
			),
		"snmp_timeout" => array(
			"friendly_name" => __("SNMP Timeout"),
			"description" => __("Default SNMP timeout in milli-seconds."),
			"method" => "textbox",
			"default" => "500",
			"max_length" => "10",
			"size" => "5"
			),
		"snmp_port" => array(
			"friendly_name" => __("SNMP Port Number"),
			"description" => __("Default UDP port to be used for SNMP Calls.  Typically 161."),
			"method" => "textbox",
			"default" => "161",
			"max_length" => "10",
			"size" => "5"
			),
		"snmp_retries" => array(
			"friendly_name" => __("SNMP Retries"),
			"description" => __("The number times the SNMP poller will attempt to reach the device before failing."),
			"method" => "textbox",
			"default" => "3",
			"max_length" => "10",
			"size" => "5"
			)
		),
	"export" => array(
		"export_hdr_general" => array(
			"friendly_name" => __("General"),
			"method" => "spacer",
			),
		"export_type" => array(
			"friendly_name" => __("Export Method"),
			"description" => __("Choose which export method to use."),
			"method" => "drop_array",
			"default" => __("disabled"),
			"array" => array(
						"disabled" => __("Disabled (no exporting)"),
						"local" => __("Classic (local path)"),
						"ftp_php" => __("FTP (remote) - use php functions"),
						"ftp_ncftpput" => __("FTP (remote) - use ncftpput"),
						"sftp_php" => __("SFTP (remote) - use ssh php functions"),
						),
			),
		"export_presentation" => array(
			"friendly_name" => __("Presentation Method"),
			"description" => __("Choose which presentation would you want for the html generated pages. If you choose classical presentation, the graphs will be in a only-one-html page. If you choose tree presentation, the graph tree architecture will be kept in the static html pages"),
			"method" => "drop_array",
			"default" => __("disabled"),
			"array" => array(
						"classical" => __("Classical Presentation"),
						"tree" => __("Tree Presentation"),
						),
			),
		"export_tree_options" => array(
			"friendly_name" => __("Tree Settings"),
			"method" => "spacer",
			),
		"export_tree_isolation" => array(
			"friendly_name" => __("Tree Isolation"),
			"description" => __("This setting determines if the entire tree is treated as a single hierarchy or as separate hierarchies.  If they are treated separately, graphs will be isolated from one another."),
			"method" => "drop_array",
			"default" => "off",
			"array" => array(
						"off" => __("Single Tree Representation"),
						CHECKED => __("Multiple Tree Representation"),
						),
			),
		"export_user_id" => array(
			"friendly_name" => __("Effective User Name"),
			"description" => __("The user name to utilize for establishing export permissions.  This user name will be used to determine which graphs/tree's are exported.  This setting works in conjunction with the current on/off behavior available within the current templates."),
			"method" => "drop_sql",
			"sql" => "SELECT id, username AS name FROM user_auth ORDER BY name",
			"default" => "1"
			),
		"export_tree_expand_devices" => array(
			"friendly_name" => __("Expand Tree devices"),
			"description" => __("This settings determines if the tree devices will be expanded or not.  If set to expanded, each device will have a sub-folder containing either data templates or data query items."),
			"method" => "drop_array",
			"default" => "off",
			"array" => array(
						"off" => __("Off"),
						CHECKED => __(CHECKED),
						),
			),
		"export_thumb_options" => array(
			"friendly_name" => __("Thumbnail Settings"),
			"method" => "spacer",
			),
		"export_default_height" => array(
			"friendly_name" => __("Thumbnail Height"),
			"description" => __("The height of thumbnail graphs in pixels."),
			"method" => "textbox",
			"default" => "100",
			"max_length" => "10",
			"size" => "5"
			),
		"export_default_width" => array(
			"friendly_name" => __("Thumbnail Width"),
			"description" => __("The width of thumbnail graphs in pixels."),
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10",
			"size" => "5"
			),
		"export_num_columns" => array(
			"friendly_name" => __("Thumbnail Columns"),
			"description" => __("The number of columns to use when displaying thumbnail graphs."),
			"method" => "textbox",
			"default" => "2",
			"max_length" => "5",
			"size" => "5"
			),
		"export_hdr_paths" => array(
			"friendly_name" => __("Paths"),
			"method" => "spacer",
			),
		"path_html_export" => array(
			"friendly_name" => __("Export Directory (both local and ftp)"),
			"description" => __("This is the directory, either on the local system or on the remote system, that will contain the exported data."),
			"method" => "dirpath",
			"max_length" => "255"
			),
		"export_temporary_directory" => array(
			"friendly_name" => __("Local Scratch Directory (ftp only)"),
			"description" => __("This is the a directory that cacti will temporarily store output prior to sending to the remote site via ftp.  The contents of this directory will be deleted after the ftp is completed."),
			"method" => "dirpath",
			"max_length" => "255"
			),
		"export_hdr_timing" => array(
			"friendly_name" => __("Timing"),
			"method" => "spacer",
			),
		"export_timing" => array(
			"friendly_name" => __("Export timing"),
			"description" => __("Choose when to export graphs."),
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"disabled" => __("Disabled"),
						"classic" => __("Classic (export every x times)"),
						"export_hourly" => __("Hourly at specified minutes"),
						"export_daily" => __("Daily at specified time"),
						),
			),
		"path_html_export_skip" => array(
			"friendly_name" => __("Export Every x Times"),
			"description" => __("If you don't want Cacti to export static images every 5 minutes, put another number here. For instance, 3 would equal every 15 minutes."),
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_hourly" => array(
			"friendly_name" => __("Hourly at specified minutes"),
			"description" => __("If you want Cacti to export static images on an hourly basis, put the minutes of the hour when to do that. Cacti assumes that you run the data gathering script every 5 minutes, so it will round your value to the one closest to its runtime. For instance, 43 would equal 40 minutes past the hour."),
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_daily" => array(
			"friendly_name" => __("Daily at specified time"),
			"description" => __("If you want Cacti to export static images on an daily basis, put here the time when to do that. Cacti assumes that you run the data gathering script every 5 minutes, so it will round your value to the one closest to its runtime. For instance, 21:23 would equal 20 minutes after 9 PM."),
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_hdr_ftp" => array(
			"friendly_name" => __("FTP Options"),
			"method" => "spacer",
			),
		"export_ftp_sanitize" => array(
			"friendly_name" => __("Sanitize remote directory"),
			"description" => __("Check this if you want to delete any existing files in the FTP remote directory. This option is in use only when using the PHP built-in ftp functions."),
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_device" => array(
			"friendly_name" => __("FTP Host"),
			"description" => __("Denotes the device to upload your graphs by ftp."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_port" => array(
			"friendly_name" => __("FTP Port"),
			"description" => __("Communication port with the ftp server (leave empty for defaults). Default: 21."),
			"method" => "textbox",
			"max_length" => "10",
			"size" => "5"
			),
		"export_ftp_passive" => array(
			"friendly_name" => __("Use passive mode"),
			"description" => __("Check this if you want to connect in passive mode to the FTP server."),
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_user" => array(
			"friendly_name" => __("FTP User"),
			"description" => __("Account to logon on the remote server (leave empty for defaults). Default: Anonymous."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_password" => array(
			"friendly_name" => __("FTP Password"),
			"description" => __("Password for the remote ftp account (leave empty for blank)."),
			"method" => "textbox_password",
			"max_length" => "255"
			)
		),
	"visual" => array(
		"graphmgmt_header" => array(
			"friendly_name" => __("Graph Management"),
			"method" => "spacer",
			),
		"num_rows_graph" => array(
			"friendly_name" => __("Rows Per Page"),
			"description" => __("The number of rows to display on a single page for graph management."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"max_title_graph" => array(
			"friendly_name" => __("Maximum Title Length"),
			"description" => __("The maximum number of characters to display for a graph title."),
			"method" => "textbox",
			"default" => "80",
			"max_length" => "10",
			"size" => "5"
			),
		"dataqueries_header" => array(
			"friendly_name" => __("Data Queries"),
			"method" => "spacer",
			),
		"max_data_query_field_length" => array(
			"friendly_name" => __("Maximum Field Length"),
			"description" => __("The maximum number of characters to display for a data query field."),
			"method" => "textbox",
			"default" => "15",
			"max_length" => "10",
			"size" => "5"
			),
		"graphs_new_header" => array(
			"friendly_name" => __("Graph Creation"),
			"method" => "spacer",
			),
		"default_graphs_new_dropdown" => array(
			"friendly_name" => __("Default Dropdown Selector"),
			"description" => __("When creating graphs, how would you like the page to appear by default"),
			"method" => "drop_array",
			"default" => "-2",
			"array" => array("-2" => __("All Types"), "-1" => "By Template/Data Query"),
			),
		"num_rows_data_query" => array(
			"friendly_name" => __("Data Query Graph Rows"),
			"description" => __("The maximum number Data Query rows to place on a page per Data Query.  This applies to the 'New Graphs' page."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"datasources_header" => array(
			"friendly_name" => __("Data Sources"),
			"method" => "spacer",
			),
		"num_rows_data_source" => array(
			"friendly_name" => __("Rows Per Page"),
			"description" => __("The number of rows to display on a single page for data sources."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"max_title_data_source" => array(
			"friendly_name" => __("Maximum Title Length"),
			"description" => __("The maximum number of characters to display for a data source title."),
			"method" => "textbox",
			"default" => "45",
			"max_length" => "10",
			"size" => "5"
			),
		"devices_header" => array(
			"friendly_name" => __("Devices"),
			"method" => "spacer",
			),
		"num_rows_device" => array(
			"friendly_name" => __("Rows Per Page"),
			"description" => __("The number of rows to display on a single page for devices."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"sites_header" => array(
			"friendly_name" => __("Sites"),
			"method" => "spacer",
			),
		"num_rows_sites" => array(
			"friendly_name" => __("Rows Per Page"),
			"description" => __("The number of rows to display on a single page for sites."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $item_rows
			),
		"logmgmt_header" => array(
			"friendly_name" => __("Log Management"),
			"method" => "spacer",
			),
		"num_rows_log" => array(
			"friendly_name" => __("Default Log File Tail Lines"),
			"description" => __("How many lines of the Cacti log file to you want to tail, by default."),
			"method" => "drop_array",
			"default" => 500,
			"array" => $log_tail_lines,
			),
		"log_refresh_interval" => array(
			"friendly_name" => __("Log File Tail Refresh"),
			"description" => __("How many often do you want the Cacti log display to update."),
			"method" => "drop_array",
			"default" => 60,
			"array" => $page_refresh_interval,
			),
		"fonts_header" => array(
			"friendly_name" => __("Default RRDtool 1.2.x++ Fonts"),
			"method" => "spacer",
			),
		"title_size" => array(
			"friendly_name" => __("Title Font Size"),
			"description" => __("The size of the font used for Graph Titles"),
			"method" => "textbox",
			"default" => "12",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"title_font" => array(
			"friendly_name" => __("Title Font File"),
			"description" => __("The font file to use for Graph Titles"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"legend_size" => array(
			"friendly_name" => __("Legend Font Size"),
			"description" => __("The size of the font used for Graph Legend items"),
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"legend_font" => array(
			"friendly_name" => __("Legend Font File"),
			"description" => __("The font file to be used for Graph Legend items"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"axis_size" => array(
			"friendly_name" => __("Axis Font Size"),
			"description" => __("The size of the font used for Graph Axis"),
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"axis_font" => array(
			"friendly_name" => __("Axis Font File"),
			"description" => __("The font file to be used for Graph Axis items"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"unit_size" => array(
			"friendly_name" => __("Unit Font Size"),
			"description" => __("The size of the font used for Graph Units"),
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"unit_font" => array(
			"friendly_name" => __("Unit Font File"),
			"description" => __("The font file to be used for Graph Unit items"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"watermark_size" => array(
			"friendly_name" => __("Watermark Font Size"),
			"description" => __("The size of the font used for Graph Watermarks"),
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"watermark_font" => array(
			"friendly_name" => __("Watermark Font File"),
			"description" => __("The font file to be used for Graph Watermarks"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"colortags_header" => array(
			"friendly_name" => __("Default RRDtool Colortags"),
			"method" => "spacer",
			),
		"colortag_sequence" => array(
			"friendly_name" => __("Sequence for using Colortags"),
			"description" => __("Colortags are available for Global/Custom/Template settings. Select the sequence to decide the priority of each"),
			"method" => "drop_array",
			"default" => COLORTAGS_UTG,
			"array" => $colortag_sequence,
			),
		"colortag_back" => array(
			"friendly_name" => __("Background (--color BACK)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the background (rrggbb[aa])."),
			),
		"colortag_canvas" => array(
			"friendly_name" => __("Canvas (--color CANVAS)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the background of the actual graph (rrggbb[aa])."),
			),
		"colortag_shadea" => array(
			"friendly_name" => __("ShadeA (--color SHADEA)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the left and top border (rrggbb[aa])."),
			),
		"colortag_shadeb" => array(
			"friendly_name" => __("ShadeB (--color SHADEB)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the right and bottom border (rrggbb[aa])."),
			),
		"colortag_grid" => array(
			"friendly_name" => __("Grid (--color GRID)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the grid (rrggbb[aa])."),
			),
		"colortag_mgrid" => array(
			"friendly_name" => __("Major Grid (--color MGRID)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the major grid (rrggbb[aa])."),
			),
		"colortag_font" => array(
			"friendly_name" => __("Font (--color FONT)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the font (rrggbb[aa])."),
			),
		"colortag_axis" => array(
			"friendly_name" => __("Axis (--color AXIS)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the axis (rrggbb[aa])."),
			),
		"colortag_frame" => array(
			"friendly_name" => __("Frame (--color FRAME)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the frame (rrggbb[aa])."),
			),
		"colortag_arrow" => array(
			"friendly_name" => __("Arrow (--color ARROW)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the arrow (rrggbb[aa])."),
			),
		),
	"poller" => array(
		"poller_header" => array(
			"friendly_name" => __("General"),
			"method" => "spacer",
			),
		"poller_enabled" => array(
			"friendly_name" => __("Enabled"),
			"description" => __("If you wish to stop the polling process, uncheck this box."),
			"method" => "checkbox",
			"default" => CHECKED,
			"tab" => "poller"
			),
		"poller_type" => array(
			"friendly_name" => __("Poller Type"),
			"description" => __("The poller type to use. This setting will take effect at next polling interval."),
			"method" => "drop_array",
			"default" => 1,
			"array" => $poller_options,
			),
		"poller_interval" => array(
			"friendly_name" => __("Poller Interval"),
			"description" => __("The polling interval in use. This setting will effect how often rrd's are checked and updated.") .
							"<br /><strong><u>" .
							__("NOTE: If you change this value, you must re-populate the poller cache.") .
							"<br />" .
							__("Make sure to select the appropriate set of RRAs") .
							"<br />" .
							__("Failure to do so will result in lost data.") .
							"</u></strong>",
			"method" => "drop_array",
			"default" => 300,
			"array" => $poller_intervals,
			),
		"cron_interval" => array(
			"friendly_name" => __("Cron Interval"),
			"description" => __("The cron interval in use.  You need to set this setting to the interval that your cron or scheduled task is currently running."),
			"method" => "drop_array",
			"default" => 300,
			"array" => $cron_intervals,
			),
		"concurrent_processes" => array(
			"friendly_name" => __("Maximum Concurrent Poller Processes"),
			"description" => __("The number of concurrent processes to execute.  Using a higher number when using cmd.php will improve performance.  Performance improvements in spine are best resolved with the threads parameter"),
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"process_leveling" => array(
			"friendly_name" => __("Balance Process Load"),
			"description" => __("If you choose this option, Cacti will attempt to balance the load of each poller process by equally distributing poller items per process."),
			"method" => "checkbox",
			"default" => CHECKED
			),
		"spine_header" => array(
			"friendly_name" => __("Spine Specific Execution Parameters"),
			"method" => "spacer",
			),
		"max_threads" => array(
			"friendly_name" => __("Maximum Threads per Process"),
			"description" => __("The maximum threads allowed per process.  Using a higher number when using Spine will improve performance."),
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"php_servers" => array(
			"friendly_name" => __("Number of PHP Script Servers"),
			"description" => __("The number of concurrent script server processes to run per Spine process.  Settings between 1 and 10 are accepted.  This parameter will help if you are running several threads and script server scripts."),
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"script_timeout" => array(
			"friendly_name" => __("Script and Script Server Timeout Value"),
			"description" => __("The maximum time that Cacti will wait on a script to complete.  This timeout value is in seconds"),
			"method" => "textbox",
			"default" => "25",
			"max_length" => "10",
			"size" => "5"
			),
		"max_get_size" => array(
			"friendly_name" => __("The Maximum SNMP OID's Per SNMP Get Request"),
			"description" => __("The maximum number of snmp get OID's to issue per snmpbulkwalk request.  Increasing this value speeds poller performance over slow links.  The maximum value is 60 OID's.  Decreasing this value to 0 or 1 will disable snmpbulkwalk"),
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10",
			"size" => "5"
			),
		),
	"avail" => array(
		"availability_header" => array(
			"friendly_name" => __("Device Availability Settings"),
			"method" => "spacer",
			),
		"availability_method" => array(
			"friendly_name" => __("Downed Device Detection"),
			"description" => __("The method Cacti will use to determine if a device is available for polling.  <br><i>NOTE: It is recommended that, at a minimum, SNMP always be selected.</i>"),
			"method" => "drop_array",
			"default" => AVAIL_SNMP,
			"array" => $availability_options,
			),
		"ping_method" => array(
			"friendly_name" => __("Ping Type"),
			"description" => __("The type of ping packet to sent.  <br><i>NOTE: ICMP requires that the Cacti Service ID have root privilages in Unix.</i>"),
			"method" => "drop_array",
			"default" => PING_UDP,
			"array" => $ping_methods,
			),
		"ping_port" => array(
			"friendly_name" => __("Ping Port"),
			"description" => __("When choosing either TCP or UDP Ping, which port should be checked for availability of the device prior to polling."),
			"method" => "textbox",
			"default" => "23",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_timeout" => array(
			"friendly_name" => __("Ping Timeout Value"),
			"description" => __("The timeout value to use for device ICMP and UDP pinging.  This device SNMP timeout value applies for SNMP pings."),
			"method" => "textbox",
			"default" => "400",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_retries" => array(
			"friendly_name" => __("Ping Retry Count"),
			"description" => __("The number of times Cacti will attempt to ping a device before failing."),
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10",
			"size" => "5"
			),
		"updown_header" => array(
			"friendly_name" => __("Device Up/Down Settings"),
			"method" => "spacer",
			),
		"ping_failure_count" => array(
			"friendly_name" => __("Failure Count"),
			"description" => __("The number of polling intervals a device must be down before logging an error and reporting device as down."),
			"method" => "textbox",
			"default" => "2",
			"max_length" => "10",
			"size" => "5"
			),
		"ping_recovery_count" => array(
			"friendly_name" => __("Recovery Count"),
			"description" => __("The number of polling intervals a device must remain up before returning device to an up status and issuing a notice."),
			"method" => "textbox",
			"default" => "3",
			"max_length" => "10",
			"size" => "5"
			)
		),
	"authentication" => array(
		"general_header" => array(
			"friendly_name" => __("General"),
			"method" => "spacer",
			),
		"auth_method" => array(
			"friendly_name" => __("Authentication Method"),
			"description" => __("<blockquote><i>None</i> - No authentication will be used, all users will have full access.<br><br><i>Builtin Authentication</i> - Cacti handles user authentication, which allows you to create users and give them rights to different areas within Cacti.<br><br><i>Web Basic Authentication</i> - Authentication is handled by the web server. Users can be added or created automatically on first login if the Template User is defined, otherwise the defined guest permissions will be used.<br><br><i>LDAP Authentication</i> - Allows for authentication against a LDAP server. Users will be created automatically on first login if the Template User is defined, otherwise the defined guest permissions will be used.  If PHP's LDAP module is not enabled, LDAP Authentication will not appear as a selectable option.</blockquote>"),
			"method" => "drop_array",
			"default" => 1,
			"array" => $auth_methods
			),
		"special_users_header" => array(
			"friendly_name" => __("Special Users"),
			"method" => "spacer",
			),
		"guest_user" => array(
			"friendly_name" => __("Guest User"),
			"description" => __("The name of the guest user for viewing graphs; is \"No User\" by default."),
			"method" => "drop_sql",
			"none_value" => __("No User"),
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "0"
			),
		"user_template" => array(
			"friendly_name" => __("User Template"),
			"description" => __("The name of the user that cacti will use as a template for new Web Basic and LDAP users; is \"guest\" by default."),
			"method" => "drop_sql",
			"none_value" => __("No User"),
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "0"
			),
		"ldap_general_header" => array(
			"friendly_name" => __("LDAP General Settings"),
			"method" => "spacer"
			),
		"ldap_server" => array(
			"friendly_name" => __("Server"),
			"description" => __("The dns hostname or ip address of the server."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_port" => array(
			"friendly_name" => __("Port Standard"),
			"description" => __("TCP/UDP port for Non SSL communications."),
			"method" => "textbox",
			"max_length" => "5",
			"default" => "389",
			"size" => "5"
			),
		"ldap_port_ssl" => array(
			"friendly_name" => __("Port SSL"),
			"description" => __("TCP/UDP port for SSL communications."),
			"method" => "textbox",
			"max_length" => "5",
			"default" => "636",
			"size" => "5"
			),
		"ldap_version" => array(
			"friendly_name" => __("Protocol Version"),
			"description" => __("Protocol Version that the server supports."),
			"method" => "drop_array",
			"default" => "3",
			"array" => $ldap_versions
			),
		"ldap_encryption" => array(
			"friendly_name" => __("Encryption"),
			"description" => __("Encryption that the server supports. TLS is only supported by Protocol Version 3."),
			"method" => "drop_array",
			"default" => "0",
			"array" => $ldap_encryption
			),
		"ldap_referrals" => array(
			"friendly_name" => __("Referrals"),
			"description" => __("Enable or Disable LDAP referrals.  If disabled, it may increase the speed of searches."),
			"method" => "drop_array",
			"default" => "0",
			"array" => array( "0" => __("Disabled"), "1" => __("Enabled"))
			),
		"ldap_mode" => array(
			"friendly_name" => __("Mode"),
		"description" => __("Mode which cacti will attempt to authenicate against the LDAP server.<blockquote><i>No Searching</i> - No Distinguished Name (DN) searching occurs, just attempt to bind with the provided Distinguished Name (DN) format.<br><br><i>Anonymous Searching</i> - Attempts to search for username against LDAP directory via anonymous binding to locate the users Distinguished Name (DN).<br><br><i>Specific Searching</i> - Attempts search for username against LDAP directory via Specific Distinguished Name (DN) and Specific Password for binding to locate the users Distinguished Name (DN).</blockquote>"),
			"method" => "drop_array",
			"default" => "0",
			"array" => $ldap_modes
			),
		"ldap_dn" => array(
			"friendly_name" => __("Distinguished Name (DN)"),
			"description" => __("Distinguished Name syntax, such as <blockquote>for windows: <br><i>\"&lt;username&gt;@win2kdomain.local\"</i> or <br><br>for OpenLDAP: <br><i>\"uid=&lt;username&gt;,ou=people,dc=domain,dc=local\"</i>.   <br><br>\"&lt;username&gt\" is replaced with the username that was supplied at the login prompt.  This is only used when in \"No Searching\" mode.</blockquote>"),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_require" => array(
			"friendly_name" => __("Require Group Membership"),
			"description" => __("Require user to be member of group to authenicate. Group settings must be set for this to work, enabling without proper group settings will cause authenication failure."),
			"default" => "",
			"method" => "checkbox"
			),
		"ldap_group_header" => array(
			"friendly_name" => __("LDAP Group Settings"),
			"method" => "spacer"
			),
		"ldap_group_dn" => array(
			"friendly_name" => __("Group Distingished Name (DN)"),
			"description" => __("Distingished Name of the group that user must have membership."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_attrib" => array(
			"friendly_name" => __("Group Member Attribute"),
			"description" => __("Name of the attribute that contains the usernames of the members."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_group_member_type" => array(
			"friendly_name" => __("Group Member Type"),
			"description" => __("Defines if users use full Distingished Name or just Username in the defined Group Member Attribute."),
			"method" => "drop_array",
			"default" => 1,
			"array" => array( 1 => __("Distingished Name"), 2 => "Username" )
			),
		"ldap_search_base_header" => array(
			"friendly_name" => __("LDAP Specific Search Settings"),
			"method" => "spacer"
			),
		"ldap_search_base" => array(
			"friendly_name" => __("Search Base"),
			"description" => __("Search base for searching the LDAP directory, such as <br><i>\"dc=win2kdomain,dc=local\"</i> <br>or <br><i>\"ou=people,dc=domain,dc=local\"</i>."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_search_filter" => array(
			"friendly_name" => __("Search Filter"),
			"description" => __("Search filter to use to locate the user in the LDAP directory, such as <br>for windows: <br><i>\"(&amp;(objectclass=user)(objectcategory=user)(userPrincipalName=&lt;username&gt;*))\"</i> or <br>for OpenLDAP: <br><i>\"(&(objectClass=account)(uid=&lt;username&gt))\"</i>.  <br>\"&lt;username&gt\" is replaced with the username that was supplied at the login prompt. "),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_specific_dn" => array(
			"friendly_name" => __("Search Distingished Name (DN)"),
			"description" => __("Distinguished Name for Specific Searching binding to the LDAP directory."),
			"method" => "textbox",
			"max_length" => "255"
			),
		"ldap_specific_password" => array(
			"friendly_name" => __("Search Password"),
			"description" => __("Password for Specific Searching binding to the LDAP directory."),
			"method" => "textbox_password",
			"max_length" => "255"
			)
		),
	"logging" => array(
		"log_header" => array(
			"friendly_name" => _("Event Logging"),
			"method" => "spacer"
			),
		"log_destination" => array(
			"friendly_name" => _("Log Destination"),
					"description" => _("<blockquote><i>Cacti System Log</i> - Internal cacti system log utilizing the database.<br><br><i>Localhost System Log</i> - Utilizing PHP syslog to log to the localhosts logs.<br><br><i>Syslog Server</i> - Syslog to local or remote syslog server</blockquote>"),
			"method" => "checkbox_group",
			"items" => array(
				"log_dest_cacti" => array(
					"friendly_name" => _("Cacti System Log"),
					"default" => "on"
				),
				"log_dest_system" => array(
					"friendly_name" => _("Localhost System Log"),
					"default" => ""
					),
				"log_dest_syslog" => array(
					"friendly_name" => _("Syslog Server"),
					"default" => ""
					)
				)
			),
		"log_severity" => array(
			"friendly_name" => _("Severity Logging Level"),
			"description" => _("Level of detail to send to log.  When selecting a severity level, every level above that will be logged as well."),
			"method" => "drop_array",
			"default" => CACTI_LOG_SEV_NOTICE,
			"array" => $log_level
			),
		"log_control_header" => array(
			"friendly_name" => _("Cacti System Log Size and Control"),
			"method" => "spacer"
			),
		"log_size" => array(
			"friendly_name" => _("Maximum Log Size"),
			"description" => _("The maximum number of records to store in the Cacti log.  The log will be pruned after each polling cycle.  The maximum number of records is an approximate value due to the nature of the record count check."),
			"default" => "1000000",
			"method" => "textbox",
			"max_length" => "10"
			),
		"log_control" => array(
			"friendly_name" => _("Log Control Mechanism"),
			"description" => _("How Cacti controls the log size.  The default is to overwrite as needed."),
			"method" => "drop_array",
			"default" => 1,
			"array" => $log_control_options
			),
		"log_maxdays" => array(
			"friendly_name" => _("Maximum Retention Period"),
			"description" => _("All events older than the specified number of days will be discarded if the maximum number of recrods in the Cacti System Log is reached."),
			"method" => "textbox",
			"default" => "7",
			"max_length" => "3"
			),
		"log_system_header" => array(
			"friendly_name" => _("System Log Settings"),
			"method" => "spacer"
			),
		"log_system_facility" => array(
			"friendly_name" => _("System Syslog Facility"),
			"description" => _("Facility to utilize when using syslog. For Windows enviroments set to USER."),
			"method" => "drop_array",
			"default" => LOG_USER,
			"array" => $log_system_facility
			),
		"log_syslog_header" => array(
			"friendly_name" => _("Syslog Server Settings"),
			"method" => "spacer"
			),
		"log_syslog_server" => array(
			"friendly_name" => _("Syslog Server"),
			"description" => _("Syslog Server to send syslog message to. To use TCP port, proceed server with \"tcp://\"."),
			"method" => "textbox",
			"default" => "localhost",
			"max_length" => "255"
			),
		"log_syslog_port" => array(
			"friendly_name" => _("Syslog Server Port"),
			"description" => _("Syslog Server port to send syslog message to."),
			"method" => "textbox",
			"default" => "514",
			"max_length" => "5"
			),
		"log_syslog_facility" => array(
			"friendly_name" => _("Syslog Facility"),
			"description" => _("Facility to utilize when using syslog. For Windows enviroments set to USER."),
			"method" => "drop_array",
			"default" => LOG_USER,
			"array" => $log_syslog_facility
			),
		"log_email_header" => array(
			"friendly_name" => _("Event Log Emailing"),
			"method" => "spacer"
			),
		"log_email_level" => array(
			"friendly_name" => _("Log Email Level"),
			"description" => _("Level of detail to send in email.  When selecting a severity level, every level above that will be logged as well."),
			"method" => "drop_array",
			"default" => CACTI_LOG_SEV_CRITICAL,
			"array" => $log_level
			),
		"log_email_group" => array(
			"friendly_name" => _("Email Group"),
			"description" => _("Events will be emailed in batches to the assigned user group."),
			"method" => "drop_array",
			"default" => "",
			"array" => array()
			)
		)
	);

$settings_graphs = array(
	"general" => array(
		"default_rra_id" => array(
			"friendly_name" => __("Default RRA"),
			"description" => __("The default RRA to use when thumbnail graphs are not being displayed or when 'Thumbnail Timespan' is set to '0'."),
			"method" => "drop_sql",
			"sql" => "select id,name from rra order by timespan",
			"default" => "1"
			),
		"default_view_mode" => array(
			"friendly_name" => __("Default View Mode"),
			"description" => __("Which mode you want displayed when you visit 'graph_view.php'"),
			"method" => "drop_array",
			"array" => $graph_views,
			"default" => GRAPH_TREE_VIEW
			),
		"default_timespan" => array(
			"friendly_name" => __("Default Graph View Timespan"),
			"description" => __("The default timespan you wish to be displayed when you display graphs"),
			"method" => "drop_array",
			"array" => $graph_timespans,
			"default" => GT_LAST_DAY
			),
		"timespan_sel" => array(
			"friendly_name" => __("Display Graph View Timespan Selector"),
			"description" => __("Choose if you want the time span selection box to be displayed."),
			"method" => "checkbox",
			"default" => CHECKED
		),
		"default_timeshift" => array(
			"friendly_name" => __("Default Graph View Timeshift"),
			"description" => __("The default timeshift you wish to be displayed when you display graphs"),
			"method" => "drop_array",
			"array" => $graph_timeshifts,
			"default" => GTS_1_DAY
			),
		"allow_graph_dates_in_future" => array(
			"friendly_name" => __("Allow Graph to extend to Future"),
			"description" => __("When displaying Graphs, allow Graph Dates to extend 'to future'"),
			"method" => "checkbox",
			"default" => CHECKED
		),
		"first_weekdayid" => array(
			"friendly_name" => __("First Day of the Week"),
			"description" => __("The first Day of the Week for weekly Graph Displays"),
			"method" => "drop_array",
			"array" => $graph_weekdays,
			"default" => WD_MONDAY
			),
		"day_shift_start" => array(
			"friendly_name" => __("Start of Daily Shift"),
			"description" => __("Start Time of the Daily Shift."),
			"method" => "textbox",
			"default" => "07:00",
			"max_length" => "5"
			),
		"day_shift_end" => array(
			"friendly_name" => __("End of Daily Shift"),
			"description" => __("End Time of the Daily Shift."),
			"method" => "textbox",
			"default" => "18:00",
			"max_length" => "5"
			),
		"default_date_format" => array(
			"friendly_name" => __("Graph Date Display Format"),
			"description" => __("The date format to use for graphs"),
			"method" => "drop_array",
			"array" => $graph_dateformats,
			"default" => GD_Y_MO_D
			),
		"default_datechar" => array(
			"friendly_name" => __("Graph Date Separator"),
			"description" => __("The date separator to be used for graphs"),
			"method" => "drop_array",
			"array" => $graph_datechar,
			"default" => GDC_SLASH
			),
		"page_refresh" => array(
			"friendly_name" => __("Page Refresh"),
			"description" => __("The number of seconds between automatic page refreshes."),
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			)
		),
	"thumbnail" => array(
		"default_height" => array(
			"friendly_name" => __("Thumbnail Height"),
			"description" => __("The height of thumbnail graphs in pixels."),
			"method" => "textbox",
			"default" => "100",
			"max_length" => "10"
			),
		"default_width" => array(
			"friendly_name" => __("Thumbnail Width"),
			"description" => __("The width of thumbnail graphs in pixels."),
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			),
		"num_columns" => array(
			"friendly_name" => __("Thumbnail Columns"),
			"description" => __("The number of columns to use when displaying thumbnail graphs."),
			"method" => "textbox",
			"default" => "2",
			"max_length" => "5"
			),
		"thumbnail_sections" => array(
			"friendly_name" => __("Thumbnail Sections"),
			"description" => __("Which sections of Cacti thumbnail graphs should be used for."),
			"method" => "checkbox_group",
			"items" => array(
				"thumbnail_section_preview" => array(
					"friendly_name" => __("Preview Mode"),
					"default" => CHECKED
					),
				"thumbnail_section_tree_1" => array(
					"friendly_name" => __("Tree View (Single Pane)"),
					"default" => CHECKED
					),
				"thumbnail_section_tree_2" => array(
					"friendly_name" => __("Tree View (Dual Pane)"),
					"default" => ""
					)
				)
			)
		),
	"tree" => array(
		"default_tree_id" => array(
			"friendly_name" => __("Default Graph Tree"),
			"description" => __("The default graph tree to use when displaying graphs in tree mode."),
			"method" => "drop_sql",
			"sql" => "select id,name from graph_tree order by name",
			"default" => "0"
			),
		"treeview_graphs_per_page" => array(
			"friendly_name" => __("Graphs Per-Page"),
			"description" => __("The number of graphs to display on one page in preview mode."),
			"method" => "drop_array",
			"default" => "10",
			"array" => $graphs_per_page
			),
		"default_dual_pane_width" => array(
			"friendly_name" => __("Dual Pane Tree Width"),
			"description" => __("When choosing dual pane Tree View, what width should the tree occupy in pixels."),
			"method" => "textbox",
			"max_length" => "5",
			"default" => "200"
			),
		"expand_devices" => array(
			"friendly_name" => __("Expand Devices"),
			"description" => __("Choose whether to expand the graph templates used for a device on the dual pane tree."),
			"method" => "checkbox",
			"default" => ""
			),
		"show_graph_title" => array(
			"friendly_name" => __("Show Graph Title"),
			"description" => __("Display the graph title on the page so that it may be searched using the browser."),
			"method" => "checkbox",
			"default" => ""
			)
		),
	"preview" => array(
		"preview_graphs_per_page" => array(
			"friendly_name" => __("Graphs Per-Page"),
			"description" => __("The number of graphs to display on one page in preview mode."),
			"method" => "drop_array",
			"default" => "10",
			"array" => $graphs_per_page
			)
		),
	"list" => array(
		"list_graphs_per_page" => array(
			"friendly_name" => __("Graphs Per-Page"),
			"description" => __("The number of graphs to display on one page in list view mode."),
			"method" => "drop_array",
			"default" => "30",
			"array" => $graphs_per_page
			)
		),
	"fonts" => array(
		"custom_fonts" => array(
			"friendly_name" => __("Use Custom Fonts"),
			"description" => __("Choose whether to use your own custom fonts and font sizes or utilize the system defaults."),
			"method" => "checkbox",
			"default" => "",
			"class" => "not_RRD_1_0_x",
			),
		"title_size" => array(
			"friendly_name" => __("Title Font Size"),
			"description" => __("The size of the font used for Graph Titles"),
			"class" => "custom_fonts",
			"method" => "textbox",
			"default" => "12",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"title_font" => array(
			"friendly_name" => __("Title Font File"),
			"description" => __("The font file to use for Graph Titles"),
			"class" => "custom_fonts",
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"legend_size" => array(
			"friendly_name" => __("Legend Font Size"),
			"description" => __("The size of the font used for Graph Legend items"),
			"class" => "custom_fonts",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"legend_font" => array(
			"friendly_name" => __("Legend Font File"),
			"description" => __("The font file to be used for Graph Legend items"),
			"class" => "custom_fonts",
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"axis_size" => array(
			"friendly_name" => __("Axis Font Size"),
			"description" => __("The size of the font used for Graph Axis"),
			"class" => "custom_fonts",
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"axis_font" => array(
			"friendly_name" => __("Axis Font File"),
			"description" => __("The font file to be used for Graph Axis items"),
			"class" => "custom_fonts",
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"unit_size" => array(
			"friendly_name" => __("Unit Font Size"),
			"description" => __("The size of the font used for Graph Units"),
			"class" => "custom_fonts",
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"unit_font" => array(
			"friendly_name" => __("Unit Font File"),
			"description" => __("The font file to be used for Graph Unit items"),
			"class" => "custom_fonts",
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		"watermark_size" => array(
			"friendly_name" => __("Watermark Font Size"),
			"description" => __("The size of the font used for Graph Watermarks"),
			"method" => "textbox",
			"default" => "8",
			"max_length" => "10",
			"size" => "5",
			"class" => "not_RRD_1_0_x",
			),
		"watermark_font" => array(
			"friendly_name" => __("Watermark Font File"),
			"description" => __("The font file to be used for Graph Watermarks"),
			"method" => "font",
			"max_length" => "100",
			"class" => "not_RRD_1_0_x",
			),
		),
	"colortags" => array(
		"custom_colortags" => array(
			"friendly_name" => __("Use Custom Colortags"),
			"description" => __("Choose whether to use your own custom colortags or utilize the system defaults."),
			"method" => "checkbox",
			"default" => ""
			),
		"colortag_back" => array(
			"friendly_name" => __("Background (--color BACK)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the background (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_canvas" => array(
			"friendly_name" => __("Canvas (--color CANVAS)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the background of the actual graph (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_shadea" => array(
			"friendly_name" => __("ShadeA (--color SHADEA)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the left and top border (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_shadeb" => array(
			"friendly_name" => __("ShadeB (--color SHADEB)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the right and bottom border (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_grid" => array(
			"friendly_name" => __("Grid (--color GRID)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the grid (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_mgrid" => array(
			"friendly_name" => __("Major Grid (--color MGRID)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the major grid (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_font" => array(
			"friendly_name" => __("Font (--color FONT)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the font (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_axis" => array(
			"friendly_name" => __("Axis (--color AXIS)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the axis (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_frame" => array(
			"friendly_name" => __("Frame (--color FRAME)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the frame (rrggbb[aa])."),
			"class" => "colortags",
			),
		"colortag_arrow" => array(
			"friendly_name" => __("Arrow (--color ARROW)"),
			"method" => "textbox",
			"max_length" => "8",
			"default" => "",
			"size" => "8",
			"description" => __("Color tag of the arrow (rrggbb[aa])."),
			"class" => "colortags",
			),
		),
	);

