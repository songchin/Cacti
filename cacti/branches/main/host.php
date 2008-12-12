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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/api_data_source.php");
include_once(CACTI_BASE_PATH . "/lib/form_host.php");
include_once(CACTI_BASE_PATH . "/lib/form_graphs_new.php");
include_once(CACTI_BASE_PATH . "/lib/api_tree.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/api_graph.php");
include_once(CACTI_BASE_PATH . "/lib/data_query.php");
include_once(CACTI_BASE_PATH . "/lib/sort.php");
include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
include_once(CACTI_BASE_PATH . "/lib/template.php");
include_once(CACTI_BASE_PATH . "/lib/snmp.php");
include_once(CACTI_BASE_PATH . "/lib/ping.php");
include_once(CACTI_BASE_PATH . "/lib/api_device.php");

define("MAX_DISPLAY_PAGES", 21);

$device_actions = array(
	1 => "Delete",
	2 => "Enable",
	3 => "Disable",
	4 => "Change SNMP Options",
	5 => "Clear Statistics",
	6 => "Change Availability Options"
	);

$device_actions = api_plugin_hook_function('device_action_array', $device_actions);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

//print_r($_REQUEST);

switch ($_REQUEST["action"]) {
	case 'save':
		if (isset($_REQUEST["save_component_graph"])) {
			api_graphs_new_form_save();
		}else{
			api_host_form_save();
		}

		break;
	case 'actions':
		api_host_form_actions();

		break;
	case 'gt_remove':
		host_remove_gt();

		header("Location: host.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_remove':
		host_remove_query();

		header("Location: host.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_reload':
		host_reload_query();

		header("Location: host.php?action=edit&id=" . $_GET["host_id"]);
		break;
	case 'query_verbose':
		host_reload_query();

		header("Location: host.php?action=edit&id=" . $_GET["host_id"] . "&display_dq_details=true");
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		host_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		host();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

?>
