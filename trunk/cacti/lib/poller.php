<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

/* exec_background - executes a program in the background so that php can continue
     to execute code in the foreground
   @arg $filename - the full pathname to the script to execute
   @arg $args - any additional arguments that must be passed onto the executable */
function exec_background($filename, $args = "") {
	global $config;

	if (file_exists($filename)) {
		if ($config["cacti_server_os"] == "win32") {
			pclose(popen("start \"Cactiplus\" /I /B \"" . $filename . "\" " . $args, "r"));
		}else{
			exec($filename . " " . $args . " > /dev/null &");
		}
	}
}

/* update_reindex_cache - builds a cache that is used by the poller to determine if the
     indexes for a particular data query/host have changed
   @arg $host_id - the id of the host to which the data query belongs
   @arg $data_query_id - the id of the data query to rebuild the reindex cache for */
function update_reindex_cache($host_id, $data_query_id) {
	global $config;

	include_once($config["library_path"] . "/data_query.php");

	/* will be used to keep track of sql statements to execute later on */
	$recache_stack = array();

	$data_query = db_fetch_row("select reindex_method,sort_field from host_snmp_query where host_id=$host_id and snmp_query_id=$data_query_id");
	$data_query_type = db_fetch_cell("select data_input.type_id from data_input,snmp_query where data_input.id=snmp_query.data_input_id and snmp_query.id=$data_query_id");
	$data_query_xml = get_data_query_array($data_query_id);

	switch ($data_query["reindex_method"]) {
		case DATA_QUERY_AUTOINDEX_NONE:
			break;
		case DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME:
			$assert_value = db_fetch_cell("select assert_value from poller_reindex where host_id=$host_id and data_query_id=$data_query_id and arg1='.1.3.6.1.2.1.1.3.0'");

			/* default uptime */
			if (empty($assert_value)) {
				$assert_value = time();
			}

			array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'<','$assert_value','.1.3.6.1.2.1.1.3.0')");

			break;
		case DATA_QUERY_AUTOINDEX_INDEX_NUM_CHANGE:
			/* this method requires that some command/oid can be used to determine the
			 * current number of indexes in the data query */
			$assert_value = sizeof(db_fetch_assoc("select snmp_index from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id group by snmp_index"));

			if ($data_query_type == DATA_INPUT_TYPE_SNMP_QUERY) {
				if (isset($data_query_xml["oid_num_indexes"])) {
					array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'=','$assert_value','" . $data_query_xml["oid_num_indexes"] . "')");
				}
			}else if ($data_query_type == DATA_INPUT_TYPE_SCRIPT_QUERY) {
				if (isset($data_query_xml["arg_num_indexes"])) {
					array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,1,'=','$assert_value','" . get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . $data_query_xml["arg_num_indexes"], $data_query_xml["script_path"], $host_id) . "')");
				}
			}

			break;
		case DATA_QUERY_AUTOINDEX_FIELD_VERIFICATION:
			$primary_indexes = db_fetch_assoc("select snmp_index,oid,field_value from host_snmp_cache where host_id=$host_id and snmp_query_id=$data_query_id and field_name='" . $data_query["sort_field"] . "'");

			if (sizeof($primary_indexes) > 0) {
				foreach ($primary_indexes as $index) {
					$assert_value = $index["field_value"];

					if ($data_query_type == DATA_INPUT_TYPE_SNMP_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,0,'=','$assert_value','" . $data_query_xml["fields"]{$data_query["sort_field"]}["oid"] . "." . $index["snmp_index"] . "')");
					}else if ($data_query_type == DATA_INPUT_TYPE_SCRIPT_QUERY) {
						array_push($recache_stack, "insert into poller_reindex (host_id,data_query_id,action,op,assert_value,arg1) values ($host_id,$data_query_id,1,'=','$assert_value','" . get_script_query_path((isset($data_query_xml["arg_prepend"]) ? $data_query_xml["arg_prepend"] . " ": "") . $data_query_xml["arg_get"] . " " . $data_query_xml["fields"]{$data_query["sort_field"]}["query_name"] . " " . $index["snmp_index"], $data_query_xml["script_path"], $host_id) . "')");
					}
				}
			}

			break;
	}

	/* save the delete for last since we need to reference this table in the code above */
	db_execute("delete from poller_reindex where host_id=$host_id and data_query_id=$data_query_id");

	for ($i=0; $i<count($recache_stack); $i++) {
		db_execute($recache_stack[$i]);
	}
}

?>
