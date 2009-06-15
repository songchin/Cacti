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

sleep(1);

if(!isset($_GET['location'])) {
return;
}

/* create a list of all languages this Cacti system supports ... */
$dhandle = opendir(CACTI_BASE_PATH . "/locales");
$supported_languages["cacti"]["us"] = "English";
while (false !== ($dirname = readdir($dhandle))) {
	$catalogue = CACTI_BASE_PATH . "/locales/" . $dirname . "/LC_MESSAGES/cacti.mo"; 
	if(file_exists($catalogue)) {
		$dirname = strtolower(substr($dirname, 3, 2));
		if(isset($lang2locale[$dirname])) {
			$supported_languages["cacti"][$dirname] = $lang2locale[$dirname]['language'];
		}
	}
}

/* in strict mode we have display languages only supported by Cacti and all installed plugins */
if(read_config_option('i18n_support') == 2){

	$plugins = db_fetch_assoc("SELECT `directory` FROM `plugin_config`");

	if(sizeof($plugins)>0) {
		foreach($plugins as $plugin) {

			$plugin = $plugin["directory"];
			$dhandle = @opendir(CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales");
			$supported_languages[$plugin]["us"] = "English";
			if($dhandle) {
				while (false !== ($dirname = readdir($dhandle))) {
					$catalogue = CACTI_BASE_PATH . "/plugins/" . $plugin . "/locales/" . $dirname . "/LC_MESSAGES/" . $plugin . ".mo";
					if(file_exists($catalogue)) {
						$dirname = strtolower($dirname, 3, 2);
						if(isset($lang2locale[$dirname])) {
							$supported_languages[$plugin][$dirname]= $lang2locale[$dirname]['language'];
						}
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
				$supported_languages["cacti"]["us"] = "English";
				break; 
			}
		}
	}
}

$location = $_GET['location'];
$location .= (strpos($location, '?')) ? '&' : '?';

?>
<ul class="down-list" style="list-style:none; display:inline;">
<?php
if(sizeof($supported_languages["cacti"])>0) {
	foreach($supported_languages["cacti"] as $code => $language) {
		?><li><img src="<?php echo URL_PATH; ?>images/flag_icons/<?php print $code;?>.gif" align="top" alt="loading" style='border-width:0px;'><a href="<?php print $location . "language=" . $code; ?>">&nbsp;<?php print $language;?></a>&nbsp;&nbsp;</li><?php
	}
}else{
	$system_language = getenv('LANG');
	print "<li><a href=\"" . $location . "language=" . $system_language . "\">" . $lang2locale[$system_language]['language'] . "</a></li>";
}
?>
</ul>