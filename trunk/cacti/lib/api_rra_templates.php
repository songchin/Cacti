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

function api_rra_template_save($id, $name, $description) {
	$save["id"] = $id;
	$save["name"] = form_input_validate($name, "name", "", true, 3);
	$save["description"] = form_input_validate($description, "description", "", false, 3);

	$rra_template_id = 0;

	if (!is_error_message()) {
		$rra_template_id = sql_save($save, "rra_template");

		if ($rra_template_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $rra_template_id;
}

function api_rra_template_delete($poller_id) {
//	$hosts_polled = db_fetch_assoc("select poller_id from host where poller_id=" . $poller_id);
//
//	if (sizeof($hosts_polled) == 0) {
//		if ($poller_id == 1) {
//			$error_message = _("This poller is the main system poller.  It can not be deleted.");
//			require_once(CACTI_BASE_PATH . "/include/top_header.php");
//			form_message(_("Can Not Delete Poller"), $error_message, "data_pollers.php");
//			require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
//		}else {
//			db_execute("DELETE FROM poller WHERE id=". $poller_id);
//		}
//	} else {
//		$error_message = sprintf(_("The poller selected is in use for '%s' hosts and can not be deleted.  You can not delete a poller when it has hosts associated with it."), sizeof($hosts_polled));
//		require_once(CACTI_BASE_PATH . "/include/top_header.php");
//		form_message(_("Can Not Delete Poller"), $error_message, "data_pollers.php");
//		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
//	}
}

function api_rra_template_duplicate($poller_id) {
//	if ($poller_id == 1) {
//		$error_message = _("This poller is the main system poller.  It can not be disabled.");
//		require_once(CACTI_BASE_PATH . "/include/top_header.php");
//		form_message(_("Can Not Disable Poller"), $error_message, "data_pollers.php");
//		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
//	} else {
//		db_execute("UPDATE poller SET active='', run_state='Disabled' WHERE id='" . $poller_id . "'");

		/* update poller cache */
		/* todo this yet */
//	}
}

?>