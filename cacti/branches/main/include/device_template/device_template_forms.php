<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

require(CACTI_BASE_PATH . "/include/device/device_arrays.php");

/* file: device_templates.php, action: edit */
$fields_device_template_edit = array(
	"device_header" => array(
		"method" => "spacer",
		"friendly_name" => __("General Device Template Options"),
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this device template."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "70"
		),
	"description" => array(
		"method" => "textarea",
		"friendly_name" => __("Description"),
		"description" => __("Additional details relative this template."),
		"value" => "|arg1:description|",
		"textarea_rows" => "5",
		"textarea_cols" => "60",
		"class" => "textAreaNotes"
		),
	"device_threads" => array(
		"method" => "drop_array",
		"friendly_name" => __("Number of Collection Threads"),
		"description" => __("The number of concurrent threads to use for polling this device type.  This applies to the Spine poller only."),
		"value" => "|arg1:device_threads|",
		"default" => "1",
		"array" => $device_threads
		),
	"image" => array(
		"method" => "drop_image",
		"path" => "images/tree_icons",
		"friendly_name" => __("Image"),
		"description" => __("A useful icon to use to associate with this Device Template."),
		"width" => "120",
		"default" => "device.gif",
		"value" => "|arg1:image|"
		),
	"override_defaults" => array(
		"method" => "checkbox",
		"friendly_name" => __("Template Based Availability and SNMP"),
		"description" => __("Check this box to have the Device Template control the defaults for Availability and SNMP Settings."),
		"value" => "|arg1:override_defaults|",
		"default" => "",
		"form_id" => "|arg1:id|"
		),
	"override_permitted" => array(
		"method" => "checkbox",
		"friendly_name" => __("Allow Override"),
		"description" => __("Check this box to have the allow the user to override the Device Template Availability and SNMP Settings.") .
						__("If unchecked, the user will not be able to change either Availability or SNMP settings when editing the device.") .
						__("However, for legacy purposes, this will only apply to new devices and legacy devices where the user has requested that template propagation be enabled."),
		"value" => "|arg1:override_permitted|",
		"default" => "on",
		"form_id" => "|arg1:id|"
		),
	"availability_header" => array(
		"method" => "spacer",
		"friendly_name" => __("Availability/Reachability Options"),
		),
	"availability_method" => array(
		"friendly_name" => __("Downed Device Detection"),
		"description" => __("The method Cacti will use to determine if a device is available for polling.") . "<br><i>" .
						__("NOTE: It is recommended that, at a minimum, SNMP always be selected.") . "</i>",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:availability_method|",
		"method" => "drop_array",
		"default" => read_config_option("availability_method"),
		"array" => $availability_options
		),
	"ping_method" => array(
		"friendly_name" => __("Ping Method"),
		"description" => __("The type of ping packet to sent.") . "<br>" .
						"<i>" . __("NOTE:") . " " . __("ICMP on Linux/UNIX requires root privileges.") . "</i>",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:ping_method|",
		"method" => "drop_array",
		"default" => read_config_option("ping_method"),
		"array" => $ping_methods
		),
	"ping_port" => array(
		"method" => "textbox",
		"friendly_name" => __("Ping Port"),
		"value" => "|arg1:ping_port|",
		"description" => __("TCP or UDP port to attempt connection."),
		"default" => read_config_option("ping_port"),
		"max_length" => "50",
		"size" => "15"
		),
	"ping_timeout" => array(
		"friendly_name" => __("Ping Timeout Value"),
		"description" => __("The timeout value to use for device ICMP and UDP pinging. This device SNMP timeout value applies for SNMP pings."),
		"method" => "textbox",
		"value" => "|arg1:ping_timeout|",
		"default" => read_config_option("ping_timeout"),
		"max_length" => "10",
		"size" => "15"
		),
	"ping_retries" => array(
		"friendly_name" => __("Ping Retry Count"),
		"description" => __("The number of times Cacti will attempt to ping a device before failing."),
		"method" => "textbox",
		"value" => "|arg1:ping_retries|",
		"default" => read_config_option("ping_retries"),
		"max_length" => "10",
		"size" => "15"
		),
	"snmp_spacer" => array(
		"method" => "spacer",
		"friendly_name" => __("SNMP Options"),
		),
	"snmp_version" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Version"),
		"description" => __("Choose the SNMP version for this device."),
		"on_change" => "changeHostForm()",
		"value" => "|arg1:snmp_version|",
		"default" => read_config_option("snmp_ver"),
		"array" => $snmp_versions,
		),
	"snmp_community" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Community"),
		"description" => __("SNMP read community for this device."),
		"value" => "|arg1:snmp_community|",
		"form_id" => "|arg1:id|",
		"default" => read_config_option("snmp_community"),
		"max_length" => "100",
		"size" => "15"
		),
	"snmp_username" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Username (v3)"),
		"description" => __("SNMP v3 username for this device."),
		"value" => "|arg1:snmp_username|",
		"default" => read_config_option("snmp_username"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_password" => array(
		"method" => "textbox_password",
		"friendly_name" => __("SNMP Password (v3)"),
		"description" => __("SNMP v3 password for this device."),
		"value" => "|arg1:snmp_password|",
		"default" => read_config_option("snmp_password"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_auth_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Auth Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Authorization Protocol."),
		"value" => "|arg1:snmp_auth_protocol|",
		"default" => read_config_option("snmp_auth_protocol"),
		"array" => $snmp_auth_protocols,
		),
	"snmp_priv_passphrase" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Privacy Passphrase (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Passphrase."),
		"value" => "|arg1:snmp_priv_passphrase|",
		"default" => read_config_option("snmp_priv_passphrase"),
		"max_length" => "200",
		"size" => "40"
		),
	"snmp_priv_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Privacy Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Protocol."),
		"value" => "|arg1:snmp_priv_protocol|",
		"default" => read_config_option("snmp_priv_protocol"),
		"array" => $snmp_priv_protocols,
		),
	"snmp_context" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Context"),
		"description" => __("Enter the SNMP Context to use for this device."),
		"value" => "|arg1:snmp_context|",
		"default" => "",
		"max_length" => "64",
		"size" => "25"
		),
	"snmp_port" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Port"),
		"description" => __("Enter the UDP port number to use for SNMP (default is 161)."),
		"value" => "|arg1:snmp_port|",
		"max_length" => "5",
		"default" => read_config_option("snmp_port"),
		"size" => "15"
		),
	"snmp_timeout" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Timeout"),
		"description" => __("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support)."),
		"value" => "|arg1:snmp_timeout|",
		"max_length" => "8",
		"default" => read_config_option("snmp_timeout"),
		"size" => "15"
		),
	"max_oids" => array(
		"method" => "textbox",
		"friendly_name" => __("Maximum OID's Per Get Request"),
		"description" => __("Specified the number of OID's that can be obtained in a single SNMP Get request."),
		"value" => "|arg1:max_oids|",
		"max_length" => "8",
		"default" => read_config_option("max_get_size"),
		"size" => "15"
		),
	);
