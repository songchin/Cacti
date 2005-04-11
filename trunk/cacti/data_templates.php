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
include_once("./lib/data_source/data_source_template_update.php");
include_once("./lib/data_query/data_query_info.php");
include_once("./lib/sys/sequence.php");
include_once("./include/data_source/data_source_constants.php");
include_once("./include/data_source/data_source_form.php");
include_once("./lib/tree.php");
include_once("./lib/html_tree.php");
include_once("./lib/utility.php");
include_once("./lib/template.php");

$ds_actions = array(
	1 => "Delete",
	2 => "Duplicate"
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

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	case 'item_remove':
		template_item_remove();

		break;
	case 'sv_remove':
		sv_remove();

		header("Location: data_templates.php?action=edit" . (empty($_GET["data_template_id"]) ? "" : "&id=" . $_GET["data_template_id"]));
		break;
	case 'sv_movedown':
		sv_movedown();

		header("Location: data_templates.php?action=edit" . (empty($_GET["data_template_id"]) ? "" : "&id=" . $_GET["data_template_id"]));
		break;
	case 'sv_moveup':
		sv_moveup();

		header("Location: data_templates.php?action=edit" . (empty($_GET["data_template_id"]) ? "" : "&id=" . $_GET["data_template_id"]));
		break;
	case 'sv_add':
		include_once ("./include/top_header.php");

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	case 'template_remove':
		template_remove();

		header("Location: data_templates.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		template();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_template"])) {
		$data_input_fields = array();
		$suggested_value_fields = array();

		reset($_POST);

		while(list($name, $value) = each($_POST)) {
			if (substr($name, 0, 4) == "dsi|") {
				$matches = explode("|", $name);
				$data_template_item_fields{$matches[2]}{$matches[1]} = $value;
			}else if (substr($name, 0, 4) == "dif_") {
				$field_name = substr($name, 4);

				$data_input_fields[$field_name]["value"] = $value;

				if (isset($_POST["t_dif_$field_name"])) {
					$data_input_fields[$field_name]["t_value"] = $_POST["t_dif_$field_name"];
				}else{
					$data_input_fields[$field_name]["t_value"] = "";
				}
			}else if (substr($name, 0, 3) == "sv|") {
				$matches = explode("|", $name);
				$suggested_value_fields{$matches[1]}{$matches[2]} = $value;
			}
		}

		$data_template_id = api_data_template_save($_POST["data_template_id"], $_POST["template_name"], $suggested_value_fields, $_POST["data_input_type"], $data_input_fields,
			(isset($_POST["t_name"]) ? $_POST["t_name"] : ""), (isset($_POST["t_active"]) ? $_POST["t_active"] : ""),
			(isset($_POST["active"]) ? $_POST["active"] : ""), (isset($_POST["t_rrd_step"]) ? $_POST["t_rrd_step"] : ""), $_POST["rrd_step"], (isset($_POST["t_rrd_id"]) ?
			$_POST["t_rra_id"] : ""), (isset($_POST["rra_id"]) ? $_POST["rra_id"] : array()));

		while (list($data_template_item_id, $fields) = each($data_template_item_fields)) {
			$data_template_item_id = api_data_template_item_save($data_template_item_id, $data_template_id, (isset($fields["t_rrd_maximum"]) ?
				$fields["t_rrd_maximum"] : ""), $fields["rrd_maximum"], (isset($fields["t_rrd_minimum"]) ? $fields["t_rrd_minimum"] : ""),
				$fields["rrd_minimum"], (isset($fields["t_rrd_heartbeat"]) ? $fields["t_rrd_heartbeat"] : ""), $fields["rrd_heartbeat"],
				(isset($fields["t_data_source_type"]) ? $fields["t_data_source_type"] : ""), $fields["data_source_type"], (isset($fields["t_data_source_name"]) ?
				$fields["t_data_source_name"] : ""), $fields["data_source_name"], (isset($fields["field_input_value"]) ? $fields["field_input_value"] : ""));
		}

		if ((is_error_message()) || (empty($data_template_id))) {
			if (isset($_POST["redirect_item_add"])) {
				$action = "item_add";
			}else{
				$action = "edit";
			}

			header("Location: data_templates.php?action=$action" . (empty($data_template_id) ? "" : "&id=$data_template_id") . (isset($_POST["data_input_type"]) ? "&data_input_type=" . $_POST["data_input_type"] : "") . (isset($_POST["dif_script_id"]) ? "&script_id=" . $_POST["dif_script_id"] : "") . (isset($_POST["dif_data_query_id"]) ? "&data_query_id=" . $_POST["dif_data_query_id"] : ""));
		}else{
			header("Location: data_templates.php");
		}
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
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_template_remove($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_data_source(0, $selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: data_templates.php");
		exit;
	}

	/* setup some variables */
	$ds_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$ds_list .= "<li>" . db_fetch_cell("select template_name from data_template where id=" . $matches[1]) . "<br>";
			$ds_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $ds_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='data_templates.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>Are you sure you want to delete the following data templates? Any data sources attached
					to these templates will become individual data sources.</p>
					<p>$ds_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following data templates will be duplicated. You can
					optionally change the title format for the new data templates.</p>
					<p>$ds_list</p>
					<p><strong>Title Format:</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($ds_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one data template.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($ds_array) ? serialize($ds_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='data_templates.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* ----------------------------
    template - Data Templates
   ---------------------------- */

function sv_movedown() {
	seq_move_item("data_template_suggested_value", $_GET["id"], "data_template_id = " . $_GET["data_template_id"] . " and field_name = 'name'", "down");
}

function sv_moveup() {
	seq_move_item("data_template_suggested_value", $_GET["id"], "data_template_id = " . $_GET["data_template_id"] . " and field_name = 'name'", "up");
}

function sv_remove() {
	db_execute("delete from data_template_suggested_value where id=" . $_GET["id"]);
}

function template_item_remove() {
	api_data_template_item_remove($_GET["id"]);

	header("Location: data_templates.php?action=edit&id=" . $_GET["data_template_id"]);
}

function template_edit() {
	global $colors, $struct_data_source, $struct_data_source_item, $data_source_types, $fields_data_template_template_edit;

	global $struct_data_input, $struct_data_input_script, $struct_data_input_snmp, $struct_data_input_data_query;

	if (!empty($_GET["id"])) {
		$data_template = db_fetch_row("select * from data_template where id=" . $_GET["id"]);
		$data_template_items = db_fetch_assoc("select * from data_template_item where data_template_id=" . $_GET["id"]);

		$header_label = "[edit: " . $data_template["template_name"] . "]";
	}else{
		$header_label = "[new]";
	}

	/* ==================== Box: Data Template ==================== */

	html_start_box("<strong>Data Template</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
			"config" => array(
				"form_name" => "form_data_template"
			),
			"fields" => inject_form_variables($fields_data_template_template_edit, (isset($data_template) ? $data_template : array()))
			)
		);

	html_end_box();

	/* ==================== Box: Data Input ==================== */

	/* determine current value for 'data_input_type' */
	if (isset($_GET["data_input_type"])) {
		$_data_input_type = $_GET["data_input_type"];
	}else if (isset($data_template["data_input_type"])) {
		$_data_input_type = $data_template["data_input_type"];
	}else{
		$_data_input_type = DATA_INPUT_TYPE_SCRIPT;
	}

	/* get a list of all data input type fields for this data template */
	if (isset($data_template)) {
		$data_input_type_fields = array_rekey(db_fetch_assoc("select name,t_value,value from data_template_field where data_template_id = " . $data_template["id"]), "name", array("t_value", "value"));
	}else{
		$data_input_type_fields = array();
	}

	/* fill in data template-specific information (data input type dropdown) */
	$_data_input_form = array("data_input_type" => $struct_data_input["data_input_type"]);

	/* fill in data source-specific information (data input type dropdown) */
	$_data_input_form["data_input_type"]["redirect_url"] = "data_templates.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_input_type=|dropdown_value|";
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
			$_data_input_type_form["dif_script_id"]["redirect_url"] = "data_templates.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_input_type=$_data_input_type&script_id=|dropdown_value|";
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
						"friendly_name" => "Custom Input Fields",
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
							"sub_template_checkbox" => array(
								"name" => "t_dif_" . $field["data_name"],
								"friendly_name" => "Do Not Template this Field",
								"value" => ((isset($data_input_type_fields{$field["data_name"]})) ? $data_input_type_fields{$field["data_name"]}["t_value"] : "")
								)
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

		if (sizeof($data_queries) > 0) {
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
			$_data_input_type_form["dif_data_query_id"]["redirect_url"] = "data_templates.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_input_type=$_data_input_type&data_query_id=|dropdown_value|";
			$_data_input_type_form["dif_data_query_id"]["form_index"] = "0";
			$_data_input_type_form["dif_data_query_id"]["value"] = $_data_query_id;

			foreach ($data_queries as $item) {
				$_data_input_type_form["dif_data_query_id"]["array"]{$item["id"]} = $item["name"];
			}
		}
	}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
		while (list($field_name, $field_array) = each($struct_data_input_snmp)) {
			$struct_data_input_snmp[$field_name]["value"] =  (isset($data_input_type_fields{substr($field_name, 4)}) ? $data_input_type_fields{substr($field_name, 4)}["value"] : "");
		}

		$_data_input_type_form = $struct_data_input_snmp;
	}else{
		$_data_input_type_form = array();
	}

	/* add 'do not template this field' checkboxes to all fields */
	while (list($field_name, $field_array) = each($_data_input_type_form)) {
		/* certain fields should not have a 'template' checkbox */
		if (($field_name != "dif_script_id") && ($field_name != "dif_data_query_id")) {
			$_data_input_type_form[$field_name]["description"] = "";

			if (!isset($_data_input_type_form[$field_name]["sub_template_checkbox"])) {
				$_data_input_type_form[$field_name]["sub_template_checkbox"] = array(
					"name" => "t_" . $field_name,
					"friendly_name" => "Do Not Template this Field",
					"value" => (isset($data_input_type_fields{substr($field_name, 4)}) ? $data_input_type_fields{substr($field_name, 4)}["t_value"] : "0")
					);
			}
		}
	}

	$_data_input_form += $_data_input_type_form;

	html_start_box("<strong>Data Input</strong>", "98%", $colors["header_background_template"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(
				"no_form_tag" => true
			),
			"fields" => inject_form_variables($_data_input_form, (isset($data_template) ? $data_template : array()), $data_input_type_fields)
			)
		);

	html_end_box();

	/* ==================== Box: Data Source ==================== */

	/* make sure 'data source path' doesn't show up for a template... we should NEVER template this field */
	unset($struct_data_source["rrd_path"]);

	$form_array = array();

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "sv_add") {
		form_hidden_box("redirect_sv_add", "x", "");
	}

	while (list($field_name, $field_array) = each($struct_data_source)) {
		$form_array += array($field_name => $struct_data_source[$field_name]);

		$form_array[$field_name]["description"] = "";
		$form_array[$field_name]["sub_template_checkbox"] = array(
			"name" => "t_" . $field_name,
			"friendly_name" => "Do Not Template this Field",
			"value" => (isset($data_template{"t_" . $field_name}) ? $data_template{"t_" . $field_name} : "")
			);
	}

	/* data template specific tables */
	$form_array["rra_id"]["sql"] = "select rra_id as id,data_template_id from data_template_rra where data_template_id=|arg1:id|";
	$form_array["rra_id"]["sql_print"] = "select rra.name from data_template_rra,rra where data_template_rra.rra_id=rra.id and data_template_rra.data_template_id=|arg1:id|";

	$form_array["name"]["method"] = "textbox_sv";
	$form_array["name"]["value"] = (empty($_GET["id"]) ? array() : array_rekey(db_fetch_assoc("select value,id from data_template_suggested_value where data_template_id = " . $_GET["id"] . " and field_name = 'name' order by sequence"), "id", "value"));
	$form_array["name"]["force_blank_field"] = (($_GET["action"] == "sv_add") ? true : false);
	$form_array["name"]["url_moveup"] = "javascript:document.forms[0].action.value='sv_moveup';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_moveup&id=|id|" . (empty($_GET["id"]) ? "" : "&data_template_id=" . $_GET["id"])) . "', '')";
	$form_array["name"]["url_movedown"] = "javascript:document.forms[0].action.value='sv_movedown';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_movedown&id=|id|" . (empty($_GET["id"]) ? "" : "&data_template_id=" . $_GET["id"])) . "', '')";
	$form_array["name"]["url_delete"] =  "javascript:document.forms[0].action.value='sv_remove';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_remove&id=|id|" . (empty($_GET["id"]) ? "" : "&data_template_id=" . $_GET["id"])) . "', '')";
	$form_array["name"]["url_add"] = "javascript:document.forms[0].action.value='sv_add';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_add" . (empty($_GET["id"]) ? "" : "&id=" . $_GET["id"])) . "', '')";

	html_start_box("<strong>Data Source</strong>", "98%", $colors["header_background_template"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(
				"no_form_tag" => true
				),
			"fields" => inject_form_variables($form_array, (isset($data_template) ? $data_template : array()))
			)
		);

	html_end_box();

	/* ==================== Box: Data Source Item ==================== */

	html_start_box("<strong>Data Source Item</strong>", "98%", $colors["header_background"], "3", "center", (empty($_GET["id"]) ? "" : "javascript:document.forms[0].action.value='item_add';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=item_add&id=" . $_GET["id"]) . "', '')"));

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "item_add") {
		form_hidden_box("redirect_item_add", "x", "");
	}

	/* this allows a "blank" data template item to be displayed when the user wants to create
	 * a new one */
	if ((!isset($data_template_items)) || (sizeof($data_template_items) == 0) || ($_GET["action"] == "item_add")) {
		if (isset($data_template_items)) {
			$next_index = sizeof($data_template_items);
		}else{
			$next_index = 0;
		}

		$data_template_items[$next_index] = array();
	}

	if (sizeof($data_template_items) > 0) {
		if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
			$script_output_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='out' order by name");
			$field_input_description = "Script Output Field";
		}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
			$data_query_xml = get_data_query_array($_data_query_id);
			$data_query_output_fields = array();

			while (list($field_name, $field_array) = each($data_query_xml["fields"])) {
				if ($field_array["direction"] == "output") {
					$data_query_output_fields[$field_name] = $field_name . " (" . $field_array["name"] . ")";
				}
			}

			$field_input_description = "Data Query Output Field";
		}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
			$field_input_description = "SNMP OID";
		}

		foreach ($data_template_items as $item) {
			if ($_data_input_type != DATA_INPUT_TYPE_NONE) {
				?>
				<tr bgcolor="<?php print $colors["header_panel_background"];?>">
					<td class='textSubHeaderDark'>
						<?php print (isset($item["data_source_name"]) ? $item["data_source_name"] : "(New Data Template Item)");?>
					</td>
					<td class='textSubHeaderDark' align='right'>
						<?php
						if ((isset($item["id"])) && (sizeof($data_template_items) > 1)) {
							print "[<a href='data_templates.php?action=item_remove&id=" . $item["id"] . "&data_template_id=" . $item["data_template_id"] . "' class='linkOverDark'>remove</a>]\n";
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
				$_t_field_name = "dsi|t_$field_name|" . (isset($item["id"]) ? $item["id"] : "0");

				$form_array += array($_field_name => $struct_data_source_item[$field_name]);

				$form_array[$_field_name]["description"] = "";
				$form_array[$_field_name]["value"] = (isset($item[$field_name]) ? $item[$field_name] : "");
				$form_array[$_field_name]["sub_template_checkbox"] = array(
					"name" => $_t_field_name,
					"friendly_name" => "Do Not Template this Field",
					"value" => (isset($item{"t_" . $field_name}) ? $item{"t_" . $field_name} : "")
					);
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

	form_save_button("data_templates.php");
}

function template() {
	global $colors, $ds_actions, $data_input_types;

	html_start_box("<strong>Data Templates</strong>", "98%", $colors["header_background"], "3", "center", "data_templates.php?action=edit");

	html_header_checkbox(array("Template Name", "Data Input Type", "Status"));

	$template_list = db_fetch_assoc("select
		data_template.id,
		data_template.template_name,
		data_template.data_input_type,
		data_template.active
		from data_template
		order by data_template.template_name");

	$i = 0;
	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
				?>
				<td>
					<a class="linkEditMain" href="data_templates.php?action=edit&id=<?php print $template["id"];?>"><?php print $template["template_name"];?></a>
				</td>
				<td>
					<?php print $data_input_types{$template["data_input_type"]};?>
				</td>
				<td>
					<?php if ($template["active"] == "1") print "Active"; else print "Disabled";?>
				</td>
				<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_<?php print $template["id"];?>' title="<?php print $template["template_name"];?>">
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>No Data Templates</em></td></tr>";
	}

	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_actions);

	print "</form>\n";
}

?>