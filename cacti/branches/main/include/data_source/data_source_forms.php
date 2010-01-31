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

require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source = array(
	"name" => array(
		"friendly_name" => __("Name"),
		"method" => "textbox",
		"max_length" => "250",
		"default" => "",
		"description" => __("Choose a name for this data source."),
		"flags" => ""
		),
	"data_source_path" => array(
		"friendly_name" => __("Data Source Path"),
		"method" => "textbox",
		"max_length" => "255",
		"size" => "70",
		"default" => "",
		"description" => __("The full path to the RRD file."),
		"flags" => "NOTEMPLATE"
		),
	"data_input_id" => array(
		"friendly_name" => __("Data Input Method"),
		"method" => "drop_sql",
		"sql" => "select id,name from data_input order by name",
		"default" => "",
		"none_value" => "None",
		"description" => __("The script/source used to gather data for this data source."),
		"flags" => "ALWAYSTEMPLATE"
		),
	"rra_id" => array(
		"method" => "drop_multi_rra",
		"friendly_name" => __("Associated RRA's"),
		"description" => __("Which RRA's to use when entering data. (It is recommended that you select all of these values)."),
		"form_id" => "|arg1:id|",
		"sql" => "select rra_id as id,data_template_data_id from data_template_data_rra where data_template_data_id=|arg1:id|",
		"sql_all" => "select rra.id from rra order by id",
		"sql_print" => "select rra.name from (data_template_data_rra,rra) where data_template_data_rra.rra_id=rra.id and data_template_data_rra.data_template_data_id=|arg1:id|",
		"flags" => "ALWAYSTEMPLATE"
		),
	"rrd_step" => array(
		"friendly_name" => __("Step"),
		"method" => "textbox",
		"max_length" => "10",
		"size" => "20",
		"default" => "300",
		"description" => __("The amount of time in seconds between expected updates."),
		"flags" => ""
		),
	"active" => array(
		"friendly_name" => __("Data Source Template Active"),
		"method" => "checkbox",
		"default" => CHECKED,
		"description" => __("Whether Cacti should gather data for this class of Data Sources."),
		"flags" => ""
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source_item = array(
	"data_source_name" => array(
		"friendly_name" => __("Internal Data Source Name"),
		"method" => "textbox",
		"max_length" => "19",
		"size" => "20",
		"default" => "",
		"description" => __("Choose unique name to represent this piece of data inside of the rrd file."),
		),
	"rrd_minimum" => array(
		"friendly_name" => __("Minimum Value"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"class" => "DS_std",
		"description" => __("The minimum value of data that is allowed to be collected."),
		),
	"rrd_maximum" => array(
		"friendly_name" => __("Maximum Value"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"class" => "DS_std",
		"description" => __("The maximum value of data that is allowed to be collected."),
		),
	"data_source_type_id" => array(
		"friendly_name" => __("Data Source Type"),
		"method" => "drop_array",
		"array" => $data_source_types,
		"default" => "",
		"description" => __("How data is represented in the RRA."),
		),
	"rrd_compute_rpn" => array(
		"friendly_name" => __("RPN for a COMPUTE DS Item Type (RRDTool 1.2.x and above)"),
		"method" => "textbox",
		"max_length" => "150",
		"size" => "30",
		"default" => "",
		"class" => "DS_compute",
		"description" => __("When using a COMPUTE data source type, please enter the RPN for it here.") . "<br>" .
						__("Available for RRDTool 1.2.x and above"),
		),
	"rrd_heartbeat" => array(
		"friendly_name" => __("Heartbeat"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "600",
		"description" => __("The maximum amount of time that can pass before data is entered as 'unknown'.") . "<br>" .
						__("(Usually 2x300=600)"),
		),
	"data_input_field_id" => array(
		"friendly_name" => __("Output Field"),
		"method" => "drop_sql",
		"default" => "0",
		"description" => __("When data is gathered, the data for this field will be put into this data source."),
		),
	);
