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
					db_execute("insert into user_log (user_id,username,time,result,ip) values('" . $_SESSION["sess_user_id"] . "','" . $username . "',NOW(),3,'" . $_SERVER["REMOTE_ADDR"] . "')");
				}elseif ($change_result == "2") {
					/* Authentication failure for old password */
					raise_message(8);
				}else{
					/* General error changing password */
					raise_message(9);
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
		if (read_config_option("auth_method") == "1") {
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