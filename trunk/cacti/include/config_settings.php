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

include($config["include_path"] . "/config_arrays.php");

/* tab information */
$tabs = array(
	"general" => "General",
	"snmp" => "SNMP",
	"path" => "Paths",
	"poller" => "Poller",
	"visual" => "Visual",
	"authentication" => "Authentication",
	"export" => "Graph Export");

$tabs_graphs = array(
	"general" => "General",
	"thumbnail" => "Graph Thumbnails",
	"tree" => "Tree View Mode",
	"preview" => "Preview Mode",
	"list" => "List View Mode");

/* setting information */
$settings = array(
	"path" => array(
		"dependent_header" => array(
			"friendly_name" => "Required Tools",
			"method" => "spacer"
			),
		"path_snmpwalk" => array(
			"friendly_name" => "snmpwalk Binary Path",
			"description" => "The path to your snmpwalk binary.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"path_snmpget" => array(
			"friendly_name" => "snmpget Binary Path",
			"description" => "The path to your snmpget binary.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"path_rrdtool" => array(
			"friendly_name" => "RRDTool Binary Path",
			"description" => "The path to the rrdtool binary.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"path_php_binary" => array(
			"friendly_name" => "PHP Binary Path",
			"description" => "The path to your PHP binary file (may require a php recompile to get this file).",
			"method" => "textbox",
			"max_length" => "255"
			),
		"logging_header" => array(
			"friendly_name" => "Logging",
			"method" => "spacer"
			),
		"path_cactilog" => array(
			"friendly_name" => "Cacti Log File Path",
			"description" => "The path to your Cacti log file (if blank, defaults to <path_cacti>/log/cacti.log)",
			"method" => "textbox",
			"default" => $config["base_path"] . "/log/cacti.log",
			"max_length" => "255"
			),
		"pollerpaths_header" => array(
			"friendly_name" => "Poller Path",
			"method" => "spacer"
			),
		"path_cactid" => array(
			"friendly_name" => "Cactid Poller File Path",
			"description" => "The path to Cactid binary.",
			"method" => "textbox",
			"max_length" => "255"
			)
		),
	"general" => array(
		"logging_header" => array(
			"friendly_name" => "Event Logging",
			"method" => "spacer"
			),
		"log_destination" => array(
			"friendly_name" => "Log File Destination",
			"description" => "How will Cacti handle event logging.",
			"method" => "drop_array",
			"default" => 1,
			"array" => $logfile_options
			),
		"web_log" => array(
			"friendly_name" => "Web Events",
			"description" => "What Cacti website messages should be placed in the log.",
			"method" => "checkbox_group",
			"tab" => "general",
			"items" => array(
				"log_snmp" => array(
					"friendly_name" => "Web SNMP Messages",
					"default" => ""
					),
				"log_graph" => array(
					"friendly_name" => "Web RRD Graph Syntax",
					"default" => ""
					),
				"log_export" => array(
					"friendly_name" => "Graph Export Messages",
					"default" => ""
					)
				),
			),
		"poller_header" => array(
			"friendly_name" => "Poller Specific Logging",
			"method" => "spacer"
			),
		"log_verbosity" => array(
			"friendly_name" => "Poller Logging Level",
			"description" => "What level of detail do you want sent to the log file.  WARNING: Leaving in any other status than NONE or LOW can exaust your disk space rapidly.",
			"method" => "drop_array",
			"default" => POLLER_VERBOSITY_LOW,
			"array" => $logfile_verbosity
			),
		"poller_log" => array(
			"friendly_name" => "Poller Syslog/Eventlog Selection",
			"description" => "If you are using the Syslog/Eventlog, What Cacti poller messages should be placed in the Syslog/Eventlog.",
			"method" => "checkbox_group",
			"tab" => "poller",
			"items" => array(
				"log_pstats" => array(
					"friendly_name" => "Poller Statsistics",
					"default" => ""
					),
				"log_pwarn" => array(
					"friendly_name" => "Poller Warnings",
					"default" => ""
					),
				"log_perror" => array(
					"friendly_name" => "Poller Errors",
					"default" => "on"
					)
				)
			),
		"db_header" => array(
			"friendly_name" => "Database Settings",
			"method" => "spacer"
			),
		"db_pconnections" => array(
			"friendly_name" => "Persistent Connections",
			"description" => "Utilize persistent connections to conserve database resources.",
			"default" => "on",
			"method" => "checkbox"
			),
		"db_retries" => array(
			"friendly_name" => "Database Retries",
			"description" => "The number of retries that Cacti will attempt to access the Cacti database prior to failing.",
			"method" => "textbox",
			"default" => "20",
			"max_length" => "3"
			),
		"php_header" => array(
			"friendly_name" => "PHP Settings",
			"method" => "spacer"
			),
		"max_memory" => array(
			"friendly_name" => "PHP Maximum Memory",
			"description" => "Maximum allowed memory for PHP processes in Megabytes",
			"method" => "textbox",
			"default" => "32",
			"max_length" => "30"
			),
		"max_execution_time" => array(
			"friendly_name" => "PHP Graph Timeout",
			"description" => "Maximum allowed time for a graph to render.  Will stop server from hanging during eroneous graphing operations.",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10"
			),
		"other_header" => array(
			"friendly_name" => "Other Defaults",
			"method" => "spacer"
			),
		"remove_verification" => array(
			"friendly_name" => "Remove Verification",
			"description" => "Prompt user before item deletion.",
			"default" => "on",
			"method" => "checkbox"
			)
		),
	"snmp" => array(
		"snmp_header" => array(
			"friendly_name" => "General Defaults",
			"method" => "spacer"
			),
		"snmp_version" => array(
			"friendly_name" => "SNMP Utility Version",
			"description" => "The type of SNMP you have installed.  Required if you are using SNMP v2c or don't have embedded SNMP support in PHP.",
			"method" => "drop_array",
			"default" => "net-snmp",
			"array" => $snmp_implimentations
			),
		"snmp_timeout" => array(
			"friendly_name" => "Timeout",
			"description" => "Default SNMP timeout in milli-seconds.",
			"method" => "textbox",
			"default" => "500",
			"max_length" => "100"
			),
		"snmp_port" => array(
			"friendly_name" => "Port Number",
			"description" => "Default UDP port to be used for SNMP Calls.  Typically 161.",
			"method" => "textbox",
			"default" => "161",
			"max_length" => "100"
			),
		"snmp_retries" => array(
			"friendly_name" => "Retries",
			"description" => "The number times the SNMP poller will attempt to reach the host before failing.",
			"method" => "textbox",
			"default" => "3",
			"max_length" => "100"
			),
		"snmp_ver" => array(
			"friendly_name" => "Version",
			"description" => "Default SNMP version for all new hosts.",
			"method" => "drop_array",
			"default" => "Version 1",
			"array" => $snmp_versions
			),
		"snmpv12c_header" => array(
			"friendly_name" => "v1/v2c Default",
			"method" => "spacer"
			),
		"snmp_community" => array(
			"friendly_name" => "Community",
			"description" => "Default SNMP read community for all new hosts.",
			"method" => "textbox",
			"default" => "public",
			"max_length" => "100"
			),
		"snmpv3_auth_header" => array(
			"friendly_name" => "v3 Authentication Defaults",
			"method" => "spacer"
			),
		"snmpv3_auth_username" => array(
			"friendly_name" => "Username",
			"description" => "The default SNMP v3 username.",
			"method" => "textbox",
			"default" => "",
			"max_length" => "100"
			),
		"snmpv3_auth_password" => array(
			"friendly_name" => "Password",
			"description" => "The default SNMP v3 password.",
			"method" => "textbox_password",
			"default" => "",
			"max_length" => "100"
			),
		"snmpv3_auth_protocol" => array(
			"friendly_name" => "Authentication Protocol",
			"description" => "Select the default SNMP v3 authentication protocol to use.",
			"method" => "drop_array",
			"default" => "MD5",
			"array" => $snmpv3_auth_protocol
			),
		"snmpv3_priv_header" => array(
			"friendly_name" => "v3 Privacy Defaults",
			"method" => "spacer"
			),
		"snmpv3_priv_passphrase" => array(
			"friendly_name" => "Privacy Passphrase",
			"description" => "The default SNMP v3 privacy passphrase.",
			"method" => "textbox_password",
			"default" => "",
			"max_length" => "100"
			),
		"snmpv3_priv_protocol" => array(
			"friendly_name" => "Privacy Protocol",
			"description" => "Select the default SNMP v3 privacy protocol to use.",
			"method" => "drop_array",
			"default" => "DES",
			"array" => $snmpv3_priv_protocol
			)
		),
	"export" => array(
		"export_hdr_general" => array(
			"friendly_name" => "General",
			"method" => "spacer"
			),
		"export_type" => array(
			"friendly_name" => "Export Method",
			"description" => "Choose which export method to use.",
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"disabled" => "Disabled (no exporting)",
						"local" => "Classic (local path)",
						"ftp_php" => "Ftp (remote) - use php functions",
						"ftp_ncftpput" => "Ftp (remote) - use ncftpput"
						)
			),
		"export_hdr_paths" => array(
			"friendly_name" => "Paths",
			"method" => "spacer"
			),
		"path_html_export" => array(
			"friendly_name" => "Export Path (both local and ftp)",
			"description" => "If you want Cacti to write static PNG's and HTML files to a directory when data is gathered, specify the location here. This feature is similar to MRTG, graphs do not have to be generated on the fly this way.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_hdr_timing" => array(
			"friendly_name" => "Timing",
			"method" => "spacer"
			),
		"export_timing" => array(
			"friendly_name" => "Export timing",
			"description" => "Choose when to export graphs.",
			"method" => "drop_array",
			"default" => "disabled",
			"array" => array(
						"disabled" => "Disabled",
						"classic" => "Classic (export every x times)",
						"export_hourly" => "Hourly at specified minutes",
						"export_daily" => "Daily at specified time"
						)
			),
		"path_html_export_skip" => array(
			"friendly_name" => "Export Every x Times",
			"description" => "If you don't want Cacti to export static images every 5 minutes, put another number here. For instance, 3 would equal every 15 minutes.",
			"method" => "textbox",
			"max_length" => "10"
			),
		"export_hourly" => array(
			"friendly_name" => "Hourly at specified minutes",
			"description" => "If you want Cacti to export static images on an hourly basis, put the minutes of the hour when to do that. Cacti assumes that you run the data gathering script every 5 minutes, so it will round your value to the one closest to its runtime. For instance, 43 would equal 40 minutes past the hour.",
			"method" => "textbox",
			"max_length" => "10"
			),
		"export_daily" => array(
			"friendly_name" => "Daily at specified time",
			"description" => "If you want Cacti to export static images on an daily basis, put here the time when to do that. Cacti assumes that you run the data gathering script every 5 minutes, so it will round your value to the one closest to its runtime. For instance, 21:23 would equal 20 minutes after 9 PM.",
			"method" => "textbox",
			"max_length" => "10"
			),
		"export_hdr_ftp" => array(
			"friendly_name" => "FTP Options",
			"method" => "spacer"
			),
		"export_ftp_sanitize" => array(
			"friendly_name" => "Sanitize remote directory",
			"description" => "Check this if you want to delete any existing files in the FTP remote directory. This option is in use only when using the PHP built-in ftp functions.",
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_host" => array(
			"friendly_name" => "FTP Host",
			"description" => "Denotes the host to upload your graphs by ftp.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_port" => array(
			"friendly_name" => "FTP Port",
			"description" => "Communication port with the ftp server (leave empty for defaults). Default: 21.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_passive" => array(
			"friendly_name" => "Use passive mode",
			"description" => "Check this if you want to connect in passive mode to the FTP server.",
			"method" => "checkbox",
			"max_length" => "255"
			),
		"export_ftp_user" => array(
			"friendly_name" => "FTP User",
			"description" => "Account to logon on the remote server (leave empty for defaults). Default: Anonymous.",
			"method" => "textbox",
			"max_length" => "255"
			),
		"export_ftp_password" => array(
			"friendly_name" => "FTP Password",
			"description" => "Password for the remote ftp account (leave empty for blank).",
			"method" => "textbox_password",
			"max_length" => "255"
			)
		),
	"visual" => array(
		"themes_header" => array(
			"friendly_name" => "Cacti Theme",
			"method" => "spacer"
			),
		"current_theme" => array(
			"friendly_name" => "Cacti Theme to Use",
			"description" => "The Cacti color theme to use.  Changes the default look of Cacti.",
			"method" => "drop_array",
			"default" => "classic",
			"array" => $themes
			),
		"graphmgmt_header" => array(
			"friendly_name" => "Graph Management",
			"method" => "spacer"
			),
		"num_rows_graph" => array(
			"friendly_name" => "Rows Per Page",
			"description" => "The number of rows to display on a single page for graph management.",
			"method" => "textbox",
			"default" => "30",
			"max_length" => "10"
			),
		"max_title_graph" => array(
			"friendly_name" => "Maximum Title Length",
			"description" => "The maximum number of characters to display for a graph title.",
			"method" => "textbox",
			"default" => "80",
			"max_length" => "10"
			),
		"dataqueries_header" => array(
			"friendly_name" => "Data Queries",
			"method" => "spacer"
			),
		"max_data_query_field_length" => array(
			"friendly_name" => "Maximum Field Length",
			"description" => "The maximum number of characters to display for a data query field.",
			"method" => "textbox",
			"default" => "15",
			"max_length" => "10"
			),
		"max_data_query_javascript_rows" => array(
			"friendly_name" => "Maximum JavaScript Rows",
			"description" => "The maximum number of data query rows to display with JavaScript on the 'New Graphs' page.",
			"method" => "textbox",
			"default" => "96",
			"max_length" => "10"
			),
		"datasources_header" => array(
			"friendly_name" => "Data Sources",
			"method" => "spacer"
			),
		"num_rows_data_source" => array(
			"friendly_name" => "Rows Per Page",
			"description" => "The number of rows to display on a single page for data sources.",
			"method" => "textbox",
			"default" => "30",
			"max_length" => "10"
			),
		"max_title_data_source" => array(
			"friendly_name" => "Maximum Title Length",
			"description" => "The maximum number of characters to display for a data source title.",
			"method" => "textbox",
			"default" => "45",
			"max_length" => "10"
			),
		"hosts_header" => array(
			"friendly_name" => "Hosts",
			"method" => "spacer"
			),
		"num_rows_device" => array(
			"friendly_name" => "Rows Per Page",
			"description" => "The number of rows to display on a single page for Hosts.",
			"method" => "textbox",
			"default" => "30",
			"max_length" => "10"
			)
		),
	"poller" => array(
		"poller_header" => array(
			"friendly_name" => "General",
			"method" => "spacer"
			),
		"poller_enabled" => array(
			"friendly_name" => "Poller Enabled",
			"description" => "If you wish to stop the polling process, uncheck this box.",
			"method" => "checkbox",
			"default" => "on"
			),
		"poller_type" => array(
			"friendly_name" => "Poller Type",
			"description" => "The Cacti poller to use.  This Setting will take effect at next polling interval.",
			"method" => "drop_array",
			"default" => 1,
			"array" => $poller_options
			),
		"poller_graph" => array(
			"friendly_name" => "Graph Poller Statistics",
			"description" => "The statistical results of polling times will be saved to a special RRD file if selected.",
			"method" => "checkbox",
			"default" => "on"
			),
		"methods_header" => array(
			"friendly_name" => "Poller Execution Parameters",
			"method" => "spacer"
			),
		"concurrent_processes" => array(
			"friendly_name" => "Maximum Concurrent Poller Processes",
			"description" => "The number of concurrent processes to execute.  Using a higher number when using cmd.php will improve performance.  Performance improvements in cactid are best resolved with the threads parameter",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10"
			),
		"max_threads" => array(
			"friendly_name" => "Maximum Threads per Process",
			"description" => "The maximum threads allowed per process.  Using a higher number when using cactid will improve performance.  NOTE Applies only to CACTID!",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10"
			),
		"max_script_runtime" => array(
			"friendly_name" => "Maximum Script Runtime",
			"description" => "The maximum time, in seconds, allowed for a script or script server object to run before forcing a timeout of the script process.",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10"
			),
		"concurrent_rrd_processes" => array(
			"friendly_name" => "Maximum Concurrent RRDTool Processes",
			"description" => "The number of concurrent RRDTool processes to execute.  Using a will improve performance to a point.",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10"
			),
		"availability_header" => array(
			"friendly_name" => "Poller Host Availability Settings",
			"method" => "spacer"
			),
		"availability_method" => array(
			"friendly_name" => "Downed Host Detection",
			"description" => "The method Cacti will use to determine if a host is available for polling.  NOTE: It is recommended that, at a minimum, SNMP always be selected.",
			"method" => "drop_array",
			"default" => AVAIL_SNMP,
			"array" => $availability_options
			),
		"ping_method" => array(
			"friendly_name" => "Ping Type",
			"description" => "The type of ping packet to sent.  NOTE: ICMP requires that the Cacti Service ID have root privilages in Unix.",
			"method" => "drop_array",
			"default" => PING_UDP,
			"array" => $ping_methods
			),
		"ping_timeout" => array(
			"friendly_name" => "Ping Timeout Value",
			"description" => "The timeout value to use for host ICMP and UDP pinging.  This host SNMP timeout value applies for SNMP pings.",
			"method" => "textbox",
			"default" => "400",
			"max_length" => "10"
			),
		"ping_retries" => array(
			"friendly_name" => "Ping Retry Count",
			"description" => "The number of times Cacti will attempt to ping a host before failing.",
			"method" => "textbox",
			"default" => "1",
			"max_length" => "10"
			),
		"updown_header" => array(
			"friendly_name" => "Host Up/Down Settings",
			"method" => "spacer"
			),
		"ping_failure_count" => array(
			"friendly_name" => "Failure Count",
			"description" => "The number of polling intervals a host must be down before logging an error and reporting host as down.",
			"method" => "textbox",
			"default" => "2",
			"max_length" => "10"
			),
		"ping_recovery_count" => array(
			"friendly_name" => "Recovery Count",
			"description" => "The number of polling intervals a host must remain up before returning host to an up status and issuing a notice.",
			"method" => "textbox",
			"default" => "3",
			"max_length" => "10"
			)
		),
	"authentication" => array(
		"general_header" => array(
			"friendly_name" => "General",
			"method" => "spacer"
			),
		"auth_method" => array(
			"friendly_name" => "Authentication Method",
			"description" => "<ul><li><i>None</i> - No authentication will be used, all users will have full access.</li><li><i>Builtin Authentication</i> - Cacti handles user authentication, which allows you to create users and give them rights to different areas within Cacti.</li><li><i>Web Basic Authentication</i> - Authentication is handled by the web server. Users can be added or created automatically on first login if the Template User is defined, otherwise the defined guest permissions will be used.</li><li><i>LDAP Authentication</i> - Allows for authentication against a LDAP server. Users will be created automatically on first login if the Template User is defined, otherwise the defined guest permissions will be used.</li><ul>",
			"method" => "drop_array",
			"default" => 1,
			"array" => $auth_methods
			),
		"guest_user" => array(
			"friendly_name" => "Guest User",
			"description" => "The name of the guest user for viewing graphs; is \"guest\" by default.",
			"method" => "drop_sql",
			"none_value" => "No User",
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "guest"
			),
		"user_template" => array(
			"friendly_name" => "User Template",
			"description" => "The name of the user that cacti will use as a template for new Web Basic and LDAP users; is \"guest\" by default.",
			"method" => "drop_sql",
			"none_value" => "No User",
			"sql" => "select username as id, username as name from user_auth where realm = 0 order by username",
			"default" => "guest"
			),
		"expiration_header" => array (
			"friendly_name" => "Password Expiration",
			"method" => "spacer",
			),
		"password_expire_length" => array(
			"friendly_name" => "Default Password Expiration",
			"description" => "Applys when creating new users.  Only applys for Builtin Authentication.",
			"method" => "drop_array",
			"default" => 0,
			"array" => $user_password_expire_intervals
			),
		"password_expire_warning" => array(
			"friendly_name" => "User Expiration Warning",
			"description" => "Number of days to start warning the user that their password is about to expire. Only applys for Builtin Authentication.",
			"method" => "textbox",
			"default" => "15",
			"max_length" => "4"
			),
		"ldap_header" => array(
			"friendly_name" => "LDAP Settings",
			"method" => "spacer"
			),
		"ldap_server" => array(
			"friendly_name" => "LDAP Server",
			"description" => "The dns hostname or ip address of the server you wish to tie authentication from.",
			"method" => "textbox",
			"max_length" => "100"
			),
		"ldap_dn" => array(
			"friendly_name" => "LDAP DN",
			"description" => "This is the Distinguished Name syntax, such as &lt;username&gt;@win2kdomain.lcl.",
			"method" => "textbox",
			"max_length" => "100"
			)
		)
	);

$settings_graphs = array(
	"general" => array(
		"default_rra_id" => array(
			"friendly_name" => "Default RRA",
			"description" => "The default RRA to use when thumbnail graphs are not being displayed or when 'Thumbnail Timespan' is set to '0'.",
			"method" => "drop_sql",
			"sql" => "select id,name from rra order by name",
			"default" => "Default"
			),
		"default_view_mode" => array(
			"friendly_name" => "Default View Mode",
			"description" => "Which mode you want displayed when you visit 'graph_view.php'",
			"method" => "drop_array",
			"array" => $graph_views,
			"default" => "1"
			),
		"default_timespan" => array(
			"friendly_name" => "Default Graph View Timespan",
			"description" => "The default timespan you wish to be displayed when you display graphs",
			"method" => "drop_array",
			"array" => $graph_timespans,
			"default" => GT_LAST_DAY
			),
		"timespan_sel" => array(
			"friendly_name" => "Display Graph View Timespan Selector",
			"description" => "Choose if you want the time span selection box to be displayed.",
			"method" => "checkbox",
			"default" => "on"
			),
		"default_date_format" => array(
			"friendly_name" => "Graph Date Display Format",
			"description" => "The date format to use for graphs",
			"method" => "drop_array",
			"array" => $graph_dateformats,
			"default" => GD_Y_MO_D
			),
		"default_datechar" => array(
			"friendly_name" => "Graph Date Separator",
			"description" => "The date separator to be used for graphs",
			"method" => "drop_array",
			"array" => $graph_datechar,
			"default" => GDC_SLASH
			),
		"page_refresh" => array(
			"friendly_name" => "Page Refresh",
			"description" => "The number of seconds between automatic page refreshes.",
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			)
		),
	"thumbnail" => array(
		"default_height" => array(
			"friendly_name" => "Thumbnail Height",
			"description" => "The height of thumbnail graphs in pixels.",
			"method" => "textbox",
			"default" => "100",
			"max_length" => "10"
			),
		"default_width" => array(
			"friendly_name" => "Thumbnail Width",
			"description" => "The width of thumbnail graphs in pixels.",
			"method" => "textbox",
			"default" => "300",
			"max_length" => "10"
			),
		"num_columns" => array(
			"friendly_name" => "Thumbnail Columns",
			"description" => "The number of columns to use when displaying thumbnail graphs.",
			"method" => "textbox",
			"default" => "2",
			"max_length" => "5"
			),
		"thumbnail_sections" => array(
			"friendly_name" => "Thumbnail Sections",
			"description" => "Which sections of Cacti thumbnail graphs should be used for.",
			"method" => "checkbox_group",
			"items" => array(
				"thumbnail_section_preview" => array(
					"friendly_name" => "Preview Mode",
					"default" => "on"
					),
				"thumbnail_section_tree_1" => array(
					"friendly_name" => "Tree View (Single Pane)",
					"default" => "on"
					),
				"thumbnail_section_tree_2" => array(
					"friendly_name" => "Tree View (Dual Pane)",
					"default" => ""
					)
				)
			)
		),
	"tree" => array(
		"default_tree_id" => array(
			"friendly_name" => "Default Graph Tree",
			"description" => "The default graph tree to use when displaying graphs in tree mode.",
			"method" => "drop_sql",
			"sql" => "select id,name from graph_tree order by name",
			"default" => "0"
			),
		"default_tree_view_mode" => array(
			"friendly_name" => "Default Tree View Mode",
			"description" => "The default mode that will be used when viewing tree mode.",
			"method" => "drop_array",
			"array" => $graph_tree_views,
			"default" => "2"
			),
		"expand_hosts" => array(
			"friendly_name" => "Expand Hosts",
			"description" => "Choose whether to expand the graph templates used for a host on the dual pane tree.",
			"method" => "checkbox",
			"default" => ""
			)
		),
	"preview" => array(
		"preview_graphs_per_page" => array(
			"friendly_name" => "Graphs Per-Page",
			"description" => "The number of graphs to display on one page in preview mode.",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10"
			)
		),
	"list" => array(
		"list_graphs_per_page" => array(
			"friendly_name" => "Graphs Per-Page",
			"description" => "The number of graphs to display on one page in list view mode.",
			"method" => "textbox",
			"default" => "10",
			"max_length" => "10"
			)
		)
	);

?>
