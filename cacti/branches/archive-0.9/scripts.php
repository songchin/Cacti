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
require_once(CACTI_BASE_PATH . "/include/script/script_constants.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_info.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_form.php");
require_once(CACTI_BASE_PATH . "/lib/script/script_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		script_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		script();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

function form_save() {
	if ($_POST["action_post"] == "script_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_script["name"] = $_POST["name"];
		$form_script["input_string"] = $_POST["input_string"];
		$form_script["type_id"] = $_POST["type_id"];

		field_register_error(api_script_field_validate($form_script, "|field|"));

		/* if the validation passes, save the row to the database */
		if (!is_error_message()) {
			$script_id = api_script_save($_POST["id"], $form_script);
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: scripts.php?action=edit&id=" . (empty($script_id) ? $_POST["id"] : $script_id));
		}else{
			header("Location: scripts.php");
		}
	/* submit button on the actions area page */
	}else if ($_POST["action_post"] == "box-1") {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $script_id) {
				api_script_remove($script_id);
			}
		}else if ($_POST["box-1-action-area-type"] == "duplicate") {
			// yet yet coded
		}

		header("Location: scripts.php");
	/* 'filter' area at the bottom of the box */
	}else if ($_POST["action_post"] == "script_list") {
		$get_string = "";

		/* the 'clear' button wasn't pressed, so we should filter */
		if (!isset($_POST["box-1-action-clear-button"])) {
			if (trim($_POST["box-1-search_filter"]) != "") {
				$get_string = ($get_string == "" ? "?" : "&") . "search_filter=" . urlencode($_POST["box-1-search_filter"]);
			}
		}

		header("Location: scripts.php$get_string");
	}
}

function script_edit() {
	$menu_items = array(
		"remove" => "Remove"
		);

	$_script_id = get_get_var_number("id");

	if (empty($_script_id)) {
		$header_label = "[new]";
	}else{
		$script = api_script_get($_script_id);

		$header_label = "[edit: " . $script["name"] . "]";
	}

	form_start("scripts.php", "form_script");

	html_start_box("<strong>" . _("Scripts") . "</strong> $header_label");

	_script_field__name("name", (isset($script["name"]) ? $script["name"] : ""), (isset($script["id"]) ? $script["id"] : "0"));
	_script_field__type_id("type_id", (isset($script["type_id"]) ? $script["type_id"] : ""), (isset($script["id"]) ? $script["id"] : "0"));
	_script_field__input_string("input_string", (isset($script["input_string"]) ? $script["input_string"] : ""), (isset($script["id"]) ? $script["id"] : "0"));

	html_end_box();

	form_hidden_box("id", $_script_id);
	form_hidden_box("action_post", "script_edit");

	form_save_button("scripts.php");

	if (!empty($_script_id)) {
		echo "<br />\n";

		form_start("scripts_fields.php", "form_script_item");

		$box_id = "1";
		html_start_box("<strong>" . _("Script Fields") . "</strong>");

		?>
		<tr class="heading">
			<td colspan="2">
				Input Fields
			</td>
			<td align="right">
				<a href="scripts_fields.php?action=edit&field_type=<?php echo SCRIPT_FIELD_TYPE_INPUT;?>&script_id=<?php echo $_script_id;?>">Add</a>
			</td>
		</tr>
		<?php
		$input_fields = api_script_field_list($_script_id, SCRIPT_FIELD_TYPE_INPUT);

		if ((is_array($input_fields) > 0) && (sizeof($input_fields) > 0)) {
			foreach ($input_fields as $field) {
				?>
				<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[1],'box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')">
					<td class="item">
						<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')" href="scripts_fields.php?action=edit&script_id=<?php echo $_script_id;?>&id=<?php echo $field["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $field["id"];?>"><?php echo $field["data_name"];?></span></a>
					</td>
					<td>
						<?php echo $field["name"];?>
					</td>
					<td class="checkbox"align="center">
						<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>' title="<?php echo $field["data_name"];?>">
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr class="empty">
				<td colspan="2">
					No input fields found. This means that no input parameters will be passed to the script.
				</td>
			</tr>
			<?php
		}

		?>
		<tr class="heading">
			<td colspan="2">
				Output Fields
			</td>
			<td align="right">
				<a href="scripts_fields.php?action=edit&field_type=<?php echo SCRIPT_FIELD_TYPE_OUTPUT;?>&script_id=<?php echo $_script_id;?>">Add</a>
			</td>
		</tr>
		<?php
		$output_fields = api_script_field_list($_script_id, SCRIPT_FIELD_TYPE_OUTPUT);

		if ((is_array($output_fields) > 0) && (sizeof($output_fields) > 0)) {
			foreach ($output_fields as $field) {
				?>
				<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[1],'box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')">
					<td class="title">
						<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $field["id"];?>')" href="scripts_fields.php?action=edit&script_id=<?php echo $_script_id;?>&id=<?php echo $field["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $field["id"];?>"><?php echo $field["data_name"];?></span></a>
					</td>
					<td>
						<?php echo $field["name"];?>
					</td>
					<td class="checkbox" align="center">
						<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $field["id"];?>' title="<?php echo $field["data_name"];?>">
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr class="empty">
				<td colspan="2">
					No output fields found. Remember that <strong>at least one output field</strong> must be defined!
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "1", "2", HTML_BOX_SEARCH_NONE);
		html_end_box(false);

		html_box_actions_menu_draw($box_id, "1", $menu_items);

		form_hidden_box("script_id", $_script_id);
		form_hidden_box("action", "save");
		form_hidden_box("action_post", "script_field_list");

		form_end();

		?>
		<script language="JavaScript">
		<!--
		function action_area_handle_type(box_id, type, parent_div, parent_form) {
			if (type == 'remove') {
				parent_div.appendChild(document.createTextNode('Are you sure you want to remove these script fields?'));
				parent_div.appendChild(action_area_generate_selected_rows(box_id));

				action_area_update_header_caption(box_id, 'Remove Script Field');
				action_area_update_submit_caption(box_id, 'Remove');
				action_area_update_selected_rows(box_id, parent_form);
			}
		}
		-->
		</script>
		<?php
	}
}

function script() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$scripts = api_script_list();

	$script_input_types = api_script_input_type_list();

	form_start("scripts.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Scripts") . "</strong>", "scripts.php?action=edit");
	html_header_checkbox(array(_("Name"), _("Input Type")), $box_id);

	if (sizeof($scripts) > 0) {
		foreach ($scripts as $script) {
			?>
			<tr class="item" id="box-<?php echo $box_id;?>-row-<?php echo $script["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $script["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $script["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $script["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $script["id"];?>')">
				<td class="title">
					<a onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $script["id"];?>')" href="scripts.php?action=edit&id=<?php echo $script["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $script["id"];?>"><?php echo $script["name"];?></span></a>
				</td>
				<td>
					<?php echo $script_input_types{$script["type_id"]}; ?>
				</td>
				<td class="checkbox" align="center">
					<input type='checkbox' name='box-<?php echo $box_id;?>-chk-<?php echo $script["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $script["id"];?>' title="<?php echo $script["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "2");
	}else{
		?>
		<tr class="empty">
			<td colspan="2">
				No scripts found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_create($box_id);

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these scripts?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Scripts');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these scripts?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Scripts');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}
?>
