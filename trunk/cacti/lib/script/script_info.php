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

function api_script_get($script_id) {
	/* sanity checks */
	validate_id_die($script_id, "script_id");

	return db_fetch_row("select * from data_input where id = " . sql_sanitize($script_id));
}

function api_script_field_list($script_id) {
	/* sanity checks */
	validate_id_die($script_id, "script_id");

	return db_fetch_assoc("select * from data_input_fields where data_input_id = " . sql_sanitize($script_id));
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
