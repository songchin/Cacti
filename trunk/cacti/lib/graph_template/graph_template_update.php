<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
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

function api_graph_template_save($graph_template_id, $_fields_graph) {
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_push.php");

	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_graph) == 0) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id);

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_graph, api_graph_template_form_list());
	$_fields += sql_get_database_field_array($_fields_graph, api_graph_form_list());

	if (db_replace("graph_template", $_fields, array("id"))) {
		if (empty($graph_template_id)) {
			$graph_template_id = db_fetch_insert_id();
		}else{
			/* push out graph template fields */
			api_graph_template_propagate($graph_template_id);
		}

		return $graph_template_id;
	}else{
		return false;
	}
}

function api_graph_template_suggested_values_save($graph_template_id, $_fields_suggested_values) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	/* insert the new custom field values */
	if (is_array($_fields_suggested_values) > 0) {
		foreach ($_fields_suggested_values as $field_name => $field_array) {
			foreach ($field_array as $field_item) {
				if (empty($field_item["id"])) {
					db_insert("graph_template_suggested_value",
						array(
							"id" => array("type" => DB_TYPE_INTEGER, "value" => "0"),
							"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id),
							"field_name" => array("type" => DB_TYPE_STRING, "value" => $field_name),
							"value" => array("type" => DB_TYPE_STRING, "value" => $field_item["value"]),
							"sequence" => array("type" => DB_TYPE_INTEGER, "value" => seq_get_current(0, "sequence", "graph_template_suggested_value", "graph_template_id = " . sql_sanitize($graph_template_id) . " and field_name = '" . sql_sanitize($field_name) . "'"))
							),
						array("id"));
				}else{
					db_update("graph_template_suggested_value",
						array(
							"id" => array("type" => DB_TYPE_INTEGER, "value" => $field_item["id"]),
							"value" => array("type" => DB_TYPE_STRING, "value" => $field_item["value"])
							),
						array("id"));
				}
			}
		}
	}
}

function api_graph_template_remove($graph_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	/* sanity checks */
	validate_id_die($graph_template_id, "graph_template_id");

	/* delete all graph template items */
	$graph_template_items = api_graph_template_item_list($graph_template_id);

	if (is_array($graph_template_items) > 0) {
		foreach ($graph_template_items as $item) {
			api_graph_template_item_remove($item["id"], false);
		}
	}

	/* base tables */
	db_delete("graph_template_suggested_value",
		array(
			"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id)
			));
	db_delete("graph_template_item_input",
		array(
			"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id)
			));
	db_delete("graph_template_item",
		array(
			"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id)
			));
	db_delete("graph_template",
		array(
			"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id)
			));

	/* host templates */
	db_delete("host_template_graph",
		array(
			"graph_template_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_id)
			));

	/* attached graphs */
	db_execute("UPDATE graph SET graph_template_id = 0 WHERE graph_template_id = " . sql_sanitize($graph_template_id));
}

function api_graph_template_item_save($graph_template_item_id, $_fields_graph_item) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");

	/* sanity checks */
	validate_id_die($graph_template_item_id, "graph_template_item_id", true);

	/* make sure that there is at least one field to save */
	if (sizeof($_fields_graph_item) == 0) {
		return false;
	}

	/* sanity check for $graph_template_id */
	if ((empty($graph_template_item_id)) && (empty($_fields_graph_item["graph_template_id"]))) {
		api_log_log("Required graph_template_id when graph_template_item_id = 0", SEV_ERROR);
		return false;
	} else if ((isset($_fields_graph_item["graph_template_id"])) && (!db_integer_validate($_fields_graph_item["graph_template_id"]))) {
		return false;
	}

	/* field: id */
	$_fields["id"] = array("type" => DB_TYPE_INTEGER, "value" => $graph_template_item_id);

	/* field: graph_template_id */
	if (!empty($_fields_graph_item["graph_template_id"])) {
		$_fields["graph_template_id"] = array("type" => DB_TYPE_INTEGER, "value" => $_fields_graph_item["graph_template_id"]);
	}

	/* field: sequence */
	if (empty($graph_template_item_id)) {
		$_fields["sequence"] = array("type" => DB_TYPE_INTEGER, "value" => seq_get_current($graph_template_item_id, "sequence", "graph_template_item", "graph_template_id = " . sql_sanitize($_fields_graph_item["graph_template_id"])));
	}

	/* convert the input array into something that is compatible with db_replace() */
	$_fields += sql_get_database_field_array($_fields_graph_item, api_graph_template_item_form_list());

	if (db_replace("graph_template_item", $_fields, array("id"))) {
		if (empty($graph_template_item_id)) {
			$graph_template_item_id = db_fetch_insert_id();
		}

		return $graph_template_item_id;
	}else{
		return false;
	}
}

function api_graph_template_item_remove($graph_template_item_id, $delete_attached = true) {
	/* sanity checks */
	validate_id_die($graph_template_item_id, "graph_template_item_id");

	/* base tables */
	db_delete("graph_template_item",
		array(
			"id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_item_id)
			));
	db_delete("graph_template_item_input_item",
		array(
			"graph_template_item_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_item_id)
			));

	/* attached graph items */
	if ($delete_attached === true) {
		db_delete("graph_item",
			array(
				"graph_template_item_id" => array("type" => DB_TYPE_INTEGER, "value" => $graph_template_item_id)
				));
	}else{
		db_execute("UPDATE graph_item SET graph_template_item_id = 0 WHERE graph_template_item_id = " . sql_sanitize($graph_template_item_id));
	}
}

function api_graph_template_item_movedown($graph_template_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_template_id = db_fetch_cell("select graph_template_id from graph_template_item where id = $graph_template_item_id");

	$next_item = seq_get_item("graph_template_item", "sequence", $graph_template_item_id, "graph_template_id = $graph_template_id", "next");

	seq_move_item("graph_template_item", $graph_template_item_id, "graph_template_id = $graph_template_id", "down");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $graph_template_item_id") . " where graph_template_item_id = $graph_template_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $next_item") . " where graph_template_item_id = $next_item");
}

function api_graph_template_item_moveup($graph_template_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$graph_template_id = db_fetch_cell("select graph_template_id from graph_template_item where id = $graph_template_item_id");

	$last_item = seq_get_item("graph_template_item", "sequence", $graph_template_item_id, "graph_template_id = $graph_template_id", "previous");

	seq_move_item("graph_template_item", $graph_template_item_id, "graph_template_id = $graph_template_id", "up");

	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $graph_template_item_id") . " where graph_template_item_id = $graph_template_item_id");
	db_execute("update graph_item set sequence = " . db_fetch_cell("select sequence from graph_template_item where id = $last_item") . " where graph_template_item_id = $last_item");
}

function api_graph_template_item_row_movedown($row_num, $graph_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_template_item", "graph_template_id = $graph_template_id", true, "down");
}

function api_graph_template_item_row_moveup($row_num, $graph_template_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	seq_move_graph_item_row($row_num, "graph_template_item", "graph_template_id = $graph_template_id", true, "up");
}

function api_graph_template_item_duplicate($graph_template_item_id, $new_data_template_item_id) {
	require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");

	$item = db_fetch_row("select * from graph_template_item where id = $graph_template_item_id");

	if (sizeof($item) > 0) {
		api_graph_template_item_save(0, $item["graph_template_id"], $new_data_template_item_id, $item["color"], $item["graph_item_type"], $item["cdef"],
			$item["consolidation_function"], $item["gprint_format"], $item["legend_format"], $item["legend_value"], $item["hard_return"]);
	}
}

function api_graph_template_item_input_save($id, $items_array, $graph_template_id, $field_name, $name) {
	$save["id"] = $id;
	$save["graph_template_id"] = $graph_template_id;
	$save["name"] = form_input_validate($name, "name", "", false, 3);
	$save["field_name"] = form_input_validate($field_name, "field_name", "", true, 3);

	$graph_template_item_input_id = 0;

	if (!is_error_message()) {
		$graph_template_item_input_id = sql_save($save, "graph_template_item_input");

		if ($graph_template_item_input_id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	if ((!is_error_message()) && (!empty($graph_template_item_input_id))) {
		/* list all graph items from the db so we can compare them with the current form */
		$selected_graph_items = db_fetch_assoc("select graph_template_item_id from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");

		$db_selected_graph_item = array();

		if (sizeof($selected_graph_items) > 0) {
			foreach ($selected_graph_items as $item) {
				$db_selected_graph_item[] = $item["graph_template_item_id"];
			}
		}

		db_execute("delete from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");

		$old_members = array();
		$new_members = array();

		/* list all graph items that have been selected on the form */
		for ($i=0; $i<sizeof($items_array); $i++) {
			db_execute("insert into graph_template_item_input_item (graph_template_item_input_id,graph_template_item_id) values ($graph_template_item_input_id," . $items_array[$i] . ")");

			if (in_array($items_array[$i], $db_selected_graph_item)) {
				/* is selected and exists in the db; old item */
				$old_members[] = $items_array[$i];
			}else{
				/* is selected and does not exist the db; new item */
				$new_members[] = $items_array[$i];
			}
		}

		for ($i=0; $i<sizeof($old_members); $i++) {
			//push_out_graph_input($graph_template_item_input_id, $old_members[$i], $new_members);
		}
	}

	return $graph_template_item_input_id;
}

function api_graph_template_item_input_remove($graph_template_item_input_id) {
	if ((empty($graph_template_item_input_id)) || (!is_numeric($graph_template_item_input_id))) {
		return false;
	}

	db_execute("delete from graph_template_item_input where id = $graph_template_item_input_id");
	db_execute("delete from graph_template_item_input_item where graph_template_item_input_id = $graph_template_item_input_id");
}

?>
