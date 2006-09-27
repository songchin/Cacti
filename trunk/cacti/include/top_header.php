<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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
global $colors;

if (isset($_SESSION["sess_user_id"])) {
	$current_user = api_user_info( array("id" => $_SESSION["sess_user_id"]) );
}else{
	$current_user = array();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=charset=<?php echo _("screen charset");?>" />
		<meta http-equiv="Content-Language" content="en-us" />
		<link rel='shortcut icon' href='<?php print html_get_theme_images_path("favicon.ico");?>' type='image/x-icon' />
		<link href='<?php print html_get_theme_css();?>' rel='stylesheet' />
		<link href='<?php print html_get_theme_images_path("favicon.ico");?>' rel='image/x-icon' />
		<?php echo (isset($xajax) ? $xajax->getJavascript("lib/xajax/") : "");?>
		<script type="text/javascript" language="javascript" src="include/js/layout.js"></script>
		<script type="text/javascript" language="javascript" src="include/js/navigation.js"></script>
		<script type="text/javascript" language="javascript" src="include/js/box.js"></script>
		<title>Cacti</title>
</head>

<body>

<div id="header">
	<div id="tabs">
		<ul>
			<?php echo ui_html_header_tab_make("graphs", "Graphs");?>
			<?php echo ui_html_header_tab_make("collection", "Collection");?>
			<?php echo ui_html_header_tab_make("templates", "Templates");?>
			<?php echo ui_html_header_tab_make("configuration", "Configuration");?>
			<?php echo ui_html_header_tab_make("users", "Users");?>
		</ul>
	</div>
	<div id="navigation">
		<?php echo ui_html_header_navigation_group_make("graphs", array("View" => "graph_view.php", "Create" => "graphs_new.php", "Manage" => "graphs.php", "Trees" => "graph_trees.php"));?>
		<?php echo ui_html_header_navigation_group_make("collection", array("Devices" => "devices.php", "Data Sources" => "data_sources.php", "Pollers" => "pollers.php", "Scripts" => "scripts.php", "Queries" => "data_queries.php"));?>
		<?php echo ui_html_header_navigation_group_make("templates", array("Packages" => "packages.php", "Graph Templates" => "graph_templates.php", "Data Templates" => "data_templates.php", "Device Templates" => "device_templates.php"));?>
		<?php echo ui_html_header_navigation_group_make("configuration", array("System Settings" => "settings.php", "User Settings" => "user_settings.php", "Data Presets" => "presets.php", "Plugins" => "plugins.php", "System Utilities" => "utilities.php", "Log Management" => "logs.php"));?>
		<?php echo ui_html_header_navigation_group_make("users", array("Manage" => "auth_user.php", "Groups" => "auth_group.php"));?>
	</div>
</div>

<div id="content">
	<div id="panel">
		<img src="<?php echo html_get_theme_images_path("side_search.gif");?>">
	</div>
	<div id="login">
		<?php
		if (read_config_option("auth_method") == "1") {
			$expire_days = api_user_expire_info($current_user["id"]);

			if (($expire_days != -1) && ($expire_days <= read_config_option("password_expire_warning"))) {
				echo "<span class=\"textError\">" . _("Password expires in ") . $expire_days . _(" days") . "<span>\n";
			}
		}

		if (read_config_option("auth_method") != "0") {
			echo sprintf(_("Logged in as") . " <strong>%s</strong>", $current_user["username"]);
			echo " (<a href='logout.php'>" . _("Logout") . "</a>)&nbsp;";
		}
		?>
	</div>
	<div id="body">
