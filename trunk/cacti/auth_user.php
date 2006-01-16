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

require_once(CACTI_BASE_PATH . "/lib/auth/auth_info.php");
/* Remove once include is in global.php */
require_once(CACTI_BASE_PATH . "/lib/auth/auth_update.php");


define("MAX_DISPLAY_PAGES", 21);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {

	case 'save':
		form_post();

	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		view_users();
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}


/* -----------------------
    Utilities Functions
   ----------------------- */
function form_post() {


	$get_string = "";

	/* Process clear request and get out */
	if (isset($_POST["box-1-action-clear-button"])) {
		header("auth_user.php");
	}

	if (($_POST["action_post"] == "box-1") && (isset($_POST["box-1-action-area-type"]))) { 
		if (($_POST["box-1-action-area-type"] == "search") || ($_POST["box-1-action-area-type"] == "export")) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
			if (isset($_POST["box-1-search_username"])) {
				if ($_POST["box-1-search_username"] != "-1") {
					$get_string .= ($get_string == "" ? "?" : "&") . "search_username=" . urlencode($_POST["box-1-search_username"]);
				}
			}
		}

	} elseif ((isset($_POST["box-1-search_filter"]))) { 
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}
	}

	header("Location: auth_user.php" . $get_string);

	exit;

}


function view_users() {

	$current_page = get_get_var_number("page", "1");

	/* setup action menu */
	$menu_items = array(
		"delete" => "Delete",
		"duplicate" => "Duplicate",
		"enable" => "Enable",
		"disable" => "Disable",
		"passwdexpire" => "Password Expire"
	);


	/* search field: filter (searchs device description and hostname) */
	$filter_array = array();
	$filter_url = "";
	if (isset_get_var("search_filter")) {
		$filter_array["name"] = get_get_var("search_filter");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_filter=" . urlencode(get_get_var("search_filter"));
	}
	if (isset_get_var("search_name")) {
		$filter_array["name"] = get_get_var("search_name");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_name=" . urlencode(get_get_var("search_name"));
	}
	if (isset_get_var("search_description")) {
		$filter_array["description"] = get_get_var("search_description");
		$filter_url .= ($filter_url == "" ? "" : "&") . "search_description=" . urlencode(get_get_var("search_description"));
	}

	/* get log entires */
	$users = api_auth_control_list($filter_array,read_config_option("num_rows_page"),read_config_option("num_rows_page")*($current_page-1));
	$total_rows = api_auth_control_total_get($filter_array);

	/* generate page list */
	$url_string = build_get_url_string(array("search_filter","search_name","search_description"));
	$url_page_select = get_page_list($current_page, MAX_DISPLAY_PAGES, read_config_option("num_rows_page"), $total_rows, "auth_user.php" . $url_string . ($url_string == "" ? "?" : "&") . "page=|PAGE_NUM|");

	/* Output html */
	$box_id = 1;
	form_start("auth_user.php");

	html_start_box("<strong>" . _("Users") . "</strong>", "auth_user.php?action=add", $url_page_select);
	html_header_checkbox(array(_("Username"), _("Full Name"), _("Enabled"), _("Last Login"), _("Last Login IP")), $box_id);

	$i = 0;
	if ((is_array($users)) && (sizeof($users) > 0)) {
		foreach ($users as $user) {
			$user_info = api_auth_control_get(AUTH_CONTROL_OBJECT_TYPE_USER,$user["id"]);
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $user["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $user["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $user["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $user["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $user["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $user["id"];?>')" href="auth_user.php?action=edit&id=<?php echo $user["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $user["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $user["name"]);?></span></a>
				</td>
				<td class="content-row">
					<?php echo $user["description"];?>
				</td>
				<td class="content-row">
					<?php if ($user_info["enabled"] == 1) { echo "Yes"; }else{ echo "No"; }?>
				</td>
				<td class="content-row">
					<?php if (($user_info["last_login"] == "0000-00-00 00:00:00") || ($user_info["last_login"] == "")) { echo "N/A"; }else{ echo $user["last_login"]; }?>
				</td>
				<td class="content-row">
					<?php if ($user_info["last_login_ip"] == "") { echo "N/A"; }else{ echo $user_info["last_login_ip"]; } ?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $user["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $user["id"];?>' title="<?php echo $user["name"];?>">
				</td>
			</tr>

			<?php
		}

	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No Users Found.
			</td>
		</tr>
		<?php
	}

	html_box_toolbar_draw($box_id, "0", "8", (sizeof($filter_array) == 0 ? HTML_BOX_SEARCH_INACTIVE : HTML_BOX_SEARCH_ACTIVE), $url_page_select, 0);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items, 250);
	html_box_actions_area_draw($box_id, "0", 400);

	form_hidden_box("action_post", "auth_user_list");
	form_end();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {

	}
	-->
	</script>

	<?php

}



?>
