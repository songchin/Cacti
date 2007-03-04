<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

$cdef_preset_operators = array(
	1 => "+",
	2 => "-",
	3 => "*",
	4 => "/",
	5 => "%"
	);

$cdef_preset_functions = array(
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

$cdef_preset_variables = array(
	"CURRENT_DATA_SOURCE" => _("Current Graph Item Data Source"),
	"ALL_DATA_SOURCES_NODUPS" => _("All Data Sources (Don't Include Duplicates)"),
	"ALL_DATA_SOURCES_DUPS" => _("All Data Sources (Include Duplicates)"),
	"CURRENT_DS_MINIMUM_VALUE" => _("Current Data Source Item: Minimum Value"),
	"SIMILAR_DATA_SOURCES_NODUPS" => _("All Similar Data Sources (Don't Include Duplicates)"),
	"CURRENT_DS_MAXIMUM_VALUE" => _("Current Data Source Item: Maximum Value"),
	"CURRENT_GRAPH_MINIMUM_VALUE" => _("Graph: Lower Limit"),
	"CURRENT_GRAPH_MAXIMUM_VALUE" => _("Graph: Upper Limit"));

?>
