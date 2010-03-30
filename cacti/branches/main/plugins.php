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

include('./include/auth.php');

$plugins = plugins_retrieve_list();

/* tab information */
$ptabs = array(
	'all'       => __('All'),
	'current'   => __('Installed'),
	'uninstalled' => __('Uninstalled')
);

$ptabs = api_plugin_hook_function ('plugin_management_tabs', $ptabs);

$status_names = array(__('Not Installed'), __('Active'), __('Awaiting Configuration'), __('Awaiting Upgrade'), __('Installed'));

/* set the default settings category */
load_current_session_value('tab', 'sess_plugins_tab', 'all');
$current_tab = $_REQUEST['tab'];

$modes = array('all', 'install', 'uninstall', 'disable', 'enable', 'check');

// Check to see if we are installing, etc...
if (isset($_GET['mode']) && in_array($_GET['mode'], $modes)  && isset($_GET['id'])) {
	input_validate_input_regex(get_request_var('id'), '/^([a-zA-Z0-9]+)$/');

	$mode = $_GET['mode'];
	$id = sanitize_search_string($_GET['id']);

	switch ($mode) {
		case 'install':
			api_plugin_install($id);
			$plugins = plugins_retrieve_list();
			break;
		case 'uninstall':
			if (!in_array($id, $plugins))
				break;
			api_plugin_uninstall($id);
			Header("Location: plugins.php\n\n");
			exit;
			break;
		case 'disable':
			if (!in_array($id, $plugins))
				break;
			api_plugin_disable($id);
			break;
		case 'enable':
			if (!in_array($id, $plugins))
				break;
			api_plugin_enable($id);
			break;
		case 'check':
			if (!in_array($id, $plugins))
				break;
			break;
	}
}

include(CACTI_BASE_PATH . "/include/top_header.php");

plugins_draw_tabs($ptabs, $current_tab);

html_start_box('<strong>' . __('Plugins') . ' (' . $ptabs[$current_tab] . ')</strong>', '100', $colors['header'], '3', 'center', '');

print "<tr><td><table width='100%'>";

switch ($current_tab) {
	case 'all':
		plugins_show('all');
		break;
	case 'current':
		plugins_show('current');
		break;
	case 'uninstalled':
		plugins_show('uninstalled');
		break;
	default:
		api_plugin_hook_function('plugin_management_tab_content', $current_tab);
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

function plugins_retrieve_list() {
	$plugins = array();
	$temp = db_fetch_assoc('SELECT directory FROM plugin_config ORDER BY name');
	foreach ($temp as $t) {
		$plugins[] = $t['directory'];
	}
	return $plugins;
}

function plugins_show($status = 'all') {
	global $plugins, $colors, $plugin_architecture, $config, $status_names;

	$cinfo = array();
	sort($plugins);

	print "<table width='100%' cellspacing=0 cellpadding=3>";
	$x = 0;

	$newplugins = array();
	$debug_log  = array();
	$cinfo      = plugins_get_plugin_info();

	$path       = CACTI_BASE_PATH . '/plugins/';
	$dh         = opendir($path);

	/* validate contents of the plugin directory */
	while (($file = readdir($dh)) !== false) {
		/* only scan directories, ignore unix hidden directories */
		if (is_dir("$path/$file") && (! preg_match("/^\..*/", $file))) {
			/* is a plugin directory */
			if (file_exists("$path/$file/setup.php")) {
				/* the plugin is not installed */
				if (!in_array($file, $plugins)) {
					/* check for a plugin that conflicts with an installed plugin */
					$plugin_functions = plugins_scan_functions("$path/$file/setup.php");

					if (sizeof($plugin_functions)) {
					foreach($plugin_functions as $plugin_function) {
						if (function_exists($plugin_function)) {
							$debug_log[] = __("Plugin Directory <strong>'$file'</strong> Can not be installed, as it contains function '$plugin_function' which is already defined");
							break;
						}
					}
					}

					if (in_array('plugin_' . $file . '_install', $plugin_functions)) {
						include_once("$path/$file/setup.php");
						if (function_exists('plugin_' . $file . '_version')) {
							$function     = 'plugin_' . $file . '_version';
							$cinfo[$file] = $function();
							$newplugins[] = $file;
							$cinfo[$file]['status'] = 0;
						}else{
							$cinfo['version']  = __('Unknown');
							$cinfo['author']   = __('Unknown');
							$cinfo['homepage'] = __('Unknown');
							$newplugins[]      = $file;
							$cinfo[$file]['status']   = 0;

							$debug_log[] = __("Plugin Directory <strong>'$file'</strong> appears to lack a required version function");
						}
					}elseif (in_array('plugin_init_' . $file, $plugin_functions)) {
						$debug_log[] = __("Plugin Directory <strong>'$file'</strong> appears by a PIA 1.x Plugin and is not supported");
					}else{
						$debug_log[] = __("Plugin Directory <strong>'$file'</strong> does not appear valid and may be renamed plugin directory");
					}
				}else{
					$newplugins[] = $file;
				}
			}else{
				$debug_log[] = __("Plugin Directory <strong>'$file'</strong> does not appear to be a plugin directory");
			}
		}
	}
	closedir($dh);

	if (count($newplugins)) {
		foreach ($newplugins as $plugin) {
			$show = true;
			switch($status) {
			case 'all':
				break;
			case 'current':
				if (!isset($cinfo[$plugin]['status']) || $cinfo[$plugin]['status'] == 0) {
					$show = false;
				}
				break;
			case 'uninstalled':
				if ($cinfo[$plugin]['status'] != 0) {
					$show = false;
				}
				break;
			default:
				break;
			}

			if ($show) {
				if (isset($cinfo[$plugin])) {
					if ($x == 0) {
						print "<tr><td width='50%'>";
					} else {
						print '</td><td>';
					}

					if (!isset($info[$plugin]['version'])) {
						$info[$plugin]['version'] = '';
					}

					if (!isset($cinfo[$plugin]['webpage'])) {
						if (isset($cinfo[$plugin]['homepage'])) {
							$cinfo[$plugin]['webpage'] = $cinfo[$plugin]['homepage'];
						}
					}

					print "<table width='100%'>";
					html_header(array((isset($cinfo[$plugin]['name']) ? $cinfo[$plugin]['name'] : $plugin)), 2);
					form_alternate_row_color();
					print "<td width='50%'><strong>" . __("Directory:") . "</strong></td><td>$plugin</td>";
					form_alternate_row_color();
					print '<td><strong>' . __("Version:") . '</strong></td><td>' . (isset($cinfo[$plugin]['version']) ? $cinfo[$plugin]['version'] : '') . '</td>';
					form_alternate_row_color();
					print '<td><strong>' . __("Author:") . '</strong></td><td>' . (isset($cinfo[$plugin]['author']) && $cinfo[$plugin]['author'] != '' ? (isset($cinfo[$plugin]['email']) && $cinfo[$plugin]['email'] != '' ? "<a href='" . htmlspecialchars("mailto:" . $cinfo[$plugin]['email']) . "'>" . $cinfo[$plugin]['author'] . '</a>'  : $cinfo[$plugin]['author']) : '') . '</td>';
					form_alternate_row_color();
					print '<td><strong>' . __("Home Page:") . '</strong></td><td>' . (isset($cinfo[$plugin]['webpage']) && $cinfo[$plugin]['webpage'] != '' ? "<a href='" . htmlspecialchars($cinfo[$plugin]['webpage']) . "'>" . $cinfo[$plugin]['webpage'] . '</a>' : '') . '</td>';
					form_alternate_row_color();
					print '<td><strong>' . __("Status:") . '</strong></td><td>' . $status_names[$cinfo[$plugin]['status']] . '</td>';
					form_alternate_row_color();

					$links = array('install' => __('Install'), 'uninstall' => __('Uninstall'), 'enable' => __('Enable'), 'disable' => __('Disable'), 'check' => __('Check'));

					switch ($cinfo[$plugin]['status']) {
						case 0:	//Not Installed
							$links['install'] = "<a href='" . htmlspecialchars("plugins.php?mode=install&id=$plugin") . "'><b>Install</b></a>";
							break;
						case 1:	// Currently Active
							$links['uninstall'] = "<a href='" . htmlspecialchars("plugins.php?mode=uninstall&id=$plugin") . "'><b>Uninstall</b></a>";
							$links['disable'] = "<a href='" . htmlspecialchars("plugins.php?mode=disable&id=$plugin") . "'><b>Disable</b></a>";
							break;
						case 2:	// Needs Configuring
							$links['check'] = "<a href='" . htmlspecialchars("plugins.php?mode=check&id=$plugin") . "'><b>Check</b></a>";
							break;
						case 3:	// Needs Upgrade
							$links['check'] = "<a href='" . htmlspecialchars("plugins.php?mode=check&id=$plugin") . "'><b>Check</b></a>";
							break;
						case 4:	// Installed but not active
							$links['enable'] = "<a href='" . htmlspecialchars("plugins.php?mode=enable&id=$plugin") . "'><b>Enable</b></a>";
							$links['uninstall'] = "<a href='" . htmlspecialchars("plugins.php?mode=uninstall&id=$plugin") . "'><b>Uninstall</b></a>";
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
		}
	} else {
		print '<center>' . __("There are no Uninstalled Plugins") . '</center>';
	}

	if ($x == 1) {
		print '</td><td></td></tr>';
	}

	print '</table>';

	html_end_box(FALSE);

	if (sizeof($debug_log)) {
		html_start_box('<strong>' . __('Plugin Warnings') . '</strong>', '100', $colors['header'], '3', 'center', '');

		foreach($debug_log as $message) {
			echo "<tr><td class='textHeaderLight'>" . $message . "</td></tr>";
		}

		html_end_box();
	}
}

function plugins_scan_functions($file) {
	$array = file($file);
	if (sizeof($array)) {
	foreach($array as $line) {
		$line = plugins_remove_spaces($line);

		if (strtolower(substr($line, 0, 8)) == "function") {
			$parts   = explode(" ", $line);
			$prefunc = $parts[1];
			$posit   = strpos($prefunc, "(");
			if ($posit != 0) {
				$prefunc = substr($prefunc, 0, $posit);
			}
			$functions[] = trim($prefunc);
		}
	}
	}

	return $functions;
}

function plugins_remove_spaces($string) {
	while ( true ) {
		$string = str_replace("  ", " ", $string);
		if (!substr_count($string, "  ")) break;
	}

	return $string;
}

function plugins_get_plugin_info() {
	$cinfo = array();
	$info  = db_fetch_assoc('SELECT * from plugin_config');
	if (is_array($info)) {
		foreach($info as $inf) {
			$cinfo[$inf['directory']] = $inf;
			$cinfo[$inf['directory']]['changes']='';
		}
	}
	return $cinfo;
}
