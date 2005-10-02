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

function html_box_toolbar_draw($box_id, $form_id) {
	?>
	<tr>
		<td colspan="2" style="border-top: 1px solid #b5b5b5; padding: 1px;">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td>
						&nbsp;
					</td>
					<td width="16" id="box-<?php echo $box_id;?>-button-duplicate" class="action-bar-button-out">
						<a href="javascript:action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'duplicate')"><img src="<?php echo html_get_theme_images_path('action_copy.gif');?>" width="16" height="16" border="0" alt="Duplicate" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-duplicate')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-duplicate')" align="absmiddle"></a>
					</td>
					<td width="16" id="box-<?php echo $box_id;?>-button-delete" class="action-bar-button-out">
						<a href="javascript:action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'remove')"><img src="<?php echo html_get_theme_images_path('action_delete.gif');?>" width="16" height="16" border="0" alt="Delete" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-delete')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-delete')" align="absmiddle"></a>
					</td>
				</tr>
			</table>
		</td>
		<td width="1%" id="box-<?php echo $box_id;?>-button-menu-container" style="border-top: 1px solid #b5b5b5; border-left: 1px solid #b5b5b5; padding: 1px;">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td id="box-<?php echo $box_id;?>-button-menu" class="action-bar-button-out">
						<a href="javascript:action_bar_button_menu_click('<?php echo $box_id;?>')"><img src="<?php echo html_get_theme_images_path('action_menu.gif');?>" width="16" height="16" border="0" alt="Choose..." onMouseOver="action_bar_button_menu_mouseover('<?php echo $box_id;?>')" onMouseOut="action_bar_button_menu_mouseout('<?php echo $box_id;?>')" align="absmiddle"></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

function html_box_actions_menu_draw($box_id, $form_id, $menu_items) {
	?>
	<div id="box-<?php echo $box_id;?>-action-bar-frame" style="width: 98%; left: 1%; position: relative;">
		<div id="box-<?php echo $box_id;?>-action-bar-menu" class="action-bar-menu" style="visibility: hidden; position: absolute; right: 0px;">
			<div id="box-<?php echo $box_id;?>-action-bar-items" class="action-bar-items">
				<?php
				if (sizeof($menu_items) > 0) {
					$i = 1;
					foreach ($menu_items as $action_name => $action_description) {
						?>
						<div id="box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>" class="action-bar-menu-out" <?php echo ($i > 1 ? "style=\"border-top: 1px solid #aab;\"" : "");?> onMouseOver="action_bar_menu_mouseover('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onMouseOut="action_bar_menu_mouseout('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onClick="action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'<?php echo $action_name;?>')">
							<?php echo $action_description;?>
						</div>
						<?php
						$i++;
					}
				}
				?>
			</div>
		</div>
	</div>
	<?php
}

function html_box_actions_area_draw($box_id, $form_id) {
	?>
	<div id="box-<?php echo $box_id;?>-action-area-frame" class="shadowedBox" style="width: 400px; position: absolute; left: 30%; top: 150px; visibility: hidden;" width="400">
		<table cellpadding="0" cellspacing="0" border="0" width="400">
			<tr valign="top">
				<td class="bdr left" width="7"></td>
				<td width="387">
					<div id="box-<?php echo $box_id;?>-action-area-menu" class="action-area-menu">
						<div id="box-<?php echo $box_id;?>-action-area-header" class="action-area-header">
							<table width="376" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td id="box-<?php echo $box_id;?>-action-area-header-caption" class="action-area-header">
										&nbsp;
									</td>
									<td align="right">
										<a href="javascript:action_area_hide('<?php echo $box_id;?>')"><img src="<?php echo html_get_theme_images_path('action_area_close.gif');?>" border="0" alt="Close Dialog"></a>
									</td>
								</tr>
							</table>
						</div>
						<div id="box-<?php echo $box_id;?>-action-area-items" class="action-area-items">
							&nbsp;
						</div>
						<div class="action-area-buttons">
							<input type="submit" value="X" class="action-area-buttons" name="box-<?php echo $box_id;?>-action-area-button" id="box-<?php echo $box_id;?>-action-area-button" onClick="action_area_update_input('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>])">
						</div>
					</div>
				</td>
				<td class="bdr right" width="7"></td>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" width="400">
			<tr valign="top">
				<td class="bdr bottomleft" width="7" height="7"></td>
				<td class="bdr bottom" width="386" height="7"></td>
				<td class="bdr bottomright" width="7" height="7"></td>
			</tr>
		</table>
	</div>
	<script language="JavaScript">
	<!--
	SET_DHTML("box-<?php echo $box_id;?>-action-area-frame", "box-<?php echo $box_id;?>-action-area-header"+DRAG, "box-<?php echo $box_id;?>-action-area-items");

	/* force position because of ie weirdness */
	dd.elements["box-<?php echo $box_id;?>-action-area-frame"].moveTo((get_browser_width() / 2) - 200, '150');
	-->
	</script>
	<?php
}
