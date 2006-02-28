<?php
$no_http_headers = true;

require(dirname(__FILE__) . "/include/global.php");

api_event_insert("cacti_log", array("message"=>"testing","function"=>"script_error()","error"=>"die!"));


print "Checking the Event Queue\n";

/* Set the status to show which events are being processed */
api_event_set_status();

/* Get all events so we can begin processing */
$events = api_event_list('');


foreach ($events as $event) {
	$all = api_event_get($event['id']);
	print_r($all);


}

/* Remove all events that were set to be processed */
api_event_removed_processed();




?>