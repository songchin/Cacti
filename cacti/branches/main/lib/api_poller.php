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

/** api_poller_cache_item_add - add an item to the poller cache
 *
 * @param int $device_id
 * @param string $device_field_override
 * @param int $local_data_id
 * @param int $rrd_step
 * @param int $poller_action_id
 * @param string $data_source_item_name
 * @param int $num_rrd_items
 * @param string $arg1
 * @param string $arg2
 * @param string $arg3
 * @return unknown_type
 */
function api_poller_cache_item_add($device_id, $device_field_override, $local_data_id, $rrd_step, $poller_action_id, $data_source_item_name, $num_rrd_items, $arg1 = "", $arg2 = "", $arg3 = "") {
	static $devices = array();

	if (!isset($devices[$device_id])) {
		$device = db_fetch_row("select
			device.id,
			device.poller_id,
			device.hostname,
			device.snmp_community,
			device.snmp_version,
			device.snmp_username,
			device.snmp_password,
			device.snmp_auth_protocol,
			device.snmp_priv_passphrase,
			device.snmp_priv_protocol,
			device.snmp_context,
			device.snmp_port,
			device.snmp_timeout,
			device.disabled
			from device
			where device.id=$device_id");

		$devices[$device_id] = $device;
	} else {
		$device = $devices[$device_id];
	}

	/* the $device_field_override array can be used to override certain device fields in the poller cache */
	if (isset($device)) {
		$device = array_merge($device, $device_field_override);
	}

	if (isset($device["id"]) || (isset($device_id))) {
		if (isset($device)) {
			if ($device["disabled"] == CHECKED) {
				return;
			}
		} else {
			if ($poller_action_id == 0) {
				return;
			}

			$device["id"] = 0;
			$device["poller_id"] = 0;
			$device["snmp_community"] = "";
			$device["snmp_timeout"] = "";
			$device["snmp_username"] = "";
			$device["snmp_password"] = "";
			$device["snmp_auth_protocol"] = "";
			$device["snmp_priv_passphrase"] = "";
			$device["snmp_priv_protocol"] = "";
			$device["snmp_context"] = "";
			$device["snmp_version"] = "";
			$device["snmp_port"] = "";
			$device["hostname"] = "None";
		}

		if ($poller_action_id == 0) {
			if (($device["snmp_version"] < 1) || ($device["snmp_version"] > 3) ||
				($device["snmp_community"] == "" && $device["snmp_version"] != 3)) {
				return;
			}
		}

		$rrd_next_step = api_poller_get_rrd_next_step($rrd_step, $num_rrd_items);

		return "($local_data_id, " . $device["poller_id"] . ", " . $device["id"] . ", $poller_action_id,'" . $device["hostname"] . "',
			'" . $device["snmp_community"]       . "', '" . $device["snmp_version"]       . "', '" . $device["snmp_timeout"] . "',
			'" . $device["snmp_username"]        . "', '" . $device["snmp_password"]      . "', '" . $device["snmp_auth_protocol"] . "',
			'" . $device["snmp_priv_passphrase"] . "', '" . $device["snmp_priv_protocol"] . "', '" . $device["snmp_context"] . "',
			'" . $device["snmp_port"]            . "', '$data_source_item_name', '"     . addslashes(clean_up_path(get_data_source_path($local_data_id, true))) . "',
			'$num_rrd_items', '$rrd_step', '$rrd_next_step', '$arg1', '$arg2', '$arg3', '1')";
	}
}

/** api_poller_get_rrd_next_step
 *
 * @param int $rrd_step
 * @param int $num_rrd_items
 * @return unknown_type
 */
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

		if (($modulus < 1) || ($rrd_step_counter == 0)) {
			$rrd_next_step = 0;
		}else{
			$rrd_next_step = $poller_interval * ($rrd_step_counter % $modulus);
		}

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
