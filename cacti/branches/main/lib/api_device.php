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

/** api_device_remove - removes a device
   @param $device_id - the id of the device to remove */
function api_device_remove($device_id) {
	db_execute("delete from device             where id=$device_id");
	db_execute("delete from device_graph       where device_id=$device_id");
	db_execute("delete from device_snmp_query  where device_id=$device_id");
	db_execute("delete from device_snmp_cache  where device_id=$device_id");
	db_execute("delete from poller_item      where device_id=$device_id");
	db_execute("delete from poller_reindex   where device_id=$device_id");
	db_execute("delete from poller_command   where command like '$device_id:%'");
	db_execute("delete from graph_tree_items where device_id=$device_id");
	db_execute("delete from user_auth_perms  where item_id=$device_id and type=" . PERM_DEVICES);

	db_execute("update data_local  set device_id=0 where device_id=$device_id");
	db_execute("update graph_local set device_id=0 where device_id=$device_id");
}

/** api_device_remove_multi - removes multiple devices in one call
   @param $device_ids - an array of device id's to remove */
function api_device_remove_multi($device_ids) {
	$devices_to_delete = "";
	$i = 0;

	if (sizeof($device_ids)) {
		/* build the list */
		foreach($device_ids as $device_id) {
			if ($i == 0) {
				$devices_to_delete .= $device_id;
			}else{
				$devices_to_delete .= ", " . $device_id;
			}

			/* poller commands go one at a time due to trashy logic */
			db_execute("DELETE FROM poller_item      WHERE device_id=$device_id");
			db_execute("DELETE FROM poller_reindex   WHERE device_id=$device_id");
			db_execute("DELETE FROM poller_command   WHERE command like '$device_id:%'");

			$i++;
		}

		db_execute("DELETE FROM device             WHERE id IN ($devices_to_delete)");
		db_execute("DELETE FROM device_graph       WHERE device_id IN ($devices_to_delete)");
		db_execute("DELETE FROM device_snmp_query  WHERE device_id IN ($devices_to_delete)");
		db_execute("DELETE FROM device_snmp_cache  WHERE device_id IN ($devices_to_delete)");

		db_execute("DELETE FROM graph_tree_items WHERE device_id IN ($devices_to_delete)");
		db_execute("DELETE FROM user_auth_perms  WHERE item_id IN ($devices_to_delete) and type=" . PERM_DEVICES);

		/* for people who choose to leave data sources around */
		db_execute("UPDATE data_local  SET device_id=0 WHERE device_id IN ($devices_to_delete)");
		db_execute("UPDATE graph_local SET device_id=0 WHERE device_id IN ($devices_to_delete)");

	}
}

/** api_device_dq_remove - removes a device->data query mapping
   @param $device_id - the id of the device which contains the mapping
   @param $data_query_id - the id of the data query to remove the mapping for */
function api_device_dq_remove($device_id, $data_query_id) {
	db_execute("delete from device_snmp_cache where snmp_query_id=$data_query_id and device_id=$device_id");
	db_execute("delete from device_snmp_query where snmp_query_id=$data_query_id and device_id=$device_id");
	db_execute("delete from poller_reindex where data_query_id=$data_query_id and device_id=$device_id");
}

/** api_device_gt_remove - removes a device->graph template mapping
   @param $device_id - the id of the device which contains the mapping
   @param $graph_template_id - the id of the graph template to remove the mapping for */
function api_device_gt_remove($device_id, $graph_template_id) {
	db_execute("delete from device_graph where graph_template_id=$graph_template_id and device_id=$device_id");
}

/** api_device_save - save a device to the database
 *
 * @param int $id
 * @param int $site_id
 * @param int $poller_id
 * @param int $device_template_id
 * @param string $description
 * @param string $hostname
 * @param string $snmp_community
 * @param int $snmp_version
 * @param string $snmp_username
 * @param string $snmp_password
 * @param int $snmp_port
 * @param int $snmp_timeout
 * @param string $disabled
 * @param int $availability_method
 * @param int $ping_method
 * @param int $ping_port
 * @param int $ping_timeout
 * @param int $ping_retries
 * @param string $notes
 * @param string $snmp_auth_protocol
 * @param string $snmp_priv_passphrase
 * @param string $snmp_priv_protocol
 * @param string $snmp_context
 * @param int $max_oids
 * @param int $device_threads
 * @param string $template_enabled
 * @return unknown_type
 */
function api_device_save($id, $site_id, $poller_id, $device_template_id, $description, $hostname, $snmp_community, $snmp_version,
	$snmp_username, $snmp_password, $snmp_port, $snmp_timeout, $disabled,
	$availability_method, $ping_method, $ping_port, $ping_timeout, $ping_retries,
	$notes, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $max_oids, $device_threads, $template_enabled) {

	/* fetch some cache variables */
	if (empty($id)) {
		$_device_template_id = 0;
	}else{
		$_device_template_id = db_fetch_cell("select device_template_id from device where id=$id");
	}

	$save["id"] = $id;
	$save["site_id"]          = form_input_validate($site_id, "site_id", "^[0-9]+$", false, 3);
	$save["poller_id"]        = form_input_validate($poller_id, "poller_id", "^[0-9]+$", false, 3);
	$save["device_template_id"] = form_input_validate($device_template_id, "device_template_id", "^[0-9]+$", false, 3);
	$save["description"]      = form_input_validate($description, "description", "", false, 3);
	$save["hostname"]         = form_input_validate(trim($hostname), "hostname", "", false, 3);
	$save["notes"]            = form_input_validate($notes, "notes", "", true, 3);
	$save["disabled"]         = form_input_validate($disabled, "disabled", "", true, 3);
	$save["template_enabled"] = form_input_validate($template_enabled, "template_enabled", "", true, 3);

	$save["snmp_version"]     = form_input_validate($snmp_version, "snmp_version", "", true, 3);
	$save["snmp_community"]   = form_input_validate($snmp_community, "snmp_community", "", true, 3);

	if ($save["snmp_version"] == 3) {
		$save["snmp_username"]        = form_input_validate($snmp_username, "snmp_username", "", true, 3);
		$save["snmp_password"]        = form_input_validate($snmp_password, "snmp_password", "", true, 3);
		$save["snmp_auth_protocol"]   = form_input_validate($snmp_auth_protocol, "snmp_auth_protocol", "", true, 3);
		$save["snmp_priv_passphrase"] = form_input_validate($snmp_priv_passphrase, "snmp_priv_passphrase", "", true, 3);
		$save["snmp_priv_protocol"]   = form_input_validate($snmp_priv_protocol, "snmp_priv_protocol", "", true, 3);
		$save["snmp_context"]         = form_input_validate($snmp_context, "snmp_context", "", true, 3);
	} else {
		$save["snmp_username"]        = "";
		$save["snmp_password"]        = "";
		$save["snmp_auth_protocol"]   = "";
		$save["snmp_priv_passphrase"] = "";
		$save["snmp_priv_protocol"]   = "";
		$save["snmp_context"]         = "";
	}

	$save["snmp_port"]           = form_input_validate($snmp_port, "snmp_port", "^[0-9]+$", false, 3);
	$save["snmp_timeout"]        = form_input_validate($snmp_timeout, "snmp_timeout", "^[0-9]+$", false, 3);

	$save["availability_method"] = form_input_validate($availability_method, "availability_method", "^[0-9]+$", false, 3);
	$save["ping_method"]         = form_input_validate($ping_method, "ping_method", "^[0-9]+$", false, 3);
	$save["ping_port"]           = form_input_validate($ping_port, "ping_port", "^[0-9]+$", true, 3);
	$save["ping_timeout"]        = form_input_validate($ping_timeout, "ping_timeout", "^[0-9]+$", true, 3);
	$save["ping_retries"]        = form_input_validate($ping_retries, "ping_retries", "^[0-9]+$", true, 3);
	$save["max_oids"]            = form_input_validate($max_oids, "max_oids", "^[0-9]+$", true, 3);
	$save["device_threads"]      = form_input_validate($device_threads, "device_threads", "^[0-9]+$", true, 3);

	$save = api_plugin_hook_function('api_device_save', $save);

	$device_id = 0;

	if (!is_error_message()) {
		$device_id = sql_save($save, "device");

		if ($device_id) {
			raise_message(1);

			/* push out relavant fields to data sources using this device */
			push_out_device($device_id, 0);

			/* the device substitution cache is now stale; purge it */
			kill_session_var("sess_device_cache_array");

			/* update title cache for graph and data source */
			update_data_source_title_cache_from_device($device_id);
			update_graph_title_cache_from_device($device_id);
		}else{
			raise_message(2);
		}

		/* recache in case any snmp information was changed */
		if (!empty($id)) { /* a valid device was already existing */
			/* detect SNMP change, if current snmp parameters cannot be found in device table */
			$snmp_changed = ($id != db_fetch_cell("SELECT " .
					"id " .
					"FROM device " .
					"WHERE id=$id " .
					"AND snmp_version='$snmp_version' " .
					"AND snmp_community='$snmp_community' " .
					"AND snmp_username='$snmp_username' " .
					"AND snmp_password='$snmp_password' " .
					"AND snmp_auth_protocol='$snmp_auth_protocol' " .
					"AND snmp_priv_passphrase='$snmp_priv_passphrase' " .
					"AND snmp_priv_protocol='$snmp_priv_protocol' " .
					"AND snmp_context='$snmp_context' " .
					"AND snmp_port='$snmp_port' " .
					"AND snmp_timeout='$snmp_timeout' "));

			if ($snmp_changed) {
				/* fecth all existing snmp queries */
				$snmp_queries = db_fetch_assoc("SELECT " .
						"snmp_query_id, " .
						"reindex_method " .
						"FROM device_snmp_query " .
						"WHERE device_id=$id");

				if (sizeof($snmp_queries) > 0) {
					foreach ($snmp_queries as $snmp_query) {
						/* recache all existing snmp queries */
						run_data_query($id, $snmp_query["snmp_query_id"]);
					}
				}
			}
		}

		/* if the user changes the device template, add each snmp query associated with it */
		if (($device_template_id != $_device_template_id) && (!empty($device_template_id))) {
			$snmp_queries = db_fetch_assoc("select snmp_query_id, reindex_method from device_template_snmp_query where device_template_id=$device_template_id");

			if (sizeof($snmp_queries) > 0) {
			foreach ($snmp_queries as $snmp_query) {
				db_execute("replace into device_snmp_query (device_id,snmp_query_id,reindex_method) values ($device_id," . $snmp_query["snmp_query_id"] . "," . $snmp_query["reindex_method"] . ")");

				/* recache snmp data */
				run_data_query($device_id, $snmp_query["snmp_query_id"]);
			}
			}

			$graph_templates = db_fetch_assoc("select graph_template_id from device_template_graph where device_template_id=$device_template_id");

			if (sizeof($graph_templates) > 0) {
			foreach ($graph_templates as $graph_template) {
				db_execute("replace into device_graph (device_id,graph_template_id) values ($device_id," . $graph_template["graph_template_id"] . ")");
				api_plugin_hook_function('add_graph_template_to_device', array("device_id" => $device_id, "graph_template_id" => $graph_template["graph_template_id"]));
			}
			}
		}
	}

	return $device_id;
}
