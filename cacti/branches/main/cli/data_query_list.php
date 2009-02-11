#!/usr/bin/php -q
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

/* We are not talking to the browser */
$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	$quietMode	= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help();
			exit(0);
		case "--quiet":
			$quietMode = TRUE;
			break;
		default:
			echo "ERROR: Invalid Argument: ($arg)\n\n";
			display_help();
			exit(1);
		}
	}

	/* list options, recognizing $quietMode */
	$data_queries = getSNMPQueries();
	displaySNMPQueries($data_queries, $quietMode);
	exit(0);

}else{
	$data_queries = getSNMPQueries();
	displaySNMPQueries($data_queries, false);
	exit(0);
}

function display_help() {
	echo "List Data Query Script 1.0, Copyright 2009 - The Cacti Group\n\n";
	echo "A simple command line utility to list data queries in Cacti\n\n";
	echo "usage: data_query_list.php [--host-id=] [--data-query-id=] [--reindex-method=] [--quiet]\n\n";
	echo "Optional:\n";
	echo "    --host-id         the numerical ID of the host\n";
	echo "    --data-query-id   the numerical ID of the data_query to be added\n";
	echo "    --reindex-method  the reindex method to be used for that data query\n";
	echo "                      0|None   = no reindexing\n";
	echo "                      1|Uptime = Uptime goes Backwards\n";
	echo "                      2|Index  = Index Count Changed\n";
	echo "                      3|Fields = Verify all Fields\n";
	echo "    --quiet - batch mode value return\n\n";
}

?>