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

$guest_account = true;
$no_http_headers = true;

include(dirname(__FILE__) . "/../../include/global.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
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
input_validate_input_number(get_request_var_request("host_id"));
input_validate_input_number(get_request_var_request("graph_template_id"));
input_validate_input_regex(get_request_var_request('graph_list'), "^([\,0-9]+)$");
input_validate_input_regex(get_request_var_request('graph_add'), "^([\,0-9]+)$");
input_validate_input_regex(get_request_var_request('graph_remove'), "^([\,0-9]+)$");
/* ==================================================== */

/* clean up search string */
if (isset($_REQUEST["filter"])) {
	$_REQUEST["filter"] = sanitize_search_string(get_request_var_request("filter"));
}

if ((read_config_option("auth_method") != 0) && (empty($current_user["show_list"]))) {
	print "<strong><font size='+1' color='FF0000'>" . __("YOU DO NOT HAVE RIGHTS FOR LIST VIEW") . "</font></strong>"; exit;
}

/* if the user pushed the 'clear' button */
if (isset($_REQUEST["clear_x"])) {
	kill_session_var("sess_graph_view_list_current_page");
	kill_session_var("sess_graph_view_list_filter");
	kill_session_var("sess_graph_view_list_host");
	kill_session_var("sess_graph_view_list_graph_template");
	kill_session_var("sess_graph_view_list_graphs");
	kill_session_var("sess_graph_view_list_graph_list");
	kill_session_var("sess_graph_view_list_graph_add");
	kill_session_var("sess_graph_view_list_graph_remove");

	unset($_REQUEST["page"]);
	unset($_REQUEST["filter"]);
	unset($_REQUEST["host_id"]);
	unset($_REQUEST["graph_template_id"]);
	unset($_REQUEST["graphs"]);
	unset($_REQUEST["graph_list"]);
	unset($_REQUEST["graph_add"]);
	unset($_REQUEST["graph_remove"]);
}

/* save selected graphs into url, for backward compatibility */
if (!empty($_REQUEST["graph_list"])) {
	foreach (explode(",",$_REQUEST["graph_list"]) as $item) {
		$graph_list[$item] = 1;
	}
}else{
	$graph_list = array();
}

load_current_session_value("host_id", "sess_graph_view_list_host", "0");
load_current_session_value("graph_template_id", "sess_graph_view_list_graph_template", "0");
load_current_session_value("filter", "sess_graph_view_list_filter", "");
load_current_session_value("page", "sess_graph_view_list_current_page", "1");
load_current_session_value("graphs", "sess_graph_view_list_graphs", read_graph_config_option("list_graphs_per_page"));
load_current_session_value("graph_list", "sess_graph_view_list_graph_list", "");
load_current_session_value("graph_add", "sess_graph_view_list_graph_add", "");
load_current_session_value("graph_remove", "sess_graph_view_list_graph_remove", "");

if (is_array($_REQUEST["graph_list"])) {	$graph_list = $_REQUEST["graph_list"];
}

if (!empty($_REQUEST["graph_add"])) {
	foreach (explode(",",$_REQUEST["graph_add"]) as $item) {
		$graph_list[$item] = 1;
	}
}
/* remove items */
if (!empty($_REQUEST["graph_remove"])) {
	foreach (explode(",",$_REQUEST["graph_remove"]) as $item) {
		unset($graph_list[$item]);
	}
}
$_SESSION["sess_graph_view_list_graph_list"] = $graph_list;

/* display graph view filter selector */
html_graph_start_box(0, FALSE);

?>

<tr class='rowGraphFilter noprint'>
	<td>
		<script type="text/javascript">
		<!--
		$().ready(function() {
			$("#host").autocomplete("./lib/ajax/get_hosts_brief.php", { max: 12, highlight: false, scroll: true, scrollHeight: 300 });
			$("#host").result(function(event, data, formatted) {
				if (data) {
					$(this).parent().find("#host_id").val(data[1]);
					applyGraphListFilterChange(document.form_graph_list);
				}
			});
		});

		function applyGraphListFilterChange(objForm, strURL) {
			form_graph(document.chk, document.form_graph_list)

			if (!strURL || strURL == '') {				strURL = "?";
			}else{				strURL = strURL + "&";
			}
			strURL = strURL + 'host_id=' + objForm.host_id.value;
			strURL = strURL + '&graph_template_id=' + objForm.graph_template_id.value;
			strURL = strURL + '&graphs=' + objForm.graphs.value;
			strURL = strURL + '&filter=' + objForm.filter.value;
			strURL = strURL + '&graph_remove=' + objForm.graph_remove.value;
			strURL = strURL + '&graph_add=' + objForm.graph_add.value;
			$.get("lib/ajax/get_graph_list_content.php" + strURL, function (data) {
				$("#graph_content").html(data);
				SetSelections();
			});
		}

		function clearFilter(objForm) {			strURL = '?clear_x=true';
			$.get("lib/ajax/get_graph_list_content.php" + strURL, function (data) {
				$("#graph_content").html(data);
			});
		}

		function form_graph(objForm,objFormSubmit) {
			var strAdd = '';
			var strDel = '';
			for(var i = 0; i < objForm.elements.length; i++) {
				if (objForm.elements[i].name.substring(0,4) == 'chk_') {
					if (objForm.elements[i].checked) {
						strAdd = strAdd + objForm.elements[i].name.substring(4) + ',';
					} else {
						if (objForm.elements[i].value != '') {
							strDel = strDel + objForm.elements[i].name.substring(4) + ',';
						}
					}
				}
			}
			strAdd = strAdd.substring(0,strAdd.length - 1);
			strDel = strDel.substring(0,strDel.length - 1);
			objFormSubmit.graph_add.value = strAdd;
			objFormSubmit.graph_remove.value = strDel;
		}

		function pageChange(page) {
			strURL = '?page=' + page;
			applyGraphListFilterChange(document.form_graph_list, strURL);
		}

		function showGraphs(objForm) {
			form_graph(document.chk, document.form_graph_list)
			strURL = '?list=true';
			strURL = strURL + '&graph_remove=' + objForm.graph_remove.value;
			strURL = strURL + '&graph_add=' + objForm.graph_add.value;
			$.get("lib/ajax/get_graph_preview_content.php" + strURL, function (data) {
				$("#graph_content").html(data);
			});
		}

		registerOnLoadFunction("graph_view", "SetSelections();");

		-->
		</script>
		<form name="form_graph_list" action="graph_view.php" method="post">
		<input type='hidden' name='graph_add' value=''>
		<input type='hidden' name='graph_remove' value=''>
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td style='white-space:nowrap;width:1px;'>
					&nbsp;Host:&nbsp;
				</td>
				<td width="1">
					<?php
					if (isset($_REQUEST["host_id"])) {
						$hostname = db_fetch_cell("SELECT description as name FROM host WHERE id=".$_REQUEST["host_id"]." ORDER BY description,hostname");
					} else {
						$hostname = "";
					}
					?>
					<input class="ac_field" type="text" id="host" size="30" value="<?php print $hostname; ?>">
					<input type="hidden" id="host_id">
				</td>
				<td style='white-space:nowrap;width:1px;'>
					&nbsp;Template:&nbsp;
				</td>
				<td width="1">
					<select name="graph_template_id" onChange="applyGraphListFilterChange(document.form_graph_list)">
						<option value="0"<?php print $_REQUEST["filter"];?><?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>>Any</option>
						<?php
						if (read_config_option("auth_method") != 0) {
							$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.*
								FROM (graph_templates_graph,graph_local)
								LEFT JOIN host ON (host.id=graph_local.host_id)
								LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id)
								LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_HOSTS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
								WHERE graph_templates_graph.local_graph_id=graph_local.id " .
								"AND graph_templates_graph.graph_template_id > 0 " .
								(($_REQUEST["host_id"] > 0) ? " and graph_local.host_id=" . $_REQUEST["host_id"] :" and graph_local.host_id > 0 ") . "
								" . (empty($sql_where) ? "" : "and $sql_where") . "
								ORDER BY name");
						}else{
							$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.* " .
									"FROM graph_templates " .
									"INNER JOIN graph_local " .
									"ON graph_templates.id=graph_local.graph_template_id" .
									(($_REQUEST["host_id"] > 0) ? " WHERE host_id=" . $_REQUEST["host_id"] :"") .
									" GROUP BY graph_templates.name " .
									" ORDER BY name");
						}

						if (sizeof($graph_templates) > 0) {
						foreach ($graph_templates as $template) {
							print "<option value='" . $template["id"] . "'"; if ($_REQUEST["graph_template_id"] == $template["id"]) { print " selected"; } print ">" . $template["name"] . "</option>\n";
						}
						}
						?>
					</select>
				</td>
				<td style='white-space:nowrap;width:80px;'>
					&nbsp;<?php print __("Graphs/Page:");?>&nbsp;
				</td>
				<td width="1">
					<select name="graphs" onChange="applyGraphListFilterChange(document.form_graph_list)">
						<?php
						if (sizeof($graphs_per_page) > 0) {
						foreach ($graphs_per_page as $key => $value) {
							print "\t\t\t\t\t\t\t<option value='" . $key . "'"; if ((isset($_REQUEST["graphs"])) && ($_REQUEST["graphs"] == $key)) { print " selected"; } print ">" . $value . "</option>\n";
						}
						}
						?>
					</select>
				</td>
				<td style='white-space:nowrap;width:1px;'>
					&nbsp;<?php print __("Search:");?>&nbsp;
				</td>
				<td width="1">
					<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
				</td>
				<td>
					&nbsp;<input type="submit" value="<?php print __("Go");?>" name="go">
					<input type="button" value="<?php print __("Clear");?>" name="clear" onClick='clearFilter(document.form_graph_list)'>
				</td>
			</tr>
		</table>
		</form>
	</td>
</tr>
<?php
html_graph_end_box(TRUE);

/* create filter for sql */
$sql_filter = "";
$sql_filter .= (empty($_REQUEST["filter"]) ? "" : " graph_templates_graph.title_cache like '%" . $_REQUEST["filter"] . "%'");
$sql_filter .= (empty($_REQUEST["host_id"]) ? "" : (empty($sql_filter) ? "" : " and") . " graph_local.host_id=" . $_REQUEST["host_id"]);
$sql_filter .= (empty($_REQUEST["graph_template_id"]) ? "" : (empty($sql_filter) ? "" : " and") . " graph_local.graph_template_id=" . $_REQUEST["graph_template_id"]);

/* graph permissions */
if (read_config_option("auth_method") != 0) {
	/* get policy information for the sql where clause */
	$sql_where = "where " . get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);
	$sql_join = "left join host on (host.id=graph_local.host_id)
		left join graph_templates on (graph_templates.id=graph_local.graph_template_id)
		left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_HOSTS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";

}else{
	$sql_where = "";
	$sql_join = "";
}

$sql_base = "from (graph_templates_graph,graph_local)
	$sql_join
	$sql_where
	" . (empty($sql_where) ? "where" : "and") . " graph_templates_graph.local_graph_id > 0
	and graph_templates_graph.local_graph_id=graph_local.id
	and graph_templates_graph.title_cache like '%" . $_REQUEST["filter"] . "%'
	" . (empty($_REQUEST["host_id"]) ? "" : " and graph_local.host_id=" . $_REQUEST["host_id"]) . "
	" . (empty($_REQUEST["graph_template_id"]) ? "" : " and graph_local.graph_template_id=" . $_REQUEST["graph_template_id"]);

$total_rows = count(db_fetch_assoc("select
	graph_templates_graph.local_graph_id
	$sql_base"));
$graphs = db_fetch_assoc("select
	graph_templates_graph.local_graph_id,
	graph_templates_graph.title_cache,
	graph_templates_graph.height,
	graph_templates_graph.width
	$sql_base
	group by graph_templates_graph.local_graph_id
	order by graph_templates_graph.title_cache
	limit " . ($_REQUEST["graphs"]*($_REQUEST["page"]-1)) . "," . $_REQUEST["graphs"]);
?>
<form name='chk' id='chk' action='graph_view.php' method='get' onSubmit='form_graph(document.chk,document.chk)'>
<?php

html_graph_start_box(0, FALSE);

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

?>
<tr class='rowSubHeader noprint'>
	<td colspan='3'>
		<table width='100%' cellspacing='0' cellpadding='0' style='border-width:0px;'>
			<tr>
				<?php
				print "<td width='1%' align='left' class='textHeaderDark' style='padding:2px;'><input type='checkbox' style='margin: 0px 0px 0px 1px;' name='all' title='Select All' onClick='SelectAll(\"chk_\",this.checked)'></td><td class='textSubHeaderDark'><strong>Select All</strong></td>\n";
				?>
			</tr>
		</table>
	</td>
</tr>
<?php

if (sizeof($graphs) > 0) {
	foreach ($graphs as $graph) {
		form_alternate_row_color('line' . $graph["local_graph_id"], true);
		if (isset($graph_list[$graph["local_graph_id"]])) {
			$checked = true;
		}else{
			$checked = false;
		}
		form_checkbox_cell($graph["title_cache"], $graph["local_graph_id"], $checked);
		form_selectable_cell("<strong><a href='" . htmlspecialchars("graph.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=all") . "'>" . $graph["title_cache"] . "</a></strong>", $graph["local_graph_id"]);
		form_selectable_cell($graph["height"] . "x" . $graph["width"], $graph["local_graph_id"]);
		form_end_row();
	}
}
?>
<tr class='rowSubHeader'>
	<td colspan='3'>
		<table width='100%' cellspacing='0' cellpadding='0' style='border-width:0px'>
			<tr><?php
				print "<td width='1%' align='right' class='textHeaderDark' style='padding:2px;'><input type='checkbox' style='margin: 0px;' name='all' title='Select All' onClick='SelectAll(\"chk_\",this.checked)'></td><td class='textSubHeaderDark'><strong>Select All</strong></td>\n";
				?>
			</tr>
		</table>
	</td>
</tr>
<?php html_graph_end_box(FALSE);?>
<table align='center' style='background-color:#FFFFFF;' width='100%'>
	<tr>
		<td width='1'><img src='images/arrow.gif' alt='' align='middle'>&nbsp;</td>
		<td><input onClick='showGraphs(document.form_graph_list)' type='button' title='<?php print __("View Graphs");?>' value='<?php print __("View Graphs");?>' alt='<?php print __("View");?>'></td>
	</tr>
</table>
<input type='hidden' name='page' value='1'>
<input type='hidden' name='style' value='selective'>
<input type='hidden' name='action' value='preview'>
<input type='hidden' name='graph_add' value=''>
<input type='hidden' name='graph_remove' value=''>
</form><?php

?>
