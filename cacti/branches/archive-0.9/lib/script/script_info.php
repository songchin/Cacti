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

function api_script_list() {
	return db_fetch_assoc("select * from data_input order by name");
}

function api_script_get($script_id) {
	/* sanity checks */
	validate_id_die($script_id, "script_id");

	return db_fetch_row("select * from data_input where id = " . sql_sanitize($script_id));
}

function api_script_field_list($script_id, $input_type = "") {
	require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");

	/* sanity checks */
	validate_id_die($script_id, "script_id");

	return db_fetch_assoc("select * from data_input_fields where data_input_id = " . sql_sanitize($script_id) . (($input_type == SCRIPT_FIELD_TYPE_INPUT) ? " and input_output = 'in'" : " and input_output = 'out'") . " order by name");
}

function api_script_field_get($script_field_id) {
	/* sanity checks */
	validate_id_die($script_field_id, "script_field_id");

	return db_fetch_row("select * from data_input_fields where id = " . sql_sanitize($script_field_id));
}

function &api_script_input_type_list() {
	require(CACTI_BASE_PATH . "/include/script/script_arrays.php");

	return $script_input_types;
}

function &api_script_field_input_type_list() {
	require(CACTI_BASE_PATH . "/include/script/script_arrays.php");

	return $script_field_input_types;
}

function &api_script_form_list() {
	require(CACTI_BASE_PATH . "/include/script/script_form.php");

	return $fields_script;
}

function &api_script_field_form_list() {
	require(CACTI_BASE_PATH . "/include/script/script_form.php");

	return $fields_script_fields;
}

?>
