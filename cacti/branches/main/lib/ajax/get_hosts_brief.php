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
} else return;

$sql = "SELECT id, description as name
	FROM host
	WHERE hostname LIKE '%$q%'
	OR description LIKE '%$q%'
	ORDER BY description,hostname";

$hosts = db_fetch_assoc($sql);

if (sizeof($hosts) > 0) {
	foreach ($hosts as $host) {
		print title_trim($host["name"], 40) . "|" . $host["id"] . "\n";
	}
}

?>