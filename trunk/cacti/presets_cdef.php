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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/sys/sequence.php");
require_once(CACTI_BASE_PATH . "/lib/sys/cdef.php");

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
    Global Form Functions
   -------------------------- */

function draw_cdef_preview($cdef_id) {
	global $colors; ?>
	<tr bgcolor="#<?php echo $colors["messagebar_background"];?>">
		<td>
			<pre>cdef=<?php echo get_cdef($cdef_id, true);?></pre>
		</td>
	</tr>
<?php }

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_cdef"])) {
		$save["id"] = $_POST["id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["cdef_string"] = form_input_validate($_POST["cdef_string"], "cdef_string", "", false, 3);

		if (!is_error_message()) {
			$cdef_id = sql_save($save, "preset_cdef");

			if ($cdef_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: presets_cdef.php?action=edit&id=" . (empty($cdef_id) ? $_POST["id"] : $cdef_id));
		}else{
			header("Location: presets.php?action=view_cdef");
		}
	}
}

/* ---------------------
    CDEF Functions
   --------------------- */

function cdef_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the CDEF") . " <strong>'" . db_fetch_cell("select name from preset_cdef where id=" . $_GET["id"]) . "'</strong>?", "presets.php?action=view_cdef", "presets_cdef.php?action=remove&id=" . $_GET["id"]);
		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from preset_cdef where id=" . $_GET["id"]);
	}
}

function cdef_edit() {
	global $colors, $cdef_item_types, $cdef_functions, $cdef_operators, $custom_data_source_types;

	if (!empty($_GET["id"])) {
		$cdef = db_fetch_row("select * from preset_cdef where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $cdef["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	?>
	<script type="text/javascript">
	<!--
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
	//-->
	</script>

	<form method='post' action='presets_cdef.php'>
	<?php

	html_start_box("<strong>" . _("CDEF's") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Name");?></font><br>
			<?php echo _("A recognizable name for this CDEF.");?>
		</td>
		<td colspan="2">
			<?php form_text_box("name", (isset($cdef) ? $cdef["name"] : ""), "", "255", 40, "text");?>
		</td>
	</tr>
	<?php
	html_end_box();

	html_start_box("", "98%", "a1a1a1", "3", "center", "");
	?>
	<tr bgcolor="#<?php echo $colors["form_alternate2"];?>">
		<td style="font-size: 12px; font-weight: bold;">
			CDEF String:
		</td>
	</tr>
	<tr bgcolor="#<?php echo $colors["form_alternate2"];?>">
		<td>
			<input style="border: 2px solid #27942e; width: 100%;" type="text" name="cdef_string" id="cdef_string" value="<?php echo (isset($cdef) ? $cdef["cdef_string"] : "");?>" maxlength="255">
		</td>
	</tr>
	<?php
	html_end_box();

	html_start_box("", "98%", $colors["header_panel_background"], "3", "center", "");
	?>
	<tr bgcolor='<?php echo $colors["header_panel_background"];?>'>
		<td colspan="3" class='textSubHeaderDark'>
			<?php echo _("CDEF String Variables (Not Saved)");?>
		</td>
	</tr>
	<?php
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Function");?></font><br>
			<?php echo _("Builtin functions that can be applied to any value.");?>
		</td>
		<td>
			<?php form_dropdown("c_function", $cdef_functions, "", "", "", "", "");?>
		</td>
		<td align="right" style="font-size: 12px;">
			[<strong><a href="javascript:insert_cdef_variable_name('c_function')">INSERT</a></strong>]
		</td>
	</tr>
	<?php
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle"><?phpe echo _("Operator");?></font><br>
			<?php echo _("Mathematical operations that can be applied to any value.");?>
		</td>
		<td>
			<?php form_dropdown("c_operator", $cdef_operators, "", "", "", "", "");?>
		</td>
		<td align="right" style="font-size: 12px;">
			[<strong><a href="javascript:insert_cdef_variable_name('c_operator')"><?php echo _("INSERT");?></a></strong>]
		</td>
	</tr>
	<?php
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Cacti Variables");?></font><br>
			<?php echo _("These values will be substituted by Cacti before being inserted into the final CDEF string.");?>
		</td>
		<td>
			<?php form_dropdown("c_special_ds", $custom_data_source_types, "", "", "", "", "", "", "40");?>
		</td>
		<td align="right" style="font-size: 12px;">
			[<strong><a href="javascript:insert_cdef_variable_value('c_special_ds')"><?php echo _("INSERT");?></a></strong>]
		</td>
	</tr>
	<?php

	html_end_box();

	form_hidden_box("id",(isset($cdef) ? $cdef["id"] : "0"),"");
	form_hidden_box("save_component_cdef","1","");
	form_save_button("presets.php?action=view_cdef");
}

?>
