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

function api_script_field_validate(&$_fields_script, $script_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	if (sizeof($_fields_script) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_script = api_script_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_script)) {
		if ((isset($_fields_script[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $script_field_name_format);

			if (!form_input_validate($_fields_script[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_script_field_field_validate(&$_fields_script_field, $script_field_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	if (sizeof($_fields_script_field) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_script_field = api_script_field_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_script_field)) {
		if ((isset($_fields_script_field[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $script_field_field_name_format);

			if (!form_input_validate($_fields_script_field[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_script_field_visible_field_list($script_field_type) {
	require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");

	$visible_fields = array();
	if ($script_field_type == SCRIPT_FIELD_TYPE_INPUT) {
		$visible_fields = array("field_input_type", "field_input_value", "name", "data_name", "input_output", "regexp_match", "allow_empty");
	}else if ($script_field_type == SCRIPT_FIELD_TYPE_OUTPUT) {
		$visible_fields = array("name", "data_name", "input_output", "update_rrd");
	}

	return $visible_fields;
}

/* script fields */

function _script_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A name for this script.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _script_field__type_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Input Type");?></span><br>
			<?php echo _("Choose the type of input to use when fetching data from this script.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_script_input_type_list(), "", "", $field_value, "", SCRIPT_INPUT_TYPE_SCRIPT);?>
		</td>
	</tr>
	<?php
}

function _script_field__input_string($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Input String");?></span><br>
			<?php echo _("The data that is sent to the script, which includes the complete path to the script and input fields in &lt;&gt; brackets.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 255, 40, "text", $field_id);?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

/* script field fields */

function _script_field_field__field_input_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Input Field Type");?></span><br>
			<?php echo _("Select where this data input field will obtain its value.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_script_field_input_type_list(), "", "", $field_value, "", SCRIPT_FIELD_INPUT_CUSTOM, "", "" ,"update_script_field_input_type_dropdown(this.value, $field_id)");?>
		</td>
	</tr>
	<script language="JavaScript">
	<!--
	function update_script_field_input_type_dropdown(script_field_input_type, row_id) {
		if (script_field_input_type == <?php echo SCRIPT_FIELD_INPUT_CUSTOM;?>) {
			document.getElementById('row_field_default_value_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_device_field_' + row_id).style.display = 'none';
		}else if (script_field_input_type == <?php echo SCRIPT_FIELD_INPUT_DEVICE;?>) {
			document.getElementById('row_field_default_value_' + row_id).style.display = 'none';
			document.getElementById('row_field_device_field_' + row_id).style.display = 'table-row';
		}
	}
	-->
	</script>
	<?php
}

function _script_field_field__field_input_type_js_update($field_value, $field_id = 0) {
	?>
	<script language="JavaScript">
	<!--
	update_script_field_input_type_dropdown('<?php echo $field_value;?>', '<?php echo $field_id;?>');
	-->
	</script>
	<?php
}

function _script_field_field__field_input_value_custom($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_default_value_<?php echo $field_id;?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Default Value");?></span><br>
			<?php echo _("Enter a default value for this input field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _script_field_field__field_input_value_device($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_device_field_<?php echo $field_id;?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Device Field");?></span><br>
			<?php echo _("Select the device field that will be used to populate this input field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, array_keys(api_device_form_list()), "", "", $field_value, "", "hostname");?>
		</td>
	</tr>
	<?php
}

function _script_field_field__data_name_input($field_name, $script_id, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");

	$script = api_script_get($script_id);

	/* parse out all field names listed in the input string */
	$array_field_names = array();
	if (preg_match_all("/<([_a-zA-Z0-9]+)>/", $script["input_string"], $matches)) {
		for ($i=0; ($i < count($matches[1])); $i++) {
			$array_field_names{$matches[1][$i]} = $matches[1][$i];
		}
	}

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Field Name");?></span><br>
			<?php echo _("The name that Cacti uses to refer to a field when sending data to the script.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, $array_field_names, "", "", $field_value, "", "hostname");?>
		</td>
	</tr>
	<?php
}

function _script_field_field__data_name_output($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Field Name");?></span><br>
			<?php echo _("The name that Cacti uses to identify data being returned from the script.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 50, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _script_field_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Friendly Name");?></span><br>
			<?php echo _("A human readable name for this field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _script_field_field__update_rrd($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Update RRD File");?></span><br>
			<?php echo _("Whether data from this output field is to be entered into the RRD file. Fields values that are not entered into the RRD can still be used as variables on graphs.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_checkbox($field_name, $field_value, _("Update RRD File"), "on", $field_id);?>
		</td>
	</tr>
	<?php
}

function _script_field_field__regexp_match($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Regular Expression Match");?></span><br>
			<?php echo _("If you want to require a certain regular expression to be matched againt input data, enter it here (ereg format).");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _script_field_field__allow_empty($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td class="field-row" width="50%">
			<span class="textEditTitle"><?php echo _("Allow Empty Input");?></span><br>
			<?php echo _("Whether empty input should be allowed for this field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_checkbox($field_name, $field_value, _("Allow Empty Input"), "", $field_id);?>
		</td>
	</tr>
	<?php
}

?>
