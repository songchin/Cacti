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

function graph_view_filter_table($mode = "mode") {
	global $current_user;
	global $graphs_per_page;
	global $colors;

	?>
	<script type='text/javascript'>
	<!--

	$().ready(function() {
		$("#host").autocomplete("./lib/ajax/get_hosts_brief.php", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#host").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#host_id").val(data[1]);
				applyGraphFilter(document.form_graph_view);
			}else{
				$(this).parent().find("#host_id").val(0);
			}
		});
	});

	function applyGraphFilter(objForm) {
		<?php if ($mode == 'tree') { ?>
		strURL = '?action=ajax_tree_graphs&host_id=' + objForm.host_id.value;
		strURL = strURL + '&graph_template_id=' + objForm.graph_template_id.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
		<?php }else{ ;?>
		strURL = '?action=ajax_preview&host_id=' + objForm.host_id.value;
		strURL = strURL + '&graph_template_id=' + objForm.graph_template_id.value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
		<?php } ;?>
	}

	function clearGraphFilter(objForm) {
		<?php if ($mode == 'tree') { ?>
		strURL = '?action=ajax_tree_graphs&clear_filter=true';
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
		<?php }else{ ;?>
		strURL = '?action=ajax_preview&clear_filter=true';
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
		<?php } ;?>
	}

	-->
	</script>
	<?php

	html_start_box("", "100", $colors["header"], "0", "center", "");

	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_graph_view" method="post" action="graph_view.php">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr class="rowGraphFilter noprint">
					<td class="nw50">
						&nbsp;<?php print __("Device:");?>&nbsp;
					</td>
					<td class='w1'>
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
					<td class='w1'>
						&nbsp;<?php print __("Template:");?>&nbsp;
					</td>
					<td class='w1'>
						<select name="graph_template_id" onChange="applyGraphFilter(document.form_graph_view)">
							<option value="0"<?php if (get_request_var_request("graph_template_id") == "0") {?> selected<?php }?>><?php print __("Any");?></option><?php
							if (read_config_option("auth_method") != 0) {
								$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.* " .
										"FROM (graph_templates_graph,graph_local) " .
										"LEFT JOIN host ON (host.id=graph_local.host_id) " .
										"LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id) " .
										"LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")) " .
										"WHERE graph_templates_graph.local_graph_id=graph_local.id " .
										"AND graph_templates_graph.graph_template_id > 0 " .
										(($_REQUEST["host_id"] > 0) ? " and graph_local.host_id=" . $_REQUEST["host_id"] :" and graph_local.host_id > 0 ") .
										(empty($sql_where) ? "" : "and $sql_where") .
										" ORDER BY name");
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
								print "\t\t\t\t\t\t\t<option value='" . $template["id"] . "'"; if (get_request_var_request("graph_template_id") == $template["id"]) { print " selected"; } print ">" . $template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw50">
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td class='w1'>
						<input type='text' name='filter' size='40' onChange='applyGraphFilter(document.form_graph_view)' value='<?php print $_REQUEST["filter"];?>'>
					</td>
					<td class='w1'>
						&nbsp;<input type='button' Value='<?php print __("Go");?>' name='go' onClick='applyGraphFilter(document.form_graph_view)'>
						<input type='button' Value='<?php print __("Clear");?>' name='clear_x' onClick='clearGraphFilter(document.form_graph_view)'>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<?php
	html_graph_end_box(FALSE);
}

function get_graph_list_content() {
	global $graphs_per_page;
	global $colors;

	/* Make sure nothing is cached */
	header("Cache-Control: must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

	$current_user = db_fetch_row("SELECT * FROM user_auth WHERE id=" . $_SESSION["sess_user_id"]);

	define("MAX_DISPLAY_PAGES", 21);

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("host_id"));
	input_validate_input_number(get_request_var_request("graph_template_id"));
	input_validate_input_regex(get_request_var_request('graph_list'), "/^([\,0-9]+)$/");
	input_validate_input_regex(get_request_var_request('graph_add'), "/^([\,0-9]+)$/");
	input_validate_input_regex(get_request_var_request('graph_remove'), "/^([\,0-9]+)$/");
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
		foreach (explode(",",get_request_var_request("graph_list")) as $item) {
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

	if (is_array(get_request_var_request("graph_list"))) {
		$graph_list = $_REQUEST["graph_list"];
	}

	if (!empty($_REQUEST["graph_add"])) {
		foreach (explode(",",get_request_var_request("graph_add")) as $item) {
			$graph_list[$item] = 1;
		}
	}
	/* remove items */
	if (!empty($_REQUEST["graph_remove"])) {
		foreach (explode(",",get_request_var_request("graph_remove")) as $item) {
			unset($graph_list[$item]);
		}
	}
	$_SESSION["sess_graph_view_list_graph_list"] = $graph_list;

	/* display graph view filter selector */
	html_start_box("", "100", $colors["header"], "0", "center", "");

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
				SetSelections();
			});

			function applyGraphListFilterChange(objForm, strURL) {
				form_graph(document.chk, document.form_graph_list)

				if (!strURL || strURL == '') {
					strURL = "?action=ajax_preview&";
				}else{
					strURL = strURL + "&action=ajax_list&";
				}

				strURL = strURL + 'host_id=' + objForm.host_id.value;
				strURL = strURL + '&graph_template_id=' + objForm.graph_template_id.value;
				strURL = strURL + '&graphs=' + objForm.graphs.value;
				strURL = strURL + '&filter=' + objForm.filter.value;
				strURL = strURL + '&graph_remove=' + objForm.graph_remove.value;
				strURL = strURL + '&graph_add=' + objForm.graph_add.value;
				$.get("graph_view.php" + strURL, function (data) {
					$("#graph_content").html(data);
					SetSelections();
				});
			}

			function clearFilter(objForm) {
				strURL = '?clear_x=true';
				$.get("graph_view.php?action=ajax_list" + strURL, function (data) {
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
				strURL = '?action=ajax_preview&list=true';
				strURL = strURL + '&graph_remove=' + objForm.graph_remove.value;
				strURL = strURL + '&graph_add=' + objForm.graph_add.value;
				$.get("graph_view.php" + strURL, function (data) {
					$("#graph_content").html(data);
				});
			}

			//-->
			</script>
			<form name="form_graph_list" action="graph_view.php" method="post">
			<input type='hidden' name='graph_add' value=''>
			<input type='hidden' name='graph_remove' value=''>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td class='w1'>
						&nbsp;<?php print __("Device:");?>&nbsp;
					</td>
					<td class="w1">
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
					<td class='w1'>
						&nbsp;<?php print __("Template:");?>&nbsp;
					</td>
					<td class='w1'>
						<select name="graph_template_id" onChange="applyGraphListFilterChange(document.form_graph_list)">
							<option value="0"<?php print get_request_var_request("filter");?><?php if (get_request_var_request("host_id") == "0") {?> selected<?php }?>><?php print __("Any");?></option>
							<?php
							if (read_config_option("auth_method") != 0) {
								$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.*
									FROM (graph_templates_graph,graph_local)
									LEFT JOIN host ON (host.id=graph_local.host_id)
									LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id)
									LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_DEVICES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
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
								print "<option value='" . $template["id"] . "'"; if (get_request_var_request("graph_template_id") == $template["id"]) { print " selected"; } print ">" . $template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td class="nw100">
						&nbsp;<?php print __("Graphs/Page:");?>&nbsp;
					</td>
					<td class="w1">
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
					<td class='w1'>
						&nbsp;<?php print __("Search:");?>&nbsp;
					</td>
					<td class="w1">
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
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_DEVICES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";

	}else{
		$sql_where = "";
		$sql_join = "";
	}

	$sql_base = "from (graph_templates_graph,graph_local)
		$sql_join
		$sql_where
		" . (empty($sql_where) ? "where" : "and") . " graph_templates_graph.local_graph_id > 0
		and graph_templates_graph.local_graph_id=graph_local.id
		and graph_templates_graph.title_cache like '%" . get_request_var_request("filter") . "%'
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
		limit " . (get_request_var_request("graphs")*(get_request_var_request("page")-1)) . "," . get_request_var_request("graphs"));
	?>
	<form name='chk' id='chk' action='graph_view.php' method='get' onSubmit='form_graph(document.chk,document.chk)'>
	<?php

	html_start_box("", "100", $colors["header"], "0", "center", "");

	if ($total_rows > get_request_var_request("graphs")) {
		$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $_REQUEST["graphs"], $total_rows, "pageChange");

		$nav = "\t\t\t<tr class='rowHeader'>
				<td colspan='11'>
					<table width='100%' cellspacing='0' cellpadding='0' border='0'>
						<tr>
							<td align='left' style='width:100px;' class='textHeaderDark'>";
		if ($_REQUEST["page"] > 1) { $nav .= "<strong><a class='linkOverDark' href='#' onClick='pageChange(" . ($_REQUEST["page"]-1) . ")'>&lt;&lt;&nbsp;Previous</a></strong>"; }
		$nav .= "</td>\n
							<td align='center' class='textHeaderDark'>
								Showing Graphs " . ((get_request_var_request("graphs")*(get_request_var_request("page")-1))+1) . " to " . ((($total_rows < get_request_var_request("graphs")) || ($total_rows < (get_request_var_request("graphs")*get_request_var_request("page")))) ? $total_rows : (get_request_var_request("graphs")*get_request_var_request("page"))) . " of $total_rows [$url_page_select]
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
								" . __("Showing All Graphs") . (strlen(get_request_var_request("filter")) ? " [ " . __("Filter") . " '" . get_request_var_request("filter") . "' " . __("Applied") . " ]" : "") . "
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
}

function get_graph_preview_content () {
	global $colors;
	/* Make sure nothing is cached */
	header("Cache-Control: must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
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
		kill_session_var("sess_graph_view_list_graph_list");
		kill_session_var("sess_graph_view_list_graph_add");
		kill_session_var("sess_graph_view_list_graph_remove");

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

	/* save selected graphs into url, for backward compatibility */
	if (!empty($_REQUEST["graph_list"])) {
		foreach (explode(",",get_request_var_request("graph_list")) as $item) {
			$graph_list[$item] = 1;
		}
	}else{
		$graph_list = array();
	}

	load_current_session_value("host_id", "sess_graph_view_host", "0");
	load_current_session_value("graph_template_id", "sess_graph_view_graph_template", "0");
	load_current_session_value("filter", "sess_graph_view_filter", "");
	load_current_session_value("page", "sess_graph_view_current_page", "1");
	load_current_session_value("thumbnails", "sess_graph_view_thumbnails", CHECKED);
	load_current_session_value("graphs", "sess_graph_view_graphs", read_graph_config_option("preview_graphs_per_page"));
	load_current_session_value("graph_list", "sess_graph_view_list_graph_list", "");
	load_current_session_value("graph_add", "sess_graph_view_list_graph_add", "");
	load_current_session_value("graph_remove", "sess_graph_view_list_graph_remove", "");

	/* graph permissions */
	if (read_config_option("auth_method") != 0) {
		$sql_where = "where " . get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);

		$sql_join = "left join host on (host.id=graph_local.host_id)
			left join graph_templates on (graph_templates.id=graph_local.graph_template_id)
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_DEVICES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}else{
		$sql_where = "";
		$sql_join = "";
	}
	/* the user select a bunch of graphs of the 'list' view and wants them dsplayed here */
	if (isset($_REQUEST["list"])) {
		if (is_array(get_request_var_request("graph_list"))) {
			$graph_list = $_REQUEST["graph_list"];
		}

		if (!empty($_REQUEST["graph_add"])) {
			foreach (explode(",",get_request_var_request("graph_add")) as $item) {
				$graph_list[$item] = 1;
			}
		}
		/* remove items */
		if (!empty($_REQUEST["graph_remove"])) {
			foreach (explode(",",get_request_var_request("graph_remove")) as $item) {
				unset($graph_list[$item]);
			}
		}
		$_SESSION["sess_graph_view_list_graph_list"] = $graph_list;

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

	$sql_base = "FROM (graph_templates_graph,graph_local)
		$sql_join
		$sql_where
		" . (empty($sql_where) ? "WHERE" : "AND") . "   graph_templates_graph.local_graph_id > 0
		AND graph_templates_graph.local_graph_id=graph_local.id
		AND graph_templates_graph.title_cache like '%%" . get_request_var_request("filter") . "%%'
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
		graph_templates_graph.title_cache,
		graph_templates_graph.image_format_id
		$sql_base
		GROUP BY graph_templates_graph.local_graph_id
		ORDER BY graph_templates_graph.title_cache
		LIMIT " . (get_request_var_request("graphs")*(get_request_var_request("page")-1)) . "," . get_request_var_request("graphs"));

	/* include graph view filter selector */
	graph_view_filter_table("preview");

	/* include time span selector */
	if (read_graph_config_option("timespan_sel") == CHECKED) {
		graph_view_timespan_selector("preview");
	}

	?>
	<script type='text/javascript'>
	<!--
	function pageChange(page) {
		strURL = '?action=ajax_preview&page=' + page;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
	}
	//-->
	</script>
	<?php

	html_start_box("", "100", $colors["header"], "0", "center", "");

	print "<table cellpadding='0' cellspacing='0' style='width:100%;border:1px solid #BEBEBE;'>\n";
	/* generate page list */

	if ($total_rows > get_request_var_request("graphs")) {
		$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, $_REQUEST["graphs"], $total_rows, "pageChange");

		$nav = "\t\t\t<tr class='rowHeader'>
				<td colspan='11'>
					<table width='100%' cellspacing='0' cellpadding='0' border='0'>
						<tr>
							<td align='left' style='width:100px;' class='textHeaderDark'>";
		if ($_REQUEST["page"] > 1) { $nav .= "<strong><a class='linkOverDark' href='#' onClick='pageChange(" . ($_REQUEST["page"]-1) . ")'>&lt;&lt;&nbsp;" . __("Previous") . "</a></strong>"; }
		$nav .= "</td>\n
							<td align='center' class='textHeaderDark'>
								" . __("Showing Graphs") . ((get_request_var_request("graphs")*(get_request_var_request("page")-1))+1) . " " . __("to") . " " . ((($total_rows < get_request_var_request("graphs")) || ($total_rows < (get_request_var_request("graphs")*get_request_var_request("page")))) ? $total_rows : (get_request_var_request("graphs")*get_request_var_request("page"))) . " " . __("of") . " $total_rows [$url_page_select]
							</td>\n
							<td align='right' style='width:100px;' class='textHeaderDark'>";
		if (($_REQUEST["page"] * $_REQUEST["graphs"]) < $total_rows) { $nav .= "<strong><a class='linkOverDark' href='#' onClick='pageChange(" . ($_REQUEST["page"]+1) . ")'>" . __("Next") . "&gt;&gt;</a></strong>"; }
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
								" . __("Showing All Graphs") . (strlen(get_request_var_request("filter")) ? " [ " . __("Filter") . " '" . get_request_var_request("filter") . "' " . __("Applied") . " ]" : "") . "
							</td>
						</tr>
					</table>
				</td>
			</tr>\n";
	}

	print $nav;

	if (read_graph_config_option("thumbnail_section_preview") == CHECKED) {
		html_graph_thumbnail_area($graphs, "","graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
	}else{
		html_graph_area($graphs, "", "graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
	}

	print $nav;

	html_graph_end_box();
}

function get_graph_tree_items() {
	include_once(dirname(__FILE__) . "/../../lib/html_tree.php");

	/* Make sure nothing is cached */
	header("Cache-Control: must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

	switch(get_request_var_request("type")) {
	case "list":
		/* parse the id string
		 * prototypes:
		 * tree_id, tree_id_leaf_id, tree_id_leaf_id_hgd_dq
		 * tree_id_leaf_id_hgd_dqi, tree_id_leaf_id_hgd_gt
		 */
		$tree_id         = 0;
		$leaf_id         = 0;
		$host_group_type = array('na', 0);

		if (isset($_REQUEST["id"])) {
			$id_array = explode("_", $_REQUEST["id"]);
			$type     = "";

			if (sizeof($id_array)) {
				foreach($id_array as $part) {
					if (is_numeric($part)) {
						switch($type) {
							case "tree":
								$tree_id = $part;
								break;
							case "leaf":
								$leaf_id = $part;
								break;
							case "dqi":
								$host_group_type = array("dqi", $part);
								break;
							case "dq":
								$host_group_type = array("dq", $part);
								break;
							case "gt":
								$host_group_type = array("gt", $part);
								break;
							default:
								break;
						}
					}else{
						$type = trim($part);
					}
				}
			}
		}

		//cacti_log("tree_id: '" . $tree_id . ", leaf_id: '" . $leaf_id . ", hgt: '" . $host_group_type[0] . "," . $host_group_type[1] . "'", false);
		$tree_items = get_tree_leaf_items($tree_id, $leaf_id, $host_group_type);

		if (sizeof($tree_items)) {
			$total_items = sizeof($tree_items);

			$i = 0;
			echo "[\n";
			foreach($tree_items as $item) {
				$node_id  = "tree_" . $item["tree_id"];
				$node_id .= "_leaf_" . $item["leaf_id"];
				switch ($item["type"]) {
					case "tree":
						$children = true;
						$icon     = "";
						break;
					case "graph":
						$children = false;
						$icon     = CACTI_BASE_PATH . "/images/tree_icons/graph.gif";
						break;
					case "host":
						if (read_graph_config_option("expand_hosts") == CHECKED) {
							$children = true;
						}else{
							$children = false;
						}
						$icon     = CACTI_BASE_PATH . "/images/tree_icons/host.gif";
						break;
					case "header":
						$children = true;
						$icon     = "";
						break;
					case "dq":
						$children = true;
						$icon     = "";
						$node_id .= "_" . $item["type"] . "_" . $item["id"];
						$icon     = CACTI_BASE_PATH . "/images/tree_icons/dataquery.png";
						break;
					case "dqi":
						$children = false;
						$icon     = "";
						$node_id .= "_" . $item["type"] . "_" . $item["id"];
						break;
					case "gt":
						$children = false;
						$node_id .= "_" . $item["type"] . "_" . $item["id"];
						$icon     = CACTI_BASE_PATH . "/images/tree_icons/template.png";
						break;
					default:
				}
				echo "{\n";
				echo "\tattributes: {\n";
				echo "\t\tid :  '" . $node_id . "'\n";
				echo "\t},\n";
				if($children) echo "\tstate: 'closed', \n";
				echo "\tdata: {\n";
				echo "\t\t'en' : { title : '".$item["name"] ."'" . ($icon != '' ? ", icon : '" . $icon . "'" : "") ." }";
				echo "\n";
				echo "\t}\n";
				echo "}";
				if(++$i < $total_items) echo ",";
				echo "\n";
			}
		}
		echo "\n]";
		break;
	case "loadfile":
		break;
	case "savefile":
		break;
	}

	exit();
}

function get_graph_tree_graphs() {
	include_once(dirname(__FILE__) . "/../../lib/html_tree.php");
	include_once(CACTI_BASE_PATH . "/lib/timespan_settings.php");

	/* Make sure nothing is cached */
	header("Cache-Control: must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: ". gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")))." GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s")." GMT");

	/* parse the id string
	 * prototypes:
	 * tree_id, tree_id_leaf_id, tree_id_leaf_id_hgd_dq
	 * tree_id_leaf_id_hgd_dqi, tree_id_leaf_id_hgd_gt
	 */
	$tree_id         = 0;
	$leaf_id         = 0;
	$host_group_type = array('na', 0);

	if (!isset($_REQUEST["id"])) {
		if (isset($_SESSION["sess_graph_navigation"])) {
			$_REQUEST["id"] = $_SESSION["sess_graph_navigation"];
		}
	}

	if (isset($_REQUEST["id"])) {
		$_SESSION["sess_graph_navigation"] = $_REQUEST["id"];
		$id_array = explode("_", $_REQUEST["id"]);
		$type     = "";

		if (sizeof($id_array)) {
			foreach($id_array as $part) {
				if (is_numeric($part)) {
					switch($type) {
						case "tree":
							$tree_id = $part;
							break;
						case "leaf":
							$leaf_id = $part;
							break;
						case "dqi":
							$host_group_type = array("dqi", $part);
							break;
						case "dq":
							$host_group_type = array("dq", $part);
							break;
						case "gt":
							$host_group_type = array("gt", $part);
							break;
						default:
							break;
					}
				}else{
					$type = trim($part);
				}
			}
		}
	}

	get_graph_tree_content($tree_id, $leaf_id, $host_group_type);

	exit();
}

function graph_view_timespan_selector($mode = "tree") {
	global $graph_timespans, $graph_timeshifts, $colors, $config;

	?>
	<script type='text/javascript'>
	<!--
	// Initialize the calendar
	calendar=null;

	// This function displays the calendar associated to the input field 'id'
	function showCalendar(id) {
		var el = document.getElementById(id);
		if (calendar != null) {
			// we already have some calendar created
			calendar.hide();  // so we hide it first.
		} else {
			// first-time call, create the calendar.
			var cal = new Calendar(true, null, selected, closeHandler);
			cal.weekNumbers = false;  // Do not display the week number
			cal.showsTime = true;     // Display the time
			cal.time24 = true;        // Hours have a 24 hours format
			cal.showsOtherMonths = false;    // Just the current month is displayed
			calendar = cal;                  // remember it in the global var
			cal.setRange(1900, 2070);        // min/max year allowed.
			cal.create();
		}

		calendar.setDateFormat('%Y-%m-%d %H:%M');    // set the specified date format
		calendar.parseDate(el.value);                // try to parse the text in field
		calendar.sel = el;                           // inform it what input field we use

		// Display the calendar below the input field
		calendar.showAtElement(el, "Br");        // show the calendar

		return false;
	}

	// This function update the date in the input field when selected
	function selected(cal, date) {
		cal.sel.value = date;      // just update the date in the input field.
	}

	// This function gets called when the end-user clicks on the 'Close' button.
	// It just hides the calendar without destroying it.
	function closeHandler(cal) {
		cal.hide();                        // hide the calendar
		calendar = null;
	}

	request_type = 'preset';

	function applyTimespanFilterChange(objForm) {
		<?php if ($mode == 'tree') { ?>
		if (request_type == 'preset') {
			strURL = '?action=ajax_tree_graphs&predefined_timespan=' + objForm.predefined_timespan.value;
		}else{
			strURL = '?action=ajax_tree_graphs&date1=' + objForm.date1.value;
			strURL = strURL + '&date2=' + objForm.date2.value;
		}
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
		<?php }else{ ;?>
		if (request_type == 'preset') {
			strURL = '?action=ajax_preview&predefined_timespan=' + objForm.predefined_timespan.value;
		}else{
			strURL = '?action=ajax_preview&date1=' + objForm.date1.value;
			strURL = strURL + '&date2=' + objForm.date2.value;
		}
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
		<?php } ;?>
	}

	function clearTimespanFilter(objForm) {
		<?php if ($mode == 'tree') { ?>
		strURL = '?action=ajax_tree_graphs&button_clear_x=true';
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
		<?php }else{ ;?>
		strURL = '?action=ajax_preview&button_clear_x=true';
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
		<?php } ;?>
	}

	function timeShift(objForm, direction) {
		<?php if ($mode == 'tree') { ?>
		strURL = '?action=ajax_tree_graphs&move_' + direction + '=true';
		strURL = strURL + '&predefined_timeshift=' + objForm.predefined_timeshift.value;
		strURL = strURL + '&date1=' + objForm.date1.value;
		strURL = strURL + '&date2=' + objForm.date2.value;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
		<?php }else{ ;?>
		strURL = '?action=ajax_preview&move_' + direction + '=true';
		strURL = strURL + '&predefined_timeshift=' + objForm.predefined_timeshift.value;
		strURL = strURL + '&date1=' + objForm.date1.value;
		strURL = strURL + '&date2=' + objForm.date2.value;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graph_content").html(data);
		});
		<?php } ;?>
	}

	//-->
	</script>
	<?php
	html_start_box("", "100", $colors["header"], "0", "center", "");
	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_timespan_selector" method="post" action="graph_view.php">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr class="rowGraphFilter">
					<td class="nw50">
						&nbsp;<?php print __("Presets:");?>&nbsp;
					</td>
					<td class="nw120">
						<select name='predefined_timespan' onChange='request_type="preset";applyTimespanFilterChange(document.form_timespan_selector)'><?php
							if ($_SESSION["custom"]) {
								$graph_timespans[GT_CUSTOM] = __("Custom");
								$start_val = 0;
								$end_val = sizeof($graph_timespans);
							} else {
								if (isset($graph_timespans[GT_CUSTOM])) {
									asort($graph_timespans);
									array_shift($graph_timespans);
								}
								$start_val = 1;
								$end_val = sizeof($graph_timespans)+1;
							}

							if (sizeof($graph_timespans) > 0) {
								for ($value=$start_val; $value < $end_val; $value++) {
									print "\t\t\t\t\t\t\t<option value='$value'"; if ($_SESSION["sess_current_timespan"] == $value) { print " selected"; } print ">" . title_trim($graph_timespans[$value], 40) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td class='nw30'>
						&nbsp;<?php print __("From:");?>&nbsp;
					</td>
					<td class='nw140'>
						<input type='text' name='date1' id='date1' title='<?php print __("Graph Begin Timestamp");?>' size='16' value='<?php print (isset($_SESSION["sess_current_date1"]) ? $_SESSION["sess_current_date1"] : "");?>'>
						&nbsp;<input type='image' class='img_filter' src='images/calendar.gif' alt='<?php print __("Start");?>' title='<?php print __("Start Date Selector");?>' onclick='return showCalendar("date1");'>&nbsp;
					</td>
					<td class='nw30'>
						&nbsp;<?php print __("To:");?>&nbsp;
					</td>
					<td class='nw140'>
						<input type='text' name='date2' id='date2' title='<?php print __("Graph End Timestamp");?>' size='16' value='<?php print (isset($_SESSION["sess_current_date2"]) ? $_SESSION["sess_current_date2"] : "");?>'>
						&nbsp;<input type='image' class='img_filter' src='images/calendar.gif' alt='<?php print __("End");?>' title='<?php print __("End Date Selector");?>' onclick='return showCalendar("date2");'>
					</td>
					<td class='nw140'>
						&nbsp;&nbsp;<img onMouseOver='this.style.cursor="pointer"' onClick='return timeShift(document.form_timespan_selector, "left")' class='img_filter' name='move_left' src='images/move_left.gif' alt='<?php print __("Left");?>' title='<?php print __("Shift Left");?>'>
						<select name='predefined_timeshift' title='<?php print __("Define Shifting Interval");?>'><?php
							$start_val = 1;
							$end_val = sizeof($graph_timeshifts)+1;
							if (sizeof($graph_timeshifts) > 0) {
								for ($shift_value=$start_val; $shift_value < $end_val; $shift_value++) {
									print "\t\t\t\t\t\t\t<option value='$shift_value'"; if ($_SESSION["sess_current_timeshift"] == $shift_value) { print " selected"; } print ">" . title_trim($graph_timeshifts[$shift_value], 40) . "</option>\n";
								}
							}
							?>
						</select>
						<img onMouseOver='this.style.cursor="pointer"' onClick='return timeShift(document.form_timespan_selector, "right")' class='img_filter' name='move_right' src='images/move_right.gif' alt='<?php print __("Right");?>' title='<?php print __("Shift Right");?>'>
					</td>
					<td class="nw120">
						&nbsp;<input type='button' value='<?php print __("Refresh");?>' name='button_refresh' onclick='request_type="daterange";applyTimespanFilterChange(document.form_timespan_selector)'>
						<input type='button' value='<?php print __("Clear");?>' name='button_clear_x' onclick='clearTimespanFilter()'>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<?php

	html_graph_end_box(FALSE);
}

function graph_view_tree_filter() {
	global $colors;

	load_current_session_value("tree_id", "sess_graph_view_tree_id", "-2");

	$trees = db_fetch_assoc("SELECT * FROM graph_tree WHERE user_id=" . $_SESSION["sess_user_id"] . " OR user_id=0 ORDER BY user_id, name");

	?>
	<table class="startBoxHeader wp100 startBox0"  cellspacing=0 cellpadding=0>
		<tr class="rowGraphFilter noprint">
			<td class="noprint">
				<form name="form_graph_tree" method="get" action="graph_view.php">
					<table cellspacing="1" cellpadding="0">
						<tr>
							<td class="w1">
								&nbsp;<?php print __("Trees:");?>&nbsp;
							</td>
							<td class="w1">
								<select id='tree' onchange='window.location.assign("graph_view.php?parent=true&tree_id="+document.getElementById("tree").value)' name='tree'>
									<option value='-2'<?php if ($_REQUEST["tree_id"] == "-2") {?> selected<?php }?>>System Trees</option><?php
									if (sizeof($trees)) {
										if (user_authorized("19")) {
											print "<option value='-1'" . ($_REQUEST["tree_id"] == "-1" ? " selected":"") . ">User Trees</option>";
										}
										foreach($trees as $tree) {
											print "<option value='" . $tree["id"] . "'" . ($_REQUEST["tree_id"] == $tree["id"] ? " selected":"") . ">" . $tree["name"] . ($tree["user_id"] == 0 ? " (System)":" (User)") . "</option>";
										}
									}?>
								</select>
							</td>
							<td class='nw'><?php if (user_authorized("19")) {?>
								<input type='button' value='Manage' onclick='window.location.assign("tree_manage.php?tree_id=<?php print $tree["id"];?>")'><?php }?>
							</td>
						</tr>
					</table>
					<table valign='top' cellpadding=0 cellspacing=0 width='100%'>
						<tr class="rowHeader">
							<td class="textHeaderDark">
								&nbsp;<?php print __("Items");?>&nbsp;
							</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
	</table>
	<?php
}

function graph_view_search_filter() {
	global $graphs_per_page;
	global $colors;

	?>
	<script type='text/javascript'>
	<!--

	function applyFilter(objForm) {
		strURL = '?action=ajax_tree_graphs&filter=' + objForm.filter.value;
		strURL = strURL + '&graphs=' + objForm.graphs.value;
		strURL = strURL + '&thumbnails=' + objForm.thumbnails.checked;
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
	}

	function clearFilter(objForm) {
		strURL = '?action=ajax_tree_graphs&clear_filter=true';
		$.get("graph_view.php" + strURL, function (data) {
			$("#graphs").html(data);
		});
	}

	//-->
	</script>
	<?php

	html_start_box("", "100", $colors["header"], "0", "center", "");
	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_graph_view" method="get" action="graph_view.php">
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nw50">
							&nbsp;<?php print __("Search:");?>&nbsp;
						</td>
						<td class="nw120">
							<input type='text' style='display:none;' name='workaround'>
							<input size='30' style='width:100;' name='filter' value='<?php print clean_html_output(get_request_var_request("filter"));?>' onChange='applyFilter(document.form_graph_view)'>
						</td>
						<td class="nw100">
							&nbsp;<?php print __("Graphs/Page:");?>&nbsp;
						</td>
						<td class="w1">
							<select name="graphs" onChange="applyFilter(document.form_graph_view)">
								<?php
								if (sizeof($graphs_per_page) > 0) {
								foreach ($graphs_per_page as $key => $value) {
									print "\t\t\t\t\t\t\t<option value='" . $key . "'"; if ((isset($_REQUEST["graphs"])) && ($_REQUEST["graphs"] == $key)) { print " selected"; } print ">" . $value . "</option>\n";
								}
								}
								?>
							</select>
						</td>
						<td width="40">
							<label for="thumbnails">&nbsp;<?php print __("Thumbnails:");?>&nbsp;</label>
						</td>
						<td>
							<input type="checkbox" name="thumbnails" id="thumbnails" onChange="applyFilter(document.form_graph_view);" <?php print ((isset($_REQUEST['thumbnails'])) && ($_REQUEST['thumbnails'] == "true") ? "checked":"");?>>
						</td>
						<td class='nw'>
							&nbsp;<input type='button' value='<?php print __("Refresh");?>' name='refresh' onClick='applyFilter(document.form_graph_view)'>
							<input type='button' value='<?php print __("Clear");?>' name='clear_x' onClick='clearFilter(document.form_graph_view)'>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
	<?php

	html_graph_end_box();
}
