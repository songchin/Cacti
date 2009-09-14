<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/* api_user_save
   @arg $array - an array containing each column -> value mapping in the user_auth table, always remember the id field
   @return - id of user saved, new or existing
   reference user_admin.php for examples */
function api_user_save($array) {

	if (db_replace("user_auth", $array)) {

		/* logging */
		if (empty($array["id"])) {
			/* New user */
			$user_id = db_fetch_insert_id();
			log_save(sprintf(_("USER_ADMIN: User id '%s' added"), $user_id), SEV_NOTICE, FACIL_AUTH);
		}else{
			/* existing user */
			$user_id = $array["id"]["value"];
			log_save(sprintf(_("USER_ADMIN: User id '%s' updated"), $user_id), SEV_NOTICE, FACIL_AUTH);
		}
		return $user_id;
	} else {
		log_save(sprintf(_("USER_ADMIN: Error saving user id '%s' "), $user_id), SEV_ERROR, FACIL_AUTH);
		return 0;
	}

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
		if (!sizeof(db_fetch_row("select id from user_auth where id =" . sql_sanitize($user_id) . " and password = '" . md5($password_old) . "' and realm = 0"))) {
			/* Password validation error */
			return 2;
		}
	}

	/* validate user exists */
	if (sizeof(db_fetch_row("select id from user_auth where id =" . sql_sanitize($user_id) . " and realm = 0"))) {
		if (db_execute("update user_auth set password = '" . md5($password_new) . "',must_change_password = '', password_change_last = NOW() where id = " . sql_sanitize($user_id)) == 1) {
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
		if (($user_id != 1) && (is_numeric($user_id))) {
			db_execute("delete from user_auth where id = " . sql_sanitize($user_id));
			db_execute("delete from user_auth_realm where user_id = " . sql_sanitize($user_id));
			db_execute("delete from user_auth_perms where user_id = " . sql_sanitize($user_id));
			db_execute("delete from settings_graphs where user_id = " . sql_sanitize($user_id));
			log_save(sprintf(_("USER_ADMIN: User id '%s' deleted"), $user_id), SEV_NOTICE, FACIL_AUTH);
			return true;
		}
	}
}

/* api_user_enable - enables  a user account
   @arg $user_id - user id */
function api_user_enable($user_id) {
	if (!empty($user_id)) {
		$user = array();
		$user["id"] = array("type" => DB_TYPE_INTEGER, "value" => $user_id);
		$user["enabled"] = array("type" => DB_TYPE_INTEGER, "value" => 1);
		if (db_update("user_auth",$user)) {
			log_save(sprintf(_("USER_ADMIN: User id '%s' enabled"), $user_id), SEV_NOTICE, FACIL_AUTH);
			return true;
		} else {
			log_save(sprintf(_("USER_ADMIN: Failed to enable user id0 '%s'"), $user_id), SEV_ERROR, FACIL_AUTH);
			return false;
		}
	}
}

/* api_user_disable - disables a user account
   @arg $user_id - user id */
function api_user_disable($user_id) {
	if (!empty($user_id)) {
		$user = array();
		$user["id"] = array("type" => DB_TYPE_INTEGER, "value" => $user_id);
		$user["enabled"] = array("type" => DB_TYPE_INTEGER, "value" => 0);
		if (db_update("user_auth",$user)) {
			log_save(sprintf(_("USER_ADMIN: User id '%s' disabled"), $user_id), SEV_NOTICE, FACIL_AUTH);
			return true;
		} else {
			log_save(sprintf(_("USER_ADMIN: Failed disable user id '%s'"), $user_id), SEV_ERROR, FACIL_AUTH);
			return false;
		}
	}
}

/* api_user_expire_length_set - sets a users expire interval
   @arg $user_id - user id
   @arg $interval - integer, the number of days */
function api_user_expire_length_set($user_id, $interval) {
	if (!empty($user_id)) {
		$user = array();
		$user["id"] = array("type" => DB_TYPE_INTEGER, "value" => sql_sanitize($user_id));
		$user["password_expire_length"] = array("type" => DB_TYPE_INTEGER, "value" => sql_sanitize($interval));
	 	if (db_update("user_auth",$user)) {
			log_save(sprintf(_("USER_ADMIN: User id '%s' expiration length set to '%s'"), $user_id,$interval), SEV_ERROR, FACIL_AUTH);
			return true;
		} else {
			log_save(sprintf(_("USER_ADMIN: Failed to set user id '%s' expiration length set to '%s'"), $user_id,$interval), SEV_ERROR, FACIL_AUTH);
			return false;
		}
	}
}

/* api_user_copy - copies a user account
   @arg $template_user - username of account that should be used as the template
   @arg $new_user - username of the account to be created
   @arg $new_realm - the realm the new account should be a member of
   @return - '0' success, '1' error */
function api_user_copy($template_user, $new_user, $new_realm=-1) {

	$user_auth = db_fetch_row("select * from user_auth where username = '" . sql_sanitize($template_user) . "'");
        $user_auth['username'] = sql_sanitize($new_user);
	if ($new_realm != -1) {
		$user_auth['realm'] = sql_sanitize($new_realm);
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

        $user_auth_perms = db_fetch_assoc("select * from user_auth_perms where user_id = " . $old_id);
        foreach ($user_auth_perms as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'user_auth_perms', array('user_id', 'item_id', 'type'));
        }

        $user_auth_realm = db_fetch_assoc("select * from user_auth_realm where user_id = " . $old_id);
        foreach ($user_auth_realm as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'user_auth_realm', array('realm_id', 'user_id'));
        }

        $settings_graphs = db_fetch_assoc("select * from settings_graphs where user_id = " . $old_id);
        foreach ($settings_graphs as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'settings_graphs', array('user_id', 'name'));
        }

        $settings_tree = db_fetch_assoc("select * from settings_tree where user_id = " . $old_id);
        foreach ($settings_tree as $row) {
                $row['user_id'] = $new_id;
                sql_save($row, 'settings_tree', array('user_id', 'graph_tree_item_id'));
        }
	log_save(sprintf(_("USER_ADMIN: User '%s' copied to user '%s'"), $template_user, $new_user), SEV_NOTICE, FACIL_AUTH);

	return 0;
}


/*
########################################
# user realm (permissions) functions
########################################
*/

/* api_user_realms_save
  @arg $user_id - user id
  @arg $array - single dimension array of realm_id that are granted, empty array will clear all realms */
function api_user_realms_save($user_id,$array) {

	/* validate */
	if (!empty($user_id)) {
		if (is_numeric($user_id)) {
			/* remove any existing permissions */
			db_execute("delete from user_auth_realm where user_id = " . sql_sanitize($user_id));

			/* insert the new permission */
			foreach($array as $realm_id) {
				db_execute("replace into user_auth_realm (user_id,realm_id) values (" . sql_sanitize($user_id) . "," . $realm_id . ")");
			}
			log_save(sprintf(_("USER_ADMIN: User id '%s' realms updated"), $user_id), SEV_NOTICE, FACIL_AUTH);
		}
	}

}

/*
########################################
# graph settings functions
########################################
*/

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

	if (! is_numeric($user_id)) {
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
					db_execute("replace into settings_graphs (user_id,name,value) values (" . sql_sanitize($user_id) . ",'" . $sub_field_name . "', '" . (isset($array[$sub_field_name]) ? $array[$sub_field_name] : "") . "')");
				}
			}else{
				/* normal field */
				db_execute("replace into settings_graphs (user_id,name,value) values (" . sql_sanitize($user_id) . ",'$field_name', '" . (isset($array[$field_name]) ? $array[$field_name] : "") . "')");
			}
		}
	}
	log_save(sprintf(_("USER_ADMIN: User id '%s' graph settings updated"), $user_id), SEV_NOTICE, FACIL_AUTH);

	return 0;


}

/*
########################################
# graph permission functions
########################################
*/

/* api_user_graph_perms_add
  @arg $type - graph, tree, host, graph_template types
  @arg $user_id - user id
  @arg $item_id = item id of the type of item, example type = graph id is from graph table */
function api_user_graph_perms_add($type,$user_id,$item_id) {
	global $graph_perms_type_array;

	/* validation */
	if ((!empty($graph_perms_type_array[$type])) && (!empty($user_id)) && (!empty($item_id) && (is_numeric($user_id)) && (is_numeric($item_id)))) {
		db_execute("replace into user_auth_perms (user_id,item_id,type) values (" . sql_sanitize($user_id) . "," . sql_sanitize($item_id) . ",'" . $graph_perms_type_array[$type] . "')");
		log_save(sprintf(_("USER_ADMIN: User id '%s' graph permissions added for type '%s' item id '%s'"), $user_id, $type, $item_id), SEV_NOTICE, FACIL_AUTH);
	}

}

/* api_user_graph_perms_remove
  @arg $type - graph, tree, host, graph_template types
  @arg $user_id - user id
  @arg $item_id - item id of type of item, example type = graph id is from graph table */
function api_user_graph_perms_remove($type,$user_id,$item_id) {
	global $graph_perms_type_array;

	/* validation */
	if ((!empty($graph_perms_type_array[$type])) && (!empty($user_id)) && (!empty($item_id)  && (is_numeric($user_id)) && (is_numeric($item_id)))) {
		db_execute("delete from user_auth_perms where type = '" . $graph_perms_type_array[$type] . "' and user_id = " . sql_sanitize($user_id) . " and item_id = " . sql_sanitize($item_id));
		log_save(sprintf(_("USER_ADMIN: User id '%s' graph permissions removed for type '%s' item id '%s'"), $user_id, $type, $item_id), SEV_NOTICE, FACIL_AUTH);
	}

}

?>
