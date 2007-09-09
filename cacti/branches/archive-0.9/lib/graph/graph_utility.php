<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

/* is_graph_item_type_primary - determines if a given graph item type id is primary,
     that is an AREA, STACK, LINE1, LINE2, or LINE3
   @arg $graph_item_type_id - the graph item type id to evaluate
   @returns - (bool) true if the item is primary, false otherwise  */
function is_graph_item_type_primary($graph_item_type_id) {
	if (($graph_item_type_id == GRAPH_ITEM_TYPE_LINE1) || ($graph_item_type_id == GRAPH_ITEM_TYPE_LINE2) || ($graph_item_type_id == GRAPH_ITEM_TYPE_LINE3) || ($graph_item_type_id == GRAPH_ITEM_TYPE_AREA) || ($graph_item_type_id == GRAPH_ITEM_TYPE_STACK)) {
		return true;
	}else{
		return false;
	}
}

/* generate_graph_def_name - takes a number and turns each digit into its letter-based
     counterpart for RRDTool DEF names (ex 1 -> a, 2 -> b, etc)
   @arg $graph_item_id - (int) the ID to generate a letter-based representation of
   @returns - a letter-based representation of the input argument */
function generate_graph_def_name($graph_item_id) {
	$lookup_table = array("a","b","c","d","e","f","g","h","i","j");

	$result = "";

	for ($i=0; $i<strlen(strval($graph_item_id)); $i++) {
		$result .= $lookup_table{substr(strval($graph_item_id), $i, 1)};
	}

	return $result;
}

?>
