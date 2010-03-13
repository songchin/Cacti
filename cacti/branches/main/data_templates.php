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

include ("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/tree.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/template.php");

define("MAX_DISPLAY_PAGES", 21);

$ds_template_actions = array(
	ACTION_NONE => __("None"),
	"1" => __("Delete"),
	"2" => __("Duplicate")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
	case 'save':
		data_source_template_save();

		break;
	case 'actions':
		data_source_template_form_actions();

		break;
	case 'rrd_add':
		template_rrd_add();

		break;
	case 'rrd_remove':
		template_rrd_remove();

		break;
	case 'template_remove':
		template_remove();

		header("Location: data_templates.php");
		break;
	case 'data_source_template_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_source_template_edit();

		include_once (CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_source_template();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function data_source_template_save() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	if (isset($_POST["save_component_template"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("data_input_id"));
		input_validate_input_number(get_request_var_post("data_template_id"));
		/* ==================================================== */

		/* save: data_template */
		$save1["id"] = $_POST["data_template_id"];
		$save1["hash"] = get_hash_data_template($_POST["data_template_id"]);
		$save1["name"] = form_input_validate($_POST["template_name"], "template_name", "", false, 3);
		$save1["description"] = form_input_validate($_POST["description"], "description", "", true, 3);

		/* save: data_template_data */
		$save2["id"] = $_POST["data_template_data_id"];
		$save2["local_data_template_data_id"] = 0;
		$save2["local_data_id"] = 0;

		$save2["data_input_id"] = form_input_validate($_POST["data_input_id"], "data_input_id", "", true, 3);
		$save2["t_name"] = form_input_validate((isset($_POST["t_name"]) ? $_POST["t_name"] : ""), "t_name", "", true, 3);
		$save2["name"] = form_input_validate($_POST["name"], "name", "", (isset($_POST["t_name"]) ? true : false), 3);
		$save2["t_active"] = form_input_validate((isset($_POST["t_active"]) ? $_POST["t_active"] : ""), "t_active", "", true, 3);
		$save2["active"] = form_input_validate((isset($_POST["active"]) ? $_POST["active"] : ""), "active", "", true, 3);
		$save2["t_rrd_step"] = form_input_validate((isset($_POST["t_rrd_step"]) ? $_POST["t_rrd_step"] : ""), "t_rrd_step", "", true, 3);
		$save2["rrd_step"] = form_input_validate($_POST["rrd_step"], "rrd_step", "^[0-9]+$", (isset($_POST["t_rrd_step"]) ? true : false), 3);
		$save2["t_rra_id"] = form_input_validate((isset($_POST["t_rra_id"]) ? $_POST["t_rra_id"] : ""), "t_rra_id", "", true, 3);

		/* save: data_template_rrd */
		$save3["id"] = $_POST["data_template_rrd_id"];
		$save3["hash"] = get_hash_data_template($_POST["data_template_rrd_id"], "data_template_item");
		$save3["local_data_template_rrd_id"] = 0;
		$save3["local_data_id"] = 0;

		$save3["t_rrd_maximum"] = form_input_validate((isset($_POST["t_rrd_maximum"]) ? $_POST["t_rrd_maximum"] : ""), "t_rrd_maximum", "", true, 3);
		$save3["rrd_maximum"] = form_input_validate($_POST["rrd_maximum"], "rrd_maximum", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", (isset($_POST["t_rrd_maximum"]) ? true : false), 3);
		$save3["t_rrd_minimum"] = form_input_validate((isset($_POST["t_rrd_minimum"]) ? $_POST["t_rrd_minimum"] : ""), "t_rrd_minimum", "", true, 3);
		$save3["rrd_minimum"] = form_input_validate($_POST["rrd_minimum"], "rrd_minimum", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", (isset($_POST["t_rrd_minimum"]) ? true : false), 3);
		$save3["t_rrd_compute_rpn"] = form_input_validate((isset($_POST["t_rrd_compute_rpn"]) ? $_POST["t_rrd_compute_rpn"] : ""), "t_rrd_compute_rpn", "", true, 3);
		/* rrd_compute_rpn requires input only for COMPUTE data source type */
		$save3["rrd_compute_rpn"] = form_input_validate($_POST["rrd_compute_rpn"], "rrd_compute_rpn", "", ((isset($_POST["t_rrd_compute_rpn"]) || ($_POST["data_source_type_id"] != DATA_SOURCE_TYPE_COMPUTE)) ? true : false), 3);
		$save3["t_rrd_heartbeat"] = form_input_validate((isset($_POST["t_rrd_heartbeat"]) ? $_POST["t_rrd_heartbeat"] : ""), "t_rrd_heartbeat", "", true, 3);
		$save3["rrd_heartbeat"] = form_input_validate($_POST["rrd_heartbeat"], "rrd_heartbeat", "^[0-9]+$", (isset($_POST["t_rrd_heartbeat"]) ? true : false), 3);
		$save3["t_data_source_type_id"] = form_input_validate((isset($_POST["t_data_source_type_id"]) ? $_POST["t_data_source_type_id"] : ""), "t_data_source_type_id", "", true, 3);
		$save3["data_source_type_id"] = form_input_validate($_POST["data_source_type_id"], "data_source_type_id", "", true, 3);
		$save3["t_data_source_name"] = form_input_validate((isset($_POST["t_data_source_name"]) ? $_POST["t_data_source_name"] : ""), "t_data_source_name", "", true, 3);
		$save3["data_source_name"] = form_input_validate($_POST["data_source_name"], "data_source_name", "^[a-zA-Z0-9_]{1,19}$", (isset($_POST["t_data_source_name"]) ? true : false), 3);
		$save3["t_data_input_field_id"] = form_input_validate((isset($_POST["t_data_input_field_id"]) ? $_POST["t_data_input_field_id"] : ""), "t_data_input_field_id", "", true, 3);
		$save3["data_input_field_id"] = form_input_validate((isset($_POST["data_input_field_id"]) ? $_POST["data_input_field_id"] : "0"), "data_input_field_id", "", true, 3);

		/* ok, first pull out all 'input' values so we know how much to save */
		$input_fields = db_fetch_assoc("select
			id,
			input_output,
			regexp_match,
			allow_nulls,
			type_code,
			data_name
			from data_input_fields
			where data_input_id=" . $_POST["data_input_id"] . "
			and input_output='in'");

		/* pass#1 for validation */
		if (sizeof($input_fields) > 0) {
			foreach ($input_fields as $input_field) {
				$form_value = "value_" . $input_field["data_name"];

				if ((isset($_POST[$form_value])) && ($input_field["type_code"] == "")) {
					if ((isset($_POST["t_" . $form_value])) &&
						(get_request_var_post("t_" . $form_value) == CHECKED)) {
						$not_required = true;
					}else if ($input_field["allow_nulls"] == CHECKED) {
						$not_required = true;
					}else{
						$not_required = false;
					}

					form_input_validate(get_request_var_post($form_value), "value_" . $input_field["data_name"], $input_field["regexp_match"], $not_required, 3);
				}
			}
		}

		if (!is_error_message()) {
			$data_template_id = sql_save($save1, "data_template");

			if ($data_template_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (!is_error_message()) {
			$save2["data_template_id"] = $data_template_id;
			$data_template_data_id = sql_save($save2, "data_template_data");

			if ($data_template_data_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		/* update actual device template information for live devices */
		if ((!is_error_message()) && ($save2["id"] > 0)) {
			db_execute("update data_template_data set data_input_id = '" . $_POST["data_input_id"] . "' where data_template_id = " . $_POST["data_template_id"] . ";");
		}

		if (!is_error_message()) {
			$save3["data_template_id"] = $data_template_id;
			$data_template_rrd_id = sql_save($save3, "data_template_rrd");

			if ($data_template_rrd_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (!is_error_message()) {
			/* save entries in 'selected rras' field */
			db_execute("delete from data_template_data_rra where data_template_data_id=$data_template_data_id");

			if (isset($_POST["rra_id"])) {
				for ($i=0; ($i < count($_POST["rra_id"])); $i++) {
					/* ================= input validation ================= */
					input_validate_input_number($_POST["rra_id"][$i]);
					/* ==================================================== */

					db_execute("insert into data_template_data_rra (rra_id,data_template_data_id)
						values (" . $_POST["rra_id"][$i] . ",$data_template_data_id)");
				}
			}

			if (!empty($_POST["data_template_id"])) {
				/* push out all data source settings to child data source using this template */
				push_out_data_source($data_template_data_id);
				push_out_data_source_item($data_template_rrd_id);

				db_execute("delete from data_input_data where data_template_data_id=$data_template_data_id");

				reset($input_fields);
				if (sizeof($input_fields) > 0) {
				foreach ($input_fields as $input_field) {
					$form_value = "value_" . $input_field["data_name"];

					if (isset($_POST[$form_value])) {
						/* save the data into the 'device_template_data' table */
						if (isset($_POST{"t_value_" . $input_field["data_name"]})) {
							$template_this_item = CHECKED;
						}else{
							$template_this_item = "";
						}

						if ((!empty($form_value)) || (!empty($_POST{"t_value_" . $input_field["data_name"]}))) {
							db_execute("insert into data_input_data (data_input_field_id,data_template_data_id,t_value,value)
								values (" . $input_field["id"] . ",$data_template_data_id,'$template_this_item','" . trim(get_request_var_post($form_value)) . "')");
						}
					}
				}
				}

				/* push out all "custom data" for this data source template */
				push_out_data_source_custom_data($data_template_id);
				push_out_device(0, 0, $data_template_id);
			}
		}

		header("Location: data_templates.php?action=data_source_template_edit&id=" . (empty($data_template_id) ? $_POST["data_template_id"] : $data_template_id) . (empty($_POST["current_rrd"]) ? "" : "&view_rrd=" . ($_POST["current_rrd"] ? $_POST["current_rrd"] : $data_template_rrd_id)));
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function data_source_template_form_actions() {
	global $colors, $ds_template_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") === "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $template_id) {
				/* ================= input validation ================= */
				input_validate_input_number($template_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM data_template_data WHERE data_template_id=$template_id LIMIT 1"))) {
					$bad_ids[] = $template_id;
				}else{
					$template_ids[] = $template_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $template_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>Data Source Template " . $template_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_dt_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('dt_ref_int');
			}

			if (isset($template_ids)) {
				$data_template_datas = db_fetch_assoc("select id from data_template_data where " . array_to_sql_or($template_ids, "data_template_id") . " and local_data_id=0");

				if (sizeof($data_template_datas) > 0) {
				foreach ($data_template_datas as $data_template_data) {
					db_execute("delete from data_template_data_rra where data_template_data_id=" . $data_template_data["id"]);
				}
				}

				db_execute("delete from data_template_data where " . array_to_sql_or($template_ids, "data_template_id") . " and local_data_id=0");
				db_execute("delete from data_template_rrd where " . array_to_sql_or($template_ids, "data_template_id") . " and local_data_id=0");
				db_execute("delete from snmp_query_graph_rrd where " . array_to_sql_or($template_ids, "data_template_id"));
				db_execute("delete from snmp_query_graph_rrd_sv where " . array_to_sql_or($template_ids, "data_template_id"));
				db_execute("delete from data_template where " . array_to_sql_or($template_ids, "id"));

				/* "undo" any graph that is currently using this template */
				db_execute("update data_template_data set local_data_template_data_id=0,data_template_id=0 where " . array_to_sql_or($template_ids, "data_template_id"));
				db_execute("update data_template_rrd set local_data_template_rrd_id=0,data_template_id=0 where " . array_to_sql_or($template_ids, "data_template_id"));
				db_execute("update data_local set data_template_id=0 where " . array_to_sql_or($template_ids, "data_template_id"));
			}
		}elseif (get_request_var_post("drp_action") === "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_data_source(0, $selected_items[$i], get_request_var_post("title_format"));
			}
		}

		header("Location: data_templates.php");
		exit;
	}

	/* setup some variables */
	$ds_list = ""; $i = 0; $ds_array = array();

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$ds_list .= "<li>" . db_fetch_cell("select name from data_template where id=" . $matches[1]) . "<br>";
			$ds_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $ds_template_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='data_templates.php' method='post'>\n";

	if (sizeof($ds_array)) {
		if (get_request_var_post("drp_action") === ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") === "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following data templates? Any data sources attached to these templates will become individual data sources.") . "</p>
						<p>$ds_list</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === "2") { /* duplicate */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following data templates will be duplicated. You can optionally change the title format for the new data templates.") . "</p>
						<p>$ds_list</p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Data Source Template.  Please select 'Return' to return to the previous menu.") . "</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($ds_array) || get_request_var_post("drp_action") === ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($ds_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ----------------------------
    template - Data Source Templates
   ---------------------------- */

function template_rrd_add() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("local_data_id"));
	/* ==================================================== */

	$hash = get_hash_data_template(0, "data_template_item");

	db_execute("insert into data_template_rrd (hash,data_template_id,rrd_maximum,rrd_minimum,rrd_heartbeat,data_source_type_id,
		data_source_name) values ('$hash'," . get_request_var("id") . ",100,0,600,1,'ds')");
	$data_template_rrd_id = db_fetch_insert_id();

	/* add this data template item to each data source using this data template */
	$children = db_fetch_assoc("select local_data_id from data_template_data where data_template_id=" . $_GET["id"] . " and local_data_id>0");

	if (sizeof($children) > 0) {
	foreach ($children as $item) {
		db_execute("insert into data_template_rrd (local_data_template_rrd_id,local_data_id,data_template_id,rrd_maximum,rrd_minimum,rrd_heartbeat,data_source_type_id,
			data_source_name) values ($data_template_rrd_id," . $item["local_data_id"] . "," . get_request_var("id") . ",100,0,600,1,'ds')");
	}
	}

	header("Location: data_templates.php?action=data_source_template_edit&id=" . $_GET["id"] . "&view_rrd=$data_template_rrd_id");
	exit;
}



function data_source_template_item() {
	global $colors;
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$template = db_fetch_row("SELECT * FROM data_template WHERE id=" . $_GET["id"]);
		$template_item_list = db_fetch_assoc("SELECT * FROM data_template_rrd WHERE data_template_id=" . $_GET["id"] . " AND local_data_id=0 ORDER BY data_source_name");
		$header_label = __("[edit: ") . $template["name"] . "]";
	}else{
		$template_item_list = array();
		$header_label = __("[new]");
	}

	html_start_box("<strong>" . __("Data Source Items") . "</strong> $header_label", "100", $colors["header"], "0", "center", "data_templates_items.php?action=item_edit&data_template_id=" . $_GET["id"], true);
	draw_data_template_items_list($template_item_list, "data_templates_items.php", "data_template_id=" . $_GET["id"], false);
	html_end_box(false);

}




function data_source_template_edit() {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("view_rrd"));
	/* ==================================================== */

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='data_data_source_template_edit'>\n";

	/* graph item list goes here */
	if (!empty($_GET["id"])) {
		data_source_template_item();
	}

	if (!empty($_GET["id"])) {
		$template = db_fetch_row("SELECT * FROM data_template WHERE id=" . $_GET["id"]);
		$template_data = db_fetch_row("SELECT * FROM data_template_data WHERE data_template_id=" . $_GET["id"] . " AND local_data_id=0");
		$header_label = __("[edit: ") . $template["name"] . "]";
	}else{
		$header_label = __("[new]");
	}


	# the template header
	html_start_box("<strong>" . __("Data Source Template") . "</strong> $header_label", "100", $colors["header"], 0, "center", "", true);
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_data_template');

	draw_edit_form(array(
		"config" => array("no_form_tag" => true),
		"fields" => inject_form_variables(data_template_form_list(), (isset($template) ? $template : array()), (isset($template_data) ? $template_data : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box(false);
	form_hidden_box("data_template_id", $template_data["data_template_id"], "0");
	form_hidden_box("data_template_data_id", $template_data["id"], "0");
	form_hidden_box("current_rrd", (isset($_GET["current_rrd"]) ? $_GET["current_rrd"] : "0"), "0");
	form_hidden_box("save_component_template", 1, "");


	html_start_box("<strong>" . __("Data Source") . "</strong>", "100", $colors["header"], 0, "center", "", true);
	draw_template_edit_form('header_data_source', data_source_form_list(), $template_data, false);
	html_end_box();


	$i = 0;
	if (!empty($_GET["id"])) {
		/* get each INPUT field for this data input source */
		$fields = db_fetch_assoc("SELECT * FROM data_input_fields WHERE data_input_id=" . $template_data["data_input_id"] . " AND input_output='in' ORDER BY sequence");

		html_start_box("<strong>" . __("Custom Data") . "</strong> [data input: " . db_fetch_cell("SELECT name FROM data_input WHERE id=" . $template_data["data_input_id"]) . "]", "100", $colors["header"], 0, "center", "", true);
		$header_items = array(__("Field"), __("Value"));
		print "<tr><td>";
		html_header($header_items, 2, true, 'data_source_custom_data');

		/* loop through each field found */
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			$data_input_data = db_fetch_row("SELECT t_value,value FROM data_input_data WHERE data_template_data_id=" . $template_data["id"] . " AND data_input_field_id=" . $field["id"]);

			if (sizeof($data_input_data) > 0) {
				$old_value = $data_input_data["value"];
			}else{
				$old_value = "";
			}

			form_alternate_row_color("custom_data" . $field["id"]); ?>
				<td class='template_checkbox'>
					<strong><?php print $field["name"];?></strong><br>
					<?php form_checkbox("t_value_" . $field["data_name"], $data_input_data["t_value"], "<em>Use Per-Data Source Value (Ignore this Value)</em>", "", "", get_request_var("id"));?>
				</td>
				<td>
					<?php form_text_box("value_" . $field["data_name"],$old_value,"","");?>
					<?php if ((preg_match('/^' . VALID_HOST_FIELDS . '$/i', $field["type_code"])) && ($data_input_data["t_value"] == "")) { print "<br><em>Value will be derived from the device if this field is left empty.</em>\n"; } ?>
				</td>
			<?php
			form_end_row();
		}
		}else{
			print "<tr><td><em>" . __("No Input Fields for the Selected Data Input Source") . "</em></td></tr>";
		}

		print "</table></td></tr>";		/* end of html_header */
		html_end_box(false);
	}

	form_save_button_alt("return");

	include_once(CACTI_BASE_PATH . "/access/js/data_source_item.js");
	include_once(CACTI_BASE_PATH . "/access/js/field_description_hover.js");
}

function data_source_template() {
	global $colors, $ds_template_actions, $item_rows;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column string */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_data_template_current_page");
		kill_session_var("sess_data_template_rows");
		kill_session_var("sess_data_template_filter");
		kill_session_var("sess_data_template_sort_column");
		kill_session_var("sess_data_template_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	?>
	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?rows=' + objForm.rows.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_data_template_current_page", "1");
	load_current_session_value("rows", "sess_data_template_rows", "-1");
	load_current_session_value("filter", "sess_data_template_filter", "");
	load_current_session_value("sort_column", "sess_data_template_sort_column", "name");
	load_current_session_value("sort_direction", "sess_data_template_sort_direction", "ASC");

	html_start_box("<strong>Data Source Templates</strong>", "100", $colors["header"], "3", "center", "data_templates.php?action=data_source_template_edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_data_template" action="data_templates.php">
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyFilterChange(document.form_data_template)">
							<option value="-1"<?php if (get_request_var_request("rows") == "-1") {?> selected<?php }?>>Default</option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if (get_request_var_request("rows") == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if ($_REQUEST["filter"] != "") {
		$sql_where = "WHERE ((data_template.name like '%%" . $_REQUEST["filter"] . "%%')
			OR (data_template.description LIKE '%%" . get_request_var_request("filter") . "%%'))
			AND data_template_data.local_data_id = 0";
	}else{
		$sql_where = "WHERE data_template_data.local_data_id = 0";
	}

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT " .
		"COUNT(data_template.id) " .
		"FROM data_template " .
		"LEFT JOIN data_template_data ON (data_template.id = data_template_data.data_template_id) " .
		$sql_where);

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$template_list = db_fetch_assoc("SELECT " .
		"data_template.id, " .
		"data_template.name, " .
		"data_template.description, " .
		"data_input.name AS data_input_method, " .
		"data_template_data.active AS active " .
		"FROM data_template " .
		"LEFT JOIN data_template_data ON (data_template.id = data_template_data.data_template_id) " .
		"LEFT JOIN data_input ON (data_template_data.data_input_id = data_input.id) " .
		$sql_where .
		" ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "data_templates.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name" => array(__("Template Name"), "ASC"),
		"description" => array(__("Description"), "ASC"),
		"data_input_method" => array(__("Data Input Method"), "ASC"),
		"active" => array(__("Status"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color('line' . $template["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("data_templates.php?action=data_source_template_edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template["name"]) : $template["name"]) . "</a>", $template["id"]);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("data_templates.php?action=data_source_template_edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template["description"]) : $template["description"]) . "</a>", $template["id"]);
			form_selectable_cell((empty($template["data_input_method"]) ? "<em>" . __("None") . "</em>": $template["data_input_method"]), $template["id"]);
			form_selectable_cell((($template["active"] == CHECKED) ? __("Active") : __("Disabled")), $template["id"]);
			form_checkbox_cell($template["name"], $template["id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>" . __("No Data Source Templates") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_template_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
