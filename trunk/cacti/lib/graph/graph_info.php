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

/* get_graph_title - returns the title of a graph without using the title cache
   @arg $graph_id - (int) the ID of the graph to get a title for
   @returns - the graph title */
function get_graph_title($graph_id, $remove_unsubstituted_variables = false) {
	include_once(CACTI_BASE_PATH . "/lib/variables.php");

	$graph = db_fetch_row("select host_id,title from graph where id = $graph_id");

	$title = $graph["title"];

	if ((strstr($graph["title"], "|host_")) && (!empty($graph["host_id"]))) {
		$title = substitute_host_variables($title, $graph["host_id"]);
	}

	if ((strstr($graph["title"], "|query_")) && (!empty($graph["host_id"]))) {
		$data_query = array_rekey(db_fetch_assoc("select distinct
			data_source_field.name,
			data_source_field.value
			from data_source_field,graph_item,data_source_item
			where graph_item.data_source_item_id=data_source_item.id
			and data_source_item.data_source_id=data_source_field.data_source_id
			and graph_item.graph_id = $graph_id"), "name", "value");

		if ((isset($data_query["data_query_id"])) && (isset($data_query["data_query_index"]))) {
			$title = substitute_data_query_variables($title, $graph["host_id"], $data_query["data_query_id"], $data_query["data_query_index"], read_config_option("max_data_query_field_length"));
		}
	}

	if ($remove_unsubstituted_variables == true) {
		return remove_variables($title);
	}else{
		return $title;
	}
}

/* get_associated_rras - returns a list of all RRAs referenced by a particular graph
   @arg $graph_id - (int) the ID of the graph to retrieve a list of RRAs for
   @returns - (array) an array containing the name and id of each RRA found */
function get_associated_rras($graph_id) {
	return db_fetch_assoc("select
		rra.id,
		rra.steps,
		rra.rows,
		rra.name,
		rra.timespan,
		data_source.rrd_step
		from graph_item,data_source_rra,data_source_item,data_source,rra
		where graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and data_source.id=data_source_rra.data_source_id
		and data_source_rra.rra_id=rra.id
		and graph_item.graph_id = $graph_id
		group by rra.id
		order by rra.timespan");
}

?>
