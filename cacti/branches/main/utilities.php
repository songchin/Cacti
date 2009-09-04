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
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_tech();

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
	$php_info = str_replace("<hr>", "", $php_info);
	$php_info = str_replace("<br />", "", $php_info);
	$php_info = str_replace("<h2>", "<h2><strong>" . __("Module Name:") . " </strong>", $php_info);
	$php_info = str_replace("cellpadding=\"3\"", "cellspacing=\"0\" cellpadding=\"3\"", $php_info);

	return $php_info;
}

function memory_bytes($val) {
	$val  = trim($val);
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


function utilities_view_tech() {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	/* Remove all cached settings, cause read of database */
	kill_session_var("sess_config_array");

	$tabs = array(
		"general" => __("General"),
		"database" => __("DB Info"),
		"process" => __("DB Processes"),
		"php" => __("PHP Info"),
		"i18n" => __("Languages")
	);

	/* set the default settings category */
	if (!isset($_REQUEST["tab"])) {
		/* there is no selected tab; select the first one */
		$current_tab = array_keys($tabs);
		$current_tab = $current_tab[0];
	}else{
		$current_tab = $_REQUEST["tab"];
	}

	/* draw the categories tabs on the top of the page */
	print "<table width='100%' cellspacing='0' cellpadding='0' align='center'><tr>";
	print "<td><div class='tabs'>";

	if (sizeof($tabs) > 0) {
	foreach (array_keys($tabs) as $tab_short_name) {
		print "<div class='tabDefault'><a " . (($tab_short_name == $current_tab) ? "class='tabSelected'" : "class='tabDefault'") . " href='" . htmlspecialchars("utilities.php?action=view_tech&tab=$tab_short_name") . "'>$tabs[$tab_short_name]</a></div>";
	}
	}
	print "</div></td></tr></table>";

	if (!isset($_REQUEST["tab"])) {
		$_REQUEST["tab"] = "general";
	}

	switch ($_REQUEST["tab"]) {
		case "general":
			display_general();

			break;
		case "database":
			display_database();

			break;
		case "process":
			display_database_processes();

			break;
		case "php":
			display_php();

			break;
		case "i18n":
			display_languages();
		default:

			break;
	}
}

function display_php() {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	$php_info = utilities_php_modules();

	html_start_box("<strong>" . __("PHP Module Information") . "</strong>", "100%", $colors["header"], "3", "center", "");
	print "<tr>\n";
	print "<td style='padding:0px;margin:0px;'>" . $php_info . "</td>\n";
	print "</tr>\n";

	html_end_box();
}

function display_general() {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	/* Get poller stats */
	$poller_item = db_fetch_assoc("SELECT action, count(action) as total FROM poller_item GROUP BY action");

	/* Get system stats */
	$host_count  = db_fetch_cell("SELECT COUNT(*) FROM host");
	$graph_count = db_fetch_cell("SELECT COUNT(*) FROM graph_local");
	$data_count  = db_fetch_assoc("SELECT i.type_id, COUNT(i.type_id) AS total FROM data_template_data AS d, data_input AS i WHERE d.data_input_id = i.id AND local_data_id <> 0 GROUP BY i.type_id");

	/* Get RRDtool version */
	$rrdtool_version = __("Unknown");
	if ((file_exists(read_config_option("path_rrdtool"))) && ((function_exists('is_executable')) && (is_executable(read_config_option("path_rrdtool"))))) {

		$out_array = array();
		exec(read_config_option("path_rrdtool"), $out_array);

		if (sizeof($out_array) > 0) {
			if (ereg("^RRDtool 1\.3", $out_array[0])) {
				$rrdtool_version = "rrd-1.3.x";
			}else if (ereg("^RRDtool 1\.2\.", $out_array[0])) {
				$rrdtool_version = "rrd-1.2.x";
			}else if (ereg("^RRDtool 1\.0\.", $out_array[0])) {
				$rrdtool_version = "rrd-1.0.x";
			}
		}
	}

	/* Get SNMP cli version */
	$snmp_version = read_config_option("snmp_version");
	if ((file_exists(read_config_option("path_snmpget"))) && ((function_exists('is_executable')) && (is_executable(read_config_option("path_snmpget"))))) {
		$snmp_version = trim(shell_exec(read_config_option("path_snmpget") . " -V 2>&1"));
	}

	/* Check RRDTool issues */
	$rrdtool_error = "";
	if ($rrdtool_version != read_config_option("rrdtool_version")) {
		$rrdtool_error .= "<br><font color='red'>" . __("ERROR: Installed RRDTool version does not match configured version.") . "<br>" . __("Please visit the") . " <a href='settings.php?tab=general'> " . __("Configuration Settings") . "</a>" . __("and select the correct RRDTool Utility Version.") . "</font><br>";
	}
	$graph_gif_count = db_fetch_cell("SELECT COUNT(*) FROM graph_templates_graph WHERE image_format_id = 2");
	if (($graph_gif_count > 0) && (read_config_option("rrdtool_version") != "rrd-1.0.x")) {
		$rrdtool_error .= "<br><font color='red'>" . sprintf(__("ERROR: RRDTool 1.2.x does not support the GIF images format, but %s graph(s) and/or templates have GIF set as the image format."), $graph_gif_count) . "</font><br>";
	}

	/* Display tech information */
	html_start_box("<strong>" . __("General Technical Support Information") . "</strong>", "100%", $colors["header"], 0, "center", "");
	print "<tr><td>";
	html_header(array(__("General Information")), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Date") . "</td>\n";
	print "		<td class='textAreaNotes'>" . date("r") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Cacti Version") . "</td>\n";
	print "		<td class='textAreaNotes'>" . CACTI_VERSION . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Cacti OS") . "</td>\n";
	print "		<td>" . CACTI_SERVER_OS . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("SNMP Version") . "</td>\n";
	print "		<td>" . $snmp_version . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("RRDTool Version") . "</td>\n";
	print "		<td class='textAreaNotes'>" . $rrdtool_versions[$rrdtool_version] . " " . $rrdtool_error . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Hosts") . "</td>\n";
	print "		<td class='textAreaNotes'>" . $host_count . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Graphs") . "</td>\n";
	print "		<td class='textAreaNotes'>" . $graph_count . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Data Sources") . "</td>\n";
	print "		<td class='textAreaNotes'>";
	$data_total = 0;
	if (sizeof($data_count)) {
		foreach ($data_count as $item) {
			print $input_types[$item["type_id"]] . ": " . $item["total"] . "<br>";
			$data_total += $item["total"];
		}
		print __("Total:") . " " . $data_total;
	}else{
		print "<font color='red'>0</font>";
	}

	$spine_version = "";
	if ($poller_options[read_config_option("poller_type")] == "spine") {
		$spine_output = shell_exec(read_config_option("path_spine") . " -v");
		$spine_version = substr($spine_output, 6, 6);
	}

	print "</table></td></tr>";		/* end of html_header */
	print "<tr><td>";
	html_header(array(__("Poller Information")), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Interval") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("poller_interval") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Type"). "</td>\n";
	print "		<td class='textAreaNotes'>" . $poller_options[read_config_option("poller_type")] . " " . $spine_version . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Items") . "</td>\n";
	print "		<td class='textAreaNotes'>";
	$total = 0;
	if (sizeof($poller_item)) {
		foreach ($poller_item as $item) {
			print "Action[" . $item["action"] . "]: " . $item["total"] . "<br>";
			$total += $item["total"];
		}
		print __("Total:") . " " . $total;
	}else{
		print "<font color='red'>" . __("No items to poll") . "</font>";
	}
	print "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Concurrent Processes") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("concurrent_processes") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Max Threads") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("max_threads") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("PHP Servers") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("php_servers") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Script Timeout") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("script_timeout") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Max OID") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("max_get_size") . "</td>\n";
	print "</tr>\n";

	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Last Run Statistics") . "</td>\n";
	print "		<td class='textAreaNotes'>" . read_config_option("stats_poller") . "</td>\n";
	print "</tr>\n";

	print "</table></td></tr>";		/* end of html_header */
	print "<tr><td>";
	html_header(array(__("PHP Information")), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("PHP Version") . "</td>\n";
	print "		<td class='textAreaNotes'>" . phpversion() . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("PHP OS") . "</td>\n";
	print "		<td class='textAreaNotes'>" . PHP_OS . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("PHP uname") . "</td>\n";
	print "		<td class='textAreaNotes'>";
	if (function_exists("php_uname")) {
		print php_uname();
	}else{
		print __("N/A");
	}
	print "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("PHP SNMP") . "</td>\n";
	print "		<td class='textAreaNotes'>";
	if (function_exists("snmpget")) {
		print __("Installed");
	} else {
		print __("Not Installed");
	}
	print "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>max_execution_time</td>\n";
	print "		<td class='textAreaNotes'>" . ini_get("max_execution_time") . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>memory_limit</td>\n";
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
		print "<br><font color='red'>" . sprintf(__("It is highly suggested that you alter you php.ini memory_limit to %s or higher.  This suggested memory value is calculated based on the number of data source present and is only to be used as a suggestion, actual values may vary system to system based on requirements."), memory_readable($memory_suggestion)) . "</font><br>";
	}
	print "</table></td></tr>";		/* end of html_header */

	html_end_box();
}

function display_database() {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	/* Get table status */
	$table_status = db_fetch_assoc("SHOW TABLE STATUS");

	$display_array = array(__("Name"), __("Engine"), __("Version"), __("Row Format"), __("Rows"), __("Average Length"), __("Data Length"), __("Index Length"), __("Auto Increment"), __("Collation"), __("Comment"));

	html_start_box("<strong>" . __("MySQL Table Information") . "</strong>", "100%", $colors["header"], 0, "center", "");
	print "<tr><td>";
	html_header($display_array);
	if (sizeof($table_status) > 0) {
		foreach ($table_status as $item) { #print "<pre>"; print_r($item); print "</pre>";
			form_alternate_row_color("row_" . $item["Name"]);
			print "<td>" . $item["Name"] . "</td>\n";
			if (isset($item["Engine"])) {
				print "  <td>" . $item["Engine"] . "</td>\n";
			}else{
				print "  <td>" . __("Unknown") . "</td>\n";
			}
			print "<td>" . $item["Version"] . "</td>\n";
			print "<td>" . $item["Row_format"] . "</td>\n";
			print "<td>" . $item["Rows"] . "</td>\n";
			print "<td>" . $item["Avg_row_length"] . "</td>\n";
			print "<td>" . $item["Data_length"] . "</td>\n";
			print "<td>" . $item["Index_length"] . "</td>\n";
			print "<td>" . $item["Auto_increment"] . "</td>\n";
			if (isset($item["Collation"])) {
				print "  <td>" . $item["Collation"] . "</td>\n";
			} else {
				print "  <td>". __("Unknown") . "</td>\n";
			}
			print "<td>" . db_fetch_cell("CHECK TABLE " . $item["Name"], "Msg_text") . "</td>\n";
			print "</tr>\n";
		}
	}else{
		print __("Unable to retrieve table status");
	}
	print "</table></td></tr>";		/* end of html_header */
	html_end_box();
}

function display_database_processes() {
	global $colors, $config, $rrdtool_versions, $poller_options, $input_types;

	/* Get table status */
	$db_processes = db_fetch_assoc("SHOW PROCESSLIST");

	$display_array = array(__("ID"), __("User"), __("Host"), __("Database"), __("Command"), __("Time"), __("State"), __("Info"));

	html_start_box("<strong>" . __("MySQL Process Information") . "</strong>", "100%", $colors["header"], 0, "center", "");
	print "<tr><td>";
	html_header($display_array);
	if (sizeof($db_processes) > 0) {
		foreach ($db_processes as $item) {
			form_alternate_row_color("row_" . $item["Id"]);
			print "<td>" . $item["Id"] . "</td>\n";
			print "<td>" . $item["User"] . "</td>\n";
			print "<td>" . $item["Host"] . "</td>\n";
			print "<td>" . $item["db"] . "</td>\n";
			print "<td>" . $item["Command"] . "</td>\n";
			print "<td>" . $item["Time"] . "</td>\n";
			print "<td>" . $item["State"] . "</td>\n";
			print "<td>" . $item["Info"] . "</td>\n";
			print "</tr>\n";
		}
	}else{
		print __("Unable to retrieve process status");
	}
	print "</table></td></tr>";		/* end of html_header */
	html_end_box();
}

function display_languages() {
	global $colors, $config, $cacti_textdomains, $lang2locale, $i18n_modes, $cacti_locale;

	$loaded_extensions = get_loaded_extensions();

	$language = $lang2locale[$cacti_locale]["language"];

	/* rebuild $lang2locale array to find country and language codes easier */
	$locations = array();
	foreach($lang2locale as $locale => $properties) {
		$locations[$properties['filename']] = $properties["language"];
	}

	/* create a list of all languages this Cacti system supports ... */
	$dhandle = opendir(CACTI_BASE_PATH . "/locales/LC_MESSAGES");
	$supported_languages["cacti"] = "English, ";
	while (false !== ($filename = readdir($dhandle))) {
		if(isset($locations[$filename])) {
			$supported_languages["cacti"] .= $locations[$filename] . ", ";
		}
	}
	$supported_languages["cacti"] = substr($supported_languages["cacti"], 0, -2);

	/* ... and do the same for all installed plugins */
	$plugins = db_fetch_assoc("SELECT `directory` FROM `plugin_config`");

	if(sizeof($plugins)>0) {
		foreach($plugins as $plugin) {

			$plugin = $plugin["directory"];
			$dhandle = @opendir(CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales/LC_MESSAGES");
			$supported_languages[$plugin] = "English, ";
			if($dhandle) {
				while (false !== ($filename = readdir($dhandle))) {
					if(isset($locations[$filename])) {
						$supported_languages[$plugin] .= $locations[$filename] . ", ";
					}
				}
			}
			$supported_languages[$plugin] = substr($supported_languages[$plugin], 0, -2);
		}
	}


	html_start_box("<strong>" . __("Language Information") . "</strong>", "100%", $colors["header"], "3", "center", "");
	html_header(array(__("General Information")), 2);
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Current Language") . "</td>\n";
	print "		<td class='textAreaNotes'>". $language . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate2'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Language Mode") . "</td>\n";
	print "		<td class='textAreaNotes'>" . $i18n_modes[read_config_option('i18n_support')] . "</td>\n";
	print "</tr>\n";
	print "<tr class='rowAlternate1'>\n";
	print "		<td style='width:20%;' class='textAreaNotes'>" . __("Default Language") . "</td>\n";
	print "		<td class='textAreaNotes'>" . __("English") . "</td>\n";
	print "</tr>\n";
	html_header(array(__("Supported Languages")), 2);
	$i = 0;
	if(sizeof($supported_languages)>0) {
		foreach($supported_languages as $domain => $languages) {
			$class_int = $i % 2 +1;
			print "<tr class='rowAlternate" . $class_int . "'>\n";
			print "		<td style='width:20%;' class='textAreaNotes'>" . ucfirst($domain) . "</td>\n";
			print "		<td class='textAreaNotes'>". $languages . "</td>\n";
			print "</tr>\n";
			$i++;
		}
	}else {
			print "<tr class='rowAlternate1'>\n";
			print "		<td class='textAreaNotes'><i>" . __("no languages supported."). "</i></td>\n";
			print "</tr>\n";
	}
	html_header(array(__("Loaded Language Files")), 2);
	$i = 0;
	if(sizeof($cacti_textdomains)>0) {
		foreach($cacti_textdomains as $domain => $paths) {
			$class_int = $i % 2 +1;
			print "<tr class='rowAlternate" . $class_int . "'>\n";
			print "		<td style='width:20%;' class='textAreaNotes'>" . ucfirst($domain) . "</td>\n";
			print "		<td class='textAreaNotes'>". $paths['path2catalogue'] . "</td>\n";
			print "</tr>\n";
			$i++;
		}
	}else {
			print "<tr class='rowAlternate1'>\n";
			print "		<td class='textAreaNotes'><i>" . __("No Languages File Loaded.") . "</i></td>\n";
			print "</tr>\n";
	}
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

	html_start_box("<strong>" . __("User Login History") . "</strong>", "100%", $colors["header"], "3", "center", "", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_userlog" action="utilites.php">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						<?php print __("Username:");?>&nbsp;
					</td>
					<td width="1">
						<select name="username" onChange="applyViewLogFilterChange(document.form_userlog)">
							<option value="-1"<?php if ($_REQUEST["username"] == "-1") {?> selected<?php }?>><?php print __("All");?></option>
							<option value="-2"<?php if ($_REQUEST["username"] == "-2") {?> selected<?php }?>><?php print __("Deleted/Invalid");?></option>
							<?php
							$users = db_fetch_assoc("SELECT DISTINCT username FROM user_auth ORDER BY username");

							if (sizeof($users) > 0) {
							foreach ($users as $user) {
								print "<option value='" . $user["username"] . "'"; if ($_REQUEST["username"] == $user["username"]) { print " selected"; } print ">" . $user["username"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<?php print __("Result:");?>&nbsp;
					</td>
					<td width="1">
						<select name="result" onChange="applyViewLogFilterChange(document.form_userlog)">
							<option value="-1"<?php if ($_REQUEST['result'] == '-1') {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="1"<?php if ($_REQUEST['result'] == '1') {?> selected<?php }?>><?php print __("Success");?></option>
							<option value="0"<?php if ($_REQUEST['result'] == '0') {?> selected<?php }?>><?php print __("Failed");?></option>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:160px;'>
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
						<input type="submit" Value="<?php print __("Purge");?>" name="purge_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_user_log'></div>
			</form>
		</td>
	</tr>
	<?php
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

	html_start_box("", "100%", $colors["header"], "0", "center", "");

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
	html_end_box();

	$display_text = array(
		"username" => array(__("Username"), "ASC"),
		"full_name" => array(__("Full Name"), "ASC"),
		"realm" => array(__("Authentication Realm"), "ASC"),
		"time" => array(__("Date"), "ASC"),
		"result" => array(__("Result"), "DESC"),
		"ip" => array(__("IP Address"), "DESC"));

	html_header_sort($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($user_log) > 0) {
		foreach ($user_log as $item) {
			form_alternate_row_color($item["username"] . strtotime($item["time"]), true);
			?>
			<td width='35%'>
				<?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["username"])) : $item["username"]);?>
			</td>
			<td width='20%'>
				<?php if (isset($item["full_name"])) {
						print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["full_name"])) : $item["full_name"]);
					}else{
						print "(" . __("User Removed") . ")";
					}
				?>
			</td>
			<td width='20%'>
				<?php if (isset($auth_realms[$item["realm"]])) {
						print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $auth_realms[$item["realm"]])) : $auth_realms[$item["realm"]]);
					}else{
						print __("N/A");
					}
				?>
			</td>
			<td width='20%'>
				<?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["time"])) : $item["time"]);?>
			</td>
			<td width='10%'>
				<?php print $item["result"] == 0 ? __("Failed") : __("Success");?>
			</td>
			<td width='15%'>
				<?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["ip"])) : $item["ip"]);?>
			</td>
			<?php
			form_end_row();
		}
	}

	print $nav;
	print "</table>\n</form>\n";	# end form and table of html_header_sort_checkbox

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

	html_start_box("<strong>" . __("Log File Filters") . "</strong>", "100%", $colors["header"], "3", "center", "", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_logfile" action="utilities.php">
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						<?php print __("Tail Lines:");?>&nbsp;
					</td>
					<td width="1">
						<select name="tail_lines" onChange="applyViewLogFilterChange(document.form_logfile)">
							<?php
							foreach($log_tail_lines AS $tail_lines => $display_text) {
								print "<option value='" . $tail_lines . "'"; if ($_REQUEST["tail_lines"] == $tail_lines) { print " selected"; } print ">" . $display_text . "</option>\n";
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:100px;'>
						&nbsp;<?php print __("Message Type:");?>&nbsp;
					</td>
					<td width="1">
						<select name="message_type" onChange="applyViewLogFilterChange(document.form_logfile)">
							<option value="-1"<?php if ($_REQUEST['message_type'] == '-1') {?> selected<?php }?>><?php print __("All");?></option>
							<option value="1"<?php if ($_REQUEST['message_type'] == '1') {?> selected<?php }?>><?php print __("Stats");?></option>
							<option value="2"<?php if ($_REQUEST['message_type'] == '2') {?> selected<?php }?>><?php print __("Warnings");?></option>
							<option value="3"<?php if ($_REQUEST['message_type'] == '3') {?> selected<?php }?>><?php print __("Errors");?></option>
							<option value="4"<?php if ($_REQUEST['message_type'] == '4') {?> selected<?php }?>><?php print __("Debug");?></option>
							<option value="5"<?php if ($_REQUEST['message_type'] == '5') {?> selected<?php }?>><?php print __("SQL Calls");?></option>
						</select>
					</td>
					<td style='white-space:nowrap;width:180px;'>
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
						<input type="submit" Value="<?php print __("Purge");?>" name="purge_x" align="middle">
					</td>
				</tr>
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						<?php print __("Refresh:");?>&nbsp;
					</td>
					<td width="1">
						<select name="refresh" onChange="applyViewLogFilterChange(document.form_logfile)">
							<?php
							foreach($page_refresh_interval AS $seconds => $display_text) {
								print "<option value='" . $seconds . "'"; if ($_REQUEST["refresh"] == $seconds) { print " selected"; } print ">" . $display_text . "</option>\n";
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:100px;'>
						&nbsp;<?php print __("Display Order:");?>&nbsp;
					</td>
					<td width="1">
						<select name="reverse" onChange="applyViewLogFilterChange(document.form_logfile)">
							<option value="1"<?php if ($_REQUEST['reverse'] == '1') {?> selected<?php }?>><?php print __("Newest First");?></option>
							<option value="2"<?php if ($_REQUEST['reverse'] == '2') {?> selected<?php }?>><?php print __("Oldest First");?></option>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="75" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_logfile'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* read logfile into an array and display */
	$logcontents = tail_file($logfile, $_REQUEST["tail_lines"], $_REQUEST["message_type"], $_REQUEST["filter"]);

	if ($_REQUEST["reverse"] == 1) {
		$logcontents = array_reverse($logcontents);
	}

	if ($_REQUEST["message_type"] > 0) {
		$start_string = "<strong>" . __("Log File") . "</strong> [" . __("Total Lines:") . " " . sizeof($logcontents) . " - " . __("Non-Matching Items Hidden") . "]";
	}else{
		$start_string = "<strong>" . __("Log File") . "</strong> [" . __("Total Lines:") . " " . sizeof($logcontents) . " - " . __("All Items Shown") . "]";
	}

	html_start_box($start_string, "100%", $colors["header"], "0", "center", "");

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
				$new_item   = $new_item . substr($item, 0, $host_start + 5) . "<a href='" . htmlspecialchars("host.php?action=edit&id=" . $host_id) . "'>" . substr($item, $host_start + 5, $host_end-($host_start + 5)) . "</a>";
				$item       = substr($item, $host_end);
				$host_start = strpos($item, "Host[");
			}

			$ds_start = strpos($item, "DS[");
			while ($ds_start) {
				$ds_end   = strpos($item, "]", $ds_start);
				$ds_id    = substr($item, $ds_start+3, $ds_end-($ds_start+3));
				$new_item = $new_item . substr($item, 0, $ds_start + 3) . "<a href='" . htmlspecialchars("data_sources.php?action=ds_edit&id=" . $ds_id) . "'>" . substr($item, $ds_start + 3, $ds_end-($ds_start + 3)) . "</a>";
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
					<?php print ">>>>  " . __("LINE LIMIT OF 1000 LINES REACHED!!") . "  <<<<";?>
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

	html_start_box("<strong>" . __("Clear Cacti Log File") . "</strong>", "100%", $colors["header"], "1", "center", "");
	if (file_exists($logfile)) {
		if (is_writable($logfile)) {
			$timestamp = date("m/d/Y h:i:s A");
			$log_fh = fopen($logfile, "w");
			fwrite($log_fh, $timestamp . " - WEBUI: Cacti Log Cleared from Web Management Interface\n");
			fclose($log_fh);
			print "<tr><td>" . __("Cacti Log File Cleared") . "</td></tr>";
		}else{
			print "<tr><td><font color='red'><b>" . __("Error: Unable to clear log, ") . __("no write permissions.") . "<b></font></td></tr>";		}
	}else{
		print "<tr><td><font color='red'><b>" . __("Error: Unable to clear log, ") . __("file does not exist.") . "</b></font></td></tr>";
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

	html_start_box("<strong>" . __("SNMP Cache Items") . "</strong>", "100%", $colors["header"], "3", "center", "", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_snmpcache" action="utilities.php">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						<?php print __("Host:");?>&nbsp;
					</td>
					<td width="1">
						<select name="host_id" onChange="applyViewSNMPFilterChange(document.form_snmpcache)">
							<option value="-1"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>><?php print __("None");?></option>
							<?php
							if ($_REQUEST["snmp_query_id"] == -1) {
								$hosts = db_fetch_assoc("SELECT DISTINCT
											host.id,
											host.description,
											host.hostname
											FROM (host_snmp_cache,snmp_query,host)
											WHERE host_snmp_cache.host_id=host.id
											AND host_snmp_cache.snmp_query_id=snmp_query.id
											ORDER by host.description");
							}else{
								$hosts = db_fetch_assoc("SELECT DISTINCT
											host.id,
											host.description,
											host.hostname
											FROM (host_snmp_cache,snmp_query,host)
											WHERE host_snmp_cache.host_id=host.id
											AND host_snmp_cache.snmp_query_id=snmp_query.id
											AND host_snmp_cache.snmp_query_id='" . $_REQUEST["snmp_query_id"] . "'
											ORDER by host.description");
							}
							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='" . $host["id"] . "'"; if ($_REQUEST["host_id"] == $host["id"]) { print " selected"; } print ">" . $host["description"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:90px;'>
						&nbsp;<?php print __("Query Name:");?>&nbsp;
					</td>
					<td width="1">
						<select name="snmp_query_id" onChange="applyViewSNMPFilterChange(document.form_snmpcache)">
							<option value="-1"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<?php
							if ($_REQUEST["host_id"] == -1) {
								$snmp_queries = db_fetch_assoc("SELECT DISTINCT
											snmp_query.id,
											snmp_query.name
											FROM (host_snmp_cache,snmp_query,host)
											WHERE host_snmp_cache.host_id=host.id
											AND host_snmp_cache.snmp_query_id=snmp_query.id
											ORDER by snmp_query.name");
							}else{
								$snmp_queries = db_fetch_assoc("SELECT DISTINCT
											snmp_query.id,
											snmp_query.name
											FROM (host_snmp_cache,snmp_query,host)
											WHERE host_snmp_cache.host_id=host.id
											AND host_snmp_cache.host_id='" . $_REQUEST["host_id"] . "'
											AND host_snmp_cache.snmp_query_id=snmp_query.id
											ORDER by snmp_query.name");
							}
							if (sizeof($snmp_queries) > 0) {
							foreach ($snmp_queries as $snmp_query) {
								print "<option value='" . $snmp_query["id"] . "'"; if ($_REQUEST["snmp_query_id"] == $snmp_query["id"]) { print " selected"; } print ">" . $snmp_query["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_snmp_cache'></div>
			</form>
		</td>
	</tr>
	<?php
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

	html_start_box("", "100%", $colors["header"], "0", "center", "");

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
	html_end_box();

	html_header(array(__("Details")));

	if (sizeof($snmp_cache) > 0) {
	foreach ($snmp_cache as $item) {
		form_alternate_row_color();
		?>
		<td>
			<?php print __("Host:");?> <?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["description"])) : $item["description"]);?>
			, <?php print __("SNMP Query:");?> <?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["name"])) : $item["name"]);?>
		</td>
		<?php
		form_end_row();
		form_alternate_row_color();
		?>
		<td>
			<?php print __("Index:");?> <?php print $item["snmp_index"];?>
			, <?php print __("Field Name:");?> <?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["field_name"])) : $item["field_name"]);?>
			, <?php print __("Field Value:");?> <?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["field_value"])) : $item["field_value"]);?>
		</td>
		<?php
		form_end_row();
		form_alternate_row_color();
		?>
		<td>
			<?php print __("OID:");?> <?php print (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["oid"])) : $item["oid"]);?>
		</td>
		<?php
		form_end_row();
	}
	}

	print $nav;
	print "</table>\n</form>\n";	# end form and table of html_header_sort_checkbox

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
		kill_session_var("sess_poller_cache_current_page");
		kill_session_var("sess_poller_cache_host_id");
		kill_session_var("sess_poller_cache_poller_action");
		kill_session_var("sess_poller_cache_filter");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["host_id"]);
		unset($_REQUEST["poller_action"]);
	}

	if ((!empty($_SESSION["sess_poller_cache_action"])) && (!empty($_REQUEST["poller_action"]))) {
		if ($_SESSION["sess_poller_cache_poller_action"] != $_REQUEST["poller_action"]) {
			$_REQUEST["page"] = 1;
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_poller_cache_current_page", "1");
	load_current_session_value("host_id", "sess_poller_cache_host_id", "-1");
	load_current_session_value("poller_action", "sess_poller_cache_poller_action", "-1");
	load_current_session_value("filter", "sess_poller_cache_filter", "");
	load_current_session_value("sort_column", "sess_poller_cache_sort_column", "data_template_data.name_cache");
	load_current_session_value("sort_direction", "sess_poller_cache_sort_direction", "ASC");

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

	html_start_box("<strong>" . __("Poller Cache Items") . "</strong>", "100%", $colors["header"], "3", "center", "", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_pollercache">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						<?php print __("Host:");?>&nbsp;
					</td>
					<td width="1">
						<select name="host_id" onChange="applyPItemFilterChange(document.form_pollercache)">
							<option value="-1"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>><?php print __("None");?></option>
							<?php
							$hosts = db_fetch_assoc("select id,description,hostname from host order by description");

							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='" . $host["id"] . "'"; if ($_REQUEST["host_id"] == $host["id"]) { print " selected"; } print ">" . $host["description"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<?php print __("Action:");?>&nbsp;
					</td>
					<td width="1">
						<select name="poller_action" onChange="applyPItemFilterChange(document.form_pollercache)">
							<option value="-1"<?php if ($_REQUEST['poller_action'] == '-1') {?> selected<?php }?>><?php print __("Any");?></option>
							<option value="0"<?php if ($_REQUEST['poller_action'] == '0') {?> selected<?php }?>><?php print __("SNMP");?></option>
							<option value="1"<?php if ($_REQUEST['poller_action'] == '1') {?> selected<?php }?>><?php print __("Script");?></option>
							<option value="2"<?php if ($_REQUEST['poller_action'] == '2') {?> selected<?php }?>><?php print __("Script Server");?></option>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_poller_cache'></div>
			</form>
		</td>
	</tr>
	<?php
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

	html_start_box("", "100%", $colors["header"], "0", "center", "");

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
	html_end_box();

	$display_text = array(
		"data_template_data.name_cache" => array(__("Data Source Name"), "ASC"),
		"" => array(__("Details"), "ASC"));

	html_header_sort($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($poller_cache) > 0) {
	foreach ($poller_cache as $item) {
		form_alternate_row_color();
			?>
			<td width="375">
				<a class="linkEditMain" href="<?php print htmlspecialchars("data_sources.php?action=ds_edit&id=" . $item["local_data_id"]);?>"><?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["name_cache"]);?></a>
			</td>

			<td>
			<?php
			if ($item["action"] == 0) {
				if ($item["snmp_version"] != 3) {
					$details =
						__("SNMP Version:") . " " . $item["snmp_version"] . ", " .
						__("Community:") . " " . $item["snmp_community"] . ", " .
						__("OID:") . " " . (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"])) : $item["arg1"]);
				}else{
					$details =
						__("SNMP Version:") . " " . $item["snmp_version"] . ", " .
						__("User:") . " " . $item["snmp_username"] . ", " .
						__("OID:") . " " . $item["arg1"];
				}
			}elseif ($item["action"] == 1) {
					$details = __("Script:") . " " . (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"])) : $item["arg1"]);
			}else{
					$details = __("Script Server:") . " " . (strlen(get_request_var_request("filter")) ? (eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $item["arg1"])) : $item["arg1"]);
			}

			print $details;
			?>
			</td>
		<?php
		form_end_row();
		form_alternate_row_color();
		?>
			<td>
			</td>
			<td>
				RRD: <?php print $item["rrd_path"];?>
			</td>
		<?php
		form_end_row();
	}
	}

	print $nav;
	print "</table>\n</form>\n";	# end form and table of html_header_sort_checkbox

}

function utilities() {
	global $colors;

	html_start_box("<strong>" . __("Cacti System Utilities") . "</strong>", "100%", $colors["header"], 0, "center", "");


	print "<tr><td>";
	html_header(array(__("Technical Support")), 2); ?>

	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_tech'><?php print __("Technical Support");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("Cacti technical support page.  Used by developers and technical support persons to assist with issues in Cacti.  Includes checks for common configuration issues.");?>
		</td>
	</tr>

	<?php
	print "</table></td></tr>";		/* end of html_header */
	print "<tr><td>";
	html_header(array(__("Log Administration")), 2);?>

	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_logfile'><?php print __("View Cacti Log File");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("The Cacti Log File stores statistic, error and other message depending on system settings.  This information can be used to identify problems with the poller and application.");?>
		</td>
	</tr>
	<tr class="rowAlternate2">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_user_log'><?php print __("View User Log");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("Allows Administrators to browse the user log.  Administrators can filter and export the log as well.");?>
		</td>
	</tr>

	<?php
	print "</table></td></tr>";		/* end of html_header */
	print "<tr><td>";
	html_header(array(__("Poller Cache Administration")), 2); ?>

	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_poller_cache'><?php print __("View Poller Cache");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("This is the data that is being passed to the poller each time it runs. This data is then in turn executed/interpreted and the results are fed into the rrd files for graphing or the database for display.");?>
		</td>
	</tr>
	<tr class="rowAlternate2">
		<td class="textAreaNotes">
			<a href='utilities.php?action=view_snmp_cache'><?php print __("View SNMP Cache");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("The SNMP cache stores information gathered from SNMP queries. It is used by cacti to determine the OID to use when gathering information from an SNMP-enabled host.");?>
		</td>
	</tr>
	<tr class="rowAlternate1">
		<td class="textAreaNotes">
			<a href='utilities.php?action=clear_poller_cache'><?php print __("Rebuild Poller Cache");?></a>
		</td>
		<td class="textAreaNotes">
			<?php print __("The poller cache will be cleared and re-generated if you select this option. Sometimes host/data source data can get out of sync with the cache in which case it makes sense to clear the cache and start over.");?>
		</td>
	</tr>

	<?php

	print "</table></td></tr>";		/* end of html_header */

	api_plugin_hook('utilities_list');

	html_end_box();
}
