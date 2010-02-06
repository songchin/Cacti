<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

require_once(CACTI_BASE_PATH . "/include/graph/graph_constants.php");

$rrd_font_render_modes = array(
	RRD_FONT_RENDER_NORMAL	=> __("Normal"),
	RRD_FONT_RENDER_LIGHT	=> __("Light"),
	RRD_FONT_RENDER_MONO	=> __("Mono"),
	);

$rrd_graph_render_modes = array(
	RRD_GRAPH_RENDER_NORMAL	=> __("Normal"),
	RRD_GRAPH_RENDER_MONO	=> __("Mono"),
	);

$rrd_legend_position = array(
	RRD_LEGEND_POS_NORTH	=> __("North"),
	RRD_LEGEND_POS_SOUTH	=> __("South"),
	RRD_LEGEND_POS_WEST		=> __("West"),
	RRD_LEGEND_POS_EAST		=> __("East"),
);

$rrd_textalign = array(
	RRD_ALIGN_LEFT			=> __("Left"),
	RRD_ALIGN_RIGHT			=> __("Right"),
	RRD_ALIGN_JUSTIFIED		=> __("Justified"),
	RRD_ALIGN_CENTER		=> __("Center"),
);

$rrd_legend_direction = array(
	RRD_LEGEND_DIR_TOPDOWN	=> __("Top -> Down"),
	RRD_LEGEND_DIR_BOTTOMUP	=> __("Bottom -> Up"),
);

$graph_item_types = array(
	GRAPH_ITEM_TYPE_COMMENT			=> "COMMENT",
	GRAPH_ITEM_TYPE_HRULE			=> "HRULE",
	GRAPH_ITEM_TYPE_VRULE			=> "VRULE",
	GRAPH_ITEM_TYPE_LINE1			=> "LINE1",
	GRAPH_ITEM_TYPE_LINE2			=> "LINE2",
	GRAPH_ITEM_TYPE_LINE3			=> "LINE3",
	GRAPH_ITEM_TYPE_AREA			=> "AREA",
	GRAPH_ITEM_TYPE_AREASTACK		=> "AREA:STACK",
	GRAPH_ITEM_TYPE_GPRINT			=> "GPRINT",
	GRAPH_ITEM_TYPE_LINESTACK		=> "LINE:STACK",
	GRAPH_ITEM_TYPE_TICK			=> "TICK",
	GRAPH_ITEM_TYPE_TEXTALIGN		=> "TEXTALIGN",
	GRAPH_ITEM_TYPE_LEGEND			=> __("Legend"),
	GRAPH_ITEM_TYPE_CUSTOM_LEGEND	=> __("Custom Legend"),
	);

$image_types = array(
	IMAGE_TYPE_PNG 	=> "PNG",
	IMAGE_TYPE_GIF	=> "GIF",
	IMAGE_TYPE_SVG	=> "SVG",
	);

$graph_color_alpha = array(
		"00" => "  0%",
		"19" => " 10%",
		"33" => " 20%",
		"4C" => " 30%",
		"66" => " 40%",
		"7F" => " 50%",
		"99" => " 60%",
		"B2" => " 70%",
		"CC" => " 80%",
		"E5" => " 90%",
		"FF" => "100%"
		);

$colortag_sequence = array(
	COLORTAGS_GLOBAL 	=> __("Accept global colortags only, if any"),
	COLORTAGS_USER	 	=> __("Accept user colortags only, if any"),
	COLORTAGS_TEMPLATE 	=> __("Accept graph template colortags only, if any"),
	COLORTAGS_UTG	 	=> __("Accept user colortags, template next, global last"),
	COLORTAGS_TUG	 	=> __("Accept template colortags, user next, global last"),
	);
