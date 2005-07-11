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

function api_data_source_save($data_source_id, &$_fields_data_source, &$_fields_data_source_rra, $skip_cache_update = false) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* sanity check for $data_source_id */
	if (!is_numeric($data_source_id)) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_source_id);

	/* field: data_template_id */
	if (isset($_fields_data_source["data_template_id"])) {
		$_fields["data_template_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_data_source["data_template_id"]);
	}

	/* field: host_id */
	if (isset($_fields_data_source["host_id"])) {
		$_fields["host_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_data_source["host_id"]);
	}

	/* fetch a list of all visible data source fields */
	$fields_data_source = get_data_source_field_list();

	foreach (array_keys($fields_data_source) as $field_name) {
		if (isset($_fields_data_source[$field_name])) {
			$_fields[$field_name] = array("type" => $fields_data_source[$field_name]["data_type"], "value" => $_fields_data_source[$field_name]);
		}
	}

	if (db_replace("data_source", $_fields, array("id"))) {
		$data_source_id = db_fetch_insert_id();

		if (sizeof($_fields_data_source_rra) > 0) {
			/* save entries in 'selected rras' field */
			db_execute("delete from data_source_rra where data_source_id = " . sql_sanitize($data_source_id));

			foreach ($_fields_data_source_rra as $rra_id) {
				db_replace("data_source_rra",
					array(
						"rra_id" => array("type" => DB_TYPE_NUMBER, "value" => $rra_id),
						"data_source_id" => array("type" => DB_TYPE_NUMBER, "value" => $data_source_id)
						),
					array("rra_id", "data_source_id"));
			}
		}

		if ($skip_cache_update == false) {
			/* update data source title cache */
			update_data_source_title_cache($data_source_id);
		}

		return true;
	}else{
		return false;
	}
}

function api_data_source_fields_save($data_source_id, &$_fields_data_input) {
	include_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	/* sanity check for $data_source_id */
	if ((!is_numeric($data_source_id)) || (empty($data_source_id))) {
		return false;
	}

	/* flush old fields if the data input type is not a data query */
	if (db_fetch_cell("select data_input_type from data_source where id = " . sql_sanitize($data_source_id)) != DATA_INPUT_TYPE_DATA_QUERY) {
		db_execute("delete from data_source_field where data_source_id = " . sql_sanitize($data_source_id));
	}

	/* save all data input fields */
	reset($_fields_data_input);
	foreach ($_fields_data_input as $field_name => $field_value) {
		db_replace("data_source_field",
			array(
				"data_source_id" => array("type" => DB_TYPE_NUMBER, "value" => $data_source_id),
				"name" => array("type" => DB_TYPE_STRING, "value" => $field_name),
				"value" => array("type" => DB_TYPE_STRING, "value" => $field_value)
				),
			array("data_source_id", "name"));
	}

	return true;
}

function api_data_source_remove($data_source_id) {
	if ((empty($data_source_id)) || (!is_numeric($data_source_id))) {
		return;
	}

	db_execute("delete from data_source_field where data_source_id = $data_source_id");
	db_execute("delete from data_source_item where data_source_id = $data_source_id");
	db_execute("delete from data_source_rra where data_source_id = $data_source_id");
	db_execute("delete from data_source where id = $data_source_id");
}

function api_data_source_enable($data_source_id) {
	db_execute("UPDATE data_source SET active=1 WHERE id=$data_source_id");
	update_poller_cache($data_source_id, false);
}

function api_data_source_disable($data_source_id) {
	db_execute("DELETE FROM poller_item WHERE local_data_id=$data_source_id");
	db_execute("UPDATE data_source SET active='' WHERE id=$data_source_id");
}

function api_data_source_item_save($data_source_item_id, &$_fields_data_source_item) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	/* sanity check for $data_source_item_id */
	if (!is_numeric($data_source_item_id)) {
		return false;
	}

	/* sanity check for $data_source_id */
	if ((empty($data_source_item_id)) && (empty($_fields_data_source_item["data_source_id"]))) {
		api_syslog_cacti_log("Required data_source_id when data_source_item_id = 0", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	} else if ((isset($_fields_data_source_item["data_source_id"])) && (!is_numeric($_fields_data_source_item["data_source_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $data_source_item_id);

	/* field: data_source_id */
	if (!empty($_fields_data_source_item["data_source_id"])) {
		$_fields["data_source_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_data_source_item["data_source_id"]);
	}

	/* field: field_input_value */
	if (isset($_fields_data_source_item["field_input_value"])) {
		$_fields["field_input_value"] = array("type" => DB_TYPE_STRING, "value" => $_fields_data_source_item["field_input_value"]);
	}

	/* fetch a list of all visible data source item fields */
	$fields_data_source_item = get_data_source_item_field_list();

	foreach (array_keys($fields_data_source_item) as $field_name) {
		if (isset($_fields_data_source_item[$field_name])) {
			$_fields[$field_name] = array("type" => $fields_data_source_item[$field_name]["data_type"], "value" => $_fields_data_source_item[$field_name]);
		}
	}

	if (db_replace("data_source_item", $_fields, array("id"))) {
		if (!empty($_fields_data_source_item["data_source_id"])) {
			/* since the data source path is based in part on the data source item name, it makes sense
			 * to update it here */
			update_data_source_path($_fields_data_source_item["data_source_id"]);
		}

		return true;
	}else{
		return false;
	}
}

function api_data_source_item_remove($data_source_item_id) {
	if ((empty($data_source_item_id)) || (!is_numeric($data_source_item_id))) {
		return;
	}

	db_execute("delete from data_source_item where id = $data_source_item_id");
}

/* update_data_source_title_cache - updates the title cache for a single data source
   @arg $data_source_id - (int) the ID of the data source to update the title cache for */
function update_data_source_title_cache($data_source_id) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (empty($data_source_id)) {
		return;
	}

	db_execute("update data_source set name_cache = '" . addslashes(get_data_source_title($data_source_id)) . "' where id = $data_source_id");
}

/* update_data_source_title_cache_from_template - updates the title cache for all data sources
	that match a given data template
   @arg $data_template_id - (int) the ID of the data template to match */
function update_data_source_title_cache_from_template($data_template_id) {
	$data = db_fetch_assoc("select local_data_id from data_template_data where data_template_id=$data_template_id and local_data_id>0");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["local_data_id"]);
	}
	}
}

/* update_data_source_title_cache_from_query - updates the title cache for all data sources
	that match a given data query/index combination
   @arg $snmp_query_id - (int) the ID of the data query to match
   @arg $snmp_index - the index within the data query to match */
function update_data_source_title_cache_from_query($snmp_query_id, $snmp_index) {
	$data = db_fetch_assoc("select id from data_local where snmp_query_id=$snmp_query_id and snmp_index='$snmp_index'");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["id"]);
	}
	}
}

/* update_data_source_title_cache_from_host - updates the title cache for all data sources
	that match a given host
   @arg $host_id - (int) the ID of the host to match */
function update_data_source_title_cache_from_host($host_id) {
	$data = db_fetch_assoc("select id from data_local where host_id=$host_id");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["id"]);
	}
	}
}

/* update_data_source_path - set the current data source path or generates a new one if a path
     does not already exist
   @arg $data_source_id - (int) the ID of the data source to set a path for */
function update_data_source_path($data_source_id) {
	include_once(CACTI_BASE_PATH . "/lib/string.php");

	$host_part = ""; $ds_part = "";

	/* we don't want to change the current path if one already exists */
	if ((empty($data_source_id)) || (db_fetch_cell("select rrd_path from data_source where id = $data_source_id") != "")) {
		return;
	}

	/* try any prepend the name with the host description */
	$hostname = db_fetch_cell("select host.description from host,data_source where data_source.host_id=host.id and data_source.id = $data_source_id");

	if ($hostname != "") {
		$host_part = strtolower(clean_up_name($hostname)) . "_";
	}

	/* then try and use the internal DS name to identify it */
	$data_source_item_name = db_fetch_cell("select data_source_name from data_source_item where data_source_id = $data_source_id order by id limit 1");

	if ($data_source_item_name != "") {
		$ds_part = strtolower(clean_up_name($data_source_item_name));
	}else{
		$ds_part = "ds";
	}

	$rrd_path = "<path_rra>/$host_part$ds_part" . "_" . "$data_source_id.rrd";

	/* update our changes to the db */
	db_execute("update data_source set rrd_path = '$rrd_path' where id = $data_source_id");

	return $rrd_path;
}

?>