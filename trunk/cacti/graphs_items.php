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

include("./include/config.php");
include("./include/auth.php");
include_once("./lib/utility.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_remove':
		item_remove();

		header("Location: graphs.php?action=graph_edit&id=" . $_GET["local_graph_id"]);
		break;
	case 'item_edit':
		include_once("./include/top_header.php");

		item_edit();

		include_once("./include/bottom_footer.php");
		break;
	case 'item_movedown':
		item_movedown();

		header("Location: graphs.php?action=graph_edit&id=" . $_GET["local_graph_id"]);
		break;
	case 'item_moveup':
		item_moveup();

		header("Location: graphs.php?action=graph_edit&id=" . $_GET["local_graph_id"]);
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_item"])) {
		global $graph_item_types;

		$items[0] = array();

		if ($graph_item_types{$_POST["graph_type_id"]} == "LEGEND") {
			/* this can be a major time saver when creating lots of graphs with the typical
			GPRINT LAST/AVERAGE/MAX legends */
			$items = array(
				0 => array(
					"color_id" => "0",
					"graph_type_id" => "9",
					"consolidation_function_id" => "4",
					"text_format" => "Current:",
					"hard_return" => ""
					),
				1 => array(
					"color_id" => "0",
					"graph_type_id" => "9",
					"consolidation_function_id" => "1",
					"text_format" => "Average:",
					"hard_return" => ""
					),
				2 => array(
					"color_id" => "0",
					"graph_type_id" => "9",
					"consolidation_function_id" => "3",
					"text_format" => "Maximum:",
					"hard_return" => "on"
					));
		}

		foreach ($items as $item) {
			/* generate a new sequence if needed */
			if (empty($_POST["sequence"])) {
				$_POST["sequence"] = get_sequence($_POST["sequence"], "sequence", "graph_templates_item", "local_graph_id=" . $_POST["local_graph_id"]);
			}

			$save["id"] = $_POST["graph_template_item_id"];
			$save["graph_template_id"] = $_POST["graph_template_id"];
			$save["local_graph_template_item_id"] = $_POST["local_graph_template_item_id"];
			$save["local_graph_id"] = $_POST["local_graph_id"];
			$save["task_item_id"] = form_input_validate($_POST["task_item_id"], "task_item_id", "", true, 3);
			$save["color_id"] = form_input_validate((isset($item["color_id"]) ? $item["color_id"] : $_POST["color_id"]), "color_id", "", true, 3);
			$save["graph_type_id"] = form_input_validate((isset($item["graph_type_id"]) ? $item["graph_type_id"] : $_POST["graph_type_id"]), "graph_type_id", "", true, 3);
			$save["cdef_id"] = form_input_validate($_POST["cdef_id"], "cdef_id", "", true, 3);
			$save["consolidation_function_id"] = form_input_validate((isset($item["consolidation_function_id"]) ? $item["consolidation_function_id"] : $_POST["consolidation_function_id"]), "consolidation_function_id", "", true, 3);
			$save["text_format"] = form_input_validate((isset($item["text_format"]) ? $item["text_format"] : $_POST["text_format"]), "text_format", "", true, 3);
			$save["value"] = form_input_validate($_POST["value"], "value", "", true, 3);
			$save["hard_return"] = form_input_validate(((isset($item["hard_return"]) ? $item["hard_return"] : (isset($_POST["hard_return"]) ? $_POST["hard_return"] : ""))), "hard_return", "", true, 3);
			$save["gprint_id"] = form_input_validate($_POST["gprint_id"], "gprint_id", "", true, 3);
			$save["sequence"] = $_POST["sequence"];

			if (!is_error_message()) {
				$graph_template_item_id = sql_save($save, "graph_templates_item");

				if ($graph_template_item_id) {
					raise_message(1);
				}else{
					raise_message(2);
				}
			}

			$_POST["sequence"] = 0;
		}

		if (is_error_message()) {
			header("Location: graphs.php?action=item_edit&graph_template_item_id=" . (empty($graph_template_item_id) ? $_POST["graph_template_item_id"] : $graph_template_item_id) . "&id=" . $_POST["local_graph_id"]);
			exit;
		}else{
			header("Location: graphs.php?action=graph_edit&id=" . $_POST["local_graph_id"]);
			exit;
		}
	}
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item_movedown() {
	global $graph_item_types;

	$arr = get_graph_group($_GET["id"]);
	$next_id = get_graph_parent($_GET["id"], "next");

	if ((!empty($next_id)) && (isset($arr{$_GET["id"]}))) {
		move_graph_group($_GET["id"], $arr, $next_id, "next");
	}elseif (ereg("(GPRINT|VRULE|HRULE|COMMENT)", $graph_item_types{db_fetch_cell("select graph_type_id from graph_templates_item where id=" . $_GET["id"])})) {
		move_item_down("graph_templates_item", $_GET["id"], "local_graph_id=" . $_GET["local_graph_id"]);
	}
}

function item_moveup() {
	global $graph_item_types;

	$arr = get_graph_group($_GET["id"]);
	$previous_id = get_graph_parent($_GET["id"], "previous");

	if ((!empty($previous_id)) && (isset($arr{$_GET["id"]}))) {
		move_graph_group($_GET["id"], $arr, $previous_id, "previous");
	}elseif (ereg("(GPRINT|VRULE|HRULE|COMMENT)", $graph_item_types{db_fetch_cell("select graph_type_id from graph_templates_item where id=" . $_GET["id"])})) {
		move_item_up("graph_templates_item", $_GET["id"], "local_graph_id=" . $_GET["local_graph_id"]);
	}
}

function item_remove() {
	db_execute("delete from graph_templates_item where id=" . $_GET["id"]);
}

function item_edit() {
	global $colors, $struct_graph_item, $graph_item_types, $consolidation_functions;

	if (!empty($_GET["id"])) {
		$template_item = db_fetch_row("select * from graph_templates_item where id=" . $_GET["id"]);
		$host_id = db_fetch_cell("select host_id from graph_local where id=" . $_GET["local_graph_id"]);
	}

	$header_label = "[edit graph: " . db_fetch_cell("select title_cache from graph_templates_graph where local_graph_id=" . $_GET["local_graph_id"]) . "]";

	html_start_box("<strong>Graph Items</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	/* by default, select the LAST DS chosen to make everyone's lives easier */
	if (!empty($_GET["local_graph_id"])) {
		$default = db_fetch_row("select task_item_id from graph_templates_item where local_graph_id=" . $_GET["local_graph_id"] . " order by sequence DESC");

		if (sizeof($default) > 0) {
			$struct_graph_item["task_item_id"]["default"] = $default["task_item_id"];
		}else{
			$struct_graph_item["task_item_id"]["default"] = 0;
		}

		/* modifications to the default graph items array */
		$struct_graph_item["task_item_id"]["sql"] = "select
			CONCAT_WS('',case when host.description is null then 'No Host' when host.description is not null then host.description end,' - ',data_template_data.name_cache,' (',data_template_rrd.data_source_name,')') as name,
			data_template_rrd.id
			from data_template_data,data_template_rrd,data_local
			left join host on data_local.host_id=host.id
			where data_template_rrd.local_data_id=data_local.id
			and data_template_data.local_data_id=data_local.id
			" . (((!empty($host_id)) || (!empty($_GET["host_id"]))) ? (!empty($host_id) ? " and data_local.host_id=$host_id" : " and data_local.host_id=" . $_GET["host_id"]) : "") . "
			order by name";
	}

	$form_array = array();

	while (list($field_name, $field_array) = each($struct_graph_item)) {
		$form_array += array($field_name => $struct_graph_item[$field_name]);

		$form_array[$field_name]["value"] = (isset($template_item) ? $template_item[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($template_item) ? $template_item["id"] : "0");

	}

	draw_edit_form(
		array(
			"config" => array(
				),
			"fields" => $form_array
			)
		);

	form_hidden_box("local_graph_id", $_GET["local_graph_id"], "0");
	form_hidden_box("graph_template_item_id", (isset($template_item) ? $template_item["id"] : "0"), "");
	form_hidden_box("local_graph_template_item_id", (isset($template_item) ? $template_item["local_graph_template_item_id"] : "0"), "");
	form_hidden_box("graph_template_id", (isset($template_item) ? $template_item["graph_template_id"] : "0"), "");
	form_hidden_box("sequence", (isset($template_item) ? $template_item["sequence"] : "0"), "");
	form_hidden_box("_graph_type_id", (isset($template_item) ? $template_item["graph_type_id"] : "0"), "");
	form_hidden_box("save_component_item", "1", "");

	html_end_box();

	form_save_button("graphs.php?action=graph_edit&id=" . $_GET["local_graph_id"]);
}
