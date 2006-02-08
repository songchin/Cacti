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

function api_data_template_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_data_template_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_data_template_form_list(), true);
		}
	}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		data_template.id,
		data_template.template_name,
		data_template.data_input_type,
		data_template.active
		from data_template
		$sql_where
		order by template_name
		$sql_limit");
}

function api_data_template_get($data_template_id) {
	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	$data_template = db_fetch_row("select * from data_template where id = " . sql_sanitize($data_template_id));

	if (sizeof($data_template) == 0) {
		api_log_log("Invalid data template [ID#$data_template_id] specified in api_data_template_get()", SEV_ERROR);
		return false;
	}else{
		return $data_template;
	}
}

function api_data_template_input_field_value_get($data_template_id, $field_name) {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	$value = db_fetch_assoc("select value from data_template_field where data_template_id = " . sql_sanitize($data_template_id) . " and name = '" . sql_sanitize($field_name) . "'");

	if (sizeof($value) == 1) {
		return $value[0]["value"];
	}else{
		return false;
	}
}

function get_data_templates_from_graph_template($graph_template_id, $data_input_type = 0) {
	/* sanity check for $graph_template_id */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	/* sanity check for $data_input_type */
	if (!is_numeric($data_input_type)) {
		return false;
	}

	return db_fetch_assoc("select
		data_template.id,
		data_template.data_input_type
		from graph_template_item,data_template_item,data_template
		where graph_template_item.data_template_item_id=data_template_item.id
		and data_template_item.data_template_id=data_template.id
		and graph_template_item.graph_template_id = " . sql_sanitize($graph_template_id) . "
		" . (empty($data_input_type) ? "" : "and data_template.data_input_type = " . sql_sanitize($data_input_type)) ."
		group by data_template.id");
}

function api_data_template_item_get($data_template_item_id) {
	/* sanity check for $data_template_item_id */
	if ((!is_numeric($data_template_item_id)) || (empty($data_template_item_id))) {
		return false;
	}

	$data_template_item = db_fetch_row("select * from data_template_item where id = " . sql_sanitize($data_template_item_id));

	if (sizeof($data_template_item) == 0) {
		api_log_log("Invalid data template item [ID#$data_template_item_id] specified in api_data_template_item_get()", SEV_ERROR);
		return false;
	}else{
		return $data_template_item;
	}
}

function api_data_template_item_list($data_template_id) {
	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	return db_fetch_assoc("select * from data_template_item where data_template_id = " . sql_sanitize($data_template_id));
}

function get_data_template_items_from_graph_template($graph_template_id) {
	/* sanity check for $graph_template_id */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	return db_fetch_assoc("select
		data_template_item.id,
		data_template_item.data_source_name,
		data_template_item.data_template_id,
		graph_template_item.id as graph_template_item_id
		from graph_template_item,data_template_item
		where graph_template_item.data_template_item_id=data_template_item.id
		and graph_template_item.graph_template_id = " . sql_sanitize($graph_template_id));
}

function api_data_template_rras_list($data_template_id) {
	/* sanity check for $graph_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	return array_rekey(db_fetch_assoc("select rra_id from data_template_rra where data_template_id = " . sql_sanitize($data_template_id)), "", "rra_id");
}

function api_data_template_suggested_values_list($data_template_id, $field_name = "") {
	/* sanity checks */
	validate_id_die($data_template_id, "data_template_id");

	return db_fetch_assoc("select * from data_template_suggested_value where data_template_id = " . sql_sanitize($data_template_id) . ($field_name == "" ? "" : " and field_name = '" . sql_sanitize($field_name) . "'") . " order by field_name,sequence");
}

function api_data_template_input_field_list($data_template_id) {
	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	return array_rekey(db_fetch_assoc("select name,t_value,value from data_template_field where data_template_id = " . sql_sanitize($data_template_id)), "name", array("value", "t_value"));

}

function &api_data_template_form_list() {
	require(CACTI_BASE_PATH . "/include/data_template/data_template_form.php");

	return $fields_data_template;
}

?>
