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

define("MAX_DISPLAY_PAGES", 21);

$poller_actions = array(
	1 => __("Delete"),
	2 => __("Duplicate")
	);

/* file: pollers.php, action: edit */
$fields_poller_edit = array(
	"device_header" => array(
		"method" => "spacer",
		"friendly_name" => __("General Poller Options")
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => __("Description"),
		"description" => __("Give this poller a meaningful description."),
		"value" => "|arg1:description|",
		"max_length" => "250",
		),
	"devicename" => array(
		"method" => "textbox",
		"friendly_name" => __("Hostname"),
		"description" => __("Fully qualified devicename of the poller device."),
		"value" => "|arg1:devicename|",
		"max_length" => "250",
		),
	"ip_address" => array(
		"method" => "textbox",
		"friendly_name" => __("IP Address"),
		"description" => __("The IP Address of this poller for status checking."),
		"value" => "|arg1:ip_address|",
		"max_length" => "250",
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => __("Disabled"),
		"description" => __("Check this box if you wish for this poller to be disabled."),
		"value" => "|arg1:disabled|",
		"default" => ""
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_poller" => array(
		"method" => "hidden",
		"value" => "1"
		)
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
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		poller_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		poller();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	/* save the poller */
	if (isset($_POST["save_component_poller"])) {
		$save["id"]          = $_POST["id"];

		$save["disabled"]    = form_input_validate((isset($_POST["disabled"]) ? get_request_var_post("disabled"):""), "disabled", "", true, 3);
		$save["description"] = form_input_validate(get_request_var_post("description"), "description", "", false, 3);
		$save["devicename"]    = form_input_validate(get_request_var_post("devicename"), "devicename", "", true, 3);
		$save["ip_address"]  = form_input_validate(get_request_var_post("ip_address"), "ip_address", "", true, 3);

		if (!is_error_message()) {
			$poller_id = sql_save($save, "poller");

			if ($poller_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: pollers.php?action=edit&id=" . (empty($poller_id) ? $_POST["id"] : $poller_id));
		}else{
			header("Location: pollers.php");
		}
		exit;
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $poller_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $poller_id) {
				/* ================= input validation ================= */
				input_validate_input_number($poller_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM device WHERE poller_id=$poller_id LIMIT 1")) || $poller_id == 1) {
					$bad_ids[] = $poller_id;
				}else{
					$poller_ids[] = $poller_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $poller_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>" . sprintf(__("Poller '%s' is in use or is the system poller and can not be removed"), $poller_id) . "</i>\n";
				}

				$_SESSION['sess_message_poller_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('poller_ref_int');
			}

			if (isset($poller_ids)) {
				db_execute("delete from poller where " . array_to_sql_or($poller_ids, "id"));
				db_execute("update poller_item set poller_id=0 where " . array_to_sql_or($poller_ids, "poller_id"));
				db_execute("update device set poller_id=0 where " . array_to_sql_or($poller_ids, "poller_id"));
			}
		}elseif (get_request_var_post("drp_action") == "2") { /* disable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				db_execute("update poller set disabled='on' where " . array_to_sql_or($selected_items, "id"));
			}
		}

		header("Location: pollers.php");
		exit;
	}

	/* setup some variables */
	$poller_list = ""; $i = 0; $poller_array = array();

	/* loop through each of the pollers selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$poller_list .= "<li>" . db_fetch_cell("select description from poller where id=" . $matches[1]) . "<br>";
			$poller_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $poller_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='pollers.php' method='post'>\n";

	if (sizeof($poller_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>". __("Are you sure you want to delete the following pollers? All devices currently attached this these pollers will be reassigned to the default poller.") . "</p>
						<p><ul>$poller_list</ul></p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == "2") { /* disable */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to disable the following pollers? All devices currently attached to these pollers will no longer have their graphs updated.") . "</p>
						<p><ul>$poller_list</ul></p>
					</td>
				</tr>\n
				";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Poller.  Please select 'Return' to return to the previous menu.") . "</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($poller_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($poller_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ---------------------
    Template Functions
   --------------------- */

function poller_edit() {
	global $colors, $fields_poller_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	/* remember if there's something we want to show to the user */
	$debug_log = debug_log_return("poller");

	if (!empty($debug_log)) {
		debug_log_clear("poller");
		?>
		<table class='topBoxAlt'>
			<tr bgcolor="<?php print $colors["light"];?>">
				<td class='mono'>
					<?php print $debug_log;?>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	if (!empty($_GET["id"])) {
		$poller = db_fetch_row("select * from poller where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $poller["description"] . "]";
	}else{
		$header_label = __("[new]");
		$_GET["id"] = 0;
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='poller_edit'>\n";
	html_start_box("<strong>" . __("Pollers") . "</strong> $header_label", "100", $colors["header"], 0, "center", "", true);
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'poller_edit');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_poller_edit, (isset($poller) ? $poller : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	form_save_button_alt();
}

function poller() {
	global $colors, $poller_actions, $item_rows;

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

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_poller_current_page");
		kill_session_var("sess_poller_rows");
		kill_session_var("sess_poller_filter");
		kill_session_var("sess_poller_sort_column");
		kill_session_var("sess_poller_sort_direction");

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
	load_current_session_value("page", "sess_poller_current_page", "1");
	load_current_session_value("rows", "sess_poller_rows", "-1");
	load_current_session_value("filter", "sess_poller_filter", "");
	load_current_session_value("sort_column", "sess_poller_sort_column", "description");
	load_current_session_value("sort_direction", "sess_poller_sort_direction", "ASC");

	display_output_messages();

	html_start_box("<strong>" . __("Pollers") . "</strong>", "100", $colors["header"], "3", "center", "pollers.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_pollers" action="pollers.php">
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
						<select name="rows" onChange="applyFilterChange(document.form_pollers)">
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
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if ($_REQUEST["filter"] != "") {
		$sql_where = "WHERE (p.description LIKE '%%" . $_REQUEST["filter"] . "%%')";
	}else{
		$sql_where = "";
	}

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM poller
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$poller_list = db_fetch_assoc("SELECT p.*,
		sum(CASE WHEN h.poller_id IS NOT NULL THEN 1 ELSE NULL END) AS total_devices
		FROM poller AS p
		LEFT JOIN device AS h ON h.poller_id=p.id
		$sql_where
		GROUP BY p.id
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "pollers.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"description" => array(__("Description"), "ASC"),
		"id" => array(__("ID"), "ASC"),
		"total_devices" => array(__("Devices"), "DESC"),
		"nosort2" => array(__("Poller Items"), "DESC"),
		"devicename" => array(__("Hostname"), "ASC"),
		"nosort1" => array(__("Status"), ""),
		"last_update" => array(__("Last Updated"), "ASC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($poller_list) > 0) {
		foreach ($poller_list as $poller) {
			form_alternate_row_color('line' . $poller["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("pollers.php?action=edit&id=" . $poller["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $poller["description"]) : $poller["description"]) . "</a>", $poller["id"]);
			form_selectable_cell($poller["id"], $poller["id"]);
			form_selectable_cell($poller["total_devices"], $poller["id"]);
			form_selectable_cell(db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . $poller["id"]), $poller["id"]);
			form_selectable_cell($poller["devicename"], $poller["id"]);
			form_selectable_cell(get_colored_poller_status(($poller["disabled"] == CHECKED ? true : false), $poller["last_update"]), $poller["id"]);
			form_selectable_cell($poller["last_update"], $poller["id"]);
			form_checkbox_cell($poller["description"], $poller["id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>" . __("No Pollers Defined") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($poller_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
