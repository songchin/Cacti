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

/*
To use this api file you must include

include/config.php

*/

$graph_perms_type_array = array(
	"graph" => "1",
	"tree" => "2",
	"host" => "3",
	"graph_template" => "4"
	);

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
	if ((sizeof($array) > 0) && (is_array($array))) {
		foreach ($array as $field => $value) {
			$sql_sort .= $value . ", ";
		}
		/* remove trailing comma */
		$sql_sort = preg_replace("/\,\ $/", "", $sql_sort);
		$sql_query = $sql_query . $sql_sort;
	}else{
		/* error no array */
		return "";
	}

	/* get the user list */
	$user_list = db_fetch_assoc($sql_query);
	return $user_list;

}

/* api_user_info - returns an array of value based on request array
   @arg $array  - Array of values to query for example:
		array( "username" => "admin")
   @return - single users array of info */
function api_user_info($array) {

	/* build SQL query */
	$sql_query = "SELECT *, DATE_FORMAT(password_change_last,'%M %e %Y %H:%i:%s') as password_change_last_formatted FROM user_auth WHERE ";
	$sql_where = "";
	if ((sizeof($array) > 0) && (is_array($array))) {
		foreach ($array as $field => $value) {
			$sql_where .= $field . " = '" . $value . "' AND ";
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
	
	/* get last login and append */
	if (sizeof($user)) {
		$last_login = db_fetch_row("select username,time as lastlogin,DATE_FORMAT(time,'%M %e %Y %H:%i:%s') as lastlogin_formatted, ip from user_log where user_id = '" . $user["id"] . "' and result = 1 order by time desc limit 1");
		$user["lastlogin"] = $last_login["lastlogin"];
		$user["lastlogin_formatted"] = $last_login["lastlogin_formatted"];
		$user["ip"] = $last_login["ip"];
	}

	return $user;

}

/* api_user_expire_info
  @arg $user_id - user id
  @return - Days till expire, "-1" for no expire. */
function api_user_expire_info($user_id) {

	if (empty($user_id)) {
		return -1;
	}

	$user = api_user_info( array( "id" => $user_id) );
	
	if (sizeof($user)) {
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

/* api_user_save
   @arg $array - an array containing each column -> value mapping in the user_auth table, always remember the id field
   @return - id of user saved, new or existing 
   reference user_admin.php for examples */
function api_user_save($array) {

	$user_id = sql_save($array, "user_auth");

	return $user_id;

}

/* api_user_changepassword - changes users password, old password is optional
   @arg $user_id - User id to change password for
   @arg $password_new - New password to set for user 
   @arg $password_old - Old password, optional, if passed will validate 
   @returns - '0' is success, '1' is general error, '2' fail password authentication,
              '3' user not found  */
function api_user_changepassword($user_id, $password_new, $password_old="") {

	/* validate we can change the password */
	if (read_config_option("auth_method") != "1") {
		return 1;
	}

	/* validate old password */
	if (!empty($password_old)) {
		if (!sizeof(db_fetch_row("select id from user_auth where id ='" . $user_id . "' and password = '" . md5($password_old) . "' and realm = 0"))) {
			/* Password validation error */
			return 2;	
		}
	}

	/* validate user exists */
	if (sizeof(db_fetch_row("select id from user_auth where id ='" . $user_id . "' and realm = 0"))) {
		if (db_execute("update user_auth set password = '" . md5($password_new) . "',must_change_password = '', password_change_last = NOW() where id = '" . $user_id . "'") == 1) {
			/* password changed */
			return 0;
		}else{
			/* error */
			return 1;
		}
	}else{
		/* user not found */
		return 3;
	}

	/* all else fails return error */
	return 1;

}

/* api_user_remove - removes a user account
   @arg $user_id - user id */ 
function api_user_remove($user_id) {

	if (!empty($user_id)) {
		if ($user_id != 1) {
			db_execute("delete from user_auth where id = '" . $user_id . "'");
			db_execute("delete from user_auth_realm where user_id = '" . $user_id . "'");
			db_execute("delete from user_auth_perms where user_id = '" . $user_id . "'");
			db_execute("delete from settings_graphs where user_id = '" . $user_id . "'");
		}
	}
}

/* api_user_enable - enables  a user account
   @arg $user_id - user id */
function api_user_enable($user_id) {
	if (!empty($user_id)) {
		if ($user_id != 1) {
			db_execute("update user_auth set enabled = 1 where id=" . $user_id);
		}
	}
}

/* api_user_disable - disables a user account
   @arg $user_id - user id */
function api_user_disable($user_id) {
	if (!empty($user_id)) {
		if ($user_id != 1) {
			db_execute("update user_auth set enabled = 0 where id=" . $user_id);
		}
	}
}

/* api_user_expire_length_set - sets a users expire interval
   @arg $user_id - user id 
   @arg $interval - integer, the number of days */
function api_user_expire_length_set($user_id, $interval) {
	if (!empty($user_id)) {
		$user = array();
		$user["id"] = $user_id;
		$user["password_expire_length"] = $interval;
		api_user_save($user);	
	}
}

/* api_user_copy - copies a user account
   @arg $template_user - username of account that should be used as the template
   @arg $new_user - username of the account to be created
   @arg $new_realm - the realm the new account should be a member of 
   @return - '0' success, '1' error */
function api_user_copy($template_user, $new_user, $new_realm=-1) {

	$user_auth = db_fetch_row("select * from user_auth where username = '$template_user'");
        $user_auth['username'] = $new_user;
	if ($new_realm != -1) {
		$user_auth['realm'] = $new_realm;
        }
	$old_id = $user_auth['id'];
        $user_auth['id'] = 0;
	$user_auth["created"] = "now()";
	$user_auth["password_change_last"] = "";

	/* check that destination user doesn't already exist */
	$user = api_user_info( array( "username" => $new_user, "realm" => $user_auth['realm'] ) );
	if (!empty($user["id"])) {
		return 1;
	}

        $new_id = sql_save($user_auth, 'user_auth');

        $user_auth_perms = db_fetch_assoc("select * from user_auth_perms where user_id = '$old_id'");
        foreach ($user_auth_perms as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'user_auth_perms', array('user_id', 'item_id', 'type'));
        }

        $user_auth_realm = db_fetch_assoc("select * from user_auth_realm where user_id = '$old_id'");
        foreach ($user_auth_realm as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'user_auth_realm', array('realm_id', 'user_id'));
        }

        $settings_graphs = db_fetch_assoc("select * from settings_graphs where user_id = '$old_id'");
        foreach ($settings_graphs as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'settings_graphs', array('user_id', 'name'));
        }

        $settings_tree = db_fetch_assoc("select * from settings_tree where user_id = '$old_id'");
        foreach ($settings_tree as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'settings_tree', array('user_id', 'graph_tree_item_id'));
        }

	return 0;
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
	/* prevent array sqaushing */	
	$user_auth_realms_local = $user_auth_realms;

	/* process realms */
	while (list($realm_id, $realm_name) = each($user_auth_realms_local)) {
			
		if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where user_id = '" . $user_id . "' and realm_id = '" . $realm_id . "'")) > 0) {
			$value = "1";
		}else{
			$value = "0";
		}
		$realm_list[$realm_id] = array(
			"realm_name" => $realm_name,
			"value" => $value
		);

	}

	return $realm_list;
}

/* api_user_realms_save
  @arg $user_id - user id
  @arg $array - single dimension array of realm_id that are granted, empty array will clear all realms */
function api_user_realms_save($user_id,$array) {

	/* validate */
	if (!empty($user_id)) {

		/* remove any existing permissions */
		db_execute("delete from user_auth_realm where user_id = '" . $user_id . "'");
		
		/* insert the new permission */
		foreach($array as $realm_id) {
			db_execute("replace into user_auth_realm (user_id,realm_id) values ('" . $user_id . "','" . $realm_id . "')");
		}
	}

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
		$setting = db_fetch_assoc("select name,value from settings_graphs where user_id = '" . $user_id . "'");
		while (list($record, $fields) = each($setting)) {
			$user_settings[$fields["name"]] = $fields["value"];	
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

/* api_user_graph_settings_save
  @arg $user_id - user id
  @arg $array - Array containing values to save to the database. fieldname => value
  @return - '0' success, '1' error */
function api_user_graph_setting_save($user_id,$array) {
	global $settings_graphs;

	/* prevent array squashing */
	$settings_graphs_local = $settings_graphs;

	/* validation */
	if (empty($user_id)) {
		return 1;
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
					db_execute("replace into settings_graphs (user_id,name,value) values (" . $user_id . ",'" . $sub_field_name . "', '" . (isset($array[$sub_field_name]) ? $array[$sub_field_name] : "") . "')");
				}
			}else{
				/* normal field */
				db_execute("replace into settings_graphs (user_id,name,value) values (" . $user_id . ",'$field_name', '" . (isset($array[$field_name]) ? $array[$field_name] : "") . "')");
			}
		}
	}
	
	return 0;


}

/*
########################################
# graph permission functions
########################################
*/

/* api_user_graph_perms_list 
   @arg $type - type of perms to look at.
   @arg $user_id - User ID to query values for
   @return - Array of values: id, name */
function api_user_graph_perms_list($type,$user_id) {
	global $graph_perms_type_array;

	/* validation */
	if (empty($user_id)) {
		$user_id = 0;
	}

	switch ($graph_perms_type_array[$type]) {

	case "1":

		$return_array = db_fetch_assoc("select 
			graph_templates_graph.local_graph_id,
			graph_templates_graph.title_cache
			from graph_templates_graph
			left join user_auth_perms on (graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1)
			where graph_templates_graph.local_graph_id > 0
			and user_auth_perms.user_id = '" . $user_id . "'
			order by graph_templates_graph.title_cache");
		break;

	case "2":

		$return_array = db_fetch_assoc("select
			graph_tree.id,
			graph_tree.name
			from graph_tree
			left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2)
			where user_auth_perms.user_id = '" . $user_id . "'
			order by graph_tree.name");

		break;

	case "3":

		$return_array = db_fetch_assoc("select
			host.id,
			CONCAT_WS('',host.description,' (',host.hostname,')') as name
			from host
			left join user_auth_perms on (host.id=user_auth_perms.item_id and user_auth_perms.type=3)
			where user_auth_perms.user_id = '" . $user_id . "'
			order by host.description,host.hostname");

		break;

	case "4":

		$return_array = db_fetch_assoc("select
			graph_templates.id,
			graph_templates.name
			from graph_templates
			left join user_auth_perms on (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4)
			where user_auth_perms.user_id = '" . $user_id . "'
			order by graph_templates.name");

		break;
	
	default:
		$return_array = array();

	}

	return $return_array;

}

/* api_user_graph_perms_add 
  @arg $type - graph, tree, host, graph_template types
  @arg $user_id - user id
  @arg $item_id = item id of the type of item, example type = graph id is from graph table */
function api_user_graph_perms_add($type,$user_id,$item_id) {
	global $graph_perms_type_array;

	/* validation */
	if ((!empty($graph_perms_type_array[$type])) && (!empty($user_id)) && (!empty($item_id))) {
		db_execute("replace into user_auth_perms (user_id,item_id,type) values ('" . $user_id . "','" . $item_id . "','" . $graph_perms_type_array[$type] . "')");
	}

}

/* api_user_graph_perms_remove 
  @arg $type - graph, tree, host, graph_template types
  @arg $user_id - user id 
  @arg $item_id - item id of type of item, example type = graph id is from graph table */
function api_user_graph_perms_remove($type,$user_id,$item_id) {
	global $graph_perms_type_array;
	
	/* validation */
	if ((!empty($graph_perms_type_array[$type])) && (!empty($user_id)) && (!empty($item_id))) {
		db_execute("delete from user_auth_perms where type = '" . $graph_perms_type_array[$type] . "' and user_id = '" . $user_id . "' and item_id = '" . $item_id . "'");
	}

}

/*
########################################
# LDAP functions
########################################
*/

/* api_user_ldap_auth
  @arg $username - username of the user
  @arg $password - password of the user
  @arg $ldap_dn - LDAP DN for binding
  @arg $ldap_host - Hostname or IP of LDAP server, Default = Configured settings value
  @arg $ldap_port - Port of the LDAP server uses, Default = Configured settings value
  @arg $ldap_port_ssl - Port of the LDAP server uses for SSL, Default = Configured settings value
  @arg $ldap_version - '2' or '3', LDAP protocol version, Default = Configured settings value
  @arg $ldap_encryption - '0' None, '1' SSL, '2' TLS, Default = Configured settings value
  @arg $ldap_referrals - '0' Referrals from server are ignored, '1' Referrals from server are processed, Default = Configured setting value

  @return - array of values 
    "error_num" = error number returned
    "error_text" = error text

Error codes:

#	Text
==============================================================
0	Authentication Success
1	Authentication Failure
2	No username defined
3	Protocol error, unable to set version
4	Unable to set referrals option
5	Protocol error, unable to start TLS communications
6	Unable to create LDAP object
7	Protocol error
8	Insuffient access
9	Unable to connect to server
10	Timeout
11	General bind error


*/
function api_user_ldap_auth($username,$password = "",$ldap_dn = "",$ldap_host = "",$ldap_port = "",$ldap_port_ssl = "",$ldap_version = "",$ldap_encryption = "",$ldap_referrals = "") {

	$output = array();

	/* validation */
	if (empty($username)) {
		$output["error_num"] = "2";
		$output["error_text"] = "No username defined";
		return $output;
	}

	/* get LDAP parameters */
	if (empty($ldap_dn)) {
		$ldap_dn = read_config_option("ldap_dn");
	}
	$ldap_dn = str_replace("<username>",$username,$ldap_dn);
	if (empty($ldap_host)) {
		$ldap_host = read_config_option("ldap_server");
	}
	if (empty($ldap_port)) {
		$ldap_port = read_config_option("ldap_port");
	}
	if (empty($ldap_port_ssl)) {
		$ldap_port_ssl = read_config_option("ldap_port_ssl");
	}
	if (empty($ldap_version)) {
		$ldap_version = read_config_option("ldap_version");
	}
	if (empty($ldap_encryption)) {
		$ldap_encryption = read_config_option("ldap_encryption");
	}
	if (empty($ldap_referrals)) {
		$ldap_referrals = read_config_option("ldap_referrals");
	}
	if ($ldap_encryption == "1") {
		$ldap_host = "ldaps://" . $ldap_host;
		$ldap_port = $ldap_port_ssl;
	}else{
		$ldap_host = "ldap://" . $ldap_host;
	}

	/* Connect to LDAP server */
	$ldap_conn = @ldap_connect($ldap_host,$ldap_port);

	if ($ldap_conn) {
		/* Set protocol version */
		if (!@ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
			$output["error_num"] = "3";
			$output["error_text"] = "Protocol Error, Unable to set version";
			@ldap_close($ldap_conn);
			return $output;
		}
		/* set referrals */
		if ($ldap_referrals == "0") {
			if (!@ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0)) {
				$output["error_num"] = "4";
				$output["error_text"] = "Unable to set referrals option";
				@ldap_close($ldap_conn);
				return $output;
			}
		}
		/* start TLS if requested */
		if ($ldap_encryption == "2") {
			if (!@ldap_start_tls($ldap_conn)) {
				$output["error_num"] = "5";
				$output["error_text"] = "Protocol error, unable to start TLS communications";
				@ldap_close($ldap_conn);
				return $output;
			}
		}
		/* Bind to the LDAP directory */
		$ldap_response = @ldap_bind($ldap_conn,$ldap_dn,$password);
		if ($ldap_response) {
			/* Auth ok */
			$output["error_num"] = "0";
			$output["error_text"] = "Authentication Success";
		}else{
			/* unable to bind */
			$ldap_error = ldap_errno($ldap_conn);
			if ($ldap_error == 0x03) {
				/* protocol error */
				$output["error_num"] = "7";
				$output["error_text"] = "Protocol error";
			}elseif ($ldap_error == 0x31) {
				/* invalid credentials */
				$output["error_num"] = "1";
				$output["error_text"] = "Authenication Failure";
			}elseif ($ldap_error == 0x32) {
				/* insuffient access */
				$output["error_num"] = "8";
				$output["error_text"] = "Insuffient access";
			}elseif ($ldap_error == 0x51) {
				/* unable to connect to server */
				$output["error_num"] = "9";
				$output["error_text"] = "Unable to connect to server";
			}elseif ($ldap_error == 0x55) {
				/* timeout */
				$output["error_num"] = "10";
				$output["error_text"] = "Timeout";
			}else{
				/* general bind error */
				$output["error_num"] = "11";
				$output["error_text"] = "General bind error, LDAP result: " . ldap_error($ldap_conn);
			}
		}
	}else{
		/* Error intializing LDAP */
		$output["error_num"] = "6";
		$output["error_text"] = "Unable to create LDAP object";
	}

	/* Close LDAP connection */
	@ldap_close($ldap_conn);

	return $output;


}

/* api_user_ldap_search_dn
  @arg $username - username to search for in the LDAP directory
  @arg $ldap_dn - configured LDAP DN for binding, "<username>" will be replaced with $username
  @arg $ldap_host - Hostname or IP of LDAP server, Default = Configured settings value
  @arg $ldap_port - Port of the LDAP server uses, Default = Configured settings value
  @arg $ldap_port_ssl - Port of the LDAP server uses for SSL, Default = Configured settings value
  @arg $ldap_version - '2' or '3', LDAP protocol version, Default = Configured settings value
  @arg $ldap_encryption - '0' None, '1' SSL, '2' TLS, Default = Configured settings value
  @arg $ldap_referrals - '0' Referrals from server are ignored, '1' Referrals from server are processed, Default = Configured setting value
  @arg $ldap_mode - '0' No Searching, '1' Anonymous Searching, '2' Specfic Searching, Default = Configured settings value
  @arg $ldap_search_base - Search base DN, Default = Configured settings value
  @arg $ldap_search_filter - Filter to find the user, Default = Configured settings value
  @arg $ldap_specific_dn - DN for binding to perform user search, Default = Configured settings value
  @arg $ldap_specific_password - Password for binding to perform user search, Default - Configured settings value

  @return - array of values 
    "error_num" = error number returned
    "error_text" = error text
    "dn" = found dn of user

Error codes:

#	Text
==============================================================
0	User found
1	No username defined
2	Unable to create LDAP connection object
3	Unable to find users DN
4	Protocol error, unable to set version
5	Protocol error, unable to start TLS communications
6	Protocol error
7	Invalid credential
8	Insuffient access
9	Unable to connect to server
10	Timeout
11	General bind error
12	Unable to set referrals option
13	More than one matching user found

*/
function api_user_ldap_search_dn($username,$ldap_dn = "",$ldap_host = "",$ldap_port = "",$ldap_port_ssl = "",$ldap_version = "",$ldap_encryption = "",$ldap_referrals = "", $ldap_mode = "",$ldap_search_base = "", $ldap_search_filter = "",$ldap_specific_dn = "",$ldap_specific_password = "") {

	$output = array();

	/* validation */
	if (empty($username)) {
		$output["dn"] = "";
		$output["error_num"] = "1";
		$output["error_text"] = "No username defined";
		return $output;
	}

	/* strip bad chars from username - prevent altering filter from username*/	
	$username = str_replace("&", "", $username);	
	$username = str_replace("|", "", $username);	
	$username = str_replace("(", "", $username);	
	$username = str_replace(")", "", $username);	
	$username = str_replace("*", "", $username);	
	$username = str_replace(">", "", $username);	
	$username = str_replace("<", "", $username);	
	$username = str_replace("!", "", $username);	
	$username = str_replace("=", "", $username);	

	/* get LDAP parameters */
	if (empty($ldap_dn)) {
		$ldap_dn = read_config_option("ldap_dn");
	}
	$ldap_dn = str_replace("<username>",$username,$ldap_dn);
	if (empty($ldap_host)) {
		$ldap_host = read_config_option("ldap_server");
	}
	if (empty($ldap_port)) {
		$ldap_port = read_config_option("ldap_port");
	}
	if (empty($ldap_port_ssl)) {
		$ldap_port_ssl = read_config_option("ldap_port_ssl");
	}
	if (empty($ldap_version)) {
		$ldap_version = read_config_option("ldap_version");
	}
	if (empty($ldap_encryption)) {
		$ldap_encryption = read_config_option("ldap_encryption");
	}
	if (empty($ldap_referrals)) {
		$ldap_referrals = read_config_option("ldap_referrals");
	}
	if (empty($ldap_mode)) {
		$ldap_mode = read_config_option("ldap_mode");
	}

	if ($ldap_encryption == "1") {
		$ldap_host = "ldaps://" . $ldap_host;
		$ldap_port = $ldap_port_ssl;
	}else{
		$ldap_host = "ldap://" . $ldap_host;
	}

	if ($ldap_mode == "2") {
		/* specific */
		if (empty($ldap_specific_dn)) {
			$ldap_specific_dn = read_config_option("ldap_specific_dn");
		}
		if (empty($ldap_specific_password)) {
			$ldap_specific_password = read_config_option("ldap_specific_password");
		}
	}elseif ($ldap_mode == "1"){
		/* assume anonymous */
		$ldap_specific_dn = "";
		$ldap_specific_password = "";
	}

	if (empty($ldap_search_base)) {
		$ldap_search_base = read_config_option("ldap_search_base");
	}
	if (empty($ldap_search_filter)) {
		$ldap_search_filter = read_config_option("ldap_search_filter");
	}
	$ldap_search_filter = str_replace("<username>",$username,$ldap_search_filter);

	if ($ldap_mode == "0") {
		/* Just bind mode, make dn and return */
		$output["dn"] = $ldap_dn;
		$output["error_num"] = "0";
		$output["error_text"] = "User found";
		return $output;	
	}

	/* Searching mode */
        /* Setup connection to LDAP server */
        $ldap_conn = @ldap_connect($ldap_host,$ldap_port);

	if ($ldap_conn) {
		/* Set protocol version */
		if (!@ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
			/* protocol error */
			$output["dn"] = "";
			$output["error_num"] = "4";
			$output["error_text"] = "Protocol error, unable to set version";
			@ldap_close($ldap_conn);
			return $output;
		}
		/* set referrals */
		if ($ldap_referrals == "0") {
			if (!@ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0)) {
				/* referrals set error */
				$output["dn"] = "";
				$output["error_num"] = "13";
				$output["error_text"] = "Unable to set referrals option";
				@ldap_close($ldap_conn);
				return $output;
			}
		}
		/* start TLS if requested */
		if ($ldap_encryption == "2") {
			if (!@ldap_start_tls($ldap_conn)) {
				/* TLS startup error */
				$output["dn"] = "";
				$output["error_num"] = "5";
				$output["error_text"] = "Protocol error, unable to start TLS communications";
				@ldap_close($ldap_conn);
				return $output;
			}
		}

		/* bind to the directory */
		if (@ldap_bind($ldap_conn,$ldap_specific_dn,$ldap_specific_password)) {
			/* Search */
		
			$ldap_results = ldap_search($ldap_conn, $ldap_search_base, $ldap_search_filter, array("dn"));
			if ($ldap_results) {
				$ldap_entries =  ldap_get_entries($ldap_conn, $ldap_results);

				if ($ldap_entries["count"] == "1") {
					/* single response return user dn */
					$output["dn"] = $ldap_entries["0"]["dn"];
					$output["error_num"] = "0";
					$output["error_text"] = "User found";
				}elseif ($ldap_entries["count"] > 1) {
					/* more than 1 result */
					$output["dn"] = "";
					$output["error_num"] = "13";
					$output["error_text"] = "More than one matching user found";
				}else{
					/* no search results */
					$output["dn"] = "";
					$output["error_num"] = "3";
					$output["error_text"] = "Unable to find users DN";
				}
			}else{
				/* no search results, user not found*/
				$output["dn"] = "";
				$output["error_num"] = "3";
				$output["error_text"] = "Unable to find users DN";
			}
		}else{
			/* unable to bind */
			$ldap_error = ldap_errno($ldap_conn);
			if ($ldap_error == 0x03) {
				/* protocol error */
				$output["dn"] = "";
				$output["error_num"] = "6";
				$output["error_text"] = "Protocol error";
			}elseif ($ldap_error == 0x31) {
				/* invalid credentials */
				$output["dn"] = "";
				$output["error_num"] = "7";
				$output["error_text"] = "Invalid credentials";
			}elseif ($ldap_error == 0x32) {
				/* insuffient access */
				$output["dn"] = "";
				$output["error_num"] = "8";
				$output["error_text"] = "Insuffient access";
			}elseif ($ldap_error == 0x51) {
				/* unable to connect to server */
				$output["dn"] = "";
				$output["error_num"] = "9";
				$output["error_text"] = "Unable to connect to server";
			}elseif ($ldap_error == 0x55) {
				/* timeout */
				$output["dn"] = "";
				$output["error_num"] = "10";
				$output["error_text"] = "Timeout";
			}else{
				/* general bind error */
				$output["dn"] = "";
				$output["error_num"] = "11";
				$output["error_text"] = "General bind error, LDAP result: " . ldap_error($ldap_conn);
			}
		}
	}else{
		/* unable to setup connection */
		$output["dn"] = "";
		$output["error_num"] = "2";
		$output["error_text"] = "Unable to create LDAP connection object";
	}

	@ldap_close($ldap_conn);

	return $output;

}


?>
