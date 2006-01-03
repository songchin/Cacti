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

	/* obtain a list of all graph template specific fields */
	$graph_template_fields = api_graph_template_form_list();
	/* obtain a copy of this specfic graph template */
	$graph_template = api_graph_template_get($graph_template_id);

	$_xml = "";
	foreach (array_keys($graph_template_fields) as $field_name) {
		/* create an XML key for each graph template field */
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template[$field_name]), $indent + 2);
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("template", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <graph>
	 */

	/* obtain a list of all graph specific fields */
	$graph_fields = api_graph_form_list();

	$_xml = "";
	foreach (array_keys($graph_fields) as $field_name) {
		/* check because the 'title' column does not exist */
		if (isset($graph_template[$field_name])) {
			/* create an XML key for each graph field */
			$_xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template[$field_name]), $indent + 2);
		}

		/* create an XML key for each "template" graph field */
		$_xml .= package_xml_tag_get("t_" . $field_name, xml_character_encode($graph_template{"t_" . $field_name}), $indent + 2);
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("graph", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <items>
	 */

	/* obtain a list of all graph template item specific fields */
	$graph_template_items_fields = api_graph_template_item_form_list();
	/* obtain a list of all graph template items associated with this graph template */
	$graph_template_items = api_graph_template_item_list($graph_template_id);

	$_xml = "";
	if (sizeof($graph_template_items) > 0) {
		foreach ($graph_template_items as $graph_template_item) {
			$__xml = "";
			foreach (array_keys($graph_template_items_fields) as $field_name) {
				if ($field_name == "data_template_item_id") {
					/* create an XML key for the 'data_template_item_id' field, making sure to resolve internal  ID's */
					$__xml .= package_xml_tag_get($field_name, xml_character_encode(package_hash_get($graph_template_item[$field_name], "data_template_item")), $indent + 3);
				}else{
					/* create an XML key for each graph template item field */
					$__xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template_item[$field_name]), $indent + 3);
				}
			}

			/* append the result onto a temporary XML string */
			$_xml .= package_xml_tag_get(package_hash_get($graph_template_item["id"], "graph_template_item"), $__xml, $indent + 2, true);
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("items", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <inputs>
	 */

	/* obtain a list of all graph template item input specific fields */
	$graph_template_inputs_fields = api_graph_template_item_input_form_list();
	/* obtain a list of all graph template item inputs associated with this graph template */
	$graph_template_inputs = api_graph_template_item_input_list($graph_template_id);

	$_xml = "";
	if (sizeof($graph_template_inputs) > 0) {
		foreach ($graph_template_inputs as $graph_template_input) {
			$__xml = "";
			foreach (array_keys($graph_template_inputs_fields) as $field_name) {
				/* create an XML key for each graph template item input field */
				$__xml .= package_xml_tag_get($field_name, xml_character_encode($graph_template_input[$field_name]), $indent + 3);
			}

			/* obtain a list of each item associated with this graph template item input */
			$graph_template_input_items = api_graph_template_item_input_item_list($graph_template_id);

			if (sizeof($graph_template_input_items) > 0) {
				$i = 0; $items_list = "";
				foreach ($graph_template_input_items as $graph_template_item_id) {
					/* create a delimited list of each item, making sure to resolve internal ID's */
					$items_list .= package_hash_get($graph_template_item_id, "graph_template_item") . (($i + 1) < sizeof($graph_template_input_items) ? "|" : "");

					$i++;
				}
			}

			/* add the items list that we created above */
			$__xml .= package_xml_tag_get("items", $items_list, $indent + 3);

			/* append the result onto a temporary XML string */
			$_xml .= package_xml_tag_get(package_hash_get($graph_template_input["id"], "graph_template_input"), $__xml, $indent + 2, true);
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("inputs", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <suggested_values>
	 */

	/* obtain a list of all suggested values associated with this graph template */
	$graph_template_suggested_values = api_graph_template_suggested_values_list($graph_template_id);

	$_xml = "";
	if (sizeof($graph_template_suggested_values) > 0) {
		$i = 0;
		foreach ($graph_template_suggested_values as $graph_template_suggested_value) {
			$__xml = "";

			/* create an XML key for each suggested value field */
			$__xml .= package_xml_tag_get("field_name", xml_character_encode($graph_template_suggested_value["field_name"]), $indent + 3);
			$__xml .= package_xml_tag_get("sequence", xml_character_encode($graph_template_suggested_value["sequence"]), $indent + 3);
			$__xml .= package_xml_tag_get("value", xml_character_encode($graph_template_suggested_value["value"]), $indent + 3);

			/* break out each row into its own key */
			$_xml .= package_xml_tag_get("item_" . str_pad($i, 5, "0", STR_PAD_LEFT), $__xml, $indent + 2, true);

			$i++;
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("suggested_values", $_xml, $indent + 1, true);

	/* wrap the whole XML string into a 'graph_template' tag and return it */
	$xml = package_xml_tag_get(package_hash_get($graph_template_id, "graph_template"), $xml, $indent, true);

	return $xml;
}

function &package_data_template_export($data_template_id, $indent = 2) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	$xml = "";

	/*
	 * XML Tag: <template>
	 */

	/* obtain a list of all data template specific fields */
	$data_template_fields = api_data_template_form_list();
	/* obtain a copy of this specfic data template */
	$data_template = api_data_template_get($data_template_id);

	$_xml = "";
	foreach (array_keys($data_template_fields) as $field_name) {
		/* create an XML key for each data template field */
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($data_template[$field_name]), $indent + 2);
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("template", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <data_source>
	 */

	/* obtain a list of all data source specific fields */
	$data_source_fields = api_data_source_form_list();

	$_xml = "";
	foreach (array_keys($data_source_fields) as $field_name) {
		/* check because the 'name' column does not exist */
		if (isset($data_template[$field_name])) {
			/* create an XML key for each data source field */
			$_xml .= package_xml_tag_get($field_name, xml_character_encode($data_template[$field_name]), $indent + 2);
		}

		/* check because the 't_data_input_type' and 't_rrd_path' columns do not exist */
		if (isset($data_template{"t_" . $field_name})) {
			/* create an XML key for each "template" data source field */
			$_xml .= package_xml_tag_get("t_" . $field_name, xml_character_encode($data_template{"t_" . $field_name}), $indent + 2);
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("data_source", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <items>
	 */

	/* obtain a list of all data source item specific fields */
	$data_source_items_fields = api_data_source_item_form_list();
	/* obtain a list of all data template items associated with this data template */
	$data_template_items = api_data_template_item_list($data_template_id);

	$_xml = "";
	if (sizeof($data_template_items) > 0) {
		foreach ($data_template_items as $data_template_item) {
			$__xml = "";
			foreach (array_keys($data_source_items_fields) as $field_name) {
				/* create an XML key for each data template item field */
				$__xml .= package_xml_tag_get($field_name, xml_character_encode($data_template_item[$field_name]), $indent + 3);
			}

			/* append the result onto a temporary XML string */
			$_xml .= package_xml_tag_get(package_hash_get($data_template_item["id"], "data_template_item"), $__xml, $indent + 2, true);
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("items", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <fields>
	 */

	/* obtain a list of all data template input fields associated with this data template */
	$data_template_input_fields = api_data_template_input_field_list($data_template_id);

	$_xml = "";
	if (sizeof($data_template_input_fields) > 0) {
		$i = 0;
		foreach ($data_template_input_fields as $data_template_input_field_name => $data_template_input_field) {
			$__xml = "";

			/* create an XML key for each suggested value field */
			$__xml .= package_xml_tag_get("name", xml_character_encode($data_template_input_field_name), $indent + 3);
			$__xml .= package_xml_tag_get("t_value", xml_character_encode($data_template_input_field["t_value"]), $indent + 3);

			/* make sure to resolve internal ID's for specific fields */
			if ($data_template_input_field_name == "data_query_id") {
				$__xml .= package_xml_tag_get("value", xml_character_encode(package_hash_get($data_template_input_field["value"], "data_query")), $indent + 3);
			}else if ($data_template_input_field_name == "script_id") {
				$__xml .= package_xml_tag_get("value", xml_character_encode(package_hash_get($data_template_input_field["value"], "script")), $indent + 3);
			}else{
				$__xml .= package_xml_tag_get("value", xml_character_encode($data_template_input_field["value"]), $indent + 3);
			}

			/* break out each row into its own key */
			$_xml .= package_xml_tag_get("item_" . str_pad($i, 5, "0", STR_PAD_LEFT), $__xml, $indent + 2, true);

			$i++;
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("fields", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <suggested_values>
	 */

	/* obtain a list of all suggested values associated with this data template */
	$data_template_suggested_values = api_data_template_suggested_values_list($data_template_id);

	$_xml = "";
	if (sizeof($data_template_suggested_values) > 0) {
		$i = 0;
		foreach ($data_template_suggested_values as $data_template_suggested_value) {
			$__xml = "";

			/* create an XML key for each suggested value field */
			$__xml .= package_xml_tag_get("field_name", xml_character_encode($data_template_suggested_value["field_name"]), $indent + 3);
			$__xml .= package_xml_tag_get("sequence", xml_character_encode($data_template_suggested_value["sequence"]), $indent + 3);
			$__xml .= package_xml_tag_get("value", xml_character_encode($data_template_suggested_value["value"]), $indent + 3);

			/* break out each row into its own key */
			$_xml .= package_xml_tag_get("item_" . str_pad($i, 5, "0", STR_PAD_LEFT), $__xml, $indent + 2, true);

			$i++;
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("suggested_values", $_xml, $indent + 1, true);

	/* wrap the whole XML string into a 'data_template' tag and return it */
	$xml = package_xml_tag_get(package_hash_get($data_template_id, "data_template"), $xml, $indent, true);

	return $xml;
}

function &package_data_query_export($data_query_id, $indent = 2) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	$xml = "";

	/*
	 * XML Tag: <data_query>
	 */

	/* obtain a list of all data query specific fields */
	$data_query_fields = api_data_query_form_list();
	/* obtain a copy of this specfic data query */
	$data_query = api_data_query_get($data_query_id);

	$_xml = "";
	foreach (array_keys($data_query_fields) as $field_name) {
		/* create an XML key for each data query field */
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($data_query[$field_name]), $indent + 2);
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("data_query", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <fields>
	 */

	/* obtain a list of all data query field specific fields */
	$data_query_field_fields = api_data_query_field_form_list();
	/* obtain a list of all data query fields associated with this data query */
	$data_query_fields = api_data_query_field_list($data_query_id);

	$_xml = "";
	if (sizeof($data_query_fields) > 0) {
		$i = 0;
		foreach ($data_query_fields as $data_query_field) {
			$__xml = "";
			foreach (array_keys($data_query_field_fields) as $field_name) {
				/* create an XML key for each data query item field */
				$__xml .= package_xml_tag_get($field_name, xml_character_encode($data_query_field[$field_name]), $indent + 3);
			}

			/* append the result onto a temporary XML string */
			$_xml .= package_xml_tag_get("item_" . str_pad($i, 5, "0", STR_PAD_LEFT), $__xml, $indent + 2, true);

			$i++;
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("fields", $_xml, $indent + 1, true);

	/* wrap the whole XML string into a 'data_query' tag and return it */
	$xml = package_xml_tag_get(package_hash_get($data_query_id, "data_query"), $xml, $indent, true);

	return $xml;
}

function &package_script_export($script_id, $indent = 2) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	$xml = "";

	/*
	 * XML Tag: <script>
	 */

	/* obtain a list of all script specific fields */
	$script_fields = api_script_form_list();
	/* obtain a copy of this specfic script */
	$script = api_script_get($script_id);

	$_xml = "";
	foreach (array_keys($script_fields) as $field_name) {
		/* create an XML key for each script field */
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($script[$field_name]), $indent + 2);
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("script", $_xml, $indent + 1, true);

	/*
	 * XML Tag: <fields>
	 */

	/* obtain a list of all script field specific fields */
	$script_field_fields = api_script_field_form_list();
	/* obtain a list of all script fields associated with this script */
	$script_fields = api_script_field_list($script_id);

	$_xml = "";
	if (sizeof($script_fields) > 0) {
		$i = 0;
		foreach ($script_fields as $script_field) {
			$__xml = "";
			foreach (array_keys($script_field_fields) as $field_name) {
				/* create an XML key for each script field field */
				$__xml .= package_xml_tag_get($field_name, xml_character_encode($script_field[$field_name]), $indent + 3);
			}

			/* append the result onto a temporary XML string */
			$_xml .= package_xml_tag_get("item_" . str_pad($i, 5, "0", STR_PAD_LEFT), $__xml, $indent + 2, true);

			$i++;
		}
	}

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("fields", $_xml, $indent + 1, true);

	/* wrap the whole XML string into a 'script' tag and return it */
	$xml = package_xml_tag_get(package_hash_get($script_id, "script"), $xml, $indent, true);

	return $xml;
}

function &package_rra_export($rra_id, $indent = 2) {
	require_once(CACTI_BASE_PATH . "/lib/rra/rra_info.php");

	$xml = "";

	/*
	 * XML Tag: <rra>
	 */

	/* obtain a list of all rra specific fields */
	$rra_fields = api_rra_form_list();
	/* obtain a copy of this specfic rra */
	$rra = api_rra_get($rra_id);

	$_xml = "";
	foreach (array_keys($rra_fields) as $field_name) {
		/* create an XML key for each rra field */
		$_xml .= package_xml_tag_get($field_name, xml_character_encode($rra[$field_name]), $indent + 2);
	}

	/* obtain a list of each consolidation function associated with this rra */
	$consolidation_functions = api_rra_consolidation_function_list($rra_id);

	if (sizeof($consolidation_functions) > 0) {
		$i = 0; $items_list = "";
		foreach ($consolidation_functions as $consolidation_function_id) {
			/* create a delimited list of each item */
			$items_list .= $consolidation_function_id . (($i + 1) < sizeof($consolidation_functions) ? "|" : "");

			$i++;
		}
	}

	/* add the items list that we created above */
	$_xml .= package_xml_tag_get("cf_items", $items_list, $indent + 2);

	/* append the result onto the final XML string */
	$xml .= package_xml_tag_get("rra", $_xml, $indent + 1, true);

	/* wrap the whole XML string into a 'rra' tag and return it */
	$xml = package_xml_tag_get(package_hash_get($rra_id, "rra"), $xml, $indent, true);

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
