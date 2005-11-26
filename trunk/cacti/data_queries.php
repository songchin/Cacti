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
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_constants.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_arrays.php");
require_once(CACTI_BASE_PATH . "/include/data_query/data_query_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

define("LIST_ACTION_REMOVE", 1);
define("LIST_ACTION_DUPLICATE", 2);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'item_remove':
		data_query_item_remove();

		header("Location: data_queries.php?action=edit&id=" . $_GET["snmp_query_id"]);
		break;
	case 'field_edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_query_field_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'remove':
		data_query_remove();

		header ("Location: data_queries.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_query_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		data_query();
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_data_query_x"])) {
		/* cache all post field values */
		init_post_field_cache();

		/* step #2: field validation */
		$form_data_query["id"] = $_POST["data_query_id"];
		$form_data_query["input_type"] = $_POST["input_type"];
		$form_data_query["name"] = $_POST["name"];
		$form_data_query["index_order_type"] = $_POST["index_order_type"];
		$form_data_query["index_title_format"] = $_POST["index_title_format"];

		/* these fields are only displayed when editing a data query field */
		if (!empty($_POST["data_query_id"])) {
			$form_data_query["index_order"] = $_POST["index_order"];
			$form_data_query["index_field_id"] = $_POST["index_field_id"];
		}

		if ($form_data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
			$form_data_query["snmp_oid_num_rows"] = $_POST["snmp_oid_num_rows"];
		}

		if (($form_data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) || ($form_data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY)) {
			$form_data_query["script_path"] = $_POST["script_path"];
		}

		if ($form_data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY) {
			$form_data_query["script_server_function"] = $_POST["script_server_function"];
		}

		field_register_error(validate_data_query_fields($form_data_query, "|field|"));

		/* step #3: field save */
		$data_query_id = false;
		if (is_error_message()) {
			api_syslog_cacti_log("User input validation error for data query [ID#" . $_POST["data_query_id"] . "]", SEV_DEBUG, 0, 0, 0, false, FACIL_WEBUI);
		}else{
			$data_query_id = api_data_query_save($_POST["data_query_id"], $form_data_query);

			if ($data_query_id === false) {
				api_syslog_cacti_log("Save error for data query [ID#" . $_POST["data_query_id"] . "]", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
			}
		}

		if ($data_query_id === false) {
			header("Location: data_queries.php?action=edit" . (empty($_POST["data_query_id"]) ? "" : "&id=" . $_POST["data_query_id"]));
		}else if (empty($_POST["data_query_id"])) {
			header("Location: data_queries.php?action=edit&id=$data_query_id");
		}else{
			header("Location: data_queries.php");
		}
	}else if (isset($_POST["save_data_query_field_x"])) {
		/* cache all post field values */
		init_post_field_cache();

		/* step #2: field validation */
		$form_data_query["id"] = $_POST["data_query_field_id"];
		$form_data_query["data_query_id"] = $_POST["data_query_id"];
		$form_data_query["type"] = $_POST["field_type"];
		$form_data_query["name"] = $_POST["name"];
		$form_data_query["name_desc"] = $_POST["name_desc"];
		$form_data_query["source"] = $_POST["source"];

		/* determine the correct values for the method type/value fields */
		if (isset($_POST["method_group"])) {
			/* value */
			if ($_POST["method_group"] == DATA_QUERY_FIELD_METHOD_GROUP_VALUE) {
				$form_data_query["method_type"] = $_POST["method_type_v"];

				if ($_POST["method_type_v"] == DATA_QUERY_FIELD_METHOD_VALUE_PARSE) {
					$form_data_query["method_value"] = $_POST["method_value_v_parse"];
				}
			/* snmp oid */
			}else if ($_POST["method_group"] == DATA_QUERY_FIELD_METHOD_GROUP_OID) {
				$form_data_query["method_type"] = $_POST["method_type_s"];

				if ($_POST["method_type_s"] == DATA_QUERY_FIELD_METHOD_OID_OCTET) {
					$form_data_query["method_value"] = $_POST["method_value_s_octet"];
				}else if ($_POST["method_type_s"] == DATA_QUERY_FIELD_METHOD_OID_PARSE) {
					$form_data_query["method_value"] = $_POST["method_value_s_parse"];
				}
			}
		}

		field_register_error(validate_data_query_field_fields($form_data_query, "|field|"));

		/* since the 'method_value' field name is abstracted above, we need to pass any input field errors
		 * on to the correct form field */
		if ((isset($_SESSION["sess_error_fields"]["method_value"])) && ($_POST["method_group"] == DATA_QUERY_FIELD_METHOD_GROUP_VALUE) && ($_POST["method_type_v"] == DATA_QUERY_FIELD_METHOD_VALUE_PARSE)) {
			$_SESSION["sess_error_fields"]["method_value_v_parse"] = 1;
		}else if ((isset($_SESSION["sess_error_fields"]["method_value"])) && ($_POST["method_group"] == DATA_QUERY_FIELD_METHOD_GROUP_OID) && ($_POST["method_type_s"] == DATA_QUERY_FIELD_METHOD_OID_OCTET)) {
			$_SESSION["sess_error_fields"]["method_value_s_octet"] = 1;
		}else if ((isset($_SESSION["sess_error_fields"]["method_value"])) && ($_POST["method_group"] == DATA_QUERY_FIELD_METHOD_GROUP_OID) && ($_POST["method_type_s"] == DATA_QUERY_FIELD_METHOD_OID_PARSE)) {
			$_SESSION["sess_error_fields"]["method_value_s_parse"] = 1;
		}

		/* step #3: field save */
		$data_query_field_id = false;
		if (is_error_message()) {
			api_syslog_cacti_log("User input validation error for data query field [ID#" . $_POST["data_query_field_id"] . "], data query [ID#" . $_POST["data_query_id"] . "]", SEV_DEBUG, 0, 0, 0, false, FACIL_WEBUI);
		}else{
			$data_query_field_id = api_data_query_field_save($_POST["data_query_field_id"], $form_data_query);

			if ($data_query_field_id === false) {
				api_syslog_cacti_log("Save error for data query field [ID#" . $_POST["data_query_field_id"] . "], data query [ID#" . $_POST["data_query_id"] . "]", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
			}
		}

		if ($data_query_field_id === false) {
			header("Location: data_queries.php?action=field_edit" . (empty($_POST["data_query_field_id"]) ? "" : "&id=" . $_POST["data_query_field_id"]) . "&data_query_id=" . $_POST["data_query_id"]);
		}else{
			header("Location: data_queries.php?action=edit&id=" . $_POST["data_query_id"]);
		}
	}else if (isset($_POST["box-1-action-area-button"])) {
		$selected_rows = explode(":", $_POST["box-1-action-area-selected-rows"]);

		if ($_POST["box-1-action-area-type"] == "remove") {
			foreach ($selected_rows as $data_query_id) {
				api_data_query_remove($data_query_id);
			}
		}

		header("Location: data_queries.php");
	}
}

function data_query_field_edit() {
	$_data_query_field_id = get_get_var_number("id");
	$_data_query_id = get_get_var_number("data_query_id");

	if (empty($_data_query_field_id)) {
		$header_label = "[new]";

		$_field_type = get_get_var("field_type");
	}else{
		$data_query_field = api_data_query_field_get($_data_query_field_id);

		$_field_type = $data_query_field["type"];

		$header_label = "[edit: " . $data_query_field["name"] . "]";
	}

	$data_query = api_data_query_get($_data_query_id);

	form_start("data_queries.php", "form_data_query");

	html_start_box("<strong>" . _("Data Query Fields") . "</strong> $header_label");

	_data_query_field_field__name("name", (isset($data_query_field["name"]) ? $data_query_field["name"] : ""), (isset($data_query_field["id"]) ? $data_query_field["id"] : "0"));
	_data_query_field_field__name_desc("name_desc", (isset($data_query_field["name_desc"]) ? $data_query_field["name_desc"] : ""), (isset($data_query_field["id"]) ? $data_query_field["id"] : "0"));

	if ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
		_data_query_field_field__source_snmp("source", (isset($data_query_field["source"]) ? $data_query_field["source"] : ""), (isset($data_query_field["id"]) ? $data_query_field["id"] : "0"));
	} else if (($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SCRIPT_QUERY) || ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_PHP_SCRIPT_SERVER_QUERY)) {
		_data_query_field_field__source_script("source", (isset($data_query_field["source"]) ? $data_query_field["source"] : ""), (isset($data_query_field["id"]) ? $data_query_field["id"] : "0"));
	}

	if ($data_query["input_type"] == DATA_QUERY_INPUT_TYPE_SNMP_QUERY) {
		_data_query_field_field__method((isset($data_query_field["method_type"]) ? $data_query_field["method_type"] : ""), (isset($data_query_field["method_value"]) ? $data_query_field["method_value"] : ""), (isset($data_query_field["id"]) ? $data_query_field["id"] : "0"));
	}

	html_end_box();

	form_hidden_box("data_query_field_id", $_data_query_field_id);
	form_hidden_box("data_query_id", $_data_query_id);
	form_hidden_box("field_type", $_field_type);

	form_save_button("data_queries.php?action=edit&id=$_data_query_id", "save_data_query_field");
}

/* ---------------------
    Data Query Functions
   --------------------- */

function data_query_edit() {
	$_data_query_id = get_get_var_number("id");

	if (empty($_data_query_id)) {
		$header_label = "[new]";
	}else{
		$data_query = api_data_query_get($_data_query_id);

		$header_label = "[edit: " . $data_query["name"] . "]";
	}

	form_start("data_queries.php", "form_data_query");

	html_start_box("<strong>" . _("Data Queries") . "</strong> $header_label");

	_data_query_field__name("name", (isset($data_query["name"]) ? $data_query["name"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	_data_query_field__input_type("input_type", (isset($data_query["input_type"]) ? $data_query["input_type"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	_data_query_field__index_order_type("index_order_type", (isset($data_query["index_order_type"]) ? $data_query["index_order_type"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	_data_query_field__index_title_format("index_title_format", (isset($data_query["index_title_format"]) ? $data_query["index_title_format"] : "|chosen_order_field|"), (isset($data_query["id"]) ? $data_query["id"] : "0"));

	if (!empty($_data_query_id)) {
		_data_query_field__field_specific_hdr();
		_data_query_field__index_order("index_order", (isset($data_query["index_order"]) ? $data_query["index_order"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
		_data_query_field__index_field_id("index_field_id", $_data_query_id, (isset($data_query["index_field_id"]) ? $data_query["index_field_id"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	}

	/* input type specific fields */
	_data_query_field__snmp_specific_hdr();
	_data_query_field__snmp_oid_num_rows("snmp_oid_num_rows", (isset($data_query["snmp_oid_num_rows"]) ? $data_query["snmp_oid_num_rows"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	_data_query_field__script_specific_hdr();
	_data_query_field__script_path("script_path", (isset($data_query["script_path"]) ? $data_query["script_path"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));
	_data_query_field__script_server_specific_hdr();
	_data_query_field__script_server_function("script_server_function", (isset($data_query["script_server_function"]) ? $data_query["script_server_function"] : ""), (isset($data_query["id"]) ? $data_query["id"] : "0"));

	/* be sure that we have the correct input type value show we display the correct form rows */
	if (isset_post_cache_field("input_type")) {
		$_input_type = get_post_cache_field("input_type");
	}else{
		$_input_type = (isset($data_query["input_type"]) ? $data_query["input_type"] : "");
	}

	echo "<script language=\"JavaScript\">\n<!--\nupdate_data_query_type_fields('$_input_type');\n-->\n</script>\n";

	html_end_box();

	if (!empty($_data_query_id)) {
		html_start_box("<strong>" . _("Data Query Fields") . "</strong>");

		?>
		<tr>
			<td class="content-header-sub" colspan="2">
				Input Fields
			</td>
			<td class="content-header-sub" align="right">
				<a class="link-dark-small" href="data_queries.php?action=field_edit&field_type=1&data_query_id=<?php echo $_data_query_id;?>">Add</a>
			</td>
		</tr>
		<?php
		$input_fields = api_data_query_fields_list($_data_query_id, DATA_QUERY_FIELD_TYPE_INPUT);

		if (sizeof($input_fields) > 0) {
			foreach ($input_fields as $field) {
				?>
				<tr class="content-row" id="row_<?php echo $field["id"];?>" onClick="display_row_select('row_<?php echo $field["id"];?>', 'chk_<?php echo $field["id"];?>')" onMouseOver="display_row_hover('row_<?php echo $field["id"];?>')" onMouseOut="display_row_clear('row_<?php echo $field["id"];?>')">
					<td class="content-row">
						<a class="linkEditMain" onClick="display_row_block('row_<?php echo $field["id"];?>')" href="data_queries.php?action=field_edit&id=<?php echo $field["id"];?>&data_query_id=<?php echo $field["data_query_id"];?>"><?php echo $field["name"];?></a>
					</td>
					<td class="content-row">
						<?php echo $field["name_desc"]; ?>
					</td>
					<td class="content-row" align="right">
						<input type='checkbox' style='margin: 0px;' id='chk_<?php echo $field["id"];?>' name='chk_<?php echo $field["id"];?>' title="<?php echo $field["name"];?>">
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr>
				<td class="content-list-empty" colspan="2">
					No input fields found. Remember that <strong>at least one index field</strong> must be defined!
				</td>
			</tr>
			<?php
		}

		?>
		<tr>
			<td class="content-header-sub" colspan="2">
				Output Fields
			</td>
			<td class="content-header-sub" align="right">
				<a class="link-dark-small" href="data_queries.php?action=field_edit&field_type=2&data_query_id=<?php echo $_data_query_id;?>">Add</a>
			</td>
		</tr>
		<?php
		$output_fields = api_data_query_fields_list($_data_query_id, DATA_QUERY_FIELD_TYPE_OUTPUT);

		if (sizeof($output_fields) > 0) {
			foreach ($output_fields as $field) {
				?>
				<tr class="content-row" id="row_<?php echo $field["id"];?>" onClick="display_row_select('row_<?php echo $field["id"];?>', 'chk_<?php echo $field["id"];?>')" onMouseOver="display_row_hover('row_<?php echo $field["id"];?>')" onMouseOut="display_row_clear('row_<?php echo $field["id"];?>')">
					<td class="content-row">
						<a class="linkEditMain" onClick="display_row_block('row_<?php echo $field["id"];?>')" href="data_queries.php?action=field_edit&id=<?php echo $field["id"];?>&data_query_id=<?php echo $field["data_query_id"];?>"><?php echo $field["name"];?></a>
					</td>
					<td class="content-row">
						<?php echo $field["name_desc"]; ?>
					</td>
					<td class="content-row" align="right">
						<input type='checkbox' style='margin: 0px;' id='chk_<?php echo $field["id"];?>' name='chk_<?php echo $field["id"];?>' title="<?php echo $field["name"];?>">
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr>
				<td class="content-list-empty" colspan="2">
					No output fields found.
				</td>
			</tr>
			<?php
		}

		html_end_box();
	}

	form_hidden_box("data_query_id", $_data_query_id);

	form_save_button("data_queries.php", "save_data_query");
}

function data_query() {
	global $data_query_input_types;

	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$data_queries = api_data_query_list();

	form_start("data_queries.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Data Queries") . "</strong>", "data_queries.php?action=edit");
	html_header_checkbox(array(_("Name"), _("Input Type")), $box_id);

	if (sizeof($data_queries) > 0) {
		foreach ($data_queries as $data_query) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $data_query["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $data_query["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $data_query["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $data_query["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $data_query["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $data_query["id"];?>')" href="data_queries.php?action=edit&id=<?php echo $data_query["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $data_query["id"];?>"><?php echo $data_query["name"];?></span></a>
				</td>
				<td class="content-row">
					<?php echo $data_query_input_types{$data_query["input_type"]}; ?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $data_query["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $data_query["id"];?>' title="<?php echo $data_query["name"];?>">
				</td>
			</tr>
			<?php
		}

		html_box_toolbar_draw($box_id, "0", "2");
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="2">
				No data queries found.
			</td>
		</tr>
		<?php
	}

	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_end();
	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these data queries?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Data Queries');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these data queries?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Data Queries');
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
