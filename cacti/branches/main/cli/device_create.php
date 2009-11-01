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
include_once(CACTI_BASE_PATH."/lib/utility.php");
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
$device 		= array();
$error		= '';

if (sizeof($parms)) {
	# read all parameters
	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);
		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;
			#case "--delim":			$delimiter						= trim($value);	break;
			#case "--device-id":		$device["id"] 					= trim($value);	break;
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
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}
	#print "parms: "; print_r($device);

	/*
	 * verify existance of required parameters
	 */
	if (!isset($device["description"])) {
		echo __("ERROR: You must supply a description for all devices!") . "\n";
		exit(1);
	} # no need for description to be unique

	if (!isset($device["hostname"])) {
		echo __("ERROR: You must supply a valid [devicename|IP address] for all devices!") . "\n";
		exit(1);
	} # no need for hostname to be unique

	if (!isset($device["host_template_id"])) {
		echo __("ERROR: You must supply a valid device template id for all devices!") . "\n";
		exit(1);
	} elseif ($device["host_template_id"] == 0) { /* allow for a template of "None" */
		$device_template["name"] = "None";
	} else {
		$device_template = db_fetch_row("SELECT name FROM host_template WHERE id = " . $device["host_template_id"]);
		if (!isset($device_template["name"])) {
			printf(__("ERROR: Unknown template id (%d)\n"), $device["host_template_id"]);
			exit(1);
		}
	}

	# now verify the parameters given
	$verify = verifyDevice($device, true);
	if (isset($verify["err_msg"])) {
		print $verify["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}

	/*
	 * now, either fetch device_template parameters for given template_id
	 * or use Cacti system defaults
	 */
	/* TODO: shall poller_id and/or site_id be part of a host_template? */
	if (!isset($device["poller_id"])) 	{$device["poller_id"] = 1;}
	if (!isset($device["site_id"])) 	{$device["site_id"] = 1;}
	if ($device["host_template_id"] != 0) { /* fetch values from a valid device_template */
		$device_template = db_fetch_row("SELECT
			host_template.id,
			host_template.name,
			host_template.snmp_community,
			host_template.snmp_version,
			host_template.snmp_username,
			host_template.snmp_password,
			host_template.snmp_port,
			host_template.snmp_timeout,
			host_template.availability_method,
			host_template.ping_method,
			host_template.ping_port,
			host_template.ping_timeout,
			host_template.ping_retries,
			host_template.snmp_auth_protocol,
			host_template.snmp_priv_passphrase,
			host_template.snmp_priv_protocol,
			host_template.snmp_context,
			host_template.max_oids
			FROM host_template
			WHERE id=" . $device["host_template_id"]);
	} else { /* no device template given, so fetch system defaults */
		/* TODO: is there a system wide default for poller_id and/or site_id? */
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
	}

	/*
	 * if any value was given as a parameter,
	 * replace device_template or default setting by this one
	 */
	if (isset($device["snmp_community"])) 		{$device_template["snmp_community"]			= $device["snmp_community"];}
	if (isset($device["snmp_version"])) 		{$device_template["snmp_version"]			= $device["snmp_version"];}
	if (isset($device["snmp_username"]))		{$device_template["snmp_username"]			= $device["snmp_username"];}
	if (isset($device["snmp_password"])) 		{$device_template["snmp_password"]			= $device["snmp_password"];}
	if (isset($device["snmp_port"])) 			{$device_template["snmp_port"]				= $device["snmp_port"];}
	if (isset($device["snmp_timeout"])) 		{$device_template["snmp_timeout"]			= $device["snmp_timeout"];}
	if (isset($device["availability_method"]))	{$device_template["availability_method"]	= $device["availability_method"];}
	if (isset($device["ping_method"])) 			{$device_template["ping_method"]			= $device["ping_method"];}
	if (isset($device["ping_port"])) 			{$device_template["ping_port"]				= $device["ping_port"];}
	if (isset($device["ping_timeout"])) 		{$device_template["ping_timeout"]			= $device["ping_timeout"];}
	if (isset($device["ping_retries"])) 		{$device_template["ping_retries"]			= $device["ping_retries"];}
	if (isset($device["snmp_auth_protocol"])) 	{$device_template["snmp_auth_protocol"]		= $device["snmp_auth_protocol"];}
	if (isset($device["snmp_priv_passphrase"])) {$device_template["snmp_priv_passphrase"]	= $device["snmp_priv_passphrase"];}
	if (isset($device["snmp_priv_protocol"])) 	{$device_template["snmp_priv_protocol"]		= $device["snmp_priv_protocol"];}
	if (isset($device["snmp_context"])) 		{$device_template["snmp_context"]			= $device["snmp_context"];}
	if (isset($device["max_oids"]))	 			{$device_template["max_oids"]				= $device["max_oids"];}

	$device_template["notes"]		= (isset($device["notes"])) 	? $device["notes"] : "";
	$device_template["disabled"]	= (isset($device["disabled"])) 	? disabled : "";

	/*
	 * perform some nice printout

	 echo printf(__("Adding %1s (%2s) as '%3s'"), $device["description"], $device["hostname"], $device_template["name"]);

	 switch($device_template["availability_method"]) {
		case AVAIL_NONE:
		echo ", " . __("Availability Method None");
		break;
		case AVAIL_SNMP_AND_PING:
		echo ", " . __("Availability Method SNMP and PING");
		break;
		case AVAIL_SNMP:
		echo ", " . __("Availability Method SNMP");
		break;
		case AVAIL_PING:
		echo ", " . __("Availability Method PING");
		break;
		}
		if (($device_template["availability_method"] == AVAIL_SNMP_AND_PING) ||
		($device_template["availability_method"] == AVAIL_PING)) {
		switch($device_template["ping_method"]) {
		case PING_ICMP:
		printf(__(", Ping Method ICMP, Retries %1d, Ping Timeout %2d"), $device_template["ping_retries"], $device_template["ping_timeout"]);
		break;
		case PING_UDP:
		printf(__(", Ping Method UDP, UDP Port %1d, Retries %2d, Ping Timeout %3d"), $device_template["ping_port"], $device_template["ping_retries"], $device_template["ping_timeout"]);
		break;
		case PING_TCP:
		printf(__(", Ping Method TCP, TCP Port %1d, Retries %2d, Ping Timeout %3d"), $device_template["ping_port"], $device_template["ping_retries"], $device_template["ping_timeout"]);
		break;
		}
		}
		if (($device_template["availability_method"] == AVAIL_SNMP_AND_PING) ||
		($device_template["availability_method"] == AVAIL_SNMP)) {
		printf(__(", SNMP V%1s, SNMP Port %2d, SNMP Timeout %3d"), $device_template["snmp_version"], $device_template["snmp_port"], $device_template["snmp_timeout"]);
		switch($device_template["snmp_version"]) {
		case 1:
		case 2:
		printf(__(", Community %s"), $device_template["snmp_community"]);
		break;
		case 3:
		printf(__(", AuthProto %1s, AuthPass %2s, PrivProto %3s, PrivPass %4s, Context %5s"), $device_template["snmp_auth_protocol"], $device_template["snmp_password"], $device_template["snmp_priv_protocol"], $device_template["snmp_priv_passphrase"], $device_template["snmp_context"]);
		break;
		}
		}

		echo "\n";
		*/

	/*
	 * last, but not least, add this device along with all
	 * graph templates and data queries
	 * associated to the given device template id
	 */
	if ($debug) {
		print("api_device_save(0, ".$device['site_id'].", ".$device['poller_id'].", ".$device['host_template_id'].", ".
		$device['description'].", ".$device['hostname'].", ".$device_template['snmp_community'].", ".
		$device_template['snmp_version'].", ". $device_template['snmp_username'].", ".
		$device_template['snmp_password'].", ".	$device_template['snmp_port'].", ".
		$device_template['snmp_timeout'].", ". $device_template['disabled'].", ".
		$device_template['availability_method'].", ". $device_template['ping_method'].", ".
		$device_template['ping_port'].", ". $device_template['ping_timeout'].", ".
		$device_template['ping_retries'].", ". $device_template['notes'].", ".
		$device_template['snmp_auth_protocol'].", ". $device_template['snmp_priv_passphrase'].", ".
		$device_template['snmp_priv_protocol'].", ". $device_template['snmp_context'].", ". $device_template['max_oids'].")\n");
	} else {
		$device_id = api_device_save(0, $device["site_id"], $device["poller_id"], $device["host_template_id"],
		$device["description"], $device["hostname"],
		$device_template["snmp_community"], $device_template["snmp_version"],
		$device_template["snmp_username"], $device_template["snmp_password"],
		$device_template["snmp_port"], $device_template["snmp_timeout"],
		$device_template["disabled"],
		$device_template["availability_method"], $device_template["ping_method"],
		$device_template["ping_port"], $device_template["ping_timeout"],
		$device_template["ping_retries"], $device_template["notes"],
		$device_template["snmp_auth_protocol"], $device_template["snmp_priv_passphrase"],
		$device_template["snmp_priv_protocol"], $device_template["snmp_context"], $device_template["max_oids"]);

		if (is_error_message()) {
			echo __("ERROR: Failed to add this device") . "\n";
			print_r($_SESSION["sess_messages"]); global $messages;
			foreach (array_keys($_SESSION["sess_messages"]) as $current_message_id) {
				if (isset($messages[$current_message_id])) {
					print_r($messages[$current_message_id]);
				}
			}
			exit(1);
		} else {
			echo __("Success - new device-id: (%d)", $device_id) . "\n";
			exit(0);
		}
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Device Script 1.2") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add a device in Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--site-id=] [--poller-id=]\n";
	echo "       [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled]\n";
	echo "       [--avail=[pingsnmp]] [--ping-method=[tcp] --ping-port=[N/A, 1-65534]] --ping-retries=[2] --ping-timeout=[500]\n";
	echo "       [--version=1] [--community=] [--port=161] [--timeout=500]\n";
	echo "    [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "       [--quiet] [-d]\n\n";
	echo __("Required:") . "\n";
	echo "    --description   " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "    --ip            " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "   --template                  " . __("denotes the device template to be used") . "\n";
	echo "                    " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                               " . __("For a device template=0 (NONE), Cacti default settings are used.") . "\n";
	echo "                    " . __("Optionally overwrite by any of the following:") . "\n";
	echo __("Optional:") . "\n";
	echo "   --site-id         1         " . __("the numerical ID of the site") . "\n";
	echo "   --poller-id       1         " . __("the numerical ID of the poller") . "\n";
	echo "    --notes             " .    __("General information about this device. Must be enclosed using double quotes.") . "\n";
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
	echo "   -d                          " . __("Debug Mode, no updates made") . "\n";
	echo "    --quiet                " . __("batch mode value return") . "\n\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . " --ip=example.company.com --description=foobar --template=1\n";
	echo "   " . __("  creates a new device using ip 'example.company.com' with a description of 'foobar' and host template id of 1") . "\n";
	echo "   php -q " . $me . " --ip=example.company.com --description=foobar --template=1 --community=secret\n";
	echo "   " . __("  same as above but overriding SNMP comunity with the value of 'secret'") . "\n";
	echo "   php -q " . $me . " --ip=example.company.com --description=foobar --template=1 --site-id=1 --poller-id=3\n";
	echo "   " . __("  same as above using site id of 1 and poller id of 3") . "\n";
}
