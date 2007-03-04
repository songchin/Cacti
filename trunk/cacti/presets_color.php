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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_color_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_color_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_color_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		color_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "color_preset_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_color["hex"] = $_POST["hex"];

		/* validate base color preset fields */
		field_register_error(api_data_preset_color_field_validate($form_color, "|field|"));

		if (!is_error_message()) {
			$preset_color_id = api_data_preset_color_save($_POST["preset_color_id"], $form_color);

			if (empty($preset_color_id)) {
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: presets_color.php?action=edit" . (empty($preset_color_id) ? "" : "&id=$preset_color_id"));
		}else{
			header("Location: presets.php?action=view_color");
		}
	}else if (isset($_POST["box-1-action-area-button"])) {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $preset_color_id) {
				api_data_preset_color_remove($preset_color_id);
			}
		}

		header("Location: presets.php?action=view_color");
	}
}

function color_edit() {
	$_color_preset_id = get_get_var_number("id");

	if (empty($_color_preset_id)) {
		$header_label = "[new]";
	}else{
		$color = api_data_preset_color_get($_color_preset_id);

		$header_label = "[edit: " . $color["hex"] . "]";
	}

	form_start("presets_color.php", "form_color");

	/* ==================== Box: Colors ==================== */

	html_start_box("<strong>" . _("Color Presets") . "</strong> $header_label");
	_data_preset_color__hex("hex", (isset($color["hex"]) ? $color["hex"] : ""), (isset($color["id"]) ? $color["id"] : "0"));
	html_end_box();

	form_hidden_box("preset_color_id", $_color_preset_id);
	form_hidden_box("action_post", "color_preset_edit");

	form_save_button("presets.php?action=view_color", "save_color");
}

?>
