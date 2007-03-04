<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

function api_data_source_save($data_source_id, &$_fields_data_source, $skip_cache_update = false) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id", true);

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_source_id);

	/* field: data_template_id */
	if (isset($_fields_data_source["data_template_id"])) {
		$_fields["data_template_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source["data_template_id"]);
	}

	/* field: host_id */
	if (isset($_fields_data_source["host_id"])) {
		$_fields["host_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source["host_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_source, api_data_source_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("data_source", $_fields, array("id"))) {
		if (empty($data_source_id)) {
			$data_source_id = db_fetch_insert_id();
		}

		if ($skip_cache_update == false) {
			/* update data source title cache */
			api_data_source_title_cache_update($data_source_id);
		}

		/* keep the poller cache up to date */
		update_poller_cache($data_source_id, true);

		return $data_source_id;
	}else{
		return false;
	}
}

function api_data_source_preset_rra_item_copy($data_source_id, $preset_rra_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");
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
			$rra_preset_item["data_source_id"] = $data_source_id;

			if (!api_data_source_rra_item_save(0, $rra_preset_item)) {
				$success = false;
			}
		}
	}

	return $success;
}

function api_data_source_data_template_rra_item_copy($data_source_id, $data_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");
	validate_id_die($data_template_id, "data_template_id");

	/* fetch the selected rra preset */
	$rra_items = api_data_template_rra_item_list($data_template_id);

	$success = true;
	/* copy down each item in the selected rra preset */
	if (is_array($rra_items)) {
		foreach ($rra_items as $rra_item) {
			/* these fields are not needed */
			unset($rra_item["id"]);
			unset($rra_item["data_template_id"]);

			/* associate the rra preset with the current data source */
			$rra_item["data_source_id"] = $data_source_id;

			if (!api_data_source_rra_item_save(0, $rra_item)) {
				$success = false;
			}
		}
	}

	return $success;
}

function api_data_source_rra_item_clear($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	return db_delete("data_source_rra_item",
		array(
			"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id)
			));
}

function api_data_source_rra_item_save($data_source_rra_item_id, $_fields_data_source_rra_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	/* sanity checks */
	validate_id_die($data_source_rra_item_id, "data_source_rra_item_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_source_rra_item) == 0) {
		return false;
	}

	/* sanity check for $preset_rra_id */
	if ((empty($data_source_rra_item_id)) && (empty($_fields_data_source_rra_item["data_source_id"]))) {
		api_log_log("Required data_source_id when data_source_rra_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_source_rra_item["data_source_id"])) && (!db_integer_validate($_fields_data_source_rra_item["data_source_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_source_rra_item_id);

	/* field: preset_rra_id */
	if (!empty($_fields_data_source_rra_item["data_source_id"])) {
		$_fields["data_source_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source_rra_item["data_source_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_source_rra_item, api_data_preset_rra_item_form_list());

	if (db_replace("data_source_rra_item", $_fields, array("id"))) {
		if (empty($data_source_rra_item_id)) {
			$data_source_rra_item_id = db_fetch_insert_id();
		}

		return $data_source_rra_item_id;
	}else{
		return false;
	}
}

function api_data_source_fields_save($data_source_id, &$_fields_data_input) {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	/* flush old fields if the data input type is not a data query */
	if (db_fetch_cell("select data_input_type from data_source where id = " . sql_sanitize($data_source_id)) != DATA_INPUT_TYPE_DATA_QUERY) {
		db_execute("delete from data_source_field where data_source_id = " . sql_sanitize($data_source_id));
	}

	/* save all data input fields */
	reset($_fields_data_input);
	foreach ($_fields_data_input as $field_name => $field_value) {
		db_replace("data_source_field",
			array(
				"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id),
				"name" => array("type" => DB_TYPE_STRING, "value" => $field_name),
				"value" => array("type" => DB_TYPE_STRING, "value" => $field_value)
				),
			array("data_source_id", "name"));
	}

	return true;
}

function api_data_source_remove($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	db_delete("data_source_field",
		array(
			"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id)
			));

	db_delete("data_source_item",
		array(
			"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id)
			));

	db_delete("data_source_rra_item",
		array(
			"data_source_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id)
			));

	db_delete("data_source",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_id)
			));
}

function api_data_source_rra_item_remove($data_source_rra_item_id) {
	/* sanity checks */
	validate_id_die($data_source_rra_item_id, "data_source_rra_item_id");

	return db_delete("data_source_rra_item",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_source_rra_item_id)
			));
}

function api_data_source_enable($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	db_execute("UPDATE data_source SET active = 1 WHERE id = " . sql_sanitize($data_source_id));
	update_poller_cache($data_source_id, false);
}

function api_data_source_disable($data_source_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	db_execute("DELETE FROM poller_item WHERE local_data_id = " . sql_sanitize($data_source_id));
	db_execute("UPDATE data_source SET active='' WHERE id = " . sql_sanitize($data_source_id));
}

function api_data_source_host_update($data_source_id, $host_id) {
	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");
	validate_id_die($host_id, "host_id", true);

	db_execute("UPDATE data_source SET host_id = " . sql_sanitize($host_id) . " WHERE id = " . sql_sanitize($data_source_id));

	/* make sure that host variables in the title stay up to date */
	api_data_source_title_cache_update($data_source_id);
}

function api_data_source_item_save($data_source_item_id, &$_fields_data_source_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* sanity checks */
	validate_id_die($data_source_item_id, "data_source_item_id", true);

	/* sanity check for $data_source_id */
	if ((empty($data_source_item_id)) && (empty($_fields_data_source_item["data_source_id"]))) {
		api_log_log("Required data_source_id when data_source_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_source_item["data_source_id"])) && (!is_numeric($_fields_data_source_item["data_source_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_source_item_id);

	/* field: data_source_id */
	if (!empty($_fields_data_source_item["data_source_id"])) {
		$_fields["data_source_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source_item["data_source_id"]);
	}

	/* field: data_template_item_id */
	if (!empty($_fields_data_source_item["data_template_item_id"])) {
		$_fields["data_template_item_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_data_source_item["data_template_item_id"]);
	}

	/* field: field_input_value */
	if (isset($_fields_data_source_item["field_input_value"])) {
		$_fields["field_input_value"] = array("type" => DB_TYPE_STRING, "value" => $_fields_data_source_item["field_input_value"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_source_item, api_data_source_item_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("data_source_item", $_fields, array("id"))) {
		if (!empty($_fields_data_source_item["data_source_id"])) {
			/* since the data source path is based in part on the data source item name, it makes sense
			 * to update it here */
			api_data_source_path_get_update($_fields_data_source_item["data_source_id"]);
		}

		return true;
	}else{
		return false;
	}
}

function api_data_source_item_remove($data_source_item_id) {
	/* sanity checks */
	validate_id_die($data_source_item_id, "data_source_item_id");

	db_execute("DELETE FROM data_source_item WHERE id = " . sql_sanitize($data_source_item_id));
}

/* update_data_source_title_cache - updates the title cache for a single data source
   @arg $data_source_id - (int) the ID of the data source to update the title cache for */
function api_data_source_title_cache_update($data_source_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* sanity checks */
	validate_id_die($data_source_id, "data_source_id");

	db_execute("UPDATE data_source SET name_cache = '" . sql_sanitize(addslashes(api_data_source_title_get($data_source_id))) . "' WHERE id = " . sql_sanitize($data_source_id));
}

/* api_data_source_path_get_update - set the current data source path or generates a new one if a path
     does not already exist
   @arg $data_source_id - (int) the ID of the data source to set a path for */
function api_data_source_path_get_update($data_source_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/string.php");

	$host_part = ""; $ds_part = "";

	/* we don't want to change the current path if one already exists */
	if ((empty($data_source_id)) || (db_fetch_cell("select rrd_path from data_source where id = $data_source_id") != "")) {
		return;
	}

	/* try any prepend the name with the host description */
	$hostname = db_fetch_cell("select host.description from host,data_source where data_source.host_id=host.id and data_source.id = $data_source_id");

	if ($hostname != "") {
		$host_part = strtolower(clean_up_name($hostname)) . "_";
	}

	/* then try and use the internal DS name to identify it */
	$data_source_item_name = db_fetch_cell("select data_source_name from data_source_item where data_source_id = $data_source_id order by id limit 1");

	if ($data_source_item_name != "") {
		$ds_part = strtolower(clean_up_name($data_source_item_name));
	}else{
		$ds_part = "ds";
	}

	$rrd_path = "<path_rra>/$host_part$ds_part" . "_" . "$data_source_id.rrd";

	/* update our changes to the db */
	db_execute("update data_source set rrd_path = '$rrd_path' where id = $data_source_id");

	return $rrd_path;
}

?>
