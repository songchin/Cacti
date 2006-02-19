<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

require_once(CACTI_BASE_PATH . "/include/auth/auth_constants.php");

/**
 * Get total number of users
 *
 * Given filter array, return the number of records
 *
 * @param array $filter_array filter array, field => value elements
 * @return int total number of records
 */
function api_auth_control_total_get ($filter_array = "") {

	$sql_where = " WHERE object_type = " . AUTH_CONTROL_OBJECT_TYPE_USER . " ";

	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_auth_control_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where .= sql_filter_array_to_where_string($filter_array, api_log_form_list(), true);
		}
	}

	return db_fetch_cell("SELECT count(*) FROM auth_control $sql_where");

}


/**
 * List log records
 *
 * Given filter array, return list of user records
 *
 * @param array $filter_array filter array, field => value elements
 * @return array user records
 */
function api_auth_control_list ($filter_array, $limit = -1, $offset = -1) {

	$sql_where = " WHERE object_type = " . AUTH_CONTROL_OBJECT_TYPE_USER . " ";

	/* validation and setup for the WHERE clauses */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = validate_auth_control_fields(sql_filter_array_to_field_array($filter_array), "|field|");

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;

		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where .= sql_filter_array_to_where_string($filter_array, api_auth_user_form_list(), false);
		}

	}

        return db_fetch_assoc("SELECT * FROM (auth_control) $sql_where ORDER BY auth_control.name DESC",$limit,$offset);

}


/**
 * Returns information about an auth control entry
 *
 * Returns information array for a given auth control entries, or single requested value.
 *
 * @return array fields => values or value, false on error
 */
function api_auth_control_get($control_type, $control_id, $data_field = "") {

	/* include required arrays */
	require_once(CACTI_BASE_PATH . "/include/auth/auth_arrays.php");

	/* Validate input */
	if (!is_numeric($control_id)) {
		return false;
	}
	if (!is_numeric($control_type)) {
		return false;
	}

	/* Setup variables */
	$value = false;
	$expired = true;
	$session = false;
	$user = false;
	if ($control_type == AUTH_CONTROL_OBJECT_TYPE_USER) {
		$user = true;
		$data_fields = $auth_control_data_user_fields;
	} elseif ($control_type == AUTH_CONTROL_OBJECT_TYPE_GROUP) {
		$data_fields = $auth_control_data_group_fields;
	}else{
		$data_fields = array();
	}

	/* Control record */
	$control = db_fetch_row("SELECT * FROM (auth_control) WHERE id = " . $control_id . " AND object_type = " . $control_type);
	if (sizeof($control) == 0) {
		return false;
	}

	/* Check session for variable and that we can use the session */
	if ((isset($_SESSION["auth_control_user_id"])) && ($user)) {
		if ($_SESSION["auth_control_user_id"] == $control_id) {
			if (isset($_SESSION["auth_data"])) {
				print_a($_SESSION["auth_data"]);
				$session = true;
			}
		}
	}

	/* Check update to user record if active session*/
	if ((isset($_SESSION["auth_data"]["updated_when"])) && ($session) && ($user)) {
		if ($_SESSION["auth_data"]["updated_when"] == $control["updated_when"]) {
			$expired = false;
		}
	}

	/* Get the requested data */
	if (! empty($data_field)) {
		/* single value return */

		/* Get the value from the session else go to the database, if we are allowed to use the session */
		if ((isset($_SESSION["auth_data"][$data_field])) && (! $expired) && ($session)) {
			$value = $_SESSION["auth_data"][$data_field];
		}else{
			$data = db_fetch_row("SELECT * FROM (auth_data) WHERE control_id = " . $control_id . " AND name = '" . sql_sanitize($data_field) . "'");
			if (isset($data[$data_field])) {
				$value = $data["value"];
			}else{
				if (isset($data_fields[$data_field])) {
					/* use default value */
					$value = $data_fields[$data_field];
				}else{
					/* data field not found */
					return false;
				}
			}

			/* put the value into the session if we are using sessions */
			if (($session) && ($user)) {
				$_SESSION["auth_data"][$data_field] = $value;
			}
		}

	}else{
		/* multi value return */

		/* Get the values from the session else go to the database, if we are allowed to use the session */
		if ((! $expired) && ($session)) {
			/* get current session variables */
			$value = $_SESSION["auth_data"];

		}else{
			/* set control data */
			$value = $control;

			/* get control data for this control id */
			$data = db_fetch_assoc("SELECT * FROM (auth_data) WHERE control_id = " . $control_id . " AND name in('" . implode("','",array_keys($data_fields)). "')");
			if (sizeof($data) > 0) {
				foreach ($data as $db_row) {
					$value[$db_row["name"]] = $db_row["value"];
				}
			}

		}

		/* check that required control data values are present */
		$db_values = array();
		foreach ($data_fields as $key => $default_value) {
			if (! array_key_exists($key, $value)) {
				/* default value not set, let's get it */
				if (sizeof($db_values) == 0) {
					$control_data = db_fetch_assoc("SELECT * FROM (auth_data) WHERE control_id = " . $control_id);
					foreach ($control_data as $data_row) {
						$db_values[$data_row["name"]] = $data_row["value"];
					}
				}
				if (array_key_exists($key, $db_values)) {
					$value[$key] = $db_values[$key];
				}else{
					$value[$key] = $data_fields[$key];
				}
			}
		}

		/* update session values if needed */
		if (($session) && ($user)) {
			$_SESSION["auth_data"] = $value;
		}

	}

	return $value;

}




/**
 * Returns list of fields in the auth control form
 *
 * Returns list of fields in the auth control form for validation
 *
 * @return array log fields
 */
function api_auth_control_form_list() {
	require(CACTI_BASE_PATH . "/include/auth/auth_form.php");

	return $fields_auth_control;

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
function validate_auth_control_fields(&$_field_auth_control, $auth_control_field_name_format = "|field|") {

	if (sizeof($_field_auth_control) == 0) {

		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_device = api_auth_control_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_device)) {
		if ((isset($_field_auth_user[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $auth_control_field_name_format);

			if (!form_input_validate($_field_auth_control[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

?>
