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
 | This program is distributed in the hope that it will be useful,         |
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

/* title_trim - takes a string of text, truncates it to $max_length and appends
     three periods onto the end
   @arg $text - the string to evaluate
   @arg $max_length - the maximum number of characters the string can contain
     before it is truncated
   @returns - the truncated string if len($text) is greater than $max_length, else
     the original string */
function title_trim($text, $max_length) {
	if (strlen($text) > $max_length) {
		return substr($text, 0, $max_length) . "...";
	}else{
		return $text;
	}
}

/* read_default_graph_config_option - finds the default value of a graph configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/config_settings.php'
   @returns - the default value of the configuration option */
function read_default_graph_config_option($config_name) {
	global $config;

	include($config["include_path"] . "/config_settings.php");

	while (list($tab_name, $tab_array) = each($settings_graphs)) {
		if ((isset($tab_array[$config_name])) && (isset($tab_array[$config_name]["default"]))) {
			return $tab_array[$config_name]["default"];
		}else{
			while (list($field_name, $field_array) = each($tab_array)) {
				if ((isset($field_array["items"])) && (isset($field_array["items"][$config_name])) && (isset($field_array["items"][$config_name]["default"]))) {
					return $field_array["items"][$config_name]["default"];
				}
			}
		}
	}
}

/* read_graph_config_option - finds the current value of a graph configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings_graphs array
     in 'include/config_settings.php'
   @returns - the current value of the graph configuration option */
function read_graph_config_option($config_name) {
	/* users must have cacti user auth turned on to use this */
	if ((read_config_option("auth_method") == "0") || (!isset($_SESSION["sess_user_id"]))) {
		return read_default_graph_config_option($config_name);
	}

	if (isset($_SESSION["sess_graph_config_array"])) {
		$graph_config_array = $_SESSION["sess_graph_config_array"];
	}

	if (!isset($graph_config_array[$config_name])) {
		$db_setting = db_fetch_row("select value from settings_graphs where name='$config_name' and user_id=" . $_SESSION["sess_user_id"]);

		if (isset($db_setting["value"])) {
			$graph_config_array[$config_name] = $db_setting["value"];
		}else{
			$graph_config_array[$config_name] = read_default_graph_config_option($config_name);
		}

		$_SESSION["sess_graph_config_array"] = $graph_config_array;
	}

	return $graph_config_array[$config_name];
}

/* config_value_exists - determines if a value exists for the current user/setting specified
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/config_settings.php'
   @returns (bool) - true if a value exists, false if a value does not exist */
function config_value_exists($config_name) {
	return sizeof(db_fetch_assoc("select value from settings where name='$config_name'"));
}

/* graph_config_value_exists - determines if a value exists for the current user/setting specified
   @arg $config_name - the name of the configuration setting as specified $settings_graphs array
     in 'include/config_settings.php'
   @arg $user_id - the id of the user to check the configuration value for
   @returns (bool) - true if a value exists, false if a value does not exist */
function graph_config_value_exists($config_name, $user_id) {
	return sizeof(db_fetch_assoc("select value from settings_graphs where name='$config_name' and user_id='$user_id'"));
}

/* read_default_config_option - finds the default value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/config_settings.php'
   @returns - the default value of the configuration option */
function read_default_config_option($config_name) {
	global $config;

	include($config["include_path"] . "/config_settings.php");

	while (list($tab_name, $tab_array) = each($settings)) {
		if ((isset($tab_array[$config_name])) && (isset($tab_array[$config_name]["default"]))) {
			return $tab_array[$config_name]["default"];
		}else{
			while (list($field_name, $field_array) = each($tab_array)) {
				if ((isset($field_array["items"])) && (isset($field_array["items"][$config_name])) && (isset($field_array["items"][$config_name]["default"]))) {
					return $field_array["items"][$config_name]["default"];
				}
			}
		}
	}
}

/* read_config_option - finds the current value of a Cacti configuration setting
   @arg $config_name - the name of the configuration setting as specified $settings array
     in 'include/config_settings.php'
   @returns - the current value of the configuration option */
function read_config_option($config_name) {
	if (isset($_SESSION["sess_config_array"])) {
		$config_array = $_SESSION["sess_config_array"];
	}

	if (!isset($config_array[$config_name])) {
		$db_setting = db_fetch_row("select value from settings where name='$config_name'");

		if (isset($db_setting["value"])) {
			$config_array[$config_name] = $db_setting["value"];
		}else{
			$config_array[$config_name] = read_default_config_option($config_name);
		}

		$_SESSION["sess_config_array"] = $config_array;
	}

	return $config_array[$config_name];
}

/* form_input_validate - validates the value of a form field and takes the appropriate action if the input
     is not valid
   @arg $field_value - the value of the form field
   @arg $field_name - the name of the $_POST field as specified in the HTML
   @arg $regexp_match - (optionally) enter a regular expression to match the value against
   @arg $allow_nulls - (bool) whether to allow an empty string as a value or not
   @arg $custom_message - (int) the ID of the message to raise upon an error which is defined in the
     $messages array in 'include/config_arrays.php'
   @returns - the original $field_value */
function form_input_validate($field_value, $field_name, $regexp_match, $allow_nulls, $custom_message = 3) {
	/* write current values to the "field_values" array so we can retain them */
	$_SESSION["sess_field_values"][$field_name] = $field_value;

	if (($allow_nulls == true) && ($field_value == "")) {
		return $field_value;
	}

	/* php 4.2+ complains about empty regexps */
	if (empty($regexp_match)) { $regexp_match = ".*"; }

	if ((!ereg($regexp_match, $field_value) || (($allow_nulls == false) && ($field_value == "")))) {
		raise_message($custom_message);

		$_SESSION["sess_error_fields"][$field_name] = $field_name;
	}else{
		$_SESSION["sess_field_values"][$field_name] = $field_value;
	}

	return $field_value;
}

/* is_error_message - finds whether an error message has been raised and has not been outputted to the
     user
   @returns - (bool) whether the messages array contains an error or not */
function is_error_message() {
	global $config;

	include($config["include_path"] . "/config_arrays.php");

	if (isset($_SESSION["sess_messages"])) {
		if (is_array($_SESSION["sess_messages"])) {
			foreach (array_keys($_SESSION["sess_messages"]) as $current_message_id) {
				if ($messages[$current_message_id]["type"] == "error") { return true; }
			}
		}
	}

	return false;
}

/* raise_message - mark a message to be displayed to the user once display_output_messages() is called
   @arg $message_id - the ID of the message to raise as defined in $messages in 'include/config_arrays.php' */
function raise_message($message_id) {
	$_SESSION["sess_messages"][$message_id] = $message_id;
}

/* display_output_messages - displays all of the cached messages from the raise_message() function and clears
     the message cache */
function display_output_messages() {
	global $config, $messages, $colors;

	if (isset($_SESSION["sess_messages"])) {
		$error_message = is_error_message();

		if (is_array($_SESSION["sess_messages"])) {
			foreach (array_keys($_SESSION["sess_messages"]) as $current_message_id) {
				eval ('$message = "' . $messages[$current_message_id]["message"] . '";');

				switch ($messages[$current_message_id]["type"]) {
				case 'info':
					if ($error_message == false) {
						print "<table align='center' width='98%' style='background-color: #" . $colors['messagebar_background'] . "; border: 1px solid #" . $colors['messagebar_border'] . ";'>";
						print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textInfo'>$message</p></td></tr>";
						print "</table><br>";

						/* we don't need these if there are no error messages */
						kill_session_var("sess_field_values");
					}
					break;
				case 'error':
					print "<table align='center' width='98%' style='background-color: #" . $colors['messagebar_background'] . "; border: 1px solid #ff0000;'>";
					print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textError'>Error: $message</p></td></tr>";
					print "</table><br>";
					break;
				}
			}
		}
	}

	kill_session_var("sess_messages");
}

/* display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @arg $text - the actual text of the error message to display */
function display_custom_error_message($message) {
	global $colors;

	print "<table align='center' width='98%' style='background-color: #ffffff; border: 1px solid #ff0000;'>";
	print "<tr><td bgcolor='#" . $colors["messagebar_background"] . "'><p class='textError'>Error: $message</p></td></tr>";
	print "</table><br>";
}

/* clear_messages - clears the message cache */
function clear_messages() {
	kill_session_var("sess_messages");
}

/* kill_session_var - kills a session variable using two methods -- session_unregister() and unset() */
function kill_session_var($var_name) {
	/* register_global = off: reset local settings cache so the user sees the new settings */
	session_unregister($var_name);

	/* register_global = on: reset local settings cache so the user sees the new settings */
	unset($_SESSION[$var_name]);
}

/* array_rekey - changes an array in the form:
     '$arr[0] = array("id" => 23, "name" => "blah")'
     to the form
     '$arr = array(23 => "blah")'
   @arg $array - (array) the original array to manipulate
   @arg $key - the name of the key
   @arg $key_value - the name of the key value
   @returns - the modified array */
function array_rekey($array, $key, $key_value) {
	$ret_array = array();

	if (sizeof($array) > 0) {
	foreach ($array as $item) {
		$item_key = $item[$key];

		if (is_array($key_value)) {
			for ($i=0; $i<count($key_value); $i++) {
				$ret_array[$item_key]{$key_value[$i]} = $item{$key_value[$i]};
			}
		}else{
			$ret_array[$item_key] = $item[$key_value];
		}
	}
	}

	return $ret_array;
}

/* cacti_log - logs a string to Cacti's log file or optionally to the browser
   @arg $string - the string to append to the log file
   @arg $output - (bool) whether to output the log line to the browser using pring() or not */
function cacti_log($string, $output = false, $environ = "CMDPHP") {
	global $config;

	/* fill in the current date for printing in the log */
	$date = date("m/d/Y h:i:s A");

	/* determine how to log data */
	$logdestination = read_config_option("log_destination");
	$logfile        = read_config_option("path_cactilog");

	/* format the message */
	if ($environ != "SYSTEM") {
		$message = "$date - " . $environ . ": " . $string . "\n";
	}else {
		$message = "$date - " . $environ . " " . $string . "\n";
	}

	/* Log to Logfile */
	if ((($logdestination == 1) || ($logdestination == 2)) && (read_config_option("log_verbosity") != POLLER_VERBOSITY_NONE)) {
		if ($logfile == "") {
			$logfile = $config["base_path"] . "/log/cacti.log";
		}

		/* echo the data to the log (append) */
		$fp = @fopen($logfile, "a");

		if ($fp) {
			@fwrite($fp, $message);
			fclose($fp);
		}
	}

	/* Log to Syslog/Eventlog */
	/* Syslog is currently Unstable in Win32 */
	if (($logdestination == 2) || ($logdestination == 3)) {
		$log_type = "";
		if (substr_count($string,"ERROR:"))
			$log_type = "err";
		else if (substr_count($string,"WARNING:"))
			$log_type = "warn";
		else if (substr_count($string,"STATS:"))
			$log_type = "stat";
		else if (substr_count($string,"NOTICE:"))
			$log_type = "note";

		if (strlen($log_type)) {
			define_syslog_variables();

			if ($config["cacti_server_os"] == "win32")
				openlog("Cacti Logging", LOG_NDELAY | LOG_PID, LOG_USER);
			else
				openlog("Cacti Logging", LOG_NDELAY | LOG_PID, LOG_SYSLOG);

			if (($log_type == "err") && (read_config_option("log_perror"))) {
				syslog(LOG_CRIT, $message);
			}

			if (($log_type == "warn") && (read_config_option("log_pwarn"))) {
				syslog(LOG_WARNING, $message);
			}

			if ((($log_type == "stat") || ($log_type == "note")) && (read_config_option("log_pstat"))) {
				syslog(LOG_INFO, $message);
			}

			closelog();
		}
	}

	/* print output to standard out if required */
	if ($output == true) {
		print $message;
	}
}

/* strip_newlines - removes \n\r from lines
   @arg $string - the string to strip */
function strip_newlines($string) {
	return strtr(strtr($string, "\n", "\0"), "\r","\0");
}

/* strip_quotes - Strip single and double quotes from a string
   @arg $result - (string) the result from the poll
   @returns - (string) the string with quotes stripped */
function strip_quotes($result) {
	/* first strip all single and double quotes from the string */
	$result = strtr($result,"'","");
	$result = strtr($result,'"','');

	return($result);
}

/* get_full_script_path - gets the full path to the script to execute to obtain data for a
     given data source. this function does not work on SNMP actions, only script-based actions
   @arg $local_data_id - (int) the ID of the data source
   @returns - the full script path or (bool) false for an error */
function get_full_script_path($local_data_id) {
	global $config;

	$data_source = db_fetch_row("select
		data_template_data.id,
		data_template_data.data_input_id,
		data_input.type_id,
		data_input.input_string
		from data_template_data,data_input
		where data_template_data.data_input_id=data_input.id
		and data_template_data.local_data_id=$local_data_id");

	/* snmp-actions don't have paths */
	if (($data_source["type_id"] == DATA_INPUT_TYPE_SNMP) || ($data_source["type_id"] == DATA_INPUT_TYPE_SNMP_QUERY)) {
		return false;
	}

	$data = db_fetch_assoc("select
		data_input_fields.data_name,
		data_input_data.value
		from data_input_fields
		left join data_input_data
		on data_input_fields.id=data_input_data.data_input_field_id
		where data_input_fields.data_input_id=" . $data_source["data_input_id"] . "
		and data_input_data.data_template_data_id=" . $data_source["id"] . "
		and data_input_fields.input_output='in'");

	$full_path = $data_source["input_string"];

	if (sizeof($data) > 0) {
	foreach ($data as $item) {
		$full_path = str_replace("<" . $item["data_name"] . ">", $item["value"], $full_path);
	}
	}

	$full_path = str_replace("<path_cacti>", $config["base_path"], $full_path);
	$full_path = str_replace("<path_snmpget>", read_config_option("path_snmpget"), $full_path);
	$full_path = str_replace("<path_php_binary>", read_config_option("path_php_binary"), $full_path);

	/* sometimes a certain input value will not have anything entered... null out these fields
	in the input string so we don't mess up the script */
	$full_path = preg_replace("/(<[A-Za-z0-9_]+>)+/", "", $full_path);

	return $full_path;
}

/* get_data_source_item_name - gets the name of a data source item or generates a new one if one does not
     already exist
   @arg $data_template_rrd_id - (int) the ID of the data source item
   @returns - the name of the data source item or an empty string for an error */
function get_data_source_item_name($data_template_rrd_id) {
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

/* get_data_source_path - gets the full path to the .rrd file associated with a given data source
   @arg $local_data_id - (int) the ID of the data source
   @arg $expand_paths - (bool) whether to expand the <path_rra> variable into its full path or not
   @returns - the full path to the data source or an empty string for an error */
function get_data_source_path($local_data_id, $expand_paths) {
	global $config;

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
			$data_source_path = str_replace("<path_rra>", $config["base_path"] . "/rra", $data_source_path);
		}

		return $data_source_path;
	}
}

/* stri_replace - a case insensitive string replace
   @arg $find - needle
   @arg $replace - replace needle with this
   @arg $string - haystack
   @returns - the original string with '$find' replaced by '$replace' */
function stri_replace($find, $replace, $string) {
	$parts = explode(strtolower($find), strtolower($string));

	$pos = 0;

	foreach ($parts as $key=>$part) {
		$parts[$key] = substr($string, $pos, strlen($part));
		$pos += strlen($part) + strlen($find);
	}

	return (join($replace, $parts));
}

/* clean_up_name - runs a string through a series of regular expressions designed to
     eliminate "bad" characters
   @arg $string - the string to modify/clean
   @returns - the modified string */
function clean_up_name($string) {
	$string = preg_replace("/[\s\.]+/", "_", $string);
	$string = preg_replace("/[^a-zA-Z0-9_]+/", "", $string);
	$string = preg_replace("/_{2,}/", "_", $string);

	return $string;
}

/* clean_up_path - takes any path and makes sure it contains the correct directory
     separators based on the current operating system
   @arg $path - the path to modify
   @returns - the modified path */
function clean_up_path($path) {
	global $config;

	if ($config["cacti_server_os"] == "unix" or read_config_option("using_cygwin") == "on") {
		$path = str_replace("\\", "/", $path);
	}elseif ($config["cacti_server_os"] == "win32") {
		$path = str_replace("/", "\\", $path);

	}

	return $path;
}

/* get_data_source_title - returns the title of a data source without using the title cache
   @arg $local_data_id - (int) the ID of the data source to get a title for
   @returns - the data source title */
function get_data_source_title($local_data_id) {
	$data = db_fetch_row("select
		data_local.host_id,
		data_local.snmp_query_id,
		data_local.snmp_index,
		data_template_data.name
		from data_template_data,data_local
		where data_template_data.local_data_id=data_local.id
		and data_local.id=$local_data_id");

	if ((strstr($data["name"], "|")) && (!empty($data["host_id"]))) {
		return expand_title($data["host_id"], $data["snmp_query_id"], $data["snmp_index"], $data["name"]);
	}else{
		return $data["name"];
	}
}

/* get_graph_title - returns the title of a graph without using the title cache
   @arg $local_graph_id - (int) the ID of the graph to get a title for
   @returns - the graph title */
function get_graph_title($local_graph_id) {
	$graph = db_fetch_row("select
		graph_local.host_id,
		graph_local.snmp_query_id,
		graph_local.snmp_index,
		graph_templates_graph.title
		from graph_templates_graph,graph_local
		where graph_templates_graph.local_graph_id=graph_local.id
		and graph_local.id=$local_graph_id");

	if ((strstr($graph["title"], "|")) && (!empty($graph["host_id"]))) {
		return expand_title($graph["host_id"], $graph["snmp_query_id"], $graph["snmp_index"], $graph["title"]);
	}else{
		return $graph["title"];
	}
}

/* generate_data_source_path - creates a new data source path from scratch using the first data source
     item name and updates the database with the new value
   @arg $local_data_id - (int) the ID of the data source to generate a new path for
   @returns - the new generated path */
function generate_data_source_path($local_data_id) {
	$host_part = ""; $ds_part = "";

	/* try any prepend the name with the host description */
	$host_name = db_fetch_cell("select host.description from host,data_local where data_local.host_id=host.id and data_local.id=$local_data_id");

	if (!empty($host_name)) {
		$host_part = strtolower(clean_up_name($host_name)) . "_";
	}

	/* then try and use the internal DS name to identify it */
	$data_source_rrd_name = db_fetch_cell("select data_source_name from data_template_rrd where local_data_id=$local_data_id order by id");

	if (!empty($data_source_rrd_name)) {
		$ds_part = strtolower(clean_up_name($data_source_rrd_name));
	}else{
		$ds_part = "ds";
	}

	/* put it all together using the local_data_id at the end */
	$new_path = "<path_rra>/$host_part$ds_part" . "_" . "$local_data_id.rrd";

	/* update our changes to the db */
	db_execute("update data_template_data set data_source_path='$new_path' where local_data_id=$local_data_id");

	return $new_path;
}

/* generate_graph_def_name - takes a number and turns each digit into its letter-based
     counterpart for RRDTool DEF names (ex 1 -> a, 2 -> b, etc)
   @arg $graph_item_id - (int) the ID to generate a letter-based representation of
   @returns - a letter-based representation of the input argument */
function generate_graph_def_name($graph_item_id) {
	$lookup_table = array("a","b","c","d","e","f","g","h","i","j");

	$result = "";

	for ($i=0; $i<strlen(strval($graph_item_id)); $i++) {
		$result .= $lookup_table{substr(strval($graph_item_id), $i, 1)};
	}

	return $result;
}

/* move_graph_group - takes a graph group (parent+children) and swaps it with another graph
     group
   @arg $graph_template_item_id - (int) the ID of the (parent) graph item that was clicked
   @arg $graph_group_array - (array) an array containing the graph group to be moved
   @arg $target_id - (int) the ID of the (parent) graph item of the target group
   @arg $direction - ('next' or 'previous') whether the graph group is to be swapped with
      group above or below the current group */
function move_graph_group($graph_template_item_id, $graph_group_array, $target_id, $direction) {
	$graph_item = db_fetch_row("select local_graph_id,graph_template_id from graph_templates_item where id=$graph_template_item_id");

	if (empty($graph_item["local_graph_id"])) {
		$sql_where = "graph_template_id = " . $graph_item["graph_template_id"] . " and local_graph_id=0";
	}else{
		$sql_where = "local_graph_id = " . $graph_item["local_graph_id"];
	}

	$graph_items = db_fetch_assoc("select id,sequence from graph_templates_item where $sql_where order by sequence");

	/* get a list of parent+children of our target group */
	$target_graph_group_array = get_graph_group($target_id);

	/* if this "parent" item has no children, then treat it like a regular gprint */
	if (sizeof($target_graph_group_array) == 0) {
		if ($direction == "next") {
			move_item_down("graph_templates_item", $graph_template_item_id, $sql_where);
		}elseif ($direction == "previous") {
			move_item_up("graph_templates_item", $graph_template_item_id, $sql_where);
		}

		return;
	}

	/* start the sequence at '1' */
	$sequence_counter = 1;

	if (sizeof($graph_items) > 0) {
	foreach ($graph_items as $item) {
		/* check to see if we are at the "target" spot in the loop; if we are, update the sequences and move on */
		if ($target_id == $item["id"]) {
			if ($direction == "next") {
				$group_array1 = $target_graph_group_array;
				$group_array2 = $graph_group_array;
			}elseif ($direction == "previous") {
				$group_array1 = $graph_group_array;
				$group_array2 = $target_graph_group_array;
			}

			while (list($sequence,$graph_template_item_id) = each($group_array1)) {
				db_execute("update graph_templates_item set sequence=$sequence_counter where id=$graph_template_item_id");

				/* propagate to ALL graphs using this template */
				if (empty($graph_item["local_graph_id"])) {
					db_execute("update graph_templates_item set sequence=$sequence_counter where local_graph_template_item_id=$graph_template_item_id");
				}

				$sequence_counter++;
			}

			while (list($sequence,$graph_template_item_id) = each($group_array2)) {
				db_execute("update graph_templates_item set sequence=$sequence_counter where id=$graph_template_item_id");

				/* propagate to ALL graphs using this template */
				if (empty($graph_item["local_graph_id"])) {
					db_execute("update graph_templates_item set sequence=$sequence_counter where local_graph_template_item_id=$graph_template_item_id");
				}

				$sequence_counter++;
			}
		}

		/* make sure to "ignore" the items that we handled above */
		if ((!isset($graph_group_array{$item["id"]})) && (!isset($target_graph_group_array{$item["id"]}))) {
			db_execute("update graph_templates_item set sequence=$sequence_counter where id=" . $item["id"]);
			$sequence_counter++;
		}
	}
	}
}

/* get_graph_group - returns an array containing each item in the graph group given a single
     graph item in that group
   @arg $graph_template_item_id - (int) the ID of the graph item to return the group of
   @returns - (array) an array containing each item in the graph group */
function get_graph_group($graph_template_item_id) {
	global $graph_item_types;

	$graph_item = db_fetch_row("select graph_type_id,sequence,local_graph_id,graph_template_id from graph_templates_item where id=$graph_template_item_id");

	if (empty($graph_item["local_graph_id"])) {
		$sql_where = "graph_template_id = " . $graph_item["graph_template_id"] . " and local_graph_id=0";
	}else{
		$sql_where = "local_graph_id = " . $graph_item["local_graph_id"];
	}

	/* a parent must NOT be the following graph item types */
	if (ereg("(GPRINT|VRULE|HRULE|COMMENT)", $graph_item_types{$graph_item["graph_type_id"]})) {
		return;
	}

	$graph_item_children_array = array();

	/* put the parent item in the array as well */
	$graph_item_children_array[$graph_template_item_id] = $graph_template_item_id;

	$graph_items = db_fetch_assoc("select id,graph_type_id from graph_templates_item where sequence > " . $graph_item["sequence"] . " and $sql_where order by sequence");

	if (sizeof($graph_items) > 0) {
	foreach ($graph_items as $item) {
		if ($graph_item_types{$item["graph_type_id"]} == "GPRINT") {
			/* a child must be a GPRINT */
			$graph_item_children_array{$item["id"]} = $item["id"];
		}else{
			/* if not a GPRINT then get out */
			return $graph_item_children_array;
		}
	}
	}

	return $graph_item_children_array;
}

/* get_graph_parent - returns the ID of the next or previous parent graph item id
   @arg $graph_template_item_id - (int) the ID of the current graph item
   @arg $direction - ('next' or 'previous') whether to find the next or previous parent
   @returns - (int) the ID of the next or previous parent graph item id */
function get_graph_parent($graph_template_item_id, $direction) {
	$graph_item = db_fetch_row("select sequence,local_graph_id,graph_template_id from graph_templates_item where id=$graph_template_item_id");

	if (empty($graph_item["local_graph_id"])) {
		$sql_where = "graph_template_id = " . $graph_item["graph_template_id"] . " and local_graph_id=0";
	}else{
		$sql_where = "local_graph_id = " . $graph_item["local_graph_id"];
	}

	if ($direction == "next") {
		$sql_operator = ">";
		$sql_order = "ASC";
	}elseif ($direction == "previous") {
		$sql_operator = "<";
		$sql_order = "DESC";
	}

	$next_parent_id = db_fetch_cell("select id from graph_templates_item where sequence $sql_operator " . $graph_item["sequence"] . " and graph_type_id != 9 and $sql_where order by sequence $sql_order limit 1");

	if (empty($next_parent_id)) {
		return 0;
	}else{
		return $next_parent_id;
	}
}

/* get_item - returns the ID of the next or previous item id
   @arg $tblname - the table name that contains the target id
   @arg $field - the field name that contains the target id
   @arg $startid - (int) the current id
   @arg $lmt_query - an SQL "where" clause to limit the query
   @arg $direction - ('next' or 'previous') whether to find the next or previous item id
   @returns - (int) the ID of the next or previous item id */
function get_item($tblname, $field, $startid, $lmt_query, $direction) {
	if ($direction == "next") {
		$sql_operator = ">";
		$sql_order = "ASC";
	}elseif ($direction == "previous") {
		$sql_operator = "<";
		$sql_order = "DESC";
	}

	$current_sequence = db_fetch_cell("select $field from $tblname where id=$startid");
	$new_item_id = db_fetch_cell("select id from $tblname where $field $sql_operator $current_sequence and $lmt_query order by $field $sql_order limit 1");

	if (empty($new_item_id)) {
		return $startid;
	}else{
		return $new_item_id;
	}
}

/* get_sequence - returns the next available sequence id
   @arg $id - (int) the current id
   @arg $field - the field name that contains the target id
   @arg $table_name - the table name that contains the target id
   @arg $group_query - an SQL "where" clause to limit the query
   @returns - (int) the next available sequence id */
function get_sequence($id, $field, $table_name, $group_query) {
	if (empty($id)) {
		$data = db_fetch_row("select max($field)+1 as seq from $table_name where $group_query");

		if ($data["seq"] == "") {
			return 1;
		}else{
			return $data["seq"];
		}
	}else{
		$data = db_fetch_row("select $field from $table_name where id=$id");
		return $data[$field];
	}
}

/* move_item_down - moves an item down by swapping it with the item below it
   @arg $table_name - the table name that contains the target id
   @arg $current_id - (int) the current id
   @arg $group_query - an SQL "where" clause to limit the query */
function move_item_down($table_name, $current_id, $group_query) {
	$next_item = get_item($table_name, "sequence", $current_id, $group_query, "next");

	$sequence = db_fetch_cell("select sequence from $table_name where id=$current_id");
	$sequence_next = db_fetch_cell("select sequence from $table_name where id=$next_item");
	db_execute("update $table_name set sequence=$sequence_next where id=$current_id");
	db_execute("update $table_name set sequence=$sequence where id=$next_item");
}

/* move_item_up - moves an item down by swapping it with the item above it
   @arg $table_name - the table name that contains the target id
   @arg $current_id - (int) the current id
   @arg $group_query - an SQL "where" clause to limit the query */
function move_item_up($table_name, $current_id, $group_query) {
	$last_item = get_item($table_name, "sequence", $current_id, $group_query, "previous");

	$sequence = db_fetch_cell("select sequence from $table_name where id=$current_id");
	$sequence_last = db_fetch_cell("select sequence from $table_name where id=$last_item");
	db_execute("update $table_name set sequence=$sequence_last where id=$current_id");
	db_execute("update $table_name set sequence=$sequence where id=$last_item");
}

/* exec_into_array - executes a command and puts each line of its output into
     an array
   @arg $command_line - the command to execute
   @returns - (array) an array containing the command output */
function exec_into_array($command_line) {
	exec($command_line,$out,$err);

	$command_array = array();

	for($i=0; list($key, $value) = each($out); $i++) {
		$command_array[$i] = $value;
	}

	return $command_array;
}

/* get_web_browser - determines the current web browser in use by the client
   @returns - ('ie' or 'moz' or 'other') */
function get_web_browser() {
	if (stristr($_SERVER["HTTP_USER_AGENT"], "Mozilla") && (!(stristr($_SERVER["HTTP_USER_AGENT"], "compatible")))) {
		return "moz";
	}elseif (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
		return "ie";
	}else{
		return "other";
	}
}

/* get_browser_query_string - returns the full url, including args requested by the browser
   @returns - the url requested by the browser */
function get_browser_query_string() {
	if (!empty($_SERVER["REQUEST_URI"])) {
		return basename($_SERVER["REQUEST_URI"]);
	}else{
		return basename($_SERVER["PHP_SELF"]) . (empty($_SERVER["QUERY_STRING"]) ? "" : "?" . $_SERVER["QUERY_STRING"]);
	}
}

/* get_graph_tree_array - returns a list of graph trees taking permissions into account if
     necessary
   @arg $return_sql - (bool) Whether to return the SQL to create the dropdown rather than an array
	@arg $force_refresh - (bool) Force the refresh of the array from the database
   @returns - (array) an array containing a list of graph trees */
function get_graph_tree_array($return_sql = false, $force_refresh = false) {

	$result = array();

	/* set the tree update time if not already set */
	if (!isset($_SESSION["tree_update_time"])) {
		$_SESSION["tree_update_time"] = time();
	}

	/* build tree array */
	if (!isset($_SESSION["tree_array"]) || ($force_refresh) ||
		(($_SESSION["tree_update_time"] + read_graph_config_option("page_refresh")) < time())) {

		if (read_config_option("auth_method") != "0") {
			$current_user = db_fetch_row("select policy_trees from user_auth where id=" . $_SESSION["sess_user_id"]);

			if ($current_user["policy_trees"] == "1") {
				$sql_where = "where user_auth_perms.user_id is null";
			}elseif ($current_user["policy_trees"] == "2") {
				$sql_where = "where user_auth_perms.user_id is not null";
			}

			$sql = "select
				graph_tree.id,
				graph_tree.name,
				user_auth_perms.user_id
				from graph_tree
				left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")
				$sql_where
				order by graph_tree.name";
		}else{
			$sql = "select * from graph_tree order by name";
		}

		$result = db_fetch_assoc($sql);
		$_SESSION["tree_array"] = $result;
		$_SESSION["tree_update_time"] = time();
	} else {
		$result = $_SESSION["tree_array"];
	}

	if ($return_sql == true) {
		return $sql;
	}else{
		return $result;
	}
}

/* get_host_array - returns a list of hosts taking permissions into account if necessary
   @returns - (array) an array containing a list of hosts */
function get_host_array() {
	if (read_config_option("auth_method") != "0") {
		$current_user = db_fetch_row("select policy_hosts from user_auth where id=" . $_SESSION["sess_user_id"]);

		if ($current_user["policy_hosts"] == "1") {
			$sql_where = "where user_auth_perms.user_id is null";
		}elseif ($current_user["policy_hosts"] == "2") {
			$sql_where = "where user_auth_perms.user_id is not null";
		}

		$host_list = db_fetch_assoc("select
			host.id,
			CONCAT_WS('',host.description,' (',host.hostname,')') as name,
			user_auth_perms.user_id
			from host
			left join user_auth_perms on (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")
			$sql_where
			order by host.description,host.hostname");
	}else{
		$host_list = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");
	}

	return $host_list;
}

/* get_associated_rras - returns a list of all RRAs referenced by a particular graph
   @arg $local_graph_id - (int) the ID of the graph to retrieve a list of RRAs for
   @returns - (array) an array containing the name and id of each RRA found */
function get_associated_rras($local_graph_id) {
	return db_fetch_assoc("select
		rra.id,
		rra.steps,
		rra.rows,
		rra.name,
		rra.timespan,
		data_template_data.rrd_step
		from graph_templates_item,data_template_data_rra,data_template_rrd,data_template_data,rra
		where graph_templates_item.task_item_id=data_template_rrd.id
		and data_template_rrd.local_data_id=data_template_data.local_data_id
		and data_template_data.id=data_template_data_rra.data_template_data_id
		and data_template_data_rra.rra_id=rra.id
		and graph_templates_item.local_graph_id=$local_graph_id
		group by rra.id
		order by rra.timespan");
}

/* get_hash_graph_template - returns the current unique hash for a graph template
   @arg $graph_template_id - (int) the ID of the graph template to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_graph_template($graph_template_id, $sub_type = "graph_template") {
	if ($sub_type == "graph_template") {
		$hash = db_fetch_cell("select hash from graph_templates where id=$graph_template_id");
	}elseif ($sub_type == "graph_template_item") {
		$hash = db_fetch_cell("select hash from graph_templates_item where id=$graph_template_id");
	}elseif ($sub_type == "graph_template_input") {
		$hash = db_fetch_cell("select hash from graph_template_input where id=$graph_template_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_template - returns the current unique hash for a data template
   @arg $graph_template_id - (int) the ID of the data template to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_template($data_template_id, $sub_type = "data_template") {
	if ($sub_type == "data_template") {
		$hash = db_fetch_cell("select hash from data_template where id=$data_template_id");
	}elseif ($sub_type == "data_template_item") {
		$hash = db_fetch_cell("select hash from data_template_rrd where id=$data_template_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_input - returns the current unique hash for a data input method
   @arg $graph_template_id - (int) the ID of the data input method to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_input($data_input_id, $sub_type = "data_input_method") {
	if ($sub_type == "data_input_method") {
		$hash = db_fetch_cell("select hash from data_input where id=$data_input_id");
	}elseif ($sub_type == "data_input_field") {
		$hash = db_fetch_cell("select hash from data_input_fields where id=$data_input_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_cdef - returns the current unique hash for a cdef
   @arg $graph_template_id - (int) the ID of the cdef to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_cdef($cdef_id, $sub_type = "cdef") {
	if ($sub_type == "cdef") {
		$hash = db_fetch_cell("select hash from cdef where id=$cdef_id");
	}elseif ($sub_type == "cdef_item") {
		$hash = db_fetch_cell("select hash from cdef_items where id=$cdef_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_gprint - returns the current unique hash for a gprint preset
   @arg $graph_template_id - (int) the ID of the gprint preset to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_gprint($gprint_id) {
	$hash = db_fetch_cell("select hash from graph_templates_gprint where id=$gprint_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_host_template - returns the current unique hash for a gprint preset
   @arg $host_template_id - (int) the ID of the host template to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_host_template($host_template_id) {
	$hash = db_fetch_cell("select hash from host_template where id=$host_template_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_data_query - returns the current unique hash for a data query
   @arg $graph_template_id - (int) the ID of the data query to return a hash for
   @arg $sub_type (optional) return the hash for a particlar sub-type of this type
   @returns - a 128-bit, hexadecimal hash */
function get_hash_data_query($data_query_id, $sub_type = "data_query") {
	if ($sub_type == "data_query") {
		$hash = db_fetch_cell("select hash from snmp_query where id=$data_query_id");
	}elseif ($sub_type == "data_query_graph") {
		$hash = db_fetch_cell("select hash from snmp_query_graph where id=$data_query_id");
	}elseif ($sub_type == "data_query_sv_data_source") {
		$hash = db_fetch_cell("select hash from snmp_query_graph_rrd_sv where id=$data_query_id");
	}elseif ($sub_type == "data_query_sv_graph") {
		$hash = db_fetch_cell("select hash from snmp_query_graph_sv where id=$data_query_id");
	}

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_round_robin_archive - returns the current unique hash for a round robin archive
   @arg $rra_id - (int) the ID of the round robin archive to return a hash for
   @returns - a 128-bit, hexadecimal hash */
function get_hash_round_robin_archive($rra_id) {
	$hash = db_fetch_cell("select hash from rra where id=$rra_id");

	if (ereg("[a-fA-F0-9]{32}", $hash)) {
		return $hash;
	}else{
		return generate_hash();
	}
}

/* get_hash_version - returns the item type and cacti version in a hash format
   @arg $type - the type of item to represent ('graph_template','data_template',
     'data_input_method','cdef','gprint_preset','data_query','host_template')
   @returns - a 24-bit hexadecimal hash (8-bits for type, 16-bits for version) */
function get_hash_version($type) {
	global $hash_type_codes, $hash_version_codes, $config;

	return $hash_type_codes[$type] . $hash_version_codes{$config["cacti_version"]};
}

/* generate_hash - generates a new unique hash
   @returns - a 128-bit, hexadecimal hash */
function generate_hash() {
	global $config;

	return md5(session_id() . microtime() . rand(0,1000));
}

/* debug_log_insert - inserts a line of text into the debug log
   @arg $type - the 'category' or type of debug message
   @arg $text - the actual debug message */
function debug_log_insert($type, $text) {
	if (!isset($_SESSION["debug_log"][$type])) {
		$_SESSION["debug_log"][$type] = array();
	}

	array_push($_SESSION["debug_log"][$type], $text);
}

/* debug_log_clear - clears the debug log for a particular category
   @arg $type - the 'category' to clear the debug log for. omitting this argument
     implies all categories */
function debug_log_clear($type = "") {
	if ($type == "") {
		kill_session_var("debug_log");
	}else{
		if (isset($_SESSION["debug_log"])) {
			unset($_SESSION["debug_log"][$type]);
		}
	}
}

/* debug_log_return - returns the debug log for a particular category
   @arg $type - the 'category' to return the debug log for.
   @returns - the full debug log for a particular category */
function debug_log_return($type) {
	$log_text = "";

	if (isset($_SESSION["debug_log"][$type])) {
		for ($i=0; $i<count($_SESSION["debug_log"][$type]); $i++) {
			$log_text .= "+ " . $_SESSION["debug_log"][$type][$i] . "<br>";
		}
	}

	return $log_text;
}

/* is_graph_item_type_primary - determines if a given graph item type id is primary,
     that is an AREA, STACK, LINE1, LINE2, or LINE3
   @arg $graph_item_type_id - the graph item type id to evaluate
   @returns - (bool) true if the item is primary, false otherwise  */
function is_graph_item_type_primary($graph_item_type_id) {
	if (($graph_item_type_id == GRAPH_ITEM_TYPE_LINE1) || ($graph_item_type_id == GRAPH_ITEM_TYPE_LINE2) || ($graph_item_type_id == GRAPH_ITEM_TYPE_LINE3) || ($graph_item_type_id == GRAPH_ITEM_TYPE_AREA) || ($graph_item_type_id == GRAPH_ITEM_TYPE_STACK)) {
		return true;
	}else{
		return false;
	}

}

?>
