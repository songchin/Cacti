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

/* coming soon */
function api_user_save($array) {

	$user_id = sql_save($array, "user_auth");

	return $user_id;

}









/* api_user_changepassword - changes users password, old password is optional
   @arg $user_id - User id to change password for
   @arg $password_new - New password to set for user 
   @arg $password_old - Old password, optional, if passed will validate 
   @returns - '0' is success, '1' is general error, '2' fail password authentication,
              '3' user not found */
function api_user_changepassword($user_id, $password_new, $password_old="") {

	/* validate old password */
	if (!empty($password_old)) {
		if (!sizeof(db_fetch_row("select id from user_auth where id ='" . $user_id . "' and password = '" . md5($password_old) . "' and realm = 0"))) {
			/* Password validation error */
			return 2;	
		}
	}

	/* validate user exists */
	if (sizeof(db_fetch_row("select id from user_auth where id ='" . $user_id . "' and realm = 0"))) {
		if (db_execute("update user_auth set password = '" . md5($password_new) . "' where id = '" . $user_id . "'") == 1) {
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
		/* remove trailing AND */
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
	$sql_query = "SELECT * FROM user_auth WHERE ";
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
	$last_login = db_fetch_row("select DATE_FORMAT(max(time),'%M %e %Y %H:%i:%s') as time from user_log where user_id = " . $user["id"] . " and result = 1");
	$user["time"] = $last_login["time"];

	return $user;

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


/* api_user_copy - copies a user account
   @arg $template_user - username of account that should be used as the template
   @arg $new_user - username of the account to be created
   @arg $new_realm - the realm the new account should be a member of */
function api_user_copy($template_user, $new_user, $new_realm=-1) {
        $user_auth = db_fetch_row("select * from user_auth where username = '$template_user'");
        $user_auth['username'] = $new_user;
	if ($new_realm != -1) {
		$user_auth['realm'] = $new_realm;
        }
	$old_id = $user_auth['id'];
        $user_auth['id'] = 0;

        $new_id = sql_save($user_auth, 'user_auth');

        $user_auth_perms = db_fetch_assoc("select * from user_auth_perms where user_id = '$old_id'");
        foreach ($user_auth_perms as $user_auth_perm) {
                $user_auth_perm['user_id'] = $new_id;
                sql_save($user_auth_perm, 'user_auth_perms', array('user_id', 'item_id', 'type'));
        }

        $user_auth_realm = db_fetch_assoc("select * from user_auth_realm where user_id = '$old_id'");
        foreach ($user_auth_realm as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'user_auth_realm', array('realm_id', 'user_id'));
        }

        $settings_graphs = db_fetch_assoc("select * from settings_graphs where user_id = '$old_id'");
        foreach ($settings_graphs as $settings_graph) {
                $settings_graph['user_id'] = $new_id;
                sql_save($settings_graph, 'settings_graphs', array('user_id', 'name'));
        }

        $settings_tree = db_fetch_assoc("select * from settings_tree where user_id = '$old_id'");
        foreach ($settings_tree as $row) {
                $row['user_id'] = $new_id;
                sql_save($settings_tree, 'settings_tree', array('user_id', 'graph_tree_item_id'));
        }

}






