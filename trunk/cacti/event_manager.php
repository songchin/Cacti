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

/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0])) {
	die("<br><strong>". _("This script is only meant to run at the command line.") . '</strong>');
}

/* let PHP run just as long as it has to */
ini_set('max_execution_time', '0');

$no_http_headers = true;

require(dirname(__FILE__) . '/include/global.php');

$event_manager_interval = read_config_option('event_manager_interval');

log_save('Event Manager Starting', SEV_INFO, FACIL_POLLER, '', 0, 0, 0, true);

$counter = time() - $event_manager_interval;
while (true) {

	/* We want to have the event manager process every XX seconds, so sleep until its processing time */
	while ($counter > time() - $event_manager_interval) {
		Sleep(1);
	}

	/* Start our timer now, so it includes the actual processing time in the processing interval */
	$counter = time();

	/* Set the status to show which events are being processed */
	$status_id = api_event_set_status();

	/* Get all events so we can begin processing */
	$events = api_event_list(array('status'=>$status_id));

	/* Loop through each event for processing */
	foreach ($events as $event) {
		log_save('Processing Event ' . $event['id'], SEV_INFO, FACIL_POLLER, '', 0, 0, 0, true);
		api_event_process ($event['id']);
	}

	/* Remove all events that were set to be processed */
	api_event_removed_processed($status_id);
	if (date('s', time()) < $event_manager_interval) {
		unset($_SESSION['sess_config_array']['event_manager_interval']);
		$event_manager_interval = read_config_option('event_manager_interval');
	}
}

log_save('Event Manager exitting', SEV_INFO, FACIL_POLLER, '', 0, 0, 0, true);

?>