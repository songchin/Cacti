<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

$no_http_headers = true;

include("../include/global.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

global $debug;

$debug = FALSE;
$size  = 300000;

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "-d":
	case "--debug":
		$debug = TRUE;
		break;
	case "-s":
	case "--size":
		$size = $value;
		break;
	case "-h":
	case "-v":
	case "-V":
	case "--version":
	case "--help":
		display_help();
		exit;
	default:
		print "ERROR: Invalid Parameter " . $parameter . "\n\n";
		display_help();
		exit;
	}
}
echo "Converting All Non-Memory Cacti Database Tables to Innodb with Less than '$size' Records\n";

$tables = db_fetch_assoc("SHOW TABLE STATUS");

if (sizeof($tables)) {
foreach($tables AS $table) {
	if ($table["Engine"] == "MyISAM") {
		if ($table["Rows"] < $size) {
			echo "Converting Table -> '" . $table['Name'] . "'";
			$status = db_execute("ALTER TABLE " . $table['Name'] . " ENGINE=Innodb");
			echo ($status == 0 ? " Failed" : " Successful") . "\n";
		}else{
			echo "Skipping Table -> '" . $table['Name'] . " too many rows '" . $table["Rows"] . "'\n";
		}
	}else{
		echo "Skipping Table ->'" . $table['Name'] . "\n";
	}
}
}

/*	display_help - displays the usage of the function */
function display_help () {
	print "Cacti Database Conversion Tool v1.0, Copyright 2010 - The Cacti Group\n\n";
	print "usage: repair_database.php [-d] [-h] [--form] [--help] [-v] [-V] [--version]\n\n";
	print "-d | --debug     - Display verbose output during execution\n";
	print "-s | --size=N    - The largest table size in records to convert\n";
	print "-v -V --version  - Display this help message\n";
	print "-h --help        - display this help message\n";
}
?>