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

function api_data_preset_rra_item_friendly_name_get($consolidation_function, $steps, $rows) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	$cf_types = api_data_preset_rra_cf_type_list();
	$row_types = api_data_preset_rra_row_type_list();

	$friendly_name = $cf_types[$consolidation_function] . ": ";

	if (($consolidation_function == RRA_CF_TYPE_AVERAGE) || ($consolidation_function == RRA_CF_TYPE_MIN) || ($consolidation_function == RRA_CF_TYPE_MAX) || ($consolidation_function == RRA_CF_TYPE_LAST)) {
		$friendly_name .= "Update every " . ($steps > 1 ? $steps : "") . " interval for " . $row_types[$rows];
	}

	return $friendly_name;
}

function api_data_preset_rra_fingerprint_generate($data_preset_rra_items) {
	$fingerprint = "";
	if (is_array($data_preset_rra_items)) {
		$i = 0;
		foreach ($data_preset_rra_items as $data_preset_rra_item) {
			$fingerprint .= api_data_preset_rra_item_fingerprint_generate($data_preset_rra_item) . (sizeof($data_preset_rra_items) > $i + 1 ? "|" : "");
			$i++;
		}
	}

	return $fingerprint;
}

function api_data_preset_rra_item_fingerprint_generate($data_preset_rra_item) {
	/* generate a separate 4 character fingerprint for each rra item so it is easy to update
	 * one item at a time */
	$_fingerprint = "";
	if (is_array($data_preset_rra_item)) {
		foreach ($data_preset_rra_item as $name => $value) {
			/* ignore any non-visible form fields */
			if (($name != "id") && ($name != "preset_rra_id") && ($name != "data_template_id") && ($name != "data_source_id")) {
				$_fingerprint .= md5($value);
			}
		}
	}

	/* the first 4 characters of the md5 sum is *good enough* */
	return str_pad($data_preset_rra_item["id"], 4, "0", STR_PAD_LEFT) . ":" . substr(md5($_fingerprint), 0, 4);
}

function api_data_preset_rra_fingerprint_strip($fingerprint) {
	$parts = explode("|", ereg_replace("[0-9]{4}:", "", $fingerprint));
	sort($parts);
	return implode("|", $parts);
}

?>
