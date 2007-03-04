<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_gprint_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_gprint_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_gprint_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		gprint_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "gprint_preset_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_gprint["name"] = $_POST["name"];
		$form_gprint["gprint_text"] = $_POST["gprint_text"];

		/* validate base gprint preset fields */
		field_register_error(api_data_preset_gprint_field_validate($form_gprint, "|field|"));

		if (!is_error_message()) {
			$preset_gprint_id = api_data_preset_gprint_save($_POST["preset_gprint_id"], $form_gprint);

			if (empty($preset_gprint_id)) {
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: presets_gprint.php?action=edit" . (empty($preset_gprint_id) ? "" : "&id=$preset_gprint_id"));
		}else{
			header("Location: presets.php?action=view_gprint");
		}
	}else if (isset($_POST["box-1-action-area-button"])) {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $preset_gprint_id) {
				api_data_preset_gprint_remove($preset_gprint_id);
			}
		}

		header("Location: presets.php?action=view_gprint");
	}
}

function gprint_edit() {
	$_gprint_preset_id = get_get_var_number("id");

	if (empty($_gprint_preset_id)) {
		$header_label = "[new]";
	}else{
		$gprint = api_data_preset_gprint_get($_gprint_preset_id);

		$header_label = "[edit: " . $gprint["name"] . "]";
	}

	form_start("presets_gprint.php", "form_gprint");

	/* ==================== Box: Colors ==================== */

	html_start_box("<strong>" . _("GPRINT Presets") . "</strong> $header_label");
	_data_preset_gprint__name("name", (isset($gprint["name"]) ? $gprint["name"] : ""), (isset($gprint["id"]) ? $gprint["id"] : "0"));
	_data_preset_gprint__gprint_text("gprint_text", (isset($gprint["gprint_text"]) ? $gprint["gprint_text"] : ""), (isset($gprint["id"]) ? $gprint["id"] : "0"));
	html_end_box();

	form_hidden_box("preset_gprint_id", $_gprint_preset_id);
	form_hidden_box("action_post", "gprint_preset_edit");

	form_save_button("presets.php?action=view_gprint", "save_gprint");
}

?>
