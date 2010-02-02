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

require_once(CACTI_BASE_PATH . "/include/device/device_constants.php");

$device_actions = array(
	DEVICE_ACTION_DELETE => __("Delete"),
	DEVICE_ACTION_ENABLE => __("Enable"),
	DEVICE_ACTION_DISABLE => __("Disable"),
	DEVICE_ACTION_CHANGE_SNMP_OPTIONS => __("Change SNMP Options"),
	DEVICE_ACTION_CLEAR_STATISTICS => __("Clear Statistics"),
	DEVICE_ACTION_CHANGE_AVAILABILITY_OPTIONS => __("Change Availability Options"),
	DEVICE_ACTION_CHANGE_POLLER => __("Change Poller"),
	DEVICE_ACTION_CHANGE_SITE => __("Change Site"),
	);

$device_threads = array(
	1 => __("1 Thread (default)"),
	2 => __("%s Threads", 2),
	3 => __("%s Threads", 3),
	4 => __("%s Threads", 4),
	5 => __("%s Threads", 5),
	6 => __("%s Threads", 6)
	);

$device_struc = array(
	"device_template_id",
	"description",
	"hostname",
	"notes",
	"snmp_community",
	"snmp_version",
	"snmp_username",
	"snmp_password",
	"snmp_auth_protocol",
	"snmp_priv_passphrase",
	"snmp_priv_protocol",
	"snmp_context",
	"snmp_port",
	"snmp_timeout",
	"max_oids",
	"availability_method",
	"ping_method",
	"ping_port",
	"ping_timeout",
	"ping_retries",
	"disabled",
	"status",
	"status_event_count",
	"status_fail_date",
	"status_rec_date",
	"status_last_error",
	"min_time",
	"max_time",
	"cur_time",
	"avg_time",
	"total_polls",
	"failed_polls",
	"availability"
	);

$snmp_versions = array(0 =>
	__("Not In Use"),
	__("Version 1"),
	__("Version 2"),
	__("Version 3"),
	);

$snmp_auth_protocols = array(
	SNMP_AUTH_PROTOCOL_NONE 	=> __("[NONE]"),
	SNMP_AUTH_PROTOCOL_MD5 		=> __("MD5 (default)"),
	SNMP_AUTH_PROTOCOL_SHA 		=> __("SHA"),
	);

$snmp_priv_protocols = array(
	SNMP_PRIV_PROTOCOL_NONE 	=> __("[None]"),
	SNMP_PRIV_PROTOCOL_DES 		=> __("DES (default)"),
	SNMP_PRIV_PROTOCOL_AES128 	=> __("AES"),
	);

$availability_options = array(
	AVAIL_NONE => __("None"),
	AVAIL_SNMP_AND_PING => __("Ping and SNMP"),
	AVAIL_SNMP_OR_PING => __("Ping or SNMP"),
	AVAIL_SNMP => __("SNMP"),
	AVAIL_PING => __("Ping"),
	);

$ping_methods = array(
	PING_ICMP => __("ICMP Ping"),
	PING_TCP => __("TCP Ping"),
	PING_UDP => __("UDP Ping"),
	);
