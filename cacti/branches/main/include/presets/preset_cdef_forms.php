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

/* file: cdef.php, action: edit */
$fields_cdef_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this CDEF."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "60"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_cdef" => array(
		"method" => "hidden",
		"value" => "1"
		),
	);

$fields_cdef_item_edit = array(
	"sequence" => "sequence",
	"type" => "type",
	"value" => "value"
	);
