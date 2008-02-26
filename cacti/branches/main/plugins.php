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

include('./include/auth.php');


$plugins = plugins_retrieve_list ();

/* tab information */
$ptabs = array(
	'current' => 'Installed',
	'uninstalled' => 'Uninstalled',
);


$ptabs = api_plugin_hook_function ('plugin_management_tabs', $ptabs);

$status_names = array('Not Installed', 'Active', 'Awaiting Configuration', 'Awaiting Upgrade', 'Installed');

/* set the default settings category */
if (!isset($_GET['tab'])) {
	/* there is no selected tab; select the first one */
	$current_tab = array_keys($ptabs);
	$current_tab = $current_tab[0];
}else{
	$current_tab = $_GET['tab'];
}

$modes = array('install', 'uninstall', 'disable', 'enable', 'check');

// Check to see if we are installing, etc...
if (isset($_GET['mode']) && in_array($_GET['mode'], $modes)  && isset($_GET['id'])) {
	input_validate_input_regex(get_request_var('id'), '^([a-zA-Z0-9]+)$');

	$mode = $_GET['mode'];
	$id = sanitize_search_string($_GET['id']);

	switch ($mode) {
		case 'install':
			api_plugin_install($id);
			$plugins = plugins_retrieve_list ();
			break;
		case 'uninstall':
			if (!in_array($id, $plugins))
				break;
			api_plugin_uninstall($id);
			$plugins = plugins_retrieve_list ();
			break;
		case 'disable':
			if (!in_array($id, $plugins))
				break;
			api_plugin_disable ($id);
			break;
		case 'enable':
			if (!in_array($id, $plugins))
				break;
			api_plugin_enable ($id);
			break;
		case 'check':
			if (!in_array($id, $plugins))
				break;
			break;
	}
}

include(CACTI_BASE_PATH . "/include/top_header.php");

plugins_draw_tabs ($ptabs, $current_tab);

html_start_box('<strong>Plugins (' . $ptabs[$current_tab] . ')</strong>', '100%', $colors['header'], '3', 'center', '');
print "<tr><td><table width='100%'>";

switch ($current_tab) {
	case 'current':
		plugins_show_current();
		break;
	case 'uninstalled':
		plugins_show_uninstalled ();
		break;
	default:
		print '<br><br><br>';
}

html_end_box();
include(CACTI_BASE_PATH . "/include/bottom_footer.php");





function plugins_draw_tabs ($tabs, $current_tab) {
	/* draw the categories tabs on the top of the page */
	print "<table width='100%' cellspacing='0' cellpadding='0' align='center'><tr>\n";
	print "<td><div class='tabs'>";
	if (sizeof($tabs) > 0) {
		foreach (array_keys($tabs) as $tab_short_name) {
			print "<div class='tabDefault'><a " . (($tab_short_name == $current_tab) ? "class='tabSelected'" : "class='tabDefault'") . " href='plugins.php?tab=$tab_short_name'>$tabs[$tab_short_name]</a></div>";
		}
	}
	print "</div></td></tr></table>\n";
}

function plugins_retrieve_list () {
	$plugins = array();
	$temp = db_fetch_assoc('SELECT directory FROM plugin_config ORDER BY name');
	foreach ($temp as $t) {
		$plugins[] = $t['directory'];
	}
	return $plugins;
}

function plugins_show_current () {
	global $plugins, $colors, $plugin_architecture, $config, $status_names;

	$cinfo = array();
	sort($plugins);
	$cinfo = plugins_get_plugin_info ();

	print "<table width='100%' cellspacing=0 cellpadding=3>";
	$x = 0;

	foreach ($plugins as $plugin) {
		if (isset($cinfo[$plugin])) {

			if ($x == 0) {
				print "<tr><td width='50%'>";
			} else {
				print '</td><td>';
			}
			if (!isset($info[$plugin]['version']))
				$info[$plugin]['version'] = '';

			print "<table width='100%'>";
			html_header(array((isset($cinfo[$plugin]['name']) ? $cinfo[$plugin]['name'] : $plugin)), 2);
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print "<td width='50%'><strong>Directory:</strong></td><td>$plugin</td>";
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Version:</strong></td><td>' . (isset($cinfo[$plugin]['version']) ? $cinfo[$plugin]['version'] : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Author:</strong></td><td>' . (isset($cinfo[$plugin]['author']) && $cinfo[$plugin]['author'] != '' ? (isset($cinfo[$plugin]['email']) && $cinfo[$plugin]['email'] != '' ? "<a href='mailto:" . $cinfo[$plugin]['email'] . "'>" . $cinfo[$plugin]['author'] . '</a>'  : $cinfo[$plugin]['author']) : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Home Page:</strong></td><td>' . (isset($cinfo[$plugin]['webpage']) && $cinfo[$plugin]['webpage'] != '' ? "<a href='" . $cinfo[$plugin]['webpage'] . "'>" . $cinfo[$plugin]['webpage'] . '</a>' : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Status:</strong></td><td>' . $status_names[$cinfo[$plugin]['status']] . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);

			$links = array('install' => 'Install', 'uninstall' => 'Uninstall', 'enable' => 'Enable', 'disable' => 'Disable', 'check' => 'Check');

			switch ($cinfo[$plugin]['status']) {
				case 0:	//Not Installed
					$links['install'] = "<a href='plugins.php?mode=install&id=$plugin'><b>Install</b></a>";
					break;
				case 1:	// Currently Active
					$links['uninstall'] = "<a href='plugins.php?mode=uninstall&id=$plugin'><b>Uninstall</b></a>";
					$links['disable'] = "<a href='plugins.php?mode=disable&id=$plugin'><b>Disable</b></a>";
					break;
				case 2:	// Needs Configuring
					$links['check'] = "<a href='plugins.php?mode=check&id=$plugin'><b>Check</b></a>";
					break;
				case 3:	// Needs Upgrade
					$links['check'] = "<a href='plugins.php?mode=check&id=$plugin'><b>Check</b></a>";
					break;
				case 4:	// Installed but not active
					$links['uninstall'] = "<a href='plugins.php?mode=uninstall&id=$plugin'><b>Uninstall</b></a>";
					$links['enable'] = "<a href='plugins.php?mode=enable&id=$plugin'><b>Enable</b></a>";
					break;
			}

			print '<td></td><td>';
			$c = 1;
			foreach ($links as $temp => $link) {
				print $link;
				if ($c < count($links))
					print ' | ';
				$c++;
			}

			print '</td>';
			print '</tr></table>';
			if ($x == 1) {
				print '</td></tr>';
			}
			$x++;
			if ($x > 1) $x = 0;
		}
	}
	if ($x == 1)
		print '</td><td></td></tr>';
	print '</table>';
	html_end_box(TRUE);
}


function plugins_show_uninstalled () {
	global $plugins, $colors, $plugin_architecture, $config, $status_names;

	$cinfo = array();
	sort($plugins);

	print "<table width='100%' cellspacing=0 cellpadding=3>";
	$x = 0;

	$newplugins = array();
	$cinfo = array ();

	$path = $config['base_path'] . '/plugins/';
	$dh = opendir($path);
	while (($file = readdir($dh)) !== false) {
		if (is_dir("$path/$file")) {
			if (file_exists("$path/$file/setup.php") && !in_array($file, $plugins)) {
				include_once("$path/$file/setup.php");
				if (function_exists('plugin_' . $file . '_install') && function_exists('plugin_' . $file . '_version')) {
					$function = 'plugin_' . $file . '_version';
					$cinfo[$file] = $function();
					$cinfo[$file]['status'] = 0;
					$newplugins[] = $file;
				}
			}
		}
	}
	closedir($dh);

	if (count($newplugins)) {
	foreach ($newplugins as $plugin) {
		if (isset($cinfo[$plugin])) {

			if ($x == 0) {
				print "<tr><td width='50%'>";
			} else {
				print '</td><td>';
			}
			if (!isset($info[$plugin]['version']))
				$info[$plugin]['version'] = '';

			print "<table width='100%'>";
			html_header(array((isset($cinfo[$plugin]['name']) ? $cinfo[$plugin]['name'] : $plugin)), 2);
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print "<td width='50%'><strong>Directory:</strong></td><td>$plugin</td>";
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Version:</strong></td><td>' . (isset($cinfo[$plugin]['version']) ? $cinfo[$plugin]['version'] : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Author:</strong></td><td>' . (isset($cinfo[$plugin]['author']) && $cinfo[$plugin]['author'] != '' ? (isset($cinfo[$plugin]['email']) && $cinfo[$plugin]['email'] != '' ? "<a href='mailto:" . $cinfo[$plugin]['email'] . "'>" . $cinfo[$plugin]['author'] . '</a>'  : $cinfo[$plugin]['author']) : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Home Page:</strong></td><td>' . (isset($cinfo[$plugin]['homepage']) && $cinfo[$plugin]['homepage'] != '' ? "<a href='" . $cinfo[$plugin]['homepage'] . "'>" . $cinfo[$plugin]['homepage'] . '</a>' : '') . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);
			print '<td><strong>Status:</strong></td><td>' . $status_names[$cinfo[$plugin]['status']] . '</td>';
			form_alternate_row_color($colors['alternate'],$colors['light'], 0);

			$links = array('install' => 'Install', 'uninstall' => 'Uninstall', 'enable' => 'Enable', 'disable' => 'Disable', 'check' => 'Check');

			switch ($cinfo[$plugin]['status']) {
				case 0:	//Not Installed
					$links['install'] = "<a href='plugins.php?mode=install&id=$plugin&tab=uninstalled'><b>Install</b></a>";
					break;
				case 1:	// Currently Active
					$links['uninstall'] = "<a href='plugins.php?mode=uninstall&id=$plugin&tab=uninstalled'><b>Uninstall</b></a>";
					$links['disable'] = "<a href='plugins.php?mode=disable&id=$plugin&tab=uninstalled'><b>Disable</b></a>";
					break;
				case 2:	// Needs Configuring
					$links['check'] = "<a href='plugins.php?mode=check&id=$plugin&tab=uninstalled'><b>Check</b></a>";
					break;
				case 3:	// Needs Upgrade
					$links['check'] = "<a href='plugins.php?mode=check&id=$plugin&tab=uninstalled'><b>Check</b></a>";
					break;
				case 4:	// Installed but not active
					$links['enable'] = "<a href='plugins.php?mode=enable&id=$plugin&tab=uninstalled'><b>Enable</b></a>";
					break;
			}

			print '<td></td><td>';
			$c = 1;
			foreach ($links as $temp => $link) {
				print $link;
				if ($c < count($links))
					print ' | ';
				$c++;
			}

			print '</td>';
			print '</tr></table>';
			if ($x == 1) {
				print '</td></tr>';
			}
			$x++;
			if ($x > 1) $x = 0;
		}
	}
	} else {
		print '<center>There are no Uninstalled Plugins</center>';
	}
	if ($x == 1)
		print '</td><td></td></tr>';
	print '</table>';
	html_end_box(TRUE);
}

function plugins_get_plugin_info () {
	$cinfo = array();
	$info = db_fetch_assoc('SELECT * from plugin_config');
	if (is_array($info)) {
		foreach($info as $inf) {
			$cinfo[$inf['directory']] = $inf;
			$cinfo[$inf['directory']]['changes']='';
		}
	}
	return $cinfo;
}


