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

function &graph_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph;
}

function &graph_labels_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_labels;
}

function &graph_right_axis_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_right_axis;
}

function &graph_size_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_size;
}

function &graph_limits_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_limits;
}

function &graph_grid_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_grid;
}

function &graph_color_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_color;
}

function &graph_legend_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_legend;
}

function &graph_misc_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_misc;
}

function &graph_cacti_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_cacti;
}

function &graph_item_form_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_forms.php");

	return $struct_graph_item;
}

function graph_actions_list() {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	global $graph_actions;

	return $graph_actions;
}
