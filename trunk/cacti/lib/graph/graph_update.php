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

function api_graph_save($id, $host_id, $graph_template_id, $image_format, $title, $height, $width, $x_grid,
	$y_grid, $y_grid_alt, $no_minor, $upper_limit, $lower_limit, $vertical_label, $auto_scale, $auto_scale_opts,
	$auto_scale_log, $auto_scale_rigid, $auto_padding, $base_value, $export, $unit_value, $unit_length,
	$unit_exponent_value, $force_rules_legend) {
	$save["id"] = $id;
	$save["host_id"] = $host_id;
	$save["graph_template_id"] = $graph_template_id;
	$save["image_format"] = form_input_validate($image_format, "image_format_", "", true, 3);
	$save["title"] = form_input_validate($title, "title", "", false, 3);
	$save["height"] = form_input_validate($height, "height", "^[0-9]+$", false, 3);
	$save["width"] = form_input_validate($width, "width", "^[0-9]+$", false, 3);
	$save["x_grid"] = form_input_validate($x_grid, "x_grid", "", true, 3);
	$save["y_grid"] = form_input_validate($y_grid, "y_grid", "", true, 3);
	$save["y_grid_alt"] = form_input_validate(html_boolean($y_grid_alt), "y_grid_alt", "", true, 3);
	$save["no_minor"] = form_input_validate(html_boolean($no_minor), "no_minor", "", true, 3);
	$save["upper_limit"] = form_input_validate($upper_limit, "upper_limit", "^-?[0-9]+$", false, 3);
	$save["lower_limit"] = form_input_validate($lower_limit, "lower_limit", "^-?[0-9]+$", false, 3);
	$save["vertical_label"] = form_input_validate($vertical_label, "vertical_label", "", true, 3);
	$save["auto_scale"] = form_input_validate(html_boolean($auto_scale), "auto_scale", "", true, 3);
	$save["auto_scale_opts"] = form_input_validate($auto_scale_opts, "auto_scale_opts", "", true, 3);
	$save["auto_scale_log"] = form_input_validate(html_boolean($auto_scale_log), "auto_scale_log", "", true, 3);
	$save["auto_scale_rigid"] = form_input_validate(html_boolean($auto_scale_rigid), "auto_scale_rigid", "", true, 3);
	$save["auto_padding"] = form_input_validate(html_boolean($auto_padding), "auto_padding", "", true, 3);
	$save["base_value"] = form_input_validate($base_value, "base_value", "", false, 3);
	$save["export"] = form_input_validate(html_boolean($export), "export", "", true, 3);
	$save["unit_value"] = form_input_validate($unit_value, "unit_value", "", true, 3);
	$save["unit_length"] = form_input_validate($unit_length, "unit_length", "^[0-9]+$", true, 3);
	$save["unit_exponent_value"] = form_input_validate((($unit_exponent_value == "none") ? "" : $unit_exponent_value), "unit_exponent_value", "", true, 3);
	$save["force_rules_legend"] = form_input_validate(html_boolean($force_rules_legend), "force_rules_legend", "", true, 3);

	$graph_id = 0;

	if (!is_error_message()) {
		$graph_id = sql_save($save, "graph");

		if ($graph_id) {
			update_graph_title_cache($graph_id);

			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $graph_id;
}

/* api_resize_graphs - resizes the selected graph, overriding the template value
   @arg $graph_templates_graph_id - the id of the graph to resize
   @arg $graph_width - the width of the resized graph
   @arg $graph_height - the height of the resized graph
  */
function api_resize_graphs($local_graph_id, $graph_width, $graph_height) {
	global $config;

	/* get graphs template id */
	db_execute("UPDATE graph SET width=" . $graph_width . ", height=" . $graph_height . " WHERE id=" . $local_graph_id);
}

function api_graph_remove($graph_id) {
	if ((empty($graph_id)) || (!is_numeric($graph_id))) {
		return;
	}

	db_execute("delete from graph_item where graph_id = $graph_id");
	db_execute("delete from graph_tree_items where local_graph_id = $graph_id");
	db_execute("delete from graph where id = $graph_id");
}

function api_graph_item_save($id, $graph_id, $data_source_item_id, $color, $graph_item_type, $cdef, $consolidation_function,
	$gprint_format, $legend_format, $legend_value, $hard_return) {
	include_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	if (empty($graph_id)) {
		return;
	}

	$graph_template_id = db_fetch_cell("select graph_template_id from graph where id = $graph_id");

	/* use the current sequence or generate a new one */
	$sequence = seq_get_current($id, "sequence", "graph_item", "graph_id = $graph_id");

	/* determine the parent graph_template_item if this graph is using a graph template */
	if (empty($graph_template_id)) {
		$graph_template_item_id = 0;
	}else{
		$graph_template_item_id = db_fetch_cell("select id from graph_template_item where graph_template_id = $graph_template_id order by sequence limit " . (seq_get_index("graph_item", $sequence, "graph_id = $graph_id") - 1) . ",1");
	}

	$save["id"] = $id;
	$save["graph_id"] = $graph_id;
	$save["graph_template_item_id"] = $graph_template_item_id;
	$save["sequence"] = $sequence;
	$save["data_source_item_id"] = form_input_validate($data_source_item_id, "data_source_item_id", "", true, 3);
	$save["color"] = form_input_validate($color, "color", "^[a-fA-F0-9]{6}$", true, 3);
	$save["graph_item_type"] = form_input_validate($graph_item_type, "graph_item_type", "", true, 3);
	$save["cdef"] = form_input_validate($cdef, "cdef", "", true, 3);
	$save["consolidation_function"] = form_input_validate($consolidation_function, "consolidation_function", "", true, 3);
	$save["gprint_format"] = form_input_validate($gprint_format, "gprint_format", "", (($graph_item_type == GRAPH_ITEM_TYPE_GPRINT) ? false : true), 3);
	$save["legend_format"] = form_input_validate($legend_format, "legend_format", "", true, 3);
	$save["legend_value"] = form_input_validate($legend_value, "legend_value", "", true, 3);
	$save["hard_return"] = form_input_validate(html_boolean($hard_return), "hard_return", "", true, 3);

	$graph_item_id = 0;

	if ((!is_error_message()) && (!empty($graph_id))) {
		$graph_item_id = sql_save($save, "graph_item");

		if ($graph_item_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $graph_item_id;
}

function api_graph_item_remove($graph_item_id) {
	if ((empty($graph_item_id)) || (!is_numeric($graph_item_id))) {
		return;
	}

	db_execute("delete from graph_item where id = $graph_item_id");
}

function api_graph_item_movedown($graph_item_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_id = db_fetch_cell("select graph_id from graph_item where id = $graph_item_id");

	$next_item = seq_get_item("graph_item", "sequence", $graph_item_id, "graph_id = $graph_id", "next");

	seq_move_item("graph_item", $graph_item_id, "graph_id = $graph_id", "down");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $graph_item_id") . " where graph_item_id = $graph_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $next_item") . " where graph_item_id = $next_item");
}

function api_graph_item_moveup($graph_item_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_id = db_fetch_cell("select graph_id from graph_item where id = $graph_item_id");

	$last_item = seq_get_item("graph_item", "sequence", $graph_item_id, "graph_id = $graph_id", "previous");

	seq_move_item("graph_item", $graph_item_id, "graph_id = $graph_id", "up");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $graph_item_id") . " where graph_item_id = $graph_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $last_item") . " where graph_item_id = $last_item");
}

function api_graph_item_row_movedown($row_num, $graph_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_item", "graph_id = $graph_id", true, "down");
}

function api_graph_item_row_moveup($row_num, $graph_id) {
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_item", "graph_id = $graph_id", true, "up");
}

/* update_graph_title_cache - updates the title cache for a single graph
   @arg $graph_id - (int) the ID of the graph to update the title cache for */
function update_graph_title_cache($graph_id) {
	include_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	if (empty($graph_id)) {
		return;
	}

	db_execute("update graph set title_cache = '" . addslashes(get_graph_title($graph_id)) . "' where id = $graph_id");
}

/* update_graph_title_cache_from_template - updates the title cache for all graphs
	that match a given graph template
   @arg $graph_template_id - (int) the ID of the graph template to match */
function update_graph_title_cache_from_template($graph_template_id) {
	$graphs = db_fetch_assoc("select local_graph_id from graph_templates_graph where graph_template_id=$graph_template_id and local_graph_id>0");

	if (sizeof($graphs) > 0) {
	foreach ($graphs as $item) {
		update_graph_title_cache($item["local_graph_id"]);
	}
	}
}

/* update_graph_title_cache_from_query - updates the title cache for all graphs
	that match a given data query/index combination
   @arg $snmp_query_id - (int) the ID of the data query to match
   @arg $snmp_index - the index within the data query to match */
function update_graph_title_cache_from_query($snmp_query_id, $snmp_index) {
	$graphs = db_fetch_assoc("select id from graph_local where snmp_query_id=$snmp_query_id and snmp_index='$snmp_index'");

	if (sizeof($graphs) > 0) {
	foreach ($graphs as $item) {
		update_graph_title_cache($item["id"]);
	}
	}
}

/* update_graph_title_cache_from_host - updates the title cache for all graphs
	that match a given host
   @arg $host_id - (int) the ID of the host to match */
function update_graph_title_cache_from_host($host_id) {
	$graphs = db_fetch_assoc("select id from graph where host_id = $host_id");

	if (sizeof($graphs) > 0) {
		foreach ($graphs as $item) {
			update_graph_title_cache($item["id"]);
		}
	}
}

?>