<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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

function grow_graph_tree($graph_tree_id, $start_branch, $user_id, $options) {
	global $colors, $current_user;

	require_once(CACTI_BASE_PATH . "/lib/sys/auth.php");
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");

	$search_key = "";
	$already_open = false;
	$hide_until_depth = false;
	$graph_ct = 0;
	$sql_where = "";
	$sql_join = "";

	/* get the "starting leaf" if the user clicked on a specific branch */
	if (($start_branch != "") && ($start_branch != "0")) {
		$graph_tree = api_graph_tree_get($start_branch);

		$search_key = substr($graph_tree["order_key"], 0, (api_graph_tree_item_depth_get($graph_tree["order_key"]) * CHARS_PER_TIER));
	}

	/* graph permissions */
	if (read_config_option("auth_method") != "0") {
		/* get policy information for the sql where clause */
		//$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);
		//$sql_where = (empty($sql_where) ? "" : "and (" . $sql_where . " OR graph_tree_items.local_graph_id=0)");
		//$sql_join = "left join graph_local on (graph_templates_graph.local_graph_id=graph_local.id)
		//	left join graph_templates on (graph_templates.id=graph_local.graph_template_id)
		//	left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}

	/* include time span selector */
	if (read_graph_config_option("timespan_sel") == "on") {
		html_graph_start_box(3, false);
		require("./include/html/inc_timespan_selector.php");
		html_graph_end_box();
		print "<br>";
	}

	/* NOTE: GRAPH PERMISSIONS HAVE NOT BEEN IMPLEMENTED */
	$graph_tree_items = api_graph_tree_item_list($graph_tree_id, "", $start_branch, true, true);

	/*
	$heirarchy = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.local_graph_id,
		graph_tree_items.rra_id,
		graph_tree_items.host_id,
		graph_tree_items.order_key,
		graph_templates_graph.title_cache as graph_title,
		CONCAT_WS('',host.description,' (',host.hostname,')') as hostname,
		settings_tree.status
		from graph_tree_items
		left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
		left join settings_tree on (graph_tree_items.id=settings_tree.graph_tree_item_id and settings_tree.user_id=$user_id)
		left join host on (graph_tree_items.host_id=host.id)
		$sql_join
		where graph_tree_items.graph_tree_id=$tree_id
		and graph_tree_items.order_key like '$search_key%'
		$sql_where
		order by graph_tree_items.order_key");
	*/

	html_graph_start_box(0, true);

	print "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td colspan='30'><table cellspacing='0' cellpadding='3' width='100%'><tr><td class='textHeaderDark'><strong><a class='linkOverDark' href='graph_view.php?action=tree&tree_id=" . $_SESSION["sess_view_tree_id"] . "'>[root]</a> - " . db_fetch_cell("select name from graph_tree where id=" . $_SESSION["sess_view_tree_id"]) . "</strong></td></tr></table></td></tr>";

	$i = 0;

	/* loop through each tree item */
	if (sizeof($graph_tree_items) > 0) {
		foreach ($graph_tree_items as $graph_tree_item) {
			/* find out how 'deep' this item is */
			$current_depth = api_graph_tree_item_depth_get($graph_tree_item["order_key"]);

			/* find the type of the current branch */
			//if ($leaf["title"] != "") { $current_leaf_type = "heading"; }elseif (!empty($leaf["local_graph_id"])) { $current_leaf_type = "graph"; }else{ $current_leaf_type = "host"; }

			/* find the type of the next branch. make sure the next item exists first */
			if (isset($graph_tree_items{$i+1})) {
				$next_leaf_type = $graph_tree_items{$i+1}["item_type"];
			}else{
				$next_leaf_type = "";
			}

			if ((($current_leaf_type == TREE_ITEM_TYPE_HEADER) || ($current_leaf_type == TREE_ITEM_TYPE_HOST)) && (($current_depth <= $hide_until_depth) || ($hide_until_depth == false))) {
				$current_title = (($current_leaf_type == TREE_ITEM_TYPE_HEADER) ? $graph_tree_item["item_value"] : $graph_tree_item["host_hostname"]);

				/* draw heading */
				draw_tree_header_row($graph_tree_id, $graph_tree_item["id"], $current_depth, $current_title, true, "0", true);

				/* this is an open host, lets expand a bit */
				if ($current_leaf_type == TREE_ITEM_TYPE_HOST) {
					/* get a list of all graph templates in use by this host */
					$graph_templates = db_fetch_assoc("select
						graph_templates.id,
						graph_templates.name
						from graph_local,graph_templates,graph_templates_graph
						where graph_local.id=graph_templates_graph.local_graph_id
						and graph_templates_graph.graph_template_id=graph_templates.id
						and graph_local.host_id=" . $leaf["host_id"] . "
						group by graph_templates.id
						order by graph_templates.name");

					if (sizeof($graph_templates) > 0) {
					foreach ($graph_templates as $graph_template) {
						draw_tree_header_row($tree_id, $leaf["id"], ($tier+1), $graph_template["name"], false, $leaf["status"], false);

						/* get a list of each graph using this graph template for this particular host */
						$graphs = db_fetch_assoc("select
							graph_templates_graph.title_cache,
							graph_templates_graph.local_graph_id
							from graph_local,graph_templates,graph_templates_graph
							where graph_local.id=graph_templates_graph.local_graph_id
							and graph_templates_graph.graph_template_id=graph_templates.id
							and graph_local.graph_template_id=" . $graph_template["id"] . "
							and graph_local.host_id=" . $leaf["host_id"] . "
							order by graph_templates_graph.title_cache");

						$graph_ct = 0;
						if (sizeof($graphs) > 0) {
						foreach ($graphs as $graph) {
							/* incriment graph counter so we know when to start a new row or not */
							$graph_ct++;

							if (!isset($graphs[$graph_ct])) { $next_leaf_type = "heading"; }else{ $next_leaf_type = "graph"; }

							/* draw graph */
							$already_open = draw_tree_graph_row($already_open, $graph_ct, $next_leaf_type, ($tier+2), $graph["local_graph_id"], 1, $graph["title_cache"]);
						}
						}
					}
					}
				}

				$graph_ct = 0;
			}elseif (($current_leaf_type == 'graph') && (($tier <= $hide_until_tier) || ($hide_until_tier == false))) {
				/* incriment graph counter so we know when to start a new row or not */
				$graph_ct++;

				/* draw graph */
				$already_open = draw_tree_graph_row($already_open, $graph_ct, $next_leaf_type, $tier, $leaf["local_graph_id"], $leaf["rra_id"], $leaf["graph_title"]);
			}

			/* if we have come back to the tier that was origionally flagged, then take away the flag */
			if (($tier <= $hide_until_tier) && ($hide_until_tier != false)) {
				$hide_until_tier = false;
			}

			/* if we are supposed to hide this branch, flag it */
			if (($leaf["status"] == "1") && ($hide_until_tier == false)) {
				$hide_until_tier = $tier;
			}

			$i++;
		}
	}

	print "</tr></table></td></tr>";

	html_graph_end_box();
}

function html_tree_dropdown_draw($graph_tree_id, $field_name, $field_value) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	$tree_items = api_graph_tree_item_list($graph_tree_id, array("item_type" => TREE_ITEM_TYPE_HEADER));

	echo "<select name='$field_name'>\n<option value='0'>[root]</option>\n";

	if ((is_array($tree_items)) && (sizeof($tree_items) > 0)) {
		foreach ($tree_items as $tree_item) {
			$current_depth = api_graph_tree_item_depth_get($tree_item["order_key"]);
			$indent = str_repeat("---", ($current_depth));

			if ($field_value == $tree_item["id"]) {
				$html_selected = " selected";
			}else{
				$html_selected = "";
			}

			echo "<option value='" . $tree_item["id"] . "'$html_selected>$indent " . $tree_item["item_value"] . "</option>\n";
		}
	}

	echo "</select>\n";
}

function grow_dhtml_trees() {
	global $colors;

	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");

	?>
	<script type="text/javascript">
	<!--
	USETEXTLINKS = 1
	STARTALLOPEN = 0
	USEFRAMES = 0
	USEICONS = 0
	WRAPTEXT = 1
	PERSERVESTATE = 1
	HIGHLIGHT = 1
	<?php
	/* get current time */
	list($micro,$seconds) = split(" ", microtime());
	$current_time = $seconds + $micro;
	$expand_hosts = read_graph_config_option("expand_hosts");

	if (!isset($_SESSION['dhtml_tree'])) {
		$dhtml_tree = create_dhtml_tree();
		$_SESSION['dhtml_tree'] = $dhtml_tree;
	}else{
		$dhtml_tree = $_SESSION['dhtml_tree'];
		if (($dhtml_tree[0] + read_graph_config_option("page_refresh") < $current_time) || ($expand_hosts != $dhtml_tree[2])) {
			$dhtml_tree = create_dhtml_tree();
			$_SESSION['dhtml_tree'] = $dhtml_tree;
		}else{
			$dhtml_tree = $_SESSION['dhtml_tree'];
		}
	}

	$total_tree_items = sizeof($dhtml_tree) - 1;

	for ($i = 2; $i <= $total_tree_items; $i++) {
		print $dhtml_tree[$i];
	}
	?>
	foldersTree.treeID = "t2";
	//-->
	</script>
	<?php
}

function create_dhtml_tree() {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

	/* Record Start Time */
	list($micro,$seconds) = split(" ", microtime());
	$start = $seconds + $micro;

	$dhtml_tree = array();

	$dhtml_tree[0] = $start;
	$dhtml_tree[1] = read_graph_config_option("expand_hosts");
	$dhtml_tree[2] = "foldersTree = gFld(\"\", \"\")\n";
	$i = 2;

	$tree_list = get_graph_tree_array();

	/* auth check for hosts on the trees */
	if (read_config_option("auth_method") != "0") {
		$current_user = db_fetch_row("select policy_hosts from user_auth where id=" . $_SESSION["sess_user_id"]);

		$sql_join = "left join user_auth_perms on (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")";

		if ($current_user["policy_hosts"] == "1") {
			$sql_where = "and !(user_auth_perms.user_id is not null and graph_tree_items.host_id > 0)";
		}elseif ($current_user["policy_hosts"] == "2") {
			$sql_where = "and !(user_auth_perms.user_id is null and graph_tree_items.host_id > 0)";
		}
	}else{
		$sql_join = "";
		$sql_where = "";
	}

	if (sizeof($tree_list) > 0) {
		foreach ($tree_list as $tree) {
			$i++;
			$heirarchy = db_fetch_assoc("select
				graph_tree_items.id,
				graph_tree_items.title,
				graph_tree_items.order_key,
				graph_tree_items.host_id,
				graph_tree_items.host_grouping_type,
				host.description as hostname
				from graph_tree_items
				left join host on (host.id=graph_tree_items.host_id)
				$sql_join
				where graph_tree_items.graph_tree_id=" . $tree["id"] . "
				$sql_where
				and graph_tree_items.local_graph_id = 0
				order by graph_tree_items.order_key");

			$dhtml_tree[$i] = "ou0 = insFld(foldersTree, gFld(\"" . $tree["name"] . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "\"))\n";

			if (sizeof($heirarchy) > 0) {
				foreach ($heirarchy as $leaf) {
					$i++;
					$tier = api_graph_tree_item_depth_get($leaf["order_key"]);

					if ($leaf["host_id"] > 0) {
						$dhtml_tree[$i] = "ou" . ($tier) . " = insFld(ou" . ($tier-1) . ", gFld(\"<strong>Device:</strong> " . addslashes($leaf["hostname"]) . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "\"))\n";
						if (read_graph_config_option("expand_hosts") == "on") {
							if ($leaf["host_grouping_type"] == HOST_GROUPING_GRAPH_TEMPLATE) {
								$graph_templates = db_fetch_assoc("select
									graph_template.id,
									graph_template.template_name
									from graph,graph_template
									where graph.graph_template_id=graph_template.id
									and graph.host_id = " . $leaf["host_id"] . "
									group by graph_template.id
									order by graph_template.template_name");

								if (sizeof($graph_templates) > 0) {
									foreach ($graph_templates as $graph_template) {
										$i++;
										$dhtml_tree[$i] = "ou" . ($tier+1) . " = insFld(ou" . ($tier) . ", gFld(\" " . addslashes($graph_template["template_name"]) . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "&host_group_data=graph_template:" . $graph_template["id"] . "\"))\n";
									}
								}
							}else if ($leaf["host_grouping_type"] == HOST_GROUPING_DATA_QUERY_INDEX) {
								$data_queries = db_fetch_assoc("select
									snmp_query.id,
									snmp_query.name
									from data_source,data_source_field,snmp_query
									where data_source.id=data_source_field.data_source_id
									and (data_source_field.value=snmp_query.id and data_source_field.name = 'data_query_id')
									and data_source.host_id = " . $leaf["host_id"] . "
									group by snmp_query.id
									order by snmp_query.name");

								array_push($data_queries, array(
									"id" => "0",
									"name" => "(Non Indexed)"
									));

								if (sizeof($data_queries) > 0) {
									foreach ($data_queries as $data_query) {
										$i++;
										$dhtml_tree[$i] = "ou" . ($tier+1) . " = insFld(ou" . ($tier) . ", gFld(\" " . addslashes($data_query["name"]) . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "&host_group_data=data_query:" . $data_query["id"] . "\"))\n";

										/* fetch a list of field names that are sorted by the preferred sort field */
										$sort_field_data = get_formatted_data_query_indexes($leaf["host_id"], $data_query["id"]);

										while (list($snmp_index, $sort_field_value) = each($sort_field_data)) {
											$i++;
											$dhtml_tree[$i] = "ou" . ($tier+2) . " = insFld(ou" . ($tier+1) . ", gFld(\" " . addslashes($sort_field_value) . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "&host_group_data=data_query_index:" . $data_query["id"] . ":" . urlencode($snmp_index) . "\"))\n";
										}
									}
								}
							}
						}
					}else{
						$dhtml_tree[$i] = "ou" . ($tier) . " = insFld(ou" . ($tier-1) . ", gFld(\"" . addslashes($leaf["title"]) . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "\"))\n";
					}
				}
			}
		}
	}

	return $dhtml_tree;
}

function grow_right_pane_tree($tree_id, $leaf_id, $host_group_data) {
	global $current_user, $colors;

	require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");

	if (empty($tree_id)) { return; }

	$sql_where = "";
	$sql_join = "";
	$title = "";
	$title_delimiter = "";
	$search_key = "";

	$leaf = db_fetch_row("select order_key,title,host_id,host_grouping_type from graph_tree_items where id=$leaf_id");
	$leaf_type = get_tree_item_type($leaf_id);

	/* get the "starting leaf" if the user clicked on a specific branch */
	if (!empty($leaf_id)) {
		$search_key = substr($leaf["order_key"], 0, (api_graph_tree_item_depth_get($leaf["order_key"]) * CHARS_PER_TIER));
	}

	/* graph permissions */
	if (read_config_option("auth_method") != "0") {
		/* get policy information for the sql where clause */
		$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);
		$sql_where = (empty($sql_where) ? "" : "and $sql_where");
		$sql_join = "
			left join host on (host.id=graph.host_id)
			left join graph_template on (graph_template.id=graph.graph_template_id)
			left join user_auth_perms on ((graph.id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_template.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}

	/* get information for the headers */
	if (!empty($tree_id)) { $tree_name = db_fetch_cell("select name from graph_tree where id=$tree_id"); }
	if (!empty($leaf_id)) { $leaf_name = $leaf["title"]; }
	if (!empty($leaf_id)) { $host_name = db_fetch_cell("select host.description from (graph_tree_items,host) where graph_tree_items.host_id=host.id and graph_tree_items.id=$leaf_id"); }

	$host_group_data_array = explode(":", $host_group_data);

	if ($host_group_data_array[0] == "graph_template") {
		$host_group_data_name = "<strong>" . _("Graph Template:") . "</strong> " . db_fetch_cell("select template_name from graph_template where id=" . $host_group_data_array[1]);
		$graph_template_id = $host_group_data_array[1];
	}elseif ($host_group_data_array[0] == "data_query") {
		$host_group_data_name = "<strong>" . _("Data Query:") . "</strong> " . (empty($host_group_data_array[1]) ? _("(Non Indexed)") : db_fetch_cell("select name from snmp_query where id=" . $host_group_data_array[1]));
		$data_query_id = $host_group_data_array[1];
	}elseif ($host_group_data_array[0] == "data_query_index") {
		$host_group_data_name = "<strong>" . _("Data Query:") . "</strong> " . (empty($host_group_data_array[1]) ? _("(Non Indexed)") : db_fetch_cell("select name from snmp_query where id=" . $host_group_data_array[1])) . "-> " . (empty($host_group_data_array[2]) ? "Unknown Index" : get_formatted_data_query_index($leaf["host_id"], $host_group_data_array[1], $host_group_data_array[2]));
		$data_query_id = $host_group_data_array[1];
		$data_query_index = $host_group_data_array[2];
	}

	if (!empty($tree_name)) { $title .= $title_delimiter . "<strong>" . _("Tree:") . "</strong> $tree_name"; $title_delimiter = "-> "; }
	if (!empty($leaf_name)) { $title .= $title_delimiter . "<strong>" . _("Leaf:") . "</strong> $leaf_name"; $title_delimiter = "-> "; }
	if (!empty($host_name)) { $title .= $title_delimiter . "<strong>" . _("Device:") . "</strong> $host_name"; $title_delimiter = "-> "; }
	if (!empty($host_group_data_name)) { $title .= $title_delimiter . " $host_group_data_name"; $title_delimiter = "-> "; }

	print "<table width='98%' align='center' cellpadding='3'>";

	/* include time span selector */
	if (read_graph_config_option("timespan_sel") == "on") {
		html_graph_start_box(3, false);
		require(CACTI_BASE_PATH . "/include/html/inc_timespan_selector.php");
		html_graph_end_box();
		print "<br>";
	}

	/* start graph display */
	html_graph_start_box(3, false);
	print "<tr bgcolor='#" . $colors["header_panel_background"] . "'><td width='390' colspan='3' class='textHeaderDark'>$title</td></tr>";

	if (($leaf_type == "header") || (empty($leaf_id))) {
		$heirarchy = db_fetch_assoc("select
			graph_tree_items.id,
			graph_tree_items.title,
			graph_tree_items.local_graph_id,
			graph_tree_items.rra_id,
			graph_tree_items.order_key,
			graph.title_cache
			from (graph_tree_items,graph)
			left join graph on (graph_tree_items.local_graph_id=graph.id)
			$sql_join
			where graph_tree_items.graph_tree_id=$tree_id
			and graph_tree_items.order_key like '$search_key" . str_repeat('_', CHARS_PER_TIER) . str_repeat('0', (MAX_TREE_DEPTH * CHARS_PER_TIER) - (strlen($search_key) + CHARS_PER_TIER)) . "'
			and graph_tree_items.local_graph_id>0
			$sql_where
			group by graph_tree_items.id
			order by graph_tree_items.order_key");

		if (read_graph_config_option("thumbnail_section_tree_2") == "on") {
			html_graph_thumbnail_area($heirarchy, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
		}else{
			html_graph_area($heirarchy, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end());
		}
	}elseif ($leaf_type == "host") {
		/* graph template grouping */
		if ($leaf["host_grouping_type"] == HOST_GROUPING_GRAPH_TEMPLATE) {
			$graph_templates = db_fetch_assoc("select
				graph_template.id,
				graph_template.template_name
				from graph,graph_template
				where graph.graph_template_id=graph_template.id
				and graph.host_id=" . $leaf["host_id"] . "
				" . (empty($graph_template_id) ? "" : "and graph_template.id=$graph_template_id") . "
				group by graph_template.id
				order by graph_template.template_name");

			/* for graphs without a template */
			array_push($graph_templates, array(
				"id" => "0",
				"template_name" => _("(No Graph Template)")
				));

			if (sizeof($graph_templates) > 0) {
				foreach ($graph_templates as $item) {
					$graphs = db_fetch_assoc("select
						graph.title_cache,
						graph.id as graph_id
						from graph
						$sql_join
						where graph.graph_template_id=" . $item["id"] . "
						and graph.host_id=" . $leaf["host_id"] . "
						$sql_where
						order by graph.title_cache");

					if (read_graph_config_option("thumbnail_section_tree_2") == "on") {
						html_graph_thumbnail_area($graphs, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'><strong>" . _("Graph Template:") . "</strong> " . $item["template_name"] . "</td></tr>");
					}else{
						html_graph_area($graphs, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'><strong>" . _("Graph Template:") . "</strong> " . $item["template_name"] . "</td></tr>");
					}
				}
			}
		/* data query index grouping */
		}elseif ($leaf["host_grouping_type"] == HOST_GROUPING_DATA_QUERY_INDEX) {
			$data_sources = db_fetch_assoc("select
				graph.id as graph_id,
				graph.title_cache as graph_title,
				data_source.data_input_type,
				data_source.id as data_source_id
				from graph,graph_item,data_source_item,data_source
				where graph.id=graph_item.graph_id
				and graph_item.data_source_item_id=data_source_item.id
				and data_source_item.data_source_id=data_source.id
				and graph.host_id = " . $leaf["host_id"] . "");

			$index_list = array();

			if (sizeof($data_sources) > 0) {
				foreach ($data_sources as $item) {
					if ($item["data_input_type"] == DATA_INPUT_TYPE_DATA_QUERY) {
						$field_list = array_rekey(db_fetch_assoc("select name,value from data_source_field where data_source_id = ". $item["data_source_id"] . " and (name = 'data_query_id' or name = 'data_query_index' or name = 'data_query_field_name' or name = 'data_query_field_value')"), "name", "value");

						if ((isset($field_list["data_query_id"])) && (isset($field_list["data_query_index"]))) {
							if ( !(((isset($data_query_id)) && ($data_query_id != $field_list["data_query_id"])) || ((isset($data_query_index)) && ($data_query_index != $field_list["data_query_index"]))) ) {
								$index_list{$field_list["data_query_id"]}{$field_list["data_query_index"]}{$item["graph_id"]} = $item["graph_title"];
							}
						}
					}else{
						$index_list[0][0]{$item["graph_id"]} = $item["graph_title"];
					}
				}
			}

			while (list($data_query_id, $graph_list) = each($index_list)) {
				if (empty($data_query_id)) {
					print "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'><strong>" . _("(Non Indexed)") . "</strong></td></tr>";

					$index_graph_list = array();

					while (list($graph_id, $graph_title) = each($graph_list[0])) {
						/* reformat the array so it's compatable with the html_graph* area functions */
						array_push($index_graph_list, array("graph_id" => $graph_id, "title_cache" => $graph_title));
					}

					if (read_graph_config_option("thumbnail_section_tree_2") == "on") {
						html_graph_thumbnail_area($index_graph_list, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "");
					}else{
						html_graph_area($index_graph_list, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "");
					}
				}else{
					/* fetch a list of field names that are sorted by the preferred sort field */
					$sort_field_data = get_formatted_data_query_indexes($leaf["host_id"], $data_query_id);

					/* re-key the results on data query index */
					if (sizeof($graph_list) > 0) {
						print "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'><strong>" . _("Data Query:") . "</strong> " . db_fetch_cell("select name from snmp_query where id = $data_query_id") . "</td></tr>";
					}

					/* using the sorted data as they key; grab each snmp index from the master list */
					while (list($data_query_index, $sort_field_value) = each($sort_field_data)) {
						/* render each graph for the current data query index */
						if (isset($graph_list[$data_query_index])) {
							$index_graph_list = array();

							while (list($graph_id, $graph_title) = each($graph_list[$data_query_index])) {
								/* reformat the array so it's compatable with the html_graph* area functions */
								array_push($index_graph_list, array("graph_id" => $graph_id, "title_cache" => $graph_title));
							}

							if (read_graph_config_option("thumbnail_section_tree_2") == "on") {
								html_graph_thumbnail_area($index_graph_list, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'>$sort_field_value</td></tr>");
							}else{
								html_graph_area($index_graph_list, "", "view_type=tree&graph_start=" . get_current_graph_start() . "&graph_end=" . get_current_graph_end(), "<tr bgcolor='#" . $colors["graph_type_background"] . "'><td colspan='3' class='textHeaderDark'>$sort_field_value</td></tr>");
							}
						}
					}
				}
			}
		}
	}

	print "</table>";
}

function find_first_folder_url() {
	$tree_list = get_graph_tree_array();

	if (sizeof($tree_list) > 0) {
		$heirarchy = db_fetch_assoc("select
			graph_tree_items.id,
			graph_tree_items.host_id
			from graph_tree_items
			where graph_tree_items.graph_tree_id=" . $tree_list[0]["id"] . "
			and graph_tree_items.local_graph_id = 0
			order by graph_tree_items.order_key");

		if (sizeof($heirarchy) > 0) {
			return "graph_view.php?action=tree&tree_id=" . $tree_list[0]["id"] . "&leaf_id=" . $heirarchy[0]["id"] . "&select_first=true";
		}
	}

	return;
}

function draw_tree_header_row($tree_id, $tree_item_id, $current_tier, $current_title, $use_expand_contract, $expand_contract_status, $show_url) {
	global $colors;

	/* start the nested table for the heading */
	print "<tr><td colspan='2'><table width='100%' cellpadding='2' cellspacing='1' border='0'><tr>\n";

	/* draw one vbar for each tier */
	for ($j=0;($j<($current_tier-1));$j++) {
		print "<td width='10' bgcolor='#" . $colors["panel"] . "'></td>\n";
	}

	/* draw the '+' or '-' icons if configured to do so */
	if (($use_expand_contract) && (!empty($current_title))) {
		if ($expand_contract_status == "1") {
			$other_status = '0';
			$ec_icon = 'show.gif';
		}else{
			$other_status = '1';
			$ec_icon =  'hide.gif';
		}

		print "<td bgcolor='" . $colors["panel"] . "' align='center' width='1%'><a
			href='graph_view.php?action=tree&tree_id=$tree_id&hide=$other_status&branch_id=$tree_item_id'>
			<img src='" . html_get_theme_images_path($ec_icon) . "' border='0'></a></td>\n";
	}elseif (!($use_expand_contract) && (!empty($current_title))) {
		print "<td bgcolor='" . $colors["panel"] . "' width='10'></td>\n";
	}

	/* draw the actual cell containing the header */
	if (!empty($current_title)) {
		print "<td bgcolor='" . $colors["panel"] . "' NOWRAP><strong>
			" . (($show_url == true) ? "<a href='graph_view.php?action=tree&tree_id=$tree_id&start_branch=$tree_item_id'>" : "") . $current_title . (($show_url == true) ? "</a>" : "") . "&nbsp;</strong></td>\n";
	}

	/* end the nested table for the heading */
	print "</tr></table></td></tr>\n";
}

function draw_tree_graph_row($already_open, $graph_counter, $next_leaf_type, $current_tier, $local_graph_id, $rra_id, $graph_title) {
	global $colors;

	/* start the nested table for the graph group */
	if ($already_open == false) {
		print "<tr><td><table width='100%' cellpadding='2' cellspacing='1'><tr>\n";

		/* draw one vbar for each tier */
		for ($j=0;($j<($current_tier-1));$j++) {
			print "<td width='10' bgcolor='#" . $colors["panel"] . "'></td>\n";
		}

		print "<td><table width='100%' cellspacing='0' cellpadding='2'><tr>\n";

		$already_open = true;
	}

	/* print out the actual graph html */
	if (read_graph_config_option("thumbnail_section_tree_1") == "on") {
		print "<td><a href='graph.php?local_graph_id=$local_graph_id&rra_id=all'><img align='middle' alt='$graph_title'
			src='graph_image.php?local_graph_id=$local_graph_id&rra_id=$rra_id&graph_start=" . -(db_fetch_cell("select timespan from rra where id=$rra_id")) . '&graph_height=' .
			read_graph_config_option("default_height") . '&graph_width=' . read_graph_config_option("default_width") . "&graph_nolegend=true' border='0'></a></td>\n";

		/* if we are at the end of a row, start a new one */
		if ($graph_counter % read_graph_config_option("num_columns") == 0) {
			print "</tr><tr>\n";
		}
	}else{
		print "<td><a href='graph.php?local_graph_id=$local_graph_id&rra_id=all'><img src='graph_image.php?local_graph_id=$local_graph_id&rra_id=$rra_id' border='0' alt='$graph_title'></a></td>";
		print "</tr><tr>\n";
	}

	/* if we are at the end of the graph group, end the nested table */
	if ($next_leaf_type != "graph") {
		print "</tr></table></td>";
		print "</tr></table></td></tr>\n";

		$already_open = false;
	}

	return $already_open;
}

function draw_tree_dropdown($current_tree_id) {
	global $colors;

	$html = "";

	$tree_list = get_graph_tree_array();

	if (isset($_GET["tree_id"])) {
		$_SESSION["sess_view_tree_id"] = $current_tree_id;
	}

	/* if there is a current tree, make sure it still exists before going on */
	if ((!empty($_SESSION["sess_view_tree_id"])) && (db_fetch_cell("select id from graph_tree where id=" . $_SESSION["sess_view_tree_id"]) == "")) {
		$_SESSION["sess_view_tree_id"] = 0;
	}

	/* set a default tree if none is already selected */
	if (empty($_SESSION["sess_view_tree_id"])) {
		if (db_fetch_cell("select id from graph_tree where id=" . read_graph_config_option("default_tree_id")) > 0) {
			$_SESSION["sess_view_tree_id"] = read_graph_config_option("default_tree_id");
		}else{
			if (sizeof($tree_list) > 0) {
				$_SESSION["sess_view_tree_id"] = $tree_list[0]["id"];
			}
		}
	}

	/* make the dropdown list of trees */
	if (sizeof($tree_list) > 1) {
		$html ="<form name='form_tree_id'>
			<td valign='middle' height='30' bgcolor='#" . $colors["panel"] . "'>\n
				<table width='100%' cellspacing='0' cellpadding='0'>\n
					<tr>\n
						<td width='200' class='textHeader'>\n
							&nbsp;&nbsp;Select a Graph Hierarchy:&nbsp;\n
						</td>\n
						<td bgcolor='#" . $colors["panel"] . "'>\n
							<select name='cbo_tree_id' onChange='window.location=document.form_tree_id.cbo_tree_id.options[document.form_tree_id.cbo_tree_id.selectedIndex].value'>\n";

		foreach ($tree_list as $tree) {
			$html .= "<option value='graph_view.php?action=tree&tree_id=" . $tree["id"] . "'";
				if ($_SESSION["sess_view_tree_id"] == $tree["id"]) { $html .= " selected"; }
				$html .= ">" . $tree["name"] . "</option>\n";
			}

		$html .= "</select>\n";
		$html .= "</td></tr></table></td></form>\n";
	}elseif (sizeof($tree_list) == 1) {
		/* there is only one tree; use it */
		//print "	<td valign='middle' height='5' colspan='3' bgcolor='#" . $colors["panel"] . "'>";
	}

	return $html;
}

?>
