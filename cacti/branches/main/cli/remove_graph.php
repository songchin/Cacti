#!/usr/bin/php -q
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

/* We are not talking to the browser */
$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");
include_once(CACTI_BASE_PATH."/lib/api_graph.php");
#include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	$displayHosts   = FALSE;
	$displayGraphs  = FALSE;
	$quietMode		= FALSE;
	$hosts			= getHosts();
	$force			= FALSE;
	$hostId         = 0;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--host-id":
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
			display_help();
			exit(0);
		case "--list-hosts":
			$displayHosts = TRUE;

			break;
		case "--list-graphs":
			$displayGraphs = TRUE;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		default:
			echo "ERROR: Invalid Argument: ($arg)\n\n";
			display_help();
			exit(1);
		}
	}



	/* 
	 * handle display options 
	 */
	if ($displayHosts) {
		displayHosts($hosts, $quietMode);
		exit(0);
	}

	if ($displayGraphs) {
		if (!isset($hostId)) {
			echo "ERROR: You must supply a host_id before you can list its graphs\n";
			echo "Try --list-hosts\n";
			exit(1);
		}

		displayHostGraphs($hostId, $quietMode);
		exit(0);
	}


	/* Verify the graph's existance */
	$graph_exists = db_fetch_cell("SELECT id FROM graph_local WHERE id=$graphId");
	if (empty($graph_exists)) {
		echo "ERROR: Unknown Graph ID ($graphId)\n";
		echo "Try --list-graphs\n";
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

		echo "Removing graph and all resources for graph id " . $graphId;
		if (sizeof($data_sources) > 0) {
			foreach ($data_sources as $data_source) {
				api_data_source_remove($data_source["local_data_id"]);
			}
		}
	} else {
		echo "Removing graph but keeping resources for graph id " . $graphId;
	}

	api_graph_remove($graphId);

	if (is_error_message()) {
		echo ". ERROR: Failed to remove this graph\n";
		exit(1);
	} else {
		echo ". Success - removed graph-id: ($graphId)\n";
		exit(0);
	}
}else{
	display_help();
	exit(0);
}

function display_help() {
	echo "Remove Graph Script 1.0, Copyright 2008 - The Cacti Group\n\n";
	echo "A simple command line utility to remove a graph from Cacti\n\n";
	echo "usage: remove_graph.php --graph-id=[ID]\n\n";
	echo "Required:\n";
	echo "    --graph-id                the numerical id of the graph\n\n";
	echo "Optional:\n";
	echo "    --force                   delete all related data sources\n\n";
	echo "List Options:\n";
	echo "    --list-hosts              list available hosts\n";
	echo "    --list-graphs --host-id   list available graphs for a specific host\n";
	echo "    --quiet                   batch mode value return\n\n";
}

?>
