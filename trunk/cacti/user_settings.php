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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		save();

		break;
	default:
		include_once("./include/top_header.php");

		settings();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function save() {
	
	$save = array();

	$save["id"] = $_SESSION["sess_user_id"];
	$save["current_theme"] = form_input_validate($_POST["current_theme"], "current_theme", "", true, 3);

	if (!is_error_message()) {
		$user_id = api_user_save($save);

		if ($user_id) {
			/* user saved */
			raise_message(1);
			/* reset local settings cache so the user sees the new settings */
			kill_session_var("sess_current_theme");
		}else{
			/* error saving */
			raise_message(2);
		}
	}

	header("Location: user_settings.php");

}

/* --------------------------
    User Settings Functions
   -------------------------- */

function settings() {
	global $colors, $user_themes;

	/* you cannot have per-user settings if cacti's user management is not turned on */
	if (read_config_option("auth_method") == "0") {
		raise_message(6);
		display_output_messages();
		return;
	}

	/* get user settings */
	$user = api_user_info( array( "id" => $_SESSION["sess_user_id"] ) );

	print "<form method='post'>\n";

	html_start_box("<strong>User Settings</strong>", "98%", $colors["header_background"], "3", "center", "");

	?>
	<tr bgcolor='<?php print $colors["header_panel_background"];?>'>
		<td colspan='2' class='textSubHeaderDark' style='padding: 3px;'>General</td>
	</tr>
		<?php

	$form_array = array(
		"current_theme" => array(
			"friendly_name" => "Visual Theme",
			"description" => "The Cacti theme to use. Changes the look of Cacti.",
			"method" => "drop_array",
			"array" => $user_themes,
			"value" => api_user_theme($_SESSION["sess_user_id"]),
			"default" => "default"
			)
		);

	draw_edit_form(
		array(
			"config" => array(
				"no_form_tag" => true
				),
			"fields" => $form_array
			)
		);

	html_end_box();

	print "<br>";

	form_hidden_box("save_component_user","1","");
	form_save_button((isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"), "save");

}

?>
