<?/* 
   +-------------------------------------------------------------------------+
   | Copyright (C) 2002 Ian Berry                                            |
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
   | cacti: the rrdtool frontend [php-auth, php-tree, php-form]              |
   +-------------------------------------------------------------------------+
   | This code is currently maintained and debugged by Ian Berry, any        |
   | questions or comments regarding this code should be directed to:        |
   | - iberry@raxnet.net                                                     |
   +-------------------------------------------------------------------------+
   | - raXnet - http://www.raxnet.net/                                       |
   +-------------------------------------------------------------------------+
   */?>
<?

function read_graph_config_option($config_name) {
	include ("config_settings.php");
	
	$graph_config_array = unserialize($_SESSION["sess_graph_config_array"]);
	
	if (!isset($graph_config_array[$config_name])) {
		$graph_config_array[$config_name] = db_fetch_cell("select value from settings_graphs where name='$config_name' and user_id=" . $_SESSION["sess_user_id"]);
		
		if (empty($graph_config_array[$config_name])) {
			$graph_config_array[$config_name] = $settings_graphs[$config_name]["default"];
		}
			
		$_SESSION["sess_graph_config_array"] = serialize($graph_config_array);
	}
	
	return $graph_config_array[$config_name];
}

function read_config_option($config_name) {
	$config_array = unserialize($_SESSION["sess_config_array"]);
	
	if (!isset($config_array[$config_name])) {
		$config_array[$config_name] = db_fetch_cell("select value from settings where name='$config_name'");
		$_SESSION["sess_config_array"] = serialize($config_array);
	}
	
	return $config_array[$config_name];
}

function form_input_validate($field_value, $field_name, $regexp_match, $allow_nulls, $custom_message = 3) {
	if (($allow_nulls == true) && ($field_value == "")) {
		$array_field_names = unserialize($_SESSION["sess_field_values"]);
		$array_field_names[$field_name] = $field_value;
		$_SESSION["sess_field_values"] = serialize($array_field_names);
		
		return $field_value;
	}
	
	if ((!ereg($regexp_match, $field_value) || (($allow_nulls == false) && ($field_value == "")))) {
		raise_message($custom_message);
		
		$array_error_fields = unserialize($_SESSION["sess_error_fields"]);
		$array_error_fields[$field_name] = $field_name;
		$_SESSION["sess_error_fields"] = serialize($array_error_fields);
	}else{
		$array_field_names = unserialize($_SESSION["sess_field_values"]);
		$array_field_names[$field_name] = $field_value;
		$_SESSION["sess_field_values"] = serialize($array_field_names);
	}
	
	return $field_value;
}

function is_error_message() {
	include("config_arrays.php");
	
	$array_messages = unserialize($_SESSION["sess_messages"]);
	
	if (is_array($array_messages)) {
		foreach (array_keys($array_messages) as $current_message_id) {
			if ($messages[$current_message_id]["type"] == "error") { return true; }
		}
	}
	
	return false;
}

function raise_message($message_id) {
	$array_messages = unserialize($_SESSION["sess_messages"]);
	$array_messages[$message_id] = $message_id;
	$_SESSION["sess_messages"] = serialize($array_messages);
}

function display_output_messages() {
	include("config_arrays.php");
	include_once("form.php");
	global $colors;
	
	$array_messages = unserialize($_SESSION["sess_messages"]);
	
	if (is_array($array_messages)) {
		foreach (array_keys($array_messages) as $current_message_id) {
			eval ('$message = "' . $messages[$current_message_id]["message"] . '";');
			
			switch ($messages[$current_message_id]["type"]) {
			case 'info':
				start_box("", "98%", "00438C", "3", "center", "");
				print "<tr><td bgcolor='#f5f5f5'><p class='textInfo'>$message</p></td></tr>";
				end_box();
				break;
			case 'error':
				start_box("", "98%", "ff0000", "3", "center", "");
				print "<tr><td bgcolor='#f5f5f5'><p class='textError'>Error: $message</p></td></tr>";
				end_box();
				break;
			}
		}
	}
	
	session_unregister("sess_messages");
}

function array_rekey($array, $key, $key_value) {
	if (sizeof($array) > 0) {
	foreach ($array as $item) {
		$item_key = $item[$key];
		
		$ret_array[$item_key] = $item[$key_value];
	}
	}
	
	return $ret_array;
}

function LogData($string) {
    global $config,$colors,$paths;
    
    /* fill in the current date for printing in the log */
    $date = date("m/d/Y g:i A");
    
    /* echo the data to the log (append) */
    exec("echo '$date - $string' >> $paths[log]");
}

function GetCronPath($dsid) {
    global $cnn_id,$config,$paths;
	
    $data = db_fetch_row("select d.ID, d.SrcID,
		s.FormatStrIn, s.FormatStrOut
	    from rrd_ds d 
	    left join src s 
	    on d.srcid=s.id 
	    where d.id=$dsid");
	
    if (sizeof($data) > 0) {
		/* make the input string */
		$fields = db_fetch_assoc("select d.FieldID, d.DSID, d.Value,
					   f.SrcID, f.DataName
					   from src_data d
					   left join src_fields f
					   on d.fieldid=f.id
					   where d.dsid=$data[ID]
					   and f.srcid=$data[SrcID]");
		$rows_fields = sizeof($fields);
		
		/* put the input string into a variable for easy access (r) */
		$str = $data[FormatStrIn];
		
		/* loop through each input field we find in the database and do a replace on
		 each one accordingly. */
	if (sizeof($fields) > 0) {
	    foreach ($fields as $field) {
		$str = ereg_replace ("<$field[DataName]>","$field[Value]",$str);
	    }
	}
		
		/* do a little path subsitution */
		$str = ereg_replace ("<path_cacti>", $paths[cacti],$str);
		$str = ereg_replace ("<path_snmpget>", read_config_option("path_snmpget"),$str);
		$str = ereg_replace ("<path_php_binary>", read_config_option("path_php_binary"),$str);
		
		return $str;
    }
}

function get_full_script_path($local_data_id) {
	global $paths;
	
	$data_source = db_fetch_row("select
		data_template_data.id,
		data_template_data.data_input_id,
		data_input.type_id,
		data_input.input_string
		from data_template_data,data_input
		where data_template_data.data_input_id=data_input.id
		and data_template_data.local_data_id=$local_data_id");
	
	if ($data_source["type_id"] > 1) {
		return 0;
	}
	
	$data = db_fetch_assoc("select
		data_input_fields.data_name,
		data_input_data.value
		from data_input_fields
		left join data_input_data
		on data_input_fields.id=data_input_data.data_input_field_id
		where data_input_fields.data_input_id=" . $data_source["data_input_id"] . "
		and data_input_fields.input_output='in'");
	
	$full_path = $data_source["input_string"];
	
	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		$full_path = str_replace("<" . $data["data_name"] . ">", $data["value"], $full_path);
	}
	}
	
	$full_path = str_replace("<path_cacti>", $paths["cacti"], $full_path);
	$full_path = str_replace("<path_snmpget>", read_config_option("path_snmpget"), $full_path);
	$full_path = str_replace("<path_php_binary>", read_config_option("path_php_binary"), $full_path);
	
	return $full_path;
}

function get_data_source_name($data_template_rrd_id) {    
	if (empty($data_template_rrd_id)) { return ""; }
	
	$data_source = db_fetch_row("select
		data_template_rrd.data_source_name,
		data_template_data.name
		from data_template_rrd,data_template_data
		where data_template_rrd.local_data_id=data_template_data.local_data_id
		and data_template_rrd.id=$data_template_rrd_id");
	
	/* use the cacti ds name by default or the user defined one, if entered */
	if (empty($data_source["data_source_name"])) {
		/* limit input to 19 characters */
		$data_source_name = clean_up_name($data_source["name"]);
		$data_source_name = substr(strtolower($data_source_name),0,(19-strlen($data_template_rrd_id))) . $data_template_rrd_id;
		
		return $data_source_name;
	}else{
		return $data_source["data_source_name"];
	}
}

function get_data_source_path($local_data_id, $expand_paths) {
	global $paths;
	
    	if (empty($local_data_id)) { return ""; }
    	
    	$data_source = db_fetch_row("select name,data_source_path from data_template_data where local_data_id=$local_data_id");
    	
	if (sizeof($data_source) > 0) {
		if (empty($data_source["data_source_path"])) {
			/* no custom path was specified */
			$data_source_path = generate_data_source_path($local_data_id);
		}else{
			if (!strstr($data_source["data_source_path"], "/")) {
				$data_source_path = "<path_rra>/" . $data_source["data_source_path"];
			}else{
				$data_source_path = $data_source["data_source_path"];
			}
		}
		
		/* whether to show the "actual" path or the <path_rra> variable name (for edit boxes) */
		if ($expand_paths == true) {
			$data_source_path = str_replace("<path_rra>", $paths["rra"], $data_source_path);
		}
		
		return $data_source_path;
	}
}

function CheckDataSourceName($data_source_name) {
    $new_data_source_name = str_replace(" ","_",$data_source_name);
    $new_data_source_name = ereg_replace("[*]|[/]|[\]|[*]|[&]|[%]|[\"]|[\']|[,]|[.]","",$new_data_source_name);
    $new_data_source_name = substr($new_data_source_name,0,19);
    
    return strtolower($new_data_source_name);
}

function clean_up_name($string) {
    $string = preg_replace("/[\s\.]+/", "_", $string);
    $string = preg_replace("/[*\/\*&%\"\',]/", "", $string);
    
    return $string;
}

function generate_data_source_path($local_data_id) {
	$data_source_name = db_fetch_cell("select name from data_template_data where local_data_id=$local_data_id");
	
	return "<path_rra>/" . substr(strtolower(clean_up_name($data_source_name)), 0, 15) . "_" . $local_data_id . ".rrd";
}

function generate_graph_def_name($graph_item_id) {
    $lookup_table = array("a","b","c","d","e","f","g","h","i","j");
    
    for($i=0; $i<strlen($graph_item_id); $i++) {
		$current_charcter = $graph_item_id[$i];
		$result .= $lookup_table[$current_charcter];
    }
    
    return $result;
}

function GetMultiCdefID($type, $value) {
    /* 	Type codes:
     3 - Total	 staggered datasources.
     5 - Average staggered datasources. */
    
    if ($type == 3 || $type == 5) {
		parse_str($value);
		return $start;
    }
    
    return "";
}

function GetGraphDefID($graph_item_id, $def_items) {
    $result = "";
    
    for($i=0; $i<sizeof($def_items); $i++) {
		if ($def_items[$i] == $graph_item_id) {
		    $result = "345" . $i;
		}
    }
    
    if ($result == "") {
		$result = $graph_item_id;
    }
    
    return $result;
}


function SyncDataSourceName($data_source_id, $saved_custom_data_source_name, $saved_custom_data_source_path) {
    global $cnn_id;
    #include_once("include/database.php");
    
    /* get old data for comparison */
    if ($data_source_id != 0) {
		$data = db_fetch_row("select dsname,dspath,name from rrd_ds where id=$data_source_id");
		
		if (sizeof($data) > 0) {
		    $old_data_source_name = $data[Name];
		    $old_custom_data_source_path = $data[DSPath];
		    $old_custom_data_source_name = $data[DSName];
		}
    }
    
    $dsname = $saved_custom_data_source_name;
    $dspath = $saved_custom_data_source_path;
    
    /* only update the dspath if there isn't already one */
    if ($old_custom_data_source_path == "") {
		$dspath = GetDataSourcePath($data_source_id, false);
    }
    
    /* only update the dsname if there isn't already one */
    if ($old_custom_data_source_name == "") {
		$dsname = GetDataSourceName($data_source_id);
    }
    
    db_execute("update rrd_ds set dsname=\"$dsname\", dspath=\"$dspath\" where id=$data_source_id");
    
    /* find out if this DS has children; if it does, then write the dspath to each child */
    db_execute("update rrd_ds set dspath=\"$dspath\" where subdsid=$data_source_id");
}

function CreateList($data,$name,$value,$prev) {
    if (($name == "") && ($value == "")) {
	foreach (array_keys($data) as $id) {
	    print '<option value="' . $id . '"';
	    
		if ($prev == $id) {
			print " selected";
	    }
		
	    print ">" . $data[$id] . "</option>\n";
	}
    }else{
	foreach ($data as $row) {
	    print "<option value='$row[$value]'";
	    
		if ($prev == $row[$value]) {
			print " selected";
	    }
		
	    print ">$row[$name]</option>\n";
	}
    }
}

function get_next_item($tblname,$field,$startid,$lmt_query) {
    $data1 = db_fetch_row("select max($field) mymax from $tblname where $lmt_query");
    $end_seq = $data1[mymax];
    $data2 = db_fetch_row("select $field from $tblname where id=$startid");
    $start_seq = $data2[$field];
    
    $i = $start_seq;
    if ($end_seq != $start_seq) {
		while ($i < $end_seq) {
		    $data3 = db_fetch_row("select $field from $tblname where $field=$i+1 and $lmt_query");
		    if (sizeof($data3) > 0) { return $data3[$field]; }
		    $i++;
		}
    }
	
    return $start_seq;
}

function get_last_item($tblname,$field,$startid,$lmt_query) {
    $data1 = db_fetch_row("select min($field) mymin from $tblname where $lmt_query");
    $end_seq = $data1[mymin];
    $data2 = db_fetch_row("select $field from $tblname where id=$startid");
    $start_seq = $data2[$field];
    
    $i = $start_seq;
    if ($end_seq != $start_seq) {
		while ($i > $end_seq) {
		    $data3 = db_fetch_row("select $field from $tblname where $field=$i-1 and $lmt_query");
		    if (sizeof($data3) > 0 && $data3[$field] != 0) {
				return $data3[$field];
		    }
		    $i--;
		}
    }
	
    return $start_seq;
}

function get_sequence($id, $field, $table_name, $group_query) {
    if (($id=="0") || ($id == "")) {
		$data = db_fetch_row("select max($field)+1 as seq from $table_name where $group_query");
		
		if ($data[seq] == "") {
		    return 1;
		}else{
		    return $data[seq];
		}
	}else{
		$data = db_fetch_row("select $field from $table_name where id=$id");
		return $data[$field];
    }
}

function move_item_down($table_name, $current_id, $group_query) {
	$next_item = get_next_item($table_name, "sequence", $current_id, $group_query);
	
	$id = db_fetch_cell("select id from $table_name where sequence=$next_item and $group_query");
	$sequence = db_fetch_cell("select sequence from $table_name where id=$current_id");
	db_execute("update $table_name set sequence=$next_item where id=$current_id");
	db_execute("update $table_name set sequence=$sequence where id=$id");
}

function move_item_up($table_name, $current_id, $group_query) {
	$last_item = get_last_item($table_name, "sequence", $current_id, $group_query);
	
	$id = db_fetch_cell("select id from $table_name where sequence=$last_item and $group_query");
	$sequence = db_fetch_cell("select sequence from $table_name where id=$current_id");
	db_execute("update $table_name set sequence=$last_item where id=$current_id");
	db_execute("update $table_name set sequence=$sequence where id=$id");
}

function ParseDelimitedLine($str,$delimiter) {
    if ($delimiter == "") {
		$fill_array[0] = $str;
		return $fill_array;
    }else{
		$fill_array = explode($delimiter, $str);
		return $fill_array;
    }
}

function exec_into_array($command_line) {
    exec($command_line,$out,$err);
    
    for($i=0; list($key, $value) = each($out); $i++) {
		$command_array[$i] = $value;
    }
    
    return $command_array;
}

function convert_mac_address($mac_address) {
    return strtolower(str_replace(" ", ":", $mac_address));
}

function hex2bin($data) {
    $len = strlen($data);
    
    for($i=0;$i<$len;$i+=2) {
		$newdata .=  pack("C",hexdec(substr($data,$i,2)));
    }
    
    return $newdata;
}

function BuildGraphTitleFromSNMP($graph_parameters) {
    $data = db_fetch_row("select * from snmp_hosts_interfaces where id=" . $graph_parameters["snmp_interface_id"]);
    
    if ($data > 0) {
		$graph_parameters["unparsed_graph_title"] = str_replace("<data_source_name>", $graph_parameters["data_source_name"], $graph_parameters["unparsed_graph_title"]);
		$graph_parameters["unparsed_graph_title"] = str_replace("<snmp_description>", $data[Description], $graph_parameters["unparsed_graph_title"]);
		$graph_parameters["unparsed_graph_title"] = str_replace("<snmp_interface_number>", $data[InterfaceNumber], $graph_parameters["unparsed_graph_title"]);
		$graph_parameters["unparsed_graph_title"] = str_replace("<snmp_interface_speed>", $data[Speed], $graph_parameters["unparsed_graph_title"]);
		$graph_parameters["unparsed_graph_title"] = str_replace("<snmp_hardware_address>", $data[HardwareAddress], $graph_parameters["unparsed_graph_title"]);
		$graph_parameters["unparsed_graph_title"] = str_replace("<snmp_ip_address>", $data[IPAddress], $graph_parameters["unparsed_graph_title"]);
    }
    
    return $graph_parameters["unparsed_graph_title"];
}

?>
