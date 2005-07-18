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

/* get_full_script_path - gets the full path to the script to execute to obtain data for a
     given data source. this function does not work on SNMP actions, only script-based actions
   @arg $local_data_id - (int) the ID of the data source
   @returns - the full script path or (bool) false for an error */
function get_full_script_path($data_source_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	$data_input_type = db_fetch_cell("select data_input_type from data_source where id = $data_source_id");

	/* snmp-actions don't have paths */
	if ($data_input_type != DATA_INPUT_TYPE_SCRIPT) {
		return false;
	}

	$data_source_fields = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = $data_source_id"), "name", "value");

	if (isset($data_source_fields["script_id"])) {
		$script_path = db_fetch_cell("select input_string from data_input where id = " . $data_source_fields["script_id"]);

		/* exclude the manditory script_id field */
		unset($data_source_fields["script_id"]);

		/* substitute user variables */
		while (list($name, $value) = each($data_source_fields)) {
			$script_path = str_replace("<" . $name . ">", $value, $script_path);
		}

		/* substitute path variables */
		$script_path = substitute_path_variables($script_path);

		/* remove all remaining variables */
		$script_path = preg_replace("/(<[A-Za-z0-9_]+>)+/", "", $script_path);

		return $script_path;
	}
}

/* get_graph_tree_array - returns a list of graph trees taking permissions into account if
     necessary
   @arg $return_sql - (bool) Whether to return the SQL to create the dropdown rather than an array
	@arg $force_refresh - (bool) Force the refresh of the array from the database
   @returns - (array) an array containing a list of graph trees */
function get_graph_tree_array($return_sql = false, $force_refresh = false) {

	/* set the tree update time if not already set */
	if (!isset($_SESSION["tree_update_time"])) {
		$_SESSION["tree_update_time"] = time();
	}

	/* build tree array */
	if (!isset($_SESSION["tree_array"]) || ($force_refresh) ||
		(($_SESSION["tree_update_time"] + read_graph_config_option("page_refresh")) < time())) {

		if (read_config_option("auth_method") != "0") {
			$current_user = db_fetch_row("select policy_trees from user_auth where id=" . $_SESSION["sess_user_id"]);

			if ($current_user["policy_trees"] == "1") {
				$sql_where = "where user_auth_perms.user_id is null";
			}elseif ($current_user["policy_trees"] == "2") {
				$sql_where = "where user_auth_perms.user_id is not null";
			}

			$sql = "select
				graph_tree.id,
				graph_tree.name,
				user_auth_perms.user_id
				from graph_tree
				left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")
				$sql_where
				order by graph_tree.name";
		}else{
			$sql = "select * from graph_tree order by name";
		}

		$_SESSION["tree_array"] = $sql;
		$_SESSION["tree_update_time"] = time();
	} else {
		$sql = $_SESSION["tree_array"];
	}

	if ($return_sql == true) {
		return $sql;
	}else{
		return db_fetch_assoc($sql);
	}
}

/* get_host_array - returns a list of hosts taking permissions into account if necessary
   @returns - (array) an array containing a list of hosts */
function get_host_array() {
	if (read_config_option("auth_method") != "0") {
		$current_user = db_fetch_row("select policy_hosts from user_auth where id=" . $_SESSION["sess_user_id"]);

		if ($current_user["policy_hosts"] == "1") {
			$sql_where = "where user_auth_perms.user_id is null";
		}elseif ($current_user["policy_hosts"] == "2") {
			$sql_where = "where user_auth_perms.user_id is not null";
		}

		$host_list = db_fetch_assoc("select
			host.id,
			CONCAT_WS('',host.description,' (',host.hostname,')') as name,
			user_auth_perms.user_id
			from host
			left join user_auth_perms on (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")
			$sql_where
			order by host.description,host.hostname");
	}else{
		$host_list = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");
	}

	return $host_list;
}

/* get_hash_graph_template - returns the current unique hash for a graph template
   @arg $graph_template_id - (int) the ID of the graph template to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_graph_template($graph_template_id, $sub_type = "graph_template") {
	if ($sub_type == "graph_template") {
		$hash = db_fetch_cell("select hash from graph_templates where id=$graph_template_id");
	}elseif ($sub_type == "graph_template_item") {
		$hash = db_fetch_cell("select hash from graph_templates_item where id=$graph_template_id");
	}elseif ($sub_type == "graph_template_input") {
		$hash = db_fetch_cell("select hash from graph_template_input where id=$graph_template_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_template - returns the current unique hash for a data template
   @arg $graph_template_id - (int) the ID of the data template to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_template($data_template_id, $sub_type = "data_template") {
	if ($sub_type == "data_template") {
		$hash = db_fetch_cell("select hash from data_template where id=$data_template_id");
	}elseif ($sub_type == "data_template_item") {
		$hash = db_fetch_cell("select hash from data_template_item where id=$data_template_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_input - returns the current unique hash for a data input method
   @arg $graph_template_id - (int) the ID of the data input method to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_input($data_input_id, $sub_type = "data_input_method") {
	if ($sub_type == "data_input_method") {
		$hash = db_fetch_cell("select hash from data_input where id=$data_input_id");
	}elseif ($sub_type == "data_input_field") {
		$hash = db_fetch_cell("select hash from data_input_fields where id=$data_input_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_cdef - returns the current unique hash for a cdef
   @arg $graph_template_id - (int) the ID of the cdef to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_cdef($cdef_id, $sub_type = "cdef") {
	if ($sub_type == "cdef") {
		$hash = db_fetch_cell("select hash from cdef where id=$cdef_id");
	}elseif ($sub_type == "cdef_item") {
		$hash = db_fetch_cell("select hash from cdef_items where id=$cdef_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_gprint - returns the current unique hash for a gprint preset
   @arg $graph_template_id - (int) the ID of the gprint preset to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_gprint($gprint_id) {
	$hash = db_fetch_cell("select hash from graph_templates_gprint where id=$gprint_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_host_template - returns the current unique hash for a gprint preset
   @arg $host_template_id - (int) the ID of the host template to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_host_template($host_template_id) {
	$hash = db_fetch_cell("select hash from host_template where id=$host_template_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_query - returns the current unique hash for a data query
   @arg $graph_template_id - (int) the ID of the data query to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_query($data_query_id, $sub_type = "data_query") {
	if ($sub_type == "data_query") {
		$hash = db_fetch_cell("select hash from snmp_query where id=$data_query_id");
	}elseif ($sub_type == "data_query_graph") {
		$hash = db_fetch_cell("select hash from snmp_query_graph where id=$data_query_id");
	}elseif ($sub_type == "data_query_sv_data_source") {
		$hash = db_fetch_cell("select hash from snmp_query_graph_rrd_sv where id=$data_query_id");
	}elseif ($sub_type == "data_query_sv_graph") {
		$hash = db_fetch_cell("select hash from snmp_query_graph_sv where id=$data_query_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_round_robin_archive - returns the current unique hash for a round robin archive
   @arg $rra_id - (int) the ID of the round robin archive to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_round_robin_archive($rra_id) {
	$hash = db_fetch_cell("select hash from rra where id=$rra_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_version - returns the item type and cacti version in a hash format
   @arg $type - the type of item to represent ('graph_template','data_template',
     'data_input_method','cdef','gprint_preset','data_query','host_template')
   @returns - a 24-bit hexadecimal hash (8-bits for type, 16-bits for version) */
function get_hash_version($type) {
	global $hash_type_codes, $hash_version_codes, $config;

	return $hash_type_codes[$type] . $hash_version_codes{$config["cacti_version"]};
}

/* generate_hash - generates a new unique hash
   @returns - a 128-bit, hexadecimal hash */
function generate_hash() {
	global $config;

	return md5(session_id() . microtime() . rand(0,1000));
}

?>