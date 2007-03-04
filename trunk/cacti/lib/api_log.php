<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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


/* To use this api file you must include

include/global.php

Check respective includes for functions definitions
*/

if (!defined("CACTI_BASE_PATH")) {
        define("CACTI_BASE_PATH", str_replace(DIRECTORY_SEPARATOR . "lib", "", dirname(__FILE__)));
}

/* Variable includes */
require_once(CACTI_BASE_PATH . "/include/log/log_constants.php");
require_once(CACTI_BASE_PATH . "/include/log/log_arrays.php");
require_once(CACTI_BASE_PATH . "/include/log/log_form.php");

/* Functions includes */
require_once(CACTI_BASE_PATH . "/lib/log/log_update.php");
require_once(CACTI_BASE_PATH . "/lib/log/log_info.php");



/*?>
