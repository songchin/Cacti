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

function api_rra_save($rra_id, $_fields_rra) {
	require_once(CACTI_BASE_PATH . "/lib/rra/rra_info.php");

	/* sanity checks */
	validate_id_die($rra_id, "rra_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_rra) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $rra_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_rra, api_rra_form_list());

	if (db_replace("rra", $_fields, array("id"))) {
		if (empty($rra_id)) {
			$rra_id = db_fetch_insert_id();
		}

		return $rra_id;
	}else{
		return false;
	}
}

function api_rra_consolidation_function_id_save($rra_id, $_fields_consolidation_function_id) {
	/* sanity checks */
	validate_id_die($rra_id, "rra_id", true);

	/* clear out the existing template -> RRA mappings */
	db_delete("data_template_rra",
		array(
			"data_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $rra_id)
			));

	/* insert new data template -> RRA mappings */
	if (is_array($_fields_consolidation_function_id) > 0) {
		foreach ($_fields_consolidation_function_id as $consolidation_function_id) {
			db_insert("rra_cf",
				array(
					"consolidation_function_id" => array("type" => DB_TYPE_INTEGER, "value" => $consolidation_function_id),
					"rra_id" => array("type" => DB_TYPE_INTEGER, "value" => $rra_id)
					),
				array("consolidation_function_id", "rra_id"));
		}
	}
}

?>
