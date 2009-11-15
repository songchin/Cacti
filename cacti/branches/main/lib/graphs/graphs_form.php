<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

function form_save() {
	if ((isset($_POST["save_component_graph_new"])) && (!empty($_POST["graph_template_id"]))) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("graph_template_id"));
		/* ==================================================== */

		$save["id"] = $_POST["local_graph_id"];
		$save["graph_template_id"] = $_POST["graph_template_id"];
		$save["host_id"] = $_POST["host_id"];

		$local_graph_id = sql_save($save, "graph_local");

		change_graph_template($local_graph_id, get_request_var_post("graph_template_id"), true);

		/* update the title cache */
		update_graph_title_cache($local_graph_id);
	}

	if (isset($_POST["save_component_graph"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("graph_template_id"));
		input_validate_input_number(get_request_var_post("_graph_template_id"));
		/* ==================================================== */

		$save1["id"] = $_POST["local_graph_id"];
		$save1["host_id"] = $_POST["host_id"];
		$save1["graph_template_id"] = $_POST["graph_template_id"];

		$save2["id"] = $_POST["graph_template_graph_id"];
		$save2["local_graph_template_graph_id"] = $_POST["local_graph_template_graph_id"];
		$save2["graph_template_id"] = $_POST["graph_template_id"];
		$save2["image_format_id"] = form_input_validate($_POST["image_format_id"], "image_format_id", "", true, 3);
		$save2["title"] = form_input_validate($_POST["title"], "title", "", false, 3);
		$save2["height"] = form_input_validate($_POST["height"], "height", "^[0-9]+$", false, 3);
		$save2["width"] = form_input_validate($_POST["width"], "width", "^[0-9]+$", false, 3);
		$save2["upper_limit"] = form_input_validate($_POST["upper_limit"], "", false, 3);
		$save2["lower_limit"] = form_input_validate($_POST["lower_limit"], "", false, 3);
		$save2["vertical_label"] = form_input_validate($_POST["vertical_label"], "vertical_label", "", true, 3);
		$save2["slope_mode"] = form_input_validate((isset($_POST["slope_mode"]) ? $_POST["slope_mode"] : ""), "slope_mode", "", true, 3);
		$save2["auto_scale"] = form_input_validate((isset($_POST["auto_scale"]) ? $_POST["auto_scale"] : ""), "auto_scale", "", true, 3);
		$save2["auto_scale_opts"] = form_input_validate($_POST["auto_scale_opts"], "auto_scale_opts", "", true, 3);
		$save2["auto_scale_log"] = form_input_validate((isset($_POST["auto_scale_log"]) ? $_POST["auto_scale_log"] : ""), "auto_scale_log", "", true, 3);
		$save2["scale_log_units"] = form_input_validate((isset($_POST["scale_log_units"]) ? $_POST["scale_log_units"] : ""), "scale_log_units", "", true, 3);
		$save2["auto_scale_rigid"] = form_input_validate((isset($_POST["auto_scale_rigid"]) ? $_POST["auto_scale_rigid"] : ""), "auto_scale_rigid", "", true, 3);
		$save2["auto_padding"] = form_input_validate((isset($_POST["auto_padding"]) ? $_POST["auto_padding"] : ""), "auto_padding", "", true, 3);
		$save2["base_value"] = form_input_validate($_POST["base_value"], "base_value", "^[0-9]+$", false, 3);
		$save2["export"] = form_input_validate((isset($_POST["export"]) ? $_POST["export"] : ""), "export", "", true, 3);
		$save2["unit_value"] = form_input_validate($_POST["unit_value"], "unit_value", "", true, 3);
		$save2["unit_exponent_value"] = form_input_validate($_POST["unit_exponent_value"], "unit_exponent_value", "^-?[0-9]+$", true, 3);

		if (!is_error_message()) {
			$local_graph_id = sql_save($save1, "graph_local");
		}

		if (!is_error_message()) {
			$save2["local_graph_id"] = $local_graph_id;
			$graph_templates_graph_id = sql_save($save2, "graph_templates_graph");

			if ($graph_templates_graph_id) {
				raise_message(1);

				/* if template information chanegd, update all necessary template information */
				if ($_POST["graph_template_id"] != $_POST["_graph_template_id"]) {
					/* check to see if the number of graph items differs, if it does; we need user input */
					if ((!empty($_POST["graph_template_id"])) && (!empty($_POST["local_graph_id"])) && (sizeof(db_fetch_assoc("select id from graph_templates_item where local_graph_id=$local_graph_id")) != sizeof(db_fetch_assoc("select id from graph_templates_item where local_graph_id=0 and graph_template_id=" . $_POST["graph_template_id"])))) {
						/* set the template back, since the user may choose not to go through with the change
						at this point */
						db_execute("update graph_local set graph_template_id=" . $_POST["_graph_template_id"] . " where id=$local_graph_id");
						db_execute("update graph_templates_graph set graph_template_id=" . $_POST["_graph_template_id"] . " where local_graph_id=$local_graph_id");

						header("Location: graphs.php?action=graph_diff&amp;id=$local_graph_id&amp;graph_template_id=" . $_POST["graph_template_id"]);
						exit;
					}
				}
			}else{
				raise_message(2);
			}

			/* update the title cache */
			update_graph_title_cache($local_graph_id);
		}

		if ((!is_error_message()) && ($_POST["graph_template_id"] != $_POST["_graph_template_id"])) {
			change_graph_template($local_graph_id, get_request_var_post("graph_template_id"), true);
		}elseif (!empty($_POST["graph_template_id"])) {
			update_graph_data_query_cache($local_graph_id);
		}
	}

	if (isset($_POST["save_component_input"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("local_graph_id"));
		/* ==================================================== */

		/* first; get the current graph template id */
		$graph_template_id = db_fetch_cell("select graph_template_id from graph_local where id=" . $_POST["local_graph_id"]);

		/* get all inputs that go along with this graph template */
		$input_list = db_fetch_assoc("select id,column_name from graph_template_input where graph_template_id=$graph_template_id");

		if (sizeof($input_list) > 0) {
		foreach ($input_list as $input) {
			/* we need to find out which graph items will be affected by saving this particular item */
			$item_list = db_fetch_assoc("select
				graph_templates_item.id
				from (graph_template_input_defs,graph_templates_item)
				where graph_template_input_defs.graph_template_item_id=graph_templates_item.local_graph_template_item_id
				and graph_templates_item.local_graph_id=" . $_POST["local_graph_id"] . "
				and graph_template_input_defs.graph_template_input_id=" . $input["id"]);

			/* loop through each item affected and update column data */
			if (sizeof($item_list) > 0) {
			foreach ($item_list as $item) {
				/* if we are changing templates, the POST vars we are searching for here will not exist.
				this is because the db and form are out of sync here, but it is ok to just skip over saving
				the inputs in this case. */
				if (isset($_POST{$input["column_name"] . "_" . $input["id"]})) {
					db_execute("update graph_templates_item set " . $input["column_name"] . "='" . $_POST{$input["column_name"] . "_" . $input["id"]} . "' where id=" . $item["id"]);
				}
			}
			}
		}
		}
	}

	if (isset($_POST["save_component_graph_diff"])) {
		if (get_request_var_post("type") == "1") {
			$intrusive = true;
		}elseif (get_request_var_post("type") == "2") {
			$intrusive = false;
		}

		change_graph_template(get_request_var_post("local_graph_id"), get_request_var_post("graph_template_id"), $intrusive);
	}

	if ((isset($_POST["save_component_graph_new"])) && (empty($_POST["graph_template_id"]))) {
		header("Location: graphs.php?action=graph_edit&amp;host_id=" . $_POST["host_id"] . "&amp;new=1");
	}elseif ((is_error_message()) || (empty($_POST["local_graph_id"])) || (isset($_POST["save_component_graph_diff"])) || ($_POST["graph_template_id"] != $_POST["_graph_template_id"]) || ($_POST["host_id"] != $_POST["_host_id"])) {
		header("Location: graphs.php?action=graph_edit&amp;id=" . (empty($local_graph_id) ? $_POST["local_graph_id"] : $local_graph_id) . (isset($_POST["host_id"]) ? "&amp;host_id=" . $_POST["host_id"] : ""));
	}else{
		header("Location: graphs.php");
	}
	exit;
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $graph_actions;
	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == GRAPH_ACTION_DELETE) { /* delete */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 1; }

				switch (get_request_var_post("delete_type")) {
					case '2': /* delete all data sources referenced by this graph */
						$data_sources = db_fetch_assoc("SELECT " .
								"data_template_data.local_data_id " .
							"FROM " .
								"(data_template_rrd, " .
								"data_template_data, " .
								"graph_templates_item) " .
							"WHERE " .
								"graph_templates_item.task_item_id=data_template_rrd.id " .
								"AND data_template_rrd.local_data_id=data_template_data.local_data_id " .
								"AND graph_templates_item.local_graph_id=" . $selected_items[$i] . " " .
								"AND data_template_data.local_data_id > 0");

						if (sizeof($data_sources) > 0) {
							foreach ($data_sources as $data_source) {
								api_data_source_remove($data_source["local_data_id"]);
							}
						}

						break;
				}

				api_graph_remove($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CHANGE_TEMPLATE) { /* change graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("graph_template_id"));
				/* ==================================================== */

				change_graph_template($selected_items[$i], get_request_var_post("graph_template_id"), true);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_DUPLICATE) { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_graph($selected_items[$i], 0, get_request_var_post("title_format"));
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CONVERT_TO_TEMPLATE) { /* graph -> graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				graph_to_graph_template($selected_items[$i], get_request_var_post("title_format"));
			}
		}elseif (ereg("^tr_([0-9]+)$", get_request_var_post("drp_action"), $matches)) { /* place on tree */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("tree_id"));
				input_validate_input_number(get_request_var_post("tree_item_id"));
				/* ==================================================== */

				api_tree_item_save(0, get_request_var_post("tree_id"), TREE_ITEM_TYPE_GRAPH, get_request_var_post("tree_item_id"), "", $selected_items[$i], read_graph_config_option("default_rra_id"), 0, 0, 0, false);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CHANGE_HOST) { /* change host */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("host_id"));
				/* ==================================================== */

				db_execute("update graph_local set host_id=" . $_POST["host_id"] . " where id=" . $selected_items[$i]);
				update_graph_title_cache($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_REAPPLY_SUGGESTED_NAMES) { /* reapply suggested naming */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_reapply_suggested_graph_title($selected_items[$i]);
				update_graph_title_cache($selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_RESIZE) { /* resize graphs */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				api_resize_graphs($selected_items[$i], get_request_var_post('graph_width'), get_request_var_post('graph_height'));
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_ENABLE_EXPORT) { /* enable graph export */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */
				db_execute("UPDATE graph_templates_graph SET export='on' WHERE local_graph_id=" . $selected_items[$i]);
			}
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_DISABLE_EXPORT) { /* disable graph export */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */
				db_execute("UPDATE graph_templates_graph SET export='' WHERE local_graph_id=" . $selected_items[$i]);
			}
		} else {
			api_plugin_hook_function('graphs_action_execute', get_request_var_post('drp_action'));
		}

		header("Location: graphs.php");
		exit;
	}

	/* setup some variables */
	$graph_list = ""; $i = 0; $graph_array = array();

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$graph_list .= "<li>" . get_graph_title($matches[1]) . "<br>";
			$graph_array[$i++] = $matches[1];
		}
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	/* add a list of tree names to the actions dropdown */
	$graph_actions = array_merge($graph_actions, api_tree_add_tree_names_to_actions_array());

	html_start_box("<strong>" . $graph_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='graphs.php' method='post'>\n";

	if (sizeof($graph_array)) {
		if (get_request_var_post("drp_action") == GRAPH_ACTION_DELETE) { /* delete */
			$graphs = array();

			/* find out which (if any) data sources are being used by this graph, so we can tell the user */
			if (isset($graph_array)) {
				$data_sources = db_fetch_assoc("select
					data_template_data.local_data_id,
					data_template_data.name_cache
					from (data_template_rrd,data_template_data,graph_templates_item)
					where graph_templates_item.task_item_id=data_template_rrd.id
					and data_template_rrd.local_data_id=data_template_data.local_data_id
					and " . array_to_sql_or($graph_array, "graph_templates_item.local_graph_id") . "
					and data_template_data.local_data_id > 0
					group by data_template_data.local_data_id
					order by data_template_data.name_cache");
			}

			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following graphs?") . "</p>
						<p>$graph_list</p>
						";
						if (sizeof($data_sources) > 0) {
							print "<tr class='rowAlternate1'><td class='textArea'><p class='textArea'>" . __("The following data sources are in use by these graphs:") . "</p>\n";

							foreach ($data_sources as $data_source) {
								print "<strong>" . $data_source["name_cache"] . "</strong><br>\n";
							}

							print "<br>";
							form_radio_button("delete_type", "1", "1", __("Leave the data sources untouched."), "1"); print "<br>";
							form_radio_button("delete_type", "1", "2", __("Delete all <strong>data sources</strong> referenced by these graphs."), "1"); print "<br>";
							print "</td></tr>";
						}
					print "
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CHANGE_TEMPLATE) { /* change graph template */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Choose a graph template and click save to change the graph template for the following graphs. Be aware that all warnings will be suppressed during the conversion, so graph data loss is possible.") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("New Graph Template:") . "</strong><br>"; form_dropdown("graph_template_id",db_fetch_assoc("select graph_templates.id,graph_templates.name from graph_templates order by name"),"name","id","","","0"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_DUPLICATE) { /* duplicate */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be duplicated. You can optionally change the title format for the new graphs.") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", __("<graph_title> (1)"), "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CONVERT_TO_TEMPLATE) { /* graph -> graph template */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be converted into graph templates.  You can optionally change the title format for the new graph templates.") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", __("<graph_title> Template"), "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (ereg("^tr_([0-9]+)$", get_request_var_post("drp_action"), $matches)) { /* place on tree */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be placed under the branch selected below.") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("Destination Branch:") . "</strong><br>"; grow_dropdown_tree($matches[1], "tree_item_id", "0"); print "</p>
					</td>
				</tr>\n
				<input type='hidden' name='tree_id' value='" . $matches[1] . "'>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_CHANGE_HOST) { /* change host */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Choose a new host for these graphs:") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("New Host:") . "</strong><br>"; form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","0"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_REAPPLY_SUGGESTED_NAMES) { /* reapply suggested naming to host */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will have their suggested naming conventions recalculated and applied to the graphs.") . "</p>
						<p>$graph_list</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_RESIZE) { /* reapply suggested naming to host */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be resized per your specifications.") . "</p>
						<p>$graph_list</p>
						<p><strong>" . __("Graph Height:") . "</strong><br>"; form_text_box("graph_height", "", "", "255", "30", "text"); print "</p>
						<p><strong>" . __("Graph Width:") . "</strong><br>"; form_text_box("graph_width", "", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_ENABLE_EXPORT) { /* enable graph export */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be enabled for graph export.") . "</p>
						<p>$graph_list</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == GRAPH_ACTION_DISABLE_EXPORT) { /* disable graph export */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following graphs will be disabled for graph export.") . "</p>
						<p>$graph_list</p>
					</td>
				</tr>\n
				";
		} else {
			$save['drp_action'] = $_POST['drp_action'];
			$save['graph_list'] = $graph_list;
			$save['graph_array'] = $graph_array;
			api_plugin_hook_function('graphs_action_prepare', $save);
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Graph.  Please select 'Return' to return to the previous menu.") . "</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($graph_array)) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($graph_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item() {
	global $colors, $consolidation_functions, $graph_item_types, $struct_graph_item;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (empty($_GET["id"])) {
		$template_item_list = array();

		$header_label = __("[new]");
	}else{
		$template_item_list = db_fetch_assoc("select
			graph_templates_item.id,
			graph_templates_item.text_format,
			graph_templates_item.value,
			graph_templates_item.hard_return,
			graph_templates_item.graph_type_id,
			graph_templates_item.consolidation_function_id,
			data_template_rrd.data_source_name,
			cdef.name as cdef_name,
			colors.hex
			from graph_templates_item
			left join data_template_rrd on (graph_templates_item.task_item_id=data_template_rrd.id)
			left join data_local on (data_template_rrd.local_data_id=data_local.id)
			left join data_template_data on (data_local.id=data_template_data.local_data_id)
			left join cdef on (cdef_id=cdef.id)
			left join colors on (color_id=colors.id)
			where graph_templates_item.local_graph_id=" . $_GET["id"] . "
			order by graph_templates_item.sequence");

		$host_id = db_fetch_cell("select host_id from graph_local where id=" . $_GET["id"]);
		$header_label = __("[edit: %s]", get_graph_title($_GET["id"]));
	}

	$graph_template_id = db_fetch_cell("select graph_template_id from graph_local where id=" . $_GET["id"]);

	if (empty($graph_template_id)) {
		$add_text = "graphs_items.php?action=item_edit&amp;local_graph_id=" . $_GET["id"] . "&amp;host_id=$host_id";
	}else{
		$add_text = "";
	}

	html_start_box("<strong>" . __("Graph Items") . "</strong> $header_label", "100", $colors["header"], "3", "center", $add_text);
	draw_graph_items_list($template_item_list, "graphs_items.php", "local_graph_id=" . $_GET["id"], (empty($graph_template_id) ? false : true));
	html_end_box();
}

/* ------------------------------------
    graph - Graphs
   ------------------------------------ */

function graph_diff() {
	global $colors, $struct_graph_item, $graph_item_types, $consolidation_functions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("graph_template_id"));
	/* ==================================================== */

	$template_query = "select
		graph_templates_item.id,
		graph_templates_item.text_format,
		graph_templates_item.value,
		graph_templates_item.hard_return,
		graph_templates_item.consolidation_function_id,
		graph_templates_item.graph_type_id,
		CONCAT_WS(' - ',data_template_data.name,data_template_rrd.data_source_name) as task_item_id,
		cdef.name as cdef_id,
		colors.hex as color_id
		from graph_templates_item
		left join data_template_rrd on (graph_templates_item.task_item_id=data_template_rrd.id)
		left join data_local on (data_template_rrd.local_data_id=data_local.id)
		left join data_template_data on (data_local.id=data_template_data.local_data_id)
		left join cdef on (cdef_id=cdef.id)
		left join colors on (color_id=colors.id)";

	/* first, get information about the graph template as that's what we're going to model this
	graph after */
	$graph_template_items = db_fetch_assoc("
		$template_query
		where graph_templates_item.graph_template_id=" . $_GET["graph_template_id"] . "
		and graph_templates_item.local_graph_id=0
		order by graph_templates_item.sequence");

	/* next, get information about the current graph so we can make the appropriate comparisons */
	$graph_items = db_fetch_assoc("
		$template_query
		where graph_templates_item.local_graph_id=" . $_GET["id"] . "
		order by graph_templates_item.sequence");

	$graph_template_inputs = db_fetch_assoc("select
		graph_template_input.column_name,
		graph_template_input_defs.graph_template_item_id
		from (graph_template_input,graph_template_input_defs)
		where graph_template_input.id=graph_template_input_defs.graph_template_input_id
		and graph_template_input.graph_template_id=" . $_GET["graph_template_id"]);

	/* ok, we want to loop through the array with the GREATEST number of items so we don't have to worry
	about tacking items on the end */
	if (sizeof($graph_template_items) > sizeof($graph_items)) {
		$items = $graph_template_items;
	}else{
		$items = $graph_items;
	}

	?>
	<table class='topBoxAlt'>
		<tr>
			<td class="textArea">
				<?php print __("The template you have selected requires some changes to be made to the structure of your graph. Below is a preview of your graph along with changes that need to be completed as shown in the left-hand column.");?>
			</td>
		</tr>
	</table>
	<br>
	<?php

	html_start_box("<strong>" . __("Graph Preview") . "</strong>", "100", $colors["header"], "3", "center", "");

	$graph_item_actions = array("normal" => "", "add" => "+", "delete" => "-");

	$group_counter = 0; $i = 0; $mode = "normal"; $_graph_type_name = "";

	if (sizeof($items) > 0) {
	foreach ($items as $item) {
		reset($struct_graph_item);

		/* graph grouping display logic */
		$bold_this_row = false; $use_custom_row_color = false; $action_css = ""; unset($graph_preview_item_values);

		if ((sizeof($graph_template_items) > sizeof($graph_items)) && ($i >= sizeof($graph_items))) {
			$mode = "add";
			$user_message = __("When you click save, the items marked with a '<strong>+</strong>' will be added <strong>(Recommended)</strong>.");
		}elseif ((sizeof($graph_template_items) < sizeof($graph_items)) && ($i >= sizeof($graph_template_items))) {
			$mode = "delete";
			$user_message = __("When you click save, the items marked with a '<strong>-</strong>' will be removed <strong>(Recommended)</strong>.");
		}

		/* here is the fun meshing part. first we check the graph template to see if there is an input
		for each field of this row. if there is, we revert to the value stored in the graph, if not
		we revert to the value stored in the template. got that? ;) */
		for ($j=0; ($j < count($graph_template_inputs)); $j++) {
			if ($graph_template_inputs[$j]["graph_template_item_id"] == (isset($graph_template_items[$i]["id"]) ? $graph_template_items[$i]["id"] : "")) {
				/* if we find out that there is an "input" covering this field/item, use the
				value from the graph, not the template */
				$graph_item_field_name = (isset($graph_template_inputs[$j]["column_name"]) ? $graph_template_inputs[$j]["column_name"] : "");
				$graph_preview_item_values[$graph_item_field_name] = (isset($graph_items[$i][$graph_item_field_name]) ? $graph_items[$i][$graph_item_field_name] : "");
			}
		}

		/* go back through each graph field and find out which ones haven't been covered by the
		"inputs" above. for each one, use the value from the template */
		while (list($field_name, $field_array) = each($struct_graph_item)) {
			if ($mode == "delete") {
				$graph_preview_item_values[$field_name] = (isset($graph_items[$i][$field_name]) ? $graph_items[$i][$field_name] : "");
			}elseif (!isset($graph_preview_item_values[$field_name])) {
				$graph_preview_item_values[$field_name] = (isset($graph_template_items[$i][$field_name]) ? $graph_template_items[$i][$field_name] : "");
			}
		}

		/* "prepare" array values */
		$consolidation_function_id = $graph_preview_item_values["consolidation_function_id"];
		$graph_type_id = $graph_preview_item_values["graph_type_id"];

		/* color logic */
		if (($graph_item_types[$graph_type_id] != "GPRINT") && ($graph_item_types[$graph_type_id] != $_graph_type_name)) {
			$bold_this_row = true; $use_custom_row_color = true; $hard_return = "";

			if ($group_counter % 2 == 0) {
				$alternate_color_1 = "EEEEEE";
				$alternate_color_2 = "EEEEEE";
				$custom_row_color = "D5D5D5";
			}else{
				$alternate_color_1 = $colors["alternate"];
				$alternate_color_2 = $colors["alternate"];
				$custom_row_color = "D2D6E7";
			}

			$group_counter++;
		}

		$_graph_type_name = $graph_item_types[$graph_type_id];

		/* alternating row colors */
		if ($use_custom_row_color == false) {
			if ($i % 2 == 0) {
				$action_column_color = $alternate_color_1;
			}else{
				$action_column_color = $alternate_color_2;
			}
		}else{
			$action_column_color = $custom_row_color;
		}

		print "<tr bgcolor='#$action_column_color'>"; $i++;

		/* make the left-hand column blue or red depending on if "add"/"remove" mode is set */
		if ($mode == "add") {
			$action_column_color = $colors["header"];
			$action_css = "";
		}elseif ($mode == "delete") {
			$action_column_color = "C63636";
			$action_css = "text-decoration: line-through;";
		}

		if ($bold_this_row == true) {
			$action_css .= " font-weight:bold;";
		}

		/* draw the TD that shows the user whether we are going to: KEEP, ADD, or DROP the item */
		print "<td width='1%' bgcolor='#$action_column_color' style='font-weight: bold; color: white;'>" . $graph_item_actions[$mode] . "</td>";
		print "<td style='$action_css'><strong>" . __("Item") . " # " . $i . "</strong></td>\n";

		if (empty($graph_preview_item_values["task_item_id"])) { $graph_preview_item_values["task_item_id"] = "No Task"; }

		switch (true) {
		case ereg("(AREA|STACK|GPRINT|LINE[123])", $_graph_type_name):
			$matrix_title = "(" . $graph_preview_item_values["task_item_id"] . "): " . $graph_preview_item_values["text_format"];
			break;
		case ereg("(HRULE|VRULE)", $_graph_type_name):
			$matrix_title = "HRULE: " . $graph_preview_item_values["value"];
			break;
		case ereg("(COMMENT)", $_graph_type_name):
			$matrix_title = "COMMENT: " . $graph_preview_item_values["text_format"];
			break;
		}

		/* use the cdef name (if in use) if all else fails */
		if ($matrix_title == "") {
			if ($graph_preview_item_values["cdef_id"] != "") {
				$matrix_title .= "CDEF: " . $graph_preview_item_values["cdef_id"];
			}
		}

		if ($graph_preview_item_values["hard_return"] == "on") {
			$hard_return = "<strong><font color=\"#FF0000\">&lt;HR&gt;</font></strong>";
		}

		print "<td style='$action_css'>" . htmlspecialchars($matrix_title) . $hard_return . "</td>\n";
		print "<td style='$action_css'>" . $graph_item_types{$graph_preview_item_values["graph_type_id"]} . "</td>\n";
		print "<td style='$action_css'>" . $consolidation_functions{$graph_preview_item_values["consolidation_function_id"]} . "</td>\n";
		print "<td" . ((!empty($graph_preview_item_values["color_id"])) ? " bgcolor='#" . $graph_preview_item_values["color_id"] . "'" : "") . " width='1%'>&nbsp;</td>\n";
		print "<td style='$action_css'>" . $graph_preview_item_values["color_id"] . "</td>\n";

		print "</tr>";
	}
	}else{
		form_alternate_row_color();
		?>
			<td colspan="7">
				<em><?php print __("No Items");?></em>
			</td>
		<?php
		form_end_row();
	}
	html_end_box();

	?>
	<form action="graphs.php" method="post">
	<table class='topBoxAlt'>
		<tr>
			<td class="textArea">
				<input type='radio' name='type' value='1' checked>&nbsp;<?php print $user_message;?><br>
				<input type='radio' name='type' value='2'>&nbsp;<?php print __("When you click save, the graph items will remain untouched (could cause inconsistencies).");?>
			</td>
		</tr>
	</table>

	<br>

	<input type="hidden" name="action" value="save">
	<input type="hidden" name="save_component_graph_diff" value="1">
	<input type="hidden" name="local_graph_id" value="<?php print $_GET["id"];?>">
	<input type="hidden" name="graph_template_id" value="<?php print $_GET["graph_template_id"];?>">
	<?php

	form_save_button_alt("action!graph_edit|id!" . get_request_var("id"));
}

function graph_edit() {
	global $colors, $struct_graph, $image_types, $consolidation_functions, $graph_item_types, $struct_graph_item;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$use_graph_template = true;

	if (!empty($_GET["id"])) {
		$local_graph_template_graph_id = db_fetch_cell("select local_graph_template_graph_id from graph_templates_graph where local_graph_id=" . $_GET["id"]);

		$graphs = db_fetch_row("select * from graph_templates_graph where local_graph_id=" . $_GET["id"]);
		$graphs_template = db_fetch_row("select * from graph_templates_graph where id=$local_graph_template_graph_id");

		$host_id = db_fetch_cell("select host_id from graph_local where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . get_graph_title($_GET["id"]) . "]";

		if ($graphs["graph_template_id"] == "0") {
			$use_graph_template = false;
		}
	}else{
		$header_label = __("[new]");
		$use_graph_template = false;
	}

	/* handle debug mode */
	if (isset($_GET["debug"])) {
		if (get_request_var("debug") == "0") {
			kill_session_var("graph_debug_mode");
		}elseif (get_request_var("debug") == "1") {
			$_SESSION["graph_debug_mode"] = true;
		}
	}

	?>
	<script type="text/javascript">
	<!--
	var disabled = true;

	$().ready(function() {
		$("input").attr("disabled","disabled")
		$("#cancel").removeAttr("disabled");
	});

	function changeGraphState() {
		if (disabled) {
			$("input").removeAttr("disabled");
			disabled = false;
		}else{
			$("input").attr("disabled","disabled")
			$("#cancel").removeAttr("disabled");
			disabled = true;
		}
	}
	-->
	</script>
	<?php

	$tip_text  = "<tr><td align=\\'right\\'><a class=\\'popup_item\\' id=\\'changeGraphState\\' onClick=\\'changeGraphState()\\' href=\\'#\\'>Unlock/Lock</a></td></tr>";
	$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('graphs.php?action=graph_edit&id=' . (isset($_GET["id"]) ? $_GET["id"] : 0) . "&debug=" . (isset($_SESSION["graph_debug_mode"]) ? "0" : "1")) . "\\'>" . __("Turn") . " <strong>" . (isset($_SESSION["graph_debug_mode"]) ? __("Off") : __("On")) . "</strong> " . __("Debug Mode") . "</a></td></tr>";
	if (!empty($graphs["graph_template_id"])) {
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('graph_templates.php?action=template_edit&id=' . (isset($graphs["graph_template_id"]) ? $graphs["graph_template_id"] : "0")) . "\\'>" . __("Edit Template") . "</a></td></tr>";
	}
	if (!empty($_GET["host_id"]) || !empty($host_id)) {
		$tip_text .= "<tr><td align=\\'right\\'><a class=\\'popup_item\\' href=\\'" . htmlspecialchars('host.php?action=edit&id=' . (isset($_GET["host_id"]) ? $_GET["host_id"] : $host_id)) . "\\'>" . __("Edit Host") . "</a></td></tr>";
	}

	if (!empty($_GET["id"])) {
		?>
		<table width="100%" align="center">
			<tr>
				<td class="textInfo" colspan="2" valign="top">
					<?php print get_graph_title(get_request_var("id"));?>
				</td>
				<td style="white-space:nowrap;" align="right" width="1"><a id='tooltip' class='popup_anchor' href='#' onMouseOver="Tip('<?php print $tip_text;?>', BGCOLOR, '#EEEEEE', FIX, ['tooltip', -45, 0], STICKY, true, SHADOW, true, CLICKCLOSE, true, FADEOUT, 400, TEXTALIGN, 'right', BORDERCOLOR, '#F5F5F5')" onMouseOut="UnTip()">Graph Options</a></td>
			</tr>
		</table>
		<?php
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='graph_edit'>\n";
	html_start_box("<strong>" . __("Graph Template Selection") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'template');

	$form_array = array(
		"graph_template_id" => array(
			"method" => "autocomplete",
			"callback_function" => "./lib/ajax/get_graph_templates.php",
			"friendly_name" => __("Selected Graph Template"),
			"description" => __("Choose a graph template to apply to this graph.  Please note that graph data may be lost if you change the graph template after one is already applied."),
			"id" => (isset($graphs) ? $graphs["graph_template_id"] : "0"),
			"name" => db_fetch_cell("SELECT name FROM graph_templates WHERE id=" . (isset($graphs) ? $graphs["graph_template_id"] : "0"))
			),
		"host_id" => array(
			"method" => "autocomplete",
			"callback_function" => "./lib/ajax/get_hosts_detailed.php",
			"friendly_name" => __("Host"),
			"description" => __("Choose the host that this graph belongs to."),
			"id" => (isset($_GET["host_id"]) ? $_GET["host_id"] : $host_id),
			"name" => db_fetch_cell("SELECT CONCAT_WS('',description,' (',hostname,')') FROM host WHERE id=" . (isset($_GET['host_id']) ? $_GET['host_id'] : $host_id))
			),
		"graph_template_graph_id" => array(
			"method" => "hidden",
			"value" => (isset($graphs) ? $graphs["id"] : "0")
			),
		"local_graph_id" => array(
			"method" => "hidden",
			"value" => (isset($graphs) ? $graphs["local_graph_id"] : "0")
			),
		"local_graph_template_graph_id" => array(
			"method" => "hidden",
			"value" => (isset($graphs) ? $graphs["local_graph_template_graph_id"] : "0")
			),
		"_graph_template_id" => array(
			"method" => "hidden",
			"value" => (isset($graphs) ? $graphs["graph_template_id"] : "0")
			),
		"_host_id" => array(
			"method" => "hidden",
			"value" => (isset($host_id) ? $host_id : "0")
			)
		);

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();


#	print "<form method='post' action='graphs.php'>\n";
	/* only display the "inputs" area if we are using a graph template for this graph */
	if (!empty($graphs["graph_template_id"])) {
		html_start_box("<strong>" . __("Supplemental Graph Template Data") . "</strong>", "100", $colors["header"], "0", "center", "");

		draw_nontemplated_fields_graph($graphs["graph_template_id"], $graphs, "|field|", "<strong>" . __("Graph Fields") . "</strong>", true, true, 0);
		draw_nontemplated_fields_graph_item($graphs["graph_template_id"], get_request_var("id"), "|field|_|id|", "<strong>" . __("Graph Item Fields") ."</strong>", true);

		html_end_box();
	}

	/* graph item list goes here */
	if ((!empty($_GET["id"])) && (empty($graphs["graph_template_id"]))) {
		item();
	}

	if (!empty($_GET["id"])) {
		?>
		<table width="100%" align="center">
			<tr>
				<td align="center" class="textInfo" colspan="2">
					<img src="<?php print htmlspecialchars("graph_image.php?action=edit&local_graph_id=" . get_request_var("id") . "&rra_id=" . read_graph_config_option("default_rra_id"));?>" alt="">
				</td>
				<?php
				if ((isset($_SESSION["graph_debug_mode"])) && (isset($_GET["id"]))) {
					$graph_data_array = array();
					$graph_data_array["output_flag"] = RRDTOOL_OUTPUT_STDERR;
					/* make rrdtool_function_graph to only print the command without executing it */
					$graph_data_array["print_source"] = 1;
					?>
					<td>
						<span class="textInfo"><?php print __("RRDTool Command:");?></span><br>
						<pre><?php print rrdtool_function_graph(get_request_var("id"), read_graph_config_option("default_rra_id"), $graph_data_array);?></pre>
						<span class="textInfo"><?php print __("RRDTool Says:");?></span><br>
						<?php /* make rrdtool_function_graph to generate AND execute the rrd command, but only for fetching the "return code" */
						unset($graph_data_array["print_source"]);?>
						<pre><?php print rrdtool_function_graph(get_request_var("id"), read_graph_config_option("default_rra_id"), $graph_data_array);?></pre>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<?php
	}

	if (((isset($_GET["id"])) || (isset($_GET["new"]))) && (empty($graphs["graph_template_id"]))) {
		html_start_box("<strong>" . __("Graph Configuration") . "</strong>", "100", $colors["header"], "0", "center", "");
		$header_items = array(__("Field"), __("Value"));
		print "<tr><td>";
		html_header($header_items, 1, true, 'template');

		$form_array = array();

		while (list($field_name, $field_array) = each($struct_graph)) {
			$form_array += array($field_name => $struct_graph[$field_name]);

			$form_array[$field_name]["value"] = (isset($graphs) ? $graphs[$field_name] : "");
			$form_array[$field_name]["form_id"] = (isset($graphs) ? $graphs["id"] : "0");

			if (!(($use_graph_template == false) || ($graphs_template{"t_" . $field_name} == "on"))) {
				$form_array[$field_name]["method"] = "template_" . $form_array[$field_name]["method"];
#				$form_array[$field_name]["description"] = "";
			}
		}

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
					),
				"fields" => $form_array
				)
			);

		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
	}

	if ((isset($_GET["id"])) || (isset($_GET["new"]))) {
		form_hidden_box("save_component_graph","1","");
		form_hidden_box("save_component_input","1","");
	}else{
		form_hidden_box("save_component_graph_new","1","");
	}

	form_hidden_box("rrdtool_version", read_config_option("rrdtool_version"), "");
	form_save_button_alt();
?>
<script type="text/javascript">
	$('#graph_item').tableDnD({
		onDrop: function(table, row) {
//			alert("lib/ajax/jquery.tablednd/graph_item.ajax.php?id=<?php print $_GET["id"];?>&"+$.tableDnD.serialize());
			$('#AjaxResult').load("lib/ajax/jquery.tablednd/graphs_item.ajax.php?id=<?php print $_GET["id"];?>&"+$.tableDnD.serialize());
//			location.reload();
		}
	});
</script>
<?php

//Now we need some javascript to make it dynamic
?>
<script type="text/javascript">

dynamic();

function dynamic() {
	//alert("RRDTool Version is '" + document.getElementById('rrdtool_version').value + "'");
	//alert("Log is '" + document.getElementById('auto_scale_log').checked + "'");
	if (document.getElementById('scale_log_units')) {
		document.getElementById('scale_log_units').disabled=true;
		if ((document.getElementById('rrdtool_version').value != 'rrd-1.0.x') &&
			(document.getElementById('auto_scale_log').checked)) {
			document.getElementById('scale_log_units').disabled=false;
		}
	}
}

function changeScaleLog() {
	//alert("Log changed to '" + document.getElementById('auto_scale_log').checked + "'");
	if (document.getElementById('scale_log_units')) {
		document.getElementById('scale_log_units').disabled=true;
		if ((document.getElementById('rrdtool_version').value != 'rrd-1.0.x') &&
			(document.getElementById('auto_scale_log').checked)) {
			document.getElementById('scale_log_units').disabled=false;
		}
	}
}
</script>
<?php

}

function graph() {
	global $colors, $graph_actions, $item_rows;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("host_id"));
	input_validate_input_number(get_request_var_request("rows"));
	input_validate_input_number(get_request_var_request("template_id"));
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

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_graph_current_page");
		kill_session_var("sess_graph_filter");
		kill_session_var("sess_graph_sort_column");
		kill_session_var("sess_graph_sort_direction");

		if (!substr_count($_SERVER["REQUEST_URI"], "/host.php")) {
			kill_session_var("sess_graph_host_id");
		}

		kill_session_var("sess_graph_rows");
		kill_session_var("sess_graph_template_id");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);

		if (!substr_count($_SERVER["REQUEST_URI"], "/host.php")) {
			unset($_REQUEST["host_id"]);
		}

		unset($_REQUEST["rows"]);
		unset($_REQUEST["template_id"]);
	}

	/* let's see if someone changed an important setting */
	$changed  = FALSE;
	$changed += check_changed("filter",      "sess_ds_filter");
	$changed += check_changed("rows",        "sess_ds_rows");
	$changed += check_changed("host_id",     "sess_ds_host_id");
	$changed += check_changed("template_id", "sess_ds_template_id");

	if ($changed) {
		$_REQUEST["page"] = "1";
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_graph_current_page", "1");
	load_current_session_value("filter", "sess_graph_filter", "");
	load_current_session_value("sort_column", "sess_graph_sort_column", "title_cache");
	load_current_session_value("sort_direction", "sess_graph_sort_direction", "ASC");
	load_current_session_value("host_id", "sess_graph_host_id", "-1");
	load_current_session_value("rows", "sess_graph_rows", "-1");
	load_current_session_value("template_id", "sess_graph_template_id", "-1");

	?>
	<script type="text/javascript">
	<!--
	$().ready(function() {
		$("#host").autocomplete("./lib/ajax/get_hosts_brief.php", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#host").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#host_id").val(data[1]);
				applyGraphsFilterChange(document.form_graph_id);
			}else{
				$(this).parent().find("#host_id").val(0);
			}
		});
	});

	function clearGraphsFilterChange(objForm) {
		<?php print (isset($_REQUEST["tab"]) ? "strURL = '?host_id=" . $_REQUEST["host_id"] . "&id=" . $_REQUEST["host_id"] . "&action=edit&amp;action=edit&tab=" . $_REQUEST["tab"] . "';" : "strURL = '?host_id=-1';");?>
		strURL = strURL + '&filter=';
		strURL = strURL + '&rows=-1';
		strURL = strURL + '&template_id=-1';
		document.location = strURL;
	}

	function applyGraphsFilterChange(objForm) {
		if (objForm.host_id.value) {
			strURL = '?host_id=' + objForm.host_id.value;
			strURL = strURL + '&filter=' + objForm.filter.value;
		}else{
			strURL = '?filter=' + objForm.filter.value;
		}
		strURL = strURL + '&rows=' + objForm.rows.value;
		strURL = strURL + '&template_id=' + objForm.template_id.value;
		<?php print (isset($_REQUEST["tab"]) ? "strURL = strURL + '&id=' + objForm.host_id.value + '&action=edit&action=edit&tab=" . $_REQUEST["tab"] . "';" : "");?>
		document.location = strURL;
	}
	-->
	</script>
	<?php

	html_start_box("<strong>" . __("Graph Management") . "</strong>", "100", $colors["header"], "3", "center", "graphs.php?action=graph_edithost_id=" . $_REQUEST["host_id"], true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_graph_id" action="graphs.php">
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Host:");?>&nbsp;
					</td>
					<td width="1">
						<?php
						if (isset($_REQUEST["host_id"])) {
							$hostname = db_fetch_cell("SELECT description as name FROM host WHERE id=".$_REQUEST["host_id"]." ORDER BY description,hostname");
						} else {
							$hostname = "";
						}
						?>
						<input class="ac_field" type="text" id="host" size="30" value="<?php print $hostname; ?>">
						<input type="hidden" id="host_id">
					<td width="70">
						&nbsp;<?php print __("Template:");?>&nbsp;
					</td>
					<td width="1">
						<select name="template_id" onChange="applyGraphsFilterChange(document.form_graph_id)">
							<option value="-1"<?php if (get_request_var_request("template_id") == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if (get_request_var_request("template_id") == "0") {?> selected<?php }?>><?php print __("None");?></option>
							<?php
							if (read_config_option("auth_method") != 0) {
								$templates = db_fetch_assoc("SELECT DISTINCT graph_templates.id, graph_templates.name
									FROM (graph_templates_graph,graph_local)
									LEFT JOIN host ON (host.id=graph_local.host_id)
									LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id)
									LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_DEVICES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
									WHERE graph_templates_graph.local_graph_id=graph_local.id
									AND graph_templates.id IS NOT NULL
									" . (empty($sql_where) ? "" : "AND $sql_where") . "
									ORDER BY name");
							}else{
								$templates = db_fetch_assoc("SELECT DISTINCT graph_templates.id, graph_templates.name
									FROM graph_templates
									ORDER BY name");
							}

							if (sizeof($templates) > 0) {
							foreach ($templates as $template) {
								print "<option value='" . $template["id"] . "'"; if (get_request_var_request("template_id") == $template["id"]) { print " selected"; } print ">" . title_trim($template["name"], 40) . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="button" Value="<?php print __("Clear");?>" name="clear_x" align="middle" onClick="clearGraphsFilterChange(document.form_graph_id)">
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td>
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyGraphsFilterChange(document.form_graph_id)">
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
			<input type='hidden' name='page' value='1'>
			<?php
			if (isset($_REQUEST["tab"])) {
				print "<input type='hidden' name='tab' value='" . $_REQUEST["tab"] . "'>\n";
				print "<input type='hidden' name='id' value='" . $_REQUEST["id"] . "'>\n";
				print "<input type='hidden' name='action' value='edit'>\n";
			}
			?>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var_request("filter"))) {
		$sql_where = "AND (graph_templates_graph.title_cache like '%%" . $_REQUEST["filter"] . "%%'" .
			" OR graph_templates.name like '%%" . get_request_var_request("filter") . "%%')";
	}else{
		$sql_where = "";
	}

	if (get_request_var_request("host_id") == "-1") {
		/* Show all items */
	}elseif (get_request_var_request("host_id") == "0") {
		$sql_where .= " AND graph_local.host_id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where .= " AND graph_local.host_id=" . $_REQUEST["host_id"];
	}

	if (get_request_var_request("template_id") == "-1") {
		/* Show all items */
	}elseif (get_request_var_request("template_id") == "0") {
		$sql_where .= " AND graph_templates_graph.graph_template_id=0";
	}elseif (!empty($_REQUEST["template_id"])) {
		$sql_where .= " AND graph_templates_graph.graph_template_id=" . $_REQUEST["template_id"];
	}

	html_start_box("", "100", $colors["header"], "0", "center", "");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_graph");
	}else{
		$rows = get_request_var_request("rows");
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(graph_templates_graph.id)
		FROM (graph_local,graph_templates_graph)
		LEFT JOIN graph_templates ON (graph_local.graph_template_id=graph_templates.id)
		WHERE graph_local.id=graph_templates_graph.local_graph_id
		$sql_where");

	$graph_list = db_fetch_assoc("SELECT
		graph_templates_graph.id,
		graph_templates_graph.local_graph_id,
		graph_templates_graph.height,
		graph_templates_graph.width,
		graph_templates_graph.title_cache,
		graph_templates.name,
		graph_local.host_id
		FROM (graph_local,graph_templates_graph)
		LEFT JOIN graph_templates ON (graph_local.graph_template_id=graph_templates.id)
		WHERE graph_local.id=graph_templates_graph.local_graph_id
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "graphs.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"title_cache" => array(__("Graph Title"), "ASC"),
		"local_graph_id" => array(__("ID"), "ASC"),
		"name" => array(__("Template Name"), "ASC"),
		"height" => array(__("Size"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($graph_list) > 0) {
		foreach ($graph_list as $graph) {
			$template_name = ((empty($graph["name"])) ? "<em>" . __("None") . "</em>" : $graph["name"]);

			form_alternate_row_color('line' . $graph["local_graph_id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("graphs.php?action=graph_edit&id=" . $graph["local_graph_id"] . "' title='" . $graph["title_cache"]) . "'>" . (($_REQUEST["filter"] != "") ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", title_trim($graph["title_cache"], read_config_option("max_title_graph"))) : title_trim($graph["title_cache"], read_config_option("max_title_graph"))) . "</a>", $graph["local_graph_id"]);
			form_selectable_cell($graph["local_graph_id"], $graph["local_graph_id"]);
			form_selectable_cell((($_REQUEST["filter"] != "") ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template_name) : $template_name), $graph["local_graph_id"]);
			form_selectable_cell($graph["height"] . "x" . $graph["width"], $graph["local_graph_id"]);
			form_checkbox_cell($graph["title_cache"], $graph["local_graph_id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>" . __("No Graphs Found") . "</em></td></tr>";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* add a list of tree names to the actions dropdown */
	$graph_actions = array_merge($graph_actions, api_tree_add_tree_names_to_actions_array());

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($graph_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
