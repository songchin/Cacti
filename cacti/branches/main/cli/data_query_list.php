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

/* We are not talking to the browser */
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
$dq			= array();
$error		= '';

if (sizeof($parms)) {
	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;
			case "--device-id":		$device["id"] 					= trim($value);	break;
			case "--site-id":		$device["site_id"] 				= trim($value);	break;
			case "--poller-id":		$device["poller_id"]			= trim($value);	break;
			case "--description":	$device["description"] 			= trim($value);	break;
			case "--ip":			$device["hostname"] 			= trim($value);	break;
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
			case "--data-query-id":	$dq["snmp_query_id"] 			= trim($value);	break;
			case "--reindex-method":$dq["reindex_method"] 			= trim($value);	break;
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}


	if (sizeof($device)) {
		# verify the parameters given
		$verify = verifyDevice($device, true);
		if (isset($verify["err_msg"])) {
			print $verify["err_msg"] . "\n\n";
			display_help($me);
			exit(1);
		}
	}

	if (sizeof($dq)) {
		# verify the parameters given
		$verify = verifyDataQuery($dq, true);
		if (isset($verify["err_msg"])) {
			print $verify["err_msg"] . "\n\n";
			display_help($me);
			exit(1);
		}
	}

	/* get devices matching criteria */
	$devices = getDevices($device);

	if (!sizeof($devices)) {
		$data_queries = getSNMPQueries();
		displaySNMPQueries($data_queries, $quietMode);
	} else {
		$columns = array();
		if (isset($dq["snmp_query_id"])) {
			$data_queries = getSNMPQueriesByDevices($devices, $dq["snmp_query_id"], $columns);
		} else {
			$data_queries = getSNMPQueriesByDevices($devices, '', $columns);
		}
		$title = __("List of Data Queries for given Devices");
		# these are the table columns for display
		displayGenericArray($data_queries, $columns, $title, $quietMode);
	}
}else{
	$data_queries = getSNMPQueries();
	displaySNMPQueries($data_queries, $quietMode);
}

function display_help($me) {
	echo __("List Data Query Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list data queries in Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--data-query-id=] [--reindex-method=] [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [--quiet]\n\n";
	echo __("Optional:") . "\n";
	echo "   --data-query-id  " . __("the numerical ID of the data_query to be listed") . "\n";
	echo "   --reindex-method " . __("the reindex method to be used for that data query") . "\n";
	echo "          0|none  " . __("no reindexing") . "\n";
	echo "          1|uptime" . __("Uptime goes Backwards") . "\n";
	echo "          2|index " . __("Index Count Changed") . "\n";
	echo "          3|fields" . __("Verify all Fields") . "\n";
	echo "          4|value " . __("Re-Index Value Changed") . "\n";
	echo "   --device-id                 " . __("the numerical ID of the device") . "\n";
	echo "   --site-id                   " . __("the numerical ID of the site") . "\n";
	echo "   --poller-id                 " . __("the numerical ID of the poller") . "\n";
	echo "   --description               " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "   --ip                        " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "   --template                  " . __("denotes the device template to be used") . "\n";
	echo "                               " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                               " . __("For a device template=0 (NONE), Cacti default settings are used.") . "\n";
	echo "                               " . __("Optionally overwrite by any of the following:") . "\n";
	echo "   --notes                     " . __("General information about this device. Must be enclosed using double quotes.") . "\n";
	echo "   --disable                   " . __("to add this device but to disable checks and 0 to enable it") . " [0|1]\n";
	echo "   --avail                     " . __("device availability check") . " [ping][none, snmp, pingsnmp]\n";
	echo "     --ping-method             " . __("if ping selected") . " [icmp|tcp|udp]\n";
	echo "     --ping-port               " . __("port used for tcp|udp pings") . " [1-65534]\n";
	echo "     --ping-retries            " . __("the number of time to attempt to communicate with a device") . "\n";
	echo "     --ping-timeout            " . __("ping timeout") . "\n";
	echo "   --version                   " . __("snmp version") . " [1|2|3]\n";
	echo "   --community                 " . __("snmp community string for snmpv1 and snmpv2. Leave blank for no community") . "\n";
	echo "   --port                      " . __("snmp port") . "\n";
	echo "   --timeout                   " . __("snmp timeout") . "\n";
	echo "   --username                  " . __("snmp username for snmpv3") . "\n";
	echo "   --password                  " . __("snmp password for snmpv3") . "\n";
	echo "   --authproto                 " . __("snmp authentication protocol for snmpv3") . " [".SNMP_AUTH_PROTOCOL_MD5."|".SNMP_AUTH_PROTOCOL_SHA."]\n";
	echo "   --privpass                  " . __("snmp privacy passphrase for snmpv3") . "\n";
	echo "   --privproto                 " . __("snmp privacy protocol for snmpv3") . " [".SNMP_PRIV_PROTOCOL_DES."|".SNMP_PRIV_PROTOCOL_AES128."]\n";
	echo "   --context                   " . __("snmp context for snmpv3") . "\n";
	echo "   --max-oids                  " . __("the number of OID's that can be obtained in a single SNMP Get request") . " [1-60]\n";
	#echo "   -d                          " . __("Debug Mode, no updates made, but printing the SQL for updates") . "\n";
	echo "   --quiet          " . __("batch mode value return") . "\n\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . " \n";
	echo "   " . __("  lists all available data queries") . "\n";
	echo "   php -q " . $me . "  --device-id=5\n";
	echo "   " . __("  lists all data queries associated with device id 5") . "\n";
	echo "   php -q " . $me . "  --template=8\n";
	echo "   " . __("  lists all data queries associated with the devices associated with device template id 8") . "\n";
	echo "   php -q " . $me . "  data-query-id=1 --template=8\n";
	echo "   " . __("  same as above, but only listing data query id 1") . "\n";
}
