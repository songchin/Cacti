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
include_once(CACTI_BASE_PATH."/lib/api_device.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

if (sizeof($parms)) {
	$quietMode	= FALSE;
	$host		= array();

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--description":
			$host["description"] = trim($value);

			break;
		case "--ip":
			$host["ip"] = trim($value);
	
			break;
		case "--template":
			$host["host_template_id"] = $value;
			if (!is_numeric($host["host_template_id"])) {
				echo __("ERROR: You must supply a numeric host template id for all hosts!\n");
				exit(1);
			}
	
			break;
		case "--community":
			$host["snmp_community"] = trim($value);

			break;
		case "--version":
			if (is_numeric($value) && ($value == 1 || $value == 2 || $value == 3)) {
				$host["snmp_version"] = $value;
			}else{
				printf(__("ERROR: Invalid SNMP Version: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--notes":
			$host["notes"] = trim($value);

			break;
		case "--disabled":
			/* validate the disabled state */
			if (is_numeric($value) && ($value == 1)) {
				$host["disabled"]  = '"on"';
			} elseif (is_numeric($value) && ($value == 0)) {
				$host["disabled"]  = '""';
			} else {
				printf(__("ERROR: Invalid disabled flag (%s)\n"), $disabled);
				exit(1);
			}

			break;
		case "--username":
			$host["snmp_username"] = trim($value);

			break;
		case "--password":
			$host["snmp_password"] = trim($value);

			break;
		case "--authproto":
			$host["snmp_auth_protocol"] = trim($value);
			if (($host["snmp_auth_protocol"] != "MD5") && ($host["snmp_auth_protocol"] != "SHA")) {
				printf(__("ERROR: Invalid SNMP AuthProto: (%s)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--privproto":
			$host["snmp_priv_protocol"] = trim($value);
			if (($host["snmp_priv_protocol"] != "DES") && ($host["snmp_priv_protocol"] != "AES")) {
				printf(__("ERROR: Invalid SNMP PrivProto: (%s)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--privpass":
			$host["snmp_priv_passphrase"] = trim($value);

			break;
		case "--context":
			$host["snmp_context"] = trim($value);

			break;
		case "--port":
			if (is_numeric($value) && ($value > 0)) {
				$host["snmp_port"]     = $value;
			}else{
				printf(__("ERROR: Invalid SNMP Port: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--timeout":
			if (is_numeric($value) && ($value > 0) && ($value <= 20000)) {
				$host["snmp_timeout"]     = $value;
			}else{
				echo __("ERROR: Invalid SNMP Timeout.  Valid values are from 1 to 20000\n");
				display_help();
				exit(1);
			}

			break;
		case "--avail":
			switch($value) {
			case "none":
				$availability_method = '0'; /* tried to use AVAIL_NONE, but then ereg failes on validation, sigh */

				break;
			case "ping":
				$availability_method = AVAIL_PING;

				break;
			case "snmp":
				$availability_method = AVAIL_SNMP;

				break;
			case "pingsnmp":
				$availability_method = AVAIL_SNMP_AND_PING;

				break;
			default:
				printf(__("ERROR: Invalid Availability Parameter: (%s)\n\n"), $value);
				display_help();
				exit(1);
			}
			$host["availability_method"] = $availability_method;
					
			break;
		case "--ping_method":
			switch(strtolower($value)) {
			case "icmp":
				$ping_method = PING_ICMP;

				break;
			case "tcp":
				$ping_method = PING_TCP;

				break;
			case "udp":
				$ping_method = PING_UDP;

				break;
			default:
				printf(__("ERROR: Invalid Ping Method: (%s)\n\n"), $value);
				display_help();
				exit(1);
			}
			$host["ping_method"] = $ping_method;
					
			break;
		case "--ping_port":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_port"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Port: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--ping_retries":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_retries"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Retries: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--ping_timeout":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_timeout"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Timeout: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--max_oids":
			if (is_numeric($value) && ($value > 0)) {
				$host["max_oids"] = $value;
			}else{
				printf(__("ERROR: Invalid Max OIDS: (%d)\n\n"), $value);
				display_help();
				exit(1);
			}

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help();
			exit(0);
		case "--quiet":
			$quietMode = TRUE;

			break;
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help();
			exit(1);
		}
	}

	/* get hosts matching criteria */
	$hosts = getHosts($host);
	/* display matching hosts */
	displayHosts($hosts, $quietMode);

}else{
	display_help();
	exit(0);
}

function display_help() {
	echo __("Device List Script 1.0, Copyright 2009 - The Cacti Group\n\n");
	echo __("A simple command line utility to list device(s) in Cacti\n\n");
	echo __("usage: device_list.php [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled=]\n");
	echo __("    [--avail=] [--ping_method=] [--ping_port=] [--ping_retries=]  [--ping_timeout=]\n");
	echo __("    [--version=] [--community=] [--port=] [--timeout=]\n");
	echo __("    [--username=] [--password=] [--authproto=] [--privpass=] [--privproto=] [--context=]\n");
	echo __("    [--quiet]\n\n");
	echo __("All Parameters are optional. Any parameters given must match\n");
	echo __("Optional:\n");
	echo __("    --description  the name that will be displayed by Cacti in the graphs\n");
	echo __("    --ip           self explanatory (can also be a FQDN)\n");
	echo __("    --template     numeric host_template id\n");
	echo __("    --notes        General information about this host.  Must be enclosed using double quotes.\n");
	echo __("    --disabled     1 for disabled hosts, 0 for enabled hosts\n");
	echo __("    --avail        [pingsnmp], [ping], [none], [snmp]\n");
	echo __("    --ping_method  [tcp], [icmp], [udp]\n");
	echo __("    --ping_port    port used for tcp|udp pings [1-65534]\n");
	echo __("    --ping_retries the number of time to attempt to communicate with a host\n");
	echo __("    --ping_timeout ping timeout\n");
	echo __("    --version      1|2|3, snmp version\n");
	echo __("    --community    snmp community string for snmpv1 and snmpv2\n");
	echo __("    --port         snmp port\n");
	echo __("    --timeout      snmp timeout\n");
	echo __("    --username     snmp username for snmpv3\n");
	echo __("    --password     snmp password for snmpv3\n");
	echo __("    --authproto    snmp authentication protocol for snmpv3 [MD5|SHA]\n");
	echo __("    --privpass     snmp privacy passphrase for snmpv3\n");
	echo __("    --privproto    snmp privacy protocol for snmpv3 [DES|AES]\n");
	echo __("    --context      snmp context for snmpv3\n");
	echo __("    --max_oids     the number of OID's that can be obtained in a single SNMP Get request\n\n");
	echo __("    --quiet        batch mode value return\n\n");
}

?>
