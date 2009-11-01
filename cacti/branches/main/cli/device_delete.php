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
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$force			= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			# to select the devices to act on, at least one parameter must be given
			case "--device-id":		$device["id"] 					= trim($value);	break;

			case "--force":			$force 							= TRUE;			break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}


	/*
	 * verify valid device id and get a name for it
	 */
	$device_name = db_fetch_cell("SELECT hostname FROM host WHERE id = " . $device_id);
	if (!isset($device_name)) {
		echo __("ERROR: Unknown Device ID (%s)", $device_id) . "\n";
		echo __("Try php -q device_list.php") . "\n";
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
		where data_local.host_id =" . $device_id);

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			$data_sources_to_act_on[] = $data_source["local_data_id"];
		}
	}

	if ($force) {
		$graphs = db_fetch_assoc("select
			graph_local.id as local_graph_id
			from graph_local
			where graph_local.host_id =" . $device_id);

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
		echo __("Removing device and all resources for host_id ") . $device_id . "\n";
	} else {
		/* leave graphs and data_sources in place, but disable the data sources */
		api_data_source_disable_multi($data_sources_to_act_on);
		echo __("Removing device but keeping resources for host_id ") . $device_id . "\n";
	}

	api_device_remove($device_id);

	if (is_error_message()) {
		echo __(". ERROR: Failed to remove this device") . "\n";
		exit(1);
	} else {
		echo __(". Success - removed device-id: ") . ($device_id) . "\n";
		exit(0);
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Remove Device Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to remove a device from Cacti") . "\n\n";
	echo __("usage: ") . $me . " --device-id=[ID]\n\n";
	echo __("Required:") . "\n";
	echo "   --device-id   " . __("the numerical ID of the device") . "\n";
	echo __("Optional:") . "\n";
	echo "   --force       " . __("delete all graphs, graph permissions, device permissions and data sources") . "\n\n";
}
