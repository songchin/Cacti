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

/* form validation functions */

function api_device_field_validate(&$_fields_device, $device_field_name_format = "|field|") {
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	if (sizeof($_fields_device) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_device = api_device_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_device)) {
		if ((isset($_fields_device[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $device_field_name_format);

			if (!form_input_validate($_fields_device[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* device fields */

function _device_field__description($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Name");?></span><br>
			<?php echo _("A identifiable name for this device.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 250, 30, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="required">(required)</span>
		</td>
	</tr>
	<?php
}

function _device_field__hostname($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("Hostname");?></span><br>
			<?php echo _("The fully qualified domain name or IP address of this device.");?>
		</td>
		<td>
			<?php form_text_box($field_name, $field_value, "", 250, 30, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="required">(required)</span>
		</td>
	</tr>
	<?php
}

function _device_field__host_template_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/device_template/device_template_info.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Device Template");?></span><br>
			<?php echo _("The type of device that you are trying to graph data for. Based upon the template that you select here, the relevant graphs will be assigned to this device.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, array_rekey(api_device_template_list(), "id", "name"), "", "", $field_value, "", "");?>
		</td>
	</tr>
	<?php
}

function _device_field__poller_id($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/poller/poller_info.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("Default Poller");?></span><br>
			<?php echo _("Choose the default poller to handle this hosts request.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, array_rekey(api_poller_list(), "id", "name"), "", "", $field_value, "", "");?>
		</td>
	</tr>
	<?php
}

function _device_field__disabled($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Disable Device");?></span><br>
			<?php echo _("Checking this box will disable all graphing and checks for this device.");?>
		</td>
		<td>
			<?php form_checkbox($field_name, $field_value, _("Disable Device"), "", $field_id);?>
		</td>
	</tr>
	<?php
}

function _device_field__snmp_version($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("SNMP Version");?></span><br>
			<?php echo _("Choose the SNMP that Cacti should use when communicating with this device. Your device must support this version for graphing to work.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, $snmp_versions, "", "", $field_value, read_config_option("snmp_ver"), 1);?>
		</td>
	</tr>
	<?php
}

function _device_field__snmp_community($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("SNMP Community");?></span><br>
			<?php echo _("Enter the SNMP read community for this device.");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_community"), 100, 30, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmp_port($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("SNMP Port");?></span><br>
			<?php echo _("Choose the UDP port that Cacti will use to communicate with the SNMP agent.");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_port"), 5, 15, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmp_timeout($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("SNMP Timeout");?></span><br>
			<?php echo _("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support).");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmp_timeout"), 5, 15, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmpv3_auth_username($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Username");?></span><br>
			<?php echo _("The username to use when authenticating against the the SNMPv3 agent.");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_auth_username"), 100, 30, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmpv3_auth_password($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("Password");?></span><br>
			<?php echo _("The password to use when authenticating against the the SNMPv3 agent.");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_auth_password"), 100, 30, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmpv3_auth_protocol($field_name, $field_value = "", $field_id = 0) {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Authentication Protocol");?></span><br>
			<?php echo _("The protocol to use when authenticating against the the SNMPv3 agent.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, $snmpv3_auth_protocol, "", "", $field_value, read_config_option("snmpv3_auth_protocol"), 1);?>
		</td>
	</tr>
	<?php
}

function _device_field__snmpv3_priv_passphrase($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field alt">
		<td width="50%">
			<span class="title"><?php echo _("Privacy Passphrase");?></span><br>
			<?php echo _("The privary passphrase to use when authenticating against the the SNMPv3 agent.");?>
		</td>
		<td colspan="2">
			<?php form_text_box($field_name, $field_value, read_config_option("snmpv3_priv_passphrase"), 100, 30, "text");?>
		</td>
	</tr>
	<?php
}

function _device_field__snmpv3_priv_protocol($field_name, $field_value = "", $field_id = 0) {
	require(CACTI_BASE_PATH . "/include/device/device_arrays.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="field">
		<td width="50%">
			<span class="title"><?php echo _("Privacy Protocol");?></span><br>
			<?php echo _("The privacy protocol to use when authenticating against the the SNMPv3 agent.");?>
		</td>
		<td colspan="2">
			<?php form_dropdown($field_name, $snmpv3_priv_protocol, "", "", $field_value, read_config_option("snmpv3_priv_protocol"), 1);?>
		</td>
	</tr>
	<?php
}

?>
