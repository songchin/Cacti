<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_gprint_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_color_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_cdef_info.php");

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
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$cdefs = api_data_preset_cdef_list();

	form_start("presets_cdef.php");

	$box_id = "1";
	html_start_box("<strong>" . _("CDEF Presets") . "</strong>", "presets_cdef.php?action=edit", "", "", false);
	html_header_checkbox(array(_("Name")), $box_id);

	if (sizeof($cdefs) > 0) {
		foreach ($cdefs as $cdef) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $cdef["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $cdef["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $cdef["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $cdef["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $cdef["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $cdef["id"];?>')" href="presets_cdef.php?action=edit&id=<?php echo $cdef["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $cdef["id"];?>"><?php echo $cdef["name"];?></span></a>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $cdef["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $cdef["id"];?>' title="<?php echo $cdef["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "1");
	}else{
		?>
		<tr class="empty">
			<td colspan="1">
				No CDEF presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these CDEF presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove CDEF Presets');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these CDEF presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate CDEF Presets');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

function view_color() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$colors = api_data_preset_color_list();

	form_start("presets_color.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Color Presets") . "</strong>", "presets_color.php?action=edit", "", "", false);
	html_header_checkbox(array(_("Hex Value"), _("Color"), ""), $box_id);

	if (sizeof($colors) > 0) {
		foreach ($colors as $color) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $color["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $color["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $color["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $color["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $color["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $color["id"];?>')" href="presets_color.php?action=edit&id=<?php echo $color["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $color["id"];?>"><?php echo $color["hex"];?></span></a>
				</td>
				<td bgcolor="#<?php echo $color["hex"];?>" width="40">
					&nbsp;
				</td>
				<td>
					&nbsp;
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $color["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $color["id"];?>' title="<?php echo $color["hex"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "3");
	}else{
		?>
		<tr class="empty">
			<td colspan="1">
				No color presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these color presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Color Presets');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these color presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Color Presets');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

function view_gprint() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$gprints = api_data_preset_gprint_list();

	form_start("presets_gprint.php");

	$box_id = "1";
	html_start_box("<strong>" . _("GPRINT Presets") . "</strong>", "presets_gprint.php?action=edit", "", "", false);
	html_header_checkbox(array(_("Name"), _("Format String")), $box_id);

	if (sizeof($gprints) > 0) {
		foreach ($gprints as $gprint) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $gprint["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $gprint["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $gprint["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $gprint["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $gprint["id"];?>')">
				<td class="title">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $gprint["id"];?>')" href="presets_gprint.php?action=edit&id=<?php echo $gprint["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $gprint["id"];?>"><?php echo $gprint["name"];?></span></a>
				</td>
				<td>
					<?php echo $gprint["gprint_text"];?>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $gprint["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $gprint["id"];?>' title="<?php echo $gprint["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "2");
	}else{
		?>
		<tr class="empty">
			<td colspan="1">
				No GPRINT presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these GPRINT presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove GPRINT Presets');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these GPRINT presets?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate GPRINT Presets');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

function view_rra() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$rras = api_data_preset_rra_list();

	form_start("presets_rra.php");

	$box_id = "1";
	html_start_box("<strong>" . _("RRA Presets") . "</strong>", "presets_rra.php?action=edit", "", "", false);
	html_header_checkbox(array(_("Name")), $box_id);

	if (sizeof($rras) > 0) {
		foreach ($rras as $rra) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $rra["id"];?>')" href="presets_rra.php?action=edit&id=<?php echo $rra["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $rra["id"];?>"><?php echo $rra["name"];?></span></a>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $rra["id"];?>' title="<?php echo $rra["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "1");
	}else{
		?>
		<tr class="empty">
			<td colspan="1">
				No RRA presets found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

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
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these RRA presets?'));
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
