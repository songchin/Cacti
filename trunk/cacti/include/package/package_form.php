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

$fields_package = array(
	"author_name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"author_email" => array(
		"default" => "",
		"validate_regexp" => "^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[_A-Za-z0-9-]+)",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"author_user_forum" => array(
		"default" => "",
		"validate_regexp" => "^[^\"]{1,25}$",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"author_user_repository" => array(
		"default" => "",
		"validate_regexp" => "^[^\"]{1,25}$",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"description" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"description_install" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"category" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"subcategory" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"vendor" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"model" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		)
	);

$fields_package_metadata = array(
	"type" => array(
		"default" => "",
		"data_type" => DB_TYPE_NUMBER
		),
	"name" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_STRING
		),
	"description" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"description_install" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => true,
		"data_type" => DB_TYPE_STRING
		),
	"required" => array(
		"default" => "1",
		"data_type" => DB_TYPE_NUMBER
		),
	"payload" => array(
		"default" => "",
		"validate_regexp" => "",
		"validate_empty" => false,
		"data_type" => DB_TYPE_BLOB
		)
	);

?>
