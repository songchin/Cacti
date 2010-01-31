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

require_once(CACTI_BASE_PATH . "/include/presets/preset_rra_constants.php");

$consolidation_functions = array(
	RRA_CF_TYPE_AVERAGE		=> "AVERAGE",
	RRA_CF_TYPE_MIN			=> "MIN",
	RRA_CF_TYPE_MAX			=> "MAX",
	RRA_CF_TYPE_LAST		=> "LAST",
	# be prepared for Holt-Winters Forecasting
#	RRA_CF_TYPE_HWPREDICT 	=> "HWPREDICT",
#	RRA_CF_TYPE_SEASONAL 	=> "SEASONAL",
#	RRA_CF_TYPE_DEVSEASONAL => "DEVSEASONAL",
#	RRA_CF_TYPE_DEVPREDICT 	=> "DEVPREDICT",
#	RRA_CF_TYPE_FAILURES 	=> "FAILURES",
	);
