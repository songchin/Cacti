<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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

function grow_graph_tree($tree_id, $start_branch, $user_id, $options) {
	global $colors, $current_user, $config;
	
	include_once($config["library_path"] . "/tree.php");
	
	$search_key = "";
	$already_open = false;
	$hide_until_tier = false;
	$graph_ct = 0;
	$sql_where = "";
	$sql_join = "";
	
	/* get the "starting leaf" if the user clicked on a specific branch */
	if (($start_branch != "") && ($start_branch != "0")) {
		$search_key = preg_replace("/0+$/","",db_fetch_cell("select order_key from graph_tree_items where id=$start_branch"));
	}
	
	/* graph permissions */
	if (read_config_option("global_auth") == "on") {
		/* get policy information for the sql where clause */
		$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);
		$sql_where = (empty($sql_where) ? "" : "and (" . $sql_where . " OR graph_tree_items.local_graph_id=0)");
		$sql_join = "left join graph_local on graph_templates_graph.local_graph_id=graph_local.id
			left join graph_templates on graph_templates.id=graph_local.graph_template_id
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}
	
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
		left join host on graph_tree_items.host_id=host.id
		$sql_join
		where graph_tree_items.graph_tree_id=$tree_id
		and graph_tree_items.order_key like '$search_key%'
		$sql_where
		order by graph_tree_items.order_key");
    	
	print "<!-- <P>Building Heirarchy w/ " . sizeof($heirarchy) . " leaves</P>  -->\n";
	print "<br><table width='98%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center' cellpadding='0' cellspacing='2'>";
	print "<tr bgcolor='#" . $colors["header_panel"] . "'><td colspan='30'><table cellspacing='0' cellpadding='3' width='100%'><tr><td class='textHeaderDark'><strong><a class='linkOverDark' href='graph_view.php?action=tree&tree_id=" . $_SESSION["sess_view_tree_id"] . "'>[root]</a> - " . db_fetch_cell("select name from graph_tree where id=" . $_SESSION["sess_view_tree_id"]) . "</strong></td></tr></table></td></tr>";
	
	$i = 0;
	
	/* loop through each tree item */
	if (sizeof($heirarchy) > 0) {
	foreach ($heirarchy as $leaf) {
		/* find out how 'deep' this item is */
		$tier = tree_tier($leaf["order_key"], 2);
		
		/* find the type of the current branch */
		if ($leaf["title"] != "") { $current_leaf_type = "heading"; }elseif (!empty($leaf["local_graph_id"])) { $current_leaf_type = "graph"; }else{ $current_leaf_type = "host"; }
		
		/* find the type of the next branch. make sure the next item exists first */
		if (isset($heirarchy{$i+1})) {
			if ($heirarchy{$i+1}["title"] != "") { $next_leaf_type = "heading"; }elseif (!empty($heirarchy{$i+1}["local_graph_id"])) { $next_leaf_type = "graph"; }else{ $next_leaf_type = "host"; }
		}else{
			$next_leaf_type = "";
		}
		
		if ((($current_leaf_type == 'heading') || ($current_leaf_type == 'host')) && (($tier <= $hide_until_tier) || ($hide_until_tier == false))) {
			$current_title = (($current_leaf_type == "heading") ? $leaf["title"] : $leaf["hostname"]);
			
			/* draw heading */
			draw_tree_header_row($tree_id, $leaf["id"], $tier, $current_title, true, $leaf["status"], true);
			
			/* this is an open host, lets expand a bit */
			if (($current_leaf_type == "host") && ($leaf["status"] == "0")) {
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
	
	print "</tr></table></td></tr></table>";
}

function grow_edit_graph_tree($tree_id, $user_id, $options) {
	global $config, $colors;
	
	include_once($config["library_path"] . "/tree.php");
	
	$tree = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.local_graph_id,
		graph_tree_items.host_id,
		graph_tree_items.order_key,
		graph_templates_graph.title_cache as graph_title,
		CONCAT_WS('',description,' (',hostname,')') as hostname
		from graph_tree_items
		left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
		left join host on host.id=graph_tree_items.host_id
		where graph_tree_items.graph_tree_id=$tree_id
		order by graph_tree_items.order_key");
	
    	print "<!-- <P>Building Heirarchy w/ " . sizeof($tree) . " leaves</P>  -->\n";
    	
    	##  Here we go.  Starting the main tree drawing loop.
	
	$i = 0;
	if (sizeof($tree) > 0) {
	foreach ($tree as $leaf) {
	    	$tier = tree_tier($leaf["order_key"], 2);
	    	$transparent_indent = "<img width='" . (($tier-1) * 20) . "' height='1' align='middle' alt=''>&nbsp;";
		
		if ($i % 2 == 0) { $row_color = $colors["form_alternate1"]; }else{ $row_color = $colors["form_alternate2"]; } $i++;
		
	    	if ($leaf["local_graph_id"] > 0) {
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>$transparent_indent<a href='tree.php?action=item_edit&tree_id=" . $_GET["id"] . "&id=" . $leaf["id"] . "'>" . $leaf["graph_title"] . "</a></td>\n";
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>Graph</td>";
		}elseif ($leaf["title"] != "") {
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>$transparent_indent<a href='tree.php?action=item_edit&tree_id=" . $_GET["id"] . "&id=" . $leaf["id"] . "'><strong>" . $leaf["title"] . "</strong></a> (<a href='tree.php?action=item_edit&tree_id=" . $_GET["id"] . "&parent_id=" . $leaf["id"] . "'>Add</a>)</td>\n";
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>Heading</td>";
		}elseif ($leaf["host_id"] > 0) {
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>$transparent_indent<a href='tree.php?action=item_edit&tree_id=" . $_GET["id"] . "&id=" . $leaf["id"] . "'><strong>Host:</strong> " . $leaf["hostname"] . "</a></td>\n";
			print "<td bgcolor='#$row_color' bgcolor='#" . $colors["panel"] . "'>Host</td>";
		}
		
		print 	"<td bgcolor='#$row_color' width='80' align='center'>\n
			<a href='tree.php?action=item_movedown&id=" . $leaf["id"] . "&tree_id=" . $_GET["id"] . "'><img src='images/move_down.gif' border='0' alt='Move Down'></a>\n
			<a href='tree.php?action=item_moveup&id=" . $leaf["id"] . "&tree_id=" . $_GET["id"] . "'><img src='images/move_up.gif' border='0' alt='Move Up'></a>\n
			</td>\n";
		
		print 	"<td bgcolor='#$row_color' width='1%' align='right'>\n
			<a href='tree.php?action=item_remove&id=" . $leaf["id"] . "&tree_id=$tree_id'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;\n
			</td></tr>\n";
	}
	}else{ print "<tr><td><em>No Graph Tree Items</em></td></tr>";
	}
}

function grow_dropdown_tree($tree_id, $form_name, $selected_tree_item_id) {
	global $colors, $config;
	
	include_once($config["library_path"] . "/tree.php");
	
	$tree = db_fetch_assoc("select
		graph_tree_items.id,
		graph_tree_items.title,
		graph_tree_items.order_key
		from graph_tree_items
		where graph_tree_items.graph_tree_id=$tree_id
		and graph_tree_items.title != ''
		order by graph_tree_items.order_key");
	
	print "<select name='$form_name'>\n";
	print "<option value='0'>[root]</option>\n";
	
	if (sizeof($tree) > 0) {
	foreach ($tree as $leaf) {
	    	$tier = tree_tier($leaf["order_key"], 2);
	    	$indent = str_repeat("---", ($tier));
		
		if ($selected_tree_item_id == $leaf["id"]) {
			$html_selected = " selected";
		}else{
			$html_selected = "";
		}
		
	    	print "<option value='" . $leaf["id"] . "'$html_selected>$indent " . $leaf["title"] . "</option>\n";
	}
	}
	
	print "</select>\n";
}

function grow_dhtml_trees() {
	global $colors, $config;
	
	include_once($config["library_path"] . "/tree.php");
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
	
	print "foldersTree = gFld(\"\", \"\")\n";
	
	$tree_list = get_graph_tree_array();
	
	/* auth check for hosts on the trees */
	if (read_config_option("global_auth") == "on") {
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
		$heirarchy = db_fetch_assoc("select
			graph_tree_items.id,
			graph_tree_items.title,
			graph_tree_items.order_key,
			graph_tree_items.host_id,
			CONCAT_WS('',host.description,' (',host.hostname,')') as hostname,
			user_auth_perms.user_id
			from graph_tree_items
			left join host on host.id=graph_tree_items.host_id
			$sql_join
			where graph_tree_items.graph_tree_id=" . $tree["id"] . "
			$sql_where
			and graph_tree_items.local_graph_id = 0
			order by graph_tree_items.order_key");
		
		print "ou0 = insFld(foldersTree, gFld(\"" . $tree["name"] . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "\"))\n";
		
		if (sizeof($heirarchy) > 0) {
		foreach ($heirarchy as $leaf) {
			$tier = tree_tier($leaf["order_key"], 2);
			
			if ($leaf["host_id"] > 0) {
				print "ou" . ($tier) . " = insFld(ou" . ($tier-1) . ", gFld(\"<strong>Host:</strong> " . $leaf["hostname"] . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "\"))\n";
				
				if (read_graph_config_option("expand_hosts") == "on") {
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
						print "ou" . ($tier+1) . " = insFld(ou" . ($tier) . ", gFld(\" " . $graph_template["name"] . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "&graph_template_id=" . $graph_template["id"] . "\"))\n";
					}
					}
				}
			}else{
				print "ou" . ($tier) . " = insFld(ou" . ($tier-1) . ", gFld(\"" . $leaf["title"] . "\", \"graph_view.php?action=tree&tree_id=" . $tree["id"] . "&leaf_id=" . $leaf["id"] . "\"))\n";
			}
		}
		}
	}
	}
	
	?>
	foldersTree.treeID = "t2";
	//-->
	</script>
	<?php
}

function grow_right_pane_tree($tree_id, $leaf_id, $graph_template_id) {
	global $current_user, $colors;
	
	if (empty($tree_id)) { return; }
	
	$sql_where = "";
	$sql_join = "";
	$title = "";
	$title_delimeter = "";
	$search_key = "";
	
	/* get the "starting leaf" if the user clicked on a specific branch */
	if (!empty($leaf_id)) {
		$search_key = preg_replace("/0+$/","",db_fetch_cell("select order_key from graph_tree_items where id=$leaf_id"));
		
		/* we the search key should always be divisible by "2" because we are searching AT a
		branch (__), not PAST it (%%) */
		if (strlen($search_key) % 2 != 0) { $search_key .= "0"; }
	}
	
	/* graph permissions */
	if (read_config_option("global_auth") == "on") {
		/* get policy information for the sql where clause */
		$sql_where = get_graph_permissions_sql($current_user["policy_graphs"], $current_user["policy_hosts"], $current_user["policy_graph_templates"]);
		$sql_where = (empty($sql_where) ? "" : "and $sql_where");
		$sql_join = "
			left join host on host.id=graph_local.host_id
			left join graph_templates on graph_templates.id=graph_local.graph_template_id
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))";
	}
	
	/* get information for the headers */
	if (!empty($tree_id)) { $tree_name = db_fetch_cell("select name from graph_tree where id=$tree_id"); }
	if (!empty($leaf_id)) { $leaf_name = db_fetch_cell("select title from graph_tree_items where id=$leaf_id"); }
	if (!empty($leaf_id)) { $host_name = db_fetch_cell("select CONCAT_WS('',host.description,' (',host.hostname,')') from graph_tree_items,host where graph_tree_items.host_id=host.id and graph_tree_items.id=$leaf_id"); }
	if (!empty($leaf_id)) { $host_id = db_fetch_cell("select host_id from graph_tree_items where id=$leaf_id"); }
	if (!empty($graph_template_id)) { $graph_template_name = db_fetch_cell("select name from graph_templates where id=$graph_template_id"); }
	
	if (!empty($tree_name)) { $title .= $title_delimeter . "<strong>Tree:</strong> $tree_name"; $title_delimeter = "-> "; }
	if (!empty($leaf_name)) { $title .= $title_delimeter . "<strong>Leaf:</strong> $leaf_name"; $title_delimeter = ", "; }
	if (!empty($host_name)) { $title .= $title_delimeter . "<strong>Host:</strong> $host_name"; $title_delimeter = ", "; }
	if (!empty($graph_template_name)) { $title .= $title_delimeter . "<strong>Graph Template:</strong> $graph_template_name"; $title_delimeter = "-> "; }
	
	print "<table width='98%' align='center' cellpadding='3'>";
	print "<tr bgcolor='#" . $colors["header_panel"] . "'><td width='390' colspan='3' class='textHeaderDark'>$title</td></tr>";
	
	if (empty($host_id)) {
		$heirarchy = db_fetch_assoc("select
			graph_tree_items.id,
			graph_tree_items.title,
			graph_tree_items.local_graph_id,
			graph_tree_items.rra_id,
			graph_tree_items.order_key,
			graph_templates_graph.title_cache as graph_title
			from graph_tree_items,graph_local
			left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
			$sql_join
			where graph_tree_items.graph_tree_id=$tree_id
			and graph_local.id=graph_templates_graph.local_graph_id
			and graph_tree_items.order_key like '$search_key" . "__" . str_repeat('0',60-(strlen($search_key)+2)) . "'
			and graph_tree_items.local_graph_id>0
			$sql_where
			group by graph_tree_items.id
			order by graph_tree_items.order_key");
		
		$i = 0;
		if (sizeof($heirarchy) > 0) {
		foreach ($heirarchy as $leaf) {
			form_alternate_row_color("f9f9f9", "ffffff", $i);
			print "<td align='center'><a href='graph.php?local_graph_id=" . $leaf["local_graph_id"] . "&rra_id=all&type=tree'><img src='graph_image.php?local_graph_id=" . $leaf["local_graph_id"] . "&rra_id=" . $leaf["rra_id"] . "' border='0' alt='" . $leaf["graph_title"] . "'></a></td>";
			print "<tr>\n";
			
			$i++;
		}
		}else{
			print "<tr><td><em>No graphs at this branch.</em></td></tr>";
		}
	}else{
		$graph_templates = db_fetch_assoc("select
			graph_templates.id,
			graph_templates.name
			from graph_local,graph_templates,graph_templates_graph
			where graph_local.id=graph_templates_graph.local_graph_id
			and graph_templates_graph.graph_template_id=graph_templates.id
			and graph_local.host_id=$host_id
			" . (empty($graph_template_id) ? "" : "and graph_templates.id=$graph_template_id") . "
			group by graph_templates.id
			order by graph_templates.name");
		
		/* for graphs without a template */
		array_push($graph_templates, array(
			"id" => "0",
			"name" => "(No Graph Template)"
			));
		
		if (sizeof($graph_templates) > 0) {
		foreach ($graph_templates as $graph_template) {
			$graphs = db_fetch_assoc("select
				graph_templates_graph.title_cache,
				graph_templates_graph.local_graph_id
				from graph_local,graph_templates_graph
				$sql_join
				where graph_local.id=graph_templates_graph.local_graph_id
				and graph_local.graph_template_id=" . $graph_template["id"] . "
				and graph_local.host_id=$host_id
				$sql_where
				order by graph_templates_graph.title_cache");
			
			$i = 0;
			if (sizeof($graphs) > 0) {
				if (empty($graph_template_id)) {
					print "<tr bgcolor='#a9b7cb'><td colspan='3' class='textHeaderDark'><strong>Graph Template:</strong> " . $graph_template["name"] . "</td></tr>";
				}
				
				foreach ($graphs as $graph) {
					form_alternate_row_color("f9f9f9", "ffffff", $i);
					print "<td align='center'><a href='graph.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=all&type=tree'><img src='graph_image.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=" . read_graph_config_option("default_rra_id") . "' border='0' alt='" . $graph["title_cache"] . "'></a></td>";
					print "<tr>\n";
					
					$i++;
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
			$ec_icon = 'show';
		}else{
			$other_status = '1';
			$ec_icon =  'hide';
		}
		
		print "<td bgcolor='" . $colors["panel"] . "' align='center' width='1%'><a
			href='graph_view.php?action=tree&tree_id=$tree_id&hide=$other_status&branch_id=$tree_item_id'>
			<img src='images/$ec_icon.gif' border='0'></a></td>\n";
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
	print "<td><a href='graph.php?local_graph_id=$local_graph_id&rra_id=all'><img align='middle' alt='$graph_title'
		src='graph_image.php?local_graph_id=$local_graph_id&rra_id=$rra_id&graph_start=" . -(db_fetch_cell("select timespan from rra where id=$rra_id")) . '&graph_height=' .
		read_graph_config_option("default_height") . '&graph_width=' . read_graph_config_option("default_width") . "&graph_nolegend=true' border='0'></a></td>\n";
	
	/* if we are at the end of a row, start a new one */
	if ($graph_counter % read_graph_config_option("num_columns") == 0) {
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

?>
