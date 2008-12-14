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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");

define("MAX_DISPLAY_PAGES", 21);

$host_actions = array(
	1 => "Delete",
	2 => "Duplicate"
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }


switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item_remove_gt':
		template_item_remove_gt();

		header("Location: host_templates.php?action=edit&id=" . $_GET["host_template_id"]);
		break;
	case 'item_remove_dq':
		template_item_remove_dq();

		header("Location: host_templates.php?action=edit&id=" . $_GET["host_template_id"]);
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
		if (ereg("^reindex_method_host_template_([0-9]+)_query_([0-9]+)_method_([0-9]+)$", $var, $matches)) {
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
			$host_template_id = sql_save($save, "host_template");

			if ($host_template_id) {
				raise_message(1);

				if (isset($_POST["add_gt_y"])) {
					/* ================= input validation ================= */
					input_validate_input_number(get_request_var_post("graph_template_id"));
					/* ==================================================== */
					db_execute("replace into host_template_graph (host_template_id,graph_template_id) values($host_template_id," . $_POST["graph_template_id"] . ")");
					/* associate this new Graph Template with all hosts that are using the current Host Template
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
						debug_log_insert("host_template", "Adding Graph Template: " . $template_name . " to ");

						foreach($new_gt_host_entries as $entry) {
							/* add the Graph Template */
							db_execute("REPLACE INTO host_graph ( host_id, graph_template_id )
									VALUES (" . $entry["host_id"] . ","
											  . $_POST["graph_template_id"] . "
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
					db_execute("replace into host_template_snmp_query (host_template_id,snmp_query_id, reindex_method) values($host_template_id," . $_POST["snmp_query_id"] . ", " . $_POST["reindex_method"] . ")");
					/* associate this new Data Query with all hosts that are using the current Host Template
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
						debug_log_insert("host_template", "Adding Data Query: " . $template_name . " to ");

						foreach($new_dq_host_entries as $entry) {
							/* add the Data Query */
							db_execute("REPLACE INTO host_snmp_query (host_id,snmp_query_id,reindex_method)
										VALUES (". $entry["host_id"] . ","
												 . $_POST["snmp_query_id"] . ","
												 . $_POST["reindex_method"] . "
												)"
										);
							/* recache snmp data */
							run_data_query($entry["host_id"], $_POST["snmp_query_id"]);
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
			header("Location: host_templates.php?action=edit&id=" . (empty($host_template_id) ? $_POST["id"] : $host_template_id));
		}else{
			header("Location: host_templates.php");
		}
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

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from host_template where " . array_to_sql_or($selected_items, "id"));
			db_execute("delete from host_template_snmp_query where " . array_to_sql_or($selected_items, "host_template_id"));
			db_execute("delete from host_template_graph where " . array_to_sql_or($selected_items, "host_template_id"));

			/* "undo" any device that is currently using this template */
			db_execute("update host set host_template_id=0 where " . array_to_sql_or($selected_items, "host_template_id"));
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				duplicate_host_template($selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: host_templates.php");
		exit;
	}

	/* setup some variables */
	$host_list = ""; $i = 0; $host_array = array();

	/* loop through each of the host templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$host_list .= "<li>" . db_fetch_cell("select name from host_template where id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $host_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='host_templates.php' method='post'>\n";

	if (sizeof($host_array)) {
		if ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>Are you sure you want to delete the following host templates? All devices currently attached
						this these host templates will lose their template assocation.</p>
						<p>$host_list</p>
					</td>
				</tr>\n
				";
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			print "	<tr>
					<td class='textArea'>
						<p>When you click save, the following host templates will be duplicated. You can
						optionally change the title format for the new host templates.</p>
						<p>$host_list</p>
						<p><strong>Title Format:</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
					</td>
				</tr>\n
				";
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>You must first select a Device Template.  Please select 'Return' to return to the previous menu.</p>
				</td>
			</tr>\n";
	}

	if (!sizeof($host_array)) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($host_array), $_POST["drp_action"]);
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
	global $colors, $fields_host_template_edit, $reindex_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	/* remember if there's something we want to show to the user */
	$debug_log = debug_log_return("host_template");

	if (!empty($debug_log)) {
		debug_log_clear("host_template");
		?>
		<table width='100%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center'>
			<tr bgcolor="<?php print $colors["light"];?>">
				<td style="padding: 3px; font-family: monospace;">
					<?php print $debug_log;?>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	if (!empty($_GET["id"])) {
		$host_template = db_fetch_row("select * from host_template where id=" . $_GET["id"]);
		$header_label = "[edit: " . $host_template["name"] . "]";
	}else{
		$header_label = "[new]";
		$_GET["id"] = 0;
	}

	html_start_box("<strong>Host Templates</strong> $header_label", "100%", $colors["header"], "3", "center", "", true);

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_host_template_edit, (isset($host_template) ? $host_template : array()))
		));

	html_end_box(FALSE);

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
				a.text="None";
				addSelectItem(a,am);

				b.value="1";
				b.text="Ping and SNMP";
				addSelectItem(b,am);

				c.value="2";
				c.text="SNMP";
				addSelectItem(c,am);

				d.value="3";
				d.text="Ping";
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
			case 3: // ping
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

	registerOnLoadFunction("host_templates", "changeHostForm();");

	/* jQuery stuff */
	$().ready(function() {

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
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('title', 'Disabled due to SNMP settings');
						break;
					default:
						/* "Uptime Goes Backwards" is allowed again */
						$("#reindex_method option[value=" + reindex_reboot + "]").removeAttr("disabled");
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('title', '');
						/* select this again as default reindex method */
						/* TODO: this ignores the default reindex method of the associated host template
						   to get it, an AJAX call is required */
						$("#reindex_method option[value=" + reindex_reboot + "]").attr('selected', 'true');
			}
		});

	});

	-->
	</script>
	<?php

	if (!empty($_GET["id"])) {
		html_start_box("<strong>Associated Graph Templates</strong>", "100%", $colors["header"], "2", "center", "", true);

		print "	<tr class='rowSubHeader'>
				<td><span style='color: white; font-weight: bold;'>Graph Template Name</span></td>
				<td></td>
			</tr>";

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
				form_alternate_row_color($_GET["id"], true);
				$i++;
				?>
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
					</td>
					<td align='right' nowrap>
						<a href='<?php print htmlspecialchars("host_templates.php?action=item_remove_gt&id=" . $item["id"] . "&host_template_id=" . $_GET["id"]);?>'><img class="buttonSmall" src='images/delete_icon_large.gif' title='Delete Graph Template Association' alt='Delete'></a>
					</td>
				</tr>
				<?php
			}
		}else{ print "<tr><td><em>No associated graph templates.</em></td></tr>"; }

		form_alternate_row_color($_GET["id"], true);
		?>
			<td colspan="2">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap>Add Graph Template:&nbsp;
						<?php form_dropdown("graph_template_id",$available_graph_templates,"name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="submit" Value="Add" name="add_gt_y" align="middle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box(FALSE);

		html_start_box("<strong>Associated Data Queries</strong>", "100%", $colors["header"], "3", "center", "", true);

		html_header(array("Data Query Name", "Re-Index Method"), 2);

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
			form_alternate_row_color($_GET["id"], true);
			$i++;
			?>
				<td style="padding: 4px;">
					<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
				</td>
				<td>
					<?php form_dropdown("reindex_method_host_template_".$_GET["id"]."_query_".$item["id"]."_method_".$item["reindex_method"],$reindex_types,"","",$item["reindex_method"],"","","","");?>
				</td>
				<td align='right'>
					<a href='<?php print htmlspecialchars("host_templates.php?action=item_remove_dq&id=" . $item["id"] . "&host_template_id=" . $_GET["id"]);?>'><img class='buttonSmall' src='images/delete_icon_large.gif' title='Delete Data Query Association' alt='Delete'></a>
				</td>
			</tr>
			<?php
		}
		}else{ print "<tr><td><em>No associated data queries.</em></td></tr>"; }

		form_alternate_row_color();
		?>
			<td colspan="5">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap>Add Data Query:&nbsp;
						<?php form_dropdown("snmp_query_id",$available_data_queries,"name","id","","","");?>
					</td>
					<td nowrap>Re-Index Method:&nbsp;
						<?php form_dropdown("reindex_method",$reindex_types,"","","1","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="submit" value="Add" name="add_dq_y" align="middle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box(TRUE);
	}

	form_save_button_alt();
}

function template() {
	global $colors, $host_actions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
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
		kill_session_var("sess_host_template_filter");
		kill_session_var("sess_host_template_sort_column");
		kill_session_var("sess_host_template_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_host_template_current_page", "1");
	load_current_session_value("filter", "sess_host_template_filter", "");
	load_current_session_value("sort_column", "sess_host_template_sort_column", "name");
	load_current_session_value("sort_direction", "sess_host_template_sort_direction", "ASC");

	display_output_messages();

	html_start_box("<strong>Host Templates</strong>", "100%", $colors["header"], "3", "center", "host_templates.php?action=edit", true);

	include(CACTI_BASE_PATH . "/include/html/inc_graph_template_filter_table.php");

	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (host_template.name LIKE '%%" . $_REQUEST["filter"] . "%%')";

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(host_template.id)
		FROM host_template
		$sql_where");

	$template_list = db_fetch_assoc("SELECT
		host_template.id,host_template.name
		FROM host_template
		$sql_where
		ORDER BY " . $_REQUEST['sort_column'] . " " . $_REQUEST['sort_direction'] .
		" LIMIT " . (read_config_option("num_rows_device")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_device"));

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 7, "host_templates.php");

	print $nav;

	$display_text = array(
		"name" => array("Template Title", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($template_list) > 0) {
		foreach ($template_list as $template) {
			form_alternate_row_color('line' . $template["id"], true, true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("host_templates.php?action=edit&id=" . $template["id"]) . "'>" . (strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $template["name"]) : $template["name"]) . "</a>", $template["id"]);
			form_checkbox_cell($template["name"], $template["id"]);
			form_end_row();
		}
		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>No Host Templates</em></td></tr>\n";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($host_actions);

	print "</form>\n";
}
?>
