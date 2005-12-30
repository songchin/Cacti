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

/* Check for config.php error otherwise */
if (file_exists("./include/config.php") == false) {
	print "<html><body><font size=+1 color=red><b>" . _("Cacti Configuration Error: include/config.php file was not found.  Please make sure that you have renamed include/config.php.dist to include/config.php") . "</b></font></body></html>\n";
	exit;
}
require(dirname(__FILE__) . "/config.php");

$colors = array();

/* this should be auto-detected, set it manually if needed */
define("CACTI_SERVER_OS", (strstr(PHP_OS, "WIN")) ? "win32" : "unix");

/* used for includes */
define("CACTI_BASE_PATH", str_replace(DIRECTORY_SEPARATOR . "include", "", dirname(__FILE__)));

/* current cacti version */
define("CACTI_VERSION", "0.9-dev");

/* colors */
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


/* Logging include */
require(CACTI_BASE_PATH . "/lib/log/log_action.php");

/* includes for database operation */
require(CACTI_BASE_PATH . "/lib/adodb/adodb.inc.php");
require(CACTI_BASE_PATH . "/lib/sys/database.php");
require(CACTI_BASE_PATH . "/lib/sys/database_utility.php");

/* connect to the database server */
db_connect_real($database_hostname, $database_username, $database_password, $database_default, $database_type);

/* global functions include */
require(CACTI_BASE_PATH . "/lib/functions.php"); // deprecated
require(CACTI_BASE_PATH . "/lib/sys/array.php");
require(CACTI_BASE_PATH . "/lib/sys/config.php");
require(CACTI_BASE_PATH . "/lib/sys/message.php");
require(CACTI_BASE_PATH . "/lib/sys/session.php");
require(CACTI_BASE_PATH . "/lib/sys/string.php");
require(CACTI_BASE_PATH . "/lib/sys/validate.php");

/* User and auth include */
require(CACTI_BASE_PATH . "/lib/user/user_info.php");

/* Files that do not need http header information - Command line scripts */
$no_http_header_files = array(
	"poller.php",
	"cmd.php",
	"query_host_cpu.php",
	"query_host_partitions.php",
	"sql.php",
	"ss_host_cpu.php",
	"ss_host_disk.php",
	"ss_sql.php"
	);

if ((!in_array(basename($_SERVER["PHP_SELF"]), $no_http_header_files, true)) && ($_SERVER["PHP_SELF"] != "")) {
	require(CACTI_BASE_PATH . "/lib/sys/form.php");
	require(CACTI_BASE_PATH . "/lib/sys/html.php");
	require(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require(CACTI_BASE_PATH . "/lib/sys/html_utility.php");
	require(CACTI_BASE_PATH . "/lib/sys/html_box.php");

	/* load color scheme from theme file.  Load the default first and then overwrite as required with theme */
	require(CACTI_BASE_PATH . "/include/global_colors.php");
	require(html_theme_color_scheme());

	/* we don't want these pages cached */
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-type: text/html; charset=UTF-8");

	/* initilize php session */
	if(ini_get('session.auto_start') != 1) {
		session_start();
	}

	/* detect and handle get_magic_quotes */
	if (!get_magic_quotes_gpc()) {
		function addslashes_deep($value) {
			$value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
			return $value;
		}

		$_POST = array_map('addslashes_deep', $_POST);
		$_GET = array_map('addslashes_deep', $_GET);
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

	/* this code handles dropdown boxes that automatically redirect the user upon select while
	 * retaining all current form values */
	if (isset($_REQUEST["cacti_js_dropdown_redirect"])) {
		init_post_field_cache();

		header("Location: " . $_REQUEST["cacti_js_dropdown_redirect"]);
		exit;
	}
}

/* Contants and Variable includes -- note that the includes must fall below the session_start() call
 * for now because they rely on read_config_option() */
require(CACTI_BASE_PATH . "/include/global_constants.php");
require(CACTI_BASE_PATH . "/include/global_arrays.php");
require(CACTI_BASE_PATH . "/include/global_settings.php");
require(CACTI_BASE_PATH . "/include/global_form.php");

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

/* display ALL errors */
error_reporting(E_ALL);

/* cacti language support */
$lang2locale = array(
	"en"=>"us_EN.UTF-8",
	"ja"=>"ja_JP.UTF-8",
	"fr"=>"fr_FR.iso-8859-1",
	"sv"=>"sv_SE.iso-8859-1",
	"es"=>"es_ES.iso-8859-1",
	"bg"=>"bg_BG.cp1251"
	);

$locale = $lang2locale[$cacti_lang];
setlocale(LC_ALL, $locale);
putenv("LC_ALL=" . $locale);
putenv("LANG=" . $cacti_lang);

/* determine whether or not we need to emulate gettext */
if (!function_exists("_")) {
	require(CACTI_BASE_PATH . "/include/gettext/streams.php");
	require(CACTI_BASE_PATH . "/include/gettext/gettext.php");

	$locale_path = $config["base_path"] . "/locales/" . $cacti_lang . "/LC_MESSAGES/" . "cacti.mo";
	if ((!file_exists($locale_path)) && ($cacti_lang != "en")) {
		die("Cacti language locale file not found.  Please locate your language file and then you can continue.");
	}

	if ($cacti_lang != "en") {
		$input = new FileReader($locale_path);
		$l10n = new gettext_reader($input);

		/* create standard wrapers, so gettext functions can work */
		function _($text) {
			global $l10n;
			return $l10n->translate($text);
		}

		function _ngettext($single, $plural, $number) {
			global $l10n;
			return $l10n->ngettext($single, $plural, $number);
		}
	} else {
		function _($text) {
			return $text;
		}

		function _ngettext($single, $plural, $number) {
			if ($number == 1) {
				return $single;
			} else {
				return $plural;
			}
		}
	}
} else {
	bindtextdomain("cacti", CACTI_BASE_PATH . "/locales");
	textdomain("cacti");
	bind_textdomain_codeset ("cactid", "UTF-8");
}

?>
