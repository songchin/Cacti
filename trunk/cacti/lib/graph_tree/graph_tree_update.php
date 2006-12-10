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

function api_graph_tree_save($graph_tree_id, &$_fields_graph_tree) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id", true);

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_graph_tree, api_graph_tree_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("graph_tree", $_fields, array("id"))) {
		if (empty($graph_tree_id)) {
			$graph_tree_id = db_fetch_insert_id();
		}

		if (isset($_fields_graph_tree["sort_type"])) {
			/* sort the tree using the algorithm chosen by the user */
			api_graph_tree_item_sort(SORT_TYPE_TREE, $graph_tree_id, $_fields_graph_tree["sort_type"]);
		}

		return $graph_tree_id;
	}else{
		return false;
	}
}

function api_graph_tree_remove($graph_tree_id) {
	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id");

	db_delete("graph_tree_items",
		array(
			"graph_tree_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_id)
			));

	return db_delete("graph_tree",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_id)
			));
}

function api_graph_tree_item_save($graph_tree_item_id, &$_fields_graph_tree_item) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id", true);

	/* sanity check for $graph_tree_id */
	if ((empty($graph_tree_item_id)) && (empty($_fields_graph_tree_item["graph_tree_id"]))) {
		api_log_log("Required graph_tree_id when graph_tree_item_id = 0", SEV_ERROR);
		return false;
	}else if ((isset($_fields_graph_tree_item["graph_tree_id"])) && (!db_integer_validate($_fields_graph_tree_item["graph_tree_id"]))) {
		return false;
	}

	/* sanity check for $item_type */
	if ((!isset($_fields_graph_tree_item["item_type"])) || (!db_integer_validate($_fields_graph_tree_item["item_type"]))) {
		api_log_log("Missing required item_type", SEV_ERROR);
		return false;
	}

	/* sanity check for $item_value */
	if ((empty($graph_tree_item_id)) && (empty($_fields_graph_tree_item["item_value"]))) {
		api_log_log("Required item_value when graph_tree_item_id = 0", SEV_ERROR);
		return false;
	}else if ((isset($_fields_graph_tree_item["item_value"])) && ( (($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) || ($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_HOST)) && (!db_integer_validate($_fields_graph_tree_item["item_value"])) )) {
		return false;
	}

	/* sanity check for $parent_item_id */
	if ((!isset($_fields_graph_tree_item["parent_item_id"])) || (!db_integer_validate($_fields_graph_tree_item["parent_item_id"], true))) {
		api_log_log("Missing required parent_item_id", SEV_ERROR);
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_item_id);

	/* field: graph_tree_id */
	if (isset($_fields_graph_tree_item["graph_tree_id"])) {
		$_fields["graph_tree_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_graph_tree_item["graph_tree_id"]);
	}

	/* get a copy of the parent tree item id */
	if ($_fields_graph_tree_item["parent_item_id"] == "0") {
		$parent_order_key = "";
		$parent_sort_type = TREE_ORDERING_NONE;
	}else{
		$parent_graph_tree_item = api_graph_tree_item_get($_fields_graph_tree_item["parent_item_id"]);
		$parent_order_key = $parent_graph_tree_item["order_key"];
		$parent_sort_type = $parent_graph_tree_item["sort_children_type"];
	}

	/* generate a new order key if this is a new graph tree item */
	if (empty($graph_tree_item_id)) {
		$_fields["order_key"] = array("type" => DB_TYPE_STRING, "value" => api_graph_tree_item_available_order_key_get($_fields_graph_tree_item["graph_tree_id"], $parent_order_key));
	}else{
		$graph_tree_item = api_graph_tree_item_get($graph_tree_item_id);
		$_fields["order_key"] = array("type" => DB_TYPE_STRING, "value" => $graph_tree_item["order_key"]);
	}

	/* if this item is a graph, make sure it is not being added to the same branch twice */
	$search_key = substr($parent_order_key, 0, (api_graph_tree_item_depth_get($parent_order_key) * CHARS_PER_TIER));
	if (($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) && (sizeof(db_fetch_assoc("select id from graph_tree_items where item_value = " . $_fields_graph_tree_item["item_value"] . " and item_type = " . TREE_ITEM_TYPE_GRAPH . " and graph_tree_id = " . $_fields_graph_tree_item["graph_tree_id"] . " and order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'")) > 0)) {
		return true;
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_graph_tree_item, api_graph_tree_item_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("graph_tree_items", $_fields, array("id"))) {
		if (empty($graph_tree_item_id)) {
			$graph_tree_item_id = db_fetch_insert_id();
		}

		/* re-parent the branch if the parent item has changed */
		if ($_fields_graph_tree_item["parent_item_id"] != api_graph_tree_item_parent_get_bykey($_fields["order_key"]["value"], $_fields_graph_tree_item["graph_tree_id"])) {
			api_graph_tree_item_reparent($graph_tree_item_id, $_fields_graph_tree_item["parent_item_id"]);
		}

		$parent_tree = api_graph_tree_get($_fields_graph_tree_item["graph_tree_id"]);

		/* tree item ordering */
		if ($parent_tree["sort_type"] == TREE_ORDERING_NONE) {
			/* resort our parent */
			if ($parent_sort_type != TREE_ORDERING_NONE) {
				echo $parent_sort_type;
				api_graph_tree_item_sort(SORT_TYPE_TREE_ITEM, $_fields_graph_tree_item["parent_item_id"], $parent_sort_type);
			}

			/* if this is a header, sort direct children */
			if (($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_HEADER) && ($parent_sort_type != TREE_ORDERING_NONE)) {
				api_graph_tree_item_sort(SORT_TYPE_TREE_ITEM, $graph_tree_item_id, $parent_sort_type);
			}
		/* tree ordering */
		}else{
			/* potential speed savings for large trees */
			if (api_graph_tree_item_depth_get($_fields["order_key"]["value"]) == 1) {
				api_graph_tree_item_sort(SORT_TYPE_TREE, $_fields_graph_tree_item["graph_tree_id"], $parent_tree["sort_type"]);
			}else{
				api_graph_tree_item_sort(SORT_TYPE_TREE_ITEM, $_fields_graph_tree_item["parent_item_id"], $parent_tree["sort_type"]);
			}
		}

		/* if the user checked the 'Propagate Changes' box */
		if (($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_HEADER) && (isset($_fields_graph_tree_item["sort_children_type"])) && (!empty($_fields_graph_tree_item["propagate_changes"]))) {
			$graph_tree_items = api_graph_tree_item_list($_fields_graph_tree_item["graph_tree_id"], array("item_type" => TREE_ITEM_TYPE_HEADER), $graph_tree_item_id, false, false);

			if (is_array($graph_tree_items) > 0) {
				foreach ($graph_tree_items as $graph_tree_item) {
					db_update("graph_tree_items",
						array(
							"id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_item["id"]),
							"sort_children_type" => array("type" => DB_TYPE_INTEGER, "value" => $_fields_graph_tree_item["sort_children_type"])
							),
						array("id"));

					if ($_fields_graph_tree_item["sort_children_type"] != TREE_ORDERING_NONE) {
						api_graph_tree_item_sort(SORT_TYPE_TREE_ITEM, $graph_tree_item["id"], $_fields_graph_tree_item["sort_children_type"]);
					}
				}
			}
		}

		return $graph_tree_item_id;
	}else{
		return false;
	}
}

/* api_graph_tree_item_sort - sorts the child items a branch using a specified sorting algorithm
   @arg $sort_type - the type of sorting to perform. available options are:
     SORT_TYPE_TREE (1) - sort the entire tree
     SORT_TYPE_TREE_ITEM (2) - sort a single tree branch
   @arg $item_id - the id tree or tree item to sort
   @arg $sort_style - the type of sorting to perform. available options are:
     TREE_ORDERING_NONE (1) - no sorting
     TREE_ORDERING_ALPHABETIC (2) - alphabetic sorting
     TREE_ORDERING_NATURAL (4) - natural sorting
     TREE_ORDERING_NUMERIC (3) - numeric sorting */
function api_graph_tree_item_sort($sort_type, $item_id, $sort_style) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/sort.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($item_id, "item_id");

	/* if no sorting style is selected then we don't have anything to do */
	if ($sort_style == TREE_ORDERING_NONE) {
		return true;
	}

	$search_key = "";

	if ($sort_type == SORT_TYPE_TREE_ITEM) {
		$graph_tree_item = api_graph_tree_item_get($item_id);

		$graph_tree_id = $graph_tree_item["graph_tree_id"];
		$limit_sub_tree_id = $item_id;
	}else if ($sort_type == SORT_TYPE_TREE) {
		$graph_tree_id = $item_id;
		$limit_sub_tree_id = "";
	}else{
		return false;
	}

	/* get a list of graph tree items that need to be sorted */
	$graph_tree_items = api_graph_tree_item_list($graph_tree_id, array(), $limit_sub_tree_id, false, false);

	/* cache all of the branches to be sorted into an array */
	$leaf_sort_array = array();
	if (is_array($graph_tree_items) > 0) {
		foreach ($graph_tree_items as $graph_tree_item) {
			$_search_key = substr($graph_tree_item["order_key"], 0, ((api_graph_tree_item_depth_get($graph_tree_item["order_key"]) - 1) * CHARS_PER_TIER));

			if ($graph_tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) {
				$leaf_sort_array{strlen($_search_key) / CHARS_PER_TIER}[$_search_key]{$graph_tree_item["order_key"]} = $graph_tree_item["graph_title"];
			}else if ($graph_tree_item["item_type"] == TREE_ITEM_TYPE_HEADER) {
				$leaf_sort_array{strlen($_search_key) / CHARS_PER_TIER}[$_search_key]{$graph_tree_item["order_key"]} = $graph_tree_item["item_value"];
			}else if ($graph_tree_item["item_type"] == TREE_ITEM_TYPE_HOST) {
				$leaf_sort_array{strlen($_search_key) / CHARS_PER_TIER}[$_search_key]{$graph_tree_item["order_key"]} = $graph_tree_item["host_description"];
			}
		}
	}

	/* do the actual sort */
	foreach ($leaf_sort_array as $_tier_key => $tier_array) {
		foreach ($tier_array as $_search_key => $search_array) {
			if ($sort_style == TREE_ORDERING_NUMERIC) {
				uasort($leaf_sort_array[$_tier_key][$_search_key], "usort_numeric");
			}elseif ($sort_style == TREE_ORDERING_ALPHABETIC) {
				uasort($leaf_sort_array[$_tier_key][$_search_key], "usort_alphabetic");
			}elseif ($sort_style == TREE_ORDERING_NATURAL) {
				uasort($leaf_sort_array[$_tier_key][$_search_key], "usort_natural");
			}
		}
	}

	/* sort from most specific to least specific */
	rsort($leaf_sort_array);

	foreach ($leaf_sort_array as $_tier_key => $tier_array) {
		foreach ($tier_array as $_search_key => $search_array) {
			/* prepend all order keys will 'x' so they don't collide during the REPLACE process */
			db_execute("UPDATE graph_tree_items SET order_key = CONCAT('x',order_key) WHERE order_key like '" . sql_sanitize($_search_key) . "%%' " . (($sort_type == SORT_TYPE_TREE_ITEM) ? "AND id != $item_id" : "AND order_key != '" . sql_sanitize($_search_key) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - strlen($_search_key)) . "'") . " AND graph_tree_id = $graph_tree_id");

			$i = 1;
			foreach ($search_array as $leaf_order_key => $leaf_title) {
				$starting_tier = api_graph_tree_item_depth_get($leaf_order_key);

				$old_base_tier = substr($leaf_order_key, 0, ($starting_tier * CHARS_PER_TIER));
				$new_base_tier = $_search_key . str_pad(strval($i), CHARS_PER_TIER, '0', STR_PAD_LEFT);

				db_execute("UPDATE graph_tree_items SET order_key = REPLACE(order_key, 'x" . sql_sanitize($old_base_tier) . "', '" . sql_sanitize($new_base_tier) . "') WHERE order_key LIKE 'x" . sql_sanitize($old_base_tier) . "%%' " . (($sort_type == SORT_TYPE_TREE_ITEM) ? "AND id != $item_id" : "") . " AND graph_tree_id = $graph_tree_id");

				$i++;
			}
		}
	}
}

/* api_graph_tree_item_move - places a branch and all of its children to a new root
     node
   @arg $new_parent_id - the target parent id for the target branch to move
   @arg $tree_item_id - the id of the branch to re-parent */
function api_graph_tree_item_reparent($graph_tree_item_id, $new_parent_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");

	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id");
	validate_id_die($new_parent_id, "new_parent_id", true);

	/* find out which tree this item belongs to */
	$graph_tree_item = api_graph_tree_item_get($graph_tree_item_id);
	$parent_graph_tree_item = api_graph_tree_item_get($new_parent_id);

	/* make sure the parent id actually changed */
	if (api_graph_tree_item_parent_get($graph_tree_item_id) == $new_parent_id) {
		return true;
	}

	/* get current key so we can do an sql select on it */
	$old_order_key = $graph_tree_item["order_key"];
	$new_order_key = api_graph_tree_item_available_order_key_get($graph_tree_item["graph_tree_id"], $parent_graph_tree_item["order_key"]);

	/* make sure nothing bad happens */
	if ((!api_graph_tree_item_order_key_validate($old_order_key)) || (!api_graph_tree_item_order_key_validate($new_order_key))) {
		return false;
	}

	/* get the item depth of the source and destination branches */
	$old_starting_depth = api_graph_tree_item_depth_get($old_order_key);
	$new_starting_depth = api_graph_tree_item_depth_get($new_order_key);

	/* get the parent order key prefix for the source and destination branches */
	$old_base_prefix = substr($old_order_key, 0, ($old_starting_depth * CHARS_PER_TIER));
	$new_base_prefix = substr($new_order_key, 0, ($new_starting_depth * CHARS_PER_TIER));

	/* prevent possible collisions */
	db_execute("UPDATE graph_tree_items SET order_key = CONCAT('x',order_key) WHERE order_key LIKE '" . sql_sanitize($old_base_prefix) . "%%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);

	/* truncate */
	if ($new_starting_depth >= $old_starting_depth) {
		db_execute("UPDATE graph_tree_items SET order_key = SUBSTRING(REPLACE(order_key, 'x" . sql_sanitize($old_base_prefix) . "', '" . sql_sanitize($new_base_prefix) . "'), 1, " . (MAX_TREE_DEPTH * CHARS_PER_TIER) . ") WHERE order_key LIKE 'x" . sql_sanitize($old_base_prefix) . "%%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);
	/* append */
	}else{
		db_execute("UPDATE graph_tree_items SET order_key = CONCAT(REPLACE(order_key, 'x" . sql_sanitize($old_base_prefix) . "', '" . sql_sanitize($new_base_prefix) . "'), '" . str_repeat('0', (strlen($old_base_prefix) - strlen($new_base_prefix))) . "') WHERE order_key LIKE 'x" . sql_sanitize($old_base_prefix) . "%%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);
	}
}

/* api_graph_tree_item_remove - deletes a branch and all of its children
   @arg $tree_item_id - the id of the branch to remove */
function api_graph_tree_item_remove($graph_tree_item_id) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");

	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id");

	/* obtain a copy of the current graph tree item */
	$graph_tree_item = api_graph_tree_item_get($graph_tree_item_id);

	/* find the id of the parent */
	$parent_graph_tree_item_id = api_graph_tree_item_parent_get($graph_tree_item_id);

	/* because deleting a parent branch deletes all of its children, it is very possible that
	 * the branch we are trying to delete has already been removed. */
	if (!isset($graph_tree_item["id"])) {
		return true;
	}

	/* if this item is a graph or host, it will have NO children, so we can just delete the
	 * item and exit. */
	if (($graph_tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) || ($graph_tree_item["item_type"] == TREE_ITEM_TYPE_HOST)) {
		return db_delete("graph_tree_items",
			array(
				"id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_tree_item_id)
				));
	}

	/* make sure nothing bad happens */
	if (!api_graph_tree_item_order_key_validate($graph_tree_item["order_key"])) {
		return false;
	}

	$graph_tree_items = api_graph_tree_item_list($graph_tree_item["graph_tree_id"], "", $graph_tree_item_id, true, true);

	if (is_array($graph_tree_items) > 0) {
		/* remove all child items */
		foreach ($graph_tree_items as $_graph_tree_item) {
			db_delete("graph_tree_items",
				array(
					"id" => array("type" => DB_TYPE_INTEGER, "value" => $_graph_tree_item["id"])
					));
		}
	}

	/* CLEANUP - reorder the tier that this branch lies in */
	$base_order_key = substr($graph_tree_item["order_key"], 0, (CHARS_PER_TIER * (api_graph_tree_item_depth_get($graph_tree_item["order_key"]) - 1)));

	/* refetch the graph tree item list now that the selected item has been removed */
	$graph_tree_items = api_graph_tree_item_list($graph_tree_item["graph_tree_id"], "", $parent_graph_tree_item_id, true, true);

	if ((is_array($graph_tree_items)) && (sizeof($graph_tree_items) > 0)) {
		$old_key_part = substr($graph_tree_items[0]["order_key"], strlen($base_order_key), CHARS_PER_TIER);

		/* we key tier==0 off of '1' and tier>0 off of '0' */
		if (api_graph_tree_item_depth_get($base_order_key) == 0) {
			$i = 1;
		}else{
			$i = 0;
		}

		foreach ($graph_tree_items as $_graph_tree_item) {
			/* this is the key column we are going to 'rekey' */
			$new_key_part = substr($_graph_tree_item["order_key"], strlen($base_order_key), CHARS_PER_TIER);

			/* incriment a counter for the new key column */
			if ($old_key_part != $new_key_part) {
				$i++;
			}

			/* build the new order key string */
			$key = $base_order_key . str_pad(strval($i), CHARS_PER_TIER, '0', STR_PAD_LEFT) . substr($_graph_tree_item["order_key"], (strlen($base_order_key) + CHARS_PER_TIER));

			db_update("graph_tree_items",
				array(
					"order_key" => array("type" => DB_TYPE_STRING, "value" => $key),
					"id" => array("type" => DB_TYPE_INTEGER, "value" => $_graph_tree_item["id"])
					),
				array("id"));

			$old_key_part = $new_key_part;
		}
	}
}

/* move_branch - moves a branch up or down in the tree
   @arg $dir - the direction of the move, either 'up' or 'down'
   @arg $order_key - the order key of the branch to move up or down
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table */
function api_graph_tree_item_move($graph_tree_item_id, $direction) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id");

	if (($direction != "up") && ($direction != "down")) {
		return false;
	}

	/* obtain a copy of the current graph tree item */
	$graph_tree_item = api_graph_tree_item_get($graph_tree_item_id);

	/* find out where in the tree this item is located */
	$current_depth = api_graph_tree_item_depth_get($graph_tree_item["order_key"]);

	$displaced_row = db_fetch_row("select
		order_key
		from graph_tree_items
		where order_key " . ($direction == "up" ? "<" : ">") . " " . sql_sanitize($graph_tree_item["order_key"]) . "
		and order_key like '%" . sql_sanitize(substr($graph_tree_item["order_key"], ($current_depth * CHARS_PER_TIER))) . "'
		and order_key not like '%" . sql_sanitize(str_repeat('0', CHARS_PER_TIER) . substr($graph_tree_item["order_key"], ($current_depth * CHARS_PER_TIER))) . "'
		and graph_tree_id = " . $graph_tree_item["graph_tree_id"] . "
		order by order_key " .  ($direction == "up" ? "DESC" : "ASC"));

	if ((is_array($displaced_row)) && (isset($displaced_row["order_key"]))) {
		$old_root = sql_sanitize(substr($graph_tree_item["order_key"], 0, ($current_depth * CHARS_PER_TIER)));
		$new_root = sql_sanitize(substr($displaced_row["order_key"], 0, ($current_depth * CHARS_PER_TIER)));

		db_execute("UPDATE graph_tree_items SET order_key = CONCAT('" . str_pad('', ($current_depth * CHARS_PER_TIER), 'Z') . "',SUBSTRING(order_key," . (($current_depth * CHARS_PER_TIER) + 1).")) WHERE order_key LIKE '$new_root%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);
		db_execute("UPDATE graph_tree_items SET order_key = CONCAT('$new_root',SUBSTRING(order_key," . (($current_depth * CHARS_PER_TIER) + 1) . ")) WHERE order_key LIKE '$old_root%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);
		db_execute("UPDATE graph_tree_items SET order_key = CONCAT('$old_root',SUBSTRING(order_key," . (($current_depth * CHARS_PER_TIER) + 1) . ")) WHERE order_key LIKE '" . str_pad('', ($current_depth * CHARS_PER_TIER), 'Z') . "%' AND graph_tree_id = " . $graph_tree_item["graph_tree_id"]);
	}
}

?>
