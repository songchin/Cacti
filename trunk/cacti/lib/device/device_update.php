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

function api_device_save($device_id, &$_fields_device) {
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	/* sanity checks */
	validate_id_die($device_id, "device_id", true);

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $device_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_device, api_device_form_list());

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("host", $_fields, array("id"))) {
		if (empty($device_id)) {
			return db_fetch_insert_id();
		}else{
			return $device_id;
		}
	}else{
		return false;
	}
}

/* api_device_remove - removes a device
   @arg $device_id - the id of the device to remove */
function api_device_remove($device_id, $remove_dependencies = false) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");

	/* sanity checks */
	validate_id_die($device_id, "device_id");

	db_execute("delete from host where id = " . sql_sanitize($device_id));
	db_execute("delete from host_graph where host_id = " . sql_sanitize($device_id));
	db_execute("delete from host_data_query where host_id = " . sql_sanitize($device_id));
	db_execute("delete from host_data_query_cache where host_id = " . sql_sanitize($device_id));
	db_execute("delete from poller_item where host_id = " . sql_sanitize($device_id));
	db_execute("delete from graph_tree_items where host_id = " . sql_sanitize($device_id));

	if ($remove_dependencies == true) {
		/* obtain a list of all data sources associated with this device */
		$data_sources = api_data_source_list(array("host_id" => $device_id));

		/* delete each data source associated with this device */
		if (sizeof($data_sources) > 0) {
			foreach ($data_sources as $data_source) {
				api_data_source_remove($data_source["id"]);
			}
		}

		/* obtain a list of all graphs associated with this device */
		$graphs = api_graph_list(array("host_id" => $device_id));

		/* delete each graph associated with this device */
		if (sizeof($graphs) > 0) {
			foreach ($graphs as $graph) {
				api_graph_remove($graph["id"]);
			}
		}
	}else{
		/* obtain a list of all data sources associated with this device */
		$data_sources = api_data_source_list(array("host_id" => $device_id));

		/* disable each data source associated with this device */
		if (sizeof($data_sources) > 0) {
			foreach ($data_sources as $data_source) {
				api_data_source_disable($data_source["id"]);
			}
		}
	}
}

function api_device_package_add($device_id, $package_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");
	validate_id_die($package_id, "package_id");

	return db_insert("host_package",
		array(
			"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id),
			"package_id" => array("type" => DB_TYPE_INTEGER, "value" => $package_id)
			),
		array("host_id", "package_id"));
}

function api_device_package_remove($device_id, $package_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");
	validate_id_die($package_id, "package_id");

	return db_delete("host_package",
		array(
			"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id),
			"package_id" => array("type" => DB_TYPE_INTEGER, "value" => $package_id)
			));
}

function api_device_enable($device_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	db_update("host",
		array(
			"disabled" => array("type" => DB_TYPE_STRING, "value" => ""),
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id)
			),
		array("id"));

	/* obtain a list of all data sources associated with this device */
	$data_sources = api_data_source_list(array("host_id" => $device_id));

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			update_poller_cache($data_source["id"], false);
		}
	}
}

function api_device_disable($device_id) {
	db_update("host",
		array(
			"disabled" => array("type" => DB_TYPE_STRING, "value" => "on"),
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id)
			),
		array("id"));

	/* update poller cache */
	db_delete("poller_item",
		array(
			"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id)
			));
	db_delete("poller_reindex",
		array(
			"host_id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id)
			));
}

function api_device_statistics_clear($device_id) {
	db_update("host",
		array(
			"min_time" => array("type" => DB_TYPE_INTEGER, "value" => "9.99999"),
			"max_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"cur_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"avg_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"total_polls" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"failed_polls" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"availability" => array("type" => DB_TYPE_INTEGER, "value" => "100.00"),
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $device_id)
			),
		array("id"));
}

/* api_device_dq_remove - removes a device->data query mapping
   @arg $device_id - the id of the device which contains the mapping
   @arg $data_query_id - the id of the data query to remove the mapping for */
function api_device_dq_remove($device_id, $data_query_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");
	validate_id_die($data_query_id, "data_query_id");

	db_execute("delete from host_data_query_cache where data_query_id = " . sql_sanitize($data_query_id) . " and host_id = " . sql_sanitize($device_id));
	db_execute("delete from host_data_query where data_query_id = " . sql_sanitize($data_query_id) . " and host_id = " . sql_sanitize($device_id));
}

/* api_device_gt_remove - removes a device->graph template mapping
   @arg $device_id - the id of the device which contains the mapping
   @arg $graph_template_id - the id of the graph template to remove the mapping for */
function api_device_gt_remove($device_id, $graph_template_id) {
	/* sanity checks */
	validate_id_die($device_id, "device_id");
	validate_id_die($graph_template_id, "graph_template_id");

	db_execute("delete from host_graph where graph_template_id = " . sql_sanitize($graph_template_id) . " and host_id = " . sql_sanitize($device_id));
}

?>
