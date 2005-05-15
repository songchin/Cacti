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

include(CACTI_BASE_PATH . "/include/graph/graph_arrays.php");
include(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");

/* file: (graphs.php|graph_templates.php), action: (graph|template)_edit */
$struct_graph = array(
	"general_header" => array(
		"friendly_name" => _("General Options"),
		"method" => "spacer"
		),
	"title" => array(
		"friendly_name" => _("Title"),
		"method" => "textbox",
		"value" => "|arg1:title|",
		"url_moveup" => "",
		"url_movedown" => "",
		"url_delete" => "",
		"url_add" => "",
		"max_length" => "255",
		"description" => _("The name that is printed on the graph.")
		),
	"vertical_label" => array(
		"friendly_name" => _("Vertical Label"),
		"method" => "textbox",
		"value" => "|arg1:vertical_label|",
		"form_id" => "|arg1:id|",
		"max_length" => "255",
		"default" => "",
		"description" => _("The label vertically printed to the left of the graph.")
		),
	"image_format" => array(
		"friendly_name" => _("Image Format"),
		"method" => "drop_array",
		"value" => "|arg1:image_format|",
		"form_id" => "|arg1:id|",
		"array" => $graph_image_types,
		"default" => "PNG",
		"description" => _("The type of graph that is generated; GIF or PNG.")
		),
	"export" => array(
		"friendly_name" => _("Allow Graph Export"),
		"method" => "checkbox",
		"value" => "|arg1:export|",
		"form_id" => "|arg1:id|",
		"default" => "on",
		"description" => _("Choose whether this graph will be included in the static HTML/PNG export if you use
			Cacti's export feature.")
		),
	"force_rules_legend" => array(
		"friendly_name" => _("Force HRULE/VRULE Legend"),
		"method" => "checkbox",
		"value" => "|arg1:force_rules_legend|",
		"form_id" => "|arg1:id|",
		"default" => "",
		"description" => _("Forces HRULE and VRULE items to be drawn on the legend even if they are not displayed
			on the graph.")
		),
	"size_header" => array(
		"friendly_name" => _("Image Size Options"),
		"method" => "spacer"
		),
	"height" => array(
		"friendly_name" => _("Height"),
		"method" => "textbox",
		"value" => "|arg1:height|",
		"form_id" => "|arg1:id|",
		"max_length" => "50",
		"default" => "120",
		"description" => _("The height (in pixels) of the graph area.")
		),
	"width" => array(
		"friendly_name" => _("Width"),
		"method" => "textbox",
		"value" => "|arg1:width|",
		"form_id" => "|arg1:id|",
		"max_length" => "50",
		"default" => "500",
		"description" => _("The width (in pixels) of the graph area.")
		),
	"grid_header" => array(
		"friendly_name" => _("Grid Options"),
		"method" => "spacer"
		),
	"x_grid" => array(
		"friendly_name" => _("X-Grid"),
		"method" => "textbox",
		"value" => "|arg1:x_grid|",
		"form_id" => "|arg1:id|",
		"max_length" => "100",
		"default" => "",
		"description" => _("Controls the layout of the x-grid. See the RRDTool manual for additional details.")
		),
	"y_grid" => array(
		"friendly_name" => _("Y-Grid"),
		"method" => "textbox",
		"value" => "|arg1:y_grid|",
		"form_id" => "|arg1:id|",
		"max_length" => "100",
		"default" => "",
		"description" => _("Controls the layout of the y-grid. See the RRDTool manual for additional details.")
		),
	"y_grid_alt" => array(
		"friendly_name" => _("Alternate Y-Grid"),
		"method" => "checkbox",
		"value" => "|arg1:y_grid_alt|",
		"form_id" => "|arg1:id|",
		"default" => "",
		"description" => _("Allows the dynamic placement of the y-grid based upon min and max values.")
		),
	"no_minor" => array(
		"friendly_name" => _("No Minor Grid Lines"),
		"method" => "checkbox",
		"value" => "|arg1:no_minor|",
		"form_id" => "|arg1:id|",
		"default" => "",
		"description" => _("Removes minor grid lines. Especially usefull on small graphs.")
		),
	"ascale_header" => array(
		"friendly_name" => _("Auto Scaling Options"),
		"method" => "spacer"
		),
	"auto_scale" => array(
		"friendly_name" => _("Utilize Auto Scale"),
		"method" => "checkbox",
		"value" => "|arg1:auto_scale|",
		"form_id" => "|arg1:id|",
		"default" => "on",
		"description" => _("Auto scale the y-axis instead of defining an upper and lower limit. If this is
			checked, both the Upper and Lower limit will be ignored.")

		),
	"auto_scale_opts" => array(
		"friendly_name" => _("Standard Auto Scale Options"),
		"method" => "radio",
		"value" => "|arg1:auto_scale_opts|",
		"form_id" => "|arg1:id|",
		"default" => "2",
		"description" => _("Use --alt-autoscale-max to scale to the maximum value, or --alt-autoscale to scale to the absolute
			minimum and maximum."),
		"items" => array(
			0 => array(
				"radio_value" => "1",
				"radio_caption" => "Use --alt-autoscale"
				),
			1 => array(
				"radio_value" => "2",
				"radio_caption" => "Use --alt-autoscale-max"
				)
			)
		),
	"auto_scale_log" => array(
		"friendly_name" => _("Logarithmic Auto Scaling (--logarithmic)"),
		"method" => "checkbox",
		"value" => "|arg1:auto_scale_log|",
		"form_id" => "|arg1:id|",
		"default" => "",
		"description" => _("Use Logarithmic y-axis scaling")
		),
	"auto_scale_rigid" => array(
		"friendly_name" => _("Rigid Boundaries Mode (--rigid)"),
		"method" => "checkbox",
		"value" => "|arg1:auto_scale_rigid|",
		"form_id" => "|arg1:id|",
		"default" => "",
		"description" => _("Do not expand the upper and lower limit if the graph contains a value outside the valid range.")
		),
	"auto_padding" => array(
		"friendly_name" => _("Auto Padding"),
		"method" => "checkbox",
		"value" => "|arg1:auto_padding|",
		"form_id" => "|arg1:id|",
		"default" => "on",
		"description" => _("Pad text so that legend and graph data always line up. Auto Padding may not
			be accurate on all types of graphs, consistant labeling usually helps.")
		),
	"rscale_header" => array(
		"friendly_name" => _("Fixed Scaling Options"),
		"method" => "spacer"
		),
	"upper_limit" => array(
		"friendly_name" => _("Upper Limit"),
		"method" => "textbox",
		"value" => "|arg1:upper_limit|",
		"form_id" => "|arg1:id|",
		"max_length" => "50",
		"default" => "100",
		"description" => _("The maximum vertical axis value for the graph.")
		),
	"lower_limit" => array(
		"friendly_name" => _("Lower Limit"),
		"method" => "textbox",
		"value" => "|arg1:lower_limit|",
		"form_id" => "|arg1:id|",
		"max_length" => "255",
		"default" => "0",
		"description" => _("The minimum vertical axis value for the graph.")
		),
	"base_value" => array(
		"friendly_name" => _("Base Value"),
		"method" => "drop_array",
		"value" => "|arg1:base_value|",
		"form_id" => "|arg1:id|",
		"array" => $graph_base_values,
		"default" => "1",
		"description" => _("Set to 1024 when graphing memory so that one kilobyte represents 1024 bytes.")
		),
	"unit_header" => array(
		"friendly_name" => _("Units Display Options"),
		"method" => "spacer"
		),
	"unit_value" => array(
		"friendly_name" => _("Units Value"),
		"method" => "textbox",
		"value" => "|arg1:unit_value|",
		"form_id" => "|arg1:id|",
		"max_length" => "20",
		"default" => "",
		"description" => _("Sets the exponent value on the Y-axis for numbers. Note: This option was
			recently added in RRDTool 1.0.36.")
		),
	"unit_length" => array(
		"friendly_name" => _("Units Length"),
		"method" => "textbox",
		"value" => "|arg1:unit_length|",
		"form_id" => "|arg1:id|",
		"default" => "9",
		"max_length" => "3",
		"description" => _("Sets the number of spaces for the units value to the left of the graph.")
		),
	"unit_exponent_value" => array(
		"friendly_name" => _("Units Exponent Value"),
		"method" => "drop_array",
		"value" => "|arg1:unit_exponent_value|",
		"form_id" => "|arg1:id|",
		"array" => $graph_unit_exponent_values,
		"default" => "none",
		"description" => _("How Cacti should scale the Y-axis label (None means autoscale).")
		)
	);

/* file: (graphs.php|graph_templates.php), action: item_edit */
$struct_graph_item = array(
	"data_template_item_id" => array(
		"friendly_name" => _("Data Template Item"),
		"method" => "drop_sql",
		"sql" => "select
			CONCAT_WS('',data_template.template_name,' - ',' (',data_template_item.data_source_name,')') as name,
			data_template_item.id
			from data_template,data_template_item
			where data_template.id=data_template_item.data_template_id
			order by data_template.template_name,data_template_item.data_source_name",
		"default" => "0",
		"none_value" => "None",
		"description" => _("The data template item to use for this graph item.")
		),
	"data_source_item_id" => array(
		"friendly_name" => _("Data Source Item"),
		"method" => "drop_sql",
		"sql" => "",
		"default" => "0",
		"none_value" => "None",
		"description" => _("The data source item to use for this graph item.")
		),
	"color" => array(
		"friendly_name" => _("Color"),
		"method" => "textbox",
		"max_length" => "6",
		"default" => "",
		"size" => "10",
		"description" => _("The color to use for the legend."),
		"preset" => array(
			"method" => "drop_color",
			"sql" => "select hex as id,hex as name from preset_color order by hex",
			"value" => "",
			"default" => ""
			)
		),
	"graph_item_type" => array(
		"friendly_name" => _("Graph Item Type"),
		"method" => "drop_array",
		"array" => $graph_item_types,
		"default" => "0",
		"description" => _("How data for this item is represented visually on the graph.")
		),
	"consolidation_function" => array(
		"friendly_name" => _("Consolidation Function"),
		"method" => "drop_array",
		"array" => $consolidation_functions,
		"default" => "0",
		"description" => _("How data for this item is represented statistically on the graph.")
		),
	"cdef" => array(
		"friendly_name" => _("CDEF Function"),
		"method" => "textbox",
		"default" => "",
		"max_length" => "255",
		"size" => "20",
		"description" => _("A CDEF (math) function to apply to this item on the graph."),
		"preset" => array(
			"method" => "drop_array",
			"sql" => "select cdef_string as id,name from preset_cdef order by name",
			"value" => "",
			"trim_length" => "20",
			"default" => ""
			)
		),
	"gprint_format" => array(
		"friendly_name" => _("GPRINT Format"),
		"method" => "textbox",
		"max_length" => "30",
		"size" => "20",
		"default" => "%8.2lf %s",
		"description" => _("If this graph item is a GPRINT, you can optionally choose another format
			here. You can define additional types under \"GPRINT Presets\"."),
		"preset" => array(
			"method" => "drop_array",
			"sql" => "select gprint_text as id,name from preset_gprint order by name",
			"value" => "",
			"trim_length" => "20",
			"default" => ""
			)
		),
	"legend_value" => array(
		"friendly_name" => _("Legend Value"),
		"method" => "textbox",
		"max_length" => "50",
		"default" => "",
		"description" => _("The value of an HRULE or VRULE graph item.")
		),
	"legend_format" => array(
		"friendly_name" => _("Legend Text Format"),
		"method" => "textbox",
		"max_length" => "255",
		"default" => "",
		"description" => _("Text that will be displayed on the legend for this graph item.")
		),
	"hard_return" => array(
		"friendly_name" => _("Insert Hard Return"),
		"method" => "checkbox",
		"default" => "",
		"description" => _("Forces the legend to the next line after this item.")
		),
	//"sequence" => array(
	//	"friendly_name" => _("Sequence"),
	//	"method" => "view"
	//	)
	);

/* file: graph_templates.php, action: template_edit */
$fields_graph_template_template_edit = array(
	"template_name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("The name given to this graph template."),
		"value" => "|arg1:template_name|",
		"max_length" => "150",
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

/* file: graph_templates.php, action: input_edit */
$fields_graph_template_input_edit = array(
	"name" => array(
		"method" => "textbox",
		"friendly_name" => _("Name"),
		"description" => _("Enter a name for this graph item input, make sure it is something you recognize."),
		"value" => "|arg1:name|",
		"max_length" => "50"
		),
	"field_name" => array(
		"method" => "drop_array",
		"friendly_name" => _("Field Type"),
		"description" => _("Choose the field type that you want to accept user input for on each graph."),
		"value" => "|arg1:field_name|",
		"array" => array(
			"color" => "Color",
			"graph_item_type" => "Graph Item Type",
			"consolidation_function" => "Consolidation Function",
			"cdef" => "CDEF",
			"gprint_format" => "GPRINT Format",
			"legend_value" => "Legend Value",
			"legend_format" => "Legend Text Format",
			"hard_return" => "Insert Hard Return"
			)
		),
	"save_component_input" => array(
		"method" => "hidden",
		"value" => "1"
		)
	);

?>
