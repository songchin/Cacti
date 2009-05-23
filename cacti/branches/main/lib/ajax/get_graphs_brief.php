<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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
$no_http_headers = true;
include(dirname(__FILE__) . "/../../include/global.php");
include_once(dirname(__FILE__) . "/../../lib/functions.php");

/* input validation */
if (isset($_REQUEST["q"])) {
	$q = strtolower(sanitize_search_string(get_request_var("q")));
} else {
	return;
}

$sql = "SELECT
	local_graph_id AS id,
	title_cache AS name
	FROM graph_templates_graph
	WHERE local_graph_id > 0
	AND local_graph_id NOT IN (SELECT item_id FROM user_auth_perms WHERE user_auth_perms.type=1 AND user_auth_perms.user_id=". get_request_var("id",0) . ")
	ORDER BY title_cache";

$graphs = db_fetch_assoc($sql);

if (sizeof($graphs) > 0) {
	foreach ($graphs as $graph) {
		print $graph["name"] . "|" . $graph["id"] . "\n";
	}
}

?>