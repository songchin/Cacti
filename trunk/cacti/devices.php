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
require_once(CACTI_BASE_PATH . "/lib/device/device_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_execute.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
require_once(CACTI_BASE_PATH . "/lib/device_template/device_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_post();

		break;
	case 'remove_package':
		host_remove_package();

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
		/* the "Add" assigned package button was pressed */
		if (isset($_POST["assoc_package_add_y"])) {
			api_device_package_add($_POST["id"], $_POST["assoc_package_id"]);
			header("Location: devices.php?action=edit&id=" . $_POST["id"]);
			exit;
		}

		/* cache all post field values */
		init_post_field_cache();

		/* field validation */
		$form_device["id"] = $_POST["id"];
		$form_device["description"] = $_POST["description"];
		$form_device["hostname"] = $_POST["hostname"];
		$form_device["host_template_id"] = $_POST["host_template_id"];
		$form_device["poller_id"] = $_POST["poller_id"];
		$form_device["disabled"] = html_boolean(isset($_POST["disabled"]) ? $_POST["disabled"] : "");
		$form_device["snmp_version"] = $_POST["snmp_version"];
		$form_device["snmp_community"] = $_POST["snmp_community"];
		$form_device["snmp_port"] = $_POST["snmp_port"];
		$form_device["snmp_timeout"] = $_POST["snmp_timeout"];
		$form_device["snmpv3_auth_username"] = $_POST["snmpv3_auth_username"];
		$form_device["snmpv3_auth_password"] = $_POST["snmpv3_auth_password"];
		$form_device["snmpv3_auth_protocol"] = $_POST["snmpv3_auth_protocol"];
		$form_device["snmpv3_priv_passphrase"] = $_POST["snmpv3_priv_passphrase"];
		$form_device["snmpv3_priv_protocol"] = $_POST["snmpv3_priv_protocol"];

		field_register_error(api_device_field_validate($form_device, "|field|"));

		/* field save */
		$device_id = false;
		if (is_error_message()) {
			api_log_log("User input validation error for device [ID#" . $_POST["id"] . "]", SEV_DEBUG);
		}else{
			$device_id = api_device_save($_POST["id"], $form_device);

			if ($device_id === false) {
				api_log_log("Save error for device [ID#" . $_POST["id"] . "]", SEV_ERROR);
			}
		}

		if (($device_id === false) || (empty($_POST["id"]))) {
			header("Location: devices.php?action=edit" . (empty($_POST["id"]) ? "" : "&id=" . $_POST["id"]));
		}else{
			header("Location: devices.php");
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

function host_reload_query() {
	api_data_query_execute($_GET["host_id"], $_GET["id"]);
}

function host_remove_package() {
	api_device_package_remove($_GET["id"], $_GET["package_id"]);

	header("Location: devices.php?action=edit&id=" . $_GET["id"]);
}

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
	$_device_id = get_get_var_number("id");

	if (empty($_device_id)) {
		$header_label = "[new]";
	}else{
		$device = api_device_get($_device_id);

		/* get a list of each package that is associated with this device */
		$packages = api_device_package_list($_device_id);

		$header_label = "[edit: " . $device["description"] . "]";
	}

	if (!empty($device["id"])) {
		echo "<div>\n";
		echo $device["description"] . " (" . $device["hostname"] . ")<br />\n";
		echo _("SNMP Information") . "\n";

		if (($device["snmp_community"] == "") && ($device["snmpv3_auth_username"] == "")) {
			echo "<span style='color: #ab3f1e; font-weight: bold;'>" . _("SNMP not in use") . "</span>\n";
		}else{
			$snmp_system = cacti_snmp_get($device["hostname"], $device["snmp_community"], ".1.3.6.1.2.1.1.1.0",
				$device["snmp_version"], $device["snmpv3_auth_username"], $device["snmpv3_auth_password"],
				$device["snmpv3_auth_protocol"], $device["snmpv3_priv_passphrase"], $device["snmpv3_priv_protocol"],
				$device["snmp_port"], $device["snmp_timeout"], SNMP_WEBUI);

			if ($snmp_system == "") {
				echo "<span style='color: #ff0000; font-weight: bold;'>" . _("SNMP error") . "</span>\n";
			}else{
				$snmp_uptime = cacti_snmp_get($device["hostname"], $device["snmp_community"], ".1.3.6.1.2.1.1.3.0",
					$device["snmp_version"], $device["snmpv3_auth_username"], $device["snmpv3_auth_password"],
					$device["snmpv3_auth_protocol"], $device["snmpv3_priv_passphrase"], $device["snmpv3_priv_protocol"],
					$device["snmp_port"], $device["snmp_timeout"], SNMP_WEBUI);
				$snmp_hostname = cacti_snmp_get($device["hostname"], $device["snmp_community"], ".1.3.6.1.2.1.1.5.0",
					$device["snmp_version"], $device["snmpv3_auth_username"], $device["snmpv3_auth_password"],
					$device["snmpv3_auth_protocol"], $device["snmpv3_priv_passphrase"], $device["snmpv3_priv_protocol"],
					$device["snmp_port"], $device["snmp_timeout"], SNMP_WEBUI);
				$snmp_location = cacti_snmp_get($device["hostname"], $device["snmp_community"], ".1.3.6.1.2.1.1.6.0",
					$device["snmp_version"], $device["snmpv3_auth_username"], $device["snmpv3_auth_password"],
					$device["snmpv3_auth_protocol"], $device["snmpv3_priv_passphrase"], $device["snmpv3_priv_protocol"],
					$device["snmp_port"], $device["snmp_timeout"], SNMP_WEBUI);
				$snmp_contact = cacti_snmp_get($device["hostname"], $device["snmp_community"], ".1.3.6.1.2.1.1.4.0",
					$device["snmp_version"], $device["snmpv3_auth_username"], $device["snmpv3_auth_password"],
					$device["snmpv3_auth_protocol"], $device["snmpv3_priv_passphrase"], $device["snmpv3_priv_protocol"],
					$device["snmp_port"], $device["snmp_timeout"], SNMP_WEBUI);

				echo "<strong>System:</strong> $snmp_system<br>\n";

				$days = intval($snmp_uptime / (60*60*24*100));
				$remainder = $snmp_uptime % (60*60*24*100);
				$hours = intval($remainder / (60*60*100));
				$remainder = $remainder % (60*60*100);
				$minutes = intval($remainder / (60*100));

				echo "<strong>" . _("Uptime:") . "</strong> $snmp_uptime";
				echo "&nbsp;($days days, $hours hours, $minutes minutes)<br>\n";
				echo "<strong>" . _("Hostname:") . "</strong> $snmp_hostname<br>\n";
				echo "<strong>" . _("Location:") . "</strong> $snmp_location<br>\n";
				echo "<strong>" . _("Contact:") . "</strong> $snmp_contact<br>\n";
			}
		}
		?>
		</span>
		</div>
		<div>
			<span style="color: #c16921;">*</span><a href="graphs_new.php?host_id=<?php print $host["id"];?>"><?php echo _("Create Graphs for this Host"); ?></a>
		</div>
		<br />
		<?php
	}

	form_start("devices.php", "form_device");

	html_start_box("<strong>" . _("Devices") . "</strong> $header_label");

	_device_field__description("description", (isset($device["description"]) ? $device["description"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__hostname("hostname", (isset($device["hostname"]) ? $device["hostname"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__host_template_id("host_template_id", (isset($device["host_template_id"]) ? $device["host_template_id"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__poller_id("poller_id", (isset($device["poller_id"]) ? $device["poller_id"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__disabled("disabled", (isset($device["disabled"]) ? $device["disabled"] : ""), (isset($device["id"]) ? $device["id"] : "0"));

	echo ui_html_box_heading_make("SNMP Options");

	_device_field__snmp_version("snmp_version", (isset($device["snmp_version"]) ? $device["snmp_version"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmp_community("snmp_community", (isset($device["snmp_community"]) ? $device["snmp_community"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmp_port("snmp_port", (isset($device["snmp_port"]) ? $device["snmp_port"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmp_timeout("snmp_timeout", (isset($device["snmp_timeout"]) ? $device["snmp_timeout"] : ""), (isset($device["id"]) ? $device["id"] : "0"));

	echo ui_html_box_heading_make("SNMPv3 Authentication");

	_device_field__snmpv3_auth_username("snmpv3_auth_username", (isset($device["snmpv3_auth_username"]) ? $device["snmpv3_auth_username"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmpv3_auth_password("snmpv3_auth_password", (isset($device["snmpv3_auth_password"]) ? $device["snmpv3_auth_password"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmpv3_auth_protocol("snmpv3_auth_protocol", (isset($device["snmpv3_auth_protocol"]) ? $device["snmpv3_auth_protocol"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmpv3_priv_passphrase("snmpv3_priv_passphrase", (isset($device["snmpv3_priv_passphrase"]) ? $device["snmpv3_priv_passphrase"] : ""), (isset($device["id"]) ? $device["id"] : "0"));
	_device_field__snmpv3_priv_protocol("snmpv3_priv_protocol", (isset($device["snmpv3_priv_protocol"]) ? $device["snmpv3_priv_protocol"] : ""), (isset($device["id"]) ? $device["id"] : "0"));

	html_end_box();

	if ((isset($_GET["display_dq_details"])) && (isset($_SESSION["debug_log"]["data_query"]))) {
		html_start_box("<strong>" . _("Data Query Debug Information") . "</strong>");

		echo "<tr><td><span style='font-family: monospace;'>" . debug_log_return("data_query") . "</span></td></tr>";

		html_end_box();
	}

	if (!empty($_device_id)) {
		html_start_box("<strong>" . _("Assigned Packages") . "</strong>");
		html_header(array(_("Package Name")), 2);

		if (sizeof($packages) > 0) {
			foreach ($packages as $package) {
				?>
				<tr class="item">
					<td style="padding: 4px;">
						<?php echo $package["name"];?>
					</td>
					<td align="right" style="padding: 4px;">
						<a href="devices.php?action=remove_package&id=<?php echo $_device_id;?>&package_id=<?php echo $package["id"];?>"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Package Assignment");?>" border="0" align="absmiddle"></a>
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr class="empty">
				<td colspan="2">
					No packages have been assigned to this device.
				</td>
			</tr>
			<?php
		}

		?>
		<tr>
			<td style="border-top: 1px solid #b5b5b5; padding: 1px;" colspan="2">
				<table width="100%" cellpadding="2" cellspacing="0">
					<tr>
						<td>
							Add package:
							<?php form_dropdown("assoc_package_id", api_package_list(), "name", "id", "", "", "");?>
						</td>
						<td align="right">
							&nbsp;<input type="image" src="<?php echo html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add');?>" name="assoc_package_add" align="absmiddle">
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<?php

		html_end_box();
	}

	form_hidden_box("id", $_device_id);
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
		$filter_array["=host_template_id"] = get_get_var("search_device_template");
	}

	/* search field: device status */
	if (isset_get_var("search_status")) {
		$filter_array["=status"] = get_get_var("search_status");
	}

	/* search field: filter (searches device description and hostname) */
	if (isset_get_var("search_filter")) {
		$filter_array["%filter"] = array("hostname" => get_get_var("search_filter"), "description" => get_get_var("search_filter"));
	}

	/* get a list of all devices on this page */
	$devices = api_device_list($filter_array, "description", "asc", (read_config_option("num_rows_device") * ($current_page - 1)), read_config_option("num_rows_device"));

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
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $host["id"];?>')" href="devices.php?action=edit&id=<?php echo $host["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $host["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $host["description"]);?></span></a>
				</td>
				<td>
					<?php echo get_colored_device_status(($host["disabled"] == "on" ? true : false), $host["status"]);?>
				</td>
				<td>
					<?php echo html_highlight_words(get_get_var("search_filter"), $host["hostname"]);?>
				</td>
				<td>
					<?php echo round($host["cur_time"], 2);?>
				</td>
				<td>
					<?php echo round($host["avg_time"], 2);?>
				</td>
				<td>
					<?php echo round($host["availability"], 2);?>%
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $host["id"];?>' title="<?php echo $host["description"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr class="empty">
			<td colspan="6">
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