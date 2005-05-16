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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		color_remove();

		header ("Location: presets.php?action=view_color");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		color_edit();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_color"])) {
		$save["id"] = $_POST["id"];
		$save["hex"] = form_input_validate($_POST["hex"], "hex", "^[a-fA-F0-9]+$", false, 3);

		if (!is_error_message()) {
			$color_id = sql_save($save, "colors");

			if ($color_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: presets_color.php?action=edit&id=" . (empty($color_id) ? $_POST["id"] : $color_id));
		}else{
			header("Location: presets_color.php");
		}
	}
}

/* -----------------------
    Color Functions
   ----------------------- */

function color_remove() {
	db_execute("delete from preset_color where id=" . $_GET["id"]);
}

function color_edit() {
	global $colors, $fields_color_edit;

	if (!empty($_GET["id"])) {
		$color = db_fetch_row("select * from preset_color where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $color["hex"] . "]";
	}else{
		$header_label = _("[new]");
	}

	html_start_box("<strong>" . _("Colors") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_color_edit, (isset($color) ? $color : array()))
		));

	html_end_box();

	form_save_button("presets.php?action=view_color");
}

?>