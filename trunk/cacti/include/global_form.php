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

require(CACTI_BASE_PATH . "/include/global_arrays.php");
require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

if (!defined("VALID_HOST_FIELDS")) {
	define("VALID_HOST_FIELDS", "(hostname|snmp_community|snmpv3_auth_username|snmpv3_auth_password|snmpv3_auth_protocol|snmpv3_priv_passphrase|snmpv3_priv_protocol|snmp_version|snmp_port|snmp_timeout)");
}

/* file: cdef.php, action: edit */
$fields_cdef_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("A useful name for this CDEF."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_cdef" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: color.php, action: edit */
$fields_color_edit = array(
	"hex" => array(
		"method" => "textbox",
		"friendly_name" => _("Hex Value"),
		"description" => _("The hex value for this color; valid range: 000000-FFFFFF."),
		"value" => "|arg1:hex|",
		"max_length" => "6",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_color" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: rra_templates.php, action: edit */
$fields_rra_template_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("RRA Template Name"),
		"description" => _("Enter a meaningful name for this Round Robin Archive Template."),
		"value" => "|arg1:name|",
		"max_length" => "100"
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => _("Description"),
		"description" => _("Detailed information relative to this RRA Template."),
		"value" => "|arg1:description|",
		"max_length" => "255"
		),
	"polling_frequency" => array(
		"method" => "drop_array",
		"friendly_name" => _("Polling Frequency"),
		"description" => _("How often you want the device to be polled."),
		"default" => 300,
		"value" => "|arg1:polling_frequency|",
		"array" => $rra_polling_frequency
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_rra_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:rra_template_id|"
		),
	"save_component_rra_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: edit */
$fields_data_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("Enter a meaningful name for this script."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"type_id" => array(
		"method" => "drop_array",
		"friendly_name" => _("Input Type"),
		"description" => _("Choose what type of script this is."),
		"value" => "|arg1:type_id|",
		"array" => $script_types,
		),
	"input_string" => array(
		"method" => "textbox",
		"friendly_name" => _("Input String"),
		"description" => _("The data that is sent to the script, which includes the complete path to the script and input fields in &lt;&gt; brackets."),
		"value" => "|arg1:input_string|",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_data_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: field_edit (dropdown) */
$fields_data_input_field_edit_input = array(
	"field_input_type" => array(
		"friendly_name" => _("Input Field Type"),
		"redirect_url" => "",
		"form_index" => "",
		"method" => "drop_array_js",
		"array" => $data_input_field_inputs,
		"default" => SCRIPT_FIELD_INPUT_CUSTOM,
		"value" => "|arg1:field_input_type|",
		"description" => _("Select where this data input field will obtain its value.")
		)
	);

$fields_data_input_field_edit_input_custom = array(
	"hdr_custom" => array(
		"friendly_name" => _("Custom Value"),
		"method" => "spacer"
		),
	"field_input_value" => array(
		"method" => "textbox",
		"friendly_name" => _("Default Value"),
		"description" => _("Enter a default value for this input field."),
		"value" => "|arg1:field_input_value|",
		"max_length" => "100",
		)
	);

$fields_data_input_field_edit_input_device = array(
	"hdr_custom" => array(
		"friendly_name" => _("Device Field Value"),
		"method" => "spacer"
		),
	"field_input_value" => array(
		"friendly_name" => _("Device Field"),
		"redirect_url" => "",
		"form_index" => "",
		"method" => "drop_array_js",
		"array" => array(
			"hostname" => "Hostname",
			"description" => _("Description")
			),
		"default" => "hostname",
		//"value" => "|arg1:field_input_value|",
		"description" => _("Select the device field that will be used to populate this input field.")
		)
	);

/* file: data_input.php, action: field_edit */
$fields_data_input_field_edit = array(
	"data_name" => array(
		"method" => "textbox",
		"friendly_name" => _("Field [|arg2:|]"),
		"description" => _("Enter a name for this |arg2:| field."),
		"value" => "|arg1:data_name|",
		"max_length" => "50",
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Friendly Name"),
		"description" => _("Enter a meaningful name for this data input field."),
		"value" => "|arg1:name|",
		"max_length" => "200",
		),
	"update_rrd" => array(
		"method" => "checkbox",
		"friendly_name" => _("Update RRD File"),
		"description" => _("Whether data from this output field is to be entered into the RRD file. Fields values that are not entered into the RRD can still be used as variables on graphs."),
		"value" => "|arg1:update_rrd|",
		"default" => "1",
		"form_id" => "|arg1:id|"
		),
	"regexp_match" => array(
		"method" => "textbox",
		"friendly_name" => _("Regular Expression Match"),
		"description" => _("If you want to require a certain regular expression to be matched againt input data, enter it here (ereg format)."),
		"value" => "|arg1:regexp_match|",
		"max_length" => "200"
		),
	"allow_empty" => array(
		"method" => "checkbox",
		"friendly_name" => _("Allow Empty Input"),
		"description" => _("Check here if you want to allow empty input in this field."),
		"value" => "|arg1:allow_empty|",
		"default" => "0",
		"form_id" => "|arg1:id|"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	//"input_output" => array(
	//	"method" => "hidden",
	//	"value" => "|arg2:|"
	//	),
	//"sequence" => array(
	//	"method" => "hidden_zero",
	//	"value" => "|arg1:sequence|"
	//	),
	//"data_input_id" => array(
	//	"method" => "hidden_zero",
	//	"value" => "|arg1:id|"
	//	),
	"save_component_field" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: grprint_presets.php, action: edit */
$fields_grprint_presets_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("Enter a name for this GPRINT preset, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50",
		),
	"gprint_text" => array(
		"method" => "textbox",
		"friendly_name" => _("GPRINT Text"),
		"description" => _("Enter the custom GPRINT string here."),
		"value" => "|arg1:gprint_text|",
		"max_length" => "50",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_gprint_presets" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: devices.php, action: edit */
$fields_host_edit = array(
	"spacer0" => array(
		"method" => "spacer",
		"friendly_name" => _("General Options")
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => _("Description"),
		"description" => _("Give this host a meaningful description."),
		"value" => "|arg1:description|",
		"max_length" => "250"
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => _("Hostname"),
		"description" => _("Fill in the fully qualified hostname for this device."),
		"value" => "|arg1:hostname|",
		"max_length" => "250"
		),
	"host_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => _("Device Template"),
		"description" => _("Choose what type of host, host template this is. The host template will govern what kinds of data should be gathered from this type of host."),
		"value" => "|arg1:host_template_id|",
		"none_value" => "None",
		"sql" => "select id,name from host_template order by name"
		),
	"poller_id" => array(
		"method" => "drop_sql",
		"friendly_name" => _("Default Poller"),
		"description" => _("Choose the default poller to handle this hosts request."),
		"value" => "|arg1:poller_id|",
		"sql" => "select id,name from poller"
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => _("Disable Host"),
		"description" => _("Check this box to disable all checks for this host."),
		"value" => "|arg1:disabled|",
		"default" => "",
		"form_id" => false
		),
	"spacer1" => array(
		"method" => "spacer",
		"friendly_name" => _("Availability Detection")
		),
	"availability_method" => array(
		"method" => "drop_array",
		"friendly_name" => _("Availability Method"),
		"description" => _("Choose the availability method to use for this host."),
		"value" => "|arg1:availability_method|",
		"default" => AVAIL_SNMP,
		"array" => $availability_options
		),
	"ping_method" => array(
		"friendly_name" => _("Ping Type"),
		"description" => _("The type of ping packet to sent.  NOTE: ICMP requires that the Cacti Service ID have root privilages in Unix."),
		"value" => "|arg1:ping_method|",
		"method" => "drop_array",
		"default" => PING_UDP,
		"array" => $ping_methods
		),
	"spacer15" => array(
		"method" => "spacer",
		"friendly_name" => _("SNMP Generic Options")
		),
	"snmp_port" => array(
		"method" => "textbox",
		"friendly_name" => _("SNMP Port"),
		"description" => _("Enter the UDP port number to use for SNMP (default is 161)."),
		"value" => "|arg1:snmp_port|",
		"max_length" => "5",
		"default" => read_config_option("snmp_port"),
		"size" => "15"
		),
	"snmp_timeout" => array(
		"method" => "textbox",
		"friendly_name" => _("SNMP Timeout"),
		"description" => _("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support)."),
		"value" => "|arg1:snmp_timeout|",
		"max_length" => "8",
		"default" => read_config_option("snmp_timeout"),
		"size" => "15"
		),
	"snmp_version" => array(
		"method" => "drop_array",
		"friendly_name" => _("SNMP Version"),
		"description" => _("Choose the SNMP version for this host."),
		"value" => "|arg1:snmp_version|",
		"default" => read_config_option("snmp_ver"),
		"array" => $snmp_versions
		),
	"spacer2" => array(
		"method" => "spacer",
		"friendly_name" => _("SNMP v1/v2c Options")
		),
	"snmp_community" => array(
		"method" => "textbox",
		"friendly_name" => _("SNMP Community"),
		"description" => _("Fill in the SNMP read community for this device."),
		"value" => "|arg1:snmp_community|",
		"form_id" => "|arg1:id|",
		"default" => read_config_option("snmp_community"),
		"max_length" => "100"
		),
	"spacer3" => array(
		"method" => "spacer",
		"friendly_name" => _("SNMP v3 Options")
		),
	"snmpv3_auth_username" => array(
		"method" => "textbox",
		"friendly_name" => _("Username"),
		"description" => _("The default SNMP v3 username."),
		"value" => "|arg1:snmpv3_auth_username|",
		"default" => read_config_option("snmpv3_auth_username"),
		"max_length" => "100"
		),
	"snmpv3_auth_password" => array(
		"method" => "textbox_password",
		"friendly_name" => _("Password"),
		"description" => _("The default SNMP v3 password."),
		"value" => "|arg1:snmpv3_auth_password|",
		"default" => read_config_option("snmpv3_auth_password"),
		"max_length" => "100"
		),
	"snmpv3_auth_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => _("Authentication Protocol"),
		"description" => _("Select the default SNMP v3 authentication protocol to use."),
		"value" => "|arg1:snmpv3_auth_protocol|",
		"default" => read_config_option("snmpv3_auth_protocol"),
		"array" => $snmpv3_auth_protocol
		),
	"snmpv3_priv_passphrase" => array(
		"method" => "textbox",
		"friendly_name" => _("Privacy Passphrase"),
		"description" => _("The default SNMP v3 privacy passphrase."),
		"value" => "|arg1:snmpv3_priv_passphrase|",
		"default" => read_config_option("snmpv3_priv_passphrase"),
		"max_length" => "100"
		),
	"snmpv3_priv_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => _("Privacy Protocol"),
		"description" => _("Select the default SNMP v3 privacy protocol to use."),
		"value" => "|arg1:snmpv3_priv_protocol|",
		"default" => read_config_option("snmpv3_priv_protocol"),
		"array" => $snmpv3_priv_protocol
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_host_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:host_template_id|"
		),
	"save_component_host" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: device_templates.php, action: edit */
$fields_host_template_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("A useful name for this host template."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: rra.php, action: edit */
$fields_rra_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("How data is to be entered in RRA's."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"consolidation_function_id" => array(
		"method" => "drop_multi",
		"friendly_name" => _("Consolidation Functions"),
		"description" => _("How data is to be entered in RRA's."),
		"array" => $consolidation_functions,
		"sql" => "select consolidation_function_id as id,rra_id from rra_cf where rra_id=|arg1:id|",
		),
	"x_files_factor" => array(
		"method" => "textbox",
		"friendly_name" => _("X-Files Factor"),
		"description" => _("The amount of unknown data that can still be regarded as known."),
		"value" => "|arg1:x_files_factor|",
		"max_length" => "10",
		),
	"steps" => array(
		"method" => "textbox",
		"friendly_name" => _("Steps"),
		"description" => _("How many data points are needed to put data into the RRA."),
		"value" => "|arg1:steps|",
		"max_length" => "8",
		),
	"rows" => array(
		"method" => "textbox",
		"friendly_name" => _("Rows"),
		"description" => _("How many generations data is kept in the RRA."),
		"value" => "|arg1:rows|",
		"max_length" => "8",
		),
	"timespan" => array(
		"method" => "textbox",
		"friendly_name" => _("Timespan"),
		"description" => _("How many seconds to display in graph for this RRA."),
		"value" => "|arg1:timespan|",
		"max_length" => "8",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_rra" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: tree.php, action: edit */
$fields_tree_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("A useful name for this graph tree."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"sort_type" => array(
		"method" => "drop_array",
		"friendly_name" => _("Sorting Type"),
		"description" => _("Choose how items in this tree will be sorted."),
		"value" => "|arg1:sort_type|",
		"array" => $tree_sort_types,
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_tree" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: user_admin.php, action: user_edit (host) */

$user_themes = $themes;
$user_themes["default"] = _("System Default (Global Setting)");

$fields_user_user_edit_host = array(
	"username" => array(
		"method" => "textbox",
		"friendly_name" => _("User Name"),
		"description" => _("The login name for this user."),
		"value" => "|arg1:username|",
		"max_length" => "255"
		),
	"full_name" => array(
		"method" => "textbox",
		"friendly_name" => _("Full Name"),
		"description" => _("A more descriptive name for this user, that can include spaces or special characters."),
		"value" => "|arg1:full_name|",
		"max_length" => "255"
		),
	"password" => array(
		"method" => "textbox_password",
		"friendly_name" => _("Password"),
		"description" => _("Enter the password for this user twice. Remember that passwords are case sensitive!"),
		"value" => "",
		"max_length" => "255"
		),
	"email_address_primary" => array(
		"method" => "textbox",
		"friendly_name" => _("Primary Email Address"),
		"description" => _("The primary e-mail address for this user."),
		"value" => "|arg1:email_address_primary|",
		"max_length" => "255"
		),
	"email_address_secondary" => array(
		"method" => "textbox",
		"friendly_name" => _("Secondary Email Address"),
		"description" => _("The secondary e-mail address for this user. This would typically be an e-mail address to a handheld or portable device."),
		"value" => "|arg1:email_address_secondary|",
		"max_length" => "255"
		),
	"grp1" => array(
		"friendly_name" => _("Account Options"),
		"method" => "checkbox_group",
		"description" => _("Set any user account-specific options here."),
		"items" => array(
			"must_change_password" => array(
				"value" => "|arg1:must_change_password|",
				"friendly_name" => _("User Must Change Password at Next Login"),
				"form_id" => "|arg1:id|",
				"default" => ""
				),
			"graph_settings" => array(
				"value" => "|arg1:graph_settings|",
				"friendly_name" => _("Allow this User to Keep Custom Graph Settings"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"grp2" => array(
		"friendly_name" => _("Graph Options"),
		"method" => "checkbox_group",
		"description" => _("Set any graph-specific options here."),
		"items" => array(
			"show_tree" => array(
				"value" => "|arg1:show_tree|",
				"friendly_name" => _("User Has Rights to Tree View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_list" => array(
				"value" => "|arg1:show_list|",
				"friendly_name" => _("User Has Rights to List View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_preview" => array(
				"value" => "|arg1:show_preview|",
				"friendly_name" => _("User Has Rights to Preview View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"login_opts" => array(
		"friendly_name" => _("Login Options"),
		"method" => "radio",
		"default" => "1",
		"description" => _("What to do when this user logs in."),
		"value" => "|arg1:login_opts|",
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => _("Show the page that user pointed their browser to.")
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => _("Show the default console screen.")
				),
			2 => array(
				"radio_value" => "3",
				"radio_caption" => _("Show the default graph screen.")
				)
			)
		),
	"enabled" => array(
		"method" => "drop_array",
		"friendly_name" => _("Status"),
		"description" => _("User status, enabled or disabled. Only enforced on login."),
		"value" => "|arg1:enabled|",
		"array" => array(1 => "Enabled", 0 => "Disabled"),
		"default" => "1"
		),
	"password_expire_length" => array(
		"method" => "drop_array",
		"friendly_name" => _("Password Expiration Interval"),
		"description" => _("How often the users password will expire and a password change will be forced."),
		"value" => "|arg1:password_expire_length|",
		"array" => $user_password_expire_intervals,
		"default" => read_config_option("password_expire_length")
		),
	"current_theme" => array(
		"method" => "drop_array",
		"friendly_name" => _("Visual Theme"),
		"description" => _("The Cacti theme to use. Changes the look of Cacti."),
		"value" => "|arg1:current_theme|",
		"array" => $user_themes,
		"default" => "default"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_policy_graphs" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graphs|"
		),
	"_policy_trees" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_trees|"
		),
	"_policy_hosts" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_hosts|"
		),
	"_policy_graph_templates" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graph_templates|"
		),
	"save_component_user" => array(
		"method" => "hidden",
		"value" => "1"
		),
	"realm" => array(
		"method" => "hidden",
		"default" => "0",
		"value" => "|arg1:realm|"
		)
	);

?>