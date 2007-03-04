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

function api_data_preset_rra_save($data_preset_rra_id, $_fields_data_preset_rra) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_preset_rra_id, "data_preset_rra_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_preset_rra) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_preset_rra_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_preset_rra, api_data_preset_rra_form_list());

	if (db_replace("preset_rra", $_fields, array("id"))) {
		if (empty($data_preset_rra_id)) {
			$data_preset_rra_id = db_fetch_insert_id();
		}

		return $data_preset_rra_id;
	}else{
		return false;
	}
}

function api_data_preset_rra_item_save($data_preset_rra_item_id, $_fields_data_preset_rra_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_preset_rra_item_id, "data_preset_rra_item_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_preset_rra_item) == 0) {
		return false;
	}

	/* sanity check for $preset_rra_id */
	if ((empty($data_preset_rra_item_id)) && (empty($_fields_data_preset_rra_item["preset_rra_id"]))) {
		api_log_log("Required preset_rra_id when data_preset_rra_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_preset_rra_item["preset_rra_id"])) && (!db_integer_validate($_fields_data_preset_rra_item["preset_rra_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_preset_rra_item_id);

	/* field: preset_rra_id */
	if (!empty($_fields_data_preset_rra_item["preset_rra_id"])) {
		$_fields["preset_rra_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_preset_rra_item["preset_rra_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_preset_rra_item, api_data_preset_rra_item_form_list());

	if (db_replace("preset_rra_item", $_fields, array("id"))) {
		if (empty($data_preset_rra_item_id)) {
			$data_preset_rra_item_id = db_fetch_insert_id();
		}

		return $data_preset_rra_item_id;
	}else{
		return false;
	}
}

function api_data_preset_rra_remove($data_preset_id) {
	/* sanity checks */
	validate_id_die($data_preset_id, "data_preset_id");

	db_delete("preset_rra_item",
		array(
			"preset_rra_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_preset_id)
			));

	db_delete("preset_rra",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_preset_id)
			));
}

function api_data_preset_rra_item_remove($data_preset_rra_item_id) {
	/* sanity checks */
	validate_id_die($data_preset_rra_item_id, "data_preset_rra_item_id");

	api_data_preset_rra_fingerprint_update($data_preset_rra_item_id, true);

	return db_delete("preset_rra_item",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_preset_rra_item_id)
			));
}

?>
