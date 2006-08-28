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
		<?php echo ui_html_header_navigation_group_make("graphs", array("View" => "#", "Create" => "#", "Manage" => "#", "Trees" => "#"));?>
		<?php echo ui_html_header_navigation_group_make("collection", array("Devices" => "#", "Data Sources" => "#", "Pollers" => "#", "Scripts" => "#", "Queries" => "#"));?>
		<?php echo ui_html_header_navigation_group_make("templates", array("Packages" => "#", "Graph Templates" => "#", "Data Templates" => "#", "Device Templates" => "#"));?>
		<?php echo ui_html_header_navigation_group_make("configuration", array("System Settings" => "#", "User Settings" => "#", "Data Presets" => "#", "Plugins" => "#", "System Utilities" => "#", "Log Management" => "#"));?>
		<?php echo ui_html_header_navigation_group_make("users", array("Manage" => "#", "Groups" => "#"));?>
	</div>
</div>

<br>

<table width="100%" cellspacing="0" cellpadding="0">
	<tr height="37" bgcolor="#<?php print $colors['main_background'];?>">
		<td valign="bottom" colspan="3" nowrap>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="bottom">
						&nbsp;<a href="index.php"><img src="<?php print html_get_theme_images_path('tab_console.gif');?>" alt="<?php echo _('Console');?>" align="absmiddle" border="0"></a><a href="graph_view.php"><img src="<?php print html_get_theme_images_path('tab_graphs.gif');?>" alt="<?php echo _('Graphs');?>" align="absmiddle" border="0"></a>
					</td>
					<td align="right">
						<img src="<?php print html_get_theme_images_path('cacti_backdrop.gif');?>" align="absmiddle">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="2" bgcolor="#<?php print $colors['main_border'];?>">
		<td colspan="3">
			<img src="<?php print html_get_theme_images_path('transparent_line.gif');?>" width="170" height="2" border="0"><br>
		</td>
	</tr>
	<tr height="5" bgcolor="#<?php print $colors['navbar_background'];?>">
		<td colspan="3">
			<table width="100%">
				<tr>
					<td>
						<?php draw_navigation_text();?>
					</td>
						<?php if (read_config_option("auth_method") == "1") {
							$expire_days = api_user_expire_info($current_user["id"]);
							if (($expire_days != -1) && ($expire_days <= read_config_option("password_expire_warning"))) {
						?>
					<td align="right" class="textError">
						<?php echo _("Password expires in ") . $expire_days . _(" days");?>
					</td>
						<?php } } ?>
					<td align="right">
						<?php if (read_config_option("auth_method") != "0") {
						    printf( _("Logged in as") . " <strong>%s</strong>", $current_user["username"]);
                            print "(<a href='logout.php'>" . _("Logout") . "</a>)&nbsp;";
                        } ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td bgcolor="#<?php print $colors['console_menu_background'];?>" colspan="1" height="8" width="135" style="background-image: url(<?php print html_get_theme_images_path('shadow_gray.gif');?>); background-repeat: repeat-x; border-right: #<?php print $colors['console_menu_border'];?> 1px solid;">
			<img src="<?php print html_get_theme_images_path('transparent_line.gif');?>" width="135" height="2" border="0"><br>
		</td>
		<td colspan="2" height="8" style="background-image: url(<?php print html_get_theme_images_path('shadow.gif');?>); background-repeat: repeat-x;" bgcolor="#<?php print $colors['console_menu_background'];?>">

		</td>
	</tr>
	<tr height="5">
		<td valign="top" rowspan="2" width="135" style="padding: 5px; border-right: #<?php print $colors['console_menu_border'];?> 1px solid;" bgcolor='#<?php print $colors['console_menu_background'];?>'>
			<table bgcolor="#<?php print $colors['console_menu_background'];?>" width="100%" cellpadding="1" cellspacing="0" border="0">
				<?php draw_menu();?>
			</table>

			<img src="<?php print html_get_theme_images_path('transparent_line.gif');?>" width="135" height="5" border="0"><br>
			<p align="center"><a href='about.php'><img src="<?php print html_get_theme_images_path('cacti_logo.gif');?>" border="0"></a></p>
		</td>
		<td></td>
	</tr>
	<tr>
		<td width="135" height="500"></td>
		<td width="100%" valign="top"><?php display_output_messages();?>
