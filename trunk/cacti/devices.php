<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");
require_once(CACTI_BASE_PATH . "/lib/sys/snmp.php");
require_once(CACTI_BASE_PATH . "/lib/device/device_update.php");
require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_execute.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
require_once(CACTI_BASE_PATH . "/lib/device_template/device_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_post();

		break;
	case 'gt_remove':
		host_remove_gt();

		header("Location: devices.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_remove':
		host_remove_query();

		header("Location: devices.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_reload':
		host_reload_query();

		header("Location: devices.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_verbose':
		host_reload_query();

		header("Location: devices.php?action=edit&id=" . $_GET["host_id"] . "&display_dq_details=true");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		host_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		host();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_post() {
	if ($_POST["action_post"] == "device_edit") {
		/* verify that the snmpv3 passwords and passphrases match */
		if ($_POST["snmpv3_auth_password"] != $_POST["snmpv3_auth_password_confirm"]) {
			raise_message(13);
		}

		if ((!empty($_POST["add_dq_y"])) && (!empty($_POST["data_query_id"]))) {
			db_execute("replace into host_data_query (host_id,data_query_id,reindex_method) values (" . $_POST["id"] . "," . $_POST["data_query_id"] . "," . $_POST["reindex_method"] . ")");

			/* recache snmp data */
			api_data_query_execute($_POST["id"], $_POST["data_query_id"]);

			header("Location: devices.php?action=edit&id=" . $_POST["id"]);
			exit;
		}

		if ((!empty($_POST["add_gt_y"])) && (!empty($_POST["graph_template_id"]))) {
			db_execute("replace into host_graph (host_id,graph_template_id) values (" . $_POST["id"] . "," . $_POST["graph_template_id"] . ")");

			header("Location: devices.php?action=edit&id=" . $_POST["id"]);
			exit;
		}

		if ((isset($_POST["save_component_host"])) && (empty($_POST["add_dq_y"]))) {
			$host_id = api_device_save($_POST["id"], $_POST["poller_id"], $_POST["host_template_id"], $_POST["description"], $_POST["hostname"],
				$_POST["snmp_community"], $_POST["snmp_version"], $_POST["snmpv3_auth_username"], $_POST["snmpv3_auth_password"],
				$_POST["snmpv3_auth_protocol"], $_POST["snmpv3_priv_passphrase"], $_POST["snmpv3_priv_protocol"],
				$_POST["snmp_port"], $_POST["snmp_timeout"], $_POST["availability_method"], $_POST["ping_method"],
				(isset($_POST["disabled"]) ? $_POST["disabled"] : ""));

			if ((is_error_message()) || ($_POST["host_template_id"] != $_POST["_host_template_id"])) {
				header("Location: devices.php?action=edit&id=" . (empty($host_id) ? $_POST["id"] : $host_id));
			}else{
				header("Location: devices.php");
			}
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "search") {
			$get_string = "";

			if ($_POST["box-1-search_device_template"] != "-1") {
				$get_string .= ($get_string == "" ? "?" : "&") . "search_device_template=" . urlencode($_POST["box-1-search_device_template"]);
			}

			if ($_POST["box-1-search_status"] != "-1") {
				$get_string .= ($get_string == "" ? "?" : "&") . "search_status=" . urlencode($_POST["box-1-search_status"]);
			}

			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string .= ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}

			header("Location: devices.php$get_string");
			exit;
		}else if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $host_id) {
				api_device_remove($host_id, ($_POST["box-1-remove_type"] == "2" ? true : false));
			}
		}else if ($_POST["box-1-action-area-type"] == "enable") {
			foreach ($selected_rows as $host_id) {
				api_device_enable($host_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "disable") {
			foreach ($selected_rows as $host_id) {
				api_device_disable($host_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "clear_stats") {
			foreach ($selected_rows as $host_id) {
				api_device_statistics_clear($host_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "change_snmp_opts") {
			// not yet implemented
		}else if ($_POST["box-1-action-area-type"] == "change_avail_opts") {
			// not yet implemented
		}else if ($_POST["box-1-action-area-type"] == "change_poller") {
			// not yet implemented
		}

		header("Location: devices.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "device_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: devices.php$get_string");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $device_actions, $fields_host_edit;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "2") { /* Enable Selected Devices */
			for ($i=0;($i<count($selected_items));$i++) {
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
				db_execute("update host set disabled='on' where id='" . $selected_items[$i] . "'");

				/* update poller cache */
				db_execute("delete from poller_item where host_id='" . $selected_items[$i] . "'");
				db_execute("delete from poller_reindex where host_id='" . $selected_items[$i] . "'");
			}
		}elseif ($_POST["drp_action"] == "4") { /* change snmp options */
			for ($i=0;($i<count($selected_items));$i++) {
				reset($fields_host_edit);
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if (isset($_POST["t_$field_name"])) {
						db_execute("update host set $field_name = '" . $_POST[$field_name] . "' where id='" . $selected_items[$i] . "'");
					}
				}

				push_out_host($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "6") { /* change poller */
			for ($i=0;($i<count($selected_items));$i++) {
				reset($fields_host_edit);
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if (isset($_POST["t_$field_name"])) {
						db_execute("update host set $field_name = '" . $_POST[$field_name] . "' where id='" . $selected_items[$i] . "'");
					}
				}

				push_out_host($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "7") { /* change availability options */
			for ($i=0;($i<count($selected_items));$i++) {
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
				db_execute("update host set min_time = '9.99999', max_time = '0', cur_time = '0',	avg_time = '0',
						total_polls = '0', failed_polls = '0',	availability = '100.00'
						where id = '" . $selected_items[$i] . "'");
			}
		}elseif ($_POST["drp_action"] == "1") { /* delete */
			for ($i=0; $i<count($selected_items); $i++) {
				if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 2; }

				switch ($_POST["delete_type"]) {
					case '1': /* leave graphs and data_sources in place, but disable the data sources */
						$data_sources = db_fetch_assoc("select id from data_source where " . array_to_sql_or($selected_items, "host_id"));

						if (sizeof($data_sources) > 0) {
							foreach ($data_sources as $data_source) {
								api_data_source_disable($data_source["id"]);
							}
						}

						break;
					case '2': /* delete graphs/data sources tied to this device */
						$data_sources = db_fetch_assoc("select id from data_source where " . array_to_sql_or($selected_items, "host_id"));

						if (sizeof($data_sources) > 0) {
							foreach ($data_sources as $data_source) {
								api_data_source_remove($data_source["id"]);
							}
						}

						$graphs = db_fetch_assoc("select id from graph where " . array_to_sql_or($selected_items, "host_id"));

						if (sizeof($graphs) > 0) {
							foreach ($graphs as $graph) {
								api_graph_remove($graph["id"]);
							}
						}

						break;
				}

				api_device_remove($selected_items[$i]);
			}
		}

		header("Location: devices.php");
		exit;
	}

	/* setup some variables */
	$host_list = ""; $i = 0;

	/* loop through each of the host templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$host_list .= "<li>" . db_fetch_cell("select description from host where id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $device_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='devices.php' method='post'>\n";

	if ($_POST["drp_action"] == "2") { /* Enable Devices */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To enable the following devices, press the \"yes\" button below.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "3") { /* Disable Devices */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To disable the following devices, press the \"yes\" button below.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "4") { /* change snmp options */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To change SNMP parameters for the following devices, check the box next to the fields
					you want to update, fill in the new value, and click Save.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
				$form_array = array();
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if ((ereg("^snmp_", $field_name)) || (ereg("^snmpv3_", $field_name))) {
						$form_array += array($field_name => $fields_host_edit[$field_name]);

						$form_array[$field_name]["value"] = "";
						$form_array[$field_name]["description"] = "";
						$form_array[$field_name]["form_id"] = 0;
						$form_array[$field_name]["sub_checkbox"] = array(
							"name" => "t_" . $field_name,
							"friendly_name" => _("Update this Field"),
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
	}elseif ($_POST["drp_action"] == "6") { /* change poller */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To change the poller that will, by default handle the processing for the selected host(s)
					simply select the host from the list, toggle the checkbox and select yes.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
				$form_array = array();
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if (ereg("^poller_", $field_name)) {
						$form_array += array($field_name => $fields_host_edit[$field_name]);

						$form_array[$field_name]["value"] = "";
						$form_array[$field_name]["description"] = "";
						$form_array[$field_name]["form_id"] = 0;
						$form_array[$field_name]["sub_checkbox"] = array(
							"name" => "t_" . $field_name,
							"friendly_name" => _("Update this Field"),
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

	}elseif ($_POST["drp_action"] == "7") { /* change availability options */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To change the availability detection for your hosts will use by default
					simply select the host from the list, make the changes you require and select yes.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
				$form_array = array();
				while (list($field_name, $field_array) = each($fields_host_edit)) {
					if ((ereg("^availability_", $field_name)) || (ereg("^ping_", $field_name))) {
						$form_array += array($field_name => $fields_host_edit[$field_name]);

						$form_array[$field_name]["value"] = "";
						$form_array[$field_name]["description"] = "";
						$form_array[$field_name]["form_id"] = 0;
						$form_array[$field_name]["sub_checkbox"] = array(
							"name" => "t_" . $field_name,
							"friendly_name" => _("Update this Field"),
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
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To clear the counters for the following devices, press the \"yes\" button below.") . "</p>
					<p>$host_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Are you sure you want to delete the following devices?") . "</p>
					<p>$host_list</p>";
					form_radio_button("delete_type", "2", "1", _("Leave all graphs and data sources untouched.  Data sources will be disabled however."), "1"); print "<br>";
					form_radio_button("delete_type", "2", "2", _("Delete all associated <strong>graphs</strong> and <strong>data sources</strong>."), "1"); print "<br>";
					print "</td></tr>
				</td>
			</tr>\n
			";
	}

	if (!isset($host_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one device.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td colspan='2' align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($host_array) ? serialize($host_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='devices.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* -------------------
    Data Query Functions
   ------------------- */

function host_reload_query() {
	api_data_query_execute($_GET["host_id"], $_GET["id"]);
}

function host_remove_query() {
	api_device_dq_remove($_GET["host_id"], $_GET["id"]);
}

function host_remove_gt() {
	api_device_gt_remove($_GET["host_id"], $_GET["id"]);
}

/* ---------------------
    Device Functions
   --------------------- */

function host_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the host <strong>'") . db_fetch_cell("select description from host where id=" . $_GET["id"]) . "'</strong>?", "devices.php", "devices.php?action=remove&id=" . $_GET["id"]);
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_device_remove($_GET["id"]);
	}
}

function host_edit() {
	global $colors, $fields_host_edit, $reindex_types;

	display_output_messages();

	if (!empty($_GET["id"])) {
		$host = db_fetch_row("select * from host where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $host["description"] . "]";
	}else{
		$header_label = _("[new]");
	}

	if (!empty($host["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td class="textInfo" colspan="2">
					<?php print $host["description"];?> (<?php print $host["hostname"];?>)
				</td>
			</tr>
			<tr>
				<td class="textHeader">
					<?php echo _("SNMP Information");?><br>

					<span style="font-size: 10px; font-weight: normal; font-family: monospace;">
					<?php
					if (($host["snmp_community"] == "") && ($host["snmpv3_auth_username"] == "")) {
						print "<span style='color: #ab3f1e; font-weight: bold;'>" . _("SNMP not in use") . "</span>\n";
					}else{
						$snmp_system = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.1.0",
											$host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
											$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
											$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

						if ($snmp_system == "") {
							print "<span style='color: #ff0000; font-weight: bold;'>" . _("SNMP error") . "</span>\n";
						}else{
							$snmp_uptime = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.3.0",
												$host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
												$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
												$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);
							$snmp_hostname = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.5.0",
												$host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
												$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
												$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);
							$snmp_location = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.6.0",
												$host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
												$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
												$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);
							$snmp_contact = cacti_snmp_get($host["hostname"], $host["snmp_community"], ".1.3.6.1.2.1.1.4.0",
												$host["snmp_version"], $host["snmpv3_auth_username"], $host["snmpv3_auth_password"],
												$host["snmpv3_auth_protocol"], $host["snmpv3_priv_passphrase"], $host["snmpv3_priv_protocol"],
												$host["snmp_port"], $host["snmp_timeout"], SNMP_WEBUI);

							print "<strong>System:</strong> $snmp_system<br>\n";
							$days = intval($snmp_uptime / (60*60*24*100));
							$remainder = $snmp_uptime % (60*60*24*100);
							$hours = intval($remainder / (60*60*100));
							$remainder = $remainder % (60*60*100);
							$minutes = intval($remainder / (60*100));
							print "<strong>" . _("Uptime:") . "</strong> $snmp_uptime";
							print "&nbsp;($days days, $hours hours, $minutes minutes)<br>\n";
							print "<strong>" . _("Hostname:") . "</strong> $snmp_hostname<br>\n";
							print "<strong>" . _("Location:") . "</strong> $snmp_location<br>\n";
							print "<strong>" . _("Contact:") . "</strong> $snmp_contact<br>\n";
						}
					}
					?>
					</span>
				</td>
				<td class="textInfo" valign="top">
					<span style="color: #c16921;">*</span><a href="graphs_new.php?host_id=<?php print $host["id"];?>"><?php echo _("Create Graphs for this Host"); ?></a>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	html_start_box("<strong>" . _("Devices") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	/* preserve the host template id if passed in via a GET variable */
	if (!empty($_GET["host_template_id"])) {
		$fields_host_edit["host_template_id"]["value"] = $_GET["host_template_id"];
	}

	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_host_edit, (isset($host) ? $host : array()))
		));

	html_end_box();

	if ((isset($_GET["display_dq_details"])) && (isset($_SESSION["debug_log"]["data_query"]))) {
		html_start_box("<strong>" . _("Data Query Debug Information") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "'><span style='font-family: monospace;'>" . debug_log_return("data_query") . "</span></td></tr>";

		html_end_box();
	}

	if (!empty($host["id"])) {
		html_start_box("<strong>" . _("Associated Graph Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		html_header(array(_("Graph Template Name"), _("Status")), 2);

		$selected_graph_templates = db_fetch_assoc("select
			graph_template.id,
			graph_template.template_name
			from graph_template,host_graph
			where graph_template.id=host_graph.graph_template_id
			and host_graph.host_id = " . $_GET["id"] . "
			order by graph_template.template_name");

		$available_graph_templates = db_fetch_assoc("select
			graph_template.id,
			graph_template.template_name
			from graph_template left join host_graph
			on (host_graph.graph_template_id = graph_template.id)
			where host_graph.graph_template_id is null
			order by graph_template.template_name");

		$i = 0;
		if (sizeof($selected_graph_templates) > 0) {
			foreach ($selected_graph_templates as $item) {
				$i++;

				/* get status information for this graph template */
				$is_being_graphed = (db_fetch_cell("select count(*) from graph where graph_template_id = " . $item["id"] . " and host_id = " . $_GET["id"]) > 0) ? true : false;

				?>
				<tr bgcolor='#<?php print $colors["form_alternate1"];?>'>
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["template_name"];?>
					</td>
					<td>
						<?php print (($is_being_graphed == true) ? "<span style='color: green;'>" . _("Is Being Graphed") . "</span> (<a href='graphs.php?action=graph_edit&id=" . db_fetch_cell("select id from graph_local where graph_template_id=" . $item["id"] . " and host_id=" . $_GET["id"] . " limit 0,1") . "'>" . _("Edit") . "</a>)" : "<span style='color: #484848;'>" . _("Not Being Graphed") . "</span>");?>
					</td>
					<td align='right' nowrap>
						<a href='devices.php?action=gt_remove&id=<?php print $item["id"];?>&host_id=<?php print $_GET["id"];?>'><img src='<?php print html_get_theme_images_path("delete_icon_large.gif");?>' alt='<?php echo _("Delete Graph Template Association");?>' border='0' align='absmiddle'></a>
					</td>
				</tr>
				<?php
			}
		}else{ print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No associated graph templates.") . "</em></td></tr>"; }

		?>
		<tr bgcolor="#<?php print $colors["buttonbar_background"];?>">
			<td colspan="4">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap><?php echo _("Add Graph Template");?>:&nbsp;
						<?php form_dropdown("graph_template_id",$available_graph_templates,"template_name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add');?>" name="add_gt" align="absmiddle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box();

		html_start_box("<strong>" . _("Associated Data Queries") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		html_header(array(_("Data Query Name"), _("Debugging"), _("Re-Index Method"), _("Status")), 2);

		$assigned_data_queries = api_data_query_device_assigned_list($_GET["id"]);

		$i = 0;
		if (sizeof($assigned_data_queries) > 0) {
			foreach ($assigned_data_queries as $item) {
				$i++;

				/* get status information for this data query */
				$num_dq_items = api_data_query_cache_num_items_get($item["id"], $_GET["id"]);
				$num_dq_rows = api_data_query_cache_num_rows_get($item["id"], $_GET["id"]);

				$status = "success";

				?>
				<tr bgcolor='#<?php echo $colors["form_alternate1"];?>'>
					<td style="padding: 4px;">
						<strong><?php echo $i;?>)</strong> <?php echo $item["name"];?>
					</td>
					<td>
						(<a href="devices.php?action=query_verbose&id=<?php echo $item["id"];?>&host_id=<?php echo $_GET["id"];?>"><?php echo _("Verbose Query");?></a>)
					</td>
					<td>
						<?php echo $reindex_types{$item["reindex_method"]};?>
					</td>
					<td>
						<?php echo (($status == "success") ? "<span style='color: green;'>" . _("Success") . "</span>" : "<span style='color: green;'>" . _("Fail") . "</span>");?> [<?php echo $num_dq_items;?> Item<?php echo ($num_dq_items == 1 ? "" : "s");?>, <?php echo $num_dq_rows;?> Row<?php echo ($num_dq_rows == 1 ? "" : "s");?>]
					</td>
					<td align='right' nowrap>
						<a href='devices.php?action=query_reload&id=<?php echo $item["id"];?>&host_id=<?php echo $_GET["id"];?>'><img src='<?php echo html_get_theme_images_path("reload_icon_small.gif");?>' alt='<?php echo _("Reload Data Query");?>' border='0' align='absmiddle'></a>&nbsp;
						<a href='devices.php?action=query_remove&id=<?php echo $item["id"];?>&host_id=<?php echo $_GET["id"];?>'><img src='<?php echo html_get_theme_images_path("delete_icon_large.gif");?>' alt='<?php echo _("Delete Data Query Association");?>' border='0' align='absmiddle'></a>
					</td>
				</tr>
				<?php
			}
		}else{
			print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No associated data queries.") . "</em></td></tr>";
		}

		?>
		<tr bgcolor="#<?php echo $colors["buttonbar_background"];?>">
			<td colspan="5">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap><?php echo _("Add Data Query");?>:&nbsp;
						<?php form_dropdown("data_query_id", api_data_query_device_unassigned_list($_GET["id"]), "name", "id", "", "None", "");?>
					</td>
					<td nowrap><?php echo _("Re-Index Method");?>:&nbsp;
						<?php form_dropdown("reindex_method", $reindex_types, "", "", "1", "", "");?>
					</td>
					<td align="right">
						&nbsp;<input type="image" src="<?php echo html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add');?>" name="add_dq" align="absmiddle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box();
	}

	form_hidden_box("action_post", "device_edit");
	form_save_button("devices.php", "save_device");
}

function host() {
	$current_page = get_get_var_number("page", "1");

	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate",
		"enable" => "Enable",
		"disable" => "Disable",
		"clear_stats" => "Clear Statistics",
		"change_snmp_opts" => "Change SNMP Options",
		"change_avail_opts" => "Change Availability Options",
		"change_poller" => "Change Poller"
		);

	$filter_array = array();

	/* search field: device template */
	if (isset_get_var("search_device_template")) {
		$filter_array["host_template_id"] = get_get_var("search_device_template");
	}

	/* search field: device status */
	if (isset_get_var("search_status")) {
		$filter_array["status"] = get_get_var("search_status");
	}

	/* search field: filter (searches device description and hostname) */
	if (isset_get_var("search_filter")) {
		$filter_array["filter"] = array("hostname" => get_get_var("search_filter"), "description" => get_get_var("search_filter"));
	}

	/* get a list of all devices on this page */
	$devices = api_device_list($filter_array, $current_page, read_config_option("num_rows_device"));

	/* get the total number of devices on all pages */
	$total_rows = api_device_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_device_template", "search_status", "search_filter"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "devices.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	form_start("devices.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Devices") . "</strong>", "devices.php?action=edit", $url_page_select);
	html_header_checkbox(array(_("Description"), _("Status"), _("Hostname"), _("Current (ms)"), _("Average (ms)"), _("Availability")), $box_id);

	$i = 0;
	if (sizeof($devices) > 0) {
		foreach ($devices as $host) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')" href="devices.php?action=edit&id=<?php echo $host["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $host["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $host["description"]);?></span></a>
				</td>
				<td class="content-row">
					<?php echo get_colored_device_status(($host["disabled"] == "on" ? true : false), $host["status"]);?>
				</td>
				<td class="content-row">
					<?php echo html_highlight_words(get_get_var("search_filter"), $host["hostname"]);?>
				</td>
				<td class="content-row">
					<?php echo round($host["cur_time"], 2);?>
				</td>
				<td class="content-row">
					<?php echo round($host["avg_time"], 2);?>
				</td>
				<td class="content-row">
					<?php echo round($host["availability"], 2);?>%
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>' title="<?php echo $host["description"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No devices found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "6", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "device_list");
	form_end();

	/* fill in the list of available device templates for the search dropdown */
	$search_device_templates = array();
	$search_device_templates["-1"] = "Any";
	$search_device_templates["0"] = "None";
	$search_device_templates += array_rekey(api_device_template_list(), "id", "name");

	/* fill in the list of available host status types for the search dropdown */
	$search_host_status_types = array();
	$search_host_status_types["-1"] = "Any";
	$search_host_status_types["-2"] = "Disabled";
	$search_host_status_types += api_device_status_type_list();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these devices?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			parent_div.appendChild(action_area_generate_input('radio', 'box-' + box_id + '-remove_type', '1'));
			parent_div.appendChild(document.createTextNode('Leave all graphs and data sources untouched. Data sources will be disabled however.'));
			parent_div.appendChild(action_area_generate_break());

			_elm_rt_input = action_area_generate_input('radio', 'box-' + box_id + '-remove_type', '2');
			_elm_rt_input.checked = true;
			parent_div.appendChild(_elm_rt_input);
			parent_div.appendChild(document.createTextNode("Delete all associated graphs and data sources."));

			action_area_update_header_caption(box_id, 'Remove Device');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these devices?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Devices');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'enable') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to enable these devices?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Enable Devices');
			action_area_update_submit_caption(box_id, 'Enable');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'disable') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to disable these devices?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Disable Devices');
			action_area_update_submit_caption(box_id, 'Disable');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'clear_stats') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to clear polling statistics for these devices?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Clear Polling Statistics');
			action_area_update_submit_caption(box_id, 'Clear');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'search') {
			_elm_dt_input = action_area_generate_select('box-' + box_id + '-search_device_template');
			<?php echo get_js_dropdown_code('_elm_dt_input', $search_device_templates, (isset_get_var("search_device_template") ? get_get_var("search_device_template") : "-1"));?>

			_elm_ds_input = action_area_generate_select('box-' + box_id + '-search_status');
			<?php echo get_js_dropdown_code('_elm_ds_input', $search_host_status_types, (isset_get_var("search_status") ? get_get_var("search_status") : "-1"));?>

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_dt_input, 'Device Template', true, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_ds_input, 'Device Status', false, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_ht_input, 'Filter', false, true));

			action_area_update_header_caption(box_id, 'Search');
			action_area_update_submit_caption(box_id, 'Search');
		}
	}
	-->
	</script>

	<?php
}

?>