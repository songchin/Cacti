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
include_once(CACTI_BASE_PATH . "/lib/utility.php");

define("MAX_DISPLAY_PAGES", 21);

$poller_actions = array(
	1 => "Delete",
	2 => "Duplicate"
	);

/* file: pollers.php, action: edit */
$fields_poller_edit = array(
	"host_header" => array(
		"method" => "spacer",
		"friendly_name" => "General Poller Options"
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => "Description",
		"description" => "Give this poller a meaningful description.",
		"value" => "|arg1:description|",
		"max_length" => "250",
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => "Hostname",
		"description" => "Fully qualified hostname of the poller device.",
		"value" => "|arg1:hostname|",
		"max_length" => "250",
		),
	"ip_address" => array(
		"method" => "textbox",
		"friendly_name" => "IP Address",
		"description" => "The IP Address of this poller for status checking.",
		"value" => "|arg1:ip_address|",
		"max_length" => "250",
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => "Disabled",
		"description" => "Check this box if you wish for this poller to be disabled.",
		"value" => "|arg1:disabled|",
		"default" => "",
		"form_id" => false
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

switch ($_REQUEST["action"]) {
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
		$save["disabled"]    = form_input_validate($_POST["disabled"], "disabled", "", false, 3);
		$save["description"] = form_input_validate($_POST["description"], "description", "", false, 3);
		$save["hostname"]    = form_input_validate($_POST["hostname"], "hostname", "", true, 3);
		$save["ip_address"]  = form_input_validate($_POST["ip_address"], "ip_address", "", true, 3);

		if (!is_error_message()) {
			$poller_id = sql_save($save, "poller");

			if ($poller_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message() || empty($_POST["id"])) {
			header("Location: poller.php?action=edit&id=" . (empty($poller_id) ? $_POST["id"] : $poller_id));
		}else{
			header("Location: poller.php");
		}
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $host_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from poller where " . array_to_sql_or($selected_items, "id"));
			db_execute("update poller_item set poller_id=0 where " . array_to_sql_or($selected_items, "poller_id"));
			db_execute("update host set poller_id=0 where " . array_to_sql_or($selected_items, "poller_id"));
		}elseif ($_POST["drp_action"] == "2") { /* disable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				db_execute("update poller set disabled='on' where " . array_to_sql_or($selected_items, "id"));
			}
		}

		header("Location: poller.php");
		exit;
	}

	/* setup some variables */
	$poller_list = ""; $i = 0; $poller_array = array();

	/* loop through each of the pollers selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$poller_list .= "<li>" . db_fetch_cell("select description from poller where id=" . $matches[1]) . "<br>";
			$poller_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $poller_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='host_templates.php' method='post'>\n";

	if (sizeof($poller_array)) {
		if ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>Are you sure you want to delete the following pollers? All devices currently attached
						this these pollers will be reassigned to the default poller.</p>
						<p>$poller_list</p>
					</td>
				</tr>\n
				";
		}elseif ($_POST["drp_action"] == "2") { /* disable */
			print "	<tr>
					<td class='textArea'>
						<p>Are you sure you want to disable the following pollers?  Add devices currently attached
						to these pollers will no longer have their graphs updated.</p>
						<p>$poller_list</p>
					</td>
				</tr>\n
				";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>You must first select a Poller.  Please select 'Return' to return to the previous menu.</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($poller_array)) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($poller_array), $_POST["drp_action"]);
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
		<table width='100%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center'>
			<tr bgcolor="<?php print $colors["light"];?>">
				<td style="padding: 3px; font-family: monospace;">
					<?php print $debug_log;?>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	if (!empty($_GET["id"])) {
		$poller = db_fetch_row("select * from poller where id=" . $_GET["id"]);
		$header_label = "[edit: " . $poller["description"] . "]";
	}else{
		$header_label = "[new]";
		$_GET["id"] = 0;
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='poller_edit'>\n";
	html_start_box("<strong>Pollers</strong> $header_label", "100%", $colors["header"], 0, "center", "", true);
	$header_items = array("Field", "Value");
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
	global $colors, $poller_actions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
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
		kill_session_var("sess_poller_filter");
		kill_session_var("sess_poller_sort_column");
		kill_session_var("sess_poller_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_poller_current_page", "1");
	load_current_session_value("filter", "sess_poller_filter", "");
	load_current_session_value("sort_column", "sess_poller_sort_column", "description");
	load_current_session_value("sort_direction", "sess_poller_sort_direction", "ASC");

	display_output_messages();

	html_start_box("<strong>Pollers</strong>", "100%", $colors["header"], "3", "center", "pollers.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_pollers" action="pollers.php">
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
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (poller.description LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100%", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM poller
		$sql_where");

	$poller_list = db_fetch_assoc("SELECT *
		FROM poller
		$sql_where
		ORDER BY " . $_REQUEST['sort_column'] . " " . $_REQUEST['sort_direction'] .
		" LIMIT " . (read_config_option("num_rows_device")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_device"));

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 7, "pollers.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"description" => array("Description", "ASC"),
		"id" => array("ID", "ASC"),
		"hostname" => array("Hostname", "ASC"),
		"nosort1" => array("Status", ""),
		"last_update" => array("Last Updated", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	$status = "Howdie";

	if (sizeof($poller_list) > 0) {
		foreach ($poller_list as $poller) {
			form_alternate_row_color('line' . $poller["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("pollers.php?action=edit&id=" . $poller["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $poller["description"]) : $poller["description"]) . "</a>", $poller["id"]);
			form_selectable_cell($poller["id"], $poller["id"]);
			form_selectable_cell($poller["hostname"], $poller["id"]);
			form_selectable_cell($status, $poller["id"]);
			form_selectable_cell($poller["last_update"], $poller["id"]);
			form_checkbox_cell($poller["description"], $poller["id"]);
			form_end_row();
		}

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>No Pollers Defined</em></td></tr>\n";
	}

	print "</table>\n</form>\n";	# end form and table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($poller_actions);
}
?>
