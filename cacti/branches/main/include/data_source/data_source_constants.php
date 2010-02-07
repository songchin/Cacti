<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

define("DS_ACTION_DELETE", "0");
define("DS_ACTION_CHANGE_TEMPLATE", "1");
define("DS_ACTION_CHANGE_HOST", "2");
define("DS_ACTION_DUPLICATE", "3");
define("DS_ACTION_CONVERT_TO_TEMPLATE", "4");
define("DS_ACTION_ENABLE", "5");
define("DS_ACTION_DISABLE", "6");
define("DS_ACTION_REAPPLY_SUGGESTED_NAMES", "7");

define("DATA_SOURCE_TYPE_GAUGE", 1);
define("DATA_SOURCE_TYPE_COUNTER", 2);
define("DATA_SOURCE_TYPE_DERIVE", 3);
define("DATA_SOURCE_TYPE_ABSOLUTE", 4);
define("DATA_SOURCE_TYPE_COMPUTE", 5);
