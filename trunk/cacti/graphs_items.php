<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_remove':
		item_remove();

		header("Location: graphs.php?action=edit&id=" . $_GET["graph_id"]);
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		item_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'item_movedown':
		item_movedown();

		header("Location: graphs.php?action=edit&id=" . $_GET["graph_id"]);
		break;
	case 'item_moveup':
		item_moveup();

		header("Location: graphs.php?action=edit&id=" . $_GET["graph_id"]);
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_item"])) {
		/* cache all post field values */
		init_post_field_cache();

		/* step #1: field validation */
		$form_graph_item["id"] = $_POST["graph_item_id"];
		$form_graph_item["graph_id"] = $_POST["graph_id"];
		$form_graph_item["data_source_item_id"] = $_POST["data_source_item_id"];
		$form_graph_item["color"] = $_POST["color"];
		$form_graph_item["graph_item_type"] = $_POST["graph_item_type"];
		$form_graph_item["consolidation_function"] = $_POST["consolidation_function"];
		$form_graph_item["cdef"] = $_POST["cdef"];
		$form_graph_item["gprint_format"] = $_POST["gprint_format"];
		$form_graph_item["legend_value"] = $_POST["legend_value"];
		$form_graph_item["legend_format"] = $_POST["legend_format"];
		$form_graph_item["hard_return"] = html_boolean(isset($_POST["hard_return"]) ? $_POST["hard_return"] : "");

		field_register_error(api_graph_item_fields_validate($form_graph_item, "|field|"));

		/* step #2: field save */
		if (!is_error_message()) {
			$graph_item_id = api_graph_item_save($_POST["graph_item_id"], $form_graph_item);
		}

		if (is_error_message()) {
			header("Location: graph_items.php?action=edit" . (empty($graph_item_id) ? "" : "&id=" . $graph_item_id) . "&graph_id=" . $_POST["graph_id"]);
		}else{
			header("Location: graphs.php?action=edit&id=" . $_POST["graph_id"]);
		}
	}
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item_movedown() {
	api_graph_item_movedown($_GET["id"]);
}

function item_moveup() {
	api_graph_item_moveup($_GET["id"]);
}

function row_movedown() {
	api_graph_item_row_movedown($_GET["row"], $_GET["graph_id"]);
}

function row_moveup() {
	api_graph_item_row_moveup($_GET["row"], $_GET["graph_id"]);
}

function item_remove() {
	api_graph_item_remove($_GET["id"]);
}

function item_edit() {
	global $colors;

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_ds_host_id");

		unset($_REQUEST["host_id"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("filter", "sess_ds_filter", "");
	load_current_session_value("host_id", "sess_ds_host_id", "-1");

	$host = db_fetch_row("select hostname from host where id = " . sql_sanitize($_REQUEST["host_id"]));

	html_start_box("<strong>Data Source by Host</strong> [host: " . (empty($host["hostname"]) ? "No Host" : $host["hostname"]) . "]", "98%", $colors["header"], "3", "center", "");

	include("./include/html/inc_graph_items_filter_table.php");

	html_end_box();

	if ($_REQUEST["host_id"] == "-1") {
		$sql_where = "";
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where = " and data_local.host_id = 0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where = " and data_local.host_id = " . sql_sanitize($_REQUEST["host_id"]);
	}

	if (!empty($_GET["id"])) {
		$graph_item = db_fetch_row("select * from graph_item where id = " . sql_sanitize($_GET["id"]));
		$host_id = db_fetch_cell("select host_id from graph where id = " . sql_sanitize($_GET["graph_id"]));
	}

	/* by default, select the LAST DS chosen to make everyone's lives easier */
	$default = db_fetch_row("select data_source_item_id from graph_item where graph_id = " . sql_sanitize($_GET["graph_id"]) . " order by sequence DESC limit 1");

	form_start("graphs_items.php", "form_graph_item");

	/* ==================== Box: Graph Item ==================== */

	html_start_box("<strong>" . _("Graph Item") . "</strong> [Graph: " . db_fetch_cell("select title_cache from graph where id=" . $_GET["graph_id"]) . "]", "98%", $colors["header_background"], "3", "center", "");

	_graph_item_field__data_source_item_id("data_source_item_id", (sizeof($default) == 1 ? $default["data_source_item_id"] : "0"), (empty($_GET["id"]) ? 0 : $_GET["id"]), $host_id);
	_graph_item_field__color("color", (isset($graph_item["color"]) ? $graph_item["color"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__graph_item_type("graph_item_type", (isset($graph_item["graph_item_type"]) ? $graph_item["graph_item_type"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__consolidation_function("consolidation_function", (isset($graph_item["consolidation_function"]) ? $graph_item["consolidation_function"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__cdef("cdef", (isset($graph_item["cdef"]) ? $graph_item["cdef"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__gprint_format("gprint_format", (isset($graph_item["gprint_format"]) ? $graph_item["gprint_format"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__legend_value("legend_value", (isset($graph_item["legend_value"]) ? $graph_item["legend_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__legend_format("legend_format", (isset($graph_item["legend_format"]) ? $graph_item["legend_format"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_graph_item_field__hard_return("hard_return", (isset($graph_item["hard_return"]) ? $graph_item["hard_return"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));

	form_hidden_box("graph_item_id", (isset($graph_item) ? $graph_item["id"] : "0"), "");
	form_hidden_box("graph_id", $_GET["graph_id"], "0");
	form_hidden_box("save_component_item", "1", "");

	html_end_box();

	form_save_button("graphs.php?action=edit&id=" . $_GET["graph_id"]);
}
