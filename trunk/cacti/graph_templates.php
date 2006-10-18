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
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_update.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_push.php"); // remove
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_form.php");
require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");
require_once(CACTI_BASE_PATH . "/lib/template.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'sv_remove':
		sv_remove();

		header("Location: graph_templates.php?action=edit" . (empty($_GET["graph_template_id"]) ? "" : "&id=" . $_GET["graph_template_id"]));
		break;
	case 'sv_movedown':
		sv_movedown();

		header("Location: graph_templates.php?action=edit" . (empty($_GET["graph_template_id"]) ? "" : "&id=" . $_GET["graph_template_id"]));
		break;
	case 'sv_moveup':
		sv_moveup();

		header("Location: graph_templates.php?action=edit" . (empty($_GET["graph_template_id"]) ? "" : "&id=" . $_GET["graph_template_id"]));
		break;
	case 'sv_add':
		include_once ("./include/top_header.php");

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	case 'edit':
		include_once ("./include/top_header.php");

		template_edit();

		include_once ("./include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		template();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "graph_template_edit") {
		$suggested_value_fields = array();

		/* cache all post field values */
		init_post_field_cache();

		reset($_POST);

		/* step #1: field parsing */
		while(list($name, $value) = each($_POST)) {
			if (substr($name, 0, 3) == "sv|") {
				$matches = explode("|", $name);

				$suggested_value_fields{$matches[1]}[] = array("id" => $matches[2], "value" => $value);
			}
		}

		/* step #2: field validation */
		$form_graph_template["template_name"] = $_POST["template_name"];
		$form_graph["t_title"] = html_boolean(isset($_POST["t_title"]) ? $_POST["t_title"] : "");
		$form_graph["vertical_label"] = $_POST["vertical_label"];
		$form_graph["t_vertical_label"] = html_boolean(isset($_POST["t_vertical_label"]) ? $_POST["t_vertical_label"] : "");
		$form_graph["image_format"] = $_POST["image_format"];
		$form_graph["t_image_format"] = html_boolean(isset($_POST["t_image_format"]) ? $_POST["t_image_format"] : "");
		$form_graph["export"] = html_boolean(isset($_POST["export"]) ? $_POST["export"] : "");
		$form_graph["t_export"] = html_boolean(isset($_POST["t_export"]) ? $_POST["t_export"] : "");
		$form_graph["force_rules_legend"] = html_boolean(isset($_POST["force_rules_legend"]) ? $_POST["force_rules_legend"] : "");
		$form_graph["t_force_rules_legend"] = html_boolean(isset($_POST["t_force_rules_legend"]) ? $_POST["t_force_rules_legend"] : "");
		$form_graph["height"] = $_POST["height"];
		$form_graph["t_height"] = html_boolean(isset($_POST["t_height"]) ? $_POST["t_height"] : "");
		$form_graph["width"] = $_POST["width"];
		$form_graph["t_width"] = html_boolean(isset($_POST["t_width"]) ? $_POST["t_width"] : "");
		$form_graph["x_grid"] = $_POST["x_grid"];
		$form_graph["t_x_grid"] = html_boolean(isset($_POST["t_x_grid"]) ? $_POST["t_x_grid"] : "");
		$form_graph["y_grid"] = $_POST["y_grid"];
		$form_graph["t_y_grid"] = html_boolean(isset($_POST["t_y_grid"]) ? $_POST["t_y_grid"] : "");
		$form_graph["y_grid_alt"] = html_boolean(isset($_POST["y_grid_alt"]) ? $_POST["y_grid_alt"] : "");
		$form_graph["t_y_grid_alt"] = html_boolean(isset($_POST["t_y_grid_alt"]) ? $_POST["t_y_grid_alt"] : "");
		$form_graph["no_minor"] = html_boolean(isset($_POST["no_minor"]) ? $_POST["no_minor"] : "");
		$form_graph["t_no_minor"] = html_boolean(isset($_POST["t_no_minor"]) ? $_POST["t_no_minor"] : "");
		$form_graph["auto_scale"] = html_boolean(isset($_POST["auto_scale"]) ? $_POST["auto_scale"] : "");
		$form_graph["t_auto_scale"] = html_boolean(isset($_POST["t_auto_scale"]) ? $_POST["t_auto_scale"] : "");
		$form_graph["auto_scale_opts"] = $_POST["auto_scale_opts"];
		$form_graph["t_auto_scale_opts"] = html_boolean(isset($_POST["t_auto_scale_opts"]) ? $_POST["t_auto_scale_opts"] : "");
		$form_graph["auto_scale_log"] = html_boolean(isset($_POST["auto_scale_log"]) ? $_POST["auto_scale_log"] : "");
		$form_graph["t_auto_scale_log"] = html_boolean(isset($_POST["t_auto_scale_log"]) ? $_POST["t_auto_scale_log"] : "");
		$form_graph["auto_scale_rigid"] = html_boolean(isset($_POST["auto_scale_rigid"]) ? $_POST["auto_scale_rigid"] : "");
		$form_graph["t_auto_scale_rigid"] = html_boolean(isset($_POST["t_auto_scale_rigid"]) ? $_POST["t_auto_scale_rigid"] : "");
		$form_graph["auto_padding"] = html_boolean(isset($_POST["auto_padding"]) ? $_POST["auto_padding"] : "");
		$form_graph["t_auto_padding"] = html_boolean(isset($_POST["t_auto_padding"]) ? $_POST["t_auto_padding"] : "");
		$form_graph["upper_limit"] = $_POST["upper_limit"];
		$form_graph["t_upper_limit"] = html_boolean(isset($_POST["t_upper_limit"]) ? $_POST["t_upper_limit"] : "");
		$form_graph["lower_limit"] = $_POST["lower_limit"];
		$form_graph["t_lower_limit"] = html_boolean(isset($_POST["t_lower_limit"]) ? $_POST["t_lower_limit"] : "");
		$form_graph["base_value"] = $_POST["base_value"];
		$form_graph["t_base_value"] = html_boolean(isset($_POST["t_base_value"]) ? $_POST["t_base_value"] : "");
		$form_graph["unit_value"] = $_POST["unit_value"];
		$form_graph["t_unit_value"] = html_boolean(isset($_POST["t_unit_value"]) ? $_POST["t_unit_value"] : "");
		$form_graph["unit_length"] = $_POST["unit_length"];
		$form_graph["t_unit_length"] = html_boolean(isset($_POST["t_unit_length"]) ? $_POST["t_unit_length"] : "");
		$form_graph["unit_exponent_value"] = $_POST["unit_exponent_value"];
		$form_graph["t_unit_exponent_value"] = html_boolean(isset($_POST["t_unit_exponent_value"]) ? $_POST["t_unit_exponent_value"] : "");

		field_register_error(api_graph_template_fields_validate($form_graph_template, "|field|"));
		field_register_error(api_graph_fields_validate($form_graph, $suggested_value_fields, "|field|", "sv||field|||id|"));

		/* step #3: field save */
		if (!is_error_message()) {
			$graph_template_id = api_graph_template_save($_POST["graph_template_id"], $form_graph_template + $form_graph);

			if ($graph_template_id) {
				api_graph_template_suggested_values_save($graph_template_id, $suggested_value_fields);
			}
		}

		if ((is_error_message()) || (empty($graph_template_id)) || (empty($_POST["graph_template_id"]))) {
			if (isset($_POST["redirect_sv_add"])) {
				$action = "sv_add";
			}else{
				$action = "edit";
			}

			header("Location: graph_templates.php?action=$action" . (empty($graph_template_id) ? "" : "&id=$graph_template_id"));
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $graph_template_id) {
				api_graph_template_remove($graph_template_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "duplicate") {
			// yet yet coded
		}

		header("Location: graph_templates.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "graph_template_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: graph_templates.php$get_string");
	}
}

/* ----------------------------
    template - Graph Templates
   ---------------------------- */

function sv_movedown() {
	seq_move_item("graph_template_suggested_value", $_GET["id"], "graph_template_id = " . $_GET["graph_template_id"] . " and field_name = 'title'", "down");
}

function sv_moveup() {
	seq_move_item("graph_template_suggested_value", $_GET["id"], "graph_template_id = " . $_GET["graph_template_id"] . " and field_name = 'title'", "up");
}

function sv_remove() {
	db_execute("delete from graph_template_suggested_value where id=" . $_GET["id"]);
}

function template_edit() {
	global $colors;

	if (!empty($_GET["id"])) {
		$graph_template = db_fetch_row("select * from graph_template where id=" . $_GET["id"]);

		$header_label = _("[edit: ") . $graph_template["template_name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	form_start("graph_templates.php", "form_graph_template");

	/* ==================== Box: Graph Template ==================== */

	html_start_box("<strong>" . _("Graph Template") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");
	_graph_template_field__template_name("template_name", (isset($graph_template) ? $graph_template["template_name"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	html_end_box();

	/* graph item list goes here */
	if (!empty($_GET["id"])) {
		/* ==================== Box: Graph Items ==================== */

		html_start_box("<strong>" . _("Graph Items") . "</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_items.php?action=edit&graph_template_id=" . $_GET["id"]);
		draw_graph_item_editor($_GET["id"], "graph_template", false);
		html_end_box();

		/* ==================== Box: Graph Item Inputs ==================== */

		html_start_box("<strong>" . _("Graph Item Inputs") . "</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_inputs.php?action=edit&graph_template_id=" . $_GET["id"]);

		$display_text = array(
			"name" => array(_("Name"), "ASC"));

		html_header_sort($display_text, $sort_column, $sort_direction, 2);

		$template_item_list = db_fetch_assoc("select id,name from graph_template_item_input where graph_template_id=" . $_GET["id"] . " order by name");

		$i = 0;
		if (sizeof($template_item_list) > 0) {
			foreach ($template_item_list as $item) {
				form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], $i); $i++;
				?>
					<td>
						<a class="linkEditMain" href="graph_templates_inputs.php?action=edit&id=<?php print $item["id"];?>&graph_template_id=<?php print $_GET["id"];?>"><?php print $item["name"];?></a>
					</td>
					<td align="right">
						<a href="graph_templates_inputs.php?action=remove&id=<?php print $item["id"];?>&graph_template_id=<?php print $_GET["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
					</td>
				</tr>
				<?php
			}
		}else{
			print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td colspan='2'><em>" . _("No Inputs") . "</em></td></tr>";
		}

		html_end_box();
	}

	/* ==================== Box: Graph ==================== */

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "sv_add") {
		form_hidden_box("redirect_sv_add", "x", "");
	}

	html_start_box("<strong>" . _("Graph") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

	field_row_header("General Options");
	_graph_field__title("title", true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_title", (isset($graph_template["t_title"]) ? $graph_template["t_title"] : ""));
	_graph_field__vertical_label("vertical_label", true, (isset($graph_template["vertical_label"]) ? $graph_template["vertical_label"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_vertical_label", (isset($graph_template["t_vertical_label"]) ? $graph_template["t_vertical_label"] : ""));
	_graph_field__image_format("image_format", (isset($graph_template["image_format"]) ? $graph_template["image_format"] : ""), true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_image_format", (isset($graph_template["t_image_format"]) ? $graph_template["t_image_format"] : ""));
	_graph_field__export("export", true, (isset($graph_template["export"]) ? $graph_template["export"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_export", (isset($graph_template["t_export"]) ? $graph_template["t_export"] : ""));
	_graph_field__force_rules_legend("force_rules_legend", true, (isset($graph_template["force_rules_legend"]) ? $graph_template["force_rules_legend"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_force_rules_legend", (isset($graph_template["t_force_rules_legend"]) ? $graph_template["t_force_rules_legend"] : ""));
	field_row_header("Image Size Options");
	_graph_field__height("height", true, (isset($graph_template["height"]) ? $graph_template["height"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_height", (isset($graph_template["t_height"]) ? $graph_template["t_height"] : ""));
	_graph_field__width("width", true, (isset($graph_template["width"]) ? $graph_template["width"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_width", (isset($graph_template["t_width"]) ? $graph_template["t_width"] : ""));
	field_row_header("Grid Options");
	_graph_field__x_grid("x_grid", true, (isset($graph_template["x_grid"]) ? $graph_template["x_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_x_grid", (isset($graph_template["t_x_grid"]) ? $graph_template["t_x_grid"] : ""));
	_graph_field__y_grid("y_grid", true, (isset($graph_template["y_grid"]) ? $graph_template["y_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_y_grid", (isset($graph_template["t_y_grid"]) ? $graph_template["t_y_grid"] : ""));
	_graph_field__y_grid_alt("y_grid_alt", true, (isset($graph_template["y_grid_alt"]) ? $graph_template["y_grid_alt"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_y_grid_alt", (isset($graph_template["t_y_grid_alt"]) ? $graph_template["t_y_grid_alt"] : ""));
	_graph_field__no_minor("no_minor", true, (isset($graph_template["no_minor"]) ? $graph_template["no_minor"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_no_minor", (isset($graph_template["t_no_minor"]) ? $graph_template["t_no_minor"] : ""));
	field_row_header("Auto Scaling Options");
	_graph_field__auto_scale("auto_scale", true, (isset($graph_template["auto_scale"]) ? $graph_template["auto_scale"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_scale", (isset($graph_template["t_auto_scale"]) ? $graph_template["t_auto_scale"] : ""));
	_graph_field__auto_scale_opts("auto_scale_opts", true, (isset($graph_template["auto_scale_opts"]) ? $graph_template["auto_scale_opts"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_scale_opts", (isset($graph_template["t_auto_scale_opts"]) ? $graph_template["t_auto_scale_opts"] : ""));
	_graph_field__auto_scale_log("auto_scale_log", true, (isset($graph_template["auto_scale_log"]) ? $graph_template["auto_scale_log"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_scale_log", (isset($graph_template["t_auto_scale_log"]) ? $graph_template["t_auto_scale_log"] : ""));
	_graph_field__auto_scale_rigid("auto_scale_rigid", true, (isset($graph_template["auto_scale_rigid"]) ? $graph_template["auto_scale_rigid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_scale_rigid", (isset($graph_template["t_auto_scale_rigid"]) ? $graph_template["t_auto_scale_rigid"] : ""));
	_graph_field__auto_padding("auto_padding", true, (isset($graph_template["auto_padding"]) ? $graph_template["auto_padding"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_padding", (isset($graph_template["t_auto_padding"]) ? $graph_template["t_auto_padding"] : ""));
	field_row_header("Fixed Scaling Options");
	_graph_field__upper_limit("upper_limit", true, (isset($graph_template["upper_limit"]) ? $graph_template["upper_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_upper_limit", (isset($graph_template["t_upper_limit"]) ? $graph_template["t_upper_limit"] : ""));
	_graph_field__lower_limit("lower_limit", true, (isset($graph_template["lower_limit"]) ? $graph_template["lower_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_lower_limit", (isset($graph_template["t_lower_limit"]) ? $graph_template["t_lower_limit"] : ""));
	_graph_field__base_value("base_value", true, (isset($graph_template["base_value"]) ? $graph_template["base_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_base_value", (isset($graph_template["t_base_value"]) ? $graph_template["t_base_value"] : ""));
	field_row_header("Units Display Options");
	_graph_field__unit_value("unit_value", true, (isset($graph_template["unit_value"]) ? $graph_template["unit_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_unit_value", (isset($graph_template["t_unit_value"]) ? $graph_template["t_unit_value"] : ""));
	_graph_field__unit_length("unit_length", true, (isset($graph_template["unit_length"]) ? $graph_template["unit_length"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_unit_length", (isset($graph_template["t_unit_length"]) ? $graph_template["t_unit_length"] : ""));
	_graph_field__unit_exponent_value("unit_exponent_value", true, (isset($graph_template["unit_exponent_value"]) ? $graph_template["unit_exponent_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_unit_exponent_value", (isset($graph_template["t_unit_exponent_value"]) ? $graph_template["t_unit_exponent_value"] : ""));

	html_end_box();

	form_hidden_box("graph_template_id", (empty($_GET["id"]) ? 0 : $_GET["id"]), "");
	form_hidden_box("action_post", "graph_template_edit");

	form_save_button("graph_templates.php");
}

function template() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$filter_array = array();

	/* search field: filter (searches template name) */
	if (isset_get_var("search_filter")) {
		$filter_array["template_name"] = get_get_var("search_filter");
	}

	/* clean up sort_column string */
	if (isset_get_var("sort_column")) {
		$filter_array["sort_column"] = get_get_var("sort_column");
	}else{
		$filter_array["sort_column"] = "template_name";
	}

	/* clean up sort_direction string */
	if (isset_get_var("sort_direction")) {
		$filter_array["sort_direction"] = get_get_var("sort_direction");
	}else{
		$filter_array["sort_direction"] = "ASC";
	}

	/* get a list of all devices on this page */
	$graph_templates = api_graph_template_list($filter_array);

	form_start("graph_templates.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Graph Templates") . "</strong>", "graph_templates.php?action=edit");

	$display_text = array(
		"template_name" => array(_("Template Name"), "ASC"));

	html_header_sort_checkbox($display_text, $filter_array["sort_column"], $filter_array["sort_direction"], $box_id);

	$i = 0;
	if (sizeof($graph_templates) > 0) {
		foreach ($graph_templates as $graph_template) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $graph_template["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $graph_template["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $graph_template["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $graph_template["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $graph_template["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $graph_template["id"];?>')" href="graph_templates.php?action=edit&id=<?php echo $graph_template["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $graph_template["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $graph_template["template_name"]);?></span></a>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $graph_template["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $graph_template["id"];?>' title="<?php echo $graph_template["template_name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr class="empty">
			<td colspan="6">
				No graph templates found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "1", HTML_BOX_SEARCH_NO_ICON);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "graph_template_list");
	form_end();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these graph templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Graph Template');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these graph templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Graph Templates');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>