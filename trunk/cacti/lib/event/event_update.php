<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

function api_event_insert($handler, $params, $message = '', $plugin_id = 0) {
	$save['id'] = '';
	$save["handler"] = $handler;
	$save['created'] = time();
	$save["plugin_id"] = $plugin_id;
	$save["message"] = $message;

	$control_id = sql_save($save, "event_queue_control");

	if ($control_id) {
		$save = array();
		$save['id'] = '';
		$save["control_id"] = $control_id;
		$save["plugin_id"] = $plugin_id;
		if (is_array($params)) {
			foreach($params as $par => $text) {
				$save = array();
				$save['id'] = '';
				$save["control_id"] = $control_id;
				$save["plugin_id"] = $plugin_id;
				$save['name'] = $par;
				$save['value'] = $text;
				$param_id = sql_save($save, "event_queue_param");
			}
		}
	}
	return $control_id;
}

function api_event_remove($id) {
	db_execute("DELETE FROM event_queue_control WHERE id=". $id);
	db_execute("DELETE FROM event_queue_param WHERE control_id=". $id);
}








function api_event_set_status() {
	db_execute("UPDATE event_queue_control set status = 1");
}

function api_event_removed_processed() {
	$events = db_fetch_assoc("SELECT id FROM event_queue_control where status = 1");
	db_execute("DELETE FROM event_queue_control WHERE status=1");

	foreach($events as $event) {
		db_execute("DELETE FROM event_queue_param WHERE control_id = " . $event['id']);
	}
}


?>