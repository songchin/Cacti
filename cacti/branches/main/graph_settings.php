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

$guest_account = true;
include("./include/auth.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
	case 'save':
		form_save();

		break;
	default:

		// We must exempt ourselves from the page refresh, or else the settings page could update while the user is making changes
		$_SESSION['custom'] = 1;
		include_once(CACTI_BASE_PATH . "/include/top_graph_header.php");
		unset($_SESSION['custom']);

		settings();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $settings_graphs;

	while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
		while (list($field_name, $field_array) = each($tab_fields)) {
			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					db_execute("replace into settings_graphs (user_id,name,value) values (" . $_SESSION["sess_user_id"] . ",'$sub_field_name', '" . (isset($_POST[$sub_field_name]) ? $_POST[$sub_field_name] : "") . "')");
				}
			}else{
				db_execute("replace into settings_graphs (user_id,name,value) values (" . $_SESSION["sess_user_id"] . ",'$field_name', '" . (isset($_POST[$field_name]) ? $_POST[$field_name] : "") . "')");
			}
		}
	}

	/* reset local settings cache so the user sees the new settings */
	kill_session_var("sess_graph_config_array");

	header("Location: " . get_request_var_post("referer"));
	exit;
}

/* --------------------------
    Graph Settings Functions
   -------------------------- */

function settings() {
	global $colors, $tabs_graphs, $settings_graphs, $current_user, $current_user;

	/* you cannot have per-user graph settings if cacti's user management is not turned on */
	if (read_config_option("auth_method") == 0) {
		raise_message(6);
		display_output_messages();
		return;
	}

	/* Find out whether this user has right here */
	if($current_user["graph_settings"] == "") {
		print "<strong><font size='+1' color='#FF0000'>" . __("YOU DO NOT HAVE RIGHTS TO CHANGE GRAPH SETTINGS") . "</font></strong>";
		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if (read_config_option("auth_method") != 0) {
		if ($current_user["policy_graphs"] == "1") {
			$sql_where = "where user_auth_tree.user_id is null";
		}elseif ($current_user["policy_graphs"] == "2") {
			$sql_where = "where user_auth_tree.user_id is not null";
		}

		$settings_graphs["tree"]["default_tree_id"]["sql"] = get_graph_tree_array(true);
	}

	/* draw the categories tabs on the top of the page */
	print "<form method='post' action='graph_settings.php'>\n";

	# the tabs
	print "<table width='100%' cellspacing='0' cellpadding='0' align='center'><tr><td><div class='tabs'>\n";
	if (sizeof($settings_graphs) > 0) {
		foreach (array_keys($settings_graphs) as $tab_short_name) {
			print "<div><a id='tab_" . clean_up_name($tabs_graphs[$tab_short_name]) . "' " . (($tab_short_name == "General") ? "class='tab tabSelected'" : "class='tab tabDefault'") . " onClick='selectTab(\"" . clean_up_name($tabs_graphs[$tab_short_name]) . "\")' href='#'>$tabs_graphs[$tab_short_name]</a></div>\n";
		}
	}
	print "</div></td></tr></table>\n";

	# the tab contents
	while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
		print "<table cellpadding='0' cellspacing='0' width='100%'><tr><td><div class='tab_settings' id='settings_" . clean_up_name($tabs_graphs[$tab_short_name]) . "'>\n";
		html_start_box("<strong>" . __("Graph Settings") . " (" . $tabs_graphs[$tab_short_name] . ")</strong>", "100", $colors["header"], 0, "center", "", false, "Tab_Settings_" . clean_up_name($tabs_graphs[$tab_short_name]));
		$header_items = array(__("Field"), __("Value"));
		print "<tr><td>";
		html_header($header_items, 2, true, "Header_Settings_" . clean_up_name($tabs_graphs[$tab_short_name]),'left wp100');

		$form_array = array();

		while (list($field_name, $field_array) = each($tab_fields)) {
			$form_array += array($field_name => $tab_fields[$field_name]);

			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					if (graph_config_value_exists($sub_field_name, $_SESSION["sess_user_id"])) {
						$form_array[$field_name]["items"][$sub_field_name]["form_id"] = 1;
					}

					$form_array[$field_name]["items"][$sub_field_name]["value"] =  db_fetch_cell("select value from settings_graphs where name='$sub_field_name' and user_id=" . $_SESSION["sess_user_id"]);
				}
			}else{
				if (graph_config_value_exists($field_name, $_SESSION["sess_user_id"])) {
					$form_array[$field_name]["form_id"] = 1;
				}

				$form_array[$field_name]["value"] = db_fetch_cell("select value from settings_graphs where name='$field_name' and user_id=" . $_SESSION["sess_user_id"]);
			}
		}

		draw_edit_form(
			array(
				"config" => array("no_form_tag" => true),
				"fields" => $form_array
				)
		);

		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
		print "</div></td></tr></table>\n";
	}

	if (isset($_SERVER["HTTP_REFERER"])) {
		$timespan_sel_pos = strpos($_SERVER["HTTP_REFERER"],"&predefined_timespan");
		if ($timespan_sel_pos) {
			$_SERVER["HTTP_REFERER"] = substr($_SERVER["HTTP_REFERER"],0,$timespan_sel_pos);
		}
	}

	form_hidden_box("referer", "graph_view.php","");
	form_hidden_box("save_component_graph_config","1","");

	form_save_button_alt("", "save", "save");

	?>
	<script type="text/javascript">
	<!--

	function selectTab(tab) {
		$('.tab_settings').hide();
		$('.tab').removeClass("tabSelected");
		$('.tab').addClass("tabDefault");
		$('#settings_'+tab).show();
		$('#tab_'+tab).removeClass("tabDefault");
		$('#tab_'+tab).addClass("tabSelected");
	}

	$().ready(function() {
		selectTab('General');
	});

	//-->
	</script>
	<?php

	include(CACTI_BASE_PATH . "/include/bottom_footer.php");
}
