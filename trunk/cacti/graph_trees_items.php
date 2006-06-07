<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_update.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_form.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		tree_item_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'moveup':
		tree_item_moveup();

		header("Location: graph_trees.php?action=edit&id=" . $_GET["graph_tree_id"]);
		break;
	case 'movedown':
		tree_item_movedown();

		header("Location: graph_trees.php?action=edit&id=" . $_GET["graph_tree_id"]);
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		tree();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

function form_save() {
	if ($_POST["action_post"] == "graph_tree_item_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_graph_tree_item["graph_tree_id"] = $_POST["graph_tree_id"];
		$form_graph_tree_item["device_grouping_type"] = $_POST["device_grouping_type"];
		$form_graph_tree_item["parent_item_id"] = $_POST["parent_item_id"];
		$form_graph_tree_item["sort_children_type"] = $_POST["sort_children_type"];
		$form_graph_tree_item["item_type"] = $_POST["item_type"];
		$form_graph_tree_item["propagate_changes"] = html_boolean(isset($_POST["propagate_changes"]) ? $_POST["propagate_changes"] : "");

		if ($_POST["item_type"] == TREE_ITEM_TYPE_HEADER) {
			$form_graph_tree_item["item_value"] = $_POST["item_value_title"];
		}else if ($_POST["item_type"] == TREE_ITEM_TYPE_GRAPH) {
			$form_graph_tree_item["item_value"] = $_POST["item_value_graph"];
		}else if ($_POST["item_type"] == TREE_ITEM_TYPE_HOST) {
			$form_graph_tree_item["item_value"] = $_POST["item_value_device"];
		}

		/* obtain a list of visible graph tree item fields on the form */
		$visible_fields = api_graph_tree_item_visible_field_list($_POST["item_type"]);

		/* all non-visible fields on the form should be discarded */
		foreach ($visible_fields as $field_name) {
			$v_form_graph_tree_item[$field_name] = $form_graph_tree_item[$field_name];
		}

		/* validate graph tree item preset fields */
		field_register_error(api_graph_tree_item_fields_validate($v_form_graph_tree_item, "|field|"));

		/* the header title textbox goes by a different name on the form */
		if ((field_error_isset("item_value")) && ($_POST["item_type"] == TREE_ITEM_TYPE_HEADER)) {
			field_register_error("item_value_title");
		}

		if (!is_error_message()) {
			$graph_tree_item_id = api_graph_tree_item_save($_POST["id"], $form_graph_tree_item);
		}

		if (is_error_message()) {
			header("Location: graph_trees_items.php?action=edit&tree_id=" . $_POST["graph_tree_id"] . (empty($graph_tree_item_id) ? "" : "&id=$graph_tree_item_id"));
		}else{
			header("Location: graph_trees.php?action=edit&id=" . $_POST["graph_tree_id"]);
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $graph_tree_item_id) {
				api_graph_tree_item_remove($graph_tree_item_id);
			}
		}

		header("Location: graph_trees.php?action=edit&id=" . $_POST["graph_tree_id"]);
	}
}

function tree_item_edit() {
	$_graph_tree_item_id = get_get_var_number("id");
	$_graph_tree_id = get_get_var_number("tree_id");

	if (isset_get_var("parent_id")) {
		$_graph_tree_item_parent_id = get_get_var_number("parent_id");
	}

	if (empty($_graph_tree_item_id)) {
		$header_label = "[new]";
	}else{
		$graph_tree_item = api_graph_tree_item_get($_graph_tree_item_id);

		$header_label = "[edit]";
	}

	$tree_sort_type = db_fetch_cell("select sort_type from graph_tree where id='" . $_GET["tree_id"] . "'");

	form_start("graph_trees_items.php", "form_graph_tree_item");

	html_start_box("<strong>Tree Items</strong> $header_label");

	_graph_tree_item_field__parent_item_id($_graph_tree_id, "parent_item_id", (isset($_graph_tree_item_parent_id) ? $_graph_tree_item_parent_id : api_graph_tree_item_parent_get($_graph_tree_item_id)), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__item_type("item_type", (isset($graph_tree_item) ? $graph_tree_item["item_type"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__title("item_value_title", (isset($graph_tree_item) ? $graph_tree_item["item_value"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__sort_children_type("sort_children_type", (isset($graph_tree_item) ? $graph_tree_item["sort_children_type"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__propagate_changes("propagate_changes", "", (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__graph("item_value_graph", (isset($graph_tree_item) ? $graph_tree_item["item_value"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__device("item_value_device", (isset($graph_tree_item) ? $graph_tree_item["item_value"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__device_grouping_type("device_grouping_type", (isset($graph_tree_item) ? $graph_tree_item["device_grouping_type"] : ""), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));
	_graph_tree_item_field__item_type_js_update((isset($graph_tree_item) ? $graph_tree_item["item_type"] : TREE_ITEM_TYPE_HEADER), (empty($_graph_tree_item_id) ? 0 : $_graph_tree_item_id));

	form_hidden_box("id", $_graph_tree_item_id, "");
	form_hidden_box("graph_tree_id", $_graph_tree_id, "");
	form_hidden_box("action_post", "graph_tree_item_edit");

	html_end_box();

	form_save_button("graph_trees.php?action=edit&id=" . $_graph_tree_id);
}

function tree_item_moveup() {
	$_graph_tree_item_id = get_get_var_number("id");

	api_graph_tree_item_move($_graph_tree_item_id, "up");
}

function tree_item_movedown() {
	$_graph_tree_item_id = get_get_var_number("id");

	api_graph_tree_item_move($_graph_tree_item_id, "down");
}

function item_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		$graph_tree_item = db_fetch_row("select title,local_graph_id,host_id from graph_tree_items where id=" . $_GET["id"]);

		if (!empty($graph_tree_item["local_graph_id"])) {
			$text = "Are you sure you want to delete the graph item <strong>'" . db_fetch_cell("select title_cache from graph where id=" . $graph_tree_item["local_graph_id"]) . "'</strong>?";
		}elseif ($graph_tree_item["title"] != "") {
			$text = "Are you sure you want to delete the header item <strong>'" . $graph_tree_item["title"] . "'</strong>?";
		}elseif (!empty($graph_tree_item["host_id"])) {
			$text = "Are you sure you want to delete the host item <strong>'" . db_fetch_cell("select CONCAT_WS('',description,' (',hostname,')') as hostname from host where id=" . $graph_tree_item["host_id"]) . "'</strong>?";
		}

		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm("Are You Sure?", $text, "graph_trees.php?action=edit&id=" . $_GET["tree_id"], "graph_trees.php?action=item_remove&id=" . $_GET["id"] . "&tree_id=" . $_GET["tree_id"]);
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		delete_branch($_GET["id"]);
	}

	header("Location: graph_trees.php?action=edit&id=" . $_GET["tree_id"]); exit;
}

?>
