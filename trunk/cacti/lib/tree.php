<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

/* get_tree_item_type - gets the type of tree item
   @arg $tree_item_id - the id of the tree item to fetch the type for
   @returns - a string reprenting the type of the tree item. valid return
     values are 'header', 'graph', and 'host' */
function get_tree_item_type($tree_item_id) {
	$tree_item = db_fetch_row("select title,local_graph_id,host_id from graph_tree_items where id=$tree_item_id");
	
	if ($tree_item["local_graph_id"] > 0) {
		return "graph";
	}elseif ($tree_item["title"] != "") {
		return "header";
	}elseif ($tree_item["host_id"] > 0) {
		return "host";
	}
	
	return "";
}

/* tree_tier - gets the "depth" of a particular branch of the tree
   @arg $order_key - the order key of the branch to fetch the depth for
   @arg $chars_per_tier - the number of characters dedicated to each branch
     depth (tier). this is typically '2' in cacti.
   @returns - a number reprenting the depth of the branch, where '0' is the
     base of the tree and the maximum value is:
     length($order_key) / $chars_per_tier */
function tree_tier($order_key, $chars_per_tier) {
	$root_test = str_pad('',$chars_per_tier,'0');
	
	if (preg_match("/^$root_test/",$order_key)) {
		$tier = 0;
	}else{
		$tier = ceil(strlen(preg_replace("/0+$/",'',$order_key)) / $chars_per_tier);
	}
	
	return($tier);
}

/* get_parent_id - returns the tree item id of the parent of this tree item
   @arg $id - the tree item id to search for a parent
   @arg $table - the sql table to use when searching for a parent id
   @arg $where - extra sql WHERE queries that must be used to query $table
   @returns - the id of the parent tree item to $id, or '0' if $id is at the root
     of the tree */
function get_parent_id($id, $table, $where = "") {
	$parent_root = 0;
	
	$order_key = db_fetch_cell("select order_key from $table where id=$id and $where");
	$tier = tree_tier($order_key,'2');
	
    	if ($tier > 1) {
		$parent_root = substr($order_key,0,(($tier - 1) * 2) );
	}
	
	$parent_id = db_fetch_cell("select id from $table where order_key='" . str_pad($parent_root,60,'0') . "' and $where");
	
	if (empty($parent_id)) {
		return "0";
	}else{
		return $parent_id;
	}
}

/* get_next_tree_id - finds the next available order key on a particular branch
   @arg $order_key - the order key to use as a starting point for the available
     order key search. this order is used as the 'root' in the search
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table
   @returns - the next available order key in $order_key's branch */
function get_next_tree_id($order_key, $table, $field, $where) {
	if (preg_match("/^00/",$order_key)) {
		$tier = 0;
		$parent_root = '';
	}else{
		$tier = tree_tier($order_key,'2');
		$parent_root = substr($order_key,0,($tier * 2));
	}
    	
    	$order_key = db_fetch_cell("SELECT $field FROM $table WHERE $where AND $field LIKE '$parent_root%' ORDER BY $field DESC LIMIT 1");
    	
	$complete_root = substr($order_key,0,($tier * 2) + 2);
  	$order_key_suffix = (substr($complete_root, -2) + 1);
	$order_key_suffix = str_pad($order_key_suffix,2,'0',STR_PAD_LEFT);
	$order_key_suffix = str_pad($parent_root . $order_key_suffix,60,'0',STR_PAD_RIGHT);
	
	return $order_key_suffix;
}

/* branch_up - moves a branch up in the tree
   @arg $order_key - the order key of the branch to move up
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table */
function branch_up($order_key, $table, $field, $where) { 
	move_branch('up',$order_key, $table, $field, $where); 
}

/* branch_down - moves a branch down in the tree
   @arg $order_key - the order key of the branch to move down
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table */
function branch_down($order_key, $table, $field, $where) { 
	move_branch('down',$order_key, $table, $field, $where); 
}

/* move_branch - moves a branch up or down in the tree
   @arg $dir - the direction of the move, either 'up' or 'down'
   @arg $order_key - the order key of the branch to move up or down
   @arg $table - the sql table to use when searching for a parent id
   @arg $field - the sql field name that contains the order key
   @arg $where - extra sql WHERE queries that must be used to query $table */
function move_branch($dir, $order_key, $table, $field, $where) {
	$tier = tree_tier($order_key,'2');
	
	if ($where != '') { $where = " AND $where"; }
	
	$arrow = $dir == 'up' ? '<' : '>';
	$order = $dir == 'up' ? 'DESC' : 'ASC';
	
	$sql = "SELECT * FROM $table WHERE $field $arrow $order_key AND $field LIKE '%".substr($order_key,($tier * 2))."' 
		AND $field NOT LIKE '%00".substr($order_key,($tier * 2))."' $where ORDER BY $field $order";
	
	$displaced_row = db_fetch_row($sql);
	
	if (sizeof($displaced_row) > 0) {
		$old_root = substr($order_key,0,($tier * 2));
		$new_root = substr($displaced_row[$field],0,($tier * 2));
		
		db_execute("LOCK TABLES $table WRITE");
		$sql = "UPDATE $table SET $field = CONCAT('".str_pad('',($tier * 2),'Z')."',SUBSTRING($field,".(($tier * 2) + 1).")) WHERE $field LIKE '$new_root%'$where";
		db_execute($sql);
		$sql = "UPDATE $table SET $field = CONCAT('$new_root',SUBSTRING($field,".(($tier * 2) + 1).")) WHERE $field LIKE '$old_root%' $where";
		db_execute($sql);
		$sql = "UPDATE $table SET $field = CONCAT('$old_root',SUBSTRING($field,".(($tier * 2) + 1).")) WHERE $field LIKE '".str_pad('',($tier * 2),'Z')."%' $where";
		db_execute($sql);
		db_execute("UNLOCK TABLES $table");
	}
}

/* sort_branch - sorts the child items a branch using a specified sorting algorithm
   @arg $tree_item_id - the id of the branch to sort
   @arg $sort_style - the type of sorting to perform. available options are:
     TREE_ORDERING_NONE (1) - no sorting
     TREE_ORDERING_ALPHABETIC (2) - alphabetic sorting
     TREE_ORDERING_NUMERIC (3) - numeric sorting */
function sort_branch($tree_item_id, $sort_style) {
	global $config;
	
	include_once($config["library_path"] . "/sort.php");
	
	if (empty($tree_item_id)) { return 0; }
	if ($sort_style == TREE_ORDERING_NONE) { return 0; }
	
	$tree_item = db_fetch_row("select order_key,graph_tree_id from graph_tree_items where id=$tree_item_id");
	
	$search_key = preg_replace("/0+$/", "", $tree_item["order_key"]);
		
	/* the search key should always be divisible by "2" because we are searching AT a
	branch (__), not PAST it (%%) */
	if (strlen($search_key) % 2 != 0) { $search_key .= "0"; }
	
	$heirarchy = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.local_graph_id,
		graph_tree_items.host_id,
		graph_tree_items.order_key,
		graph_templates_graph.title_cache as graph_title,
		CONCAT_WS('',description,' (',hostname,')') as hostname
		from graph_tree_items
		left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
		left join host on host.id=graph_tree_items.host_id
		where graph_tree_items.graph_tree_id=" . $tree_item["graph_tree_id"] . "
		and graph_tree_items.order_key like '$search_key" . "__" . str_repeat('0',60-(strlen($search_key)+2)) . "'
		and graph_tree_items.id != $tree_item_id
		order by graph_tree_items.order_key");
	
	$leaf_sort_array = array();
	if (sizeof($heirarchy) > 0) {
		foreach ($heirarchy as $leaf) {
			if ($leaf["local_graph_id"] > 0) {
				$leaf_sort_array{$leaf["order_key"]} = $leaf["graph_title"];
			}elseif ($leaf["title"] != "") {
				$leaf_sort_array{$leaf["order_key"]} = $leaf["title"];
			}elseif ($leaf["host_id"] > 0) {
				$leaf_sort_array{$leaf["order_key"]} = $leaf["hostname"];
			}
		}
	}
	
	/* do the actual sort */
	if ($sort_style == TREE_ORDERING_NUMERIC) {
		uasort($leaf_sort_array, "usort_numeric");
	}elseif ($sort_style == TREE_ORDERING_ALPHABETIC) {
		uasort($leaf_sort_array, "usort_alphabetic");
	}
	
	/* prepend all order keys will 'x' so they don't collide during the REPLACE process */
	if (sizeof($leaf_sort_array) > 0) {
		db_execute("update graph_tree_items set order_key = CONCAT('x',order_key) where order_key like '$search_key%%' and id != $tree_item_id");
	}
	
	$i = 1;
	while (list($leaf_order_key, $leaf_title) = each($leaf_sort_array)) {
		$starting_tier = tree_tier($leaf_order_key, 2);
		
		$old_base_tier = substr($leaf_order_key, 0, ($starting_tier*2));
		$new_base_tier = $search_key . str_pad(strval($i),2,'0',STR_PAD_LEFT);
		
		db_execute("update graph_tree_items set order_key = REPLACE(order_key, 'x$old_base_tier', '$new_base_tier') where order_key like 'x$old_base_tier%%' and id != $tree_item_id");
		
		$i++;
	}
}

/* reparent_branch - places a branch and all of its children to a new root
     node
   @arg $new_parent_id - the target parent id for the target branch to move
   @arg $tree_item_id - the id of the branch to re-parent */
function reparent_branch($new_parent_id, $tree_item_id) {
	if (empty($tree_item_id)) { return 0; }
	
	/* get the current tree_id */
	$graph_tree_id = db_fetch_cell("select graph_tree_id from graph_tree_items where id=$tree_item_id");
	
	/* make sure the parent id actually changed */
	if (get_parent_id($tree_item_id, "graph_tree_items", "graph_tree_id=$graph_tree_id") == $new_parent_id) {
		return 0;
	}
	
	/* get current key so we can do a sql select on it */
	$old_order_key = db_fetch_cell("select order_key from graph_tree_items where id=$tree_item_id");
	$new_order_key = get_next_tree_id(db_fetch_cell("select order_key from graph_tree_items where id=$new_parent_id"),"graph_tree_items","order_key","graph_tree_id=$graph_tree_id");
	
	/* yeah, this would be really bad */
	if (empty($old_order_key)) { return 0; }
	
	$old_starting_tier = tree_tier($old_order_key, 2);
	$new_starting_tier = tree_tier($new_order_key, 2);
	
	$new_base_tier = substr($new_order_key, 0, ($new_starting_tier*2));
	$old_base_tier = substr($old_order_key, 0, ($old_starting_tier*2));
	
	$padding = "";
	
	$tree = db_fetch_assoc("select 
		graph_tree_items.id, graph_tree_items.order_key
		from graph_tree_items
		where graph_tree_items.order_key like '$old_base_tier%%'
		and graph_tree_items.graph_tree_id=$graph_tree_id
		order by graph_tree_items.order_key");
	
	/* since we are building the order_key based on two unrelated tiers, we must be sure the final product
	always adds up to 60 characters */
	if ((($old_starting_tier * 2) + 1) > strlen($new_base_tier)) {
		$padding = ",'" . str_repeat('0', (($old_starting_tier * 2) + 1) - strlen($new_base_tier)) . "'";
	}
	
	db_execute("update graph_tree_items set order_key = CONCAT('$new_base_tier',SUBSTRING(order_key," . (($old_starting_tier * 2) + 1) . ")$padding) where order_key like '$old_base_tier%%' and graph_tree_id=$graph_tree_id");
}

/* delete_branch - deletes a branch and all of its children
   @arg $tree_item_id - the id of the branch to remove */
function delete_branch($tree_item_id) {
	if (empty($tree_item_id)) { return 0; }
	
	$tree_item = db_fetch_row("select order_key,local_graph_id,host_id,graph_tree_id from graph_tree_items where id=$tree_item_id");
	
	/* if this item is a graph/host, it will have NO children, so we can just delete the
	graph and exit. */
	if ((!empty($tree_item["local_graph_id"])) || (!empty($tree_item["host_id"]))) {
		db_execute("delete from graph_tree_items where id=$tree_item_id");
		return 0;
	}
	
	/* yeah, this would be really bad */
	if (empty($tree_item["order_key"])) { return 0; }
	
	$starting_tier = tree_tier($tree_item["order_key"], 2);
	$order_key = substr($tree_item["order_key"], 0, (2 * $starting_tier));
	
	$tree = db_fetch_assoc("select 
		graph_tree_items.id, graph_tree_items.order_key
		from graph_tree_items
		where graph_tree_items.order_key like '$order_key%%'
		and graph_tree_items.graph_tree_id='" . $tree_item["graph_tree_id"] . "'
		order by graph_tree_items.order_key");
	
	if (sizeof($tree) > 0) {
	foreach ($tree as $item) {
		/* delete the folder */
		db_execute("delete from graph_tree_items where id=" . $item["id"]);
	}
	}
	
	/* CLEANUP - reorder the tier that this branch lies in */
	$order_key = substr($order_key, 0, (2 * ($starting_tier-1)));
	
	$tree = db_fetch_assoc("select 
		graph_tree_items.id, graph_tree_items.order_key
		from graph_tree_items
		where graph_tree_items.order_key like '$order_key%%'
		and graph_tree_items.graph_tree_id='" . $tree_item["graph_tree_id"] . "'
		order by graph_tree_items.order_key");
	
	if (sizeof($tree) > 0) {
		$old_key_part = substr($tree[0]["order_key"], strlen($order_key), 2);
		
		/* we key tier==0 off of '1' and tier>0 off of '0' */
		if (tree_tier($order_key, 2) == "0") {
			$i = 1;
		}else{
			$i = 0;
		}
		
		foreach ($tree as $tree_item) {
			/* this is the key column we are going to 'rekey' */
			$new_key_part = substr($tree_item["order_key"], strlen($order_key), 2);
			
			/* incriment a counter for the new key column */
			if ($old_key_part != $new_key_part) {
				$i++;
			}
			
			/* build the new order key string */
			$key = $order_key . str_pad(strval($i),2,'0',STR_PAD_LEFT) . substr($tree_item["order_key"], (strlen($order_key) + 2));
			
			db_execute("update graph_tree_items set order_key='$key' where id=" . $tree_item["id"]);
			
			$old_key_part = $new_key_part;
		}
	}
}

?>
