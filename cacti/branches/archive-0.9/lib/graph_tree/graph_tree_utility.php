<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

define("CHARS_PER_TIER", 3);
define("MAX_TREE_DEPTH", 30);

define("SORT_TYPE_TREE", 1);
define("SORT_TYPE_TREE_ITEM", 2);

/* tree_tier - gets the "depth" of a particular branch of the tree
   @arg $order_key - the order key of the branch to fetch the depth for
   @arg $chars_per_tier - the number of characters dedicated to each branch
     depth (tier). this is typically '3' in cacti.
   @returns - a number reprenting the depth of the branch, where '0' is the
     base of the tree and the maximum value is:
     length($order_key) / $chars_per_tier */
function api_graph_tree_item_depth_get($order_key, $chars_per_tier = CHARS_PER_TIER) {
	$root_test = str_pad('', $chars_per_tier, '0');

	if (preg_match("/^$root_test/", $order_key)) {
		$tier = 0;
	}else{
		$tier = ceil(strlen(preg_replace("/0+$/",'',$order_key)) / $chars_per_tier);
	}

	return $tier;
}

/* api_graph_tree_item_parent_get - returns the tree item id of the parent of this tree item
   @arg $id - the tree item id to search for a parent
   @arg $table - the sql table to use when searching for a parent id
   @arg $where - extra sql WHERE queries that must be used to query $table
   @returns - the id of the parent tree item to $id, or '0' if $id is at the root
     of the tree */
function api_graph_tree_item_parent_get($graph_tree_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id");

	/* get a copy of the current graph tree item */
	$graph_tree_item = api_graph_tree_item_get($graph_tree_item_id);

	return api_graph_tree_item_parent_get_bykey($graph_tree_item["order_key"], $graph_tree_item["graph_tree_id"]);
}

function api_graph_tree_item_parent_get_bykey($order_key, $graph_tree_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id");

	if (($order_key != "") && (!api_graph_tree_item_order_key_validate($order_key))) {
		die("Invalid order key '$order_key'");
	}

	$parent_root = 0;

	/* find out how deep the current item is in the tree */
	$current_depth = api_graph_tree_item_depth_get($order_key);

	/* if the current item is below the root level, figure out the order key prefix for its parent */
	if ($current_depth > 1) {
		$parent_root = substr($order_key, 0, (($current_depth - 1) * CHARS_PER_TIER));
	}

	$parent_graph_item_id = db_fetch_cell("select id from graph_tree_items where order_key = '" . str_pad($parent_root, (MAX_TREE_DEPTH * CHARS_PER_TIER), '0') . "' and graph_tree_id = " . $graph_tree_id);

	if ((empty($parent_graph_item_id)) || (empty($current_depth))) {
		return "0";
	}else{
		return $parent_graph_item_id;
	}
}

/* api_graph_tree_item_available_order_key_get - finds the next available order key on a particular branch
   @arg $order_key - the order key to use as a starting point for the available
     order key search. this order is used as the 'root' in the search
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table
   @returns - the next available order key in $order_key's branch */
function api_graph_tree_item_available_order_key_get($graph_tree_id, $order_key) {
	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id");

	if (($order_key != "") && (!api_graph_tree_item_order_key_validate($order_key))) {
		die("Invalid order key '$order_key'");
	}

	if (preg_match("/^" . str_repeat('0', CHARS_PER_TIER) . "/", $order_key)) {
		$tier = 0;
		$parent_root = '';
	}else{
		$tier = api_graph_tree_item_depth_get($order_key);
		$parent_root = substr($order_key, 0, ($tier * CHARS_PER_TIER));
	}

	$order_key = db_fetch_cell("SELECT order_key FROM graph_tree_items WHERE graph_tree_id = $graph_tree_id AND order_key LIKE '$parent_root%' ORDER BY order_key DESC LIMIT 1");

	$complete_root = substr($order_key, 0, ($tier * CHARS_PER_TIER) + CHARS_PER_TIER);
	$order_key_suffix = (substr($complete_root, - CHARS_PER_TIER) + 1);
	$order_key_suffix = str_pad($order_key_suffix, CHARS_PER_TIER, '0', STR_PAD_LEFT);
	$order_key_suffix = str_pad($parent_root . $order_key_suffix, (MAX_TREE_DEPTH * CHARS_PER_TIER), '0', STR_PAD_RIGHT);

	return $order_key_suffix;
}

function api_graph_tree_item_order_key_validate($order_key) {
	if (preg_match("/^[0-9]{" . (CHARS_PER_TIER * MAX_TREE_DEPTH) . "}$/", $order_key)) {
		return true;
	}else{
		return false;
	}
}

?>