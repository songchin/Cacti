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

require_once(CACTI_BASE_PATH . "/include/auth/auth_constants.php");

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
	"policy_devices" => AUTH_CONTROL_DATA_POLICY_ALLOW,
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
	"policy_devices" => AUTH_CONTROL_DATA_POLICY_ALLOW,
	"policy_graph_templates" => AUTH_CONTROL_DATA_POLICY_ALLOW
);


$graph_policy_array = array(
	AUTH_CONTROL_DATA_POLICY_ALLOW 	=> __("Allow"),
	AUTH_CONTROL_DATA_POLICY_DENY 	=> __("Deny"),
	);

$perm_item_types = array(
	PERM_GRAPHS 			=> __('graph'),
	PERM_TREES 				=> __('tree'),
	PERM_DEVICES			=> __('device'),
	PERM_GRAPH_TEMPLATES 	=> __('graph_template'),
	);


$auth_methods = array(
	AUTH_METHOD_NONE 		=> __("None"),
	AUTH_METHOD_BUILTIN 	=> __("Builtin Authentication"),
	AUTH_METHOD_WEB 		=> __("Web Basic Authentication"),
	);
if (function_exists("ldap_connect")) {
	$auth_methods[AUTH_METHOD_LDAP] = __("LDAP Authentication");
}

$auth_realms = array(
	AUTH_REALM_BUILTIN		=> __("Local"),
	AUTH_REALM_WEB			=> __("Web Basic"),
	);
if (function_exists("ldap_connect")) {
	$auth_realms[AUTH_REALM_LDAP] = __("LDAP");
}

$ldap_versions = array(
	2 => __("Version 2"),
	3 => __("Version 3"),
	);

$ldap_encryption = array(
	LDAP_ENCRYPT_NONE 		=> __("None"),
	LDAP_ENCRYPT_SSL 		=> __("SSL"),
	LDAP_ENCRYPT_TLS 		=> __("TLS"),
	);

$ldap_modes = array(
	LDAP_SEARCHMODE_NONE		=> __("No Searching"),
	LDAP_SEARCHMODE_ANON		=> __("Anonymous Searching"),
	LDAP_SEARCHMODE_SPECIFIC	=> __("Specific Searching"),
	);


?>
