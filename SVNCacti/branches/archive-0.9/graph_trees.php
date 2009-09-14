<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Group                                 |
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
require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
require_once(CACTI_BASE_PATH . "/lib/api_tree.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_utility.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		tree_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		tree();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */
function form_save() {
	if ($_POST["action_post"] == "graph_tree_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_graph_tree["name"] = $_POST["name"];
		$form_graph_tree["sort_type"] = $_POST["sort_type"];

		/* validate graph tree preset fields */
		field_register_error(api_graph_tree_fields_validate($form_graph_tree, "|field|"));

		if (!is_error_message()) {
			$graph_tree_id = api_graph_tree_save($_POST["id"], $form_graph_tree);
		}

		if (is_error_message()) {
			header("Location: graph_trees.php?action=edit" . (empty($graph_tree_id) ? "" : "&id=$graph_tree_id"));
		}else{
			header("Location: graph_trees.php");
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $graph_tree_id) {
				api_graph_tree_remove($graph_tree_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "duplicate") {
			// yet yet coded
		}

		header("Location: graph_trees.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "graph_tree_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: graph_trees.php$get_string");
	}
}

/* ---------------------
    Tree Functions
   --------------------- */

function tree_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(_("Are You Sure?"), "Are you sure you want to delete the tree <strong>'" . db_fetch_cell("select name from graph_tree where id=" . $_GET["id"]) . "'</strong>?", "graph_trees.php", "graph_trees.php?action=remove&id=" . $_GET["id"]);
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from graph_tree where id=" . $_GET["id"]);
		db_execute("delete from graph_tree_items where graph_tree_id=" . $_GET["id"]);
	}
}

function tree_edit() {
	$menu_items = array(
		"remove" => "Remove"
		);

	$_graph_tree_id = get_get_var_number("id");

	if (empty($_graph_tree_id)) {
		$header_label = "[new]";
	}else{
		$graph_tree = api_graph_tree_get($_graph_tree_id);

		$header_label = "[edit: " . $graph_tree["name"] . "]";
	}

	form_start("graph_trees.php", "form_graph_tree");

	html_start_box("<strong>" . _("Graph Trees") . "</strong> $header_label", "");

	_graph_tree_field__name("name", (isset($graph_tree["name"]) ? $graph_tree["name"] : ""), (empty($_graph_tree_id) ? 0 : $_graph_tree_id));
	_graph_tree_field__sort_type("sort_type", (isset($graph_tree["sort_type"]) ? $graph_tree["sort_type"] : ""), (empty($_graph_tree_id) ? 0 : $_graph_tree_id));

	html_end_box();

	form_hidden_box("id", $_graph_tree_id);
	form_hidden_box("action_post", "graph_tree_edit");

	form_save_button("graph_trees.php");

	if (!empty($_graph_tree_id)) {
		echo "<br />\n";

		form_start("graph_trees_items.php", "form_graph_tree_item");

		$box_id = "1";
		html_start_box("<strong>" . _("Tree Items") . "</strong>", "graph_trees_items.php?action=edit&tree_id=" . $_graph_tree_id . "&parent_id=0");
		html_header_checkbox(array(_("Item"), _("Type"), ""), $box_id, "1");

		/* get a sorted list of all graph items inside of this tree */
		$tree_items = api_graph_tree_item_list($_graph_tree_id);

		/* get a list of available types (header, host, graph, etc) */
		$tree_item_types = api_graph_tree_item_type_list();

		if ((is_array($tree_items) > 0) && (sizeof($tree_items) > 0)) {
			foreach ($tree_items as $tree_item) {
				$current_depth = api_graph_tree_item_depth_get($tree_item["order_key"]);

				/* keep track of the current item's sort type so we know whether to display sort
				 * arrays for items children or not */
				$sort_cache[$current_depth] = $tree_item["sort_children_type"];

				$transparent_indent = "";

				if ($tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) {
					$item_text = $tree_item["graph_title"];
				}else if ($tree_item["item_type"] == TREE_ITEM_TYPE_HEADER) {
					$item_text = "<strong>" . $tree_item["item_value"] . "</strong></a> (<a href='graph_trees_items.php?action=edit&tree_id=" . $_graph_tree_id . "&parent_id=" . $tree_item["id"] . "'>Add</a>)";
				}else if ($tree_item["item_type"] == TREE_ITEM_TYPE_HOST) {
					$item_text = "<strong>Device:</strong> " . $tree_item["host_hostname"];
				}

				?>
				<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $tree_item["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[1],'box-<?php echo $box_id;?>-row-<?php echo $tree_item["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $tree_item["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $tree_item["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $tree_item["id"];?>')">
					<td class="title">
						<img width="<?php echo (($current_depth - 1) * 20);?>" height="1" align="middle" alt="">&nbsp;<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $tree_item["id"];?>')" href="graph_trees_items.php?action=edit&tree_id=<?php echo $_graph_tree_id;?>&id=<?php echo $tree_item["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $tree_item["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $item_text);?></span></a>
					</td>
					<td>
						<?php echo $tree_item_types{$tree_item["item_type"]};?>
					</td>
					<?php if ( ((isset($sort_cache{$current_depth - 1})) && ($sort_cache{$current_depth - 1} != TREE_ORDERING_NONE)) || ($graph_tree["sort_type"] != TREE_ORDERING_NONE) ) { ?>
					<td width="80">
						&nbsp;
					</td>
					<?php }else{ ?>
					<td width="80" align="center">
						<a href="graph_trees_items.php?action=movedown&id=<?php echo $tree_item["id"];?>&graph_tree_id=<?php echo $_graph_tree_id;?>"><img src="<?php echo html_get_theme_images_path("move_down.gif");?>" border="0" alt="Move Down"></a>
						<a href="graph_trees_items.php?action=moveup&id=<?php echo $tree_item["id"];?>&graph_tree_id=<?php echo $_graph_tree_id;?>"><img src="<?php echo html_get_theme_images_path("move_up.gif");?>" border="0" alt="Move Up"></a>
					</td>
					<?php } ?>

					<td class="checkbox" align="center">
						<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $tree_item["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $tree_item["id"];?>' title="<?php echo strip_tags($item_text);?>">
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr class="empty">
				<td colspan="6">
					No graph tree items found.
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "1", "3", HTML_BOX_SEARCH_NONE);
		html_end_box(false);

		html_box_actions_menu_draw($box_id, "1", $menu_items);

		form_hidden_box("graph_tree_id", $_graph_tree_id);
		form_hidden_box("action", "save");
		form_hidden_box("action_post", "graph_tree_item_list");

		form_end();

		?>
		<script language="JavaScript">
		<!--
		function action_area_handle_type(box_id, type, parent_div, parent_form) {
			if (type == 'remove') {
				parent_div.appendChild(document.createTextNode('Are you sure you want to remove these graph tree items?'));
				parent_div.appendChild(action_area_generate_selected_rows(box_id));

				action_area_update_header_caption(box_id, 'Remove Graph Tree Item');
				action_area_update_submit_caption(box_id, 'Remove');
				action_area_update_selected_rows(box_id, parent_form);
			}
		}
		-->
		</script>
		<?php
	}
}

function tree() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$filter_array = array();

	/* search field: filter (searches template name) */
	if (isset_get_var("search_filter")) {
		$filter_array["name"] = get_get_var("search_filter");
	}

	/* get a list of all devices on this page */
	$graph_trees = api_graph_tree_list($filter_array);

	form_start("graph_trees.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Graph Trees") . "</strong>", "graph_trees.php?action=edit");
	html_header_checkbox(array(_("Name")), $box_id);

	$i = 0;
	if (sizeof($graph_trees) > 0) {
		foreach ($graph_trees as $graph_tree) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $graph_tree["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $graph_tree["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $graph_tree["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $graph_tree["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $graph_tree["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $graph_tree["id"];?>')" href="graph_trees.php?action=edit&id=<?php echo $graph_tree["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $graph_tree["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $graph_tree["name"]);?></span></a>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $graph_tree["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $graph_tree["id"];?>' title="<?php echo $graph_tree["name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr class="empty">
			<td colspan="6">
				No graph trees found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "1", HTML_BOX_SEARCH_NO_ICON);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_hidden_box("action_post", "graph_tree_list");
	form_end();

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these graph trees?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Graph Tree');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these graph trees?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Graph Trees');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}
 ?>
