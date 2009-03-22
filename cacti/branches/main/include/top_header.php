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

global $colors, $config;

$page_title = api_plugin_hook_function('page_title', 'Cacti');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<link type="text/css" href="<?php echo URL_PATH; ?>include/main.css" rel="stylesheet">
	<link type="text/css" href="<?php echo URL_PATH; ?>include/jquery.autocomplete.css" rel="stylesheet">
	<link href="<?php echo URL_PATH; ?>images/favicon.ico" rel="shortcut icon">
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/layout.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/jquery/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/jquery/jquery.bgiframe.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/jquery/jquery.ajaxQueue.js"></script>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/jquery/jquery.tablednd.js"></script>

<?php if (isset($refresh)) { print "\t<meta http-equiv=refresh content=\"" . $refresh["seconds"] . "; url='" . $refresh["page"] . "'\">\n"; }

api_plugin_hook('page_head');

 ?>
</head>
<body id='body' onResize='pageResize()' onLoad='pageInitialize()'>
<div id='header'>
	<div id='logobar' class='logobar'></div>
	<div id='navbar' class='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", "Console", URL_PATH . "index.php");?>
				<?php echo draw_header_tab("graphs", "Graphs", URL_PATH . "graph_view.php");?>
				<?php api_plugin_hook('top_header_tabs'); ?>
			</ul>
		</div>
		<div id='navbar_r'>
			<ul>
				<li id="tab_help" class="notselected"><a href="<?php echo pagehelp_url()?>" target="_blank" title="Help">Help</a></li>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left'>
			<?php print draw_navigation_text() . "\n";?>
		</div>
		<div style='float:right'><?php
			if (read_config_option("auth_method") != 0) { $date = date_time_format();?><strong><?php echo date("D, " . $date . " T");?></strong>&nbsp;&nbsp;&nbsp;Logged in as <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="<?php echo URL_PATH; ?>logout.php">Logout</a>)<?php } ?>
		</div>
	</div>
</div>
<div id='wrapper' style='opacity:0;'>
	<div id='menu'>
		<?php draw_menu();?>
		<table align='center' style='margin-top:10px;'><tr><td><a href='<?php echo URL_PATH; ?>about.php'><img src="<?php echo URL_PATH; ?>images/cacti_logo.gif" align="middle" alt="Cacti" style='border-width:0px;'></a></td></tr></table>
	</div>
	<div id='vsplitter' onMouseout='doneDivResize()' onMouseover='doDivResize(this,event)' onMousemove='doDivResize(this,event)'>
		<div id='vsplitter_toggle' onClick='vSplitterToggle()' onMouseover='vSplitterEm()' onMouseout='vSplitterUnEm()' title='Hide/Unhide Menu'></div>
	</div>
	<div id='content'>
	<?php display_output_messages();?>
