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

function &api_data_query_list_input_types() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

	return $data_query_input_types;
}

function &api_data_query_list_index_sort_types() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");

	return $data_query_index_sort_types;
}

function &api_data_query_list_fields() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_form.php");

	return $fields_data_query;
}

function &api_data_query_fields_list_fields() {
	require(CACTI_BASE_PATH . "/include/data_query/data_query_form.php");

	return $fields_data_query_fields;
}

?>
