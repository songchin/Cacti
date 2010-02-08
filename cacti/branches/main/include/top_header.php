<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<link type="text/css" href="<?php echo CACTI_URL_PATH; ?>include/main.css" rel="stylesheet">
	<link type="text/css" href="<?php echo CACTI_URL_PATH; ?>include/jquery.autocomplete.css" rel="stylesheet">
	<link type="text/css" href="<?php echo CACTI_URL_PATH; ?>include/dd.css" rel="stylesheet">
	<link type="text/css" media="screen" href="<?php echo CACTI_URL_PATH; ?>include/css/colorpicker.css" rel="stylesheet">
	<link type="text/css" media="screen" href="<?php echo CACTI_URL_PATH; ?>include/css/cacti_dd_menu.css" rel="stylesheet">	
	<link href="<?php echo CACTI_URL_PATH; ?>images/favicon.ico" rel="shortcut icon">
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/layout.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.fg.menu.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.bgiframe.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.ajaxQueue.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.tablednd.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.dropdown.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/jquery.dd.js"></script>
	<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/jquery/colorpicker.js"></script>

<?php if (isset($refresh)) { print "\t<meta http-equiv=refresh content=\"" . $refresh["seconds"] . "; url='" . $refresh["page"] . "'\">\n"; }
initializeCookieVariable();
api_plugin_hook('page_head');
?>
</head>
<body id='body'>
<script type="text/javascript" src="<?php echo CACTI_URL_PATH; ?>include/js/wztooltip/wz_tooltip.js"></script>
<div id='header'>
	<div id='logobar' class='logobar'></div>
	<div id='navbar' class='navbar'>
		<div id='navbar_l'>
			<ul>
				<?php echo draw_header_tab("console", __("Console"), CACTI_URL_PATH . "index.php");?>
				<?php echo draw_header_tab("graphs", __("Graphs"), CACTI_URL_PATH . "graph_view.php");?>
				<?php api_plugin_hook('top_header_tabs'); ?>
			</ul>
		</div>
	</div>
	<div id='navbrcrumb'>
		<div style='float:left;'>
			<?php print draw_navigation_text() . "\n";?>
		</div>
		<div style='float:right;'>
			<a href="<?php echo cacti_wiki_url();?>" target="_blank">
			<img src="<?php echo CACTI_URL_PATH; ?>images/help.gif" title="<?php print __("Help");?>" alt="<?php print __("Help");?>" align="top">
			</a>
		</div>
		<div style='float:right'>
		<?php	if (read_config_option("auth_method") != 0) {
					if(read_config_option('i18n_timezone_support') != 0) {
						?><a href="#" id="menu_timezones" rel="<?php echo CACTI_URL_PATH; ?>"><span id="date_time_format"><strong><?php echo __date("D, " . date_time_format() . " T");?></a></span><?php
					}else {
						?><span id="date_time_format"><strong><?php echo __date("D, " . date_time_format() . " T");?></span><?php
					}
				?>
					</strong>&nbsp;&nbsp;&nbsp;<?php print __("Logged in as");?> <strong><?php print db_fetch_cell("select username from user_auth where id=" . $_SESSION["sess_user_id"]);?></strong> (<a href="<?php echo CACTI_URL_PATH; ?>logout.php"><?php print __("Logout");?></a>)
		<?php } ?>
		</div>
		<?php if(read_config_option('i18n_support') != 0) {?>
		<div style='float:right;'>
			<a href="#" id="menu_languages" rel="<?php echo CACTI_URL_PATH; ?>"><img src="<?php echo CACTI_URL_PATH; ?>images/flag_icons/<?php print CACTI_COUNTRY;?>.gif" align="top">&nbsp;<?php print CACTI_LANGUAGE;?></a>
		</div>
		<div id="loading" style="display:none; float:right"><img src="<?php echo CACTI_URL_PATH; ?>images/load_small.gif" align="top" alt="<?php print __("loading");?>">LOADING</div>
		<?php }?>
	</div>
</div>
<div id='wrapper' style='opacity:0;'>
	<div id='menu'>
		<?php draw_menu();?>
		<table align='left' style='margin-top:25px;'><tr><td><a href='<?php echo CACTI_URL_PATH; ?>about.php'><img src="<?php echo CACTI_URL_PATH; ?>images/cacti_logo.gif" align="middle" alt="Cacti"></a></td></tr></table>
	</div>
	<div id='vsplitter' onMouseout='doneDivResize()' onMouseover='doDivResize(this,event)' onMousemove='doDivResize(this,event)'>
		<div id='vsplitter_toggle' onClick='vSplitterToggle()' onMouseover='vSplitterEm()' onMouseout='vSplitterUnEm()' title='<?php echo __("Hide/Unhide Menu");?>'></div>
	</div>
	<div id='content'>
	<?php display_output_messages();
