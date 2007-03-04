<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

function api_graph_template_fields_validate(&$_fields_graph_template, $graph_template_field_name_format = "|field|") {
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	if (sizeof($_fields_graph_template) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_graph_template = api_graph_template_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_graph_template)) {
		if ((isset($_fields_graph_template[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $graph_template_field_name_format);

			if (!form_input_validate($_fields_graph_template[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* graph template fields */

function _graph_template_field__template_name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("The name given to this graph template.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 150, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

?>
