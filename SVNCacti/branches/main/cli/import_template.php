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
include_once("../lib/import.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$filename = "";
	$import_custom_rra_settings = false;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "--filename":
				$filename = trim($value);

				break;
			case "--with-rras":
				$import_custom_rra_settings = true;

				break;
			case "--version":
			case "-V":
			case "-H":
			case "--help":
				display_help($me);
				exit(0);
			default:
				printf(__("ERROR: Invalid Argument: (%s)\n\n"), $arg);
				exit(1);
		}
	}

	if($filename != "") {
		if(file_exists($filename) && is_readable($filename)) {
			$fp = fopen($filename,"r");
			$xml_data = fread($fp,filesize($filename));
			fclose($fp);

			printf(__("Read %d bytes of XML data\n"), strlen($xml_data));

			$debug_data = import_xml_data($xml_data, $import_custom_rra_settings);

			while (list($type, $type_array) = each($debug_data)) {
				print "** " . $hash_type_names[$type] . "\n";

				while (list($index, $vals) = each($type_array)) {
					if ($vals["result"] == "success") {
						$result_text = __(" [success]");
					}else{
						$result_text = __(" [fail]");
					}

					if ($vals["type"] == "update") {
						$type_text = __(" [update]");
					}else{
						$type_text = __(" [new]");
					}
					echo "   $result_text " . $vals["title"] . " $type_text" . "\n";

					$dep_text = ""; $errors = false;
					if ((isset($vals["dep"])) && (sizeof($vals["dep"]) > 0)) {
						while (list($dep_hash, $dep_status) = each($vals["dep"])) {
							if ($dep_status == "met") {
								$dep_status_text = __("Found Dependency: ");
							} else {
								$dep_status_text = __("Unmet Dependency: ");
								$errors = true;
							}

							$dep_text .= "    + $dep_status_text " . hash_to_friendly_name($dep_hash, true) . "\n";
						}
					}

					/* dependency errors need to be reported */
					if ($errors) {
						echo $dep_text;
						exit(-1);
					}else{
						exit(0);
					}
				}
			}
		} else {
			printf(__("ERROR: file %s is not readable, or does not exist\n\n"), $filename);
			exit(1);
		}
	} else {
		echo __("ERROR: no filename specified") . "\n\n";
		display_help($me);
		exit(1);
	}
} else {
	echo __("ERROR: no parameters given") . "\n\n";
	display_help($me);
	exit(1);
}

function display_help($me) {
	echo __("Add Graphs Script 1.0") . ", " . __("Copyright 2004-2009 - The Cacti Group") . "\n";
	echo __("A simple command line utility to import a Template into Cacti") . "\n\n";
	echo __("usage: ") . $me . " --filename=[filename] [--with-rras] [-h] [--help] [-v] [--version]\n";
	echo __("Required:") . "\n";
	echo "   --filename     " . __("the name of the XML file to import") . "\n";
	echo __("Optional:") . "\n";
	echo "   --with-rras    " . __("also import custom RRA definitions from the template") . "\n";
	echo "   -v --version   " . __("Display this help message") . "\n";
	echo "   -h --help      " . __("Display this help message") . "\n";
}
