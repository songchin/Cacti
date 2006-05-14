<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

	$locale_path = CACTI_BASE_PATH . "/locales/" . $cacti_lang . "/LC_MESSAGES/" . "cacti.mo";
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