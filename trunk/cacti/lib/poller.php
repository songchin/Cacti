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
 | This program is distributed in the hope that it will be useful,
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

/* exec_poll - executes a command and returns its output
   @arg $command - the command to execute
   @returns - the output of $command after execution */
function exec_poll($command) {
	return `$command`;
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

			/* send command to the php server */
			fwrite($pipes[0], $command . "\r\n");

			/* get result from server */
			$output = fgets($pipes[1], 1024);

			if (substr_count($output, "ERROR") > 0) {
				$output = "U";
			}
		}
	/* execute the old fashion way */
	}else{
		$command = read_config_option("path_php_binary") . " " . $command;
		$output = `$command`;
	}

	return $output;
}

/* exec_background - executes a program in the background so that php can continue
     to execute code in the foreground
   @arg $filename - the full pathname to the script to execute
   @arg $args - any additional arguments that must be passed onto the executable */
function exec_background($filename, $args = "") {
	global $config;

	if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
		cacti_log("DEBUG: About to Spawn a Remote Process [CMD: $filename, ARGS: $args]", true, "POLLER");
	}

	if (file_exists($filename)) {
		if ($config["cacti_server_os"] == "win32") {
			pclose(popen("start \"Cactiplus\" /I /B \"" . $filename . "\" " . $args, "r"));
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
	global $config;

	include_once($config["library_path"] . "/data_query.php");
	include_once($config["library_path"] . "/snmp.php");

	/* will be used to keep track of sql statements to execute later on */
	$recache_stack = array();

	$host = db_fetch_row("select hostname,snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_port,snmp_timeout from host where id=$host_id");
	$data_query = db_fetch_row("select reindex_method,sort_field from host_snmp_query where host_id=$host_id and snmp_query_id=$data_query_id");
	$data_query_type = db_fetch_cell("select data_input.type_id from data_input,snmp_query where data_input.id=snmp_query.data_input_id and snmp_query.id=$data_query_id");
	$data_query_xml = get_data_query_array($data_query_id);

	switch ($data_query["reindex_method"]) {
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

				array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'<','$assert_value','.1.3.6.1.2.1.1.3.0')");
			}

			break;
		case DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE:
			/* this method requires that some command/oid can be used to determine the
			 * current number of indexes in the data query */
			$assert_value = sizeof(db_fetch_assoc("select snmp_index from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id group by snmp_index"));

			if ($data_query_type == DATA_INPUT_TYPE_SNMP_QUERY) {
				if (isset($data_query_xml["oid_num_indexes"])) {
					array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'=','$assert_value','" . $data_query_xml["oid_num_indexes"] . "')");
				}
			}else if ($data_query_type == DATA_INPUT_TYPE_SCRIPT_QUERY) {
				if (isset($data_query_xml["arg_num_indexes"])) {
					array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,1,'=','$assert_value','" . get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . $data_query_xml["arg_num_indexes"], $data_query_xml["script_path"], $host_id) . "')");
				}
			}

			break;
		case DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION:
			$primary_indexes = db_fetch_assoc("select snmp_index,oid,field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='" . $data_query["sort_field"] . "'");

			if (sizeof($primary_indexes) > 0) {
				foreach ($primary_indexes as $index) {
					$assert_value = $index["field_value"];

					if ($data_query_type == DATA_INPUT_TYPE_SNMP_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'=','$assert_value','" . $data_query_xml["fields"]{$data_query["sort_field"]}["oid"] . "." . $index["snmp_index"] . "')");
					}else if ($data_query_type == DATA_INPUT_TYPE_SCRIPT_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,1,'=','$assert_value','" . get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . $data_query_xml["arg_get"] . " " . $data_query_xml["fields"]{$data_query["sort_field"]}["query_name"] . " " . $index["snmp_index"], $data_query_xml["script_path"], $host_id) . "')");
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
function process_poller_output($rrdtool_pipe) {
	global $config;

	include_once($config["library_path"] . "/rrd.php");

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

			$rrd_update_array{$item["rrd_path"]}["local_data_id"] = $item["local_data_id"];

			/* single one value output */
			if ((is_numeric($value)) || ($value == "U")) {
				$rrd_update_array{$item["rrd_path"]}["times"][$unix_time]{$item["rrd_name"]} = $value;
			/* multiple value output */
			}else{
				$values = explode(" ", $value);

				$rrd_field_names = array_rekey(db_fetch_assoc("select
					data_template_rrd.data_source_name,
					data_input_fields.data_name
					from data_template_rrd,data_input_fields
					where data_template_rrd.data_input_field_id=data_input_fields.id
					and data_template_rrd.local_data_id=" . $item["local_data_id"]), "data_name", "data_source_name");

				for ($i=0; $i<count($values); $i++) {
					if (preg_match("/^([a-zA-Z0-9_-]+):([+-0123456789Ee.]+)$/", $values[$i], $matches)) {
						if (isset($rrd_field_names{$matches[1]})) {
							if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
								cacti_log("Parsed MULTI output field '" . $matches[0] . "' [map " . $matches[1] . "->" . $rrd_field_names{$matches[1]} . "]" , true, "POLLER");
							}

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
					db_execute("delete from poller_output where local_data_id='" . $item["local_data_id"] . "' and rrd_name='" . $item["rrd_name"] . "' and time='" . $item["time"] . "'");
				}else{
					unset($rrd_update_array{$item["rrd_path"]}["times"][$unix_time]);
				}
			}
		}

		rrdtool_function_update($rrd_update_array, $rrdtool_pipe);
	}
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
				$hosts[$host_id]["status_last_error"] = "Device does not require SNMP";
			}else {
				$hosts[$host_id]["status_last_error"] = $ping->snmp_response;
			}
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
				cacti_log("Poller[$poller_id] Host[$host_id] PING: " . $ping->ping_response, $print_data_to_stdout);
				cacti_log("Poller[$poller_id] Host[$host_id] SNMP: " . $ping->snmp_response, $print_data_to_stdout);
			} elseif ($ping_availability == AVAIL_SNMP) {
				if ($hosts[$host_id]["snmp_community"] == "") {
					cacti_log("Poller[$poller_id] Host[$host_id] SNMP: Device does not require SNMP", $print_data_to_stdout);
				}else{
					cacti_log("Poller[$poller_id] Host[$host_id] SNMP: " . $ping->snmp_response, $print_data_to_stdout);
				}
			} else {
				cacti_log("Poller[$poller_id] Host[$host_id] PING: " . $ping->ping_response, $print_data_to_stdout);
			}
		} else {
			if ($ping_availability == AVAIL_SNMP_AND_PING) {
				cacti_log("Poller[$poller_id] Host[$host_id] PING: " . $ping->ping_response, $print_data_to_stdout);
				cacti_log("Poller[$poller_id] Host[$host_id] SNMP: " . $ping->snmp_response, $print_data_to_stdout);
			} elseif ($ping_availability == AVAIL_SNMP) {
				cacti_log("Poller[$poller_id] Host[$host_id] SNMP: " . $ping->snmp_response, $print_data_to_stdout);
			} else {
				cacti_log("Poller[$poller_id] Host[$host_id] PING: " . $ping->ping_response, $print_data_to_stdout);
			}
		}
	}

	/* if there is supposed to be an event generated, do it */
	if ($issue_log_message) {
		if ($hosts[$host_id]["status"] == HOST_DOWN) {
			cacti_log("Poller[$poller_id] Host[$host_id] ERROR: HOST EVENT: Host is DOWN Message: " . $hosts[$host_id]["status_last_error"], $print_data_to_stdout);
		} else {
			cacti_log("Poller[$poller_id] Host[$host_id] NOTICE: HOST EVENT: Host Returned from DOWN State: ", $print_data_to_stdout);
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