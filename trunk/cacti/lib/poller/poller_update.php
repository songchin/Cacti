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

function api_poller_save($poller_id, $active, $hostname, $description) {
	$save["id"] = $poller_id;
	$save["name"] = form_input_validate($description, "name", "", true, 3);
	$save["hostname"] = form_input_validate($hostname, "hostname", "", true, 3);
	$save["active"] = form_input_validate($active, "active", "", true, 3);

	$poller_id = 0;

	if (!is_error_message()) {
		$poller_id = sql_save($save, "poller");

		if ($poller_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $poller_id;
}

function api_poller_delete($poller_id) {
	$hosts_polled = db_fetch_assoc("SELECT poller_id FROM host WHERE poller_id=" . $poller_id);

	if (sizeof($hosts_polled) == 0) {
		if ($poller_id == 1) {
			$error_message = _("This poller is the main system poller.  It can not be deleted.");
			require_once(CACTI_BASE_PATH . "/include/top_header.php");
			form_message(_("Can Not Delete Poller"), $error_message, "pollers.php");
			require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		}else {
			db_execute("DELETE FROM poller WHERE id=". $poller_id);
		}
	} else {
		$error_message = sprintf(_("The poller selected is in use for '%s' hosts and can not be deleted.  You can not delete a poller when it has hosts associated with it."), sizeof($hosts_polled));
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_message(_("Can Not Delete Poller"), $error_message, "pollers.php");
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
	}
}

function api_poller_disable($poller_id) {
	if ($poller_id == 1) {
		$error_message = _("This poller is the main system poller.  It can not be disabled.");
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_message(_("Can Not Disable Poller"), $error_message, "pollers.php");
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
	} else {
		db_execute("UPDATE poller SET active='', run_state='Disabled' WHERE id='" . $poller_id . "'");

		/* update poller cache */
		/* todo this yet */
	}
}

function api_poller_enable($poller_id) {
	db_execute("UPDATE poller SET active='on', run_state='Wait' WHERE id='" . $poller_id . "'");

	/* update poller cache */
	/* todo this yet */
}

function api_poller_statistics_clear($poller_id) {
	db_update("poller",
		array(
			"min_time" => array("type" => DB_TYPE_INTEGER, "value" => "9.99999"),
			"max_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"cur_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"avg_time" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"total_polls" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"failed_polls" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
			"availability" => array("type" => DB_TYPE_INTEGER, "value" => "100.00"),
			"poller_id" => array("type" => DB_TYPE_INTEGER, "value" => $poller_id)
			),
		array("poller_id"));
}

?>
