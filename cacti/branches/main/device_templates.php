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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");

define("MAX_DISPLAY_PAGES", 21);

$host_actions = array(
	1 => __("Delete"),
	2 => __("Duplicate")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }


switch (get_request_var_request("action")) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item_remove_gt':
		template_item_remove_gt();

		header("Location: device_templates.php?action=edit&id=" . $_GET["host_template_id"]);
		break;
	case 'item_remove_dq':
		template_item_remove_dq();

		header("Location: device_templates.php?action=edit&id=" . $_GET["host_template_id"]);
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		template_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		template();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	/* required for "run_data_query" */
	include_once(CACTI_BASE_PATH . "/lib/data_query.php");

	/*
	 * loop for all possible changes of reindex_method
	 * post variable is build like this
	 * 		reindex_method_host_template_<host_id>_query_<snmp_query_id>_method_<old_reindex_method>
	 * if values of this variable differs from <old_reindex_method>, we will have to update
	 */
	$reindex_performed = false;
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^reindex_method_host_template_([0-9]+)_query_([0-9]+)_method_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("id"));
			input_validate_input_number($matches[1]); # host_template
			input_validate_input_number($matches[2]); # snmp_query_id
			input_validate_input_number($matches[3]); # old reindex method
			$reindex_method = $val;
			input_validate_input_number($reindex_method); # new reindex_method
			/* ==================================================== */

			# change reindex method of this very item
			if ( $reindex_method != $matches[3]) {
				db_execute("replace into host_template_snmp_query (host_template_id,snmp_query_id,reindex_method) values (" . $matches[1] . "," . $matches[2] . "," . $reindex_method . ")");
				$reindex_performed = true;
			}
		}
	}

	if (isset($_POST["save_component_template"])) {
		$redirect_back = false;

		$save["id"] 					= $_POST["id"];
		$save["hash"]					= get_hash_host_template($_POST["id"]);
		$save["name"]					= form_input_validate($_POST["name"], "name", "", false, 3);
		$save["description"]			= form_input_validate($_POST["description"], "description", "", true, 3);
		$save["image"]					= form_input_validate($_POST["image"], "image", "", true, 3);
		$save["override_defaults"]		= form_input_validate((isset($_POST["override_defaults"]) ? "on":""), "override_defaults", "", true, 3);
		$save["override_permitted"]		= form_input_validate((isset($_POST["override_permitted"]) ? "on":""), "override_permitted", "", true, 3);
		$save["snmp_version"]			= form_input_validate($_POST["snmp_version"], "snmp_version", "", true, 3);
		$save["snmp_version"]			= form_input_validate($_POST["snmp_version"], "snmp_version", "", true, 3);
		$save["snmp_community"]			= form_input_validate($_POST["snmp_community"], "snmp_community", "", true, 3);
		$save["snmp_username"]			= form_input_validate($_POST["snmp_username"], "snmp_username", "", true, 3);
		$save["snmp_password"]			= form_input_validate($_POST["snmp_password"], "snmp_password", "", true, 3);
		$save["snmp_auth_protocol"]		= form_input_validate($_POST["snmp_auth_protocol"], "snmp_auth_protocol", "", true, 3);
		$save["snmp_priv_passphrase"]	= form_input_validate($_POST["snmp_priv_passphrase"], "snmp_priv_passphrase", "", true, 3);
		$save["snmp_priv_protocol"]		= form_input_validate($_POST["snmp_priv_protocol"], "snmp_priv_protocol", "", true, 3);
		$save["snmp_context"]			= form_input_validate($_POST["snmp_context"], "snmp_context", "", true, 3);
		$save["snmp_port"]				= form_input_validate($_POST["snmp_port"], "snmp_port", "^[0-9]+$", false, 3);
		$save["snmp_timeout"]			= form_input_validate($_POST["snmp_timeout"], "snmp_timeout", "^[0-9]+$", false, 3);
		$save["availability_method"]	= form_input_validate($_POST["availability_method"], "availability_method", "^[0-9]+$", false, 3);
		$save["ping_method"]			= form_input_validate($_POST["ping_method"], "ping_method", "^[0-9]+$", false, 3);
		$save["ping_port"]				= form_input_validate($_POST["ping_port"], "ping_port", "^[0-9]+$", true, 3);
		$save["ping_timeout"]			= form_input_validate($_POST["ping_timeout"], "ping_timeout", "^[0-9]+$", true, 3);
		$save["ping_retries"]			= form_input_validate($_POST["ping_retries"], "ping_retries", "^[0-9]+$", true, 3);
		$save["max_oids"]				= form_input_validate($_POST["max_oids"], "max_oids", "^[0-9]+$", true, 3);

		if (!is_error_message()) {
			$device_template_id = sql_save($save, "host_template");

			if ($device_template_id) {
				raise_message(1);

				if (isset($_POST["add_gt_y"])) {
					/* ================= input validation ================= */
					input_validate_input_number(get_request_var_post("graph_template_id"));
					/* ==================================================== */
					db_execute("replace into host_template_graph (host_template_id,graph_template_id) values($device_template_id," . get_request_var_post("graph_template_id") . ")");
					/* associate this new Graph Template with all hosts that are using the current Device Template
					   but leave those hosts that have this template already */
					$new_gt_host_entries = db_fetch_assoc("
								SELECT 	host.id AS host_id,
										host.description AS description,
										host.hostname AS hostname
								FROM 	host,
									 	host_template_graph
								WHERE	host.host_template_id 					= host_template_graph.host_template_id
								AND		host_template_graph.graph_template_id 	= " . $_POST["graph_template_id"] . "
								AND		host.id NOT
								IN (
									SELECT host_graph.host_id
									FROM   host_graph
									WHERE  host_graph.graph_template_id = " . $_POST["graph_template_id"] . "
									)"
								);
					if (sizeof($new_gt_host_entries) > 0) {
						/* notify the user of changes to hosts */
						debug_log_clear("host_template");
						$template_name = db_fetch_cell("SELECT name FROM graph_templates WHERE id = " . $_POST["graph_template_id"]);
						debug_log_insert("host_template", __("Adding Graph Template: ") . $template_name . " to ");

						foreach($new_gt_host_entries as $entry) {
							/* add the Graph Template */
							db_execute("REPLACE INTO host_graph ( host_id, graph_template_id )
									VALUES (" . $entry["host_id"] . ","
											  . get_request_var_post("graph_template_id") . "
											)"
									);
							debug_log_insert("host_template", $entry["hostname"] . ", " . $entry["description"]);
						}
					}
					$redirect_back = true;
				}elseif (isset($_POST["add_dq_y"])) {
					/* ================= input validation ================= */
					input_validate_input_number(get_request_var_post("snmp_query_id"));
					input_validate_input_number(get_request_var_post("reindex_method"));
					/* ==================================================== */
					db_execute("replace into host_template_snmp_query (host_template_id,snmp_query_id, reindex_method) values($device_template_id," . get_request_var_post("snmp_query_id") . ", " . get_request_var_post("reindex_method") . ")");
					/* associate this new Data Query with all hosts that are using the current Device Template
					   but leave those hosts that have this Data Query already.
					   Reindex all those Hosts */
					$new_dq_host_entries = db_fetch_assoc("
								SELECT 	host.id AS host_id,
										host.description AS description,
										host.hostname AS hostname
								FROM  	host,
										host_template_snmp_query
								WHERE	host.host_template_id					= host_template_snmp_query.host_template_id
								AND		host_template_snmp_query.snmp_query_id	= " . $_POST["snmp_query_id"] . "
								AND		host.id NOT
								IN (
									SELECT host_snmp_query.host_id
									FROM   host_snmp_query
									WHERE  host_snmp_query.snmp_query_id = " . $_POST["snmp_query_id"] . "
									)"
								);
					if (sizeof($new_dq_host_entries) > 0) {
						/* notify the user of changes to hosts */
						debug_log_clear("host_template");
						$template_name = db_fetch_cell("SELECT name FROM snmp_query WHERE id = " . $_POST["snmp_query_id"]);
						debug_log_insert("host_template", __("Adding Data Query: ") . $template_name . " to ");

						foreach($new_dq_host_entries as $entry) {
							/* add the Data Query */
							db_execute("REPLACE INTO host_snmp_query (host_id,snmp_query_id,reindex_method)
										VALUES (". $entry["host_id"] . ","
												 . get_request_var_post("snmp_query_id") . ","
												 . get_request_var_post("reindex_method") . "
												)"
										);
							/* recache snmp data */
							run_data_query($entry["host_id"], get_request_var_post("snmp_query_id"));
							debug_log_insert("host_template", $entry["hostname"] . ", " . $entry["description"]);
						}
					}
					$redirect_back = true;
				}
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"])) || ($redirect_back == true) || $reindex_performed) {
			header("Location: device_templates.php?action=edit&id=" . (empty($device_template_id) ? $_POST["id"] : $device_template_id));
		}else{
			header("Location: device_templates.php");
		}
		exit;
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $host_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $template_id) {
				/* ================= input validation ================= */
				input_validate_input_number($template_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM host WHERE host_template_id=$template_id LIMIT 1"))) {
					$bad_ids[] = $template_id;
				}else{
					$template_ids[] = $template_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $template_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>Device Template " . $template_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_dt_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('dt_ref_int');
			}

			if (isset($template_ids)) {
				db_execute("delete from host_template where " . array_to_sql_or($template_ids, "id"));
				db_execute("delete from host_template_snmp_query where " . array_to_sql_or($template_ids, "host_template_id"));
				db_execute("delete from host_template_graph where " . array_to_sql_or($template_ids, "host_template_id"));

				/* "undo" any device that is currently using this template */
				db_execute("update host set host_template_id=0 where " . array_to_sql_or($template_ids, "host_template_id"));
			}
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_host_template($selected_items[$i], get_request_var_post("title_format"));
			}
		}

		header("Location: device_templates.php");
		exit;
	}

	/* setup some variables */
	$host_list = ""; $i = 0; $host_array = array();

	/* loop through each of the device templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$host_list .= "<li>" . db_fetch_cell("select name from host_template where id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $host_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='device_templates.php' method='post'>\n";

	if (sizeof($host_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following Device Templates? All devices currently attached this these Device Templates will lose their template assocation.") . "</p>
						<p>$host_list</p>
					</td>
				</tr>\n
				";
		}elseif (get_request_var_post("drp_action") == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("When you click save, the following Device Templates will be duplicated. You can optionally change the title format for the new Device Templates.") . "</p>
						<p>$host_list</p>
						<p><strong>" . __("Title Format:") . "</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>" . __("You must first select a Device Template.  Please select 'Return' to return to the previous menu.") . "</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($host_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($host_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ---------------------
    Template Functions
   --------------------- */

function template_item_remove_gt() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("host_template_id"));
	/* ==================================================== */

	db_execute("delete from host_template_graph where graph_template_id=" . $_GET["id"] . " and host_template_id=" . $_GET["host_template_id"]);
}

function template_item_remove_dq() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("host_template_id"));
	/* ==================================================== */

	db_execute("delete from host_template_snmp_query where snmp_query_id=" . $_GET["id"] . " and host_template_id=" . $_GET["host_template_id"]);
}

function template_edit() {
	global $colors, $fields_device_template_edit, $reindex_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	/* remember if there's something we want to show to the user */
	$debug_log = debug_log_return("host_template");

	if (!empty($debug_log)) {
		debug_log_clear("host_template");
		?>
		<table class='topBoxAlt'>
			<tr bgcolor="<?php print $colors["light"];?>">
				<td class='mono'>
					<?php print $debug_log;?>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	if (!empty($_GET["id"])) {
		$device_template = db_fetch_row("select * from host_template where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $device_template["name"] . "]";
	}else{
		$header_label = __("[new]");
		$_GET["id"] = 0;
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='host_template_edit'>\n";
	html_start_box("<strong>" . __("Device Templates") . "</strong> $header_label", "100", $colors["header"], "0", "center", "", true);
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'host_template');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_device_template_edit, (isset($device_template) ? $device_template : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	?>
	<script type="text/javascript">
	<!--

	// default snmp information
	var snmp_community       = document.getElementById('snmp_community').value;
	var snmp_username        = document.getElementById('snmp_username').value;
	var snmp_password        = document.getElementById('snmp_password').value;
	var snmp_auth_protocol   = document.getElementById('snmp_auth_protocol').value;
	var snmp_priv_passphrase = document.getElementById('snmp_priv_passphrase').value;
	var snmp_priv_protocol   = document.getElementById('snmp_priv_protocol').value;
	var snmp_context         = document.getElementById('snmp_context').value;
	var snmp_port            = document.getElementById('snmp_port').value;
	var snmp_timeout         = document.getElementById('snmp_timeout').value;
	var max_oids             = document.getElementById('max_oids').value;

	// default ping methods
	var ping_method    = document.getElementById('ping_method').value;
	var ping_port      = document.getElementById('ping_port').value;
	var ping_timeout   = document.getElementById('ping_timeout').value;
	var ping_retries   = document.getElementById('ping_retries').value;

	// default availability methods
	var availability_methods = document.getElementById('availability_method').options;
	var num_methods          = document.getElementById('availability_method').length;
	var selectedIndex        = document.getElementById('availability_method').selectedIndex;

	var agent = navigator.userAgent;
	agent = agent.match("MSIE");

	function setPingVisibility() {
		availability_method = document.getElementById('availability_method').value;
		ping_method         = document.getElementById('ping_method').value;

		/* debugging, uncomment as required */
		//alert("The availability method is '" + availability_method + "'");
		//alert("The ping method is '" + ping_method + "'");

		switch(availability_method) {
		case "0": // none
			document.getElementById('row_ping_method').style.display  = "none";
			document.getElementById('row_ping_port').style.display    = "none";
			document.getElementById('row_ping_timeout').style.display = "none";
			document.getElementById('row_ping_retries').style.display = "none";

			break;
		case "2": // snmp
			document.getElementById('row_ping_method').style.display  = "none";
			document.getElementById('row_ping_port').style.display    = "none";
			document.getElementById('row_ping_timeout').style.display = "";
			document.getElementById('row_ping_retries').style.display = "";

			break;
		default: // ping ok
			switch(ping_method) {
			case "1": // ping icmp
				document.getElementById('row_ping_method').style.display  = "";
				document.getElementById('row_ping_port').style.display    = "none";
				document.getElementById('row_ping_timeout').style.display = "";
				document.getElementById('row_ping_retries').style.display = "";

				break;
			case "2": // ping udp
			case "3": // ping tcp
				document.getElementById('row_ping_method').style.display  = "";
				document.getElementById('row_ping_port').style.display    = "";
				document.getElementById('row_ping_timeout').style.display = "";
				document.getElementById('row_ping_retries').style.display = "";

				break;
			}

			break;
		}
	}

	function addSelectItem(item, formObj) {
		if (agent != "MSIE") {
			formObj.add(item,null); // standards compliant
		}else{
			formObj.add(item);      // IE only
		}
	}

	function setAvailability(type) {
		/* get the availability structure */
		var am=document.getElementById('availability_method');

		/* get current selectedIndex */
		selectedIndex = document.getElementById('availability_method').selectedIndex;

		/* debugging uncomment as required */
		//alert("The selectedIndex is '" + selectedIndex + "'");
		//alert("The array length is '" + am.length + "'");

		switch(type) {
		case "NoSNMP":
			/* remove snmp options */
			if (am.length == 4) {
				am.remove(1);
				am.remove(1);
			}

			/* set the index to something valid, like "ping" */
			if (selectedIndex > 1) {
				am.selectedIndex=1;
			}

			break;
		case "All":
			/* restore all options */
			if (am.length == 2) {
				am.remove(0);
				am.remove(0);

				var a=document.createElement('option');
				var b=document.createElement('option');
				var c=document.createElement('option');
				var d=document.createElement('option');

				a.value="0";
				a.text="<?php print __("None");?>";
				addSelectItem(a,am);

				b.value="1";
				b.text="<?php print __("Ping and SNMP");?>";
				addSelectItem(b,am);

				c.value="2";
				c.text="<?php print __("SNMP");?>";
				addSelectItem(c,am);

				d.value="3";
				d.text="<?php print __("Ping");?>";
				addSelectItem(d,am);

				/* restore the correct index number */
				if (selectedIndex == 0) {
					am.selectedIndex = 0;
				}else{
					am.selectedIndex = 3;
				}
			}

			break;
		}

		setAvailabilityVisibility(type, am.selectedIndex);
		setPingVisibility();
	}

	function setAvailabilityVisibility(type, selectedIndex) {
		switch(type) {
		case "NoSNMP":
			switch(selectedIndex) {
			case "0": // availability none
				document.getElementById('row_ping_method').style.display="none";
				document.getElementById('ping_method').value=0;

				break;
			case "1": // ping
				document.getElementById('row_ping_method').style.display="";
				document.getElementById('ping_method').value=ping_method;

				break;
			}
		case "All":
			switch(selectedIndex) {
			case "0": // availability none
				document.getElementById('row_ping_method').style.display="none";
				document.getElementById('ping_method').value=0;

				break;
			case "1": // ping and snmp
			case "3": // ping
				if ((document.getElementById('row_ping_method').style.display == "none") ||
					(document.getElementById('row_ping_method').style.display == undefined)) {
					document.getElementById('ping_method').value=ping_method;
					document.getElementById('row_ping_method').style.display="";
				}

				break;
			case "2": // snmp
				document.getElementById('row_ping_method').style.display="none";
				document.getElementById('ping_method').value="0";

				break;
			}
		}
	}

	function changeHostForm() {
		snmp_version        = document.getElementById('snmp_version').value;

		switch(snmp_version) {
		case "0":
			setAvailability("NoSNMP");
			setSNMP("None");

			break;
		case "1":
		case "2":
			setAvailability("All");
			setSNMP("v1v2");

			break;
		case "3":
			setAvailability("All");
			setSNMP("v3");

			break;
		}
	}

	function setSNMP(snmp_type) {
		switch(snmp_type) {
		case "None":
			document.getElementById('row_snmp_username').style.display        = "none";
			document.getElementById('row_snmp_password').style.display        = "none";
			document.getElementById('row_snmp_community').style.display       = "none";
			document.getElementById('row_snmp_auth_protocol').style.display   = "none";
			document.getElementById('row_snmp_priv_passphrase').style.display = "none";
			document.getElementById('row_snmp_priv_protocol').style.display   = "none";
			document.getElementById('row_snmp_context').style.display         = "none";
			document.getElementById('row_snmp_port').style.display            = "none";
			document.getElementById('row_snmp_timeout').style.display         = "none";
			document.getElementById('row_max_oids').style.display             = "none";

			break;
		case "v1v2":
			document.getElementById('row_snmp_username').style.display        = "none";
			document.getElementById('row_snmp_password').style.display        = "none";
			document.getElementById('row_snmp_community').style.display       = "";
			document.getElementById('row_snmp_auth_protocol').style.display   = "none";
			document.getElementById('row_snmp_priv_passphrase').style.display = "none";
			document.getElementById('row_snmp_priv_protocol').style.display   = "none";
			document.getElementById('row_snmp_context').style.display         = "none";
			document.getElementById('row_snmp_port').style.display            = "";
			document.getElementById('row_snmp_timeout').style.display         = "";
			document.getElementById('row_max_oids').style.display             = "";

			break;
		case "v3":
			document.getElementById('row_snmp_username').style.display        = "";
			document.getElementById('row_snmp_password').style.display        = "";
			document.getElementById('row_snmp_community').style.display       = "none";
			document.getElementById('row_snmp_auth_protocol').style.display   = "";
			document.getElementById('row_snmp_priv_passphrase').style.display = "";
			document.getElementById('row_snmp_priv_protocol').style.display   = "";
			document.getElementById('row_snmp_context').style.display         = "";
			document.getElementById('row_snmp_port').style.display            = "";
			document.getElementById('row_snmp_timeout').style.display         = "";
			document.getElementById('row_max_oids').style.display             = "";

			break;
		}
	}

	function toggleAvailabilityAndSnmp(show) {
		if (show) {
			$('#row_override_permitted').show();
			$('#row_availability_header').show();
			$('#row_availability_method').show();
			$('#row_ping_method').show();
			$('#row_ping_port').show();
			$('#row_ping_timeout').show();
			$('#row_ping_retries').show();
			$('#row_snmp_spacer').show();
			$('#row_snmp_version').show();
			$('#row_snmp_username').show();
			$('#row_snmp_password').show();
			$('#row_snmp_community').show();
			$('#row_snmp_auth_protocol').show();
			$('#row_snmp_priv_passphrase').show();
			$('#row_snmp_priv_protocol').show();
			$('#row_snmp_context').show();
			$('#row_snmp_port').show();
			$('#row_snmp_timeout').show();
			$('#row_max_oids').show();

			changeHostForm();
		}else{
			$('#row_override_permitted').hide();
			$('#row_availability_header').hide();
			$('#row_availability_method').hide();
			$('#row_ping_method').hide();
			$('#row_ping_port').hide();
			$('#row_ping_timeout').hide();
			$('#row_ping_retries').hide();
			$('#row_snmp_spacer').hide();
			$('#row_snmp_version').hide();
			$('#row_snmp_username').hide();
			$('#row_snmp_password').hide();
			$('#row_snmp_community').hide();
			$('#row_snmp_auth_protocol').hide();
			$('#row_snmp_priv_passphrase').hide();
			$('#row_snmp_priv_protocol').hide();
			$('#row_snmp_context').hide();
			$('#row_snmp_port').hide();
			$('#row_snmp_timeout').hide()
			$('#row_max_oids').hide();
		}
	}

	/* jQuery stuff */
	$().ready(function() {
		toggleAvailabilityAndSnmp(document.getElementById('override_defaults').checked);

		/* Hide options when override is turned off */
		$("#override_defaults").change(function () {
			toggleAvailabilityAndSnmp(this.checked);
		});

		/* Hide "Uptime Goes Backwards" if snmp_version has been set to "None" */
		$("#snmp_version").change(function () {
				/* get PHP constants into javascript namespace */
				var reindex_none = <?php print DATA_QUERY_AUTOINDEX_NONE;?>;
				var reindex_reboot = <?php print DATA_QUERY_AUTOINDEX_BACKWARDS_UPTIME;?>;
				/* we require numeric values for comparison */
				var current_reindex = parseInt($(this).val());
				switch (current_reindex)
				{
					case reindex_none:
						/* now that SNMP is disabled, select reindex method "None" */
						$("#reindex_method option[value=" + reindex_none + "]").attr('selected', 'true');
						/* disable SNMP options: "Uptime Goes Backwards" never works with pure Script Data Queries */
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('disabled', 'true');
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('title', '<?php print __("Disabled due to SNMP settings");?>');
						break;
					default:
						/* "Uptime Goes Backwards" is allowed again */
						$("#reindex_method option[value=" + reindex_reboot + "]").removeAttr("disabled");
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('title', '');
						/* select this again as default reindex method */
						/* TODO: this ignores the default reindex method of the associated Device Template
						   to get it, an AJAX call is required */
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('selected', 'true');
			}
		});
	});

	//-->
	</script>
	<?php

	if (!empty($_GET["id"])) {
		html_start_box("<strong>" . __("Associated Graph Templates") . "</strong>", "100", $colors["header"], "3", "center", "", true);
		print "<tr><td>";
		html_header(array(__("Graph Template Name")), 3);

		$selected_graph_templates = db_fetch_assoc("SELECT
			graph_templates.id,
			graph_templates.name
			FROM (graph_templates,host_template_graph)
			WHERE graph_templates.id=host_template_graph.graph_template_id
			AND host_template_graph.host_template_id=" . $_GET["id"] . "
			ORDER BY graph_templates.name");

		$available_graph_templates = db_fetch_assoc("SELECT
			graph_templates.id,
			graph_templates.name
			FROM graph_templates LEFT JOIN host_template_graph
			ON (graph_templates.id=host_template_graph.graph_template_id AND host_template_graph.host_template_id=" . $_GET["id"] . ")
			WHERE host_template_graph.host_template_id IS NULL
			ORDER BY graph_templates.name");

		/* omit those graph_templates, that have already been associated */
		$keeper = array();
		foreach ($available_graph_templates as $item) {
			if (sizeof(db_fetch_assoc("SELECT graph_template_id FROM host_template_graph " .
					" WHERE ((host_template_id=" . $_GET["id"] . ")" .
					" AND (graph_template_id=" . $item["id"] ."))")) > 0) {
				/* do nothing */
			} else {
				array_push($keeper, $item);
			}
		}

		$available_graph_templates = $keeper;

		$i = 0;
		if (sizeof($selected_graph_templates) > 0) {
			foreach ($selected_graph_templates as $item) {
				form_alternate_row_color("selected_graph_template" . $item["id"], true);
				$i++;
				?>
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
					</td>
					<td align='right' nowrap>
						<a href='<?php print htmlspecialchars("device_templates.php?action=item_remove_gt&id=" . $item["id"] . "&host_template_id=" . $_GET["id"]);?>'><img class="buttonSmall" src='images/delete_icon_large.gif' title='<?php print __("Delete Graph Template Association");?>' alt='<?php print __("Delete");?>' align='middle'></a>
					</td>
				<?php
				form_end_row();
			}
		}else{
			print "<tr><td><em>" . __("No associated graph templates.") . "</em></td></tr>";
		}

		form_alternate_row_color("add_template" . get_request_var("id"), true);
		?>
			<td colspan="2">
				<table cellspacing="0" cellpadding="1" width="100%">
					<tr>
					<td nowrap><?php print __("Add Graph Template:");?>&nbsp;
						<?php form_dropdown("graph_template_id",$available_graph_templates,"name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="submit" Value="<?php print __("Add");?>" name="add_gt_y" align="middle">
					</td>
					</tr>
				</table>
			</td>

		<?php
		form_end_row();
		print "</table></td></tr>";		/* end of html_header */
		html_end_box(FALSE);

		html_start_box("<strong>" . __("Associated Data Queries") . "</strong>", "100", $colors["header"], "0", "center", "", true);
		print "<tr><td>";
		html_header(array(__("Data Query Name"), __("Re-Index Method")), 2);

		$selected_data_queries = db_fetch_assoc("SELECT
			snmp_query.id,
			snmp_query.name,
			host_template_snmp_query.reindex_method
			FROM (snmp_query,host_template_snmp_query)
			WHERE snmp_query.id=host_template_snmp_query.snmp_query_id
			AND host_template_snmp_query.host_template_id=" . $_GET["id"] . "
			ORDER BY snmp_query.name");

		$available_data_queries = db_fetch_assoc("SELECT
			snmp_query.id,
			snmp_query.name
			FROM snmp_query LEFT JOIN host_template_snmp_query
			ON (snmp_query.id=host_template_snmp_query.snmp_query_id AND host_template_snmp_query.host_template_id=" . $_GET["id"] . ")
			WHERE host_template_snmp_query.host_template_id IS NULL
			ORDER BY snmp_query.name");

		/* omit those data_queries, that have already been associated */
		$keeper = array();
		foreach ($available_data_queries as $item) {
			if (sizeof(db_fetch_assoc("SELECT snmp_query_id FROM host_template_snmp_query " .
					" WHERE ((host_template_id=" . $_GET["id"] . ")" .
					" AND (snmp_query_id=" . $item["id"] ."))")) > 0) {
				/* do nothing */
			} else {
				array_push($keeper, $item);
			}
		}

		$available_data_queries = $keeper;

		$i = 0;
		if (sizeof($selected_data_queries) > 0) {
			foreach ($selected_data_queries as $item) {
				form_alternate_row_color("selected_data_query" . $item["id"], true);
				$i++;
				?>
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
					</td>
					<td>
						<?php form_dropdown("reindex_method_host_template_".get_request_var("id")."_query_".$item["id"]."_method_".$item["reindex_method"],$reindex_types,"","",$item["reindex_method"],"","","","");?>
					</td>
					<td align='right'>
						<a href='<?php print htmlspecialchars("device_templates.php?action=item_remove_dq&id=" . $item["id"] . "&host_template_id=" . $_GET["id"]);?>'><img class='buttonSmall' src='images/delete_icon_large.gif' title='Delete Data Query Association' alt='Delete' align='middle'></a>
					</td>
				<?php
				form_end_row();
			}
		}else{
			print "<tr><td><em>" . __("No associated data queries.") . "</em></td></tr>";
		}

		form_alternate_row_color("add_data_query" . get_request_var("id"), true);
		?>
			<td colspan="5">
				<table cellspacing="0" cellpadding="1" width="100%">
					<tr>
					<td nowrap><?php print __("Add Data Query:");?>&nbsp;
						<?php form_dropdown("snmp_query_id",$available_data_queries,"name","id","","","");?>
					</td>
					<td nowrap><?php print __("Re-Index Method:");?>&nbsp;
						<?php form_dropdown("reindex_method",$reindex_types,"","","1","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="submit" value="<?php print __("Add");?>" name="add_dq_y" align="middle">
					</td>
					</tr>
				</table>
			</td>
		<?php
		form_end_row();
		print "</table></td></tr>";		/* end of html_header */
		html_end_box(TRUE);
	}

	form_save_button_alt();
}

function template() {
	global $colors, $host_actions, $item_rows;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_host_template_current_page");
		kill_session_var("sess_host_template_rows");
		kill_session_var("sess_host_template_filter");
		kill_session_var("sess_host_template_sort_column");
		kill_session_var("sess_host_template_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	?>
	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?rows=' + objForm.rows.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	//-->
	</script>
	<?php

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_host_template_current_page", "1");
	load_current_session_value("rows", "sess_host_template_rows", "-1");
	load_current_session_value("filter", "sess_host_template_filter", "");
	load_current_session_value("sort_column", "sess_host_template_sort_column", "name");
	load_current_session_value("sort_direction", "sess_host_template_sort_direction", "ASC");

	display_output_messages();

	html_start_box("<strong>" . __("Device Templates") . "</strong>", "100", $colors["header"], "3", "center", "device_templates.php?action=edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_host_template" action="device_templates.php">
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyFilterChange(document.form_host_template)">
							<option value="-1"<?php if (get_request_var_request("rows") == "-1") {?> selected<?php }?>>Default</option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if (get_request_var_request("rows") == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var_request("filter"))) {
		$sql_where = "WHERE (host_template.name LIKE '%%" . $_REQUEST["filter"] . "%%')
			OR (host_template.description LIKE '%%" . get_request_var_request("filter") . "%%')";
	}else{
		$sql_where = "";
	}

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(host_template.id)
		FROM host_template
		$sql_where");

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$template_list = db_fetch_assoc("SELECT *
		FROM host_template
		$sql_where
		ORDER BY " . get_request_var_request('sort_column') . " " . get_request_var_request('sort_direction') .
		" LIMIT " . ($rows*(get_request_var_request("page")-1)) . "," . $rows);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, 7, "device_templates.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"name" => array(__("Template Title"), "ASC"),
		"description" => array(__("Description"), "ASC"),
		"nosort1" => array(__("Availbility/SNMP Settings"), "ASC"),
		"nosort2" => array(__("Image"), "")
	);

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color('line' . $template["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("device_templates.php?action=edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template["name"]) : $template["name"]) . "</a>", $template["id"]);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("device_templates.php?action=edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $template["description"]) : $template["description"]) . "</a>", $template["id"]);
			form_selectable_cell(($template["override_defaults"] == "on" ? __("Template controls Availability and SNMP") . ($template["override_permitted"] == "on" ? __(", User can override"):__(", Template propagation is forced")):__("Using System Defaults")) , $template["id"]);
			form_selectable_cell("<img src='" . $template["image"] . "'>", $template["id"]);
			form_checkbox_cell($template["name"], $template["id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>" . __("No Device Templates") . "</em></td></tr>\n";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($host_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
