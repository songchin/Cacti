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
include("./include/auth.php");
include("./lib/user/user_action.php");


/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }


/* process action */
switch ($_REQUEST["action"]) {
	case 'save':
		change_password();
		break;
	default:
		change_password_form();
		break;
}

function change_password() {
	global $colors;

	$change_result = 1;

	$user_realms = api_user_realms_list($_SESSION["sess_user_id"]);

	/* check if authorized */
	if ($user_realms["18"]["value"] == "1") {
		/* check passwords */
		if ((!empty($_POST["password_old"])) && (!empty($_POST["password_new"])) && (!empty($_POST["password_new_confirm"]))) {
			if ($_POST["password_new"] != $_POST["password_new_confirm"]) {
				/* New passwords do not match */
				raise_message(4);
			}else{
				$change_result = api_user_changepassword($_SESSION["sess_user_id"],$_POST["password_new"],$_POST["password_old"]);
				if ($change_result == "0") {
					/* Password changed successfully */
					raise_message(11);
					/* Log password change */
					$username = db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);
					cacti_log("CHANGEPASSWORD: Password change successful", SEV_INFO, 0, 0, 0, false, FACIL_AUTH);
				}elseif ($change_result == "2") {
					/* Authentication failure for old password */
					raise_message(8);
					cacti_log("CHANGEPASSWORD: Authenication failure on old password", SEV_WARNING, 0, 0, 0, false, FACIL_AUTH);
				}else{
					/* General error changing password */
					raise_message(9);
					cacti_log("CHANGEPASSWORD: General Error unable to change password", SEV_ERROR, 0, 0, 0, false, FACIL_AUTH);
				}
			}
		}else{
			/* error empty fields */
			raise_message(10);
		}
	}

	include_once("include/top_header.php");
	if ($user_realms["18"]["value"] != "1") {
		/* Access Denied */
		display_custom_error_message("Access Denied.");
	}
	include_once("include/bottom_footer.php");

}



function change_password_form() {
	global $colors;

	$user = api_user_info( array( "id" => $_SESSION["sess_user_id"]) );

	$user_realms = api_user_realms_list($_SESSION["sess_user_id"]);

	$form_fields = array (
		"password_old" => array(
			"method" => "textbox_password_single",
			"friendly_name" => "Current Password",
			"description" => "Enter your current password validation.",
			"value" => "",
			"max_length" => "255"
		),
		"password_new" => array(
			"method" => "textbox_password",
			"friendly_name" => "New Password",
			"description" => "Enter your new password twice. Remember that passwords are case sensitive!",
			"value" => "",
			"max_length" => "255"
		),

	);


	include_once("include/top_header.php");

	/* check if authorized */
	if ($user_realms["18"]["value"] == "1") {
		if ((read_config_option("auth_method") == "1") || (($current_user["realm"] == "0") && (read_config_option("auth_method") == "3"))) {
			/* Builtin auth method, password can be changed */
			html_start_box("<strong>Change Password</strong>", "98%", $colors["header_background"], "3", "center", "");
			draw_edit_form(array(
				"config" => array("form_name" => "chk"),
				"fields" => inject_form_variables($form_fields, (isset($user) ? $user : array()))
				));
			html_end_box();
			form_save_button((isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"),"save");
		}else{
			/* Password changing not supported */
			display_custom_error_message("Current selected Authentication Method does not support changing of passwords.");
		}
	}else{
		/* access denied */
		display_custom_error_message("Access Denied.");
	}

	include_once("include/bottom_footer.php");
}

?>
