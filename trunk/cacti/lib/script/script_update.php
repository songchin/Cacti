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

function api_script_save($script_id, $_fields_script) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	/* sanity checks */
	validate_id_die($script_id, "script_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_script) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $script_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_script, api_script_form_list());

	if (db_replace("data_input", $_fields, array("id"))) {
		if (empty($script_id)) {
			$script_id = db_fetch_insert_id();
		}

		return $script_id;
	}else{
		return false;
	}
}

function api_script_remove($script_id) {
	/* sanity checks */
	validate_id_die($script_id, "script_id");

	/* base tables */
	db_delete("data_input",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $script_id)
			));
	db_delete("data_input_fields",
		array(
			"data_input_id" => array("type" => DB_TYPE_INTEGER, "value" => $script_id)
			));
	db_delete("data_input_data",
		array(
			"data_input_id" => array("type" => DB_TYPE_INTEGER, "value" => $script_id)
			));
}

function api_script_field_save($script_field_id, $_fields_script_field) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	/* sanity checks */
	validate_id_die($script_field_id, "script_field_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_script_field) == 0) {
		return false;
	}

	/* sanity check for $script_id */
	if ((empty($script_field_id)) && (empty($_fields_script_field["data_input_id"]))) {
		api_log_log("Required script_id when script_field_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_script_field["data_input_id"])) && (!db_integer_validate($_fields_script_field["data_input_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $script_field_id);

	/* field: graph_tree_id */
	if (isset($_fields_script_field["data_input_id"])) {
		$_fields["data_input_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_script_field["data_input_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_script_field, api_script_field_form_list());

	if (db_replace("data_input_fields", $_fields, array("id"))) {
		if (empty($script_field_id)) {
			$script_field_id = db_fetch_insert_id();
		}

		return $script_field_id;
	}else{
		return false;
	}
}

function api_script_field_remove($script_field_id) {
	/* sanity checks */
	validate_id_die($script_field_id, "script_field_id");

	/* base tables */
	db_delete("data_input_fields",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $script_field_id)
			));
	db_delete("data_input_data",
		array(
			"data_input_field_id" => array("type" => DB_TYPE_INTEGER, "value" => $script_field_id)
			));
}

?>
