<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

include ("./include/auth.php");
//include_once('./lib/api_data_input.php');

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'delete':
		poller_delete();

		header("Location: data_pollers.php");
		break;
	case 'edit':
		include_once("./include/top_header.php");

		poller_edit();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		pollers();

		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $registered_cacti_names;

	if (isset($_POST["save_component_data_input"])) {
		$data_input_id = api_data_input_save($_POST["id"], $_POST["name"], $_POST["input_string"], $_POST["type_id"]);

		if ((is_error_message()) || (empty($_POST["id"]))) {
			header("Location: data_pollers.php?action=edit&id=" . (empty($data_poller_id) ? $_POST["id"] : $data_poller_id));
		}else{
			header("Location: data_pollers.php");
		}
	}elseif (isset($_POST["save_component_field"])) {
		$data_input_field_id = api_data_input_field_save($_POST["id"], $_POST["data_input_id"], $_POST["name"],
			$_POST["data_name"], $_POST["input_output"], (isset($_POST["update_rra"]) ? $_POST["update_rra"] : ""),
			(isset($_POST["type_code"]) ? $_POST["type_code"] : ""), (isset($_POST["regexp_match"]) ? $_POST["regexp_match"] : ""),
			(isset($_POST["allow_nulls"]) ? $_POST["allow_nulls"] : ""));

		if (is_error_message()) {
			header("Location: data_pollers.php?action=field_edit&data_poller_id=" . $_POST["data_poller_id"] . "&id=" . (empty($data_poller_field_id) ? $_POST["id"] : $data_poller_field_id) . (!empty($_POST["input_output"]) ? "&type=" . $_POST["input_output"] : ""));
		}else{
			header("Location: data_pollers.php?action=edit&id=" . $_POST["data_poller_id"]);
		}
	}
}

/* -----------------------
    Data Input Functions
   ----------------------- */

function poller_delete() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm("Are You Sure?", "Are you sure you want to delete the poller <strong>'" . db_fetch_cell("select hostname from poller where id=" . $_GET["id"]) . "'</strong>?", "data_pollers.php", "data_pollers.php?action=delete&id=" . $_GET["id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ($_GET["id"] != 0) {
		if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
			api_data_input_remove($_GET["id"]);
		}
	} else {
		// Can't delete poller id = 0
	}
}

function poller_edit() {
	global $colors, $fields_data_poller_edit;

	if ((isset($_GET["id"])) && ($_GET["id"] >= 0)) {
		$data_poller = db_fetch_row("select * from poller where id=" . $_GET["id"]);
		$header_label = "[edit: " . $data_poller["hostname"] . "]";
	}else{
		$header_label = "[new]";
	}

	html_start_box("<strong>Data Pollers</strong> $header_label", "98%", $colors["header"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_data_poller_edit, (isset($data_poller) ? $data_poller : array()))
		));

	html_end_box();

	form_save_button("data_pollers.php");
}

function pollers() {
	global $colors, $input_types;

	html_start_box("<strong>Data Pollers</strong>", "98%", $colors["header"], "3", "center", "data_pollers.php?action=edit");

	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("Description",$colors["header_text"],1);
		DrawMatrixHeaderItem("Hostname",$colors["header_text"],1);
		DrawMatrixHeaderItem("Active",$colors["header_text"],1);
		DrawMatrixHeaderItem("&nbsp;",$colors["header_text"],1);
	print "</tr>";

	$data_pollers = db_fetch_assoc("select * from poller order by description");

	$i = 0;
	if (sizeof($data_pollers) > 0) {
	foreach ($data_pollers as $data_poller) {
		form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="data_pollers.php?action=edit&id=<?php print $data_poller["id"];?>"><?php print $data_poller["description"];?></a>
			</td>
			<td>
				<?php print $data_poller["hostname"];?></a>
			</td>
			<td>
				<?php print $data_poller["active"];?></a>
			</td>
			<td align="right">
				<a href="data_pollers.php?action=delete&id=<?php print $data_poller["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td><em>No Data Pollers</em></td></tr>";
	}
	html_end_box();
}
?>