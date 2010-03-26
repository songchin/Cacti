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

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);
$debug		= FALSE;	# no debug mode
$delimiter 	= ',';		# default delimiter, if not given by user
$quietMode 	= FALSE;	# be verbose by default
$device 	= array();
$perm	 	= array();

if (sizeof($parms) == 0) {
	display_help($me);

	exit(1);
}else{

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;
			case "--delim":			$delimiter						= trim($value);	break;

			# required parms for permissions
			case "--user-id":		$perm["user_id"]				= trim($value);	break;
			case "--item-type":		$perm["item_type"]				= trim($value);	break;
			case "--item-id":		$perm["item_id"]				= trim($value);	break;

			# to select the devices to act on, at least one parameter must be given to specify device list
			case "--device-id":		$device["id"] 					= trim($value);	break;
			case "--site-id":		$device["site_id"] 				= trim($value);	break;
			case "--poller-id":		$device["poller_id"]			= trim($value);	break;
			case "--description":	$device["description"] 			= trim($value);	break;
			case "--ip":			$device["hostname"] 			= trim($value);	break;
			case "--device_template_id":
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
			case "--device0threads":$device["device_threads"]		= trim($value);	break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	# either item_id or device parms must be given
	if (isset($perm["item_id"])) {		# a specific item_id was given
		# verify later
	} elseif (sizeof($device)) { 		# assume, that at least one device parameter must be specified
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
		} else {
			$perm["devices"] = $devices;
		}
	} else {							# neither ietm_id nor device given
		echo __("ERROR: --item-id missing. Please specify.") . "\n\n";
		display_help($me);
		exit(1);
	}

	# verify the parameters given, return user_id as array of verified userids
	$verify = verifyPermissions($perm, $delimiter, true);
	if (isset($verify["err_msg"])) {
		print $verify["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}

	if (sizeof($perm["userids"])) {
		foreach ($perm["userids"] as $id) {
			$sql = "FROM user_auth_perms " .
					"WHERE `type`=" . $perm["item_type_id"] . " " .
					"AND user_id=" . $id . " " .
					"AND item_id=" . $perm["item_id"];
			if ($debug) {
				print "DELETE " . $sql . "\n";
			} else {
				# we have to fetch the data first, delete will NOT return error code
				$ok = db_fetch_cell("SELECT COUNT(*) " . $sql);
				if ($ok) {
					$ok = db_execute("DELETE " . $sql);
					echo __("Success - Permission deleted for user (%s) item type (%s: %s) item id (%s)", $id, $perm["item_type_id"], $perm["item_type"], $perm["item_id"]) . "\n";
				} else {
					echo __("ERROR: Failed to delete permission for user (%s) item type (%s: %s) item id (%s)", $id, $perm["item_type_id"], $perm["item_type"], $perm["item_id"]) . "\n";
				}
			}
		}
	}
}

function display_help($me) {
	echo "Delete Permissions Script 1.0" . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to delete permissions in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  [ --user-id=[ID] ]\n";
	echo "   --item-type=[graph|tree|device|graph_template]\n";
	echo "   --item-id=[ID] [--quiet]\n";
	echo __("Where %s is the id of the object of type %s", "item-id", "item-type") . "\n";
	echo "   --quiet          " . __("batch mode value return") . "\n\n";
}
