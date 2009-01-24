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

chdir('../../../');
require('./include/auth.php');

/* ================= Input validation ================= */
	input_validate_input_number(get_request_var("id"));

	if(!isset($_REQUEST['cdef']) || !is_array($_REQUEST['cdef'])) exit;
	/* cdef table contains one row defined as "nodrag&nodrop" */
	unset($_REQUEST['cdef'][0]);

	/* delivered cdef ids has to be exactly the same like we have stored */
	$old_order = array();
	$new_order = $_REQUEST['cdef'];

	$sql = "SELECT id, sequence FROM cdef_items WHERE cdef_id = " . $_GET['id'];
	$cdef_items = db_fetch_assoc($sql);

	if(sizeof($cdef_items)>0) {
		foreach($cdef_items as $item) {
			$old_order[$item['sequence']] = $item['id'];
		}
	}else {
		exit;
	}
	if(sizeof(array_diff($new_order, $old_order))>0) exit;

	/* the set of sequence numbers has to be the same too */
	if(sizeof(array_diff_key($new_order, $old_order))>0) exit;
/* ==================================================== */

foreach($new_order as $sequence => $cdef_id) {
	$sql = "UPDATE cdef_items SET sequence = $sequence WHERE id = $cdef_id";
	db_execute($sql);
}
?>