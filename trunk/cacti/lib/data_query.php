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

/* update_data_query_cache - updates the local data query cache for each graph and data
     source tied to this host/data query
   @arg $host_id - the id of the host to refresh
   @arg $data_query_id - the id of the data query to refresh */
function update_data_query_cache($host_id, $data_query_id) {
	$graphs = db_fetch_assoc("select id from graph_local where host_id = '$host_id' and snmp_query_id = '$data_query_id'");

	if (sizeof($graphs) > 0) {
		foreach ($graphs as $graph) {
			update_graph_data_query_cache($graph["id"]);
		}
	}

	$data_sources = db_fetch_assoc("select id from data_local where host_id = '$host_id' and snmp_query_id = '$data_query_id'");

	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			update_data_source_data_query_cache($data_source["id"]);
		}
	}
}

/* update_data_source_data_query_cache - updates the local data query cache for a particular
	data source
   @arg $local_data_id - the id of the data source to update the data query cache for */
function update_data_source_data_query_cache($local_data_id) {
	$host_id = db_fetch_cell("select host_id from data_local where id=$local_data_id");

	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from data_template_data
		where data_template_data.local_data_id=$local_data_id"));

	if (empty($field)) { return; }

	$data_query_id = db_fetch_cell("select snmp_query_id from snmp_query_graph where id='" . $field["output_type"] . "'");

	$index = get_data_query_row_index($field["index_type"], $field["index_value"], $host_id, $data_query_id);

	if (($data_query_id != "0") && ($index != "")) {
		db_execute("update data_local set snmp_query_id='$data_query_id',snmp_index='$index' where id='$local_data_id'");

		/* update data source title cache */
		update_data_source_title_cache($local_data_id);
	}
}

/* update_graph_data_query_cache - updates the local data query cache for a particular
	graph
   @arg $local_graph_id - the id of the graph to update the data query cache for */
function update_graph_data_query_cache($local_graph_id) {
	$host_id = db_fetch_cell("select host_id from graph_local where id=$local_graph_id");

	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from graph_templates_item,data_template_rrd,data_template_data
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and graph_templates_item.local_graph_id=$local_graph_id
		limit 0,1"));

	if (empty($field)) { return; }

	$data_query_id = db_fetch_cell("select snmp_query_id from snmp_query_graph where id='" . $field["output_type"] . "'");

	$index = get_data_query_row_index($field["index_type"], $field["index_value"], $host_id, $data_query_id);

	if (($data_query_id != "0") && ($index != "")) {
		db_execute("update graph_local set snmp_query_id='$data_query_id',snmp_index='$index' where id=$local_graph_id");

		/* update graph title cache */
		update_graph_title_cache($local_graph_id);
	}
}

?>
