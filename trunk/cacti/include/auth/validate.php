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

/* check to see if this is a new installation */
if (db_fetch_cell("select cacti from version") != CACTI_VERSION) {
	header ("Location: install/");
	exit;
}

if (read_config_option("auth_method") != "0") {
	/* handle change password dialog - only with builtin auth */
	if ((isset($_SESSION['sess_change_password'])) && (read_config_option("auth_method") == 1)) {
		api_syslog_cacti_log(_("AUTH: User password change forced"), SEV_NOTICE, 0, 0, 0, false, FACIL_AUTH);
		header ("Location: auth_changepassword.php?ref=" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"));
		exit;
	}

	/* Check if we are logged in, and process guest account if set, used by graph_view.php */
	if ((isset($guest_account)) && (empty($_SESSION["sess_user_id"]))) {
		if (read_config_option("guest_user") != "0") {
			$user = api_user_info( array( "username" => read_config_option("guest_user"), "enabled" => "1" ) );
			$guest_user_id = $user["id"];
			if (!empty($guest_user_id)) {
				$_SESSION["sess_user_id"] = $guest_user_id;
			}
			api_syslog_cacti_log(_("AUTH: Guest access enabled, using username '") . $user["username"] . _("' as guest"), SEV_INFO, 0, 0, 0, false, FACIL_AUTH);
		}
	}

	/* if we are a guest user in a non-guest area, wipe credentials and prompt for login */
	if (!empty($_SESSION["sess_user_id"])) {
		if ((!isset($guest_account)) && ( sizeof( api_user_info( array( "username" => read_config_option("guest_user") ) ) ) == $_SESSION["sess_user_id"])) {
			kill_session_var("sess_user_id");
		}
	}

	if (empty($_SESSION["sess_user_id"])) {
		/* User not authenticated, prompt for login */
		require_once(CACTI_BASE_PATH . "/include/auth/login.php");
		exit;
	}elseif (!empty($_SESSION["sess_user_id"])) {
		/* User authenticated */

		/* check if password is expired */
		if (api_user_expire_info($_SESSION["sess_user_id"]) == "0") {
			$_SESSION["sess_change_password"] = true;
			if ((read_config_option("auth_method") == 1) || (($current_user["realm"] == "0") && (read_config_option("auth_method") == "3"))) {
				api_syslog_cacti_log(_("AUTH: User password expired, password change forced"), SEV_NOTICE, 0, 0, 0, false, FACIL_AUTH);
				header ("Location: auth_changepassword.php?ref=" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"));
				exit;
			}
		}

		/* Check permissions to use this realm against database */
		$realm_id = 0;

		if (isset($user_auth_realm_filenames{basename($_SERVER["PHP_SELF"])})) {
			$realm_id = $user_auth_realm_filenames{basename($_SERVER["PHP_SELF"])};
		}

		$user_realms = api_user_realms_list($_SESSION["sess_user_id"]);

		if ($user_realms[$realm_id]["value"] != "1") {
			api_syslog_cacti_log(_("AUTH: User access denied to realm ") . $user_auth_realms[$realm_id], SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
			?>
			<html>
			<head>
				<link rel='shortcut icon' href='<?php print html_get_theme_images_path("favicon.ico");?>' type='image/x-icon'>
				<link href='<?php print html_get_theme_images_path("favicon.ico");?>' rel='image/x-icon'>
				<title><?php echo _("Cacti");?></title>
				<link href='<?php print html_get_theme_css();?>' rel='stylesheet'>
			</style>
			</head>

			<br><br>

			<table width='450' align='center'>
				<tr>
					<td colspan='2'><img src='<?php print html_get_theme_images_path("auth_deny.gif");?>' border='0' alt='" . _("Access Denied") . "'></td>
				</tr>
				<tr height='10'><td></td></tr>
				<tr>
					<td class='textArea' colspan='2'><?php print _("You are not permitted to access this section of Cacti. If you feel that you need access to this particular section, please contact the Cacti administrator."); ?></td>
				</tr>
				<tr>
                                        <td class='textArea' colspan='2' align='center'>( <a href='' onclick='javascript: history.back();'><?php print _("Return"); ?></a> | <a href='logout.php'><?php print _("Login"); ?></a> )</td>
                                </tr>
			</table>

			</body>
			</html>
			<?php
			exit;
		}
	}
}

?>
