<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

include ("./include/auth.php");
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
	global $registered_cacti_names;

	if (isset($_POST["save_component_data_input"])) {
		$data_input_id = api_data_input_save($_POST["id"], $_POST["name"], $_POST["input_string"], $_POST["type_id"]);

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: data_input.php?action=edit&id=" . (empty($data_input_id) ? $_POST["id"] : $data_input_id));
		}else{
			header("Location: data_input.php");
		}
	}elseif (isset($_POST["save_component_field"])) {
		$data_input_field_id = api_data_input_field_save($_POST["id"], $_POST["data_input_id"], $_POST["name"],
			$_POST["data_name"], $_POST["input_output"], (isset($_POST["update_rra"]) ? $_POST["update_rra"] : ""),
			(isset($_POST["type_code"]) ? $_POST["type_code"] : ""), (isset($_POST["regexp_match"]) ? $_POST["regexp_match"] : ""),
			(isset($_POST["allow_nulls"]) ? $_POST["allow_nulls"] : ""));

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
		form_confirm("Are You Sure?", "Are you sure you want to delete the field <strong>'" . db_fetch_cell("select name from data_input_fields where id=" . $_GET["id"]) . "'</strong>?", "data_input.php?action=edit&id=" . $_GET["data_input_id"], "data_input.php?action=field_remove&id=" . $_GET["id"] . "&data_input_id=" . $_GET["data_input_id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_data_input_field_remove($_GET["id"]);
	}
}

function field_edit() {
	global $colors, $registered_cacti_names, $fields_data_input_field_edit_1, $fields_data_input_field_edit_2, $fields_data_input_field_edit;

	if (!empty($_GET["id"])) {
		$field = db_fetch_row("select * from data_input_fields where id=" . $_GET["id"]);
	}

	if (!empty($_GET["type"])) {
		$current_field_type = $_GET["type"];
	}else{
		$current_field_type = $field["input_output"];
	}

	if ($current_field_type == "out") {
		$header_name = "Output";
	}elseif ($current_field_type == "in") {
		$header_name = "Input";
	}

	$data_input = db_fetch_row("select type_id,name from data_input where id=" . $_GET["data_input_id"]);

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
		display_custom_error_message("This script appears to have no input values, therefore there is nothing to add.");
		return;
	}

	html_start_box("<strong>$header_name Fields</strong> [edit: " . $data_input["name"] . "]", "98%", $colors["header_background"], "3", "center", "");

	$form_array = array();

	/* field name */
	if (($data_input["type_id"] == DATA_INPUT_TYPE_SCRIPT) && ($current_field_type == "in")) { /* script */
		$form_array = inject_form_variables($fields_data_input_field_edit_1, $header_name, $array_field_names, (isset($field) ? $field : array()));
	}elseif (($data_input["type_id"] == DATA_INPUT_TYPE_SNMP) || ($data_input["type_id"] == DATA_INPUT_TYPE_SNMP_QUERY) || ($data_input["type_id"] == DATA_INPUT_TYPE_SCRIPT_QUERY) || ($data_input["type_id"] == DATA_INPUT_TYPE_PHP_SCRIPT_SERVER) || ($data_input["type_id"] == DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER) || ($current_field_type == "out")) { /* snmp */
		$form_array = inject_form_variables($fields_data_input_field_edit_2, $header_name, (isset($field) ? $field : array()));
	}

	/* ONLY if the field is an input */
	if ($current_field_type == "in") {
		unset($fields_data_input_field_edit["update_rra"]);
	}elseif ($current_field_type == "out") {
		unset($fields_data_input_field_edit["regexp_match"]);
		unset($fields_data_input_field_edit["allow_nulls"]);
		unset($fields_data_input_field_edit["type_code"]);
	}

	draw_edit_form(array(
		"config" => array(),
		"fields" => $form_array + inject_form_variables($fields_data_input_field_edit, (isset($field) ? $field : array()), $current_field_type, $_GET)
		));

	html_end_box();

	form_save_button("data_input.php?action=edit&id=" . $_GET["data_input_id"]);
}

/* -----------------------
    Data Input Functions
   ----------------------- */

function data_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm("Are You Sure?", "Are you sure you want to delete the data input method <strong>'" . db_fetch_cell("select name from data_input where id=" . $_GET["id"]) . "'</strong>?", "data_input.php", "data_input.php?action=remove&id=" . $_GET["id"]);
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
		$header_label = "[edit: " . $data_input["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>Data Input Methods</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_data_input_edit, (isset($data_input) ? $data_input : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		html_start_box("<strong>Input Fields</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=field_edit&type=in&data_input_id=" . $_GET["id"]);
		print "<tr bgcolor='#" . $colors["header_panel_background"] . "'>";
			DrawMatrixHeaderItem("Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Found in Input String?",$colors["header_text"],1);
			DrawMatrixHeaderItem("Friendly Name",$colors["header_text"],2);
		print "</tr>";

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
			print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td><em>No Input Fields</em></td><td></td><td></td></tr>";
		}
		html_end_box();

		html_start_box("<strong>Output Fields</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=field_edit&type=out&data_input_id=" . $_GET["id"]);
		print "<tr bgcolor='#" . $colors["header_panel_background"] . "'>";
			DrawMatrixHeaderItem("Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Friendly Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Update RRA",$colors["header_text"],2);
		print "</tr>";

		$fields = db_fetch_assoc("select id,name,data_name,update_rra from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='out'");

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
					<?php print html_boolean_friendly($field["update_rra"]);?>
				</td>
				<td align="right">
					<a href="data_input.php?action=field_remove&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
		<?php
		}
		}else{
			print "<tr><td bgcolor='#" . $colors["form_alternate2"] . "' colspan=4><em>No Output Fields</em></td></tr>";
		}
		html_end_box();
	}

	form_save_button("data_input.php");
}

function data() {
	global $colors, $input_types;

	html_start_box("<strong>Data Input Scripts</strong>", "98%", $colors["header_background"], "3", "center", "data_input.php?action=edit");

	print "<tr bgcolor='#" . $colors["header_panel_background"] . "'>";
		DrawMatrixHeaderItem("Name",$colors["header_text"],1);
		DrawMatrixHeaderItem("Data Input Method",$colors["header_text"],1);
		DrawMatrixHeaderItem("&nbsp;",$colors["header_text"],1);
	print "</tr>";

	if (read_config_option("show_hidden") == "on") {
		$data_inputs = db_fetch_assoc("select * from data_input order by name");
	}else{
		$data_inputs = db_fetch_assoc("select * from data_input where reserved = '0' order by name");
	}

	$i = 0;
	if (sizeof($data_inputs) > 0) {
	foreach ($data_inputs as $data_input) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="data_input.php?action=edit&id=<?php print $data_input["id"];?>"><?php print $data_input["name"];?></a>
			</td>
			<td>
				<?php print $input_types{$data_input["type_id"]};?>
			</td>
			<td align="right">
				<a href="data_input.php?action=remove&id=<?php print $data_input["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td><em>No Data Input Methods</em></td></tr>";
	}
	html_end_box();
}
?>