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



/* Merges settings array with wizard helper array for render function */
function wizard_settings_array_merge($section) {
	global $settings;
	
	$helper_array = array(
		"db_header" => array(
			"helper_text" => "",
			"page" => "1"
			),
		"db_pconnections" => array(
			"helper_text" => _("Enabling this setting will allow mysql and php to pool connections.  This typically will reduce the number of connections to the database.  If you are running Solaris, you should consider disabling this option."),
			"page" => "1"
			),
		"db_retries" => array(
			"helper_text" => _("Number of retries before a connection error is returned from mysql.  This setting typically should not exceed 100."), 
			"page" => "1"
			),
		"php_header" => array(
			"helper_text" => "",
			"page" => "2"
			),
		"max_memory" => array(
			"helper_text" => _("This is the number of Megabytes of RAM that PHP will use while executing Cacti related tasks.  If you are or are planning on monitoring devices with a large number of metrics, i.e. switch ports in excess of 300, then set this to 64.  Otherwise the default value of 32 will be more than enough."),
			"page" => "2"
			),
		"max_execution_time" => array(
			"helper_text" => _("PHP timeout used while executing Cacti related tasks.  If you are or are planning on monitoring devices with a large number of metrics, i.e. switch ports in excess of 300, then set this to 60 seconds.  Typically not change is needed, the default is 10 seconds."),
			"page" => "2"
			),
		"other_header" => array(
			"helper_text" => "",
			"page" => "3"
			),
		"remove_verification" => array(
			"helper_text" => _("Enabling this setting will turn on prompting throughout the user inferface for confirmation on delete actions."),
			"page" => "3"
			),
		"show_hidden" => array(
			"helper_text" => _("Enabling this setting will allow you to see some interal cacti settings, mostly related to templates."),
			"page" => "3"
		)
	);

	$output = array();

#	while (list($field_name, $field_array) = each($settings[$section])) {
#		
#		$output[$field_name] = $field_array;
#		
#		if (isset($helper_array[$field_name])) {
#			$output[$field_name]["helper_text"] = $helper_array[$field_name]["helper_text"];
#			$output[$field_name]["page"] = $helper_array[$field_name]["page"];
#		}
#
#	}

	while (list($field_name, $field_array) = each($helper_array)) {
		
		#$output[$field_name] = $field_array;
		
		if (isset($settings[$section][$field_name])) {
			$output[$field_name] = array_merge($settings[$section][$field_name],$field_array);
		}

	}


	return $output;

}

/* Renders form and loads saved values from the session, database, default, in that order */
function wizard_render_settings_section($wizard,$page) {

	global $settings;

	$wizard_settings = wizard_settings_array_merge($wizard);

	$form_array = array();

        while (list($field_name, $field_array) = each($wizard_settings)) {
                $form_array += array($field_name => $field_array);

#		print "<pre>";
#		print_r($field_array);
#		print "</pre>";
		
		if ($page = $field_array["page"]) {
                if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
                        while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
                                if (config_value_exists($sub_field_name)) {
                                        $form_array[$field_name]["items"][$sub_field_name]["form_id"] = 1;
                                }

                                $form_array[$field_name]["items"][$sub_field_name]["value"] = db_fetch_cell("select value from settings where name='$sub_field_name'");
                        }
                }else{
                        if (config_value_exists($field_name)) {
                                $form_array[$field_name]["form_id"] = 1;
                        }

                        $form_array[$field_name]["value"] = db_fetch_cell("select value from settings where name='$field_name'");
                }
		}
        }

#        print "<pre>";
#	print_r($settings["general"]);
#	print "</pre>";



#       print "<pre>";
#	print_r($form_array);
#	print "</pre>";




        draw_edit_form(
                array(
                        "config" => array(),
                        "fields" => $form_array)
                        );



}

/* Page save of values from previous page to session */
function wizard_save_settings_section($wizard,$page) {




}

/* Final save to database from session */
function wizard_save_commit($wizard) {



}

/* Render the html pages of the wizards */
function wizard_render($wizard) {
	
	$page = wizard_history();

	wizard_header($wizard,"80%");
	wizard_start_area();


	switch ($page) {

		case "0":
			/* page 0 */
			wizard_render_settings_section($wizard,$page);



		case "1":
			break;

		case "2":
			break;

		case "3":
			break;

	}

	wizard_end_area();
	wizard_footer(true,false,false,true,"80%");
}

?>
