#!/usr/bin/php -q
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

define("MAX_POLLER_RUNTIME", 296);

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
include_once($config["base_path"] . "/lib/graph_export.php");
include_once($config["base_path"] . "/lib/rrd.php");

/* determine the poller_id if specified */
$poller_id = 1;
if ( $_SERVER["argc"] == 2 ) {
	$poller_id = $_SERVER["argv"][1];
	if (!is_numeric($poller_id)) {
		api_syslog_cacti_log("The Poller ID is not numeric", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
		exit -1;
	}
}

/* let PHP run just as long as it has to */
ini_set("max_execution_time", "0");

/* record start time */
list($micro,$seconds) = split(" ", microtime());
$start = $seconds + $micro;

/* default the number of pollers to 0 */
$num_pollers = 0;

/* poller_id 1 tasks only */
if ($poller_id == 1) {
	/* get total number of polling items from the database for the specified poller */
	$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . $poller_id);
	$polling_hosts = array_merge(array(0 => array("id" => "0")), db_fetch_assoc("SELECT id FROM host WHERE (disabled = '' AND poller_id=" . $poller_id . ") ORDER BY id"));

	/* get total number of polling items from the database for all pollers */
	$all_num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id=" . $poller_id);
	$all_polling_hosts = array_merge(array(0 => array("id" => "0")), db_fetch_assoc("select id from host where disabled = '' ORDER BY id"));

	/* get the number of active pollers */
	$pollers = db_fetch_assoc("SELECT * FROM poller WHERE active = 'on'");
	$num_pollers = sizeof($pollers) + 1;

	/* update web paths for the poller */
	db_execute("REPLACE INTO settings (name,value) VALUES ('path_webroot','" . addslashes(($config["cacti_server_os"] == "win32") ? strtolower(str_replace("\\","/",substr(dirname(__FILE__), 0, 1))) . str_replace("\\","/",substr(dirname(__FILE__), 1)) : dirname(__FILE__)) . "')");

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
		print "Poller ID " . $poller_id . " does not exist in this system.\n";
		api_syslog_cacti_log("Poller " . $poller_id . " is attempting to run, but does not exist on this system.", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
		exit;
	}

	/* wait for signal from main poller to begin polling */
	while (1) {
		print "Poller " . $poller_id . " waiting on signal from main poller to begin.\n";
		$state = db_fetch_cell("SELECT run_state FROM poller where id=" . $poller_id);

		if ($state == "Ready") {
			/* show that I am not running */
			db_execute("UPDATE poller SET run_state='Running' WHERE id=". $poller_id);
			break;
		}

		if (($start + MAX_POLLER_RUNTIME) < time()) {
			api_syslog_cacti_log("Maximum runtime of " . MAX_POLLER_RUNTIME . " seconds exceeded for Poller_ID " . $poller_id . " - Exiting.", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
			db_execute("update poller set run_state = 'Timeout' where poller_id=" . $poller_id);
			exit;
		}
		sleep(1);
	}

	/* get total number of polling items from the database for the specified poller */
	$num_polling_items = db_fetch_cell("SELECT count(*) FROM poller_item WHERE poller_id='" . $poller_id . "'");
	$polling_hosts = db_fetch_assoc("SELECT id FROM host WHERE (disabled != 'on' and poller_id = '" . $poller_id . "') ORDER BY id");

	db_execute("UPDATE poller SET run_state='Running' where id=" . $poller_id);
}

/* retreive the number of concurrent process settings */
$concurrent_processes = read_config_option("concurrent_processes");

/* initialize counters for script file handling */
$host_count = 1;

/* initialize file creation flags */
$change_files = False;

/* initialize file and host count pointers */
$process_file_number = 0;
$first_host = 0;
$last_host = 0;

/* obtain some defaults from the database */
$poller_type = read_config_option("poller_type");
$max_threads = read_config_option("max_threads");

/* enter mainline processing */
if ((($num_polling_items > 0) || ($num_pollers > 1)) && (read_config_option("poller_enabled") == "on")) {
	/* Determine the number of hosts to process per file */
	$hosts_per_file = ceil(sizeof($polling_hosts) / $concurrent_processes );

	/* Determine Command Name */
	if (($config["cacti_server_os"] == "unix") and ($poller_type == "2")) {
		$command_string = read_config_option("path_cactid");
		$extra_args = "";
		$method = "cactid";
		chdir(dirname(read_config_option("path_cactid")));
	}else if ($config["cacti_server_os"] == "unix") {
		$command_string = read_config_option("path_php_binary");
		$extra_args = "-q " . $config["base_path"] . "/cmd.php";
		$method = "cmd.php";
	}else if ($poller_type == "2") {
		$command_string = read_config_option("path_cactid");
		$extra_args = "";
		$method = "cactid";
		chdir(dirname(read_config_option("path_cactid")));
	}else{
		$command_string = read_config_option("path_php_binary");
		$extra_args = "-q " . strtolower($config["base_path"] . "/cmd.php");
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
		$max_threads = "N/A";
	}

	while (1) {
		$polling_items = db_fetch_assoc("SELECT poller_id,end_time FROM poller_time WHERE poller_id = " . $poller_id);

		if (sizeof($polling_items) == $process_file_number) {
			/* set poller status complete */
			db_execute("UPDATE poller SET run_state='Complete' WHERE id=" . $poller_id);

			if ($poller_id != 1) {
				break;
			} else {
				/* process RRD output if any exists */
				process_poller_output($rrdtool_pipe);

				/* wait for other pollers to finish */
				$active_pollers = sizeof(db_fetch_assoc("SELECT * FROM poller WHERE active='on'"));
				while (1) {
					$running_pollers = sizeof(db_fetch_assoc("SELECT * FROM poller WHERE (active='on' AND (run_state = 'Running' OR run_state = 'Ready'))"));
					if ($running_pollers == 0) {
						process_poller_output($rrdtool_pipe);

						break;
					} else {
						process_poller_output($rrdtool_pipe);

						if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) {
							print "Main Poller Complete.  Waiting on " . ($active_pollers - $running_pollers) . " Pollers to complete.\n";
						}

						if (($start + MAX_POLLER_RUNTIME) < time()) {
							/* close rrdtool if poller is 0 */
							rrd_close($rrdtool_pipe);
							api_syslog_cacti_log("Maximum runtime of " . MAX_POLLER_RUNTIME . " seconds exceeded for Poller_ID " . $poller_id . " - Exiting.", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
							db_execute("update poller set run_state = 'Timeout' WHERE (active='on' AND run_state != 'Complete')");
							exit;
						}
					}

					/* give pollers another second to end */
					sleep(1);
				}

				/* take time and log performance data */
				list($micro,$seconds) = split(" ", microtime());
				$end = $seconds + $micro;

				api_syslog_cacti_log(sprintf("System Time: %01.4f s, " .
					"Total Pollers: %s, " .
					"Method: %s, " .
					"Processes: %s, " .
					"Threads: %s, " .
					"Hosts: %s, " .
					"Hosts/Process: %s",
					round($end-$start,4),
					$num_pollers,
					$method,
					$concurrent_processes,
					$max_threads,
					sizeof($all_polling_hosts),
					$hosts_per_file),
					SEV_NOTICE, $poller_id, 0, 0, true, FACIL_POLLER);

				db_execute("UPDATE poller SET run_state='Idle' WHERE active='on'");

				break;
			}
		}else {
			if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) {
				print "Waiting on " . ($process_file_number - sizeof($polling_items)) . "/$process_file_number Processes to Complete.\n";
			}

			/* process RRD output if any exists */
			if ($poller_id == 1) {
				process_poller_output($rrdtool_pipe);
			}

			/* end the process if the runtime exceeds MAX_POLLER_RUNTIME */
			if (($start + MAX_POLLER_RUNTIME) < time()) {
				/* close RRDTool */
				if ($poller_id == 1) { rrd_close($rrdtool_pipe); }

				api_syslog_cacti_log("Maximum runtime of " . MAX_POLLER_RUNTIME . " seconds exceeded for Poller_ID " . $poller_id . " - Exiting.", SEV_ERROR, $poller_id, 0, 0, true, FACIL_POLLER);
				db_execute("update poller set run_state = 'Timeout' where poller_id=" . $poller_id);

				/* manage cacti syslog size */
				api_syslog_manage_cacti_log(true);

				exit;
			}

			sleep(1);
		}
	}

	/* close RRDTool */
	if ($poller_id == 1) { rrd_close($rrdtool_pipe, $rrd_processes); }

	/* process poller commands */
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
					$recached_hosts = $recached_hosts + 1;
				} else {
					$first_host = false;
				}

				if ($first_host) {
					api_syslog_cacti_log("Recache Event Detected for Host", SEV_WARNING, $poller_id, 0, 0, true, FACIL_POLLER);
				}

				if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
					api_syslog_cacti_log("RECACHE: Re-cache for Host, data query #$data_query_id", SEV_NOTICE, $poller_id, 0, 0, true, FACIL_POLLER);
				}

				run_data_query($host_id, $data_query_id);

				if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
					api_syslog_cacti_log("RECACHE: Re-cache successful.", SEV_NOTICE, $poller_id, 0, 0, true, FACIL_POLLER);
				}
			}
		}

		db_execute("delete from poller_command where poller_id=" . $poller_id);

		/* take time and log performance data */
		list($micro,$seconds) = split(" ", microtime());
		$recache = $seconds + $micro;

		api_syslog_cacti_log(sprintf("Recache Runtime: %01.4f s, " .
			"Poller: %s",
			"Hosts Recached: %s",
			round($recache - $end,4),
			$poller_id,
			$recached_hosts),
			SEV_NOTICE, $poller_id, 0, 0, true, FACIL_POLLER);
	}

	if ($poller_id == 1) {
		/* graph export */
		graph_export();

		/* i don't know why we are doing this */
		db_execute("truncate table poller_output");

		/* i don't know why we are doing this */
		db_execute("truncate table poller_time");

		/* idle the pollers till the next polling cycle */
	   	db_execute("update poller set run_state = 'Wait' where active='on'");
	}

	if ($method == "cactid") {
		chdir(read_config_option("path_webroot"));
	}
}else{
	if ($poller_id == 1) {
		api_syslog_cacti_log("Either there are no pollers enabled, no items in your poller cache or polling is disabled. Make sure you have at least one data source created, your poller is active. If both are true, go to 'Utilities', and select 'Clear Poller Cache'.", SEV_WARNING, $poller_id, 0, 0, true, FACIL_POLLER);
	} else {
		db_execute("update poller set run_state = 'Complete' where id=" . $poller_id);
		api_syslog_cacti_log("Poller had not items to process.", SEV_WARNING, $poller_id, 0, 0, true, FACIL_POLLER);
	}
}

/* manage size of cacti syslog */
api_syslog_manage_cacti_log(true);

/* end mainline processing */

?>