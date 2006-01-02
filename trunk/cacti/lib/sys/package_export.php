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

require_once(CACTI_BASE_PATH . "/lib/sys/xml.php");

/* global cache of all package element hashes */
$package_hash_cache = array();

/* keep track of the last numeric hash that was generated */
$package_hash_counter = 0;

function &package_payload_export($package_id) {
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");

	$_xml = "";

	$dep_array = package_dependencies_list("package", $package_id, array());

	print_a($dep_array);
	//echo "<pre>" . htmlspecialchars($_xml) . "</pre>";
	return $_xml;
}

function &package_dependencies_list($type, $id, $dep_array) {
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* make sure we define our variables */
	if (sizeof($dep_array) == 0) {
		$dep_array["graph_template"] = array();
		$dep_array["data_template"] = array();
		$dep_array["script"] = array();
		$dep_array["data_query"] = array();
		$dep_array["round_robin_archive"] = array();
	}

	switch ($type) {
		case 'package':
			/* dependency: graph template */
			$graph_templates = api_package_graph_templates_list($id);

			if (sizeof($graph_templates) > 0) {
				foreach ($graph_templates as $graph_template_id) {
					if (!in_array($graph_template_id, $dep_array["graph_template"])) {
						$dep_array["graph_template"][] = $graph_template_id;
						$dep_array = package_dependencies_list("graph_template", $graph_template_id, $dep_array);
					}
				}
			}

			break;
		case 'graph_template':
			/* dependency: data template */
			$data_templates = api_graph_template_data_template_list($id);

			if (sizeof($data_templates) > 0) {
				foreach ($data_templates as $data_template_id) {
					if (!in_array($data_template_id, $dep_array["data_template"])) {
						$dep_array["data_template"][] = $data_template_id;
						$dep_array = package_dependencies_list("data_template", $data_template_id, $dep_array);
					}
				}
			}

			break;
		case 'data_template':
			/* dependency: script */
			$script_id = api_data_template_input_field_value_get($id, "script_id");

			if (($script_id !== false) && (!in_array($script_id, $dep_array["script"]))) {
				$dep_array["script"][] = $script_id;
			}

			/* dependency: data query */
			$data_query_id = api_data_template_input_field_value_get($id, "data_query_id");

			if (($data_query_id !== false) && (!in_array($data_query_id, $dep_array["data_query"]))) {
				$dep_array["data_query"][] = $data_query_id;
			}

			/* dependency: round robin archive */
			$rras = api_data_template_rras_list($id);

			if (sizeof($rras) > 0) {
				foreach ($rras as $rra_id) {
					if (!in_array($rra_id, $dep_array["round_robin_archive"])) {
						$dep_array["round_robin_archive"][] = $rra_id;
					}
				}
			}

			break;
	}

	return $dep_array;
}

function &package_graph_template_export($graph_template_id, $indent = 2) {
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	$xml = "";

	/*
	 * XML Tag: <template>
	 */

	$graph_template_fields = api_graph_template_field_list();
	$graph_template = api_graph_template_get($graph_template_id);

	$_xml = "";
	foreach (array_keys($graph_template_fields) as $field_name) {
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template[$field_name]), $indent + 2);
	}

	$xml .= package_xml_tag_get("template", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <graph>
	 */

	$graph_fields = get_graph_field_list();

	$_xml = "";
	foreach (array_keys($graph_fields) as $field_name) {
		/* check because the 'title' column does not exist */
		if (isset($graph_template[$field_name])) {
			$_xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template[$field_name]), $indent + 2);
		}

		$_xml .= package_xml_tag_get("t_" . $field_name, xml_character_encode($graph_template{"t_" . $field_name}), $indent + 2);
	}

	$xml .= package_xml_tag_get("graph", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <items>
	 */

	$graph_template_items_fields = api_graph_template_item_field_list();
	$graph_template_items = api_graph_template_item_list($graph_template_id);

	$_xml = "";
	if (sizeof($graph_template_items) > 0) {
		foreach ($graph_template_items as $graph_template_item) {
			$__xml = "";
			foreach (array_keys($graph_template_items_fields) as $field_name) {
				if ($field_name == "data_template_item_id") {
					$__xml .= package_xml_tag_get($field_name, xml_character_encode(package_hash_get($graph_template_item[$field_name], "data_template_item")), $indent + 3);
				}else{
					$__xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template_item[$field_name]), $indent + 3);
				}
			}

			$_xml .= package_xml_tag_get(package_hash_get($graph_template_item["id"], "graph_template_item"), $__xml, $indent + 2, true);
		}
	}

	$xml .= package_xml_tag_get("items", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <inputs>
	 */

	$graph_template_inputs_fields = api_graph_template_item_input_field_list();
	$graph_template_inputs = api_graph_template_item_input_list($graph_template_id);

	$_xml = "";
	if (sizeof($graph_template_inputs) > 0) {
		foreach ($graph_template_inputs as $graph_template_input) {
			$__xml = "";
			foreach (array_keys($graph_template_inputs_fields) as $field_name) {
				$__xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template_input[$field_name]), $indent + 3);
			}

			$_xml .= package_xml_tag_get(package_hash_get($graph_template_input["id"], "graph_template_input"), $__xml, $indent + 2, true);
		}
	}

	$xml .= package_xml_tag_get("inputs", $_xml, $indent + 1, true);

	$xml = package_xml_tag_get(package_hash_get($graph_template_id, "graph_template"), $xml, $indent, true);

	return $xml;

}

function &package_xml_tag_get($name, $value, $indent_num, $prepend_nl = false) {
	/* the variable assignment is to make php happy */
	$hash = str_repeat("\t", $indent_num) . "<$name>" . ($prepend_nl === true ? "\n" : "") . $value . ($prepend_nl === true ? str_repeat("\t", $indent_num) : "") . "</$name>\n";

	return $hash;
}

function package_hash_get($id, $category) {
	global $package_hash_cache, $package_hash_counter;

	if (!isset($package_hash_cache[$category])) {
		$package_hash_cache[$category] = array();
	}

	if (!isset($package_hash_cache[$category][$id])) {
		$package_hash_cache[$category][$id] = ++$package_hash_counter;
	}

	return "id_" . str_pad($package_hash_cache[$category][$id], 10, "0", STR_PAD_LEFT);
}

?>
