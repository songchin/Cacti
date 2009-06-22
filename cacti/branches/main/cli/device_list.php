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
$me = array_shift($parms);

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
				echo __("ERROR: You must supply a numeric device template id for all devices!\n");
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
				display_help($me);
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
				display_help($me);
				exit(1);
			}

			break;
		case "--privproto":
			$host["snmp_priv_protocol"] = trim($value);
			if (($host["snmp_priv_protocol"] != "DES") && ($host["snmp_priv_protocol"] != "AES")) {
				printf(__("ERROR: Invalid SNMP PrivProto: (%s)\n\n"), $value);
				display_help($me);
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
				display_help($me);
				exit(1);
			}

			break;
		case "--timeout":
			if (is_numeric($value) && ($value > 0) && ($value <= 20000)) {
				$host["snmp_timeout"]     = $value;
			}else{
				echo __("ERROR: Invalid SNMP Timeout.  Valid values are from 1 to 20000") . "\n";
				display_help($me);
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
				display_help($me);
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
				display_help($me);
				exit(1);
			}
			$host["ping_method"] = $ping_method;

			break;
		case "--ping_port":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_port"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Port: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--ping_retries":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_retries"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Retries: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--ping_timeout":
			if (is_numeric($value) && ($value > 0)) {
				$host["ping_timeout"] = $value;
			}else{
				printf(__("ERROR: Invalid Ping Timeout: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--max_oids":
			if (is_numeric($value) && ($value > 0)) {
				$host["max_oids"] = $value;
			}else{
				printf(__("ERROR: Invalid Max OIDS: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--version":
		case "-V":
		case "-H":
		case "--help":
			display_help($me);
			exit(0);
		case "--quiet":
			$quietMode = TRUE;

			break;
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help($me);
			exit(1);
		}
	}

	/* get devices matching criteria */
	$hosts = getHosts($host);
	/* display matching hosts */
	displayHosts($hosts, $quietMode);

}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Device List Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list device(s) in Cacti") . "\n\n";
	echo __("usage: ") . $me . " [--description=] [--ip=] [--template=] [--notes=\"[]\"] [--disabled=]\n";
	echo "    [--avail=] [--ping_method=] [--ping_port=] [--ping_retries=]  [--ping_timeout=]\n";
	echo "    [--version=] [--community=] [--port=] [--timeout=]\n";
	echo "    [--username=] [--password=] [--authproto=] [--privpass=] [--privproto=] [--context=]\n";
	echo "    [--quiet]\n\n";
	echo __("All Parameters are optional. Any parameters given must match") . "\n";
	echo __("Optional:") . "\n";
	echo "    --description        " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "    --ip                 " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "    --template           " . __("numeric device template id") . "\n";
	echo "    --notes              " . __("General information about this device.  Must be enclosed using double quotes.") . "\n";
	echo "    --disabled      1    " . __("for disabled devices, 0 for enabled devices") . "\n";
	echo "    --avail         pingsnmp, [ping][none, snmp, pingsnmp] " . __("device availability check") . "\n";
	echo "      --ping_method   tcp, icmp|tcp|udp " . __("if ping selected") . "\n";
	echo "      --ping_port     23,  " . __("port used for tcp|udp pings [1-65534]") . "\n";
	echo "      --ping_retries  2,   " . __("the number of time to attempt to communicate with a device") . "\n";
	echo "      --ping_timeout  500, " . __("ping timeout") . "\n";
	echo "    --version       1, 1|2|3, " . __("snmp version") . "\n";
	echo "    --community     '',  " . __("snmp community string for snmpv1 and snmpv2.  Leave blank for no community") . "\n";
	echo "    --port          161, " . __("snmp port") . "\n";
	echo "    --timeout       500, " . __("snmp timeout") . "\n";
	echo "    --username      '',  " . __("snmp username for snmpv3") . "\n";
	echo "    --password      '',  " . __("snmp password for snmpv3") . "\n";
	echo "    --authproto     '',  " . __("snmp authentication protocol for snmpv3 [MD5|SHA]") . "\n";
	echo "    --privpass      '',  " . __("snmp privacy passphrase for snmpv3") . "\n";
	echo "    --privproto     '',  " . __("snmp privacy protocol for snmpv3 [DES|AES]") . "\n";
	echo "    --context       '',  " . __("snmp context for snmpv3") . "\n";
	echo "    --max_oids      10,  " . __("the number of OID's that can be obtained in a single SNMP Get request [1-60]") . "\n\n";
	echo "    --quiet              " . __("batch mode value return") . "\n\n";
}

?>
