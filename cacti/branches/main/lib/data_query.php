<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

function run_data_query($host_id, $snmp_query_id) {
	global $config;

	include_once(CACTI_BASE_PATH . "/lib/poller.php");
	include_once(CACTI_BASE_PATH . "/lib/utility.php");

	debug_log_insert("data_query", __("Running data query") . " [$snmp_query_id].");
	$type_id = db_fetch_cell("select data_input.type_id from (snmp_query,data_input) where snmp_query.data_input_id=data_input.id and snmp_query.id=$snmp_query_id");

	if ($type_id == DATA_INPUT_TYPE_SNMP_QUERY) {
		debug_log_insert("data_query", __("Found type") . " = '3' [snmp query].");
		$result = query_snmp_host($host_id, $snmp_query_id);
	}elseif ($type_id == DATA_INPUT_TYPE_SCRIPT_QUERY) {
		debug_log_insert("data_query", __("Found type") . " = '4 '[script query].");
		$result = query_script_host($host_id, $snmp_query_id);
	}elseif ($type_id == DATA_INPUT_TYPE_QUERY_SCRIPT_SERVER) {
		debug_log_insert("data_query", __("Found type") . " = '6 '[script query].");
		$result = query_script_host($host_id, $snmp_query_id);
	}else{
		debug_log_insert("data_query", __("Unknown type") . " = '$type_id'");
	}

	/* update the sort cache */
	update_data_query_sort_cache($host_id, $snmp_query_id);

	/* update the auto reindex cache */
	update_reindex_cache($host_id, $snmp_query_id);

	/* update the the "local" data query cache */
	update_data_query_cache($host_id, $snmp_query_id);

	/* update the poller cache */
	update_poller_cache_from_query($host_id, $snmp_query_id);

	return (isset($result) ? $result : true);
}

function get_data_query_array($snmp_query_id) {
	global $config, $data_query_xml_arrays;

	include_once(CACTI_BASE_PATH . "/lib/xml.php");

	/* load the array into memory if it hasn't been done yet */
	if (!isset($data_query_xml_arrays[$snmp_query_id])) {
		$xml_file_path = db_fetch_cell("select xml_path from snmp_query where id=$snmp_query_id");
		$xml_file_path = str_replace("<path_cacti>", CACTI_BASE_PATH, $xml_file_path);

		if (!file_exists($xml_file_path)) {
			debug_log_insert("data_query", "Could not find data query XML file at '$xml_file_path'");
			return false;
		}

		debug_log_insert("data_query", "Found data query XML file at '$xml_file_path'");

		$data = implode("",file($xml_file_path));

		$xml_data = xml2array($data);

		/* store the array value to the global array for future reference */
		$data_query_xml_arrays[$snmp_query_id] = $xml_data;
	}

	return $data_query_xml_arrays[$snmp_query_id];
}

function query_script_host($host_id, $snmp_query_id) {
	$script_queries = get_data_query_array($snmp_query_id);

	/* invalid xml check */
	if ((!is_array($script_queries)) || (sizeof($script_queries) == 0)) {
		debug_log_insert("data_query", __("Error parsing XML file into an array."));
		return false;
	}

	debug_log_insert("data_query", __("XML file parsed ok."));

	if (isset($script_queries["script_server"])) {
		$script_queries["script_path"] = "|path_php_binary| -q " . $script_queries["script_path"];
	}

	$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . $script_queries["arg_index"], $script_queries["script_path"], $host_id);

	/* fetch specified index at specified OID */
	$script_index_array = exec_into_array($script_path);

	debug_log_insert("data_query", __("Executing script for list of indexes") . " '$script_path'");

	/* prepare an output array */
	$output_array = array();

	while (list($field_name, $field_array) = each($script_queries["fields"])) {
		if ($field_array["direction"] == "input") {
			$script_path = get_script_query_path((isset($script_queries["arg_prepend"]) ? $script_queries["arg_prepend"] . " ": "") . $script_queries["arg_query"] . " " . $field_array["query_name"], $script_queries["script_path"], $host_id);

			$script_data_array = exec_into_array($script_path);

			debug_log_insert("data_query", __("Executing script query") . " '$script_path'");

			for ($i=0;($i<sizeof($script_data_array));$i++) {
				if (preg_match("/(.*)" . preg_quote($script_queries["output_delimeter"]) . "(.*)/", $script_data_array[$i], $matches)) {
					$script_index = $matches[1];
					$field_value  = $matches[2];

					$output_array[] = data_query_format_record($host_id, $snmp_query_id, $field_name, $field_value, $script_index, '');

					debug_log_insert("data_query", __("Found item [%s='%s'] index: %s", $field_name, $field_value, $script_index));
				}
			}
		}
	}

	if (sizeof($output_array)) {
		data_query_update_host_cache_from_buffer($host_id, $snmp_query_id, $output_array);
	}

	return true;
}

function query_snmp_host($host_id, $snmp_query_id) {
	global $config;

	include_once(CACTI_BASE_PATH . "/lib/snmp.php");

	$host = db_fetch_row("SELECT
		hostname,
		snmp_community,
		snmp_version,
		snmp_username,
		snmp_password,
		snmp_auth_protocol,
		snmp_priv_passphrase,
		snmp_priv_protocol,
		snmp_context,
		snmp_port,
		snmp_timeout,
		ping_retries,
		max_oids
		FROM host
		WHERE id='$host_id'");

	$snmp_queries = get_data_query_array($snmp_query_id);

	if ($host["hostname"] == "") {
		debug_log_insert("data_query", __("Invalid host_id:") . " $host_id");
		return false;
	}

	/* invalid xml check */
	if ((!is_array($snmp_queries)) || (sizeof($snmp_queries) == 0)) {
		debug_log_insert("data_query", __("Error parsing XML file into an array."));
		return false;
	}

	debug_log_insert("data_query", __("XML file parsed ok."));

	/* fetch specified index at specified OID */
	$snmp_indexes = cacti_snmp_walk($host["hostname"], $host["snmp_community"], $snmp_queries["oid_index"],
									$host["snmp_version"], $host["snmp_username"], $host["snmp_password"],
									$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
									$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"],
									$host["ping_retries"], $host["max_oids"], SNMP_WEBUI);

	debug_log_insert("data_query", __("Executing SNMP walk for list of indexes @ '%s'", $snmp_queries["oid_index"]));

	/* no data found; get out */
	if (!$snmp_indexes) {
		debug_log_insert("data_query", __("No SNMP data returned"));
		return false;
	} else {
		/* show list of indices found */
		for ($i=0; $i<sizeof($snmp_indexes); $i++) {
			debug_log_insert("data_query", __("Index found at OID: '%s' value: '%s'", $snmp_indexes[$i]["oid"], $snmp_indexes[$i]["value"]));
		}
	}

	/* the last octet of the oid is the index by default */
	$index_parse_regexp = '.*\.([0-9]+)$';

	/* parse the index if required */
	if (isset($snmp_queries["oid_index_parse"])) {
		$index_parse_regexp = str_replace("OID/REGEXP:", "", $snmp_queries["oid_index_parse"]);

		for ($i=0; $i<sizeof($snmp_indexes); $i++) {
			$snmp_indexes[$i]["value"] = ereg_replace($index_parse_regexp, "\\1", $snmp_indexes[$i]["oid"]);
			debug_log_insert("data_query", __("index_parse at OID: '%s' results: '%s'", $snmp_indexes[$i]["oid"], $snmp_indexes[$i]["value"]));
		}
	}

	/* set an array to host all updates */
	$output_array = array();
	$fields_processed = array();
	rewrite_snmp_enum_value(NULL);

	while (list($field_name, $field_array) = each($snmp_queries["fields"])) {
		$values = array();

		if ((!isset($field_array["oid"])) && ($field_array["source"] == "index")) {
			for ($i=0; $i<sizeof($snmp_indexes); $i++) {
				debug_log_insert("data_query", __("Inserting index data for field '%s' [value='%s']", $field_name, $snmp_indexes[$i]["value"]));
				$values[] = array( "value" => $snmp_indexes[$i]["value"], "index" => $snmp_indexes[$i]["value"], "oid" => $oid);
			}
		}elseif (($field_array["method"] == "get") && ($field_array["direction"] == "input")) {
			debug_log_insert("data_query", __("Located input field '%s' [get]", $field_name));

			$rewritten_indexes = array();
			if (isset($field_array["rewrite_index"])) {
				$rewritten_indexes = data_query_rewrite_indexes($host_id, $snmp_query_id, $field_array["rewrite_index"], $snmp_indexes, $fields_processed);
			}

			for ($i=0; $i<sizeof($snmp_indexes); $i++) {
				$oid = $field_array["oid"];
				if (isset($field_array["rewrite_index"])) {
					if (isset($rewritten_indexes[$snmp_indexes[$i]["value"]])) {
						$oid_suffix = $rewritten_indexes[$snmp_indexes[$i]["value"]];
					}else{
						debug_log_insert("data_query", "Cannot load rewritten index value for original index='" . $snmp_indexes[$i]["value"] . "'");
						continue;
					}
					$oid .= "." . $oid_suffix;
				} else {
					$oid .= "." . $snmp_indexes[$i]["value"];
				}
				$oid .= isset($field_array["oid_suffix"]) ? ("." . $field_array["oid_suffix"]) : "";
				$value = NULL;
				if (substr($field_array["source"], 0, 13) == "VALUE/REGEXP:" || $field_array["source"] == "value") {
					$value = cacti_snmp_get($host["hostname"], $host["snmp_community"], $oid,
	 										$host["snmp_version"], $host["snmp_username"], $host["snmp_password"],
 											$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
 											$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

 					if (substr($field_array["source"], 0, 13) == "VALUE/REGEXP:") {
						$value = ereg_replace(str_replace("VALUE/REGEXP:", "", $field_array["source"]), "\\1", $value);
					}
				} else if(substr($field_array["source"], 0, 11) == "OID/REGEXP:") {
					$value = ereg_replace(str_replace("OID/REGEXP:", "", $field_array["source"]), "\\1", $oid);
				}

				debug_log_insert("data_query", __("Executing SNMP get for data") . " @ '$oid' [value='$value']");

				$values[] = array( "value" => $value, "index" => $snmp_indexes[$i]["value"], "oid" => $oid);
			}
		} elseif (($field_array["method"] == "walk") && ($field_array["direction"] == "input")) {
			debug_log_insert("data_query", __("Located input field '%s' [walk]", $field_name));
			if (isset($field_array["rewrite_index"])) {
				debug_log_insert("data_query", __("ERROR: rewrite_index is not allowed for method=walk"));
				continue;
			} elseif (isset($field_array["oid_suffix"])) {
				debug_log_insert("data_query", __("ERROR: oid_suffix is not allowed for method=walk"));
				continue;
			}

			$snmp_data = array();
			$snmp_data = cacti_snmp_walk($host["hostname"], $host["snmp_community"], $field_array["oid"],
				$host["snmp_version"], $host["snmp_username"], $host["snmp_password"],
				$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
				$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], $host["ping_retries"], $host["max_oids"], SNMP_WEBUI);

			debug_log_insert("data_query", __("Executing SNMP walk for data @ '%s'", $field_array["oid"]));

			if ($field_array["source"] == "value") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$snmp_index = ereg_replace(isset($field_array["oid_index_parse"]) ? $field_array["oid_index_parse"] : $index_parse_regexp, "\\1", $snmp_data[$i]["oid"]);

					$oid = $field_array["oid"] . ".$snmp_index";

					# deprecated in favour of "<rewrite_value>", bug#0001225
					#if ($field_name == "ifOperStatus") {
					#	if ((substr_count(strtolower($snmp_data[$i]["value"]), "down")) ||
					#		($snmp_data[$i]["value"] == "2")) {
					#		$snmp_data[$i]["value"] = __("Down");
					#	}else if ((substr_count(strtolower($snmp_data[$i]["value"]), "up")) ||
					#		($snmp_data[$i]["value"] == "1")) {
					#		$snmp_data[$i]["value"] = __("Up");
					#	}else if ((substr_count(strtolower($snmp_data[$i]["value"]), "notpresent")) ||
					#		($snmp_data[$i]["value"] == "6")) {
					#		$snmp_data[$i]["value"] = __("notPresent");
					#	}else{
					#		$snmp_data[$i]["value"] = __("Testing");
					#	}
					#}

					debug_log_insert("data_query", __("Found item [$field_name='%s'] index: %s [from value]", $snmp_data[$i]["value"], $snmp_index));

					$values[] = array( "value" => $snmp_data[$i]["value"], "index" => $snmp_index, "oid" => $oid);
				}
			} elseif (substr($field_array["source"], 0, 11) == "OID/REGEXP:") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$value = ereg_replace(str_replace("OID/REGEXP:", "", $field_array["source"]), "\\1", $snmp_data[$i]["oid"]);

					if (isset($snmp_queries["oid_index_parse"])) {
						$snmp_index = ereg_replace($index_parse_regexp, "\\1", $snmp_data[$i]["oid"]);
					}else if ((isset($snmp_data[$i]["value"])) && ($snmp_data[$i]["value"] != "")) {
						$snmp_index = $snmp_data[$i]["value"];
					}

					$oid = $field_array["oid"] .  "." . $value;

					debug_log_insert("data_query", __("Found item [%s='%s'] index: %s [from regexp oid parse]", $field_name, $value, $snmp_index));

					$values[] = array( "value" => $value, "index" => $snmp_index, "oid" => $oid);
				}
			}elseif (substr($field_array["source"], 0, 13) == "VALUE/REGEXP:") {
				for ($i=0; $i<sizeof($snmp_data); $i++) {
					$value = ereg_replace(str_replace("VALUE/REGEXP:", "", $field_array["source"]), "\\1", $snmp_data[$i]["value"]);
					$snmp_index = ereg_replace($index_parse_regexp, "\\1", $snmp_data[$i]["oid"]);
					$oid = $field_array["oid"] .  "." . $value;

					debug_log_insert("data_query", __("Found item [%s='%s'] index: %s [from regexp value parse]", $field_name, $value, $snmp_index));

					$values[] = array( "value" => $value, "index" => $snmp_index, "oid" => $oid);
				}
			}
		}

		if (sizeof($values)) {
			foreach($values as $item){
				if(isset($field_array["rewrite_value"])) {
					$item["value"] = rewrite_snmp_enum_value($field_name, $item["value"], $field_array["rewrite_value"]);
				}
				$output_array[] = data_query_format_record($host_id, $snmp_query_id, $field_name, $item["value"], $item["index"], $item["oid"]);
			}
			$fields_processed[] = $field_name;
		}
	}

	if (sizeof($output_array)) {
		data_query_update_host_cache_from_buffer($host_id, $snmp_query_id, $output_array);
	}
	return true;
}

function data_query_format_record($host_id, $snmp_query_id, $field_name, $value, $snmp_index, $oid) {
	global $cnn_id;

	return "($host_id, $snmp_query_id, " . $cnn_id->qstr($field_name) . ", " . $cnn_id->qstr($value) . ", " . $cnn_id->qstr($snmp_index) . ", " . $cnn_id->qstr($oid) . ", 1)";
}

function data_query_update_host_cache_from_buffer($host_id, $snmp_query_id, &$output_array) {
	/* set all fields present value to 0, to mark the outliers when we are all done */
	db_execute("UPDATE host_snmp_cache SET present=0 WHERE host_id='$host_id' AND snmp_query_id='$snmp_query_id'");

	/* setup the database call */
	$sql_prefix   = "INSERT INTO host_snmp_cache (host_id, snmp_query_id, field_name, field_value, snmp_index, oid, present) VALUES";
	$sql_suffix   = " ON DUPLICATE KEY UPDATE field_value=VALUES(field_value), oid=VALUES(oid), present=VALUES(present)";

	/* use a reasonable insert buffer, the default is 1MByte */
	$max_packet   = 256000;

	/* setup somme defaults */
	$overhead     = strlen($sql_prefix) + strlen($sql_suffix);
	$buf_len      = 0;
	$buf_count    = 0;
	$buffer       = "";

	foreach($output_array as $record) {
		if ($buf_count == 0) {
			$delim = " ";
		} else {
			$delim = ", ";
		}

		$buffer .= $delim . $record;

		$buf_len += strlen($record);

		if (($overhead + $buf_len) > ($max_packet - 1024)) {
			db_execute($sql_prefix . $buffer . $sql_suffix);

			$buffer    = "";
			$buf_len   = 0;
			$buf_count = 0;
		} else {
			$buf_count++;
		}
	}

	if ($buf_count > 0) {
		db_execute($sql_prefix . $buffer . $sql_suffix);
	}

	/* remove stale records from the host cache */
	db_execute("DELETE FROM host_snmp_cache WHERE host_id='$host_id' AND snmp_query_id='$snmp_query_id' AND present='0'");
}

/* data_query_index - returns an array containing the data query ID and index value given
	a data query index type/value combination and a host ID
   @arg $index_type - the name of the index to match
   @arg $index_value - the value of the index to match
   @arg $host_id - (int) the host ID to match
   @arg $data_query_id - (int) the data query ID to match
   @returns - (array) the data query ID and index that matches the three arguments */
function data_query_index($index_type, $index_value, $host_id, $data_query_id) {
	return db_fetch_cell("select
		host_snmp_cache.snmp_index
		from host_snmp_cache
		where host_snmp_cache.field_name='$index_type'
		and host_snmp_cache.field_value='" . addslashes($index_value) . "'
		and host_snmp_cache.host_id='$host_id'
		and host_snmp_cache.snmp_query_id='$data_query_id'");
}

/* data_query_field_list - returns an array containing data query information for a given data source
   @arg $data_template_data_id - the ID of the data source to retrieve information for
   @returns - (array) an array that looks like:
	Array
	(
	   [index_type] => ifIndex
	   [index_value] => 3
	   [output_type] => 13
	) */
function data_query_field_list($data_template_data_id) {
	if (!is_numeric($data_template_data_id)) {
		return 0;
	}

	$field = db_fetch_assoc("select
		data_input_fields.type_code,
		data_input_data.value
		from (data_input_fields,data_input_data)
		where data_input_fields.id=data_input_data.data_input_field_id
		and data_input_data.data_template_data_id=$data_template_data_id
		and (data_input_fields.type_code='index_type' or data_input_fields.type_code='index_value' or data_input_fields.type_code='output_type')");
	$field = array_rekey($field, "type_code", "value");

	if ((!isset($field["index_type"])) || (!isset($field["index_value"])) || (!isset($field["output_type"]))) {
		return 0;
	}else{
		return $field;
	}
}

/* encode_data_query_index - encodes a data query index value so that it can be included
	inside of a form
   @arg $index - the index name to encode
   @returns - the encoded data query index */
function encode_data_query_index($index) {
	return md5($index);
}

/* decode_data_query_index - decodes a data query index value so that it can be read from
	a form
   @arg $encoded_index - the index that was encoded with encode_data_query_index()
   @arg $data_query_id - the id of the data query that this index belongs to
   @arg $encoded_index - the id of the host that this index belongs to
   @returns - the decoded data query index */
function decode_data_query_index($encoded_index, $data_query_id, $host_id) {
	/* yes, i know MySQL has a MD5() function that would make this a bit quicker. however i would like to
	keep things abstracted for now so Cacti works with ADODB fully when i get around to porting my db calls */
	$indexes = db_fetch_assoc("select snmp_index from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id  group by snmp_index");

	if (sizeof($indexes) > 0) {
	foreach ($indexes as $index) {
		if (encode_data_query_index($index["snmp_index"]) == $encoded_index) {
			return $index["snmp_index"];
		}
	}
	}
}

/* update_data_query_cache - updates the local data query cache for each graph and data
	source tied to this host/data query
   @arg $host_id - the id of the host to refresh
   @arg $data_query_id - the id of the data query to refresh */
function update_data_query_cache($host_id, $data_query_id) {
	$graphs = db_fetch_assoc("select id from graph_local where host_id = '$host_id' and snmp_query_id = '$data_query_id'");

	if (sizeof($graphs) > 0) {
		foreach ($graphs as $graph) {
			update_graph_data_query_cache($graph["id"]);
		}
	}

	$data_sources = db_fetch_assoc("select id from data_local where host_id = '$host_id' and snmp_query_id = '$data_query_id'");

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			update_data_source_data_query_cache($data_source["id"]);
		}
	}
}

/* update_graph_data_query_cache - updates the local data query cache for a particular
	graph
   @arg $local_graph_id - the id of the graph to update the data query cache for */
function update_graph_data_query_cache($local_graph_id) {
	$host_id = db_fetch_cell("select host_id from graph_local where id=$local_graph_id");

	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from (graph_templates_item,data_template_rrd,data_template_data)
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and graph_templates_item.local_graph_id=$local_graph_id
		limit 0,1"));

	if (empty($field)) { return; }

	$data_query_id = db_fetch_cell("select snmp_query_id from snmp_query_graph where id='" . $field["output_type"] . "'");

	$index = data_query_index($field["index_type"], $field["index_value"], $host_id, $data_query_id);

	if (($data_query_id != "0") && ($index != "")) {
		db_execute("update graph_local set snmp_query_id='$data_query_id',snmp_index='$index' where id=$local_graph_id");

		/* update graph title cache */
		update_graph_title_cache($local_graph_id);
	}
}

/* update_data_source_data_query_cache - updates the local data query cache for a particular
	data source
   @arg $local_data_id - the id of the data source to update the data query cache for */
function update_data_source_data_query_cache($local_data_id) {
	$host_id = db_fetch_cell("select host_id from data_local where id=$local_data_id");

	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from data_template_data
		where data_template_data.local_data_id=$local_data_id"));

	if (empty($field)) { return; }

	$data_query_id = db_fetch_cell("select snmp_query_id from snmp_query_graph where id='" . $field["output_type"] . "'");

	$index = data_query_index($field["index_type"], $field["index_value"], $host_id, $data_query_id);

	if (($data_query_id != "0") && ($index != "")) {
		db_execute("update data_local set snmp_query_id='$data_query_id',snmp_index='$index' where id='$local_data_id'");

		/* update data source title cache */
		update_data_source_title_cache($local_data_id);
	}
}

/* get_formatted_data_query_indexes - obtains a list of indexes for a host/data query that
	is sorted by the chosen index field and formatted using the data query index title
	format
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to retrieve a list of indexes for
   @returns - an array formatted like the following:
	$arr[snmp_index] = "formatted data query index string" */
function get_formatted_data_query_indexes($host_id, $data_query_id) {
	global $config;

	include_once(CACTI_BASE_PATH . "/lib/sort.php");

	if (empty($data_query_id)) {
		return array("" => __("Unknown Index"));
	}

	/* from the xml; cached in 'host_snmp_query' */
	$sort_cache = db_fetch_row("select sort_field,title_format from host_snmp_query where host_id='$host_id' and snmp_query_id='$data_query_id'");

	/* get a list of data query indexes and the field value that we are supposed
	to sort */
	$sort_field_data = array_rekey(db_fetch_assoc("select
		graph_local.snmp_index,
		host_snmp_cache.field_value
		from (graph_local,host_snmp_cache)
		where graph_local.host_id=host_snmp_cache.host_id
		and graph_local.snmp_query_id=host_snmp_cache.snmp_query_id
		and graph_local.snmp_index=host_snmp_cache.snmp_index
		and graph_local.snmp_query_id=$data_query_id
		and graph_local.host_id=$host_id
		and host_snmp_cache.field_name='" . $sort_cache["sort_field"] . "'
		group by graph_local.snmp_index"), "snmp_index", "field_value");

	/* sort the data using the "data query index" sort algorithm */
	uasort($sort_field_data, "usort_data_query_index");

	$sorted_results = array();

	while (list($snmp_index, $sort_field_value) = each($sort_field_data)) {
		$sorted_results[$snmp_index] = substitute_snmp_query_data($sort_cache["title_format"], $host_id, $data_query_id, $snmp_index);
	}

	return $sorted_results;
}

/* get_formatted_data_query_index - obtains a single index for a host/data query/data query
	index that is formatted using the data query index title format
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query which contains the data query index
   @arg $data_query_index - the index to retrieve the formatted name for
   @returns - a string containing the formatted name for the given data query index */
function get_formatted_data_query_index($host_id, $data_query_id, $data_query_index) {
	/* from the xml; cached in 'host_snmp_query' */
	$sort_cache = db_fetch_row("select sort_field,title_format from host_snmp_query where host_id='$host_id' and snmp_query_id='$data_query_id'");

	return substitute_snmp_query_data($sort_cache["title_format"], $host_id, $data_query_id, $data_query_index);
}

/* get_ordered_index_type_list - builds an ordered list of data query index types that are
	valid given a list of data query indexes that will be checked against the data query
	cache
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to build the type list from
   @arg $data_query_index_array - an array containing each data query index to use when checking
	each data query type for validity. a valid data query type will contain no empty or duplicate
	values for each row in the cache that matches one of the $data_query_index_array
   @returns - an array of data query types either ordered or unordered depending on whether
	the xml file has a manual ordering preference specified */
function get_ordered_index_type_list($host_id, $data_query_id, $data_query_index_array = array()) {
	$raw_xml = get_data_query_array($data_query_id);

	/* invalid xml check */
	if ((!is_array($raw_xml)) || (sizeof($raw_xml) == 0)) {
		return array();
	}

	$xml_outputs = array();

	/* create an SQL string that contains each index in this snmp_index_id */
	$sql_or = array_to_sql_or($data_query_index_array, "snmp_index");

	/* check for nonunique query parameter, set value */
	if (isset($raw_xml["index_type"])) {
		if ($raw_xml["index_type"] == "nonunique") {
			$nonunique = 1;
		}else{
			$nonunique = 0;
		}
	} else {
		$nonunique = 0;
	}

	/* list each of the input fields for this snmp query */
	while (list($field_name, $field_array) = each($raw_xml["fields"])) {
		if ($field_array["direction"] == "input") {
			/* create a list of all values for this index */
			if (sizeof($data_query_index_array) == 0) {
				$field_values = db_fetch_assoc("select field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='$field_name'");
			}else{
				$field_values = db_fetch_assoc("select field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='$field_name' and $sql_or");
			}

			/* aggregate the above list so there is no duplicates */
			$aggregate_field_values = array_rekey($field_values, "field_value", "field_value");

			/* fields that contain duplicate or empty values are not suitable to index off of */
			if (!((sizeof($aggregate_field_values) < sizeof($field_values)) || (in_array("", $aggregate_field_values) == true) || (sizeof($aggregate_field_values) == 0)) || ($nonunique)) {
				array_push($xml_outputs, $field_name);
			}
		}
	}

	$return_array = array();

	/* the xml file contains an ordered list of "indexable" fields */
	if (isset($raw_xml["index_order"])) {
		$index_order_array = explode(":", $raw_xml["index_order"]);

		for ($i=0; $i<count($index_order_array); $i++) {
			if (in_array($index_order_array[$i], $xml_outputs)) {
				$return_array[] = $index_order_array[$i];
			}
		}
	/* the xml file does not contain a field list, ignore the order */
	}else{
		for ($i=0; $i<count($xml_outputs); $i++) {
			$return_array[] = $xml_outputs[$i];
		}
	}

	return $return_array;
}

/* update_data_query_sort_cache - updates the sort cache for a particular host/data query
	combination. this works by fetching a list of valid data query index types and choosing
	the first one in the list. the user can optionally override how the cache is updated
	in the data query xml file
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query update the sort cache for */
function update_data_query_sort_cache($host_id, $data_query_id) {
	$raw_xml = get_data_query_array($data_query_id);

	/* get a list of valid data query types */
	$valid_index_types = get_ordered_index_type_list($host_id, $data_query_id);

	/* something is probably wrong with the data query */
	if (sizeof($valid_index_types) == 0) {
		$sort_field = "";
	}else{
		/* grab the first field off the list */
		$sort_field = $valid_index_types[0];
	}

	/* substitute variables */
	if (isset($raw_xml["index_title_format"])) {
		$title_format = str_replace("|chosen_order_field|", "|query_$sort_field|", $raw_xml["index_title_format"]);
	}else{
		$title_format = "|query_$sort_field|";
	}

	/* update the cache */
	db_execute("update host_snmp_query set sort_field = '$sort_field', title_format = '$title_format' where host_id = '$host_id' and snmp_query_id = '$data_query_id'");
}

/* update_data_query_sort_cache_by_host - updates the sort cache for all data queries associated
	with a particular host. see update_data_query_sort_cache() for details about updating the cache
   @arg $host_id - the id of the host to update the cache for */
function update_data_query_sort_cache_by_host($host_id) {
	$data_queries = db_fetch_assoc("select snmp_query_id from host_snmp_query where host_id = '$host_id'");

	if (sizeof($data_queries) > 0) {
		foreach ($data_queries as $data_query) {
			update_data_query_sort_cache($host_id, $data_query["snmp_query_id"]);
		}
	}
}

/* get_best_data_query_index_type - returns the best available data query index type using the
	sort cache
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query to fetch the best data query index type for
   @returns - a string containing containing best data query index type. this will be one of the
	valid input field names as specified in the data query xml file */
function get_best_data_query_index_type($host_id, $data_query_id) {
	return db_fetch_cell("select sort_field from host_snmp_query where host_id = '$host_id' and snmp_query_id = '$data_query_id'");
}

/* get_script_query_path - builds the complete script query executable path
   @arg $args - the variable that contains any arguments to be appended to the argument
	list (variables will be substituted in this function)
   @arg $script_path - the path on the disk to the script file
   @arg $host_id - the id of the host that this script query belongs to
   @returns - a full path to the script query script containing all arguments */
function get_script_query_path($args, $script_path, $host_id) {
	global $config;

	include_once(CACTI_BASE_PATH . "/lib/variables.php");

	/* get any extra arguments that need to be passed to the script */
	if (!empty($args)) {
		$extra_arguments = substitute_host_data($args, "|", "|", $host_id);
	}else{
		$extra_arguments = "";
	}

	/* get a complete path for out target script */
	return substitute_script_query_path($script_path) . " $extra_arguments";
}

/* data_query_rewrite_indexes - returns array of rewritten indexes
	@arg $host_id
	@arg $snmp_query_id
	@arg $rewrite_index 	- value of <rewrite_index> from data query XML
	@arg $snmp_indexes 		- array of snmp indexes as it used in query_snmp_host() or single index
	@arg $fields_processed 	- array of field names that are processed already in query_snmp_host(),
							  refusing non-processed fields to be used as index rewrite source
	@returns 				- (array) of original snmp indexed associated with rewritten ones */
function data_query_rewrite_indexes($host_id, $snmp_query_id, $rewrite_index, $snmp_indexes, $fields_processed = FALSE) {
	$oid_items = explode(".", $rewrite_index);
	$chain_indexes = array();

	if (sizeof($oid_items)) {
		foreach ($oid_items as $item) {
			$matches = array();
			if (preg_match("/^\|query_([^|]+)\|$/", $item, $matches)) {
				$iv = mysql_escape_string($matches[1]);
				if (is_array($fields_processed) && !in_array($iv, $fields_processed)) {
					debug_log_insert("data_query", __("Field '%s' has not been processed yet, cannot be used as index source", $iv));
					continue;
				}

				$traw_indexes = db_fetch_assoc("SELECT snmp_index,field_value
									FROM host_snmp_cache
									WHERE host_id='$host_id'
									AND snmp_query_id='$snmp_query_id'
									AND field_name='$iv'");
				if (sizeof($traw_indexes) && !isset($chain_indexes[$iv])) {
					$chain_indexes[$iv] = array();
					foreach($traw_indexes as $key => $value){
						$chain_indexes[$iv][$value["snmp_index"]] = $value["field_value"];
					}
				}
			}
		}
	}

	$out = array();
	$numeric_output = FALSE;
	if (!is_array($snmp_indexes)) {
		$snmp_indexes = array(array("value" => $snmp_indexes));
		$numeric_output = TRUE;
	}

	for ($i=0; $i<sizeof($snmp_indexes); $i++) {
		$num_index = $snmp_indexes[$i];
		if(is_array($num_index)){
			$num_index = $num_index["value"];
		}

		$index = $rewrite_index;
		foreach ($chain_indexes as $key => $values) {
			if (isset($values[$snmp_indexes[$i]["value"]])) {
				$index = str_replace("|query_$key|", trim($values[$snmp_indexes[$i]["value"]]), $index);
			} else {
				debug_log_insert("data_query", __("Cannot load value of '%s' index %s as index for '%s'", $key, $snmp_indexes[$i]["value"], $field_name));
			}
		}

		$index = str_replace("|index|", trim($snmp_indexes[$i]["value"]), $index);
		if (!preg_match("/^[0-9.]*$/", $index)) {
			debug_log_insert("data_query", __("Some parts of rewrite_index field can not be parsed: ") . '$index');
			continue;
		}
		if ($numeric_output) {
			return $index;
		}
		$out[$snmp_indexes[$i]["value"]] = $index;
	}

	return $out;
}

/* rewrite_snmp_enum_value - returns rewritten $value based on rewrite map
   @arg $field_name 	- name of field being rewritten, used for cache purpuses
   @arg $value 			- value to be translated
   @arg $map 			- translation map in serialize() form
   @returns 			- rewritten value if possible, original one otherwise */
function rewrite_snmp_enum_value($field_name, $value=NULL, $map=NULL) {
	static $mapcache = array();

	if ($field_name === NULL) {
		$mapcache = array();
		return NULL;
	}

	$map = unserialize($map);
	if ($map === FALSE || !is_array($map)) {
		debug_log_insert("data_query", __("Cannot parse translation map (rewrite_value)"));
		return $value;
	}

	if (!isset($mapcache[$field_name])) {
		$mapcache[$field_name] = array();
		foreach ($map as $src => $dst) {
			if (preg_match('/^REGEXP(NC)?:(.*)$/', $src, $matches)) {
				if($matches[1] == "NC")
					$src = '/' . str_replace('/', '\/', $matches[2]) .  '/i';
				else
					$src = '/' . str_replace('/', '\/', $matches[1]) .  '/';
			} else {
				$src = '/^' . str_replace('/^', '\/', $src) .  '$/';
			}
			$mapcache[$field_name][$src] = $dst;
		}
	}

	foreach ($mapcache[$field_name] as $src => $dst) {
		if (preg_match($src, $value)) {
			$nvalue = preg_replace($src, $dst, $value);
			debug_log_insert("data_query", __("rewrite_value: ") . "'$value' => '$nvalue'");
			$value = $nvalue;
			break;
		}
	}

	return $value;
}
