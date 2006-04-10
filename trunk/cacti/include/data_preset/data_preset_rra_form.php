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

$fields_data_preset_rra = array(
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

$fields_data_preset_rra_item = array(
	"consolidation_function" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"steps" => array(
		"default" => "1",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"rows" => array(
		"default" => "0",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"x_files_factor" => array(
		"default" => "0.5",
		"validate_regexp" => "^[0-9]+(\.[0-9]+)$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_alpha" => array(
		"default" => "0.1",
		"validate_regexp" => "^[0-9]+(\.[0-9]+)$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_beta" => array(
		"default" => "0.0035",
		"validate_regexp" => "^[0-9]+(\.[0-9]+)$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_gamma" => array(
		"default" => "0.1",
		"validate_regexp" => "^[0-9]+(\.[0-9]+)$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_seasonal_period" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_rra_num" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_threshold" => array(
		"default" => "7",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		),
	"hw_window_length" => array(
		"default" => "9",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_NUMBER
		)
	);

?>
