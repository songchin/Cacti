<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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
	case 'print';
		print_logs();
		break;

	case 'export';
		export_logs();
		break;

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


	$get_string = "";

	/* Process clear request and get out */
	if (isset($_POST["box-1-action-clear-button"])) {
		header("logs.php");
	}

	if (($_POST["action_post"] == "box-1") && (isset($_POST["box-1-action-area-type"]))) {
		if (($_POST["box-1-action-area-type"] == "search") || ($_POST["box-1-action-area-type"] == "export")) {
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
			if (isset($_POST["box-1-search_source"])) {
				if ($_POST["box-1-search_source"] != "") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_source=" . urlencode($_POST["box-1-search_source"]);
				}
			}
			if (isset($_POST["box-1-search_start_date"])) {
				if ($_POST["box-1-search_start_date"] != "") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_start_date=" . urlencode($_POST["box-1-search_start_date"]);
				}
			}
			if (isset($_POST["box-1-search_end_date"])) {
				if ($_POST["box-1-search_end_date"] != "") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_end_date=" . urlencode($_POST["box-1-search_end_date"]);
				}
			}
		}

	} elseif ((isset($_POST["box-1-search_filter"]))) {
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}
	}
	if (isset($_POST["box-1-action-area-type"])) {
		if ($_POST["box-1-action-area-type"] == "export") {
			$get_string .= ($get_string == "" ? "?" : "&") . "action=export";
		}elseif ($_POST["box-1-action-area-type"] == "purge") {
			log_clear();
			$get_string="";
		}
	}

	header("Location: logs.php" . $get_string);

	exit;

}


function view_logs() {

	$current_page = get_get_var_number("page", "1");

	/* setup action menu */
	$menu_items = array(
		"purge" => "Purge",
		"export" => "Export",
		"print" => "Print"
	);


	/* search field: filter (searchs device description and hostname) */
	$filter_array = array();
	$filter_url = "";
	if (isset_get_var("search_filter")) {
		$filter_array["message"] = get_get_var("search_filter");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_filter=" . urlencode(get_get_var("search_filter"));
	}
	if (isset_get_var("search_facility")) {
		$filter_array["facility"] = get_get_var("search_facility");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_facility=" . urlencode(get_get_var("search_facility"));
	}
	if (isset_get_var("search_severity")) {
		$filter_array["severity"] = get_get_var("search_severity");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_severity=" . urlencode(get_get_var("search_severity"));
	}
	if (isset_get_var("search_poller")) {
		$filter_array["poller_id"] = get_get_var("search_poller");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_poller=" . urlencode(get_get_var("search_poller"));
	}
	if (isset_get_var("search_host")) {
		$filter_array["host_id"] = get_get_var("search_host");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_host=" . urlencode(get_get_var("search_host"));
	}
	if (isset_get_var("search_plugin")) {
		$filter_array["plugin"] = get_get_var("search_plugin");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_plugin=" . urlencode(get_get_var("search_plugin"));
	}
	if (isset_get_var("search_username")) {
		$filter_array["username"] = get_get_var("search_username");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_username=" . urlencode(get_get_var("search_username"));
	}
	if (isset_get_var("search_source")) {
		$filter_array["source"] = get_get_var("search_source");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_source=" . urlencode(get_get_var("search_source"));
	}
	if (isset_get_var("search_start_date")) {
		$filter_array["start_date"] = get_get_var("search_start_date");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_start_date=" . urlencode(get_get_var("search_start_date"));
	}
	if (isset_get_var("search_end_date")) {
		$filter_array["end_date"] = get_get_var("search_end_date");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_end_date=" . urlencode(get_get_var("search_end_date"));
	}

	/* get log entires */
	$logs = log_list($filter_array,read_config_option("num_rows_log"),read_config_option("num_rows_log")*($current_page-1));
	$total_rows = log_get_total($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_filter","search_facility","search_severity","search_poller","search_host","search_plugin","search_username","search_source","search_start_date","search_end_date"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_log"), $total_rows, "logs.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	/* Output html */
	$action_box_id = 1;
	form_start("logs.php");

	html_start_box("<strong>" . _("Log Management") . "</strong>", "", $url_page_select);

	print "<tr>\n";
	print "<td class='log-content-header-sub-div'>" . _("Date") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Facility") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Severity") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Poller") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Host") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("Plugin") . "</td>\n";
	print "<td class='log-content-header-sub-div'>" . _("User") . "</td>\n";
	print "<td colspan='2' class='log-content-header-sub-div'>" . _("Source") . "</td>\n";
	print "</tr>";

	$i = 0;
	if ((is_array($logs)) && (sizeof($logs) > 0)) {
		foreach ($logs as $log) {
			?>
			<tr class="<?php echo log_get_html_css_class(log_get_severity($log["severity"])); ?>">
				<td class="log-content-row">
					<?php echo $log["logdate"]; ?>
				</td>
				<td class="log-content-row">
					<?php echo log_get_facility($log["facility"]); ?>
				</td>
				<td class="log-content-row">
					<?php echo log_get_severity($log["severity"]); ?>
				</td>
				<td class="log-content-row">
					<?php if ($log["poller_name"] == "") { echo "SYSTEM"; }else{ echo $log["poller_name"]; } ?>
				</td>
				<td class="log-content-row">
					<?php if ($log["host"] == "") { echo "SYSTEM"; }else{ echo $log["host"]; } ?>
				</td>
				<td class="log-content-row">
					<?php if ($log["plugin"] == "") { echo "N/A"; }else{ echo $log["plugin"]; } ?>
				</td>
				<td class="log-content-row">
					<?php if ($log["username"] == "") { echo "SYSTEM"; }else{ echo $log["username"]; } ?>
				</td>
				<td class="log-content-row">
					<?php if ($log["source"] == "") { echo "SYSTEM"; }else{ echo $log["source"]; } ?>
				</td>
				<td width="1%" class="log-content-row">
					&nbsp;
				</td>
			</tr><tr class="<?php echo log_get_html_css_class(log_get_severity($log["severity"])); ?>">
				<td colspan="9" class="log-content-row-div">
					<?php echo $log["message"]; ?>
				</td>
			</tr>
			<?php
		}

	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="9">
				No Log Entries Found.
			</td>
		</tr>
		<?php
	}

	html_box_toolbar_draw($action_box_id, "0", "8", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select, 0);
	html_end_box(false);

	html_box_actions_menu_draw($action_box_id, "0", $menu_items, 250);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "log_list");
	form_end();

	/* fill in the list of available search dropdown */
	$search_facility = array();
	$search_facility["-1"] = "Any";
	$search_facility += log_list_facility();

	$search_severity = array();
	$search_severity["-2"] = "Any";
	$search_severity += log_list_severity();

	$search_poller = array();
	$search_poller["-1"] = "Any";
	$search_poller += log_list_poller();

	$search_host = array();
	$search_host["-1"] = "Any";
	$search_host += log_list_host();

	$search_plugin = array();
	$search_plugin["-1"] = "Any";
	$search_plugin["N/A"] = "N/A";
	$search_plugin += log_list_plugin();

	$search_username = array();
	$search_username["-1"] = "Any";
	$search_username += log_list_username();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'purge') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to purge the log?  All logs will be cleared!'));

			action_area_update_header_caption(box_id, 'Purge Logs');
			action_area_update_submit_caption(box_id, 'Purge');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'export') {
			<?php if (sizeof($filter_array) == 0) { ?>
			parent_div.appendChild(document.createTextNode('Are you sure you want to export all the logs?'));
			<?php }else{ ?>

			parent_div.appendChild(document.createTextNode('Are you sure you want to export the filtered logs?'));

			_elm_start_date_input = action_area_generate_input('text', 'box-' + box_id + '-search_start_date', '<?php echo get_get_var("search_start_date");?>');
			_elm_start_date_input.size = '30';

			_elm_end_date_input = action_area_generate_input('text', 'box-' + box_id + '-search_end_date', '<?php echo get_get_var("search_end_date");?>');
			_elm_end_date_input.size = '30';

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

			_elm_source_input = action_area_generate_input('text', 'box-' + box_id + '-search_source', '<?php echo get_get_var("search_source");?>');
			_elm_source_input.size = '30';

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_start_date_input, 'Start Date Range (YYYY-MM-DD HH:MM:SS)', true, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_end_date_input, 'End Date Range (YYYY-MM-DD HH:MM:SS)', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_fac_input, 'Facility', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_sev_input, 'Severity', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_pol_input, 'Poller', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_host_input, 'Host', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_plug_input, 'Plugin', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_user_input, 'User', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_source_input, 'Source', false, false));

			parent_div.appendChild(action_area_generate_search_field(_elm_ht_input, 'Filter', false, true));


			<?php } ?>

			action_area_update_header_caption(box_id, 'Export');
			action_area_update_submit_caption(box_id, 'Export');
			action_area_update_selected_rows(box_id, parent_form);

		}else if (type == 'search') {

			_elm_start_date_input = action_area_generate_input('text', 'box-' + box_id + '-search_start_date', '<?php echo get_get_var("search_start_date");?>');
			_elm_start_date_input.size = '30';

			_elm_end_date_input = action_area_generate_input('text', 'box-' + box_id + '-search_end_date', '<?php echo get_get_var("search_end_date");?>');
			_elm_end_date_input.size = '30';

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

			_elm_source_input = action_area_generate_input('text', 'box-' + box_id + '-search_source', '<?php echo get_get_var("search_source");?>');
			_elm_source_input.size = '30';

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_start_date_input, 'Start Date Range (YYYY-MM-DD HH:MM:SS)', true, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_end_date_input, 'End Date Range (YYYY-MM-DD HH:MM:SS)', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_fac_input, 'Facility', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_sev_input, 'Severity', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_pol_input, 'Poller', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_host_input, 'Host', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_plug_input, 'Plugin', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_user_input, 'User', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_source_input, 'Source', false, false));

			parent_div.appendChild(action_area_generate_search_field(_elm_ht_input, 'Filter', false, true));

			action_area_update_header_caption(box_id, 'Search');
			action_area_update_submit_caption(box_id, 'Search');
		}else if (type == 'print') {
			window.open('?action=print<?php if ($filter_url != "") { echo "&" . $filter_url; } ?>');
			action_area_hide(<?php echo $action_box_id; ?>);
		}
	}
	-->
	</script>

	<?php

}


function print_logs() {

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
	if (isset_get_var("search_source")) {
		$filter_array["source"] = get_get_var("search_source");
	}
	if (isset_get_var("search_start_date")) {
		$filter_array["start_date"] = get_get_var("search_start_date");
	}
	if (isset_get_var("search_end_date")) {
		$filter_array["end_date"] = get_get_var("search_end_date");
	}

	/* get log entires */
	$logs = log_list($filter_array);
	$total_rows = log_get_total($filter_array);

	/* Output html */
	print "<html>\n";
	print "<head><title>Print Cacti System Logs</title></head>\n";
	print "<body bgcolor='#FFFFF'>\n";
	print "<h1 align='center'>Cacti System Log";
	if (sizeof($filter_array) > 0) {
		print " (Filtered)";
	}
	print "</h1>\n";
	print "<h3 align='center'>" . $total_rows . " entries printed on " . date("F j, Y, g:i a") . "</h3>\n";

	print "<table border='1' cellpadding='2' cellspacing='0' align='center' width='80%'>\n";
	print "	<tr>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Date") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Facility") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Severity") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Poller") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Host") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Plugin") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("User") . "</font></td>\n";
	print "		<td bgcolor='black'><font color='#FFFFFF'>" . _("Source") . "</font></td>\n";
	print "	</tr>";

	$i = 0;
	if ((is_array($logs)) && (sizeof($logs) > 0)) {
		foreach ($logs as $log) {
			?>

			<tr>
				<td><?php echo $log["logdate"]; ?></td>
				<td><?php echo log_get_facility($log["facility"]); ?></td>
				<td><?php echo log_get_severity($log["severity"]); ?></td>
				<td><?php if ($log["poller_name"] == "") { echo "SYSTEM"; }else{ echo $log["poller_name"]; } ?></td>
				<td><?php if ($log["host"] == "") { echo "SYSTEM"; }else{ echo $log["host"]; } ?></td>
				<td><?php if ($log["plugin"] == "") { echo "N/A"; }else{ echo $log["plugin"]; } ?></td>
				<td><?php if ($log["username"] == "") { echo "SYSTEM"; }else{ echo $log["username"]; } ?></td>
				<td><?php if ($log["source"] == "") { echo "SYSTEM"; }else{ echo $log["source"]; } ?></td>
			</tr><tr>
				<td colspan="8" style="padding-left: 25px"><?php echo $log["message"]; ?></td>
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

	?>

	</table>

	<script language="JavaScript">
	<!--
		window.print();
	-->
	</script>

	<?php
}


function export_logs() {

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
	if (isset_get_var("search_source")) {
		$filter_array["source"] = get_get_var("search_source");
	}

	/* Search and Replace chars */
	$search = array("\"","\t","\n","\r");
	$replace = array(""," "," "," ");

	/* get log entires */
	$logs = log_list($filter_array);

	/* Output CSV */
	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=cacti_system_log." . date("Ymd.Hms") . ".csv");

	print "\"" . _("Date") . "\",";
	print "\"" . _("Facility") . "\",";
	print "\"" . _("Severity") . "\",";
	print "\"" . _("Poller") . "\",";
	print "\"" . _("Host") . "\",";
	print "\"" . _("Plugin") . "\",";
	print "\"" . _("User") . "\",";
	print "\"" . _("Source") . "\",";
	print "\"" . _("Message") . "\"\n";

	$i = 0;
	if ((is_array($logs)) && (sizeof($logs) > 0)) {
		foreach ($logs as $log) {
			print "\"" . $log["logdate"] . "\",";
			print "\"" . log_get_facility($log["facility"]) . "\",";
			print "\"" . log_get_severity($log["severity"]) . "\",";
			print "\"";
			if ($log["poller_name"] == "") {
				print "SYSTEM";
			}else{
				print $log["poller_name"];
			}
			print "\",\"";
			if ($log["host"] == "") {
				print "SYSTEM";
			}else{
				print $log["host"];
			}
			print "\",\"";
			if ($log["plugin"] == "") {
				print "N/A";
			}else{
				print $log["plugin"];
			}
			print "\",\"";
			if ($log["username"] == "") {
				print "SYSTEM";
			}else{
				print $log["username"];
			}
			print "\",\"";
			if ($log["source"] == "") {
				print "SYSTEM";
			}else{
				print $log["source"];
			}

			print "\",\"" . str_replace($search,$replace,$log["message"]) . "\"\n";
		}
	}

}


?>
