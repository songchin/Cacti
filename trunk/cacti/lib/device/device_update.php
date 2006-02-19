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

function api_device_save($id, $poller_id, $host_template_id, $description, $hostname, $snmp_community, $snmp_version,
	$snmpv3_auth_username, $snmpv3_auth_password, $snmpv3_auth_protocol, $snmpv3_priv_passphrase, $snmpv3_priv_protocol,
	$snmp_port, $snmp_timeout, $availability_method, $ping_method, $disabled) {
	/* fetch some cache variables */
	if (empty($id)) {
		$_host_template_id = 0;
	}else{
		$_host_template_id = db_fetch_cell("select host_template_id from host where id=$id");
	}

	$save["id"] = $id;
	$save["poller_id"] = $poller_id;
	$save["host_template_id"] = $host_template_id;
	$save["description"] = form_input_validate($description, "description", "", false, 3);
	$save["hostname"] = form_input_validate($hostname, "hostname", "", false, 3);
	$save["snmp_community"] = form_input_validate($snmp_community, "snmp_community", "", true, 3);
	$save["snmp_version"] = form_input_validate($snmp_version, "snmp_version", "", true, 3);
	$save["snmpv3_auth_username"] = form_input_validate($snmpv3_auth_username, "snmpv3_auth_username", "", true, 3);
	$save["snmpv3_auth_password"] = form_input_validate($snmpv3_auth_password, "snmpv3_auth_password", "", true, 3);
	$save["snmpv3_auth_protocol"] = form_input_validate($snmpv3_auth_protocol, "snmpv3_auth_protocol", "", true, 3);
	$save["snmpv3_priv_passphrase"] = form_input_validate($snmpv3_priv_passphrase, "snmpv3_priv_passphrase", "", true, 3);
	$save["snmpv3_priv_protocol"] = form_input_validate($snmpv3_priv_protocol, "snmpv3_priv_protocol", "", true, 3);
	$save["snmp_port"] = form_input_validate($snmp_port, "snmp_port", "^[0-9]+$", false, 3);
	$save["snmp_timeout"] = form_input_validate($snmp_timeout, "snmp_timeout", "^[0-9]+$", false, 3);
	$save["availability_method"] = form_input_validate($availability_method, "availability_method", "", true, 3);
	$save["ping_method"] = form_input_validate($ping_method, "ping_method", "", true, 3);
	$save["disabled"] = form_input_validate($disabled, "disabled", "", true, 3);

	$host_id = 0;

	if (!is_error_message()) {
		$host_id = sql_save($save, "host");

		if ($host_id) {
			raise_message(1);

			/* the host substitution cache is now stale; purge it */
			kill_session_var("sess_host_cache_array");

			/* push out relavant fields to data sources using this host */
			//push_out_host($host_id, 0);

			/* update title cache for graph and data source */
			//update_data_source_title_cache_from_host($host_id);
			//api_graph_title_cache_host_update($host_id);
		}else{
			raise_message(2);
		}

		/* if the user changes the host template, add each snmp query associated with it */
		if (($host_template_id != $_host_template_id) && (!empty($host_template_id))) {
			$snmp_queries = db_fetch_assoc("select data_query_id from host_template_data_query where host_template_id=$host_template_id");

			if (sizeof($snmp_queries) > 0) {
			foreach ($snmp_queries as $snmp_query) {
				db_execute("replace into host_data_query (host_id,data_query_id,reindex_method) values ($host_id," . $snmp_query["snmp_query_id"] . "," . DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME . ")");

				/* recache snmp data */
				api_data_query_execute($host_id, $snmp_query["data_query_id"]);
			}
			}

			$graph_templates = db_fetch_assoc("select graph_template_id from host_template_graph where host_template_id=$host_template_id");

			if (sizeof($graph_templates) > 0) {
			foreach ($graph_templates as $graph_template) {
				db_execute("replace into host_graph (host_id,graph_template_id) values ($host_id," . $graph_template["graph_template_id"] . ")");
			}
			}
		}
	}

	return $host_id;
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

function api_device_enable($device_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	db_update("host",
		array(
			"disabled" => array("type" => DB_TYPE_STRING, "value" => ""),
			"id" => array("type" => DB_TYPE_NUMBER, "value" => $device_id)
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
			"id" => array("type" => DB_TYPE_NUMBER, "value" => $device_id)
			),
		array("id"));

	/* update poller cache */
	db_delete("poller_item",
		array(
			"host_id" => array("type" => DB_TYPE_NUMBER, "value" => $device_id)
			));
	db_delete("poller_reindex",
		array(
			"host_id" => array("type" => DB_TYPE_NUMBER, "value" => $device_id)
			));
}

function api_device_statistics_clear($device_id) {
	db_update("host",
		array(
			"min_time" => array("type" => DB_TYPE_NUMBER, "value" => "9.99999"),
			"max_time" => array("type" => DB_TYPE_NUMBER, "value" => "0"),
			"cur_time" => array("type" => DB_TYPE_NUMBER, "value" => "0"),
			"avg_time" => array("type" => DB_TYPE_NUMBER, "value" => "0"),
			"total_polls" => array("type" => DB_TYPE_NUMBER, "value" => "0"),
			"failed_polls" => array("type" => DB_TYPE_NUMBER, "value" => "0"),
			"availability" => array("type" => DB_TYPE_NUMBER, "value" => "100.00"),
			"id" => array("type" => DB_TYPE_NUMBER, "value" => $device_id)
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
