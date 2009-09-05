<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

if (!defined('VALID_HOST_FIELDS')) {
	$string = do_hook_function('valid_host_fields', '(hostname|snmp_community|snmp_username|snmp_password|snmp_auth_protocol|snmp_priv_passphrase|snmp_priv_protocol|snmp_context|snmp_version|snmp_port|snmp_timeout)');
	define('VALID_HOST_FIELDS', $string);
}

/* file: cdef.php, action: edit */
$fields_cdef_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this CDEF."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "60"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_cdef" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: color.php, action: edit */
$fields_color_edit = array(
	"hex" => array(
		"method" => "textbox",
		"friendly_name" => __("Hex Value"),
		"description" => __("The hex value for this color; valid range: 000000-FFFFFF."),
		"value" => "|arg1:hex|",
		"max_length" => "6",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_color" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: edit */
$fields_data_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("Enter a meaningful name for this data input method."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		),
	"type_id" => array(
		"method" => "drop_array",
		"friendly_name" => __("Input Type"),
		"description" => __("Choose what type of data input method this is."),
		"value" => "|arg1:type_id|",
		"array" => $input_types,
		),
	"input_string" => array(
		"method" => "textbox",
		"friendly_name" => __("Input String"),
		"description" => __("The data that is sent to the script, which includes the complete path to the script and input sources in &lt;&gt; brackets."),
		"value" => "|arg1:input_string|",
		"max_length" => "255",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_data_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_input.php, action: field_edit (dropdown) */
$fields_data_input_field_edit_1 = array(
	"data_name" => array(
		"method" => "drop_array",
		"friendly_name" => __("Field [|arg1:|]"),
		"description" => __("Choose the associated field from the |arg1:| field."),
		"value" => "|arg3:data_name|",
		"array" => "|arg2:|",
		)
	);

/* file: data_input.php, action: field_edit (textbox) */
$fields_data_input_field_edit_2 = array(
	"data_name" => array(
		"method" => "textbox",
		"friendly_name" => __("Field [|arg1:|]"),
		"description" => __("Enter a name for this |arg1:| field."),
		"value" => "|arg2:data_name|",
		"max_length" => "50",
		)
	);

/* file: data_input.php, action: field_edit */
$fields_data_input_field_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Friendly Name"),
		"description" => __("Enter a meaningful name for this data input method."),
		"value" => "|arg1:name|",
		"max_length" => "200",
		),
	"update_rra" => array(
		"method" => "checkbox",
		"friendly_name" => __("Update RRD File"),
		"description" => __("Whether data from this output field is to be entered into the rrd file."),
		"value" => "|arg1:update_rra|",
		"default" => "on",
		"form_id" => "|arg1:id|"
		),
	"regexp_match" => array(
		"method" => "textbox",
		"friendly_name" => __("Regular Expression Match"),
		"description" => __("If you want to require a certain regular expression to be matched againt input data, enter it here (ereg format)."),
		"value" => "|arg1:regexp_match|",
		"max_length" => "200"
		),
	"allow_nulls" => array(
		"method" => "checkbox",
		"friendly_name" => __("Allow Empty Input"),
		"description" => __("Check here if you want to allow NULL input in this field from the user."),
		"value" => "|arg1:allow_nulls|",
		"default" => "",
		"form_id" => false
		),
	"type_code" => array(
		"method" => "textbox",
		"friendly_name" => __("Special Type Code"),
		"description" => __("If this field should be treated specially by host templates, indicate so here. Valid keywords for this field are 'hostname', 'snmp_community', 'snmp_username', 'snmp_password', 'snmp_auth_protocol', 'snmp_priv_passphrase', 'snmp_priv_protocol', 'snmp_context', 'snmp_port', 'snmp_timeout', and 'snmp_version'."),
		"value" => "|arg1:type_code|",
		"max_length" => "40"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"input_output" => array(
		"method" => "hidden",
		"value" => "|arg2:|"
		),
	"sequence" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:sequence|"
		),
	"data_input_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:data_input_id|"
		),
	"save_component_field" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_templates.php, action: template_edit */
$fields_data_template_template_edit = array(
	"template_name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("The name given to this data template."),
		"value" => "|arg1:name|",
		"max_length" => "150",
		),
	"data_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:data_template_id|"
		),
	"data_template_data_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:id|"
		),
	"current_rrd" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:view_rrd|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source = array(
	"name" => array(
		"friendly_name" => __("Name"),
		"method" => "textbox",
		"max_length" => "250",
		"default" => "",
		"description" => __("Choose a name for this data source."),
		"flags" => ""
		),
	"data_source_path" => array(
		"friendly_name" => __("Data Source Path"),
		"method" => "textbox",
		"max_length" => "255",
		"size" => "70",
		"default" => "",
		"description" => __("The full path to the RRD file."),
		"flags" => "NOTEMPLATE"
		),
	"data_input_id" => array(
		"friendly_name" => __("Data Input Method"),
		"method" => "drop_sql",
		"sql" => "select id,name from data_input order by name",
		"default" => "",
		"none_value" => "None",
		"description" => __("The script/source used to gather data for this data source."),
		"flags" => "ALWAYSTEMPLATE"
		),
	"rra_id" => array(
		"method" => "drop_multi_rra",
		"friendly_name" => __("Associated RRA's"),
		"description" => __("Which RRA's to use when entering data. (It is recommended that you select all of these values)."),
		"form_id" => "|arg1:id|",
		"sql" => "select rra_id as id,data_template_data_id from data_template_data_rra where data_template_data_id=|arg1:id|",
		"sql_all" => "select rra.id from rra order by id",
		"sql_print" => "select rra.name from (data_template_data_rra,rra) where data_template_data_rra.rra_id=rra.id and data_template_data_rra.data_template_data_id=|arg1:id|",
		"flags" => "ALWAYSTEMPLATE"
		),
	"rrd_step" => array(
		"friendly_name" => __("Step"),
		"method" => "textbox",
		"max_length" => "10",
		"size" => "20",
		"default" => "300",
		"description" => __("The amount of time in seconds between expected updates."),
		"flags" => ""
		),
	"active" => array(
		"friendly_name" => __("Data Template Active"),
		"method" => "checkbox",
		"default" => "on",
		"description" => __("Whether Cacti should gather data for this class of Data Sources."),
		"flags" => ""
		)
	);

/* file: (data_sources.php|data_templates.php), action: (ds|template)_edit */
$struct_data_source_item = array(
	"data_source_name" => array(
		"friendly_name" => __("Internal Data Source Name"),
		"method" => "textbox",
		"max_length" => "19",
		"size" => "20",
		"default" => "",
		"description" => __("Choose unique name to represent this piece of data inside of the rrd file."),
		),
	"rrd_minimum" => array(
		"friendly_name" => __("Minimum Value"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"class" => "DS_std",
		"description" => __("The minimum value of data that is allowed to be collected."),
		),
	"rrd_maximum" => array(
		"friendly_name" => __("Maximum Value"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "0",
		"class" => "DS_std",
		"description" => __("The maximum value of data that is allowed to be collected."),
		),
	"data_source_type_id" => array(
		"friendly_name" => __("Data Source Type"),
		"method" => "drop_array",
		"array" => $data_source_types,
		"default" => "",
		"description" => __("How data is represented in the RRA."),
		),
	"rrd_compute_rpn" => array(
		"friendly_name" => __("RPN for a COMPUTE DS Item Type (RRDTool 1.2.x and above)"),
		"method" => "textbox",
		"max_length" => "150",
		"size" => "30",
		"default" => "",
		"class" => "DS_compute",
		"description" => __("When using a COMPUTE data source type, please enter the RPN for it here.") . "<br>" .
						__("Available for RRDTool 1.2.x and above"),
		),
	"rrd_heartbeat" => array(
		"friendly_name" => __("Heartbeat"),
		"method" => "textbox",
		"max_length" => "20",
		"size" => "30",
		"default" => "600",
		"description" => __("The maximum amount of time that can pass before data is entered as 'unknown'.") . "<br>" .
						__("(Usually 2x300=600)"),
		),
	"data_input_field_id" => array(
		"friendly_name" => __("Output Field"),
		"method" => "drop_sql",
		"default" => "0",
		"description" => __("When data is gathered, the data for this field will be put into this data source."),
		),
	);

/* file: grprint_presets.php, action: edit */
$fields_grprint_presets_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("Enter a name for this GPRINT preset, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50",
		),
	"gprint_text" => array(
		"method" => "textbox",
		"friendly_name" => __("GPRINT Text"),
		"description" => __("Enter the custom GPRINT string here."),
		"value" => "|arg1:gprint_text|",
		"max_length" => "50",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_gprint_presets" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: (graphs.php|graph_templates.php), action: (graph|template)_edit */
$struct_graph = array(
	"title" => array(
		"friendly_name" => __("Title (--title)"),
		"method" => "textbox",
		"max_length" => "255",
		"size" => "70",
		"default" => "",
		"description" => __("The name that is printed on the graph."),
		),
	"image_format_id" => array(
		"friendly_name" => __("Image Format (--imgformat)"),
		"method" => "drop_array",
		"array" => $image_types,
		"default" => "1",
		"description" => __("The type of graph that is generated; PNG, GIF or SVG.  The selection of graph image type is very RRDtool dependent."),
		),
	"height" => array(
		"friendly_name" => __("Height (--height)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "120",
		"size" => "10",
		"description" => __("The height (in pixels) that the graph is."),
		),
	"width" => array(
		"friendly_name" => __("Width (--width)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "500",
		"size" => "10",
		"description" => __("The width (in pixels) that the graph is."),
		),
	"slope_mode" => array(
		"friendly_name" => __("Slope Mode (--slope-mode)"),
		"method" => "checkbox",
		"default" => "on",
		"description" => __("Using Slope Mode, in RRDtool 1.2.x and above, evens out the shape of the graphs at the expense of some on screen resolution."),
		),
	"auto_scale" => array(
		"friendly_name" => __("Auto Scale"),
		"method" => "checkbox",
		"default" => "on",
		"description" => __("Auto scale the y-axis instead of defining an upper and lower limit.") . "<br>" .
						"<strong>" . __("Note:") . " </strong>" . __("if this is checked, both the Upper and Lower limit will be ignored."),
		),
	"auto_scale_opts" => array(
		"friendly_name" => __("Auto Scale Options"),
		"method" => "radio",
		"default" => "2",
		"description" => __("Use") . "<br>" .
			__("--alt-autoscale to scale to the absolute minimum and maximum") . "<br>" .
		    __("--alt-autoscale-max to scale to the maximum value, using a given lower limit") . "<br>" .
		    __("--alt-autoscale-min to scale to the minimum value, using a given upper limit") . "<br>" .
			__("--alt-autoscale (with limits) to scale using both lower and upper limits (rrdtool default)"),
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => __("Use") . " " . __("--alt-autoscale (ignoring given limits)"),
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => __("Use") . " " . __("--alt-autoscale-max (accepting a lower limit)"),
				),
			2 => array(
				"radio_value" => "3",
				"radio_caption" => __("Use") . " " . __("--alt-autoscale-min (accepting an upper limit, requires rrdtool 1.2.x)"),
				),
			3 => array(
				"radio_value" => "4",
				"radio_caption" => __("Use") . " " . __("--alt-autoscale (accepting both limits, rrdtool default)"),
				)
			)
		),
	"auto_scale_log" => array(
		"friendly_name" => __("Logarithmic Scaling (--logarithmic)"),
		"method" => "checkbox",
		"default" => "",
		"on_change" => "changeScaleLog()",
		"description" => __("Use Logarithmic y-axis scaling"),
		),
	"scale_log_units" => array(
		"friendly_name" => __("SI Units for Logarithmic Scaling (--units=si)"),
		"method" => "checkbox",
		"default" => "",
		"description" => __("Use SI Units for Logarithmic Scaling instead of using exponential notation (not available for rrdtool-1.0.x).") . "<br>" .
						"<strong>" . __("Note:") . " </strong>" . __("Linear graphs use SI notation by default."),
		),
	"auto_scale_rigid" => array(
		"friendly_name" => __("Rigid Boundaries Mode (--rigid)"),
		"method" => "checkbox",
		"default" => "",
		"description" => __("Do not expand the lower and upper limit if the graph contains a value outside the valid range."),
		),
	"alt_y_grid" => array(
		"friendly_name" => __("Alternative Y Grid (--alt-y-grid)"),
		"method" => "checkbox",
		"default" => "",
		"description" => __("The algorithm ensures that you always have a grid, that there are enough but not too many grid lines, and that the grid is metric. This parameter will also ensure that you get enough decimals displayed.") . "<br>" .
						"<strong>" . __("Note:") . " </strong>" . __("This parameter may interfere with --alt-autoscale options."),
		),
	"auto_padding" => array(
		"friendly_name" => __("Auto Padding"),
		"method" => "checkbox",
		"default" => "on",
		"description" => __("Pad text so that legend and graph data always line up. Note: this could cause graphs to take longer to render because of the larger overhead. Also Auto Padding may not be accurate on all types of graphs, consistant labeling usually helps."),
		),
	"export" => array(
		"friendly_name" => __("Allow Graph Export"),
		"method" => "checkbox",
		"default" => "on",
		"description" => __("Choose whether this graph will be included in the static html/png export if you use cacti's export feature."),
		),
	"upper_limit" => array(
		"friendly_name" => __("Upper Limit (--upper-limit)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "100",
		"size" => "10",
		"description" => __("The maximum vertical value for the rrd graph."),
		),
	"lower_limit" => array(
		"friendly_name" => __("Lower Limit (--lower-limit)"),
		"method" => "textbox",
		"max_length" => "255",
		"default" => "0",
		"size" => "10",
		"description" => __("The minimum vertical value for the rrd graph."),
		),
	"base_value" => array(
		"friendly_name" => __("Base Value (--base)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "1000",
		"size" => "10",
		"description" => __("Should be set to 1024 for memory and 1000 for traffic measurements."),
		),
	"unit_value" => array(
		"friendly_name" => __("Unit Grid Value (--unit/--y-grid)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		"size" => "30",
		"description" => __("Sets the xponent value on the Y-axis for numbers. Note: This option was added in rrdtool 1.0.36 and depricated in 1.2.x. In RRDtool 1.2.x, this value is replaced by the --y-grid option. In this option, Y-axis grid lines appear at each grid step interval. Labels are placed every label factor lines."),
		),
	"unit_exponent_value" => array(
		"friendly_name" => __("Unit Exponent Value (--units-exponent)"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		"size" => "30",
		"description" => __("What unit cacti should use on the Y-axis. Use 3 to display everything in 'k' or -6 to display everything in 'u' (micro)."),
		),
	"vertical_label" => array(
		"friendly_name" => __("Vertical Label (--vertical-label)"),
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		"size" => "30",
		"description" => __("The label vertically printed to the left of the graph."),
		)
	);

/* file: (graphs.php|graph_templates.php), action: item_edit */
$struct_graph_item = array(
	"task_item_id" => array(
		"friendly_name" => __("Data Source"),
		"method" => "drop_sql",
		"sql" => "select
			CONCAT_WS('', CASE WHEN host.description IS NULL THEN 'No Host' WHEN host.description IS NOT NULL THEN host.description end,' - ',data_template_data.name,' (',data_template_rrd.data_source_name,')') AS name,
			data_template_rrd.id
			FROM (data_template_data,data_template_rrd,data_local)
			LEFT JOIN host ON (data_local.host_id=host.id)
			WHERE data_template_rrd.local_data_id=data_local.id
			AND data_template_data.local_data_id=data_local.id
			ORDER BY name",
		"default" => "0",
		"none_value" => "None",
		"description" => __("The data source to use for this graph item."),
		),
	"color_id" => array(
		"friendly_name" => __("Color"),
		"method" => "drop_color",
		"default" => "0",
		"on_change" => "changeColorId()",
		"description" => __("The color to use for the legend."),
		),
	"alpha" => array(
		"friendly_name" => __("Opacity/Alpha Channel"),
		"method" => "drop_array",
		"default" => "FF",
		"array" => $graph_color_alpha,
		"description" => __("The opacity/alpha channel of the color. Not available for rrdtool-1.0.x."),
		),
	"graph_type_id" => array(
		"friendly_name" => __("Graph Item Type"),
		"method" => "drop_array",
		"array" => $graph_item_types,
		"default" => "0",
		"description" => __("How data for this item is represented visually on the graph."),
		),
	"consolidation_function_id" => array(
		"friendly_name" => __("Consolidation Function"),
		"method" => "drop_array",
		"array" => $consolidation_functions,
		"default" => "0",
		"description" => __("How data for this item is represented statistically on the graph."),
		),
	"cdef_id" => array(
		"friendly_name" => __("CDEF Function"),
		"method" => "drop_sql",
		"sql" => "select id,name from cdef order by name",
		"default" => "0",
		"none_value" => "None",
		"description" => __("A CDEF (math) function to apply to this item on the graph."),
		),
	"value" => array(
		"friendly_name" => __("Value"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		"size" => "10",
		"description" => __("The value of an HRULE or VRULE graph item."),
		),
	"gprint_id" => array(
		"friendly_name" => __("GPRINT Type"),
		"method" => "drop_sql",
		"sql" => "select id,name from graph_templates_gprint order by name",
		"default" => "2",
		"description" => __("If this graph item is a GPRINT, you can optionally choose another format here. You can define additional types under 'GPRINT Presets'."),
		),
	"text_format" => array(
		"friendly_name" => __("Text Format"),
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		"description" => __("Text that will be displayed on the legend for this graph item."),
		),
	"hard_return" => array(
		"friendly_name" => __("Insert Hard Return"),
		"method" => "checkbox",
		"default" => "",
		"description" => __("Forces the legend to the next line after this item."),
		),
	"sequence" => array(
		"friendly_name" => __("Sequence"),
		"method" => "view"
		)
	);

/* file: graph_templates.php, action: template_edit */
$fields_graph_template_template_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("The name given to this graph template."),
		"value" => "|arg1:name|",
		"max_length" => "150",
		"size" => "70",
		),
	"graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:graph_template_id|"
		),
	"graph_template_graph_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: graph_templates.php, action: input_edit */
$fields_graph_template_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("Enter a name for this graph item input, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50"
		),
	"description" => array(
		"method" => "textarea",
		"friendly_name" => __("Description"),
		"description" => __("Enter a description for this graph item input to describe what this input is used for."),
		"value" => "|arg1:description|",
		"textarea_rows" => "5",
		"textarea_cols" => "40"
		),
	"column_name" => array(
		"method" => "drop_array",
		"friendly_name" => __("Field Type"),
		"description" => __("How data is to be represented on the graph."),
		"value" => "|arg1:column_name|",
		"array" => "|arg2:|",
		),
	"graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:graph_template_id|"
		),
	"graph_template_input_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg3:id|"
		),
	"save_component_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

	/* file: sites.php, action: edit */
$fields_site_edit = array(
	"spacer0" => array(
		"method" => "spacer",
		"friendly_name" => __("Site Information"),
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"value" => "|arg1:name|",
		"size" => "50",
		"max_length" => "100"
		),
	"alternate_id" => array(
		"method" => "textbox",
		"friendly_name" => __("Alternate Name"),
		"value" => "|arg1:alternate_id|",
		"size" => "50",
		"max_length" => "30"
		),
	"address1" => array(
		"method" => "textbox",
		"friendly_name" => __("Address1"),
		"value" => "|arg1:address1|",
		"size" => "70",
		"max_length" => "100"
		),
	"address2" => array(
		"method" => "textbox",
		"friendly_name" => __("Address2"),
		"value" => "|arg1:address2|",
		"size" => "70",
		"max_length" => "100"
		),
	"city" => array(
		"method" => "textbox",
		"friendly_name" => __("City"),
		"value" => "|arg1:city|",
		"size" => "20",
		"max_length" => "30"
		),
	"state" => array(
		"method" => "textbox",
		"friendly_name" => __("State"),
		"value" => "|arg1:state|",
		"size" => "10",
		"max_length" => "20"
		),
	"postal_code" => array(
		"method" => "textbox",
		"friendly_name" => __("Postal/Zip Code"),
		"value" => "|arg1:postal_code|",
		"size" => "10",
		"max_length" => "20"
		),
	"country" => array(
		"method" => "textbox",
		"friendly_name" => __("Country"),
		"value" => "|arg1:country|",
		"size" => "20",
		"max_length" => "30"
		),
	"notes" => array(
		"method" => "textarea",
		"friendly_name" => __("Site Notes"),
		"textarea_rows" => "3",
		"textarea_cols" => "70",
		"value" => "|arg1:notes|",
		"max_length" => "255"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_site" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: host.php, action: edit */
$fields_host_edit = array(
	"host_header" => array(
		"method" => "spacer",
		"friendly_name" => __("General Device Options"),
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => __("Description"),
		"description" => __("Give this device a meaningful description."),
		"value" => "|arg1:description|",
		"max_length" => "250",
		"size" => "70"
		),
	"hostname" => array(
		"method" => "textbox",
		"friendly_name" => __("Hostname"),
		"description" => __("Fully qualified hostname or IP address for this device."),
		"value" => "|arg1:hostname|",
		"max_length" => "250",
		"size" => "70"
		),
	"poller_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Poller"),
		"description" => __("Choose which poller will be the polling of this device."),
		"value" => "|arg1:poller_id|",
		"none_value" => __("System Default"),
		"sql" => "select id,description as name from poller order by name",
		),
	"site_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Site"),
		"description" => __("Choose the site that is to be associated with this device."),
		"value" => "|arg1:site_id|",
		"none_value" => "N/A",
		"sql" => "select id,name from sites order by name",
		),
	"host_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Device Template"),
		"description" => __("Choose what type of device, device template this is. The device template will govern what kinds of data should be gathered from this type of device."),
		"value" => "|arg1:host_template_id|",
		"none_value" => "None",
		"sql" => "select id,name from host_template order by name",
		),
	"notes" => array(
		"method" => "textarea",
		"friendly_name" => __("Notes"),
		"description" => __("Enter notes to this device."),
		"class" => "textAreaNotes",
		"value" => "|arg1:notes|",
		"textarea_rows" => "5",
		"textarea_cols" => "50"
		),
	"disabled" => array(
		"method" => "checkbox",
		"friendly_name" => __("Disable Device"),
		"description" => __("Check this box to disable all checks for this device."),
		"value" => "|arg1:disabled|",
		"default" => "",
		"form_id" => false
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_host_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:host_template_id|"
		),
	"save_basic_host" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: host.php, action: edit */
$fields_host_edit_availability = array(
	"availability_header" => array(
		"method" => "spacer",
		"friendly_name" => __("Availability/Reachability Options"),
		),
	"availability_method" => array(
		"friendly_name" => __("Downed Device Detection"),
		"description" => __("The method Cacti will use to determine if a device is available for polling.") . "<br>" .
						"<i>" . __("NOTE:") . " " . __("It is recommended that, at a minimum, SNMP always be selected.") . "</i>",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:availability_method|",
		"method" => "drop_array",
		"default" => read_config_option("availability_method"),
		"array" => $availability_options
		),
	"ping_method" => array(
		"friendly_name" => __("Ping Method"),
		"description" => __("The type of ping packet to sent.") . "<br>" .
						"<i>" . __("NOTE:") . __("ICMP on Linux/UNIX requires root privileges.") . "</i>",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:ping_method|",
		"method" => "drop_array",
		"default" => read_config_option("ping_method"),
		"array" => $ping_methods
		),
	"ping_port" => array(
		"method" => "textbox",
		"friendly_name" => __("Ping Port"),
		"value" => "|arg1:ping_port|",
		"description" => __("TCP or UDP port to attempt connection."),
		"default" => read_config_option("ping_port"),
		"max_length" => "50",
		"size" => "15"
		),
	"ping_timeout" => array(
		"friendly_name" => __("Ping Timeout Value"),
		"description" => __("The timeout value to use for device ICMP and UDP pinging. This device SNMP timeout value applies for SNMP pings."),
		"method" => "textbox",
		"value" => "|arg1:ping_timeout|",
		"default" => read_config_option("ping_timeout"),
		"max_length" => "10",
		"size" => "15"
		),
	"ping_retries" => array(
		"friendly_name" => __("Ping Retry Count"),
		"description" => __("After an initial failure, the number of ping retries Cacti will attempt before failing."),
		"method" => "textbox",
		"value" => "|arg1:ping_retries|",
		"default" => read_config_option("ping_retries"),
		"max_length" => "10",
		"size" => "15"
		),
	"snmp_spacer" => array(
		"method" => "spacer",
		"friendly_name" => __("SNMP Options"),
		),
	"snmp_version" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Version"),
		"description" => __("Choose the SNMP version for this device."),
		"on_change" => "changeHostForm()",
		"value" => "|arg1:snmp_version|",
		"default" => read_config_option("snmp_ver"),
		"array" => $snmp_versions,
		),
	"snmp_community" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Community"),
		"description" => __("SNMP read community for this device."),
		"value" => "|arg1:snmp_community|",
		"form_id" => "|arg1:id|",
		"default" => read_config_option("snmp_community"),
		"max_length" => "100",
		"size" => "15"
		),
	"snmp_username" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Username (v3)"),
		"description" => __("SNMP v3 username for this device."),
		"value" => "|arg1:snmp_username|",
		"default" => read_config_option("snmp_username"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_password" => array(
		"method" => "textbox_password",
		"friendly_name" => __("SNMP Password (v3)"),
		"description" => __("SNMP v3 password for this device."),
		"value" => "|arg1:snmp_password|",
		"default" => read_config_option("snmp_password"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_auth_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Auth Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Authorization Protocol."),
		"value" => "|arg1:snmp_auth_protocol|",
		"default" => read_config_option("snmp_auth_protocol"),
		"array" => $snmp_auth_protocols,
		),
	"snmp_priv_passphrase" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Privacy Passphrase (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Passphrase."),
		"value" => "|arg1:snmp_priv_passphrase|",
		"default" => read_config_option("snmp_priv_passphrase"),
		"max_length" => "200",
		"size" => "40"
		),
	"snmp_priv_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Privacy Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Protocol."),
		"value" => "|arg1:snmp_priv_protocol|",
		"default" => read_config_option("snmp_priv_protocol"),
		"array" => $snmp_priv_protocols,
		),
	"snmp_context" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Context"),
		"description" => __("Enter the SNMP Context to use for this device."),
		"value" => "|arg1:snmp_context|",
		"default" => "",
		"max_length" => "64",
		"size" => "25"
		),
	"snmp_port" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Port"),
		"description" => __("Enter the UDP port number to use for SNMP (default is 161)."),
		"value" => "|arg1:snmp_port|",
		"max_length" => "5",
		"default" => read_config_option("snmp_port"),
		"size" => "15"
		),
	"snmp_timeout" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Timeout"),
		"description" => __("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support)."),
		"value" => "|arg1:snmp_timeout|",
		"max_length" => "8",
		"default" => read_config_option("snmp_timeout"),
		"size" => "15"
		),
	"max_oids" => array(
		"method" => "textbox",
		"friendly_name" => __("Maximum OID's Per Get Request"),
		"description" => __("Specified the number of OID's that can be obtained in a single SNMP Get request."),
		"value" => "|arg1:max_oids|",
		"max_length" => "8",
		"default" => read_config_option("max_get_size"),
		"size" => "15"
		),
	"save_component_host" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: host_templates.php, action: edit */
$fields_host_template_edit = array(
	"host_header" => array(
		"method" => "spacer",
		"friendly_name" => __("General Device Template Options"),
		),
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this device template."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "70"
		),
	"avalaibility_header" => array(
		"method" => "spacer",
		"friendly_name" => __("Availability/Reachability Options"),
		),
	"availability_method" => array(
		"friendly_name" => __("Downed Device Detection"),
		"description" => __("The method Cacti will use to determine if a device is available for polling.  <br><i>NOTE: It is recommended that, at a minimum, SNMP always be selected.</i>"),
		"on_change" => "changeHostForm()",
		"value" => "|arg1:availability_method|",
		"method" => "drop_array",
		"default" => read_config_option("availability_method"),
		"array" => $availability_options
		),
	"ping_method" => array(
		"friendly_name" => __("Ping Method"),
		"description" => __("The type of ping packet to sent.") . "<br>" .
						"<i>" . __("NOTE:") . " " . __("ICMP on Linux/UNIX requires root privileges.") . "</i>",
		"on_change" => "changeHostForm()",
		"value" => "|arg1:ping_method|",
		"method" => "drop_array",
		"default" => read_config_option("ping_method"),
		"array" => $ping_methods
		),
	"ping_port" => array(
		"method" => "textbox",
		"friendly_name" => __("Ping Port"),
		"value" => "|arg1:ping_port|",
		"description" => __("TCP or UDP port to attempt connection."),
		"default" => read_config_option("ping_port"),
		"max_length" => "50",
		"size" => "15"
		),
	"ping_timeout" => array(
		"friendly_name" => __("Ping Timeout Value"),
		"description" => __("The timeout value to use for device ICMP and UDP pinging. This device SNMP timeout value applies for SNMP pings."),
		"method" => "textbox",
		"value" => "|arg1:ping_timeout|",
		"default" => read_config_option("ping_timeout"),
		"max_length" => "10",
		"size" => "15"
		),
	"ping_retries" => array(
		"friendly_name" => __("Ping Retry Count"),
		"description" => __("The number of times Cacti will attempt to ping a device before failing."),
		"method" => "textbox",
		"value" => "|arg1:ping_retries|",
		"default" => read_config_option("ping_retries"),
		"max_length" => "10",
		"size" => "15"
		),
	"snmp_spacer" => array(
		"method" => "spacer",
		"friendly_name" => __("SNMP Options"),
		),
	"snmp_version" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Version"),
		"description" => __("Choose the SNMP version for this device."),
		"on_change" => "changeHostForm()",
		"value" => "|arg1:snmp_version|",
		"default" => read_config_option("snmp_ver"),
		"array" => $snmp_versions,
		),
	"snmp_community" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Community"),
		"description" => __("SNMP read community for this device."),
		"value" => "|arg1:snmp_community|",
		"form_id" => "|arg1:id|",
		"default" => read_config_option("snmp_community"),
		"max_length" => "100",
		"size" => "15"
		),
	"snmp_username" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Username (v3)"),
		"description" => __("SNMP v3 username for this device."),
		"value" => "|arg1:snmp_username|",
		"default" => read_config_option("snmp_username"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_password" => array(
		"method" => "textbox_password",
		"friendly_name" => __("SNMP Password (v3)"),
		"description" => __("SNMP v3 password for this device."),
		"value" => "|arg1:snmp_password|",
		"default" => read_config_option("snmp_password"),
		"max_length" => "50",
		"size" => "15"
		),
	"snmp_auth_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Auth Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Authorization Protocol."),
		"value" => "|arg1:snmp_auth_protocol|",
		"default" => read_config_option("snmp_auth_protocol"),
		"array" => $snmp_auth_protocols,
		),
	"snmp_priv_passphrase" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Privacy Passphrase (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Passphrase."),
		"value" => "|arg1:snmp_priv_passphrase|",
		"default" => read_config_option("snmp_priv_passphrase"),
		"max_length" => "200",
		"size" => "40"
		),
	"snmp_priv_protocol" => array(
		"method" => "drop_array",
		"friendly_name" => __("SNMP Privacy Protocol (v3)"),
		"description" => __("Choose the SNMPv3 Privacy Protocol."),
		"value" => "|arg1:snmp_priv_protocol|",
		"default" => read_config_option("snmp_priv_protocol"),
		"array" => $snmp_priv_protocols,
		),
	"snmp_context" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Context"),
		"description" => __("Enter the SNMP Context to use for this device."),
		"value" => "|arg1:snmp_context|",
		"default" => "",
		"max_length" => "64",
		"size" => "25"
		),
	"snmp_port" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Port"),
		"description" => __("Enter the UDP port number to use for SNMP (default is 161)."),
		"value" => "|arg1:snmp_port|",
		"max_length" => "5",
		"default" => read_config_option("snmp_port"),
		"size" => "15"
		),
	"snmp_timeout" => array(
		"method" => "textbox",
		"friendly_name" => __("SNMP Timeout"),
		"description" => __("The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support)."),
		"value" => "|arg1:snmp_timeout|",
		"max_length" => "8",
		"default" => read_config_option("snmp_timeout"),
		"size" => "15"
		),
	"max_oids" => array(
		"method" => "textbox",
		"friendly_name" => __("Maximum OID's Per Get Request"),
		"description" => __("Specified the number of OID's that can be obtained in a single SNMP Get request."),
		"value" => "|arg1:max_oids|",
		"max_length" => "8",
		"default" => read_config_option("max_get_size"),
		"size" => "15"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_template" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: rra.php, action: edit */
$fields_rra_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("How data is to be entered in RRA's."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
		),
	"consolidation_function_id" => array(
		"method" => "drop_multi",
		"friendly_name" => __("Consolidation Functions"),
		"description" => __("How data is to be entered in RRA's."),
		"array" => $consolidation_functions,
		"sql" => "select consolidation_function_id as id,rra_id from rra_cf where rra_id=|arg1:id|",
		),
	"x_files_factor" => array(
		"method" => "textbox",
		"friendly_name" => __("X-Files Factor"),
		"description" => __("The amount of unknown data that can still be regarded as known."),
		"value" => "|arg1:x_files_factor|",
		"max_length" => "10",
		"size" => "10"
		),
	"steps" => array(
		"method" => "textbox",
		"friendly_name" => __("Steps"),
		"description" => __("How many data points are needed to put data into the RRA."),
		"value" => "|arg1:steps|",
		"max_length" => "8",
		"size" => "10"
		),
	"rows" => array(
		"method" => "textbox",
		"friendly_name" => __("Rows"),
		"description" => __("How many generations data is kept in the RRA."),
		"value" => "|arg1:rows|",
		"max_length" => "12",
		"size" => "10"
		),
	"timespan" => array(
		"method" => "textbox",
		"friendly_name" => __("Timespan"),
		"description" => __("How many seconds to display in graph for this RRA."),
		"value" => "|arg1:timespan|",
		"max_length" => "12",
		"size" => "10"
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_rra" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_queries.php, action: edit */
$fields_data_query_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A name for this data query."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
		),
	"description" => array(
		"method" => "textbox",
		"friendly_name" => __("Description"),
		"description" => __("A description for this data query."),
		"value" => "|arg1:description|",
		"max_length" => "255",
		"size" => "70"
		),
	"xml_path" => array(
		"method" => "textbox",
		"friendly_name" => __("XML Path"),
		"description" => __("The full path to the XML file containing definitions for this data query."),
		"value" => "|arg1:xml_path|",
		"default" => "<path_cacti>/resource/",
		"max_length" => "255",
		"size" => "70"
		),
	"data_input_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Data Input Method"),
		"description" => __("Choose what type of device, device template this is. The device template will govern what kinds of data should be gathered from this type of device."),
		"value" => "|arg1:data_input_id|",
		"sql" => "select id,name from data_input where (type_id=3 or type_id=4 or type_id=5 or type_id=6) order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|",
		),
	"save_component_snmp_query" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: data_queries.php, action: item_edit */
$fields_data_query_item_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A name for this associated graph."),
		"value" => "|arg1:name|",
		"max_length" => "100",
		"size" => "50"
		),
	"graph_template_id" => array(
		"method" => "drop_sql",
		"friendly_name" => __("Graph Template"),
		"description" => __("Choose what type of device, device template this is. The device template will govern what kinds of data should be gathered from this type of device."),
		"value" => "|arg1:graph_template_id|",
		"sql" => "select id,name from graph_templates order by name",
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"snmp_query_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg2:snmp_query_id|"
		),
	"_graph_template_id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:graph_template_id|"
		),
	"save_component_snmp_query_item" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: tree.php, action: edit */
$fields_tree_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => __("Name"),
		"description" => __("A useful name for this graph tree."),
		"value" => "|arg1:name|",
		"max_length" => "255",
		"size" => "70"
		),
	"sort_type" => array(
		"method" => "drop_array",
		"friendly_name" => __("Sorting Type"),
		"description" => __("Choose how items in this tree will be sorted."),
		"value" => "|arg1:sort_type|",
		"array" => $tree_sort_types,
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"save_component_tree" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

/* file: user_admin.php, action: user_edit (host) */
$fields_user_user_edit_host = array(
	"username" => array(
		"method" => "textbox",
		"friendly_name" => __("User Name"),
		"description" => __("The login name for this user."),
		"value" => "|arg1:username|",
		"max_length" => "255",
		"size" => "70"
		),
	"full_name" => array(
		"method" => "textbox",
		"friendly_name" => __("Full Name"),
		"description" => __("A more descriptive name for this user, that can include spaces or special characters."),
		"value" => "|arg1:full_name|",
		"max_length" => "255",
		"size" => "70"
		),
	"password" => array(
		"method" => "textbox_password",
		"friendly_name" => __("Password"),
		"description" => __("Enter the password for this user twice. Remember that passwords are case sensitive!"),
		"value" => "",
		"max_length" => "255",
		"size" => "70"
		),
	"enabled" => array(
		"method" => "checkbox",
		"friendly_name" => __("Enabled"),
		"description" => __("Determines if user is able to login."),
		"value" => "|arg1:enabled|",
		"default" => ""
		),
	"grp1" => array(
		"friendly_name" => __("Account Options"),
		"method" => "checkbox_group",
		"description" => __("Set any user account-specific options here."),
		"items" => array(
			"must_change_password" => array(
				"value" => "|arg1:must_change_password|",
				"friendly_name" => __("User Must Change Password at Next Login"),
				"form_id" => "|arg1:id|",
				"default" => ""
				),
			"graph_settings" => array(
				"value" => "|arg1:graph_settings|",
				"friendly_name" => __("Allow this User to Keep Custom Graph Settings"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"grp2" => array(
		"friendly_name" => __("Graph Options"),
		"method" => "checkbox_group",
		"description" => __("Set any graph-specific options here."),
		"items" => array(
			"show_tree" => array(
				"value" => "|arg1:show_tree|",
				"friendly_name" => __("User Has Rights to Tree View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_list" => array(
				"value" => "|arg1:show_list|",
				"friendly_name" => __("User Has Rights to List View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				),
			"show_preview" => array(
				"value" => "|arg1:show_preview|",
				"friendly_name" => __("User Has Rights to Preview View"),
				"form_id" => "|arg1:id|",
				"default" => "on"
				)
			)
		),
	"login_opts" => array(
		"friendly_name" => __("Login Options"),
		"method" => "radio",
		"default" => "1",
		"description" => __("What to do when this user logs in."),
		"value" => "|arg1:login_opts|",
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => __("Show the page that user pointed their browser to."),
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => __("Show the default console screen."),
				),
			2 => array(
				"radio_value" => "3",
				"radio_caption" => __("Show the default graph screen."),
				)
			)
		),
	"realm" => array(
		"method" => "drop_array",
		"friendly_name" => __("Authentication Realm"),
		"description" => __("Only used if you have LDAP or Web Basic Authentication enabled. Changing this to an non-enabled realm will effectively disable the user."),
		"value" => "|arg1:realm|",
		"default" => 0,
		"array" => $auth_realms,
		),
	"id" => array(
		"method" => "hidden_zero",
		"value" => "|arg1:id|"
		),
	"_policy_graphs" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graphs|"
		),
	"_policy_trees" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_trees|"
		),
	"_policy_hosts" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_hosts|"
		),
	"_policy_graph_templates" => array(
		"method" => "hidden",
		"default" => "2",
		"value" => "|arg1:policy_graph_templates|"
		),
	"save_component_user" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

$export_types = array(
	"graph_template" => array(
		"name" => __("Graph Template"),
		"title_sql" => "select name from graph_templates where id=|id|",
		"dropdown_sql" => "select id,name from graph_templates order by name"
		),
	"data_template" => array(
		"name" => __("Data Template"),
		"title_sql" => "select name from data_template where id=|id|",
		"dropdown_sql" => "select id,name from data_template order by name"
		),
	"host_template" => array(
		"name" => __("Device Template"),
		"title_sql" => "select name from host_template where id=|id|",
		"dropdown_sql" => "select id,name from host_template order by name"
		),
	"data_query" => array(
		"name" => __("Data Query"),
		"title_sql" => "select name from snmp_query where id=|id|",
		"dropdown_sql" => "select id,name from snmp_query order by name"
		)
	);
