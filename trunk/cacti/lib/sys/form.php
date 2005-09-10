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

function init_post_field_cache() {
	$_SESSION["sess_field_values"] = $_POST;
}

function kill_post_field_cache() {
	kill_session_var("sess_field_values");
}

function isset_post_cache_field($field_name) {
	return isset($_SESSION["sess_field_values"][$field_name]);
}

function get_post_cache_field($field_name) {
	return $_SESSION["sess_field_values"][$field_name];
}

function register_field_errors($error_fields) {
	/* mark this field with an input error */
	$_SESSION["sess_error_fields"][$field_name] = 1;

	/* raise the error message */
	raise_message(3);
}

/* form_input_validate - validates the value of a form field and takes the appropriate action if the input
     is not valid
   @arg $field_value - the value of the form field
   @arg $field_name - the name of the $_POST field as specified in the HTML
   @arg $regexp_match - (optionally) enter a regular expression to match the value against
   @arg $allow_nulls - (bool) whether to allow an empty string as a value or not
   @arg $custom_message - (int) the ID of the message to raise upon an error which is defined in the
     $messages array in 'include/config_arrays.php'
   @returns - the original $field_value */
function form_input_validate($field_value, $field_name, $regexp_match, $allow_nulls, $custom_message = 0) {
	if (($allow_nulls == true) && ($field_value == "")) {
		return true;
	}

	/* php 4.2+ complains about empty regexps */
	if (empty($regexp_match)) { $regexp_match = ".*"; }

	if ((!ereg($regexp_match, $field_value) || (($allow_nulls == false) && ($field_value == "")))) {
		if ($custom_message == 3) {
			return $field_value;
		}else{
			return false;
		}
	}else{
		if ($custom_message == 3) {
			return $field_value;
		}else{
			return true;
		}
	}
}

function get_get_var_number($name) {
	if (isset($_GET[$name])) {
		if (is_numeric($_GET[$name])) {
			return $_GET[$name];
		}else{
			return "0";
		}
	}else{
		return "0";
	}
}

function get_get_var($name) {
	if (isset($_GET[$name])) {
		return $_GET[$name];
	}else{
		return "";
	}
}

?>
