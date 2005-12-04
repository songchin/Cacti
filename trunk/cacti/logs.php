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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");

require(CACTI_BASE_PATH . "/include/log/log_form.php");
require(CACTI_BASE_PATH . "/lib/log/log_info.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_post();

	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		view_logs();
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}


/* -----------------------
    Utilities Functions
   ----------------------- */
function form_post() {

	if (isset($_POST["box-1-search_filter"])) {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
			if (isset($_POST["box-1-search_facility"])) {
				if ($_POST["box-1-search_facility"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_facility=" . urlencode($_POST["box-1-search_facility"]);
				}
			}
			if (isset($_POST["box-1-search_severity"])) {
				if ($_POST["box-1-search_severity"] != "-2") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_severity=" . urlencode($_POST["box-1-search_severity"]);
				}
			}
			if (isset($_POST["box-1-search_poller"])) {
				if ($_POST["box-1-search_poller"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_poller=" . urlencode($_POST["box-1-search_poller"]);
				}
			}
			if (isset($_POST["box-1-search_host"])) {
				if ($_POST["box-1-search_host"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_host=" . urlencode($_POST["box-1-search_host"]);
				}
			}
			if (isset($_POST["box-1-search_plugin"])) {
				if ($_POST["box-1-search_plugin"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_plugin=" . urlencode($_POST["box-1-search_plugin"]);
				}
			}
			if (isset($_POST["box-1-search_username"])) {
				if ($_POST["box-1-search_username"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_username=" . urlencode($_POST["box-1-search_username"]);
				}
			}
		}

		header("Location: logs.php$get_string");
		exit;

	}

}


function view_logs() {
	global $device_actions;

	$current_page = get_get_var_number("page", "1");

	/* setup action menu */
	$menu_items = array(
		"purge" => "Purge",
		"export" => "Export",
		"print" => "Print"
	);


	/* search field: filter (searchs device description and hostname) */
	$filter_array = array();
	if (isset_get_var("search_filter")) {
		$filter_array["message"] = get_get_var("search_filter");
	}
	if (isset_get_var("search_facility")) {
		$filter_array["facility"] = get_get_var("search_facility");
	}
	if (isset_get_var("search_severity")) {
		$filter_array["severity"] = get_get_var("search_severity");
	}
	if (isset_get_var("search_poller")) {
		$filter_array["poller_id"] = get_get_var("search_poller");
	}
	if (isset_get_var("search_host")) {
		$filter_array["host_id"] = get_get_var("search_host");
	}
	if (isset_get_var("search_plugin")) {
		$filter_array["plugin"] = get_get_var("search_plugin");
	}
	if (isset_get_var("search_username")) {
		$filter_array["username"] = get_get_var("search_username");
	}

	/* get log entires */
	$logs = api_log_list($filter_array,read_config_option("num_rows_log"),read_config_option("num_rows_log")*($current_page-1));
	$total_rows = api_log_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_filter","search_facility","search_severity","search_poller","search_host","search_plugin","search_user"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_log"), $total_rows, "logs.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	/* Output html */
	$action_box_id = 1;
	$view_box_id  = 2;
	form_start("logs.php");

	html_start_box("<strong>" . _("Log Management") . "</strong>", "", $url_page_select);

	print "<tr>\n";
	print "<td class='log-content-header-sub-div'>" . _("Date") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Facility") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Severity") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Poller") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Host") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Plugin") . "</td>\n";
	print "<td colspan= '2' class='log-content-header-sub-div'>" . _("User") . "</td>\n";
	print "</tr>\n<tr>\n";
	print "<td colspan=\"8\" class='log-content-header-sub'>" . _("Message") . "</td>\n";
	print "</tr>";



	$i = 0;
	if ((is_array($logs)) && (sizeof($logs) > 0)) {
		foreach ($logs as $log) {
			?>
			<tr class="<?php echo api_log_html_css_class(api_log_severity_get($log["severity"])); ?>" id="box-<?php echo $view_box_id;?>-row-<?php echo $log["id"];?>" onclick="action_area_show('<?php echo $view_box_id; ?>', <?php echo $log["id"]; ?>, 'view_record');">
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-logdate">
					<?php echo $log["logdate"]; ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-facility">
					<?php echo api_log_facility_get($log["facility"]); ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-severity">
					<?php echo api_log_severity_get($log["severity"]); ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-poller_name">
					<?php if ($log["poller_name"] == "") { echo "SYSTEM"; }else{ echo $log["poller_name"]; } ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-host">
					<?php if ($log["host"] == "") { echo "SYSTEM"; }else{ echo $log["host"]; } ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-plugin">
					<?php if ($log["plugin"] == "") { echo "N/A"; }else{ echo $log["plugin"]; } ?>
				</td>
				<td class="log-content-row" id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-username">
					<?php if ($log["username"] == "") { echo "SYSTEM"; }else{ echo $log["username"]; } ?>
				</td>
				<td width="1%" class="log-content-row">
					&nbsp;
				</td>
			</tr><tr class="<?php echo api_log_html_css_class(api_log_severity_get($log["severity"])); ?>" onclick="action_area_show('<?php echo $view_box_id; ?>', <?php echo $log["id"]; ?>, 'view_record');">
				<td colspan="8" class="log-content-row-div">
					<?php if (strlen($log["message"]) > read_config_option("log_max_message_length")) { echo substr($log["message"], 0, read_config_option("log_max_message_length") - 3) . "..."; }else{ echo $log["message"]; } ?>
					<div id="box-<?php echo $view_box_id; ?>-row-<?php echo $log["id"]; ?>-message" style="position: absolute; visibility: hidden;"><?php echo $log["message"]; ?></div>
				</td>
			</tr>
			<?php
		}

	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="8">
				No Log Entries Found.
			</td>
		</tr>
		<?php
	}

	html_box_toolbar_draw($action_box_id, "0", "7", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select, 0);
	html_end_box(false);

	html_box_actions_menu_draw($action_box_id, "0", $menu_items);
	html_box_actions_area_draw($action_box_id, "0", 250);
	html_box_actions_area_draw($view_box_id, "0", 500, 0);

	form_hidden_box("action_post", "log_list");
	form_end();

	/* fill in the list of available search dropdown */
	$search_facility = array();
	$search_facility["-1"] = "Any";
	$search_facility += api_log_facility_list();

	$search_severity = array();
	$search_severity["-2"] = "Any";
	$search_severity += api_log_severity_list();

	$search_poller = array();
	$search_poller["-1"] = "Any";
	$search_poller += api_log_poller_list();

	$search_host = array();
	$search_host["-1"] = "Any";
	$search_host += api_log_host_list();

	$search_plugin = array();
	$search_plugin["-1"] = "Any";
	$search_plugin["N/A"] = "N/A";
	$search_plugin += api_log_plugin_list();

	$search_username = array();
	$search_username["-1"] = "Any";
	$search_username += api_log_username_list();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'view_record') {

			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-logdate').innerHTML, 'Date:', true, false,false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-facility').innerHTML, 'Facility:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-severity').innerHTML, 'Severity:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-poller_name').innerHTML, 'Poller:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-host').innerHTML, 'Host:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-plugin').innerHTML, 'Plugin:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-username').innerHTML, 'User:', false, false, false));
			parent_div.appendChild(action_area_generate_text_field(document.getElementById('box-' + box_id + '-row-' + parent_form + '-message').innerHTML, 'Message:', false, true, true));

			action_area_update_header_caption(box_id, 'View Log Entry');
		
		}else if (type == 'purge') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to purge the log?  All logs will be cleared!'));

			action_area_update_header_caption(box_id, 'Purge Logs');
			action_area_update_submit_caption(box_id, 'Purge');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'export') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to export all the logs?'));

			action_area_update_header_caption(box_id, 'Export');
			action_area_update_submit_caption(box_id, 'Export');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'search') {
			_elm_fac_input = action_area_generate_select('box-' + box_id + '-search_facility');
			<?php echo get_js_dropdown_code('_elm_fac_input', $search_facility, (isset_get_var("search_facility") ? get_get_var("search_facility") : "-1"));?>

			_elm_sev_input = action_area_generate_select('box-' + box_id + '-search_severity');
			<?php echo get_js_dropdown_code('_elm_sev_input', $search_severity, (isset_get_var("search_severity") ? get_get_var("search_severity") : "-2"));?>

			_elm_pol_input = action_area_generate_select('box-' + box_id + '-search_poller');
			<?php echo get_js_dropdown_code('_elm_pol_input', $search_poller, (isset_get_var("search_poller") ? get_get_var("search_poller") : "-1"));?>

			_elm_host_input = action_area_generate_select('box-' + box_id + '-search_host');
			<?php echo get_js_dropdown_code('_elm_host_input', $search_host, (isset_get_var("search_host") ? get_get_var("search_host") : "-1"));?>

			_elm_plug_input = action_area_generate_select('box-' + box_id + '-search_plugin');
			<?php echo get_js_dropdown_code('_elm_plug_input', $search_plugin, (isset_get_var("search_plugin") ? get_get_var("search_plugin") : "-1"));?>

			_elm_user_input = action_area_generate_select('box-' + box_id + '-search_username');
			<?php echo get_js_dropdown_code('_elm_user_input', $search_username, (isset_get_var("search_username") ? get_get_var("search_username") : "-1"));?>

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_fac_input, 'Facility', true, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_sev_input, 'Severity', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_pol_input, 'Poller', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_host_input, 'Host', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_plug_input, 'Plugin', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_user_input, 'User', false, false));

			parent_div.appendChild(action_area_generate_search_field(_elm_ht_input, 'Filter', false, true));

			action_area_update_header_caption(box_id, 'Search');
			action_area_update_submit_caption(box_id, 'Search');
		}
	}
	-->
	</script>

	<?php

}

?>
