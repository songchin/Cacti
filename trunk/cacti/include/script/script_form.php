<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

include_once(CACTI_BASE_PATH . "/include/script/script_constants.php");

$fields_script = array(
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"type_id" => array(
		"default" => SCRIPT_INPUT_TYPE_SCRIPT,
		"data_type" => DB_TYPE_INTEGER
		),
	"input_string" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

$fields_script_fields = array(
	"field_input_type" => array(
		"default" => SCRIPT_FIELD_INPUT_CUSTOM,
		"data_type" => DB_TYPE_INTEGER
		),
	"field_input_value" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"data_name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"input_output" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"update_rrd" => array(
		"default" => "1",
		"data_type" => DB_TYPE_INTEGER
		),
	"regexp_match" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"allow_empty" => array(
		"default" => "0",
		"data_type" => DB_TYPE_INTEGER
		)
	);

?>
