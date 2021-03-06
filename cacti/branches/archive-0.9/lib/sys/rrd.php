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

define("RRD_NL", " \\\n");

function rrd_init($rrd_count = 1) {
	/* set the rrdtool default font */
	if (read_config_option("path_rrdtool_default_font")) {
		putenv("RRD_DEFAULT_FONT=" . read_config_option("path_rrdtool_default_font"));
	}

	for ($i = 0; $i < $rrd_count; $i++) {
		$rrd_struc[$i]["fd"] = popen(read_config_option("path_rrdtool") . " -", "w");
	}

	return $rrd_struc;
}

function rrd_close($rrd_struc, $rrd_count = 1) {
	/* close the rrdtool file descriptor */
	for ($i = 0; $i < $rrd_count; $i++) {
		pclose($rrd_struc[$i]["fd"]);
	}
}

function rrd_get_fd(&$rrd_struc, $fd_type) {
	if (sizeof($rrd_struc) == 0) {
		return 0;
	}else{
		return $rrd_struc["fd"];
	}
}

function rrdtool_execute($command_line, $log_to_stdout, $output_flag, $rrd_struc = array(), $syslog_facility = FACIL_POLLER) {
	require_once(CACTI_BASE_PATH . "/lib/sys/exec.php");

	if (!is_numeric($output_flag)) {
		$output_flag = RRDTOOL_OUTPUT_STDOUT;
	}

	/* WIN32: before sending this command off to rrdtool, get rid
	of all of the '\' characters. Unix does not care; win32 does.
	Also make sure to replace all of the fancy \'s at the end of the line,
	but make sure not to get rid of the "\n"'s that are supposed to be
	in there (text format) */
	$command_line = str_replace("\\\n", " ", $command_line);

	/* output information to the log file if appropriate */
	if (($syslog_facility == FACIL_POLLER) || ($syslog_facility == FACIL_CMDPHP)) {
		log_save("RRD: " . addslashes(read_config_option("path_rrdtool")) . " $command_line", SEV_DEBUG, $syslog_facility, "", 0, 0, $log_to_stdout);
	} else {
		log_save("RRD: " . addslashes(read_config_option("path_rrdtool")) . " $command_line", SEV_DEBUG, $syslog_facility);
	}

	/* if we want to see the error output from rrdtool; make sure to specify this */
	if (($output_flag == RRDTOOL_OUTPUT_STDERR) && (sizeof($rrd_struc) == 0)) {
		$command_line .= " 2>&1";
	}

	/* use popen to eliminate the zombie issue */
	if (CACTI_SERVER_OS == "unix") {
		/* an empty $rrd_struc array means no fp is available */
		if (sizeof($rrd_struc) == 0) {
			$fp = popen(read_config_option("path_rrdtool") . escape_command(" $command_line"), "r");
		}else{
			fwrite(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ), escape_command(" $command_line") . "\r\n");
			fflush(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ));
		}
	}elseif (CACTI_SERVER_OS == "win32") {
		/* an empty $rrd_struc array means no fp is available */
		if (sizeof($rrd_struc) == 0) {
			$fp = popen(read_config_option("path_rrdtool") . escape_command(" $command_line"), "rb");
		}else{
			fwrite(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ), escape_command(" $command_line") . "\r\n");
			fflush(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ));
		}
	}

	switch ($output_flag) {
		case RRDTOOL_OUTPUT_NULL:
			return; break;
		case RRDTOOL_OUTPUT_STDOUT:
			if (isset($fp)) {
				$line = "";
				while (!feof($fp)) {
					$line .= fgets($fp, 4096);
				}

				return $line;
			}

			break;
		case RRDTOOL_OUTPUT_STDERR:
			if (isset($fp)) {
				$output = fgets($fp, 1000000);

				if (substr($output, 1, 3) == "PNG") {
					return "OK";
				}

				if (substr($output, 0, 5) == "GIF87") {
					return "OK";
				}

				print $output;
			}

			break;
		case RRDTOOL_OUTPUT_GRAPH_DATA:
			if (isset($fp)) {
				return fpassthru($fp);
			}

			break;
	}
}

function rrdtool_function_create($data_source_id, $show_source, $rrd_struc, $syslog_facility = FACIL_POLLER) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	$data_source_path = api_data_source_path_get($data_source_id, true);

	/* make sure the RRD file does not already exist */
	if (($show_source != true) && (file_exists($data_source_path) == true)) {
		return;
	}

	/* get the current data source */
	$data_source = api_data_source_get($data_source_id);

	/* create the "--step" line */
	$create_ds = RRD_NL . "--step ". $data_source["polling_interval"] . " " . RRD_NL;

	/* get a list of valid data source types (COUNTER, GAUGE, etc) */
	$data_source_types = api_data_source_type_list();

	/* query the data sources to be used in this .rrd file */
	$data_source_items = api_data_source_item_list($data_source_id);

	if (is_array($data_source_items) > 0) {
		foreach ($data_source_items as $data_source_item) {
			$create_ds .= "DS:" . $data_source_item["data_source_name"] . ":" . $data_source_types{$data_source_item["data_source_type"]} . ":" . $data_source_item["rrd_heartbeat"] . ":" . $data_source_item["rrd_minimum"] . ":" . (empty($data_source_item["rrd_maximum"]) ? "U" : $data_source_item["rrd_maximum"]) . RRD_NL;
		}
	}

	/* get a list of valid consolidation functions (AVERAGE, MAX, etc) */
	$rra_cf_types = api_data_preset_rra_cf_type_list();

	/* get a list of RRA's that have been associated with this data source */
	$rra_items = api_data_source_rra_item_list($data_source_id);

	/* if we find that this data source has no RRA associated; get out */
	if ((!is_array($rra_items)) || (sizeof($rra_items) == 0)) {
		log_save(_("There are no RRA's assigned to data_source_id: ") . $data_source_id . ".", SEV_ERROR, FACIL_POLLER);
		return false;
	}

	$create_rra = "";
	/* loop through each available RRA for this data source */
	foreach ($rra_items as $rra_item) {
		/* calculate the correct number of steps based on the RRA steps and the polling interval */
		$rra_steps = ceil($rra_item["steps"] / $data_source["polling_interval"]);

		/* calculate the correct number of rows based on the polling interval and the RRA
		 * retention interval */
		$rra_rows = ceil($rra_item["rows"] / ($data_source["polling_interval"] * $rra_steps));

		if (($rra_item["consolidation_function"] == RRA_CF_TYPE_AVERAGE) || ($rra_item["consolidation_function"] == RRA_CF_TYPE_MIN) || ($rra_item["consolidation_function"] == RRA_CF_TYPE_MAX) || ($rra_item["consolidation_function"] == RRA_CF_TYPE_LAST)) {
			$create_rra .= "RRA:" . $rra_cf_types{$rra_item["consolidation_function"]} . ":" . $rra_item["x_files_factor"] . ":" . $rra_steps . ":" . $rra_rows . RRD_NL;
		}else if ($rra_item["consolidation_function"] == RRA_CF_TYPE_HWPREDICT) {
			$create_rra .= "RRA:" . $rra_cf_types{$rra_item["consolidation_function"]} . ":" . $rra_item["rows"] . ":" . $rra_item["hw_alpha"] . ":" . $rra_item["hw_beta"] . ":" . $rra_item["hw_seasonal_period"] . (empty($rra_item["hw_rra_num"]) ? "" : ":" . $rra_item["hw_rra_num"]) . RRD_NL;
		}else if (($rra_item["consolidation_function"] == RRA_CF_TYPE_SEASONAL) || ($rra_item["consolidation_function"] == RRA_CF_TYPE_DEVSEASONAL)) {
			$create_rra .= "RRA:" . $rra_cf_types{$rra_item["consolidation_function"]} . ":" . $rra_item["hw_seasonal_period"] . ":" . $rra_item["hw_gamma"] . ":" . $rra_item["hw_rra_num"] . RRD_NL;
		}else if ($rra_item["consolidation_function"] == RRA_CF_TYPE_DEVPREDICT) {
			$create_rra .= "RRA:" . $rra_cf_types{$rra_item["consolidation_function"]} . ":" . $rra_item["rows"] . ":" . $rra_item["hw_rra_num"] . RRD_NL;
		}else if ($rra_item["consolidation_function"] == RRA_CF_TYPE_FAILURES) {
			$create_rra .= "RRA:" . $rra_cf_types{$rra_item["consolidation_function"]} . ":" . $rra_item["rows"] . ":" . $rra_item["hw_threshold"] . ":" . $rra_item["hw_window_length"] . ":" . $rra_item["hw_rra_num"] . RRD_NL;
		}
	}

	if ($show_source == true) {
		return read_config_option("path_rrdtool") . " create" . RRD_NL . "$data_source_path$create_ds$create_rra";
	}else{
		rrdtool_execute("create $data_source_path $create_ds$create_rra", true, RRDTOOL_OUTPUT_STDOUT, $rrd_struc, $syslog_facility);
	}
}

function rrdtool_function_update($update_cache_array, $rrd_struc, $syslog_facility = FACIL_POLLER) {
	/* lets count the number of rrd files processed */
	$rrds_processed = 0;

	/* set the first RRD pipe to process from */
	$rrd_process = 1;

	while (list($rrd_path, $rrd_fields) = each($update_cache_array)) {
		$rrd_num = bcmod($rrd_process, read_config_option("concurrent_rrd_processes"));

		$create_rrd_file = false;

		/* create the rrd if one does not already exist */
		if (!file_exists($rrd_path)) {
			rrdtool_function_create($rrd_fields["data_source_id"], false, $rrd_struc[$rrd_num]);

			$create_rrd_file = true;
		}

		if ((is_array($rrd_fields["times"])) && (sizeof($rrd_fields["times"]) > 0)) {
			ksort($rrd_fields["times"]);

			while (list($update_time, $field_array) = each($rrd_fields["times"])) {
				if (empty($update_time)) {
					/* default the rrdupdate time to now */
					$current_rrd_update_time = "N";
				}else if ($create_rrd_file == true) {
					/* for some reason rrdtool will not let you update using times less than the
					rrd create time */
					$current_rrd_update_time = "N";
				}else{
					$current_rrd_update_time = $update_time;
				}

				$i = 0; $rrd_update_template = ""; $rrd_update_values = $current_rrd_update_time . ":";
				while (list($field_name, $value) = each($field_array)) {
					$rrd_update_template .= $field_name;

					/* if we have "invalid data", give rrdtool an Unknown (U) */
					if ((!isset($value)) || (!is_numeric($value))) {
						$value = "U";
					}

					$rrd_update_values .= $value;

					if (($i+1) < count($field_array)) {
						$rrd_update_template .= ":";
						$rrd_update_values .= ":";
					}

					$i++;
				}

				rrdtool_execute("update $rrd_path --template $rrd_update_template $rrd_update_values", true, RRDTOOL_OUTPUT_STDOUT, $rrd_struc[$rrd_num], $syslog_facility);
				$rrds_processed++;
			}
		}

		$rrd_process++;
	}

	return $rrds_processed;
}

function rrdtool_function_tune($rrd_tune_array) {
	require(CACTI_BASE_PATH . "/include/global_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	$data_source_name = get_data_source_item_name($rrd_tune_array["data_source_id"]);
	$data_source_type = $data_source_types{$rrd_tune_array["data-source-type"]};
	$data_source_path = api_data_source_path_get($rrd_tune_array["data_source_id"], true);

	if ($rrd_tune_array["heartbeat"] != "") {
		$rrd_tune .= " --heartbeat $data_source_name:" . $rrd_tune_array["heartbeat"];
	}

	if ($rrd_tune_array["minimum"] != "") {
		$rrd_tune .= " --minimum $data_source_name:" . $rrd_tune_array["minimum"];
	}

	if ($rrd_tune_array["maximum"] != "") {
		$rrd_tune .= " --maximum $data_source_name:" . $rrd_tune_array["maximum"];
	}

	if ($rrd_tune_array["data-source-type"] != "") {
		$rrd_tune .= " --data-source-type $data_source_name:" . $data_source_type;
	}

	if ($rrd_tune_array["data-source-rename"] != "") {
		$rrd_tune .= " --data-source-rename $data_source_name:" . $rrd_tune_array["data-source-rename"];
	}

	if ($rrd_tune != "") {
		if (file_exists($data_source_path) == true) {
			$fp = popen(read_config_option("path_rrdtool") . " tune $data_source_path $rrd_tune", "r");
			pclose($fp);

			log_save("RRD_TUNE: " . addslashes(read_config_option("path_rrdtool")) . " tune $data_source_path $rrd_tune", SEV_DEBUG, FACIL_POLLER);
		}
	}
}

$rrd_fetch_cache = array();

/* rrdtool_function_fetch - given a data source, return all of its data in an array
   @arg $local_data_id - the data source to fetch data for
   @arg $start_time - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $end_time - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $resolution - the accuracy of the data measured in seconds
   @arg $show_unknown - Show unknown 'NAN' values in the output as 'U'
   @returns - (array) an array containing all data in this data source broken down
	 by each data source item. the maximum of all data source items is included in
	 an item called 'ninety_fifth_percentile_maximum' */
function &rrdtool_function_fetch($local_data_id, $start_time, $end_time, $resolution = 0, $show_unknown = 0, $syslog_facility = FACIL_WEBUI) {
	global $rrd_fetch_cache;

	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (empty($local_data_id)) {
		unset($var);
		return $var;
	}

	$regexps = array();
	$fetch_array = array();

	/* the cache hash is used to identify unique items in the cache */
	$current_hash_cache = md5($local_data_id . $start_time . $end_time . $resolution . $show_unknown);

	/* return the cached entry if available */
	if (isset($rrd_fetch_cache[$current_hash_cache])) {
		return $rrd_fetch_cache[$current_hash_cache];
	}

	$data_source_path = api_data_source_path_get($local_data_id, true);

	/* build and run the rrdtool fetch command with all of our data */
	$cmd_line = "fetch $data_source_path AVERAGE -s $start_time -e $end_time";
	if ($resolution > 0) {
		$cmd_line .= " -r $resolution";
	}
	$output = rrdtool_execute($cmd_line, false, RRDTOOL_OUTPUT_STDOUT, array(), $syslog_facility);

	/* grab the first line of the output which contains a list of data sources
	in this .rrd file */
	$line_one = substr($output, 0, strpos($output, "\n"));

	/* loop through each data source in this .rrd file ... */
	if (preg_match_all("/\S+/", $line_one, $data_source_names)) {
		/* version 1.0.49 changed the output slightly */
		if (preg_match("/^timestamp/", $line_one)) {
			array_shift($data_source_names[0]);
		}

		$fetch_array["data_source_names"] = $data_source_names[0];

		/* build a unique regexp to match each data source individually when
		passed to preg_match_all() */
		for ($i=0;$i<count($fetch_array["data_source_names"]);$i++) {
			$regexps[$i] = '/[0-9]+:\s+';

			for ($j=0;$j<count($fetch_array["data_source_names"]);$j++) {
				/* it seems that at least some versions of the Windows RRDTool binary pads
				the exponent to 3 digits, rather than 2 on every Unix version that I have
				ever seen */
				if ($j == $i) {
					if ($show_unknown == 1) {
						$regexps[$i] .= '([\-]?[0-9]{1}\.[0-9]+)e([\+-][0-9]{2,3})|(nan)|(NaN)';
					} else {
						$regexps[$i] .= '([\-]?[0-9]{1}\.[0-9]+)e([\+-][0-9]{2,3})';
					}
				}else{
					$regexps[$i] .= '[\-]?[0-9]{1}\.[0-9]+e[\+-][0-9]{2,3}';
				}

				if ($j < count($fetch_array["data_source_names"])) {
					$regexps[$i] .= '\s+';
				}
			}

			$regexps[$i] .= '/';
		}
	}

	$max_array = array();

	/* loop through each regexp determined above (or each data source) */
	for ($i=0;$i<count($regexps);$i++) {
		$fetch_array["values"][$i] = array();

		/* match the regexp against the rrdtool fetch output to get a mantisa and
		exponent for each line */
		if (preg_match_all($regexps[$i], $output, $matches)) {
			for ($j=0; ($j < count($matches[1])); $j++) {
				$line = ($matches[1][$j] * (pow(10,(float)$matches[2][$j])));
				if ((($line == "NaN") || ($line == "nan")) && ($show_unknown == 1)) {
					array_push($fetch_array["values"][$i], "U");
					$max_array[$j][$i] = "U";
				} else {
					array_push($fetch_array["values"][$i], ($line * 1));
					$max_array[$j][$i] = $line;
				}
			}
		}
	}

	/* nth_percentile_maximun is removed if Unknown values are requested in the output.  This
	is because the max_array function will give unpredictable results when there is a mix
	of number and text data */
	if ((isset($fetch_array["data_source_names"])) && ($show_unknown  == 0)) {
		$next_index = count($fetch_array["data_source_names"]);

		$fetch_array["data_source_names"][$next_index] = "ninety_fifth_percentile_maximum";

		/* calculate the max for each row */
		for ($i=0; $i<count($max_array); $i++) {
			$fetch_array["values"][$next_index][$i] = max($max_array[$i]);
		}
	}

	return $fetch_array;
}

function rrdtool_function_graph($graph_id, $rra_id, $graph_data_array, $rrd_struc = array(),$syslog_facility = FACIL_WEBUI) {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/sys/auth.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/graph_variable.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_utility.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	/* set the rrdtool default font */
	if (read_config_option("path_rrdtool_default_font")) {
		putenv("RRD_DEFAULT_FONT=" . read_config_option("path_rrdtool_default_font"));
	}

	/* before we do anything; make sure the user has permission to view this graph,
	if not then get out */
	if ((read_config_option("auth_method") != "0") && (isset($_SESSION["sess_user_id"]))) {
		$access_denied = !(is_graph_allowed($graph_id));

		if ($access_denied == true) {
			return _("GRAPH ACCESS DENIED");
		}
	}

	/* find the step and how often this graph is updated with new data */
	$ds_step = db_fetch_cell("select
		data_source.rrd_step
		from data_source,data_source_item,graph_item
		where graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and graph_item.graph_id = $graph_id
		limit 0,1");
	$ds_step = empty($ds_step) ? 300 : $ds_step;

	/* if no rra was specified, we need to figure out which one RRDTool will choose using
	 * "best-fit" resolution fit algorithm */
	if (empty($rra_id)) {
		if ((empty($graph_data_array["graph_start"])) || (empty($graph_data_array["graph_end"]))) {
			$rra["rows"] = 600;
			$rra["steps"] = 1;
			$rra["timespan"] = 86400;
		}else{
			/* get a list of RRAs related to this graph */
			$rras = api_graph_associated_rras_list($graph_id);

			if (sizeof($rras) > 0) {
				foreach ($rras as $unchosen_rra) {
					/* the timespan specified in the RRA "timespan" field may not be accurate */
					$real_timespan = ($ds_step * $unchosen_rra["steps"] * $unchosen_rra["rows"]);

					/* make sure the current start/end times fit within each RRA's timespan */
					if ( (($graph_data_array["graph_end"] - $graph_data_array["graph_start"]) <= $real_timespan) && ((time() - $graph_data_array["graph_start"]) <= $real_timespan) ) {
						/* is this RRA better than the already chosen one? */
						if ((isset($rra)) && ($unchosen_rra["steps"] < $rra["steps"])) {
							$rra = $unchosen_rra;
						}else if (!isset($rra)) {
							$rra = $unchosen_rra;
						}
					}
				}
			}

			if (!isset($rra)) {
				$rra["rows"] = 600;
				$rra["steps"] = 1;
			}
		}
	}else{
		$rra = db_fetch_row("select timespan,rows,steps from rra where id=$rra_id");
	}

	$seconds_between_graph_updates = ($ds_step * $rra["steps"]);

	$graph = db_fetch_row("select
		graph.host_id,
		graph.title_cache,
		graph.vertical_label,
		graph.auto_scale,
		graph.auto_scale_opts,
		graph.auto_scale_log,
		graph.auto_scale_rigid,
		graph.auto_padding,
		graph.base_value,
		graph.upper_limit,
		graph.lower_limit,
		graph.x_grid,
		graph.y_grid,
		graph.y_grid_alt,
		graph.no_minor,
		graph.height,
		graph.width,
		graph.image_format,
		graph.unit_value,
		graph.unit_length,
		graph.unit_exponent_value,
		graph.export
		from graph
		where graph.id = $graph_id");

	/* lets make that sql query... */
	$graph_items = db_fetch_assoc("select
		graph_item.id,
		graph_item.cdef,
		graph_item.legend_format,
		graph_item.legend_value,
		graph_item.hard_return,
		graph_item.consolidation_function,
		graph_item.graph_item_type,
		graph_item.gprint_format,
		graph_item.color,
		graph_item.data_source_item_id,
		data_source_item.data_source_id,
		data_source_item.rrd_minimum,
		data_source_item.rrd_maximum,
		data_source_item.data_source_name
		from graph_item
		left join data_source_item on (graph_item.data_source_item_id=data_source_item.id)
		where graph_item.graph_id = $graph_id
		order by graph_item.sequence");

	/* +++++++++++++++++++++++ GRAPH OPTIONS +++++++++++++++++++++++ */

	/* define some variables */
	$scale = "";
	$rigid = "";
	$no_minor = "";
	$y_grid = "";
	$y_grid_alt = "";
	$x_grid = "";
	$unit_value = "";
	$unit_length = "";
	$unit_exponent_value = "";
	$graph_legend = "";
	$graph_defs = "";
	$txt_graph_items = "";
	$text_padding = "";
	$greatest_text_format = 0;
	$last_graph_type = "";

	if (empty($graph["auto_scale"])) {
		$scale =  "--upper-limit=" . $graph["upper_limit"] . RRD_NL;
		$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
	}else{
		if ($graph["auto_scale_opts"] == "1") {
			$scale = "--alt-autoscale" . RRD_NL;
		}elseif ($graph["auto_scale_opts"] == "2") {
			$scale = "--alt-autoscale-max" . RRD_NL;
			$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
		}

		if (!empty($graph["auto_scale_log"])) {
			$scale .= "--logarithmic" . RRD_NL;
		}
	}

	if (!empty($graph["auto_scale_rigid"])) {
		$rigid = "--rigid" . RRD_NL;
	}

	if (!empty($graph["unit_value"])) {
		$unit_value = "--unit=" . $graph["unit_value"] . RRD_NL;
	}

	if (!empty($graph["no_minor"])) {
		$no_minor = "--no_minor" . RRD_NL;
	}

	if (!empty($graph["y_grid_alt"])) {
		$y_grid_alt = "--alt-y-grid" . RRD_NL;
	}

	if (!empty($graph["y_grid"])) {
		$y_grid = $graph["y_grid"] . RRD_NL;
	}

	if (!empty($graph["x_grid"])) {
		$x_grid = $graph["x_grid"] . RRD_NL;
	}

	if (is_numeric($graph["unit_length"])) {
		$unit_length = "--units-length=" . $graph["unit_length"] . RRD_NL;
	}

	if (!empty($graph["unit_value"])) {
		$unit_value = "--unit=" . $graph["unit_value"] . RRD_NL;
	}

	if (is_numeric($graph["unit_exponent_value"])) {
		$unit_exponent_value = "--units-exponent=" . $graph["unit_exponent_value"] . RRD_NL;
	}

	/*
	 * optionally you can specify and array that overrides some of the db's values, lets set
	 * that all up here
	 */

	/* override: graph start time */
	if ((!isset($graph_data_array["graph_start"])) || ($graph_data_array["graph_start"] == "0")) {
		$graph_start = -($rra["timespan"]);
	}else{
		$graph_start = $graph_data_array["graph_start"];
	}

	/* override: graph end time */
	if ((!isset($graph_data_array["graph_end"])) || ($graph_data_array["graph_end"] == "0")) {
		$graph_end = -($seconds_between_graph_updates);
	}else{
		$graph_end = $graph_data_array["graph_end"];
	}

	/* override: graph height (in pixels) */
	if (isset($graph_data_array["graph_height"])) {
		$graph_height = $graph_data_array["graph_height"];
	}else{
		$graph_height = $graph["height"];
	}

	/* override: graph width (in pixels) */
	if (isset($graph_data_array["graph_width"])) {
		$graph_width = $graph_data_array["graph_width"];
	}else{
		$graph_width = $graph["width"];
	}

	/* override: skip drawing the legend? */
	if (isset($graph_data_array["graph_nolegend"])) {
		$graph_legend = "--no-legend" . RRD_NL;
	}else{
		$graph_legend = "";
	}

	/* export options */
	if (isset($graph_data_array["export"])) {
		$graph_opts = read_config_option("path_html_export") . "/" . $graph_data_array["export_filename"] . RRD_NL;
	}else{
		if (empty($graph_data_array["output_filename"])) {
				$graph_opts = "-" . RRD_NL;
		}else{
			$graph_opts = $graph_data_array["output_filename"] . RRD_NL;
		}
	}

	/* setup date format */
	$date_fmt = read_graph_config_option("default_date_format");
	$datechar = read_graph_config_option("default_datechar");

	if ($datechar == GDC_HYPHEN) {
		$datechar = "-";
	}else {
		$datechar = "/";
	}

	switch ($date_fmt) {
		case GD_MO_D_Y:
			$graph_date = "m" . $datechar . "d" . $datechar . "Y H:i:s";
			break;
		case GD_MN_D_Y:
			$graph_date = "M" . $datechar . "d" . $datechar . "Y H:i:s";
			break;
		case GD_D_MO_Y:
			$graph_date = "d" . $datechar . "m" . $datechar . "Y H:i:s";
			break;
		case GD_D_MN_Y:
			$graph_date = "d" . $datechar . "M" . $datechar . "Y H:i:s";
			break;
		case GD_Y_MO_D:
			$graph_date = "Y" . $datechar . "m" . $datechar . "d H:i:s";
			break;
		case GD_Y_MN_D:
			$graph_date = "Y" . $datechar . "M" . $datechar . "d H:i:s";
			break;
	}

	/* display the timespan for zoomed graphs */
	if ((isset($graph_data_array["graph_start"])) && (isset($graph_data_array["graph_end"]))) {
		if (($graph_data_array["graph_start"] < 0) && ($graph_data_array["graph_end"] < 0)) {
			if (read_config_option("rrdtool_version") == "rrd-1.2.x") {
				$graph_legend .= "COMMENT:\"" . _("From ") . str_replace(":", "\:", date($graph_date, time()+$graph_data_array["graph_start"])) . _(" To ") . str_replace(":", "\:", date($graph_date, time()+$graph_data_array["graph_end"])) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}else {
				$graph_legend .= "COMMENT:\"" . _("From ") . date($graph_date, time()+$graph_data_array["graph_start"]) . _(" To ") . date($graph_date, time()+$graph_data_array["graph_end"]) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}
		}else if (($graph_data_array["graph_start"] >= 0) && ($graph_data_array["graph_end"] >= 0)) {
			if (read_config_option("rrdtool_version") == "rrd-1.2.x") {
				$graph_legend .= "COMMENT:\"" . _("From ") . str_replace(":", "\:", date($graph_date, $graph_data_array["graph_start"])) . _(" To ") . str_replace(":", "\:", date($graph_date, $graph_data_array["graph_end"])) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}else {
				$graph_legend .= "COMMENT:\"" . _("From ") . date($graph_date, $graph_data_array["graph_start"]) . _(" To ") . date($graph_date, $graph_data_array["graph_end"]) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}
		}
	}

	/* basic graph options */
	$graph_opts .=
		"--imgformat=" . $graph_image_types{$graph["image_format"]} . RRD_NL .
		"--start=$graph_start" . RRD_NL .
		"--end=$graph_end" . RRD_NL .
		"-c CANVAS#" . $colors["rrd_canvas"] . " -c FONT#" . $colors["rrd_font"] . " -c BACK#" . $colors["rrd_back"] . " " .
		"--title=\"" . str_replace("\"", "\\\"", $graph["title_cache"]) . "\"" . RRD_NL .
		"$rigid" .
		"--base=" . $graph["base_value"] . RRD_NL .
		"--height=$graph_height" . RRD_NL .
		"--width=$graph_width" . RRD_NL .
		"$scale" .
		"$x_grid" .
		"$y_grid" .
		"$y_grid_alt" .
		"$no_minor" .
		"$unit_value" .
		"$unit_length" .
		"$unit_exponent_value" .
		"$graph_legend" .
		"--vertical-label=\"" . $graph["vertical_label"] . "\"" . RRD_NL;

	/* rrdtool 1.2.x does not provide smooth lines, let's force it */
	if (read_config_option("rrdtool_version") == "rrd-1.2.x") {
		$graph_opts .= "--slope-mode" . RRD_NL;
	}

	/* rrdtool 1.2 font options */
	if (read_config_option("rrdtool_version") == "rrd-1.2.x") {
		/* title fonts */
		if (file_exists(read_graph_config_option("title_font"))) {
			$graph_opts .= "--font TITLE:" . read_graph_config_option("title_size") . ":" . read_graph_config_option("title_font") . RRD_NL;
        }elseif (file_exists(read_config_option("title_font"))) {
			$graph_opts .= "--font TITLE:" . read_config_option("title_size") . ":" . read_config_option("title_font") . RRD_NL;
		}

		/* axis fonts */
		if (file_exists(read_graph_config_option("axis_font"))) {
			$graph_opts .= "--font AXIS:" . read_graph_config_option("axis_size") . ":" . read_graph_config_option("axis_font") . RRD_NL;
        }elseif (file_exists(read_config_option("axis_font"))) {
			$graph_opts .= "--font AXIS:" . read_config_option("axis_size") . ":" . read_config_option("axis_font") . RRD_NL;
		}

		/* legend fonts */
		if (file_exists(read_graph_config_option("legend_font"))) {
			$graph_opts .= "--font LEGEND:" . read_graph_config_option("legend_size") . ":" . read_graph_config_option("legend_font") . RRD_NL;
        }elseif (file_exists(read_config_option("legend_font"))) {
			$graph_opts .= "--font LEGEND:" . read_config_option("legend_size") . ":" . read_config_option("legend_font") . RRD_NL;
        }

		/* unit fonts */
		if (file_exists(read_graph_config_option("unit_font"))) {
			$graph_opts .= "--font UNIT:" . read_graph_config_option("unit_size") . ":" . read_graph_config_option("unit_font") . RRD_NL;
        }elseif (file_exists(read_config_option("unit_font"))) {
			$graph_opts .= "--font UNIT:" . read_config_option("unit_size") . ":" . read_config_option("unit_font") . RRD_NL;
        }
	}

	$i = 0;
	if (sizeof($graph_items > 0)) {
		foreach ($graph_items as $graph_item) {
			/* mimic the old behavior: LINE[123], AREA, and STACK items use the CF specified in the graph item */
			if (is_graph_item_type_primary($graph_item["graph_item_type"])) {
				$graph_cf = $graph_item["consolidation_function"];
			/* all other types are based on the AVERAGE CF */
			}else{
				$graph_cf = 1;
			}

			if ((!empty($graph_item["data_source_id"])) && (!isset($cf_ds_cache{$graph_item["data_source_item_id"]}[$graph_cf]))) {
				/* use a user-specified ds path if one is entered */
				$data_source_path = api_data_source_path_get($graph_item["data_source_id"], true);

				/* FOR WIN32: Escape all colon for drive letters (ex. D\:/path/to/rra) */
				$data_source_path = str_replace(":", "\:", $data_source_path);

				if (!empty($data_source_path)) {
					/* NOTE: (Update) Data source DEF names are created using the graph_item_id; then passed
					to a function that matches the digits with letters. rrdtool likes letters instead
					of numbers in DEF names; especially with CDEF's. cdef's are created
					the same way, except a 'cdef' is put on the beginning of the hash */
					$graph_defs .= "DEF:" . generate_graph_def_name(strval($i)) . "=\"$data_source_path\":" . $graph_item["data_source_name"] . ":" . $consolidation_functions[$graph_cf] . RRD_NL;

					$cf_ds_cache{$graph_item["data_source_item_id"]}[$graph_cf] = "$i";

					$i++;
				}
			}

			/* +++++++++++++++++++++++ LEGEND: TEXT SUBSITUTION (<>'s) +++++++++++++++++++++++ */

			/* note the current item_id for easy access */
			$graph_item_id = $graph_item["id"];

			/* the following fields will be searched for graph variables */
			$variable_fields = array(
				"legend_format" => array(
					"process_no_legend" => false
					),
				"legend_value" => array(
					"process_no_legend" => true
					)
				);

			/* loop through each field that we want to substitute values for:
			currently: text format and value */
			while (list($field_name, $field_array) = each($variable_fields)) {
				/* certain fields do not require values when the legend is not to be shown */
				if (($field_array["process_no_legend"] == false) && (isset($graph_data_array["graph_nolegend"]))) {
					continue;
				}

				$graph_variables[$field_name][$graph_item_id] = $graph_item[$field_name];

				/* date/time substitution */
				if (strstr($graph_variables[$field_name][$graph_item_id], "|date_time|")) {
					$graph_variables[$field_name][$graph_item_id] = str_replace("|date_time|", date('D d M H:i:s T Y', strtotime(db_fetch_cell("select value from settings where name='date'"))), $graph_variables[$field_name][$graph_item_id]);
				}

				/* data query variables */
				if (preg_match("/\|query_[a-zA-Z0-9_]+\|/", $graph_variables[$field_name][$graph_item_id])) {
					/* default to the graph data query information from the graph */
					if (empty($graph_item["local_data_id"])) {
						$graph_variables[$field_name][$graph_item_id] = substitute_data_query_variables($graph_variables[$field_name][$graph_item_id], $graph["host_id"], $graph["snmp_query_id"], $graph["snmp_index"]);
					/* use the data query information from the data source if possible */
					}else{
						$data_local = db_fetch_row("select snmp_index,snmp_query_id,host_id from data_local where id='" . $graph_item["local_data_id"] . "'");
						$graph_variables[$field_name][$graph_item_id] = substitute_data_query_variables($graph_variables[$field_name][$graph_item_id], $data_local["host_id"], $data_local["snmp_query_id"], $data_local["snmp_index"]);
					}
				}

				/* 95th percentile */
				if (preg_match_all("/\|95:(bits|bytes):(\d):(current|total|max|total_peak|all_max_current|all_max_peak|aggregate_max|aggregate_sum|aggregate)(:(\d))?\|/", $graph_variables[$field_name][$graph_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						$graph_variables[$field_name][$graph_item_id] = str_replace($match[0], variable_ninety_fifth_percentile($match[1], $match[2], $match[3], (isset($match[5]) ? $match[5] : 2), $graph_item, $graph_items, $graph_start, $graph_end), $graph_variables[$field_name][$graph_item_id]);
					}
				}

				/* bandwidth summation */
				if (preg_match_all("/\|sum:(\d|auto):(current|total|atomic):(\d):(\d+|auto)\|/", $graph_variables[$field_name][$graph_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						$graph_variables[$field_name][$graph_item_id] = str_replace($match[0], variable_bandwidth_summation($match[1], $match[2], $match[3], $match[4], $graph_item, $graph_items, $graph_start, $graph_end, $rra["steps"], $ds_step), $graph_variables[$field_name][$graph_item_id]);
					}
				}
			}

			/* if we are not displaying a legend there is no point in us even processing the auto padding,
			text format stuff. */
			if (!isset($graph_data_array["graph_nolegend"])) {
				/* set hard return variable if selected (\n) */
				if (empty($graph_item["hard_return"])) {
					$hardreturn[$graph_item_id] = "";
				}else{
					$hardreturn[$graph_item_id] = "\\n";
				}

				/* +++++++++++++++++++++++ LEGEND: AUTO PADDING (<>'s) +++++++++++++++++++++++ */

				/* PADDING: remember this is not perfect! its main use is for the basic graph setup of:
				AREA - GPRINT-CURRENT - GPRINT-AVERAGE - GPRINT-MAXIMUM \n
				of course it can be used in other situations, however may not work as intended.
				If you have any additions to this small peice of code, feel free to send them to me. */
				if (!empty($graph["auto_padding"])) {
					/* only applies to AREA and STACK */
					if (is_graph_item_type_primary($graph_item["graph_item_type"])) {
						$text_format_lengths{$graph_item["data_source_item_id"]} = strlen($graph_variables["legend_format"][$graph_item_id]);

						if ((strlen($graph_variables["legend_format"][$graph_item_id]) > $greatest_text_format) && ($graph_item_types{$graph_item["graph_item_type"]} != GRAPH_ITEM_TYPE_COMMENT)) {
							$greatest_text_format = strlen($graph_variables["legend_format"][$graph_item_id]);
						}
					}
				}
			}
		}
	}

	/* +++++++++++++++++++++++ GRAPH ITEMS: CDEF's +++++++++++++++++++++++ */

	$i = 0;
	reset($graph_items);

	if (sizeof($graph_items) > 0) {
	foreach ($graph_items as $graph_item) {
		/* first we need to check if there is a DEF for the current data source/cf combination. if so,
		we will use that */
		if (isset($cf_ds_cache{$graph_item["data_source_item_id"]}{$graph_item["consolidation_function"]})) {
			$cf_id = $graph_item["consolidation_function"];
		}else{
		/* if there is not a DEF defined for the current data source/cf combination, then we will have to
		improvise. choose the first available cf in the following order: AVERAGE, MAX, MIN, LAST */
			if (isset($cf_ds_cache{$graph_item["data_source_item_id"]}[1])) {
				$cf_id = 1; /* CF: AVERAGE */
			}elseif (isset($cf_ds_cache{$graph_item["data_source_item_id"]}[3])) {
				$cf_id = 3; /* CF: MAX */
			}elseif (isset($cf_ds_cache{$graph_item["data_source_item_id"]}[2])) {
				$cf_id = 2; /* CF: MIN */
			}elseif (isset($cf_ds_cache{$graph_item["data_source_item_id"]}[4])) {
				$cf_id = 4; /* CF: LAST */
			}else{
				$cf_id = 1; /* CF: AVERAGE */
			}
		}

		/* make cdef string here; a note about CDEF's in cacti. A CDEF is neither unique to a
		data source of global cdef, but is unique when those two variables combine. */
		$cdef_graph_defs = ""; $cdef_total_ds = ""; $cdef_similar_ds = "";

		if ((!empty($graph_item["cdef"])) && (!isset($cdef_cache{$graph_item["cdef"]}{$graph_item["data_source_item_id"]}[$cf_id]))) {
			$cdef_string = $graph_item["cdef"];

			/* create cdef string for "total all data sources" if requested */
			if ((strstr($cdef_string, "ALL_DATA_SOURCES_DUPS")) || (strstr($cdef_string, "ALL_DATA_SOURCES_NODUPS"))) {
				$item_count = 0;
				for ($t=0;($t<count($graph_items));$t++) {
					if ((is_graph_item_type_primary($graph_items[$t]["graph_item_type"])) && (!empty($graph_items[$t]["data_source_item_id"]))) {
						/* if the user screws up CF settings, PHP will generate warnings if left unchecked */
						if (isset($cf_ds_cache{$graph_items[$t]["data_source_item_id"]}[$cf_id])) {
							$def_name = generate_graph_def_name(strval($cf_ds_cache{$graph_items[$t]["data_source_item_id"]}[$cf_id]));
							$cdef_total_ds .= ($item_count == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
							$item_count++;
						}
					}
				}

				/* if there is only one item to total, don't even bother with the summation. otherwise
				cdef=a,b,c,+,+ is fine. */
				if ($item_count > 1) {
					$cdef_total_ds .= str_repeat(",+", ($item_count - 2)) . ",+";
				}
			}

			/* create cdef string for "total similar data sources" if requested */
			if ((strpos($cdef_string, "SIMILAR_DATA_SOURCES_DUPS")) || (strpos($cdef_string, "SIMILAR_DATA_SOURCES_NODUPS"))) {
				$sources_seen = array();
				$item_count = 0;

				for ($t=0;($t<count($graph_items));$t++) {
					if ((is_graph_item_type_primary($graph_items[$t]["graph_item_type"])) && (!empty($graph_items[$t]["data_source_item_id"])) && ($graph_item["data_source_name"] == $graph_items[$t]["data_source_name"])) {
						/* if the user screws up CF settings, PHP will generate warnings if left unchecked */
						if (isset($cf_ds_cache{$graph_items[$t]["data_source_item_id"]}[$cf_id]) && (!isset($sources_seen{$graph_items[$t]["data_source_item_id"]}))) {
							$def_name = generate_graph_def_name(strval($cf_ds_cache{$graph_items[$t]["data_source_item_id"]}[$cf_id]));
							$cdef_similar_ds .= ($item_count == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
							$sources_seen{$graph_items[$t]["data_source_item_id"]} = 1;
							$item_count++;
						}
					}
				}

				/* if there is only one item to total, don't even bother with the summation. otherwise
				cdef=a,b,c,+,+ is fine. */
				if ($item_count > 1) {
					$cdef_similar_ds .= str_repeat(",+", ($item_count - 2)) . ",+";
				}
			}

			$cdef_string = str_replace("CURRENT_DATA_SOURCE", generate_graph_def_name(strval((isset($cf_ds_cache{$graph_item["data_source_item_id"]}[$cf_id]) ? $cf_ds_cache{$graph_item["data_source_item_id"]}[$cf_id] : "0"))), $cdef_string);
			$cdef_string = str_replace("ALL_DATA_SOURCES_NODUPS", $cdef_total_ds, $cdef_string);
			$cdef_string = str_replace("SIMILAR_DATA_SOURCES_NODUPS", $cdef_similar_ds, $cdef_string);

			/* data source item variables */
			$cdef_string = str_replace("CURRENT_DS_MINIMUM_VALUE", (empty($graph_item["rrd_minimum"]) ? "0" : $graph_item["rrd_minimum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_DS_MAXIMUM_VALUE", (empty($graph_item["rrd_maximum"]) ? "0" : $graph_item["rrd_maximum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MINIMUM_VALUE", (empty($graph["lower_limit"]) ? "0" : $graph["lower_limit"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MAXIMUM_VALUE", (empty($graph["upper_limit"]) ? "0" : $graph["upper_limit"]), $cdef_string);

			/* make the initial "virtual" cdef name: 'cdef' + [a,b,c,d...] */
			$cdef_graph_defs .= "CDEF:cdef" . generate_graph_def_name(strval($i)) . "=";
			$cdef_graph_defs .= $cdef_string;
			$cdef_graph_defs .= " \\\n";

			/* the CDEF cache is so we do not create duplicate CDEF's on a graph */
			$cdef_cache{$graph_item["cdef"]}{$graph_item["data_source_item_id"]}[$cf_id] = "$i";
		}

		/* add the cdef string to the end of the def string */
		$graph_defs .= $cdef_graph_defs;

		/* note the current item_id for easy access */
		$graph_item_id = $graph_item["id"];

		/* if we are not displaying a legend there is no point in us even processing the auto padding,
		text format stuff. */
		if ((!isset($graph_data_array["graph_nolegend"])) && (!empty($graph["auto_padding"])) && (isset($text_format_lengths{$graph_item["data_source_item_id"]}))) {
			/* we are basing how much to pad on area and stack text format,
			not gprint. but of course the padding has to be displayed in gprint,
			how fun! */

			$pad_number = ($greatest_text_format - $text_format_lengths{$graph_item["data_source_item_id"]});
			$text_padding = str_pad("", $pad_number);

			/* two GPRINT's in a row screws up the padding, lets not do that */
			if (($graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_GPRINT) && ($last_graph_type == GRAPH_ITEM_TYPE_GPRINT)) {
				$text_padding = "";
			}

			$last_graph_type = $graph_item["graph_item_type"];
		}

		/* we put this in a variable so it can be manipulated before mainly used
		if we want to skip it, like below */
		$current_graph_item_type = $graph_item["graph_item_type"];

		/* IF this graph item has a data source... get a DEF name for it, or the cdef if that applies
		to this graph item */
		if ($graph_item["cdef"] == "") {
			if (isset($cf_ds_cache{$graph_item["data_source_item_id"]}[$cf_id])) {
				$data_source_name = generate_graph_def_name(strval($cf_ds_cache{$graph_item["data_source_item_id"]}[$cf_id]));
			}else{
				$data_source_name = "";
			}
		}else{
			$data_source_name = "cdef" . generate_graph_def_name(strval($cdef_cache{$graph_item["cdef"]}{$graph_item["data_source_item_id"]}[$cf_id]));
		}

		/* to make things easier... if there is no text format set; set blank text */
		if (!isset($graph_variables["legend_format"][$graph_item_id])) {
			$graph_variables["legend_format"][$graph_item_id] = "";
		}

		if (!isset($hardreturn[$graph_item_id])) {
			$hardreturn[$graph_item_id] = "";
		}

		/* +++++++++++++++++++++++ GRAPH ITEMS +++++++++++++++++++++++ */

		/* most of the calculations have been done above. now we have for print everything out
		in an RRDTool-friendly fashion */
		if (is_graph_item_type_primary($graph_item["graph_item_type"])) {
			$graph_variables["legend_format"][$graph_item_id] = str_replace(":", "\:", $graph_variables["legend_format"][$graph_item_id]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_item_type"]} . ":" . $data_source_name . "#" . $graph_item["color"] . ":" . "\"" . $graph_variables["legend_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
		}elseif ($graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_COMMENT) {
			$txt_graph_items .= $graph_item_types{$graph_item["graph_item_type"]} . ":\"" . $graph_variables["legend_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
		}elseif (($graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_GPRINT) && (!isset($graph_data_array["graph_nolegend"]))) {
			$graph_variables["legend_format"][$graph_item_id] = str_replace(":", "\:", $graph_variables["legend_format"][$graph_item_id]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_item_type"]} . ":" . $data_source_name . ":" . $consolidation_functions{$graph_item["consolidation_function"]} . ":\"$text_padding" . $graph_variables["legend_format"][$graph_item_id] . $graph_item["gprint_format"] . $hardreturn[$graph_item_id] . "\" ";
		}elseif ($graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_HRULE) {
			$graph_variables["legend_format"][$graph_item_id] = str_replace(":", "\:", $graph_variables["legend_format"][$graph_item_id]); /* escape colons */
			$graph_variables["legend_value"][$graph_item_id] = str_replace(":", "\:", $graph_variables["legend_value"][$graph_item_id]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_item_type"]} . ":" . $graph_variables["legend_value"][$graph_item_id] . "#" . $graph_item["color"] . ":\"" . $graph_variables["legend_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
		}elseif ($graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_VRULE) {
			$graph_variables["legend_format"][$graph_item_id] = str_replace(":", "\:", $graph_variables["legend_format"][$graph_item_id]); /* escape colons */

			$value_array = explode(":", $graph_item["legend_value"]);

			if ($value_array[0] < 0) {
				$value = date("U") - (-3600 * $value_array[0]) - 60 * $value_array[1];
			}else{
				$value = date("U", mktime($value_array[0],$value_array[1],0));
			}

			$txt_graph_items .= $graph_item_types{$graph_item["graph_item_type"]} . ":" . $value . "#" . $graph_item["color"] . ":\"" . $graph_variables["legend_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
		}

		$i++;

		if ($i < sizeof($graph_items)) {
			$txt_graph_items .= RRD_NL;
		}
	}
	}

	/* either print out the source or pass the source onto rrdtool to get us a nice PNG */
	if (isset($graph_data_array["print_source"])) {
		print "<PRE>" . read_config_option("path_rrdtool") . " graph $graph_opts$graph_defs$txt_graph_items</PRE>";
	}else{
		if (isset($graph_data_array["export"])) {
			rrdtool_execute("graph $graph_opts$graph_defs$txt_graph_items", false, RRDTOOL_OUTPUT_NULL, $rrd_struc, FACIL_POLLER);
			return 0;
		}else{
			if (isset($graph_data_array["output_flag"])) {
				$output_flag = $graph_data_array["output_flag"];
			}else{
				$output_flag = RRDTOOL_OUTPUT_GRAPH_DATA;
			}

			return rrdtool_execute("graph $graph_opts$graph_defs$txt_graph_items", false, $output_flag, $rrd_struc,$syslog_facility);
		}
	}
}

?>
