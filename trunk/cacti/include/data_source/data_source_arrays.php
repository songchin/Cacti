<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

include_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

$consolidation_functions = array(
	1 => _("AVERAGE"),
	2 => _("MIN"),
	3 => _("MAX"),
	4 => _("LAST")
	);

$data_source_types = array(
	1 => _("GAUGE"),
	2 => _("COUNTER"),
	3 => _("DERIVE"),
	4 => _("ABSOLUTE")
	);

$data_input_types = array(
	DATA_INPUT_TYPE_NONE => _("None (External Source)"),
	DATA_INPUT_TYPE_DATA_QUERY => _("Data Query"),
	DATA_INPUT_TYPE_SCRIPT => _("Script"),
	DATA_INPUT_TYPE_SNMP => _("SNMP")
	);

$data_source_polling_intervals = array(
	60 => "1 Minute",
	120 => "2 Minutes",
	180 => "3 Minutes",
	240 => "4 Minutes",
	300 => "5 Minutes",
	600 => "10 Minutes",
	900 => "15 Minutes",
	1800 => "30 Minutes",
	3600 => "1 Hour",
	7200 => "2 Hours",
	21600 => "6 Hours",
	43200 => "12 Hours",
	86400 => "1 Day"
	);

?>