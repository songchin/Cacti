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




function wizard_settings_array_merge($section) {
	global $settings;
	
	$helper_array = array(
		"db_header" => array(
			"helper_text" => "",
			"page" => "1"
			),
		"db_pconnections" => array(
			"helper_text" => "Enabling this setting will allow mysql and php to pool connections.  This typically will reduce the number off connections to the database.  If you are running Solaris, you should consider disabling this option.",
			"page" => "1"
			),
		"db_retries" => array(
			"helper_text" => "Number of retries before a connection error is returned from mysql.  This setting typically should not exceed 100.", 
			"page" => "1"
			),
		"php_header" => array(
			"helper_text" => "",
			"page" => "2"
			),
		"max_memory" => array(
			"helper_text" => "This is the number of Megabytes of RAM that PHP will use while executing Cacti related tasks.  If you are or are planing on monitoring devices with a large number of metrics, i.e. switch ports in excess of 300, then set this to 64.  Otherwise the default value of 32 will be more than enough.",
			"page" => "2"
			),
		"max_execution_time" => array(
			"helper_text" => "PHP timeout used while executing Cacti related tasks.  Typically not change is needed, the default is 10 seconds.",
			"page" => "2"
			),
		"other_header" => array(
			"helper_text" => "",
			"page" => "3"
			),
		"remove_verification" => array(
			"helper_text" => "Enabling this setting will turn on prompting throughout the user inferface for confirmation on delete actions.",
			"page" => "3"
			),
		"show_hidden" => array(
			"helper_text" => "Enabling this setting will allow you to see some interal cacti settings, mostly related to templates.",
			"page" => "3"
		)
	);

	$output = array();

	while (list($field_name, $field_array) = each($settings[$section])) {
		
		$output[$field_name] = $field_array;
		
		if (isset($helper_array[$field_name])) {
			$output[$field_name]["helper_text"] = $helper_array[$field_name]["helper_text"];
			$output[$field_name]["page"] = $helper_array[$field_name]["page"];
		}

	}

	return $output;

}

function wizard_render($wizard) {
	
	$wizard_settings = wizard_settings_array_merge($wizard);


	switch (wizard_history()) {
		case "1":
			$page = "1";
			break;

		case "2":
			$page = "2";
			break;


		default:
			/* page 0 */
			$page = "0";

	}


	wizard_header($wizard,"80%");
	wizard_start_area();
	print "<input type='hidden' name='next_page' value='" . ($page + 1) . "'>";

	print "Page: " . $page . "<br><br>";	
	
	print "<pre>";
	print_r($wizard_settings);
	print "</pre>"; 

	wizard_end_area();
	wizard_footer(true,false,false,true,"80%");

}

?>
