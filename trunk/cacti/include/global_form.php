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

require(CACTI_BASE_PATH . "/include/global_arrays.php");
require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

if (!defined("VALID_HOST_FIELDS")) {
	define("VALID_HOST_FIELDS", "(hostname|snmp_community|snmpv3_auth_username|snmpv3_auth_password|snmpv3_auth_protocol|snmpv3_priv_passphrase|snmpv3_priv_protocol|snmp_version|snmp_port|snmp_timeout)");
}

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