#!/usr/bin/php -q
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

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");
include_once(CACTI_BASE_PATH."/lib/utility.php");
include_once(CACTI_BASE_PATH."/lib/sort.php");
include_once(CACTI_BASE_PATH."/lib/template.php");
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");
include_once(CACTI_BASE_PATH."/lib/api_graph.php");
include_once(CACTI_BASE_PATH."/lib/snmp.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	/* setup defaults */
	$graph_type    = "";
	$templateGraph = array();
	$dsGraph       = array();
	$dsGraph["snmpFieldSpec"]  = "";
	$dsGraph["snmpQueryId"]    = "";
	$dsGraph["snmpQueryType"]  = "";
	$dsGraph["snmpField"]      = "";
	$dsGraph["snmpValue"]      = "";
	$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;

	$input_fields  = array();
	$values["cg"]  = array();

	$host			= array();
	$hosts          = getHosts($host);
	$graphTemplates = getGraphTemplates();

	$graphTitle = "";
	$cgInputFields = "";

	$hostId     	= 0;
	$templateId 	= 0;
	$hostTemplateId = 0;
	$force      	= 0;

	$listGraphTemplates 	= FALSE;
	$listSNMPFields  		= FALSE;
	$listSNMPValues  		= FALSE;
	$listQueryTypes  		= FALSE;
	$listInputFields 		= FALSE;

	$quietMode       = FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter, 2);

		switch($arg) {
		case "--graph-type":
			$graph_type = $value;

			break;
		case "--graph-title":
			$graphTitle = $value;

			break;
		case "--graph-template-id":
			$templateId = $value;

			break;
		case "--host-template-id":
			$hostTemplateId = $value;

			break;
		case "--device-id":
			$hostId = $value;

			break;
		case "--input-fields":
			$cgInputFields = $value;

			break;
		case "--snmp-query-id":
			$dsGraph["snmpQueryId"] = $value;

			break;
		case "--snmp-query-type-id":
			$dsGraph["snmpQueryType"] = $value;

			break;
		case "--snmp-field":
			$dsGraph["snmpField"] = $value;

			break;
		case "--snmp-field-spec" :
			$dsGraph["snmpFieldSpec"] = $value;

			break;
		case "--snmp-value":
			$dsGraph["snmpValue"] = $value;

			break;
		case "--reindex-method":
			if (is_numeric($value) &&
				($value >= DATA_QUERY_AUTOINDEX_NONE) &&
				($value <= DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION)) {
				$dsGraph["reindex_method"] = $value;
			} else {
				switch (strtolower($value)) {
					case "none":
						$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_NONE;
						break;
					case "uptime":
						$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;
						break;
					case "index":
						$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_INDEX_COUNT_CHANGE;
						break;
					case "fields":
						$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION;
						break;
					case "value":
						$dsGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_VALUE_CHANGE;
						break;
					default:
						echo __("ERROR: You must supply a valid reindex method for this graph!") . "\n";
						exit(1);
				}
			}

			break;
		case "--list-snmp-fields":
			$listSNMPFields = TRUE;

			break;
		case "--list-snmp-values":
			$listSNMPValues = TRUE;

			break;
		case "--list-query-types":
			$listQueryTypes = TRUE;

			break;
		case "--force":
			$force = TRUE;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		case "--list-input-fields":
			$listInputFields = TRUE;

			break;
		case "--list-graph-templates":
			$listGraphTemplates = TRUE;

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help($me);
			exit(1);
		}
	}

	if ($listGraphTemplates) {
		/* is a device Template Id is given, print the related Graph Templates */
		if ($hostTemplateId > 0) {
			$graphTemplates = getGraphTemplatesByHostTemplate($hostTemplateId);
			if (!sizeof($graphTemplates)) {
				echo __("ERROR: You must supply a valid --host-template-id before you can list its graph templates") . "\n";
				echo __("Try --list-graph-template-id --host-template-id=[ID]") . "\n";
				exit(1);
			}
		}

		displayGraphTemplates($graphTemplates, $quietMode);

		exit(0);
	}


	if ($listInputFields) {
		if ($templateId > 0) {
			$input_fields = getInputFields($templateId, $quietMode);
			displayInputFields($input_fields, $quietMode);
		} else {
			echo __("ERROR: You must supply an graph-template-id before you can list its input fields") . "\n";
			echo __("Try --graph-template-id=[ID] --list-input-fields") . "\n";
			exit(1);
		}

		exit(0);
	}

	/* get the existing snmp queries */
	$snmpQueries = getSNMPQueries();

	/* Some sanity checking... */
	if ($dsGraph["snmpQueryId"] != "") {
		if (!isset($snmpQueries[$dsGraph["snmpQueryId"]])) {
			printf(__("ERROR: Unknown snmp-query-id (%d)\n"), $dsGraph["snmpQueryId"]);
			echo __("Try --list-snmp-queries") . "\n";
			exit(1);
		}

		/* get the snmp query types for comparison */
		$snmp_query_types = getSNMPQueryTypes($dsGraph["snmpQueryId"]);

		if ($listQueryTypes) {
			displayQueryTypes($snmp_query_types, $quietMode);
			exit(0);
		}

		if ($dsGraph["snmpQueryType"] != "") {
			if (!isset($snmp_query_types[$dsGraph["snmpQueryType"]])) {
				printf(__("ERROR: Unknown snmp-query-type-id (%s)\n"), $dsGraph["snmpQueryType"]);
				printf(__("Try --snmp-query-id=%d --list-query-types\n"), $dsGraph["snmpQueryId"]);
				exit(1);
			}
		}

		if (!($listHosts ||			# you really want to create a new graph
			$listSNMPFields || 		# add this check to avoid reindexing on any list option
			$listSNMPValues ||
			$listQueryTypes ||
			$listInputFields)) {

			/* if data query is not yet associated,
			 * add it and run it once to get the cache filled */

			/* is this data query already associated (independent of the reindex method)? */
			$exists_already = db_fetch_cell("SELECT COUNT(host_id) FROM host_snmp_query WHERE host_id=$hostId AND snmp_query_id=" . $dsGraph["snmpQueryId"]);
			if ((isset($exists_already)) &&
				($exists_already > 0)) {
				/* yes: do nothing, everything's fine */
			}else{
				db_execute("REPLACE INTO host_snmp_query (host_id,snmp_query_id,reindex_method) " .
						   "VALUES (". $hostId . ","
									 . $dsGraph["snmpQueryId"] . ","
									 . $dsGraph["reindex_method"] .
									")");
				/* recache snmp data, this is time consuming,
				 * but should happen only once even if multiple graphs
				 * are added for the same data query
				 * because we checked above, if dq was already associated */
				run_data_query($hostId, $dsGraph["snmpQueryId"]);
			}
		}
	}

	/* Verify the device's existance */
	if (!isset($hosts[$hostId]) || $hostId == 0) {
		printf(__("ERROR: Unknown Device ID (%d)\n"), $hostId);
		echo __("Try php -q device_list.php") . "\n";
		exit(1);
	}

	/* process the snmp fields */
	$snmpFields = getSNMPFields($hostId, $dsGraph["snmpQueryId"]);

	if ($listSNMPFields) {
		displaySNMPFields($snmpFields, $hostId, $quietMode);
		exit(0);
	}

	$snmpValues = array();

	/* More sanity checking */
	if ($dsGraph["snmpField"] != "") {
		if (!isset($snmpFields[$dsGraph["snmpField"]])) {
			printf(__("ERROR: Unknown snmp-field %1s for device %2d\n"), $dsGraph["snmpField"], $hostId);
			echo __("Try --list-snmp-fields") . "\n";
			exit(1);
		}

		$snmpValues = getSNMPValues($hostId, $dsGraph["snmpField"], $dsGraph["snmpQueryId"]);

		if ($dsGraph["snmpValue"] != "") {
			if(!isset($snmpValues[$dsGraph["snmpValue"]])) {
				printf(__("ERROR: Unknown snmp-value for field %1s - %2d\n"), $dsGraph["snmpField"], $dsGraph["snmpValue"]);
				printf(__("Try --snmp-field=%s --list-snmp-values\n"), $dsGraph["snmpField"]);
				exit(1);
			}
		}
	}

	if ($listSNMPValues)  {
		if ($dsGraph["snmpField"] == "") {
			if ($dsGraph["snmpQueryId"] == "") {
				echo __("ERROR: Unknown snmp-query-id") . "\n";
				echo __("Try --list-snmp-queries") . "\n";
				exit (1);
			}

			$rc = displaySNMPValuesExtended($hostId, $dsGraph["snmpFieldSpec"], $dsGraph["snmpQueryId"], $quietMode);
			exit ($rc);

		} else {

			displaySNMPValues($snmpValues, $hostId, $dsGraph["snmpField"], $quietMode);
			exit (0);

		}
	}

	if (!isset($graphTemplates[$templateId])) {
		printf(__("ERROR: Unknown Graph Template ID (%d)\n"), $templateId);
		echo __("Try --list-graph-templates") . "\n";
		exit(1);
	}

	if ((!isset($templateId)) || (!isset($hostId))) {
		echo __("ERROR: Must have at least a device-id and a graph-template-id") . "\n\n";
		display_help($me);
		exit(1);
	}

	if (strlen($cgInputFields)) {
		$fields = explode(" ", $cgInputFields);
		if ($templateId > 0) {
			$input_fields = getInputFields($templateId, $quietMode);
		}

		if (sizeof($fields)) {
			foreach ($fields as $option) {
				$data_template_id = 0;
				$option_value = explode("=", $option);

				if (substr_count($option_value[0], ":")) {
					$compound = explode(":", $option_value[0]);
					$data_template_id = $compound[0];
					$field_name       = $compound[1];
				}else{
					$field_name       = $option_value[0];
				}

				/* check for the input fields existance */
				$field_found = FALSE;
				if (sizeof($input_fields)) {
					foreach ($input_fields as $key => $row) {
						if (substr_count($key, $field_name)) {
							if ($data_template_id == 0) {
								$data_template_id = $row["data_template_id"];
							}

							$field_found = TRUE;

							break;
						}
					}
				}

				if (!$field_found) {
					printf(__("ERROR: Unknown input-field (%s)\n"), $field_name);
					echo __("Try --list-input-fields") . "\n";
					exit(1);
				}

				$value = $option_value[1];

				$values["cg"][$templateId]["custom_data"][$data_template_id][$input_fields[$data_template_id . ":" . $field_name]["data_input_field_id"]] = $value;
			}
		}
	}

	$returnArray = array();

	if ($graph_type == "cg") {
		$existsAlready = db_fetch_cell("SELECT id FROM graph_local WHERE graph_template_id=$templateId AND host_id=$hostId");

		if ((isset($existsAlready)) &&
			($existsAlready > 0) &&
			(!$force)) {
			$dataSourceId  = db_fetch_cell("SELECT
				data_template_rrd.local_data_id
				FROM graph_templates_item, data_template_rrd
				WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
				AND graph_templates_item.task_item_id = data_template_rrd.id
				LIMIT 1");

			printf(__("NOTE: Not Adding Graph - this graph already exists - graph-id: (%1s) - data-source-id: (%2d)\n"), $existsAlready, $dataSourceId);
			exit(1);
		}else{
			$dataSourceId = "";
			$returnArray = create_complete_graph_from_template($templateId, $hostId, "", $values["cg"]);
		}

		if ($graphTitle != "") {
			db_execute("UPDATE graph_templates_graph
				SET title=\"$graphTitle\"
				WHERE local_graph_id=" . $returnArray["local_graph_id"]);

			update_graph_title_cache($returnArray["local_graph_id"]);
		}

		foreach($returnArray["local_data_id"] as $item) {
			push_out_host($hostId, $item);

			if (strlen($dataSourceId)) {
				$dataSourceId .= ", " . $item;
			}else{
				$dataSourceId = $item;
			}
		}

		/* add this graph template to the list of associated graph templates for this device */
		db_execute("replace into host_graph (host_id,graph_template_id) values (" . $hostId . "," . $templateId . ")");

		printf(__("Graph Added - graph-id: (%1d) - data-source-ids: (%2d)\n"), $returnArray["local_graph_id"], $dataSourceId);
	}elseif ($graph_type == "ds") {
		if (($dsGraph["snmpQueryId"] == "") || ($dsGraph["snmpQueryType"] == "") || ($dsGraph["snmpField"] == "") || ($dsGraph["snmpValue"] == "")) {
			echo __("ERROR: For graph-type of 'ds' you must supply more options") . "\n";
			display_help($me);
			exit(1);
		}

		$snmp_query_array = array();
		$snmp_query_array["snmp_query_id"]       = $dsGraph["snmpQueryId"];
		$snmp_query_array["snmp_index_on"]       = get_best_data_query_index_type($hostId, $dsGraph["snmpQueryId"]);
		$snmp_query_array["snmp_query_graph_id"] = $dsGraph["snmpQueryType"];

		$snmp_indexes = db_fetch_assoc("SELECT snmp_index
			FROM host_snmp_cache
			WHERE host_id=" . $hostId . "
			AND snmp_query_id=" . $dsGraph["snmpQueryId"] . "
			AND field_name='" . $dsGraph["snmpField"] . "'
			AND field_value='" . $dsGraph["snmpValue"] . "'");

		if (sizeof($snmp_indexes)) {
			foreach ($snmp_indexes as $snmp_index) {
				$snmp_query_array["snmp_index"] = $snmp_index["snmp_index"];

				$existsAlready = db_fetch_cell("SELECT id
					FROM graph_local
					WHERE graph_template_id=$templateId
					AND host_id=$hostId
					AND snmp_query_id=" . $dsGraph["snmpQueryId"] . "
					AND snmp_index='" . $snmp_query_array["snmp_index"] . "'");

				if (isset($existsAlready) && $existsAlready > 0) {
					if ($graphTitle != "") {
						db_execute("UPDATE graph_templates_graph
							SET title = \"$graphTitle\"
							WHERE local_graph_id = $existsAlready");

						update_graph_title_cache($existsAlready);
					}

					$dataSourceId = db_fetch_cell("SELECT
						data_template_rrd.local_data_id
						FROM graph_templates_item, data_template_rrd
						WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
						AND graph_templates_item.task_item_id = data_template_rrd.id
						LIMIT 1");

					printf(__("NOTE: Not Adding Graph - this graph already exists - graph-id: (%1d) - data-source-id: (%2d)\n"), $existsAlready, $dataSourceId);

					continue;
				}

				$empty = array(); /* Suggested Values are not been implemented */

				$returnArray = create_complete_graph_from_template($templateId, $hostId, $snmp_query_array, $empty);

				if ($graphTitle != "") {
					db_execute("UPDATE graph_templates_graph
						SET title=\"$graphTitle\"
						WHERE local_graph_id=" . $returnArray["local_graph_id"]);

					update_graph_title_cache($returnArray["local_graph_id"]);
				}

				$dataSourceId = db_fetch_cell("SELECT
					data_template_rrd.local_data_id
					FROM graph_templates_item, data_template_rrd
					WHERE graph_templates_item.local_graph_id = " . $returnArray["local_graph_id"] . "
					AND graph_templates_item.task_item_id = data_template_rrd.id
					LIMIT 1");

				foreach($returnArray["local_data_id"] as $item) {
					push_out_host($hostId, $item);

					if (strlen($dataSourceId)) {
						$dataSourceId .= ", " . $item;
					}else{
						$dataSourceId = $item;
					}
				}

				printf(__("Graph Added - graph-id: (%1d) - data-source-ids: (%2d)\n"), $returnArray["local_graph_id"], $dataSourceId);
			}
		}else{
			printf(__("ERROR: Could not find snmp-field %1s (%2d) for device-id %3d (%4s)\n"), $dsGraph["snmpField"], $dsGraph["snmpValue"], $hostId, $hosts[$hostId]["hostname"]);
			printf(__("Try --device-id=%s --list-snmp-fields\n"), $hostId);
			exit(1);
		}
	}else{
		echo __("ERROR: Graph Types must be either 'cg' or 'ds'") . "\n";
		exit(1);
	}

	exit(0);
}else{
	display_help($me);
	exit(1);
}

function display_help($me) {
	echo __("Add Graphs Script 1.2") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add graphs in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --graph-type=[cg|ds] --graph-template-id=[ID]\n";
	echo "    --device-id=[ID] [--graph-title=title] [graph options] [--force] [--quiet]\n\n";
	echo __("For cg graphs:") . "\n";
	echo "  [--input-fields=\ [data-template-id:]field-name=value ...\"] [--force]\n\n";
	echo "   --input-fields   " . __("If your data template allows for custom input data, you may specify that") . "\n";
	echo "                    " . __("here.  The data template id is optional and applies where two input fields") . "\n";
	echo "                    " . __("have the same name.") . "\n";
	echo "   --force          " . __("If you set this flag, then new cg graphs will be created, even though they") . "\n";
	echo "                    " . __("may already exist") . "\n\n";
	echo __("For ds graphs:") . "\n";
	echo "   --snmp-query-id=[ID] --snmp-query-type-id=[ID] --snmp-field=[SNMP Field] --snmp-value=[SNMP Value]\n\n";
	echo "  [--graph-title=]  " . __("Defaults to what ever is in the graph template/data-source template.") . "\n\n";
	echo "   --reindex-method " . __("the reindex method to be used for that data query") . "\n";
	echo "            0|None   " . __("no reindexing") . "\n";
	echo "            1|Uptime " . __("Uptime goes Backwards") . "\n";
	echo "            2|Index  " . __("Index Count Changed") . "\n";
	echo "            3|Fields " . __("Verify all Fields") . "\n";
	echo "            4|Value  " . __("Re-Index Value Changed") . "\n";
	echo __("List Options:") . "\n";
	echo "   --list-graph-templates [--host_template=[ID]]\n";
	echo "   --list-input-fields --graph-template-id=[ID]\n";
	echo __("More list Options for 'ds' graphs only:") . "\n";
	echo "   --list-query-types  --snmp-query-id=[ID]\n";
	echo "   --list-snmp-fields  --device-id=[ID]\n";
	echo "   --list-snmp-values  --device-id=[ID] --snmp-query-id=[ID]\n";
	echo "   --list-snmp-values  --device-id=[ID] --snmp-query-id=[ID] --snmp-field-spec=[field1[,field2]...[,fieldn]]\n";
	echo "   --list-snmp-values  --device-id=[ID] --snmp-field=[Field]\n\n";
	echo __("'cg' graphs are for things like CPU temp/fan speed, while ") . "\n";
	echo __("'ds' graphs are for data-source based graphs (interface stats etc.)") . "\n";
}
