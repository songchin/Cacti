<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

include("./include/auth.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		color_remove();

		header ("Location: color.php");
		break;
	case 'edit':
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		color_edit();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		color();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_color"])) {
		$save["id"] = $_POST["id"];
		$save["hex"] = form_input_validate($_POST["hex"], "hex", "^[a-fA-F0-9]+$", false, 3);

		if (!is_error_message()) {
			$color_id = sql_save($save, "colors");

			if ($color_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header("Location: color.php?action=edit&id=" . (empty($color_id) ? $_POST["id"] : $color_id));
		}else{
			header("Location: color.php");
		}
	}
}

/* -----------------------
    Color Functions
   ----------------------- */

function color_remove() {
	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (sizeof(db_fetch_assoc("SELECT * FROM graph_templates_item WHERE color_id=" . get_request_var("id") . " LIMIT 1"))) {
		$message = "<i>Color " . get_request_var("id") . " is in use and can not be removed</i>\n";

		$_SESSION['sess_message_color_ref_int'] = array('message' => "<font size=-2>$message</font>", 'type' => 'info');

		raise_message('color_ref_int');
	}else{
		db_execute("delete from colors where id=" . $_GET["id"]);
	}
}

function color_edit() {
	global $colors, $fields_color_edit;

	/* ================= input validation ================= */
	input_validate_input_number(get_request_var("id"));
	/* ==================================================== */

	if (!empty($_GET["id"])) {
		$color = db_fetch_row("select * from colors where id=" . $_GET["id"]);
		$header_label = __("[edit: ") . $color["hex"] . "]";
	}else{
		$header_label = __("[new]");
	}

	print "<form method='post' action='" .  basename($_SERVER["PHP_SELF"]) . "' name='color_edit'>\n";
	html_start_box("<strong>" . __("Colors") . "</strong> $header_label", "100%", $colors["header"], 0, "center", "");
	$header_items = array(__("Field"), __("Value"));
	print "<tr><td>";
	html_header($header_items, 2, true, 'header_color_edit');

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_color_edit, (isset($color) ? $color : array()))
		));

	print "</table></td></tr>";		/* end of html_header */
	html_end_box();

	form_save_button_alt();
}

function color() {
	global $colors;

	html_start_box("<strong>" . __("Colors") . "</strong>", "100%", $colors["header"], "3", "center", "color.php?action=edit");

	print "<tr class='rowSubHeader'>";
		DrawMatrixHeaderItem(__("Hex"),    $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem(__("Color"),  $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);

		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);
		DrawMatrixHeaderItem(__("Hex"),    $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem(__("Color"),  $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);

		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);
		DrawMatrixHeaderItem(__("Hex"),    $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem(__("Color"),  $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);

		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);
		DrawMatrixHeaderItem(__("Hex"),    $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem(__("Color"),  $colors["header_text"], 1, "center");
		DrawMatrixHeaderItem("&nbsp;", $colors["header_text"], 1);
	print "</tr>";

	$color_list = db_fetch_assoc("select * from colors order by hex");

	if (sizeof($color_list) > 0) {
		$j=0; ## even/odd counter
		foreach ($color_list as $color) {
			$j++;
			if ($j % 4 == 1) {
				form_alternate_row_color($color["id"], true);
					?>
					<td width='1'>
						<a class="linkEditMain" style='display:block;' href="<?php print htmlspecialchars("color.php?action=edit&id=" . $color["id"]);?>"><?php print $color["hex"];?></a>
					</td>
					<td bgcolor="#<?php print $color['hex'];?>" width="10%">&nbsp;</td>
					<td align="right">
						<a href="<?php print htmlspecialchars("color.php?action=remove&id=" . $color["id"]);?>"><img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='middle'></a>
					</td>
				<?php	$j=1;
			}elseif (($j % 4 == 2) || ($j % 4 == 3)) {
					?>
					<td></td>
					<td width='1'>
						<a class="linkEditMain" style='display:block;' href="<?php print htmlspecialchars("color.php?action=edit&id=" . $color["id"]);?>"><?php print $color["hex"];?></a>
					</td>
					<td bgcolor="#<?php print $color['hex'];?>" width="10%">&nbsp;</td>
					<td align="right">
						<a href="<?php print htmlspecialchars("color.php?action=remove&id=" . $color["id"]);?>"><img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='middle'></a>
					</td>
				<?php	$j=$j++;
			} else { ?>
					<td></td>
					<td width='1'>
						<a class="linkEditMain" style='display:block;' href="<?php print htmlspecialchars("color.php?action=edit&id=" . $color["id"]);?>"><?php print $color["hex"];?></a>
					</td>
					<td bgcolor="#<?php print $color['hex'];?>" width="10%">&nbsp;</td>
					<td align="right">
						<a href="<?php print htmlspecialchars("color.php?action=remove&id=" . $color["id"]);?>"><img class="buttonSmall" src="images/delete_icon.gif" alt="<?php print __("Delete");?>" align='middle'></a>
					</td>
			<?php
			form_end_row();
			}
		}
		## check for completion of odd number second column:
		if ($j == 1) {
			?>
				<td colspan=4></td>
			<?php
			form_end_row();
		}
	}
	html_end_box();
}
