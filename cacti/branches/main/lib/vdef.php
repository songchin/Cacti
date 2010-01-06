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

/* get_vdef_item_name - resolves a single VDEF item into its text-based representation
   @arg $vdef_item_id - the id of the individual vdef item
   @returns - a text-based representation of the vdef item */
function get_vdef_item_name($vdef_item_id) 	{
	global $config, $vdef_functions, $vdef_operators;;

	$vdef_item = db_fetch_row("select type,value from vdef_items where id=$vdef_item_id");
	$current_vdef_value = $vdef_item["value"];

	switch ($vdef_item["type"]) {
		case '1': return $vdef_functions[$current_vdef_value];
		case '4': return $current_vdef_value;
		case '6': return $current_vdef_value;
	}
}

/* get_vdef - resolves an entire VDEF into its text-based representation for use in the RRDTool 'graph'
     string. this name will be resolved recursively if necessary
   @arg $vdef_id - the id of the vdef to resolve
   @returns - a text-based representation of the vdef */
function get_vdef($vdef_id) {
	$vdef_items = db_fetch_assoc("select * from vdef_items where vdef_id=$vdef_id order by sequence");

	$i = 0; $vdef_string = "";

	if (sizeof($vdef_items) > 0) {
		foreach ($vdef_items as $vdef_item) {
			if ($i > 0) {
				$vdef_string .= ",";
			}

			if ($vdef_item["type"] == 5) {
				$current_vdef_id = $vdef_item["value"];
				$vdef_string .= get_vdef($current_vdef_id);
			}else{
				$vdef_string .= get_vdef_item_name($vdef_item["id"]);
			}

			$i++;
		}
	}

	return $vdef_string;
}

?>
