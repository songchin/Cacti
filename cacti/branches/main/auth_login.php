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

/* set default action */
if (isset($_REQUEST["action"])) {
	$action = $_REQUEST["action"];
}else{
	$action = "";
}

/* Get the username */
if (read_config_option("auth_method") == "2") {
	/* Get the Web Basic Auth username and set action so we login right away */
	$action = "login";
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["PHP_AUTH_USER"]);
	}elseif (isset($_SERVER["REMOTE_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["REMOTE_USER"]);
	}else{
		/* No user - Bad juju! */
		$username = "";
		cacti_log("ERROR: No username passed with Web Basic Authentication enabled.", false, "AUTH");
		auth_display_custom_error_message(_("Web Basic Authentication configured, but no username was passed from the web server.  Please make sure you have authentication enabled on the web server."));
		exit;
	}
}else{
	if ($action == "login") {
		/* LDAP and Builtin get username from Form */
		$username = get_request_var_post("login_username");
	}else{
		$username = "";
	}
}

$username = sanitize_search_string($username);

/* process login */
$copy_user = false;
$user_auth = false;
$user_enabled = 1;
$ldap_error = false;
$ldap_error_message = "";
$realm = 0;
if ($action == 'login') {

	switch (read_config_option("auth_method")) {
	case "0":
		/* No auth, no action, also shouldn't get here */
		exit;
		break;

	case "2":
		/* Web Basic Auth */
		$copy_user = true;
		$user_auth = true;
		$realm = 2;
		/* Locate user in database */
		$user = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $username . "' AND realm = 2");
		break;

	case "3":
		/* LDAP Auth */
 		if ((get_request_var_post("realm") == "ldap") && (strlen(get_request_var_post("login_password")) > 0)) {

			/* include LDAP lib */
			include_once(CACTI_BASE_PATH . "/lib/ldap.php");

			/* get user DN */
			$ldap_dn_search_response = cacti_ldap_search_dn($username);
			if ($ldap_dn_search_response["error_num"] == "0") {
				$ldap_dn = $ldap_dn_search_response["dn"];
			}else{
				/* Error searching */
				cacti_log("LOGIN: LDAP Error: " . $ldap_dn_search_response["error_text"], false, "AUTH");
				$ldap_error = true;
				$ldap_error_message = __("LDAP Search Error: ") . $ldap_dn_search_response["error_text"];
				$user_auth = false;
				$user = array();
			}

			if (!$ldap_error) {
				/* auth user with LDAP */
				$ldap_auth_response = cacti_ldap_auth($username,stripslashes(get_request_var_post("login_password")),$ldap_dn);

				if ($ldap_auth_response["error_num"] == "0") {
					/* User ok */
					$user_auth = true;
					$copy_user = true;
					$realm = 1;
					/* Locate user in database */
					cacti_log("LOGIN: LDAP User '" . $username . "' Authenticated", false, "AUTH");
					$user = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $username . "' AND realm = 1");
				}else{
					/* error */
					cacti_log("LOGIN: LDAP Error: " . $ldap_auth_response["error_text"], false, "AUTH");
					$ldap_error = true;
					$ldap_error_message = __("LDAP Error: ") . $ldap_auth_response["error_text"];
					$user_auth = false;
					$user = array();
				}
			}

		}

	default:
		/* Builtin Auth */
		if ((!$user_auth) && (!$ldap_error)) {
			/* if auth has not occured process for builtin - AKA Ldap fall through */
			$user = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $username . "' AND password = '" . md5(get_request_var_post("login_password")) . "' AND realm = 0");
		}
	}
	/* end of switch */

	/* Create user from template if requested */
	if ((! sizeof($user)) && ($copy_user) && (read_config_option("user_template") != "0") && (strlen($username) > 0)) {
		cacti_log("WARN: User '" . $username . "' does not exist, copying template user", false, "AUTH");
		/* check that template user exists */
		if (db_fetch_row("SELECT id FROM user_auth WHERE username = '" . read_config_option("user_template") . "' AND realm = 0")) {
			/* template user found */
			user_copy(read_config_option("user_template"), $username, 0, $realm);
			/* requery newly created user */
			$user = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . $username . "' AND realm = " . $realm);
		}else{
			/* error */
			cacti_log("LOGIN: Template user '" . read_config_option("user_template") . "' does not exist.", false, "AUTH");
			auth_display_custom_error_message(__("Template user '") . read_config_option("user_template") . __("' does not exist."));
			exit;
		}
	}

	/* Guest account checking - Not for builtin */
	$guest_user = false;
	if ((sizeof($user) < 1) && ($user_auth) && (read_config_option("guest_user") != "0")) {
		/* Locate guest user record */
		$user = db_fetch_row("SELECT * FROM user_auth WHERE username = '" . read_config_option("guest_user") . "'");
		if ($user) {
			cacti_log("LOGIN: Authenicated user '" . $username . "' using guest account '" . $user["username"] . "'", false, "AUTH");
			$guest_user = true;
		}else{
			/* error */
			auth_display_custom_error_message(__("Guest user \"") . read_config_option("guest_user") . __("\" does not exist."));
			cacti_log("LOGIN: Unable to locate guest user '" . read_config_option("guest_user") . "'", false, "AUTH");
			exit;
		}
	}

	/* Process the user  */
	if (sizeof($user) > 0) {
		cacti_log("LOGIN: User '" . $user["username"] . "' Authenticated", false, "AUTH");
		db_execute("INSERT INTO user_log (username,user_id,result,ip,time) VALUES ('" . $username ."'," . $user["id"] . ",1,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");
		/* is user enabled */
		$user_enabled = $user["enabled"];
		if ($user_enabled != CHECKED) {
			/* Display error */
			auth_display_custom_error_message(__("Access Denied, user account disabled."));
			exit;
		}

		/* set the php session */
		$_SESSION["sess_user_id"] = $user["id"];

		/* handle "force change password" */
		if (($user["must_change_password"] == CHECKED) && (read_config_option("auth_method") == 1)) {
			$_SESSION["sess_change_password"] = true;
		}

		/* ok, at the point the user has been sucessfully authenticated; so we must
		decide what to do next */
		switch ($user["login_opts"]) {
			case '1': /* referer */
				if (sizeof(db_fetch_assoc("SELECT realm_id FROM user_auth_realm WHERE realm_id = 8 AND user_id = " . $_SESSION["sess_user_id"])) == 0) {
					header("Location: graph_view.php");
				}else{
					if (isset($_SERVER["HTTP_REFERER"])) {
						$referer = $_SERVER["HTTP_REFERER"];
						if (basename($referer) == "logout.php") {
							$referer = "index.php";
						}
					} else if (isset($_SERVER["REQUEST_URI"])) {
						$referer = $_SERVER["REQUEST_URI"];
						if (basename($referer) == "logout.php") {
							$referer = "index.php";
						}
					} else {
						$referer = "index.php";
					}
					header("Location: " . $referer);
				}
				break;
			case '2': /* default console page */
				header("Location: index.php"); break;
			case '3': /* default graph page */
				header("Location: graph_view.php"); break;
			default:
				api_plugin_hook_function('login_options_navigate', $user['login_opts']);
		}
		exit;

	}else{
		if ((!$guest_user) && ($user_auth)) {
			/* No guest account defined */
			auth_display_custom_error_message(__("Access Denied, please contact you Cacti Administrator."));
			cacti_log("LOGIN: Access Denied, No guest enabled or template user to copy", false, "AUTH");
			exit;
		}else{
			/* BAD username/password builtin and LDAP */
			db_execute("INSERT INTO user_log (username,user_id,result,ip,time) VALUES ('" . $username . "',0,0,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");
		}
	}
}

/* auth_display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @param $message - the actual text of the error message to display */
function auth_display_custom_error_message($message) {
	global $config;
	/* kill the session */
	setcookie(session_name(),"",time() - 3600,"/");
	/* print error */
	print "<html>\n<head>\n";
	print "     <title>" . "Cacti" . "</title>\n";
	print "     <link href=\"" . CACTI_URL_PATH . "include/main.css\" rel=\"stylesheet\" type=\"text/css\">";
	print "</head>\n";
	print "<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n<br><br>\n";
	display_custom_error_message($message);
	print "</body>\n</html>\n";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta http-equiv="Content-Script-Type" content="text/javascript" >
	<meta http-equiv="Content-Style-Type" content="text/css">
	<link type="text/css" href="include/main.css" rel="stylesheet">
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
<body id='authBody' onload="document.login.login_username.focus()">
	<div id='autoContainer'>
		<div id='authLogo'></div>
		<div id='authLogin'>
			<form action="<?php print basename($_SERVER['PHP_SELF']);?>" name="login" method="post">
			<input type="hidden" name="action" value="login">
			<?php api_plugin_hook("login_before"); ?>
			<table align='center'>
				<?php

				if ($ldap_error) {?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2"><font color="#FF0000"><strong><?php print $ldap_error_message; ?></strong></font></td>
				</tr>
				<?php }else{
				if ($action == "login") {?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2"><font color="#FF0000"><strong><?php print __("Invalid User Name/Password Please Retype");?></strong></font></td>
				</tr>
				<?php }
				if ($user_enabled == "0") {?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2"><font color="#FF0000"><strong><?php print __("User Account Disabled");?></strong></font></td>
				</tr>
				<?php } } ?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2"><?php print __("Please enter your Cacti user name and password below:");?></td>
				</tr>
				<tr><td></td></tr>
				<tr>
					<td><?php print __("User Name:");?></td>
					<td><input type="text" name="login_username" size="40" class='nw295' value="<?php print $username; ?>"></td>
				</tr>
				<tr>
					<td><?php print __("Password:");?></td>
					<td><input type="password" name="login_password" size="40" class='nw295'></td>
				</tr>
				<?php
				if (read_config_option("auth_method") == "3") {?>
				<tr>
					<td><?php print __("Realm:");?></td>
					<td>
						<select name="realm" class='nw295'>
							<option value="local"><?php print __("Local");?></option>
							<option value="ldap" selected><?php print __("LDAP");?></option>
						</select>
					</td>
				</tr>
				<?php }?>
				<tr><td></td></tr>
				<tr>
					<td><input type="submit" value="<?php print __("Login");?>"></td>
				</tr>
			</table>
			<?php api_plugin_hook("login_after"); ?>

			</form>
		</div>
		<div id='authFooter'></div>
	</div>
</body>
</html>
