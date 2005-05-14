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

/* set_graph_template - changes the graph template for a particular graph to
     $graph_template_id
   @arg $graph_id - the id of the graph to change the graph template for, '0'
     means that the graph does not exist in which case a new graph will be
     created from the graph template
   @arg $graph_template_id - id the of the graph template to change to. specify '0' for no
     graph template
   @arg $host_id - the id of the device attached to this graph
   @arg $form_graph_fields - graph field data from the active form which will be used
     override any any graph fields in the database.
   @arg $form_graph_item_fields - graph item field data from the active form which will
     be used override any any graph item fields in the database.
   @arg $form_data_input_fields - can be optionally be used to specify data query field
     information to be used to resolve data |query_ variables before data sources for this
     graph exist.
   @returns - the id of the graph created from this template */
function set_graph_template($graph_id, $graph_template_id, $host_id, &$form_graph_fields, &$form_graph_item_input_fields, $form_data_input_fields = "") {
	global $struct_graph, $struct_graph_item;
	include_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
	include_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	if (empty($graph_template_id)) {
		if (!empty($graph_id)) {
			db_execute("update graph set graph_template_id = 0 where id = $graph_id");
		}

		return;
	}

	/* copy down old graph_id for reference later on */
	$_graph_id = $graph_id;

	/* get data about the graph */
	$graph = array_merge((empty($graph_id) ? array() : db_fetch_row("select * from graph where id = $graph_id")), $form_graph_fields);

	/* get information about the graph template */
	$graph_template = db_fetch_row("select * from graph_template where id = $graph_template_id");

	/* evaluate suggested values for this graph */
	if ( ((is_array($form_data_input_fields)) && (isset($form_data_input_fields["data_query_id"])) && (isset($form_data_input_fields["data_query_index"])) && ((empty($graph_template["t_title"])) || (empty($graph_id)))) ) {
		$graph_template["title"] = evaluate_data_query_suggested_values($host_id, $form_data_input_fields["data_query_id"], $form_data_input_fields["data_query_index"], "graph_template_suggested_value", "graph_template_id = $graph_template_id and field_name = 'title'", 0);
	}else if ((empty($graph_template["t_title"]) || (empty($graph_id)))) {
		/* evaluation of host/other variables might go here */
		$graph_template["title"] = db_fetch_cell("select value from graph_template_suggested_value where graph_template_id = $graph_template_id and field_name = 'title' order by sequence limit 1");
	}

	$graph_id = api_graph_save(
		$graph_id,
		$host_id,
		$graph_template_id,
		(((empty($graph_template["t_image_format"])) || (!isset($graph["image_format"]))) ? $graph_template["image_format"] : $graph["image_format"]),
		((((empty($graph_id)) || (!empty($graph_template["t_title"]))) && (isset($graph["title"]))) ? $graph["title"] : $graph_template["title"]),
		//(((empty($graph_template["t_title"])) || (!isset($graph["title"]))) ? $graph_template["title"] : $graph["title"]),
		(((empty($graph_template["t_height"])) || (!isset($graph["height"]))) ? $graph_template["height"] : $graph["height"]),
		(((empty($graph_template["t_width"])) || (!isset($graph["width"]))) ? $graph_template["width"] : $graph["width"]),
		(((empty($graph_template["t_x_grid"])) || (!isset($graph["x_grid"]))) ? $graph_template["x_grid"] : $graph["x_grid"]),
		(((empty($graph_template["t_y_grid"])) || (!isset($graph["y_grid"]))) ? $graph_template["y_grid"] : $graph["y_grid"]),
		(((empty($graph_template["t_y_grid_alt"])) || (!isset($graph["y_grid_alt"]))) ? $graph_template["y_grid_alt"] : $graph["y_grid_alt"]),
		(((empty($graph_template["t_no_minor"])) || (!isset($graph["no_minor"]))) ? $graph_template["no_minor"] : $graph["no_minor"]),
		(((empty($graph_template["t_upper_limit"])) || (!isset($graph["upper_limit"]))) ? $graph_template["upper_limit"] : $graph["upper_limit"]),
		(((empty($graph_template["t_lower_limit"])) || (!isset($graph["lower_limit"]))) ? $graph_template["lower_limit"] : $graph["lower_limit"]),
		(((empty($graph_template["t_vertical_label"])) || (!isset($graph["vertical_label"]))) ? $graph_template["vertical_label"] : $graph["vertical_label"]),
		(((empty($graph_template["t_auto_scale"])) || (!isset($graph["auto_scale"]))) ? $graph_template["auto_scale"] : $graph["auto_scale"]),
		(((empty($graph_template["t_auto_scale_opts"])) || (!isset($graph["auto_scale_opts"]))) ? $graph_template["auto_scale_opts"] : $graph["auto_scale_opts"]),
		(((empty($graph_template["t_auto_scale_log"])) || (!isset($graph["auto_scale_log"]))) ? $graph_template["auto_scale_log"] : $graph["auto_scale_log"]),
		(((empty($graph_template["t_auto_scale_rigid"])) || (!isset($graph["auto_scale_rigid"]))) ? $graph_template["auto_scale_rigid"] : $graph["auto_scale_rigid"]),
		(((empty($graph_template["t_auto_padding"])) || (!isset($graph["auto_padding"]))) ? $graph_template["auto_padding"] : $graph["auto_padding"]),
		(((empty($graph_template["t_base_value"])) || (!isset($graph["base_value"]))) ? $graph_template["base_value"] : $graph["base_value"]),
		(((empty($graph_template["t_export"])) || (!isset($graph["export"]))) ? $graph_template["export"] : $graph["export"]),
		(((empty($graph_template["t_unit_value"])) || (!isset($graph["unit_value"]))) ? $graph_template["unit_value"] : $graph["unit_value"]),
		(((empty($graph_template["t_unit_length"])) || (!isset($graph["unit_length"]))) ? $graph_template["unit_length"] : $graph["unit_length"]),
		(((empty($graph_template["t_unit_exponent_value"])) || (!isset($graph["unit_exponent_value"]))) ? $graph_template["unit_exponent_value"] : $graph["unit_exponent_value"]),
		(((empty($graph_template["t_force_rules_legend"])) || (!isset($graph["unit_exponent_value"]))) ? $graph_template["unit_exponent_value"] : $graph["force_rules_legend"]));

	if ($graph_id) {
		/* get a complete list of graph template item inputs */
		$graph_template_item_inputs = db_fetch_assoc("select
			graph_template_item_input.field_name,
			graph_template_item_input_item.graph_template_item_id
			from graph_template_item_input,graph_template_item_input_item
			where graph_template_item_input.id=graph_template_item_input_item.graph_template_item_input_id
			and graph_template_item_input.graph_template_id = $graph_template_id");

		/* the $graph_template_item_input_items array is built to determine whether a given graph item field is to
		 * be templated or not */
		if (sizeof($graph_template_item_inputs) > 0) {
			foreach ($graph_template_item_inputs as $item) {
				$graph_template_item_input_items{$item["field_name"]}{$item["graph_template_item_id"]} = true;
			}
		}else{
			$graph_template_item_input_items = array();
		}

		/* get information about the graph template items */
		$graph_template_items = array_rekey(db_fetch_assoc("select * from graph_template_item where graph_template_id = $graph_template_id order by sequence"), "", array("id", "data_template_item_id", "color", "graph_item_type", "cdef", "consolidation_function", "gprint_format", "legend_format", "legend_value", "hard_return"));

		/* create an array of mappings between $graph_template_item_id and its numeric index equivelant */
		$graph_template_item_id_to_index = array();

		for ($i=0; $i<sizeof($graph_template_items); $i++) {
			$graph_template_item_id_to_index{$graph_template_items[$i]["id"]} = $i;
		}

		/* traverse the $form_graph_item_input_fields array and load each input value into the $form_graph_item_fields array */
		$form_graph_item_fields = array();

		reset($form_graph_item_input_fields);
		while (list($field_name, $inputs) = each($form_graph_item_input_fields)) {
			while (list($input_id, $input_value) = each($inputs)) {
				$input_items = db_fetch_assoc("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = $input_id");

				if (sizeof($input_items) > 0) {
					foreach ($input_items as $item) {
						$form_graph_item_fields{$graph_template_item_id_to_index{$item["graph_template_item_id"]}}[$field_name] = $input_value;
					}
				}
			}
		}

		$graph_items = array_merge_recursive_replace((empty($graph_id) ? array() : array_rekey(db_fetch_assoc("select * from graph_item where graph_id = $graph_id order by sequence"), "", array("id", "data_source_item_id", "color", "graph_item_type", "cdef", "consolidation_function", "gprint_format", "legend_format", "legend_value", "hard_return"))), $form_graph_item_fields);

		/* if there are more graph items then there are items in the template, delete the difference */
		if (sizeof($graph_items) > sizeof($graph_template_items)) {
			for ($i=sizeof($template_items_list)-1; ($i < count($graph_items)); $i++) {
				api_graph_item_remove($graph_items[$i]["id"]);
			}
		}

		for ($i=0; $i<sizeof($graph_template_items); $i++) {
			api_graph_item_save(
				(isset($graph_items[$i]["id"]) ? $graph_items[$i]["id"] : "0"),
				$graph_id,
				0,
				((isset($graph_template_item_input_items["color"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["color"] : $graph_template_items[$i]["color"]),
				((isset($graph_template_item_input_items["graph_item_type"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["graph_item_type"] : $graph_template_items[$i]["graph_item_type"]),
				((isset($graph_template_item_input_items["cdef"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["cdef"] : $graph_template_items[$i]["cdef"]),
				((isset($graph_template_item_input_items["consolidation_function"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["consolidation_function"] : $graph_template_items[$i]["consolidation_function"]),
				((isset($graph_template_item_input_items["gprint_format"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["gprint_format"] : $graph_template_items[$i]["gprint_format"]),
				((isset($graph_template_item_input_items["legend_format"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["legend_format"] : $graph_template_items[$i]["legend_format"]),
				((isset($graph_template_item_input_items["legend_value"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["legend_value"] : $graph_template_items[$i]["legend_value"]),
				((isset($graph_template_item_input_items["hard_return"]{$graph_template_items[$i]["id"]}) && (isset($graph_items[$i]))) ? $graph_items[$i]["hard_return"] : $graph_template_items[$i]["hard_return"]));
		}
	}

	return $graph_id;
}

function generate_complete_graph($graph_template_id, $host_id, &$form_graph_fields, &$form_graph_item_input_fields, &$form_data_source_fields, &$form_data_source_item_fields, &$form_data_input_fields) {
	global $cnn_id;

	include_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
	include_once(CACTI_BASE_PATH . "/lib/data_source/data_source_template.php");

	if (empty($graph_template_id)) {
		return;
	}

	$data_templates = db_fetch_assoc("select
		data_template.id,
		data_template.data_input_type
		from graph_template_item,data_template_item,data_template
		where graph_template_item.data_template_item_id=data_template_item.id
		and data_template_item.data_template_id=data_template.id
		and graph_template_item.graph_template_id = $graph_template_id
		group by data_template.id");

	/* decide which data sources we will need to create */
	$create_data_templates_list = array();

	if (sizeof($data_templates) > 0) {
		foreach ($data_templates as $item) {
			if (($item["data_input_type"] == DATA_INPUT_TYPE_DATA_QUERY) && (isset($form_data_input_fields["data_query_id"]))) {
				if (db_fetch_cell("select value from data_template_field where data_template_id = " . $item["id"] . " and name = 'data_query_id'") == $form_data_input_fields["data_query_id"]) {
					$create_data_templates_list[] = $item["id"];
				}
			}else{
				$create_data_templates_list[] = $item["id"];
			}
		}
	}

	/* no data templates have been marked for creation */
	if (sizeof($create_data_templates_list) == 0) {
		return;
	}

	/* initialize for set_graph_template() down below */
	$dt_form_data_input_fields = array();

	for ($i=0; $i<sizeof($create_data_templates_list); $i++) {
		$data_template_id = $create_data_templates_list[$i];

		/* php won't let you pass array() by reference without assigning it to a variable first */
		$dt_form_data_source_fields = (isset($form_data_source_fields[$data_template_id]) ? $form_data_source_fields[$data_template_id] : array());
		$dt_form_data_source_item_fields = (isset($form_data_source_item_fields[$data_template_id]) ? $form_data_source_item_fields[$data_template_id] : array());
		$dt_form_data_input_fields = (isset($form_data_input_fields[$data_template_id]) ? $form_data_input_fields[$data_template_id] : array());

		/* create the actual data source from the chosen data template */
		$dti_to_dsi{$create_data_templates_list[$i]} = set_data_template(0, $create_data_templates_list[$i], $host_id, $dt_form_data_source_fields, $dt_form_data_source_item_fields, $dt_form_data_input_fields);
	}

	/* fetch a list graph template items and their associated data template items */
	$data_template_items = db_fetch_assoc("select
		data_template_item.data_source_name,
		data_template_item.data_template_id,
		graph_template_item.sequence
		from graph_template_item,data_template_item
		where graph_template_item.data_template_item_id=data_template_item.id
		and graph_template_item.graph_template_id = $graph_template_id");

	/* create the actual graph from the chosen graph template */
	$graph_id = set_graph_template(0, $graph_template_id, $host_id, $form_graph_fields, $form_graph_item_input_fields, $dt_form_data_input_fields);

	if (sizeof($data_template_items) > 0) {
		foreach ($data_template_items as $item) {
			/* write out the graph->data source item mapping here. note, that we are only able to use 'sequence'
			 * as a primary key here because the graph was just created above and therefore must be accurate */
			db_execute("update graph_item set data_source_item_id = " . db_fetch_cell("select id from data_source_item where data_source_name = '" . $item["data_source_name"] . "' and data_source_id = " . $dti_to_dsi{$item["data_template_id"]}) . " where id = " . db_fetch_cell("select id from graph_item where graph_id = $graph_id and sequence = " . $item["sequence"]));
		}
	}

	/* make sure the graph title is up to date */
	update_graph_title_cache($graph_id);

	return $graph_id;
}

/* api_graph_template_propagate - pushes out templated graph template fields to all matching graphs
   @arg $graph_template_id - the id of the graph template to push out values for */
function api_graph_template_propagate($graph_template_id) {
	global $struct_graph, $cnn_id;

	/* get information about this graph template */
	$graph_template = db_fetch_row("select * from graph_template where id=$graph_template_id");

	/* must be a valid data template */
	if (sizeof($graph_template) == 0) { return 0; }

	/* get data sources list for ADODB */
	$graphs = $cnn_id->Execute("select * from graph where graph_template_id = $graph_template_id");

	/* loop through each graph column name (from the above array) */
	reset($struct_graph);
	while (list($field_name, $field_array) = each($struct_graph)) {
		/* are we allowed to push out the column? */
		if ((isset($graph_template["t_$field_name"])) && (isset($graph_template[$field_name])) && ($graph_template["t_$field_name"] == "0")) {
			$g_fields[$field_name] = $graph_template[$field_name];
		}
	}

	if (isset($g_fields["name"])) {
		//update_data_source_title_cache_from_template($data_template_data["data_template_id"]);
	}

	db_execute($cnn_id->GetUpdateSQL($graphs, $g_fields));
}

?>
