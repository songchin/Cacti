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

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	/* setup defaults */
	$graph_type    				= "";
	$cg_input_fields 			= "";
	$host						= array();
	$input_fields  				= array();
	$values["cg"]  				= array();
	$template_graph 			= array();

	$ds_graph       			= array();
	$ds_graph["snmpFieldSpec"]  = "";
	$ds_graph["snmpQueryId"]    = "";
	$ds_graph["snmpQueryType"]  = "";
	$ds_graph["snmpField"]      = "";
	$ds_graph["snmpValue"]      = "";
	
	$listGraphTemplates 		= FALSE;
	$listSNMPFields  			= FALSE;
	$listSNMPValues  			= FALSE;
	$listQueryTypes  			= FALSE;
	$listInputFields 			= FALSE;
	$quietMode       			= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter, 2);

		switch($arg) {
		case "--graph-type":
			$graph_type = strtolower(trim($value));
			if (($graph_type != "cg") && ($graph_type != "ds")) {
				echo "ERROR: Invalid Graph Type: ($value)\n\n";
				display_help();
				exit(1);
			}

			break;
		case "--graph-template-id":
			$graph_template_id = $value;

			break;
		case "--host-template-id":
			$host_template_id = $value;

			break;
		case "--host-id":
			$host_id = $value;

			break;
		case "--input-fields":
			$cg_input_fields = $value;

			break;
		case "--snmp-query-id":
			$ds_graph["snmpQueryId"] = $value;

			break;
		case "--snmp-query-type-id":
			$ds_graph["snmpQueryType"] = $value;

			break;
		case "--snmp-field":
			$ds_graph["snmpField"] = $value;

			break;
		case "--snmp-field-spec" :
			$ds_graph["snmpFieldSpec"] = $value;

			break;
		case "--snmp-value":
			$ds_graph["snmpValue"] = $value;

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
		case "--list-input-fields":
			$listInputFields = TRUE;

			break;
		case "--list-graph-templates":
			$listGraphTemplates = TRUE;
			
			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
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

	if ($listGraphTemplates) {
		$graph_templates = array();
		if (isset($host_template_id) && !($host_template_id === 0)) {
			if (db_fetch_cell("SELECT id FROM host_template WHERE id=$host_template_id")) {
				/* if a Host Template Id is given, print the related Graph Templates */
				$graph_templates = getGraphTemplatesByHostTemplate($host_template_id);
			} else {
				echo "ERROR: Invalid host-template-id (" . $host_template_id . ") given\n";
				echo "Try -php -q host_template_list.php\n";
				exit(1);
			}
		} else {
			$graph_templates = getGraphTemplates();
		}
		displayGraphTemplates($graph_templates, $quietMode);
		exit(0);
	}


	if ($listInputFields) {
		if (isset($graph_template_id) && !($graph_template_id === 0) && (db_fetch_cell("SELECT id FROM graph_templates WHERE id=$graph_template_id"))) {
			$input_fields = getInputFields($graph_template_id, $quietMode);
			displayInputFields($input_fields, $quietMode);
		} else {
			echo "ERROR: You must supply a valid --graph-template-id before you can list its input fields\n";
			echo "Try --list-graph-templates\n";
			exit(1);
		}
		exit(0);
	}

	if ($listQueryTypes) {
		if ($graph_type != "ds") {
			echo "ERROR: Invalid Graph Type: ($value); expecting: ds\n\n";
			display_help();
			exit(1);
		}
		if (isset($ds_graph["snmpQueryId"]) && !($ds_graph["snmpQueryId"] === 0) && (db_fetch_cell("SELECT id FROM snmp_query WHERE id=" . $ds_graph["snmpQueryId"]))) {
			$snmp_query_types = getSNMPQueryTypes($ds_graph["snmpQueryId"]);
			displayQueryTypes($snmp_query_types, $quietMode);
			exit(0);
		} else {
			echo "ERROR: You must supply a valid --snmp-query-id before you can list its query types\n";
			echo "Try php -q data_query_list.php\n";
			exit(1);
		}
	}

	if ($listSNMPFields) {
		if ($graph_type != "ds") {
			echo "ERROR: Invalid Graph Type: ($value); expecting: ds\n\n";
			display_help();
			exit(1);
		}
		if (isset($host_id) && !($host_id === 0) && (db_fetch_cell("SELECT id FROM host WHERE id=$host_id"))) {
			$snmpFields = getSNMPFields($host_id, $ds_graph["snmpQueryId"]);
			displaySNMPFields($snmpFields, $host_id, $quietMode);
			exit(0);
		} else {
			echo "ERROR: You must supply a valid --host-id before you can list its SNMP fields\n";
			echo "Try php -q device_list.php\n";
			exit(1);
		}
	}

	if ($listSNMPValues)  {
		if ($graph_type != "ds") {
			echo "ERROR: Invalid Graph Type: ($value); expecting: ds\n\n";
			display_help();
			exit(1);
		}
		if (isset($host_id) && !($host_id === 0) && (db_fetch_cell("SELECT id FROM host WHERE id=$host_id"))) {
		
			$snmpValues = array();
			if ($ds_graph["snmpField"] != "") {
				/* snmp field(s) given: --list-snmp-values --host-id=[ID] --snmp-field=[Field] [--snmp-query-id=[ID]]*/
				/* get fields for query id (if any) */
				$snmpFields = getSNMPFields($host_id, $ds_graph["snmpQueryId"]);
				if (!isset($snmpFields[$ds_graph["snmpField"]])) {
					echo "ERROR: You must supply a valid --snmp-field (found:" . $ds_graph["snmpField"] . ") before you can list its SNMP Values\n";
					echo "Try --list-snmp-fields\n";
					exit(1);
				}
				/* get values for given field(s) and optional query id */
				$snmpValues = getSNMPValues($host_id, $ds_graph["snmpField"], $ds_graph["snmpQueryId"]);
				displaySNMPValues($snmpValues, $host_id, $ds_graph["snmpField"], $quietMode);
				exit (0);
			} else { /* snmp fields not given */
				if ($ds_graph["snmpQueryId"] == "") {
					/* snmp query id not given */
					echo "ERROR: You must supply a valid --snmp-field or --snmp-query-id before you can list its SNMP Values\n";
					echo "Try --list-snmp-queries or --list-snmp-fields\n";
					exit (1);
				} else {
					/* snmp query id given, no snmp field(s), optional snmp field spec */	
					$rc = displaySNMPValuesExtended($host_id, $ds_graph["snmpFieldSpec"], $ds_graph["snmpQueryId"], $quietMode);
					exit ($rc);
				}
			}
		} else {
			echo "ERROR: You must supply a valid --host-id before you can list its SNMP values\n";
			echo "Try php -q device_list.php\n";
			exit(1);
		}
	}
	
	/* nothing to do */
	echo "ERROR: No valid list option given\n\n";
	display_help();
	exit(1);
}else{
	display_help();
	exit(1);
}

function display_help() {
	echo "List Graphs Script 1.0, Copyright 2009 - The Cacti Group\n\n";
	echo "A simple command line utility to list graphs in Cacti\n\n";
	echo "usage: graph_list.php --graph-type=[cg|ds] --graph-template-id=[ID] --host-id=[ID]\n\n";
	echo "Options:\n";
	echo "    --list-graph-templates [--host-template-id=[ID]]\n";
	echo "    --list-input-fields     --graph-template-id=[ID]\n";
	echo "More list Options for 'ds' graphs only:\n";
	echo "    --list-query-types      --snmp-query-id=[ID]\n";
	echo "    --list-snmp-fields      --host-id=[ID] [--snmp-query-id=[ID]]\n";
	echo "    --list-snmp-values      --host-id=[ID]  --snmp-query-id=[ID]\n";
	echo "    --list-snmp-values      --host-id=[ID]  --snmp-query-id=[ID]  --snmp-field-spec=[field1[,field2]...[,fieldn]]\n";
	echo "    --list-snmp-values      --host-id=[ID]  --snmp-field=[Field] [--snmp-query-id=[ID]]\n\n";
	echo "    --quiet                 batch mode value return\n\n";
	echo "'cg' graphs are for things like CPU temp/fan speed, while \n";
	echo "'ds' graphs are for data-source based graphs (interface stats etc.)\n";
}

?>
