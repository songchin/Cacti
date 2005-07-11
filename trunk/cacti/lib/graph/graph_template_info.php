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

function get_graph_template($graph_template_id) {
	/* sanity check */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	return db_fetch_row("select * from graph_template where id = " . sql_sanitize($graph_template_id));
}

function get_graph_template_items($graph_template_id) {
	/* sanity check */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	return db_fetch_assoc("select * from graph_template_item where graph_template_id = " . sql_sanitize($graph_template_id));
}

function &get_graph_template_field_list() {
	include(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	return $fields_graph;
}

function &get_graph_template_items_field_list() {
	include(CACTI_BASE_PATH . "/include/graph/graph_form.php");

	return array(
			"data_template_item_id" => array(
				"default" => "",
				"data_type" => DB_TYPE_NUMBER
			)
		) + $fields_graph_item;
}

?>
