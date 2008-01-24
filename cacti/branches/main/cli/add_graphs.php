#!/usr/bin/php -q
<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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
include_once($config["base_path"]."/lib/api_automation_tools.php");
include_once($config["base_path"]."/lib/data_query.php");
include_once($config["base_path"]."/lib/utility.php");
include_once($config["base_path"]."/lib/sort.php");
include_once($config["base_path"]."/lib/template.php");
include_once($config["base_path"]."/lib/api_data_source.php");
include_once($config["base_path"]."/lib/api_graph.php");
include_once($config["base_path"]."/lib/snmp.php");
include_once($config["base_path"]."/lib/data_query.php");
include_once($config["base_path"]."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	/* setup defaults */
	$graph_type    = "";
	$templateGraph = array();
	$dsGraph       = array();
	$dsGraph["snmpFieldSpec"] = "";
	$input_fields  = array();
	$values["cg"]  = array();

	$hosts          = getHosts();
	$graphTemplates = getGraphTemplates();

	$graphTitle = "";
	$cgInputFields = "";

	$hostId     = 0;
	$templateId = 0;
	$force      = 0;

	$listHosts       = FALSE;
	$listSNMPFields  = FALSE;
	$listSNMPValues  = FALSE;
	$listQueryTypes  = FALSE;
	$listSNMPQueries = FALSE;
	$listInputFields = FALSE;

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
		case "--host-id":
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
		case "--list-hosts":
			$listHosts = TRUE;

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
		case "--list-snmp-queries":
			$listSNMPQueries = TRUE;

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
			displayGraphTemplates($graphTemplates, $quietMode);
			exit(0);
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help();
			exit(0);
		default:
			echo "ERROR: Invalid Argument: ($arg)\n\n";
			display_help();
			exit(1);
		}
	}

	if ($templateId > 0) {
		$input_fields = getInputFields($templateId, $quietMode);
	}

	if ($listInputFields) {
		if ($templateId > 0) {
			displayInputFields($input_fields, $quietMode);
		} else {
			echo "ERROR: You must supply an graph-template-id before you can list its input fields\n";
			echo "Try --graph-template-id=[ID] --list-input-fields\n";
			exit(1);
		}

		exit(0);
	}

	if ($listHosts) {
		displayHosts($hosts, $quietMode);
		exit(0);
	}

	/* get the existing snmp queries */
	$snmpQueries = getSNMPQueries();

	if ($listSNMPQueries) {
		displaySNMPQueries($snmpQueries, $quietMode);
		exit(0);
	}

	/* Some sanity checking... */
	if (isset($dsGraph["snmpQueryId"])) {
		if (!isset($snmpQueries[$dsGraph["snmpQueryId"]])) {
			echo "ERROR: Unknown snmp-query-id (" . $dsGraph["snmpQueryId"] . ")\n";
			echo "Try --list-snmp-queries\n";
			exit(1);
		}

		/* get the snmp query types for comparison */
		$snmp_query_types = getSNMPQueryTypes($dsGraph["snmpQueryId"]);

		if ($listQueryTypes) {
			displayQueryTypes($snmp_query_types, $quietMode);
			exit(0);
		}

		if (isset($dsGraph["snmpQueryType"])) {
			if (!isset($snmp_query_types[$dsGraph["snmpQueryType"]])) {
				echo "ERROR: Unknown snmp-query-type-id (" . $dsGraph["snmpQueryType"] . ")\n";
				echo "Try --snmp-query-id=" . $dsGraph["snmpQueryId"] . " --list-query-types\n";
				exit(1);
			}
		}
	}

	/* Verify the host's existance */
	if (!isset($hosts[$hostId]) || $hostId == 0) {
		echo "ERROR: Unknown Host ID ($hostId)\n";
		echo "Try --list-hosts\n";
		exit(1);
	}

	/* process the snmp fields */
	$snmpFields = getSNMPFields($hostId);

	if ($listSNMPFields) {
		displaySNMPFields($snmpFields, $hostId, $quietMode);
		exit(0);
	}

	$snmpValues = array();

	/* More sanity checking */
	if (isset($dsGraph["snmpField"])) {
		if (!isset($snmpFields[$dsGraph["snmpField"]])) {
			echo "ERROR: Unknown snmp-field " . $dsGraph["snmpField"] . " for host $hostId\n";
			echo "Try --list-snmp-fields\n";
			exit(1);
		}

		$snmpValues = getSNMPValues($hostId, $dsGraph["snmpField"]);

		if (isset($dsGraph["snmpValue"])) {
			if(!isset($snmpValues[$dsGraph["snmpValue"]])) {
				echo "ERROR: Unknown snmp-value for field " . $dsGraph["snmpField"] . " - " . $dsGraph["snmpValue"] . "\n";
				echo "Try --snmp-field=" . $dsGraph["snmpField"] . " --list-snmp-values\n";
				exit(1);
			}
		}
	}

	if ($listSNMPValues)  {
		if (!isset ($dsGraph["snmpField"])) {
			if (!isset ($dsGraph["snmpQueryId"])) {
				echo "ERROR: Unknown snmp-query-id\n";
				echo "Try --list-snmp-queries\n";
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
		echo "ERROR: Unknown graph-template-id (" . $templateId . ")\n";
		echo "Try --list-graph-templates\n";
		exit(1);
	}

	if ((!isset($templateId)) || (!isset($hostId))) {
		echo "ERROR: Must have at least a host-id and a graph-template-id\n\n";
		display_help();
		exit(1);
	}

	if (strlen($cgInputFields)) {
		$fields = explode(" ", $cgInputFields);

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
					echo "ERROR: Unknown input-field (" . $field_name . ")\n";
					echo "Try --list-input-fields\n";
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
		$dataSourceId  = db_fetch_cell("SELECT DISTINCT
			data_template_rrd.local_data_id
			FROM graph_templates_item, data_template_rrd
			WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
			AND graph_templates_item.task_item_id = data_template_rrd.id");

		if ((isset($existsAlready)) &&
			($existsAlready > 0) &&
			(!$force)) {
			echo "ERROR: Not Adding Graph - this graph already exists - graph-id: ($existsAlready) - data-source-id: ($dataSourceId)\n";
			exit(1);
		}else{
			$returnArray = create_complete_graph_from_template($templateId, $hostId, "", $values["cg"]);
		}

		if ($graphTitle != "") {
			db_execute("UPDATE graph_templates_graph
				SET title=\"$graphTitle\"
				WHERE local_graph_id=" . $returnArray["local_graph_id"]);

			update_graph_title_cache($returnArray["local_graph_id"]);
		}

		push_out_host($hostId,0);

		$dataSourceId = db_fetch_cell("SELECT DISTINCT
			data_template_rrd.local_data_id
			FROM graph_templates_item, data_template_rrd
			WHERE graph_templates_item.local_graph_id = " . $returnArray["local_graph_id"] . "
			AND graph_templates_item.task_item_id = data_template_rrd.id");

		echo "Graph Added - graph-id: (" . $returnArray["local_graph_id"] . ") - data-source-id: ($dataSourceId)\n";
	}elseif ($graph_type == "ds") {
		if ((!isset($dsGraph["snmpQueryId"])) || (!isset($dsGraph["snmpQueryType"])) || (!isset($dsGraph["snmpField"])) || (!isset($dsGraph["snmpValue"]))) {
			echo "ERROR: For graph-type of 'ds' you must supply more options\n";
			display_help();
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

					$dataSourceId = db_fetch_cell("SELECT DISTINCT
						data_template_rrd.local_data_id
						FROM graph_templates_item, data_template_rrd
						WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
						AND graph_templates_item.task_item_id = data_template_rrd.id");

					echo "ERROR: Not Adding Graph - this graph already exists - graph-id: ($existsAlready) - data-source-id: ($dataSourceId)\n";

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

				$dataSourceId = db_fetch_cell("SELECT DISTINCT
					data_template_rrd.local_data_id
					FROM graph_templates_item, data_template_rrd
					WHERE graph_templates_item.local_graph_id = " . $returnArray["local_graph_id"] . "
					AND graph_templates_item.task_item_id = data_template_rrd.id");

				echo "Graph Added - graph-id: (" . $returnArray["local_graph_id"] . ") - data-source-id: ($dataSourceId)\n";
			}

			push_out_host($hostId,0);
		}else{
			echo "ERROR: Could not find snmp-field " . $dsGraph["snmpField"] . " (" . $dsGraph["snmpValue"] . ") for host-id " . $hostId . " (" . $hosts[$hostId]["hostname"] . ")\n";
			echo "Try --host-id=" . $hostId . " --list-snmp-fields\n";
			exit(1);
		}
	}else{
		echo "ERROR: Graph Types must be either 'cg' or 'ds'\n";
		exit(1);
	}

	exit(0);
}else{
	display_help();
	exit(1);
}

function display_help() {
	echo "Add Graphs Script 1.1, Copyright 2008 - The Cacti Group\n\n";
	echo "A simple command line utility to add graphs in Cacti\n\n";
	echo "usage: add_graphs.php --graph-type=[cg|ds] --graph-template-id=[ID]\n";
	echo "    --host-id=[ID] [--graph-title=title] [graph options] [--force] [--quiet]\n\n";
	echo "For cg graphs:\n";
	echo "    [--input-fields=\"[data-template-id:]field-name=value ...\"] [--force]\n\n";
	echo "    --input-fields  If your data template allows for custom input data, you may specify that\n";
	echo "                    here.  The data template id is optional and applies where two input fields\n";
	echo "                    have the same name.\n";
	echo "    --force         If you set this flag, then new cg graphs will be created, even though they\n";
	echo "                    may already exist\n\n";
	echo "For ds graphs:\n";
	echo "    --snmp-query-id=[ID] --snmp-query-type-id=[ID] --snmp-field=[SNMP Field] --snmp-value=[SNMP Value]\n\n";
	echo "    [--graph-title=] Defaults to what ever is in the graph template/data-source template.\n\n";
	echo "List Options:\n";
	echo "    --list-hosts\n";
	echo "    --list-graph-templates\n";
	echo "    --list-input-fields --graph-template-id=[ID]\n";
	echo "More list Options for 'cg' graphs only:\n";
	echo "    --list-snmp-queries\n";
	echo "    --list-query-types  --snmp-query-id=[ID]\n";
	echo "    --list-snmp-fields  --host-id=[ID]\n";
	echo "    --list-snmp-values  --host-id=[ID] --snmp-query-id=[ID]\n";
	echo "    --list-snmp-values  --host-id=[ID] --snmp-query-id=[ID] --snmp-field-spec=[field1[,field2]...[,fieldn]]\n";
	echo "    --list-snmp-values  --host-id=[ID] --snmp-field=[Field]\n\n";
	echo "'cg' graphs are for things like CPU temp/fan speed, while \n";
	echo "'ds' graphs are for data-source based graphs (interface stats etc.)\n";
}

?>
