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

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$displayGraphTemplates 	= FALSE;
	$quietMode				= FALSE;
	unset($host_id);
	unset($graph_template_id);

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
		case "--graph-template-id":
			$graph_template_id = $value;
			if (!is_numeric($graph_template_id)) {
				echo __("ERROR: You must supply a numeric graph-template-id for all devices!") ."\n";
				exit(1);
			}

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		case "--list-graph-templates":
			$displayGraphTemplates = TRUE;
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

	/* list options, recognizing $quiteMode */
	if ($displayGraphTemplates) {
		$graphTemplates = getGraphTemplates();
		displayGraphTemplates($graphTemplates, $quietMode);
		exit(0);
	}

	/*
	 * verify required parameters
	 * for update / insert options
	 */
	if (!isset($host_id)) {
		echo __("ERROR: You must supply a valid device-id for all devices!") . "\n";
		exit(1);
	}

	if (!isset($graph_template_id)) {
		echo __("ERROR: You must supply a valid data-query-id for all devices!") . "\n";
		exit(1);
	}

	/*
	 * verify valid host id and get a name for it
	 */
	$host_name = db_fetch_cell("SELECT hostname FROM host WHERE id = " . $host_id);
	if (!isset($host_name)) {
		printf(__("ERROR: Unknown device-id (%d)\n"), $host_id);
		exit(1);
	}

	/*
	 * verify valid graph template and get a name for it
	 */
	$graph_template_name = db_fetch_cell("SELECT name FROM graph_templates WHERE id = " . $graph_template_id);
	if (!isset($graph_template_name)) {
		printf(__("ERROR: Unknown Graph Template Id (%d)\n"), $graph_template_id);
		exit(1);
	}

	/* check, if graph template was already associated */
	$exists_already = db_fetch_cell("SELECT host_id FROM host_graph WHERE graph_template_id=$graph_template_id AND host_id=$host_id");
	if ((isset($exists_already)) &&
		($exists_already > 0)) {
		printf(__("ERROR: Graph Template is already associated for device: (%1d: %2s) - graph-template: (%3d: %4s)\n"), $host_id, $host_name, $graph_template_id, $graph_template_name);
		exit(1);
	}else{
		db_execute("replace into host_graph (host_id,graph_template_id) values (" . $host_id . "," . $graph_template_id . ")");
	}

	if (is_error_message()) {
		printf(__("ERROR: Failed to add this graph template for device: (%1d: %2s) - graph-template: (%3d: %4s)\n"), $host_id, $host_name, $graph_template_id, $graph_template_name);
		exit(1);
	} else {
		printf(__("Success: Graph Template associated for device: (%1d: %2s) - graph-template: (%3d: %4s)\n"), $host_id, $host_name, $graph_template_id, $graph_template_name);
		exit(0);
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Graph Template Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to associate a graph template with a device in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --device-id=[ID] --graph-template-id=[ID]\n";
	echo "    [--quiet]\n\n";
	echo __("Required:\n");
	echo "   --device-id          " . __("the numerical ID of the device") . "\n";
	echo "   --graph_template-id  " . __("the numerical ID of the graph template to be added") . "\n\n";
	echo __("List Options:\n");
	echo "   --list-graph-templates\n";
	echo "   --quiet              " . __("batch mode value return") . "\n\n";
}

?>
