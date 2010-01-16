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

/* We are not talking to the browser */
$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");
include_once(CACTI_BASE_PATH."/lib/api_graph.php");
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);
$debug		= FALSE;	# no debug mode
$error		= '';

if (sizeof($parms)) {
	$force			= FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;
			#case "--delim":			$delimiter						= trim($value);	break;
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

			case "--force":			$force							= TRUE;			break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	# at least one matching criteria has to be defined
	if (!sizeof($device)) {
		print __("ERROR: No device matching criteria found\n");
		exit(1);
	}

	# now verify the parameters given
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

	foreach ($devices as $item) {
		# get the data sources and graphs to act on
		$data_sources_to_act_on = array();
		$graphs_to_act_on       = array();

		$data_sources = db_fetch_assoc("select
			data_local.id as local_data_id
			from data_local
			where data_local.device_id =" . $item["id"]);

		if (sizeof($data_sources) > 0) {
			foreach ($data_sources as $data_source) {
				$data_sources_to_act_on[] = $data_source["local_data_id"];
			}
		}

		if ($force) {
			$graphs = db_fetch_assoc("select
				graph_local.id as local_graph_id
				from graph_local
				where graph_local.device_id =" . $item["id"]);

			if (sizeof($graphs) > 0) {
				foreach ($graphs as $graph) {
					$graphs_to_act_on[] = $graph["local_graph_id"];
				}
			}
			/* delete graphs/data sources tied to this device */
			if (!$debug) {
				api_data_source_remove_multi($data_sources_to_act_on);
				api_graph_remove_multi($graphs_to_act_on);
			}
			echo __("Removing device and all resources for device id ") . $item["id"];
		} else {
			/* leave graphs and data_sources in place, but disable the data sources */
			if (!$debug) {
				api_data_source_disable_multi($data_sources_to_act_on);
			}
			echo __("Removing device but keeping resources for device id ") . $item["id"];
		}

		api_device_remove($item["id"]);

		if (is_error_message()) {
			echo __(". ERROR: Failed to remove this device") . "\n";
		} else {
			echo __(". Success - removed device id: ") . ($item["id"]) . "\n";
		}
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Remove Device Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to remove a device from Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [-d]\n\n";
	echo __("At least one device related parameter is required. All matching devices will be deleted") . "\n";
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
	echo __("Optional:") . "\n";
	echo "   --force       " . __("delete all graphs, graph permissions, device permissions and data sources") . "\n\n";
}
