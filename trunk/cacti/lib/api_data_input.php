<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

function api_data_input_save($id, $name, $input_string, $type_id) {
	$save["id"] = $id;
	$save["hash"] = get_hash_data_input($id);
	$save["name"] = form_input_validate($name, "name", "", false, 3);
	$save["input_string"] = form_input_validate($input_string, "input_string", "", true, 3);
	$save["type_id"] = form_input_validate($type_id, "type_id", "", true, 3);

	$data_input_id = 0;

	if (!is_error_message()) {
		$data_input_id = sql_save($save, "data_input");

		if ($data_input_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $data_input_id;
}

function api_data_input_field_save($id, $data_input_id, $name, $data_name, $input_output, $update_rra, $type_code,
	$regexp_match, $allow_nulls) {
	$save["id"] = $id;
	$save["hash"] = get_hash_data_input($id, "data_input_field");
	$save["data_input_id"] = $data_input_id;
	$save["name"] = form_input_validate($name, "name", "", false, 3);
	$save["data_name"] = form_input_validate($data_name, "data_name", "", false, 3);
	$save["input_output"] = $input_output;
	$save["update_rra"] = form_input_validate($update_rra, "update_rra", "", true, 3);
	$save["type_code"] = form_input_validate($type_code, "type_code", "", true, 3);
	$save["regexp_match"] = form_input_validate($regexp_match, "regexp_match", "", true, 3);
	$save["allow_nulls"] = form_input_validate($allow_nulls, "allow_nulls", "", true, 3);

	$data_input_field_id = 0;

	if (!is_error_message()) {
		$data_input_field_id = sql_save($save, "data_input_fields");

		if ($data_input_field_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $data_input_field_id;
}

function api_data_input_remove($data_input_id) {
	db_execute("delete from data_input where id='$data_input_id'");
	db_execute("delete from data_input_fields where data_input_id='$data_input_id'");
	db_execute("delete from data_input_data where data_input_id='$data_input_id'");
}

function api_data_input_field_remove($data_input_field_id) {
	db_execute("delete from data_input_fields where id='$data_input_field_id'");
	db_execute("delete from data_input_data where data_input_field_id='$data_input_field_id'");
}

?>
