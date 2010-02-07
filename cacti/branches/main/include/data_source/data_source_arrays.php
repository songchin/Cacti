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

require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

$ds_actions = array(
	DS_ACTION_DELETE => __("Delete"),
	DS_ACTION_CHANGE_TEMPLATE => __("Change Data Source Template"),
	DS_ACTION_DUPLICATE => __("Duplicate"),
	DS_ACTION_CONVERT_TO_TEMPLATE => __("Convert to Data Source Template"),
	DS_ACTION_CHANGE_HOST => __("Change Host"),
	DS_ACTION_REAPPLY_SUGGESTED_NAMES => __("Reapply Suggested Names"),
	DS_ACTION_ENABLE => __("Enable"),
	DS_ACTION_DISABLE => __("Disable"),
	);

$data_source_types = array(
	DATA_SOURCE_TYPE_GAUGE		=> "GAUGE",
	DATA_SOURCE_TYPE_COUNTER	=> "COUNTER",
	DATA_SOURCE_TYPE_DERIVE		=> "DERIVE",
	DATA_SOURCE_TYPE_ABSOLUTE	=> "ABSOLUTE",
	DATA_SOURCE_TYPE_COMPUTE	=> "COMPUTE"
	);
