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

function api_data_template_save($_fields_data_source, $_fields_suggested_values, $_fields_data_input, $_fields_rra_id) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	/* keep the template hash fresh */
	$_fields_data_source["hash"] = get_hash_data_template($_fields_data_source["id"]);

	$data_template_id = sql_save($_fields_data_source, "data_template");

	if ($data_template_id) {
		/* save all suggested value fields */
		while (list($field_name, $field_array) = each($_fields_suggested_values)) {
			while (list($id, $value) = each($field_array)) {
				if (empty($id)) {
					db_execute("insert into data_template_suggested_value (hash,data_template_id,field_name,value,sequence) values ('',$data_template_id,'$field_name','$value'," . seq_get_current(0, "sequence", "data_template_suggested_value", "data_template_id = $data_template_id and field_name = '$field_name'") . ")");
				}else{
					db_execute("update data_template_suggested_value set value = '$value' where id = $id");
				}
			}
		}

		db_execute("delete from data_template_field where data_template_id = $data_template_id");

		/* save all data input fields */
		while (list($name, $field_array) = each($_fields_data_input)) {
			db_execute("insert into data_template_field (data_template_id,name,t_value,value) values ($data_template_id,'$name'," . html_boolean($field_array["t_value"]) . ",'" . $field_array["value"] . "')");
		}

		/* save entries in 'selected rras' field */
		db_execute("delete from data_template_rra where data_template_id = $data_template_id");

		for ($i=0; ($i < count($_fields_rra_id)); $i++) {
			db_execute("insert into data_template_rra (rra_id,data_template_id) values (" . $_fields_rra_id[$i] . ",$data_template_id)");
		}

		/* push out data template fields */
		api_data_template_propagate($data_template_id);
	}

	return $data_template_id;
}

function api_data_template_item_save($_fields_data_source_item) {
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");

	/* keep the template hash fresh */
	$_fields_data_source_item["hash"] = get_hash_data_template($_fields_data_source_item["id"], "data_template_item");

	$data_template_item_id = sql_save($_fields_data_source_item, "data_template_item");

	if ($data_template_item_id) {
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
