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

define("DATA_QUERY_AUTOINDEX_NONE", 0);
define("DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME", 1);
define("DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE", 2);
define("DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION", 3);

define("DATA_QUERY_INPUT_TYPE_SNMP_QUERY", 1);
define("DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY", 2);
define("DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY", 3);

define("DATA_QUERY_INDEX_SORT_TYPE_ALPHABETIC", 1);
define("DATA_QUERY_INDEX_SORT_TYPE_NUMERIC", 2);

define("DATA_QUERY_FIELD_TYPE_INPUT", 1);
define("DATA_QUERY_FIELD_TYPE_OUTPUT", 2);

define("DATA_QUERY_FIELD_METHOD_VALUE", 1);
define("DATA_QUERY_FIELD_METHOD_VALUE_PARSE", 2);
define("DATA_QUERY_FIELD_METHOD_OID_OCTET", 3);
define("DATA_QUERY_FIELD_METHOD_OID_PARSE", 4);

define("DATA_QUERY_FIELD_METHOD_GROUP_VALUE", 1);
define("DATA_QUERY_FIELD_METHOD_GROUP_OID", 2);

define("DATA_QUERY_SCRIPT_ARG_GET", "get");
define("DATA_QUERY_SCRIPT_ARG_QUERY", "query");
define("DATA_QUERY_SCRIPT_ARG_INDEX", "index");
define("DATA_QUERY_SCRIPT_ARG_NUM_INDEXES", "num_indexes");

?>
