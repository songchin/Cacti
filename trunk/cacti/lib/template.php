<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

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
		on data_input_fields.id=data_input_data.data_input_field_id
		where data_input_data.data_template_data_id=" . $data_template["id"] . "
		and data_input_fields.input_output='in'");
	
	$data_rra = db_fetch_assoc("select rra_id from data_template_data_rra where data_template_data_id=" . $data_template["id"]);
	
	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		reset($input_fields);
		
		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			/* do not push out "host fields" */
			if (!eregi('^(hostname|snmp_community|snmp_username|snmp_password|snmp_version)$', $input_field["type_code"])) {
				if (empty($input_field["t_value"])) { /* template this value */
					db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values (" . $input_field["id"] . "," . $data_source["id"] . ",'','" . $input_field["value"] . "')");
				}else{
					db_execute("update data_input_data set t_value='on' where data_input_field_id=" . $input_field["id"] . " and data_template_data_id=" . $data_source["id"]);
				}
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

function push_out_data_source_item($data_template_rrd_id) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
	/* get information about this data template */
	$data_template_rrd = db_fetch_row("select * from data_template_rrd where id=$data_template_rrd_id");
	
	/* must be a data template */
	if (empty($data_template_rrd["data_template_id"])) { return 0; }
	
	/* loop through each data source column name (from the above array) */
	while (list($field_name, $field_array) = each($struct_data_source_item)) {
		/* are we allowed to push out the column? */
		if (((empty($data_template_rrd{"t_" . $field_name})) || (ereg("FORCE:", $field_name))) && ((isset($data_template_rrd{"t_" . $field_name})) && (isset($data_template_rrd[$field_name])))) {
			db_execute("update data_template_rrd set $field_name='" . $data_template_rrd[$field_name] . "' where local_data_template_rrd_id=" . $data_template_rrd["id"]); 
		}
	}
}

function push_out_data_source($data_template_data_id) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
	/* get information about this data template */
	$data_template_data = db_fetch_row("select * from data_template_data where id=$data_template_data_id");
	
	/* must be a data template */
	if (empty($data_template_data["data_template_id"])) { return 0; }
	
	/* loop through each data source column name (from the above array) */
	while (list($field_name, $field_array) = each($struct_data_source)) {
		/* are we allowed to push out the column? */
		if (((empty($data_template_data{"t_" . $field_name})) || (ereg("FORCE:", $field_name))) && ((isset($data_template_data{"t_" . $field_name})) && (isset($data_template_data[$field_name])))) {
			db_execute("update data_template_data set $field_name='" . $data_template_data[$field_name] . "' where local_data_template_data_id=" . $data_template_data["id"]); 
			
			/* update the title cache */
			if ($field_name == "name") {
				update_data_source_title_cache_from_template($data_template_data["data_template_id"]);
			}
		}
	}
}

function change_data_template($local_data_id, $data_template_id) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
	/* always update tables to new data template (or no data template) */
	db_execute("update data_local set data_template_id=$data_template_id where id=$local_data_id");
	
	/* get data about the template and the data source */
	$data = db_fetch_row("select * from data_template_data where local_data_id=$local_data_id");
	$template_data = (($data_template_id == "0") ? $data : db_fetch_row("select * from data_template_data where local_data_id=0 and data_template_id=$data_template_id"));
	
	/* determine if we are here for the first time, or coming back */
	if ((db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=$local_data_id") == "0") ||
	(db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=$local_data_id") == "")) {
		$new_save = true;
	}else{
		$new_save = false;
	}
	
	/* some basic field values that ALL data sources should have */
	$save["id"] = $data["id"];
	$save["local_data_template_data_id"] = $template_data["id"];
	$save["local_data_id"] = $local_data_id;
	$save["data_template_id"] = $data_template_id;
	
	/* loop through the "templated field names" to find to the rest... */
	while (list($field_name, $field_array) = each($struct_data_source)) {
		if ((isset($data[$field_name])) || (isset($template_data[$field_name]))) {
			if ((!empty($template_data{"t_" . $field_name})) && ($new_save == false)) {
				$save[$field_name] = $data[$field_name];
			}else{
				$save[$field_name] = $template_data[$field_name];
			}
		}
	}
	
	/* these fields should never be overwritten by the template */
	$save["data_source_path"] = $data["data_source_path"];
	
	$data_template_data_id = sql_save($save, "data_template_data");
	
	$data_rrds_list = db_fetch_assoc("select * from data_template_rrd where local_data_id=$local_data_id");
	$template_rrds_list = (($data_template_id == "0") ? $data_rrds_list : db_fetch_assoc("select * from data_template_rrd where local_data_id=0 and data_template_id=$data_template_id"));
	
	if (sizeof($data_rrds_list) > 0) {
		/* this data source already has "child" items */
	}else{
		/* this data source does NOT have "child" items; loop through each item in the template
		and write it exactly to each item */
		if (sizeof($template_rrds_list) > 0) {
		foreach ($template_rrds_list as $template_rrd) {
			unset($save);
			reset($struct_data_source_item);
			
			$save["id"] = 0;
			$save["local_data_template_rrd_id"] = $template_rrd["id"];
			$save["local_data_id"] = $local_data_id;
			$save["data_template_id"] = $template_rrd["data_template_id"];
			
			while (list($field_name, $field_array) = each($struct_data_source_item)) {
				$save[$field_name] = $template_rrd[$field_name];
			}
			
			sql_save($save, "data_template_rrd");
		}
		}
	}
	
	/* make sure to copy down script data (data_input_data) as well */
	$data_input_data = db_fetch_assoc("select data_input_field_id,t_value,value from data_input_data where data_template_data_id=" . $template_data["id"]);
	
	/* this section is before most everthing else so we can determine if this is a new save, by checking
	the status of the 'local_data_template_data_id' column */
	if (sizeof($data_input_data) > 0) {
	foreach ($data_input_data as $item) {
		/* always propagate on a new save, only propagate templated fields thereafter */
		if (($new_save == true) || (empty($item["t_value"]))) {
			db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values (" . $item["data_input_field_id"] . ",$data_template_data_id,'" . $item["t_value"] . "','" . $item["value"] . "')");
		}
	}
	}
	
	/* make sure to update the 'data_template_data_rra' table for each data source */
	$data_rra = db_fetch_assoc("select rra_id from data_template_data_rra where data_template_data_id=" . $template_data["id"]);
	db_execute("delete from data_template_data_rra where data_template_data_id=$data_template_data_id");
	
	if (sizeof($data_rra) > 0) {
	foreach ($data_rra as $rra) {
		db_execute("insert into data_template_data_rra (data_template_data_id,rra_id) values ($data_template_data_id," . $rra["rra_id"] . ")");
	}
	}
}

/* propagates values from the graph template out to each graph using that template */
function push_out_graph($graph_template_graph_id) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
	/* get information about this graph template */
	$graph_template_graph = db_fetch_row("select * from graph_templates_graph where id=$graph_template_graph_id");
	
	/* must be a graph template */
	if ($graph_template_graph["graph_template_id"] == 0) { return 0; }
	
	/* loop through each graph column name (from the above array) */
	while (list($field_name, $field_array) = each($struct_graph)) {
		/* are we allowed to push out the column? */
		if (empty($graph_template_graph{"t_" . $field_name})) {
			db_execute("update graph_templates_graph set $field_name='$graph_template_graph[$field_name]' where local_graph_template_graph_id=" . $graph_template_graph["id"]);
			
			/* update the title cache */
			if ($field_name == "title") {
				update_graph_title_cache_from_template($graph_template_graph["graph_template_id"]);
			}
		}
	}
}

/* propagates values from the graph template item out to each graph item using that template */
function push_out_graph_item($graph_template_item_id) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
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
	while (list($field_name, $field_array) = each($struct_graph_item)) {
		/* are we allowed to push out the column? */
		if (!isset($graph_item_inputs[$field_name])) {
			db_execute("update graph_templates_item set $field_name='$graph_template_item[$field_name]' where local_graph_template_item_id=" . $graph_template_item["id"]); 
		}
	}
}

function change_graph_template($local_graph_id, $graph_template_id, $intrusive) {
	global $config;
	
	include($config["include_path"] . "/config_form.php");
	
	/* always update tables to new graph template (or no graph template) */
	db_execute("update graph_local set graph_template_id=$graph_template_id where id=$local_graph_id");
	
	/* get information about both the graph and the graph template we're using */
	$graph_list = db_fetch_row("select * from graph_templates_graph where local_graph_id=$local_graph_id");
	$template_graph_list = (($graph_template_id == "0") ? $graph_list : db_fetch_row("select * from graph_templates_graph where local_graph_id=0 and graph_template_id=$graph_template_id"));
	
	/* determine if we are here for the first time, or coming back */
	if ((db_fetch_cell("select local_graph_template_graph_id from graph_templates_graph where local_graph_id=$local_graph_id") == "0") ||
	(db_fetch_cell("select local_graph_template_graph_id from graph_templates_graph where local_graph_id=$local_graph_id") == "")) {
		$new_save = true;
	}else{
		$new_save = false;
	}
	
	/* some basic field values that ALL graphs should have */
	$save["id"] = $graph_list["id"];
	$save["local_graph_template_graph_id"] = $template_graph_list["id"];
	$save["local_graph_id"] = $local_graph_id;
	$save["graph_template_id"] = $graph_template_id;
	
	/* loop through the "templated field names" to find to the rest... */
	while (list($field_name, $field_array) = each($struct_graph)) {
		$value_type = "t_$field_name";
		
		if ((!empty($template_graph_list[$value_type])) && ($new_save == false)) {
			$save[$field_name] = $graph_list[$field_name];
		}else{
			$save[$field_name] = $template_graph_list[$field_name];
		}
	}
	
	sql_save($save, "graph_templates_graph");
	
	$graph_items_list = db_fetch_assoc("select * from graph_templates_item where local_graph_id=$local_graph_id order by sequence");
	$template_items_list = (($graph_template_id == "0") ? $graph_items_list : db_fetch_assoc("select * from graph_templates_item where local_graph_id=0 and graph_template_id=$graph_template_id order by sequence"));
	
	$graph_template_inputs = db_fetch_assoc("select
		graph_template_input.column_name,
		graph_template_input_defs.graph_template_item_id
		from graph_template_input,graph_template_input_defs
		where graph_template_input.id=graph_template_input_defs.graph_template_input_id
		and graph_template_input.graph_template_id=$graph_template_id");
	
	$k=0;
	if (sizeof($template_items_list) > 0) {
	foreach ($template_items_list as $template_item) {
		unset($save);
		reset($struct_graph_item);
		
		$save["local_graph_template_item_id"] = $template_item["id"];
		$save["local_graph_id"] = $local_graph_id;
		$save["graph_template_id"] = $template_item["graph_template_id"];
		
		if (isset($graph_items_list[$k])) {
			/* graph item at this position, "mesh" it in */
			$save["id"] = $graph_items_list[$k]["id"];
			
			/* make a first pass filling in ALL values from template */
			while (list($field_name, $field_array) = each($struct_graph_item)) {
				$save[$field_name] = $template_item[$field_name];
			}
			
			/* go back a second time and fill in the INPUT values from the graph */
			for ($j=0; ($j < count($graph_template_inputs)); $j++) {
				if ($graph_template_inputs[$j]["graph_template_item_id"] == $template_items_list[$k]["id"]) {
					/* if we find out that there is an "input" covering this field/item, use the 
					value from the graph, not the template */
					$graph_item_field_name = $graph_template_inputs[$j]["column_name"];
					$save[$graph_item_field_name] = $graph_items_list[$k][$graph_item_field_name];
				}
			}
		}else{
			/* no graph item at this position, tack it on */
			$save["id"] = 0;
			$save["task_item_id"] = 0;
			
			if ($intrusive == true) {
				while (list($field_name, $field_array) = each($struct_graph_item)) {
					$save[$field_name] = $template_item[$field_name];
				}
			}else{
				unset($save);
			}
			
			
		}
		
		if (isset($save)) {
			sql_save($save, "graph_templates_item");
		}
		
		$k++;
	}
	}
	
	/* if there are more graph items then there are items in the template, delete the difference */
	if ((sizeof($graph_items_list) > sizeof($template_items_list)) && ($intrusive == true)) {
		for ($i=(sizeof($graph_items_list) - (sizeof($graph_items_list) - sizeof($template_items_list))); ($i < count($graph_items_list)); $i++) {
			db_execute("delete from graph_templates_item where id=" . $graph_items_list[$i]["id"]);
		}
	}
	
	return true;
}

function graph_to_graph_template($local_graph_id, $graph_title) {
	/* create a new graph template entry */
	db_execute("insert into graph_templates (id,name) values (0,'" . str_replace("<graph_title>", db_fetch_cell("select title from graph_templates_graph where local_graph_id=$local_graph_id"), $graph_title) . "')");
	$graph_template_id = db_fetch_insert_id();
	
	/* update graph to point to the new template */
	db_execute("update graph_templates_graph set local_graph_id=0,local_graph_template_graph_id=0,graph_template_id=$graph_template_id where local_graph_id=$local_graph_id");
	db_execute("update graph_templates_item set local_graph_id=0,local_graph_template_item_id=0,graph_template_id=$graph_template_id where local_graph_id=$local_graph_id");
	
	/* delete the old graph local entry */
	db_execute("delete from graph_local where id=$local_graph_id");
	db_execute("delete from graph_tree_items where local_graph_id=$local_graph_id");
}

function data_source_to_data_template($local_data_id, $data_source_title) {
	/* create a new graph template entry */
	db_execute("insert into data_template (id,name) values (0,'" . str_replace("<ds_title>", db_fetch_cell("select name from data_template_data where local_data_id=$local_data_id"), $data_source_title) . "')");
	$data_template_id = db_fetch_insert_id();
	
	/* update graph to point to the new template */
	db_execute("update data_template_data set local_data_id=0,local_data_template_data_id=0,data_template_id=$data_template_id where local_data_id=$local_data_id");
	db_execute("update data_template_rrd set local_data_id=0,local_graph_template_item_id=0,data_template_id=$data_template_id where local_data_id=$local_data_id");
	
	/* delete the old graph local entry */
	db_execute("delete from data_local where id=$local_data_id");
	db_execute("delete from data_input_data_cache where local_data_id=$local_data_id");
}

function create_complete_graph_from_template($graph_template_id, $host_id, $snmp_query_array, &$suggested_values_array) {
	/* create the graph */
	$save["id"] = 0;
	$save["graph_template_id"] = $graph_template_id;
	$save["host_id"] = $host_id;
	
	$cache_array["local_graph_id"] = sql_save($save, "graph_local");
	change_graph_template($cache_array["local_graph_id"], $graph_template_id, true);
	
	if (is_array($snmp_query_array)) {
		/* suggested values for snmp query code */
		$suggested_values = db_fetch_assoc("select text,field_name from snmp_query_graph_sv where snmp_query_graph_id=" . $snmp_query_array["snmp_query_graph_id"] . " order by sequence");
		
		if (sizeof($suggested_values) > 0) {
		foreach ($suggested_values as $suggested_value) {
			/* once we find a match; don't try to find more */
			if (!isset($suggested_values_graph[$graph_template_id]{$suggested_value["field_name"]})) {
				$subs_string = subsitute_snmp_query_data($suggested_value["text"], "|", "|", $host_id, $snmp_query_array["snmp_query_id"], $snmp_query_array["snmp_index"]);
				
				/* if there are no '|' characters, all of the subsitutions were successful */
				if (!strstr($subs_string, "|query")) {
					db_execute("update graph_templates_graph set " . $suggested_value["field_name"] . "='" . $suggested_value["text"] . "' where local_graph_id=" . $cache_array["local_graph_id"]);
					
					/* once we find a working value, stop */
					$suggested_values_graph[$graph_template_id]{$suggested_value["field_name"]} = true;
				}
			}
		}
		}
	}
	
	/* suggested values: graph */
	if (isset($suggested_values_array[$graph_template_id]["graph_template"])) {
		while (list($field_name, $field_value) = each($suggested_values_array[$graph_template_id]["graph_template"])) {
			db_execute("update graph_templates_graph set $field_name='$field_value' where local_graph_id=" . $cache_array["local_graph_id"]);
		}
	}
	
	/* suggested values: graph item */
	if (isset($suggested_values_array[$graph_template_id]["graph_template_item"])) {
		while (list($graph_template_item_id, $field_array) = each($suggested_values_array[$graph_template_id]["graph_template_item"])) {
			while (list($field_name, $field_value) = each($field_array)) {
				$graph_item_id = db_fetch_cell("select id from graph_templates_item where local_graph_template_item_id=$graph_template_item_id and local_graph_id=" . $cache_array["local_graph_id"]);
				db_execute("update graph_templates_item set $field_name='$field_value' where id=$graph_item_id");
			}
		}
	}
	
	update_graph_title_cache($cache_array["local_graph_id"]);
	
	/* create each data source */
	$data_templates = db_fetch_assoc("select
		data_template.id,
		data_template.name,
		data_template_rrd.data_source_name
		from data_template, data_template_rrd, graph_templates_item
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.data_template_id=data_template.id
		and data_template_rrd.local_data_id=0
		and graph_templates_item.local_graph_id=0
		and graph_templates_item.graph_template_id=" . $graph_template_id . "
		group by data_template.id
		order by data_template.name");
	
	if (sizeof($data_templates) > 0) {
	foreach ($data_templates as $data_template) {
		unset($save);
		
		$save["id"] = 0;
		$save["data_template_id"] = $data_template["id"];
		$save["host_id"] = $host_id;
		
		$cache_array["local_data_id"]{$data_template["id"]} = sql_save($save, "data_local");
		change_data_template($cache_array["local_data_id"]{$data_template["id"]}, $data_template["id"]);
		
		if (is_array($snmp_query_array)) {
			/* suggested values for snmp query code */
			$suggested_values = db_fetch_assoc("select text,field_name from snmp_query_graph_rrd_sv where snmp_query_graph_id=" . $snmp_query_array["snmp_query_graph_id"] . " and data_template_id=" . $data_template["id"] . " order by sequence");
			
			if (sizeof($suggested_values) > 0) {
			foreach ($suggested_values as $suggested_value) {
				/* once we find a match; don't try to find more */
				if (!isset($suggested_values_ds{$data_template["id"]}{$suggested_value["field_name"]})) {
					$subs_string = subsitute_snmp_query_data($suggested_value["text"], "|", "|", $host_id, $snmp_query_array["snmp_query_id"], $snmp_query_array["snmp_index"]);
					
					/* if there are no '|' characters, all of the subsitutions were successful */
					if (!strstr($subs_string, "|query")) {
						db_execute("update data_template_data set " . $suggested_value["field_name"] . "='" . $suggested_value["text"] . "' where local_data_id=" . $cache_array["local_data_id"]{$data_template["id"]});
						
						/* once we find a working value, stop */
						$suggested_values_ds{$data_template["id"]}{$suggested_value["field_name"]} = true;
					}
				}
			}
			}
		}
		
		if (is_array($snmp_query_array)) {
			$data_input_field_id_index = db_fetch_cell("select data_input_field_id from snmp_query_field where snmp_query_id=" . $snmp_query_array["snmp_query_id"] . " and action_id=1");
			$data_input_field_id_index_value = db_fetch_cell("select data_input_field_id from snmp_query_field where snmp_query_id=" . $snmp_query_array["snmp_query_id"] . " and action_id=2");
			$data_input_field_id_output_type = db_fetch_cell("select data_input_field_id from snmp_query_field where snmp_query_id=" . $snmp_query_array["snmp_query_id"] . " and action_id=3");
			
			$data_template_data_id = db_fetch_cell("select id from data_template_data where local_data_id=" . $cache_array["local_data_id"]{$data_template["id"]});
			$snmp_cache_value = db_fetch_cell("select field_value from host_snmp_cache where host_id=$host_id and field_name='" . $snmp_query_array["snmp_index_on"] . "' and snmp_index='" . $snmp_query_array["snmp_index"] . "'");
			
			/* save the value to index on (ie. ifindex, ifip, etc) */
			db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values ($data_input_field_id_index,$data_template_data_id,'','" . $snmp_query_array["snmp_index_on"] . "')");
			
			/* save the actual value (ie. 3, 192.168.1.101, etc) */
			db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values ($data_input_field_id_index_value,$data_template_data_id,'','$snmp_cache_value')");
			
			/* set the expected output type (ie. bytes, errors, packets) */
			db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values ($data_input_field_id_output_type,$data_template_data_id,'','" . $snmp_query_array["snmp_query_graph_id"] . "')");
			
			/* now that we have put data into the 'data_input_data' table, update the snmp cache for ds's */
			update_data_source_snmp_query_cache($cache_array["local_data_id"]{$data_template["id"]});
		}
		
		/* suggested values: data source */
		if (isset($suggested_values_array[$graph_template_id]["data_template"])) {
			while (list($field_name, $field_value) = each($suggested_values_array[$graph_template_id]["data_template"])) {
				db_execute("update data_template_data set $field_name='$field_value' where local_data_id=" . $cache_array["local_data_id"]{$data_template["id"]});
			}
		}
		
		/* suggested values: data source item */
		if (isset($suggested_values_array[$graph_template_id]["data_template_item"])) {
			while (list($data_template_item_id, $field_array) = each($suggested_values_array[$graph_template_id]["data_template_item"])) {
				while (list($field_name, $field_value) = each($field_array)) {
					$data_source_item_id = db_fetch_cell("select id from data_template_rrd where local_data_template_rrd_id=$data_template_item_id and local_data_id=" . $cache_array["local_data_id"]{$data_template["id"]});
					db_execute("update data_template_rrd set $field_name='$field_value' where id=$data_source_item_id");
				}
			}
		}
		
		update_data_source_title_cache($cache_array["local_data_id"]{$data_template["id"]});
	}
	}
	
	/* connect the dots: graph -> data source(s) */
	$template_item_list = db_fetch_assoc("select
		graph_templates_item.id,
		data_template_rrd.id as data_template_rrd_id,
		data_template_rrd.data_template_id
		from graph_templates_item,data_template_rrd
		where graph_templates_item.task_item_id=data_template_rrd.id
		and graph_templates_item.graph_template_id=$graph_template_id
		and local_graph_id=0
		and task_item_id>0");
	
	/* loop through each item affected and update column data */
	if (sizeof($template_item_list) > 0) {
	foreach ($template_item_list as $template_item) {
		$local_data_id = $cache_array["local_data_id"]{$template_item["data_template_id"]};
						
		$graph_template_item_id = db_fetch_cell("select id from graph_templates_item where local_graph_template_item_id=" . $template_item["id"] . " and local_graph_id=" . $cache_array["local_graph_id"]);
		$data_template_rrd_id = db_fetch_cell("select id from data_template_rrd where local_data_template_rrd_id=" . $template_item["data_template_rrd_id"] . " and local_data_id=$local_data_id");
		
		if (!empty($data_template_rrd_id)) {
			db_execute("update graph_templates_item set task_item_id='$data_template_rrd_id' where id=$graph_template_item_id");
		}
	}
	}
	
	/* this will not work until the ds->graph dots are connected */
	if (is_array($snmp_query_array)) {
		update_graph_snmp_query_cache($cache_array["local_graph_id"]);
	}
	
	return $cache_array;
}

?>
