<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

/* is_error_message - finds whether an error message has been raised and has not been outputted to the
     user
   @returns - (bool) whether the messages array contains an error or not */
function is_error_message() {
	global $messages;

	if (isset($_SESSION["sess_messages"])) {
		if (is_array($_SESSION["sess_messages"])) {
			foreach (array_keys($_SESSION["sess_messages"]) as $current_message_id) {
				if ($messages[$current_message_id]["type"] == "error") { return true; }
			}
		}
	}

	return false;
}

/* raise_message - mark a message to be displayed to the user once display_output_messages() is called
   @arg $message_id - the ID of the message to raise as defined in $messages in 'include/config_arrays.php' */
function raise_message($message_id) {
	$_SESSION["sess_messages"][$message_id] = $message_id;
}

/* display_output_messages - displays all of the cached messages from the raise_message() function and clears
     the message cache */
function display_output_messages() {
	global $messages, $colors;

	if (isset($_SESSION["sess_messages"])) {
		$error_message = is_error_message();

		if (is_array($_SESSION["sess_messages"])) {
			foreach (array_keys($_SESSION["sess_messages"]) as $current_message_id) {
				eval ('$message = "' . $messages[$current_message_id]["message"] . '";');

				switch ($messages[$current_message_id]["type"]) {
				case 'info':
					if ($error_message == false) {
						print "<table align='center' width='98%' style='background-color: #" . $colors['messagebar_background'] . "; border: 1px solid #" . $colors['messagebar_border'] . ";'>";
						print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textInfo'>$message</p></td></tr>";
						print "</table><br>";
					}
					break;
				case 'error':
					print "<table align='center' width='98%' style='background-color: #" . $colors['messagebar_background'] . "; border: 1px solid #ff0000;'>";
					print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textError'>Error: $message</p></td></tr>";
					print "</table><br>";
					break;
				}
			}
		}
	}

	kill_session_var("sess_messages");
}

/* display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @arg $text - the actual text of the error message to display */
function display_custom_error_message($message) {
	global $colors;

	print "<table align='center' width='98%' style='background-color: #ffffff; border: 1px solid #ff0000;'>";
	print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textError'>Error: $message</p></td></tr>";
	print "</table><br>";
}

/* clear_messages - clears the message cache */
function clear_messages() {
	kill_session_var("sess_messages");
}

/* debug_log_insert - inserts a line of text into the debug log
   @arg $type - the 'category' or type of debug message
   @arg $text - the actual debug message */
function debug_log_insert($type, $text) {
	if (!isset($_SESSION["debug_log"][$type])) {
		$_SESSION["debug_log"][$type] = array();
	}

	array_push($_SESSION["debug_log"][$type], $text);
}

/* debug_log_clear - clears the debug log for a particular category
   @arg $type - the 'category' to clear the debug log for. omitting this argument
     implies all categories */
function debug_log_clear($type = "") {
	if ($type == "") {
		kill_session_var("debug_log");
	}else{
		if (isset($_SESSION["debug_log"])) {
			unset($_SESSION["debug_log"][$type]);
		}
	}
}

/* debug_log_return - returns the debug log for a particular category
   @arg $type - the 'category' to return the debug log for.
   @returns - the full debug log for a particular category */
function debug_log_return($type) {
	$log_text = "";

	if (isset($_SESSION["debug_log"][$type])) {
		for ($i=0; $i<count($_SESSION["debug_log"][$type]); $i++) {
			$log_text .= "+ " . $_SESSION["debug_log"][$type][$i] . "<br>";
		}
	}

	return $log_text;
}

?>
