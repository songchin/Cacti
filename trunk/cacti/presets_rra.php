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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

require_once(CACTI_BASE_PATH . "/lib/xajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction("testXajax", CACTI_BASE_PATH . "/lib/sys/html_xajax.php");
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

	_data_preset_rra_item_js();

	$box_id = "1";
	html_start_box("<strong>" . _("RRA Items") . "</strong>", "javascript:new_rra_item('$box_id')", "", $box_id, true, 0);

	if (is_array($rra_items) && sizeof($rra_items) > 0) {
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
								<a class="linkOverDark" href="#">Remove</a>
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
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="1">
				No RRA presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box();

	?>
	<a href="#" onClick="javascript:xajax_testXajax()">do ajax</a>
	<div id="testDiv">sdsd</div>
	<?php



	form_hidden_box("rra_preset_id", $_rra_preset_id);

	//form_save_button("presets_rra.php", "save_rra");
}

?>
