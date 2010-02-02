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

$rra_actions = array(
	ACTION_NONE => __("None"),
	"1" => __("Delete")
	);

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
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
	global $colors, $rra_actions, $messages;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") === "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $rra_id) {
				/* ================= input validation ================= */
				input_validate_input_number($rra_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM data_template_data_rra WHERE rra_id=$rra_id LIMIT 1"))) {
					$bad_ids[] = $rra_id;
				}else{
					$rra_ids[] = $rra_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $rra_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>RRA " . $rra_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_rra_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('rra_ref_int');
			}

			if (isset($rra_ids)) {
				db_execute("delete from rra where " . array_to_sql_or($rra_ids, "id"));
				db_execute("delete from rra_cf where " . array_to_sql_or($rra_ids, "rra_id"));
			}
		}
		header("Location: rra.php");
		exit;
	}

	/* setup some variables */
	$rra_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$rra_list .= "<li>" . db_fetch_cell("select name from rra where id=" . $matches[1]) . "<br>";
			$rra_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $rra_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='rra.php' method='post'>\n";

	if (isset($rra_array)) {
		if (get_request_var_post("drp_action") === ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") === "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("Are you sure you want to delete the following RRAs?") . "</p>
						<p><ul>$rra_list</ul></p>
					</td>
				</tr>\n
				";
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . __("You must select at least one RRA.") . "</span></td></tr>\n";
	}

	print "<div><input type='hidden' name='action' value='actions'></div>";
	print "<div><input type='hidden' name='selected_items' value='" . (isset($rra_array) ? serialize($rra_array) : '') . "'></div>";
	print "<div><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'></div>";

	if (!isset($rra_array) || get_request_var_post("drp_action") === ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($rra_array), get_request_var_post("drp_action"));
	}

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
		exit;
	}
}

/* -------------------
    RRA Functions
   ------------------- */

function rra_edit() {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_rra_info.php");

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$rra = db_fetch_row("select * from rra where id=" . $_GET["id"]);
		$header_label = "[edit: " . $rra["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='rra_edit'>\n";
	html_start_box("<strong>" . __("Round Robin Archives") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array("Field", "Value");
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_rra_edit');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables(preset_rra_form_list(), (isset($rra) ? $rra : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	form_save_button_alt();
}

function rra() {
	global $colors, $rra_actions, $item_rows;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

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
		kill_session_var("sess_rra_rows");
		kill_session_var("sess_rra_filter");
		kill_session_var("sess_rra_sort_column");
		kill_session_var("sess_rra_sort_direction");

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
	load_current_session_value("page", "sess_rra_current_page", "1");
	load_current_session_value("rows", "sess_rra_rows", "-1");
	load_current_session_value("filter", "sess_rra_filter", "");
	load_current_session_value("sort_column", "sess_rra_sort_column", "name");
	load_current_session_value("sort_direction", "sess_rra_sort_direction", "ASC");

	html_start_box("<strong>" . __("Round Robin Archives") . "</strong>", "100", $colors["header"], "3", "center", "rra.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name='form_rra' action="<?php print basename($_SERVER['PHP_SELF']);?>" method="post">
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td class="w1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td class="w1">
						<select name="rows" onChange="applyFilterChange(document.form_rra)">
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
			<input type='hidden' name='page' value='1'>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (rra.name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(rra.id)
		FROM rra
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$rra_list = db_fetch_assoc("SELECT *
		FROM rra
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 11, "rra.php?filter=" . $_REQUEST["filter"]);

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name" => array(__("Name"), "ASC"),
		"xff" => array(__("X Files Factor"), "ASC"),
		"steps" => array(__("Steps"), "ASC"),
		"rows" => array(__("Rows"), "ASC"),
		"timespan" => array(__("Timespan"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($rra_list) > 0) {
		foreach ($rra_list as $rra) {
			form_alternate_row_color('line' . $rra["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("rra.php?action=edit&id=" . $rra["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $rra["name"]) : $rra["name"]) . "</a>", $rra["id"]);
			form_selectable_cell($rra["x_files_factor"], $rra["id"]);
			form_selectable_cell($rra["steps"], $rra["id"]);
			form_selectable_cell($rra["rows"], $rra["id"]);
			form_selectable_cell($rra["timespan"], $rra["id"]);
			form_checkbox_cell($rra["name"], $rra["id"]);
			form_end_row();
		}

		form_end_table();

		print $nav;
	}else{
		print "<tr><td><em>" . __("No RRAs") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($rra_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
