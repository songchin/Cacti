<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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
require_once(CACTI_BASE_PATH . "/lib/sys/rrd.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_push.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");
require_once(CACTI_BASE_PATH . "/include/data_source/data_source_form.php");
require_once(CACTI_BASE_PATH . "/lib/template.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_form_template.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_post();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item_add':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		ds_edit();

		include_once ("./include/bottom_footer.php");
		break;
	case 'item_remove':
		ds_item_remove();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		ds_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		ds();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    Form Post Handler
   -------------------------- */

function form_post() {
	if ($_POST["action_post"] == "data_source_edit") {
		/* fetch some cache variables */
		if (empty($_POST["id"])) {
			$_data_template_id = 0;
		}else{
			$_data_template_id = db_fetch_cell("select data_template_id from data_source where id = " . $_POST["id"]);
		}

		/* cache all post field values */
		init_post_field_cache();

		$data_source_fields = array();
		$data_source_item_fields = array();
		$data_input_fields = array();

		/* parse out form values that we care about (data source / data source item fields) */
		reset($_POST);
		while (list($name, $value) = each($_POST)) {
			if (substr($name, 0, 4) == "dsi|") {
				$matches = explode("|", $name);
				$data_source_item_fields{$matches[2]}{$matches[1]} = $value;
			}else if (substr($name, 0, 4) == "dif_") {
				$data_input_fields{substr($name, 4)} = $value;
			}else if (substr($name, 0, 3) == "ds|") {
				$matches = explode("|", $name);
				$data_source_fields{$matches[1]} = $value;
			}
		}

		/* add any unchecked checkbox fields */
		$data_source_fields += field_register_html_checkboxes(api_data_source_fields_list(), "ds||field|");

		/* step #2: field validation */
		$suggested_value_fields = array(); /* placeholder */
		field_register_error(api_data_source_validate_fields_base($data_source_fields, $suggested_value_fields, "ds||field|", ""));
		field_register_error(api_data_source_validate_fields_input($data_input_fields, "dif_|field|"));

		foreach ($data_source_item_fields as $data_source_item_id => $data_source_item) {
			field_register_error(api_data_source_validate_fields_item($data_source_item, "dsi||field||$data_source_item_id"));
		}

		/* step #3: field save */
		if (is_error_message()) {
			api_log_log("User input validation error for data source [ID#" . $_POST["id"] . "]", SEV_DEBUG);
		}else{
			/* handle rra_id multi-select */
			if (isset($data_source_fields["rra_id"])) {
				$data_source_rra_fields = $data_source_fields["rra_id"];
				unset($data_source_fields["rra_id"]);
			}else{
				$data_source_rra_fields = array();
			}

			/* save data source data */
			if (api_data_source_save($_POST["id"], $data_source_fields, $data_source_rra_fields)) {
				$data_source_id = (empty($_POST["id"]) ? db_fetch_insert_id() : $_POST["id"]);

				/* save data source input fields */
				if (!api_data_source_fields_save($data_source_id, $data_input_fields)) {
					api_log_log("Save error for data input fields, data source [ID#" . $_POST["id"] . "]", SEV_ERROR);
				}

				/* save data source item data */
				foreach ($data_source_item_fields as $data_source_item_id => $data_source_item) {
					/* required fields */
					$data_source_item_fields[$data_source_item_id]["data_source_id"] = $data_source_id;

					if (!api_data_source_item_save($data_source_item_id, $data_source_item)) {
						api_log_log("Save error for data source item [ID#" . $data_source_item_id . "], data source [ID#" . $_POST["id"] . "]", SEV_ERROR);
					}
				}

			}else{
				api_log_log("Save error for data source [ID#" . $_POST["id"] . "]", SEV_ERROR);
			}
		}

		if ((is_error_message()) || ($_POST["data_template_id"] != $_data_template_id)) {
			if (isset($_POST["redirect_item_add"])) {
				$action = "item_add";
			}else{
				$action = "edit";
			}

			header("Location: data_sources.php?action=$action" . (empty($_POST["id"]) ? "" : "&id=" . $_POST["id"]) . (!isset($_POST["host_id"]) ? "" : "&host_id=" . $_POST["host_id"]) . (!isset($_POST["data_template_id"]) ? "" : "&data_template_id=" . $_POST["data_template_id"]) . (isset($_POST["data_input_type"]) ? "&data_input_type=" . $_POST["data_input_type"] : "") . (isset($_POST["dif_script_id"]) ? "&script_id=" . $_POST["dif_script_id"] : "") . (isset($_POST["dif_data_query_id"]) ? "&data_query_id=" . $_POST["dif_data_query_id"] : ""));
		}else{
			header("Location: data_sources.php");
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "search") {
			$get_string = "";

			if ($_POST["box-1-search_device"] != "-1") {
				$get_string .= ($get_string == "" ? "?" : "&") . "search_device=" . urlencode($_POST["box-1-search_device"]);
			}

			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string .= ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}

			header("Location: data_sources.php$get_string");
		}
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "data_source_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: data_sources.php$get_string");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $ds_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 1; }

			switch ($_POST["delete_type"]) {
				case '2': /* delete all graph items tied to this data source */
					$data_source_items = db_fetch_assoc("select id from data_source_item where " . array_to_sql_or($selected_items, "data_source_id"));

					/* loop through each data source item */
					if (sizeof($data_source_items) > 0) {
						foreach ($data_source_items as $item) {
							db_execute("delete from graph_item where data_source_item_id = " . $item["id"]);
						}
					}

					break;
				case '3': /* delete all graphs tied to this data source */
					$graphs = db_fetch_assoc("select distinct
						graph.id
						from data_source_item,graph_item,graph
						where graph_item.data_source_item_id=data_source_item.id
						and graph_item.graph_id=graph.id
						and " . array_to_sql_or($selected_items, "data_source_item.data_source_id"));

					if (sizeof($graphs) > 0) {
						foreach ($graphs as $graph) {
							api_graph_remove($graph["id"]);
						}
					}

					break;
				}

				for ($i=0;($i<count($selected_items));$i++) {
					api_data_source_remove($selected_items[$i]);
				}
		}elseif ($_POST["drp_action"] == "2") { /* change graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				change_data_template($selected_items[$i], $_POST["data_template_id"]);
			}
		}elseif ($_POST["drp_action"] == "3") { /* change host */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("update data_local set host_id=" . $_POST["host_id"] . " where id=" . $selected_items[$i]);
				push_out_host($_POST["host_id"], $selected_items[$i]);
				api_data_source_title_cache_update($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "4") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_data_source($selected_items[$i], 0, $_POST["title_format"]);
			}
		}elseif ($_POST["drp_action"] == "5") { /* data source -> data template */
			for ($i=0;($i<count($selected_items));$i++) {
				data_source_to_data_template($selected_items[$i], $_POST["title_format"]);
			}
		}elseif ($_POST["drp_action"] == "6") { /* data source enable */
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_source_enable($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "7") { /* data source disable */
			for ($i=0;($i<count($selected_items));$i++) {
				api_data_source_disable($selected_items[$i]);
			}
		}

		header("Location: data_sources.php");
		exit;
	}

	/* setup some variables */
	$ds_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$ds_list .= "<li>" . api_data_source_title($matches[1]) . "<br>";
			$ds_array[$i] = $matches[1];
		}

		$i++;
	}

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $ds_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='data_sources.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		$graphs = array();

		/* find out which (if any) graphs are using this data source, so we can tell the user */
		if (isset($ds_array)) {
			$graphs = db_fetch_assoc("select distinct
				graph.id,
				graph.title_cache
				from data_source_item,graph_item,graph
				where graph_item.data_source_item_id=data_source_item.id
				and graph_item.graph_id=graph.id
				and " . array_to_sql_or($ds_array, "data_source_item.data_source_id") . "
				order by graph.title_cache");
		}

		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Are you sure you want to delete the following data sources?") . "</p>
					<p>$ds_list</p>
					";
					if (sizeof($graphs) > 0) {
						print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td class='textArea'><p class='textArea'>The following graphs are using these data sources:</p>\n";

						foreach ($graphs as $graph) {
							print "<strong>" . $graph["title_cache"] . "</strong><br>\n";
						}

						print "<br>";
						form_radio_button("delete_type", "3", "1", _("Leave the graphs untouched."), "1"); print "<br>";
						form_radio_button("delete_type", "3", "2", _("Delete all <strong>graph items</strong> that reference these data sources."), "1"); print "<br>";
						form_radio_button("delete_type", "3", "3", _("Delete all <strong>graphs</strong> that reference these data sources."), "1"); print "<br>";
						print "</td></tr>";
					}
				print "
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* change graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>"._("Choose a data template and click save to change the data template for
					the following data souces. Be aware that all warnings will be suppressed during the
					conversion, so graph data loss is possible.")."</p>
					<p>$ds_list</p>
					<p><strong>" . _("New Data Template:") . "</strong><br>"; form_dropdown("data_template_id",db_fetch_assoc("select data_template.id,data_template.name from data_template order by data_template.name"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "3") { /* change host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Choose a new host for these data sources:") . "</p>
					<p>$ds_list</p>
					<p><strong>" . _("New Host:") . "</strong><br>"; form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "4") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following data sources will be duplicated. You can
					optionally change the title format for the new data sources.") . "</p>
					<p>$ds_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "5") { /* graph -> graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following data sources will be converted into data templates.
					You can optionally change the title format for the new data templates.") . "</p>
					<p>$ds_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<ds_title> " . _("Template"), "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "6") { /* data source enable */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click yes, the following data sources will be enabled.</p>
					<p>$ds_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "7") { /* data source disable */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click yes, the following data sources will be disabled.</p>
					<p>$ds_list</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($ds_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one data source.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='". html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($ds_array) ? serialize($ds_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='data_sources.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ------------------------
    Data Source Functions
   ------------------------ */

function ds_item_remove() {
	api_data_source_item_remove($_GET["id"]);

	header("Location: data_sources.php?action=edit&id=" . $_GET["data_source_id"]);
}

function ds_edit() {
	global $colors, $data_source_types;

	$host_id = 0;

	if (!empty($_GET["id"])) {
		$data_source = db_fetch_row("select * from data_source where id = " . $_GET["id"]);
		$data_source_items = db_fetch_assoc("select * from data_source_item where data_source_id = " . $_GET["id"]);

		if (!empty($data_source["data_template_id"])) {
			$data_template = db_fetch_row("select id,name from data_template where id='" . $data_source["data_template_id"] . "'");
		}

		$header_label = _("[edit: ") . api_data_source_title($_GET["id"]) . "]";

		/* get a list of all data input type fields for this data template */
		$data_input_type_fields = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = " . $data_source["id"]), "name", array("value"));
	}else{
		$header_label = _("[new]");

		$data_input_type_fields = array();
	}

	/* handle debug mode */
	if (isset($_GET["debug"])) {
		if ($_GET["debug"] == "0") {
			kill_session_var("ds_debug_mode");
		}elseif ($_GET["debug"] == "1") {
			$_SESSION["ds_debug_mode"] = true;
		}
	}

	if (!empty($_GET["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td class="textInfo" colspan="2" valign="top">
					<?php print api_data_source_title($_GET["id"]);?>
				</td>
				<td class="textInfo" align="right" valign="top">
					<span style="color: #c16921;">*<a href='data_sources.php?action=edit&id=<?php print (isset($_GET["id"]) ? $_GET["id"] : 0);?>&debug=<?php print (isset($_SESSION["ds_debug_mode"]) ? "0" : "1");?>'>Turn <strong><?php print (isset($_SESSION["ds_debug_mode"]) ? "Off" : "On");?></strong> Data Source Debug Mode.</a>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	form_start("data_sources.php", "form_data_source");

	/* ==================== Box: Device/Template Selection ==================== */

	html_start_box("<strong>" . _("Device/Template Selection") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");
	_data_source_field__data_template_id("data_template_id", (isset($data_source) ? $data_source["data_template_id"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	_data_source_field__host_id("host_id", (isset($data_source) ? $data_source["host_id"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
	html_end_box();

	/* ==================== Box: Supplemental Template Data ==================== */

	/* only display the "inputs" area if we are using a data template for this data source */
	if (!empty($data_source["data_template_id"])) {
		ob_start();

		html_start_box("<strong>" . _("Supplemental Template Data") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		$num_output_fields =  draw_nontemplated_fields_data_input($data_source["data_template_id"], $data_input_type_fields, "dif_|field|", "<strong>" . _("Data Input") . "</strong>", true);

		if ($data_source["data_input_type"] == DATA_INPUT_TYPE_DATA_QUERY) {
			_data_source_input_field__data_query_hdr();
			_data_source_input_field__data_query_field_name("dif_data_query_field_name", $data_input_type_fields["data_query_id"]["value"], (isset($data_input_type_fields["data_query_field_name"]["value"]) ? $data_input_type_fields["data_query_field_name"]["value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
			_data_source_input_field__data_query_field_value("dif_data_query_field_value", (isset($data_input_type_fields["data_query_field_name"]["value"]) ? $data_input_type_fields["data_query_field_value"]["value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
			$num_output_fields += 2;
		}

		$num_output_fields += draw_nontemplated_fields_data_source($data_source["data_template_id"], $data_source, "ds||field|", "<strong>" . _("Data Source Fields") . "</strong>", true, true);
		$num_output_fields += draw_nontemplated_fields_data_source_item($data_source["data_template_id"], db_fetch_assoc("select * from data_source_item where data_source_id = " . $data_source["id"] . " order by data_source_name"), "dsi||field|||id|", "<strong>" . _("Data Source Item Fields") . "</strong>", true, true);

		html_end_box();

		if ($num_output_fields == 0) {
			ob_end_clean();
		}else{
			ob_end_flush();
		}
	}

	if ( (empty($data_source["data_template_id"])) && ( ((isset($_GET["id"])) && (is_numeric($_GET["id"]))) || ((isset($_GET["host_id"])) && (isset($_GET["data_template_id"]))) ) ) {
		/* determine current value for 'data_input_type' */
		if (isset($_GET["data_input_type"])) {
			$_data_input_type = $_GET["data_input_type"];
		}else if (isset($data_source["data_input_type"])) {
			$_data_input_type = $data_source["data_input_type"];
		}else{
			$_data_input_type = DATA_INPUT_TYPE_SCRIPT;
		}

		/* ==================== Box: Data Input ==================== */

		html_start_box("<strong>" . _("Data Input") . "</strong>", "98%", $colors["header_background_template"], "3", "center", "");

		_data_source_input_field__data_input_type("data_input_type", false, $_data_input_type, (empty($_GET["id"]) ? 0 : $_GET["id"]));

		/* grab the appropriate data input type form array */
		if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
			/* since the "sql" key is not executed until draw_edit_form(), we have fetch the list of
			 * external scripts here as well */
			$scripts = db_fetch_assoc($_data_input_type_form["dif_script_id"]["sql"]);

			if (sizeof($scripts) > 0) {
				/* determine current value for 'script_id' */
				if ((isset($_GET["script_id"])) && (is_numeric($_GET["script_id"]))) {
					$_script_id = $_GET["script_id"];
				}else if (isset($data_input_type_fields["script_id"])) {
					$_script_id = $data_input_type_fields["script_id"]["value"];
				}else{
					/* default to the first item in the script list */
					$_script_id = $scripts[0]["id"];
				}

				field_row_header(_("External Script"));
				_data_source_input_field__script_id("dif_script_id", "data_sources.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=$_data_input_type&script_id=|dropdown_value|", $_script_id);

				/* get each INPUT field for this script */
				$script_input_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='in' order by name");

				if (sizeof($script_input_fields) > 0) {
					field_row_header(_("Custom Input Fields"));

					foreach ($script_input_fields as $field) {
						_data_source_input_field__script("dif_" . $field["data_name"], $field["name"], false, ((isset($data_input_type_fields{$field["data_name"]})) ? $data_input_type_fields{$field["data_name"]}["value"] : ""), "", "", (isset($_GET["id"]) ? $_GET["id"] : 0));
					}
				}
			}
		}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
			/* determine current value for 'data_query_id' */
			if ((isset($_GET["data_query_id"])) && (is_numeric($_GET["data_query_id"]))) {
				$_data_query_id = $_GET["data_query_id"];
			}else if (isset($data_input_type_fields["data_query_id"])) {
				$_data_query_id = $data_input_type_fields["data_query_id"]["value"];
			}else{
				/* default to the first item in the data query list */
				$_data_query_id = $data_queries[0]["id"];
			}

			field_row_header(_("Data Query"));
			_data_source_input_field__data_query_id("dif_data_query_id", "data_sources.php?action=edit" . (!empty($_GET["id"]) ? "&id=" . $_GET["id"] : "") . "&data_template_id=" . (isset($_GET["data_template_id"]) ? $_GET["data_template_id"] : (isset($data_source) ? $data_source["data_template_id"] : 0)) . "&host_id=" . (isset($_GET["host_id"]) ? $_GET["host_id"] : (isset($data_source) ? $data_source["host_id"] : 0)) . "&data_input_type=$_data_input_type&data_query_id=|dropdown_value|", $_data_query_id);
		}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
			_data_source_input_field__device_hdr_generic();
			_data_source_input_field__device_snmp_port("dif_snmp_port", false, (isset($data_input_type_fields["snmp_port"]) ? $data_input_type_fields["snmp_port"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmp_timeout("dif_snmp_timeout", false, (isset($data_input_type_fields["snmp_timeout"]) ? $data_input_type_fields["snmp_timeout"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmp_version("dif_snmp_version", false, (isset($data_input_type_fields["snmp_version"]) ? $data_input_type_fields["snmp_version"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_hdr_snmpv12();
			_data_source_input_field__device_snmp_community("dif_snmp_community", false, (isset($data_input_type_fields["snmp_community"]) ? $data_input_type_fields["snmp_community"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_hdr_snmpv3();
			_data_source_input_field__device_snmpv3_auth_username("dif_snmpv3_auth_username", false, (isset($data_input_type_fields["snmpv3_auth_username"]) ? $data_input_type_fields["snmpv3_auth_username"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmpv3_auth_password("dif_snmpv3_auth_password", false, (isset($data_input_type_fields["snmpv3_auth_password"]) ? $data_input_type_fields["snmpv3_auth_password"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmpv3_auth_protocol("dif_snmpv3_auth_protocol", false, (isset($data_input_type_fields["snmpv3_auth_protocol"]) ? $data_input_type_fields["snmpv3_auth_protocol"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmpv3_priv_passphrase("dif_snmpv3_priv_passphrase", false, (isset($data_input_type_fields["snmpv3_priv_passphrase"]) ? $data_input_type_fields["snmpv3_priv_passphrase"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
			_data_source_input_field__device_snmpv3_priv_protocol("dif_snmpv3_priv_protocol", false, (isset($data_input_type_fields["snmpv3_priv_protocol"]) ? $data_input_type_fields["snmpv3_priv_protocol"]["value"] : ""), (isset($_GET["id"]) ? $_GET["id"] : 0));
		}

		html_end_box();

		/* ==================== Box: Data Source ==================== */

		html_start_box("<strong>" . _("Data Source") . "</strong>", "98%", $colors["header_background"], "3", "center", "");
		_data_source_field__name("ds|name", false, (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_data_source_field__rra_id("ds|rra_id", false, (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_data_source_field__rrd_step("ds|rrd_step", false, (isset($data_source["rrd_step"]) ? $data_source["rrd_step"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_data_source_field__active("ds|active", false, (isset($data_source["active"]) ? $data_source["active"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		html_end_box();

		/* ==================== Box: Data Source Item ==================== */

		html_start_box("<strong>" . _("Data Source Item") . "</strong>", "98%", $colors["header_background"], "3", "center", (empty($_GET["id"]) ? "" : "javascript:document.forms[0].action.value='item_add';submit_redirect(0, '" . htmlspecialchars("data_sources.php?action=item_add&id=" . $_GET["id"]) . "', '')"));

		/* the user clicked the "add item" link. we need to make sure they get redirected back to
		 * this page if an error occurs */
		if ($_GET["action"] == "item_add") {
			form_hidden_box("redirect_item_add", "x", "");
		}

		/* this allows a "blank" data template item to be displayed when the user wants to create
		 * a new one */
		if ((!isset($data_source_items)) || (sizeof($data_source_items) == 0) || ($_GET["action"] == "item_add")) {
			if (isset($data_source_items)) {
				$next_index = sizeof($data_source_items);
			}else{
				$next_index = 0;
			}

			$data_source_items[$next_index] = array();
		}

		if (sizeof($data_source_items) > 0) {
			if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
				$script_output_fields = db_fetch_assoc("select * from data_input_fields where data_input_id = $_script_id and input_output='out' order by name");
				$field_input_description = _("Script Output Field");
			}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
				$field_list = api_data_query_fields_list($_data_query_id, DATA_QUERY_FIELD_TYPE_OUTPUT);

				$data_query_output_fields = array();
				if (sizeof($field_list) > 0) {
					foreach ($field_list as $field) {
						$data_query_output_fields{$field["name"]} = $field["name"] . " (" . $field["name_desc"] . ")";
					}
				}

				$field_input_description = _("Data Query Output Field");
			}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
				$field_input_description = _("SNMP OID");
			}

			foreach ($data_source_items as $item) {
				if ($_data_input_type != DATA_INPUT_TYPE_NONE) {
					?>
					<tr bgcolor="<?php print $colors["header_panel_background"];?>">
						<td class='textSubHeaderDark'>
							<?php print (isset($item["data_source_name"]) ? $item["data_source_name"] : "(" . _("New Data Source Item") . ")");?>
						</td>
						<td class='textSubHeaderDark' align='right'>
							<?php
							if ((isset($item["id"])) && (sizeof($data_source_items) > 1)) {
								print "[<a href='data_sources.php?action=item_remove&id=" . $item["id"] . "&data_source_id=" . $item["data_source_id"] . "' class='linkOverDark'>remove</a>]\n";
							}
							?>
						</td>
					</tr>
					<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
						<td width="50%" style="border-bottom: 1px dashed gray;">
							<font class='textEditTitle'>Field Input: <?php print $field_input_description;?></font><br>
						</td>
						<td style="border-bottom: 1px dashed gray;">
							<?php
							if ($_data_input_type == DATA_INPUT_TYPE_SCRIPT) {
								form_dropdown("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), $script_output_fields, "name", "data_name", (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "");
							}else if ($_data_input_type == DATA_INPUT_TYPE_DATA_QUERY) {
								form_dropdown("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), $data_query_output_fields, "", "", (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "");
							}else if ($_data_input_type == DATA_INPUT_TYPE_SNMP) {
								form_text_box("dsi|field_input_value|" . (isset($item["id"]) ? $item["id"] : "0"), (isset($item["field_input_value"]) ? $item["field_input_value"] : ""), "", "100", 40, "text", 0);
							}
							?>
						</td>
					</tr>
					<?php
				}

				$_field_id = (isset($item["id"]) ? $item["id"] : 0);

				field_reset_row_color();
				field_increment_row_color();
				_data_source_item_field__data_source_name("dsi|data_source_name|$_field_id", false, (isset($item["data_source_name"]) ? $item["data_source_name"] : ""), $_field_id);
				_data_source_item_field__rrd_minimum("dsi|rrd_minimum|$_field_id", false, (isset($item["rrd_minimum"]) ? $item["rrd_minimum"] : ""), $_field_id, "dsi|t_rrd_minimum|$_field_id");
				_data_source_item_field__rrd_maximum("dsi|rrd_maximum|$_field_id", false, (isset($item["rrd_maximum"]) ? $item["rrd_maximum"] : ""), $_field_id, "dsi|t_rrd_maximum|$_field_id");
				_data_source_item_field__data_source_type("dsi|data_source_type|$_field_id", false, (isset($item["data_source_type"]) ? $item["data_source_type"] : ""), $_field_id, "dsi|t_data_source_type|$_field_id");
				_data_source_item_field__rrd_heartbeat("dsi|rrd_heartbeat|$_field_id", false, (isset($item["rrd_heartbeat"]) ? $item["rrd_heartbeat"] : ""), $_field_id, "dsi|t_rrd_heartbeat|$_field_id");
			}
		}

		html_end_box();
	}

	/* display the debug mode box if the user wants it */
	if ((isset($_SESSION["ds_debug_mode"])) && (isset($_GET["id"]))) {
		html_start_box("<strong>Data Source Debug</strong>", "98%", $colors["header_background"], "3", "center", "");
		print "<tr><td bgcolor=#'" . $colors["messagebar_background"] . "' <pre>" . rrdtool_function_create($_GET["id"], true, array()) . "</pre></td></tr>";
		html_end_box();
	}

	if ((isset($_GET["id"])) || ((isset($_GET["host_id"])) && (isset($_GET["data_template_id"])))) {
		form_hidden_box("save_component_data_source", "1", "");
	}else{
		form_hidden_box("save_component_data_source_new", "1", "");
	}

	form_hidden_box("id", (empty($_GET["id"]) ? 0 : $_GET["id"]), "");
	form_hidden_box("action_post", "data_source_edit");
	form_save_button("data_sources.php");
}

function ds() {
	$current_page = get_get_var_number("page", "1");

	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate",
		"enable" => "Enable",
		"disable" => "Disable",
		"change_data_template" => "Change Data Template",
		"change_host" => "Change Host",
		"convert_data_template" => "Convert to Data Template"
		);

	$filter_array = array();

	/* search field: device template */
	if (isset_get_var("search_device")) {
		$filter_array["host_id"] = get_get_var("search_device");
	}

	/* search field: filter (searches data source name) */
	if (isset_get_var("search_filter")) {
		$filter_array["filter"] = array("name_cache|name" => get_get_var("search_filter"));
	}

	/* get a list of all data sources on this page */
	$data_sources = api_data_source_list($filter_array, $current_page, read_config_option("num_rows_data_source"));

	/* get the total number of data sources on all pages */
	$total_rows = api_data_source_total_get($filter_array);

	/* get a list of data input types for display in the data sources list */
	$data_input_types = api_data_source_input_types_list();

	/* generate page list */
	$url_string = build_get_url_string(array("search_device", "search_filter"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_data_source"), $total_rows, "data_sources.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	form_start("data_sources.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Data Sources") . "</strong>", "data_sources.php?action=edit", $url_page_select);
	html_header_checkbox(array(_("Name"), _("Data Input Type"), _("Active"), _("Template Name")), $box_id);

	$i = 0;
	if (sizeof($data_sources) > 0) {
		foreach ($data_sources as $data_source) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $data_source["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $data_source["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $data_source["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $data_source["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $data_source["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $data_source["id"];?>')" href="data_sources.php?action=edit&id=<?php echo $data_source["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $data_source["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $data_source["name_cache"]);?></span></a>
				</td>
				<td class="content-row">
					<?php echo $data_input_types{$data_source["data_input_type"]};?>
				</td>
				<td class="content-row">
					<?php echo (empty($data_source["active"]) ? "<span style='color: red;'>" . _("No") . "</span>" : _("Yes"));?>
				</td>
				<td class="content-row">
					<?php echo ((empty($data_source["data_template_name"])) ? "<em>" . _("None") . "</em>" : $data_source["data_template_name"]);?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $data_source["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $data_source["id"];?>' title="<?php echo $data_source["name_cache"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No data sources found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "4", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_hidden_box("action_post", "data_source_list");
	form_end();

	/* fill in the list of available devices for the search dropdown */
	$search_devices = array();
	$search_devices["-1"] = "Any";
	$search_devices["0"] = "None";
	$search_devices += array_rekey(api_device_list(), "id", "description");

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these data sources?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Data Source');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these data sources?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Data Source');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'search') {
			_elm_dt_input = action_area_generate_select('box-' + box_id + '-search_device');
			<?php echo get_js_dropdown_code('_elm_dt_input', $search_devices, (isset_get_var("search_devices") ? get_get_var("search_devices") : "-1"));?>

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_dt_input, 'Device', true, false));
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
