<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");
require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");
require_once(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");
require_once(CACTI_BASE_PATH . "/lib/template.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

require_once(CACTI_BASE_PATH . "/lib/xajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("_data_preset_rra_item_xajax_save");
$xajax->registerFunction("_data_preset_rra_item_xajax_remove");
$xajax->processRequests();

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_add':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

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
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		template();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "data_template_edit") {
		$data_input_fields = array();
		$data_template_item_fields = array();
		$suggested_value_fields = array();
		$rra_item_fields = array();

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
					$data_input_fields[$field_name]["t_value"] = html_boolean($_POST["t_dif_$field_name"]);
				}else{
					$data_input_fields[$field_name]["t_value"] = "0";
				}
			}else if (substr($name, 0, 3) == "sv|") {
				$matches = explode("|", $name);
				$suggested_value_fields{$matches[1]}[] = array("id" => $matches[2], "value" => $value);
			}else if (substr($name, 0, 5) == "rrai|") {
				$matches = explode("|", $name);
				$rra_item_fields{$matches[2]}{$matches[1]} = $value;
			}
		}

		/* step #2: field validation */
		$form_data_template["template_name"] = $_POST["template_name"];
		$form_data_source["data_input_type"] = $_POST["data_input_type"];
		$form_data_source["t_name"] = html_boolean(isset($_POST["t_name"]) ? $_POST["t_name"] : "");
		$form_data_source["active"] = html_boolean(isset($_POST["active"]) ? $_POST["active"] : "");
		$form_data_source["t_active"] = html_boolean(isset($_POST["t_active"]) ? $_POST["t_active"] : "");
		$form_data_source["polling_interval"] = (isset($_POST["polling_interval"]) ? $_POST["polling_interval"] : "");
		$form_data_source["t_polling_interval"] = html_boolean(isset($_POST["t_polling_interval"]) ? $_POST["t_polling_interval"] : "");

		field_register_error(api_data_source_fields_validate($form_data_source, $suggested_value_fields, "|field|", "sv||field|||id|"));
		field_register_error(api_data_source_input_fields_validate($data_input_fields, "|field|"));
		field_register_error(api_data_template_fields_validate($form_data_template, "|field|"));

		foreach ($data_template_item_fields as $data_template_item_id => $fields) {
			$form_data_source_item[$data_template_item_id]["t_rrd_maximum"] = html_boolean(isset($fields["t_rrd_maximum"]) ? $fields["t_rrd_maximum"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_maximum"] = (isset($fields["rrd_maximum"]) ? $fields["rrd_maximum"] : "");
			$form_data_source_item[$data_template_item_id]["t_rrd_minimum"] = html_boolean(isset($fields["t_rrd_minimum"]) ? $fields["t_rrd_minimum"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_minimum"] = (isset($fields["rrd_minimum"]) ? $fields["rrd_minimum"] : "");
			$form_data_source_item[$data_template_item_id]["t_rrd_heartbeat"] = html_boolean(isset($fields["t_rrd_heartbeat"]) ? $fields["t_rrd_heartbeat"] : "");
			$form_data_source_item[$data_template_item_id]["rrd_heartbeat"] = (isset($fields["rrd_heartbeat"]) ? $fields["rrd_heartbeat"] : "");
			$form_data_source_item[$data_template_item_id]["t_data_source_type"] = html_boolean(isset($fields["t_data_source_type"]) ? $fields["t_data_source_type"] : "");
			$form_data_source_item[$data_template_item_id]["data_source_type"] = $fields["data_source_type"];
			$form_data_source_item[$data_template_item_id]["data_source_name"] = $fields["data_source_name"];
			$form_data_source_item[$data_template_item_id]["field_input_value"] = (isset($fields["field_input_value"]) ? $fields["field_input_value"] : "");

			api_data_source_item_fields_validate($form_data_source_item[$data_template_item_id], "dsi||field|||id|");
		}

		/* step #3: field save */
		if (!is_error_message()) {
			$data_template_id = api_data_template_save($_POST["data_template_id"], $form_data_template + $form_data_source);

			if ($data_template_id) {
				/* copy down the selected rra preset into the data template if a preset is selected */
				api_data_template_rra_item_clear($data_template_id);
				api_data_template_preset_rra_item_copy($data_template_id, $_POST["preset_rra_id"]);

				/* save suggested values (for the 'name' field) */
				api_data_template_suggested_values_save($data_template_id, $suggested_value_fields);

				/* save custom data input data */
				api_data_template_input_fields_save($data_template_id, $data_input_fields);

				/* save each data template item on the form */
				foreach (array_keys($data_template_item_fields) as $data_template_item_id) {
					$form_data_source_item[$data_template_item_id]["data_template_id"] = $data_template_id;

					$data_template_item_id = api_data_template_item_save($data_template_item_id, $form_data_source_item[$data_template_item_id]);

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
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $data_template_id) {
				api_data_template_remove($data_template_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "duplicate") {
			// yet yet coded
		}

		header("Location: data_templates.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "data_template_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: data_templates.php$get_string");
	}
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
	global $colors, $data_source_types;

	if (!empty($_GET["id"])) {
		$data_template = db_fetch_row("select * from data_template where id=" . $_GET["id"]);
		$data_template_items = db_fetch_assoc("select * from data_template_item where data_template_id=" . $_GET["id"]);

		$header_label = _("[edit: ") . $data_template["template_name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	form_start("data_templates.php", "form_data_template");

	/* ==================== Box: Data Template ==================== */

	html_start_box("<strong>" . _("Data Template") . "</strong> $header_label");
	_data_template_field__template_name("template_name", (isset($data_template) ? $data_template["template_name"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
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

	html_start_box("<strong>" . _("Data Input") . "</strong>");

	_data_source_input_field__data_input_type("data_input_type", true, $_data_input_type, (empty($_GET["id"]) ? 0 : $_GET["id"]));

	/* grab the appropriate data input type form array */
	if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
		$scripts = api_script_list();

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

			field_row_header(_("External Script"));
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
		$data_queries = api_data_query_list();

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

			field_row_header(_("Data Query"));
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

	$rra_items = api_data_template_rra_item_list($data_template["id"]);

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "sv_add") {
		form_hidden_box("redirect_sv_add", "x", "");
	}

	html_start_box("<strong>" . _("Data Source") . "</strong>");

	_data_source_field__name("name", true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_name", (isset($data_template["t_name"]) ? $data_template["t_name"] : ""));
	_data_source_field__rra("preset_rra_id", true, (isset($data_template["preset_rra_id"]) ? $data_template["preset_rra_id"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_data_source_field__polling_interval("polling_interval", true, (isset($data_template["polling_interval"]) ? $data_template["polling_interval"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_polling_interval", (isset($data_template["t_polling_interval"]) ? $data_template["t_polling_interval"] : ""));
	_data_source_field__active("active", true, (isset($data_template["active"]) ? $data_template["active"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_active", (isset($data_template["t_active"]) ? $data_template["t_active"] : ""));

	html_end_box();

	/* ==================== Box: Data Source Item ==================== */

	html_start_box("<strong>" . _("Data Source Item") . "</strong>", (empty($_GET["id"]) ? "" : "javascript:document.forms[0].action.value='item_add';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=item_add&id=" . $_GET["id"]) . "', '')"));

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
			$field_input_description = _("Script Output Field");
		}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
			$field_list = api_data_query_field_list($_data_query_id, DATA_QUERY_FIELD_TYPE_OUTPUT);

			$data_query_output_fields = array();
			if (sizeof($field_list) > 0) {
				foreach ($field_list as $field) {
					$data_query_output_fields{$field["name"]} = $field["name"] . " (" . $field["name_desc"] . ")";
				}
			}

			$field_input_description = _("Data Query Output Field");
		}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
			$field_input_description = _("SNMP OID");
		}

		foreach ($data_template_items as $item) {
			if ($_data_input_type != DATA_INPUT_TYPE_NONE) {
				?>
				<tr bgcolor="<?php print $colors["header_panel_background"];?>">
					<td class='textSubHeaderDark' colspan="2">
						<?php print (isset($item["data_source_name"]) ? $item["data_source_name"] : "(" . _("New Data Template Item") . ")");?>
					</td>
					<td class='textSubHeaderDark' align='right'>
						<?php
						if ((isset($item["id"])) && (sizeof($data_template_items) > 1)) {
							print "[<a href='data_templates.php?action=item_remove&id=" . $item["id"] . "&data_template_id=" . $item["data_template_id"] . "' class='linkOverDark'>remove</a>]\n";
						}
						?>
					</td>
				</tr>
				<tr bgcolor="#e1e1e1">
					<td width="50%" style="border-bottom: 1px solid #a1a1a1;">
						<font class='textEditTitle'>Field Input: <?php print $field_input_description;?></font><br>
					</td>
					<td style="border-bottom: 1px solid #a1a1a1;" colspan="2">
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

	form_hidden_box("data_template_id", (empty($_GET["id"]) ? 0 : $_GET["id"]), "");
	form_hidden_box("action_post", "data_template_edit");

	form_save_button("data_templates.php");
}

function template() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$filter_array = array();

	/* search field: filter (searches template name) */
	if (isset_get_var("search_filter")) {
		$filter_array["template_name"] = get_get_var("search_filter");
	}

	/* get a list of all data templates on this page */
	$data_templates = api_data_template_list($filter_array);

	/* get a list of data input types for display in the data sources list */
	$data_input_types = api_data_source_input_type_list();

	form_start("data_templates.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Data Templates") . "</strong>", "data_templates.php?action=edit");
	html_header_checkbox(array(_("Template Name"), _("Data Input Type"), _("Status")), $box_id);

	$i = 0;
	if (sizeof($data_templates) > 0) {
		foreach ($data_templates as $data_template) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $data_template["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $data_template["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $data_template["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $data_template["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $data_template["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $data_template["id"];?>')" href="data_templates.php?action=edit&id=<?php echo $data_template["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $data_template["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $data_template["template_name"]);?></span></a>
				</td>
				<td>
					<?php echo $data_input_types{$data_template["data_input_type"]};?>
				</td>
				<td>
					<?php if ($data_template["active"] == "1") echo _("Active"); else echo _("Disabled");?>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $data_template["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $data_template["id"];?>' title="<?php echo $data_template["template_name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr class="empty">
			<td colspan="6">
				No data templates found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "3", HTML_BOX_SEARCH_NO_ICON);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "data_template_list");
	form_end();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Data Template');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Data Templates');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>
