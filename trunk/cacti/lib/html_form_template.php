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

function template_form_header_precheck($num_draw_fields, $left_title, $right_title = "") {
	global $colors;

	if (($num_draw_fields == 0) && ($left_title != "")) {
		echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td style='font-size: 10px; color: white;'>$left_title</td><td style='font-size: 10px; color: white;' align='right'>$right_title</td></tr>\n";
	}

	return ++$num_draw_fields;
}

/* draw_nontemplated_fields_graph - draws a form that consists of all non-templated graph fields associated
     with a particular graph template
   @arg $graph_template_id - the id of the graph template to base the form after
   @arg $values_array - any values that should be included by default on the form
   @arg $field_name_format - all fields on the form will be named using the following format, the following
     variables can be used:
       |field| - the current field name
   @arg $header_title - the title to use on the header for this form
   @arg $alternate_colors (bool) - whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_graph($graph_template_id, &$values_array, $field_name_format = "|field|", $header_title = "", $alternate_colors = true) {
	global $colors;

	include(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	if (empty($graph_template_id)) {
		return;
	}

	$form_array = array();

	/* fetch information about the graph template */
	$graph_template = db_fetch_row("select * from graph_template where id = $graph_template_id");

	while (list($field_name, $field_array) = each($struct_graph)) {
		if ((isset($graph_template{"t_" . $field_name}) ? $graph_template{"t_" . $field_name} : "0") == "1") {
			/* find our field name */
			$form_field_name = str_replace("|field|", $field_name, $field_name_format);

			$form_array += array($form_field_name => $struct_graph[$field_name]);

			/* modifications to the default form array */
			$form_array[$form_field_name]["value"] = (isset($values_array[$field_name]) ? $values_array[$field_name] : "");
			$form_array[$form_field_name]["form_id"] = (isset($values_array["id"]) ? $values_array["id"] : "0");
			unset($form_array[$form_field_name]["default"]);
		}
	}

	if ((sizeof($form_array) > 0) && ($header_title != "")) {
		echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='2' style='font-size: 10px; color: white;'>$header_title</td></tr>\n";
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	draw_edit_form(
		array(
			"config" => $form_config_array,
			"fields" => $form_array
			)
		);

	return sizeof($form_array);
}

/* draw_nontemplated_fields_graph_item - draws a form that consists of all non-templated graph item fields
     associated with a particular graph template
   @arg $values_array - any values that should be included by default on the form
   @arg $graph_template_id - the id of the graph template to base the form after
   @arg $field_name_format - all fields on the form will be named using the following format, the following
     variables can be used:
       |field| - the current field name
       |id| - the current graph input id
   @arg $header_title - the title to use on the header for this form
   @arg $alternate_colors (bool) - whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_graph_item($graph_template_id, &$values_array, $field_name_format = "|field|_|id|", $header_title = "", $alternate_colors = true) {
	global $colors;

	include(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	if (empty($graph_template_id)) {
		return;
	}

	$form_array = array();

	/* fetch a list of graph item inputs for this graph template */
	$graph_template_item_inputs = db_fetch_assoc("select * from graph_template_item_input where graph_template_id = $graph_template_id order by field_name,name");

	if (sizeof($graph_template_item_inputs) > 0) {
		foreach ($graph_template_item_inputs as $item) {
			/* grab the first graph template item referenced by this graph item input */
			$first_graph_template_item = db_fetch_row("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = " . $item["id"]);

			if (sizeof($first_graph_template_item) > 0) {
				/* get a complete list of graph template items for this graph template */
				$ordered_graph_items_list = array_rekey(db_fetch_assoc("select id from graph_template_item where graph_template_id = $graph_template_id order by sequence"), "", "id");

				/* find the sequential index for the graph template item that we want to retrieve a value for */
				$graph_item_index = array_search($first_graph_template_item["graph_template_item_id"], $ordered_graph_items_list);

				if ($graph_item_index !== false) {
					/* find our field name */
					$form_field_name = str_replace("|field|", $item["field_name"], $field_name_format);
					$form_field_name = str_replace("|id|", $item["id"], $form_field_name);

					$form_array += array($form_field_name => $struct_graph_item{$item["field_name"]});

					/* modifications to the default form array */
					$form_array[$form_field_name]["friendly_name"] = $item["name"];
					$form_array[$form_field_name]["value"] = $values_array[$graph_item_index]{$item["field_name"]};
				}
			}
		}
	}

	if ((sizeof($form_array) > 0) && ($header_title != "")) {
		echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='2' style='font-size: 10px; color: white;'>$header_title</td></tr>\n";
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	draw_edit_form(
		array(
			"config" => $form_config_array,
			"fields" => $form_array
			)
		);

	return sizeof($form_array);
}

/* draw_nontemplated_fields_data_source - draws a form that consists of all non-templated data source fields
     associated with a particular data template
   @arg $data_template_id - the id of the data template to base the form after
   @arg $values_array - any values that should be included by default on the form
   @arg $field_name_format - all fields on the form will be named using the following format, the following
     variables can be used:
       |field| - the current field name
   @arg $header_title - the title to use on the header for this form
   @arg $alternate_colors (bool) - whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_data_source($data_template_id, &$values_array, $field_name_format = "|field|", $display_template_name = true) {
	global $colors;

	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

	if (empty($data_template_id)) {
		return;
	}

	$num_draw_fields = 0;

	/* fetch information about the data template */
	$data_template = db_fetch_row("select * from data_template where id = $data_template_id");

	if ($data_template["t_name"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>Data Source</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
		_data_source_field__name(str_replace("|field|", "name", $field_name_format), false, 0);
	}

	if ($data_template["t_rrd_step"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>Data Source</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
		_data_source_field__rrd_step(str_replace("|field|", "rrd_step", $field_name_format), false, $values_array["rrd_step"], 0);
	}

	if ($data_template["t_active"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>Data Source</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
		_data_source_field__active(str_replace("|field|", "active", $field_name_format), false, $values_array["active"], 0);
	}

	return $num_draw_fields;
}

/* draw_nontemplated_fields_data_source_item - draws a form that consists of all non-templated data source
     item fields associated with a particular data template
   @arg $data_template_id - the id of the data template to base the form after
   @arg $values_array - any values that should be included by default on the form
   @arg $field_name_format - all fields on the form will be named using the following format, the following
     variables can be used:
       |field| - the current field name
       |name| - the data source item name
       |id| - the id of the current data source item
   @arg $header_title - the title to use on the header for this form
   @arg $draw_title_for_each_item (bool) - should a separate header be drawn for each data source item, or
     should all data source items be drawn under one header?
   @arg $alternate_colors (bool) - whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_data_source_item($data_template_id, &$values_array, $field_name_format = "|field_id|", $display_template_name = true) {
	global $colors;

	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

	if (empty($data_template_id)) {
		return;
	}

	$num_draw_fields = 0;

	if ($display_template_name == true) {
		$data_template_name = db_fetch_cell("select template_name from data_template where id = $data_template_id");
	}

	if (sizeof($values_array) > 0) {
		foreach ($values_array as $item) {
			$field_name = str_replace("|id|", $item["id"], $field_name_format);
			$num_draw_item_fields = 0;

			$data_template_item = db_fetch_row("select * from data_template_item where data_template_id = $data_template_id and data_source_name = '" . $item["data_source_name"] . "'");

			if ($data_template_item["t_rrd_minimum"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>Data Source Item</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__rrd_minimum(str_replace("|field|", "rrd_minimum", $field_name), false, $item["rrd_minimum"], $field_id = 0);
			}

			if ($data_template_item["t_rrd_maximum"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>Data Source Item</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__rrd_maximum(str_replace("|field|", "rrd_maximum", $field_name), false, $item["rrd_maximum"], $field_id = 0);
			}

			if ($data_template_item["t_data_source_type"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>Data Source Item</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__data_source_type(str_replace("|field|", "data_source_type", $field_name), false, $item["data_source_type"], 0);
			}

			if ($data_template_item["t_rrd_heartbeat"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>Data Source Item</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__rrd_heartbeat(str_replace("|field|", "rrd_heartbeat", $field_name), false, $item["rrd_heartbeat"], 0);
			}

			/* keep a global field draw count */
			$num_draw_fields += $num_draw_item_fields;
		}
	}

	return $num_draw_fields;
}

/* draw_nontemplated_fields_data_input - draws a form that consists of all non-templated data input
     item fields associated with a particular data template
   @arg $data_template_id - the id of the data template to base the form after
   @arg $values_array - any values that should be included by default on the form
   @arg $field_name_format - all fields on the form will be named using the following format, the following
     variables can be used:
       |field| - the current field name
   @arg $header_title - the title to use on the header for this form
   @arg $alternate_colors (bool) - whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_data_input($data_template_id, &$values_array, $field_name_format = "|field_id|", $header_title = "", $alternate_colors = true) {
	global $colors, $fields_host_edit;

	if (empty($data_template_id)) {
		return;
	}

	$form_array = array();

	$data_input_type = db_fetch_cell("select data_input_type from data_template where id = $data_template_id");
	$data_template_fields = array_rekey(db_fetch_assoc("select name,t_value,value from data_template_field where data_template_id = $data_template_id"), "name", array("t_value", "value"));

	while (list($field_name, $field_array) = each($values_array)) {
		/* find our field name */
		$form_field_name = str_replace("|field|", $field_name, $field_name_format);

		if ( ((isset($data_template_fields[$field_name])) && ($data_template_fields[$field_name]["t_value"] == "1"))
			&& (($field_name != "script_id") && ($field_name != "data_query_id")) ) {
			if ($data_input_type == "snmp") {
				$form_array += array($form_field_name => $fields_host_edit[$field_name]);
			}else if ($data_input_type == "script") {
				$form_array += array(
					$form_field_name => array(
						"method" => "textbox",
						"friendly_name" => (isset($values_array["script_id"]) ? db_fetch_cell("select name from data_input_fields where data_input_id = " . $values_array["script_id"]["value"] . " and data_name = '$field_name' and input_output = 'in'") : $field_name),
						"max_length" => "255",
						)
					);
			}else if ($data_input_type == "data_query") {

			}

			/* modifications to the default form array */
			$form_array[$form_field_name]["value"] = $field_array["value"];
			//$form_array[$form_field_name]["form_id"] = (isset($item["id"]) ? $item["id"] : "0");
			unset($form_array[$form_field_name]["default"]);
		}
	}

	if (sizeof($form_array) > 0) {
		if ($header_title != "") {
			echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='2' style='font-size: 10px; color: white;'>$header_title</td></tr>\n";
		}else{
			echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='2' style='font-size: 10px; color: white;'>Data Input</td></tr>\n";
		}
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	draw_edit_form(
		array(
			"config" => $form_config_array,
			"fields" => $form_array
			)
		);

	return sizeof($form_array);
}

?>
