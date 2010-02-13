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

/* detect system time zone */
define("CACTI_SYSTEM_TIME_ZONE", date("e"));

/* return to main if time zone support has been deactivated */
if (read_config_option("i18n_timezone_support") == 0) {
	define("CACTI_CUSTOM_TIME_ZONE", CACTI_SYSTEM_TIME_ZONE);
	return;
}

/* determine whether or not we can support a different time zone */
/* user requests another timezone (Validation is not required!)*/
if (isset($_GET['time_zone'])) {
	if(init_time_zone($_GET['time_zone'])) {
		set_user_config_option('time_zone', $_GET['time_zone']);
		$_SESSION['time_zone'] = $_GET['time_zone'];
	}

/* time zone definition is stored in the SESSION */
}elseif (isset($_SESSION['time_zone'])) {
	init_time_zone($_SESSION['time_zone']);

/* look up for user customized time zone stored in Cacti DB */
}elseif ($time_zone = read_user_config_option('time_zone')) {
	if(init_time_zone($time_zone)) {
		$_SESSION['time_zone'] = $time_zone;
	};

/* use the default time zone defined under "general" or fall back to sytsem time zone*/
}else {
	init_time_zone(read_config_option("i18n_default_timezone"));
}


/**
 * init_time_zone() - initialize the custom time zone
 * 
 * @time_zone - custom time zone that has to be used
 * @return - returns true (successful) or false (failed)
 */
function init_time_zone($time_zone){
	if(set_time_zone($time_zone)) {
		@define("CACTI_CUSTOM_TIME_ZONE", $time_zone);
		return true;
	}else {
		@define("CACTI_CUSTOM_TIME_ZONE", CACTI_SYSTEM_TIME_ZONE);
		return false;
	}
}

/**
 * set_time_zone() - toogle between system and custom time zone
 * 
 * @time_zone - time zone 
 * @return - returns true (successful) or false (failed)
 */
function set_time_zone($time_zone) {
	/* lock this function if time zone support is disabled */
	if(read_config_option("i18n_timezone_support")) {
		/* if defined only system or custom time zone will be accepted. Avoid that plugins will setup another time zone. */
		if(defined('CACTI_CUSTOM_TIME_ZONE') & $time_zone != CACTI_SYSTEM_TIME_ZONE && $time_zone != CACTI_CUSTOM_TIME_ZONE) {
			return false;
		}else {
			/* use date functions if possible (PHP>=5.1.0) */
			if(function_exists('date_default_timezone_set')) {
				return (@date_default_timezone_set($time_zone)) ? true : false;
			
			/* try to setup time zone if safe mode is not enabled. */
			}else {
				return (@putenv("TZ=" . $time_zone)) ? true : false;
			}
		}
	}
	return false;
}

/**
 * disable_tmz_support() - fall back to system time zone
 * 
 * @return - returns true (successful) or false (failed)
 */
function disable_tmz_support() {
	return (set_time_zone(CACTI_SYSTEM_TIME_ZONE)) ? true : false;
}

/**
 * enable_tmz_support() - switch to custom time zone
 * 
 * @return - returns true (successful) or false (failed)
 */
function enable_tmz_support() {
	return (set_time_zone(CACTI_CUSTOM_TIME_ZONE)) ? true : false;
}


function get_list_of_timezones() {
	/* define a human friendly array of timezones */
	$africa = __("Africa") . ": ";
	$america = __("America") . ": ";
	$antartica = __("Antarctica") . ": ";
	$artic = __("Artic") . ": ";
	$asia = __("Asia") . ": ";
	$atlantic = __("Atlantic") . ": ";
	$australia = __("Australia") . ": ";
	$europe = __("Europe") . ": ";
	$indian = __("Indian") . ": ";
	$pacific = __("Pacific") . ": ";

	$timezones = array(
		"Africa/Abidjan	" => $africa . __("Abidja"),
		"Africa/Accra" => $africa . __("Accra"),
		"Africa/Addis_Ababa" => $africa . __("Addis Ababa"),
		"Africa/Algiers" => $africa . __("Algiers"),
		"Africa/Asmara" => $africa . __("Asmara"),
		"Africa/Bamako" => $africa . __("Bamako"),
		"Africa/Bangui" => $africa . __("Bangui"),
		"Africa/Banjul" => $africa . __("Banju"),
		"Africa/Bissau" => $africa . __("Bissau"),
		"Africa/Blantyre" => $africa . __("Blantyre"),
		"Africa/Brazzaville" => $africa . __("Brazzaville"),
		"Africa/Bujumbura" => $africa . __("Bujumbura"),
		"Africa/Cairo" => $africa . __("Cairo"),
		"Africa/Casablanca" => $africa . __("Casablanca"),
		"Africa/Ceuta" => $africa . __("Ceuta"),
		"Africa/Conakry" => $africa . __("Conakry"),
		"Africa/Dakar" => $africa . __("Dakar"),
		"Africa/Dar_es_Salaam" => $africa . __("Dar es Salaam"),
		"Africa/Djibouti" => $africa . __("Djibouti"),
		"Africa/Douala" => $africa . __("Douala"),
		"Africa/El_Aaiun" => $africa . __("El Aaiun"),
		"Africa/Freetown" => $africa . __("Freetown"),
		"Africa/Gaborone" => $africa . __("Gaborone"),
		"Africa/Harare" => $africa . __("Harare"),
		"Africa/Johannesburg" => $africa . __("Johannesburg"),
		"Africa/Kampala" => $africa . __("Kampala"),
		"Africa/Khartoum" => $africa . __("Khartoum"),
		"Africa/Kigali" => $africa . __("Kigali"),
		"Africa/Kinshasa" => $africa . __("Kinshasa"),
		"Africa/Lagos" => $africa . __("Lagos"),
		"Africa/Libreville" => $africa . __("Libreville"),
		"Africa/Lome" => $africa . __("Lome"),
		"Africa/Luanda" => $africa . __("Luanda"),
		"Africa/Lubumbashi" => $africa . __("Lubumbashi"),
		"Africa/Lusaka" => $africa . __("Lusaka"),
		"Africa/Malabo" => $africa . __("Malabo"),
		"Africa/Maputo" => $africa . __("Maputo"),
		"Africa/Maseru" => $africa . __("Maseru"),
		"Africa/Mbabane" => $africa . __("Mbabane"),
		"Africa/Mogadishu" => $africa . __("Mogadishu"),
		"Africa/Monrovia" => $africa . __("Monrovia"),
		"Africa/Nairobi" => $africa . __("Nairobi"),
		"Africa/Ndjamena" => $africa . __("Ndjamena"),
		"Africa/Niamey" => $africa . __("Niamey"),
		"Africa/Nouakchott" => $africa . __("Nouakchott"),
		"Africa/Ouagadougou" => $africa . __("Ouagadougou"),
		"Africa/Porto-Novo" => $africa . __("Porto-Novo"),
		"Africa/Sao_Tome" => $africa . __("Sao Tome"),
		"Africa/Tripoli" => $africa . __("Tripoli"),
		"Africa/Tunis" => $africa . __("Tunis"),
		"Africa/Windhoek" => $africa . __("Windhoek"),
		"America/Adak" => $america . __("Adak"),
		"America/Anchorage" => $america . __("Anchorage"),
		"America/Anguilla" => $america . __("Anguilla"),
		"America/Antigua" => $america . __("Antigua"),
		"America/Araguaina" => $america . __("Araguaina"),
		"America/Argentina/Buenos_Aires" => $america . __("Argentina") . " / " . __("Buenos Aires"),
		"America/Argentina/Catamarca" => $america . __("Argentina") . " / " . __("Catamarca"),
		"America/Argentina/Cordoba" => $america . __("Argentina") . " / " . __("Cordoba"),
		"America/Argentina/Jujuy" => $america . __("Argentina") . " / " . __("Jujuy"),
		"America/Argentina/La_Rioja" => $america . __("Argentina") . " / " . __("La Rioja"),
		"America/Argentina/Mendoza" => $america . __("Argentina") . " / " . __("Mendoza"),
		"America/Argentina/Rio_Gallegos" => $america . __("Argentina") . " / " . __("Rio Gallegos"),
		"America/Argentina/Salta" => $america . __("Argentina") . " / " . __("Salta"),
		"America/Argentina/San_Juan" => $america . __("Argentina") . " / " . __("San Juan"),
		"America/Argentina/San_Luis" => $america . __("Argentina") . " / " . __("San Luis"),
		"America/Argentina/Tucuman" => $america . __("Argentina") . " / " . __("Tucuman"),
		"America/Argentina/Ushuaia" => $america . __("Argentina") . " / " . __("Ushuaia"),
		"America/Aruba" => $america . __("Aruba"),
		"America/Asuncion" => $america . __("Asuncion"),
		"America/Atikokan" => $america . __("Atikokan"),
		"America/Bahia" => $america . __("Bahia"),
		"America/Barbados" => $america . __("Barbados"),
		"America/Belem" => $america . __("Belem"),
		"America/Belize" => $america . __("Belize"),
		"America/Blanc-Sablon" => $america . __("Blanc-Sablon"),
		"America/Boa_Vista" => $america . __("Boa Vista"),
		"America/Bogota" => $america . __("Bogota"),
		"America/Boise" => $america . __("Boise"),
		"America/Cambridge_Bay" => $america . __("Cambridge Bay"),
		"America/Campo_Grande" => $america . __("Campo Grande"),
		"America/Cancun" => $america . __("Cancun"),
		"America/Caracas" => $america . __("Caracas"),
		"America/Cayenne" => $america . __("Cayenne"),
		"America/Cayman" => $america . __("Cayman"),
		"America/Chicago" => $america . __("Chicago"),
		"America/Chihuahua" => $america . __("Chihuahua"),
		"America/Costa_Rica" => $america . __("Costa Rica"),
		"America/Cuiaba" => $america . __("Cuiaba"),
		"America/Curacao" => $america . __("Curacao"),
		"America/Danmarkshavn" => $america . __("Danmarkshavn"),
		"America/Dawson" => $america . __("Dawson"),
		"America/Dawson_Creek" => $america . __("Dawson Creek"),
		"America/Denver" => $america . __("Denver"),
		"America/Detroit" => $america . __("Detroit"),
		"America/Dominica" => $america . __("Dominica"),
		"America/Edmonton" => $america . __("Edmonton"),
		"America/Eirunepe" => $america . __("Eirunepe"),
		"America/El_Salvador" => $america . __("El Salvador"),
		"America/Fortaleza" => $america . __("Fortaleza"),
		"America/Glace_Bay" => $america . __("Glace Bay"),
		"America/Godthab" => $america . __("Godthab"),
		"America/Goose_Bay" => $america . __("Goose Bay"),
		"America/Grand_Turk" => $america . __("Grand Turk"),
		"America/Grenada" => $america . __("Grenada"),
		"America/Guadeloupe" => $america . __("Guadeloupe"),
		"America/Guatemala" => $america . __("Guatemala"),
		"America/Guayaquil" => $america . __("Guayaquil"),
		"America/Guyana" => $america . __("Guyana"),
		"America/Halifax" => $america . __("Halifax"),
		"America/Havana" => $america . __("Havana"),
		"America/Hermosillo" => $america . __("Hermosillo"),
		"America/Indiana/Indianapolis" => $america . __("Indiana") . " / " . __("Indianapolis"),
		"America/Indiana/Knox" => $america . __("Indiana") . " / " . __("Knox"),
		"America/Indiana/Marengo" => $america . __("Indiana") . " / " . __("Marengo"),
		"America/Indiana/Petersburg" => $america . __("Indiana") . " / " . __("Petersburg"),
		"America/Indiana/Tell_City" => $america . __("Indiana") . " / " . __("Tell_City"),
		"America/Indiana/Vevay" => $america . __("Indiana") . " / " . __("Vevay"),
		"America/Indiana/Vincennes" => $america . __("Indiana") . " / " . __("Vincennes"),
		"America/Indiana/Winamac" => $america . __("Indiana") . " / " . __("Winamac"),
		"America/Inuvik" => $america . __("Inuvik"),
		"America/Iqaluit" => $america . __("Iqaluit"),
		"America/Jamaica" => $america . __("Jamaica"),
		"America/Juneau" => $america . __("Juneau"),
		"America/Kentucky/Louisville" => $america . __("Kentucky") . " / " . __("Louisville"),
		"America/Kentucky/Monticello" => $america . __("Kentucky") . " / " . __("Monticello"),
		"America/La_Paz" => $america . __("La Paz"),
		"America/Lima" => $america . __("Lima"),
		"America/Los_Angeles" => $america . __("Los Angeles"),
		"America/Maceio" => $america . __("Maceio"),
		"America/Managua" => $america . __("Managua"),
		"America/Manaus" => $america . __("Manaus"),
		"America/Marigot" => $america . __("Marigot"),
		"America/Martinique" => $america . __("Martinique"),
		"America/Mazatlan" => $america . __("Mazatlan"),
		"America/Menominee" => $america . __("Menominee"),
		"America/Merida" => $america . __("Merida"),
		"America/Mexico_City" => $america . __("Mexico City"),
		"America/Miquelon" => $america . __("Miquelo"),
		"America/Moncton" => $america . __("Moncton"),
		"America/Monterrey" => $america . __("Monterrey"),
		"America/Montevideo" => $america . __("Montevideo"),
		"America/Montreal" => $america . __("Montreal"),
		"America/Montserrat" => $america . __("Montserrat"),
		"America/Nassau" => $america . __("Nassau"),
		"America/New_York" => $america . __("New York"),
		"America/Nipigon" => $america . __("Nipigon"),
		"America/Nome" => $america . __("Nome"),
		"America/Noronha" => $america . __("Noronha"),
		"America/North_Dakota/Center" => $america . __("North Dakota") . " / " . __("Center"),
		"America/North_Dakota/New_Salem" => $america . __("North Dakota") . " / " . __("New Salem"),
		"America/Panama" => $america . __("Panama"),
		"America/Pangnirtung" => $america . __("Pangnirtung"),
		"America/Paramaribo" => $america . __("Paramaribo"),
		"America/Phoenix" => $america . __("Phoenix"),
		"America/Port-au-Prince" => $america . __("Port-au-Prince"),
		"America/Port_of_Spain" => $america . __("Port of Spain"),
		"America/Porto_Velho" => $america . __("Porto Velho"),
		"America/Puerto_Rico" => $america . __("Puerto Rico"),
		"America/Rainy_River" => $america . __("Rainy River"),
		"America/Rankin_Inlet" => $america . __("Rankin Inlet"),
		"America/Recife" => $america . __("Recife"),
		"America/Regina" => $america . __("Regina"),
		"America/Resolute" => $america . __("Resolute"),
		"America/Rio_Branco" => $america . __("Rio Branco"),
		"America/Santarem" => $america . __("Santarem"),
		"America/Santiago" => $america . __("Santiago"),
		"America/Santo_Domingo" => $america . __("Santo Domingo"),
		"America/Sao_Paulo" => $america . __("Sao Paulo"),
		"America/Scoresbysund" => $america . __("Scoresbysund"),
		"America/Shiprock" => $america . __("Shiprock"),
		"America/St_Barthelemy" => $america . __("St Barthelemy"),
		"America/St_Johns" => $america . __("St Johns"),
		"America/St_Kitts" => $america . __("St Kitts"),
		"America/St_Lucia" => $america . __("St Lucia"),
		"America/St_Thomas" => $america . __("St Thomas"),
		"America/St_Vincent" => $america . __("St Vincent"),
		"America/Swift_Current" => $america . __("Swift Current"),
		"America/Tegucigalpa" => $america . __("Tegucigalpa"),
		"America/Thule" => $america . __("Thule"),
		"America/Thunder_Bay" => $america . __("Thunder Bay"),
		"America/Tijuana" => $america . __("Tijuana"),
		"America/Toronto" => $america . __("Toronto"),
		"America/Tortola" => $america . __("Tortola"),
		"America/Vancouver" => $america . __("Vancouver"),
		"America/Whitehorse" => $america . __("Whitehorse"),
		"America/Winnipeg" => $america . __("Winnipeg"),
		"America/Yakutat" => $america . __("Yakutat"),
		"America/Yellowknife" => $america . __("Yellowknife"),
		"Antarctica/Casey" => $antartica . __("Casey"),
		"Antarctica/Davis" => $antartica . __("Davis"),
		"Antarctica/DumontDUrville" => $antartica . __("DumontDUrville"),
		"Antarctica/Mawson" => $antartica . __("Mawson"),
		"Antarctica/McMurdo" => $antartica . __("McMurdo"),
		"Antarctica/Palmer" => $antartica . __("Palme"),
		"Antarctica/Rothera" => $antartica . __("Rothera"),
		"Antarctica/South_Pole" => $antartica . __("South Pole"),
		"Antarctica/Syowa" => $antartica . __("Syowa"),
		"Antarctica/Vostok" => $antartica . __("Vostok"),
		"Arctic/Longyearbyen" => $artic . __("Longyearbyen"),
		"Asia/Aden" => $asia . __("Aden"),
		"Asia/Almaty" => $asia . __("Almaty"),
		"Asia/Amman" => $asia . __("Amman"),
		"Asia/Anadyr" => $asia . __("Anadyr"),
		"Asia/Aqtau" => $asia . __("Aqtau"),
		"Asia/Aqtobe" => $asia . __("Aqtobe"),
		"Asia/Ashgabat" => $asia . __("Ashgabat"),
		"Asia/Baghdad" => $asia . __("Baghdad"),
		"Asia/Bahrain" => $asia . __("Bahrain"),
		"Asia/Baku" => $asia . __("Baku"),
		"Asia/Bangkok" => $asia . __("Bangkok"),
		"Asia/Beirut" => $asia . __("Beirut"),
		"Asia/Bishkek" => $asia . __("Bishkek"),
		"Asia/Brunei" => $asia . __("Brunei"),
		"Asia/Choibalsan" => $asia . __("Choibalsan"),
		"Asia/Chongqing" => $asia . __("Chongqing"),
		"Asia/Colombo" => $asia . __("Colombo"),
		"Asia/Damascus" => $asia . __("Damascus"),
		"Asia/Dhaka" => $asia . __("Dhaka"),
		"Asia/Dili" => $asia . __("Dili"),
		"Asia/Dubai" => $asia . __("Dubai"),
		"Asia/Dushanbe" => $asia . __("Dushanbe"),
		"Asia/Gaza" => $asia . __("Gaza"),
		"Asia/Harbin" => $asia . __("Harbin"),
		"Asia/Ho_Chi_Minh" => $asia . __("Ho Chi Minh"),
		"Asia/Hong_Kong" => $asia . __("Hong Kong"),
		"Asia/Hovd" => $asia . __("Hovd"),
		"Asia/Irkutsk" => $asia . __("Irkutsk"),
		"Asia/Jakarta" => $asia . __("Jakarta"),
		"Asia/Jayapura" => $asia . __("Jayapura"),
		"Asia/Jerusalem" => $asia . __("Jerusalem"),
		"Asia/Kabul" => $asia . __("Kabul"),
		"Asia/Kamchatka" => $asia . __("Kamchatka"),
		"Asia/Karachi" => $asia . __("Karachi"),
		"Asia/Kashgar" => $asia . __("Kashgar"),
		"Asia/Katmandu" => $asia . __("Katmandu"),
		"Asia/Kolkata" => $asia . __("Kolkata"),
		"Asia/Krasnoyarsk" => $asia . __("Krasnoyarsk"),
		"Asia/Kuala_Lumpur" => $asia . __("Kuala Lumpur"),
		"Asia/Kuching" => $asia . __("Kuching"),
		"Asia/Kuwait" => $asia . __("Kuwait"),
		"Asia/Macau" => $asia . __("Macau"),
		"Asia/Magadan" => $asia . __("Magadan"),
		"Asia/Makassar" => $asia . __("Makassar"),
		"Asia/Manila" => $asia . __("Manila"),
		"Asia/Muscat" => $asia . __("Muscat"),
		"Asia/Nicosia" => $asia . __("Nicosia"),
		"Asia/Novosibirsk" => $asia . __("Novosibirsk"),
		"Asia/Omsk" => $asia . __("Omsk"),
		"Asia/Oral" => $asia . __("Oral"),
		"Asia/Phnom_Penh" => $asia . __("Phnom Penh"),
		"Asia/Pontianak" => $asia . __("Pontianak"),
		"Asia/Pyongyang" => $asia . __("Pyongyang"),
		"Asia/Qatar" => $asia . __("Qatar"),
		"Asia/Qyzylorda" => $asia . __("Qyzylorda"),
		"Asia/Rangoon" => $asia . __("Rangoon"),
		"Asia/Riyadh" => $asia . __("Riyadh"),
		"Asia/Sakhalin" => $asia . __("Sakhalin"),
		"Asia/Samarkand" => $asia . __("Samarkand"),
		"Asia/Seoul" => $asia . __("Seoul"),
		"Asia/Shanghai" => $asia . __("Shanghai"),
		"Asia/Singapore" => $asia . __("Singapore"),
		"Asia/Taipei" => $asia . __("Taipei"),
		"Asia/Tashkent" => $asia . __("Tashkent"),
		"Asia/Tbilisi" => $asia . __("Tbilisi"),
		"Asia/Tehran" => $asia . __("Tehran"),
		"Asia/Thimphu" => $asia . __("Thimphu"),
		"Asia/Tokyo" => $asia . __("Tokyo"),
		"Asia/Ulaanbaatar" => $asia . __("Ulaanbaatar"),
		"Asia/Urumqi" => $asia . __("Urumqi"),
		"Asia/Vientiane" => $asia . __("Vientiane"),
		"Asia/Vladivostok" => $asia . __("Vladivostok"),
		"Asia/Yakutsk" => $asia . __("Yakutsk"),
		"Asia/Yekaterinburg" => $asia . __("Yekaterinburg"),
		"Asia/Yerevan" => $asia . __("Yerevan"),
		"Atlantic/Azores" => $atlantic . __("Azores"),
		"Atlantic/Bermuda" => $atlantic . __("Bermuda"),
		"Atlantic/Canary" => $atlantic . __("Canary"),
		"Atlantic/Cape_Verde" => $atlantic . __("Cape Verde"),
		"Atlantic/Faroe" => $atlantic . __("Faroe"),
		"Atlantic/Madeira" => $atlantic . __("Madeira"),
		"Atlantic/Reykjavik" => $atlantic . __("Reykjavik"),
		"Atlantic/South_Georgia" => $atlantic . __("South Georgia"),
		"Atlantic/St_Helena" => $atlantic . __("St Helena"),
		"Atlantic/Stanley" => $atlantic . __("Stanley"),
		"Australia/Adelaide" => $australia . __("Adelaide"),
		"Australia/Brisbane" => $australia . __("Brisbane"),
		"Australia/Broken_Hill" => $australia . __("Broken Hill"),
		"Australia/Currie" => $australia . __("Currie"),
		"Australia/Darwin" => $australia . __("Darwin"),
		"Australia/Eucla" => $australia . __("Eucla"),
		"Australia/Hobart" => $australia . __("Hobart"),
		"Australia/Lindeman" => $australia . __("Lindeman"),
		"Australia/Lord_Howe" => $australia . __("Lord Howe"),
		"Australia/Melbourne" => $australia . __("Melbourne"),
		"Australia/Perth" => $australia . __("Perth"),
		"Australia/Sydney" => $australia . __("Sydney"),
		"Europe/Amsterdam" => $europe . __("Amsterdam"),
		"Europe/Andorra" => $europe . __("Andorra"),
		"Europe/Athens" => $europe . __("Athens"),
		"Europe/Belgrade" => $europe . __("Belgrade"),
		"Europe/Berlin" => $europe . __("Berlin"),
		"Europe/Bratislava" => $europe . __("Bratislava"),
		"Europe/Brussels" => $europe . __("Brussels"),
		"Europe/Bucharest" => $europe . __("Bucharest"),
		"Europe/Budapest" => $europe . __("Budapest"),
		"Europe/Chisinau" => $europe . __("Chisinau"),
		"Europe/Copenhagen" => $europe . __("Copenhagen"),
		"Europe/Dublin" => $europe . __("Dublin"),
		"Europe/Gibraltar" => $europe . __("Gibraltar"),
		"Europe/Guernsey" => $europe . __("Guernsey"),
		"Europe/Helsinki" => $europe . __("Helsinki"),
		"Europe/Isle_of_Man" => $europe . __("Isle of Man"),
		"Europe/Istanbul" => $europe . __("Istanbul"),
		"Europe/Jersey" => $europe . __("Jersey"),
		"Europe/Kaliningrad" => $europe . __("Kaliningrad"),
		"Europe/Kiev" => $europe . __("Kiev"),
		"Europe/Lisbon" => $europe . __("Lisbon"),
		"Europe/Ljubljana" => $europe . __("Ljubljana"),
		"Europe/London" => $europe . __("London"),
		"Europe/Luxembourg" => $europe . __("Luxembourg"),
		"Europe/Madrid" => $europe . __("Madrid"),
		"Europe/Malta" => $europe . __("Malta"),
		"Europe/Mariehamn" => $europe . __("Mariehamn"),
		"Europe/Minsk" => $europe . __("Minsk"),
		"Europe/Monaco" => $europe . __("Monaco"),
		"Europe/Moscow" => $europe . __("Moscow"),
		"Europe/Oslo" => $europe . __("Oslo"),
		"Europe/Paris" => $europe . __("Paris"),
		"Europe/Podgorica" => $europe . __("Podgorica"),
		"Europe/Prague" => $europe . __("Prague"),
		"Europe/Riga" => $europe . __("Riga"),
		"Europe/Rome" => $europe . __("Rome"),
		"Europe/Samara" => $europe . __("Samara"),
		"Europe/San_Marino" => $europe . __("San Marino"),
		"Europe/Sarajevo" => $europe . __("Sarajevo"),
		"Europe/Simferopol" => $europe . __("Simferopol"),
		"Europe/Skopje" => $europe . __("Skopje"),
		"Europe/Sofia" => $europe . __("Sofia"),
		"Europe/Stockholm" => $europe . __("Stockholm"),
		"Europe/Tallinn" => $europe . __("Tallinn"),
		"Europe/Tirane" => $europe . __("Tirane"),
		"Europe/Uzhgorod" => $europe . __("Uzhgorod"),
		"Europe/Vaduz" => $europe . __("Vaduz"),
		"Europe/Vatican" => $europe . __("Vatican"),
		"Europe/Vienna" => $europe . __("Vienna"),
		"Europe/Vilnius" => $europe . __("Vilnius"),
		"Europe/Volgograd" => $europe . __("Volgograd"),
		"Europe/Warsaw" => $europe . __("Warsaw"),
		"Europe/Zagreb" => $europe . __("Zagreb"),
		"Europe/Zaporozhye" => $europe . __("Zaporozhye"),
		"Europe/Zurich" => $europe . __("Zurich"),
		"Indian/Antananarivo" => $indian . __("Antananarivo"),
		"Indian/Chagos" => $indian . __("Chagos"),
		"Indian/Christmas" => $indian . __("Christmas"),
		"Indian/Cocos" => $indian . __("Cocos"),
		"Indian/Comoro" => $indian . __("Comoro"),
		"Indian/Kerguelen" => $indian . __("Kerguelen"),
		"Indian/Mahe" => $indian . __("Mahe"),
		"Indian/Maldives" => $indian . __("Maldives"),
		"Indian/Mauritius" => $indian . __("Mauritius"),
		"Indian/Mayotte" => $indian . __("Mayotte"),
		"Indian/Reunion" => $indian . __("Reunion"),
		"Pacific/Apia" => $pacific . __("Apia"),
		"Pacific/Auckland" => $pacific . __("Auckland"),
		"Pacific/Chatham" => $pacific . __("Chatham"),
		"Pacific/Easter" => $pacific . __("Easter"),
		"Pacific/Efate" => $pacific . __("Efate"),
		"Pacific/Enderbury" => $pacific . __("Enderbury"),
		"Pacific/Fakaofo" => $pacific . __("Fakaofo"),
		"Pacific/Fiji" => $pacific . __("Fiji"),
		"Pacific/Funafuti" => $pacific . __("Funafuti"),
		"Pacific/Galapagos" => $pacific . __("Galapagos"),
		"Pacific/Gambier" => $pacific . __("Gambier"),
		"Pacific/Guadalcanal" => $pacific . __("Guadalcanal"),
		"Pacific/Guam" => $pacific . __("Guam"),
		"Pacific/Honolulu" => $pacific . __("Honolulu"),
		"Pacific/Johnston" => $pacific . __("Johnston"),
		"Pacific/Kiritimati" => $pacific . __("Kiritimati"),
		"Pacific/Kosrae" => $pacific . __("Kosrae"),
		"Pacific/Kwajalein" => $pacific . __("Kwajalein"),
		"Pacific/Majuro" => $pacific . __("Majuro"),
		"Pacific/Marquesas" => $pacific . __("Marquesas"),
		"Pacific/Midway" => $pacific . __("Midway"),
		"Pacific/Nauru" => $pacific . __("Nauru"),
		"Pacific/Niue" => $pacific . __("Niue"),
		"Pacific/Norfolk" => $pacific . __("Norfolk"),
		"Pacific/Noumea" => $pacific . __("Noumea"),
		"Pacific/Pago_Pago" => $pacific . __("Pago Pago"),
		"Pacific/Palau" => $pacific . __("Palau"),
		"Pacific/Pitcairn" => $pacific . __("Pitcairn"),
		"Pacific/Ponape" => $pacific . __("Ponape"),
		"Pacific/Port_Moresby" => $pacific . __("Port Moresby"),
		"Pacific/Rarotonga" => $pacific . __("Rarotonga"),
		"Pacific/Saipan" => $pacific . __("Saipan"),
		"Pacific/Tahiti" => $pacific . __("Tahiti"),
		"Pacific/Tarawa" => $pacific . __("Tarawa"),
		"Pacific/Tongatapu" => $pacific . __("Tongatapu"),
		"Pacific/Truk" => $pacific . __("Truk"),
		"Pacific/Wake" => $pacific . __("Wake"),
		"Pacific/Wallis" => $pacific . __("Wallis"),
	);

	return $timezones;
}