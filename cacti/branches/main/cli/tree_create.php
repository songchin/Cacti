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
include_once(CACTI_BASE_PATH.'/lib/api_tree.php');
include_once(CACTI_BASE_PATH.'/lib/tree.php');

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	/* setup defaults */
	$type       = '';  # tree or node
	$name       = '';  # Name of a tree or node
	$sortMethod = 'alpha'; # manual, alpha, natural, numeric
	$parentNode = 0;   # When creating a node, the parent node of this node (or zero for root-node)
	$treeId     = 0;   # When creating a node, it has to go in a tree
	$nodeType   = '';  # Should be 'header', 'graph' or 'host' when creating a node
	$graphId    = 0;   # The ID of the graph to add (gets added to parentNode)
	$rra_id     = 1;   # The rra_id for the graph to display: 1 = daily, 2 = weekly, 3 = monthly, 4 = yearly

	$sortMethods = array('manual' => 1, 'alpha' => 2, 'natural' => 4, 'numeric' => 3);
	$nodeTypes   = array('header' => 1, 'graph' => 2, 'host' => 3);

	$hostId         = 0;
	$hostGroupStyle = 1; # 1 = Graph Template,  2 = Data Query Index

	$quietMode      = FALSE;
	$displayTrees   = FALSE;
	$displayNodes   = FALSE;
	$displayRRAs    = FALSE;
	$displayGraphs  = FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "--type":
			$type = trim($value);

			break;
		case "--name":
			$name = trim($value);

			break;
		case "--sort-method":
			$sortMethod = trim($value);

			break;
		case "--parent-node":
			$parentNode = $value;

			break;
		case "--tree-id":
			$treeId = $value;

			break;
		case "--node-type":
			$nodeType = trim($value);

			break;
		case "--graph-id":
			$graphId = $value;

			break;
		case "--rra-id":
			$rra_id = $value;

			break;
		case "--device-id":
			$hostId = $value;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		case "--list-trees":
			$displayTrees = TRUE;

			break;
		case "--list-nodes":
			$displayNodes = TRUE;

			break;
		case "--list-rras":
			$displayRRAs = TRUE;

			break;
		case "--list-graphs":
			$displayGraphs = TRUE;

			break;
		case "--device-group-style":
			$hostGroupStyle = trim($value);

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

	if ($displayTrees) {
		displayTrees($quietMode);
		exit(0);
	}

	if ($displayNodes) {
		if (!isset($treeId)) {
			echo __("ERROR: You must supply a tree_id before you can list its nodes") . "\n";
			echo __("Try --list-trees") . "\n";
			exit(1);
		}

		displayTreeNodes($treeId, $nodeType, $parentNode, $quietMode);
		exit(0);
	}

	if ($displayRRAs) {
		displayRRAs($quietMode);
		exit(0);
	}

	if ($displayGraphs) {
		if (!isset($hostId) || $hostId == 0) {
			echo __("ERROR: You must supply a device-id before you can list its graphs") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			exit(1);
		}

		displayHostGraphs($hostId, $quietMode);
		exit(0);
	}

	if ($type == 'tree') {
		# Add a new tree
		if (empty($name)) {
			echo __("ERROR: You must supply a name with --name") . "\n";
			display_help($me);
			exit(1);
		}

		$treeOpts = array();
		$treeOpts["id"]        = 0; # Zero means create a new one rather than save over an existing one
		$treeOpts["name"]      = $name;

		if ($sortMethod == "manual"||
			$sortMethod == "alpha" ||
			$sortMethod == "numeric" ||
			$sortMethod == "natural") {
			$treeOpts["sort_type"] = $sortMethods[$sortMethod];
		} else {
			printf(__("ERROR: Invalid sort-method: (%s)\n"), $sortMethod);
			display_help($me);
			exit(1);
		}

		$existsAlready = db_fetch_cell("select id from graph_tree where name = '$name'");
		if ($existsAlready) {
			printf(__("ERROR: Not adding tree - it already exists - tree-id: (%s)\n"), $existsAlready);
			exit(1);
		}

		$treeId = sql_save($treeOpts, "graph_tree");

		sort_tree(SORT_TYPE_TREE, $treeId, $treeOpts["sort_type"]);

		printf(__("Tree Created - tree-id: (%d)\n"), $treeId);

		exit(0);
	} elseif ($type == 'node') {
		# Add a new node to a tree
		if ($nodeType == "header"||
			$nodeType == "graph" ||
			$nodeType == "host") {
			$itemType = $nodeTypes[$nodeType];
		} else {
			printf(__("ERROR: Invalid node-type: (%d)"), $nodeType);
			display_help($me);
			exit(1);
		}

		if (!is_numeric($parentNode)) {
			printf(__("ERROR: parent-node %s must be numeric > 0\n"), $parentNode);
			display_help($me);
			exit(1);
		} elseif ($parentNode > 0 ) {
			$parentNodeExists = db_fetch_cell("SELECT id
				FROM graph_tree_items
				WHERE graph_tree_id=$treeId
				AND id=$parentNode");

			if (!isset($parentNodeExists)) {
				printf(__("ERROR: parent-node %s does not exist\n"), $parentNode);
				exit(1);
			}
		}

		if ($nodeType == 'header') {
			# Header --name must be given
			if (empty($name)) {
				echo __("ERROR: You must supply a name with --name") . "\n";
				display_help($me);
				exit(1);
			}

			# Blank out the graphId, rra_id, hostID and host_grouping_style  fields
			$graphId        = 0;
			$rra_id         = 0;
			$hostId         = 0;
			$hostGroupStyle = 1;
		}else if($nodeType == 'graph') {
			# Blank out name, hostID, host_grouping_style
			$name           = '';
			$hostId         = 0;
			$hostGroupStyle = 1;

			# verify rra-id
			if (!is_numeric($rra_id)) {
				printf(__("ERROR: rra-id %s must be numeric > 0\n"), $rra_id);
				display_help($me);
				exit(1);
			} elseif ($rra_id > 0 ) {
				$rraExists = db_fetch_cell("SELECT id FROM rra WHERE id=$rra_id");

				if (!isset($rraExists)) {
					printf(__("ERROR: rra-id %d does not exist\n"), $rra_id);
					exit(1);
				}
			}
		}else if ($nodeType == 'host') {
			# Blank out graphId, rra_id, name fields
			$graphId        = 0;
			$rra_id         = 0;
			$name           = '';

			$host_exists = db_fetch_cell("SELECT COUNT(*) FROM host WHERE id=" . $hostId);
			if (($host_exists > 0) || $hostId == 0) {
				printf(__("ERROR: No such device-id (%d) exists. Try php -q device_list.php\n"), $hostId);
				exit(1);
			}

			if ($hostGroupStyle != 1 && $hostGroupStyle != 2) {
				echo __("ERROR: Host Group Style must be 1 or 2 (Graph Template or Data Query Index)") . "\n";
				display_help($me);
				exit(1);
			}
		}

		# $nodeId could be a Header Node, a Graph Node, or a Host node.
		$nodeId = api_tree_item_save(0, $treeId, $itemType, $parentNode, $name, $graphId, $rra_id, $hostId, $hostGroupStyle, $sortMethods[$sortMethod], false);

		printf(__("Added Node node-id: (%d)\n"), $nodeId);

		exit(0);
	} else {
		printf(__("ERROR: Unknown type: (%s)\n"), $type);
		display_help($me);
		exit(1);
	}
} else {
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Tree Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add objects to a tree in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  --type=[tree|node] [type-options] [--quiet]\n\n";
	echo __("Tree options:") . "\n";
	echo "   --name=[Tree Name]  " . __("name of the Tree") . "\n";
	echo "   --sort-method=[manual|alpha|natural|numeric]  " . __("Sort Method") . "\n\n";
	echo __("Node options:") . "\n";
	echo "   --node-type=[header|device|graph]  " . __("Node Type [header|device|graph]") . "\n";
	echo "   --tree-id=[ID]      " . __("Id of Tree") . "\n";
	echo "  [--parent-node=[ID] [Node Type Options]]  " . __("Parent Node Id") . "\n\n";
	echo __("Header node options:") . "\n";
	echo "   --name=[Name]       " . __("Header Node Name") . "\n\n";
	echo __("Device node options:") . "\n";
	echo "   --device-id=[ID]    " . __("Device Node Id") . "\n";
	echo "  [--device-group-style=[1|2]]  " . __("Device Group Style:") . "\n";
	echo "     1 = " . __("Graph Template") . "\n";
	echo "     2 = " . __("Data Query Index") . "\n\n";
	echo __("Graph node options:") . "\n";
	echo "   --graph-id=[ID]     " . __("Graph Id") . "\n";
	echo "  [--rra-id=[ID]]      " . __("RRA Id") . "\n\n";
	echo __("List Options:") . "\n";
	echo "   --list-trees" . "\n";
	echo "   --list-nodes --tree-id=[ID]" . "\n";
	echo "   --list-rras" . "\n";
	echo "   --list-graphs --device-id=[ID]" . "\n";
}
