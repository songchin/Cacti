<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/* push_out_data_source_custom_data - pushes out the "custom data" associated with a data
	template to all of its children. this includes all fields inhereted from the host
	and the data template
   @arg $data_template_id - the id of the data template to push out values for */
function push_out_data_source_custom_data($data_template_id) {
	/* get data_input_id */
	$data_template = db_fetch_row("select
		id,
		data_input_id
		from data_template_data
		where data_template_id=$data_template_id
		and local_data_id=0");

	/* must be a data template */
	if ((empty($data_template_id)) || (empty($data_template["data_input_id"]))) { return 0; }

	/* get a list of data sources using this template */
	$data_sources = db_fetch_assoc("select
		data_template_data.id
		from data_template_data
		where data_template_id=$data_template_id
		and local_data_id>0");

	/* pull out all 'input' values so we know how much to save */
	$input_fields = db_fetch_assoc("select
		data_input_fields.id,
		data_input_fields.type_code,
		data_input_data.value,
		data_input_data.t_value
		from data_input_fields left join data_input_data
		on (data_input_fields.id=data_input_data.data_input_field_id)
		where data_input_data.data_template_data_id=" . $data_template["id"] . "
		and data_input_fields.input_output='in'");

	$data_rra = db_fetch_assoc("select rra_id from data_template_data_rra where data_template_data_id=" . $data_template["id"]);

	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		reset($input_fields);

		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			/* do not push out "host fields" */
			if (!eregi('^' . VALID_HOST_FIELDS . '$', $input_field["type_code"])) {
				/* this is not a "host field", so we should either push out the value if it is templated
				or leave it alone if the user checked "Use Per-Data Source Value". */
				if ($input_field["t_value"] == "") { /* template this value */
					db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,value) values (" . $input_field["id"] . "," . $data_source["id"] . ",'" . $input_field["value"] . "')");
				}
			}elseif (($input_field["t_value"] == "") && ($input_field["value"] != "")) {
				/* we only template a "host field" when the user types something in the field. this way the data
				template always overides the host if the user chooses to do so */
				db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,value) values (" . $input_field["id"] . "," . $data_source["id"] . ",'" . $input_field["value"] . "')");
			}
		}
		}

		/* make sure to update the 'data_template_data_rra' table for each data source */
		db_execute("delete from data_template_data_rra where data_template_data_id=" . $data_source["id"]);

		reset($data_rra);

		if (sizeof($data_rra) > 0) {
		foreach ($data_rra as $rra) {
			db_execute("insert into data_template_data_rra (data_template_data_id,rra_id) values (" . $data_source["id"] . "," . $rra["rra_id"] . ")");
		}
		}
	}
	}
}

/* push_out_graph_input - pushes out the value of a graph input to a single child item. this function
     differs from other push_out_* functions in that it does not push out the value of this element to
     all attached children. instead, it obtains the current value of the graph input based on other
     graph items and pushes out the "active" value
   @arg $graph_template_input_id - the id of the graph input to push out values for
   @arg $graph_template_item_id - the id the graph template item to push out
   @arg $session_members - when looking for the "active" value of the graph input, ignore these graph
     template items. typically you want to ignore all items that were just selected and have yet to be
     saved to the database. this is because these items most likely contain incorrect data */
function push_out_graph_input($graph_template_input_id, $graph_template_item_id, $session_members) {
	$graph_input = db_fetch_row("select graph_template_id,column_name from graph_template_input where id=$graph_template_input_id");
	$graph_input_items = db_fetch_assoc("select graph_template_item_id from graph_template_input_defs where graph_template_input_id=$graph_template_input_id");

	$i = 0;
	if (sizeof($graph_input_items) > 0) {
	foreach ($graph_input_items as $item) {
		$include_items[$i] = $item["graph_template_item_id"];
		$i++;
	}
	}

	/* we always want to make sure to stay within the same graph item input, so make a list of each
	item included in this input to be included in the sql query */
	if (isset($include_items)) {
		$sql_include_items = "and " . array_to_sql_or($include_items, "local_graph_template_item_id");
	}else{
		$sql_include_items = "and 0=1";
	}

	if (sizeof($session_members) == 0) {
		$values_to_apply = db_fetch_assoc("select local_graph_id," . $graph_input["column_name"] . " from graph_templates_item where graph_template_id=" . $graph_input["graph_template_id"] . " $sql_include_items and local_graph_id>0 group by local_graph_id");
	}else{
		$i = 0;
		while (list($item_id, $item_id) = each($session_members)) {
			$new_session_members[$i] = $item_id;
			$i++;
		}

		$values_to_apply = db_fetch_assoc("select local_graph_id," . $graph_input["column_name"] . " from graph_templates_item where graph_template_id=" . $graph_input["graph_template_id"] . " and local_graph_id>0 and !(" . array_to_sql_or($new_session_members, "local_graph_template_item_id") . ") $sql_include_items group by local_graph_id");
	}

	if (sizeof($values_to_apply) > 0) {
	foreach ($values_to_apply as $value) {
		/* this is just an extra check that i threw in to prevent users' graphs from getting really messed up */
		if (!(($graph_input["column_name"] == "task_item_id") && (empty($value{$graph_input["column_name"]})))) {
			db_execute("update graph_templates_item set " . $graph_input["column_name"] . "='" . $value{$graph_input["column_name"]} . "' where local_graph_id=" . $value["local_graph_id"] . " and local_graph_template_item_id=$graph_template_item_id");
		}
	}
	}
}

/* push_out_graph_item - pushes out templated graph template item fields to all matching
     children. if the graph template item is part of a graph input, the field will not be
     pushed out
   @arg $graph_template_item_id - the id of the graph template item to push out values for */
function push_out_graph_item($graph_template_item_id) {
	global $struct_graph_item;

	/* get information about this graph template */
	$graph_template_item = db_fetch_row("select * from graph_templates_item where id=$graph_template_item_id");

	/* must be a graph template */
	if ($graph_template_item["graph_template_id"] == 0) { return 0; }

	/* find out if any graphs actual contain this item */
	if (sizeof(db_fetch_assoc("select id from graph_templates_item where local_graph_template_item_id=$graph_template_item_id")) == 0) {
		/* if not, reapply the template to push out the new item */
		$attached_graphs = db_fetch_assoc("select local_graph_id from graph_templates_graph where graph_template_id=" . $graph_template_item["graph_template_id"] . " and local_graph_id>0");

		if (sizeof($attached_graphs) > 0) {
		foreach ($attached_graphs as $item) {
			change_graph_template($item["local_graph_id"], $graph_template_item["graph_template_id"], true);
		}
		}
	}

	/* this is trickier with graph_items than with the actual graph... we have to make sure not to
	overwrite any items covered in the "graph item inputs". the same thing applies to graphs, but
	is easier to detect there (t_* columns). */
	$graph_item_inputs = db_fetch_assoc("select
		graph_template_input.column_name,
		graph_template_input_defs.graph_template_item_id
		from graph_template_input, graph_template_input_defs
		where graph_template_input.graph_template_id=" . $graph_template_item["graph_template_id"] . "
		and graph_template_input.id=graph_template_input_defs.graph_template_input_id
		and graph_template_input_defs.graph_template_item_id=$graph_template_item_id");

	$graph_item_inputs = array_rekey($graph_item_inputs, "column_name", "graph_template_item_id");

	/* loop through each graph item column name (from the above array) */
	reset($struct_graph_item);
	while (list($field_name, $field_array) = each($struct_graph_item)) {
		/* are we allowed to push out the column? */
		if (!isset($graph_item_inputs[$field_name])) {
			db_execute("update graph_templates_item set $field_name='$graph_template_item[$field_name]' where local_graph_template_item_id=" . $graph_template_item["id"]);
		}
	}
}

/* graph_to_graph_template - converts a graph to a graph template
   @arg $local_graph_id - the id of the graph to be converted
   @arg $graph_title - the graph title to use for the new graph template. the variable
	<graph_title> will be substituted for the current graph title */
function graph_to_graph_template($local_graph_id, $graph_title) {
	/* create a new graph template entry */
	db_execute("insert into graph_templates (id,name,hash) values (0,'" . str_replace("<graph_title>", db_fetch_cell("select title from graph_templates_graph where local_graph_id=$local_graph_id"), $graph_title) . "','" . get_hash_graph_template(0) . "')");
	$graph_template_id = db_fetch_insert_id();

	/* update graph to point to the new template */
	db_execute("update graph_templates_graph set local_graph_id=0,local_graph_template_graph_id=0,graph_template_id=$graph_template_id where local_graph_id=$local_graph_id");
	db_execute("update graph_templates_item set local_graph_id=0,local_graph_template_item_id=0,graph_template_id=$graph_template_id,task_item_id=0 where local_graph_id=$local_graph_id");

	/* create hashes for the graph template items */
	$items = db_fetch_assoc("select id from graph_templates_item where graph_template_id='$graph_template_id' and local_graph_id=0");
	for ($j=0; $j<count($items); $j++) {
		db_execute("update graph_templates_item set hash='" . get_hash_graph_template($items[$j]["id"], "graph_template_item") . "' where id=" . $items[$j]["id"]);
	}

	/* delete the old graph local entry */
	db_execute("delete from graph_local where id=$local_graph_id");
	db_execute("delete from graph_tree_items where local_graph_id=$local_graph_id");
}

/* data_source_to_data_template - converts a data source to a data template
   @arg $local_data_id - the id of the data source to be converted
   @arg $data_source_title - the data source title to use for the new data template. the variable
	<ds_title> will be substituted for the current data source title */
function data_source_to_data_template($local_data_id, $data_source_title) {
	/* create a new graph template entry */
	db_execute("insert into data_template (id,name,hash) values (0,'" . str_replace("<ds_title>", db_fetch_cell("select name from data_template_data where local_data_id=$local_data_id"), $data_source_title) . "','" .  get_hash_data_template(0) . "')");
	$data_template_id = db_fetch_insert_id();

	/* update graph to point to the new template */
	db_execute("update data_template_data set local_data_id=0,local_data_template_data_id=0,data_template_id=$data_template_id where local_data_id=$local_data_id");
	db_execute("update data_template_rrd set local_data_id=0,local_data_template_rrd_id=0,data_template_id=$data_template_id where local_data_id=$local_data_id");

	/* create hashes for the data template items */
	$items = db_fetch_assoc("select id from data_template_rrd where data_template_id='$data_template_id' and local_data_id=0");
	for ($j=0; $j<count($items); $j++) {
		db_execute("update data_template_rrd set hash='" . get_hash_data_template($items[$j]["id"], "data_template_item") . "' where id=" . $items[$j]["id"]);
	}

	/* delete the old graph local entry */
	db_execute("delete from data_local where id=$local_data_id");
	db_execute("delete from poller_item where local_data_id=$local_data_id");
}

?>