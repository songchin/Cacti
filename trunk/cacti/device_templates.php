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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");

$host_actions = array(
	1 => _("Delete"),
	2 => _("Duplicate")
	);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'actions':
		form_actions();

		break;
	case 'item_remove_gt':
		template_item_remove_gt();

		header("Location: device_templates.php?action=edit&id=" . $_GET["host_template_id"]);
		break;
	case 'item_remove_dq':
		template_item_remove_dq();

		header("Location: device_templates.php?action=edit&id=" . $_GET["host_template_id"]);
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		template_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		template();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_template"])) {
		$redirect_back = false;

		$save["id"] = $_POST["id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);

		if (!is_error_message()) {
			$host_template_id = sql_save($save, "host_template");

			if ($host_template_id) {
				raise_message(1);

				if (isset($_POST["add_gt_x"])) {
					db_execute("replace into host_template_graph (host_template_id,graph_template_id) values($host_template_id," . $_POST["graph_template_id"] . ")");
					$redirect_back = true;
				}elseif (isset($_POST["add_dq_x"])) {
					db_execute("replace into host_template_data_query (host_template_id,data_query_id) values($host_template_id," . $_POST["snmp_query_id"] . ")");
					$redirect_back = true;
				}
			}else{
				raise_message(2);
			}
		}

		if ((is_error_message()) || (empty($_POST["id"])) || ($redirect_back == true)) {
			header("Location: device_templates.php?action=edit&id=" . (empty($host_template_id) ? $_POST["id"] : $host_template_id));
		}else{
			header("Location: device_templates.php");
		}
	}
}

/* ------------------------
    The "actions" function
   ------------------------ */

function form_actions() {
	global $colors, $host_actions;

	/* if we are to save this form, instead of display it */
	if (isset($_POST["selected_items"])) {
		$selected_items = unserialize(stripslashes($_POST["selected_items"]));

		if ($_POST["drp_action"] == "1") { /* delete */
			db_execute("delete from host_template where " . array_to_sql_or($selected_items, "id"));
			db_execute("delete from host_template_data_query where " . array_to_sql_or($selected_items, "host_template_id"));
			db_execute("delete from host_template_graph where " . array_to_sql_or($selected_items, "host_template_id"));

			/* "undo" any device that is currently using this template */
			db_execute("update host set host_template_id=0 where " . array_to_sql_or($selected_items, "host_template_id"));
		}elseif ($_POST["drp_action"] == "2") { /* duplicate */
			for ($i=0;($i<count($selected_items));$i++) {
				duplicate_host_template($selected_items[$i], $_POST["title_format"]);
			}
		}

		header("Location: device_templates.php");
		exit;
	}

	/* setup some variables */
	$host_list = ""; $i = 0;

	/* loop through each of the host templates selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (ereg("^chk_([0-9]+)$", $var, $matches)) {
			$host_list .= "<li>" . db_fetch_cell("select name from host_template where id=" . $matches[1]) . "<br>";
			$host_array[$i] = $matches[1];
		}

		$i++;
	}

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	html_start_box("<strong>" . $host_actions{$_POST["drp_action"]} . "</strong>", "60%", $colors["header_panel_background"], "3", "center", "");

	print "<form action='device_templates.php' method='post'>\n";

	if ($_POST["drp_action"] == "1") { /* delete */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("Are you sure you want to delete the following host templates? All devices currently attached
					this these host templates will lose their template assocation.") . "</p>
					<p>$host_list</p>
				</td>
			</tr>\n
			";
	}elseif ($_POST["drp_action"] == "2") { /* duplicate */
		print "	<tr>
				<td class='textArea' bgcolor='#" . $colors["form_alternate1"]. "'>
					<p>" . _("When you click save, the following host templates will be duplicated. You can
					optionally change the title format for the new host templates.") . "</p>
					<p>$host_list</p>
					<p><strong>" . _("Title Format:") . "</strong><br>"; form_text_box("title_format", "<template_title> (1)", "", "255", "30", "text"); print "</p>
				</td>
			</tr>\n
			";
	}

	if (!isset($host_array)) {
		print "<tr><td bgcolor='#" . $colors["form_alternate1"]. "'><span class='textError'>" . _("You must select at least one host template.") . "</span></td></tr>\n";
		$save_html = "";
	}else{
		$save_html = "<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' alt='" . _("Save") . "' align='absmiddle'>";
	}

	print "	<tr>
			<td align='right' bgcolor='#" . $colors["buttonbar_background"] . "'>
				<input type='hidden' name='action' value='actions'>
				<input type='hidden' name='selected_items' value='" . (isset($host_array) ? serialize($host_array) : '') . "'>
				<input type='hidden' name='drp_action' value='" . $_POST["drp_action"] . "'>
				<a href='device_templates.php'><img src='" . html_get_theme_images_path("button_no.gif") . "' alt='" . _("Cancel") . "' align='absmiddle' border='0'></a>
				$save_html
			</td>
		</tr>
		";

	html_end_box();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* ---------------------
    Template Functions
   --------------------- */

function template_item_remove_gt() {
	db_execute("delete from host_template_graph where graph_template_id=" . $_GET["id"] . " and host_template_id=" . $_GET["host_template_id"]);
}

function template_item_remove_dq() {
	db_execute("delete from host_template_data_query where data_query_id=" . $_GET["id"] . " and host_template_id=" . $_GET["host_template_id"]);
}

function template_edit() {
	global $colors, $fields_host_template_edit;

	display_output_messages();

	if (!empty($_GET["id"])) {
		$host_template = db_fetch_row("select * from host_template where id=" . $_GET["id"]);
		$header_label = "[edit: " . $host_template["name"] . "]";
	}else{
		$header_label = "[new]";
		$_GET["id"] = 0;
	}

	html_start_box("<strong>" . _("Device Templates") . "</strong> $header_label", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_host_template_edit, (isset($host_template) ? $host_template : array()))
		));

	html_end_box();

	if (!empty($_GET["id"])) {
		html_start_box("<strong>" . _("Associated Graph Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		$selected_graph_templates = db_fetch_assoc("select
			graph_template.id,
			graph_template.template_name
			from graph_template,host_template_graph
			where graph_template.id=host_template_graph.graph_template_id
			and host_template_graph.host_template_id=" . $_GET["id"] . "
			order by graph_template.template_name");

		$available_graph_templates = db_fetch_assoc("select
			graph_template.id,
			graph_template.template_name
			from graph_template left join host_template_graph
			on (host_template_graph.graph_template_id = graph_template.id)
			where host_template_graph.graph_template_id is  null
			order by graph_template.template_name");

		$i = 0;
		if (sizeof($selected_graph_templates) > 0) {
			foreach ($selected_graph_templates as $item) {
				$i++;
				?>
				<tr bgcolor="<?php print $colors["form_alternate2"];?>">
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["template_name"];?>
					</td>
					<td align="right">
						<a href='device_templates.php?action=item_remove_gt&id=<?php print $item["id"];?>&host_template_id=<?php print $_GET["id"];?>'><img src='<?php print html_get_theme_images_path("delete_icon.gif");?>' width='10' height='10' border='0' alt='Delete'></a>
					</td>
				</tr>
				<?php
			}
		}else{
			print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td><em>" . _("No associated graph templates.") . "</em></td></tr>";
		}

		?>
		<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
			<td colspan="2">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap><?php echo _("Add Graph Template");?>:&nbsp;
						<?php form_dropdown("graph_template_id",$available_graph_templates,"template_name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="Add" name="add_gt" align="absmiddle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box();

		html_start_box("<strong>" . _("Associated Data Queries") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

		$selected_data_queries = db_fetch_assoc("select
			data_query.id,
			data_query.name
			from data_query,host_template_data_query
			where data_query.id=host_template_data_query.data_query_id
			and host_template_data_query.host_template_id=" . $_GET["id"] . "
			order by data_query.name");

		$i = 0;
		if (sizeof($selected_data_queries) > 0) {
			foreach ($selected_data_queries as $item) {
				$i++;
				?>
				<tr bgcolor="<?php print $colors["form_alternate2"];?>">
					<td style="padding: 4px;">
						<strong><?php print $i;?>)</strong> <?php print $item["name"];?>
					</td>
					<td align='right'>
						<a href='device_templates.php?action=item_remove_dq&id=<?php print $item["id"];?>&host_template_id=<?php print $_GET["id"];?>'><img src='<?php print html_get_theme_images_path("delete_icon.gif");?>' width='10' height='10' border='0' alt='Delete'></a>
					</td>
				</tr>
				<?php
			}
		}else{
			print "<tr bgcolor='#" . $colors["form_alternate2"] . "'><td><em>" . _("No associated data queries.") . "</em></td></tr>";
		}

		?>
		<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
			<td colspan="2">
				<table cellspacing="0" cellpadding="1" width="100%">
					<td nowrap><?php echo _("Add Data Query");?>:&nbsp;
						<?php form_dropdown("snmp_query_id", api_data_query_list(),"name","id","","","");?>
					</td>
					<td align="right">
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_add.gif');?>" alt="Add" name="add_dq" align="absmiddle">
					</td>
				</table>
			</td>
		</tr>

		<?php
		html_end_box();
	}

	form_save_button("device_templates.php");
}

function template() {
	global $colors, $host_actions;

	display_output_messages();

	html_start_box("<strong>" . _("Device Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "device_templates.php?action=edit");

	html_header_checkbox(array("Template Title"));

	$host_templates = db_fetch_assoc("select * from host_template order by name");

	$i = 0;
	if (sizeof($host_templates) > 0) {
	foreach ($host_templates as $host_template) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="device_templates.php?action=edit&id=<?php print $host_template["id"];?>"><?php print $host_template["name"];?></a>
			</td>
			<td style="<?php print get_checkbox_style();?>" width="1%" align="right">
				<input type='checkbox' style='margin: 0px;' name='chk_<?php print $host_template["id"];?>' title="<?php print $host_template["name"];?>">
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No Device Templates") . "</em></td></tr>";
	}
	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($host_actions);

	print "</form>\n";
}
?>
