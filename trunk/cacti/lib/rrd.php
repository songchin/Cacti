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

define("RRD_NL", " \\\n");

function bufprint($text) {
	$_SESSION["sess_debug_buffer"] .= $text;
}

function rrdtool_execute($command_line, $log_command, $output_flag) {
	include ('include/config.php');
	include_once ('include/functions.php');
	
	if ($log_command == true) {
		LogData("CMD: " . read_config_option("path_rrdtool") . " $command_line");
	}
	
	if ($output_flag == "") { $output_flag = "1"; }
	
	/* WIN32: before sending this command off to rrdtool, get rid
	of all of the '\' characters. Unix does not care; win32 does. 
	Also make sure to replace all of the fancy \'s at the end of the line,
	but make sure not to get rid of the "\n"'s that are supposed to be
	in there (text format) */
	$command_line = str_replace("\\\n", " ", $command_line);
	
	/* if we want to see the error output from rrdtool; make sure to specify this */
	if ($output_flag == "2") {
		$command_line .= " 2>&1";
	}
	
	/* use popen to eliminate the zombie issue */
	if ($config["cacti_server_os"] == "unix") {
		$fp = popen(read_config_option("path_rrdtool") . " $command_line", "r");
	}elseif ($config["cacti_server_os"] == "win32") {
		$fp = popen(read_config_option("path_rrdtool") . " $command_line", "rb");
	}
	
	/* Return Flag:
	0: Null
	1: Pass output back
	2: Pass error output back */
	
	switch ($output_flag) {
		case '0':
			return; break;
		case '1':
			return fpassthru($fp); break;
		case '2':
			$output = fgets($fp, 1000000);
			
			if (substr($output, 0, 4) == "�PNG") {
				return "OK";
			}
			
			if (substr($output, 0, 5) == "GIF87") {
				return "OK";
			}
			
			print $output;
			break;
	}
}

function rrdtool_function_create($local_data_id, $show_source) {
	include_once ("functions.php");
	include ("config_arrays.php");
	
	$data_source_path = get_data_source_path($local_data_id, true);
	
	/* ok, if that passes lets check to make sure an rra does not already
	exist, the last thing we want to do is overright data! */
	if ($show_source != true) {
		if (file_exists($data_source_path) == true) {
			return -1;
		}
	}
	
	/* the first thing we must do is make sure there is at least one
	rra associated with this data source... * 
	UPDATE: As of version 0.6.6, we are splitting this up into two
	SQL strings because of the multiple DS per RRD support. This is 
	not a big deal however since this function gets called once per
	data source */
	
	$rras = db_fetch_assoc("select 
		data_template_data.rrd_step,
		rra.x_files_factor,
		rra.steps,
		rra.rows,
		rra_cf.consolidation_function_id,
		(rra.rows*rra.steps) as rra_order
		from data_template_data
		left join data_template_data_rra on data_template_data.id=data_template_data_rra.data_template_data_id
		left join rra on data_template_data_rra.rra_id=rra.id
		left join rra_cf on rra.id=rra_cf.rra_id
		where data_template_data.local_data_id=$local_data_id
		order by rra_cf.consolidation_function_id,rra_order");
	
	/* if we find that this DS has no RRA associated; get out */
	if (sizeof($rras) <= 0) {
		LogData("There are no RRA's assigned to local_data_id: $local_data_id!");
		return -1;
	}
	
	/* create the "--step" line */
	$create_ds .= RRD_NL . "--step ". $rras[0]["rrd_step"] . " " . RRD_NL;
	
	/* query the data sources to be used in this .rrd file */
	$data_sources = db_fetch_assoc("select
		data_template_rrd.rrd_heartbeat,
		data_template_rrd.rrd_minimum,
		data_template_rrd.rrd_maximum,
		data_template_rrd.data_source_type_id
		from data_template_rrd
		where data_template_rrd.local_data_id=$local_data_id");
	
	/* ONLY make a new DS entry if:
	- There is multiple data sources and this item is not the main one.
	- There is only one data source (then use it) */
	
	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		/* use the cacti ds name by default or the user defined one, if entered */
		$data_source_name = get_data_source_name($local_data_id);
	
		$create_ds .= "DS:$data_source_name:" . $data_source_types{$data_source["data_source_type_id"]} . ":" . $data_source["rrd_heartbeat"] . ":" . $data_source["rrd_minimum"] . ":" . $data_source["rrd_maximum"] . RRD_NL;
	}
	}
	
	/* loop through each available RRA for this DS */
	foreach ($rras as $rra) {
		$create_rra .= "RRA:" . $consolidation_functions{$rra["consolidation_function_id"]} . ":" . $rra["x_files_factor"] . ":" . $rra["steps"] . ":" . $rra["rows"] . RRD_NL;
	}
	
	if ($show_source == true) {
		return read_config_option("path_rrdtool") . " create" . RRD_NL . "$data_source_path$create_ds$create_rra";
	}else{
		if (read_config_option("log_create") == "on") { $log_data = true; }
		rrdtool_execute("create $data_source_path $create_ds$create_rra",$log_data,1);
	}
}

function rrdtool_function_update($local_data_id, $show_source) {
	include_once ('include/functions.php');
	
	$data_source_path = get_data_source_path($local_data_id, true);
	
	if ($multi_data_source == true) {
		/* multi DS: This string joins on the rrd_ds->src_fields to get the 
		field names */
		$data = db_fetch_assoc("select 
		d.DSName,
		a.Value
		from rrd_ds d left join src_fields f on d.subfieldid=f.id 
		left join src_data a on d.id=a.dsid
		where d.subdsid=$dsid
		and f.inputoutput=\"out\"
		and f.updaterra=\"on\"
		order by d.id");
	}else{
		/* single DS: This string joins on the src_data->src_fields table to get 
		the field name */
		$data = db_fetch_assoc("select 
		d.DSName,
		a.Value
		from rrd_ds d left join src_data a on d.id=a.dsid
		left join src_fields f on a.fieldid=f.id 
		where d.id=$dsid
		and f.inputoutput=\"out\"
		and f.updaterra=\"on\"
		order by d.id");
	}
    
    /* setup the counter */
    $rows = sizeof($data);
    
    /* set initial values for strings to be used in the loop */
    $update_string = "N";
    $template_string = "--template ";
    
    $i = 0;
    /* loop through each item in this data source and build the UPDATE string */
    if (sizeof($data) > 0) {
		foreach ($data as $item) {
		    ++$i;
		    if (trim($data[Value]) == "") {
				$data_value = "U"; /* rrdtool: unknown */
		    }else{
				$data_value = $data[Value];
		    }
		    
		    $update_string .= ":$data_value";
		    $template_string .= $data[DSName];
		    
		    /* do NOT put a colon after the last template item */
		    if ($i != sizeof($data)) { $template_string .= ":"; }
		}
    }
    
    $update_string = "update $data_source_path $template_string $update_string";
    
    if ($show_source == true) {
		return read_config_option("path_rrdtool") . " $update_string";
    }else{
		if (read_config_option("log_update") == "on") { $log_data = true; }
		rrdtool_execute($update_string,true,1);
    }
}

function rrdtool_function_tune($rrd_tune_array) {
	include_once ('functions.php');
	include ('config_arrays.php');
	
	$data_source_name = GetDataSourceName($rrd_tune_array["data_source_id"]);
	$data_source_type = $data_source_types{$rrd_tune_array["data-source-type"]};
	$data_source_path = GetDataSourcePath($rrd_tune_array["data_source_id"], true);
	
	if ($rrd_tune_array["heartbeat"] != "") {
		$rrd_tune .= " --heartbeat $data_source_name:" . $rrd_tune_array["heartbeat"];
	}
	
	if ($rrd_tune_array["minimum"] != "") {
		$rrd_tune .= " --minimum $data_source_name:" . $rrd_tune_array["minimum"];
	}
	
	if ($rrd_tune_array["maximum"] != "") {
		$rrd_tune .= " --maximum $data_source_name:" . $rrd_tune_array["maximum"];
	}
	
	if ($rrd_tune_array["data-source-type"] != "") {
		$rrd_tune .= " --data-source-type $data_source_name:" . $data_source_type;
	}
	
	if ($rrd_tune_array["data-source-rename"] != "") {
		$rrd_tune .= " --data-source-rename $data_source_name:" . $rrd_tune_array["data-source-rename"];
	}
	
	if ($rrd_tune != "") {
		if (file_exists($data_source_path) == true) {
			$fp = popen(read_config_option("path_rrdtool") . " tune $data_source_path $rrd_tune", "r");
			pclose($fp);
			
			LogData("CMD: " . read_config_option("path_rrdtool") . " tune $data_source_path $rrd_tune");
		}
	}
}

function rrdtool_function_graph($local_graph_id, $rra_id, $graph_data_array) {
	include_once ("functions.php");
	include ("config_arrays.php");
	
	/* before we do anything; make sure the user has permission to view this graph,
	if not then get out */
	if (read_config_option("global_auth") == "on") {
		$user_auth = db_fetch_row("select user_id from user_auth_graph where local_graph_id=$local_graph_id and user_id=" . $_SESSION["sess_user_id"]);
		
		if ($current_user["graph_policy"] == "1") {
			if (sizeof($user_auth) > 0) { $access_denied = true; }
		}elseif ($current_user["graph_policy"] == "2") {
			if (sizeof($user_auth) == 0) { $access_denied = true; }
		}
		
		if ($access_denied == true) {
			return "GRAPH ACCESS DENIED";
		}
	}
	
	//$graph = db_fetch_row("select g.*, i.Name from rrd_graph g left join def_image_type i 
	//	on g.imageformatid=i.id where g.id=$graphid");
	
	/* define the time span, which decides which rra to use */
	$rra = db_fetch_row("select rows,steps from rra where id=$rra_id");
	$timespan = -($rra["rows"] * $rra["steps"] * 144);
	
	/* this is so we do not show the data for MIN/MAX data on daily graphs (steps <= 1),
	this code is a little hacked at the moment as GPRINT's are not covered. some changes will have
	to be made for this to be included also */
	/*if ($rra["steps"] > 1) {
		$sql_order_by = "";
	}else{
		$sql_order_by = "and !(cf.id != 1 and (t.name='LINE1' or t.name='AREA' or t.name='STACK'
			or t.name='LINE2' or t.name='LINE3'))";
	}*/
    	
	$graph = db_fetch_row("select
		graph_templates_graph.title,
		graph_templates_graph.vertical_label,
		graph_templates_graph.auto_scale,
		graph_templates_graph.auto_scale_opts,
		graph_templates_graph.auto_scale_log,
		graph_templates_graph.auto_scale_rigid,
		graph_templates_graph.auto_padding,
		graph_templates_graph.base_value,
		graph_templates_graph.upper_limit,
		graph_templates_graph.lower_limit,
		graph_templates_graph.height,
		graph_templates_graph.width,
		graph_templates_graph.image_format_id,
		graph_templates_graph.unit_value,
		graph_templates_graph.unit_exponent_value,
		graph_templates_graph.export
		from graph_templates_graph
		where graph_templates_graph.local_graph_id=$local_graph_id");
	
    	/* lets make that sql query... */
    	$graph_items = db_fetch_assoc("select
		graph_templates_item.id as graph_templates_item_id,
		graph_templates_item.cdef_id,
		graph_templates_item.text_format,
		graph_templates_item.value,
		graph_templates_item.hard_return,
		graph_templates_item.consolidation_function_id,
		graph_templates_item.graph_type_id,
		graph_templates_gprint.gprint_text,
		colors.hex,
		data_template_rrd.id as data_template_rrd_id,
		data_template_rrd.local_data_id,
		data_template_rrd.data_source_name
		from graph_templates_item
		left join data_template_rrd on graph_templates_item.task_item_id=data_template_rrd.id
		left join colors on graph_templates_item.color_id=colors.id
		left join graph_templates_gprint on graph_templates_item.gprint_id=graph_templates_gprint.id
		where graph_templates_item.local_graph_id=$local_graph_id
		order by graph_templates_item.sequence");
	
  	/* +++++++++++++++++++++++ GRAPH OPTIONS +++++++++++++++++++++++ */
	
    	if ($graph["auto_scale"] == "on") {
		if ($graph["auto_scale_opts"] == "1") {
			$scale .= "--alt-autoscale" . RRD_NL;
		}elseif ($graph["auto_scale_opts"] == "2") {
			$scale .= "--alt-autoscale-max" . RRD_NL;
			$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL; 
		}
		
		if ($graph["auto_scale_log"] == "on") {
			$scale .= "--logarithmic" . RRD_NL;
		}
	}else{
		$scale =  "--upper-limit=" . $graph["upper_limit"] . RRD_NL;
		$scale .= "--lower-limit=" . $graph["lower_limit"] . RRD_NL;
	}
	
	if ($graph["rigid"] == "on") {
		$rigid = "--rigid" . RRD_NL;
	}
	
	if (!empty($graph["unit_value"])) {
		$unit_value = "--unit=" . $graph["unit_value"] . RRD_NL;
	}
	
	if (!empty($graph["unit_exponent_value"])) {
		$unit_exponent_value = "--units-exponent=" . $graph["unit_exponent_value"] . RRD_NL;
	}
	
	/* optionally you can specify and array that overrides some of the db's
	values, lets set that all up here */
	if ($graph_data_array["use"] == true) {
		if ($graph_data_array["graph_start"] == "0") {
			$graph_start = $timespan;
		}else{
			$graph_start = $graph_data_array["graph_start"];
		}
		
		$graph_height = $graph_data_array["graph_height"];
		$graph_width = $graph_data_array["graph_width"];
	}else{
		$graph_start = $timespan;
		$graph_height = $graph["height"];
		$graph_width = $graph["width"];
	}
	
	if ($graph_data_array["graph_nolegend"] == true) {
		$graph_legend = "--no-legend" . RRD_NL;
	}else{
		$graph_legend = "";
	}
    
	/* export options */
	if ($graph_data_array["export"] == true) {
		$graph_opts = read_config_option("path_html_export") . "/" . $graph_data_array["export_filename"] . RRD_NL;
	}else{
		if ($graph_data_array["output_filename"] == "") {
	    		$graph_opts = "-" . RRD_NL;
		}else{
			$graph_opts = $graph_data_array["output_filename"] . RRD_NL;
		}
	}
    
	/* basic graph options */
	$graph_opts .= 
		"--imgformat=" . $image_types{$graph["image_format_id"]} . RRD_NL . 
		"--start='$graph_start'" . RRD_NL .
		"--title='" . $graph["title"] . "'" . RRD_NL .
		"$rigid" .
		"--base=" . $graph["base_value"] . RRD_NL .
		"--height=$graph_height" . RRD_NL .
		"--width=$graph_width" . RRD_NL .
		"$scale" .
		"$graph_legend" .
		"--vertical-label='" . $graph["vertical_label"] . "'" . RRD_NL;
    
    	/* a note about different CF's on a graph. for now we are only going to display MAX/MIN CF
	data when viewing anything greater than daily graphs. From what I know, rrdtool does not
	store AVERAGE MAX/MIN data for any RRA with less than 1 step */
	/*if ($rra[teps] > 1) {
	$sql_group_by = "group by d.id,i.consolidationfunction";
	}else{
	$sql_group_by = "and c.name=\"AVERAGE\" group by d.id";
	}*/
    	
    	/* +++++++++++++++++++++++ GRAPH DEFS +++++++++++++++++++++++ */
    
    	/* define the datasources used; only once */
	/*$defs = db_fetch_assoc("select 
		i.id as IID, i.CDEFID, i.ConsolidationFunction,
		d.ID, d.Name, d.DSName, d.DSPath,
		c.Name as CName
		from rrd_graph_item i 
		left join rrd_ds d 
		on i.dsid=d.id 
		left join def_cf c 
		on i.consolidationfunction=c.id
		left join def_graph_type t 
		on i.graphtypeid=t.id
		where i.graphid=$graphid 
		and (t.name=\"AREA\" or t.name=\"STACK\" 
		or t.name=\"LINE1\" or t.name=\"LINE2\" 
		or t.name=\"LINE3\") 
		and d.dsname is not null
		$sql_group_by");
		$rows_defs = sizeof($defs);*/
    	
	$i = 0;
    	if (sizeof($graph_items > 0)) {
	foreach ($graph_items as $graph_item) {
		if (ereg("(AREA|STACK|LINE[123])", $graph_item_types{$graph_item["graph_type_id"]})) {
			if (empty($graph_item["data_source_name"])) {
				//$data_source_name = generate_data_source_name();
			}else{
				$data_source_name = $graph_item["data_source_name"];
			}
			
			/* use a user-specified ds path if one is entered */
			$data_source_path = get_data_source_path($graph_item["local_data_id"], true);
			
			/* FOR WIN32: Ecsape all colon for drive letters (ex. D\:/path/to/rra) */
			$data_source_path = str_replace(":", "\:", $data_source_path);
	
			/* NOTE: (Update) Data source DEF names are created using the graph_item_id; then passed
			to a function that matches the digits with letters. rrdtool likes letters instead
			of numbers in DEF names; especially with CDEF's. cdef's are created
			the same way, except a 'cdef' is put on the beginning of the hash */
			
			$graph_defs .= "DEF:" . generate_graph_def_name(("$i")) . "=\"$data_source_path\":$data_source_name:" . $consolidation_functions{$graph_item["consolidation_function_id"]} . RRD_NL;
			
			$cf_ds_cache{$graph_item["data_template_rrd_id"]}{$graph_item["consolidation_function_id"]} = "$i";
			//$graph_group_cache[$def[IID]] = "$i";
			
			$i++;
		}
	}
	}
    
	/* if we are not displaying a legend there is no point in us even processing the auto padding,
	text format stuff. */
	if ($graph_data_array["graph_nolegend"] != true) {
		/* use this loop to to setup all textformat data (hr, padding, subsitution, etc) */
		$greatest_text_format = 0;
		
		reset($graph_items);
		
		if (sizeof($graph_items) > 0) {
		foreach ($graph_items as $graph_item) {
			/* +++++++++++++++++++++++ LEGEND: TEXT SUBSITUTION (<>'s) +++++++++++++++++++++++ */
			
			/* format the textformat string, and add values where there are <>'s */
			$text_format[$i] = $graph_item["text_format"];
			$value_format[$i] = $graph_item["value"];
			
			/* set hard return variable if selected (\n) */
			if ($graph_item["hard_return"] == "on") { 
				$hardreturn[$i] = "\\n"; 
			}else{
				$hardreturn[$i] = "";
			}
			
			if ($graph_item["graph_templates_item_id"] != "") {
				/*$fields = db_fetch_assoc("select d.FieldID, d.DSID, d.Value, 
					f.SrcID, f.DataName
					from src_data d
					left join src_fields f
					on d.fieldid=f.id
					where d.dsid=$graph_item[graph_templates_item_id]");
			
				if (sizeof($fields) > 0) {
				foreach ($fields as $field) {
					$text_format[$i] = ereg_replace ("<$field[DataName]>", $field[Value],$text_format[$i]);
					$value_format[$i] = ereg_replace ("<$field[DataName]>", $field[Value],$value_format[$i]);
				}
				}*/
			}
	    		
			/* +++++++++++++++++++++++ LEGEND: AUTO PADDING (<>'s) +++++++++++++++++++++++ */
			
			/* PADDING: remember this is not perfect! its main use is for the basic graph setup of:
			AREA - GPRINT-CURRENT - GPRINT-AVERAGE - GPRINT-MAXIMUM \n
			of course it can be used in other situations, however may not work as intended.
			If you have any additions to this small peice of code, feel free to send them to me. */
			if ($graph["auto_padding"] == "on") {
				$graph_item_dsid = $graph_item[ID];
				
				/* only applies to AREA and STACK */
				if (ereg("(AREA|STACK|LINE[123])", $graph_item_types{$graph_item["graph_type_id"]})) {
					$text_format_lengths{$graph_item["data_template_rrd_id"]} = strlen($text_format[$i]);
					
					if ((strlen($text_format[$i]) > $greatest_text_format) && ($graph_item_types{$graph_item["graph_type_id"]} == "COMMENT")) {
						$greatest_text_format = strlen($text_format[$i]);
					}
				}
			}
	    		
	    		$i++;
		}
		}
    	}
	
    	
    	/* +++++++++++++++++++++++ GRAPH ITEMS: CDEF's +++++++++++++++++++++++ */
    	
	$i = 0;
	reset($graph_items);
		
	if (sizeof($graph_items) > 0) {
	foreach ($graph_items as $graph_item) {
		/* make cdef string here; a note about CDEF's in cacti. A CDEF is neither unique to a 
		data source of global cdef, but is unique when those two variables combine. */
		$cdef_graph_defs = "";
		
		if (($graph_item["cdef_id"] != "0") && (isset($cdef_cache[$graph_item["cdef_id"]][$graph_item["graph_templates_item_id"]]) == false)) {
		/* pull out what kind of cdef type this is */
		$cdef_type = db_fetch_cell("select Type from rrd_ds_cdef where id=" . $graph_item["cdef_id"]);
		
		/* get all of the items for this cdef */
		$cdef_items = db_fetch_assoc("select case
		when ci.type=\"CDEF Function\" then cf.name
		when ci.type=\"Data Source\" then ds.name
		when ci.type=\"Custom Entry\" then ci.custom
		end 'CDEF',
		ci.Type,ci.DSID,ci.CurrentDS
		from rrd_ds_cdef_item ci left join def_cdef cf on cf.id=ci.cdeffunctionid left 
		join rrd_ds ds on ds.id=ci.dsid
		where ci.cdefid=$item[CDEFID]
		order by ci.sequence");
		
		/* CF rules: if we are using a CF because it's defined in the AREA, STACK, LINE[1-3] then
		it is ok to use it elsewhere on the graph. But it is not ok to use a CF DEF because
		its used in a GPRINT; so check that here */
		if (isset($cf_ds_cache[$item[ID]][$item[ConsolidationFunction]]) == true) {
		$cf_id = $item[ConsolidationFunction];
		}else{
		$cf_id = 1; /* CF: AVERAGE */
		}
		
		/* make the initial "virtual" cdef name: 'cdef' + md5(dsid) */
		$cdef_graph_defs .= "CDEF:cdef" . generate_graph_def_name("$i") . "=";
		$i_cdef = 0;
		
		/* form the cdef string by looping through each item. Additionally MD5 hash each
		data source that we come across so this works right */
		switch ($cdef_type) {
		case "1": /* normal */
		if (sizeof($cdef_items) > 0) {
		foreach ($cdef_items as $cdef_item) {
		if ($cdef_item[Type] == "Data Source") {
		if ($cdef_item[CurrentDS] == "on") {
		$cdef_current_item = generate_graph_def_name($cf_ds_cache[$item[ID]][$cf_id]);
		}else{
		$cdef_current_item = generate_graph_def_name($cf_ds_cache[$cdef_item[DSID]][$cf_id]);
		}
		}else{
		$cdef_current_item = $cdef_item[CDEF];
		}
		
		if ($i_cdef == 0) { $delimeter = ""; }else{ $delimeter = ","; }
		$cdef_graph_defs .= "$delimeter$cdef_current_item";
		
		$i_cdef++;
		}
		}
		break;
		}
		
		$cdef_graph_defs .= " \\\n";
		
		/* the CDEF cache is so we do not create duplicate CDEF's on a graph */
		$cdef_cache[$item[CDEFID]][$item[ID]] = "$i";
		}
		
		/* add the cdef string to the end of the def string */
		$graph_defs .= $cdef_graph_defs;
		
		/* if we are not displaying a legend there is no point in us even processing the auto padding,
		text format stuff. */
		if (($graph_data_array["graph_nolegend"] != true) && ($graph["auto_padding"] == "on")) {
			/* we are basing how much to pad on area and stack text format, 
			not gprint. but of course the padding has to be displayed in gprint,
			how fun! */
			
			$pad_number = ($greatest_text_format - $text_format_lengths{$graph_item["data_template_rrd_id"]});
			//LogData("MAX: $greatest_text_format, CURR: $text_format_lengths[$item_dsid], DSID: $item_dsid");
			$text_padding = str_pad("", $pad_number);
			
			/* two GPRINT's in a row screws up the padding, lets not do that */
			if (($graph_item_types{$graph_item["graph_type_id"]} == "GPRINT") && ($last_graph_type == "GPRINT")) {
				$text_padding = "";
			}
			
			$last_graph_type = $graph_item_types{$graph_item["graph_type_id"]};
		}
		
	
		/* we put this in a variable so it can be manipulated before mainly used
		if we want to skip it, like below */
		$current_graph_item_type = $graph_item_types{$graph_item["graph_type_id"]};
		
		/* CF rules: if we are using a CF because it's defined in the AREA, STACK, LINE[1-3] then
		it is ok to use it elsewhere on the graph. But it is not ok to use a CF DEF because
		its used in a GPRINT; so check that here */
		if ((isset($cf_ds_cache{$graph_item["data_template_rrd_id"]}{$graph_item["consolidation_function_id"]})) && ($graph_item_types{$graph_item["graph_type_id"]} != "GPRINT")) {
			$cf_id = $graph_item["consolidation_function_id"];
		}else{
			$cf_id = 1; /* CF: AVERAGE */
		}
		
		/* make sure grouping is on, before we make decisions based on the group */
		//if ($graph[Grouping] == "on") {
		/* if this item belongs to a graph group that has a parent that does not exist, do
		not show the child item. this happens with MAX/MIN items on daily graphs mostly. */
		//if ((isset($graph_group_cache[$item[Parent]]) == false) && ($item[TName] == "GPRINT")) {
		//$current_graph_item_type = "SKIP";
		//}
		//}
		/* use cdef if one if specified */
		if ($graph_item["cdef_id"] == "0") {
			$data_source_name = generate_graph_def_name($cf_ds_cache{$graph_item["data_template_rrd_id"]}[$cf_id]);
		}else{
			$data_source_name = "cdef" . generate_graph_def_name($cdef_cache{$graph_item["cdef_id"]}{$graph_item["data_template_rrd_id"]});
		}
		
		/* +++++++++++++++++++++++ GRAPH ITEMS +++++++++++++++++++++++ */
		
		/* this switch statement is basically used to grab all of the graph data above and
		print it out in an rrdtool-friendly fashion, not too much calculation done here. */
		
		switch ($graph_item_types{$graph_item["graph_type_id"]}) {
		case 'AREA':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . 
			$data_source_name . "#" . 
			$graph_item["hex"] . ":" . 
			"\"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'STACK':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . 
			$data_source_name . "#" . 
			$graph_item["hex"] . ":" .
			"\"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'LINE1':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . 
			$data_source_name . "#" . 
			$graph_item["hex"] . ":" . 
			"\"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'LINE2':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . 
			$data_source_name . "#" . 
			$graph_item["hex"] . ":" . 
			"\"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'LINE3':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" . 
			$data_source_name . "#" . 
			$graph_item["hex"] . ":" . 
			"\"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'COMMENT':
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":\"" .
			"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'GPRINT':
			if ($graph_data_array["graph_nolegend"] != true) {
				$gprint_text = $graph_item["gprint_text"];
			
				$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
				$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" .
				$data_source_name . ":" . $consolidation_functions{$graph_item["consolidation_function_id"]} .
				":\"$text_padding$text_format[$i]$gprint_text$hardreturn[$i]\" ";
			}
			
			break;
		case 'HRULE':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			
			if ($graph_data_array["graph_nolegend"] == true) {
				$value_format[$i] = "0";
			}else{
				$value_format[$i] = ereg_replace (":", "\:" ,$value_format[$i]); /* escape colons */
			}
			
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" .
			$value_format[$i] . "#" . $graph_item["hex"] . ":\"" . 
			"$text_format[$i]$hardreturn[$i]\" ";
			break;
		case 'VRULE':
			$text_format[$i] = ereg_replace (":", "\:" ,$text_format[$i]); /* escape colons */
			
			$value_array = explode(":", $graph_item["value"]);
			$value = date("U", mktime($value_array[0],$value_array[1],0));
			
			$txt_graph_items .= $graph_item_types{$graph_item["graph_type_id"]} . ":" .
			$value . "#" . $graph_item["hex"] . ":\"" . 
			"$text_format[$i]$hardreturn[$i]\" ";
			break;
		}
		
		$i++;
		
		if ($i < sizeof($graph_items)) {
			$txt_graph_items .= RRD_NL;
		}
	}
	}
    
	/* either print out the source or pass the source onto rrdtool to get us a nice PNG */
	if ($graph_data_array["print_source"] == "true") {
		print "<PRE>" . read_config_option("path_rrdtool") . " graph $graph_opts$graph_defs$txt_graph_items</PRE>";
	}else{
		if ($graph_data_array["export"] == true) {
			rrdtool_execute("graph $graph_opts$graph_defs$graph_items", false, "0");
			return 0;
		}else{
			if (read_config_option("log_graph") == "on") { $log_data = true; }
			//if ($graph_data_array["output_flag"] == "") { $graph_data_array["output_flag"] = 1; }
			return rrdtool_execute("graph $graph_opts$graph_defs$graph_items",$log_data,$graph_data_array["output_flag"]);
		}
	}
}

?>
