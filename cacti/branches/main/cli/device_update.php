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
require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");
include_once(CACTI_BASE_PATH."/lib/utility.php");
include_once(CACTI_BASE_PATH."/lib/api_data_source.php");
include_once(CACTI_BASE_PATH."/lib/api_graph.php");
include_once(CACTI_BASE_PATH."/lib/snmp.php");
include_once(CACTI_BASE_PATH."/lib/data_query.php");
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms 		= $_SERVER["argv"];
$me 		= array_shift($parms);
$debug		= FALSE;	# no debug mode
$delimiter 	= ':';		# default delimiter, if not given by user
$quietMode 	= FALSE;	# be verbose by default
$device 	= array();
$error		= '';

if (sizeof($parms)) {
	# read all parameters
	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);
		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;
			case "--delim":			$delimiter						= trim($value);	break;

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

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}
	#print "parms: "; print_r($device);

	foreach($device as $key => $value) {
		# now split each parameter using the default or the given delimiter
		@list($old{$key}, $new{$key}) = @explode($delimiter, $device{$key});
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

	# we do not want to change the device["id"] because that's the autoincremented table index
	if (isset($new["id"])) {
		echo(__("ERROR: Update of device id not permitted\n"));
		exit(1);
	}
	if (isset($new["device_template_id"])) {
		echo(__("ERROR: Update of device template id not permitted\n"));
		exit(1);
	}
	# at least one matching criteria has to be defined
	if (!sizeof($old)) {
		print __("ERROR: No device matching criteria found\n");
		exit(1);
	}
	if (!sizeof($new)) {
		print __("ERROR: No Update Parameters found\n");
		exit(1);
	}
	#print "old1: "; print_r($old);
	#print "new1: "; print_r($new);

	# now verify the parameters given
	$verify = verifyDevice($old, true);
	if (isset($verify["err_msg"])) {
		print $verify["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}
	$verify = verifyDevice($new, true);
	if (isset($verify["err_msg"])) {
		print $verify["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}
	#print "old2: "; print_r($old);
	#print "new2: "; print_r($new);

	# get all devices matching criteria
	$devices = getDevices($old);
	if (!sizeof($devices)) {
		echo __("ERROR: No matching Devices found") . "\n";
		echo __("Try php -q device_list.php") . "\n";
		exit(1);
	}

	#print "devices: "; print_r($devices);
	/* build raw SQL update command */
	$sql_upd1 = "UPDATE device SET ";
	$sql_upd2 = "";
	$sql_upd3 = " WHERE " . array_to_sql_or($devices, "id");

	/*
	 * if a new template is given,
	 * how do we propagate the device template settings to the devices?
	 * Is there a function for updating device parms AND graphs + data queries?
	 */
	/*	if (isset($template_id) && $template_id == 0) { # allow for a template of "None"
		$device_template["name"] = "None";
		} else {
		$device_template = db_fetch_row("SELECT name FROM device_template WHERE id = " . $template_id);
		if (!isset($device_template["name"])) {
		printf(__("ERROR: Unknown template id (%d)\n"), $template_id);
		exit(1);
		}
		}
		*/
	/*
	 * now, either fetch device_template parameters for given template_id
	 * or use Cacti system defaults
	 */
	/*	if ($template_id != 0) { # fetch values from a valid device_template
		$device_template = db_fetch_row("SELECT
		device_template.id,
		device_template.name,
		device_template.snmp_community,
		device_template.snmp_version,
		device_template.snmp_username,
		device_template.snmp_password,
		device_template.snmp_port,
		device_template.snmp_timeout,
		device_template.availability_method,
		device_template.ping_method,
		device_template.ping_port,
		device_template.ping_timeout,
		device_template.ping_retries,
		device_template.snmp_auth_protocol,
		device_template.snmp_priv_passphrase,
		device_template.snmp_priv_protocol,
		device_template.snmp_context,
		device_template.max_oids,
		device_template.device_threads
		FROM device_template
		WHERE id=" . $template_id);
		} else { # no device template given, so fetch system defaults
		$device_template["snmp_community"]		= read_config_option("snmp_community");
		$device_template["snmp_version"]		= read_config_option("snmp_ver");
		$device_template["snmp_username"]		= read_config_option("snmp_username");
		$device_template["snmp_password"]		= read_config_option("snmp_password");
		$device_template["snmp_port"]			= read_config_option("snmp_port");
		$device_template["snmp_timeout"]		= read_config_option("snmp_timeout");
		$device_template["availability_method"]	= read_config_option("availability_method");
		$device_template["ping_method"]			= read_config_option("ping_method");
		$device_template["ping_port"]			= read_config_option("ping_port");
		$device_template["ping_timeout"]		= read_config_option("ping_timeout");
		$device_template["ping_retries"]		= read_config_option("ping_retries");
		$device_template["snmp_auth_protocol"]	= read_config_option("snmp_auth_protocol");
		$device_template["snmp_priv_passphrase"]= read_config_option("snmp_priv_passphrase");
		$device_template["snmp_priv_protocol"]	= read_config_option("snmp_priv_protocol");
		$device_template["snmp_context"]		= read_config_option("snmp_context");
		$device_template["max_oids"]			= read_config_option("max_get_size");
		$device_template["device_threads"]		= read_config_option("device_threads");
		}
		*/
	/*
	 * if any value was given as a parameter,
	 * replace device_template or default setting by this one
	 */
	/*	if (isset($snmp_community)) 		{$device_template["snmp_community"]			= $snmp_community;}
	 if (isset($snmp_ver)) 				{$device_template["snmp_version"]			= $snmp_ver;}
	 if (isset($snmp_username))			{$device_template["snmp_username"]			= $snmp_username;}
	 if (isset($snmp_password)) 			{$device_template["snmp_password"]			= $snmp_password;}
	 if (isset($snmp_port)) 				{$device_template["snmp_port"]				= $snmp_port;}
	 if (isset($snmp_timeout)) 			{$device_template["snmp_timeout"]			= $snmp_timeout;}
	 if (isset($availability_method))	{$device_template["availability_method"]	= $availability_method;}
	 if (isset($ping_method)) 			{$device_template["ping_method"]			= $ping_method;}
	 if (isset($ping_port)) 				{$device_template["ping_port"]				= $ping_port;}
	 if (isset($ping_timeout)) 			{$device_template["ping_timeout"]			= $ping_timeout;}
	 if (isset($ping_retries)) 			{$device_template["ping_retries"]			= $ping_retries;}
	 if (isset($snmp_auth_protocol)) 	{$device_template["snmp_auth_protocol"]		= $snmp_auth_protocol;}
	 if (isset($snmp_priv_passphrase)) 	{$device_template["snmp_priv_passphrase"]	= $snmp_priv_passphrase;}
	 if (isset($snmp_priv_protocol)) 	{$device_template["snmp_priv_protocol"]		= $snmp_priv_protocol;}
	 if (isset($snmp_context)) 			{$device_template["snmp_context"]			= $snmp_context;}
	 if (isset($max_oids))	 			{$device_template["max_oids"]				= $max_oids;}
	 if (isset($device_threads))		{$device_template["device_threads"]			= $device_threads;}

	 $device_template["notes"]		= (isset($notes)) ? $notes : "";
	 $device_template["disabled"]	= (isset($disabled)) ? disabled : "";
	 */
	/*
	 * verify each parameter given and append it to the SQL update command
	 */
	$first = true;
	reset($new);
	while (list($parm, $value) = each($new)) {
		$sql_upd2 .= ($first ? " " : ", ");
		$sql_upd2 .= $parm . "='" . $value . "'";
		$first = false;
	}

	/*
	 * update everything
	 */
	if (sizeof($devices)) {
		if ($debug) {
			print $sql_upd1 . $sql_upd2 . $sql_upd3 . "\n";
		} else {
			$ok = db_execute($sql_upd1 . $sql_upd2 . $sql_upd3);
			if (!$quietMode) {
				if ($ok) {
					print(__("Devices successfully updated: %s", sizeof($devices)) . "\n");
				} else {
					print(__("ERROR: Device update failed due to SQL error") . "\n");
				}
			}
		}
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Change Device Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to change existing devices in Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--device-id=] [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "       [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [--quiet] [-d] [--delim]\n\n";
	echo __("All Parameters are optional. Any parameters given must match. A non-empty selection is required.") . "\n";
	echo "   " . __("Values are given in format [<old>][:<new>]") . "\n";
	echo "   " . __("If <old> is given, all devices matching the selection will be acted upon. Multiple <old> parameters are allowed") . "\n";
	echo "   " . __("All new values must be seperated by a delimiter (defaults to ':') from <old>. Multiple <new> parameters are allowed") . "\n";
	echo __("Optional:") . "\n";
	echo "   --device-id                 " . __("the numerical ID of the device") . "\n";
	echo "   --site-id        0          " . __("the numerical ID of the site") . "\n";
	echo "   --poller-id      0          " . __("the numerical ID of the poller") . "\n";
	echo "   --description               " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "   --ip                        " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "   --template                  " . __("denotes the device template to be used") . "\n";
	echo "                               " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                               " . __("For a device template=0 (NONE), Cacti default settings are used.") . "\n";
	echo "                               " . __("Optionally overwrite by any of the following:") . "\n";
	echo "   --notes                     " . __("General information about this device. Must be enclosed using double quotes.") . "\n";
	echo "   --disable         1         " . __("to add this device but to disable checks and 0 to enable it") . " [0|1]\n";
	echo "   --avail           pingsnmp  " . __("device availability check") . " [ping][none, snmp, pingsnmp]\n";
	echo "     --ping-method   tcp       " . __("if ping selected") . " [icmp|tcp|udp]\n";
	echo "     --ping-port     23        " . __("port used for tcp|udp pings") . " [1-65534]\n";
	echo "     --ping-retries  2         " . __("the number of time to attempt to communicate with a device") . "\n";
	echo "     --ping-timeout  500       " . __("ping timeout") . "\n";
	echo "   --version         1         " . __("snmp version") . " [1|2|3]\n";
	echo "   --community       ''        " . __("snmp community string for snmpv1 and snmpv2. Leave blank for no community") . "\n";
	echo "   --port            161       " . __("snmp port") . "\n";
	echo "   --timeout         500       " . __("snmp timeout") . "\n";
	echo "   --username        ''        " . __("snmp username for snmpv3") . "\n";
	echo "   --password        ''        " . __("snmp password for snmpv3") . "\n";
	echo "   --authproto       ''        " . __("snmp authentication protocol for snmpv3") . " [".SNMP_AUTH_PROTOCOL_MD5."|".SNMP_AUTH_PROTOCOL_SHA."]\n";
	echo "   --privpass        ''        " . __("snmp privacy passphrase for snmpv3") . "\n";
	echo "   --privproto       ''        " . __("snmp privacy protocol for snmpv3") . " [".SNMP_PRIV_PROTOCOL_DES."|".SNMP_PRIV_PROTOCOL_AES128."]\n";
	echo "   --context         ''        " . __("snmp context for snmpv3") . "\n";
	echo "   --max-oids        10        " . __("the number of OID's that can be obtained in a single SNMP Get request") . " [1-60]\n";
	echo "   --delim           :         " . __("sets the delimiter") . "\n";
	echo "   -d                          " . __("Debug Mode, no updates made, but printing the SQL for updates") . "\n";
	echo "   --quiet                     " . __("batch mode value return") . "\n\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . " --device-id=0 --description=:foobar\n";
	echo "   " . __("  changes the description of device 0 to foobar") . "\n";
	echo "   php -q " . $me . " --community=public#secret --delim=#\n";
	echo "   " . __("   changes the SNMP community string for all (matching) devices from 'public' to 'secret' using a custom delimiter of '#'") . "\n";
	echo "   php -q " . $me . " --template=7 --version=:1 --timeout=:1000\n";
	echo "   " . __("   changes both SNMP version and timeout for all devices related to the device template id of 7") . "\n";
}
