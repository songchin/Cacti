<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/user/user_action.php");

$themes["default"] = _("System Default (Global Setting)");
require_once(CACTI_BASE_PATH . "/include/user/user_form.php");


/* Detect deep linking the remove it */
if (! isset($_SERVER["HTTP_REFERER"]) && ((strlen($_SERVER["QUERY_STRING"]) > 0) || (sizeof($_POST) > 0))) {
	/* invalid no referer, but we have a query string or a form post, this is not normal */
	header("Location: logout.php");
}

$user_actions = array(
	1 => _("Delete"),
	2 => _("Copy"),
	3 => _("Enable"),
	4 => _("Disable"),
	5 => _("Password Expiration")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		user_actions();

		break;
	case 'perm_remove':
		perm_remove();

		break;
	case 'user_realms_edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		user_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'graph_settings_edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		user_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'graph_perms_edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		user_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'user_edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		user_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		user();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    form_save function
   -------------------------- */

function form_save() {
	global $settings_graphs;

	/* graph permissions */
	if ((isset($_POST["save_component_graph_perms"])) && (!is_error_message())) {
		$add_button_clicked = false;

		if (isset($_POST["add_graph_y"])) {
			api_user_graph_perms_add("graph",$_POST["id"],$_POST["perm_graphs"]);
			$add_button_clicked = true;
		}elseif (isset($_POST["add_tree_y"])) {
			api_user_graph_perms_add("tree",$_POST["id"],$_POST["perm_trees"]);
			$add_button_clicked = true;
		}elseif (isset($_POST["add_host_y"])) {
			api_user_graph_perms_add("host",$_POST["id"],$_POST["perm_hosts"]);
			$add_button_clicked = true;
		}elseif (isset($_POST["add_graph_template_y"])) {
			api_user_graph_perms_add("graph_template",$_POST["id"],$_POST["perm_graph_templates"]);
			$add_button_clicked = true;
		}

		if ($add_button_clicked == true) {
			header("Location: user_admin.php?action=graph_perms_edit&id=" . $_POST["id"]);
			exit;
		}
	}

	/* user management save */
	if (isset($_POST["save_component_user"])) {

		/* check to make sure the passwords match; if not error */
		if ($_POST["password"] != $_POST["password_confirm"]) {
			raise_message(4);
		}

		/* check for duplicate username */
		$user = api_user_info( array( "username" => $_POST["username"] ) );
		if (sizeof($user)) {
			if (!empty($_POST["id"])) {
				if (($user["id"] != $_POST["id"]) && ($user["realm"] == $_POST["realm"])) {
					raise_message(12);
				}
			}else{
				raise_message(12);
			}
		}

		/* password processing */
		if ((empty($_POST["password"])) && (empty($_POST["password_confirm"]))) {
			$user = api_user_info( array( "id" => $_POST["id"] ) );
			if (sizeof($user)) {
				$password = $user["password"];
			}else{
				$password = "";
			}
		}else{
			$password = md5($_POST["password"]);
		}


		form_input_validate($_POST["password"], "password", "" . preg_quote($_POST["password_confirm"]) . "", true, 4);
		form_input_validate($_POST["password_confirm"], "password_confirm", "" . preg_quote($_POST["password"]) . "", true, 4);

		$save["id"] = array("type" => DB_TYPE_NUMBER, "value" => $_POST["id"]);
		$save["username"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["username"], "username", "^[A-Za-z_0-9\.]+$", false, 3));
		$save["full_name"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["full_name"], "full_name", "", true, 3));
		$save["password"] = array("type" => DB_TYPE_STRING, "value" => $password);
		$save["email_address_primary"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["email_address_primary"], "email_address_primary", "", true, 3));
		$save["email_address_secondary"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["email_address_secondary"], "email_address_secondary", "", true, 3));
		$save["must_change_password"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["must_change_password"]) ? $_POST["must_change_password"] : ""), "must_change_password", "", true, 3));
		$save["show_tree"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["show_tree"]) ? $_POST["show_tree"] : ""), "show_tree", "", true, 3));
		$save["show_list"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["show_list"]) ? $_POST["show_list"] : ""), "show_list", "", true, 3));
		$save["show_preview"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["show_preview"]) ? $_POST["show_preview"] : ""), "show_preview", "", true, 3));
		$save["graph_settings"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["graph_settings"]) ? $_POST["graph_settings"] : ""), "graph_settings", "", true, 3));
		$save["login_opts"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["login_opts"], "login_opts", "", true, 3));
		$save["enabled"] = array("type" => DB_TYPE_NUMBER, "value" => form_input_validate($_POST["enabled"], "enabled", "", true, 3));
		$save["password_expire_length"] = array("type" => DB_TYPE_NUMBER, "value" => form_input_validate($_POST["password_expire_length"], "password_expire_length", "", true, 3));
		$save["current_theme"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate($_POST["current_theme"], "current_theme", "", true, 3));
		$save["policy_graphs"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["policy_graphs"]) ? $_POST["policy_graphs"] : $_POST["_policy_graphs"]), "policy_graphs", "", true, 3));
		$save["policy_trees"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["policy_trees"]) ? $_POST["policy_trees"] : $_POST["_policy_trees"]), "policy_trees", "", true, 3));
		$save["policy_hosts"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["policy_hosts"]) ? $_POST["policy_hosts"] : $_POST["_policy_hosts"]), "policy_hosts", "", true, 3));
		$save["policy_graph_templates"] = array("type" => DB_TYPE_STRING, "value" => form_input_validate((isset($_POST["policy_graph_templates"]) ? $_POST["policy_graph_templates"] : $_POST["_policy_graph_templates"]), "policy_graph_templates", "", true, 3));

		/* New user, update created */
		if (empty($_POST["id"])) {
			$save["created"] = array("type" => DB_TYPE_NUMBER, "value" => "now()");
		}

		if (!is_error_message()) {
			if (api_user_save($save) != 0) {
				/* user saved */
				raise_message(1);
			}else{
				/* error saving */
				raise_message(2);
			}
			if ($_SESSION["sess_user_id"] == $_POST["id"]) {
				/* reset local settings cache so the user sees the new settings */
				kill_session_var("sess_current_theme");
			}

			/* realms perms */
			if (isset($_POST["save_component_realm_perms"])) {
				$i = 0;
				$realm_perms_list = array();
				while (list($var, $val) = each($_POST)) {
					if (substr($var, 0, 7) == "section") {
						$realm_perms_list[$i] = substr($var, 7);
						$i++;
					}
				}
				api_user_realms_save($user_id,$realm_perms_list);

			/* graph settings */
			}elseif (isset($_POST["save_component_graph_settings"])) {
				if (api_user_graph_setting_save($_POST["id"],$_POST) == 1) {
					raise_message(2);
				}

				if ($_SESSION["sess_user_id"] == $_POST["id"]) {
					/* reset local settings cache so the user sees the new settings */
					kill_session_var("sess_graph_config_array");
				}
			/* graph perms - allow/deny */
			}elseif (isset($_POST["save_component_graph_perms"])) {
				$user = array();
				$user["policy_graphs"] = array("type" => DB_TYPE_STRING, "value" => $_POST["policy_graphs"]);
				$user["policy_tress"] = array("type" => DB_TYPE_STRING, "value" => $_POST["policy_trees"]);
				$user["policy_hosts"] = array("type" => DB_TYPE_STRING, "value" => $_POST["policy_hosts"]);
				$user["policy_graph_templates"] = array("type" => DB_TYPE_STRING, "value" => $_POST["policy_graph_templates"]);
				$user["id"] = array("type" => DB_TYPE_NUMBER, "value" => $_POST["id"]);
				if (api_user_save($user) == 0) {
					raise_message(2);
				}
			}
		}
	}

	/* redirect page */
	header("Location: user_admin.php?action=" . (isset($_POST["last_action"]) ? $_POST["last_action"] : "user_edit") . "&id=" . (empty($user_id) ? $_POST["id"] : $user_id));

}

/* --------------------------
    perm_remove function
   -------------------------- */

function perm_remove() {

	api_user_graph_perms_remove($_GET["type"],$_GET["user_id"],$_GET["id"]);
	header("Location: user_admin.php?action=graph_perms_edit&id=" . $_GET["user_id"]);
}


/* ---------------------------------
    graph_perms_edit function
   --------------------------------- */

function graph_perms_edit() {
	global $colors;

	$graph_policy_array = array(
		1 => "Allow",
		2 => "Deny");

	if (!empty($_GET["id"])) {
		$policy = api_user_info( array( "id" => $_GET["id"] ) );
		$header_label = _("[edit: ") . $policy["username"] . "]";
	}

	?>
	<table width='98%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'><?php echo _("Graph policies will be evaluated in the order shown until a match is found."); ?></span>
			</td>
		</tr>
	</table>
	<?php

	/* box: graph permissions */
	html_start_box("<strong>"._("Graph Permissions (By Graph)")."</strong>", "98%", $colors["header_background"], "3", "center", "");

	$graphs = db_fetch_assoc("select
		graph.id,
		graph.title_cache
		from graph
		left join user_auth_perms on (graph.id=user_auth_perms.item_id and user_auth_perms.type=1)
		where user_auth_perms.user_id = " . $_GET["id"] . "
		order by graph.title_cache");

	?>
	<form method="post" action="user_admin.php">

	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Default Policy"); ?></font><br>
			<?php echo _("The default allow/deny graph policy for this user."); ?>
		</td>
		<td align="right">
			<?php form_dropdown("policy_graphs",$graph_policy_array,"","",$policy["policy_graphs"],"",""); ?>
		</td>
	</tr>
	<tr bgcolor="<?php print $colors["form_alternate2"];?>">
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($graphs) > 0) {
					foreach ($graphs as $item) {
						$i++;
						print "	<tr>
								<td><span style='font-weight: bold; color: " . (($policy["policy_graphs"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["title_cache"] . "</td>
								<td align='right'><a href='user_admin.php?action=perm_remove&type=graph&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='" . html_get_theme_images_path("delete_icon.gif") . "' width='10' height='10' border='0' alt='"._("Delete")."'></a>&nbsp;</td>
							</tr>\n";
					}
				}else{
					print "<tr><td><em>" . _("No Graphs") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
	<?php

	html_end_box(false);

	?>
	<table align='center' width='98%'>
		<tr>
			<td nowrap><?php echo _("Add Graph");?>:&nbsp;
				<?php form_dropdown("perm_graphs",db_fetch_assoc("select id,title_cache from graph order by title_cache"),"title_cache","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _("Add"); ?>" name="add_graph" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: host permissions */
	html_start_box("<strong>"._("Graph Permissions (By Host)")."</strong>", "98%", $colors["header_background"], "3", "center", "");

	$hosts = db_fetch_assoc("select
		host.id,
		CONCAT_WS('',host.description,' (',host.hostname,')') as name
		from host
		left join user_auth_perms on (host.id=user_auth_perms.item_id and user_auth_perms.type=3)
		where user_auth_perms.user_id = " . $_GET["id"] . "
		order by host.description,host.hostname");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Default Policy"); ?></font><br>
			<?php echo _("The default allow/deny graph policy for this user."); ?>
		</td>
		<td align="right">
			<?php form_dropdown("policy_hosts",$graph_policy_array,"","",$policy["policy_hosts"],"",""); ?>
		</td>
	</tr>
	<tr bgcolor="<?php print $colors["form_alternate2"];?>">
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($hosts) > 0) {
					foreach ($hosts as $item) {
						$i++;
						print "	<tr>
								<td><span style='font-weight: bold; color: " . (($policy["policy_hosts"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["name"] . "</td>
								<td align='right'><a href='user_admin.php?action=perm_remove&type=host&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='" . html_get_theme_images_path("delete_icon.gif") . "' width='10' height='10' border='0' alt='"._("Delete")."'></a>&nbsp;</td>
							</tr>\n";
					}
				}else{
					print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>"._("No Hosts")."</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='98%'>
		<tr>
			<td nowrap><?php echo _("Add Host");?>:&nbsp;
				<?php form_dropdown("perm_hosts",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _("Add"); ?>" name="add_host" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: graph template permissions */
	html_start_box("<strong>" . _("Graph Permissions (By Graph Template)") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

	$graph_templates = db_fetch_assoc("select
		graph_template.id,
		graph_template.template_name
		from graph_template
		left join user_auth_perms on (graph_template.id=user_auth_perms.item_id and user_auth_perms.type=4)
		where user_auth_perms.user_id = " . $_GET["id"] . "
		order by graph_template.template_name");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Default Policy"); ?></font><br>
			<?php echo _("The default allow/deny graph policy for this user."); ?>
		</td>
		<td align="right">
			<?php form_dropdown("policy_graph_templates",$graph_policy_array,"","",$policy["policy_graph_templates"],"",""); ?>
		</td>
	</tr>
	<tr bgcolor="<?php print $colors["form_alternate2"];?>">
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($graph_templates) > 0) {
					foreach ($graph_templates as $item) {
						$i++;
						print "	<tr>
								<td><span style='font-weight: bold; color: " . (($policy["policy_graph_templates"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["template_name"] . "</td>
								<td align='right'><a href='user_admin.php?action=perm_remove&type=graph_template&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='" . html_get_theme_images_path("delete_icon.gif") . "' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
							</tr>\n";
					}
				}else{
					print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No Graph Templates") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='98%'>
		<tr>
			<td nowrap><?php echo _("Add Graph Template:"); ?>&nbsp;
				<?php form_dropdown("perm_graph_templates",db_fetch_assoc("select id,template_name from graph_template order by template_name"),"template_name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add'); ?>" name="add_graph_template" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>
	<?php

	/* box: tree permissions */
	html_start_box("<strong>"._("Tree Permissions")."</strong>", "98%", $colors["header_background"], "3", "center", "");

	$trees = db_fetch_assoc("select
		graph_tree.id,
		graph_tree.name
		from graph_tree
		left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2)
		where user_auth_perms.user_id = " . $_GET["id"] . "
		order by graph_tree.name");

	?>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Default Policy");?></font><br>
			<?php echo _("The default allow/deny graph policy for this user.");?>
		</td>
		<td align="right">
			<?php form_dropdown("policy_trees",$graph_policy_array,"","",$policy["policy_trees"],"",""); ?>
		</td>
	</tr>
	<tr bgcolor="<?php print $colors["form_alternate2"];?>">
		<td colspan="2">
			<table width="100%" cellpadding="1">
				<?php
				$i = 0;
				if (sizeof($trees) > 0) {
					foreach ($trees as $item) {
						$i++;
						print "	<tr>
								<td><span style='font-weight: bold; color: " . (($policy["policy_trees"] == "1") ? "red" : "blue") . ";'>$i)</span> " . $item["name"] . "</td>
								<td align='right'><a href='user_admin.php?action=perm_remove&type=tree&id=" . $item["id"] . "&user_id=" . $_GET["id"] . "'><img src='" . html_get_theme_images_path("delete_icon.gif") . "' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>
							</tr>\n";
					}
				}else{
					print "<tr><td><em>" . _("No Trees") . "</em></td></tr>";
				}
				?>
			</table>
		</td>
	</tr>

	<?php

	html_end_box(false);

	?>
	<table align='center' width='98%'>
		<tr>
			<td nowrap><?php echo _("Add Tree:"); ?>&nbsp;
				<?php form_dropdown("perm_trees",db_fetch_assoc("select id,name from graph_tree order by name"),"name","id","","","");?>
			</td>
			<td align="right">
				&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _("Add"); ?>" name="add_tree" align="absmiddle">
			</td>
		</tr>
	</table>
	<br>

	<?php
	form_hidden_box("save_component_graph_perms","1","");
}


/* ---------------------------------
    user_realms_edit function
   --------------------------------- */

function user_realms_edit() {
	global $colors, $user_auth_realms;

	?>
	<table width='98%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'><?php echo _("Realm permissions control which sections of Cacti this user will have access to."); ?></span>
			</td>
		</tr>
	</table>
	<?php

	html_start_box("", "98%", $colors["header_background"], "3", "center", "");

	print "	<tr bgcolor='#" . $colors["header_background"] . "'>
			<td class='textHeaderDark'><strong>" . _("Realm Permissions") . "</strong></td>
			<td width='1%' align='center' bgcolor='#819bc0' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='all' title='" . _("Select All") . "' onClick='SelectAll(\"section\",this.checked)'></td>\n
		</tr>\n";

	?>

	<tr bgcolor='#<?php print $colors["form_alternate2"]; ?>'>
		<td colspan="2" width="100%">
			<table width="100%">
				<tr>
					<td align="top" width="50%">
						<?php
						$i = 0;
						$user_realms_list = api_user_realms_list((empty($_GET["id"]) ? "-1" : $_GET["id"]));
						while (list($realm_id, $realm_data) = each($user_realms_list)) {
							if ($realm_data["value"] == "1") {
								$old_value = "on";
							}else{
								$old_value = "";
							}

							$column1 = floor((sizeof($user_realms_list) / 2) + (sizeof($user_realms_list) % 2));

							if ($i == $column1) {
								print "</td><td valign='top' width='50%'>";
							}

							form_checkbox("section" . $realm_id, $old_value, $realm_data["realm_name"], "", (!empty($_GET["id"]) ? 1 : 0)); print "<br>";

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


/* ---------------------------------
    graph_settings_edit function
   --------------------------------- */

function graph_settings_edit() {
	global $settings_graphs, $tabs_graphs, $colors, $graph_views, $graph_tree_views;

	?>
	<table width='98%' align='center' cellpadding="5">
		<tr>
			<td>
				<span style='font-size: 12px; font-weight: bold;'><?php echo _("Graph settings control how graphs are displayed for this user."); ?></span>
			</td>
		</tr>
	</table>
	<?php

	html_start_box("<strong>" . _("Graph Settings") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

	/* get user graph settings */
	$user_settings = api_user_graph_setting_list($_GET["id"]);

	while (list($tab_short_name, $tab_fields) = each($settings_graphs)) {
		?>
		<tr bgcolor='<?php print $colors["header_panel_background"];?>'>
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
					$form_array[$field_name]["items"][$sub_field_name]["value"] =  $user_settings[$sub_field_name];
				}
			}else{
				if (graph_config_value_exists($field_name, $_GET["id"])) {
					$form_array[$field_name]["form_id"] = 1;
				}
				$form_array[$field_name]["value"] = $user_settings[$field_name];
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
    user_edit function
   -------------------------- */

function user_edit() {
	global $colors, $fields_user_user_edit_host;

	if (!empty($_GET["id"])) {
		$user = api_user_info(array( "id" => $_GET["id"]));
		$header_label = _("[edit: ") . $user["username"] . "]";
	}else{
		$header_label = _("[new]");
	}

	html_start_box("<strong>" . _("User Management") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");
	draw_edit_form(array(
		"config" => array("form_name" => "chk"),
		"fields" => inject_form_variables($fields_user_user_edit_host, (isset($user) ? $user : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		/* draw user admin nav tabs */
		?>
		<input type='hidden' name='last_action' value='<?php print $_GET["action"] ?>'>
		<table class='tabs' width='98%' cellspacing='0' cellpadding='3' align='center'>
			<tr>
				<td width='1'></td>
				<td <?php print ((($_GET["action"] == "user_realms_edit") || ($_GET["action"] == "user_edit")) ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='150' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=user_realms_edit&id=<?php print $_GET["id"];?>'><?php echo _("Realm Permissions");?></a></span>
				</td>
				<td width='1'></td>
				<td <?php print (($_GET["action"] == "graph_perms_edit") ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='150' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=graph_perms_edit&id=<?php print $_GET["id"];?>'><?php echo _("Graph Permissions");?></a></span>
				</td>
				<td width='1'></td>
				<td <?php print (($_GET["action"] == "graph_settings_edit") ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='130' align='center' class='tab'>
					<span class='textHeader'><a href='user_admin.php?action=graph_settings_edit&id=<?php print $_GET["id"];?>'><?php echo _("Graph Settings");?></a></span>
				</td>
				<td></td>
			</tr>
		</table>
		<?php
	}

	if ($_GET["action"] == "graph_settings_edit") {
		graph_settings_edit();
	}elseif ($_GET["action"] == "user_realms_edit") {
		user_realms_edit();
	}elseif ($_GET["action"] == "graph_perms_edit") {
		graph_perms_edit();
	}else{
		user_realms_edit();
	}

	form_save_button("user_admin.php");
}


/* ------------------------
    user function
   ------------------------ */

function user() {
	global $user_actions, $colors, $auth_realms;

	html_start_box("<strong>" . _("User Management") . "</strong>", "98%", $colors["header_background"], "3", "center", "user_admin.php?action=user_edit");

	html_header_checkbox(array(_("User Name"), _("Full Name"), _("Status"),_("Realm"), _("Default Graph Policy"), _("Last Login"), _("Last Login From"),_("Last Password Change")));

	$user_list = api_user_list( array( "1" => "username" ) );

	$i = 0;
	if (sizeof($user_list) > 0) {
	foreach ($user_list as $user_list_values) {
		$user = api_user_info( array( "id" => $user_list_values["id"] ) );
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
			?>
			<td>
				<a class="linkEditMain" href="user_admin.php?action=user_edit&id=<?php print $user["id"];?>"><?php print $user["username"];?></a>
			</td>
			<td>
				<?php print $user["full_name"];?>
			</td>
			<td>
				<?php if ($user["enabled"] == "1") { print _("Enabled"); }else{ print _("Disabled"); }?>
			</td>
			<td>
				<?php print $auth_realms[$user["realm"]];?>
			</td>
			<td>
				<?php if ($user["policy_graphs"] == "1") { print _("ALLOW"); }else{ print _("DENY"); }?>
			</td>
			<td>
				<?php print $user["last_login_formatted"];?>
			</td>
			<td>
				<?php print $user["last_login_ip"];?>
			</td>
			<td>
				<?php
				if ($user["realm"] != "0") {
					print _("N/A");
				}else{
					if ($user["password_change_last"] == "0000-00-00 00:00:00") {
						print _("Never");
					}else{
						print $user["password_change_last_formatted"];
					}
				} ?>
			</td>


			<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
				<input type='checkbox' style='margin: 0px;' name='chk_<?php print $user["id"];?>' title="<?php print $user["username"];?>">
			</td>
		</tr>
	<?php
	$i++;
	}
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($user_actions);

}

/* ------------------------
    actions function
   ------------------------ */

function user_actions() {
	global $colors, $user_actions, $fields_user_edit, $user_password_expire_intervals;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "3") {
			/* Enable Selected Users */
			for ($i=0;($i<count($selected_items));$i++) {
				api_user_enable($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "4") {
			/* Disable Selected Users */
			for ($i=0;($i<count($selected_items));$i++) {
				api_user_disable($selected_items[$i]);
			}
		}elseif ($_POST["drp_action"] == "1") {
			/* Delete User */
			for ($i=0; $i<count($selected_items); $i++) {
				api_user_remove($selected_items[$i]);
			}

		}elseif ($_POST["drp_action"] == "2") {
			/* Copy User */
			/* Check for new user name */
			if ((!empty($_POST["user_new"])) && (!empty($_POST["user_name"]))) {
				if (api_user_copy($_POST["user_name"],$_POST["user_new"]) == 1) {
					raise_message(12);
				}
			}
		}elseif ($_POST["drp_action"] == "5") {
			/* Password Expiration */
			for ($i=0; $i<count($selected_items); $i++) {
				api_user_expire_length_set($selected_items[$i], $_POST["expire_interval"]);
			}

		}

		header("Location: user_admin.php");
		exit;
	}

	/* setup some variables */
	$user_list = ""; $i = 0; $username = "";

	/* loop through each of the users selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$user = api_user_info( array( "id" => $matches[1]) );
			$user_list .= "<li>" . $user["username"] . "<br>";
			$username_list[$user["username"]] = $user["username"];
			$user_array[$i] = $matches[1];
		}
		$i++;
	}

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $user_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='user_admin.php' method='post'>\n";

	if ($_POST["drp_action"] == "3") { /* Enable Users */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("To enable the following users, press the \"yes\" button below.") . "</p>
					<p>$user_list</p>
				</td>
				</tr>";
	}elseif ($_POST["drp_action"] == "4") { /* Disable Users */
		print "	<tr>
				<td colspan='4' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>". _("To disable the following users, press the \"yes\" button below.") . "</p>
					<p>$user_list</p>
				</td>
				</tr>";

	}elseif ($_POST["drp_action"] == "2") { /* copy user */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Would you like to copy a user to a new user?") . "</p>
				</td>
				</tr>";

		if (isset($user_array)) {

			$form_array = array(
			"user_name" => array(
				"method" => "drop_array",
				"friendly_name" => _("User Name"),
				"description" => _("Select the user name you would like to copy from."),
				"value" => "",
				"array" => $username_list
				),
			"user_new" => array(
				"method" => "textbox",
				"friendly_name" => _("New User Name"),
				"description" => _("Type the user name of the new user."),
				"value" => "",
				"max_length" => "100"
				)
			);
			draw_edit_form(
				array(
					"config" => array("no_form_tag" => true),
					"fields" => $form_array
					)
				);
		}

	}elseif ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Are you sure you want to delete the following users?") . "</p>
					<p>$user_list</p>
					</td></tr>
				</td>
			</tr>\n
			";

	}elseif ($_POST["drp_action"] == "5") { /* Password Expiration */
		print "	<tr>
				<td colspan='2' class='textArea' bgcolor='#" . $colors["form_alternate1"] . "'>
					<p>" . _("Would you like to set Password Expiration?") . "</p>
					<p>$user_list</p>
				</td>
				</tr>";


		$form_array = array(
		"expire_interval" => array(
			"method" => "drop_array",
			"friendly_name" => _("Password Expiration Interval"),
			"description" => _("Select the interval that you would like to apply to the selected users."),
			"value" => "",
			"array" => $user_password_expire_intervals
			)
		);
		draw_edit_form(
			array(
				"config" => array("no_form_tag" => true),
				"fields" => $form_array
				)
			);
	}

	if (!isset($user_array)) {
		print "<tr><td colspan='2' bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one user.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td colspan='2' align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($user_array) ? serialize($user_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='user_admin.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

?>
