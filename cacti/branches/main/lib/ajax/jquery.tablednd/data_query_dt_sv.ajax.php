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
input_validate_input_number(get_request_var("dt_id"));
input_validate_input_number(get_request_var("gt_id"));
/* ================= Input validation ================= */

/* $_REQUEST variable depends on data template id */
$_request_var = 'data_template_suggested_values_' . $_GET['dt_id'];

if(!isset($_REQUEST[$_request_var]) || !is_array($_REQUEST[$_request_var])) exit;
/* remove all rows not related to a suggested value */
foreach ($_REQUEST[$_request_var] as $key => $value) {
	if (!is_numeric($value)) unset($_REQUEST[$_request_var][$key]);
}
$new_data = $_REQUEST[$_request_var]; /* array(seq => id) */

$old_order = array();
$new_order = array();


/*
 * get old sequence information
 */
$sql = "SELECT " .
			"id, " .
			"sequence, " .
			"field_name " .
			"FROM snmp_query_graph_rrd_sv " .
			"WHERE data_template_id=" . $_GET['dt_id'] . " " .
			"AND snmp_query_graph_id=" . $_GET['gt_id'] . " " .
			"ORDER BY field_name, sequence";
$old_data = db_fetch_assoc($sql);

/* rekey old data to get old_order*/
if (sizeof($old_data)) {
	foreach($old_data as $item) {
		$old_order[$item["id"]]["field_name"] = $item["field_name"];
		$old_order[$item["id"]]["sequence"] = $item["sequence"];
	}
} /* array(id => array(field_name, sequence)) */



/*
 * build new_order but take field_name into account!
 */
$sequence = array();							/* remember sequence for each field_name seperately 	*/
foreach($new_data as $key => $id) {
	$fname = $old_order[$id]["field_name"];		/* this is the field we're working on 					*/
	if (!isset($sequence[$fname])) {
		$sequence[$fname] = 1; 					/* restart sequence_no each time a new field is found 	*/
	}

	if ($sequence[$fname] != $old_order[$id]["sequence"]) { 	/* sequence has been changed 			*/
		$new_order[$id] = $sequence[$fname];					/* remember this record for update		*/
	}
	$sequence[$fname]++;						/* increment sequence for current field					*/
}


/* ==================================================== */
if(sizeof($new_order) == 0) exit;
foreach($new_order as $id => $sequence) {
	# update the template item itself
	$sql = "UPDATE snmp_query_graph_rrd_sv SET sequence = $sequence WHERE id = $id";
	db_execute($sql);
}
?>