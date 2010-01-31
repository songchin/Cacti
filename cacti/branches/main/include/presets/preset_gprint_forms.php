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

/* file: grprint_presets.php, action: edit */
$fields_grprint_presets_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("Enter a name for this GPRINT preset, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50",
		),
	"gprint_text" => array(
		"method" => "textbox",
		"friendly_name" => __("GPRINT Text"),
		"description" => __("Enter the custom GPRINT string here."),
		"value" => "|arg1:gprint_text|",
		"max_length" => "50",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_gprint_presets" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);
