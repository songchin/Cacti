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

/*
To use this api file you must include

include/config.php

Check respective includes for functions definitions

*/

$root_dir = ereg_replace("(.*)[\/\\]lib", "\\1", dirname(__FILE__));

/* Variable includes */
include_once($root_dir . "/include/user/user_constants.php");
include_once($root_dir . "/include/user/user_arrays.php");

/* Functions includes */
include_once($root_dir . "/lib/user/user_action.php");
include_once($root_dir . "/lib/user/user_info.php");
include_once($root_dir . "/lib/user/user_ldap.php");

?>
