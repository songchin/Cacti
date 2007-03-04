<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

$event_manager_interval = read_config_option('event_manager_interval');
$event_last_ran = db_fetch_cell("SELECT created FROM event_queue_control ORDER BY created");

if ($event_last_ran != '' && (time() - ($event_manager_interval + 5) > $event_last_ran)) {
	$command_string = read_config_option("path_php_binary");
	exec_background($command_string, (CACTI_SERVER_OS == "unix" ? '-q ' : '') . CACTI_BASE_PATH . "/event_manager.php");
}

/* determine the poller_id if specified */
$poller_id = 1;
if ( $_SERVER["argc"] == 2 ) {
	$poller_id = $_SERVER["argv"][1];
	if (!is_numeric($poller_id)) {
		api_log_log(_("The poller id is not numeric"), SEV_ALERT, FACIL_POLLER, "", $poller_id, 0, true);
		exit -1;
	}
}

/* let PHP run just as long as it has to */
ini_set("max_execution_time", "0");

/* Disable Mib File Loading */
putenv("MIBS=NONE");

/* record start time */
list($micro,$seconds) = split(" ", microtime());
$start = $seconds + $micro;

/* default the number of pollers to 0 */
$num_pollers = 0;

/* determine the polling interval */
$polling_interval = read_config_option("poller_interval");

/* poller_id 1 tasks only */
if ($poller_id == 1) {
	/* get total number of polling items from the database for the specified poller */
	if (isset($polling_interval)) {
		$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE (rrd_next_step<=0 AND poller_id=" . $poller_id . ")");
		$data_source_stats = array_rekey(db_fetch_assoc("SELECT action, count(action) AS total FROM poller_item WHERE (rrd_next_step<=0 AND poller_id=" . $poller_id . ") GROUP BY action"), "action", "total");
	}else{
		$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . $poller_id);
		$data_source_stats = array_rekey(db_fetch_assoc("SELECT action, count(action) AS total FROM poller_item WHERE poller_id=" . $poller_id . " GROUP BY action"), "action", "total");
	}
	$polling_hosts = array_merge(array(0 => array("id" => "0")), db_fetch_assoc("SELECT id FROM host WHERE (disabled = '' AND poller_id=" . $poller_id . ") ORDER BY id"));

	/* get total number of polling items from the database for all pollers */
	$all_num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . $poller_id);
	$all_polling_hosts = array_merge(array(0 => array("id" => "0")), db_fetch_assoc("select id from host where disabled = '' ORDER BY id"));

	/* get the number of active pollers */
	$pollers = db_fetch_assoc("SELECT * FROM poller WHERE active = 'on'");
	$num_pollers = sizeof($pollers);

	/* update web paths for the poller */
	db_execute("REPLACE INTO settings (name,value) VALUES ('path_webroot','" . addslashes((CACTI_SERVER_OS == "win32") ? strtolower(str_replace("\\","/",substr(dirname(__FILE__), 0, 1))) . str_replace("\\","/",substr(dirname(__FILE__), 1)) : dirname(__FILE__)) . "')");

	/* initialize poller_time and poller_output tables */
	db_execute("TRUNCATE TABLE poller_time");

	/* open a pipe to rrdtool for writing */
	$rrd_processes = read_config_option("concurrent_rrd_processes");
	$rrdtool_pipe = rrd_init($rrd_processes);

	/* insert the current date/time for graphs */
	db_execute("REPLACE INTO settings (name,value) VALUES ('date',NOW())");

	/* allow remote pollers to start */
	db_execute("UPDATE poller SET run_state='Ready' WHERE active='on'");

	/* show main poller as running */
	db_execute("UPDATE poller SET run_state='Running' WHERE id=1");

} else {
	/* verify I am a valid poller */
	if (sizeof(db_fetch_assoc("SELECT id FROM poller WHERE id = " . $poller_id)) == 0) {
		print sprintf(_("Poller '%i' does not exist in this system.\n"), $poller_id);
		api_log_log(sprintf(_("Poller '%i' is attempting to run, but does not exist on this system."),$poller_id), SEV_CRITICAL, FACIL_POLLER, "", $poller_id, 0, true);
		exit;
	}

	/* wait for signal from main poller to begin polling */
	while (1) {
		print sprintf(_("Poller '%i' waiting on signal from main poller to begin.\n"), $poller_id);
		$state = db_fetch_cell("SELECT run_state FROM poller where id=" . $poller_id);

		if ($state == _("Ready")) {
			/* show that I am not running */
			db_execute("UPDATE poller SET run_state='" . _("Running") . "' WHERE id=". $poller_id);
			break;
		}

		if (($start + MAX_POLLER_RUNTIME) < time()) {
			api_log_log("Maximum runtime of " . MAX_POLLER_RUNTIME . " seconds exceeded for Poller_ID " . $poller_id . " - Exiting.", SEV_ERROR, FACIL_POLLER, "", $poller_id, 0, true);
			db_execute("update poller set run_state = '" . _("Timeout") . "' where poller_id=" . $poller_id);
			exit;
		}
		sleep(1);
	}

	/* get total number of polling items from the database for the specified poller */
	if (isset($polling_interval)) {
		$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE (rrd_next_step<=0 AND poller_id=" . poller_id . ")");
		$data_source_stats = array_rekey(db_fetch_assoc("SELECT action, count(action) AS total FROM poller_item WHERE (rrd_next_step<=0 AND poller_id=" . $poller_id . ") GROUP BY action"), "action", "total");
	}else{
		$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . poller_id);
		$data_source_stats = array_rekey(db_fetch_assoc("SELECT action, count(action) AS total FROM poller_item WHERE poller_id=" . $poller_id . " GROUP BY action"), "action", "total");
	}
	$polling_hosts = db_fetch_assoc("SELECT id FROM host WHERE (disabled != 'on' and poller_id = '" . $poller_id . "') ORDER BY id");

	db_execute("UPDATE poller SET run_state='" . _("Running") . "' where id=" . $poller_id);
}

/* retreive the number of concurrent process settings */
$concurrent_processes = read_config_option("concurrent_processes");

/* initialize counters for script file handling */
$host_count = 1;

/* initialize file creation flags */
$change_files = false;

/* initialize file and host count pointers */
$process_file_number = 0;
$first_host = 0;
$last_host = 0;

/* obtain some defaults from the database */
$poller_type = read_config_option("poller_type");
$max_threads = read_config_option("max_threads");

/* enter mainline processing */
if (read_config_option("poller_enabled") == "on") {
	/* Determine the number of hosts to process per file */
	$hosts_per_file = ceil(sizeof($polling_hosts) / $concurrent_processes );

	/* Exit poller if cactid is selected and file does not exist */
	if (($poller_type == "2") && (!file_exists(read_config_option("path_cactid")))) {
		api_log_log(sprintf(_("ERROR: The path: %s is invalid.  Can not continue"),read_config_option("path_cactid")), SEV_CRITICAL, FACIL_POLLER, "", $poller_id, 0, true);
		exit;
	}

	/* Determine Command Name */
	if ((CACTI_SERVER_OS == "unix") and ($poller_type == "2")) {
		$command_string = read_config_option("path_cactid");
		$extra_args = "";
		$method = "cactid";
		chdir(dirname(read_config_option("path_cactid")));
	}else if (CACTI_SERVER_OS == "unix") {
		$command_string = read_config_option("path_php_binary");
		$extra_args = "-q " . CACTI_BASE_PATH . "/cmd.php";
		$method = "cmd.php";
	}else if ($poller_type == "2") {
		$command_string = read_config_option("path_cactid");
		$extra_args = "";
		$method = "cactid";
		chdir(dirname(read_config_option("path_cactid")));
	}else{
		$command_string = read_config_option("path_php_binary");
		$extra_args = "-q " . strtolower(CACTI_BASE_PATH . "/cmd.php");
		$method = "cmd.php";
	}

	/* execute each process with the host list */
	foreach ($polling_hosts as $item) {
		if ($host_count == 1) {
			$first_host = $item["id"];
		}

		if ($host_count == $hosts_per_file) {
			$last_host = $item["id"];
			$change_files = True;
		}

		$host_count ++;

		if ($change_files) {
			exec_background($command_string, "$extra_args -f=$first_host -l=$last_host -p=$poller_id");
			$host_count = 1;
			$change_files = False;
			$process_file_number++;
			$first_host = 0;
			$last_host = 0;
		} /* end change_files */
	} /* end for each */

	/* execute the last process if present */
	if ($host_count > 1) {
		$last_host = $item["id"];
		exec_background($command_string, "$extra_args -f=$first_host -l=$last_host -p=$poller_id");
		$process_file_number++;
	}

	if ($poller_type == "1") {
		$max_threads = _("N/A");
	}

	/* lets count the number of rrds processed */
	$rrds_processed = 0;

	while (1) {
		$polling_items = db_fetch_assoc("SELECT poller_id,end_time FROM poller_time WHERE poller_id = " . $poller_id);

		if (sizeof($polling_items) == $process_file_number) {
			/* set poller status complete */
			db_execute("UPDATE poller SET run_state='" . _("Complete") . "' WHERE id=" . $poller_id);

			if ($poller_id != 1) {
				break;
			} else {
				/* process RRD output if any exists */
				$rrds_processed = $rrds_processed + process_poller_output($rrdtool_pipe);

				/* wait for other pollers to finish */
				$active_pollers = sizeof(db_fetch_assoc("SELECT * FROM poller WHERE active='on'"));
				while (1) {
					$running_pollers = sizeof(db_fetch_assoc("SELECT * FROM poller WHERE (active='on' AND (run_state = '" . _("Running") . "' OR run_state = '" . _("Ready") . "'))"));
					if ($running_pollers == 0) {
						process_poller_output($rrdtool_pipe);

						break;
					} else {
						process_poller_output($rrdtool_pipe);

						if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) {
							print sprintf(_("Main Poller Complete.  Waiting on %i Pollers to Complete\n"), ($active_pollers - $running_pollers));
						}

						if (($start + MAX_POLLER_RUNTIME) < time()) {
							/* close rrdtool if poller is 0 */
							rrd_close($rrdtool_pipe);
							api_log_log(sprintf(_("Maximum Runtime of %i Seconds Exceeded for Poller id=%i - Exiting"), MAX_POLLER_RUNTIME, $poller_id), SEV_ERROR, FACIL_POLLER, "", $poller_id, 0, true);
							db_execute("update poller set run_state = '" . _("Timeout") . "' WHERE (active='on' AND run_state != '" . _("Complete") . "')");
							exit;
						}
					}

					/* give pollers another second to end */
					sleep(1);
				}

				/* record the end of polling time */
				list($micro,$seconds) = split(" ", microtime());
				$end = $seconds + $micro;

				db_execute("UPDATE poller SET run_state='" . _("Idle") . "' WHERE active='on'");

				break;
			}
		}else {
			if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) {
				print sprintf(_("Waiting on %i/%i Processes to Complete\n"), ($process_file_number - sizeof($polling_items)), $process_file_number);
			}

			/* process RRD output if any exists */
			if ($poller_id == 1) {
				$rrds_processed = $rrds_processed + process_poller_output($rrdtool_pipe);
			}

			/* end the process if the runtime exceeds MAX_POLLER_RUNTIME */
			if (($start + MAX_POLLER_RUNTIME) < time()) {
				/* close RRDTool */
				if ($poller_id == 1) { rrd_close($rrdtool_pipe); }

				api_log_log(sprintf(_("Maximum Runtime of %i Seconds Exceeded for Poller id=%i - Exiting"), MAX_POLLER_RUNTIME, $poller_id), SEV_ERROR, FACIL_POLLER, "", $poller_id, 0, true);
				db_execute("update poller set run_state = 'Timeout' where poller_id=" . $poller_id);

				/* manage cacti syslog size */
				api_log_maintain(true);

				exit;
			}

			sleep(1);
		}
	}

	/* close RRDTool */
	if ($poller_id == 1) { rrd_close($rrdtool_pipe, $rrd_processes); }

	/* process poller commands */
	$command_string = read_config_option("path_php_binary");
	$extra_args = "-q " . CACTI_BASE_PATH . "/poller_commands.php";
	exec_background($command_string, "$extra_args");

	if ($poller_id == 1) {
		/* graph export */
		$command_string = read_config_option("path_php_binary");
		$extra_args = "-q " . CACTI_BASE_PATH . "/poller_export.php";
		exec_background($command_string, "$extra_args");

		/* i don't know why we are doing this */
		db_execute("truncate table poller_time");

		/* idle the pollers till the next polling cycle */
		db_execute("update poller set run_state = '" . _("Wait") . "' where active='on'");
	}

	/* record the end of polling time, recaching and graph export */
	list($micro,$seconds) = split(" ", microtime());
	$end = $seconds + $micro;

	/* record some statistics */
	api_log_log(sprintf(_("SystemTime:") . "%01.4f " .
		_("TotalPollers:") . "%s " .
		_("Method:") . "%s " .
		_("Processes:") . "%s " .
		_("Threads:") . "%s " .
		_("Hosts:") . "%s " .
		_("HostsPerProcess:") . "%s " .
		_("DataSources:") . "%s " .
		_("RRDsProcessed:") . "%s",
		round($end-$start,4),
		$num_pollers,
		$method,
		$concurrent_processes,
		$max_threads,
		sizeof($all_polling_hosts),
		$hosts_per_file,
		$num_polling_items,
		$rrds_processed),
		SEV_NOTICE, FACIL_POLLER, "", $poller_id, 0, 0, true);

	/* calculate additional poller statistics */
	$total_time = round($end-$start,4);
	$poller = db_fetch_row("select * from poller where id = $poller_id");

	if ($total_time > $poller["max_time"]) $poller["max_time"] = $total_time;
	if ($total_time < $poller["min_time"]) $poller["min_time"] = $total_time;
	$poller["cur_time"] = $total_time;
	$poller["avg_time"] = ($poller["avg_time"] * $poller["total_polls"] + $total_time) / ($poller["total_polls"] + 1);
	$poller["total_polls"]++;
	$poller["last_update"] = date("Y-m-d H:i:s");
	$poller["availability"] = round(1 - ($poller["failed_polls"]/$poller["total_polls"]),4) * 100;

	/* save additional statistics to the poller table */
	db_execute("UPDATE poller SET hosts=" . $host_count . ", " .
		 "num_total=" . $num_polling_items . ", " .
		 "num_snmp=" . (isset($data_source_stats[POLLER_ACTION_SNMP]) ? $data_source_stats[POLLER_ACTION_SNMP] : "0") . ", " .
		 "num_script=" . (isset($data_source_stats[POLLER_ACTION_SCRIPT]) ? $data_source_stats[POLLER_ACTION_SCRIPT] : "0") . ", " .
		 "num_script_ss_php=" . (isset($data_source_stats[POLLER_ACTION_SCRIPT_PHP]) ? $data_source_stats[POLLER_ACTION_SCRIPT_PHP] : "0") . ", " .
		 "num_internal=" . (isset($data_source_stats[POLLER_ACTION_INTERNAL]) ? $data_source_stats[POLLER_ACTION_INTERNAL] : "0") . ", " .
		 "cur_time=" . $poller["cur_time"] . ", " .
		 "avg_time=" . $poller["avg_time"] . ", " .
		 "max_time=" . $poller["max_time"] . ", " .
		 "min_time=" . $poller["min_time"] . ", " .
		 "total_polls=" . $poller["total_polls"] . ", " .
		 "last_update='" . $poller["last_update"] ."', " .
		 "availability=" . $poller["availability"] . " WHERE id=$poller_id");

	if ($method == "cactid") {
		chdir(read_config_option("path_webroot"));
	}
}else{
	if ($poller_id == 1) {
		api_log_log(_("Either there are no pollers enabled, no items in your poller cache or polling is disabled. Make sure you have at least one data source created, your poller is active. If both are true, go to 'Utilities', and select 'Clear Poller Cache'."), SEV_CRITICAL, FACIL_POLLER, "", $poller_id, 0, true);
	} else {
		db_execute("update poller set run_state = 'Complete' where id=" . $poller_id);
		api_log_log(_("Poller had not items to process."), SEV_CRITICAL, FACIL_POLLER, "", $poller_id, 0, true);
	}
}

/* manage size of cacti log */
api_log_maintain(true);

?>
