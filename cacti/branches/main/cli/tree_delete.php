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
$debug		= FALSE;	# no debug mode
$error		= '';

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
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			# more parameters
			case "--type":			$type 							= trim($value);	break;
			case "--id":			$tree["id"]						= $value;		break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode 						= TRUE;			break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	# id is used both for tree and tree_items
	if (isset($tree["id"])) $tree_item["id"] = $tree["id"];

	if (isset($type)) {
		switch (strtolower($type)) {
			case strtolower($tree_types[TREE_TYPE_TREE]):
				if (!sizeof($tree)) {
					print __("ERROR: No tree matching criteria found") . "\n\n";
					exit(1);
				}
				# now verify the parameters given
				$verify = verifyTree($tree, true);
				if (isset($verify["err_msg"])) {
					print $verify["err_msg"] . "\n\n";
					display_help($me);
					exit(1);
				}
				if (!$debug) {
					db_execute("delete from graph_tree where id=" . $tree["id"]);
					db_execute("delete from graph_tree_items where graph_tree_id=" . $tree["id"]);
				}
				echo __("Success - Tree and all items deleted (%s)", $tree["id"]) . "\n";

				break;

			case (strtolower($tree_types[TREE_TYPE_NODE])):

				if (!sizeof($tree_item)) {
					print __("ERROR: No tree matching criteria found") . "\n\n";
					exit(1);
				}
				if (!$debug) {
					# delete this tree item and all lower levels
					delete_branch($tree_item["id"]);
				}
				echo __("Success - Tree item and lower tree item levels deleted (%s)", $tree_item["id"]) . "\n";
				break;
			default:
				echo __("ERROR: Unknown type: (%s)", $type) . "\n";
				display_help($me);
				exit(1);
		}
	} else {
		echo __("ERROR: Missing type: (%s)", $type) . "\n";
		display_help($me);
		exit(1);
	}

} else {
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Delete Tree Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to delete objects from a tree in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  --type=[tree|node] --id=[ID]\n\n";
	echo "   --id=[ID]      " . __("Id of Tree|Node") . "\n";
}
