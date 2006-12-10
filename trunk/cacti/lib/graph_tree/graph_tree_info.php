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

function api_graph_tree_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_graph_tree_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_graph_tree_form_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		graph_tree.id,
		graph_tree.name,
		graph_tree.sort_type
		from graph_tree
		$sql_where
		order by name
		$sql_limit");
}

function api_graph_tree_get($graph_tree_id) {
	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id");

	return db_fetch_row("select * from graph_tree where id = " . sql_sanitize($graph_tree_id));
}

function api_graph_tree_item_list($graph_tree_id, $filter_array = "", $limit_sub_tree_id = "", $show_sub_tree_parent = false, $show_sub_tree_children = true, $current_page = 0, $rows_per_page = 0) {
	/* sanity checks */
	validate_id_die($graph_tree_id, "graph_tree_id");

	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_graph_tree_item_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_graph_tree_item_form_list(), false);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	/* only show tree items under this item if specified */
	if (db_integer_validate($limit_sub_tree_id, false, false)) {
		$graph_tree_item = api_graph_tree_item_get($limit_sub_tree_id);
		$search_key = substr($graph_tree_item["order_key"], 0, (api_graph_tree_item_depth_get($graph_tree_item["order_key"]) * CHARS_PER_TIER));

		if ($show_sub_tree_children == true) {
			$sql_where .= "and graph_tree_items.order_key like '$search_key%%'";
		}else{
			$sql_where .= " and graph_tree_items.order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'";
		}

		if ($show_sub_tree_parent == false) {
			$sql_where .= "and graph_tree_items.id != $limit_sub_tree_id";
		}
	}

	return db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.order_key,
		graph_tree_items.sort_children_type,
		graph_tree_items.device_grouping_type,
		graph_tree_items.item_type,
		graph_tree_items.item_value,
		graph.title_cache as graph_title,
		host.description as host_description,
		host.hostname as host_hostname
		from graph_tree_items
		left join graph on (graph_tree_items.item_value = graph.id and graph_tree_items.item_type = " . TREE_ITEM_TYPE_GRAPH . ")
		left join host on (graph_tree_items.item_value = host.id and graph_tree_items.item_type = " . TREE_ITEM_TYPE_HOST . ")
		where graph_tree_items.graph_tree_id = " . sql_sanitize($graph_tree_id) . "
		$sql_where
		order by graph_tree_items.order_key
		$sql_limit");
}

function api_graph_tree_item_get($graph_tree_item_id) {
	/* sanity checks */
	validate_id_die($graph_tree_item_id, "graph_tree_item_id");

	return db_fetch_row("select * from graph_tree_items where id = " . sql_sanitize($graph_tree_item_id));
}

function &api_graph_tree_form_list() {
	require(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_form.php");

	return $fields_graph_tree;
}

function &api_graph_tree_item_form_list() {
	require(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_form.php");

	return $fields_graph_tree_item;
}

function &api_graph_tree_sort_type_list() {
	require(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_arrays.php");

	return $graph_tree_sort_types;
}

function &api_graph_tree_item_device_grouping_type_list() {
	require(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_arrays.php");

	return $graph_tree_item_device_grouping_types;
}

function &api_graph_tree_item_type_list() {
	require(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_arrays.php");

	return $graph_tree_item_types;
}

?>
