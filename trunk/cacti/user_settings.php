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
		form_save();

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

function form_save() {
	global $settings_users;

	while (list($tab_short_name, $tab_fields) = each($settings_users)) {
		while (list($field_name, $field_array) = each($tab_fields)) {
			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					db_execute("replace into settings_users (user_id,name,value) values (" . $_SESSION["sess_user_id"] . ",'$sub_field_name', '" . (isset($_POST[$sub_field_name]) ? $_POST[$sub_field_name] : "") . "')");
				}
			}else{
				db_execute("replace into settings_users (user_id,name,value) values (" . $_SESSION["sess_user_id"] . ",'$field_name', '" . (isset($_POST[$field_name]) ? $_POST[$field_name] : "") . "')");
			}
		}
	}

	/* reset local settings cache so the user sees the new settings */
	kill_session_var("sess_user_config_array");

	header("Location: user_settings.php");
}

/* --------------------------
    Graph Settings Functions
   -------------------------- */

function settings() {
	global $colors, $tabs_graphs, $settings_users, $graph_views, $current_user, $graph_tree_views;

	/* you cannot have per-user settings if cacti's user management is not turned on */
	if (read_config_option("auth_method") == "0") {
		raise_message(6);
		display_output_messages();
		return;
	}


	print "<form method='post'>\n";

	html_graph_start_box(1, true);

	print "<tr bgcolor='#" . $colors["header_background"] . "'><td colspan='3'><table cellspacing='0' cellpadding='3' width='100%'><tr><td class='textHeaderDark'><strong>User Settings</strong></td></tr></table></td></tr>";

	while (list($tab_short_name, $tab_fields) = each($settings_users)) {
		?>
		<tr bgcolor='<?php print $colors["header_panel_background"];?>'>
			<td colspan='2' class='textSubHeaderDark' style='padding: 3px;'>
				<?php print $tabs_graphs[$tab_short_name];?>
			</td>
		</tr>
		<?php

		$form_array = array();

		while (list($field_name, $field_array) = each($tab_fields)) {
			$form_array += array($field_name => $tab_fields[$field_name]);

			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					if (user_config_value_exists($sub_field_name, $_SESSION["sess_user_id"])) {
						$form_array[$field_name]["items"][$sub_field_name]["form_id"] = 1;
					}

					$form_array[$field_name]["items"][$sub_field_name]["value"] =  db_fetch_cell("select value from settings_users where name='$sub_field_name' and user_id=" . $_SESSION["sess_user_id"]);
				}
			}else{
				if (user_config_value_exists($field_name, $_SESSION["sess_user_id"])) {
					$form_array[$field_name]["form_id"] = 1;
				}

				$form_array[$field_name]["value"] = db_fetch_cell("select value from settings_users where name='$field_name' and user_id=" . $_SESSION["sess_user_id"]);
			}
		}

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
					),
				"fields" => $form_array
				)
			);
	}

	html_graph_end_box();

	print "<br>";

	form_hidden_box("save_component_user_config","1","");
	form_save_button("user_settings.php", "save");
}

?>
