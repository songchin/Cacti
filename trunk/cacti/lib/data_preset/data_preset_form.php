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

/* form validation functions */


/* rra preset fields */

function _data_preset_rra__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A name for this RRA preset.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

/* rra item preset fields */

function _data_preset_rra_item_js() {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_constants.php");
	?>
	<script language="JavaScript">
	<!--
	var new_rra_form_displayed = false;

	function new_rra_item(box_id) {
		if (new_rra_form_displayed == false) {
			/* clone the first RRA item and append it to the table */
			table = document.getElementById("box-" + box_id + "-content");
			var newRow = table.tBodies[0].rows[0].cloneNode(true);

			make_row_new(box_id, newRow);

			table.tBodies[0].appendChild(newRow);

			new_rra_form_displayed = true;
		}
	}

	function make_row_new(box_id, row) {
		var row_id = row.id.replace("row", "");
		var row_container = row.childNodes[1].childNodes[1].childNodes[1].childNodes;

		row.id = "row0";
		row_container[0].childNodes[1].childNodes[0].nodeValue = "(new)";
		row_container[0].childNodes[3].innerHTML = "<a class='linkOverDark' href='javascript:discard_new_row(\"" + box_id + "\")'>Discard</a>, <a class='linkOverDark' href='#'>Save</a>";

		/* start at index 1 to skip the header */
		for (var i = 1; i < row_container.length; i++) {
			if ((row_container[i].tagName == "TR") || (row_container[i].tagName == "tr")) {
				row_container[i].style.color = "gray";

				/* make sure each form element gets unique name */
				if (row_container[i].childNodes[3]) {
					row_container[i].childNodes[3].childNodes[1].id = row_container[i].childNodes[3].childNodes[1].id.replace(row_id, "0");
					row_container[i].childNodes[3].childNodes[1].name = row_container[i].childNodes[3].childNodes[1].name.replace(row_id, "0");
				}
			}
		}
	}

	function make_row_old(row, rra_item_id) {

	}

	function discard_new_row(box_id) {
		var table = document.getElementById("box-" + box_id + "-content");
		var row = document.getElementById("row0");
		var newRow = table.tBodies[0].removeChild(row);

		new_rra_form_displayed = false;
	}

	function update_consolidation_function(consolidation_function, row_id) {
		if (consolidation_function == <?php echo RRA_CF_TYPE_AVERAGE;?> || consolidation_function == <?php echo RRA_CF_TYPE_MIN;?> || consolidation_function == <?php echo RRA_CF_TYPE_MAX;?> || consolidation_function == <?php echo RRA_CF_TYPE_LAST;?>) {
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_HWPREDICT;?>) {
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_SEASONAL;?> || consolidation_function == <?php echo RRA_CF_TYPE_DEVSEASONAL;?>) {
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_DEVPREDICT;?>) {
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_FAILURES;?>) {
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'table-row';
		}
	}
	-->
	</script>
	<?php
}

function _data_preset_rra_item__consolidation_function_js_update($field_value, $field_id = 0) {
	?>
	<script language="JavaScript">
	<!--
	update_consolidation_function('<?php echo $field_value;?>', '<?php echo $field_id;?>');
	-->
	</script>
	<?php
}

function _data_preset_rra_item__consolidation_function($field_name_base, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Consolidation Function");?></span><br>
			<?php echo _("This function will be applied to a group of data points before they are entered into the RRA.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name_base, api_data_preset_rra_cf_type_list(), "", "", $field_value, "", "86400", "", "", "update_consolidation_function(this.value, \"$field_id\")");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__steps($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Update Interval (steps)");?></span><br>
			<?php echo _("How many data points are required before the data is entered into the RRA.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "1", 5, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__rows($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Retention Length (rows)");?></span><br>
			<?php echo _("How many values are kept in the RRA at one time.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__x_files_factor($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("X-Files Factor");?></span><br>
			<?php echo _("The percentage of data points that can be missing before the data is entered into the RRA as \"Unknown\" (must be between 0 and 1).");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "0.5", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_alpha($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_alpha_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Alpha (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight historic (0) or current (1) data has on the prediction (must be between 0 and 1).");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "0.1", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_beta($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_beta_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Beta (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight the slope of the line has on the prediction (must be between 0 and 1).");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "0.0035", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_gamma($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_gamma_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Gamma (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight the seasonal properties of line data has on the prediction (must be between 0 and 1).");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "0.1", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_seasonal_period($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_seasonal_period_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Seasonal Period (Holt-Winters)");?></span><br>
			<?php echo _("The amount of time for each seasonal period.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_rra_num($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_rra_num_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Dependent RRA (Holt-Winters)");?></span><br>
			<?php echo _("The amount of time for each seasonal period.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_threshold($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_threshold_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Threshold (Holt-Winters)");?></span><br>
			<?php echo _("The minimum number of violations that occur within a window that constitutes a failure.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "7", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_window_length($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_window_length_<?php echo $field_id;?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Window Length (Holt-Winters)");?></span><br>
			<?php echo _("The number of points contained within a window. Must be greater than or equal to the threshold and less than 28.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "9", 6, 10, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

?>
