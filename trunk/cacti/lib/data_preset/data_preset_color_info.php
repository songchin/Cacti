<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

function api_data_preset_color_list() {
	return db_fetch_assoc("select * from preset_color order by hex");
}

function api_data_preset_color_get($preset_color_id) {
	/* sanity checks */
	validate_id_die($preset_color_id, "preset_color_id");

	return db_fetch_row("select * from preset_color where id = " . sql_sanitize($preset_color_id));
}

function &api_data_preset_color_form_list() {
	require(CACTI_BASE_PATH . "/include/data_preset/data_preset_color_form.php");

	return $fields_data_preset_color;
}

?>
