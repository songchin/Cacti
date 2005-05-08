<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

include("./include/config.php");
include("./include/auth.php");
include_once('./lib/api_data_pollers.php');

$poller_actions = array(
	1 => "Enable",
	2 => "Disable",
	3 => "Delete"
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
	case 'delete':
		poller_delete();

		header("Location: data_pollers.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		poller_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		pollers();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $registered_cacti_names;

	if (isset($_POST["save_component_data_poller"])) {
		$data_poller_id = api_data_poller_save($_POST["id"], (isset($_POST["active"]) ? $_POST["active"] : ""), $_POST["hostname"], $_POST["name"]);

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: data_pollers.php?action=edit&id=" . (empty($data_poller_id) ? $_POST["id"] : $data_poller_id));
		}else{
			header("Location: data_pollers.php");
		}
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

		if ($_POST["drp_action"] == "1") { /* Enable Selected Pollers */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("UPDATE poller SET active='on' WHERE id='" . $selected_items[$i] . "'");

				/* update poller cache */
				/* todo this yet */
			}
		}elseif ($_POST["drp_action"] == "2") { /* Disable Selected Pollers */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("UPDATE poller SET active='' status='Disabled' WHERE id='" . $selected_items[$i] . "'");

				/* update poller cache */
				/* todo this yet */
			}
		}elseif ($_POST["drp_action"] == "3") { /* Delete Selected Pollers */
			for ($i=0; $i<count($selected_items); $i++) {
				poller_delete($selected_items[$i]);
			}
		}

		header("Location: data_pollers.php");
		exit;
	}
	/* setup some variables */
	$poller_list = ""; $i = 0;

	/* loop through each of the host templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$poller_list .= "<li>" . db_fetch_cell("select name from poller where id=" . $matches[1]) . "<br>";
			$poller_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $poller_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='data_pollers.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* Enable Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>To enable the following pollers, press the \"yes\" button below.</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "2") { /* Disable Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>To disable the following pollers, press the \"yes\" button below.</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "3") { /* Delete Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>To delete the following pollers, press the \"yes\" button below.</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}

	if (!isset($poller_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one poller.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>";
	}

	print "	<tr>
			<td colspan='2' align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($poller_array) ? serialize($poller_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='data_pollers.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
 }

/* -----------------------
    Data Input Functions
   ----------------------- */

function poller_delete() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm("Are You Sure?", "Are you sure you want to delete the poller <strong>'" . db_fetch_cell("select hostname from poller where id=" . $_GET["id"]) . "'</strong>?", "data_pollers.php", "data_pollers.php?action=delete&id=" . $_GET["id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	$hosts_polled = db_fetch_assoc("select poller_id from host where poller_id=" . $_GET["id"]);

	if (sizeof($hosts_polled) == 0) {
		if ($_GET["id"] == 1) {
			$error_message = "This poller is the main system poller.  It can not be deleted.";
			include("./include/top_header.php");
			form_message("Can Not Delete Poller", $error_message, "data_pollers.php");
			include("./include/bottom_footer.php");
		}else {
			if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
				api_data_poller_delete($_GET["id"]);
			}
		}
	} else {
		$error_message = "The poller selected is in use for " . sizeof($hosts_polled) . " hosts and can not be deleted.  You can not delete a poller when it has hosts associated with it.";
		include("./include/top_header.php");
		form_message("Can Not Delete Poller", $error_message, "data_pollers.php");
		include("./include/bottom_footer.php");
	}
}

function poller_edit() {
	global $colors, $fields_data_poller_edit;

	if ((isset($_GET["id"])) && ($_GET["id"] >= 0)) {
		$data_poller = db_fetch_row("select * from poller where id=" . $_GET["id"]);
		$header_label = "[edit: " . $data_poller["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>Data Pollers</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_data_poller_edit, (isset($data_poller) ? $data_poller : array()))
		));

	html_end_box();

	form_save_button("data_pollers.php");
}

function pollers() {
	global $colors, $poller_actions, $input_types;

	html_start_box("<strong>Data Pollers</strong>", "98%", $colors["header_background"], "3", "center", "data_pollers.php?action=edit");

	html_header_checkbox(array("Name", "Hostname", "Status", "Last Time", "Min Time", "Max Time", "Avg Time", "Enabled", "Last Run Time"));

	$data_pollers = db_fetch_assoc("select * from poller order by name");

	$i = 0;
	if (sizeof($data_pollers) > 0) {
	foreach ($data_pollers as $data_poller) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="data_pollers.php?action=edit&id=<?php print $data_poller["id"];?>"><?php print $data_poller["name"];?></a>
			</td>
			<td>
				<?php print $data_poller["hostname"];?>
			</td>
			<td>
				<?php print $data_poller["run_state"];?>
			</td>
			<td>
				<?php print $data_poller["cur_time"];?>
			</td>
			<td>
				<?php print $data_poller["min_time"];?>
			</td>
			<td>
				<?php print $data_poller["max_time"];?>
			</td>
			<td>
				<?php print $data_poller["avg_time"];?>
			</td>
			<td>
				<?php print ($data_poller["active"] == "on" ? "Yes" : "No");?>
			</td>
			<td>
				<?php print $data_poller["last_update"];?>
			</td>
			<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
				<input type='checkbox' style='margin: 0px;' name='chk_<?php print $data_poller["id"];?>' title="<?php print $data_poller["name"];?>">
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>No Data Pollers</em></td></tr>";
	}
	html_end_box(false);

   	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($poller_actions);
}
?>