<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

chdir('../../../');
require_once('./include/auth.php');

/* ================= Input validation ================= */
	input_validate_input_number(get_request_var("id"));
/* ================= Input validation ================= */

	if(!isset($_REQUEST['vdef_item']) || !is_array($_REQUEST['vdef_item'])) exit;
	/* vdef table contains one row defined as "nodrag&nodrop" */
	unset($_REQUEST['vdef_item'][0]);

	/* delivered vdef ids has to be exactly the same like we have stored */
	$old_order = array();
	$new_order = $_REQUEST['vdef_item'];

	$sql = "SELECT id, sequence FROM vdef_items WHERE vdef_id = " . $_GET['id'];
	$vdef_items = db_fetch_assoc($sql);

	if(sizeof($vdef_items)>0) {
		foreach($vdef_items as $item) {
			$old_order[$item['sequence']] = $item['id'];
		}
	}else {
		exit;
	}
	if(sizeof(array_diff($new_order, $old_order))>0) exit;

	/* the set of sequence numbers has to be the same too */
	if(sizeof(array_diff_key($new_order, $old_order))>0) exit;
/* ==================================================== */

foreach($new_order as $sequence => $vdef_id) {
	$sql = "UPDATE vdef_items SET sequence = $sequence WHERE id = $vdef_id";
	db_execute($sql);
}
?>
