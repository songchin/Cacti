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

define("MAX_DISPLAY_PAGES", 21);

$user_actions = array(
	1 => __("Delete"),
	2 => __("Copy"),
	3 => __("Enable"),
	4 => __("Disable"),
	5 => __("Batch Copy")
	);

switch (get_request_var_request("action")) {
	case 'actions':
		form_actions();

		break;
	case 'save':
		form_save();

		break;
	case 'perm_remove':
		perm_remove();

		break;
	case 'user_edit':
	case 'user_realms_edit':
	case 'graph_settings_edit':
	case 'graph_perms_edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");
		user_edit();
		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");

		break;
	default:
		if (!api_plugin_hook_function('user_admin_action', get_request_var_request("action"))) {
			include_once(CACTI_BASE_PATH . "/include/top_header.php");
			user();
			include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		}
		break;
}

/* --------------------------
    Actions Function
   -------------------------- */

function form_actions() {
	global $colors, $user_actions, $auth_realms;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		if (get_request_var_post("drp_action") != "2") {
			$selected_items = unserialize(stripslashes(get_request_var_post("selected_items")));
		}

		if (get_request_var_post("drp_action") == "1") { /* delete */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_remove($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "2") { /* copy */
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("selected_items"));
			input_validate_input_number(get_request_var_post("new_realm"));
			/* ==================================================== */

			$new_username = get_request_var_post("new_username");
			$new_realm = get_request_var_post("new_realm", 0);
			$template_user = db_fetch_row("SELECT username, realm FROM user_auth WHERE id = " . get_request_var_post("selected_items"));
			$overwrite = array( "full_name" => get_request_var_post("new_fullname") );

			if (strlen($new_username)) {
				if (sizeof(db_fetch_assoc("SELECT username FROM user_auth WHERE username = '" . $new_username . "' AND realm = " . $new_realm))) {
					raise_message(19);
				} else {
					if (user_copy($template_user["username"], $new_username, $template_user["realm"], $new_realm, false, $overwrite) === false) {
						raise_message(2);
					} else {
						raise_message(1);
					}
				}
			}
		}

		if (get_request_var_post("drp_action") == "3") { /* enable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_enable($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "4") { /* disable */
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				user_disable($selected_items[$i]);
			}
		}

		if (get_request_var_post("drp_action") == "5") { /* batch copy */
			/* ================= input validation ================= */
			input_validate_input_number(get_request_var_post("template_user"));
			/* ==================================================== */

			$copy_error = false;
			$template = db_fetch_row("SELECT username, realm FROM user_auth WHERE id = " . get_request_var_post("template_user"));
			for ($i=0;($i<count($selected_items));$i++) {
				/* ================= input validation ================= */
				input_validate_input_number($selected_items[$i]);
				/* ==================================================== */

				$user = db_fetch_row("SELECT username, realm FROM user_auth WHERE id = " . $selected_items[$i]);
				if ((isset($user)) && (isset($template))) {
					if (user_copy($template["username"], $user["username"], $template["realm"], $user["realm"], true) === false) {
						$copy_error = true;
					}
				}
			}
			if ($copy_error) {
				raise_message(2);
			} else {
				raise_message(1);
			}
		}

		header("Location: user_admin.php");
		exit;
	}

	/* loop through each of the users and process them */
	$user_list = "";
	$user_array = array();
	$i = 0;
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */

			if (get_request_var_post("drp_action") != "2") {
				$user_list .= "<li>" . db_fetch_cell("SELECT username FROM user_auth WHERE id=" . $matches[1]) . "<br>";
			}
			$user_array[$i] = $matches[1];
		}

		$i++;
	}

	/* Check for deleting of Graph Export User */
	if ((get_request_var_post("drp_action") == "1") && (sizeof($user_array))) { /* delete */
		$exportuser = read_config_option('export_user_id');
		if (in_array($exportuser, $user_array)) {
			raise_message(22);
			header("Location: user_admin.php");
			exit;

		}
	}

	include_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $user_actions[get_request_var_post("drp_action")] . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='user_admin.php' method='post'>\n";

	if ((get_request_var_post("drp_action") == "1") && (sizeof($user_array))) { /* delete */
		print "
			<tr>
				<td class='textArea'>
					<p>" . __("Are you sure you want to delete the following users?") . "</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}
	$user_id = "";
	if ((get_request_var_post("drp_action") == "2") && (sizeof($user_array))) { /* copy */
		$user_id = $user_array[0];
		$user_realm = db_fetch_cell("SELECT realm FROM user_auth WHERE id = " . $user_id);

		print "
			<tr>
				<td class='textArea'>
					<p>" . __("Would you like to copy this user?") . "</p>
				</td>
			</tr><tr>
				<td class='textArea'>\n" .
					__("Template Username:") . " <i>" . db_fetch_cell("SELECT username FROM user_auth WHERE id=" . $user_id) . "</i>
				</td>
			</tr><tr>
				<td class='textArea'>
				" . __("New Username:");
		print form_text_box("new_username", "", "", 25);
		print "				</td>
			</tr><tr>
				<td class='textArea'>
					" . __("New Full Name:");
		print form_text_box("new_fullname", "", "", 35);
		print "				</td>
			</tr><tr>
				<td class='textArea'>
					" . __("New Realm:") . " \n";
		print form_dropdown("new_realm", $auth_realms, "", "", $user_realm, "", 0);
		print "				</td>

			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "3") && (sizeof($user_array))) { /* enable */
		print "
			<tr>
				<td class='textArea'>
					<p>" . __("Are you sure you want to enable the following users?") . "</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "4") && (sizeof($user_array))) { /* disable */
		print "
			<tr>
				<td class='textArea'>
					<p>" . __("Are you sure you want to disable the following users?") . "</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "5") && (sizeof($user_array))) { /* batch copy */
		$usernames = db_fetch_assoc("SELECT id,username FROM user_auth WHERE realm = 0 ORDER BY username");
		print "
			<tr>
				<td class='textArea'>
					<p>" . __("Are you sure you want to overwrite the selected users with the selected template users settings and permissions?  Original user Full Name, Password, Realm and Enable status will be retained, all other fields will be overwritten from template user.") . "</p>
				</td>
			</tr>
			<tr>
				<td class='textArea'>
					" . __("Template User:") . " \n";
		print form_dropdown("template_user", $usernames, "username", "id", "", "", 0);
		print "		</td>
			</tr>
			<tr>
				<td class='textArea'>
					<p>" . __("Users to update:") . "</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if (sizeof($user_array) == 0) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one user.</span></td></tr>\n";
		$save_html = "<a href='user_admin.php'><img src='images/button_cancel.gif' alt='Cancel' align='middle' border='0'></a>";

	}else{
		$save_html = "<a href='user_admin.php'><img src='images/button_no.gif' alt='Cancel' align='middle' border='0'></a> <input type='image' src='images/button_yes.gif' alt='Save' align='middle'>";
	}

	print " <tr>
			<td align='right' bgcolor='#eaeaea'>
				<input type='hidden' name='action' value='actions'>";
	if (get_request_var_post("drp_action") == "2") { /* copy */
		print "				<input type='hidden' name='selected_items' value='" . $user_id . "'>\n";
	}else{
		print "				<input type='hidden' name='selected_items' value='" . (isset($user_array) ? serialize($user_array) : '') . "'>\n";
	}
	print "				<input type='hidden' name='drp_action' value='" . get_request_var_post("drp_action") . "'>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}


/* --------------------------
    Save Function
   -------------------------- */

function form_save() {
	global $settings_graphs;

	/* graph permissions */
	if ((isset($_POST["save_component_graph_perms"])) && (!is_error_message())) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("perm_graphs"));
		input_validate_input_number(get_request_var_post("perm_trees"));
		input_validate_input_number(get_request_var_post("perm_hosts"));
		input_validate_input_number(get_request_var_post("perm_graph_templates"));
		input_validate_input_number(get_request_var_post("policy_graphs"));
		input_validate_input_number(get_request_var_post("policy_trees"));
		input_validate_input_number(get_request_var_post("policy_hosts"));
		input_validate_input_number(get_request_var_post("policy_graph_templates"));
		/* ==================================================== */

		$add_button_clicked = false;

		if (isset($_POST["add_graph_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_graphs") . "," . PERM_GRAPHS . ")");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_tree_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_trees") . "," . PERM_TREES . ")");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_host_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_hosts") . "," . PERM_HOSTS . ")");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_graph_template_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_graph_templates") . "," . PERM_GRAPH_TEMPLATES . ")");
			$add_button_clicked = true;
		}

		if ($add_button_clicked == true) {
			header("Location: user_admin.php?action=graph_perms_edit&id=" . get_request_var_post("id"));
			exit;
		}
	}

	/* user management save */
	if (isset($_POST["save_component_user"])) {
		/* ================= input validation ================= */
		input_validate_input_number(get_request_var_post("id"));
		input_validate_input_number(get_request_var_post("realm"));
		/* ==================================================== */

		if ((get_request_var_post("password") == "") && (get_request_var_post("password_confirm") == "")) {
			$password = db_fetch_cell("SELECT password FROM user_auth WHERE id = " . get_request_var_post("id"));
		}else{
			$password = md5(get_request_var_post("password"));
		}

		/* check duplicate username */
		if (sizeof(db_fetch_row("select * from user_auth where realm = " . get_request_var_post("realm") . " and username = '" . get_request_var_post("username") . "' and id != " . get_request_var_post("id")))) {
			raise_message(12);
		}

		/* check for guest or template user */
		$username = db_fetch_cell("select username from user_auth where id = " . get_request_var_post("id"));
		if ($username != get_request_var_post("username")) {
			if ($username == read_config_option("user_template")) {
				raise_message(20);
			}
			if ($username == read_config_option("guest_user")) {
				raise_message(20);
			}
		}

		/* check to make sure the passwords match; if not error */
		if (get_request_var_post("password") != get_request_var_post("password_confirm")) {
			raise_message(4);
		}

		form_input_validate(get_request_var_post("password"), "password", "" . preg_quote(get_request_var_post("password_confirm")) . "", true, 4);
		form_input_validate(get_request_var_post("password_confirm"), "password_confirm", "" . preg_quote(get_request_var_post("password")) . "", true, 4);

		$save["id"] = get_request_var_post("id");
		$save["username"] = form_input_validate(get_request_var_post("username"), "username", "^[A-Za-z0-9\._\\\@\ -]+$", false, 3);
		$save["full_name"] = form_input_validate(get_request_var_post("full_name"), "full_name", "", true, 3);
		$save["password"] = $password;
		$save["must_change_password"] = form_input_validate(get_request_var_post("must_change_password", ""), "must_change_password", "", true, 3);
		$save["show_tree"] = form_input_validate(get_request_var_post("show_tree", ""), "show_tree", "", true, 3);
		$save["show_list"] = form_input_validate(get_request_var_post("show_list", ""), "show_list", "", true, 3);
		$save["show_preview"] = form_input_validate(get_request_var_post("show_preview", ""), "show_preview", "", true, 3);
		$save["graph_settings"] = form_input_validate(get_request_var_post("graph_settings", ""), "graph_settings", "", true, 3);
		$save["login_opts"] = form_input_validate(get_request_var_post("login_opts"), "login_opts", "", true, 3);
		$save["policy_graphs"] = form_input_validate(get_request_var_post("policy_graphs", get_request_var_post("_policy_graphs")), "policy_graphs", "", true, 3);
		$save["policy_trees"] = form_input_validate(get_request_var_post("policy_trees", get_request_var_post("_policy_trees")), "policy_trees", "", true, 3);
		$save["policy_hosts"] = form_input_validate(get_request_var_post("policy_hosts", get_request_var_post("_policy_hosts")), "policy_hosts", "", true, 3);
		$save["policy_graph_templates"] = form_input_validate(get_request_var_post("policy_graph_templates", get_request_var_post("_policy_graph_templates")), "policy_graph_templates", "", true, 3);
		$save["realm"] = get_request_var_post("realm", 0);
		$save["enabled"] = form_input_validate(get_request_var_post("enabled", ""), "enabled", "", true, 3);
		$save = api_plugin_hook_function('user_admin_setup_sql_save', $save);

		if (!is_error_message()) {
			$user_id = sql_save($save, "user_auth");

			if ($user_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}

			if (isset($_POST["save_component_realm_perms"])) {
				db_execute("DELETE FROM user_auth_realm WHERE user_id = " . $user_id);

				while (list($var, $val) = each($_POST)) {
					if (eregi("^[section]", $var)) {
						if (substr($var, 0, 7) == "section") {
						    db_execute("REPLACE INTO user_auth_realm (user_id,realm_id) VALUES (" . $user_id . "," . substr($var, 7) . ")");
						}
					}
				}
			}elseif (isset($_POST["save_component_graph_settings"])) {
				while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
					while (list($field_name, $field_array) = each($tab_fields)) {
						if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
							while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
								db_execute("REPLACE INTO settings_graphs (user_id,name,value) VALUES (" . (!empty($user_id) ? $user_id : get_request_var_post("id")) . ",'$sub_field_name', '" . get_request_var_post($sub_field_name, "") . "')");
							}
						}else{
							db_execute("REPLACE INTO settings_graphs (user_id,name,value) VALUES (" . (!empty($user_id) ? $user_id : $_POST["id"]) . ",'$field_name', '" . get_request_var_post($field_name) . "')");
						}
					}
				}

				/* reset local settings cache so the user sees the new settings */
				kill_session_var("sess_graph_config_array");
			}elseif (isset($_POST["save_component_graph_perms"])) {
				db_execute("UPDATE user_auth SET
					policy_graphs = " . get_request_var_post("policy_graphs") . ",
					policy_trees = " . get_request_var_post("policy_trees") . ",
					policy_hosts = " . get_request_var_post("policy_hosts") . ",
					policy_graph_templates = " . get_request_var_post("policy_graph_templates") . "
					WHERE id = " . get_request_var_post("id"));
			} else {
				api_plugin_hook('user_admin_user_save');
			}
		}
	}

	/* redirect to the appropriate page */
	if (isset($_POST["save_component_realm_perms"])) {
		header("Location: user_admin.php?action=user_realms_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}elseif (isset($_POST["save_component_graph_perms"])) {
		header("Location: user_admin.php?action=graph_perms_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}elseif (isset($_POST["save_component_graph_settings"])) {
		header("Location: user_admin.php?action=graph_settings_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}elseif (isset($_POST["save_component_user"])) {
		header("Location: user_admin.php?action=user_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}else{
		header(api_plugin_hook_function('user_admin_save_location', "Location: user_admin.php?action=user_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id)));
	}
}

/* --------------------------
    Graph Permissions
   -------------------------- */

function perm_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	input_validate_input_number(get_request_var("user_id"));
	/* ==================================================== */

	if (get_request_var("type") == "graph") {
		db_execute("DELETE FROM user_auth_perms WHERE type = " . PERM_GRAPHS . " AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "tree") {
		db_execute("DELETE FROM user_auth_perms WHERE type = " . PERM_TREES . " AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "host") {
		db_execute("DELETE FROM user_auth_perms WHERE type = " . PERM_HOSTS . " AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "graph_template") {
		db_execute("DELETE FROM user_auth_perms WHERE type = " . PERM_GRAPH_TEMPLATES . " AND user_id=" . get_request_var("user_id") . " and item_id = " . get_request_var("id"));
	}

	header("Location: user_admin.php?action=graph_perms_edit&id=" . get_request_var("user_id"));
}

function graph_perms_edit() {
	global $colors;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$graph_policy_array = array(
		1 => __("Allow"),
		2 => __("Deny")
	);

	if (!empty($_GET["id"])) {
		$policy = db_fetch_row("SELECT policy_graphs,policy_trees,policy_hosts,policy_graph_templates FROM user_auth WHERE id = " . get_request_var("id"));

		$header_label = __("[edit: ") . db_fetch_cell("SELECT username FROM user_auth WHERE id = " . get_request_var("id")) . "]";
	}

	?>
	<script type="text/javascript">
	<!--
	$().ready(function() {
		$("#host").autocomplete("./lib/ajax/get_hosts_detailed.php", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#host").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#perm_hosts").val(data[1]);
			}else{
				$(this).parent().find("#perm_hosts").val(0);
			}
		});
		$("#graph").autocomplete("./lib/ajax/get_graphs_brief.php?id=<?php print get_request_var("id", 0);?>", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#graph").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#perm_graphs").val(data[1]);
			}else{
				$(this).parent().find("#perm_graphs").val(0);
			}
		});
	});
	-->
	</script>
	<?php

	#print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='user_admin'>\n";
	/* box: graph permissions */
	html_start_box("<strong>" . __("Graph Permissions (By Graph)") . "</strong>", "100%", $colors["header"], "3", "center", "");

	$graphs = db_fetch_assoc("SELECT
		graph_templates_graph.local_graph_id AS id,
		graph_templates_graph.title_cache AS name
		FROM graph_templates_graph
		LEFT JOIN user_auth_perms ON (graph_templates_graph.local_graph_id=user_auth_perms.item_id AND user_auth_perms.type=" . PERM_GRAPHS . ")
		WHERE graph_templates_graph.local_graph_id > 0
		AND user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_templates_graph.title_cache");

	?>

	<tr bgcolor="#<?php print $colors['form_alternate1'];?>">
		<td style='width:100px;white-space:nowrap;'>
			<font class="textEditTitle" title="The default allow/deny graph policy for this user"><?php print __("Default Policy");?></font>
		</td>
		<td align="left">
			<?php form_dropdown("policy_graphs",$graph_policy_array,"","",$policy["policy_graphs"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1" cellspacing="0">
				<?php
				if (sizeof($graphs) > 0) {
					foreach ($graphs as $item) {
						form_alternate_row_color("graph" . $item["id"], true);
						print "<td><strong>" . $item["name"] . "</strong>" . (($policy["policy_graphs"] == "1") ? __(" - No Access") : __(" - Accessible")) . "</td>
								<td align='right'><a href='" . htmlspecialchars("user_admin.php?action=perm_remove&type=graph&id=" . $item["id"] . "&user_id=" . $_GET["id"]) . "'><img class='buttonSmall' src='images/delete_icon.gif' alt='" . __("Delete") . "' align='absmiddle'></a>&nbsp;</td>\n";
						form_end_row();
					}
				}else{
					print "<tr><td><em>" . __("No Graphs") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='left'>
		<tr>
			<td width='40'>
				<input type="submit" value="<?php print __("Add");?>" name="add_graph_y">
			</td>
			<td align='left' width='1'>
				<input class="ac_field" type="text" id="graph" size="70" value="">
				<input type="hidden" id="perm_graphs" name="perm_graphs">
			</td>
		</tr>
	</table>
	<?php

	/* box: device permissions */
	html_start_box("<strong>" . __("Graph Permissions (By Device)") . "</strong>", "100%", $colors["header"], "3", "center", "");

	$hosts = db_fetch_assoc("SELECT
		host.id,
		CONCAT('',host.description,' (',host.hostname,')') as name
		FROM host
		LEFT JOIN user_auth_perms ON (host.id = user_auth_perms.item_id AND user_auth_perms.type = " . PERM_HOSTS . ")
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY host.description,host.hostname");

	?>
	<tr bgcolor="#<?php print $colors['form_alternate1'];?>">
		<td style='width:100px;white-space:nowrap;'>
			<font class="textEditTitle" title="<?php print __("The default allow/deny graph policy for this user");?>"><?php print __("Default Policy");?></font>
		</td>
		<td align="left">
			<?php form_dropdown("policy_hosts",$graph_policy_array,"","",$policy["policy_hosts"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1" cellspacing="0">
				<?php
				if (sizeof($hosts)) {
					foreach ($hosts as $item) {
						form_alternate_row_color("host" . $item["id"], true);
						print "<td><strong>" . $item["name"] . "</strong>" . (($policy["policy_hosts"] == "1") ? __(" - No Access") : __(" - Accessible")) . "</td>
								<td align='right'><a href='" . htmlspecialchars("user_admin.php?action=perm_remove&type=host&id=" . $item["id"] . "&user_id=" . $_GET["id"]) . "'><img class='buttonSmall' src='images/delete_icon.gif' alt='" . __("Delete") . "' align='absmiddle'></a>&nbsp;</td>\n";
						form_end_row();
					}
				}else{
					print "<tr><td><em>" . __("No Devices") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='left'>
		<tr>
			<td width='40'>
				&nbsp;<input type="submit" value="<?php print __("Add");?>" name="add_host_y">
			</td>
			<td align='left' width='1'>
				<input class="ac_field" type="text" id="host" size="70" value="">
				<input type="hidden" id="perm_hosts" name="perm_hosts">
			</td>
		</tr>
	</table>
	<?php

	/* box: graph template permissions */
	html_start_box("<strong>" . __("Graph Permissions (By Graph Template)") . "</strong>", "100%", $colors["header"], "3", "center", "");

	$graph_templates = db_fetch_assoc("SELECT
		graph_templates.id,
		graph_templates.name
		from graph_templates
		LEFT JOIN user_auth_perms ON (graph_templates.id = user_auth_perms.item_id AND user_auth_perms.type = " . PERM_GRAPH_TEMPLATES . ")
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_templates.name");

	?>
	<tr bgcolor="#<?php print $colors['form_alternate1'];?>">
		<td style='width:100px;white-space:nowrap;'>
			<font class="textEditTitle" title="<?php print __("The default allow/deny graph policy for this user");?>"><?php print __("Default Policy");?></font>
		</td>
		<td align="left">
			<?php form_dropdown("policy_graph_templates",$graph_policy_array,"","",$policy["policy_graph_templates"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1" cellspacing="0">
				<?php
				if (sizeof($graph_templates)) {
					foreach ($graph_templates as $item) {
						form_alternate_row_color("templates" . $item["id"], true);
						print "<td><strong>" . $item["name"] . "</strong>" . (($policy["policy_graph_templates"] == "1") ? __(" - No Access") : __(" - Accessible")) . "</td>
								<td align='right'><a href='" . htmlspecialchars("user_admin.php?action=perm_remove&type=graph_template&id=" . $item["id"] . "&user_id=" . $_GET["id"]) . "'><img class='buttonSmall' src='images/delete_icon.gif' alt='" . __("Delete") . "' align='absmiddle'></a>&nbsp;</td>\n";
						form_end_row();
					}
				}else{
					print "<tr><td><em>" . __("No Graph Templates") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='left'>
		<tr>
			<td width='40'>
				&nbsp;<input type="submit" value="<?php print __("Add");?>" name="add_graph_template_y">
			</td>
			<td align='left' width='1'>
				<?php form_dropdown("perm_graph_templates",db_fetch_assoc("SELECT id, name FROM graph_templates WHERE id NOT IN (SELECT item_id FROM user_auth_perms WHERE user_auth_perms.type=4 AND user_auth_perms.user_id=".get_request_var("id",0).") ORDER BY name"),"name","id","","","");?>
			</td>
		</tr>
	</table>
	<?php

	/* box: tree permissions */
	html_start_box("<strong>" . __("Tree Permissions") . "</strong>", "100%", $colors["header"], "3", "center", "");

	$trees = db_fetch_assoc("SELECT
		graph_tree.id,
		graph_tree.name
		from graph_tree
		LEFT JOIN user_auth_perms ON (graph_tree.id = user_auth_perms.item_id AND user_auth_perms.type = " . PERM_TREES . ")
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_tree.name");

	?>
	<tr bgcolor="#<?php print $colors['form_alternate1'];?>">
		<td style='width:100px;white-space:nowrap;'>
			<font class="textEditTitle" title="<?php print __("The default allow/deny graph policy for this user");?>"><?php print __("Default Policy");?></font>
		</td>
		<td align="left">
			<?php form_dropdown("policy_trees",$graph_policy_array,"","",$policy["policy_trees"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1" cellspacing="0">
				<?php
				if (sizeof($trees)) {
					foreach ($trees as $item) {
						form_alternate_row_color("tree" . $item["id"], true);
						print "<td><strong>" . $item["name"] . "</strong>" . (($policy["policy_trees"] == "1") ? __(" - No Access") : __(" - Accessible")) . "</td>
							<td align='right'><a href='" . htmlspecialchars("user_admin.php?action=perm_remove&type=tree&id=" . $item["id"] . "&user_id=" . $_GET["id"]) . "'><img class='buttonSmall' src='images/delete_icon.gif' alt='Delete' align='absmiddle'></a>&nbsp;</td>\n";
						form_end_row();
					}
				}else{
					print "<tr><td><em>" . __("No Trees") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='left'>
		<tr>
			<td width='40'>
				&nbsp;<input type="submit" value="<?php print __("Add");?>" name="add_tree_y">
			</td>
			<td align='left' width='1'>
				<?php form_dropdown("perm_trees",db_fetch_assoc("SELECT id, name FROM graph_tree WHERE id NOT IN (SELECT item_id FROM user_auth_perms WHERE user_auth_perms.type=2 AND user_auth_perms.user_id=".get_request_var("id",0)." ) ORDER BY name"),"name","id","","","");?>
			</td>
		</tr>
	</table>
	<?php

	form_hidden_box("save_component_graph_perms","1","");
}

function user_realms_edit() {
	global $colors, $user_auth_realms;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	html_start_box("", "100%", $colors["header"], "0", "center", "");

	print "	<tr class='rowHeader'>
			<td class='textHeaderDark'><strong>" . __("Realm Permissions") . "</strong></td>
			<td class='textHeaderDark' width='1%' align='center'><input type='checkbox' style='margin: 0px;' name='all' title='" . __("Select All") . "' onClick='SelectAll(\"section\",this.checked)'></td>\n
		</tr>\n";

	?>

	<tr>
		<td colspan="2" width="100%">
			<table width="100%">
				<tr>
					<td valign="top" width="50%">
						<?php
						$i = 0;
						while (list($realm_id, $realm_name) = each($user_auth_realms)) {
							if (sizeof(db_fetch_assoc("SELECT realm_id FROM user_auth_realm WHERE user_id = " . get_request_var("id", 0) . " AND realm_id = " . $realm_id)) > 0) {
								$old_value = "on";
							}else{
								$old_value = "";
							}

							$column1 = floor((sizeof($user_auth_realms) / 2) + (sizeof($user_auth_realms) % 2));

							if ($i == $column1) {
								print "</td><td valign='top' width='50%'>";
							}

							form_checkbox("section" . $realm_id, $old_value, $realm_name, "", "", "", (!empty($_GET["id"]) ? 1 : 0)); print "<br>";

							$i++;
						}
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?php
	html_end_box();

	form_hidden_box("save_component_realm_perms","1","");
}

function graph_settings_edit() {
	global $settings_graphs, $tabs_graphs, $colors, $graph_views;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	html_start_box("<strong>" . __("Graph Settings") . "</strong>", "100%", $colors["header"], 0, "center", "");

	while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
		$header_items = array($tabs_graphs[$tab_short_name], "&nbsp;");
		print "<tr><td>";
		html_header($header_items, 1, true, $tab_short_name);

		$form_array = array();

		while (list($field_name, $field_array) = each($tab_fields)) {
			$form_array += array($field_name => $tab_fields[$field_name]);

			if ((isset($field_array["items"])) && (is_array($field_array["items"]))) {
				while (list($sub_field_name, $sub_field_array) = each($field_array["items"])) {
					if (graph_config_value_exists($sub_field_name, $_GET["id"])) {
						$form_array[$field_name]["items"][$sub_field_name]["form_id"] = 1;
					}

					$form_array[$field_name]["items"][$sub_field_name]["value"] =  db_fetch_cell("SELECT value FROM settings_graphs WHERE name = '" . $sub_field_name . "' AND user_id = " . get_request_var("id"));
				}
			}else{
				if (graph_config_value_exists($field_name, $_GET["id"])) {
					$form_array[$field_name]["form_id"] = 1;
				}

				$form_array[$field_name]["value"] = db_fetch_cell("select value from settings_graphs where name='$field_name' and user_id=" . $_GET["id"]);
			}
		}

		draw_edit_form(
			array(
				"config" => array(
					"no_form_tag" => true
					),
				"fields" => $form_array
				)
			);
		print "</table></td></tr>";		/* end of html_header */
	}

	html_end_box();

	form_hidden_box("save_component_graph_settings","1","");
}

/* --------------------------
    User Administration
   -------------------------- */

function user_edit() {
	global $colors, $fields_user_user_edit_host;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$user = db_fetch_row("SELECT * FROM user_auth WHERE id = " . get_request_var("id"));
		$header_label = __("[edit: ") . $user["username"] . "]";
	}else{
		$header_label = __("[new]");
	}

	api_plugin_hook_function('user_admin_edit', (isset($user) ? get_request_var("id") : 0));

	$user_tabs = array(
		"user_edit" => array("name" => __("General Settings"), "title" => __("General Settings are common settings for all users.")),
		"user_realms_edit" => array("name" => __("Realm Permissions"), "title" => __("Realm permissions control which sections of Cacti this user will have access to.")),
		"graph_perms_edit" => array("name" => __("Graph Permissions"), "title" => __("Graph policies will be evaluated in the order shown until a match is found.")),
		"graph_settings_edit" => array("name" => __("Graph Settings"), "title" => __("Graph settings control how graphs are displayed for this user.")));

	/* draw the categories tabs on the top of the page */
	/* set the default settings category */
	if (!isset($_GET["action"])) {
		/* there is no selected tab; select the first one */
		$current_tab = array_keys($user_tabs);
		$current_tab = $current_tab[0];
	}else{
		$current_tab = $_GET["action"];
	}

	print "<table width='100%' cellspacing='0' cellpadding='0' align='center'><tr>";
	print "<td><div class='tabs'>";

	if (sizeof($user_tabs)) {
	foreach (array_keys($user_tabs) as $tab_short_name) {
		print "<div title='" . $user_tabs[$tab_short_name]["title"] . "' class='tabDefault'><a " . (($tab_short_name == $current_tab) ? "class='tabSelected'" : "class='tabDefault'") . " href='" . htmlspecialchars("user_admin.php?action=shift&action=" . $tab_short_name . "&id=" . $_GET["id"]) . "'>" . $user_tabs[$tab_short_name]["name"] . "</a></div>";

		if (empty($_GET["id"])) break;
	}
	}

	print "</div></td></tr></table>\n";

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='user_edit'>\n";
	if (get_request_var("action") == "user_edit") {
		html_start_box("<strong>" . __("General Settings") . "</strong>", "100%", $colors["header"], 0, "center");
		$header_items = array(__("Field"), __("Value"));
		print "<tr><td>";
		html_header($header_items, 2, true, 'settings_general');

		draw_edit_form(array(
			"config" => array("form_name" => "chk"),
			"fields" => inject_form_variables($fields_user_user_edit_host, (isset($user) ? $user : array()))
		));
		print "</table></td></tr>";		/* end of html_header */
		html_end_box();
	}else{
#		print "<span style='display:none;'>";
#
#		html_start_box("", "100%", $colors["header"], "3", "center");
#		draw_edit_form(array(
#			"config" => array("form_name" => "chk"),
#			"fields" => inject_form_variables($fields_user_user_edit_host, (isset($user) ? $user : array()))
#		));
#		html_end_box();
#
#		print "</span>";

		if (get_request_var("action") == "graph_settings_edit") {
			graph_settings_edit();
		}elseif (get_request_var("action") == "user_realms_edit") {
			user_realms_edit();
		}elseif (get_request_var("action") == "graph_perms_edit") {
			graph_perms_edit();
		}else{
			if (!api_plugin_hook_function('user_admin_run_action', get_request_var_request("action"))) {
				user_realms_edit();
			}
		}
	}
	form_save_button_alt("return!user_admin.php");
}

function user() {
	global $colors, $auth_realms, $user_actions;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var_request("page"));
	/* ==================================================== */

	/* clean up search string */
	if (isset($_REQUEST["filter"])) {
		$_REQUEST["filter"] = sanitize_search_string(get_request_var("filter"));
	}

	/* clean up sort_column */
	if (isset($_REQUEST["sort_column"])) {
		$_REQUEST["sort_column"] = sanitize_search_string(get_request_var("sort_column"));
	}

	/* clean up sort_direction string */
	if (isset($_REQUEST["sort_direction"])) {
		$_REQUEST["sort_direction"] = sanitize_search_string(get_request_var("sort_direction"));
	}

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_user_admin_current_page");
		kill_session_var("sess_user_admin_filter");
		kill_session_var("sess_user_admin_sort_column");
		kill_session_var("sess_user_admin_sort_direction");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["sort_column"]);
		unset($_REQUEST["sort_direction"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_user_admin_current_page", "1");
	load_current_session_value("filter", "sess_user_admin_filter", "");
	load_current_session_value("sort_column", "sess_user_admin_sort_column", "username");
	load_current_session_value("sort_direction", "sess_user_admin_sort_direction", "ASC");

	html_start_box("<strong>" . __('User Management') . "</strong>", "100%", $colors["header"], "3", "center", "user_admin.php?action=user_edit", true);
	?>
	<tr class='rowAlternate2'>
		<td>
			<form name="form_user_admin" action="user_admin.php">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						<?php print __("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;<input type="submit" Value="<?php echo __('Go');?>" name="go" align="middle">
						<input type="submit" Value="<?php echo __('Clear');?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
	<?php
	html_end_box(false);

	/* form the 'where' clause for our main sql query */
	if (strlen($_REQUEST["filter"])) {
		$sql_where = "WHERE (user_auth.username LIKE '%" . get_request_var_request("filter") . "%' OR user_auth.full_name LIKE '%" . get_request_var_request("filter") . "%')";
	}else{
		$sql_where = "";
	}

	html_start_box("", "100%", $colors["header"], "0", "center", "");

	$total_rows = db_fetch_cell("SELECT
		COUNT(user_auth.id)
		FROM user_auth
		$sql_where");

	$user_list = db_fetch_assoc("SELECT
		id,
		user_auth.username,
		full_name,
		realm,
		enabled,
		policy_graphs,
		time,
		max(time) as dtime
		FROM user_auth
		LEFT JOIN user_log ON (user_auth.id = user_log.user_id)
		$sql_where
		GROUP BY id
		ORDER BY " . get_request_var_request("sort_column") . " " . get_request_var_request("sort_direction") .
		" LIMIT " . (read_config_option("num_rows_device") * (get_request_var_request("page") - 1)) . "," . read_config_option("num_rows_device"));

	/* generate page list navigation */
	$nav = html_create_nav($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, 7, "user_admin.php");

	print $nav;
	html_end_box(false);

	$display_text = array(
		"username" => array(__("User Name"), "ASC"),
		"full_name" => array(__("Full Name"), "ASC"),
		"enabled" => array(__("Enabled"), "ASC"),
		"realm" => array(__("Realm"), "ASC"),
		"policy_graphs" => array(__("Default Graph Policy"), "ASC"),
		"dtime" => array(__("Last Login"), "DESC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));

	if (sizeof($user_list) > 0) {
		foreach ($user_list as $user) {
			if (empty($user["dtime"]) || ($user["dtime"] == "12/31/1969")) {
				$last_login = "N/A";
			}else{
				$last_login = strftime("%A, %B %d, %Y %H:%M:%S ", strtotime($user["dtime"]));;
			}
			if ($user["enabled"] == "on") {
				$enabled = __("Yes");
			}else{
				$enabled = __("No");
			}

			form_alternate_row_color('line' . $user["id"], true);
			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars("user_admin.php?action=user_edit&id=" . $user["id"]) . "'>" .
			(strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["username"]) : $user["username"]) . "</a>"
			, $user["id"]);
			form_selectable_cell((strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["full_name"]) : $user["full_name"]), $user["id"]);
			form_selectable_cell($enabled, $user["id"]);
			form_selectable_cell($auth_realms[$user["realm"]], $user["id"]);
			if ($user["policy_graphs"] == "1") {
				form_selectable_cell( __("ALLOW"), $user["id"]);
			}else{
				form_selectable_cell( __("DENY"), $user["id"]);
			}
			form_selectable_cell($last_login, $user["id"]);
			form_checkbox_cell($user["username"], $user["id"]);
			form_end_row();
		}

		form_end_table();

		print $nav;
	}else{
		print "<tr><td><em>" . __("No Users") ."</em></td></tr>";
	}

	print "</table>\n";	# end table of html_header_sort_checkbox

	draw_actions_dropdown($user_actions);
	print "</form>\n";	# end form of html_header_sort_checkbox
}
?>
