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

define("MAX_DISPLAY_PAGES", 21);

$user_actions = array(
	1 => "Delete",
	2 => "Copy",
	3 => "Enable",
	4 => "Disable",
	5 => "Batch Copy"
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

	case 'user_realms_edit':
		include_once("include/top_header.php");
		user_edit();
		include_once("include/bottom_footer.php");
		break;

	case 'graph_settings_edit':
		include_once("include/top_header.php");
		user_edit();
		include_once("include/bottom_footer.php");
		break;

	case 'graph_perms_edit':
		include_once("include/top_header.php");
		user_edit();
		include_once("include/bottom_footer.php");
		break;

	case 'user_edit':
		include_once("include/top_header.php");
		user_edit();
		include_once("include/bottom_footer.php");
		break;

	default:
		include_once("include/top_header.php");
		user();
		include_once("include/bottom_footer.php");
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

	include_once("./include/top_header.php");

	html_start_box("<strong>" . $user_actions[get_request_var_post("drp_action")] . "</strong>", "60%", $colors["header_panel"], "3", "center", "");

	print "<form action='user_admin.php' method='post'>\n";

	if ((get_request_var_post("drp_action") == "1") && (sizeof($user_array))) { /* delete */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to delete the following users?</p>
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
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Would you like to copy this user?<br><br>
				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Template Username: <i>" . db_fetch_cell("SELECT username FROM user_auth WHERE id=" . $user_id) . "</i>
				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
				New Username: ";
		print form_text_box("new_username", "", "", 25);
		print "				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					New Full Name: ";
		print form_text_box("new_fullname", "", "", 35);
		print "				</td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					New Realm: \n";
		print form_dropdown("new_realm", $auth_realms, "", "", $user_realm, "", 0);
		print "				</td>

			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "3") && (sizeof($user_array))) { /* enable */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to enable the following users?</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "4") && (sizeof($user_array))) { /* disable */
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Are you sure you want to disable the following users?</p>
					<p>$user_list</p>
				</td>
			</tr>\n";
	}

	if ((get_request_var_post("drp_action") == "5") && (sizeof($user_array))) { /* batch copy */
		$usernames = db_fetch_assoc("SELECT id,username FROM user_auth WHERE realm = 0 ORDER BY username");
		print "
			<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>Are you sure you want to overwrite the selected users with the selected template users settings and permissions?  Original user Full Name, Password, Realm and Enable status will be retained, all other fields will be overwritten from template user.<br><br></td>
			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					Template User: \n";
		print form_dropdown("template_user", $usernames, "username", "id", "", "", 0);
		print "		</td>

			</tr><tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>Users to update:
					$user_list</p>
				</td>
			</tr>\n";
	}

	if (sizeof($user_array) == 0) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>You must select at least one user.</span></td></tr>\n";
		$save_html = "<a href='user_admin.php'><img src='images/button_cancel.gif' alt='Cancel' align='absmiddle' border='0'></a>";

	}else{
		$save_html = "<a href='user_admin.php'><img src='images/button_no.gif' alt='Cancel' align='absmiddle' border='0'></a> <input type='image' src='images/button_yes.gif' alt='Save' align='absmiddle'>";
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

	include_once("./include/bottom_footer.php");

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
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_graphs") . ",1)");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_tree_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_trees") . ",2)");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_host_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_hosts") . ",3)");
			$add_button_clicked = true;
		}elseif (isset($_POST["add_graph_template_y"])) {
			db_execute("REPLACE INTO user_auth_perms (user_id,item_id,type) VALUES (" . get_request_var_post("id") . "," . get_request_var_post("perm_graph_templates") . ",4)");
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
			}
		}
	}

	/* redirect to the appropriate page */
	if (is_error_message()) {
		header("Location: user_admin.php?action=user_edit&id=" . (empty($user_id) ? $_POST["id"] : $user_id));
	}else{
		header("Location: user_admin.php");
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
		db_execute("DELETE FROM user_auth_perms WHERE type = 1 AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "tree") {
		db_execute("DELETE FROM user_auth_perms WHERE type = 2 AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "host") {
		db_execute("DELETE FROM user_auth_perms WHERE type = 3 AND user_id = " . get_request_var("user_id") . " AND item_id = " . get_request_var("id"));
	}elseif (get_request_var("type") == "graph_template") {
		db_execute("DELETE FROM user_auth_perms WHERE type = 4 AND user_id=" . get_request_var("user_id") . " and item_id = " . get_request_var("id"));
	}

	header("Location: user_admin.php?action=graph_perms_edit&id=" . get_request_var("user_id"));
}

function graph_perms_edit() {
	global $colors;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	$graph_policy_array = array(
		1 => "Allow",
		2 => "Deny");

	if (!empty($_GET["id"])) {
		$policy = db_fetch_row("SELECT policy_graphs,policy_trees,policy_hosts,policy_graph_templates FROM user_auth WHERE id = " . get_request_var("id"));

		$header_label = "[edit: " . db_fetch_cell("SELECT username FROM user_auth WHERE id = " . get_request_var("id")) . "]";
	}

	?>
	<table width='100%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'>Graph policies will be evaluated in the order shown until a match is found.</span>
			</td>
		</tr>
	</table>
	<?php

	/* box: graph permissions */
	html_start_box("<strong>Graph Permissions (By Graph)</strong>", "100%", $colors["header"], "3", "center", "");

	$graphs = db_fetch_assoc("SELECT
		graph_templates_graph.local_graph_id,
		graph_templates_graph.title_cache
		FROM graph_templates_graph
		LEFT JOIN user_auth_perms ON (graph_templates_graph.local_graph_id = user_auth_perms.item_id AND user_auth_perms.type = 1)
		WHERE graph_templates_graph.local_graph_id > 0
		AND user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_templates_graph.title_cache");

	?>
	<form method="post" action="user_admin.php">

	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle">Default Policy</font><br>
			The default allow/deny graph policy for this user.
		</td>
		<td align="right">
			<?php form_dropdown("policy_graphs",$graph_policy_array,"","",$policy["policy_graphs"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($graphs) > 0) {
				foreach ($graphs as $item) {
					$i++;
					print "	<tr>
							<td><span style='font-weight: bold; color: " . (($policy["policy_graphs"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["title_cache"] . "</td>
							<td align='right'><a href='user_admin.php?action=perm_remove&type=graph&id=" . $item["local_graph_id"] . "&user_id=" . $_GET["id"] . "'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
						</tr>\n";
				}
				}else{ print "<tr><td><em>No Graphs</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='center' width='100%'>
		<tr>
			<td nowrap>Add Graph:&nbsp;
				<?php form_dropdown("perm_graphs",db_fetch_assoc("SELECT local_graph_id,title_cache FROM graph_templates_graph WHERE local_graph_id > 0 ORDER BY title_cache"),"title_cache","local_graph_id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="images/button_add.gif" alt="Add" name="add_graph" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: device permissions */
	html_start_box("<strong>Graph Permissions (By Device)</strong>", "100%", $colors["header"], "3", "center", "");

	$hosts = db_fetch_assoc("SELECT
		host.id,
		CONCAT('',host.description,' (',host.hostname,')') as name
		FROM host
		LEFT JOIN user_auth_perms ON (host.id = user_auth_perms.item_id AND user_auth_perms.type = 3)
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY host.description,host.hostname");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle">Default Policy</font><br>
			The default allow/deny graph policy for this user.
		</td>
		<td align="right">
			<?php form_dropdown("policy_hosts",$graph_policy_array,"","",$policy["policy_hosts"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($hosts) > 0) {
					foreach ($hosts as $item) {
						$i++;
						print "	<tr>
							<td><span style='font-weight: bold; color: " . (($policy["policy_hosts"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["name"] . "</td>
							<td align='right'><a href='user_admin.php?action=perm_remove&type=host&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
						</tr>\n";
					}
				}else{
					print "<tr><td><em>No Devices</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='100%'>
		<tr>
			<td nowrap>Add Host:&nbsp;
				<?php form_dropdown("perm_hosts",db_fetch_assoc("SELECT id,CONCAT('',description,' (',hostname,')') AS name FROM host ORDER BY description,hostname"),"name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="images/button_add.gif" alt="Add" name="add_host" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: graph template permissions */
	html_start_box("<strong>Graph Permissions (By Graph Template)</strong>", "100%", $colors["header"], "3", "center", "");

	$graph_templates = db_fetch_assoc("SELECT
		graph_templates.id,
		graph_templates.name
		from graph_templates
		LEFT JOIN user_auth_perms ON (graph_templates.id = user_auth_perms.item_id AND user_auth_perms.type = 4)
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_templates.name");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle">Default Policy</font><br>
			The default allow/deny graph policy for this user.
		</td>
		<td align="right">
			<?php form_dropdown("policy_graph_templates",$graph_policy_array,"","",$policy["policy_graph_templates"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($graph_templates) > 0) {
				foreach ($graph_templates as $item) {
					$i++;
					print "	<tr>
							<td><span style='font-weight: bold; color: " . (($policy["policy_graph_templates"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["name"] . "</td>
							<td align='right'><a href='user_admin.php?action=perm_remove&type=graph_template&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
						</tr>\n";
				}
				}else{ print "<tr><td><em>No Graph Templates</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='100%'>
		<tr>
			<td nowrap>Add Graph Template:&nbsp;
				<?php form_dropdown("perm_graph_templates",db_fetch_assoc("SELECT id,name FROM graph_templates ORDER BY name"),"name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="images/button_add.gif" alt="Add" name="add_graph_template" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: tree permissions */
	html_start_box("<strong>Tree Permissions</strong>", "100%", $colors["header"], "3", "center", "");

	$trees = db_fetch_assoc("SELECT
		graph_tree.id,
		graph_tree.name
		from graph_tree
		LEFT JOIN user_auth_perms ON (graph_tree.id = user_auth_perms.item_id AND user_auth_perms.type = 2)
		WHERE user_auth_perms.user_id = " . get_request_var("id", 0) . "
		ORDER BY graph_tree.name");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle">Default Policy</font><br>
			The default allow/deny graph policy for this user.
		</td>
		<td align="right">
			<?php form_dropdown("policy_trees",$graph_policy_array,"","",$policy["policy_trees"],"",""); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($trees) > 0) {
				foreach ($trees as $item) {
					$i++;
					print "	<tr>
							<td><span style='font-weight: bold; color: " . (($policy["policy_trees"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["name"] . "</td>
							<td align='right'><a href='user_admin.php?action=perm_remove&type=tree&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
						</tr>\n";
				}
				}else{ print "<tr><td><em>No Trees</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='100%'>
		<tr>
			<td nowrap>Add Tree:&nbsp;
				<?php form_dropdown("perm_trees",db_fetch_assoc("SELECT id,name FROM graph_tree ORDER BY name"),"name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="images/button_add.gif" alt="Add" name="add_tree" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>

	<?php
	form_hidden_box("save_component_graph_perms","1","");
}

function user_realms_edit() {
	global $colors, $user_auth_realms;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	?>
	<table width='100%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'>Realm permissions control which sections of Cacti this user will have access to.</span>
			</td>
		</tr>
	</table>
	<?php

	html_start_box("", "100%", $colors["header"], "3", "center", "");

	print "	<tr bgcolor='#" . $colors["header"] . "'>
			<td class='textHeaderDark'><strong>Realm Permissions</strong></td>
			<td width='1%' align='center' bgcolor='#819bc0' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='all' title='Select All' onClick='SelectAll(\"section\",this.checked)'></td>\n
		</tr>\n";

	?>

	<tr>
		<td colspan="2" width="100%">
			<table width="100%">
				<tr>
					<td align="top" width="50%">
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
	global $settings_graphs, $tabs_graphs, $colors, $graph_views, $graph_tree_views;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	?>
	<table width='100%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'>Graph settings control how graphs are displayed for this user.</span>
			</td>
		</tr>
	</table>
	<?php

	html_start_box("<strong>Graph Settings</strong>", "100%", $colors["header"], "3", "center", "");

	while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
		?>
		<tr bgcolor='<?php print $colors["header_panel"];?>'>
			<td colspan='2' class='textSubHeaderDark' style='padding: 3px;'>
				<?php print $tabs_graphs[$tab_short_name];?>
			</td>
		</tr>
		<?php

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
		$header_label = "[edit: " . $user["username"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>User Management</strong> $header_label", "100%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_user_user_edit_host, (isset($user) ? $user : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		/* draw user admin nav tabs */
		?>
		<table class='tabs' width='100%' cellspacing='0' cellpadding='3' align='center'>
			<tr>
				<td width='1'></td>
				<td <?php print (((get_request_var("action") == "user_realms_edit") || (get_request_var("action") == "user_edit")) ? "bgcolor='silver'" : "bgcolor='#DFDFDF'");?> nowrap='nowrap' width='150' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=user_realms_edit&id=<?php print $_GET["id"];?>'>Realm Permissions</a></span>
				</td>
				<td width='1'></td>
				<td <?php print ((get_request_var("action") == "graph_perms_edit") ? "bgcolor='silver'" : "bgcolor='#DFDFDF'");?> nowrap='nowrap' width='150' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=graph_perms_edit&id=<?php print $_GET["id"];?>'>Graph Permissions</a></span>
				</td>
				<td width='1'></td>
				<td <?php print ((get_request_var("action") == "graph_settings_edit") ? "bgcolor='silver'" : "bgcolor='#DFDFDF'");?> nowrap='nowrap' width='130' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=graph_settings_edit&id=<?php print $_GET["id"];?>'>Graph Settings</a></span>
				</td>
				<td></td>
			</tr>
		</table>
		<?php
	}

	if (get_request_var("action") == "graph_settings_edit") {
		graph_settings_edit();
	}elseif (get_request_var("action") == "user_realms_edit") {
		user_realms_edit();
	}elseif (get_request_var("action") == "graph_perms_edit") {
		graph_perms_edit();
	}else{
		user_realms_edit();
	}

	form_save_button("user_admin.php");
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

	html_start_box("<strong>User Management</strong>", "100%", $colors["header"], "3", "center", "user_admin.php?action=user_edit");

	include("./include/html/inc_user_admin_filter_table.php");

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (strlen($_REQUEST["filter"])) {
		$sql_where = "WHERE (user_auth.username LIKE '%" . get_request_var_request("filter") . "%' OR user_auth.full_name LIKE '%" . get_request_var_request("filter") . "%')";
	}else{
		$sql_where = "";
	}

	html_start_box("", "100%", $colors["header"], "3", "center", "");

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

	/* generate page list */
	$url_page_select = get_page_list(get_request_var_request("page"), MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "user_admin.php?filter=" . get_request_var_request("filter"));

	$nav = "<tr bgcolor='#" . $colors["header"] . "'>
		<td colspan='7'>
			<table width='100%' cellspacing='0' cellpadding='0' border='0'>
				<tr>
					<td align='left' class='textHeaderDark'>
						<strong>&lt;&lt; "; if (get_request_var_request("page") > 1) { $nav .= "<a class='linkOverDark' href='user_admin.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page") - 1) . "'>"; } $nav .= "Previous"; if (get_request_var_request("page") > 1) { $nav .= "</a>"; } $nav .= "</strong>
					</td>\n
					<td align='center' class='textHeaderDark'>
						Showing Rows " . ((read_config_option("num_rows_device") * (get_request_var_request("page") - 1)) + 1) . " to " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device") * get_request_var_request("page")))) ? $total_rows : (read_config_option("num_rows_device") * get_request_var_request("page"))) . " of $total_rows [$url_page_select]
					</td>\n
					<td align='right' class='textHeaderDark'>
						<strong>"; if ((get_request_var_request("page") * read_config_option("num_rows_device")) < $total_rows) { $nav .= "<a class='linkOverDark' href='user_admin.php?filter=" . get_request_var_request("filter") . "&page=" . (get_request_var_request("page") + 1) . "'>"; } $nav .= "Next"; if ((get_request_var_request("page") * read_config_option("num_rows_device")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
					</td>\n
				</tr>
			</table>
		</td>
		</tr>\n";

	print $nav;

	$display_text = array(
		"username" => array("User Name", "ASC"),
		"full_name" => array("Full Name", "ASC"),
		"enabled" => array("Enabled", "ASC"),
		"realm" => array("Realm", "ASC"),
		"policy_graphs" => array("Default Graph Policy", "ASC"),
		"dtime" => array("Last Login", "DESC"));

	html_header_sort_checkbox($display_text, get_request_var_request("sort_column"), get_request_var_request("sort_direction"));


	$i = 0;
	if (sizeof($user_list) > 0) {
		foreach ($user_list as $user) {
			if (empty($user["dtime"]) || ($user["dtime"] == "12/31/1969")) {
				$last_login = "N/A";
			}else{
				$last_login = strftime("%A, %B %d, %Y %H:%M:%S ", strtotime($user["dtime"]));;
			}
			if ($user["enabled"] == "on") {
				$enabled = "Yes";
			}else{
				$enabled = "No";
			}

			form_alternate_row_color($colors["alternate"], $colors["light"], $i, 'line' . $user["id"]); $i++;
			form_selectable_cell("<a class='linkEditMain' href='user_admin.php?action=user_edit&id=" . $user["id"] . "'>" .
			(strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["username"]) : $user["username"])
			, $user["id"]);
			form_selectable_cell((strlen(get_request_var_request("filter")) ? eregi_replace("(" . preg_quote(get_request_var_request("filter")) . ")", "<span style='background-color: #F8D93D;'>\\1</span>",  $user["full_name"]) : $user["full_name"]), $user["id"]);
			form_selectable_cell($enabled, $user["id"]);
			form_selectable_cell($auth_realms[$user["realm"]], $user["id"]);
			if ($user["policy_graphs"] == "1") {
				form_selectable_cell("ALLOW", $user["id"]);
			}else{
				form_selectable_cell("DENY", $user["id"]);
			}
			form_selectable_cell($last_login, $user["id"]);
			form_checkbox_cell($user["username"], $user["id"]);
			form_end_row();
		}

		print $nav;
	}else{
		print "<tr><td><em>No Users</em></td></tr>";
	}
	html_end_box(false);

	draw_actions_dropdown($user_actions);

}
?>
