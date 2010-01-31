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

$cdef_operators = array(1 =>
	"+",
	"-",
	"*",
	"/",
	"%");

$cdef_functions = array(1 =>
	"ADDNAN",
	"SIN",
	"COS",
	"LOG",
	"EXP",
	"SQRT",
	"ATAN",
	"ATAN2",
	"FLOOR",
	"CEIL",
	"DEG2RAD",
	"RAD2DEG",
	"ABS",
	"SORT",
	"REV",
	"AVG",
	"TREND",
	"TRENDNAN",
	"PREDICT",
	"PREDICTSIGMA",
	"LT",
	"LE",
	"GT",
	"GE",
	"EQ",
	"NE",
	"UN",
	"ISINF",
	"IF",
	"MIN",
	"MAX",
	"LIMIT",
	"DUP",
	"POP",
	"EXC",
	"UNKN",
	"INF",
	"NEGINF",
	"PREV",
	"NOW",
	"TIME",
	"LTIME");

$cdef_item_types = array(
	CVDEF_ITEM_TYPE_FUNCTION	=> __("Function"),
	CVDEF_ITEM_TYPE_OPERATOR	=> __("Operator"),
	CVDEF_ITEM_TYPE_SPEC_DS		=> __("Special Data Source"),
	CVDEF_ITEM_TYPE_CDEF		=> __("Another CDEF"),
	CVDEF_ITEM_TYPE_STRING		=> __("Custom String"),
	);

$custom_data_source_types = array(
	"CURRENT_DATA_SOURCE"				=> __("Current Graph Item Data Source"),
	"ALL_DATA_SOURCES_NODUPS"			=> __("All Data Sources (Don't Include Duplicates)"),
	"ALL_DATA_SOURCES_DUPS"				=> __("All Data Sources (Include Duplicates)"),
	"SIMILAR_DATA_SOURCES_NODUPS"		=> __("All Similar Data Sources (Don't Include Duplicates)"),
	"SIMILAR_DATA_SOURCES_DUPS"			=> __("All Similar Data Sources (Include Duplicates)"),
	"CURRENT_DS_MINIMUM_VALUE"			=> __("Current Data Source Item: Minimum Value"),
	"CURRENT_DS_MAXIMUM_VALUE"			=> __("Current Data Source Item: Maximum Value"),
	"CURRENT_GRAPH_MINIMUM_VALUE"		=> __("Graph: Lower Limit"),
	"CURRENT_GRAPH_MAXIMUM_VALUE"		=> __("Graph: Upper Limit"),
	"COUNT_ALL_DS_NODUPS"				=> __("Count of All Data Sources (Don't Include Duplicates)"),
	"COUNT_ALL_DS_DUPS"					=> __("Count of All Data Sources (Include Duplicates)"),
	"COUNT_SIMILAR_DS_NODUPS"			=> __("Count of All Similar Data Sources (Don't Include Duplicates)"),
	"COUNT_SIMILAR_DS_DUPS"		 		=> __("Count of All Similar Data Sources (Include Duplicates)"),
	"TIME_SHIFT_START"					=> __("Graph: Shift Start Time"),
	"TIME_SHIFT_END"					=> __("Graph: Shift End Time"),
);
