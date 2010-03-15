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

/** draw_nontemplated_fields_graph - draws a form that consists of all non-templated graph fields associated
     with a particular graph template
   @param int $graph_template_id 		- the id of the graph template to base the form after
   @param array $values_array 			- any values that should be included by default on the form
   @param string $field_name_format 	- all fields on the form will be named using the following format, the following
     									  variables can be used:
       									  |field| - the current field name
   @param string $header_title 			- the title to use on the header for this form
   @param bool $alternate_colors 		- whether to alternate colors for each row on the form or not
   @param bool $include_hidden_fields 	- should elements that are not to be displayed be represented as hidden
     									  html input elements or omitted altogether?
   @param int $snmp_query_graph_id 		- if this graph template is part of a data query, specify the graph id here. this
     									  will be used to determine if a given field is using suggested values */
function draw_nontemplated_fields_graph($graph_template_id, &$values_array, $field_name_format = "|field|", $header_title = "", $alternate_colors = true, $include_hidden_fields = true, $snmp_query_graph_id = 0) {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	$form_array = array();
	$draw_any_items = false;

	/* fetch information about the graph template */
	$graph_template = db_fetch_row("select * from graph_templates_graph where graph_template_id=$graph_template_id and local_graph_id=0");

	$struct_graph = graph_form_list();
	reset($struct_graph);
	while (list($field_name, $field_array) = each($struct_graph)) {
		/* find our field name */
		$form_field_name = str_replace("|field|", $field_name, $field_name_format);

		$form_array += array($form_field_name => $struct_graph[$field_name]);

		/* modifications to the default form array */
		$form_array[$form_field_name]["value"] = (isset($values_array[$field_name]) ? $values_array[$field_name] : "");
		$form_array[$form_field_name]["form_id"] = (isset($values_array["id"]) ? $values_array["id"] : "0");
		unset($form_array[$form_field_name]["default"]);

		if ($graph_template{"t_" . $field_name} != CHECKED) {
			if ($include_hidden_fields == true) {
				$form_array[$form_field_name]["method"] = "hidden";
			}else{
				unset($form_array[$form_field_name]);
			}
		}elseif ((!empty($snmp_query_graph_id)) && (sizeof(db_fetch_assoc("select id from snmp_query_graph_sv where snmp_query_graph_id=$snmp_query_graph_id and field_name='$field_name'")) > 0)) {
			if ($include_hidden_fields == true) {
				$form_array[$form_field_name]["method"] = "hidden";
			}else{
				unset($form_array[$form_field_name]);
			}
		}else{
			if (($draw_any_items == false) && ($header_title != "")) {
				print "<tr><td>";
				html_header(array($header_title, "&nbsp;"), 1, true, 'template_graph_' . $field_name);
			}

			$draw_any_items = true;
		}
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	draw_edit_form(
		array(
			"config" => $form_config_array,
			"fields" => $form_array
			)
		);

	if ($draw_any_items) print "</table></td></tr>";		/* end of html_header */

	return (isset($form_array) ? sizeof($form_array) : 0);
}

/** draw_nontemplated_fields_graph_item - draws a form that consists of all non-templated graph item fields
     associated with a particular graph template
   @param int $graph_template_id 	- the id of the graph template to base the form after
   @param int $local_graph_id 		- specify the id of the associated graph if it exists
   @param string $field_name_format - all fields on the form will be named using the following format, the following
     								  variables can be used:
      								  |field| - the current field name
       								  |id| - the current graph input id
   @param string $header_title 		- the title to use on the header for this form
   @param bool $alternate_colors 	- whether to alternate colors for each row on the form or not */
function draw_nontemplated_fields_graph_item($graph_template_id, $local_graph_id, $field_name_format = "|field|_|id|", $header_title = "", $alternate_colors = true) {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	$form_array = array();
	$draw_any_items = false;

	/* fetch information about the graph template */
	$input_item_list = db_fetch_assoc("SELECT *
		FROM graph_template_input
		WHERE graph_template_id=$graph_template_id
		ORDER BY column_name,name");

	/* modifications to the default graph items array */
	$struct_graph_item = graph_item_form_list();
	if (!empty($local_graph_id)) {
		$device_id = db_fetch_cell("SELECT device_id FROM graph_local WHERE id=$local_graph_id");

		$struct_graph_item["task_item_id"]["sql"] = "SELECT
			CONCAT_WS('',
			CASE
			WHEN device.description IS NULL THEN 'No Host - '
			WHEN device.description IS NOT NULL THEN ''
			end,data_template_data.name_cache,' (',data_template_rrd.data_source_name,')') AS name,
			data_template_rrd.id
			FROM (data_template_data,data_template_rrd,data_local)
			LEFT JOIN device ON (data_local.device_id=device.id)
			WHERE data_template_rrd.local_data_id=data_local.id
			AND data_template_data.local_data_id=data_local.id
			" . (empty($device_id) ? "" : " AND data_local.device_id=$device_id") . "
			ORDER BY name";
	}

	if (sizeof($input_item_list) > 0) {
		foreach ($input_item_list as $item) {
			if (!empty($local_graph_id)) {
				$current_def_value = db_fetch_row("SELECT
					graph_templates_item." . $item["column_name"] . ",
					graph_templates_item.id
					FROM (graph_templates_item,graph_template_input_defs)
					WHERE graph_template_input_defs.graph_template_item_id=graph_templates_item.local_graph_template_item_id
					AND graph_template_input_defs.graph_template_input_id=" . $item["id"] . "
					AND graph_templates_item.local_graph_id=$local_graph_id
					LIMIT 0,1");
			}else{
				$current_def_value = db_fetch_row("SELECT
					graph_templates_item." . $item["column_name"] . ",
					graph_templates_item.id
					FROM (graph_templates_item,graph_template_input_defs)
					WHERE graph_template_input_defs.graph_template_item_id=graph_templates_item.id
					AND graph_template_input_defs.graph_template_input_id=" . $item["id"] . "
					AND graph_templates_item.graph_template_id=" . $graph_template_id . "
					LIMIT 0,1");
			}

			/* find our field name */
			$form_field_name = str_replace("|field|", $item["column_name"], $field_name_format);
			$form_field_name = str_replace("|id|", $item["id"], $form_field_name);

			$form_array += array($form_field_name => $struct_graph_item{$item["column_name"]});

			/* change drop_sql's to callbacks */
			if ($form_array[$form_field_name]["method"] == "drop_sql") {
				$form_array[$form_field_name]["method"]     = "drop_sqlcb";
				$form_array[$form_field_name]["text_value"] = $current_def_value[$item["column_name"]];
				$form_array[$form_field_name]["sql_id"]     = "data_template_rrd.id";
			}

			/* modifications to the default form array */
			$form_array[$form_field_name]["friendly_name"] = $item["name"];
			$form_array[$form_field_name]["value"] = $current_def_value{$item["column_name"]};

			/* if we are drawing the graph input list in the pre-graph stage we should omit the data
			source fields because they are basically meaningless at this point */
			if ((empty($local_graph_id)) && ($item["column_name"] == "task_item_id")) {
				unset($form_array[$form_field_name]);
			}else{
				if (($draw_any_items == false) && ($header_title != "")) {
					$header_items = array($header_title, "");
					print "<tr><td>";
					html_header($header_items, 1, true, 'template_graph_item');
				}

				$draw_any_items = true;
			}
		}
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	if (sizeof($input_item_list > 0)) {
		draw_edit_form(
			array(
				"config" => $form_config_array,
				"fields" => $form_array
				)
			);
	}

	if ($draw_any_items) print "</table></td></tr>";		/* end of html_header */

	return (isset($form_array) ? sizeof($form_array) : 0);
}

/** draw_nontemplated_fields_data_source - draws a form that consists of all non-templated data source fields
     associated with a particular data template
   @param int $data_template_id 		- the id of the data template to base the form after
   @param int $local_data_id 			- specify the id of the associated data source if it exists
   @param array $values_array 			- any values that should be included by default on the form
   @param string $field_name_format 	- all fields on the form will be named using the following format, the following
     									  variables can be used:
       									  |field| - the current field name
   @param string $header_title 			- the title to use on the header for this form
   @param bool $alternate_colors 		- whether to alternate colors for each row on the form or not
   @param bool $include_hidden_fields 	- should elements that are not to be displayed be represented as hidden
     									  html input elements or omitted altogether?
   @param int $snmp_query_graph_id 		- if this data template is part of a data query, specify the graph id here. this
     									  will be used to determine if a given field is using suggested values */
function draw_nontemplated_fields_data_source($data_template_id, $local_data_id, &$values_array, $field_name_format = "|field|", $header_title = "", $alternate_colors = true, $include_hidden_fields = true, $snmp_query_graph_id = 0) {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	$form_array = array();
	$draw_any_items = false;

	/* fetch information about the data template */
	$data_template = db_fetch_row("select * from data_template_data where data_template_id=$data_template_id and local_data_id=0");

	$struct_data_source = data_source_form_list();
	reset($struct_data_source);
	while (list($field_name, $field_array) = each($struct_data_source)) {
		/* find our field name */
		$form_field_name = str_replace("|field|", $field_name, $field_name_format);

		$form_array += array($form_field_name => $struct_data_source[$field_name]);

		/* modifications to the default form array */
		$form_array[$form_field_name]["value"] = (isset($values_array[$field_name]) ? $values_array[$field_name] : "");
		$form_array[$form_field_name]["form_id"] = (isset($values_array["id"]) ? $values_array["id"] : "0");
		unset($form_array[$form_field_name]["default"]);

		$current_flag = (isset($field_array["flags"]) ? $field_array["flags"] : "");
		$current_template_flag = (isset($data_template{"t_" . $field_name}) ? $data_template{"t_" . $field_name} : CHECKED);

		if (($current_template_flag != CHECKED) || ($current_flag == "ALWAYSTEMPLATE")) {
			if ($include_hidden_fields == true) {
				$form_array[$form_field_name]["method"] = "hidden";
			}else{
				unset($form_array[$form_field_name]);
			}
		}elseif ((!empty($snmp_query_graph_id)) && (sizeof(db_fetch_assoc("select id from snmp_query_graph_rrd_sv where snmp_query_graph_id=$snmp_query_graph_id and data_template_id=$data_template_id and field_name='$field_name'")) > 0)) {
			if ($include_hidden_fields == true) {
				$form_array[$form_field_name]["method"] = "hidden";
			}else{
				unset($form_array[$form_field_name]);
			}
		}elseif ((empty($local_data_id)) && ($field_name == "data_source_path")) {
			if ($include_hidden_fields == true) {
				$form_array[$form_field_name]["method"] = "hidden";
			}else{
				unset($form_array[$form_field_name]);
			}
		}else{
			if (($draw_any_items == false) && ($header_title != "")) {
				$header_items = array($header_title, "");
				print "<tr><td>";
				html_header($header_items, 1, true, 'template_data_source');
			}

			$draw_any_items = true;
		}
	}

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	draw_edit_form(
		array(
			"config" => $form_config_array,
			"fields" => $form_array
			)
		);

	if ($draw_any_items) print "</table></td></tr>";		/* end of html_header */

	return (isset($form_array) ? sizeof($form_array) : 0);
}

/** draw_nontemplated_fields_data_source_item - draws a form that consists of all non-templated data source
     item fields associated with a particular data template
   @param int $data_template_id 			- the id of the data template to base the form after
   @param array $values_array 				- any values that should be included by default on the form
   @param string $field_name_format 		- all fields on the form will be named using the following format, the following
     										  variables can be used:
    									      |field| - the current field name
       										  |id| - the id of the current data source item
   @param string $header_title 				- the title to use on the header for this form
   @param bool $draw_title_for_each_item  	- should a separate header be drawn for each data source item, or
     										  should all data source items be drawn under one header?
   @param bool $alternate_colors 			- whether to alternate colors for each row on the form or not
   @param bool $include_hidden_fields 		- should elements that are not to be displayed be represented as hidden
     										  html input elements or omitted altogether?
   @param int $snmp_query_graph_id 			- if this graph template is part of a data query, specify the graph id here. this
     										  will be used to determine if a given field is using suggested values */
function draw_nontemplated_fields_data_source_item($data_template_id, &$values_array, $field_name_format = "|field_id|", $header_title = "", $draw_title_for_each_item = true, $alternate_colors = true, $include_hidden_fields = true, $snmp_query_graph_id = 0) {
	global $colors;
	require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");

	$draw_any_items = false;
	$num_fields_drawn = 0;

	/* setup form options */
	if ($alternate_colors == true) {
		$form_config_array = array("no_form_tag" => true);
	}else{
		$form_config_array = array("no_form_tag" => true, "force_row_color" => $colors["form_alternate1"]);
	}

	if (sizeof($values_array) > 0) {
		$struct_data_source_item = data_source_item_form_list();
	foreach ($values_array as $rrd) {
		reset($struct_data_source_item);
		$form_array = array();

		/* if the user specifies a title, we only want to draw that. if not, we should create our
		own title for each data source item */
		if ($draw_title_for_each_item == true) {
			$draw_any_items = false;
		}

		if (empty($rrd["local_data_id"])) { /* this is a template */
			$data_template_rrd = $rrd;
		}else{ /* this is not a template */
			$data_template_rrd = db_fetch_row("select * from data_template_rrd where id=" . $rrd["local_data_template_rrd_id"]);
		}

		while (list($field_name, $field_array) = each($struct_data_source_item)) {
			/* find our field name */
			$form_field_name = str_replace("|field|", $field_name, $field_name_format);
			$form_field_name = str_replace("|id|", $rrd["id"], $form_field_name);

			$form_array += array($form_field_name => $struct_data_source_item[$field_name]);

			/* modifications to the default form array */
			$form_array[$form_field_name]["value"] = (isset($rrd[$field_name]) ? $rrd[$field_name] : "");
			$form_array[$form_field_name]["form_id"] = (isset($rrd["id"]) ? $rrd["id"] : "0");
			unset($form_array[$form_field_name]["default"]);

			/* append the data source item name so the user will recognize it */
			if ($draw_title_for_each_item == false) {
				$form_array[$form_field_name]["friendly_name"] .= " [" . $rrd["data_source_name"] . "]";
			}

			if ($data_template_rrd{"t_" . $field_name} != CHECKED) {
				if ($include_hidden_fields == true) {
					$form_array[$form_field_name]["method"] = "hidden";
				}else{
					unset($form_array[$form_field_name]);
				}
			}elseif ((!empty($snmp_query_graph_id)) && (sizeof(db_fetch_assoc("select id from snmp_query_graph_rrd_sv where snmp_query_graph_id=$snmp_query_graph_id and data_template_id=$data_template_id and field_name='$field_name'")) > 0)) {
				if ($include_hidden_fields == true) {
					$form_array[$form_field_name]["method"] = "hidden";
				}else{
					unset($form_array[$form_field_name]);
				}
			}else{
				if (($draw_any_items == false) && ($draw_title_for_each_item == false) && ($header_title != "")) {
					$header_items = array($header_title, "");
					print "<tr><td>";
					html_header($header_items, 1, true, 'template_data_source_item_' . $form_field_name);
				}elseif (($draw_any_items == false) && ($draw_title_for_each_item == true) && ($header_title != "")) {
					$header_items = array($header_title ." [" . $rrd["data_source_name"] . "]", "");
					print "<tr><td>";
					html_header($header_items, 1, true, 'template_data_source_item_' . $form_field_name);
				}

				$draw_any_items = true;

				/* if the "Output field" appears here among the non-templated fields, the
				   valid choices for the drop-down box must be fetched from the associated
				   data input method */
				if ($field_name == "data_input_field_id") {
					$data_input_id = db_fetch_cell("select data_input_id from data_template_data where data_template_id=".$rrd["data_template_id"]." and local_data_id=0");
					$form_array[$form_field_name]["sql"] = "select id,CONCAT(data_name,' - ',name) as name from data_input_fields where data_input_id=".$data_input_id." and input_output='out' and update_rra='on' order by data_name,name";
				}
			}
		}

		draw_edit_form(
			array(
				"config" => $form_config_array,
				"fields" => $form_array
				)
			);

		$num_fields_drawn += sizeof($form_array);

		if (sizeof($form_array)) print "</table></td></tr>";		/* end of html_header */
	}
	}

	if ($draw_any_items) print "</table></td></tr>";		/* end of html_header */

	return $num_fields_drawn;
}

/** draw_nontemplated_fields_custom_data - draws a form that consists of all non-templated custom data fields
     associated with a particular data template
   @param int $data_template_id	 			- the id of the data template to base the form after
   @param string $field_name_format 		- all fields on the form will be named using the following format, the following
     										  variables can be used:
      										  |id| - the id of the current field
   @param string $header_title 				- the title to use on the header for this form
   @param bool $draw_title_for_each_item 	- should a separate header be drawn for each data source item, or
     										  should all data source items be drawn under one header?
   @param bool $alternate_colors 			- whether to alternate colors for each row on the form or not
   @param bool $include_hidden_fields 		- should elements that are not to be displayed be represented as hidden
     										  html input elements or omitted altogether?
   @param int $snmp_query_id 				- if this graph template is part of a data query, specify the data query id here. this
     										  will be used to determine if a given field is associated with a suggested value */
function draw_nontemplated_fields_custom_data($data_template_data_id, $field_name_format = "|field|", $header_title = "", $alternate_colors = true, $include_hidden_fields = true, $snmp_query_id = 0) {
	global $colors;

	$data = db_fetch_row("select id,data_input_id,data_template_id,name,local_data_id from data_template_data where id=$data_template_data_id");
	$device_id = db_fetch_cell("select device.id from (data_local,device) where data_local.device_id=device.id and data_local.id=" . $data["local_data_id"]);
	$template_data = db_fetch_row("select id,data_input_id from data_template_data where data_template_id=" . $data["data_template_id"] . " and local_data_id=0");

	$draw_any_items = false;

	/* get each INPUT field for this data input source */
	$fields = db_fetch_assoc("select * from data_input_fields where data_input_id=" . $data["data_input_id"] . " and input_output='in' order by sequence");

	/* loop through each field found */
	$i = 0;
	if (sizeof($fields) > 0) {
	foreach ($fields as $field) {
		$data_input_data = db_fetch_row("select * from data_input_data where data_template_data_id=" . $data["id"] . " and data_input_field_id=" . $field["id"]);

		if (sizeof($data_input_data) > 0) {
			$old_value = $data_input_data["value"];
		}else{
			$old_value = "";
		}

		/* if data template then get t_value from template, else always allow user input */
		if (empty($data["data_template_id"])) {
			$can_template = CHECKED;
		}else{
			$can_template = db_fetch_cell("select t_value from data_input_data where data_template_data_id=" . $template_data["id"] . " and data_input_field_id=" . $field["id"]);
		}

		/* find our field name */
		$form_field_name = str_replace("|id|", $field["id"], $field_name_format);

		if ((!empty($device_id)) && (preg_match('/^' . VALID_HOST_FIELDS . '$/i', $field["type_code"])) && (empty($can_template))) { /* no device fields */
			if ($include_hidden_fields == true) {
				form_hidden_box($form_field_name, $old_value, "");
			}
		}elseif ((!empty($snmp_query_id)) && (preg_match('/^(index_type|index_value|output_type)$/', $field["type_code"]))) { /* no data query fields */
			if ($include_hidden_fields == true) {
				form_hidden_box($form_field_name, $old_value, "");
			}
		}elseif (empty($can_template)) { /* no templated fields */
			if ($include_hidden_fields == true) {
				form_hidden_box($form_field_name, $old_value, "");
			}
		}else{
			if (($draw_any_items == false) && ($header_title != "")) {
				$header_items = array($header_title, "");
				print "<tr><td>";
				html_header($header_items, 1, true, 'template_custom_data');
			}

			if ($alternate_colors == true) {
				form_alternate_row_color();
			}else{
				print "<tr bgcolor='#" . $colors["form_alternate1"] . "'>\n";
			}

			print "<td width='50%'><strong>" . $field["name"] . "</strong></td>\n";
			print "<td>";

			draw_custom_data_row($form_field_name, $field["id"], $data["id"], $old_value);

			print "</td>";
			print "</tr>\n";

			$draw_any_items = true;
			$i++;
		}
	}
	}

	if ($draw_any_items) print "</table></td></tr>";		/* end of html_header */

	return $i;
}

/** draw_custom_data_row - draws a single row representing 'custom data' for a single data input field.
     this function is where additional logic can be applied to control how a certain field of custom
     data is represented on the HTML form
   @param string $field_name 			- the name of this form element
   @param int $data_input_field_id 		- the id of the data input field that this row represents
   @param int $data_template_data_id 	- the id of the data source data element that this data input field belongs to
   @param string $current_value 		- the current value of this field */
function draw_custom_data_row($field_name, $data_input_field_id, $data_template_data_id, $current_value) {
	$field = db_fetch_row("select data_name,type_code from data_input_fields where id=$data_input_field_id");

	if (($field["type_code"] == "index_type") && (db_fetch_cell("select local_data_id from data_template_data where id=$data_template_data_id") > 0)) {
		$index_type = db_fetch_assoc("select
			device_snmp_cache.field_name
			from (data_template_data,data_local,device_snmp_cache)
			where data_template_data.local_data_id=data_local.id
			and data_local.snmp_query_id=device_snmp_cache.snmp_query_id
			and data_template_data.id=$data_template_data_id
			group by device_snmp_cache.field_name");

		if (sizeof($index_type) == 0) {
			print "<em>" . __("Data query data sources must be created through %sNew Graphs%s.", "<a href='graphs_new.php'>", "</a>") . "</em>\n";
		}else{
			form_dropdown($field_name, $index_type, "field_name", "field_name", $current_value, "", "", "");
		}
	}elseif (($field["type_code"] == "output_type") && (db_fetch_cell("select local_data_id from data_template_data where id=$data_template_data_id") > 0)) {
		$output_type = db_fetch_assoc("select
			snmp_query_graph.id,
			snmp_query_graph.name
			from (data_template_data,data_local,snmp_query_graph)
			where data_template_data.local_data_id=data_local.id
			and data_local.snmp_query_id=snmp_query_graph.snmp_query_id
			and data_template_data.id=$data_template_data_id
			group by snmp_query_graph.id");

		if (sizeof($output_type) == 0) {
			print "<em>" . __("Data query data sources must be created through %sNew Graphs%s.", "<a href='graphs_new.php'>", "<a href='graphs_new.php'>") . "</em>\n";
		}else{
			form_dropdown($field_name, $output_type, "name", "id", $current_value, "", "", "");
		}
	}else{
		form_text_box($field_name, $current_value, "", "");
	}
}
