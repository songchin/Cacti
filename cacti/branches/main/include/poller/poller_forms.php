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

require(CACTI_BASE_PATH . "/include/poller/poller_arrays.php");

/* file: pollers.php, action: edit */
$fields_poller_edit = array(
	"device_header" => array(
		"method" => "spacer",
		"friendly_name" => __("General Poller Options")
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => __("Description"),
		"description" => __("Give this poller a meaningful description."),
		"value" => "|arg1:description|",
		"max_length" => "250",
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => __("Hostname"),
		"description" => __("Fully qualified hostname of the poller device."),
		"value" => "|arg1:hostname|",
		"max_length" => "250",
		),
	"ip_address" => array(
		"method" => "textbox",
		"friendly_name" => __("IP Address"),
		"description" => __("The IP Address of this poller for status checking."),
		"value" => "|arg1:ip_address|",
		"max_length" => "250",
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => __("Disabled"),
		"description" => __("Check this box if you wish for this poller to be disabled."),
		"value" => "|arg1:disabled|",
		"default" => ""
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_poller" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);
