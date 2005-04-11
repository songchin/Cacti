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

include("./include/config.php");
include("./include/auth.php");
include_once("./lib/utility.php");
include_once("./lib/graph/graph_update.php");
include_once("./lib/graph/graph_info.php");
include_once("./lib/graph/graph_form.php");
include_once("./lib/graph/graph_template.php");
include_once("./include/graph/graph_form.php");
include_once("./lib/api_tree.php");
include_once("./lib/data_source/data_source_update.php");
include_once("./lib/template.php");
//include_once("./lib/html_tree.php");
include_once("./lib/html_form_template.php");
include_once("./lib/rrd.php");

define("MAX_DISPLAY_PAGES", 21);

$graph_actions = array(
	1 => "Delete",
	2 => "Change Graph Template",
	3 => "Duplicate",
	4 => "Convert to Graph Template",
	5 => "Change Host",
	6 => "Reapply Suggested Names",
	7 => "Place on a Tree"
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
	case 'item':
		include_once("./include/top_header.php");

		item();

		include_once("./include/bottom_footer.php");
		break;
	case 'graph_remove':
		graph_remove();

		header("Location: graphs.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		graph_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		graph();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	/* fetch some cache variables */
	if (empty($_POST["id"])) {
		$_graph_template_id = 0;
	}else{
		$_graph_template_id = db_fetch_cell("select graph_template_id from graph where id=" . $_POST["id"]);
	}

	$graph_fields = array();
	$graph_item_input_fields = array();
	//$suggested_value_fields = array();

	/* parse out form values that we care about */
	reset($_POST);
	while (list($name, $value) = each($_POST)) {
		if (substr($name, 0, 2) == "g|") {
			$matches = explode("|", $name);
			$graph_fields{$matches[1]} = $value;
		}else if (substr($name, 0, 4) == "gip|") {
			$matches = explode("|", $name);
			$graph_item_input_fields{$matches[1]}{$matches[2]} = $value;
		//}else if (substr($name, 0, 3) == "sv|") {
		//	$matches = explode("|", $name);
		//	$suggested_value_fields{$matches[1]}{$matches[2]} = $value;
		}
	}

	/* save code for templated graphs */
	if ((!empty($_graph_template_id)) || (!empty($_POST["graph_template_id"]))) {
		//print "<pre>";print_r($graph_item_input_fields);print "</pre>";
		$graph_id = set_graph_template($_POST["id"], $_POST["graph_template_id"], $_POST["host_id"], $graph_fields, $graph_item_input_fields);
	}

	/* save code for non-templated graphs */
	if ((empty($_POST["graph_template_id"])) && (empty($_graph_template_id)) && (isset($_POST["save_component_graph"]))) {
		$graph_id = api_graph_save($_POST["id"], $_POST["host_id"], $_POST["graph_template_id"], $graph_fields["image_format"],
			$graph_fields["title"], $graph_fields["height"], $graph_fields["width"], $graph_fields["x_grid"],
			$graph_fields["y_grid"], (isset($graph_fields["y_grid_alt"]) ? $graph_fields["y_grid_alt"] : ""),
			(isset($graph_fields["no_minor"]) ? $graph_fields["no_minor"] : ""), $graph_fields["upper_limit"], $graph_fields["lower_limit"],
			$graph_fields["vertical_label"], (isset($graph_fields["auto_scale"]) ? $graph_fields["auto_scale"] : ""),
			$graph_fields["auto_scale_opts"], (isset($graph_fields["auto_scale_log"]) ? $graph_fields["auto_scale_log"] : ""),
			(isset($graph_fields["auto_scale_rigid"]) ? $graph_fields["auto_scale_rigid"] : ""),
			(isset($graph_fields["auto_padding"]) ? $graph_fields["auto_padding"] : ""), $graph_fields["base_value"],
			(isset($graph_fields["export"]) ? $graph_fields["export"] : ""), $graph_fields["unit_value"], $graph_fields["unit_length"],
			$graph_fields["unit_exponent_value"], (isset($graph_fields["force_rules_legend"]) ? $graph_fields["force_rules_legend"] : ""));
	}

	if ((is_error_message()) || (empty($graph_id)) || ($_POST["graph_template_id"] != $_graph_template_id)) {
		header("Location: graphs.php?action=edit" . (empty($graph_id) ? "" : "&id=$graph_id") . (!isset($_POST["host_id"]) ? "" : "&host_id=" . $_POST["host_id"]) . (!isset($_POST["graph_template_id"]) ? "" : "&graph_template_id=" . $_POST["graph_template_id"]));
	}else{
		header("Location: graphs.php");
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
				update_graph_title_cache($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "6") { /* reapply suggested naming */
			for ($i=0;($i<count($selected_items));$i++) {
				api_reapply_suggested_graph_title($selected_items[$i]);
				update_graph_title_cache($selected_items[$i]);
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

	include_once("./include/top_header.php");

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
					<p>Are you sure you want to delete the following graphs?</p>
					<p>$graph_list</p>
					";
					if (sizeof($data_sources) > 0) {
						print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td class='textArea'><p class='textArea'>The following data sources are in use by these graphs:</p>\n";

						foreach ($data_sources as $data_source) {
							print "<strong>" . $data_source["name_cache"] . "</strong><br>\n";
						}

						print "<br>";
						form_radio_button("delete_type", "1", "1", "Leave the data sources untouched.", "1"); print "<br>";
						form_radio_button("delete_type", "1", "2", "Delete all <strong>data sources</strong> referenced by these graphs.", "1"); print "<br>";
						print "</td></tr>";
					}
				print "
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* change graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>Choose a graph template and click save to change the graph template for
					the following graphs. Be aware that all warnings will be suppressed during the
					conversion, so graph data loss is possible.</p>
					<p>$graph_list</p>
					<p><strong>New Graph Template:</strong><br>"; form_dropdown("graph_template_id",db_fetch_assoc("select graph_templates.id,graph_templates.name from graph_templates"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "3") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graphs will be duplicated. You can
					optionally change the title format for the new graphs.</p>
					<p>$graph_list</p>
					<p><strong>Title Format:</strong><br>"; form_text_box("title_format", "<graph_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "4") { /* graph -> graph template */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graphs will be converted into graph templates.
					You can optionally change the title format for the new graph templates.</p>
					<p>$graph_list</p>
					<p><strong>Title Format:</strong><br>"; form_text_box("title_format", "<graph_title> Template", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "5") { /* change host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>Choose a new host for these graphs:</p>
					<p>$graph_list</p>
					<p><strong>New Host:</strong><br>"; form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","0"); print "</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "6") { /* reapply suggested naming to host */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graphs will have thier suggested naming convensions
					recalculated and applies to the graphs.</p>
					<p>$graph_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "7") { /* place on tree */
		$trees = db_fetch_assoc("select id,name from graph_tree order by name");

		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>When you click save, the following graphs will be placed under the branch selected
					below.</p>
					<p>$graph_list</p>
					<p><strong>Destination Branch:</strong><br>"; grow_dropdown_tree($matches[1], "tree_item_id", "0"); print "</p>
				</td>
			</tr>\n
			<input type='hidden' name='tree_id' value='" . $matches[1] . "'>\n
			";
	}

	if (!isset($graph_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one graph.</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='Save' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($graph_array) ? serialize($graph_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='graphs.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='Cancel' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once("./include/bottom_footer.php");
}

/* ------------------------------------
    graph - Graphs
   ------------------------------------ */

function graph_edit() {
	global $colors, $struct_graph, $struct_graph_item;

	if (!empty($_GET["id"])) {
		$graph = db_fetch_row("select * from graph where id=" . $_GET["id"]);

		if (!empty($graph["graph_template_id"])) {
			$graph_template = db_fetch_row("select * from graph_template where id=" . $graph["graph_template_id"]);
		}

		$header_label = "[edit: " . $graph["title_cache"] . "]";
	}else{
		$header_label = "[new]";
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
			"friendly_name" => "Selected Graph Template",
			"description" => "Choose a graph template to apply to this graph. Please note that graph data may be lost if you change the graph template after one is already applied.",
			"value" => (isset($graph) ? $graph["graph_template_id"] : "0"),
			"none_value" => "None",
			"sql" => "select graph_template.id,graph_template.template_name as name from graph_template order by template_name"
			),
		"host_id" => array(
			"method" => "drop_sql",
			"friendly_name" => "Host",
			"description" => "Choose the host that this graph belongs to.",
			"value" => (isset($_GET["host_id"]) ? $_GET["host_id"] : $graph["host_id"]),
			"none_value" => "None",
			"sql" => "select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"
			),
		"id" => array(
			"method" => "hidden",
			"value" => (isset($graph) ? $graph["id"] : "0")
			)
		);

	html_start_box("<strong>Device/Template Selection</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

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

		html_start_box("<strong>Supplemental Template Data</strong>", "98%", $colors["header_background"], "3", "center", "");

		$num_output_fields =  draw_nontemplated_fields_graph($graph["graph_template_id"], $graph, "g||field|", "<strong>Graph Fields</strong>", true);
		$num_output_fields += draw_nontemplated_fields_graph_item($graph["graph_template_id"], array_rekey(db_fetch_assoc("select * from graph_item where graph_id = " . $graph["id"]), "", array("id", "data_source_item_id", "color", "graph_item_type", "cdef", "consolidation_function", "gprint_format", "legend_format", "legend_value", "hard_return")), "gip||field|||id|", "<strong>Graph Item Fields</strong>", true);

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

		html_start_box("<strong>Graph Items</strong>", "98%", $colors["header_background"], "3", "center", "graphs_items.php?action=edit&graph_id=" . $_GET["id"]);
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
						<span class="textInfo">RRDTool Says:</span><br>
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
		html_start_box("<strong>Graph</strong>", "98%", $colors["header_background"], "3", "center", "");

		$form_array = array();

		while (list($field_name, $field_array) = each($struct_graph)) {
			$form_array += array("g|$field_name" => $struct_graph[$field_name]);
		}

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
				),
				"fields" => inject_form_variables($form_array, (isset($graph) ? $graph : array()))
				)
			);

		html_end_box();
	}

	if ((isset($_GET["id"])) || ((isset($_GET["host_id"])) && (isset($_GET["graph_template_id"])))) {
		form_hidden_box("save_component_graph","1","");
		form_hidden_box("save_component_input","1","");
	}else{
		form_hidden_box("save_component_graph_new","1","");
	}

	form_save_button("graphs.php");
}

function graph() {
	global $colors, $graph_actions;

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_graph_current_page");
		kill_session_var("sess_graph_filter");
		kill_session_var("sess_graph_host_id");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["host_id"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_graph_current_page", "1");
	load_current_session_value("filter", "sess_graph_filter", "");
	load_current_session_value("host_id", "sess_graph_host_id", "-1");

	html_start_box("<strong>Graphs</strong>", "98%", $colors["header_background"], "3", "center", "graphs.php?action=edit&host_id=" . $_REQUEST["host_id"]);

	include("./include/html/inc_graph_filter_table.php");

	html_end_box();

	/* form the 'where' clause for our main sql query */
	$sql_where = "where graph.title_cache like '%%" . $_REQUEST["filter"] . "%%'";

	if ($_REQUEST["host_id"] == "-1") {
		/* Show all items */
	}elseif ($_REQUEST["host_id"] == "0") {
		$sql_where .= " and graph.host_id=0";
	}elseif (!empty($_REQUEST["host_id"])) {
		$sql_where .= " and graph.host_id=" . $_REQUEST["host_id"];
	}

	html_start_box("", "98%", $colors["header_background"], "3", "center", "");

	$total_rows = db_fetch_cell("select
		count(*) from graph
		$sql_where");

	$graphs = db_fetch_assoc("select
		graph.id,
		graph.height,
		graph.width,
		graph.title_cache,
		graph.host_id,
		graph_template.template_name
		from graph
		left join graph_template on graph.graph_template_id=graph_template.id
		$sql_where
		order by graph.title_cache,graph.host_id
		limit " . (read_config_option("num_rows_graph")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_graph"));

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_graph"), $total_rows, "graphs.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"]);

	$nav = "<tr bgcolor='#" . $colors["header_background"] . "'>
			<td colspan='4'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='graphs.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"] . "&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "Previous"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
						</td>\n
						<td align='center' class='textHeaderDark'>
							Showing Rows " . ((read_config_option("num_rows_graph")*($_REQUEST["page"]-1))+1) . " to " . ((($total_rows < read_config_option("num_rows_graph")) || ($total_rows < (read_config_option("num_rows_graph")*$_REQUEST["page"]))) ? $total_rows : (read_config_option("num_rows_graph")*$_REQUEST["page"])) . " of $total_rows [$url_page_select]
						</td>\n
						<td align='right' class='textHeaderDark'>
							<strong>"; if (($_REQUEST["page"] * read_config_option("num_rows_graph")) < $total_rows) { $nav .= "<a class='linkOverDark' href='graphs.php?filter=" . $_REQUEST["filter"] . "&host_id=" . $_REQUEST["host_id"] . "&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "Next"; if (($_REQUEST["page"] * read_config_option("num_rows_graph")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	print $nav;

	html_header_checkbox(array("Graph Title", "Template Name", "Size"));

	$i = 0;
	if (sizeof($graphs) > 0) {
		foreach ($graphs as $graph) {
			if (trim($_REQUEST["filter"]) == "") {
				$highlight_text = title_trim($graph["title_cache"], read_config_option("max_title_graph"));
			}else{
				$highlight_text = eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", title_trim($graph["title_cache"], read_config_option("max_title_graph")));
			}

			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="graphs.php?action=edit&id=<?php print $graph["id"];?>"><?php print $highlight_text;?></a>
				</td>
				<td>
					<?php print ((empty($graph["template_name"])) ? "<em>None</em>" : $graph["template_name"]); ?>
				</td>
				<td>
					<?php print $graph["height"];?>x<?php print $graph["width"];?>
				</td>
				<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
					<input type='checkbox' style='margin: 0px;' name='chk_<?php print $graph["id"];?>' title="<?php print $graph["title_cache"];?>">
				</td>
			</tr>
			<?php
		}

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>No Graphs Found</em></td></tr>";
	}

	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($graph_actions);

	print "</form>\n";
}

?>