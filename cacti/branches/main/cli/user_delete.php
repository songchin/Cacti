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

include(dirname(__FILE__)."/../include/global.php");
include_once(CACTI_BASE_PATH."/lib/auth.php");

if (empty($_SERVER["argv"][2])) {
	echo "\n";
	echo __("It is highly recommended that you use the web interface to copy users as this script will only copy Local Cacti users.") . "\n\n";
	echo __("Syntax:\n php copy_cacti_user.php <template user> <new user>") . "\n\n";
	exit;
}

$no_http_headers = true;

$template_user = $_SERVER["argv"][1];
$new_user = $_SERVER["argv"][2];

echo "\n";
echo __("It is highly recommended that you use the web interface to copy users as this script will only copy Local Cacti users.") . "\n\n";
echo __("Cacti User Copy Utility") . "\n";
printf(__("Template User: %s\n"), $template_user);
printf(__("New User: %s"), $new_user);

/* Check that user exists */
$user_auth = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $template_user . "' AND realm = 0");
if (! isset($user_auth)) {
	die("Error: Template user does not exist!") . "\n\n";
}

echo __("Copying User...") . "\n";

if (user_copy($template_user, $new_user) === false) {
	die("Error: User not copied!") . "\n\n";
}

$user_auth = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $new_user . "' AND realm = 0");
if (! isset($user_auth)) {
	die("Error: User not copied!") . "\n\n";
}

echo __("User copied...") . "\n";
