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

function api_package_save($package_id, &$_fields_package) {
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");

	/* sanity checks */
	validate_id_die($package_id, "package_id", true);

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $package_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_package, api_package_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("package", $_fields, array("id"))) {
		if (empty($package_id)) {
			return db_fetch_insert_id();
		}else{
			return $package_id;
		}
	}else{
		return false;
	}
}

?>
