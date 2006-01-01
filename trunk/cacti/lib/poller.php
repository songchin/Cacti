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

function repopulate_poller_cache() {
	db_execute("truncate table poller_item");

	$data_sources = db_fetch_assoc("select id from data_source");

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $item) {
			update_poller_cache($item["id"], true);
		}
	}
}

function update_poller_cache_from_query($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	$data_sources = db_fetch_assoc("select
		data_source.id
		from data_source,data_source_field
		where data_source.id=data_source_field.data_source_id
		and data_source.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
		and data_source_field.name = 'data_query_id'");

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			update_poller_cache($data_source["id"]);
		}
	}
}

$c_xml_data = array();

function update_poller_cache($data_source_id, $truncate_performed = false) {
	global $c_xml_data;

	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/api_poller.php");

	if (empty($data_source_id)) {
		return;
	}

	/* clear cache for this local_data_id */
	if (!$truncate_performed) {
		db_execute("delete from poller_item where local_data_id = $data_source_id");
	}

	/* fetch information about this data source */
	$data_source = db_fetch_row("select host_id,data_input_type,active,rrd_step from data_source where id = $data_source_id");

	/* device is marked as disabled */
	if ((!empty($data_source["host_id"])) && (db_fetch_cell("select disabled from host where id = " . $data_source["host_id"]) == "on")) {
		return;
	}

	$field_list = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = $data_source_id"), "name", "value");

	if (!empty($data_source["active"])) {
		if (($data_source["data_input_type"] == DATA_INPUT_TYPE_SCRIPT) && (isset($field_list["script_id"]))) {
			/* how does the script get its data? */
			$script_input_type = db_fetch_cell("select type_id from data_input where id = " . $field_list["script_id"]);

			/* fall back to non-script server actions if the user is running a version of php older than 4.3 */
			if (($script_input_type == SCRIPT_INPUT_TYPE_PHP_SCRIPT_SERVER) && (function_exists("proc_open"))) {
				$action = POLLER_ACTION_SCRIPT_PHP;
				$script_path = get_full_script_path($data_source_id);
			}else if (($script_input_type == SCRIPT_INPUT_TYPE_PHP_SCRIPT_SERVER) && (!function_exists("proc_open"))) {
				$action = POLLER_ACTION_SCRIPT;
				$script_path = read_config_option("path_php_binary") . " -q " . get_full_script_path($data_source_id);
			}else{
				$action = POLLER_ACTION_SCRIPT;
				$script_path = get_full_script_path($data_source_id);
			}

			$num_output_fields = db_fetch_cell("select count(*) from data_input_fields where data_input_id = " . $field_list["script_id"] . " and input_output = 'out' and update_rra = 1");

			api_poller_cache_item_add($data_source["host_id"], $data_source_id, $data_source["rrd_step"], $action, (($num_output_fields == 1) ? db_fetch_cell("select data_source_name from data_source_item where data_source_id = $data_source_id") : ""), 1, addslashes($script_path));
		}else if ($data_source["data_input_type"] == DATA_INPUT_TYPE_SNMP) {
			$data_source_items = db_fetch_assoc("select data_source_name,field_input_value from data_source_item where data_source_id = $data_source_id");

			if (sizeof($data_source_items) > 0) {
				foreach ($data_source_items as $item) {
					api_poller_cache_item_add($data_source["host_id"], $data_source_id, $data_source["rrd_step"], POLLER_ACTION_SNMP, $item["data_source_name"], 1, $item["field_input_value"]);
				}
			}
		}else if (($data_source["data_input_type"] == DATA_INPUT_TYPE_DATA_QUERY) && (isset($field_list["data_query_id"])) && (isset($field_list["data_query_index"]))) {
			/* how does this data query get its data? */
			$data_query = api_data_query_get($field_list["data_query_id"]);

			$data_query_input_type = db_fetch_cell("select input_type from snmp_query where id = " . $field_list["data_query_id"]);

			/* obtain a list of data source items for this data source */
			$data_source_items = db_fetch_assoc("select id,field_input_value,data_source_name from data_source_item where data_source_id = $data_source_id");

			if (sizeof($data_source_items) > 0) {
				foreach ($data_source_items as $item) {
					$data_query_field = api_data_query_field_get_by_name($field_list["data_query_id"], $item["field_input_value"]);

					if ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
						api_poller_cache_item_add($data_source["host_id"], $data_source_id, $data_source["rrd_step"], POLLER_ACTION_SNMP, $item["data_source_name"], sizeof($data_source_items), $data_query_field["source"] . "." . $field_list["data_query_index"]);
					}else if (($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) || ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY)) {
						/* fall back to non-script server actions if the user is running a version of php older than 4.3 */
						if (($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) && (function_exists("proc_open"))) {
							$action = POLLER_ACTION_SCRIPT_PHP;
							$script_path = $data_query["script_path"] . " " . $data_query["script_server_function"] . " " . DATA_QUERY_SCRIPT_ARG_GET . " " . $data_query_field["source"] . " " . $field_list["data_query_index"];
						}else if (($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) && (!function_exists("proc_open"))) {
							$action = POLLER_ACTION_SCRIPT;
							$script_path = read_config_option("path_php_binary") . " -q " . $data_query["script_path"] . " " . DATA_QUERY_SCRIPT_ARG_GET . " " . $data_query_field["source"] . " " . $field_list["data_query_index"];
						}else{
							$action = POLLER_ACTION_SCRIPT;
							$script_path = $data_query["script_path"] . " " . DATA_QUERY_SCRIPT_ARG_GET . " " . $data_query_field["source"] . " " . $field_list["data_query_index"];
						}

						api_poller_cache_item_add($data_source["host_id"], $data_source_id, $data_source["rrd_step"], $action, $item["data_source_name"], sizeof($data_source_items), addslashes($script_path));
					}
				}
			}
		}
	}
}

/* exec_poll - executes a command and returns its output
   @arg $command - the command to execute
   @returns - the output of $command after execution */
function exec_poll($command) {
	if (function_exists("stream_set_timeout")) {
		if (CACTI_SERVER_OS == "win32") {
			$fp = popen($command, "rb");
		}else{
			$fp = popen($command, "r");
		}

		/* set script server timeout */
		$script_timeout = read_config_option("script_timeout");
		stream_set_timeout($fp, $script_timeout);

		/* get output from command */
		$output = fgets($fp, 4096);

		/* determine if the script timedout */
		$info = stream_get_meta_data($fp);

		if ($info['timed_out']) {
			cacti_log("ERROR: Script Timed Out\n", true);
		}

		pclose($fp);
	}else{
		$output = `$command`;
	}

	return $output;
}

/* exec_poll_php - sends a command to the php script server and returns the
	output
   @arg $command - the command to send to the php script server
   @arg $using_proc_function - whether or not this version of php is making use
	of the proc_open() and proc_close() functions (php 4.3+)
   @arg $pipes - the array of r/w pipes returned from proc_open()
   @arg $proc_fd - the file descriptor returned from proc_open()
   @returns - the output of $command after execution against the php script
	server */
function exec_poll_php($command, $using_proc_function, $pipes, $proc_fd) {
	/* execute using php process */
	if ($using_proc_function == 1) {
		if (is_resource($proc_fd)) {
			/* $pipes now looks like this:
			 * 0 => writeable handle connected to child stdin
			 * 1 => readable handle connected to child stdout
			 * 2 => any error output will be sent to child stderr */

			/* set script server timeout */
			$script_timeout = read_config_option("script_timeout");
			stream_set_timeout($pipes[0], $script_timeout);

			/* send command to the php server */
			fwrite($pipes[0], $command . "\r\n");

			/* get result from server */
			$output = fgets($pipes[1], 4096);

			/* determine if the script timedout */
			$info = stream_get_meta_data($pipes[0]);

			if ($info['timed_out']) {
				cacti_log(_("ERROR: Script Server Timed Out"), true);
				$output = "U";
			}elseif (substr_count($output, _("ERROR")) > 0) {
				$output = "U";
			}
		}
	}else{
		/* formulate command */
		$command = read_config_option("path_php_binary") . " " . $command;
		if (function_exists("stream_set_timeout")) {
			if (CACTI_SERVER_OS == "unix") {
				$fp = popen($command, "r");
			}else{
				$fp = popen($command, "rb");
			}

			/* set script server timeout */
			$script_timeout = read_config_option("script_timeout");
			stream_set_timeout($fp, $script_timeout);

			/* get output from command */
			$output = fgets($fp, 4096);

			/* determine if the script timedout */
			$info = stream_get_meta_data($pipes[0]);

			if ($info['timed_out']) {
				cacti_log("ERROR: Script Timed Out\n", true);
			}

			$pclose($fp);
		}else{
			$output = `$command`;
		}
	}

	return $output;
}

/* exec_background - executes a program in the background so that php can continue
	to execute code in the foreground
   @arg $filename - the full pathname to the script to execute
   @arg $args - any additional arguments that must be passed onto the executable */
function exec_background($filename, $args = "", $poller_id = 1) {
	if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
		api_log_log(sprintf(_("About to Spawn a Remote Process [CMD: %s, ARGS: %s]"),$filename, $args), SEV_DEBUG, FACIL_POLLER, "", $poller_id);
	}

	if (file_exists($filename)) {
		if (CACTI_SERVER_OS == "win32") {
			pclose(popen("start \"Cactiplus\" /I \"" . $filename . "\" " . $args, "rb"));
		}else{
			exec($filename . " " . $args . " > /dev/null &");
		}
	}
}

/* update_reindex_cache - builds a cache that is used by the poller to determine if the
	indexes for a particular data query/host have changed
   @arg $host_id - the id of the host to which the data query belongs
   @arg $data_query_id - the id of the data query to rebuild the reindex cache for */
function update_reindex_cache($host_id, $data_query_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/snmp.php");
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* will be used to keep track of sql statements to execute later on */
	$recache_stack = array();

	/* get information about the host */
	$host = api_device_get($host_id);

	/* get information about the host->data query assignment */
	$host_data_query = api_device_data_query_get($host_id, $data_query_id);

	/* get information about the data query */
	$data_query = api_data_query_get($data_query_id);

	switch ($host_data_query["reindex_method"]) {
		case DATA_QUERY_AUTOINDEX_NONE:
			break;
		case DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME:
			/* the uptime backwards method requires snmp, so make sure snmp is actually enabled
			 * on this device first */
			if ($host["snmp_community"] != "") {
				$assert_value = cacti_snmp_get($host["hostname"],
					$host["snmp_community"],
					".1.3.6.1.2.1.1.3.0",
					$host["snmp_version"],
					$host["snmpv3_auth_username"],
					$host["snmpv3_auth_password"],
					$host["snmpv3_auth_protocol"],
					$host["snmpv3_priv_passphrase"],
					$host["snmpv3_priv_protocol"],
					$host["snmp_port"],
					$host["snmp_timeout"],
					SNMP_POLLER);

				array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values (" . sql_sanitize($host_id) . "," . sql_sanitize($data_query_id) . ",0,'<','" . sql_sanitize($assert_value) . "','.1.3.6.1.2.1.1.3.0')");
			}

			break;
		case DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE:
			/* this method requires that some command/oid can be used to determine the
			 * current number of indexes in the data query */
			$assert_value = api_data_query_cache_num_rows_get($data_query_id, $host_id);

			if ($data_query_type == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
				if ($data_query["snmp_oid_num_rows"] != "") {
					array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values (" . sql_sanitize($host_id) . "," . sql_sanitize($data_query_id) . ",0,'=','" . sql_sanitize($assert_value) . "','" . sql_sanitize($data_query["snmp_oid_num_rows"]) . "')");
				}
			}else if ($data_query_type == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) {
				array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values (" . sql_sanitize($host_id) . "," . sql_sanitize($data_query_id) . ",1,'=','" . sql_sanitize($assert_value) . "','" . sql_sanitize(get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . DATA_QUERY_SCRIPT_ARG_NUM_INDEXES, $data_query_xml["script_path"], $host_id)) . "')");
			}

			break;
		case DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION:
			$primary_indexes = api_data_query_cache_field_get($data_query_id, $host_id, $data_query["sort_field"]);

			if (sizeof($primary_indexes) > 0) {
				foreach ($primary_indexes as $index) {
					$assert_value = $index["field_value"];

					if ($data_query_type == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values (" . sql_sanitize($host_id) . "," . sql_sanitize($data_query_id) . ",0,'=','" . sql_sanitize($assert_value) . "','" . sql_sanitize($index["oid"]) . "')");
					}else if ($data_query_type == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values (" . sql_sanitize($host_id) . "," . sql_sanitize($data_query_id) . ",1,'=','" . sql_sanitize($assert_value) . "','" . sql_sanitize(get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . DATA_QUERY_SCRIPT_ARG_GET . " " . $data_query_xml["fields"]{$data_query["sort_field"]}["query_name"] . " " . $index["snmp_index"], $data_query_xml["script_path"], $host_id)) . "')");
					}
				}
			}

			break;
	}

	/* save the delete for last since we need to reference this table in the code above */
	db_execute("delete from poller_reindex where host_id=$host_id and data_query_id=$data_query_id");

	for ($i=0; $i<count($recache_stack); $i++) {
		db_execute($recache_stack[$i]);
	}
}

/* process_poller_output - grabs data from the 'poller_output' table and feeds the *completed*
	results to RRDTool for processing
   @arg $rrdtool_pipe - the array of pipes containing the file descriptor for rrdtool */
function process_poller_output($rrdtool_pipe, $print_to_stdout = false) {
	require_once(CACTI_BASE_PATH . "/lib/sys/rrd.php");

	/* let's count the number of rrd files we processed */
	$rrds_processed = 0;

	/* create/update the rrd files */
	$results = db_fetch_assoc("select
		poller_output.output,
		poller_output.time,
		poller_output.local_data_id,
		poller_item.rrd_path,
		poller_item.rrd_name,
		poller_item.rrd_num
		from poller_output,poller_item
		where (poller_output.local_data_id=poller_item.local_data_id and poller_output.rrd_name=poller_item.rrd_name)");

	if (sizeof($results) > 0) {
		/* create an array keyed off of each .rrd file */
		foreach ($results as $item) {
			$value = rtrim(strtr(strtr($item["output"],'\r',''),'\n',''));
			$unix_time = strtotime($item["time"]);

			$rrd_update_array{$item["rrd_path"]}["data_source_id"] = $item["local_data_id"];

			/* single one value output */
			if ((is_numeric($value)) || ($value == "U")) {
				$rrd_update_array{$item["rrd_path"]}["times"][$unix_time]{$item["rrd_name"]} = $value;
			/* multiple value output (only supported for scripts) */
			}else{
				$values = explode(" ", $value);

				$rrd_field_names = array_rekey(db_fetch_assoc("select data_source_name,field_input_value from data_source_item where data_source_id = " . $item["local_data_id"]), "field_input_value", "data_source_name");

				for ($i=0; $i<count($values); $i++) {
					if (preg_match("/^([a-zA-Z0-9_.-]+):([+-0-9Ee.]+)$/", $values[$i], $matches)) {
						if (isset($rrd_field_names{$matches[1]})) {
							api_log_log("Parsed MULTI output field '" . $matches[0] . "' [map " . $matches[1] . "->" . $rrd_field_names{$matches[1]} . "]", SEV_DEBUG, FACIL_POLLER, "", $poller_id, $host_id, $print_to_stdout);

							$rrd_update_array{$item["rrd_path"]}["times"][$unix_time]{$rrd_field_names{$matches[1]}} = $matches[2];
						}
					}
				}
			}

			/* fallback values */
			if ((!isset($rrd_update_array{$item["rrd_path"]}["times"][$unix_time])) && ($item["rrd_name"] != "")) {
				$rrd_update_array{$item["rrd_path"]}["times"][$unix_time]{$item["rrd_name"]} = "U";
			}else if ((!isset($rrd_update_array{$item["rrd_path"]}["times"][$unix_time])) && ($item["rrd_name"] == "")) {
				unset($rrd_update_array{$item["rrd_path"]});
			}
		}

		/* make sure each .rrd file has complete data */
		reset($results);
		foreach ($results as $item) {
			$unix_time = strtotime($item["time"]);

			if (isset($rrd_update_array{$item["rrd_path"]}["times"][$unix_time])) {
				if ($item["rrd_num"] <= sizeof($rrd_update_array{$item["rrd_path"]}["times"][$unix_time])) {
					db_execute("delete from poller_output where local_data_id = " . $item["local_data_id"] . " and rrd_name = '" . $item["rrd_name"] . "' and time = '" . $item["time"] . "'");
				}else{
					unset($rrd_update_array{$item["rrd_path"]}["times"][$unix_time]);
				}
			}
		}

		$rrds_processed = rrdtool_function_update($rrd_update_array, $rrdtool_pipe);
	}

	return $rrds_processed;
}

/* update_poller_status - updates the poller table with informaton about each pollers status.
   It will also output to the appropriate log file when an event occurs.
   @arg $status - (int constant) the status of the poller (Up/Down)
   @arg $poller_id - (int) the poller ID for the results
   @arg $poller_time - (array) the start_time, end_time and total_time for the poller
*/
function update_poller_status($status, $poller_id, $pollers, $poller_time) {
	$issue_log_message   = false;
	$poller_failure_count  = read_config_option("poller_failure_count");
	$poller_recovery_count = read_config_option("poller_recovery_count");

	if ($status == POLLER_DOWN) {
		/* update total polls, failed polls and availability */
		$pollers[$poller_id]["failed_polls"]++;
		$pollers[$poller_id]["total_polls"]++;
		$pollers[$poller_id]["availability"] = 100 * ($pollers[$poller_id]["total_polls"] - $pollers[$poller_id]["failed_polls"]) / $pollers[$poller_id]["total_polls"];

		/* set the error message */
		$pollers[$poller_id]["status_last_error"] = _("Poller is down for some reason");

		/* determine if to send an alert and update remainder of statistics */
		if ($pollers[$poller_id]["status"] == POLLER_UP) {
			/* increment the event failure count */
			$pollers[$poller_id]["status_event_count"]++;

			/* if it's time to issue an error message, indicate so */
			if ($pollers[$poller_id]["status_event_count"] >= $poller_failure_count) {
				/* poller is now down, flag it that way */
				$pollers[$poller_id]["status"] = POLLER_DOWN;

				$issue_log_message = true;

				/* update the failure date only if the failure count is 1 */
				if ($poller_failure_count == 1) {
					$pollers[$poller_id]["status_fail_date"] = date("Y-m-d h:i:s");
				}
			/* poller is down, but not ready to issue log message */
			} else {
				/* poller down for the first time, set event date */
				if ($pollers[$poller_id]["status_event_count"] == 1) {
					$pollers[$poller_id]["status_fail_date"] = date("Y-m-d h:i:s");
				}
			}
		/* poller is recovering, put back in failed state */
		} elseif ($pollers[$poller_id]["status"] == POLLER_RECOVERING) {
			$pollers[$poller_id]["status_event_count"] = 1;
			$pollers[$poller_id]["status"] = POLLER_DOWN;

		/* poller was unknown and now is down */
		} elseif ($pollers[$poller_id]["status"] == POLLER_UNKNOWN) {
			$pollers[$poller_id]["status"] = POLLER_DOWN;
			$pollers[$poller_id]["status_event_count"] = 0;
		} else {
			$pollers[$poller_id]["status_event_count"]++;
		}
	/* poller is up!! */
	} else {
		/* update total polls and availability */
		$pollers[$poller_id]["total_polls"]++;
		$pollers[$poller_id]["availability"] = 100 * ($pollers[$poller_id]["total_polls"] - $pollers[$poller_id]["failed_polls"]) / $pollers[$poller_id]["total_polls"];

		/* update times as required */
		$pollers[$poller_id]["cur_time"] = $ping_time;

		/* maximum time */
		if ($ping_time > $pollers[$poller_id]["max_time"])
			$pollers[$poller_id]["max_time"] = $ping_time;

		/* minimum time */
		if ($ping_time < $pollers[$poller_id]["min_time"])
			$pollers[$poller_id]["min_time"] = $ping_time;

		/* average time */
		$pollers[$poller_id]["avg_time"] = (($pollers[$poller_id]["total_polls"]-1-$pollers[$poller_id]["failed_polls"])
			* $pollers[$poller_id]["avg_time"] + $ping_time) / ($pollers[$poller_id]["total_polls"]-$pollers[$poller_id]["failed_polls"]);

		/* the poller was down, now it's recovering */
		if (($pollers[$poller_id]["status"] == POLLER_DOWN) || ($pollers[$poller_id]["status"] == POLLER_RECOVERING )) {
			/* just up, change to recovering */
			if ($pollers[$poller_id]["status"] == POLLER_DOWN) {
				$pollers[$poller_id]["status"] = POLLER_RECOVERING;
				$pollers[$poller_id]["status_event_count"] = 1;
			} else {
				$pollers[$poller_id]["status_event_count"]++;
			}

			/* if it's time to issue a recovery message, indicate so */
			if ($pollers[$poller_id]["status_event_count"] >= $poller_recovery_count) {
				/* host is up, flag it that way */
				$pollers[$poller_id]["status"] = POLLER_UP;

				$issue_log_message = true;

				/* update the recovery date only if the recovery count is 1 */
				if ($poller_recovery_count == 1) {
					$pollers[$poller_id]["status_rec_date"] = date("Y-m-d h:i:s");
				}

				/* reset the event counter */
				$pollers[$poller_id]["status_event_count"] = 0;
			/* host is recovering, but not ready to issue log message */
			} else {
				/* poller recovering for the first time, set event date */
				if ($pollers[$poller_id]["status_event_count"] == 1) {
					$pollers[$poller_id]["status_rec_date"] = date("Y-m-d h:i:s");
				}
			}
		} else {
		/* poller was unknown and now is up */
			$pollers[$poller_id]["status"] = POLLER_UP;
			$pollers[$poller_id]["status_event_count"] = 0;
		}
	}

	/* if there is supposed to be an event generated, do it */
	if ($issue_log_message) {
		if ($pollers[$poller_id]["status"] == HOST_DOWN) {
			api_log_log(_("POLLER EVENT: Poller is DOWN Message: ") . $pollers[$poller_id]["status_last_error"], SEV_CRITICAL, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
		} else {
			api_log_log(_("POLLER EVENT: Poller Returned from DOWN State"), SEV_NOTICE, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
		}
	}

	db_execute("UPDATE poller SET
		run_state = '" . $pollers[$poller_id]["status"] . "',
		status_event_count = '" . $pollers[$poller_id]["status_event_count"] . "',
		status_fail_date = '" . $pollers[$poller_id]["status_fail_date"] . "',
		status_rec_date = '" . $pollers[$poller_id]["status_rec_date"] . "',
		status_last_error = '" . $pollers[$poller_id]["status_last_error"] . "',
		min_time = '" . $pollers[$poller_id]["min_time"] . "',
		max_time = '" . $pollers[$poller_id]["max_time"] . "',
		cur_time = '" . $pollers[$poller_id]["cur_time"] . "',
		avg_time = '" . $pollers[$poller_id]["avg_time"] . "',
		total_polls = '" . $pollers[$poller_id]["total_polls"] . "',
		failed_polls = '" . $pollers[$poller_id]["failed_polls"] . "',
		availability = '" . $pollers[$poller_id]["availability"] . "'
		where id = '" . $pollers[$poller_id]["id"] . "'");
}

/* update_host_status - updates the host table with informaton about it's status.
   It will also output to the appropriate log file when an event occurs.
   @arg $status - (int constant) the status of the host (Up/Down)
   @arg $host_id - (int) the host ID for the results
   @arg $hosts - (array) a memory resident host table for speed
   @arg $ping - (class array) results of the ping command */
function update_host_status($poller_id, $status, $host_id, &$hosts, &$ping, $ping_availability, $print_data_to_stdout) {
	$issue_log_message   = false;
	$ping_failure_count  = read_config_option("ping_failure_count");
	$ping_recovery_count = read_config_option("ping_recovery_count");

	if ($status == HOST_DOWN) {
		/* update total polls, failed polls and availability */
		$hosts[$host_id]["failed_polls"]++;
		$hosts[$host_id]["total_polls"]++;
		$hosts[$host_id]["availability"] = 100 * ($hosts[$host_id]["total_polls"] - $hosts[$host_id]["failed_polls"]) / $hosts[$host_id]["total_polls"];

		/* determine the error message to display */
		if ($ping_availability == AVAIL_SNMP_AND_PING) {
			if ($hosts[$host_id]["snmp_community"] == "") {
				$hosts[$host_id]["status_last_error"] = $ping->ping_response;
			}else {
				$hosts[$host_id]["status_last_error"] = $ping->snmp_response . ", " . $ping->ping_response;
			}
		}elseif ($ping_availability == AVAIL_SNMP) {
			if ($hosts[$host_id]["snmp_community"] == "") {
				$hosts[$host_id]["status_last_error"] = _("Device does not require SNMP");
			}else {
				$hosts[$host_id]["status_last_error"] = $ping->snmp_response;
			}
		}elseif ($ping_availability == AVAIL_NONE) {
			$hosts[$host_id]["status_last_error"] = _("Availability disabled for host");
		}else {
			$hosts[$host_id]["status_last_error"] = $ping->ping_response;
		}

		/* determine if to send an alert and update remainder of statistics */
		if ($hosts[$host_id]["status"] == HOST_UP) {
			/* increment the event failure count */
			$hosts[$host_id]["status_event_count"]++;

			/* if it's time to issue an error message, indicate so */
			if ($hosts[$host_id]["status_event_count"] >= $ping_failure_count) {
				/* host is now down, flag it that way */
				$hosts[$host_id]["status"] = HOST_DOWN;

				$issue_log_message = true;

				/* update the failure date only if the failure count is 1 */
				if ($ping_failure_count == 1) {
					$hosts[$host_id]["status_fail_date"] = date("Y-m-d h:i:s");
				}
			/* host is down, but not ready to issue log message */
			} else {
				/* host down for the first time, set event date */
				if ($hosts[$host_id]["status_event_count"] == 1) {
					$hosts[$host_id]["status_fail_date"] = date("Y-m-d h:i:s");
				}
			}
		/* host is recovering, put back in failed state */
		} elseif ($hosts[$host_id]["status"] == HOST_RECOVERING) {
			$hosts[$host_id]["status_event_count"] = 1;
			$hosts[$host_id]["status"] = HOST_DOWN;

		/* host was unknown and now is down */
		} elseif ($hosts[$host_id]["status"] == HOST_UNKNOWN) {
			$hosts[$host_id]["status"] = HOST_DOWN;
			$hosts[$host_id]["status_event_count"] = 0;
		} else {
			$hosts[$host_id]["status_event_count"]++;
		}
	/* host is up!! */
	} else {
		/* update total polls and availability */
		$hosts[$host_id]["total_polls"]++;
		$hosts[$host_id]["availability"] = 100 * ($hosts[$host_id]["total_polls"] - $hosts[$host_id]["failed_polls"]) / $hosts[$host_id]["total_polls"];

		/* determine the ping statistic to set and do so */
		if ($ping_availability == AVAIL_SNMP_AND_PING) {
			if ($hosts[$host_id]["snmp_community"] == "") {
				$ping_time = $ping->ping_status;
			}else {
				/* calculate the average of the two times */
				$ping_time = ($ping->snmp_status + $ping->ping_status) / 2;
			}
		}elseif ($ping_availability == AVAIL_SNMP) {
			if ($hosts[$host_id]["snmp_community"] == "") {
				$ping_time = 0.000;
			}else {
				$ping_time = $ping->snmp_status;
			}
		}elseif ($ping_availability == AVAIL_NONE) {
			$ping_time = 0.000;
		}else {
			$ping_time = $ping->ping_status;
		}

		/* update times as required */
		$hosts[$host_id]["cur_time"] = $ping_time;

		/* maximum time */
		if ($ping_time > $hosts[$host_id]["max_time"])
			$hosts[$host_id]["max_time"] = $ping_time;

		/* minimum time */
		if ($ping_time < $hosts[$host_id]["min_time"])
			$hosts[$host_id]["min_time"] = $ping_time;

		/* average time */
		$hosts[$host_id]["avg_time"] = (($hosts[$host_id]["total_polls"]-1-$hosts[$host_id]["failed_polls"])
			* $hosts[$host_id]["avg_time"] + $ping_time) / ($hosts[$host_id]["total_polls"]-$hosts[$host_id]["failed_polls"]);

		/* the host was down, now it's recovering */
		if (($hosts[$host_id]["status"] == HOST_DOWN) || ($hosts[$host_id]["status"] == HOST_RECOVERING )) {
			/* just up, change to recovering */
			if ($hosts[$host_id]["status"] == HOST_DOWN) {
				$hosts[$host_id]["status"] = HOST_RECOVERING;
				$hosts[$host_id]["status_event_count"] = 1;
			} else {
				$hosts[$host_id]["status_event_count"]++;
			}

			/* if it's time to issue a recovery message, indicate so */
			if ($hosts[$host_id]["status_event_count"] >= $ping_recovery_count) {
				/* host is up, flag it that way */
				$hosts[$host_id]["status"] = HOST_UP;

				$issue_log_message = true;

				/* update the recovery date only if the recovery count is 1 */
				if ($ping_recovery_count == 1) {
					$hosts[$host_id]["status_rec_date"] = date("Y-m-d h:i:s");
				}

				/* reset the event counter */
				$hosts[$host_id]["status_event_count"] = 0;
			/* host is recovering, but not ready to issue log message */
			} else {
				/* host recovering for the first time, set event date */
				if ($hosts[$host_id]["status_event_count"] == 1) {
					$hosts[$host_id]["status_rec_date"] = date("Y-m-d h:i:s");
				}
			}
		} else {
		/* host was unknown and now is up */
			$hosts[$host_id]["status"] = HOST_UP;
			$hosts[$host_id]["status_event_count"] = 0;
		}
	}
	/* if the user wants a flood of information then flood them */
	if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_HIGH) {
		if (($hosts[$host_id]["status"] == HOST_UP) || ($hosts[$host_id]["status"] == HOST_RECOVERING)) {
			/* log ping result if we are to use a ping for reachability testing */
			if ($ping_availability == AVAIL_SNMP_AND_PING) {
				api_log_log(_("PING: ") . $ping->ping_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
				api_log_log(_("SNMP: ") . $ping->snmp_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			} elseif ($ping_availability == AVAIL_SNMP) {
				if ($hosts[$host_id]["snmp_community"] == "") {
					api_log_log(_("SNMP: Device does not require SNMP"), SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
				}else{
					api_log_log(_("SNMP: ") . $ping->snmp_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
				}
			} elseif ($ping_availability == AVAIL_NONE) {
				api_log_log(_("AVAIL: Availability checking disabled for host"), SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			} else {
				api_log_log(_("PING: ") . $ping->ping_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			}
		} else {
			if ($ping_availability == AVAIL_SNMP_AND_PING) {
				api_log_log(_("PING: ") . $ping->ping_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
				api_log_log(_("SNMP: ") . $ping->snmp_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			} elseif ($ping_availability == AVAIL_SNMP) {
				api_log_log(_("SNMP: ") . $ping->snmp_response, SEV_INFO, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			} elseif ($ping_availability == AVAIL_NONE) {
				api_log_log(_("AVAIL: Availability cheking disabled for host"), SEV_INFO,  FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			} else {
				api_log_log(_("PING: ") . $ping->ping_response, SEV_INFO,  FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
			}
		}
	}

	/* if there is supposed to be an event generated, do it */
	if ($issue_log_message) {
		if ($hosts[$host_id]["status"] == HOST_DOWN) {
			api_log_log(_("HOST EVENT: Host is DOWN Message: ") . $hosts[$host_id]["status_last_error"], SEV_ERROR, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
		} else {
			api_log_log(_("HOST EVENT: Host Returned from DOWN State"), SEV_NOTICE, FACIL_POLLER, "", $poller_id, $host_id, $print_data_to_stdout);
		}
	}

	db_execute("update host set
		status = '" . $hosts[$host_id]["status"] . "',
		status_event_count = '" . $hosts[$host_id]["status_event_count"] . "',
		status_fail_date = '" . $hosts[$host_id]["status_fail_date"] . "',
		status_rec_date = '" . $hosts[$host_id]["status_rec_date"] . "',
		status_last_error = '" . $hosts[$host_id]["status_last_error"] . "',
		min_time = '" . $hosts[$host_id]["min_time"] . "',
		max_time = '" . $hosts[$host_id]["max_time"] . "',
		cur_time = '" . $hosts[$host_id]["cur_time"] . "',
		avg_time = '" . $hosts[$host_id]["avg_time"] . "',
		total_polls = '" . $hosts[$host_id]["total_polls"] . "',
		failed_polls = '" . $hosts[$host_id]["failed_polls"] . "',
		availability = '" . $hosts[$host_id]["availability"] . "'
		where hostname = '" . $hosts[$host_id]["hostname"] . "'");
}

/* validate_result - determine's if the result value is valid or not.  If not valid returns a "U"
   @arg $result - (string) the result from the poll
   @returns - (int) either to result is valid or not */
function validate_result($result) {
	$delim_cnt = 0;
	$space_cnt = 0;

	$valid_result = false;
	$checked = false;

	/* check the easy cases first */
	/* it has no delimiters, and no space, therefore, must be numeric */
	if ((substr_count($result, ":") == 0) && (substr_count($result, "!") == 0) && (substr_count($result, " ") == 0)) {
		$checked = true;
		if (is_numeric($result)) {
			$valid_result = true;
		} else if (is_float($result)) {
			$valid_result = true;
		} else {
			$valid_result = false;
		}
	}
	/* it has delimiters and has no space */
	if (!$checked) {
		if (((substr_count($result, ":")) || (substr_count($result, "!")))) {
			if (substr_count($result, " ") == 0) {
				$valid_result = true;
				$checked = true;
			}

			if (substr_count($result, " ") != 0) {
				$checked = true;
				if (substr_count($result, ":")) {
					$delim_cnt = substr_count($result, ":");
				} else if (strstr($result, "!")) {
					$delim_cnt = substr_count($result, "!");
				}

				$space_cnt = substr_count($result, " ");

				if ($space_cnt+1 == $delim_cnt) {
					$valid_result = true;
				} else {
					$valid_result = false;
				}
			}
		}
	}

	/* default handling */
	if (!$checked) {
		if (is_numeric($result)) {
			$valid_result = true;
		} else if (is_float($result)) {
			$valid_result = true;
		} else {
			$valid_result = false;
		}
	}

	return($valid_result);
}

?>
