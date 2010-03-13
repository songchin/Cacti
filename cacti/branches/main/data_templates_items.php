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

function data_template_item_save() {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");

	if (isset($_POST["save_component_item"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("data_template_id"));
		input_validate_input_number(get_request_var_post("task_item_id"));
		/* ==================================================== */

		$save["id"] 				= form_input_validate($_POST["graph_template_item_id"], "graph_template_item_id", "^[0-9]+$", false, 3);
		$save["hash"] 				= get_hash_graph_template($_POST["graph_template_item_id"], "graph_template_item");
		$save["data_template_id"] 	= form_input_validate($_POST["data_template_id"], "data_template_id", "^[0-9]+$", false, 3);
		$save["local_graph_id"] 	= 0;
		$save["task_item_id"] 		= form_input_validate(((isset($item["task_item_id"]) ? $item["task_item_id"] : (isset($_POST["task_item_id"]) ? $_POST["task_item_id"] : 0))), "task_item_id", "^[0-9]+$", true, 3);
		$save["color_id"] 			= form_input_validate(((isset($item["color_id"]) ? $item["color_id"] : (isset($_POST["color_id"]) ? $_POST["color_id"] : 0))), "color_id", "^[0-9]+$", true, 3);
		$save["alpha"] 				= form_input_validate(((isset($item["alpha"]) ? $item["alpha"] : (isset($_POST["alpha"]) ? $_POST["alpha"] : "FF"))), "alpha", "^[a-fA-F0-9]+$", true, 3);
		$save["graph_type_id"]		= form_input_validate(((isset($item["graph_type_id"]) ? $item["graph_type_id"] : (isset($_POST["graph_type_id"]) ? $_POST["graph_type_id"] : 0))), "graph_type_id", "^[0-9]+$", true, 3);
		$save["dashes"] 			= form_input_validate((isset($_POST["dashes"]) ? $_POST["dashes"] : ""), "dashes", "^[0-9]+[,0-9]*$", true, 3);
		$save["dash_offset"] 		= form_input_validate((isset($_POST["dash_offset"]) ? $_POST["dash_offset"] : ""), "dash_offset", "^[0-9]+$", true, 3);
		$save["cdef_id"] 			= form_input_validate(((isset($item["cdef_id"]) ? $item["cdef_id"] : (isset($_POST["cdef_id"]) ? $_POST["cdef_id"] : 0))), "cdef_id", "^[0-9]+$", true, 3);
		$save["vdef_id"] 			= form_input_validate(((isset($item["vdef_id"]) ? $item["vdef_id"] : (isset($_POST["vdef_id"]) ? $_POST["vdef_id"] : 0))), "vdef_id", "^[0-9]+$", true, 3);
		$save["shift"] 				= form_input_validate((isset($_POST["shift"]) ? $_POST["shift"] : ""), "shift", "^((on)|)$", true, 3);
		$save["consolidation_function_id"] = form_input_validate(((isset($item["consolidation_function_id"]) ? $item["consolidation_function_id"] : (isset($_POST["consolidation_function_id"]) ? $_POST["consolidation_function_id"] : 0))), "consolidation_function_id", "^[0-9]+$", true, 3);
		$save["textalign"] 			= form_input_validate((isset($_POST["textalign"]) ? $_POST["textalign"] : ""), "textalign", "^[a-z]+$", true, 3);
		$save["text_format"] 		= form_input_validate(((isset($item["text_format"]) ? $item["text_format"] : (isset($_POST["text_format"]) ? $_POST["text_format"] : ""))), "text_format", "", true, 3);
		$save["value"] 				= form_input_validate((isset($_POST["value"]) ? $_POST["value"] : ""), "value", "", true, 3);
		$save["hard_return"] 		= form_input_validate(((isset($item["hard_return"]) ? $item["hard_return"] : (isset($_POST["hard_return"]) ? $_POST["hard_return"] : ""))), "hard_return", "", true, 3);
		$save["gprint_id"] 			= form_input_validate(((isset($item["gprint_id"]) ? $item["gprint_id"] : (isset($_POST["gprint_id"]) ? $_POST["gprint_id"] : 0))), "gprint_id", "^[0-9]+$", true, 3);

		if (!is_error_message()) {
			/* Before we save the item, let's get a look at task_item_id <-> input associations */
			$orig_data_source_graph_inputs = db_fetch_assoc("select
					graph_template_input.id,
					graph_template_input.name,
					data_templates_item.task_item_id
					from (graph_template_input,graph_template_input_defs,data_templates_item)
					where graph_template_input.id=graph_template_input_defs.graph_template_input_id
					and graph_template_input_defs.graph_template_item_id=data_templates_item.id
					and graph_template_input.data_template_id=" . $save["data_template_id"] . "
					and graph_template_input.column_name='task_item_id'
					group by data_templates_item.task_item_id");

			$orig_data_source_to_input = array_rekey($orig_data_source_graph_inputs, "task_item_id", "id");

			$graph_template_item_id = sql_save($save, "data_templates_item");

			if ($graph_template_item_id) {
				raise_message(1);

				if (!empty($save["task_item_id"])) {
					/* old item clean-up.  Don't delete anything if the item <-> task_item_id association remains the same. */
					if ($_POST["hidden_task_item_id"] != $_POST["task_item_id"]) {
						/* It changed.  Delete any old associations */
						db_execute("delete from graph_template_input_defs where graph_template_item_id=$graph_template_item_id");

						/* Input for current data source exists and has changed.  Update the association */
						if (isset($orig_data_source_to_input{$save["task_item_id"]})) {
							db_execute("REPLACE INTO graph_template_input_defs " .
											"(graph_template_input_id, graph_template_item_id) " .
											"VALUES (" .
							$orig_data_source_to_input{$save["task_item_id"]} . "," .
							$graph_template_item_id .
											")");
						}
					}

					/* an input for the current data source does NOT currently exist, let's create one */
					if (!isset($orig_data_source_to_input{$save["task_item_id"]})) {
						$ds_name = db_fetch_cell("select data_source_name from data_template_rrd where id=" . $_POST["task_item_id"]);

						db_execute("REPLACE INTO graph_template_input " .
										"(hash,data_template_id,name,column_name) " .
										"VALUES ('" .
						get_hash_graph_template(0, "graph_template_input") . "'," .
						$save["data_template_id"] . "," .
										"'Data Source [" . $ds_name . "]'," .
										'task_item_id' .
										")");

						$graph_template_input_id = db_fetch_insert_id();

						$graph_items = db_fetch_assoc("select id from data_templates_item where data_template_id=" . $save["data_template_id"] . " and task_item_id=" . $_POST["task_item_id"]);

						if (sizeof($graph_items) > 0) {
							foreach ($graph_items as $graph_item) {
								db_execute("REPLACE INTO graph_template_input_defs " .
												"(graph_template_input_id,graph_template_item_id) " .
												"VALUES (" .
								$graph_template_input_id . "," .
								$graph_item["id"] .
												")");
							}
						}
					}
				}

				push_out_graph_item($graph_template_item_id);


				if (isset($_POST["task_item_id"]) && isset($orig_data_source_to_input{$_POST["task_item_id"]})) {
					/* make sure all current graphs using this graph input are aware of this change */
					push_out_graph_input($orig_data_source_to_input{$_POST["task_item_id"]}, $graph_template_item_id, array($graph_template_item_id => $graph_template_item_id));
				}
			}else{
				raise_message(2);
			}
		}

	}

	if (is_error_message()) {
		header("Location: data_templates_items.php?action=data_template_item_edit&graph_template_item_id=" . (empty($graph_template_item_id) ? $_POST["graph_template_item_id"] : $graph_template_item_id) . "&id=" . $_POST["data_template_id"]);
	}else{
		header("Location: data_templates.php?action=template_edit&id=" . $_POST["data_template_id"]);
	}
	exit;
}



function data_template_item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("data_template_id"));
	/* ==================================================== */

	$children = db_fetch_assoc("select id from data_template_rrd where local_data_template_rrd_id=" . $_GET["id"] . " or id=" . $_GET["id"]);

	if (sizeof($children) > 0) {
		foreach ($children as $item) {
			db_execute("delete from data_template_rrd where id=" . $item["id"]);
			db_execute("delete from snmp_query_graph_rrd where data_template_rrd_id=" . $item["id"]);
			db_execute("update data_templates_item set task_item_id=0 where task_item_id=" . $item["id"]);
		}
	}

	header("Location: data_templates.php?action=data_source_template_edit&id=" . $_GET["data_template_id"]);
	exit;
}


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
		$template_item = db_fetch_row("SELECT * FROM data_template_rrd WHERE id=" . get_request_var("id"));
		$header_label = __("[edit: ") . $template_item["data_source_name"] . "]";
	}else{
		$template_data = array();
		$template_item = array();
		$header_label = __("[new]");
	}


	# the template header
	html_start_box("<strong>" . __("Data Template Items") . "</strong> $header_label", "100", $colors["header"], 0, "center", "", true);
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
		$form_array[$field_name]["value"] = (isset($template_item) ? $template_item[$field_name] : "");
		$form_array[$field_name]["sub_checkbox"] = array(
			"name" => "t_" . $field_name,
			"friendly_name" => "Use Per-Data Source Value (Ignore this Value)",
			"value" => (isset($template_item) ? $template_item{"t_" . $field_name} : "")
			);
	}

	draw_edit_form(
		array(
			"config" => array("no_form_tag" => true),
			"fields" => $form_array + array(
				"data_template_rrd_id" => array(
					"method" => "hidden",
					"value" => (isset($template_item) ? $template_item["id"] : "0")
				)
			)
			)
		);

	html_end_box();

	form_hidden_box("data_template_item_id", (isset($template_item) ? $template_item["id"] : "0"), "");
	form_hidden_box("data_template_id", get_request_var("data_template_id"), "0");
	form_hidden_box("save_component_item", "1", "");
	form_hidden_box("hidden_rrdtool_version", read_config_option("rrdtool_version"), "");

	form_save_button_alt("url!" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""));

#	include_once(CACTI_BASE_PATH . "/access/js/graph_item_dependencies.js");	# this one modifies attr("disabled")
#	include_once(CACTI_BASE_PATH . "/access/js/line_width.js");
#	include_once(CACTI_BASE_PATH . "/access/js/rrdtool_version.js");			# this one sets attr("disabled) and comes last!

}
