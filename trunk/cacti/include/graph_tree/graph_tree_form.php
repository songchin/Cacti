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

$fields_graph_tree = array(
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"sort_type" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_INTEGER
		)
	);

$fields_graph_tree_item = array(
	"device_grouping_type" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_INTEGER
		),
	"sort_children_type" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_INTEGER
		),
	"item_type" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_INTEGER
		),
	"item_value" => array(
		"default" => "",
		"validate_regexp" => "^[0-9]+$",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		)
	);

?>
