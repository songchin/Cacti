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
$section = "Add/Edit Graphs"; include ('include/auth.php'); 
include_once ("include/functions.php");
include_once ("include/cdef_functions.php");
include_once ("include/config_arrays.php");
include_once ('include/form.php');

switch ($_REQUEST["action"]) {
	case 'save':
		$redirect_location = form_save();
		
		header ("Location: $redirect_location"); exit;
		break;
	case 'data_edit':
		include_once ("include/top_header.php");
		
		data_edit();
		
		include_once ("include/bottom_footer.php");
		break;
	case 'tree':
		include_once ("include/top_header.php");
		
		tree();
		
		include_once ("include/bottom_footer.php");
		break;
	case 'tree_edit':
		include_once ("include/top_header.php");
		
		tree_edit();
		
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
	case 'tree_remove':
		tree_remove();

		header ("Location: data_sources.php?action=tree");
		break;
	case 'ds_remove':
		ds_remove();
		
		header ("Location: data_sources.php");
		break;
	case 'ds_edit':
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
	if ((isset($_POST["save_component_data_source"])) && (isset($_POST["save_component_data"]))) {
		ds_save();
		data_save();
		
		if ((is_error_message()) || ($_POST["data_template_id"] != $_POST["_data_template_id"])) {
			return $_SERVER["HTTP_REFERER"];
		}else{
			return "data_sources.php";
		}
	}elseif (isset($_POST["save_component_data_source"])) {
		return ds_save();
	}elseif (isset($_POST["save_component_data"])) {
		data_save();
		
		if ($config["full_view_data_source"]["value"] == "") {
			return "data_sources.php?action=ds_edit&local_data_id=" . $_POST["local_data_id"] . "&view_rrd=" . $_POST["current_rrd"];
		}elseif ($config["full_view_data_source"]["value"] == "on") {
			return "data_sources.php";
		}
	}
}


/* --------------------------
    Global Form Functions
   -------------------------- */

function draw_tabs() {
	global $action;
	?>
	<table height="20" cellspacing="0" cellpadding="0" width="98%" align="center">
		<tr>
			<td valign="bottom">
				<?if ($action != "") {?><a href="data_sources.php"><?}?><img src="images/tab_con_data_sources<?if ((strstr($action,"ds") == true) || (empty($action)) || strstr($action,"data")) { print "_down"; }?>.gif" alt="Data Sources" border="0" align="absmiddle"><?if ($action != "") {?></a><?}?>
				<?if ($action != "tree") {?><a href="data_sources.php?action=tree"><?}?><img src="images/tab_con_data_source_tree<?if (strstr($action,"tree") == true) { print "_down"; }?>.gif" alt="Data Source Tree" border="0" align="absmiddle"><?if ($action != "tree") {?></a><?}?>
			</td>
		</tr>
	</table>
	<?
}

function draw_data_form_select($main_action) { 
	global $colors; ?>
	<tr bgcolor="<?print $colors["panel"];?>">
		<form name="form_graph_id">
		<td colspan="6">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="1%">
						<select name="cbo_graph_id" onChange="window.location=document.form_graph_id.cbo_graph_id.options[document.form_graph_id.cbo_graph_id.selectedIndex].value">
							<option value="data_sources.php?action=ds_edit&local_data_id=<?print $_GET["local_data_id"];?>"<?if (strstr($_GET["action"],"ds")) {?> selected<?}?>>Data Source Configuration</option>
							<option value="data_sources.php?action=data_edit&local_data_id=<?print $_GET["local_data_id"];?>"<?if (strstr($_GET["action"],"data")) {?> selected<?}?>>Custom Data Configuration</option>
						</select>
					</td>
					<td>
						&nbsp;<a href="data_sources.php<?print $main_action;?>"><img src="images/button_go.gif" alt="Go" border="0" align="absmiddle"></a><br>
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>
<?}

/* ----------------------------
    data - Custom Data
   ---------------------------- */

function data_save() {
	/* ok, first pull out all 'input' values so we know how much to save */
	$input_fields = db_fetch_assoc("select
		data_template_data.data_input_id,
		data_input_fields.id,
		data_input_fields.input_output,
		data_input_fields.data_name,
		data_input_fields.regexp_match 
		from data_template_data
		left join data_input_fields
		on data_input_fields.data_input_id=data_template_data.data_input_id
		where data_template_data.id=" . $_POST["data_template_data_id"] . "
		and data_input_fields.input_output='in'");
	
	if (sizeof($input_fields) > 0) {
	foreach ($input_fields as $input_field) {
		/* save the data into the 'data_input_data' table */
		$form_name = "value_" . $input_field["data_name"];
		$form_value = $_POST[$form_name];
		
		if (isset($_POST[$form_name])) {
			/* run regexp match on input string */
			$form_value = form_input_validate($form_value, $form_name, $input_field["regexp_match"], true, 3);
			
			if (!is_error_message()) {
				db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values
					(" . $input_field["id"] . "," . $_POST["data_template_data_id"] . ",'" . db_fetch_cell("select t_value from data_input_data where data_input_field_id=" . $input_field["id"] . " and data_template_data_id=" . $_POST["data_template_data_id"]) . "','$form_value')");
			}
		}
	}
	}
}

function data_edit() {
	global $config, $colors;
	
	display_output_messages();
	
	if ($config["full_view_data_source"]["value"] == "") {
		draw_tabs();
		
		start_box("<strong>Data Sources</strong> [edit]", "", "");
		draw_data_form_select("?action=data_edit&local_data_id=" . $_GET["local_data_id"]);
		end_box();
	}
	
	if (isset($_GET["local_data_id"])) {
		$template_data = db_fetch_row("select id,data_input_id from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		$host = db_fetch_row("select host.id,host.hostname from data_local,host where data_local.host_id=host.id and data_local.id=" . $_GET["local_data_id"]);
	}else{
		unset($template_data);
	}
	
	print "<form method='post' action='data_sources.php'>\n";
	
	$i = 0;
	if (!empty($template_data["data_input_id"])) {
		/* get each INPUT field for this data input source */
		$fields = db_fetch_assoc("select * from data_input_fields where data_input_id=" . $template_data["data_input_id"] . " and input_output='in' order by name");
		
		start_box("Custom Data [" . db_fetch_cell("select name from data_input where id=" . $template_data["data_input_id"]) . "]", "", "");
		
		/* loop through each field found */
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			$data_input_data = db_fetch_row("select * from data_input_data where data_template_data_id=" . $template_data["id"] . " and data_input_field_id=" . $field["id"]);
			
			if (sizeof($data_input_data) > 0) {
				$old_value = $data_input_data["value"];
			}else{
				$old_value = "";
				$data_input_data["t_value"] = "on"; /* default to allow users to input data */
			}
			
			DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i);
			
			if ((!empty($host["id"])) && (eregi('^(hostname|management_ip|snmp_community|snmp_username|snmp_password)$', $field["type_code"]))) {
				print "<td width='50%'><strong>" . $field["name"] . "</strong> (From Host: " . $host["hostname"] . ")</td>\n";
				print "<td><em>$old_value</em></td>\n";
			}elseif (empty($data_input_data["t_value"])) {
				print "<td width='50%'><strong>" . $field["name"] . "</strong> (From Host Template)</td>\n";
				print "<td><em>" . (empty($old_value) ? "Nothing Entered" : $old_value) . "</em></td>\n";
			}else{
				print "<td width='50%'><strong>" . $field["name"] . "</strong></td>\n";
				DrawFormItemTextBox("value_" . $field["data_name"],$old_value,"","");
			}
			
			print "</tr>\n";
			
			$i++;
		}
		}else{
			print "<tr><td><em>No Input Fields for the Selected Data Input Source</em></td></tr>";
		}
		
		end_box();
	}
	
	DrawFormItemHiddenIDField("local_data_id",$_GET["local_data_id"]);
	DrawFormItemHiddenIDField("data_template_data_id",$template_data["id"]);
	DrawFormItemHiddenTextBox("save_component_data","1","");
	
	if ($config["full_view_data_source"]["value"] == "") {
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
}

/* ----------------------
    Tree Edit Functions
   ---------------------- */

function tree() {
	include_once ('include/tree_view_functions.php');
	
	$tree_parameters["edit_mode"] = true;
	
	draw_tabs();
	start_box("<strong>Data Source Tree</strong>", "", "data_sources.php?action=edit");
    	grow_polling_tree($start_branch, 1, $tree_parameters);
	
	end_box();
}

function tree_edit() {
	include_once("include/tree_view_functions.php");
	
	global $colors;
	
	if (isset($_GET["id"])) {
		$tree_item = db_fetch_row("select * from data_tree where id=" . $_GET["id"]);
	}else{
		unset($tree_item);
	}
	
	display_output_messages();
	
	?>
	<form method="post" action="data_sources.php">
	<?
	
	draw_tabs();
	start_box("<strong>Date Source Tree</strong> [edit] - Tree Item", "", "");
	
	DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Item Parent</font><br>
			Choose a parent for this item.
		</td>
		<?draw_data_source_dropdown("parent_id", 0);?>
	</tr>
	<?
	end_box();
	
	/* bold the active "type" */
	if ($tree_item["host_id"] > 0) { $title = "<strong>Tree Item [host]</strong>"; }else{ $title = "Tree Item [host]"; }
	
	start_box($title, "", "");
	
	DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Host</font><br>
			If this item is a host, please select it from the list.
		</td>
		<?DrawFormItemDropdownFromSQL("host_id",db_fetch_assoc("select id,hostname from host order by hostname"),"hostname","id",$tree_item["host_id"],"None","1");?>
	</tr>
	
	<?
	
	end_box();
	
	/* bold the active "type" */
	if ($tree_item["title"] != "") { $title = "<strong>Tree Item [header]</strong>"; }else{ $title = "Tree Item [header]"; }
	
	start_box($title, "", "");
	
	DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Header Title</font><br>
			If this item is a header, enter a title here.
		</td>
		<?DrawFormItemTextBox("title",$tree_item["title"],"","100","40");?>
	</tr>
	<?
	
	end_box();
	
	DrawFormItemHiddenIDField("id",$_GET["tree_item_id"]);
	DrawFormItemHiddenIDField("tree_id",$_GET["tree_id"]);
	DrawFormItemHiddenTextBox("save_component_tree_item","1","");
	
	start_box("", "", "");
	?>
	<tr bgcolor="#FFFFFF">
		 <td colspan="2" align="right">
			<?DrawFormSaveButton("save", "tree.php?action=edit&id=" . $_GET["tree_id"]);?>
		</td>
	</tr>
	</form>
	<?
	end_box();
}

function tree_moveup() {
	include_once("include/tree_functions.php");
	
	$order_key = db_fetch_cell("SELECT order_key FROM data_tree WHERE id=" . $_GET["branch_id"]);
	if ($order_key > 0) { branch_up($order_key, 'data_tree', 'order_key', ''); }
}

function tree_movedown() {
	include_once("include/tree_functions.php");
	
	$order_key = db_fetch_cell("SELECT order_key FROM data_tree WHERE id=" . $_GET["branch_id"]);
	if ($order_key > 0) { branch_down($order_key, 'data_tree', 'order_key', ''); }
}

function tree_remove() {
	global $config;
	
	if (($config["remove_verification"]["value"] == "on") && ($_GET["confirm"] != "yes")) {
		include ('include/top_header.php');
		DrawConfirmForm("Are You Sure?", "Are you sure you want to delete the data source tree <strong>'" . db_fetch_cell("select title from data_tree where id=" . $_GET["id"]) . "'</strong>?", getenv("HTTP_REFERER"), "data_sources.php?action=tree_remove&id=" . $_GET["id"]);
		include ('include/bottom_footer.php');
		exit;
	}

	if (($config["remove_verification"]["value"] == "") || ($_GET["confirm"] == "yes")) {
		db_execute("delete from data_tree where id=" . $_GET["id"]);
	}
}

/* ------------------------
    Data Source Functions
   ------------------------ */

function ds_remove() {
	global $config;
	
	if (($config["remove_verification"]["value"] == "on") && ($_GET["confirm"] != "yes")) {
		include ('include/top_header.php');
		DrawConfirmForm("Are You Sure?", "Are you sure you want to delete the data source <strong>'" . db_fetch_cell("select name from data_template_data where local_data_id=" . $_GET["local_data_id"]) . "'</strong>?", getenv("HTTP_REFERER"), "data_sources.php?action=ds_remove&local_data_id=" . $_GET["local_data_id"]);
		include ('include/bottom_footer.php');
		exit;
	}
	
	if (($config["remove_verification"]["value"] == "") || ($_GET["confirm"] == "yes")) {
		db_execute("delete from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		db_execute("delete from data_template_rrd where local_data_id=" . $_GET["local_data_id"]);
		db_execute("delete from data_local where id=" . $_GET["local_data_id"]);
	}
}

function ds_save() {
	$save["id"] = $_POST["local_data_id"];
	$save["data_template_id"] = $_POST["data_template_id"];
	$save["host_id"] = $_POST["host_id"];
	
	$local_data_id = sql_save($save, "data_local");
	unset($save);
	
	$save["id"] = $_POST["data_template_data_id"];
	$save["local_data_template_data_id"] = $_POST["local_data_template_data_id"];
	$save["local_data_id"] = $local_data_id;
	$save["data_template_id"] = $_POST["data_template_id"];
	$save["data_input_id"] = $_POST["data_input_id"];
	$save["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
	$save["data_source_path"] = $_POST["data_source_path"];
	$save["active"] = $_POST["active"];
	$save["rrd_step"] = form_input_validate($_POST["rrd_step"], "rrd_step", "^[0-9]+$", false, 3);
	
	if (!is_error_message()) {
		sql_save($save, "data_template_data");
	}
	
	unset($save);
	
	/* if this is a new data source and a template has been selected, skip item creation this time
	otherwise it throws off the templatate creation because of the NULL data */
	if ($_POST["data_template_id"] == $_POST["_data_template_id"]) {
		$save["id"] = $_POST["data_template_rrd_id"];
		$save["local_data_template_rrd_id"] = $_POST["local_data_template_rrd_id"];
		$save["local_data_id"] = $local_data_id;
		$save["data_template_id"] = $_POST["data_template_id"];
		$save["rrd_maximum"] = form_input_validate($_POST["rrd_maximum"], "rrd_maximum", "^[0-9]+$", false, 3);
		$save["rrd_minimum"] = form_input_validate($_POST["rrd_minimum"], "rrd_minimum", "^[0-9]+$", false, 3);
		$save["rrd_heartbeat"] = form_input_validate($_POST["rrd_heartbeat"], "rrd_heartbeat", "^[0-9]+$", false, 3);
		$save["data_source_type_id"] = $_POST["data_source_type_id"];
		$save["data_source_name"] = form_input_validate($_POST["data_source_name"], "data_source_name", "^[a-zA-Z0-9_]{1,19}$", false, 3);
		$save["data_input_field_id"] = $_POST["data_input_field_id"];
		
		if (!is_error_message()) {
			sql_save($save, "data_template_rrd");
		}
	}
	
	if ($_POST["data_template_id"] != $_POST["_data_template_id"]) {
		/* update all nessesary template information */
		include_once ("include/utility_functions.php");
		$return_status = change_data_template($local_data_id, $_POST["data_template_id"], $_POST["_data_template_id"]);
	}
	
	if (is_error_message()) {
		return "data_sources.php?action=ds_edit&local_data_id=$local_data_id&host_id=" . $_POST["host_id"] . "&view_rrd=" . $_POST["view_rrd"];
	}else{
		if ($_POST["data_template_id"] != $_POST["_data_template_id"]) {
			return "data_sources.php?action=ds_edit&local_data_id=$local_data_id&host_id=" . $_POST["host_id"] . "&view_rrd=" . $_POST["view_rrd"];
		}else{
			return "data_sources.php";
		}
	}
}

function ds_edit() {
	global $config, $colors;
	
	$use_data_template = true;
	
	if (isset($_GET["local_data_id"])) {
		$local_data_template_data_id = db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		
		$data = db_fetch_row("select * from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		$data_template = db_fetch_row("select * from data_template_data where id=$local_data_template_data_id");
	}else{
		unset($data_template);
		unset($data);
		
		$use_data_template = false;
	}
	
	if ($data["data_template_id"] == "0") {
		$use_data_template = false;
	}
	
	display_output_messages();
	
	$data_template_name = db_fetch_cell("select name from data_template where id=" . $data["data_template_id"]);
	
	draw_tabs();
	
	if ($config["full_view_data_source"]["value"] == "") {
		start_box("<strong>Data Sources</strong> [edit]", "", "");
		draw_data_form_select("?action=ds_edit&local_data_id=" . $_GET["local_data_id"]);
		end_box();
	}
	
	start_box("<strong>Data Sources</strong> [edit] - Data Templation Selection", "", "");	
	
	print "<form method='post' action='data_sources.php'>\n";
	
	DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Selected Data Template</font><br>
			The name given to this data template.
		</td>
		<?DrawFormItemDropdownFromSQL("data_template_id",db_fetch_assoc("select id,name from data_template order by name"),"name","id",$data_template["data_template_id"],"None","0");?>
	</tr>
	
	<?
	end_box();
	
	start_box("Data Source Configuration", "", "");
	?>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Name</font><br>
			<?if (($use_data_template == false) || ($data_template["t_name"] == "on")) { print "Choose a name for this data source."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template["t_name"] == "on")) {
			DrawFormItemTextBox("name",$data["name"],"","50", "40");
		}else{
			print "<td><em>" . $data["name"] . "</em></td>";
			DrawFormItemHiddenTextBox("name",$data_template["name"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Path</font><br>
			Specify the full path to the rrd file containing the data.
		</td>
		<?DrawFormItemTextBox("data_source_path",$data["data_source_path"],"","255", "40");?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Input Source</font><br>
			<?if (($use_data_template == false) || ($data_template["t_data_input_id"] == "on")) { print "The method used to gather for this data source."; }?>
		</td>
		<?if ($use_data_template == false) {
			DrawFormItemDropdownFromSQL("data_input_id",db_fetch_assoc("select id,name from data_input order by name"),"name","id",$data["data_input_id"],"None","1");
		}else{
			print "<td><em>" . db_fetch_cell("select name from data_input where id=" . $data["data_input_id"]) . "</em></td>";
			DrawFormItemHiddenTextBox("data_input_id",$data_template["data_input_id"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Step</font><br>
			<?if (($use_data_template == false) || ($data_template["t_rrd_step"] == "on")) { print "The amount of time in seconds between expected updates."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template["t_rrd_step"] == "on")) {
			DrawFormItemTextBox("rrd_step",$data["rrd_step"],"","50", "40");
		}else{
			print "<td><em>" . $data["rrd_step"] . "</em></td>";
			DrawFormItemHiddenTextBox("rrd_step",$data_template["rrd_step"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Active</font><br>
			<?if (($use_data_template == false) || ($data_template["t_active"] == "on")) { print "Whether cacti should gather data for this data source or not."; }?>
		</td>
		<?if (($use_data_template == false) || ($data_template["t_active"] == "on")) {
			DrawFormItemCheckBox("active",$data["active"],"Data Source Active","on",$_GET["local_data_id"]);
		}else{
			print "<td><em>" . html_boolean_friendly($data["active"]) . "</em></td>";
			DrawFormItemHiddenTextBox("active",$data_template["active"],"");
		}?>
	</tr>
	
	<?
	end_box();
	
	/* fetch ALL rrd's for this data source */
	if (isset($_GET["local_data_id"])) {
		$template_data_rrds = db_fetch_assoc("select id,data_source_name from data_template_rrd where local_data_id=" . $_GET["local_data_id"] . " order by data_source_name");
	}
	
	/* select the first "rrd" of this data source by default */
	if (empty($_GET["view_rrd"])) {
		$_GET["view_rrd"] = $template_data_rrds[0]["id"];
	}
	
	/* get more information about the rrd we chose */
	if (!empty($_GET["view_rrd"])) {
		$local_data_template_rrd_id = db_fetch_cell("select local_data_template_rrd_id from data_template_rrd where id=" . $_GET["view_rrd"]);
		
		$rrd = db_fetch_row("select * from data_template_rrd where id=" . $_GET["view_rrd"]);
		$rrd_template = db_fetch_row("select * from data_template_rrd where id=$local_data_template_rrd_id");
	}
	
	start_box("Data Source Item Configuration [" . $rrd["data_source_name"] . "]", "", "");
	
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
							<img src="images/tab_left.gif" border="0" align="absmiddle"><a class="linkTabs" href="data_templates.php?action=template_edit&data_template_id=<?print $_GET["data_template_id"];?>&view_rrd=<?print $template_data_rrd["id"];?>"><?print "$i: " . $template_data_rrd["data_source_name"];?></a><img src="images/tab_right.gif" border="0" align="absmiddle">
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
		$_GET["view_rrd"] = $template_data_rrds[0]["id"];
	}
	
	?>
	
	<form method="post" action="data_sources.php">
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Internal Data Source Name</font><br>
			<?if (($use_data_template == false) || ($rrd_template["t_data_source_name"] == "on")) { print "Choose unique name to represent this piece of data inside of the rrd file."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template["t_data_source_name"] == "on")) {
			DrawFormItemTextBox("data_source_name",$rrd["data_source_name"],"","19", "40");
		}else{
			print "<td><em>" . $rrd["data_source_name"] . "</em></td>";
			DrawFormItemHiddenTextBox("data_source_name",$rrd_template["data_source_name"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Maximum Value</font><br>
			<?if (($use_data_template == false) || ($rrd_template["t_rrd_maximum"] == "on")) { print "The maximum value of data that is allowed to be collected."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template["t_rrd_maximum"] == "on")) {
			DrawFormItemTextBox("rrd_maximum",$rrd["rrd_maximum"],"","20", "30");
		}else{
			print "<td><em>" . $rrd["rrd_maximum"] . "</em></td>";
			DrawFormItemHiddenTextBox("rrd_maximum",$rrd_template["rrd_maximum"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Minimum Value</font><br>
			<?if (($use_data_template == false) || ($rrd_template["t_rrd_minimum"] == "on")) { print "The minimum value of data that is allowed to be collected."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template["t_rrd_minimum"] == "on")) {
			DrawFormItemTextBox("rrd_minimum",$rrd["rrd_minimum"],"","20", "30");
		}else{
			print "<td><em>" . $rrd["rrd_minimum"] . "</em></td>";
			DrawFormItemHiddenTextBox("rrd_minimum",$rrd_template["rrd_minimum"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Data Source Type</font><br>
			<?if (($use_data_template == false) || ($rrd_template["t_data_source_type_id"] == "on")) { print "How data is represented in the RRA."; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template["t_data_source_type_id"] == "on")) {
			DrawFormItemDropdownFromSQL("data_source_type_id",db_fetch_assoc("select * from def_ds order by Name"),"Name","ID",$rrd["data_source_type_id"],"","1");
		}else{
			print "<td><em>" . db_fetch_cell("select name from def_ds where id=" . $rrd["data_source_type_id"]) . "</em></td>";
			DrawFormItemHiddenTextBox("data_source_type_id",$rrd_template["data_source_type_id"],"");
		}?>
	</tr>
	
	<?DrawMatrixRowAlternateColorBegin($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++; ?>
		<td width="50%">
			<font class="textEditTitle">Heartbeat</font><br>
			<?if (($use_data_template == false) || ($rrd_template["t_rrd_heartbeat"] == "on")) { print "The maximum amount of time that can pass before data is entered as \"unknown\". (Usually 2x300=600)"; }?>
		</td>
		<?if (($use_data_template == false) || ($rrd_template["t_rrd_heartbeat"] == "on")) {
			DrawFormItemTextBox("rrd_heartbeat",$rrd["rrd_heartbeat"],"","20", "30");
		}else{
			print "<td><em>" . $rrd["rrd_heartbeat"] . "</em></td>";
			DrawFormItemHiddenTextBox("rrd_heartbeat",$rrd_template["rrd_heartbeat"],"");
		}?>
	</tr>
	
	<?
	end_box();
	
	if ($config["full_view_data_source"]["value"] == "on") {
		data_edit();	
	}
	
	DrawFormItemHiddenIDField("_data_template_id",$data["data_template_id"]);
	DrawFormItemHiddenIDField("data_template_data_id",$data["id"]);
	DrawFormItemHiddenIDField("data_template_rrd_id",$rrd["id"]);
	DrawFormItemHiddenIDField("local_data_template_data_id",$data["local_data_template_data_id"]);
	DrawFormItemHiddenIDField("local_data_template_rrd_id",$rrd["local_data_template_rrd_id"]);
	DrawFormItemHiddenIDField("local_data_id",$data["local_data_id"]);
	DrawFormItemHiddenIDField("current_rrd",$_GET["view_rrd"]);
	DrawFormItemHiddenTextBox("host_id",$_GET["host_id"],db_fetch_cell("select host_id from data_local where id=" . $data["local_data_id"]));
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
	
	display_output_messages();
	
	draw_tabs();
	start_box("<strong>Data Sources</strong>", "", "data_sources.php?action=ds_edit");
    	grow_polling_tree($start_branch, 1, $tree_parameters);
	
	end_box();
}
?>
