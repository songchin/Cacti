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

$using_guest_account = false;
$show_console_tab = true;

include_once($config["library_path"] . "/tree_view.php");
include_once($config["library_path"] . "/rrd.php");

if (read_config_option("global_auth") == "on") {
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
if ((isset($_SESSION["sess_graph_view_url_cache"])) && (empty($_REQUEST["action"])) && (basename($_SERVER["PHP_SELF"]) == "graph_view.php") && (ereg("action=(tree|preview|list)", $_SESSION["sess_graph_view_url_cache"]))) {
	header("Location: " . $_SESSION["sess_graph_view_url_cache"]);
}

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

/* set the default action if none has been set */
if ((!ereg('^(tree|list|preview)$', $_REQUEST["action"])) && (basename($_SERVER["PHP_SELF"]) == "graph_view.php")) {
	if (read_graph_config_option("default_view_mode") == "1") {
		$_REQUEST["action"] = "tree";
	}elseif (read_graph_config_option("default_view_mode") == "2") {
		$_REQUEST["action"] = "list";
	}elseif (read_graph_config_option("default_view_mode") == "3") {
		$_REQUEST["action"] = "preview";
	}
}

/* setup tree selection defaults if the user has not been here before */
if ((read_graph_config_option("default_tree_view_mode") == "2") && ($_REQUEST["action"] == "tree") && (!isset($_SESSION["sess_has_viewed_graphs"]))) {
	$_SESSION["sess_has_viewed_graphs"] = true;
	
	$first_branch = find_first_folder_url();
	
	if (!empty($first_branch)) {
		header("Location: $first_branch");
	}
}

?>
<html>
<head>
	<title>cacti</title>
	<?php print "<meta http-equiv=refresh content='" . read_graph_config_option("page_refresh") . "'; url='" . basename($_SERVER["PHP_SELF"]) . "'>\r\n";?>
	<link href="include/main.css" rel="stylesheet">
	
	<script src="include/treeview/ua.js"></script>
	<script src="include/treeview/ftiens4.js"></script>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="100%" height="100%" cellspacing="0" cellpadding="0">
	<tr height="37" bgcolor="#a9a9a9">
		<td colspan="2" valign="bottom" nowrap>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td nowrap>
						&nbsp;<?php if ($show_console_tab == true) {?><a href="index.php"><img src="images/tab_console.gif" alt="Console" align="absmiddle" border="0"></a><?php }?><a href="graph_view.php"><img src="images/tab_graphs.gif" alt="Graphs" align="absmiddle" border="0"></a>&nbsp;
					</td>
					<td>
						<img src="images/cacti_backdrop2.gif" align="absmiddle">
					</td>
					<td align="right" nowrap>
						<a href="graph_settings.php"><img src="images/tab_settings<?php if (basename($_SERVER["PHP_SELF"]) == "graph_settings.php") { print "_down"; }?>.gif" border="0" alt="Settings" align="absmiddle"></a>&nbsp;&nbsp;<?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_tree"] == "on")) {?><a href="graph_view.php?action=tree"><img src="images/tab_mode_tree<?php if ($_REQUEST["action"] == "tree") { print "_down"; }?>.gif" border="0" title="Tree View" alt="Tree View" align="absmiddle"></a><?php }?><?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_list"] == "on")) {?><a href="graph_view.php?action=list"><img src="images/tab_mode_list<?php if ($_REQUEST["action"] == "list") { print "_down"; }?>.gif" border="0" title="List View" alt="List View" align="absmiddle"></a><?php }?><?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_preview"] == "on")) {?><a href="graph_view.php?action=preview"><img src="images/tab_mode_preview<?php if ($_REQUEST["action"] == "preview") { print "_down"; }?>.gif" border="0" title="Preview View" alt="Preview View" align="absmiddle"></a><?php }?>&nbsp;<br>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="2" colspan="2" bgcolor="#183c8f">
		<td colspan="2">
			<img src="images/transparent_line.gif" width="170" height="2" border="0"><br>
		</td>
	</tr>
	<tr height="5" bgcolor="#e9e9e9">
		<td colspan="2">
			<table width="100%">
				<tr>
					<td>
						<?php draw_navigation_text();?>
					</td>
					<td align="right">
						<?php if ((isset($_SESSION["sess_user_id"])) && ($using_guest_account == false)) { ?>
						Logged in as <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="logout.php">Logout</a>)&nbsp;
						<?php } ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td bgcolor="#efefef" colspan="1" height="8" style="background-image: url(images/shadow_gray.gif); background-repeat: repeat-x; border-right: #aaaaaa 1px solid;">
			<img src="images/transparent_line.gif" width="170" height="2" border="0"><br>
		</td>
		<td bgcolor="#ffffff" colspan="1" height="8" style="background-image: url(images/shadow.gif); background-repeat: repeat-x;">
		
		</td>
	</tr>
	
	<?php if (!empty($_GET["show_source"])) {?>
	<tr>
		<td valign="top" height="1" colspan="3" bgcolor="#efefef">
			<?php
			$graph_data_array["print_source"] = true;
			print trim(rrdtool_function_graph($_GET["local_graph_id"], $_GET["rra_id"], $graph_data_array));
			?>
		</td>
	</tr>
	<?php }?>
	
	<tr>
		<?php if ((read_graph_config_option("default_tree_view_mode") == "2") && (($_REQUEST["action"] == "tree") || ((isset($_GET["type"]) ? $_GET["type"] : "") == "tree"))) { ?>
		<td valign="top" style="padding: 5px; border-right: #aaaaaa 1px solid;" bgcolor='#efefef' width='200'>
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
		<td valign="top">
