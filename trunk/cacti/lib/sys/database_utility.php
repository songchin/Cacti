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

/* array_to_sql_or - loops through a single dimentional array and converts each
     item to a string that can be used in the OR portion of an sql query in the
     following form:
        column=item1 OR column=item2 OR column=item2 ...
   @arg $array - the array to convert
   @arg $sql_column - the column to set each item in the array equal to
   @returns - a string that can be placed in a SQL OR statement */
function array_to_sql_or($array, $sql_column) {
	/* if the last item is null; pop it off */
	if ((empty($array{count($array)-1})) && (sizeof($array) > 1)) {
		array_pop($array);
	}

	if (count($array) > 0) {
		$sql_or = "(";

		for ($i=0;($i<count($array));$i++) {
			$sql_or .= $sql_column . "=" . $array[$i];

			if (($i+1) < count($array)) {
				$sql_or .= " OR ";
			}
		}

		$sql_or .= ")";

		return $sql_or;
	}
}

function sql_filter_array_to_where_string($array, &$master_field_list, $first_where = true) {
	$field_array = array();
	$sql_and = ($first_where == true ? "WHERE" : "AND");
	$sql_where = "";

	if (sizeof($array) > 0) {
		/* loop through each field => value in the field array */
		foreach ($array as $field_name => $field_value) {
			/* if the 'value' is an array itself, traverse it one level down and treat it as an OR group */
			if ((is_array($field_value)) && (sizeof($field_value) > 0)) {
				$sql_or = "";
				$i = 1;
				foreach ($field_value as $or_field_name => $or_field_value) {
					/* translate field names for situations where the database and validation field names differ */
					$db_or_field_name = sql_filter_array_get_database_field_name($or_field_name);
					$vl_or_field_name = sql_filter_array_get_validation_field_name($or_field_name);

					/* make sure that the field exists in the $master_field_list array */
					if (isset($master_field_list[$vl_or_field_name])) {
						$sql_or .= ($sql_or == "" ? "(" : " OR") . " $db_or_field_name " . ($master_field_list[$vl_or_field_name]["data_type"] == DB_TYPE_STRING ? " like '%%" . sql_sanitize($or_field_value) . "%%'" : "= " . sql_get_quoted_string(array("type" => $master_field_list[$vl_or_field_name]["data_type"], "value" => $or_field_value))) . (($i == sizeof($field_value) && $sql_or != "") || (sizeof($field_value) == 1) ? ")" : "");
					}

					$i++;
				}

				/* update the final $sql_where string */
				if ($sql_or != "") {
					$sql_where .= " $sql_and $sql_or";
					$sql_and = "AND";
				}
			/* if the 'value' is not an array, simply handle it as a standalone AND */
			}else{
				/* translate field names for situations where the database and validation field names differ */
				$db_field_name = sql_filter_array_get_database_field_name($field_name);
				$vl_field_name = sql_filter_array_get_validation_field_name($field_name);

				/* make sure that the field exists in the $master_field_list array */
				if (isset($master_field_list[$vl_field_name])) {
					$sql_where .= " $sql_and $db_field_name" . ($master_field_list[$vl_field_name]["data_type"] == DB_TYPE_STRING ? " like '%%" . sql_sanitize($field_value) . "%%'" : "= " . sql_get_quoted_string(array("type" => $master_field_list[$vl_field_name]["data_type"], "value" => $field_value)));
					$sql_and = "AND";
				}
			}
		}
	}

	return $sql_where;
}

function sql_filter_array_to_field_array($array) {
	$field_array = array();
	if (sizeof($array) > 0) {
		/* loop through each field => value in the field array */
		foreach ($array as $field_name => $field_value) {
			/* if the 'value' is an array itself, traverse it one level down */
			if ((is_array($field_value)) && (sizeof($field_value) > 0)) {
				foreach ($field_value as $or_field_name => $or_field_value) {
					/* translate field names for situations where the database and validation field names differ */
					$vl_or_field_name = sql_filter_array_get_validation_field_name($or_field_name);

					if (isset($field_array[$vl_or_field_name])) {
						/* there is a potential security issue here if we allow key collisions since an
						 * attacker could effectivly bypass validation by faking multiple duplicate field
						 * names */
						api_log_log("Key collision found at '$vl_or_field_name' in " . __FUNCTION__ . "()", SEV_WARNING);
						die("Key collision found at '$vl_or_field_name' in " . __FUNCTION__ . "()");
					}else{
						$field_array[$vl_or_field_name] = $or_field_value;
					}
				}
			/* if the 'value' is not an array, simply handle it as a standalone value */
			}else{
				/* translate field names for situations where the database and validation field names differ */
				$vl_field_name = sql_filter_array_get_validation_field_name($field_name);

				if (isset($field_array[$vl_field_name])) {
					/* there is a potential security issue here if we allow key collisions since an
					 * attacker could effectivly bypass validation by faking multiple duplicate field
					 * names */
					api_log_log("Key collision found at '$vl_field_name' in " . __FUNCTION__ . "()", SEV_WARNING);
					die("Key collision found at '$vl_field_name' in " . __FUNCTION__ . "()");
				}else{
					$field_array[$vl_field_name] = $field_value;
				}
			}
		}
	}

	return $field_array;
}

function sql_filter_array_get_database_field_name($name) {
	if (strpos($name, "|")) {
		list($db_field, $validation_field) = explode("|", $name);
		return $db_field;
	}else{
		return $name;
	}
}

function sql_filter_array_get_validation_field_name($name) {
	if (strpos($name, "|")) {
		list($db_field, $validation_field) = explode("|", $name);
		return $validation_field;
	}else{
		return $name;
	}
}

function sql_get_database_field_array($field_list, &$master_field_list) {
	$_fields = array();
	foreach (array_keys($master_field_list) as $field_name) {
		if (isset($field_list[$field_name])) {
			$_fields[$field_name] = array("type" => $master_field_list[$field_name]["data_type"], "value" => $field_list[$field_name]);
		}
	}

	return $_fields;
}

function sql_get_quoted_string($field) {
	if ($field["type"] == DB_TYPE_STRING) {
		return "'" . sql_sanitize($field["value"]) . "'";
	}else if ($field["type"] == DB_TYPE_NUMBER) {
		if (is_numeric($field["value"])) {
			return $field["value"];
		}else{
			api_log_log("Invalid integer column '" . $field . "' value '" . $field["value"] . "' in " . __FUNCTION__ . "()", SEV_WARNING);
			die("Invalid integer column value '$field' in " . __FUNCTION__ . "()");
		}
	}else if ($field["type"] == DB_TYPE_NULL) {
		return "NULL";
	}else if ($field["type"] == DB_TYPE_BLOB) {
		// i think the addslashes() may cause problems for non-mysql dbs, but it wasn't working for me otherwise
		return "'" . addslashes($field["value"]) . "'";
	}else if ($field["type"] == DB_TYPE_HTML_CHECKBOX) {
		if ($field["value"] == "on") {
			return 1;
		}else if ($field["value"] == "") {
			return 0;
		}else if ($field["value"] == "0") {
			return 0;
		}else if ($field["value"] == "1") {
			return 1;
		}else{
			return 0;
		}
	}else if ($field["type"] == DB_TYPE_FUNC_NOW) {
		return "NOW()";
	}else if ($field["type"] == DB_TYPE_FUNC_MD5) {
		return "'" . md5($field["value"]) . "'";
	}else{
		api_log_log("Invalid column type for '" . $field . "' value '" . $field["value"] . "' in " . __FUNCTION__ . "()", SEV_WARNING);
	}

}

/* sql_sanitize - removes and quotes unwanted chars in values passed for use in SQL statements
   @arg $value - value to sanitize
   @return - fixed value */
function sql_sanitize($value) {
	$value = str_replace("'", "''", $value);
	$value = str_replace(";", "", $value);

	return $value;
}

?>
