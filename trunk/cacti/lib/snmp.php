<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

define ("REGEXP_SNMP_TRIM", "(hex|counter(32|64)|gauge|gauge(32|64)|float|ipaddress|string|integer):");

function data_query($host_id, $snmp_query_id) {
	$type_id = db_fetch_cell("select data_input.type_id from snmp_query,data_input where snmp_query.data_input_id=data_input.id and snmp_query.id=$snmp_query_id");
	
	if ($type_id == "3") {
		return query_snmp_host($host_id, $snmp_query_id);
	}elseif ($type_id == "4") {
		return query_script_host($host_id, $snmp_query_id);
	}
}

function get_data_query_array($snmp_query_id) {
	include_once ("xml_functions.php");
	include ("config.php");
	print "<pre>";print_r($_SESSION);print "</pre>";
	$xml_file_path = db_fetch_cell("select xml_path from snmp_query where id=$snmp_query_id");
	$xml_file_path = str_replace("<path_cacti>", $paths["cacti"], $xml_file_path);
	print "!!!!!!!!!!!!!!XML PARSE: $xml_file_path!!!!!!!!!!!!!!!!<br>";
	if (!file_exists($xml_file_path)) {
		return false;
	}
	
	$data = implode("",file($xml_file_path));
	return xml2array($data);
}

function query_script_host($host_id, $snmp_query_id) {
	include ("config.php");
	
	$script_queries = get_data_query_array($snmp_query_id);
	
	if (sizeof($script_queries) == 0) {
		return false;
	}
	
	/* get a complete path for out target script */
	$script_path = str_replace("|path_cacti|", $paths["cacti"], $script_queries["script_path"]);
	
	/* fetch specified index at specified OID */
	$script_index_array = exec_into_array($script_path . " " . $script_queries["arg_index"]);
	
	db_execute("delete from host_snmp_cache where host_id=$host_id and snmp_query_id=$snmp_query_id");
	
	while (list($field_name, $field_array) = each($script_queries["fields"][0])) {
		$field_array = $field_array[0];
		
		if (($field_array["method"] == "walk") && ($field_array["direction"] == "input")) {
			$script_data_array = exec_into_array($script_path . " " . $script_queries["arg_query"] . " " . $field_array["query_name"]);
			
			for ($i=0;($i<sizeof($script_data_array));$i++) {
				if (preg_match("/(.*)" . preg_quote($script_queries["output_delimeter"]) . "(.*)/", $script_data_array[$i], $matches)) {
					$script_index = $matches[1];
					$field_value = $matches[2];
					
					db_execute("replace into host_snmp_cache 
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','$field_value','$script_index','')");
				}
			}
		}
	}
	
	return true;
}

function query_snmp_host($host_id, $snmp_query_id) {
	$host = db_fetch_row("select management_ip,snmp_community,snmp_version,snmp_username,snmp_password from host where id=$host_id");
	
	$snmp_queries = get_data_query_array($snmp_query_id);
	
	if ((empty($host["management_ip"])) || (sizeof($snmp_queries) == 0)) {
		return false;
	}
	
	/* fetch specified index at specified OID */
	$snmp_index = cacti_snmp_walk($host["management_ip"], $host["snmp_community"], $snmp_queries["oid_index"], $host["snmp_version"], $host["snmp_username"], $host["snmp_password"]);
	
	/* no data found; get out */
	if (!$snmp_index) {
		return false;
	}
	
	db_execute("delete from host_snmp_cache where host_id=$host_id and snmp_query_id=$snmp_query_id");
	
	while (list($field_name, $field_array) = each($snmp_queries["fields"][0])) {
		$field_array = $field_array[0];
		
		if (($field_array["method"] == "get") && ($field_array["direction"] == "input")) {
			if ($field_array["source"] == "value") {
				for ($i=0;($i<sizeof($snmp_index));$i++) {
					$oid = $field_array["oid"] .  "." . $snmp_index[$i]["value"];
					
					$value = cacti_snmp_get($host["management_ip"], $host["snmp_community"], $oid, $host["snmp_version"], $host["snmp_username"], $host["snmp_password"]);
					
					db_execute("replace into host_snmp_cache 
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','$value'," . $snmp_index[$i]["value"] . ",'$oid')");
				}
			}
		}elseif (($field_array["method"] == "walk") && ($field_array["direction"] == "input")) {
			$snmp_data = cacti_snmp_walk($host["management_ip"], $host["snmp_community"], $field_array["oid"], $host["snmp_version"], $host["snmp_username"], $host["snmp_password"]);
			
			if ($field_array["source"] == "value") {
				for ($i=0;($i<sizeof($snmp_data));$i++) {
					$snmp_index = ereg_replace('.*\.([0-9]+)$', "\\1", $snmp_data[$i]["oid"]);
					$oid = $field_array["oid"] . ".$snmp_index";
					
					db_execute("replace into host_snmp_cache 
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','" . $snmp_data[$i]["value"] . "',$snmp_index,'$oid')");
				}
			}elseif (ereg("^OID/REGEXP:", $field_array["source"])) {
				for ($i=0;($i<sizeof($snmp_data));$i++) {
					$value = ereg_replace(ereg_replace("^OID/REGEXP:", "", $field_array["source"]), "\\1", $snmp_data[$i]["oid"]);
					$snmp_index = $snmp_data[$i]["value"];
					$oid = $field_array["oid"] .  "." . $value;
					
					db_execute("replace into host_snmp_cache 
						(host_id,snmp_query_id,field_name,field_value,snmp_index,oid)
						values ($host_id,$snmp_query_id,'$field_name','$value',$snmp_index,'$oid')");
				}
			}
		}
	}
	
	return true;
}

function cacti_snmp_get($hostname, $community, $oid, $version, $username, $password) {
	include ("config.php");
	include_once ("functions.php");
	
	if ($config["php_snmp_support"] == true) {
		/* make sure snmp* is verbose so we can see what types of data
		we are getting back */
		snmp_set_quick_print(0);
		
		$snmp_value = @snmpget($hostname, $community, $oid);
	}else{
		if ($version == "1") {
			$snmp_auth = "-c \"$community\""; /* v1/v2 - community string */
		}elseif ($version == "2") {
			$snmp_auth = "-c \"$community\""; /* v1/v2 - community string */
			$version = "2c"; /* ucd/net snmp prefers this over '2' */
		}elseif ($version == "3") {
			$snmp_auth = "-u $username -X $password"; /* v3 - username/password */
		}
		
		/* no valid snmp version has been set, get out */
		if (empty($snmp_auth)) { return; }
		
		if (read_config_option("smnp_version") == "ucd-snmp") {
			$snmp_value = exec(read_config_option("path_snmpget") . " $hostname $snmp_auth -v $version $oid");
		}elseif (read_config_option("smnp_version") == "net-snmp") {
			$snmp_value = exec(read_config_option("path_snmpget") . " $snmp_auth -v $version $hostname $oid");
		}
	}
	
	$snmp_value = format_snmp_string($snmp_value);
	
	return $snmp_value;
}

function cacti_snmp_walk($hostname, $community, $oid, $version, $username, $password) {
	include ("config.php");
	include_once ("functions.php");
	
	$snmp_array = array();
	$temp_array = array();
	
	if ($config["php_snmp_support"] == true) {
		$temp_array = @snmpwalkoid($hostname, $community, $oid);
		
		$o = 0;
		for (@reset($temp_array); $i = @key($temp_array); next($temp_array)) {
			$snmp_array[$o]["oid"] = ereg_replace("^\.", "", $i); 
			$snmp_array[$o]["value"] = format_snmp_string($temp_array[$i]);
			$o++;
		}
	}else{
		if ($version == "1") {
			$snmp_auth = "-c \"$community\""; /* v1/v2 - community string */
		}elseif ($version == "2") {
			$snmp_auth = "-c \"$community\""; /* v1/v2 - community string */
			$version = "2c"; /* ucd/net snmp prefers this over '2' */
		}elseif ($version == "3") {
			$snmp_auth = "-u $username -X $password"; /* v3 - username/password */
		}
		
		if (read_config_option("smnp_version") == "ucd-snmp") {
			$temp_array = exec_into_array(read_config_option("path_snmpwalk") . " $hostname $snmp_auth -v $version $oid");
		}elseif (read_config_option("smnp_version") == "net-snmp") {
			$temp_array = exec_into_array(read_config_option("path_snmpwalk") . " $snmp_auth -v $version $hostname $oid");
		}
		
		if (sizeof($temp_array) == 0) {
			return 0;
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
	}
	
	$string = eregi_replace(REGEXP_SNMP_TRIM, "", $string);
	
	return trim($string);
}

?>
