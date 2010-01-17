#!/usr/bin/php -q
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

/* do NOT run this script through a web browser */
if (!isset ($_SERVER["argv"][0]) || isset ($_SERVER['REQUEST_METHOD']) || isset ($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

ini_set("max_execution_time", "0");

$no_http_headers = true;

include (dirname(__FILE__) . "/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

/* utility requires input parameters */
if (sizeof($parms) == 0) {
	echo __("ERROR: You must supply input parameters") . "\n\n";
	display_help($me);
	exit;
}

$debug = FALSE;
$device_id = "";
$filter = "";

foreach ($parms as $parameter) {
	@ list ($arg, $value) = @ explode("=", $parameter);

	switch ($arg) {
		case "-id" :
		case "--device-id" :
			$device_id = $value;
			break;
		case "-s" :
		case "--filter" :
			$filter = $value;
			break;
		case "-d" :
		case "--debug" :
			$debug = TRUE;
			break;
		case "-h" :
		case "--help" :
		case "-v" :
		case "--verbose" :
		case "--version" :
			display_help($me);
			exit;
		default :
			printf(__("ERROR: Invalid Parameter %s\n\n"), $parameter);
			display_help($me);
			exit;
	}
}

/* form the 'where' clause for our main sql query */
if (strlen($filter)) {
	$sql_where = "AND (data_template_data.name_cache like '%%" . $filter . "%%'" .
	" OR data_template_data.local_data_id like '%%" . $filter . "%%'" .
	" OR data_template.name like '%%" . $filter . "%%'" .
	" OR data_input.name like '%%" . $filter . "%%')";
} else {
	$sql_where = "";
}

if (strtolower($device_id) == "all") {
	/* Act on all graphs */
}
elseif (substr_count($device_id, "|")) {
	$devices = explode("|", $device_id);
	$device_str = "";

	foreach ($devices as $device) {
		if (strlen($device_str)) {
			$device_str .= ", '" . $device . "'";
		} else {
			$device_str .= "'" . $device . "'";
		}
	}

	$sql_where .= " AND data_local.device_id IN ($device_str)";
}
elseif ($device_id == "0") {
	$sql_where .= " AND data_local.device_id=0";
}
elseif (!empty ($device_id)) {
	$sql_where .= " AND data_local.device_id=" . $device_id;
} else {
	echo __("ERROR: You must specify either a device-id or 'All' to proceed.") . "\n";
	display_help($me);
	exit;
}

$data_source_list = db_fetch_assoc("SELECT
		data_template_data.local_data_id,
		data_template_data.name_cache,
		data_template_data.active,
		data_input.name as data_input_name,
		data_template.name as data_template_name,
		data_local.device_id
		FROM (data_local,data_template_data)
		LEFT JOIN data_input
		ON (data_input.id=data_template_data.data_input_id)
		LEFT JOIN data_template
		ON (data_local.data_template_id=data_template.id)
		WHERE data_local.id=data_template_data.local_data_id
		$sql_where");

/* issue warnings and start message if applicable */
echo __("WARNING: Do not interrupt this script.  Interrupting during rename can cause issues") . "\n";
debug("There are '" . sizeof($data_source_list) . "' Data Sources to rename");

$i = 1;
foreach ($data_source_list as $data_source) {
	if (!$debug)
		print ".";
	debug("Data Source Name '" . $data_source["name_cache"] . "' starting");
	api_reapply_suggested_data_source_title($data_source["local_data_id"]);
	update_data_source_title_cache($data_source["local_data_id"]);
	debug("Data Source Rename Done for Data Source '" . addslashes(get_data_source_title($data_source["local_data_id"])) . "'");
	$i++;
}

/*	display_help - displays the usage of the function */
function display_help($me) {
	echo __("Cacti Reapply Data Sources Names Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("usage: ") . $me . " --id=[device_id|All][device_id1|device_id2|...]\n";
	echo "       [--filter=[search_string] [--debug] [-h] [--help] [-v] [--version]\n\n";
	echo "   --id          " . __("The device_id or 'All' or a pipe delimited list of device_id's") . "\n";
	echo "   --filter      " . __("A data template name or data source title to search for") . "\n";
	echo "   --debug       " . __("Display verbose output during execution") . "\n";
	echo "   -v --version  " . __("Display this help message") . "\n";
	echo "   -h --help     " . __("Display this help message") . "\n";
}

function debug($message) {
	global $debug;

	if ($debug) {
		print ("DEBUG: " . $message . "") . "\n";
	}
}
