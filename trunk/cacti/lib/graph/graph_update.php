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

function api_graph_save($graph_id, &$_fields_graph, $skip_cache_update = false) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	/* sanity check for $graph_id */
	if (!is_numeric($graph_id)) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $graph_id);

	/* field: graph_template_id */
	if (isset($_fields_graph["graph_template_id"])) {
		$_fields["graph_template_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_graph["graph_template_id"]);
	}

	/* field: host_id */
	if (isset($_fields_graph["host_id"])) {
		$_fields["host_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_graph["host_id"]);
	}

	/* fetch a list of all visible graph fields */
	$fields_graph = get_graph_field_list();

	foreach (array_keys($fields_graph) as $field_name) {
		if (isset($_fields_graph[$field_name])) {
			$_fields[$field_name] = array("type" => $fields_graph[$field_name]["data_type"], "value" => $_fields_graph[$field_name]);
		}
	}

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	if (db_replace("graph", $_fields, array("id"))) {
		$graph_id = db_fetch_insert_id();

		if ($skip_cache_update == false) {
			update_graph_title_cache($graph_id);
		}

		return true;
	}else{
		return false;
	}
}

/* api_resize_graphs - resizes the selected graph, overriding the template value
   @arg $graph_templates_graph_id - the id of the graph to resize
   @arg $graph_width - the width of the resized graph
   @arg $graph_height - the height of the resized graph
  */
function api_resize_graphs($local_graph_id, $graph_width, $graph_height) {
	/* get graphs template id */
	db_execute("UPDATE graph SET width=" . $graph_width . ", height=" . $graph_height . " WHERE id=" . $local_graph_id);
}

function api_graph_remove($graph_id) {
	if ((empty($graph_id)) || (!is_numeric($graph_id))) {
		return;
	}

	db_execute("delete from graph_item where graph_id = $graph_id");
	db_execute("delete from graph_tree_items where local_graph_id = $graph_id");
	db_execute("delete from graph where id = $graph_id");
}

function api_graph_item_save($graph_item_id, &$_fields_graph_item) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	/* sanity check for $graph_item_id */
	if (!is_numeric($graph_item_id)) {
		return false;
	}

	/* sanity check for $graph_id */
	if ((empty($graph_item_id)) && (empty($_fields_graph_item["graph_id"]))) {
		api_syslog_cacti_log("Required graph_id when graph_item_id = 0", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
		return false;
	} else if ((isset($_fields_graph_item["graph_id"])) && (!is_numeric($_fields_graph_item["graph_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_NUMBER, "value" => $graph_item_id);

	/* field: graph_id */
	if (!empty($_fields_graph_item["graph_id"])) {
		$_fields["graph_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_graph_item["graph_id"]);
	}

	/* field: graph_template_item_id */
	if (isset($_fields_graph_item["graph_template_item_id"])) {
		$_fields["graph_template_item_id"] = array("type" => DB_TYPE_NUMBER, "value" => $_fields_graph_item["graph_template_item_id"]);
	}

	/* field: sequence */
	if (empty($graph_item_id)) {
		$_fields["sequence"] = array("type" => DB_TYPE_NUMBER, "value" => seq_get_current($_fields_graph_item["id"], "sequence", "graph_item", "graph_id = " . sql_sanitize($_fields_graph_item["graph_id"])));
	}

	/* fetch a list of all visible graph item fields */
	$fields_graph_item = get_graph_items_field_list();

	/* check for an empty field list */
	if (sizeof($_fields) == 1) {
		return true;
	}

	foreach (array_keys($fields_graph_item) as $field_name) {
		if (isset($_fields_graph_item[$field_name])) {
			$_fields[$field_name] = array("type" => $fields_graph_item[$field_name]["data_type"], "value" => $_fields_graph_item[$field_name]);
		}
	}

	if (db_replace("graph_item", $_fields, array("id"))) {
		return true;
	}else{
		return false;
	}
}

function api_graph_item_remove($graph_item_id) {
	if ((empty($graph_item_id)) || (!is_numeric($graph_item_id))) {
		return;
	}

	db_execute("delete from graph_item where id = $graph_item_id");
}

function api_graph_item_movedown($graph_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_id = db_fetch_cell("select graph_id from graph_item where id = $graph_item_id");

	$next_item = seq_get_item("graph_item", "sequence", $graph_item_id, "graph_id = $graph_id", "next");

	seq_move_item("graph_item", $graph_item_id, "graph_id = $graph_id", "down");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $graph_item_id") . " where graph_item_id = $graph_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $next_item") . " where graph_item_id = $next_item");
}

function api_graph_item_moveup($graph_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_id = db_fetch_cell("select graph_id from graph_item where id = $graph_item_id");

	$last_item = seq_get_item("graph_item", "sequence", $graph_item_id, "graph_id = $graph_id", "previous");

	seq_move_item("graph_item", $graph_item_id, "graph_id = $graph_id", "up");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $graph_item_id") . " where graph_item_id = $graph_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_item where id = $last_item") . " where graph_item_id = $last_item");
}

function api_graph_item_row_movedown($row_num, $graph_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_item", "graph_id = $graph_id", true, "down");
}

function api_graph_item_row_moveup($row_num, $graph_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_item", "graph_id = $graph_id", true, "up");
}

/* update_graph_title_cache - updates the title cache for a single graph
   @arg $graph_id - (int) the ID of the graph to update the title cache for */
function update_graph_title_cache($graph_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	if (empty($graph_id)) {
		return;
	}

	db_execute("update graph set title_cache = '" . addslashes(get_graph_title($graph_id)) . "' where id = $graph_id");
}

/* update_graph_title_cache_from_template - updates the title cache for all graphs
	that match a given graph template
   @arg $graph_template_id - (int) the ID of the graph template to match */
function update_graph_title_cache_from_template($graph_template_id) {
	$graphs = db_fetch_assoc("select local_graph_id from graph_templates_graph where graph_template_id=$graph_template_id and local_graph_id>0");

	if (sizeof($graphs) > 0) {
	foreach ($graphs as $item) {
		update_graph_title_cache($item["local_graph_id"]);
	}
	}
}

/* update_graph_title_cache_from_query - updates the title cache for all graphs
	that match a given data query/index combination
   @arg $snmp_query_id - (int) the ID of the data query to match
   @arg $snmp_index - the index within the data query to match */
function update_graph_title_cache_from_query($snmp_query_id, $snmp_index) {
	$graphs = db_fetch_assoc("select id from graph_local where snmp_query_id=$snmp_query_id and snmp_index='$snmp_index'");

	if (sizeof($graphs) > 0) {
	foreach ($graphs as $item) {
		update_graph_title_cache($item["id"]);
	}
	}
}

/* update_graph_title_cache_from_host - updates the title cache for all graphs
	that match a given host
   @arg $host_id - (int) the ID of the host to match */
function update_graph_title_cache_from_host($host_id) {
	$graphs = db_fetch_assoc("select id from graph where host_id = $host_id");

	if (sizeof($graphs) > 0) {
		foreach ($graphs as $item) {
			update_graph_title_cache($item["id"]);
		}
	}
}

?>