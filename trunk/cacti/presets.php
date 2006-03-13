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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'view_cdef':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		draw_tabs();
		view_cdef();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_color':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		draw_tabs();
		view_color();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_gprint':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		draw_tabs();
		view_gprint();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_rra':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		draw_tabs();
		view_rra();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		draw_tabs();
		view_cdef();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* ------------------------
    Global Functions
   ------------------------ */
function draw_tabs() {
	html_tab_start();
	html_tab_draw("CDEFs", "presets.php?action=view_cdef", ((($_REQUEST["action"] == "") || ($_REQUEST["action"] == "view_cdef")) ? true : false));
	html_tab_draw("Colors", "presets.php?action=view_color", (($_REQUEST["action"] == "view_color") ? true : false));
	html_tab_draw("GPRINTs", "presets.php?action=view_gprint", (($_REQUEST["action"] == "view_gprint") ? true : false));
	html_tab_draw("RRAs", "presets.php?action=view_rra", (($_REQUEST["action"] == "view_rra") ? true : false));
	html_tab_end();
}

/* ------------------------
    Presets View Functions
   ------------------------ */

function view_cdef() {
	global $colors;

	html_start_box("<strong>" . _("CDEFs") . "</strong>", "98%", $colors["header_background"], "3", "center", "presets_cdef.php?action=edit");

	html_header(array(_("Name")), 2);

	$cdefs = db_fetch_assoc("select * from preset_cdef order by name");

	if (sizeof($cdefs) > 0) {
		$i = 0;
		foreach ($cdefs as $cdef) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="presets_cdef.php?action=edit&id=<?php echo $cdef["id"];?>"><?php echo $cdef["name"];?></a>
				</td>
				<td align="right">
					<a href="presets_cdef.php?action=remove&id=<?php echo $cdef["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
		<?php
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em><?php echo _("No Items Found");?></em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

function view_color() {
	global $colors;

	html_start_box("<strong>" . _("Colors") . "</strong>", "98%", $colors["header_background"], "3", "center", "color.php?action=edit");

	html_header(array(_("Hex Value"), _("Color")), 2);

	$color_list = db_fetch_assoc("select * from preset_color order by hex");

	if (sizeof($color_list) > 0) {
		$i = 0;
		foreach ($color_list as $color) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="presets_color.php?action=edit&id=<?php echo $color["id"];?>"><?php echo $color["hex"];?></a>
				</td>
				<td bgcolor="#<?php echo $color["hex"];?>" width="40">&nbsp;</td>
				<td align="right">
					<a href="presets_color.php?action=remove&id=<?php echo $color["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
			<?php
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em><?php echo _("No Items Found");?></em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

function view_gprint() {
	global $colors;

	html_start_box("<strong>" . _("GPRINT Presets") . "</strong>", "98%", $colors["header_background"], "3", "center", "presets_gprint.php?action=edit");

	html_header(array(_("Name"), _("Format String")), 2);

	$gprints = db_fetch_assoc("select id,name,gprint_text from preset_gprint order by name");

	if (sizeof($gprints) > 0) {
		$i = 0;
		foreach ($gprints as $gprint) {
			form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], $i);
				?>
				<td>
					<a class="linkEditMain" href="presets_gprint.php?action=edit&id=<?php echo $gprint["id"];?>"><?php echo $gprint["name"];?></a>
				</td>
				<td>
					<?php echo $gprint["gprint_text"];?>
				</td>
				<td align="right">
					<a href="presets_gprint.php?action=remove&id=<?php echo $gprint["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt=<?php echo _("Delete");?>></a>
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em><?php echo _("No Items Found");?></em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

function view_rra() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$rras = api_data_preset_rra_list();

	$box_id = "1";
	html_start_box("<strong>" . _("RRA Presets") . "</strong>", "presets_rra.php?action=edit", "", "", false);
	html_header_checkbox(array(_("Name")), $box_id);

	if (sizeof($rras) > 0) {
		foreach ($rras as $rra) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')" href="presets_rra.php?action=edit&id=<?php echo $rra["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $rra["id"];?>"><?php echo $rra["name"];?></span></a>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>' title="<?php echo $rra["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "1");
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="1">
				No RRA presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these RRA presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove RRA Presets');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these RRA Presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate RRA Presets');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>
