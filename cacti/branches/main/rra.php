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

$rra_actions = array(
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

		rra_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		rra();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}


/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $rra_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from rra where " . array_to_sql_or($selected_items, "id"));
			db_execute("delete from rra_cf where " . array_to_sql_or($selected_items, "rra_id"));
		}
		header("Location: rra.php");
		exit;
	}

	/* setup some variables */
	$rra_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$rra_list .= "<li>" . db_fetch_cell("select name from rra where id=" . $matches[1]) . "<br>";
			$rra_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $rra_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='rra.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>Are you sure you want to delete the following RRAs?</p>
					<p>$rra_list</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($rra_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one RRA.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='images/button_yes.gif' alt='Save' align='middle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($rra_array) ? serialize($rra_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='rra.php'><img src='images/button_no.gif' alt='Cancel' align='middle' border='0'></a>
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
	if (isset($_POST["save_component_rra"])) {
		$save["id"] = $_POST["id"];
		$save["hash"] = get_hash_round_robin_archive($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["x_files_factor"] = form_input_validate($_POST["x_files_factor"], "x_files_factor", "^[01]?(\.[0-9]+)?$", false, 3);
		$save["steps"] = form_input_validate($_POST["steps"], "steps", "^[0-9]*$", false, 3);
		$save["rows"] = form_input_validate($_POST["rows"], "rows", "^[0-9]*$", false, 3);
		$save["timespan"] = form_input_validate($_POST["timespan"], "timespan", "^[0-9]*$", false, 3);

		if (!is_error_message()) {
			$rra_id = sql_save($save, "rra");

			if ($rra_id) {
				raise_message(1);

				db_execute("delete from rra_cf where rra_id=$rra_id");

				if (isset($_POST["consolidation_function_id"])) {
					for ($i=0; ($i < count($_POST["consolidation_function_id"])); $i++) {
						/* ================= input validation ================= */
						input_validate_input_number($_POST["consolidation_function_id"][$i]);
						/* ==================================================== */

						db_execute("insert into rra_cf (rra_id,consolidation_function_id)
							values ($rra_id," . $_POST["consolidation_function_id"][$i] . ")");
					}
				}
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: rra.php?action=edit&id=" . (empty($rra_id) ? $_POST["id"] : $rra_id));
		}else{
			header("Location: rra.php");
		}
	}
}

/* -------------------
    RRA Functions
   ------------------- */

function rra_edit() {
	global $colors, $fields_rra_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$rra = db_fetch_row("select * from rra where id=" . $_GET["id"]);
		$header_label = "[edit: " . $rra["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>Round Robin Archives</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_rra_edit, (isset($rra) ? $rra : array()))
		));

	html_end_box();

	form_save_button_alt();
}

function rra() {
	global $colors, $rra_actions;

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
		kill_session_var("sess_rra_current_page");
		kill_session_var("sess_rra_filter");
		kill_session_var("sess_rra_sort_column");
		kill_session_var("sess_rra_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);

	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_rra_current_page", "1");
	load_current_session_value("filter", "sess_rra_filter", "");
	load_current_session_value("sort_column", "sess_rra_sort_column", "name");
	load_current_session_value("sort_direction", "sess_rra_sort_direction", "ASC");

	html_start_box("<strong>Round Robin Archives</strong>", "100%", $colors["header"], "3", "center", "rra.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_rra">
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
	$sql_where = "WHERE (rra.name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(rra.id)
		FROM rra
		$sql_where");

	$rra_list = db_fetch_assoc("SELECT
		id,
		name,
		rows,
		steps,
		timespan
		FROM rra
		$sql_where
		ORDER BY " . $_REQUEST['sort_column'] . " " . $_REQUEST['sort_direction']);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 11, "rra.php?filter=" . $_REQUEST["filter"]);
	print $nav;

	$display_text = array(
		"name" => array("Name", "ASC"),
		"steps" => array("Steps", "ASC"),
		"rows" => array("Rows", "ASC"),
		"timespan" => array("Timespan", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($rra_list) > 0) {
		foreach ($rra_list as $rra) {
			form_alternate_row_color('line' . $rra["id"], true, true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("rra.php?action=edit&id=" . $rra["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $rra["name"]) : $rra["name"]) . "</a>", $rra["id"]);
			form_selectable_cell($rra["steps"], $rra["id"]);
			form_selectable_cell($rra["rows"], $rra["id"]);
			form_selectable_cell($rra["timespan"], $rra["id"]);
			form_checkbox_cell($rra["name"], $rra["id"]);
			form_end_row();
		}
		print $nav;
	}else{
		print "<tr><td><em>No RRAs</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($rra_actions);

	print "</form>\n";
}

