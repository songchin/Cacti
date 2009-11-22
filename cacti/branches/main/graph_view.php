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

$guest_account = true;
include_once("./include/auth.php");
include_once(CACTI_BASE_PATH . "/lib/html_tree.php");
include_once(CACTI_BASE_PATH . "/lib/timespan_settings.php");
include_once(CACTI_BASE_PATH . "/lib/graph_view/graph_view_form.php");

if (isset($_REQUEST["action"])) {
switch(get_request_var_request("action")){
	case "ajax_list":
		get_graph_list_content();
		exit;

		break;
	case "ajax_preview":
		get_graph_preview_content();
		exit;

		break;
	case "ajax_tree_items":
		get_graph_tree_items();
		exit;

		break;
	case "ajax_tree_graphs":
		get_graph_tree_graphs();
		exit;

		break;
	default:
}
}

/* ================= input validation ================= */
input_validate_input_number(get_request_var("branch_id"));
input_validate_input_number(get_request_var("hide"));
input_validate_input_number(get_request_var("tree_id"));
input_validate_input_number(get_request_var("leaf_id"));
input_validate_input_number(get_request_var("rra_id"));
input_validate_input_regex(get_request_var_request('graph_list'), "/^([\,0-9]+)$/");
input_validate_input_regex(get_request_var_request('graph_add'), "/^([\,0-9]+)$/");
input_validate_input_regex(get_request_var_request('graph_remove'), "/^([\,0-9]+)$/");
/* ==================================================== */

if (isset($_GET["hide"])) {
	if ((get_request_var("hide") == "0") || (get_request_var("hide") == "1")) {
		/* only update expand/contract info is this user has rights to keep their own settings */
		if ((isset($current_user)) && ($current_user["graph_settings"] == "on")) {
			db_execute("delete from settings_tree where graph_tree_item_id=" . $_GET["branch_id"] . " and user_id=" . $_SESSION["sess_user_id"]);
			db_execute("insert into settings_tree (graph_tree_item_id,user_id,status) values (" . get_request_var("branch_id") . "," . $_SESSION["sess_user_id"] . "," . get_request_var("hide") . ")");
		}
	}
}

if (preg_match("/action=(tree|preview|list)/", get_browser_query_string())) {
	$_SESSION["sess_graph_view_url_cache"] = get_browser_query_string();
}

/* set default action */
if (!isset($_REQUEST["action"])) {
	switch (read_graph_config_option("default_view_mode")) {
	case GRAPH_TREE_VIEW:
		$_REQUEST["action"] = "tree";

		break;
	case GRAPH_LIST_VIEW:
		$_REQUEST["action"] = "list";

		break;
	case GRAPH_PREVIEW_VIEW:
		$_REQUEST["action"] = "preview";

		break;
	}
}

switch (get_request_var_request("action")) {
case 'tree':
	include_once(CACTI_BASE_PATH . "/include/top_graph_header.php");

	if ((read_config_option("auth_method") != 0) && (empty($current_user["show_tree"]))) {
		print "<strong><font size='+1' color='FF0000'>" . __("YOU DO NOT HAVE RIGHTS FOR TREE VIEW") . "</font></strong>"; exit;
	}

	?>
	<script type="text/javascript" src="<?php echo URL_PATH; ?>include/js/jstree/cactiTree.js"></script>
	<?php

	/* if cacti's builtin authentication is turned on then make sure to take
	graph permissions into account here. if a user does not have rights to a
	particular graph; do not show it. they will get an access denied message
	if they try and view the graph directly. */

	$access_denied = false;
	$tree_parameters = array();

	/* don't even print the table if there is not >1 tree */
	if (isset($_SESSION["sess_view_tree_id"])) {
		if (read_config_option("auth_method") != 0) {
			/* take tree permissions into account here, if the user does not have permission
			give an "access denied" message */
			$access_denied = !(is_tree_allowed($_SESSION["sess_view_tree_id"]));

			if ($access_denied == true) {
				print "<strong><font size='+1' color='FF0000'>" . __("ACCESS DENIED") . "</font></strong>"; exit;
			}
		}
	}

	break;
case 'preview':
	include_once(CACTI_BASE_PATH . "/include/top_graph_header.php");

	?>
	<script type='text/javascript'>
	<!--

	$().ready(function() {
		$.get("graph_view.php?action=ajax_preview", function (data) {
			$("#graph_content").html(data);
		});
	});

	-->
	</script><?php

	break;
case 'list':
	include_once(CACTI_BASE_PATH . "/include/top_graph_header.php");

	?>
	<script type='text/javascript'>
	<!--

	$().ready(function() {
		$.get("graph_view.php?action=ajax_list", function (data) {
			$("#graph_content").html(data);
			SetSelections();
		});
	});

	-->
	</script><?php

	break;
}

include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
