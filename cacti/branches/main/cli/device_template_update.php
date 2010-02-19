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

include(dirname(__FILE__)."/../include/global.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
include_once(CACTI_BASE_PATH."/lib/snmp.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

/* utility requires input parameters */
if (sizeof($parms) == 0) {
	print "ERROR: You must supply input parameters\n\n";
	display_help($me);
	exit;
}

$debug    = FALSE;
$template = "";
$deviceid   = "";

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "--device-template":
		$template = $value;
		break;
	case "--device-id":
		$device_id = $value;
		break;
	case "--list-device-templates":
		displayHostTemplates(getHostTemplates());
		exit(0);
	case "-d":
		$debug = TRUE;
		break;
	case "-h":
		display_help($me);
		exit;
	case "-v":
		display_help($me);
		exit;
	case "--version":
		display_help($me);
		exit;
	case "--help":
		display_help($me);
		exit;
	default:
		print "ERROR: Invalid Parameter " . $parameter . "\n\n";
		display_help($me);
		exit;
	}
}

/* determine the devices to reindex */
if (strtolower($device_id) == "all") {
	$sql_where = "";
}else if (is_numeric($device_id)) {
	$sql_where = " WHERE device_id='$device_id'";
}else{
	print "ERROR: You must specify either a --device-id or 'all' to proceed.\n\n";
	display_help($me);
	exit;
}

/* determine data queries to rerun */
if (is_numeric($template)) {
	$sql_where .= (strlen($sql_where) ? " AND device_template_id=$template": "WHERE device_template_id=$template");
}else{
	print "ERROR: You must specify a Device Template to proceed.\n\n";
	display_help($me);
	exit;
}

/* verify that the device template is accurate */
if (db_fetch_cell("SELECT id FROM device_template WHERE id=$template") > 0) {
	$devices = db_fetch_assoc("SELECT * FROM device $sql_where");

	if (sizeof($devices)) {
	foreach($devices as $device) {
		echo __("NOTE: Updating Device '") . $device["description"] . "'\n";
		$snmp_queries = db_fetch_assoc("SELECT snmp_query_id
			FROM device_template_snmp_query
			WHERE device_template_id=" . $device["device_template_id"]);

		if (sizeof($snmp_queries) > 0) {
			echo __("NOTE: Updating Data Queries. There were %d found)", sizeof($snmp_queries)) . "\n";
			foreach ($snmp_queries as $snmp_query) {
				echo __("NOTE: Updating Data Query ID ", $snmp_query["snmp_query_id"]) . "'\n";
				db_execute("REPLACE INTO device_snmp_query (device_id,snmp_query_id,reindex_method)
					VALUES (" . $device["id"] . ", " . $snmp_query["snmp_query_id"] . "," . DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME . ")");

				/* recache snmp data */
				run_data_query($device["id"], $snmp_query["snmp_query_id"]);
			}
		}

		$graph_templates = db_fetch_assoc("SELECT graph_template_id FROM device_template_graph WHERE device_template_id=" . $device["device_template_id"]);

		if (sizeof($graph_templates) > 0) {
			echo __("NOTE: Updating Graph Templates. There were %d found", sizeof($graph_templates)) . "\n";

			foreach ($graph_templates as $graph_template) {
				db_execute("REPLACE INTO device_graph (device_id, graph_template_id) VALUES (" . $device["id"] . ", " . $graph_template["graph_template_id"] . ")");
				api_plugin_hook_function('add_graph_template_to_device', array("device_id" => $device["id"], "graph_template_id" => $graph_template["graph_template_id"]));
			}
		}
	}
	}
}else{
	echo __("ERROR: The selected Device Template does not exist, try --list-device-templates") . "\n\n";
	exit(1);
}


/*	display_help - displays the usage of the function */
function display_help ($me) {
	echo __("Cacti Device Template Update Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("usage: ") . $me . " -device-id=[device-id|All] [--device-template=[ID]] [-d] [-h] [--help] [-v] [--version]\n\n";
	echo "   --device-id        " . __("the numerical ID of the device") . "\n";
	echo "   --device-template  " . __("The Device Template to Refresh") . "\n\n";
	echo __("Optional:") . "\n";
	echo "   -d                      " . __("Display verbose output during execution") . "\n";
	echo "   -v --version            " . __("Display this help message") . "\n";
	echo "   -h --help               " . __("Display this help message") . "\n";
	echo __("List Options:") . "\n\n";
	echo "   --list-device-templates " . __("Lists all available Device Templates") . "\n\n";
}

function debug($message) {
	global $debug;

	if ($debug) {
		print("DEBUG: " . $message . "\n");
	}
}
