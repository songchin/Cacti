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
     $messages array in 'include/global_arrays.php'
   @returns - the original $field_value */
function form_input_validate($field_value, $field_name, $regexp_match, $allow_empty, $custom_message = 0) {
	if (($allow_empty == true) && ($field_value == "")) {
		if ($custom_message == 3) {
			return $field_value;
		}else{
			return true;
		}
	}

	/* php 4.2+ complains about empty regexps */
	if (empty($regexp_match)) { $regexp_match = ".*"; }

	if ((!ereg($regexp_match, $field_value) || (($allow_empty == false) && ($field_value == "")))) {
		if ($custom_message == 3) {
			return $field_value;
		}else{
			api_log_log("Field validation error occured for field '$field_name' value '$field_value' pattern '$regexp_match' allow empty '" . ($allow_empty == false ? "no" : "yes") . "' in ". __FUNCTION__ . "()", SEV_NOTICE);

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

function get_get_var_number($name, $default_value = "0") {
	if (isset($_GET[$name])) {
		if (is_numeric($_GET[$name])) {
			return $_GET[$name];
		}else{
			return $default_value;
		}
	}else{
		return $default_value;
	}
}

function get_get_var($name) {
	if (isset($_GET[$name])) {
		return $_GET[$name];
	}else{
		return "";
	}
}

function isset_get_var($name) {
	return isset($_GET[$name]);
}

function build_get_url_string($name_list) {
	$url_string = "";

	if (sizeof($name_list) > 0) {
		foreach ($name_list as $get_field_name) {
			if (isset_get_var($get_field_name)) {
				$url_string .= ($url_string == "" ? "?" : "&") . "$get_field_name=" . urlencode(get_get_var($get_field_name));
			}
		}
	}

	return $url_string;
}

?>