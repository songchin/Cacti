<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

function import_xml_data(&$xml_data, $import_custom_rra_settings) {
	global $config, $hash_type_codes, $hash_version_codes;

	include_once(CACTI_BASE_PATH . "/lib/xml.php");

	$info_array = array();

	$xml_array = xml2array($xml_data);
cacti_log("xml_array", false, "TEST");
	if (sizeof($xml_array) == 0) {
		raise_message(7); /* xml parse error */
		return $info_array;
	}

	while (list($hash, $hash_array) = each($xml_array)) {
		/* parse information from the hash */
		$parsed_hash = parse_xml_hash($hash);
cacti_log("parse_xml_hash", false, "TEST");

		/* invalid/wrong hash */
		if ($parsed_hash == false) { return $info_array; }

		if (isset($dep_hash_cache{$parsed_hash["type"]})) {
			array_push($dep_hash_cache{$parsed_hash["type"]}, $parsed_hash);
		}else{
			$dep_hash_cache{$parsed_hash["type"]} = array($parsed_hash);
		}
	}

	$hash_cache = array();

	/* the order of the $hash_type_codes array is ordered such that the items
	with the most dependencies are last and the items with no dependenecies are first.
	this means dependencies will just magically work themselves out :) */
	reset($hash_type_codes);
	while (list($type, $code) = each($hash_type_codes)) {
		/* do we have any matches for this type? */
		if (isset($dep_hash_cache[$type])) {
			/* yes we do. loop through each match for this type */
			for ($i=0; $i<count($dep_hash_cache[$type]); $i++) {
				$hash_array = $xml_array{"hash_" . $hash_type_codes{$dep_hash_cache[$type][$i]["type"]} . $hash_version_codes{$dep_hash_cache[$type][$i]["version"]} . $dep_hash_cache[$type][$i]["hash"]};
cacti_log("parse_xml_hash: " . $type, false, "TEST");

				switch($type) {
				case 'graph_template':
					$hash_cache += xml_to_graph_template($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'data_template':
					$hash_cache += xml_to_data_template($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache, $import_custom_rra_settings);
					break;
				case 'device_template':
					$hash_cache += xml_to_device_template($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'data_input_method':
					$hash_cache += xml_to_data_input_method($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'data_query':
					$hash_cache += xml_to_data_query($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'gprint_preset':
					$hash_cache += xml_to_gprint_preset($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'cdef':
					$hash_cache += xml_to_cdef($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'vdef':
					$hash_cache += xml_to_vdef($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'xaxis':
					$hash_cache += xml_to_xaxis($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					break;
				case 'round_robin_archive':
					if ($import_custom_rra_settings === true) {
						$hash_cache += xml_to_round_robin_archive($dep_hash_cache[$type][$i]["hash"], $hash_array, $hash_cache);
					}
					break;
				}

				if (isset($_SESSION["import_debug_info"])) {
					$info_array[$type]{isset($info_array[$type]) ? count($info_array[$type]) : 0} = $_SESSION["import_debug_info"];
				}

				kill_session_var("import_debug_info");
			}
		}
	}

	return $info_array;
}

function xml_to_graph_template($hash, &$xml_array, &$hash_cache) {
	global $hash_version_codes;
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	/* import into: graph_templates */
	$_graph_template_id = db_fetch_cell("select id from graph_templates where hash='$hash'");
	$save["id"] = (empty($_graph_template_id) ? "0" : $_graph_template_id);
	$save["hash"] = $hash;
	$save["name"] = $xml_array["name"];
	$graph_template_id = sql_save($save, "graph_templates");

	$hash_cache["graph_template"][$hash] = $graph_template_id;

	/* import into: graph_templates_graph */
	unset($save);
	$save["id"] = (empty($_graph_template_id) ? "0" : db_fetch_cell("select graph_templates_graph.id from (graph_templates,graph_templates_graph) where graph_templates.id=graph_templates_graph.graph_template_id and graph_templates.id=$graph_template_id and graph_templates_graph.local_graph_id=0"));
	$save["graph_template_id"] = $graph_template_id;

	/* parse information from the hash */
	$parsed_hash = parse_xml_hash($hash);

	$struct_graph = graph_form_list();
	reset($struct_graph);
	while (list($field_name, $field_array) = each($struct_graph)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array["graph"]{"t_" . $field_name})) {
			$save{"t_" . $field_name} = $xml_array["graph"]{"t_" . $field_name};
		}

		/* make sure this field exists in the xml array first */
		if (isset($xml_array["graph"][$field_name])) {
			if (($field_name == "unit_exponent_value") && (get_version_index($parsed_hash["version"]) < get_version_index("0.8.5")) && ($xml_array["graph"][$field_name] == "0")) { /* backwards compatability */
				$save[$field_name] = "";
			}else{
				$save[$field_name] = addslashes(xml_character_decode($xml_array["graph"][$field_name]));
			}
		}
	}

	$graph_template_graph_id = sql_save($save, "graph_templates_graph");

	/* import into: graph_templates_item */
	if (is_array($xml_array["items"])) {
		$struct_graph_item = graph_item_form_list();
		while (list($item_hash, $item_array) = each($xml_array["items"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_graph_template_item_id = db_fetch_cell("select id from graph_templates_item where hash='" . $parsed_hash["hash"] . "' and graph_template_id=$graph_template_id and local_graph_id=0");
			$save["id"] = (empty($_graph_template_item_id) ? "0" : $_graph_template_item_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["graph_template_id"] = $graph_template_id;

			reset($struct_graph_item);
			while (list($field_name, $field_array) = each($struct_graph_item)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					/* is the value of this field a hash or not? */
					if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $item_array[$field_name])) {
						$save[$field_name] = resolve_hash_to_id($item_array[$field_name], $hash_cache);
					}elseif (($field_name == "color_id") && (preg_match("/^[a-fA-F0-9]{6}$/", $item_array[$field_name])) && (get_version_index($parsed_hash["version"]) >= get_version_index("0.8.5"))) { /* treat the 'color' field differently */
						$color_id = db_fetch_cell("select id from colors where hex='" . $item_array[$field_name] . "'");

						if (empty($color_id)) {
							db_execute("insert into colors (hex) values ('" . $item_array[$field_name] . "')");
							$color_id = db_fetch_insert_id();
						}

						$save[$field_name] = $color_id;
					}elseif (($field_name == "graph_type_id") && (get_version_index($parsed_hash["version"]) < get_version_index("0.8.8"))) { /* backwards compatability */
						$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name])); # save graph_type_id as is TODO: may recode to LINE instead of LINEx
						# additionally, save line_width as numeric value in intermediate field
						switch ($item_array[$field_name]) {
							case GRAPH_ITEM_TYPE_LINE1:
								$save["_line_width"] = 1;
								break;
							case GRAPH_ITEM_TYPE_LINE2:
								$save["_line_width"] = 2;
								break;
							case GRAPH_ITEM_TYPE_LINE3:
								$save["_line_width"] = 3;
								break;
							default:
								$save["_line_width"] = 0;
						}
					}else{
						$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
					}
				}
			}

			if (!array_key_exists("line_width", $save) && array_key_exists("_line_width", $save)) {
				# no explicit line_width given, but implicit one available
				$save["line_width"] = $save["_line_width"];
			}
			unset($save["_line_width"]); # we won't save the intermediate

			$graph_template_item_id = sql_save($save, "graph_templates_item");

			$hash_cache["graph_template_item"]{$parsed_hash["hash"]} = $graph_template_item_id;
		}
	}


	if (get_version_index($parsed_hash["version"]) < get_version_index("0.8.8")) {
		$graph_template_items = db_fetch_assoc("SELECT * " .
								"FROM graph_templates_item " .
								"WHERE local_graph_id = 0 " .
								"AND graph_template_id = " . $graph_template_id .  " " .
								"ORDER BY graph_template_id ASC, sequence ASC");
		update_pre_088_graph_items($graph_template_items);
	}


	/* import into: graph_template_input */
	$fields_graph_template_input_edit = graph_template_input_form_list();
	if (is_array($xml_array["inputs"])) {
		while (list($item_hash, $item_array) = each($xml_array["inputs"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_graph_template_input_id = db_fetch_cell("select id from graph_template_input where hash='" . $parsed_hash["hash"] . "' and graph_template_id=$graph_template_id");
			$save["id"] = (empty($_graph_template_input_id) ? "0" : $_graph_template_input_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["graph_template_id"] = $graph_template_id;

			reset($fields_graph_template_input_edit);
			while (list($field_name, $field_array) = each($fields_graph_template_input_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
				}
			}

			$graph_template_input_id = sql_save($save, "graph_template_input");

			$hash_cache["graph_template_input"]{$parsed_hash["hash"]} = $graph_template_input_id;

			/* import into: graph_template_input_defs */
			$hash_items = explode("|", $item_array["items"]);

			if (!empty($hash_items[0])) {
				for ($i=0; $i<count($hash_items); $i++) {
					/* parse information from the hash */
					$parsed_hash = parse_xml_hash($hash_items[$i]);

					/* invalid/wrong hash */
					if ($parsed_hash == false) { return false; }

					if (isset($hash_cache["graph_template_item"]{$parsed_hash["hash"]})) {
						db_execute("replace into graph_template_input_defs (graph_template_input_id,graph_template_item_id) values ($graph_template_input_id," . $hash_cache["graph_template_item"]{$parsed_hash["hash"]} . ")");
					}
				}
			}
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_graph_template_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($graph_template_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_data_template($hash, &$xml_array, &$hash_cache, $import_custom_rra_settings) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* import into: data_template */
	$_data_template_id = db_fetch_cell("select id from data_template where hash='$hash'");
	$save["id"] = (empty($_data_template_id) ? "0" : $_data_template_id);
	$save["hash"] = $hash;
	$save["name"] = $xml_array["name"];

	$data_template_id = sql_save($save, "data_template");

	$hash_cache["data_template"][$hash] = $data_template_id;

	/* import into: data_template_data */
	unset($save);
	$save["id"] = (empty($_data_template_id) ? "0" : db_fetch_cell("select data_template_data.id from (data_template,data_template_data) where data_template.id=data_template_data.data_template_id and data_template.id=$data_template_id and data_template_data.local_data_id=0"));
	$save["data_template_id"] = $data_template_id;

	$struct_data_source = data_source_form_list();
	reset($struct_data_source);
	while (list($field_name, $field_array) = each($struct_data_source)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array["ds"]{"t_" . $field_name})) {
			$save{"t_" . $field_name} = $xml_array["ds"]{"t_" . $field_name};
		}

		/* make sure this field exists in the xml array first */
		if (isset($xml_array["ds"][$field_name])) {
			/* is the value of this field a hash or not? */
			if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $xml_array["ds"][$field_name])) {
				$save[$field_name] = resolve_hash_to_id($xml_array["ds"][$field_name], $hash_cache);
			}else{
				$save[$field_name] = addslashes(xml_character_decode($xml_array["ds"][$field_name]));
			}
		}
	}

	/* use the polling interval as the step if we are to use the default rra settings */
	if ($import_custom_rra_settings === false) {
		$save["rrd_step"] = read_config_option("poller_interval");
	}

	$data_template_data_id = sql_save($save, "data_template_data");

	/* use custom rra settings from the xml */
	if ($import_custom_rra_settings === true) {
		/* import into: data_template_data_rra */
		$hash_items = explode("|", $xml_array["ds"]["rra_items"]);

		if (!empty($hash_items[0])) {
			for ($i=0; $i<count($hash_items); $i++) {
				/* parse information from the hash */
				$parsed_hash = parse_xml_hash($hash_items[$i]);

				/* invalid/wrong hash */
				if ($parsed_hash == false) { return false; }

				if (isset($hash_cache["round_robin_archive"]{$parsed_hash["hash"]})) {
					db_execute("replace into data_template_data_rra (data_template_data_id,rra_id) values ($data_template_data_id," . $hash_cache["round_robin_archive"]{$parsed_hash["hash"]} . ")");
				}
			}
		}
	/* use all rras by default */
	}else{
		$rras = db_fetch_assoc("select id from rra");

		if (is_array($rras)) {
			foreach ($rras as $rra) {
				db_execute("replace into data_template_data_rra (data_template_data_id,rra_id) values ($data_template_data_id," . $rra["id"] . ")");
			}
		}
	}

	/* import into: data_template_rrd */
	if (is_array($xml_array["items"])) {
		while (list($item_hash, $item_array) = each($xml_array["items"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_data_template_rrd_id = db_fetch_cell("select id from data_template_rrd where hash='" . $parsed_hash["hash"] . "' and data_template_id=$data_template_id and local_data_id=0");
			$save["id"] = (empty($_data_template_rrd_id) ? "0" : $_data_template_rrd_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["data_template_id"] = $data_template_id;

			$struct_data_source_item = data_source_item_form_list();
			reset($struct_data_source_item);
			while (list($field_name, $field_array) = each($struct_data_source_item)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array{"t_" . $field_name})) {
					$save{"t_" . $field_name} = $item_array{"t_" . $field_name};
				}

				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					/* is the value of this field a hash or not? */
					if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $item_array[$field_name])) {
						$save[$field_name] = resolve_hash_to_id($item_array[$field_name], $hash_cache);
					}else{
						$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
					}
				}
			}

			/* use the polling interval * 2 as the heartbeat if we are to use the default rra settings */
			if ($import_custom_rra_settings === false) {
				$save["rrd_heartbeat"] = read_config_option("poller_interval") * 2;
			}

			$data_template_rrd_id = sql_save($save, "data_template_rrd");

			$hash_cache["data_template_item"]{$parsed_hash["hash"]} = $data_template_rrd_id;
		}
	}

	/* import into: data_input_data */
	if (is_array($xml_array["data"])) {
		while (list($item_hash, $item_array) = each($xml_array["data"])) {
			unset($save);
			$save["data_template_data_id"] = $data_template_data_id;
			$save["data_input_field_id"] = resolve_hash_to_id($item_array["data_input_field_id"], $hash_cache);
			$save["t_value"] = $item_array["t_value"];
			$save["value"] = addslashes(xml_character_decode($item_array["value"]));

			sql_save($save, "data_input_data", array("data_template_data_id", "data_input_field_id"), false);
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_data_template_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($data_template_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_data_query($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* import into: snmp_query */
	$_data_query_id = db_fetch_cell("select id from snmp_query where hash='$hash'");
	$save["id"] = (empty($_data_query_id) ? "0" : $_data_query_id);
	$save["hash"] = $hash;

	$fields_data_query_edit = data_query_form_list();
	reset($fields_data_query_edit);
	while (list($field_name, $field_array) = each($fields_data_query_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			/* is the value of this field a hash or not? */
			if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $xml_array[$field_name])) {
				$save[$field_name] = resolve_hash_to_id($xml_array[$field_name], $hash_cache);
			}else{
				$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
			}
		}
	}

	$data_query_id = sql_save($save, "snmp_query");

	$hash_cache["data_query"][$hash] = $data_query_id;

	/* import into: snmp_query_graph */
	if (is_array($xml_array["graphs"])) {
		while (list($item_hash, $item_array) = each($xml_array["graphs"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_data_query_graph_id = db_fetch_cell("select id from snmp_query_graph where hash='" . $parsed_hash["hash"] . "' and snmp_query_id=$data_query_id");
			$save["id"] = (empty($_data_query_graph_id) ? "0" : $_data_query_graph_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["snmp_query_id"] = $data_query_id;

			$fields_data_query_item_edit = data_query_item_form_list();
			reset($fields_data_query_item_edit);
			while (list($field_name, $field_array) = each($fields_data_query_item_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					/* is the value of this field a hash or not? */
					if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $item_array[$field_name])) {
						$save[$field_name] = resolve_hash_to_id($item_array[$field_name], $hash_cache);
					}else{
						$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
					}
				}
			}

			$data_query_graph_id = sql_save($save, "snmp_query_graph");

			$hash_cache["data_query_graph"]{$parsed_hash["hash"]} = $data_query_graph_id;

			/* import into: snmp_query_graph_rrd */
			if (is_array($item_array["rrd"])) {
				while (list($sub_item_hash, $sub_item_array) = each($item_array["rrd"])) {
					unset($save);
					$save["snmp_query_graph_id"] = $data_query_graph_id;
					$save["data_template_id"] = resolve_hash_to_id($sub_item_array["data_template_id"], $hash_cache);
					$save["data_template_rrd_id"] = resolve_hash_to_id($sub_item_array["data_template_rrd_id"], $hash_cache);
					$save["snmp_field_name"] = $sub_item_array["snmp_field_name"];

					sql_save($save, "snmp_query_graph_rrd", array("snmp_query_graph_id", "data_template_id", "data_template_rrd_id"), false);
				}
			}

			/* import into: snmp_query_graph_sv */
			if (is_array($item_array["sv_graph"])) {
				while (list($sub_item_hash, $sub_item_array) = each($item_array["sv_graph"])) {
					/* parse information from the hash */
					$parsed_hash = parse_xml_hash($sub_item_hash);

					/* invalid/wrong hash */
					if ($parsed_hash == false) { return false; }

					unset($save);
					$_data_query_graph_sv_id = db_fetch_cell("select id from snmp_query_graph_sv where hash='" . $parsed_hash["hash"] . "' and snmp_query_graph_id=$data_query_graph_id");
					$save["id"] = (empty($_data_query_graph_sv_id) ? "0" : $_data_query_graph_sv_id);
					$save["hash"] = $parsed_hash["hash"];
					$save["snmp_query_graph_id"] = $data_query_graph_id;
					$save["sequence"] = $sub_item_array["sequence"];
					$save["field_name"] = $sub_item_array["field_name"];
					$save["text"] = xml_character_decode($sub_item_array["text"]);

					$data_query_graph_sv_id = sql_save($save, "snmp_query_graph_sv");

					$hash_cache["data_query_sv_graph"]{$parsed_hash["hash"]} = $data_query_graph_sv_id;
				}
			}

			/* import into: snmp_query_graph_rrd_sv */
			if (is_array($item_array["sv_data_source"])) {
				while (list($sub_item_hash, $sub_item_array) = each($item_array["sv_data_source"])) {
					/* parse information from the hash */
					$parsed_hash = parse_xml_hash($sub_item_hash);

					/* invalid/wrong hash */
					if ($parsed_hash == false) { return false; }

					unset($save);
					$_data_query_graph_rrd_sv_id = db_fetch_cell("select id from snmp_query_graph_rrd_sv where hash='" . $parsed_hash["hash"] . "' and snmp_query_graph_id=$data_query_graph_id");
					$save["id"] = (empty($_data_query_graph_rrd_sv_id) ? "0" : $_data_query_graph_rrd_sv_id);
					$save["hash"] = $parsed_hash["hash"];
					$save["snmp_query_graph_id"] = $data_query_graph_id;
					$save["data_template_id"] = resolve_hash_to_id($sub_item_array["data_template_id"], $hash_cache);
					$save["sequence"] = $sub_item_array["sequence"];
					$save["field_name"] = $sub_item_array["field_name"];
					$save["text"] = xml_character_decode($sub_item_array["text"]);

					$data_query_graph_rrd_sv_id = sql_save($save, "snmp_query_graph_rrd_sv");

					$hash_cache["data_query_sv_data_source"]{$parsed_hash["hash"]} = $data_query_graph_rrd_sv_id;
				}
			}
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_data_query_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($data_query_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_gprint_preset($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_gprint_info.php");

	/* import into: graph_templates_gprint */
	$_gprint_preset_id = db_fetch_cell("select id from graph_templates_gprint where hash='$hash'");
	$save["id"] = (empty($_gprint_preset_id) ? "0" : $_gprint_preset_id);
	$save["hash"] = $hash;

	$fields_gprint_presets_edit = preset_gprint_form_list();
	reset($fields_gprint_presets_edit);
	while (list($field_name, $field_array) = each($fields_gprint_presets_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$gprint_preset_id = sql_save($save, "graph_templates_gprint");

	$hash_cache["gprint_preset"][$hash] = $gprint_preset_id;

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_gprint_preset_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($gprint_preset_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_round_robin_archive($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_rra_info.php");

	/* import into: rra */
	$_rra_id = db_fetch_cell("select id from rra where hash='$hash'");
	$save["id"] = (empty($_rra_id) ? "0" : $_rra_id);
	$save["hash"] = $hash;

	$fields_rra_edit = preset_rra_form_list();
	reset($fields_rra_edit);
	while (list($field_name, $field_array) = each($fields_rra_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$rra_id = sql_save($save, "rra");

	$hash_cache["round_robin_archive"][$hash] = $rra_id;

	/* import into: rra_cf */
	$hash_items = explode("|", $xml_array["cf_items"]);

	if (!empty($hash_items[0])) {
		for ($i=0; $i<count($hash_items); $i++) {
			db_execute("replace into rra_cf (rra_id,consolidation_function_id) values ($rra_id," . $hash_items[$i] . ")");
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_rra_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($rra_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_device_template($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/device_template/device_template_info.php");

	/* import into: graph_templates_gprint */
	$_device_template_id = db_fetch_cell("select id from device_template where hash='$hash'");
	$save["id"] = (empty($_device_template_id) ? "0" : $_device_template_id);
	$save["hash"] = $hash;

	$fields_device_template_edit = device_template_form_list();
	reset($fields_device_template_edit);
	while (list($field_name, $field_array) = each($fields_device_template_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$device_template_id = sql_save($save, "device_template");

	$hash_cache["device_template"][$hash] = $device_template_id;

	/* import into: device_template_graph */
	$hash_items = explode("|", $xml_array["graph_templates"]);

	if (!empty($hash_items[0])) {
		for ($i=0; $i<count($hash_items); $i++) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($hash_items[$i]);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			if (isset($hash_cache["graph_template"]{$parsed_hash["hash"]})) {
				db_execute("replace into device_template_graph (device_template_id,graph_template_id) values ($device_template_id," . $hash_cache["graph_template"]{$parsed_hash["hash"]} . ")");
			}
		}
	}

	/* import into: device_template_snmp_query */
	$hash_items = explode("|", $xml_array["data_queries"]);

	if (!empty($hash_items[0])) {
		for ($i=0; $i<count($hash_items); $i++) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($hash_items[$i]);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			if (isset($hash_cache["data_query"]{$parsed_hash["hash"]})) {
				db_execute("replace into device_template_snmp_query (device_template_id,snmp_query_id) values ($device_template_id," . $hash_cache["data_query"]{$parsed_hash["hash"]} . ")");
			}
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_device_template_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($device_template_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_cdef($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_cdef_info.php");

	/* import into: cdef */
	$_cdef_id = db_fetch_cell("select id from cdef where hash='$hash'");
	$save["id"] = (empty($_cdef_id) ? "0" : $_cdef_id);
	$save["hash"] = $hash;

	$fields_cdef_edit = preset_cdef_form_list();
	reset($fields_cdef_edit);
	while (list($field_name, $field_array) = each($fields_cdef_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$cdef_id = sql_save($save, "cdef");

	$hash_cache["cdef"][$hash] = $cdef_id;

	/* import into: cdef_items */
	$fields_cdef_item_edit = preset_cdef_item_form_list();
	if (is_array($xml_array["items"])) {
		while (list($item_hash, $item_array) = each($xml_array["items"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_cdef_item_id = db_fetch_cell("select id from cdef_items where hash='" . $parsed_hash["hash"] . "' and cdef_id=$cdef_id");
			$save["id"] = (empty($_cdef_item_id) ? "0" : $_cdef_item_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["cdef_id"] = $cdef_id;

			reset($fields_cdef_item_edit);
			while (list($field_name, $field_array) = each($fields_cdef_item_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
				}
			}

			$cdef_item_id = sql_save($save, "cdef_items");

			$hash_cache["cdef_item"]{$parsed_hash["hash"]} = $cdef_item_id;
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_cdef_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($cdef_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_vdef($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_vdef_info.php");

	/* import into: vdef */
	$_vdef_id = db_fetch_cell("select id from vdef where hash='$hash'");
	$save["id"] = (empty($_vdef_id) ? "0" : $_vdef_id);
	$save["hash"] = $hash;

	$fields_vdef_edit = preset_vdef_form_list();
	reset($fields_vdef_edit);
	while (list($field_name, $field_array) = each($fields_vdef_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$vdef_id = sql_save($save, "vdef");

	$hash_cache["vdef"][$hash] = $vdef_id;

	/* import into: vdef_items */
	if (is_array($xml_array["items"])) {
		while (list($item_hash, $item_array) = each($xml_array["items"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_vdef_item_id = db_fetch_cell("select id from vdef_items where hash='" . $parsed_hash["hash"] . "' and vdef_id=$vdef_id");
			$save["id"] = (empty($_vdef_item_id) ? "0" : $_vdef_item_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["vdef_id"] = $vdef_id;

			$fields_vdef_item_edit = preset_vdef_item_form_list();
			reset($fields_vdef_item_edit);
			while (list($field_name, $field_array) = each($fields_vdef_item_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
				}
			}

			$vdef_item_id = sql_save($save, "vdef_items");

			$hash_cache["vdef_item"]{$parsed_hash["hash"]} = $vdef_item_id;
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_vdef_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($vdef_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_xaxis($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/presets/preset_xaxis_info.php");

	/* import into: xaxis */
	$_xaxis_id = db_fetch_cell("select id from graph_templates_xaxis where hash='$hash'");
	$save["id"] = (empty($_xaxis_id) ? "0" : $_xaxis_id);
	$save["hash"] = $hash;

	$fields_xaxis_edit = preset_xaxis_form_list();
	reset($fields_xaxis_edit);
	while (list($field_name, $field_array) = each($fields_xaxis_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$xaxis_id = sql_save($save, "graph_templates_xaxis");

	$hash_cache["xaxis"][$hash] = $xaxis_id;

	/* import into: xaxis_items */
	if (is_array($xml_array["items"])) {
		while (list($item_hash, $item_array) = each($xml_array["items"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_xaxis_item_id = db_fetch_cell("select id from graph_templates_xaxis_items where hash='" . $parsed_hash["hash"] . "' and xaxis_id=$xaxis_id");
			$save["id"] = (empty($_xaxis_item_id) ? "0" : $_xaxis_item_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["xaxis_id"] = $xaxis_id;

			$fields_xaxis_item_edit = preset_xaxis_item_form_list();
			reset($fields_xaxis_item_edit);
			while (list($field_name, $field_array) = each($fields_xaxis_item_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
				}
			}

			$xaxis_item_id = sql_save($save, "graph_templates_xaxis_items");

			$hash_cache["xaxis_item"]{$parsed_hash["hash"]} = $xaxis_item_id;
		}
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_xaxis_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($xaxis_id) ? "fail" : "success");

	return $hash_cache;
}

function xml_to_data_input_method($hash, &$xml_array, &$hash_cache) {
	require_once(CACTI_BASE_PATH . "/lib/data_input/data_input_info.php");

	/* aggregate field arrays */
	$fields_data_input_field_edit = data_input_field_form_list() + data_input_field1_form_list();

	/* import into: data_input */
	$_data_input_id = db_fetch_cell("select id from data_input where hash='$hash'");
	$save["id"] = (empty($_data_input_id) ? "0" : $_data_input_id);
	$save["hash"] = $hash;

	$fields_data_input_edit = data_input_form_list();
	reset($fields_data_input_edit);
	while (list($field_name, $field_array) = each($fields_data_input_edit)) {
		/* make sure this field exists in the xml array first */
		if (isset($xml_array[$field_name])) {
			/* fix issue with data input method importing and white spaces */
			if ($field_name == "input_string") {
				$xml_array[$field_name] = str_replace("><", "> <", $xml_array[$field_name]);
			}

			$save[$field_name] = addslashes(xml_character_decode($xml_array[$field_name]));
		}
	}

	$data_input_id = sql_save($save, "data_input");

	$hash_cache["data_input_method"][$hash] = $data_input_id;

	/* import into: data_input_fields */
	if (is_array($xml_array["fields"])) {
		while (list($item_hash, $item_array) = each($xml_array["fields"])) {
			/* parse information from the hash */
			$parsed_hash = parse_xml_hash($item_hash);

			/* invalid/wrong hash */
			if ($parsed_hash == false) { return false; }

			unset($save);
			$_data_input_field_id = db_fetch_cell("select id from data_input_fields where hash='" . $parsed_hash["hash"] . "' and data_input_id=$data_input_id");
			$save["id"] = (empty($_data_input_field_id) ? "0" : $_data_input_field_id);
			$save["hash"] = $parsed_hash["hash"];
			$save["data_input_id"] = $data_input_id;

			reset($fields_data_input_field_edit);
			while (list($field_name, $field_array) = each($fields_data_input_field_edit)) {
				/* make sure this field exists in the xml array first */
				if (isset($item_array[$field_name])) {
					$save[$field_name] = addslashes(xml_character_decode($item_array[$field_name]));
				}
			}

			$data_input_field_id = sql_save($save, "data_input_fields");

			$hash_cache["data_input_field"]{$parsed_hash["hash"]} = $data_input_field_id;
		}
	}

	/* update field use counter cache if possible */
	if ((isset($xml_array["input_string"])) && (!empty($data_input_id))) {
		generate_data_input_field_sequences($xml_array["input_string"], $data_input_id);
	}

	/* status information that will be presented to the user */
	$_SESSION["import_debug_info"]["type"] = (empty($_data_input_id) ? "new" : "update");
	$_SESSION["import_debug_info"]["title"] = $xml_array["name"];
	$_SESSION["import_debug_info"]["result"] = (empty($data_input_id) ? "fail" : "success");

	return $hash_cache;
}

function hash_to_friendly_name($hash, $display_type_name) {
	global $hash_type_names;

	/* parse information from the hash */
	$parsed_hash = parse_xml_hash($hash);

	/* invalid/wrong hash */
	if ($parsed_hash == false) { return false; }

	if ($display_type_name == true) {
		$prepend = "(<em>" . $hash_type_names{$parsed_hash["type"]} . "</em>) ";
	}else{
		$prepend = "";
	}

	switch ($parsed_hash["type"]) {
	case 'graph_template':
		return $prepend . db_fetch_cell("select name from graph_templates where hash='" . $parsed_hash["hash"] . "'");
	case 'data_template':
		return $prepend . db_fetch_cell("select name from data_template where hash='" . $parsed_hash["hash"] . "'");
	case 'data_template_item':
		return $prepend . db_fetch_cell("select data_source_name from data_template_rrd where hash='" . $parsed_hash["hash"] . "'");
	case 'device_template':
		return $prepend . db_fetch_cell("select name from device_template where hash='" . $parsed_hash["hash"] . "'");
	case 'data_input_method':
		return $prepend . db_fetch_cell("select name from data_input where hash='" . $parsed_hash["hash"] . "'");
	case 'data_input_field':
		return $prepend . db_fetch_cell("select name from data_input_fields where hash='" . $parsed_hash["hash"] . "'");
	case 'data_query':
		return $prepend . db_fetch_cell("select name from snmp_query where hash='" . $parsed_hash["hash"] . "'");
	case 'gprint_preset':
		return $prepend . db_fetch_cell("select name from graph_templates_gprint where hash='" . $parsed_hash["hash"] . "'");
	case 'cdef':
		return $prepend . db_fetch_cell("select name from cdef where hash='" . $parsed_hash["hash"] . "'");
	case 'vdef':
		return $prepend . db_fetch_cell("select name from vdef where hash='" . $parsed_hash["hash"] . "'");
	case 'round_robin_archive':
		return $prepend . db_fetch_cell("select name from rra where hash='" . $parsed_hash["hash"] . "'");
	}
}

function resolve_hash_to_id($hash, &$hash_cache_array) {
	/* parse information from the hash */
	$parsed_hash = parse_xml_hash($hash);

	/* invalid/wrong hash */
	if ($parsed_hash == false) { return false; }

	if (isset($hash_cache_array{$parsed_hash["type"]}{$parsed_hash["hash"]})) {
		$_SESSION["import_debug_info"]["dep"][$hash] = "met";
		return $hash_cache_array{$parsed_hash["type"]}{$parsed_hash["hash"]};
	}else{
		$_SESSION["import_debug_info"]["dep"][$hash] = "unmet";
		return 0;
	}
}

function parse_xml_hash($hash) {
	if (preg_match("/hash_([a-f0-9]{2})([a-f0-9]{4})([a-f0-9]{32})/", $hash, $matches)) {
		$parsed_hash["type"] = check_hash_type($matches[1]);
		$parsed_hash["version"] = strval(check_hash_version($matches[2]));
		$parsed_hash["hash"] = $matches[3];

		/* an error has occured */
		if (($parsed_hash["type"] == false) || ($parsed_hash["version"] == false)) {
			return false;
		}
	}else{
		return false;
	}

	return $parsed_hash;
}

function check_hash_type($hash_type) {
	global $hash_type_codes;

	/* lets not mess up the pointer for other people */
	$local_hash_type_codes = $hash_type_codes;

	reset($local_hash_type_codes);
	while (list($type, $code) = each($local_hash_type_codes)) {
		if ($code == $hash_type) {
			$current_type = $type;
		}
	}

	if (!isset($current_type)) {
		raise_message(18); /* error: cannot find type */
		return false;
	}

	return $current_type;
}

function check_hash_version($hash_version) {
	global $hash_version_codes, $config;

	$i = 0;

	reset($hash_version_codes);
	while (list($version, $code) = each($hash_version_codes)) {
		if ($version == CACTI_VERSION) {
			$current_version_index = $i;
		}

		if ($code == $hash_version) {
			$hash_version_index = $i;
			$current_version = $version;
		}

		$i++;
	}

	if (!isset($current_version_index)) {
		raise_message(15); /* error: current cacti version does not exist! */
		return false;
	}elseif (!isset($hash_version_index)) {
		raise_message(16); /* error: hash version does not exist! */
		return false;
	}elseif ($hash_version_index > $current_version_index) {
		raise_message(17); /* error: hash made with a newer version of cacti */
		return false;
	}

	return $current_version;
}

function get_version_index($string_version) {
	global $hash_version_codes;

	$i = 0;

	reset($hash_version_codes);
	while (list($version, $code) = each($hash_version_codes)) {
		if ($string_version == $version) {
			return $i;
		}

		$i++;
	}

	return -1;
}

function xml_character_decode($text) {
	if (function_exists("html_entity_decode")) {
		return html_entity_decode($text, ENT_QUOTES, "UTF-8");
	} else {
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		return strtr($text, $trans_tbl);
	}
}

function update_pre_088_graph_items($items) {
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	require_once(CACTI_BASE_PATH . "/include/presets/preset_rra_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/template.php");

	if (sizeof($items)) {
		foreach ($items as $key => $graph_item) {
			/* mimic the old behavior: LINE[123], AREA and STACK items use the CF specified in the graph item */
			if (($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1) ||
				($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2) ||
				($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3) ||
				($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA)  ||
				($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK)) {
				$graph_cf = $graph_item["consolidation_function_id"];
				/* remember the last CF for this data source for use with GPRINT
				 * if e.g. an AREA/AVERAGE and a LINE/MAX is used
				 * we will have AVERAGE first and then MAX, depending on GPRINT sequence */
				$last_graph_cf{$graph_item["task_item_id"]} = $graph_cf;
				/* remember this for second foreach loop */
				$items[$key]["cf_reference"] = $graph_cf;
			}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT) {
				/* ATTENTION!
				* the "CF" given on graph_item edit screen for GPRINT is indeed NOT a real "CF",
				* but an aggregation function
				* see "man rrdgraph_data" for the correct VDEF based notation
				* so our task now is to "guess" the very graph_item, this GPRINT is related to
				* and to use that graph_item's CF */
				if (isset($last_graph_cf{$graph_item["task_item_id"]})) {
					$graph_cf = $last_graph_cf{$graph_item["task_item_id"]};
					/* remember this for second foreach loop */
					$items[$key]["cf_reference"] = $graph_cf;
				} else {
					$graph_cf = generate_graph_best_cf($graph_item["local_data_id"], $graph_item["consolidation_function_id"]);
					/* remember this for second foreach loop */
					$items[$key]["cf_reference"] = $graph_cf;
				}

				switch($graph_item["consolidation_function_id"]) {
					case RRA_CF_TYPE_AVERAGE:
						$items[$key]["graph_type_id"] = GRAPH_ITEM_TYPE_GPRINT_AVERAGE;
						break;
					case RRA_CF_TYPE_MIN:
						$items[$key]["graph_type_id"] = GRAPH_ITEM_TYPE_GPRINT_MIN;
						break;
					case RRA_CF_TYPE_MAX:
						$items[$key]["graph_type_id"] = GRAPH_ITEM_TYPE_GPRINT_MAX;
						break;
					case RRA_CF_TYPE_LAST:
						$items[$key]["graph_type_id"] = GRAPH_ITEM_TYPE_GPRINT_LAST;
						break;
				}

				db_execute("UPDATE graph_templates_item SET `consolidation_function_id`=".$items[$key]["cf_reference"].", `graph_type_id`=".$items[$key]["graph_type_id"]." WHERE `id`=".$graph_item["id"]);
				if ($graph_item["graph_template_id"] != 0) {
					db_execute("UPDATE graph_templates_item SET `consolidation_function_id`=".$items[$key]["cf_reference"].", `graph_type_id`=".$items[$key]["graph_type_id"]." WHERE `local_graph_template_item_id`=".$graph_item["id"]);
				}
			}
		}
	}
}
