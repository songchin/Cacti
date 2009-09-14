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

//include_once(CACTI_BASE_PATH . "/include/poller/poller_constants.php");

/* file: pollers.php, action: edit */
$fields_poller_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Poller Name"),
		"description" => _("Enter a meaningful name for this poller."),
		"value" => "|arg1:name|",
		"max_length" => "255"
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => _("Hostname"),
		"description" => _("Enter the IP address or hostname of this poller."),
		"value" => "|arg1:hostname|",
		"max_length" => "255"
		),
	"active" => array(
		"method" => "checkbox",
		"friendly_name" => _("Poller Active"),
		"description" => _("Whether or not this data poller is to be used."),
		"default" => "",
		"value" => "|arg1:active|",
		"form_id" => false
		),
	"poller_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_data_poller" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

?>
