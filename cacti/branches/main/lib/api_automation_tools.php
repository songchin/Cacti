
<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

function api_tree_item_save($id, $tree_id, $type, $parent_tree_item_id, 
	$title, $local_graph_id, $rra_id, $host_id, $host_grouping_type, 
	$sort_children_type, $propagate_changes) {

	global $config;

	include_once(CACTI_BASE_PATH . "/lib/tree.php");

	$parent_order_key = db_fetch_cell("select order_key from graph_tree_items where id=$parent_tree_item_id");

	/* fetch some cache variables */
	if (empty($id)) {
		/* new/save - generate new order key */
		$order_key = get_next_tree_id($parent_order_key, "graph_tree_items", "order_key", "graph_tree_id=$tree_id");
	}else{
		/* edit/save - use old order_key */
		$order_key = db_fetch_cell("select order_key from graph_tree_items where id=$id");
	}

	/* duplicate graph check */
	$search_key = substr($parent_order_key, 0, (tree_tier($parent_order_key) * CHARS_PER_TIER));
	if (($type == TREE_ITEM_TYPE_GRAPH) && (sizeof(db_fetch_assoc("select id from graph_tree_items where local_graph_id='$local_graph_id' and graph_tree_id='$tree_id' and order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'")) > 0)) {
		return db_fetch_cell("select id from graph_tree_items where local_graph_id='$local_graph_id' and graph_tree_id='$tree_id' and order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'");
	}

	/* Duplicate header check */
	if (($type == TREE_ITEM_TYPE_HEADER)) {
		if ((sizeof(db_fetch_assoc("select id from graph_tree_items where title='$title' and graph_tree_id='$tree_id' and order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'")) > 0)) {
			return db_fetch_cell("select id from graph_tree_items where title='$title' and graph_tree_id='$tree_id' and order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'");
		}
	}

	$save["id"]                 = $id;
	$save["graph_tree_id"]      = $tree_id;
	$save["title"]              = form_input_validate($title, "title", "", ($type == TREE_ITEM_TYPE_HEADER ? false : true), 3);
	$save["order_key"]          = $order_key;
	$save["local_graph_id"]     = form_input_validate($local_graph_id, "local_graph_id", "", true, 3);
	$save["rra_id"]	            = form_input_validate($rra_id, "rra_id", "", true, 3);
	$save["host_id"]            = form_input_validate($host_id, "host_id", "", true, 3);
	$save["host_grouping_type"] = form_input_validate($host_grouping_type, "host_grouping_type", "", true, 3);
	$save["sort_children_type"] = form_input_validate($sort_children_type, "sort_children_type", "", true, 3);

	$tree_item_id = 0;

	if (!is_error_message()) {
		$tree_item_id = sql_save($save, "graph_tree_items");

		if ($tree_item_id) {
			raise_message(1);

			/* re-parent the branch if the parent item has changed */
			if ($parent_tree_item_id != $tree_item_id) {
				reparent_branch($parent_tree_item_id, $tree_item_id);
			}

			$tree_sort_type = db_fetch_cell("select sort_type from graph_tree where id='$tree_id'");

			/* tree item ordering */
			if ($tree_sort_type == TREE_ORDERING_NONE) {
				/* resort our parent */
				$parent_sorting_type = db_fetch_cell("select sort_children_type from graph_tree_items where id=$parent_tree_item_id");

				if ((!empty($parent_tree_item_id)) && ($parent_sorting_type != TREE_ORDERING_NONE)) {
					sort_tree(SORT_TYPE_TREE_ITEM, $parent_tree_item_id, $parent_sorting_type);
				}

				/* if this is a header, sort direct children */
				if (($type == TREE_ITEM_TYPE_HEADER) && ($sort_children_type != TREE_ORDERING_NONE)) {
					sort_tree(SORT_TYPE_TREE_ITEM, $tree_item_id, $sort_children_type);
				}
				/* tree ordering */
			}else{
				/* potential speed savings for large trees */
				if (tree_tier($save["order_key"]) == 1) {
					sort_tree(SORT_TYPE_TREE, $tree_id, $tree_sort_type);
				}else{
					sort_tree(SORT_TYPE_TREE_ITEM, $parent_tree_item_id, $tree_sort_type);
				}
			}

			/* if the user checked the 'Propagate Changes' box */
			if (($type == TREE_ITEM_TYPE_HEADER) && ($propagate_changes == true)) {
				$search_key = preg_replace("/0+$/", "", $order_key);

				$tree_items = db_fetch_assoc("select
					graph_tree_items.id
					from graph_tree_items
					where graph_tree_items.host_id = 0
					and graph_tree_items.local_graph_id = 0
					and graph_tree_items.title != ''
					and graph_tree_items.order_key like '$search_key%%'
					and graph_tree_items.graph_tree_id='$tree_id'");

				if (sizeof($tree_items) > 0) {
					foreach ($tree_items as $item) {
						db_execute("update graph_tree_items set sort_children_type = '$sort_children_type' where id = '" . $item["id"] . "'");

						if ($sort_children_type != TREE_ORDERING_NONE) {
							sort_tree(SORT_TYPE_TREE_ITEM, $item["id"], $sort_children_type);
						}
					}
				}
			}
		}else{
			raise_message(2);
		}
	}

	return $tree_item_id;
}

function getHostTemplates() {
	$tmpArray = db_fetch_assoc("select id, name from host_template order by id");

	$host_templates[0] = "None";

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $template) {
			$host_templates[$template["id"]] = $template["name"];
		}
	}

	return $host_templates;
}

/* getHosts				get all matching hosts for given selection criteria
 * @arg $input_parms	array of selection criteria
 * returns				array of hosts, indexed by host_id
 */
function getHosts($input_parms) {
	$hosts    = array();
	
	$sql_where = "";
	
	if (isset($input_parms["description"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'description like "%%' . $input_parms["description"] . '%%" '; 
	}
	
	if (isset($input_parms["ip"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'hostname like "%%' . $input_parms["ip"] . '%%" '; 
	}
	
	if (isset($input_parms["host_template_id"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'host_template_id = ' . $input_parms["host_template_id"] . ' '; 
	}
	
	if (isset($input_parms["notes"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'notes like "%%' . $input_parms["notes"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_community"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_community like "%%' . $input_parms["snmp_community"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_version"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_version = ' . $input_parms["snmp_version"] . ' '; 
	}
	
	if (isset($input_parms["snmp_username"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_username like "%%' . $input_parms["snmp_username"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_password"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_password like "%%' . $input_parms["snmp_password"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_auth_protocol"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_auth_protocol = ' . $input_parms["snmp_auth_protocol"] . ' '; 
	}
	
	if (isset($input_parms["snmp_priv_passphrase"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_priv_passphrase like "%%' . $input_parms["snmp_priv_passphrase"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_priv_protocol"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_priv_protocol = ' . $input_parms["snmp_priv_protocol"] . ' '; 
	}
	
	if (isset($input_parms["snmp_context"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_context like "%%' . $input_parms["snmp_context"] . '%%" '; 
	}
	
	if (isset($input_parms["snmp_port"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_port = ' . $input_parms["snmp_port"] . ' '; 
	}
	
	if (isset($input_parms["snmp_timeout"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_timeout = ' . $input_parms["snmp_timeout"] . ' '; 
	}
	
	if (isset($input_parms["availability_method"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'availability_method = ' . $input_parms["availability_method"] . ' '; 
	}
	
	if (isset($input_parms["ping_method"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'ping_method = ' . $input_parms["ping_method"] . ' '; 
	}
	
	if (isset($input_parms["ping_port"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'ping_port = ' . $input_parms["ping_port"] . ' '; 
	}
	
	if (isset($input_parms["ping_timeout"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'ping_timeout = ' . $input_parms["ping_timeout"] . ' '; 
	}
	
	if (isset($input_parms["ping_retries"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'ping_retries = ' . $input_parms["ping_retries"] . ' '; 
	}
	
	if (isset($input_parms["max_oids"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'max_oids = ' . $input_parms["max_oids"] . ' '; 
	}
	
	if (isset($input_parms["disabled"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'disabled = ' . $input_parms["disabled"] . ' '; 
	}

	$sql_stmt = ("SELECT " .
					"* " .
				"FROM " .
					"host " .
				$sql_where .
				"ORDER BY id");
	//print $sql_stmt ."\n";	
	$tmpArray = db_fetch_assoc($sql_stmt);

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $host) {
			$hosts[$host["id"]] = $host;
		}
	}

	return $hosts;
}

function getInputFields($templateId) {
	$fields    = array();

	$tmpArray = db_fetch_assoc("SELECT DISTINCT
		data_input_fields.data_name AS `name`,
		data_input_fields.name AS `description`,
		data_input_data.value AS `default`,
		data_template_data.data_template_id,
		data_input_fields.id AS `data_input_field_id`
		FROM data_input_data
		INNER JOIN (((data_template_rrd
		INNER JOIN (graph_templates
		INNER JOIN graph_templates_item
		ON graph_templates.id=graph_templates_item.graph_template_id)
		ON data_template_rrd.id=graph_templates_item.task_item_id)
		INNER JOIN data_template_data
		ON data_template_rrd.data_template_id=data_template_data.data_template_id)
		INNER JOIN data_input_fields
		ON data_template_data.data_input_id=data_input_fields.data_input_id)
		ON (data_input_data.data_template_data_id=data_template_data.id)
		AND (data_input_data.data_input_field_id=data_input_fields.id)
		WHERE (((graph_templates.id)=$templateId)
		AND (data_template_rrd.local_data_id=0)
		AND (data_template_data.local_data_id=0)
		AND ((data_input_data.t_value)='on')
		AND ((data_input_fields.input_output)='in'))");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $row) {
			$fields[$row["data_template_id"] . ":" . $row["name"]] = $row;
		}
	}

	return $fields;
}

function getAddresses() {
	$addresses = array();
	$tmpArray  = db_fetch_assoc("SELECT id, hostname FROM host ORDER BY hostname");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $tmp) {
			$addresses[$tmp["hostname"]] = $tmp["id"];
		}
	}

	return $addresses;
}

function getSNMPFields($hostId, $snmp_query_id = "") {
	$fieldNames = array();

	if ($snmp_query_id != "") {
		$sql_where = " AND snmp_query_id=$snmp_query_id";
	}else{
		$sql_where = "";
	}

	$tmpArray   = db_fetch_assoc("SELECT DISTINCT field_name
		FROM host_snmp_cache
		WHERE host_id = " . $hostId . "
		$sql_where
		ORDER BY field_name");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $f) {
			$fieldNames[$f["field_name"]] = 1;
		}
	}

	return $fieldNames;
}

function getSNMPValues($hostId, $field, $snmp_query_id = "") {
	$values   = array();

	if ($snmp_query_id != "") {
		$sql_where = " AND snmp_query_id=$snmp_query_id";
	}else{
		$sql_where = "";
	}

	$tmpArray = db_fetch_assoc("SELECT field_value
		FROM host_snmp_cache
		WHERE host_id=" . $hostId . "
		AND field_name='" . $field . "'
		$sql_where
		ORDER BY field_value");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $v) {
			$values[$v["field_value"]] = 1;
		}
	}

	return $values;
}

function getSNMPQueries() {
	$queries  = array();
	$tmpArray = db_fetch_assoc("SELECT id, name
		FROM snmp_query
		ORDER by id");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $q) {
			$queries[$q["id"]] = $q["name"];
		}
	}

	return $queries;
}

function getSNMPQueryTypes($snmpQueryId) {
	$types    = array();
	$tmpArray = db_fetch_assoc("SELECT id, name
		FROM snmp_query_graph
		WHERE snmp_query_id = " . $snmpQueryId . "
		ORDER BY id");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $type) {
			$types[$type["id"]] = $type["name"];
		}
	}

	return $types;
}

function getGraphTemplates() {
	$graph_templates = array();
	$tmpArray        = db_fetch_assoc("SELECT id, name
		FROM graph_templates
		ORDER BY id");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $t) {
			$graph_templates[$t["id"]] = $t["name"];
		}
	}

	return $graph_templates;
}

function getGraphTemplatesByHostTemplate($host_template_id) {
	$graph_templates = array();
	$tmpArray 		 = db_fetch_assoc("SELECT " .
										"host_template_graph.graph_template_id AS id, " .
										"graph_templates.name AS name " .
									"FROM host_template_graph " .
									"LEFT JOIN graph_templates " .
										"ON (host_template_graph.graph_template_id = graph_templates.id) " .
									"WHERE host_template_id = $host_template_id");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $t) {
			$graph_templates[$t["id"]] = $t["name"];
		}
	}

	return $graph_templates;
}

function displayQueryTypes($types, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known SNMP Query Types: (id, name)\n";
	}

	while (list($id, $name) = each ($types)) {
		echo $id . "\t" . $name . "\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayHostTemplates($host_templates, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Valid Host Templates: (id, name)\n";
	}

	if (sizeof($host_templates)) {
		foreach ($host_templates as $id => $name) {
			echo "$id\t$name\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayCommunities($quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known communities are: (community)\n";
	}

	$communities = db_fetch_assoc("SELECT DISTINCT
		snmp_community
		FROM host
		ORDER BY snmp_community");

	if (sizeof($communities)) {
		foreach ($communities as $community) {
			echo $community["snmp_community"]."\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displaySNMPFields($fields, $hostId, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known SNMP Fields for host-id $hostId: (name)\n";
	}

	while (list($field, $values) = each ($fields)) {
		echo $field . "\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displaySNMPValues($values, $hostId, $field, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known values for $field for host $hostId: (name)\n";
	}

	while (list($value, $foo) = each($values)) {
		echo "$value\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displaySNMPValuesExtended($hostId, $fields, $snmpQueryId, $quietMode = FALSE) {
	$exit_code = 1; # assume an error until we've printed sth

	$req_fields = array();
	if (strlen($fields) > 0) {
		# remove unwanted blanks
		$query_FieldSpec = str_replace(" ", "", $fields);
		# add tics for SQL query
		$query_FieldSpec = str_replace(",", "','", $query_FieldSpec);
		$query_FieldSpec = " AND field_name in ('" . $query_FieldSpec . "')";
	} else {
		$query_FieldSpec = "";
	}

	$xml_array = get_data_query_array($snmpQueryId);

	if ($xml_array != false) {
		/* loop through once so we can find out how many input fields there are */
		reset($xml_array["fields"]);
		while (list ($field_name, $field_array) = each($xml_array["fields"])) {
			if ($field_array["direction"] == "input") {
				# if spec was given ...
				if (strlen($fields) > 0) {
					#  ... but current field doesn't match (make it case-insensitive, beware of the users!)
					if (strpos(strtolower($fields), strtolower($field_name)) === false) {
						# skip it
						continue;
					}
				}
				$req_fields[$field_name] = $xml_array["fields"][$field_name];
				/* initialize column lengths */
				$req_fields[$field_name]["length"] = $quietMode ? 0 : max(strlen($field_array["name"]), strlen($field_name));

				if (!isset ($total_rows)) {
					$total_rows = db_fetch_cell("SELECT COUNT(*) FROM host_snmp_cache WHERE host_id=" . $hostId . " AND snmp_query_id=" . $snmpQueryId . " AND field_name='$field_name'");
				}
			}
		}

		if (!isset ($total_rows)) {
			if (!sizeof($req_fields)) {
				echo "ERROR: Invalid --snmp-field-spec (found: " . $fields . ") given\n";
				echo "Try --list-snmp-fields\n";
				return (1);
			} else {
				echo "ERROR: No cached SNMP values found for this SNMP Query\n";
				return (1);
			}
		}

		$snmp_query_graphs = db_fetch_assoc("SELECT snmp_query_graph.id,snmp_query_graph.name FROM snmp_query_graph WHERE snmp_query_graph.snmp_query_id=" . $snmpQueryId . " ORDER BY snmp_query_graph.name");

		reset($req_fields);
		$snmp_query_indexes = array ();
		$sql_order = "";

		/* get the unique field values from the database */
		$field_names = db_fetch_assoc("SELECT DISTINCT " .
											"field_name " .
										"FROM " .
											"host_snmp_cache " .
										"WHERE " .
											"host_id=" . $hostId . 
										" AND " .
											"snmp_query_id=" . $snmpQueryId .
											$query_FieldSpec);

		/* build magic query */
		$sql_query = "SELECT host_id, snmp_query_id, snmp_index";
		if (sizeof($field_names) > 0) {
			foreach ($field_names as $column) {
				$field_name = $column["field_name"];
				$sql_query .= ", MAX(CASE WHEN field_name='$field_name' THEN field_value ELSE NULL END) AS '$field_name'";
			}
		} else {
			echo "ERROR: No SNMP field names found for this SNMP Query\n";
			return (1);
		}

		$sql_query .= 	" FROM host_snmp_cache " .
						"WHERE host_id=" . $hostId .
						" AND snmp_query_id=" . $snmpQueryId .
						$query_FieldSpec .
						" GROUP BY host_id, snmp_query_id, snmp_index " .
						$sql_order;

		$snmp_query_indexes = db_fetch_assoc($sql_query);

		if (!sizeof($snmp_query_indexes)) {
			print "This data query returned 0 rows, perhaps there was a problem executing this data query.\n";
			return (1);
		}

		if (sizeof($snmp_query_indexes) > 0) {
			foreach ($snmp_query_indexes as $row) {
				# determine length of each data field
				reset($req_fields);
				while (list ($field_name, $field_array) = each($req_fields)) {
					/* verify, that the requested field is known in snmp_cache */
					if (isset($row[$field_name])) {
						$req_fields[$field_name]["length"] = max($req_fields[$field_name]["length"],strlen($row[$field_name]));
					}
				}
			}
		}

		if (!$quietMode) {
			if ($fields === "") {
				echo "Known values for host-id $hostId:\n";
			} else {
				echo "Known values for host-id $hostId: ($fields)\n";
			}
			# now print headers: field identifier and field names
			reset($req_fields);
			while (list ($field_name, $field_array) = each($req_fields)) {
				foreach ($field_names as $row) {
					if ($row["field_name"] == $field_name) {
						print(str_pad($field_name, $req_fields[$field_name]["length"]+1));
						break;
					}
				}
			}
			print "\n";
			reset($req_fields);
			while (list ($field_name, $field_array) = each($req_fields)) {
				foreach ($field_names as $row) {
					if ($row["field_name"] == $field_name) {
						print(str_pad($field_array["name"], $req_fields[$field_name]["length"]+1));
						break;
					}
				}
			}
			print "\n";
		}

		# and data, finally
		if (sizeof($snmp_query_indexes) > 0) {
			foreach ($snmp_query_indexes as $row) {
				reset($req_fields);
				while (list ($field_name, $field_array) = each($req_fields)) {
					if (isset($row[$field_name])) {
						print(str_pad($row[$field_name], $req_fields[$field_name]["length"]+1));
						$exit_code = 0;
					}
				}
				print "\n";
			}
		}
	}

	if (!$quietMode) {
		echo "\n";
	}

	return($exit_code);
}

function displaySNMPQueries($queries, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known SNMP Queries:(id, name)\n";
	}

	while (list($id, $name) = each ($queries)) {
		echo $id . "\t" . $name . "\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayInputFields($input_fields, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known Input Fields:(name, default, description)\n";
	}

	if (sizeof($input_fields)) {
	foreach ($input_fields as $row) {
		echo $row["data_template_id"] . ":" . $row["name"] . "\t" . $row["default"] . "\t" . $row["description"] . "\n";
	}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayGraphTemplates($templates, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known Graph Templates:(id, name)\n";
	}

	while (list($id, $name) = each ($templates)) {
		echo $id . "\t" . $name . "\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayHosts($hosts, $quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known Hosts: (id, hostname, template, description)\n";
	}

	if (sizeof($hosts)) {
		foreach($hosts as $host) {
			echo $host["id"] . "\t" . $host["hostname"] . "\t" . $host["host_template_id"] . "\t" . $host["description"] . "\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayTrees($quietMode = FALSE) {
	global $tree_sort_types;

	if (!$quietMode) {
		echo "Known Trees:\nid\tsort method\t\t\tname\n";
	}

	$trees = db_fetch_assoc("SELECT
		id,
		sort_type,
		name
		FROM graph_tree
		ORDER BY id");

	if (sizeof($trees)) {
		foreach ($trees as $tree) {
			echo $tree["id"]."\t";
			echo $tree_sort_types[$tree["sort_type"]]."\t";
			echo $tree["name"]."\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayTreeNodes($tree_id, $quietMode = FALSE) {
	global $tree_sort_types, $tree_item_types, $host_group_types;

	if (!$quietMode) {
		echo "Known Tree Nodes:\n";
		echo "type\tid\ttext\n";
	}

	$nodes = db_fetch_assoc("SELECT
		id,
		local_graph_id,
		rra_id,
		title,
		host_id,
		host_grouping_type,
		sort_children_type
		FROM graph_tree_items
		WHERE graph_tree_id=$tree_id
		ORDER BY id");

	if (sizeof($nodes)) {
		foreach ($nodes as $node) {
			/* taken from tree.php, funtion item_edit() */
			$current_type = TREE_ITEM_TYPE_HEADER;
			if ($node["local_graph_id"] > 0) { $current_type = TREE_ITEM_TYPE_GRAPH; }
			if ($node["title"] != "") { $current_type = TREE_ITEM_TYPE_HEADER; }
			if ($node["host_id"] > 0) { $current_type = TREE_ITEM_TYPE_HOST; }
			echo $tree_item_types[$current_type]."\t";
			echo $node["id"]."\t";


			switch ($current_type) {
				case TREE_ITEM_TYPE_HEADER:
					echo $node["title"]."\t";
					echo $tree_sort_types[$node["sort_children_type"]]."\t";
					echo "\n";
					break;

				case TREE_ITEM_TYPE_GRAPH:
					/* fetch the title for that graph */
					$graph_title = db_fetch_cell("SELECT
					graph_templates_graph.title_cache as name
					FROM (
						graph_templates_graph,
						graph_local)
					WHERE
						graph_local.id=graph_templates_graph.local_graph_id and
						local_graph_id = " . $node["local_graph_id"]);

					$rra = db_fetch_cell("SELECT
						name
						FROM rra
						WHERE id =" . $node["rra_id"]);

					echo $graph_title ."\t";
					echo $rra . "\t";
					echo "\n";
					break;

				case TREE_ITEM_TYPE_HOST:
					$name = db_fetch_cell("SELECT
					hostname
					FROM host
					WHERE id = " . $node["host_id"]);
					echo $name . "\t";
					echo $host_group_types[$node["host_grouping_type"]]."\t";
					echo "\n";
				break;
			}
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayRRAs($quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known RRAs:\nid\tsteps\trows\ttimespan\tname\n";
	}

	$rras = db_fetch_assoc("SELECT
		id,
		name,
		steps,
		rows,
		timespan
		FROM rra
		ORDER BY id");

	if (sizeof($rras)) {
		foreach ($rras as $rra) {
			echo $rra["id"]."\t";
			echo $rra["steps"]."\t";
			echo $rra["rows"]."\t";
			echo $rra["timespan"]."\t\t";
			echo $rra["name"]."\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayHostGraphs($host_id, $quietMode = FALSE) {

	if (!$quietMode) {
		echo "Known Host Graphs: (id, name, template)\n";
	}

	$graphs = db_fetch_assoc("SELECT
		graph_templates_graph.local_graph_id as id,
		graph_templates_graph.title_cache as name,
		graph_templates.name as template_name
		FROM (graph_local,graph_templates_graph)
		LEFT JOIN graph_templates ON (graph_local.graph_template_id=graph_templates.id)
		WHERE graph_local.id=graph_templates_graph.local_graph_id
		AND graph_local.host_id=" . $host_id . "
		ORDER BY graph_templates_graph.local_graph_id");

	if (sizeof($graphs)) {
		foreach ($graphs as $graph) {
			echo $graph["id"] . "\t";
			echo $graph["name"] . "\t";
			echo $graph["template_name"] . "\t";
			echo "\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayUsers($quietMode = FALSE) {
	if (!$quietMode) {
		echo "Known Users:\nid\tusername\tfull_name\n";
	}

	$groups = db_fetch_assoc("SELECT
				id,
				username,
				full_name
				FROM user_auth
				ORDER BY id");

	if (sizeof($groups)) {
		foreach ($groups as $group) {
			echo $group["id"]."\t";
			echo $group["username"]."\t";
			echo $group["full_name"]."\n";
		}
	}

	if (!$quietMode) {
		echo "\n";
	}
}

?>