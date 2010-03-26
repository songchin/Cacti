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

$no_http_headers = true;

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/api_automation_tools.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);
$delimiter 	= ',';		# default delimiter, if not given by user
$quietMode 	= FALSE;	# be verbose by default
$perm	 	= array();

if (sizeof($parms) == 0) {
	display_help($me);

	exit(1);
}else{
	$quietMode				= FALSE;
	$displayGroups			= FALSE;
	$displayUsers			= FALSE;
	$displayTrees			= FALSE;
	$displayRealms			= FALSE;
	$displayPerms			= FALSE;


	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "--user-id":		$perm["user_id"]				= trim($value);	break;
			case "--realm-id":		$perm["realm_id"]				= trim($value);	break;
			case "--item-type":		$perm["item_type"]				= trim($value);	break;
			case "--item-id":		$perm["item_id"]				= trim($value);	break;
			case "--list-groups":	$displayGroups 					= TRUE;	break;
			case "--list-users":	$displayUsers 					= TRUE;	break;
			case "--list-trees":	$displayTrees 					= TRUE;	break;
			case "--list-realms":	$displayRealms 					= TRUE;	break;
			case "--list-perms":	$displayPerms 					= TRUE;	break;
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode = TRUE;								break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	if ($displayGroups) {
		displayGroups($quietMode);
		exit(1);
	}

	if ($displayUsers) {
		displayUsers($quietMode);
		exit(1);
	}

	if ($displayTrees) {
		displayTrees($quietMode);
		exit(1);
	}

	if ($displayRealms) {
		displayRealms($perm, $quietMode);
		exit(1);
	}


	if ($displayPerms) {

		# verify the parameters given, return user_id as array of verified userids
		$verify = verifyPermissions($perm, $delimiter, true);
		if (isset($verify["err_msg"])) {
			print $verify["err_msg"] . "\n\n";
			display_help($me);
			exit(1);
		}

		displayPerms($perm, $quietMode);
		exit(1);
	}


}

function display_help($me) {
	echo "List Permissions Script 1.0" . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list permissions in Cacti") . "\n\n";
	echo __("usage: ") . $me . "  \n";
	echo __("List Options:") . "\n";
	echo "   --list-groups\n";
	echo "   --list-users\n";
	echo "   --list-trees\n";
	echo "   --list-realms    [--user-id=]  [--realm-id=]\n";
	echo "   --list-perms     [--user-id=]  [--item-id=]  [--item-type=[graph|tree|device|graph_template]]\n";
	echo "   --quiet          " . __("batch mode value return") . "\n\n";
}
