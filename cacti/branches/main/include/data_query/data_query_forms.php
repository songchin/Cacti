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

/* file: data_queries.php, action: edit */
$fields_data_query_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A name for this data query."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
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
	"image" => array(
		"method" => "drop_image",
		"path" => "images/tree_icons",
		"friendly_name" => __("Image"),
		"description" => __("A useful icon to use to associate with this Device Template."),
		"default" => "dataquery.png",
		"width" => "120",
		"value" => "|arg1:image|"
		),
	"xml_path" => array(
		"method" => "textbox",
		"friendly_name" => __("XML Path"),
		"description" => __("The full path to the XML file containing definitions for this data query."),
		"value" => "|arg1:xml_path|",
		"default" => "<path_cacti>/resource/",
		"max_length" => "255",
		"size" => "70"
		),
	"data_input_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Data Input Method"),
		"description" => __("Choose what type of device, device template this is. The device template will govern what kinds of data should be gathered from this type of device."),
		"value" => "|arg1:data_input_id|",
		"sql" => "select id,name from data_input where (type_id=3 or type_id=4 or type_id=5 or type_id=6) order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|",
		),
	"save_component_snmp_query" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_queries.php, action: item_edit */
$fields_data_query_item_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A name for this associated graph."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
		),
	"graph_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Graph Template"),
		"description" => __("Choose what type of device, device template this is. The device template will govern what kinds of data should be gathered from this type of device."),
		"value" => "|arg1:graph_template_id|",
		"sql" => "select id,name from graph_templates order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"snmp_query_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:snmp_query_id|"
		),
	"_graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:graph_template_id|"
		),
	"save_component_snmp_query_item" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);
