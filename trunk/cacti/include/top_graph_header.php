<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

if (read_config_option("auth_method") != "0") {
	/* at this point this user is good to go... get user info */
	$current_user = api_user_info( array( "id" => $_SESSION["sess_user_id"]) );

	/* find out if we are logged in as a 'guest user' or not */
	if (read_config_option("guest_user") != "0") {
		if ($current_user["username"] == read_config_option("guest_user")) {
			$using_guest_account = true;
		}
	}
	/* find out if we should show the "console" tab or not, based on this user's permissions */
	$current_user_realms = api_user_realms_list($current_user["id"]);
	if ($current_user_realms["8"]["value"] != "1") {
		$show_console_tab = false;
	}
}else{
	/* set permission for no auth */
	$current_user["graph_settings"] = 'on';
	$current_user["show_tree"] = 'on';
	$current_user["show_list"] = 'on';
	$current_user["show_preview"] = 'on';

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
	<link rel="shortcut icon" href="<?php print html_get_theme_images_path('favicon.ico');?>" type="image/x-icon">
	<link href="<?php print html_get_theme_images_path('favicon.ico');?>" rel="image/x-icon">
	<title>cacti</title>
	<?php if (isset($_SESSION["custom"])) {
		print "<meta http-equiv=refresh content='99999'; url='" . basename($_SERVER["PHP_SELF"]) . "'>\r\n";
	}else{
		print "<meta http-equiv=refresh content='" . read_graph_config_option("page_refresh") . "'; url='" . basename($_SERVER["PHP_SELF"]) . "'>\r\n";
	}
	?>
	<link href='<?php print html_get_theme_css();?>' rel='stylesheet'>
	<script type="text/javascript" src="include/treeview/ua.js"></script>
	<script type="text/javascript" src="include/treeview/ftiens4.js"></script>
	<script type="text/javascript" src="include/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="include/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="include/jscalendar/calendar-setup.js"></script>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table width="100%" height="100%" cellspacing="0" cellpadding="0">
	<tr height="37" bgcolor="#<?php print $colors['main_background'];?>" class="noprint">
		<td colspan="2" valign="bottom" nowrap>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td nowrap>
						&nbsp;<?php if ($show_console_tab == true) {?><a href="index.php"><img src="<?php print html_get_theme_images_path('tab_console.gif');?>" alt="Console" align="absmiddle" border="0"></a><?php }?><a href="graph_view.php"><img src="<?php print html_get_theme_images_path('tab_graphs.gif');?>" alt="<?php echo _('Graphs');?>" align="absmiddle" border="0"></a>&nbsp;
					</td>
					<td>
						<img src="<?php print html_get_theme_images_path('cacti_backdrop2.gif');?>" align="absmiddle">
					</td>
					<td align="right" nowrap>
						<?php if ($current_user["graph_settings"] == "on") { ?>
						<a href="graph_settings.php"><img src="<?php if (basename($_SERVER["PHP_SELF"]) == "graph_settings.php") print html_get_theme_images_path('tab_settings_down.gif'); else print html_get_theme_images_path('tab_settings.gif');?>" border="0" alt="<?php echo _('Settings');?>" align="absmiddle"></a>
						<?php }else{ ?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } ?>
						&nbsp;&nbsp;<?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_tree"] == "on")) {?>
						<a href="graph_view.php?action=tree"><img src="<?php if ($_REQUEST["action"] == "tree") print html_get_theme_images_path('tab_mode_tree_down.gif'); else print html_get_theme_images_path('tab_mode_tree.gif');?>" border="0" title="<?php echo _('Tree View');?>" alt="<?php echo _('Tree View');?>" align="absmiddle"></a>
						<?php }?><?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_list"] == "on")) {?>
						<a href="graph_view.php?action=list"><img src="<?php if ($_REQUEST["action"] == "list") print html_get_theme_images_path('tab_mode_list_down.gif'); else print html_get_theme_images_path('tab_mode_list.gif');?>" border="0" title="<?php echo _('List View');?>" alt="<?php echo _('List View');?>" align="absmiddle"></a>
						<?php }?><?php if ((!isset($_SESSION["sess_user_id"])) || ($current_user["show_preview"] == "on")) {?>
						<a href="graph_view.php?action=preview"><img src="<?php if ($_REQUEST["action"] == "preview") print html_get_theme_images_path('tab_mode_preview_down.gif'); else print html_get_theme_images_path('tab_mode_preview.gif')?>" border="0" title="<?php echo _('Preview View');?>" alt="<?php echo _('Preview View');?>" align="absmiddle"></a><?php }?>&nbsp;<br>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="2" colspan="2" bgcolor="#<?php print $colors['main_border'];?>" class="noprint">
		<td colspan="2">
			<img src="<?php print html_get_theme_images_path('transparent_line.gif');?>" width="170" height="2" border="0"><br>
		</td>
	</tr>
	<tr height="5" bgcolor="#<?php print $colors['navbar_background'];?>" class="noprint">
		<td colspan="2">
			<table width="100%">
				<tr>
					<td>
						<?php draw_navigation_text();?>
					</td>
					<?php if (isset($_SESSION["sess_user_id"])) {
						if ((read_config_option("auth_method") == "1") || (($current_user["realm"] == "0") && (read_config_option("auth_method") == "3"))) {
							$expire_days = api_user_expire_info($current_user["id"]);
							if (($expire_days != -1) && ($expire_days <= read_config_option("password_expire_warning"))) {
						?>
						<td align="right" class="textError">
							<?php echo _("Password expires in") . " " . $expire_days . _("days");?>
						</td>
					<?php } } } ?>
					<td align="right">
						<?php if ((isset($_SESSION["sess_user_id"])) && ($using_guest_account == false) && (read_config_option("auth_method") != "0")) { ?>
						Logged in as <strong><?php print $current_user["username"];?></strong> (<?php if ($current_user_realms["19"]["value"] == "1") { ?><a href="user_settings.php">User Settings</a>|<?php } if (((read_config_option("auth_method") == "1") || (($current_user["realm"] == "0") && (read_config_option("auth_method") == "3"))) && ($current_user_realms["18"]["value"] == "1")) { ?><a href="user_changepassword.php"><?php echo _("Change Password");?></a>|<?php } ?><a href="logout.php"><?php echo _("Logout");?></a>)&nbsp;
						<?php
						}else{
							if ((read_config_option("auth_method") != "0") && (read_config_option("auth_method") != "2")) {
						?>
						&nbsp;(<a href="logout.php"><?php echo _("Login");?></a>)&nbsp;

						<?php
							}
						} ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="noprint">
		<td bgcolor="#<?php print $colors['graph_menu_background'];?>" colspan="1" height="8" style="background-image: url(<?php print html_get_theme_images_path('shadow_gray.gif');?>); background-repeat: repeat-x; border-right: #<?php print $colors['graph_menu_border'];?> 1px solid;">
			<img src="<?php print html_get_theme_images_path('transparent_line.gif');?>" width="170" height="2" border="0"><br>
		</td>
		<td bgcolor="#<?php print $colors['graph_menu_background'];?>" colspan="1" height="8" style="background-image: url(<?php print html_get_theme_images_path('shadow.gif');?>); background-repeat: repeat-x;">

		</td>
	</tr>

	<?php if ((basename($_SERVER["PHP_SELF"]) == "graph.php") && ($_REQUEST["action"] == "properties") && (! $using_guest_account)) {?>
	<tr>
		<td valign="top" height="1" colspan="3" bgcolor="#<?php print $colors['graph_menu_background'];?>">
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

			print trim(rrdtool_function_graph($_GET["graph_id"], $_GET["rra_id"], $graph_data_array));
			?>
		</td>
	</tr>
	<?php }?>

	<tr>
		<?php if ((read_graph_config_option("default_tree_view_mode") == "2") && (($_REQUEST["action"] == "tree") || ((isset($_REQUEST["view_type"]) ? $_REQUEST["view_type"] : "") == "tree"))) { ?>
		<td valign="top" style="padding: 5px; border-right: #<?php print $colors['graph_menu_border'];?> 1px solid;" bgcolor="#<?php print $colors['graph_menu_background'];?>" width="200" class="noprint">
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