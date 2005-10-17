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
		$result = query_script_host($host_id, $data_query_id);
	}elseif ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) {
		debug_log_insert("data_query", _("Found type = '3 '[php script server query]."));
		$result = query_script_host($host_id, $data_query_id);
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

function query_script_host($host_id, $snmp_query_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/exec.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	//$script_queries = get_data_query_array($snmp_query_id);

	//if ($script_queries == false) {
	//	debug_log_insert("data_query", _("Error parsing XML file into an array."));
	//	return false;
	//}

	//debug_log_insert("data_query", _("XML file parsed ok."));

	if (isset($script_queries["script_server"])) {
		$script_queries["script_path"] = "|path_php_binary| -q " . $script_queries["script_path"];
	}

	$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . DATA_QUERY_SCRIPT_ARG_INDEX, $script_queries["script_path"], $host_id);

	/* fetch specified index at specified OID */
	$script_index_array = exec_into_array($script_path);

	debug_log_insert("data_query", sprintf(_("Executing script for list of indexes '%s'"), $script_path));

	db_execute("delete from host_snmp_cache where host_id=$host_id and snmp_query_id=$snmp_query_id");

	while (list($field_name, $field_array) = each($script_queries["fields"])) {
		if ($field_array["direction"] == "input") {
			$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . DATA_QUERY_SCRIPT_ARG_QUERY . " " . $field_array["query_name"], $script_queries["script_path"], $host_id);

			$script_data_array = exec_into_array($script_path);

			debug_log_insert("data_query", sprintf(_("Executing script query '%s'"), $script_path));

			for ($i=0;($i<sizeof($script_data_array));$i++) {
				if (preg_match("/(.*)" . preg_quote($script_queries["output_delimeter"]) . "(.*)/", $script_data_array[$i], $matches)) {
					$script_index = $matches[1];
					$field_value = $matches[2];

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','$field_value','$script_index','')");

					debug_log_insert("data_query", sprintf(_("Found item [%s='%s'] index: %s"), $field_name, $field_value, $script_index));
				}
			}
		}
	}

	return true;
}

function api_data_query_snmp_execute($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	/* get information about the data query */
	$data_query = api_data_query_get($data_query_id);

	/* get a list of all input fields defined for this data query */
	$data_query_fields = api_data_query_fields_list($data_query_id, DATA_QUERY_FIELD_TYPE_INPUT);

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

	if (sizeof($data_query_fields) > 0) {
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
						$expected_index = substr($oid, strlen($field["source"]));
					}

					/* a match for this index has been located */
					if (isset($index_field_values[$expected_index])) {
						debug_log_insert("data_query", "Found value [" . $field["name"] . " = '" . $result["value_parsed"] . "'] for index [$expected_index]");

						db_insert("host_data_query_cache",
							array(
								"host_id" => array("type" => DB_TYPE_NUMBER, "value" => $host_id),
								"data_query_id" => array("type" => DB_TYPE_NUMBER, "value" => $data_query_id),
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