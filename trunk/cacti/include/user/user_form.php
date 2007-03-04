<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

/* User and Auth form variables include */

/* file: user_admin.php, action: user_edit (host) */

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
		"array" => array(1 => _("Enabled"), 0 => _("Disabled")),
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
		"array" => $themes,
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
