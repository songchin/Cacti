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

function api_graph_template_save($id, $template_name, $suggested_values, $t_image_format, $image_format, $t_title, $t_height,
	$height, $t_width, $width, $t_x_grid, $x_grid, $t_y_grid, $y_grid, $t_y_grid_alt, $y_grid_alt, $t_no_minor,
	$no_minor, $t_upper_limit, $upper_limit, $t_lower_limit, $lower_limit, $t_vertical_label, $vertical_label,
	$t_auto_scale, $auto_scale, $t_auto_scale_opts, $auto_scale_opts, $t_auto_scale_log, $auto_scale_log,
	$t_auto_scale_rigid, $auto_scale_rigid, $t_auto_padding, $auto_padding, $t_base_value, $base_value, $t_export,
	$export, $t_unit_value, $unit_value, $t_unit_length, $unit_length, $t_unit_exponent_value, $unit_exponent_value,
	$t_force_rules_legend, $force_rules_legend) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$save["id"] = $id;
	$save["hash"] = get_hash_graph_template($id);
	$save["template_name"] = form_input_validate($template_name, "template_name", "", false, 3);
	$save["t_image_format"] = form_input_validate(html_boolean($t_image_format), "t_image_format", "", true, 3);
	$save["image_format"] = form_input_validate($image_format, "image_format_", "", true, 3);
	$save["t_title"] = form_input_validate(html_boolean($t_title), "t_title", "", true, 3);
	//$save["title"] = form_input_validate($title, "title", "", false, 3);
	$save["t_height"] = form_input_validate(html_boolean($t_height), "t_height", "", true, 3);
	$save["height"] = form_input_validate($height, "height", "^[0-9]+$", false, 3);
	$save["t_width"] = form_input_validate(html_boolean($t_width), "t_width", "", true, 3);
	$save["width"] = form_input_validate($width, "width", "^[0-9]+$", false, 3);
	$save["t_x_grid"] = form_input_validate(html_boolean($t_x_grid), "t_x_grid", "", true, 3);
	$save["x_grid"] = form_input_validate($x_grid, "x_grid", "", true, 3);
	$save["t_y_grid"] = form_input_validate(html_boolean($t_y_grid), "t_y_grid", "", true, 3);
	$save["y_grid"] = form_input_validate($y_grid, "y_grid", "", true, 3);
	$save["t_y_grid_alt"] = form_input_validate(html_boolean($t_y_grid_alt), "t_y_grid_alt", "", true, 3);
	$save["y_grid_alt"] = form_input_validate(html_boolean($y_grid_alt), "y_grid_alt", "", true, 3);
	$save["t_no_minor"] = form_input_validate(html_boolean($t_no_minor), "t_no_minor", "", true, 3);
	$save["no_minor"] = form_input_validate(html_boolean($no_minor), "no_minor", "", true, 3);
	$save["t_upper_limit"] = form_input_validate(html_boolean($t_upper_limit), "t_upper_limit", "", true, 3);
	$save["upper_limit"] = form_input_validate($upper_limit, "upper_limit", "^-?[0-9]+$", false, 3);
	$save["t_lower_limit"] = form_input_validate(html_boolean($t_lower_limit), "t_lower_limit", "", true, 3);
	$save["lower_limit"] = form_input_validate($lower_limit, "lower_limit", "^-?[0-9]+$", false, 3);
	$save["t_vertical_label"] = form_input_validate(html_boolean($t_vertical_label), "t_vertical_label", "", true, 3);
	$save["vertical_label"] = form_input_validate($vertical_label, "vertical_label", "", true, 3);
	$save["t_auto_scale"] = form_input_validate(html_boolean($t_auto_scale), "t_auto_scale", "", true, 3);
	$save["auto_scale"] = form_input_validate(html_boolean($auto_scale), "auto_scale", "", true, 3);
	$save["t_auto_scale_opts"] = form_input_validate(html_boolean($t_auto_scale_opts), "t_auto_scale_opts", "", true, 3);
	$save["auto_scale_opts"] = form_input_validate($auto_scale_opts, "auto_scale_opts", "", true, 3);
	$save["t_auto_scale_log"] = form_input_validate(html_boolean($t_auto_scale_log), "t_auto_scale_log", "", true, 3);
	$save["auto_scale_log"] = form_input_validate(html_boolean($auto_scale_log), "auto_scale_log", "", true, 3);
	$save["t_auto_scale_rigid"] = form_input_validate(html_boolean($t_auto_scale_rigid), "t_auto_scale_rigid", "", true, 3);
	$save["auto_scale_rigid"] = form_input_validate(html_boolean($auto_scale_rigid), "auto_scale_rigid", "", true, 3);
	$save["t_auto_padding"] = form_input_validate(html_boolean($t_auto_padding), "t_auto_padding", "", true, 3);
	$save["auto_padding"] = form_input_validate(html_boolean($auto_padding), "auto_padding", "", true, 3);
	$save["t_base_value"] = form_input_validate(html_boolean($t_base_value), "t_base_value", "", true, 3);
	$save["base_value"] = form_input_validate($base_value, "base_value", "", false, 3);
	$save["t_export"] = form_input_validate(html_boolean($t_export), "t_export", "", true, 3);
	$save["export"] = form_input_validate(html_boolean($export), "export", "", true, 3);
	$save["t_unit_value"] = form_input_validate(html_boolean($t_unit_value), "t_unit_value", "", true, 3);
	$save["unit_value"] = form_input_validate($unit_value, "unit_value", "", true, 3);
	$save["t_unit_length"] = form_input_validate(html_boolean($t_unit_length), "t_unit_length", "", true, 3);
	$save["unit_length"] = form_input_validate($unit_length, "unit_length", "^[0-9]+$", true, 3);
	$save["t_unit_exponent_value"] = form_input_validate(html_boolean($t_unit_exponent_value), "t_unit_exponent_value", "", true, 3);
	$save["unit_exponent_value"] = form_input_validate((($unit_exponent_value == "none") ? "" : $unit_exponent_value), "unit_exponent_value", "", true, 3);
	$save["t_force_rules_legend"] = form_input_validate(html_boolean($t_force_rules_legend), "t_force_rules_legend", "", true, 3);
	$save["force_rules_legend"] = form_input_validate(html_boolean($force_rules_legend), "force_rules_legend", "", true, 3);

	$graph_template_id = 0;

	if (!is_error_message()) {
		$graph_template_id = sql_save($save, "graph_template");

		if ($graph_template_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	/* save all suggested value fields */
	while (list($field_name, $field_array) = each($suggested_values)) {
		while (list($id, $value) = each($field_array)) {
			form_input_validate($value, "sv|$field_name|$id", "", false, 3);

			if ((!is_error_message()) && ($graph_template_id)) {
				if (empty($id)) {
					db_execute("insert into graph_template_suggested_value (hash,graph_template_id,field_name,value,sequence) values ('',$graph_template_id,'$field_name','$value'," . seq_get_current(0, "sequence", "graph_template_suggested_value", "graph_template_id = $graph_template_id and field_name = '$field_name'") . ")");
				}else{
					db_execute("update graph_template_suggested_value set value = '$value' where id = $id");
				}
			}
		}
	}

	if ((!is_error_message()) && (!empty($graph_template_id))) {
		/* push out graph template fields */
		api_graph_template_propagate($graph_template_id);
	}

	return $graph_template_id;
}

function api_graph_template_remove($graph_template_id) {
	if ((empty($graph_template_id)) || (!is_numeric($graph_template_id))) {
		return;
	}

	/* delete all graph template items */
	$graph_template_items = db_fetch_assoc("select id from graph_template_item where graph_template_id = $graph_template_id");

	if (sizeof($graph_template_items) > 0) {
		foreach ($graph_template_items as $item) {
			api_graph_template_item_remove($item["id"], false);
		}
	}

	db_execute("delete from graph_template_suggested_value where graph_template_id = $graph_template_id");
	db_execute("delete from graph_template_item_input where graph_template_id = $graph_template_id");
	db_execute("delete from graph_template_item where graph_template_id = $graph_template_id");
	db_execute("delete from graph_template where id = $graph_template_id");

	/* host templates */
	db_execute("delete from host_template_graph where graph_template_id = $graph_template_id");

	/* attached graphs */
	db_execute("update graph set graph_template_id = 0 where graph_template_id = $graph_template_id");
}

function api_graph_template_item_save($id, $graph_template_id, $data_template_item_id, $color, $graph_item_type, $cdef,
	$consolidation_function, $gprint_format, $legend_format, $legend_value, $hard_return) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$save["id"] = $id;
	$save["hash"] = get_hash_graph_template($id, "graph_template_item");
	$save["graph_template_id"] = $graph_template_id;
	$save["sequence"] = seq_get_current($id, "sequence", "graph_template_item", "graph_template_id = $graph_template_id");
	$save["data_template_item_id"] = form_input_validate($data_template_item_id, "data_template_item_id", "", true, 3);
	$save["color"] = form_input_validate($color, "color", "^[a-fA-F0-9]{6}$", true, 3);
	$save["graph_item_type"] = form_input_validate($graph_item_type, "graph_item_type", "", true, 3);
	$save["cdef"] = form_input_validate($cdef, "cdef", "", true, 3);
	$save["consolidation_function"] = form_input_validate($consolidation_function, "consolidation_function", "", true, 3);
	$save["gprint_format"] = form_input_validate($gprint_format, "gprint_format", "", (($graph_item_type == GRAPH_ITEM_TYPE_GPRINT) ? false : true), 3);
	$save["legend_format"] = form_input_validate($legend_format, "legend_format", "", true, 3);
	$save["legend_value"] = form_input_validate($legend_value, "legend_value", "", true, 3);
	$save["hard_return"] = form_input_validate(html_boolean($hard_return), "hard_return", "", true, 3);

	$graph_template_item_id = 0;

	if (!is_error_message()) {
		$graph_template_item_id = sql_save($save, "graph_template_item");

		if ($graph_template_item_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $graph_template_item_id;

	//push_out_graph_item($graph_template_item_id);
}

function api_graph_template_item_remove($graph_template_item_id, $delete_attached = true) {
	if ((empty($graph_template_item_id)) || (!is_numeric($graph_template_item_id))) {
		return;
	}

	db_execute("delete from graph_template_item where id = $graph_template_item_id");
	db_execute("delete from graph_template_item_input_item where graph_template_item_id = $graph_template_item_id");

	/* attached graph items */
	if ($delete_attached == true) {
		db_execute("delete from graph_item where graph_template_item_id = $graph_template_item_id");
	}else{
		db_execute("update graph_item set graph_template_item_id = 0 where graph_template_item_id = $graph_template_item_id");
	}
}

function api_graph_template_item_movedown($graph_template_item_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_template_id = db_fetch_cell("select graph_template_id from graph_template_item where id = $graph_template_item_id");

	$next_item = seq_get_item("graph_template_item", "sequence", $graph_template_item_id, "graph_template_id = $graph_template_id", "next");

	seq_move_item("graph_template_item", $graph_template_item_id, "graph_template_id = $graph_template_id", "down");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $graph_template_item_id") . " where graph_template_item_id = $graph_template_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $next_item") . " where graph_template_item_id = $next_item");
}

function api_graph_template_item_moveup($graph_template_item_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_template_id = db_fetch_cell("select graph_template_id from graph_template_item where id = $graph_template_item_id");

	$last_item = seq_get_item("graph_template_item", "sequence", $graph_template_item_id, "graph_template_id = $graph_template_id", "previous");

	seq_move_item("graph_template_item", $graph_template_item_id, "graph_template_id = $graph_template_id", "up");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $graph_template_item_id") . " where graph_template_item_id = $graph_template_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $last_item") . " where graph_template_item_id = $last_item");
}

function api_graph_template_item_row_movedown($row_num, $graph_template_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_template_item", "graph_template_id = $graph_template_id", true, "down");
}

function api_graph_template_item_row_moveup($row_num, $graph_template_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_template_item", "graph_template_id = $graph_template_id", true, "up");
}

function api_graph_template_item_duplicate($graph_template_item_id, $new_data_template_item_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$item = db_fetch_row("select * from graph_template_item where id = $graph_template_item_id");

	if (sizeof($item) > 0) {
		api_graph_template_item_save(0, $item["graph_template_id"], $new_data_template_item_id, $item["color"], $item["graph_item_type"], $item["cdef"],
			$item["consolidation_function"], $item["gprint_format"], $item["legend_format"], $item["legend_value"], $item["hard_return"]);
	}
}

function api_graph_template_item_input_save($id, $items_array, $graph_template_id, $field_name, $name) {
	$save["id"] = $id;
	$save["hash"] = get_hash_graph_template($id, "graph_template_input");
	$save["graph_template_id"] = $graph_template_id;
	$save["name"] = form_input_validate($name, "name", "", false, 3);
	$save["field_name"] = form_input_validate($field_name, "field_name", "", true, 3);

	$graph_template_item_input_id = 0;

	if (!is_error_message()) {
		$graph_template_item_input_id = sql_save($save, "graph_template_item_input");

		if ($graph_template_item_input_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	if ((!is_error_message()) && (!empty($graph_template_item_input_id))) {
		/* list all graph items from the db so we can compare them with the current form */
		$selected_graph_items = db_fetch_assoc("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");

		$db_selected_graph_item = array();

		if (sizeof($selected_graph_items) > 0) {
			foreach ($selected_graph_items as $item) {
				$db_selected_graph_item[] = $item["graph_template_item_id"];
			}
		}

		db_execute("delete from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");

		$old_members = array();
		$new_members = array();

		/* list all graph items that have been selected on the form */
		for ($i=0; $i<sizeof($items_array); $i++) {
			db_execute("insert into graph_template_item_input_item (graph_template_item_input_id,graph_template_item_id) values ($graph_template_item_input_id," . $items_array[$i] . ")");

			if (in_array($items_array[$i], $db_selected_graph_item)) {
				/* is selected and exists in the db; old item */
				$old_members[] = $items_array[$i];
			}else{
				/* is selected and does not exist the db; new item */
				$new_members[] = $items_array[$i];
			}
		}

		for ($i=0; $i<sizeof($old_members); $i++) {
			//push_out_graph_input($graph_template_item_input_id, $old_members[$i], $new_members);
		}
	}

	return $graph_template_item_input_id;
}

function api_graph_template_item_input_remove($graph_template_item_input_id) {
	if ((empty($graph_template_item_input_id)) || (!is_numeric($graph_template_item_input_id))) {
		return;
	}

	db_execute("delete from graph_template_item_input where id = $graph_template_item_input_id");
	db_execute("delete from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");
}

?>
