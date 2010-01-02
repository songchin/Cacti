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
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/cdef.php");

define("MAX_DISPLAY_PAGES", 21);

$cdef_actions = array(
	1 => __("Delete"),
	2 => __("Duplicate")
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
	case 'item_remove':
		item_remove();

		header("Location: cdef.php?action=edit&id=" . $_GET["cdef_id"]);
		break;
	case 'item_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		item_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'remove':
		cdef_remove();

		header ("Location: cdef.php");
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		cdef_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		cdef();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    Global Form Functions
   -------------------------- */

function draw_cdef_preview($cdef_id) {
	global $colors; ?>
	<tr>
		<td>
			<pre>cdef=<?php print get_cdef($cdef_id, true);?></pre>
		</td>
	</tr>
<?php }


/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_cdef"])) {
		$save["id"]   = $_POST["id"];
		$save["hash"] = get_hash_cdef($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);

		if (!is_error_message()) {
			$cdef_id = sql_save($save, "cdef");

			if ($cdef_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: cdef.php?action=edit&id=" . (empty($cdef_id) ? $_POST["id"] : $cdef_id));
		}else{
			header("Location: cdef.php");
		}
		exit;
	}elseif (isset($_POST["save_component_item"])) {
		$sequence = get_sequence($_POST["id"], "sequence", "cdef_items", "cdef_id=" . $_POST["cdef_id"]);

		$save["id"]       = $_POST["id"];
		$save["hash"]     = get_hash_cdef($_POST["id"], "cdef_item");
		$save["cdef_id"]  = $_POST["cdef_id"];
		$save["sequence"] = $sequence;
		$save["type"]     = $_POST["type"];
		$save["value"]    = $_POST["value"];

		if (!is_error_message()) {
			$cdef_item_id = sql_save($save, "cdef_items");

			if ($cdef_item_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: cdef.php?action=item_edit&cdef_id=" . $_POST["cdef_id"] . "&id=" . (empty($cdef_item_id) ? $_POST["id"] : $cdef_item_id));
		}else{
			header("Location: cdef.php?action=edit&id=" . $_POST["cdef_id"]);
		}
		exit;
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $cdef_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $cdef_id) {
				/* ================= input validation ================= */
				input_validate_input_number($cdef_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM graph_templates_item WHERE cdef_id=$cdef_id LIMIT 1"))) {
					$bad_ids[] = $cdef_id;
				}else{
					$cdef_ids[] = $cdef_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $cdef_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>CDEF " . $cdef_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_cdef_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('cdef_ref_int');
			}

			if (isset($cdef_ids)) {
				db_execute("delete from cdef where " . array_to_sql_or($cdef_ids, "id"));
				db_execute("delete from cdef_items where " . array_to_sql_or($cdef_ids, "cdef_id"));
			}
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_cdef($selected_items[$i], get_request_var_post("title_format"));
			}
		}

		header("Location: cdef.php");
		exit;
	}

	/* setup some variables */
	$cdef_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$cdef_list .= "<li>" . db_fetch_cell("select name from cdef where id=" . $matches[1]) . "<br>";
			$cdef_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $cdef_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='cdef.php' method='post'>\n";

	if (isset($cdef_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("Are you sure you want to delete the following CDEFs?") . "</p>
						<p><ul>$cdef_list</ul></p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . __("When you click save, the following CDEFs will be duplicated. You can optionally change the title format for the new CDEFs.") . "</p>
						<p><ul>$cdef_list</ul></p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<cdef_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . __("You must select at least one CDEF.") . "</span></td></tr>\n";
	}

	print "<div><input type='hidden' name='action' value='actions'></div>";
	print "<div><input type='hidden' name='selected_items' value='" . (isset($cdef_array) ? serialize($cdef_array) : '') . "'></div>";
	print "<div><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'></div>";

	if (!isset($cdef_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($cdef_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* --------------------------
    CDEF Item Functions
   -------------------------- */

function item_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("cdef_id"));
	/* ==================================================== */

	db_execute("delete from cdef_items where id=" . $_GET["id"]);
}

function item_edit() {
	global $colors, $cdef_item_types, $cdef_functions, $cdef_operators, $custom_data_source_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("cdef_id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$cdef = db_fetch_row("select * from cdef_items where id=" . $_GET["id"]);
		$current_type = $cdef["type"];
		$values[$current_type] = $cdef["value"];
	}

	html_start_box("", "100", "aaaaaa", "3", "center", "");
	draw_cdef_preview(get_request_var("cdef_id"));
	html_end_box();

	print "<form action='cdef.php' name='form_cdef' method='post'>\n";
	html_start_box("<strong>" . __("CDEF Items") . "</strong> [edit: " . db_fetch_cell("select name from cdef where id=" . $_GET["cdef_id"]) . "]", "100", $colors["header"], "3", "center", "");

	if (isset($_GET["type_select"])) {
		$current_type = $_GET["type_select"];
	}elseif (isset($cdef["type"])) {
		$current_type = $cdef["type"];
	}else{
		$current_type = "1";
	}

	form_alternate_row_color("cdef_item_type"); ?>
		<td width="50%">
			<font class="textEditTitle"><?php print __("CDEF Item Type");?></font><br>
			<?php print __("Choose what type of CDEF item this is.");?>
		</td>
		<td>
			<select name="type_select" onChange="window.location=document.form_cdef.type_select.options[document.form_cdef.type_select.selectedIndex].value">
				<?php
				while (list($var, $val) = each($cdef_item_types)) {
					print "<option value='" . htmlspecialchars("cdef.php?action=item_edit" . (isset($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&cdef_id=" . $_GET["cdef_id"] . "&type_select=$var") . "'"; if ($var == $current_type) { print " selected"; } print ">$val</option>\n";
				}
				?>
			</select>
		</td>
	<?php
	form_end_row();
	form_alternate_row_color("cdem_item_value");
	?>
		<td width="50%">
			<font class="textEditTitle"><?php print __("CDEF Item Value");?></font><br>
			<?php print __("Enter a value for this CDEF item.");?>
		</td>
		<td>
			<?php
			switch ($current_type) {
			case '1':
				form_dropdown("value", $cdef_functions, "", "", (isset($cdef["value"]) ? $cdef["value"] : ""), "", "");
				break;
			case '2':
				form_dropdown("value", $cdef_operators, "", "", (isset($cdef["value"]) ? $cdef["value"] : ""), "", "");
				break;
			case '4':
				form_dropdown("value", $custom_data_source_types, "", "", (isset($cdef["value"]) ? $cdef["value"] : ""), "", "");
				break;
			case '5':
				form_dropdown("value", db_fetch_assoc("select name,id from cdef order by name"), "name", "id", (isset($cdef["value"]) ? $cdef["value"] : ""), "", "");
				break;
			case '6':
				form_text_box("value", (isset($cdef["value"]) ? $cdef["value"] : ""), "", "255", 60, "text", (isset($_GET["id"]) ? $_GET["id"] : "0"));
				break;
			}
			?>
		</td>
	<?php
	form_end_row();

	html_end_box();

	form_hidden_box("id", (isset($_GET["id"]) ? $_GET["id"] : "0"), "");
	form_hidden_box("type", $current_type, "");
	form_hidden_box("cdef_id", get_request_var("cdef_id"), "");
	form_hidden_box("save_component_item", "1", "");

	form_save_button_alt("path!cdef.php|action!edit|id!" . get_request_var("cdef_id"));
}

/* ---------------------
    CDEF Functions
   --------------------- */

function cdef_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if ((read_config_option("deletion_verification") == CHECKED) && (!isset($_GET["confirm"]))) {
		include(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(__("Are You Sure?"), __("Are you sure you want to delete the CDEF") . " <strong>'" . db_fetch_cell("select name from cdef where id=" . $_GET["id"]) . "'</strong>?", "cdef.php", "cdef.php?action=remove&id=" . $_GET["id"]);
		include(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("deletion_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from cdef where id=" . $_GET["id"]);
		db_execute("delete from cdef_items where cdef_id=" . $_GET["id"]);
	}
}

function cdef_edit() {
	global $colors, $cdef_item_types, $fields_cdef_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$cdef = db_fetch_row("select * from cdef where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $cdef["name"] . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='cdef_edit'>\n";
	html_start_box("<strong>". __("CDEF's") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, false, 'header_cdef_edit','left wp100');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_cdef_edit, (isset($cdef) ? $cdef : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	if (!empty($_GET["id"])) {
		html_start_box("", "100", "aaaaaa", "3", "center", "");
		draw_cdef_preview(get_request_var("id"));
		html_end_box();

		html_start_box("<strong>" . __("CDEF Items") . "</strong>", "100", $colors["header"], 0, "center", "cdef.php?action=item_edit&cdef_id=" . $cdef["id"], false, "cdef");
		$header_items = array(__("Item"), __("Item Value"));
		print "<tr><td>";
		html_header($header_items, 2, true, 'cdef_item','left wp100');

		$cdef_items = db_fetch_assoc("select * from cdef_items where cdef_id=" . $_GET["id"] . " order by sequence");

		$i = 0;
		if (sizeof($cdef_items) > 0) {
			foreach ($cdef_items as $cdef_item) {
				form_alternate_row_color($cdef_item["id"], true);
					?>
					<td>
						<a class="linkEditMain" href="<?php print htmlspecialchars("cdef.php?action=item_edit&id=" . $cdef_item["id"] . "&cdef_id=" . $cdef["id"]);?>">Item #<?php print $i;?></a>
					</td>
					<td>
						<em><?php $cdef_item_type = $cdef_item["type"]; print $cdef_item_types[$cdef_item_type];?></em>: <strong><?php print get_cdef_item_name($cdef_item["id"]);?></strong>
					</td>
					<td align="right">
						<a href="<?php print htmlspecialchars("cdef.php?action=item_remove&id=" . $cdef_item["id"] . "&cdef_id=" . $cdef["id"]);?>"><img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='middle'></a>
					</td>
			<?php
			form_end_row();
			$i++;
			}
		}
		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
	}
	form_save_button_alt("path!cdef.php");
?>
<script type="text/javascript">
	$('#cdef_item').tableDnD({
		onDrop: function(table, row) {
			$('#AjaxResult').load("lib/ajax/jquery.tablednd/cdef.ajax.php?id=<?php isset($_GET["id"]) ? print $_GET["id"] : print 0;?>&"+$.tableDnD.serialize());
		}
	});
</script>
<?php

}

function cdef() {
	global $colors, $cdef_actions, $item_rows;

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
		kill_session_var("sess_cdef_current_page");
		kill_session_var("sess_cdef_rows");
		kill_session_var("sess_cdef_filter");
		kill_session_var("sess_cdef_sort_column");
		kill_session_var("sess_cdef_sort_direction");

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
	load_current_session_value("page", "sess_cdef_current_page", "1");
	load_current_session_value("rows", "sess_cdef_rows", "-1");
	load_current_session_value("filter", "sess_cdef_filter", "");
	load_current_session_value("sort_column", "sess_cdef_sort_column", "name");
	load_current_session_value("sort_direction", "sess_cdef_sort_direction", "ASC");

	html_start_box("<strong>" . __("CDEF's") . "</strong>", "100", $colors["header"], "3", "center", "cdef.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_cdef" action="cdef.php">
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
						<select name="rows" onChange="applyFilterChange(document.form_cdef)">
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
	$sql_where = "WHERE (cdef.name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(cdef.id)
		FROM cdef
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$cdef_list = db_fetch_assoc("SELECT
		cdef.id,cdef.name
		FROM cdef
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 11, "cdef.php?filter=" . $_REQUEST["filter"]);

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name" => array(__("CDEF Title"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($cdef_list) > 0) {
		foreach ($cdef_list as $cdef) {
			form_alternate_row_color('line' . $cdef["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("cdef.php?action=edit&id=" . $cdef["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $cdef["name"]) : $cdef["name"]) . "</a>", $cdef["id"]);
			form_checkbox_cell($cdef["name"], $cdef["id"]);
			form_end_row();
		}

		form_end_table();

		print $nav;
	}else{
		print "<tr><td><em>" . __("No CDEF's") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($cdef_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
