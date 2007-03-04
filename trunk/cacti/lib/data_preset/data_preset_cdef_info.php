<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

function api_data_preset_cdef_list() {
	return db_fetch_assoc("select * from preset_cdef order by name");
}

function api_data_preset_cdef_get($preset_cdef_id) {
	/* sanity checks */
	validate_id_die($preset_cdef_id, "preset_cdef_id");

	return db_fetch_row("select * from preset_cdef where id = " . sql_sanitize($preset_cdef_id));
}

function &api_data_preset_cdef_operator_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_cdef_arrays.php");

	return $cdef_preset_operators;
}

function &api_data_preset_cdef_function_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_cdef_arrays.php");

	return $cdef_preset_functions;
}

function &api_data_preset_cdef_variable_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_cdef_arrays.php");

	return $cdef_preset_variables;
}

function &api_data_preset_cdef_form_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_cdef_form.php");

	return $fields_data_preset_cdef;
}

?>
