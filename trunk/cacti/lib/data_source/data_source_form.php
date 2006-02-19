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
function api_data_source_fields_validate(&$_fields_data_source, &$_fields_suggested_values, $data_source_field_name_format = "|field|", $suggested_values_field_name_format = "") {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (sizeof($_fields_data_source) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_source = api_data_source_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_source)) {
		if ((isset($_fields_data_source[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_source_field_name_format);

			if (!form_input_validate($_fields_data_source[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	/* suggested values */
	while (list($_field_name, $_sv_array) = each($_fields_suggested_values)) {
		if ((isset($fields_data_source[$_field_name])) && (isset($fields_data_source[$_field_name]["validate_regexp"])) && (isset($fields_data_source[$_field_name]["validate_empty"]))) {
			while (list($_sv_seq, $_sv_arr) = each($_sv_array)) {
				$form_field_name = str_replace("|field|", $_field_name, str_replace("|id|", $_sv_arr["id"], $suggested_values_field_name_format));

				if (!form_input_validate($_sv_arr["value"], $form_field_name, $fields_data_source[$_field_name]["validate_regexp"], $fields_data_source[$_field_name]["validate_empty"])) {
					$error_fields[] = $form_field_name;
				}
			}
		}
	}

	return $error_fields;
}

function api_data_source_input_fields_validate(&$_fields_data_input, $data_input_field_name_format) {
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	/* array containing errored fields */
	$error_fields = array();

	/* data input fields */
	if (isset($_fields_data_source["data_input_type"])) {
		reset($_fields_data_input);
		while (list($_field_name, $_field_array) = each($_fields_data_input)) {
			$form_field_name =  str_replace("|field|", $_field_name, $data_input_field_name_format);

			if (($_fields_data_source["data_input_type"] == DATA_INPUT_TYPE_SCRIPT) && (isset($_fields_data_input["script_id"])) && ($_field_name != "script_id")) {
				$script_input_field = db_fetch_row("select id,regexp_match,allow_empty from data_input_fields where data_input_id = " . $_fields_data_input["script_id"]["value"] . " and data_name = '$_field_name' and input_output = 'in'");

				if (isset($script_input_field["id"])) {
					if (!form_input_validate($_field_array["value"], $form_field_name, $script_input_field["regexp_match"], $script_input_field["allow_empty"])) {
						$error_fields[] = $form_field_name;
					}
				}
			}else if (($data_input_type == DATA_INPUT_TYPE_DATA_QUERY) && ($_field_name == "data_query_field_name")) {
				if (!form_input_validate($value, $form_field_name, "", false)) {
					$error_fields[] = $form_field_name;
				}
			}else if (($data_input_type == DATA_INPUT_TYPE_DATA_QUERY) && ($_field_name == "data_query_field_value")) {
				if (!form_input_validate($value, $form_field_name, "", false)) {
					$error_fields[] = $form_field_name;
				}
			}
		}
	}

	return $error_fields;
}

function api_data_source_item_fields_validate(&$_fields_data_source_item, $data_source_item_field_name_format) {
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (sizeof($_fields_data_source_item) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_data_source_item = api_data_source_item_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_data_source_item)) {
		if ((isset($_fields_data_source_item[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $data_source_item_field_name_format);

			if (!form_input_validate($_fields_data_source_item[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* data source input fields */

function _data_source_input_field__data_input_type($field_name, $template_flag = false, $field_value = "", $field_id = 0) {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
	require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	if ($template_flag == true) {
		$redirect_url = "data_templates.php?action=edit" . (!empty($field_id) ? "&id=" . $field_id : "") . "&data_input_type=|dropdown_value|";
	}else{
		$redirect_url = "data_sources.php?action=edit" . (!empty($field_id) ? "&id=" . $field_id : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=|dropdown_value|";
	}

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Input Type");?></span><br>
			<?php echo _("Where this data source should get its input data.");?>
		</td>
		<td>
			<?php form_dropdown($field_name, $data_input_types, "", "", $field_value, "", DATA_INPUT_TYPE_SCRIPT, "", 0, "submit_redirect(\"0\", \"" . htmlspecialchars($redirect_url) . "\", document.forms[0].$field_name.options[document.forms[0].$field_name.selectedIndex].value)");?>
		</td>
	</tr>
	<?php

	form_hidden_box("cacti_js_dropdown_redirect_x", "", "");
}

function _data_source_input_field__script_id($field_name, $redirect_url, $field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Script");?></span><br>
			<?php echo _("The script/source used to gather data for this data source.");?>
		</td>
		<td>
			<?php form_dropdown($field_name, db_fetch_assoc("select id,name from data_input order by name"), "name", "id", $field_value, "", "", "", 0, "submit_redirect(\"0\", \"" . htmlspecialchars($redirect_url) . "\", document.forms[0].$field_name.options[document.forms[0].$field_name.selectedIndex].value)");?>
		</td>
	</tr>
	<?php

	form_hidden_box("cacti_js_dropdown_redirect_x", "", "");
}

function _data_source_input_field__data_query_id($field_name, $redirect_url, $field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Query");?></span><br>
			<?php echo _("Choose the data query to use for retrieving data for this data source.");?>
		</td>
		<td>
			<?php form_dropdown($field_name, db_fetch_assoc("select id,name from data_query order by name"), "name", "id", $field_value, "", "", "", 0, "submit_redirect(\"0\", \"" . htmlspecialchars($redirect_url) . "\", document.forms[0].$field_name.options[document.forms[0].$field_name.selectedIndex].value)");?>
		</td>
	</tr>
	<?php

	form_hidden_box("cacti_js_dropdown_redirect_x", "", "");
}

/* data source input fields (data query) */

function _data_source_input_field__data_query_hdr() {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	field_row_header(_("Data Query Parameters"));
}

function _data_source_input_field__data_query_field_name($field_name, $data_query_id, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Field Name");?></span><br>
			<?php echo _("Determines the field that Cacti will use when locating a unique row for this data query."); ?>
		</td>
		<td>
			<?php form_dropdown($field_name, db_fetch_assoc("select field_name as name,field_name as id from host_data_query_cache where data_query_id = " . sql_sanitize($data_query_id) . " group by field_name"), "name", "id", $field_value, "", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_input_field__data_query_field_value($field_name,  $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Field Value");?></span><br>
			<?php echo _("When assigned to the field name above, produces a single data query row used by th)e poller to retrieve data.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 0, 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

/* data source input fields (script) */

function _data_source_input_field__script($field_name, $friendly_name, $template_flag = false, $field_value = "", $t_field_name = "", $t_field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo $friendly_name;?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

/* data source input fields (device) */

function _data_source_input_field__device_hdr_generic() {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	field_row_header(_("SNMP (Generic Options)"));
}

function _data_source_input_field__device_hdr_snmpv12() {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	field_row_header(_("SNMP (v1/v2c Options)"));
}

function _data_source_input_field__device_hdr_snmpv3() {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	field_row_header(_("SNMP (v3 Options)"));
}

function _data_source_input_field__device_snmp_port($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("SNMP Port");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("Enter the UDP port number to use for SNMP (default is 161).");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_port"), 5, 15, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmp_timeout($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("SNMP Timeout");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support).");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_timeout"), 8, 15, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmp_version($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("SNMP Version");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("Choose the SNMP version for this host.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $snmp_versions, "", "", $field_value, read_config_option("snmp_ver"), 1);?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmp_community($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("SNMP Community");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("Fill in the SNMP read community for this device.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_community"), 100, 30, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmpv3_auth_username($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Username");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("The default SNMP v3 username.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_auth_username"), 100, 30, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmpv3_auth_password($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Password");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("The default SNMP v3 password.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_auth_password"), 100, 30, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmpv3_auth_protocol($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Authentication Protocol");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("Select the default SNMP v3 authentication protocol to use.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $snmpv3_auth_protocol, "", "", $field_value, read_config_option("snmpv3_auth_protocol"), 1);?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmpv3_priv_passphrase($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Privacy Passphrase");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("The default SNMP v3 privacy passphrase.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_priv_passphrase"), 100, 30, "text", ($o_field_value == "on" ? $field_id : 0));?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

function _data_source_input_field__device_snmpv3_priv_protocol($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_value, $o_field_value) {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Privacy Protocol");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox("o_$field_name", $o_field_value, _("Override Device Field"), "", $field_id, "set_data_template_override_device_field(\"$field_name\")"); echo "<br>";
				form_checkbox("t_$field_name", $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"t_$field_name\")");
			}else{
				echo _("Select the default SNMP v3 privacy protocol to use.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $snmpv3_priv_protocol, "", "", $field_value, read_config_option("snmpv3_priv_protocol"), 1);?>
		</td>
	</tr>
	<?php if ($template_flag == true) { ?>
	<script language="JavaScript">
	template_checkbox_status("<?php echo $field_name;?>","t_<?php echo $field_name;?>");
	set_data_template_override_device_field("<?php echo $field_name;?>");
	</script>
	<?php }
}

/* data source fields */

function _data_source_field__data_template_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Selected Data Template");?></span><br>
			<?php echo _("The data template associated with this data source."); ?>
		</td>
		<td>
			<?php form_dropdown($field_name, db_fetch_assoc("select id,template_name as name from data_template order by template_name"), "name", "id", $field_value, "None", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_field__host_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Device");?></span><br>
			<?php echo _("Choose the device that this data source belongs to."); ?>
		</td>
		<td>
			<?php form_dropdown($field_name, db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"), "name", "id", $field_value, "None", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_field__name($field_name, $template_flag = false, $field_id = 0, $t_field_name = "", $t_field_value = "") {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	if (empty($field_id)) {
		$values_array = array();
	}else{
		$values_array = array_rekey(db_fetch_assoc("select value,id from data_template_suggested_value where data_template_id = " . $field_id . " and field_name = 'name' order by sequence"), "id", "value");
	}

	if ($template_flag == true) {
		$url_moveup = "javascript:document.forms[0].action.value='sv_moveup';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_moveup&id=|id|" . (empty($field_id) ? "" : "&data_template_id=" . $field_id)) . "', '')";
		$url_movedown = "javascript:document.forms[0].action.value='sv_movedown';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_movedown&id=|id|" . (empty($field_id) ? "" : "&data_template_id=" . $field_id)) . "', '')";
		$url_delete = "javascript:document.forms[0].action.value='sv_remove';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_remove&id=|id|" . (empty($field_id) ? "" : "&data_template_id=" . $field_id)) . "', '')";
		$url_add = "javascript:document.forms[0].action.value='sv_add';submit_redirect(0, '" . htmlspecialchars("data_templates.php?action=sv_add" . (empty($field_id) ? "" : "&id=" . $field_id)) . "', '')";
	}else{
		if (empty($field_id)) {
			$field_value = "";
		}else{
			$field_value = db_fetch_cell("select name from data_source where id = $field_id");
		}
	}

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Choose a name for this data source.");
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

function _data_source_field__rrd_path($field_name, $template_flag = false, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Source Path");?></span><br>
			<?php echo _("The full path to the RRD file.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_field__rra_id($field_name, $template_flag = false, $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Associated RRA's");?></span><br>
			<?php
			if ($template_flag == false) {
				echo _("Which RRA's to use when entering data. (It is recommended that you select all of these values).");
			}
			?>
		</td>
		<td>
			<?php
			if (empty($field_id)) {
				$current_value = db_fetch_assoc("select rra.id from rra order by id");
			}else if ($template_flag == true) {
				$current_value = db_fetch_assoc("select rra_id as id,data_template_id from data_template_rra where data_template_id = " . sql_sanitize($field_id));
			}else{
				$current_value = db_fetch_assoc("select rra_id as id,data_source_id from data_source_rra where data_source_id = " . sql_sanitize($field_id));
			}

			form_multi_dropdown($field_name, array_rekey(db_fetch_assoc("select id,name from rra order by name"), "id", "name"), $current_value, "id");?>
		</td>
	</tr>
	<?php
}

function _data_source_field__rrd_step($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Step");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The amount of time in seconds between expected updates.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 300, 10, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_field__active($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Active");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("Whether Cacti should gather data for this data source or not.");
			}
			?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Data Source Active"), "on", $field_id);?>
		</td>
	</tr>
	<?php
	form_checkbox_marker($field_name);
}

/* data source item fields */

function _data_source_item_field__data_source_name($field_name, $template_flag = false, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Internal Data Source Name");?></span><br>
			<?php
			if ($template_flag == false) {
				echo _("Choose unique name to represent this piece of data inside of the rrd file.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 19, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_item_field__rrd_minimum($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Minimum Value");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The minimum value of data that is allowed to be collected.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 0, 20, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_item_field__rrd_maximum($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Maximum Value");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The maximum value of data that is allowed to be collected.");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 0, 20, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _data_source_item_field__data_source_type($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Data Source Type");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("How data is represented in the RRA.");
			}
			?>
		</td>
		<td>
			<?php form_dropdown($field_name, $data_source_types, "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

function _data_source_item_field__rrd_heartbeat($field_name, $template_flag = false, $field_value = "", $field_id = 0, $t_field_name = "", $t_field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr bgcolor="#<?php echo field_get_row_color();?>">
		<td width="50%">
			<span class="textEditTitle"><?php echo _("Heartbeat");?></span><br>
			<?php
			if ($template_flag == true) {
				form_checkbox($t_field_name, $t_field_value, _("Do Not Template this Field"), "", $field_id, "template_checkbox_status(\"$field_name\",\"$t_field_name\")");
			}else{
				echo _("The maximum amount of time that can pass before data is entered as \"unknown\". (Usually 2x300=600)");
			}
			?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, 600, 20, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

?>