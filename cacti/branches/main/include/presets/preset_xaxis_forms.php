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

require(CACTI_BASE_PATH . "/include/presets/preset_xaxis_arrays.php");

/* file: xaxis.php, action: edit */
$fields_xaxis_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this X-Axis Preset."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "60"
		),
	);

/* file: xaxis.php, action: item_edit */
$fields_xaxis_item_edit = array(
	"item_name" => array(
		"method" => "textbox",
		"friendly_name" => __("Item Name"),
		"description" => __("Item name, just for your convenience."),
		"value" => "|arg1:item_name|",
		"max_length" => "100",
		"size" => "30"
		),
	"timespan" => array(
		"method" => "textbox",
		"friendly_name" => __("Timespan"),
		"description" => __("If the Graph's Timespan is lower than this value, the related set of X-Axis Parameters will be applied."),
		"value" => "|arg1:timespan|",
		"max_length" => "12",
		"size" => "12"
		),
	"gtm" => array(
		"method" => "drop_array",
		"friendly_name" => __("Global Grid Timespan (--x-axis GTM)"),
		"description" => __("The Timespan which applies to the global grid."),
		"value" => "|arg1:gtm|",
		"array" => $rrd_xaxis_timespans,
		),
	"gst" => array(
		"method" => "textbox",
		"friendly_name" => __("Global Grid Steps (--x-axis GST)"),
		"description" => __("Steps for global grid."),
		"value" => "|arg1:gst|",
		"max_length" => "4",
		"size" => "4"
		),
	"mtm" => array(
		"method" => "drop_array",
		"friendly_name" => __("Major Grid Timespan (--x-axis MTM)"),
		"description" => __("The Timespan which applies to the major grid."),
		"value" => "|arg1:mtm|",
		"array" => $rrd_xaxis_timespans,
		),
	"mst" => array(
		"method" => "textbox",
		"friendly_name" => __("Major Grid Steps (--x-axis MST)"),
		"description" => __("Steps for major grid."),
		"value" => "|arg1:mst|",
		"max_length" => "4",
		"size" => "4"
		),
	"ltm" => array(
		"method" => "drop_array",
		"friendly_name" => __("Label Grid Timespan (--x-axis LTM)"),
		"description" => __("The Timespan which applies to the label grid."),
		"value" => "|arg1:ltm|",
		"array" => $rrd_xaxis_timespans,
		),
	"lst" => array(
		"method" => "textbox",
		"friendly_name" => __("Label Grid Steps (--x-axis LST)"),
		"description" => __("Steps for label grid."),
		"value" => "|arg1:lst|",
		"max_length" => "4",
		"size" => "4"
		),
	"lpr" => array(
		"method" => "textbox",
		"friendly_name" => __("Relative Label Position (--x-axis LPR)"),
		"description" => __("The position of the label with respect to the label grid."),
		"value" => "|arg1:lpr|",
		"default" => 0,
		"max_length" => "12",
		"size" => "12"
		),
	"lfm" => array(
		"method" => "textbox",
		"friendly_name" => __("Label Format (--x-axis LFM)"),
		"description" => __("A format string for the label."),
		"value" => "|arg1:lfm|",
		"max_length" => "100",
		"size" => "30"
		),
	);
