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

/* ninety_fifth_percentile - given a data source, calculate the 95th percentile for a given
     time period
   @arg $local_data_id - the data source to perform the 95th percentile calculation
   @arg $start_time - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $end_time - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $resolution - the accuracy of the data measured in seconds
   @returns - (array) an array containing each data source item, and its 95th percentile */
function ninety_fifth_percentile($local_data_id, $start_seconds, $end_seconds, $resolution = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/rrd.php");

	$values_array = array();

	/* Check to see if we have array, if array, we are doing complex 95th calculations */
	if (is_array($local_data_id)) {
		$sum_array = array();
		$fetch_array = array();
		$percentile_array = array();
		$num_ds = 0;

		$i = 0;
		/* do a fetch for each data source */
		while (list($ldi, $ldi_value) = each($local_data_id)) {
			$fetch_array[$i] = rrdtool_function_fetch($ldi, $start_seconds, $end_seconds, $resolution);
			/* clean up unwanted data source items */
			if (! empty($fetch_array[$i])) {
				while (list($id, $name) = each($fetch_array[$i]["data_source_names"])) {
					/* get rid of the 95th max for now since we'll need to re-calculate it later */
					if ($name == "ninety_fifth_percentile_maximum") {
						unset($fetch_array[$i]["data_source_names"][$id]);
						unset($fetch_array[$i]["values"][$id]);
					}
					/* clean up DS items that aren't defined on the graph */
					if (! in_array($name, $local_data_id[$ldi])) {
						unset($fetch_array[$i]["data_source_names"][$id]);
						unset($fetch_array[$i]["values"][$id]);
					}
				}
				$i++;
			}
		}

		/* Create our array for working  */
		if (empty($fetch_array[0]["data_source_names"])) {
			/* here to avoid warning on non-exist file */
			$fetch_array = array();
		}else{
			$sum_array["data_source_names"] = $fetch_array[0]["data_source_names"];
		}

		if (sizeof($fetch_array) > 0) {
			/* Create hash of ds_item_name => array or fetch_array indexes */
			/* this is used to later sum, max and total the data sources used on the graph */
			/* Loop fetch array index  */
			$dsi_name_to_id = array();
			reset($fetch_array);
			for ($i=0; $i<count($fetch_array); $i++) {
				/* Go through data souce names */
				reset($fetch_array[$i]["data_source_names"]);
				foreach ( $fetch_array[$i]["data_source_names"] as $ds_name ) {
					$dsi_name_to_id[$ds_name][] = $i;
				}
			}

			/* Sum up the like data sources */
			$sum_array = array();
			$i = 0;
			foreach ( $dsi_name_to_id as $ds_name => $id_array ) {
				$sum_array["data_source_names"][$i] = $ds_name;
				foreach ($id_array as $id) {
					$fetch_id = array_search($ds_name,$fetch_array[$id]["data_source_names"]);
						/* Sum up like ds names */
						for ($j=0; $j<count($fetch_array[$id]["values"][$fetch_id]); $j++) {
							if (isset($fetch_array[$id]["values"][$fetch_id][$j])) {
								$value = $fetch_array[$id]["values"][$fetch_id][$j];
							}else{
								$value = 0;
							}
							if (isset($sum_array["values"][$i][$j])) {
								$sum_array["values"][$i][$j] += $value;
							}else{
								$sum_array["values"][$i][$j] = $value;
							}
						}
				}
				$i++;
			}
		}

		/* calculate extra data, max, sum */
		if (isset($sum_array["values"])) {
			$num_ds = count($sum_array["values"]);
			$total_ds = count($sum_array["values"]);
			for ($j=0; $j<$total_ds; $j++) { /* each data source item */
				for ($k=0; $k<count($sum_array["values"][$j]); $k++) { /* each rrd row */
					/* now we must re-calculate the 95th max */
					$value = 0;
					if (isset($sum_array["values"][$j][$k])) {
						$value = $sum_array["values"][$j][$k];
					}
					if (isset($sum_array["values"][$num_ds][$k])) {
						$sum_array["values"][$num_ds][$k] = max($value, $sum_array["values"][$num_ds][$k]);
					}else{
						$sum_array["values"][$num_ds][$k] = max($value, 0);
					}
					/* sum of all ds rows */
					$value = 0;
					if (isset($sum_array["values"][$j][$k])) {
						$value = $sum_array["values"][$j][$k];
					}

					if (isset($sum_array["values"][$num_ds + 1][$k])) {
						$sum_array["values"][$num_ds + 1][$k] += $value;
					}else{
						$sum_array["values"][$num_ds + 1][$k] = $value;
					}

				}
			}

			$sum_array["data_source_names"][$num_ds] = "ninety_fifth_percentile_aggregate_max";
			$sum_array["data_source_names"][$num_ds + 1] = "ninety_fifth_percentile_aggregate_sum";

		}

		$fetch_array = $sum_array;
	}else{
		/* No array, just calculate the 95th for the data source */
		$fetch_array = rrdtool_function_fetch($local_data_id, $start_seconds, $end_seconds, $resolution);
	}

	/* loop through each data source */
	if (empty($fetch_array["data_source_names"])) {
		$return_array = array();
	}else{
		for ($i=0; $i<count($fetch_array["data_source_names"]); $i++) {
			if (isset($fetch_array["values"][$i])) {
				$values_array = $fetch_array["values"][$i];

				/* sort the array in descending order */
				rsort($values_array, SORT_NUMERIC);
			}

			/* grab the 95% row (or 5% in reverse) and use that as our 95th percentile
			value */
			$target = ((count($values_array) + 1) * .05);
			$target = sprintf("%d", $target);

			if (empty($values_array[$target])) { $values_array[$target] = 0; }

			/* collect 95th percentile values in this array so we can return them */
			$return_array{$fetch_array["data_source_names"][$i]} = $values_array[$target];

			/* get max 95th calculation for aggregate */
			if (($fetch_array["data_source_names"][$i] != "ninety_fifth_percentile_aggregate_max") &&
				($fetch_array["data_source_names"][$i] != "ninety_fifth_percentile_aggregate_sum") &&
				($fetch_array["data_source_names"][$i] != "ninety_fifth_percentile_maximum")) {
				if (isset($return_array{"ninety_fifth_percentile_aggregate_total"})) {
					if (($return_array{"ninety_fifth_percentile_aggregate_total"} < $values_array[$target])) {
						$return_array{"ninety_fifth_percentile_aggregate_total"} = $values_array[$target];
					}
				}else{
					$return_array{"ninety_fifth_percentile_aggregate_total"} = $values_array[$target];
				}
			}
		}
	}

	if (isset($return_array)) {
		return $return_array;
	}

}

/* bandwidth_summation - given a data source, sums all data in the rrd for a given
     time period
   @arg $local_data_id - the data source to perform the summation for
   @arg $start_time - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $end_time - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $resolution - the accuracy of the data measured in seconds
   @arg $rra_steps - how many periods each sample in the RRA counts for, values above '1'
     result in an averaged summation
   @arg $ds_steps - how many seconds each period represents
   @returns - (array) an array containing each data source item, and its sum */
function bandwidth_summation($local_data_id, $start_time, $end_time, $rra_steps, $ds_steps) {
	require_once(CACTI_BASE_PATH . "/lib/sys/rrd.php");

	$fetch_array = rrdtool_function_fetch($local_data_id, $start_time, $end_time, $rra_steps * $ds_steps);

	if ((!isset($fetch_array["data_source_names"])) || (count($fetch_array["data_source_names"]) == 0)) {
		return;
	}

	$return_array = array();

	/* loop through each regexp determined above (or each data source) */
	for ($i=0;$i<count($fetch_array["data_source_names"]);$i++) {
		$sum = 0;

		if (isset($fetch_array["values"][$i])) {
			$values_array = $fetch_array["values"][$i];

			for ($j=0;$j<count($fetch_array["values"][$i]);$j++) {
				$sum += $fetch_array["values"][$i][$j];
			}

			if (count($fetch_array["values"][$i]) != 0) {
				$sum = ($sum * $ds_steps * $rra_steps);
			}else{
				$sum = 0;
			}

			/* collect 95th percentile values in this array so we can return them */
			$return_array{$fetch_array["data_source_names"][$i]} = $sum;
		}
	}

	return $return_array;
}

/* this variable is used as a cache to prevent extra calls to ninety_fifth_percentile() */
$ninety_fifth_cache = array();

/* variable_ninety_fifth_percentile - given a 95th percentile variable, calculate the 95th percentile
     and format it for display on the graph
   @arg $var_scale - variable scale (bytes or bytes)
   @arg $var_divisor - variable power of 10 divisor (3 = kilo, 6 = mega, etc)
   @arg $var_type - variable type (current, total, max, total_peak, all_max_current, all_max_current)
   @arg $var_precision - variable digits of floating point precision
   @arg $graph_item - an array that contains the current graph item
   @arg $graph_items - an array that contains all graph items
   @arg $graph_start - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $graph_end - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $seconds_between_graph_updates - the number of seconds between each update on the graph which
     varies depending on the RRA in use
   @returns - a string containg the 95th percentile suitable for placing on the graph */
function variable_ninety_fifth_percentile($var_scale, $var_divisor, $var_type, $var_precision, &$graph_item, &$graph_items, $graph_start, $graph_end) {
	global $ninety_fifth_cache;

	require_once(CACTI_BASE_PATH . "/lib/graph/graph_utility.php");

	if (($var_type == "current") || ($var_type == "max")) {
		if (!isset($ninety_fifth_cache{$graph_item["local_data_id"]})) {
			$ninety_fifth_cache{$graph_item["local_data_id"]} = ninety_fifth_percentile($graph_item["local_data_id"], $graph_start, $graph_end);
		}
	}elseif (($var_type == "total") || ($var_type == "total_peak") || ($var_type == "all_max_current") || ($var_type == "all_max_peak")) {
		for ($t=0;($t<count($graph_items));$t++) {
			if ((!isset($ninety_fifth_cache{$graph_items[$t]["local_data_id"]})) && (!empty($graph_items[$t]["local_data_id"]))) {
				$ninety_fifth_cache{$graph_items[$t]["local_data_id"]} = ninety_fifth_percentile($graph_items[$t]["local_data_id"], $graph_start, $graph_end);
			}
		}
	}elseif (($var_type == "aggregate") || ($var_type == "aggregate_sum") || ($var_type == "aggregate_max")) {
		$local_data_array = array();
		for ($t=0;($t<count($graph_items));$t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_data_array[$graph_items[$t]["local_data_id"]][] = $graph_items[$t]["data_source_name"];
			}
		}
                $ninety_fifth_cache{0} = ninety_fifth_percentile($local_data_array, $graph_start, $graph_end);
	}

	$ninety_fifth = 0;

	/* format the output according to args passed to the variable */
	if ($var_type == "current") {
		$ninety_fifth = $ninety_fifth_cache{$graph_item["local_data_id"]}{$graph_item["data_source_name"]};
		$ninety_fifth = ($var_scale == "bits") ? $ninety_fifth * 8 : $ninety_fifth;
		$ninety_fifth /= pow(10, intval($var_divisor));
	}elseif ($var_type == "total") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_ninety_fifth = $ninety_fifth_cache{$graph_items[$t]["local_data_id"]}{$graph_items[$t]["data_source_name"]};
				$local_ninety_fifth = ($var_scale == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
				$local_ninety_fifth /= pow(10, intval($var_divisor));

				$ninety_fifth += $local_ninety_fifth;
			}
		}
	}elseif ($var_type == "max") {
		$ninety_fifth = $ninety_fifth_cache{$graph_item["local_data_id"]}["ninety_fifth_percentile_maximum"];
		$ninety_fifth = ($var_scale == "bits") ? $ninety_fifth * 8 : $ninety_fifth;
		$ninety_fifth /= pow(10, intval($var_divisor));
	}elseif ($var_type == "total_peak") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_ninety_fifth = $ninety_fifth_cache{$graph_items[$t]["local_data_id"]}["ninety_fifth_percentile_maximum"];
				$local_ninety_fifth = ($var_scale == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
				$local_ninety_fifth /= pow(10, intval($var_divisor));

				$ninety_fifth += $local_ninety_fifth;
			}
		}
	}elseif ($var_type == "all_max_current") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_ninety_fifth = $ninety_fifth_cache{$graph_items[$t]["local_data_id"]}{$graph_items[$t]["data_source_name"]};
				$local_ninety_fifth = ($var_scale == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
				$local_ninety_fifth /= pow(10, intval($var_divisor));

				if ($local_ninety_fifth > $ninety_fifth) {
					$ninety_fifth = $local_ninety_fifth;
				}
			}
		}
	}elseif ($var_type == "all_max_peak") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_ninety_fifth = $ninety_fifth_cache{$graph_items[$t]["local_data_id"]}["ninety_fifth_percentile_maximum"];
				$local_ninety_fifth = ($var_scale == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
				$local_ninety_fifth /= pow(10, intval($var_divisor));

				if ($local_ninety_fifth > $ninety_fifth) {
					$ninety_fifth = $local_ninety_fifth;
				}
			}
		}
	}elseif ($var_type == "aggregate") {
		if (empty($ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_total"])) {
			$ninety_fifth = 0;
		}else{
			$local_ninety_fifth = $ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_total"];
			$local_ninety_fifth = ($regexp_match_array[1] == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
			$local_ninety_fifth /= pow(10,intval($var_divisor));
			$ninety_fifth = $local_ninety_fifth;
		}
	}elseif ($var_type == "aggregate_max") {
		if (empty($ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_max"])) {
			$ninety_fifth = 0;
		}else{
			$local_ninety_fifth = $ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_max"];
			$local_ninety_fifth = ($regexp_match_array[1] == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
			$local_ninety_fifth /= pow(10,intval($var_divisor));
			$ninety_fifth = $local_ninety_fifth;
		}
	}elseif ($var_type == "aggregate_sum") {
		if (empty($ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_sum"])) {
			$ninety_fifth = 0;
		}else{
			$local_ninety_fifth = $ninety_fifth_cache{0}["ninety_fifth_percentile_aggregate_sum"];
			$local_ninety_fifth = ($regexp_match_array[1] == "bits") ? $local_ninety_fifth * 8 : $local_ninety_fifth;
			$local_ninety_fifth /= pow(10,intval($var_divisor));
			$ninety_fifth = $local_ninety_fifth;
		}
	}

	/* determine the floating point precision */
	if ((isset($var_precision)) && (is_numeric($var_precision))) {
		$round_to = $var_precision;
	}else{
		$round_to = 2;
	}

	/* return the final result and round off to two decimal digits */
	return round($ninety_fifth, $round_to);
}

/* this variable is used as a cache to prevent extra calls to bandwidth_summation() */
$summation_cache = array();

/* variable_bandwidth_summation - given a bandwidth summation variable, calculate the summation
     and format it for display on the graph
   @arg $var_divisor - variable power of 10 divisor (3 = kilo, 6 = mega, etc)
   @arg $var_type - variable type (current, total, atomic)
   @arg $var_precision - variable digits of floating point precision
   @arg $var_timespan - seconds to perform the calculation for or 'auto'
   @arg $graph_item - an array that contains the current graph item
   @arg $graph_items - an array that contains all graph items
   @arg $graph_start - the start time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $graph_end - the end time to use for the data calculation. this value can
     either be absolute (unix timestamp) or relative (to now)
   @arg $seconds_between_graph_updates - the number of seconds between each update on the graph which
     varies depending on the RRA in use
   @arg $rra_step - how many periods each sample in the RRA counts for, values above '1' result in an
     averaged summation
   @arg $ds_step - how many seconds each period represents
   @returns - a string containg the bandwidth summation suitable for placing on the graph */
function variable_bandwidth_summation($var_divisor, $var_type, $var_precision, $var_timespan, &$graph_item, &$graph_items, $graph_start, $graph_end, $rra_step, $ds_step) {
	global $summation_cache;

	require_once(CACTI_BASE_PATH . "/lib/graph/graph_utility.php");

	if (is_numeric($var_timespan)) {
		$summation_timespan_start = -$var_timespan;
	}else{
		$summation_timespan_start = $graph_start;
	}

	if ($var_type == "current") {
		if (!isset($summation_cache{$graph_item["local_data_id"]})) {
			$summation_cache{$graph_item["local_data_id"]} = bandwidth_summation($graph_item["local_data_id"], $summation_timespan_start, $graph_end, $rra_step, $ds_step);
		}
	}elseif ($var_type == "total") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((!isset($summation_cache{$graph_items[$t]["local_data_id"]})) && (!empty($graph_items[$t]["local_data_id"]))) {
				$summation_cache{$graph_items[$t]["local_data_id"]} = bandwidth_summation($graph_items[$t]["local_data_id"], $summation_timespan_start, $graph_end, $rra_step, $ds_step);
			}
		}
	}elseif ($var_type == "atomic") {
		if (!isset($summation_cache{$graph_item["local_data_id"]})) {
			$summation_cache{$graph_item["local_data_id"]} = bandwidth_summation($graph_item["local_data_id"], $summation_timespan_start, $graph_end, $rra_step, 1);
		}
	}

	$summation = 0;

	/* format the output according to args passed to the variable */
	if (($var_type == "current") || ($var_type == "atomic")) {
		$summation = $summation_cache{$graph_item["local_data_id"]}{$graph_item["data_source_name"]};
	}elseif ($var_type == "total") {
		for ($t=0; $t<count($graph_items); $t++) {
			if ((is_graph_item_type_primary($graph_items[$t]["graph_type_id"])) && (!empty($graph_items[$t]["data_template_rrd_id"]))) {
				$local_summation = $summation_cache{$graph_items[$t]["local_data_id"]}{$graph_items[$t]["data_source_name"]};

				$summation += $local_summation;
			}
		}
	}

	if (is_numeric($var_divisor)) {
		$summation /= pow(10, intval($var_divisor));
	}elseif ($var_divisor == "auto") {
		if ($summation < 1000) {
			$summation_label = "bytes";
		}elseif ($summation < 1000000) {
			$summation_label = "KB";
			$summation /= 1000;
		}elseif ($summation < 1000000000) {
			$summation_label = "MB";
			$summation /= 1000000;
		}elseif ($summation < 1000000000000) {
			$summation_label = "GB";
			$summation /= 1000000000;
		}else{
			$summation_label = "TB";
			$summation /= 1000000000000;
		}
	}

	/* determine the floating point precision */
	if (is_numeric($var_precision)) {
		$round_to = $var_precision;
	}else{
		$round_to = 2;
	}

	/* substitute in the final result and round off to two decimal digits */
	if (isset($summation_label)) {
		return round($summation, $round_to) . " $summation_label";
	}else{
		return round($summation, $round_to);
	}
}

?>
