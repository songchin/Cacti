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

include("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/xaxis/xaxis_form.php");

define("MAX_DISPLAY_PAGES", 21);

define("XAXIS_ACTION_DELETE", 1);
define("XAXIS_ACTION_DUPLICATE", 2);
$xaxis_actions = array(
	XAXIS_ACTION_DELETE => __("Delete"),
	XAXIS_ACTION_DUPLICATE => __("Duplicate")
	);

$xaxis_actions = api_plugin_hook_function('xaxis_action_array', $xaxis_actions);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

//print_r($_REQUEST);

switch (get_request_var_request("action")) {
	case 'save':
		xaxis_form_save();

		break;
	case 'actions':
		xaxis_form_actions();

		break;
	case 'item_remove':
		item_remove();

		header("Location: xaxis_presets.php?action=edit&id=" . $_GET["xaxis_id"]);
		break;
	case 'item_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		item_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		xaxis_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		xaxis();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}
