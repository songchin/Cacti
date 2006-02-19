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

/* read_default_graph_config_option - finds the default value of a graph configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns - the default value of the configuration option */
function read_default_graph_config_option($config_name) {
	require(CACTI_BASE_PATH . "/include/global_settings.php");

	while (list($tab_name, $tab_array) = each($settings_graphs)) {
		if ((isset($tab_array[$config_name])) && (isset($tab_array[$config_name]["default"]))) {
			return $tab_array[$config_name]["default"];
		}else{
			while (list($field_name, $field_array) = each($tab_array)) {
				if ((isset($field_array["items"])) && (isset($field_array["items"][$config_name])) && (isset($field_array["items"][$config_name]["default"]))) {
					return $field_array["items"][$config_name]["default"];
				}
			}
		}
	}
}

/* read_graph_config_option - finds the current value of a graph configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings_graphs array
     in 'include/global_settings.php'
   @returns - the current value of the graph configuration option */
function read_graph_config_option($config_name) {
	/* users must have cacti user auth turned on to use this */
	if ((read_config_option("auth_method") == "0") || (!isset($_SESSION["sess_user_id"]))) {
		return read_default_graph_config_option($config_name);
	}

	if (!isset($_SESSION["sess_graph_config_array"][$config_name])) {
		$db_setting = db_fetch_row("select value from settings_graphs where name='$config_name' and user_id=" . $_SESSION["sess_user_id"]);

		if (isset($db_setting["value"])) {
			$_SESSION["sess_graph_config_array"][$config_name] = $db_setting["value"];
		}else{
			$_SESSION["sess_graph_config_array"][$config_name] = read_default_graph_config_option($config_name);
		}
	}

	return $_SESSION["sess_graph_config_array"][$config_name];
}

/* config_value_exists - determines if a value exists for the current user/setting specified
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns (bool) - true if a value exists, false if a value does not exist */
function config_value_exists($config_name) {
	return sizeof(db_fetch_assoc("select value from settings where name='$config_name'"));
}

/* graph_config_value_exists - determines if a value exists for the current user/setting specified
   @arg $config_name - the name of the configuration setting as specified $settings_graphs array
     in 'include/global_settings.php'
   @arg $user_id - the id of the user to check the configuration value for
   @returns (bool) - true if a value exists, false if a value does not exist */
function graph_config_value_exists($config_name, $user_id) {
	return sizeof(db_fetch_assoc("select value from settings_graphs where name='$config_name' and user_id='$user_id'"));
}

/* read_default_config_option - finds the default value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns - the default value of the configuration option */
function read_default_config_option($config_name) {
	require(CACTI_BASE_PATH . "/include/global_settings.php");

	while (list($tab_name, $tab_array) = each($settings)) {
		if ((isset($tab_array[$config_name])) && (isset($tab_array[$config_name]["default"]))) {
			return $tab_array[$config_name]["default"];
		}else{
			while (list($field_name, $field_array) = each($tab_array)) {
				if ((isset($field_array["items"])) && (isset($field_array["items"][$config_name])) && (isset($field_array["items"][$config_name]["default"]))) {
					return $field_array["items"][$config_name]["default"];
				}
			}
		}
	}
}

/* read_config_option - finds the current value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/global_settings.php'
   @returns - the current value of the configuration option */
function read_config_option($config_name) {
	if (!isset($_SESSION["sess_config_array"][$config_name])) {
		$db_setting = db_fetch_row("select value from settings where name='$config_name'");

		if (isset($db_setting["value"])) {
			$_SESSION["sess_config_array"][$config_name] = $db_setting["value"];
		}else{
			$_SESSION["sess_config_array"][$config_name] = read_default_config_option($config_name);
		}
	}

	return $_SESSION["sess_config_array"][$config_name];
}

?>
