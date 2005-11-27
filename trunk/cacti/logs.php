<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group				      |
 |									 |
 | This program is free software; you can redistribute it and/or	   |
 | modify it under the terms of the GNU General Public License	     |
 | as published by the Free Software Foundation; either version 2	  |
 | of the License, or (at your option) any later version.		  |
 |									 |
 | This program is distributed in the hope that it will be useful,	 |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of	  |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the	   |
 | GNU General Public License for more details.			    |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution		     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/						   |
 +-------------------------------------------------------------------------+
*/

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");

require(CACTI_BASE_PATH . "/include/log/log_form.php");
require(CACTI_BASE_PATH . "/lib/log/log_info.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		view_logs();
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}


/* -----------------------
    Utilities Functions
   ----------------------- */
function form_save() {

	if (isset($_POST["box-1-search_filter"])) {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: logs.php$get_string");
	}
}


function view_logs() {
	global $device_actions;

	$current_page = get_get_var_number("page", "1");

	/* setup action menu */
	$menu_items = array(
		"purge" => "Purge All",
		"export" => "Export All"
	);


	/* search field: filter (searchs device description and hostname) */
	$filter_array = array();
	if (isset_get_var("search_filter")) {
		$filter_array["message"] = get_get_var("search_filter");
	}

	/* get log entires */
	$logs = api_log_list($filter_array,read_config_option("num_rows_log"),read_config_option("num_rows_log")*($current_page-1));
	$total_rows = api_log_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_filter"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_log"), $total_rows, "logs.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	/* Output html */
	$box_id = 1;
	form_start("logs.php");

	html_start_box("<strong>" . _("Log Management") . "</strong>", "", $url_page_select);
	html_header(array(_("Date"), _("Facility"), _("Severity"), _("Poller"), _("Host"), _("Plugin"), _("User"), _("Message"),""));

	$i = 0;
	if ((is_array($logs)) && (sizeof($logs) > 0)) {
		foreach ($logs as $log) {
			?>
			<tr class="<?php echo api_log_html_css_class(api_log_severity_get($log["severity"])); ?>" id="box-<?php echo $box_id;?>-row-<?php echo $log["id"];?>">
				<td class="content-row">
					<?php echo $log["logdate"]; ?>
				</td>
				<td class="content-row">
					<?php echo api_log_facility_get($log["facility"]); ?>
				</td>
				<td class="content-row">
					<?php echo api_log_severity_get($log["severity"]); ?>
				</td>
				<td class="content-row">
					<?php if ($log["poller_name"] == "") { echo "SYSTEM"; }else{ echo $log["poller_name"]; } ?>
				</td>
				<td class="content-row">
					<?php if ($log["host"] == "") { echo "SYSTEM"; }else{ echo $log["host"]; } ?>
				</td>
				<td class="content-row">
					<?php #if ($log["plugin"] == "") { echo "N/A"; }else{ #echo $log["plugin"]; } ?>
				</td>
				<td class="content-row">
					<?php if ($log["username"] == "") { echo "SYSTEM"; }else{ echo $log["username"]; } ?>
				</td>
				<td colspan="2" class="content-row">
					<?php if (strlen($log["message"]) > 40) { echo substr($log["message"],0,37) . "..."; }else{ echo $log["message"]; } ?>
				</td>
				<div id="box-<?php echo $box_id; ?>-row-<?php echo $log["id"]; ?>-message" style="position: absolute; visibility: hidden;"><?php echo $log["message"]; ?></div>
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


	html_box_toolbar_draw($box_id, "0", "8", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select, 0);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");


}

?>
