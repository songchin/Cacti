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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		gprint_presets_remove();

		header("Location: presets_gprint.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		gprint_presets_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_gprint_presets"])) {
		$save["id"] = $_POST["id"];
		$save["hash"] = get_hash_gprint($_POST["id"]);
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["gprint_text"] = form_input_validate($_POST["gprint_text"], "gprint_text", "", false, 3);

		if (!is_error_message()) {
			$gprint_preset_id = sql_save($save, "graph_template_gprint");

			if ($gprint_preset_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: presets_gprint.php?action=edit&id=" . (empty($gprint_preset_id) ? $_POST["id"] : $gprint_preset_id));
			exit;
		}else{
			header("Location: presets_gprint.php");
			exit;
		}
	}
}

/* -----------------------------------
    gprint_presets - GPRINT Presets
   ----------------------------------- */

function gprint_presets_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the GPRINT preset") . " <strong>'" . db_fetch_cell("select name from preset_gprint where id=" . $_GET["id"]) . "'</strong>? This could affect every graph that uses this preset, make sure you know what you are doing first!", "presets.php?action=view_gprint", "presets_gprint.php?action=remove&id=" . $_GET["id"]);
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from preset_gprint where id=" . $_GET["id"]);
	}
}

function gprint_presets_edit() {
	global $colors, $fields_grprint_presets_edit;

	if (!empty($_GET["id"])) {
		$gprint_preset = db_fetch_row("select * from preset_gprint where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $gprint_preset["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	html_start_box("<strong>" . _("GPRINT Presets") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_grprint_presets_edit, (isset($gprint_preset) ? $gprint_preset : array()))
		));

	html_end_box();

	form_save_button("presets.php?action=view_gprint");
}

?>
