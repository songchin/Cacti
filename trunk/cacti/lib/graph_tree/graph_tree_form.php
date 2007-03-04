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

/* form validation functions */

function api_graph_tree_fields_validate(&$_fields_graph_tree, $graph_tree_field_name_format = "|field|") {
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	if (sizeof($_fields_graph_tree) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_graph_tree = api_graph_tree_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_graph_tree)) {
		if ((isset($_fields_graph_tree[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $graph_tree_field_name_format);

			if (!form_input_validate($_fields_graph_tree[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_graph_tree_item_fields_validate(&$_fields_graph_tree_item, $graph_tree_item_field_name_format = "|field|") {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	if (sizeof($_fields_graph_tree_item) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_graph_tree_item = api_graph_tree_item_form_list();

	/* the type of input allowed in the 'item_value' field varies depending upon the selected 'item_type' */
	if (isset($_fields_graph_tree_item["item_type"])) {
		if ($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_HEADER) {
			$fields_graph_tree_item["item_value"]["validate_regexp"] = "";
		}else if ($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_GRAPH) {
			$fields_graph_tree_item["item_value"]["validate_regexp"] = "^[0-9]+$";
		}else if ($_fields_graph_tree_item["item_type"] == TREE_ITEM_TYPE_HOST) {
			$fields_graph_tree_item["item_value"]["validate_regexp"] = "^[0-9]+$";
		}
	}else{
		/* if no item type is passed in, we have no way of validating the item value field */
		unset($fields_graph_tree_item["item_value"]);
	}

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_graph_tree_item)) {
		if ((isset($_fields_graph_tree_item[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $graph_tree_item_field_name_format);

			if (!form_input_validate($_fields_graph_tree_item[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

function api_graph_tree_item_visible_field_list($graph_tree_item_type) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");

	$visible_fields = array();
	if ($graph_tree_item_type == TREE_ITEM_TYPE_HEADER) {
		$visible_fields = array("item_type", "item_value", "sort_children_type");
	}else if ($graph_tree_item_type == TREE_ITEM_TYPE_GRAPH) {
		$visible_fields = array("item_type", "item_value");
	}else if ($graph_tree_item_type == TREE_ITEM_TYPE_HOST) {
		$visible_fields = array("item_type", "item_value", "device_grouping_type");
	}

	return $visible_fields;
}

/* graph tree fields */

function _graph_tree_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A useful name for this graph tree.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _graph_tree_field__sort_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Sorting Type");?></span><br>
			<?php echo _("Choose how items in this tree will be sorted.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_graph_tree_sort_type_list(), "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

/* graph tree item fields */

function _graph_tree_item_field__parent_item_id($graph_tree_id, $field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_tree.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Parent Branch");?></span><br>
			<?php echo _("Choose a parent branch for this item which controls where on the tree this branch lies.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php html_tree_dropdown_draw($graph_tree_id, $field_name, $field_value);?>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__item_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/include/graph_tree/graph_tree_constants.php");
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Tree Item Type");?></span><br>
			<?php echo _("Choose whether this tree item is to represent a branch header, a device, or an individual graph.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_graph_tree_item_type_list(), "", "", $field_value, "", 1, "", "", "update_graph_item_type_function(this.value, $field_id)");?>
		</td>
	</tr>
	<script language="JavaScript">
	<!--
	function update_graph_item_type_function(graph_tree_item_type, row_id) {
		if (graph_tree_item_type == <?php echo TREE_ITEM_TYPE_HEADER;?>) {
			document.getElementById('row_field_title_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_sort_children_type_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_propagate_changes_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_graph_' + row_id).style.display = 'none';
			document.getElementById('row_field_device_' + row_id).style.display = 'none';
			document.getElementById('row_field_device_grouping_type_' + row_id).style.display = 'none';
		}else if (graph_tree_item_type == <?php echo TREE_ITEM_TYPE_GRAPH;?>) {
			document.getElementById('row_field_title_' + row_id).style.display = 'none';
			document.getElementById('row_field_sort_children_type_' + row_id).style.display = 'none';
			document.getElementById('row_field_propagate_changes_' + row_id).style.display = 'none';
			document.getElementById('row_field_graph_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_device_' + row_id).style.display = 'none';
			document.getElementById('row_field_device_grouping_type_' + row_id).style.display = 'none';
		}else if (graph_tree_item_type == <?php echo TREE_ITEM_TYPE_HOST;?>) {
			document.getElementById('row_field_title_' + row_id).style.display = 'none';
			document.getElementById('row_field_sort_children_type_' + row_id).style.display = 'none';
			document.getElementById('row_field_propagate_changes_' + row_id).style.display = 'none';
			document.getElementById('row_field_graph_' + row_id).style.display = 'none';
			document.getElementById('row_field_device_' + row_id).style.display = 'table-row';
			document.getElementById('row_field_device_grouping_type_' + row_id).style.display = 'table-row';
		}
	}
	-->
	</script>
	<?php
}

function _graph_tree_item_field__item_type_js_update($field_value, $field_id = 0) {
	?>
	<script language="JavaScript">
	<!--
	update_graph_item_type_function('<?php echo $field_value;?>', '<?php echo $field_id;?>');
	-->
	</script>
	<?php
}

function _graph_tree_item_field__title($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_title_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Title");?></span><br>
			<?php echo _("Enter a title for this branch header.");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 255, 30, "text", $field_id);?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__sort_children_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_sort_children_type_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Sorting Type");?></span><br>
			<?php echo _("Choose how children of this branch will be sorted.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_graph_tree_sort_type_list(), "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__propagate_changes($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_propagate_changes_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Propagate Sorting Type");?></span><br>
			<?php echo _("Propagate the selected sorting type to all child branch header items.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_checkbox($field_name, $field_value, "Propagate Sorting Type", "", $field_id);?>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__graph($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_graph_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Graph");?></span><br>
			<?php echo _("Choose a graph from this list to add it to the tree.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php
			form_dropdown($field_name, api_graph_list(), "title_cache", "id", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__device($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/device/device_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_device_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Device");?></span><br>
			<?php echo _("Choose a device from this list to add it to the tree.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php
			$devices = api_device_list();

			$_devices = array();
			if (is_array($devices)) {
				foreach ($devices as $device) {
					$_devices{$device["id"]} = $device["description"] . " (" . $device["hostname"] . ")";
				}
			}

			form_dropdown($field_name, $_devices, "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

function _graph_tree_item_field__device_grouping_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/graph_tree/graph_tree_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_device_grouping_type_<?php echo $field_id;?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Graph Grouping Style");?></span><br>
			<?php echo _("Choose how graphs are grouped when drawn for this particular host on the tree.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_graph_tree_item_device_grouping_type_list(), "", "", $field_value, "", 1);?>
		</td>
	</tr>
	<?php
}

?>
