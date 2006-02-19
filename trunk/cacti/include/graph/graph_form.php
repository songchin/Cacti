<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

$fields_graph = array(
	"title" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"vertical_label" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"image_format" => array(
		"default" => GRAPH_IMAGE_TYPE_PNG,
		"data_type" => DB_TYPE_NUMBER
		),
	"export" => array(
		"default" => "on",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"force_rules_legend" => array(
		"default" => "",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"height" => array(
		"default" => "120",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"width" => array(
		"default" => "500",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"x_grid" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"y_grid" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"y_grid_alt" => array(
		"default" => "",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"no_minor" => array(
		"default" => "",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"auto_scale" => array(
		"default" => "on",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"auto_scale_opts" => array(
		"default" => GRAPH_AUTOSCALE_OPT_AUTOSCALE_MAX,
		"data_type" => DB_TYPE_NUMBER
		),
	"auto_scale_log" => array(
		"default" => "",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"auto_scale_rigid" => array(
		"default" => "",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"auto_padding" => array(
		"default" => "on",
		"validate_regexp" => FORM_VALIDATE_CHECKBOX,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		),
	"upper_limit" => array(
		"default" => "100",
		"validate_regexp" => "^-?[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"lower_limit" => array(
		"default" => "0",
		"validate_regexp" => "^-?[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"base_value" => array(
		"default" => "1000",
		"data_type" => DB_TYPE_NUMBER
		),
	"unit_value" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"unit_length" => array(
		"default" => "9",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"unit_exponent_value" => array(
		"default" => "0",
		"data_type" => DB_TYPE_NUMBER
		)
	);

$fields_graph_item = array(
	"color" => array(
		"default" => "",
		"validate_regexp" => "^[a-fA-F0-9]{6}$",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"graph_item_type" => array(
		"default" => GRAPH_ITEM_TYPE_COMMENT,
		"data_type" => DB_TYPE_NUMBER
		),
	"consolidation_function" => array(
		"default" => 1,
		"data_type" => DB_TYPE_NUMBER
		),
	"cdef" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"gprint_format" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"legend_value" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"legend_format" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"hard_return" => array(
		"default" => "",
		"data_type" => DB_TYPE_NUMBER
		)
	);

/* file: graph_templates.php, action: input_edit */
$fields_graph_template_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("Enter a name for this graph item input, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50"
		),
	"field_name" => array(
		"method" => "drop_array",
		"friendly_name" => _("Field Type"),
		"description" => _("Choose the field type that you want to accept user input for on each graph."),
		"value" => "|arg1:field_name|",
		"array" => array(
			"color" => "Color",
			"graph_item_type" => "Graph Item Type",
			"consolidation_function" => "Consolidation Function",
			"cdef" => "CDEF",
			"gprint_format" => "GPRINT Format",
			"legend_value" => "Legend Value",
			"legend_format" => "Legend Text Format",
			"hard_return" => "Insert Hard Return"
			)
		),
	"save_component_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

?>
