<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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
 * User/Group viewing actions
 */

require_once(CACTI_BASE_PATH . "/include/auth/auth_constants.php");

/*
 * List auth records
 *
 * Given filter array, return list of user records
 *
 * @param array $filter_array filter array, field => value elements
 * @return array user records
 */
function auth_control_list ($control_type, $filter_array, $limit = -1, $offset = -1) {



}


/*
 * Returns information about an auth control entry
 *
 * Returns information array for a given auth control entries, or single requested value.
 *
 * @return array fields => values or value, false on error
 */
function auth_control_get($control_type, $control_id, $data_field = "") {



}


/*
 * Returns information about an auth control data
 *
 * Returns information array for a given auth control data entries, or single requested value.
 *
 * @return array of data rows, false on error
 */
function auth_control_data_get($data, $category = "SYSTEM", $plugin_id = 0, $control_id = 0) {

	/* Validate input */
	if (!is_array($data)) {
		return 0;
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
	$sql = "SELECT * FROM auth_data WHERE ";
	if (sizeof($data) > 0) {
		$sql .= "name IN(";
		foreach ($data as $name) {
			$sql .= "'" . $name . "',";
		}
		$sql = substr($sql,0,strlen($sql) - 1) . ") AND ";
	}
	$sql .= "category = '" . $category . "' AND ";
	$sql .= "plugin_id = " . $plugin_id . " AND ";
	$sql .= "control_id = " . $control_id;

	/* Execute query and return */
	return db_fetch_assoc($sql);

}




?>
