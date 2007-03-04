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

function api_data_preset_rra_list() {
	return db_fetch_assoc("select * from preset_rra order by name");
}

function api_data_preset_rra_get($preset_rra_id) {
	/* sanity checks */
	validate_id_die($preset_rra_id, "preset_rra_id");

	return db_fetch_row("select * from preset_rra where id = " . sql_sanitize($preset_rra_id));
}

function api_data_preset_rra_item_list($preset_rra_id) {
	/* sanity checks */
	validate_id_die($preset_rra_id, "preset_rra_id");

	return db_fetch_assoc("select * from preset_rra_item where preset_rra_id = " . sql_sanitize($preset_rra_id) . " order by consolidation_function,steps");
}

function api_data_preset_rra_item_get($preset_rra_item_id) {
	/* sanity checks */
	validate_id_die($preset_rra_item_id, "preset_rra_item_id");

	return db_fetch_row("select * from preset_rra_item where id = " . sql_sanitize($preset_rra_item_id));
}

function &api_data_preset_rra_step_type_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_arrays.php");

	return $rra_preset_step_types;
}

function &api_data_preset_rra_row_type_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_arrays.php");

	return $rra_preset_row_types;
}

function &api_data_preset_rra_cf_type_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_arrays.php");

	return $rra_preset_cf_types;
}

function &api_data_preset_rra_form_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_form.php");

	return $fields_data_preset_rra;
}

function &api_data_preset_rra_item_form_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_form.php");

	return $fields_data_preset_rra_item;
}

?>
