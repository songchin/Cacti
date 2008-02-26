<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

load_current_session_value("page_referrer", "page_referrer", "");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

if (isset($_REQUEST["sort_direction"])) {
	if ($_REQUEST['page_referrer'] == "view_snmp_cache") {
		$_REQUEST["action"] = "view_snmp_cache";
	}else if ($_REQUEST['page_referrer'] == "view_poller_cache") {
		$_REQUEST["action"] = "view_poller_cache";
	}else{
		$_REQUEST["action"] = "view_user_log";
	}
}

if ((isset($_REQUEST["clear_x"])) || (isset($_REQUEST["go_x"]))) {
	if ($_REQUEST['page_referrer'] == "view_snmp_cache") {
		$_REQUEST["action"] = "view_snmp_cache";
	}else if ($_REQUEST['page_referrer'] == "view_poller_cache") {
		$_REQUEST["action"] = "view_poller_cache";
	}else if ($_REQUEST['page_referrer'] == "view_user_log") {
		$_REQUEST["action"] = "view_user_log";
	}else{
		$_REQUEST["action"] = "view_logfile";
	}
}

if (isset($_REQUEST["purge_x"])) {
	if ($_REQUEST['page_referrer'] == "view_user_log") {
		$_REQUEST["action"] = "clear_user_log";
	}else{
		$_REQUEST["action"] = "clear_logfile";
	}
}

switch ($_REQUEST["action"]) {
	case 'clear_poller_cache':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		/* obtain timeout settings */
		$max_execution = ini_get("max_execution_time");
		$max_memory = ini_get("memory_limit");

		ini_set("max_execution_time", "0");
		ini_set("memory_limit", "32M");

		repopulate_poller_cache();

		ini_set("max_execution_time", $max_execution);
		ini_set("memory_limit", $max_memory);

		utilities_view_poller_cache();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_snmp_cache':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_snmp_cache();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_poller_cache':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_poller_cache();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_logfile':
		utilities_view_logfile();

		break;
	case 'clear_logfile':
		utilities_clear_logfile();
		utilities_view_logfile();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_user_log':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_user_log();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'clear_user_log':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_clear_user_log();
		utilities_view_user_log();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_tech':
		$php_info = utilities_php_modules();

		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_tech($php_info);

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:

		if (!api_plugin_hook_function('utilities_action', $_REQUEST['action'])) {
			include_once(CACTI_BASE_PATH . "/include/top_header.php");

			utilities();

			include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		}
		break;
}

/* -----------------------
    Utilities Functions
   ----------------------- */

function utilities_php_modules() {

	/*
	   Gather phpinfo into a string variable - This has to be done before
	   any headers are sent to the browser, as we are going to do some
	   output buffering fun
	*/

	ob_start();
	phpinfo(INFO_MODULES);
	$php_info = ob_get_contents();
	ob_end_clean();

	/* Remove nasty style sheets, links and other junk */
	$php_info = str_replace("\n", "", $php_info);
	$php_info = preg_replace('/^.*\<body\>/', '', $php_info);
	$php_info = preg_replace('/\<\/body\>.*$/', '', $php_info);
	$php_info = preg_replace('/\<a.*\>/U', '', $php_info);
	$php_info = preg_replace('/\<\/a\>/', '<hr>', $php_info);
	$php_info = preg_replace('/\<img.*\>/U', '', $php_info);
	$php_info = preg_replace('/\<\/?address\>/', '', $php_info);

	return $php_info;
}


function memory_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}


function memory_readable($val) {

	if ($val < 1024) {
		$val_label = "bytes";
	}elseif ($val < 1048576) {
		$val_label = "K";
		$val /= 1024;
	}elseif ($val < 1073741824) {
		$val_label = "M";
		$val /= 1048576;
	}else{
		$val_label = "G";
		$val /= 1073741824;
	}

    return $val . $val_label;
}


function utilities_view_tech($php_info = "") {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	/* Remove all cached settings, cause read of database */
	kill_session_var("sess_config_array");

	/* Get table status */
	$table_status = db_fetch_assoc("SHOW TABLE STATUS");

	/* Get poller stats */
	$poller_item = db_fetch_assoc("SELECT action, count(action) as total FROM poller_item GROUP BY action");

	/* Get system stats */
	$host_count = db_fetch_cell("SELECT COUNT(*) FROM host");
	$graph_count = db_fetch_cell("SELECT COUNT(*) FROM graph_local");
	$data_count = db_fetch_assoc("SELECT i.type_id, COUNT(i.type_id) AS total FROM data_template_data AS d, data_input AS i WHERE d.data_input_id = i.id AND local_data_id <> 0 GROUP BY i.type_id");

	/* Get RRDtool version */
	$rrdtool_version = "Unknown";
	if ((file_exists(read_config_option("path_rrdtool"))) && ((CACTI_SERVER_OS == "win32") || (is_executable(read_config_option("path_rrdtool"))))) {

		$out_array = array();
		exec(read_config_option("path_rrdtool"), $out_array);

		if (sizeof($out_array) > 0) {
			if (ereg("^RRDtool 1\.2", $out_array[0])) {
				$rrdtool_version = "rrd-1.2.x";
			}else if (ereg("^RRDtool 1\.0\.", $out_array[0])) {
				$rrdtool_version = "rrd-1.0.x";
			}
		}
	}

	/* Check RRDTool issues */
	$rrdtool_error = "";
	if ($rrdtool_version != read_config_option("rrdtool_version")) {
		$rrdtool_error .= "<br><font color='red'>ERROR: Installed RRDTool version does not match configured version.<br>Please visit the <a href='settings.php?tab=general'>Configuration Settings</a> and select the correct RRDTool Utility Version.</font><br>";
	}
	$graph_gif_count = db_fetch_cell("SELECT COUNT(*) FROM graph_templates_graph WHERE image_format_id = 2");
	if (($graph_gif_count > 0) && (read_config_option("rrdtool_version") == "rrd-1.2.x")) {
		$rrdtool_error .= "<br><font color='red'>ERROR: RRDTool 1.2.x does not support the GIF images format, but " . $graph_gif_count . " graph(s) and/or templates have GIF set as the image format.</font><br>";
	}

	/* Display tech information */
	html_start_box("<strong>Technical Support</strong>", "100%", $colors["header"], "3", "center", "");
	html_header(array("General Information"), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Date</td>\n";
	print "		<td class='textAreaNotes'>" . date("r") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Cacti Version</td>\n";
	print "		<td class='textAreaNotes'>" . CACTI_VERSION . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Cacti OS</td>\n";
	print "		<td>" . CACTI_SERVER_OS . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>SNMP Version</td>\n";
	print "		<td>" . read_config_option("snmp_version") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>RRDTool Version</td>\n";
	print "		<td class='textAreaNotes'>" . $rrdtool_versions[$rrdtool_version] . " " . $rrdtool_error . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Hosts</td>\n";
	print "		<td class='textAreaNotes'>" . $host_count . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Graphs</td>\n";
	print "		<td class='textAreaNotes'>" . $graph_count . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Data Sources</td>\n";
	print "		<td class='textAreaNotes'>";
	$data_total = 0;
	if (sizeof($data_count)) {
		foreach ($data_count as $item) {
			print $input_types[$item["type_id"]] . ": " . $item["total"] . "<br>";
			$data_total += $item["total"];
		}
		print "Total: " . $data_total;
	}else{
		print "<font color='red'>0</font>";
	}
	print "</td>\n";
	print "</tr>\n";

	$spine_version = "";
	if ($poller_options[read_config_option("poller_type")] == "spine") {
		$spine_output = shell_exec(read_config_option("path_spine") . " -v");
		$spine_version = substr($spine_output, 6, 6);
	}

	html_header(array("Poller Information"), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Interval</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("poller_interval") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Type</td>\n";
	print "		<td class='textAreaNotes'>" . $poller_options[read_config_option("poller_type")] . " " . $spine_version . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Items</td>\n";
	print "		<td class='textAreaNotes'>";
	$total = 0;
	if (sizeof($poller_item)) {
		foreach ($poller_item as $item) {
			print "Action[" . $item["action"] . "]: " . $item["total"] . "<br>";
			$total += $item["total"];
		}
		print "Total: " . $total;
	}else{
		print "<font color='red'>No items to poll</font>";
	}
	print "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Concurrent Processes</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("concurrent_processes") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Max Threads</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("max_threads") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>PHP Servers</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("php_servers") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Script Timeout</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("script_timeout") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>Max OID</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("max_get_size") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>Last Run Statistics</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("stats_poller") . "</td>\n";
	print "</tr>\n";

	html_header(array("PHP Information"), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>PHP Version</td>\n";
	print "		<td class='textAreaNotes'>" . phpversion() . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>PHP OS</td>\n";
	print "		<td class='textAreaNotes'>" . PHP_OS . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>PHP uname</td>\n";
	print "		<td class='textAreaNotes'>";
	if (function_exists("php_uname")) {
		print php_uname();
	}else{
		print "N/A";
	}
	print "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>PHP SNMP</td>\n";
	print "		<td class='textAreaNotes'>";
	if (function_exists("snmpget")) {
		print "Installed";
	} else {
		print "Not Installed";
	}
	print "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes'>max_execution_time</td>\n";
	print "		<td class='textAreaNotes'>" . ini_get("max_execution_time") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td class='textAreaNotes'>memory_limit</td>\n";
	print "		<td class='textAreaNotes'>" . ini_get("memory_limit");

	/* Calculate memory suggestion based off of data source count */
	$memory_suggestion = $data_total * 32768;
	/* Set minimum - 16M */
	if ($memory_suggestion < 16777216) {
		$memory_suggestion = 16777216;
	}
	/* Set maximum - 512M */
	if ($memory_suggestion > 536870912) {
		$memory_suggestion = 536870912;
	}
	/* Suggest values in 8M increments */
	$memory_suggestion = round($memory_suggestion / 8388608) * 8388608;
	if (memory_bytes(ini_get('memory_limit')) < $memory_suggestion) {
		print "<br><font color='red'>It is highly suggested that you alter you php.ini memory_limit to " . memory_readable($memory_suggestion) . " or higher.  This suggested memory value is calculated based on the number of data source present and is only to be used as a suggestion, actual values may vary system to system based on requirements.</font><br>";
	}
	print "</td>\n";
	print "</tr>\n";

	html_header(array("MySQL Table Information"), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes' colspan='2' align='center'>";
	if (sizeof($table_status) > 0) {
		print "<table border='1' cellpadding='2' cellspacing='0'>\n";
		print "<tr>\n";
		print "  <th>Name</th>\n";
		print "  <th>Rows</th>\n";
		print "  <th>Engine</th>\n";
		print "  <th>Collation</th>\n";
		print "  <th>Check Status</th>\n";
		print "</tr>\n";
		foreach ($table_status as $item) {
			form_alternate_row_color();
			print "  <td>" . $item["Name"] . "</td>\n";
			print "  <td>" . $item["Rows"] . "</td>\n";
			if (isset($item["Engine"])) {
				print "  <td>" . $item["Engine"] . "</td>\n";
			}else{
				print "  <td>Unknown</td>\n";
			}
			if (isset($item["Collation"])) {
				print "  <td>" . $item["Collation"] . "</td>\n";
			} else {
				print "  <td>Unknown</td>\n";
			}
			print "  <td>" . db_fetch_cell("CHECK TABLE " . $item["Name"], "Msg_text") . "</td>\n";
			print "</tr>\n";
		}
		print "</table>\n";
	}else{
		print "Unable to retrieve table status";
	}

	print "</td>\n";
	print "</tr>\n";

	html_header(array("PHP Module Information"), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td class='textAreaNotes' colspan='2'>" . $php_info . "</td>\n";
	print "</tr>\n";

	html_end_box();
}

function utilities_view_user_log() {
	global $colors, $auth_realms;

	define("MAX_DISPLAY_PAGES", 21);

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("result"));
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up username */
	if (isset($_REQUEST["username"])) {
		$_REQUEST["username"] = sanitize_search_string(get_request_var("username"));
	}

	/* clean up search filter */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort direction */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_userlog_current_page");
		kill_session_var("sess_userlog_username");
		kill_session_var("sess_userlog_result");
		kill_session_var("sess_userlog_filter");
		kill_session_var("sess_userlog_sort_column");
		kill_session_var("sess_userlog_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["result"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["username"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_userlog_current_page", "1");
	load_current_session_value("username", "sess_userlog_username", "-1");
	load_current_session_value("result", "sess_userlog_result", "-1");
	load_current_session_value("filter", "sess_userlog_filter", "");
	load_current_session_value("sort_column", "sess_userlog_sort_column", "time");
	load_current_session_value("sort_direction", "sess_userlog_sort_direction", "DESC");

	$_REQUEST['page_referrer'] = 'view_user_log';
	load_current_session_value('page_referrer', 'page_referrer', 'view_user_log');

	?>
	<script type="text/javascript">
	<!--

	function applyViewLogFilterChange(objForm) {
		strURL = '?username=' + objForm.username.value;
		strURL = strURL + '&result=' + objForm.result.value;
		strURL = strURL + '&action=view_user_log';
		strURL = strURL + '&page=1';
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box("<strong>User Login History</strong>", "100%", $colors["header"], "3", "center", "", true);

	include(CACTI_BASE_PATH . "/include/html/inc_user_log_filter_table.php");

	html_end_box(false);

	$sql_where = "";

	/* filter by host */
	if ($_REQUEST["username"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["username"] == "-2") {
		$sql_where = "WHERE user_log.username NOT IN (SELECT DISTINCT username from user_auth)";
	}elseif (!empty($_REQUEST["username"])) {
		$sql_where = "WHERE user_log.username='" . $_REQUEST["username"] . "'";
	}

	/* filter by result */
	if ($_REQUEST["result"] == "-1") {
		/* Show all items */
	}else{
		if (strlen($sql_where)) {
			$sql_where .= " AND user_log.result=" . $_REQUEST["result"];
		}else{
			$sql_where = "WHERE user_log.result=" . $_REQUEST["result"];
		}
	}

	/* filter by search string */
	if ($_REQUEST["filter"] <> "") {
		if (strlen($sql_where)) {
			$sql_where .= " AND (user_log.username LIKE '%%" . $_REQUEST["filter"] . "%%'
				OR user_log.time LIKE '%%" . $_REQUEST["filter"] . "%%'
				OR user_log.ip LIKE '%%" . $_REQUEST["filter"] . "%%')";
		}else{
			$sql_where = "WHERE (user_log.username LIKE '%%" . $_REQUEST["filter"] . "%%'
				OR user_log.time LIKE '%%" . $_REQUEST["filter"] . "%%'
				OR user_log.ip LIKE '%%" . $_REQUEST["filter"] . "%%')";
		}
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM user_auth
		RIGHT JOIN user_log
		ON user_auth.username = user_log.username
		$sql_where");

	$user_log_sql = "SELECT
		user_log.username,
		user_auth.full_name,
		user_auth.realm,
		user_log.time,
		user_log.result,
		user_log.ip
		FROM user_auth
		RIGHT JOIN user_log
		ON user_auth.username = user_log.username
		$sql_where
		ORDER BY " . $_REQUEST["sort_column"] . " " . $_REQUEST["sort_direction"] . "
		LIMIT " . (read_config_option("num_rows_data_source")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_data_source");

//	print $user_log_sql;

	$user_log = db_fetch_assoc($user_log_sql);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_data_source"), $total_rows, 7, "utilities.php?action=view_user_log");

	print $nav;

	$display_text = array(
		"username" => array("Username", "ASC"),
		"full_name" => array("Full Name", "ASC"),
		"realm" => array("Authentication Realm", "ASC"),
		"time" => array("Date", "ASC"),
		"result" => array("Result", "DESC"),
		"ip" => array("IP Address", "DESC"));

	html_header_sort($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($user_log) > 0) {
		foreach ($user_log as $item) {
			form_alternate_row_color();
			?>
			<td width='35%'>
				<?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["username"]);?>
			</td>
			<td width='20%'>
				<?php if (isset($item["full_name"])) {
						print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["full_name"]);
					}else{
						print "(User Removed)";
					}
				?>
			</td>
			<td width='20%'>
				<?php if (isset($auth_realms[$item["realm"]])) {
						print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $auth_realms[$item["realm"]]);
					}else{
						print "N/A";
					}
				?>
			</td>
			<td width='20%'>
				<?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["time"]);?>
			</td>
			<td width='10%'>
				<?php print $item["result"] == 0 ? "Failed" : "Success";?>
			</td>
			<td width='15%'>
				<?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["ip"]);?>
			</td>
			</tr>
			<?php
		}
	}

	print $nav;

	html_end_box();
}

function utilities_clear_user_log() {
	$users = db_fetch_assoc("SELECT DISTINCT username FROM user_auth");

	if (sizeof($users)) {
		/* remove active users */
		foreach ($users as $user) {
			$total_rows = db_fetch_cell("SELECT COUNT(username) FROM user_log WHERE username = '" . $user['username'] . "' AND result = 1");
			if ($total_rows > 1) {
				db_execute("DELETE FROM user_log WHERE username = '" . $user['username'] . "' AND result = 1 ORDER BY time LIMIT " . ($total_rows - 1));
			}
			db_execute("DELETE FROM user_log WHERE username = '" . $user['username'] . "' AND result = 0");
		}

		/* delete inactive users */
		db_execute("DELETE FROM user_log WHERE user_id NOT IN (SELECT id FROM user_auth) OR username NOT IN (SELECT username FROM user_auth)");

	}
}

function utilities_view_logfile() {
	global $colors, $log_tail_lines, $page_refresh_interval;

	$logfile = read_config_option("path_cactilog");

	if ($logfile == "") {
		$logfile = "./log/rrd.log";
	}

	/* helps determine output color */
	$linecolor = True;

	input_validate_input_number(get_request_var_request("tail_files"));
	input_validate_input_number(get_request_var_request("message_type"));
	input_validate_input_number(get_request_var_request("refresh"));
	input_validate_input_number(get_request_var_request("reverse"));

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_logfile_tail_lines");
		kill_session_var("sess_logfile_message_type");
		kill_session_var("sess_logfile_filter");
		kill_session_var("sess_logfile_refresh");
		kill_session_var("sess_logfile_reverse");

		unset($_REQUEST["tail_lines"]);
		unset($_REQUEST["message_type"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["refresh"]);
		unset($_REQUEST["reverse"]);
	}

	load_current_session_value("tail_lines", "sess_logfile_tail_lines", read_config_option("num_rows_log"));
	load_current_session_value("message_type", "sess_logfile_message_type", "-1");
	load_current_session_value("filter", "sess_logfile_filter", "");
	load_current_session_value("refresh", "sess_logfile_refresh", read_config_option("log_refresh_interval"));
	load_current_session_value("reverse", "sess_logfile_reverse", 1);

	$_REQUEST['page_referrer'] = 'view_logfile';
	load_current_session_value('page_referrer', 'page_referrer', 'view_logfile');

	$refresh["seconds"] = $_REQUEST["refresh"];
	$refresh["page"] = "utilities.php?action=view_logfile";

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	?>
	<script type="text/javascript">
	<!--

	function applyViewLogFilterChange(objForm) {
		strURL = '?tail_lines=' + objForm.tail_lines.value;
		strURL = strURL + '&message_type=' + objForm.message_type.value;
		strURL = strURL + '&refresh=' + objForm.refresh.value;
		strURL = strURL + '&reverse=' + objForm.reverse.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		strURL = strURL + '&action=view_logfile';
		strURL = strURL + '&page=1';
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box("<strong>Log File Filters</strong>", "100%", $colors["header"], "3", "center", "", true);

	include(CACTI_BASE_PATH . "/include/html/inc_view_logfile_table.php");

	html_end_box(false);

	/* read logfile into an array and display */
	$logcontents = tail_file($logfile, $_REQUEST["tail_lines"], $_REQUEST["message_type"], $_REQUEST["filter"]);

	if ($_REQUEST["reverse"] == 1) {
		$logcontents = array_reverse($logcontents);
	}

	if ($_REQUEST["message_type"] > 0) {
		$start_string = "<strong>Log File</strong> [Total Lines: " . sizeof($logcontents) . " - Non-Matching Items Hidden]";
	}else{
		$start_string = "<strong>Log File</strong> [Total Lines: " . sizeof($logcontents) . " - All Items Shown]";
	}

	html_start_box($start_string, "100%", $colors["header"], "3", "center", "");

	$i = 0;
	$j = 0;
	$linecolor = false;
	foreach ($logcontents as $item) {
		$host_start = strpos($item, "Host[");
		$ds_start   = strpos($item, "DS[");

		$new_item = "";

		if ((!$host_start) && (!$ds_start)) {
			$new_item = $item;
		}else{
			while ($host_start) {
				$host_end   = strpos($item, "]", $host_start);
				$host_id    = substr($item, $host_start+5, $host_end-($host_start+5));
				$new_item   = $new_item . substr($item, 0, $host_start + 5) . "<a href='host.php?action=edit&id=" . $host_id . "'>" . substr($item, $host_start + 5, $host_end-($host_start + 5)) . "</a>";
				$item       = substr($item, $host_end);
				$host_start = strpos($item, "Host[");
			}

			$ds_start = strpos($item, "DS[");
			while ($ds_start) {
				$ds_end   = strpos($item, "]", $ds_start);
				$ds_id    = substr($item, $ds_start+3, $ds_end-($ds_start+3));
				$new_item = $new_item . substr($item, 0, $ds_start + 3) . "<a href='data_sources.php?action=ds_edit&id=" . $ds_id . "'>" . substr($item, $ds_start + 3, $ds_end-($ds_start + 3)) . "</a>";
				$item     = substr($item, $ds_end);
				$ds_start = strpos($item, "DS[");
			}

			$new_item = $new_item . $item;
		}

		/* get the background color */
		if ((substr_count($new_item, "ERROR")) || (substr_count($new_item, "FATAL"))) {
			$bgcolor = "FF3932";
		}elseif (substr_count($new_item, "WARN")) {
			$bgcolor = "EACC00";
		}elseif (substr_count($new_item, " SQL ")) {
			$bgcolor = "6DC8FE";
		}elseif (substr_count($new_item, "DEBUG")) {
			$bgcolor = "C4FD3D";
		}elseif (substr_count($new_item, "STATS")) {
			$bgcolor = "96E78A";
		}else{
			if ($linecolor) {
				$bgcolor = "CCCCCC";
			}else{
				$bgcolor = "FFFFFF";
			}
			$linecolor = !$linecolor;
		}

		?>
		<tr bgcolor='#<?php print $bgcolor;?>'>
			<td>
				<?php print $new_item;?>
			</td>
		</tr>
		<?php
		$j++;
		$i++;

		if ($j > 1000) {
			?>
			<tr bgcolor='#EACC00'>
				<td>
					<?php print ">>>>  LINE LIMIT OF 1000 LINES REACHED!!  <<<<";?>
				</td>
			</tr>
			<?php

			break;
		}
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

function utilities_clear_logfile() {
	global $colors;

	load_current_session_value("refresh", "sess_logfile_refresh", read_config_option("log_refresh_interval"));

	$refresh["seconds"] = $_REQUEST["refresh"];
	$refresh["page"] = "utilities.php?action=view_logfile";

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	$logfile = read_config_option("path_cactilog");

	if ($logfile == "") {
		$logfile = "./log/cacti.log";
	}

	html_start_box("<strong>Clear Cacti Log File</strong>", "100%", $colors["header"], "1", "center", "");
	if (file_exists($logfile)) {
		if (is_writable($logfile)) {
			$timestamp = date("m/d/Y h:i:s A");
			$log_fh = fopen($logfile, "w");
			fwrite($log_fh, $timestamp . " - WEBUI: Cacti Log Cleared from Web Management Interface\n");
			fclose($log_fh);
			print "<tr><td>Cacti Log File Cleared</td></tr>";
		}else{
			print "<tr><td><font color='red'><b>Error: Unable to clear log, no write permissions.<b></font></td></tr>";
		}
	}else{
		print "<tr><td><font color='red'><b>Error: Unable to clear log, file does not exist.</b></font></td></tr>";
	}
	html_end_box();
}

function utilities_view_snmp_cache() {
	global $colors, $poller_actions;

	define("MAX_DISPLAY_PAGES", 21);

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("host_id"));
	input_validate_input_number(get_request_var_request("snmp_query_id"));
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("poller_action"));
	/* ==================================================== */

	/* clean up search filter */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_snmp_current_page");
		kill_session_var("sess_snmp_host_id");
		kill_session_var("sess_snmp_snmp_query_id");
		kill_session_var("sess_snmp_filter");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["host_id"]);
		unset($_REQUEST["snmp_query_id"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_snmp_current_page", "1");
	load_current_session_value("host_id", "sess_snmp_host_id", "-1");
	load_current_session_value("snmp_query_id", "sess_snmp_snmp_query_id", "-1");
	load_current_session_value("filter", "sess_snmp_filter", "");

	$_REQUEST['page_referrer'] = 'view_snmp_cache';
	load_current_session_value('page_referrer', 'page_referrer', 'view_snmp_cache');

	?>
	<script type="text/javascript">
	<!--

	function applyViewSNMPFilterChange(objForm) {
		strURL = '?host_id=' + objForm.host_id.value;
		strURL = strURL + '&snmp_query_id=' + objForm.snmp_query_id.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		strURL = strURL + '&action=view_snmp_cache';
		strURL = strURL + '&page=1';
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box("<strong>SNMP Cache Items</strong>", "100%", $colors["header"], "3", "center", "", true);

	include(CACTI_BASE_PATH . "/include/html/inc_snmp_cache_filter_table.php");

	html_end_box(false);

	$sql_where = "";

	/* filter by host */
	if ($_REQUEST["host_id"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where .= " AND host.id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where .= " AND host.id=" . $_REQUEST["host_id"];
	}

	/* filter by query name */
	if ($_REQUEST["snmp_query_id"] == "-1") {
		/* Show all items */
	}elseif (!empty($_REQUEST["snmp_query_id"])) {
		$sql_where .= " AND host_snmp_cache.snmp_query_id=" . $_REQUEST["snmp_query_id"];
	}

	/* filter by search string */
	if ($_REQUEST["filter"] <> "") {
		$sql_where .= " AND (host.description LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR snmp_query.name LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR host_snmp_cache.field_name LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR host_snmp_cache.field_value LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR host_snmp_cache.oid LIKE '%%" . $_REQUEST["filter"] . "%%')";
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM (host_snmp_cache,snmp_query,host)
		WHERE host_snmp_cache.host_id=host.id
		AND host_snmp_cache.snmp_query_id=snmp_query.id
		$sql_where");

	$snmp_cache_sql = "SELECT
		host_snmp_cache.*,
		host.description,
		snmp_query.name
		FROM (host_snmp_cache,snmp_query,host)
		WHERE host_snmp_cache.host_id=host.id
		AND host_snmp_cache.snmp_query_id=snmp_query.id
		$sql_where
		LIMIT " . (read_config_option("num_rows_data_source")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_data_source");

//	print $snmp_cache_sql;

	$snmp_cache = db_fetch_assoc($snmp_cache_sql);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_data_source"), $total_rows, 7, "utilities.php?action=view_snmp_cache");

	print $nav;

	html_header(array("Details"));

	if (sizeof($snmp_cache) > 0) {
	foreach ($snmp_cache as $item) {
		form_alternate_row_color();
		?>
		<td>
			Host: <?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["description"]);?>
			, SNMP Query: <?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["name"]);?>
		</td>
		</tr>
		<?php
		form_alternate_row_color();
		?>
		<td>
			Index: <?php print $item["snmp_index"];?>
			, Field Name: <?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["field_name"]);?>
			, Field Value: <?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["field_value"]);?>
		</td>
		</tr>
		<?php
		form_alternate_row_color();
		?>
		<td>
			OID: <?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["oid"]);?>
		</td>
		</tr>
		<?php
	}
	}

	print $nav;

	html_end_box();
}

function utilities_view_poller_cache() {
	global $colors, $poller_actions;

	define("MAX_DISPLAY_PAGES", 21);

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("host_id"));
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("poller_action"));
	/* ==================================================== */

	/* clean up search filter */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort direction */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_poller_current_page");
		kill_session_var("sess_poller_host_id");
		kill_session_var("sess_poller_poller_action");
		kill_session_var("sess_poller_filter");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["host_id"]);
		unset($_REQUEST["poller_action"]);
	}

	if ((!empty($_SESSION["sess_poller_action"])) && (!empty($_REQUEST["poller_action"]))) {
		if ($_SESSION["sess_poller_poller_action"] != $_REQUEST["poller_action"]) {
			$_REQUEST["page"] = 1;
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_poller_current_page", "1");
	load_current_session_value("host_id", "sess_poller_host_id", "-1");
	load_current_session_value("poller_action", "sess_poller_poller_action", "-1");
	load_current_session_value("filter", "sess_poller_filter", "");
	load_current_session_value("sort_column", "sess_poller_sort_column", "data_template_data.name_cache");
	load_current_session_value("sort_direction", "sess_poller_sort_direction", "ASC");

	$_REQUEST['page_referrer'] = 'view_poller_cache';
	load_current_session_value('page_referrer', 'page_referrer', 'view_poller_cache');

	?>
	<script type="text/javascript">
	<!--

	function applyPItemFilterChange(objForm) {
		strURL = '?poller_action=' + objForm.poller_action.value;
		strURL = strURL + '&host_id=' + objForm.host_id.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		strURL = strURL + '&action=view_poller_cache';
		strURL = strURL + '&page=1';
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box("<strong>Poller Cache Items</strong>", "100%", $colors["header"], "3", "center", "", true);

	include(CACTI_BASE_PATH . "/include/html/inc_poller_item_filter_table.php");

	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE poller_item.local_data_id=data_template_data.local_data_id";

	if ($_REQUEST["poller_action"] == "-1") {
		/* Show all items */
	}else {
		$sql_where .= " AND poller_item.action='" . $_REQUEST["poller_action"] . "'";
	}

	if ($_REQUEST["host_id"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where .= " AND poller_item.host_id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where .= " AND poller_item.host_id=" . $_REQUEST["host_id"];
	}

	if (strlen($_REQUEST["filter"])) {
		$sql_where .= " AND (data_template_data.name_cache LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR host.description LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR poller_item.arg1 LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR poller_item.hostname LIKE '%%" . $_REQUEST["filter"] . "%%'
			OR poller_item.rrd_path  LIKE '%%" . $_REQUEST["filter"] . "%%')";
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM data_template_data
		RIGHT JOIN (poller_item
		LEFT JOIN host
		ON poller_item.host_id=host.id)
		ON data_template_data.local_data_id=poller_item.local_data_id
		$sql_where");

	$poller_sql = "SELECT
		poller_item.*,
		data_template_data.name_cache,
		host.description
		FROM data_template_data
		RIGHT JOIN (poller_item
		LEFT JOIN host
		ON poller_item.host_id=host.id)
		ON data_template_data.local_data_id=poller_item.local_data_id
		$sql_where
		ORDER BY " . $_REQUEST["sort_column"] . " " . $_REQUEST["sort_direction"] . ", action ASC
		LIMIT " . (read_config_option("num_rows_data_source")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_data_source");

//	print $poller_sql;

	$poller_cache = db_fetch_assoc($poller_sql);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_data_source"), $total_rows, 7, "utilities.php?action=view_poller_cache");

	print $nav;

	$display_text = array(
		"data_template_data.name_cache" => array("Data Source Name", "ASC"),
		"" => array("Details", "ASC"));

	html_header_sort($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($poller_cache) > 0) {
	foreach ($poller_cache as $item) {
		form_alternate_row_color();
			?>
			<td width="375">
				<a class="linkEditMain" href="data_sources.php?action=ds_edit&id=<?php print $item["local_data_id"];?>"><?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["name_cache"]);?></a>
			</td>

			<td>
			<?php
			if ($item["action"] == 0) {
				if ($item["snmp_version"] != 3) {
					$details =
						"SNMP Version: " . $item["snmp_version"] . ", " .
						"Community: " . $item["snmp_community"] . ", " .
						"OID: " . eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"]);
				}else{
					$details =
						"SNMP Version: " . $item["snmp_version"] . ", " .
						"User: " . $item["snmp_username"] . ", OID: " . $item["arg1"];
				}
			}elseif ($item["action"] == 1) {
					$details = "Script: " . eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"]);
			}else{
					$details = "Script Server: " . eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"]);
			}

			print $details;
			?>
			</td>
		</tr>
		<?php

		form_alternate_row_color();
		?>
			<td>
			</td>
			<td>
				RRD: <?php print $item["rrd_path"];?>
			</td>
		</tr>
		<?php
	}
	}

	print $nav;

	html_end_box();
}

function utilities() {
	global $colors;

	html_start_box("<strong>Cacti System Utilities</strong>", "100%", $colors["header"], "3", "center", "");

	?>
	<colgroup span="3">
		<col valign="top" width="20"></col>
		<col valign="top" width="10"></col>
	</colgroup>

	<?php html_header(array("Technical Support"), 2); ?>
	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_tech'>Technical Support</a>
		</td>
		<td class="textAreaNotes">
			Cacti technical support page.  Used by developers and technical support persons to assist with issues in Cacti.  Includes checks for common configuration issues.
		</td>
	</tr>

	<?php html_header(array("Log Administration"), 2);?>

	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_logfile'>View Cacti Log File</a>
		</td>
		<td class="textAreaNotes">
			The Cacti Log File stores statistic, error and other message depending on system settings.  This information can be used to identify problems with the poller and application.
		</td>
	</tr>
	<tr class="rowAlternate2">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_user_log'>View User Log</a>
		</td>
		<td class="textAreaNotes">
			Allows Administrators to browse the user log.  Administrators can filter and export the log as well.
		</td>
	</tr>

	<?php html_header(array("Poller Cache Administration"), 2); ?>

	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_poller_cache'>View Poller Cache</a>
		</td>
		<td class="textAreaNotes">
			This is the data that is being passed to the poller each time it runs. This data is then in turn executed/interpreted and the results are fed into the rrd files for graphing or the database for display.
		</td>
	</tr>
	<tr class="rowAlternate2">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_snmp_cache'>View SNMP Cache</a>
		</td>
		<td class="textAreaNotes">
			The SNMP cache stores information gathered from SNMP queries. It is used by cacti to determine the OID to use when gathering information from an SNMP-enabled host.
		</td>
	</tr>
	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=clear_poller_cache'>Rebuild Poller Cache</a>
		</td>
		<td class="textAreaNotes">
			The poller cache will be cleared and re-generated if you select this option. Sometimes host/data source data can get out of sync with the cache in which case it makes sense to clear the cache and start over.
		</td>
	</tr>

	<?php

	api_plugin_hook('utilities_list');

	html_end_box();
}

?>
