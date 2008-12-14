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

/* --------------------------
    The Save Function
   -------------------------- */

function api_host_form_save() {
	/*
	 * loop for all possible changes of reindex_method
	 * post variable is build like this
	 * 		reindex_method_host_<host_id>_query_<snmp_query_id>_method_<old_reindex_method>
	 * if values of this variable differs from <old_reindex_method>, we will have to update
	 */
	$reindex_performed = false;
	while (list($var,$val) = each($_POST)) {
		if (ereg("^reindex_method_host_([0-9]+)_query_([0-9]+)_method_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("id"));
			input_validate_input_number($matches[1]); # host
			input_validate_input_number($matches[2]); # snmp_query_id
			input_validate_input_number($matches[3]); # old reindex method
			$reindex_method = $val;
			input_validate_input_number($reindex_method); # new reindex_method
			/* ==================================================== */

			# change reindex method of this very item
			if ( $reindex_method != $matches[3]) {
				db_execute("replace into host_snmp_query (host_id,snmp_query_id,reindex_method) values (" . $matches[1] . "," . $matches[2] . "," . $reindex_method . ")");

				/* recache snmp data */
				run_data_query($matches[1], $matches[2]);
				$reindex_performed = true;
			}
		}
	}

	if ((!empty($_POST["add_dq_y"])) && (!empty($_POST["snmp_query_id"]))) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("snmp_query_id"));
		input_validate_input_number(get_request_var_post("reindex_method"));
		/* ==================================================== */

		db_execute("replace into host_snmp_query (host_id,snmp_query_id,reindex_method) values (" . $_POST["id"] . "," . $_POST["snmp_query_id"] . "," . $_POST["reindex_method"] . ")");

		/* recache snmp data */
		run_data_query($_POST["id"], $_POST["snmp_query_id"]);

		header("Location: host.php?action=edit&id=" . $_POST["id"]);
		exit;
	}

	if ((!empty($_POST["add_gt_y"])) && (!empty($_POST["graph_template_id"]))) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("graph_template_id"));
		/* ==================================================== */

		db_execute("replace into host_graph (host_id,graph_template_id) values (" . $_POST["id"] . "," . $_POST["graph_template_id"] . ")");

		header("Location: host.php?action=edit&id=" . $_POST["id"]);
		exit;
	}

	/* save basic host information during first run, host_template should have bee selected */
	if (isset($_POST["save_basic_host"])) {
		/* host template was given, so fetch defaults from it */
		if ($_POST["template_id"] != 0) {
			$host_template = db_fetch_row("SELECT
				host_template.id,
				host_template.name,
				host_template.snmp_community,
				host_template.snmp_version,
				host_template.snmp_username,
				host_template.snmp_password,
				host_template.snmp_port,
				host_template.snmp_timeout,
				host_template.availability_method,
				host_template.ping_method,
				host_template.ping_port,
				host_template.ping_timeout,
				host_template.ping_retries,
				host_template.snmp_auth_protocol,
				host_template.snmp_priv_passphrase,
				host_template.snmp_priv_protocol,
				host_template.snmp_context,
				host_template.max_oids
				FROM host_template
				WHERE id=" . $_POST["template_id"]);
		} else { /* no host template given, so fetch system defaults */
			$host_template["snmp_community"]		= read_config_option("snmp_community");
			$host_template["snmp_version"]			= read_config_option("snmp_ver");
			$host_template["snmp_username"]			= read_config_option("snmp_username");
			$host_template["snmp_password"]			= read_config_option("snmp_password");
			$host_template["snmp_port"]				= read_config_option("snmp_port");
			$host_template["snmp_timeout"]			= read_config_option("snmp_timeout");
			$host_template["availability_method"]	= read_config_option("availability_method");
			$host_template["ping_method"]			= read_config_option("ping_method");
			$host_template["ping_port"]				= read_config_option("ping_port");
			$host_template["ping_timeout"]			= read_config_option("ping_timeout");
			$host_template["ping_retries"]			= read_config_option("ping_retries");
			$host_template["snmp_auth_protocol"]	= read_config_option("snmp_auth_protocol");
			$host_template["snmp_priv_passphrase"]	= read_config_option("snmp_priv_passphrase");
			$host_template["snmp_priv_protocol"]	= read_config_option("snmp_priv_protocol");
			$host_template["snmp_context"]			= read_config_option("snmp_context");
			$host_template["max_oids"]				= read_config_option("max_get_size");
		}

		$host_template["notes"] = ""; /* no support for notes in a host template */
		$host_template["disabled"] = ""; /* no support for disabling in a host template */
		$host_id = api_device_save($_POST["id"], $_POST["template_id"], $_POST["description"],
			$_POST["hostname"], $host_template["snmp_community"], $host_template["snmp_version"],
			$host_template["snmp_username"], $host_template["snmp_password"],
			$host_template["snmp_port"], $host_template["snmp_timeout"],
			$host_template["disabled"],
			$host_template["availability_method"], $host_template["ping_method"],
			$host_template["ping_port"], $host_template["ping_timeout"],
			$host_template["ping_retries"], $host_template["notes"],
			$host_template["snmp_auth_protocol"], $host_template["snmp_priv_passphrase"],
			$host_template["snmp_priv_protocol"], $host_template["snmp_context"], $host_template["max_oids"]);

		header("Location: host.php?action=edit&id=" . (empty($host_id) ? $_POST["id"] : $host_id));
	}

	if ((isset($_POST["save_component_host"])) && (empty($_POST["add_dq_y"]))) {
		if ($_POST["snmp_version"] == 3 && ($_POST["snmp_password"] != $_POST["snmp_password_confirm"])) {
			raise_message(4);
		}else{
			$host_id = api_device_save($_POST["id"], $_POST["template_id"], $_POST["description"],
				trim($_POST["hostname"]), $_POST["snmp_community"], $_POST["snmp_version"],
				$_POST["snmp_username"], $_POST["snmp_password"],
				$_POST["snmp_port"], $_POST["snmp_timeout"],
				(isset($_POST["disabled"]) ? $_POST["disabled"] : ""),
				$_POST["availability_method"], $_POST["ping_method"],
				$_POST["ping_port"], $_POST["ping_timeout"],
				$_POST["ping_retries"], $_POST["notes"],
				$_POST["snmp_auth_protocol"], $_POST["snmp_priv_passphrase"],
				$_POST["snmp_priv_protocol"], $_POST["snmp_context"], $_POST["max_oids"]);
		}

		if ((is_error_message()) || ($_POST["template_id"] != $_POST["_template_id"]) || $reindex_performed) {
			header("Location: host.php?action=edit&id=" . (empty($host_id) ? $_POST["id"] : $host_id));
		}else{
			header("Location: host.php");
		}
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function api_host_form_actions() {
	global $colors, $device_actions, $fields_host_edit;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "2") { /* Enable Selected Devices */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				db_execute("update host set disabled='' where id='" . $selected_items[$i] . "'");

				/* update poller cache */
				$data_sources = db_fetch_assoc("select id from data_local where host_id='" . $selected_items[$i] . "'");

				if (sizeof($data_sources) > 0) {
					foreach ($data_sources as $data_source) {
						update_poller_cache($data_source["id"], false);
					}
				}
			}
		}elseif ($_POST["drp_action"] == "3") { /* Disable Selected Devices */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				db_execute("update host set disabled='on' where id='" . $selected_items[$i] . "'");

				/* update poller cache */
				db_execute("delete from poller_item where host_id='" . $selected_items[$i] . "'");
				db_execute("delete from poller_reindex where host_id='" . $selected_items[$i] . "'");
			}
		}elseif ($_POST["drp_action"] == "4") { /* change snmp options */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				reset($fields_host_edit);
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if (isset($_POST["t_$field_name"])) {
						db_execute("update host set $field_name = '" . $_POST[$field_name] . "' where id='" . $selected_items[$i] . "'");
					}
				}

				push_out_host($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "5") { /* Clear Statisitics for Selected Devices */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				db_execute("update host set min_time = '9.99999', max_time = '0', cur_time = '0',	avg_time = '0',
						total_polls = '0', failed_polls = '0',	availability = '100.00'
						where id = '" . $selected_items[$i] . "'");
			}
		}elseif ($_POST["drp_action"] == "6") { /* change availability options */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				reset($fields_host_edit);
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if (isset($_POST["t_$field_name"])) {
						db_execute("update host set $field_name = '" . $_POST[$field_name] . "' where id='" . $selected_items[$i] . "'");
					}
				}

				push_out_host($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "1") { /* delete */
			if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 2; }

			$data_sources_to_act_on = array();
			$graphs_to_act_on       = array();
			$devices_to_act_on      = array();

			for ($i=0; $i<count($selected_items); $i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				$data_sources = db_fetch_assoc("select
					data_local.id as local_data_id
					from data_local
					where " . array_to_sql_or($selected_items, "data_local.host_id"));

				if (sizeof($data_sources) > 0) {
				foreach ($data_sources as $data_source) {
					$data_sources_to_act_on[] = $data_source["local_data_id"];
				}
				}

				if ($_POST["delete_type"] == 2) {
					$graphs = db_fetch_assoc("select
						graph_local.id as local_graph_id
						from graph_local
						where " . array_to_sql_or($selected_items, "graph_local.host_id"));

					if (sizeof($graphs) > 0) {
					foreach ($graphs as $graph) {
						$graphs_to_act_on[] = $graph["local_graph_id"];
					}
					}
				}

				$devices_to_act_on[] = $selected_items[$i];
			}

			switch ($_POST["delete_type"]) {
				case '1': /* leave graphs and data_sources in place, but disable the data sources */
					api_data_source_disable_multi($data_sources_to_act_on);

					break;
				case '2': /* delete graphs/data sources tied to this device */
					api_data_source_remove_multi($data_sources_to_act_on);

					api_graph_remove_multi($graphs_to_act_on);

					break;
			}

			api_device_remove_multi($devices_to_act_on);
		}elseif (ereg("^tr_([0-9]+)$", $_POST["drp_action"], $matches)) { /* place on tree */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				input_validate_input_number(get_request_var_post("tree_id"));
				input_validate_input_number(get_request_var_post("tree_item_id"));
				/* ==================================================== */

				api_tree_item_save(0, $_POST["tree_id"], TREE_ITEM_TYPE_HOST, $_POST["tree_item_id"], "", 0, read_graph_config_option("default_rra_id"), $selected_items[$i], 1, 1, false);
			}
		} else {
			api_plugin_hook_function('device_action_execute', $_POST['drp_action']);
		}

		header("Location: host.php");
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

			$host_list .= "<li>" . db_fetch_cell("select description from host where id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	/* add a list of tree names to the actions dropdown */
	$device_actions = array_merge($device_actions, api_tree_add_tree_names_to_actions_array());

	html_start_box("<strong>" . $device_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='host.php' method='post'>\n";

	if (sizeof($host_array)) {
		if ($_POST["drp_action"] == "2") { /* Enable Devices */
			print "	<tr>
					<td colspan='2' class='textArea'>
						<p>To enable the following devices, press the \"yes\" button below.</p>
						<p>$host_list</p>
					</td>
					</tr>";
		}elseif ($_POST["drp_action"] == "3") { /* Disable Devices */
			print "	<tr>
					<td colspan='2' class='textArea'>
						<p>To disable the following devices, press the \"yes\" button below.</p>
						<p>$host_list</p>
					</td>
					</tr>";
		}elseif ($_POST["drp_action"] == "4") { /* change snmp options */
			print "	<tr>
					<td colspan='2' class='textArea'>
						<p>To change SNMP parameters for the following devices, check the box next to the fields
						you want to update, fill in the new value, and click Save.</p>
						<p>$host_list</p>
					</td>
					</tr>";

			$form_array = array();
			while (list($field_name, $field_array) = each($fields_host_edit)) {
				if ((ereg("^snmp_", $field_name)) ||
					($field_name == "max_oids")) {
					$form_array += array($field_name => $fields_host_edit[$field_name]);

					$form_array[$field_name]["value"] = "";
					$form_array[$field_name]["description"] = "";
					$form_array[$field_name]["form_id"] = 0;
					$form_array[$field_name]["sub_checkbox"] = array(
						"name" => "t_" . $field_name,
						"friendly_name" => "Update this Field",
						"value" => ""
						);
				}
			}

			draw_edit_form(
				array(
					"config" => array("no_form_tag" => true),
					"fields" => $form_array
					)
				);
		}elseif ($_POST["drp_action"] == "6") { /* change availability options */
			print "	<tr>
					<td colspan='2' class='textArea'>
						<p>To change SNMP parameters for the following devices, check the box next to the fields
						you want to update, fill in the new value, and click Save.</p>
						<p>$host_list</p>
					</td>
					</tr>";

			$form_array = array();
			while (list($field_name, $field_array) = each($fields_host_edit)) {
				if (ereg("(availability_method|ping_method|ping_port)", $field_name)) {
					$form_array += array($field_name => $fields_host_edit[$field_name]);

					$form_array[$field_name]["value"] = "";
					$form_array[$field_name]["description"] = "";
					$form_array[$field_name]["form_id"] = 0;
					$form_array[$field_name]["sub_checkbox"] = array(
						"name" => "t_" . $field_name,
						"friendly_name" => "Update this Field",
						"value" => ""
						);
				}
			}

			draw_edit_form(
				array(
					"config" => array("no_form_tag" => true),
					"fields" => $form_array
					)
				);
		}elseif ($_POST["drp_action"] == "5") { /* Clear Statisitics for Selected Devices */
			print "	<tr>
					<td colspan='2' class='textArea'>
						<p>To clear the counters for the following devices, press the \"yes\" button below.</p>
						<p>$host_list</p>
					</td>
					</tr>";
		}elseif ($_POST["drp_action"] == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>Are you sure you want to delete the following devices?</p>
						<p>$host_list</p>";
						form_radio_button("delete_type", "2", "1", "Leave all graphs and data sources untouched.  Data sources will be disabled however.", "1"); print "<br>";
						form_radio_button("delete_type", "2", "2", "Delete all associated <strong>graphs</strong> and <strong>data sources</strong>.", "1"); print "<br>";
						print "</td></tr>
					</td>
				</tr>\n
				";
		}elseif (ereg("^tr_([0-9]+)$", $_POST["drp_action"], $matches)) { /* place on tree */
			print "	<tr>
					<td class='textArea'>
						<p>When you click save, the following hosts will be placed under the branch selected
						below.</p>
						<p>$host_list</p>
						<p><strong>Destination Branch:</strong><br>"; grow_dropdown_tree($matches[1], "tree_item_id", "0"); print "</p>
					</td>
				</tr>\n
				<input type='hidden' name='tree_id' value='" . $matches[1] . "'>\n
				";
		} else {
			$save['drp_action'] = $_POST['drp_action'];
			$save['host_list'] = $host_list;
			$save['host_array'] = (isset($host_array)? $host_array : array());
			api_plugin_hook_function('device_action_prepare', $save);
		}
	} else {
		print "	<tr>
				<td class='textArea'>
					<p>You must first select a Device.  Please select 'Return' to return to the previous menu.</p>
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

/* -------------------
    Data Query Functions
   ------------------- */

function host_reload_query() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("host_id"));
	/* ==================================================== */

	run_data_query($_GET["host_id"], $_GET["id"]);
}

function host_remove_query() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("host_id"));
	/* ==================================================== */

	api_device_dq_remove($_GET["host_id"], $_GET["id"]);
}

function host_remove_gt() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("host_id"));
	/* ==================================================== */

	api_device_gt_remove($_GET["host_id"], $_GET["id"]);
}

/* ---------------------
    Host Functions
   --------------------- */

function host_remove() {
	global $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if ((read_config_option("deletion_verification") == "on") && (!isset($_GET["confirm"]))) {
		include(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm("Are You Sure?", "Are you sure you want to delete the host <strong>'" . db_fetch_cell("select description from host where id=" . $_GET["id"]) . "'</strong>?", "host.php", "host.php?action=remove&id=" . $_GET["id"]);
		include(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("deletion_verification") == "") || (isset($_GET["confirm"]))) {
		api_device_remove($_GET["id"]);
	}
}

function host_edit() {
	global $colors, $fields_host_edit, $fields_host_edit_availability, $reindex_types;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	display_output_messages();

	$host_tabs = array(
		"general" => "General",
		"newgraphs" => "New Graphs",
		"graphs" => "Graphs",
		"datasources" => "Data Sources",
		"meta" => "Metadata"
	);

	if (!empty($_GET["id"])) {
		$host         = db_fetch_row("select * from host where id=" . $_GET["id"]);
		$host_text    = "<strong>" . $host["description"] . "(" . $host["hostname"] . ")</strong>";
		$header_label = "[edit: " . $host["description"] . "]";
	}elseif (!empty($_GET["host_id"])) {		$_GET["id"]   = $_GET["host_id"];
		$host         = db_fetch_row("select * from host where id=" . $_GET["id"]);
		$host_text    = "<strong>" . $host["description"] . "(" . $host["hostname"] . ")</strong>";
		$header_label = "[edit: " . $host["description"] . "]";
	}else{
		$header_label = "[new]";
		$host_text    = "New Host";
		$host         = "";
	}

	/* set the default settings category */
	if (!isset($_GET["tab"])) {
		/* there is no selected tab; select the first one */
		$current_tab = array_keys($host_tabs);
		$current_tab = $current_tab[0];
	}else{
		$current_tab = $_GET["tab"];
	}

	/* draw the categories tabs on the top of the page */
	print "<table width='100%' cellspacing='0' cellpadding='0' align='center'><tr>";
	print "<td><div class='tabs'>";

	if (sizeof($host_tabs) > 0) {
	foreach (array_keys($host_tabs) as $tab_short_name) {
		print "<div class='tabDefault'><a " . (($tab_short_name == $current_tab) ? "class='tabSelected'" : "class='tabDefault'") . " href='host.php?action=edit" . (isset($_GET['id']) ? "&id=" . $_GET['id'] . "&host_id=" . $_GET['id']: "") . "&tab=$tab_short_name'>$host_tabs[$tab_short_name]</a></div>";
	}
	}
	print "</div></td></tr></table>";

	if (!isset($_REQUEST["tab"])) {
		$_REQUEST["tab"] = "general";
	}

	switch ($_REQUEST["tab"]) {
		case "newgraphs":
			include_once(CACTI_BASE_PATH . "/lib/form_graphs_new.php");
			include_once(CACTI_BASE_PATH . "/lib/data_query.php");
			include_once(CACTI_BASE_PATH . "/lib/utility.php");
			include_once(CACTI_BASE_PATH . "/lib/sort.php");
			include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
			include_once(CACTI_BASE_PATH . "/lib/template.php");

			graphs_new();

			break;
		case "datasources":
			include_once(CACTI_BASE_PATH . "/lib/form_data_sources.php");
			include_once(CACTI_BASE_PATH . "/lib/utility.php");
			include_once(CACTI_BASE_PATH . "/lib/api_graph.php");
			include_once(CACTI_BASE_PATH . "/lib/api_data_source.php");
			include_once(CACTI_BASE_PATH . "/lib/template.php");
			include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
			include_once(CACTI_BASE_PATH . "/lib/rrd.php");
			include_once(CACTI_BASE_PATH . "/lib/data_query.php");

			data_source();

			break;
		case "graphs":
			include_once(CACTI_BASE_PATH . "/lib/form_graphs.php");
			include_once(CACTI_BASE_PATH . "/lib/utility.php");
			include_once(CACTI_BASE_PATH . "/lib/api_graph.php");
			include_once(CACTI_BASE_PATH . "/lib/api_tree.php");
			include_once(CACTI_BASE_PATH . "/lib/api_data_source.php");
			include_once(CACTI_BASE_PATH . "/lib/template.php");
			include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
			include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
			include_once(CACTI_BASE_PATH . "/lib/rrd.php");
			include_once(CACTI_BASE_PATH . "/lib/data_query.php");

			graph();

			break;
		default:
			host_display_general($host, $host_text);

			break;
	}

}

function host_display_general($host, $host_text) {
	global $colors, $fields_host_edit, $fields_host_edit_availability, $reindex_types;

	if (isset($host["id"])) {
		html_start_box($host_text, "100%", $colors["header"], "3", "center", "", true);
		?>
		<table width="100%" align="center">
			<tr>
				<?php if (($host["availability_method"] == AVAIL_SNMP) ||
					($host["availability_method"] == AVAIL_SNMP_AND_PING) ||
					($host["availability_method"] == AVAIL_SNMP_OR_PING)) { ?>
				<td class="textInfo">
					SNMP Information<br>
					<span style="font-size: 11px; font-weight: normal;">
					<?php
					if ((($host["snmp_community"] == "") && ($host["snmp_username"] == "")) ||
						($host["snmp_version"] == 0)) {
						print "<span style='color: #ab3f1e; font-weight: bold;'>SNMP not in use</span>\n";
					}else{
						$snmp_system = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.1.0", $host["snmp_version"],
							$host["snmp_username"], $host["snmp_password"],
							$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
							$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"),SNMP_WEBUI);

						/* modify for some system descriptions */
						/* 0000937: System output in hosts.php poor for Alcatel */
						if (substr_count($snmp_system, "00:")) {
							$snmp_system = str_replace("00:", "", $snmp_system);
							$snmp_system = str_replace(":", " ", $snmp_system);
						}

						if ($snmp_system == "") {
							print "<span style='color: #ff0000; font-weight: bold;'>SNMP error</span>\n";
						}else{
							$snmp_uptime   = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.3.0", $host["snmp_version"],
								$host["snmp_username"], $host["snmp_password"],
								$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
								$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"), SNMP_WEBUI);

							$snmp_hostname = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.5.0", $host["snmp_version"],
								$host["snmp_username"], $host["snmp_password"],
								$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
								$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"), SNMP_WEBUI);

							$snmp_location = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.6.0", $host["snmp_version"],
								$host["snmp_username"], $host["snmp_password"],
								$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
								$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"), SNMP_WEBUI);

							$snmp_contact  = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.4.0", $host["snmp_version"],
								$host["snmp_username"], $host["snmp_password"],
								$host["snmp_auth_protocol"], $host["snmp_priv_passphrase"], $host["snmp_priv_protocol"],
								$host["snmp_context"], $host["snmp_port"], $host["snmp_timeout"], read_config_option("snmp_retries"), SNMP_WEBUI);

							print "<strong>System:</strong> " . html_split_string($snmp_system,200) . "<br>\n";
							$days      = intval($snmp_uptime / (60*60*24*100));
							$remainder = $snmp_uptime % (60*60*24*100);
							$hours     = intval($remainder / (60*60*100));
							$remainder = $remainder % (60*60*100);
							$minutes   = intval($remainder / (60*100));
							print "<strong>Uptime: </strong> $snmp_uptime";
							print "&nbsp;($days days, $hours hours, $minutes minutes)<br>\n";
							print "<strong>Hostname: </strong> $snmp_hostname<br>\n";
							print "<strong>Location: </strong> $snmp_location<br>\n";
							print "<strong>Contact: </strong> $snmp_contact<br>\n";
						}
					}
					?>
					</span>
				</td>
				<?php }
				if (($host["availability_method"] == AVAIL_PING) ||
					($host["availability_method"] == AVAIL_SNMP_AND_PING) ||
					($host["availability_method"] == AVAIL_SNMP_OR_PING)) {
					/* create new ping socket for host pinging */
					$ping = new Net_Ping;

					$ping->host = $host;
					$ping->port = $host["ping_port"];

					/* perform the appropriate ping check of the host */
					if ($ping->ping($host["availability_method"], $host["ping_method"],
						$host["ping_timeout"], $host["ping_retries"])) {
						$host_down = false;
						$color     = "color:#000000";
						$font      = "font-weight: normal;";
					}else{
						$host_down = true;
						$color     = "color:#ff0000";
						$font      = "font-weight:bold;";
					}

				?>
				<td class="textInfo" style="vertical-align:top;">
					Ping Results<br>
					<span style="font-size: 10px; <?php print $font . $color;?>;">
					<?php print $ping->ping_response; ?>
					</span>
				</td>
				<?php }else if ($host["availability_method"] == AVAIL_NONE) { ?>
				<td class="textInfo">
					No Availability Check In Use<br>
				</td>
				<?php } ?>
			</tr>
		</table>
		<?php
	}else{		html_start_box($host_text, "100%", $colors["header"], "3", "center", "", false);
	}

	html_end_box(FALSE);

	html_start_box("<strong>General Settings</strong>", "100%", $colors["header"], "3", "center", "", true);

	/* preserve the host template id if passed in via a GET variable */
	if (!empty($_GET["template_id"])) {
		$fields_host_edit["template_id"]["value"] = $_GET["template_id"];
	}

	/* draw basic fields only on first run for a new host */
	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_host_edit, (is_array($host) ? $host : array()))
		));

	/* for a given host, display all availability options as well */
	if (isset($host["id"])) {
		draw_edit_form(array(
			"config" => array("form_name" => "chk"),
			"fields" => inject_form_variables($fields_host_edit_availability, (is_array($host) ? $host : array()))
			));
	}

	html_end_box(!isset($host["id"]));

	/* javascript relates to availability options, so include it only for existing hosts */
	if (isset($host["id"])) {
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
					var e=document.createElement('option');

					a.value="0";
					a.text="None";
					addSelectItem(a,am);

					b.value="1";
					b.text="Ping and SNMP";
					addSelectItem(b,am);

					e.value="4";
					e.text="Ping or SNMP";
					addSelectItem(e,am);

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
				case "3": // ping
				case "4": // ping or snmp
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

		registerOnLoadFunction("host", "changeHostForm();");

		-->
		</script>
		<?php
	}

	if ((isset($_GET["display_dq_details"])) && (isset($_SESSION["debug_log"]["data_query"]))) {
		html_start_box("<strong>Data Query Debug Information</strong>", "100%", $colors["header"], "3", "center", "");

		print "<tr><td><span style='font-family: monospace;'>" . debug_log_return("data_query") . "</span></td></tr>";

		html_end_box();
	}

	if (isset($host["id"])) {
		html_start_box("<strong>Associated Graph Templates</strong>", "100%", $colors["header"], "3", "center", "", true);

		html_header(array("Graph Template Name", "Status"), 2);

		$selected_graph_templates = db_fetch_assoc("select
			graph_templates.id,
			graph_templates.name
			from (graph_templates,host_graph)
			where graph_templates.id=host_graph.graph_template_id
			and host_graph.host_id=" . $_GET["id"] . "
			order by graph_templates.name");

		$available_graph_templates = db_fetch_assoc("SELECT
			graph_templates.id, graph_templates.name
			FROM snmp_query_graph RIGHT JOIN graph_templates
			ON (snmp_query_graph.graph_template_id = graph_templates.id)
			WHERE (((snmp_query_graph.name) Is Null)) ORDER BY graph_templates.name");

		/* omit those graph_templates, that have already been associated */
		$keeper = array();
		foreach ($available_graph_templates as $item) {
			if (sizeof(db_fetch_assoc("SELECT graph_template_id FROM host_graph " .
					" WHERE ((host_id=" . $_GET["id"] . ")" .
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
			$i++;
			form_alternate_row_color();

			/* get status information for this graph template */
			$is_being_graphed = (sizeof(db_fetch_assoc("select id from graph_local where graph_template_id=" . $item["id"] . " and host_id=" . $_GET["id"])) > 0) ? true : false;

			?>
				<td style="padding: 4px;">
					<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
				</td>
				<td>
					<?php print (($is_being_graphed == true) ? "<span style='color:green;'>Is Being Graphed</span> (<a href='graphs.php?action=graph_edit&id=" . db_fetch_cell("select id from graph_local where graph_template_id=" . $item["id"] . " and host_id=" . $_GET["id"] . " limit 0,1") . "'>Edit</a>)" : "<span style='color: #484848;'>Not Being Graphed</span>");?>
				</td>
				<td align='right' nowrap>
					<a href='host.php?action=gt_remove&id=<?php print $item["id"];?>&host_id=<?php print $_GET["id"];?>'><img class='buttonSmall' src='images/delete_icon_large.gif' title='Delete Graph Template Association' alt='Delete'></a>
				</td>
			</tr>
			<?php
		}
		}else{ print "<tr><td><em>No associated graph templates.</em></td></tr>"; }

		form_alternate_row_color();
		?>
			<td colspan="4">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap>Add Graph Template:&nbsp;
						<?php form_dropdown("graph_template_id",$available_graph_templates,"name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="submit" value="Add" name="add_gt_y" align="middle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box(FALSE);

		html_start_box("<strong>Associated Data Queries</strong>", "100%", $colors["header"], "3", "center", "", true);

		html_header(array("Data Query Name", "Debugging", "Re-Index Method", "Status"), 2);

		$selected_data_queries = db_fetch_assoc("select
			snmp_query.id,
			snmp_query.name,
			host_snmp_query.reindex_method
			from (snmp_query,host_snmp_query)
			where snmp_query.id=host_snmp_query.snmp_query_id
			and host_snmp_query.host_id=" . $_GET["id"] . "
			order by snmp_query.name");

		$available_data_queries = db_fetch_assoc("select
			snmp_query.id,
			snmp_query.name
			from snmp_query
			order by snmp_query.name");

		$keeper = array();
		foreach ($available_data_queries as $item) {
			if (sizeof(db_fetch_assoc("SELECT snmp_query_id FROM host_snmp_query " .
					" WHERE ((host_id=" . $_GET["id"] . ")" .
					" and (snmp_query_id=" . $item["id"] ."))")) > 0) {
				/* do nothing */
			} else {
				array_push($keeper, $item);
			}
		}

		$available_data_queries = $keeper;

		$i = 0;
		if (sizeof($selected_data_queries) > 0) {
		foreach ($selected_data_queries as $item) {
			$i++;
			form_alternate_row_color();

			/* get status information for this data query */
			$num_dq_items = sizeof(db_fetch_assoc("select snmp_index from host_snmp_cache where host_id=" . $_GET["id"] . " and snmp_query_id=" . $item["id"]));
			$num_dq_rows = sizeof(db_fetch_assoc("select snmp_index from host_snmp_cache where host_id=" . $_GET["id"] . " and snmp_query_id=" . $item["id"] . " group by snmp_index"));

			$status = "success";

			?>
				<td style="padding: 4px;">
					<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
				</td>
				<td>
					(<a href="host.php?action=query_verbose&id=<?php print $item["id"];?>&host_id=<?php print $_GET["id"];?>">Verbose Query</a>)
				</td>
				<td>
					<?php form_dropdown("reindex_method_host_".$_GET["id"]."_query_".$item["id"]."_method_".$item["reindex_method"],$reindex_types,"","",$item["reindex_method"],"","","","");?>
				</td>
				<td>
					<?php print (($status == "success") ? "<span style='color: green;'>Success</span>" : "<span style='color: green;'>Fail</span>");?> [<?php print $num_dq_items;?> Item<?php print ($num_dq_items == 1 ? "" : "s");?>, <?php print $num_dq_rows;?> Row<?php print ($num_dq_rows == 1 ? "" : "s");?>]
				</td>
				<td align='right' nowrap>
					<a href='host.php?action=query_reload&id=<?php print $item["id"];?>&host_id=<?php print $_GET["id"];?>'><img id='buttonSmall' src='images/reload_icon_small.gif' title='Reload Data Query' alt='Reload' align='middle'></a>&nbsp;
					<a href='host.php?action=query_remove&id=<?php print $item["id"];?>&host_id=<?php print $_GET["id"];?>'><img id='buttonSmall' src='images/delete_icon_large.gif' title='Delete Data Query Association' alt='Delete'></a>
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
		html_end_box();
	}

	form_save_button_alt();
}

function host() {
	global $colors, $device_actions, $item_rows;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("template_id"));
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("status"));
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

	/* clean up search string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_device_current_page");
		kill_session_var("sess_device_filter");
		kill_session_var("sess_device_template_id");
		kill_session_var("sess_host_status");
		kill_session_var("sess_host_rows");
		kill_session_var("sess_host_sort_column");
		kill_session_var("sess_host_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["template_id"]);
		unset($_REQUEST["status"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* let's see if someone changed an important setting */
	$changed  = FALSE;
	$changed += check_changed("filter",      "sess_ds_filter");
	$changed += check_changed("rows",        "sess_ds_rows");
	$changed += check_changed("host_id",     "sess_ds_host_id");
	$changed += check_changed("template_id", "sess_ds_template_id");

	if ($changed) {
		$_REQUEST["page"] = "1";
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_device_current_page", "1");
	load_current_session_value("filter", "sess_device_filter", "");
	load_current_session_value("template_id", "sess_device_template_id", "-1");
	load_current_session_value("status", "sess_host_status", "-1");
	load_current_session_value("rows", "sess_host_rows", read_config_option("num_rows_device"));
	load_current_session_value("sort_column", "sess_host_sort_column", "description");
	load_current_session_value("sort_direction", "sess_host_sort_direction", "ASC");

	?>
	<script type="text/javascript">
	<!--

	function applyViewDeviceFilterChange(objForm) {
		strURL = '?status=' + objForm.status.value;
		strURL = strURL + '&template_id=' + objForm.template_id.value;
		strURL = strURL + '&rows=' + objForm.rows.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}

	-->
	</script>
	<?php

	html_start_box("<strong>Devices</strong>", "100%", $colors["header"], "3", "center", "host.php?action=edit&template_id=" . $_REQUEST["template_id"] . "&status=" . $_REQUEST["status"], true);

	include(CACTI_BASE_PATH . "/include/html/inc_device_filter_table.php");

	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if (strlen($_REQUEST["filter"])) {
		$sql_where = "where (host.hostname like '%%" . $_REQUEST["filter"] . "%%' OR host.description like '%%" . $_REQUEST["filter"] . "%%')";
	}else{
		$sql_where = "";
	}

	if ($_REQUEST["status"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["status"] == "-2") {
		$sql_where .= (strlen($sql_where) ? " and host.disabled='on'" : "where host.disabled='on'");
	}elseif ($_REQUEST["status"] == "-3") {
		$sql_where .= (strlen($sql_where) ? " and host.disabled=''" : "where host.disabled=''");
	}elseif ($_REQUEST["status"] == "-4") {
		$sql_where .= (strlen($sql_where) ? " and (host.status!='3' or host.disabled='on')" : "where (host.status!='3' or host.disabled='on')");
	}else {
		$sql_where .= (strlen($sql_where) ? " and (host.status=" . $_REQUEST["status"] . " AND host.disabled = '')" : "where (host.status=" . $_REQUEST["status"] . " AND host.disabled = '')");
	}

	if ($_REQUEST["template_id"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["template_id"] == "0") {
		$sql_where .= (strlen($sql_where) ? " and host.host_template_id=0" : "where host.template_id=0");
	}elseif (!empty($_REQUEST["template_id"])) {
		$sql_where .= (strlen($sql_where) ? " and host.host_template_id=" . $_REQUEST["template_id"] : "where host.host_template_id=" . $_REQUEST["template_id"]);
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	$total_rows = db_fetch_cell("select
		COUNT(host.id)
		from host
		$sql_where");

	$sortby = $_REQUEST["sort_column"];
	if ($sortby=="hostname") {
		$sortby = "INET_ATON(hostname)";
	}

	$host_graphs       = array_rekey(db_fetch_assoc("SELECT host_id, count(*) as graphs FROM graph_local GROUP BY host_id"), "host_id", "graphs");
	$host_data_sources = array_rekey(db_fetch_assoc("SELECT host_id, count(*) as data_sources FROM data_local GROUP BY host_id"), "host_id", "data_sources");

	$sql_query = "SELECT *
		FROM host
		$sql_where
		ORDER BY " . $sortby . " " . $_REQUEST["sort_direction"] . "
		LIMIT " . ($_REQUEST["rows"]*($_REQUEST["page"]-1)) . "," . $_REQUEST["rows"];

	//print $sql_query;

	$hosts = db_fetch_assoc($sql_query);

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, $_REQUEST["rows"], $total_rows, 11, "host.php");

	print $nav;

	$display_text = array(
		"description" => array("Description", "ASC"),
		"id" => array("ID", "ASC"),
		"nosort1" => array("Graphs", "ASC"),
		"nosort2" => array("Data Sources", "ASC"),
		"status" => array("Status", "ASC"),
		"status_event_count" => array("Event Count", "ASC"),
		"hostname" => array("Hostname", "ASC"),
		"cur_time" => array("Current (ms)", "DESC"),
		"avg_time" => array("Average (ms)", "DESC"),
		"availability" => array("Availability", "ASC"));

	html_header_sort_checkbox($display_text, $_REQUEST["sort_column"], $_REQUEST["sort_direction"]);

	if (sizeof($hosts) > 0) {
		foreach ($hosts as $host) {
			form_alternate_row_color('line' . $host["id"], true, true);
			form_selectable_cell("<a class='linkEditMain' href='host.php?action=edit&id=" . $host["id"] . "'>" .
				(strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $host["description"]) : $host["description"]) . "</a>", $host["id"]);
			form_selectable_cell(round(($host["id"]), 2), $host["id"]);
			form_selectable_cell((isset($host_graphs[$host["id"]]) ? $host_graphs[$host["id"]] : 0), $host["id"]);
			form_selectable_cell((isset($host_data_sources[$host["id"]]) ? $host_data_sources[$host["id"]] : 0), $host["id"]);
			form_selectable_cell(get_colored_device_status(($host["disabled"] == "on" ? true : false), $host["status"]), $host["id"]);
			form_selectable_cell(round(($host["status_event_count"]), 2), $host["id"]);
			form_selectable_cell((strlen($_REQUEST["filter"]) ? eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $host["hostname"]) : $host["hostname"]), $host["id"]);
			form_selectable_cell(round(($host["cur_time"]), 2), $host["id"]);
			form_selectable_cell(round(($host["avg_time"]), 2), $host["id"]);
			form_selectable_cell(round($host["availability"], 2), $host["id"]);
			form_checkbox_cell($host["description"], $host["id"]);
			form_end_row();
		}

		form_end_table();

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td><em>No Hosts</em></td></tr>";
	}

	html_end_box(false);

	/* add a list of tree names to the actions dropdown */
	$device_actions = array_merge($device_actions, api_tree_add_tree_names_to_actions_array());

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($device_actions);

	print "</form>\n";
}

?>
