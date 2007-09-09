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

//include(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

$fields_data_query = array(
	"input_type" => array(
		"default" => "",
		"data_type" => DB_TYPE_INTEGER
		),
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"index_order_type" => array(
		"default" => "",
		"data_type" => DB_TYPE_INTEGER
		),
	"index_title_format" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"index_order" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"index_field_id" => array(
		"default" => "",
		"data_type" => DB_TYPE_INTEGER
		),
	"snmp_oid_num_rows" => array(
		"default" => "",
		"validate_regexp" => "^\.?([0-9]+\.?)+$",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"script_path" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"script_server_function" => array(
		"default" => "",
		"validate_regexp" => "^[a-zA-Z0-9_-]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

$fields_data_query_fields = array(
	"type" => array(
		"default" => "",
		"data_type" => DB_TYPE_INTEGER
		),
	"name" => array(
		"default" => "",
		"validate_regexp" => "^[a-zA-Z0-9_-]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"name_desc" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"source" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"method_type" => array(
		"default" => "",
		"data_type" => DB_TYPE_INTEGER
		),
	"method_value" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		)
	);

/* file: data_queries.php, action: edit */
/*
$fields_data_query_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("A name for this data query."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => _("Description"),
		"description" => _("A description for this data query."),
		"value" => "|arg1:description|",
		"max_length" => "255",
		),
	"xml_path" => array(
		"method" => "textbox",
		"friendly_name" => _("XML Path"),
		"description" => _("The full path to the XML file containing definitions for this data query."),
		"value" => "|arg1:xml_path|",
		"default" => "<path_cacti>/resource/",
		"max_length" => "255",
		),
	"input_type" => array(
		"method" => "drop_array",
		"friendly_name" => _("Input Type"),
		"description" => _("How this data query gets its data whether from a script or using SNMP."),
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
*/
/* file: data_queries.php, action: item_edit */
/*
$fields_data_query_item_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("A name for this associated graph."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		),
	"graph_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => _("Graph Template"),
		"description" => _("Choose what type of host, host template this is. The host template will govern what kinds of data should be gathered from this type of host."),
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
*/
?>
