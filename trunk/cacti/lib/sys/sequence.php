<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

function seq_get_graph_item_row($row_num, $table, $group_query) {
	$row_array = array();

	/* return if less than the first row */
	if ($row_num <= 0) {
		return $row_array;
	}

	$items = db_fetch_assoc("select id,hard_return,sequence from $table where $group_query order by sequence");

	if (sizeof($items) > 0) {
		$i = 1;
		$current_row_num = 1;

		foreach ($items as $item) {+
			$row_array{$item["sequence"]} = $item["id"];

			if ((!empty($item["hard_return"])) || ($i == sizeof($items))) {
				if ($row_num == $current_row_num) {
					return $row_array;
				}else{
					$row_array = array();
					$current_row_num++;
				}
			}

			$i++;
		}
	}

	/* return if no matches found (probably over last row */
	return array();
}

function seq_move_graph_item_row($row_num, $table, $group_query, $update_template, $direction) {
	if ($direction == "down") {
		$new_row_num = ($row_num + 1);
	}else if ($direction == "up") {
		$new_row_num = ($row_num - 1);
	}else{
		return;
	}

	$current_row = seq_get_graph_item_row($row_num, $table, $group_query);
	$new_row = seq_get_graph_item_row($new_row_num, $table, $group_query);

	if (sizeof($new_row) == 0) {
		return;
	}

	if ($direction == "down") {
		$sum_row = $current_row + $new_row;
		$new_row = $new_row + $current_row;
	}else if ($direction == "up") {
		$sum_row = $new_row + $current_row;
		$new_row = $current_row + $new_row;
	}

	while ((list($old_sequence, $old_item_id) = each($sum_row)) && (list($new_sequence, $new_item_id) = each($new_row))) {
		db_execute("update $table set sequence = $old_sequence where id = $new_item_id");

		if ($update_template == true) {
			db_execute("update graph_item set sequence = $old_sequence where graph_template_item_id = $new_item_id");
		}
	}
}

function seq_get_index($table, $sequence, $group_query) {
	$items = db_fetch_assoc("select sequence from $table where $group_query order by sequence");

	if (sizeof($items) > 0) {
		$i = 1;
		foreach ($items as $item) {
			if ($item["sequence"] == $sequence) {
				return $i;
			}

			$i++;
		}
	}

	/* if we do not get a match, assume that the item has yet to be saved and will appear at the end
	 * of the list when it is saved */
	return (sizeof($items) + 1);
}

/* get_sequence - returns the next available sequence id
   @arg $id - (int) the current id
   @arg $field - the field name that contains the target id
   @arg $table_name - the table name that contains the target id
   @arg $group_query - an SQL "where" clause to limit the query
   @returns - (int) the next available sequence id */
function seq_get_current($id, $field, $table_name, $group_query) {
	if (empty($id)) {
		$data = db_fetch_row("select max($field)+1 as seq from $table_name where $group_query");

		if ($data["seq"] == "") {
			return 1;
		}else{
			return $data["seq"];
		}
	}else{
		$data = db_fetch_row("select $field from $table_name where id=$id");
		return $data[$field];
	}
}

/* get_item - returns the ID of the next or previous item id
   @arg $tblname - the table name that contains the target id
   @arg $field - the field name that contains the target id
   @arg $startid - (int) the current id
   @arg $lmt_query - an SQL "where" clause to limit the query
   @arg $direction - ('next' or 'previous') whether to find the next or previous item id
   @returns - (int) the ID of the next or previous item id */
function seq_get_item($tblname, $field, $startid, $lmt_query, $direction) {
	if ($direction == "next") {
		$sql_operator = ">";
		$sql_order = "ASC";
	}else if ($direction == "previous") {
		$sql_operator = "<";
		$sql_order = "DESC";
	}

	$current_sequence = db_fetch_cell("select $field from $tblname where id=$startid");
	$new_item_id = db_fetch_cell("select id from $tblname where $field $sql_operator $current_sequence and $lmt_query order by $field $sql_order limit 1");

	if (empty($new_item_id)) {
		return $startid;
	}else{
		return $new_item_id;
	}
}

/* move_item_down - moves an item down by swapping it with the item below it
   @arg $table_name - the table name that contains the target id
   @arg $current_id - (int) the current id
   @arg $group_query - an SQL "where" clause to limit the query */
function seq_move_item($table_name, $current_id, $group_query, $direction) {
	$new_item = seq_get_item($table_name, "sequence", $current_id, $group_query, ($direction == "down" ? "next" : "previous"));

	$current_sequence = db_fetch_cell("select sequence from $table_name where id=$current_id");
	$new_sequence = db_fetch_cell("select sequence from $table_name where id=$new_item");
	db_execute("update $table_name set sequence=$new_sequence where id=$current_id");
	db_execute("update $table_name set sequence=$current_sequence where id=$new_item");
}

?>
