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

/* form validation functions */

function api_graph_fields_validate(&$_fields_graph, &$_fields_suggested_values, $graph_field_name_format = "|field|", $suggested_values_field_name_format = "") {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	if (sizeof($_fields_graph) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_graph = api_graph_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_graph)) {
		if ((isset($_fields_graph[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $graph_field_name_format);

			if (!form_input_validate($_fields_graph[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	/* suggested values */
	while (list($_field_name, $_sv_array) = each($_fields_suggested_values)) {
		if ((isset($fields_graph[$_field_name])) && (isset($fields_graph[$_field_name]["validate_regexp"])) && (isset($fields_graph[$_field_name]["validate_empty"]))) {
				while (list($_sv_seq, $_sv_arr) = each($_sv_array)) {
				$form_field_name = str_replace("|field|", $_field_name, str_replace("|id|", $_sv_arr["id"], $suggested_values_field_name_format));

				if (!form_input_validate($_sv_arr["value"], $form_field_name, $fields_graph[$_field_name]["validate_regexp"], $fields_graph[$_field_name]["validate_empty"])) {
					$error_fields[] = $form_field_name;
				}
			}
		}
	}

	return $error_fields;
}

function api_graph_item_fields_validate(&$_fields_graph_item, $graph_item_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");

	if (sizeof($_fields_graph_item) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_graph_item = api_graph_item_form_list();

	/* do not allow empty gprint format when the item type is GPRINT */
	if ((isset($_fields_graph_item["graph_item_type"])) && ($_fields_graph_item["graph_item_type"] == GRAPH_ITEM_TYPE_GPRINT)) {
		$fields_graph_item["gprint_format"]["validate_empty"] = false;
	}

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_graph_item)) {
		if ((isset($_fields_graph_item[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $graph_item_field_name_format);

			if (!form_input_validate($_fields_graph_item[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* graph fields */

function _graph_field__title($field_name, $template_flag = false, $field_id = 0, $t_field_name = "", $t_field_value = "") {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	if ($template_flag == true) {
		if (empty($field_id)) {
			$values_array = array();
		}else{
			$values_array = array_rekey(db_fetch_assoc("select value,id from graph_template_suggested_value where graph_template_id = " . $field_id . " and field_name = 'title' order by sequence"), "id", "value");
		}

		$url_moveup = "javascript:document.forms[0].action.value='sv_moveup';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_moveup&id=|id|" . (empty($field_id) ? "" : "&graph_template_id=" . $field_id)) . "', '')";
		$url_movedown = "javascript:document.forms[0].action.value='sv_movedown';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_movedown&id=|id|" . (empty($field_id) ? "" : "&graph_template_id=" . $field_id)) . "', '')";
		$url_delete = "javascript:document.forms[0].action.value='sv_remove';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_remove&id=|id|" . (empty($field_id) ? "" : "&graph_template_id=" . $field_id)) . "', '')";
		$url_add = "javascript:document.forms[0].action.value='sv_add';submit_redirect(0, '" . htmlspecialchars("graph_templates.php?action=sv_add" . (empty($field_id) ? "" : "&id=" . $field_id)) . "', '')";
	}else{
		if (empty($field_id)) {
			$field_value = "";
		}else{
			$field_value = db_fetch_cell("select title from graph where id = $field_id");
		}
	}

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Title");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The name that is printed on the graph.");
			}
			?>
		</td>
		<td>
			<?php
			if ($template_flag == true) {
				form_text_box_sv($field_name, $values_array, $url_moveup, "", $url_delete, $url_add, (($_GET["action"] == "sv_add") ? true : false), 255, 30);
			}else{
				form_text_box($field_name, $field_value, "", 255, 40, "text", $field_id);
			}
			?>
		</td>
	</tr>
	<?php
}

function _graph_field__vertical_label($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Vertical Label");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The label vertically printed to the left of the graph.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 200, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__image_format($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Image Format");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The type of graph that is generated; GIF or PNG.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $graph_image_types, "", "", $field_value, "", GRAPH_IMAGE_TYPE_PNG);?>
		</td>
	</tr>
	<?php
}

function _graph_field__export($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Allow Graph Export");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Choose whether this graph will be included in the static HTML/PNG export if you use Cacti's export feature.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Allow Graph Export"), "on", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__force_rules_legend($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Force HRULE/VRULE Legend");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Forces HRULE and VRULE items to be drawn on the legend even if they are not displayed on the graph.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Force HRULE/VRULE Legend"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__height($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Height");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The height (in pixels) of the graph area.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 120, 5, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__width($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Width");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The width (in pixels) of the graph area.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 500, 5, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__x_grid($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("X-Grid");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Controls the layout of the x-grid. See the RRDTool manual for additional details.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 50, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__y_grid($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Y-Grid");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Controls the layout of the y-grid. See the RRDTool manual for additional details.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 50, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__y_grid_alt($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Alternate Y-Grid");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Allows the dynamic placement of the y-grid based upon min and max values.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Alternate Y-Grid"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__no_minor($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("No Minor Grid Lines");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Removes minor grid lines. Especially usefull on small graphs.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("No Minor Grid Lines"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__auto_scale($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Utilize Auto Scale");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Auto scale the y-axis instead of defining an upper and lower limit. If this is checked, both the Upper and Lower limit will be ignored.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Utilize Auto Scale"), "on", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__auto_scale_opts($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Standard Auto Scale Options");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Use --alt-autoscale-max to scale to the maximum value, or --alt-autoscale to scale to the absolute minimum and maximum.");
			}
			?>
		</td>
		<td>
			<?php
			form_radio_button($field_name, $field_value, GRAPH_AUTOSCALE_OPT_AUTOSCALE, "Use --alt-autoscale", GRAPH_AUTOSCALE_OPT_AUTOSCALE_MAX); echo "<br>";
			form_radio_button($field_name, $field_value, GRAPH_AUTOSCALE_OPT_AUTOSCALE_MAX, "Use --alt-autoscale-max", GRAPH_AUTOSCALE_OPT_AUTOSCALE_MAX);
			?>
		</td>
	</tr>
	<?php
}

function _graph_field__auto_scale_log($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Logarithmic Auto Scaling (--logarithmic)");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Use Logarithmic y-axis scaling");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Logarithmic Auto Scaling"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__auto_scale_rigid($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Rigid Boundaries Mode (--rigid)");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Do not expand the upper and lower limit if the graph contains a value outside the valid range.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Rigid Boundaries Mode"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__auto_padding($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Auto Padding");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Pad text so that legend and graph data always line up. Auto Padding may not be accurate on all types of graphs, consistant labeling usually helps.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Auto Padding"), "on", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function _graph_field__upper_limit($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Upper Limit");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The maximum vertical axis value for the graph.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 100, 20, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__lower_limit($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Lower Limit");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The minimum vertical axis value for the graph.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 0, 20, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__base_value($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Base Value");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Set to 1024 when graphing memory so that one kilobyte represents 1024 bytes.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $graph_base_values, "", "", $field_value, "", 1000);?>
		</td>
	</tr>
	<?php
}

function _graph_field__unit_value($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Units Value");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Sets the exponent value on the Y-axis for numbers. Note: This option was recently added in RRDTool 1.0.36.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 20, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__unit_length($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Units Length");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Sets the number of spaces for the units value to the left of the graph.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 9, 3, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_field__unit_exponent_value($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Units Exponent Value");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("How Cacti should scale the Y-axis label (None means autoscale).");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $graph_unit_exponent_values, "", "", $field_value, "", 0);?>
		</td>
	</tr>
	<?php
}

/* graph item fields */

function _graph_item_field__data_template_item_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Template Item");?></span><br>
			<?php echo _("The data template item to use for this graph item.");?>
		</td>
		<td>
			<?php
			$items = db_fetch_assoc("select
				data_template.template_name,
				data_template_item.data_source_name,
				data_template_item.id
				from data_template,data_template_item
				where data_template.id=data_template_item.data_template_id
				order by data_template.template_name,data_template_item.data_source_name");

			$_list_items = array();
			if (sizeof($items) > 0) {
				foreach ($items as $item) {
					$_list_items{$item["id"]} = $item["template_name"] . " - (" . $item["data_source_name"] . ")";
				}
			}

			form_dropdown($field_name, $_list_items, "", "", $field_value, "", "");
			?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__data_source_item_id($field_name, $field_value = "", $field_id = 0, $host_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Source Item");?></span><br>
			<?php echo _("The data source item to use for this graph item.");?>
		</td>
		<td>
			<?php
			$items = db_fetch_assoc("select
				data_source.name_cache,
				data_source.id,
				host.description,
				data_source_item.data_source_name
				from (data_source,data_source_item)
				left join host on (data_source.host_id = host.id)
				where data_source_item.data_source_id = data_source.id
				and data_source.host_id = $host_id
				order by host.description,data_source.name_cache,data_source_item.data_source_name");

			$_list_items = array();
			if (sizeof($items) > 0) {
				foreach ($items as $item) {
					$_list_items{$item["id"]} = ($item["description"] == "" ? "No Host" : $item["description"]) . " - " . $item["name_cache"] . " (" . $item["data_source_name"] . ")";
				}
			}

			form_dropdown($field_name, $_list_items, "", "", $field_value, "", "");
			?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__color($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Color");?></span><br>
			<?php echo _("The color to use for the legend.");?>
		</td>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<?php form_text_box($field_name, $field_value, "", 6, 10, "text", $field_id);?>
					</td>
					<td align="right">
						<?php
						$colors = array();
						$colors[""] = "(None)";
						$colors += array_rekey(db_fetch_assoc("select hex as id,hex as name from preset_color order by hex"), "id", "name");

						form_color_dropdown("preset_$field_name", $colors, "", "", "", "document.forms[0].$field_name.value=document.forms[0].preset_$field_name.value");
						?>
						<span style="font-weight: bold; color: #c34138; font-size: 14px;" title="Preset Selection">*</span
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

function _graph_item_field__graph_item_type($field_name, $field_value = "", $field_id = 0) {
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Graph Item Type");?></span><br>
			<?php echo _("How data for this item is represented visually on the graph.");?>
		</td>
		<td>
			<?php form_dropdown($field_name, $graph_item_types, "", "", $field_value, "", GRAPH_ITEM_TYPE_COMMENT);?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__consolidation_function($field_name, $field_value = "", $field_id = 0) {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Consolidation Function");?></span><br>
			<?php echo _("How data for this item is represented statistically on the graph.");?>
		</td>
		<td>
			<?php form_dropdown($field_name, $consolidation_functions, "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__cdef($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("CDEF Function");?></span><br>
			<?php echo _("A CDEF (math) function to apply to this item on the graph.");?>
		</td>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<?php form_text_box($field_name, $field_value, "", 255, 20, "text", $field_id);?>
					</td>
					<td align="right">
						<?php
						$cdefs = array();
						$cdefs[""] = "(None)";
						$cdefs += array_rekey(db_fetch_assoc("select cdef_string as id,name from preset_cdef order by name"), "id", "name");

						form_dropdown("preset_$field_name", $cdefs, "", "", $field_value, "", "", "", 20, "document.forms[0].$field_name.value=document.forms[0].preset_$field_name.value");
						?>
						<span style="font-weight: bold; color: #c34138; font-size: 14px;" title="Preset Selection">*</span
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

function _graph_item_field__gprint_format($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("GPRINT Format");?></span><br>
			<?php echo _("If this graph item is a GPRINT, you can optionally choose another format here. You can define additional types under \"GPRINT Presets\".");?>
		</td>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<?php form_text_box($field_name, $field_value, "%8.2lf %s", 30, 20, "text", $field_id);?>
					</td>
					<td align="right">
						<?php
						$gprints = array();
						$gprints[""] = "(None)";
						$gprints += array_rekey(db_fetch_assoc("select gprint_text as id,name from preset_gprint order by name"), "id", "name");

						form_dropdown("preset_$field_name", $gprints, "", "", $field_value, "", "", "", 20, "document.forms[0].$field_name.value=document.forms[0].preset_$field_name.value");
						?>
						<span style="font-weight: bold; color: #c34138; font-size: 14px;" title="Preset Selection">*</span
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

function _graph_item_field__legend_value($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Legend Value");?></span><br>
			<?php echo _("The value of an HRULE or VRULE graph item.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 50, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__legend_format($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Legend Text Format");?></span><br>
			<?php echo _("Text that will be displayed on the legend for this graph item.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_item_field__hard_return($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Insert Hard Return");?></span><br>
			<?php echo _("Forces the legend to the next line after this item.");?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Insert Hard Return"), "", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

function draw_graph_item_editor($graph_X_id, $form_type, $disable_controls) {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/graph/graph_utility.php");
	require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");
	require(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

	$graph_actions = array(
		1 => _("Delete Items"),
		2 => _("Duplicate Items")
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
			data_template_item.data_source_name
			from (graph_template_item,graph_template)
			left join data_template_item on (graph_template_item.data_template_item_id=data_template_item.id)
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
			data_source_item.data_source_name
			from (graph_item,graph)
			left join data_source_item on (graph_item.data_source_item_id=data_source_item.id)
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
			<input type='checkbox' style='margin: 0px;' name='all' title='<?php echo _("Select All");?>' onClick='graph_item_rows_selection(this.checked)'>
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
						<a href="javascript:graph_item_row_visibility(<?php echo $row_counter;?>)"><img id='img_<?php echo $row_counter;?>' src='<?php echo html_get_theme_images_path("hide.gif");?>' border='0' title='<?php echo _("Collapse Row");?>' alt='<?php echo _("Collapse Row");?>' align='absmiddle'></a>
					</td>
					<td style='border-right: 1px solid #b5b5b5; border-bottom: 1px solid #b5b5b5;' width='60'>
						<strong><?php echo _("Row #") . $row_counter;?></strong>
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
						<a href='<?php echo $url_filename;?>?action=row_movedown&row=<?php echo $row_counter;?><?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_down.gif");?>' border='0' title='<?php echo _("Move Item Down");?>' alt='<?php echo _("Move Item Down");?>'></a>
						<?php }else{ ?>
						&nbsp;
						<?php } ?>
					</td>
					<td align='left' width='25' style='border-bottom: 1px solid #b5b5b5;'>
						<?php if ($i > 0) { ?>
						<a href='<?php echo $url_filename;?>?action=row_moveup&row=<?php echo $row_counter;?><?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_up.gif");?>' border='0' title='<?php echo _("Move Item Up");?>' alt='<?php echo _("Move Item Up");?>'></a>
						<?php }else{ ?>
						&nbsp;
						<?php } ?>
					</td>
					<td style="<?php echo get_checkbox_style();?> border-bottom: 1px solid #b5b5b5;" width="1%" align="right">
						<input type='checkbox' style='margin: 0px;' onClick="graph_item_row_selection(<?php echo $row_counter;?>)" name='row_chk_<?php echo $row_counter;?>' id='row_chk_<?php echo $row_counter;?>' title="<?php echo _('Row #') . $row_counter;?>">
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
					<a href='<?php echo $url_filename;?>?action=edit&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'><?php echo _("Item #") . ($i+1);?></a>
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
					<a href='<?php echo $url_filename;?>?action=item_movedown&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_down.gif");?>' border='0' title='<?php echo _("Move Item Down");?>' alt='<?php echo _("Move Item Down");?>'></a>
					<?php } ?>
				</td>
				<td width='25' align='left'>
					<?php if ($i > 0) { ?>
					<a href='<?php echo $url_filename;?>?action=item_moveup&id=<?php echo $item["id"];?>&<?php echo $url_data;?>'><img src='<?php echo html_get_theme_images_path("move_up.gif");?>' border='0' title='<?php echo _("Move Item Up");?>' alt='<?php echo _("Move Item Up");?>'></a>
					<?php } ?>
				</td>
				<td width='1' style="<?php echo get_checkbox_style();?>" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_gi_<?php echo $item["id"];?>' id='chk_<?php echo $item["id"];?>' title="<?php echo _('Item #') . ($i + 1);?>">
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
		echo "<tr><td><em>" . _("No graph items found.") . "</em></td></tr>\n";
	}
}

?>