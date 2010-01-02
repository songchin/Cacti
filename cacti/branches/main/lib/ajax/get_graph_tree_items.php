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

$no_http_headers = true;
include(dirname(__FILE__) . "/../../include/global.php");
include_once(dirname(__FILE__) . "/../../lib/functions.php");
include_once(dirname(__FILE__) . "/../../lib/html_tree.php");

/* Make sure nothing is cached */
header("Cache-Control: must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

input_validate_input_number(get_request_var("tree_id"));

switch(get_request_var_request("type")) {
case "list":
	/* parse the id string
	 * prototypes:
	 * tree_id, tree_id_leaf_id, tree_id_leaf_id_hgd_dq
	 * tree_id_leaf_id_hgd_dqi, tree_id_leaf_id_hgd_gt
	 */
	if (!isset($_REQUEST["tree_id"])) {
		$tree_id = 0;
	}else{
		$tree_id = $_REQUEST["tree_id"];
	}

	$leaf_id         = 0;
	$host_group_type = array('na', 0);

	if (isset($_REQUEST["id"])) {
		$id_array = explode("_", $_REQUEST["id"]);
		$type     = "";

		if (sizeof($id_array)) {
			foreach($id_array as $part) {
				if (is_numeric($part)) {
					switch($type) {
						case "tree":
							$tree_id = $part;
							break;
						case "leaf":
							$leaf_id = $part;
							break;
						case "dqi":
							$host_group_type = array("dqi", $part);
							break;
						case "dq":
							$host_group_type = array("dq", $part);
							break;
						case "gt":
							$host_group_type = array("gt", $part);
							break;
						default:
							break;
					}
				}else{
					$type = trim($part);
				}
			}
		}
	}

	//cacti_log("tree_id: '" . $tree_id . ", leaf_id: '" . $leaf_id . ", hgt: '" . $host_group_type[0] . "," . $host_group_type[1] . "'", false);
	if (is_numeric($_REQUEST["id"]) || $tree_id <= 0) {
		$tree_items = get_tree_leaf_items($tree_id, $leaf_id, $host_group_type, true);
	}else{
		$tree_items = get_tree_leaf_items($tree_id, $leaf_id, $host_group_type);
	}

	if (sizeof($tree_items)) {
		$total_items = sizeof($tree_items);

		$i = 0;
		echo "[\n";

		foreach($tree_items as $item) {
			$node_id  = "tree_" . $item["tree_id"];
			$node_id .= "_leaf_" . $item["leaf_id"];
			$display  = true;
			switch ($item["type"]) {
				case "tree":
					$children = true;
					$icon     = "";
					break;
				case "graph":
					$children = false;
					$icon     = CACTI_URL_PATH . "/images/tree_icons/graph.gif";
					$display  = false;
					break;
				case "host":
					if (read_graph_config_option("expand_hosts") == CHECKED) {
						$children = true;
					}else{
						$children = false;
					}
					$icon     = CACTI_URL_PATH . "/images/tree_icons/host.gif";
					break;
				case "header":
					$children = true;
					$icon     = "";
					break;
				case "dq":
					$children = true;
					$icon     = "";
					$node_id .= "_" . $item["type"] . "_" . $item["id"];
					$icon     = CACTI_URL_PATH . "/images/tree_icons/dataquery.png";
					break;
				case "dqi":
					$children = false;
					$icon     = "";
					$node_id .= "_" . $item["type"] . "_" . $item["id"];
					break;
				case "gt":
					$children = false;
					$node_id .= "_" . $item["type"] . "_" . $item["id"];
					$icon     = CACTI_URL_PATH . "/images/tree_icons/template.png";
					break;
				default:
			}
			if ($display) {
				echo "{\n";
				echo "\tattributes: {\n";
				echo "\t\tid :  '" . $node_id . "'\n";
				echo "\t},\n";
				if($children) echo "\tstate: 'closed', \n";
				echo "\tdata: {\n";
				echo "\t\t'en' : { title : '".$item["name"] ."'" . ($icon != '' ? ", icon : '" . $icon . "'" : "") ." }";
				echo "\n";
				echo "\t}\n";
				echo "}";
				if(++$i < $total_items) echo ",";
				echo "\n";
			}
		}
	}
	echo "\n]";
	break;
case "loadfile":
	break;
case "savefile":
	break;
}

exit();
