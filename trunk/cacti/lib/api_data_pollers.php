<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
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

function api_data_poller_delete($data_poller_id) {
	db_execute("delete from poller where id='$data_poller_id'");
}

?>