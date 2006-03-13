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

include_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_constants.php");

$rra_preset_row_types = array(
	1800 => "30 Minutes",
	3600 => "1 Hour",
	7200 => "2 Hours",
	14400 => "4 Hours",
	28800 => "8 Hours",
	43200 => "12 Hours",
	86400 => "1 Day",
	172800 => "2 Days",
	345600 => "4 Days",
	604800 => "1 Week",
	120960 => "2 Weeks",
	2592000 => "1 Month",
	5184000 => "2 Months",
	7776000 => "3 Months",
	15552000 => "6 Months",
	23328000 => "9 Months",
	31536000 => "1 Year",
	63072000 => "2 Years",
	157680000 => "5 Years",
	315360000 => "10 Years"
	);

$rra_preset_cf_types = array(
	RRA_CF_TYPE_AVERAGE => "AVERAGE",
	RRA_CF_TYPE_MIN => "MIN",
	RRA_CF_TYPE_MAX => "MAX",
	RRA_CF_TYPE_LAST => "LAST",
	RRA_CF_TYPE_HWPREDICT => "HWPREDICT",
	RRA_CF_TYPE_SEASONAL => "SEASONAL",
	RRA_CF_TYPE_DEVSEASONAL => "DEVSEASONAL",
	RRA_CF_TYPE_DEVPREDICT => "DEVPREDICT",
	RRA_CF_TYPE_FAILURES => "FAILURES"
	);

?>
