<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

define("REGEXP_SNMP_TRIM", "(hex|counter(32|64)|gauge|gauge(32|64)|float|ipaddress|string|integer):");

define("SNMP_METHOD_PHP", 1);
define("SNMP_METHOD_BINARY", 2);

/* we must use an apostrophe to escape community names under Unix in case the user uses
characters that the shell might interpret. the ucd-snmp binaries on Windows flip out when
you do this, but are perfectly happy with a quotation mark. */
if (CACTI_SERVER_OS == "unix") {
	define("SNMP_ESCAPE_CHARACTER", "'");
}else{
	define("SNMP_ESCAPE_CHARACTER", "\"");
}

function cacti_snmp_get($hostname, $community, $oid, $version, $v3username, $v3password, $v3authproto = "", $v3privpassphrase = "", $v3privproto = "", $port = 161, $timeout = 500, $environ = SNMP_POLLER) {
	/* determine default retries */
	$retries = read_config_option("snmp_retries");
	if ($retries == "") $retries = 3;

	/* get rid of quotes in privacy passphrase */
	$v3privpassphrase = str_replace("#space#", " ", $v3privpassphrase);

	if ($v3privproto == "[None]") {
		$v3privproto = "";
	}

	if (snmp_get_method($version) == SNMP_METHOD_PHP) {
		/* make sure snmp* is verbose so we can see what types of data
		we are getting back */
		snmp_set_quick_print(0);

		if ($version == "1") {
			$snmp_value = @snmpget("$hostname:$port", $community, trim($oid), ($timeout * 1000), $retries);
		} else if ($version == "2") {
			$snmp_value = @snmp2_get("$hostname:$port", $community, $oid, ($timeout * 1000), $retries);
		} else {
			$snmp_value = @snmp3_get("$hostname:$port", $v3username, snmp_get_v3authpriv($v3privproto), $v3authproto,
				$v3password, $v3privproto, $v3privpassphrase, trim($oid), ($timeout * 1000), $retries);
		}
	}else{
		/* ucd/net snmp want the timeout in seconds */
		$timeout = ceil($timeout / 1000);

		if ($version == "1") {
			$snmp_auth = (read_config_option("snmp_version") == "ucd-snmp") ? SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER : "-c " . SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER; /* v1/v2 - community string */
		}else if ($version == "2") {
			$snmp_auth = (read_config_option("snmp_version") == "ucd-snmp") ? SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER : "-c " . SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER; /* v1/v2 - community string */
			$version = "2c"; /* ucd/net snmp prefers this over '2' */
		}else if ($version == "3") {
			$snmp_auth = "-u $v3username -A $v3password -a $v3authproto -X $v3privpassphrase -x $v3privproto -l " . snmp_get_v3authpriv($v3privproto); /* v3 - username/password/etc... */
		}

		/* no valid snmp version has been set, get out */
		if (empty($snmp_auth)) { return; }

		if (read_config_option("snmp_version") == "ucd-snmp") {
			exec(read_config_option("path_snmpget") . " -O vt -v$version -t $timeout -r $retries $hostname:$port $snmp_auth $oid", $snmp_value);
		}else {
			exec(read_config_option("path_snmpget") . " -O fntev $snmp_auth -v $version -t $timeout -r $retries $hostname:$port $oid", $snmp_value);
		}
	}
	if (isset($snmp_value)) {
		/* fix for multi-line snmp output */
		if (is_array($snmp_value)) {
			$snmp_value = implode(" ", $snmp_value);
		}
	}
	/* strip out non-snmp data */
	$snmp_value = format_snmp_string($snmp_value);

	return $snmp_value;
}

function cacti_snmp_walk($hostname, $community, $oid, $version, $v3username, $v3password, $v3authproto = "", $v3privpassphrase = "", $v3privproto = "", $port = 161, $timeout = 500, $environ = SNMP_POLLER) {
	require_once(CACTI_BASE_PATH . "/lib/sys/exec.php");

	$snmp_array = array();
	$temp_array = array();

	/* determine default retries */
	$retries = read_config_option("snmp_retries");
	if ($retries == "") $retries = 3;

	/* get rid of quotes in privacy passphrase */
	$v3privpassphrase = str_replace("#space#", " ", $v3privpassphrase);

	if ($v3privproto == "[None]") {
		$v3privproto = "";
	}

	if (snmp_get_method($version) == SNMP_METHOD_PHP) {
		/* make sure snmp* is verbose so we can see what types of data
		we are getting back */
		snmp_set_quick_print(0);

		if (function_exists("snmp_set_valueretrieval")) {
			snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
		}
		if ($version == "1") {
			$temp_array = @snmprealwalk("$hostname:$port", $community, trim($oid), ($timeout * 1000), $retries);
		} else if ($version == "2") {
			$temp_array = @snmp2_real_walk("$hostname:$port", $community, trim($oid), ($timeout * 1000), $retries);
		} else {
			$temp_array = @snmp3_real_walk("$hostname:$port", $v3username, snmp_get_v3authpriv($v3privproto), $v3authproto,
				$v3password, $v3privproto, $v3privpassphrase, trim($oid), ($timeout * 1000), $retries);
		}

		$o = 0;
		for (@reset($temp_array); $i = @key($temp_array); next($temp_array)) {
			$snmp_array[$o]["oid"] = ereg_replace("^\.", "", $i);
			$snmp_array[$o]["value"] = format_snmp_string($temp_array[$i]);
			$o++;
		}
	}else{
		/* ucd/net snmp want the timeout in seconds */
		$timeout = ceil($timeout / 1000);

		if ($version == "1") {
			$snmp_auth = (read_config_option("snmp_version") == "ucd-snmp") ? SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER : "-c " . SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER; /* v1/v2 - community string */
		}elseif ($version == "2") {
			$snmp_auth = (read_config_option("snmp_version") == "ucd-snmp") ? SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER : "-c " . SNMP_ESCAPE_CHARACTER . $community . SNMP_ESCAPE_CHARACTER; /* v1/v2 - community string */
			$version = "2c"; /* ucd/net snmp prefers this over '2' */
		}elseif ($version == "3") {
			$snmp_auth = "-u $v3username -A $v3password -a $v3authproto -X $v3privpassphrase -x $v3privproto -l " . snmp_get_v3authpriv($v3privproto); /* v3 - username/password/etc... */
		}

		if (read_config_option("snmp_version") == "ucd-snmp") {
			$temp_array = exec_into_array(read_config_option("path_snmpwalk") . " -v$version -t $timeout -r $retries $hostname:$port $snmp_auth $oid");
		}else {
			$temp_array = exec_into_array(read_config_option("path_snmpwalk") . " -O QfntUe $snmp_auth -v $version -t $timeout -r $retries $hostname:$port $oid");
		}

		if ((sizeof($temp_array) == 0) || (substr_count($temp_array[0], "No Such Object"))) {
			return array();
		}

		for ($i=0; $i < count($temp_array); $i++) {
			$snmp_array[$i]["oid"] = trim(ereg_replace("(.*) =.*", "\\1", $temp_array[$i]));
			$snmp_array[$i]["value"] = format_snmp_string($temp_array[$i]);
		}
	}

	return $snmp_array;
}

function format_snmp_string($string) {
	/* strip off all leading junk (the oid and stuff) */
	$string = trim(ereg_replace(".*= ?", "", $string));

	/* remove ALL quotes */
	$string = str_replace("\"", "", $string);
	$string = str_replace("'", "", $string);
	$string = str_replace(">", "", $string);
	$string = str_replace("<", "", $string);
	$string = str_replace("\\", "", $string);

	if (preg_match("/(hex:\?)?([a-fA-F0-9]{1,2}(:|\s)){5}/", $string)) {
		$octet = "";

		/* strip of the 'hex:' */
		$string = eregi_replace("hex: ?", "", $string);

		/* split the hex on the delimiter */
		$octets = preg_split("/\s|:/", $string);

		/* loop through each octet and format it accordingly */
		for ($i=0;($i<count($octets));$i++) {
			$octet .= str_pad($octets[$i], 2, "0", STR_PAD_LEFT);

			if (($i+1) < count($octets)) {
				$octet .= ":";
			}
		}

		/* copy the final result and make it upper case */
		$string = strtoupper($octet);
	}elseif (preg_match("/Timeticks:\s\((\d+)\)\s/", $string, $matches)) {
		$string = $matches[1];
	}

	$string = eregi_replace(REGEXP_SNMP_TRIM, "", $string);

	return trim($string);
}

function snmp_get_method($version = 1) {
	if ((function_exists("snmp3_get")) && ($version == 3)) {
		return SNMP_METHOD_PHP;
	}elseif ((function_exists("snmpget")) && ($version == 1)) {
		return SNMP_METHOD_PHP;
	}elseif ((function_exists("snmp2_get")) && ($version == 2)) {
		return SNMP_METHOD_PHP;
	}elseif (($version == 2) && (file_exists(read_config_option("path_snmpget")))) {
		return SNMP_METHOD_BINARY;
	}elseif (function_exists("snmpget")) {
		/* last resort (hopefully it isn't a 64-bit result) */
		return SNMP_METHOD_PHP;
	}elseif (file_exists(read_config_option("path_snmpget"))) {
		return SNMP_METHOD_BINARY;
	}else{
		/* looks like snmp is broken */
		return SNMP_METHOD_BINARY;
	}
}

function snmp_get_v3authpriv($v3privproto) {
	if (($v3privproto == "[None]") || ($v3privproto == "")) {
		return "authNoPriv";
	} else {
		return "authPriv";
	}
}

?>