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

function run_data_query($host_id, $data_query_id) {
	include_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	include_once(CACTI_BASE_PATH . "/lib/data_query/data_query_update.php");
	include_once(CACTI_BASE_PATH . "/lib/poller.php");

	debug_log_insert("data_query", "Running data query [$data_query_id].");
	$input_type = db_fetch_cell("select input_type from snmp_query where id = $data_query_id");

	if ($input_type == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
		debug_log_insert("data_query", "Found type = '1' [snmp query].");
		$result = query_snmp_host($host_id, $data_query_id);
	}elseif ($input_type == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) {
		debug_log_insert("data_query", "Found type = '2 '[script query].");
		$result = query_script_host($host_id, $data_query_id);
	}elseif ($input_type == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) {
		debug_log_insert("data_query", "Found type = '3 '[php script server query].");
		$result = query_script_host($host_id, $data_query_id);
	}else{
		debug_log_insert("data_query", "Unknown type = '$input_type'");
	}

	/* update the sort cache */
	update_data_query_sort_cache($host_id, $data_query_id);

	/* update the auto reindex cache */
	update_reindex_cache($host_id, $data_query_id);

	/* update the the "local" data query cache */
	//update_data_query_cache($host_id, $data_query_id);

	/* update the poller cache */
	update_poller_cache_from_query($host_id, $data_query_id);

	return (isset($result) ? $result : true);
}

function query_script_host($host_id, $snmp_query_id) {
	include_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	$script_queries = get_data_query_array($snmp_query_id);

	if ($script_queries == false) {
		debug_log_insert("data_query", "Error parsing XML file into an array.");
		return false;
	}

	debug_log_insert("data_query", "XML file parsed ok.");

	if (isset($script_queries["script_server"])) {
		$script_queries["script_path"] = "|path_php_binary| -q " . $script_queries["script_path"];
	}

	$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . $script_queries["arg_index"], $script_queries["script_path"], $host_id);

	/* fetch specified index at specified OID */
	$script_index_array = exec_into_array($script_path);

	debug_log_insert("data_query", "Executing script for list of indexes '$script_path'");

	db_execute("delete from host_snmp_cache where host_id=$host_id and snmp_query_id=$snmp_query_id");

	while (list($field_name, $field_array) = each($script_queries["fields"])) {
		if ($field_array["direction"] == "input") {
			$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . $script_queries["arg_query"] . " " . $field_array["query_name"], $script_queries["script_path"], $host_id);

			$script_data_array = exec_into_array($script_path);

			debug_log_insert("data_query", "Executing script query '$script_path'");

			for ($i=0;($i<sizeof($script_data_array));$i++) {
				if (preg_match("/(.*)" . preg_quote($script_queries["output_delimeter"]) . "(.*)/", $script_data_array[$i], $matches)) {
					$script_index = $matches[1];
					$field_value = $matches[2];

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','$field_value','$script_index','')");

					debug_log_insert("data_query", "Found item [$field_name='$field_value'] index: $script_index");
				}
			}
		}
	}

	return true;
}

function query_snmp_host($host_id, $data_query_id) {
	global $config;

	include_once(CACTI_BASE_PATH . "/lib/snmp.php");
	include_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	$host = db_fetch_row("select hostname,snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_port,snmp_timeout from host where id = $host_id");

	$snmp_query_xml_data = get_data_query_array($data_query_id);

	if ((empty($host["hostname"])) || (sizeof($snmp_query_xml_data) == 0)) {
		debug_log_insert("data_query", "Error parsing XML file into an array.");
		return false;
	}

	debug_log_insert("data_query", "XML file parsed ok.");

	/* fetch specified index at specified OID */
	$snmp_index = cacti_snmp_walk($host["hostname"], $host["snmp_community"], $snmp_query_xml_data["oid_index"], $host["snmp_version"],
		$host["snmpv3_auth_username"], $host["snmpv3_auth_password"], $host["snmpv3_auth_protocol"],
		$host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"], $host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

	debug_log_insert("data_query", "Executing SNMP walk for list of indexes @ '" . $snmp_query_xml_data["oid_index"] . "'");

	/* no data found; get out */
	if (!$snmp_index) {
		debug_log_insert("data_query", "No SNMP data returned");
		return false;
	}

	/* the last octet of the oid is the index by default */
	$index_parse_regexp = '.*\.([0-9]+)$';

	/* parse the index if required */
	if (isset($snmp_query_xml_data["oid_index_parse"])) {
		$index_parse_regexp = str_replace("OID/REGEXP:", "", $snmp_query_xml_data["oid_index_parse"]);

		for ($i=0; $i<sizeof($snmp_index); $i++) {
			$snmp_index[$i]["value"] = ereg_replace($index_parse_regexp, "\\1", $snmp_index[$i]["oid"]);
		}
	}

	db_execute("delete from host_snmp_cache where host_id = $host_id and snmp_query_id = $data_query_id");

	while (list($field_name, $field_array) = each($snmp_query_xml_data["fields"])) {
		if ((!isset($field_array["oid"])) && ($field_array["source"] == "index")) {
			for ($i=0; $i<sizeof($snmp_index); $i++) {
				debug_log_insert("data_query", "Inserting index data [value='" . $snmp_index[$i]["value"] . "']");

				db_execute("replace into host_snmp_cache
					(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
					values ($host_id,$data_query_id,'$field_name','" . $snmp_index[$i]["value"] . "','" . $snmp_index[$i]["value"] . "','')");
			}
		}else if (($field_array["method"] == "get") && ($field_array["direction"] == "input")) {
			debug_log_insert("data_query", "Located input field '$field_name' [get]");

			if ($field_array["source"] == "value") {
				for ($i=0; $i<sizeof($snmp_index); $i++) {
					$oid = $field_array["oid"] .  "." . $snmp_index[$i]["value"];

					$value = cacti_snmp_get($host["hostname"], $host["snmp_community"], $oid, $host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
						$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
						$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

					debug_log_insert("data_query", "Executing SNMP get for data @ '$oid' [value='$value']");

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$data_query_id,$field_name,'$value','" . $snmp_index[$i]["value"] . "','$oid')");
				}
			}
		}else if (($field_array["method"] == "walk") && ($field_array["direction"] == "input")) {
			debug_log_insert("data_query", "Located input field '$field_name' [walk]");

			$snmp_data = array();
			$snmp_data = cacti_snmp_walk($host["hostname"], $host["snmp_community"], $field_array["oid"], $host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
				$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
				$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

			debug_log_insert("data_query", "Executing SNMP walk for data @ '" . $field_array["oid"] . "'");

			if ($field_array["source"] == "value") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$snmp_index = ereg_replace($index_parse_regexp, "\\1", $snmp_data[$i]["oid"]);

					$oid = $field_array["oid"] . ".$snmp_index";

					if ($field_name == "ifOperStatus") {
						if ($snmp_data[$i]["value"] == "down(2)") $snmp_data[$i]["value"] = "Down";
						if ($snmp_data[$i]["value"] == "up(1)") $snmp_data[$i]["value"] = "Up";
					}

					debug_log_insert("data_query", "Found item [$field_name='" . $snmp_data[$i]["value"] . "'] index: $snmp_index [from value]");

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$data_query_id,'$field_name','" . $snmp_data[$i]["value"] . "','$snmp_index','$oid')");
				}
			}elseif (substr($field_array["source"], 0, 11) == "OID/REGEXP:") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$value = ereg_replace(str_replace("OID/REGEXP:", "", $field_array["source"]), "\\1", $snmp_data[$i]["oid"]);

					if ((isset($snmp_data[$i]["value"])) && ($snmp_data[$i]["value"] != "")) {
						$snmp_index = $snmp_data[$i]["value"];
					}

					/* correct bogus index value */
					/* found in some devices such as an EMC Cellera */
					if ($snmp_index == 0) {
						$snmp_index = 1;
					}

					$oid = $field_array["oid"] .  "." . $value;

					debug_log_insert("data_query", "Found item [$field_name='$value'] index: $snmp_index [from regexp oid parse]");

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$data_query_id,'$field_name','$value','$snmp_index','$oid')");
				}
			}elseif (substr($field_array["source"], 0, 13) == "VALUE/REGEXP:") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$value = ereg_replace(str_replace("VALUE/REGEXP:", "", $field_array["source"]), "\\1", $snmp_data[$i]["value"]);
					$snmp_index = ereg_replace($index_parse_regexp, "\\1", $snmp_data[$i]["oid"]);
					$oid = $field_array["oid"] .  "." . $value;

					debug_log_insert("data_query", "Found item [$field_name='$value'] index: $snmp_index [from regexp value parse]");

					db_execute("replace into host_snmp_cache
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$data_query_id,'$field_name','$value','$snmp_index','$oid')");
				}
			}
		}
	}

	return true;
}

?>
