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

function repopulate_poller_cache() {
	db_execute("truncate table data_input_data_cache");
	
	$poller_data = db_fetch_assoc("select id from data_local");
	
	if (sizeof($poller_data) > 0) {
	foreach ($poller_data as $data) {
		update_poller_cache($data["id"]);
	}
	}
}

function update_poller_cache($local_data_id) {
	global $config;
	
	include_once($config["include_path"] . "/snmp_functions.php");
	
	$data_input = db_fetch_row("select
		data_input.id,
		data_input.type_id,
		data_template_data.id as data_template_data_id,
		data_template_data.data_template_id,
		data_template_data.active
		from data_template_data,data_input
		where data_template_data.data_input_id=data_input.id
		and data_template_data.local_data_id=$local_data_id");
	
	$host = db_fetch_row("select
		host.id,
		host.management_ip,
		host.snmp_community,
		host.snmp_version,
		host.snmp_username,
		host.snmp_password
		from
		data_local,host
		where data_local.host_id=host.id
		and data_local.id=$local_data_id
		and host.disabled=''");
	
	/* we have to perform some additional sql queries if this is a "query" */
	if (($data_input["type_id"] == "3") || ($data_input["type_id"] == "4")) {
		$field = data_query_field_list($data_input["data_template_data_id"]);
		
		if (empty($field)) { return; }
		
		$query = data_query_index($field["index_type"], $field["index_value"], $host["id"]);
		
		$outputs = db_fetch_assoc("select
			snmp_query_graph_rrd.snmp_field_name,
			data_template_rrd.id as data_template_rrd_id
			from snmp_query_graph_rrd,data_template_rrd
			where snmp_query_graph_rrd.data_template_rrd_id=data_template_rrd.local_data_template_rrd_id
			and snmp_query_graph_rrd.snmp_query_graph_id=" . $field["output_type"] . "
			and snmp_query_graph_rrd.data_template_id=" . $data_input["data_template_id"] . "
			and data_template_rrd.local_data_id=$local_data_id");
	}
	
	/* clear cache for this local_data_id */
	db_execute("delete from data_input_data_cache where local_data_id=$local_data_id");
	
	if ($data_input["active"] == "on") {
		switch ($data_input["type_id"]) {
		case '1': /* script */
			$action_type = 0;
			$data_template_rrd_id = 0;
			
			$command = get_full_script_path($local_data_id);
			
			$num_output_fields = sizeof(db_fetch_assoc("select id from data_input_fields where data_input_id=" . $data_input["id"] . " and input_output='out'"));
			
			if ($num_output_fields == 1) {
				$action_type = 1; /* one ds */
				
				$data_template_rrd = db_fetch_assoc("select id from data_template_rrd where local_data_id=$local_data_id");
				$data_template_rrd_id = $data_template_rrd[0]["id"];
			}elseif ($num_output_fields > 1) {
				$action_type = 2; /* >= two ds */
				
				db_execute("delete from data_input_data_fcache where local_data_id=$local_data_id");
				
				/* update the field cache (fcache) */
				$names = db_fetch_assoc("select
					data_template_rrd.data_source_name,
					data_input_fields.data_name
					from data_template_rrd,data_input_fields
					where data_template_rrd.data_input_field_id=data_input_fields.id
					and data_template_rrd.local_data_id=$local_data_id");
				
				if (sizeof($names) > 0) {
				foreach ($names as $name) {
					db_execute("insert into data_input_data_fcache (local_data_id,data_input_field_name,rrd_data_source_name)
						values ($local_data_id,'" . $name["data_name"] . "','" . $name["data_source_name"] . "')");
				}
				}
			}
			
			if ($action_type) {
				db_execute("insert into data_input_data_cache (local_data_id,host_id,data_input_id,action,management_ip,
					snmp_community,snmp_version,snmp_username,snmp_password,rrd_name,rrd_path,
					command,rrd_num) values ($local_data_id," . (empty($host["id"]) ? 0 : $host["id"]) . "," . $data_input["id"] . ",$action_type,'" . $host["management_ip"] . "',
					'" . $host["snmp_community"] . "','" . $host["snmp_version"] . "',
					'" . $host["snmp_username"] . "','" . $host["snmp_password"] . "',
					'" . get_data_source_name($data_template_rrd_id) . "',
					'" . get_data_source_path($local_data_id,true) . "','$command',1)");
			}
			
			break;
		case '2': /* snmp */
			$field = db_fetch_assoc("select
				data_input_fields.type_code,
				data_input_data.value
				from data_input_fields left join data_input_data
				on (data_input_fields.id=data_input_data.data_input_field_id and data_input_data.data_template_data_id=" . $data_input["data_template_data_id"] . ")
				where data_input_fields.type_code='snmp_oid'");
			$field = array_rekey($field, "type_code", "value");
			
			$data_template_rrd_id = db_fetch_cell("select id from data_template_rrd where local_data_id=$local_data_id");
			
			db_execute("insert into data_input_data_cache (local_data_id,host_id,data_input_id,action,management_ip,
				snmp_community,snmp_version,snmp_username,snmp_password,rrd_name,rrd_path,
				arg1,rrd_num) values ($local_data_id," . (empty($host["id"]) ? 0 : $host["id"]) . "," . $data_input["id"]. ",0,'" . $host["management_ip"] . "',
				'" . $host["snmp_community"] . "','" . $host["snmp_version"] . "',
				'" . $host["snmp_username"] . "','" . $host["snmp_password"] . "',
				'" . get_data_source_name($data_template_rrd_id) . "',
				'" . get_data_source_path($local_data_id,true,1) . "','" . $field["snmp_oid"] . "',1)");
			
			break;
		case '3': /* snmp query */
			$snmp_queries = get_data_query_array($query["snmp_query_id"]);
			
			if (sizeof($outputs) > 0) {
			foreach ($outputs as $output) {
				if (isset($snmp_queries["fields"][0]{$output["snmp_field_name"]}[0]["oid"])) {
					$oid = $snmp_queries["fields"][0]{$output["snmp_field_name"]}[0]["oid"] . "." . $query["snmp_index"];
				}
				
				if (!empty($oid)) {
					db_execute("insert into data_input_data_cache (local_data_id,host_id,data_input_id,action,management_ip,
						snmp_community,snmp_version,snmp_username,snmp_password,rrd_name,rrd_path,
						arg1,rrd_num) values ($local_data_id," . (empty($host["id"]) ? 0 : $host["id"]) . "," . $data_input["id"]. ",0,'" . $host["management_ip"] . "',
						'" . $host["snmp_community"] . "','" . $host["snmp_version"] . "',
						'" . $host["snmp_username"] . "','" . $host["snmp_password"] . "',
						'" . get_data_source_name($output["data_template_rrd_id"]) . "',
						'" . get_data_source_path($local_data_id,true) . "','$oid'," . sizeof($outputs) . ")");
				}
			}
			}
			
			break;
		case '4': /* script query */
			$script_queries = get_data_query_array($query["snmp_query_id"]);
			
			if (sizeof($outputs) > 0) {
			foreach ($outputs as $output) {
				if (isset($script_queries["fields"][0]{$output["snmp_field_name"]}[0]["query_name"])) {
					$identifier = $script_queries["fields"][0]{$output["snmp_field_name"]}[0]["query_name"];
					
					/* get any extra arguments that need to be passed to the script */
					if (!empty($script_queries["arg_prepend"])) {
						$extra_arguments = subsitute_host_data($script_queries["arg_prepend"], "|", "|", $host["id"]);
					}else{
						$extra_arguments = "";
					}
					
					/* get a complete path for out target script */
					$script_path = subsitute_data_query_path($script_queries["script_path"]);
					$script_path .= " $extra_arguments " . $script_queries["arg_get"] . " " . $identifier . " " . $query["snmp_index"];
				}
				
				if (isset($script_path)) {
					db_execute("insert into data_input_data_cache (local_data_id,host_id,data_input_id,action,management_ip,
						snmp_community,snmp_version,snmp_username,snmp_password,rrd_name,rrd_path,command,rrd_num) values 
						($local_data_id," . (empty($host["id"]) ? 0 : $host["id"]) . "," . $data_input["id"]. ",1,'" . $host["management_ip"] . "',
						'" . $host["snmp_community"] . "','" . $host["snmp_version"] . "',
						'" . $host["snmp_username"] . "','" . $host["snmp_password"] . "',
						'" . get_data_source_name($output["data_template_rrd_id"]) . "',
						'" . get_data_source_path($local_data_id,true) . "','$script_path'," . sizeof($outputs) . ")");
				}
			}
			}
			
			break;
		}
	}
}

function update_graph_snmp_query_cache($local_graph_id) {
	$host_id = db_fetch_cell("select host_id from graph_local where id=$local_graph_id");
	
	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from graph_templates_item,data_template_rrd,data_template_data 
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and graph_templates_item.local_graph_id=$local_graph_id
		limit 0,1"));
	
	if (empty($field)) { return; }
	
	$query = data_query_index($field["index_type"], $field["index_value"], $host_id);
	
	if (($query["snmp_query_id"] != "0") && ($query["snmp_index"] != "")) {
		db_execute("update graph_local set snmp_query_id=" . $query["snmp_query_id"] . ",snmp_index='" . $query["snmp_index"] . "' where id=$local_graph_id");
		
		/* update data source/graph title cache */
		update_data_source_title_cache_from_query($query["snmp_query_id"], $query["snmp_index"]);
		update_graph_title_cache_from_query($query["snmp_query_id"], $query["snmp_index"]);
	}
}

function update_data_source_snmp_query_cache($local_data_id) {
	$host_id = db_fetch_cell("select host_id from data_local where id=$local_data_id");
	
	$field = data_query_field_list(db_fetch_cell("select
		data_template_data.id
		from data_template_data 
		where data_template_data.local_data_id=$local_data_id"));
	
	if (empty($field)) { return; }
	
	$query = data_query_index($field["index_type"], $field["index_value"], $host_id);
	
	if (($query["snmp_query_id"] != "0") && ($query["snmp_index"] != "")) {
		db_execute("update data_local set snmp_query_id=" . $query["snmp_query_id"] . ",snmp_index='" . $query["snmp_index"] . "' where id=$local_data_id");
		
		/* update graph title cache */
		update_graph_title_cache_from_query($query["snmp_query_id"], $query["snmp_index"]);
	}
}

function push_out_data_template($data_template_id) {
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
	
	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		reset($input_fields);
		
		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			/* do not push out "host fields" */
			if (!eregi('^(hostname|management_ip|snmp_community|snmp_username|snmp_password|snmp_version)$', $input_field["type_code"])) {
				if (empty($input_field["t_value"])) { /* template this value */
					db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values (" . $input_field["id"] . "," . $data_source["id"] . ",'','" . $input_field["value"] . "')");
				}else{
					db_execute("update data_input_data set t_value='on' where data_input_field_id=" . $input_field["id"] . " and data_template_data_id=" . $data_source["id"]);
				}
			}
		}
		}
	}
	}	
}

function push_out_data_source_item($data_template_rrd_id) {
	global $config;
	
	include($config["include_path"] . "/config_arrays.php");
	
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
	
	include($config["include_path"] . "/config_arrays.php");
	
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

function push_out_host($host_id, $local_data_id = 0) {
	/* ok here's the deal: first we need to find every data source that uses this host.
	then we go through each of those data sources, finding each one using a data input method
	with "special fields". if we find one, fill it will the data here from this host */
	
	if (empty($host_id)) { return 0; }
	
	/* get all information about this host so we can write it to the data source */
	$host = db_fetch_row("select hostname,management_ip,snmp_community,snmp_username,snmp_password,snmp_version from host where id=$host_id");
	
	$data_sources = db_fetch_assoc("select
		data_template_data.id,
		data_template_data.data_input_id,
		data_template_data.local_data_id
		from data_local,data_template_data
		where " . (empty($local_data_id) ? "data_local.host_id=$host_id" : "data_local.id=$local_data_id") . "
		and data_local.id=data_template_data.local_data_id
		and data_template_data.data_input_id>0");
	
	/* loop through each matching data source */
	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		$input_fields = db_fetch_assoc("select
			data_input_fields.id,
			data_input_fields.type_code
			from data_input_fields
			where data_input_fields.data_input_id=" . $data_source["data_input_id"] . "
			and data_input_fields.input_output='in'
			and data_input_fields.type_code!=''");
		
		/* loop through each matching field (must be special field) */
		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			/* fetch the appropriate data from this host based on the 'type_code'
			  -- note: the type code name comes straight from the column names of the 'host'
			           table, just fyi */
			
			/* make sure it is HOST related type code */
			if (eregi('^(hostname|management_ip|snmp_community|snmp_username|snmp_password|snmp_version)$', $input_field["type_code"])) {
				db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,value) values (" . $input_field["id"] . "," . $data_source["id"] . ",'" . $host{$input_field["type_code"]} . "')");
			}
		}
		}
		
		/* make sure to update the poller cache as well */
		update_poller_cache($data_source["local_data_id"]);
	}
	}
}

function change_data_template($local_data_id, $data_template_id) {
	global $config;
	
	include($config["include_path"] . "/config_arrays.php");
	
	/* always update tables to new data template (or no data template) */
	db_execute("update data_template_data set data_template_id=$data_template_id where local_data_id=$local_data_id");
	db_execute("update data_template_rrd set data_template_id=$data_template_id where local_data_id=$local_data_id");
	db_execute("update data_local set data_template_id=$data_template_id where id=$local_data_id");
	
	/* get data about the template and the data source */
	$data = db_fetch_row("select * from data_template_data where local_data_id=$local_data_id");
	$template_data = db_fetch_row("select * from data_template_data where local_data_id=0 and data_template_id=$data_template_id");
	
	/* determine if we are here for the first time, or coming back */
	if ((db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=$local_data_id") == "0") ||
	(db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=$local_data_id") == "")) {
		$new_save = true;
	}else{
		$new_save = false;
	}
	
	/* make sure the 'local_data_template_data_id' column is set */
	$local_data_template_data_id = db_fetch_cell("select id from data_template_data where data_template_id=$data_template_id and data_template_id=id");
	
	if ($local_data_template_data_id == "") { $local_data_template_data_id = 0; }
	db_execute("update data_template_data set local_data_template_data_id=$local_data_template_data_id where local_data_id=$local_data_id");
	
	/* if the user turned off the template for this data source; there is nothing more to do here */
	if ($data_template_id == "0") { return 0; }
	
	/* some basic field values that ALL data sources should have */
	$save["id"] = $data["id"];
	$save["local_data_template_data_id"] = $template_data["id"];
	$save["local_data_id"] = $local_data_id;
	$save["data_template_id"] = $data_template_id;
	
	/* loop through the "templated field names" to find to the rest... */
	while (list($field_name, $field_array) = each($struct_data_source)) {
		if ($field_array["type"] != "custom") {
			if ((!empty($template_data{"t_" . $field_name})) && ($new_save == false)) {
				$save[$field_name] = $data[$field_name];
			}else{
				$save[$field_name] = $template_data[$field_name];
			}
		}
	}
	
	/* these fields should never be overwritten by the template */
	$save["data_source_path"] = $data["data_source_path"];
	
	//print "<pre>";print_r($save);print "</pre>";
	$data_template_data_id = sql_save($save, "data_template_data");
	
	$data_rrds_list = db_fetch_assoc("select * from data_template_rrd where local_data_id=$local_data_id");
	$template_rrds_list = db_fetch_assoc("select * from data_template_rrd where local_data_id=0 and data_template_id=$data_template_id");
	
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
			
			//print "<pre>";print_r($save);print "</pre>";
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
	
	/* find out if there is a host and a host template involved, if there is... push out the 
	host template's settings */
	$host_id = db_fetch_cell("select host_id from data_local where id=$local_data_id");
	
	if ($host_id != "0") {
		$host_template_id = db_fetch_cell("select host_template_id from host where id=$host_id");
		if ($host_template_id != "0") {
			//push_out_host_template($host_template_id, $data_template_id);
		}
	}
}

/* propagates values from the graph template out to each graph using that template */
function push_out_graph($graph_template_graph_id) {
	global $config;
	
	include ($config["include_path"] . "/config_arrays.php");
	
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
	
	include ($config["include_path"] . "/config_arrays.php");
	
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
	overright any items covered in the "graph item inputs". the same thing applies to graphs, but
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
	
	include($config["include_path"] . "/config_arrays.php");
	
	/* always update tables to new graph template (or no graph template) */
	db_execute("update graph_templates_graph set graph_template_id=$graph_template_id where local_graph_id=$local_graph_id");
	db_execute("update graph_templates_item set graph_template_id=$graph_template_id where local_graph_id=$local_graph_id");
	db_execute("update graph_local set graph_template_id=$graph_template_id where id=$local_graph_id");
	
	/* make sure the 'local_graph_template_graph_id' column is set */
	$local_graph_template_graph_id = db_fetch_cell("select id from graph_templates_graph where graph_template_id=$graph_template_id and graph_template_id=id");
	
	if ($local_graph_template_graph_id == "") { $local_graph_template_graph_id = 0; }
	db_execute("update graph_templates_graph set local_graph_template_graph_id=$local_graph_template_graph_id where local_graph_id=$local_graph_id");
	
	/* if the user turned off the template for this graph; there is nothing more to do here */
	if ($graph_template_id == "0") { return 0; }
	
	/* get information about both the graph and the graph template we're using */
	$graph_list = db_fetch_row("select * from graph_templates_graph where local_graph_id=$local_graph_id");
	$template_graph_list = db_fetch_row("select * from graph_templates_graph where local_graph_id=0 and graph_template_id=$graph_template_id");
	
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
	
	//print "<pre>";print_r($save);print "</pre>";
	sql_save($save, "graph_templates_graph");
	
	$graph_items_list = db_fetch_assoc("select * from graph_templates_item where local_graph_id=$local_graph_id order by sequence");
	$template_items_list = db_fetch_assoc("select * from graph_templates_item where local_graph_id=0 and graph_template_id=$graph_template_id order by sequence");
	
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
		
		//print "<pre>";print_r($save);print "</pre>";
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

function duplicate_graph($_local_graph_id, $_graph_template_id, $graph_title) {
	global $config;
	
	include($config["include_path"] . "/config_arrays.php");
	
	if (!empty($_local_graph_id)) {
		$graph_local = db_fetch_row("select * from graph_local where id=$_local_graph_id");
		$graph_template_graph = db_fetch_row("select * from graph_templates_graph where local_graph_id=$_local_graph_id");
		$graph_template_items = db_fetch_assoc("select * from graph_templates_item where local_graph_id=$_local_graph_id");
		
		/* create new entry: graph_local */
		$save["id"] = 0;
		$save["graph_template_id"] = $graph_local["graph_template_id"];
		$save["host_id"] = $graph_local["host_id"];
		$save["snmp_query_id"] = $graph_local["snmp_query_id"];
		$save["snmp_index"] = $graph_local["snmp_index"];
		
		$local_graph_id = sql_save($save, "graph_local");
		
		$graph_template_graph["title"] = str_replace("<graph_title>", $graph_template_graph["title"], $graph_title);
	}elseif (!empty($_graph_template_id)) {
		$graph_template = db_fetch_row("select * from graph_templates where id=$_graph_template_id");
		$graph_template_graph = db_fetch_row("select * from graph_templates_graph where graph_template_id=$_graph_template_id and local_graph_id=0");
		$graph_template_items = db_fetch_assoc("select * from graph_templates_item where graph_template_id=$_graph_template_id and local_graph_id=0");
		$graph_template_inputs = db_fetch_assoc("select * from graph_template_input where graph_template_id=$_graph_template_id");
		
		/* create new entry: graph_templates */
		$save["id"] = 0;
		$save["name"] = str_replace("<template_title>", $graph_template["name"], $graph_title);
		
		$graph_template_id = sql_save($save, "graph_templates");
	}
	
	unset($save);
	reset($struct_graph);
	
	/* create new entry: graph_templates_graph */
	$save["id"] = 0;
	$save["local_graph_id"] = (isset($local_graph_id) ? $local_graph_id : 0);
	$save["local_graph_template_graph_id"] = (isset($graph_template_graph["local_graph_template_graph_id"]) ? $graph_template_graph["local_graph_template_graph_id"] : 0);
	$save["graph_template_id"] = (!empty($_local_graph_id) ? $graph_template_graph["graph_template_id"] : $graph_template_id);
	$save["title_cache"] = $graph_template_graph["title_cache"];
	
	while (list($field, $array) = each($struct_graph)) {
		$save{$field} = $graph_template_graph{$field};
		$save{"t_" . $field} = $graph_template_graph{"t_" . $field};
	}
	
	$graph_templates_graph_id = sql_save($save, "graph_templates_graph");
	
	/* create new entry(s): graph_templates_item */
	if (sizeof($graph_template_items) > 0) {
	foreach ($graph_template_items as $graph_template_item) {
		unset($save);
		reset($struct_graph_item);
		
		$save["id"] = 0;
		$save["local_graph_id"] = (isset($local_graph_id) ? $local_graph_id : 0);
		$save["graph_template_id"] = (!empty($_local_graph_id) ? $graph_template_item["graph_template_id"] : $graph_template_id);
		$save["local_graph_template_item_id"] = (isset($graph_template_item["local_graph_template_item_id"]) ? $graph_template_item["local_graph_template_item_id"] : 0);
		
		while (list($field, $array) = each($struct_graph_item)) {
			$save{$field} = $graph_template_item{$field};
		}
		
		$graph_item_mappings{$graph_template_item["id"]} = sql_save($save, "graph_templates_item");
	}
	}
	
	if (!empty($_graph_template_id)) {
		/* create new entry(s): graph_template_input (graph template only) */
		if (sizeof($graph_template_inputs) > 0) {
		foreach ($graph_template_inputs as $graph_template_input) {
			unset($save);
			
			$save["id"] = 0;
			$save["graph_template_id"] = $graph_template_id;
			$save["name"] = $graph_template_input["name"];
			$save["description"] = $graph_template_input["description"];
			$save["column_name"] = $graph_template_input["column_name"];
			
			$graph_template_input_id = sql_save($save, "graph_template_input");
			
			$graph_template_input_defs = db_fetch_assoc("select * from graph_template_input_defs where graph_template_input_id=" . $graph_template_input["id"]);
			
			/* create new entry(s): graph_template_input_defs (graph template only) */
			if (sizeof($graph_template_input_defs) > 0) {
			foreach ($graph_template_input_defs as $graph_template_input_def) {
				db_execute("insert into graph_template_input_defs (graph_template_input_id,graph_template_item_id)
					values ($graph_template_input_id," . $graph_item_mappings{$graph_template_input_def["graph_template_item_id"]} . ")");
			}
			}
		}
		}
	}
	
	if (!empty($_local_graph_id)) {
		update_graph_title_cache($local_graph_id);
	}
}

function duplicate_data_source($_local_data_id, $_data_template_id, $data_source_title) {
	global $config;
	
	include ($config["include_path"] . "/config_arrays.php");
	
	if (!empty($_local_data_id)) {
		$data_local = db_fetch_row("select * from data_local where id=$_local_data_id");
		$data_template_data = db_fetch_row("select * from data_template_data where local_data_id=$_local_data_id");
		$data_template_rrds = db_fetch_assoc("select * from data_template_rrd where local_data_id=$_local_data_id");
		
		$data_input_datas = db_fetch_assoc("select * from data_input_data where data_template_data_id=" . $data_template_data["id"]);
		$data_template_data_rras = db_fetch_assoc("select * from data_template_data_rra where data_template_data_id=" . $data_template_data["id"]);
		
		/* create new entry: data_local */
		$save["id"] = 0;
		$save["data_template_id"] = $data_local["data_template_id"];
		$save["host_id"] = $data_local["host_id"];
		$save["snmp_query_id"] = $data_local["snmp_query_id"];
		$save["snmp_index"] = $data_local["snmp_index"];
		
		$local_data_id = sql_save($save, "data_local");
		
		$data_template_data["name"] = str_replace("<ds_title>", $data_template_data["name"], $data_source_title);
	}elseif (!empty($_data_template_id)) {
		$data_template = db_fetch_row("select * from data_template where id=$_data_template_id");
		$data_template_data = db_fetch_row("select * from data_template_data where data_template_id=$_data_template_id and local_data_id=0");
		$data_template_rrds = db_fetch_assoc("select * from data_template_rrd where data_template_id=$_data_template_id and local_data_id=0");
		
		$data_input_datas = db_fetch_assoc("select * from data_input_data where data_template_data_id=" . $data_template_data["id"]);
		$data_template_data_rras = db_fetch_assoc("select * from data_template_data_rra where data_template_data_id=" . $data_template_data["id"]);
		
		/* create new entry: data_template */
		$save["id"] = 0;
		$save["name"] = str_replace("<template_title>", $data_template["name"], $data_source_title);
		
		$data_template_id = sql_save($save, "data_template");
	}
	
	unset($save);
	unset($struct_data_source["rra_id"]);
	unset($struct_data_source["data_source_path"]);
	reset($struct_data_source);
	
	/* create new entry: data_template_data */
	$save["id"] = 0;
	$save["local_data_id"] = (isset($local_data_id) ? $local_data_id : 0);
	$save["local_data_template_data_id"] = (isset($data_template_data["local_data_template_data_id"]) ? $data_template_data["local_data_template_data_id"] : 0);
	$save["data_template_id"] = (!empty($_local_data_id) ? $data_template_data["data_template_id"] : $data_template_id);
	$save["name_cache"] = $data_template_data["name_cache"];
	
	while (list($field, $array) = each($struct_data_source)) {
		$save{$field} = $data_template_data{$field};
		
		if ($array["flags"] != "ALWAYSTEMPLATE") {
			$save{"t_" . $field} = $data_template_data{"t_" . $field};
		}
	}
	
	$data_template_data_id = sql_save($save, "data_template_data");
	
	/* create new entry(s): data_template_rrd */
	if (sizeof($data_template_rrds) > 0) {
	foreach ($data_template_rrds as $data_template_rrd) {
		unset($save);
		reset($struct_data_source_item);
		
		$save["id"] = 0;
		$save["local_data_id"] = (isset($local_data_id) ? $local_data_id : 0);
		$save["local_data_template_rrd_id"] = (isset($data_template_rrd["local_data_template_rrd_id"]) ? $data_template_rrd["local_data_template_rrd_id"] : 0);
		$save["data_template_id"] = (!empty($_local_data_id) ? $data_template_rrd["data_template_id"] : $data_template_id);
		
		while (list($field, $array) = each($struct_data_source_item)) {
			$save{$field} = $data_template_rrd{$field};
		}
		
		$data_template_rrd_id = sql_save($save, "data_template_rrd");
	}
	}
	
	/* create new entry(s): data_input_data */
	if (sizeof($data_input_datas) > 0) {
	foreach ($data_input_datas as $data_input_data) {
		$save["data_input_field_id"] = $data_input_data["data_input_field_id"];
		$save["data_template_data_id"] = $data_template_data_id;
		$save["t_value"] = $data_input_data["t_value"];
		$save["value"] = $data_input_data["value"];
		
		db_execute("insert into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values
			(" . $data_input_data["data_input_field_id"] . ",$data_template_data_id,'" . $data_input_data["t_value"] . 
			"','" . $data_input_data["value"] . "')");
	}
	}
	
	/* create new entry(s): data_template_data_rra */
	if (sizeof($data_template_data_rras) > 0) {
	foreach ($data_template_data_rras as $data_template_data_rra) {
		$save["data_template_data_id"] = $data_template_data_id;
		$save["rra_id"] = $data_template_data_rra["rra_id"];
		
		db_execute("insert into data_template_data_rra (data_template_data_id,rra_id) values ($data_template_data_id,
			" . $data_template_data_rra["rra_id"] . ")");
	}
	}
	
	if (!empty($_local_data_id)) {
		update_data_source_title_cache($local_data_id);	
	}
}

?>
