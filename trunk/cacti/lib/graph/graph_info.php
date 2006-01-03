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

function api_graph_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$_sv_arr = array();
		$field_errors = api_graph_fields_validate(sql_filter_array_to_field_array($filter_array), $_sv_arr);

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_graph_form_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		graph.id,
		graph.height,
		graph.width,
		graph.title_cache,
		graph.host_id,
		graph_template.template_name
		from graph
		left join graph_template on (graph.graph_template_id=graph_template.id)
		$sql_where
		order by graph.title_cache,graph.host_id
		$sql_limit");
}

function api_graph_total_get($filter_array = "") {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$_sv_arr = array();
		$field_errors = api_graph_fields_validate(sql_filter_array_to_field_array($filter_array), $_sv_arr);

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_graph_form_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from graph $sql_where");
}

function api_graph_get($graph_id) {
	/* sanity checks */
	validate_id_die($graph_id, "graph_id");

	$graph = db_fetch_row("select * from graph where id = " . sql_sanitize($graph_id));

	if (sizeof($graph) == 0) {
		api_log_log("Invalid graph [ID#$graph] specified in api_graph_get()", SEV_ERROR);
		return false;
	}else{
		return $graph;
	}
}

/* api_graph_title_get - returns the title of a graph without using the title cache
   @arg $graph_id - (int) the ID of the graph to get a title for
   @returns - the graph title */
function api_graph_title_get($graph_id, $remove_unsubstituted_variables = false) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	$graph = db_fetch_row("select host_id,title from graph where id = $graph_id");

	$title = $graph["title"];

	if ((strstr($graph["title"], "|host_")) && (!empty($graph["host_id"]))) {
		$title = substitute_host_variables($title, $graph["host_id"]);
	}

	if ((strstr($graph["title"], "|query_")) && (!empty($graph["host_id"]))) {
		$data_query = array_rekey(db_fetch_assoc("select distinct
			data_source_field.name,
			data_source_field.value
			from data_source_field,graph_item,data_source_item
			where graph_item.data_source_item_id=data_source_item.id
			and data_source_item.data_source_id=data_source_field.data_source_id
			and graph_item.graph_id = $graph_id"), "name", "value");

		if ((isset($data_query["data_query_id"])) && (isset($data_query["data_query_index"]))) {
			$title = substitute_data_query_variables($title, $graph["host_id"], $data_query["data_query_id"], $data_query["data_query_index"], read_config_option("max_data_query_field_length"));
		}
	}

	if ($remove_unsubstituted_variables == true) {
		return remove_variables($title);
	}else{
		return $title;
	}
}

/* api_graph_associated_rras_list - returns a list of all RRAs referenced by a particular graph
   @arg $graph_id - (int) the ID of the graph to retrieve a list of RRAs for
   @returns - (array) an array containing the name and id of each RRA found */
function api_graph_associated_rras_list($graph_id) {
	return db_fetch_assoc("select
		rra.id,
		rra.steps,
		rra.rows,
		rra.name,
		rra.timespan,
		data_source.rrd_step
		from graph_item,data_source_rra,data_source_item,data_source,rra
		where graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and data_source.id=data_source_rra.data_source_id
		and data_source_rra.rra_id=rra.id
		and graph_item.graph_id = $graph_id
		group by rra.id
		order by rra.timespan");
}

function &api_graph_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	return $fields_graph;
}

function &api_graph_item_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	$field_list = array(
			"data_source_item_id" => array(
				"default" => "",
				"data_type" => DB_TYPE_NUMBER
			)
		) + $fields_graph_item;

	return $field_list;
}

?>
