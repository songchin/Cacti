<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/* default localization of Cacti */
$cacti_locale = "en";
$cacti_country = "us";


/* an array that will contains all textdomains being in use. */ 
$cacti_textdomains = array();

/* get a list of locale settings */
$lang2locale = get_list_of_locales();


/* determine whether or not we can support the language */
/* user requests another language */
if (isset($_GET['language']) && isset($lang2locale[$_GET['language']])) {
	$cacti_locale = $_GET['language'];
	$cacti_country = $lang2locale[$_GET['language']]['country'];
	$_SESSION['language'] = $cacti_locale;
	
/* language definition stored in the SESSION */
}elseif (isset($_SESSION['language']) && isset($lang2locale[$_SESSION['language']])){
	$cacti_locale = $_SESSION['language'];
	$cacti_country = $lang2locale[$_SESSION['language']]['country'];

/* detect browser settings if user did not setup language via GUI */
}elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$accepted = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$accepted = strtolower(str_replace(strstr($accepted, ','), '', $accepted));
	
	$accepted = (isset($lang2locale[$accepted])) ? $accepted 
												 : str_replace(strstr($accepted, '-'), '', $accepted);
	
	if (isset($lang2locale[$accepted])) {
		$cacti_locale = $accepted;
		$cacti_country = $lang2locale[$accepted]['country'];	
	}
}


/* use a fallback if i18n is disabled (default) or English is requested */
if (read_config_option('i18n_support') == 0 || $cacti_locale == "en") {
	load_fallback_procedure();
	return;
}


/* define the path to the language file */
$path2catalogue = CACTI_BASE_PATH . "/locales/LC_MESSAGES/" . $lang2locale[$cacti_locale]['filename'];

/* use fallback procedure if requested language is not available */
if (file_exists($path2catalogue)) {
	$cacti_textdomains['cacti']['path2locales'] = CACTI_BASE_PATH . "/locales";
	$cacti_textdomains['cacti']['path2catalogue'] = $path2catalogue;
}else {
	load_fallback_procedure();
	return;
}


/* search the correct textdomains for all plugins being installed */
$plugins = db_fetch_assoc("SELECT `directory` FROM `plugin_config`");
if(sizeof($plugins)>0) {
	foreach($plugins as $plugin) {

		$plugin = $plugin['directory'];
		$path2catalogue =  CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales/LC_MESSAGES/" . $lang2locale[$cacti_locale]['filename'];

		if(file_exists($path2catalogue)) {
			$cacti_textdomains[$plugin]['path2locales'] = CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales";
			$cacti_textdomains[$plugin]['path2catalogue'] = $path2catalogue;
		}
	}
	
	/* if i18n support is set to strict mode then check if all plugins support the requested language */
	if(read_config_option('i18n_support') == 2) {
		if(sizeof($plugins) != (sizeof($cacti_textdomains)-1)) {
			load_fallback_procedure();
			return;
		}
	}
}


/* load php-gettext class */	
require(CACTI_BASE_PATH . "/include/gettext/streams.php");
require(CACTI_BASE_PATH . "/include/gettext/gettext.php");


/* prefetch all language files to work in memory only, 
   die if one of the language files is corrupted */
$l10n = array();

foreach($cacti_textdomains as $domain => $paths) {
	$input = new FileReader($cacti_textdomains[$domain]['path2catalogue']);
	if($input == false) {
		die("Unable to read file: " . $cacti_textdomains[$domain]['path2catalogue']);	
	}
	
	$l10n[$domain] = new gettext_reader($input);
	if($l10n[$domain] == false) {
		die("Invalid language file: " . $cacti_textdomains[$domain]['path2catalogue']);	
	}
}

/* load standard wrappers */
load_i18n_gettext_wrappers();


/**
 * load_fallback_procedure()
 * Load wrapper package if native language has to be used 
 */
function load_fallback_procedure(){
	global $cacti_textdomains, $cacti_locale, $cacti_country;

	/* load wrappers if native gettext is not available */
	load_i18n_fallback_wrappers();

	/* reset variables */
	$_SESSION['language'] = "en";

	$cacti_textdomains = array();
	$cacti_locale = "en";
	$cacti_country = "us";
}


/**
 * load_i18n_gettext_wrappers()
 */
function load_i18n_gettext_wrappers(){

	function __gettext($text, $domain = "cacti") {
		global $l10n;
		if (isset($l10n[$domain])) {
			return $l10n[$domain]->translate($text);
		}else {
			return $text;
		}
		
	}


	function __n($single, $plural, $number, $domain = "cacti") {
		global $l10n;
		return $l10n->_ngettext($single, $plural, $number);
	}
	

	function __() {
		global $l10n;

		$args = func_get_args();
		$num  = func_num_args();

		/* this should not happen */
		if ($num < 1) {
			return false;
		
		/* convert pure text strings */
		}elseif ($num == 1) {
			return __gettext($args[0]);
		
		/* convert pure text strings by using a different textdomain */
		}elseif ($num == 2 && isset($l10n[$args[1]])) {
			return __gettext($args[0], $args[1]);
		
		/* convert stings including one or more placeholders */
		}else {
			
			/* only the last argument is allowed to initiate 
			the use of a different textdomain */

			/* get gettext string */
			$args[0] = isset($l10n[$args[$num-1]]) 	? __gettext($args[0], $args[$num-1]) 
													: __gettext($args[0]);

			/* process return string against input arguments */
			return call_user_func_array("sprintf", $args);
		}
	}	


	function __date($format, $timestamp = false, $domain = "cacti") {
	
		global $i18n_date_placeholders;
	
		if (!$timestamp) {
			$timestamp = time();
		}	

		/* placeholders will allow to fill in the translated weekdays, month and so on.. */
		$i18n_date_placeholders = array(
			"#1" => __(date("D", $timestamp), $domain),
			"#2" => str_replace("_", "", __( "_"  . date("M", $timestamp) . "_", $domain)),
			"#3" => str_replace("_", "", __( "__" . date("F", $timestamp) . "_", $domain)),
			"#4" => __(date("l", $timestamp), $domain)
		);	

		/* if defined exchange the format string for the configured locale */ 
		$format = __gettext($format, $domain);
		
		/* replace special date chars by placeholders */
		$format = str_replace(array("D", "M", "F", "l"), array("#1", "#2", "#3", "#4"), $format);
		
		/* get date string included placeholders */
		$date = date($format, $timestamp);
		
		/* fill in specific translations */
		$date = str_replace(array_keys($i18n_date_placeholders), array_values($i18n_date_placeholders), $date);
	
		return $date;
	}

}


/**
 * load_i18n_fallback_wrappers()
 */
function load_i18n_fallback_wrappers(){

	function __gettext($text, $domain = "cacti") {
		return $text;
	}

	function __n($single, $plural, $number, $domain = "cacti") {
		return ($number == 1) ? $single : $plural;
	}

	function __() {

		$args = func_get_args();
		$num  = func_num_args();

		/* this should not happen */
		if ($num < 1) {
			return false;
		
		/* convert pure text strings */
		}elseif ($num == 1) {
			return $args[0];
		
		/* convert pure text strings by using a different textdomain */
		}elseif ($num == 2 && isset($l10n[$args[1]])) {
			return $args[0];
		
		/* convert stings including one or more placeholders */
		}else {
			
			/* only the last argument is allowed to initiate 
			the use of a different textdomain */

			/* process return string against input arguments */
			return call_user_func_array("sprintf", $args);
		}
	}

	function __date($format, $timestamp = false, $domain = "cacti") {
		if (!$timestamp) {$timestamp = time();}	
		return date($format, $timestamp);
	}
}



function get_list_of_locales(){
	$lang2locale = array(
	"sq" 		=> array("language"=>"Albanian", 				"country" => "al", "filename" => "albanian_albania.mo"),
	"ar"		=> array("language"=>"Arabic", 					"country" => "sa", "filename" => "arabic_saudi_arabia.mo"),
	"hy"		=> array("language"=>"Armenian",				"country" => "am", "filename" => "armenian_armenia.mo"),
	"be"		=> array("language"=>"Belarusian",				"country" => "by", "filename" => "belarusian_belarus.mo"),
	"bg"		=> array("language"=>"Bulgarian",				"country" => "bg", "filename" => "bulgarian_bulgaria.mo"),
	"zh" 		=> array("language"=>"Chinese", 				"country" => "cn", "filename" => "chinese_china.mo"),
	"zh-cn"		=> array("language"=>"Chinese (China)",			"country" => "cn", "filename" => "chinese_china.mo"),
	"zh-hk"		=> array("language"=>"Chinese (Hong Kong)",		"country" => "hk", "filename" => "chinese_hong_kong.mo"),
	"zh-sg"		=> array("language"=>"Chinese (Singapore)",		"country" => "sg", "filename" => "chinese_singapore.mo"),
	"zh-tw"		=> array("language"=>"Chinese (Taiwan)",		"country" => "tw", "filename" => "chinese_taiwan.mo"),
	"hr" 		=> array("language"=>"Croatian", 				"country" => "hr", "filename" => "croatian_croatia.mo"),
	"cs"		=> array("language"=>"Czech",					"country" => "cz", "filename" => "czech_czech_republic.mo"),
	"da" 		=> array("language"=>"Danish", 					"country" => "dk", "filename" => "danish_denmark.mo"),
	"nl" 		=> array("language"=>"Dutch", 					"country" => "nl", "filename" => "dutch_netherlands.mo"),
	"en"		=> array("language"=>"English",					"country" => "us", "filename" => "english_dummy"),
	"et"		=> array("language"=>"Estonian", 				"country" => "ee", "filename" => "estonian_estonia.mo"),
	"fi" 		=> array("language"=>"Finnish", 				"country" => "fi", "filename" => "finnish_finland.mo"),
	"fr" 		=> array("language"=>"French", 					"country" => "fr", "filename" => "french_france.mo"),
	"de"		=> array("language"=>"German",					"country" => "de", "filename" => "german_germany.mo"),
	"el" 		=> array("language"=>"Greek", 					"country" => "gr", "filename" => "greek_greece.mo"),
	"iw" 		=> array("language"=>"Hebrew", 					"country" => "il", "filename" => "hebrew_israel.mo"),
	"hi" 		=> array("language"=>"Hindi", 					"country" => "in", "filename" => "hindi_india.mo"),
	"hu" 		=> array("language"=>"Hungarian",				"country" => "hu", "filename" => "hungarian_hungary.mo"),
	"is" 		=> array("language"=>"Icelandic",				"country" => "is", "filename" => "icelandic_iceland.mo"),
	"id" 		=> array("language"=>"Indonesian", 				"country" => "id", "filename" => "indonesian_indonesia.mo"),
	"ga" 		=> array("language"=>"Irish", 					"country" => "ie", "filename" => "irish_ireland.mo"),
	"it" 		=> array("language"=>"Italian", 				"country" => "it", "filename" => "italian_italy.mo"),
	"ja" 		=> array("language"=>"Japanese", 				"country" => "jp", "filename" => "japanese_japan.mo"),
	"ko" 		=> array("language"=>"Korean", 					"country" => "kr", "filename" => "korean_korea.mo"),
	"lv" 		=> array("language"=>"Lativan",					"country" => "lv", "filename" => "latvian_latvia.mo"),
	"lt"		=> array("language"=>"Lithuanian", 				"country" => "lt", "filename" => "lithuanian_lithuania.mo"),
	"mk"		=> array("language"=>"Macedonian", 				"country" => "mk", "filename" => "macedonian_macedonia.mo"),
	"ms"		=> array("language"=>"Malay", 					"country" => "my", "filename" => "malay_malaysia.mo"),
	"mt"		=> array("language"=>"Maltese", 				"country" => "lt", "filename" => "maltese_malta.mo"),
	"no"		=> array("language"=>"Norwegian", 				"country" => "no", "filename" => "norwegian_norway.mo"),
	"pl"		=> array("language"=>"Polish", 					"country" => "pl", "filename" => "polish_poland.mo"),
	"pt"		=> array("language"=>"Portuguese",				"country" => "pt", "filename" => "portuguese_portugal.mo"),
	"ro"		=> array("language"=>"Romanian", 				"country" => "ro", "filename" => "romanian_romania.mo"),
	"ru"		=> array("language"=>"Russian", 				"country" => "ru", "filename" => "russian_russia.mo"),
	"sr"		=> array("language"=>"Serbian", 				"country" => "rs", "filename" => "serbian_serbia.mo"),
	"sk"		=> array("language"=>"Slovak", 					"country" => "sk", "filename" => "slovak_slovakia.mo"),
	"sl"		=> array("language"=>"Slovenian", 				"country" => "si", "filename" => "slovenian_slovenia.mo"),
	"es"		=> array("language"=>"Spanish", 				"country" => "es", "filename" => "spanish_spain.mo"),
	"sv"		=> array("language"=>"Swedish",					"country" => "se", "filename" => "swedish_sweden.mo"),
	"th"		=> array("language"=>"Thai", 					"country" => "th", "filename" => "thai_thailand.mo"),
	"tr"		=> array("language"=>"Turkish", 				"country" => "tr", "filename" => "turkish_turkey.mo"),
	"uk"		=> array("language"=>"Vietnamese", 				"country" => "vn", "filename" => "vietnamese_vietnam.mo"));
	return $lang2locale;
}
?>