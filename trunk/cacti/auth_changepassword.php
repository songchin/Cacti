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

include("./include/config.php");
include("./lib/api_user.php");

$user = api_user_info( array( "id" => $_SESSION["sess_user_id"] ) );

/* find out if we are logged in as a 'guest user' or not */
$access_denied = false;
if (read_config_option("guest_user") != "0") {
	if ($user["username"] == read_config_option("guest_user")) {
		$access_denied = true;
	}
}

/* check that force password change it set */
if (!isset($_SESSION["sess_change_password"])) {
	$access_denied = true;
}

/* default to !bad_password */
$bad_password = false;
$old_password = false;

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

if (!$access_denied) {

	switch ($_REQUEST["action"]) {
	case 'changepassword':
	
		
		if (db_fetch_cell("select password from user_auth where id=" . $_SESSION["sess_user_id"]) == md5($_POST["password"])) {
			$old_password = true;
		}else{
			if (($_POST["password"] == $_POST["confirm"]) && ($_POST["password"] != "")) {

				/* Log password change */
				db_execute("insert into user_log (user_id,username,time,result,ip) values('" . $_SESSION["sess_user_id"] . "','" . $user["username"] . "',NOW(),3,'" . $_SERVER["REMOTE_ADDR"] . "')");
	
				/* change password */
				api_user_changepassword($_SESSION["sess_user_id"], $_POST["password"]);
		
				kill_session_var("sess_change_password");

				/* ok, at the point the user has been sucessfully authenticated; so we must
				decide what to do next */
	
				/* if no console permissions show graphs otherwise, pay attention to user setting */
				$realm_id = $user_auth_realm_filenames["index.php"];

				if (sizeof(db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id = '" . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id = '" . $realm_id . "'")) > 0) {
					switch ($user["login_opts"]) {
						case '1': /* referer */
							header("Location: " . $_POST["ref"]); break;
						case '2': /* default console page */
							header("Location: index.php"); break;
						case '3': /* default graph page */
							header("Location: graph_view.php"); break;
					}
				}else{
					header("Location: graph_view.php");
				}
	
				exit;
			}else{
				$bad_password = true;
			}
		}
		break;
	}

}
?>
<html>
<head>
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
<?php if (!$access_denied) { ?>

<body onload="document.login.password.focus()">

<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">

<?php } ?>

<table align="center">
	<tr>
		<td colspan="2"><img src="<?php print html_get_theme_images_path('auth_login.gif');?>" border="0" alt=""></td>
	</tr>
<?php if ($access_denied) { ?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2" align="center"><font color="#FF0000" size="+2"><strong>Access Denied</strong></font></td>
	</tr>
<?php }else{ ?>
	<?php if ($bad_password == true) {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>Your passwords do not match, please retype:</strong></font></td>
	</tr>
	<?php }
	if ($old_password == true) {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>You cannot reuse your old password, please retype:</strong></font></td>
	</tr>
	<?php }?>

	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2">
			<strong><font color="#FF0000">*** Forced Password Change ***</font></strong><br><br>
			Please enter a new password for cacti:
		</td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password" size="40"></td>
	</tr>
	<tr>
		<td>Confirm:</td>
		<td><input type="password" name="confirm" size="40"></td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td><input type="submit" value="Save"></td>
	</tr>

<?php } ?>
</table>


<?php if (!$access_denied) { ?>
<input type="hidden" name="action" value="changepassword">
<input type="hidden" name="ref" value="<?php print $_REQUEST["ref"];?>">

</form>
<?php } ?>
</body>
</html>
