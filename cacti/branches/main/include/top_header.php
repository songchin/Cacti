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

global $colors;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Cacti</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<link href="include/main.css" rel="stylesheet">
	<link href="images/favicon.ico" rel="shortcut icon">
	<script type="text/javascript" src="include/layout.js"></script>
<?php if (isset($refresh)) { print "\t<meta http-equiv=refresh content=\"" . $refresh["seconds"] . "; url='" . $refresh["page"] . "'\">\n"; } ?>
</head>
<body id='body' onResize='pageResize()' onLoad='pageInitialize()'>
<div id='header'>
	<div id=logobar' class='logobar'></div>
	<div id='navbar' class='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", "Console", "index.php");?>
				<?php echo draw_header_tab("graphs", "Graphs", "graph_view.php");?>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left'>
			<?php print draw_navigation_text() . "\n";?>
		</div>
		<div style='float:right'>
			<?php if (read_config_option("auth_method") != 0) { ?>Logged in as <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="logout.php">Logout</a>)
<?php } ?>
		</div>
	</div>
</div>
<div id='wrapper'>
	<div id='menu'>
		<?php draw_menu();?>
		<div id='about'><a href='about.php'><img src="images/cacti_logo.gif" align="absmiddle" alt="Cacti" border="0"></a></div>
	</div>
	<div id='vsplitter' onMouseout='doneDivResize()' onMouseover='doDivResize(this,event)' onMousemove='doDivResize(this,event)'>
		<div id='vsplitter_toggle' onClick='vSplitterToggle()' title='ToggleMenu'></div>
	</div>
	<div id='content'>
	<?php display_output_messages();?>
