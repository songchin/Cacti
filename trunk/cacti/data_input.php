<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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

include ('include/auth.php');
include_once ("include/functions.php");
include_once ("include/config_arrays.php");
include_once ('include/form.php');

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();
		
		break;
	case 'field_remove':
		field_remove();
	    
		header ("Location: data_input.php?action=edit&id=" . $_GET["data_input_id"]);
		break;
	case 'field_edit':
		include_once ("include/top_header.php");
		
		field_edit();
		
		include_once ("include/bottom_footer.php");
		break;
	case 'remove':
		data_remove();
		
		header ("Location: data_input.php");
		break;
	case 'edit':
		include_once ("include/top_header.php");
		
		data_edit();
		
		include_once ("include/bottom_footer.php");
		break;
	default:
		include_once ("include/top_header.php");
		
		data();
		
		include_once ("include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $registered_cacti_names;
	
	if (isset($_POST["save_component_data_input"])) {
		$save["id"] = $_POST["id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["input_string"] = form_input_validate($_POST["input_string"], "input_string", "", true, 3);
		$save["output_string"] = form_input_validate($_POST["output_string"], "output_string", "", true, 3);
		$save["type_id"] = form_input_validate($_POST["type_id"], "type_id", "", true, 3);
		
		if (!is_error_message()) {
			$data_input_id = sql_save($save, "data_input");
			
			if ($data_input_id) {
				raise_message(1);
				
				/* get a list of each field so we can note their sequence of occurance in the database */
				if (!empty($_POST["id"])) {
					db_execute("update data_input_fields set sequence=0 where data_input_id=" . $_POST["id"]);
					
					generate_data_input_field_sequences($_POST["input_string"], $_POST["id"], "in");
					generate_data_input_field_sequences($_POST["output_string"], $_POST["id"], "out");
				}
			}else{
				raise_message(2);
			}
		}
		
		if ((is_error_message()) || (empty($_POST["id"]))) {
			header ("Location: data_input.php?action=edit&id=" . (empty($data_input_id) ? $_POST["id"] : $data_input_id));
		}else{
			header ("Location: data_input.php");
		}
	}elseif (isset($_POST["save_component_field"])) {
		$save["id"] = $_POST["id"];
		$save["data_input_id"] = $_POST["data_input_id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["data_name"] = form_input_validate($_POST["data_name"], "data_name", "", true, 3);
		$save["input_output"] = $_POST["input_output"];
		$save["update_rra"] = form_input_validate((isset($_POST["update_rra"]) ? $_POST["update_rra"] : ""), "update_rra", "", true, 3);
		$save["sequence"] = $_POST["sequence"];
		$save["type_code"] = form_input_validate((isset($_POST["type_code"]) ? $_POST["type_code"] : ""), "type_code", "", true, 3);
		$save["regexp_match"] = form_input_validate((isset($_POST["regexp_match"]) ? $_POST["regexp_match"] : ""), "regexp_match", "", true, 3);
		$save["allow_nulls"] = form_input_validate((isset($_POST["allow_nulls"]) ? $_POST["allow_nulls"] : ""), "allow_nulls", "", true, 3);
		
		if (!is_error_message()) {
			$data_input_field_id = sql_save($save, "data_input_fields");
			
			if ($data_input_field_id) {
				raise_message(1);
				
				if (!empty($data_input_field_id)) {
					generate_data_input_field_sequences(db_fetch_cell("select " . $_POST["input_output"] . "put_string from data_input where id=" . $_POST["data_input_id"]), $_POST["data_input_id"], $_POST["input_output"]);
				}
			}else{
				raise_message(2);
			}
		}
		
		if (is_error_message()) {
			header ("Location: data_input.php?action=field_edit&data_input_id=" . $_POST["data_input_id"] . "&id=" . (empty($data_input_field_id) ? $_POST["id"] : $data_input_field_id) . (!empty($_POST["input_output"]) ? "&type=" . $_POST["input_output"] : ""));
		}else{
			header ("Location: data_input.php?action=edit&id=" . $_POST["data_input_id"]);
		}
	}
}

/* --------------------------
    CDEF Item Functions
   -------------------------- */

function field_remove() {
	global $registered_cacti_names;
	
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include ('include/top_header.php');
		form_confirm("Are You Sure?", "Are you sure you want to delete the field <strong>'" . db_fetch_cell("select name from data_input_fields where id=" . $_GET["id"]) . "'</strong>?", $_SERVER["HTTP_REFERER"], "data_input.php?action=field_remove&id=" . $_GET["id"] . "&data_input_id=" . $_GET["data_input_id"]);
		include ('include/bottom_footer.php');
		exit;
	}
	
	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		/* get information about the field we're going to delete so we can re-order the seqs */
		$field = db_fetch_row("select input_output,data_input_id from data_input_fields where id=" . $_GET["id"]);
		
		db_execute("delete from data_input_fields where id=" . $_GET["id"]);
		db_execute("delete from data_input_data where data_input_field_id=" . $_GET["id"]);
		
		/* when a field is deleted; we need to re-order the field sequences */
		if (preg_match_all("/<([_a-zA-Z0-9]+)>/", db_fetch_cell("select " . $field["input_output"] . "put_string from data_input where id=" . $field["data_input_id"]), $matches)) {
			$j = 0;
			for ($i=0; ($i < count($matches[1])); $i++) {
				if (in_array($matches[1][$i], $registered_cacti_names) == false) {
					$j++; db_execute("update data_input_fields set sequence=$j where data_input_id=" . $field["data_input_id"] . " and input_output='" .  $field["input_output"]. "' and data_name='" . $matches[1][$i] . "'");
				}
			}
		}
	}
}

function field_edit() {
	global $colors, $registered_cacti_names;
	
	if (!empty($_GET["id"])) {
		$field = db_fetch_row("select * from data_input_fields where id=" . $_GET["id"]);
	}
	
	if (!empty($_GET["type"])) {
		$current_field_type = $_GET["type"];
	}else{
		$current_field_type = $field["input_output"];
	}
	
	if ($current_field_type == "out") {
		$header_name = "Output";
	}elseif ($current_field_type == "in") {
		$header_name = "Input";
	}
	
	$data_input = db_fetch_row("select type_id,name from data_input where id=" . $_GET["data_input_id"]);
	
	/* obtain a list of available fields for this given field type (input/output) */
	if (preg_match_all("/<([_a-zA-Z0-9]+)>/", db_fetch_cell("select $current_field_type" . "put_string from data_input where id=" . ($_GET["data_input_id"] ? $_GET["data_input_id"] : $field["data_input_id"])), $matches)) {
		for ($i=0; ($i < count($matches[1])); $i++) {
			if (in_array($matches[1][$i], $registered_cacti_names) == false) {
				$current_field_name = $matches[1][$i];
				$array_field_names[$current_field_name] = $current_field_name;
			}
		}
	}
	
	/* if there are no input fields to choose from, complain */
	if ((!isset($array_field_names)) && (isset($_GET["type"]) ? $_GET["type"] == "in" : false) && ($data_input["type_id"] == "1")) {
		print "<span class='textError'>This script appears to have no input values, therefore there is nothing to add.</span>";
		return;
	}
	
	start_box("<strong>$header_name Fields</strong> [edit: " . $data_input["name"] . "]", "98%", $colors["header"], "3", "center", "");
	
	?>
	<form method="post" action="data_input.php">
	
	<?php
	$i = 0;
	if (($data_input["type_id"] == "1") && ($current_field_type == "in")) { /* script */
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
			<td width="50%">
				<font class="textEditTitle">Field [<?php print $header_name;?>]</font><br>
				Choose the associated field from the <?php print $header_name;?> field.
			</td>
			<?php form_dropdown("data_name",$array_field_names,"","",(isset($field) ? $field["data_name"] : ""),"","");?>
		</tr><?php
	}elseif (($data_input["type_id"] == "2") || ($data_input["type_id"] == "3") || ($data_input["type_id"] == "4") || ($current_field_type == "out")) { /* snmp */
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
			<td width="50%">
				<font class="textEditTitle">Field Name [<?php print $header_name;?>]</font><br>
				Enter a name for this <?php print $header_name;?> field.
			</td>
			<?php form_text_box("data_name",(isset($field) ? $field["data_name"] : ""),"","50", "40");?>
		</tr><?php
	}
	
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Friendly Name</font><br>
			Enter a meaningful name for this data input method.
		</td>
		<?php form_text_box("name",(isset($field) ? $field["name"] : ""),"","200", "40");?>
	</tr>
	
	<?php
	if ($current_field_type == "out") {
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Update RRD File</font><br>
			Whether data from this output field is to be entered into the rrd file.
		</td>
		<?php form_checkbox("update_rra",(isset($field) ? $field["update_rra"] : ""),"Update RRD File","on",(isset($field) ? $field["id"] : ""));?>
	</tr>
	<?php
	}
	
	if ($current_field_type == "in") {
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Regular Expression Match</font><br>
			If you want to require a certain regular expression to be matched againt input data, enter it here (ereg format).
		</td>
		<?php form_text_box("regexp_match",(isset($field) ? $field["regexp_match"] : ""),"","200", "40");?>
	</tr>
	<?php
	}
	
	if ($current_field_type == "in") {
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Allow Empty Input</font><br>
			Check here if you want to allow NULL input in this field from the user.
		</td>
		<?php form_checkbox("allow_nulls",(isset($field) ? $field["allow_nulls"] : ""),"Allow NULL's","",false);?>
	</tr>
	<?php
	}
	
	if ($current_field_type == "in") {
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Special Type Code</font><br>
			If this field should be treated specially by host templates, indicate so here. Valid keywords for this field are 'hostname', 'management_ip', 'snmp_community', 'snmp_username', 'snmp_password', and 'snmp_version'.
		</td>
		<?php form_text_box("type_code",(isset($field) ? $field["type_code"] : ""),"","40", "40");?>
	</tr>
	<?php
	}
	
	form_hidden_id("id",(isset($field) ? $field["id"] : "0"));
	form_hidden_box("input_output",$current_field_type,"");
	form_hidden_box("sequence",(isset($field) ? $field["sequence"] : "0"),"");
	form_hidden_box("data_input_id",$_GET["data_input_id"],(isset($field) ? $field["data_input_id"] : "0"));
	form_hidden_box("save_component_field","1","");
	end_box();
	
	form_save_button("data_input.php?action=edit&id=" . $_GET["data_input_id"]);	
}
   
/* -----------------------
    Data Input Functions
   ----------------------- */

function data_remove() {
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include ('include/top_header.php');
		form_confirm("Are You Sure?", "Are you sure you want to delete the data input method <strong>'" . db_fetch_cell("select name from data_input where id=" . $_GET["id"]) . "'</strong>?", $_SERVER["HTTP_REFERER"], "data_input.php?action=remove&id=" . $_GET["id"]);
		include ('include/bottom_footer.php');
		exit;
	}
	
	if ((read_config_option("remove_verification") == "") || (isset($_GET["confirm"]))) {
		db_execute("delete from data_input where id=" . $_GET["id"]);
		db_execute("delete from data_input_fields where data_input_id=" . $_GET["id"]);
		db_execute("delete from data_input_data where data_input_id=" . $_GET["id"]);
	}
}

function data_edit() {
	global $colors, $input_types;
	
	if (!empty($_GET["id"])) {
		$data_input = db_fetch_row("select * from data_input where id=" . $_GET["id"]);
		$header_label = "[edit: " . $data_input["name"] . "]";
	}else{
		$header_label = "[new]";
	}
	
	start_box("<strong>Data Input Methods</strong> $header_label", "98%", $colors["header"], "3", "center", "");
	
	?>
	<form method="post" action="data_input.php">
	
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Name</font><br>
			Enter a meaningful name for this data input method.
		</td>
		<?php form_text_box("name",(isset($data_input) ? $data_input["name"] : ""),"","255", "40");?>
	</tr>
	
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">Input Type</font><br>
			Choose what type of data input method this is.
		</td>
		<?php form_dropdown("type_id",$input_types,"","",(isset($data_input) ? $data_input["type_id"] : ""),"","");?>
	</tr>
	
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Input String</font><br>
			The data that in sent to the script, which includes the complete path to the script and input sources in &lt;&gt; brackets.
		</td>
		<?php form_text_box("input_string",(isset($data_input) ? $data_input["input_string"] : ""),"","255", "40");?>
	</tr>
	
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">Output String</font><br>
			The data that is expected back from the input script; defined as &lt;&gt; brackets.
		</td>
		<?php form_text_box("output_string",(isset($data_input) ? $data_input["output_string"] : ""),"","255", "40");?>
	</tr>
	
	<?php
	form_hidden_id("id",(isset($data_input) ? $data_input["id"] : "0"));
	end_box();
	
	if (!empty($_GET["id"])) {
		start_box("<strong>Input Fields</strong>", "98%", $colors["header"], "3", "center", "data_input.php?action=field_edit&type=in&data_input_id=" . $_GET["id"]);
		print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
			DrawMatrixHeaderItem("Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Field Order",$colors["header_text"],1);
			DrawMatrixHeaderItem("Friendly Name",$colors["header_text"],2);
		print "</tr>";
	    
		$fields = db_fetch_assoc("select id,data_name,name,sequence from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='in' order by sequence");
		
		$i = 0;
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="data_input.php?action=field_edit&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><?php print $field["data_name"];?></a>
				</td>
				<td>
					<?php print $field["sequence"]; if ($field["sequence"] == "0") { print " (Not In Use)"; }?>
				</td>
				<td>
					<?php print $field["name"];?>
				</td>
				<td width="1%" align="right">
					<a href="data_input.php?action=field_remove&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>&nbsp;
				</td>
			</tr>
		<?php
		}
		}else{
			print "<tr><td><em>No Input Fields</em></td></tr>";
		}
		end_box();
		
		start_box("<strong>Output Fields</strong>", "98%", $colors["header"], "3", "center", "data_input.php?action=field_edit&type=out&data_input_id=" . $_GET["id"]);
		print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
			DrawMatrixHeaderItem("Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Field Order",$colors["header_text"],1);
			DrawMatrixHeaderItem("Friendly Name",$colors["header_text"],1);
			DrawMatrixHeaderItem("Update RRA",$colors["header_text"],2);
		print "</tr>";
	
		$fields = db_fetch_assoc("select id,name,data_name,update_rra,sequence from data_input_fields where data_input_id=" . $_GET["id"] . " and input_output='out' order by sequence");
		
		$i = 0;
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="data_input.php?action=field_edit&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><?php print $field["data_name"];?></a>
				</td>
				<td>
					<?php print $field["sequence"]; if ($field["sequence"] == "0") { print " (Not In Use)"; }?>
				</td>
				<td>
					<?php print $field["name"];?>
				</td>
				<td>
					<?php print html_boolean_friendly($field["update_rra"]);?>
				</td>
				<td width="1%" align="right">
					<a href="data_input.php?action=field_remove&id=<?php print $field["id"];?>&data_input_id=<?php print $_GET["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>&nbsp;
				</td>
			</tr>
		<?php
		}
		}else{
			print "<tr><td><em>No Output Fields</em></td></tr>";
		}
		end_box();
	}
	
	form_hidden_box("save_component_data_input","1","");
	
	form_save_button("data_input.php");
}

function data() {
	global $colors;
	
	start_box("<strong>Data Input Methods</strong>", "98%", $colors["header"], "3", "center", "data_input.php?action=edit");
	
	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("Name",$colors["header_text"],1);
		DrawMatrixHeaderItem("&nbsp;",$colors["header_text"],1);
	print "</tr>";
    	
	$data_inputs = db_fetch_assoc("select * from data_input order by name");
	
	$i = 0;
	if (sizeof($data_inputs) > 0) {
	foreach ($data_inputs as $data_input) {
		form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="data_input.php?action=edit&id=<?php print $data_input["id"];?>"><?php print $data_input["name"];?></a>
			</td>
			<td width="1%" align="right">
				<a href="data_input.php?action=remove&id=<?php print $data_input["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>&nbsp;
			</td>
		</tr>
	<?php
	}
	}else{
		print "<tr><td><em>No Data Input Methods</em></td></tr>";
	}
	end_box();	
}
?>
