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
 * List Event records
 *
 * Given filter array, return list of event records
 *
 * @param array $filter_array filter array, field => value elements
 * @return array event records
 */
function api_event_list($filter_array) {
	$sql_where = '';
	/* validation and setup for the WHERE clause */
	if ((is_array($filter_array)) && (sizeof($filter_array) > 0)) {
		/* validate each field against the known master field list */
		$field_errors = api_event_fields_validate(sql_filter_array_to_field_array($filter_array));

		/* if a field input error has occured, register the error in the session and return */
		if (sizeof($field_errors) > 0) {
			field_register_error($field_errors);
			return false;
		/* otherwise, form an SQL WHERE string using the filter fields */
		}else{
			$sql_where = sql_filter_array_to_where_string($filter_array, api_event_form_list(), true);
		}
	}
	return db_fetch_assoc("SELECT * FROM event_queue_control $sql_where ORDER BY id");
}


/**
 * Retreive the function name for a given Event Hander
 *
 * Given a handler name, return list of function names to be called
 *
 * @param string $handler_name Handler name
 * @return array list of function names
 */
function api_event_handler_function_name ($handler_name) {
	$handler_name = sql_sanitize($handler_name);
	$handlers = db_fetch_assoc("select function_name from event_handler where handler_name = '$handler_name'");

	$function_name = array();
	foreach ($handlers as $handler) {
		$function_name[] = $handler['function_name'];
	}
	return $function_name;
}


/**
 * Retreive an event and all associated parameters
 *
 * Given an event id, return all name => value pairs containing all the data for an event
 *
 * @param int $event_id Event ID
 * @return array event parameters
 */
function api_event_get($event_id) {
	/* sanity check for $event_id */
	if ((!is_numeric($event_id)) || (empty($event_id))) {
		log_save("Invalid input '$event_id' for 'event_id' in " . __FUNCTION__ . "()", SEV_ERROR);
		return false;
	}
	$event = db_fetch_row("select * from event_queue_control where id = " . sql_sanitize($event_id));
	$params = db_fetch_assoc("select * from event_queue_param where control_id = " . sql_sanitize($event_id));

	foreach($params as $param) {
		$event[$param['name']] = $param['value'];
	}	

	return $event;
}


/**
 * Returns list of fields in the event form
 *
 * Returns list of fields in the event form for validation
 *
 * @return array event fields
 */
function &api_event_form_list() {
	require(CACTI_BASE_PATH . "/include/event/event_form.php");
	return $fields_event_control;
}


?>
