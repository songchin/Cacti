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
include_once(CACTI_BASE_PATH . "/lib/utility.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
	case 'save':
		form_save();

		break;
	case 'item_remove':
		item_remove();

		header("Location: graphs.php?action=graph_edit&id=" . $_GET["local_graph_id"]);
		break;
	case 'item_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		item_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
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

		if (get_request_var_post("graph_type_id") == GRAPH_ITEM_TYPE_LEGEND) {
			/* this can be a major time saver when creating lots of graphs with the typical
			GPRINT LAST/AVERAGE/MAX legends */
			$items = array(
				0 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => RRD_CF_LAST,
					"text_format" => __("Current:"),
					"hard_return" => ""
					),
				1 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => RRD_CF_AVERAGE,
					"text_format" => __("Average:"),
					"hard_return" => ""
					),
				2 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => RRD_CF_MAX,
					"text_format" => __("Maximum:"),
					"hard_return" => CHECKED
					));
		}

		if ($_POST["graph_type_id"] == GRAPH_ITEM_TYPE_CUSTOM_LEGEND) {
			/* this can be a major time saver when creating lots of graphs with the typical VDEFs */
			$items = array(
				0 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => read_config_option("cl1_cf_id"),
					"vdef_id" => read_config_option("cl1_vdef_id"),
					"text_format" => read_config_option("cl1_text_format"),
					"hard_return" => read_config_option("cl1_hard_return")
					),
				1 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => read_config_option("cl2_cf_id"),
					"vdef_id" => read_config_option("cl2_vdef_id"),
					"text_format" => read_config_option("cl2_text_format"),
					"hard_return" => read_config_option("cl2_hard_return")
					),
				2 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => read_config_option("cl3_cf_id"),
					"vdef_id" => read_config_option("cl3_vdef_id"),
					"text_format" => read_config_option("cl3_text_format"),
					"hard_return" => read_config_option("cl3_hard_return")
					),
				3 => array(
					"color_id" => "0",
					"graph_type_id" => GRAPH_ITEM_TYPE_GPRINT,
					"consolidation_function_id" => read_config_option("cl4_cf_id"),
					"vdef_id" => read_config_option("cl4_vdef_id"),
					"text_format" => read_config_option("cl4_text_format"),
					"hard_return" => read_config_option("cl4_hard_return")
					),
				);
			foreach ($items as $key => $item) { #drop "empty" custom legend items
				if (empty($item["text_format"])) unset($items[$key]);
			}
		}

		foreach ($items as $item) {
			$save["id"] 				= form_input_validate($_POST["graph_template_item_id"], "graph_template_item_id", "^[0-9]+$", false, 3);
			$save["graph_template_id"] 	= form_input_validate($_POST["graph_template_id"], "graph_template_id", "^[0-9]+$", false, 3);
			$save["local_graph_template_item_id"] = form_input_validate($_POST["local_graph_template_item_id"], "local_graph_template_item_id", "^[0-9]+$", false, 3);
			$save["local_graph_id"] 	= form_input_validate($_POST["local_graph_id"], "local_graph_id", "^[0-9]+$", false, 3);
			$save["task_item_id"] 		= form_input_validate(((isset($item["task_item_id"]) ? $item["task_item_id"] : (isset($_POST["task_item_id"]) ? $_POST["task_item_id"] : 0))), "task_item_id", "^[0-9]+$", true, 3);
			$save["color_id"] 			= form_input_validate(((isset($item["color_id"]) ? $item["color_id"] : (isset($_POST["color_id"]) ? $_POST["color_id"] : 0))), "color_id", "^[0-9]+$", true, 3);
			$save["alpha"] 				= form_input_validate(((isset($item["alpha"]) ? $item["alpha"] : (isset($_POST["alpha"]) ? $_POST["alpha"] : "FF"))), "alpha", "^[a-fA-F0-9]+$", true, 3);
			$save["graph_type_id"]		= form_input_validate(((isset($item["graph_type_id"]) ? $item["graph_type_id"] : (isset($_POST["graph_type_id"]) ? $_POST["graph_type_id"] : 0))), "graph_type_id", "^[0-9]+$", true, 3);
			if (isset($_POST["line_width"]) || isset($item["line_width"])) {
				$save["line_width"] 	= form_input_validate((isset($item["line_width"]) ? $item["line_width"] : $_POST["line_width"]), "line_width", "^[0-9]+[\.,]+[0-9]+$", true, 3);
			}else { # make sure to transfer old LINEx style into line_width on save
				switch ($save["graph_type_id"]) {
					case GRAPH_ITEM_TYPE_LINE1:
						$save["line_width"] = 1;
						break;
					case GRAPH_ITEM_TYPE_LINE2:
						$save["line_width"] = 2;
						break;
					case GRAPH_ITEM_TYPE_LINE3:
						$save["line_width"] = 3;
						break;
					default:
						$save["line_width"] = 0;
				}
			}
			$save["dashes"] 			= form_input_validate((isset($_POST["dashes"]) ? $_POST["dashes"] : ""), "dashes", "^[0-9]+[,0-9]*$", true, 3);
			$save["dash_offset"] 		= form_input_validate((isset($_POST["dash_offset"]) ? $_POST["dash_offset"] : ""), "dash_offset", "^[0-9]+$", true, 3);
			$save["cdef_id"] 			= form_input_validate(((isset($item["cdef_id"]) ? $item["cdef_id"] : (isset($_POST["cdef_id"]) ? $_POST["cdef_id"] : 0))), "cdef_id", "^[0-9]+$", true, 3);
			$save["vdef_id"] 			= form_input_validate(((isset($item["vdef_id"]) ? $item["vdef_id"] : (isset($_POST["vdef_id"]) ? $_POST["vdef_id"] : 0))), "vdef_id", "^[0-9]+$", true, 3);
			$save["consolidation_function_id"] = form_input_validate(((isset($item["consolidation_function_id"]) ? $item["consolidation_function_id"] : (isset($_POST["consolidation_function_id"]) ? $_POST["consolidation_function_id"] : 0))), "consolidation_function_id", "^[0-9]+$", true, 3);
			$save["textalign"] 			= form_input_validate((isset($_POST["textalign"]) ? $_POST["textalign"] : ""), "textalign", "^[a-z]+$", true, 3);
			$save["text_format"] 		= form_input_validate(((isset($item["text_format"]) ? $item["text_format"] : (isset($_POST["text_format"]) ? $_POST["text_format"] : ""))), "text_format", "", true, 3);
			$save["value"] 				= form_input_validate((isset($_POST["value"]) ? $_POST["value"] : ""), "value", "", true, 3);
			$save["hard_return"] 		= form_input_validate(((isset($item["hard_return"]) ? $item["hard_return"] : (isset($_POST["hard_return"]) ? $_POST["hard_return"] : ""))), "hard_return", "", true, 3);
			$save["gprint_id"] 			= form_input_validate(((isset($item["gprint_id"]) ? $item["gprint_id"] : (isset($_POST["gprint_id"]) ? $_POST["gprint_id"] : 0))), "gprint_id", "^[0-9]+$", true, 3);
			/* generate a new sequence if needed */
			if (empty($_POST["sequence"])) {
				$_POST["sequence"] 		= get_sequence($_POST["sequence"], "sequence", "graph_templates_item", "graph_template_id=" . $_POST["graph_template_id"] . " and local_graph_id=0");
			}
			$save["sequence"] 			= form_input_validate($_POST["sequence"], "sequence", "^[0-9]+$", false, 3);

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
		}else{
			header("Location: graphs.php?action=graph_edit&id=" . $_POST["local_graph_id"]);
		}
		exit;
	}
}

/* -----------------------
    item - Graph Items
   ----------------------- */

function item_movedown() {
	global $graph_item_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("local_graph_id"));
	/* ==================================================== */

	$arr = get_graph_group($_GET["id"]);
	$next_id = get_graph_parent($_GET["id"], "next");

	$graph_type_id = db_fetch_cell("select graph_type_id from graph_templates_item where id=" . $_GET["id"]);
	if ((!empty($next_id)) && (isset($arr{$_GET["id"]}))) {
		move_graph_group(get_request_var("id"), $arr, $next_id, "next");
	}elseif ($graph_type_id == GRAPH_ITEM_TYPE_GPRINT ||
			$graph_type_id == GRAPH_ITEM_TYPE_HRULE ||
			$graph_type_id == GRAPH_ITEM_TYPE_VRULE ||
			$graph_type_id == GRAPH_ITEM_TYPE_COMMENT) {
		move_item_down("graph_templates_item", $_GET["id"], "local_graph_id=" . $_GET["local_graph_id"]);
	}
}

function item_moveup() {
	global $graph_item_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("local_graph_id"));
	/* ==================================================== */

	$arr = get_graph_group($_GET["id"]);
	$previous_id = get_graph_parent($_GET["id"], "previous");

	$graph_type_id = db_fetch_cell("select graph_type_id from graph_templates_item where id=" . $_GET["id"]);
	if ((!empty($previous_id)) && (isset($arr{$_GET["id"]}))) {
		move_graph_group(get_request_var("id"), $arr, $previous_id, "previous");
	}elseif ($graph_type_id == GRAPH_ITEM_TYPE_GPRINT ||
			$graph_type_id == GRAPH_ITEM_TYPE_HRULE ||
			$graph_type_id == GRAPH_ITEM_TYPE_VRULE ||
			$graph_type_id == GRAPH_ITEM_TYPE_COMMENT) {
		move_item_up("graph_templates_item", $_GET["id"], "local_graph_id=" . $_GET["local_graph_id"]);
	}
}

function item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	db_execute("delete from graph_templates_item where id=" . $_GET["id"]);
}

function item_edit() {
	global $colors, $struct_graph_item, $graph_item_types, $consolidation_functions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("id"));
	input_validate_input_number(get_request_var_request("device_id"));
	input_validate_input_number(get_request_var_request("graph_template_id"));
	input_validate_input_number(get_request_var_request("local_graph_id"));
	input_validate_input_number(get_request_var_request("device_id"));
	input_validate_input_number(get_request_var_request("data_template_id"));
	/* ==================================================== */

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("local_graph_id", "sess_local_graph_id", "");
	load_current_session_value("device_id", "sess_ds_device_id", "-1");
	load_current_session_value("data_template_id", "sess_data_template_id", "-1");

	$id = (!empty($_REQUEST["id"]) ? "&id=" . get_request_var_request("id") : "");
	$device = db_fetch_row("select hostname from device where id=" . get_request_var_request("device_id"));

	html_start_box("<strong>" . __("Data Sources") . "</strong> " . __("[device: ") . (empty($device["hostname"]) ? __("No Host") : $device["hostname"]) . "]", "100", $colors["header"], "3", "center", "");

	?>
	<tr>
		<td>
			<form name="form_graph_items">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td class="nw50">
						<?php print __("Host");?>:&nbsp;
					</td>
					<td>
						<select name="cbo_device_id" onChange="window.location=document.form_graph_items.cbo_device_id.options[document.form_graph_items.cbo_device_id.selectedIndex].value">
							<option value="<?php print htmlspecialchars("graphs_items.php?action=item_edit" . $id . "&local_graph_id=" . get_request_var_request("local_graph_id") . "&device_id=-1&data_template_id=" . get_request_var_request("data_template_id"));?>"<?php if (get_request_var_request("device_id") == "-1") {?> selected<?php }?>>Any</option>
							<option value="<?php print htmlspecialchars("graphs_items.php?action=item_edit" . $id . "&local_graph_id=" . get_request_var_request("local_graph_id") . "&device_id=0&data_template_id=" . get_request_var_request("data_template_id"));?>"<?php if (get_request_var_request("device_id") == "0") {?> selected<?php }?>>None</option>
							<?php
							$devices = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from device order by description,hostname");

							if (sizeof($devices) > 0) {
								foreach ($devices as $device) {
									print "<option value='" . htmlspecialchars("graphs_items.php?action=item_edit" . $id . "&local_graph_id=" . get_request_var_request("local_graph_id") . "&device_id=" . $device["id"] . "&data_template_id=" . get_request_var_request("data_template_id")) . "'"; if (get_request_var_request("device_id") == $device["id"]) { print " selected"; } print ">" . $device["name"] . "</option>\n";
								}
							}
							?>

						</select>
					</td>
				</tr>
				<tr>
					<td class="nw100">
						<?php print __("Data Source Template:");?>&nbsp;
					</td>
					<td>
						<select name="cbo_data_template_id" onChange="window.location=document.form_graph_items.cbo_data_template_id.options[document.form_graph_items.cbo_data_template_id.selectedIndex].value">
							<option value="graphs_items.php?action=item_edit<?php print $id; ?>&local_graph_id=<?php print get_request_var_request("local_graph_id");?>&data_template_id=-1&device_id=<?php print get_request_var_request("device_id");?>"<?php if (get_request_var_request("data_template_id") == "-1") {?> selected<?php }?>>Any</option>
							<option value="graphs_items.php?action=item_edit<?php print $id; ?>&local_graph_id=<?php print get_request_var_request("local_graph_id");?>&data_template_id=0&device_id=<?php print get_request_var_request("device_id");?>"<?php if (get_request_var_request("data_template_id") == "0") {?> selected<?php }?>>None</option>
							<?php
							$data_templates = db_fetch_assoc("select id, name from data_template order by name");

							if (sizeof($data_templates) > 0) {
								foreach ($data_templates as $data_template) {
									print "<option value='graphs_items.php?action=item_edit" . $id . "&local_graph_id=" . get_request_var_request("local_graph_id") . "&data_template_id=" . $data_template["id"]. "&device_id=" . get_request_var_request("device_id") . "'"; if (get_request_var_request("data_template_id") == $data_template["id"]) { print " selected"; } print ">" . $data_template["name"] . "</option>\n";
								}
							}
							?>

						</select>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<?php
	html_end_box();

	if (get_request_var_request("device_id") == "-1") {
		$sql_where = "";
	}elseif (get_request_var_request("device_id") == "0") {
		$sql_where = " data_local.device_id=0 and ";
	}elseif (!empty($_REQUEST["device_id"])) {
		$sql_where = " data_local.device_id=" . get_request_var_request("device_id") . " and ";
	}

	if (get_request_var_request("data_template_id") == "-1") {
		$sql_where .= "";
	}elseif (get_request_var_request("data_template_id") == "0") {
		$sql_where .= " data_local.data_template_id=0 and ";
	}elseif (!empty($_REQUEST["data_template_id"])) {
		$sql_where .= " data_local.data_template_id=" . get_request_var_request("data_template_id") . " and ";
	}

	if (!empty($_REQUEST["id"])) {
		$template_item = db_fetch_row("select * from graph_templates_item where id=" . get_request_var_request("id"));
		$device_id = db_fetch_cell("select device_id from graph_local where id=" . get_request_var_request("local_graph_id"));
	}

	/* by default, select the LAST DS chosen to make everyone's lives easier */
	if (!empty($_REQUEST["local_graph_id"])) {
		$default = db_fetch_row("select task_item_id from graph_templates_item where local_graph_id=" . get_request_var_request("local_graph_id") . " order by sequence DESC");

		if (sizeof($default) > 0) {
			$struct_graph_item["task_item_id"]["default"] = $default["task_item_id"];
		}else{
			$struct_graph_item["task_item_id"]["default"] = 0;
		}

		/* modifications to the default graph items array */
		$struct_graph_item["task_item_id"]["sql"] = "select
			CONCAT_WS('',data_template_data.name_cache,' (',data_template_rrd.data_source_name,')') as name,
			data_template_rrd.id
			from (data_template_data,data_template_rrd,data_local)
			left join device on (data_local.device_id=device.id)
			where data_template_rrd.local_data_id=data_local.id
			and data_template_data.local_data_id=data_local.id ";
		/* Make sure we don't limit the list so that the selected DS isn't in the list in edit mode */
		if (strlen($sql_where) > 0) {
			$sql_where = substr($sql_where,0,-5);
			if (!empty($_REQUEST["id"])) {
				$struct_graph_item["task_item_id"]["sql"] .= " and ((" . $sql_where .  ") or (data_template_rrd.id = " .  $template_item["task_item_id"] . "))";
			} else {
				$struct_graph_item["task_item_id"]["sql"] .= " and (" . $sql_where . ")";
			}
		}
		$struct_graph_item["task_item_id"]["sql"] .= " order by name";
	}

	$form_array = array();

	while (list($field_name, $field_array) = each($struct_graph_item)) {
		$form_array += array($field_name => $struct_graph_item[$field_name]);

		$form_array[$field_name]["value"] = (isset($template_item) ? $template_item[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($template_item) ? $template_item["id"] : "0");
	}


	if (!empty($_GET["local_graph_id"])) {
		$header_label = __("[edit: ") . db_fetch_cell("select title_cache from graph_templates_graph where local_graph_id=" . get_request_var_request("local_graph_id")) . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='graph_item_edit'>\n";
	html_start_box("<strong>" . __("Graph Items") . "</strong> $header_label", "100", $colors["header"], 0, "center", "", true);
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_graph_item_edit');
	draw_edit_form(
		array(
			"config" => array("no_form_tag" => true),
			"fields" => $form_array
			)
		);

	form_hidden_box("local_graph_id", get_request_var_request("local_graph_id"), "0");
	form_hidden_box("graph_template_item_id", (isset($template_item) ? $template_item["id"] : "0"), "");
	form_hidden_box("local_graph_template_item_id", (isset($template_item) ? $template_item["local_graph_template_item_id"] : "0"), "");
	form_hidden_box("graph_template_id", (isset($template_item) ? $template_item["graph_template_id"] : "0"), "");
#	form_hidden_box("sequence", (isset($template_item) ? $template_item["sequence"] : "0"), "");
#	form_hidden_box("_graph_type_id", (isset($template_item) ? $template_item["graph_type_id"] : "0"), "");
#	form_hidden_box("hidden_task_item_id", (isset($template_item) ? $template_item["task_item_id"] : "0"), "");
	form_hidden_box("save_component_item", "1", "");
	form_hidden_box("hidden_rrdtool_version", read_config_option("rrdtool_version"), "");

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	form_save_button_alt("path!graphs.php|action!graph_edit|id!" . get_request_var_request("local_graph_id"));

	include_once(CACTI_BASE_PATH . "/access/js/graph_item_dependencies.js");	# this one modifies attr("disabled")
	include_once(CACTI_BASE_PATH . "/access/js/line_width.js");
	include_once(CACTI_BASE_PATH . "/access/js/rrdtool_version.js");			# this one sets attr("disabled) and comes last!
}
