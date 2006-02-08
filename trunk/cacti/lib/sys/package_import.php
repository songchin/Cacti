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

function package_import(&$xml) {
	$xml_array = xml_array_get($xml);

	if (sizeof($xml_array) == 0) {
		raise_message(7); /* xml parse error */
		return;
	}

	//$package_id = package_header_import($xml_array["header"]);

	package_payload_import($xml_array["payload"], $package_id);
}

function package_header_import(&$xml_array) {
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");
	require_once(CACTI_BASE_PATH . "/lib/package/package_update.php");

	print_a($xml_array);

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
				$save_fields[$field_name] = $xml_array["package"][$field_name];
			}
		}

		//api_package_save(0, $save_fields);
	}

}

function package_payload_import(&$xml_array, $package_id) {
	print_a($xml_array);
exit;
	if (isset($xml_array["round_robin_archive"])) {
		if (sizeof($xml_array["round_robin_archive"]) > 0) {
			foreach (array_keys($xml_array["round_robin_archive"]) as $object_hash) {
				package_rra_import($xml_array["round_robin_archive"][$object_hash], $package_id, $object_hash);
			}
		}
	}

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
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_source_info.php");
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
				$save_fields[$field_name] = $xml_array["template"][$field_name];
			}
		}
	}

	/*
	 * XML Tag: <graph>
	 */

	/* obtain a list of all graph specific fields */
	$graph_fields = api_data_source_form_list();

	if (isset($xml_array["graph"])) {
		/* get the base fields from the xml */
		foreach (array_keys($graph_fields) as $field_name) {
			if (isset($xml_array["graph"][$field_name])) {
				$save_fields[$field_name] = $xml_array["graph"][$field_name];
			}
		}
	}

	/* make sure we got the required information before trying to save */
	if ((isset($xml_array["template"])) && (isset($xml_array["graph"]))) {
		/* save the graph template field to the database and register its new id */
		$graph_template_id = package_hash_update($object_hash, api_graph_template_save(0, $save_fields));
	}

	/* make sure the save completed successfully */
	if (empty($graph_template_id)) {
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
						$save_fields[$field_name] = $graph_template_item[$field_name];
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
				$save_fields{$field_array["field_name"]} = array("sequence" => $field_array["sequence"], "value" => $field_array["value"]);
			}
		}

		/* save the suggested values to the database */
		api_graph_template_suggested_values_save($graph_template_id, $save_fields);
	}
}

function package_data_template_import(&$xml_array, $package_id, $object_hash) {
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
				$save_fields[$field_name] = $xml_array["template"][$field_name];
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
				$save_fields[$field_name] = $xml_array["data_source"][$field_name];
			}
		}
	}

	/* make sure we got the required information before trying to save */
	if ((isset($xml_array["template"])) && (isset($xml_array["data_source"]))) {
		/* save the data template field to the database and register its new id */
		$data_template_id = package_hash_update($object_hash, api_data_template_save(0, $save_fields, array()));
	}

	/* make sure the save completed successfully */
	if (empty($data_template_id)) {
		return;
	}

	/*
	 * XML Tag: <items>
	 */

	/* obtain a list of all data source item specific fields */
	$data_source_item_fields = api_data_source_form_list();

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
					$save_fields[$field_name] = $data_template_item[$field_name];
				}
			}

			/* save the data query field to the database and register its new id */
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
					$field_value = $field_array["value"];
				}

				$save_fields{$field_array["name"]} = array("t_value" => $field_array["t_value"], "value" => $field_value);
			}
		}

		/* save the fields to the database */
		api_data_template_input_fields_save($data_template_id, $save_fields);
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
				$save_fields{$field_array["field_name"]} = array("sequence" => $field_array["sequence"], "value" => $field_array["value"]);
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
				$save_fields[$field_name] = $xml_array["script"][$field_name];
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

	if (isset($xml_array["fields"])) {
		if (sizeof($xml_array["fields"]) > 0) {
			/* loop through each available script field */
			foreach ($xml_array["fields"] as $script_field_hash => $script_field) {
				$save_fields = array();

				/* make sure that each field is associated with the new script */
				$save_fields["data_input_id"] = $script_id;

				/* get the base fields from the xml */
				foreach (array_keys($script_field_fields) as $field_name) {
					if (isset($script_field[$field_name])) {
						$save_fields[$field_name] = $script_field[$field_name];
					}
				}

				/* save the script field to the database and register its new id */
				package_hash_update($script_field_hash, api_script_field_save(0, $save_fields));
			}
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
				$save_fields[$field_name] = $xml_array["data_query"][$field_name];
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
					$save_fields[$field_name] = $data_query_field[$field_name];
				}
			}

			/* save the data query field to the database and register its new id */
			package_hash_update($data_query_field_hash, api_data_query_field_save(0, $save_fields));
		}
	}
}

function package_rra_import(&$xml_array, $package_id, $object_hash) {
	require_once(CACTI_BASE_PATH . "/lib/rra/rra_info.php");

	$save_fields = array();

	/* tag the graph template as a package member */
	$save_fields["package_id"] = $package_id;

	/*
	 * XML Tag: <rra>
	 */

	/* obtain a list of all round robin archive specific fields */
	$rra_fields = api_rra_form_list();

	if (isset($xml_array["rra"])) {
		/* get the base fields from the xml */
		foreach (array_keys($rra_fields) as $field_name) {
			if (isset($xml_array["rra"][$field_name])) {
				$save_fields[$field_name] = $xml_array["rra"][$field_name];
			}
		}

		if (isset($xml_array["rra"]["cf_items"])) {
			$cf_list = explode("|", $xml_array["rra"]["cf_items"]);
		}

		// save
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
