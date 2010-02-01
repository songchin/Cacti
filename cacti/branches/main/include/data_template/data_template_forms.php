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

/* file: data_templates.php, action: template_edit */
$fields_data_template_template_edit = array(
	"template_name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("The name given to this data template."),
		"value" => "|arg1:name|",
		"max_length" => "150",
		),
	"description" => array(
		"method" => "textarea",
		"friendly_name" => __("Description"),
		"description" => __("Additional details relative this template."),
		"value" => "|arg1:description|",
		"textarea_rows" => "5",
		"textarea_cols" => "60",
		"class" => "textAreaNotes"
		),
	"data_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:data_template_id|"
		),
	"data_template_data_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:id|"
		),
	"current_rrd" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:view_rrd|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);
