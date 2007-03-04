<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/poller/poller_update.php");
require_once(CACTI_BASE_PATH . "/lib/poller/poller_info.php");
require_once(CACTI_BASE_PATH . "/include/poller/poller_form.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_post();

		break;
	case 'actions':
		form_actions();

		break;
	case 'delete':
		poller_delete();

		header("Location: pollers.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		poller_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		pollers();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_post() {
	global $registered_cacti_names;

	if (isset($_POST["save_component_data_poller"])) {
		$poller_id = api_poller_save($_POST["poller_id"], (isset($_POST["active"]) ? $_POST["active"] : ""), $_POST["hostname"], $_POST["name"]);

		if ((is_error_message()) || (empty($_POST["poller_id"]))) {
			header("Location: pollers.php?action=edit&poller_id=" . (empty($poller_id) ? $_POST["poller_id"] : $poller_id));
		}else{
			header("Location: pollers.php");
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
				api_data_poller_enable($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "2") { /* Disable Selected Pollers */
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_poller_disable($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "3") { /* Delete Selected Pollers */
			for ($i=0; $i<count($selected_items); $i++) {
				api_data_poller_delete($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "3") { /* Delete Selected Pollers */
			for ($i=0; $i<count($selected_items); $i++) {
				api_poller_statistics_clear($selected_items[$i]);
			}
		}

		header("Location: pollers.php");
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

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $poller_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='pollers.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* Enable Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To enable the following pollers, press the \"yes\" button below.") . "</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "2") { /* Disable Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To disable the following pollers, press the \"yes\" button below.") . "</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "3") { /* Delete Pollers */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To delete the following pollers, press the \"yes\" button below.") . "</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "4") { /* Clear Poller Statistics */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To clear data collection statistics for the following pollers, press the \"yes\" button below.") . "</p>
					<p>$poller_list</p>
				</td>
				</tr>";
	}

	if (!isset($poller_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one poller.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td colspan='2' align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($poller_array) ? serialize($poller_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='pollers.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
 }

/* -----------------------
    Data Input Functions
   ----------------------- */

function poller_edit() {
	global $colors, $fields_poller_edit;

	display_output_messages();
	if ((isset($_GET["poller_id"])) && ($_GET["poller_id"] >= 0)) {
		$poller = db_fetch_row("SELECT * FROM poller WHERE id=" . $_GET["poller_id"]);
		$header_label = _("[edit: ") . $poller["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	html_start_box("<strong>" . _("Pollers") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_poller_edit, (isset($poller) ? $poller : array()))
		));

	html_end_box();

	form_save_button("pollers.php");
}

function pollers() {
	$current_page = get_get_var_number("page", "1");

	global $colors, $poller_actions, $input_types;

	$menu_items = array(
		"enable" => _("Enable"),
		"disable" => _("Disable"),
		"delete" => _("Delete")
		);

	$filter_array = array();

	/* search field: filter (searches device description and hostname) */
	if (isset_get_var("search_filter")) {
		$filter_array["filter"] = array("name" => get_get_var("search_filter"), "hostname" => get_get_var("search_filter"));
	}

	/* clean up sort_column string */
	if (isset_get_var("sort_column")) {
		$filter_array["sort_column"] = get_get_var("sort_column");
	}else{
		$filter_array["sort_column"] = "name";
	}

	/* clean up sort_direction string */
	if (isset_get_var("sort_direction")) {
		$filter_array["sort_direction"] = get_get_var("sort_direction");
	}else{
		$filter_array["sort_direction"] = "ASC";
	}

	/* get a list of all devices on this page */
	$pollers = api_poller_list($filter_array, $current_page, read_config_option("num_rows_device"));

	/* get the total number of devices on all pages */
	$total_rows = api_poller_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_filter"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "pollers.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	form_start("pollers.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Pollers") . "</strong>", "pollers.php?action=edit", $url_page_select);

	$display_text = array(
		"name"        => array(_("Name"),          "ASC"),
		"hostname"    => array(_("Hostname"),      "ASC"),
		"run_state"   => array(_("Status"),        "ASC"),
		"cur_time"    => array(_("Last Time"),     "DESC"),
		"min_time"    => array(_("Min Time"),      "DESC"),
		"max_time"    => array(_("Max Time"),      "DESC"),
		"avg_time"    => array(_("Avg Time"),      "DESC"),
		"active"      => array(_("Enabled"),       "ASC"),
		"last_update" => array(_("Last Run Date"), "ASC"));

	html_header_sort_checkbox($display_text, $filter_array["sort_column"], $filter_array["sort_direction"], $box_id);

	$i = 0;

	if (sizeof($pollers) > 0) {
	foreach ($pollers as $poller) {
		?>
		<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $poller["poller_id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $poller["poller_id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $poller["poller_id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $poller["poller_id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $poller["poller_id"];?>')">
			<td class="title">
				<a href="pollers.php?action=edit&poller_id=<?php print $poller["id"];?>"><?php print $poller["name"];?></a>
			</td>
			<td>
				<?php echo $poller["hostname"];?>
			</td>
			<td>
				<?php echo $poller["run_state"];?>
			</td>
			<td>
				<?php echo $poller["cur_time"];?>
			</td>
			<td>
				<?php echo $poller["min_time"];?>
			</td>
			<td>
				<?php echo $poller["max_time"];?>
			</td>
			<td>
				<?php echo $poller["avg_time"];?>
			</td>
			<td>
				<?php echo ($poller["active"] == "on" ? _("Yes") : _("No"));?>
			</td>
			<td>
				<?php echo $poller["last_update"];?>
			</td>
			<td class="checkbox" align="center">
				<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $poller["poller_id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $poller["poller_id"];?>' title="<?php echo $poller["name"];?>">
			</td>
		</tr>
		<?php
	}
	}else{
		?>
		<tr class="empty">
			<td colspan="6">
				<?php echo _("No Pollers Found.");?>
			</td>
		</tr>
		<?php
	}

	html_box_toolbar_draw($box_id, "0", "9", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "poller_list");
	form_end();
}
?>