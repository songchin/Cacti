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

define("MAX_RECACHE_RUNTIME", 296);

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>" . __("This script is only meant to run at the command line.") . "</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;

/* Start Initialization Section */
include(dirname(__FILE__) . "/include/global.php");
include_once(CACTI_BASE_PATH . "/lib/poller.php");
include_once(CACTI_BASE_PATH . "/lib/data_query.php");
include_once(CACTI_BASE_PATH . "/lib/rrd.php");

/* Record Start Time */
list($micro,$seconds) = explode(" ", microtime());
$start = $seconds + $micro;

$poller_commands = db_fetch_assoc("select
	poller_command.action,
	poller_command.command
	from poller_command
	where poller_command.poller_id=0");

$last_device_id = 0;
$first_device = true;
$recached_devices = 0;

if (sizeof($poller_commands) > 0) {
	foreach ($poller_commands as $command) {
		switch ($command["action"]) {
		case POLLER_COMMAND_REINDEX:
			list($device_id, $data_query_id) = explode(":", $command["command"]);
				if ($last_device_id != $device_id) {
				$last_device_id = $device_id;
				$first_device = true;
				$recached_devices++;
			} else {
				$first_device = false;
			}

			if ($first_device) {
				cacti_log("Host[$device_id] WARNING: Recache Event Detected for Host", true, "PCOMMAND");
			}

			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("Host[$device_id] RECACHE: Re-cache for Host, data query #$data_query_id", true, "PCOMMAND");
			}

			run_data_query($device_id, $data_query_id);

			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("Host[$device_id] RECACHE: Re-cache successful.", true, "PCOMMAND");
			}
			break;
		default:
			cacti_log("ERROR: Unknown poller command issued", true, "PCOMMAND");
		}

		/* record current_time */
		list($micro,$seconds) = explode(" ", microtime());
		$current = $seconds + $micro;

		/* end if runtime has been exceeded */
		if (($current-$start) > MAX_RECACHE_RUNTIME) {
			cacti_log("ERROR: Poller Command processing timed out after processing '" . $command . "'",true,"PCOMMAND");
			break;
		}
	}

	db_execute("delete from poller_command where poller_id=0");
}

/* take time to log performance data */
list($micro,$seconds) = explode(" ", microtime());
$recache = $seconds + $micro;

$recache_stats = sprintf("RecacheTime:%01.4f HostsRecached:%s",	round($recache - $start, 4), $recached_devices);

if ($recached_devices > 0) {
	cacti_log("STATS: " . $recache_stats, true, "RECACHE");
}

/* insert poller stats into the settings table */
db_execute("replace into settings (name,value) values ('stats_recache','$recache_stats')");
