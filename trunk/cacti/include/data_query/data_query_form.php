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

include(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

/* file: data_queries.php, action: edit */
$fields_data_query_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => "Name",
		"description" => "A name for this data query.",
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => "Description",
		"description" => "A description for this data query.",
		"value" => "|arg1:description|",
		"max_length" => "255",
		),
	"xml_path" => array(
		"method" => "textbox",
		"friendly_name" => "XML Path",
		"description" => "The full path to the XML file containing definitions for this data query.",
		"value" => "|arg1:xml_path|",
		"default" => "<path_cacti>/resource/",
		"max_length" => "255",
		),
	"input_type" => array(
		"method" => "drop_array",
		"friendly_name" => "Input Type",
		"description" => "How this data query gets its data whether from a script or using SNMP.",
		"value" => "|arg1:input_type|",
		"array" => $data_query_input_types,
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
		"friendly_name" => "Name",
		"description" => "A name for this associated graph.",
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"graph_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => "Graph Template",
		"description" => "Choose what type of host, host template this is. The host template will govern what kinds of data should be gathered from this type of host.",
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

?>