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
	(preg_match("/action=(tree|preview|list)/", $_SESSION["sess_graph_view_url_cache"]))) {

	header("Location: " . $_SESSION["sess_graph_view_url_cache"]);
}

/* set default action */
if (!isset($_REQUEST["action"])) {
	$_REQUEST["action"] = "";
}

/* need to correct $_SESSION["sess_nav_level_cache"] in zoom view */
if ($_REQUEST["action"] == "zoom") {
	$_SESSION["sess_nav_level_cache"][2]["url"] = htmlspecialchars("graph.php?local_graph_id=" . $_REQUEST["local_graph_id"] . "&rra_id=all");
}

/* set the default action if none has been set */
if ((!preg_match('/^(tree|list|preview)$/', $_REQUEST["action"])) &&
	(basename($_SERVER["PHP_SELF"]) == "graph_view.php")) {

	if (read_graph_config_option("default_view_mode") == GRAPH_TREE_VIEW) {
		$_REQUEST["action"] = "tree";
	}elseif (read_graph_config_option("default_view_mode") == GRAPH_LIST_VIEW) {
		$_REQUEST["action"] = "list";
	}elseif (read_graph_config_option("default_view_mode") == GRAPH_PREVIEW_VIEW) {
		$_REQUEST["action"] = "preview";
	}
}

/* setup tree selection defaults if the user has not been here before */
if (($_REQUEST["action"] == "tree") &&
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
			$refresh = api_plugin_hook_function('top_graph_refresh', '0');

			if ($refresh > 0) {
				print "<meta http-equiv=refresh content='" . htmlspecialchars($refresh,ENT_QUOTES) . "'>\r\n";
			}
		}
	}
	?>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta http-equiv="Content-Script-Type" content="text/javascript" >
	<meta http-equiv="Content-Style-Type" content="text/css">
	<link type="text/css" href="<?php echo URL_PATH; ?>include/main.css" rel="stylesheet">
	<link type="text/css" href="<?php echo URL_PATH; ?>include/dd.css" rel="stylesheet">
	<link type="text/css" href="<?php echo URL_PATH; ?>include/jquery.autocomplete.css" rel="stylesheet">
	<link href="<?php echo URL_PATH; ?>images/favicon.ico" rel="shortcut icon">
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/layout.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jstree/jquery.tree.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.cookie.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.bgiframe.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.ajaxQueue.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.tablednd.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.dropdown.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jquery/jquery.dd.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jscalendar/lang/<?php print (read_config_option('i18n_support') != 0) ? CACTI_LANGUAGE_FILE : "english_usa";?>.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jstree/plugins/jquery.tree.cookie.js"></script>
	<?php api_plugin_hook('page_head'); ?>
</head>
<body class='body' onLoad='pageInitialize()'>
<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/wztooltip/wz_tooltip.js"></script>
<div id='header'>
	<div id='logobar'></div>
	<div id='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", __("Console"), URL_PATH . "index.php");?>
				<?php echo draw_header_tab("graphs", __("Graphs"), URL_PATH . "graph_view.php");?>
				<?php api_plugin_hook('top_graph_header_tabs');?>
			</ul>
		</div>
		<div id='navbar_r'>
			<ul>
				<?php if (substr_count($_SERVER["REQUEST_URI"], "graph_view.php")) { ?>
				<?php echo draw_header_tab("graph_settings", __("Settings"), URL_PATH . "graph_settings.php");?>
				<?php echo draw_header_tab("tree", __("Tree"), URL_PATH . "graph_view.php?action=tree", URL_PATH . "images/tab_mode_tree_new.gif");?>
				<?php echo draw_header_tab("list", __("List"), URL_PATH . "graph_view.php?action=list", URL_PATH . "images/tab_mode_list_new.gif");?>
				<?php echo draw_header_tab("preview", __("Preview"), URL_PATH . "graph_view.php?action=preview", URL_PATH . "images/tab_mode_preview_new.gif");?>
				<?php }else{ api_plugin_hook('top_graph_header_tabs_right'); }?>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left'>
			<?php draw_navigation_text();?>
		</div>
		<div style='float:right'>
			<a href="<?php echo CACTI_WIKI_URL . rtrim(basename($_SERVER["PHP_SELF"]), ".php");?>" target="_blank">
			<img src="<?php echo URL_PATH; ?>images/help.gif" title="<?php print __("Help");?>" alt="<?php print __("Help");?>" align="top">
			</a>
		</div>
		<div style='float:right'><?php
			if (read_config_option("auth_method") != 0) { $date = date_time_format();?><strong><?php echo __date("D, " . $date . " T");?></strong>&nbsp;&nbsp;&nbsp;<?php print __("Logged in as");?> <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="<?php echo URL_PATH; ?>logout.php"><?php print __("Logout");?></a>)<?php } ?>
		</div>
		<?php if(read_config_option('i18n_support') != 0) {?>
		<div id="codelist" style="float:right; list-style:none; display:inline;">
			<span id="loading" style="display:none;"><img src="<?php echo URL_PATH; ?>images/load_small.gif" align="top" alt="<?php print __("loading");?>">LOADING</span>
			<ul class="down-list" style="list-style:none; display:inline;">
				<li><a href="#" class="languages" id="languages"><img src="<?php echo URL_PATH; ?>images/flag_icons/<?php print CACTI_COUNTRY;?>.gif" align="top" alt="<?php print __("loading");?>">&nbsp;<?php print CACTI_LANGUAGE;?></a></li>
			</ul>
		</div>
		<?php }?>
	</div>
</div>
<div id='wrapper' style='opacity:0;'>
	<?php if (($_REQUEST["action"] == "tree") || ((isset($_REQUEST["view_type"]) ? $_REQUEST["view_type"] : "") == "tree")) { ?>
	<div id='graph_tree'>
		<div id='tree_filter'>
			<?php graph_view_tree_filter();?>
		</div>
		<div class="tree"></div>
	</div>
	<div id='vsplitter' onMouseout='doneDivResize()' onMouseover='doDivResize(this,event)' onMousemove='doDivResize(this,event)'>
		<div id='vsplitter_toggle' onClick='vSplitterToggle()' onMouseover='vSplitterEm()' onMouseout='vSplitterUnEm()' title='<?php print __("Hide/Unhide Menu");?>'></div>
	</div>
	<div id='graph_tree_content'>
		<div id='graphs'>
		</div>
	<?php }else{ ?>
<div id='graph_content'>
	<?php }
