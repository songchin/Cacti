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

require_once(CACTI_BASE_PATH . "/include/presets/preset_cdef_constants.php");

$vdef_functions = array(1 =>
	"MAXIMUM",
	"MINIMUM",
	"AVERAGE",
	"STDEV",
	"LAST",
	"FIRST",
	"TOTAL",
	"PERCENT",
	"PERCENTNAN",
	"LSLSLOPE",
	"LSLINT",
	"LSLCORREL");

$vdef_item_types = array(
	CVDEF_ITEM_TYPE_FUNCTION	=> __("Function"),
	CVDEF_ITEM_TYPE_SPEC_DS		=> __("Special Data Source"),
	CVDEF_ITEM_TYPE_STRING		=> __("Custom String"),
	);

$custom_vdef_data_source_types = array( # this may change as soon as RRDTool supports math in VDEF, until then only reference to CDEF may help
	"CURRENT_DATA_SOURCE"				=> __("Current Graph Item Data Source"),
	);
