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
include_once('./lib/api_data_input.php');

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'field_remove':
		field_remove();

		header("Location: data_input.php?action=edit&id=" . $_GET["data_input_id"]);
		break;
	case 'field_edit':
		include_once("./include/top_header.php");

		field_edit();

		include_once("./include/bottom_footer.php");
		break;
	case 'remove':
		data_remove();

		header("Location: data_input.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		data_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		data();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_data_input"])) {
		$data_input_id = api_data_input_save($_POST["id"], $_POST["name"], $_POST["input_string"], $_POST["type_id"]);

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: data_input.php?action=edit&id=" . (empty($data_input_id) ? $_POST["id"] : $data_input_id));
		}else{
			header("Location: data_input.php");
		}
	}elseif (isset($_POST["save_component_field"])) {
		$data_input_field_id = api_data_input_field_save($_POST["id"], $_POST["data_input_id"], (isset($_POST["field_input_type"]) ? $_POST["field_input_type"] : ""),
			(isset($_POST["field_input_value"]) ? $_POST["field_input_value"] : ""), $_POST["name"], $_POST["data_name"], $_POST["input_output"], (isset($_POST["update_rrd"]) ?
			$_POST["update_rrd"] : ""), (isset($_POST["regexp_match"]) ? $_POST["regexp_match"] : ""), (isset($_POST["allow_empty"]) ?
			$_POST["allow_empty"] : ""));
		if (is_error_message()) {
			header("Location: data_input.php?action=field_edit&data_input_id=" . $_POST["data_input_id"] . "&id=" . (empty($data_input_field_id) ? $_POST["id"] : $data_input_field_id) . (!empty($_POST["input_output"]) ? "&type=" . $_POST["input_output"] : ""));
		}else{
			header("Location: data_input.php?action=edit&id=" . $_POST["data_input_id"]);
		}
	}
}

/* ------------------------------
    Data Input Field Functions
   ------------------------------ */

function field_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the field") . "<strong>'" . db_fetch_cell("select name from data_input_fields where id=" . $_GET["id"]) . "'</strong>?", "data_input.php?action=edit&id=" . $_GET["data_input_id"], "data_input.php?action=field_remove&id=" . $_GET["id"] . "&data_input_id=" . $_GET["data_input_id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_data_input_field_remove($_GET["id"]);
	}
}

function field_edit() {
	global $colors, $registered_cacti_names, $fields_data_input_field_edit, $fields_data_input_field_edit_input, $fields_data_input_field_edit_input_custom, $fields_data_input_field_edit_input_device;

	if (!empty($_GET["id"])) {
		$field = db_fetch_row("select * from data_input_fields where id=" . $_GET["id"]);

		$header_label = _("[edit: ") . $field["data_name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	$data_input = db_fetch_row("select type_id,name from data_input where id=" . $_GET["data_input_id"]);

	if ((isset($_GET["type"])) && (($_GET["type"] == "in") || ($_GET["type"] == "out"))) {
		$current_field_type = $_GET["type"];
	}else{
		$current_field_type = $field["input_output"];
	}

	if ($current_field_type == "out") {
		$current_field_type_friendly = _("Output");
	}elseif ($current_field_type == "in") {
		$current_field_type_friendly = _("Input");
	}

	if ($current_field_type == "in") {
		/* determine current value for 'field_input_type' */
		if (isset($_GET["field_input_type"])) {
			$_field_input_type = $_GET["field_input_type"];
		}else if (isset($field["field_input_type"])) {
			$_field_input_type = $field["field_input_type"];
		}else{
			$_field_input_type = SCRIPT_FIELD_INPUT_CUSTOM;
		}

		/* fill in data input field information (field input type dropdown) */
		$_field_input_form = array("field_input_type" => $fields_data_input_field_edit_input["field_input_type"]);

		$_field_input_form["field_input_type"]["redirect_url"] = "data_input.php?action=field_edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . (!empty($_GET["type"]) ? "&type=in" : "") . "&data_input_id=" . $_GET["data_input_id"] . "&field_input_type=|dropdown_value|";
		$_field_input_form["field_input_type"]["form_index"] = "0";

		/* grab the appropriate field input type form array */
		if ($_field_input_type == SCRIPT_FIELD_INPUT_CUSTOM) {
			$_field_input_type_form = $fields_data_input_field_edit_input_custom;
		}else if ($_field_input_type == SCRIPT_FIELD_INPUT_DEVICE) {
			$_field_input_type_form = $fields_data_input_field_edit_input_device;

			/* determine current value for 'field_input_value' */
			if (isset($_GET["field_input_value"])) {
				$_field_input_value = $_GET["field_input_value"];
			}else if (isset($field["field_input_value"])) {
				$_field_input_value = $field["field_input_value"];
			}else{
				$_field_input_value = "hostname";
			}

			/* fill in data input field information (device fields dropdown) */
			$_field_input_type_form["field_input_value"]["redirect_url"] = "data_input.php?action=field_edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . (!empty($_GET["type"]) ? "&type=in" : "") . (!empty($_GET["field_input_type"]) ? "&field_input_type=" . $_GET["field_input_type"] : "") . "&data_input_id=" . $_GET["data_input_id"] . "&field_input_value=|dropdown_value|";
			$_field_input_type_form["field_input_value"]["form_index"] = "0";
			$_field_input_type_form["field_input_value"]["value"] = $_field_input_value;
		}else{
			$_field_input_type_form = array();
		}

		$_field_input_form += $_field_input_type_form;

		/* ==================== Box: Field Input ==================== */

		html_start_box("<strong>"._("Field Input")."</strong>", "98%", $colors["header_background_template"], "3", "center", "");

		draw_edit_form(
			array(
				"config" => array(
					"form_name" => "form_data_input_field"
				),
				"fields" => inject_form_variables($_field_input_form, (isset($field) ? $field : array()))
				)
			);

		html_end_box();
	}

	/* obtain a list of available fields for this given field type (input/output) */
	if (preg_match_all("/<([_a-zA-Z0-9]+)>/", db_fetch_cell("select $current_field_type" . "put_string from data_input where id=" . ($_GET["data_input_id"] ? $_GET["data_input_id"] : $field["data_input_id"])), $matches)) {
		for ($i=0; ($i < count($matches[1])); $i++) {
			if (in_array($matches[1][$i], $registered_cacti_names) == false) {
				$current_field_name = $matches[1][$i];
				$array_field_names[$current_field_name] = $current_field_name;
			}
		}
	}

	/* if there are no input fields to choose from, complain */
	if ((!isset($array_field_names)) && (isset($_GET["type"]) ? $_GET["type"] == "in" : false) && ($data_input["type_id"] == DATA_INPUT_TYPE_SCRIPT)) {
		display_custom_error_message(_("This script appears to have no input values, therefore there is nothing to add."));
		return;
	}

	/* ONLY if the field is an input */
	if ($current_field_type == "in") {
		unset($fields_data_input_field_edit["update_rra"]);
	}elseif ($current_field_type == "out") {
		unset($fields_data_input_field_edit["regexp_match"]);
		unset($fields_data_input_field_edit["allow_empty"]);
	}

	/* ==================== Box: Input/Output Field ==================== */

	html_start_box("<strong>$current_field_type_friendly Field</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_data_input_field_edit, (isset($field) ? $field : array()), $current_field_type_friendly)
		));

	html_end_box();

	form_hidden_box("data_input_id", $_GET["data_input_id"], "0");
	form_hidden_box("input_output", $current_field_type, "");

	form_save_button("data_input.php?action=edit&id=" . $_GET["data_input_id"]);
}

/* -----------------------
    Data Input Functions
   ----------------------- */

function data_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the data input method") ." <strong>'" . db_fetch_cell("select name from data_input where id=" . $_GET["id"]) . "'</strong>?", "data_input.php", "data_input.php?action=remove&id=" . $_GET["id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_data_input_remove($_GET["id"]);
	}
}

function data_edit() {
	global $colors, $fields_data_input_edit;

	if (!empty($_GET["id"])) {
		$data_input = db_fetch_row("select * from data_input where id=" . $_GET["id"]);

		$header_label = _("[edit: ") . $data_input["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	/* ==================== Box: Data Input Methods ==================== */

	html_start_box("<strong>" . _("Custom Scripts") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_data_input_edit, (isset($data_input) ? $data_input : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		/* ==================== Box: Input Fields ==================== */

		html_start_box("<strong>"._("Input Fields")."</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=field_edit&type=in&data_input_id=" . $_GET["id"]);

		html_header(array(_("Name"), _("Found in Input String?"), _("Friendly Name")), 2);

		$fields = db_fetch_assoc("select id,data_name,name from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='in' order by data_name");

		/* locate all fields in the input string */
		preg_match_all("/<([_a-zA-Z0-9]+)>/", $data_input["input_string"], $matches);

		$i = 0;
		if (sizeof($fields) > 0) {
			foreach ($fields as $field) {
				form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
					?>
					<td>
						<a class="linkEditMain" href="data_input.php?action=field_edit&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><?php print $field["data_name"];?></a>
					</td>
					<td>
						<?php print ((isset($matches)) && (in_array($field["data_name"], $matches[1])) ? "<span style='color: green; font-weight: bold;'>Found</span>" : "<span style='color: red; font-weight: bold;'>Not Found</span>");?>
					</td>
					<td>
						<?php print $field["name"];?>
					</td>
					<td align="right">
						<a href="data_input.php?action=field_remove&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
					</td>
				</tr>
			<?php
			}
		}else{
			print "<tr><td><em>" . _("No Input Fields Defined") . "</em></td></tr>";
		}

		html_end_box();

		/* ==================== Box: Output Fields ==================== */

		html_start_box("<strong>" . _("Output Fields") . "</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=field_edit&type=out&data_input_id=" . $_GET["id"]);

		html_header(array(_("Name"), _("Friendly Name"), _("Update RRD")), 2);

		$fields = db_fetch_assoc("select id,name,data_name,update_rrd from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='out'");

		$i = 0;
		if (sizeof($fields) > 0) {
			foreach ($fields as $field) {
				form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
					?>
					<td>
						<a class="linkEditMain" href="data_input.php?action=field_edit&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><?php print $field["data_name"];?></a>
					</td>
					<td>
						<?php print $field["name"];?>
					</td>
					<td>
						<?php print html_boolean_friendly($field["update_rrd"]);?>
					</td>
					<td align="right">
						<a href="data_input.php?action=field_remove&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
					</td>
				</tr>
			<?php
			}
		}else{
			print "<tr><td bgcolor='#" . $colors["form_alternate2"] . "' colspan=4><em>" . _("No Output Fields") . "</em></td></tr>";
		}
		html_end_box();
	}

	form_save_button("data_input.php");
}

function data() {
	global $colors;

	html_start_box("<strong>" . _("Custom Scripts") . "</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=edit");

	html_header(array(_("Name")), 2);

	$data_inputs = db_fetch_assoc("select * from data_input order by name");

	$i = 0;
	if (sizeof($data_inputs) > 0) {
		foreach ($data_inputs as $data_input) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="data_input.php?action=edit&id=<?php print $data_input["id"];?>"><?php print $data_input["name"];?></a>
				</td>
				<td align="right">
					<a href="data_input.php?action=remove&id=<?php print $data_input["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
		<?php
		}
	}else{
		print "<tr><td><em>" . _("No Data Input Methods") . "</em></td></tr>";
	}

	html_end_box();
}
?>
