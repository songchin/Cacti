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

function api_graph_template_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_graph_template_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_graph_template_form_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		graph_template.id,
		graph_template.template_name
		from graph_template
		$sql_where
		" . ($sql_where == "" ? "where" : "and") . " graph_template.package_id = 0
		order by template_name
		$sql_limit");
}

function api_graph_template_get($graph_template_id) {
	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	$graph_template = db_fetch_row("select * from graph_template where id = " . sql_sanitize($graph_template_id));

	if (sizeof($graph_template) == 0) {
		api_log_log("Invalid graph template [ID#$graph_template_id] specified in api_graph_template_get()", SEV_ERROR);
		return false;
	}else{
		return $graph_template;
	}
}

function api_graph_template_item_list($graph_template_id) {
	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	return db_fetch_assoc("select * from graph_template_item where graph_template_id = " . sql_sanitize($graph_template_id) . " order by sequence");
}

function api_graph_template_item_input_list($graph_template_id) {
	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	return db_fetch_assoc("select * from graph_template_item_input where graph_template_id = " . sql_sanitize($graph_template_id));
}

function api_graph_template_item_input_item_list($graph_template_item_input_id) {
	/* sanity checks */
	validate_id_die($graph_template_item_input_id, "graph_template_item_input_id");

	return array_rekey(db_fetch_assoc("select * from graph_template_item_input_item where graph_template_item_input_id = " . sql_sanitize($graph_template_item_input_id)), "", "graph_template_item_id");
}

function api_graph_template_suggested_values_list($graph_template_id, $field_name = "") {
	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	return db_fetch_assoc("select * from graph_template_suggested_value where graph_template_id = " . sql_sanitize($graph_template_id) . ($field_name == "" ? "" : " and field_name = '" . sql_sanitize($field_name) . "'") . " order by field_name,sequence");
}

function api_graph_template_data_template_list($graph_template_id) {
	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	return db_fetch_assoc("select distinct
		data_template_item.data_template_id as id,
		data_template.template_name
		from graph_template_item,data_template_item,data_template
		where graph_template_item.data_template_item_id=data_template_item.id
		and data_template_item.data_template_id=data_template.id
		and graph_template_item.graph_template_id = " . sql_sanitize($graph_template_id) . "
		order by data_template.template_name");
}

function &api_graph_template_form_list() {
	require(CACTI_BASE_PATH . "/include/graph_template/graph_template_form.php");

	return $fields_graph_template;
}

function &api_graph_template_item_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	$field_list = array(
			"data_template_item_id" => array(
				"default" => "",
				"data_type" => DB_TYPE_INTEGER
			)
		) + $fields_graph_item;

	return $field_list;
}

function &api_graph_template_item_input_form_list() {
	require(CACTI_BASE_PATH . "/include/graph_template/graph_template_form.php");

	return $fields_graph_template_input;
}

?>
