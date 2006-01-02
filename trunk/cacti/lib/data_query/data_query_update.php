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

function api_data_query_save($data_query_id, &$_fields_data_query) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* sanity check for $data_query_id */
	if (!is_numeric($data_query_id)) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_query_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_query, api_data_query_field_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("data_query", $_fields, array("id"))) {
		if (empty($data_query_id)) {
			return db_fetch_insert_id();
		}else{
			return $data_query_id;
		}
	}else{
		return false;
	}
}

function api_data_query_field_save($data_query_field_id, &$_fields_data_query_fields) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* sanity check for $data_query_field_id */
	if (!is_numeric($data_query_field_id)) {
		return false;
	}

	/* sanity check for $data_query_id */
	if ((empty($data_query_field_id)) && (empty($_fields_data_query_fields["data_query_id"]))) {
		api_log_log("Required data_query_id when data_query_field_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_data_query_fields["data_query_id"])) && (!is_numeric($_fields_data_query_fields["data_query_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_query_field_id);

	/* field: graph_id */
	if (!empty($_fields_data_query_fields["data_query_id"])) {
		$_fields["data_query_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_data_query_fields["data_query_id"]);
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_data_query_fields, api_data_query_field_field_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("data_query_field", $_fields, array("id"))) {
		if (empty($data_query_field_id)) {
			return db_fetch_insert_id();
		}else{
			return $data_query_field_id;
		}
	}else{
		return false;
	}
}

function api_data_query_remove($data_query_id) {
	/* sanity checks */
	validate_id_die($data_query_id, "data_query_id");

	db_execute("delete from data_query_field where data_query_id = " . sql_sanitize($data_query_id));
	db_execute("delete from data_query where id = " . sql_sanitize($data_query_id));

	db_execute("delete from host_data_query where data_query_id = " . sql_sanitize($data_query_id));
	db_execute("delete from host_template_data_query where data_query_id = " . sql_sanitize($data_query_id));
	db_execute("delete from host_data_query_cache where data_query_id = " . sql_sanitize($data_query_id));
}

/* update_data_query_sort_cache - updates the sort cache for a particular host/data query
	combination. this works by fetching a list of valid data query index types and choosing
	the first one in the list. the user can optionally override how the cache is updated
	in the data query xml file
   @arg $host_id - the id of the host which contains the data query
   @arg $data_query_id - the id of the data query update the sort cache for */
function update_data_query_sort_cache($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* sanity check for $host_id */
	if ((!is_numeric($host_id)) || (empty($host_id))) {
		api_log_log("Invalid input '$host_id' for 'host_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	/* sanity check for $data_query_id */
	if ((!is_numeric($data_query_id)) || (empty($data_query_id))) {
		api_log_log("Invalid input '$data_query_id' for 'data_query_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}

	/* retrieve information about this data query */
	$data_query = api_data_query_get($data_query_id);

	/* get a list of valid data query types */
	$valid_index_types = get_ordered_index_type_list($data_query_id, $host_id);

	/* something is probably wrong with the data query */
	if (sizeof($valid_index_types) == 0) {
		$sort_field = "";
	}else{
		/* grab the first field off the list */
		list($sort_field, $sort_field_formatted) = each($valid_index_types);
	}

	/* substitute variables */
	if (isset($raw_xml["index_title_format"])) {
		$title_format = str_replace("|chosen_order_field|", "|query_$sort_field|", $data_query["index_title_format"]);
	}else{
		$title_format = "|query_$sort_field|";
	}

	/* update the cache */
	db_update("host_data_query",
		array(
			"sort_field" => array("type" => DB_TYPE_STRING, "value" => $sort_field),
			"title_format" => array("type" => DB_TYPE_STRING, "value" => $title_format),
			"host_id" => array("type" => DB_TYPE_NUMBER, "value" => $host_id),
			"data_query_id" => array("type" => DB_TYPE_NUMBER, "value" => $data_query_id)
			),
		array("host_id", "data_query_id"));
}

/* update_data_query_sort_cache_by_host - updates the sort cache for all data queries associated
	with a particular host. see update_data_query_sort_cache() for details about updating the cache
   @arg $host_id - the id of the host to update the cache for */
function update_data_query_sort_cache_by_host($host_id) {
	$data_queries = db_fetch_assoc("select data_query_id from host_data_query where host_id = " . sql_sanitize($host_id));

	if (sizeof($data_queries) > 0) {
		foreach ($data_queries as $data_query) {
			update_data_query_sort_cache($host_id, $data_query["snmp_query_id"]);
		}
	}
}

?>
