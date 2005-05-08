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

function api_data_poller_save($id, $active, $hostname, $description) {
	$save["id"] = $id;
	$save["name"] = form_input_validate($description, "name", "", true, 3);
	$save["hostname"] = form_input_validate($hostname, "hostname", "", false, 3);
	$save["active"] = form_input_validate($active, "active", "", true, 3);

	$data_poller_id = 0;

	if (!is_error_message()) {
		$data_poller_id = sql_save($save, "poller");

		if ($data_poller_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $data_poller_id;
}

function api_data_poller_delete($poller_id) {
	$hosts_polled = db_fetch_assoc("select poller_id from host where poller_id=" . $poller_id);

	if (sizeof($hosts_polled) == 0) {
		if ($poller_id == 1) {
			$error_message = "This poller is the main system poller.  It can not be deleted.";
			include("./include/top_header.php");
			form_message("Can Not Delete Poller", $error_message, "data_pollers.php");
			include("./include/bottom_footer.php");
		}else {
			db_execute("DELETE FROM poller WHERE id=". $poller_id);
		}
	} else {
		$error_message = "The poller selected is in use for " . sizeof($hosts_polled) . " hosts and can not be deleted.  You can not delete a poller when it has hosts associated with it.";
		include("./include/top_header.php");
		form_message("Can Not Delete Poller", $error_message, "data_pollers.php");
		include("./include/bottom_footer.php");
	}
}

function api_data_poller_disable($poller_id) {
	db_execute("UPDATE poller SET active='', run_state='Disabled' WHERE id='" . $selected_items[$i] . "'");

	/* update poller cache */
	/* todo this yet */
}

function api_data_poller_enable($poller_id) {
	db_execute("UPDATE poller SET active='on', run_state='Wait' WHERE id='" . $selected_items[$i] . "'");

	/* update poller cache */
	/* todo this yet */
}

?>