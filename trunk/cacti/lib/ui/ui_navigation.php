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

function ui_html_header_tab_make($name, $title) {
	return "<li id=\"tab_" . ui_html_escape($name) . "\" class=\"notselected\"><a href=\"javascript:navigation_select('" . ui_html_escape($name) . "')\" title=\"" . ui_html_escape(_($title)) . "\">" . ui_html_escape(_($title)) . "</a></li>\n";
}

function ui_html_header_navigation_group_make($name, $items) {
	$html  = "<div id=\"nav_" . ui_html_escape($name) . "\">\n";
	$html .= "\t<ul>\n";

	if (is_array($items)) {
		foreach ($items as $item_title => $item_url) {
			$html .= "\t\t<li><a href=\"" . ui_html_escape($item_url) . "\">" . ui_html_escape(_($item_title)) . "</a></li>\n";
		}
	}

	$html .= "\t</ul>\n";
	$html .= "</div>\n";

	return $html;
}

?>
