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
include_once("./lib/graph/graph_template_update.php");
include_once("./include/graph/graph_form.php");
include_once("./lib/template.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		item_remove();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'item_movedown':
		item_movedown();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'item_moveup':
		item_moveup();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'row_movedown':
		row_movedown();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'row_moveup':
		row_moveup();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'group':
		item_group();

		header("Location: graph_templates.php?action=edit&id=" . $_SESSION["sess_field_values"]["id"]);
		break;
	case 'ungroup':
		item_ungroup();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
	case 'edit':
		include_once("./include/top_header.php");

		item_edit();

		include_once("./include/bottom_footer.php");
		break;
	case 'item':
		include_once("./include/top_header.php");

		item();

		include_once ("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_item"])) {
		$graph_template_item_id = api_graph_template_item_save($_POST["graph_template_item_id"], $_POST["graph_template_id"],
			$_POST["data_template_item_id"], $_POST["color"], $_POST["graph_item_type"], $_POST["cdef"], $_POST["consolidation_function"],
			$_POST["gprint_format"], $_POST["legend_format"], $_POST["legend_value"], (isset($_POST["hard_return"]) ?
			$_POST["hard_return"] : ""));

		if (is_error_message()) {
			header("Location: graph_templates_items.php?action=edit" . (empty($graph_template_item_id) ? "" : "&id=" . $graph_template_item_id) . "&graph_template_id=" . $_POST["graph_template_id"]);
		}else{
			header("Location: graph_templates.php?action=edit&id=" . $_POST["graph_template_id"]);
		}
	}
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item_group() {
	if (ereg("&group_item_id=([0-9]+)$", $_SESSION["sess_field_values"]["cacti_js_dropdown_redirect"], $matches)) {
		$graph_template_item_id = $matches[1];

		$selected_items = array();

		/* list each selected item */
		while (list($name, $value) = each($_SESSION["sess_field_values"])) {
			if ((substr($name, 0, 9) == "gi_value_") && ($value == "1")) {
				$selected_items{substr($name, 9)} = 1;
			}
		}

		/* get an official list of items to compare against */
		$graph_template_items = db_fetch_assoc("select id from graph_template_item where graph_template_id = " . $_SESSION["sess_field_values"]["id"] . " order by sequence");

		/* find out which items were selected for grouping by the user */
		$_group = array();
		$keep_this_group = false;
		if (sizeof($graph_template_items) > 0) {
			foreach ($graph_template_items as $item) {
				if (isset($selected_items{$item["id"]})) {
					$_group[] = $item["id"];

					if ($graph_template_item_id == $item["id"]) {
						$keep_this_group = true;
					}
				}else{
					if ($keep_this_group == true) {
						break;
					}

					$_group = array();
				}
			}
		}

		if (sizeof($_group) > 1) {
			db_execute("insert into graph_template_item_group (id,hash,graph_template_id) values (0,''," . $_SESSION["sess_field_values"]["id"] . ")");

			$graph_template_item_group_id = db_fetch_insert_id();

			if ($graph_template_item_group_id) {
				for ($i=0; $i<sizeof($_group); $i++) {
					db_execute("insert into graph_template_item_group_item (graph_template_item_group_id,graph_template_item_id) values ($graph_template_item_group_id," . $_group[$i] . ")");
				}
			}
		}
	}
}

function item_ungroup() {
	if (!empty($_GET["id"])) {
		db_execute("delete from graph_template_item_group_item where graph_template_item_group_id = " . $_GET["id"]);
		db_execute("delete from graph_template_item_group where id = " . $_GET["id"]);
	}
}

function item_movedown() {
	api_graph_template_item_movedown($_GET["id"]);
}

function item_moveup() {
	api_graph_template_item_moveup($_GET["id"]);
}

function row_movedown() {
	api_graph_template_item_row_movedown($_GET["row"], $_GET["graph_template_id"]);
}

function row_moveup() {
	api_graph_template_item_row_moveup($_GET["row"], $_GET["graph_template_id"]);
}

function item_remove() {
	db_execute("delete from graph_templates_item where id=" . $_GET["id"]);
	db_execute("delete from graph_templates_item where local_graph_template_item_id=" . $_GET["id"]);

	/* delete the graph item input if it is empty */
	$graph_item_inputs = db_fetch_assoc("select
		graph_template_input.id
		from graph_template_input,graph_template_input_defs
		where graph_template_input.id=graph_template_input_defs.graph_template_input_id
		and graph_template_input.graph_template_id=" . $_GET["graph_template_id"] . "
		and graph_template_input_defs.graph_template_item_id=" . $_GET["id"] . "
		group by graph_template_input.id");

	if (sizeof($graph_item_inputs) > 0) {
		foreach ($graph_item_inputs as $graph_item_input) {
			if (sizeof(db_fetch_assoc("select graph_template_input_id from graph_template_input_defs where graph_template_input_id=" . $graph_item_input["id"])) == 1) {
				db_execute("delete from graph_template_input where id=" . $graph_item_input["id"]);
			}
		}
	}

	db_execute("delete from graph_template_input_defs where graph_template_item_id=" . $_GET["id"]);
}

function item_edit() {
	global $colors, $struct_graph_item;

	if (!empty($_GET["id"])) {
		$graph_template_item = db_fetch_row("select * from graph_template_item where id=" . $_GET["id"]);
	}

	/* by default, select the LAST DS chosen to make everyone's lives easier */
	$default = db_fetch_row("select data_template_item_id from graph_template_item where graph_template_id=" . $_GET["graph_template_id"] . " order by sequence DESC");

	if (sizeof($default) > 0) {
		$struct_graph_item["data_template_item_id"]["default"] = $default["data_template_item_id"];
	}else{
		$struct_graph_item["data_template_item_id"]["default"] = 0;
	}

	/* modifications to the default graph items array */
	unset($struct_graph_item["data_source_item_id"]);

	$form_array = array();

	while (list($field_name, $field_array) = each($struct_graph_item)) {
		$form_array += array($field_name => $struct_graph_item[$field_name]);

		$form_array[$field_name]["value"] = (isset($graph_template_item) ? $graph_template_item[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($graph_template_item) ? $graph_template_item["id"] : "0");

	}

	/* ==================== Box: Graph Item ==================== */

	html_start_box("<strong>" . _("Graph Item") . "</strong> [" . _("Graph Template: ") . db_fetch_cell("select template_name from graph_template where id=" . $_GET["graph_template_id"]) . "]", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(
				),
			"fields" => $form_array
			)
		);

	html_end_box();

	form_hidden_box("graph_template_item_id", (isset($graph_template_item) ? $graph_template_item["id"] : "0"), "");
	form_hidden_box("graph_template_id", $_GET["graph_template_id"], "0");
	form_hidden_box("save_component_item", "1", "");

	form_save_button("graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
}