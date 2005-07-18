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
function draw_nontemplated_fields_graph($graph_template_id, &$values_array, $field_name_format = "|field|", $display_template_name = true) {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");

	if (empty($graph_template_id)) {
		return;
	}

	$num_draw_fields = 0;

	/* fetch information about the graph template */
	$graph_template = db_fetch_row("select * from graph_template where id = $graph_template_id");

	if ($graph_template["t_title"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__title(str_replace("|field|", "title", $field_name_format), false, 0);
	}

	if ($graph_template["t_vertical_label"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__vertical_label(str_replace("|field|", "vertical_label", $field_name_format), false, $values_array["vertical_label"], 0);
	}

	if ($graph_template["t_image_format"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__image_format(str_replace("|field|", "image_format", $field_name_format), false, $values_array["image_format"], 0);
	}

	if ($graph_template["t_vertical_label"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__vertical_label(str_replace("|field|", "vertical_label", $field_name_format), false, $values_array["vertical_label"], 0);
	}

	if ($graph_template["t_export"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__export(str_replace("|field|", "export", $field_name_format), false, $values_array["export"], 0);
	}

	if ($graph_template["t_force_rules_legend"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__force_rules_legend(str_replace("|field|", "force_rules_legend", $field_name_format), false, $values_array["force_rules_legend"], 0);
	}

	if ($graph_template["t_vertical_label"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__vertical_label(str_replace("|field|", "vertical_label", $field_name_format), false, $values_array["vertical_label"], 0);
	}

	if ($graph_template["t_height"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__height(str_replace("|field|", "height", $field_name_format), false, $values_array["height"], 0);
	}

	if ($graph_template["t_vertical_label"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__vertical_label(str_replace("|field|", "vertical_label", $field_name_format), false, $values_array["vertical_label"], 0);
	}

	if ($graph_template["t_width"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__width(str_replace("|field|", "width", $field_name_format), false, $values_array["width"], 0);
	}

	if ($graph_template["t_x_grid"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__x_grid(str_replace("|field|", "x_grid", $field_name_format), false, $values_array["x_grid"], 0);
	}

	if ($graph_template["t_y_grid"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__y_grid(str_replace("|field|", "y_grid", $field_name_format), false, $values_array["y_grid"], 0);
	}

	if ($graph_template["t_y_grid_alt"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__y_grid_alt(str_replace("|field|", "y_grid_alt", $field_name_format), false, $values_array["y_grid_alt"], 0);
	}

	if ($graph_template["t_no_minor"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__no_minor(str_replace("|field|", "no_minor", $field_name_format), false, $values_array["no_minor"], 0);
	}

	if ($graph_template["t_auto_scale"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__auto_scale(str_replace("|field|", "auto_scale", $field_name_format), false, $values_array["auto_scale"], 0);
	}

	if ($graph_template["t_auto_scale_opts"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__auto_scale_opts(str_replace("|field|", "auto_scale_opts", $field_name_format), false, $values_array["auto_scale_opts"], 0);
	}

	if ($graph_template["t_auto_scale_log"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__auto_scale_log(str_replace("|field|", "auto_scale_log", $field_name_format), false, $values_array["auto_scale_log"], 0);
	}

	if ($graph_template["t_auto_scale_rigid"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__auto_scale_rigid(str_replace("|field|", "auto_scale_rigid", $field_name_format), false, $values_array["auto_scale_rigid"], 0);
	}

	if ($graph_template["t_auto_padding"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__auto_padding(str_replace("|field|", "auto_padding", $field_name_format), false, $values_array["auto_padding"], 0);
	}

	if ($graph_template["t_upper_limit"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__upper_limit(str_replace("|field|", "upper_limit", $field_name_format), false, $values_array["upper_limit"], 0);
	}

	if ($graph_template["t_lower_limit"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__lower_limit(str_replace("|field|", "lower_limit", $field_name_format), false, $values_array["lower_limit"], 0);
	}

	if ($graph_template["t_base_value"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__base_value(str_replace("|field|", "base_value", $field_name_format), false, $values_array["base_value"], 0);
	}

	if ($graph_template["t_unit_value"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__unit_value(str_replace("|field|", "unit_value", $field_name_format), false, $values_array["unit_value"], 0);
	}

	if ($graph_template["t_unit_length"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__unit_length(str_replace("|field|", "unit_length", $field_name_format), false, $values_array["unit_length"], 0);
	}

	if ($graph_template["t_unit_exponent_value"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
		_graph_field__unit_exponent_value(str_replace("|field|", "unit_exponent_value", $field_name_format), false, $values_array["unit_exponent_value"], 0);
	}

	return $num_draw_fields;
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
function draw_nontemplated_fields_graph_item($graph_template_id, &$values_array, $field_name_format = "|field|_|id|", $display_template_name = true) {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");

	if (empty($graph_template_id)) {
		return;
	}

	$num_draw_fields = 0;

	/* fetch information about the graph template (for the template name) */
	$graph_template = db_fetch_row("select * from graph_template where id = $graph_template_id");

	/* fetch a list of graph item inputs for this graph template */
	$graph_template_item_inputs = db_fetch_assoc("select * from graph_template_item_input where graph_template_id = $graph_template_id order by field_name,name");

	if (sizeof($graph_template_item_inputs) > 0) {
		foreach ($graph_template_item_inputs as $item) {
			/* substitute the graph item input id in the field name */
			$_field_name_format = str_replace("|id|", $item["id"], $field_name_format);

			/* grab the first graph template item referenced by this graph item input */
			$first_graph_template_item_id = db_fetch_cell("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = " . $item["id"] . " limit 1");

			if ((!empty($first_graph_template_item_id)) && (isset($values_array[$first_graph_template_item_id]))) {
				/* find our field name */
				$form_field_name = str_replace("|field|", $item["field_name"], $field_name_format);
				$form_field_name = str_replace("|id|", $item["id"], $form_field_name);

				if ($item["field_name"] == "color") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__color(str_replace("|field|", "color", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "graph_item_type") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__graph_item_type(str_replace("|field|", "graph_item_type", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "consolidation_function") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__consolidation_function(str_replace("|field|", "consolidation_function", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "cdef") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__cdef(str_replace("|field|", "cdef", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "gprint_format") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__gprint_format(str_replace("|field|", "gprint_format", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "legend_value") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__legend_value(str_replace("|field|", "legend_value", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "legend_format") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__legend_format(str_replace("|field|", "legend_format", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}

				if ($item["field_name"] == "hard_return") {
					$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Graph Item") . "</strong>", ($display_template_name == true ? $graph_template["template_name"] : ""));
					_graph_item_field__hard_return(str_replace("|field|", "hard_return", $_field_name_format), $values_array[$first_graph_template_item_id]{$item["field_name"]}, 1);
				}
			}
		}
	}

	return $num_draw_fields;
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

	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

	if (empty($data_template_id)) {
		return;
	}

	$num_draw_fields = 0;

	/* fetch information about the data template */
	$data_template = db_fetch_row("select * from data_template where id = $data_template_id");

	if ($data_template["t_name"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Data Source") . "</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
		_data_source_field__name(str_replace("|field|", "name", $field_name_format), false, 0);
	}

	if ($data_template["t_rrd_step"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Data Source") . "</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
		_data_source_field__rrd_step(str_replace("|field|", "rrd_step", $field_name_format), false, $values_array["rrd_step"], 0);
	}

	if ($data_template["t_active"] == "1") {
		$num_draw_fields = template_form_header_precheck($num_draw_fields, "<strong>" . _("Data Source") . "</strong>", ($display_template_name == true ? $data_template["template_name"] : ""));
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

	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");

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
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>" . _("Data Source Item") . "</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__rrd_minimum(str_replace("|field|", "rrd_minimum", $field_name), false, $item["rrd_minimum"], $field_id = 0);
			}

			if ($data_template_item["t_rrd_maximum"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>" . _("Data Source Item") . "</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__rrd_maximum(str_replace("|field|", "rrd_maximum", $field_name), false, $item["rrd_maximum"], $field_id = 0);
			}

			if ($data_template_item["t_data_source_type"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>" . _("Data Source Item") . "</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
				_data_source_item_field__data_source_type(str_replace("|field|", "data_source_type", $field_name), false, $item["data_source_type"], 0);
			}

			if ($data_template_item["t_rrd_heartbeat"] == "1") {
				$num_draw_item_fields = template_form_header_precheck($num_draw_item_fields, "<strong>" . _("Data Source Item") . "</strong> [" . $item["data_source_name"] . "]", ($display_template_name == true ? $data_template_name : ""));
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
			echo "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='2' style='font-size: 10px; color: white;'>" . _("Data Input") . "</td></tr>\n";
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
