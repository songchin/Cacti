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

/* form validation functions */

function validate_data_query_fields(&$_fields_data_query, $data_query_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_list.php");

	if (sizeof($_fields_data_query) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_query = api_data_query_list_fields();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_query)) {
		if ((isset($_fields_data_query[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_query_field_name_format);

			if (!form_input_validate($_fields_data_query[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function validate_data_query_field_fields(&$_fields_data_query_field, $data_query_field_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_list.php");

	if (sizeof($_fields_data_query_field) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_query_fields = api_data_query_fields_list_fields();

	/* if enough field values are available, additional validation checks can be made */
	if ((isset($_fields_data_query_field["method_type"])) && (isset($_fields_data_query_field["method_value"])) && ($_fields_data_query_field["method_type"] == DATA_QUERY_FIELD_METHOD_VALUE_PARSE)) {
		$fields_data_query_fields["method_value"]["validate_empty"] = false;
	}else if ((isset($_fields_data_query_field["method_type"])) && (isset($_fields_data_query_field["method_value"])) && ($_fields_data_query_field["method_type"] == DATA_QUERY_FIELD_METHOD_OID_OCTET)) {
		$fields_data_query_fields["method_value"]["validate_empty"] = false;
		$fields_data_query_fields["method_value"]["validate_regexp"] = "^[0-9]+$";
	}else if ((isset($_fields_data_query_field["method_type"])) && (isset($_fields_data_query_field["method_value"])) && ($_fields_data_query_field["method_type"] == DATA_QUERY_FIELD_METHOD_OID_PARSE)) {
		$fields_data_query_fields["method_value"]["validate_empty"] = false;
	}

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_query_fields)) {
		if ((isset($_fields_data_query_field[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_query_field_field_name_format);

			if (!form_input_validate($_fields_data_query_field[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* data query fields */

function _data_query_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A name for this data query.");?>
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

function _data_query_field__input_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_list.php");

	?>
	<script language="JavaScript">
	<!--
	function update_data_query_type_fields(value) {
		document.getElementById('row_field_snmp_header').style.display = 'none';
		document.getElementById('row_field_snmp_oid_num_rows').style.display = 'none';
		document.getElementById('row_field_script_header').style.display = 'none';
		document.getElementById('row_field_script_path').style.display = 'none';
		document.getElementById('row_field_script_server_header').style.display = 'none';
		document.getElementById('row_field_script_server_function').style.display = 'none';

		if (value == <?php echo DATA_QUERY_INPUT_TYPE_SNMP_QUERY;?>) {
			document.getElementById('row_field_snmp_header').style.display = 'table-row';
			document.getElementById('row_field_snmp_oid_num_rows').style.display = 'table-row';
		}

		if ((value == <?php echo DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY;?>) || (value == <?php echo DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY;?>)) {
			document.getElementById('row_field_script_header').style.display = 'table-row';
			document.getElementById('row_field_script_path').style.display = 'table-row';
		}

		if (value == <?php echo DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY;?>) {
			document.getElementById('row_field_script_server_header').style.display = 'table-row';
			document.getElementById('row_field_script_server_function').style.display = 'table-row';
		}
	}
	-->
	</script>

	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Input Type");?></span><br>
			<?php echo _("Specifies how data is to be retrieved for this data query.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_data_query_list_input_types(), "", "", $field_value, "", DATA_QUERY_INPUT_TYPE_SNMP_QUERY, "", "", "update_data_query_type_fields(this.value)");?>
		</td>
	</tr>
	<?php
}

function _data_query_field__index_order_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_list.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Index Order Method");?></span><br>
			<?php echo _("Specifies how data is to be retrieved for this data query.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_data_query_list_index_sort_types(), "", "", $field_value, "", DATA_QUERY_INDEX_SORT_TYPE_ALPHABETIC);?>
		</td>
	</tr>
	<?php
}

function _data_query_field__index_title_format($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Index Title Format");?></span><br>
			<?php echo _("A name for this data query.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field__field_specific_hdr() {
	?>
	<tr>
		<td class="content-header-sub" colspan="3">
			Data Query Field Specific
		</td>
	</tr>
	<?php
}

function _data_query_field__index_order($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Valid Index Order");?></span><br>
			<?php echo _("A name for this data query.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field__index_field_id($field_name, $data_query_id, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Index Field");?></span><br>
			<?php echo _("Select if the values from this input field are to be used as unique indexes for this data query.");?>
		</td>
		<td class="field-row">
			<?php form_dropdown($field_name, api_data_query_fields_list($data_query_id, DATA_QUERY_FIELD_TYPE_INPUT), "name", "id", $field_value, "(None Selected)", "");?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _data_query_field__snmp_specific_hdr() {
	?>
	<tr id="row_field_snmp_header" style="display: none;">
		<td class="content-header-sub" colspan="3">
			SNMP Specific
		</td>
	</tr>
	<?php
}

function _data_query_field__snmp_oid_num_rows($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr id="row_field_snmp_oid_num_rows" class="<?php echo field_get_row_style();?>" style="display: none;">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Number of Rows OID");?></span><br>
			<?php echo _("The value of this OID must return the actual number of rows for the data query. This field is required when using the 'Index Count Changed' data query reindex method.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field__script_specific_hdr() {
	?>
	<tr id="row_field_script_header" style="display: none;">
		<td class="content-header-sub" colspan="3">
			Script Specific
		</td>
	</tr>
	<?php
}

function _data_query_field__script_path($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr id="row_field_script_path" class="<?php echo field_get_row_style();?>" style="display: none;">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Script Path");?></span><br>
			<?php echo _("The path to the script that is to be used for this data query. If you are making use of the script server, do not include any additional commands or arguments here.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 40, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field__script_server_specific_hdr() {
	?>
	<tr id="row_field_script_server_header" style="display: none;">
		<td class="content-header-sub" colspan="3">
			Script Server Specific
		</td>
	</tr>
	<?php
}

function _data_query_field__script_server_function($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr id="row_field_script_server_function" class="<?php echo field_get_row_style();?>" style="display: none;">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Script Server Function Name");?></span><br>
			<?php echo _("The function name in your script server script that will be called by Cacti. See the documentation for information about how this function should be setup.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 40, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

/* data query field fields */

function _data_query_field_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A unique name for this field. Only alphanumeric characters are allowed.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 50, 25, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field_field__name_desc($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Friendly Name");?></span><br>
			<?php echo _("A human readable name for this field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field_field__source_snmp($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Base SNMP OID");?></span><br>
			<?php echo _("This OID will be walked to retrieve data for this field.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field_field__source_script($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Script Field Name");?></span><br>
			<?php echo _("This name will be used to reference this field when communicating with the script.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_query_field_field__method($field_value_method_type = "", $field_value_method_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	$row_class = field_get_row_style();

	if ($field_value_method_type == "") {
		$field_value_method_group = DATA_QUERY_FIELD_METHOD_GROUP_VALUE;
	}else if (($field_value_method_type == DATA_QUERY_FIELD_METHOD_VALUE) || ($field_value_method_type == DATA_QUERY_FIELD_METHOD_VALUE_PARSE)) {
		$field_value_method_group = DATA_QUERY_FIELD_METHOD_GROUP_VALUE;
	}else{
		$field_value_method_group = DATA_QUERY_FIELD_METHOD_GROUP_OID;
	}

	?>
	<script language="JavaScript">
	<!--
	function init_method_field() {
		if (get_radio_value(document.forms[0].method_group) == <?php echo DATA_QUERY_FIELD_METHOD_GROUP_VALUE;?>) {
			enable_radio_value();
		}else if (get_radio_value(document.forms[0].method_group) == <?php echo DATA_QUERY_FIELD_METHOD_GROUP_OID;?>) {
			enable_radio_oid();
		}
	}

	function enable_radio_value() {
		document.getElementById("method_type_s_3").disabled = true;
		document.getElementById("method_type_s_4").disabled = true;
		document.getElementById("method_value_s_octet").disabled = true;
		document.getElementById("method_value_s_parse").disabled = true;
		document.getElementById("tx_s_octet").className = "disabled";
		document.getElementById("tx_s_parse").className = "disabled";

		document.getElementById("method_type_v_1").disabled = false;
		document.getElementById("method_type_v_2").disabled = false;

		if (get_radio_value(document.forms[0].method_type_v) == <?php echo DATA_QUERY_FIELD_METHOD_VALUE;?>) {
			enable_radio_v_value()
		}else if (get_radio_value(document.forms[0].method_type_v) == <?php echo DATA_QUERY_FIELD_METHOD_VALUE_PARSE;?>) {
			enable_radio_v_parse();
		}

		document.getElementById("tx_v_value").className = "enabled";
		document.getElementById("tx_v_parse").className = "enabled";
	}

	function enable_radio_oid() {
		document.getElementById("method_type_s_3").disabled = false;
		document.getElementById("method_type_s_4").disabled = false;

		if (get_radio_value(document.forms[0].method_type_s) == <?php echo DATA_QUERY_FIELD_METHOD_OID_OCTET;?>) {
			enable_radio_s_octet();
		}else if (get_radio_value(document.forms[0].method_type_s) == <?php echo DATA_QUERY_FIELD_METHOD_OID_PARSE;?>) {
			enable_radio_s_parse();
		}

		document.getElementById("tx_s_octet").className = "enabled";
		document.getElementById("tx_s_parse").className = "enabled";

		document.getElementById("method_type_v_1").disabled = true;
		document.getElementById("method_type_v_2").disabled = true;
		document.getElementById("method_value_v_parse").disabled = true;
		document.getElementById("tx_v_value").className = "disabled";
		document.getElementById("tx_v_parse").className = "disabled";
	}

	function enable_radio_v_value() {
		document.getElementById("method_value_v_parse").disabled = true;
	}

	function enable_radio_v_parse() {
		document.getElementById("method_value_v_parse").disabled = false;
	}

	function enable_radio_s_octet() {
		document.getElementById("method_value_s_octet").disabled = false;
		document.getElementById("method_value_s_parse").disabled = true;
	}

	function enable_radio_s_parse() {
		document.getElementById("method_value_s_octet").disabled = true;
		document.getElementById("method_value_s_parse").disabled = false;
	}

	-->
	</script>

	<tr class="<?php echo $row_class;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Retrieval Method");?></span><br>
			<?php echo _("Determines how data from this field is derived from walking the base SNMP OID.");?>
		</td>
		<td class="field-row" colspan="2">
			<table width="100%" cellpadding="1" cellspacing="0">
				<tr>
					<td width="1">
						<?php form_radio_button("method_group", $field_value_method_group, DATA_QUERY_FIELD_METHOD_GROUP_VALUE, "", DATA_QUERY_FIELD_METHOD_GROUP_VALUE, "enable_radio_value()");?>
					</td>
					<td colspan="2">
						<span id="tx_v">Value</span>
					</td>
				</tr>
				<tr>
					<td width="1">
					</td>
					<td width="1">
						<?php form_radio_button("method_type_v", ($field_value_method_group == DATA_QUERY_FIELD_METHOD_GROUP_OID ? DATA_QUERY_FIELD_METHOD_VALUE : $field_value_method_type), DATA_QUERY_FIELD_METHOD_VALUE, "", DATA_QUERY_FIELD_METHOD_VALUE, "enable_radio_v_value()");?>
					</td>
					<td>
						<span id="tx_v_value">Use exact value</span>
					</td>
				</tr>
				<tr>
					<td  width="1">
					</td>
					<td width="1">
						<?php form_radio_button("method_type_v", $field_value_method_type, DATA_QUERY_FIELD_METHOD_VALUE_PARSE, "", DATA_QUERY_FIELD_METHOD_VALUE, "enable_radio_v_parse()");?>
					</td>
					<td>
						<span id="tx_v_parse">Parse using regular expression</span>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom: 3px;"  width="1">
					</td>
					<td style="padding-bottom: 3px;" width="1">
					</td>
					<td style="padding-bottom: 3px;">
						<?php form_text_box("method_value_v_parse", ($field_value_method_type == DATA_QUERY_FIELD_METHOD_VALUE_PARSE ? $field_value_method_value : ""), "", 150, 30, "text", $field_id, "small");?>
					</td>
				</tr>
				<tr>
					<td class="sfield-row" width="1" style="padding-top: 3px;">
						<?php form_radio_button("method_group", $field_value_method_group, DATA_QUERY_FIELD_METHOD_GROUP_OID, "", DATA_QUERY_FIELD_METHOD_GROUP_VALUE, "enable_radio_oid()");?>
					</td>
					<td class="sfield-row" style="padding-top: 3px;" colspan="2">
						<span id="tx_s">SNMP OID</span>
					</td>
				</tr>
				<tr>
					<td  width="1">
					</td>
					<td width="1">
						<?php form_radio_button("method_type_s", ($field_value_method_group == DATA_QUERY_FIELD_METHOD_GROUP_VALUE ? DATA_QUERY_FIELD_METHOD_OID_OCTET : $field_value_method_type), DATA_QUERY_FIELD_METHOD_OID_OCTET, "", DATA_QUERY_FIELD_METHOD_OID_OCTET, "enable_radio_s_octet()");?>
					</td>
					<td>
						<span id="tx_s_octet">Last <em>n</em> octets</span>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom: 3px;"  width="1">
					</td>
					<td style="padding-bottom: 3px;" width="1">
					</td>
					<td style="padding-bottom: 3px;">
						<?php form_text_box("method_value_s_octet", ($field_value_method_type == DATA_QUERY_FIELD_METHOD_OID_OCTET ? $field_value_method_value : ""), "", 150, 10, "text", $field_id, "small");?>
					</td>
				</tr>
				<tr>
					<td  width="1">
					</td>
					<td width="1">
						<?php form_radio_button("method_type_s", $field_value_method_type, DATA_QUERY_FIELD_METHOD_OID_PARSE, "", DATA_QUERY_FIELD_METHOD_OID_OCTET, "enable_radio_s_parse()");?>
					</td>
					<td>
						<span id="tx_s_parse">Parse using regular expression</span>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom: 3px;"  width="1">
					</td>
					<td style="padding-bottom: 3px;" width="1">
					</td>
					<td style="padding-bottom: 3px;">
						<?php form_text_box("method_value_s_parse", ($field_value_method_type == DATA_QUERY_FIELD_METHOD_OID_PARSE ? $field_value_method_value : ""), "", 150, 30, "text", $field_id, "small");?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<script language="JavaScript">
	<!--
	init_method_field();
	-->
	</script>
	<?php
}



function _data_query_field_field__op_num_rows($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Total Number of Rows Field");?></span><br>
			<?php echo _("Select if the value from this field ");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_checkbox($field_name, $field_value, "This is an index field", "", $field_id);?>
		</td>
	</tr>
	<?php
}













?>
