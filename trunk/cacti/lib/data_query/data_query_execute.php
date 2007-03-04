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

function api_data_query_execute($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_update.php");
	require_once(CACTI_BASE_PATH . "/lib/poller.php");

	/* get information about the data query */
	$data_query = api_data_query_get($data_query_id);

	debug_log_insert("data_query", "Running data query [$data_query_id].");

	if ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
		debug_log_insert("data_query", _("Found type = '1' [snmp query]."));
		$result = api_data_query_snmp_execute($host_id, $data_query_id);
	}elseif ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) {
		debug_log_insert("data_query", _("Found type = '2 '[script query]."));
		$result = api_data_query_script_execute($host_id, $data_query_id);
	}elseif ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) {
		debug_log_insert("data_query", _("Found type = '3 '[php script server query]."));
		$result = api_data_query_script_execute($host_id, $data_query_id);
	}else{
		debug_log_insert("data_query", sprintf(_("Unknown type = '%i'"), $data_query["input_type"]));
	}

	/* update the sort cache */
	update_data_query_sort_cache($host_id, $data_query_id);

	/* update the auto reindex cache */
	update_reindex_cache($host_id, $data_query_id);

	/* update the poller cache */
	update_poller_cache_from_query($host_id, $data_query_id);

	return (isset($result) ? $result : true);
}

function api_data_query_script_execute($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/exec.php");

	/* get information about the data query */
	$data_query = api_data_query_get($data_query_id);

	/* get a list of all input fields defined for this data query */
	$data_query_fields = api_data_query_field_list($data_query_id, DATA_QUERY_FIELD_TYPE_INPUT);

	if ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) {
		$script_path = api_data_query_script_path_format("|path_php_binary| -q " . $data_query["script_path"]);
	}else{
		$script_path = api_data_query_script_path_format($data_query["script_path"]);
	}

	/* fetch a list of indexes for this data query */
	$field_values{$data_query["index_field_id"]} = api_data_query_script_execute_field($host_id, $data_query["index_field_id"], $script_path);

	if (($field_values{$data_query["index_field_id"]} === false) || (sizeof($field_values{$data_query["index_field_id"]}) == 0)) {
		debug_log_insert("data_query", _("No indexes returned, cannot continue."));
		return false;
	}

	/* reindex the parsed index values as a hash (value->oid) for quicker access. DUPLICATE INDEX VALUES
	 * WILL CAUSE PROBLEMS HERE */
	foreach ($field_values{$data_query["index_field_id"]} as $result) {
		$index_field_values{$result["value"]} = true;
	}

	/* clear old data from the data query cache */
	db_execute("delete from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));

	if (is_array($data_query_fields)) {
		foreach ($data_query_fields as $data_query_field) {
			/* fetch a list of values for this field (assuming that we haven't already seen it */
			if (!isset($field_values{$data_query_field["id"]})) {
				$field_values{$data_query_field["id"]} = api_data_query_script_execute_field($host_id, $data_query_field["id"], $script_path);
			}else{
				debug_log_insert("data_query", sprintf(_("Executing script for list of values '%s' (cached)"), $script_path . " " . DATA_QUERY_SCRIPT_ARG_QUERY . " " . $data_query_field["source"]));
			}

			/* see if we have some output to play with */
			if (($field_values{$data_query_field["id"]} !== false) && (sizeof($field_values{$data_query_field["id"]}) > 0)) {
				foreach ($field_values{$data_query_field["id"]} as $found_index => $result) {
					/* a match for this index has been located */
					if (isset($index_field_values[$found_index])) {
						debug_log_insert("data_query", sprintf(_("Found value [%s = '%s'] for index [%s]"), $data_query_field["name"], $result["value"], $found_index));

						db_insert("host_data_query_cache",
							array(
								"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $host_id),
								"data_query_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_query_id),
								"field_name" => array("type" => DB_TYPE_STRING, "value" => $data_query_field["name"]),
								"field_value" => array("type" => DB_TYPE_STRING, "value" => $result["value"]),
								"index_value" => array("type" => DB_TYPE_STRING, "value" => $found_index)
								),
							array("host_id", "data_query_id", "field_name", "index_value"));
					/* a match for this index has not been located */
					}else{
						debug_log_insert("data_query", _("Ignoring unknown index '$found_index'."));
					}
				}
			}else{
				debug_log_insert("data_query", _("No values returned from the field '" . $data_query_field["name"] . "', ignoring."));
			}
		}
	}

	return true;
}

function api_data_query_script_execute_field($host_id, $data_query_field_id, $script_path) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	/* fetch information about the data query field */
	$data_query_field = api_data_query_field_get($data_query_field_id);

	/* query a list of values for the script query field */
	$script_field_path = $script_path . " " . DATA_QUERY_SCRIPT_ARG_QUERY . " " . $data_query_field["source"];
	debug_log_insert("data_query", sprintf(_("Executing script for list of values '%s'"), $script_field_path));
	$script_field_array = exec_into_array($script_field_path);

	$values_array = array();
	for ($i = 0; $i < sizeof($script_field_array); $i++) {
		/* parse each row into an index -> value pair */
		if (preg_match("/(.*):(.*)/", $script_field_array[$i], $matches)) {
			$data_query_index = $matches[1];
			$field_value = $matches[2];

			$values_array[$data_query_index] = array("value" => $field_value);
		}
	}

	return $values_array;
}

function api_data_query_snmp_execute($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	/* get information about the data query */
	$data_query = api_data_query_get($data_query_id);

	/* get a list of all input fields defined for this data query */
	$data_query_fields = api_data_query_field_list($data_query_id, DATA_QUERY_FIELD_TYPE_INPUT);

	/* fetch a list of indexes for this data query */
	$field_values{$data_query["index_field_id"]} = api_data_query_snmp_execute_field($host_id, $data_query["index_field_id"]);

	if (($field_values{$data_query["index_field_id"]} === false) || (sizeof($field_values{$data_query["index_field_id"]}) == 0)) {
		debug_log_insert("data_query", _("No indexes returned, cannot continue."));
		return false;
	}

	/* reindex the parsed index values as a hash (value->oid) for quicker access. DUPLICATE INDEX VALUES
	 * WILL CAUSE PROBLEMS HERE */
	foreach ($field_values{$data_query["index_field_id"]} as $result) {
		$index_field_values{$result["value_parsed"]} = true;
	}

	/* clear old data from the data query cache */
	db_execute("delete from host_data_query_cache where host_id = " . sql_sanitize($host_id) . " and data_query_id = " . sql_sanitize($data_query_id));

	if (is_array($data_query_fields)) {
		foreach ($data_query_fields as $field) {
			/* fetch a list of values for this field (assuming that we haven't already seen it */
			if (!isset($field_values{$field["id"]})) {
				$field_values{$field["id"]} = api_data_query_snmp_execute_field($host_id, $field["id"]);
			}else{
				debug_log_insert("data_query", "Walking OID '" . $field["source"] . "' (cached)");
			}

			/* see if we have some output to play with */
			if (($field_values{$field["id"]} !== false) && (sizeof($field_values{$field["id"]}) > 0)) {
				foreach ($field_values{$field["id"]} as $oid => $result) {
					/* stick with the 0.8.x behavior: use the value for the index when the actual value is
					 * derived from the oid */
					if (($field["method_type"] == DATA_QUERY_FIELD_METHOD_OID_OCTET) || ($field["method_type"] == DATA_QUERY_FIELD_METHOD_OID_PARSE)) {
						$expected_index = $result["value"];
					/* find the index at the end of the oid */
					}else{
						$expected_index = substr($oid, strlen($field["source"])+1);
					}

					/* a match for this index has been located */
					if (isset($index_field_values[$expected_index])) {
						debug_log_insert("data_query", sprintf(_("Found value [%s = '%s'] for index [%s]"), $field["name"], $result["value_parsed"], $expected_index));

						db_insert("host_data_query_cache",
							array(
								"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $host_id),
								"data_query_id" => array("type" => DB_TYPE_INTEGER, "value" => $data_query_id),
								"field_name" => array("type" => DB_TYPE_STRING, "value" => $field["name"]),
								"field_value" => array("type" => DB_TYPE_STRING, "value" => $result["value_parsed"]),
								"index_value" => array("type" => DB_TYPE_STRING, "value" => $expected_index),
								"oid" => array("type" => DB_TYPE_STRING, "value" => $oid)
								),
							array("host_id", "data_query_id", "field_name", "index_value"));
					/* a match for this index has not been located */
					}else{
						debug_log_insert("data_query", _("Ignoring unknown index '$expected_index'."));
					}
				}
			}else{
				debug_log_insert("data_query", _("No values returned from the field '" . $field["name"] . "', ignoring."));
			}
		}
	}

	return true;
}

function api_data_query_snmp_execute_field($host_id, $data_query_field_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/snmp.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	/* fetch information about the data query field */
	$data_query_field = api_data_query_field_get($data_query_field_id);

	/* fetch information about the associated device */
	$device = api_device_get($host_id);

	debug_log_insert("data_query", "Walking OID '" . $data_query_field["source"] . "'");

	/* walk the base snmp oid to get a raw list of values */
	$snmp_output = cacti_snmp_walk(
		$device["hostname"],
		$device["snmp_community"],
		$data_query_field["source"],
		$device["snmp_version"],
		$device["snmpv3_auth_username"],
		$device["snmpv3_auth_password"],
		$device["snmpv3_auth_protocol"],
		$device["snmpv3_priv_passphrase"],
		$device["snmpv3_priv_protocol"],
		$device["snmp_port"],
		$device["snmp_timeout"],
		SNMP_WEBUI);

	/* make sure some data has been returned */
	if (!$snmp_output) {
		debug_log_insert("data_query", _("No SNMP data returned when walking OID '" . $data_query_field["source"] . "'"));
		return false;
	}

	debug_log_insert("data_query", _("Parsing output using method type '" . $data_query_field["method_type"] . "'"));

	$values_array = array();
	switch ($data_query_field["method_type"]) {
		/* use the values returned from the snmpwalk without modification */
		case DATA_QUERY_FIELD_METHOD_VALUE:
			foreach ($snmp_output as $row) {
				$values_array{$row["oid"]} = array("value" => $row["value"], "value_parsed" => $row["value"]);
			}

			break;
		/* apply a regular expression to the values returned from the snmpwalk */
		case DATA_QUERY_FIELD_METHOD_VALUE_PARSE:
			foreach ($snmp_output as $row) {
				/* a match was found; grab the first hit */
				if (ereg($data_query_field["method_value"], $row["value"], $matches)) {
					$values_array{$row["oid"]} = array("value" => $row["value"], "value_parsed" => $matches[1]);
				/* no match was found. use an empty string */
				}else{
					$values_array{$row["oid"]} = array("value" => "", "value_parsed" => "");
				}
			}

			break;
		/* use the last N octets of the oid for each value */
		case DATA_QUERY_FIELD_METHOD_OID_OCTET:
			foreach ($snmp_output as $row) {
				$octets = explode(".", $row["oid"]);

				$_new_oid = "";
				/* start at the sizeof(array)-Nth item, and move forward */
				for ($i=$data_query_field["method_value"]; $i>0; $i--) {
					$_new_oid .= $octets{sizeof($octets)-$i} . ($i > 1 ? "." : "");
				}

				$values_array{$row["oid"]} = array("value" => $row["value"], "value_parsed" => $_new_oid);
			}

			break;
		/* apply a regular expression to the oid's returned from the snmpwalk */
		case DATA_QUERY_FIELD_METHOD_OID_PARSE:
			foreach ($snmp_output as $row) {
				/* a match was found; grab the first hit */
				if (ereg($data_query_field["method_value"], $row["oid"], $matches)) {
					$values_array{$row["oid"]} = array("value" => $row["value"], "value_parsed" => $matches[1]);
				/* no match was found. use an empty string */
				}else{
					$values_array{$row["oid"]} = array("value" => "", "value_parsed" => "");
				}
			}

			break;
	}

	return $values_array;
}

?>