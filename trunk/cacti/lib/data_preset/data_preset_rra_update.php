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

function api_data_preset_rra_save($data_preset_rra_id, $_fields_data_preset_rra) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_preset_rra_id, "data_preset_rra_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_preset_rra) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_preset_rra_id);

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

function api_data_preset_rra_fingerprint_update($data_preset_rra_item_id, $remove_item = false) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	$data_preset_rra_item = api_data_preset_rra_item_get($data_preset_rra_item_id);

	if (is_array($data_preset_rra_item)) {
		/* we need a copy of the actual rra preset to get the existing fingerprint */
		$data_preset_rra = api_data_preset_rra_get($data_preset_rra_item["preset_rra_id"]);

		/* break the fingerprint into its individual components */
		if ($data_preset_rra["fingerprint"] == "") {
			$hash_parts = array();
		}else{
			$hash_parts = explode("|", $data_preset_rra["fingerprint"]);
		}

		$updated_hash = false;
		/* loop through each fingerprint component */
		for ($i = 0; $i < sizeof($hash_parts); $i++) {
			/* see if we can find the current rra item id in the fingerprint */
			if (substr($hash_parts[$i], 0, 4) == str_pad($data_preset_rra_item_id, 4, "0", STR_PAD_LEFT)) {
				/* if we are to remove the component, simply unset it from the array */
				if ($remove_item === true) {
					unset($hash_parts[$i]);
				/* otherwise, generate an updated hash for this component of the fingerprint */
				}else{
					$hash_parts[$i] = api_data_preset_rra_item_fingerprint_generate($data_preset_rra_item);
				}

				$updated_hash = true;
			}
		}

		/* if no match was found above, generate a new component for the current rra item */
		if (($updated_hash === false) && ($remove_item === false)) {
			$hash_parts[] = api_data_preset_rra_item_fingerprint_generate($data_preset_rra_item);
		}

		/* splice the fingerprint back together from the array */
		$new_fingerprint = implode("|", $hash_parts);

		/* only update the database if the fingerprint has changed */
		if ($new_fingerprint != $data_preset_rra["fingerprint"]) {
			$_fields = array(
				"id" => array("type" => DB_TYPE_NUMBER, "value" => $data_preset_rra_item["preset_rra_id"]),
				"fingerprint" => array("type" => DB_TYPE_STRING, "value" => $new_fingerprint)
				);

			if (db_update("preset_rra", $_fields, array("id"))) {
				return true;
			}
		}else{
			return true;
		}
	}

	return false;
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
	} else if ((isset($_fields_data_preset_rra_item["preset_rra_id"])) && (!db_number_validate($_fields_data_preset_rra_item["preset_rra_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_preset_rra_item_id);

	/* field: preset_rra_id */
	if (!empty($_fields_data_preset_rra_item["preset_rra_id"])) {
		$_fields["preset_rra_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_data_preset_rra_item["preset_rra_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_preset_rra_item, api_data_preset_rra_item_form_list());

	if (db_replace("preset_rra_item", $_fields, array("id"))) {
		if (empty($data_preset_rra_item_id)) {
			$data_preset_rra_item_id = db_fetch_insert_id();
		}

		api_data_preset_rra_fingerprint_update($data_preset_rra_item_id);

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
			"preset_rra_id" => array("type" => DB_TYPE_NUMBER, "value" => $data_preset_id)
			));

	db_delete("preset_rra",
		array(
			"id" => array("type" => DB_TYPE_NUMBER, "value" => $data_preset_id)
			));
}

function api_data_preset_rra_item_remove($data_preset_rra_item_id) {
	/* sanity checks */
	validate_id_die($data_preset_rra_item_id, "data_preset_rra_item_id");

	api_data_preset_rra_fingerprint_update($data_preset_rra_item_id, true);

	return db_delete("preset_rra_item",
		array(
			"id" => array("type" => DB_TYPE_NUMBER, "value" => $data_preset_rra_item_id)
			));
}

?>
