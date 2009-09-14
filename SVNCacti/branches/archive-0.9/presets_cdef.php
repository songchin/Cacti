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
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_cdef_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_cdef_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_cdef_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		cdef_remove();

		header ("Location: presets.php?action=view_cdef");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		cdef_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if ($_POST["action_post"] == "cdef_preset_edit") {
		/* cache all post field values */
		init_post_field_cache();

		$form_cdef["name"] = $_POST["name"];
		$form_cdef["cdef_string"] = $_POST["cdef_string"];

		/* validate base cdef preset fields */
		field_register_error(api_data_preset_cdef_field_validate($form_cdef, "|field|"));

		if (!is_error_message()) {
			$preset_cdef_id = api_data_preset_cdef_save($_POST["preset_cdef_id"], $form_cdef);

			if (empty($preset_cdef_id)) {
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: presets_cdef.php?action=edit" . (empty($preset_cdef_id) ? "" : "&id=$preset_cdef_id"));
		}else{
			header("Location: presets.php?action=view_cdef");
		}
	}else if (isset($_POST["box-1-action-area-button"])) {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $preset_cdef_id) {
				api_data_preset_cdef_remove($preset_cdef_id);
			}
		}

		header("Location: presets.php?action=view_cdef");
	}
}

function cdef_edit() {
	$_cdef_preset_id = get_get_var_number("id");

	if (empty($_cdef_preset_id)) {
		$header_label = "[new]";
	}else{
		$cdef = api_data_preset_cdef_get($_cdef_preset_id);

		$header_label = "[edit: " . $cdef["name"] . "]";
	}

	form_start("presets_cdef.php", "form_cdef");

	/* ==================== Box: RRAs ==================== */

	html_start_box("<strong>" . _("CDEF Presets") . "</strong> $header_label");
	_data_preset_cdef__name("name", (isset($cdef["name"]) ? $cdef["name"] : ""), (isset($cdef["id"]) ? $cdef["id"] : "0"));
	_data_preset_cdef__cdef_string("cdef_string", (isset($cdef["cdef_string"]) ? $cdef["cdef_string"] : ""), (isset($cdef["id"]) ? $cdef["id"] : "0"));
	html_end_box();

	html_box_actions_area_create("1");

	form_hidden_box("preset_cdef_id", $_cdef_preset_id);
	form_hidden_box("action_post", "cdef_preset_edit");

	form_save_button("presets.php?action=view_cdef", "save_cdef");

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'editor') {
			parent_div.appendChild(document.createTextNode('The dropdown boxes below provide quick access to the variables, mathematical operators, and functions that can be used in CDEF strings.'));

			action_area_update_header_caption(box_id, 'CDEF String Editor');

			_elm_function_input = action_area_generate_select('box-' + box_id + '-cdef_function');
			<?php echo get_js_dropdown_code('_elm_function_input', api_data_preset_cdef_function_list(), "");?>

			_elm_operator_input = action_area_generate_select('box-' + box_id + '-cdef_operator');
			<?php echo get_js_dropdown_code('_elm_operator_input', api_data_preset_cdef_operator_list(), "");?>

			_elm_variable_input = action_area_generate_select('box-' + box_id + '-cdef_variable');
			<?php echo get_js_dropdown_code('_elm_variable_input', api_data_preset_cdef_variable_list(), "");?>

			_elm_dt_container = document.createElement('div');
			_elm_dt_container.style.paddingTop = '8px';
			_elm_dt_container.style.paddingBottom = '3px';
			_elm_dt_container.style.marginLeft = '1px';
			_elm_dt_container.style.width = '550px';

			_elm_dt_table_fld = document.createElement('table');

			_elm_dt_table_fld.appendChild(action_area_generate_insert_row('Functions', _elm_function_input, 'javascript:insert_cdef_variable_name(\'box-' + box_id + '-cdef_function\')'));
			_elm_dt_table_fld.appendChild(action_area_generate_insert_row('Operators', _elm_operator_input, 'javascript:insert_cdef_variable_name(\'box-' + box_id + '-cdef_operator\')'));
			_elm_dt_table_fld.appendChild(action_area_generate_insert_row('Variables', _elm_variable_input, 'javascript:insert_cdef_variable_value(\'box-' + box_id + '-cdef_variable\')'));

			_elm_dt_container.appendChild(_elm_dt_table_fld);

			parent_div.appendChild(_elm_dt_container);
		}
	}

	function action_area_generate_insert_row(field_caption, input, href) {
		_elm_dt_tablerow_fld = document.createElement('tr');

		_elm_dt_tablecell_txt = document.createElement('td');
		_elm_dt_tablecell_txt.style.width = '90px';
		_elm_dt_tablecell_txt.appendChild(document.createTextNode(field_caption));

		_elm_dt_tablecell_inp = document.createElement('td');
		_elm_dt_tablecell_inp.style.width = '400px';
		_elm_dt_tablecell_inp.appendChild(input);

		_elm_dt_tablecell_ins = document.createElement('td');
		_elm_dt_tablecell_ins.style.textAlign = 'right';
		_elm_dt_href_insert = document.createElement('a');
		_elm_dt_href_insert.style.fontWeight = 'bold';
		_elm_dt_href_insert.href = href;
		_elm_dt_href_insert.textContent = 'insert';
		_elm_dt_tablecell_ins.appendChild(_elm_dt_href_insert);

		_elm_dt_tablerow_fld.appendChild(_elm_dt_tablecell_txt);
		_elm_dt_tablerow_fld.appendChild(_elm_dt_tablecell_inp);
		_elm_dt_tablerow_fld.appendChild(_elm_dt_tablecell_ins);

		return _elm_dt_tablerow_fld;
	}

	function insert_cdef_variable_name(dropdown_name) {
		cdef_string = document.getElementById('cdef_string');
		dropdown = document.getElementById(dropdown_name);

		if ((cdef_string.value.length > 0) && (cdef_string.value.substr(cdef_string.value.length - 1, cdef_string.value.length) != ",")) {
			cdef_string.value += ",";
		}

		cdef_string.value += dropdown.options[dropdown.selectedIndex].text;
	}

	function insert_cdef_variable_value(dropdown_name) {
		cdef_string = document.getElementById('cdef_string');
		dropdown = document.getElementById(dropdown_name);

		if ((cdef_string.value.length > 0) && (cdef_string.value.substr(cdef_string.value.length - 1, cdef_string.value.length) != ",")) {
			cdef_string.value += ",";
		}

		cdef_string.value += dropdown.options[dropdown.selectedIndex].value;
	}
	-->
	</script>

	<?php
}

?>
