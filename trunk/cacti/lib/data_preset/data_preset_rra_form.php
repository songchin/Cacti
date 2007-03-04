<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

/*
 * FORM VALIDATION
 */

function api_data_preset_rra_field_validate(&$_fields_data_preset_rra, $data_preset_rra_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	if (sizeof($_fields_data_preset_rra) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_preset_rra = api_data_preset_rra_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_preset_rra)) {
		if ((isset($_fields_data_preset_rra[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_preset_rra_field_name_format);

			if (!form_input_validate($_fields_data_preset_rra[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_data_preset_rra_item_field_validate(&$_fields_data_preset_rra_item, $data_preset_rra_item_field_name_format) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	if (sizeof($_fields_data_preset_rra_item) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_preset_rra_item = api_data_preset_rra_item_form_list();

	/* only certain fields are displayed on the form depending on the selected consolidation function */
	if (isset($_fields_data_preset_rra_item["consolidation_function"])) {
		$invisible_fields = array_diff(array_keys($fields_data_preset_rra_item), api_data_preset_rra_item_visible_field_list($_fields_data_preset_rra_item["consolidation_function"]));

		foreach ($invisible_fields as $field_name) {
			unset($_fields_data_preset_rra_item[$field_name]);
		}
	}

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_preset_rra_item)) {
		if ((isset($_fields_data_preset_rra_item[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_preset_rra_item_field_name_format);

			if (!form_input_validate($_fields_data_preset_rra_item[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_data_preset_rra_item_visible_field_list($consolidation_function) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");

	$visible_fields = array();
	if (($consolidation_function == RRA_CF_TYPE_AVERAGE) || ($consolidation_function == RRA_CF_TYPE_MIN) || ($consolidation_function == RRA_CF_TYPE_MAX) || ($consolidation_function == RRA_CF_TYPE_LAST)) {
		$visible_fields = array("consolidation_function", "steps", "rows", "x_files_factor");
	}else if ($consolidation_function == RRA_CF_TYPE_HWPREDICT) {
		$visible_fields = array("consolidation_function", "rows", "hw_alpha", "hw_beta", "hw_seasonal_period", "hw_rra_num");
	}else if (($consolidation_function == RRA_CF_TYPE_SEASONAL) || ($consolidation_function == RRA_CF_TYPE_DEVSEASONAL)) {
		$visible_fields = array("consolidation_function", "hw_gamma", "hw_seasonal_period", "hw_rra_num");
	}else if ($consolidation_function == RRA_CF_TYPE_DEVPREDICT) {
		$visible_fields = array("consolidation_function", "rows", "hw_rra_num");
	}else if ($consolidation_function == RRA_CF_TYPE_FAILURES) {
		$visible_fields = array("consolidation_function", "rows", "hw_rra_num", "hw_threshold", "hw_window_length");
	}

	return $visible_fields;
}

/*
 * XAJAX HANDLERS
 */

function _data_preset_rra_item_xajax_save($post_args) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");

	$objResponse = new xajaxResponse();

	if (basename($_SERVER["PHP_SELF"]) == "presets_rra.php") {
		$form_rra_item["preset_rra_id"] = $post_args["preset_rra_id"];
	}else if (basename($_SERVER["PHP_SELF"]) == "data_templates.php") {
		$form_rra_item["data_template_id"] = $post_args["data_template_id"];
	}else if (basename($_SERVER["PHP_SELF"]) == "data_sources.php") {
		$form_rra_item["data_source_id"] = $post_args["data_source_id"];
	}

	/* obtain a list of visible rra item fields on the form */
	$visible_fields = api_data_preset_rra_item_visible_field_list($post_args["rrai|consolidation_function|0"]);

	/* all non-visible fields on the form should be discarded */
	foreach ($visible_fields as $field_name) {
		$form_rra_item[$field_name] = $post_args["rrai|$field_name|0"];
	}

	$field_errors = api_data_preset_rra_item_field_validate($form_rra_item, "rrai||field||0");

	foreach (array_keys($form_rra_item) as $field_name) {
		if (isset($post_args{"rrai|" . $field_name . "|0"})) {
			/* make a red border around the fields which have validation errors */
			if (in_array("rrai|" . $field_name . "|0", $field_errors)) {
				$objResponse->addAssign("rrai|" . $field_name . "|0", "style.border", "2px solid red");
			/* clear the border for all of the fields without validation errors */
			}else{
				$objResponse->addClear("rrai|" . $field_name . "|0", "style.border");
			}
		}
	}

	$rra_preset_item_id = false;
	if (sizeof($field_errors) > 0) {
		$objResponse->addAlert("Form validation error!");
	}else{
		if (basename($_SERVER["PHP_SELF"]) == "presets_rra.php") {
			$rra_preset_item_id = api_data_preset_rra_item_save(0, $form_rra_item);
		}else if (basename($_SERVER["PHP_SELF"]) == "data_templates.php") {
			$rra_preset_item_id = api_data_template_rra_item_save(0, $form_rra_item);
		}else if (basename($_SERVER["PHP_SELF"]) == "data_sources.php") {
			$rra_preset_item_id = api_data_source_rra_item_save(0, $form_rra_item);
		}else{
			$rra_preset_item_id = false;
		}

		if ($rra_preset_item_id === false) {
			$objResponse->addAlert("Save error!");
		}else{
			/* update the rra item header text */
			$objResponse->addAssign("row_rra_item_header_0", "innerHTML", api_data_preset_rra_item_friendly_name_get($post_args["rrai|consolidation_function|0"], $post_args["rrai|steps|0"], $post_args["rrai|rows|0"]));

			$objResponse->addScript("make_row_old(\"$rra_preset_item_id\");");
		}
	}

	return $objResponse->getXML();
}

function _data_preset_rra_item_xajax_remove($preset_rra_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	$objResponse = new xajaxResponse();

	if (basename($_SERVER["PHP_SELF"]) == "presets_rra.php") {
		$preset_rra_item = api_data_preset_rra_item_get($preset_rra_id);
		$result = api_data_preset_rra_item_remove($preset_rra_id);
	}else if (basename($_SERVER["PHP_SELF"]) == "data_templates.php") {
		$preset_rra_item = api_data_template_rra_item_get($preset_rra_id);
		$result = api_data_template_rra_item_remove($preset_rra_id);
	}else if (basename($_SERVER["PHP_SELF"]) == "data_sources.php") {
		$preset_rra_item = api_data_source_rra_item_get($preset_rra_id);
		$result = api_data_source_rra_item_remove($preset_rra_id);
	}else{
		return false;
	}

	if ($result) {
		if (basename($_SERVER["PHP_SELF"]) == "presets_rra.php") {
			$num_items = sizeof(api_data_preset_rra_item_list($preset_rra_item["preset_rra_id"]));
		}else if (basename($_SERVER["PHP_SELF"]) == "data_templates.php") {
			$num_items = sizeof(api_data_template_rra_item_list($preset_rra_item["data_template_id"]));
		}else if (basename($_SERVER["PHP_SELF"]) == "data_sources.php") {
			$num_items = sizeof(api_data_source_rra_item_list($preset_rra_item["data_source_id"]));
		}

		/* if there are no rra items left, do not remove the row from the form but instead mark it as "new" */
		if ($num_items == 0) {
			$objResponse->addScript("remove_rra_item_last_row(\"$preset_rra_id\");");
		/* if there is at least one rra item left, visibly remove the row from the page */
		}else{
			$objResponse->addScript("remove_rra_item_row(\"$preset_rra_id\");");
		}
	}else{
		$objResponse->addAlert("Error removing RRA preset item!");
	}

	return $objResponse->getXML();
}

/*
 * RRA PRESET FIELDS
 */

function _data_preset_rra__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A name for this RRA preset.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

/*
 * RRA ITEM PRESET FIELDS
 */

function _data_preset_rra_item_js($form_name) {
	require_once(CACTI_BASE_PATH . "/include/data_preset/data_preset_rra_constants.php");
	?>
	<script language="JavaScript">
	<!--
	var new_rra_form_displayed = false;
	var html_form_name = '<?php echo $form_name;?>';

	function new_rra_item(box_id) {
		if (new_rra_form_displayed == false) {
			/* clone the first RRA item and append it to the table */
			table = document.getElementById("box-" + box_id + "-content");
			var newRow = table.tBodies[0].rows[0].cloneNode(true);

			make_row_new(newRow, false);

			table.tBodies[0].appendChild(newRow);
		}

		window.location = '#rra_preset_bottom';
	}

	function make_row_new(row, last_row) {
		var row_id = row.id.replace("row", "");
		var row_container = row.childNodes[1].childNodes[1].childNodes[1].childNodes;

		row.id = "row0";
		row_container[0].childNodes[1].id = "row_rra_item_header_0";
		row_container[0].childNodes[1].style.backgroundColor = "#883e61";
		row_container[0].childNodes[1].childNodes[0].nodeValue = "(new)";
		row_container[0].childNodes[3].innerHTML = (last_row == true ? "" : "<a class='linkOverDark' href='javascript:remove_rra_item_row(\"0\")'>Discard</a>, ") + "<a class='linkOverDark' href='#rra_preset_bottom' onClick='javascript:xajax__data_preset_rra_item_xajax_save(xajax.getFormValues(\"" + html_form_name + "\"))'>Save</a>";
		row_container[0].childNodes[3].style.backgroundColor = "#883e61";

		/* start at index 1 to skip the header */
		for (var i = 1; i < row_container.length; i++) {
			if ((row_container[i].tagName == "TR") || (row_container[i].tagName == "tr")) {
				row_container[i].style.color = "gray";
				row_container[i].id = row_container[i].id.replace(row_id, "0");

				/* make sure each form element gets unique name */
				if (row_container[i].childNodes[3]) {
					row_container[i].childNodes[3].childNodes[1].id = row_container[i].childNodes[3].childNodes[1].id.replace(row_id, "0");
					row_container[i].childNodes[3].childNodes[1].name = row_container[i].childNodes[3].childNodes[1].name.replace(row_id, "0");
				}
			}
		}

		new_rra_form_displayed = true;
	}

	function make_row_old(rra_item_id) {
		var row = document.getElementById("row0");
		var row_container = row.childNodes[1].childNodes[1].childNodes[1].childNodes;
		var row_id = "0";

		row.id = "row" + rra_item_id;
		row_container[0].childNodes[1].id = "row_rra_item_header_" + rra_item_id;
		row_container[0].childNodes[1].style.backgroundColor = "#6d88ad";
		row_container[0].childNodes[3].innerHTML = "<a class='linkOverDark' href='#' onClick='javascript:xajax__data_preset_rra_item_xajax_remove(\"" + rra_item_id  + "\")'>Remove</a>";
		row_container[0].childNodes[3].style.backgroundColor = "#6d88ad";

		/* start at index 1 to skip the header */
		for (var i = 1; i < row_container.length; i++) {
			if ((row_container[i].tagName == "TR") || (row_container[i].tagName == "tr")) {
				row_container[i].style.color = "black";
				row_container[i].id = row_container[i].id.replace(row_id, rra_item_id);

				/* make sure each form element gets unique name */
				if (row_container[i].childNodes[3]) {
					row_container[i].childNodes[3].childNodes[1].id = row_container[i].childNodes[3].childNodes[1].id.replace(row_id, rra_item_id);
					row_container[i].childNodes[3].childNodes[1].name = row_container[i].childNodes[3].childNodes[1].name.replace(row_id, rra_item_id);
				}
			}
		}

		new_rra_form_displayed = false;
	}

	function remove_rra_item_last_row(rra_item_id) {
		var row = document.getElementById("row" + rra_item_id);
		make_row_new(row, true);
	}

	function remove_rra_item_row(rra_item_id) {
		var row = document.getElementById("row" + rra_item_id);
		row.parentNode.removeChild(row);

		new_rra_form_displayed = false;
	}

	function update_consolidation_function(consolidation_function, row_id) {
		if (consolidation_function == <?php echo RRA_CF_TYPE_AVERAGE;?> || consolidation_function == <?php echo RRA_CF_TYPE_MIN;?> || consolidation_function == <?php echo RRA_CF_TYPE_MAX;?> || consolidation_function == <?php echo RRA_CF_TYPE_LAST;?>) {
			document.getElementById('row_field_steps_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_rows_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_x_files_factor_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_HWPREDICT;?>) {
			document.getElementById('row_field_steps_' + row_id).style.display = 'none';
			document.getElementById('row_field_rows_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_x_files_factor_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_SEASONAL;?> || consolidation_function == <?php echo RRA_CF_TYPE_DEVSEASONAL;?>) {
			document.getElementById('row_field_steps_' + row_id).style.display = 'none';
			document.getElementById('row_field_rows_' + row_id).style.display = 'none';
			document.getElementById('row_field_x_files_factor_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_DEVPREDICT;?>) {
			document.getElementById('row_field_steps_' + row_id).style.display = 'none';
			document.getElementById('row_field_rows_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_x_files_factor_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_alpha_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_beta_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_gamma_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_seasonal_period_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_rra_num_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_hw_threshold_' + row_id).style.display = 'none';
			document.getElementById('row_field_hw_window_length_' + row_id).style.display = 'none';
		}else if (consolidation_function == <?php echo RRA_CF_TYPE_FAILURES;?>) {
			document.getElementById('row_field_steps_' + row_id).style.display = 'none';
			document.getElementById('row_field_rows_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_x_files_factor_' + row_id).style.display = 'none';
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
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_consolidation_function_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Consolidation Function");?></span><br>
			<?php echo _("This function will be applied to a group of data points before they are entered into the RRA.");?>
		</td>
		<td colspan="2" class="field-row">
			<?php form_dropdown($field_name_base, api_data_preset_rra_cf_type_list(), "", "", $field_value, "", "86400", "", "", "update_consolidation_function(this.value, this.parentNode.parentNode.id.replace(\"row_field_consolidation_function_\", \"\"))");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__steps($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_steps_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Update Interval (steps)");?></span><br>
			<?php echo _("How many data points are required before the data is entered into the RRA.");?>
		</td>
		<td colspan="2" class="field-row">
			<?php form_dropdown($field_name, api_data_preset_rra_step_type_list(), "", "", $field_value, "", "300");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__rows($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_rows_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Data Retention Length (rows)");?></span><br>
			<?php echo _("How many values are kept in the RRA at one time.");?>
		</td>
		<td colspan="2" class="field-row">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__x_files_factor($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_x_files_factor_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("X-Files Factor");?></span><br>
			<?php echo _("The percentage of data points that can be missing before the data is entered into the RRA as \"Unknown\" (must be between 0 and 1).");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "0.5", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_alpha($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_alpha_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Alpha (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight historic (0) or current (1) data has on the prediction (must be between 0 and 1).");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "0.1", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_beta($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_beta_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Beta (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight the slope of the line has on the prediction (must be between 0 and 1).");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "0.0035", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_gamma($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_gamma_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Gamma (Holt-Winters)");?></span><br>
			<?php echo _("Controls how much weight the seasonal properties of line data has on the prediction (must be between 0 and 1).");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "0.1", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_seasonal_period($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_seasonal_period_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Seasonal Period (Holt-Winters)");?></span><br>
			<?php echo _("The amount of time for each seasonal period.");?>
		</td>
		<td colspan="2" class="field-row">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_rra_num($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_rra_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_rra_num_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Dependent RRA (Holt-Winters)");?></span><br>
			<?php echo _("The amount of time for each seasonal period.");?>
		</td>
		<td colspan="2" class="field-row">
			<?php form_dropdown($field_name, api_data_preset_rra_row_type_list(), "", "", $field_value, "", "86400");?>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_threshold($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_threshold_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Threshold (Holt-Winters)");?></span><br>
			<?php echo _("The minimum number of violations that occur within a window that constitutes a failure.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "7", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_preset_rra_item__hw_window_length($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_hw_window_length_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Window Length (Holt-Winters)");?></span><br>
			<?php echo _("The number of points contained within a window. Must be greater than or equal to the threshold and less than 28.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "9", 6, 10, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

?>
