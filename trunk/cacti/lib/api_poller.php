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

function api_poller_cache_item_add($host_id, $data_source_id, $rrd_step, $poller_action_id, $data_source_item_name, $num_rrd_items, $arg1 = "", $arg2 = "", $arg3 = "") {
	global $cnn_id;

	require_once(CACTI_BASE_PATH . "/lib/sys/string.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (empty($data_source_id)) {
		return;
	}

	if (!empty($host_id)) {
		$host = db_fetch_row("select
			host.id,
			host.poller_id,
			host.hostname,
			host.snmp_community,
			host.snmp_version,
			host.snmpv3_auth_username,
			host.snmpv3_auth_password,
			host.snmpv3_auth_protocol,
			host.snmpv3_priv_passphrase,
			host.snmpv3_priv_protocol,
			host.snmp_port,
			host.snmp_timeout,
			host.availability_method,
			host.ping_method,
			host.disabled
			from host
			where host.id = $host_id");
	}

	/* return if the device is marked as disabled */
	if ((isset($host)) && ($host["disabled"] == "on")) {
		return;
	}

	$save["local_data_id"] = $data_source_id;
	$save["host_id"] = $host_id;
	$save["action"] = $poller_action_id;
	$save["poller_id"] = (isset($host) ? $host["poller_id"] : "");
	$save["hostname"] = (isset($host) ? $host["hostname"] : "");
	$save["snmp_community"] = (isset($host) ? $host["snmp_community"] : "");
	$save["snmp_version"] = (isset($host) ? $host["snmp_version"] : "");
	$save["snmpv3_auth_username"] = (isset($host) ? $host["snmpv3_auth_username"] : "");
	$save["snmpv3_auth_password"] = (isset($host) ? $host["snmpv3_auth_password"] : "");
	$save["snmpv3_auth_protocol"] = (isset($host) ? $host["snmpv3_auth_protocol"] : "");
	$save["snmpv3_priv_passphrase"] = (isset($host) ? $host["snmpv3_priv_passphrase"] : "");
	$save["snmpv3_priv_protocol"] = (isset($host) ? $host["snmpv3_priv_protocol"] : "");
	$save["snmp_timeout"] = (isset($host) ? $host["snmp_timeout"] : "");
	$save["snmp_port"] = (isset($host) ? $host["snmp_port"] : "");
	$save["availability_method"] = (isset($host) ? $host["availability_method"] : "");
	$save["ping_method"] = (isset($host) ? $host["ping_method"] : "");
	$save["rrd_name"] = $data_source_item_name;
	$save["rrd_path"] = addslashes(clean_up_path(api_data_source_path($data_source_id, true)));
	$save["rrd_num"] = $num_rrd_items;
	$save["rrd_step"] = $rrd_step;
	$save["rrd_next_step"] = api_poller_get_rrd_next_step($rrd_step, $num_rrd_items);
	$save["arg1"] = $arg1;
	$save["arg2"] = $arg2;
	$save["arg3"] = $arg3;

	/* for pass by reference */
	$table_name = "poller_item";

	db_execute($cnn_id->GetInsertSQL($table_name, $save));
}

function api_poller_get_rrd_next_step($rrd_step=300, $num_rrd_items=1) {
	global $config;

	$poller_interval = read_config_option("poller_interval");
	$rrd_next_step = 0;
	if (($rrd_step != $poller_interval) && (isset($poller_interval))){
		if (!isset($config["rrd_step_counter"])) {
			$rrd_step_counter = read_config_option("rrd_step_counter");
		}else{
			$rrd_step_counter = $config["rrd_step_counter"];
		}

		if ($num_rrd_items == 1) {
			$config["rrd_num_counter"] = 0;
		}else{
			if (!isset($config["rrd_num_counter"])) {
				$config["rrd_num_counter"] = 1;
			}else{
				$config["rrd_num_counter"]++;
			}
		}

		$modulus = $rrd_step / $poller_interval;

		$rrd_next_step = $poller_interval * ($rrd_step_counter % $modulus);

		if ($num_rrd_items == 1) {
			$rrd_step_counter++;
		}else{
			if ($num_rrd_items == $config["rrd_num_counter"]) {
				$rrd_step_counter++;
				$config["rrd_num_counter"] = 0;
			}
		}

		if ($rrd_step_counter >= $modulus) {
			$rrd_step_counter = 0;
		}

		/* save rrd_step_counter */
		$config["rrd_step_counter"] = $rrd_step_counter;
		db_execute("replace into settings (name, value) values ('rrd_step_counter','$rrd_step_counter')");
	}

	return $rrd_next_step;
}
?>