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
include_once("./lib/data_source/data_source_form.php");
include_once("./lib/data_query/data_query_info.php");
include_once("./lib/sys/sequence.php");
include_once("./include/data_source/data_source_constants.php");
include("./include/data_source/data_source_form.php");
include_once("./lib/tree.php");
include_once("./lib/html_tree.php");
include_once("./lib/utility.php");
include_once("./lib/template.php");

$ds_actions = array(
	1 => _("Delete"),
	2 => _("Duplicate")
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
	global $fields_data_source;

	if (isset($_POST["save_component_template"])) {
		$data_input_fields = array();
		$suggested_value_fields = array();

		/* cache all post field values */
		init_post_field_cache();

		reset($_POST);

		/* step #1: field parsing */
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

		/* step #2: field validation */
		$form_data_source["id"] = $_POST["data_template_id"];
		$form_data_source["template_name"] = $_POST["template_name"];
		$form_data_source["data_input_type"] = $_POST["data_input_type"];
		$form_data_source["t_name"] = html_boolean(isset($_POST["t_name"]) ? $_POST["t_name"] : "");
		$form_data_source["active"] = html_boolean(isset($_POST["active"]) ? $_POST["active"] : (empty($_POST["data_template_id"]) ? $fields_data_source["active"]["default"] : "") );
		$form_data_source["t_active"] = html_boolean(isset($_POST["t_active"]) ? $_POST["t_active"] : "");
		$form_data_source["rrd_step"] = (isset($_POST["rrd_step"]) ? $_POST["rrd_step"] : $fields_data_source["rrd_step"]["default"]);
		$form_data_source["t_rrd_step"] = html_boolean(isset($_POST["t_rrd_step"]) ? $_POST["t_rrd_step"] : "");

		validate_data_source_fields($form_data_source, $suggested_value_fields, "|field|", "sv||field|||id|");
		validate_data_source_input_fields($data_input_fields, "|field|");
		validate_data_template_fields($form_data_source, "|field|");

		while (list($data_template_item_id, $fields) = each($data_template_item_fields)) {
			$form_data_source_item[$data_template_item_id]["id"] = $data_template_item_id;
			$form_data_source_item[$data_template_item_id]["t_rrd_maximum"] = html_boolean(isset($fields["t_rrd_maximum"]) ? $fields["t_rrd_maximum"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_maximum"] = (isset($fields["rrd_maximum"]) ? $fields["rrd_maximum"] : $fields_data_source["rrd_maximum"]["default"]);
			$form_data_source_item[$data_template_item_id]["t_rrd_minimum"] = html_boolean(isset($fields["t_rrd_minimum"]) ? $fields["t_rrd_minimum"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_minimum"] = (isset($fields["rrd_minimum"]) ? $fields["rrd_minimum"] : $fields_data_source["rrd_minimum"]["default"]);
			$form_data_source_item[$data_template_item_id]["t_rrd_heartbeat"] = html_boolean(isset($fields["t_rrd_heartbeat"]) ? $fields["t_rrd_heartbeat"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_heartbeat"] = (isset($fields["rrd_heartbeat"]) ? $fields["rrd_heartbeat"] : $fields_data_source["rrd_heartbeat"]["default"]);
			$form_data_source_item[$data_template_item_id]["t_data_source_type"] = html_boolean(isset($fields["t_data_source_type"]) ? $fields["t_data_source_type"] : "");
			$form_data_source_item[$data_template_item_id]["data_source_type"] = $fields["data_source_type"];
			$form_data_source_item[$data_template_item_id]["data_source_name"] = $fields["data_source_name"];
			$form_data_source_item[$data_template_item_id]["field_input_value"] = (isset($fields["field_input_value"]) ? $fields["field_input_value"] : "");

			validate_data_source_item_fields($form_data_source_item[$data_template_item_id], "dsi||field|||id|");
		}

		/* step #3: field save */
		if (!is_error_message()) {
			$data_template_id = api_data_template_save($form_data_source, $suggested_value_fields, $data_input_fields, (isset($_POST["rra_id"]) ? $_POST["rra_id"] : array()));

			if ($data_template_id) {
				reset($data_template_item_fields);
				while (list($data_template_item_id, $fields) = each($data_template_item_fields)) {
					$form_data_source_item[$data_template_item_id]["data_template_id"] = $data_template_id;

					$data_template_item_id = api_data_template_item_save($form_data_source_item[$data_template_item_id]);

					if (!$data_template_item_id) {
						raise_message(2);
					}
				}
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			if (isset($_POST["redirect_item_add"])) {
				$action = "item_add";
			}else{
				$action = "edit";
			}

			header("Location: data_templates.php?action=$action" . (empty($data_template_id) ? "&id=" . $_POST["data_template_id"] : "&id=$data_template_id") . (isset($_POST["data_input_type"]) ? "&data_input_type=" . $_POST["data_input_type"] : "") . (isset($_POST["dif_script_id"]) ? "&script_id=" . $_POST["dif_script_id"] : "") . (isset($_POST["dif_data_query_id"]) ? "&data_query_id=" . $_POST["dif_data_query_id"] : ""));
		}else{
			raise_message(1);

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
					<p>"._("Are you sure you want to delete the following data templates? Any data sources attached
					to these templates will become individual data sources.")."</p>
					<p>$ds_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>"._("When you click save, the following data templates will be duplicated. You can
					optionally change the title format for the new data templates.")."</p>
					<p>$ds_list</p>
					<p><strong>"._("Title Format:")."</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($ds_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one data template.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='"._("Save")."' align='absmiddle'>";
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

	html_start_box("<strong>"._("Data Template")."</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

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

	html_start_box("<strong>Data Input</strong>", "98%", $colors["header_background_template"], "3", "center", "");

	_data_source_input_field__data_input_type("data_input_type", true, $_data_input_type, (empty($_GET["id"]) ? 0 : $_GET["id"]));

	/* grab the appropriate data input type form array */
	if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
		/* since the "sql" key is not executed until draw_edit_form(), we have fetch the list of
		 * external scripts here as well */
		$scripts = db_fetch_assoc("select id,name from data_input order by name");

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

			field_row_header("External Script");
			_data_source_input_field__script_id("dif_script_id", "data_templates.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_input_type=$_data_input_type&script_id=|dropdown_value|", $_script_id);

			/* get each INPUT field for this script */
			$script_input_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='in' order by name");

			if (sizeof($script_input_fields) > 0) {
				field_row_header(_("Custom Input Fields"));

				foreach ($script_input_fields as $field) {
					_data_source_input_field__script("dif_" . $field["data_name"], $field["name"], true, ((isset($data_input_type_fields{$field["data_name"]})) ? $data_input_type_fields{$field["data_name"]}["value"] : ""), "t_dif_" . $field["data_name"], ((isset($data_input_type_fields{$field["data_name"]})) ? $data_input_type_fields{$field["data_name"]}["t_value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
				}
			}
		}
	}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
		/* since the "sql" key is not executed until draw_edit_form(), we have fetch the list of
		 * data queries here as well */
		$data_queries = db_fetch_assoc("select id,name from snmp_query order by name");

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

			field_row_header("Data Query");
			_data_source_input_field__data_query_id("dif_data_query_id", "data_templates.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_input_type=$_data_input_type&data_query_id=|dropdown_value|", $_data_query_id);
		}

	}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
		_data_source_input_field__device_hdr_generic();
		_data_source_input_field__device_snmp_port("dif_snmp_port", true, (isset($data_input_type_fields["snmp_port"]) ? $data_input_type_fields["snmp_port"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmp_port"]) ? $data_input_type_fields["snmp_port"]["t_value"] : "0"), (isset($data_input_type_fields["snmp_port"]) ? "on" : ""));
		_data_source_input_field__device_snmp_timeout("dif_snmp_timeout", true, (isset($data_input_type_fields["snmp_timeout"]) ? $data_input_type_fields["snmp_timeout"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmp_timeout"]) ? $data_input_type_fields["snmp_timeout"]["t_value"] : "0"), (isset($data_input_type_fields["snmp_timeout"]) ? "on" : ""));
		_data_source_input_field__device_snmp_version("dif_snmp_version", true, (isset($data_input_type_fields["snmp_version"]) ? $data_input_type_fields["snmp_version"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmp_version"]) ? $data_input_type_fields["snmp_version"]["t_value"] : "0"), (isset($data_input_type_fields["snmp_version"]) ? "on" : ""));
		_data_source_input_field__device_hdr_snmpv12();
		_data_source_input_field__device_snmp_community("dif_snmp_community", true, (isset($data_input_type_fields["snmp_community"]) ? $data_input_type_fields["snmp_community"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmp_community"]) ? $data_input_type_fields["snmp_community"]["t_value"] : "0"), (isset($data_input_type_fields["snmp_community"]) ? "on" : ""));
		_data_source_input_field__device_hdr_snmpv3();
		_data_source_input_field__device_snmpv3_auth_username("dif_snmpv3_auth_username", true, (isset($data_input_type_fields["snmpv3_auth_username"]) ? $data_input_type_fields["snmpv3_auth_username"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmpv3_auth_username"]) ? $data_input_type_fields["snmpv3_auth_username"]["t_value"] : "0"), (isset($data_input_type_fields["snmpv3_auth_username"]) ? "on" : ""));
		_data_source_input_field__device_snmpv3_auth_password("dif_snmpv3_auth_password", true, (isset($data_input_type_fields["snmpv3_auth_password"]) ? $data_input_type_fields["snmpv3_auth_password"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmpv3_auth_password"]) ? $data_input_type_fields["snmpv3_auth_password"]["t_value"] : "0"), (isset($data_input_type_fields["snmpv3_auth_password"]) ? "on" : ""));
		_data_source_input_field__device_snmpv3_auth_protocol("dif_snmpv3_auth_protocol", true, (isset($data_input_type_fields["snmpv3_auth_protocol"]) ? $data_input_type_fields["snmpv3_auth_protocol"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmpv3_auth_protocol"]) ? $data_input_type_fields["snmpv3_auth_protocol"]["t_value"] : "0"), (isset($data_input_type_fields["snmpv3_auth_protocol"]) ? "on" : ""));
		_data_source_input_field__device_snmpv3_priv_passphrase("dif_snmpv3_priv_passphrase", true, (isset($data_input_type_fields["snmpv3_priv_passphrase"]) ? $data_input_type_fields["snmpv3_priv_passphrase"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmpv3_priv_passphrase"]) ? $data_input_type_fields["snmpv3_priv_passphrase"]["t_value"] : "0"), (isset($data_input_type_fields["snmpv3_priv_passphrase"]) ? "on" : ""));
		_data_source_input_field__device_snmpv3_priv_protocol("dif_snmpv3_priv_protocol", true, (isset($data_input_type_fields["snmpv3_priv_protocol"]) ? $data_input_type_fields["snmpv3_priv_protocol"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0), (isset($data_input_type_fields["snmpv3_priv_protocol"]) ? $data_input_type_fields["snmpv3_priv_protocol"]["t_value"] : "0"), (isset($data_input_type_fields["snmpv3_priv_protocol"]) ? "on" : ""));
	}

	html_end_box();

	/* ==================== Box: Data Source ==================== */

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "sv_add") {
		form_hidden_box("redirect_sv_add", "x", "");
	}

	html_start_box("<strong>"._("Data Source")."</strong>", "98%", $colors["header_background_template"], "3", "center", "");

	_data_source_field__name("name", true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_name", (isset($data_template["t_name"]) ? $data_template["t_name"] : ""));
	_data_source_field__rra_id("rra_id", true, (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_data_source_field__rrd_step("rrd_step", true, (isset($data_template["rrd_step"]) ? $data_template["rrd_step"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_rrd_step", (isset($data_template["t_rrd_step"]) ? $data_template["t_rrd_step"] : ""));
	_data_source_field__active("active", true, (isset($data_template["active"]) ? $data_template["active"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_active", (isset($data_template["t_active"]) ? $data_template["t_active"] : ""));

	html_end_box();

	/* ==================== Box: Data Source Item ==================== */

	html_start_box("<strong>"._("Data Source Item")."</strong>", "98%", $colors["header_background"], "3", "center", (empty($_GET["id"]) ? "" : "javascript:document.forms[0].action.value='item_add';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=item_add&id=" . $_GET["id"]) . "', '')"));

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

			$_field_id = (isset($item["id"]) ? $item["id"] : 0);

			field_reset_row_color();
			field_increment_row_color();
			_data_source_item_field__data_source_name("dsi|data_source_name|$_field_id", true, (isset($item["data_source_name"]) ? $item["data_source_name"] : ""), $_field_id);
			_data_source_item_field__rrd_minimum("dsi|rrd_minimum|$_field_id", true, (isset($item["rrd_minimum"]) ? $item["rrd_minimum"] : ""), $_field_id, "dsi|t_rrd_minimum|$_field_id", (isset($item["t_rrd_minimum"]) ? $item["t_rrd_minimum"] : ""));
			_data_source_item_field__rrd_maximum("dsi|rrd_maximum|$_field_id", true, (isset($item["rrd_maximum"]) ? $item["rrd_maximum"] : ""), $_field_id, "dsi|t_rrd_maximum|$_field_id", (isset($item["t_rrd_maximum"]) ? $item["t_rrd_maximum"] : ""));
			_data_source_item_field__data_source_type("dsi|data_source_type|$_field_id", true, (isset($item["data_source_type"]) ? $item["data_source_type"] : ""), $_field_id, "dsi|t_data_source_type|$_field_id", (isset($item["t_data_source_type"]) ? $item["t_data_source_type"] : ""));
			_data_source_item_field__rrd_heartbeat("dsi|rrd_heartbeat|$_field_id", true, (isset($item["rrd_heartbeat"]) ? $item["rrd_heartbeat"] : ""), $_field_id, "dsi|t_rrd_heartbeat|$_field_id", (isset($item["t_rrd_heartbeat"]) ? $item["t_rrd_heartbeat"] : ""));
		}
	}

	html_end_box();

	form_save_button("data_templates.php");
}

function template() {
	global $colors, $ds_actions, $data_input_types;

	html_start_box("<strong>Data Templates</strong>", "98%", $colors["header_background"], "3", "center", "data_templates.php?action=edit");

	html_header_checkbox(array(_("Template Name"), _("Data Input Type"), _("Status")));

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
		print "<tr><td><em>"._("No Data Templates")."</em></td></tr>\n";
	}

	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($ds_actions);

	print "</form>\n";
}

?>
