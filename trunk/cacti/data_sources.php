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
	case 'data_edit':
		include_once ("include/top_header.php");
		
		data_edit();
		
		include_once ("include/bottom_footer.php");
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
	include_once ("include/utility_functions.php");
	
	if (isset($_POST["save_component_data_source"])) {
		$save1["id"] = $_POST["local_data_id"];
		$save1["data_template_id"] = $_POST["data_template_id"];
		$save1["host_id"] = $_POST["host_id"];
		
		$save2["id"] = $_POST["data_template_data_id"];
		$save2["local_data_template_data_id"] = $_POST["local_data_template_data_id"];
		$save2["data_template_id"] = form_input_validate($_POST["data_template_id"], "data_template_id", "", true, 3);
		$save2["data_input_id"] = form_input_validate($_POST["data_input_id"], "data_input_id", "", true, 3);
		$save2["name"] = form_input_validate($_POST["name"], "name", "", false, 3);
		$save2["data_source_path"] = form_input_validate($_POST["data_source_path"], "data_source_path", "", true, 3);
		$save2["active"] = form_input_validate((isset($_POST["active"]) ? $_POST["active"] : ""), "active", "", true, 3);
		$save2["rrd_step"] = form_input_validate($_POST["rrd_step"], "rrd_step", "^[0-9]+$", false, 3);
		
		$save3["id"] = $_POST["data_template_rrd_id"];
		$save3["local_data_template_rrd_id"] = $_POST["local_data_template_rrd_id"];
		$save3["data_template_id"] = form_input_validate($_POST["data_template_id"], "data_template_id", "", true, 3);
		$save3["rrd_maximum"] = form_input_validate($_POST["rrd_maximum"], "rrd_maximum", "^[0-9]+$", false, 3);
		$save3["rrd_minimum"] = form_input_validate($_POST["rrd_minimum"], "rrd_minimum", "^[0-9]+$", false, 3);
		$save3["rrd_heartbeat"] = form_input_validate($_POST["rrd_heartbeat"], "rrd_heartbeat", "^[0-9]+$", false, 3);
		$save3["data_source_type_id"] = $_POST["data_source_type_id"];
		$save3["data_source_name"] = form_input_validate($_POST["data_source_name"], "data_source_name", "^[a-zA-Z0-9_]{1,19}$", false, 3);
		
		if (!is_error_message()) {
			$local_data_id = sql_save($save1, "data_local");
		}
		
		if (!is_error_message()) {
			$save2["local_data_id"] = $local_data_id;
			$data_template_data_id = sql_save($save2, "data_template_data");
			
			if ($data_template_data_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}
		
		/* if this is a new data source and a template has been selected, skip item creation this time
		otherwise it throws off the templatate creation because of the NULL data */
		if ($_POST["data_template_id"] == $_POST["_data_template_id"]) {
			if (!is_error_message()) {
				$save3["local_data_id"] = $local_data_id;
				$data_template_rrd_id = sql_save($save3, "data_template_rrd");
				
				if ($data_template_rrd_id) {
					raise_message(1);
				}else{
					raise_message(2);
				}
			}
		}
		
		if (!is_error_message()) {
			update_poller_cache($local_data_id);
			
			if ($_POST["host_id"] != $_POST["_host_id"]) {
				/* push out all nessesary host information */
				push_out_host($_POST["host_id"]);
				
				/* reset current host for display purposes */
				$_SESSION["sess_data_source_current_host_id"] = $_POST["host_id"];
			}
			
			if ($_POST["data_template_id"] != $_POST["_data_template_id"]) {
				/* update all nessesary template information */
				change_data_template($local_data_id, $_POST["data_template_id"]);
			}
			
			/* if no data source path has been entered, generate one */
			if (empty($_POST["data_source_path"])) {
				generate_data_source_path($local_data_id);
			}
			
			/* save entried in 'selected rras' field */
			db_execute("delete from data_template_data_rra where data_template_data_id=$data_template_data_id"); 
			
			for ($i=0; ($i < count($_POST["rra_id"])); $i++) {
				db_execute("insert into data_template_data_rra (rra_id,data_template_data_id) 
					values (" . $_POST["rra_id"][$i] . ",$data_template_data_id)");
			}
		}
	}
	
	if ((isset($_POST["save_component_data"])) && (!is_error_message())) {
		/* ok, first pull out all 'input' values so we know how much to save */
		$input_fields = db_fetch_assoc("select
			data_template_data.data_input_id,
			data_local.host_id
			data_input_fields.id,
			data_input_fields.input_output,
			data_input_fields.data_name,
			data_input_fields.regexp_match,
			data_input_fields.allow_nulls,
			data_input_fields.type_code
			from data_template_data
			left join data_input_fields on data_input_fields.data_input_id=data_template_data.data_input_id
			left join data_local on data_template_data.local_data_id=data_local.id
			where data_template_data.id=" . $_POST["data_template_data_id"] . "
			and data_input_fields.input_output='in'");
		
		if (sizeof($input_fields) > 0) {
		foreach ($input_fields as $input_field) {
			/* save the data into the 'data_input_data' table */
			$form_name = "value_" . $input_field["data_name"];
			$form_value = $_POST[$form_name];
			
			if (isset($_POST[$form_name])) {
				if ($input_field["allow_nulls"] == "on") {
					$allow_nulls = true;
				}elseif (empty($input_field["allow_nulls"])) {
					$allow_nulls = false;
				}
				
				/* run regexp match on input string */
				$form_value = form_input_validate($form_value, $form_name, $input_field["regexp_match"], $allow_nulls, 3);
				
				/* make sure we don't overwrite 'host fields' */ 
				if ((!is_error_message()) && ((empty($input_field["host_id"]) || (empty($input_field["type_code"]))))) {
					db_execute("replace into data_input_data (data_input_field_id,data_template_data_id,t_value,value) values
						(" . $input_field["id"] . "," . $_POST["data_template_data_id"] . ",'" . db_fetch_cell("select t_value from data_input_data where data_input_field_id=" . $input_field["id"] . " and data_template_data_id=" . $_POST["data_template_data_id"]) . "','$form_value')");
				}
			}
		}
		}
		
		if ((read_config_option("full_view_data_source") == "") && (is_error_message())) {
			/* ds data edit page */
		}
	}
	
	if ((is_error_message()) || ($_POST["data_template_id"] != $_POST["_data_template_id"]) || ($_POST["host_id"] != $_POST["_host_id"])) {
		header ("Location: data_sources.php?action=ds_edit&local_data_id=" . (empty($local_data_id) ? $_POST["local_data_id"] : $local_data_id) . "&host_id=" . $_POST["host_id"] . "&view_rrd=" . (isset($_POST["view_rrd"]) ? $_POST["view_rrd"] : "0"));
	}else{
		header ("Location: data_sources.php");
	}
}


/* --------------------------
    Global Form Functions
   -------------------------- */

function draw_data_form_select($main_action) { 
	global $colors; ?>
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_graph_id">
		<td colspan="6">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="1%">
						<select name="cbo_graph_id" onChange="window.location=document.form_graph_id.cbo_graph_id.options[document.form_graph_id.cbo_graph_id.selectedIndex].value">
							<option value="data_sources.php?action=ds_edit&local_data_id=<?php print $_GET["local_data_id"];?>"<?php if (strstr($_GET["action"],"ds")) {?> selected<?php }?>>Data Source Configuration</option>
							<option value="data_sources.php?action=data_edit&local_data_id=<?php print $_GET["local_data_id"];?>"<?php if (strstr($_GET["action"],"data")) {?> selected<?php }?>>Custom Data Configuration</option>
						</select>
					</td>
					<td>
						&nbsp;<a href="data_sources.php<?php print $main_action;?>"><img src="images/button_go.gif" alt="Go" border="0" align="absmiddle"></a><br>
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>
<?php }

/* ----------------------------
    data - Custom Data
   ---------------------------- */

function data_edit() {
	global $config, $colors;
	
	if (!empty($_GET["local_data_id"])) {
		$data = db_fetch_row("select id,data_input_id,data_template_id,name,local_data_id from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		$template_data = db_fetch_row("select id,data_input_id from data_template_data where data_template_id=" . $data["data_template_id"] . " and local_data_id=0");
		
		$host = db_fetch_row("select host.id,host.hostname from data_local,host where data_local.host_id=host.id and data_local.id=" . $_GET["local_data_id"]);
		
		$header_label = "[edit: " . $data["name"] . "]";
	}else{
		$header_label = "[new]";
	}
	
	if (read_config_option("full_view_data_source") == "") {
		start_box("<strong>Data Sources</strong> $header_label", "98%", $colors["header"], "3", "center", "");
		draw_data_form_select("?action=data_edit&local_data_id=" . $_GET["local_data_id"]);
		end_box();
	}
	
	print "<form method='post' action='data_sources.php'>\n";
	
	$i = 0;
	if (!empty($data["data_input_id"])) {
		/* get each INPUT field for this data input source */
		$fields = db_fetch_assoc("select * from data_input_fields where data_input_id=" . $data["data_input_id"] . " and input_output='in' order by name");
		
		start_box("<strong>Custom Data</strong> [data input: " . db_fetch_cell("select name from data_input where id=" . $data["data_input_id"]) . "]", "98%", $colors["header"], "3", "center", "");
		
		/* loop through each field found */
		if (sizeof($fields) > 0) {
		foreach ($fields as $field) {
			$data_input_data = db_fetch_row("select * from data_input_data where data_template_data_id=" . $data["id"] . " and data_input_field_id=" . $field["id"]);
			
			if (sizeof($data_input_data) > 0) {
				$old_value = $data_input_data["value"];
			}else{
				$old_value = "";
			}
			
			/* if data template then get t_value from template, else always allow user input */
			if (empty($data["data_template_id"])) {
				$can_template = "on";
			}else{
				$can_template = db_fetch_cell("select t_value from data_input_data where data_template_data_id=" . $template_data["id"] . " and data_input_field_id=" . $field["id"]);
			}
			
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
			
			if ((!empty($host["id"])) && (eregi('^(hostname|management_ip|snmp_community|snmp_username|snmp_password|snmp_version)$', $field["type_code"]))) {
				print "<td width='50%'><strong>" . $field["name"] . "</strong> (From Host: " . $host["hostname"] . ")</td>\n";
				print "<td><em>$old_value</em></td>\n";
			}elseif (empty($can_template)) {
				print "<td width='50%'><strong>" . $field["name"] . "</strong> (From Data Template)</td>\n";
				print "<td><em>" . (empty($old_value) ? "Nothing Entered" : $old_value) . "</em></td>\n";
			}else{
				print "<td width='50%'><strong>" . $field["name"] . "</strong></td>\n";
				form_text_box("value_" . $field["data_name"],$old_value,"","");
			}
			
			print "</tr>\n";
			
			$i++;
		}
		}else{
			print "<tr><td><em>No Input Fields for the Selected Data Input Source</em></td></tr>";
		}
		
		end_box();
	}
	
	form_hidden_id("local_data_id",(isset($data) ? $data["local_data_id"] : "0"));
	form_hidden_id("data_template_data_id",(isset($data) ? $data["id"] : "0"));
	form_hidden_box("save_component_data","1","");
	
	if (read_config_option("full_view_data_source") == "") {
		form_save_button("data_sources.php");
	}
}

/* ------------------------
    Data Source Functions
   ------------------------ */

function ds_remove() {
	global $config;
	
	if ((read_config_option("remove_verification") == "on") && (!isset($_GET["confirm"]))) {
		include ('include/top_header.php');
		
		print "<br>";
		
		start_box("<strong>Are You Sure?</strong>", "60%", "B61D22", "3", "center", "");
		
		form_area("Are you sure you want to delete the data source <strong>'" . db_fetch_cell("select name from data_template_data where local_data_id=" . $_GET["local_data_id"]) . "'</strong>?");
		
		/* find out what (if any) graphs are using this data source, so we can complain to the user */
		$graphs = db_fetch_assoc("select
			graph_templates_graph.title
			from data_template_rrd
			left join graph_templates_item on graph_templates_item.task_item_id=data_template_rrd.id
			left join graph_templates_graph on graph_templates_item.local_graph_id=graph_templates_graph.local_graph_id
			where data_template_rrd.local_data_id=" . $_GET["local_data_id"] . "
			and graph_templates_item.local_graph_id>0
			and graph_templates_graph.local_graph_id>0
			group by graph_templates_graph.title
			order by graph_templates_graph.title");
		
		if (sizeof($graphs) > 0) {
			print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td class='textArea'><p class='textArea'>The following graphs are using this data source:</p>\n";
			
			foreach ($graphs as $graph) {
				print "<strong>" . $graph["title"] . "</strong><br>\n";
			}
			
			print "<br>";
			form_base_radio_button("delete_type", "1", "1", "Leave the graphs untouched.", "1", true);
			form_base_radio_button("delete_type", "1", "2", "Delete all graph items that reference to this data source.", "1", true);
			print "</td></tr>";
		}
		
		form_confirm_buttons("data_sources.php?action=ds_remove&local_data_id=" . $_GET["local_data_id"], "data_sources.php");
		
		end_box();
		
		include ('include/bottom_footer.php');
		exit;
	}
	
	if ((read_config_option("remove_verification") == "") || ($_GET["confirm"] == "yes")) {
		db_execute("delete from data_template_data_rra where data_template_data_id=" . db_fetch_cell("select id from data_template_data where local_data_id=" . $_GET["local_data_id"]));
		db_execute("delete from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		db_execute("delete from data_template_rrd where local_data_id=" . $_GET["local_data_id"]);
		db_execute("delete from data_input_data_cache where local_data_id=" . $_GET["local_data_id"]);
		db_execute("delete from data_local where id=" . $_GET["local_data_id"]);
	}
}

function ds_edit() {
	include_once ("include/rrd_functions.php");
	
	global $colors, $struct_data_source, $struct_data_source_item, $data_source_types;
	
	$use_data_template = true;
	$host_id = 0;
	
	if (!empty($_GET["local_data_id"])) {
		$local_data_template_data_id = db_fetch_cell("select local_data_template_data_id from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		
		$data = db_fetch_row("select * from data_template_data where local_data_id=" . $_GET["local_data_id"]);
		$data_template = db_fetch_row("select * from data_template_data where id=$local_data_template_data_id");
		
		$host_id = db_fetch_cell("select host_id from data_local where id=" . $_GET["local_data_id"]);
		$data_template_name = db_fetch_cell("select name from data_template where id=" . $data["data_template_id"]);
		
		$header_label = "[edit: " . $data["name"] . "]";
		
		if ($data["data_template_id"] == "0") {
			$use_data_template = false;
		}
	}else{
		$header_label = "[new]";
		
		$use_data_template = false;
	}
	
	
	
	if (read_config_option("full_view_data_source") == "") {
		start_box("<strong>Data Sources</strong> $header_label", "98%", $colors["header"], "3", "center", "");
		draw_data_form_select("?action=ds_edit&local_data_id=" . $_GET["local_data_id"]);
		end_box();
	}
	
	//start_box("", "98%", "aaaaaa", "3", "center", "");
	//print "<tr><td><pre>";
	//print rrdtool_function_create($_GET["local_data_id"], true);
	//print "</pre></td></tr>";
	//end_box();
	
	start_box("<strong>Data Templation Selection</strong> $header_label", "98%", $colors["header"], "3", "center", "");
	
	print "<form method='post' action='data_sources.php'>\n";
	
	form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],0); ?>
		<td width="50%">
			<font class="textEditTitle">Selected Data Template</font><br>
			The name given to this data template.
		</td>
		<?php form_dropdown("data_template_id",db_fetch_assoc("select id,name from data_template order by name"),"name","id",(isset($data_template) ? $data_template["data_template_id"] : "0"),"None","0");?>
	</tr>
	
	<?php form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],1); ?>
		<td width="50%">
			<font class="textEditTitle">Host</font><br>
			Choose the host that this data source belongs to.
		</td>
		<?php form_dropdown("host_id",db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname"),"name","id",$host_id,"None",(isset($_GET["host_id"]) ? $_GET["host_id"] : "0"));?>
	</tr>
	
	<?php
	end_box();
	
	start_box("<strong>Data Source</strong>", "98%", $colors["header"], "3", "center", "");
	
	$i = 0;
	while (list($field_name, $field_array) = each($struct_data_source)) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
		
		print "<td width='50%'><font class='textEditTitle'>" . $field_array["title"] . "</font><br>\n";
		if (($use_data_template == false) || (!empty($data_template{"t_" . $field_name})) || ($field_array["flags"] == "NOTEMPLATE")) {
			print $field_array["description"];
		}
		
		print "</td>\n";
		
		if ($field_array["type"] == "custom") {
			$array_rra = array_rekey(db_fetch_assoc("select id,name from rra order by name"), "id", "name");
			form_multi_dropdown("rra_id",$array_rra,db_fetch_assoc("select * from data_template_data_rra where data_template_data_id=" . (isset($data) ? $data["id"] : "0")), "rra_id");
		}else{
			if (($use_data_template == false) || (!empty($data_template{"t_" . $field_name})) || ($field_array["flags"] == "NOTEMPLATE")) {
				draw_nontemplated_item($field_array, $field_name, (isset($data) ? $data[$field_name] : ""));
			}else{
				draw_templated_item($field_array, $field_name, (isset($data) ? $data[$field_name] : ""));
			}
		}
		
		print "</tr>\n";
	}
	
	end_box();
	
	/* fetch ALL rrd's for this data source */
	if (!empty($_GET["local_data_id"])) {
		$template_data_rrds = db_fetch_assoc("select id,data_source_name from data_template_rrd where local_data_id=" . $_GET["local_data_id"] . " order by data_source_name");
	}
	
	/* select the first "rrd" of this data source by default */
	if (empty($_GET["view_rrd"])) {
		$_GET["view_rrd"] = (isset($template_data_rrds[0]["id"]) ? $template_data_rrds[0]["id"] : "0");
	}
	
	/* get more information about the rrd we chose */
	if (!empty($_GET["view_rrd"])) {
		$local_data_template_rrd_id = db_fetch_cell("select local_data_template_rrd_id from data_template_rrd where id=" . $_GET["view_rrd"]);
		
		$rrd = db_fetch_row("select * from data_template_rrd where id=" . $_GET["view_rrd"]);
		$rrd_template = db_fetch_row("select * from data_template_rrd where id=$local_data_template_rrd_id");
	}
	
	start_box("<strong>Data Source Item</strong> [edit: " . (isset($rrd) ? $rrd["data_source_name"] : "") . "]", "98%", $colors["header"], "3", "center", "");
	
	$i = 0;
	if (isset($template_data_rrds)) {
		if (sizeof($template_data_rrds) > 1) {
			?>
			<tr height="33">
				<td valign="bottom" colspan="3" background="images/tab_back_light.gif">
					<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<?php
							foreach ($template_data_rrds as $template_data_rrd) {
							$i++;
							?>
							<td nowrap class="textTab" align="center" background="images/tab_middle.gif">
								<img src="images/tab_left.gif" border="0" align="absmiddle"><a class="linkTabs" href="data_sources.php?action=ds_edit&local_data_id=<?php print $_GET["local_data_id"];?>&view_rrd=<?php print $template_data_rrd["id"];?>"><?php print "$i: " . $template_data_rrd["data_source_name"];?></a><img src="images/tab_right.gif" border="0" align="absmiddle">
							</td>
							<?php
							}
							?>
						</tr>
					</table>
				</td>
			</tr>
			<?php
		}elseif (sizeof($template_data_rrds) == 1) {
			$_GET["view_rrd"] = $template_data_rrds[0]["id"];
		}
	}
	
	print "<form method='post' action='data_sources.php'>";
	
	$i = 0;
	while (list($field_name, $field_array) = each($struct_data_source_item)) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
		
		print "<td width='50%'><font class='textEditTitle'>" . $field_array["title"] . "</font><br>\n";
		
		if (($use_data_template == false) || ($rrd_template{"t_" . $field_name} == "on")) {
			print $field_array["description"];
		}
		
		print "</td>\n";
		
		if (($use_data_template == false) || ($rrd_template{"t_" . $field_name} == "on")) {
			draw_nontemplated_item($field_array, $field_name, (isset($rrd) ? $rrd[$field_name] : ""));
		}else{
			draw_templated_item($field_array, $field_name, (isset($rrd) ? $rrd[$field_name] : ""));
		}
		
		print "</tr>\n";
	}
	
	end_box();
	
	if (read_config_option("full_view_data_source") == "on") {
		data_edit();	
	}
	
	form_hidden_id("_data_template_id",(isset($data) ? $data["data_template_id"] : "0"));
	form_hidden_box("_host_id",$host_id,(isset($_GET["host_id"]) ? $_GET["host_id"] : "0"));
	form_hidden_id("data_template_data_id",(isset($data) ? $data["id"] : "0"));
	form_hidden_id("data_template_rrd_id",(isset($rrd) ? $rrd["id"] : "0"));
	form_hidden_id("local_data_template_data_id",(isset($data) ? $data["local_data_template_data_id"] : "0"));
	form_hidden_id("local_data_template_rrd_id",(isset($rrd) ? $rrd["local_data_template_rrd_id"] : "0"));
	form_hidden_id("local_data_id",(isset($data) ? $data["local_data_id"] : "0"));
	form_hidden_id("current_rrd",$_GET["view_rrd"]);
	form_hidden_box("save_component_data_source","1","");
	
	form_save_button("data_sources.php");	
}

function ds() {
	global $colors;
	
	include_once ('include/tree_view_functions.php');
	
	/* if no host_id is specified, use the session one */
	if (!isset($_GET["host_id"])) {
		$_GET["host_id"] = (empty($_SESSION["sess_data_source_current_host_id"]) ? 0 : $_SESSION["sess_data_source_current_host_id"]);
	}
	
	/* remember the last used host_id */
	$_SESSION["sess_data_source_current_host_id"] = $_GET["host_id"];
	
	start_box("<strong>Data Sources</strong>", "98%", $colors["header"], "3", "center", "data_sources.php?action=ds_edit");
	?>
	
	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_graph_id">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="100">
						Select a host:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_graph_id" onChange="window.location=document.form_graph_id.cbo_graph_id.options[document.form_graph_id.cbo_graph_id.selectedIndex].value">
							<option value="data_sources.php?host_id=0"<?php if ($_GET["host_id"] == "0") {?> selected<?php }?>>None</option>
							
							<?php
							$hosts = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");
							
							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='data_sources.php?host_id=" . $host["id"] . "'"; if ($_GET["host_id"] == $host["id"]) { print " selected"; } print ">" . $host["name"] . "</option>\n";
							}
							}
							?>
							
						</select>
					</td>
					<td>
						&nbsp;<a href="data_sources.php<?php print $main_action;?>"><img src="images/button_go.gif" alt="Go" border="0" align="absmiddle"></a><br>
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>
	<?php
	end_box();
	
	$host = db_fetch_row("select hostname from host where id=" . $_GET["host_id"]);
	
	start_box("<strong>Data Sources</strong> [host: " . (empty($host["hostname"]) ? "No Host" : $host["hostname"]) . "]", "98%", $colors["header"], "3", "center", "data_sources.php?action=ds_edit&host_id=" . $_GET["host_id"]);
	
	print "	<tr bgcolor='#" . $colors["header_panel"] . "'>
			<td class='textSubHeaderDark'>Name</td>
			<td class='textSubHeaderDark'>Data Input Method</td>
			<td class='textSubHeaderDark' colspan='2'>Template Name</td>
		</tr>\n";
	
	$data_sources = db_fetch_assoc("select
		data_template_data.local_data_id,
		data_template_data.name,
		data_input.name as data_input_name,
		data_template.name as data_template_name
		from data_local
		left join data_template_data
		on data_local.id=data_template_data.local_data_id
		left join data_input
		on data_input.id=data_template_data.data_input_id
		left join data_template
		on data_local.data_template_id=data_template.id
		where data_local.host_id=" . $_GET["host_id"] . "
		order by data_template_data.name");
	
	$i = 0;
	if (sizeof($data_sources) > 0) {
	foreach ($data_sources as $data_source) {
		form_alternate_row_color($colors["alternate"],$colors["light"],$i); $i++;
		print "<td><a class='linkEditMain' href='data_sources.php?action=ds_edit&local_data_id=" . $data_source["local_data_id"] . "'>" . $data_source["name"] . "</a></td>";
		print "<td>" . $data_source["data_input_name"] . "</td>";
		print "<td>" . ((empty($data_source["data_template_name"])) ? "<em>None</em>" : $data_source["data_template_name"]) . "</td>";
		print "<td width='1%' align='right'><a href='data_sources.php?action=ds_remove&local_data_id=" . $data_source["local_data_id"] . "'><img src='images/delete_icon.gif' width='10' height='10' border='0' alt='Delete'></a>&nbsp;</td>";
		print "</tr>";
	}
	}else{
		print "<tr><td><em>No Data Sources</em></td></tr>";
	}
	
	end_box();
}
?>
