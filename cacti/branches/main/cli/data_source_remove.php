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
if (!isset ($_SERVER["argv"][0]) || isset ($_SERVER['REQUEST_METHOD']) || isset ($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;

include (dirname(__FILE__) . "/../include/global.php");
include_once (CACTI_BASE_PATH . "/lib/api_automation_tools.php");
include_once (CACTI_BASE_PATH . "/lib/api_data_source.php");
include_once (CACTI_BASE_PATH . "/lib/api_graph.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$dry_run = "";
	$host_id = 0;

	foreach ($parms as $parameter) {
		@ list ($arg, $value) = @ explode("=", $parameter);

		switch ($arg) {
			case "-d" :
				$debug = TRUE;

				break;
			case "--device-id" :
				$host_id = $value;

				break;
			case "--data-source-id" :
				$data_source_id = $value;

				break;
			case "--snmp-field":
				$snmp_field = $value;

				break;
			case "--snmp-value":
				$snmp_value = $value;

				break;
			case "--version" :
			case "-V" :
			case "-H" :
			case "--help" :
				display_help($me);
				exit (0);
			case "--dry-run" :
				$dry_run = __("DRY RUN >>>");

				break;
			default :
				printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
				display_help($me);
				exit (1);
		}
	}


	if (isset ($data_source_id) && ($data_source_id > 0)) {
		remove_data_source($data_source_id, $dry_run);
		exit (0);
	}

	if (isset ($host_id) && ($host_id > 0)) {
		if (!isset($snmp_field) || ($snmp_field === 0)) {
			echo __("ERROR: You must supply a valid --snmp-field") . "\n";
			echo __("Try php -q graph_list.php --list-snmp-fields") . "\n";
			exit (1);
		}
		if (!isset($snmp_value) || ($snmp_value === 0)) {
			echo __("ERROR: You must supply a valid --snmp-value") . "\n";
			echo __("Try php -q graph_list.php --list-snmp-values") . "\n";
			exit (1);
		}
		$data_sources = db_fetch_assoc("SELECT data_local.id " .
				"FROM      host_snmp_cache " .
				"LEFT JOIN data_local USING (host_id, snmp_query_id, snmp_index) " .
				"LEFT JOIN data_template_data ON (data_local.id=data_template_data.local_data_id) " .
				"WHERE     host_snmp_cache.host_id=$host_id " .
				"AND       host_snmp_cache.field_name='$snmp_field' " .
				"AND       host_snmp_cache.field_value='$snmp_value' " .
				"AND       data_local.id > 0 " .
				"ORDER BY  data_local.id");

		if (sizeof($data_sources) > 0) {
			echo $dry_run;
		       	printf(__("Removing all Data Sources for Device=%1s, SNMP Field=%2s, SNMP Value=%3d\n"), $host_id, $snmp_field, $snmp_value);
			$i = 0;
			foreach ($data_sources as $data_source) {
				remove_data_source($data_source["id"], $dry_run);
				$i++;
			}
			echo $dry_run;
			printf(__("Removed %4d Data Sources for Device=%1s, SNMP Field=%2s, SNMP Value=%3d\n"), $host_id, $snmp_field, $snmp_value, $i);
		}
		exit (0);
	}

	/* we should NOT get here */
	display_help($me);

} else {
	display_help($me);
	exit (0);
}

function remove_data_source($data_source_id, $dry_run) {

	$dry_run ? $dry_run = __("DRY RUN >>>") : $dry_run = "";

	/* Verify the data source's existance */
	if (!db_fetch_cell("SELECT id FROM data_local WHERE id=$data_source_id")) {
		printf(__("ERROR: Unknown Data Source ID (%d)\n"), $data_source_id);
		exit (1);
	}

	/*
	 * get the data sources and graphs to act on
	 * (code stolen from data_sources.php)
	 */
	$graphs = db_fetch_assoc("SELECT graph_templates_graph.local_graph_id " .
			"FROM (data_template_rrd,graph_templates_item,graph_templates_graph) " .
			"WHERE graph_templates_item.task_item_id=data_template_rrd.id " .
			"AND graph_templates_item.local_graph_id=graph_templates_graph.local_graph_id " .
			"AND data_template_rrd.local_data_id=$data_source_id " .
			"AND graph_templates_graph.local_graph_id > 0 " .
			"GROUP BY graph_templates_graph.local_graph_id");

	if (sizeof($graphs) > 0) {
		echo $dry_run . "\n";
		echo __("Delete Graph(s): ");
		foreach ($graphs as $graph) {

			if ($dry_run) {
				printf(__("Graph: %d"), $graph["local_graph_id"]);
			} else {
				echo $graph["local_graph_id"] . " ";
				api_graph_remove($graph["local_graph_id"]);
			}
		}
		echo "\n";
	}

	if ($dry_run) {
		echo $dry_run . "\n";
		printf(__("Data Source: %d"), $data_source_id);
	} else {
		printf(__("Delete Data Source: %d", $data_source_id));
		api_data_source_remove($data_source_id);
	}

	if (is_error_message()) {
		echo __(" - ERROR: Failed to remove this data source") . "\n";
		exit (1);
	} else {
		printf(__(" - SUCCESS: Removed data-source-id: (%d)\n"), $data_source_id);
	}
}

function display_help($me) {
	echo __("Remove Data Source Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to remove a data source from Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--data-source-id=[ID]|--device-id=[ID]] [--dry-run]\n\n";
	echo __("Required is either of:") . "\n";
	echo "   --device-id           " . __("the numerical ID of the device") . "\n";
	echo "   --data-source-id=[id] " . __("the numerical id of the graph") . "\n";
	echo __("When using a device-id, the following is required (ds graphs only!):") . "\n";
	echo "   --snmp-field=[field]  " . __("snmp-field to be checked") . "\n";
	echo "   --snmp-value=[value]  " . __("snmp-value to be checked") . "\n\n";
	echo __("Optional:") . "\n";
	echo "   --dry-run             " . __("produce list output only, do NOT remove anything") . "\n\n";
	printf(__("e.g. php -q %s --device-id=[ID] --snmp-field=ifOperStatus --snmp-value=DOWN\n"), $me);
	echo __("to remove all data sources and graphs for interfaces with ifOperStatus = DOWN") . "\n\n";
}
