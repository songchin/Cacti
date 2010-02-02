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
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
require_once(CACTI_BASE_PATH . "/include/device/device_constants.php");
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
$debug		= FALSE;	# no debug mode
$quietMode 	= FALSE;	# be verbose by default
$device 	= array();
$error		= '';

#$parms[] = "--device-id=5";
#$parms[] = "--graph-template-id=2";
#$parms[] = "--graph-type=ds";
#$parms[] = "--snmp-query-id=1";
#$parms[] = "--snmp-query-type-id=14";
#$parms[] = "--snmp-field=ifName";
#$parms[] = "--snmp-value=lo";

if (sizeof($parms)) {
	/* setup defaults */
	$graph_type = "";
	$graphTitle = "";
	$cgInputFields = "";
	$graph_template_id= 0;
	$force = FALSE;
	$quietMode = FALSE;
	$input_fields = array();
	$dqGraph = array();
	$dqGraph["snmp_query_id"] = "";
	$dqGraph["snmp_query_graph_id"] = "";
	$dqGraph["snmp_field"] = "";
	$dqGraph["snmp_value"] = "";
	$dqGraph["reindex_method"] = DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;


	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter, 2);

		switch($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			# to select the devices to act on, at least one parameter must be given
			case "--device-id":		$device["id"] 					= trim($value);	break;
			case "--site-id":		$device["site_id"] 				= trim($value);	break;
			case "--poller-id":		$device["poller_id"]			= trim($value);	break;
			case "--description":	$device["description"] 			= trim($value);	break;
			case "--ip":			$device["hostname"] 			= trim($value);	break;
			case "--template":		$device["device_template_id"]	 	= trim($value);	break;
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
			case "--device-threads":$device["device_threads"] 		= trim($value);	break;

			# required for specifying the graph to be added
			case "--graph-type":	$graph_type 					= trim($value);	break;
			case "--graph-template-id":	$graph_template_id 			= trim($value);	break;
			case "--graph-title":	$graphTitle 					= trim($value);	break;

			# optional for cg graphs
			case "--input-fields":	$cgInputFields 					= trim($value);	break;
			case "--force":			$force 							= TRUE;			break;

			# for ds graphs
			case "--data-query-id":
			case "--snmp-query-id":	$dqGraph["snmp_query_id"] 		= trim($value);	break;
			case "--snmp-query-type-id": $dqGraph["snmp_query_graph_id"]= trim($value);	break;
			case "--snmp-field":	$dqGraph["snmp_field"] 			= trim($value);	break;
			case "--snmp-value":	$dqGraph["snmp_value"] 			= trim($value);	break;
			case "--reindex-method":$dqGraph["reindex_method"] 		= trim($value);	break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}


	# at least one matching criteria for device(s) has to be defined
	if (!sizeof($device)) {
		print __("ERROR: No device matching criteria found") . "\n\n";
		exit(1);
	}
	if (!isset($graph_template_id)) {
		echo __("ERROR: No graph-template-id given") . "\n\n";
		display_help($me);
		exit(1);
	}

	# now verify the parameters given
	$verify = verifyDevice($device, true);
	if (isset($verify["err_msg"])) {
		print $verify["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}

	$graph_template = db_fetch_row("SELECT id, name FROM graph_templates WHERE id=" . $graph_template_id);
	if (!sizeof($graph_template)) {
		echo __("ERROR: This Graph template id does not exist (%s)", $value) . "\n";
		echo __("Try php -q graph_list.php --list-graph-templates") . "\n";
		exit(1);
	}


	# let's do it, now
	$returnArray = array();

	if (strtolower($graph_type) == "cg") {
		# input fields are optional
		if ($graph_template["id"] > 0) {
			$input_fields = getInputFields($graph_template["id"], $quietMode);
		}
		$values = verifyGraphInputFields($cgInputFields, $input_fields);

		/* get devices matching criteria */
		$devices = getDevices($device);
		if (!sizeof($devices)) {
			echo __("ERROR: No matching Devices found") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			exit(1);
		}

		foreach ($devices as $device) {
			# now create cg type graph(s)
			createDIGraph($device, $graph_template, $values, $graphTitle, $force);
		}


	} elseif (strtolower($graph_type) == "ds") {
		/* get devices matching criteria */
		$devices = getDevices($device);
		if (!sizeof($devices)) {
			echo __("ERROR: No matching Devices found") . "\n";
			echo __("Try php -q device_list.php") . "\n";
			exit(1);
		}

		$dqGraph["graph_template_id"] = $graph_template["id"];
		foreach ($devices as $device) {
			$dqGraph["device_id"] = $device["id"];
			if (sizeof($dqGraph)) {
				# verify the parameters given
				$verify = verifyDQGraph($dqGraph, true);
				if (isset($verify["err_msg"])) {
					print $verify["err_msg"] . "\n\n";
					display_help($me);
					exit(1);
				}
			}
			# now create data query type graph(s)
			createDQGraph($dqGraph, $graphTitle, $force);
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


function createDIGraph($device, $graph_template, $input_values, $graphTitle, $force) {

	$existsAlready = db_fetch_cell("SELECT id FROM graph_local WHERE graph_template_id=" . $graph_template["id"] . " AND device_id=" . $device["id"]);

	if ((isset($existsAlready)) &&
	($existsAlready > 0) &&
	(!$force)) {
		$dataSourceId  = db_fetch_cell("SELECT
				data_template_rrd.local_data_id
				FROM graph_templates_item, data_template_rrd
				WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
				AND graph_templates_item.task_item_id = data_template_rrd.id
				LIMIT 1");

		echo __("NOTE: Not Adding Graph - this graph already exists - device-id: (%d) - graph-id: (%s) - data-source-id: (%d)", $device["id"], $existsAlready, $dataSourceId) . "\n";
		exit(1);
	}else{
		$dataSourceId = "";
		$returnArray = create_complete_graph_from_template($graph_template["id"], $device["id"], "", $input_values["cg"]);

		if ($graphTitle != "") {
			db_execute("UPDATE graph_templates_graph
					SET title=\"$graphTitle\"
					WHERE local_graph_id=" . $returnArray["local_graph_id"]);

			update_graph_title_cache($returnArray["local_graph_id"]);
		}

		foreach($returnArray["local_data_id"] as $item) {
			push_out_device($device["id"], $item);
			$dataSourceId .= (strlen($dataSourceId) ? ", " : "") . $item;
		}

		/* add this graph template to the list of associated graph templates for this device, if not yet present */
		db_execute("REPLACE INTO device_graph (device_id,graph_template_id) VALUES (" . $device["id"] . "," . $graph_template["id"] . ")");

		echo __("Graph Added - device-id: (%d) - graph-id: (%d) - data-source-ids: (%d)", $device["id"], $returnArray["local_graph_id"], $dataSourceId) . "\n";
	}
}



function createDQGraph($snmp_query_array, $graphTitle, $force) {

	/* is this data query already associated (independent of the reindex method) */
	$exists_already = db_fetch_cell("SELECT COUNT(device_id) FROM device_snmp_query WHERE device_id=" . $snmp_query_array["device_id"] . " AND snmp_query_id=" . $snmp_query_array["snmp_query_id"]);
	if ((isset($exists_already)) &&
	($exists_already > 0)) {
		/* yes: do nothing, everything's fine */
	}else{
		db_execute("REPLACE INTO device_snmp_query (device_id,snmp_query_id,reindex_method) " .
					   "VALUES (" .
		$snmp_query_array["device_id"] . "," .
		$snmp_query_array["snmp_query_id"] . "," .
		$snmp_query_array["reindex_method"] .
						")");
		/* recache snmp data, this is time consuming,
		 * but should happen only once even if multiple graphs
		 * are added for the same data query
		 * because we checked above, if dq was already associated */
		run_data_query($snmp_query_array["device_id"], $snmp_query_array["snmp_query_id"]);
	}

	$snmp_query_array["snmp_index_on"] = get_best_data_query_index_type($snmp_query_array["device_id"], $snmp_query_array["snmp_query_id"]);

	$snmp_indexes = db_fetch_assoc("SELECT snmp_index " .
										"FROM device_snmp_cache " .
										"WHERE device_id=" . $snmp_query_array["device_id"] . " " .
										"AND snmp_query_id=" . $snmp_query_array["snmp_query_id"] . " " .
										"AND field_name='" . $snmp_query_array["snmp_field"] . "' " .
										"AND field_value='" . $snmp_query_array["snmp_value"] . "'");

	if (sizeof($snmp_indexes)) {
		foreach ($snmp_indexes as $snmp_index) {
			$snmp_query_array["snmp_index"] = $snmp_index["snmp_index"];

			$existsAlready = db_fetch_cell("SELECT id " .
												"FROM graph_local " .
												"WHERE graph_template_id=" . $snmp_query_array["graph_template_id"] . " " .
												"AND device_id=" . $snmp_query_array["device_id"] . " " .
												"AND snmp_query_id=" . $snmp_query_array["snmp_query_id"] . " " .
												"AND snmp_index='" . $snmp_query_array["snmp_index"] . "'");

			if (isset($existsAlready) && $existsAlready > 0) {
				$dataSourceId = db_fetch_cell("SELECT
						data_template_rrd.local_data_id
						FROM graph_templates_item, data_template_rrd
						WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
						AND graph_templates_item.task_item_id = data_template_rrd.id
						LIMIT 1");
				echo __("NOTE: Not Adding Graph - this graph already exists - graph-id: (%d) - data-source-id: (%d)", $existsAlready, $dataSourceId) . "\n";
				continue;
			}

			$empty = array(); /* Suggested Values are not been implemented */
			$returnArray = create_complete_graph_from_template($snmp_query_array["graph_template_id"], $snmp_query_array["device_id"], $snmp_query_array, $empty);

			if ($graphTitle != "") {
				db_execute("UPDATE graph_templates_graph " .
								"SET title='" . $graphTitle ."' " .
								"WHERE local_graph_id=" . $returnArray["local_graph_id"]);
				update_graph_title_cache($returnArray["local_graph_id"]);
			}

			$dataSourceId = db_fetch_cell("SELECT " .
					"data_template_rrd.local_data_id " .
					"FROM graph_templates_item, data_template_rrd " .
					"WHERE graph_templates_item.local_graph_id = " . $returnArray["local_graph_id"] . " " .
					"AND graph_templates_item.task_item_id = data_template_rrd.id " .
					"LIMIT 1");

			foreach($returnArray["local_data_id"] as $item) {
				push_out_device($snmp_query_array["device_id"], $item);
				$dataSourceId .= (strlen($dataSourceId) ? ", " : "") . $item;
			}

			echo __("Graph Added - graph-id: (%d) - data-source-ids: (%d)", $returnArray["local_graph_id"], $dataSourceId) . "\n";
		}
	}else{
		echo __("ERROR: Could not find snmp-field %s (%d) for device-id %d (%s)", $snmp_query_array["snmp_field"], $snmp_query_array["snmp_value"], $snmp_query_array["device_id"], $devices[$snmp_query_array["device_id"]]["hostname"]) . "\n";
		echo __("Try php -q graph_list.php --device-id=%s --list-snmp-fields", $snmp_query_array["device_id"]) . "\n";
		exit(1);
	}
}


function display_help($me) {
	echo __("Add Graphs Script 1.3") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add graphs in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --graph-type=[cg|ds] --graph-template-id=[ID]\n";
	echo "       [--graph-title=title] [graph options] [--force]\n";
	echo "       [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [-d]\n\n";
	echo __("For cg graphs:") . "\n";
	echo "  [--input-fields=\ [data-template-id:]field-name=value ...\"] [--force]\n\n";
	echo "   --input-fields    " . __("If your data template allows for custom input data, you may specify that here.") . "\n";
	echo "                     " . __("The data template id is optional and applies where two input fields have the same name.") . "\n";
	echo "   --force           " . __("If you set this flag, then new cg graphs will be created, even though they may already exist") . "\n\n";
	echo __("For ds graphs:") . "\n";
	echo "   --snmp-query-id=[ID] --snmp-query-type-id=[ID] --snmp-field=[SNMP Field] --snmp-value=[SNMP Value]\n\n";
	echo "  [--graph-title=]   " . __("Defaults to what ever is in the graph template/data-source template.") . "\n\n";
	echo "   --reindex-method  " . __("the reindex method to be used for that data query") . "\n";
	echo "            0|None   " . __("no reindexing") . "\n";
	echo "            1|Uptime " . __("Uptime goes Backwards") . "\n";
	echo "            2|Index  " . __("Index Count Changed") . "\n";
	echo "            3|Fields " . __("Verify all Fields") . "\n";
	echo "            4|Value  " . __("Re-Index Value Changed") . "\n";
	echo __("'cg' graphs are for things like CPU temp/fan speed, while ") . "\n";
	echo __("'ds' graphs are for data-source based graphs (interface stats etc.)") . "\n";
	echo __("At least one device related parameter is required. The given data query will be added to all matching devices.") . "\n";
	echo __("Optional:") . "\n";
	echo "   --device-id       " . __("the numerical ID of the device") . "\n";
	echo "   --site-id         " . __("the numerical ID of the site") . "\n";
	echo "   --poller-id       " . __("the numerical ID of the poller") . "\n";
	echo "   --description     " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "   --ip              " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "   --template        " . __("denotes the device template to be used") . "\n";
	echo "                     " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                     " . __("For a device template=0 (NONE), Cacti default settings are used.") . "\n";
	echo "                     " . __("Optionally overwrite by any of the following:") . "\n";
	echo "   --notes           " . __("General information about this device. Must be enclosed using double quotes.") . "\n";
	echo "   --disable         " . __("to add this device but to disable checks and 0 to enable it") . " [0|1]\n";
	echo "   --avail           " . __("device availability check") . " [ping][none, snmp, pingsnmp]\n";
	echo "     --ping-method   " . __("if ping selected") . " [icmp|tcp|udp]\n";
	echo "     --ping-port     " . __("port used for tcp|udp pings") . " [1-65534]\n";
	echo "     --ping-retries  " . __("the number of time to attempt to communicate with a device") . "\n";
	echo "     --ping-timeout  " . __("ping timeout") . "\n";
	echo "   --version         " . __("snmp version") . " [1|2|3]\n";
	echo "   --community       " . __("snmp community string for snmpv1 and snmpv2. Leave blank for no community") . "\n";
	echo "   --port            " . __("snmp port") . "\n";
	echo "   --timeout         " . __("snmp timeout") . "\n";
	echo "   --username        " . __("snmp username for snmpv3") . "\n";
	echo "   --password        " . __("snmp password for snmpv3") . "\n";
	echo "   --authproto       " . __("snmp authentication protocol for snmpv3") . " [".SNMP_AUTH_PROTOCOL_MD5."|".SNMP_AUTH_PROTOCOL_SHA."]\n";
	echo "   --privpass        " . __("snmp privacy passphrase for snmpv3") . "\n";
	echo "   --privproto       " . __("snmp privacy protocol for snmpv3") . " [".SNMP_PRIV_PROTOCOL_DES."|".SNMP_PRIV_PROTOCOL_AES128."]\n";
	echo "   --context         " . __("snmp context for snmpv3") . "\n";
	echo "   --max-oids        " . __("the number of OID's that can be obtained in a single SNMP Get request") . " [1-60]\n";
}
