<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

$current_field_row_index = 1;

function field_reset_row_color() {
	global $current_field_row_index, $colors;

	$current_field_row_index = 1;
}

function field_increment_row_color() {
	global $current_field_row_index, $colors;

	if ($current_field_row_index == 1) {
		$current_field_row_index = 2;
		return $colors["form_alternate1"];
	}else{
		$current_field_row_index = 1;
		return $colors["form_alternate2"];
	}
}

function field_get_row_color() {
	global $current_field_row_index, $colors;

	return field_increment_row_color();
}

function field_increment_row_style() {
	global $current_field_row_index;

	if ($current_field_row_index == 1) {
		$current_field_row_index = 2;
		return "field-row-alt1";
	}else{
		$current_field_row_index = 1;
		return "field-row-alt2";
	}
}

function field_get_row_style() {
	global $current_field_row_index;

	return field_increment_row_style();
}

function field_row_header($text) {
	global $colors;
	?>
	<tr bgcolor="<?php echo $colors["header_panel_background"];?>">
		<td colspan="2" class="textSubHeaderDark">
			<?php echo $text;?>
		</td>
	</tr>
	<?php
}

function field_register_error($field_name) {
	if (is_array($field_name)) {
		if (sizeof($field_name) > 0) {
			foreach ($field_name as $_name) {
				$_SESSION["sess_error_fields"][$_name] = 1;
			}

			raise_message(3);
		}
	}else{
		$_SESSION["sess_error_fields"][$field_name] = 1;
		raise_message(3);
	}
}

function field_error_isset($field_name) {
	return isset($_SESSION["sess_error_fields"][$field_name]);
}

function field_register_html_checkboxes(&$field_list, $field_name_format = "|field|") {
	$chk_fields = array();

	if (sizeof($field_list) > 0) {
		foreach (array_keys($field_list) as $field_name) {
			$form_field_name = str_replace("|field|", $field_name, $field_name_format);

			if ((!isset($_POST[$form_field_name])) && (isset($_POST{$form_field_name . "__chk"}))) {
				$chk_fields[$field_name] = "";
			}
		}
	}

	return $chk_fields;
}

/*
 * Standard HTML form elements
 */

/* draw_edit_form - draws an html edit form
   @arg $array - an array that contains all of the information needed to draw
	the html form. see the arrays contained in include/global_settings.php
	for the extact syntax of this array */
function draw_edit_form($array) {
	global $colors;

	//print "<pre>";print_r($array);print "</pre>";

	if (sizeof($array) > 0) {
		while (list($top_branch, $top_children) = each($array)) {
			if ($top_branch == "config") {
				$config_array = $top_children;
			}elseif ($top_branch == "fields") {
				$fields_array = $top_children;
			}
		}
	}

	$i = 0;
	if (sizeof($fields_array) > 0) {
		while (list($field_name, $field_array) = each($fields_array)) {
			if ($i == 0) {
				if (!isset($config_array["no_form_tag"])) {
					echo "<form method='post' action='" . ((isset($config_array["post_to"])) ? $config_array["post_to"] : basename($_SERVER["PHP_SELF"])) . "'" . ((isset($config_array["form_name"])) ? " name='" . $config_array["form_name"] . "'" : "") . ">\n";
				}
			}

			if ($field_array["method"] == "hidden") {
				form_hidden_box($field_name, $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""));
			}elseif ($field_array["method"] == "hidden_zero") {
				form_hidden_box($field_name, $field_array["value"], "0");
			}elseif ($field_array["method"] == "spacer") {
				echo "<tr bgcolor='" . $colors["header_panel_background"] . "'><td colspan='2' class='textSubHeaderDark'>" . $field_array["friendly_name"] . "</td></tr>\n";
			}else{
				/* row color */
				if (isset($config_array["force_row_color"])) {
					echo "<tr bgcolor='#" . $config_array["force_row_color"] . "'>";
				}else{
					form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
				}

				/* column width */
				echo "<td width='" . ((isset($config_array["left_column_width"])) ? $config_array["left_column_width"] : "50%") . "'>\n<font class='textEditTitle'>" . $field_array["friendly_name"] . "</font><br>\n";

				/* sub-field name components */
				if (isset($field_array["sub_template_checkbox"])) {
					form_checkbox($field_array["sub_template_checkbox"]["name"], $field_array["sub_template_checkbox"]["value"], $field_array["sub_template_checkbox"]["friendly_name"], "", ((isset($field_array["form_id"])) ? $field_array["form_id"] : ""), "template_checkbox_status(\"$field_name\",\"" . $field_array["sub_template_checkbox"]["name"] . "\",\"" . $field_array["method"] . "\")");
				}

				/* field description */
				echo ((isset($field_array["description"])) ? $field_array["description"] : "") . "</td>\n";

				echo "<td>";

				if (isset($field_array["preset"])) {
					$field_array["preset"]["js_onchange"] = "document.forms[0].$field_name.value=document.forms[0].preset_$field_name.value";
					echo "<table width='100%' cellspacing='0' cellpadding='0'><tr>";

					/* main form item */
					echo "<td>";
					draw_edit_control($field_name, $field_array);
					echo "</td>";

					/* special handling for drop_array's so we can add a 'none' option that clears the
					 * preset value */
					if (($field_array["preset"]["method"] == "drop_array") || ($field_array["preset"]["method"] == "drop_color")) {
						$_arr = array();

						/* convert sql-keyed array into array("id" => "value") style */
						if (isset($field_array["preset"]["sql"])) {
							$_arr = array_rekey(db_fetch_assoc($field_array["preset"]["sql"]), "id", "name");
						}

						/* add a "None" option that clears the preset */
						$field_array["preset"]["array"][""] = "(None)";
						$field_array["preset"]["array"] += $_arr;
					}

					/* preset form item */
					echo "<td align='right'>";
					draw_edit_control("preset_$field_name", $field_array["preset"]);
					echo "<span style='font-weight: bold; color: #c34138; font-size: 14px;' title='Preset Selection'>*</span\n";
					echo "</td>";

					echo "</tr></table>\n";
				}else{
					draw_edit_control($field_name, $field_array);
				}

				echo "</td>\n</tr>\n";

				$i++;
			}

			if ($i == sizeof($fields_array)) {
				//print "</form>";
			}
		}
	}
}

/* draw_edit_control - draws a single control to be used on an html edit form
   @arg $field_name - the name of the control
   @arg $field_array - an array containing data for this control. see include/global_form.php
	for more specific syntax */
function draw_edit_control($field_name, &$field_array) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");

	switch ($field_array["method"]) {
	case 'textbox':
		form_text_box($field_name, $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""), $field_array["max_length"], ((isset($field_array["size"])) ? $field_array["size"] : "40"), "text", ((isset($field_array["form_id"])) ? $field_array["form_id"] : ""));
		break;
	case 'textbox_password':
		form_text_box($field_name, $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""), $field_array["max_length"], ((isset($field_array["size"])) ? $field_array["size"] : "40"), "password");
		print "<br>";
		form_text_box($field_name . "_confirm", $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""), $field_array["max_length"], ((isset($field_array["size"])) ? $field_array["size"] : "40"), "password");
		break;
	case 'textbox_password_single':
		form_text_box($field_name, $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""), $field_array["max_length"], ((isset($field_array["size"])) ? $field_array["size"] : "40"), "password");
		print "<br>";
		break;
	case 'textbox_sv':
		form_text_box_sv($field_name, $field_array["value"], $field_array["url_moveup"], $field_array["url_movedown"], $field_array["url_delete"], $field_array["url_add"], ((isset($field_array["force_blank_field"])) ? $field_array["force_blank_field"] : false), ((isset($field_array["max_length"])) ? $field_array["max_length"] : "255"), ((isset($field_array["size"])) ? $field_array["size"] : "40"));
		print "<input type='hidden' name='cacti_js_dropdown_redirect_x' value='' id='cacti_js_dropdown_redirect_x'>\n";
		break;
	case 'textarea':
		form_text_area($field_name, $field_array["value"], $field_array["textarea_rows"], $field_array["textarea_cols"], ((isset($field_array["default"])) ? $field_array["default"] : ""));
		break;
	case 'drop_array':
		form_dropdown($field_name, $field_array["array"], "", "", $field_array["value"], ((isset($field_array["none_value"])) ? $field_array["none_value"] : ""), ((isset($field_array["default"])) ? $field_array["default"] : ""), "", ((isset($field_array["trim_length"])) ? $field_array["trim_length"] : "0"), ((isset($field_array["js_onchange"])) ? $field_array["js_onchange"] : ""));
		break;
	case 'drop_array_js':
		form_dropdown($field_name, $field_array["array"], "", "", $field_array["value"], ((isset($field_array["none_value"])) ? $field_array["none_value"] : ""), ((isset($field_array["default"])) ? $field_array["default"] : ""), "", ((isset($field_array["trim_length"])) ? $field_array["trim_length"] : "0"), "submit_redirect(\"" . $field_array["form_index"] . "\", \"" . htmlspecialchars($field_array["redirect_url"]) . "\", document.forms[" . $field_array["form_index"] . "].$field_name.options[document.forms[" . $field_array["form_index"] . "].$field_name.selectedIndex].value)");
		print "<input type='hidden' name='cacti_js_dropdown_redirect_x' value='' id='cacti_js_dropdown_redirect_x'>\n";
		break;
	case 'drop_sql':
		form_dropdown($field_name, db_fetch_assoc($field_array["sql"]), "name", "id", $field_array["value"], ((isset($field_array["none_value"])) ? $field_array["none_value"] : ""), ((isset($field_array["default"])) ? $field_array["default"] : ""), "", ((isset($field_array["trim_length"])) ? $field_array["trim_length"] : "0"), ((isset($field_array["js_onchange"])) ? $field_array["js_onchange"] : ""));
		break;
	case 'drop_multi':
		form_multi_dropdown($field_name, $field_array["array"], db_fetch_assoc($field_array["sql"]), "id");
		break;
	case 'drop_multi_rra':
		form_multi_dropdown($field_name, array_rekey(db_fetch_assoc("select id,name from rra order by timespan"), "id", "name"), (empty($field_array["form_id"]) ? db_fetch_assoc($field_array["sql_all"]) : db_fetch_assoc($field_array["sql"])), "id");
		break;
	case 'drop_tree':
		grow_dropdown_tree($field_array["tree_id"], $field_name, $field_array["value"]);
		break;
	case 'drop_color':
		form_color_dropdown($field_name, $field_array["array"], $field_array["value"], ((isset($field_array["none_value"])) ? $field_array["none_value"] : ""), ((isset($field_array["default"])) ? $field_array["default"] : ""), ((isset($field_array["js_onchange"])) ? $field_array["js_onchange"] : ""));
		break;
	case 'checkbox':
		form_checkbox($field_name, $field_array["value"], $field_array["friendly_name"], ((isset($field_array["default"])) ? $field_array["default"] : ""), ((isset($field_array["form_id"])) ? $field_array["form_id"] : ""), ((isset($field_array["js_onclick"])) ? $field_array["js_onclick"] : ""));
		break;
	case 'checkbox_group':
		while (list($check_name, $check_array) = each($field_array["items"])) {
			form_checkbox($check_name, $check_array["value"], $check_array["friendly_name"], ((isset($check_array["default"])) ? $check_array["default"] : ""), ((isset($check_array["form_id"])) ? $check_array["form_id"] : ""));
			print "<br>";
		}
		break;
	case 'radio':
		while (list($radio_index, $radio_array) = each($field_array["items"])) {
			form_radio_button($field_name, $field_array["value"], $radio_array["radio_value"], $radio_array["radio_caption"], ((isset($field_array["default"])) ? $field_array["default"] : ""));
			print "<br>";
		}
		break;
	case 'custom':
		print $field_array["value"];
		break;
	case 'template_checkbox':
		print "<em>" . html_boolean_friendly($field_array["value"]) . "</em>";
		form_hidden_box($field_name, $field_array["value"], "");
		break;
	case 'template_drop_array':
		print "<em>" . $field_array["array"]{$field_array["value"]} . "</em>";
		form_hidden_box($field_name, $field_array["value"], "");
		break;
	case 'template_drop_multi_rra':
		$items = db_fetch_assoc($field_array["sql_print"]);

		if (sizeof($items) > 0) {
		foreach ($items as $item) {
			print $item["name"] . "<br>";
		}
		}
		break;
	default:
		print "<em>" . $field_array["value"] . "</em>";
		form_hidden_box($field_name, $field_array["value"], "");
		break;
	}
}

/* form_text_box - draws a standard html textbox
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element
   @arg $form_default_value - the value of this form element to use if there is
	no current value available
   @arg $form_max_length - the maximum number of characters that can be entered
	into this textbox
   @arg $form_size - the size (width) of the textbox
   @arg $type - the type of textbox, either 'text' or 'password'
   @arg $current_id - used to determine if a current value for this form element
	exists or not. a $current_id of '0' indicates that no current value exists,
	a non-zero value indicates that a current value does exist */
function form_text_box($field_name, $form_previous_value, $form_default_value, $form_max_length, $form_size = 30, $type = "text", $current_id = 0, $css_class = "") {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	echo "<input type='$type'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$field_name])) {
			echo " style='border: 2px solid red;'";
			unset($_SESSION["sess_error_fields"][$field_name]);
		}
	}

	/* always use the cached value if it's available */
	if (isset_post_cache_field($field_name)) {
		$form_previous_value = get_post_cache_field($field_name);
	}

	echo " name='$field_name' id='$field_name' size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . (!empty($css_class) ? " class='$css_class'" : "") . " value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>\n";
}

function form_text_box_sv($field_name, $values_array, $url_moveup, $url_movedown, $url_delete, $url_add, $force_blank_field = false, $form_max_length, $form_size = 30) {
	?>
	<table cellpadding='1' cellspacing='0' width='100%'>
		<?php
		$i = 1;
		while (list($id, $value) = each($values_array)) {
			?>
			<tr>
				<td>
					<strong>[<?php echo $i;?>]</strong>&nbsp;
					<?php form_text_box("sv|$field_name|$id", $value, "", $form_max_length, $form_size, "text", 0);?>
					<?php
					if (sizeof($values_array) > 1) {
						?>
						<a href="<?php echo str_replace("|id|", $id, $url_movedown);?>"><img src="<?php echo html_get_theme_images_path('move_down.gif');?>" border="0" alt="<?php echo _('Move Down');?>"></a>
						<a href="<?php echo str_replace("|id|", $id, $url_moveup);?>"><img src="<?php echo html_get_theme_images_path('move_up.gif');?>" border="0" alt="<?php echo _('Move Up');?>"></a>
						<?php
					}
					?>
				</td>
				<td align="right">
					<?php
					if (sizeof($values_array) > 1) {
						?>
						<a href="<?php echo str_replace("|id|", $id, $url_delete);?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="<?php echo _('Delete');?>"></a>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
			$i++;
		}

		if ((sizeof($values_array) == 0) || ($force_blank_field == true)) {
			?>
			<tr>
				<td>
					<?php echo ((sizeof($values_array) > 0) ? "<strong>[$i]</strong>&nbsp;" : "");?>
					<?php form_text_box("sv|$field_name|0", "", "", $form_max_length, $form_size, "text", 0);?>
					<br>
				</td>
			</tr>
			<?php
		}

		if (sizeof($values_array) > 0) {
			?>
			<tr>
				<td align="right" style="border-top: 1px dashed gray; padding: 2px; font-size: 12px;" colspan="2">
					<strong><a href="<?php echo $url_add;?>">Add New Field</a</strong>
				</td>
			</tr>
		<?php
		}
		?>
	</table>
	<?php
}

/* form_hidden_box - draws a standard html hidden element
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element
   @arg $form_default_value - the value of this form element to use if there is
	no current value available */
function form_hidden_box($field_name, $form_previous_value, $form_default_value = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	print "<input type='hidden' name='$field_name' id='$field_name' value='$form_previous_value'>\n";
}

/* form_dropdown - draws a standard html dropdown box
   @arg $field_name - the name of this form element
   @arg $form_data - an array containing data for this dropdown. it can be formatted
	in one of two ways:
	$array["id"] = "value";
	-- or --
	$array[0]["id"] = 43;
	$array[0]["name"] = "Red";
   @arg $column_display - used to indentify the key to be used for display data. this
	is only applicable if the array is formatted using the second method above
   @arg $column_id - used to indentify the key to be used for id data. this
	is only applicable if the array is formatted using the second method above
   @arg $form_previous_value - the current value of this form element
   @arg $form_none_entry - the name to use for a default 'none' element in the dropdown
   @arg $form_default_value - the value of this form element to use if there is
	no current value available
   @arg $css_style - any css that needs to be applied to this form element */
function form_dropdown($field_name, $form_data, $column_display, $column_id, $form_previous_value, $form_none_entry, $form_default_value, $css_style = "", $trim_display_length = 0, $js_onchange = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	/* always use the cached value if it's available */
	if (isset_post_cache_field($field_name)) {
		$form_previous_value = get_post_cache_field($field_name);
	}

	print "<select name='$field_name' id='$field_name' style='$css_style'" . ($js_onchange == "" ? "" : " onChange='$js_onchange'") . ">";

	if (!empty($form_none_entry)) {
		print "<option value='0'" . (empty($form_previous_value) ? " selected" : "") . ">$form_none_entry</option>\n";
	}

	html_create_list($form_data, $column_display, $column_id, $form_previous_value, $trim_display_length);

	print "</select>\n";
}

/* form_text_box - draws a standard html checkbox
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element
   @arg $form_caption - the text to display to the right of the checkbox
   @arg $form_default_value - the value of this form element to use if there is
	no current value available
   @arg $current_id - used to determine if a current value for this form element
	exists or not. a $current_id of '0' indicates that no current value exists,
	a non-zero value indicates that a current value does exist */
function form_checkbox($field_name, $form_previous_value, $form_caption, $form_default_value, $current_id = 0, $js_onclick = "") {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	/* always use the cached value if it's available */
	if (isset_post_cache_field($field_name)) {
		$form_previous_value = get_post_cache_field($field_name);
	}

	print "<input type='checkbox' name='$field_name' id='$field_name'" . ($js_onclick == "" ? "" : " onClick='$js_onclick'") . ((($form_previous_value == "on") || ($form_previous_value == "1")) ? " checked" : "") . "> <span class='txtEnabledText' id='chk_caption_$field_name'>$form_caption</span>\n";
}

function form_checkbox_marker($field_name) {
	/* this is used to detect the presence of a checkbox when the user POST's an unchecked box */
	form_hidden_box($field_name . "__chk", "1", "");
}

/* form_text_box - draws a standard html radio button
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element (selected or not)
   @arg $form_current_value - the current value of this form element (element id)
   @arg $form_caption - the text to display to the right of the checkbox
   @arg $form_default_value - the value of this form element to use if there is
	no current value available */
function form_radio_button($field_name, $form_previous_value, $form_current_value, $form_caption, $form_default_value, $js_onclick = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	/* always use the cached value if it's available */
	if (isset_post_cache_field($field_name)) {
		$form_previous_value = get_post_cache_field($field_name);
	}

	print "<input type='radio' name='$field_name' id='$field_name" . "_$form_current_value' value='$form_current_value'" . ($js_onclick == "" ? "" : " onClick='$js_onclick'") . (($form_previous_value == $form_current_value) ? " checked" : "") . "> $form_caption\n";
}

/* form_text_box - draws a standard html text area box
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element (selected or not)
   @arg $form_rows - the number of rows in the text area box
   @arg $form_columns - the number of columns in the text area box
   @arg $form_default_value - the value of this form element to use if there is
	no current value available */
function form_text_area($field_name, $form_previous_value, $form_rows, $form_columns, $form_default_value) {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	/* always use the cached value if it's available */
	if (isset_post_cache_field($field_name)) {
		$form_previous_value = get_post_cache_field($field_name);
	}

	print "<textarea cols='$form_columns' id='$field_name' rows='$form_rows' name='$field_name'>" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "</textarea>\n";
}

/* form_multi_dropdown - draws a standard html multiple select dropdown
   @arg $field_name - the name of this form element
   @arg $array_display - an array containing display values for this dropdown. it must
	be formatted like:
	$array[id] = display;
   @arg $sql_previous_values - an array containing keys that should be marked as selected.
	it must be formatted like:
	$array[0][$column_id] = key
   @arg $column_id - the name of the key used to reference the keys above */
function form_multi_dropdown($field_name, $array_display, $sql_previous_values, $column_id) {
	print "<select name='$field_name" . "[]' id='$field_name" . "[]' multiple>\n";

	foreach (array_keys($array_display) as $id) {
		print "<option value='" . $id . "'";

		for ($i=0; ($i < count($sql_previous_values)); $i++) {
			if ($sql_previous_values[$i][$column_id] == $id) {
				print " selected";
			}
		}

		print ">". $array_display[$id];
		print "</option>\n";
	}

	print "</select>\n";
}

/*
 * Second level form elements
 */

/* form_color_dropdown - draws a dropdown containing a list of colors that uses a bit
	of css magic to make the dropdown item background color represent each color in
	the list
   @arg $field_name - the name of this form element
   @arg $form_previous_value - the current value of this form element
   @arg $form_none_entry - the name to use for a default 'none' element in the dropdown
   @arg $form_default_value - the value of this form element to use if there is
	no current value available */
function form_color_dropdown($field_name, $form_data, $form_previous_value, $form_none_entry, $form_default_value, $js_onchange = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	print "<select name='$field_name'" . ($js_onchange == "" ? "" : " onChange='$js_onchange'") . ">\n";

	if ($form_none_entry != "") {
		print "<option value='0'>$form_none_entry</option>\n";
	}

	while (list($id, $hex) = each($form_data)) {
		print "<option style='background: #" . $hex . ";' value='" . $id . "'";

		if ($form_previous_value == $hex) {
			print " selected";
		}

		print ">" . $hex . "</option>\n";
	}

	print "</select>\n";
}

/* form_confirm - draws a table presenting the user with some choice and allowing
	them to either proceed (delete) or cancel
   @arg $body_text - the text to prompt the user with on this form
   @arg $cancel_url - the url to go to when the user clicks 'cancel'
   @arg $action_url - the url to go to when the user clicks 'delete' */
function form_confirm($title_text, $body_text, $cancel_url, $action_url) { ?>
		<br>
		<table align="center" cellpadding=1 cellspacing=0 border=0 bgcolor="#B61D22" width="60%">
			<tr>
				<td bgcolor="#B61D22" colspan="10">
					<table width="100%" cellpadding="3" cellspacing="0">
						<tr>
							<td bgcolor="#B61D22" class="textHeaderDark"><strong><?php print $title_text;?></strong></td>
						</tr>
						<?php
						form_area($body_text);
						form_confirm_buttons($action_url, $cancel_url);
						?>
					</table>
				</td>
			</tr>
		</table>

<?php }

/* form_message - draws a table presenting the user with a message and an Ok button.
   @arg $body_text - the text to prompt the user with on this form
   @arg $ok_url - the url to go to when the user clicks 'ok' */
function form_message($title_text, $body_text, $ok_url) { ?>
		<br>
		<table align="center" cellpadding=1 cellspacing=0 border=0 bgcolor="#B61D22" width="60%">
			<tr>
				<td bgcolor="#B61D22" colspan="10">
					<table width="100%" cellpadding="3" cellspacing="0">
						<tr>
							<td bgcolor="#B61D22" class="textHeaderDark"><strong><?php print $title_text;?></strong></td>
						</tr>
						<?php	form_area($body_text); ?>
						<tr>
							<td bgcolor="#E1E1E1">
								<a href="<?php print $ok_url;?>"><img src="<?php print html_get_theme_images_path('button_ok.gif');?>" border="0" alt="<?php echo _('Ok');?>" align="absmiddle"></a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

<?php }

/* form_confirm_buttons - draws a cancel and delete button suitable for display
	on a confirmation form
   @arg $cancel_url - the url to go to when the user clicks 'cancel'
   @arg $action_url - the url to go to when the user clicks 'delete' */
function form_confirm_buttons($action_url, $cancel_url) { global $colors; ?>
	<tr>
		<td bgcolor="#<?php print $colors['buttonbar_background'];?>">
			<a href="<?php print $cancel_url;?>"><img src="<?php print html_get_theme_images_path('button_cancel.gif');?>" border="0" alt="Cancel" align="absmiddle"></a>
			<a href="<?php print $action_url . "&confirm=yes";?>"><img src="<?php print html_get_theme_images_path('button_delete.gif');?>" border="0" alt="Delete" align="absmiddle"></a>
		</td>
	</tr>
<?php }

/* form_save_button - draws a (save|create) and cancel button at the bottom of
	an html edit form
   @arg $cancel_url - the url to go to when the user clicks 'cancel'
   @arg $force_type - if specified, will force the 'action' button to be either
	'save' or 'create'. otherwise this field should be properly auto-detected */
function form_save_button($cancel_url, $button_name = "", $force_type = "") {
	global $colors;

	if (empty($force_type)) {
		if (!isset($_GET["id"])) {
			$img = "button_create.gif";
			$alt = "Create";
		}else{
			$img = "button_save.gif";
			$alt = "Save";
		}
	}elseif ($force_type == "save") {
		$img = "button_save.gif";
		$alt = "Save";
	}elseif ($force_type == "create") {
		$img = "button_create.gif";
		$alt = "Create";
	}
	?>
	<table align='center' width='98%' cellpadding="3">
		<tr>
			<td style="border: 1px solid gray; background-color: #ffffff;" align="right">
				<a href='<?php echo $cancel_url;?>'><img src='<?php echo html_get_theme_images_path("button_cancel2.gif");?>' alt='<?php echo _("Cancel");?>' align='absmiddle' border='0'></a>
				<input type='image'<?php echo ($button_name == "" ? "" : " name='$button_name'");?> src='<?php echo html_get_theme_images_path($img);?>' alt='<?php echo $alt;?>' align='absmiddle'>
			</td>
		</tr>
	</table>
	<input type='hidden' name='action' value='save'>
	</form>
	<?php
}

function form_start($action, $name = "", $enc_multipart = false)
{
	echo "<form action='$action' method='post'" . ($name == "" ? "" : " name='$name' id='$name'") . ($enc_multipart == true ? " enctype='multipart/form-data'" : "") . ">\n";
}

function form_end()
{
	echo "</form>\n";
}

?>
