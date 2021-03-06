<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
case 'save':
	while (list($field_name, $field_array) = each($settings{$_POST["tab"]})) {
		if (($field_array["method"] == "header") || ($field_array["method"] == "spacer" )){
				/* do nothing */
		}elseif ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
			while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
				db_execute("replace into settings (name,value) values ('$sub_field_name', '" . (isset($_POST[$sub_field_name]) ? $_POST[$sub_field_name] : "") . "')");
			}
		}else{
			db_execute("replace into settings (name,value) values ('$field_name', '" . (isset($_POST[$field_name]) ? $_POST[$field_name] : "") . "')");
		}
	}

	raise_message(1);

	/* reset local settings cache so the user sees the new settings */
	kill_session_var("sess_config_array");

	header("Location: settings.php?tab=" . $_POST["tab"]);
	break;
default:
	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	/* set the default settings category */
	if (!isset($_GET["tab"])) {
		/* there is no selected tab; select the first one */
		$current_tab = array_keys($tabs);
		$current_tab = $current_tab[0];
	}else{
		$current_tab = $_GET["tab"];
	}

	/* start the tab group for the settings page */
	html_tab_start();

	if (sizeof($tabs) > 0) {
		foreach (array_keys($tabs) as $tab_short_name) {
			html_tab_draw($tabs[$tab_short_name], "settings.php?tab=$tab_short_name", ($tab_short_name == $current_tab));
		}
	}

	html_tab_end();

	html_start_box("<strong>" . _("Cacti Settings") . " (" . $tabs[$current_tab] . ")</strong>");

	$form_array = array();

	while (list($field_name, $field_array) = each($settings[$current_tab])) {
		$form_array += array($field_name => $field_array);

		if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
			while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
				if (config_value_exists($sub_field_name)) {
					$form_array[$field_name]["items"][$sub_field_name]["form_id"] = 1;
				}

				$form_array[$field_name]["items"][$sub_field_name]["value"] = db_fetch_cell("select value from settings where name='$sub_field_name'");
			}
		}else{
			if (config_value_exists($field_name)) {
				$form_array[$field_name]["form_id"] = 1;
			}

			$form_array[$field_name]["value"] = db_fetch_cell("select value from settings where name='$field_name'");
		}
	}

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array)
			);

	html_end_box(true);

	form_hidden_box("tab", $current_tab, "");

	form_save_button("settings.php?tab=$current_tab", "save", "save");

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");

	break;
}
?>
