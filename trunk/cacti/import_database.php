#!/usr/bin/php -q
<?
set_time_limit(0);

$input = true;
$order_key_array = array(); /* for graph trees */

/* defaults */
$database_hostname = "localhost";
$database_username = "root";
$database_password = "xixxix";
$database_old = "rrdtool";
$database = "dev_cacti_3";

print "+------------------------------------------------------------------+\n";
print "|  Welcome to the cacti 0.6.8 to 0.8 database converter. This      |\n";
print "|  script will allow you to move all of your old data to the new   |\n";
print "|  table structure.                                                |\n";
print "+------------------------------------------------------------------+\n\n";

print "Enter some information about the database you are connecting to:\n\n";

$stdin = fopen("php://stdin","r");

while($input == true) {
	/* get database hostname from user */
	print "Database Hostname <$database_hostname>: ";
	$buffer = trim(fgets($stdin,256));
	
	if (!empty($buffer)) { $database_hostname = $buffer; }
	
	/* get database username from user */
	print "Database Username <$database_username>: ";
	$buffer = trim(fgets($stdin,256));
	
	if (!empty($buffer)) { $database_username = $buffer; }
	
	/* get database password from user */
	print "Database Password <$database_password>: ";
	$buffer = trim(fgets($stdin,256));
	
	if (!empty($buffer)) { $database_password = $buffer; }
	
	/* get old database name from user */
	print "0.6.8 Database Name <$database_old>: ";
	$buffer = trim(fgets($stdin,256));
	
	if (!empty($buffer)) { $database_old = $buffer; }
	
	/* get new database name from user */
	print "0.8 Database Name <$database>: ";
	$buffer = trim(fgets($stdin,256));
	
	if (!empty($buffer)) { $database = $buffer; }
	
	$input = false;
}

fclose($stdin);

print "\n";

include_once("include/database.php");
include_once("include/snmp_functions.php");
include_once("include/functions.php");

$paths["cacti"] = read_config_option("path_webroot") . read_config_option("path_webcacti");

if ($cnn_id) {
	print "Successfully connected to database: $database\n";
}else{
	exit;
}

print "\n+++++++++++++++++++++++ Importing Users +++++++++++++++++++++++\n";

db_execute("truncate table $database.user");
db_execute("truncate table $database.user_auth_realm");
db_execute("truncate table $database.user_auth_hosts");
db_execute("truncate table $database.user_log");

$_users = db_fetch_assoc("select * from $database_old.auth_users");

if (sizeof($_users) > 0) {
foreach ($_users as $item) {
	if (db_execute("insert into $database.user (id,username,password,must_change_password,show_tree,show_list,
		show_preview,login_opts,graph_policy,full_name) values ('" . $item["ID"] . "','" . $item["Username"] . "',
		'" . $item["Password"] . "','" . $item["MustChangePassword"] . "','" . $item["ShowTree"] . "',
		'" . $item["ShowList"] . "','" . $item["ShowPreview"] . "','" . $item["LoginOpts"] . "',
		'" . $item["GraphPolicy"] . "','" . $item["FullName"] . "')")) {
		
		print "SUCCESS: User: " . $item["Username"] . "\n";
	}else{
		print "FAIL: User: " . $item["Username"] . "\n";
	}
}
}

$_users_acl = db_fetch_assoc("select * from $database_old.auth_acl");

if (sizeof($_users_acl) > 0) {
foreach ($_users_acl as $item) {
	db_execute("insert into $database.user_auth_realm (realm_id,user_id) values ('" . $item["SectionID"] . "',
		'" . $item["UserID"] . "')");
}
}

print "\n + User Permissions.\n";

$_users_hosts = db_fetch_assoc("select * from $database_old.auth_hosts");

if (sizeof($_users_hosts) > 0) {
foreach ($_users_hosts as $item) {
	db_execute("insert into $database.user_auth_hosts (user_id,hostname,policy) values ('" . $item["UserID"] . "',
		'" . $item["Hostname"] . "','" . $item["Type"] . "')");
}
}

print " + Host Permissions.\n";

$_users_logs = db_fetch_assoc("select * from $database_old.auth_log");

if (sizeof($_users_logs) > 0) {
foreach ($_users_logs as $item) {
	db_execute("insert into $database.user_log (username,time,result,ip) values ('" . $item["Username"] . "',
		'" . $item["Time"] . "','" . $item["Success"] . "','" . $item["IP"] . "')");
}
}

print " + User Login Log.\n";

print "\n+++++++++++++++++++++++ Importing Data Input Sources +++++++++++++++++++++++\n";

db_execute("delete from data_input_fields where data_input_id != 33");
db_execute("delete from data_input where id != 33");

$_src = db_fetch_assoc("select * from $database_old.src where id != 11 or id != 13");

if (sizeof($_src) > 0) {
foreach ($_src as $item) {
	if (db_execute("insert into $database.data_input (id,name,input_string,output_string,type_id) values (0,
		'" . $item["Name"] . "','" . $item["FormatStrIn"] . "','" . $item["FormatStrOut"] . "',
		1)")) {
		$data_input_cache{$item["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
		print "SUCCESS: Data Input Source: " . $item["Name"] . "\n";
		
		$_src_fields = db_fetch_assoc("select * from $database_old.src_fields where srcid=" . $item["ID"]);
		
		if (sizeof($_src_fields) > 0) {
		foreach ($_src_fields as $item2) {
			if (db_execute("insert into data_input_fields (id,data_input_id,name,data_name,input_output,
				update_rra,sequence,type_code,regexp_match,allow_nulls) values (0,
				'" . $data_input_cache{$item["ID"]} . "','" . $item2["Name"] . "','" . $item2["DataName"] . "',
				'" . $item2["InputOutput"] . "','" . $item2["UpdateRRA"] . "',0,'','','')")) {
				$data_input_field_cache{$item2["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
				print "   SUCCESS: Data Input Field: " . $item2["Name"] . "\n";
			}else{
				print "   FAIL: Data Input Field: " . $item2["Name"] . "\n";
			}
		}
		}
	}else{
		print "FAIL: Data Input Source: " . $item["Name"] . "\n";
	}
	
	print "\n";
}
}

print "\n+++++++++++++++++++++++ Importing SNMP Hosts +++++++++++++++++++++++\n";

db_execute("truncate table $database.host");
db_execute("truncate table $database.host_snmp_query");
db_execute("truncate table $database.host_snmp_cache");

$_hosts = db_fetch_assoc("select * from $database_old.snmp_hosts");

if (sizeof($_hosts) > 0) {
foreach ($_hosts as $item) {
	if (db_execute("insert into host (id,host_template_id,description,hostname,management_ip,snmp_community,
		snmp_version,snmp_username,snmp_password) values (0,0,'" . $item["Hostname"] . "',
		'" . $item["Hostname"] . "','" . gethostbyname($item["Hostname"]) . "','" . $item["Community"] . "',
		1,'','')")) {
		$host_id = db_fetch_cell("select LAST_INSERT_ID()");
		db_execute("insert into host_snmp_query (host_id,snmp_query_id) values ($host_id,1)");
		
		print "SUCCESS: Host: " . $item["Hostname"] . "\n";
		
		print "   Re-caching interface data for host: " . $item["Hostname"] . "\n";
		//query_snmp_host($host_id, 1);
	}else{
		print "FAIL: Host: " . $item["Hostname"] . "\n";
	}
}
}

print "\n+++++++++++++++++++++++ Data Sources +++++++++++++++++++++++\n";

$non_templated_data_sources = db_fetch_assoc("select id from data_template_data where local_data_id > 0");

if (sizeof($non_templated_data_sources) > 0) {
foreach ($non_templated_data_sources as $item) {
	db_execute("delete from data_template_data_rra where data_template_data_id=" . $item["id"]);
	db_execute("delete from data_input_data where data_template_data_id=" . $item["id"]);
}
}

db_execute("truncate table $database.data_local");
db_execute("delete from $database.data_template_data where local_data_id > 0");
db_execute("delete from $database.data_template_rrd where local_data_id > 0");

$_ds = db_fetch_assoc("select * from $database_old.rrd_ds where subdsid=0");

if (sizeof($_ds) > 0) {
foreach ($_ds as $item) {
	$hostname = db_fetch_cell("select value from $database_old.src_data where dsid=" . $item["ID"] . " and fieldid=21");
	$host_id = 0;
	
	if (!empty($hostname)) {
		$host_id = db_fetch_cell("select id from host where hostname='$hostname'");
	}
	
	if (db_execute("insert into data_local (id,data_template_id,host_id) values (0,0,$host_id)")) {
		$local_data_id = db_fetch_cell("select LAST_INSERT_ID()");
		print "SUCCESS: Local Data Entry: " . $item["Name"] . "\n";
		
		if (db_execute("insert into data_template_data (id,local_data_template_data_id,local_data_id,
			data_template_id,data_input_id,name,data_source_path,active,rrd_step) values (0,0,$local_data_id,
			0," . $data_input_cache{$item["SrcID"]} . ",'" . $item["Name"] . "','" . $item["DSPath"] . "',
			'" . $item["Active"] . "','" . $item["Step"] . "')")) {
			$data_template_data_cache{$item["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
			print "   SUCCESS: Data Source: " . $item["Name"] . "\n";
			
			if ($item["IsParent"] == "0") {
				if (db_execute("insert into data_template_rrd (id,local_data_template_rrd_id,
					local_data_id,data_template_id,rrd_maximum,rrd_minimum,rrd_heartbeat,
					data_source_type_id,data_source_name,data_input_field_id) values (0,0,$local_data_id,0,
					" . $item["MaxValue"] . "," . $item["MinValue"] . "," . $item["Heartbeat"] . ",
					" . $item["DataSourceTypeID"] . ",'" . $item["DSName"] . "',0)")) {
					$data_template_rrd_cache{$item["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
					print "   SUCCESS: Data Source Item: " . $item["DSName"] . "\n";
				}else{
					print "   FAIL: Data Source Item: " . $item["DSName"] . "\n";
				}
			}elseif ($item["IsParent"] == "1") {
				$_sub_ds = db_fetch_assoc("select * from $database_old.rrd_ds where subdsid=" . $item["ID"]);
				
				if (sizeof($_sub_ds) > 0) {
				foreach ($_sub_ds as $item2) {
					if (db_execute("insert into data_template_rrd (id,local_data_template_rrd_id,
						local_data_id,data_template_id,rrd_maximum,rrd_minimum,rrd_heartbeat,
						data_source_type_id,data_source_name,data_input_field_id) values (0,0,$local_data_id,0,
						" . $item2["MaxValue"] . "," . $item2["MinValue"] . "," . $item2["Heartbeat"] . ",
						" . $item2["DataSourceTypeID"] . ",'" . $item2["DSName"] . "',
						" . $data_input_field_cache{$item2["SubFieldID"]} . ")")) {
						$data_template_rrd_cache{$item2["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
						print "   SUCCESS: Data Source Item (sub): " . $item2["DSName"] . "\n";
					}else{
						print "   FAIL: Data Source Item (sub): " . $item2["DSName"] . "\n";
					}
				}
				}
			}
			
			/* ds data */
			$_ds_data = db_fetch_assoc("select * from $database_old.src_data where DSID=" . $item["ID"]);
			
			if (sizeof($_ds_data) > 0) {
			foreach ($_ds_data as $item2) {
				if (db_execute("insert into data_input_data (data_input_field_id,data_template_data_id,
					t_value,value) values (" . $data_input_field_cache{$item2["FieldID"]} . ",
					" . $data_template_data_cache{$item2["DSID"]} . ",'','" . $item2["Value"] . "')")) {
					print "   SUCCESS: Data Source Data: " . $item2["Value"] . "\n";
				}else{
					print "   FAIL: Data Source Data: " . $item2["Value"] . "\n";
				}
			}
			}
			
			/* ds->rra mappings */
			$_ds_rra = db_fetch_assoc("select * from $database_old.lnk_ds_rra where DSID=" . $item["ID"]);
			
			if (sizeof($_ds_rra) > 0) {
			foreach ($_ds_rra as $item2) {
				if (db_execute("insert into data_template_data_rra (data_template_data_id,rra_id) values
					(" . $data_template_data_cache{$item2["DSID"]} . "," . $item2["RRAID"] . ")")) {
					print "   SUCCESS: Data Source -> RRA Mapping: RRA ID: " . $item2["RRAID"] . "\n";
				}else{
					print "   FAIL: Data Source -> RRA Mapping: RRA ID: " . $item2["RRAID"] . "\n";
				}
			}
			}
		}else{
			print "   FAIL: Data Source: " . $item["Name"] . "\n";
		}
	}else{
		print "FAIL: Local Data Entry: " . $item["Name"] . "\n";
	}
	
	print "\n";
}
}

print "\n+++++++++++++++++++++++ CDEF's +++++++++++++++++++++++\n";

db_execute("truncate table $database.cdef");
db_execute("truncate table $database.cdef_items");

$_cdef = db_fetch_assoc("select * from $database_old.rrd_ds_cdef");

if (sizeof($_cdef) > 0) {
foreach ($_cdef as $item) {
	if (db_execute("insert into cdef (id,name) values (0,'" . $item["Name"] . "')")) {
		$cdef_cache{$item["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
		print "SUCCESS: CDEF: " . $item["Name"] . "\n";
		
		$_cdef_items = db_fetch_assoc("select * from $database_old.rrd_ds_cdef_item where CDEFID=" . $cdef_cache{$item["ID"]});
		
		if ($item["Type"] == "2") {
			$_cdef_items[0]["CDEFID"] = $item["ID"];
			$_cdef_items[0]["Type"] = "Total";
			$_cdef_items[0]["Sequence"] = "1";
		}
		
		$cdef_ds_counter = 0;
		if (sizeof($_cdef_items) > 0) {
		foreach ($_cdef_items as $item2) {
			switch ($item2["Type"]) {
			case 'Custom Entry':
				$item_type = 6;
				$item_value = $item2["Custom"];
				break;
			case 'Data Source':
				if ($item2["CurrentDS"] == "on") {
					$item_type = 4;
					$item_value = "CURRENT_DATA_SOURCE";
				}else{
					$item_type = 6;
					$item_value = generate_graph_def_name("$cdef_ds_counter");
					$cdef_ds_counter++;
				}
				break;
			case 'CDEF Function':
				if (ereg('^(27|28|29|30|31)$', $item2["CDEFFunctionID"])) {
					$item_type = 2;
					$item_value = ($item2["CDEFFunctionID"] - 26);
				}else{
					$item_type = 1;
					$item_value = $item2["CDEFFunctionID"];
				}
				break;
			case 'Total':
				$item_type = 4;
				$item_value = "ALL_DATA_SOURCES_NODUPS";
				break;
			}
			
			if (db_execute("insert into cdef_items (id,cdef_id,sequence,type,value) values (0," . $cdef_cache{$item2["CDEFID"]} . ",
				" . $item2["Sequence"] . ",$item_type,'$item_value')")) {
				print "   SUCCESS: CDEF Item: Type: $item_type, Value: $item_value\n";
			}
		}
		}
	}else{
		print "FAIL: CDEF: " . $item["Name"] . "\n";
	}
	
	print "\n";
}
}

print "\n+++++++++++++++++++++++ Graphs +++++++++++++++++++++++\n";

db_execute("truncate table $database.graph_local");
db_execute("delete from $database.graph_templates_graph where local_graph_id > 0");
db_execute("delete from $database.graph_templates_item where local_graph_id > 0");

$_graphs = db_fetch_assoc("select * from $database_old.rrd_graph");

if (sizeof($_graphs) > 0) {
foreach ($_graphs as $item) {
	if (db_execute("insert into graph_local (id,graph_template_id) values (0,0)")) {
		$local_graph_id_cache{$item["ID"]} = db_fetch_cell("select LAST_INSERT_ID()");
		print "SUCCESS: Local Graph Entry: " . $item["Title"] . "\n";
		
		if (db_execute("insert into graph_templates_graph (id,local_graph_template_graph_id,local_graph_id,graph_template_id,image_format_id,title,
			height,width,upper_limit,lower_limit,vertical_label,auto_scale,auto_scale_opts,auto_scale_log,
			auto_scale_rigid,auto_padding,base_value,export,unit_value,unit_exponent_value) values (
			0,0," . $local_graph_id_cache{$item["ID"]} . ",0," . $item["ImageFormatID"] . ",'" . $item["Title"] . "'," . $item["Height"] . ",
			" . $item["Width"] . "," . $item["UpperLimit"] . "," . $item["LowerLimit"] . ",'" . $item["VerticalLabel"] . "',
			'" . $item["AutoScale"] . "','" . $item["AutoScaleOpts"] . "','" . $item["AutoScaleLog"] . "',
			'" . $item["Rigid"] . "','" . $item["AutoPadding"] . "','" . $item["BaseValue"] . "',
			'" . $item["Export"] . "','" . $item["UnitValue"] . "','" . $item["UnitExponentValue"] . "')")) {
			print "   SUCCESS: Graph Entry: " . $item["Title"] . "\n";
			
			$_graph_items = db_fetch_assoc("select * from $database_old.rrd_graph_item where GraphID=" . $item["ID"] . " order by SequenceParent,Sequence");
			
			$seq = 0;
			if (sizeof($_graph_items) > 0) {
			foreach ($_graph_items as $item2) {
				$seq++;
				
				if ($item2["GprintOpts"] == "1") {
					$gprint_id = 2;
				}elseif ($item2["GprintOpts"] == "2") {
					$gprint_id = 3;
				}
				
				if (!isset($cdef_cache{$item2["CDEFID"]})) {
					$cdef_cache{$item2["CDEFID"]} = 0;
				}
				
				if (!isset($data_template_rrd_cache{$item2["DSID"]})) {
					$data_template_rrd_cache{$item2["DSID"]} = 0;
				}
				
				if (db_execute("insert into graph_templates_item (id,local_graph_template_item_id,
					local_graph_id,graph_template_id,task_item_id,color_id,graph_type_id,cdef_id,
					consolidation_function_id,text_format,value,hard_return,gprint_id,sequence) 
					values (0,0," . $local_graph_id_cache{$item["ID"]} . ",0," . $data_template_rrd_cache{$item2["DSID"]} . ",
					" . $item2["ColorID"] . "," . $item2["GraphTypeID"] . "," . $cdef_cache{$item2["CDEFID"]} . ",
					" . $item2["ConsolidationFunction"] . ",'" . $item2["TextFormat"] . "',
					'" . $item2["Value"] . "','" . $item2["HardReturn"] . "',$gprint_id,$seq)")) {
					print "   SUCCESS: Graph Item Entry: " . $item2["TextFormat"] . "\n";
				}else{
					print "   FAIL: Graph Item Entry: " . $item2["TextFormat"] . "\n";
				}
			}
			}
		}else{
			print "   FAIL: Graph Entry: " . $item["Title"] . "\n";
		}
	}else{
		print "FAIL: Local Graph Entry: " . $item["Name"] . "\n";
	}
	
	print "\n";
}
}

print "\n+++++++++++++++++++++++ Graph Trees +++++++++++++++++++++++\n";

db_execute("truncate table $database.graph_tree");
db_execute("truncate table $database.graph_tree_items");

$_tree = db_fetch_assoc("select * from $database_old.graph_hierarchy");

if (sizeof($_tree) > 0) {
foreach ($_tree as $item) {
	if (db_execute("insert into graph_tree (id,user_id,name) values (0,0,'" . $item["Name"] . "')")) {
		$graph_tree_id = db_fetch_cell("select LAST_INSERT_ID()");
		print "SUCCESS: Graph Tree: " . $item["Name"] . "\n";
		climb_tree(0, $item["ID"], 0, "", "", "");
		
		$_tree_items = db_fetch_assoc("select * from $database_old.graph_hierarchy_items where TreeID=" . $item["ID"]);
		
		if (sizeof($_tree_items) > 0) {
		foreach ($_tree_items as $item2) {
			if (!isset($local_graph_id_cache{$item2["GraphID"]})) {
				$local_graph_id_cache{$item2["GraphID"]} = 0;
			}
			
			if (db_execute("insert into graph_tree_items (id,graph_tree_id,local_graph_id,rra_id,title,
				order_key) values (0,$graph_tree_id," . $local_graph_id_cache{$item2["GraphID"]} . ",
				" . $item2["RRAID"] . ",'" . $item2["Title"] . "','" . $order_key_array{$item2["ID"]} . "')")) {
				print "   SUCCESS: Graph Tree Item: " . $item2["ID"] . "/" . $item2["Title"] . "\n";
			}else{
				print "   FAIL: Graph Tree Item: " . $item2["ID"] . "/" . $item2["Title"] . "\n";
			}
		}
		}
	}else{
		print "FAIL: Graph Tree: " . $item["Name"] . "\n";
	}
	
	print "\n";
}
}

function climb_tree($parent, $tree_id, $branch, $prefix_key, $item_count_array) {
	global $database_old, $order_key_array;
	
	$tree = db_fetch_assoc("select ID from $database_old.graph_hierarchy_items where TreeID=$tree_id and Parent=$parent order by Sequence");
	
	if (sizeof($tree) > 0) {
	foreach ($tree as $item) {
		$item_count_array[$branch]++;
		
		$current_key_item = str_pad($item_count_array[$branch],2,'0',STR_PAD_LEFT);
		$order_key = str_pad("$prefix_key$current_key_item",60,'0',STR_PAD_RIGHT);
		$local_prefix_key = "$prefix_key$current_key_item";
		
		$order_key_array{$item["ID"]} = $order_key;
		
		if (sizeof(db_fetch_assoc("select ID from $database_old.graph_hierarchy_items where TreeID=$tree_id and Parent=" . $item["ID"])) > 0) {
			climb_tree($item["ID"], $tree_id, ($branch+1), "$local_prefix_key", $item_count_array);
		}
	}
	}
	
	return $branch;
}























?>
