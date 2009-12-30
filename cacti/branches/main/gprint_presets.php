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
	1 => __("Delete")
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

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $gprint_id) {
				/* ================= input validation ================= */
				input_validate_input_number($gprint_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT gprint_id FROM graph_templates_item WHERE gprint_id=$gprint_id LIMIT 1 UNION (SELECT right_axis_format AS gprint_id FROM graph_templates_graph WHERE right_axis_format=$gprint_id LIMIT 1)"))) {
					$bad_ids[] = $gprint_id;
				}else{
					$gprint_ids[] = $gprint_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $gprint_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>GPrint " . $gprint_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_gprint_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('gprint_ref_int');
			}

			if (isset($gprint_ids)) {
				db_execute("delete from graph_templates_gprint where " . array_to_sql_or($selected_items, "id"));
			}
		}
		header("Location: gprint_presets.php");
		exit;
	}

	/* setup some variables */
	$gprint_list = ""; $i = 0;

	/* loop through each of the items selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$gprint_list .= "<li>" . db_fetch_cell("select name from graph_templates_gprint where id=" . $matches[1]) . "<br>";
			$gprint_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $gprint_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='gprint_presets.php' method='post'>\n";

	if (isset($gprint_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("Are you sure you want to delete the following GPRINT presets?") . "</p>
						<p><ul>$gprint_list</ul></p>
					</td>
				</tr>\n
				";
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . __("You must select at least one GPRINT preset.") . "</span></td></tr>\n";
	}

	print "<div><input type='hidden' name='action' value='actions'></div>";
	print "<div><input type='hidden' name='selected_items' value='" . (isset($gprint_array) ? serialize($gprint_array) : '') . "'></div>";
	print "<div><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'></div>";

	if (!isset($gprint_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($gprint_array), get_request_var_post("drp_action"));
	}

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
		}else{
			header("Location: gprint_presets.php");
		}
		exit;
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
		$header_label = __("[edit: ") . $gprint_preset["name"] . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='gprint_edit'>\n";
	html_start_box("<strong>" . __("GPRINT Presets") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_gprint_preset');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_grprint_presets_edit, (isset($gprint_preset) ? $gprint_preset : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	form_save_button_alt();
}

function gprint_presets() {
	global $colors, $gprint_actions, $item_rows;

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
		kill_session_var("sess_gprint_current_page");
		kill_session_var("sess_gprint_rows");
		kill_session_var("sess_gprint_filter");
		kill_session_var("sess_gprint_sort_column");
		kill_session_var("sess_gprint_sort_direction");

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
	load_current_session_value("page", "sess_gprint_current_page", "1");
	load_current_session_value("rows", "sess_gprint_rows", "-1");
	load_current_session_value("filter", "sess_gprint_filter", "");
	load_current_session_value("sort_column", "sess_gprint_sort_column", "name");
	load_current_session_value("sort_direction", "sess_gprint_sort_direction", "ASC");

	html_start_box("<strong>" . __("GPRINT Presets") . "</strong>", "100", $colors["header"], "3", "center", "gprint_presets.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_gprint" action="gprint_presets.php">
			<table cellpadding="0" cellspacing="3">
				<tr>
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
						<select name="rows" onChange="applyFilterChange(document.form_gprint)">
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
	$sql_where = "WHERE (name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(id)
		FROM graph_templates_gprint
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$template_list = db_fetch_assoc("SELECT
		id,
		name
		FROM graph_templates_gprint
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction'));

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 11, "gprint_presets.php?filter=" . $_REQUEST["filter"]);

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name" => array(__("Name"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color('line' . $template["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("gprint_presets.php?action=edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template["name"]) : $template["name"]) . "</a>", $template["id"]);
			form_checkbox_cell($template["name"], $template["id"]);
			form_end_row();
		}

		form_end_table();

		print $nav;
	}else{
		print "<tr><td><em>" . __("No Items") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($gprint_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
