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
include_once(CACTI_BASE_PATH . "/lib/export.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch (get_request_var_request("action")) {
	case 'save':
		form_save();

		break;
	default:
		include_once(CACTI_BASE_PATH . "/include/top_header.php");

		export();

		include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $export_types;

	if (isset($_POST["save_component_export"])) {
		$xml_data = get_item_xml($_POST["export_type"], $_POST["export_item_id"], (((isset($_POST["include_deps"]) ? $_POST["include_deps"] : "") == "") ? false : true));

		if (get_request_var_post("output_format") == "1") {
			include_once(CACTI_BASE_PATH . "/include/top_header.php");
			print "<table class='wp100 center'><tr><td><pre>" . htmlspecialchars($xml_data) . "</pre></td></tr></table>";
			include_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		}elseif (get_request_var_post("output_format") == "2") {
			header("Content-type: application/xml");
			print $xml_data;
		}elseif (get_request_var_post("output_format") == "3") {
			header("Content-type: application/xml");
			header("Content-Disposition: attachment; filename=cacti_" . $_POST["export_type"] . "_" . strtolower(clean_up_file_name(db_fetch_cell(str_replace("|id|", $_POST["export_item_id"], $export_types{$_POST["export_type"]}["title_sql"])))) . ".xml");
			print $xml_data;
		}
	}
}

/* ---------------------------
    Template Export Functions
   --------------------------- */

function export() {
	global $colors, $export_types;

	/* 'graph_template' should be the default */
	if (!isset($_REQUEST["export_type"])) {
		$_REQUEST["export_type"] = "graph_template";
	}

	?>
	<form name="form_graph_id" action="templates_export.php">
	<table class='topBoxAlt'>
		<tr>
			<td class="textArea" style="padding: 3px;">
				<?php print __("What would you like to export?");?>&nbsp;

				<select name="cbo_graph_id" onChange="window.location=document.form_graph_id.cbo_graph_id.options[document.form_graph_id.cbo_graph_id.selectedIndex].value">
					<?php
					while (list($key, $array) = each($export_types)) {
						print "<option value='templates_export.php?export_type=$key'"; if (get_request_var_request("export_type") == $key) { print " selected"; } print ">" . $array["name"] . "</option>\n";
					}
					?>
				</select>
			</td>
		</tr>
	</table>
	</form>
	<form action="templates_export.php" method="post">
	<?php

	html_start_box("<strong>" . __("Export Template") . "</strong> [" . $export_types{get_request_var_request("export_type")}["name"] . "]", "100", $colors["header"], "3", "center", "");

	form_alternate_row_color("item"); ?>
		<td width="50%">
			<font class="textEditTitle"><?php print $export_types{$_REQUEST["export_type"]}["name"];?> <?php print __("to Export");?></font><br>
			<?php print __("Choose the exact item to export to XML.");?>
		</td>
		<td>
			<?php form_dropdown("export_item_id",db_fetch_assoc($export_types{get_request_var_request("export_type")}["dropdown_sql"]),"name","id","","","0");?>
		</td>
	</tr>

	<?php form_alternate_row_color("dependencies"); ?>
		<td width="50%">
			<font class="textEditTitle"><?php print __("Include Dependencies");?></font><br>
			<?php print __("Some templates rely on other items in Cacti to function properly. It is highly recommended that you select this box or the resulting import may fail.");?>
		</td>
		<td>
			<?php form_checkbox("include_deps", "on", __("Include Dependencies"), "on", "", true);?>
		</td>
	</tr>

	<?php form_alternate_row_color("format"); ?>
		<td width="50%">
			<font class="textEditTitle"><?php print __("Output Format");?></font><br>
			<?php print __("Choose the format to output the resulting XML file in.");?>
		</td>
		<td>
			<?php
			form_radio_button("output_format", "3", "1", __("Output to the Browser (within Cacti)"),"1",true); print "<br>";
			form_radio_button("output_format", "3", "2", __("Output to the Browser (raw XML)"),"1",true); print "<br>";
			form_radio_button("output_format", "3", "3", __("Save File Locally"),"1",true);
			?>
		</td>
	</tr>
	<?php

	html_end_box();

	form_hidden_box("export_type", get_request_var_request("export_type"), "");
	form_hidden_box("save_component_export","1","");

	form_save_button_alt("", "save", "export");
}
