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

function api_data_preset_rra_item_friendly_name_get($consolidation_function, $steps, $rows) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	$cf_types = api_data_preset_rra_cf_type_list();
	$step_types = api_data_preset_rra_step_type_list();
	$row_types = api_data_preset_rra_row_type_list();

	$friendly_name = $cf_types[$consolidation_function] . ": ";

	if (($consolidation_function == RRA_CF_TYPE_AVERAGE) || ($consolidation_function == RRA_CF_TYPE_MIN) || ($consolidation_function == RRA_CF_TYPE_MAX) || ($consolidation_function == RRA_CF_TYPE_LAST)) {
		$friendly_name .= "Update every " . strtolower($step_types[$steps]) . " for " . strtolower($row_types[$rows]);
	}

	return $friendly_name;
}

?>
