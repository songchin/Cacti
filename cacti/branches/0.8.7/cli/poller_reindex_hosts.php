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

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

ini_set("max_execution_time", "0");
ini_set("memory_limit", "64M");

$no_http_headers = true;

include(dirname(__FILE__) . "/../include/global.php");
include_once($config["base_path"] . "/lib/snmp.php");
include_once($config["base_path"] . "/lib/data_query.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

/* utility requires input parameters */
if (sizeof($parms) == 0) {
	print "ERROR: You must supply input parameters\n\n";
	display_help();
	exit;
}

$debug    = FALSE;
$query_id = "";

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "-id":
		$host_id = $value;
		break;
	case "-qid":
		$query_id = $value;
		break;
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

/* determine the hosts to reindex */
if (strtolower($host_id) == "all") {
	$sql_where = "";
}else if (is_numeric($host_id)) {
	$sql_where = " WHERE host_id = '$host_id'";
}else{
	print "ERROR: You must specify either a host_id or 'All' to proceed.\n";
	display_help();
	exit;
}

/* determine data queries to rerun */
if (strtolower($query_id) == "all") {
	/* do nothing */
}else if (is_numeric($query_id)) {
	$sql_where .= (strlen($sql_where) ? " AND snmp_query_id=$query_id": "WHERE snmp_query_id=$query_id");
}else{
	print "ERROR: You must specify either a query_id or 'all' to proceed.\n";
	display_help();
	exit;
}

$data_queries = db_fetch_assoc("SELECT host_id, snmp_query_id FROM host_snmp_query" . $sql_where);

/* issue warnings and start message if applicable */
print "WARNING: Do not interrupt this script.  Reindexing can take quite some time\n";
debug("There are '" . sizeof($data_queries) . "' data queries to run");

$i = 1;
foreach ($data_queries as $data_query) {
	if (!$debug) print ".";
	debug("Data query number '" . $i . "' starting");
	run_data_query($data_query["host_id"], $data_query["snmp_query_id"]);
	debug("Data query number '" . $i . "' ending");
	$i++;
}

/*	display_help - displays the usage of the function */
function display_help () {
print "Cacti Reindex Host Script 1.1, Copyright 2007-2008 - The Cacti Group\n\n";
	print "usage: poller_reindex_hosts.php -id=[host_id|All] [--qid=[ID]] [-d] [-h] [--help] [-v] [--version]\n\n";
	print "-id=host_id   - The host_id to have data queries reindexed or 'All' to reindex all hosts\n";
	print "-qid=query_id - Only index on a specific data query id\n";
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
