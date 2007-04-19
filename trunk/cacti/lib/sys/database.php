<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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
function db_connect_real($host,$user,$pass,$db_name,$db_type, $port = "3306", $retries = 20) {
	global $cnn_id;

	$i = 1;
	$cnn_id = NewADOConnection($db_type);

	$hostport = $host . ":" . $port;

	while ($i <= $retries) {
		if ($cnn_id->PConnect($hostport,$user,$pass,$db_name)) {
			return(1);
		}

		$i++;
		usleep(100000);
	}

	/* Can't log if the database isn't accessable..........  */
	#api_syslog_cacti_log(sprintf(_("Cannot connect to MySQL server on '%s'. Please make sure you have specified a valid MySQL database name in 'include/config.php'."),$host), SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
	die("<br>" . sprintf(_("Cannot connect to MySQL server on '%s'. Please make sure you have specified a valid MySQL database name in 'include/config.php'."), $host));

	return(0);
}

/* db_execute - run an sql query and do not return any output
   @arg $sql - the sql query to execute
   @returns - '1' for success, '0' for error */
function db_execute($sql) {
	global $cnn_id;

	log_save("Executing SQL: $sql", SEV_DEV);

	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		log_save("SQL error: " . $cnn_id->ErrorMsg(), SEV_ERROR);
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

	log_save("Executing SQL: $sql", SEV_DEV);

	$cnn_id->SetFetchMode(ADODB_FETCH_NUM);
	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		log_save("SQL error: " . $cnn_id->ErrorMsg(), SEV_ERROR);
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

	log_save("Executing SQL: $sql", SEV_DEV);

	$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $cnn_id->Execute($sql);

	if ($result === false) {
		log_save("SQL error: " . $cnn_id->ErrorMsg(), SEV_ERROR);
	}else{
		if (!$result->EOF) {
			return $result->fields;
		}
	}

	return false;
}

/* db_fetch_assoc - run a 'select' sql query and return all rows found
   @arg $sql - the sql query to execute
   @arg $limit - limit number of returned row, may not work with union queries
   @arg $offset - offset to start returning rows from
   @returns - the entire result set as a multi-dimensional hash */
function db_fetch_assoc($sql,$limit = -1, $offset = -1) {
	global $cnn_id;

	log_save("Executing SQL: $sql", SEV_DEV);

	$cnn_id->SetFetchMode(ADODB_FETCH_ASSOC);
	if ($limit != -1) {
		$result = $cnn_id->SelectLimit($sql,$limit,$offset);
	}else{
		$result = $cnn_id->Execute($sql);
	}

	if ($result === false) {
		log_save("SQL error: " . $cnn_id->ErrorMsg(), SEV_ERROR);
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

	if (sizeof($fields) > 0) {
		foreach ($fields as $db_field_name => $db_field_value) {
			$new_fields[$db_field_name] = array("type" => DB_TYPE_STRING, "value" => $db_field_value);
		}
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
		$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $keys[$i]  . " = " . sql_get_quoted_string($fields{$keys[$i]}["type"], $fields{$keys[$i]}["value"]);
	}

	/* no rows exist at this key; generate an INSERT statement */
	if (db_fetch_cell("SELECT count(*) FROM $table_name $sql_key_where") == 0) {
		$sql_field_names = ""; $sql_field_values = ""; $i = 0;
		if (sizeof($fields) > 0) {
			foreach ($fields as $db_field_name => $db_field_array) {
				if ($i == 0) {
					$sql_field_names = "(";
					$sql_field_values = "(";
				}

				$sql_field_names .= $db_field_name . ($i == (sizeof($fields) - 1) ? "" : ",");
				$sql_field_values .= sql_get_quoted_string($db_field_array["type"], $db_field_array["value"]) . ($i == (sizeof($fields) - 1) ? "" : ",");

				if ($i == (sizeof($fields) - 1)) {
					$sql_field_names .= ")";
					$sql_field_values .= ")";
				}

				$i++;
			}
		}

		$sql = "INSERT INTO $table_name $sql_field_names VALUES $sql_field_values";
	/* more than one row exists at this key; generate an UPDATE statement */
	}else{
		$sql_set_fields = ""; $i = 0;
		if (sizeof($fields) > 0) {
			foreach ($fields as $db_field_name => $db_field_array) {
				/* do not include the key fields in the SET string */
				if (!in_array($db_field_name, $keys)) {
					$sql_set_fields .= $db_field_name . " = " . sql_get_quoted_string($db_field_array["type"], $db_field_array["value"]) . (($i == (sizeof($fields) - sizeof($keys) - 1)) ? "" : ",");

					$i++;
				}
			}
		}

		/* if there are not any fields to update, log a warning */
		if ($sql_set_fields == "") {
			log_save("Invalid empty update field list for table '$table_name' in " . __FUNCTION__ . "()", SEV_WARNING);
			return false;
		}

		$sql = "UPDATE $table_name SET $sql_set_fields $sql_key_where";
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

function db_insert($table_name, $fields, $keys = "") {
	global $cnn_id, $last_insert_id;

	/* default primary key */
	if (!is_array($keys)) {
		$keys = array("id");
	}

	/* generate a WHERE statement that reflects the list of keys */
	$sql_key_where = "";
	for ($i=0; $i<sizeof($keys); $i++) {
		$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $keys[$i]  . " = " . sql_get_quoted_string($fields{$keys[$i]}["type"], $fields{$keys[$i]}["value"]);
	}

	$sql_field_names = ""; $sql_field_values = ""; $i = 0;
	if (sizeof($fields) > 0) {
		foreach ($fields as $db_field_name => $db_field_array) {
			if ($i == 0) {
				$sql_field_names = "(";
				$sql_field_values = "(";
			}

			$sql_field_names .= $db_field_name . ($i == (sizeof($fields) - 1) ? "" : ",");
			$sql_field_values .= sql_get_quoted_string($db_field_array["type"], $db_field_array["value"]) . ($i == (sizeof($fields) - 1) ? "" : ",");

			if ($i == (sizeof($fields) - 1)) {
				$sql_field_names .= ")";
				$sql_field_values .= ")";
			}

			$i++;
		}
	}

	$sql = "INSERT INTO $table_name $sql_field_names VALUES $sql_field_values";

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
		$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $keys[$i] . " = " . sql_get_quoted_string($fields{$keys[$i]}["type"], $fields{$keys[$i]}["value"]);
	}

	$sql_set_fields = ""; $i = 0;
	if (sizeof($fields) > 0) {
		foreach ($fields as $db_field_name => $db_field_array) {
			/* do not include the key fields in the SET string */
			if (!in_array($db_field_name, $keys)) {
				$sql_set_fields .= $db_field_name . " = " . sql_get_quoted_string($db_field_array["type"], $db_field_array["value"]) . (($i == (sizeof($fields) - sizeof($keys) - 1)) ? "" : ",");

				$i++;
			}
		}
	}

	$sql = "UPDATE $table_name SET $sql_set_fields $sql_key_where";

	/* execute the sql statement and return the result */
	if (db_execute($sql)) {
		return true;
	}else{
		return false;
	}
}

function db_delete($table_name, $fields) {
	/* generate a WHERE statement that reflects the list of keys */
	$sql_key_where = ""; $i = 0;
	if (sizeof($fields) > 0) {
		foreach ($fields as $db_field_name => $db_field_array) {
			$sql_key_where .= ($i == 0 ? "WHERE " : " AND ") . $db_field_name  . " = " . sql_get_quoted_string($db_field_array["type"], $db_field_array["value"]);
			$i++;
		}
	}

	$sql = "DELETE FROM $table_name $sql_key_where";

	/* execute the sql statement and return the result */
	if (db_execute($sql)) {
		return true;
	}else{
		return false;
	}
}

?>
