<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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
     corner of the box ("" for no 'Add' link)
   @arg $table_id - the table id to make the table addressable by jQuery's table DND plugin */
function html_start_box($title, $width, $background_color, $cell_padding, $align, $add_text = "", $collapsing = false, $table_id = "") {
	global $colors, $config;
	static $form_number = 0;
	$form_number++;

	$function_name = "addObject" . $form_number . "()";

	if ($add_text != "") {
		?>
		<script type="text/javascript">
		<!--
		function <?php print $function_name;?> {
			document.location = '<?php echo $add_text;?>';
			return false;
		}
		-->
		</script>
		<?php
	}

	$temp_string = str_replace("strong", "", $title);
	if (strpos($temp_string, "[")) {
		$temp_string = substr($temp_string, 0, strpos($temp_string, "[")-1);
	}

	if ($title != "") {
		$item_id = clean_up_name($temp_string);
	}else{
		$item_id = "item_" . rand(255, 65535);
	}

	if ($collapsing) {
		$ani  = "style=\"cursor:pointer;\" onClick=\"htmlStartBoxFilterChange('" . $item_id . "')\"";
		$ani3 = "onClick=\"htmlStartBoxFilterChange('" . $item_id . "')\"";
	}else{
		$ani  = "";
		$ani3 = "";
	}

	$table_id = ($table_id != '') ? "id=\"$table_id\"" : "";

	?>
		<script type="text/javascript">
		<!--
			registerOnLoadFunction("<?php print basename($_SERVER['PHP_SELF']);?>", "htmlStartBoxFilterChange('<?php print $item_id;?>', true)");
		-->
		</script>
		<table class="startBoxHeader" align="<?php print $align;?>" width="<?php print $width;?>" cellpadding=0 cellspacing=0>
			<?php if ($title != "") {?><tr class="rowHeader">
				<td colspan="100">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<table width="100%" cellpadding="0" cellspacing="0" <?php print $ani;?>>
									<tr>
										<?php if ($collapsing) {?><td class="textHeaderDark" width="9">
											<img id="<?php print $item_id . '_twisty';?>" src="<?php print URL_PATH; ?>images/tw_open.gif" alt="Filter" align="middle">
										</td><?php } ?>
										<td onMouseDown='return false' class="textHeaderDark"><?php print $title;?>
										</td>
									</tr>
								</table>
							</td><?php if ($add_text != "") {?>
							<td style='width:1px' class="textHeaderDark" align="right">
								<input type='button' onClick='<?php print $function_name;?>' style='font-size:10px;' value='Add'>
							</td><?php }?>
						</tr>
					</table>
				</td>
			</tr>
			<?php }?><tr style='border: 0px;' id='<?php print $item_id;?>'>
				<td>
					<table <?php print $table_id;?> width="100%" cellpadding="<?php print $cell_padding;?>" cellspacing="0" style='border-width:0px;'><?php
}

function html_start_box_dq($query_name, $query_id, $host_id, $colspan, $width, $background_color, $cell_padding, $align) {
	global $colors;

	$temp_string = str_replace("strong", "", $query_name);
	if (strpos($temp_string, "[")) {
		$temp_string = substr($temp_string, 0, strpos($temp_string, "[")-1);
	}

	if ($query_name != "") {
		$item_id = clean_up_name($temp_string);
	}else{
		$item_id = "item_" . rand(255, 65535);
	}

	?>
		<table class='startBoxHeader' cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr class='rowHeader'>
				<td style='padding:0px 5px 0px 5px;' colspan='<?php print $colspan+1;?>'>
					<table cellspacing='0' cellpadding='0' width='100%' >
						<tr>
							<td class='textHeaderDark'>
								<strong><?php print __("Data Query");?></strong> [<?php print $query_name; ?>]
							</td>
							<td align='right' nowrap>
								<a href='graphs_new.php?action=query_reload&amp;id=<?php print $query_id;?>&amp;host_id=<?php print $host_id;?>'><img class='buttonSmall' src='images/reload_icon_small.gif' alt='<?php print __("Reload");?>' title='<?php print __("Reload Associated Query");?>' align='middle'></a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr style='border: 0px;' id='<?php print $item_id;?>'>
				<td colspan='<?php print $colspan+1;?>'>
					<table width="100%" cellpadding="<?php print $cell_padding;?>" cellspacing="0" style='border-width:0px;'><?php
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

/* html_graph_start_box - draws the start of an HTML graph view box
   @arg $cellpadding - the table cell padding for the box
   @arg $leading_br (bool) - whether to draw a leader <br> tag before the start of the table */
function html_graph_start_box($cellpadding = 3, $leading_br = true) {
	if ($leading_br == true) {
		print "<br>\n";
	}

	print "\t<table width='100%' style='border: 1px solid #6A6A6A;padding:0px;' align='center' cellspacing=0 cellpadding='$cellpadding'>\n";
}

/* html_graph_end_box - draws the end of an HTML graph view box */
function html_graph_end_box() {
	print "</table>\n";
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
	global $config;

	$i = 0;
	if (sizeof($graph_array) > 0) {
		if ($header != "") {
			print $header;
		}

		foreach ($graph_array as $graph) {
			if (isset($graph["graph_template_name"])) {
				if (isset($prev_graph_template_name)) {
					if ($prev_graph_template_name != $graph["graph_template_name"]) {
						$print  = true;
						$prev_graph_template_name = $graph["graph_template_name"];
					}else{
						$print = false;
					}
				}else{
					$print  = true;
					$prev_graph_template_name = $graph["graph_template_name"];
				}

				if ($print) {
					print "\t\t<tr class='rowSubHeader'>
						<td colspan='3' class='textHeaderDark'>
							<strong>" . __("Graph Template:") . "</strong> " . $graph["graph_template_name"] . "
						</td>
					</tr>";
				}
			}elseif (isset($graph["data_query_name"])) {
				if (isset($prev_data_query_name)) {
					if ($prev_data_query_name != $graph["data_query_name"]) {
						$print  = true;
						$prev_data_query_name = $graph["data_query_name"];
					}else{
						$print = false;
					}
				}else{
					$print  = true;
					$prev_data_query_name = $graph["data_query_name"];
				}

				if ($print) {
					print "\t\t\t<tr class='rowSubHeaderAlt'><td colspan='3' class='textHeaderDark'><strong>" . __("Data Query:") . "</strong> " . $graph["data_query_name"] . "</td></tr>";
				}
				print "<tr>
					<td colspan='3' class='textHeaderDark'>
						" . $graph["sort_field_value"]. "
					</td>
				</tr>";
			}

			?>
			<tr align='center' style='background-color: #<?php print ($i % 2 == 0 ? "f9f9f9" : "ffffff");?>;'>
				<td align='center'>
					<table align='center' cellpadding='0'>
						<tr>
							<td align='center'>
								<div style="min-height: <?php echo (1.6 * read_config_option("export_default_height")) . "px"?>;"><a href='graph.php?action=view&local_graph_id=<?php print $graph["local_graph_id"];?>&rra_id=all'><img class='graphimage' id='graph_<?php print $graph["local_graph_id"] ?>' src='<?php print htmlspecialchars("graph_image.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0" . (($extra_url_args == "") ? "" : "&$extra_url_args"));?>' border='0' alt='<?php print $graph["title_cache"];?>'></a></div>
								<?php print (read_graph_config_option("show_graph_title") == "on" ? "<p style='font-size: 10;' align='center'><strong>" . $graph["title_cache"] . "</strong></p>" : "");?>
							</td>
							<td valign='top' style='align: left; padding: 3px;' class='noprint'>
								<a href='<?php print htmlspecialchars("graph.php?action=zoom&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_zoom.gif' alt='<?php print __("Zoom Graph");?>' title='<?php print __("Zoom Graph");?>' style='border-width:0px;padding:3px;'></a><br>
								<a href='<?php print htmlspecialchars("graph_xport.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_query.png' alt='<?php print __("CSV Export");?>' title='<?php print __("CSV Export");?>' style='border-width:0px;padding: 3px;'></a><br>
								<a href='<?php print htmlspecialchars("graph.php?action=properties&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_properties.gif' alt='<?php print __("Properties");?>' title='<?php print __("Graph Source/Properties");?>' style='border-width:0px;padding:3px;'></a><br>
								<a href='#page_top'><img src='images/graph_page_top.gif' alt='<?php print __("Page Top");?>' title='<?php print __("Page Top");?>' style='border-width:0px;padding:3px;'></a><br>
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
	$i = 0; $k = 0; $j = 0;

	$num_graphs = sizeof($graph_array);

	if ($num_graphs > 0) {
		if ($header != "") {
			print $header;
		}

		$start = true;
		foreach ($graph_array as $graph) {
			if (isset($graph["graph_template_name"])) {
				if (isset($prev_graph_template_name)) {
					if ($prev_graph_template_name != $graph["graph_template_name"]) {
						$print  = true;
						$prev_graph_template_name = $graph["graph_template_name"];
					}else{
						$print = false;
					}
				}else{
					$print  = true;
					$prev_graph_template_name = $graph["graph_template_name"];
				}

				if ($print) {
					if (!$start) {
						while($i % read_graph_config_option("num_columns") != 0) {
							print "\t\t\t<td align='center' width='" . ceil(100 / read_graph_config_option("num_columns")) . "%'></td>";
							$i++;
						}
						print "\t\t</tr>\t";
					}

					print "\t\t<tr class='rowSubHeader'>
						<td colspan='" . read_graph_config_option("num_columns") . "' class='textHeaderDark'>
							<strong>" . __("Graph Template:") . "</strong> " . $graph["graph_template_name"] . "
						</td>
					</tr>";
					$i = 0;
				}
			}elseif (isset($graph["data_query_name"])) {
				if (isset($prev_data_query_name)) {
					if ($prev_data_query_name != $graph["data_query_name"]) {
						$print  = true;
						$prev_data_query_name = $graph["data_query_name"];
					}else{
						$print = false;
					}
				}else{
					$print  = true;
					$prev_data_query_name = $graph["data_query_name"];
				}

				if ($print) {
					if (!$start) {
						while($i % read_graph_config_option("num_columns") != 0) {
							print "<td align='center' width='" . ceil(100 / read_graph_config_option("num_columns")) . "%'></td>";
							$i++;
						}

						print "</tr>";
					}

					print "\t\t\t<tr style='rowSubHeaderAlt'>
							<td colspan='" . read_graph_config_option("num_columns") . "' class='textHeaderDark'><strong>" . __("Data Query:") . "</strong> " . $graph["data_query_name"] . "</td>
						</tr>";
					$i = 0;
				}
				print "<tr style='background-color:#a9b7cb;'>
					<td colspan='" . read_graph_config_option("num_columns") . "' class='textHeaderDark'>
						" . $graph["sort_field_value"]. "
					</td>
				</tr>";
			}

			if ($i == 0) {
				print "<tr style='background-color: #" . ($j % 2 == 0 ? "F2F2F2" : "FFFFFF") . ";'>";
				$start = false;
			}

			?>
			<td align='center' width='<?php print ceil(100 / read_graph_config_option("num_columns"));?>%'>
				<table align='center' cellpadding='0'>
					<tr>
						<td align='center'>
							<a href='<?php print htmlspecialchars("graph.php?action=view&rra_id=all&local_graph_id=" . $graph["local_graph_id"]);?>'><img class='graphimage' id='graph_<?php print $graph["local_graph_id"] ?>' src='<?php print htmlspecialchars("graph_image.php?local_graph_id=". $graph["local_graph_id"] . "&rra_id=0&graph_height=" . read_graph_config_option("default_height") . "&graph_width=" . read_graph_config_option("default_width") . "&graph_nolegend=true" . (($extra_url_args == "") ? "" : "&$extra_url_args"));?>' border='0' alt='<?php print $graph["title_cache"];?>'></a>
							<?php print (read_graph_config_option("show_graph_title") == "on" ? "<p style='font-size: 10;' align='center'><strong>" . $graph["title_cache"] . "</strong></p>" : "");?>
						</td>
						<td valign='top' style='align: left; padding: 3px;'>
							<a href='<?php print htmlspecialchars("graph.php?action=zoom&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_zoom.gif' alt='<?php print __("Zoom Graph");?>' title='<?php print __("Zoom Graph");?>' style='border-width:0px;padding:3px;'></a><br>
							<a href='<?php print htmlspecialchars("graph_xport.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_query.png' alt='<?php print __("CSV Export");?>' title='<?php print __("CSV Export");?>' style='border-width:0px;padding:3px;'></a><br>
							<a href='<?php print htmlspecialchars("graph.php?action=properties&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='images/graph_properties.gif' alt='<?php print __("Graph Source/Properties");?>' title='<?php print __("Graph Source/Properties");?>' style='border-width:0px;padding:3px;'></a><br>
							<a href='#page_top'><img src='images/graph_page_top.gif' alt='<?php print __("Page Top");?>' title='<?php print __("Page Top");?>' style='border-width:0px;padding:3px;'></a><br>
						</td>
					</tr>
				</table>
			</td>
			<?php

			$i++;
			$k++;

			if (($i % read_graph_config_option("num_columns") == 0) && ($k < $num_graphs)) {
				$i=0;
				$j++;
				print "</tr>\n";
				$start = true;
			}
		}

		if (!$start) {
			while($i % read_graph_config_option("num_columns") != 0) {
				print "<td align='center' width='" . ceil(100 / read_graph_config_option("num_columns")) . "%'></td>";
				$i++;
			}

			print "</tr>\n";
		}
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
	if (substr_count($nav_url, "?")) {
		$nav_url .= "&";
	}else{
		$nav_url .= "?";
	}
	?>
	<tr class='rowHeader noprint'>
		<td colspan='<?php print $colspan;?>'>
			<table width='100%' cellspacing='0' cellpadding='0' border='0' style='border-width:0px;'>
				<tr>
					<td align='left' class='textHeaderDark' width='15%'>
						<?php if ($current_page > 1) {
							print "<strong><a class='linkOverDark' href='" . htmlspecialchars(str_replace("<PAGE>", ($current_page-1), $nav_url)) . "'>&lt;&lt;&nbsp;" . __("Previous") . "</a></strong>";
						} ?>
					</td>
					<td align='center' class='textHeaderDark' width='70%'>
						<?php print __("Showing Rows");?> <?php print (($rows_per_page*($current_page-1))+1);?> <?php print __("to");?> <?php print ((($total_rows < $rows_per_page) || ($total_rows < ($rows_per_page*$current_page))) ? $total_rows : ($rows_per_page*$current_page));?> <?php print __("of");?> <?php print $total_rows;?>
					</td>
					<td align='right' class='textHeaderDark' width='15%'>
						<?php if (($current_page * $rows_per_page) < $total_rows) {
							print "<strong><a class='linkOverDark' href='" . htmlspecialchars(str_replace("<PAGE>", ($current_page+1), $nav_url)) . "'>" . __("Next") . "&gt;&gt;</a></strong>";
						} ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

/* html_header_sort - draws a header row suitable for display inside of a box element.  When
     a user selects a column header, the collback function "filename" will be called to handle
     the sort the column and display the altered results.
   @arg $header_items - an array containing a list of column items to display.  The
        format is similar to the html_header, with the exception that it has three
        dimensions associated with each element (db_column => display_text, default_sort_order)
   @arg $sort_column - the value of current sort column.
   @arg $sort_direction - the value the current sort direction.  The actual sort direction
        will be opposite this direction if the user selects the same named column.
   @arg $last_item_colspan - the TD 'colspan' to apply to the last cell in the row */
function html_header_sort($header_items, $sort_column, $sort_direction, $last_item_colspan = 1) {
	global $colors;
	static $rand_id = 0;

	/* reverse the sort direction */
	if ($sort_direction == "ASC") {
		$new_sort_direction = "DESC";
		$selected_sort_class = "sort_asc";
	}else{
		$new_sort_direction = "ASC";
		$selected_sort_class = "sort_desc";
	}

	print "\t\t<table class='resizable startBoxHeader' cellpadding='3' cellspacing='0' width='100%'><tr class='rowSubHeader'>\n";

	$pathname = html_get_php_pathname();

	foreach($header_items as $db_column => $display_array) {
		/* by default, you will always sort ascending, with the exception of an already sorted column */
		if ($sort_column == $db_column) {
			$direction    = $new_sort_direction;
			$display_text = $display_array[0];
			$sort_class   = $selected_sort_class;
		}else{
			$display_text = $display_array[0];
			$direction    = $display_array[1];
			$sort_class   = "";
		}


		if (($db_column == "") || (substr_count($db_column, "nosort"))) {
			$width = html_get_column_width($pathname, "hhs_$rand_id");

			print "\t\t\t<th nowrap style='width:$width;white-space:nowrap;' id='hhs_$rand_id'" . ((($rand_id+1) == count($header_items)) ? "colspan='$last_item_colspan' " : "") . " onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' class='textSubHeaderDark'>" . $display_text . "</th>\n";

			$rand_id++;
		}else{
			$width = html_get_column_width($pathname, $db_column);

			print "\t\t\t<th nowrap style='width:$width;white-space:nowrap;' id='$db_column'" . ((($rand_id+1) == count($header_items)) ? "colspan='$last_item_colspan' " : "") . " onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' class='textSubHeaderDark'>";
			print "\n\t\t\t\t<a class='$sort_class' style='display:block;' href='" . htmlspecialchars($_SERVER["PHP_SELF"] . "?sort_column=" . $db_column . "&sort_direction=" . $direction) . "'>" . $display_text . "</a>";
			print "\n\t\t\t</th>\n";
		}
	}

	print "\t\t</tr>\n";
}

/* html_header_sort_checkbox - draws a header row with a 'select all' checkbox in the last cell
     suitable for display inside of a box element.  When a user selects a column header,
     the collback function "filename" will be called to handle the sort the column and display
     the altered results.
   @arg $header_items - an array containing a list of column items to display.  The
        format is similar to the html_header, with the exception that it has three
        dimensions associated with each element (db_column => display_text, default_sort_order)
   @arg $sort_column - the value of current sort column.
   @arg $sort_direction - the value the current sort direction.  The actual sort direction
        will be opposite this direction if the user selects the same named column.
   @arg $form_action - the url to post the 'select all' form to */
function html_header_sort_checkbox($header_items, $sort_column, $sort_direction, $form_action = "") {
	global $colors;
	static $rand_id = 0;

	/* reverse the sort direction */
	if ($sort_direction == "ASC") {
		$new_sort_direction = "DESC";
		$selected_sort_class = "sort_asc";
	}else{
		$new_sort_direction = "ASC";
		$selected_sort_class = "sort_desc";
	}

	/* default to the 'current' file */
	if ($form_action == "") { $form_action = basename($_SERVER["PHP_SELF"]); }

	print "<form name='chk' method='post' action='$form_action'>\n";	# properly place form outside table
	print "\t<table class='resizable startBoxHeader' cellpadding='3' cellspacing='0' width='100%'>\n";
	print "\t\t<tr class='rowSubHeader'>\n";

	$pathname = html_get_php_pathname();

	foreach($header_items as $db_column => $display_array) {
		/* by default, you will always sort ascending, with the exception of an already sorted column */
		if ($sort_column == $db_column) {
			$direction    = $new_sort_direction;
			$display_text = $display_array[0];
			$sort_class   = $selected_sort_class;
		}else{
			$display_text = $display_array[0];
			$direction    = $display_array[1];
			$sort_class   = "";
		}


		if (($db_column == "") || (substr_count($db_column, "nosort"))) {
			$width = html_get_column_width($pathname, "hhscrand_$rand_id");

			print "\t\t\t<th nowrap style='width:$width;white-space:nowrap;' id='hhsc_$rand_id' onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' class='textSubHeaderDark'>" . $display_text . "</th>\n";

			$rand_id++;
		}else{
			$width = html_get_column_width($pathname, $db_column);

			print "\t\t\t<th nowrap style='width:$width;white-space:nowrap;' id='$db_column' onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' class='textSubHeaderDark'>";
			print "\n\t\t\t\t<a class='$sort_class' style='display:block;' href='" . htmlspecialchars($_SERVER["PHP_SELF"] . "?sort_column=" . $db_column . "&sort_direction=" . $direction) . "'>" . $display_text . "</a>";
			print "\n\t\t\t</th>\n";
		}
	}

	print "\t\t\t<th id='hhsc_$rand_id' style='width: 14px;' class='textSubHeaderDark'><input type='checkbox' style='width: 14px; margin: 0px;' name='all' title='Select All' onClick='SelectAll(\"chk_\",this.checked)'></th>\n";
	print "\t\t</tr>\n";
}

/* html_header - draws a header row suitable for display inside of a box element
   @arg $header_items - an array containing a list of items to be included in the header
   @arg $last_item_colspan - the TD 'colspan' to apply to the last cell in the row */
function html_header($header_items, $last_item_colspan = 1, $resizable = true, $table_id = '') {
	global $colors;
	static $rand_id = 0;

	$table_id = ($table_id != '') ? "id=\"$table_id\"" : "";

	if ($resizable) {
		$pathname = html_get_php_pathname();

		print "\t\t<table $table_id class='resizable startBoxHeader' cellpadding='3' cellspacing='0' width='100%'><tr class='rowSubHeader nodrag nodrop'>\n";
	}else{
		print "\t\t<table $table_id cellpadding='3' cellspacing='0' width='100%'><tr class='rowSubHeader nodrag nodrop'>\n";
	}

	for ($i=0; $i<count($header_items); $i++) {
		if ($resizable) {
			$width = html_get_column_width($pathname, "hh_$rand_id");

			print "\t\t\t<th id='hh_$rand_id' style='width: $width;' onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' " . ((($i+1) == count($header_items)) ? "colspan='$last_item_colspan' " : "") . " class='textSubHeaderDark'>" . $header_items[$i] . "</th>\n";
		}else{
			print "\t\t\t<th id='hh_$rand_id' " . ((($i+1) == count($header_items)) ? "colspan='$last_item_colspan' " : "") . " class='textSubHeaderDark'>" . $header_items[$i] . "</th>\n";
		}
		$rand_id++;
	}

	print "\t\t</tr>\n";
}

/* html_header_checkbox - draws a header row with a 'select all' checkbox in the last cell
     suitable for display inside of a box element
   @arg $header_items - an array containing a list of items to be included in the header
   @arg $form_action - the url to post the 'select all' form to */
function html_header_checkbox($header_items, $form_action = "", $resizable = false) {
	global $colors;
	static $rand_id = 0;

	/* default to the 'current' file */
	if ($form_action == "") { $form_action = basename($_SERVER["PHP_SELF"]); }

	if ($resizable) {
		$pathname = html_get_php_pathname();

		print "\t\t<table class='resizable' cellpadding='3' cellspacing='0' width='100%'><tr class='rowSubHeader'>\n";
	}else{
		print "\t\t<table cellpadding='3' cellspacing='0' width='100%'><tr class='rowSubHeader'>\n";
	}

	for ($i=0; $i<count($header_items); $i++) {
		if ($resizable) {
			$width = html_get_column_width($pathname, "hhc_$rand_id");

			print "\t\t\t<th id='hhc_$rand_id' style='width: $width;' onMouseDown='return false' onMousemove='doColResize(this,event)' onMouseover='doColResize(this,event)' onMouseup='doneColResize()' class='textSubHeaderDark'>" . $header_items[$i] . "</th>\n";
		}else{
			print "\t\t\t<th id='hhc_$rand_id' class='textSubHeaderDark'>" . $header_items[$i] . "</th>\n";
		}
		$rand_id++;
	}

	print "\t\t\t<th id='hhc_$rand_id' style='width: 14px' class='textSubHeaderDark'><input type='checkbox' style='margin: 0px;' name='all' title='Select All' onClick='SelectAll(\"chk_\",this.checked)'></th>\n<form name='chk' method='post' action='$form_action'>\n";
	print "\t\t</tr>\n";
}

/* html_create_list - draws the items for an html dropdown given an array of data
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
function html_create_list($form_data, $column_display, $column_id, $form_previous_value) {
	if (empty($column_display)) {
		foreach (array_keys($form_data) as $id) {
			print "\t\t\t\t\t\t\t<option value='" . $id . "'";

			if ($form_previous_value == $id) {
			print " selected";
			}

			print ">" . title_trim(null_out_substitutions($form_data[$id]), 75) . "</option>\n";
		}
	}else{
		if (sizeof($form_data) > 0) {
			foreach ($form_data as $row) {
				print "\t\t\t\t\t\t\t<option value='$row[$column_id]'";

				if ($form_previous_value == $row[$column_id]) {
					print " selected";
				}

				if (isset($row["host_id"])) {
					print ">" . title_trim($row[$column_display], 75) . "</option>\n";
				}else{
					print ">" . title_trim(null_out_substitutions($row[$column_display]), 75) . "</option>\n";
				}
			}
		}
	}
}

/* html_split_string - takes a string and breaks it into a number of <br> separated segments
   @arg $string - string to be modified and returned
   @arg $length - the maximal string length to split to
   @arg $forgiveness - the maximum number of characters to walk back from to determine
         the correct break location.
   @returns $new_string - the modified string to be returned. */
function html_split_string($string, $length = 70, $forgiveness = 10) {
	$new_string = "";
	$j    = 0;
	$done = false;

	while (!$done) {
		if (strlen($string) > $length) {
			for($i = 0; $i < $forgiveness; $i++) {
				if (substr($string, $length-$i, 1) == " ") {
					$new_string .= substr($string, 0, $length-$i) . "<br>";

					break;
				}
			}

			$string = substr($string, $length-$i);
		}else{
			$new_string .= $string;
			$done        = true;
		}

		$j++;
		if ($j > 4) break;
	}

	return $new_string;
}

/* html_create_nav - creates page select navigation html
 * 					creates a table inside of a row
   @arg $current_page - the current page displayed
   @arg $max_pages - the maxium number of pages to show on a page
   @arg $rows_per_page - the number of rows to display per page
   @arg $total_rows - the total number of rows that can be displayed
   @arg $columns - the total number of columns on this page
   @arg $base_url - the url to navigate to
   @arg $page_var - the request variable to look for the page number
   @arg $url_page_select - the page list to display */
function html_create_nav($current_page, $max_pages, $rows_per_page, $total_rows, $columns, $base_url, $page_var = "page") {
	if (substr_count($base_url, "?")) {
		$base_url .= "&";
	}else{
		$base_url .= "?";
	}

	$url_page_select = get_page_list($current_page, $max_pages, $rows_per_page, $total_rows, $base_url, $page_var);

	$nav = "
		<tr class='rowHeader'>
			<td colspan='$columns'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark' width='15%'>";
							if ($current_page > 1) {
								$nav .= "<strong>";
								$nav .= "<a class='linkOverDark' href='" . $base_url . $page_var . "=" . ($current_page-1) . "'>";
								$nav .= "&lt;&lt;&nbsp;" . __("Previous");
								$nav .= "</a></strong>";
							}
							$nav .= "
						</td>\n
						<td align='center' class='textHeaderDark' width='70%'>
							" . __("Showing Rows") . " " . (($rows_per_page*($current_page-1))+1) . " " . __("to") . " " . ((($total_rows < $rows_per_page) || ($total_rows < ($rows_per_page*$current_page))) ? $total_rows : ($rows_per_page*$current_page)) . " " . __("of") . " $total_rows [$url_page_select]
						</td>\n
						<td align='right' class='textHeaderDark' width='15%'>";
							if (($current_page * $rows_per_page) < $total_rows) {
								$nav .= "<strong>";
								$nav .= "<a class='linkOverDark' href='" . $base_url . $page_var . "=" . ($current_page+1) . "'>";
								$nav .= __("Next") . " &gt;&gt;";
								$nav .= "</a></strong>";
							}
							$nav .= "
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	return $nav;
}

/* draw_graph_items_list - draws a nicely formatted list of graph items for display
     on an edit form
   @arg $item_list - an array representing the list of graph items. this array should
     come directly from the output of db_fetch_assoc()
   @arg $filename - the filename to use when referencing any external url
   @arg $url_data - any extra GET url information to pass on when referencing any
     external url
   @arg $disable_controls - whether to hide all edit/delete functionality on this form */
function draw_graph_items_list($item_list, $filename, $url_data, $disable_controls) {
	global $colors, $config;

	include(CACTI_BASE_PATH . "/include/global_arrays.php");

	$header_items = array(__("Graph Item"), __("Data Source"), __("Graph Item Type"), __("CF Type"), __("CDEF"), __("GPRINT Type"), __("Item Color"));
	$last_item_colspan = 3;

	print "<tr><td>";
	html_header($header_items, $last_item_colspan, true, 'graph_item');

	$group_counter = 0; $_graph_type_name = ""; $i = 0;
	$alternate_color_1 = $colors["alternate"]; $alternate_color_2 = $colors["alternate"];

	$i = 0;
	if (sizeof($item_list) > 0) {
	foreach ($item_list as $item) {
		/* graph grouping display logic */
		$this_row_style = ""; $use_custom_row_color = false; $hard_return = "";

		if ($graph_item_types{$item["graph_type_id"]} != "GPRINT") {
			$this_row_style = "font-weight: bold;"; $use_custom_row_color = true;

			if ($group_counter % 2 == 0) {
				$alternate_color_1 = "EEEEEE";
				$alternate_color_2 = "EEEEEE";
				$custom_row_color = "D5D5D5";
			}else{
				$alternate_color_1 = $colors["alternate"];
				$alternate_color_2 = $colors["alternate"];
				$custom_row_color = "D2D6E7";
			}

			$group_counter++;
		}

		$_graph_type_name = $graph_item_types{$item["graph_type_id"]};

		/* alternating row color */
		if ($use_custom_row_color == false) {
#			form_alternate_row_color();
			form_alternate_row_color($item["id"], true);
		}else{
			print "<tr id='row_".$item["id"]."' bgcolor='#$custom_row_color'>";
		}

		print "<td>";
		if ($disable_controls == false) { print "<a href='" . htmlspecialchars("$filename?action=item_edit&id=" . $item["id"] . "&$url_data") ."'>"; }
		print "<strong>Item # " . ($i+1) . "</strong>";
		if ($disable_controls == false) { print "</a>"; }
		print "</td>\n";

		if (empty($item["data_source_name"])) { $item["data_source_name"] = __("No Task"); }

		switch (true) {
		case ereg("(AREA|STACK|GPRINT|LINE[123])", $_graph_type_name):
			$matrix_title = "(" . $item["data_source_name"] . "): " . $item["text_format"];
			break;
		case ereg("(HRULE)", $_graph_type_name):
			$matrix_title = "HRULE: " . $item["value"];
			break;
		case ereg("(VRULE)", $_graph_type_name):
			$matrix_title = "VRULE: " . $item["value"];
			break;
		case ereg("(COMMENT)", $_graph_type_name):
			$matrix_title = "COMMENT: " . $item["text_format"];
			break;
		}

		if ($item["hard_return"] == "on") {
			$hard_return = "<strong><font color=\"#FF0000\">&lt;HR&gt;</font></strong>";
		}

		print "<td style='$this_row_style'>" . htmlspecialchars($matrix_title) . $hard_return . "</td>\n";
		print "<td style='$this_row_style'>" . $graph_item_types{$item["graph_type_id"]} . "</td>\n";
		print "<td style='$this_row_style'>" . $consolidation_functions{$item["consolidation_function_id"]} . "</td>\n";
		print "<td style='$this_row_style'>" . ((strlen($item["cdef_name"]) > 0) ? substr($item["cdef_name"],0,30) : __("None")) . "</td>\n";
		print "<td style='$this_row_style'>" . ((strlen($item["cdef_name"]) > 0) ? substr($item["gprint_name"],0,30) : __("None")) . "</td>\n";
		print "<td" . ((!empty($item["hex"])) ? " bgcolor='#" . $item["hex"] . "'" : "") . " width='1%'>&nbsp;</td>\n";
		print "<td style='$this_row_style'>" . $item["hex"] . "</td>\n";

		if ($disable_controls == false) {
			print "<td align='right'><a href='" . htmlspecialchars("$filename?action=item_remove&id=" . $item["id"] . "&$url_data") . "'><img id='buttonSmall" . $item["id"] . "' class='buttonSmall' src='images/delete_icon.gif' title='Delete this Item' alt='Delete' align='middle'></a></td>\n";
		}

		print "</tr>";

		$i++;
	}
	}else{
		print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td colspan='" . (sizeof($header_items)+$last_item_colspan-1) . "'><em>" . __("No Items") . "</em></td></tr>";
	}

	print "</table></td></tr>";
}

function draw_header_tab($name, $title, $location, $image = "") {
	global $config;
	if ($image == "") {
		return "<li id=\"tab_" . html_escape($name) . "\"" . (html_selected_tab($name, $location) ? " class=\"selected\"" : " class=\"notselected\"") . "><a href=\"javascript:navigation_select('" . html_escape($name) . "','" . htmlspecialchars($location) . "')\" title=\"" . html_escape($title) . "\">" . html_escape($title) . "</a></li>\n";
	}else{
		return "<li id=\"tab_" . html_escape($name) . "\"" . (html_selected_tab($name, $location) ? " class=\"selected\"" : " class=\"notselected\"") . "><a href=\"javascript:navigation_select('" . html_escape($name) . "','" . htmlspecialchars($location) . "')\" title=\"" . html_escape($title) . "\"><img style='border-width:0px;' src='$image' alt='$title' align='middle'></a></li>\n";
	}
}

function html_selected_tab($name, $location) {
	if (isset($_COOKIE["navbar_id"])) {
		if ($name == "graphs") {
			if (substr_count($_SERVER["REQUEST_URI"], "graph_view.php")) {
				switch($_COOKIE["navbar_id"]) {
					case "list":
					case "preview":
					case "tree":
					case "graphs":
						return true;
				}
			}
		}else if (substr_count($_SERVER["REQUEST_URI"], $location)) {
			return true;
		}
	}else{
		if ($name == "console") {
			return true;
		}
	}

	return false;
}

function html_escape($html) {
	return htmlentities($html, ENT_QUOTES, 'UTF-8');
}

/* html_get_php_pathname() - extracts the name of the php file without the
   extention.  This value is used to store and retriev cookie values */
function html_get_php_pathname() {
	$path = $_SERVER["PHP_SELF"];

	while (($location = strpos($path, "/")) !== FALSE) {
		$path = substr($path, $location + 1);
	}

	return str_replace(".php", "", $path);
}

function html_get_column_width($name, $element) {
	$width = html_read_cookie_element($name, $element);

	if (!strlen($width)) {
		return "auto";
	}else{
		return $width . "px";
	}
}

/* html_read_cookie_element - extracts an element from the specified cookie array
   @arg $name - the cookie name that contains the cookie elements
   @arg $element - the name of the cookie element to be searched for. */
function html_read_cookie_element($name, $element) {
	if (isset($_COOKIE[$name])) {
		$parts = explode("!", $_COOKIE[$name]);

		foreach ($parts as $part) {
			$name_value = explode("@@", $part);

			if ($name_value[0] == $element) {
				if ($name_value[1] == "NaN") {
					return "";
				}else{
					return $name_value[1];
				}
			}
		}
	}

	return "";
}

/* draw_menu - draws the cacti menu for display in the console */
function draw_menu($user_menu = "") {
	global $colors, $config, $user_auth_realms, $user_auth_realm_filenames, $menu;

	if (strlen($user_menu == 0)) {
		$user_menu = $menu;
	}

	/* list all realms that this user has access to */
	if (read_config_option("auth_method") != 0) {
		$user_realms = db_fetch_assoc("select realm_id from user_auth_realm where user_id=" . $_SESSION["sess_user_id"]);
		$user_realms = array_rekey($user_realms, "realm_id", "realm_id");
	}else{
		$user_realms = $user_auth_realms;
	}

	$first_ul = true;

	/* loop through each header */
	while (list($header_name, $header_array) = each($user_menu)) {
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
			if (!$first_ul) {
				print "</ul></div>";
			}else{
				$first_ul = false;
			}

			$id = clean_up_name(strtolower($header_name));
			$ani  = "onClick='changeMenuState(\"" . $id . "\")'";
			?>
			<script type="text/javascript">
			<!--
				registerOnLoadFunction("menu", "changeMenuState('<?php print $id;?>', true)");
			-->
			</script>
			<?php
			print "<div id='mm_$id' onMouseDown='return false' class='menuMain' style='white-space:nowrap' $ani>$header_name</div>
				<div>
				<ul id='ul_$id' class='menuSubMain'>";
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
						$item_sub_url = URL_PATH . $item_sub_url;

						/* indent sub-items */
						if ($i > 0) {
							$prepend_string = "--- ";
						}else{
							$prepend_string = "";
						}

						/* do not put a line between each sub-item */
						if (($i == 0) || ($draw_sub_items == false)) {
							$background = URL_PATH . "images/menu_line.gif";
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
								print "<li class='menuSubMainSelected'><a href='$item_sub_url'>$prepend_string$item_sub_title</a></li>";
							}else{
								print "<li><a href='$item_sub_url'>$prepend_string$item_sub_title</a></li>";
							}
						}

						$i++;
					}
				}
			}else{
				if ((isset($user_realms[$current_realm_id])) || (!isset($user_auth_realm_filenames{basename($item_url)}))) {
					/* draw normal (non sub-item) menu item */
					$item_url = URL_PATH . $item_url;
					if (basename($_SERVER["PHP_SELF"]) == basename($item_url)) {
						print "<li class='menuSubMainSelected'><a href='$item_url'>$item_title</a></li>";
					}else{
						print "<li><a href='$item_url'>$item_title</a></li>";
					}
				}
			}
		}
	}

	print "</ul></div>";
}

/* draw_actions_dropdown - draws a table the allows the user to select an action to perform
     on one or more data elements
   @arg $actions_array - an array that contains a list of possible actions. this array should
     be compatible with the form_dropdown() function */
function draw_actions_dropdown($actions_array) {
	global $config;
	?>
	<table align='center' width='100%'>
		<tr>
			<td width='1' valign='top'>
				<img src='<?php echo URL_PATH; ?>images/arrow.gif' alt='' align='middle'>&nbsp;
			</td>
			<td align='right'>
				<?php print __("Choose an action:");?>
				<?php form_dropdown("drp_action",$actions_array,"","","1","","");?>
			</td>
			<td width='1' align='right'>
				<input type='submit' value='<?php print __("Go");?>' name='go'>
			</td>
		</tr>
	</table>

	<input type='hidden' name='action' value='actions'>
	<?php
}

/*
 * Deprecated functions
 */

function DrawMatrixHeaderItem($matrix_name, $matrix_text_color, $column_span = 1, $align = "left") { ?>
		<td height="1" align="<?php print $align;?>" colspan="<?php print $column_span;?>">
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

?>
