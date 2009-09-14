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
$me = array_shift($parms);

if (sizeof($parms)) {
	unset($host_id);
	unset($data_query_id);
	unset($reindex_method);

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--device-id":
			$host_id = trim($value);
			if (!is_numeric($host_id)) {
				echo __("ERROR: You must supply a valid device-id to run this script!") . "\n";
				exit(1);
			}

			break;
		case "--data-query-id":
			$data_query_id = $value;
			if (!is_numeric($data_query_id)) {
				echo __("ERROR: You must supply a numeric data-query-id for all devices!") . "\n";
				exit(1);
			}

			break;
		case "--reindex-method":
			if (is_numeric($value) &&
				($value >= DATA_QUERY_AUTOINDEX_NONE) &&
				($value <= DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION)) {
				$reindex_method = $value;
			} else {
				switch (strtolower($value)) {
					case "none":
						$reindex_method = DATA_QUERY_AUTOINDEX_NONE;
						break;
					case "uptime":
						$reindex_method = DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;
						break;
					case "index":
						$reindex_method = DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE;
						break;
					case "fields":
						$reindex_method = DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION;
						break;
					default:
						echo __("ERROR: You must supply a valid reindex method for all devices!") . "\n";
						exit(1);
				}
			}
			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg) ;
			display_help($me);
			exit(1);
		}
	}

	/*
	 * verify required parameters
	 * for update / insert options
	 */
	if (!isset($host_id)) {
		echo __("ERROR: You must supply a valid device-id for all devices!") . "\n";
		exit(1);
	}

	if (!isset($data_query_id)) {
		echo __("ERROR: You must supply a valid data-query-id for all devices!") . "\n";
		exit(1);
	}

	if (!isset($reindex_method)) {
		echo __("ERROR: You must supply a valid reindex-method for all devices!") . "\n";
		exit(1);
	}


	/*
	 * verify valid host id and get a name for it
	 */
	$host_name = db_fetch_cell("SELECT hostname FROM host WHERE id = " . $host_id);
	if (!isset($host_name)) {
		printf(__("ERROR: Unknown Host Id (%d)\n"), $host_id);
		exit(1);
	}

	/*
	 * verify valid data query and get a name for it
	 */
	$data_query_name = db_fetch_cell("SELECT name FROM snmp_query WHERE id = " . $data_query_id);
	if (!isset($data_query_name)) {
		printf(__("ERROR: Unknown Data Query Id (%d)\n"), $data_query_id);
		exit(1);
	}

	/*
	 * Now, add the data query and run it once to get the cache filled
	 */
	$exists_already = db_fetch_cell("SELECT host_id FROM host_snmp_query WHERE host_id=$host_id AND snmp_query_id=$data_query_id AND reindex_method=$reindex_method");
	if ((isset($exists_already)) &&
		($exists_already > 0)) {
		       printf(__("ERROR: Data Query is already associated for device: (%1d: %2s) data query (%3d: %4s) reindex method (%5s: %6s)\n"), $host_id, $host_name, $data_query_id, $data_query_name, $reindex_method, $reindex_types[$reindex_method]);
		exit(1);
	}else{
		db_execute("REPLACE INTO host_snmp_query (host_id,snmp_query_id,reindex_method) " .
				   "VALUES (". $host_id . ","
							 . $data_query_id . ","
							 . $reindex_method . "
							)");
		/* recache snmp data */
		run_data_query($host_id, $data_query_id);
	}

	if (is_error_message()) {
		printf(__("ERROR: Failed to add this data query for device (%1d: %2s) data query (%3d: %4s) reindex method (%5s: %6s)\n"), $host_id, $host_name, $data_query_id, $data_query_name, $reindex_method, $reindex_types[$reindex_method]);
		exit(1);
	} else {
		printf(__("Success - Device (%1d: %2s) data query (%3d: %4s) reindex method (%5s: %6s)\n"), $host_id, $host_name, $data_query_id, $data_query_name, $reindex_method, $reindex_types[$reindex_method]);
		exit(0);
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Data Query Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add a data query to an existing device in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --device-id=[ID] --data-query-id=[dq_id] --reindex-method=[method] [--quiet]\n\n";
	echo __("Required:") . "\n";
	echo "   --device-id      " . __("the numerical ID of the device") . "\n";
	echo "   --data-query-id  " . __("the numerical ID of the data_query to be added") . "\n";
	echo "   --reindex-method " . __("the reindex method to be used for that data query") . "\n";
	echo "          0|None    " . __("no reindexing") . "\n";
	echo "          1|Uptime  " . __("Uptime goes Backwards") . "\n";
	echo "          2|Index   " . __("Index Count Changed") . "\n";
	echo "          3|Fields  " . __("Verify all Fields") . "\n";
	echo __("If the data query was already associated, it will be reindexed.") . "\n\n";
}
