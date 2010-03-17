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

define("RRD_NL", " \\\n");
define("MAX_FETCH_CACHE_SIZE", 5);

function escape_command($command) {
	return preg_replace("/(\\\$|`)/", "", $command);
	#TODO return preg_replace((\\\$(?=\w+|\*|\@|\#|\?|\-|\\\$|\!|\_|[0-9]|\(.*\))|`(?=.*(?=`)))","$2", $command);  #suggested by ldevantier to allow for a single $
}

function rrd_init($output_to_term = TRUE) {
	global $config;

	/* set the rrdtool default font */
	if (read_config_option("path_rrdtool_default_font")) {
		putenv("RRD_DEFAULT_FONT=" . read_config_option("path_rrdtool_default_font"));
	}

	if ($output_to_term) {
		$command = read_config_option("path_rrdtool") . " - ";
	}else{
		if (CACTI_SERVER_OS == "win32") {
			$command = read_config_option("path_rrdtool") . " - > nul";
		}else{
			$command = read_config_option("path_rrdtool") . " - > /dev/null 2>&1";
		}
	}

	$rrd_struc["fd"] = popen($command, "w");

	return $rrd_struc;
}

function rrd_close($rrd_struc) {
	/* close the rrdtool file descriptor */
	pclose($rrd_struc["fd"]);
}

function rrd_get_fd(&$rrd_struc, $fd_type) {
	if (sizeof($rrd_struc) == 0) {
		return 0;
	}else{
		return $rrd_struc["fd"];
	}
}

function rrdtool_execute($command_line, $log_to_stdout, $output_flag, $rrd_struc = array(), $logopt = "WEBLOG") {
	global $config;

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
	if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_DEBUG) {
		cacti_log("CACTI2RRD: " . read_config_option("path_rrdtool") . " $command_line", $log_to_stdout, $logopt);
	}

	/* if we want to see the error output from rrdtool; make sure to specify this */
	if (($output_flag == RRDTOOL_OUTPUT_STDERR) && (!isset($rrd_struc["fd"]) || (sizeof($rrd_struc["fd"]) == 0))) {
		$command_line .= " 2>&1";
	}

	/* use popen to eliminate the zombie issue */
	if (CACTI_SERVER_OS == "unix") {
		/* an empty $rrd_struc array means no fp is available */
		if (!isset($rrd_struc["fd"]) || (sizeof($rrd_struc["fd"]) == 0)) {
			session_write_close();
			$fp = popen(read_config_option("path_rrdtool") . escape_command(" $command_line"), "r");
			if (!$fp) {
				unset($fp);
			}
		}else{
			$i = 0;

			while (1) {
				if (fwrite(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ), escape_command(" $command_line") . "\r\n") == false) {
					cacti_log("ERROR: Detected RRDtool Crash attempting to perform write");

					/* close the invalid pipe */
					rrd_close($rrd_struc);

					/* open a new rrdtool process */
					$rrd_struc = rrd_init();

					if ($i > 4) {
						cacti_log("FATAL: RRDtool Restart Attempts Exceeded. Giving up on command.");

						break;
					}else{
						$i++;
					}

					continue;
				}else{
					fflush(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ));

					break;
				}
			}
		}
	}elseif (CACTI_SERVER_OS == "win32") {
		/* an empty $rrd_struc array means no fp is available */
		if (!isset($rrd_struc["fd"]) || (sizeof($rrd_struc["fd"]) == 0)) {
			session_write_close();
			$fp = popen(read_config_option("path_rrdtool") . escape_command(" $command_line"), "rb");
			if (!$fp) {
				unset($fp);
			}
		}else{
			$i = 0;

			while (1) {
				if (fwrite(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ), escape_command(" $command_line") . "\r\n") == false) {
					cacti_log("ERROR: Detected RRDtool Crash attempting to perform write");

					/* close the invalid pipe */
					rrd_close($rrd_struc);

					/* open a new rrdtool process */
					$rrd_struc = rrd_init();

					if ($i > 4) {
						cacti_log("FATAL: RRDtool Restart Attempts Exceeded.  Giving up on command.");

						break;
					}else{
						$i++;
					}

					continue;
				}else{
					fflush(rrd_get_fd($rrd_struc, RRDTOOL_PIPE_CHILD_READ));

					break;
				}
			}
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
					return __("PNG Output OK");
				}

				if (substr($output, 0, 5) == "GIF87") {
					return __("GIF Output OK");
				}

				if (substr($output, 0, 5) == "<?xml") {
					return __("SVG/XML Output OK");
				}

				print $output;
			}

			break;
		case RRDTOOL_OUTPUT_GRAPH_DATA:
			if (isset($fp)) {
				#return fpassthru($fp); /* TODO: this fails for SVG; still not clear, why (gandalf) */
				$line = "";
				while (!feof($fp)) {
					$line .= fgets($fp, 4096);
				}

				return $line;
			}

			break;
	}
}

function rrdtool_function_create($local_data_id, $show_source, $rrd_struc) {
	global $config;
	include(CACTI_BASE_PATH . "/include/global_arrays.php");
	require(CACTI_BASE_PATH . "/include/presets/preset_rra_arrays.php");
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	$data_source_path = get_data_source_path($local_data_id, true);

	/* ok, if that passes lets check to make sure an rra does not already
	exist, the last thing we want to do is overright data! */
	if ($show_source != true) {
		if (file_exists($data_source_path) == true) {
			return -1;
		}
	}

	/* the first thing we must do is make sure there is at least one
	rra associated with this data source... *
	UPDATE: As of version 0.6.6, we are splitting this up into two
	SQL strings because of the multiple DS per RRD support. This is
	not a big deal however since this function gets called once per
	data source */

	$rras = db_fetch_assoc("select
		data_template_data.rrd_step,
		rra.x_files_factor,
		rra.steps,
		rra.rows,
		rra_cf.consolidation_function_id,
		(rra.rows*rra.steps) as rra_order
		from data_template_data
		left join data_template_data_rra on (data_template_data.id=data_template_data_rra.data_template_data_id)
		left join rra on (data_template_data_rra.rra_id=rra.id)
		left join rra_cf on (rra.id=rra_cf.rra_id)
		where data_template_data.local_data_id=$local_data_id
		and (rra.steps is not null or rra.rows is not null)
		order by rra_cf.consolidation_function_id,rra_order");

	/* if we find that this DS has no RRA associated; get out */
	if (sizeof($rras) <= 0) {
		cacti_log("ERROR: There are no RRA's assigned to local_data_id: $local_data_id.");
		return false;
	}

	/* create the "--step" line */
	$create_ds = RRD_NL . "--step ". $rras[0]["rrd_step"] . " " . RRD_NL;

	/* query the data sources to be used in this .rrd file */
	$data_sources = db_fetch_assoc("SELECT
		data_template_rrd.id,
		data_template_rrd.rrd_heartbeat,
		data_template_rrd.rrd_minimum,
		data_template_rrd.rrd_maximum,
		data_template_rrd.rrd_compute_rpn,
		data_template_rrd.data_source_type_id
		FROM data_template_rrd
		WHERE data_template_rrd.local_data_id=$local_data_id
		ORDER BY local_data_template_rrd_id");

	/* ONLY make a new DS entry if:
	- There is multiple data sources and this item is not the main one.
	- There is only one data source (then use it) */

	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		/* use the cacti ds name by default or the user defined one, if entered */
		$data_source_name = get_data_source_item_name($data_source["id"]);

		/* special format for COMPUTE data source type */
		if ( $data_source["data_source_type_id"] == DATA_SOURCE_TYPE_COMPUTE ) {
			$create_ds .= "DS:$data_source_name:" . $data_source_types{$data_source["data_source_type_id"]} . ":" . (empty($data_source["rrd_compute_rpn"]) ? "U" : $data_source["rrd_compute_rpn"]) . RRD_NL;
		} else {
			$create_ds .= "DS:$data_source_name:" . $data_source_types{$data_source["data_source_type_id"]} . ":" . $data_source["rrd_heartbeat"] . ":" . $data_source["rrd_minimum"] . ":" . (empty($data_source["rrd_maximum"]) ? "U" : $data_source["rrd_maximum"]) . RRD_NL;
		}
	}
	}

	$create_rra = "";
	/* loop through each available RRA for this DS */
	foreach ($rras as $rra) {
		$create_rra .= "RRA:" . $consolidation_functions{$rra["consolidation_function_id"]} . ":" . $rra["x_files_factor"] . ":" . $rra["steps"] . ":" . $rra["rows"] . RRD_NL;
	}

	/* check for structured path configuration, if in place verify directory
	   exists and if not create it.
	 */
	if (read_config_option("extended_paths") == CHECKED) {
		if (!is_dir(dirname($data_source_path))) {
			if (mkdir(dirname($data_source_path), 0775)) {
				if (CACTI_SERVER_OS != "win32") {
					$owner_id      = fileowner(CACTI_RRA_PATH);
					$group_id      = filegroup(CACTI_RRA_PATH);

					if ((chown(dirname($data_source_path), $owner_id)) &&
						(chgrp(dirname($data_source_path), $group_id))) {
						/* permissions set ok */
					}else{
						cacti_log("ERROR: Unable to set directory permissions for '" . dirname($data_source_path) . "'", FALSE);
					}
				}
			}else{
				cacti_log("ERROR: Unable to create directory '" . dirname($data_source_path) . "'", FALSE);
			}
		}
	}

	if ($show_source == true) {
		return read_config_option("path_rrdtool") . " create" . RRD_NL . "$data_source_path$create_ds$create_rra";
	}else{
		rrdtool_execute("create $data_source_path $create_ds$create_rra", true, RRDTOOL_OUTPUT_STDOUT, $rrd_struc, "POLLER");
	}
}

function rrdtool_function_update($update_cache_array, $rrd_struc) {
	/* lets count the number of rrd files processed */
	$rrds_processed = 0;

	while (list($rrd_path, $rrd_fields) = each($update_cache_array)) {
		$create_rrd_file = false;

		/* create the rrd if one does not already exist */
		if (!file_exists($rrd_path)) {
			rrdtool_function_create($rrd_fields["local_data_id"], false, $rrd_struc);

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

				rrdtool_execute("update $rrd_path --template $rrd_update_template $rrd_update_values", true, RRDTOOL_OUTPUT_STDOUT, $rrd_struc, "POLLER");
				$rrds_processed++;
			}
		}
	}

	return $rrds_processed;
}

$rrd_fetch_cache = array();

/* rrdtool_function_fetch - given a data source, return all of its data in an array
   @param $local_data_id - the data source to fetch data for
   @param $start_time - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @param $end_time - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @param $resolution - the accuracy of the data measured in seconds
   @param $show_unknown - Show unknown 'NAN' values in the output as 'U'
   @returns - (array) an array containing all data in this data source broken down
     by each data source item. the maximum of all data source items is included in
     an item called 'ninety_fifth_percentile_maximum' */
function rrdtool_function_fetch($local_data_id, $start_time, $end_time, $resolution = 0, $show_unknown = 0) {
	global $rrd_fetch_cache;

	if (empty($local_data_id)) {
		unset($var);
		return $var;
	}

	/* the cache hash is used to identify unique items in the cache */
	$current_hash_cache = md5($local_data_id . $start_time . $end_time . $resolution . $show_unknown);

	/* return the cached entry if available */
	if (isset($rrd_fetch_cache[$current_hash_cache])) {
		return $rrd_fetch_cache[$current_hash_cache];
	}

	$regexps = array();
	$fetch_array = array();

	$data_source_path = get_data_source_path($local_data_id, true);

	/* update the rrd from boost if applicable */
	api_plugin_hook_function('rrdtool_function_fetch_cache_check', $local_data_id);

	/* build and run the rrdtool fetch command with all of our data */
	$cmd_line = "fetch $data_source_path AVERAGE -s $start_time -e $end_time";
	if ($resolution > 0) {
		$cmd_line .= " -r $resolution";
	}
	$output = rrdtool_execute($cmd_line, false, RRDTOOL_OUTPUT_STDOUT);

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


	/* nth_percentile_maximum is removed if Unknown values are requested in the output.  This
	is because the max_array function will give unpredictable results when there is a mix
	of number and text data */
	if ((isset($fetch_array["data_source_names"])) && ($show_unknown  == 0)) {
		$next_index = count($fetch_array["data_source_names"]);

		$fetch_array["data_source_names"][$next_index] = "nth_percentile_maximum";

		/* calculate the max for each row */
		for ($i=0; $i<count($max_array); $i++) {
			$fetch_array["values"][$next_index][$i] = max($max_array[$i]);
		}
	}

	/* clear the cache if it gets too big */
	if (sizeof($rrd_fetch_cache) >= MAX_FETCH_CACHE_SIZE) {
		$rrd_fetch_cache = array();
	}

	/* update the cache */
	if (MAX_FETCH_CACHE_SIZE > 0) {
		$rrd_fetch_cache[$current_hash_cache] = $fetch_array;
	}

	return $fetch_array;
}

function rrdtool_function_graph($local_graph_id, $rra_id, $graph_data_array, $rrd_struc = array()) {
	global $config;
	include(CACTI_BASE_PATH . "/include/global_arrays.php");
	require(CACTI_BASE_PATH . "/include/presets/preset_rra_arrays.php");
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	include_once(CACTI_BASE_PATH . "/lib/cdef.php");
	include_once(CACTI_BASE_PATH . "/lib/vdef.php");
	include_once(CACTI_BASE_PATH . "/lib/graph_variables.php");
	include_once(CACTI_BASE_PATH . "/lib/time.php");

	/* set the rrdtool default font */
	if (read_config_option("path_rrdtool_default_font")) {
		putenv("RRD_DEFAULT_FONT=" . read_config_option("path_rrdtool_default_font"));
	}

	/* before we do anything; make sure the user has permission to view this graph,
	if not then get out */
	if ((read_config_option("auth_method") != 0) && (isset($_SESSION["sess_user_id"]))) {
		$access_denied = !(is_graph_allowed($local_graph_id));

		if ($access_denied == true) {
			return "GRAPH ACCESS DENIED";
		}
	}

	$data = api_plugin_hook_function('rrdtool_function_graph_cache_check', array('local_graph_id' => $local_graph_id,'rra_id' => $rra_id,'rrd_struc' => $rrd_struc,'graph_data_array' => $graph_data_array, 'return' => false));
	if (isset($data['return']) && $data['return'] != false)
		return $data['return'];

	/* find the step and how often this graph is updated with new data */
	$ds_step = db_fetch_cell("select
		data_template_data.rrd_step
		from (data_template_data,data_template_rrd,graph_templates_item)
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and graph_templates_item.local_graph_id=$local_graph_id
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
			$rras = get_associated_rras($local_graph_id);

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
		graph_local.device_id,
		graph_local.snmp_query_id,
		graph_local.snmp_index,
		graph_templates_graph.title_cache,
		graph_templates_graph.vertical_label,
		graph_templates_graph.slope_mode,
		graph_templates_graph.auto_scale,
		graph_templates_graph.auto_scale_opts,
		graph_templates_graph.auto_scale_log,
		graph_templates_graph.scale_log_units,
		graph_templates_graph.auto_scale_rigid,
		graph_templates_graph.alt_y_grid,
		graph_templates_graph.auto_padding,
		graph_templates_graph.base_value,
		graph_templates_graph.upper_limit,
		graph_templates_graph.lower_limit,
		graph_templates_graph.height,
		graph_templates_graph.width,
		graph_templates_graph.image_format_id,
		graph_templates_graph.unit_value,
		graph_templates_graph.unit_exponent_value,
		graph_templates_graph.export,
		graph_templates_graph.right_axis,
		graph_templates_graph.right_axis_label,
		graph_templates_graph.right_axis_format,
		graph_templates_graph.only_graph,
		graph_templates_graph.full_size_mode,
		graph_templates_graph.no_gridfit,
		graph_templates_graph.x_grid,
		graph_templates_graph.unit_length,
		graph_templates_graph.colortag_back,
		graph_templates_graph.colortag_canvas,
		graph_templates_graph.colortag_shadea,
		graph_templates_graph.colortag_shadeb,
		graph_templates_graph.colortag_grid,
		graph_templates_graph.colortag_mgrid,
		graph_templates_graph.colortag_font,
		graph_templates_graph.colortag_axis,
		graph_templates_graph.colortag_frame,
		graph_templates_graph.colortag_arrow,
		graph_templates_graph.font_render_mode,
		graph_templates_graph.font_smoothing_threshold,
		graph_templates_graph.graph_render_mode,
		graph_templates_graph.pango_markup,
		graph_templates_graph.interlaced,
		graph_templates_graph.tab_width,
		graph_templates_graph.watermark,
		graph_templates_graph.force_rules_legend,
		graph_templates_graph.legend_position,
		graph_templates_graph.legend_direction,
		graph_templates_graph.grid_dash,
		graph_templates_graph.border
		from (graph_templates_graph,graph_local)
		where graph_local.id=graph_templates_graph.local_graph_id
		and graph_templates_graph.local_graph_id=$local_graph_id");

	/* lets make that sql query... */
	$graph_items = db_fetch_assoc("select
		graph_templates_item.id as graph_templates_item_id,
		graph_templates_item.cdef_id,
		graph_templates_item.vdef_id,
		graph_templates_item.text_format,
		graph_templates_item.value,
		graph_templates_item.hard_return,
		graph_templates_item.consolidation_function_id,
		graph_templates_item.graph_type_id,
		graph_templates_item.line_width,
		graph_templates_item.dashes,
		graph_templates_item.dash_offset,
		graph_templates_item.shift,
		graph_templates_item.textalign,
		graph_templates_gprint.gprint_text,
		colors.hex,
		graph_templates_item.alpha,
		data_template_rrd.id as data_template_rrd_id,
		data_template_rrd.local_data_id,
		data_template_rrd.rrd_minimum,
		data_template_rrd.rrd_maximum,
		data_template_rrd.data_source_name,
		data_template_rrd.local_data_template_rrd_id
		from graph_templates_item
		left join data_template_rrd on (graph_templates_item.task_item_id=data_template_rrd.id)
		left join colors on (graph_templates_item.color_id=colors.id)
		left join graph_templates_gprint on (graph_templates_item.gprint_id=graph_templates_gprint.id)
		where graph_templates_item.local_graph_id=$local_graph_id
		order by graph_templates_item.sequence");

	/* +++++++++++++++++++++++ GRAPH OPTIONS +++++++++++++++++++++++ */

	$rrdtool_version = read_config_option("rrdtool_version");

	/* export options: either output to stream or to file */
	if (isset($graph_data_array["export"])) {
		$graph_opts = read_config_option("path_html_export") . "/" . $graph_data_array["export_filename"] . RRD_NL;
	}else{
		if (empty($graph_data_array["output_filename"])) {
				$graph_opts = "-" . RRD_NL;
		}else{
			$graph_opts = $graph_data_array["output_filename"] . RRD_NL;
		}
	}

	# image format
	$graph_opts .= rrdgraph_image_format($graph["image_format_id"], $rrdtool_version);

	# start and end time
	list($graph_start, $graph_end) = rrdgraph_start_end($graph_data_array, $rra, $seconds_between_graph_updates);
	$graph["graph_start"] = $graph_start;
	$graph["graph_end"] = $graph_end;
	$graph_opts .= "--start=$graph_start" . RRD_NL . "--end=$graph_end" . RRD_NL;

	$graph_opts .= rrdgraph_opts($graph, $graph_data_array, $rrdtool_version);

	$graph_opts .= rrdgraph_scale($graph);

	$graph_date = date_time_format();

	/* display the timespan for zoomed graphs */
	if ((isset($graph_data_array["graph_start"])) && (isset($graph_data_array["graph_end"]))) {
		if (($graph_data_array["graph_start"] < 0) && ($graph_data_array["graph_end"] < 0)) {
			if ($rrdtool_version != RRD_VERSION_1_0) {
				$graph_opts .= "COMMENT:\"" . __("From") . " " . str_replace(":", "\:", date($graph_date, time()+$graph_data_array["graph_start"])) . " " . __("To") . " " . str_replace(":", "\:", date($graph_date, time()+$graph_data_array["graph_end"])) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}else {
				$graph_opts .= "COMMENT:\"" . __("From") . " " . date($graph_date, time()+$graph_data_array["graph_start"]) . " " . __("To") . " " . date($graph_date, time()+$graph_data_array["graph_end"]) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}
		}else if (($graph_data_array["graph_start"] >= 0) && ($graph_data_array["graph_end"] >= 0)) {
			if ($rrdtool_version != RRD_VERSION_1_0) {
				$graph_opts .= "COMMENT:\"" . __("From") . " " . str_replace(":", "\:", date($graph_date, $graph_data_array["graph_start"])) . " " . __("To") . " " . str_replace(":", "\:", date($graph_date, $graph_data_array["graph_end"])) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}else {
				$graph_opts .= "COMMENT:\"" . __("From") . " " . date($graph_date, $graph_data_array["graph_start"]) . " " . __("To") . " " . date($graph_date, $graph_data_array["graph_end"]) . "\\c\"" . RRD_NL . "COMMENT:\"  \\n\"" . RRD_NL;
			}
		}
	}


	/* Replace "|query_*|" in the graph command to replace e.g. vertical_label.  */
	$graph_opts = rrd_substitute_device_query_data($graph_opts, $graph, NULL);


	/* define some variables */
	$graph_defs = "";
	$txt_graph_items = "";
	$text_padding = "";
	$greatest_text_format = 0;
	$last_graph_type = "";
	$i = 0; $j = 0;
	$last_graph_cf = array();
	if (sizeof($graph_items) > 0) {

		/* we need to add a new column "cf_reference", so unless PHP 5 is used, this foreach syntax is required */
		foreach ($graph_items as $key => $graph_item) {
			/* mimic the old behavior: LINE[123], AREA and STACK items use the CF specified in the graph item */
			if ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA  ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_TICK) {
				$graph_cf = $graph_item["consolidation_function_id"];
				/* remember the last CF for this data source for use with GPRINT
				 * if e.g. an AREA/AVERAGE and a LINE/MAX is used
				 * we will have AVERAGE first and then MAX, depending on GPRINT sequence */
				$last_graph_cf["data_source_name"]["local_data_template_rrd_id"] = $graph_cf;
				/* remember this for second foreach loop */
				$graph_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_AVERAGE) {
				$graph_cf = $graph_item["consolidation_function_id"];
				$graph_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_LAST) {
				$graph_cf = $graph_item["consolidation_function_id"];
				$graph_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MAX) {
				$graph_cf = $graph_item["consolidation_function_id"];
				$graph_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MIN) {
				$graph_cf = $graph_item["consolidation_function_id"];
				$graph_items[$key]["cf_reference"] = $graph_cf;
				#}elseif ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT) {
				#/* ATTENTION!
				# * the "CF" given on graph_item edit screen for GPRINT is indeed NOT a real "CF",
				# * but an aggregation function
				# * see "man rrdgraph_data" for the correct VDEF based notation
				# * so our task now is to "guess" the very graph_item, this GPRINT is related to
				# * and to use that graph_item's CF */
				#if (isset($last_graph_cf["data_source_name"]["local_data_template_rrd_id"])) {
				#	$graph_cf = $last_graph_cf["data_source_name"]["local_data_template_rrd_id"];
				#	/* remember this for second foreach loop */
				#	$graph_items[$key]["cf_reference"] = $graph_cf;
				#} else {
				#	$graph_cf = generate_graph_best_cf($graph_item["local_data_id"], $graph_item["consolidation_function_id"]);
				#	/* remember this for second foreach loop */
				#	$graph_items[$key]["cf_reference"] = $graph_cf;
				#}
			}else{
				/* all other types are based on the best matching CF */
				#GRAPH_ITEM_TYPE_COMMENT
				#GRAPH_ITEM_TYPE_HRULE
				#GRAPH_ITEM_TYPE_VRULE
				#GRAPH_ITEM_TYPE_TEXTALIGN
				$graph_cf = generate_graph_best_cf($graph_item["local_data_id"], $graph_item["consolidation_function_id"]);
				/* remember this for second foreach loop */
				$graph_items[$key]["cf_reference"] = $graph_cf;
			}

			if ((!empty($graph_item["local_data_id"])) && (!isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$graph_cf]))) {
				/* use a user-specified ds path if one is entered */
				$data_source_path = get_data_source_path($graph_item["local_data_id"], true);

				/* FOR WIN32: Escape all colon for drive letters (ex. D\:/path/to/rra) */
				$data_source_path = str_replace(":", "\:", $data_source_path);

				if (!empty($data_source_path)) {
					/* NOTE: (Update) Data source DEF names are created using the graph_item_id; then passed
					to a function that matches the digits with letters. rrdtool likes letters instead
					of numbers in DEF names; especially with CDEF's. cdef's are created
					the same way, except a 'cdef' is put on the beginning of the hash */
					$graph_defs .= "DEF:" . generate_graph_def_name(strval($i)) . "=\"$data_source_path\":" . $graph_item["data_source_name"] . ":" . $consolidation_functions[$graph_cf];
					if ($graph_item["shift"] == CHECKED && $graph_item["value"] > 0) {	# create a SHIFTed DEF
						$graph_defs .= ":start=" . $graph["graph_start"] . "-" . $graph_item["value"];
						$graph_defs .= ":end=" . $graph["graph_end"] . "-" . $graph_item["value"];
					}
					$graph_defs .= RRD_NL;

					$cf_ds_cache{$graph_item["data_template_rrd_id"]}[$graph_cf] = "$i";

					$i++;
				}
			}

			/* cache cdef value here to support data query variables in the cdef string */
			if (empty($graph_item["cdef_id"])) {
				$graph_item["cdef_cache"] = "";
				$graph_items[$j]["cdef_cache"] = "";
			}else{
				$graph_item["cdef_cache"] = get_cdef($graph_item["cdef_id"]);
				$graph_items[$j]["cdef_cache"] = get_cdef($graph_item["cdef_id"]);
			}

			/* cache vdef value here */
			if (empty($graph_item["vdef_id"])) {
				$graph_item["vdef_cache"] = "";
				$graph_items[$j]["vdef_cache"] = "";
			}else{
				$graph_item["vdef_cache"] = get_vdef($graph_item["vdef_id"]);
				$graph_items[$j]["vdef_cache"] = get_vdef($graph_item["vdef_id"]);
			}


			/* +++++++++++++++++++++++ LEGEND: TEXT SUBSTITUTION (<>'s) +++++++++++++++++++++++ */

			/* note the current item_id for easy access */
			$graph_item_id = $graph_item["graph_templates_item_id"];

			/* the following fields will be searched for graph variables */
			$variable_fields = array(
				"text_format" => array(
					"process_no_legend" => false
					),
				"value" => array(
					"process_no_legend" => true
					),
				"cdef_cache" => array(
					"process_no_legend" => true
					),
				"vdef_cache" => array(
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
					$graph_variables[$field_name][$graph_item_id] = str_replace("|date_time|", date(date_time_format(), strtotime(db_fetch_cell("select value from settings where name='date'"))), $graph_variables[$field_name][$graph_item_id]);
				}

				/* data source title substitution */
				if (strstr($graph_variables[$field_name][$graph_item_id], "|data_source_title|")) {
					$graph_variables[$field_name][$graph_item_id] = str_replace("|data_source_title|", get_data_source_title($graph_item["local_data_id"]), $graph_variables[$field_name][$graph_item_id]);
				}

				/* data query variables */
				$graph_variables[$field_name][$graph_item_id] = rrd_substitute_device_query_data($graph_variables[$field_name][$graph_item_id], $graph, $graph_item);

				/* Nth percentile */
				if (preg_match_all("/\|([0-9]{1,2}):(bits|bytes):(\d):(current|total|max|total_peak|all_max_current|all_max_peak|aggregate_max|aggregate_sum|aggregate_current|aggregate):(\d)?\|/", $graph_variables[$field_name][$graph_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						$graph_variables[$field_name][$graph_item_id] = str_replace($match[0], variable_nth_percentile($match, $graph_item, $graph_items, $graph_start, $graph_end), $graph_variables[$field_name][$graph_item_id]);
					}
				}

				/* bandwidth summation */
				if (preg_match_all("/\|sum:(\d|auto):(current|total|atomic):(\d):(\d+|auto)\|/", $graph_variables[$field_name][$graph_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						$graph_variables[$field_name][$graph_item_id] = str_replace($match[0], variable_bandwidth_summation($match, $graph_item, $graph_items, $graph_start, $graph_end, $rra["steps"], $ds_step), $graph_variables[$field_name][$graph_item_id]);
					}
				}
			}

			/* if we are not displaying a legend there is no point in us even processing the auto padding,
			text format stuff. */
			if (!isset($graph_data_array["graph_nolegend"])) {
				/* set hard return variable if selected (\n) */
				if ($graph_item["hard_return"] == CHECKED) {
					$hardreturn[$graph_item_id] = "\\n";
				}else{
					$hardreturn[$graph_item_id] = "";
				}

				/* +++++++++++++++++++++++ LEGEND: AUTO PADDING (<>'s) +++++++++++++++++++++++ */

				/* PADDING: remember this is not perfect! its main use is for the basic graph setup of:
				AREA - GPRINT-CURRENT - GPRINT-AVERAGE - GPRINT-MAXIMUM \n
				of course it can be used in other situations, however may not work as intended.
				If you have any additions to this small peice of code, feel free to send them to me. */
				if ($graph["auto_padding"] == CHECKED) {
					/* only applies to AREA, STACK and LINEs */
					if ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_TICK) {
						$text_format_length = strlen($graph_variables["text_format"][$graph_item_id]);

						if ($text_format_length > $greatest_text_format) {
							$greatest_text_format = $text_format_length;
						}
					}
				}
			}

			$j++;
		}
	}

	/* +++++++++++++++++++++++ GRAPH ITEMS: CDEF's +++++++++++++++++++++++ */

	$i = 0;
	reset($graph_items);

	if (sizeof($graph_items) > 0) {
	foreach ($graph_items as $graph_item) {
		/* first we need to check if there is a DEF for the current data source/cf combination. if so,
		we will use that */
		if (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}{$graph_item["consolidation_function_id"]})) {
			$cf_id = $graph_item["consolidation_function_id"];
		}else{
		/* if there is not a DEF defined for the current data source/cf combination, then we will have to
		improvise. choose the first available cf in the following order: AVERAGE, MAX, MIN, LAST */
			if (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[RRA_CF_TYPE_AVERAGE])) {
				$cf_id = RRA_CF_TYPE_AVERAGE; /* CF: AVERAGE */
			}elseif (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[RRA_CF_TYPE_MAX])) {
				$cf_id = RRA_CF_TYPE_MAX; /* CF: MAX */
			}elseif (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[RRA_CF_TYPE_MIN])) {
				$cf_id = RRA_CF_TYPE_MIN; /* CF: MIN */
			}elseif (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[RRA_CF_TYPE_LAST])) {
				$cf_id = RRA_CF_TYPE_LAST; /* CF: LAST */
			}else{
				$cf_id = RRA_CF_TYPE_AVERAGE; /* CF: AVERAGE */
			}
		}
		/* now remember the correct CF reference */
		$cf_id = $graph_item["cf_reference"];

		/* make cdef string here; a note about CDEF's in cacti. A CDEF is neither unique to a
		data source of global cdef, but is unique when those two variables combine. */
		$cdef_graph_defs = "";

		if ((!empty($graph_item["cdef_id"])) && (!isset($cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id]))) {

			$cdef_string 	= $graph_variables["cdef_cache"]{$graph_item["graph_templates_item_id"]};
			$magic_item 	= array();
			$already_seen	= array();
			$sources_seen	= array();
			$count_all_ds_dups = 0;
			$count_all_ds_nodups = 0;
			$count_similar_ds_dups = 0;
			$count_similar_ds_nodups = 0;

			/* if any of those magic variables are requested ... */
			if (preg_match("/(ALL_DATA_SOURCES_(NO)?DUPS|SIMILAR_DATA_SOURCES_(NO)?DUPS)/", $cdef_string) ||
				preg_match("/(COUNT_ALL_DS_(NO)?DUPS|COUNT_SIMILAR_DS_(NO)?DUPS)/", $cdef_string)) {

				/* now walk through each case to initialize array*/
				if (preg_match("/ALL_DATA_SOURCES_DUPS/", $cdef_string)) {
					$magic_item["ALL_DATA_SOURCES_DUPS"] = "";
				}
				if (preg_match("/ALL_DATA_SOURCES_NODUPS/", $cdef_string)) {
					$magic_item["ALL_DATA_SOURCES_NODUPS"] = "";
				}
				if (preg_match("/SIMILAR_DATA_SOURCES_DUPS/", $cdef_string)) {
					$magic_item["SIMILAR_DATA_SOURCES_DUPS"] = "";
				}
				if (preg_match("/SIMILAR_DATA_SOURCES_NODUPS/", $cdef_string)) {
					$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] = "";
				}
				if (preg_match("/COUNT_ALL_DS_DUPS/", $cdef_string)) {
					$magic_item["COUNT_ALL_DS_DUPS"] = "";
				}
				if (preg_match("/COUNT_ALL_DS_NODUPS/", $cdef_string)) {
					$magic_item["COUNT_ALL_DS_NODUPS"] = "";
				}
				if (preg_match("/COUNT_SIMILAR_DS_DUPS/", $cdef_string)) {
					$magic_item["COUNT_SIMILAR_DS_DUPS"] = "";
				}
				if (preg_match("/COUNT_SIMILAR_DS_NODUPS/", $cdef_string)) {
					$magic_item["COUNT_SIMILAR_DS_NODUPS"] = "";
				}

				/* loop over all graph items */
				for ($t=0;($t<count($graph_items));$t++) {

					/* only work on graph items, omit GRPINTs, COMMENTs and stuff */
					if (($graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_AREA ||
						$graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
						$graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
						$graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
						$graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
						$graph_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK) &&
						(!empty($graph_items[$t]["data_template_rrd_id"]))) {
						/* if the user screws up CF settings, PHP will generate warnings if left unchecked */

						/* matching consolidation function? */
						if (isset($cf_ds_cache{$graph_items[$t]["data_template_rrd_id"]}[$cf_id])) {
							$def_name = generate_graph_def_name(strval($cf_ds_cache{$graph_items[$t]["data_template_rrd_id"]}[$cf_id]));

							/* do we need ALL_DATA_SOURCES_DUPS? */
							if (isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
								$magic_item["ALL_DATA_SOURCES_DUPS"] .= ($count_all_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
							}

							/* do we need COUNT_ALL_DS_DUPS? */
							if (isset($magic_item["COUNT_ALL_DS_DUPS"])) {
								$magic_item["COUNT_ALL_DS_DUPS"] .= ($count_all_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
							}

							$count_all_ds_dups++;

							/* check if this item also qualifies for NODUPS  */
							if(!isset($already_seen[$def_name])) {
								if (isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
									$magic_item["ALL_DATA_SOURCES_NODUPS"] .= ($count_all_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
								}
								if (isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
									$magic_item["COUNT_ALL_DS_NODUPS"] .= ($count_all_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
								}
								$count_all_ds_nodups++;
								$already_seen[$def_name]=TRUE;
							}

							/* check for SIMILAR data sources */
							if ($graph_item["data_source_name"] == $graph_items[$t]["data_source_name"]) {

								/* do we need SIMILAR_DATA_SOURCES_DUPS? */
								if (isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"]) && ($graph_item["data_source_name"] == $graph_items[$t]["data_source_name"])) {
									$magic_item["SIMILAR_DATA_SOURCES_DUPS"] .= ($count_similar_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
								}

								/* do we need COUNT_SIMILAR_DS_DUPS? */
								if (isset($magic_item["COUNT_SIMILAR_DS_DUPS"]) && ($graph_item["data_source_name"] == $graph_items[$t]["data_source_name"])) {
									$magic_item["COUNT_SIMILAR_DS_DUPS"] .= ($count_similar_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
								}

								$count_similar_ds_dups++;

								/* check if this item also qualifies for NODUPS  */
								if(!isset($sources_seen{$graph_items[$t]["data_template_rrd_id"]})) {
									if (isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
										$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] .= ($count_similar_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
									}
									if (isset($magic_item["COUNT_SIMILAR_DS_NODUPS"]) && ($graph_item["data_source_name"] == $graph_items[$t]["data_source_name"])) {
										$magic_item["COUNT_SIMILAR_DS_NODUPS"] .= ($count_similar_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
									}
									$count_similar_ds_nodups++;
									$sources_seen{$graph_items[$t]["data_template_rrd_id"]} = TRUE;
								}
							} # SIMILAR data sources
						} # matching consolidation function?
					} # only work on graph items, omit GRPINTs, COMMENTs and stuff
				} #  loop over all graph items

				/* if there is only one item to total, don't even bother with the summation.
				 * Otherwise cdef=a,b,c,+,+ is fine. */
				if ($count_all_ds_dups > 1 && isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
					$magic_item["ALL_DATA_SOURCES_DUPS"] .= str_repeat(",+", ($count_all_ds_dups - 2)) . ",+";
				}
				if ($count_all_ds_nodups > 1 && isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
					$magic_item["ALL_DATA_SOURCES_NODUPS"] .= str_repeat(",+", ($count_all_ds_nodups - 2)) . ",+";
				}
				if ($count_similar_ds_dups > 1 && isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"])) {
					$magic_item["SIMILAR_DATA_SOURCES_DUPS"] .= str_repeat(",+", ($count_similar_ds_dups - 2)) . ",+";
				}
				if ($count_similar_ds_nodups > 1 && isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
					$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] .= str_repeat(",+", ($count_similar_ds_nodups - 2)) . ",+";
				}
				if ($count_all_ds_dups > 1 && isset($magic_item["COUNT_ALL_DS_DUPS"])) {
					$magic_item["COUNT_ALL_DS_DUPS"] .= str_repeat(",+", ($count_all_ds_dups - 2)) . ",+";
				}
				if ($count_all_ds_nodups > 1 && isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
					$magic_item["COUNT_ALL_DS_NODUPS"] .= str_repeat(",+", ($count_all_ds_nodups - 2)) . ",+";
				}
				if ($count_similar_ds_dups > 1 && isset($magic_item["COUNT_SIMILAR_DS_DUPS"])) {
					$magic_item["COUNT_SIMILAR_DS_DUPS"] .= str_repeat(",+", ($count_similar_ds_dups - 2)) . ",+";
				}
				if ($count_similar_ds_nodups > 1 && isset($magic_item["COUNT_SIMILAR_DS_NODUPS"])) {
					$magic_item["COUNT_SIMILAR_DS_NODUPS"] .= str_repeat(",+", ($count_similar_ds_nodups - 2)) . ",+";
				}
			}

			$cdef_string = str_replace("CURRENT_DATA_SOURCE", generate_graph_def_name(strval((isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id]) ? $cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id] : "0"))), $cdef_string);

			/* ALL|SIMILAR_DATA_SOURCES(NO)?DUPS are to be replaced here */
			if (isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
				$cdef_string = str_replace("ALL_DATA_SOURCES_DUPS", $magic_item["ALL_DATA_SOURCES_DUPS"], $cdef_string);
			}
			if (isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
				$cdef_string = str_replace("ALL_DATA_SOURCES_NODUPS", $magic_item["ALL_DATA_SOURCES_NODUPS"], $cdef_string);
			}
			if (isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"])) {
				$cdef_string = str_replace("SIMILAR_DATA_SOURCES_DUPS", $magic_item["SIMILAR_DATA_SOURCES_DUPS"], $cdef_string);
			}
			if (isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
				$cdef_string = str_replace("SIMILAR_DATA_SOURCES_NODUPS", $magic_item["SIMILAR_DATA_SOURCES_NODUPS"], $cdef_string);
			}

			/* COUNT_ALL|SIMILAR_DATA_SOURCES(NO)?DUPS are to be replaced here */
			if (isset($magic_item["COUNT_ALL_DS_DUPS"])) {
				$cdef_string = str_replace("COUNT_ALL_DS_DUPS", $magic_item["COUNT_ALL_DS_DUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
				$cdef_string = str_replace("COUNT_ALL_DS_NODUPS", $magic_item["COUNT_ALL_DS_NODUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_SIMILAR_DS_DUPS"])) {
				$cdef_string = str_replace("COUNT_SIMILAR_DS_DUPS", $magic_item["COUNT_SIMILAR_DS_DUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_SIMILAR_DS_NODUPS"])) {
				$cdef_string = str_replace("COUNT_SIMILAR_DS_NODUPS", $magic_item["COUNT_SIMILAR_DS_NODUPS"], $cdef_string);
			}

			/* data source item variables */
			$cdef_string = str_replace("CURRENT_DS_MINIMUM_VALUE", (empty($graph_item["rrd_minimum"]) ? "0" : $graph_item["rrd_minimum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_DS_MAXIMUM_VALUE", (empty($graph_item["rrd_maximum"]) ? "0" : $graph_item["rrd_maximum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MINIMUM_VALUE", (empty($graph["lower_limit"]) ? "0" : $graph["lower_limit"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MAXIMUM_VALUE", (empty($graph["upper_limit"]) ? "0" : $graph["upper_limit"]), $cdef_string);
			$_time_shift_start = strtotime(read_graph_config_option("day_shift_start")) - strtotime("00:00");
			$_time_shift_end = strtotime(read_graph_config_option("day_shift_end")) - strtotime("00:00");
			$cdef_string = str_replace("TIME_SHIFT_START", (empty($_time_shift_start) ? "64800" : $_time_shift_start), $cdef_string);
			$cdef_string = str_replace("TIME_SHIFT_END", (empty($_time_shift_end) ? "28800" : $_time_shift_end), $cdef_string);

			/* replace query variables in cdefs */
			$cdef_string = rrd_substitute_device_query_data($cdef_string, $graph, $graph_item);

			/* make the initial "virtual" cdef name: 'cdef' + [a,b,c,d...] */
			$cdef_graph_defs .= "CDEF:cdef" . generate_graph_def_name(strval($i)) . "=";
			$cdef_graph_defs .= $cdef_string;
			$cdef_graph_defs .= " \\\n";

			/* the CDEF cache is so we do not create duplicate CDEF's on a graph */
			$cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id] = "$i";
		}

		/* add the cdef string to the end of the def string */
		$graph_defs .= $cdef_graph_defs;

		/* +++++++++++++++++++++++ GRAPH ITEMS: VDEF's +++++++++++++++++++++++ */
		if ($rrdtool_version != RRD_VERSION_1_0) {

			/* make vdef string here, copied from cdef stuff */
			$vdef_graph_defs = "";

			if ((!empty($graph_item["vdef_id"])) && (!isset($vdef_cache{$graph_item["vdef_id"]}{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id]))) {
				$vdef_string = $graph_variables["vdef_cache"]{$graph_item["graph_templates_item_id"]};
				if ($graph_item["cdef_id"] != "0") {
					/* "calculated" VDEF: use (cached) CDEF as base, only way to get calculations into VDEFs, lvm */
					$vdef_string = "cdef" . str_replace("CURRENT_DATA_SOURCE", generate_graph_def_name(strval(isset($cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id]) ? $cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id] : "0")), $vdef_string);
			 	} else {
					/* "pure" VDEF: use DEF as base */
					$vdef_string = str_replace("CURRENT_DATA_SOURCE", generate_graph_def_name(strval(isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id]) ? $cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id] : "0")), $vdef_string);
				}
# TODO: It would be possible to refer to a CDEF, but that's all. So ALL_DATA_SOURCES_NODUPS and stuff can't be used directly!
#				$vdef_string = str_replace("ALL_DATA_SOURCES_NODUPS", $magic_item["ALL_DATA_SOURCES_NODUPS"], $vdef_string);
#				$vdef_string = str_replace("ALL_DATA_SOURCES_DUPS", $magic_item["ALL_DATA_SOURCES_DUPS"], $vdef_string);
#				$vdef_string = str_replace("SIMILAR_DATA_SOURCES_NODUPS", $magic_item["SIMILAR_DATA_SOURCES_NODUPS"], $vdef_string);
#				$vdef_string = str_replace("SIMILAR_DATA_SOURCES_DUPS", $magic_item["SIMILAR_DATA_SOURCES_DUPS"], $vdef_string);

				/* make the initial "virtual" vdef name */
				$vdef_graph_defs .= "VDEF:vdef" . generate_graph_def_name(strval($i)) . "=";
				$vdef_graph_defs .= $vdef_string;
				$vdef_graph_defs .= " \\\n";

				/* the VDEF cache is so we do not create duplicate VDEF's on a graph,
				 * but take info account, that same VDEF may use different CDEFs
				 * so index over VDEF_ID, CDEF_ID per DATA_TEMPLATE_RRD_ID, lvm */
				$vdef_cache{$graph_item["vdef_id"]}{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id] = "$i";
			}

			/* add the cdef string to the end of the def string */
			$graph_defs .= $vdef_graph_defs;
		}

		/* note the current item_id for easy access */
		$graph_item_id = $graph_item["graph_templates_item_id"];

		/* if we are not displaying a legend there is no point in us even processing the auto padding,
		text format stuff. */
		if ((!isset($graph_data_array["graph_nolegend"])) && ($graph["auto_padding"] == CHECKED)) {
			/* only applies to AREA, STACK and LINEs */
			if ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK ||
				$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_TICK) {
				$text_format_length = strlen($graph_variables["text_format"][$graph_item_id]);

				/* we are basing how much to pad on area and stack text format,
				not gprint. but of course the padding has to be displayed in gprint,
				how fun! */

				$pad_number = ($greatest_text_format - $text_format_length);
				//cacti_log("MAX: $greatest_text_format, CURR: $text_format_lengths[$item_dsid], DSID: $item_dsid");
				$text_padding = str_pad("", $pad_number);

			/* two GPRINT's in a row screws up the padding, lets not do that */
			} else if (($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_AVERAGE ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_LAST ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MAX ||
						$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MIN) && (
						$last_graph_type == GRAPH_ITEM_TYPE_GPRINT_AVERAGE ||
						$last_graph_type == GRAPH_ITEM_TYPE_GPRINT_LAST ||
						$last_graph_type == GRAPH_ITEM_TYPE_GPRINT_MAX ||
						$last_graph_type == GRAPH_ITEM_TYPE_GPRINT_MIN)) {
				$text_padding = "";
			}

			$last_graph_type = $graph_item["graph_type_id"];
		}

		/* we put this in a variable so it can be manipulated before mainly used
		if we want to skip it, like below */
		$current_graph_item_type = $graph_item["graph_type_id"];

		/* IF this graph item has a data source... get a DEF name for it, or the cdef if that applies
		to this graph item */
		if ($graph_item["cdef_id"] == "0") {
			if (isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id])) {
				$data_source_name = generate_graph_def_name(strval($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id]));
			}else{
				$data_source_name = "";
			}
		}else{
			$data_source_name = "cdef" . generate_graph_def_name(strval($cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id]));
		}

		/* IF this graph item has a data source... get a DEF name for it, or the vdef if that applies
		to this graph item */
		if ($graph_item["vdef_id"] == "0") {
			/* do not overwrite $data_source_name that stems from cdef above */
		}else{
			$data_source_name = "vdef" . generate_graph_def_name(strval($vdef_cache{$graph_item["vdef_id"]}{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]}[$cf_id]));
		}

		/* to make things easier... if there is no text format set; set blank text */
		if (!isset($graph_variables["text_format"][$graph_item_id])) {
			$graph_variables["text_format"][$graph_item_id] = "";
		} else {
			$graph_variables["text_format"][$graph_item_id] = str_replace(':', '\:', $graph_variables["text_format"][$graph_item_id]); /* escape colons */
			$graph_variables["text_format"][$graph_item_id] = str_replace('"', '\"', $graph_variables["text_format"][$graph_item_id]); /* escape doublequotes */
		}

		if (!isset($hardreturn[$graph_item_id])) {
			$hardreturn[$graph_item_id] = "";
		}

		/* +++++++++++++++++++++++ GRAPH ITEMS +++++++++++++++++++++++ */

		/* most of the calculations have been done above. now we have for print everything out
		in an RRDTool-friendly fashion */
		$need_rrd_nl = TRUE;

		/* initialize line width support */
		if ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3) {
			if ($rrdtool_version == RRD_VERSION_1_0) {
				# round line_width to 1 <= line_width <= 3
				if ($graph_item["line_width"] < 1) {$graph_item["line_width"] = 1;}
				if ($graph_item["line_width"] > 3) {$graph_item["line_width"] = 3;}

				$graph_item["line_width"] = intval($graph_item["line_width"]);
			}
		}

		/* initialize color support */
		$graph_item_color_code = "";
		if (!empty($graph_item["hex"])) {
			$graph_item_color_code = "#" . $graph_item["hex"];
			if ($rrdtool_version != RRD_VERSION_1_0) {
				$graph_item_color_code .= $graph_item["alpha"];
			}
		}


		/* initialize dash support */
		$dash = "";
		if ($graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_HRULE ||
			$graph_item["graph_type_id"] == GRAPH_ITEM_TYPE_VRULE) {
			if ($rrdtool_version != RRD_VERSION_1_0 &&
				$rrdtool_version != RRD_VERSION_1_2) {
				if (!empty($graph_item["dashes"])) {
					$dash .= ":dashes=" . $graph_item["dashes"];
				}
				if (!empty($graph_item["dash_offset"])) {
					$dash .= ":dash-offset=" . $graph_item["dash_offset"];
				}
			}
		}


		switch($graph_item["graph_type_id"]) {
			case GRAPH_ITEM_TYPE_COMMENT:
				$comment_string = $graph_item_types{$graph_item["graph_type_id"]} . ":\"" .
						substr(rrd_substitute_device_query_data(str_replace(":", "\:", $graph_variables["text_format"][$graph_item_id]), $graph, $graph_item),0,198) .
						$hardreturn[$graph_item_id] . "\" ";
				if (trim($comment_string) == 'COMMENT:"\n"') {
					$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ':" \n"'; # rrdtool will skip a COMMENT that holds a NL only; so add a blank to make NL work
				}elseif (trim($comment_string) != "COMMENT:\"\"") {
					$txt_graph_items .= $comment_string;
				}
				break;


			case GRAPH_ITEM_TYPE_TEXTALIGN:
				if (!empty($graph_item["textalign"]) &&
					$rrdtool_version != RRD_VERSION_1_0 &&
					$rrdtool_version != RRD_VERSION_1_2) {
						$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $graph_item["textalign"];
					}
				break;


			case GRAPH_ITEM_TYPE_GPRINT_AVERAGE:
				if (!isset($graph_data_array["graph_nolegend"])) {
					/* rrdtool 1.2.x VDEFs must suppress the consolidation function on GPRINTs */
					if ($rrdtool_version != RRD_VERSION_1_0) {
						if ($graph_item["vdef_id"] == "0") {
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":AVERAGE:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}else{
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}
					}else {
						$txt_graph_items .= "GPRINT:" . $data_source_name . ":AVERAGE:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
					}
				}
				break;


			case GRAPH_ITEM_TYPE_GPRINT_LAST:
				if (!isset($graph_data_array["graph_nolegend"])) {
					/* rrdtool 1.2.x VDEFs must suppress the consolidation function on GPRINTs */
					if ($rrdtool_version != RRD_VERSION_1_0) {
						if ($graph_item["vdef_id"] == "0") {
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":LAST:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}else{
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}
					}else {
						$txt_graph_items .= "GPRINT:" . $data_source_name . ":LAST:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
					}
				}
				break;


			case GRAPH_ITEM_TYPE_GPRINT_MAX:
				if (!isset($graph_data_array["graph_nolegend"])) {
					/* rrdtool 1.2.x VDEFs must suppress the consolidation function on GPRINTs */
					if ($rrdtool_version != RRD_VERSION_1_0) {
						if ($graph_item["vdef_id"] == "0") {
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":MAX:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}else{
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}
					}else {
						$txt_graph_items .= "GPRINT:" . $data_source_name . ":MAX:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
					}
				}
				break;


			case GRAPH_ITEM_TYPE_GPRINT_MIN:
				if (!isset($graph_data_array["graph_nolegend"])) {
					/* rrdtool 1.2.x VDEFs must suppress the consolidation function on GPRINTs */
					if ($rrdtool_version != RRD_VERSION_1_0) {
						if ($graph_item["vdef_id"] == "0") {
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":MIN:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}else{
							$txt_graph_items .= "GPRINT:" . $data_source_name . ":\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
						}
					}else {
						$txt_graph_items .= "GPRINT:" . $data_source_name . ":MIN:\"$text_padding" . $graph_variables["text_format"][$graph_item_id] . $graph_item["gprint_text"] . $hardreturn[$graph_item_id] . "\" ";
					}
				}
				break;


			case GRAPH_ITEM_TYPE_AREA:
				$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $data_source_name . $graph_item_color_code . ":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
				if ($graph_item["shift"] == CHECKED && $graph_item["value"] > 0) {	# create a SHIFT statement
					$txt_graph_items .= RRD_NL . "SHIFT:" . $data_source_name . ":" . $graph_item["value"];
				}
				break;


			case GRAPH_ITEM_TYPE_AREASTACK:
				if ($rrdtool_version != RRD_VERSION_1_0) {
					$txt_graph_items .= "AREA:" . $data_source_name . $graph_item_color_code . ":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\":STACK";
				}else {
					$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $data_source_name . $graph_item_color_code . ":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\" ";
				}
				if ($graph_item["shift"] == CHECKED && $graph_item["value"] > 0) {	# create a SHIFT statement
					$txt_graph_items .= RRD_NL . "SHIFT:" . $data_source_name . ":" . $graph_item["value"];
				}
				break;


			case GRAPH_ITEM_TYPE_LINE1:
			case GRAPH_ITEM_TYPE_LINE2:
			case GRAPH_ITEM_TYPE_LINE3:
				$txt_graph_items .= "LINE" . $graph_item["line_width"] . ":" . $data_source_name . $graph_item_color_code . ":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\"" . $dash;
				if ($graph_item["shift"] == CHECKED && $graph_item["value"] > 0) {	# create a SHIFT statement
					$txt_graph_items .= RRD_NL . "SHIFT:" . $data_source_name . ":" . $graph_item["value"];
				}
				break;


			case GRAPH_ITEM_TYPE_LINESTACK:
				if ($rrdtool_version != RRD_VERSION_1_0) {
					$txt_graph_items .= "LINE" . $graph_item["line_width"] . ":" . $data_source_name . $graph_item_color_code . ":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\":STACK" . $dash;
				}
				if ($graph_item["shift"] == CHECKED && $graph_item["value"] > 0) {	# create a SHIFT statement
					$txt_graph_items .= RRD_NL . "SHIFT:" . $data_source_name . ":" . $graph_item["value"];
				}
				break;


			case GRAPH_ITEM_TYPE_TICK:
				if ($rrdtool_version != RRD_VERSION_1_0) {
					$_fraction 	= (empty($graph_item["graph_type_id"]) 						? "" : (":" . $graph_item["value"]));
					$_legend 	= (empty($graph_variables["text_format"][$graph_item_id]) 	? "" : (":" . "\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\""));
					$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $data_source_name . $graph_item_color_code . $_fraction . $_legend;
				}
				break;


			case GRAPH_ITEM_TYPE_HRULE:
				$graph_variables["value"][$graph_item_id] = str_replace(":", "\:", $graph_variables["value"][$graph_item_id]); /* escape colons */
				/* perform variable substitution; if this does not return a number, rrdtool will FAIL! */
				$substitute = rrd_substitute_device_query_data($graph_variables["value"][$graph_item_id], $graph, $graph_item);
				if (is_numeric($substitute)) {
					$graph_variables["value"][$graph_item_id] = $substitute;
				}
				$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $graph_variables["value"][$graph_item_id] . $graph_item_color_code . ":\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\"" . $dash;
				break;


			case GRAPH_ITEM_TYPE_VRULE:
				if (substr_count($graph_item["value"], ":")) {
					$value_array = explode(":", $graph_item["value"]);

					if ($value_array[0] < 0) {
						$value = date("U") - (-3600 * $value_array[0]) - 60 * $value_array[1];
					}else{
						$value = date("U", mktime($value_array[0],$value_array[1],0));
					}
				}else if (is_numeric($graph_item["value"])) {
					$value = $graph_item["value"];
				}

				$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . $value . $graph_item_color_code . ":\"" . $graph_variables["text_format"][$graph_item_id] . $hardreturn[$graph_item_id] . "\"" . $dash;
				break;


			default:
				$need_rrd_nl = FALSE;

		}

		$i++;

		if (($i < sizeof($graph_items)) && ($need_rrd_nl)) {
			$txt_graph_items .= RRD_NL;
		}
	}
	}

	$graph_array = api_plugin_hook_function('rrd_graph_graph_options', array('graph_opts' => $graph_opts, 'graph_defs' => $graph_defs, 'txt_graph_items' => $txt_graph_items, 'graph_id' => $local_graph_id, 'start' => $graph_start, 'end' => $graph_end));
	if (!empty($graph_array)) {
		$graph_defs = $graph_array['graph_defs'];
		$txt_graph_items = $graph_array['txt_graph_items'];
		$graph_opts = $graph_array['graph_opts'];
	}

	/* either print out the source or pass the source onto rrdtool to get us a nice graph */
	if (isset($graph_data_array["print_source"])) {
		# since pango markup allows for <span> tags, we need to escape this stuff using htmlspecialchars
		print htmlspecialchars(read_config_option("path_rrdtool") . " graph $graph_opts$graph_defs$txt_graph_items");
	}else{
		if (isset($graph_data_array["export"])) {
			rrdtool_execute("graph $graph_opts$graph_defs$txt_graph_items", false, RRDTOOL_OUTPUT_NULL, $rrd_struc);
			return 0;
		}else{
			$graph_data_array = api_plugin_hook_function('prep_graph_array', $graph_data_array);

			if (isset($graph_data_array["output_flag"])) {
				$output_flag = $graph_data_array["output_flag"];
			}else{
				$output_flag = RRDTOOL_OUTPUT_GRAPH_DATA;
			}
			$output = rrdtool_execute("graph $graph_opts$graph_defs$txt_graph_items", false, $output_flag, $rrd_struc);

			api_plugin_hook_function('rrdtool_function_graph_set_file', array('output' => $output, 'local_graph_id' => $local_graph_id, 'rra_id' => $rra_id));

			return $output;
		}
	}
}

function rrdtool_function_xport($local_graph_id, $rra_id, $xport_data_array, &$xport_meta, $rrd_struc = array()) {
	global $config;
	require(CACTI_BASE_PATH . "/include/presets/preset_rra_arrays.php");

	include_once(CACTI_BASE_PATH . "/lib/cdef.php");
	include_once(CACTI_BASE_PATH . "/lib/graph_variables.php");
	include_once(CACTI_BASE_PATH . "/lib/xml.php");
	include(CACTI_BASE_PATH . "/include/global_arrays.php");

	/* before we do anything; make sure the user has permission to view this graph,
	if not then get out */
	if ((read_config_option("auth_method") != 0) && (isset($_SESSION["sess_user_id"]))) {
		$access_denied = !(is_graph_allowed($local_graph_id));

		if ($access_denied == true) {
			return "GRAPH ACCESS DENIED";
		}
	}

	/* find the step and how often this graph is updated with new data */
	$ds_step = db_fetch_cell("select
		data_template_data.rrd_step
		from (data_template_data,data_template_rrd,graph_templates_item)
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and graph_templates_item.local_graph_id=$local_graph_id
		limit 0,1");
	$ds_step = empty($ds_step) ? 300 : $ds_step;

	/* if no rra was specified, we need to figure out which one RRDTool will choose using
	 * "best-fit" resolution fit algorithm */
	if (empty($rra_id)) {
		if ((empty($xport_data_array["graph_start"])) || (empty($xport_data_array["graph_end"]))) {
			$rra["rows"] = 600;
			$rra["steps"] = 1;
			$rra["timespan"] = 86400;
		}else{
			/* get a list of RRAs related to this graph */
			$rras = get_associated_rras($local_graph_id);

			if (sizeof($rras) > 0) {
				foreach ($rras as $unchosen_rra) {
					/* the timespan specified in the RRA "timespan" field may not be accurate */
					$real_timespan = ($ds_step * $unchosen_rra["steps"] * $unchosen_rra["rows"]);

					/* make sure the current start/end times fit within each RRA's timespan */
					if ( (($xport_data_array["graph_end"] - $xport_data_array["graph_start"]) <= $real_timespan) && ((time() - $xport_data_array["graph_start"]) <= $real_timespan) ) {
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

	/* override: graph start time */
	if ((!isset($xport_data_array["graph_start"])) || ($xport_data_array["graph_start"] == "0")) {
		$graph_start = -($rra["timespan"]);
	}else{
		$graph_start = $xport_data_array["graph_start"];
	}

	/* override: graph end time */
	if ((!isset($xport_data_array["graph_end"])) || ($xport_data_array["graph_end"] == "0")) {
		$graph_end = -($seconds_between_graph_updates);
	}else{
		$graph_end = $xport_data_array["graph_end"];
	}

	$graph = db_fetch_row("select
		graph_local.device_id,
		graph_local.snmp_query_id,
		graph_local.snmp_index,
		graph_templates_graph.title_cache,
		graph_templates_graph.vertical_label,
		graph_templates_graph.slope_mode,
		graph_templates_graph.auto_scale,
		graph_templates_graph.auto_scale_opts,
		graph_templates_graph.auto_scale_log,
		graph_templates_graph.scale_log_units,
		graph_templates_graph.auto_scale_rigid,
		graph_templates_graph.alt_y_grid,
		graph_templates_graph.auto_padding,
		graph_templates_graph.base_value,
		graph_templates_graph.upper_limit,
		graph_templates_graph.lower_limit,
		graph_templates_graph.height,
		graph_templates_graph.width,
		graph_templates_graph.image_format_id,
		graph_templates_graph.unit_value,
		graph_templates_graph.unit_exponent_value,
		graph_templates_graph.export
		from (graph_templates_graph,graph_local)
		where graph_local.id=graph_templates_graph.local_graph_id
		and graph_templates_graph.local_graph_id=$local_graph_id");

	/* lets make that sql query... */
	$xport_items = db_fetch_assoc("select
		graph_templates_item.id as graph_templates_item_id,
		graph_templates_item.cdef_id,
		graph_templates_item.vdef_id,
		graph_templates_item.text_format,
		graph_templates_item.value,
		graph_templates_item.hard_return,
		graph_templates_item.consolidation_function_id,
		graph_templates_item.graph_type_id,
		graph_templates_item.line_width,
		graph_templates_item.dashes,
		graph_templates_item.dash_offset,
		graph_templates_item.shift,
		graph_templates_item.textalign,
		graph_templates_gprint.gprint_text,
		colors.hex,
		graph_templates_item.alpha,
		data_template_rrd.id as data_template_rrd_id,
		data_template_rrd.local_data_id,
		data_template_rrd.rrd_minimum,
		data_template_rrd.rrd_maximum,
		data_template_rrd.data_source_name,
		data_template_rrd.local_data_template_rrd_id
		from graph_templates_item
		left join data_template_rrd on (graph_templates_item.task_item_id=data_template_rrd.id)
		left join colors on (graph_templates_item.color_id=colors.id)
		left join graph_templates_gprint on (graph_templates_item.gprint_id=graph_templates_gprint.id)
		where graph_templates_item.local_graph_id=$local_graph_id
		order by graph_templates_item.sequence");

	/* +++++++++++++++++++++++ XPORT OPTIONS +++++++++++++++++++++++ */

	/* override: graph start time */
	if ((!isset($xport_data_array["graph_start"])) || ($xport_data_array["graph_start"] == "0")) {
		$xport_start = -($rra["timespan"]);
	}else{
		$xport_start = $xport_data_array["graph_start"];
	}

	/* override: graph end time */
	if ((!isset($xport_data_array["graph_end"])) || ($xport_data_array["graph_end"] == "0")) {
		$xport_end = -($seconds_between_graph_updates);
	}else{
		$xport_end = $xport_data_array["graph_end"];
	}

	/* basic export options */
	$xport_opts =
		"--start=$xport_start" . RRD_NL .
		"--end=$xport_end" . RRD_NL .
		"--maxrows=10000" . RRD_NL;

	$xport_defs = "";

	$i = 0; $j = 0;
	$nth = 0; $sum = 0;
	if (sizeof($xport_items) > 0) {
		/* we need to add a new column "cf_reference", so unless PHP 5 is used, this foreach syntax is required */
		foreach ($xport_items as $key => $xport_item) {
			/* mimic the old behavior: LINE[123], AREA, STACK and GPRINT items use the CF specified in the graph item */
			if ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA  ||
				$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
				$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
				$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
				$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
				$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK) {
				$xport_cf = $xport_item["consolidation_function_id"];
				$last_xport_cf["data_source_name"]["local_data_template_rrd_id"] = $xport_cf;
				/* remember this for second foreach loop */
				$xport_items[$key]["cf_reference"] = $xport_cf;
			}elseif ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_AVERAGE) {
				$graph_cf = $xport_item["consolidation_function_id"];
				$xport_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_LAST) {
				$graph_cf = $xport_item["consolidation_function_id"];
				$xport_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MAX) {
				$graph_cf = $xport_item["consolidation_function_id"];
				$xport_items[$key]["cf_reference"] = $graph_cf;
			}elseif ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT_MIN) {
				$graph_cf = $xport_item["consolidation_function_id"];
				$xport_items[$key]["cf_reference"] = $graph_cf;
			#}elseif ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_GPRINT) {
				#/* ATTENTION!
				# * the "CF" given on graph_item edit screen for GPRINT is indeed NOT a real "CF",
				# * but an aggregation function
				# * see "man rrdgraph_data" for the correct VDEF based notation
				# * so our task now is to "guess" the very graph_item, this GPRINT is related to
				# * and to use that graph_item's CF */
				#if (isset($last_xport_cf["data_source_name"]["local_data_template_rrd_id"])) {
				#	$xport_cf = $xport_item["data_source_name"]["local_data_template_rrd_id"];
				#	/* remember this for second foreach loop */
				#	$xport_items[$key]["cf_reference"] = $xport_cf;
				#} else {
				#	$xport_cf = generate_graph_best_cf($xport_item["local_data_id"], $xport_item["consolidation_function_id"]);
				#	/* remember this for second foreach loop */
				#	$xport_items[$key]["cf_reference"] = $xport_cf;
				#}
			}else{
				/* all other types are based on the best matching CF */
				$xport_cf = generate_graph_best_cf($xport_item["local_data_id"], $xport_item["consolidation_function_id"]);
				/* remember this for second foreach loop */
				$xport_items[$key]["cf_reference"] = $xport_cf;
			}

			if ((!empty($xport_item["local_data_id"])) &&
				(!isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[$xport_cf]))) {
				/* use a user-specified ds path if one is entered */
				$data_source_path = get_data_source_path($xport_item["local_data_id"], true);

				/* FOR WIN32: Escape all colon for drive letters (ex. D\:/path/to/rra) */
				$data_source_path = str_replace(":", "\:", $data_source_path);

				if (!empty($data_source_path)) {
					/* NOTE: (Update) Data source DEF names are created using the graph_item_id; then passed
					to a function that matches the digits with letters. rrdtool likes letters instead
					of numbers in DEF names; especially with CDEF's. cdef's are created
					the same way, except a 'cdef' is put on the beginning of the hash */
					$xport_defs .= "DEF:" . generate_graph_def_name(strval($i)) . "=\"$data_source_path\":" . $xport_item["data_source_name"] . ":" . $consolidation_functions[$xport_cf] . RRD_NL;

					$cf_ds_cache{$xport_item["data_template_rrd_id"]}[$xport_cf] = "$i";

					$i++;
				}
			}

			/* cache cdef value here to support data query variables in the cdef string */
			if (empty($xport_item["cdef_id"])) {
				$xport_item["cdef_cache"] = "";
				$xport_items[$j]["cdef_cache"] = "";
			}else{
				$xport_item["cdef_cache"] = get_cdef($xport_item["cdef_id"]);
				$xport_items[$j]["cdef_cache"] = get_cdef($xport_item["cdef_id"]);
			}

			/* +++++++++++++++++++++++ LEGEND: TEXT SUBSTITUTION (<>'s) +++++++++++++++++++++++ */

			/* note the current item_id for easy access */
			$xport_item_id = $xport_item["graph_templates_item_id"];

			/* the following fields will be searched for graph variables */
			$variable_fields = array(
				"text_format" => array(
					"process_no_legend" => false
					),
				"value" => array(
					"process_no_legend" => true
					),
				"cdef_cache" => array(
					"process_no_legend" => true
					)
				);

			/* loop through each field that we want to substitute values for:
			currently: text format and value */
			while (list($field_name, $field_array) = each($variable_fields)) {
				/* certain fields do not require values when the legend is not to be shown */
				if (($field_array["process_no_legend"] == false) && (isset($xport_data_array["graph_nolegend"]))) {
					continue;
				}

				$xport_variables[$field_name][$xport_item_id] = $xport_item[$field_name];

				/* date/time substitution */
				if (strstr($xport_variables[$field_name][$xport_item_id], "|date_time|")) {
					$xport_variables[$field_name][$xport_item_id] = str_replace("|date_time|", date(date_time_format(), strtotime(db_fetch_cell("select value from settings where name='date'"))), $xport_variables[$field_name][$xport_item_id]);
				}

				/* data query variables */
				$xport_variables[$field_name][$xport_item_id] = rrd_substitute_device_query_data($xport_variables[$field_name][$xport_item_id], $graph, $xport_item);

				/* Nth percentile */
				if (preg_match_all("/\|([0-9]{1,2}):(bits|bytes):(\d):(current|total|max|total_peak|all_max_current|all_max_peak|aggregate_max|aggregate_sum|aggregate_current|aggregate):(\d)?\|/", $xport_variables[$field_name][$xport_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						if ($field_name == "value") {
							$xport_meta["NthPercentile"][$nth]["format"] = $match[0];
							$xport_meta["NthPercentile"][$nth]["value"]  = str_replace($match[0], variable_nth_percentile($match, $xport_item, $xport_items, $graph_start, $graph_end), $xport_variables[$field_name][$xport_item_id]);
							$nth++;
						}
					}
				}

				/* bandwidth summation */
				if (preg_match_all("/\|sum:(\d|auto):(current|total|atomic):(\d):(\d+|auto)\|/", $xport_variables[$field_name][$xport_item_id], $matches, PREG_SET_ORDER)) {
					foreach ($matches as $match) {
						if ($field_name == "text_format") {
							$xport_meta["Summation"][$sum]["format"] = $match[0];
							$xport_meta["Summation"][$sum]["value"]  = str_replace($match[0], variable_bandwidth_summation($match, $xport_item, $xport_items, $graph_start, $graph_end, $rra["steps"], $ds_step), $xport_variables[$field_name][$xport_item_id]);
							$sum++;
						}
					}
				}
			}

			$j++;
		}
	}

	/* +++++++++++++++++++++++ CDEF's +++++++++++++++++++++++ */

	$i = 0;
	$j = 1;
	if (is_array($xport_items)) {
		reset($xport_items);
	}

	$xport_item_stack_type = "";
	$txt_xport_items       = "";
	$stacked_columns       = array();

	if (sizeof($xport_items) > 0) {
	foreach ($xport_items as $xport_item) {
		/* first we need to check if there is a DEF for the current data source/cf combination. if so,
		we will use that */
		if (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}{$xport_item["consolidation_function_id"]})) {
			$cf_id = $xport_item["consolidation_function_id"];
		}else{
		/* if there is not a DEF defined for the current data source/cf combination, then we will have to
		improvise. choose the first available cf in the following order: AVERAGE, MAX, MIN, LAST */
			if (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[RRA_CF_TYPE_AVERAGE])) {
				$cf_id = RRA_CF_TYPE_AVERAGE; /* CF: AVERAGE */
			}elseif (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[RRA_CF_TYPE_MAX])) {
				$cf_id = RRA_CF_TYPE_MAX; /* CF: MAX */
			}elseif (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[RRA_CF_TYPE_MIN])) {
				$cf_id = RRA_CF_TYPE_MIN; /* CF: MIN */
			}elseif (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[RRA_CF_TYPE_LAST])) {
				$cf_id = RRA_CF_TYPE_LAST; /* CF: LAST */
			}else{
				$cf_id = RRA_CF_TYPE_AVERAGE; /* CF: AVERAGE */
			}
		}
		/* now remember the correct CF reference */
		$cf_id = $xport_item["cf_reference"];

		/* make cdef string here; a note about CDEF's in cacti. A CDEF is neither unique to a
		data source of global cdef, but is unique when those two variables combine. */
		$cdef_xport_defs = ""; $cdef_all_ds_dups = ""; $cdef_similar_ds_dups = "";
		$cdef_similar_ds_nodups = ""; $cdef_all_ds_nodups = "";

		if ((!empty($xport_item["cdef_id"])) && (!isset($cdef_cache{$xport_item["cdef_id"]}{$xport_item["data_template_rrd_id"]}[$cf_id]))) {

			$cdef_string = $xport_variables["cdef_cache"]{$xport_item["graph_templates_item_id"]};
			$magic_item 	= array();
			$already_seen	= array();
			$sources_seen	= array();
			$count_all_ds_dups = 0;
			$count_all_ds_nodups = 0;
			$count_similar_ds_dups = 0;
			$count_similar_ds_nodups = 0;

			/* if any of those magic variables are requested ... */
			if (preg_match("/(ALL_DATA_SOURCES_(NO)?DUPS|SIMILAR_DATA_SOURCES_(NO)?DUPS)/", $cdef_string) ||
				preg_match("/(COUNT_ALL_DS_(NO)?DUPS|COUNT_SIMILAR_DS_(NO)?DUPS)/", $cdef_string)) {

				/* now walk through each case to initialize array*/
				if (preg_match("/ALL_DATA_SOURCES_DUPS/", $cdef_string)) {
					$magic_item["ALL_DATA_SOURCES_DUPS"] = "";
				}
				if (preg_match("/ALL_DATA_SOURCES_NODUPS/", $cdef_string)) {
					$magic_item["ALL_DATA_SOURCES_NODUPS"] = "";
				}
				if (preg_match("/SIMILAR_DATA_SOURCES_DUPS/", $cdef_string)) {
					$magic_item["SIMILAR_DATA_SOURCES_DUPS"] = "";
				}
				if (preg_match("/SIMILAR_DATA_SOURCES_NODUPS/", $cdef_string)) {
					$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] = "";
				}
				if (preg_match("/COUNT_ALL_DS_DUPS/", $cdef_string)) {
					$magic_item["COUNT_ALL_DS_DUPS"] = "";
				}
				if (preg_match("/COUNT_ALL_DS_NODUPS/", $cdef_string)) {
					$magic_item["COUNT_ALL_DS_NODUPS"] = "";
				}
				if (preg_match("/COUNT_SIMILAR_DS_DUPS/", $cdef_string)) {
					$magic_item["COUNT_SIMILAR_DS_DUPS"] = "";
				}
				if (preg_match("/COUNT_SIMILAR_DS_NODUPS/", $cdef_string)) {
					$magic_item["COUNT_SIMILAR_DS_NODUPS"] = "";
				}

				/* loop over all graph items */
				for ($t=0;($t<count($xport_items));$t++) {

					/* only work on graph items, omit GRPINTs, COMMENTs and stuff */
					if (($xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_AREA ||
						$xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
						$xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
						$xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
						$xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
						$xport_items[$t]["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK) &&
						(!empty($xport_items[$t]["data_template_rrd_id"]))) {
						/* if the user screws up CF settings, PHP will generate warnings if left unchecked */

						/* matching consolidation function? */
						if (isset($cf_ds_cache{$xport_items[$t]["data_template_rrd_id"]}[$cf_id])) {
							$def_name = generate_graph_def_name(strval($cf_ds_cache{$xport_items[$t]["data_template_rrd_id"]}[$cf_id]));

							/* do we need ALL_DATA_SOURCES_DUPS? */
							if (isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
								$magic_item["ALL_DATA_SOURCES_DUPS"] .= ($count_all_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
							}

							/* do we need COUNT_ALL_DS_DUPS? */
							if (isset($magic_item["COUNT_ALL_DS_DUPS"])) {
								$magic_item["COUNT_ALL_DS_DUPS"] .= ($count_all_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
							}

							$count_all_ds_dups++;

							/* check if this item also qualifies for NODUPS  */
							if(!isset($already_seen[$def_name])) {
								if (isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
									$magic_item["ALL_DATA_SOURCES_NODUPS"] .= ($count_all_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
								}
								if (isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
									$magic_item["COUNT_ALL_DS_NODUPS"] .= ($count_all_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
								}
								$count_all_ds_nodups++;
								$already_seen[$def_name]=TRUE;
							}

							/* check for SIMILAR data sources */
							if ($xport_item["data_source_name"] == $xport_items[$t]["data_source_name"]) {

								/* do we need SIMILAR_DATA_SOURCES_DUPS? */
								if (isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"]) && ($xport_item["data_source_name"] == $xport_items[$t]["data_source_name"])) {
									$magic_item["SIMILAR_DATA_SOURCES_DUPS"] .= ($count_similar_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
								}

								/* do we need COUNT_SIMILAR_DS_DUPS? */
								if (isset($magic_item["COUNT_SIMILAR_DS_DUPS"]) && ($xport_item["data_source_name"] == $xport_items[$t]["data_source_name"])) {
									$magic_item["COUNT_SIMILAR_DS_DUPS"] .= ($count_similar_ds_dups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
								}

								$count_similar_ds_dups++;

								/* check if this item also qualifies for NODUPS  */
								if(!isset($sources_seen{$xport_items[$t]["data_template_rrd_id"]})) {
									if (isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
										$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] .= ($count_similar_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,$def_name,$def_name,UN,0,$def_name,IF,IF"; /* convert unknowns to '0' first */
									}
									if (isset($magic_item["COUNT_SIMILAR_DS_NODUPS"]) && ($xport_item["data_source_name"] == $xport_items[$t]["data_source_name"])) {
										$magic_item["COUNT_SIMILAR_DS_NODUPS"] .= ($count_similar_ds_nodups == 0 ? "" : ",") . "TIME," . (time() - $seconds_between_graph_updates) . ",GT,1,$def_name,UN,0,1,IF,IF"; /* convert unknowns to '0' first */
									}
									$count_similar_ds_nodups++;
									$sources_seen{$xport_items[$t]["data_template_rrd_id"]} = TRUE;
								}
							} # SIMILAR data sources
						} # matching consolidation function?
					} # only work on graph items, omit GRPINTs, COMMENTs and stuff
				} #  loop over all graph items

				/* if there is only one item to total, don't even bother with the summation.
				 * Otherwise cdef=a,b,c,+,+ is fine. */
				if ($count_all_ds_dups > 1 && isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
					$magic_item["ALL_DATA_SOURCES_DUPS"] .= str_repeat(",+", ($count_all_ds_dups - 2)) . ",+";
				}
				if ($count_all_ds_nodups > 1 && isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
					$magic_item["ALL_DATA_SOURCES_NODUPS"] .= str_repeat(",+", ($count_all_ds_nodups - 2)) . ",+";
				}
				if ($count_similar_ds_dups > 1 && isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"])) {
					$magic_item["SIMILAR_DATA_SOURCES_DUPS"] .= str_repeat(",+", ($count_similar_ds_dups - 2)) . ",+";
				}
				if ($count_similar_ds_nodups > 1 && isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
					$magic_item["SIMILAR_DATA_SOURCES_NODUPS"] .= str_repeat(",+", ($count_similar_ds_nodups - 2)) . ",+";
				}
				if ($count_all_ds_dups > 1 && isset($magic_item["COUNT_ALL_DS_DUPS"])) {
					$magic_item["COUNT_ALL_DS_DUPS"] .= str_repeat(",+", ($count_all_ds_dups - 2)) . ",+";
				}
				if ($count_all_ds_nodups > 1 && isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
					$magic_item["COUNT_ALL_DS_NODUPS"] .= str_repeat(",+", ($count_all_ds_nodups - 2)) . ",+";
				}
				if ($count_similar_ds_dups > 1 && isset($magic_item["COUNT_SIMILAR_DS_DUPS"])) {
					$magic_item["COUNT_SIMILAR_DS_DUPS"] .= str_repeat(",+", ($count_similar_ds_dups - 2)) . ",+";
				}
				if ($count_similar_ds_nodups > 1 && isset($magic_item["COUNT_SIMILAR_DS_NODUPS"])) {
					$magic_item["COUNT_SIMILAR_DS_NODUPS"] .= str_repeat(",+", ($count_similar_ds_nodups - 2)) . ",+";
				}
			}

			$cdef_string = str_replace("CURRENT_DATA_SOURCE", generate_graph_def_name(strval((isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[$cf_id]) ? $cf_ds_cache{$xport_item["data_template_rrd_id"]}[$cf_id] : "0"))), $cdef_string);

			/* ALL|SIMILAR_DATA_SOURCES(NO)?DUPS are to be replaced here */
			if (isset($magic_item["ALL_DATA_SOURCES_DUPS"])) {
				$cdef_string = str_replace("ALL_DATA_SOURCES_DUPS", $magic_item["ALL_DATA_SOURCES_DUPS"], $cdef_string);
			}
			if (isset($magic_item["ALL_DATA_SOURCES_NODUPS"])) {
				$cdef_string = str_replace("ALL_DATA_SOURCES_NODUPS", $magic_item["ALL_DATA_SOURCES_NODUPS"], $cdef_string);
			}
			if (isset($magic_item["SIMILAR_DATA_SOURCES_DUPS"])) {
				$cdef_string = str_replace("SIMILAR_DATA_SOURCES_DUPS", $magic_item["SIMILAR_DATA_SOURCES_DUPS"], $cdef_string);
			}
			if (isset($magic_item["SIMILAR_DATA_SOURCES_NODUPS"])) {
				$cdef_string = str_replace("SIMILAR_DATA_SOURCES_NODUPS", $magic_item["SIMILAR_DATA_SOURCES_NODUPS"], $cdef_string);
			}

			/* COUNT_ALL|SIMILAR_DATA_SOURCES(NO)?DUPS are to be replaced here */
			if (isset($magic_item["COUNT_ALL_DS_DUPS"])) {
				$cdef_string = str_replace("COUNT_ALL_DS_DUPS", $magic_item["COUNT_ALL_DS_DUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_ALL_DS_NODUPS"])) {
				$cdef_string = str_replace("COUNT_ALL_DS_NODUPS", $magic_item["COUNT_ALL_DS_NODUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_SIMILAR_DS_DUPS"])) {
				$cdef_string = str_replace("COUNT_SIMILAR_DS_DUPS", $magic_item["COUNT_SIMILAR_DS_DUPS"], $cdef_string);
			}
			if (isset($magic_item["COUNT_SIMILAR_DS_NODUPS"])) {
				$cdef_string = str_replace("COUNT_SIMILAR_DS_NODUPS", $magic_item["COUNT_SIMILAR_DS_NODUPS"], $cdef_string);
			}

			/* data source item variables */
			$cdef_string = str_replace("CURRENT_DS_MINIMUM_VALUE", (empty($xport_item["rrd_minimum"]) ? "0" : $xport_item["rrd_minimum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_DS_MAXIMUM_VALUE", (empty($xport_item["rrd_maximum"]) ? "0" : $xport_item["rrd_maximum"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MINIMUM_VALUE", (empty($graph["lower_limit"]) ? "0" : $graph["lower_limit"]), $cdef_string);
			$cdef_string = str_replace("CURRENT_GRAPH_MAXIMUM_VALUE", (empty($graph["upper_limit"]) ? "0" : $graph["upper_limit"]), $cdef_string);

			/* replace query variables in cdefs */
			$cdef_string = rrd_substitute_device_query_data($cdef_string, $graph, $xport_item);

			/* make the initial "virtual" cdef name: 'cdef' + [a,b,c,d...] */
			$cdef_xport_defs .= "CDEF:cdef" . generate_graph_def_name(strval($i)) . "=";
			$cdef_xport_defs .= $cdef_string;
			$cdef_xport_defs .= " \\\n";

			/* the CDEF cache is so we do not create duplicate CDEF's on a graph */
			$cdef_cache{$xport_item["cdef_id"]}{$xport_item["data_template_rrd_id"]}[$cf_id] = "$i";
		}

		/* add the cdef string to the end of the def string */
		$xport_defs .= $cdef_xport_defs;

		/* note the current item_id for easy access */
		$xport_item_id = $xport_item["graph_templates_item_id"];

		/* IF this graph item has a data source... get a DEF name for it, or the cdef if that applies
		to this graph item */
		if ($xport_item["cdef_id"] == "0") {
			if (isset($cf_ds_cache{$xport_item["data_template_rrd_id"]}[$cf_id])) {
				$data_source_name = generate_graph_def_name(strval($cf_ds_cache{$xport_item["data_template_rrd_id"]}[$cf_id]));
			}else{
				$data_source_name = "";
			}
		}else{
			$data_source_name = "cdef" . generate_graph_def_name(strval($cdef_cache{$xport_item["cdef_id"]}{$xport_item["data_template_rrd_id"]}[$cf_id]));
		}

		/* +++++++++++++++++++++++ XPORT ITEMS +++++++++++++++++++++++ */

		$need_rrd_nl = TRUE;
		if ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREA ||
			$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK ||
			$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE1 ||
			$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE2 ||
			$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINE3 ||
			$xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK) {
			/* give all export items a name */
			if (trim($xport_variables["text_format"][$xport_item_id]) == "") {
				$legend_name = "col" . $j . "-" . $data_source_name;
			}else{
				$legend_name = $xport_variables["text_format"][$xport_item_id];
			}
			$stacked_columns["col" . $j] = ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_AREASTACK) ? 1 : 0;
			$stacked_columns["col" . $j] = ($xport_item["graph_type_id"] == GRAPH_ITEM_TYPE_LINESTACK) ? 1 : 0;
			$j++;

			$txt_xport_items .= "XPORT:" . $data_source_name . ":" . "\"" . str_replace(":", "", $legend_name) . "\"";
		}else{
			$need_rrd_nl = FALSE;
		}

		$i++;

		if (($i < sizeof($xport_items)) && ($need_rrd_nl)) {
			$txt_xport_items .= RRD_NL;
		}
	}
	}

	$output_flag = RRDTOOL_OUTPUT_STDOUT;

	$xport_array = rrdxport2array(rrdtool_execute("xport $xport_opts$xport_defs$txt_xport_items", false, $output_flag, $rrd_struc));

	/* add device and graph information */
	$xport_array["meta"]["stacked_columns"]= $stacked_columns;
	$xport_array["meta"]["title_cache"]    = $graph["title_cache"];
	$xport_array["meta"]["vertical_label"] = $graph["vertical_label"];
	$xport_array["meta"]["local_graph_id"] = $local_graph_id;
	$xport_array["meta"]["device_id"]        = $graph["device_id"];

	return $xport_array;
}


/* rrdtool_set_font		- set the rrdtool font option
 * @param $type			- the type of font: DEFAULT, TITLE, AXIS, UNIT, LEGEND, WATERMARK
 * @param $no_legend		- special handling for TITLE if legend is suppressed
 * returns				- rrdtool --font option for the given font type
 */
function rrdtool_set_font($type, $no_legend = "") {
	global $config;
	if (read_graph_config_option("custom_fonts") == CHECKED) {
		$font = read_graph_config_option($type . "_font");
		$size = read_graph_config_option($type . "_size");
	}else{
		$font = read_config_option($type . "_font");
		$size = read_config_option($type . "_size");
	}

	/* do some simple checks */
	if (read_config_option("rrdtool_version") == RRD_VERSION_1_0 ||
		read_config_option("rrdtool_version") == RRD_VERSION_1_2) { # rrdtool 1.0 and 1.2 use font files
		if (!file_exists($font)) {
			$font = "";
		}
	} else {	# rrdtool 1.3+ uses fontconfig
		$font = '"' . $font . '"';
		$out_array = array();
		exec('fc-list ' . $font, $out_array);
		if (sizeof($out_array) == 0) {
			$font = "";
		}
	}

	if ($type == "title") {
		if (!empty($no_legend)) {
			$size = $size * .70;
		}elseif (($size <= 4) || ($size == "")) {
			$size = 12;
		}
	}else if (($size <= 4) || ($size == "")) {
		$size = 8;
	}

	return "--font " . strtoupper($type) . ":" . $size . ":" . $font . RRD_NL;
}


function rrdtool_set_colortag($type, $colortag) {
	global $config;

	$tag = "";
	$sequence = read_config_option("colortag_sequence");

	switch ($sequence) {
		case COLORTAGS_GLOBAL:
			$colortag = read_config_option("colortag_" . $type);
			if (!empty($colortag)) {$tag = $colortag;}
			break;

		case COLORTAGS_USER:
			$colortag = read_graph_config_option("colortag_" . $type);
			if (!empty($colortag)) {$tag = $colortag;}
			break;

		case COLORTAGS_TEMPLATE:
			if (!empty($colortag)) {$tag = $colortag;}
			break;

		case COLORTAGS_UTG:
			if (read_graph_config_option("custom_colortags") == CHECKED) {		# user tag "for all graphs" comes first
				$colortag = read_graph_config_option("colortag_" . $type);
				if (!empty($colortag)) {$tag = $colortag;}
			}
			if (empty($tag) && !empty($colortag)) {								# graph specific tag comes next
				$tag = $colortag;
			}
			if (empty($tag)) {													# global tag is least priority
				$colortag = read_config_option("colortag_" . $type);
				if (!empty($colortag)) {$tag = $colortag;}
			}
			break;

		case COLORTAGS_TUG:
			if (empty($tag) && !empty($colortag)) {								# graph specific tag comes first
				$tag = $colortag;
			}
			if (read_graph_config_option("custom_colortags") == CHECKED) {		# user tag "for all graphs" comes next
				$colortag = read_graph_config_option("colortag_" . $type);
				if (!empty($colortag)) {$tag = $colortag;}
			}
			if (empty($tag)) {													# global tag is least priority
				$colortag = read_config_option("colortag_" . $type);
				if (!empty($colortag)) {$tag = $colortag;}
			}
			break;
	}

	if (!empty($tag)) {
		return "--color " . $type . "#" . $tag . RRD_NL;
	} else {
		return "";
	}
}

function rrdtool_set_x_grid($xaxis_id, $start, $end) {

	$format = "";
	$xaxis_items = db_fetch_assoc("SELECT timespan, gtm, gst, mtm, mst, ltm, lst, lpr, lfm " .
					"FROM graph_templates_xaxis_items WHERE xaxis_id=" . $xaxis_id .
					" AND timespan > " . ($end - $start) .
					" ORDER BY timespan ASC LIMIT 1");
	# find best matching timestamp
	if (sizeof($xaxis_items)) {
		foreach ($xaxis_items as $xaxis_item) { # there's only one matching entry due to LIMIT 1
			$format .= $xaxis_item["gtm"] . ":";
			$format .= $xaxis_item["gst"] . ":";
			$format .= $xaxis_item["mtm"] . ":";
			$format .= $xaxis_item["mst"] . ":";
			$format .= $xaxis_item["ltm"] . ":";
			$format .= $xaxis_item["lst"] . ":";
			$format .= $xaxis_item["lpr"] . ":";
			$format .= $xaxis_item["lfm"];
		}
	}

	if (!empty($format)) {
		$format = "--x-grid \"" . $format . "\"" . RRD_NL;
	}

	return $format;
}

/* rrd_substitute_device_query_data substitute |device*| and |query*| type variables
 * @param $txt_graph_item 	the variable to be substituted
 * @param $graph				from table graph_templates_graph
 * @param $graph_item			from table graph.templates_item
 * returns					variable substituted by value
 */
function rrd_substitute_device_query_data($txt_graph_item, $graph, $graph_item) {
	/* replace device variables in graph elements */
	$txt_graph_item = substitute_device_data($txt_graph_item, '|','|', $graph["device_id"], true);

	/* replace query variables in graph elements */
	if (preg_match("/\|query_[a-zA-Z0-9_]+\|/", $txt_graph_item)) {
		/* default to the graph data query information from the graph */
		if (empty($graph_item["local_data_id"])) {
			return substitute_snmp_query_data($txt_graph_item, $graph["device_id"], $graph["snmp_query_id"], $graph["snmp_index"]);
		/* use the data query information from the data source if possible */
		}else{
			$data_local = db_fetch_row("select snmp_index,snmp_query_id,device_id from data_local where id='" . $graph_item["local_data_id"] . "'");
			return substitute_snmp_query_data($txt_graph_item, $data_local["device_id"], $data_local["snmp_query_id"], $data_local["snmp_index"]);
		}
	}else{
		return $txt_graph_item;
	}
}

/* rrdgraph_scale		compute scaling parameters for rrd graphs
 * @param $graph			graph options
 * returns				graph options prepared for use with rrdtool graph
 */
function rrdgraph_scale($graph) {

	$scale = "";

	/* do query_ substitions for upper and lower limit */
	if (isset($graph["lower_limit"])) {
		$graph["lower_limit"] = rrd_substitute_device_query_data($graph["lower_limit"], $graph, null);
	}
	if (isset($graph["upper_limit"])) {
		$graph["upper_limit"] = rrd_substitute_device_query_data($graph["upper_limit"], $graph, null);
	}

	if ($graph["auto_scale"] == CHECKED) {
		switch ($graph["auto_scale_opts"]) {
			case GRAPH_ALT_AUTOSCALE: /* autoscale ignores lower, upper limit */
				$scale = "--alt-autoscale" . RRD_NL;
				break;
			case GRAPH_ALT_AUTOSCALE_MIN: /* autoscale-max, accepts a given lower limit */
				$scale = "--alt-autoscale-max" . RRD_NL;
				if ( is_numeric($graph["lower_limit"])) {
					$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
				}
				break;
			case GRAPH_ALT_AUTOSCALE_MAX: /* autoscale-min, accepts a given upper limit */
				if (read_config_option("rrdtool_version") != RRD_VERSION_1_0) {
					$scale = "--alt-autoscale-min" . RRD_NL;
					if ( is_numeric($graph["upper_limit"])) {
						$scale .= "--upper-limit=" . $graph["upper_limit"] . RRD_NL;
					}
				}
				break;
			case GRAPH_ALT_AUTOSCALE_LIMITS: /* auto_scale with limits */
				$scale = "--alt-autoscale" . RRD_NL;
				if ( is_numeric($graph["upper_limit"])) {
					$scale .= "--upper-limit=" . $graph["upper_limit"] . RRD_NL;
				}
				if ( is_numeric($graph["lower_limit"])) {
					$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
				}
				break;
		}
	}else{
		if ( is_numeric($graph["upper_limit"])) {
			$scale .= "--upper-limit=" . $graph["upper_limit"] . RRD_NL;
		}
		if ( is_numeric($graph["lower_limit"])) {
			$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
		}
	}

	if ($graph["auto_scale_log"] == CHECKED) {
		$scale .= "--logarithmic" . RRD_NL;
	}

	/* --units=si only defined for logarithmic y-axis scaling, even if it doesn't hurt on linear graphs */
	if (($graph["scale_log_units"] == CHECKED) &&
		($graph["auto_scale_log"] == CHECKED)) {
		$scale .= "--units=si" . RRD_NL;
	}

	if ($graph["auto_scale_rigid"] == CHECKED) {
		$scale .= "--rigid" . RRD_NL;
	}

	return $scale;
}

/* rrdgraph_image_format		determine image format for rrdtool graph statement
 * @param $image_format_id		the id of the wanted image format
 * @param $rrdtool_version		rrdtool version used for checks
 * returns						--imgformat string
 */
function rrdgraph_image_format($image_format_id, $rrdtool_version) {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");

	$format = "--imgformat=";

	switch($rrdtool_version) {
		case RRD_VERSION_1_0:
			if ($image_format_id == IMAGE_TYPE_PNG || $image_format_id == IMAGE_TYPE_GIF) {
				$format .= $image_types{$image_format_id};
			} else {
				$format .= $image_types{IMAGE_TYPE_PNG};
			}
			break;

		case RRD_VERSION_1_2:
			if ($image_format_id == IMAGE_TYPE_PNG || $image_format_id == IMAGE_TYPE_SVG) {
				$format .= $image_types{$image_format_id};
			} else {
				$format .= $image_types{IMAGE_TYPE_PNG};
			}
			break;

		case RRD_VERSION_1_3:
			if ($image_format_id == IMAGE_TYPE_PNG || $image_format_id == IMAGE_TYPE_SVG) {
				$format .= $image_types{$image_format_id};
			} else {
				$format .= $image_types{IMAGE_TYPE_PNG};
			}
			break;

		case RRD_VERSION_1_4:
			if ($image_format_id == IMAGE_TYPE_PNG || $image_format_id == IMAGE_TYPE_SVG) {
				$format .= $image_types{$image_format_id};
			} else {
				$format .= $image_types{IMAGE_TYPE_PNG};
			}
			break;

		default:
			$format .= $image_types{IMAGE_TYPE_PNG};
			break;

	}

	$format .= RRD_NL;
	return $format;
}

/* rrdgraph_start_end		computes start and end timestamps in unixtime format
 * @param $graph_data_array	override parameters for start, end, e.g. for zooming
 * @param $rra				rra parameters used for this graph
 * @param $seconds_between_graph_updates
 * returns					array of start, end time
 */
function rrdgraph_start_end($graph_data_array, $rra, $seconds_between_graph_updates) {

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

	return array($graph_start, $graph_end);

}


function rrdgraph_opts($graph, $graph_data_array, $version) {

	$option = "";

	foreach ($graph as $key => $value) {
		#cacti_log("Parameter: " . $key . " value: " . $value . " RRDTool: " . $version, true, "TEST");
		switch ($key) {
			case "title_cache":
				if (!empty($value)) {
					$option .= "--title=\"" . str_replace("\"", "\\\"", $value) . "\"" . RRD_NL;
				}
				break;

			case "alt_y_grid":
				if ($value == CHECKED) 	{$option .= "--alt-y-grid" . RRD_NL;}
				break;

			case "unit_value":
				if (!empty($value)) {
					$option .= "--y-grid=" . $value . RRD_NL;
				}
				break;

			case "unit_exponent_value":
				if (preg_match("/^[0-9]+$/", $value)) {
					$option .= "--units-exponent=" . $value . RRD_NL;
				}
				break;

			case "height":
				/* override: graph height (in pixels) */
				if (isset($graph_data_array["graph_height"]) && preg_match("/^[0-9]+$/", $graph_data_array["graph_height"])) {
					$option .= "--height=" . $graph_data_array["graph_height"] . RRD_NL;
				}else{
					$option .= "--height=" . $value . RRD_NL;
				}
				break;

			case "width":
				/* override: graph width (in pixels) */
				if (isset($graph_data_array["graph_width"]) && preg_match("/^[0-9]+$/", $graph_data_array["graph_width"])) {
					$option .= "--width=" . $graph_data_array["graph_width"] . RRD_NL;
				}else{
					$option .= "--width=" . $value . RRD_NL;
				}
				break;

			case "graph_nolegend":
				/* override: skip drawing the legend? */
				if (isset($graph_data_array["graph_nolegend"])) {
					$option .= "--no-legend" . RRD_NL;
				}else{
					$option .= "";
				}
				break;

			case "base_value":
				if ($value == 1000 || $value == 1024) {
					$option .= "--base=" . $value . RRD_NL;
				}
				break;

			case "vertical_label":
				if (!empty($value)) {
					$option .= "--vertical-label=\"" . $value . "\"" . RRD_NL;
				}
				break;

			case "slope_mode":
				/* rrdtool 1.2.x, 1.3.x does not provide smooth lines, let's force it */
				if ($version != RRD_VERSION_1_0) {
					if ($value == CHECKED) {
						$option .= "--slope-mode" . RRD_NL;
					}
				}
				break;

			case "right_axis":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if (!empty($value)) {
						$option .= "--right-axis " . $value . RRD_NL;
					}
				}
				break;

			case "right_axis_label":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if (!empty($value)) {
						$option .= "--right-axis-label \"" . $value . "\"" . RRD_NL;
					}
				}
				break;

			case "right_axis_format":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if (!empty($value)) {
						$format = db_fetch_cell('SELECT gprint_text from graph_templates_gprint WHERE id=' . $value);
						$option .= "--right-axis-format \"" . $format . "\"" . RRD_NL;
					}
				}
				break;

			case "only_graph":
				if ($value == CHECKED) {
					$option .= "--only-graph" . RRD_NL;
				}
				break;

			case "full_size_mode":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if ($value == CHECKED) {
						$option .= "--full-size-mode" . RRD_NL;
					}
				}
				break;

			case "no_gridfit":
				if ($version != RRD_VERSION_1_0) {
					if ($value == CHECKED) {
						$option .= "--no-gridfit" . RRD_NL;
					}
				}
				break;

			case "x_grid":
				if (!empty($value)) {
					$option .= rrdtool_set_x_grid($value, $graph["graph_start"], $graph["graph_end"]);
				}
				break;

			case "unit_length":
				if (!empty($value)) {
					$option .= "--units-length " . $value . RRD_NL;
				}
				break;

			case "font_render_mode":
				if ($version != RRD_VERSION_1_0) {
					if (!empty($value)) {
						$option .= "--font-render-mode " . $value . RRD_NL;
					}
				}
				break;

			case "font_smoothing_threshold":
				if ($version != RRD_VERSION_1_0) {
					if (!empty($value)) {
						$option .= "--font-smoothing-threshold " . $value . RRD_NL;
					}
				}
				break;

			case "graph_render_mode":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if (!empty($value)) {
						$option .= "--graph-render-mode " . $value . RRD_NL;
					}
				}
				break;

			case "pango_markup":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
					if (!empty($value)) {
						$option .= "--pango-markup" . RRD_NL;
					}
				}
				break;

			case "interlaced":
				if ($value == CHECKED) {
					$option .= "--interlaced" . RRD_NL;
				}
				break;

			case "tab_width":
				if ($version != RRD_VERSION_1_0) {
					if (!empty($value)) {
						$option .= "--tabwidth " . $value . RRD_NL;
					}
				}
				break;

			case "watermark":
				if ($version != RRD_VERSION_1_0) {
					if (!empty($value)) {
						$option .= "--watermark \"" . $value . "\"" . RRD_NL;
					}
				}
				break;

			case "force_rules_legend":
				if ($value == CHECKED) {
					$option .= "--force-rules-legend" . RRD_NL;
				}
				break;

			case "legend_position":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2 && $version != RRD_VERSION_1_3) {
					if (!empty($value)) {
						$option .= "--legend-position " . $value . RRD_NL;
					}
				}
				break;

			case "legend_direction":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2 && $version != RRD_VERSION_1_3) {
					if (!empty($value)) {
						$option .= "--legend-direction " . $value . RRD_NL;
					}
				}
				break;

			case "grid_dash":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2 && $version != RRD_VERSION_1_3) {
					if (!empty($value)) {
						$option .= "--grid-dash " . $value . RRD_NL;
					}
				}
				break;

			case "border":
				if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2 && $version != RRD_VERSION_1_3) {
					if (preg_match("/^[0-9]+$/", $value)) { # stored as string, do not use ===; border=0 is valid but != empty border!
						$option .= "--border " . $value . RRD_NL;
					}
				}
				break;

		}
	}



	/* rrdtool 1.2.x++ font options */
	if ($version != RRD_VERSION_1_0) {
		/* title fonts */
		$option .= rrdtool_set_font("title", ((!empty($graph_data_array["graph_nolegend"])) ? $graph_data_array["graph_nolegend"] : ""));

		/* axis fonts */
		$option .= rrdtool_set_font("axis");

		/* legend fonts */
		$option .= rrdtool_set_font("legend");

		/* unit fonts */
		$option .= rrdtool_set_font("unit");
	}

	/* rrdtool 1.3.x++ colortag options */
	if ($version != RRD_VERSION_1_0 && $version != RRD_VERSION_1_2) {
		/* title fonts */
		$option .= rrdtool_set_colortag("BACK", $graph["colortag_back"]);
		$option .= rrdtool_set_colortag("CANVAS", $graph["colortag_canvas"]);
		$option .= rrdtool_set_colortag("SHADEA", $graph["colortag_shadea"]);
		$option .= rrdtool_set_colortag("SHADEB", $graph["colortag_shadeb"]);
		$option .= rrdtool_set_colortag("GRID", $graph["colortag_grid"]);
		$option .= rrdtool_set_colortag("MGRID", $graph["colortag_mgrid"]);
		$option .= rrdtool_set_colortag("FONT", $graph["colortag_font"]);
		$option .= rrdtool_set_colortag("AXIS", $graph["colortag_axis"]);
		$option .= rrdtool_set_colortag("FRAME", $graph["colortag_frame"]);
		$option .= rrdtool_set_colortag("ARROW", $graph["colortag_arrow"]);
	}


	return $option;
}


function rrdgraph_item_opts($graph_item, $graph_data_array, $hardreturn, $graph_variables, $version) {

	$option = "";



	return $option;
}

/* rrdtool_cacti_compare 	compares cacti information to rrd file information
 * @param $data_source_id		the id of the data source
 * @param $info				rrdtool info as an array
 * returns					array build like $info defining html class in case of error
 */
function rrdtool_cacti_compare($data_source_id, &$info) {
	require(CACTI_BASE_PATH . "/include/presets/preset_rra_arrays.php");
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	/* get cacti header information for given data source id */
	$cacti_header_array = db_fetch_row("SELECT " .
										"local_data_template_data_id, " .
										"rrd_step " .
									"FROM " .
										"data_template_data " .
									"WHERE " .
										"local_data_id=$data_source_id");

	$cacti_file = get_data_source_path($data_source_id, true);

	/* get cacti DS information */
	$cacti_ds_array = db_fetch_assoc("SELECT " .
									"data_source_name, " .
									"data_source_type_id, " .
									"rrd_heartbeat, " .
									"rrd_maximum, " .
									"rrd_minimum " .
								"FROM " .
									"data_template_rrd " .
								"WHERE " .
									"local_data_id = $data_source_id");

	/* get cacti RRA information */
	$cacti_rra_array = db_fetch_assoc("SELECT " .
									"rra_cf.consolidation_function_id AS cf, " .
									"rra.x_files_factor AS xff, " .
									"rra.steps AS steps, " .
									"rra.rows AS rows " .
								"FROM " .
									"rra, " .
									"rra_cf, " .
									"data_template_data_rra " .
								"WHERE " .
									"data_template_data_rra.data_template_data_id = " . $cacti_header_array["local_data_template_data_id"] .	" AND " .
									"data_template_data_rra.rra_id = rra.id AND " .
									"rra_cf.rra_id = rra.id " .
								"ORDER BY " .
									"rra_cf.consolidation_function_id, " .
									"rra.steps");


	$diff = array();
	/* -----------------------------------------------------------------------------------
	 * header information
	 -----------------------------------------------------------------------------------*/
	if ($cacti_header_array["rrd_step"] != $info["step"]) {
		$diff["step"] = __("required rrd step size is '%s'", $cacti_header_array["rrd_step"]);
	}

	/* -----------------------------------------------------------------------------------
	 * data source information
	 -----------------------------------------------------------------------------------*/
	if (sizeof($cacti_ds_array) > 0) {
		foreach ($cacti_ds_array as $key => $data_source) {
			$ds_name = $data_source["data_source_name"];

			/* try to print matching rrd file's ds information */
			if (isset($info["ds"][$ds_name]) ) {
				if (!isset($info["ds"][$ds_name]["seen"])) {
					$info["ds"][$ds_name]["seen"] = TRUE;
				} else {
					continue;
				}

				$ds_type = trim($info["ds"][$ds_name]["type"], '"');
				if ($data_source_types[$data_source["data_source_type_id"]] != $ds_type) {
					$diff["ds"][$ds_name]["type"] = __("type for data source '%s' should be '%s'", $ds_name, $data_source_types[$data_source["data_source_type_id"]]);
					$diff["tune"][] = $info["filename"] . " " . "--data-source-type " . $ds_name . ":" . $data_source_types[$data_source["data_source_type_id"]];
				}

				if ($data_source["rrd_heartbeat"] != $info["ds"][$ds_name]["minimal_heartbeat"]) {
					$diff["ds"][$ds_name]["minimal_heartbeat"] = __("heartbeat for data source '%s' should be '%s'", $ds_name, $data_source["rrd_heartbeat"]);
					$diff["tune"][] = $info["filename"] . " " . "--heartbeat " . $ds_name . ":" . $data_source["rrd_heartbeat"];
				}

				if ($data_source["rrd_minimum"] != $info["ds"][$ds_name]["min"]) {
					$diff["ds"][$ds_name]["min"] = __("rrd minimum for data source '%s' should be '%s'", $ds_name, $data_source["rrd_minimum"]);
					$diff["tune"][] = $info["filename"] . " " . "--maximum " . $ds_name . ":" . $data_source["rrd_minimum"];
				}

				if ($data_source["rrd_maximum"] != $info["ds"][$ds_name]["max"]) {
					$diff["ds"][$ds_name]["max"] = __("rrd maximum for data source '%s' should be '%s'", $ds_name, $data_source["rrd_maximum"]);
					$diff["tune"][] = $info["filename"] . " " . "--minimum " . $ds_name . ":" . $data_source["rrd_maximum"];
				}
			} else {
				# cacti knows this ds, but the rrd file does not
				$info["ds"][$ds_name]["type"] = $data_source_types[$data_source["data_source_type_id"]];
				$info["ds"][$ds_name]["minimal_heartbeat"] = $data_source["rrd_heartbeat"];
				$info["ds"][$ds_name]["min"] = $data_source["rrd_minimum"];
				$info["ds"][$ds_name]["max"] = $data_source["rrd_maximum"];
				$info["ds"][$ds_name]["seen"] = TRUE;
				$diff["ds"][$ds_name]["error"] = __("DS '%s' missing in rrd file", $ds_name);
			}
		}
	}
	/* print all data sources still known to the rrd file (no match to cacti ds will happen here) */
	if (sizeof($info["ds"]) > 0) {
		foreach ($info["ds"] as $ds_name => $data_source) {
			if (!isset($data_source["seen"])) {
				$diff["ds"][$ds_name]["error"] = __("DS '%s' missing in cacti definition", $ds_name);
			}
		}
	}


	/* -----------------------------------------------------------------------------------
	 * RRA information
	 -----------------------------------------------------------------------------------*/
	$resize = TRUE;		# assume a resize operation as long as no rra duplicates are found
	# scan cacti rra information for duplicates of (CF, STEPS)
	if (sizeof($cacti_rra_array) > 0) {
		for ($i=0; $i<= sizeof($cacti_rra_array)-1; $i++) {
			$cf = $cacti_rra_array{$i}["cf"];
			$steps = $cacti_rra_array{$i}["steps"];
			foreach($cacti_rra_array as $cacti_rra_id => $cacti_rra) {
				if ($cf == $cacti_rra["cf"] && $steps == $cacti_rra["steps"] && ($i != $cacti_rra_id)) {
					$diff['rra'][$i]["error"] = __("Cacti RRA '%s' has same cf/steps (%s, %s) as '%s'", $i, $consolidation_functions{$cf}, $steps, $cacti_rra_id);
					$diff['rra'][$cacti_rra_id]["error"] = __("Cacti RRA '%s' has same cf/steps (%s, %s) as '%s'", $cacti_rra_id, $consolidation_functions{$cf}, $steps, $i);
					$resize = FALSE;
				}
			}
		}
	}
	# scan file rra information for duplicates of (CF, PDP_PER_ROWS)
	if (sizeof($info['rra']) > 0) {
		for ($i=0; $i<= sizeof($info['rra'])-1; $i++) {
			$cf = $info['rra']{$i}["cf"];
			$steps = $info['rra']{$i}["pdp_per_row"];
			foreach($info['rra'] as $file_rra_id => $file_rra) {
				if (($cf == $file_rra["cf"]) && ($steps == $file_rra["pdp_per_row"]) && ($i != $file_rra_id)) {
					$diff['rra'][$i]["error"] = __("File RRA '%s' has same cf/steps (%s, %s) as '%s'", $i, $cf, $steps, $file_rra_id);
					$diff['rra'][$file_rra_id]["error"] = __("File RRA '%s' has same cf/steps (%s, %s) as '%s'", $file_rra_id, $cf, $steps, $i);
					$resize = FALSE;
				}
			}
		}
	}

	/* print all RRAs known to cacti and add those from matching rrd file */
	if (sizeof($cacti_rra_array) > 0) {
		foreach($cacti_rra_array as $cacti_rra_id => $cacti_rra) {
			/* find matching rra info from rrd file
			 * do NOT assume, that rra sequence is kept ($cacti_rra_id != $file_rra_id may happen)!
			 * Match is assumed, if CF and STEPS/PDP_PER_ROW match; so go for it */
			foreach ($info['rra'] as $file_rra_id => $file_rra) {

				if ($consolidation_functions{$cacti_rra["cf"]} == trim($file_rra["cf"], '"') &&
					$cacti_rra["steps"] == $file_rra["pdp_per_row"]) {

					if (!isset($info['rra'][$file_rra_id]["seen"])) {
						# mark both rra id's as seen to avoid printing them as non-matching
						$info['rra'][$file_rra_id]["seen"] = TRUE;
						$cacti_rra_array[$cacti_rra_id]["seen"] = TRUE;
					} else {
						continue;
					}

					if ($cacti_rra["xff"] != $file_rra["xff"]) {
						$diff['rra'][$file_rra_id]["xff"] = __("xff for cacti rra id '%s' should be '%s'", $cacti_rra_id, $cacti_rra["xff"]);
					}

					if ($cacti_rra["rows"] != $file_rra["rows"] && $resize) {
						$diff['rra'][$file_rra_id]["rows"] = __("number of rows for cacti rra id '%s' should be '%s'", $cacti_rra_id, $cacti_rra["rows"]);
						if ($cacti_rra["rows"] > $file_rra["rows"]) {
							$diff["resize"][] = $info["filename"] . " " . $cacti_rra_id . " GROW " . ($cacti_rra["rows"] - $file_rra["rows"]);
						} else {
							$diff["resize"][] = $info["filename"] . " " . $cacti_rra_id . " SHRINK " . ($file_rra["rows"] - $cacti_rra["rows"]);
						}
					}
				}
			}
			# if cacti knows an rra that has no match, consider this as an error
			if (!isset($cacti_rra_array[$cacti_rra_id]["seen"])) {
				# add to info array for printing, the index $cacti_rra_id has no real meaning
				$info['rra']["cacti_" . $cacti_rra_id]["cf"] = $consolidation_functions{$cacti_rra["cf"]};
				$info['rra']["cacti_" . $cacti_rra_id]["steps"] = $cacti_rra["steps"];
				$info['rra']["cacti_" . $cacti_rra_id]["xff"] = $cacti_rra["xff"];
				$info['rra']["cacti_" . $cacti_rra_id]["rows"] = $cacti_rra["rows"];
				$diff['rra']["cacti_" . $cacti_rra_id]["error"] = __("RRA '%s' missing in rrd file", $cacti_rra_id);
			}
		}
	}

	# if the rrd file has an rra that has no cacti match, consider this as an error
	if (sizeof($info['rra']) > 0) {
		foreach ($info['rra'] as $file_rra_id => $file_rra) {
			if (!isset($info['rra'][$file_rra_id]["seen"])) {
				$diff['rra'][$file_rra_id]["error"] = __("RRA '%s' missing in cacti definition", $file_rra_id);
			}
		}
	}

	return $diff;

}

/* rrdtool_tune			- create rrdtool tune/resize commands
 * 						  html+cli enabled
 * @param $rrd_file		- rrd file name
 * @param $diff			- array of discrepancies between cacti setttings and rrd file info
 * @param $show_source	- only show text+commands or execute all commands, execute is for cli mode only!
 */
function rrdtool_tune($rrd_file, $diff, $show_source=TRUE) {

	function print_leaves($array, $nl) {
		foreach ($array as $key => $line) {
			if (!is_array($line)) {
				print $line . $nl;
			} else {
				if ($key === "tune") continue;
				if ($key === "resize") continue;
				print_leaves($line, $nl);
			}
		}

	}


	$cmd = array();
	# for html/cli mode
	if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
		$nl = "<br/>";
	} else {
		$nl = "\n";
	}

	if ($show_source && sizeof($diff)) {
		# print error descriptions
		print_leaves($diff, $nl);
	}

	if (isset($diff["tune"]) && sizeof($diff["tune"])) {
		# create tune commands
		foreach ($diff["tune"] as $line) {
			if ($show_source == true) {
				print read_config_option("path_rrdtool") . " tune " . $line . $nl;
			}else{
				rrdtool_execute("tune $line", true, RRDTOOL_OUTPUT_STDOUT, array(), "POLLER");
			}
		}
	}

	if (isset($diff["resize"]) && sizeof($diff["resize"])) {
		# each resize goes into an extra line
		foreach ($diff["resize"] as $line) {
			if ($show_source == true) {
				print read_config_option("path_rrdtool") . " resize " . $line . $nl;
				print __("rename %s to %s", dirname($rrd_file) . "/resize.rrd", $rrd_file) . $nl;
			}else{
				rrdtool_execute("resize $line", true, RRDTOOL_OUTPUT_STDOUT, array(), "POLLER");
				rename(dirname($rrd_file) . "/resize.rrd", $rrd_file);
			}
		}
	}
}

/* rrd_check - Given a data source id, check the rrdtool file to the data source definition
   @param $data_source_id - data source id
   @rerturn - (array) an array containing issues with the rrdtool file definition vs data source */
function rrd_check($data_source_id) {
	global $config, $rrd_tune_array;
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	$data_source_name = get_data_source_item_name($rrd_tune_array["data_source_id"]);
	$data_source_type = $data_source_types{$rrd_tune_array["data-source-type"]};
	$data_source_path = get_data_source_path($rrd_tune_array["data_source_id"], true);


}

/* rrd_repair - Given a data source id, update the rrdtool file to match the data source definition
   @param $data_source_id - data source id
   @rerturn - 1 success, 2 false */
function rrd_repair($data_source_id) {
	global $config, $rrd_tune_array;
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	$data_source_name = get_data_source_item_name($rrd_tune_array["data_source_id"]);
	$data_source_type = $data_source_types{$rrd_tune_array["data-source-type"]};
	$data_source_path = get_data_source_path($rrd_tune_array["data_source_id"], true);


}

/* rrdtool_function_info - given a data source id, return rrdtool info array
   @param $data_source_id - data source id
   @returns - (array) an array containing all data from rrdtool info command */
function rrdtool_function_info($data_source_id) {
	global $config;

	/* Get the path to rrdtool file */
	$data_source_path = get_data_source_path($data_source_id, true);

	/* Execute rrdtool info command */
	$cmd_line = " info " . $data_source_path;
	$output = rrdtool_execute($cmd_line, RRDTOOL_OUTPUT_NULL, RRDTOOL_OUTPUT_STDOUT);
	if (sizeof($output) == 0) {
		return false;
	}

	/* Parse the output */
	$matches = array();
	$rrd_info = array( 'rra' => array(), "ds" => array() );
	$output = explode("\n", $output);
	foreach ($output as $line) {
		$line = trim($line);
		if (preg_match("/^ds\[(\S+)\]\.(\S+) = (\S+)$/", $line, $matches)) {
			$rrd_info["ds"][$matches[1]][$matches[2]] = $matches[3];
		} elseif (preg_match("/^rra\[(\S+)\]\.(\S+)\[(\S+)\]\.(\S+) = (\S+)$/", $line, $matches)) {
			$rrd_info['rra'][$matches[1]][$matches[2]][$matches[3]][$matches[4]] = $matches[5];
		} elseif (preg_match("/^rra\[(\S+)\]\.(\S+) = (\S+)$/", $line, $matches)) {
			$rrd_info['rra'][$matches[1]][$matches[2]] = $matches[3];
		} elseif (preg_match("/^(\S+) = \"(\S+)\"$/", $line, $matches)) {
			$rrd_info[$matches[1]] = $matches[2];
		} elseif (preg_match("/^(\S+) = (\S+)$/", $line, $matches)) {
			$rrd_info[$matches[1]] = $matches[2];
		}
	}
	$output = "";
	$matches = array();

	/* Return parsed values */
	return $rrd_info;

}

/* rrdtool_info2html	- take output from rrdtool info array and build html table
 * returns				  html code
 */
function rrdtool_info2html($info_array, $diff=array()) {
	global $colors;

	html_start_box("<strong>" . __("RRD File Information") . "</strong>", "100", $colors["header"], 0, "center", "");

	# header data
	$header_items = array(__("Header"), '');
	print "<tr><td>";
	html_header($header_items, 1, false, 'info_header');
	# add human readable timestamp
	if (isset($info_array["last_update"])) {
		$info_array["last_update"] .= " [" . date(date_time_format(), $info_array["last_update"]) . "]";
	}
	$loop = array(
		"filename" 		=> $info_array["filename"],
		"rrd_version"	=> $info_array["rrd_version"],
		"step" 			=> $info_array["step"],
		"last_update"	=> $info_array["last_update"]);
	foreach ($loop as $key => $value) {
		form_alternate_row_color($key, true);
		form_selectable_cell($key, 'key');
		form_selectable_cell($value, 'value', "", ((isset($diff[$key]) ? "textError" : "")));
		form_end_row();
	}
	form_end_table();

	# data sources
	$header_items = array(__("Data Source Items"), __('Type'), __('Minimal Heartbeat'), __('Min'), __('Max'), __('Last DS'), __('Value'), __('Unkown Sec'));
	print "<tr><td>";
	html_header($header_items, 1, false, 'info_ds');
	if (sizeof($info_array["ds"]) > 0) {
		foreach ($info_array["ds"] as $key => $value) {
			form_alternate_row_color('line' . $key, true);
			form_selectable_cell($key, 																			'name', 				"", (isset($diff["ds"][$key]["error"]) 				? "textError" : ""));
			form_selectable_cell((isset($value['type']) 				? $value['type'] : ''), 				'type', 				"", (isset($diff["ds"][$key]['type']) 				? "textError" : ""));
			form_selectable_cell((isset($value['minimal_heartbeat']) 	? $value['minimal_heartbeat'] : ''), 	'minimal_heartbeat', 	"", (isset($diff["ds"][$key]['minimal_heartbeat'])	? "textError" : ""));
			form_selectable_cell((isset($value['min']) 					? floatval($value['min']) : ''), 		'min', 					"", (isset($diff["ds"][$key]['min']) 				? "textError" : ""));
			form_selectable_cell((isset($value['max']) 					? floatval($value['max']) : ''), 		'max', 					"", (isset($diff["ds"][$key]['max']) 				? "textError" : ""));
			form_selectable_cell((isset($value['last_ds']) 				? $value['last_ds'] : ''), 				'last_ds');
			form_selectable_cell((isset($value['value']) 				? floatval($value['value']) : ''), 		'value');
			form_selectable_cell((isset($value['unknown_sec']) 			? $value['unknown_sec'] : ''), 			'unknown_sec');
			form_end_row();
		}
		form_end_table();
	}


	# round robin archive
	$header_items = array(__("Round Robin Archive"), __('Consolidation Function'), __('Rows'), __('Cur Row'), __('PDP per Row'), __('X Files Factor'), __('CDP Prep Value (0)'), __('CDP Unknown Datapoints (0)'));
	print "<tr><td>";
	html_header($header_items, 1, false, 'info_rra');
	if (sizeof($info_array['rra']) > 0) {
		foreach ($info_array['rra'] as $key => $value) {
			form_alternate_row_color('line_' . $key, true);
			form_selectable_cell($key, 																										'name', 			"", (isset($diff['rra'][$key]["error"]) ? "textError" : ""));
			form_selectable_cell((isset($value['cf']) 								? $value['cf'] : ''), 									'cf');
			form_selectable_cell((isset($value['rows']) 							? $value['rows'] : ''), 								'rows', 			"", (isset($diff['rra'][$key]['rows']) 	? "textError" : ""));
			form_selectable_cell((isset($value['cur_row']) 							? $value['cur_row'] : ''), 								'cur_row');
			form_selectable_cell((isset($value['pdp_per_row']) 						? $value['pdp_per_row'] : ''), 							'pdp_per_row');
			form_selectable_cell((isset($value['xff']) 								? floatval($value['xff']) : ''), 						'xff', 				"", (isset($diff['rra'][$key]['xff']) 	? "textError" : ""));
			form_selectable_cell((isset($value['cdp_prep'][0]['value']) 			? (strtolower($value['cdp_prep'][0]['value']) == "nan") ? $value['cdp_prep'][0]['value'] : floatval($value['cdp_prep'][0]['value']) : ''), 'value');
			form_selectable_cell((isset($value['cdp_prep'][0]['unknown_datapoints'])? $value['cdp_prep'][0]['unknown_datapoints'] : ''), 	'unknown_datapoints');
			form_end_row();
		}
		form_end_table();
	}


	print "</table></td></tr>";		/* end of html_header */


	html_end_box();
}
