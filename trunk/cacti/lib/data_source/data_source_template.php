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

/* set_data_template - changes the data template for a particular data source to
     $data_template_id
   @arg $data_source_id - the id of the data source to change the data template for, '0'
     means that the data source does not exist in which case a new data source will be
     created from the data template
   @arg $data_template_id - id the of the data template to change to. specify '0' for no
     data template
   @arg $host_id - the id of the device attached to this data source
   @arg $form_data_source_fields - data source field data from the active form which will be used
     override any any data source fields in the database.
   @arg $form_data_source_item_fields - data source item field data from the active form which will
     be used override any any data source item fields in the database.
   @arg $form_data_input_fields - data input field data from the active form which will be
     used override any any data input fields in the database.
   @returns - the id of the data source created from this template */
function set_data_template($data_source_id, $data_template_id, $host_id, &$form_data_source_fields, &$form_data_source_item_fields, &$form_data_input_fields) {
	global $struct_data_source, $struct_data_source_item;

	include_once(CACTI_BASE_PATH . "/lib/poller.php");
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");

	if (empty($data_template_id)) {
		if (!empty($data_source_id)) {
			db_execute("update data_source set data_template_id = 0 where id = $data_source_id");
		}

		return;
	}

	/* get data about the template and the data source */
	$data_source = array_merge((empty($data_source_id) ? array() : db_fetch_row("select * from data_source where id = $data_source_id")), $form_data_source_fields);
	$data_template = db_fetch_row("select * from data_template where id = $data_template_id");

	/* make sure to copy down field data as well */
	$data_template_fields = array_rekey(db_fetch_assoc("select name,t_value,value from data_template_field where data_template_id = $data_template_id"), "name", array("t_value", "value"));
	$data_source_fields = array_merge_recursive_replace((empty($data_source_id) ? array() : array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = $data_source_id"), "name", "value")), $form_data_input_fields);

	/* remove all orphaned field data */
	db_execute("delete from data_source_field where data_source_id = $data_source_id");

	$data_input_fields = array();

	/* handle input field data -- fields that exist in data template, but not data source */
	reset($data_template_fields);
	while (list($name, $value) = each($data_template_fields)) {
		if ((isset($data_source_fields[$name])) && (!empty($data_template_fields[$name]["t_value"]))) {
			$data_input_fields[$name] = $data_source_fields[$name]["value"];
		}else{
			$data_input_fields[$name] = $data_template_fields[$name]["value"];
		}
	}

	/* user form variables *always* take precidence */
	$data_input_fields = array_merge_recursive_replace($form_data_input_fields, $data_input_fields);

	/* make sure to update the 'data_source_rra' table for each data source */
	$data_template_rra = db_fetch_assoc("select rra_id from data_template_rra where data_template_id = $data_template_id");

	$rra_id = array();

	if (sizeof($data_template_rra) > 0) {
		foreach ($data_template_rra as $item) {
			array_push($rra_id, $item["rra_id"]);
		}
	}

	/* select an appropriate data source title using suggested values */
	if ((isset($data_input_fields["data_query_id"])) && (isset($data_input_fields["data_query_index"])) && ((empty($data_template["t_name"])) || (empty($data_source_id)))) {
		$data_template["name"] = evaluate_data_query_suggested_values($host_id, $data_input_fields["data_query_id"], $data_input_fields["data_query_index"], "data_template_suggested_value", "data_template_id = $data_template_id and field_name = 'name'", 0);
	}else if ((empty($data_template["t_name"])) || (empty($data_source_id))) {
		/* evaluation of host/other variables might go here */
		$data_template["name"] = db_fetch_cell("select value from data_template_suggested_value where data_template_id = $data_template_id and field_name = 'name' order by sequence limit 1");
	}

	$data_source_id = api_data_source_save(
		$data_source_id,
		$host_id,
		$data_template_id,
		(empty($data_template_id) ? $data_source["data_input_type"] : $data_template["data_input_type"]),
		$data_input_fields,
		((((empty($data_source_id)) || (!empty($data_template["t_name"]))) && (isset($data_source["name"]))) ? $data_source["name"] : $data_template["name"]),
		//(((empty($data_template["t_name"])) || (!isset($data_source["name"]))) ? $data_template["name"] : $data_source["name"]),
		(((empty($data_template["t_active"])) || (!isset($data_source["active"]))) ? $data_template["active"] : $data_source["active"]),
		(empty($data_source_id) ? "" : $data_source["rrd_path"]),
		(((empty($data_template["t_rrd_step"])) || (!isset($data_source["rrd_step"]))) ? $data_template["rrd_step"] : $data_source["rrd_step"]),
		$rra_id,
		"ds||field|",
		"dif_|field|");

	if ($data_source_id) {
		/* rekey $form_data_source_item_fields by data source item name */
		$_form_data_source_item_fields = array();

		reset($form_data_source_item_fields);
		while (list($data_source_item_id, $field_array) = each($form_data_source_item_fields)) {
			$data_source_name = db_fetch_cell("select data_source_name from data_source_item where id = $data_source_item_id");

			if ($data_source_name != "") {
				$_form_data_source_item_fields[$data_source_name] = $field_array;
			}
		}

		$data_source_items = array_merge_recursive_replace((empty($data_source_id) ? array() : array_rekey(db_fetch_assoc("select * from data_source_item where data_source_id = $data_source_id"), "data_source_name", array("id", "rrd_maximum", "rrd_minimum", "rrd_heartbeat", "data_source_type", "field_input_value"))), $_form_data_source_item_fields);
		$data_template_items = array_rekey(db_fetch_assoc("select * from data_template_item where data_template_id = $data_template_id"), "data_source_name", array("id", "t_rrd_maximum", "rrd_maximum", "t_rrd_minimum", "rrd_minimum", "t_rrd_heartbeat", "rrd_heartbeat", "t_data_source_type", "data_source_type", "field_input_value"));

		/* remove any data source items that do not match the template */
		while (list($name, $field_array) = each($data_source_items)) {
			if (!isset($data_template_items[$name])) {
				api_data_source_item_remove($field_array["id"]);
			}
		}

		while (list($name, $field_array) = each($data_template_items)) {
			api_data_source_item_save(
				(isset($data_source_items[$name]) ? $data_source_items[$name]["id"] : "0"),
				$data_source_id,
				(((empty($data_template_items[$name]["t_rrd_maximum"])) || (!isset($data_source_items[$name]))) ? $data_template_items[$name]["rrd_maximum"] : $data_source_items[$name]["rrd_maximum"]),
				(((empty($data_template_items[$name]["t_rrd_minimum"])) || (!isset($data_source_items[$name]))) ? $data_template_items[$name]["rrd_minimum"] : $data_source_items[$name]["rrd_minimum"]),
				(((empty($data_template_items[$name]["t_rrd_heartbeat"])) || (!isset($data_source_items[$name]))) ? $data_template_items[$name]["rrd_heartbeat"] : $data_source_items[$name]["rrd_heartbeat"]),
				(((empty($data_template_items[$name]["t_data_source_type"])) || (!isset($data_source_items[$name]))) ? $data_template_items[$name]["data_source_type"] : $data_source_items[$name]["data_source_type"]),
				$name,
				(((empty($data_template_items[$name]["t_field_input_value"])) || (!isset($data_source_items[$name]))) ? $data_template_items[$name]["field_input_value"] : $data_source_items[$name]["field_input_value"]),
				"dsi||field|||id|");
		}

		/* updating the poller cache needs to happen last; after all of the data source items have been added */
		update_poller_cache($data_source_id, false);
	}

	return $data_source_id;
}

/* api_data_template_push - pushes out templated data template fields to all matching child data sources
   @arg $data_template_id - the id of the data template to push out values for */
function api_data_template_propagate($data_template_id) {
	global $struct_data_source, $cnn_id;

	/* get information about this data template */
	$data_template = db_fetch_row("select * from data_template where id=$data_template_id");

	/* must be a valid data template */
	if (sizeof($data_template) == 0) { return 0; }

	/* get data sources list for ADODB */
	$data_sources = $cnn_id->Execute("select * from data_source where data_template_id = $data_template_id");

	/* loop through each data source column name (from the above array) */
	reset($struct_data_source);
	while (list($field_name, $field_array) = each($struct_data_source)) {
		/* are we allowed to push out the column? */
		if ((isset($data_template["t_$field_name"])) && (isset($data_template[$field_name])) && ($data_template["t_$field_name"] == "0")) {
			$ds_fields[$field_name] = $data_template[$field_name];
		}
	}

	if (isset($ds_fields["name"])) {
		//update_data_source_title_cache_from_template($data_template_data["data_template_id"]);
	}

	db_execute($cnn_id->GetUpdateSQL($data_sources, $ds_fields));
}

/* api_data_source_item_propagate - pushes out templated data template item fields to all matching
	child data source items
   @arg $data_template_item_id - the id of the data template item to push out values for */
function api_data_source_item_propagate($data_template_item_id) {
	global $struct_data_source_item, $cnn_id;

	/* get information about this data template */
	$data_template_item = db_fetch_row("select * from data_template_item where id=$data_template_item_id");

	/* must be a valid data template */
	if (sizeof($data_template_item) == 0) { return 0; }

	/* get data source items list for ADODB */
	$data_source_items = $cnn_id->Execute("select * from data_source_item where data_source_name = '" . db_fetch_cell("select data_source_name from data_template_item where id = $data_template_item_id") . "'");

	/* loop through each data source column name (from the above array) */
	reset($struct_data_source_item);
	while (list($field_name, $field_array) = each($struct_data_source_item)) {
		/* are we allowed to push out the column? */
		if ((isset($data_template_item["t_$field_name"])) && (isset($data_template_item[$field_name])) && ($data_template_item["t_$field_name"] == "0")) {
			$dsi_fields[$field_name] = $data_template_item[$field_name];
		}
	}

	db_execute($cnn_id->GetUpdateSQL($data_source_items, $dsi_fields));
}

?>
