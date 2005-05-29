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
include("./lib/api_rra_templates.php");

define("MAX_DISPLAY_PAGES", 21);

$rra_template_actions = array(
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
	case 'gt_remove':
		host_remove_gt();

		header("Location: rra_templates.php?action=edit&id=" . $_GET["rra_template_id"]);
		break;
	case 'rra_remove':
		host_remove_query();

		header("Location: rra_templates.php?action=edit&id=" . $_GET["rra_template_id"]);
		break;
	case 'edit':
		include_once("./include/top_header.php");

		rra_template_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		rra_template();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
//	if ((!empty($_POST["add_gt_y"])) && (!empty($_POST["rra_template_id"]))) {
//		db_execute("replace into host_graph (host_id,graph_template_id) values (" . $_POST["id"] . "," . $_POST["graph_template_id"] . ")");
//
//		header("Location: rra_templates.php?action=edit&id=" . $_POST["id"]);
//		exit;
//	}

	if ((isset($_POST["save_component_rra_template"])) && (empty($_POST["add_dq_y"]))) {
		$rra_template_id = api_rra_template_save($_POST["id"], $_POST["name"], $_POST["description"]);

		if ((is_error_message()) || ($_POST["id"] != $_POST["_rra_template_id"])) {
			header("Location: rra_templates.php?action=edit&id=" . (empty($rra_template_id) ? $_POST["id"] : $rra_template_id));
		}else{
			header("Location: rra_templates.php");
		}
	}

}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $rra_template_actions, $fields_rra_template_edit;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete rratemplate */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("delete from rra_template_settings where rra_template_id=" . $selected_items[$i]);
				db_execute("delete from rra_template where rra_template_id=" . $selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "2") { /* dupliate rratemplate */
			for ($i=0;($i<count($selected_items));$i++) {
			}
		}

		header("Location: rra_templates.php");
		exit;
	}

	/* setup some variables */
	$rra_template_list = ""; $i = 0;

	/* loop through each of the rra templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$rra_template_list .= "<li>" . db_fetch_cell("select description from rra_template where id=" . $matches[1]) . "<br>";
			$rra_template_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $rra_template_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='rra_templates.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete rra_template */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To delete the following rra template, press the \"yes\" button below.") . "</p>
					<p>$rra_template_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate rra_template */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To duplicate the rra template, provide a name and then press the \"yes\" button below.") . "</p>
				</td>
				</tr>";
	}

	if (!isset($rra_template_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one rra template.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td colspan='2' align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($rra_template_array) ? serialize($rra_template_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='host.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* ---------------------
    RRA Template Functions
   --------------------- */

function rra_template_remove() {
	global $config;

	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the rra template?<strong>'") . db_fetch_cell("select description from rra_template where id=" . $_GET["id"]) . "'</strong>?", "rra_templates.php", "rra_templates.php?action=remove&id=" . $_GET["id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_rra_template_remove($_GET["id"]);
	}
}

function rra_template_edit() {
	global $colors, $fields_rra_template_edit;

	display_output_messages();

	if (!empty($_GET["id"])) {
		$rra_template = db_fetch_row("select * from rra_template where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $rra_template["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	if (!empty($rra_template["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td class="textInfo" colspan="2">
					<?php print $rra_template["name"];?> (<?php print $rra_template["description"];?>)
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	html_start_box("<strong>" . _("RRA Templates") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	/* preserve the rra template id if passed in via a GET variable */
	if (!empty($_GET["rra_template_id"])) {
		$fields_rra_template_edit["rra_template_id"]["value"] = $_GET["rra_template_id"];
	}

	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_rra_template_edit, (isset($rra_template) ? $rra_template : array()))
		));

	html_end_box();

	if (!empty($rra_template["id"])) {
		html_start_box("<strong>" . _("Associated RRA Items") . "</strong>", "98%", $colors["header_background"], "3", "center", "rra_templates.php?action=item_edit");

		html_header(array(_("Name"), _("Steps"), _("Rows"), _("Timespan"), ("X Files Factor")), 2);

		$rra_template_settings = db_fetch_assoc("select
			rra_template_settings.id,
			rra_template_settings.hash
			rra_template_settings.name,
			rra_template_settings.steps
			rra_template_settings.rows
			rra_template_settings.timespan
			rra_template_settings.x_files_factor
			from rra_template_settings
			where rra_template_settings.rra_template_id=" . $_GET["id"] . "
			order by sequence");

		$i = 0;
		if (sizeof($rra_template_settings) > 0) {
			foreach ($rra_template_settings as $rra_template_setting) {
				$i++;
				form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
					?>
					<td>
						<a class="linkEditMain" href="rra.php?action=edit&id=<?php print $rra_template_settings["id"];?>"><?php print $rra_template_settings["name"];?></a>
					</td>
					<td>
						<?php print $rra_template_settings["steps"];?>
					</td>
					<td>
						<?php print $rra_template_settings["rows"];?>
					</td>
					<td>
						<?php print $rra_template_settings["timespan"];?>
					</td>
					<td align="right">
						<a href="rra_templates.php?action=remove&id=<?php print $rra_template_settings["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
					</td>
				</tr>
			<?php
			}
		}else{
			print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No associated rra template settings.") . "</em></td></tr>";
		}

		html_end_box();
	}

	if (!empty($_GET["id"])) {
		html_start_box("<strong>" . _("Round Robin Archives") . "</strong>", "98%", $colors["header_background"], "3", "center", "rra.php?action=edit");

		print "<tr bgcolor='#" . $colors["header_panel_background"] . "'>";
			DrawMatrixHeaderItem(_("Name"),$colors["header_text"],1);
			DrawMatrixHeaderItem(_("Steps"),$colors["header_text"],1);
			DrawMatrixHeaderItem(_("Rows"),$colors["header_text"],1);
			DrawMatrixHeaderItem(_("Timespan"),$colors["header_text"],2);
		print "</tr>";

		$rras = db_fetch_assoc("select id,name,rows,steps,timespan from rra order by steps");

		$i = 0;
		if (sizeof($rras) > 0) {
			foreach ($rras as $rra) {
				form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
					?>
					<td>
						<a class="linkEditMain" href="rra.php?action=edit&id=<?php print $rra["id"];?>"><?php print $rra["name"];?></a>
					</td>
					<td>
						<?php print $rra["steps"];?>
					</td>
					<td>
						<?php print $rra["rows"];?>
					</td>
					<td>
						<?php print $rra["timespan"];?>
					</td>
					<td align="right">
						<a href="rra.php?action=remove&id=<?php print $rra["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
					</td>
				</tr>
			<?php
			}
		}

		html_end_box();
	}

	form_save_button("rra_templates.php");
}

function rra_template() {
	global $colors, $rra_template_actions;

	html_start_box("<strong>" . _("Round Robin Archive Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "rra_templates.php?action=edit");

	$rra_templates = db_fetch_assoc("select
		rra_template.id,
		rra_template.hash,
		rra_template.name,
		rra_template.description
		from rra_template
		order by rra_template.name");

	html_header(array(_("RRA Template Name"), _("Description")), 2);

	$i = 0;
	if (sizeof($rra_templates) > 0) {
		foreach ($rra_templates as $rra_template) {
			$highlight_text = $rra_template["name"];

			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td width=200>
					<a class="linkEditMain" href="rra_templates.php?action=edit&id=<?php print $rra_template["id"];?>"><?php print $highlight_text;?></a>
				</td>
				<td>
					<?php print $rra_template["description"];?>
				</td>
				<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_<?php print $rra_template["id"];?>' title="<?php print $rra_template["name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=3><em>" . _("No RRA Templates") . "</em></td></tr>";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($rra_template_actions);
}

?>
