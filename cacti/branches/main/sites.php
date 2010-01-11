<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

define("MAX_DISPLAY_PAGES", 21);

$site_actions = array(
	ACTION_NONE => __("None"),
	1 => __("Delete")
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
	case 'edit':
		include_once("./include/top_header.php");

		site_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		if (isset($_REQUEST["export_sites_x"])) {
			site_export();
		}else{
			include_once("./include/top_header.php");

			site();

			include_once("./include/bottom_footer.php");
		}
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ((isset($_POST["save_component_site"])) && (empty($_POST["add_dq_y"]))) {
		$id = api_site_save($_POST["id"], $_POST["name"], $_POST["alternate_id"], $_POST["address1"],
		get_request_var_post("address2"), get_request_var_post("city"), get_request_var_post("state"), get_request_var_post("postal_code"),
		get_request_var_post("country"), get_request_var_post("notes"));

		if ((is_error_message()) || ($_POST["id"] != $_POST["hidden_id"])) {
			header("Location: sites.php?action=edit&id=" . (empty($id) ? $_POST["id"] : $id));
		}else{
			header("Location: sites.php");
		}
		exit;
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $config, $site_actions, $fields_site_edit;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if (get_request_var_post("drp_action") == "1") { /* delete */
			/* do a referential integrity check */
			if (sizeof($selected_items)) {
			foreach($selected_items as $site_id) {
				/* ================= input validation ================= */
				input_validate_input_number($site_id);
				/* ==================================================== */

				if (sizeof(db_fetch_assoc("SELECT * FROM device WHERE site_id=$site_id LIMIT 1"))) {
					$bad_ids[] = $site_id;
				}else{
					$site_ids[] = $site_id;
				}
			}
			}

			if (isset($bad_ids)) {
				$message = "";
				foreach($bad_ids as $rra_id) {
					$message .= (strlen($message) ? "<br>":"") . "<i>Site " . $rra_id . " is in use and can not be removed</i>\n";
				}

				$_SESSION['sess_message_site_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

				raise_message('site_ref_int');
			}

			if (isset($site_ids)) {
			foreach($site_ids as $id) {
				api_site_remove($id);
			}
			}
		}

		header("Location: sites.php");
		exit;
	}

	/* setup some variables */
	$site_list = ""; $i = 0;

	/* loop through each of the sites selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match("/^chk_([0-9]+)$/", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			$site_info = db_fetch_cell("SELECT name FROM sites WHERE id=" . $matches[1]);
			$site_list .= "<li>" . $site_info . "<br>";
			$site_array[$i] = $matches[1];
		}

		$i++;
	}

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $site_actions{get_request_var_post("drp_action")} . "</strong>", "60", $colors["header_panel"], "3", "center", "");

	print "<form action='sites.php' method='post'>\n";

	if (isset($site_array)) {
		if (get_request_var_post("drp_action") == ACTION_NONE) { /* NONE */
			print "	<tr>
						<td class='textArea'>
							<p>" . __("You did not select a valid action. Please select 'Return' to return to the previous menu.") . "</p>
						</td>
					</tr>\n";
		}elseif (get_request_var_post("drp_action") == "1") { /* delete */
			print "	<tr>
					<td class='textArea'>
						<p>" . __("Are you sure you want to delete the following site(s)?") . "</p>
						<p><ul>$site_list</ul></p>";
						print "</td></tr>
					</td>
				</tr>\n
				";
		}

		print "<div><input type='hidden' name='action' value='actions'></div>";
		print "<div><input type='hidden' name='selected_items' value='" . (isset($site_array) ? serialize($site_array) : '') . "'></div>";
		print "<div><input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'></div>";
	}else{
		print "<tr><td class='textArea'><span class='textError'>" . __("You must select at least one site.") . "</span></td></tr>\n";
	}

	if (!isset($site_array) || get_request_var_post("drp_action") == ACTION_NONE) {
		form_return_button_alt();
	}else{
		form_yesno_button_alt(serialize($site_array), get_request_var_post("drp_action"));
	}

	html_end_box();

	include_once("./include/bottom_footer.php");
}

function site_export() {
	global $colors, $site_actions, $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("id"));
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["detail"])) {
		$_REQUEST["detail"] = sanitize_search_string(get_request_var("detail"));
	}

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

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_sites_current_page", "1");
	load_current_session_value("detail", "sess_sites_detail", "false");
	load_current_session_value("id", "sess_sites_site_id", "-1");
	load_current_session_value("id", "sess_sites_device_template_id", "-1");
	load_current_session_value("filter", "sess_sites_filter", "");
	load_current_session_value("sort_column", "sess_sites_sort_column", "name");
	load_current_session_value("sort_direction", "sess_sites_sort_direction", "ASC");

	$sql_where = "";

	$sites = site_get_site_records($sql_where, "", FALSE);

	if (get_request_var_request("detail") == "false") {
		$xport_array = array();
		array_push($xport_array, '"name","total_devices","total_device_errors",' .
			'"total_macs","total_ips","total_oper_ports",' .
			'"total_user_ports"');

		if (sizeof($sites)) {
			foreach($sites as $site) {
				array_push($xport_array,'"' . $site['name'] . '","' .
				$site['total_devices'] . '","' .
				$site['total_device_errors'] . '","' .
				$site['total_macs'] . '","' .
				$site['total_ips'] . '","' .
				$site['total_oper_ports'] . '","' .
				$site['total_user_ports'] . '"');
			}
		}
	}else{
		$xport_array = array();
		array_push($xport_array, '"name","address1","address2",' .
			'"city","state","postal_code",' .
			'"country"');

		if (sizeof($sites)) {
			foreach($sites as $site) {
				array_push($xport_array,'"' . $site['name'] . '","' .
				$site['address1'] . '","' .
				$site['address2'] . '","' .
				$site['city'] . '","' .
				$site['state'] . '","' .
				$site['postal_code'] . '","' .
				$site['country'] . '"');
			}
		}
	}

	header("Content-type: application/xml");
	header("Content-Disposition: attachment; filename=cacti_site_xport.csv");
	foreach($xport_array as $xport_line) {
		print $xport_line . "\n";
	}
}

function api_site_save($id, $name, $alternate_id, $address1, $address2, $city, $state, $postal_code, $country, $notes) {
	$save["id"]           = $id;
	$save["name"]         = form_input_validate($name,         $_POST["name"],         "", false, 3);
	$save["alternate_id"] = form_input_validate($alternate_id, $_POST["alternate_id"], "", true, 3);
	$save["address1"]     = form_input_validate($address1,     $_POST["address1"],     "", true, 3);
	$save["address2"]     = form_input_validate($address2,     $_POST["address2"],     "", true, 3);
	$save["city"]         = form_input_validate($city,         $_POST["city"],         "", true, 3);
	$save["state"]        = form_input_validate($state,        $_POST["state"],        "", true, 3);
	$save["postal_code"]  = form_input_validate($postal_code,  $_POST["postal_code"],  "", true, 3);
	$save["country"]      = form_input_validate($country,      $_POST["country"],      "", true, 3);
	$save["notes"]        = form_input_validate($notes,        $_POST["notes"],        "", true, 3);

	$id = 0;
	if (!is_error_message()) {
		$id = sql_save($save, "sites", "id");

		if ($id) {
			raise_message(1);
		}else{
			raise_message(2);
		}
	}

	return $id;
}

function api_site_remove($id) {
	$devices = db_fetch_cell("SELECT COUNT(*) FROM device WHERE site_id='" . $id . "'");

	if ($devices == 0) {
		db_execute("DELETE FROM sites WHERE id='" . $id . "'");
	}else{
		$_SESSION["sess_messages"] = __("Some sites not removed as they contain devices!");
	}
}

/* ---------------------
    Site Functions
   --------------------- */

function site_remove() {
	global $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$devices = db_fetch_cell("SELECT COUNT(*) FROM device WHERE site_id='" . $_REQUEST["site_id"] . "'");

	if ($devices == 0) {
		if ((read_config_option("remove_verification") == CHECKED) && (!isset($_GET["confirm"]))) {
			include("./include/top_header.php");
			form_confirm(__("Are You Sure?"), __("Are you sure you want to delete the site") . " <strong>'" . db_fetch_cell("select description from device where id=" . get_request_var("device_id")) . "'</strong>?", "sites.php", "sites.php?action=remove&id=" . get_request_var("id"));
			include("./include/bottom_footer.php");
			exit;
		}

		if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
			api_site_remove(get_request_var("id"));
		}
	}else{
		display_custom_error_message(__("You can not delete this site while there are devices associated with it."));
	}
}

function site_get_site_records(&$sql_where, $rows = 30, $apply_limits = TRUE) {
	/* create SQL where clause */
	$device_type_info = db_fetch_row("SELECT * FROM device_template WHERE id='" . $_REQUEST["device_template_id"] . "'");

	$sql_where = "";

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var_request("filter"))) {
		if (get_request_var_request("detail") == "false") {
			$sql_where = "WHERE (sites.name LIKE '%%" . $_REQUEST["filter"] . "%%')";
		}else{
			$sql_where = "WHERE (device_template.name LIKE '%%" . $_REQUEST["filter"] . "%%' OR " .
				"sites.name LIKE '%%" . get_request_var_request("filter") . "%%')";
		}
	}

	if (sizeof($device_type_info)) {
		if (!strlen($sql_where)) {
			$sql_where = "WHERE (device.device_template_id=" . $device_type_info["id"] . ")";
		}else{
			$sql_where .= " AND (device.device_template_id=" . $device_type_info["id"] . ")";
		}
	}

	if (($_REQUEST["site_id"] != "-1") && ($_REQUEST["detail"])){
		if (!strlen($sql_where)) {
			$sql_where = "WHERE (device.site_id='" . $_REQUEST["site_id"] . "')";
		}else{
			$sql_where .= " AND (device.site_id='" . $_REQUEST["site_id"] . "')";
		}
	}

	if (get_request_var_request("detail") == "false") {
		$query_string = "SELECT *
			FROM sites
			$sql_where
			ORDER BY " . get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction");

		if ($apply_limits) {
			$query_string .= " LIMIT " . ($rows*($_REQUEST["page"]-1)) . "," . $rows;
		}
	}else{
		$query_string ="SELECT sites.id,
			sites.name,
			sites.alternate_id,
			sites.address1,
			sites.address2,
			sites.city,
			sites.state,
			sites.country,
			Count(device_template.id) AS total_devices,
			device_template.name as device_template_name
			FROM (device_template
			RIGHT JOIN device ON (device_template.id=device.device_template_id))
			RIGHT JOIN sites ON (device.site_id=sites.id)
			$sql_where
			GROUP BY sites.name, device_template.name
			ORDER BY " . get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction");

		if ($apply_limits) {
			$query_string .= " LIMIT " . ($rows*($_REQUEST["page"]-1)) . "," . $rows;
		}
	}

	//echo $query_string;

	return db_fetch_assoc($query_string);
}

function site_edit() {
	global $colors, $fields_site_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	display_output_messages();

	if (!empty($_GET["id"])) {
		$site = db_fetch_row("select * from sites where id=" . get_request_var("id"));
		$header_label = "[edit: " . $site["name"] . "]";
	}else{
		$header_label = "[new]";
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='site_edit'>\n";
	html_start_box("<strong>" . __("Site") . "</strong> $header_label", "100", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, 'site_edit');

	draw_edit_form(array(
		"config" => array("form_name" => "chk", "no_form_tag" => true),
		"fields" => inject_form_variables($fields_site_edit, (isset($site) ? $site : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();
	form_hidden_box("id", (isset($site["id"]) ? $site["id"] : "0"), "");
	form_hidden_box("hidden_id", (isset($site["hidden_id"]) ? $site["hidden_id"] : "0"), "");
	form_hidden_box("save_component_site", "1", "");

	form_save_button_alt();
}

function site_filter() {
	global $item_rows, $colors;

	?>
	<script type="text/javascript">
	<!--
	function applySiteFilterChange(objForm) {
		strURL = '?report=sites';
		if (objForm.hidden_device_template_id) {
			strURL = strURL + '&device_template_id=-1';
			strURL = strURL + '&site_id=-1';
		}else{
			strURL = strURL + '&device_template_id=' + objForm.device_template_id.value;
			strURL = strURL + '&site_id=' + objForm.site_id.value;
		}
		strURL = strURL + '&detail=' + objForm.detail.checked;
		strURL = strURL + '&filter=' + objForm.filter.value;
		strURL = strURL + '&rows=' + objForm.rows.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php html_start_box("<strong>" . __("Site Filters") . "</strong>", "100", $colors["header"], "3", "center", "sites.php?action=edit", true);?>
	<tr class='rowAlternate2'>
		<td>
			<form method='get' action='<?php print basename($_SERVER["PHP_SELF"]);?>' name='site_edit'>
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td class="w1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Rows:");?>&nbsp;
					</td>
					<td class="w1">
						<select name="rows" onChange="applySiteFilterChange(document.site_edit)">
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
					<td>
						&nbsp;<input type="checkbox" id="detail" name="detail" <?php if ((get_request_var_request("detail") == "true") || (get_request_var_request("detail") == CHECKED)) print ' checked="true"';?> onClick="applySiteFilterChange(document.form_sites)">
					</td>
					<td>
						<label for="detail"><?php print __("Show Device Details");?></label>
					</td>
					<td class="nw120">
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			<?php
			if (!(get_request_var_request("detail") == "false")) { ?>
			</table>
			<table cellpadding="0" cellspacing="3">
				<tr>
					<td class="nw50">
						&nbsp;<?php print __("Site:");?>
					</td>
					<td class="w1">
						<select name="site_id" onChange="applySiteFilterChange(document.form_sites)">
						<option value="-1"<?php if (get_request_var_request("site_id") == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
						<?php
						$sites = db_fetch_assoc("SELECT * FROM sites ORDER BY sites.name");
						if (sizeof($sites) > 0) {
						foreach ($sites as $site) {
							print '<option value="' . $site["id"] . '"'; if (get_request_var_request("site_id") == $site["id"]) { print " selected"; } print ">" . $site["name"] . "</option>";
						}
						}
						?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="70">
						&nbsp;<?php print __("Device Template:");?>
					</td>
					<td class="w1">
						<select name="device_template_id" onChange="applySiteFilterChange(document.form_sites)">
						<option value="-1"<?php if (get_request_var_request("device_template_id") == "-1") {?> selected<?php }?>><?php print __("Any");?></option>
						<?php
						$device_templates = db_fetch_assoc("SELECT DISTINCT device_template.id,
							device_template.name
							FROM device_template
							INNER JOIN device ON (device_template.id = device.device_template_id)
							ORDER BY device_template.name");
						if (sizeof($device_templates) > 0) {
						foreach ($device_templates as $device_template) {
							print '<option value="' . $device_template["id"] . '"'; if (get_request_var_request("device_template_id") == $device_template["id"]) { print " selected"; } print ">" . $device_template["name"] . "</option>";
						}
						}
						?>
						</select>
					</td>
				</tr>
			<?php }?>
			</table>
			<div>
				<input type='hidden' name='page' value='1'>
				<input type='hidden' name='report' value='sites'>
				<?php
				if (get_request_var_request("detail") == "false") { ?>
				<input type='hidden' name='hidden_device_template_id' value='-1'>
				<?php }?>
			</div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);
}

function site() {
	global $colors, $site_actions, $config;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("site_id"));
	input_validate_input_number(get_request_var_request("device_template_id"));
	input_validate_input_number(get_request_var_request("page"));
	input_validate_input_number(get_request_var_request("rows"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["detail"])) {
		$_REQUEST["detail"] = sanitize_search_string(get_request_var("detail"));
	}

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
		kill_session_var("sess_sites_current_page");
		kill_session_var("sess_sites_detail");
		kill_session_var("sess_sites_site_id");
		kill_session_var("sess_sites_device_template_id");
		kill_session_var("sess_sites_filter");
		kill_session_var("sess_sites_rows");
		kill_session_var("sess_sites_sort_column");
		kill_session_var("sess_sites_sort_direction");

		$_REQUEST["page"] = 1;
		unset($_REQUEST["filter"]);
		unset($_REQUEST["rows"]);
		unset($_REQUEST["site_id"]);
		unset($_REQUEST["device_template_id"]);
		unset($_REQUEST["detail"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}else{
		/* if any of the settings changed, reset the page number */
		$changed = 0;
		$changed += check_changed("site_id", "sess_sites_site_id");
		$changed += check_changed("device_template_id", "sess_sites_device_template_id");
		$changed += check_changed("filter", "sess_sites_filter");
		$changed += check_changed("rows", "sess_sites_rows");
		$changed += check_changed("detail", "sess_sites_detail");
		if ($changed) {
			$_REQUEST["page"] = "1";
		}
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_sites_current_page", "1");
	load_current_session_value("rows", "sess_sites_rows", "-1");
	load_current_session_value("detail", "sess_sites_detail", "false");
	load_current_session_value("site_id", "sess_sites_site_id", "-1");
	load_current_session_value("device_template_id", "sess_sites_device_template_id", "-1");
	load_current_session_value("filter", "sess_sites_filter", "");
	load_current_session_value("rows", "sess_sites_rows", read_config_option("num_rows_devices"));
	load_current_session_value("sort_column", "sess_sites_sort_column", "name");
	load_current_session_value("sort_direction", "sess_sites_sort_direction", "ASC");

	site_filter();

	html_start_box("", "100", $colors["header"], "0", "center", "");

	$sql_where = "";

	if (get_request_var_request("rows") == "-1") {
		$rows = read_config_option("num_rows_device");
	}else{
		$rows = get_request_var_request("rows");
	}

	$sites = site_get_site_records($sql_where, $rows);

	if (get_request_var_request("detail") == "false") {
		$total_rows = db_fetch_cell("SELECT
			COUNT(sites.id)
			FROM sites
			$sql_where");
	}else{
		$total_rows = sizeof(db_fetch_assoc("SELECT
			device_template.id, sites.name
			FROM (device_template
			RIGHT JOIN device ON (device_template.id=device.device_template_id))
			RIGHT JOIN sites ON (device.site_id=sites.id)
			$sql_where
			GROUP BY sites.name, device_template.id"));
	}

	/* generate page list */
	$url_page_select = str_replace("&page", "?page", get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $rows, $total_rows, "sites.php"));

	if (get_request_var_request("detail") == "false") {
		/* generate page list navigation */
		$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 6, "sites.php");

		print $nav;
		html_end_box(false);

		$display_text = array(
			"name" => array(__("Site Name"), "ASC"),
			"address1" => array(__("Address"), "ASC"),
			"city" => array(__("City"), "ASC"),
			"state" => array(__("State"), "DESC"),
			"country" => array(__("Country"), "DESC"));

		html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

		if (sizeof($sites) > 0) {
			foreach ($sites as $site) {
				form_alternate_row_color('line' . $site["id"], true);
				form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("sites.php?action=edit&id=" . $site["id"]) . "'>" .
					(strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $site["name"]) : $site["name"]) . "</a>", $site["id"], "20%");
				form_selectable_cell($site["address1"], $site["id"]);
				form_selectable_cell($site["city"], $site["id"]);
				form_selectable_cell($site["state"], $site["id"]);
				form_selectable_cell($site["country"], $site["id"]);
				form_checkbox_cell($site["name"], $site["id"]);
				form_end_row();
			}

			form_end_table();

			/* put the nav bar on the bottom as well */
			print $nav;
		}else{
			print "<tr><td><em>" . __("No Sites") . "</em></td></tr>";
		}
		print "</table>\n";	# end table of html_header_sort_checkbox
	}else{
		$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 10, "sites.php");

		print $nav;
		html_end_box(false);

		$display_text = array(
			"name" => array(__("Site Name"), "ASC"),
			"device_template_name" => array(__("Device Type"), "ASC"),
			"total_devices" => array(__("Devices"), "DESC"),
			"address1" => array(__("Address"), "ASC"),
			"city" => array(__("City"), "ASC"),
			"state" => array(__("State"), "DESC"),
			"country" => array(__("Country"), "DESC"));

		html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

		$i = 0;
		if (sizeof($sites) > 0) {
			foreach ($sites as $site) {
				form_alternate_row_color($site["id"], true); $i++;
				form_selectable_cell("<a class='linkEditMain' href='sites.php?action=edit&id=" . $site["id"] . "'>" .
					(strlen($_REQUEST["filter"]) ? preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $site["name"]) : $site["name"]) . "</a>", $site["id"], "20%");
				form_selectable_cell(preg_replace("/(" . preg_quote($_REQUEST["filter"]) . ")/i", "<span class=\"filter\">\\1</span>", $site["device_template_name"]), $site["id"]);
				form_selectable_cell($site["total_devices"], $site["id"]);
				form_selectable_cell($site["address1"], $site["id"]);
				form_selectable_cell($site["city"], $site["id"]);
				form_selectable_cell($site["state"], $site["id"]);
				form_selectable_cell($site["country"], $site["id"]);
				form_checkbox_cell($site["name"], $site["id"]);
				form_end_row();
			}

			/* put the nav bar on the bottom as well */
			print $nav;
		}else{
			print "<tr><td><em>" . __("No Sites") . "</em></td></tr>";
		}
		print "</table>\n";	# end table of html_header_sort_checkbox
	}

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($site_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
