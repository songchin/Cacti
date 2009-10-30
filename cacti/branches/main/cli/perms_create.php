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

$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms) == 0) {
	display_help($me);

	exit(1);
}else{
	$userId    = 0;

	$itemTypes = array('graph' => PERM_GRAPHS, 'tree' => PERM_TREES, 'host' => PERM_HOSTS, 'graph_template' => PERM_GRAPH_TEMPLATES);

	$itemType = 0;
	$itemId   = 0;
	$hostId   = 0;

	$quietMode				= FALSE;
	$displayGroups			= FALSE;
	$displayUsers			= FALSE;
	$displayTrees			= FALSE;
	$displayGraphs			= FALSE;
	$displayGraphTemplates 	= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "--user-id":
			$userId = $value;

			break;
		case "--item-type":
			if ( ($value == "graph") || ($value == "tree") || ($value == "host") || ($value == "graph_template")) {
				$itemType = $itemTypes[$value];
			}else{
				printf(__("ERROR: Invalid Item Type: (%s)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--item-id":
			$itemId = $value;

			break;
		case "--device-id":
			$hostId = $value;

			break;
		case "--list-groups":
			$displayGroups = TRUE;

			break;
		case "--list-users":
			$displayUsers = TRUE;

			break;
		case "--list-trees":
			$displayTrees = TRUE;

			break;
		case "--list-graphs":
			$displayGraphs = TRUE;

			break;
		case "--list-graph-templates":
			$displayGraphTemplates = TRUE;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help($me);
			exit(1);
		}
	}

	if ($displayGroups) {
		displayGroups($quietMode);
		exit(1);
	}

	if ($displayUsers) {
		displayUsers($quietMode);
		exit(1);
	}

	if ($displayTrees) {
		displayTrees($quietMode);
		exit(1);
	}

	if ($displayGraphs) {
		if (!isset($hostId) || ($hostId === 0) || (!db_fetch_cell("SELECT id FROM host WHERE id=$hostId"))) {
			echo __("ERROR: You must supply a valid device-id before you can list its graphs") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			display_help($me);
			exit(1);
		} else {
			displayHostGraphs($hostId, $quietMode);
			exit(1);
		}
	}

	if ($displayGraphTemplates) {
		$graphTemplates = getGraphTemplates();
		displayGraphTemplates($graphTemplates, $quietMode);
		exit(1);
	}

	/* verify, that a valid userid is provided */
	$userIds = array();

	if (isset($userId) && $userId > 0) {
		/* verify existing user id */
		if ( db_fetch_cell("SELECT id FROM user_auth WHERE id=$userId") ) {
			array_push($userIds, $userId);
		} else {
			printf(__("ERROR: Invalid Userid: (%d)\n\n"), $value);
			display_help($me);
			exit(1);
		}
	}
	/* now, we should have at least one verified userid */

	/* verify --item-id */
	if ($itemType == 0) {
		echo __("ERROR: --item-type missing. Please specify.") . "\n\n";
		display_help($me);
		exit(1);
	}

	if ($itemId == 0) {
		echo __("ERROR: --item-id missing. Please specify.") . "\n\n";
		display_help($me);
		exit(1);
	}

	switch ($itemType) {
		case PERM_GRAPHS: /* graph */
			if ( !db_fetch_cell("SELECT local_graph_id FROM graph_templates_graph WHERE local_graph_id=$itemId") ) {
				printf(__("ERROR: Invalid Graph item id: (%d)\n\n"), $itemId);
				display_help($me);
				exit(1);
			}
			break;
		case PERM_TREES: /* tree */
			if ( !db_fetch_cell("SELECT id FROM graph_tree WHERE id=$itemId") ) {
				printf(__("ERROR: Invalid Tree item id: (%d)\n\n"), $itemId);
				display_help($me);
				exit(1);
			}
			break;
		case PERM_HOSTS: /* device */
			if ( !db_fetch_cell("SELECT id FROM host WHERE id=$itemId") ) {
				printf(__("ERROR: Invalid device item id: (%d)\n\n"), $itemId);
				display_help($me);
				exit(1);
			}
			break;
		case PERM_GRAPH_TEMPLATES: /* graph_template */
			if ( !db_fetch_cell("SELECT id FROM graph_templates WHERE id=$itemId") ) {
				printf(__("ERROR: Invalid Graph Template item id: (%d)\n\n"), $itemId);
				display_help($me);
				exit(1);
			}
			break;
	}
	/* verified item-id */

	foreach ($userIds as $id) {
		db_execute("replace into user_auth_perms (user_id, item_id, type) values ($id, $itemId, $itemType)");
	}
}

function display_help($me) {
	echo __("Add Permissions Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add permissions to tree items in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  [ --user-id=[ID] ]\n";
	echo "   --item-type=[graph|tree|host|graph_template]\n";
	echo "   --item-id [--quiet]\n\n";
	echo __("Where %s1 is the id of the object of type %s2", "item-id", "item-type") . "\n";
	echo __("List Options:") . "\n";
	echo "   --list-users\n";
	echo "   --list-trees\n";
	echo "   --list-graph-templates\n";
	echo "   --list-graphs --device-id=[ID]\n";
	echo "   --quiet          " . __("batch mode value return") . "\n\n";
}
