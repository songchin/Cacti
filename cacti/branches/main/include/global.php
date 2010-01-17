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

/*
   !!! IMPORTANT !!!

   The following defaults are not to be altered.  Please refer to
   include/config.php for user configurable database settings.

*/

/* Default database settings*/
$database_type = "mysql";
$database_default = "cacti";
$database_hostname = "localhost";
$database_username = "cactiuser";
$database_password = "cactiuser";
$database_port = "3306";

/* Default session name - Session name must contain alpha characters */
$cacti_session_name = "Cacti";

/* Do not edit this line */
$config = array();

/* Default url path */
if (!isset($config['url_path'])) {
	$config['url_path'] = "";
}

/* Include configuration */
@include(dirname(__FILE__) . "/config.php");

if (isset($config["cacti_version"])) {
	die("Invalid include/config.php file detected.");
	exit;
}

/* set script memory limits */
if (isset($config['memory_limit']) && $config['memory_limit'] != '') {
	ini_set('memory_limit', $config['memory_limit']);
}else{
	ini_set('memory_limit', '512M');
}

/* Files that do not need http header information - Command line scripts */
$no_http_header_files = array(
	"cmd.php",
	"poller.php",
	"poller_commands.php",
	"script_server.php",
	"query_host_cpu.php",
	"query_host_partitions.php",
	"sql.php",
	"ss_host_cpu.php",
	"ss_host_disk.php",
	"ss_sql.php",
	"add_device.php",
	"add_graphs.php",
	"add_perms.php",
	"add_tree.php",
	"copy_user.php",
	"device_update_template.php",
	"poller_export.php",
	"poller_graphs_reapply_names.php",
	"poller_output_empty.php",
	"poller_reindex_devices.php",
	"rebuild_poller_cache.php",
	"data_query_add.php",
	"data_query_list.php",
	"data_source_remove.php",
	"device_add.php",
	"device_list.php",
	"device_template_list.php",
	"graph_add.php",
	"graph_list.php",
	"perms_add.php",
	"tree_add.php",
	"user_copy.php",
	"repair_database.php",
	"structure_rra_paths.php"
);

$colors = array();

/* this should be auto-detected, set it manually if needed */
define("CACTI_SERVER_OS", (strstr(PHP_OS, "WIN")) ? "win32" : "unix");

/* built-in snmp support */
define("PHP_SNMP_SUPPORT", function_exists("snmpget"));

/* define some path constants */
if (CACTI_SERVER_OS == "win32") {
	define("CACTI_BASE_PATH", dosPath(preg_replace("/(.*)[\/]include/", "\\1", str_replace("\\","/", dirname(__FILE__)))));
}else{
	define("CACTI_BASE_PATH", preg_replace("/(.*)[\/]include/", "\\1", str_replace("\\","/", dirname(__FILE__))));
}

define('CACTI_RRA_PATH', CACTI_BASE_PATH . '/rra');
define('CACTI_URL_PATH', $config['url_path']);

/* display ALL errors */
error_reporting(E_ALL);

/* current cacti version */
define("CACTI_VERSION", "0.8.8");
define('CACTI_WIKI_URL', "http://docs.cacti.net/reference:088:");

/* include base modules */
include(CACTI_BASE_PATH . "/lib/adodb/adodb.inc.php");
include(CACTI_BASE_PATH . "/lib/database.php");

/* check that the absolute necessary mysql PHP module is loaded  (install checks the rest), and report back if not */
if (!function_exists('mysql_data_seek')) {
	die ("\n\nRequired 'mysql' PHP extension not loaded. Check your php.ini file.\n");
}

/* connect to the database server */
db_connect_real($database_hostname, $database_username, $database_password, $database_default, $database_type, $database_port);

/* Check that the database has tables in it - can't use db_fetch_assoc because that uses read_config_option! */
$result = mysql_query("show tables from $database_default");
if(mysql_num_rows($result) == 0) {
	$database_empty = true;
} else {
	$database_empty = false;
}

/* initilize php session */
session_name($cacti_session_name);
session_start();

/* include additional modules */
include_once(CACTI_BASE_PATH . "/lib/functions.php");
include_once(CACTI_BASE_PATH . "/lib/plugins.php");
include_once(CACTI_BASE_PATH . "/include/global_constants.php");
include_once(CACTI_BASE_PATH . "/include/global_language.php");
include_once(CACTI_BASE_PATH . "/lib/log/update.php");
include_once(CACTI_BASE_PATH . "/include/global_arrays.php");
include_once(CACTI_BASE_PATH . "/include/global_settings.php");
if(!$database_empty) {
	// avoid running read_config_option against an empty DB - this isn't needed during the install process anyway
	include_once(CACTI_BASE_PATH . "/include/global_form.php");
}
include_once(CACTI_BASE_PATH . "/lib/html.php");
include_once(CACTI_BASE_PATH . "/lib/html_form.php");
include_once(CACTI_BASE_PATH . "/lib/html_utility.php");
include_once(CACTI_BASE_PATH . "/lib/html_validate.php");
include_once(CACTI_BASE_PATH . "/lib/variables.php");
include_once(CACTI_BASE_PATH . "/lib/auth.php");

api_plugin_hook('config_arrays');
api_plugin_hook('config_settings');
api_plugin_hook('config_form');

if (read_config_option('require_ssl') == 'on') {
	if (!isset($_SERVER['HTTPS']) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
		Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n\n");
		exit;
	}
}

if ((!in_array(basename($_SERVER["PHP_SELF"]), $no_http_header_files, true)) && ($_SERVER["PHP_SELF"] != "")) {
	/* Sanity Check on "Corrupt" PHP_SELF */
	if ($_SERVER["SCRIPT_NAME"] != $_SERVER["PHP_SELF"]) {
		echo "\nInvalid PHP_SELF Path \n";
		exit;
	}

	/* we don't want these pages cached */
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	/* prevent IE from silently rejects cookies sent from third party sites. */
	header('P3P: CP="CAO PSA OUR"');

	/* detect and handle get_magic_quotes */
	if (!get_magic_quotes_gpc()) {
		function addslashes_deep($value) {
			$value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
			return $value;
		}

		$_POST   = array_map('addslashes_deep', $_POST);
		$_GET    = array_map('addslashes_deep', $_GET);
		$_COOKIE = array_map('addslashes_deep', $_COOKIE);
	}

	/* make sure to start only only Cacti session at a time */
	if (!isset($_SESSION["cacti_cwd"])) {
		$_SESSION["cacti_cwd"] = CACTI_BASE_PATH;
	}else{
		if ($_SESSION["cacti_cwd"] != CACTI_BASE_PATH) {
			session_unset();
			session_destroy();
		}
	}

	updateCookieChanges();
}

/* emulate 'register_globals' = 'off' if turned on */
if ((bool)ini_get("register_globals")) {
	$not_unset = array("_GET", "_POST", "_COOKIE", "_SERVER", "_SESSION", "_ENV", "_FILES", "database_type", "database_default", "database_hostname", "database_username", "database_password", "config", "colors");

	/* Not only will array_merge give a warning if a parameter is not an array, it will
	* actually fail. So we check if HTTP_SESSION_VARS has been initialised. */
	if (!isset($_SESSION)) {
		$_SESSION = array();
	}

	/* Merge all into one extremely huge array; unset this later */
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_SESSION, $_ENV, $_FILES);

	unset($input["input"]);
	unset($input["not_unset"]);

	while (list($var,) = @each($input)) {
		if (!in_array($var, $not_unset)) {
			unset($$var);
		}
	}

	unset($input);
}


/* colors - depercated */
$colors["dark_outline"] = "454E53";
$colors["dark_bar"] = "AEB4B7";
$colors["panel"] = "E5E5E5";
$colors["panel_text"] = "000000";
$colors["panel_link"] = "000000";
$colors["light"] = "F5F5F5";
$colors["alternate"] = "E7E9F2";
$colors["panel_dark"] = "C5C5C5";

$colors["header"] = "00438C";
$colors["header_panel"] = "6d88ad";
$colors["header_text"] = "ffffff";
$colors["form_background_dark"] = "E1E1E1";

$colors["form_alternate1"] = "F5F5F5";
$colors["form_alternate2"] = "E5E5E5";


/* dosPath - converts a path with spaces to a dos 8.3 path
    @arg $path - the path with spaces
    @returns (un)modified path */
function dosPath($path) {
	$path = str_replace("\\", "/", $path);
	if (substr_count($path, " ") || strlen($path) > 11) {
		$path_parts = pathinfo($path);
		$dir  = $path_parts['dirname'];
		$base = $path_parts['basename'];
		$ext  = (isset($path_parts['extension']) ? $path_parts['extension']:"");
		if (isset($path_parts['filename'])) {
			$file = $path_parts['filename'];
		}else{
			$file = str_replace("." . $ext, "", $base);
		}
		$npath = "";

		if (is_dir($dir)) {
			/* the pathinfo replaces the backslash if it's a base path */
			$dir   = str_replace("\\", "", $dir);
			$odir  = "";
			$parts = explode("/", $dir);

			foreach($parts as $part) {
				if (strlen($part) > 8) {
					$odir .= "/" . $part;
					$part = substr($part, 0, 6);

					for ($i = 0; $i < 10; $i++) {
						$test = $npath . (strlen($npath) ? "/":"") . $part . "~" . $i;

						if (is_dir($test) && is_dir($odir)) {
							if (scandir($test) == scandir($odir)) {
								$npath .= (strlen($npath) ? "/":"") . $part . "~" . $i;
								break;
							}
						}
					}
				}else{
					$npath .= (strlen($npath) ? "/":"") . $part;
				}
			}

			if (strlen($file) > 8) {
				$part = substr($file, 0, 6);

				for ($i = 1; $i < 10; $i++) {
					$test = $npath . (strlen($npath) ? "/":"") . $part . "~" . $i . (strlen($ext) ? "." . $ext:"");

					if (is_file($test) || is_dir($test)) {
						$npath = $test;
						break;
					}
				}
			}else{
				$npath .= (strlen($npath) ? "/":"") . $base;
			}

			return $npath;
		}else{
			return $path;
		}
	}else{
		return $path;
	}
}
