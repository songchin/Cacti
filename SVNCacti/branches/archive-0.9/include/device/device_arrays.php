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

include_once(CACTI_BASE_PATH . "/include/device/device_constants.php");

$snmp_versions = array(
	1 => _("Version 1"),
	2 => _("Version 2"),
	3 => _("Version 3")
	);

$snmpv3_security_level = array(
	"authNoPriv" => _("No Privacy Protocol"),
	"authPriv" => _("Privacy Protocol")
	);

$snmpv3_auth_protocol = array(
	"MD5" => _("MD5 (default)"),
	"SHA" => _("SHA")
	);

$snmpv3_priv_protocol = array(
	"[None]" => "[None]",
	"DES" => "DES (default)",
	"AES128" => "AES"
	);

$host_status_types = array(
	HOST_UNKNOWN => "Unknown",
	HOST_DOWN => "Down",
	HOST_RECOVERING => "Recovering",
	HOST_UP => "Up"
	);

?>
