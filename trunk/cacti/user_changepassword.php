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

include("./include/auth.php");
include("./lib/api_user.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'change':
		/* change the password */
		
		break;
	default:

		change_password_form();

		break;
}

/* --------------------------
    User Administration
   -------------------------- */

function change_password_form() {
	global $colors;

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
	html_start_box("<strong>Change Password</strong>", "98%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($form_fields, (isset($user) ? $user : array()))
		));

	html_end_box();


	form_save_button("index.php","save");
	include_once("include/bottom_footer.php");
}

?>
