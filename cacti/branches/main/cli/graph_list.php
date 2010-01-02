#!/usr/bin/php -q
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
$me = array_shift($parms);
$debug		= FALSE;	# no debug mode
$quietMode 	= FALSE;	# be verbose by default
$device 	= array();


if (sizeof($parms)) {
	/* setup defaults */
	$graph_type    				= "";
	$cg_input_fields 			= "";
	$input_fields  				= array();
	$values["cg"]  				= array();

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

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter, 2);

		switch($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			# to select the devices to act on, at least one parameter must be given to specify device list
			case "--device-id":		$device["id"] 					= trim($value);	break;
			case "--site-id":		$device["site_id"] 				= trim($value);	break;
			case "--poller-id":		$device["poller_id"]			= trim($value);	break;
			case "--description":	$device["description"] 			= trim($value);	break;
			case "--ip":			$device["hostname"] 			= trim($value);	break;
			case "--host_template_id":
			case "--template":		$device["host_template_id"]	 	= trim($value);	break;
			case "--community":		$device["snmp_community"] 		= trim($value);	break;
			case "--version":		$device["snmp_version"] 		= trim($value);	break;
			case "--notes":			$device["notes"] 				= trim($value);	break;
			case "--disabled":		$device["disabled"] 			= trim($value);	break;
			case "--username":		$device["snmp_username"] 		= trim($value);	break;
			case "--password":		$device["snmp_password"] 		= trim($value);	break;
			case "--authproto":		$device["snmp_auth_protocol"]	= trim($value);	break;
			case "--privproto":		$device["snmp_priv_protocol"] 	= trim($value);	break;
			case "--privpass":		$device["snmp_priv_passphrase"] = trim($value);	break;
			case "--context":		$device["snmp_context"] 		= trim($value);	break;
			case "--port":			$device["snmp_port"] 			= trim($value);	break;
			case "--timeout":		$device["snmp_timeout"] 		= trim($value);	break;
			case "--avail":			$device["availability_method"] 	= trim($value);	break;
			case "--ping-method":	$device["ping_method"] 			= trim($value);	break;
			case "--ping-port":		$device["ping_port"] 			= trim($value);	break;
			case "--ping-retries":	$device["ping_retries"] 		= trim($value);	break;
			case "--ping-timeout":	$device["ping_timeout"] 		= trim($value);	break;
			case "--max-oids":		$device["max_oids"] 			= trim($value);	break;

			# various paramaters for list options
			#case "--graph-type":	$graph_type 					= strtolower(trim($value));	break;
			case "--graph-template-id":	$graph_template_id 			= trim($value);	break;
			case "--graph-id":		$graph_id 						= trim($value);	break;
			case "--input-fields":	$cg_input_fields 				= trim($value);	break;

			case "--snmp-query-id":	$ds_graph["snmpQueryId"] 		= trim($value);	break;
			case "--snmp-query-type-id":$ds_graph["snmpQueryType"] 	= trim($value);	break;
			case "--snmp-field":	$ds_graph["snmpField"] 			= trim($value);	break;
			case "--snmp-field-spec":$ds_graph["snmpFieldSpec"] 	= trim($value);	break;
			case "--snmp-value":	$ds_graph["snmpValue"] 			= trim($value);	break;

			# various list options
			case "--list-snmp-fields":$listSNMPFields 				= TRUE;			break;
			case "--list-snmp-values":$listSNMPValues 				= TRUE;			break;
			case "--list-query-types":$listQueryTypes 				= TRUE;			break;
			case "--list-input-fields":$listInputFields 			= TRUE;			break;
			case "--list-graph-templates":$listGraphTemplates 		= TRUE;			break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}



	if ($listGraphTemplates) {		# list graph templates
		$graph_templates = array();
		if (isset($device["host_template_id"]) && !($device["host_template_id"] === 0)) {
			if (db_fetch_cell("SELECT id FROM host_template WHERE id=" . $device["host_template_id"])) {
				/* if a device Template Id is given, print the related Graph Templates */
				$graph_templates = getGraphTemplatesByHostTemplate($device["host_template_id"]);
			} else {
				echo __("ERROR: Invalid host-template-id (%d) given", $device["host_template_id"]) . "\n";
				echo __("Try -php -q device_template_list.php") . "\n";
				exit(1);
			}
		} else {
			$graph_templates = getGraphTemplates();
		}
		displayGraphTemplates($graph_templates, $quietMode);


	} elseif ($listInputFields) {	# list Input Fields
		if (isset($graph_template_id) && !($graph_template_id === 0) && (db_fetch_cell("SELECT id FROM graph_templates WHERE id=$graph_template_id"))) {
			$input_fields = getInputFields($graph_template_id, $quietMode);
			displayInputFields($input_fields, $quietMode);
		} else {
			echo __("ERROR: You must supply a valid --graph-template-id before you can list its input fields") . "\n";
			echo __("Try --list-graph-templates") . "\n";
			exit(1);
		}


	} elseif ($listQueryTypes) {	# list Data Query Types
		if (isset($ds_graph["snmpQueryId"]) && !($ds_graph["snmpQueryId"] === 0) && (db_fetch_cell("SELECT id FROM snmp_query WHERE id=" . $ds_graph["snmpQueryId"]))) {
			$snmp_query_types = getSNMPQueryTypes($ds_graph["snmpQueryId"]);
			displayQueryTypes($snmp_query_types, $quietMode);
			exit(0);
		} else {
			echo __("ERROR: You must supply a valid --snmp-query-id before you can list its query types") . "\n";
			echo __("Try php -q data_query_list.php") . "\n";
			exit(1);
		}


	} elseif ($listSNMPFields) {	# list SNMP Fields
		if (isset($device["id"]) && !($device["id"] === 0) && (db_fetch_cell("SELECT id FROM host WHERE id=" . $device["id"]))) {
			$snmpFields = getSNMPFields($device["id"], $ds_graph["snmpQueryId"]);
			displaySNMPFields($snmpFields, $device["id"], $quietMode);
			exit(0);
		} else {
			echo __("ERROR: You must supply a valid --device-id before you can list its SNMP fields") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			exit(1);
		}


	} elseif ($listSNMPValues)  {	# list SNMP Values
		if (isset($device["id"]) && !($device["id"] === 0) && (db_fetch_cell("SELECT id FROM host WHERE id=" . $device["id"]))) {

			$snmpValues = array();
			if ($ds_graph["snmpField"] != "") {
				/* snmp field(s) given: --list-snmp-values --device-id=[ID] --snmp-field=[Field] [--snmp-query-id=[ID]]*/
				/* get fields for query id (if any) */
				$snmpFields = getSNMPFields($device["id"], $ds_graph["snmpQueryId"]);
				if (!isset($snmpFields[$ds_graph["snmpField"]])) {
					echo __("ERROR: You must supply a valid --snmp-field (found: %s) before you can list its SNMP Values", $ds_graph["snmpField"]) . "\n";
					echo __("Try --list-snmp-fields") . "\n";
					exit(1);
				}
				/* get values for given field(s) and optional query id */
				$snmpValues = getSNMPValues($device["id"], $ds_graph["snmpField"], $ds_graph["snmpQueryId"]);
				displaySNMPValues($snmpValues, $device["id"], $ds_graph["snmpField"], $quietMode);
				exit (0);
			} else { /* snmp fields not given */
				if ($ds_graph["snmpQueryId"] == "") {
					/* snmp query id not given */
					echo __("ERROR: You must supply a valid --snmp-field or --snmp-query-id before you can list its SNMP Values") . "\n";
					echo __("Try --list-snmp-queries or --list-snmp-fields") . "\n";
					exit (1);
				} else {
					/* snmp query id given, no snmp field(s), optional snmp field spec */
					$rc = displaySNMPValuesExtended($device["id"], $ds_graph["snmpFieldSpec"], $ds_graph["snmpQueryId"], $quietMode);
					exit ($rc);
				}
			}
		} else {
			echo __("ERROR: You must supply a valid --device-id before you can list its SNMP values") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			exit(1);
		}


	} elseif (isset($graph_id)) {	# list a single graph
		/* Verify the graph's existance */
		$graph_exists = db_fetch_cell("SELECT id FROM graph_local WHERE id=$graph_id");
		if (empty($graph_exists)) {
			echo __("ERROR: Unknown Graph ID (%d)", $graph_id) . "\n";
			echo __("Try --list-graphs") . "\n";
			exit(1);
		} else {
			$selection = " WHERE graph_local.id=" . $graph_id;
			$columns = array();
			$graphs = getGraphs($selection, $columns);
			$title = __("List graph");
			/* display matching hosts */
			displayGenericArray($graphs, $columns, $title, $quietMode);
		}


	} else {			# list graphs related to the given device and/or graph template

		$selection = "";
		if (sizeof($device)) {
			# verify the parameters given
			$verify = verifyDevice($device, true);
			if (isset($verify["err_msg"])) {
				print $verify["err_msg"] . "\n\n";
				display_help($me);
				exit(1);
			}

			/* get devices matching criteria */
			$devices = getDevices($device);

			if (!sizeof($devices)) {
				echo __("ERROR: No matching Devices found") . "\n";
				echo __("Try php -q device_list.php") . "\n";
				exit(1);
			}

			/* form a valid sql statement for host_id */
			$selection = "WHERE " . str_replace("id", "host_id", array_to_sql_or($devices, "id")) . " ";
		}


		if (isset($graph_template_id) && !($graph_template_id === 0) && (db_fetch_cell("SELECT id FROM graph_templates WHERE id=$graph_template_id"))) {
			/* form a valid sql statement for host_id */
			$selection .= (strlen($selection) ? " AND " : " WHERE ") . " graph_templates.id=" . $graph_template_id;
		}

		$columns = array();
		$graphs = getGraphs($selection, $columns);
		$title = __("List of existing graphs for given device selection");
		/* display matching hosts */
		displayGenericArray($graphs, $columns, $title, $quietMode);
	}


} else {
	display_help($me);
	exit(1);
}

function display_help($me) {
	echo __("List Graphs Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list graphs in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --device-id=[ID] --graph-template-id=[ID] [--graph-id=]\n\n";
	echo __("List Options:") . "\n";
	echo "   --list-graph-templates [--host-template-id=[ID]]\n";
	echo "   --list-input-fields  --graph-template-id=[ID]\n";
	echo __("More list Options for 'ds' graphs only:") . "\n";
	echo "   --list-query-types   --snmp-query-id=[ID]\n";
	echo "   --list-snmp-fields   --device-id=[ID] [--snmp-query-id=[ID]]\n";
	echo "   --list-snmp-values   --device-id=[ID]  --snmp-query-id=[ID]\n";
	echo "   --list-snmp-values   --device-id=[ID]  --snmp-query-id=[ID]  --snmp-field-spec=[field1[,field2]...[,fieldn]]\n";
	echo "   --list-snmp-values   --device-id=[ID]  --snmp-field=[Field] [--snmp-query-id=[ID]]\n\n";
	echo __("'cg' graphs are for things like CPU temp/fan speed, while ") . "\n";
	echo __("'ds' graphs are for data-source based graphs (interface stats etc.)") . "\n";
	echo "   --graph-id          " . __("the numerical ID of the graph") . "\n";
	echo "   --graph-template-id " . __("the numerical ID of the graph template") . "\n";
	echo __("Optional parameters for device specification:") . "\n";
	echo "   --device-id         " . __("the numerical ID of the device") . "\n";
	echo "   --site-id           " . __("the numerical ID of the site") . "\n";
	echo "   --poller-id         " . __("the numerical ID of the poller") . "\n";
	echo "   --description       " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "   --ip                " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "   --template          " . __("denotes the device template to be used") . "\n";
	echo "                       " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                       " . __("For a device template=0 (NONE), Cacti default settings are used.") . "\n";
	echo "                       " . __("Optionally overwrite by any of the following:") . "\n";
	echo "   --notes             " . __("General information about this device. Must be enclosed using double quotes.") . "\n";
	echo "   --disable           " . __("to add this device but to disable checks and 0 to enable it") . " [0|1]\n";
	echo "   --avail             " . __("device availability check") . " [ping][none, snmp, pingsnmp]\n";
	echo "     --ping-method     " . __("if ping selected") . " [icmp|tcp|udp]\n";
	echo "     --ping-port       " . __("port used for tcp|udp pings") . " [1-65534]\n";
	echo "     --ping-retries    " . __("the number of time to attempt to communicate with a device") . "\n";
	echo "     --ping-timeout    " . __("ping timeout") . "\n";
	echo "   --version           " . __("snmp version") . " [1|2|3]\n";
	echo "   --community         " . __("snmp community string for snmpv1 and snmpv2. Leave blank for no community") . "\n";
	echo "   --port              " . __("snmp port") . "\n";
	echo "   --timeout           " . __("snmp timeout") . "\n";
	echo "   --username          " . __("snmp username for snmpv3") . "\n";
	echo "   --password          " . __("snmp password for snmpv3") . "\n";
	echo "   --authproto         " . __("snmp authentication protocol for snmpv3") . " [".SNMP_AUTH_PROTOCOL_MD5."|".SNMP_AUTH_PROTOCOL_SHA."]\n";
	echo "   --privpass          " . __("snmp privacy passphrase for snmpv3") . "\n";
	echo "   --privproto         " . __("snmp privacy protocol for snmpv3") . " [".SNMP_PRIV_PROTOCOL_DES."|".SNMP_PRIV_PROTOCOL_AES128."]\n";
	echo "   --context           " . __("snmp context for snmpv3") . "\n";
	echo "   --max-oids          " . __("the number of OID's that can be obtained in a single SNMP Get request") . " [1-60]\n";
	echo "   --quiet             " . __("batch mode value return") . "\n\n";
}
