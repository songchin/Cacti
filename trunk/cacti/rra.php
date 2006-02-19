<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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
require_once(CACTI_BASE_PATH . "/lib/rra/rra_form.php");
require_once(CACTI_BASE_PATH . "/lib/rra/rra_update.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		rra_remove();

		header("Location: rra.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		rra_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		rra();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_rra"])) {
		/* cache all post field values */
		init_post_field_cache();

		$form_rra["name"] = $_POST["name"];
		$form_rra["x_files_factor"] = $_POST["x_files_factor"];
		$form_rra["steps"] = $_POST["steps"];
		$form_rra["rows"] = $_POST["rows"];
		$form_rra["timespan"] = $_POST["timespan"];

		field_register_error(api_rra_field_validate($form_rra, "|field|"));

		/* if the validation passes, save the row to the database */
		if (!is_error_message()) {
			$rra_id = api_rra_save($_POST["id"], $form_rra);

			if ($rra_id) {
				/* save consolidation function mappings (for the 'consolidation_function_id' field) */
				api_rra_consolidation_function_id_save($rra_id, $_POST["consolidation_function_id"]);
			}
		}

		if (is_error_message()) {
			header("Location: rra.php?action=edit&id=" . (empty($rra_id) ? $_POST["id"] : $rra_id));
		}else{
			header("Location: rra.php");
		}
	}
}

/* -------------------
    RRA Functions
   ------------------- */

function rra_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		require_once(CACTI_BASE_PATH . "/include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the round robin archive") . " <strong>'" . db_fetch_cell("select name from rra where id=" . $_GET["id"]) . "'</strong>?", "rra.php", "rra.php?action=remove&id=" . $_GET["id"]);
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from rra where id=" . $_GET["id"]);
		db_execute("delete from rra_cf where rra_id=" . $_GET["id"]);
    	}
}

function rra_edit() {
	global $colors, $fields_rra_edit;

	if (!empty($_GET["id"])) {
		$rra = db_fetch_row("select * from rra where id=" . $_GET["id"]);
		$header_label = _("[edit: ") . $rra["name"] . "]";
	}else{
		$header_label = _("[new]");
	}

	html_start_box("<strong>" . _("Round Robin Archives") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_rra_edit, (isset($rra) ? $rra : array()))
		));

	html_end_box();

	form_save_button("rra.php");
}

function rra() {
	global $colors;

	html_start_box("<strong>" . _("Round Robin Archives") . "</strong>", "98%", $colors["header_background"], "3", "center", "rra.php?action=edit");

	print "<tr bgcolor='#" . $colors["header_panel_background"] . "'>";
		DrawMatrixHeaderItem(_("Name"),$colors["header_text"],1);
		DrawMatrixHeaderItem(_("Steps"),$colors["header_text"],1);
		DrawMatrixHeaderItem(_("Rows"),$colors["header_text"],1);
		DrawMatrixHeaderItem(_("Timespan"),$colors["header_text"],2);
	print "</tr>";

	$rras = db_fetch_assoc("select id,name,rows,steps,timespan from rra order by steps");

	$i = 0;
	if (sizeof($rras) > 0) {
	foreach ($rras as $rra) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="rra.php?action=edit&id=<?php print $rra["id"];?>"><?php print $rra["name"];?></a>
			</td>
			<td>
				<?php print $rra["steps"];?>
			</td>
			<td>
				<?php print $rra["rows"];?>
			</td>
			<td>
				<?php print $rra["timespan"];?>
			</td>
			<td align="right">
				<a href="rra.php?action=remove&id=<?php print $rra["id"];?>"><img src="<?php print html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
			</td>
		</tr>
	<?php
	}
	}
	html_end_box();
}
?>
