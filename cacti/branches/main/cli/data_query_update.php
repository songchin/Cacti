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
require(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");
require_once(CACTI_BASE_PATH . "/include/device/device_constants.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);
$debug		= FALSE;	# no debug mode
$delimiter 	= ':';		# default delimiter, if not given by user
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
			case "--delim":			$delimiter						= trim($value);	break;
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

	# split old/new values for data queries only, makes no sense for device, as this is data_query_update.php
	# seems to be a bit overdesigned this way, because currently only reindex method is eligible
	foreach($dq as $key => $value) {
		# now split each parameter using the default or the given delimiter
		@list($old{$key}, $new{$key}) = @explode($delimiter, $dq{$key});
		# unset, if parm left empty but allow for "empty" input
		if (!strlen($old{$key})) {
			unset($old{$key});
		} elseif (($old{$key} === "''") || ($old{$key} === '""')) {
			$old{$key} = '';
		}
		if (!strlen($new{$key})) {
			unset($new{$key});
		} elseif (($new{$key} === "''") || ($new{$key} === '""')) {
			$new{$key} = '';
		}
	}

	# we do not want to change the dq["snmp_query_id"] because that's the autoincremented table index
	if (isset($new["snmp_query_id"])) {
		echo(__("ERROR: Update of data query id not permitted\n"));
		exit(1);
	}

	# verify new parameters, this currently only matches reindex method
	if (!sizeof($new)) {
		print __("ERROR: No Update Parameters found\n");
		exit(1);
	}

	# at least, the old data query id has to be given
	if (!isset($old["snmp_query_id"])) {
		print __("ERROR: No matching Data Query found\n");
		print __("Try php -q data_query_list.php") . "\n";
		exit(1);
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

	if (sizeof($old)) {
		# verify the parameters given
		$verify = verifyDataQuery($old, true);
		if (isset($verify["err_msg"])) {
			print $verify["err_msg"] . "\n\n";
			display_help($me);
			exit(1);
		}
	}

	if (sizeof($new)) {
		# verify the parameters given
		$verify = verifyDataQuery($new, true);
		if (isset($verify["err_msg"])) {
			print $verify["err_msg"] . "\n\n";
			display_help($me);
			exit(1);
		}
	}

	/* get devices matching criteria */
	$devices = getDevices($device);

	if (!sizeof($devices)) {
		echo __("ERROR: No matching Devices found") . "\n";
		echo __("Try php -q device_list.php") . "\n";
		exit(1);
	}

	# restrict further processing to those devices only, that are associated with the given data query
	$sql = "SELECT device.id, " .
			"device.hostname, " .
			"device_snmp_query.reindex_method " .
			"FROM device_snmp_query " .
			"LEFT JOIN device ON (device_snmp_query.device_id = device.id) ".
			"WHERE " . str_replace("id", "device_id", array_to_sql_or($devices, "id")) . " " .
			"AND snmp_query_id=" . $old["snmp_query_id"];
	if ($debug) {
		print $sql . "\n";
	}
	$verified_devices = db_fetch_assoc($sql);

	/* build raw SQL update command */
	$sql_upd1 = "UPDATE device_snmp_query SET ";
	$sql_upd2 = "";
	$sql_upd3 = " WHERE " . str_replace("id", "device_id", array_to_sql_or($verified_devices, "id")) . " AND snmp_query_id=" . $old["snmp_query_id"];

	# verify each parameter given and append it to the SQL update command
	$first = true;
	reset($new);
	while (list($parm, $value) = each($new)) {
		$sql_upd2 .= ($first ? " " : ", ");
		$sql_upd2 .= $parm . "='" . $value . "'"; # TODO: relies on data type conversion, else tics would matter
		$first = false;
	}

	# update everything
	if (sizeof($verified_devices)) {
		if ($debug) {
			print $sql_upd1 . $sql_upd2 . $sql_upd3 . "\n";
		} else {
			$ok = db_execute($sql_upd1 . $sql_upd2 . $sql_upd3);
			# add the snmp query name for printout
			$old["snmp_query_name"] = db_fetch_cell("SELECT name FROM snmp_query WHERE id=" . $old["snmp_query_id"]);

			if ($ok) {

				if (!$quietMode) {
					echo __("Data Query (%s: %s) reindex method (%s: %s) updated for %s Device(s)", $old["snmp_query_id"], $old["snmp_query_name"], $new["reindex_method"], $reindex_types{$new["reindex_method"]}, sizeof($verified_devices)) . "\n";
				}

				foreach ($verified_devices as $verified_device) {
					/* recache snmp data */
					run_data_query($verified_device["id"], $old["snmp_query_id"]);
					if (!$quietMode) {
						if (is_error_message()) {
							echo __("ERROR: Rerun of this data query failed for device (%s: %s) data query (%s: %s) reindex method (%s: %s)", $verified_device["id"], $verified_device["hostname"], $old["snmp_query_id"], $old["snmp_query_name"], $new["reindex_method"], $reindex_types[$new["reindex_method"]]) . "\n";
						} else {
							echo __("Data Query (%s: %s) reindex method (%s: %s) rerun for Device (%s: %s)", $old["snmp_query_id"], $old["snmp_query_name"], $new["reindex_method"], $reindex_types{$new["reindex_method"]}, $verified_device["id"], $verified_device["hostname"]) . "\n";
						}
					}
				}
			} else {
				echo __("ERROR: Failed to update Data Query (%s: %s) reindex method (%s: %s) for %s Device(s)", $old["snmp_query_id"], $old["snmp_query_name"], $new["reindex_method"], $reindex_types{$new["reindex_method"]}, sizeof($verified_devices)) . "\n";
			}
		}
	}
}

function display_help($me) {
	echo __("Update Data Query Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to update data queries in Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--data-query-id=] [--reindex-method=] [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [--quiet] [-d] [--delim]\n\n";
	echo __("Required:") . "\n";
	echo "   " . __("Values are given in format [<old>][:<new>]") . "\n";
	#echo "   " . __("If <old> is given, all devices matching the selection will be acted upon. Multiple <old> parameters are allowed") . "\n";
	#echo "   " . __("All new values must be seperated by a delimiter (defaults to ':') from <old>. Multiple <new> parameters are allowed") . "\n";
	echo "   --data-query-id  " . __("the numerical ID of the data_query to be listed") . "\n";
	echo "   --reindex-method " . __("the reindex method to be used for that data query") . "\n";
	echo "          0|none  " . __("no reindexing") . "\n";
	echo "          1|uptime" . __("Uptime goes Backwards") . "\n";
	echo "          2|index " . __("Index Count Changed") . "\n";
	echo "          3|fields" . __("Verify all Fields") . "\n";
	echo "          4|value " . __("Re-Index Value Changed") . "\n";
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
	echo "   --delim           :         " . __("sets the delimiter") . "\n";
	echo "   -d                          " . __("Debug Mode, no updates made, but printing the SQL for updates") . "\n";
	echo "   --quiet                     " . __("batch mode value return") . "\n\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . "  --data-query-id=3 --reindex-method=:index --device-id=5\n";
	echo "   " . __("  changes reindex method of data query id 3 on device id 5 to 'index'") . "\n";
	echo "   php -q " . $me . "  --data-query-id=3 --reindex-method=uptime:index --template=8\n";
	echo "   " . __("  same as above, but updating for old reindex method of 'uptime' only, working on all devices associated with template id of 8") . "\n";
}
