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

define("MAX_RECACHE_RUNTIME", 296);

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;

/* Start Initialization Section */
include(dirname(__FILE__) . "/include/config.php");
include_once($config["base_path"] . "/lib/poller.php");
include_once($config["base_path"] . "/lib/data_query.php");
include_once($config["base_path"] . "/lib/rrd.php");

/* Record Start Time */
list($micro,$seconds) = split(" ", microtime());
$start = $seconds + $micro;

$poller_commands = db_fetch_assoc("select
	poller_command.action,
	poller_command.command
	from poller_command
	where poller_command.poller_id=0");

$last_host_id = 0;
$first_host = true;
$recached_hosts = 0;

if (sizeof($poller_commands) > 0) {
	foreach ($poller_commands as $command) {
		switch ($command["action"]) {
		case POLLER_COMMAND_REINDEX:
			list($host_id, $data_query_id) = explode(":", $command["command"]);
				if ($last_host_id != $host_id) {
				$last_host_id = $host_id;
				$first_host = true;
				$recached_hosts++;
			} else {
				$first_host = false;
			}

			if ($first_host) {
				cacti_log("Host[$host_id] WARNING: Recache Event Detected for Host", true, "POLLER");
			}

			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("Host[$host_id] RECACHE: Re-cache for Host, data query #$data_query_id", true, "POLLER");
			}

			run_data_query($host_id, $data_query_id);

			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("Host[$host_id] RECACHE: Re-cache successful.", true, "POLLER");
			}
		}

		/* record current_time */
		list($micro,$seconds) = split(" ", microtime());
		$current = $seconds + $micro;

		/* end if runtime has been exceeded */
		if (($current-$start) > MAX_RECACHE_RUNTIME) {
			cacti_log("ERROR: Host Recaching Timed Out While Processing '" . $command . "'",true,"RECACHE");
			break;
		}
	}

	db_execute("delete from poller_command where poller_id=0");
}

/* take time to log performance data */
list($micro,$seconds) = split(" ", microtime());
$recache = $seconds + $micro;

$recache_stats = sprintf("RecacheTime:%01.4f HostsRecached:%s",	round($recache - $start, 4), $recached_hosts);

if ($recached_hosts > 0) {
	cacti_log("STATS: " . $recache_stats, true, "RECACHE");
}

/* insert poller stats into the settings table */
db_execute("replace into settings (name,value) values ('stats_recache','$recache_stats')");

?>