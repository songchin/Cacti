<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

/* we are not talking to the browser */
$no_http_headers = true;

/* start initialization section */
include(dirname(__FILE__) . "/include/global.php");
include_once($config["base_path"] . "/lib/poller.php");
include_once($config["base_path"] . "/lib/data_query.php");
include_once($config["base_path"] . "/lib/graph_export.php");
include_once($config["base_path"] . "/lib/rrd.php");

/* record the start time */
list($micro,$seconds) = split(" ", microtime());
$poller_start         = $seconds + $micro;
$overhead_time = 0;

/* get number of polling items from the database */
$poller_interval = read_config_option("poller_interval");

/* retreive the last time the poller ran */
$poller_lastrun = read_config_option('poller_lastrun');

/* assume a scheduled task of either 60 or 300 seconds */
if (isset($poller_interval)) {
	if (isset($poller_lastrun)) {
		$last_poll_interval = $poller_start - $poller_lastrun;
	}else{
		$last_poll_interval = $poller_interval;
	}

	if (isset($last_poll_interval) && ($last_poll_interval > 300)) {
		$last_poll_interval = 300;
	}

	$num_polling_items = db_fetch_cell("select count(*) from poller_item where rrd_next_step<=0");
	$poller_runs       = ceil(($poller_start - $poller_lastrun) / $poller_interval);

	define("MAX_POLLER_RUNTIME", $poller_runs * $poller_interval - 2);
}else{
	$num_polling_items = db_fetch_cell("select count(*) from poller_item");
	$poller_runs       = 1;

	define("MAX_POLLER_RUNTIME", 298);
}

if (read_config_option('log_verbosity') >= POLLER_VERBOSITY_DEBUG) {
	echo "The number of runs is '" . $poller_runs . "'.\n";
}

/* get to see if we are polling faster than reported by the settings, if so, exit */
if (isset($poller_lastrun) && isset($poller_interval) && $poller_lastrun > 0) {
	if ($seconds - $poller_lastrun + 5 < MAX_POLLER_RUNTIME) {
		if (read_config_option('log_verbosity') >= POLLER_VERBOSITY_MEDIUM) {
			if ($config["cacti_server_os"] == "unix") {
				cacti_log("NOTE: Cron entry is configured to run too often!  The Cacti Poller Interval is $poller_interval seconds, but only " . ($seconds - $poller_lastrun) . ' seconds have passed since the last polling.', true, 'POLLER');
			}else{
				cacti_log("NOTE: Windows Scheduled Task is configured to run too often!  The Cacti Poller Interval is $poller_interval seconds, but only " . ($seconds - $poller_lastrun) . ' seconds have passed since the last polling.', true, 'POLLER');
			}
		}
		exit;
	}
}

/* check to see whether we have the poller interval set lower than the poller is actually ran, if so, issue a warning */
if (($seconds - $poller_lastrun - 5 > MAX_POLLER_RUNTIME) && $poller_lastrun > 0) {
	if ($config["cacti_server_os"] == "unix") {
		cacti_log("WARNING: Cron entry is out of sync with Cacti Poller Interval!  The Poller Interval is $poller_interval seconds, but " . ($seconds - $poller_lastrun) . ' seconds have passed since the last poll!', true, 'POLLER');
	}else{
		cacti_log("WARNING: Windows Scheduled Task is out of sync with Cacti Poller Interval!  The Poller Interval is $poller_interval seconds, but " . ($seconds - $poller_lastrun) . ' seconds have passed since the last poll!', true, 'POLLER');
	}
}

db_execute("replace into settings (name,value) values ('poller_lastrun'," . $seconds . ')');

/* let PHP only run 1 second longer than the max runtime */
ini_set("max_execution_time", MAX_POLLER_RUNTIME + 1);

$poller_runs_completed = 0;

while ($poller_runs_completed < $poller_runs) {
	/* record the start time for this loop */
	list($micro,$seconds) = split(" ", microtime());
	$loop_start = $seconds + $micro;

	/* calculate overhead time */
	if ($overhead_time == 0) {
		$overhead_time = $loop_start - $poller_start;
	}

	$polling_hosts = array_merge(array(0 => array("id" => "0")), db_fetch_assoc("select id from host where disabled = '' order by id"));

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

	/* update web paths for the poller */
	db_execute("replace into settings (name,value) values ('path_webroot','" . addslashes(($config["cacti_server_os"] == "win32") ? strtr(strtolower(substr(dirname(__FILE__), 0, 1)) . substr(dirname(__FILE__), 1),"\\", "/") : dirname(__FILE__)) . "')");

	/* obtain some defaults from the database */
	$poller = read_config_option("poller_type");
	$max_threads = read_config_option("max_threads");

	/* initialize poller_time and poller_output tables, check poller_output for issues */
	db_execute("TRUNCATE TABLE poller_time");

	$issues = db_fetch_assoc("SELECT local_data_id, rrd_name FROM poller_output");
	if (sizeof($issues)) {
		$issue_list = "";
		$count = 0;
		foreach($issues as $issue) {
			if ($count == 0) {
				$issue_list .= $issue["rrd_name"] . "(DS[" . $issue["local_data_id"] . "])";
			}else{
				$issue_list .= ", " . $issue["rrd_name"] . "(DS[" . $issue["local_data_id"] . "])";
			}
			$count++;
		}

		cacti_log("WARNING: Poller Output Table not Empty.  Potential Data Source Issues for Data Sources: $issue_list", FALSE, "POLLER");
		db_execute("TRUNCATE TABLE poller_output");
	}

	/* mainline */
	if (read_config_option("poller_enabled") == "on") {
		/* determine the number of hosts to process per file */
		$hosts_per_file = ceil(sizeof($polling_hosts) / $concurrent_processes );

		/* exit poller if cactid is selected and file does not exist */
		if (($poller == "2") && (!file_exists(read_config_option("path_cactid")))) {
			cacti_log("ERROR: The path: " . read_config_option("path_cactid") . " is invalid.  Can not continue", true, "POLLER");
			exit;
		}

		/* Determine Command Name */
		if ($poller == "2") {
			$command_string = read_config_option("path_cactid");
			$extra_args = "";
			$method = "cactid";
			chdir(dirname(read_config_option("path_cactid")));
		}else if ($config["cacti_server_os"] == "unix") {
			$command_string = read_config_option("path_php_binary");
			$extra_args = "-q " . $config["base_path"] . "/cmd.php";
			$method = "cmd.php";
		}else{
			$command_string = read_config_option("path_php_binary");
			$extra_args = "-q " . strtolower($config["base_path"] . "/cmd.php");
			$method = "cmd.php";
		}

		/* Populate each execution file with appropriate information */
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
				exec_background($command_string, "$extra_args $first_host $last_host");
				usleep(100000);

				$host_count = 1;
				$change_files = False;
				$process_file_number++;
				$first_host = 0;
				$last_host = 0;
			} /* End change_files */
		} /* End For Each */

		if ($host_count > 1) {
			$last_host = $item["id"];

			exec_background($command_string, "$extra_args $first_host $last_host");
			usleep(100000);

			$process_file_number++;
		}

		/* insert the current date/time for graphs */
		db_execute("replace into settings (name,value) values ('date',NOW())");

		if ($poller == "1") {
			$max_threads = "N/A";
		}

		/* open a pipe to rrdtool for writing */
		$rrdtool_pipe = rrd_init();

		$rrds_processed = 0;
		while (1) {
			$polling_items = db_fetch_assoc("select poller_id,end_time from poller_time where poller_id=0");

			if (sizeof($polling_items) == $process_file_number) {
				$rrds_processed = $rrds_processed + process_poller_output($rrdtool_pipe, TRUE);

				/* take time and log performance data */
				list($micro,$seconds) = split(" ", microtime());
				$loop_end = $seconds + $micro;

				$cacti_stats = sprintf(
					"Time:%01.4f " .
					"Method:%s " .
					"Processes:%s " .
					"Threads:%s " .
					"Hosts:%s " .
					"HostsPerProcess:%s " .
					"DataSources:%s " .
					"RRDsProcessed:%s",
					round($loop_end-$loop_start,4),
					$method,
					$concurrent_processes,
					$max_threads,
					sizeof($polling_hosts),
					$hosts_per_file,
					$num_polling_items,
					$rrds_processed);

				cacti_log("STATS: " . $cacti_stats ,true,"SYSTEM");

				/* insert poller stats into the settings table */
				db_execute("replace into settings (name,value) values ('stats_poller','$cacti_stats')");

				break;
			}else {
				if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_MEDIUM) {
					print "Waiting on " . ($process_file_number - sizeof($polling_items)) . "/$process_file_number pollers.\n";
				}

				$rrds_processed = $rrds_processed + process_poller_output($rrdtool_pipe);

				/* end the process if the runtime exceeds MAX_POLLER_RUNTIME */
				if (($poller_start + MAX_POLLER_RUNTIME) < time()) {
					rrd_close($rrdtool_pipe);
					cacti_log("Maximum runtime of " . MAX_POLLER_RUNTIME . " seconds exceeded. Exiting.", true, "POLLER");

					/* take time and log performance data */
					list($micro,$seconds) = split(" ", microtime());
					$loop_end = $seconds + $micro;

					$cacti_stats = sprintf(
						"Time:%01.4f " .
						"Method:%s " .
						"Processes:%s " .
						"Threads:%s " .
						"Hosts:%s " .
						"HostsPerProcess:%s " .
						"DataSources:%s " .
						"RRDsProcessed:%s",
						round($loop_end-$loop_start,4),
						$method,
						$concurrent_processes,
						$max_threads,
						sizeof($polling_hosts),
						$hosts_per_file,
						$num_polling_items,
						$rrds_processed);

					cacti_log("STATS: " . $cacti_stats ,true,"SYSTEM");

					/* insert poller stats into the settings table */
					db_execute("replace into settings (name,value) values ('stats_poller','$cacti_stats')");

					break;
				}

				sleep(1);
			}
		}

		rrd_close($rrdtool_pipe);

		/* process poller commands */
		if (db_fetch_cell("select count(*) from poller_command") > 0) {
			$command_string = read_config_option("path_php_binary");
			$extra_args = "-q " . $config["base_path"] . "/poller_commands.php";
			exec_background($command_string, "$extra_args");
		}

		/* graph export */
		if ((read_config_option("export_type") != "disabled") && (read_config_option("export_timing") != "disabled")) {
			$command_string = read_config_option("path_php_binary");
			$extra_args = "-q " . $config["base_path"] . "/poller_export.php";
			exec_background($command_string, "$extra_args");
		}

		if ($method == "cactid") {
			chdir(read_config_option("path_webroot"));
		}
	}else if (read_config_option('log_verbosity') >= POLLER_VERBOSITY_MEDIUM) {
		cacti_log("NOTE: There are no items in your poller for this polling cycle!", TRUE, "POLLER");
	}

	$poller_runs_completed++;

	/* record the start time for this loop */
	list($micro,$seconds) = split(" ", microtime());
	$loop_end = $seconds + $micro;

	if (($loop_end - $loop_start) < $poller_interval) {
		if ($poller_runs_completed == 1) {
			$sleep_time = ($poller_interval - ($loop_end - $loop_start) - $overhead_time);
		}else{
			$sleep_time = ($poller_interval -  ($loop_end - $loop_start));
		}

		/* log some nice debug information */
		if (read_config_option('log_verbosity') >= POLLER_VERBOSITY_DEBUG) {
			echo "Loop  Time is: " . ($loop_end - $loop_start) . "\n";
			echo "Sleep Time is: " . $sleep_time . "\n";
			echo "Total Time is: " . ($loop_end - $poller_start) . "\n";
 		}

		/* sleep the appripriate amount of time */
		if ($poller_runs_completed < $poller_runs) {
			usleep($sleep_time * 1000000);
		}
	}else if (read_config_option('log_verbosity') >= POLLER_VERBOSITY_MEDIUM) {
		cacti_log("WARNING: Cacti Polling Cycle Exceeded Poller Interval by " . $loop_end-$loop_start-$poller_interval . " seconds", TRUE, "POLLER");
	}
}
// End Mainline Processing

?>
