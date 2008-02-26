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
 * Saves auth control data for a certain control id
 *
 * Saves auth control data for a certain control id
 *
 * @return 1 on success, 0 on error
 */
function auth_control_data_save($data, $category = "SYSTEM", $enable_user_edit = 0, $plugin_id = 0, $control_id) {

	$user_id = "";
	$user_name = "";

	$sql = "REPLACE INTO `auth_data` (`control_id`,`plugin_id`,`category`,`name`,`value`,`enable_user_edit`,`updated_when`,`updated_by`) VALUES (";
	$where = "";

	if (!is_array($data)) {
		return 0;
	}

	





}

?>
