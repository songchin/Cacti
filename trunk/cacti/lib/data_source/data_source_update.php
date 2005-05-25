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

function api_data_source_save($id, $host_id, $data_template_id, $data_input_type, $data_input_fields, $name,
	$active, $rrd_path, $rrd_step, $rra_id, $data_source_field_name_format = "ds||field|",
	$data_input_field_name_format = "dif_|field|") {

	$save["id"] = $id;
	$save["host_id"] = $host_id;
	$save["data_template_id"] = $data_template_id;
	$save["data_input_type"] = form_input_validate($data_input_type, str_replace("|field|", "data_input_type", $data_source_field_name_format), "", true, 3);
	$save["name"] = form_input_validate($name, str_replace("|field|", "name", $data_source_field_name_format), "", false, 3);
	$save["active"] = form_input_validate(html_boolean($active), str_replace("|field|", "active", $data_source_field_name_format), "", true, 3);
	$save["rrd_path"] = form_input_validate($rrd_path, str_replace("|field|", "rrd_path", $data_source_field_name_format), "", true, 3);
	$save["rrd_step"] = form_input_validate($rrd_step, str_replace("|field|", "rrd_step", $data_source_field_name_format), "^[0-9]+$", false, 3);

	$data_source_id = 0;

	if (!is_error_message()) {
		$data_source_id = sql_save($save, "data_source");

		if (!is_error_message()) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	if ((!is_error_message()) && ($data_source_id)) {
		/* save entries in 'selected rras' field */
		db_execute("delete from data_source_rra where data_source_id=$data_source_id");

		if (isset($rra_id)) {
			for ($i=0; ($i < count($rra_id)); $i++) {
				db_execute("insert into data_source_rra (rra_id,data_source_id) values (" . $rra_id[$i] . ",$data_source_id)");
			}
		}

		/* save all data input fields */
		db_execute("delete from data_source_field where data_source_id=$data_source_id");
	}

	while (list($name, $value) = each($data_input_fields)) {
		if (($data_input_type == DATA_INPUT_TYPE_SCRIPT) && (isset($data_input_fields["script_id"])) && ($name != "script_id")) {
			$script_input_field = db_fetch_row("select id,regexp_match,allow_empty from data_input_fields where data_input_id = " . $data_input_fields["script_id"] . " and data_name = '$name' and input_output = 'in'");

			if (isset($script_input_field["id"])) {
				form_input_validate($value, str_replace("|field|", $name, $data_input_field_name_format), $script_input_field["regexp_match"], $script_input_field["allow_empty"], 3);
			}
		}else if (($data_input_type == DATA_INPUT_TYPE_DATA_QUERY) && ($name == "data_query_field_name")) {
			form_input_validate($value, "dif_data_query_field_name", "", false, 3);
		}else if (($data_input_type == DATA_INPUT_TYPE_DATA_QUERY) && ($name == "data_query_field_value")) {
			form_input_validate($value, "dif_data_query_field_value", "", false, 3);
		}

		if ((!is_error_message()) && ($data_source_id)) {
			db_execute("insert into data_source_field (data_source_id,name,value) values ($data_source_id,'$name','$value')");
		}
	}

	if ($data_source_id) {
		/* update data source title cache */
		update_data_source_title_cache($data_source_id);
	}

	return $data_source_id;

}

function api_data_source_remove($data_source_id) {
	if ((empty($data_source_id)) || (!is_numeric($data_source_id))) {
		return;
	}

	db_execute("delete from data_source_field where data_source_id = $data_source_id");
	db_execute("delete from data_source_item where data_source_id = $data_source_id");
	db_execute("delete from data_source_rra where data_source_id = $data_source_id");
	db_execute("delete from data_source where id = $data_source_id");
}

function api_data_source_enable($data_source_id) {
    db_execute("UPDATE data_source SET active=1 WHERE id=$data_source_id");
	update_poller_cache($data_source_id, false);
}

function api_data_source_disable($data_source_id) {
	db_execute("DELETE FROM poller_item WHERE local_data_id=$data_source_id");
	db_execute("UPDATE data_source SET active='' WHERE id=$data_source_id");
}

function api_data_source_item_save($id, $data_source_id, $rrd_maximum, $rrd_minimum, $rrd_heartbeat, $data_source_type,
	$data_source_name, $field_input_value, $data_source_item_field_name_format = "dsi||field|||id|") {
	include_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");

	$save["id"] = $id;
	$save["data_source_id"] = $data_source_id;
	$save["rrd_maximum"] = form_input_validate($rrd_maximum, str_replace("|field|", "rrd_maximum", str_replace("|id|", $id, $data_source_item_field_name_format)), "^(-?[0-9]+)|[uU]$", false, 3);
	$save["rrd_minimum"] = form_input_validate($rrd_minimum, str_replace("|field|", "rrd_minimum", str_replace("|id|", $id, $data_source_item_field_name_format)), "^(-?[0-9]+)|[uU]$", false, 3);
	$save["rrd_heartbeat"] = form_input_validate($rrd_heartbeat, str_replace("|field|", "rrd_heartbeat", str_replace("|id|", $id, $data_source_item_field_name_format)), "^[0-9]+$", false, 3);
	$save["data_source_type"] = form_input_validate($data_source_type, str_replace("|field|", "data_source_type", str_replace("|id|", $id, $data_source_item_field_name_format)), "", true, 3);
	$save["data_source_name"] = form_input_validate($data_source_name, str_replace("|field|", "data_source_name", str_replace("|id|", $id, $data_source_item_field_name_format)), "^[a-zA-Z0-9_]{1,19}$", false, 3);

	/* the 'none' data input type does not have a data source item field value */
	if (db_fetch_cell("select data_input_type from data_source where id = $data_source_id") != DATA_INPUT_TYPE_NONE) {
		$save["field_input_value"] = form_input_validate($field_input_value, str_replace("|field|", "field_input_value", str_replace("|id|", $id, $data_source_item_field_name_format)), "", false, 3);
	}

	$data_source_item_id = 0;

	if ((!is_error_message()) && (!empty($data_source_id))) {
		$data_source_item_id = sql_save($save, "data_source_item");

		if ($data_source_item_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	if ($data_source_item_id) {
		/* since the data source path is based in part on the data source item name, it makes sense
		 * to update it here */
		update_data_source_path($data_source_id);
	}

	return $data_source_item_id;
}

function api_data_source_item_remove($data_source_item_id) {
	if ((empty($data_source_item_id)) || (!is_numeric($data_source_item_id))) {
		return;
	}

	db_execute("delete from data_source_item where id = $data_source_item_id");
}

/* update_data_source_title_cache - updates the title cache for a single data source
   @arg $data_source_id - (int) the ID of the data source to update the title cache for */
function update_data_source_title_cache($data_source_id) {
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	if (empty($data_source_id)) {
		return;
	}

	db_execute("update data_source set name_cache = '" . addslashes(get_data_source_title($data_source_id)) . "' where id = $data_source_id");
}

/* update_data_source_title_cache_from_template - updates the title cache for all data sources
	that match a given data template
   @arg $data_template_id - (int) the ID of the data template to match */
function update_data_source_title_cache_from_template($data_template_id) {
	$data = db_fetch_assoc("select local_data_id from data_template_data where data_template_id=$data_template_id and local_data_id>0");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["local_data_id"]);
	}
	}
}

/* update_data_source_title_cache_from_query - updates the title cache for all data sources
	that match a given data query/index combination
   @arg $snmp_query_id - (int) the ID of the data query to match
   @arg $snmp_index - the index within the data query to match */
function update_data_source_title_cache_from_query($snmp_query_id, $snmp_index) {
	$data = db_fetch_assoc("select id from data_local where snmp_query_id=$snmp_query_id and snmp_index='$snmp_index'");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["id"]);
	}
	}
}

/* update_data_source_title_cache_from_host - updates the title cache for all data sources
	that match a given host
   @arg $host_id - (int) the ID of the host to match */
function update_data_source_title_cache_from_host($host_id) {
	$data = db_fetch_assoc("select id from data_local where host_id=$host_id");

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		update_data_source_title_cache($item["id"]);
	}
	}
}

/* update_data_source_path - set the current data source path or generates a new one if a path
     does not already exist
   @arg $data_source_id - (int) the ID of the data source to set a path for */
function update_data_source_path($data_source_id) {
	include_once(CACTI_BASE_PATH . "/lib/string.php");

	$host_part = ""; $ds_part = "";

	/* we don't want to change the current path if one already exists */
	if ((empty($data_source_id)) || (db_fetch_cell("select rrd_path from data_source where id = $data_source_id") != "")) {
		return;
	}

	/* try any prepend the name with the host description */
	$hostname = db_fetch_cell("select host.description from host,data_source where data_source.host_id=host.id and data_source.id = $data_source_id");

	if ($hostname != "") {
		$host_part = strtolower(clean_up_name($hostname)) . "_";
	}

	/* then try and use the internal DS name to identify it */
	$data_source_item_name = db_fetch_cell("select data_source_name from data_source_item where data_source_id = $data_source_id order by id limit 1");

	if ($data_source_item_name != "") {
		$ds_part = strtolower(clean_up_name($data_source_item_name));
	}else{
		$ds_part = "ds";
	}

	$rrd_path = "<path_rra>/$host_part$ds_part" . "_" . "$data_source_id.rrd";

	/* update our changes to the db */
	db_execute("update data_source set rrd_path = '$rrd_path' where id = $data_source_id");

	return $rrd_path;
}

?>