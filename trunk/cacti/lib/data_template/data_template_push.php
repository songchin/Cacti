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

function copy_data_template_to_data_source($data_template_id, $host_id = 0, $data_query_id = 0, $data_query_index = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		return false;
	}

	/* fetch field lists */
	$fields_data_source = api_data_source_fields_list();
	$fields_data_source_item = api_data_source_item_fields_list();

	/* fetch information from that data template */
	$data_template = get_data_template($data_template_id);
	$data_template_rras = get_data_template_rras($data_template_id);
	$_data_template_input_fields = get_data_template_input_fields($data_template_id);

	if (sizeof($data_template) > 0) {
		/* copy down per-data source only fields */
		$_fields = array();
		$_fields["id"] = "0";
		$_fields["data_template_id"] = $data_template_id;
		$_fields["host_id"] = $host_id;

		/* evaluate suggested values: data query-based graphs */
		if ((!empty($data_query_id)) && ($data_query_index != "")) {
			$_fields["name"] = evaluate_data_query_suggested_values($host_id, $data_query_id, $data_query_index, "data_template_suggested_value", "data_template_id = " . sql_sanitize($data_template_id) . " and field_name = 'name'", 0);
		/* evaluate suggested values: non-data query-based graphs */
		}else{
			$_fields["name"] = db_fetch_cell("select value from data_template_suggested_value where data_template_id = " . sql_sanitize($data_template_id) . " and field_name = 'name' order by sequence limit 1");
		}

		/* copy down all visible fields */
		foreach (array_keys($fields_data_source) as $field_name) {
			if (isset($data_template[$field_name])) {
				$_fields[$field_name] = $data_template[$field_name];
			}
		}

		if (api_data_source_save(0, $_fields, $data_template_rras, true)) {
			$data_source_id = db_fetch_insert_id();

			api_log_log("Cloning data source [ID#$data_source_id] from template [ID#$data_template_id]", SEV_DEBUG);

			/* reformat the $_data_template_input_fields to be more compatible with api_data_source_save() */
			$data_template_input_fields = array();
			foreach (array_keys($_data_template_input_fields) as $field_name) {
				$data_template_input_fields[$field_name] = $_data_template_input_fields[$field_name]["value"];
			}

			/* handle data source custom fields */
			api_data_source_fields_save($data_source_id, $data_template_input_fields);

			/* move onto the data source items */
			$data_template_items = get_data_template_items($data_template_id);

			if (sizeof($data_template_items) > 0) {
				foreach ($data_template_items as $data_template_item) {
					/* copy down per-data source only fields */
					$_fields = array();
					$_fields["id"] = "0";
					$_fields["data_source_id"] = $data_source_id;
					$_fields["data_template_item_id"] = $data_template_item["id"];
					$_fields["field_input_value"] = $data_template_item["field_input_value"];

					/* copy down all visible fields */
					foreach (array_keys($fields_data_source_item) as $field_name) {
						$_fields[$field_name] = $data_template_item[$field_name];
					}

					if (!api_data_source_item_save(0, $_fields)) {
						api_log_log("Save error in api_data_source_item_save()", SEV_ERROR);
					}
				}
			}

			return $data_source_id;
		}else{
			api_log_log("Save error in api_data_source_save()", SEV_ERROR, FACIL_WEBUI);

			return false;
		}
	}

	return false;
}

/* api_data_template_push - pushes out templated data template fields to all matching child data sources
   @arg $data_template_id - the id of the data template to push out values for */
function api_data_template_propagate($data_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* get information about this data template */
	$data_template = get_data_template($data_template_id);

	/* must be a valid data template */
	if ($data_template === false) {
		return false;
	}

	/* retrieve a list of data source fields */
	$data_source_fields = api_data_source_fields_list();

	$ds_fields = array();
	/* loop through each data source column name (from the above array) */
	foreach ($data_source_fields as $field_name => $field_array) {
		/* are we allowed to push out the column? */
		if ((isset($data_template["t_$field_name"])) && (isset($data_template[$field_name])) && ($data_template["t_$field_name"] == "0")) {
			$ds_fields[$field_name] = array("type" => $field_array["data_type"], "value" => $data_template[$field_name]);
		}
	}

	if (sizeof($ds_fields) > 0) {
		$ds_fields["data_template_id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_template_id);

		return db_update("data_source", $ds_fields, array("data_template_id"));
	}

	return true;
}

/* api_data_source_item_propagate - pushes out templated data template item fields to all matching
	child data source items
   @arg $data_template_item_id - the id of the data template item to push out values for */
function api_data_source_item_propagate($data_template_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* get information about this data template */
	$data_template_item = get_data_template_item($data_template_item_id);

	/* must be a valid data template item */
	if ($data_template_item === false) {
		return false;
	}

	/* retrieve a list of data source item fields */
	$data_source_item_fields = api_data_source_item_fields_list();

	$dsi_fields = array();
	/* loop through each data source column name (from the above array) */
	foreach ($data_source_item_fields as $field_name => $field_array) {
		/* are we allowed to push out the column? */
		if ((isset($data_template_item["t_$field_name"])) && (isset($data_template_item[$field_name])) && ($data_template_item["t_$field_name"] == "0")) {
			$dsi_fields[$field_name] = array("type" => $field_array["data_type"], "value" => $data_template_item[$field_name]);
		}
	}

	if (sizeof($dsi_fields) > 0) {
		$dsi_fields["data_template_item_id"] = array("type" => DB_TYPE_STRING, "value" => $data_template_item["id"]);

		return db_update("data_source_item", $dsi_fields, array("data_template_item_id"));
	}

	return true;
}

?>
