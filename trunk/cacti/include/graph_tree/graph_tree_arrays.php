<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");

$graph_tree_sort_types = array(
	TREE_ORDERING_NONE => _("Manual Ordering (No Sorting)"),
	TREE_ORDERING_ALPHABETIC => _("Alphabetic Ordering"),
	TREE_ORDERING_NATURAL => _("Natural Ordering"),
	TREE_ORDERING_NUMERIC => _("Numeric Ordering")
	);

$graph_tree_item_types = array(
	TREE_ITEM_TYPE_HEADER => "Header",
	TREE_ITEM_TYPE_GRAPH => "Graph",
	TREE_ITEM_TYPE_HOST => "Host"
	);

$graph_tree_item_device_grouping_types = array(
	TREE_DEVICE_GROUPING_GRAPH_TEMPLATE => "Graph Template",
	TREE_DEVICE_GROUPING_DATA_QUERY_INDEX => "Data Query Index"
	);

?>
