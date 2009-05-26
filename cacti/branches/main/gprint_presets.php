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

include("./include/auth.php");

$gprint_actions = array(
	1 => "Delete"
	);

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		gprint_presets_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		gprint_presets();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}


/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $gprint_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from graph_templates_gprint where " . array_to_sql_or($selected_items, "id"));
		}
		header("Location: gprint_presets.php");
		exit;
	}

	/* setup some variables */
	$gprint_list = ""; $i = 0;

	/* loop through each of the items selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$gprint_list .= "<li>" . db_fetch_cell("select name from graph_templates_gprint where id=" . $matches[1]) . "<br>";
			$gprint_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $gprint_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='gprint_presets.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>Are you sure you want to delete the following GPRINT presets?</p>
					<p>$gprint_list</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($gprint_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one GPRINT preset.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='images/button_yes.gif' alt='Save' align='middle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($gprint_array) ? serialize($gprint_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='gprint_presets.php'><img src='images/button_no.gif' alt='Cancel' align='middle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_gprint_presets"])) {
		$save["id"] = $_POST["id"];
		$save["hash"] = get_hash_gprint($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["gprint_text"] = form_input_validate($_POST["gprint_text"], "gprint_text", "", false, 3);

		if (!is_error_message()) {
			$gprint_preset_id = sql_save($save, "graph_templates_gprint");

			if ($gprint_preset_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: gprint_presets.php?action=edit&id=" . (empty($gprint_preset_id) ? $_POST["id"] : $gprint_preset_id));
			exit;
		}else{
			header("Location: gprint_presets.php");
			exit;
		}
	}
}

/* -----------------------------------
    gprint_presets - GPRINT Presets
   ----------------------------------- */

function gprint_presets_edit() {
	global $colors, $fields_grprint_presets_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$gprint_preset = db_fetch_row("select * from graph_templates_gprint where id=" . $_GET["id"]);
		$header_label = "[edit: " . $gprint_preset["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>GPRINT Presets</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_grprint_presets_edit, (isset($gprint_preset) ? $gprint_preset : array()))
		));

	html_end_box();

	form_save_button_alt();
}

function gprint_presets() {
	global $colors, $gprint_actions;

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
		kill_session_var("sess_gprint_current_page");
		kill_session_var("sess_gprint_filter");
		kill_session_var("sess_gprint_sort_column");
		kill_session_var("sess_gprint_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);

	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_gprint_current_page", "1");
	load_current_session_value("filter", "sess_gprint_filter", "");
	load_current_session_value("sort_column", "sess_gprint_sort_column", "name");
	load_current_session_value("sort_direction", "sess_gprint_sort_direction", "ASC");

	html_start_box("<strong>GPRINT Presets</strong>", "100%", $colors["header"], "3", "center", "gprint_presets.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_gprint">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;<input type="submit" Value="Go" name="go" align="middle">
						<input type="submit" Value="Clear" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<input type='hidden' name='page' value='1'>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(id)
		FROM graph_templates_gprint
		$sql_where");

	$template_list = db_fetch_assoc("SELECT
		id,
		name
		FROM graph_templates_gprint
		$sql_where
		ORDER BY " . $_REQUEST['sort_column'] . " " . $_REQUEST['sort_direction']);


	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 11, "gprint_presets.php?filter=" . $_REQUEST["filter"]);
	print $nav;

	$display_text = array(
		"name" => array("Name", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color('line' . $template["id"], true, true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("gprint_presets.php?action=edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $template["name"]) : $template["name"]) . "</a>", $template["id"]);
			form_checkbox_cell($template["name"], $template["id"]);
			form_end_row();
		}
		print $nav;
	}else{
		print "<tr><td><em>No Items</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($gprint_actions);

	print "</form>\n";
}
