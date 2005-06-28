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
include_once("./lib/graph/graph_update.php");
include_once("./include/graph/graph_constants.php");
include_once("./include/graph/graph_arrays.php");
include_once("./include/graph/graph_form.php");
include_once("./lib/utility.php");

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
		include_once("./include/top_header.php");

		item_edit();

		include_once("./include/bottom_footer.php");
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
		//$items[0] = array();

		//if ($graph_item_types{$_POST["graph_type_id"]} == "LEGEND") {
			/* this can be a major time saver when creating lots of graphs with the typical
			GPRINT LAST/AVERAGE/MAX legends */
		//	$items = array(
		//		0 => array(
		//			"color_id" => "0",
		//			"graph_type_id" => "9",
		//			"consolidation_function_id" => "4",
		//			"text_format" => "Current:",
		//			"hard_return" => ""
		//			),
		//		1 => array(
		//			"color_id" => "0",
		//			"graph_type_id" => "9",
		//			"consolidation_function_id" => "1",
		//			"text_format" => "Average:",
		//			"hard_return" => ""
		//			),
		//		2 => array(
		//			"color_id" => "0",
		//			"graph_type_id" => "9",
		//			"consolidation_function_id" => "3",
		//			"text_format" => "Maximum:",
		//			"hard_return" => "on"
		//			));
		//}

		$graph_item_id = api_graph_item_save($_POST["graph_item_id"], $_POST["graph_id"],
			$_POST["data_source_item_id"], $_POST["color"], $_POST["graph_item_type"], $_POST["cdef"], $_POST["consolidation_function"],
			$_POST["gprint_format"], $_POST["legend_format"], $_POST["legend_value"], (isset($_POST["hard_return"]) ?
			$_POST["hard_return"] : ""));

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
	global $colors, $struct_graph_item;

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_ds_host_id");

		unset($_REQUEST["host_id"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("filter", "sess_ds_filter", "");
	load_current_session_value("host_id", "sess_ds_host_id", "-1");

	$host = db_fetch_row("select hostname from host where id=" . $_REQUEST["host_id"]);

	html_start_box("<strong>Data Source by Host</strong> [host: " . (empty($host["hostname"]) ? "No Host" : $host["hostname"]) . "]", "98%", $colors["header"], "3", "center", "");

	include("./include/html/inc_graph_items_filter_table.php");

	html_end_box();

	if ($_REQUEST["host_id"] == "-1") {
		$sql_where = "";
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where = " and data_local.host_id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where = " and data_local.host_id=" . $_REQUEST["host_id"];
	}

	if (!empty($_GET["id"])) {
		$graph_item = db_fetch_row("select * from graph_item where id=" . $_GET["id"]);
		$host_id = db_fetch_cell("select host_id from graph_local where id=" . $_GET["local_graph_id"]);
	}

	/* by default, select the LAST DS chosen to make everyone's lives easier */
	$default = db_fetch_row("select data_source_item_id from graph_item where graph_id=" . $_GET["graph_id"] . " order by sequence DESC");

	if (sizeof($default) > 0) {
		$struct_graph_item["data_source_item_id"]["default"] = $default["data_source_item_id"];
	}else{
		$struct_graph_item["data_source_item_id"]["default"] = 0;
	}

	/* modifications to the default graph items array */
	unset($struct_graph_item["data_template_item_id"]);

	if ($_REQUEST["host_id"] > 0) {
    	$struct_graph_item["data_source_item_id"]["sql"] = "select
			CONCAT_WS('',data_source.name_cache,' (',data_template.template_name,'[',data_source_item.data_source_name,'])') AS name,
			data_source_item.id,
			data_template.template_name AS data_template_name
			FROM host
			RIGHT JOIN (data_template RIGHT JOIN (data_source LEFT JOIN data_source_item ON data_source.id = data_source_item.data_source_id) ON data_template.id = data_source.data_template_id) ON host.id = data_source.host_id
			WHERE host.id=" . $_GET["host_id"] . "
			ORDER BY name";
    }elseif ($_REQUEST["host_id"] == -1) {
		$struct_graph_item["data_source_item_id"]["sql"] = "select
			CONCAT_WS('',case when host.description is null then 'No Host - ' end,data_source.name_cache,' (',case when data_template.template_name is null then 'No Template' when data_template.template_name is not null then data_template.template_name end,'[',data_source_item.data_source_name,'])') as name,
			data_source_item.id
			FROM host
			RIGHT JOIN (data_template RIGHT JOIN (data_source LEFT JOIN data_source_item ON data_source.id = data_source_item.data_source_id) ON data_template.id = data_source.data_template_id) ON host.id = data_source.host_id
			WHERE data_source_item.data_source_id=data_source.id and host.id is null ORDER BY name";
	}else{
		$struct_graph_item["data_source_item_id"]["sql"] = "select
			CONCAT_WS('',case when host.description is null then 'No Host - ' end,data_source.name_cache,' (',case when data_template.template_name is null then 'No Template' when data_template.template_name is not null then data_template.template_name end,'[',data_source_item.data_source_name,'])') as name,
			data_source_item.id
			FROM host
			RIGHT JOIN (data_template RIGHT JOIN (data_source LEFT JOIN data_source_item ON data_source.id = data_source_item.data_source_id) ON data_template.id = data_source.data_template_id) ON host.id = data_source.host_id
			WHERE data_source_item.data_source_id=data_source.id ORDER BY name";
	}

	$form_array = array();

	while (list($field_name, $field_array) = each($struct_graph_item)) {
		$form_array += array($field_name => $struct_graph_item[$field_name]);

		$form_array[$field_name]["value"] = (isset($graph_item) ? $graph_item[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($graph_item) ? $graph_item["id"] : "0");
	}

	/* ==================== Box: Graph Item ==================== */

	html_start_box("<strong>" . _("Graph Item") . "</strong> [Graph: " . db_fetch_cell("select title_cache from graph where id=" . $_GET["graph_id"]) . "]", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	form_hidden_box("graph_item_id", (isset($graph_item) ? $graph_item["id"] : "0"), "");
	form_hidden_box("graph_id", $_GET["graph_id"], "0");
	form_hidden_box("save_component_item", "1", "");

	html_end_box();

	form_save_button("graphs.php?action=edit&id=" . $_GET["graph_id"]);
}