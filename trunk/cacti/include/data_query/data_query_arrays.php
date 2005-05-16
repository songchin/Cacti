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

include_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");

$data_query_input_types = array(
	DATA_QUERY_INPUT_TYPE_SNMP_QUERY => _("SNMP Query"),
	DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY => _("Script Query"),
	DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY => _("Script Query (Using PHP Script Server)")
	);

$reindex_types = array(
	DATA_QUERY_AUTOINDEX_NONE => _("None"),
	DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME => _("Uptime Goes Backwards"),
	DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE => _("Index Count Changed"),
	DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION => _("Verify All Fields")
	);

?>