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
include_once($config["base_path"] . "/lib/utility.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

$debug = FALSE;

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "-d":
		$debug = TRUE;
		break;
	case "-h":
		display_help();
		exit;
	case "-v":
		display_help();
		exit;
	case "--version":
		display_help();
		exit;
	case "--help":
		display_help();
		exit;
	default:
		print "ERROR: Invalid Parameter " . $parameter . "\n\n";
		display_help();
		exit;
	}
}

/* obtain timeout settings */
$max_execution = ini_get("max_execution_time");
$max_memory = ini_get("memory_limit");

/* set new timeout and memory settings */
ini_set("max_execution_time", "0");
ini_set("memory_limit", "64M");

/* clear the poller cache first */
db_execute("truncate table poller_item");

/* get the data_local Id's for the poller cache */
$poller_data = db_fetch_assoc("select id from data_local");

/* initialize some variables */
$current_ds = 1;
$total_ds = sizeof($poller_data);

/* issue warnings and start message if applicable */
print "WARNING: Do not interrupt this script.  Rebuilding the Poller Cache can take quite some time\n";
debug("There are '" . sizeof($poller_data) . "' data source elements to update.");

/* start rebuilding the poller cache */
if (sizeof($poller_data) > 0) {
	foreach ($poller_data as $data) {
		if (!$debug) print ".";
		update_poller_cache($data["id"], true);
		debug("Data Source Item '$current_ds' of '$total_ds' updated");
		$current_ds++;
	}
}
if (!$debug) print "\n";

/* poller cache rebuilt, restore runtime parameters */
ini_set("max_execution_time", $max_execution);
ini_set("memory_limit", $max_memory);

/*	display_help - displays the usage of the function */
function display_help () {
	print "Cacti Rebuild Poller Cache Script 1.0, Copyright 2004-2009 - The Cacti Group\n\n";
	print "usage: rebuild_poller_cache.php [-d] [-h] [--help] [-v] [--version]\n\n";
	print "-d            - Display verbose output during execution\n";
	print "-v --version  - Display this help message\n";
	print "-h --help     - Display this help message\n";
}

function debug($message) {
	global $debug;

	if ($debug) {
		print("DEBUG: " . $message . "\n");
	}
}


?>