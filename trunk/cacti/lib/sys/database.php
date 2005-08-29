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

$last_insert_id = 0;

/* db_connect_real - makes a connection to the database server
   @arg $host - the hostname of the database server, 'localhost' if the database server is running
      on this machine
   @arg $user - the username to connect to the database server as
   @arg $pass - the password to connect to the database server with
   @arg $db_name - the name of the database to connect to
   @arg $db_type - the type of database server to connect to, only 'mysql' is currently supported
   @arg $retries - the number a time the server should attempt to connect before failing
   @returns - (bool) '1' for success, '0' for error */
function db_connect_real($host,$user,$pass,$db_name,$db_type, $retries = 20) {
	global $cnn_id;

	$i = 1;
	$cnn_id = NewADOConnection($db_type);

	while ($i <= $retries) {
		if ($cnn_id->PConnect($host,$user,$pass,$db_name)) {
			return(1);
		}

		$i++;
		usleep(100000);
	}

	api_syslog_cacti_log(sprintf(_("Cannot connect to MySQL server on '%s'. Please make sure you have specified a valid MySQL database name in 'include/config.php'."),$host), SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
	die("<br>" . sprintf(_("Cannot connect to MySQL server on '%s'. Please make sure you have specified a valid MySQL database name in 'include/config.php'."), $host));

	return(0);
}

/* db_execute - run an sql query and do not return any output
   @arg $sql - the sql query to execute
   @returns - '1' for success, '0' for error */
function db_execute($sql) {
	global $cnn_id;

	api_syslog_cacti_log("Executing SQL: $sql", SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);

	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		api_syslog_cacti_log("SQL error: " . $cnn_id->ErrorMsg(), SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);
	}else{
		return true;
	}

	return false;
}

/* db_fetch_cell - run a 'select' sql query and return the first column of the
     first row found
   @arg $sql - the sql query to execute
   @arg $col_name - use this column name instead of the first one
   @returns - (bool) the output of the sql query as a single variable */
function db_fetch_cell($sql) {
	global $cnn_id;

	api_syslog_cacti_log("Executing SQL: $sql", SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);

	$cnn_id->SetFetchMode(ADODB_FETCH_NUM);
	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		api_syslog_cacti_log("SQL error: " . $cnn_id->ErrorMsg(), SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);
	}else{
		if (!$result->EOF) {
			return $result->fields[0];
		}
	}

	return false;
}

/* db_fetch_row - run a 'select' sql query and return the first row found
   @arg $sql - the sql query to execute
   @returns - the first row of the result as a hash */
function db_fetch_row($sql) {
	global $cnn_id;

	api_syslog_cacti_log("Executing SQL: $sql", SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);

	$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		api_syslog_cacti_log("SQL error: " . $cnn_id->ErrorMsg(), SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);
	}else{
		if (!$result->EOF) {
			return $result->fields;
		}
	}

	return false;
}

/* db_fetch_assoc - run a 'select' sql query and return all rows found
   @arg $sql - the sql query to execute
   @returns - the entire result set as a multi-dimensional hash */
function db_fetch_assoc($sql) {
	global $cnn_id;

	api_syslog_cacti_log("Executing SQL: $sql", SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);

	$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		api_syslog_cacti_log("SQL error: " . $cnn_id->ErrorMsg(), SEV_DEV, 0, 0, 0, false, FACIL_WEBUI);
	}else{
		$data = array();

		while (!$result->EOF) {
			$data{sizeof($data)} = $result->fields;
			$result->MoveNext();
		}

		return $data;
	}

	return false;
}

/* db_fetch_insert_id - get the last insert_id or auto incriment
   @returns - the id of the last auto incriment row that was created */
function db_fetch_insert_id() {
	global $last_insert_id;

	return $last_insert_id;
}

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

/* sql_save - saves data to an sql table
   @arg $fields - an array containing each column -> value mapping in the row
   @arg $table_name - the name of the table to make the replacement in
   @arg $keys - the primary key(s)
   @returns - the auto incriment id column (if applicable) */
function sql_save($fields, $table_name, $keys = "") {
	global $cnn_id;

	/* default primary key */
	if (!is_array($keys)) {
		$keys = array("id");
	}

	while (list($field_name, $field_value) = each($fields)) {
		$new_fields[$field_name] = array("type" => DB_TYPE_STRING, "value" => $field_value);
	}

	if (db_replace($table_name, $new_fields, $keys)) {
		if (empty($fields["id"])) {
			return $cnn_id->Insert_ID();
		}else{
			return $fields["id"];
		}
	}else{
		return false;
	}
}

function db_replace($table_name, $fields, $keys = "") {
	global $cnn_id, $last_insert_id;

	/* default primary key */
	if (!is_array($keys)) {
		$keys = array("id");
	}

	/* generate a WHERE statement that reflects the list of keys */
	$sql_key_where = "";
	for ($i=0; $i<sizeof($keys); $i++) {
		$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $keys[$i]  . " = " . sql_get_quoted_string($fields{$keys[$i]});
	}

	/* no rows exist at this key; generate an INSERT statement */
	if (db_fetch_cell("select count(*) from $table_name $sql_key_where") == 0) {
		$sql_field_names = ""; $sql_field_values = ""; $i = 0;
		while (list($db_field_name, $db_field_array) = each($fields)) {
			if ($i == 0) {
				$sql_field_names = "(";
				$sql_field_values = "(";
			}

			$sql_field_names .= $db_field_name . ($i == (sizeof($fields) - 1) ? "" : ",");
			$sql_field_values .= sql_get_quoted_string($db_field_array) . ($i == (sizeof($fields) - 1) ? "" : ",");

			if ($i == (sizeof($fields) - 1)) {
				$sql_field_names .= ")";
				$sql_field_values .= ")";
			}

			$i++;
		}

		$sql = "insert into $table_name $sql_field_names values $sql_field_values";
	/* more than one row exists at this key; generate an UPDATE statement */
	}else{
		$sql_set_fields = ""; $i = 0;
		while (list($db_field_name, $db_field_array) = each($fields)) {
			/* do not include the key fields in the SET string */
			if (!in_array($db_field_name, $keys)) {
				$sql_set_fields .= $db_field_name . " = " . sql_get_quoted_string($db_field_array) . (($i == (sizeof($fields) - sizeof($keys) - 1)) ? "" : ",");

				$i++;
			}
		}

		/* if there are not any fields to update, log a warning */
		if ($sql_set_fields == "") {
			api_syslog_cacti_log("Invalid empty update field list for table '$table_name' in " . __FUNCTION__ . "()", SEV_WARNING, 0, 0, 0, false, FACIL_WEBUI);
			return false;
		}

		$sql = "update $table_name set $sql_set_fields $sql_key_where";
	}

	/* execute the sql statement and return the result */
	if (db_execute($sql)) {
		/* cache the inserted id for later use */
		$_last_insert_id = $cnn_id->Insert_ID();

		if (!empty($_last_insert_id)) {
			$last_insert_id = $_last_insert_id;
		}

		return true;
	}else{
		return false;
	}
}

function db_update($table_name, $fields, $keys = "") {
	global $cnn_id;

	/* default primary key */
	if (!is_array($keys)) {
		$keys = array("id");
	}

	/* generate a WHERE statement that reflects the list of keys */
	$sql_key_where = "";
	for ($i=0; $i<sizeof($keys); $i++) {
		$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $keys[$i]  . " = " . sql_get_quoted_string($fields{$keys[$i]});
	}

	$sql_set_fields = ""; $i = 0;
	while (list($db_field_name, $db_field_array) = each($fields)) {
		/* do not include the key fields in the SET string */
		if (!in_array($db_field_name, $keys)) {
			$sql_set_fields .= $db_field_name . " = " . sql_get_quoted_string($db_field_array) . (($i == (sizeof($fields) - sizeof($keys) - 1)) ? "" : ",");

			$i++;
		}
	}

	$sql = "update $table_name set $sql_set_fields $sql_key_where";

	/* execute the sql statement and return the result */
	if (db_execute($sql)) {
		return true;
	}else{
		return false;
	}
}

function sql_get_quoted_string($field) {
	if ($field["type"] == DB_TYPE_STRING) {
		return "'" . sql_sanitize($field["value"]) . "'";
	}else if ($field["type"] == DB_TYPE_NUMBER) {
		if (is_numeric($field["value"])) {
			return $field["value"];
		}else{
			api_syslog_cacti_log("Invalid integer column value '$field' in " . __FUNCTION__ . "()", SEV_WARNING, 0, 0, 0, false, FACIL_WEBUI);
			die("Invalid integer column value '$field' in " . __FUNCTION__ . "()");
		}
	}else if ($field["type"] == DB_TYPE_NULL) {
		return "NULL";
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
