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

include(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
include(CACTI_BASE_PATH . "/include/config_form.php");

/* file: data_templates.php, action: template_edit */
$fields_data_template_template_edit = array(
	"template_name" => array(
		"method" => "textbox",
		"friendly_name" => "Name",
		"description" => "The name given to this data template.",
		"value" => "|arg1:template_name|",
		"max_length" => "150",
		),
	"data_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: (data_sources.php|data_templates.php), action: edit */
$struct_data_input = array(
	"data_input_type" => array(
		"friendly_name" => "Data Input Type",
		"redirect_url" => "",
		"form_index" => "",
		"method" => "drop_array_js",
		"array" => $data_input_types,
		"value" => "|arg1:data_input_type|",
		"description" => "Where this data source should get its input data."
		)
	);

/* file: (data_sources.php|data_templates.php), action: edit */
$struct_data_source = array(
	"name" => array(
		"friendly_name" => "Name",
		"method" => "textbox",
		"value" => "|arg1:name|",
		"url_moveup" => "",
		"url_movedown" => "",
		"url_delete" => "",
		"url_add" => "",
		"max_length" => "255",
		"description" => "Choose a name for this data source."
		),
	"rrd_path" => array(
		"friendly_name" => "Data Source Path",
		"method" => "textbox",
		"value" => "|arg1:rrd_path|",
		"form_id" => "|arg1:id|",
		"max_length" => "255",
		"default" => "",
		"description" => "The full path to the RRD file.",
		"flags" => "NOTEMPLATE"
		),
	"rra_id" => array(
		"method" => "drop_multi_rra",
		"friendly_name" => "Associated RRA's",
		"description" => "Which RRA's to use when entering data. (It is recommended that you select all of these values).",
		"form_id" => "|arg1:id|",
		"sql" => "",
		"sql_all" => "select rra.id from rra order by id",
		"sql_print" => "",
		"flags" => "ALWAYSTEMPLATE"
		),
	"rrd_step" => array(
		"friendly_name" => "Step",
		"method" => "textbox",
		"form_id" => "|arg1:id|",
		"value" => "|arg1:rrd_step|",
		"max_length" => "10",
		"size" => "20",
		"default" => "300",
		"description" => "The amount of time in seconds between expected updates.",
		"flags" => ""
		),
	"active" => array(
		"friendly_name" => "Data Source Active",
		"method" => "checkbox",
		"value" => "|arg1:active|",
		"form_id" => "|arg1:id|",
		"default" => "on",
		"description" => "Whether Cacti should gather data for this data source or not.",
		"flags" => ""
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source_item = array(
	"data_source_name" => array(
		"friendly_name" => "Internal Data Source Name",
		"method" => "textbox",
		"max_length" => "19",
		"default" => "",
		"description" => "Choose unique name to represent this piece of data inside of the rrd file."
		),
	"rrd_minimum" => array(
		"friendly_name" => "Minimum Value",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"description" => "The minimum value of data that is allowed to be collected."
		),
	"rrd_maximum" => array(
		"friendly_name" => "Maximum Value",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"description" => "The maximum value of data that is allowed to be collected."
		),
	"data_source_type" => array(
		"friendly_name" => "Data Source Type",
		"method" => "drop_array",
		"array" => $data_source_types,
		"default" => "",
		"description" => "How data is represented in the RRA."
		),
	"rrd_heartbeat" => array(
		"friendly_name" => "Heartbeat",
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "600",
		"description" => "The maximum amount of time that can pass before data is entered as \"unknown\".
			(Usually 2x300=600)"
		)
	);

/* data input type-specific fields (these fields are at the bottom of this file for
 * array inheritance reasons */
$struct_data_input_script = array(
	"hdr_script" => array(
		"friendly_name" => "External Script",
		"method" => "spacer"
		),
	"dif_script_id" => array(
		"friendly_name" => "Script",
		"redirect_url" => "",
		"method" => "drop_array_js",
		"array" => array(),
		"sql" => "select id,name from data_input order by name",
		"description" => "The script/source used to gather data for this data source."
		)
	);

$struct_data_input_snmp = array(
	"hdr_snmp_generic" => array(
		"friendly_name" => "SNMP (Generic Options)",
		"method" => "spacer"
		),
	"dif_snmp_port" => $fields_host_edit["snmp_port"],
	"dif_snmp_timeout" => $fields_host_edit["snmp_timeout"],
	"dif_snmp_version" => $fields_host_edit["snmp_version"],
	"hdr_snmp_v12" => array(
		"friendly_name" => "SNMP (v1/v2c Options)",
		"method" => "spacer"
		),
	"dif_snmp_community" => $fields_host_edit["snmp_community"],
	"hdr_snmp_v3" => array(
		"friendly_name" => "SNMP (v3 Options)",
		"method" => "spacer"
		),
	"dif_snmpv3_auth_username" => $fields_host_edit["snmpv3_auth_username"],
	"dif_snmpv3_auth_password" => $fields_host_edit["snmpv3_auth_password"],
	"dif_snmpv3_auth_protocol" => $fields_host_edit["snmpv3_auth_protocol"],
	"dif_snmpv3_priv_passphrase" => $fields_host_edit["snmpv3_priv_passphrase"],
	"dif_snmpv3_priv_protocol" => $fields_host_edit["snmpv3_priv_protocol"]
	);

$struct_data_input_data_query = array(
	"hdr_data_query" => array(
		"friendly_name" => "Data Query",
		"method" => "spacer"
		),
	"dif_data_query_id" => array(
		"redirect_url" => "",
		"method" => "drop_array_js",
		"array" => array(),
		"friendly_name" => "Data Query",
		"description" => "Choose the data query to use for retrieving data for this data source.",
		"sql" => "select id,name from snmp_query order by name",
		),
	);

?>
