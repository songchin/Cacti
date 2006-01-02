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

/* html_start_box - draws the start of an HTML box with an optional title
   @arg $title - the title of this box ("" for no title)
   @arg $width - the width of the box in pixels or percent
   @arg $background_color - the color of the box border and title row background
	color
   @arg $cell_padding - the amount of cell padding to use inside of the box
   @arg $align - the HTML alignment to use for the box (center, left, or right)
   @arg $add_text - the url to use when the user clicks 'Add' in the upper-right
	corner of the box ("" for no 'Add' link) */
function html_start_box($title, $add_url = "", $search_url = "") {
	?>
	<table width="98%" cellspacing="1" cellpadding="0" align="center" class="content">
		<?php if ($title != "") {?>
		<tr>
			<td>
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td class="content-header" width="200">
							<?php echo $title;?>
						</td>
						<td class="content-header content-navigation" align="center" style="padding-right: 5px; font-weight: bold;" nowrap>
							<?php if ($search_url != "") { ?>
							[ <?php echo $search_url;?> ]
							<?php } ?>
						</td>
						<?php if ($add_url == "") { ?>
						<td class="content-header" width="200" align="right" style="padding-right: 5px; font-weight: bold;">&nbsp;</td>
						<?php }else{ ?>
						<td class="content-header" width="200" align="right" style="padding-right: 5px; font-weight: bold;">
							<a class="linkOverDark" href="<?php echo $add_url;?>">Add</a>
						</td>
						<?php } ?>
					</tr>
				</table>
			</td>
		</tr>
		<?php }?>
		<tr>
			<td>
				<table width="100%" cellpadding="3" cellspacing="0">
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
	<?php if ($trailing_br == true) { print "<br>"; } ?>
<?php }

function html_box_toolbar_draw($box_id, $form_id, $colspan, $search_type = HTML_BOX_SEARCH_NONE, $search_url = "", $show_default_actions = 1, $action_area_width = 400) {
	?>
	<tr>
		<td style="border-top: 1px solid #b5b5b5; padding: 1px;" colspan="<?php echo $colspan;?>" >
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td width="200" style="padding: 0px;">
						<?php if (($search_type == HTML_BOX_SEARCH_ACTIVE) || ($search_type == HTML_BOX_SEARCH_INACTIVE) || ($search_type == HTML_BOX_SEARCH_NO_ICON)) { ?>
						<table width="100%" cellpadding="3" cellspacing="0">
							<tr>
								<?php if (($search_type == HTML_BOX_SEARCH_ACTIVE) || ($search_type == HTML_BOX_SEARCH_INACTIVE)) { ?>
								<td width="16" id="box-<?php echo $box_id;?>-button-search" class="action-bar-button-out">
									<a href="javascript:action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'search',<?php echo $action_area_width; ?>)"><img src="<?php echo html_get_theme_images_path($search_type == HTML_BOX_SEARCH_ACTIVE ? 'action_search_active.gif' : 'action_search.gif');?>" width="16" height="16" border="0" alt="Search" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-search')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-search')" align="absmiddle"></a>
								</td>
								<td width="3">
									<img src="<?php echo html_get_theme_images_path('vertical_spacer.gif');?>" alt="" align="absmiddle">
								</td>
								<?php } ?>
								<td nowrap>
									<?php
									form_text_box("box-$box_id-search_filter", get_get_var("search_filter"), "", 100, 15, "text", 0, "small");
									form_hidden_box("action", "save");
									?>
									<input type="submit" name="box-<?php echo $box_id;?>-action-filter-button" value="Filter" class="small">
									<input type="submit" name="box-<?php echo $box_id;?>-action-clear-button" value="Clear" class="small">
								</td>
							</tr>
						</table>
						<?php } ?>
					</td>
					<td align="center" nowrap>
						<?php if (($search_type == HTML_BOX_SEARCH_ACTIVE) || ($search_type == HTML_BOX_SEARCH_INACTIVE)) { ?>
						[ <?php echo $search_url;?> ]
						<?php } ?>
					</td>
					<td width="165" style="padding: 0px;">
						<table width="100%" cellpadding="3" cellspacing="0">
							<tr>
								<td>
									&nbsp;
								</td>
								<?php if ($show_default_actions == 1) { ?>
								<td width="16" id="box-<?php echo $box_id;?>-button-duplicate" class="action-bar-button-out">
									<a href="javascript:action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'duplicate')"><img src="<?php echo html_get_theme_images_path('action_copy.gif');?>" width="16" height="16" border="0" alt="Duplicate" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-duplicate')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-duplicate')" align="absmiddle"></a>
								</td>
								<?php }else{ ?>
								<td width="16">&nbsp;</td>
								<?php } ?>
							</tr>
						</table>
					</td>
					<?php if ($show_default_actions == 1) { ?>
					<td width="16" id="box-<?php echo $box_id;?>-button-delete" class="action-bar-button-out">
						<a href="javascript:action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'remove')"><img src="<?php echo html_get_theme_images_path('action_delete.gif');?>" width="16" height="16" border="0" alt="Delete" onMouseOver="action_bar_button_mouseover('box-<?php echo $box_id;?>-button-delete')" onMouseOut="action_bar_button_mouseout('box-<?php echo $box_id;?>-button-delete')" align="absmiddle"></a>
					</td>
					<?php }else{ ?>
						<td width="16"></td>
					<?php } ?>
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

function html_box_actions_menu_draw($box_id, $form_id, $menu_items, $width = 400) {
	?>
	<div id="box-<?php echo $box_id;?>-action-bar-frame" style="width: 98%; left: 1%; position: relative;">
		<div id="box-<?php echo $box_id;?>-action-bar-menu" class="action-bar-menu" style="visibility: hidden; position: absolute; right: 0px;">
			<div id="box-<?php echo $box_id;?>-action-bar-items" class="action-bar-items">
				<?php
				if (sizeof($menu_items) > 0) {
					$i = 1;
					foreach ($menu_items as $action_name => $action_description) {
						?>
						<div id="box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>" class="action-bar-menu-out" <?php echo ($i > 1 ? "style=\"border-top: 1px solid #aab;\"" : "");?> onMouseOver="action_bar_menu_mouseover('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onMouseOut="action_bar_menu_mouseout('box-<?php echo $box_id;?>-action-bar-item-<?php echo $i;?>')" onClick="action_area_show('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>],'<?php echo $action_name;?>', '<?php echo $width; ?>')">
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

function html_box_actions_area_draw($box_id, $form_id, $width = 400, $submit = 1) {
	?>
	<div id="box-<?php echo $box_id;?>-action-area-frame" class="shadowedBox" style="width: <?php echo $width + 14;?>px; position: absolute; left: 10px; top: 10px; visibility: hidden;" width="<?php echo $width + 14;?>">
		<table cellpadding="0" cellspacing="0" border="0" width="<?php echo $width + 14;?>">
			<tr valign="bottom">
				<td class="bdr topleftcorner" width="7" height="7"></td>
				<td class="bdr topleft" width="7" height="7"></td>
				<td class="bdr top" width="<?php echo $width;?>" height="7"><img src"images/trans.gif" width="1" height="1"></td>
				<td class="bdr topright" width="7" height="7"></td>
				<td class="bdr toprightcorner" width="7" height="7"></td>
			</tr>
			<tr valign="top">
				<td height="100%">
					<table border="0" cellpadding="0" cellspacing="0" height="100%">
						<tr><td class="bdr lefttop" width="7" height="7"></td></td>
						<tr><td class="bdr left" width="7"><img src"images/trans.gif" width="7" height="1"></td></tr>
						<tr><td class="bdr leftbottom" width="7" height="7"></td></tr>
					</table>
				</td>
				<td class="action-box-border" colspan="3">
					<table border="0" cellpadding="0" cellspacing="0" width="<?php echo $width; ?>">
						<tr align="top">
							<td width="<?php echo $width;?>">
								<div id="box-<?php echo $box_id;?>-action-area-menu" class="action-area-menu">
									<div id="box-<?php echo $box_id;?>-action-area-header" class="action-area-header">
										<table width="<?php echo ($width);?>" cellspacing="0" cellpadding="0" border="0">
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
									<div class="action-area-buttons" width="<?php echo $width; ?>">
										<input type="reset" value="Cancel" class="action-area-buttons" name="box-<?php echo $box_id;?>-action-area-button-cancel" id="box-<?php echo $box_id;?>-action-area-button-cancel" onClick="action_area_hide('<?php echo $box_id;?>')">
										<?php if ($submit == 1) { ?>
										<input type="submit" value="X" class="action-area-buttons" name="box-<?php echo $box_id;?>-action-area-button" id="box-<?php echo $box_id;?>-action-area-button" onClick="action_area_update_input('<?php echo $box_id;?>',document.forms[<?php echo $form_id;?>])">
										<?php } ?>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
				<td height="100%">
					<table border="0" cellpadding="0" cellspacing="0" height="100%">
						<tr><td class="bdr righttop" width="7" height="7"></td></tr>
						<tr><td class="bdr right" width="7"><img src"images/trans.gif" width="7" height="1"></td></tr>
						<tr><td class="bdr rightbottom" width="7" height="7"></td></tr>
					</table>
				</td>
			</tr>
			<tr valign="top">
				<td class="bdr bottomleftcorner" width="7" height="7"></td>
				<td class="bdr bottomleft" width="7" height="7"></td>
				<td class="bdr bottom" width="<?php echo $width;?>" height="7"><img src"images/trans.gif" width="1" height="1"></td>
				<td class="bdr bottomright" width="7" height="7"></td>
				<td class="bdr bottomrightcorner" width="7" height="7"></td>
			</tr>
		</table>
	</div>
	<script language="JavaScript">
	<!--
	/* add this div to the drag DHTML */
	ADD_DHTML("box-<?php echo $box_id;?>-action-area-frame", "box-<?php echo $box_id;?>-action-area-header", "box-<?php echo $box_id;?>-action-area-items");

	/* force position because of ie weirdness */
	dd.elements["box-<?php echo $box_id;?>-action-area-frame"].moveTo((get_browser_width() / 2) - <?php echo $width / 2; ?>, '100');
	-->
	</script>
	<?php
}
