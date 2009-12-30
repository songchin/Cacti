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

/* --------------------------
 The Save Function
 -------------------------- */

function xaxis_form_save() {

	if (isset($_POST["save_component_xaxis"])) {
		$save["id"]   = $_POST["id"];
		$save["hash"] = get_hash_xaxis($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);

		if (!is_error_message()) {
			$xaxis_id = sql_save($save, "graph_templates_xaxis");

			if ($xaxis_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: xaxis_presets.php?action=edit&id=" . (empty($xaxis_id) ? $_POST["id"] : $xaxis_id));
		}else{
			header("Location: xaxis_presets.php");
		}
		exit;
	}

	if ((isset($_POST["save_component_item"]))) {
		$save["id"]   = $_POST["id"];
		$save["hash"] = get_hash_xaxis($_POST["id"], "xaxis_item");
		$save["item_name"] = form_input_validate($_POST["item_name"], "item_name", "", true, 3);
		$save["xaxis_id"] = form_input_validate($_POST["xaxis_id"], "xaxis_id", "^[0-9]+$", false, 3);
		$save["timespan"] = form_input_validate($_POST["timespan"], "timespan", "^[0-9]+$", false, 3);
		$save["gtm"] = form_input_validate($_POST["gtm"], "gtm", "^[0-9]+$", false, 3);
		$save["gst"] = form_input_validate($_POST["gst"], "gst", "^[0-9]+$", false, 3);
		$save["mtm"] = form_input_validate($_POST["mtm"], "mtm", "^[0-9]+$", false, 3);
		$save["mst"] = form_input_validate($_POST["mst"], "mst", "^[0-9]+$", false, 3);
		$save["ltm"] = form_input_validate($_POST["ltm"], "ltm", "^[0-9]+$", false, 3);
		$save["lst"] = form_input_validate($_POST["lst"], "lst", "^[0-9]+$", false, 3);
		$save["lpr"] = form_input_validate($_POST["lpr"], "lpr", "^[0-9]+$", false, 3);
		$save["lfm"] = form_input_validate($_POST["lfm"], "lfm", "", true, 3);

		if (!is_error_message()) {
			$xaxis_item_id = sql_save($save, "graph_templates_xaxis_items");

			if ($xaxis_item_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: xaxis_presets.php?action=item_edit&xaxis_id=" . $_POST["xaxis_id"] . "&id=" . (empty($xaxis_item_id) ? $_POST["id"] : $xaxis_item_id));
		}else{
			header("Location: xaxis_presets.php?action=edit&id=" . (!empty($_POST["xaxis_id"]) ? $_POST["xaxis_id"] : 0));
		}
		exit;
	}
}

/* ------------------------
 The "actions" function
 ------------------------ */

function xaxis_form_actions() {
	global $colors, $xaxis_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
				foreach($selected_items as $xaxis_id) {
					/* ================= input validation ================= */
					input_validate_input_number($xaxis_id);
					/* ==================================================== */

					$graph_data = db_fetch_assoc("SELECT " .
									"local_graph_id, " .
									"graph_template_id, " .
									"graph_template_xaxis.id, " .
									"graph_template_xaxis.name " .
									"FROM graph_templates_xaxis " .
									"LEFT JOIN graph_templates_graph " .
									"ON (graph_templates_xaxis.id = graph_templates_graph.x_grid) " .
									"WHERE graph_template_xaxis.id=" . $xaxis_id .
									" LIMIT 1");
					if (sizeof($graph_data)) {
						$bad_ids[$xaxis_id] = $graph_data;
					}else{
						$xaxis_ids[] = $xaxis_id;
					}
				}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $key => $value) {
					$message .= (strlen($message) ? "<br>":"") . "<i>" .
					__("X-Axis Preset Id/Name ($s, $s) is in use by Graph/Template ($d, $d) and can not be removed", $key, $value["name"], $value["local_graph_id"], $value["graph_template_id"]) .
					"</i>\n";
				}

				$_SESSION['sess_message_xaxis_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('xaxis_ref_int');
			}

			if (isset($xaxis_ids)) {
				db_execute("delete from graph_templates_xaxis where " . array_to_sql_or($xaxis_ids, "id"));
				db_execute("delete from graph_templates_xaxis_items where " . array_to_sql_or($xaxis_ids, "xaxis_id"));
			}
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_xaxis($selected_items[$i], get_request_var_post("title_format"));
			}
		}

		header("Location: xaxis_presets.php");
		exit;
	}

	/* setup some variables */
	$xaxis_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$xaxis_list .= "<li>" . db_fetch_cell("select name from graph_templates_xaxis where id=" . $matches[1]) . "<br>";
			$xaxis_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $xaxis_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='xaxis_presets.php' method='post'>\n";

	if (isset($xaxis_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("Are you sure you want to delete the following X-Axis Presets?") . "</p>
						<p><ul>$xaxis_list</ul></p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("When you click save, the following X-Axis Presets will be duplicated. You can optionally change the title format for the new X-Axis Presets.") . "</p>
						<p><ul>$xaxis_list</ul></p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<xaxis_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . __("You must select at least one CDEF.") . "</span></td></tr>\n";
	}

	print "<div><input type='hidden' name='action' value='actions'></div>";
	print "<div><input type='hidden' name='selected_items' value='" . (isset($xaxis_array) ? serialize($xaxis_array) : '') . "'></div>";
	print "<div><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'></div>";

	if (!isset($xaxis_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($xaxis_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* ---------------------
 X-Axis Functions
 --------------------- */

function item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	db_execute("DELETE FROM graph_templates_xaxis_items WHERE id=" . $_GET["id"]);
}

function item_edit() {
	global $colors, $fields_xaxis_item_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("xaxis_id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$xaxis_items = db_fetch_row("select * from graph_templates_xaxis_items where id=" . $_GET["id"]);
		$header_label = __("[edit: " . $xaxis_items["item_name"] . "]");
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='xaxis_item_edit'>\n";
	html_start_box("<strong>" . __("X-Axis Items") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'template');

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => inject_form_variables($fields_xaxis_item_edit, (isset($xaxis_items) ? $xaxis_items : array()))
			)
		);

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();


	form_hidden_box("id", (isset($_GET["id"]) ? $_GET["id"] : "0"), "");
	form_hidden_box("xaxis_id", get_request_var("xaxis_id"), "0");
	form_hidden_box("save_component_item", "1", "");
	form_save_button_alt("path!xaxis_presets.php|action!edit|id!" . get_request_var("xaxis_id"));
}



function xaxis_edit() {
	global $colors, $fields_xaxis_edit, $rrd_xaxis_timespans;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$xaxis = db_fetch_row("select * from graph_templates_xaxis where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $xaxis["name"] . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='xaxis_edit'>\n";
	html_start_box("<strong>". __("X-Axis Presets") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, false, 'header_xaxis_edit','left wp100');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_xaxis_edit, (isset($xaxis) ? $xaxis : array()))
	));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	if (!empty($_GET["id"])) {
		$sql_query = "SELECT * FROM graph_templates_xaxis_items WHERE xaxis_id=" . $_GET["id"] . " ORDER BY timespan ASC";
		$xaxis_items = db_fetch_assoc($sql_query);

		html_start_box("<strong>" . __("X-Axis Items") . "</strong>", "100", $colors["header"], 0, "center", "xaxis_presets.php?action=item_edit&xaxis_id=" . $_GET["id"], false, "xaxis");
		$header_items = array(__("Item"), __("Item Name"), __("Timespan"),
		__("Global Grid Span"), __("Steps"),
		__("Major Grid Span"), __("Steps"),
		__("Label Grid Span"), __("Steps"),
		__("Relative Label Position"), __("Label Format"));
		print "<tr><td>";
		html_header($header_items, 12, true, 'xaxis_item','left wp100');

		if (sizeof($xaxis_items) > 0) {
			$i = 0;
			foreach ($xaxis_items as $xaxis_item) {
				form_alternate_row_color('line' . $xaxis_item["id"], true);
				form_selectable_cell("<a style='white-space:nowrap;' class='linkEditMain' href='" . htmlspecialchars("xaxis_presets.php?action=item_edit&id=" . $xaxis_item["id"] . "&xaxis_id=" . $_GET["id"]) . "'>Item#$i</a>", $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["item_name"]) ? $xaxis_item["item_name"] : ''), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["timespan"]) ? $xaxis_item["timespan"] : 0), $xaxis_item["id"]);
				form_selectable_cell((isset($rrd_xaxis_timespans[$xaxis_item["gtm"]]) ? $rrd_xaxis_timespans[$xaxis_item["gtm"]] : __("None")), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["gst"]) ? $xaxis_item["gst"] : 0), $xaxis_item["id"]);
				form_selectable_cell((isset($rrd_xaxis_timespans[$xaxis_item["mtm"]]) ? $rrd_xaxis_timespans[$xaxis_item["mtm"]] : __("None")), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["mst"]) ? $xaxis_item["mst"] : 0), $xaxis_item["id"]);
				form_selectable_cell((isset($rrd_xaxis_timespans[$xaxis_item["ltm"]]) ? $rrd_xaxis_timespans[$xaxis_item["ltm"]] : __("None")), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["lst"]) ? $xaxis_item["lst"] : 0), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["lpr"]) ? $xaxis_item["lpr"] : 0), $xaxis_item["id"]);
				form_selectable_cell((isset($xaxis_item["lfm"]) ? $xaxis_item["lfm"] : __("None")), $xaxis_item["id"]);
				?>
				<td align="right"><a
					href="<?php print htmlspecialchars("xaxis_presets.php?action=item_remove&id=" . $xaxis_item["id"] . "&xaxis_id=" . $xaxis["id"]);?>"><img
					class="buttonSmall" src="images/delete_icon.gif"
					alt="<?php print __("Delete");?>" align='middle'></a>
				</td>
				<?php
				$i++;
				form_end_row();
			}
			form_end_table();
		}else{
			print "<tr><td><em>" . __("No X-Axis Preset Items") . "</em></td></tr>";
		}
		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
	}

	form_hidden_box("id", (isset($_GET["id"]) ? $_GET["id"] : "0"), "");
	form_hidden_box("save_component_xaxis", "1", "");
	form_save_button_alt("path!xaxis_presets.php");
}



function xaxis() {
	global $colors, $xaxis_actions, $item_rows;

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
		kill_session_var("sess_xaxis_current_page");
		kill_session_var("sess_xaxis_filter");
		kill_session_var("sess_xaxis_rows");
		kill_session_var("sess_xaxis_sort_column");
		kill_session_var("sess_xaxis_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* let's see if someone changed an important setting */
	$changed  = FALSE;
	$changed += check_changed("filter",      "sess_xaxis_filter");
	$changed += check_changed("rows",        "sess_xaxis_rows");

	if ($changed) {
		$_REQUEST["page"] = "1";
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_xaxis_current_page", "1");
	load_current_session_value("filter", "sess_xaxis_filter", "");
	load_current_session_value("rows", "sess_xaxis_rows", "-1");
	load_current_session_value("sort_column", "sess_xaxis_sort_column", "name");
	load_current_session_value("sort_direction", "sess_xaxis_sort_direction", "ASC");

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

	html_start_box("<strong>" . __("X-Axis Presets") . "</strong>", "100", $colors["header"], "3", "center", "xaxis_presets.php?action=edit", true);
	?>
<tr class='rowAlternate2'>
	<td>
	<form action="xaxis_presets.php" name="form_xaxis" method="post">
	<table cellpadding="0" cellspacing="3">
		<tr>
			<td class="nw50">&nbsp;<?php print __("Search:");?>&nbsp;</td>
			<td class="w1"><input type="text" name="filter" size="40"
				value="<?php print $_REQUEST["filter"];?>"></td>
			<td class="nw50">&nbsp;<?php print __("Rows:");?>&nbsp;</td>
			<td class="w1"><select name="rows"
				onChange="applyFilterChange(document.form_xaxis)">
				<option value="-1"
				<?php if (get_request_var_request("rows") == "-1") {?> selected
				<?php }?>>Default</option>
				<?php
				if (sizeof($item_rows) > 0) {
					foreach ($item_rows as $key => $value) {
						print "<option value='" . $key . "'"; if (get_request_var_request("rows") == $key) { print " selected"; } print ">" . $value . "</option>\n";
					}
				}
				?>
			</select></td>
			<td class="nw120">&nbsp;<input type="submit"
				Value="<?php print __("Go");?>" name="go" align="middle"> <input
				type="submit" Value="<?php print __("Clear");?>" name="clear_x"
				align="middle"></td>
		</tr>
	</table>
	<div><input type='hidden' name='page' value='1'></div>
	</form>
	</td>
</tr>
				<?php
				html_end_box(false);

				/* form the 'where' clause for our main sql query */
				if (strlen(get_request_var_request("filter"))) {
					$sql_where = "where (name like '%%" . $_REQUEST["filter"] . "%%')";
				}else{
					$sql_where = "";
				}

				html_start_box("", "100", $colors["header"], "0", "center", "");

				$total_rows = db_fetch_cell("select
		COUNT(id)
		from graph_templates_xaxis
		$sql_where");

		if (get_request_var_request("rows") == "-1") {
			$rows = read_config_option("num_rows_device");
		}else{
			$rows = get_request_var_request("rows");
		}

		$sql_query = "SELECT * " .
		"FROM graph_templates_xaxis " .
		$sql_where .
		" ORDER BY " . get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction") .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows;

		//print $sql_query;

		$xaxis_array = db_fetch_assoc($sql_query);

		/* generate page list navigation */
		$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 2, "xaxis_presets.php");

		print $nav;
		html_end_box(false);

		$display_text = array(
		"name" => array(__("Name"), "ASC"),
		);

		html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

		if (sizeof($xaxis_array) > 0) {
			foreach ($xaxis_array as $xaxis) {
				form_alternate_row_color('line' . $xaxis["id"], true);
				form_selectable_cell("<a style='white-space:nowrap;' class='linkEditMain' href='" . htmlspecialchars("xaxis_presets.php?action=edit&id=" . $xaxis["id"]) . "'>" .
				(strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $xaxis["name"]) : $xaxis["name"]) . "</a>", $xaxis["id"]);
				form_checkbox_cell($xaxis["name"], $xaxis["id"]);
				form_end_row();
			}

			form_end_table();

			/* put the nav bar on the bottom as well */
			print $nav;
		}else{
			print "<tr><td><em>" . __("No X-Axis Presets") . "</em></td></tr>";
		}

		print "</table>\n";

		/* draw the dropdown containing a list of available actions for this form */
		draw_actions_dropdown($xaxis_actions);

		print "</form>\n";
}
