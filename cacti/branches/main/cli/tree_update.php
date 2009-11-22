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

			case "--type":			$type 							= trim($value);	break;
			case "--name":			$tree["name"]					= trim($value);	break;
			case "--sort-method":	$tree["sort_type_cli"]			= trim($value);	break;
			case "--id":			$tree["id"]						= $value;		break;
			case "--node-type":		$nodeType 						= trim($value);	break;
			case "--graph-id":		$tree_item["local_graph_id"]	= $value;		break;
			case "--rra-id":		$tree_item["rra_id"]			= $value;		break;
			case "--device-id":		$tree_item["host_id"]			= $value;		break;
			case "--device-group-type":$tree_item["host_grouping_type"] = trim($value);	break;
			case "--sort-children-type":$tree_item["sort_children_type"] = trim($value);	break;
			case "--parent-node":	$tree_item["parent_node"]		= $value;		break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode 						= TRUE;			break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

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

				$sql = "UPDATE graph_tree SET ";
				$sql_vars = "";
				if (isset($tree["name"])) {
					$sql_vars .= (strlen($sql_vars) ? "," : "");
					$sql_vars .= "name='" . $tree["name"] . "'";
				}
				if (isset($tree["sort_type"])) {
					$sql_vars .= (strlen($sql_vars) ? "," : "");
					$sql_vars .= "sort_type=" . $tree["sort_type"];
				}
				if (!strlen($sql_vars)) {
					print __("ERROR: No tree update criteria found") . "\n\n";
					exit(1);
				}
				$sql .= $sql_vars . " WHERE id=" . $tree["id"];

				if (!$debug) {
					db_execute($sql);
					echo __("Success - Tree updated (%s)", $tree["id"]) . "\n";
				} else {
					print $sql;
				}

				break;

			case (strtolower($tree_types[TREE_TYPE_NODE])):

				# id,name are used both for tree and tree_items
				if (isset($tree["name"])) $tree_item["title"] = $tree["name"];
				if (isset($tree["sort_type_cli"])) $tree_item["sort_type_cli"] = $tree["sort_type_cli"];
				if (isset($tree["id"])) $tree_item["id"] = $tree["id"];

				# at least one matching criteria for host(s) has to be defined
				if (!sizeof($tree_item) || !isset($tree_item["id"])) {
					print __("ERROR: No tree item matching criteria found") . "\n\n";
					exit(1);
				}

				$item = db_fetch_row("SELECT * FROM graph_tree_items WHERE id=" . $tree_item["id"]);
				if (isset($tree_item["parent_node"])) {	# fetch related graph tree id
					$tree_item["graph_tree_id"] = $item["graph_tree_id"];
				}

				# now verify the parameters given
				$verify = verifyTreeItem($tree_item, true);
				if (isset($verify["err_msg"])) {
					print $verify["err_msg"] . "\n\n";
					display_help($me);
					exit(1);
				}

				$current_type = "";
				if ($item["local_graph_id"] > 0) 	{ $current_type = TREE_ITEM_TYPE_GRAPH; }
				if ($item["title"] != "") 			{ $current_type = TREE_ITEM_TYPE_HEADER; }
				if ($item["host_id"] > 0) 			{ $current_type = TREE_ITEM_TYPE_DEVICE; }

				# create sql depending on node type
				$sql = "UPDATE graph_tree_items SET ";
				$sql_vars = "";
				switch ($current_type) {
					case TREE_ITEM_TYPE_HEADER:
						if (isset($tree_item["title"])) {
							$sql_vars .= (strlen($sql_vars) ? "," : "");
							$sql_vars .= "title='" . $tree_item["title"] . "'";
						}
						if (isset($tree_item["sort_children_type"])) {
							$sql_vars .= (strlen($sql_vars) ? "," : "");
							$sql_vars .= "sort_children_type=" . $tree_item["sort_children_type"];
						}
						break;
					case TREE_ITEM_TYPE_GRAPH:
						if (isset($tree_item["rra_id"])) {
							$sql_vars .= (strlen($sql_vars) ? "," : "");
							$sql_vars .= "rra_id='" . $tree_item["rra_id"] . "'";
						}
						break;
					case TREE_ITEM_TYPE_DEVICE:
						if (isset($tree_item["host_grouping_type"])) {
							$sql_vars .= (strlen($sql_vars) ? "," : "");
							$sql_vars .= "host_grouping_type=" . $tree_item["host_grouping_type"];
						}
						break;
					default:
						echo __("ERROR: Unknown node type: (%s)", $nodeType) . "\n";
						display_help($me);
						exit(1);
				}
				if (!strlen($sql_vars)) {	# no sql updates required
					$sql = "";
				} else {
					$sql .= $sql_vars . " WHERE id=" . $tree_item["id"];
				}

				if (!$debug) {
					if (strlen($sql)) {	# if only reparent action required, sql may be empty
						db_execute($sql);
					}
					/* re-parent the branch if the parent item has changed */
					if (isset($tree_item["parent_node"])) {
						$current_parent = get_parent_id($tree_item["id"], "graph_tree_items");
						if ($tree_item["parent_node"] != $current_parent) {
							reparent_branch($tree_item["parent_node"], $tree_item["id"]);
						}
					}
					echo __("Success - Tree item updated (%s)", $tree_item["id"]) . "\n";
				} else {
					print $sql . "\n";
				}

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
	echo __("Update Tree Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to update objects of a tree in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  --type=[tree|node] --id=[ID]\n\n";
	echo "   --type=[tree|node]                              " . __("Type of object") . "\n";
	echo "   --id=[ID]                                       " . __("Id of Tree|Node") . "\n";
	echo __("Tree options:") . "\n";
	echo "   [--name=[Tree Name]]                            " . __("name of the Tree") . "\n";
	echo "   [--sort-method=[manual|alpha|natural|numeric]]  " . __("Sort Method") . "\n\n";
	echo __("Node options:") . "\n";
	echo "  [--parent-node=[ID] [Node Type Options]]         " . __("Parent Node Id") . "\n\n";
	echo __("Header node options:") . "\n";
	echo "   [--name=[Name]]                                 " . __("Header Node Name") . "\n\n";
	echo "   [--sort-children-type=[1|2|3|4]]                " . __("Sort Children Type:") . "\n";
	echo "     1 = " . __("Manual") . "\n";
	echo "     2 = " . __("Alphabetic") . "\n";
	echo "     3 = " . __("Numeric") . "\n";
	echo "     4 = " . __("Natural") . "\n\n";
	echo __("Device node options:") . "\n";
	echo "  --device-group-style=[1|2]                       " . __("Device Group Style:") . "\n";
	echo "     1 = " . __("Graph Template") . "\n";
	echo "     2 = " . __("Data Query Index") . "\n\n";
	echo __("Graph node options:") . "\n";
	echo "  --rra-id=[ID]                                    " . __("RRA Id") . "\n\n";
}
