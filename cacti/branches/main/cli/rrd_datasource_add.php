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
/* required includes */
include(dirname(__FILE__)."/../include/global.php");
require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
include_once(CACTI_BASE_PATH."/lib/api_rrd.php");

/* verify required PHP extension */
if (!extension_loaded("DOM")) {
	print(__("Extension 'DOM' is missing. This extension requires PHP Version 5.") . "\n");
	exit;
}


/* process calling arguments */
$parms 		= $_SERVER["argv"];
$me 		= array_shift($parms);
$debug		= FALSE;	# no debug mode
$delimiter 	= ':';		# default delimiter for separating ds arguments, if not given by user
$separator 	= ';';		# default delimiter for separating multiple ds', if not given by user

if (sizeof($parms)) {
	foreach ($parms as $parameter) {
		@ list ($arg, $value) = @ explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":				$debug 						= TRUE; 		break;
			case "--delim":				$delimiter					= trim($value);	break;
			case "--sep":				$separator					= trim($value);	break;
			case "--data-template-id" :	$data_template_id 			= trim($value);	break;
			case "--data-source-id" :	$data_source_id 			= trim($value);	break;
			case "--rrd":				$rrd 						= trim($value);	break;
			case "--ds":				$ds_parm					= trim($value); break;
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	/* Now we either have
	 * - a data template id or
	 * - a data source id or
	 * - a plain rrd file name
	 * At the end, we need an array of file names for processing
	 * rrdtool dump - modify XML - rrdtool restore
	 */
	$file_array = array();
	if (isset($data_template_id) && ($data_template_id > 0)) {
		/* get file array for a given data template id */
		$file_array = get_data_template_rrd($data_template_id);
	}elseif (isset($data_source_id) && ($data_source_id > 0)) {
		/* get file array (single element) for a given data source id */
		$file_array = get_data_source_rrd($data_source_id);
	}elseif (isset($rrd)) {
		if (!file_exists($rrd)) {
			echo __("ERROR: You must supply a valid rrd file name.") . "\n";
			echo __("Found:") . " $rrd\n";
			exit (1);
		}else {
			$file_array[] = $rrd;
		}
	}
	/* verify if at least one valid rrd file was given */
	if (!sizeof($file_array)) {
		echo __("ERROR: No valid rrd file name found.") . "\n";
		echo __("Please either supply %s or %s or %s", "--data-template-id", "--data-source-id", "--rrd") . "\n";
		exit (1);
	}
	#print_r($file_array);

	/* we may have multiple ds to append
	 * so let's first get the array of ds'
	 * $ds_array = array(
	 * 		0 => array(
	 * 			'name' => name
	 * 			'type' => type
	 * 			'heartbeat => heartbeat
	 * 			'min' => min
	 * 			'max' => max
	 * 			)
	 * 		1 => array(
	 * 			...
	 * 			)
	 * 		...
	 * 		)
	 */
	$ds_parm_array = explode($separator, $ds_parm);
	if (sizeof($ds_parm_array)) {
		foreach($ds_parm_array as $key => $value) {
			/* verify the given parameters for the new data source
			 * $min,$max are omitted for COMPUTE DS, so use @ to suppress warnings */
			@list($name, $type, $heartbeat, $min, $max) = explode(":", $value);
			/* name must be string, len < 20, characters [a-zA-Z0-9_] */
			if ((!preg_match('/[a-zA-Z0-9_]/', $name) || (strlen($name) > 19))) {
				echo __("ERROR: You must supply a valid data source name.") . "\n";
				echo __("Found:") . " $name\n";
				exit (1);
			}else {
				$ds_array[$key]['name'] = $name;
			}

			switch($type) {
				/* type must be either [GAUGE|COUNTER|DERIVE|ABSOLUTE] ... */
				case $data_source_types[DATA_SOURCE_TYPE_GAUGE]:
				case $data_source_types[DATA_SOURCE_TYPE_COUNTER]:
				case $data_source_types[DATA_SOURCE_TYPE_DERIVE]:
				case $data_source_types[DATA_SOURCE_TYPE_ABSOLUTE]:
					$ds_array[$key]['type'] = $type;

					/* verify heartbeat */
					$ds_array[$key]['heartbeat'] = verify_heartbeat($heartbeat);
					if($ds_array[$key]['heartbeat'] === false) exit(1);

					/* verify min, max */
					$ds_array[$key]['min'] = verify_min_max($min);
					if($ds_array[$key]['min'] === false) exit(1);
					$ds_array[$key]['max'] = verify_min_max($max);
					if($ds_array[$key]['max'] === false) exit(1);

					break;
				case $data_source_types[DATA_SOURCE_TYPE_COMPUTE]:
					$ds_array[$key]['type'] = $type;
					/* verify CDEF RPN ??? */
					$ds_array[$key]['cdef'] = $heartbeat;

					break;
				default:
					echo __("ERROR: You must supply a valid data source type.") . "\n";
					echo __("Found:") . " $type\n";
					exit (1);
			}
		}
	}

	#print_r($ds_array);

	$rc= api_rrd_datasource_add($file_array, $ds_array, $debug);
	if (isset($rc["err_msg"])) {
		print $rc["err_msg"] . "\n\n";
		display_help($me);
		exit(1);
	}

	#exit($rc);
}else{
	display_help($me);
	exit(0);
}

function verify_heartbeat($heartbeat) {
	/* heartbeat must be numeric; warning when it differs from other heartbeats of same rrd? */
	if (!preg_match('/[0-9]/', $heartbeat)) {
		echo __("ERROR: You must supply a valid heartbeat.") . "\n";
		echo __("Found:") . " $heartbeat\n";
		return false;
	}
	return $heartbeat;
}

function verify_min_max($value) {
	/* min,max must be numeric or ["NaN"|"U"] for no min,max */
	if (preg_match('/^[0-9]+$/', $value)) {
		return $value;
	}elseif (preg_match('/(NaN|U)/', $value)) {
		return "NaN";	# "U" is not valid in the XML, only on the UI!
	}else {
		echo __("ERROR: You must supply a valid [minimum|maximum] value.") . "\n";
		echo __("Found:") . " $value\n";
		return false;
	}

}


function display_help($me) {
	echo "Add Datasource to RRD File Script 1.0" . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to add a new datasource to an existing RRD file") . "\n\n";
	echo __("usage: ") . $me . " --ds= [--data-template-id=] [--data-source-id=] [--rrd=]\n";
	echo "       [--delim=] [--sep=] [-d]\n\n";
	echo __("Required:") . "\n";
	echo "   --ds                    " . __("specifies the datasource to be added.") . "\n";
	echo "                           " . __("Format is 'name:type:heartbeat:min:max [;name:type:heartbeat:min:max ...]'") . "\n";
	echo "                           " . __("For %s, use 'name:type:cdef_rpn'", $data_source_types[DATA_SOURCE_TYPE_COMPUTE]) . "\n";
	echo __("One of [%s|%s|%s] must be given.", '--data-template-id', '--data-source-id', '--rrd') . "\n";
	echo __("Write permissions to the files is required.") . "\n";
	echo "   --data-template-id      " . __("Id of a data-template.") . " " . __("All related rrd files will be modified") . "\n";
	echo "   --data-source-id        " . __("Id of a data-source.") . " " . __("The related rrd file will be modified") . "\n";
	echo "   --rrd                   " . __("RRD file name.") . " " . __("The related rrd file will be modified") . "\n";
	echo __("Optional:") . "\n";
	echo "   --delim                 " . __("Delimiter to separate the --ds parameters") . " " . __("Defaults to '%s'", ":") . "\n";
	echo "   --sep	                 " . __("Separator for multiple DS parameters") . __("Defaults to '%s'", ";") .  "\n";
	echo "   --debug, -d             " . __("Debug Mode, no updates made, but printing the SQL for updates") . "\n";
	echo __("Examples:") . "\n";
	echo "   php -q " . $me . " --ds='temp:GAUGE:600:0:100' --rrd='rra/system_temperature.rrd'\n";
	echo "   " . __("adds a 'temp' datasource to the given rrd file") . "\n";
	echo "   php -q " . $me . " --ds='temp:GAUGE:600:0:100, fan:GAUGE:600:0:10000' --data-template-id=1\n";
	echo "   " . __("adds a 'temp' and a 'fan' datasource to all rrd files related to data-template-id 1") . "\n";
	echo "   php -q " . $me . " --ds='sum:COMPUTE:traffic_in,traffic_out,+' --data-source-id=8\n";
	echo "   " . __("adds a COMPUTE datasource to the given rrd file") . "\n";
}

