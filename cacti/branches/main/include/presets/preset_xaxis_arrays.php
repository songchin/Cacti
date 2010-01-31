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

require_once(CACTI_BASE_PATH . "/include/presets/preset_xaxis_constants.php");

$rrd_xaxis_timespans = array(
	RRD_XAXIS_SECOND		=> __("Second"),
	RRD_XAXIS_MINUTE		=> __("Minute"),
	RRD_XAXIS_HOUR			=> __("Hour"),
	RRD_XAXIS_DAY			=> __("Day"),
	RRD_XAXIS_WEEK			=> __("Week"),
	RRD_XAXIS_MONTH			=> __("Month"),
	RRD_XAXIS_YEAR			=> __("Year"),
);
