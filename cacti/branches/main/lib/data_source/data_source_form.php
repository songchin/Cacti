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

/* --------------------------
    The Save Function
   -------------------------- */

function data_source_form_save() {
	if ((isset($_POST["save_component_data_source_new"])) && (!empty($_POST["data_template_id"]))) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("device_id"));
		input_validate_input_number(get_request_var_post("data_template_id"));
		/* ==================================================== */

		$save["id"] = $_POST["local_data_id"];
		$save["data_template_id"] = $_POST["data_template_id"];
		$save["device_id"] = $_POST["device_id"];

		$local_data_id = sql_save($save, "data_local");

		change_data_template($local_data_id, get_request_var_post("data_template_id"));

		/* update the title cache */
		update_data_source_title_cache($local_data_id);

		/* update device data */
		if (!empty($_POST["device_id"])) {
			push_out_device(get_request_var_post("device_id"), $local_data_id);
		}
	}

	if ((isset($_POST["save_component_data"])) && (!is_error_message())) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("data_template_data_id"));
		/* ==================================================== */

		/* ok, first pull out all 'input' values so we know how much to save */
		$input_fields = db_fetch_assoc("select
			data_template_data.data_input_id,
			data_local.device_id,
			data_input_fields.id,
			data_input_fields.input_output,
			data_input_fields.data_name,
			data_input_fields.regexp_match,
			data_input_fields.allow_nulls,
			data_input_fields.type_code
			from data_template_data
			left join data_input_fields on (data_input_fields.data_input_id=data_template_data.data_input_id)
			left join data_local on (data_template_data.local_data_id=data_local.id)
			where data_template_data.id=" . $_POST["data_template_data_id"] . "
			and data_input_fields.input_output='in'");

		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			if (isset($_POST{"value_" . $input_field["id"]})) {
				/* save the data into the 'data_input_data' table */
				$form_value = $_POST{"value_" . $input_field["id"]};

				/* we shouldn't enforce rules on fields the user cannot see (ie. templated ones) */
				$is_templated = db_fetch_cell("select t_value from data_input_data where data_input_field_id=" . $input_field["id"] . " and data_template_data_id=" . db_fetch_cell("select local_data_template_data_id from data_template_data where id=" . $_POST["data_template_data_id"]));

				if ($is_templated == "") {
					$allow_nulls = true;
				}elseif ($input_field["allow_nulls"] == CHECKED) {
					$allow_nulls = true;
				}elseif (empty($input_field["allow_nulls"])) {
					$allow_nulls = false;
				}

				/* run regexp match on input string */
				$form_value = form_input_validate($form_value, "value_" . $input_field["id"], $input_field["regexp_match"], $allow_nulls, 3);

				if (!is_error_message()) {
					db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values
						(" . $input_field["id"] . "," . get_request_var_post("data_template_data_id") . ",'','$form_value')");
				}
			}
		}
		}
	}

	if ((isset($_POST["save_component_data_source"])) && (!is_error_message())) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("local_data_id"));
		input_validate_input_number(get_request_var_post("current_rrd"));
		input_validate_input_number(get_request_var_post("data_template_id"));
		input_validate_input_number(get_request_var_post("device_id"));
		/* ==================================================== */

		$save1["id"] = $_POST["local_data_id"];
		$save1["data_template_id"] = $_POST["data_template_id"];
		$save1["device_id"] = $_POST["device_id"];

		$save2["id"] = $_POST["data_template_data_id"];
		$save2["local_data_template_data_id"] = $_POST["local_data_template_data_id"];
		$save2["data_template_id"] = $_POST["data_template_id"];
		$save2["data_input_id"] = form_input_validate($_POST["data_input_id"], "data_input_id", "", true, 3);
		$save2["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save2["data_source_path"] = form_input_validate($_POST["data_source_path"], "data_source_path", "", true, 3);
		$save2["active"] = form_input_validate((isset($_POST["active"]) ? $_POST["active"] : ""), "active", "", true, 3);
		$save2["rrd_step"] = form_input_validate($_POST["rrd_step"], "rrd_step", "^[0-9]+$", false, 3);

		if (!is_error_message()) {
			$local_data_id = sql_save($save1, "data_local");

			$save2["local_data_id"] = $local_data_id;
			$data_template_data_id = sql_save($save2, "data_template_data");

			if ($data_template_data_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

#		if (!is_error_message()) {
#			/* if this is a new data source and a template has been selected, skip item creation this time
#			otherwise it throws off the templatate creation because of the NULL data */
#			if ((!empty($_POST["local_data_id"])) || (empty($_POST["data_template_id"]))) {
#				/* if no template was set before the save, there will be only one data source item to save;
#				otherwise there might be >1 */
#				if (empty($_POST["hidden_data_template_id"])) {
#					$rrds[0]["id"] = $_POST["current_rrd"];
#				}else{
#					$rrds = db_fetch_assoc("select id from data_template_rrd where local_data_id=" . $_POST["local_data_id"]);
#				}
#
#				if (sizeof($rrds) > 0) {
#				foreach ($rrds as $rrd) {
#					if (empty($_POST["hidden_data_template_id"])) {
#						$name_modifier = "";
#					}else{
#						$name_modifier = "_" . $rrd["id"];
#					}
#
#					$save3["id"] = $rrd["id"];
#					$save3["local_data_id"] = $local_data_id;
#					$save3["local_data_template_rrd_id"] = db_fetch_cell("select local_data_template_rrd_id from data_template_rrd where id=" . $rrd["id"]);
#					$save3["data_template_id"] = $_POST["data_template_id"];
#					$save3["rrd_maximum"] = form_input_validate($_POST["rrd_maximum$name_modifier"], "rrd_maximum$name_modifier", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", false, 3);
#					$save3["rrd_minimum"] = form_input_validate($_POST["rrd_minimum$name_modifier"], "rrd_minimum$name_modifier", "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$", false, 3);
#					$save3["rrd_heartbeat"] = form_input_validate($_POST["rrd_heartbeat$name_modifier"], "rrd_heartbeat$name_modifier", "^[0-9]+$", false, 3);
#					$save3["data_source_type_id"] = $_POST["data_source_type_id$name_modifier"];
#					$save3["data_source_name"] = form_input_validate($_POST["data_source_name$name_modifier"], "data_source_name$name_modifier", "^[a-zA-Z0-9_-]{1,19}$", false, 3);
#					$save3["data_input_field_id"] = form_input_validate((isset($_POST["data_input_field_id$name_modifier"]) ? $_POST["data_input_field_id$name_modifier"] : "0"), "data_input_field_id$name_modifier", "", true, 3);
#
#					$data_template_rrd_id = sql_save($save3, "data_template_rrd");
#
#					if ($data_template_rrd_id) {
#						raise_message(1);
#					}else{
#						raise_message(2);
#					}
#				}
#				}
#			}
#		}

		if (!is_error_message()) {
			if (!empty($_POST["rra_id"])) {
				/* save entries in 'selected rras' field */
				db_execute("delete from data_template_data_rra where data_template_data_id=$data_template_data_id");

				for ($i=0; ($i < count($_POST["rra_id"])); $i++) {
					/* ================= input validation ================= */
					input_validate_input_number($_POST["rra_id"][$i]);
					/* ==================================================== */

					db_execute("insert into data_template_data_rra (rra_id,data_template_data_id)
						values (" . $_POST["rra_id"][$i] . ",$data_template_data_id)");
				}
			}

			if ($_POST["data_template_id"] != $_POST["hidden_data_template_id"]) {
				/* update all necessary template information */
				change_data_template($local_data_id, get_request_var_post("data_template_id"));
			}elseif (!empty($_POST["data_template_id"])) {
				update_data_source_data_query_cache($local_data_id);
			}

			if ($_POST["device_id"] != $_POST["hidden_device_id"]) {
				/* push out all necessary device information */
				push_out_device(get_request_var_post("device_id"), $local_data_id);

				/* reset current device for display purposes */
				$_SESSION["sess_data_source_currenthidden_device_id"] = $_POST["device_id"];
			}

			/* if no data source path has been entered, generate one */
			if (empty($_POST["data_source_path"])) {
				generate_data_source_path($local_data_id);
			}

			/* update the title cache */
			update_data_source_title_cache($local_data_id);
		}
	}

	/* update the poller cache last to make sure everything is fresh */
	if ((!is_error_message()) && (!empty($local_data_id))) {
		update_poller_cache($local_data_id, true);
	}

	if ((isset($_POST["save_component_data_source_new"])) && (empty($_POST["data_template_id"]))) {
		header("Location: data_sources.php?action=data_source_edit&device_id=" . $_POST["device_id"] . "&new=1");
	}elseif ((is_error_message()) || ($_POST["data_template_id"] != $_POST["hidden_data_template_id"]) || ($_POST["data_input_id"] != $_POST["hidden_data_input_id"]) || ($_POST["device_id"] != $_POST["hidden_device_id"])) {
		header("Location: data_sources.php?action=data_source_edit&id=" . (empty($local_data_id) ? $_POST["local_data_id"] : $local_data_id) . "&device_id=" . $_POST["device_id"] . "&view_rrd=" . (isset($_POST["current_rrd"]) ? $_POST["current_rrd"] : "0"));
	}else{
		header("Location: data_sources.php");
	}
	exit;
}

/* ------------------------
    The "actions" function
   ------------------------ */

function data_source_form_actions() {
	global $colors;
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") === DS_ACTION_DELETE) { /* delete */
			if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 1; }

			switch (get_request_var_post("delete_type")) {
				case '2': /* delete all graph items tied to this data source */
					$data_template_rrds = db_fetch_assoc("select id from data_template_rrd where " . array_to_sql_or($selected_items, "local_data_id"));

					/* loop through each data source item */
					if (sizeof($data_template_rrds) > 0) {
						foreach ($data_template_rrds as $item) {
							db_execute("delete from graph_templates_item where task_item_id=" . $item["id"] . " and local_graph_id > 0");
						}
					}

					break;
				case '3': /* delete all graphs tied to this data source */
					$graphs = db_fetch_assoc("select
						graph_templates_graph.local_graph_id
						from (data_template_rrd,graph_templates_item,graph_templates_graph)
						where graph_templates_item.task_item_id=data_template_rrd.id
						and graph_templates_item.local_graph_id=graph_templates_graph.local_graph_id
						and " . array_to_sql_or($selected_items, "data_template_rrd.local_data_id") . "
						and graph_templates_graph.local_graph_id > 0
						group by graph_templates_graph.local_graph_id");

					if (sizeof($graphs) > 0) {
						foreach ($graphs as $graph) {
							api_graph_remove($graph["local_graph_id"]);
						}
					}

					break;
				}

				for ($i=0;($i<count($selected_items));$i++) {
					/* ================= input validation ================= */
					input_validate_input_number($selected_items[$i]);
					/* ==================================================== */

					api_data_source_remove($selected_items[$i]);
				}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CHANGE_TEMPLATE) { /* change graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("data_template_id"));
				/* ==================================================== */

				change_data_template($selected_items[$i], get_request_var_post("data_template_id"));
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CHANGE_HOST) { /* change device */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("device_id"));
				/* ==================================================== */

				db_execute("update data_local set device_id=" . $_POST["device_id"] . " where id=" . $selected_items[$i]);
				push_out_device(get_request_var_post("device_id"), $selected_items[$i]);
				update_data_source_title_cache($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_DUPLICATE) { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_data_source($selected_items[$i], 0, get_request_var_post("title_format"));
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CONVERT_TO_TEMPLATE) { /* data source -> data template */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				data_source_to_data_template($selected_items[$i], get_request_var_post("title_format"));
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_ENABLE) { /* data source enable */
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_source_enable($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_DISABLE) { /* data source disable */
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_source_disable($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") === DS_ACTION_REAPPLY_SUGGESTED_NAMES) { /* reapply suggested data source naming */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */
				api_reapply_suggested_data_source_title($selected_items[$i]);
				update_data_source_title_cache($selected_items[$i]);
			}
		}

		header("Location: data_sources.php");
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

			$ds_list .= "<li>" . get_data_source_title($matches[1]) . "<br>";
			$ds_array[$i++] = $matches[1];
		}
	}

	$ds_actions[ACTION_NONE] = __("None");

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $ds_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='data_sources.php' method='post'>\n";

	if (sizeof($ds_array)) {
		if (get_request_var_post("drp_action") === ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_DELETE) { /* delete */
			$graphs = array();

			/* find out which (if any) graphs are using this data source, so we can tell the user */
			if (isset($ds_array)) {
				$graphs = db_fetch_assoc("select
					graph_templates_graph.local_graph_id,
					graph_templates_graph.title_cache
					from (data_template_rrd,graph_templates_item,graph_templates_graph)
					where graph_templates_item.task_item_id=data_template_rrd.id
					and graph_templates_item.local_graph_id=graph_templates_graph.local_graph_id
					and " . array_to_sql_or($ds_array, "data_template_rrd.local_data_id") . "
					and graph_templates_graph.local_graph_id > 0
					group by graph_templates_graph.local_graph_id
					order by graph_templates_graph.title_cache");
			}

			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following data sources?") . "</p>
						<p><ul>$ds_list</ul></p>
						";
						if (sizeof($graphs) > 0) {
							form_alternate_row_color();

							print "<td class='textArea'><p class='textArea'>" . __("The following graphs are using these data sources:") . "</p>\n";

							foreach ($graphs as $graph) {
								print "<strong>" . $graph["title_cache"] . "</strong><br>\n";
							}

							print "<br>";
							form_radio_button("delete_type", "3", "1", __("Leave the graphs untouched."), "1"); print "<br>";
							form_radio_button("delete_type", "3", "2", __("Delete all <strong>graph items</strong> that reference these data sources."), "1"); print "<br>";
							form_radio_button("delete_type", "3", "3", __("Delete all <strong>graphs</strong> that reference these data sources."), "1"); print "<br>";
							print "</td></tr>";
						}
					print "
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CHANGE_TEMPLATE) { /* change graph template */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Choose a data template and click save to change the data template for the following data souces. Be aware that all warnings will be suppressed during the conversion, so graph data loss is possible.") . "</p>
						<p><ul>$ds_list</ul></p>
						<p><strong>". __("New Data Source Template:") . "</strong><br>"; form_dropdown("data_template_id",db_fetch_assoc("select data_template.id,data_template.name from data_template order by data_template.name"),"name","id","","","0"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CHANGE_HOST) { /* change device */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Choose a new device for these data sources.") . "</p>
						<p><ul>$ds_list</ul></p>
						<p><strong>" . __("New Host:") . "</strong><br>"; form_dropdown("device_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from device order by description,hostname"),"name","id","","","0"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_DUPLICATE) { /* duplicate */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following data sources will be duplicated. You can optionally change the title format for the new data sources.") . "</p>
						<p><ul>$ds_list</ul></p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_CONVERT_TO_TEMPLATE) { /* data source -> data template */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following data sources will be converted into data templates.  You can optionally change the title format for the new data templates.") . "</p>
						<p><ul>$ds_list</ul></p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> Template", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_ENABLE) { /* data source enable */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click yes, the following data sources will be enabled.") . "</p>
						<p><ul>$ds_list</ul></p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_DISABLE) { /* data source disable */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click yes, the following data sources will be disabled.") . "</p>
						<p><ul>$ds_list</ul></p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") === DS_ACTION_REAPPLY_SUGGESTED_NAMES) { /* reapply suggested data source naming */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("When you click yes, the following data sources will will have their suggested naming conventions recalculated.") . "</p>
						<p><ul>$ds_list</ul></p>
					</td>
				</tr>\n
				";
			}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Data Source.  Please select 'Return' to return to the previous menu.") . "</p>
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
    data - Custom Data
   ---------------------------- */

function data_source_toggle_status() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (get_request_var("newstate") == 1) {
		api_data_source_enable(get_request_var("id"));
	}else{
		cacti_log("Disabling Bad DS");
		api_data_source_disable(get_request_var("id"));
	}

	header("Location: " . $_SERVER["HTTP_REFERER"]);
	exit;
}

function data_source_data_edit() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	global $config, $colors;

	if (!empty($_GET["id"])) {
		$data = db_fetch_row("select id,data_input_id,data_template_id,name,local_data_id from data_template_data where local_data_id=" . $_GET["id"]);
		$template_data = db_fetch_row("select id,data_input_id from data_template_data where data_template_id=" . $data["data_template_id"] . " and local_data_id=0");

		$device = db_fetch_row("select device.id,device.hostname from (data_local,device) where data_local.device_id=device.id and data_local.id=" . $_GET["id"]);

		$header_label = __("[edit: ") . $data["name"] . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form action='data_sources.php' method='post'>\n";

	$i = 0;
	if (!empty($data["data_input_id"])) {
		/* get each INPUT field for this data input source */
		$fields = db_fetch_assoc("select * from data_input_fields where data_input_id=" . $data["data_input_id"] . " and input_output='in' order by sequence");

		html_start_box("<strong>" . __("Custom Data") . "</strong> " . __("[data input:") . " " . db_fetch_cell("select name from data_input where id=" . $data["data_input_id"]) . "]", "100", $colors["header"], "3", "center", "");

		/* loop through each field found */
		if (sizeof($fields) > 0) {
			foreach ($fields as $field) {
				$data_input_data = db_fetch_row("select * from data_input_data where data_template_data_id=" . $data["id"] . " and data_input_field_id=" . $field["id"]);

				if (sizeof($data_input_data) > 0) {
					$old_value = $data_input_data["value"];
				}else{
					$old_value = "";
				}

				/* if data template then get t_value from template, else always allow user input */
				if (empty($data["data_template_id"])) {
					$can_template = CHECKED;
				}else{
					$can_template = db_fetch_cell("select t_value from data_input_data where data_template_data_id=" . $template_data["id"] . " and data_input_field_id=" . $field["id"]);
				}

				form_alternate_row_color();

				if ((!empty($device["id"])) && (preg_match('/^' . VALID_HOST_FIELDS . '$/i', $field["type_code"]))) {
					print "<td width='50%'><strong>" . $field["name"] . "</strong> (" . __("From Host:") . " " . $device["hostname"] . ")</td>\n";
					print "<td><em>$old_value</em></td>\n";
				}elseif (empty($can_template)) {
					print "<td width='50%'><strong>" . $field["name"] . "</strong> (" . __("From Data Source Template") . ")</td>\n";
					print "<td><em>" . (empty($old_value) ? __("Nothing Entered") : $old_value) . "</em></td>\n";
				}else{
					print "<td width='50%'><strong>" . $field["name"] . "</strong></td>\n";
					print "<td>";

					draw_custom_data_row("value_" . $field["id"], $field["id"], $data["id"], $old_value);

					print "</td>";
				}

				print "</tr>\n";
			}
		}else{
			print "<tr><td><em>" . __("No Input Fields for the Selected Data Input Source") . "</em></td></tr>";
		}

		html_end_box();
	}

	form_hidden_box("local_data_id", (isset($data) ? $data["local_data_id"] : "0"), "");
	form_hidden_box("data_template_data_id", (isset($data) ? $data["id"] : "0"), "");
	form_hidden_box("save_component_data", "1", "");
}

/* ------------------------
    Data Source Functions
   ------------------------ */

function data_source_rrd_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	db_execute("delete from data_template_rrd where id=" . $_GET["id"]);
	db_execute("update graph_templates_item set task_item_id=0 where task_item_id=" . $_GET["id"]);

	header("Location: data_sources.php?action=data_source_edit&id=" . $_GET["local_data_id"]);
	exit;
}

function data_source_rrd_add() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	db_execute("insert into data_template_rrd (local_data_id,rrd_maximum,rrd_minimum,rrd_heartbeat,data_source_type_id,
		data_source_name) values (" . get_request_var("id") . ",100,0,600,1,'ds')");
	$data_template_rrd_id = db_fetch_insert_id();

	header("Location: data_sources.php?action=data_source_edit&id=" . $_GET["id"] . "&view_rrd=$data_template_rrd_id");
	exit;
}

function data_source_edit() {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$use_data_template = true;
	$device_id = 0;

	if (!empty($_GET["id"])) {
		$data_local 		= db_fetch_row("select device_id,data_template_id from data_local where id='" . $_GET["id"] . "'");
		$data       		= db_fetch_row("select * from data_template_data where local_data_id='" . $_GET["id"] . "'");
		$data_source_items 	= db_fetch_assoc("select * from data_template_rrd where local_data_id=" . $_GET["id"] . " order by data_source_name");

		if (isset($data_local["data_template_id"]) && $data_local["data_template_id"] >= 0) {
			$data_template      = db_fetch_row("select id,name from data_template where id='" . $data_local["data_template_id"] . "'");
			$data_template_data = db_fetch_row("select * from data_template_data where data_template_id='" . $data_local["data_template_id"] . "' and local_data_id=0");
		} else {
			$_SESSION["sess_messages"] = 'Data Source "' . $_GET["id"] . '" does not exist.';
			header ("Location: data_sources.php");
			exit;
		}

		$header_label = __("[edit: ") . get_data_source_title($_GET["id"]) . "]";

		if (empty($data_local["data_template_id"])) {
			$use_data_template = false;
		}
	}else{
		$header_label = __("[new]");

		$use_data_template = false;
	}

	/* handle debug mode */
	if (isset($_GET["debug"])) {
		if (get_request_var("debug") == "0") {
			kill_session_var("ds_debug_mode");
		}elseif (get_request_var("debug") == "1") {
			$_SESSION["ds_debug_mode"] = true;
		}
	}

	/* handle info mode */
	if (isset($_GET["info"])) {
		if (get_request_var("info") == "0") {
			kill_session_var("ds_info_mode");
		}elseif (get_request_var("info") == "1") {
			$_SESSION["ds_info_mode"] = true;
		}
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	$tip_text = "";
	if (isset($data)) {
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' id=\\'changeDSState\\' onClick=\\'changeDSState()\\' href=\\'#\\'>Unlock/Lock</a></td></tr>";
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('data_sources.php?action=data_source_toggle_status&id=' . (isset($_GET["id"]) ? $_GET["id"] : 0) . '&newstate=' . (($data["active"] == CHECKED) ? "0" : "1")) . "\\'>" . (($data["active"] == CHECKED) ? __("Disable") : __("Enable")) . "</a></td></tr>";
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('data_sources.php?action=data_source_edit&id=' . (isset($_GET["id"]) ? $_GET["id"] : 0) . '&debug=' . (isset($_SESSION["ds_debug_mode"]) ? "0" : "1")) . "\\'>" . __("Turn") . " <strong>" . (isset($_SESSION["ds_debug_mode"]) ? __("Off") : __(CHECKED)) . "</strong> " . __("Debug Mode") . "</a></td></tr>";
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('data_sources.php?action=data_source_edit&id=' . (isset($_GET["id"]) ? $_GET["id"] : 0) . '&info=' . (isset($_SESSION["ds_info_mode"]) ? "0" : "1")) . "\\'>" . __("Turn") . " <strong>" . (isset($_SESSION["ds_info_mode"]) ? __("Off") : __(CHECKED)) . "</strong> " . __("RRD Info Mode") . "</a><td></tr>";
	}
	if (!empty($data_template["id"])) {
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('data_templates.php?action=template_edit&id=' . (isset($data_template["id"]) ? $data_template["id"] : "0")) . "\\'>" . __("Edit Data Source Template") . "<br></a></td></td>";
	}
	if (!empty($_GET["device_id"]) || !empty($data_local["device_id"])) {
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('devices.php?action=edit&id=' . (isset($_GET["device_id"]) ? $_GET["device_id"] : $data_local["device_id"])) . "\\'>" . __("Edit Host") . "</a></td></tr>";
	}

	if (!empty($_GET["id"])) {
		?>
		<script type="text/javascript">
		<!--
		var disabled = true;

		$().ready(function() {
			$("input").attr("disabled","disabled")
			$("select").attr("disabled","disabled")
			$("#cancel").removeAttr("disabled");
		});

		function changeDSState() {
			if (disabled) {
				$("input").removeAttr("disabled");
				$("select").removeAttr("disabled");
				$("#cancel").removeAttr("disabled");
				disabled = false;
			}else{
				$("input").attr("disabled","disabled")
				$("select").attr("disabled","disabled")
				disabled = true;
			}
		}
		-->
		</script>
		<table width="100%" align="center">
			<tr>
				<td class="textInfo" colspan="2" valign="top">
					<?php print get_data_source_title(get_request_var("id"));?>
				</td>
				<td style="white-space:nowrap;" align="right" class="w1"><a id='tooltip' class='popup_anchor' href='#' onMouseOver="Tip('<?php print $tip_text;?>', BGCOLOR, '#EEEEEE', FIX, ['tooltip', -20, 0], STICKY, true, SHADOW, true, CLICKCLOSE, true, FADEOUT, 400, TEXTALIGN, 'right', BORDERCOLOR, '#F5F5F5')" onMouseOut="UnTip()">Data Source Options</a></td>
			</tr>
		</table>
		<?php
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='data_source_edit'>\n";
	html_start_box("<strong>" . __("Data Source Template Selection") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'template');

	$form_array = fields_data_source_form_list();
	$form_array["data_template_id"]["id"] = (isset($data_template["id"]) ? $data_template["id"] : "0");
	$form_array["data_template_id"]["name"] = db_fetch_cell("SELECT name FROM data_template WHERE id=" . $form_array["data_template_id"]["id"]);
	$form_array["device_id"]["id"] = (isset($_GET["device_id"]) ? $_GET["device_id"] : $data_local["device_id"]);
	$form_array["device_id"]["name"] = db_fetch_cell("SELECT CONCAT_WS('',description,' (',hostname,')') FROM device WHERE id=" . $form_array["device_id"]["id"]);

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();
	form_hidden_box("hidden_data_template_id", (isset($data_template["id"]) ? $data_template["id"] : "0"), "");
	form_hidden_box("hidden_device_id", (empty($data_local["device_id"]) ? (isset($_GET["device_id"]) ? $_GET["device_id"] : "0") : $data_local["device_id"]), "");
	form_hidden_box("hidden_data_input_id", (isset($data["data_input_id"]) ? $data["data_input_id"] : "0"), "");
	form_hidden_box("data_template_data_id", (isset($data) ? $data["id"] : "0"), "");
	form_hidden_box("local_data_template_data_id", (isset($data) ? $data["local_data_template_data_id"] : "0"), "");
	form_hidden_box("local_data_id", (isset($data) ? $data["local_data_id"] : "0"), "");

	/* only display the "inputs" area if we are using a data template for this data source */
	if (!empty($data["data_template_id"])) {

		html_start_box("<strong>" . __("Supplemental Data Source Template Data") . "</strong>", "100", $colors["header"], 0, "center", "");

		draw_nontemplated_fields_data_source($data["data_template_id"], $data["local_data_id"], $data, "|field|", "<strong>" . __("Data Source Fields") . "</strong>", true, true, 0);
		draw_nontemplated_fields_data_source_item($data["data_template_id"], $data_source_items, "|field|_|id|", "<strong>" . __("Data Source Item Fields") . "</strong>", true, true, true, 0);
		draw_nontemplated_fields_custom_data($data["id"], "value_|id|", "<strong>" . __("Custom Data") . "</strong>", true, true, 0);

		html_end_box();

		form_hidden_box("save_component_data","1","");
	}

	if (((isset($_GET["id"])) || (isset($_GET["new"]))) && (empty($data["data_template_id"]))) {
		html_start_box("<strong>" . __("Data Source") . "</strong>", "100", $colors["header"], "3", "center", "");

		$form_array = array();

		$struct_data_source = data_source_form_list();
		while (list($field_name, $field_array) = each($struct_data_source)) {
			$form_array += array($field_name => $struct_data_source[$field_name]);

			$form_array[$field_name]["value"] = (isset($data[$field_name]) ? $data[$field_name] : "");
			$form_array[$field_name]["form_id"] = (empty($data["id"]) ? "0" : $data["id"]);

			if (!(($use_data_template == false) || (!empty($data_template_data{"t_" . $field_name})) || ($field_array["flags"] == "NOTEMPLATE"))) {
				$form_array[$field_name]["method"] = "template_" . $form_array[$field_name]["method"];
			}
		}

		draw_edit_form(
			array(
				"config" => array("no_form_tag" => true),
				"fields" => inject_form_variables($form_array, (isset($data) ? $data : array()))
				)
			);

		html_end_box();


		if (!empty($_GET["id"])) {

			html_start_box("<strong>" . __("Data Source Items") . "</strong>", "100", $colors["header"], "0", "center", "data_sources_items.php?action=item_edit&local_data_id=" . $_GET["id"], true);
			draw_data_template_items_list($data_source_items, "data_sources_items.php", "local_data_id=" . $_GET["id"], $use_data_template);
			html_end_box(false);
		}

		/* data source data goes here */
		data_source_data_edit();
	}

	/* display the debug mode box if the user wants it */
	if ((isset($_SESSION["ds_debug_mode"])) && (isset($_GET["id"]))) {
		?>
		<table width="100%" align="center">
			<tr>
				<td>
					<span class="textInfo"><?php print __("Data Source Debug");?></span><br>
					<pre><?php print rrdtool_function_create(get_request_var("id"), true, array());?></pre>
				</td>
			</tr>
		</table>
		<?php
	}

	if ((isset($_SESSION["ds_info_mode"])) && (isset($_GET["id"]))) {
		$rrd_info = rrdtool_function_info($_GET["id"]);

		if (sizeof($rrd_info["rra"])) {
			$diff = rrdtool_cacti_compare($_GET["id"], $rrd_info);
			rrdtool_info2html($rrd_info, $diff);
			rrdtool_tune($rrd_info["filename"], $diff, true);
		}
	}

	if ((isset($_GET["id"])) || (isset($_GET["new"]))) {
		form_hidden_box("save_component_data_source","1","");
	}else{
		form_hidden_box("save_component_data_source_new","1","");
	}

	form_save_button_alt();

	include_once(CACTI_BASE_PATH . "/access/js/data_source_item.js");
	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

function get_poller_interval($seconds) {
	if ($seconds == 0) {
		return "<em>" . __("External") . "</em>";
	}else if ($seconds < 60) {
		return "<em>" . $seconds . " " . __("Seconds") . "</em>";
	}else if ($seconds == 60) {
		return __("1 Minute");
	}else{
		return "<em>" . ($seconds / 60) . " " . __("Minutes") . "</em>";
	}
}

function data_source_validate() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("rows"));
	input_validate_input_number(get_request_var_request("device_id"));
	input_validate_input_number(get_request_var_request("template_id"));
	input_validate_input_number(get_request_var_request("method_id"));
	input_validate_input_number(get_request_var_request("page"));
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
}

function data_source() {
	global $colors, $item_rows;
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	/* validate request variables */
	data_source_validate();

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_ds_current_page");
		kill_session_var("sess_ds_filter");
		kill_session_var("sess_ds_sort_column");
		kill_session_var("sess_ds_sort_direction");
		kill_session_var("sess_ds_rows");

		if (!substr_count($_SERVER["REQUEST_URI"], "/devices.php")) {
			kill_session_var("sess_ds_device_id");
		}

		kill_session_var("sess_ds_template_id");
		kill_session_var("sess_ds_method_id");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
		unset($_REQUEST["rows"]);

		if (!substr_count($_SERVER["REQUEST_URI"], "/devices.php")) {
			unset($_REQUEST["device_id"]);
		}

		unset($_REQUEST["template_id"]);
		unset($_REQUEST["method_id"]);

		$_REQUEST["page"] = 1;
	}else{
		/* let's see if someone changed an important setting */
		$changed  = FALSE;
		$changed += check_changed("filter",      "sess_ds_filter");
		$changed += check_changed("rows",        "sess_ds_rows");
		$changed += check_changed("device_id",     "sess_ds_device_id");
		$changed += check_changed("template_id", "sess_ds_template_id");
		$changed += check_changed("method_id",   "sess_ds_method_id");

		if ($changed) {
			$_REQUEST["page"] = "1";
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_ds_current_page", "1");
	load_current_session_value("filter", "sess_ds_filter", "");
	load_current_session_value("sort_column", "sess_ds_sort_column", "name_cache");
	load_current_session_value("sort_direction", "sess_ds_sort_direction", "ASC");
	load_current_session_value("rows", "sess_ds_rows", "-1");
	load_current_session_value("device_id", "sess_ds_device_id", "-1");
	load_current_session_value("template_id", "sess_ds_template_id", "-1");
	load_current_session_value("method_id", "sess_ds_method_id", "-1");

	$device = db_fetch_row("select hostname from device where id=" . $_REQUEST["device_id"]);

	?>
	<script type="text/javascript">
	<!--
	$().ready(function() {
		$("#device").autocomplete("./lib/ajax/get_devices_brief.php", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#device").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#device_id").val(data[1]);
				applyDSFilterChange(document.form_data_sources);
			}else{
				$(this).parent().find("#device_id").val(0);
			}
		});
	});

	function clearDSFilterChange(objForm) {
		strURL = '?filter=';
		<?php
		# called from outside
		if (isset($_REQUEST["tab"])) {
			# print the tab
			print "strURL = strURL + &tab=" . $_REQUEST["tab"] . "';";
			# now look for more parameters
			if (isset($_REQUEST["device_id"])) {
				print "strURL = strURL + '&device_id=" . $_REQUEST["device_id"] . "&id=" . $_REQUEST["device_id"] . "';";
			}
			if (isset($_REQUEST["template_id"])) {
				print "strURL = strURL + '&template_id=" . $_REQUEST["template_id"] . "&id=" . $_REQUEST["template_id"] . "';";
			}
		}else {
			# clear all parms
			print "strURL = strURL + '&device_id=-1';";
			print "strURL = strURL + '&template_id=-1';";
		}
		?>
		strURL = strURL + '&rows=-1';
		strURL = strURL + '&method_id=-1';
		document.location = strURL;
	}

	function applyDSFilterChange(objForm) {
		strURL = '?filter=' + objForm.filter.value;
		// take care of parms provided via autocomplete
		// those are passed as objForm.<parm>.value
		// instead of $_REQUEST["<parm>"] when called from outside
		if (objForm.device_id.value) {
			strURL = '?device_id=' + objForm.device_id.value;
		}else{
			<?php print (isset($_REQUEST["device_id"]) ? "strURL = strURL + '&device_id=" . $_REQUEST["device_id"] . "&id=" . $_REQUEST["device_id"] . "';" : "strURL = strURL + '&device_id=-1';");?>
		}
		if (objForm.template_id.value) {
			strURL = '?template_id=' + objForm.template_id.value;
		}else{
			<?php print (isset($_REQUEST["template_id"]) ? "strURL = strURL + '&template_id=" . $_REQUEST["template_id"] . "&id=" . $_REQUEST["template_id"] . "';" : "strURL = strURL + '&template_id=-1';");?>
		}
		strURL = strURL + '&rows=' + objForm.rows.value;
		strURL = strURL + '&method_id=' + objForm.method_id.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php

	html_start_box("<strong>" . __("Data Sources") . "</strong> " . __("[device:") . " " . (empty($device["hostname"]) ? __("No Host") : $device["hostname"]) . "]", "100", $colors["header"], "3", "center", "data_sources.php?action=data_source_edit&device_id=" . $_REQUEST["device_id"], true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form action="data_sources.php" name="form_data_sources">
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Host:");?>&nbsp;
					</td>
					<td class="w1">
						<?php
						if (isset($_REQUEST["device_id"])) {
							$hostname = db_fetch_cell("SELECT description as name FROM device WHERE id=".$_REQUEST["device_id"]." ORDER BY description,hostname");
						} else {
							$hostname = "";
						}
						?>
						<input class="ac_field" type="text" id="device" size="30" value="<?php print $hostname; ?>">
						<input type="hidden" id="device_id">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Template:");?>&nbsp;
					</td>
					<td class="w1">
						<select name="template_id" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if (get_request_var_request("template_id") == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if (get_request_var_request("template_id") == "0") {?> selected<?php }?>><?php print __("None");?></option>
							<?php

							$templates = db_fetch_assoc("SELECT DISTINCT data_template.id, data_template.name
								FROM data_template
								INNER JOIN data_template_data
								ON data_template.id=data_template_data.data_template_id
								WHERE data_template_data.local_data_id>0
								ORDER BY data_template.name");

							if (sizeof($templates) > 0) {
							foreach ($templates as $template) {
								print "<option value='" . $template["id"] . "'"; if (get_request_var_request("template_id") == $template["id"]) { print " selected"; } print ">" . title_trim($template["name"], 40) . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" value="<?php print __("Go");?>" name="go" align="middle">
						<input type="button" value="<?php print __("Clear");?>" name="clear" align="middle" onClick="clearDSFilterChange(document.form_data_sources)">
					</td>
				</tr>
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Method:");?>&nbsp;
					</td>
					<td class="w1">
						<select name="method_id" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if (get_request_var_request("method_id") == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if (get_request_var_request("method_id") == "0") {?> selected<?php }?>><?php print __("None");?></option>
							<?php

							$methods = db_fetch_assoc("SELECT DISTINCT data_input.id, data_input.name
								FROM data_input
								INNER JOIN data_template_data
								ON data_input.id=data_template_data.data_input_id
								WHERE data_template_data.local_data_id>0
								ORDER BY data_input.name");

							if (sizeof($methods) > 0) {
							foreach ($methods as $method) {
								print "<option value='" . $method["id"] . "'"; if (get_request_var_request("method_id") == $method["id"]) { print " selected"; } print ">" . title_trim($method["name"], 40) . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td class="w1">
						<select name="rows" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if (get_request_var_request("rows") == "-1") {?> selected<?php }?>><?php print __("Default");?></option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if (get_request_var_request("rows") == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td class="w1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
			<input type='hidden' name='page' value='1'>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var_request("filter"))) {
		$sql_where1 = "AND (data_template_data.name_cache like '%%" . $_REQUEST["filter"] . "%%'" .
			" OR data_template_data.local_data_id like '%%" . get_request_var_request("filter") . "%%'" .
			" OR data_template.name like '%%" . get_request_var_request("filter") . "%%'" .
			" OR data_input.name like '%%" . get_request_var_request("filter") . "%%')";

		$sql_where2 = "AND (data_template_data.name_cache like '%%" . $_REQUEST["filter"] . "%%'" .
			" OR data_template.name like '%%" . get_request_var_request("filter") . "%%')";
	}else{
		$sql_where1 = "";
		$sql_where2 = "";
	}

	if (get_request_var_request("device_id") == "-1") {
		/* Show all items */
	}elseif (get_request_var_request("device_id") == "0") {
		$sql_where1 .= " AND data_local.device_id=0";
		$sql_where2 .= " AND data_local.device_id=0";
	}else {
		$sql_where1 .= " AND data_local.device_id=" . $_REQUEST["device_id"];
		$sql_where2 .= " AND data_local.device_id=" . $_REQUEST["device_id"];
	}

	if (get_request_var_request("template_id") == "-1") {
		/* Show all items */
	}elseif (get_request_var_request("template_id") == "0") {
		$sql_where1 .= " AND data_template_data.data_template_id=0";
		$sql_where2 .= " AND data_template_data.data_template_id=0";
	}else {
		$sql_where1 .= " AND data_template_data.data_template_id=" . $_REQUEST["template_id"];
		$sql_where2 .= " AND data_template_data.data_template_id=" . $_REQUEST["template_id"];
	}

	if (get_request_var_request("method_id") == "-1") {
		/* Show all items */
	}elseif (get_request_var_request("method_id") == "0") {
		$sql_where1 .= " AND data_template_data.data_input_id=0";
		$sql_where2 .= " AND data_template_data.data_input_id=0";
	}else {
		$sql_where1 .= " AND data_template_data.data_input_id=" . $_REQUEST["method_id"];
		$sql_where2 .= " AND data_template_data.data_input_id=" . $_REQUEST["method_id"];
	}

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_data_source");
	}else{
		$rows = get_request_var_request("rows");
	}

	$total_rows = sizeof(db_fetch_assoc("SELECT
		data_local.id
		FROM (data_local,data_template_data)
		LEFT JOIN data_input
		ON (data_input.id=data_template_data.data_input_id)
		LEFT JOIN data_template
		ON (data_local.data_template_id=data_template.id)
		WHERE data_local.id=data_template_data.local_data_id
		$sql_where1"));

	$poller_intervals = array_rekey(db_fetch_assoc("SELECT data_template_data.local_data_id AS id,
		Min(data_template_data.rrd_step*rra.steps) AS poller_interval
		FROM data_template
		INNER JOIN (data_local
		INNER JOIN ((data_template_data_rra
		INNER JOIN data_template_data ON data_template_data_rra.data_template_data_id=data_template_data.id)
		INNER JOIN rra ON data_template_data_rra.rra_id = rra.id) ON data_local.id = data_template_data.local_data_id) ON data_template.id = data_template_data.data_template_id
		$sql_where2
		GROUP BY data_template_data.local_data_id"), "id", "poller_interval");

	$dssql = "SELECT
		data_template_data.local_data_id,
		data_template_data.name_cache,
		data_template_data.active,
		data_input.name as data_input_name,
		data_template.name as data_template_name,
		data_local.device_id
		FROM (data_local,data_template_data)
		LEFT JOIN data_input
		ON (data_input.id=data_template_data.data_input_id)
		LEFT JOIN data_template
		ON (data_local.data_template_id=data_template.id)
		WHERE data_local.id=data_template_data.local_data_id
		$sql_where1
		ORDER BY ". get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows;

	$data_sources = db_fetch_assoc($dssql);

	html_start_box("", "100", $colors["header"], "0", "center", "");

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "data_sources.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name_cache" => array(__("Name"), "ASC"),
		"local_data_id" => array(__("ID"),"ASC"),
		"data_input_name" => array(__("Data Input Method"), "ASC"),
		"nosort" => array(__("Poller Interval"), "ASC"),
		"active" => array(__("Active"), "ASC"),
		"data_template_name" => array(__("Template Name"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			$data_source = api_plugin_hook_function('data_sources_table', $data_source);
			$data_template_name = ((empty($data_source["data_template_name"])) ? "<em>" . __("None") . "</em>" : $data_source["data_template_name"]);
			$data_input_name    = ((empty($data_source["data_input_name"])) ? "<em>" . __("External") . "</em>" : $data_source["data_input_name"]);
			$poller_interval    = ((isset($poller_intervals[$data_source["local_data_id"]])) ? $poller_intervals[$data_source["local_data_id"]] : 0);

			form_alternate_row_color('line' . $data_source["local_data_id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("data_sources.php?action=data_source_edit&id=" . $data_source["local_data_id"]) . "' title='" . htmlspecialchars($data_source["name_cache"]) . "'>" . (($_REQUEST["filter"] != "") ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", title_trim(htmlentities($data_source["name_cache"], ENT_NOQUOTES, "UTF-8"), read_config_option("max_title_data_source"))) : title_trim(htmlentities($data_source["name_cache"], ENT_NOQUOTES, "UTF-8"), read_config_option("max_title_data_source"))) . "</a>", $data_source["local_data_id"]);
			form_selectable_cell($data_source['local_data_id'], $data_source['local_data_id']);
			form_selectable_cell((($_REQUEST["filter"] != "") ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $data_input_name) : $data_input_name), $data_source["local_data_id"]);
			form_selectable_cell(get_poller_interval($poller_interval), $data_source["local_data_id"]);
			form_selectable_cell(($data_source['active'] == CHECKED ? "Yes" : "No"), $data_source["local_data_id"]);
			form_selectable_cell((($_REQUEST["filter"] != "") ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $data_source['data_template_name']) : $data_source['data_template_name']), $data_source["local_data_id"]);
			form_checkbox_cell($data_source["name_cache"], $data_source["local_data_id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>" . __("No Data Sources") . "</em></td></tr>";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
