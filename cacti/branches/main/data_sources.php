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

include ("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/utility.php");
include_once(CACTI_BASE_PATH . "/lib/form_data_sources.php");
include_once(CACTI_BASE_PATH . "/lib/api_graph.php");
include_once(CACTI_BASE_PATH . "/lib/api_data_source.php");
include_once(CACTI_BASE_PATH . "/lib/template.php");
include_once(CACTI_BASE_PATH . "/lib/html_form_template.php");
include_once(CACTI_BASE_PATH . "/lib/rrd.php");
include_once(CACTI_BASE_PATH . "/lib/data_query.php");

define("MAX_DISPLAY_PAGES", 21);

$ds_actions = array(
	1 => "Delete",
	2 => "Change Data Template",
	3 => "Change Host",
	8 => "Reapply Suggested Names",
	4 => "Duplicate",
	5 => "Convert to Data Template",
	6 => "Enable",
	7 => "Disable"
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		data_source_form_save();

		break;
	case 'actions':
		data_source_form_actions();

		break;
	case 'rrd_add':
		data_source_rrd_add();

		break;
	case 'rrd_remove':
		data_source_rrd_remove();

		break;
	case 'data_source_data_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_source_data_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'ds_remove':
		ds_remove();

		header ("Location: data_sources.php");
		break;
	case 'data_source_edit':
		data_source_edit();

		break;

	case 'data_source_toggle_status':
		data_source_toggle_status();

		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_source();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

?>
