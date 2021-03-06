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

include("./include/global.php");

/* find out if we are logged in as a 'guest user' or not, if we are redirect away from password change */
if (db_fetch_cell("select id from user_auth where username='" . read_config_option("guest_user") . "'") == $_SESSION["sess_user_id"]) {
	header("Location: index.php");
	exit;
}

$user = db_fetch_row("select * from user_auth where id=" . $_SESSION["sess_user_id"]);

/* default to !bad_password */
$bad_password = false;

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
case 'changepassword':
	if ((get_request_var_post("password") == get_request_var_post("confirm")) && (get_request_var_post("password") != "")) {
		db_execute("insert into user_log (username,result,ip) values('" . $user["username"] . "',3,'" . $_SERVER["REMOTE_ADDR"] . "')");
		db_execute("update user_auth set must_change_password='',password='" . md5($_POST["password"]) . "' where id=" . $_SESSION["sess_user_id"]);

		kill_session_var("sess_change_password");

		/* ok, at the point the user has been sucessfully authenticated; so we must
		decide what to do next */

		/* if no console permissions show graphs otherwise, pay attention to user setting */
		$realm_id = $user_auth_realm_filenames["index.php"];

		if (sizeof(db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id = '" . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id = '" . $realm_id . "'")) > 0) {
			switch ($user["login_opts"]) {
				case '1': /* referer */
					header("Location: " . get_request_var_post("ref")); break;
				case '2': /* default console page */
					header("Location: index.php"); break;
				case '3': /* default graph page */
					header("Location: graph_view.php"); break;
				default:
					api_plugin_hook_function('login_options_navigate', $user['login_opts']);
			}
		}else{
			header("Location: graph_view.php");
		}
		exit;

	}else{
		$bad_password = true;
	}

	break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta http-equiv="Content-Script-Type" content="text/javascript" >
	<meta http-equiv="Content-Style-Type" content="text/css">
	<link href="include/main.css" rel="stylesheet" type="text/css">
	<title>Login to cacti</title>
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
<body id='authBody' onload="document.login.password.focus()">
	<div id='autoContainer'>
		<div id='authLogo'></div>
		<div id='authLogin'>
			<form action="<?php print basename($_SERVER['PHP_SELF']);?>" name="login" method="post">
			<table align="center">
				<?php if ($bad_password == true) {?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2"><font color="#FF0000"><strong><?php print __("Your passwords do not match, please retype:");?></strong></font></td>
				</tr>
				<?php }?>
				<tr><td></td></tr>
				<tr>
					<td colspan="2">
						<strong><font color="#FF0000"><?php print __("*** Forced Password Change ***");?></font></strong><br><br>
						<?php print __("Please enter a new password for cacti:");?>
					</td>
				</tr>
				<tr><td></td></tr>
				<tr>
					<td><?php print __("Password:");?></td>
					<td><input type="password" name="password" size="40"></td>
				</tr>
				<tr>
					<td><?php print __("Confirm:");?></td>
					<td><input type="password" name="confirm" size="40"></td>
				</tr>
				<tr><td></td></tr>
				<tr>
					<td><input type="submit" value="<?php print __("Save");?>"></td>
				</tr>
			</table>
			<input type="hidden" name="action" value="changepassword">
			<input type="hidden" name="ref" value="<?php print $_REQUEST["ref"];?>">
			</form>
		</div>
		<div id='authFooter'></div>
	</div>
</body>
</html>
