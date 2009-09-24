<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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
$no_http_headers = true;
include(dirname(__FILE__) . "/../../include/global.php");
include_once(dirname(__FILE__) . "/../../lib/functions.php");

//sleep(1);

/* rebuild $lang2locale array to find country and language codes easier */
$locations = array();
foreach($lang2locale as $locale => $properties) {
	$locations[$properties['filename']] = array("flag" => $properties["country"], "language" => $properties["language"], "locale" => $locale);
}

/* create a list of all languages this Cacti system supports ... */
$dhandle = opendir(CACTI_BASE_PATH . "/locales/LC_MESSAGES");
$supported_languages["cacti"][] = "english_dummy";
while (false !== ($filename = readdir($dhandle))) {
	if(isset($locations[$filename])) {
		$supported_languages["cacti"][] = $filename;
	}
}

/* in strict mode we have display languages only supported by Cacti and all installed plugins */
if(read_config_option('i18n_support') == 2){

	$plugins = db_fetch_assoc("SELECT `directory` FROM `plugin_config`");

	if(sizeof($plugins)>0) {
		foreach($plugins as $plugin) {

			$plugin = $plugin["directory"];
			$dhandle = @opendir(CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales/LC_MESSAGES");
			$supported_languages[$plugin][] = "english_dummy";
			if($dhandle) {
				while (false !== ($filename = readdir($dhandle))) {
					if(isset($locations[$filename])) {
						$supported_languages[$plugin][]= $filename;
					}
				}
				/* remove all languages which will not be supported by the plugin */
				$intersect = array_intersect($supported_languages["cacti"], $supported_languages[$plugin]);
				if(sizeof($intersect)>0) {
					$supported_languages["cacti"] = $intersect;
				}
				if (sizeof($supported_languages["cacti"]) == 1) {
					break;
				}
			}else {
				/* no language support */
				$supported_languages["cacti"] = array();
				$supported_languages["cacti"][] = "english_dummy";
				break;
			}
		}
	}
}

$location = $_SERVER['HTTP_REFERER'];

/* clean up from an existing language parameter */
$search = "language=" . $cacti_locale;
$location = str_replace(array( "?" . $search . "&", "?" . $search, "&" . $search), array( "?", "", ""), $location);
$location .= (strpos($location, '?')) ? '&' : '?';

?>
<ul class="down-list" style="list-style:none; display:inline;">
<?php
if(sizeof($supported_languages["cacti"])>0) {
	foreach($supported_languages["cacti"] as $lang) {
		?><li><a href="<?php print $location . "language=" . $locations[$lang]["locale"]; ?>"><img src="<?php echo URL_PATH; ?>images/flag_icons/<?php print $locations[$lang]["flag"];?>.gif" align="top" alt="loading">&nbsp;<?php print $locations[$lang]["language"];?></a>&nbsp;&nbsp;</li><?php
	}
}
?>
</ul>
