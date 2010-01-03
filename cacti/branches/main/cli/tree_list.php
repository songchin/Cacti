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
include_once(CACTI_BASE_PATH.'/lib/api_tree.php');
include_once(CACTI_BASE_PATH.'/lib/tree.php');

/* process calling arguments */
$parms = $_SERVER["argv"];
$me = array_shift($parms);

if (sizeof($parms)) {
	$sortMethods = array('manual' => 1, 'alpha' => 2, 'natural' => 4, 'numeric' => 3);
	$nodeTypes   = array('header' => 1, 'graph' => 2, 'device' => 3);

	$quietMode      = FALSE;
	$displayTrees   = FALSE;
	$displayNodes   = FALSE;
	$displayRRAs    = FALSE;
	$displayGraphs  = FALSE;

	foreach($parms as $parameter) {
		@list($arg, $value) = @explode("=", $parameter);

		switch ($arg) {
			case "-d":
			case "--debug":			$debug 							= TRUE; 		break;

			# more parameters
			case "--tree-id":		$treeId 						= trim($value);	break;
			case "--node-type":		$nodeType 						= trim($value);	break;
			case "--parent-node":	$parentNode						= trim($value);	break;

			# various list options
			case "--list-trees":	$displayTrees 					= TRUE;			break;
			case "--list-nodes":	$displayNodes 					= TRUE;			break;
			case "--list-rras":		$displayRRAs 					= TRUE;			break;

			# miscellaneous
			case "-V":
			case "-H":
			case "--help":
			case "--version":		display_help($me);								exit(0);
			case "--quiet":			$quietMode 						= TRUE;			break;
			default:				echo __("ERROR: Invalid Argument: (%s)", $arg) . "\n\n"; display_help($me); exit(1);
		}
	}

	if ($displayTrees) {
		displayTrees($quietMode);
		exit(0);
	}

	if ($displayNodes) {
		if (!isset($treeId)) {
			echo __("ERROR: You must supply a tree_id before you can list its nodes") . "\n";
			echo __("Try --list-trees") . "\n";
			exit(1);
		}

		if (!isset($nodeType)) $nodeType = '';
		if (!isset($parentNode)) $parentNode = '';
		displayTreeNodes($treeId, $nodeType, $parentNode, $quietMode);
		exit(0);
	}

	if ($displayRRAs) {
		displayRRAs($quietMode);
		exit(0);
	}
} else {
	display_help($me);
	exit(0);
}

function display_help($me) {
	echo __("List Tree Script 1.0") . ", " . __("Copyright 2004-2010 - The Cacti Group") . "\n";
	echo __("A simple command line utility to list trees in Cacti") . "\n\n";
	echo __("usage: ") . $me . "\n";
	echo __("List Options:") . "\n";
	echo "   --list-trees" . "\n";
	echo "   --list-nodes  --tree-id=[ID]  [--node-type=[header|device|graph]]" . "\n";
	echo "   --list-rras" . "\n";
	echo "   --quiet       " . __("batch mode value return") . "\n\n";
}
