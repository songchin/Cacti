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

function api_data_template_save($id, $template_name, $suggested_values, $data_input_type, $data_input_fields, $t_name,
	$t_active, $active, $t_rrd_step, $rrd_step, $t_rra_id, $rra_id) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_template.php");
	include_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$save["id"] = $id;
	$save["hash"] = get_hash_data_template($id);
	$save["template_name"] = form_input_validate($template_name, "template_name", "", false, 3);
	$save["data_input_type"] = form_input_validate($data_input_type, "data_input_type", "", true, 3);
	$save["t_name"] = form_input_validate(html_boolean($t_name), "t_name", "", true, 3);
	//$save["name"] = form_input_validate($name, "name", "", false, 3);
	$save["t_active"] = form_input_validate(html_boolean($t_active), "t_active", "", true, 3);
	$save["active"] = form_input_validate(html_boolean($active), "active", "", true, 3);
	$save["t_rrd_step"] = form_input_validate(html_boolean($t_rrd_step), "t_rrd_step", "", true, 3);
	$save["rrd_step"] = form_input_validate($rrd_step, "rrd_step", "^[0-9]+$", false, 3);
	$save["t_rra_id"] = form_input_validate(html_boolean($t_rra_id), "t_rra_id", "", true, 3);

	$data_template_id = 0;

	if (!is_error_message()) {
		$data_template_id = sql_save($save, "data_template");

		if ($data_template_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	/* save all suggested value fields */
	while (list($field_name, $field_array) = each($suggested_values)) {
		while (list($id, $value) = each($field_array)) {
			form_input_validate($value, "sv|$field_name|$id", "", false, 3);

			if ((!is_error_message()) && ($data_template_id)) {
				if (empty($id)) {
					db_execute("insert into data_template_suggested_value (hash,data_template_id,field_name,value,sequence) values ('',$data_template_id,'$field_name','$value'," . seq_get_current(0, "sequence", "data_template_suggested_value", "data_template_id = $data_template_id and field_name = '$field_name'") . ")");
				}else{
					db_execute("update data_template_suggested_value set value = '$value' where id = $id");
				}
			}
		}
	}

	if ((!is_error_message()) && ($data_template_id)) {
		db_execute("delete from data_template_field where data_template_id=$data_template_id");
	}

	/* save all data input fields */
	while (list($name, $field_array) = each($data_input_fields)) {
		if (($data_input_type == DATA_INPUT_TYPE_SCRIPT) && (isset($data_input_fields["script_id"])) && ($name != "script_id")) {
			$script_input_field = db_fetch_row("select id,regexp_match,allow_empty from data_input_fields where data_input_id = " . $data_input_fields["script_id"]["value"] . " and data_name = '$name' and input_output = 'in'");

			if (isset($script_input_field["id"])) {
				form_input_validate($field_array["value"], "dif_$name", $script_input_field["regexp_match"], $script_input_field["allow_empty"], 3);
			}
		}

		if ((!is_error_message()) && ($data_template_id)) {
			db_execute("insert into data_template_field (data_template_id,name,t_value,value) values ($data_template_id,'$name'," . html_boolean($field_array["t_value"]) . ",'" . $field_array["value"] . "')");
		}
	}

	if ((!is_error_message()) && ($data_template_id)) {
		/* save entries in 'selected rras' field */
		db_execute("delete from data_template_rra where data_template_id=$data_template_id");

		if (isset($rra_id)) {
			for ($i=0; ($i < count($rra_id)); $i++) {
				db_execute("insert into data_template_rra (rra_id,data_template_id) values (" . $rra_id[$i] . ",$data_template_id)");
			}
		}

		/* push out data template fields */
		api_data_template_propagate($data_template_id);
	}

	return $data_template_id;
}

function api_data_template_item_save($id, $data_template_id, $t_rrd_maximum, $rrd_maximum, $t_rrd_minimum, $rrd_minimum,
	$t_rrd_heartbeat, $rrd_heartbeat, $t_data_source_type, $data_source_type, $t_data_source_name, $data_source_name,
	$field_input_value) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_template.php");

	$save["id"] = $id;
	$save["hash"] = get_hash_data_template($id, "data_template_item");
	$save["data_template_id"] = $data_template_id;
	$save["t_rrd_maximum"] = form_input_validate(html_boolean($t_rrd_maximum), "dsi|t_rrd_maximum|$id", "", true, 3);
	$save["rrd_maximum"] = form_input_validate($rrd_maximum, "dsi|rrd_maximum|$id", "^(-?[0-9]+)|[uU]$", false, 3);
	$save["t_rrd_minimum"] = form_input_validate(html_boolean($t_rrd_minimum), "dsi|t_rrd_minimum|$id", "", true, 3);
	$save["rrd_minimum"] = form_input_validate($rrd_minimum, "dsi|rrd_minimum|$id", "^(-?[0-9]+)|[uU]$", false, 3);
	$save["t_rrd_heartbeat"] = form_input_validate(html_boolean($t_rrd_heartbeat), "dsi|t_rrd_heartbeat|$id", "", true, 3);
	$save["rrd_heartbeat"] = form_input_validate($rrd_heartbeat, "dsi|rrd_heartbeat|$id", "^[0-9]+$", false, 3);
	$save["t_data_source_type"] = form_input_validate(html_boolean($t_data_source_type), "dsi|t_data_source_type|$id", "", true, 3);
	$save["data_source_type"] = form_input_validate($data_source_type, "dsi|t_data_source_type|$id", "", true, 3);
	$save["t_data_source_name"] = form_input_validate(html_boolean($t_data_source_name), "dsi|t_data_source_name|$id", "", true, 3);
	$save["data_source_name"] = form_input_validate($data_source_name, "dsi|data_source_name|$id", "^[a-zA-Z0-9_]{1,19}$", false, 3);
	$save["field_input_value"] = form_input_validate($field_input_value, "dsi|field_input_value|$id", "", false, 3);

	$data_template_item_id = 0;

	if ((!is_error_message()) && (!empty($data_template_id))) {
		$data_template_item_id = sql_save($save, "data_template_item");

		if ($data_template_item_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	if ((!is_error_message()) && (!empty($data_template_item_id))) {
		/* push out data template item fields */
		api_data_source_item_propagate($data_template_item_id);
	}

	return $data_template_item_id;
}

function api_data_template_remove($data_template_id) {
	if ((empty($data_template_id)) || (!is_numeric($data_template_id))) {
		return;
	}

	/* detach this template from all data sources */
	db_execute("update data_source set data_template_id = 0 where data_template_id = $data_template_id");

	db_execute("delete from data_template_rra where data_template_id = $data_template_id");
	db_execute("delete from data_template_field where data_template_id = $data_template_id");
	db_execute("delete from data_template_item where data_template_id = $data_template_id");
	db_execute("delete from data_template where id = $data_template_id");
}

function api_data_template_item_remove($data_template_item_id) {
	if ((empty($data_template_item_id)) || (!is_numeric($data_template_item_id))) {
		return;
	}

	$data_template_item = db_fetch_row("select data_template_id,data_source_name from data_template_item where id = $data_template_item_id");

	$data_sources = db_fetch_assoc("select id from data_source where data_template_id = " . $data_template_item["data_template_id"]);

	/* delete all attached data source items */
	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $item) {
			db_execute("delete from data_source_item where data_source_id = " . $item["id"] . " and data_source_name = '" . $data_template_item["data_source_name"] . "'");
		}
	}

	db_execute("delete from data_template_item where id = $data_template_item_id");
}

?>
