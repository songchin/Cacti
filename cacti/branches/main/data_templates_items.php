<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/template.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }


switch (get_request_var_request("action")) {
	case 'save':
		data_template_item_save();

		break;
	case 'item_remove':
		data_template_item_remove();

		header("Location: data_templates.php?action=template_edit&id=" . get_request_var("data_template_id"));
		break;
	case 'item_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_template_item_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
 The Save Function
 -------------------------- */
/**
 * data_template_item_save	- save data to table data_template_rrd
 */
function data_template_item_save() {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	if (isset($_POST["save_component_item"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("data_template_id"));
		/* ==================================================== */

		/* save: data_template_rrd */
		$save["id"] = $_POST["data_template_rrd_id"];
		$save["hash"] = get_hash_data_template($_POST["data_template_rrd_id"], "data_template_item");
		$save["local_data_template_rrd_id"] = 0;
		$save["local_data_id"] = 0;
		$save["data_template_id"] = $_POST["data_template_id"];

		$save["t_rrd_maximum"] = form_input_validate((isset($_POST["t_rrd_maximum"]) ? $_POST["t_rrd_maximum"] : ""), "t_rrd_maximum", "", true, 3);
		$save["rrd_maximum"] = form_input_validate($_POST["rrd_maximum"], "rrd_maximum", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", (isset($_POST["t_rrd_maximum"]) ? true : false), 3);
		$save["t_rrd_minimum"] = form_input_validate((isset($_POST["t_rrd_minimum"]) ? $_POST["t_rrd_minimum"] : ""), "t_rrd_minimum", "", true, 3);
		$save["rrd_minimum"] = form_input_validate($_POST["rrd_minimum"], "rrd_minimum", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", (isset($_POST["t_rrd_minimum"]) ? true : false), 3);
		$save["t_rrd_compute_rpn"] = form_input_validate((isset($_POST["t_rrd_compute_rpn"]) ? $_POST["t_rrd_compute_rpn"] : ""), "t_rrd_compute_rpn", "", true, 3);
		/* rrd_compute_rpn requires input only for COMPUTE data source type */
		$save["rrd_compute_rpn"] = form_input_validate($_POST["rrd_compute_rpn"], "rrd_compute_rpn", "", ((isset($_POST["t_rrd_compute_rpn"]) || ($_POST["data_source_type_id"] != DATA_SOURCE_TYPE_COMPUTE)) ? true : false), 3);
		$save["t_rrd_heartbeat"] = form_input_validate((isset($_POST["t_rrd_heartbeat"]) ? $_POST["t_rrd_heartbeat"] : ""), "t_rrd_heartbeat", "", true, 3);
		$save["rrd_heartbeat"] = form_input_validate($_POST["rrd_heartbeat"], "rrd_heartbeat", "^[0-9]+$", (isset($_POST["t_rrd_heartbeat"]) ? true : false), 3);
		$save["t_data_source_type_id"] = form_input_validate((isset($_POST["t_data_source_type_id"]) ? $_POST["t_data_source_type_id"] : ""), "t_data_source_type_id", "", true, 3);
		$save["data_source_type_id"] = form_input_validate($_POST["data_source_type_id"], "data_source_type_id", "", true, 3);
		$save["t_data_source_name"] = form_input_validate((isset($_POST["t_data_source_name"]) ? $_POST["t_data_source_name"] : ""), "t_data_source_name", "", true, 3);
		$save["data_source_name"] = form_input_validate($_POST["data_source_name"], "data_source_name", "^[a-zA-Z0-9_]{1,19}$", (isset($_POST["t_data_source_name"]) ? true : false), 3);
		$save["t_data_input_field_id"] = form_input_validate((isset($_POST["t_data_input_field_id"]) ? $_POST["t_data_input_field_id"] : ""), "t_data_input_field_id", "", true, 3);
		$save["data_input_field_id"] = form_input_validate((isset($_POST["data_input_field_id"]) ? $_POST["data_input_field_id"] : "0"), "data_input_field_id", "", true, 3);

		if (!is_error_message()) {

			$data_template_rrd_id = sql_save($save, "data_template_rrd");

			if ($data_template_rrd_id) {
				raise_message(1);
				push_out_data_source_item($data_template_rrd_id);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: data_templates_items.php?action=item_edit&item_id=" . (empty($data_template_rrd_id) ? $_POST["data_template_rrd_id"] : $data_template_rrd_id) . "&id=" . $_POST["data_template_id"]);
		}else{
			header("Location: data_templates.php?action=template_edit&id=" . $_POST["data_template_id"]);
		}
	}
}


/**
 * data_template_item_remove	- remove a data template item (table data_template_rrd)
 */
function data_template_item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("data_template_id"));
	/* ==================================================== */

	$children = db_fetch_assoc("select id from data_template_rrd where local_data_template_rrd_id=" . $_GET["id"] . " or id=" . $_GET["id"]);

	if (sizeof($children) > 0) {
		foreach ($children as $item) {
			db_execute("DELETE FROM data_template_rrd WHERE id=" . $item["id"]);
			db_execute("DELETE FROM snmp_query_graph_rrd WHERE data_template_rrd_id=" . $item["id"]);
			db_execute("UPDATE data_templates_item set task_item_id=0 WHERE task_item_id=" . $item["id"]);
		}
	}

	header("Location: data_templates.php?action=template_edit&id=" . $_GET["data_template_id"]);
	exit;
}


/**
 * data_template_item_edit	- edit a data template item (aka data source in rrdtool lingo)
 */
function data_template_item_edit() {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("data_template_id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		#$template = db_fetch_row("SELECT * FROM data_template WHERE id=" . $_GET["id"]);
		$template_data = db_fetch_row("SELECT * FROM data_template_data WHERE data_template_id=" . $_GET["id"] . " AND local_data_id=0");
		$template_item = db_fetch_row("SELECT * FROM data_template_rrd WHERE id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $template_item["data_source_name"] . "]";
	}else{
		$template_data = array();
		$template_item = array();
		$header_label = __("[new]");
	}


	# the template header
	html_start_box("<strong>" . __("Data Template Item") . "</strong> $header_label", "100", $colors["header"], 0, "center", "", true);
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_data_template_item_edit');

	/* data input fields list */
	$struct_data_source_item = data_source_item_form_list();
	if ((empty($template_data["data_input_id"])) ||
		((db_fetch_cell("select type_id from data_input where id=" . $template_data["data_input_id"]) != "1") &&
		(db_fetch_cell("select type_id from data_input where id=" . $template_data["data_input_id"]) != "5"))) {
		unset($struct_data_source_item["data_input_field_id"]);
	}else{
		$struct_data_source_item["data_input_field_id"]["sql"] = "select id,CONCAT(data_name,' - ',name) as name from data_input_fields where data_input_id=" . $template_data["data_input_id"] . " and input_output='out' and update_rra='on' order by data_name,name";
	}

	$form_array = array();

	while (list($field_name, $field_array) = each($struct_data_source_item)) {
		$form_array += array($field_name => $struct_data_source_item[$field_name]);

		$form_array[$field_name]["description"] = "";
		$form_array[$field_name]["value"] = (isset($template_item[$field_name]) ? $template_item[$field_name] : "");
		$form_array[$field_name]["sub_checkbox"] = array(
			"name" => "t_" . $field_name,
			"friendly_name" => "Use Per-Data Source Value (Ignore this Value)",
			"value" => (isset($template_item[$field_name]) ? $template_item{"t_" . $field_name} : "")
			);
	}

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	html_end_box();

	form_hidden_box("data_template_rrd_id", (isset($template_item["id"]) ? $template_item["id"] : "0"), "");
	form_hidden_box("data_template_item_id", (isset($template_item["id"]) ? $template_item["id"] : "0"), "");
	form_hidden_box("data_template_id", (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : "0"), "");
	form_hidden_box("save_component_item", "1", "");
	form_hidden_box("hidden_rrdtool_version", read_config_option("rrdtool_version"), "");

	#form_save_button("data_templates.php?action=template_edit&id=" . $_GET["id"]);
	form_save_button_alt("url!" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""));

	include_once(CACTI_BASE_PATH . "/access/js/data_source_item.js");
	include_once(CACTI_BASE_PATH . "/access/js/field_description_hover.js");

}
