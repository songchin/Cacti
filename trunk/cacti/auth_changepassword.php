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

include("./include/config.php");
include("./lib/user/user_action.php");

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


		if (api_user_info( array( "id" => $_SESSION["sess_user_id"], "password" => md5($_POST["password"]) ) ) ) {
			$old_password = true;
		}else{
			if (($_POST["password"] == $_POST["confirm"]) && ($_POST["password"] != "")) {

				/* Log password change */
				api_syslog_cacti_log("CHANGEPASSWORD: Password change successful", SEV_INFO, 0, 0, 0, false, FACIL_AUTH);

				/* change password */
				api_user_changepassword($_SESSION["sess_user_id"], $_POST["password"]);

				kill_session_var("sess_change_password");

				/* ok, at the point the user has been successfully authenticated; so we must
				decide what to do next */

				/* if no console permissions show graphs otherwise, pay attention to user setting */
				$user_realms = api_user_realms_list($_SESSION["sess_user_id"]);

				if ($user_realms[$user_auth_realm_filenames["index.php"]]["value"] == "1") {
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
		<td colspan="2" align="center"><font color="#FF0000" size="+2"><strong><?php echo _("Access Denied");?></strong></font></td>
	</tr>
<?php }else{ ?>
	<?php if ($bad_password == true) {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong><?php echo _("Your passwords do not match, please retype:"); ?></strong></font></td>
	</tr>
	<?php }
	if ($old_password == true) {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong><?php echo _("You cannot reuse your old password, please retype:"); ?></strong></font></td>
	</tr>
	<?php }?>

	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2">
			<strong><font color="#FF0000"><?php echo _("*** Forced Password Change ***"); ?></font></strong><br><br>
			<?php echo _("Please enter a new password for cacti:"); ?>
		</td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td><?php echo _("Password:)"; ?></td>
		<td><input type="password" name="password" size="40"></td>
	</tr>
	<tr>
		<td><?php echo _("Confirm:"); ?></td>
		<td><input type="password" name="confirm" size="40"></td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td><input type="submit" value="<?php echo _("Save"); ?>"></td>
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