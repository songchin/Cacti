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

function copy_graph_template_to_graph($graph_template_id, $host_id = 0, $data_query_id = 0, $data_query_index = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/variable.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	/* sanity check for $graph_template_id */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		return false;
	}

	/* fetch field lists */
	$fields_graph = get_graph_field_list();
	$fields_graph_item = get_graph_items_field_list();

	$graph_template = get_graph_template($graph_template_id);

	if (sizeof($graph_template) > 0) {
		/* copy down per-graph only fields */
		$_fields = array();
		$_fields["id"] = "0";
		$_fields["graph_template_id"] = $graph_template_id;
		$_fields["host_id"] = $host_id;

		/* evaluate suggested values: data query-based graphs */
		if ((!empty($data_query_id)) && ($data_query_index != "")) {
			$_fields["title"] = evaluate_data_query_suggested_values($host_id, $data_query_id, $data_query_index, "graph_template_suggested_value", "graph_template_id = " . sql_sanitize($graph_template_id) . " and field_name = 'title'", 0);
		/* evaluate suggested values: non-data query-based graphs */
		}else{
			$_fields["title"] = db_fetch_cell("select value from graph_template_suggested_value where graph_template_id = " . sql_sanitize($graph_template_id) . " and field_name = 'title' order by sequence limit 1");
		}

		/* copy down all visible fields */
		foreach (array_keys($fields_graph) as $field_name) {
			if (isset($graph_template[$field_name])) {
				$_fields[$field_name] = $graph_template[$field_name];
			}
		}

		if (api_graph_save(0, $_fields, true)) {
			$graph_id = db_fetch_insert_id();

			api_log_log("Cloning graph [ID#$graph_id] from template [ID#$graph_template_id]", SEV_DEBUG);

			/* move onto the graph items */
			$graph_template_items = get_graph_template_items($graph_template_id);

			if (sizeof($graph_template_items) > 0) {
				foreach ($graph_template_items as $graph_template_item) {
					/* copy down per-graph only fields */
					$_fields = array();
					$_fields["id"]	= "0";
					$_fields["graph_id"] = $graph_id;
					$_fields["graph_template_item_id"] = $graph_template_item["id"]; /* this allows us to connect the dots later */

					foreach (array_keys($fields_graph_item) as $field_name) {
						if (isset($graph_template_item[$field_name])) {
							$_fields[$field_name] = $graph_template_item[$field_name];
						}
					}

					if (!api_graph_item_save(0, $_fields)) {
						api_log_log("Save error in api_graph_item_save()", SEV_ERROR);
					}
				}
			}

			return $graph_id;
		}else{
			api_log_log("Save error in api_graph_save()", SEV_ERROR);

			return false;
		}
	}

	return false;
}

function copy_graph_to_graph_template($graph_id) {

}

function generate_complete_graph($graph_template_id, $host_id = 0, $data_query_id = 0, $data_query_index = "") {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");
	require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");

	/* sanity check for $graph_template_id */
	if ((!is_numeric($graph_template_id)) || (empty($graph_template_id))) {
		return false;
	}

	/* sanity check for $host_id */
	if (!is_numeric($host_id)) {
		return false;
	}

	$data_templates = get_data_templates_from_graph_template($graph_template_id);

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
		return false;
	}

	$dti_to_dsi = array();

	foreach ($create_data_templates_list as $data_template_id) {
		$data_source_id = copy_data_template_to_data_source($data_template_id, $host_id, $data_query_id, $data_query_index);

		if ($data_source_id === false) {
			api_log_log("Error generating data source from data template [ID#$data_template_id]", SEV_ERROR);
		}else{
			$dti_to_dsi[$data_template_id] = $data_source_id;
		}
	}

	/* create the actual graph from the chosen graph template */
	$graph_id = copy_graph_template_to_graph($graph_template_id, $host_id, $data_query_id, $data_query_index);

	if ($graph_id === false) {
		api_log_log("Error generating graph from graph template [ID#$graph_template_id]", SEV_ERROR);
	}else{
		/* fetch a list graph template items and their associated data template items */
		$data_template_items = get_data_template_items_from_graph_template($graph_template_id);

		if (sizeof($data_template_items) > 0) {
			foreach ($data_template_items as $item) {
				/* write out the graph->data source item mapping here. note, that we are only able to use 'sequence'
				 * as a primary key here because the graph was just created above and therefore must be accurate */
				db_update("graph_item",
					array(
						"id" => array("type" => DB_TYPE_NUMBER, "value" => db_fetch_cell("select id from graph_item where graph_id = " . sql_sanitize($graph_id) . " and graph_template_item_id = " . sql_sanitize($item["graph_template_item_id"]))),
						"data_source_item_id" => array("type" => DB_TYPE_NUMBER, "value" => db_fetch_cell("select id from data_source_item where data_source_name = '" . sql_sanitize($item["data_source_name"]) . "' and data_source_id = " . sql_sanitize($dti_to_dsi{$item["data_template_id"]})))
						),
					array("id"));
			}
		}

		/* make sure the graph title is up to date */
		api_graph_title_cache_update($graph_id);

		return array("graph" => array($graph_template_id => $graph_id), "data_source" => $dti_to_dsi);
	}

	return false;
}

/* api_graph_template_propagate - pushes out templated graph template fields to all matching graphs
   @arg $graph_template_id - the id of the graph template to push out values for */
function api_graph_template_propagate($graph_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	if ((empty($graph_template_id)) || (!is_numeric($graph_template_id))) {
		return false;
	}

	/* get information about this graph template */
	$graph_template = get_graph_template($graph_template_id);

	/* must be a valid graph template */
	if ($graph_template === false) {
		return false;
	}

	/* retrieve a list of graph fields */
	$graph_fields = get_graph_field_list();

	$g_fields = array();
	/* loop through each graph column name (from the above array) */
	foreach ($graph_fields as $field_name => $field_array) {
		/* are we allowed to push out the column? */
		if ((isset($graph_template["t_$field_name"])) && (isset($graph_template[$field_name])) && ($graph_template["t_$field_name"] == "0")) {
			$g_fields[$field_name] = array("type" => $field_array["data_type"], "value" => $graph_template[$field_name]);
		}
	}

	if (sizeof($g_fields) > 0) {
		$g_fields["graph_template_id"] = array("type" => DB_TYPE_NUMBER, "value" => $graph_template_id);

		return db_update("graph", $g_fields, array("graph_template_id"));
	}

	return true;
}

function api_graph_template_item_input_propagate($graph_template_item_input_id, $value) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	if ((empty($graph_template_item_input_id)) || (!is_numeric($graph_template_item_input_id))) {
		return false;
	}

	/* retrieve a list of graph item fields */
	$graph_item_fields = get_graph_items_field_list();

	/* get the db field name for this graph item input */
	$input_field_name = db_fetch_cell("select field_name from graph_template_item_input where id = " . sql_sanitize($graph_template_item_input_id));

	if ($input_field_name != "") {
		$graph_template_items = db_fetch_assoc("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = " . sql_sanitize($graph_template_item_input_id));

		if (sizeof($graph_template_items) > 0) {
			foreach ($graph_template_items as $graph_template_item) {
				db_update("graph_item",
					array(
						"graph_template_item_id" => array("type" => DB_TYPE_NUMBER, "value" => $graph_template_item["graph_template_item_id"]),
						$input_field_name => array("type" => $graph_item_fields[$input_field_name]["data_type"], "value" => $value)
						),
					array("graph_template_item_id"));
			}
		}

		return true;
	}else{
		return false;
	}
}

?>
