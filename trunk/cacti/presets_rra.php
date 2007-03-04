<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_utility.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

require_once(CACTI_BASE_PATH . "/lib/xajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("_data_preset_rra_item_xajax_save");
$xajax->registerFunction("_data_preset_rra_item_xajax_remove");
$xajax->processRequests();

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		rra_presets_remove();

		header("Location: presets_gprint.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		rra_presets_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "rra_preset_edit") {
		$rra_item_fields = array();

		/* cache all post field values */
		init_post_field_cache();

		/* parse form fields into manageable arrays */
		foreach ($_POST as $name => $value) {
			if (substr($name, 0, 5) == "rrai|") {
				$matches = explode("|", $name);
				$rra_item_fields{$matches[2]}{$matches[1]} = $value;
			}
		}

		$form_rra["name"] = $_POST["name"];

		/* validate base rra preset fields */
		field_register_error(api_data_preset_rra_field_validate($form_rra, "|field|"));

		foreach ($rra_item_fields as $rra_item_id => $fields) {
			/* obtain a list of visible rra item fields on the form */
			$visible_fields = api_data_preset_rra_item_visible_field_list($fields["consolidation_function"]);

			/* all non-visible fields on the form should be discarded */
			foreach ($visible_fields as $field_name) {
				$form_rra_item[$rra_item_id][$field_name] = $fields[$field_name];
			}

			/* validate rra item preset fields */
			field_register_error(api_data_preset_rra_item_field_validate($form_rra_item[$rra_item_id], "rrai||field||$rra_item_id"));
		}

		if (!is_error_message()) {
			$preset_rra_id = api_data_preset_rra_save($_POST["preset_rra_id"], $form_rra);

			if ($preset_rra_id) {
				/* save each rra item on the form */
				foreach (array_keys($rra_item_fields) as $rra_item_id) {
					$form_rra_item[$rra_item_id]["preset_rra_id"] = $_POST["preset_rra_id"];

					$preset_rra_item_id = api_data_preset_rra_item_save($rra_item_id, $form_rra_item[$rra_item_id]);

					if (!$preset_rra_item_id) {
						raise_message(2);
					}
				}
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["preset_rra_id"]))) {
			header("Location: presets_rra.php?action=edit" . (empty($preset_rra_id) ? "" : "&id=$preset_rra_id"));
		}else{
			header("Location: presets.php?action=view_rra");
		}
	}else if (isset($_POST["box-1-action-area-button"])) {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $preset_rra_id) {
				api_data_preset_rra_remove($preset_rra_id);
			}
		}

		header("Location: presets.php?action=view_rra");
	}
}

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

function rra_presets_edit() {
	global $xajax, $colors;

	$_rra_preset_id = get_get_var_number("id");

	if (empty($_rra_preset_id)) {
		$header_label = "[new]";
	}else{
		$rra = api_data_preset_rra_get($_rra_preset_id);

		$header_label = "[edit: " . $rra["name"] . "]";
	}

	form_start("presets_rra.php", "form_rra");

	/* ==================== Box: RRAs ==================== */

	html_start_box("<strong>" . _("RRA Presets") . "</strong> $header_label");
	_data_preset_rra__name("name", (isset($rra["name"]) ? $rra["name"] : ""), (isset($rra["id"]) ? $rra["id"] : "0"));
	html_end_box();

	/* ==================== Box: RRA Items ==================== */

	if (!empty($_rra_preset_id)) {
		$rra_items = api_data_preset_rra_item_list($_rra_preset_id);

		_data_preset_rra_item_js("form_rra");

		$box_id = "1";
		html_start_box("<strong>" . _("RRA Items") . "</strong>", "javascript:new_rra_item('$box_id')", "", $box_id, true, 0);

		$empty_rra_item_list = false;
		if (is_array($rra_items)) {
			/* if there are no rra items to display, we need to create a "fake" item which we will then turn
			 * into a "new" row using JS */
			if (sizeof($rra_items) == 0) {
				$empty_rra_item_list = true;

				$rra_items = array(
					array(
						"id" => "0",
						"consolidation_function" => "1",
						"steps" => "",
						"rows" => "",
						"x_files_factor" => "",
						"hw_alpha" => "",
						"hw_beta" => "",
						"hw_gamma" => "",
						"hw_seasonal_period" => "",
						"hw_rra_num" => "",
						"hw_threshold" => "",
						"hw_window_length" => ""
						)
					);
			}

			foreach ($rra_items as $rra_item) {
				?>
				<tr id="row<?php echo $rra_item["id"];?>">
					<td>
						<table width="100%" cellpadding="3" cellspacing="0">
							<tr bgcolor="<?php echo $colors["header_panel_background"];?>">
								<td colspan="2" class="textSubHeaderDark" id="row_rra_item_header_<?php echo $rra_item["id"];?>">
									<?php echo (empty($rra_item["id"]) ? "(new)" : api_data_preset_rra_item_friendly_name_get($rra_item["consolidation_function"], $rra_item["steps"], $rra_item["rows"]));?>
								</td>
								<td align="right" class="textSubHeaderDark">
									<a class="linkOverDark" href="#" onClick="javascript:xajax_xajax_remove_rra_item('<?php echo $rra_item["id"];?>')">Remove</a>
								</td>
							</tr>
							<?php
							_data_preset_rra_item__consolidation_function("rrai|consolidation_function|" . $rra_item["id"], $rra_item["consolidation_function"], $rra_item["id"]);
							_data_preset_rra_item__steps("rrai|steps|" . $rra_item["id"], $rra_item["steps"], $rra_item["id"]);
							_data_preset_rra_item__rows("rrai|rows|" . $rra_item["id"], $rra_item["rows"], $rra_item["id"]);
							_data_preset_rra_item__x_files_factor("rrai|x_files_factor|" . $rra_item["id"], $rra_item["x_files_factor"], $rra_item["id"]);
							_data_preset_rra_item__hw_alpha("rrai|hw_alpha|" . $rra_item["id"], $rra_item["hw_alpha"], $rra_item["id"]);
							_data_preset_rra_item__hw_beta("rrai|hw_beta|" . $rra_item["id"], $rra_item["hw_beta"], $rra_item["id"]);
							_data_preset_rra_item__hw_gamma("rrai|hw_gamma|" . $rra_item["id"], $rra_item["hw_gamma"], $rra_item["id"]);
							_data_preset_rra_item__hw_seasonal_period("rrai|hw_seasonal_period|" . $rra_item["id"], $rra_item["hw_seasonal_period"], $rra_item["id"]);
							_data_preset_rra_item__hw_rra_num("rrai|hw_rra_num|" . $rra_item["id"], $rra_item["hw_rra_num"], $rra_item["id"]);
							_data_preset_rra_item__hw_threshold("rrai|hw_threshold|" . $rra_item["id"], $rra_item["hw_threshold"], $rra_item["id"]);
							_data_preset_rra_item__hw_window_length("rrai|hw_window_length|" . $rra_item["id"], $rra_item["hw_window_length"], $rra_item["id"]);
							_data_preset_rra_item__consolidation_function_js_update($rra_item["consolidation_function"], $rra_item["id"]);
							?>
						</table>
					</td>
				</tr>
				<?php
			}
		}

		html_end_box();

		echo "<a name='rra_preset_bottom' />\n";

		if ($empty_rra_item_list == true) {
			?>
			<script language="JavaScript">
			<!--
			make_row_new(document.getElementById("row0"), true);
			-->
			</script>
			<?php
		}

		?>
		<?php
	}

	form_hidden_box("preset_rra_id", $_rra_preset_id);
	form_hidden_box("action_post", "rra_preset_edit");

	form_save_button("presets.php?action=view_rra", "save_rra");
}

?>
