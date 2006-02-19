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

include(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
include(CACTI_BASE_PATH . "/include/global_form.php");

$fields_data_template = array(
	"template_name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

$fields_data_source = array(
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"data_input_type" => array(
		"default" => "3",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"rrd_path" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"rra_id" => array(
		"default" => "",
		"data_type" => DB_TYPE_STRING
		),
	"rrd_step" => array(
		"default" => "300",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"active" => array(
		"default" => "on",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_HTML_CHECKBOX
		)
	);

$fields_data_source_item = array(
	"data_source_name" => array(
		"default" => "",
		"validate_regexp" => "^[a-zA-Z0-9_]{1,19}$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"rrd_minimum" => array(
		"default" => "0",
		"validate_regexp" => "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"rrd_maximum" => array(
		"default" => "0",
		"validate_regexp" => "^(-?([0-9]+(\.[0-9]*)?|[0-9]*\.[0-9]+)([eE][+\-]?[0-9]+)?)|U$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"data_source_type" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => true,
		"data_type" => DB_TYPE_NUMBER
		),
	"rrd_heartbeat" => array(
		"default" => "600",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"field_input_value" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

?>
