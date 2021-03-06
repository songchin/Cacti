#!/usr/bin/php -q
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

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$quietMode = FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
		case "-d":
			$debug = TRUE;

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
			echo __("ERROR: Invalid Argument: (") . $arg .")\n\n";
			display_help($me);
			exit(1);
		}
	}

	/*
	 * handle display options
	 */
	displayHostTemplates(getHostTemplates(), $quietMode);
	exit(0);

}else{
	displayHostTemplates(getHostTemplates(), false);
	exit(0);
}

function display_help($me) {
	echo "List Device Template Script 1.0" . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list device templates in Cacti") . "\n\n";
	echo __("usage: ") . $me . " php -q device_template_list.php\n";
	echo __("Optional:") . "\n";
	echo "   --quiet  " . __("batch mode value return") . "\n\n";
}
