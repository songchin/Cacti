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

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

include(dirname(__FILE__) . "/../include/global.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

$debug   = FALSE;
$host_id = 0;

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "-d":
	case "--debug":
		$debug = TRUE;
		break;
	case "--host-id":
		$host_id = trim($value);

		if (!is_numeric($host_id)) {
			echo __("ERROR: You must supply a valid host-id to run this script!\n");
			exit(1);
		}

		break;
	case "-h":
	case "-v":
	case "--version":
	case "--help":
		display_help($me);

		exit;
	default:
		printf(__("ERROR: Invalid Parameter %s\n\n"), $parameter);
		display_help($me);
		exit;
	}
}

/* obtain timeout settings */
$max_execution = ini_get("max_execution_time");

/* set new timeout */
ini_set("max_execution_time", "0");

/* get the data_local Id's for the poller cache */
if ($host_id > 0) {
	$poller_data  = db_fetch_assoc("SELECT id FROM data_local WHERE host_id=$host_id");
} else {
	$poller_data  = db_fetch_assoc("SELECT id FROM data_local");
}

/* initialize some variables */
$current_ds = 1;
$total_ds   = sizeof($poller_data);

/* setting local_data_ids to an empty array saves time during updates */
$local_data_ids = array();
$poller_items   = array();

/* issue warnings and start message if applicable */
echo __("WARNING: Do not interrupt this script.  Rebuilding the Poller Cache can take quite some time") . "\n";
debug("There are '" . sizeof($poller_data) . "' data source elements to update.");

/* start rebuilding the poller cache */
if (sizeof($poller_data) > 0) {
	foreach ($poller_data as $data) {
		if (!$debug) print ".";
		$poller_items = array_merge($poller_items, update_poller_cache($data["id"]));

		debug("Data Source Item '$current_ds' of '$total_ds' updated");
		$current_ds++;
	}

	poller_update_poller_cache_from_buffer($local_data_ids, $poller_items);
}
if (!$debug) print "\n";

/* poller cache rebuilt, restore runtime parameters */
ini_set("max_execution_time", $max_execution);

/*	display_help - displays the usage of the function */
function display_help($me) {
	echo __("Cacti Rebuild Poller Cache Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("usage: ") . $me . " [--host-id=ID] [-d | --debug] [-h | --help | -v | --version]\n\n";
	echo "   -d            " . __("Display verbose output during execution") . "\n";
	echo "   -v --version  " . __("Display this help message") . "\n";
	echo "   -h --help     " . __("Display this help message") . "\n";
}

function debug($message) {
	global $debug;

	if ($debug) {
		print("DEBUG: " . $message . "") . "\n";
	}
}
