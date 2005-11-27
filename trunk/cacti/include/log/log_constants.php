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

define("SEV_EMERGENCY", 7);
define("SEV_ALERT", 6);
define("SEV_CRITICAL", 5);
define("SEV_ERROR", 4);
define("SEV_WARNING", 3);
define("SEV_NOTICE", 2);
define("SEV_INFO", 1);
define("SEV_DEBUG", 0);
define("SEV_DEV", -1);

define("FACIL_UNKNOWN",0);
define("FACIL_POLLER",1);
define("FACIL_CMDPHP",2);
define("FACIL_CACTID",3);
define("FACIL_SCPTSVR",4);
define("FACIL_AUTH",5);
define("FACIL_WEBUI",6);
define("FACIL_EXPORT",7);
define("FACIL_SMTP",8);

define("SYSLOG_CACTI",1);
define("SYSLOG_BOTH",2);
define("SYSLOG_SYSTEM",3);

define("SYSLOG_MNG_NONE",0);
define("SYSLOG_MNG_ASNEEDED",1);
define("SYSLOG_MNG_DAYSOLD",2);
define("SYSLOG_MNG_STOPLOG",3);

/* Define syslog variables for php */
define_syslog_variables();

?>
