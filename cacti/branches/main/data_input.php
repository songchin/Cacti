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
include_once("./lib/utility.php");

define("MAX_DISPLAY_PAGES", 21);

$di_actions = array(
	"1" => "Delete"
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'field_remove':
		field_remove();

		header("Location: data_input.php?action=edit&id=" . $_GET["data_input_id"]);
		break;
	case 'field_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		field_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $registered_cacti_names;

	if (isset($_POST["save_component_data_input"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		/* ==================================================== */

		$save["id"] = $_POST["id"];
		$save["hash"] = get_hash_data_input($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["input_string"] = form_input_validate($_POST["input_string"], "input_string", "", true, 3);
		$save["type_id"] = form_input_validate($_POST["type_id"], "type_id", "", true, 3);

		if (!is_error_message()) {
			$data_input_id = sql_save($save, "data_input");

			if ($data_input_id) {
				raise_message(1);

				/* get a list of each field so we can note their sequence of occurance in the database */
				if (!empty($_POST["id"])) {
					db_execute("update data_input_fields set sequence=0 where data_input_id=" . $_POST["id"]);

					generate_data_input_field_sequences(get_request_var_post("input_string"), get_request_var_post("id"));
				}

				push_out_data_input_method($data_input_id);
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: data_input.php?action=edit&id=" . (empty($data_input_id) ? $_POST["id"] : $data_input_id));
		}else{
			header("Location: data_input.php");
		}
		exit;
	}elseif (isset($_POST["save_component_field"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("data_input_id"));
		input_validate_input_regex(get_request_var_post("input_output"), "/^(in|out)$/");
		/* ==================================================== */

		$save["id"] = $_POST["id"];
		$save["hash"] = get_hash_data_input($_POST["id"], "data_input_field");
		$save["data_input_id"] = $_POST["data_input_id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["data_name"] = form_input_validate($_POST["data_name"], "data_name", "", false, 3);
		$save["input_output"] = $_POST["input_output"];
		$save["update_rra"] = form_input_validate((isset($_POST["update_rra"]) ? $_POST["update_rra"] : ""), "update_rra", "", true, 3);
		$save["sequence"] = $_POST["sequence"];
		$save["type_code"] = form_input_validate((isset($_POST["type_code"]) ? $_POST["type_code"] : ""), "type_code", "", true, 3);
		$save["regexp_match"] = form_input_validate((isset($_POST["regexp_match"]) ? $_POST["regexp_match"] : ""), "regexp_match", "", true, 3);
		$save["allow_nulls"] = form_input_validate((isset($_POST["allow_nulls"]) ? $_POST["allow_nulls"] : ""), "allow_nulls", "", true, 3);

		if (!is_error_message()) {
			$data_input_field_id = sql_save($save, "data_input_fields");

			if ($data_input_field_id) {
				raise_message(1);

				if ((!empty($data_input_field_id)) && ($_POST["input_output"] == "in")) {
					generate_data_input_field_sequences(db_fetch_cell("select input_string from data_input where id=" . $_POST["data_input_id"]), $_POST["data_input_id"]);
				}
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: data_input.php?action=field_edit&data_input_id=" . $_POST["data_input_id"] . "&id=" . (empty($data_input_field_id) ? $_POST["id"] : $data_input_field_id) . (!empty($_POST["input_output"]) ? "&type=" . $_POST["input_output"] : ""));
		}else{
			header("Location: data_input.php?action=edit&id=" . $_POST["data_input_id"]);
		}
		exit;
	}
}

function form_actions() {
	global $colors, $di_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") === "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $data_input_id) {
				/* ================= input validation ================= */
				input_validate_input_number($data_input_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM data_template_data WHERE data_input_id=$data_input_id LIMIT 1"))) {
					$bad_ids[] = $data_input_id;
				}else{
					$data_input_ids[] = $data_input_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $data_input_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>Data Input Method " . $data_input_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_data_input_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('data_input_ref_int');
			}

			if (isset($data_input_ids)) {
			foreach($data_input_ids as $data_input_id) {
				data_remove($data_input_id);
			}
			}
		}

		header("Location: data_input.php");
		exit;
	}

	/* setup some variables */
	$di_list = ""; $i = 0; $di_array = array();

	/* loop through each of the data queries and process them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$di_list .= "<li>" . db_fetch_cell("SELECT name FROM data_input WHERE id='" . $matches[1] . "'") . "<br>";
			$di_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $di_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='data_input.php' method='post'>\n";

	if (sizeof($di_array)) {
		if (get_request_var_post("drp_action") === ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") === "1") { /* delete */
			$graphs = array();

			print "
				<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following data input methods?") . "</p>
						<p>$di_list</p>
					</td>
				</tr>\n";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Data Input Method.  Please select 'Return' to return to the previous menu.") . "</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($di_array) || get_request_var_post("drp_action") === ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($di_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* --------------------------
    CDEF Item Functions
   -------------------------- */

function field_remove() {
	global $registered_cacti_names;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("data_input_id"));
	/* ==================================================== */

	if ((read_config_option("deletion_verification") == CHECKED) && (!isset($_GET["confirm"]))) {
		include(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(__("Are You Sure?"), __("Are you sure you want to delete the field") ." <strong>'" . db_fetch_cell("select name from data_input_fields where id=" . $_GET["id"]) . "'</strong>?", "data_input.php?action=edit&id=" . $_GET["data_input_id"], "data_input.php?action=field_remove&id=" . $_GET["id"] . "&data_input_id=" . $_GET["data_input_id"]);
		include(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("deletion_verification") == "") || (isset($_GET["confirm"]))) {
		/* get information about the field we're going to delete so we can re-order the seqs */
		$field = db_fetch_row("select input_output,data_input_id from data_input_fields where id=" . $_GET["id"]);

		db_execute("delete from data_input_fields where id=" . $_GET["id"]);
		db_execute("delete from data_input_data where data_input_field_id=" . $_GET["id"]);

		/* when a field is deleted; we need to re-order the field sequences */
		if (($field["input_output"] == "in") && (preg_match_all("/<([_a-zA-Z0-9]+)>/", db_fetch_cell("select input_string from data_input where id=" . $field["data_input_id"]), $matches))) {
			$j = 0;
			for ($i=0; ($i < count($matches[1])); $i++) {
				if (in_array($matches[1][$i], $registered_cacti_names) == false) {
					$j++; db_execute("update data_input_fields set sequence=$j where data_input_id=" . $field["data_input_id"] . " and input_output='in' and data_name='" . $matches[1][$i] . "'");
				}
			}
		}
	}
}

function field_edit() {
	global $colors, $registered_cacti_names;
	require_once(CACTI_BASE_PATH . "/lib/data_input/data_input_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("data_input_id"));
	input_validate_input_regex(get_request_var("type"), "/^(in|out)$/");
	/* ==================================================== */

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
	if (($current_field_type == "in") && (preg_match_all("/<([_a-zA-Z0-9]+)>/", db_fetch_cell("select input_string from data_input where id=" . (get_request_var("data_input_id") ? get_request_var("data_input_id") : $field["data_input_id"])), $matches))) {
		for ($i=0; ($i < count($matches[1])); $i++) {
			if (in_array($matches[1][$i], $registered_cacti_names) == false) {
				$current_field_name = $matches[1][$i];
				$array_field_names[$current_field_name] = $current_field_name;
			}
		}
	}

	/* if there are no input fields to choose from, complain */
	if ((!isset($array_field_names)) && (isset($_GET["type"]) ? $_GET["type"] == "in" : false) && ($data_input["type_id"] == "1")) {
		display_custom_error_message(__("This script appears to have no input values, therefore there is nothing to add."));
		return;
	}

	html_start_box("<strong>$header_name " . __("Fields") . "</strong> " . __("[edit: ") . $data_input["name"] . "]", "100", $colors["header"], "3", "center", "");

	$form_array = array();

	/* field name */
	if ((($data_input["type_id"] == "1") || ($data_input["type_id"] == "5")) && ($current_field_type == "in")) { /* script */
		$form_array = inject_form_variables(data_input_field1_form_list(), $header_name, $array_field_names, (isset($field) ? $field : array()));
	}elseif (($data_input["type_id"] == "2") ||
			($data_input["type_id"] == "3") ||
			($data_input["type_id"] == "4") ||
			($data_input["type_id"] == "6") ||
			($data_input["type_id"] == "7") ||
			($data_input["type_id"] == "8") ||
			($current_field_type == "out")) { /* snmp */
		$form_array = inject_form_variables(data_input_field2_form_list(), $header_name, (isset($field) ? $field : array()));
	}

	$fields_data_input_field_edit = data_input_field_form_list();
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

	form_save_button_alt("path!data_input.php|action!edit|id!" . get_request_var("data_input_id"));
}

/* -----------------------
    Data Input Functions
   ----------------------- */

function data_remove($id) {
	$data_input_fields = db_fetch_assoc("select id from data_input_fields where data_input_id=" . $id);

	if (is_array($data_input_fields)) {
		foreach ($data_input_fields as $data_input_field) {
			db_execute("delete from data_input_data where data_input_field_id=" . $data_input_field["id"]);
		}
	}

	db_execute("delete from data_input where id=" . $id);
	db_execute("delete from data_input_fields where data_input_id=" . $id);
}

function data_edit() {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_input/data_input_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$data_input = db_fetch_row("select * from data_input where id=" . $_GET["id"]);
		$header_label = "[edit: " . $data_input["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='data_input_edit'>\n";
	html_start_box("<strong>" . __("Data Input Methods") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'data_input','left wp100');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables(data_input_form_list(), (isset($data_input) ? $data_input : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	if (!empty($_GET["id"])) {
		html_start_box("<strong>" . __("Input Fields") . "</strong>", "100", $colors["header"], 0, "center", "data_input.php?action=field_edit&type=in&data_input_id=" . $_GET["id"]);
		$header_items = array(__("Name"), __("Field Order"), __("Friendly Name"));
		print "<tr><td>";
		html_header($header_items, 2, true, 'data_input_fields', 'left wp100');

		$fields = db_fetch_assoc("select id,data_name,name,sequence from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='in' order by sequence, data_name");

		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			form_alternate_row_color("input_fields" . $field["id"]);
				?>
				<td>
					<a class="linkEditMain" href="<?php print htmlspecialchars("data_input.php?action=field_edit&id=" . $field["id"] . "&data_input_id=" . $_GET["id"]);?>"><?php print $field["data_name"];?></a>
				</td>
				<td>
					<?php print $field["sequence"]; if ($field["sequence"] == "0") { print " (Not In Use)"; }?>
				</td>
				<td>
					<?php print $field["name"];?>
				</td>
				<td align="right">
					<a href="<?php print htmlspecialchars("data_input.php?action=field_remove&id=" . $field["id"] . "&data_input_id=" . $_GET["id"]);?>">
						<img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='right'>
					</a>
				</td>
		<?php
		form_end_row();
		}
		}else{
			print "<tr><td><em>" . __("No Input Fields") . "</em></td></tr>";
		}
		print "</table></td></tr>";		/* end of html_header */
		html_end_box();

		html_start_box("<strong>" . __("Output Fields"). "</strong>", "100", $colors["header"], 0, "center", "data_input.php?action=field_edit&type=out&data_input_id=" . $_GET["id"]);
		$header_items = array(__("Name"), __("Field Order"), __("Friendly Name"), __("Update RRA"));
		print "<tr><td>";
		html_header($header_items, 2, true, 'data_output_fields', 'left wp100');

		$fields = db_fetch_assoc("select id,name,data_name,update_rra,sequence from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='out' order by sequence, data_name");
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			form_alternate_row_color("output_fields" . $field["id"]);
				?>
				<td>
					<a class="linkEditMain" href="<?php print htmlspecialchars("data_input.php?action=field_edit&id=" . $field["id"] . "&data_input_id=". $_GET["id"]);?>"><?php print $field["data_name"];?></a>
				</td>
				<td>
					<?php print $field["sequence"]; if ($field["sequence"] == "0") { print __(" (Not In Use)"); }?>
				</td>
				<td>
					<?php print $field["name"];?>
				</td>
				<td>
					<?php print html_boolean_friendly($field["update_rra"]);?>
				</td>
				<td align="right">
					<a href="<?php print htmlspecialchars("data_input.php?action=field_remove&id=" . $field["id"] . "&data_input_id=" . $_GET["id"]);?>">
						<img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='right'>
					</a>
				</td>
		<?php
		form_end_row();
		}
		}else{
			print "<tr><td><em>" . __("No Output Fields") . "</em></td></tr>";
		}
		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
	}

	form_save_button_alt("url!data_input.php");
}

function data() {
	global $colors, $di_actions, $item_rows;
	require(CACTI_BASE_PATH . "/include/data_input/data_input_arrays.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up search string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_data_input_current_page");
		kill_session_var("sess_data_input_rows");
		kill_session_var("sess_data_input_filter");
		kill_session_var("sess_data_input_sort_column");
		kill_session_var("sess_data_input_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	?>
	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?rows=' + objForm.rows.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_data_input_current_page", "1");
	load_current_session_value("rows", "sess_data_input_rows", "-1");
	load_current_session_value("filter", "sess_data_input_filter", "");
	load_current_session_value("sort_column", "sess_data_input_sort_column", "name");
	load_current_session_value("sort_direction", "sess_data_input_sort_direction", "ASC");

	html_start_box("<strong>" . __("Data Input Methods") . "</strong>", "100", $colors["header"], "3", "center", "data_input.php?action=edit", true);
	?>
	<tr class="rowAlternate2 noprint">
		<td class="noprint">
			<form name="form_graph_id" action="data_input.php">
			<table cellpadding="0" cellspacing="3">
				<tr class="noprint">
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyFilterChange(document.form_graph_id)">
							<option value="-1"<?php if (get_request_var_request("rows") == "-1") {?> selected<?php }?>>Default</option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if (get_request_var_request("rows") == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(FALSE);

	html_start_box("", "100", $colors["header"], "0", "center", "");

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (data_input.name like '%%" . $_REQUEST["filter"] . "%%')";

	$sql_where .= " AND (data_input.name!='Get Script Data (Indexed)'
		AND data_input.name!='Get Script Server Data (Indexed)'
		AND data_input.name!='Get SNMP Data'
		AND data_input.name!='Get SNMP Data (Indexed)')";

	$total_rows = db_fetch_cell("SELECT
		count(*)
		FROM data_input
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$data_inputs = db_fetch_assoc("SELECT *
		FROM data_input
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') . "
		LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "data_input.php");

	print $nav;
	html_end_box(FALSE);

	$display_text = array(
		"name" => array(__("Name"), "ASC"),
		"type_id" => array(__("Data Input Method"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($data_inputs) > 0) {
		foreach ($data_inputs as $data_input) {
			/* hide system types */
			form_alternate_row_color('line' . $data_input["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("data_input.php?action=edit&id=" . $data_input["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $data_input["name"]) : $data_input["name"]) . "</a>", $data_input["id"]);
			form_selectable_cell($input_types{$data_input["type_id"]}, $data_input["id"]);
			form_checkbox_cell($data_input["name"], $data_input["id"]);
			form_end_row();
		}

		form_end_table();

		print $nav;
	}else{
		print "<tr><td><em>" . __("No Data Input Methods") . "</em></td></tr>";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($di_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
