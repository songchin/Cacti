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

/*
 * User viewing actions
 */

/**
 * Get total number of users 
 *
 * Given filter array, return the number of records
 *
 * @param array $filter_array filter array, field => value elements
 * @return int total number of records
 */
function api_auth_user_total_get ($filter_array = "") {

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_auth_user_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where .= sql_filter_array_to_where_string($filter_array, api_log_form_list(), true);
		}
	}

	return db_fetch_cell("select count(*) from auth_user $sql_where");

}

/**
 * List log records
 *
 * Given filter array, return list of user records
 *
 * @param array $filter_array filter array, field => value elements
 * @return array user records
 */
function api_auth_user_list ($filter_array,$limit = -1,$offset = -1) {

	$sql_where = "";
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_log_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;

		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where .= sql_filter_array_to_where_string($filter_array, api_log_form_list(), true);
		}

	}

        return db_fetch_assoc("SELECT * FROM (auth_user) $sql_where order by auth_user.username desc",$limit,$offset);

}


/**
 * Returns list of fields in the auth user form
 *
 * Returns list of fields in the auth user form for validation
 *
 * @return array log fields
 */
function api_auth_user_form_list() {
	require(CACTI_BASE_PATH . "/include/auth/auth_form.php");

	return $fields_auth_user;

}


/**
 * Validates log field values
 *
 * Validates log field values against the log form definitions
 *
 * @param $_fields_log field array
 * @param $log_field_name_format replacement variable
 * @return array error array if any
 */
function validate_auth_user_fields(&$_field_auth_user, $auth_user_field_name_format = "|field|") {

	if (sizeof($_field_auth_user) == 0) {

		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_device = api_log_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_device)) {
		if ((isset($_field_auth_user[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $auth_user_field_name_format);

			if (!form_input_validate($_field_auth_user[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

?>
