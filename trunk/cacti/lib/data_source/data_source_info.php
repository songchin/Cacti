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

/* get_data_source_title - returns the title of a data source without using the title cache unless the title ends up empty.
   @arg $data_source_id - (int) the ID of the data source to get a title for
   @returns - the data source title */
function get_data_source_title($data_source_id, $remove_unsubstituted_variables = false) {
	include_once(CACTI_BASE_PATH . "/lib/variables.php");

	$data_source = db_fetch_row("select host_id,name,name_cache from data_source where id = $data_source_id");

	$title = $data_source["name"];

	if ((strstr($data_source["name"], "|host_")) && (!empty($data_source["host_id"]))) {
		$title = substitute_host_variables($title, $data_source["host_id"]);
	}

	if ((strstr($data_source["name"], "|query_")) && (!empty($data_source["host_id"]))) {
		$data_query = array_rekey(db_fetch_assoc("select
			data_source_field.name,
			data_source_field.value
			from data_source_field,data_source
			where data_source.id=data_source_field.data_source_id
			and data_source.id = $data_source_id"), "name", "value");

		if ((isset($data_query["data_query_id"])) && (isset($data_query["data_query_index"]))) {
			$title = substitute_data_query_variables($title, $data_source["host_id"], $data_query["data_query_id"], $data_query["data_query_index"], read_config_option("max_data_query_field_length"));
		}
	}

	if ($remove_unsubstituted_variables == true) {
		$title = remove_variables($title);
	}

	if (((empty($title)) || (substr_count($title,"|"))) && (!empty($data_source["name_cache"]))) {
		$title = $data_source["name_cache"];
	}

	return $title;
}

/* get_data_source_path - returns the full path to the .rrd file associated with a given data source
   @arg $data_source_id - (int) the ID of the data source
   @arg $expand_paths - (bool) whether to expand the <path_rra> variable into its full path or not
   @returns - the full path to the data source or an empty string for an error */
function get_data_source_path($data_source_id, $expand_paths) {
	include_once(CACTI_BASE_PATH . "/lib/variables.php");
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");

	$current_path = db_fetch_cell("select rrd_path from data_source where id = $data_source_id");

	/* generate a new path if needed */
	if ($current_path == "") {
		$current_path = update_data_source_path($data_source_id);
	}

	if ($expand_paths == true) {
		return substitute_path_variables($current_path);
	}else{
		return $current_path;
	}
}

function &get_data_source_field_list() {
	include(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");

	return $fields_data_source;
}

function &get_data_source_item_field_list() {
	include(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");

	return $fields_data_source_item;
}

?>