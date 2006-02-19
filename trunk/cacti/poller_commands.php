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

define("MAX_POLLER_RUNTIME", 296);

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0])) {
	die("<br><strong>". _("This script is only meant to run at the command line.") . "</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;

/* Start Initialization Section */
require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/lib/sys/rrd.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");
require_once(CACTI_BASE_PATH . "/lib/sys/graph_export.php");

/* determine the poller_id if specified */
$poller_id = 1;
if ( $_SERVER["argc"] == 2 ) {
	$poller_id = $_SERVER["argv"][1];
	if (!is_numeric($poller_id)) {
		api_log_log(_("The poller id is not numeric"), SEV_ALERT, FACIL_POLLER, "", $poller_id, 0, true);
		exit -1;
	}
}

/* Record Start Time */
list($micro,$seconds) = split(" ", microtime());
$start = $seconds + $micro;

$poller_commands = db_fetch_assoc("select
	poller_command.action,
	poller_command.command
	from poller_command
	where poller_command.poller_id=" . $poller_id);

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
				api_log_log(_("Host[$host_id] WARNING: Recache Event Detected for Host"), SEV_WARNING, FACIL_POLLER, "", $poller_id, 0, true);
			}

			api_log_log(_("Host[$host_id] RECACHE: Re-cache for Host, data query #$data_query_id"), SEV_NOTICE, FACIL_POLLER, "", $poller_id, 0, true);

			api_data_query_execute($host_id, $data_query_id);

			api_log_log(_("Host[$host_id] RECACHE: Re-cache successful."), SEV_NOTICE, FACIL_POLLER, "", $poller_id, 0, true);

			break;
		case POLLER_COMMAND_RRDPURGE:
			api_log_log(sprintf("_(Host[%i] PURGE: Unused RRDfile removed from system '%s')", $host_id, $command),SEV_NOTICE,  FACIL_POLLER, "", $poller_id, 0, 0, true);

			if (file_exists($command)) {
				@unlink($command);
			}

			break;
		default:
			cacti_log("Unknown poller command issued", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
		}

		/* record current_time */
		list($micro,$seconds) = split(" ", microtime());
		$current = $seconds + $micro;

		/* end if runtime has been exceeded */
		if (($current-$start) > MAX_RECACHE_RUNTIME) {
			api_log_log(sprintf(_("Poller Command processing timed out after processing '%s'"), $command), SEV_ERROR, FACIL_POLLER, "", $poller_id, 0, true);
			break;
		}
	}

	db_execute("delete from poller_command where poller_id=0");
}

/* take time to log performance data */
list($micro,$seconds) = split(" ", microtime());
$recache = $seconds + $micro;

$recache_stats = sprintf(_("RecacheTime:%01.4f HostsRecached:%s"), round($recache - $start, 4), $recached_hosts);

if ($recached_hosts > 0) {
	api_log_log($recache_stats, SEV_NOTICE, FACIL_POLLER, "", $poller_id, 0, true);
}

/* insert poller stats into the settings table */
db_execute("replace into settings (name,value) values ('stats_recache','$recache_stats')");

?>
