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
$section = "Add/Edit Graphs"; 
include ('include/auth.php');
header("Cache-control: no-cache");

include_once ("include/functions.php");
include_once ("include/cdef_functions.php");
include_once ("include/config_arrays.php");
include_once ('include/form.php');

if (isset($form[action])) { $action = $form[action]; } else { $action = $args[action]; }
if (isset($form[ID])) { $id = $form[ID]; } else { $id = $args[id]; }

switch ($action) {
	case 'save':
		$redirect_location = form_save();
		
		header ("Location: $redirect_location"); exit;
		break;
	case 'tree':
		include_once ("include/top_header.php");
		
		tree();
		
		include_once ("include/bottom_footer.php");
		break;
	case 'tree_moveup': 
		tree_moveup();
		
		header ("Location: data_sources.php?action=tree");
		break;
	case 'tree_movedown':
		tree_movedown();
		
		header ("Location: data_sources.php?action=tree");
		break;
	case 'leaf_remove':
		leaf_remove();

		header ("Location: data_sources.php?action=tree");
		break;
	case 'remove':
		ds_remove();
		
		header ("Location: data_sources.php");
		break;
	case 'edit':
		include_once ("include/top_header.php");
		
		ds_edit();
		
		include_once ("include/bottom_footer.php");
		break;
	default:
		include_once ("include/top_header.php");
		
		ds();
		
		include_once ("include/bottom_footer.php");
		break;
}


/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	global $form;
	
	if (isset($form[save_component_data_source])) {
		$local_data_id = ds_save();
		
		if ($form[data_template_id] != $form[_data_template_id]) {
			return "data_sources.php?action=edit&local_data_id=$local_data_id&host_id=$form[host_id]&view_rrd=$form[view_rrd]";
		}else{
			return "data_sources.php";
		}
	}
}


/* --------------------------
    Global Form Functions
   -------------------------- */

function draw_tabs() {
?>
		<tr height="33">
			<td valign="bottom" colspan="30" background="images/tab_back.gif">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td nowrap class="textTab" align="center" background="images/tab_middle.gif">
							<img src="images/tab_left.gif" border="0" align="absmiddle"><a class="linkTabs" href="data_sources.php">Data Sources</a><img src="images/tab_right.gif" border="0" align="absmiddle">
						</td>
						<td nowrap class="textTab" align="center" background="images/tab_middle.gif">
							<img src="images/tab_left.gif" border="0" align="absmiddle"><a class="linkTabs" href="data_sources.php?action=tree">Data Source Tree</a><img src="images/tab_right.gif" border="0" align="absmiddle">
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?	
}

/* ----------------------
    Tree Edit Functions
   ---------------------- */

function tree() {
	include_once ('include/tree_view_functions.php');
	
	start_box("<strong>Data Source Tree</strong>", "", "data_sources.php?action=edit");
	
	$tree_parameters[edit_mode] = true;
	
	draw_tabs();
    	grow_polling_tree($start_branch, 1, $tree_parameters);
	
	end_box();
}

function tree_moveup() {
	include_once("include/tree_functions.php");
	global $args;
	
	$order_key = db_fetch_cell("SELECT order_key FROM data_tree WHERE id=$args[branch_id]");
	if ($order_key > 0) { branch_up($order_key, 'data_tree', 'order_key', ''); }
}

function tree_movedown() {
	include_once("include/tree_functions.php");
	global $args;
	
	$order_key = db_fetch_cell("SELECT order_key FROM data_tree WHERE id=$args[branch_id]");
	if ($order_key > 0) { branch_down($order_key, 'data_tree', 'order_key', ''); }
}

/* ---------------------
    Template Functions
   --------------------- */
function leaf_remove() {
	global $args, $config;
	
	if (($config["remove_verification"]["value"] == "on") && ($args[confirm] != "yes")) {
		include ('include/top_header.php');
		DrawConfirmForm("Are You Sure?", "Are you sure you want to delete the data source tree <strong>'" . db_fetch_cell("select title from data_tree where id=$args[id]") . "'</strong>?", getenv("HTTP_REFERER"), "data_sources.php?action=leaf_remove&id=$args[id]");
		include ('include/bottom_footer.php');
		exit;
	}

	if (($config["remove_verification"]["value"] == "") || ($args[confirm] == "yes")) {
		db_execute("delete from data_tree where id=$args[id]");
	}
}

function ds_remove() {
	global $args, $config;
	
	if (($config["remove_verification"]["value"] == "on") && ($args[confirm] != "yes")) {
		include ('include/top_header.php');
		DrawConfirmForm("Are You Sure?", "Are you sure you want to delete the data source <strong>'" . db_fetch_cell("select name from data_template_data where local_data_id=$args[local_data_id]") . "'</strong>?", getenv("HTTP_REFERER"), "data_sources.php?action=remove&local_data_id=$args[local_data_id]");
		include ('include/bottom_footer.php');
		exit;
	}
	
	if (($config["remove_verification"]["value"] == "") || ($args[confirm] == "yes")) {
		db_execute("delete from data_template_data where local_data_id=$args[local_data_id]");
		db_execute("delete from data_template_rrd where local_data_id=$args[local_data_id]");
		db_execute("delete from data_local where id=$args[local_data_id]");
	}
}

function ds_save() {
	global $form;
	
	$save["id"] = $form["local_data_id"];
	$save["data_template_id"] = $form["data_template_id"];
	$save["host_id"] = $form["host_id"];
	
	$local_data_id = sql_save($save, "data_local");
	unset($save);
	
	$save["id"] = $form["data_template_data_id"];
	$save["local_data_template_data_id"] = $form["local_data_template_data_id"];
	$save["local_data_id"] = $local_data_id;
	$save["data_template_id"] = $form["data_template_id"];
	$save["data_input_id"] = $form["data_input_id"];
	$save["name"] = $form["name"];
	$save["data_source_path"] = $form["data_source_path"];
	$save["active"] = $form["active"];
	$save["rrd_step"] = $form["rrd_step"];
	
	sql_save($save, "data_template_data");
	unset($save);
	
	/* if this is a new data source and a template has been selected, skip item creation this time
	otherwise it throws off the templatlate creation because of the NULL data */
	if (($form["data_template_id"] == "0") || ($form["data_template_rrd_id"] != "0")) {
		$save["id"] = $form["data_template_rrd_id"];
		$save["local_data_template_rrd_id"] = $form["local_data_template_rrd_id"];
		$save["local_data_id"] = $local_data_id;
		$save["data_template_id"] = $form["data_template_id"];
		$save["rrd_maximum"] = $form["rrd_maximum"];
		$save["rrd_minimum"] = $form["rrd_minimum"];
		$save["rrd_heartbeat"] = $form["rrd_heartbeat"];
		$save["data_source_type_id"] = $form["data_source_type_id"];
		$save["data_source_name"] = $form["data_source_name"];
		$save["script_output_argument"] = $form["script_output_argument"];
		
		sql_save($save, "data_template_rrd");
	}
	
	if ($form[data_template_id] != $form[_data_template_id]) {
		/* update all nessesary template information */
		include_once ("include/utility_functions.php");
		$return_status = change_data_template($local_data_id, $form[data_template_id], $form[_data_template_id]);
	}
	
	return $local_data_id;
}

function ds_edit() {
	global $args, $config, $colors;
	
	$use_data_template = true;
	
	if (isset($args[local_data_id])) {
		$local_data_template_data_id = db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=$args[local_data_id]");
		
		$data = db_fetch_row("select * from data_template_data where local_data_id=$args[local_data_id]");
		$data_template = db_fetch_row("select * from data_template_data where id=$local_data_template_data_id");
	}else{
		unset($data_template);
		unset($data);
		
		$use_data_template = false;
	}
	
	if ($data[data_template_id] == "0") {
		$use_data_template = false;
	}
	
	$data_template_name = db_fetch_cell("select name from data_template where id=$data[data_template_id]");
	
	?>
	<form method="post" action="data_sources.php">
	<?
	start_box("Data Templation Selection", "", "");	
	
	DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],0); ?>
		<td width="50%">
			<font class="textEditTitle">Selected Data Template</font><br>
			The name given to this data template.
		</td>
		<?DrawFormItemDropdownFromSQL("data_template_id",db_fetch_assoc("select id,name from data_template order by name"),"name","id",$data_template[data_template_id],"None","0");?>
	</tr>
	
	<?
	end_box();
	
	start_box("Data Template Configuration", "", "");
	?>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Name</font><br>
			<?if (($use_data_template == false) || ($data_template[t_name] == "on")) { print "Choose a name for this data source."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template[t_name] == "on")) {
			DrawFormItemTextBox("name",$data[name],"","50", "40");
		}else{
			print "<td><em>$data_template[name]</em></td>";
			DrawFormItemHiddenTextBox("name",$data_template[name],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Path</font><br>
			Specify the full path to the rrd file containing the data.
		</td>
		<?DrawFormItemTextBox("data_source_path",$data[data_source_path],"","255", "40");?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Input Source</font><br>
			<?if (($use_data_template == false) || ($data_template[t_data_input_id] == "on")) { print "The method used to gather for this data source."; }?>
		</td>
		<?if ($use_data_template == false) {
			DrawFormItemDropdownFromSQL("data_input_id",db_fetch_assoc("select id,name from data_input order by name"),"name","id",$data[data_input_id],"None","1");
		}else{
			print "<td><em>" . db_fetch_cell("select name from data_input where id=$data_template[data_input_id]") . "</em></td>";
			DrawFormItemHiddenTextBox("data_input_id",$data_template[data_input_id],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Step</font><br>
			<?if (($use_data_template == false) || ($data_template[t_rrd_step] == "on")) { print "The amount of time in seconds between expected updates."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template[t_rrd_step] == "on")) {
			DrawFormItemTextBox("rrd_step",$data[rrd_step],"","50", "40");
		}else{
			print "<td><em>$data_template[rrd_step]</em></td>";
			DrawFormItemHiddenTextBox("rrd_step",$data_template[rrd_step],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Active</font><br>
			<?if (($use_data_template == false) || ($data_template[t_active] == "on")) { print "Whether cacti should gather data for this data source or not."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template[t_active] == "on")) {
			DrawFormItemCheckBox("active",$data[active],"Data Source Active","on",$args[local_data_id]);
		}else{
			print "<td><em>" . html_boolean_friendly($data_template[active]) . "</em></td>";
			DrawFormItemHiddenTextBox("active",$data_template[active],"");
		}?>
	</tr>
	
	<?
	end_box();
	
	/* fetch ALL rrd's for this data source */
	if (isset($args[local_data_id])) {
		$template_data_rrds = db_fetch_assoc("select id,data_source_name from data_template_rrd where local_data_id=$args[local_data_id] order by data_source_name");
	}
	
	/* select the first "rrd" of this data source by default */
	if (empty($args[view_rrd])) {
		$args[view_rrd] = $template_data_rrds[0][id];
	}
	
	/* get more information about the rrd we chose */
	if (!empty($args[view_rrd])) {
		$local_data_template_rrd_id = db_fetch_cell("select local_data_template_rrd_id from data_template_rrd where id=$args[view_rrd]");
		
		$rrd = db_fetch_row("select * from data_template_rrd where id=$args[view_rrd]");
		$rrd_template = db_fetch_row("select * from data_template_rrd where id=$local_data_template_rrd_id");
	}
	
	start_box("Data Source Configuration [" . $rrd[data_source_name] . "]", "", "");
	
	if (sizeof($template_data_rrds) > 1) {
		?>
		<tr height="33">
			<td valign="bottom" colspan="3" background="images/tab_back_light.gif">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<?
						foreach ($template_data_rrds as $template_data_rrd) {
						$i++;
						?>
						<td nowrap class="textTab" align="center" background="images/tab_middle.gif">
							<img src="images/tab_left.gif" border="0" align="absmiddle"><a class="linkTabs" href="data_templates.php?action=template_edit&data_template_id=<?print $args[data_template_id];?>&view_rrd=<?print $template_data_rrd[id];?>"><?print "$i: $template_data_rrd[data_source_name]";?></a><img src="images/tab_right.gif" border="0" align="absmiddle">
						</td>
						<?
						}
						?>
					</tr>
				</table>
			</td>
		</tr>
		<?
	}elseif (sizeof($template_data_rrds) == 1) {
		$args[view_rrd] = $template_data_rrds[0][id];
	}
	
	?>
	
	<form method="post" action="data_sources.php">
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Internal Data Source Name</font><br>
			<?if (($use_data_template == false) || ($rrd_template[t_data_source_name] == "on")) { print "Choose unique name to represent this piece of data inside of the rrd file."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template[t_data_source_name] == "on")) {
			DrawFormItemTextBox("data_source_name",$rrd[data_source_name],"","19", "40");
		}else{
			print "<td><em>$rrd_template[data_source_name]</em></td>";
			DrawFormItemHiddenTextBox("data_source_name",$rrd_template[data_source_name],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Maximum Value</font><br>
			<?if (($use_data_template == false) || ($rrd_template[t_rrd_maximum] == "on")) { print "The maximum value of data that is allowed to be collected."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template[t_rrd_maximum] == "on")) {
			DrawFormItemTextBox("rrd_maximum",$rrd[rrd_maximum],"","20", "30");
		}else{
			print "<td><em>$rrd_template[rrd_maximum]</em></td>";
			DrawFormItemHiddenTextBox("rrd_maximum",$rrd_template[rrd_maximum],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Minimum Value</font><br>
			<?if (($use_data_template == false) || ($rrd_template[t_rrd_minimum] == "on")) { print "The minimum value of data that is allowed to be collected."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template[t_rrd_minimum] == "on")) {
			DrawFormItemTextBox("rrd_minimum",$rrd[rrd_minimum],"","20", "30");
		}else{
			print "<td><em>$rrd_template[rrd_minimum]</em></td>";
			DrawFormItemHiddenTextBox("rrd_minimum",$rrd_template[rrd_minimum],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Type</font><br>
			<?if (($use_data_template == false) || ($rrd_template[t_data_source_type_id] == "on")) { print "How data is represented in the RRA."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template[t_data_source_type_id] == "on")) {
			DrawFormItemDropdownFromSQL("data_source_type_id",db_fetch_assoc("select * from def_ds order by Name"),"Name","ID",$rrd[data_source_type_id],"","1");
		}else{
			print "<td><em>" . db_fetch_cell("select name from def_ds where id=$rrd_template[data_source_type_id]") . "</em></td>";
			DrawFormItemHiddenTextBox("data_source_type_id",$rrd_template[data_source_type_id],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors[form_alternate1],$colors[form_alternate2],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Heartbeat</font><br>
			<?if (($use_data_template == false) || ($rrd_template[t_rrd_heartbeat] == "on")) { print "The maximum amount of time that can pass before data is entered as \"unknown\". (Usually 2x300=600)"; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template[t_rrd_heartbeat] == "on")) {
			DrawFormItemTextBox("rrd_heartbeat",$rrd[rrd_heartbeat],"","20", "30");
		}else{
			print "<td><em>$rrd_template[rrd_heartbeat]</em></td>";
			DrawFormItemHiddenTextBox("rrd_heartbeat",$rrd_template[rrd_heartbeat],"");
		}?>
	</tr>
	
	<?
	end_box();
	
	DrawFormItemHiddenIDField("_data_template_id",$data[data_template_id]);
	DrawFormItemHiddenIDField("data_template_data_id",$data[id]);
	DrawFormItemHiddenIDField("data_template_rrd_id",$rrd[id]);
	DrawFormItemHiddenIDField("local_data_template_data_id",$data[local_data_template_data_id]);
	DrawFormItemHiddenIDField("local_data_template_rrd_id",$rrd[local_data_template_rrd_id]);
	DrawFormItemHiddenIDField("local_data_id",$data[local_data_id]);
	DrawFormItemHiddenIDField("current_rrd",$args[view_rrd]);
	DrawFormItemHiddenTextBox("host_id",$args[host_id],db_fetch_cell("select host_id from data_local where id=$data[local_data_id]"));
	DrawFormItemHiddenTextBox("save_component_data_source","1","");
	
	start_box("", "", "");
	?>
	<tr bgcolor="#FFFFFF">
		 <td colspan="2" align="right">
			<?DrawFormSaveButton("save", "data_sources.php");?>
		</td>
	</tr>
	</form>
	<?
	end_box();	
}

function ds() {
	include_once ('include/tree_view_functions.php');
	
	start_box("<strong>Data Sources</strong>", "", "data_sources.php?action=edit");
	
	draw_tabs();
    	grow_polling_tree($start_branch, 1, $tree_parameters);
	
	end_box();
}
?>
