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

function api_data_preset_color_field_validate(&$_fields_data_preset_color, $data_preset_color_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_color_info.php");

	if (sizeof($_fields_data_preset_color) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_preset_color = api_data_preset_color_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_preset_color)) {
		if ((isset($_fields_data_preset_color[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_preset_color_field_name_format);

			if (!form_input_validate($_fields_data_preset_color[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/*
 * COLOR PRESET FIELDS
 */

function _data_preset_color__hex($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Hex");?></span><br>
			<?php echo _("The hex value for this color; valid range: 000000-FFFFFF.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 6, 20, "text", $field_id);?>
		</td>
		<td align="right" class="field-row">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

?>
