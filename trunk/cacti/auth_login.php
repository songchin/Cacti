<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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

include("./lib/user/user_ldap.php");
include("./lib/user/user_action.php");

/* set default action */
if (!isset($_REQUEST["action"])) {
	$action = "";
}else{
	$action = $_REQUEST["action"];
}


/* Get the username */
cacti_log("LOGIN: Figuring out username", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
if (read_config_option("auth_method") == "2") {
	/* Get the Web Basic Auth username and set action so we login right away */
	cacti_log("LOGIN: Web Basic Authenication enabled, getting username from webserver", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
	$action = "login";
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$username = $_SERVER["PHP_AUTH_USER"];
	} else {
		/* No user - Bad juju! */
		$username = "";
		auth_display_custom_error_message("Web Basic Authentication configured, but no username was passed from the web server.  Please make sure you have authentication enabled on the web server.");
		cacti_log("No username passed with Web Basic Authentication enabled.", SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
		exit;
	}
}else{
	/* LDAP and Builtin get username from Form */
	cacti_log("LOGIN: Builtin or LDAP Authenication, getting username from form post", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
	if (isset($_POST["login_username"])) {
		$username = $_POST["login_username"];
	}else{
		$username = "";
	}
}
cacti_log("LOGIN: Username set to '" . $username . "'", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);


/* process login */
$copy_user = false;
$user_auth = false;
$user_enabled = 1;
$ldap_error = false;
$ldap_error_message = "";
if ($action == 'login') {

	switch (read_config_option("auth_method")) {
	case "0":
		cacti_log("LOGIN: No Authenication enabled", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		/* No auth, no action, also shouldn't get here */
		exit;
		break;
	case "2":
		/* Web Basic Auth */
		cacti_log("LOGIN: Web Basic Authenication enabled", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		$copy_user = true;
		$user_auth = true;
		$realm = 0;
		/* Locate user in database */
		$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 0");
		break;
	case "3":
		/* LDAP Auth */
		cacti_log("LOGIN: LDAP Authentication enabled", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
 		if (($_POST["realm"] == "ldap") && (strlen($_POST["login_password"]) > 0)) {
			cacti_log("LOGIN: LDAP realm selected", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);

			/* get user DN */
			cacti_log("LOGIN: LDAP Generating DN", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$ldap_dn_search_response = api_user_ldap_search_dn($username);
			if ($ldap_dn_search_response["error_num"] == "0") {
				$ldap_dn = $ldap_dn_search_response["dn"];
				cacti_log("LOGIN: LDAP DN: " . $ldap_dn, SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			}else{
				/* Error searching */
				cacti_log("LOGIN: LDAP Error: " . $ldap_dn_search_response["error_text"], SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
				$ldap_error = true;
				$ldap_error_message = "LDAP Search Error: " . $ldap_dn_search_response["error_text"];
				$user_auth = false;
				$user = array();
			}

			if (!$ldap_error) {
				/* auth user with LDAP */
				cacti_log("LOGIN: LDAP Authenication processing", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
				$ldap_auth_response = api_user_ldap_auth($username,$_POST["login_password"],$ldap_dn);

				if ($ldap_auth_response["error_num"] == "0") {
					/* User ok */
					$user_auth = true;
					$copy_user = true;
					/* Locate user in database */
					cacti_log("LOGIN: LDAP User '" . $username . "' Authenicated", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
					$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 1");
				}else{
					/* error */ 
					cacti_log("LOGIN: LDAP Error: " . $ldap_auth_response["error_text"], SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
					$ldap_error = true;
					$ldap_error_message = "LDAP Error: " . $ldap_auth_response["error_text"];
					$user_auth = false;
					$user = array();
				}
			}

		}

	case "1":
		/* Builtin Auth */
		if ((!$user_auth) && (!$ldap_error)) {
			/* if auth has not occured process for builtin - AKA Ldap fall through */
			cacti_log("LOGIN: Builtin Authenication enabled", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$user = db_fetch_row("select * from user_auth where username='" . $username . "' and password = '" . md5($_POST["login_password"]) . "' and realm = 0");
		}
	}
	/* end of switch */

	/* Create user from template if requested */
	if ((!sizeof($user)) && ($copy_user) && (read_config_option("user_template") != "0") && (strlen($username) > 0)) {
		cacti_log("LOGIN: User '" . $username . "' does not exist, copying template user", SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
		/* check that template user exists */
		if (db_fetch_row("select * from user_auth where username='" . read_config_option("user_template") . "' and realm = 0")) {
			cacti_log("LOGIN: Coping Template user '" . read_config_option("user_template") . "' to user '" . $username . "'", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			/* template user found */
			api_user_copy(read_config_option("user_template"), $username, $realm);
			/* requery newly created user */
			$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = " . $realm);
		}else{
			/* error */
			auth_display_custom_error_message("Template user \"" . read_config_option("user_template") . "\" does not exist.");
			cacti_log("LOGIN: Unable to locate template user '" . read_config_option("user_template") . "'", SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}
	}

	/* Guest account checking - Not for builtin */
	$guest_user = false;
	if ((! $user) && ($user_auth) && (read_config_option("guest_user") != "0")) {
		cacti_log("LOGIN: Authenicated user, but no cacti user record, loading guest account", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
		/* Locate guest user record */
		$user = db_fetch_row("select * from user_auth where username='" . read_config_option("guest_user") . "'");
		if ($user) {
			cacti_log("LOGIN: Authenicated user '" . $username . "' using guest account '" . $user["username"] . "'", SEV_INFO, 0, 0, 0, false, FACIL_AUTH);
			$guest_user = true;
		}else{
			/* error */
			auth_display_custom_error_message("Guest user \"" . read_config_option("guest_user") . "\" does not exist.");
			cacti_log("LOGIN: Unable to locate guest user '" . read_config_option("guest_user") . "'", SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}
	}

	/* Process the user  */
	if (sizeof($user)) {
		cacti_log("LOGIN: User '" . $user["username"] . "' authenicated", SEV_NOTICE, 0, 0, 0, false, FACIL_AUTH);

		/* is user enabled */
		$user_enabled = $user["enabled"];
		if ($user_enabled == "0") {
			if (read_config_option("auth_method") == "2") {
				/* Display error */
				cacti_log("LOGIN: User '" . $user["username"] . "' is disabled", SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
				auth_display_custom_error_message("Access Denied, user account disabled.");
				exit;
			}
			$action = "";
		}else{

			/* set the php session */
			$_SESSION["sess_user_id"] = $user["id"];
			cacti_log("LOGIN: Setting up session variables", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);


			/* Update ip and lastlogin information for the user*/
			cacti_log("LOGIN: Updating user last login information", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			$user_save["id"] = $user["id"];
			$user_save["last_login"] = "now()";
			$user_save["last_login_ip"] = $_SERVER["REMOTE_ADDR"];
			api_user_save($user_save);
			unset($user_save);

			/* handle "force change password" */
			if ($user["must_change_password"] == "on") {
				cacti_log("LOGIN: Setting user force change password", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
				$_SESSION["sess_change_password"] = true;
			}

			/* ok, at the point the user has been sucessfully authenticated; so we must
			decide what to do next */
			cacti_log("LOGIN: Figuring out URL to send user to", SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			switch ($user["login_opts"]) {
				case '1': /* referer */
					if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where realm_id=8 and user_id=" . $_SESSION["sess_user_id"])) == 0) {
						$url_location = "graph_view.php";
					}else{
						$url_location  = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php");
					}
					break;
				case '2': /* default console page */
					$url_location = "index.php"; break;
				case '3': /* default graph page */
					$url_location = "graph_view.php"; break;
			}
			cacti_log("LOGIN: URL: " . $url_location, SEV_DEBUG, 0, 0, 0, false, FACIL_AUTH);
			header("Location: " . $url_location);
			exit;
		}
	}else{
		if ((!$guest_user) && ($user_auth)) {
			/* No guest account defined */
			auth_display_custom_error_message("Access Denied, please contact you Cacti Administrator.");
			cacti_log("LOGIN: Access Denied, No guest enabled or template user to copy", SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
			exit;
		}else{
			/* BAD username/password builtin and LDAP */
			cacti_log("LOGIN: Invalid username '" . $username . "' and password", SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
			db_execute("insert into user_log (username,user_id,result,ip,time) values('" . $username . "',0,0,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");
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
        print "     <title>cacti</title>\n";
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
	<title>Login to Cacti</title>
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
<body onload="document.login.login_username.focus()">
<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">
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
	<?php }else {
	if ($action == "login") {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>Invalid User Name/Password Please Retype:</strong></font></td>
	</tr>
	<?php }
	if ($user_enabled == "0") {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>User Account Disabled</strong></font></td>
	</tr>
	<?php } } ?>


	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2">Please enter your Cacti user name and password below:</td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td>User Name:</td>
		<td><input type="text" name="login_username" size="40" style="width: 295px;" value="<?php print $username; ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="login_password" size="40" style="width: 295px;"></td>
	</tr>
	<?php
	if (read_config_option("auth_method") == "3") {?>
        <tr>
                <td>Realm:</td>
                <td>
			<select name="realm" style="width: 295px;">
				<option value="local">Local</option>
				<option value="ldap" selected>LDAP</option>
			</select>
		</td>
        </tr>
	<?php }?>
	<tr height="10"><td></td></tr>
	<tr>
		<td><input type="submit" value="Login"></td>
	</tr>
</table>
<input type="hidden" name="action" value="login">
</form>
</body>
</html>
