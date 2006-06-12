<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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
require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_form.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		script_field_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

function form_save() {
	if ($_POST["action_post"] == "script_field_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_script_field["data_input_id"] = $_POST["script_id"];
		$form_script_field["name"] = $_POST["name"];
		$form_script_field["data_name"] = $_POST["data_name"];
		$form_script_field["input_output"] = ($_POST["field_type"] == SCRIPT_FIELD_TYPE_INPUT ? "in" : "out");

		if ($_POST["field_type"] == SCRIPT_FIELD_TYPE_INPUT) {
			$form_script_field["field_input_type"] = $_POST["field_input_type"];

			if ($_POST["field_input_type"] == SCRIPT_FIELD_INPUT_CUSTOM) {
				$form_script_field["field_input_value"] = $_POST["field_input_value_custom"];
			}else if ($_POST["field_input_type"] == SCRIPT_FIELD_INPUT_DEVICE) {
				$form_script_field["field_input_value"] = $_POST["field_input_value_device"];
			}

			$form_script_field["regexp_match"] = $_POST["regexp_match"];
			$form_script_field["allow_empty"] = html_boolean(isset($_POST["allow_empty"]) ? $_POST["allow_empty"] : "");
		}else if ($_POST["field_type"] == SCRIPT_FIELD_TYPE_OUTPUT) {
			$form_script_field["update_rrd"] = html_boolean(isset($_POST["update_rrd"]) ? $_POST["update_rrd"] : "");
		}

		/* obtain a list of visible script field fields on the form */
		$visible_fields = api_script_field_visible_field_list($_POST["field_type"]);

		/* all non-visible fields on the form should be discarded */
		foreach ($visible_fields as $field_name) {
			$v_form_script_field[$field_name] = $form_script_field[$field_name];
		}

		field_register_error(api_script_field_field_validate($v_form_script_field, "|field|"));

		/* if the validation passes, save the row to the database */
		if (!is_error_message()) {
			$script_field_id = api_script_field_save($_POST["id"], $form_script_field);
		}

		if (is_error_message()) {
			header("Location: scripts_fields.php?action=edit&script_id=" . $_POST["script_id"] . "&id=" . (empty($script_field_id) ? $_POST["id"] : $script_field_id) . (!empty($_POST["field_type"]) ? "&field_type=" . $_POST["field_type"] : ""));
		}else{
			header("Location: scripts.php?action=edit&id=" . $_POST["script_id"]);
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $script_field_id) {
				api_script_field_remove($script_field_id);
			}
		}

		header("Location: scripts.php?action=edit&id=" . $_POST["script_id"]);
	}
}

function script_field_edit() {
	$_script_id = get_get_var_number("script_id");
	$_script_field_id = get_get_var_number("id");
	$_field_type = get_get_var_number("field_type");

	if (empty($_script_field_id)) {
		$header_label = "[new]";
	}else{
		$script_field = api_script_field_get($_script_field_id);

		$header_label = "[edit: " . $script_field["data_name"] . "]";
	}

	$script = api_script_get($_script_id);

	if (($_field_type == SCRIPT_FIELD_TYPE_INPUT) || ($_field_type == SCRIPT_FIELD_TYPE_OUTPUT)) {
		$current_field_type = $_field_type;
	}else{
		$current_field_type = ($script_field["input_output"] == "in" ? SCRIPT_FIELD_TYPE_INPUT : "out");
	}

	if ($current_field_type == SCRIPT_FIELD_TYPE_INPUT) {
		$current_field_type_friendly = _("Input");
	}elseif ($current_field_type == SCRIPT_FIELD_TYPE_OUTPUT) {
		$current_field_type_friendly = _("Output");
	}

	form_start("scripts_fields.php", "form_script_field");

	html_start_box("<strong>$current_field_type_friendly Field</strong> $header_label");

	if ($current_field_type == SCRIPT_FIELD_TYPE_INPUT) {
		field_row_header("Input Options");

		_script_field_field__field_input_type("field_input_type", (isset($script_field["field_input_type"]) ? $script_field["field_input_type"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
		_script_field_field__field_input_value_custom("field_input_value_custom", (isset($script_field["field_input_value"]) ? $script_field["field_input_value"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
		_script_field_field__field_input_value_device("field_input_value_device", (isset($script_field["field_input_value"]) ? $script_field["field_input_value"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
	}

	field_row_header("Field Options");

	if ($current_field_type == SCRIPT_FIELD_TYPE_INPUT) {
		_script_field_field__data_name_input("data_name", $_script_id, (isset($script_field["data_name"]) ? $script_field["data_name"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
	}else if ($current_field_type == SCRIPT_FIELD_TYPE_OUTPUT) {
		_script_field_field__data_name_output("data_name", (isset($script_field["data_name"]) ? $script_field["data_name"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
	}

	_script_field_field__name("name", (isset($script_field["name"]) ? $script_field["name"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));

	if ($current_field_type == SCRIPT_FIELD_TYPE_INPUT) {
		_script_field_field__regexp_match("regexp_match", (isset($script_field["regexp_match"]) ? $script_field["regexp_match"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
		_script_field_field__allow_empty("allow_empty", (isset($script_field["allow_empty"]) ? $script_field["allow_empty"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
	}else if ($current_field_type == SCRIPT_FIELD_TYPE_OUTPUT) {
		_script_field_field__update_rrd("update_rrd", (isset($script_field["update_rrd"]) ? $script_field["update_rrd"] : ""), (isset($script_field["id"]) ? $script_field["id"] : "0"));
	}

	_script_field_field__field_input_type_js_update((isset($script_field["field_input_type"]) ? $script_field["field_input_type"] : SCRIPT_FIELD_INPUT_CUSTOM), (empty($_script_field_id) ? 0 : $_script_field_id));

	html_end_box();

	form_hidden_box("id", $_script_field_id, "0");
	form_hidden_box("script_id", $_script_id, "0");
	form_hidden_box("field_type", $current_field_type, "0");
	form_hidden_box("action_post", "script_field_edit");

	form_save_button("scripts.php?action=edit&id=" . $_script_id);
}

?>
