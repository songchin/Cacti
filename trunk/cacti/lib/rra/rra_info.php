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

function api_rra_get($rra_id) {
	/* sanity checks */
	validate_id_die($rra_id, "rra_id");

	return db_fetch_row("select * from rra where id = " . sql_sanitize($rra_id));
}

function api_rra_consolidation_function_list($rra_id) {
	/* sanity checks */
	validate_id_die($rra_id, "rra_id");

	return array_rekey(db_fetch_assoc("select * from rra_cf where rra_id = " . sql_sanitize($rra_id)), "", "consolidation_function_id");
}

function &api_rra_form_list() {
	require(CACTI_BASE_PATH . "/include/rra/rra_form.php");

	return $fields_rra;
}

?>
