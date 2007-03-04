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


/**
 * Insert an event including all parameters
 *
 * This function if used by plugins to insert events to be processed
 *
 * @param string $handler Handler Name
 * @param array $params array of parameters, field => value elements
 * @param string $message Event Message
 * @param int $plugin_id ID of plugin that is inserting the event
 * @return int row number of insert (false if unsuccessful)
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


/**
 * Remove an event including all parameters
 *
 * This function if used by the event handler to remove an event
 *
 * @param int $id ID of event to be removed
 */
function api_event_remove($id) {
	db_execute("DELETE FROM event_queue_control WHERE id=". $id);
	db_execute("DELETE FROM event_queue_param WHERE control_id=". $id);
}


/**
 * Processes events in the queue
 *
 * This function if used by the Event Handler to process a specific event
 *
 * @param int $id ID of event to be processed
 */
function api_event_process ($id) {
	$event = api_event_get($id);

	$function_names = api_event_handler_function_name ($event['handler']);
	foreach ($function_names as $function_name) {
		$function_name ($event);
	}
}


/**
 * Update event status
 *
 * This function sets the status of all events to 1 to allow them to be processed
 *
 */
function api_event_set_status() {
	$id = rand(1, 99999);
	db_execute("UPDATE event_queue_control set status = $id");
	return $id;
}


/**
 * Remove all processed events
 *
 * This function removes all events from the queue that have a status showing that they have been processed.
 *
 */
function api_event_removed_processed($status_id) {
	$events = db_fetch_assoc("SELECT id FROM event_queue_control where status = $status_id");
	db_execute("DELETE FROM event_queue_control WHERE status=$status_id");

	foreach($events as $event) {
		db_execute("DELETE FROM event_queue_param WHERE control_id = " . $event['id']);
	}
}


?>