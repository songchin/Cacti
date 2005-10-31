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

/* get_graph_permissions_sql - creates SQL that reprents the current graph, host and graph
     template policies
   @arg $policy_graphs - (int) the current graph policy
   @arg $policy_hosts - (int) the current host policy
   @arg $policy_graph_templates - (int) the current graph template policy
   @returns - an SQL "where" statement */
function get_graph_permissions_sql($policy_graphs, $policy_hosts, $policy_graph_templates) {
	$sql = "";
	$sql_or = "";
	$sql_and = "";
	$sql_policy_or = "";
	$sql_policy_and = "";

	if ($policy_graphs == "1") {
		$sql_policy_and .= "$sql_and(user_auth_perms.type != 1 OR user_auth_perms.type is null)";
		$sql_and = " AND ";
		$sql_null = "is null";
	}elseif ($policy_graphs == "2") {
		$sql_policy_or .= "$sql_or(user_auth_perms.type = 1 OR user_auth_perms.type is not null)";
		$sql_or = " OR ";
		$sql_null = "is not null";
	}

	if ($policy_hosts == "1") {
		$sql_policy_and .= "$sql_and((user_auth_perms.type != 3) OR (user_auth_perms.type is null))";
		$sql_and = " AND ";
	}elseif ($policy_hosts == "2") {
		$sql_policy_or .= "$sql_or((user_auth_perms.type = 3) OR (user_auth_perms.type is not null))";
		$sql_or = " OR ";
	}

	if ($policy_graph_templates == "1") {
		$sql_policy_and .= "$sql_and((user_auth_perms.type != 4) OR (user_auth_perms.type is null))";
		$sql_and = " AND ";
	}elseif ($policy_graph_templates == "2") {
		$sql_policy_or .= "$sql_or((user_auth_perms.type = 4) OR (user_auth_perms.type is not null))";
		$sql_or = " OR ";
	}

	$sql_and = "";

	if (!empty($sql_policy_or)) {
		$sql_and = "AND ";
		$sql .= $sql_policy_or;
	}

	if (!empty($sql_policy_and)) {
		$sql .= "$sql_and$sql_policy_and";
	}

	if (empty($sql)) {
		return "";
	}else{
		return "(" . $sql . ")";
	}
}

/* is_graph_allowed - determines whether the current user is allowed to view a certain graph
   @arg $graph_id - (int) the ID of the graph to check permissions for
   @returns - (bool) whether the current user is allowed the view the specified graph or not */
function is_graph_allowed($graph_id) {
	$current_user = db_fetch_row("select policy_graphs,policy_hosts,policy_graph_templates from user_auth where id=" . $_SESSION["sess_user_id"]);

	/* get policy information for the sql where clause */
	$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);

	$graphs = db_fetch_assoc("select distinct
		graph.id
		from graph
		left join host on (host.id=graph.host_id)
		left join graph_template on (graph_template.id=graph.graph_template_id)
		left join user_auth_perms on ((graph.id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_template.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
		where graph.id = $graph_id");

	if (sizeof($graphs) > 0) {
		return true;
	}else{
		return false;
	}
}

/* is_tree_allowed - determines whether the current user is allowed to view a certain graph tree
   @arg $tree_id - (int) the ID of the graph tree to check permissions for
   @returns - (bool) whether the current user is allowed the view the specified graph tree or not */
function is_tree_allowed($tree_id) {
	$current_user = db_fetch_row("select policy_trees from user_auth where id=" . $_SESSION["sess_user_id"]);

	$trees = db_fetch_assoc("select
		user_id
		from user_auth_perms
		where user_id=" . $_SESSION["sess_user_id"] . "
		and type=2
		and item_id=$tree_id");

	/* policy == allow AND matches = DENY */
	if ((sizeof($trees) > 0) && ($current_user["policy_trees"] == "1")) {
		return false;
	/* policy == deny AND matches = ALLOW */
	}elseif ((sizeof($trees) > 0) && ($current_user["policy_trees"] == "2")) {
		return true;
	/* policy == allow AND no matches = ALLOW */
	}elseif ((sizeof($trees) == 0) && ($current_user["policy_trees"] == "1")) {
		return true;
	/* policy == deny AND no matches = DENY */
	}elseif ((sizeof($trees) == 0) && ($current_user["policy_trees"] == "2")) {
		return false;
	}
}

?>