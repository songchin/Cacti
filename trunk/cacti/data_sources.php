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
include_once("./lib/utility.php");
include_once("./lib/graph/graph_update.php");
include_once("./lib/data_source/data_source_update.php");
include_once("./lib/data_source/data_source_info.php");
include_once("./lib/data_source/data_source_template.php");
include_once("./lib/data_query/data_query_info.php");
include_once("./include/data_source/data_source_constants.php");
include_once("./include/data_source/data_source_form.php");
include_once("./lib/template.php");
include_once("./lib/html_form_template.php");
include_once("./lib/rrd.php");

define("MAX_DISPLAY_PAGES", 21);

$ds_actions = array(
	1 => _("Delete"),
	2 => _("Change Data Template"),
	3 => _("Change Host"),
	4 => _("Duplicate"),
	5 => _("Convert to Data Template")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item_add':
		include_once("./include/top_header.php");

		ds_edit();

		include_once ("./include/bottom_footer.php");
		break;
	case 'item_remove':
		ds_item_remove();

		break;
	case 'edit':
		include_once("./include/top_header.php");

		ds_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		ds();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	/* fetch some cache variables */
	if (empty($_POST["id"])) {
		$_data_template_id = 0;
	}else{
		$_data_template_id = db_fetch_cell("select data_template_id from data_source where id=" . $_POST["id"]);
	}

	$data_source_fields = array();
	$data_source_item_fields = array();
	$data_input_fields = array();

	/* parse out form values that we care about (data source / data source item fields) */
	reset($_POST);
	while (list($name, $value) = each($_POST)) {
		if (substr($name, 0, 4) == "dsi|") {
			$matches = explode("|", $name);
			$data_source_item_fields{$matches[2]}{$matches[1]} = $value;
		}else if (substr($name, 0, 4) == "dif_") {
			$data_input_fields{substr($name, 4)} = $value;
		}else if (substr($name, 0, 3) == "ds|") {
			$matches = explode("|", $name);
			$data_source_fields{$matches[1]} = $value;
		}
	}

	/* save code for templated data sources */
	if (!empty($_data_template_id)) {
		$data_source_id = set_data_template($_POST["id"], $_POST["data_template_id"], $_POST["host_id"], $data_source_fields, $data_source_item_fields, $data_input_fields);
	}

	/* save code for non-templated data sources */
	if ((empty($_data_template_id)) && (isset($_POST["save_component_data_source"]))) {
		$data_source_id = api_data_source_save($_POST["id"], $_POST["host_id"], $_POST["data_template_id"], $_POST["data_input_type"],
			$data_input_fields, $data_source_fields["name"], (isset($data_source_fields["active"]) ? $data_source_fields["active"] : ""), $data_source_fields["rrd_path"],
			$data_source_fields["rrd_step"], $data_source_fields["rra_id"], "ds||field|", "dif_|field|");

		while (list($data_source_item_id, $fields) = each($data_source_item_fields)) {
			api_data_source_item_save($data_source_item_id, $data_source_id, $fields["rrd_maximum"],
				$fields["rrd_minimum"], $fields["rrd_heartbeat"], $fields["data_source_type"], $fields["data_source_name"],
				(isset($fields["field_input_value"]) ? $fields["field_input_value"] : ""), "dsi||field|||id|");
		}
	}

	if ((is_error_message()) || (empty($data_source_id)) || ($_POST["data_template_id"] != $_data_template_id)) {
		if (isset($_POST["redirect_item_add"])) {
			$action = "item_add";
		}else{
			$action = "edit";
		}

		header("Location: data_sources.php?action=$action" . (empty($_POST["id"]) ? "" : "&id=" . $_POST["id"]) . (!isset($_POST["host_id"]) ? "" : "&host_id=" . $_POST["host_id"]) . (!isset($_POST["data_template_id"]) ? "" : "&data_template_id=" . $_POST["data_template_id"]) . (isset($_POST["data_input_type"]) ? "&data_input_type=" . $_POST["data_input_type"] : "") . (isset($_POST["dif_script_id"]) ? "&script_id=" . $_POST["dif_script_id"] : "") . (isset($_POST["dif_data_query_id"]) ? "&data_query_id=" . $_POST["dif_data_query_id"] : ""));
	}else{
		header("Location: data_sources.php");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $ds_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 1; }

			switch ($_POST["delete_type"]) {
				case '2': /* delete all graph items tied to this data source */
					$data_source_items = db_fetch_assoc("select id from data_source_item where " . array_to_sql_or($selected_items, "data_source_id"));

					/* loop through each data source item */
					if (sizeof($data_source_items) > 0) {
						foreach ($data_source_items as $item) {
							db_execute("delete from graph_item where data_source_item_id = " . $item["id"]);
						}
					}

					break;
				case '3': /* delete all graphs tied to this data source */
					$graphs = db_fetch_assoc("select distinct
						graph.id
						from data_source_item,graph_item,graph
						where graph_item.data_source_item_id=data_source_item.id
						and graph_item.graph_id=graph.id
						and " . array_to_sql_or($selected_items, "data_source_item.data_source_id"));

					if (sizeof($graphs) > 0) {
						foreach ($graphs as $graph) {
							api_graph_remove($graph["id"]);
						}
					}

					break;
				}

				for ($i=0;($i<count($selected_items));$i++) {
					api_data_source_remove($selected_items[$i]);
				}
		}elseif ($_POST["drp_action"] == "2") { /* change graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				change_data_template($selected_items[$i], $_POST["data_template_id"]);
			}
		}elseif ($_POST["drp_action"] == "3") { /* change host */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("update data_local set host_id=" . $_POST["host_id"] . " where id=" . $selected_items[$i]);
				push_out_host($_POST["host_id"], $selected_items[$i]);
				update_data_source_title_cache($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "4") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_data_source($selected_items[$i], 0, $_POST["title_format"]);
			}
		}elseif ($_POST["drp_action"] == "5") { /* data source -> data template */
			for ($i=0;($i<count($selected_items));$i++) {
				data_source_to_data_template($selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: data_sources.php");
		exit;
	}

	/* setup some variables */
	$ds_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$ds_list .= "<li>" . get_data_source_title($matches[1]) . "<br>";
			$ds_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $ds_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='data_sources.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		$graphs = array();

		/* find out which (if any) graphs are using this data source, so we can tell the user */
		if (isset($ds_array)) {
			$graphs = db_fetch_assoc("select distinct
				graph.id,
				graph.title_cache
				from data_source_item,graph_item,graph
				where graph_item.data_source_item_id=data_source_item.id
				and graph_item.graph_id=graph.id
				and " . array_to_sql_or($ds_array, "data_source_item.data_source_id") . "
				order by graph.title_cache");
		}

		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Are you sure you want to delete the following data sources?") . "</p>
					<p>$ds_list</p>
					";
					if (sizeof($graphs) > 0) {
						print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td class='textArea'><p class='textArea'>The following graphs are using these data sources:</p>\n";

						foreach ($graphs as $graph) {
							print "<strong>" . $graph["title_cache"] . "</strong><br>\n";
						}

						print "<br>";
						form_radio_button("delete_type", "3", "1", _("Leave the graphs untouched."), "1"); print "<br>";
						form_radio_button("delete_type", "3", "2", _("Delete all <strong>graph items</strong> that reference these data sources."), "1"); print "<br>";
						form_radio_button("delete_type", "3", "3", _("Delete all <strong>graphs</strong> that reference these data sources."), "1"); print "<br>";
						print "</td></tr>";
					}
				print "
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* change graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>"._("Choose a data template and click save to change the data template for
					the following data souces. Be aware that all warnings will be suppressed during the
					conversion, so graph data loss is possible.")."</p>
					<p>$ds_list</p>
					<p><strong>"._("New Data Template:")."</strong><br>"; form_dropdown("data_template_id",db_fetch_assoc("select data_template.id,data_template.name from data_template order by data_template.name"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "3") { /* change host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>"._("Choose a new host for these data sources:")."</p>
					<p>$ds_list</p>
					<p><strong>"._("New Host:")."</strong><br>"; form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "4") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following data sources will be duplicated. You can
					optionally change the title format for the new data sources.") . "</p>
					<p>$ds_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "5") { /* graph -> graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following data sources will be converted into data templates.
					You can optionally change the title format for the new data templates.") . "</p>
					<p>$ds_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> " . _("Template"), "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($ds_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one data source.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='". html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($ds_array) ? serialize($ds_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='data_sources.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* ------------------------
    Data Source Functions
   ------------------------ */

function ds_item_remove() {
	api_data_source_item_remove($_GET["id"]);

	header("Location: data_sources.php?action=edit&id=" . $_GET["data_source_id"]);
}

function ds_edit() {
	global $colors, $struct_data_source, $struct_data_source_item, $data_source_types;
	global $struct_data_input, $struct_data_input_script, $struct_data_input_snmp, $struct_data_input_data_query;

	$host_id = 0;

	if (!empty($_GET["id"])) {
		$data_source = db_fetch_row("select * from data_source where id = " . $_GET["id"]);
		$data_source_items = db_fetch_assoc("select * from data_source_item where data_source_id = " . $_GET["id"]);

		if (!empty($data_source["data_template_id"])) {
			$data_template = db_fetch_row("select id,name from data_template where id='" . $data_source["data_template_id"] . "'");
		}

		$header_label = _("[edit: ") . get_data_source_title($_GET["id"]) . "]";

		/* get a list of all data input type fields for this data template */
		$data_input_type_fields = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = " . $data_source["id"]), "name", array("value"));
	}else{
		$header_label = _("[new]");

		$data_input_type_fields = array();
	}

	/* handle debug mode */
	if (isset($_GET["debug"])) {
		if ($_GET["debug"] == "0") {
			kill_session_var("ds_debug_mode");
		}elseif ($_GET["debug"] == "1") {
			$_SESSION["ds_debug_mode"] = true;
		}
	}

	if (!empty($_GET["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td class="textInfo" colspan="2" valign="top">
					<?php print get_data_source_title($_GET["id"]);?>
				</td>
				<td class="textInfo" align="right" valign="top">
					<span style="color: #c16921;">*<a href='data_sources.php?action=edit&id=<?php print (isset($_GET["id"]) ? $_GET["id"] : 0);?>&debug=<?php print (isset($_SESSION["ds_debug_mode"]) ? "0" : "1");?>'>Turn <strong><?php print (isset($_SESSION["ds_debug_mode"]) ? "Off" : "On");?></strong> Data Source Debug Mode.</a>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	/* ==================== Box: Device/Template Selection ==================== */

	$form_array = array(
		"data_template_id" => array(
			"method" => "drop_sql",
			"friendly_name" => _("Selected Data Template"),
			"description" => _("The name given to this data template."),
			"value" => (isset($data_source) ? $data_source["data_template_id"] : "0"),
			"none_value" => "None",
			"sql" => "select id,template_name as name from data_template order by template_name"
			),
		"host_id" => array(
			"method" => "drop_sql",
			"friendly_name" => _("Device"),
			"description" => _("Choose the device that this graph belongs to."),
			"value" => (isset($_GET["host_id"]) ? $_GET["host_id"] : $data_source["host_id"]),
			"none_value" => _("None"),
			"sql" => "select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"
			),
		"id" => array(
			"method" => "hidden",
			"value" => (isset($data_source) ? $data_source["id"] : "0")
			)
		);

	// get_data_query_array()
	$form_data_query_fields = array(
		"hdr_snmp_generic" => array(
			"friendly_name" => _("Data Query Parameters"),
			"method" => "spacer"
			),
		"dif_data_query_field_name" => array(
			"method" => "drop_sql",
			"friendly_name" => _("Field Name"),
			"description" => _("Determines the field that Cacti will use when locating a unique row for this dat)a query."),
			"value" => (isset($data_input_type_fields["data_query_field_name"]["value"]) ? $data_input_type_fields["data_query_field_name"]["value"] : ""),
			"none_value" => "",
			"sql" => "select field_name as name,field_name as id from host_snmp_cache where snmp_query_id = '|data_query_id|' group by field_name"
			),
		"dif_data_query_field_value" => array(
			"method" => "textbox",
			"friendly_name" => _("Field Value"),
			"description" => _("When assigned to the field name above, produces a single data query row used by th)e poller to retrieve data."),
			"value" => (isset($data_input_type_fields["data_query_field_value"]["value"]) ? $data_input_type_fields["data_query_field_value"]["value"] : ""),
			"max_length" => "100",
			"size" => "30"
			)
		);

	html_start_box("<strong>" . _("Device/Template Selection") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	html_end_box();

	/* ==================== Box: Supplemental Template Data ==================== */

	/* only display the "inputs" area if we are using a data template for this data source */
	if (!empty($data_source["data_template_id"])) {
		ob_start();

		html_start_box("<strong>"._("Supplemental Template Data")."</strong>", "98%", $colors["header_background"], "3", "center", "");

		$num_output_fields =  draw_nontemplated_fields_data_input($data_source["data_template_id"], $data_input_type_fields, "dif_|field|", "<strong>"._("Data Input")."</strong>", true);
		$num_output_fields += draw_nontemplated_fields_data_source($data_source["data_template_id"], $data_source, "ds||field|", "<strong>" . _("Data Source Fields") . "</strong>", true, true);
		$num_output_fields += draw_nontemplated_fields_data_source_item($data_source["data_template_id"], db_fetch_assoc("select * from data_source_item where data_source_id = " . $data_source["id"] . " order by data_source_name"), "dsi||field|||id|", "<strong>" . _("Data Source Item Fields") . "</strong>", true, true);

		$form_data_query_fields["dif_data_query_field_name"]["sql"] = str_replace("|data_query_id|", $data_input_type_fields["data_query_id"]["value"], $form_data_query_fields["dif_data_query_field_name"]["sql"]);
		draw_edit_form(array("config" => array("no_form_tag" => true), "fields" => $form_data_query_fields));

		html_end_box();

		if ($num_output_fields == 0) {
			ob_end_clean();
		}else{
			ob_end_flush();
		}
	}

	if ( (empty($data_source["data_template_id"])) && ( ((isset($_GET["id"])) && (is_numeric($_GET["id"]))) || ((isset($_GET["host_id"])) && (isset($_GET["data_template_id"]))) ) ) {
		/* ==================== Box: Data Input ==================== */

		/* determine current value for 'data_input_type' */
		if (isset($_GET["data_input_type"])) {
			$_data_input_type = $_GET["data_input_type"];
		}else if (isset($data_source["data_input_type"])) {
			$_data_input_type = $data_source["data_input_type"];
		}else{
			$_data_input_type = DATA_INPUT_TYPE_SCRIPT;
		}

		$_data_input_form = array("data_input_type" => $struct_data_input["data_input_type"]);

		/* fill in data source-specific information (data input type dropdown) */
		$_data_input_form["data_input_type"]["redirect_url"] = "data_sources.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=|dropdown_value|";
		$_data_input_form["data_input_type"]["form_index"] = "0";
		$_data_input_form["data_input_type"]["default"] = "script";
		$_data_input_form["data_input_type"]["value"] = $_data_input_type;

		/* grab the appropriate data input type form array */
		if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
			$_data_input_type_form = $struct_data_input_script;

			/* since the "sql" key is not executed until draw_edit_form(), we have fetch the list of
			 * external scripts here as well */
			$scripts = db_fetch_assoc($_data_input_type_form["dif_script_id"]["sql"]);

			if (sizeof($scripts) > 0) {
				/* determine current value for 'script_id' */
				if ((isset($_GET["script_id"])) && (is_numeric($_GET["script_id"]))) {
					$_script_id = $_GET["script_id"];
				}else if (isset($data_input_type_fields["script_id"])) {
					$_script_id = $data_input_type_fields["script_id"]["value"];
				}else{
					/* default to the first item in the script list */
					$_script_id = $scripts[0]["id"];
				}

				/* fill in data template-specific information (external scripts dropdown) */
				$_data_input_type_form["dif_script_id"]["redirect_url"] = "data_sources.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=$_data_input_type&script_id=|dropdown_value|";
				$_data_input_type_form["dif_script_id"]["form_index"] = "0";
				$_data_input_type_form["dif_script_id"]["value"] = $_script_id;

				foreach ($scripts as $item) {
					$_data_input_type_form["dif_script_id"]["array"]{$item["id"]} = $item["name"];
				}

				/* get each INPUT field for this script */
				$script_input_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='in' order by name");

				if (sizeof($script_input_fields) > 0) {
					$_data_input_type_form += array(
						"hdr_script_custom_fields" => array(
							"friendly_name" => _("Custom Input Fields"),
							"method" => "spacer"
							)
						);

					foreach ($script_input_fields as $field) {
						$_data_input_type_form += array(
							"dif_" . $field["data_name"] => array(
								"method" => "textbox",
								"friendly_name" => $field["name"],
								"value" => ((isset($data_input_type_fields{$field["data_name"]})) ? $data_input_type_fields{$field["data_name"]}["value"] : ""),
								"max_length" => "255",
								)
							);
					}
				}
			}
		}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
			$_data_input_type_form = $struct_data_input_data_query;

			/* since the "sql" key is not executed until draw_edit_form(), we have fetch the list of
			 * data queries here as well */
			$data_queries = db_fetch_assoc($_data_input_type_form["dif_data_query_id"]["sql"]);

			/* determine current value for 'data_query_id' */
			if ((isset($_GET["data_query_id"])) && (is_numeric($_GET["data_query_id"]))) {
				$_data_query_id = $_GET["data_query_id"];
			}else if (isset($data_input_type_fields["data_query_id"])) {
				$_data_query_id = $data_input_type_fields["data_query_id"]["value"];
			}else{
				/* default to the first item in the data query list */
				$_data_query_id = $data_queries[0]["id"];
			}

			/* fill in data template-specific information (data queries dropdown) */
			$_data_input_type_form["dif_data_query_id"]["redirect_url"] = "data_sources.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=$_data_input_type&data_query_id=|dropdown_value|";
			$_data_input_type_form["dif_data_query_id"]["form_index"] = "0";
			$_data_input_type_form["dif_data_query_id"]["value"] = $_data_query_id;

			if (sizeof($data_queries) > 0) {
				foreach ($data_queries as $item) {
					$_data_input_type_form["dif_data_query_id"]["array"]{$item["id"]} = $item["name"];
				}
			}

			/* per-data source data query parameters */
			$_data_input_type_form += $form_data_query_fields;
			$_data_input_type_form["dif_data_query_field_name"]["sql"] = str_replace("|data_query_id|", $_data_query_id, $_data_input_type_form["dif_data_query_field_name"]["sql"]);
		}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
			while (list($field_name, $field_array) = each($struct_data_input_snmp)) {
				$struct_data_input_snmp[$field_name]["value"] =  (isset($data_input_type_fields{substr($field_name, 4)}) ? $data_input_type_fields{substr($field_name, 4)}["value"] : "");
			}

			$_data_input_type_form = $struct_data_input_snmp;
		}else{
			$_data_input_type_form = array();
		}

		$_data_input_form += $_data_input_type_form;

		html_start_box("<strong>"._("Data Input")."</strong>", "98%", $colors["header_background_template"], "3", "center", "");

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
				),
				"fields" => inject_form_variables($_data_input_form, (isset($data_sources) ? $data_sources : array()), $data_input_type_fields)
				)
			);

		html_end_box();

		/* ==================== Box: Data Source ==================== */

		html_start_box("<strong>" . _("Data Source") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		$form_array = array();

		while (list($field_name, $field_array) = each($struct_data_source)) {
			$form_array += array("ds|$field_name" => $struct_data_source[$field_name]);
		}

		/* data source specific tables */
		$form_array["ds|rra_id"]["sql"] = "select rra_id as id,data_source_id from data_source_rra where data_source_id=|arg1:id|";
		$form_array["ds|rra_id"]["sql_print"] = "select rra.name from data_source_rra,rra where data_source_rra.rra_id=rra.id and data_source_rra.data_source_id=|arg1:id|";

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
					),
				"fields" => inject_form_variables($form_array, (isset($data_source) ? $data_source : array()))
				)
			);

		html_end_box();

		/* ==================== Box: Data Source Item ==================== */

		html_start_box("<strong>" . _("Data Source Item") . "</strong>", "98%", $colors["header_background"], "3", "center", (empty($_GET["id"]) ? "" : "javascript:document.forms[0].action.value='item_add';submit_redirect(0, '" . htmlspecialchars("data_sources.php?action=item_add&id=" . $_GET["id"]) . "', '')"));

		/* the user clicked the "add item" link. we need to make sure they get redirected back to
		 * this page if an error occurs */
		if ($_GET["action"] == "item_add") {
			form_hidden_box("redirect_item_add", "x", "");
		}

		/* this allows a "blank" data template item to be displayed when the user wants to create
		 * a new one */
		if ((!isset($data_source_items)) || (sizeof($data_source_items) == 0) || ($_GET["action"] == "item_add")) {
			if (isset($data_source_items)) {
				$next_index = sizeof($data_source_items);
			}else{
				$next_index = 0;
			}

			$data_source_items[$next_index] = array();
		}

		if (sizeof($data_source_items) > 0) {
			if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
				$script_output_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='out' order by name");
				$field_input_description = _("Script Output Field");
			}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
				$data_query_xml = get_data_query_array($_data_query_id);
				$data_query_output_fields = array();

				while (list($field_name, $field_array) = each($data_query_xml["fields"])) {
					if ($field_array["direction"] == "output") {
						$data_query_output_fields[$field_name] = $field_name . " (" . $field_array["name"] . ")";
					}
				}

				$field_input_description = _("Data Query Output Field");
			}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
				$field_input_description = _("SNMP OID");
			}

			foreach ($data_source_items as $item) {
				if ($_data_input_type != DATA_INPUT_TYPE_NONE) {
					?>
					<tr bgcolor="<?php print $colors["header_panel_background"];?>">
						<td class='textSubHeaderDark'>
							<?php print (isset($item["data_source_name"]) ? $item["data_source_name"] : "(" . _("New Data Source Item") . ")");?>
						</td>
						<td class='textSubHeaderDark' align='right'>
							<?php
							if ((isset($item["id"])) && (sizeof($data_source_items) > 1)) {
								print "[<a href='data_sources.php?action=item_remove&id=" . $item["id"] . "&data_source_id=" . $item["data_source_id"] . "' class='linkOverDark'>remove</a>]\n";
							}
							?>
						</td>
					</tr>
					<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
						<td width="50%" style="border-bottom: 1px dashed gray;">
							<font class='textEditTitle'>Field Input: <?php print $field_input_description;?></font><br>
						</td>
						<td style="border-bottom: 1px dashed gray;">
							<?php
							if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
								form_dropdown("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), $script_output_fields, "name", "data_name", (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "");
							}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
								form_dropdown("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), $data_query_output_fields, "", "", (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "");
							}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
								form_text_box("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "100", 40, "text", 0);
							}
							?>
						</td>
					</tr>
					<?php
				}

				$form_array = array();

				reset($struct_data_source_item);
				while (list($field_name, $field_array) = each($struct_data_source_item)) {
					$_field_name = "dsi|$field_name|" . (isset($item["id"]) ? $item["id"] : "0");

					$form_array += array($_field_name => $struct_data_source_item[$field_name]);

					$form_array[$_field_name]["value"] = (isset($item[$field_name]) ? $item[$field_name] : "");
				}

				draw_edit_form(
					array(
						"config" => array(
							"no_form_tag" => true
							),
						"fields" => $form_array
						)
					);
			}
		}

		html_end_box();
	}

	/* display the debug mode box if the user wants it */
	if ((isset($_SESSION["ds_debug_mode"])) && (isset($_GET["id"]))) {
		html_start_box("<strong>Data Source Debug</strong>", "98%", $colors["header_background"], "3", "center", "");
		print "<tr><td bgcolor=#'" . $colors["messagebar_background"] . "' <pre>" . rrdtool_function_create($_GET["id"], true, array()) . "</pre></td></tr>";
		html_end_box();
	}

	if ((isset($_GET["id"])) || ((isset($_GET["host_id"])) && (isset($_GET["data_template_id"])))) {
		form_hidden_box("save_component_data_source","1","");
	}else{
		form_hidden_box("save_component_data_source_new","1","");
	}

	form_save_button("data_sources.php");
}

function ds() {
	global $colors, $ds_actions, $data_input_types;

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_ds_current_page");
		kill_session_var("sess_ds_filter");
		kill_session_var("sess_ds_host_id");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["host_id"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_ds_current_page", "1");
	load_current_session_value("filter", "sess_ds_filter", "");
	load_current_session_value("host_id", "sess_ds_host_id", "-1");

	$host = db_fetch_row("select hostname from host where id=" . $_REQUEST["host_id"]);

	html_start_box("<strong>Data Sources</strong> " . _("[host: ") . (empty($host["hostname"]) ? _("No Host") : $host["hostname"]) . "]", "98%", $colors["header_background"], "3", "center", "data_sources.php?action=edit&host_id=" . $_REQUEST["host_id"]);

	include("./include/html/inc_data_source_filter_table.php");

	html_end_box();

	/* form the 'where' clause for our main sql query */
	$sql_where = "where data_source.name_cache like '%%" . $_REQUEST["filter"] . "%%'";

	if ($_REQUEST["host_id"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where .= " and data_source.host_id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where .= " and data_source.host_id=" . $_REQUEST["host_id"];
	}

	$total_rows = db_fetch_cell("select
		count(*) from data_source
		$sql_where");

	$data_sources = db_fetch_assoc("select
		data_source.id,
		data_source.name_cache,
		data_source.active,
		data_source.data_input_type,
		data_template.template_name as data_template_name,
		data_source.host_id
		from data_source
		left join data_template
		on data_source.data_template_id=data_template.id
		$sql_where
		order by data_source.name_cache,data_source.host_id
		limit " . (read_config_option("num_rows_data_source")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_data_source"));

	html_start_box("", "98%", $colors["header_background"], "3", "center", "");

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_data_source"), $total_rows, "data_sources.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"]);

	$nav = "<tr bgcolor='#" . $colors["header_background"] . "'>
			<td colspan='5'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='data_sources.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"] . "&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= _("Previous"); if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
						</td>\n
						<td align='center' class='textHeaderDark'>"
							. _("Showing Rows") . " " . ((read_config_option("num_rows_data_source")*($_REQUEST["page"]-1))+1) . " to " . ((($total_rows < read_config_option("num_rows_data_source")) || ($total_rows < (read_config_option("num_rows_data_source")*$_REQUEST["page"]))) ? $total_rows : (read_config_option("num_rows_data_source")*$_REQUEST["page"])) . " " . _("of") . $total_rows [$url_page_select] .
						"</td>\n
						<td align='right' class='textHeaderDark'>
							<strong>"; if (($_REQUEST["page"] * read_config_option("num_rows_data_source")) < $total_rows) { $nav .= "<a class='linkOverDark' href='data_sources.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"] . "&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= _("Next"); if (($_REQUEST["page"] * read_config_option("num_rows_data_source")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	print $nav;

	html_header_checkbox(array(_("Name"), _("Data Input Type"), _("Active"), _("Template Name")));

	$i = 0;
	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			if (trim($_REQUEST["filter"]) == "") {
				$highlight_text = title_trim($data_source["name_cache"], read_config_option("max_title_data_source"));
			}else{
				$highlight_text = eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", title_trim($data_source["name_cache"], read_config_option("max_title_data_source")));
			}

			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class='linkEditMain' href='data_sources.php?action=edit&id=<?php print $data_source["id"];?>'><?php print $highlight_text;?></a>
				</td>
				<td>
					<?php print $data_input_types{$data_source["data_input_type"]};?>
				</td>
				<td>
					<?php print (empty($data_source["active"]) ? "<span style='color: red;'>" . _("No") . "</span>" : _("Yes"));?>
				</td>
				<td>
					<?php print ((empty($data_source["data_template_name"])) ? "<em>" . _("None") . "</em>" : $data_source["data_template_name"]);?>
				</td>
				<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_<?php print $data_source["id"];?>' title="<?php print $data_source["name_cache"];?>">
				</td>
			</tr>
			<?php
		}

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>"._("No Data Sources")."</em></td></tr>";
	}

	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_actions);

	print "</form>\n";
}
?>
