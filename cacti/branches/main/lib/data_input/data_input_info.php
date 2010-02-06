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

function &data_input_form_list() {
	require(CACTI_BASE_PATH . "/include/data_input/data_input_forms.php");

	return $fields_data_input_edit;
}

function &data_input_field_form_list() {
	require(CACTI_BASE_PATH . "/include/data_input/data_input_forms.php");

	return $fields_data_input_field_edit;
}

function &data_input_field1_form_list() {
	require(CACTI_BASE_PATH . "/include/data_input/data_input_forms.php");

	return $fields_data_input_field_edit_1;
}

function &data_input_field2_form_list() {
	require(CACTI_BASE_PATH . "/include/data_input/data_input_forms.php");

	return $fields_data_input_field_edit_2;
}
