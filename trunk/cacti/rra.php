<?/* 
   +-------------------------------------------------------------------------+
   | Copyright (C) 2002 Ian Berry                                            |
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
   | cacti: the rrdtool frontend [php-auth, php-tree, php-form]              |
   +-------------------------------------------------------------------------+
   | This code is currently maintained and debugged by Ian Berry, any        |
   | questions or comments regarding this code should be directed to:        |
   | - iberry@raxnet.net                                                     |
   +-------------------------------------------------------------------------+
   | - raXnet - http://www.raxnet.net/                                       |
   +-------------------------------------------------------------------------+
   */?>
<?
$section = "Add/Edit Round Robin Archives"; include ('include/auth.php');
include_once ('include/config_arrays.php');
include_once ('include/functions.php');
include_once ('include/form.php');

switch ($_REQUEST["action"]) {
	case 'save':
		$redirect_location = form_save();
		
		header ("Location: $redirect_location"); exit;
		break;
    	case 'remove':
		rra_remove();
		
		header ("Location: rra.php");
		break;
	case 'edit':
		include_once ("include/top_header.php");
		
		rra_edit();
		
		include_once ("include/bottom_footer.php");
		break;
	default:
		include_once ("include/top_header.php");
		
		rra();
		
		include_once ("include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_rra"])) {
		return rra_save();
	}
}

/* -------------------
    RRA Functions
   ------------------- */

function rra_save() {
	$save["id"] = $_POST["id"];
	$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
	$save["x_files_factor"] = form_input_validate($_POST["x_files_factor"], "x_files_factor", "^[0-9]+(\.[0-9])?$", false, 3);
	$save["steps"] = form_input_validate($_POST["steps"], "steps", "^[0-9]*$", false, 3);
	$save["rows"] = form_input_validate($_POST["rows"], "rows", "^[0-9]*$", false, 3);
	
	if (!is_error_message()) {
		$rra_id = sql_save($save, "rra");
		
		db_execute("delete from rra_cf where rra_id=$rra_id"); 
		
		for ($i=0; ($i < count($_POST["consolidation_function_id"])); $i++) {
			db_execute("insert into rra_cf (rra_id,consolidation_function_id) 
				values ($rra_id," . $_POST["consolidation_function_id"][$i] . ")");
		}
	}
	
	if (is_error_message()) {
		return "rra.php?action=edit&id=" . $_POST["id"];
	}else{
		return "rra.php";
	}
}

function rra_remove() {
	if (($config["remove_verification"]["value"] == "on") && ($_GET["confirm"] != "yes")) {
		include_once ('include/top_header.php');
		DrawConfirmForm("Are You Sure?", "Are you sure you want to delete this round robin archive?", getenv("HTTP_REFERER"), "rra.php?action=remove&id=" . $_GET["id"]);
		exit;
	}
	
	if (($config["remove_verification"]["value"] == "") || ($_GET["confirm"] == "yes")) {
		db_execute("delete from rra where id=" . $_GET["id"]);
		db_execute("delete from rra_cf where rra_id=" . $_GET["id"]);
    	}	
}

function rra_edit() {
	global $colors, $consolidation_functions;
	
	display_output_messages();
	
	start_box("<strong>Round Robin Archives [edit]</strong>", "", "");
	
	if (isset($_GET["id"])) {
		$rra = db_fetch_row("select * from rra where id=" . $_GET["id"]);
	}else{
		unset($rra);
	}
	
	?>
	<form method="post" action="rra.php">
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Name</font><br>
			How data is to be entered in RRA's.
		</td>
		<?DrawFormItemTextBox("name",$rra["name"],"","100", "40");?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">Consolidation Functions</font><br>
			How data is to be entered in RRA's.
		</td>
		<?DrawFormItemMultipleList("consolidation_function_id",$consolidation_functions,db_fetch_assoc("select * from rra_cf where rra_id=" . $_GET["id"]), "consolidation_function_id");?>
	</tr>
    
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">X-Files Factor</font><br>
			The amount of unknown data that can still be regarded as known.
		</td>
		<?DrawFormItemTextBox("x_files_factor",$rra["x_files_factor"],"","10", "40");?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">Steps</font><br>
			How many data points are needed to put data into the RRA.
		</td>
		<?DrawFormItemTextBox("steps",$rra["steps"],"","8", "40");?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Rows</font><br>
			How many generations data is kept in the RRA.
		</td>
		<?DrawFormItemTextBox("rows",$rra["rows"],"","8", "40");?>
	</tr>
	
	<?
	DrawFormItemHiddenIDField("id",$_GET["id"]);
	DrawFormItemHiddenTextBox("save_component_rra","1","");
	?>
	
	<tr bgcolor="#FFFFFF">
		 <td colspan="2" align="right" background="images/blue_line.gif">
			<?DrawFormSaveButton("save", "rra.php");?>
			</form>
		</td>
	</tr>
	<?
	end_box();	
}

function rra() {
	global $colors;
	
	display_output_messages();
	
	start_box("<strong>Round Robin Archives</strong>", "", "rra.php?action=edit");
	
	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("Name",$colors["header_text"],1);
		DrawMatrixHeaderItem("Steps",$colors["header_text"],1);
		DrawMatrixHeaderItem("Rows",$colors["header_text"],2);
	print "</tr>";
    
	$rras = db_fetch_assoc("select id,name,rows,steps from rra order by steps");
	
	if (sizeof($rras) > 0) {
	foreach ($rras as $rra) {
		DrawMatrixRowAlternateColorBegin($colors["alternate"],$colors["light"],$i); $i++;
			?>
			<td>
				<a class="linkEditMain" href="rra.php?action=edit&id=<?print $rra["id"];?>"><?print $rra["name"];?></a>
			</td>
			<td>
				<?print $rra["steps"];?>
			</td>
			<td>
				<?print $rra["rows"];?>
			</td>
			<td width="1%" align="right">
				<a href="rra.php?action=remove&id=<?print $rra["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>&nbsp;
			</td>
		</tr>
	<?
	}
	}
	end_box();	
}
?>
