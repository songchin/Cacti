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

function api_data_preset_cdef_save($data_preset_cdef_id, $_fields_data_preset_cdef) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_cdef_info.php");

	/* sanity checks */
	validate_id_die($data_preset_cdef_id, "data_preset_cdef_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_data_preset_cdef) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $data_preset_cdef_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_preset_cdef, api_data_preset_cdef_form_list());

	if (db_replace("preset_cdef", $_fields, array("id"))) {
		if (empty($data_preset_cdef_id)) {
			$data_preset_cdef_id = db_fetch_insert_id();
		}

		return $data_preset_cdef_id;
	}else{
		return false;
	}
}

function api_data_preset_cdef_remove($data_preset_id) {
	/* sanity checks */
	validate_id_die($data_preset_id, "data_preset_id");

	db_delete("preset_cdef_item",
		array(
			"preset_cdef_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_preset_id)
			));

	db_delete("preset_cdef",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $data_preset_id)
			));
}

?>
