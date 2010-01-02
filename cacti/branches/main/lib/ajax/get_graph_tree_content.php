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
include_once(CACTI_BASE_PATH . "/lib/functions.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/timespan_settings.php");

/* Make sure nothing is cached */
header("Cache-Control: must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

/* parse the id string
 * prototypes:
 * tree_id, tree_id_leaf_id, tree_id_leaf_id_hgd_dq
 * tree_id_leaf_id_hgd_dqi, tree_id_leaf_id_hgd_gt
 */
$tree_id         = 0;
$leaf_id         = 0;
$host_group_type = array('na', 0);

if (!isset($_REQUEST["id"])) {
	if (isset($_SESSION["sess_graph_navigation"])) {
		$_REQUEST["id"] = $_SESSION["sess_graph_navigation"];
	}
}

if (isset($_REQUEST["id"])) {
	$_SESSION["sess_graph_navigation"] = $_REQUEST["id"];
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

get_graph_tree_content($tree_id, $leaf_id, $host_group_type);

exit();
