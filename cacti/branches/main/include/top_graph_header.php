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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Cacti</title>
	<?php if (isset($_SESSION["custom"])) {
		if ($_SESSION["custom"]) {
			print "<meta http-equiv=refresh content='99999'>\r\n";
		}else{
			print "<meta http-equiv=refresh content='" . read_graph_config_option("page_refresh") . "'>\r\n";
		}
	}
	?>
	<link href="include/main.css" rel="stylesheet">
	<link href="images/favicon.ico" rel="shortcut icon"/>
	<script type="text/javascript" src="include/layout.js"></script>
	<script type="text/javascript" src="include/treeview/ua.js"></script>
	<script type="text/javascript" src="include/treeview/ftiens4.js"></script>
	<script type="text/javascript" src="include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="include/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="include/jscalendar/calendar-setup.js"></script>
</head>
<body class='body' onLoad='initializePage()'>
<a name='page_top'></a>
<div id='header' class='header'>
	<div id=logobar' class='logobar'></div>
	<div id='navbar' class='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", "Console", "index.php");?>
				<?php echo draw_header_tab("graphs", "Graphs", "graph_view.php");?>
			</ul>
		</div>
		<div id='navbar_r' class='navbar_r'>
			<ul>
				<?php echo draw_header_tab("graph_settings", "Settings", "graph_settings.php");?>
				<?php echo draw_header_tab("tree", "Tree", "graph_view.php?action=tree", "images/tab_mode_tree_new.gif");?>
				<?php echo draw_header_tab("list", "List", "graph_view.php?action=list", "images/tab_mode_list_new.gif");?>
				<?php echo draw_header_tab("preview", "Preview", "graph_view.php?action=preview", "images/tab_mode_preview_new.gif");?>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left'>
			<?php draw_navigation_text();?>
		</div>
		<div style='float:right'>
			<?php if (read_config_option("auth_method") != 0) { ?>
				Logged in as <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="logout.php">Logout</a>)&nbsp;
			<?php } ?>
		</div>
	</div>
</div>
<table width="100%" cellspacing="0" cellpadding="0">
	<tr class="noprint">
		<td bgcolor="#efefef" colspan="1" height="8" style="background-image: url(images/shadow_gray.gif); background-repeat: repeat-x; border-right: #aaaaaa 1px solid;">
			<img src="images/transparent_line.gif" width="<?php print read_graph_config_option("default_dual_pane_width");?>" alt="" height="2" border="0"><br>
		</td>
		<td bgcolor="#ffffff" colspan="1" height="8" style="background-image: url(images/shadow.gif); background-repeat: repeat-x;">

		</td>
	</tr>

	<?php if ((basename($_SERVER["PHP_SELF"]) == "graph.php") && ($_REQUEST["action"] == "properties")) {?>
	<tr>
		<td valign="top" height="1" colspan="3" bgcolor="#efefef">
			<?php
			$graph_data_array["print_source"] = true;

			/* override: graph start time (unix time) */
			if (!empty($_GET["graph_start"])) {
				$graph_data_array["graph_start"] = $_GET["graph_start"];
			}

			/* override: graph end time (unix time) */
			if (!empty($_GET["graph_end"])) {
				$graph_data_array["graph_end"] = $_GET["graph_end"];
			}

			print trim(rrdtool_function_graph($_GET["local_graph_id"], $_GET["rra_id"], $graph_data_array));
			?>
		</td>
	</tr>
	<?php }?>

	<tr>
		<?php if ((read_graph_config_option("default_tree_view_mode") == "2") && (($_REQUEST["action"] == "tree") || ((isset($_REQUEST["view_type"]) ? $_REQUEST["view_type"] : "") == "tree"))) { ?>
		<td valign="top" style="padding: 5px; border-right: #aaaaaa 1px solid;" bgcolor='#efefef' width='<?php print read_graph_config_option("default_dual_pane_width");?>' class='noprint'>
			<table border=0 cellpadding=0 cellspacing=0><tr><td><font size=-2><a style="font-size:7pt;text-decoration:none;color:silver" href="http://www.treemenu.net/" target=_blank></a></font></td></tr></table>
			<?php grow_dhtml_trees(); ?>
			<script type="text/javascript">initializeDocument();</script>

			<?php if (isset($_GET["select_first"])) { ?>
			<script type="text/javascript">
			var obj;
			obj = findObj(1);

			if (!obj.isOpen) {
				clickOnNode(1);
			}

			clickOnLink(2,'','main');
			</script>
			<?php } ?>
		</td>
		<?php } ?>
		<td valign="top" style="padding: 5px; border-right: #aaaaaa 1px solid;">
