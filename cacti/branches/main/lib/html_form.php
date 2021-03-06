<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

/*
 * Standard HTML form elements
 */

/** draw_edit_form - draws an html edit form
   @param array $array - an array that contains all of the information needed to draw
     the html form. see the arrays contained in include/global_settings.php
     for the extact syntax of this array */
function draw_edit_form($array) {
	global $colors;

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
					print "<form method='post' action='" . ((isset($config_array["post_to"])) ? $config_array["post_to"] : basename($_SERVER["PHP_SELF"])) . "'" . ((isset($config_array["form_name"])) ? " name='" . $config_array["form_name"] . "'" : "") . ">\n";
				}
			}

			if ($field_array["method"] == "hidden") { /* TODO: input type=hidden is not allowed inside a <table> but outside e.g. a <td> */
				form_hidden_box($field_name, $field_array["value"], ((isset($field_array["default"])) ? $field_array["default"] : ""));
			}elseif ($field_array["method"] == "hidden_zero") {
				form_hidden_box($field_name, $field_array["value"], "0");
			}elseif ($field_array["method"] == "spacer") {
				print "<tr id='row_$field_name'><td colspan='2' class='textRowSubHeaderDark'>" . $field_array["friendly_name"] . "</td></tr>\n";
			}else{
				if (isset($config_array["force_row_color"])) {
					print "<tr id='row_$field_name' bgcolor='#" . $config_array["force_row_color"] . "'>";
				}else{
					form_alternate_row_color('row_' . $field_name);
				}

				if (isset($field_array["sub_checkbox"])) {
					/* print description as a hover */
					$width = ((isset($config_array["left_column_width"])) ? (" width='" . $config_array["left_column_width"] . "'") : "");
					print "<td" . $width . " class='template_checkbox'>\n";
					print "<font class='textEditTitle'>" . $field_array["friendly_name"] . "</font><br>\n";

					if (isset($field_array["description"])) {
						if (strlen($field_array["description"])) {
							print "<div>" . $field_array["description"] . "</div>";
						}
					}

					form_checkbox($field_array["sub_checkbox"]["name"],
						$field_array["sub_checkbox"]["value"],
						$field_array["sub_checkbox"]["friendly_name"],
						((isset($field_array["default"])) ? $field_array["default"] : ""),
						((isset($field_array["form_id"])) ? $field_array["form_id"] : ""),
						((isset($field_array["class"])) ? $field_array["class"] : ""),
						((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));
					print "</td>\n";
				} else {
					$width = ((isset($config_array["left_column_width"])) ? (" width='" . $config_array["left_column_width"] . "'") : "");
					print "<td" . $width . ">\n";
					print "<font class='textEditTitle'>" . $field_array["friendly_name"] . "</font><br>\n";
					print ((isset($field_array["description"])) ? $field_array["description"] : "");
					print "</td>\n";
				}

				print "<td>";

				draw_edit_control($field_name, $field_array);

				print "</td>\n</tr>\n";
			}

			$i++;
		}
	}
}

/** draw_edit_control - draws a single control to be used on an html edit form
   @param string $field_name - the name of the control
   @param array $field_array - an array containing data for this control. see include/global_form.php
     for more specific syntax */
function draw_edit_control($field_name, &$field_array) {
	switch ($field_array["method"]) {
	case 'textbox':
		form_text_box($field_name, $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "text",
			((isset($field_array["form_id"])) ? $field_array["form_id"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'filepath':
		form_filepath_box($field_name, $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "text",
			((isset($field_array["form_id"])) ? $field_array["form_id"] : ""));

		break;
	case 'dirpath':
		form_dirpath_box($field_name, $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "text",
			((isset($field_array["form_id"])) ? $field_array["form_id"] : ""));

		break;
	case 'textbox_password':
		form_text_box($field_name, $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "password");

		print "<br>";

		form_text_box($field_name . "_confirm", $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "password");

		break;
	case 'textarea':
		form_text_area($field_name, $field_array["value"], $field_array["textarea_rows"],
			$field_array["textarea_cols"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_array':
		form_dropdown($field_name, $field_array["array"], "", "", $field_array["value"],
			((isset($field_array["none_value"])) ? $field_array["none_value"] : ""),
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_sql':
		form_dropdown($field_name,
			db_fetch_assoc($field_array["sql"]), "name", "id", $field_array["value"],
			((isset($field_array["none_value"])) ? $field_array["none_value"] : ""),
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_image':
		form_dropdown_image($field_name,
			$field_array["path"], $field_array["value"], $field_array["default"], (isset($field_array["width"]) ? $field_array["width"]: ""));

		break;
	case 'drop_sqlcb':
		form_dropdown_cb($field_name,
			$field_array["sql"], $field_array["sql_id"], $field_array["value"],
			((isset($field_array["text_value"])) ? $field_array["text_value"] : ""),
			((isset($field_array["none_value"])) ? $field_array["none_value"] : "0"),
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_multi':
		form_multi_dropdown($field_name, $field_array["array"], db_fetch_assoc($field_array["sql"]), "id",
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_multi_rra':
		form_multi_dropdown($field_name, array_rekey(db_fetch_assoc("select id,name from rra order by timespan"), "id", "name"),
			(empty($field_array["form_id"]) ? db_fetch_assoc($field_array["sql_all"]) : db_fetch_assoc($field_array["sql"])), "id",
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'drop_tree':
		grow_dropdown_tree($field_array["tree_id"], $field_name, $field_array["value"]);

		break;
	case 'drop_color':
		form_color_dropdown($field_name, $field_array["value"], "None",
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'checkbox':
		form_checkbox($field_name,
			$field_array["value"],
			$field_array["friendly_name"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			((isset($field_array["form_id"])) ? $field_array["form_id"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	case 'checkbox_group':
		while (list($check_name, $check_array) = each($field_array["items"])) {
			form_checkbox($check_name,
				$check_array["value"],
				$check_array["friendly_name"],
				((isset($check_array["default"])) ? $check_array["default"] : ""),
				((isset($check_array["form_id"])) ? $check_array["form_id"] : ""),
				((isset($field_array["class"])) ? $field_array["class"] : ""),
				((isset($check_array["on_change"])) ? $check_array["on_change"] : (((isset($field_array["on_change"])) ? $field_array["on_change"] : ""))));

			print "<br>";
		}

		break;
	case 'radio':
		while (list($radio_index, $radio_array) = each($field_array["items"])) {
			form_radio_button($field_name, $field_array["value"], $radio_array["radio_value"], $radio_array["radio_caption"],
				((isset($field_array["default"])) ? $field_array["default"] : ""),
				((isset($field_array["class"])) ? $field_array["class"] : ""),
				((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

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
			print htmlspecialchars($item["name"],ENT_QUOTES) . "<br>";
		}
		}

		break;
	case 'autocomplete':
		form_autocomplete_box($field_name,
			$field_array["callback_function"], $field_array["id"],
				((isset($field_array["name"])) ? $field_array["name"] : ""),
				((isset($field_array["size"])) ? $field_array["size"] : "40"),
				((isset($field_array["max_length"])) ? $field_array["max_length"] : ""));
		break;
	case 'font':
		form_font_box($field_name, $field_array["value"],
			((isset($field_array["default"])) ? $field_array["default"] : ""),
			$field_array["max_length"],
			((isset($field_array["size"])) ? $field_array["size"] : "40"), "text",
			((isset($field_array["form_id"])) ? $field_array["form_id"] : ""),
			((isset($field_array["class"])) ? $field_array["class"] : ""),
			((isset($field_array["on_change"])) ? $field_array["on_change"] : ""));

		break;
	default:
		print "<em>" . htmlspecialchars($field_array["value"],ENT_QUOTES) . "</em>";

		form_hidden_box($field_name, $field_array["value"], "");

		break;
	}
}

/** form_filepath_box - draws a standard html textbox and provides status of a files existence
   @param string$form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param int $form_max_length - the maximum number of characters that can be entered
     into this textbox
   @param int $form_size - the size (width) of the textbox
   @param string $type - the type of textbox, either 'text' or 'password'
   @param int $current_id - used to determine if a current value for this form element
     exists or not. a $current_id of '0' indicates that no current value exists,
     a non-zero value indicates that a current value does exist */
function form_filepath_box($form_name, $form_previous_value, $form_default_value, $form_max_length, $form_size = 30, $type = "text", $current_id = 0) {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	print "<input type='$type'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			print "class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (is_file($form_previous_value)) {
		$extra_data = "<span class=\"success\"><br>[" . __("OK: FILE FOUND") . "]</span>";
	}else if (is_dir($form_previous_value)) {
		$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: IS DIR") . "]</span>";
	}else if (strlen($form_previous_value) == 0) {
		$extra_data = "";
	}else{
		$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: FILE NOT FOUND") . "]</span>";
	}

	print " id='$form_name' name='$form_name' size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . " value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>" . $extra_data;
}

/** form_dirpath_box - draws a standard html textbox and provides status of a directories existence
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param int $form_max_length - the maximum number of characters that can be entered
     into this textbox
   @param int $form_size - the size (width) of the textbox
   @param string $type - the type of textbox, either 'text' or 'password'
   @param int $current_id - used to determine if a current value for this form element
     exists or not. a $current_id of '0' indicates that no current value exists,
     a non-zero value indicates that a current value does exist */
function form_dirpath_box($form_name, $form_previous_value, $form_default_value, $form_max_length, $form_size = 30, $type = "text", $current_id = 0) {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	print "<input type='$type'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			print "class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (is_dir($form_previous_value)) {
		$extra_data = "<span class=\"success\"><br>[" . __("OK: DIR FOUND") . "]</span>";
	}else if (is_file($form_previous_value)) {
		$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: IS FILE") . "]</span>";
	}else if (strlen($form_previous_value) == 0) {
		$extra_data = "";
	}else{
		$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: DIR NOT FOUND") . "]</span>";
	}

	print " id='$form_name' name='$form_name' size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . " value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>" . $extra_data;
}

/** form_text_box - draws a standard html textbox
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param int $form_max_length - the maximum number of characters that can be entered
     into this textbox
   @param int $form_size - the size (width) of the textbox
   @param string $type - the type of textbox, either 'text' or 'password'
   @param int $current_id - used to determine if a current value for this form element
     exists or not. a $current_id of '0' indicates that no current value exists,
     a non-zero value indicates that a current value does exist
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
   function form_text_box($form_name, $form_previous_value, $form_default_value, $form_max_length, $form_size = 30, $type = "text", $current_id = 0, $class = "", $on_change = "") {
   	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	print "<input type='$type'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			print " class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	print " id='$form_name' name='$form_name' " . $on_change . $class ." size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . " value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>\n";
}

/** form_autocomplete_box - draws a standard html textbox as an autocomplete type
   @param string $form_name  - the name of this form element
   @param string $callback_function - the function that primes the field
   @param int $id - the key for this textbox
   @param string $name - what should be displayed to the user
   @param int $form_size - the size of the text box
   @param int $form_max_length - the maximum number of text to allow */
function form_autocomplete_box($form_name, $callback_function, $id, $name, $form_size = "40", $form_max_length = "") {
	$display_id = $form_name . "_display";

	print '<script  type="text/javascript">
	$().ready(function() {
		$("#' . $display_id . '").autocomplete("' . $callback_function . '", { max: 8, highlight: false, scroll: true, scrollHeight: 300 });
		$("#' . $display_id . '").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#' . $form_name . '").val(data[1]);
			}
		});
	});
	</script>';

	print "<input class='ac_field' type='text'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			print "class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	print " id='${form_name}_display' size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . " value='" . htmlspecialchars($name, ENT_QUOTES) . "'>\n";
	print "<div><input type='hidden' id='$form_name' name='$form_name' value='$id'></div>";
}

/** form_hidden_box - draws a standard html hidden element
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_default_value - the value of this form element to use if there is no current value available */
function form_hidden_box($form_name, $form_previous_value, $form_default_value, $echo = false) {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	if ($echo) { /* TODO: both times same action??? */
		print "<input type='hidden' id='$form_name' name='$form_name' value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>\n";
	}else{
		print "<input type='hidden' id='$form_name' name='$form_name' value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>\n";
	}
}

/** form_dropdown - draws a standard html dropdown box
   @param string $form_name - the name of this form element
   @param array $form_data - an array containing data for this dropdown. it can be formatted
     in one of two ways:
     $array["id"] = "value";
     -- or --
     $array[0]["id"] = 43;
     $array[0]["name"] = "Red";
   @param string $column_display - used to indentify the key to be used for display data. this
     is only applicable if the array is formatted using the second method above
   @param int $column_id - used to indentify the key to be used for id data. this
     is only applicable if the array is formatted using the second method above
   @param string $form_previous_value - the current value of this form element
   @param string $form_none_entry - the name to use for a default 'none' element in the dropdown
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param string $class - any css that needs to be applied to this form element
   @param string $on_change - onChange modifier */
function form_dropdown($form_name, $form_data, $column_display, $column_id, $form_previous_value, $form_none_entry, $form_default_value, $class = "", $on_change = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			$class .= " class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	print "<select id='$form_name' name='$form_name'" . $class . $on_change . ">";

	if (!empty($form_none_entry)) {
		print "<option value='0'" . (empty($form_previous_value) ? " selected" : "") . ">$form_none_entry</option>\n";
	}

	html_create_list($form_data, $column_display, $column_id, htmlspecialchars($form_previous_value, ENT_QUOTES));

	print "</select>\n";
}

/** form_dropdown_image
 *
 * @param string $form_name
 * @param string $form_path
 * @param string $form_previous_value
 * @param string $form_default_value
 * @param int $form_width
 */
function form_dropdown_image($form_name, $form_path, $form_previous_value, $form_default_value, $form_width = "120") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_path . "/" . $form_default_value;
	}
	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	print "<select id='$form_name' style='width:" . $form_width . "px;' name='$form_name'>";

	$form_none_entry = ucfirst(str_replace("_", " ", str_replace(".gif", "", str_replace(".jpg", "", str_replace(".png", "", $form_default_value)))));

	$path       = CACTI_BASE_PATH . "/". $form_path;
	$imgpath    = CACTI_URL_PATH . $form_path;

	if (!empty($form_none_entry)) {
		print "<option style='width:" . $form_width . "px;' title='" . $form_previous_value . "' value='" . $form_previous_value . "'" . (empty($form_previous_value) ? " selected" : "") . ">&nbsp;$form_none_entry&nbsp;</option>\n";
	}

	$dh = opendir($path);
	/* validate contents of the plugin directory */
	if (is_resource($dh)) {
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != ".." && !is_dir("$path/$file")) {
				if (sizeof(getimagesize($path . "/" . $file))) {
					$title = ucfirst(str_replace("_", " ", str_replace(".gif", "", str_replace(".jpg", "", str_replace(".png", "", $file)))));
					print "<option style='width:" . $form_width . "px;' title='" . $imgpath . "/" . $file . "' value='" . $imgpath . "/" . $file . "'" . (($form_previous_value == ($imgpath . "/" . $file)) ? " selected" : "") . ">&nbsp;" . $title . "&nbsp;</option>\n";
				}
			}
		}
		closedir($dh);
	}

	print "</select>\n";

	?>
	<script type="text/javascript">
	<!--
	$().ready(function(arg) {
		$("#<?php print $form_name;?>").msDropDown();
		$("#designhtml select").msDropDown();
		$("#dynamic").msDropDown();
	});
	//-->
	</script><?php
}

/** form_dropdown_cb - draws an ajax html dropdown box
   @param string $form_name - the name of the form
   @param string $form_sql - the name of this form element
   @param string $form_sql_id - sql syntax for the dropdown array
   @param string $form_previous_value - the current value of this form element
   @param string $form_previous_text - the current text value of this form element
   @param string $form_none_entry - the name to use for a default 'none' element in the dropdown
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param string $on_change - onChange modifier */
function form_dropdown_cb($form_name, $form_sql, $form_sql_id, $form_previous_value, $form_previous_text, $form_none_entry, $form_default_value, $on_change = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
		$form_previous_text  = $form_none_entry;
	}else{
		if ($where_pos = strpos(strtoupper($form_sql), "WHERE")) {
			$new_form_sql = substr($form_sql, 0, $where_pos+5) . " $form_sql_id=" . $form_previous_value . " AND " . substr($form_sql, $where_pos+5);
		}elseif ($orderby_pos = strpos(strtoupper($form_data), "ORDER BY")) {
			$new_form_sql = substr($form_sql, 0, $orderby_pos) . " AND $form_sql_id=" . $form_previous_value . " " . substr($form_sql, $orderby_pos);
		}else{
			$new_form_sql = $form_sql . " AND $form_sql_id=" . $form_previous_value;
		}

		$previous_row = db_fetch_row($new_form_sql);
		$form_previous_text = $previous_row["name"];
	}

	?>
	<script type="text/javascript">
	<!--
	$().ready(function() {
		$("#<?php print $form_name . '_cb';?>").autocomplete("./lib/ajax/get_form_dropdown.php?sql=<?php print base64_encode($form_sql);?>", { max: 24, highlight: false, scroll: true, scrollHeight: 300 });
		$("#<?php print $form_name . '_cb';?>").result(function(event, data, formatted) {
			if (data) {
				$(this).parent().find("#<?php print $form_name;?>").val(data[1]);
			}else{
				$(this).parent().find("#<?php print $form_name;?>").val(0);
			}
		});
	});
	//-->
	</script>
	<input class="ac_field" type="text" id="<?php print $form_name . '_cb';?>" size="70" value="<?php print $form_previous_text; ?>">
	<input type="hidden" id="<?php print $form_name;?>" name="<?php print $form_name;?>" value='<?php print $form_previous_value;?>'>
	<?php
}

/** form_checkbox - draws a standard html checkbox
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_caption - the text to display to the right of the checkbox
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param int $current_id - used to determine if a current value for this form element
     exists or not. a $current_id of '0' indicates that no current value exists,
     a non-zero value indicates that a current value does exist
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_checkbox($form_name, $form_previous_value, $form_caption, $form_default_value, $current_id = 0, $class = "", $on_change = "") {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($class)) {
		$class = " class='$class'";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change'";
	}

	if ($form_previous_value == CHECKED) {
		$checked = " checked";
	}else{
		$checked = "";
	}

	print "<input type='checkbox' id='$form_name' name='$form_name'" . $on_change . $class . $checked . "> <label for='$form_name'>$form_caption</label>\n";
}

/** form_radio_button - draws a standard html radio button
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element (selected or not)
   @param string $form_current_value - the current value of this form element (element id)
   @param string $form_caption - the text to display to the right of the checkbox
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_radio_button($form_name, $form_previous_value, $form_current_value, $form_caption, $form_default_value, $class = "", $on_change = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	if ($form_previous_value == $form_current_value) {
		$checked = " checked";
	}else{
		$checked = "";
	}

	$css_id = $form_name . "_" . $form_current_value;

	print "<input type='radio' id='$css_id' name='$form_name' value='$form_current_value'" . $class . $on_change . $checked . "><label for='$css_id'>$form_caption</label>\n";
}

/** form_text_area - draws a standard html text area box
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element (selected or not)
   @param string $form_rows - the number of rows in the text area box
   @param string $form_columns - the number of columns in the text area box
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_text_area($form_name, $form_previous_value, $form_rows, $form_columns, $form_default_value, $class = "", $on_change = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	print "<textarea cols='$form_columns' rows='$form_rows' id='$form_name' name='$form_name'" . $class . $on_change . ">" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "</textarea>\n";
}

/** form_multi_dropdown - draws a standard html multiple select dropdown
   @param string $form_name - the name of this form element
   @param array $array_display - an array containing display values for this dropdown. it must
     be formatted like:
     $array[id] = display;
   @param array $sql_previous_values - an array containing keys that should be marked as selected.
     it must be formatted like:
     $array[0][$column_id] = key
   @param int $column_id - the name of the key used to reference the keys above
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_multi_dropdown($form_name, $array_display, $sql_previous_values, $column_id, $class = "", $on_change = "") {
	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	print "<select id='$form_name' name='$form_name" . "[]'" . $class . $class . " multiple>\n";

	foreach (array_keys($array_display) as $id) {
		print "<option value='" . $id . "'";

		for ($i=0; ($i < count($sql_previous_values)); $i++) {
			if ($sql_previous_values[$i][$column_id] == $id) {
				print " selected";
			}
		}

		print ">". htmlspecialchars($array_display[$id],ENT_QUOTES);
		print "</option>\n";
	}

	print "</select>\n";
}

/*
 * Second level form elements
 */

/** form_color_dropdown - draws a dropdown containing a list of colors that uses a bit
     of css magic to make the dropdown item background color represent each color in
     the list
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_none_entry - the name to use for a default 'none' element in the dropdown
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_color_dropdown($form_name, $form_previous_value, $form_none_entry, $form_default_value, $class = "", $on_change = "") {
	if ($form_previous_value == "") {
		$form_previous_value = $form_default_value;
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	$current_color = db_fetch_cell("SELECT hex FROM colors WHERE id=$form_previous_value");

	if (strlen($on_change)) {
		$on_change = " " . $on_change . ";";
	}

	$on_change = " onChange='this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor;$on_change'";

	$colors_list = db_fetch_assoc("select id,hex from colors order by hex desc");

	print "<select style='background-color: #$current_color;' id='$form_name' name='$form_name'" . $class . $on_change . ">\n";

	if ($form_none_entry != "") {
		print "<option value='0'>$form_none_entry</option>\n";
	}

	if (sizeof($colors_list) > 0) {
		foreach ($colors_list as $color) {
			print "<option style='background-color: #" . $color["hex"] . ";' value='" . $color["id"] . "'";

			if ($form_previous_value == $color["id"]) {
				print " selected";
			}

			print ">" . $color["hex"] . "</option>\n";
		}
	}

	print "</select>\n";
}

/** form_font_box - draws a standard html textbox and provides status of a fonts existence
   @param string $form_name - the name of this form element
   @param string $form_previous_value - the current value of this form element
   @param string $form_default_value - the value of this form element to use if there is
     no current value available
   @param int $form_max_length - the maximum number of characters that can be entered
     into this textbox
   @param int $form_size - the size (width) of the textbox
   @param string $type - the type of textbox, either 'text' or 'password'
   @param int $current_id - used to determine if a current value for this form element
     exists or not. a $current_id of '0' indicates that no current value exists,
     a non-zero value indicates that a current value does exist
   @param string $class - specify a css class
   @param string $on_change - specify a javascript onchange action */
function form_font_box($form_name, $form_previous_value, $form_default_value, $form_max_length, $form_size = 30, $type = "text", $current_id = 0, $class = "", $on_change = "") {
	if (($form_previous_value == "") && (empty($current_id))) {
		$form_previous_value = $form_default_value;
	}

	print "<input type='$type'";

	if (isset($_SESSION["sess_error_fields"])) {
		if (!empty($_SESSION["sess_error_fields"][$form_name])) {
			print "class='txtErrorTextBox'";
			unset($_SESSION["sess_error_fields"][$form_name]);
		}
	}

	if (isset($_SESSION["sess_field_values"])) {
		if (!empty($_SESSION["sess_field_values"][$form_name])) {
			$form_previous_value = $_SESSION["sess_field_values"][$form_name];
		}
	}

	if (strlen($form_previous_value) == 0) { # no data: defaults are used; everythings fine
			$extra_data = "";
	} else {
		if (read_config_option("rrdtool_version") == RRD_VERSION_1_3) {	# rrdtool 1.3 uses fontconfig
			$font = '"' . $form_previous_value . '"';
			$out_array = array();
			exec('fc-list ' . $font, $out_array);
			if (sizeof($out_array) == 0) {
				$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: FONT NOT FOUND") . "]</span>";
			} else {
				$extra_data = "<span class=\"success\"><br>[" . __("OK: FONT FOUND") . "]</span>";
			}
		} elseif (read_config_option("rrdtool_version") == RRD_VERSION_1_0 ||
				  read_config_option("rrdtool_version") == RRD_VERSION_1_2) { # rrdtool 1.0 and 1.2 use font files
			if (is_file($form_previous_value)) {
				$extra_data = "<span class=\"success\"><br>[" . __("OK: FILE FOUND") . "]</span>";
			}else if (is_dir($form_previous_value)) {
				$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: IS DIR") . "]</span>";
			}else{
				$extra_data = "<span class=\"warning\"><br>[" . __("ERROR: FILE NOT FOUND") . "]</span>";
			}
		} # will be used for future versions of rrdtool
	}

	if (strlen($class)) {
		$class = " class='$class' ";
	}

	if (strlen($on_change)) {
		$on_change = " onChange='$on_change' ";
	}

	print " id='$form_name' name='$form_name'" . $on_change . $class ." size='$form_size'" . (!empty($form_max_length) ? " maxlength='$form_max_length'" : "") . " value='" . htmlspecialchars($form_previous_value, ENT_QUOTES) . "'>" . $extra_data;
}

/** form_confirm - draws a table presenting the user with some choice and allowing
     them to either proceed (delete) or cancel
   @param string $body_text - the text to prompt the user with on this form
   @param string $cancel_url - the url to go to when the user clicks 'cancel'
   @param string $action_url - the url to go to when the user clicks 'delete' */
function form_confirm($title_text, $body_text, $cancel_url, $action_url) { ?>
	<br>
	<table align="center" cellpadding="1" cellspacing="0" border="0" bgcolor="#B61D22" width="60%">
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

/** form_confirm_buttons - draws a cancel and delete button suitable for display
     on a confirmation form
   @param string $cancel_url - the url to go to when the user clicks 'cancel'
   @param string $action_url - the url to go to when the user clicks 'delete' */
function form_confirm_buttons($action_url, $cancel_url) {
	global $config;
 ?>
	<tr>
		<td bgcolor="#E1E1E1">
			<a href="<?php print $cancel_url;?>"><img src="<?php print CACTI_URL_PATH; ?>images/button_cancel.gif" alt="<?php print __("Cancel");?>" align="middle"></a>
			<a href="<?php print $action_url . "&confirm=yes";?>"><img src="<?php print CACTI_URL_PATH ?>images/button_delete.gif" alt="<?php print __("Delete");?>" align="middle"></a>
		</td>
	</tr>
<?php }

/** form_save_button - draws a (save|create) and cancel button at the bottom of
     an html edit form
   @param string $cancel_url - the url to go to when the user clicks 'cancel'
   @param string $force_type - if specified, will force the 'action' button to be either
     'save' or 'create'. otherwise this field should be properly auto-detected
   @param string $key_field  - required to dinstinguish between SAVE and CREATE */
function form_save_button($cancel_url, $force_type = "", $key_field = "id") {
	global $config;
	if (empty($force_type)) {
		if (empty($_GET[$key_field])) {
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
	<table class='saveBox'>
		<tr>
			<td>
				<input type='hidden' name='action' value='save'>
				<a href='<?php print $cancel_url;?>'><img src='<?php echo CACTI_URL_PATH; ?>images/button_cancel2.gif' alt='<?php print __("Cancel");?>' align='middle' border='0'></a>
				<input type='image' src='<?php echo CACTI_URL_PATH; ?>images/<?php print $img;?>' alt='<?php print $alt;?>' align='middle'>
			</td>
		</tr>
	</table>
	</form>
	<?php
}

/** form_confirm_alt - draws a table presenting the user with some choice and allowing
     them to either proceed (delete) or cancel
   @param string $body_text - the text to prompt the user with on this form
   @param string $body_text - the text to prompt the user with on this form
   @param string $cancel_url - the url to go to when the user clicks 'cancel'
   @param string $action_url - the url to go to when the user clicks 'delete' */
function form_confirm_alt($title_text, $body_text, $cancel_url, $action_url) { ?>
		<br>
		<table align="center" cellpadding="1" cellspacing="0" border="0" bgcolor="#B61D22" width="60%">
			<tr>
				<td bgcolor="#B61D22" colspan="10">
					<table width="100%" cellpadding="3" cellspacing="0">
						<tr>
							<td bgcolor="#B61D22" class="textHeaderDark"><strong><?php print $title_text;?></strong></td>
						</tr>
						<?php
						form_area($body_text);
						form_confirm_buttons_alt();
						?>
					</table>
				</td>
			</tr>
		</table>

<?php }

/** form_save_button_alt - draws a (save|create) and cancel button at the bottom of
     an html edit form
   @param string $cancel_action - determines CANCEL action, e.g. URL to go to
   @param string $action 		- determines SAVE action
   @param string $force_type 	- if specified, will force the 'action' button to be either
     'save' or 'create'. otherwise this field should be properly auto-detected
   @param string $key_field  	- required to dinstinguish between SAVE and CREATE */
function form_save_button_alt($cancel_action = "", $action = "save", $force_type = "", $key_field = "id") {
	global $config;

	$calt = __("Cancel");

	if ((empty($force_type)) || (substr_count($cancel_action,"return"))) {
		if (empty($_GET[$key_field])) {
			$sname = "save";
			$salt  = __("Create");
		}else{
			$sname = "save";
			$salt  = __("Save");
		}

		if (substr_count($cancel_action,"return")) {
			$calt   = __("Return");
			$action = "save";
		}else{
			$calt   = __("Cancel");
		}
	}elseif ($force_type == "save") {
		$sname = "save";
		$salt  = __("Save");
	}elseif ($force_type == "create") {
		$sname = "save";
		$salt  = __("Create");
	}elseif ($force_type == "import") {
		$sname = "create";
		$salt  = __("Import");
	}elseif ($force_type == "export") {
		$sname = "create";
		$salt  = __("Export");
	}

	if ($force_type != "import" && $force_type != "export") {
		if (substr_count($cancel_action, "!")) {
			$url = form_cancel_action_compose($cancel_action);
			$action = "window.location.assign(\"" . htmlspecialchars($url) . "\")";
		}elseif (isset($_SERVER['HTTP_REFERER'])) {
			$url = $_SERVER['HTTP_REFERER'];
			$action = "window.location.assign(\"" . htmlspecialchars($url) . "\")";
		}else{
			$action = "history.back()";
		}
	}

	?>
	<table class='saveBox'>
		<tr>
			<td>
				<input type='hidden' name='action' value='<?php print $sname;?>'>
				<?php if ($force_type != "import" && $force_type != "export") { ?><input id='cancel' type='button' value='<?php print $calt;?>' onClick='<?php print $action;?>' name='cancel'><?php } ?>
				<input id='<?php print $sname;?>' type='submit' value='<?php print $salt;?>' name='<?php print $sname;?>'>
			</td>
		</tr>
	</table>
	</form>
	<?php
}


/** form_cancel_action_compose - determine if the user has chosen to cancel, and if the user
   has selected "cancel", where to goto.  the default will be to goto the current
   page with no action (aka continue)
  @param string $cancel_action -
  		'url' 	- defines the URL to go to
  		'path'	-
  		'return'-
  		else	- composes the URL parameters from REQUEST_URI
  @return string - URL to go to
*/
function form_cancel_action_compose($cancel_action) {
	global $url_path;

	$vars        = explode("|", $cancel_action);
	$uri         = $_SERVER["REQUEST_URI"];
	$uri_request = "";
	$url         = "";

	if (sizeof($vars)) {
	foreach($vars as $var) {
		$request = explode("!", $var);

		if ($request[0] == "url") {
			$url = $request[1];
		}elseif ($request[0] == "path" || $request[0] == "return") {
			$url = $request[1];
		}elseif (strlen($uri_request)) {
			$uri_request .= "&" . $request[0] . "=" . $request[1];
		}else{
			$uri_request .= "?" . $request[0] . "=" . $request[1];
		}
	}
	}

	if ((isset($url)) && (strlen($url))) {
		return html_simple_decode($url_path . $url . $uri_request);
	}elseif ((isset($uri)) && (strlen($uri))) {
		return $url_path . $uri . $uri_request;
	}
}

/** form_return_button_alt - draws a return button at the bottom of
     an html edit form
*/
function form_return_button_alt() {
	?>
	<tr>
		<td bgcolor="#f5f5f5" align="right">
			<input type='button' value='<?php print __("Return");?>' onClick='window.location.assign("<?php print htmlspecialchars($_SERVER['HTTP_REFERER']);?>")' name='cancel'>
		</td>
	</tr>
	</form>
	<?php
}

/** form_yesno_button_alt - draws a yes and no button at the bottom of an html edit form
   @param string $device_list 	- serialized device array
   @param string $drp_action 	- if specified, will direct the system what to do if "No" is selected
 */
function form_yesno_button_alt($device_list, $drp_action = "none") {
	global $config;

	?>
	<tr>
		<td align="right">
			<div><input type='hidden' name='action' value='actions'></div>
			<div><input type='hidden' name='selected_items' value='<?php print $device_list;?>'></div>
			<div><input type='hidden' name='drp_action' value='<?php print $drp_action;?>'></div>
			<input type='button' value='<?php print __("No");?>' onClick='window.location.assign("<?php print htmlspecialchars($_SERVER['HTTP_REFERER']);?>")' name='cancel'>
			<input type='submit' value='<?php print __("Yes");?>' name='yes'>
		</td>
	</tr>
	</form>
	<?php
}

/** form_yesno_button_alt2 		- draws a yes and no button at the bottom of an html edit form
   @param string $device_list 	- serialized item array
   @param string $action 		- specifies the action code, e.g. "save_gt" for the main procedure select directive
 */
function form_yesno_button_alt2($item_list, $action = "none") {
	global $config;

	?>
	<tr>
		<td align="right">
			<div><input type='hidden' name='action' value='<?php print $action;?>'></div>
			<div><input type='hidden' name='selected_items' value='<?php print $item_list;?>'></div>
			<input type='button' value='<?php print __("No");?>' onClick='window.location.assign("<?php print htmlspecialchars($_SERVER['HTTP_REFERER']);?>")' name='cancel'>
			<input type='submit' value='<?php print __("Yes");?>' name='yes'>
		</td>
	</tr>
	</form>
	<?php
}

/** form_confirm_buttons_alt - draws a cancel and delete button suitable for display
     on a confirmation form */
function form_confirm_buttons_alt() {
	global $config;
	?>
	<tr>
		<td bgcolor="#E1E1E1">
			<input type='submit' value='<?php print __("Cancel");?>' name='cancel'>
			<input type='submit' value='<?php print __("Delete");?>' name='delete'>
		</td>
	</tr>
<?php }

/** html_simple_decode
 *
 * @param string $string - string to decode
 * @return string
 */
function html_simple_decode($string) {
	if (function_exists("html_entity_decode")) {
		return html_entity_decode($string);
	}else{
		return str_replace("&amp;", "&", str_replace("&quot;", "\"", str_replace("&#039;", "'", $string)));
	}
}

/** draw_template_edit_form
 *
 * @param string $table_id
 * @param string $edit_struct
 * @param string $edit_data
 * @param string $use_template
 */
function draw_template_edit_form ($table_id, $edit_struct, $edit_data, $use_template=false) {

	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 1, true, $table_id);

	$form_array = array();

	while (list($field_name, $field_array) = each($edit_struct)) {
		if (isset($field_array["flags"]) && $field_array["flags"] == "NOTEMPLATE" && !$use_template) {
			CONTINUE;
		}
		$form_array += array($field_name => $edit_struct[$field_name]);

		$form_array[$field_name]["value"] = (isset($edit_data[$field_name]) ? $edit_data[$field_name] : "");
		$form_array[$field_name]["form_id"] = (isset($edit_data["id"]) ? $edit_data["id"] : "0");

		if (!(($use_template === false) || ($edit_data{"t_" . $field_name} == CHECKED))) {
			$form_array[$field_name]["method"] = "template_" . $form_array[$field_name]["method"];
		}


		$form_array[$field_name]["sub_checkbox"] = array(
			"name" => "t_" . $field_name,
			"friendly_name" => "<em>" . __("Use Per-Graph Value (Ignore this Value)") . "</em>",
			"value" => (isset($edit_data{"t_" . $field_name}) ? $edit_data{"t_" . $field_name} : ""),
			"class" => (isset($form_array[$field_name]["class"]) ? $form_array[$field_name]["class"] : "")
		);
	}

	draw_edit_form(
		array(
			"config" => array("no_form_tag" => true),
			"fields" => inject_form_variables($form_array, $edit_data)
			)
		);

	print "</table></td></tr>";		/* end of html_header */

}
