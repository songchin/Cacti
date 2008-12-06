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
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	$force			= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--host-id":
			$host_id = $value;
	
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
		default:
			echo "ERROR: Invalid Argument: ($arg)\n\n";
			display_help();
			exit(1);
		}
	}


	/* 
	 * verify valid host id and get a name for it
	 */
	$host_name = db_fetch_cell("SELECT hostname FROM host WHERE id = " . $host_id);
	if (!isset($host_name)) {
		echo "ERROR: Unknown Host Id ($host_id)\n";
		echo "Try php -q device_list.php\n";
		exit(1);
	}
	
	
	/*
	 * get the data sources and graphs to act on 
	 * (code stolen from host.php)
	 */
	$data_sources_to_act_on = array();
	$graphs_to_act_on       = array();

	$data_sources = db_fetch_assoc("select
		data_local.id as local_data_id
		from data_local
		where data_local.host_id =" . $host_id);

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			$data_sources_to_act_on[] = $data_source["local_data_id"];
		}
	}

	if ($force) {
		$graphs = db_fetch_assoc("select
			graph_local.id as local_graph_id
			from graph_local
			where graph_local.host_id =" . $host_id);

		if (sizeof($graphs) > 0) {
			foreach ($graphs as $graph) {
				$graphs_to_act_on[] = $graph["local_graph_id"];
			}
		}
	}

	if ($force) { 
		/* delete graphs/data sources tied to this device */
		api_data_source_remove_multi($data_sources_to_act_on);
		api_graph_remove_multi($graphs_to_act_on);
		echo "Removing host and all resources for host id " . $host_id;
	} else { 
		/* leave graphs and data_sources in place, but disable the data sources */
		api_data_source_disable_multi($data_sources_to_act_on);
		echo "Removing host but keeping resources for host id " . $host_id;
	}

	api_device_remove($host_id);

	if (is_error_message()) {
		echo ". ERROR: Failed to remove this device\n";
		exit(1);
	} else {
		echo ". Success - removed device-id: ($host_id)\n";
		exit(0);
	}
}else{
	display_help();
	exit(0);
}

function display_help() {
	echo "Remove Device Script 1.0, Copyright 2008 - The Cacti Group\n\n";
	echo "A simple command line utility to remove a device from Cacti\n\n";
	echo "usage: remove_device.php --host-id=[ID]\n\n";
	echo "Required:\n";
	echo "    --host-id		the numerical id of the host\n\n";
	echo "Optional:\n";
	echo "    --force		delete all graphs, graph permissions, host permissions and data sources\n\n";
}

?>
