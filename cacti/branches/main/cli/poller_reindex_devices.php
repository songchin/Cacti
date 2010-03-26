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
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

ini_set("max_execution_time", "0");

$no_http_headers = true;

include(dirname(__FILE__) . "/../include/global.php");
include_once(CACTI_BASE_PATH . "/lib/snmp.php");
include_once(CACTI_BASE_PATH . "/lib/data_query.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

/* utility requires input parameters */
if (sizeof($parms) == 0) {
	echo __("ERROR: You must supply input parameters") . "\n\n";
	display_help($me);
	exit;
}

$debug    = FALSE;
$device_id	= "";
$query_id	= "All";		/* just to mimic the old behaviour */
$device_descr	= "";

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "-id":
	case "--id":
		$device_id = $value;
		break;
	case "-qid":
	case "--qid":
		$query_id = $value;
		break;
	case "-device-descr":
	case "--device-descr":
		$device_descr = $value;
		break;
	case "-d":
	case "--debug":
		$debug = TRUE;
		break;
	case "-h":
	case "-v":
	case "--version":
	case "--help":
		display_help($me);
		exit;
	default:
		echo __("ERROR: Invalid Parameter ") . $parameter . "\n\n";
		display_help($me);
		exit;
	}
}

/* determine the devices to reindex */
if ($device_id == "All") {
	$sql_where = "";
}else if (is_numeric($device_id)) {
	$sql_where = " WHERE device_id = '$device_id'";
}else{
	echo __("ERROR: You must specify either a device-id or 'All' to proceed.") . "\n";
	display_help($me);
	exit;
}

/* determine data queries to rerun */
if ($query_id != "") {
	$sql_where .= (strlen($sql_where) ? " AND snmp_query_id=$query_id": " WHERE snmp_query_id=$query_id");
}

/* allow for additional filtering on device description */
if (strlen($device_descr)) {
	$sql_where .= (strlen($sql_where) ? " AND device.description like '%%" . $device_descr . "%%' AND device.id=device_snmp_query.device_id" : " WHERE device.description like '%%" . $device_descr . "%%' AND device.id=device_snmp_query.device_id");
	$data_queries = db_fetch_assoc("SELECT device_id, snmp_query_id FROM device_snmp_query,device" . $sql_where);
} else {
	$data_queries = db_fetch_assoc("SELECT device_id, snmp_query_id FROM device_snmp_query" . $sql_where);
}

/* issue warnings and start message if applicable */
echo __("WARNING: Do not interrupt this script.  Reindexing can take quite some time") . "\n";
debug("There are '" . sizeof($data_queries) . "' data queries to run");

$i = 1;
if (sizeof($data_queries)) {
	foreach ($data_queries as $data_query) {
		if (!$debug) print ".";
		debug("Data query number '" . $i . "' device: '".$data_query["device_id"]."' SNMP Query Id: '".$data_query["snmp_query_id"]."' starting");
		run_data_query($data_query["device_id"], $data_query["snmp_query_id"]);
		debug("Data query number '" . $i . "' device: '".$data_query["device_id"]."' SNMP Query Id: '".$data_query["snmp_query_id"]."' ending");
		$i++;
	}
}

/*	display_help - displays the usage of the function */
function display_help($me) {
	echo "Cacti Reindex Device Script 1.2" . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("usage: ") . $me . " --id=[device-id|All] [--qid=[ID|All]] [--device-descr=[description]]\n";
	echo "              [-d] [-h] [--help] [-v] [--version]\n\n";
	echo "   --id          " . __("The device-id to have data queries reindexed or 'All' to reindex all devices") . "\n";
	echo "   --qid         " . __("Only index on a specific data query id; defaults to 'All'") . "\n";
	echo "   --device-descr  " . __("The device description to filter by (SQL filters acknowledged)") . "\n";
	echo "   --debug       " . __("Display verbose output during execution") . "\n";
	echo "   -v --version  " . __("Display this help message") . "\n";
	echo "   -h --help     " . __("Display this help message") . "\n";
}

function debug($message) {
	global $debug;

	if ($debug) {
		print("DEBUG: " . $message . "\n");
	}
}
