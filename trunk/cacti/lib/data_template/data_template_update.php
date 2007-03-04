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

function api_data_template_save($data_template_id, $_fields_data_source) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");

	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_source) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_template_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_source, api_data_template_form_list());
	$_fields += sql_get_database_field_array($_fields_data_source, api_data_source_form_list());

	if (db_replace("data_template", $_fields, array("id"))) {
		if (empty($data_template_id)) {
			$data_template_id = db_fetch_insert_id();
		}else{
			/* push out data template fields */
			api_data_template_propagate($data_template_id);
		}

		return $data_template_id;
	}else{
		return false;
	}
}

function api_data_template_rra_item_save($data_template_rra_item_id, $_fields_data_template_rra_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_template_rra_item_id, "data_template_rra_item_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_template_rra_item) == 0) {
		return false;
	}

	/* sanity check for $preset_rra_id */
	if ((empty($data_template_rra_item_id)) && (empty($_fields_data_template_rra_item["data_template_id"]))) {
		api_log_log("Required data_template_id when data_template_rra_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_template_rra_item["data_template_id"])) && (!db_integer_validate($_fields_data_template_rra_item["data_template_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_template_rra_item_id);

	/* field: preset_rra_id */
	if (!empty($_fields_data_template_rra_item["data_template_id"])) {
		$_fields["data_template_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_template_rra_item["data_template_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_template_rra_item, api_data_preset_rra_item_form_list());

	if (db_replace("data_template_rra_item", $_fields, array("id"))) {
		if (empty($data_template_rra_item_id)) {
			$data_template_rra_item_id = db_fetch_insert_id();
		}

		return $data_template_rra_item_id;
	}else{
		return false;
	}
}

function api_data_template_preset_rra_item_copy($data_template_id, $preset_rra_id) {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");
	validate_id_die($preset_rra_id, "preset_rra_id");

	/* fetch the selected rra preset */
	$rra_preset_items = api_data_preset_rra_item_list($preset_rra_id);

	$success = true;
	/* copy down each item in the selected rra preset */
	if (is_array($rra_preset_items)) {
		foreach ($rra_preset_items as $rra_preset_item) {
			/* these fields are not needed */
			unset($rra_preset_item["id"]);
			unset($rra_preset_item["preset_rra_id"]);

			/* associate the rra preset with the current data source */
			$rra_preset_item["data_template_id"] = $data_template_id;

			if (!api_data_template_rra_item_save(0, $rra_preset_item)) {
				$success = false;
			}
		}
	}

	return $success;
}

function api_data_template_rra_item_clear($data_template_id) {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	return db_delete("data_template_rra_item",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));
}

function api_data_template_suggested_values_save($data_template_id, $_fields_suggested_values) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	/* insert the new custom field values */
	if (is_array($_fields_suggested_values) > 0) {
		foreach ($_fields_suggested_values as $field_name => $field_array) {
			foreach ($field_array as $field_item) {
				if (empty($field_item["id"])) {
					db_insert("data_template_suggested_value",
						array(
							"id" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
							"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id),
							"field_name" => array("type" => DB_TYPE_STRING, "value" => $field_name),
							"value" => array("type" => DB_TYPE_STRING, "value" => $field_item["value"]),
							"sequence" => array("type" => DB_TYPE_INTEGER, "value" => seq_get_current(0, "sequence", "data_template_suggested_value", "data_template_id = " . sql_sanitize($data_template_id) . " and field_name = '" . sql_sanitize($field_name) . "'"))
							),
						array("id"));
				}else{
					db_update("data_template_suggested_value",
						array(
							"id" => array("type" => DB_TYPE_INTEGER, "value" => $field_item["id"]),
							"value" => array("type" => DB_TYPE_STRING, "value" => $field_item["value"])
							),
						array("id"));
				}
			}
		}
	}
}

function api_data_template_input_fields_save($data_template_id, $_fields_input_fields) {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	/* clear out the old custom field values */
	db_delete("data_template_field",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));

	/* insert the new custom field values */
	if (is_array($_fields_input_fields) > 0) {
		foreach ($_fields_input_fields as $field_name => $field_array) {
			db_insert("data_template_field",
				array(
					"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id),
					"name" => array("type" => DB_TYPE_STRING, "value" => $field_name),
					"t_value" => array("type" => DB_TYPE_INTEGER, "value" => $field_array["t_value"]),
					"value" => array("type" => DB_TYPE_STRING, "value" => $field_array["value"])
					),
				array("data_template_id", "name"));
		}
	}
}

function api_data_template_item_save($data_template_item_id, $_fields_data_source_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");

	/* sanity checks */
	validate_id_die($data_template_item_id, "data_template_item_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_source_item) == 0) {
		return false;
	}

	/* sanity check for $data_template_id */
	if ((empty($data_template_item_id)) && (empty($_fields_data_source_item["data_template_id"]))) {
		api_log_log("Required data_template_id when data_template_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_source_item["data_template_id"])) && (!db_integer_validate($_fields_data_source_item["data_template_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_template_item_id);

	/* field: data_template_id */
	if (!empty($_fields_data_source_item["data_template_id"])) {
		$_fields["data_template_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source_item["data_template_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_source_item, api_data_source_item_form_list());

	if (db_replace("data_template_item", $_fields, array("id"))) {
		if (empty($data_template_item_id)) {
			$data_template_item_id = db_fetch_insert_id();
		}

		/* push out data template item fields */
		api_data_source_item_propagate($data_template_item_id);

		return $data_template_item_id;
	}else{
		return false;
	}
}

function api_data_template_remove($data_template_id) {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	/* base tables */
	db_delete("data_template_rra",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));
	db_delete("data_template_field",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));
	db_delete("data_template_item",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));
	db_delete("data_template_rra_item",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));
	db_delete("data_template",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_id)
			));

	/* detach this template from all data sources */
	db_execute("UPDATE data_source SET data_template_id = 0 WHERE data_template_id = " . sql_sanitize($data_template_id));
}

function api_data_template_rra_item_remove($data_template_rra_item_id) {
	/* sanity checks */
	validate_id_die($data_template_rra_item_id, "data_template_rra_item_id");

	return db_delete("data_template_rra_item",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_rra_item_id)
			));
}

function api_data_template_item_remove($data_template_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* sanity checks */
	validate_id_die($data_template_item_id, "data_template_item_id");

	/* retrieve information about this data template */
	$data_template_item = api_data_template_item_get($data_template_item_id);

	$data_sources = db_fetch_assoc("select id from data_source where data_template_id = " . $data_template_item["data_template_id"]);

	/* delete all attached data source items */
	if (is_array($data_sources) > 0) {
		foreach ($data_sources as $item) {
			db_delete("data_source_item",
				array(
					"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $item["id"]),
					"data_source_name" => array("type" => DB_TYPE_STRING, "value" => $data_template_item["data_source_name"])
					));
		}
	}

	db_delete("data_template_item",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_template_item_id)
			));
}

?>
