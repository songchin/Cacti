<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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

/* we don't want these pages cached */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");

include("./include/config.php");

/* initilize php session */
session_start();

/* check to see if this is a new installation */
if (db_fetch_cell("select cacti from version") != $config["cacti_version"]) {
	header ("Location: install/");
	exit;
}

if (read_config_option("global_auth") == "on") {
	/* handle change password dialog */
	if (isset($_SESSION['sess_change_password'])) {
		header ("Location: auth_changepassword.php?ref=" . $_SERVER["HTTP_REFERER"]);
		exit;
	}
	
	/* don't even bother with the guest code if we're already logged in */
	if ((isset($guest_account)) && (empty($_SESSION["sess_user_id"]))) {
		$guest_user_id = db_fetch_cell("select id from user_auth where username='" . read_config_option("guest_user") . "'");
		
		/* cannot find guest user */
		if (empty($guest_user_id)) {
			print "<strong><font size='+1' color='FF0000'>CANNOT FIND GUEST USER: " . read_config_option("guest_user") . "</font></strong>";
		}else{
			$_SESSION["sess_user_id"] = $guest_user_id;
		}
	}
	
	/* if we are a guest user in a non-guest area, wipe credentials */
	if (!empty($_SESSION["sess_user_id"])) {
		if ((!isset($guest_account)) && (db_fetch_cell("select id from user_auth where username='" . read_config_option("guest_user") . "'") == $_SESSION["sess_user_id"])) {
			kill_session_var("sess_user_id");
		}
	}
	
	if (empty($_SESSION["sess_user_id"])) {
		include ("./auth_login.php");
		exit;
	}elseif (!empty($_SESSION["sess_user_id"])) {
		$realm_id = db_fetch_cell("select realm_id from user_realm_filename where filename='" . basename($_SERVER["PHP_SELF"]) . "'");
		
		if (!db_fetch_assoc("select
			user_auth_realm.realm_id
			from
			user_auth_realm
			where user_auth_realm.user_id='" . $_SESSION["sess_user_id"] . "'
			and user_auth_realm.realm_id='$realm_id'")) {
			
			include_once ($config["include_path"] . "/form.php");
			include ($config["include_path"] . "/top_header.php");
			
			print "	<table width='98%' align='center'>\n
					<tr>\n
						<td colspan='2'><img src='images/auth_deny.gif' border='0' alt='Access Denied'></td>\n
					</tr>\n
					<tr height='10'><td></td></tr>\n
					<tr>\n
						<td class='textArea' colspan='2'>You are not permitted to access this section of cacti. If you feel that you 
						need access to this particular section, please contact the webmaster.</td>\n
					</tr>\n
				</table>\n";
			
			include ($config["include_path"] . "/bottom_footer.php");
			exit;
		}
	}
}

?>
