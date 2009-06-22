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

if (sizeof($parms)) {
	$displayCommunities   = FALSE;
	$quietMode            = FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

			break;
		case "--description":
			$description = trim($value);
			if ($description == "") {
				echo __("ERROR: You must supply a valid description!") . "\n";
				exit(1);
			}

			break;
		case "--ip":
			$ip = trim($value);
			if ($ip == "") {
				echo __("ERROR: You must supply a valid [hostname|IP address] for all devices!\n");
				exit(1);
			}

			break;
		case "--template":
			$template_id = $value;
			if (!is_numeric($template_id)) {
				echo __("ERROR: You must supply a numeric device template id for all devices!\n");
				exit(1);
			}

			break;
		case "--community":
			$snmp_community = trim($value);

			break;
		case "--poller":
			$poller_id = trim($value);

			break;
		case "--version":
			if (is_numeric($value) && ($value == 1 || $value == 2 || $value == 3)) {
				$snmp_ver = $value;
			}else{
				printf(__("ERROR: Invalid SNMP Version: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--notes":
			$notes = trim($value);

			break;
		case "--disable":
			/* validate the disabled state */
			if (is_numeric($value) && ($value == 1 || $value == 0)) {
				$disabled  = $value;
			} else {
				printf(__("ERROR: Invalid disabled flag (%d)\n\n"), $disabled);
				exit(1);
			}

			break;
		case "--username":
			$snmp_username = trim($value);

			break;
		case "--password":
			$snmp_password = trim($value);

			break;
		case "--authproto":
			$snmp_auth_protocol = trim($value);
			if (($snmp_auth_protocol != "MD5") || ($snmp_auth_protocol != "SHA")) {
				printf(__("ERROR: Invalid SNMP AuthProto: (%s)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--privproto":
			$snmp_priv_protocol = trim($value);
			if (($snmp_priv_protocol != "DES") || ($snmp_priv_protocol != "AES")) {
				printf(__("ERROR: Invalid SNMP PrivProto: (%s)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--privpass":
			$snmp_priv_passphrase = trim($value);

			break;
		case "--port":
			if (is_numeric($value) && ($value > 0)) {
				$snmp_port     = $value;
			}else{
				printf(__("ERROR: Invalid SNMP Port: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--timeout":
			if (is_numeric($value) && ($value > 0) && ($value <= 20000)) {
				$snmp_timeout     = $value;
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

			break;
		case "--ping_port":
			if (is_numeric($value) && ($value > 0)) {
				$ping_port = $value;
			}else{
				printf(__("ERROR: Invalid Ping Port: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--ping_retries":
			if (is_numeric($value) && ($value > 0)) {
				$ping_retries = $value;
			}else{
				printf(__("ERROR: Invalid Ping Retries: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--ping_timeout":
			if (is_numeric($value) && ($value > 0)) {
				$ping_timeout = $value;
			}else{
				printf(__("ERROR: Invalid Ping Timeout: (%d)\n\n"), $value);
				display_help($me);
				exit(1);
			}

			break;
		case "--max_oids":
			if (is_numeric($value) && ($value > 0)) {
				$max_oids = $value;
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
		case "--list-communities":
			$displayCommunities = TRUE;

			break;
		case "--quiet":
			$quietMode = TRUE;

			break;
		default:
			printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
			display_help($me);
			exit(1);
		}
	}

	/*
	 * handle display options
	 */
	if ($displayCommunities) {
		displayCommunities($quietMode);
		exit(0);
	}

	/*
	 * verify required parameters
	 * for update / insert options
	 */
	if (!isset($description)) {
		echo __("ERROR: You must supply a description for all devices!\n");
		exit(1);
	}

	if (!isset($ip)) {
		echo __("ERROR: You must supply a valid [hostname|IP address] for all devices!\n");
		exit(1);
	}

	if (!isset($template_id)) {
		echo __("ERROR: You must supply a valid device template id for all devices!\n");
		exit(1);
	}

	/*
	 * set the default poller id if none was specified
	 */
	if (!isset($poller_id)) {
		$poller_id = 0;
	}

	/*
	 * verify valid device template and get a name for it
	 * pay attention to template_id 0 for "None"
	 */
	if ($template_id == 0) { /* allow for a template of "None" */
		$host_template["name"] = "None";
	} else {
		$host_template = db_fetch_row("SELECT name FROM host_template WHERE id = " . $template_id);
		if (!isset($host_template["name"])) {
			printf(__("ERROR: Unknown template id (%d)\n"), $template_id);
			exit(1);
		}
	}

	/*
	 * update host hostname|ip for a given description
	 */
	$hosts = db_fetch_row("SELECT id, hostname FROM host WHERE description = " . $description);
	if (isset($hosts["id"])) {
		db_execute("UPDATE host SET hostname='$ip' WHERE id=" . $hosts["id"]);
		printf(__("Updated device (%1s) with new hostname='%2s', device-id: (%3d)\n"), $description, $ip, $hosts["id"]);
		exit(1);
	}

	/*
	 * update device description for given hostname|ip
	 */
	$addresses = db_fetch_row("SELECT id, hostname, description FROM host WHERE hostname = " . $ip);
	if (isset($addresses["id"])) {
		db_execute("UPDATE host SET description = '$description' WHERE id = " . $addresses["id"]);
		printf(__("Updated device (%2s) with new description='%1s', device-id: (%3d)\n"), $description, $ip, $addresses["id"]);
		exit(1);
	}

	/*
	 * now, either fetch host_template parameters for given template_id
	 * or use Cacti system defaults
	 */
	if ($template_id != 0) { /* fetch values from a valid host_template */
		$host_template = db_fetch_row("SELECT
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
			WHERE id=" . $template_id);
	} else { /* no host template given, so fetch system defaults */
		$host_template["snmp_community"]		= read_config_option("snmp_community");
		$host_template["snmp_version"]			= read_config_option("snmp_ver");
		$host_template["snmp_username"]			= read_config_option("snmp_username");
		$host_template["snmp_password"]			= read_config_option("snmp_password");
		$host_template["snmp_port"]				= read_config_option("snmp_port");
		$host_template["snmp_timeout"]			= read_config_option("snmp_timeout");
		$host_template["availability_method"]	= read_config_option("availability_method");
		$host_template["ping_method"]			= read_config_option("ping_method");
		$host_template["ping_port"]				= read_config_option("ping_port");
		$host_template["ping_timeout"]			= read_config_option("ping_timeout");
		$host_template["ping_retries"]			= read_config_option("ping_retries");
		$host_template["snmp_auth_protocol"]	= read_config_option("snmp_auth_protocol");
		$host_template["snmp_priv_passphrase"]	= read_config_option("snmp_priv_passphrase");
		$host_template["snmp_priv_protocol"]	= read_config_option("snmp_priv_protocol");
		$host_template["snmp_context"]			= read_config_option("snmp_context");
		$host_template["max_oids"]				= read_config_option("max_get_size");
	}

	/*
	 * if any value was given as a parameter,
	 * replace host_template or default setting by this one
	 */
	if (isset($snmp_community)) 		{$host_template["snmp_community"]			= $snmp_community;}
	if (isset($snmp_ver)) 				{$host_template["snmp_version"]				= $snmp_ver;}
	if (isset($snmp_username))			{$host_template["snmp_username"]			= $snmp_username;}
	if (isset($snmp_password)) 			{$host_template["snmp_password"]			= $snmp_password;}
	if (isset($snmp_port)) 				{$host_template["snmp_port"]				= $snmp_port;}
	if (isset($snmp_timeout)) 			{$host_template["snmp_timeout"]				= $snmp_timeout;}
	if (isset($availability_method))	{$host_template["availability_method"]		= $availability_method;}
	if (isset($ping_method)) 			{$host_template["ping_method"]				= $ping_method;}
	if (isset($ping_port)) 				{$host_template["ping_port"]				= $ping_port;}
	if (isset($ping_timeout)) 			{$host_template["ping_timeout"]				= $ping_timeout;}
	if (isset($ping_retries)) 			{$host_template["ping_retries"]				= $ping_retries;}
	if (isset($snmp_auth_protocol)) 	{$host_template["snmp_auth_protocol"]		= $snmp_auth_protocol;}
	if (isset($snmp_priv_passphrase)) 	{$host_template["snmp_priv_passphrase"]		= $snmp_priv_passphrase;}
	if (isset($snmp_priv_protocol)) 	{$host_template["snmp_priv_protocol"]		= $snmp_priv_protocol;}
	if (isset($snmp_context)) 			{$host_template["snmp_context"]				= $snmp_context;}
	if (isset($max_oids))	 			{$host_template["max_oids"]					= $max_oids;}

	$host_template["notes"]		= (isset($notes)) ? $notes : "";
	$host_template["disabled"]	= (isset($disabled)) ? disabled : "";

	/*
	 * perform some nice printout
	 */
	echo printf(__("Adding %1s (%2s) as '%3s'"), $description, $ip, $host_template["name"]);

	switch($host_template["availability_method"]) {
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
	if (($host_template["availability_method"] == AVAIL_SNMP_AND_PING) ||
		($host_template["availability_method"] == AVAIL_PING)) {
		switch($host_template["ping_method"]) {
			case PING_ICMP:
				printf(__(", Ping Method ICMP, Retries %1d, Ping Timeout %2d"), $host_template["ping_retries"], $host_template["ping_timeout"]);
				break;
			case PING_UDP:
				printf(__(", Ping Method UDP, UDP Port %1d, Retries %2d, Ping Timeout %3d"), $host_template["ping_port"], $host_template["ping_retries"], $host_template["ping_timeout"]);
				break;
			case PING_TCP:
				printf(__(", Ping Method TCP, TCP Port %1d, Retries %2d, Ping Timeout %3d"), $host_template["ping_port"], $host_template["ping_retries"], $host_template["ping_timeout"]);
				break;
		}
	}
	if (($host_template["availability_method"] == AVAIL_SNMP_AND_PING) ||
		($host_template["availability_method"] == AVAIL_SNMP)) {
		printf(__(", SNMP V%1s, SNMP Port %2d, SNMP Timeout %3d"), $host_template["snmp_version"], $host_template["snmp_port"], $host_template["snmp_timeout"]);
		switch($host_template["snmp_version"]) {
			case 1:
			case 2:
				printf(__(", Community %s"), $host_template["snmp_community"]);
				break;
			case 3:
				printf(__(", AuthProto %1s, AuthPass %2s, PrivProto %3s, PrivPass %4s, Context %5s"), $host_template["snmp_auth_protocol"], $host_template["snmp_password"], $host_template["snmp_priv_protocol"], $host_template["snmp_priv_passphrase"], $host_template["snmp_context"]);
				break;
		}
	}

	echo "\n";


	/*
	 * last, but not least, add this device along with all
	 * graph templates and data queries
	 * associated to the given device template id
	 */
	$host_id = api_device_save(0, $poller_id, $template_id, $description,
		$ip, $host_template["snmp_community"], $host_template["snmp_version"],
		$host_template["snmp_username"], $host_template["snmp_password"],
		$host_template["snmp_port"], $host_template["snmp_timeout"],
		$host_template["disabled"],
		$host_template["availability_method"], $host_template["ping_method"],
		$host_template["ping_port"], $host_template["ping_timeout"],
		$host_template["ping_retries"], $host_template["notes"],
		$host_template["snmp_auth_protocol"], $host_template["snmp_priv_passphrase"],
		$host_template["snmp_priv_protocol"], $host_template["snmp_context"], $host_template["max_oids"]);

	if (is_error_message()) {
		echo __("ERROR: Failed to add this device") . "\n";
		exit(1);
	} else {
		printf(__("Success - new device-id: (%d)\n", $host_id));
		exit(0);
	}
}else{
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("Add Device Script 1.2") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add a device in Cacti") . "\n\n";
	echo __("usage: ") . $me . " --description=[description] --ip=[IP] --template=[ID] [--notes=\"[]\"] [--disabled]\n";
	echo "    [--avail=[ping]] --ping_method=[icmp] --ping_port=[N/A, 1-65534] --ping_retries=[2]  --ping_timeout=[500]\n";
	echo "    [--version=[1|2|3]] [--community=] [--port=161] [--timeout=500]\n";
	echo "    [--username= --password=] [--authproto=] [--privpass= --privproto=] [--context=]\n";
	echo "    [--quiet]\n\n";
	echo __("Required:") . "\n";
	echo "   --device-id      " . __("the numerical ID of the device") . "\n";
	echo "    --description   " . __("the name that will be displayed by Cacti in the graphs") . "\n";
	echo "    --ip            " . __("self explanatory (can also be a FQDN)") . "\n";
	echo "    --template      " . __("denotes the device template to be used (read below to get a list of templates)") . "\n";
	echo "                    " . __("In case a device template is given, all values are fetched from this one.") . "\n";
	echo "                    " . __("For a device template = 0 (NONE), Cacti default settings are used.") . "\n";
	echo "                    " . __("Optionally overwrite by any of the following:") . "\n";
	echo __("Optional:") . "\n";
	echo "    --poller        0,  " . __("defines which Cacti poller will be handles the polling of this device.") . "\n";
	echo "    --notes             " .    __("General information about this device. Must be enclosed using double quotes.") . "\n";
	echo "    --disable       1   " . __("to add this device but to disable checks and 0 to enable it") . "\n";
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
	echo __("List Options:") . "\n";
	echo "    --list-communities     " . __("list community strings used") . "\n";
	echo "    --quiet                " . __("batch mode value return") . "\n\n";
}

?>
