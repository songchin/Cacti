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
	1 => "Delete",
	2 => "Duplicate"
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
	global $graph_actions;

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

			header("Location: graph_templates.php?action=edit&id=" . $_POST["id"]);
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
						<p>Are you sure you want to delete these graph items?</p>
					</td>
				</tr>\n
				";
		}else if ($_POST["drp_action"] == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
						<p>Please choose a new data template item to assign to these graph items.</p>
						<p><strong>Data Template Item:</strong><br>"; form_dropdown("new_dti", db_fetch_assoc("select CONCAT_WS('',data_template.template_name,' - ',' (',data_template_item.data_source_name,')') as name,data_template_item.id from data_template,data_template_item where data_template.id=data_template_item.data_template_id order by data_template.template_name,data_template_item.data_source_name"), "name", "id", "", "", ""); print "</p>
					</td>
				</tr>\n
				";
		}

		print "	<tr>
				<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
					<input type='hidden' name='action' value='save'>
					<input type='hidden' name='id' value='" . $_POST["id"] . "'>
					<input type='hidden' name='action_button_y' value='X'>
					<input type='hidden' name='selected_items' value='" . (isset($graph_array) ? serialize($graph_array) : '') . "'>
					<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
					<a href='graph_templates.php?action=edit&id=" . $_POST["id"] . "'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
					<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>
				</td>
			</tr>
			";

		html_end_box();

		include_once("./include/bottom_footer.php");
	}

	if (isset($_POST["save_component_template"])) {
		$suggested_value_fields = array();

		reset($_POST);
		while(list($name, $value) = each($_POST)) {
			if (substr($name, 0, 3) == "sv|") {
				$matches = explode("|", $name);
				$suggested_value_fields{$matches[1]}{$matches[2]} = $value;
			}
		}

		$graph_template_id = api_graph_template_save($_POST["id"], $_POST["template_name"], $suggested_value_fields, (isset($_POST["t_image_format"]) ?
			$_POST["t_image_format"] : ""), $_POST["image_format"], (isset($_POST["t_title"]) ? $_POST["t_title"] : ""),
			(isset($_POST["t_height"]) ? $_POST["t_height"] : ""), $_POST["height"], (isset($_POST["t_width"]) ?
			$_POST["t_width"] : ""), $_POST["width"], (isset($_POST["t_x_grid"]) ? $_POST["t_x_grid"] : ""), $_POST["x_grid"],
			(isset($_POST["t_y_grid"]) ? $_POST["t_y_grid"] : ""), $_POST["y_grid"], (isset($_POST["t_y_grid_alt"]) ?
			$_POST["t_y_grid_alt"] : ""), (isset($_POST["y_grid_alt"]) ? $_POST["y_grid_alt"] : ""), (isset($_POST["t_no_minor"]) ?
			$_POST["t_no_minor"] : ""), (isset($_POST["no_minor"]) ? $_POST["no_minor"] : ""), (isset($_POST["t_upper_limit"]) ? $_POST["t_upper_limit"] : ""),
			$_POST["upper_limit"], (isset($_POST["t_lower_limit"]) ? $_POST["t_lower_limit"] : ""), $_POST["lower_limit"],
			(isset($_POST["t_vertical_label"]) ? $_POST["t_vertical_label"] : ""), $_POST["vertical_label"], (isset($_POST["t_auto_scale"]) ?
			$_POST["t_auto_scale"] : ""), (isset($_POST["auto_scale"]) ? $_POST["auto_scale"] : ""), (isset($_POST["t_auto_scale_opts"]) ?
			$_POST["t_auto_scale_opts"] : ""), $_POST["auto_scale_opts"], (isset($_POST["t_auto_scale_log"]) ? $_POST["t_auto_scale_log"] : ""),
			(isset($_POST["auto_scale_log"]) ? $_POST["auto_scale_log"] : ""), (isset($_POST["t_auto_scale_rigid"]) ?
			$_POST["t_auto_scale_rigid"] : ""), (isset($_POST["auto_scale_rigid"]) ? $_POST["auto_scale_rigid"] : ""),
			(isset($_POST["t_auto_padding"]) ? $_POST["t_auto_padding"] : ""), (isset($_POST["auto_padding"]) ? $_POST["auto_padding"] : ""),
			(isset($_POST["t_base_value"]) ? $_POST["t_base_value"] : ""), $_POST["base_value"], (isset($_POST["t_export"]) ?
			$_POST["t_export"] : ""), (isset($_POST["export"]) ? $_POST["export"] : ""), (isset($_POST["t_unit_value"]) ?
			$_POST["t_unit_value"] : ""), $_POST["unit_value"], (isset($_POST["t_unit_length"]) ? $_POST["t_unit_length"] : ""),
			$_POST["unit_length"], (isset($_POST["t_unit_exponent_value"]) ? $_POST["t_unit_exponent_value"] : ""),
			$_POST["unit_exponent_value"], (isset($_POST["t_force_rules_legend"]) ? $_POST["t_force_rules_legend"] : ""),
			(isset($_POST["force_rules_legend"]) ? $_POST["force_rules_legend"] : ""));
	}

	if ((is_error_message()) || (empty($graph_template_id)) || (empty($_POST["id"]))) {
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
					<p>Are you sure you want to delete the following graph templates? Any graphs attached
					to these templates will become individual graphs.</p>
					<p>$graph_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graph templates will be duplicated. You can
					optionally change the title format for the new graph templates.</p>
					<p>$graph_list</p>
					<p><strong>Title Format:</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($graph_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one graph template.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>";
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

		$header_label = "[edit: " . $graph_template["template_name"] . "]";
	}else{
		$header_label = "[new]";
	}

	/* ==================== Box: Graph Template ==================== */

	html_start_box("<strong>Graph Template</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(
			"form_name" => "form_graph_template"
		),
		"fields" => inject_form_variables($fields_graph_template_template_edit, (isset($graph_template) ? $graph_template : array()), (isset($template_graph) ? $template_graph : array()))
		));

	html_end_box();

	/* graph item list goes here */
	if (!empty($_GET["id"])) {
		/* ==================== Box: Graph Items ==================== */

		html_start_box("<strong>Graph Items</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_items.php?action=edit&graph_template_id=" . $_GET["id"]);
		draw_graph_item_editor($_GET["id"], "graph_template", false);
		html_end_box();

		/* ==================== Box: Graph Item Inputs ==================== */

		html_start_box("<strong>Graph Item Inputs</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates_inputs.php?action=edit&graph_template_id=" . $_GET["id"]);

		html_header(array("Name"), 2);

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
			print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td colspan='2'><em>No Inputs</em></td></tr>";
		}

		html_end_box();
	}

	/* ==================== Box: Graph ==================== */

	$form_array = array();

	/* the user clicked the "add item" link. we need to make sure they get redirected back to
	 * this page if an error occurs */
	if ($_GET["action"] == "sv_add") {
		form_hidden_box("redirect_sv_add", "x", "");
	}

	while (list($field_name, $field_array) = each($struct_graph)) {
		$form_array += array($field_name => $struct_graph[$field_name]);

		if ($field_array["method"] != "spacer") {
			$form_array[$field_name]["value"] = (isset($graph_template) ? $graph_template[$field_name] : "");
			$form_array[$field_name]["form_id"] = (isset($graph_template) ? $graph_template["id"] : "0");
			$form_array[$field_name]["description"] = "";
			$form_array[$field_name]["sub_template_checkbox"] = array(
				"name" => "t_" . $field_name,
				"friendly_name" => "Use Per-Graph Value (Ignore this Value)",
				"value" => (isset($graph_template) ? $graph_template{"t_" . $field_name} : "")
				);
		}
	}

	/* graph template specific fields */
	$form_array["title"]["method"] = "textbox_sv";
	$form_array["title"]["value"] = (empty($_GET["id"]) ? array() : array_rekey(db_fetch_assoc("select value,id from graph_template_suggested_value where graph_template_id = " . $_GET["id"] . " and field_name = 'title' order by sequence"), "id", "value"));
	$form_array["title"]["force_blank_field"] = (($_GET["action"] == "sv_add") ? true : false);
	$form_array["title"]["url_moveup"] = "javascript:document.forms[0].action.value='sv_moveup';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_moveup&id=|id|" . (empty($_GET["id"]) ? "" : "&graph_template_id=" . $_GET["id"])) . "', '')";
	$form_array["title"]["url_movedown"] = "javascript:document.forms[0].action.value='sv_movedown';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_movedown&id=|id|" . (empty($_GET["id"]) ? "" : "&graph_template_id=" . $_GET["id"])) . "', '')";
	$form_array["title"]["url_delete"] =  "javascript:document.forms[0].action.value='sv_remove';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_remove&id=|id|" . (empty($_GET["id"]) ? "" : "&graph_template_id=" . $_GET["id"])) . "', '')";
	$form_array["title"]["url_add"] = "javascript:document.forms[0].action.value='sv_add';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_add" . (empty($_GET["id"]) ? "" : "&id=" . $_GET["id"])) . "', '')";

	html_start_box("<strong>Graph</strong>", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(
				"no_form_tag" => true
			),
			"fields" => $form_array
			)
		);

	html_end_box();

	form_save_button("graph_templates.php");
}

function template() {
	global $colors, $graph_actions;
	$a = array();
	$b = array();
	$c = array();
	$d = array();
	$e = array();

	html_start_box("<strong>Graph Templates</strong>", "98%", $colors["header_background"], "3", "center", "graph_templates.php?action=edit");

	html_header_checkbox(array("Template Title"));

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
		print "<tr><td><em>No Graph Templates</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($graph_actions);

	print "</form>\n";
}

?>
