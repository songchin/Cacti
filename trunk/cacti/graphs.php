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
require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_push.php");
require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");
require_once(CACTI_BASE_PATH . "/include/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/api_tree.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
require_once(CACTI_BASE_PATH . "/lib/template.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_form_template.php");

define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		item();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'graph_remove':
		graph_remove();

		header("Location: graphs.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		graph_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		graph();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "graph_edit") {
		/* fetch some cache variables */
		if (empty($_POST["id"])) {
			$_graph_template_id = 0;
		}else{
			$_graph_template_id = db_fetch_cell("select graph_template_id from graph where id=" . $_POST["id"]);
		}

		/* cache all post field values */
		init_post_field_cache();

		$form_graph_fields = array();
		$form_graph_item_fields = array();

		/* parse out form values that we care about */
		reset($_POST);
		while (list($name, $value) = each($_POST)) {
			if (substr($name, 0, 2) == "g|") {
				$matches = explode("|", $name);
				$form_graph_fields{$matches[1]} = $value;
			}else if (substr($name, 0, 4) == "gip|") {
				$matches = explode("|", $name);
				$form_graph_item_fields{$matches[2]} = $value;
			}
		}

		/* make a list of fields to save */
		while (list($_field_name, $_field_value) = each($form_graph_fields)) {
			/* make sure that we know about this field */
			if (isset($fields_graph[$_field_name])) {
				$save_graph[$_field_name] = $_field_value;
			}
		}

		/* add any unchecked checkbox fields */
		$form_graph_fields += field_register_html_checkboxes(get_graph_field_list(), "g||field|");

		$form_graph_fields["host_id"] = $_POST["host_id"];
		$form_graph_fields["graph_template_id"] = $_POST["graph_template_id"];

		/* step #2: field validation */
		$suggested_value_fields = array(); /* placeholder */
		field_register_error(api_graph_fields_validate($form_graph_fields, $suggested_value_fields, "g||field|", ""));

		/* step #3: field save */
		if (is_error_message()) {
			api_log_log("User input validation error for graph [ID#" . $_POST["id"] . "]", SEV_DEBUG);
		}else{
			/* save graph data */
			if (!api_graph_save($_POST["id"], $form_graph_fields)) {
				api_log_log("Save error for graph [ID#" . $_POST["id"] . "]", SEV_ERROR);
			}

			/* save graph item data for templated graphs */
			if (!empty($_graph_template_id)) {
				if (sizeof($form_graph_item_fields) > 0) {
					foreach ($form_graph_item_fields as $graph_template_item_input_id => $value) {
						if (!api_graph_template_item_input_propagate($graph_template_item_input_id, $value)) {
							api_log_log("Save error when propagating graph item input [ID#$graph_template_item_input_id] to graph [ID#" . $_POST["id"] . "]", SEV_ERROR);
						}
					}
				}
			}
		}

		if ((is_error_message()) || ($_POST["graph_template_id"] != $_graph_template_id)) {
			header("Location: graphs.php?action=edit&id=" . $_POST["id"] . (!isset($_POST["host_id"]) ? "" : "&host_id=" . $_POST["host_id"]) . (!isset($_POST["graph_template_id"]) ? "" : "&graph_template_id=" . $_POST["graph_template_id"]));
		}else{
			header("Location: graphs.php");
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

			header("Location: graphs.php$get_string");
		}else if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $graph_id) {
				api_graph_remove($graph_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "duplicate") {
			// not yet coded
		}else if ($_POST["box-1-action-area-type"] == "change_graph_template") {
			// note yet coded
		}else if ($_POST["box-1-action-area-type"] == "change_host") {
			foreach ($selected_rows as $graph_id) {
				api_graph_host_update($graph_id, $_POST["box-1-change_device"]);
			}
		}else if ($_POST["box-1-action-area-type"] == "convert_graph_template") {
			// note yet coded
		}else if ($_POST["box-1-action-area-type"] == "place_tree") {
			// note yet coded
		}

		header("Location: graphs.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "graph_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: graphs.php$get_string");
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $graph_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			for ($i=0;($i<count($selected_items));$i++) {
				if (!isset($_POST["delete_type"])) { $_POST["delete_type"] = 1; }

				switch ($_POST["delete_type"]) {
					case '2': /* delete all data sources referenced by this graph */
						$data_sources = db_fetch_assoc("select distinct
							data_source.id
							from data_source_item,data_source,graph_item
							where graph_item.data_source_item_id=data_source_item.id
							and data_source_item.data_source_id=data_source.id
							and " . array_to_sql_or($selected_items, "graph_item.graph_id") . "
							order by data_source.name_cache");

						if (sizeof($data_sources) > 0) {
							foreach ($data_sources as $data_source) {
								api_data_source_remove($data_source["id"]);
							}
						}

						break;
				}

				api_graph_remove($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "2") { /* change graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				change_graph_template($selected_items[$i], $_POST["graph_template_id"], true);
			}
		}elseif ($_POST["drp_action"] == "3") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_graph($selected_items[$i], 0, $_POST["title_format"]);
			}
		}elseif ($_POST["drp_action"] == "4") { /* graph -> graph template */
			for ($i=0;($i<count($selected_items));$i++) {
				graph_to_graph_template($selected_items[$i], $_POST["title_format"]);
			}
		}elseif (ereg("^tr_([0-9]+)$", $_POST["drp_action"], $matches)) { /* place on tree */
			for ($i=0;($i<count($selected_items));$i++) {
				api_tree_item_save(0, $_POST["tree_id"], TREE_ITEM_TYPE_GRAPH, $_POST["tree_item_id"], "", $selected_items[$i], read_graph_config_option("default_rra_id"), 0, 0, 0, false);
			}
		}elseif ($_POST["drp_action"] == "5") { /* change host */
			for ($i=0;($i<count($selected_items));$i++) {
				db_execute("update graph set host_id = " . $_POST["host_id"] . " where id = " . $selected_items[$i]);
				api_graph_title_cache_update($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "6") { /* reapply suggested naming */
			for ($i=0;($i<count($selected_items));$i++) {
				api_reapply_suggested_graph_title($selected_items[$i]);
				api_graph_title_cache_update($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "7") { /* resize graphs */
			for ($i=0;($i<count($selected_items));$i++) {
				api_graph_resize($selected_items[$i], $_POST["graph_width"], $_POST["graph_height"]);
			}
		}

		header("Location: graphs.php");
		exit;
	}

	/* setup some variables */
	$graph_list = ""; $i = 0;

	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$graph_list .= "<li>" . db_fetch_cell("select title_cache from graph where id = " . $matches[1]) . "<br>";
			$graph_array[$i] = $matches[1];
		}

		$i++;
	}

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $graph_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='graphs.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		$graphs = array();

		/* find out which (if any) data sources are being used by this graph, so we can tell the user */
		if (isset($graph_array)) {
			$data_sources = db_fetch_assoc("select distinct
				data_source.id,
				data_source.name_cache
				from data_source_item,data_source,graph_item
				where graph_item.data_source_item_id=data_source_item.id
				and data_source_item.data_source_id=data_source.id
				and " . array_to_sql_or($graph_array, "graph_item.graph_id") . "
				order by data_source.name_cache");
		}

		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Are you sure you want to delete the following graphs?") . "</p>
					<p>$graph_list</p>
					";
					if (sizeof($data_sources) > 0) {
						print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td class='textArea'><p class='textArea'>" . _("The following data sources are in use by these graphs:") . "</p>\n";

						foreach ($data_sources as $data_source) {
							print "<strong>" . $data_source["name_cache"] . "</strong><br>\n";
						}

						print "<br>";
						form_radio_button("delete_type", "1", "1", _("Leave the data sources untouched."), "1"); print "<br>";
						form_radio_button("delete_type", "1", "2", _("Delete all <strong>data sources</strong> referenced by these graphs."), "1"); print "<br>";
						print "</td></tr>";
					}
				print "
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* change graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Choose a graph template and click save to change the graph template for
					the following graphs. Be aware that all warnings will be suppressed during the
					conversion, so graph data loss is possible.") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("New Graph Template:") . "</strong><br>"; form_dropdown("graph_template_id",db_fetch_assoc("select graph_templates.id,graph_templates.name from graph_templates"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "3") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following graphs will be duplicated. You can
					optionally change the title format for the new graphs.") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<graph_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "4") { /* graph -> graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following graphs will be converted into graph templates.
					You can optionally change the title format for the new graph templates.") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<graph_title> " . _("Template"), "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "5") { /* change host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Choose a new host for these graphs:") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("New Host:") . "</strong><br>"; form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "6") { /* reapply suggested naming to host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following graphs will have thier suggested naming convensions
					recalculated and applies to the graphs.") . "</p>
					<p>$graph_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "7") { /* reapply suggested naming to host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graphs will be resized per your specifications.</p>
					<p>$graph_list</p>
					<p><strong>Graph Height:</strong><br>"; form_text_box("graph_height", "", "", "255", "30", "text"); print "</p>
					<p><strong>Graph Width:</strong><br>"; form_text_box("graph_width", "", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "8") { /* place on tree */
		$trees = db_fetch_assoc("select id,name from graph_tree order by name");

		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("When you click save, the following graphs will be placed under the branch selected
					below.") . "</p>
					<p>$graph_list</p>
					<p><strong>" . _("Destination Branch:") . "</strong><br>"; grow_dropdown_tree($matches[1], "tree_item_id", "0"); print "</p>
				</td>
			</tr>\n
			<input type='hidden' name='tree_id' value='" . $matches[1] . "'>\n
			";
	}

	if (!isset($graph_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "'><span class='textError'>" . _("You must select at least one graph.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($graph_array) ? serialize($graph_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='graphs.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ------------------------------------
    graph - Graphs
   ------------------------------------ */

function graph_edit() {
	global $colors;

	if (!empty($_GET["id"])) {
		$graph = db_fetch_row("select * from graph where id=" . $_GET["id"]);

		if (!empty($graph["graph_template_id"])) {
			$graph_template = db_fetch_row("select * from graph_template where id=" . $graph["graph_template_id"]);
		}

		$header_label = _("[edit: ") . $graph["title_cache"] . "]";
	}else{
		$header_label = _("[new]");
	}

	/* handle debug mode */
	if (isset($_GET["debug"])) {
		if ($_GET["debug"] == "0") {
			kill_session_var("graph_debug_mode");
		}elseif ($_GET["debug"] == "1") {
			$_SESSION["graph_debug_mode"] = true;
		}
	}

	if (!empty($_GET["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td class="textInfo" colspan="2" valign="top">
					<?php echo $graph["title_cache"];?>
				</td>
				<td class="textInfo" align="right" valign="top">
					<span style="color: #c16921;">*<a href='graphs.php?action=edit&id=<?php print (isset($_GET["id"]) ? $_GET["id"] : 0);?>&debug=<?php print (isset($_SESSION["graph_debug_mode"]) ? "0" : "1");?>'>Turn <strong><?php print (isset($_SESSION["graph_debug_mode"]) ? "Off" : "On");?></strong> Graph Debug Mode.</a>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}

	/* ==================== Box: Device/Template Selection ==================== */

	$form_array = array(
		"graph_template_id" => array(
			"method" => "drop_sql",
			"friendly_name" => _("Selected Graph Template"),
			"description" => _("Choose a graph template to apply to this graph. Please note that graph data may be lost if you change the graph template after one is already applied."),
			"value" => (isset($graph) ? $graph["graph_template_id"] : "0"),
			"none_value" => _("None"),
			"sql" => "select graph_template.id,graph_template.template_name as name from graph_template order by template_name"
			),
		"host_id" => array(
			"method" => "drop_sql",
			"friendly_name" => _("Host"),
			"description" => _("Choose the host that this graph belongs to."),
			"value" => (isset($_GET["host_id"]) ? $_GET["host_id"] : $graph["host_id"]),
			"none_value" => _("None"),
			"sql" => "select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"
			),
		"id" => array(
			"method" => "hidden",
			"value" => (isset($graph) ? $graph["id"] : "0")
			)
		);

	html_start_box("<strong>" . _("Device/Template Selection") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(
		array(
			"config" => array(),
			"fields" => $form_array
			)
		);

	html_end_box();

	/* ==================== Box: Supplemental Template Data ==================== */

	/* only display the "inputs" area if we are using a graph template for this graph */
	if (!empty($graph["graph_template_id"])) {
		ob_start();

		html_start_box("<strong>" . _("Supplemental Template Data") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		$num_output_fields =  draw_nontemplated_fields_graph($graph["graph_template_id"], $graph, "g||field|", "<strong>" . _("Graph Fields") . "</strong>", true);
		$num_output_fields += draw_nontemplated_fields_graph_item($graph["graph_template_id"], array_rekey(db_fetch_assoc("select * from graph_item where graph_id = " . $graph["id"]), "graph_template_item_id", array("id", "data_source_item_id", "color", "graph_item_type", "cdef", "consolidation_function", "gprint_format", "legend_format", "legend_value", "hard_return")), "gip||field|||id|", "<strong>" . _("Graph Item Fields") . "</strong>", true);

		html_end_box();

		if ($num_output_fields == 0) {
			ob_end_clean();
		}else{
			ob_end_flush();
		}
	}

	/* graph item list goes here */
	if ((!empty($_GET["id"])) && (empty($graph["graph_template_id"]))) {
		$graph_items = db_fetch_assoc("select
			graph_item.id
			from graph_item
			where graph_item.graph_id = " . $_GET["id"] . "
			order by graph_item.sequence");

		/* ==================== Box: Graph Items ==================== */

		html_start_box("<strong>" . _("Graph Items") . "</strong>", "98%", $colors["header_background"], "3", "center", "graphs_items.php?action=edit&graph_id=" . $_GET["id"]);
		draw_graph_item_editor($_GET["id"], "graph", false);
		html_end_box();
	}

	/* display sample graph, or graph source in debug mode */
	if (!empty($_GET["id"])) {
		?>
		<table width="98%" align="center">
			<tr>
				<td align="center" class="textInfo" colspan="2">
					<img src="graph_image.php?graph_id=<?php print $_GET["id"];?>&rra_id=1" alt="">
				</td>
				<?php
				if ((isset($_SESSION["graph_debug_mode"])) && (isset($_GET["id"]))) {
					$graph_data_array["output_flag"] = RRDTOOL_OUTPUT_STDERR;
					?>
					<td>
						<span class="textInfo"><?php echo _("RRDTool Says:");?></span><br>
						<pre><?php print rrdtool_function_graph($_GET["id"], 1, $graph_data_array);?></pre>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<br>
		<?php
	}

	if ( (empty($graph["graph_template_id"])) && ( ((isset($_GET["id"])) && (is_numeric($_GET["id"]))) || ((isset($_GET["host_id"])) && (isset($_GET["graph_template_id"]))) ) ) {
		/* ==================== Box: Graph ==================== */

		html_start_box("<strong>" . _("Graph") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		field_row_header("General Options");
		_graph_field__title("g|title", false, (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__vertical_label("g|vertical_label", false, (isset($graph["vertical_label"]) ? $graph["vertical_label"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__image_format("g|image_format", false, (isset($graph["image_format"]) ? $graph["image_format"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__export("g|export", false, (isset($graph["export"]) ? $graph["export"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__force_rules_legend("g|force_rules_legend", false, (isset($graph["force_rules_legend"]) ? $graph["force_rules_legend"] : ""));
		field_row_header("Image Size Options");
		_graph_field__height("g|height", false, (isset($graph["height"]) ? $graph["height"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__width("g|width", false, (isset($graph["width"]) ? $graph["width"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		field_row_header("Grid Options");
		_graph_field__x_grid("g|x_grid", false, (isset($graph["x_grid"]) ? $graph["x_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__y_grid("g|y_grid", false, (isset($graph["y_grid"]) ? $graph["y_grid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__y_grid_alt("g|y_grid_alt", false, (isset($graph["y_grid_alt"]) ? $graph["y_grid_alt"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__no_minor("g|no_minor", false, (isset($graph["no_minor"]) ? $graph["no_minor"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		field_row_header("Auto Scaling Options");
		_graph_field__auto_scale("g|auto_scale", false, (isset($graph["auto_scale"]) ? $graph["auto_scale"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__auto_scale_opts("g|auto_scale_opts", false, (isset($graph["auto_scale_opts"]) ? $graph["auto_scale_opts"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__auto_scale_log("g|auto_scale_log", false, (isset($graph["auto_scale_log"]) ? $graph["auto_scale_log"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__auto_scale_rigid("g|auto_scale_rigid", false, (isset($graph["auto_scale_rigid"]) ? $graph["auto_scale_rigid"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));
		_graph_field__auto_padding("g|auto_padding", false, (isset($graph["auto_padding"]) ? $graph["auto_padding"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_auto_padding");
		field_row_header("Fixed Scaling Options");
		_graph_field__upper_limit("g|upper_limit", false, (isset($graph["upper_limit"]) ? $graph["upper_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_upper_limit");
		_graph_field__lower_limit("g|lower_limit", false, (isset($graph["lower_limit"]) ? $graph["lower_limit"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_lower_limit");
		_graph_field__base_value("g|base_value", false, (isset($graph["base_value"]) ? $graph["base_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_base_value");
		field_row_header("Units Display Options");
		_graph_field__unit_value("g|unit_value", false, (isset($graph["unit_value"]) ? $graph["unit_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_unit_value");
		_graph_field__unit_length("g|unit_length", false, (isset($graph["unit_length"]) ? $graph["unit_length"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]), "t_unit_length");
		_graph_field__unit_exponent_value("g|unit_exponent_value", false, (isset($graph["unit_exponent_value"]) ? $graph["unit_exponent_value"] : ""), (empty($_GET["id"]) ? 0 : $_GET["id"]));

		html_end_box();
	}

	form_hidden_box("action_post", "graph_edit");

	form_save_button("graphs.php");
}

function graph() {
	$current_page = get_get_var_number("page", "1");

	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate",
		"change_graph_template" => "Change Graph Template",
		"change_host" => "Change Host",
		"convert_graph_template" => "Convert to Graph Template",
		"place_tree" => "Place on Tree"
		);

	$filter_array = array();

	/* search field: device template */
	if (isset_get_var("search_device")) {
		$filter_array["host_id"] = get_get_var("search_device");
	}

	/* search field: filter (searches data source name) */
	if (isset_get_var("search_filter")) {
		$filter_array["filter"] = array("title_cache|title" => get_get_var("search_filter"));
	}

	/* get a list of all graphs on this page */
	$graphs = api_graph_list($filter_array, $current_page, read_config_option("num_rows_data_source"));

	/* get the total number of graphs on all pages */
	$total_rows = api_graph_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_device", "search_filter"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_graph"), $total_rows, "graphs.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	form_start("graphs.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Graphs") . "</strong>", "graphs.php?action=edit", $url_page_select);
	html_header_checkbox(array(_("Graph Title"), _("Template Name"), _("Size")), $box_id);

	$i = 0;
	if (sizeof($graphs) > 0) {
		foreach ($graphs as $graph) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $graph["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $graph["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $graph["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $graph["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $graph["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $graph["id"];?>')" href="graphs.php?action=edit&id=<?php echo $graph["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $graph["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $graph["title_cache"]);?></span></a>
				</td>
				<td class="content-row">
					<?php echo ((empty($graph["template_name"])) ? "<em>" . _("None") . "</em>" : $graph["template_name"]); ?>
				</td>
				<td class="content-row">
					<?php echo $graph["height"];?>x<?php echo $graph["width"];?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $graph["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $graph["id"];?>' title="<?php echo $graph["title_cache"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No graphs found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "3", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_hidden_box("action_post", "graph_list");
	form_end();

	/* pre-cache the device list since we need it in more than one place below */
	$device_list = array_rekey(api_device_list(), "id", "description");

	/* fill in the list of available devices for the search dropdown */
	$search_devices = array();
	$search_devices["-1"] = "Any";
	$search_devices["0"] = "None";
	$search_devices += $device_list;

	/* fill in the list of available devices for the change host dropdown */
	$change_host_list = array();
	$change_host_list["0"] = "None";
	$change_host_list += $device_list;

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these graphs?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Graph');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these graphs?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Graph');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'search') {
			_elm_dt_input = action_area_generate_select('box-' + box_id + '-search_device');
			<?php echo get_js_dropdown_code('_elm_dt_input', $search_devices, (isset_get_var("search_device") ? get_get_var("search_device") : "-1"));?>

			_elm_ht_input = action_area_generate_input('text', 'box-' + box_id + '-search_filter', '<?php echo get_get_var("search_filter");?>');
			_elm_ht_input.size = '30';

			parent_div.appendChild(action_area_generate_search_field(_elm_dt_input, 'Device', true, false));
			parent_div.appendChild(action_area_generate_search_field(_elm_ht_input, 'Filter', false, true));

			action_area_update_header_caption(box_id, 'Search');
			action_area_update_submit_caption(box_id, 'Search');
		}else if (type == 'change_host') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to change the host for these graphs?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			_elm_dt_input = action_area_generate_select('box-' + box_id + '-change_device');
			<?php echo get_js_dropdown_code('_elm_dt_input', $change_host_list, "0");?>

			parent_div.appendChild(action_area_generate_search_field(_elm_dt_input, 'New Device', true, true));

			action_area_update_header_caption(box_id, 'Change Host');
			action_area_update_submit_caption(box_id, 'Change');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>
