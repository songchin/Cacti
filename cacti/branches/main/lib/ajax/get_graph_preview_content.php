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

$no_http_headers = true;

include(dirname(__FILE__) . "/../../include/global.php");
include_once(dirname(__FILE__) . "/../../lib/functions.php");
include_once(dirname(__FILE__) . "/../../lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/timespan_settings.php");
include_once(CACTI_BASE_PATH . "/lib/form_graph_view.php");

/* Make sure nothing is cached */
header("Cache-Control: must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H")-2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

$current_user = db_fetch_row("SELECT * FROM user_auth WHERE id=" . $_SESSION["sess_user_id"]);

define("MAX_DISPLAY_PAGES", 21);

/* ================= input validation ================= */
input_validate_input_number(get_request_var("graphs"));
input_validate_input_number(get_request_var_request("host_id"));
input_validate_input_number(get_request_var_request("graph_template_id"));
input_validate_input_number(get_request_var_request("page"));
/* ==================================================== */

/* clean up search string */
if (isset($_REQUEST["filter"])) {
	$_REQUEST["filter"] = sanitize_search_string(get_request_var_request("filter"));
}

/* clean up search string */
if (isset($_REQUEST["thumbnails"])) {
	$_REQUEST["thumbnails"] = sanitize_search_string(get_request_var_request("thumbnails"));
}

$sql_or = ""; $sql_where = ""; $sql_join = "";

if ((read_config_option("auth_method") != 0) && (empty($current_user["show_preview"]))) {
	print "<strong><font size='+1' color='FF0000'>" . __("YOU DO NOT HAVE RIGHTS FOR PREVIEW VIEW") . "</font></strong>"; exit;
}

/* if the user pushed the 'clear' button */
if (isset($_REQUEST["clear_filter"])) {
	kill_session_var("sess_graph_view_current_page");
	kill_session_var("sess_graph_view_filter");
	kill_session_var("sess_graph_view_graph_template");
	kill_session_var("sess_graph_view_host");
	kill_session_var("sess_graph_view_graphs");
	kill_session_var("sess_graph_view_thumbnails");

	unset($_REQUEST["page"]);
	unset($_REQUEST["filter"]);
	unset($_REQUEST["host_id"]);
	unset($_REQUEST["graphs"]);
	unset($_REQUEST["thumbnails"]);
	unset($_REQUEST["graph_template_id"]);
	unset($_REQUEST["graph_list"]);
	unset($_REQUEST["graph_add"]);
	unset($_REQUEST["graph_remove"]);
}

/* reset the page counter to '1' if a search in initiated */
if (isset($_REQUEST["filter"])) {
	$_REQUEST["page"] = "1";
}

load_current_session_value("host_id", "sess_graph_view_host", "0");
load_current_session_value("graph_template_id", "sess_graph_view_graph_template", "0");
load_current_session_value("filter", "sess_graph_view_filter", "");
load_current_session_value("page", "sess_graph_view_current_page", "1");
load_current_session_value("thumbnails", "sess_graph_view_thumbnails", "on");
load_current_session_value("graphs", "sess_graph_view_graphs", read_graph_config_option("preview_graphs_per_page"));

/* graph permissions */
if (read_config_option("auth_method") != 0) {
	$sql_where = "where " . get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);

	$sql_join = "left join host on (host.id=graph_local.host_id)
		left join graph_templates on (graph_templates.id=graph_local.graph_template_id)
		left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_HOSTS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
}else{
	$sql_where = "";
	$sql_join = "";
}
/* the user select a bunch of graphs of the 'list' view and wants them dsplayed here */
if (isset($_REQUEST["style"])) {
	if ($_REQUEST["style"] == "selective") {

		/* process selected graphs */
		if (! empty($_REQUEST["graph_list"])) {
			foreach (explode(",",$_REQUEST["graph_list"]) as $item) {
				$graph_list[$item] = 1;
			}
		}else{
			$graph_list = array();
		}
		if (! empty($_REQUEST["graph_add"])) {
			foreach (explode(",",$_REQUEST["graph_add"]) as $item) {
				$graph_list[$item] = 1;
			}
		}
		/* remove items */
		if (! empty($_REQUEST["graph_remove"])) {
			foreach (explode(",",$_REQUEST["graph_remove"]) as $item) {
				unset($graph_list[$item]);
			}
		}

		$i = 0;
		foreach ($graph_list as $item => $value) {
			$graph_array[$i] = $item;
			$i++;
		}

		if ((isset($graph_array)) && (sizeof($graph_array) > 0)) {
			/* build sql string including each graph the user checked */
			$sql_or = "AND " . array_to_sql_or($graph_array, "graph_templates_graph.local_graph_id");

			/* clear the filter vars so they don't affect our results */
			$_REQUEST["filter"]  = "";
			$_REQUEST["host_id"] = "0";

			/* Fix to avoid error in 'preview' after selection in 'list' : Notice: Undefined index: rra_id in C:\apache2\htdocs\cacti\graph_view.php on line 142 */
			$set_rra_id = empty($rra_id) ? read_graph_config_option("default_rra_id") : $_REQUEST["rra_id"];
		}
	}
}

$sql_base = "FROM (graph_templates_graph,graph_local)
	$sql_join
	$sql_where
	" . (empty($sql_where) ? "WHERE" : "AND") . "   graph_templates_graph.local_graph_id > 0
	AND graph_templates_graph.local_graph_id=graph_local.id
	AND graph_templates_graph.title_cache like '%%" . $_REQUEST["filter"] . "%%'
	" . (empty($_REQUEST["host_id"]) ? "" : " and graph_local.host_id=" . $_REQUEST["host_id"]) . "
	" . (empty($_REQUEST["graph_template_id"]) ? "" : " and graph_local.graph_template_id=" . $_REQUEST["graph_template_id"]) . "
	$sql_or";

$total_rows = count(db_fetch_assoc("SELECT
	graph_templates_graph.local_graph_id
	$sql_base"));

/* reset the page if you have changed some settings */
if ($_REQUEST["graphs"] * ($_REQUEST["page"]-1) >= $total_rows) {
	$_REQUEST["page"] = "1";
}

$graphs = db_fetch_assoc("SELECT
	graph_templates_graph.local_graph_id,
	graph_templates_graph.title_cache
	$sql_base
	GROUP BY graph_templates_graph.local_graph_id
	ORDER BY graph_templates_graph.title_cache
	LIMIT " . ($_REQUEST["graphs"]*($_REQUEST["page"]-1)) . "," . $_REQUEST["graphs"]);

/* include graph view filter selector */
graph_view_filter_table("preview");

/* include time span selector */
if (read_graph_config_option("timespan_sel") == "on") {
	graph_view_timespan_selector("preview");
}

?>
<script type='text/javascript'>
<!--
function pageChange(page) {
	strURL = '?page=' + page;
	$.get("lib/ajax/get_graph_preview_content.php" + strURL, function (data) {
		$("#graph_content").html(data);
	});
}
-->
</script>
<?php

html_graph_start_box(0, false);

print "<table cellpadding='0' cellspacing='0' style='width:100%;border:1px solid #BEBEBE;'>\n";
/* generate page list */

if ($total_rows > $_REQUEST["graphs"]) {
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $_REQUEST["graphs"], $total_rows, "pageChange");

	$nav = "\t\t\t<tr class='rowHeader'>
			<td colspan='11'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' style='width:100px;' class='textHeaderDark'>";
	if ($_REQUEST["page"] > 1) { $nav .= "<strong><a class='linkOverDark' href='#' onClick='pageChange(" . ($_REQUEST["page"]-1) . ")'>&lt;&lt;&nbsp;Previous</a></strong>"; }
	$nav .= "</td>\n
						<td align='center' class='textHeaderDark'>
							Showing Graphs " . (($_REQUEST["graphs"]*($_REQUEST["page"]-1))+1) . " to " . ((($total_rows < $_REQUEST["graphs"]) || ($total_rows < ($_REQUEST["graphs"]*$_REQUEST["page"]))) ? $total_rows : ($_REQUEST["graphs"]*$_REQUEST["page"])) . " of $total_rows [$url_page_select]
						</td>\n
						<td align='right' style='width:100px;' class='textHeaderDark'>";
	if (($_REQUEST["page"] * $_REQUEST["graphs"]) < $total_rows) { $nav .= "<strong><a class='linkOverDark' href='#' onClick='pageChange(" . ($_REQUEST["page"]+1) . ")'>Next &gt;&gt;</a></strong>"; }
	$nav .= "</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";
}else{
	$nav = "<tr class='rowHeader'>
			<td colspan='11'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='center' class='textHeaderDark'>
							Showing All Graphs" . (strlen($_REQUEST["filter"]) ? " [ Filter '" . $_REQUEST["filter"] . "' Applied ]" : "") . "
						</td>
					</tr>
				</table>
			</td>
		</tr>\n";
}

print $nav;

if (read_graph_config_option("thumbnail_section_preview") == "on") {
	html_graph_thumbnail_area($graphs, "","graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
}else{
	html_graph_area($graphs, "", "graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
}

print $nav;

html_graph_end_box();

?>
