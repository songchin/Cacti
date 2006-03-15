<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

require_once(CACTI_BASE_PATH . "/lib/xajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("xajax_save_rra_item");
$xajax->registerFunction("xajax_remove_rra_item");
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

function xajax_save_rra_item($post_args) {
	$objResponse = new xajaxResponse();

	$form_rra_item["preset_rra_id"] = $post_args["rra_preset_id"];
	$form_rra_item["consolidation_function"] = $post_args["consolidation_function_0"];
	$form_rra_item["steps"] = $post_args["steps_0"];
	$form_rra_item["rows"] = $post_args["rows_0"];
	$form_rra_item["x_files_factor"] = $post_args["x_files_factor_0"];
	$form_rra_item["hw_alpha"] = $post_args["hw_alpha_0"];
	$form_rra_item["hw_beta"] = $post_args["hw_beta_0"];
	$form_rra_item["hw_gamma"] = $post_args["hw_gamma_0"];
	$form_rra_item["hw_seasonal_period"] = $post_args["hw_seasonal_period_0"];
	$form_rra_item["hw_rra_num"] = $post_args["hw_rra_num_0"];
	$form_rra_item["hw_threshold"] = $post_args["hw_threshold_0"];
	$form_rra_item["hw_window_length"] = $post_args["hw_window_length_0"];

	$field_errors = validate_data_preset_rra_item_fields($form_rra_item, "|field|_0");

	foreach (array_keys($form_rra_item) as $field_name) {
		if (isset($post_args{$field_name . "_0"})) {
			/* make a red border around the fields which have validation errors */
			if (in_array($field_name . "_0", $field_errors)) {
				$objResponse->addAssign($field_name . "_0", "style.border", "2px solid red");
			/* clear the border for all of the fields without validation errors */
			}else{
				$objResponse->addClear($field_name . "_0", "style.border");
			}
		}
	}

	$rra_preset_item_id = false;
	if (sizeof($field_errors) > 0) {
		$objResponse->addAlert("Form validation error!");
	}else{
		$rra_preset_item_id = api_data_preset_rra_item_save(0, $form_rra_item);

		if ($rra_preset_item_id === false) {
			$objResponse->addAlert("Save error!");
		}else{
			$objResponse->addScript("make_row_old(\"$rra_preset_item_id\");");
		}
	}

	return $objResponse->getXML();
}

function xajax_remove_rra_item($preset_rra_id) {
	$objResponse = new xajaxResponse();

	if (api_data_preset_rra_item_remove($preset_rra_id)) {
		$objResponse->addScript("remove_rra_item_row(\"1\", \"$preset_rra_id\");");
	}else{
		$objResponse->addAlert("Error removing RRA preset item!");
	}

	return $objResponse->getXML();
}

function form_save() {
	if (isset($_POST["save_component_gprint_presets"])) {
		$save["id"] = $_POST["id"];
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

	html_start_box("<strong>" . _("RRAs") . "</strong> $header_label");
	_data_preset_rra__name("name", (isset($rra["name"]) ? $rra["name"] : ""), (isset($rra["id"]) ? $rra["id"] : "0"));
	html_end_box();

	/* ==================== Box: RRA Items ==================== */

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

		$rra_cf_types = api_data_preset_rra_cf_type_list();

		foreach ($rra_items as $rra_item) {
			?>
			<tr id="row<?php echo $rra_item["id"];?>">
				<td>
					<table width="100%" cellpadding="3" cellspacing="0">
						<tr bgcolor="<?php echo $colors["header_panel_background"];?>">
							<td colspan="2" class="textSubHeaderDark">
								<?php echo $rra_cf_types{$rra_item["consolidation_function"]};?>
							</td>
							<td align="right" class="textSubHeaderDark">
								<a class="linkOverDark" href="#" onClick="javascript:xajax_xajax_remove_rra_item('<?php echo $rra_item["id"];?>')">Remove</a>
							</td>
						</tr>
						<?php
						_data_preset_rra_item__consolidation_function("consolidation_function_" . $rra_item["id"], $rra_item["consolidation_function"], $rra_item["id"]);
						_data_preset_rra_item__steps("steps_" . $rra_item["id"], $rra_item["steps"], $rra_item["id"]);
						_data_preset_rra_item__rows("rows_" . $rra_item["id"], $rra_item["rows"], $rra_item["id"]);
						_data_preset_rra_item__x_files_factor("x_files_factor_" . $rra_item["id"], $rra_item["x_files_factor"], $rra_item["id"]);
						_data_preset_rra_item__hw_alpha("hw_alpha_" . $rra_item["id"], $rra_item["hw_alpha"], $rra_item["id"]);
						_data_preset_rra_item__hw_beta("hw_beta_" . $rra_item["id"], $rra_item["hw_beta"], $rra_item["id"]);
						_data_preset_rra_item__hw_gamma("hw_gamma_" . $rra_item["id"], $rra_item["hw_gamma"], $rra_item["id"]);
						_data_preset_rra_item__hw_seasonal_period("hw_seasonal_period_" . $rra_item["id"], $rra_item["hw_seasonal_period"], $rra_item["id"]);
						_data_preset_rra_item__hw_rra_num("hw_rra_num_" . $rra_item["id"], $rra_item["hw_rra_num"], $rra_item["id"]);
						_data_preset_rra_item__hw_threshold("hw_threshold_" . $rra_item["id"], $rra_item["hw_threshold"], $rra_item["id"]);
						_data_preset_rra_item__hw_window_length("hw_window_length_" . $rra_item["id"], $rra_item["hw_window_length"], $rra_item["id"]);
						_data_preset_rra_item__consolidation_function_js_update($rra_item["consolidation_function"], $rra_item["id"]);
						?>
					</table>
				</td>
			</tr>
			<?php
		}
	}

	html_end_box();

	if ($empty_rra_item_list == true) {
		?>
		<script language="JavaScript">
		<!--
		make_row_new("1", document.getElementById("row0"));
		-->
		</script>
		<?php
	}

	?>
	<?php

	form_hidden_box("rra_preset_id", $_rra_preset_id);

	form_save_button("presets_rra.php", "save_rra");
}

?>
