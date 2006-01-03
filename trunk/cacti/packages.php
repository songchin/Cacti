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
require_once(CACTI_BASE_PATH . "/lib/sys/package_export.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
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
	html_start_box("<strong>" . _("Template Packages") . "</strong>", "packages.php?action=edit");
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

	package_payload_export("1");
	echo "<pre>" . htmlspecialchars(package_graph_template_export("1")) . "</pre>";
	echo "<pre>" . htmlspecialchars(package_data_template_export("6")) . "</pre>";
	echo "<pre>" . htmlspecialchars(package_data_query_export("1")) . "</pre>";
	echo "<pre>" . htmlspecialchars(package_script_export("10")) . "</pre>";
	echo "<pre>" . htmlspecialchars(package_rra_export("1")) . "</pre>";

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
