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

function api_data_preset_package_category_list() {
	return array_rekey(db_fetch_assoc("select * from preset_package_category order by name"), "id", "name");
}

function api_data_preset_package_subcategory_list() {
	return array_rekey(db_fetch_assoc("select * from preset_package_subcategory order by name"), "id", "name");
}

function api_data_preset_package_vendor_list() {
	return array_rekey(db_fetch_assoc("select * from preset_package_vendor order by name"), "id", "name");
}

function api_data_preset_package_category_get($preset_id) {
	/* sanity checks */
	validate_id_die($preset_id, "preset_id");

	return db_fetch_cell("select name from preset_package_category where id = " . sql_sanitize($preset_id));
}

function api_data_preset_package_subcategory_get($preset_id) {
	/* sanity checks */
	validate_id_die($preset_id, "preset_id");

	return db_fetch_cell("select name from preset_package_subcategory where id = " . sql_sanitize($preset_id));
}

function api_data_preset_package_vendor_get($preset_id) {
	/* sanity checks */
	validate_id_die($preset_id, "preset_id");

	return db_fetch_cell("select name from preset_package_vendor where id = " . sql_sanitize($preset_id));
}

?>
