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

/* html_graph_start_box - draws the start of an HTML graph view box
   @arg $cellpadding - the table cell padding for the box
   @arg $leading_br (bool) - whether to draw a leader <br> tag before the start of the table */
function html_graph_start_box($cellpadding = 3, $leading_br = true) {
	global $colors;

	if ($leading_br == true) {
		print "<br>\n";
	}

	print "<table width='98%' style='background-color: #" . $colors["graph_menu_background"] . "; border: 1px solid #" . $colors["graph_menu_border"] . ";' align='center' cellpadding='$cellpadding'>\n";
}

/* html_graph_end_box - draws the end of an HTML graph view box */
function html_graph_end_box() {
	print "</table>";
}

/* html_graph_area - draws an area the contains full sized graphs
   @arg $graph_array - the array to contains graph information. for each graph in the
	array, the following two keys must exist
	$arr[0]["local_graph_id"] // graph id
	$arr[0]["title_cache"] // graph title
   @arg $no_graphs_message - display this message if no graphs are found in $graph_array
   @arg $extra_url_args - extra arguments to append to the url
   @arg $header - html to use as a header */
function html_graph_area(&$graph_array, $no_graphs_message = "", $extra_url_args = "", $header = "") {
	global $colors;

	$i = 0;

	if (sizeof($graph_array) > 0) {
		if ($header != "") {
			print $header;
		}

		foreach ($graph_array as $graph) {
			?>
			<tr bgcolor='#<?php print ($i % 2 == 0 ? $colors["graph_alternate1"] : $colors["graph_alternate2"]);?>'>
				<td align='center'>
					<table width='1' cellpadding='0'>
						<tr>
							<td>
								<a href='graph.php?graph_id=<?php print $graph["graph_id"];?>&rra_id=all'><img src='graph_image.php?graph_id=<?php print $graph["graph_id"];?>&rra_id=0<?php print (($extra_url_args == "") ? "" : "&$extra_url_args");?>' border='0' alt='<?php print $graph["title_cache"];?>'></a>
							</td>
							<td valign='top' style='padding: 3px;' class='noprint'>
								<a href='graph.php?action=zoom&graph_id=<?php print $graph["graph_id"];?>&rra_id=0&<?php print $extra_url_args;?>'><img src='<?php print html_get_theme_images_path("graph_zoom.gif");?>' border='0' alt='<?php echo _("Zoom Graph");?>' title='<?php echo _("Zoom Graph");?>' style='padding: 3px;'></a><br>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php

			$i++;
		}
	}else{
		if ($no_graphs_message != "") {
			print "<td><em>$no_graphs_message</em></td>";
		}
	}
}

/* html_graph_thumbnail_area - draws an area the contains thumbnail sized graphs
   @arg $graph_array - the array to contains graph information. for each graph in the
	array, the following two keys must exist
	$arr[0]["local_graph_id"] // graph id
	$arr[0]["title_cache"] // graph title
   @arg $no_graphs_message - display this message if no graphs are found in $graph_array
   @arg $extra_url_args - extra arguments to append to the url
   @arg $header - html to use as a header */
function html_graph_thumbnail_area(&$graph_array, $no_graphs_message = "", $extra_url_args = "", $header = "") {
	global $colors;

	$i = 0; $k = 0;
	if (sizeof($graph_array) > 0) {
		if ($header != "") {
			print $header;
		}

		print "<tr>";

		foreach ($graph_array as $graph) {
			?>
			<td align='center' width='<?php print (98 / read_graph_config_option("num_columns"));?>%'>
				<table width='1' cellpadding='0'>
					<tr>
						<td>
							<a href='graph.php?rra_id=all&graph_id=<?php print $graph["graph_id"];?>'><img src='graph_image.php?graph_id=<?php print $graph["graph_id"];?>&rra_id=0&graph_height=<?php print read_graph_config_option("default_height");?>&graph_width=<?php print read_graph_config_option("default_width");?>&graph_nolegend=true<?php print (($extra_url_args == "") ? "" : "&$extra_url_args");?>' border='0' alt='<?php print $graph["title_cache"];?>'></a>
						</td>
						<td valign='top' style='padding: 3px;'>
							<a href='graph.php?action=zoom&graph_id=<?php print $graph["graph_id"];?>&rra_id=0&<?php print $extra_url_args;?>'><img src='<?php print html_get_theme_images_path("graph_zoom.gif");?>' border='0' alt='<?php echo _("Zoom Graph");?>' title='<?php echo _("Zoom Graph");?>' style='padding: 3px;'></a><br>
						</td>
					</tr>
				</table>
			</td>
			<?php

			$i++;
			$k++;

			if (($i == read_graph_config_option("num_columns")) && ($k < count($graph_array))) {
				$i = 0;
				print "</tr><tr>";
			}
		}

		print "</tr>";
	}else{
		if ($no_graphs_message != "") {
			print "<td><em>$no_graphs_message</em></td>";
		}
	}
}

/* html_nav_bar - draws a navigation bar which includes previous/next links as well as current
	page information
   @arg $background_color - the background color of this navigation bar row
   @arg $colspan - the colspan for the entire row
   @arg $current_page - the current page in the navigation system
   @arg $rows_per_page - the number of rows that are displayed on a single page
   @arg $total_rows - the total number of rows in the navigation system
   @arg $nav_url - the url to use when presenting users with previous/next links. the variable
	<PAGE> will be substituted with the correct page number if included */
function html_nav_bar($background_color, $colspan, $current_page, $rows_per_page, $total_rows, $nav_url) {
	?>
	<tr bgcolor='#<?php print $background_color;?>'>
		<td colspan='<?php print $colspan;?>'>
			<table width='100%' cellspacing='0' cellpadding='3' border='0'>
				<tr>
					<td align='left' class='textHeaderDark'>
						<strong>&lt;&lt; <?php if ($current_page > 1) { print "<a class='linkOverDark' href='" . str_replace("<PAGE>", ($current_page-1), $nav_url) . "'>"; } print "Previous"; if ($current_page > 1) { print "</a>"; } ?></strong>
					</td>
					<td align='center' class='textHeaderDark'>
						Showing Rows <?php print (($rows_per_page*($current_page-1))+1);?> to <?php print ((($total_rows < $rows_per_page) || ($total_rows < ($rows_per_page*$current_page))) ? $total_rows : ($rows_per_page*$current_page));?> of <?php print $total_rows;?>
					</td>
					<td align='right' class='textHeaderDark'>
						<strong><?php if (($current_page * $rows_per_page) < $total_rows) { print "<a class='linkOverDark' href='" . str_replace("<PAGE>", ($current_page+1), $nav_url) . "'>"; } print _("Next"); if (($current_page * $rows_per_page) < $total_rows) { print "</a>"; } ?> &gt;&gt;</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

/* html_header - draws a header row suitable for display inside of a box element
   @arg $header_items - an array containing a list of items to be included in the header
   @arg $last_item_colspan - the TD 'colspan' to apply to the last cell in the row */
function html_header($header_items, $last_item_colspan = 1) {
	echo "<tr>\n";

	for ($i=0; $i<count($header_items); $i++) {
		echo "<td " . ((($i+1) == count($header_items)) ? "colspan='$last_item_colspan' " : "") . "class='content-header-sub'>" . $header_items[$i] . "</td>\n";
	}

	echo "</tr>\n";
}

/* html_header_checkbox - draws a header row with a 'select all' checkbox in the last cell
	suitable for display inside of a box element
   @arg $header_items - an array containing a list of items to be included in the header
   @arg $form_action - the url to post the 'select all' form to */
function html_header_checkbox($header_items, $box_id) {
	echo "<tr>\n";

	for ($i=0; $i<count($header_items); $i++) {
		echo "<td class='content-header-sub'>" . $header_items[$i] . "</td>\n";
	}

	echo "<td width='1%' align='center' bgcolor='#819bc0' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='box-$box_id-allchk' id='box-$box_id-allchk' title='" . _("Select All") . "' onClick='display_row_select_all(\"$box_id\",document.forms[0])'></td>\n";
	echo "</tr>\n";
}

/* html_get_theme_css() - returns the Style sheet reference for the current theme
   */
function html_get_theme_css() {
	if ((isset($_SESSION["sess_user_id"])) && (api_user_theme($_SESSION["sess_user_id"]) != "default")) {
		$theme = api_user_theme($_SESSION["sess_user_id"]);
	}else{
		$theme = read_config_option("default_theme");
	}

	return "themes/" . $theme . "/" . $theme . ".css";
}

/* html_get_theme_image_path() - returns the Style sheet reference for the current theme
   */
function html_get_theme_images_path($image_file = "") {
	if ((isset($_SESSION["sess_user_id"])) && (api_user_theme($_SESSION["sess_user_id"]) != "default")) {
		$theme = api_user_theme($_SESSION["sess_user_id"]);
	}else{
		$theme = read_config_option("default_theme");
	}

	if (file_exists("themes/" . $theme . "/images/" . $image_file)) {
		return "themes/" . $theme . "/images/" . $image_file;
	} else {
		if ($image_file != "") {
			return "images/" . $image_file;
		} else {
			return "images";
		}
	}
}

function html_theme_color_scheme() {
	if ((isset($_SESSION["sess_user_id"])) && (api_user_theme($_SESSION["sess_user_id"]) != "default")) {
		$theme = api_user_theme($_SESSION["sess_user_id"]);
	}else{
		$theme = read_config_option("default_theme");
	}

	if (file_exists(CACTI_BASE_PATH . "/themes/" . $theme . "/" . $theme . ".php")) {
		return CACTI_BASE_PATH . "/themes/" . $theme . "/" . $theme . ".php";
	} else {
		return CACTI_BASE_PATH . "/include/config_colors.php";
	}
}

/* create_list - draws the items for an html dropdown given an array of data
   @arg $form_data - an array containing data for this dropdown. it can be formatted
	in one of two ways:
	$array["id"] = "value";
	-- or --
	$array[0]["id"] = 43;
	$array[0]["name"] = "Red";
   @arg $column_display - used to indentify the key to be used for display data. this
	is only applicable if the array is formatted using the second method above
   @arg $column_id - used to indentify the key to be used for id data. this
	is only applicable if the array is formatted using the second method above
   @arg $form_previous_value - the current value of this form element */
function html_create_list($form_data, $column_display, $column_id, $form_previous_value, $trim_display_length = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");

	if (empty($column_display)) {
		foreach (array_keys($form_data) as $id) {
			if (empty($trim_display_length)) {
				$item_text = htmlspecialchars((remove_variables($form_data[$id])), ENT_QUOTES);
			}else{
				$item_text = htmlspecialchars((title_trim(remove_variables($form_data[$id]), $trim_display_length)), ENT_QUOTES);
			}

			echo '<option value="' . htmlspecialchars($id, ENT_QUOTES) . '"';

			if (strval($form_previous_value) == $id) {
				echo " selected";
			}

			echo ">$item_text</option>\n";
		}
	}else{
		if (sizeof($form_data) > 0) {
			foreach ($form_data as $row) {
				if (empty($trim_display_length)) {
					$item_text = htmlspecialchars((remove_variables($row[$column_display])), ENT_QUOTES);
				}else{
					$item_text = htmlspecialchars((title_trim(remove_variables($row[$column_display]), $trim_display_length)), ENT_QUOTES);
				}

				echo "<option value='" . htmlspecialchars($row[$column_id], ENT_QUOTES) . "'";

				if (strval($form_previous_value) == $row[$column_id]) {
					echo " selected";
				}

				//if (isset($row["host_id"])) {
				//	print ">" . htmlspecialchars(title_trim($row[$column_display], 75), ENT_QUOTES) . "</option>\n";
				//}else{
				echo ">$item_text</option>\n";
				//}
			}
		}
	}
}

/* html_get_php_os_icon - returns the name of the os Icon for output processing in Cacti
 */
function html_get_php_os_icon() {
	if (PHP_OS == "WINNT") {
		$os = "xp";
	} else {
		$os = PHP_OS;
	}

	if (file_exists(CACTI_BASE_PATH . "/images/os_" . $os . ".gif")) {
		return "images/os_" . $os . ".gif";
	} else {
		return "images/os_cacti.gif";
	}
}

/* draw_menu - draws the cacti menu for display in the console */
function draw_menu() {
	global $colors, $user_auth_realms, $user_auth_realm_filenames;

	require(CACTI_BASE_PATH . "/include/config_arrays.php");

	/* list all realms that this user has access to */
	if (read_config_option("auth_method") != "0") {
		$user_realms = db_fetch_assoc("select realm_id from user_auth_realm where user_id=" . $_SESSION["sess_user_id"]);
		$user_realms = array_rekey($user_realms, "realm_id", "realm_id");
	}else{
		$user_realms = $user_auth_realms;
	}

	print "<tr><td width='100%'><table cellpadding='3' cellspacing='0' border='0' width='100%'>\n";

	/* loop through each header */
	while (list($header_name, $header_array) = each($menu)) {
		/* pass 1: see if we are allowed to view any children */
		$show_header_items = false;
		while (list($item_url, $item_title) = each($header_array)) {
			$current_realm_id = (isset($user_auth_realm_filenames{basename($item_url)}) ? $user_auth_realm_filenames{basename($item_url)} : 0);

			if ((isset($user_realms[$current_realm_id])) || (!isset($user_auth_realm_filenames{basename($item_url)}))) {
				$show_header_items = true;
			}
		}

		reset($header_array);

		if ($show_header_items == true) {
			print "<tr><td class='textMenuHeader'>$header_name</td></tr>\n";
		}

		/* pass 2: loop through each top level item and render it */
		while (list($item_url, $item_title) = each($header_array)) {
			$current_realm_id = (isset($user_auth_realm_filenames{basename($item_url)}) ? $user_auth_realm_filenames{basename($item_url)} : 0);

			/* if this item is an array, then it contains sub-items. if not, is just
			the title string and needs to be displayed */
			if (is_array($item_title)) {
				$i = 0;

				if ((isset($user_realms[$current_realm_id])) || (!isset($user_auth_realm_filenames{basename($item_url)}))) {
					/* if the current page exists in the sub-items array, draw each sub-item */
					if (array_key_exists(basename($_SERVER["PHP_SELF"]), $item_title) == true) {
						$draw_sub_items = true;
					}else{
						$draw_sub_items = false;
					}

					while (list($item_sub_url, $item_sub_title) = each($item_title)) {
						/* indent sub-items */
						if ($i > 0) {
							$prepend_string = "---&nbsp;";
						}else{
							$prepend_string = "";
						}

						/* do not put a line between each sub-item */
						if (($i == 0) || ($draw_sub_items == false)) {
							$background = "" . html_get_theme_images_path("menu_line.gif");
						}else{
							$background = "";
						}

						/* draw all of the sub-items as selected for ui grouping reasons. we can use the 'bold'
						or 'not bold' to distinguish which sub-item is actually selected */
						if ((basename($_SERVER["PHP_SELF"]) == basename($item_sub_url)) || ($draw_sub_items)) {
							$td_class = "textMenuItemSelected";
						}else{
							$td_class = "textMenuItem";
						}

						/* always draw the first item (parent), only draw the children if we are viewing a page
						that is contained in the sub-items array */
						if (($i == 0) || ($draw_sub_items)) {
							if (basename($_SERVER["PHP_SELF"]) == basename($item_sub_url)) {
								print "<tr><td class='$td_class' background='$background'>$prepend_string<strong><a href='$item_sub_url'>$item_sub_title</a></strong></td></tr>\n";
							}else{
								print "<tr><td class='$td_class' background='$background'>$prepend_string<a href='$item_sub_url'>$item_sub_title</a></td></tr>\n";
							}
						}

						$i++;
					}
				}
			}else{
				if ((isset($user_realms[$current_realm_id])) || (!isset($user_auth_realm_filenames{basename($item_url)}))) {
					/* draw normal (non sub-item) menu item */
					if (basename($_SERVER["PHP_SELF"]) == basename($item_url)) {
						print "<tr><td class='textMenuItemSelected' background='" . html_get_theme_images_path("menu_line.gif") . "'><strong><a href='$item_url'>$item_title</a></strong></td></tr>\n";
					}else{
						print "<tr><td class='textMenuItem' background='" . html_get_theme_images_path("menu_line.gif") . "'><a href='$item_url'>$item_title</a></td></tr>\n";
					}
				}
			}
		}
	}

	print "<tr><td class='textMenuItem' background='" . html_get_theme_images_path("menu_line.gif") . "'></td></tr>\n";

	print '</table></td></tr>';
}

/* draw_actions_dropdown - draws a table the allows the user to select an action to perform
	on one or more data elements
   @arg $actions_array - an array that contains a list of possible actions. this array should
	be compatible with the form_dropdown() function */
function draw_actions_dropdown($actions_array, $padding = 6, $percent_width = 98, $action_variable = true) {
	?>
	<table align='center' width='<?php echo $percent_width;?>%' cellspacing="0" cellpadding="0">
		<tr>
			<td width='1' valign='middle' style='padding: <?php echo $padding;?>px;'>
				<img src='<?php print html_get_theme_images_path("arrow.gif");?>' alt='' align='absmiddle'>
			</td>
			<td align='right'>
				Choose an action:
				<?php form_dropdown("drp_action",$actions_array,"","","1","","");?>
			</td>
			<td width='1' align='right' style='padding: <?php echo $padding;?>px;'>
				<input type='image' src='<?php print html_get_theme_images_path("button_go.gif");?>' name='action_button' alt='Go'>
			</td>
		</tr>
	</table>
	<?php

	if ($action_variable == true) {
		echo "<input type='hidden' name='action' value='actions'>\n";
	}
}

/*
 * Deprecated functions
 */

function DrawMatrixHeaderItem($matrix_name, $matrix_text_color, $column_span = 1) { ?>
		<td height="1" colspan="<?php print $column_span;?>">
			<strong><font color="#<?php print $matrix_text_color;?>"><?php print $matrix_name;?></font></strong>
		</td>
<?php }

function form_area($text) { ?>
	<tr>
		<td bgcolor="#E1E1E1" class="textArea">
			<?php print $text;?>
		</td>
	</tr>
<?php }

/* draw_navigation_text - determines the top header navigation text for the current page and displays it to
	the browser */
function draw_navigation_text() {
	require_once(CACTI_BASE_PATH . "/lib/sys/http.php");

	$nav_level_cache = (isset($_SESSION["sess_nav_level_cache"]) ? $_SESSION["sess_nav_level_cache"] : array());

	$nav = array(
		"graph_view.php:" => array(
			"title" => _("Graphs"),
			"mapping" => "",
			"url" => "graph_view.php",
			"level" => "0"
			),
		"graph_view.php:tree" => array(
			"title" => _("Tree Mode"),
			"mapping" => "graph_view.php:",
			"url" => "graph_view.php?action=tree",
			"level" => "1"
			),
		"graph_view.php:list" => array(
			"title" => _("List Mode"),
			"mapping" => "graph_view.php:",
			"url" => "graph_view.php?action=list",
			"level" => "1"
			),
		"graph_view.php:preview" => array(
			"title" => _("Preview Mode"),
			"mapping" => "graph_view.php:",
			"url" => "graph_view.php?action=preview",
			"level" => "1"
			),
		"graph.php:" => array(
			"title" => "|current_graph_title|",
			"mapping" => "graph_view.php:,?",
			"level" => "2"
			),
		"graph.php:view" => array(
			"title" => "|current_graph_title|",
			"mapping" => "graph_view.php:,?",
			"level" => "2"
			),
		"graph.php:zoom" => array(
			"title" => _("Zoom"),
			"mapping" => "graph_view.php:,?,graph.php:view",
			"level" => "3"
			),
		"graph.php:properties" => array(
			"title" => _("Properties"),
			"mapping" => "graph_view.php:,?,graph.php:view",
			"level" => "3"
			),
		"graph_settings.php:" => array(
			"title" => _("Settings"),
			"mapping" => "graph_view.php:",
			"level" => "1"
			),
		"index.php:" => array(
			"title" => _("Console"),
			"mapping" => "",
			"url" => "index.php",
			"level" => "0"
			),
		"graphs.php:" => array(
			"title" => _("Graph Management"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"graphs.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,graphs.php:",
			"level" => "2"
			),
		"graphs.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,graphs.php:",
			"level" => "2"
			),
		"graphs_items.php:edit" => array(
			"title" => _("Graph Items"),
			"mapping" => "index.php:,graphs.php:,graphs.php:edit",
			"level" => "3"
			),
		"graphs_new.php:" => array(
			"title" => _("Create New Graphs"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"graphs_new.php:save" => array(
			"title" => _("Create Graphs from Data Query"),
			"mapping" => "index.php:,graphs_new.php:",
			"level" => "2"
			),
		"tree.php:" => array(
			"title" => _("Graph Trees"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"tree.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,tree.php:",
			"level" => "2"
			),
		"tree.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,tree.php:",
			"level" => "2"
			),
		"tree.php:item_edit" => array(
			"title" => _("Graph Tree Items"),
			"mapping" => "index.php:,tree.php:,tree.php:edit",
			"level" => "3"
			),
		"tree.php:item_remove" => array(
			"title" => _("(Remove Item)"),
			"mapping" => "index.php:,tree.php:,tree.php:edit",
			"level" => "3"
			),
		"graph_templates.php:" => array(
			"title" => _("Graph Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"graph_templates.php:sv_add" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,graph_templates.php:",
			"level" => "2"
			),
		"graph_templates.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,graph_templates.php:",
			"level" => "2"
			),
		"graph_templates.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,graph_templates.php:",
			"level" => "2"
			),
		"graph_templates_items.php:edit" => array(
			"title" => _("Graph Template Items"),
			"mapping" => "index.php:,graph_templates.php:,graph_templates.php:edit",
			"level" => "3"
			),
		"graph_templates_inputs.php:edit" => array(
			"title" => _("Graph Item Inputs"),
			"mapping" => "index.php:,graph_templates.php:,graph_templates.php:edit",
			"level" => "3"
			),
		"graph_templates_inputs.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,graph_templates.php:,graph_templates.php:edit",
			"level" => "3"
			),
		"host_templates.php:" => array(
			"title" => _("Host Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"host_templates.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,host_templates.php:",
			"level" => "2"
			),
		"host_templates.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,host_templates.php:",
			"level" => "2"
			),
		"graph_templates.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,graph_templates.php:",
			"level" => "2"
			),
		"data_templates.php:" => array(
			"title" => _("Data Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_templates.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_templates.php:",
			"level" => "2"
			),
		"data_templates.php:sv_add" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_templates.php:",
			"level" => "2"
			),
		"data_templates.php:item_add" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_templates.php:",
			"level" => "2"
			),
		"data_templates.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,data_templates.php:",
			"level" => "2"
			),
		"data_sources.php:" => array(
			"title" => _("Data Sources"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_sources.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_sources.php:",
			"level" => "2"
			),
		"data_sources.php:item_add" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_sources.php:",
			"level" => "2"
			),
		"data_sources.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,data_sources.php:",
			"level" => "2"
			),
		"host.php:" => array(
			"title" => _("Devices"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"host.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,host.php:",
			"level" => "2"
			),
		"host.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,host.php:",
			"level" => "2"
			),
		"rra.php:" => array(
			"title" => _("Round Robin Archives"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"rra.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,rra.php:",
			"level" => "2"
			),
		"rra.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,rra.php:",
			"level" => "2"
			),
		"rra_templates.php:" => array(
			"title" => _("Round Robin Archive Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"rra_templates.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,rra_templates.php:",
			"level" => "2"
			),
		"data_input.php:" => array(
			"title" => _("Data Input Methods"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_input.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_input.php:",
			"level" => "2"
			),
		"data_input.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,data_input.php:",
			"level" => "2"
			),
		"data_input.php:field_edit" => array(
			"title" => _("Data Input Fields"),
			"mapping" => "index.php:,data_input.php:,data_input.php:edit",
			"level" => "3"
			),
		"data_input.php:field_remove" => array(
			"title" => _("(Remove Item)"),
			"mapping" => "index.php:,data_input.php:,data_input.php:edit",
			"level" => "3"
			),
		"data_queries.php:" => array(
			"title" => _("Data Queries"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_queries.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_queries.php:",
			"level" => "2"
			),
		"data_queries.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,data_queries.php:",
			"level" => "2"
			),
		"data_queries.php:item_edit" => array(
			"title" => _("Associated Graph Templates"),
			"mapping" => "index.php:,data_queries.php:,data_queries.php:edit",
			"level" => "3"
			),
		"data_queries.php:item_remove" => array(
			"title" => _("(Remove Item)"),
			"mapping" => "index.php:,data_queries.php:,data_queries.php:edit",
			"level" => "3"
			),
		"utilities.php:" => array(
			"title" => _("Utilities"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"utilities.php:view_poller_cache" => array(
			"title" => _("View Poller Cache"),
			"mapping" => "index.php:,utilities.php:",
			"level" => "2"
			),
		"utilities.php:view_snmp_cache" => array(
			"title" => _("View SNMP Cache"),
			"mapping" => "index.php:,utilities.php:",
			"level" => "2"
			),
		"utilities.php:rebuild_poller_cache" => array(
			"title" => _("Rebuild Poller Cache"),
			"mapping" => "index.php:,utilities.php:",
			"level" => "2"
			),
		"utilities.php:view_logs" => array(
			"title" => _("View Cacti Logs"),
			"mapping" => "index.php:,utilities.php:",
			"level" => "2"
			),
		"utilities.php:clear_syslog" => array(
			"title" => _("Clear Cacti Syslog"),
			"mapping" => "index.php:,utilities.php:",
			"level" => "2"
			),
		"settings.php:" => array(
			"title" => _("Cacti Settings"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"user_admin.php:" => array(
			"title" => _("User Management"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"user_admin.php:actions" => array(
			"title" => _("User Management"),
			"mapping" => "index.php:",
			"level" => "2"
			),
		"user_admin.php:user_edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"user_admin.php:user_remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"user_admin.php:graph_perms_edit" => array(
			"title" => _("Edit (Graph Permissions)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"user_admin.php:user_realms_edit" => array(
			"title" => _("Edit (Realm Permissions)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"user_admin.php:graph_settings_edit" => array(
			"title" => _("Edit (Graph Settings)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"user_admin.php:user_settings_edit" => array(
			"title" => _("Edit (User Settings)"),
			"mapping" => "index.php:,user_admin.php:",
			"level" => "2"
			),
		"about.php:" => array(
			"title" => _("About Cacti"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"templates_export.php:" => array(
			"title" => _("Export Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"templates_export.php:save" => array(
			"title" => _("Export Results"),
			"mapping" => "index.php:,templates_export.php:",
			"level" => "2"
			),
		"templates_import.php:" => array(
			"title" => _("Import Templates"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_pollers.php:" => array(
			"title" => _("Pollers"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"data_pollers.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,data_pollers.php:",
			"level" => "2"
			),
		"data_pollers.php:actions" => array(
			"title" => _("Actions"),
			"mapping" => "index.php:,data_pollers.php:",
			"level" => "2"
			),
		"user_changepassword.php:" => array(
			"title" => _("Change Password"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"user_changepassword.php:save" => array(
			"title" => _("Change Password"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"user_settings.php:" => array(
			"title" => _("User Settings"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"presets.php:" => array(
			"title" => _("Data Presets"),
			"mapping" => "index.php:",
			"url" => "presets.php",
			"level" => "1"
			),
		"presets.php:view_cdef" => array(
			"title" => _("CDEFs"),
			"mapping" => "index.php:,presets.php:",
			"level" => "2"
			),
		"presets.php:view_color" => array(
			"title" => _("Colors"),
			"mapping" => "index.php:,presets.php:",
			"level" => "2"
			),
		"presets.php:view_gprint" => array(
			"title" => _("GPRINTs"),
			"mapping" => "index.php:,presets.php:",
			"level" => "2"
			),
		"presets_cdef.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,presets.php:,presets.php:view_cdef",
			"level" => "3"
			),
		"presets_cdef.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,presets.php:,presets.php:view_cdef",
			"level" => "3"
			),
		"presets_cdef.php:item_edit" => array(
			"title" => _("CDEF Items"),
			"mapping" => "index.php:,presets.php:,presets.php:view_cdef,presets_cdef.php:edit",
			"level" => "4"
			),
		"presets_color.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,presets.php:,presets.php:view_color",
			"level" => "3"
			),
		"presets_gprint.php:edit" => array(
			"title" => _("(Edit)"),
			"mapping" => "index.php:,presets.php:,presets.php:view_gprint",
			"level" => "3"
			),
		"presets_gprint.php:remove" => array(
			"title" => _("(Remove)"),
			"mapping" => "index.php:,presets.php:,presets.php:view_gprint",
			"level" => "3"
			),
		"logs.php:" => array(
			"title" => _("Log Management"),
			"mapping" => "index.php:",
			"level" => "1"
			),
		"logs.php:view" => array(
			"title" => _("View"),
			"mapping" => "index.php:,logs.php:",
			"level" => "2"
			),
		"plugins.php:" => array(
			"title" => _("Plugins"),
			"mapping" => "index.php:",
			"level" => "1"
			)
		);

	$current_page = basename($_SERVER["PHP_SELF"]);
	$current_action = (isset($_REQUEST["action"]) ? $_REQUEST["action"] : "");

	/* find the current page in the big array */
	$current_array = $nav{$current_page . ":" . $current_action};
	$current_mappings = explode(",", $current_array["mapping"]);
	$current_nav = "";

	/* resolve all mappings to build the navigation string */
	for ($i=0; ($i<count($current_mappings)); $i++) {
		if (empty($current_mappings[$i])) { continue; }

		if  (isset($nav{$current_mappings[$i]}["url"])) {
			/* always use the default for level == 0 */
			$url = $nav{$current_mappings[$i]}["url"];
		}elseif (!empty($nav_level_cache{$i}["url"])) {
			/* found a match in the url cache for this level */
			$url = $nav_level_cache{$i}["url"];

		}else{
			/* default to no url */
			$url = "";
		}

		if ($current_mappings[$i] == "?") {
			/* '?' tells us to pull title from the cache at this level */
			if (isset($nav_level_cache{$i})) {
				$current_nav .= (empty($url) ? "" : "<a href='$url'>") . resolve_navigation_variables($nav{$nav_level_cache{$i}["id"]}["title"]) . (empty($url) ? "" : "</a>") . " -> ";
			}
		}else{
			/* there is no '?' - pull from the above array */
			$current_nav .= (empty($url) ? "" : "<a href='$url'>") . resolve_navigation_variables($nav{$current_mappings[$i]}["title"]) . (empty($url) ? "" : "</a>") . " -> ";
		}
	}

	$current_nav .= resolve_navigation_variables($current_array["title"]);

	/* keep a cache for each level we encounter */
	$nav_level_cache{$current_array["level"]} = array("id" => $current_page . ":" . $current_action, "url" => get_browser_query_string());
	$_SESSION["sess_nav_level_cache"] = $nav_level_cache;

	print $current_nav;
}

/* resolve_navigation_variables - substitute any variables contained in the navigation text
   @arg $text - the text to substitute in
   @returns - the original navigation text with all substitutions made */
function resolve_navigation_variables($text) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	if (preg_match_all("/\|([a-zA-Z0-9_]+)\|/", $text, $matches)) {
		for ($i=0; $i<count($matches[1]); $i++) {
			switch ($matches[1][$i]) {
			case 'current_graph_title':
				$text = str_replace("|" . $matches[1][$i] . "|", get_graph_title($_GET["graph_id"]), $text);
				break;
			}
		}
	}

	return $text;
}

?>
