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
require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");
require_once(CACTI_BASE_PATH . "/lib/package/package_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/sys/package_export.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'new':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_new();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

function form_save() {
	if ($_POST["action_post"] == "package_new") {
		header("Location: packages.php?action=edit");
	}
}

function package_new() {
	form_start("packages.php", "form_package");

	html_start_box("<strong>" . _("Template Packages") . "</strong> [new]");

	_package_field__create_type("create_type", "new");

	html_end_box();

	form_hidden_box("action_post", "package_new");

	form_save_button("packages.php", "save_package");
}

function package_edit() {
	$_package_id = get_get_var_number("id");

	if (empty($_package_id)) {
		$header_label = "[new]";
	}else{
		$package = api_package_get($_package_id);

		$header_label = "[new, from: " . $package["name"] . "]";
	}

	form_start("packages.php", "form_package", true);

	/* ==================== Box: Template Packages ==================== */

	html_start_box("<strong>" . _("Template Packages") . "</strong> $header_label");

	_package_field__name("name", "", "0");
	_package_field__description("description", "", "0");
	_package_field__description_install("description_install", "", "0");
	_package_field__category("category", "", "0");
	_package_field__subcategory("subcategory", "", "0");
	_package_field__vendor("vendor", "", "0");
	_package_field__model("model", "", "0");
	_package_field__author_hdr();
	_package_field__author_type("author_type", "new", "0");
	_package_author_field__name("author_name", "", "0");
	_package_author_field__email("author_email", "", "0");
	_package_author_field__user_forum("author_user_forum", "", "0");
	_package_author_field__user_repository("author_user_repository", "", "0");
	_package_author_type_js();

	html_end_box();

	/* ==================== Box: Associated Graph Templates ==================== */

	html_start_box("<strong>" . _("Associated Graph Templates") . "</strong>");
	html_header(array(_("Template Title")), 2);

	?>
	<tr class="content-row">
		<td class="content-row" style="padding: 4px;">
			Some Template
		</td>
		<td class="content-row" align="right" style="padding: 4px;">
			<a href="dd"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Graph Template Association");?>" border="0" align="absmiddle"></a>
		</td>
	</tr>
	<tr class="content-row">
		<td class="content-row" style="padding: 4px;">
			dffddsf
		</td>
		<td class="content-row" align="right" style="padding: 4px;">
			<a href="dd"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Graph Template Association");?>" border="0" align="absmiddle"></a>
		</td>
	</tr>
	<tr>
		<td style="border-top: 1px solid #b5b5b5; padding: 1px;" colspan="2">
			<table width="100%" cellpadding="2" cellspacing="0">
				<tr>
					<td>
						Add graph template:
						<?php form_dropdown("assoc_graph_template_id", api_graph_template_list(), "template_name", "id", "", "", "");?>
					</td>
					<td align="right">
						&nbsp;<input type="image" src="<?php echo html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add');?>" name="assoc_graph_template_add" align="absmiddle">
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?php

	html_end_box();

	/* ==================== Box: Associated Meta Data ==================== */

	html_start_box("<strong>" . _("Associated Meta Data") . "</strong>");
	html_header(array(_("Name"), _("Type")), 2);

	?>
	<tr class="content-row">
		<td class="content-row" style="padding: 4px;">
			Traffic Sample #1
		</td>
		<td class="content-row" style="padding: 4px;">
			Screenshot
		</td>
		<td class="content-row" align="right" style="padding: 4px;">
			<a href="dd"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Graph Template Association");?>" border="0" align="absmiddle"></a>
		</td>
	</tr>
	<tr class="content-row">
		<td class="content-row" style="padding: 4px;">
			Fetch Octets Script
		</td>
		<td class="content-row" style="padding: 4px;">
			Script
		</td>
		<td class="content-row" align="right" style="padding: 4px;">
			<a href="dd"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Graph Template Association");?>" border="0" align="absmiddle"></a>
		</td>
	</tr>
	<?php

	html_header(array(_("Attach New Meta Data")), 3);

	_package_metadata_field__type("metadata_type", "", "0");
	_package_metadata_field__name("metadata_name", "", "0");
	_package_metadata_field__description("metadata_description", "", "0");
	_package_metadata_field__description_install("metadata_description_install", "", "0");
	_package_metadata_field__required("metadata_required", "", "0");
	_package_metadata_field__payload("metadata_payload", "", "0");
	_package_metadata_field__add_button();
	_package_metadata_field__type_js();

	html_end_box();

	form_hidden_box("action_post", "package_edit");

	form_save_button("packages.php", "save_package");
}

function package() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$filter_array = array();

	/* search field: filter (searches package name) */
	if (isset_get_var("search_filter")) {
		$filter_array["name"] = get_get_var("search_filter");
	}

	/* get a list of all packages on this page */
	$packages = api_package_list($filter_array);

	form_start("packages.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Template Packages") . "</strong>", "packages.php?action=new");
	html_header_checkbox(array(_("Name"), _("Author"), _("Category")), $box_id);

	$i = 0;
	if (sizeof($packages) > 0) {
		foreach ($packages as $package) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')" href="packages.php?action=view&id=<?php echo $package["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $package["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $package["name"]);?></span></a>
				</td>
				<td class="content-row">
					Ian Berry
				</td>
				<td class="content-row">
					<?php echo $package["category"];?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>' title="<?php echo $package["name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No template packages found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "3", HTML_BOX_SEARCH_NONE);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_hidden_box("action_post", "package_list");
	form_end();

	echo "<pre>" . htmlspecialchars(package_payload_export("1")) . "</pre>";

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Data Template');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Data Templates');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>
