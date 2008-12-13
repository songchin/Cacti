<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

include_once(CACTI_BASE_PATH . "/lib/time.php");

$using_guest_account = false;
$show_console_tab = true;

/* ================= input validation ================= */
input_validate_input_number(get_request_var_request("local_graph_id"));
/* ==================================================== */

if (read_config_option("auth_method") != 0) {
	/* at this point this user is good to go... so get some setting about this
	user and put them into variables to save excess SQL in the future */
	$current_user = db_fetch_row("select * from user_auth where id=" . $_SESSION["sess_user_id"]);

	/* find out if we are logged in as a 'guest user' or not */
	if (db_fetch_cell("select id from user_auth where username='" . read_config_option("guest_user") . "'") == $_SESSION["sess_user_id"]) {
		$using_guest_account = true;
	}

	/* find out if we should show the "console" tab or not, based on this user's permissions */
	if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where realm_id=8 and user_id=" . $_SESSION["sess_user_id"])) == 0) {
		$show_console_tab = false;
	}
}

/* use cached url if available and applicable */
if ((isset($_SESSION["sess_graph_view_url_cache"])) &&
	(empty($_REQUEST["action"])) &&
	(basename($_SERVER["PHP_SELF"]) == "graph_view.php") &&
	(ereg("action=(tree|preview|list)", $_SESSION["sess_graph_view_url_cache"]))) {

	header("Location: " . $_SESSION["sess_graph_view_url_cache"]);
}

/* set default action */
if (!isset($_REQUEST["action"])) {
	$_REQUEST["action"] = "";
}

/* need to correct $_SESSION["sess_nav_level_cache"] in zoom view */
if ($_REQUEST["action"] == "zoom") {
	$_SESSION["sess_nav_level_cache"][2]["url"] = "graph.php?local_graph_id=" . $_REQUEST["local_graph_id"] . "&rra_id=all";
}

/* set the default action if none has been set */
if ((!ereg('^(tree|list|preview)$', $_REQUEST["action"])) &&
	(basename($_SERVER["PHP_SELF"]) == "graph_view.php")) {

	if (read_graph_config_option("default_view_mode") == "1") {
		$_REQUEST["action"] = "tree";
	}elseif (read_graph_config_option("default_view_mode") == "2") {
		$_REQUEST["action"] = "list";
	}elseif (read_graph_config_option("default_view_mode") == "3") {
		$_REQUEST["action"] = "preview";
	}
}

/* setup tree selection defaults if the user has not been here before */
if ((read_graph_config_option("default_tree_view_mode") == "2") &&
	($_REQUEST["action"] == "tree") &&
	(!isset($_GET["leaf_id"])) &&
	(!isset($_SESSION["sess_has_viewed_graphs"]))) {

	$_SESSION["sess_has_viewed_graphs"] = true;

	$first_branch = find_first_folder_url();

	if (!empty($first_branch)) {
		header("Location: $first_branch");
	}
}

$page_title = api_plugin_hook_function('page_title', 'Cacti');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php if (isset($_SESSION["custom"])) {
		if ($_SESSION["custom"]) {
			print "<meta http-equiv=refresh content='99999'>\r\n";
		}else{
			$refresh = api_plugin_hook_function('top_graph_refresh', read_graph_config_option('page_refresh'));
			print "<meta http-equiv=refresh content='" . $refresh . "'>\r\n";		}
	}
	?>
	<link type="text/css" href="<?php echo $config['url_path']; ?>include/main.css" rel="stylesheet"/>
	<link href="<?php echo $config['url_path']; ?>images/favicon.ico" rel="shortcut icon"/>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/layout.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/treeview/ua.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/treeview/ftiens4.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="<?php echo $config['url_path']; ?>include/jscalendar/calendar-setup.js"></script>
	<?php api_plugin_hook('page_head'); ?>
</head>
<body class='body' onResize='pageResize()' onLoad='pageInitialize()'>
<div id='header'>
	<div id='logobar'></div>
	<div id='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", "Console", $config['url_path'] . "index.php");?>
				<?php echo draw_header_tab("graphs", "Graphs", $config['url_path'] . "graph_view.php");?>
				<?php api_plugin_hook('top_graph_header_tabs'); ?>
			</ul>
		</div>
		<div id='navbar_r'>
			<ul>
				<?php echo draw_header_tab("graph_settings", "Settings", $config['url_path'] . "graph_settings.php");?>
				<?php echo draw_header_tab("tree", "Tree", $config['url_path'] . "graph_view.php?action=tree", $config['url_path'] . "images/tab_mode_tree_new.gif");?>
				<?php echo draw_header_tab("list", "List", $config['url_path'] . "graph_view.php?action=list", $config['url_path'] . "images/tab_mode_list_new.gif");?>
				<?php echo draw_header_tab("preview", "Preview", $config['url_path'] . "graph_view.php?action=preview", $config['url_path'] . "images/tab_mode_preview_new.gif");?>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left'>
			<?php draw_navigation_text();?>
		</div>
		<div style='float:right'>
			<?php
			if (read_config_option("auth_method") != 0) {
				$date = date_time_format();

				?><strong><?php echo date("D, " . $date . " T");?></strong>&nbsp;&nbsp;&nbsp;
				Logged in as <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="<?php echo $config['url_path']; ?>logout.php">Logout</a>)
			<?php } ?>
		</div>
	</div>
</div>
<div id='wrapper' style='opacity:0;'>
	<?php if ((read_graph_config_option("default_tree_view_mode") == "2") && (($_REQUEST["action"] == "tree") || ((isset($_REQUEST["view_type"]) ? $_REQUEST["view_type"] : "") == "tree"))) { ?>
	<div id='graph_tree'>
		<table border=0 cellpadding=0 cellspacing=0><tr><td><a style="font-size:7pt;text-decoration:none;color:silver" href="http://www.treemenu.net/" target=_blank></a></td></tr></table>
		<?php grow_dhtml_trees(); ?>
		<script type="text/javascript">initializeDocument();</script>

		<?php if (isset($_GET["select_first"])) { ?>
		<script type="text/javascript">
		var obj;
		obj = findObj(1);

		if (obj) {
			if (!obj.isOpen) {
				clickOnNode(1);
			}
		}

		clickOnLink(2,'','main');
		</script>
		<?php } ?>
	</div>
	<div id='vsplitter' onMouseout='doneDivResize()' onMouseover='doDivResize(this,event)' onMousemove='doDivResize(this,event)'>
		<div id='vsplitter_toggle' onClick='vSplitterToggle()' title='ToggleMenu'></div>
	</div>
	<div id='graph_tree_content'>
	<?php }else{ ?>
	<div id='graph_content'>
	<?php } ?>
