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

include("./include/config.php");
include("./include/auth.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'view_cdef':
		include_once("./include/top_header.php");

		draw_tabs();
		view_cdef();

		include_once("./include/bottom_footer.php");
		break;
	case 'view_color':
		include_once("./include/top_header.php");

		draw_tabs();
		view_color();

		include_once("./include/bottom_footer.php");
		break;
	case 'view_gprint':
		include_once("./include/top_header.php");

		draw_tabs();
		view_gprint();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		draw_tabs();
		view_cdef();

		include_once("./include/bottom_footer.php");
		break;
}

/* ------------------------
    Global Functions
   ------------------------ */
function draw_tabs() {
	global $colors;
	?>
	<table class='tabs' width='98%' cellspacing='0' cellpadding='3' align='center'>
		<tr>
			<td <?php echo ((($_REQUEST["action"] == "") || ($_REQUEST["action"] == "view_cdef")) ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='70' align='center' class='tab'>
				<span class='textHeader'><a href='presets.php?action=view_cdef'>CDEFs</a></span>
			</td>
			<td width='1'></td>
			<td <?php echo (($_REQUEST["action"] == "view_color") ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='70' align='center' class='tab'>
				<span class='textHeader'><a href='presets.php?action=view_color'>Colors</a></span>
			</td>
			<td width='1'></td>
			<td <?php echo (($_REQUEST["action"] == "view_gprint") ? "bgcolor='" . $colors["form_alternate1"] . "'" : "bgcolor='" . $colors["form_alternate2"] . "'");?> nowrap='nowrap' width='80' align='center' class='tab'>
				<span class='textHeader'><a href='presets.php?action=view_gprint'>GPRINTs</a></span>
			</td>
			<td></td>
		</tr>
	</table>
	<?php
}

/* ------------------------
    Presets View Functions
   ------------------------ */

function view_cdef() {
	global $colors;

	html_start_box("<strong>CDEFs</strong>", "98%", $colors["header_background"], "3", "center", "presets_cdef.php?action=edit");

	html_header(array("Name"), 2);

	$cdefs = db_fetch_assoc("select * from preset_cdef order by name");

	if (sizeof($cdefs) > 0) {
		$i = 0;
		foreach ($cdefs as $cdef) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="presets_cdef.php?action=edit&id=<?php echo $cdef["id"];?>"><?php echo $cdef["name"];?></a>
				</td>
				<td align="right">
					<a href="presets_cdef.php?action=remove&id=<?php echo $cdef["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
		<?php
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em>No Items Found</em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

function view_color() {
	global $colors;

	html_start_box("<strong>Colors</strong>", "98%", $colors["header_background"], "3", "center", "color.php?action=edit");

	html_header(array("Hex Value", "Color"), 2);

	$color_list = db_fetch_assoc("select * from preset_color order by hex");

	if (sizeof($color_list) > 0) {
		$i = 0;
		foreach ($color_list as $color) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="presets_color.php?action=edit&id=<?php echo $color["id"];?>"><?php echo $color["hex"];?></a>
				</td>
				<td bgcolor="#<?php echo $color["hex"];?>" width="40">&nbsp;</td>
				<td align="right">
					<a href="presets_color.php?action=remove&id=<?php echo $color["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
			<?php
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em>No Items Found</em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

function view_gprint() {
	global $colors;

	html_start_box("<strong>GPRINT Presets</strong>", "98%", $colors["header_background"], "3", "center", "presets_gprint.php?action=edit");

	html_header(array("Name", "Format String"), 2);

	$gprints = db_fetch_assoc("select id,name,gprint_text from preset_gprint order by name");

	if (sizeof($gprints) > 0) {
		$i = 0;
		foreach ($gprints as $gprint) {
			form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], $i);
				?>
				<td>
					<a class="linkEditMain" href="presets_gprint.php?action=edit&id=<?php echo $gprint["id"];?>"><?php echo $gprint["name"];?></a>
				</td>
				<td>
					<?php echo $gprint["gprint_text"];?>
				</td>
				<td align="right">
					<a href="presets_gprint.php?action=remove&id=<?php echo $gprint["id"];?>"><img src="<?php echo html_get_theme_images_path('delete_icon.gif');?>" width="10" height="10" border="0" alt="Delete"></a>
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{
		form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
			<td colspan="2">
				<em>No Items Found</em>
			</td>
		</tr>
		<?php
	}
	html_end_box();
}

?>
