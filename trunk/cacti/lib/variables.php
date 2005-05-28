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

// variable cache??

function remove_variables($string) {
	return ereg_replace("\|[a-zA-Z0-9_]+\|( - )?", "", $string);
}

/* substitute_path_variables - takes a string and substitutes all path variables contained in it
   @arg $path - the string to make path variable substitutions on
   @returns - the original string with all of the variable substitutions made */
function substitute_path_variables($path) {
	include_once(CACTI_BASE_PATH . "/lib/string.php");

	/* script query */
	$path = clean_up_path(str_replace("|path_cacti|", CACTI_BASE_PATH, $path));
	$path = clean_up_path(str_replace("|path_php_binary|", read_config_option("path_php_binary"), $path));

	/* data source */
	$path = clean_up_path(str_replace("<path_rra>", CACTI_BASE_PATH . "/rra", $path));

	/* script */
	$path = clean_up_path(str_replace("<path_cacti>", CACTI_BASE_PATH, $path));
	$path = clean_up_path(str_replace("<path_snmpget>", read_config_option("path_snmpget"), $path));
	$path = clean_up_path(str_replace("<path_php_binary>", read_config_option("path_php_binary"), $path));

	return $path;
}

/* substitute_host_variables - takes a string and substitutes all host variables contained in it
   @arg $string - the string to make host variable substitutions on
   @arg $host_id - (int) the host ID to match
   @returns - the original string with all of the variable substitutions made */
function substitute_host_variables($string, $host_id) {
	$host = db_fetch_row("select * from host where id = $host_id");

	/* accomodate for snmp variations */
	if (!strlen($host["snmp_community"])) $host["snmp_community"] = "[None]";
	if (!strlen($host["snmpv3_auth_username"])) $host["snmpv3_auth_username"] = "[None]";
	if (!strlen($host["snmpv3_auth_password"])) $host["snmpv3_auth_password"] = "[None]";
	if (!strlen($host["snmpv3_auth_protocol"])) $host["snmpv3_auth_protocol"] = "[None]";
	if (!strlen($host["snmpv3_priv_passphrase"])) $host["snmpv3_priv_passphrase"] = "[None]";
	if (!strlen($host["snmpv3_priv_protocol"]))  $host["snmpv3_priv_protocol"] = "[None]";

	$string = str_replace("|host_management_ip|", $host["hostname"], $string); /* for compatability */
	$string = str_replace("|host_hostname|", $host["hostname"], $string);
	$string = str_replace("|host_description|", $host["description"], $string);
	$string = str_replace("|host_snmp_version|", $host["snmp_version"], $string);
	$string = str_replace("|host_snmp_community|", $host["snmp_community"], $string);
	$string = str_replace("|host_snmpv3_auth_username|", $host["snmpv3_auth_username"], $string);
	$string = str_replace("|host_snmpv3_auth_password|", $host["snmpv3_auth_password"], $string);
	$string = str_replace("|host_snmpv3_auth_protocol|", $host["snmpv3_auth_protocol"], $string);
	$string = str_replace("|host_snmpv3_priv_passphrase|", $host["snmpv3_priv_passphrase"], $string);
	$string = str_replace("|host_snmpv3_priv_protocol|", $host["snmpv3_priv_protocol"], $string);
	$string = str_replace("|host_snmp_port|", $host["snmp_port"], $string);
	$string = str_replace("|host_snmp_timeout|", $host["snmp_timeout"], $string);
	$string = str_replace("|host_id|", $host["id"], $string);

	return $string;
}

/* substitute_data_query_variables - takes a string and substitutes all data query variables contained in it
   @arg $string - the original string that contains the data query variables
   @arg $host_id - (int) the host ID to match
   @arg $data_query_id - (int) the data query ID to match
   @arg $data_query_index - the data query index to match
   @arg $max_chars - the maximum number of characters to substitute
   @returns - the original string with all of the variable substitutions made */
function substitute_data_query_variables($string, $host_id, $data_query_id, $data_query_index, $max_chars = 0) {
	include_once(CACTI_BASE_PATH . "/lib/string.php");

	$data_query_cache = db_fetch_assoc("select field_name,field_value from host_snmp_cache where host_id = $host_id and snmp_query_id = $data_query_id and snmp_index = '$data_query_index'");

	if (sizeof($data_query_cache) > 0) {
		foreach ($data_query_cache as $item) {
			if ($item["field_value"] != "") {
				if ($max_chars > 0) {
					$item["field_value"] = substr($item["field_value"], 0, $max_chars);
				}

				$string = stri_replace("|query_" . $item["field_name"] . "|", $item["field_value"], $string);
			}
		}
	}

	return $string;
}

function evaluate_data_query_suggested_values($host_id, $data_query_id, $data_query_index, $sql_table, $sql_where) {
	/* see which data query cache variables are available */
	$data_query_cache = array_rekey(db_fetch_assoc("select field_name,field_value from host_snmp_cache where host_id = $host_id and snmp_query_id = $data_query_id and snmp_index = '$data_query_index'"), "field_name", "field_value");

	/* get a list of suggested values */
	$suggested_values = db_fetch_assoc("select value from $sql_table where $sql_where order by sequence");

	if (sizeof($suggested_values) > 0) {
		foreach ($suggested_values as $item) {
			$found_negative_match = false;

			/* match any data query variables in the string */
			if (preg_match_all("/\|query_([a-zA-Z0-9_]+)\|/", $item["value"], $matches)) {
				for ($i=0; $i<sizeof($matches[1]); $i++) {
					/* keep matching variable against the data query cache until we get a negative hit */
					if (($found_negative_match == false) && ( ((!isset($data_query_cache{$matches[1][$i]})) || ($data_query_cache{$matches[1][$i]} == "")) )) {
						$found_negative_match = true;
					}
				}

				/* no negative hits found; we found our match */
				if ($found_negative_match == false) {
					return $item["value"];
				}
			}else{
				/* if there are none, then we found our match */
				return $item["value"];
			}
		}

		/* last resort: pick the last item in the list */
		return $item["value"];
	}

	return "";
}

?>
