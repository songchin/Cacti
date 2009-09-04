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
	$q = sanitize_search_string(get_request_var("q"));
} else return;

if (isset($_REQUEST["sql"])) {
	$sql = base64_decode(get_request_var("sql"));
} else return;

if ($asname_pos = strpos(strtoupper($sql), "AS NAME")) {
	$name_qry = substr($sql, 6, $asname_pos-6);
	cacti_log($name_qry);
}else{
	$name_qry = "name";
}

if ($where_pos = strpos(strtoupper($sql), "WHERE")) {
	$sql = substr($sql, 0, $where_pos+5) . " LOWER($name_qry) LIKE '%$q%' AND " . substr($sql, $where_pos+5);
}elseif ($orderby_pos = strpos(strtoupper($form_data), "ORDER BY")) {
	$sql = substr($sql, 0, $orderby_pos) . " AND LOWER($name_qry) LIKE '%$q%' " . substr($sql, $orderby_pos);
}else{
	$sql = $sql . " AND LOWER($name_qry) LIKE '%$s%'";
}

$entries = db_fetch_assoc($sql);

if (sizeof($entries) > 0) {
	foreach ($entries as $entry) {
		print $entry["name"] . "|" . $entry["id"] . "\n";
	}
}
