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
include_once("./lib/graph/graph_template_update.php");
include_once("./include/graph/graph_form.php");
include_once("./lib/template.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'remove':
		input_remove();

		header("Location: graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
		break;
	case 'edit':
		include_once("./include/top_header.php");

		input_edit();

		include_once("./include/bottom_footer.php");
		break;
}

function form_save() {
	if (isset($_POST["save_component_input"])) {
		$form_selected_graph_item = array();

		/* list all selected graph items on the form */
		reset($_POST);
		while (list($var, $val) = each($_POST)) {
			if (substr($var, 0, 2) == "i_") {
				$matches = explode("_", $var);

				$form_selected_graph_item[] = $matches[1];
			}
		}

		$graph_template_item_input_id = api_graph_template_item_input_save($_POST["id"], $form_selected_graph_item, $_POST["graph_template_id"],
			$_POST["field_name"], $_POST["name"]);

		if (is_error_message()) {
			header("Location: graph_templates_inputs.php?action=edit" . (empty($graph_template_item_input_id) ? "" : "&id=" . $graph_template_item_input_id) . "&graph_template_id=" . $_POST["graph_template_id"]);
		}else{
			header("Location: graph_templates.php?action=edit&id=" . $_POST["graph_template_id"]);
		}
	}
}

/* ------------------------------------
    input - Graph Template Item Inputs
   ------------------------------------ */

function input_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include("./include/top_header.php");
		form_confirm(_("Are You Sure?"), _("Are you sure you want to delete the input item") . " <strong>'" . db_fetch_cell("select name from graph_template_item_input where id=" . $_GET["id"]) . "'</strong>? NOTE: Deleting this input will <strong>not</strong> affect graphs that use this template.", "graph_templates.php?action=edit&id=" . $_GET["graph_template_id"], "graph_templates_inputs.php?action=remove&id=" . $_GET["id"] . "&graph_template_id=" . $_GET["graph_template_id"]);
		include("./include/bottom_footer.php");
		exit;
	}

	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		api_graph_template_item_input_remove($_GET["id"]);
	}
}

function input_edit() {
	global $colors, $graph_item_types, $consolidation_functions, $fields_graph_template_input_edit;

	$header_label = "[" . _("Graph Template: ") . db_fetch_cell("select template_name from graph_template where id=" . $_GET["graph_template_id"]) . "]";

	if (!empty($_GET["id"])) {
		$graph_template_input = db_fetch_row("select * from graph_template_item_input where id=" . $_GET["id"]);
	}

	$graph_template_items = db_fetch_assoc("select
		graph_template_item.id as graph_template_item_id,
		graph_template_item.graph_item_type,
		graph_template_item.consolidation_function,
		graph_template_item_input_item.graph_template_item_input_id
		from graph_template_item
		left join graph_template_item_input_item on (graph_template_item_input_item.graph_template_item_id=graph_template_item.id and graph_template_item_input_item.graph_template_item_input_id = " . (empty($_GET["id"]) ? "NULL" : $_GET["id"]) . ")
		" . (empty($_GET["id"]) ? "where" : "where") . " graph_template_item.graph_template_id = " . $_GET["graph_template_id"] . "
		order by graph_template_item.sequence");

	/* ==================== Box: Graph Item Input ==================== */

	html_start_box("<strong>" . _("Graph Item Input") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_graph_template_input_edit, (isset($graph_template_input) ? $graph_template_input : array()))
		));

	form_alternate_row_color($colors["form_alternate1"], $colors["form_alternate2"], 0); ?>
		<td width="50%">
			<font class="textEditTitle"><?php echo _("Associated Graph Items");?></font><br>
			<?php echo _("Select the graph items to be non-templated for the field above.");?>
		</td>
		<td>
		<?php
		$i = 0;
		if (sizeof($graph_template_items) > 0) {
			foreach ($graph_template_items as $item) {
				if ($item["graph_template_item_input_id"] == "") {
					$old_value = "";
				}else{
					$old_value = "on";
				}

				if ($item["graph_item_type"] == GRAPH_ITEM_TYPE_GPRINT) {
					$start_bold = "";
					$end_bold = "";
				}else{
					$start_bold = "<strong>";
					$end_bold = "</strong>";
				}

				form_checkbox("i_" . $item["graph_template_item_id"], $old_value, "$start_bold Item #" . ($i+1) . ": " . $graph_item_types{$item["graph_item_type"]} . " (" . $consolidation_functions{$item["consolidation_function"]} . ")$end_bold", "", $_GET["graph_template_id"], true); print "<br>";

				$i++;
			}
		}else{
			print "<em>" . _("No Items") . "</em>";
		}
		?>
		</td>
	</tr>

	<?php
	html_end_box();

	form_hidden_box("id", (isset($_GET["id"]) ? $_GET["id"] : "0"), "0");
	form_hidden_box("graph_template_id", $_GET["graph_template_id"], "0");

	form_save_button("graph_templates.php?action=edit&id=" . $_GET["graph_template_id"]);
}