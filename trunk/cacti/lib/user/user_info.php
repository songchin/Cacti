<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

/*
########################################
# user functions
########################################
*/

/* api_user_list - returns an array of users on the system
   @arg $array  - Array of columns to sort by for example:
		array( "1" => "username", "2" => "full_name")
   @return - list of users id, username only */
function api_user_list($array) {

	/* build SQL query */
	$sql_query = "SELECT id,username FROM user_auth ";
	$sql_sort = "ORDER BY ";
	if (($array) && (is_array($array))) {
		foreach ($array as $field => $value) {
			$sql_sort .= sql_sanitize($value) . ", ";
		}
		/* remove trailing comma */
		$sql_sort = preg_replace("/\,\ $/", "", $sql_sort);
		$sql_query = $sql_query . $sql_sort;
	}else{
		/* error no array */
		return NULL;
	}

	/* get the user list */
	$user_list = db_fetch_assoc($sql_query);
	if ((count($user_list) > 0) && (is_array($user_list))) {
		return $user_list;
	}else{
		return NULL;
	}

}

/* api_user_info - returns an array of value based on request array
   @arg $array  - Array of values to query for example:
		array( "username" => "admin")
   @return - single users array of info, NULL if no user found */
function api_user_info($array) {

	/* build SQL query */
	$sql_query = "SELECT *, DATE_FORMAT(password_change_last,'%M %e %Y %H:%i:%s') as password_change_last_formatted, DATE_FORMAT(last_login,'%M %e %Y %H:%i:%s') as last_login_formatted FROM user_auth WHERE ";
	$sql_where = "";
	if (($array) && (is_array($array))) {
		foreach ($array as $field => $value) {
			$sql_where .= $field . " = '" . sql_sanitize($value) . "' AND ";
		}
		/* remove trailing AND */
		$sql_where = preg_replace("/ AND\ $/", "", $sql_where);
		$sql_query = $sql_query . $sql_where;
	}else{
		/* error no array */
		return "";
	}

	/* get the user info */
	$user = db_fetch_row($sql_query);
	if ((is_array($user)) && (sizeof($user) > 0)) {
		return $user;
	}else{
		return NULL;
	}

}

/* api_user_expire_info
  @arg $user_id - user id
  @return - Days till expire, "-1" for no expire. */
function api_user_expire_info($user_id) {

	if (empty($user_id)) {
		return -1;
	}

	$user = api_user_info( array( "id" => $user_id) );

	if ($user) {
		/* check that user has expire length */
		if ($user["password_expire_length"] == "0") {
			return -1;
		}

		/* get last time the password was changed */
		if ($user["password_change_last"] == "0000-00-00 00:00:00") {
			$change_last = strtotime($user["created"]);
		}else{
			$change_last = strtotime($user["password_change_last"]);
		}
		$expire_time = $user["password_expire_length"] * 86400;

		$now = strtotime("now");

		$days = ( $change_last + $expire_time - $now ) / 86400;

		if ($days <= 0) {
			$days = 0;
		}
		$days = floor($days);

		return $days;


	}else{
		return -1;
	}

	return -1;

}

/* api_user_theme - returns the users current theme, stores in session variable so the database is hit only once
   @arg $user_id = user id
   @returns - returns the users current theme */
function api_user_theme($user_id) {
	/* users must have cacti user auth turned on to use this */
	if ((read_config_option("auth_method") == "0") || (!isset($user_id))) {
		return read_config_option("default_theme");
	}

	if (isset($_SESSION["sess_current_theme"])) {
		return $_SESSION["sess_current_theme"];
	}else{
		$user = api_user_info( array( "id" => $user_id ) );
		if ((empty($user["current_theme"])) || ($user["current_theme"] == "default")) {
			$user_theme = read_config_option("default_theme");
		}else{
			$user_theme = $user["current_theme"];
		}

		$_SESSION["sess_current_theme"] = $user_theme;
	}

	return $user_theme;
}



/*
########################################
# user realm (permissions) functions
########################################
*/

/* api_user_realms_list
  @arg $user_id - user id
  @return - Array of indexed by realm_id with a sub array of realm_name and value */
function api_user_realms_list($user_id) {
	global $user_auth_realms;

	$realm_list = array();

	if (empty($user_id)) {
		$user_id = "0";
	}

	if (!is_numeric($user_id)) {
		$user_id = "0";
	}
	/* prevent array sqaushing */
	$user_auth_realms_local = $user_auth_realms;

	/* process realms */
	$user_realms = db_fetch_assoc("select realm_id from user_auth_realm where user_id = " . $user_id);
	while (list($realm_id, $realm_name) = each($user_auth_realms_local)) {
		$value = 0;
		while (list($record,$user_realm) = each($user_realms)) {
			if ($user_realm["realm_id"] == $realm_id) {
				$value = "1";
			}
		}
		/* return to the start of the recordset */
		reset($user_realms);
		$realm_list[$realm_id] = array(
			"realm_name" => $realm_name,
			"value" => $value
		);

	}

	return $realm_list;
}

/*
########################################
# graph settings functions
########################################
*/

/* api_user_graph_setting_list
  @arg $user_id - user id
  @return - array of field => value
*/
function api_user_graph_setting_list($user_id) {
	global $settings_graphs;

	/* prevent array squashing */
	$settings_graphs_local = $settings_graphs;

	/* Get settings from database */
	$user_settings = array();
	if (!empty($user_id)) {
		if (is_numeric($user_id)) {
			$setting = db_fetch_assoc("select name,value from settings_graphs where user_id = " . $user_id);
			while (list($record, $fields) = each($setting)) {
				$user_settings[$fields["name"]] = $fields["value"];
			}
		}
	}

	/* build array of values */
	$return_array = array();

	/* go through form sections */
	while (list($tab_short_name, $tab_fields) = each($settings_graphs_local)) {
		/* process fields */
		while (list($field_name, $field_array) = each($tab_fields)) {
			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				/* sub fields detected */
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					if (isset($user_settings[$sub_field_name])) {
						$return_array[$sub_field_name] = $user_settings[$sub_field_name];
					}else{
						$return_array[$sub_field_name] = $sub_field_array["default"];
					}
				}
			}else{
				/* regular field */
				if (isset($user_settings[$field_name])) {
					$return_array[$field_name] = $user_settings[$field_name];
				}else{
					$return_array[$field_name] = $field_array["default"];
				}
			}
		}
	}

	return $return_array;

}

?>
