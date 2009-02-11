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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/api_graph.php");
include_once(CACTI_BASE_PATH . "/lib/api_tree.php");
include_once(CACTI_BASE_PATH . "/lib/api_data_source.php");
include_once(CACTI_BASE_PATH . "/lib/template.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
include_once(CACTI_BASE_PATH . "/lib/rrd.php");
include_once(CACTI_BASE_PATH . "/lib/data_query.php");
include_once(CACTI_BASE_PATH . "/lib/form_graphs.php");

define("MAX_DISPLAY_PAGES", 21);

$graph_actions = api_plugin_hook_function('graphs_action_array', $graph_actions);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'graph_diff':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		graph_diff();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'item':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		item();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'graph_remove':
		graph_remove();

		header("Location: graphs.php");
		break;
	case 'graph_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		graph_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		graph();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

?>
