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

$graph_actions = array(
	GRAPH_ACTION_DELETE 					=> __("Delete"),
	GRAPH_ACTION_CHANGE_TEMPLATE 			=> __("Change Graph Template"),
	GRAPH_ACTION_DUPLICATE 					=> __("Duplicate"),
	GRAPH_ACTION_CONVERT_TO_TEMPLATE 		=> __("Convert to Graph Template"),
	GRAPH_ACTION_CHANGE_HOST 				=> __("Change Host"),
	GRAPH_ACTION_REAPPLY_SUGGESTED_NAMES 	=> __("Reapply Suggested Names"),
	GRAPH_ACTION_RESIZE 					=> __("Resize Graphs"),
	GRAPH_ACTION_ENABLE_EXPORT 				=> __("Enable Graph Export"),
	GRAPH_ACTION_DISABLE_EXPORT 			=> __("Disable Graph Export"),
	);

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

$graph_item_gprint_types = array(
	GRAPH_ITEM_TYPE_GPRINT_AVERAGE	=> "GPRINT:AVERAGE",
	GRAPH_ITEM_TYPE_GPRINT_LAST		=> "GPRINT:LAST",
	GRAPH_ITEM_TYPE_GPRINT_MAX		=> "GPRINT:MAX",
	GRAPH_ITEM_TYPE_GPRINT_MIN		=> "GPRINT:MIN",
	);

$graph_item_types1 = array(
	GRAPH_ITEM_TYPE_COMMENT			=> "COMMENT",
	GRAPH_ITEM_TYPE_HRULE			=> "HRULE",
	GRAPH_ITEM_TYPE_VRULE			=> "VRULE",
	GRAPH_ITEM_TYPE_LINE1			=> "LINE1",
	GRAPH_ITEM_TYPE_LINE2			=> "LINE2",
	GRAPH_ITEM_TYPE_LINE3			=> "LINE3",
	GRAPH_ITEM_TYPE_AREA			=> "AREA",
	GRAPH_ITEM_TYPE_AREASTACK		=> "AREA:STACK",
	);
$graph_item_types2 = array(
	GRAPH_ITEM_TYPE_LINESTACK		=> "LINE:STACK",
	GRAPH_ITEM_TYPE_TICK			=> "TICK",
	GRAPH_ITEM_TYPE_TEXTALIGN		=> "TEXTALIGN",
	GRAPH_ITEM_TYPE_LEGEND			=> __("Legend"),
	GRAPH_ITEM_TYPE_CUSTOM_LEGEND	=> __("Custom Legend"),
	);

$graph_item_types = $graph_item_types1 + $graph_item_gprint_types + $graph_item_types2;

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

$graph_views = array(
	GRAPH_TREE_VIEW 	=> __("Tree View"),
	GRAPH_LIST_VIEW 	=> __("List View"),
	GRAPH_PREVIEW_VIEW 	=> __("Preview View"),
	);

$graph_timespans = array(
	GT_LAST_HALF_HOUR 	=> __("Last Half Hour"),
	GT_LAST_HOUR 		=> __("Last Hour"),
	GT_LAST_2_HOURS 	=> __("Last %d Hours", 2),
	GT_LAST_4_HOURS 	=> __("Last %d Hours", 4),
	GT_LAST_6_HOURS 	=> __("Last %d Hours", 6),
	GT_LAST_12_HOURS 	=> __("Last %d Hours", 12),
	GT_LAST_DAY 		=> __("Last Day"),
	GT_LAST_2_DAYS 		=> __("Last %d Days", 2),
	GT_LAST_3_DAYS 		=> __("Last %d Days", 3),
	GT_LAST_4_DAYS 		=> __("Last %d Days", 4),
	GT_LAST_WEEK 		=> __("Last Week"),
	GT_LAST_2_WEEKS 	=> __("Last %d Weeks", 2),
	GT_LAST_MONTH 		=> __("Last Month"),
	GT_LAST_2_MONTHS 	=> __("Last %d Months", 2),
	GT_LAST_3_MONTHS 	=> __("Last %d Months", 3),
	GT_LAST_4_MONTHS 	=> __("Last %d Months", 4),
	GT_LAST_6_MONTHS 	=> __("Last %d Months", 6),
	GT_LAST_YEAR 		=> __("Last Year"),
	GT_LAST_2_YEARS 	=> __("Last %d Years", 2),
	GT_DAY_SHIFT 		=> __("Day Shift"),
	GT_THIS_DAY 		=> __("This Day"),
	GT_THIS_WEEK 		=> __("This Week"),
	GT_THIS_MONTH 		=> __("This Month"),
	GT_THIS_YEAR 		=> __("This Year"),
	GT_PREV_DAY 		=> __("Previous Day"),
	GT_PREV_WEEK 		=> __("Previous Week"),
	GT_PREV_MONTH 		=> __("Previous Month"),
	GT_PREV_YEAR 		=> __("Previous Year"),
	);

$graph_timeshifts = array(
	GTS_HALF_HOUR 	=> __("30 Min"),
	GTS_1_HOUR 		=> __("1 Hour"),
	GTS_2_HOURS 	=> __("%d Hours", 2),
	GTS_4_HOURS 	=> __("%d Hours", 4),
	GTS_6_HOURS 	=> __("%d Hours", 6),
	GTS_12_HOURS 	=> __("%d Hours", 12),
	GTS_1_DAY 		=> __("1 Day"),
	GTS_2_DAYS 		=> __("%d Days", 2),
	GTS_3_DAYS 		=> __("%d Days", 3),
	GTS_4_DAYS 		=> __("%d Days", 4),
	GTS_1_WEEK 		=> __("1 Week"),
	GTS_2_WEEKS 	=> __("%d Weeks", 2),
	GTS_1_MONTH 	=> __("1 Month"),
	GTS_2_MONTHS 	=> __("%d Months", 2),
	GTS_3_MONTHS 	=> __("%d Months", 3),
	GTS_4_MONTHS 	=> __("%d Months", 4),
	GTS_6_MONTHS 	=> __("%d Months", 6),
	GTS_1_YEAR 		=> __("1 Year"),
	GTS_2_YEARS 	=> __("%d Years", 2),
	);

$graph_weekdays = array(
	WD_SUNDAY	 	=> date("l", strtotime("Sunday")),
	WD_MONDAY 		=> date("l", strtotime("Monday")),
	WD_TUESDAY	 	=> date("l", strtotime("Tuesday")),
	WD_WEDNESDAY 	=> date("l", strtotime("Wednesday")),
	WD_THURSDAY 	=> date("l", strtotime("Thursday")),
	WD_FRIDAY	 	=> date("l", strtotime("Friday")),
	WD_SATURDAY		=> date("l", strtotime("Saturday"))
	);

$graph_dateformats = array(
	GD_MO_D_Y =>"Month Number, Day, Year",
	GD_MN_D_Y =>"Month Name, Day, Year",
	GD_D_MO_Y =>"Day, Month Number, Year",
	GD_D_MN_Y =>"Day, Month Name, Year",
	GD_Y_MO_D =>"Year, Month Number, Day",
	GD_Y_MN_D =>"Year, Month Name, Day"
	);

$graph_datechar = array(
	GDC_HYPHEN => "-",
	GDC_SLASH => "/",
	GDC_DOT => "."
	);
