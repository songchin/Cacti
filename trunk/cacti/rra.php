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

include("./include/auth.php");

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
		include_once("./include/top_header.php");
		
		rra_edit();
		
		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");
		
		rra();
		
		include_once("./include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_rra"])) {
		$save["id"] = $_POST["id"];
		$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save["x_files_factor"] = form_input_validate($_POST["x_files_factor"], "x_files_factor", "^[0-9]+(\.[0-9])?$", false, 3);
		$save["steps"] = form_input_validate($_POST["steps"], "steps", "^[0-9]*$", false, 3);
		$save["rows"] = form_input_validate($_POST["rows"], "rows", "^[0-9]*$", false, 3);
		$save["timespan"] = form_input_validate($_POST["timespan"], "timespan", "^[0-9]*$", false, 3);
		
		if (!is_error_message()) {
			$rra_id = sql_save($save, "rra");
			
			if ($rra_id) {
				raise_message(1);
				
				db_execute("delete from rra_cf where rra_id=$rra_id"); 
				
				if (isset($_POST["consolidation_function_id"])) {
					for ($i=0; ($i < count($_POST["consolidation_function_id"])); $i++) {
						db_execute("insert into rra_cf (rra_id,consolidation_function_id) 
							values ($rra_id," . $_POST["consolidation_function_id"][$i] . ")");
					}
				}
			}else{
				raise_message(2);
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
		include_once("./include/top_header.php");
		form_confirm("Are You Sure?", "Are you sure you want to delete the round robin archive <strong>'" . db_fetch_cell("select name from rra where id=" . $_GET["id"]) . "'</strong>?", $_SERVER["HTTP_REFERER"], "rra.php?action=remove&id=" . $_GET["id"]);
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
		$header_label = "[edit: " . $rra["name"] . "]";
	}else{
		$header_label = "[new]";
	}
	
	start_box("<strong>Round Robin Archives</strong> $header_label", "98%", $colors["header"], "3", "center", "");
	
	draw_edit_form(array(
		"config" => array(),
		"fields" => inject_form_variables($fields_rra_edit, (isset($rra) ? $rra : array()))
		));
	
	end_box();
	
	form_save_button("rra.php");	
}

function rra() {
	global $colors;
	
	start_box("<strong>Round Robin Archives</strong>", "98%", $colors["header"], "3", "center", "rra.php?action=edit");
	
	print "<tr bgcolor='#" . $colors["header_panel"] . "'>";
		DrawMatrixHeaderItem("Name",$colors["header_text"],1);
		DrawMatrixHeaderItem("Steps",$colors["header_text"],1);
		DrawMatrixHeaderItem("Rows",$colors["header_text"],1);
		DrawMatrixHeaderItem("Timespan",$colors["header_text"],2);
	print "</tr>";
    
	$rras = db_fetch_assoc("select id,name,rows,steps,timespan from rra order by steps");
	
	$i = 0;
	if (sizeof($rras) > 0) {
	foreach ($rras as $rra) {
		form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
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
			<td width="1%" align="right">
				<a href="rra.php?action=remove&id=<?php print $rra["id"];?>"><img src="images/delete_icon.gif" width="10" height="10" border="0" alt="Delete"></a>&nbsp;
			</td>
		</tr>
	<?php
	}
	}
	end_box();	
}
?>
