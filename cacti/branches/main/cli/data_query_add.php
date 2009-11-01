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

	# verify required parameters
	if (!isset($dq["snmp_query_id"])) {
		echo __("ERROR: You must supply a valid data-query-id for all devices!") . "\n";
		exit(1);
	}

	if (!isset($dq["reindex_method"])) {
		echo __("ERROR: You must supply a valid reindex-method for all devices!") . "\n";
		exit(1);
	}

	# at least one matching criteria for host(s) has to be defined
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
		print __("ERROR: No matching Devices found\n");
		print __("Try php -q device_list.php") . "\n";
		exit(1);
	}

	/* verify valid data query and get a name for it */
	$data_query_name = db_fetch_cell("SELECT name FROM snmp_query WHERE id = " . $dq["snmp_query_id"]);
	if (!isset($data_query_name)) {
		echo __("ERROR: Unknown Data Query Id (%s)", $dq["snmp_query_id"]) . "\n";
		exit(1);
	}

	/* Now, add the data query and run it once to get the cache filled */
	foreach ($devices as $device) {
		$current_reindex_method = db_fetch_cell("SELECT reindex_method FROM host_snmp_query WHERE host_id=" . $device["id"] .
										" AND snmp_query_id=" . $dq["snmp_query_id"]);
		if (isset($current_reindex_method)) {
			echo __("ERROR: Data Query is already associated for device: (%s: %s) data query (%s: %s) using reindex method of (%s: %s)", $device["id"], $device["hostname"], $dq["snmp_query_id"], $data_query_name, $current_reindex_method, $reindex_types{$current_reindex_method}) . "\n";
			continue;
		}else{
			$sql = "REPLACE INTO host_snmp_query " .
					"(host_id,snmp_query_id,reindex_method) " .
					"VALUES (".
						$device["id"] . "," .
						$dq["snmp_query_id"] . "," .
						$dq["reindex_method"] .
					")";
			if ($debug) {
				print $sql . "\n";
			} else {
				# update of sort_field and title are done later in update_data_query_sort_cache via run_data_query
				$ok = db_execute($sql);
				if (!$quietMode) {
					if ($ok) {
						/* recache snmp data */
						run_data_query($device["id"], $dq["snmp_query_id"]);
						if (is_error_message()) {
							echo __("ERROR: Failed to add this data query for device (%s: %s) data query (%s: %s) reindex method (%s: %s)", $device["id"], $device["hostname"], $dq["snmp_query_id"], $data_query_name, $dq["reindex_method"], $reindex_types[$dq["reindex_method"]]) . "\n";
						} else {
							echo __("Success - Device (%s: %s) data query (%s: %s) reindex method (%s: %s)", $device["id"], $device["hostname"], $dq["snmp_query_id"], $data_query_name, $dq["reindex_method"], $reindex_types{$dq["reindex_method"]}) . "\n";
						}
					} else {
						echo __("ERROR: Failed to add this data query for device (%s: %s) data query (%s: %s) reindex method (%s: %s)", $device["id"], $device["hostname"], $dq["snmp_query_id"], $data_query_name, $dq["reindex_method"], $reindex_types[$dq["reindex_method"]]) . "\n";
					}
				}
			}
		}
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Data Query Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add a data query to an existing device in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --data-query-id=[dq_id] --reindex-method=[method] [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [--quiet] [-d]\n\n";
	echo __("Required:") . "\n";
	echo "   --data-query-id  " . __("the numerical ID of the data_query to be added") . "\n";
	echo "   --reindex-method " . __("the reindex method to be used for that data query") . "\n";
	echo "          0|none  " . __("no reindexing") . "\n";
	echo "          1|uptime" . __("Uptime goes Backwards") . "\n";
	echo "          2|index " . __("Index Count Changed") . "\n";
	echo "          3|fields" . __("Verify all Fields") . "\n";
	echo "          4|value " . __("Re-Index Value Changed") . "\n";
	echo __("At least one device related parameter is required. The given data query will be added to all matching devices.") . "\n";
	echo __("Optional:") . "\n";
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
	echo "   -d                          " . __("Debug Mode, no updates made, but printing the SQL for updates") . "\n";
	echo "   --quiet                     " . __("batch mode value return") . "\n\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . " --device-id=1 --data-query-id=1 --reindex-method=index\n";
	echo "   " . __("  adds data query id 1 to the device id 1 using reindex method of 'index'") . "\n";
	echo "   php -q " . $me . "  --data-query-id=5 --reindex-method=uptime --template=3\n";
	echo "   " . __("  adds data query id 5 using reindex method of 'uptime' to all devices related to device template id 3") . "\n";
}
