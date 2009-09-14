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

$guest_account = true;
include("./include/auth.php");
include("./lib/html_tree.php");
include("./include/html/inc_timespan_settings.php");
include("./include/top_graph_header.php");

/* ================= input validation ================= */
input_validate_input_number(get_request_var("branch_id"));
input_validate_input_number(get_request_var("hide"));
input_validate_input_number(get_request_var("tree_id"));
input_validate_input_number(get_request_var("leaf_id"));
input_validate_input_number(get_request_var("rra_id"));
/* ==================================================== */

if (isset($_GET["hide"])) {
	if (($_GET["hide"] == "0") || ($_GET["hide"] == "1")) {
		/* only update expand/contract info is this user has rights to keep their own settings */
		if ((isset($current_user)) && ($current_user["graph_settings"] == "on")) {
			db_execute("delete from settings_tree where graph_tree_item_id=" . $_GET["branch_id"] . " and user_id=" . $_SESSION["sess_user_id"]);
			db_execute("insert into settings_tree (graph_tree_item_id,user_id,status) values (" . $_GET["branch_id"] . "," . $_SESSION["sess_user_id"] . "," . $_GET["hide"] . ")");
		}
	}
}

if (ereg("action=(tree|preview|list)", get_browser_query_string())) {
	$_SESSION["sess_graph_view_url_cache"] = get_browser_query_string();
}

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
case 'tree':
	if ((read_config_option("global_auth") == "on") && (empty($current_user["show_tree"]))) {
		print "<strong><font size='+1' color='FF0000'>YOU DO NOT HAVE RIGHTS FOR TREE VIEW</font></strong>"; exit;
	}

	/* if cacti's builtin authentication is turned on then make sure to take
	graph permissions into account here. if a user does not have rights to a
	particular graph; do not show it. they will get an access denied message
	if they try and view the graph directly. */

	$access_denied = false;
	$tree_parameters = array();

	if ((!isset($_GET["tree_id"])) && (isset($_SESSION['dhtml_tree']))) {
		unset($_SESSION["dhtml_tree"]);
	}

	$tree_dropdown_html = draw_tree_dropdown((isset($_GET["tree_id"]) ? $_GET["tree_id"] : "0"));

	/* don't even print the table if there is not >1 tree */
	if ((!empty($tree_dropdown_html)) && (read_graph_config_option("default_tree_view_mode") == "1")) {
		print "
		<br>
		<table width='98%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center' cellpadding='3'>
			<tr>
				$tree_dropdown_html
			</tr>
		</table>\n";
	}

	if (isset($_SESSION["sess_view_tree_id"])) {
		if (read_config_option("global_auth") == "on") {
			/* take tree permissions into account here, if the user does not have permission
			give an "access denied" message */
			$access_denied = !(is_tree_allowed($_SESSION["sess_view_tree_id"]));

			if ($access_denied == true) {
				print "<strong><font size='+1' color='FF0000'>ACCESS DENIED</font></strong>"; exit;
			}
		}

		if (read_graph_config_option("default_tree_view_mode") == "1") {
			grow_graph_tree($_SESSION["sess_view_tree_id"], (!empty($start_branch) ? $start_branch : 0), isset($_SESSION["sess_user_id"]) ? $_SESSION["sess_user_id"] : 0, $tree_parameters);
		}elseif (read_graph_config_option("default_tree_view_mode") == "2") {
			grow_right_pane_tree((isset($_GET["tree_id"]) ? $_GET["tree_id"] : 0), (isset($_GET["leaf_id"]) ? $_GET["leaf_id"] : 0), (isset($_GET["host_group_data"]) ? urldecode($_GET["host_group_data"]) : 0));
		}
	}

	print "<br><br>";

	break;
case 'preview':
	define("ROWS_PER_PAGE", read_graph_config_option("preview_graphs_per_page"));

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("host_id"));
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	$sql_or = ""; $sql_where = ""; $sql_join = "";

	if ((read_config_option("global_auth") == "on") && (empty($current_user["show_preview"]))) {
		print "<strong><font size='+1' color='FF0000'>YOU DO NOT HAVE RIGHTS FOR PREVIEW VIEW</font></strong>"; exit;
	}

	/* reset the page counter to '1' if a search in initiated */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["page"] = "1";
	}

	load_current_session_value("host_id", "sess_graph_view_host", "0");
	load_current_session_value("filter", "sess_graph_view_filter", "");
	load_current_session_value("page", "sess_graph_view_current_page", "1");

	/* graph permissions */
	if (read_config_option("global_auth") == "on") {
		$sql_where = "where " . get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);

		$sql_join = "left join host on host.id=graph_local.host_id
			left join graph_templates on graph_templates.id=graph_local.graph_template_id
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}

	/* the user select a bunch of graphs of the 'list' view and wants them dsplayed here */
	if (isset($_REQUEST["style"])) {
		if ($_REQUEST["style"] == "selective") {
			$i = 0;
			while (list($var, $val) = each($_GET)) {
				if (ereg('^graph_([0-9]+)$', $var, $matches)) {
					$graph_array[$i] = $matches[1];
					$i++;
				}
			}

			if ((isset($graph_array)) && (sizeof($graph_array) > 0)) {
				/* build sql string including each graph the user checked */
				$sql_or = "and " . array_to_sql_or($graph_array, "graph_templates_graph.local_graph_id");

				/* clear the filter vars so they don't affect our results */
				$_REQUEST["filter"] = "";
				$_REQUEST["host_id"] = "0";

				/* Fix to avoid error in 'preview' after selection in 'list' : Notice: Undefined index: rra_id in C:\apache2\htdocs\cacti\graph_view.php on line 142 */
				$set_rra_id = empty($rra_id) ? read_graph_config_option("default_rra_id") : $_REQUEST["rra_id"];
			}
		}
	}

	$sql_base = "from graph_templates_graph,graph_local
		$sql_join
		$sql_where
		" . (empty($sql_where) ? "where" : "and") . " graph_templates_graph.local_graph_id > 0
		and graph_templates_graph.local_graph_id=graph_local.id
		and graph_templates_graph.title_cache like '%%" . $_REQUEST["filter"] . "%%'
		" . (empty($_REQUEST["host_id"]) ? "" : " and graph_local.host_id=" . $_REQUEST["host_id"]) . "
		$sql_or";

	$total_rows = count(db_fetch_assoc("select
		graph_templates_graph.local_graph_id
		$sql_base"));
	$graphs = db_fetch_assoc("select
		graph_templates_graph.local_graph_id,
		graph_templates_graph.title_cache
		$sql_base
		group by graph_templates_graph.local_graph_id
		order by graph_templates_graph.title_cache
		limit " . (ROWS_PER_PAGE*($_REQUEST["page"]-1)) . "," . ROWS_PER_PAGE);

	/* include graph view filter selector */
	html_graph_start_box(3, true);
	include("./include/html/inc_graph_view_filter_table.php");
	html_graph_end_box();

	/* include time span selector */
	if (read_graph_config_option("timespan_sel") == "on") {
		html_graph_start_box(3, true);
		include("./include/html/inc_timespan_selector.php");
		html_graph_end_box();
	}

	/* do some fancy navigation url construction so we don't have to try and rebuild the url string */
	if (ereg("page=[0-9]+",basename($_SERVER["QUERY_STRING"]))) {
		$nav_url = str_replace("page=" . $_REQUEST["page"], "page=<PAGE>", basename($_SERVER["PHP_SELF"]) . "?" . $_SERVER["QUERY_STRING"]);
	}else{
		$nav_url = basename($_SERVER["PHP_SELF"]) . "?" . $_SERVER["QUERY_STRING"] . "&page=<PAGE>&host_id=" . $_REQUEST["host_id"];
	}

	$nav_url = ereg_replace("((\?|&)host_id=[0-9]+|(\?|&)filter=[a-zA-Z0-9]*)", "", $nav_url);

	html_graph_start_box(1, true);
	html_nav_bar($colors["header_panel"], read_graph_config_option("num_columns"), $_REQUEST["page"], ROWS_PER_PAGE, $total_rows, $nav_url);

	if (read_graph_config_option("thumbnail_section_preview") == "on") {
		html_graph_thumbnail_area($graphs, "","graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
	}else{
		html_graph_area($graphs, "", "graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
	}

	html_graph_end_box();

	print "<br><br>";

	break;
case 'list':
	if ((read_config_option("global_auth") == "on") && (empty($current_user["show_list"]))) {
		print "<strong><font size='+1' color='FF0000'>YOU DO NOT HAVE RIGHTS FOR LIST VIEW</font></strong>"; exit;
	}

	/* graph permissions */
	if (read_config_option("global_auth") == "on") {
		/* get policy information for the sql where clause */
		$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);

		$graphs = db_fetch_assoc("select
			graph_templates_graph.local_graph_id,
			graph_templates_graph.title_cache,
			graph_templates_graph.height,
			graph_templates_graph.width
			from graph_templates_graph,graph_local
			left join host on host.id=graph_local.host_id
			left join graph_templates on graph_templates.id=graph_local.graph_template_id
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
			where graph_templates_graph.local_graph_id=graph_local.id
			and graph_templates_graph.local_graph_id>0
			" . (empty($sql_where) ? "" : "and $sql_where") . "
			group by graph_templates_graph.local_graph_id
			order by graph_templates_graph.title_cache");
	}else{
		$graphs = db_fetch_assoc("select
			local_graph_id,title_cache,height,width
			from graph_templates_graph
			where local_graph_id > 0
			order by title_cache");
	}

	print "<form action='graph_view.php' method='get'>\n";

	html_graph_start_box(1, true);

	print "<tr bgcolor='#" . $colors["header_panel"] . "'><td colspan='3'><table cellspacing='0' cellpadding='3' width='100%'><tr><td class='textHeaderDark'><strong>Displaying " . sizeof($graphs) . " Graph" . ((sizeof($graphs) == 1) ? "" : "s") . "</strong></td></tr></table></td></tr>";

	$i = 0;
	if (sizeof($graphs) > 0) {
	foreach ($graphs as $graph) {
		form_alternate_row_color("f5f5f5", "ffffff", $i);

		print "<td width='1%'>";
		form_checkbox("graph_" . $graph["local_graph_id"], "", "", "", 0);
		print "</td>";

		print "<td><strong><a href='graph.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=all'>" . $graph["title_cache"] . "</a></strong></td>\n";
		print "<td>" . $graph["height"] . "x" . $graph["width"] . "</td>\n";
		print "</tr>";

		$i++;
	}
	}

	print "	</table>
		<table align='center' width='98%'>
			<tr>
				<td width='1'>
					<img src='images/arrow.gif' alt='' align='absmiddle'>&nbsp;
				</td>
				<td>
					<input type='image' src='images/button_view.gif' alt='View'>
				</td>
			</tr>
		</table><br><br>\n
	<input type='hidden' name='page' value='1'>
	<input type='hidden' name='style' value='selective'>\n
	<input type='hidden' name='action' value='preview'>\n
	</form>\n";

	break;
}

//print "<pre>";print $_SESSION["sess_debug_buffer"];print "</pre>";session_unregister("sess_debug_buffer");

include_once("./include/bottom_footer.php");
