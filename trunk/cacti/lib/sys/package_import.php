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

require_once(CACTI_BASE_PATH . "/lib/sys/xml.php");

/* global cache of all package element hashes */
$package_hash_cache = array();

function package_import(&$xml) {
	$xml_array = xml_array_get($xml);

	if (sizeof($xml_array) == 0) {
		raise_message(7); /* xml parse error */
		return;
	}

	/* first, import the header which consists of the actual package data */
	$package_id = package_header_import($xml_array["header"]);

	/* next, import the full payload of the package including all of the templates */
	package_payload_import($xml_array["payload"], $package_id);

	/* last, tie the knot by adding the completed graph template references to the package */
	package_header_items_import($xml_array["header"], $package_id);
}

function package_header_import(&$xml_array) {
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");
	require_once(CACTI_BASE_PATH . "/lib/package/package_update.php");

	/*
	 * XML Tag: <package>
	 */

	/* obtain a list of all package specific fields */
	$package_fields = api_package_form_list();

	if (isset($xml_array["package"])) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach (array_keys($package_fields) as $field_name) {
			if (isset($xml_array["package"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["package"][$field_name]);
			}
		}

		/* save the package to the database */
		$package_id = api_package_save(0, $save_fields);

		if ($package_id === false) {
			return false;
		}
	}

	/*
	 * XML Tag: <metadata>
	 */

	/* obtain a list of all package metadata specific fields */
	$package_metadata_fields = api_package_metadata_form_list();

	if ((isset($xml_array["metadata"])) && (is_array($xml_array["metadata"]))) {
		/* loop through each available package metadata field */
		foreach ($xml_array["metadata"] as $package_metadata_hash => $package_metadata) {
			$save_fields = array();

			/* make sure that each metadata item is associated with the new package */
			$save_fields["package_id"] = $package_id;

			/* get the base fields from the xml */
			foreach (array_keys($package_metadata_fields) as $field_name) {
				if (isset($package_metadata[$field_name])) {
					if ($field_name == "payload") {
						/* decode any binary payload data */
						$save_fields[$field_name] = base64_decode(str_replace("\n", "", $package_metadata[$field_name]));
					}else{
						$save_fields[$field_name] = xml_character_decode($package_metadata[$field_name]);
					}
				}
			}

			/* save the package metadata item to the database */
			api_package_metadata_save(0, $save_fields);
		}
	}

	return $package_id;
}

function package_header_items_import(&$xml_array, $package_id) {
	require_once(CACTI_BASE_PATH . "/lib/package/package_update.php");

	if ((isset($xml_array["package"])) && (isset($xml_array["package"]["items"]))) {
		$graph_templates = explode("|", $xml_array["package"]["items"]);

		foreach ($graph_templates as $graph_template_hash) {
			/* convert the hash in the xml to an actual graph template id in the database */
			$graph_template_id = package_hash_resolve($graph_template_hash);

			if ($graph_template_id) {
				/* add the graph template reference to the package */
				api_package_package_template_add($package_id, $graph_template_id);
			}
		}
	}
}

function package_payload_import(&$xml_array, $package_id) {
	if (isset($xml_array["data_query"])) {
		if (sizeof($xml_array["data_query"]) > 0) {
			foreach (array_keys($xml_array["data_query"]) as $object_hash) {
				package_data_query_import($xml_array["data_query"][$object_hash], $package_id, $object_hash);
			}
		}
	}

	if (isset($xml_array["script"])) {
		if (sizeof($xml_array["script"]) > 0) {
			foreach (array_keys($xml_array["script"]) as $object_hash) {
				package_script_import($xml_array["script"][$object_hash], $package_id, $object_hash);
			}
		}
	}

	if (isset($xml_array["data_template"])) {
		if (sizeof($xml_array["data_template"]) > 0) {
			foreach (array_keys($xml_array["data_template"]) as $object_hash) {
				package_data_template_import($xml_array["data_template"][$object_hash], $package_id, $object_hash);
			}
		}
	}

	if (isset($xml_array["graph_template"])) {
		if (sizeof($xml_array["graph_template"]) > 0) {
			foreach (array_keys($xml_array["graph_template"]) as $object_hash) {
				package_graph_template_import($xml_array["graph_template"][$object_hash], $package_id, $object_hash);
			}
		}
	}
}

function package_graph_template_import(&$xml_array, $package_id, $object_hash) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_update.php");

	$save_fields = array();

	/* tag the graph template as a package member */
	$save_fields["package_id"] = $package_id;

	/*
	 * XML Tag: <template>
	 */

	/* obtain a list of all graph template specific fields */
	$graph_template_fields = api_graph_template_form_list();

	if (isset($xml_array["template"])) {
		/* get the base fields from the xml */
		foreach (array_keys($graph_template_fields) as $field_name) {
			if (isset($xml_array["template"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["template"][$field_name]);
			}
		}
	}

	/*
	 * XML Tag: <graph>
	 */

	/* obtain a list of all graph specific fields */
	$graph_fields = api_graph_form_list();

	if (isset($xml_array["graph"])) {
		/* get the base fields from the xml */
		foreach (array_keys($graph_fields) as $field_name) {
			if (isset($xml_array["graph"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["graph"][$field_name]);
			}
		}
	}

	/* make sure we got the required information before trying to save */
	if ((isset($xml_array["template"])) && (isset($xml_array["graph"]))) {
		/* save the graph template field to the database and register its new id */
		$graph_template_id = package_hash_update($object_hash, api_graph_template_save(0, $save_fields));
	}

	/* make sure the save completed successfully */
	if ($graph_template_id === false) {
		return;
	}

	/*
	 * XML Tag: <items>
	 */

	/* obtain a list of all graph template item specific fields */
	$graph_template_item_fields = api_graph_template_item_form_list();

	if ((isset($xml_array["items"])) && (is_array($xml_array["items"]))) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["items"] as $graph_template_item_hash => $graph_template_item) {
			$save_fields = array();

			/* make sure that each field is associated with the new graph template */
			$save_fields["graph_template_id"] = $graph_template_id;

			/* get the base fields from the xml */
			foreach (array_keys($graph_template_item_fields) as $field_name) {
				if (isset($graph_template_item[$field_name])) {
					if ($field_name == "data_template_item_id") {
						$save_fields[$field_name] = package_hash_resolve($graph_template_item[$field_name]);
					}else{
						$save_fields[$field_name] = xml_character_decode($graph_template_item[$field_name]);
					}
				}
			}

			/* save the data query field to the database and register its new id */
			package_hash_update($graph_template_item_hash, api_graph_template_item_save(0, $save_fields));
		}
	}

	/*
	 * XML Tag: <suggested_values>
	 */

	if (isset($xml_array["suggested_values"])) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["suggested_values"] as $field_array) {
			if ((isset($field_array["field_name"])) && (isset($field_array["sequence"])) && (isset($field_array["value"]))) {
				/* build an array containing each data input field */
				$save_fields{$field_array["field_name"]}[] = array("id" => "0", "value" => xml_character_decode($field_array["value"]));
			}
		}

		/* save the suggested values to the database */
		api_graph_template_suggested_values_save($graph_template_id, $save_fields);
	}
}

function package_data_template_import(&$xml_array, $package_id, $object_hash) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_update.php");

	$save_fields = array();

	/* tag the graph template as a package member */
	$save_fields["package_id"] = $package_id;

	/*
	 * XML Tag: <template>
	 */

	/* obtain a list of all data template specific fields */
	$data_template_fields = api_data_template_form_list();

	if (isset($xml_array["template"])) {
		/* get the base fields from the xml */
		foreach (array_keys($data_template_fields) as $field_name) {
			if (isset($xml_array["template"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["template"][$field_name]);
			}
		}
	}

	/*
	 * XML Tag: <data_source>
	 */

	/* obtain a list of all data source specific fields */
	$data_source_fields = api_data_source_form_list();

	if (isset($xml_array["data_source"])) {
		/* get the base fields from the xml */
		foreach (array_keys($data_source_fields) as $field_name) {
			if (isset($xml_array["data_source"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["data_source"][$field_name]);
			}
		}
	}

	/* make sure we got the required information before trying to save */
	if ((isset($xml_array["template"])) && (isset($xml_array["data_source"]))) {
		/* save the data template field to the database and register its new id */
		$data_template_id = package_hash_update($object_hash, api_data_template_save(0, $save_fields));
	}

	/* make sure the save completed successfully */
	if ($data_template_id === false) {
		return;
	}

	/*
	 * XML Tag: <items>
	 */

	/* obtain a list of all data source item specific fields */
	$data_source_item_fields = api_data_source_item_form_list();

	if ((isset($xml_array["items"])) && (is_array($xml_array["items"]))) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["items"] as $data_template_item_hash => $data_template_item) {
			$save_fields = array();

			/* make sure that each field is associated with the new data template */
			$save_fields["data_template_id"] = $data_template_id;

			/* get the base fields from the xml */
			foreach (array_keys($data_source_item_fields) as $field_name) {
				if (isset($data_template_item[$field_name])) {
					$save_fields[$field_name] = xml_character_decode($data_template_item[$field_name]);
				}
			}

			/* save the data template item to the database and register its new id */
			package_hash_update($data_template_item_hash, api_data_template_item_save(0, $save_fields));
		}
	}

	/*
	 * XML Tag: <fields>
	 */

	if (isset($xml_array["fields"])) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["fields"] as $field_array) {
			if ((isset($field_array["name"])) && (isset($field_array["t_value"])) && (isset($field_array["value"]))) {
				/* build an array containing each data input field */
				if ($field_array["name"] == "data_query_id") {
					$field_value = package_hash_resolve($field_array["value"]);
				}else if ($field_array["name"] == "script_id") {
					$field_value = package_hash_resolve($field_array["value"]);
				}else{
					$field_value = xml_character_decode($field_array["value"]);
				}

				$save_fields{$field_array["name"]} = array("t_value" => $field_array["t_value"], "value" => $field_value);
			}
		}

		/* save the fields to the database */
		api_data_template_input_fields_save($data_template_id, $save_fields);
	}

	/*
	 * XML Tag: <rra_items>
	 */

	/* obtain a list of all rra item preset specific fields */
	$rra_items_fields = api_data_preset_rra_item_form_list();

	if ((isset($xml_array["rra_items"])) && (is_array($xml_array["rra_items"]))) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["rra_items"] as $rra_item) {
			$save_fields = array();

			/* make sure that each field is associated with the new data template */
			$save_fields["data_template_id"] = $data_template_id;

			/* get the base fields from the xml */
			foreach (array_keys($rra_items_fields) as $field_name) {
				if (isset($rra_item[$field_name])) {
					$save_fields[$field_name] = xml_character_decode($rra_item[$field_name]);
				}
			}

			/* save the rra item to the database and register its new id */
			api_data_template_rra_item_save(0, $save_fields);
		}
	}

	/*
	 * XML Tag: <suggested_values>
	 */

	if (isset($xml_array["suggested_values"])) {
		$save_fields = array();

		/* get the base fields from the xml */
		foreach ($xml_array["suggested_values"] as $field_array) {
			if ((isset($field_array["field_name"])) && (isset($field_array["sequence"])) && (isset($field_array["value"]))) {
				/* build an array containing each data input field */
				$save_fields{$field_array["field_name"]}[] = array("id" => "0", "value" => xml_character_decode($field_array["value"]));
			}
		}

		/* save the suggested values to the database */
		api_data_template_suggested_values_save($data_template_id, $save_fields);
	}
}

function package_script_import(&$xml_array, $package_id, $object_hash) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");
	require_once(CACTI_BASE_PATH . "/lib/script/script_update.php");

	/*
	 * XML Tag: <script>
	 */

	/* obtain a list of all script specific fields */
	$script_fields = api_script_form_list();

	if (isset($xml_array["script"])) {
		$save_fields = array();

		/* tag the script as a package member */
		$save_fields["package_id"] = $package_id;

		/* get the base fields from the xml */
		foreach (array_keys($script_fields) as $field_name) {
			if (isset($xml_array["script"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["script"][$field_name]);
			}
		}

		/* save the script to the database and register its new id */
		$script_id = package_hash_update($object_hash, api_script_save(0, $save_fields));

		if ($script_id === false) {
			return;
		}
	}

	/*
	 * XML Tag: <fields>
	 */

	/* obtain a list of all data query field specific fields */
	$script_field_fields = api_script_field_form_list();

	if ((isset($xml_array["fields"])) && (is_array($xml_array["fields"]))) {
		/* loop through each available script field */
		foreach ($xml_array["fields"] as $script_field_hash => $script_field) {
			$save_fields = array();

			/* make sure that each field is associated with the new script */
			$save_fields["data_input_id"] = $script_id;

			/* get the base fields from the xml */
			foreach (array_keys($script_field_fields) as $field_name) {
				if (isset($script_field[$field_name])) {
					$save_fields[$field_name] = xml_character_decode($script_field[$field_name]);
				}
			}

			/* save the script field to the database and register its new id */
			api_script_field_save(0, $save_fields);
		}
	}
}

function package_data_query_import(&$xml_array, $package_id, $object_hash) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_update.php");

	/*
	 * XML Tag: <data_query>
	 */

	/* obtain a list of all data query specific fields */
	$data_query_fields = api_data_query_form_list();

	if (isset($xml_array["data_query"])) {
		$save_fields = array();

		/* tag the data query as a package member */
		$save_fields["package_id"] = $package_id;

		/* get the base fields from the xml */
		foreach (array_keys($data_query_fields) as $field_name) {
			if (isset($xml_array["data_query"][$field_name])) {
				$save_fields[$field_name] = xml_character_decode($xml_array["data_query"][$field_name]);
			}
		}

		/* save the data query to the database and register its new id */
		$data_query_id = package_hash_update($object_hash, api_data_query_save(0, $save_fields));

		if ($data_query_id === false) {
			return;
		}
	}

	/*
	 * XML Tag: <fields>
	 */

	/* obtain a list of all data query field specific fields */
	$data_query_field_fields = api_data_query_field_form_list();

	if ((isset($xml_array["fields"])) && (is_array($xml_array["fields"]))) {
		/* loop through each available data query field */
		foreach ($xml_array["fields"] as $data_query_field_hash => $data_query_field) {
			$save_fields = array();

			/* make sure that each field is associated with the new data query */
			$save_fields["data_query_id"] = $data_query_id;

			/* get the base fields from the xml */
			foreach (array_keys($data_query_field_fields) as $field_name) {
				if (isset($data_query_field[$field_name])) {
					$save_fields[$field_name] = xml_character_decode($data_query_field[$field_name]);
				}
			}

			/* save the data query field to the database and register its new id */
			api_data_query_field_save(0, $save_fields);
		}
	}
}

function package_hash_update($hash, $id) {
	global $package_hash_cache;

	$package_hash_cache[$hash] = $id;

	return $id;
}

function package_hash_resolve($hash) {
	global $package_hash_cache;

	if (isset($package_hash_cache[$hash])) {
		return $package_hash_cache[$hash];
	}else{
		return "0";
	}
}

?>
