<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

include_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");

$cdef_operators = array(
	1 => "+",
	2 => "-",
	3 => "*",
	4 => "/",
	5 => "%"
	);

$cdef_functions = array(
	1 => "SIN",
	2 => "COS",
	3 => "LOG",
	4 => "EXP",
	5 => "FLOOR",
	6 => "CEIL",
	7 => "LT",
	8 => "LE",
	9 => "GT",
	10 => "GE",
	11 => "EQ",
	12 => "IF",
	13 => "MIN",
	14 => "MAX",
	15 => "LIMIT",
	16 => "DUP",
	17 => "EXC",
	18 => "POP",
	19 => "UN",
	20 => "UNKN",
	21 => "PREV",
	22 => "INF",
	23 => "NEGINF",
	24 => "NOW",
	25 => "TIME",
	26 => "LTIME"
	);

$graph_item_types = array(
	GRAPH_ITEM_TYPE_COMMENT => "COMMENT",
	GRAPH_ITEM_TYPE_HRULE => "HRULE",
	GRAPH_ITEM_TYPE_VRULE => "VRULE",
	GRAPH_ITEM_TYPE_LINE1 => "LINE1",
	GRAPH_ITEM_TYPE_LINE2 => "LINE2",
	GRAPH_ITEM_TYPE_LINE3 => "LINE3",
	GRAPH_ITEM_TYPE_AREA => "AREA",
	GRAPH_ITEM_TYPE_STACK => "STACK",
	GRAPH_ITEM_TYPE_GPRINT => "GPRINT"
	);

$graph_image_types = array(
	GRAPH_IMAGE_TYPE_PNG => "PNG",
	GRAPH_IMAGE_TYPE_GIF => "GIF"
	);

$graph_base_values = array(
	"1000" => _("1000 - Traffic"),
	"1024" => _("1024 - Memory")
	);

$graph_unit_exponent_values = array(
	"none" => _("None"),
	"-18" => "a - atto",
	"-15" => "f - femto",
	"-12" => "p - pico",
	"-9" => "n - nano",
	"-6" => "µ - micro",
	"-3" => "m - milli",
	"0" => "(no unit)",
	"3" => "k - kilo",
	"6" => "M - mega",
	"9" => "G - giga",
	"12" => "T - tera",
	"15" => "P - peta",
	"18" => "E - exa"
	);

$graph_timespans = array(
	GT_LAST_HALF_HOUR => _("Last Half Hour"),
	GT_LAST_HOUR => _("Last Hour"),
	GT_LAST_2_HOURS => _("Last 2 Hours"),
	GT_LAST_4_HOURS => _("Last 4 Hours"),
	GT_LAST_6_HOURS => _("Last 6 Hours"),
	GT_LAST_12_HOURS => _("Last 12 Hours"),
	GT_LAST_DAY => _("Last Day"),
	GT_LAST_2_DAYS => _("Last 2 Days"),
	GT_LAST_3_DAYS => _("Last 3 Days"),
	GT_LAST_4_DAYS => _("Last 4 Days"),
	GT_LAST_WEEK => _("Last Week"),
	GT_LAST_2_WEEKS => _("Last 2 Weeks"),
	GT_LAST_MONTH => _("Last Month"),
	GT_LAST_2_MONTHS => _("Last 2 Months"),
	GT_LAST_3_MONTHS => _("Last 3 Months"),
	GT_LAST_4_MONTHS => _("Last 4 Months"),
	GT_LAST_6_MONTHS => _("Last 6 Months"),
	GT_LAST_YEAR => _("Last Year"),
	GT_LAST_2_YEARS => _("Last 2 Years")
	);

?>