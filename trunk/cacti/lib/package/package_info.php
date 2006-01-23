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

function api_package_list($filter_array = "", $current_page = 0, $rows_per_page = 0) {
	//require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_form.php");

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	//if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
	//	$field_errors = api_data_template_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
	//	if (sizeof($field_errors) > 0) {
	//		field_register_error($field_errors);
	//		return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
	//	}else{
	//		$sql_where = sql_filter_array_to_where_string($filter_array, api_data_template_fields_list(), true);
	//	}
	//}

	$sql_limit = "";
	/* validation and setup for the LIMIT clause */
	if ((is_numeric($current_page)) && (is_numeric($rows_per_page)) && (!empty($current_page)) && (!empty($rows_per_page))) {
		$sql_limit = "limit " . ($rows_per_page * ($current_page - 1)) . ",$rows_per_page";
	}

	return db_fetch_assoc("select
		package.id,
		package.version,
		package.date_create,
		package.name,
		package.category,
		package.subcategory
		from package
		$sql_where
		order by name
		$sql_limit");
}

function api_package_get($package_id) {
	/* sanity checks */
	validate_id_die($package_id, "package_id");

	return db_fetch_row("select * from package where id = " . sql_sanitize($package_id));
}

function api_package_graph_template_list($package_id) {
	/* sanity checks */
	validate_id_die($package_id, "package_id");

	return db_fetch_assoc("select
		graph_template.id,
		graph_template.template_name
		from graph_template,package_graph_template
		where graph_template.id=package_graph_template.graph_template_id
		and package_graph_template.package_id = " . sql_sanitize($package_id) . "
		order by graph_template.template_name");
}

function api_package_metadata_list($package_id, $type = 0) {
	/* sanity checks */
	validate_id_die($package_id, "package_id");
	validate_id_die($type, "type", true);

	return db_fetch_assoc("select id,package_id,type,name,description,description_install,required from package_metadata where package_id = " . sql_sanitize($package_id) . (empty($type) ? "" : " and type = " . sql_sanitize($type)));
}

function api_package_metadata_get($package_metadata_id) {
	/* sanity checks */
	validate_id_die($package_metadata_id, "package_metadata_id");

	return db_fetch_row("select * from package_metadata where id = " . sql_sanitize($package_metadata_id));
}

function api_package_author_list() {
	return db_fetch_assoc("select * from package_author order by name");
}

function api_package_author_get($package_author_id) {
	/* sanity checks */
	validate_id_die($package_author_id, "package_author_id");

	return db_fetch_row("select * from package_author where id = " . sql_sanitize($package_author_id));
}

function &api_package_metadata_type_list() {
	require(CACTI_BASE_PATH . "/include/package/package_arrays.php");

	return $package_metadata_types;
}

function &api_package_form_list() {
	require(CACTI_BASE_PATH . "/include/package/package_form.php");

	return $fields_package;
}

function &api_package_metadata_form_list() {
	require(CACTI_BASE_PATH . "/include/package/package_form.php");

	return $fields_package_metadata;
}

?>
