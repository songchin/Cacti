<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

function api_poller_cache_item_add($host_id, $host_field_override, $local_data_id, $poller_action_id, $data_source_item_name, $num_rrd_items, $arg1 = "", $arg2 = "", $arg3 = "") {
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
		where host.id=$host_id");

	/* the $host_field_override array can be used to override certain host fields in the poller cache */
	if (isset($host)) {
		$host = array_merge($host, $host_field_override);
	}

	if (isset($host["id"]) || (isset($host_id))) {
		if (isset($host)) {
			if ($host["disabled"] == "on") {
				return true;
			}
		} else {
			$host["id"] = 0;
			$host["poller_id"] = 0;
			$host["snmp_community"] = "";
			$host["snmp_timeout"] = "";
			$host["snmpv3_auth_username"] = "";
			$host["snmpv3_auth_password"] = "";
			$host["snmpv3_auth_protocol"] = "";
			$host["snmpv3_priv_passphrase"] = "";
			$host["snmpv3_priv_protocol"] = "";
			$host["snmp_version"] = "";
			$host["snmp_port"] = "";
			$host["availability_method"] = "";
			$host["ping_method"] = "";
			$host["hostname"] = "None";
		}

		db_execute("insert into poller_item (local_data_id,host_id,poller_id,action,hostname,
			snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,
			snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_timeout,snmp_port,
			availability_method,ping_method,rrd_name,rrd_path,rrd_num,arg1,arg2,arg3)
			values ($local_data_id," . $host["id"] . "," . $host["poller_id"] . ",$poller_action_id,'" . $host["hostname"] . "','" .
			$host["snmp_community"] . "','" . $host["snmp_version"] . "','" .
			$host["snmpv3_auth_username"] . "','" . $host["snmpv3_auth_password"] . "','" .
			$host["snmpv3_auth_protocol"] . "','" . $host["snmpv3_priv_passphrase"] . "','" . $host["snmpv3_priv_protocol"] . "','" .
			$host["snmp_timeout"] . "','" . $host["snmp_port"] . "','" . $host["availability_method"] . "','" . $host["ping_method"] .
			"','$data_source_item_name','" .	addslashes(clean_up_path(get_data_source_path($local_data_id, true))) .
			"','$num_rrd_items','$arg1','$arg2','$arg3')");
	}
}
?>