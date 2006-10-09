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

/* html_start_box - draws the start of an HTML box with an optional title
   @arg $title - the title of this box ("" for no title)
   @arg $width - the width of the box in pixels or percent
   @arg $background_color - the color of the box border and title row background
	color
   @arg $cell_padding - the amount of cell padding to use inside of the box
   @arg $align - the HTML alignment to use for the box (center, left, or right)
   @arg $add_text - the url to use when the user clicks 'Add' in the upper-right
	corner of the box ("" for no 'Add' link) */
function html_start_box($title, $add_url = "", $search_url = "", $box_id = false, $include_top_border = true, $box_padding = 3, $hidden = false) {
	?>
	<table<?php echo ($box_id === false ? "" : " id=\"box-$box_id\"");?> cellspacing="1" cellpadding="0" align="center" class="box"<?php echo ($include_top_border == true ? "" : " style=\"border-top: none;\"");?><?php echo ($hidden == true ? " style=\"display: none;\"" : "");?>>
		<?php if ($title != "") {?>
		<tr>
			<td>
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr class="header">
						<td width="200">
							<?php echo $title;?>
						</td>
						<?php if ($search_url != "") { ?>
						<td class="pagination" align="center" nowrap="true">
							[ <?php echo $search_url;?> ]
						</td>
						<?php } ?>
						<?php if ($add_url == "") { ?>
						<td width="200" align="right">&nbsp;</td>
						<?php }else{ ?>
						<td width="200" align="right">
							<a href="<?php echo $add_url;?>">Add</a>
						</td>
						<?php } ?>
					</tr>
				</table>
			</td>
		</tr>
		<?php }?>
		<tr>
			<td>
				<table<?php echo ($box_id === false ? "" : " id=\"box-$box_id-content\"");?> width="100%" cellpadding="<?php echo $box_padding;?>" cellspacing="0">
					<?php
}

/* html_end_box - draws the end of an HTML box
   @arg $trailing_br (bool) - whether to draw a trailing <br> tag after ending
	the box */
function html_end_box($trailing_br = true) { ?>
				</table>
			</td>
		</tr>
	</table>
	<?php if ($trailing_br == true) { echo "<br />"; } ?>
<?php }

function html_box_toolbar_draw($box_id, $form_id, $colspan, $search_type = HTML_BOX_SEARCH_NONE, $search_url = "", $show_default_actions = 1, $action_area_width = 400) {
	?>
	<tr class="toolbar">
		<td colspan="<?php echo $colspan;?>">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td width="200">
						&nbsp;
					</td>
					<td class="pagination" align="center" nowrap="true">
						[ <?php echo $search_url;?> ]
					</td>
					<td width="126">&nbsp;</td>
					<?php if ($show_default_actions == 1) { ?>
					<td width="16" id="box-<?php echo $box_id;?>-button-duplicate" class="button_mouseout">
						<a href="javascript:action_area_box_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'duplicate')"><img src="<?php echo html_get_theme_images_path('action_copy.gif');?>" width="16" height="16" border="0" alt="Duplicate" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-duplicate')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-duplicate')" align="absmiddle"></a>
					</td>
					<td width="16" id="box-<?php echo $box_id;?>-button-delete" class="button_mouseout">
						<a href="javascript:action_area_box_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'remove')"><img src="<?php echo html_get_theme_images_path('action_delete.gif');?>" width="16" height="16" border="0" alt="Delete" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-delete')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-delete')" align="absmiddle"></a>
					</td>
					<?php }else{ ?>
					<td width="16">&nbsp;</td>
					<td width="16"></td>
					<?php } ?>
					</td>
				</tr>
			</table>
		</td>
		<td width="1%" id="box-<?php echo $box_id;?>-button-menu-container" class="button_menu">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td id="box-<?php echo $box_id;?>-button-menu" class="button_mouseout">
						<a href="javascript:action_bar_button_menu_click('<?php echo $box_id;?>')"><img src="<?php echo html_get_theme_images_path('action_menu.gif');?>" width="16" height="16" border="0" alt="Choose..." onMouseOver="action_bar_button_menu_mouseover('<?php echo $box_id;?>')" onMouseOut="action_bar_button_menu_mouseout('<?php echo $box_id;?>')" align="absmiddle"></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

function html_box_actions_menu_draw($box_id, $form_id, $menu_items, $width = 400) {
	?>
	<div id="box-<?php echo $box_id;?>-action-bar-menu" class="action_menu">
		<?php
		if (sizeof($menu_items) > 0) {
			$i = 1;
			foreach ($menu_items as $action_name => $action_description) {
				?>
				<div id="box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>" class="mouseout<?php echo ($i > 1 ? " item_spacer" : "");?>" onMouseOver="action_bar_menu_mouseover('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onMouseOut="action_bar_menu_mouseout('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onClick="action_area_box_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'<?php echo $action_name;?>', '<?php echo $width; ?>')">
					<?php echo $action_description;?>
				</div>
				<?php
				$i++;
			}
		}
		?>
	</div>
	<?php
}

function html_box_actions_area_create($box_id, $width = 400) {
	?>
	<script language="JavaScript">
	<!--
	action_area_box_create("<?php echo $box_id;?>", <?php echo $width;?>);
	-->
	</script>
	<?php
}

?>
