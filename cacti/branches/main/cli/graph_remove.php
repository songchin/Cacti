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
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");
include_once(CACTI_BASE_PATH."/lib/api_graph.php");
#include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$displayGraphs  = FALSE;
	$quietMode		= FALSE;
	$force			= FALSE;
	$hostId         = 0;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--device-id":
			$hostId = $value;

			break;
		case "--graph-id":
			$graphId = $value;

			break;
		case "--force":
			$force = TRUE;

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		case "--list-graphs":
			$displayGraphs = TRUE;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help($me);
			exit(1);
		}
	}



	/*
	 * handle display options
	 */
	if ($displayGraphs) {
		if (!isset($hostId)) {
			echo __("ERROR: You must supply a device-id before you can list its graphs") . "\n";
			echo __("Try --list-devices") . "\n";
			exit(1);
		}

		displayHostGraphs($hostId, $quietMode);
		exit(0);
	}


	/* Verify the graph's existance */
	$graph_exists = db_fetch_cell("SELECT id FROM graph_local WHERE id=$graphId");
	if (empty($graph_exists)) {
		printf(__("ERROR: Unknown Graph ID (%d)\n"), $graphId);
		echo __("Try --list-graphs") . "\n";
		exit(1);
	}


	/*
	 * get the data sources and graphs to act on
	 * (code stolen from graphs.php)
	 */
	if ($force) {
		/* delete all data sources referenced by this graph */
		$data_sources = db_fetch_assoc("SELECT
			data_template_data.local_data_id
			FROM (data_template_rrd,data_template_data,graph_templates_item)
			WHERE graph_templates_item.task_item_id=data_template_rrd.id
			AND data_template_rrd.local_data_id=data_template_data.local_data_id
			AND graph_templates_item.local_graph_id=" . $graphId . "
			AND data_template_data.local_data_id > 0");

		echo __("Removing graph and all resources for graph id ") . $graphId;
		if (sizeof($data_sources) > 0) {
			foreach ($data_sources as $data_source) {
				api_data_source_remove($data_source["local_data_id"]);
			}
		}
	} else {
		echo __("Removing graph but keeping resources for graph id ") . $graphId;
	}

	api_graph_remove($graphId);

	if (is_error_message()) {
		echo __(". ERROR: Failed to remove this graph") . "\n";
		exit(1);
	} else {
		printf(__(". Success - removed graph-id: (%d)"), $graphId);
		exit(0);
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Remove Graph Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to remove a graph from Cacti") . "\n\n";
	echo __("usage: ") . $me . " --graph-id=[ID]\n\n";
	echo __("Required:") . "\n";
	echo "   --graph-id      " . __("the numerical id of the graph") . "\n\n";
	echo __("Optional:") . "\n";
	echo "   --force         " . __("delete all related data sources") . "\n\n";
	echo __("List Options:") . "\n";
	echo "   --list-graphs --device-id " . __("list available graphs for a specific device") . "\n";
	echo "   --quiet         " . __("batch mode value return") . "\n\n";
}

?>
