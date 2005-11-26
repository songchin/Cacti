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

require_once(CACTI_BASE_PATH . "/lib/user/user_ldap.php");
require_once(CACTI_BASE_PATH . "/lib/user/user_action.php");

/* set default action */
if (!isset($_REQUEST["action"])) {
	$action = "";
}else{
	$action = $_REQUEST["action"];
}


/* Get the username */
if (read_config_option("auth_method") == "2") {
	/* Get the Web Basic Auth username and set action so we login right away */
	api_syslog_cacti_log(_("LOGIN: Web Basic Authenication enabled, getting username from webserver"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
	$action = "login";
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$username = $_SERVER["PHP_AUTH_USER"];
		api_syslog_cacti_log(sprintf(_("LOGIN: Username set to '%s'"), $username), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
	} else {
		/* No user - Bad juju! */
		$username = "";
		auth_display_custom_error_message(_("Web Basic Authentication configured, but no username was passed from the web server.  Please make sure you have authentication enabled on the web server."));
		api_syslog_cacti_log(_("No username passed with Web Basic Authentication enabled."), SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
		exit;
	}
}else{
	if ($action == "login") {
		/* LDAP and Builtin get username from Form */
		api_syslog_cacti_log(_("LOGIN: Builtin or LDAP Authenication, getting username from form post"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		if (isset($_POST["login_username"])) {
			$username = $_POST["login_username"];
		}else{
			$username = "";
		}
		api_syslog_cacti_log(sprintf(_("LOGIN: Username set to '%s'"), $username), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
	}else{
		$username = "";
	}
}


/* process login */
$copy_user = false;
$user_auth = false;
$user_enabled = 1;
$ldap_error = false;
$ldap_error_message = "";
if ($action == 'login') {

	switch (read_config_option("auth_method")) {
	case "0":
		api_syslog_cacti_log(_("LOGIN: No Authenication enabled"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		/* No auth, no action, also shouldn't get here */
		exit;
		break;
	case "2":
		/* Web Basic Auth */
		api_syslog_cacti_log(_("LOGIN: Web Basic Authenication enabled"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		$copy_user = true;
		$user_auth = true;
		$realm = 0;
		/* Locate user in database */
		$user = api_user_info(array( "username" => $username, "realm" => 0) );
		#$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 0");
		break;
	case "3":
		/* LDAP Auth */
		api_syslog_cacti_log(_("LOGIN: LDAP Authentication enabled"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
 		if (($_POST["realm"] == "ldap") && (strlen($_POST["login_password"]) > 0)) {
			api_syslog_cacti_log(_("LOGIN: LDAP realm selected"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);

			/* get user DN */
			api_syslog_cacti_log(_("LOGIN: LDAP Generating DN"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$ldap_dn_search_response = api_user_ldap_search_dn($username);
			if ($ldap_dn_search_response["error_num"] == "0") {
				$ldap_dn = $ldap_dn_search_response["dn"];
				api_syslog_cacti_log(_("LOGIN: LDAP DN: ") . $ldap_dn, SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			}else{
				/* Error searching */
				api_syslog_cacti_log(_("LOGIN: LDAP Error: ") . $ldap_dn_search_response["error_text"], SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
				$ldap_error = true;
				$ldap_error_message = _("LDAP Search Error: ") . $ldap_dn_search_response["error_text"];
				$user_auth = false;
				$user = array();
			}

			if (!$ldap_error) {
				/* auth user with LDAP */
				api_syslog_cacti_log(_("LOGIN: LDAP Authenication processing"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
				$ldap_auth_response = api_user_ldap_auth($username,$_POST["login_password"],$ldap_dn);

				if ($ldap_auth_response["error_num"] == "0") {
					/* User ok */
					$user_auth = true;
					$copy_user = true;
					/* Locate user in database */
					api_syslog_cacti_log(sprintf(_("LOGIN: LDAP User '%s' Authenticated"),$username), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
					$user = api_user_info( array( "username" => $username, "realm" => 1) );
					#$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 1");
				}else{
					/* error */
					api_syslog_cacti_log(_("LOGIN: LDAP Error: ") . $ldap_auth_response["error_text"], SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
					$ldap_error = true;
					$ldap_error_message = _("LDAP Error: ") . $ldap_auth_response["error_text"];
					$user_auth = false;
					$user = array();
				}
			}

		}

	case "1":
		/* Builtin Auth */
		if ((!$user_auth) && (!$ldap_error)) {
			/* if auth has not occured process for builtin - AKA Ldap fall through */
			api_syslog_cacti_log(_("LOGIN: Builtin Authenication enabled"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$user = api_user_info( array( "username" => $username, "password" => md5($_POST["login_password"]), "realm" => 0) );
			#$user = db_fetch_row("select * from user_auth where username='" . $username . "' and password = '" . md5($_POST["login_password"]) . "' and realm = 0");
		}
	}
	/* end of switch */

	/* Create user from template if requested */
	if ((! sizeof($user)) && ($copy_user) && (read_config_option("user_template") != "0") && (strlen($username) > 0)) {
		api_syslog_cacti_log(sprintf(_("LOGIN: User '%s' does not exist, copying template user"), $username), SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
		/* check that template user exists */
		if (api_user_info(array( "username" => read_config_option("user_template"), "realm" => 0) )) {
		#if (db_fetch_row("select * from user_auth where username='" . read_config_option("user_template") . "' and realm = 0")) {
			api_syslog_cacti_log(sprintf(_("LOGIN: Coping Template user '%s' to user '%s'"), read_config_option("user_template"), $username), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			/* template user found */
			api_user_copy(read_config_option("user_template"), $username, $realm);
			/* requery newly created user */
			$user = api_user_info( array( "username" => $username, "realm" => $realm ) );
			#$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = " . $realm);
		}else{
			/* error */
			auth_display_custom_error_message(sprintf(_("Template user '%s' does not exist."), read_config_option("user_template")));
			api_syslog_cacti_log(sprintf(_("LOGIN: Unable to locate template user '%s'"), read_config_option("user_template")), SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}
	}

	/* Guest account checking - Not for builtin */
	$guest_user = false;
	if ((sizeof($user) < 1) && ($user_auth) && (read_config_option("guest_user") != "0")) {
		api_syslog_cacti_log(_("LOGIN: Authenicated user, but no cacti user record, loading guest account"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		/* Locate guest user record */
		$user = api_user_info(array( "username" => read_config_option("guest_user") ));
		#$user = db_fetch_row("select * from user_auth where username='" . read_config_option("guest_user") . "'");
		if ($user) {
			api_syslog_cacti_log(sprintf(_("LOGIN: Authenicated user '%s' using guest account '%s'"), $username, $user["username"]), SEV_INFO, 0, 0, 0, false, FACIL_AUTH);
			$guest_user = true;
		}else{
			/* error */
			auth_display_custom_error_message("Guest user \"" . read_config_option("guest_user") . "\" does not exist.");
			api_syslog_cacti_log(sprintf(_("LOGIN: Unable to locate guest user '%s'"), read_config_option("guest_user")), SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}
	}

	/* Process the user  */
	if (sizeof($user) > 0) {
		api_syslog_cacti_log(sprintf(_("LOGIN: User '%s' Authenticated") , $user["username"]), SEV_NOTICE, 0, 0, 0, false, FACIL_AUTH);

		/* is user enabled */
		$user_enabled = $user["enabled"];
		if ($user_enabled == "0") {
			if (read_config_option("auth_method") == "2") {
				/* Display error */
				api_syslog_cacti_log(sprintf(_("LOGIN: User '%s' is disabled"), $user["username"]), SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
				auth_display_custom_error_message(_("Access Denied, user account disabled."));
				exit;
			}
			$action = "";
		}else{

			/* set the php session */
			$_SESSION["sess_user_id"] = $user["id"];
			api_syslog_cacti_log(_("LOGIN: Setting up session variables"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);

			/* Update ip and lastlogin information for the user*/
			api_syslog_cacti_log(_("LOGIN: Updating user last login information"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$user_save = array();	
			$user_save["id"] = array("type" => DB_TYPE_NUMBER, "value" => $user["id"]);
			$user_save["last_login"] = array("type" => DB_TYPE_FUNC_NOW, "value" => "");
			$user_save["last_login_ip"] = array("type" => DB_TYPE_STRING, "value" => $_SERVER["REMOTE_ADDR"]);
			api_user_save($user_save);
			unset($user_save);

			/* handle "force change password" */
			if ($user["must_change_password"] == "on") {
				api_syslog_cacti_log(_("LOGIN: Setting user force change password"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
				$_SESSION["sess_change_password"] = true;
			}

			/* ok, at the point the user has been sucessfully authenticated; so we must
			decide what to do next */
			api_syslog_cacti_log(_("LOGIN: Figuring out URL to send user to"), SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			switch ($user["login_opts"]) {
				case '1': /* referer */
					if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where realm_id=8 and user_id=" . $_SESSION["sess_user_id"])) == 0) {
						$url_location = "graph_view.php";
					}else{
						$url_location  = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php");
					}
					break;
				case '2': /* default console page */
					$url_location = "index.php";
					break;
				case '3': /* default graph page */
					$url_location = "graph_view.php";
					break;
				default:
					$url_location  = "index.php";
			}
			api_syslog_cacti_log(_("LOGIN: URL: ") . $url_location, SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			header("Location: " . $url_location);
			exit;
		}
	}else{
		if ((!$guest_user) && ($user_auth)) {
			/* No guest account defined */
			auth_display_custom_error_message(_("Access Denied, please contact you Cacti Administrator."));
			api_syslog_cacti_log(_("LOGIN: Access Denied, No guest enabled or template user to copy"), SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}else{
			/* BAD username/password builtin and LDAP */
			api_syslog_cacti_log(sprintf(_("LOGIN: Invalid username '%s' and password"), $username), SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
		}
	}
}

/* auth_display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @arg $message - the actual text of the error message to display */
function auth_display_custom_error_message($message) {
	/* kill the session */
	setcookie(session_name(),"",time() - 3600,"/");
	/* print error */
	print "<html>\n<head>\n";
        print "     <title>" . _("Cacti") . "</title>\n";
        print "     <link href=\"" . html_get_theme_css() . "\" rel=\"stylesheet\">";
	print "</head>\n";
	print "<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n<br><br>\n";
	display_custom_error_message($message);
        print "</body>\n</html>\n";
}

?>

<html>
<head>
	<link rel='shortcut icon' href='<?php print html_get_theme_images_path("favicon.ico");?>' type='image/x-icon'>
	<link href='<?php print html_get_theme_images_path("favicon.ico");?>' rel='image/x-icon'>
	<meta http-equiv='Content-Type' content='text/html; charset=<?php echo _("screen charset");?>'>
	<title><?php echo _("Login to Cacti");?></title>
	<STYLE TYPE="text/css">
	<!--
		BODY, TABLE, TR, TD {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;}
		A {text-decoration: none;}
		A:active { text-decoration: none;}
		A:hover {text-decoration: underline; color: #333333;}
		A:visited {color: Blue;}
	-->
	</style>
</head>
<body bgcolor="#FFFFFF" onload="document.login.login_username.focus()">
	<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">
	<input type="hidden" name="action" value="login">
	<table align="center">
		<tr>
			<td colspan="2"><img src="<?php print html_get_theme_images_path('auth_login.gif');?>" border="0" alt=""></td>
		</tr>
		<?php

		if ($ldap_error) {?>
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><font color="#FF0000"><strong><?php print $ldap_error_message; ?></strong></font></td>
		</tr>
		<?php }else{
		if ($action == "login") {?>
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><font color="#FF0000"><strong><?php echo _("Invalid User Name/Password Please Retype:"); ?></strong></font></td>
		</tr>
		<?php }
		if ($user_enabled == "0") {?>
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><font color="#FF0000"><strong><?php echo _("User Account Disabled"); ?></strong></font></td>
		</tr>
		<?php } } ?>
	
		<tr height="10"><td></td></tr>
		<tr>
			<td colspan="2"><?php echo _("Please enter your Cacti user name and password below:"); ?></td>
		</tr>
		<tr height="10"><td></td></tr>
		<tr>
			<td><?php echo _("User Name:"); ?></td>
			<td><input type="text" name="login_username" size="40" style="width: 295px;" value="<?php print $username; ?>"></td>
		</tr>
		<tr>
			<td><?php echo _("Password:"); ?></td>
			<td><input type="password" name="login_password" size="40" style="width: 295px;"></td>
		</tr>
		<?php
		if (read_config_option("auth_method") == "3") {?>
        	<tr>
	                <td>Realm:</td>
	                <td>
				<select name="realm" style="width: 295px;">
					<option value="local"><?php echo _("Local"); ?></option>
					<option value="ldap" selected>LDAP</option>
				</select>
			</td>
        	</tr>
		<?php }?>
		<tr height="10"><td></td></tr>
		<tr>
			<td><input type="submit" value="<?php echo _("Login");?>"></td>
		</tr>
	</table>
	</form>
</body>
</html>
