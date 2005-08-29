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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_query/data_query_execute.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_push.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_form.php");
require_once(CACTI_BASE_PATH . "/lib/graph/graph_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_form.php");
require_once(CACTI_BASE_PATH . "/lib/data_template/data_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/data_source/data_source_update.php");
require_once(CACTI_BASE_PATH . "/include/data_source/data_source_constants.php");
require_once(CACTI_BASE_PATH . "/lib/utility.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");
require_once(CACTI_BASE_PATH . "/lib/sys/sort.php");
require_once(CACTI_BASE_PATH . "/lib/sys/html_form_template.php");
require_once(CACTI_BASE_PATH . "/lib/template.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'query_reload':
		host_reload_query();

		header("Location: graphs_new.php?host_id=" . $_GET["host_id"]);
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		graphs();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* --------------------------
    The Save Function
   -------------------------- */

function form_save() {
	if (isset($_POST["save_component_graph"])) {
		host_new_graphs();
	}

	if (isset($_POST["save_component_new_graphs"])) {
		host_new_graphs_save();

		header("Location: graphs_new.php?host_id=" . $_POST["host_id"]);
	}
}

/* -------------------
    Data Query Functions
   ------------------- */

function host_reload_query() {
	run_data_query($_GET["host_id"], $_GET["id"]);
}

/* -------------------
    New Graph Functions
   ------------------- */

function host_new_graphs_save() {
	$validation_array = array();
	$selected_graphs_array = unserialize(stripslashes($_POST["selected_graphs_array"]));
	$map_id_to_index_array = unserialize(stripslashes($_POST["map_id_to_index_array"]));

	/* form an array that contains all of the data on the previous form */
	while (list($var, $val) = each($_POST)) {
		if (preg_match("/^g_(\d+)_(\d+)_(\w+)/", $var, $matches)) { /* 1: data_query_id, 2: graph_template_id, 3: field_name */
			foreach ($map_id_to_index_array{empty($matches[1]) ? "gt" : "dq"}{empty($matches[1]) ? $matches[2] : $matches[1]} as $uniq_id) {
				$selected_graphs_array[$uniq_id]["graph_template"]{$matches[3]} = $val;
			}

			$validation_array["graph_template"]{$matches[3]}[$var] = $val;
		}elseif (preg_match("/^gi_(\d+)_(\d+)_(\d+)_(\w+)/", $var, $matches)) { /* 1: data_query_id, 2: graph_template_id, 3: graph_template_input_id, 4: field_name */
			foreach ($map_id_to_index_array{empty($matches[1]) ? "gt" : "dq"}{empty($matches[1]) ? $matches[2] : $matches[1]} as $uniq_id) {
				$selected_graphs_array[$uniq_id]["graph_template_item"]{$matches[2]}{$matches[3]} = $val;
			}

			$validation_array["graph_template_item"]{$matches[4]}[$var] = $val;
		}elseif (preg_match("/^d_(\d+)_(\d+)_(\d+)_(\w+)/", $var, $matches)) { /* 1: data_query_id, 2: graph_template_id, 3: data_template_id, 4: field_name */
			foreach ($map_id_to_index_array{empty($matches[1]) ? "gt" : "dq"}{empty($matches[1]) ? $matches[2] : $matches[1]} as $uniq_id) {
				$selected_graphs_array[$uniq_id]["data_template"]{$matches[3]}{$matches[4]} = $val;
			}

			$validation_array["data_template"]{$matches[4]}[$var] = $val;
		}elseif (preg_match("/^c_(\d+)_(\d+)_(\d+)_(\d+)/", $var, $matches)) { /* 1: data_query_id, 2: graph_template_id, 3: data_template_id, 4: data_input_field_name */
			foreach ($map_id_to_index_array{empty($matches[1]) ? "gt" : "dq"}{empty($matches[1]) ? $matches[2] : $matches[1]} as $uniq_id) {
				$selected_graphs_array[$uniq_id]["custom_data"]{$matches[3]}{$matches[4]} = $val;
			}

			$validation_array["custom_data"]{$matches[4]}[$var] = $val;
		}elseif (preg_match("/^di_(\d+)_(\d+)_(\d+)_(\d+)_(\w+)/", $var, $matches)) { /* 1: data_query_id, 2: graph_template_id, 3: data_template_id, 4: data_template_item_id, 5: field_name */
			foreach ($map_id_to_index_array{empty($matches[1]) ? "gt" : "dq"}{empty($matches[1]) ? $matches[2] : $matches[1]} as $uniq_id) {
				$selected_graphs_array[$uniq_id]["data_template_item"]{$matches[3]}{$matches[4]}{$matches[5]} = $val;
			}

			$validation_array["data_template_item"]{$matches[5]}[$var] = $val;
		}
	}

	/* first pass: form validation */
	while (list($type, $type_array) = each($validation_array)) {
		while (list($field_name, $field_array) = each($type_array)) {
			while (list($form_field_name, $value) = each($field_array)) {
				$_v_arr = array($field_name => $value);

				if ($type == "data_template") {
					$_sv_arr = array();
					field_register_error(api_data_source_validate_fields_base($_v_arr, $_sv_arr, $form_field_name, ""));
				} else if ($type == "custom_data") {
					field_register_error(api_data_source_validate_fields_input($_v_arr, $form_field_name));
				} else if ($type == "data_template_item") {
					$_v_arr["id"] = 0;
					field_register_error(api_data_source_validate_fields_item($_v_arr, $form_field_name));
				} else if ($type == "graph_template") {
					$_sv_arr = array();
					field_register_error(validate_graph_fields($_v_arr, $_sv_arr, $form_field_name, ""));
				} else if ($type == "graph_template_item") {
					field_register_error(validate_graph_item_fields($_v_arr, $form_field_name));
				}
			}
		}
	}

	/* form validation failed: redirect back */
	if (is_error_message()) {
		/* cache all post field values */
		init_post_field_cache();

		host_new_graphs($selected_graphs_array, $map_id_to_index_array);
	/* form validation passed: save the data on the form */
	}else{
		debug_log_clear("new_graphs");

		foreach ($selected_graphs_array as $uniq_id => $skel) {
			if (isset($skel["custom_data"]["all_dq"])) {
				$is_data_query_graph = true;
				$data_query_id = (isset($skel["custom_data"]["all_dq"]["data_query_id"]) ? $skel["custom_data"]["all_dq"]["data_query_id"] : 0);

				/* decode the data query index into its literal form */
				$data_query_index = decode_data_query_index((isset($skel["custom_data"]["all_dq"]["data_query_index"]) ? $skel["custom_data"]["all_dq"]["data_query_index"] : 0), get_data_query_indexes($_POST["host_id"], $data_query_id));
			}else{
				$is_data_query_graph = false;
				$data_query_id = 0;
				$data_query_index = "";
			}

			$create_info = generate_complete_graph($skel["graph_template_id"], $_POST["host_id"], $data_query_id, $data_query_index);

			/* set the appropriate 'custom_data' keys */
			if ($is_data_query_graph == true) {
				/* pick the data query field name to index on */
				$data_query_field_name = get_best_data_query_index_type($_POST["host_id"], $data_query_id);

				/* get a list of all data templates associated with this graph template that are of type DATA_INPUT_TYPE_DATA_QUERY */
				$dq_data_templates = get_data_templates_from_graph_template($skel["graph_template_id"], DATA_INPUT_TYPE_DATA_QUERY);

				if (sizeof($dq_data_templates) > 0) {
					foreach ($dq_data_templates as $data_template) {
						$skel["custom_data"]{$data_template["id"]}["data_query_id"] = $data_query_id;
						$skel["custom_data"]{$data_template["id"]}["data_query_index"] = $data_query_index;
						$skel["custom_data"]{$data_template["id"]}["data_query_field_name"] = $data_query_field_name;
						$skel["custom_data"]{$data_template["id"]}["data_query_field_value"] = get_data_query_row_value($data_query_field_name, $data_query_index, $_POST["host_id"], $data_query_id);
					}
				}
			}

			/* update user specified data: data source-specific fields */
			foreach (array_keys($create_info["data_source"]) as $data_template_id) {
				if (isset($skel["data_template"][$data_template_id])) {
					$_arr_rra = array(); /* placeholder */
					if (!api_data_source_save($create_info["data_source"][$data_template_id], $skel["data_template"][$data_template_id], $_arr_rra)) {
						api_syslog_cacti_log("Problems updating new data source [ID#" . $create_info["data_source"][$data_template_id] . "], data template [ID#$data_template_id] from user data", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
					}
				}

				if (isset($skel["data_template_item"][$data_template_id])) {
					foreach ($skel["data_template_item"][$data_template_id] as $data_source_item_id => $data_template_item_array) {
						if (!api_data_source_item_save($data_source_item_id, $data_template_item_array)) {
							api_syslog_cacti_log("Problems updating new data source [item] [ID#" . $create_info["data_source"][$data_template_id] . "], data template [ID#$data_template_id] from user data", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
						}
					}
				}

				if (isset($skel["custom_data"][$data_template_id])) {
					if (!api_data_source_fields_save($create_info["data_source"][$data_template_id], $skel["custom_data"][$data_template_id])) {
						api_syslog_cacti_log("Problems updating new data source (fields) [ID#" . $create_info["data_source"][$data_template_id] . "], data template [ID#$data_template_id] from user data", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
					}
				}

				/* update the title cache */
				api_data_source_title_cache_update($create_info["data_source"][$data_template_id]);

				/* update poller cache */
				update_poller_cache($create_info["data_source"][$data_template_id]);
			}

			/* update user specified data: graph-specific fields */
			foreach (array_keys($create_info["graph"]) as $graph_template_id) {
				if (isset($skel["graph_template"][$graph_template_id])) {
					if (!api_graph_save($create_info["graph"][$graph_template_id], $skel["graph_template"][$graph_template_id])) {
						api_syslog_cacti_log("Problems updating new graph [ID#" . $create_info["graph"][$graph_template_id] . "], graph template [ID#$graph_template_id] from user data", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
					}
				}

				if (isset($skel["graph_template_item"][$graph_template_id])) {
					foreach ($skel["graph_template_item"][$graph_template_id] as $graph_template_item_input_id => $value) {
						if (!api_graph_template_item_input_propagate($graph_template_item_input_id, $value)) {
							api_syslog_cacti_log("Problems updating new graph [item] [ID#" . $create_info["graph"][$graph_template_id] . "], graph template [ID#$graph_template_id] from user data", SEV_ERROR, 0, 0, 0, false, FACIL_WEBUI);
						}
					}
				}

				/* update the title cache */
				update_graph_title_cache($create_info["graph"][$graph_template_id]);
			}

			debug_log_insert("new_graphs", _("Created graph: ") . get_graph_title($create_info["graph"]{$skel["graph_template_id"]}));
		}

		/* lastly push host-specific information to our data sources */
		//push_out_host($_POST["host_id"], 0);
	}
}

function host_new_graphs($selected_graphs = "", $map_id_to_index = "") {
	global $colors;

	if (!is_array($selected_graphs)) {
		$selected_graphs = array();
		$map_id_to_index = array();

		/* summarize the 'create graph from host template/snmp index' stuff into an array */
		while (list($name, $value) = each($_POST)) {
			if ((substr($name, 0, 3) == "cg_") && ($name != "cg_g")) {
				$matches = explode("_", $name);

				/* uniquely identify this yet to be created graph */
				$uniq_id = md5("gt" . $matches[1]);

				$map_id_to_index["gt"]{$matches[1]}[] = $uniq_id;
				$selected_graphs[$uniq_id]["graph_template_id"] = $matches[1];
			}else if (substr($name, 0, 3) == "sg_") {
				$matches = explode("_", $name);

				/* uniquely identify this yet to be created graph */
				$uniq_id = md5("dq" . $matches[1] . $matches[2]);

				$map_id_to_index["dq"]{$matches[1]}[] = $uniq_id;
				$selected_graphs[$uniq_id]["graph_template_id"] = $_POST{"sgg_" . $matches[1]};
				$selected_graphs[$uniq_id]["custom_data"]["all_dq"]["data_query_id"] = $matches[1];
				$selected_graphs[$uniq_id]["custom_data"]["all_dq"]["data_query_index"] = $matches[2];
			}else if (($name == "cg_g") && (!empty($value))) {
				/* uniquely identify this yet to be created graph */
				$uniq_id = md5("gt" . $_POST["cg_g"]);

				$map_id_to_index["gt"]{$_POST["cg_g"]}[] = $uniq_id;
				$selected_graphs[$uniq_id]["graph_template_id"] = $_POST["cg_g"];
			}
		}
	}

	/* we use object buffering on this page to allow redirection to another page if no
	fields are actually drawn */
	ob_start();

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	print "<form method='post' action='graphs_new.php'>\n";

	$num_output_fields = 0;
	$data_query_id = 0;
	$graph_template_id = 0;

	foreach ($map_id_to_index as $form_type => $form_type_array) {
		foreach ($form_type_array as $form_type_index => $form_type_index_array) {
			if ($form_type == "gt") {
				$graph_template_id = $form_type_index;
				$data_query_id = 0;

				html_start_box("<strong>Create Graph from '" . db_fetch_cell("select template_name from graph_template where id = $graph_template_id") . "'", "98%", $colors["header_background"], "3", "center", "");
			}else if ($form_type == "dq") {
				/* one graph_template_id per data query, at a time */
				$arr_values = array_values($form_type_index_array);
				$graph_template_id = $selected_graphs{$arr_values[0]}["graph_template_id"];

				$data_query_id = $form_type_index;
				$num_graphs = sizeof($form_type_index_array);

				/* DRAW: Data Query */
				html_start_box("<strong>" . _("Create") . " $num_graphs " . _("Graph") . (($num_graphs > 1) ? "s" : "") . " from '" . db_fetch_cell("select name from snmp_query where id = $data_query_id") . "'", "98%", $colors["header_background"], "3", "center", "");
			}

			/* get information about this graph template */
			$graph_template = db_fetch_row("select * from graph_template where id = $graph_template_id");

			$num_output_fields += draw_nontemplated_fields_graph($graph_template_id, $graph_template, "g_$data_query_id" . "_" . $graph_template_id . "_|field|", "<strong>Graph</strong> [Template: " . $graph_template["template_name"] . "]", false);
			$num_output_fields += draw_nontemplated_fields_graph_item($graph_template_id, array_rekey(db_fetch_assoc("select * from graph_template_item where graph_template_id = $graph_template_id"), "id", array("id", "data_template_item_id", "color", "graph_item_type", "cdef", "consolidation_function", "gprint_format", "legend_format", "legend_value", "hard_return")), "gi_" . $data_query_id . "_" . $graph_template_id . "_|id|_|field|", "<strong>" . _("Graph Items") . "</strong> [" . _("Template: ") . $graph_template["template_name"] . "]", false);

			/* get information about each data template referenced by this graph template */
			$data_templates = db_fetch_assoc("select distinct
				data_template.*
				from data_template,data_template_item,graph_template_item
				where graph_template_item.data_template_item_id=data_template_item.id
				and data_template_item.data_template_id=data_template.id
				and graph_template_item.graph_template_id = $graph_template_id");

			/* DRAW: Data Sources */
			if (sizeof($data_templates) > 0) {
				foreach ($data_templates as $data_template) {
					$num_output_fields += draw_nontemplated_fields_data_source($data_template["id"], $data_template, "d_" . $data_query_id . "_" . $graph_template_id . "_" . $data_template["id"] . "_|field|", true);
					$num_output_fields += draw_nontemplated_fields_data_source_item($data_template["id"], db_fetch_assoc("select * from data_template_item where data_template_id = " . $data_template["id"] . " order by data_source_name"), "di_" . $data_query_id . "_" . $graph_template_id . "_" . $data_template["id"] . "_|id|_|field|", true);
					$num_output_fields += draw_nontemplated_fields_data_input($data_template["id"], array_rekey(db_fetch_assoc("select name,value from data_template_field where data_template_id = " . $data_template["id"]), "name", array("value")), "c_" . $data_query_id . "_" . $graph_template_id . "_" . $data_template["id"] . "_|field|", "<strong>" . _("Custom Data") . "</strong> [" . _("Template: ") . $data_template["template_name"] . "]", false);
				}
			}

			html_end_box();
		}
	}

	/* no fields were actually drawn on the form; just save without prompting the user */
	if ($num_output_fields == 0) {
		ob_end_clean();

		/* since the user didn't actually click "Create" to POST the data; we have to
		pretend like they did here */
		$_POST["host_id"] = $_POST["host_id"];
		$_POST["save_component_new_graphs"] = "1";
		$_POST["selected_graphs_array"] = addslashes(serialize($selected_graphs));
		$_POST["map_id_to_index_array"] = addslashes(serialize($map_id_to_index));

		host_new_graphs_save();

		header("Location: graphs_new.php?host_id=" . $_POST["host_id"]);
		exit;
	}

	/* flush the current output buffer to the browser */
	ob_end_flush();

	form_hidden_box("host_id", $_POST["host_id"], "0");
	form_hidden_box("save_component_new_graphs", "1", "");
	form_hidden_box("selected_graphs_array", serialize($selected_graphs), "");
	form_hidden_box("map_id_to_index_array", serialize($map_id_to_index), "");

	form_save_button("graphs_new.php?host_id=" . $_POST["host_id"]);

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/* -------------------
    Graph Functions
   ------------------- */

function graphs() {
	global $colors;

	/* use the first host in the list as the default */
	if ((!isset($_SESSION["sess_graphs_new_host_id"])) && (empty($_REQUEST["host_id"]))) {
		$_REQUEST["host_id"] = db_fetch_cell("select id from host order by description,hostname limit 1");
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	if (isset($_REQUEST["host_id"])) { $_SESSION["sess_graphs_new_host_id"] = $_REQUEST["host_id"]; }else{ $_REQUEST["host_id"] = $_SESSION["sess_graphs_new_host_id"]; }

	$host = db_fetch_row("select id,description,hostname,host_template_id from host where id=" . $_REQUEST["host_id"]);

	$debug_log = debug_log_return("new_graphs");

	if (!empty($debug_log)) {
		debug_log_clear("new_graphs");
		?>
		<table width='98%' style='background-color: #<?php print $colors["messagebar_background"];?>; border: 1px solid #<?php print $colors["messagebar_border"];?>;' align='center'>
			<tr bgcolor="<?php print $colors["form_alternate2"];?>">
				<td style="padding: 3px; font-family: monospace;">
					<?php print $debug_log;?>
				</td>
			</tr>
		</table>
		<br>
		<?php
	}
	?>

	<table width="98%" align="center">
		<form name="form_graph_id">
		<tr>
			<td class="textInfo" colspan="2">
				<?php print $host["description"];?> (<?php print $host["hostname"];?>)
			</td>
			<td align="right" class="textInfo" style="color: #aaaaaa;">
				<?php
				if (!empty($host["host_template_id"])) {
					print db_fetch_cell("select name from host_template where id=" . $host["host_template_id"]);
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>

		<tr>
			<td class="textArea" style="padding: 3px;" width="300" nowrap>
				<?php echo _("Create new graphs for the following host:");?>
			</td>
			<td class="textInfo" rowspan="2" valign="top">
				<span style="color: #c16921;">*</span><a href="host.php?action=edit&id=<?php print $_REQUEST["host_id"];?>"><?php echo _("Edit this Host");?></a><br>
				<span style="color: #c16921;">*</span><a href="host.php?action=edit"><?php echo _("Create New Host");?></a>
			</td>
		</tr>
			<td>
				<select name="cbo_graph_id" onChange="window.location=document.form_graph_id.cbo_graph_id.options[document.form_graph_id.cbo_graph_id.selectedIndex].value">
					<?php
					$hosts = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");

					if (sizeof($hosts) > 0) {
						foreach ($hosts as $item) {
							print "<option value='graphs_new.php?host_id=" . $item["id"] . "'"; if ($_REQUEST["host_id"] == $item["id"]) { print " selected"; } print ">" . $item["name"] . "</option>\n";
						}
					}
					?>
				</select>
			</td>
		</tr>
		</form>
	</table>

	<br>

	<form name="chk" method="post" action="graphs_new.php">
	<?php
	$total_rows = sizeof(db_fetch_assoc("select graph_template_id from host_graph where host_id=" . $_REQUEST["host_id"]));

	/* we give users the option to turn off the javascript features for data queries with lots of rows */
	if (read_config_option("max_data_query_javascript_rows") >= $total_rows) {
		$use_javascript = true;
	}else{
		$use_javascript = false;
	}

	/* ==================== Box: Graph Templates ==================== */

	html_start_box("<strong>" . _("Graph Templates") . "</strong>", "98%", $colors["header_background"], "3", "center", "");

	print "	<tr bgcolor='#" . $colors["header_panel"] . "'>
			<td class='textSubHeaderDark'>" . _("Name") . "</td>
			<td width='1%' align='center' bgcolor='#819bc0' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='all_cg' title='" . _("Select All") . "' onClick='SelectAll(\"cg\",this.checked);gt_update_selection_indicators();'></td>\n
		</tr>\n";

	$ht_graph_templates = db_fetch_assoc("select
		graph_template.id,
		graph_template.template_name
		from host_graph,graph_template
		where host_graph.graph_template_id=graph_template.id
		and host_graph.host_id = " . $_REQUEST["host_id"] . "
		order by graph_template.template_name");

	$ht_created_graph_templates = db_fetch_assoc("select
		graph.graph_template_id
		from graph,host_graph
		where graph.graph_template_id=host_graph.graph_template_id
		and graph.host_id = " . $host["id"] . "
		group by graph.graph_template_id");

	print "<script type='text/javascript'>\nvar gt_created_graphs = new Array()\n</script>\n";

	if ((sizeof($ht_created_graph_templates) > 0) && ($use_javascript == true)) {
		print "<script type='text/javascript'>\n<!--\n";
		print "var gt_created_graphs = new Array(";

		$cg_ctr = 0;
		foreach ($ht_created_graph_templates as $item) {
			print (($cg_ctr > 0) ? "," : "") . "'" . $item["graph_template_id"] . "'";

			$cg_ctr++;
		}

		print ")\n";
		print "//-->\n</script>\n";
	}

	/* create a row for each graph template associated with the host template */
	$i = 0;
	if (sizeof($ht_graph_templates) > 0) {
		foreach ($ht_graph_templates as $item) {
			$query_row = $item["id"];

			print "<tr id='gt_line$query_row' bgcolor='#" . (($i % 2 == 0) ? $colors["form_alternate1"] : $colors["form_alternate2"]) . "'>"; $i++;

			print "		<td" . (($use_javascript == true) ? " onClick='gt_select_line(" . $item["id"] . ");'" : "") . "><span id='gt_text$query_row" . "_0'>
						<span id='gt_text$query_row" . "_0'><strong>" . _("Create:") . "</strong> " . $item["template_name"] . "</span>
					</td>
					<td align='right'>
						<input type='checkbox' name='cg_$query_row' id='cg_$query_row'" . (($use_javascript == true) ? " onClick='gt_update_selection_indicators();'" : "") . ">
					</td>
				</tr>";
		}
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>" . _("No graph templates specified for this host template.") . "</em></td></tr>";
	}

	if ($use_javascript == true) {
		print "<script type='text/javascript'>gt_update_deps(1);</script>\n";
	}

	$available_graph_templates = db_fetch_assoc("SELECT
		graph_template.id,
		graph_template.template_name as name
		FROM snmp_query_graph RIGHT JOIN graph_template
		ON snmp_query_graph.graph_template_id = graph_template.id
		WHERE (((snmp_query_graph.name) Is Null))
		ORDER BY graph_template.template_name");

	/* create a row at the bottom that lets the user create any graph they choose */
	print "	<tr bgcolor='#" . (($i % 2 == 0) ? $colors["form_alternate1"] : $colors["form_alternate2"]) . "'>
			<td colspan='2' width='60' nowrap>
				<strong>Create:</strong>&nbsp;";
				form_dropdown("cg_g", $available_graph_templates, "name", "id", "", "(" . _("Select a graph type to create") . ")", "", "font-size: 10px;");
	print "		</td>
		</tr>";

	html_end_box();

	$data_queries = db_fetch_assoc("select
		snmp_query.id,
		snmp_query.name,
		snmp_query.xml_path
		from snmp_query,host_snmp_query
		where host_snmp_query.snmp_query_id=snmp_query.id
		and host_snmp_query.host_id = " . $host["id"] . "
		order by snmp_query.name");

	print "<script type='text/javascript'>\nvar created_graphs = new Array()\n</script>\n";

	if (sizeof($data_queries) > 0) {
		foreach ($data_queries as $data_query) {
			unset($total_rows);

			$xml_array = get_data_query_array($data_query["id"]);

			$num_input_fields = 0;
			$num_visible_fields = 0;

			if ($xml_array != false) {
				/* loop through once so we can find out how many input fields there are */
				reset($xml_array["fields"]);
				while (list($field_name, $field_array) = each($xml_array["fields"])) {
					if ($field_array["direction"] == "input") {
						$num_input_fields++;

						if (!isset($total_rows)) {
							$total_rows = db_fetch_cell("select count(*) from host_snmp_cache where host_id = " . $host["id"] . " and snmp_query_id = " . $data_query["id"] . " and field_name = '$field_name'");
						}
					}
				}
			}

			if (!isset($total_rows)) {
				$total_rows = 0;
			}

			/* we give users the option to turn off the javascript features for data queries with lots of rows */
			if (read_config_option("max_data_query_javascript_rows") >= $total_rows) {
				$use_javascript = true;
			}else{
				$use_javascript = false;
			}

			$data_query_graphs = db_fetch_assoc("select distinct
				graph_template.id,
				graph_template.template_name
				from graph_template,graph_template_item,data_template_item,data_template,data_template_field
				where graph_template.id=graph_template_item.graph_template_id
				and graph_template_item.data_template_item_id=data_template_item.id
				and data_template_item.data_template_id=data_template.id
				and data_template.id=data_template_field.data_template_id
				and data_template.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
				and (data_template_field.name = 'data_query_id' and data_template_field.value = '" . $data_query["id"] . "')");

			if ((sizeof($data_query_graphs) > 0) && ($use_javascript == true)) {
				print "<script type='text/javascript'>\n<!--\n";

				foreach ($data_query_graphs as $data_query_graph) {
					$created_graphs = db_fetch_assoc("select distinct
						data_source_field.value as data_query_index
						from graph,graph_item,data_source_item,data_source,data_source_field
						where graph.id=graph_item.graph_id
						and graph_item.data_source_item_id=data_source_item.id
						and data_source_item.data_source_id=data_source.id
						and data_source.id=data_source_field.data_source_id
						and graph.graph_template_id = " . $data_query_graph["id"] . "
						and graph.host_id = " . $host["id"] . "
						and data_source.data_input_type = " . DATA_INPUT_TYPE_DATA_QUERY . "
						and data_source_field.name = 'data_query_index'");

					print "created_graphs[" . $data_query_graph["id"] . "] = new Array(";

					$cg_ctr = 0;
					if (sizeof($created_graphs) > 0) {
						foreach ($created_graphs as $created_graph) {
							print (($cg_ctr > 0) ? "," : "") . "'" . encode_data_query_index($created_graph["data_query_index"]) . "'";

							$cg_ctr++;
						}
					}

					print ")\n";
				}

				print "//-->\n</script>\n";
			}

			print "	<table width='98%' style='background-color: #" . $colors["form_alternate2"] . "; border: 1px solid #" . $colors["header_background"] . ";' align='center' cellpadding='3' cellspacing='0'>\n
					<tr>
						<td bgcolor='#" . $colors["header_background"] . "' colspan='" . ($num_input_fields+1) . "'>
							<table  cellspacing='0' cellpadding='0' width='100%' >
								<tr>
									<td class='textHeaderDark'>
										<strong>" . _("Data Query") . " </strong> [" . $data_query["name"] . "]
									</td>
									<td align='right' nowrap>
										<a href='graphs_new.php?action=query_reload&id=" . $data_query["id"] . "&host_id=" . $host["id"] . "'><img src='". html_get_theme_images_path("reload_icon_small.gif") . "' alt='" . _("Reload Associated Query") . "' border='0' align='absmiddle'></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>";

		if ($xml_array != false) {
			$html_dq_header = "";
			$data_query_indexes = array();

			reset($xml_array["fields"]);
			while (list($field_name, $field_array) = each($xml_array["fields"])) {
				if ($field_array["direction"] == "input") {
					$raw_data = db_fetch_assoc("select field_value,snmp_index from host_snmp_cache where host_id = " . $host["id"] . " and field_name = '$field_name' and snmp_query_id = " . $data_query["id"]);

					/* don't even both to display the column if it has no data */
					if (sizeof($raw_data) > 0) {
						/* draw each header item <TD> */
						$html_dq_header .= "<td height='1'><strong><font color='#" . $colors["header_text"] . "'>" . $field_array["name"] . "</font></strong></td>\n";

						foreach ($raw_data as $data) {
							$data_query_data[$field_name]{$data["snmp_index"]} = $data["field_value"];

							if (!in_array($data["snmp_index"], $data_query_indexes, true)) {
								array_push($data_query_indexes, $data["snmp_index"]);
							}
						}

						$num_visible_fields++;
					}elseif (sizeof($raw_data) == 0) {
						/* we are choosing to not display this column, so unset the associated
						field in the xml array so it is not drawn */
						unset($xml_array["fields"][$field_name]);
					}
				}
			}

			/* if the user specified a prefered sort order; sort the list of indexes before displaying them */
			if (isset($xml_array["index_order_type"])) {
				if ($xml_array["index_order_type"] == "numeric") {
					usort($data_query_indexes, "usort_numeric");
				}else if ($xml_array["index_order_type"] == "alphabetic") {
					usort($data_query_indexes, "usort_alphabetic");
				}
			}

			if (sizeof($data_query_graphs) == 0) {
				print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td>" . _("This data query is not being used by any graph templates. You must create at
					least one graph template that references to a data template using this data query.") . "</td></tr>\n";
			}else if ($num_visible_fields == 0) {
				print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td>" . _("This data query returned 0 rows, perhaps there was a problem executing this
					data query. You can") . " <a href='host.php?action=query_verbose&id=" . $data_query["id"] . "&host_id=" . $host["id"] . "'>" . _("run this data
					query in debug mode</a> to get more information.") . "</td></tr>\n";
			}else{
				print "	<tr bgcolor='#" . $colors["header_panel_background"] . "'>
						$html_dq_header
						<td width='1%' align='center' bgcolor='#" . $colors["header_panel_background"] . "' style='" . get_checkbox_style() . "'><input type='checkbox' style='margin: 0px;' name='all_" . $data_query["id"] . "' title='Select All' onClick='" . _("SelectAll") . "(\"sg_" . $data_query["id"] . "\",this.checked);" . (($use_javascript == true) ? "dq_update_selection_indicators();" : "") . "'></td>\n
					</tr>\n";
			}

			$row_counter = 0;
			if ((sizeof($data_query_indexes) > 0) && (sizeof($data_query_graphs) > 0)) {
				while (list($id, $data_query_index) = each($data_query_indexes)) {
					$query_row = $data_query["id"] . "_" . encode_data_query_index($data_query_index);

					print "<tr id='line$query_row' bgcolor='#" . (($row_counter % 2 == 0) ? $colors["form_alternate1"] : $colors["form_alternate2"]) . "'>"; $i++;

					$column_counter = 0;
					reset($xml_array["fields"]);
					while (list($field_name, $field_array) = each($xml_array["fields"])) {
						if ($field_array["direction"] == "input") {
							if (isset($data_query_data[$field_name][$data_query_index])) {
								print "<td " . (($use_javascript == true) ? "onClick='dq_select_line(" . $data_query["id"] . ",\"" . encode_data_query_index($data_query_index) . "\");'" : "")  ."><span id='text$query_row" . "_" . $column_counter . "'>" . $data_query_data[$field_name][$data_query_index] . "</span></td>";
							}else{
								print "<td " . (($use_javascript == true) ? "onClick='dq_select_line(" . $data_query["id"] . ",\"" . encode_data_query_index($data_query_index) . "\");'" : "") . "><span id='text$query_row" . "_" . $column_counter . "'></span></td>";
							}

							$column_counter++;
						}
					}

					print "<td align='right'>";
					print "<input type='checkbox' name='sg_$query_row' id='sg_$query_row' " . (($use_javascript == true) ? "onClick='dq_update_selection_indicators();'" : "") . ">";
					print "</td>";
					print "</tr>\n";

					$row_counter++;
				}
			}
		}else{
			print "<tr bgcolor='#" . $colors["form_alternate1"] . "'><td colspan='2' style='color: red; font-size: 12px; font-weight: bold;'>" . _("Error in data query.") . "</td></tr>\n";
		}

		print "</table>";

		if (sizeof($data_query_graphs) == 1) {
			form_hidden_box("sgg_" . $data_query["id"] . "' id='sgg_" . $data_query["id"], $data_query_graphs[0]["id"], "");
		}elseif (sizeof($data_query_graphs) > 1) {
			print "	<table align='center' width='98%'>
					<tr>
						<td width='1' valign='top'>
							<img src='" . html_get_theme_images_path("arrow.gif") . "' alt='' align='absmiddle'>&nbsp;
						</td>
						<td align='right'>
							<span style='font-size: 12px; font-style: italic;'>" . _("Select a graph type:") . "</span>&nbsp;
							<select name='sgg_" . $data_query["id"] . "' id='sgg_" . $data_query["id"] . "' " . (($use_javascript == true) ? "onChange='dq_update_deps(" . $data_query["id"] . "," . $num_visible_fields . ");'" : "") . ">
								"; html_create_list($data_query_graphs,"template_name","id","0"); print "
							</select>
						</td>
					</tr>
				</table>";
		}

		print "<br>";

		if ($use_javascript == true) {
			print "<script type='text/javascript'>dq_update_deps(" . $data_query["id"] . "," . ($num_visible_fields) . ");</script>\n";
		}
	}
	}

	form_hidden_box("save_component_graph", "1", "");
	form_hidden_box("host_id", $host["id"], "0");

	form_save_button("graphs_new.php");

	print "<script type='text/javascript'>dq_update_selection_indicators();</script>\n";
	print "<script type='text/javascript'>gt_update_selection_indicators();</script>\n";
}
?>
