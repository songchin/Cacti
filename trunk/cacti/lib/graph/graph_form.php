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

function draw_graph_item_editor($graph_X_id, $form_type, $disable_controls) {
	global $colors;

	include_once(CACTI_BASE_PATH . "/lib/graph/graph_utility.php");
	include_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	include(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	include(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	$graph_actions = array(
		1 => "Delete Items",
		2 => "Duplicate Items"
		);

	if ($form_type == "graph_template") {
		$item_list = db_fetch_assoc("select
			graph_template_item.id,
			graph_template_item.legend_format,
			graph_template_item.legend_value,
			graph_template_item.gprint_format,
			graph_template_item.hard_return,
			graph_template_item.graph_item_type,
			graph_template_item.consolidation_function,
			graph_template_item.color,
			graph_template_item.sequence,
			graph_template.auto_padding,
			data_template.name,
			data_template_item.data_source_name
			from graph_template_item,graph_template
			left join data_template_item on graph_template_item.data_template_item_id=data_template_item.id
			left join data_template on data_template_item.data_template_id=data_template.id
			where graph_template_item.graph_template_id=graph_template.id
			and graph_template_item.graph_template_id = $graph_X_id
			order by graph_template_item.sequence");

		$url_filename = "graph_templates_items.php";
		$url_data = "&graph_template_id=$graph_X_id";
	}else if ($form_type == "graph") {
		$item_list = db_fetch_assoc("select
			graph_item.id,
			graph_item.legend_format,
			graph_item.legend_value,
			graph_item.gprint_format,
			graph_item.hard_return,
			graph_item.graph_item_type,
			graph_item.consolidation_function,
			graph_item.color,
			graph_item.sequence,
			graph.auto_padding,
			data_source.name,
			data_source_item.data_source_name
			from graph_item,graph
			left join data_source_item on graph_item.data_source_item_id=data_source_item.id
			left join data_source on data_source_item.data_source_id=data_source.id
			where graph_item.graph_id=graph.id
			and graph_item.graph_id = $graph_X_id
			order by graph_item.sequence");

		$url_filename = "graphs_items.php";
		$url_data = "&graph_id=$graph_X_id";
	}else{
		return;
	}

	?>
	<tr bgcolor='#<?php echo $colors["header_panel_background"];?>'>
		<td width='12'>
			&nbsp;
		</td>
		<td width='60' class='textSubHeaderDark'>
			Item
		</td>
		<td class='textSubHeaderDark'>
			Graph Item Type
		</td>
		<td class='textSubHeaderDark'>
			Data Source
		</td>
		<td class='textSubHeaderDark'>
			Legend Text
		</td>
		<td class='textSubHeaderDark'>
			Color
		</td>
		<td class='textSubHeaderDark'>
			CF Type
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			&nbsp;
		</td>
		<td width='1%' align='right' bgcolor='#819bc0' style='<?php echo get_checkbox_style();?>'>
			<input type='checkbox' style='margin: 0px;' name='all' title='Select All' onClick='graph_item_rows_selection(this.checked)'>
		</td>
	</tr>
	<?php

	if (sizeof($item_list) > 0) {
		/* calculate auto padding information and other information that we will need below */
		$max_pad_length = 0;
		$total_num_rows = 0;

		for ($i=0; $i<sizeof($item_list); $i++) {
			if (($i == 0) || (!empty($item_list{$i-1}["hard_return"]))) {
				if (strlen($item_list[$i]["legend_format"]) > $max_pad_length) {
					$max_pad_length = strlen($item_list[$i]["legend_format"]);
				}

				$total_num_rows++;
			}
		}

		$i = 0;
		$row_counter = 1;

		/* preload expand/contract icons */
		echo "<script type='text/javascript'>\nvar auxImg;\nauxImg = new Image();auxImg.src = '" . html_get_theme_images_path("show.gif") . "';\nauxImg.src = '" . html_get_theme_images_path("hide.gif") . "';\n</script>\n";

		/* initialize JS variables */
		echo "<script type='text/javascript'>\nvar item_row_list = new Array()\n</script>\n";

		foreach ($item_list as $item) {
			$matrix_title = "";
			$hard_return = "";
			$show_moveup = true;
			$show_movedown = true;

			if (is_graph_item_type_primary($item["graph_item_type"])) {
				$matrix_title = "(" . $item["data_source_name"] . "): " . $item["legend_format"];
			}else if (($item["graph_item_type"] == GRAPH_ITEM_TYPE_HRULE) || ($item["graph_item_type"] == GRAPH_ITEM_TYPE_VRULE)) {
				$matrix_title = $graph_item_types{$item["graph_item_type"]} . ": " . $item["legend_value"];
			}else if ($item["graph_item_type"] == GRAPH_ITEM_TYPE_COMMENT) {
				$matrix_title = "COMMENT: " . $item["legend_format"];
			}

			if (!empty($item["hard_return"])) {
				$hard_return = "<strong><font color=\"#FF0000\">&lt;HR&gt;</font></strong>";
			}

			if (($i == 0) || (!empty($item_list{$i-1}["hard_return"]))) {
				?>
				<tr bgcolor="#<?php echo $colors["form_custom1"];?>">
					<td width='12' style='border-bottom: 1px solid #b5b5b5;' align='center'>
						<a href="javascript:graph_item_row_visibility(<?php echo $row_counter;?>)"><img id='img_<?php echo $row_counter;?>' src='<?php echo html_get_theme_images_path("hide.gif");?>' border='0' title='Collapse Row' alt='Collapse Row' align='absmiddle'></a>
					</td>
					<td style='border-right: 1px solid #b5b5b5; border-bottom: 1px solid #b5b5b5;' width='60'>
						<strong>Row #<?php echo $row_counter;?></strong>
					</td>
					<td colspan='5' style='font-family: monospace; color: #515151; cursor: pointer; border-bottom: 1px solid #b5b5b5;' onClick="graph_item_row_visibility(<?php echo $row_counter;?>)" nowrap>
						<pre><?php
						$j = $i;
						$graph_item_row = array();
						do {
							$_item = $item_list[$j];
							if (is_graph_item_type_primary($_item["graph_item_type"])) {
								if ($_item["color"] != "") {
									echo "<img src='" . html_get_theme_images_path("transparent_line.gif") . "'style='width: 9px; height: 9px; border: 1px solid #000000; background-color: #" . $_item["color"] . "' border='0' align='absmiddle' alt=''>&nbsp;";
								}
							}

							if ($_item["graph_item_type"] == GRAPH_ITEM_TYPE_GPRINT) {
								printf(" " . $_item["legend_format"] . $_item["gprint_format"], "0", "");
							}else{
								echo $_item["legend_format"];
							}

							/* the first item of the row is where auto padding is applied */
							if ($i == $j) {
								echo (empty($_item["auto_padding"])) ? "" : str_repeat(" ", (($max_pad_length + 1) - strlen($_item["legend_format"])));
							}

							/* keep track of each item in this row so we can create a JS array below */
							$graph_item_row[] = $_item["id"];

							$j++;
						} while ((empty($item_list{$j-1}["hard_return"])) && (($j+1)<=sizeof($item_list)));
						?></pre>
					</td>
					<td align='center' width='15' style='border-bottom: 1px solid #b5b5b5;'>
						<?php if ($row_counter < $total_num_rows) { ?>
						<a href='<?php echo $url_filename;?>?action=row_movedown&row=<?php echo $row_counter;?><?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_down.gif");?>' border='0' title='Move Item Down' alt='Move Item Down'></a>
						<?php }else{ ?>
						&nbsp;
						<?php } ?>
					</td>
					<td align='left' width='25' style='border-bottom: 1px solid #b5b5b5;'>
						<?php if ($i > 0) { ?>
						<a href='<?php echo $url_filename;?>?action=row_moveup&row=<?php echo $row_counter;?><?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_up.gif");?>' border='0' title='Move Item Up' alt='Move Item Up'></a>
						<?php }else{ ?>
						&nbsp;
						<?php } ?>
					</td>
					<td style="<?php echo get_checkbox_style();?> border-bottom: 1px solid #b5b5b5;" width="1%" align="right">
						<input type='checkbox' style='margin: 0px;' onClick="graph_item_row_selection(<?php echo $row_counter;?>)" name='row_chk_<?php echo $row_counter;?>' id='row_chk_<?php echo $row_counter;?>' title="Row #<?php echo $row_counter;?>">
					</td>
				</tr>
				<?php

				/* create a JS array of graph items in each row */
				echo "<script type='text/javascript'>\nitem_row_list[$row_counter] = new Array(";

				for ($j=0; $j<sizeof($graph_item_row); $j++) {
					echo "'" . $graph_item_row[$j] . "'" . (($j+1) < sizeof($graph_item_row) ? "," : "");
				}

				echo ")\n</script>\n";

				$row_counter++;
			}

			/* only show arrows when they are supposed to be shown */
			if ($i == 0) {
				$show_moveup = false;
			}else if (($i+1) == sizeof($item_list)) {
				$show_movedown = false;
			}

			if (empty($item["graph_template_item_group_id"])) {
				$row_color = $colors["form_alternate1"];
			}else{
				$row_color = $colors["alternate"];
			}

			?>
			<tr id="tr_<?php echo $item["id"];?>" bgcolor="#<?php echo $row_color;?>">
				<td width='12' align='center'>
					&nbsp;
				</td>
				<td width='60' style='border-right: 1px solid #b5b5b5;'>
					<a href='<?php echo $url_filename;?>?action=edit&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'>Item #<?php echo ($i+1);?></a>
				</td>
				<td>
					<?php echo $graph_item_types{$item["graph_item_type"]};?>
				</td>
				<td>
					<?php echo $item["data_source_name"];?>
				</td>
				<td>
					<?php echo $item["legend_format"];?><?php echo (empty($item["hard_return"]) ? "" : "<span style='color: red; font-weight: bold;'>&lt;HR&gt;</span>");?>
				</td>
				<td>
					<?php echo $item["color"];?>
				</td>
				<td>
					<?php echo $consolidation_functions{$item["consolidation_function"]};?>
				</td>
				<td width='15' align='center'>
					<?php if (($i+1) < sizeof($item_list)) { ?>
					<a href='<?php echo $url_filename;?>?action=item_movedown&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_down.gif");?>' border='0' title='Move Item Down' alt='Move Item Down'></a>
					<?php } ?>
				</td>
				<td width='25' align='left'>
					<?php if ($i > 0) { ?>
					<a href='<?php echo $url_filename;?>?action=item_moveup&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_up.gif");?>' border='0' title='Move Item Up' alt='Move Item Up'></a>
					<?php } ?>
				</td>
				<td width='1' style="<?php echo get_checkbox_style();?>" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_gi_<?php echo $item["id"];?>' id='chk_<?php echo $item["id"];?>' title="Item #<?php echo ($i + 1);?>">
				</td>
			</tr>
			<?php

			$i++;
		}

		/* create a JS array for each row */
		echo "<script type='text/javascript'>\nvar item_rows = new Array(";

		for ($j=1; $j<$row_counter; $j++) {
			echo $j . (($j+1) < $row_counter ? "," : "");
		}

		echo ")\n</script>\n";

		?>
		<tr bgcolor='#ffffff'>
			<td colspan='10' style='border-top: 1px dashed #a1a1a1;'>
				<?php draw_actions_dropdown($graph_actions, 2, 100); ?>
			</td>
		</tr>
		<?php
	}else{
		echo "<tr><td><em>No graph items found.</em></td></tr>\n";
	}
}

?>
