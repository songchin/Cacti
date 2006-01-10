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

function validate_id_die($argument_value, $argument_name, $allow_empty = false) {
	if ((!is_numeric($argument_value)) || ((empty($argument_value) && ($allow_empty == false)))) {
		die("Invalid input '$argument_value' for '$argument_name' in " . __FUNCTION__ . "()");
	}
}

function db_number_validate($number, $allow_empty = false) {
	$number_str = strval($number);

	/* only allow whole digit numbers */
	for ($i=0; $i<strlen($number_str); $i++) {
		if ((ord(substr($number_str, $i, 1)) < 48) || (ord(substr($number_str, $i, 1)) > 57)) {
			api_log_log("Invalid number '$number' in " . api_log_last_function_get() . "()", SEV_WARNING);
			return false;
		}
	}

	if (($allow_empty === false) && (empty($number))) {
		api_log_log("Invalid (empty) number '$number' in " . api_log_last_function_get() . "()", SEV_WARNING);
		return false;
	}else{
		return true;
	}
}

function db_order_column_validate($column_name) {
	if (preg_match("/^[a-z_]+$/", $column_name)) {
		return true;
	}else{
		api_log_log("Invalid order column name '$column_name' in " . api_log_last_function_get() . "()", SEV_WARNING);
		return false;
	}
}

function db_order_direction_validate($direction) {
	if (($direction == "asc") || ($direction == "desc")) {
		return true;
	}else{
		api_log_log("Invalid order direction '$direction' in " . api_log_last_function_get() . "()", SEV_WARNING);
		return false;
	}
}

?>
