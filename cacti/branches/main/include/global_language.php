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

/* default language of Cacti */
# should be setup in global settings
$cacti_lang = "en";


/* An array that will contains all used textdomains
 will be needed for plugin wrappers and can be used
 to display system information */
$cacti_textdomains = array();


/* get a list of locale settings */
$lang2locale = get_list_of_locales();


/* determine whether or not we can support the language */
/* user requests another language */
if(isset($_GET['language']) && isset($lang2locale[$_GET['language']])) {
	$cacti_lang = $_GET['language'];
	$_SESSION['language'] = $cacti_lang;

	/* language definition stored in the SESSION */
}elseif(isset($_SESSION['language']) && isset($lang2locale[$_SESSION['language']])){
	$cacti_lang = $_SESSION['language'];

	/* detect browser settings if user did not setup language via GUI */
}elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$accepted = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$accepted = str_replace(strstr($accepted, ','), '', $accepted);
	$accepted = str_replace(strstr($accepted, '-'), '', $accepted);
	if (isset($lang2locale[$accepted])) {
		$cacti_lang = $accepted;
	}
}


/* use fallback if i18n is disabled (default) or English is requested */
if (read_config_option('i18n_support') == 0 || $cacti_lang == "en" || $cacti_lang == "us") {

	setlocale(LC_ALL, array("us_EN", "us"));
	putenv("LC_ALL=us_EN");
	putenv("LANG=en");

	load_fallback_procedure();
	return;
}

/* define the locale */
$locale = substr($lang2locale[$cacti_lang]["locale"][0], 0, -6);

/* exit if language local file is not available */
$path2catalogue = CACTI_BASE_PATH . "/locales/" . $locale . "/LC_MESSAGES/cacti.mo";
if (!file_exists($path2catalogue)) {
	die("Cacti language locale file not found. Please locate your language file and then you can continue.");
}else {
	$cacti_textdomains['cacti']['path2locales'] = CACTI_BASE_PATH . "/locales";
	$cacti_textdomains['cacti']['path2catalogue'] = $path2catalogue;
}


/* search the correct textdomains for all plugins being installed */
$plugins = db_fetch_assoc("SELECT `directory` FROM `plugin_config`");
if(sizeof($plugins)>0) {
	foreach($plugins as $plugin) {

		$plugin = $plugin['directory'];
		$path2catalogue =  CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales/" . $locale . "/LC_MESSAGES/" . $plugin . ".mo";

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


/* try to setup localization */
$country = $lang2locale[$cacti_lang];
$ls = setlocale(LC_ALL, $country["locale"]);

if($ls !== false) {

	putenv("LC_ALL=" . $country["locale"][0]);
	putenv("LANG=" . $cacti_lang);

}else {

	load_fallback_procedure();
	return;
}


/* determine whether or not we need to emulate gettext */
if (!function_exists("_")) {

	/* use php-gettext */
	require(CACTI_BASE_PATH . "/include/gettext/streams.php");
	require(CACTI_BASE_PATH . "/include/gettext/gettext.php");

	/* bind default textdomain to Cacti */
	$input = new FileReader($cacti_textdomains['cacti']['path2catalogue']);
	$l10n = new gettext_reader($input);

	load_i18n_gettext_wrappers();

} else {

	/* use native support of gettext */
	foreach($cacti_textdomains as $domain => $paths) {
		bindtextdomain($domain, $paths['path2locales']);
		bind_textdomain_codeset($domain, "UTF-8");
	}

	/* bind default textdomain to Cacti */
	textdomain("cacti");
}

/* load wrappers for Cacti plugins */
load_i18n_plugin_wrappes();



function __() {

	/* this should not happen */
	if (func_num_args() < 1) return false;

	$args = func_get_args();
	/* convert pure text strings */
	if (func_num_args() == 1) {
		return _($args[0]);
	}

	/*
	 * in case we have variables to be replaced,
	 * get function arguments */

	/* get gettext string */
	$args[0] = _($args[0]);

	/* process return string against input arguments */
	return call_user_func_array("sprintf", $args);
}



/**
 * load_fallback_procedure()
 * Load wrapper package if native language has to be used
 */
function load_fallback_procedure(){
	global $cacti_textdomains;

	/* load wrappers if native gettext is not available */
	if(!function_exists('_')) load_i18n_fallback_wrappers();

	/* load wrappers for Cacti Plugins */
	load_i18n_plugin_wrappes();

	/* reset variables */
	$cacti_textdomains = array();

}



/**
 * load_i18n_gettext_wrappers()
 * Create wrappers for Cacti if php-gettext will be use to
 * emulate native gettext support
 */
function load_i18n_gettext_wrappers(){

	function _($text) {
		global $l10n;
		return $l10n->translate($text);
	}

	function gettext($text) {
		global $l10n;
		return $l10n->translate($text);
	}

	function ngettext($single, $plural, $number) {
		global $l10n;
		return $l10n->ngettext($single, $plural, $number);
	}

	function dgettext($domain, $text) {
		global $cacti_textdomains;

		if(isset($cacti_textdomains[$domain])) {
			$input = new FileReader($cacti_textdomains[$domain]['path2catalogue']);
			$l10n = new gettext_reader($input);
			return $l10n->translate($text);
		}else {
			return $text;
		}
	}

	function dngettext($domain, $single, $plural, $number) {
		global $cacti_textdomains;

		if(isset($cacti_textdomains[$domain])) {
			$input = new FileReader($cacti_textdomains[$domain]['path2catalogue']);
			$l10n = new gettext_reader($input);
			return $l10n->ngettext($single, $plural, $number);
		}else {
			return ($number == 1) ? $single : $plural;
		}
	}
}



/**
 * load_i18n_fallback_wrappers()
 *
 * Create wrappers for Cacti if fallback is necessary
 */
function load_i18n_fallback_wrappers(){

	function _($text) {
		return $text;
	}

	function gettext($text) {
		return $text;
	}

	function ngettext($single, $plural, $number) {
		return ($number == 1) ? $single : $plural;
	}

	function dgettext($domain, $text){
		return $text;
	}

	function dngettext($domain, $single, $plural, $number){
		return ($number == 1) ? $single : $plural;
	}
}


/**
 * load_i18n_plugin_wrappes()
 *
 * Create standard wrappers for Cacti plugins
 */
function load_i18n_plugin_wrappes(){

	function _p($text, $domain) {
		return dgettext($domain, $text);
	}

	function _pngettext($single, $plural, $number, $domain) {
		return dngettext($domain, $single, $plural, $number);
	}
}


function get_list_of_locales(){
	$lang2locale = array(
	"af" 		=> array("language"=>"Afrikaans", 				"locale" => array("af_ZA.UTF-8","Afrikaans_South Africa.1252")),
	"sq" 		=> array("language"=>"Albanian", 				"locale" => array("sq_AL.UTF-8","Albanian_Albania.1250")),
	"ar" 		=> array("language"=>"Arabic", 					"locale" => array("ar_SA.UTF-8","Arabic_Saudi Arabia.1256")),
	"hy"		=> array("language"=>"Armenia",					"locale" => array("hy_AM.UTF-8","Armenian_Armenia")),
	"eu" 		=> array("language"=>"Basque", 					"locale" => array("eu_ES.UTF-8","Basque_Spain.1252")),
	"be" 		=> array("language"=>"Belarusian", 				"locale" => array("be_BY.UTF-8","Belarusian_Belarus.1251")),
	"bs" 		=> array("language"=>"Bosnian", 				"locale" => array("bs_BA.UTF-8","Serbian (Latin)")),
	"bg" 		=> array("language"=>"Bulgarian",				"locale" => array("bg_BG.UTF-8","Bulgarian_Bulgaria.1251")),
	"ca" 		=> array("language"=>"Catalan", 				"locale" => array("ca_ES.UTF-8","Catalan_Spain.1252")),
	"hr" 		=> array("language"=>"Croatian", 				"locale" => array("hr_HR.UTF-8","Croatian_Croatia.1250")),
	"zh" 		=> array("language"=>"Chinese", 				"locale" => array("zh_CN.UTF-8","Chinese_China.936")),
	"zh_cn" 	=> array("language"=>"Chinese (China)", 		"locale" => array("zh_CN.UTF-8","Chinese_China.936")),
	"zh_tw" 	=> array("language"=>"Chinese (Traditional)", 	"locale" => array("zh_TW.UTF-8","Chinese_Taiwan.950")),
	"cs" 		=> array("language"=>"Czech", 					"locale" => array("cs_CZ.UTF-8","Czech_Czech Republic.1250")),
	"da" 		=> array("language"=>"Danish", 					"locale" => array("da_DK.UTF-8","Danish_Denmark.1252")),
	"nl" 		=> array("language"=>"Dutch", 					"locale" => array("nl_NL.UTF-8","Dutch_Netherlands.1252")),
	"en" 		=> array("language"=>"English", 				"locale" => array("en_US.UTF-8","English_United States.1252")),
	"us" 		=> array("language"=>"English", 				"locale" => array("en_US.UTF-8","English_United States.1252")),
	"et" 		=> array("language"=>"Estonian", 				"locale" => array("et_EE.UTF-8","Estonian_Estonia.1257")),
	"fa" 		=> array("language"=>"Farsi", 					"locale" => array("fa_IR.UTF-8","Farsi_Iran.1256")),
	"fil" 		=> array("language"=>"Filipino", 				"locale" => array("ph_PH.UTF-8","")),
	"fi" 		=> array("language"=>"Finnish", 				"locale" => array("fi_FI.UTF-8","Finnish_Finland.1252")),
	"fr" 		=> array("language"=>"French", 					"locale" => array("fr_FR.UTF-8","French_France.1252")),
	"ga" 		=> array("language"=>"Gaelic", 					"locale" => array("ga.UTF-8","")),
	"gl" 		=> array("language"=>"Gallego", 				"locale" => array("gl_ES.UTF-8","Galician_Spain.1252")),
	"ka" 		=> array("language"=>"Georgian", 				"locale" => array("ka_GE.UTF-8","")),
	"de" 		=> array("language"=>"German", 					"locale" => array("de_DE.UTF-8","German_Germany.1252")),
	"el" 		=> array("language"=>"Greek", 					"locale" => array("el_GR.UTF-8","Greek_Greece.1253")),
	"gu" 		=> array("language"=>"Gujarati", 				"locale" => array("gu.UTF-8","Gujarati_India.0")),
	"he" 		=> array("language"=>"Hebrew", 					"locale" => array("he_IL.utf8","Hebrew_Israel.1255")),
	"hi" 		=> array("language"=>"Hindi", 					"locale" => array("hi_IN.UTF-8","")),
	"hu" 		=> array("language"=>"Hungarian", 				"locale" => array("hu.UTF-8","Hungarian_Hungary.1250")),
	"is" 		=> array("language"=>"Icelandic", 				"locale" => array("is_IS.UTF-8","Icelandic_Iceland.1252")),
	"id" 		=> array("language"=>"Indonesian", 				"locale" => array("id_ID.UTF-8","Indonesian_indonesia.1252")),
	"it" 		=> array("language"=>"Italian", 				"locale" => array("it_IT.UTF-8","Italian_Italy.1252")),
	"ja" 		=> array("language"=>"Japanese", 				"locale" => array("ja_JP.UTF-8","Japanese_Japan.932")),
	"kn" 		=> array("language"=>"Kannada", 				"locale" => array("kn_IN.UTF-8","")),
	"km" 		=> array("language"=>"Khmer", 					"locale" => array("km_KH.UTF-8","")),
	"ko" 		=> array("language"=>"Korean", 					"locale" => array("ko_KR.UTF-8","Korean_Korea.949")),
	"lo" 		=> array("language"=>"Lao", 					"locale" => array("lo_LA.UTF-8","Lao_Laos.UTF-8")),
	"lt" 		=> array("language"=>"Lithuanian", 				"locale" => array("lt_LT.UTF-8","Lithuanian_Lithuania.1257")),
	"lv" 		=> array("language"=>"Latvian", 				"locale" => array("lat.UTF-8","Latvian_Latvia.1257")),
	"ml" 		=> array("language"=>"Malayalam", 				"locale" => array("ml_IN.UTF-8","")),
	"ms" 		=> array("language"=>"Malaysian", 				"locale" => array("id_ID.UTF-8","")),
	"mi_tn" 	=> array("language"=>"Maori (Ngai Tahu)", 		"locale" => array("mi_NZ.UTF-8","")),
	"mi_wwow" 	=> array("language"=>"Maori (Waikoto Uni)", 	"locale" => array("mi_NZ.UTF-8","")),
	"mn" 		=> array("language"=>"Mongolian", 				"locale" => array("mn.UTF-8","Cyrillic_Mongolian.1251")),
	"no" 		=> array("language"=>"Norwegian", 				"locale" => array("no_NO.UTF-8","Norwegian_Norway.1252")),
	"nn" 		=> array("language"=>"Nynorsk", 				"locale" => array("nn_NO.UTF-8","Norwegian-Nynorsk_Norway.1252")),
	"pl" 		=> array("language"=>"Polish", 					"locale" => array("pl.UTF-8","Polish_Poland.1250")),
	"pt" 		=> array("language"=>"Portuguese", 				"locale" => array("pt_PT.UTF-8","Portuguese_Portugal.1252")),
	"pt_br" 	=> array("language"=>"Portuguese (Brazil)", 	"locale" => array("pt_BR.UTF-8","Portuguese_Brazil.1252")),
	"ro" 		=> array("language"=>"Romanian", 				"locale" => array("ro_RO.UTF-8","Romanian_Romania.1250")),
	"ru" 		=> array("language"=>"Russian", 				"locale" => array("ru_RU.UTF-8","Russian_Russia.1251")),
	"sm" 		=> array("language"=>"Samoan", 					"locale" => array("mi_NZ.UTF-8","Maori.1252")),
	"sr" 		=> array("language"=>"Serbian", 				"locale" => array("sr_CS.UTF-8","Serbian (Cyrillic)_Serbia and Montenegro.1251")),
	"sk" 		=> array("language"=>"Slovak", 					"locale" => array("sk_SK.UTF-8","Slovak_Slovakia.1250")),
	"sl" 		=> array("language"=>"Slovenian", 				"locale" => array("sl_SI.UTF-8","Slovenian_Slovenia.1250")),
	"so" 		=> array("language"=>"Somali", 					"locale" => array("so_SO.UTF-8","")),
	"es" 		=> array("language"=>"Spanish", 				"locale" => array("es_ES.UTF-8","Spanish_Spain.1252")),
	"sv" 		=> array("language"=>"Swedish", 				"locale" => array("sv_SE.UTF-8","Swedish_Sweden.1252")),
	"tl" 		=> array("language"=>"Tagalog", 				"locale" => array("tl.UTF-8","")),
	"ta" 		=> array("language"=>"Tamil", 					"locale" => array("ta_IN.UTF-8","English_Australia.1252")),
	"th" 		=> array("language"=>"Thai", 					"locale" => array("th_TH.UTF-8","Thai_Thailand.874")),
	"to" 		=> array("language"=>"Tongan", 					"locale" => array("mi_NZ.UTF-8","Maori.1252")),
	"tr" 		=> array("language"=>"Turkish", 				"locale" => array("tr_TR.UTF-8","Turkish_Turkey.1254")),
	"uk" 		=> array("language"=>"Ukrainian", 				"locale" => array("uk_UA.UTF-8","Ukrainian_Ukraine.1251")),
	"vi" 		=> array("language"=>"Vietnamese", 				"locale" => array("vi_VN.UTF-8","Vietnamese_Viet Nam.1258")));

	return $lang2locale;
}
?>