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


/**
 * Saves auth control data for a certain control id
 *
 * Saves auth control data for a certain control id
 *
 * @return 1 on success, 0 on error
 */
function auth_control_data_save($data, $category = "SYSTEM", $enable_user_edit = 0, $plugin_id = 0, $control_id = 0) {

	/* Validate input */
	if (!is_array($data)) {
		return 0;
	}
	if (!is_numeric($enable_user_edit)) {
		return 0;
	}
	if (($enable_user_edit < 0) || ($enable_user_edit > 1)) {
		$enable_user_edit = 0;
	}
	if (!is_numeric($plugin_id)) {
		return 0;
	}
	if (!is_numeric($control_id)) {
		return 0;
	}
	if (empty($control_id)) {
		$control_id = $_SESSION["sess_user_id"];
	}

	/* Create SQL Query */
	$username = db_fetch_cell("SELECT username FROM user_auth WHERE id = " . $control_id, "username");
	$sql = "REPLACE INTO `auth_data` (`control_id`,`plugin_id`,`category`,`name`,`value`,`enable_user_edit`,`updated_when`,`updated_by`) VALUES ";
	foreach ($data as $name => $value) {
		$sql .= "(" . $control_id . "," . $plugin_id . ",'" . $category . "','" . $name . "','" . $value . "'," . $enable_user_edit . ",NOW(),'" . $username . "'),";
	}
	$sql = substr($sql,0,strlen($sql) - 1);

	/* Execute query and return */
	return db_execute($sql);

}

?>
