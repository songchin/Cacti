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

require(CACTI_BASE_PATH . "/include/presets/preset_rra_arrays.php");

/* file: rra.php, action: edit */
$fields_rra_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("How data is to be entered in RRA's."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
		),
	"consolidation_function_id" => array(
		"method" => "drop_multi",
		"friendly_name" => __("Consolidation Functions"),
		"description" => __("How data is to be entered in RRA's."),
		"array" => $consolidation_functions,
		"sql" => "select consolidation_function_id as id,rra_id from rra_cf where rra_id=|arg1:id|",
		),
	"x_files_factor" => array(
		"method" => "textbox",
		"friendly_name" => __("X-Files Factor"),
		"description" => __("The amount of unknown data that can still be regarded as known."),
		"value" => "|arg1:x_files_factor|",
		"max_length" => "10",
		"size" => "10"
		),
	"steps" => array(
		"method" => "textbox",
		"friendly_name" => __("Steps"),
		"description" => __("How many data points are needed to put data into the RRA."),
		"value" => "|arg1:steps|",
		"max_length" => "8",
		"size" => "10"
		),
	"rows" => array(
		"method" => "textbox",
		"friendly_name" => __("Rows"),
		"description" => __("How many generations data is kept in the RRA."),
		"value" => "|arg1:rows|",
		"max_length" => "12",
		"size" => "10"
		),
	"timespan" => array(
		"method" => "textbox",
		"friendly_name" => __("Timespan"),
		"description" => __("How many seconds to display in graph for this RRA."),
		"value" => "|arg1:timespan|",
		"max_length" => "12",
		"size" => "10"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_rra" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);
