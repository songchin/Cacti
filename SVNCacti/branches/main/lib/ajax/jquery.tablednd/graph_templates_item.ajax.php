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

	if(!isset($_REQUEST['graph_item']) || !is_array($_REQUEST['graph_item'])) exit;
	/* graph_item table contains one row defined as "nodrag&nodrop" */
	unset($_REQUEST['graph_item'][0]);

	/* delivered graph_item ids has to be exactly the same like we have stored */
	$old_order = array();
	$new_order = $_REQUEST['graph_item'];

	$sql = "SELECT id, sequence FROM graph_templates_item WHERE graph_template_id = " . $_GET['id'] . " and local_graph_id=0";
	$graph_templates_items = db_fetch_assoc($sql);

	if(sizeof($graph_templates_items)>0) {
		foreach($graph_templates_items as $item) {
			$old_order[$item['sequence']] = $item['id'];
		}
	}else {
		exit;
	}

	#if(sizeof(array_diff($new_order, $old_order)) > 0) exit;

	# compute difference of arrays
	$diff = array_diff_assoc($new_order, $old_order);
	# nothing to do?

	if(sizeof($diff) == 0) exit;
/* ==================================================== */

foreach($diff as $sequence => $graph_templates_item_id) {
	# update the template item itself
	$sql = "UPDATE graph_templates_item SET sequence = $sequence WHERE id = $graph_templates_item_id";
	db_execute($sql);
	# update all items referring the template item
	$sql = "UPDATE graph_templates_item SET sequence = $sequence WHERE local_graph_template_item_id = $graph_templates_item_id";
	db_execute($sql);
}
?>