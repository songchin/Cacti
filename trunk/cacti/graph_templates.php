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
include_once("./lib/graph/graph_template_update.php");
include_once("./lib/graph/graph_form.php");
include_once("./lib/graph/graph_template.php"); // remove
include_once("./lib/graph/graph_form.php");
include_once("./lib/sys/sequence.php");
include_once("./include/graph/graph_constants.php");
include_once("./include/graph/graph_arrays.php");
include_once("./include/graph/graph_form.php");
include_once("./lib/utility.php");
include_once("./lib/template.php");
include_once("./lib/tree.php");
include_once("./lib/html_tree.php");

$graph_actions = array(
	1 => _("Delete"),
	2 => _("Duplicate")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

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
		include_once("./include/top_header.php");

		template();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $graph_actions, $fields_graph;

	/* the 'go' button was click in the graph items box */
	if (isset($_POST["action_button_y"])) {
		if (isset($_POST["selected_items"])) {
			$selected_items = unserialize(stripslashes($_POST["selected_items"]));

			if ($_POST["drp_action"] == "1") { /* delete */
				for ($i=0;($i<count($selected_items));$i++) {
					api_graph_template_item_remove($selected_items[$i]);
				}
			}elseif ($_POST["drp_action"] == "2") { /* duplicate */
				for ($i=0;($i<count($selected_items));$i++) {
					api_graph_template_item_duplicate($selected_items[$i], $_POST["new_dti"]);
				}
			}

			header("Location: graph_templates.php?action=edit&id=" . $_POST["graph_template_id"]);
			exit;
		}

		/* loop through each of the graphs selected on the previous page and get more info about them */
		while (list($var,$val) = each($_POST)) {
			if (ereg("^chk_gi_([0-9]+)$", $var, $matches)) {
				$graph_array[] = $matches[1];
			}
		}

		include_once("./include/top_header.php");

		html_start_box("<strong>" . $graph_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

		print "<form action='graph_templates.php' method='post'>\n";

		if ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . _("Are you sure you want to delete these graph items?") . "</p>
					</td>
				</tr>\n
				";
		}else if ($_POST["drp_action"] == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>" . _("Please choose a new data template item to assign to these graph items.") . "</p>
						<p><strong>" . _("Data Template Item:") . "</strong><br>"; form_dropdown("new_dti", db_fetch_assoc("select CONCAT_WS('',data_template.template_name,' - ',' (',data_template_item.data_source_name,')') as name,data_template_item.id from data_template,data_template_item where data_template.id=data_template_item.data_template_id order by data_template.template_name,data_template_item.data_source_name"), "name", "id", "", "", ""); print "</p>
					</td>
				</tr>\n
				";
		}

		print "	<tr>
				<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
					<input type='hidden' name='action' value='save'>
					<input type='hidden' name='graph_template_id' value='" . $_POST["graph_template_id"] . "'>
					<input type='hidden' name='action_button_y' value='X'>
					<input type='hidden' name='selected_items' value='" . (isset($graph_array) ? serialize($graph_array) : '') . "'>
					<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
					<a href='graph_templates.php?action=edit&id=" . $_POST["graph_template_id"] . "'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
					<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>
				</td>
			</tr>
			";

		html_end_box();

		include_once("./include/bottom_footer.php");
	}

	if (isset($_POST["save_component_template"])) {
		$suggested_value_fields = array();

		/* cache all post field values */
		init_post_field_cache();

		reset($_POST);

		/* step #1: field parsing */
		while(list($name, $value) = each($_POST)) {
			if (substr($name, 0, 2) == "g|") {
				$matches = explode("|", $name);
				$form_graph_fields{$matches[1]} = $value;
			}else if (substr($name, 0, 3) == "sv|") {
				$matches = explode("|", $name);
				$suggested_value_fields{$matches[1]}{$matches[2]} = $value;
			}
		}

		/* make a list of fields to save */
		while (list($_field_name, $_field_value) = each($form_graph_fields)) {
			/* make sure that we know about this field */
			if (isset($fields_graph[$_field_name])) {
				/* checkbox values must be converted into [0|1] format first */
				if ((isset($fields_graph[$_field_name]["validate_regexp"])) && ($fields_graph[$_field_name]["validate_regexp"] == FORM_VALIDATE_CHECKBOX)) {
					$save_graph[$_field_name] = html_boolean($_field_value);
				/* non-checkbox values should be handled normally */
				}else{
					$save_graph[$_field_name] = $_field_value;
				}
			/* also handle the "template this field" checkboxes */
			}else if (substr($_field_name, 0, 2) == "t_") {
				$save_graph[$_field_name] = html_boolean($_field_value);
			}
		}

		$save_graph["id"] = $_POST["graph_template_id"];

		/* step #2: field validation */
		validate_graph_fields($save_graph, $suggested_value_fields, "g||field|", "sv||field|||id|");

		/* step #3: field save */
		if (!is_error_message()) {
			$graph_template_id = api_graph_template_save($save_graph, $suggested_value_fields);
		}
	}

	if ((is_error_message()) || (empty($graph_template_id)) || (empty($_POST["graph_template_id"]))) {
		if (isset($_POST["redirect_sv_add"])) {
			$action = "sv_add";
		}else{
			$action = "edit";
		}

		header("Location: graph_templates.php?action=$action" . (empty($graph_template_id) ? "" : "&id=$graph_template_id"));
	}else{
		header("Location: graph_templates.php");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $graph_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			for ($i=0;($i<count($selected_items));$i++) {
				api_graph_template_remove($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_graph(0, $selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: graph_templates.php");
		exit;
	}

	/* setup some variables */
	$graph_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$graph_list .= "<li>" . db_fetch_cell("select template_name from graph_template where id=" . $matches[1]) . "<br>";
			$graph_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $graph_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='graph_templates.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Are you sure you want to delete the following graph templates? Any graphs attached
					to these templates will become individual graphs.") . "</p>
					<p>$graph_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following graph templates will be duplicated. You can
					optionally change the title format for the new graph templates.") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($graph_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one graph template.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($graph_array) ? serialize($graph_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='graph_templates.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
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
	global $colors, $struct_graph, $fields_graph_template_template_edit;

	if (!empty($_GET["id"])) {
		$graph_template = db_fetch_row("select * from graph_template where id=" . $_GET["id"]);

		$header_label = _("[edit: ") . $graph_template["template_name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	form_start("graph_templates.php", "form_graph_template");

	/* ==================== Box: Graph Template ==================== */

	html_start_box("<strong>" . _("Graph Template") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");
	_graph_template_field__template_name("g|template_name", (isset($graph_template) ? $graph_template["template_name"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	html_end_box();

	/* graph item list goes here */
	if (!empty($_GET["id"])) {
		/* ==================== Box: Graph Items ==================== */

		html_start_box("<strong>" . _("Graph Items") . "</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_items.php?action=edit&graph_template_id=" . $_GET["id"]);
		draw_graph_item_editor($_GET["id"], "graph_template", false);
		html_end_box();

		/* ==================== Box: Graph Item Inputs ==================== */

		html_start_box("<strong>" . _("Graph Item Inputs") . "</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_inputs.php?action=edit&graph_template_id=" . $_GET["id"]);

		html_header(array(_("Name")), 2);

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
	_graph_field__title("g|title", true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_title", (isset($graph_template["t_title"]) ? $graph_template["t_title"] : ""));
	_graph_field__vertical_label("g|vertical_label", true, (isset($graph_template["vertical_label"]) ? $graph_template["vertical_label"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_vertical_label", (isset($graph_template["t_vertical_label"]) ? $graph_template["t_vertical_label"] : ""));
	_graph_field__image_format("g|image_format", (isset($graph_template["image_format"]) ? $graph_template["image_format"] : ""), true, (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_image_format", (isset($graph_template["t_image_format"]) ? $graph_template["t_image_format"] : ""));
	_graph_field__export("g|export", true, (isset($graph_template["export"]) ? $graph_template["export"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_export", (isset($graph_template["t_export"]) ? $graph_template["t_export"] : ""));
	_graph_field__force_rules_legend("g|force_rules_legend", true, (isset($graph_template["force_rules_legend"]) ? $graph_template["force_rules_legend"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_force_rules_legend", (isset($graph_template["t_force_rules_legend"]) ? $graph_template["t_force_rules_legend"] : ""));
	field_row_header("Image Size Options");
	_graph_field__height("g|height", true, (isset($graph_template["height"]) ? $graph_template["height"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_height", (isset($graph_template["t_height"]) ? $graph_template["t_height"] : ""));
	_graph_field__width("g|width", true, (isset($graph_template["width"]) ? $graph_template["width"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_width", (isset($graph_template["t_width"]) ? $graph_template["t_width"] : ""));
	field_row_header("Grid Options");
	_graph_field__x_grid("g|x_grid", true, (isset($graph_template["x_grid"]) ? $graph_template["x_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_x_grid", (isset($graph_template["t_x_grid"]) ? $graph_template["t_x_grid"] : ""));
	_graph_field__y_grid("g|y_grid", true, (isset($graph_template["y_grid"]) ? $graph_template["y_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_y_grid", (isset($graph_template["t_y_grid"]) ? $graph_template["t_y_grid"] : ""));
	_graph_field__y_grid_alt("g|y_grid_alt", true, (isset($graph_template["y_grid_alt"]) ? $graph_template["y_grid_alt"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_y_grid_alt", (isset($graph_template["t_y_grid_alt"]) ? $graph_template["t_y_grid_alt"] : ""));
	_graph_field__no_minor("g|no_minor", true, (isset($graph_template["no_minor"]) ? $graph_template["no_minor"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_no_minor", (isset($graph_template["t_no_minor"]) ? $graph_template["t_no_minor"] : ""));
	field_row_header("Auto Scaling Options");
	_graph_field__auto_scale("g|auto_scale", true, (isset($graph_template["auto_scale"]) ? $graph_template["auto_scale"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_auto_scale", (isset($graph_template["t_auto_scale"]) ? $graph_template["t_auto_scale"] : ""));
	_graph_field__auto_scale_opts("g|auto_scale_opts", true, (isset($graph_template["auto_scale_opts"]) ? $graph_template["auto_scale_opts"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_auto_scale_opts", (isset($graph_template["t_auto_scale_opts"]) ? $graph_template["t_auto_scale_opts"] : ""));
	_graph_field__auto_scale_log("g|auto_scale_log", true, (isset($graph_template["auto_scale_log"]) ? $graph_template["auto_scale_log"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_auto_scale_log", (isset($graph_template["t_auto_scale_log"]) ? $graph_template["t_auto_scale_log"] : ""));
	_graph_field__auto_scale_rigid("g|auto_scale_rigid", true, (isset($graph_template["auto_scale_rigid"]) ? $graph_template["auto_scale_rigid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_auto_scale_rigid", (isset($graph_template["t_auto_scale_rigid"]) ? $graph_template["t_auto_scale_rigid"] : ""));
	_graph_field__auto_padding("g|auto_padding", true, (isset($graph_template["auto_padding"]) ? $graph_template["auto_padding"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_auto_padding", (isset($graph_template["t_auto_padding"]) ? $graph_template["t_auto_padding"] : ""));
	field_row_header("Fixed Scaling Options");
	_graph_field__upper_limit("g|upper_limit", true, (isset($graph_template["upper_limit"]) ? $graph_template["upper_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_upper_limit", (isset($graph_template["t_upper_limit"]) ? $graph_template["t_upper_limit"] : ""));
	_graph_field__lower_limit("g|lower_limit", true, (isset($graph_template["lower_limit"]) ? $graph_template["lower_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_lower_limit", (isset($graph_template["t_lower_limit"]) ? $graph_template["t_lower_limit"] : ""));
	_graph_field__base_value("g|base_value", true, (isset($graph_template["base_value"]) ? $graph_template["base_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_base_value", (isset($graph_template["t_base_value"]) ? $graph_template["t_base_value"] : ""));
	field_row_header("Units Display Options");
	_graph_field__unit_value("g|unit_value", true, (isset($graph_template["unit_value"]) ? $graph_template["unit_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_unit_value", (isset($graph_template["t_unit_value"]) ? $graph_template["t_unit_value"] : ""));
	_graph_field__unit_length("g|unit_length", true, (isset($graph_template["unit_length"]) ? $graph_template["unit_length"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_unit_length", (isset($graph_template["t_unit_length"]) ? $graph_template["t_unit_length"] : ""));
	_graph_field__unit_exponent_value("g|unit_exponent_value", true, (isset($graph_template["unit_exponent_value"]) ? $graph_template["unit_exponent_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "g|t_unit_exponent_value", (isset($graph_template["t_unit_exponent_value"]) ? $graph_template["t_unit_exponent_value"] : ""));

	html_end_box();

	form_hidden_box("graph_template_id", (empty($_GET["id"]) ? 0 : $_GET["id"]), "");
	form_hidden_box("save_component_template", "1", "");

	form_save_button("graph_templates.php");
}

function template() {
	global $colors, $graph_actions;

	html_start_box("<strong>" . _("Graph Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates.php?action=edit");

	html_header_checkbox(array(_("Template Title")));

	$template_list = db_fetch_assoc("select
		graph_template.id,
		graph_template.template_name
		from graph_template
		order by template_name");

	$i = 0;
	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
				?>
				<td>
					<a class="linkEditMain" href="graph_templates.php?action=edit&id=<?php print $template["id"];?>"><?php print $template["template_name"];?></a>
				</td>
				<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_<?php print $template["id"];?>' title="<?php print $template["template_name"];?>">
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No Graph Templates") . "</em></td></tr>";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($graph_actions);

	print "</form>\n";
}

?>