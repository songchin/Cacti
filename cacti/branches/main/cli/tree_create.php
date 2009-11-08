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
$quietMode 	= FALSE;	# be verbose by default
$tree	 	= array();
$error		= '';

if (sizeof($parms)) {
	/* setup defaults */

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			case "--type":			$type 							= trim($value);	break;
			case "--name":			$tree["name"]					= trim($value);	break;
			case "--sort-method":	$tree["sort_type_cli"]			= trim($value);	break;
			case "--parent-node":	$tree["parent_node"] 			= $value;		break;
			case "--tree-id":		$tree["id"]						= $value;		break;
			case "--node-type":		$nodeType 						= trim($value);	break;
			case "--graph-id":		$graphId 						= $value;		break;
			case "--rra-id":		$rra_id 						= $value;		break;
			case "--device-id":		$device_id 						= $value;		break;
			case "--device-group-style":$device_group_style 		= trim($value);	break;

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
				# at least one matching criteria for host(s) has to be defined
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

				if ($debug) {
					print __("Save Tree Array:") . "\n";
					print_r($tree);
				} else {
					$tree["id"] = 0;				# create a new tree item
					unset($tree["sort_type_cli"]);	# remove for save
					$tree["id"] = sql_save($tree, "graph_tree");
					if ($tree["id"] === 0) {
						echo __("Failed to create Tree") . "\n";
					} else {
						sort_tree(SORT_TYPE_TREE, $tree["id"], $tree["sort_type"]);
						echo __("Tree Created - tree-id: (%d)", $tree["id"]) . "\n";
					}
				}
				break;


			case (strtolower($tree_types[TREE_TYPE_NODE])):

				# at least one matching criteria for host(s) has to be defined
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

				switch (strtolower($nodeType)) {
					case strtolower($tree_item_types[TREE_ITEM_TYPE_HEADER]):
						$itemType = TREE_ITEM_TYPE_HEADER;
						# Header --name must be given
						if (empty($tree["name"])) {
							echo __("ERROR: You must supply a name with --name") . "\n";
							display_help($me);
							exit(1);
						}

						# Blank out the graphId, rra_id, hostID and host_grouping_style  fields
						$graphId        	= 0;
						$rra_id         	= 0;
						$device_id         	= 0;
						$device_group_style = HOST_GROUPING_GRAPH_TEMPLATE;
						break;
					case strtolower($tree_item_types[TREE_ITEM_TYPE_GRAPH]):
						$itemType = TREE_ITEM_TYPE_GRAPH;
						# Blank out name, hostID, host_grouping_style
						$tree["name"]		= '';
						$device_id         	= 0;
						$device_group_style = HOST_GROUPING_GRAPH_TEMPLATE;

						# verify rra-id
						if (!is_numeric($rra_id)) {
							echo __("ERROR: rra-id %s must be numeric > 0", $rra_id) . "\n";
							display_help($me);
							exit(1);
						} elseif ($rra_id > 0 ) {
							$rraExists = db_fetch_cell("SELECT id FROM rra WHERE id=$rra_id");

							if (!isset($rraExists)) {
								echo __("ERROR: rra-id %d does not exist", $rra_id) . "\n";
								exit(1);
							}
						}
						break;
					case strtolower($tree_item_types[TREE_ITEM_TYPE_DEVICE]):
						$itemType = TREE_ITEM_TYPE_DEVICE;
						# Blank out graphId, rra_id, name fields
						$graphId        = 0;
						$rra_id         = 0;
						$tree["name"]	= '';

						$device_exists = db_fetch_cell("SELECT COUNT(*) FROM host WHERE id=" . $device_id);
						if (!($device_exists > 0)) {
							echo __("ERROR: No such device-id (%d) exists. Try php -q device_list.php", $device_id) . "\n";
							exit(1);
						}

						if (!isset($device_group_style) || ($device_group_style != HOST_GROUPING_GRAPH_TEMPLATE && $device_group_style != HOST_GROUPING_DATA_QUERY_INDEX)) {
							echo __("ERROR: Host Group Style must be %d or %d (Graph Template or Data Query Index)", HOST_GROUPING_GRAPH_TEMPLATE, HOST_GROUPING_DATA_QUERY_INDEX) . "\n";
							display_help($me);
							exit(1);
						}
						break;
					default:
						echo __("ERROR: Unknown node type: (%s)", $nodeType) . "\n";
						display_help($me);
						exit(1);
				}


				# optional parameters must be defined for api call
				if (!isset($tree["parent_node"])) 	$tree["parent_node"] 	= 0;
				if (!isset($tree["sort_type"])) 	$tree["sort_type"] 		= TREE_ORDERING_NONE;
				if (!isset($graphId)) 				$graphId 				= 0;
				if (!isset($rra_id)) 				$rra_id 				= 1;
				if (!isset($device_id)) 			$device_id 				= 0;
				if (!isset($device_group_style)) 	$device_group_style 	= HOST_GROUPING_GRAPH_TEMPLATE;

				# $nodeId could be a Header Node, a Graph Node, or a Host node.
				$nodeId = api_tree_item_save(0, $tree["id"], $itemType, $tree["parent_node"],
											$tree["name"], $graphId, $rra_id, $device_id,
											$device_group_style, $tree["sort_type"], false);

				echo __("Added Node node-id: (%d)", $nodeId) . "\n";

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
	echo __("Create Tree Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to create a tree or add tree items in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  --type=[tree|node] [type-options] [--quiet]\n\n";
	echo __("Tree options:") . "\n";
	echo "   --name=[Tree Name]                            " . __("name of the Tree") . "\n";
	echo "   --sort-method=[manual|alpha|natural|numeric]  " . __("Sort Method") . "\n\n";
	echo __("Node options:") . "\n";
	echo "   --node-type=[header|device|graph]             " . __("Node Type [header|device|graph]") . "\n";
	echo "   --tree-id=[ID]                                " . __("Id of Tree") . "\n";
	echo "  [--parent-node=[ID] [Node Type Options]]       " . __("Parent Node Id") . "\n\n";
	echo __("Header node options:") . "\n";
	echo "   --name=[Name]                                 " . __("Header Node Name") . "\n\n";
	echo __("Device node options:") . "\n";
	echo "   --device-id=[ID]                              " . __("Device Node Id") . "\n";
	echo "   --device-group-style=[1|2]                    " . __("Device Group Style:") . "\n";
	echo "     1 = " . __("Graph Template") . "\n";
	echo "     2 = " . __("Data Query Index") . "\n\n";
	echo __("Graph node options:") . "\n";
	echo "   --graph-id=[ID]                               " . __("Graph Id") . "\n";
	echo "  [--rra-id=[ID]]                                " . __("RRA Id") . "\n\n";
}
