
<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

function getHostTemplates() {
	$tmpArray = db_fetch_assoc("select id, name from host_template order by id");

	$host_templates[0] = __("None");

	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $template) {
			$host_templates[$template["id"]] = $template["name"];
		}
	}

	return $host_templates;
}

/* getDevices				get all matching hosts for given selection criteria
 * @arg $input_parms	array of selection criteria
 * returns				array of hosts, indexed by host_id
 */
function getDevices($input_parms) {
	$hosts    = array();

	$sql_where = "";

	if (isset($input_parms["id"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'id = ' . $input_parms["id"] . ' ';
	}

	if (isset($input_parms["site_id"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'site_id = ' . $input_parms["site_id"] . ' ';
	}

	if (isset($input_parms["poller_id"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'poller_id = ' . $input_parms["poller_id"] . ' ';
	}

	if (isset($input_parms["description"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'description like "%%' . $input_parms["description"] . '%%" ';
	}

	if (isset($input_parms["hostname"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'hostname like "%%' . $input_parms["hostname"] . '%%" ';
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
		$sql_where .= 'snmp_auth_protocol = "' . $input_parms["snmp_auth_protocol"] . '" ';
	}

	if (isset($input_parms["snmp_priv_passphrase"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_priv_passphrase like "%%' . $input_parms["snmp_priv_passphrase"] . '%%" ';
	}

	if (isset($input_parms["snmp_priv_protocol"])) {
		strlen($sql_where) ? ($sql_where .= ' AND ') : ($sql_where .= ' WHERE ');
		$sql_where .= 'snmp_priv_protocol = "' . $input_parms["snmp_priv_protocol"] . '" ';
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
	#print $sql_stmt ."\n";

	return db_fetch_assoc($sql_stmt);
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

/* displayGenericArray	- column-save printout of arrays
 * @arg $data			- the array to be printed; fields of each array item must relate to $req_fields
 * @arg $req_fields		- an array of fields to be printed;
 * 							index = field name;
 * 							"desc" = human readable description
 * @arg $title			- optional title of printout; skipped in quietMode
 * @arg $quietMode		- optionally suppress title printout
 * returns				  true, if anything has been printed
 */
function displayGenericArray($data, $req_fields=array(), $title="", $quietMode=FALSE) {
	$exit_code = false; # assume an error until we've printed sth
	$pad = 2;		# default padding size

	if (sizeof($data) && sizeof($req_fields)) {
		# determine length of each data field
		reset($req_fields);
		foreach($req_fields as $key => $value) {
			if(!isset($req_fields{$key}["desc"])) {	# if no explicit field description given, use field name as description
				$req_fields{$key}["desc"] = $key;
			}
			# default field length equals length of header description
			$req_fields{$key}["length"] = strlen($req_fields{$key}["desc"]);

			foreach ($data as $row) {				# see, whether any data field is longer than the corresponding header
				if (isset($row{$key}))
					$req_fields{$key}["length"] = max($req_fields{$key}["length"],strlen($row{$key}));
			}
		}

		if (!$quietMode) {
			if ($title === "") {
				#
			} else {
				echo $title . "\n";
			}
			# now print headers: field identifier and field names
			reset($req_fields);
			foreach($req_fields as $item) {
				print(str_pad($item["desc"], $item["length"]+$pad));
			}
			print "\n";
		}

		# and data, finally
		if (sizeof($data) > 0) {
			foreach ($data as $row) {
				reset($req_fields);
				while (list ($field_name, $field_array) = each($req_fields)) {
					if (isset($row[$field_name])) {
						print(str_pad($row[$field_name], $req_fields[$field_name]["length"]+$pad));
						$exit_code = true;
					}
				}
				print "\n";
			}
		}
	}
	return $exit_code;
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

function getSNMPQueriesByDevices($devices, $snmp_query_id='', &$header) {
	global $reindex_types;

	$header = array();	# provides header info for printout
	$sql_where = "";

	if (sizeof($devices)) {
		$sql_where .= ((strlen($sql_where) === 0) ? "WHERE " : "AND ") . str_replace("id", "host_id", array_to_sql_or($devices, "id")) . " ";
	}
	if ($snmp_query_id != '') {
		$sql_where .= ((strlen($sql_where) === 0) ? "WHERE " : "AND ") . " snmp_query.id =" . $snmp_query_id . " ";
	}
	$sql = "SELECT " .
				"host.id as host_id, " .
				"host.hostname as hostname, " .
				"snmp_query.id as snmp_query_id, " .
				"snmp_query.name as snmp_query_name, " .
				"host_snmp_query.sort_field, " .
				"host_snmp_query.title_format, " .
				"host_snmp_query.reindex_method " .
				"FROM host " .
				"LEFT JOIN host_snmp_query ON (host.id = host_snmp_query.host_id) " .
				"LEFT JOIN snmp_query ON (host_snmp_query.snmp_query_id = snmp_query.id) " .
				$sql_where .
				"ORDER by host.id, snmp_query.id";
	#print $sql . "\n";

	$tmpArray = db_fetch_assoc($sql);
	if (sizeof($tmpArray)) {
		foreach ($tmpArray as $key => $value) {	# recode reindex type in a human readable fashion
			$tmpArray{$key}["human_reindex_method"] = $reindex_types[$tmpArray{$key}["reindex_method"]];
		}
		# provide human readable column headers
		$header["host_id"]["desc"] 				= __("Host Id");
		$header["hostname"]["desc"] 			= __("Hostname");
		$header["snmp_query_id"]["desc"] 		= __("Query Id");
		$header["snmp_query_name"]["desc"] 		= __("Query Name");
		$header["sort_field"]["desc"] 			= __("Sort Field");
		$header["title_format"]["desc"] 		= __("Title Format");
		$header["reindex_method"]["desc"] 		= __("#");
		$header["human_reindex_method"]["desc"] = __("Reindex Method");
	}

	return $tmpArray;
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
		echo __("Known SNMP Query Types: (id, name)") . "\n";
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
		echo __("Valid Host Templates: (id, name)") . "\n";
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
		echo __("Known communities are: (community)") . "\n";
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
		echo __("Known SNMP Fields for host-id $hostId: (name)") . "\n";
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
		echo __("Known values for $field for host $hostId: (name)") . "\n";
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
				echo __("ERROR: Invalid --snmp-field-spec (found: %s) given", $fields) . "\n";
				echo __("Try --list-snmp-fields") . "\n";
				return (1);
			} else {
				echo __("ERROR: No cached SNMP values found for this SNMP Query") . "\n";
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
			echo __("ERROR: No SNMP field names found for this SNMP Query") . "\n";
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
			print __("This data query returned 0 rows, perhaps there was a problem executing this data query.") . "\n";
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
				echo __("Known values for host-id") . " " . $hostId . ":\n";
			} else {
				echo __("Known values for host-id") . " " . $hostId . ": (" . $fields . ")\n";
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
		echo __("Known SNMP Queries:(id, name)") . "\n";
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
		echo __("Known Input Fields: (name, default, description)") . "\n";
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
		echo __("Known Graph Templates: (id, name)") . "\n";
	}

	while (list($id, $name) = each ($templates)) {
		echo $id . "\t" . $name . "\n";
	}

	if (!$quietMode) {
		echo "\n";
	}
}

function displayDevices($hosts, $quietMode = FALSE) {
	if (!$quietMode) {
		echo __("Known Hosts: (id, hostname, template, description)") . "\n";
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
		echo __("Known Trees: (id, sort method, name") . "\n";
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

function displayTreeNodes($tree_id, $nodeType = "", $parentNode = "", $quietMode = FALSE) {
	global $tree_sort_types, $tree_item_types, $host_group_types;

	if (!$quietMode) {
		echo __("Known Tree Nodes: (type, id, parentid, text)") . "\n";
	}

	$parentID = 0;

	$nodes = db_fetch_assoc("SELECT
		id,
		local_graph_id,
		rra_id,
		title,
		host_id,
		host_grouping_type,
		order_key,
		sort_children_type
		FROM graph_tree_items
		WHERE graph_tree_id=$tree_id
		ORDER BY order_key");

	if (sizeof($nodes)) {
		foreach ($nodes as $node) {
			/* taken from tree.php, funtion item_edit() */
			$current_type = TREE_ITEM_TYPE_HEADER;
			if ($node["local_graph_id"] > 0) { $current_type = TREE_ITEM_TYPE_GRAPH; }
			if ($node["title"] != "") { $current_type = TREE_ITEM_TYPE_HEADER; }
			if ($node["host_id"] > 0) { $current_type = TREE_ITEM_TYPE_HOST; }

			switch ($current_type) {
				case TREE_ITEM_TYPE_HEADER:
					$starting_tier = tree_tier($node["order_key"]);
					if ($starting_tier == 1) {
						$parentID = 0;
					}else{
						$parent_tier = substr($node["order_key"], 0, (($starting_tier - 1) * CHARS_PER_TIER));
						$parentID = db_fetch_cell("SELECT id FROM graph_tree_items WHERE order_key LIKE '$parent_tier%%' AND graph_tree_id=$tree_id ORDER BY order_key LIMIT 1");
					}

					if ($nodeType == '' || $nodeType == 'header') {
						if ($parentNode == '' || $parentNode == $parentID) {
							echo $tree_item_types[$current_type]."\t";
							echo $node["id"]."\t";
							if ($parentID == 0) {
								echo "N/A\t";
							}else{
								echo $parentID."\t";
							}

							echo $node["title"]."\t";
							echo $tree_sort_types[$node["sort_children_type"]]."\t";
							echo "\n";
						}
					}
					$parentID = $node["id"];

					break;

				case TREE_ITEM_TYPE_GRAPH:
					if ($nodeType == '' || $nodeType == 'graph') {
						if ($parentNode == '' || $parentNode == $parentID) {
							echo $tree_item_types[$current_type]."\t";
							echo $node["id"]."\t";
							if ($parentID == 0) {
								echo "N/A\t";
							}else{
								echo $parentID."\t";
							}

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
						}
					}
					break;

				case TREE_ITEM_TYPE_HOST:
					if ($nodeType == '' || $nodeType == 'host') {
						if ($parentNode == '' || $parentNode == $parentID) {
							echo $tree_item_types[$current_type]."\t";
							echo $node["id"]."\t";
							if ($parentID == 0) {
								echo "N/A\t";
							}else{
								echo $parentID."\t";
							}

							$name = db_fetch_cell("SELECT
							hostname
							FROM host
							WHERE id = " . $node["host_id"]);
							echo $name . "\t";
							echo $host_group_types[$node["host_grouping_type"]]."\t";
							echo "\n";
						}
					}
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
		echo __("Known RRAs: (id, steps, rows, timespan, name)") . "\n";
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
		echo __("Known Host Graphs: (id, name, template)") . "\n";
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
		echo __("Known Users: (id, username, full_name)") . "\n";
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

/*
 * verifyDevice		- verifies all array items for a host array
 * 					  recodes the host array, if necessary
 * @arg $host		- host array (part of host table)
 * @arg $ri_check	- request a referential integrity test
 * returns			- if ok, returns true with array recoded; otherwise array containg error message
 */
function verifyDevice(&$host, $ri_check=false) {

	foreach($host as $key => $value) {

		switch ($key) {
			case "id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: Id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM host WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This host id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "site_id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: Site Id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM sites WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This site id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "poller_id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: Poller Id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM poller WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This poller id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "host_template_id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: Host Template Id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM host_template WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This host template id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "description":
				break;
			case "hostname":
				break;
			case "notes":
				break;
			case "snmp_community":
				break;
			case "snmp_version":
				if (($value == 1 || $value == 2 || $value == 3)) {
					#
				}else{
					$check["err_msg"] = __("ERROR: Invalid SNMP Version: (%s)", $value);
					return $check;
				}
				break;
			case "snmp_username":
				break;
			case "snmp_password":
				break;
			case "snmp_auth_protocol":
				if (strtoupper($value) == SNMP_AUTH_PROTOCOL_MD5) {
					$host{$key} = SNMP_AUTH_PROTOCOL_MD5;
				} elseif (strtoupper($value) == SNMP_AUTH_PROTOCOL_SHA) {
					$host{$key} = SNMP_AUTH_PROTOCOL_SHA;
				} elseif (strtoupper($value) == SNMP_AUTH_PROTOCOL_NONE) {
					$host{$key} = SNMP_AUTH_PROTOCOL_NONE;
				} else {
					$check["err_msg"] = __("ERROR: Invalid SNMP Authentication Protocol: (%s)", $value);
					return $check;
				}

				break;
			case "snmp_priv_passphrase":
				break;
			case "snmp_priv_protocol":
				if (strtoupper($value) == SNMP_PRIV_PROTOCOL_DES) {
					$host{$key} = SNMP_PRIV_PROTOCOL_DES;
				} elseif (strtoupper($value) == SNMP_PRIV_PROTOCOL_AES128) {
					$host{$key} = SNMP_PRIV_PROTOCOL_AES128;
				} elseif (strtoupper($value) == SNMP_PRIV_PROTOCOL_NONE) {
					$host{$key} = SNMP_PRIV_PROTOCOL_NONE;
				} else {
					$check["err_msg"] = __("ERROR: Invalid SNMP Privacy Protocol: (%s)", $value);
					return $check;
				}

				break;
			case "snmp_context":
				break;
			case "snmp_port":
				if ($value > 0) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid SNMP Port: (%s)", $value);
					return $check;
				}
				break;
			case "snmp_timeout":
				if (($value > 0) && ($value <= 20000)) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid SNMP Timeout: (%s). Valid values are from 1 to 20000", $value);
					return $check;
				}
				break;
			case "availability_method":
				switch(strtolower($value)) {
					case "none":
						$availability_method = '0'; /* tried to use AVAIL_NONE, but then ereg failes on validation, sigh */
						break;
					case "ping":
						$availability_method = AVAIL_PING;
						break;
					case "snmp":
						$availability_method = AVAIL_SNMP;
						break;
					case "pingsnmp":
						$availability_method = AVAIL_SNMP_AND_PING;
						break;
					default:
						$check["err_msg"] = __("ERROR: Invalid Availability Parameter: (%s)", $value);
						return $check;
				}
				$host{$key} = $availability_method;
				break;
			case "ping_method":
				switch(strtolower($value)) {
					case "icmp":
						$ping_method = PING_ICMP;
						break;
					case "tcp":
						$ping_method = PING_TCP;
						break;
					case "udp":
						$ping_method = PING_UDP;
						break;
					default:
						$check["err_msg"] = __("ERROR: Invalid Ping Method: (%s)", $value);
						return $check;
				}
				$host{$key} = $ping_method;
				break;
			case "ping_port":
				if ($value > 0) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid Ping Port: (%s)", $value);
					return $check;
				}
				break;
			case "ping_timeout":
				if ($value > 0) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid Ping Timeout: (%s)", $value);
					return $check;
				}
				break;
			case "ping_retries":
				if ($value > 0) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid Ping Retries: (%s)", $value);
					return $check;
				}
				break;
			case "max_oids":
				if ($value > 0) {
					# fine
				}else{
					$check["err_msg"] = __("ERROR: Invalid Max OIDs: (%s)", $value);
					return $check;
				}
				break;
			case "disabled":
				switch ($value) {
					case 1:
					case 'on':
					case "'on'":
						$host["disabled"]  = '"on"';
						break;
					case 0:
					case '':
					case "''":
					case 'off':
					case "'off'":
						$host["disabled"]  = '""';
						break;
					default:
						$check["err_msg"] = __("ERROR: Invalid disabled flag (%s)", $value);
						return $check;
				}
				break;
			default:
				# host array may contain "unknown" columns due to extensions made by any plugin
				# in future, a validation hook may be implemented here
				/* TODO: validation hook */
		}
	}

	# everything's fine
	return true;
}

/*
 * verifyDataQuery	- verifies all array items for a data query array
 * 					  recodes the array, if necessary
 * @arg $data_query	- data query array (part of host_snmp_query)
 * @arg $ri_check	- request a referential integrity test
 * returns			- if ok, returns true with array recoded; otherwise array containg error message
 */
function verifyDataQuery(&$data_query, $ri_check=false) {

	foreach($data_query as $key => $value) {

		switch ($key) {
			case "host_id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: Id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM host WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This host id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "snmp_query_id":
				if (!(((string) $value) === ((string)(int) $value))) {
					$check["err_msg"] = __("ERROR: SNMP query id must be integer (%s)", $value);
					return $check;
				} elseif ($ri_check) {
					$match = db_fetch_cell("SELECT COUNT(*) FROM snmp_query WHERE id=" . $value);
					if ($match == 0) {
						$check["err_msg"] = __("ERROR: This SNMP query id does not exist (%s)", $value);
						return $check;
					}
				}
				break;
			case "sort_field":
				break;
			case "title_format":
				break;
			case "reindex_method":
				if ((((string) $value) === ((string)(int) $value)) &&
					($value >= DATA_QUERY_AUTOINDEX_NONE) &&
					($value <= DATA_QUERY_AUTOINDEX_VALUE_CHANGE)) {
					$data_query["reindex_method"] = $value;
				} else {
					switch (strtolower($value)) {
						case "none":
							$data_query["reindex_method"] = DATA_QUERY_AUTOINDEX_NONE;
							break;
						case "uptime":
							$data_query["reindex_method"] = DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;
							break;
						case "index":
							$data_query["reindex_method"] = DATA_QUERY_AUTOINDEX_INDEX_COUNT_CHANGE;
							break;
						case "fields":
							$data_query["reindex_method"] = DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION;
							break;
						case "value":
							$data_query["reindex_method"] = DATA_QUERY_AUTOINDEX_VALUE_CHANGE;
							break;
						default:
							$check["err_msg"] = __("ERROR: You must supply a valid reindex method for all devices!") . "\n";
							return $check;
					}
				}
			default:
				# host array may contain "unknown" columns due to extensions made by any plugin
				# in future, a validation hook may be implemented here
				/* TODO: validation hook */
		}
	}

	# everything's fine
	return true;
}
?>