
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

/* auth data required fields for user */
$auth_control_data_user_fields = array(
	"password" => "",
	"last_login_ip" => "",
	"last_login" => "",
	"user_type" => "0",
	"must_change_password" => "0",
	"show_tree" => "1",
	"show_list" => "1",
	"show_preview" => "1",
	"graph_settings" => "1",
	"login_opts" => "0",
	"policy_graphs" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_trees" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_hosts" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_graph_templates" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"password_expire_length" => read_config_option("password_expire_length"),
	"password_change_last" => "",
	"theme" => "classic"
);

$auth_control_data_group_fields = array(
	"group_type" => "0",
	"show_tree" => "1",
	"show_list" => "1",
	"show_preview" => "1",
	"graph_settings" => "1",
	"login_opts" => "0",
	"policy_graphs" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_trees" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_hosts" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_graph_templates" => AUTH_CONTROL_DATA_POLICY_ALLOW
);




?>
