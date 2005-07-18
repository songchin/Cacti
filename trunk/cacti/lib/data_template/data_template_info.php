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

function get_data_template($data_template_id) {
	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	$data_template = db_fetch_row("select * from data_template where id = " . sql_sanitize($data_template_id));

	if (sizeof($data_template) == 0) {
		api_syslog_cacti_log("Invalid data template [ID#$data_template_id] specified in get_data_template()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}else{
		return $data_template;
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

function get_data_template_item($data_template_item_id) {
	/* sanity check for $data_template_item_id */
	if ((!is_numeric($data_template_item_id)) || (empty($data_template_item_id))) {
		return false;
	}

	$data_template_item = db_fetch_row("select * from data_template_item where id = " . sql_sanitize($data_template_item_id));

	if (sizeof($data_template_item) == 0) {
		api_syslog_cacti_log("Invalid data template item [ID#$data_template_item_id] specified in get_data_template_item()", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	}else{
		return $data_template_item;
	}
}

function get_data_template_items($data_template_id) {
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

function get_data_template_rras($data_template_id) {
	/* sanity check for $graph_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	return array_rekey(db_fetch_assoc("select rra_id from data_template_rra where data_template_id = " . sql_sanitize($data_template_id)), "", "rra_id");
}

function get_data_template_input_fields($data_template_id) {
	/* sanity check for $data_template_id */
	if ((!is_numeric($data_template_id)) || (empty($data_template_id))) {
		return false;
	}

	return array_rekey(db_fetch_assoc("select name,t_value,value from data_template_field where data_template_id = " . sql_sanitize($data_template_id)), "name", array("value", "t_value"));

}

function &get_data_template_field_list() {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");

	return $fields_data_template;
}

?>