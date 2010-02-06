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

define("RRD_FONT_RENDER_NORMAL", "normal");
define("RRD_FONT_RENDER_LIGHT","light");
define("RRD_FONT_RENDER_MONO", "mono");

define("RRD_GRAPH_RENDER_NORMAL", "normal");
define("RRD_GRAPH_RENDER_MONO", "mono");

define("COLORTAGS_GLOBAL",		1);
define("COLORTAGS_USER",		2);
define("COLORTAGS_TEMPLATE",	3);
define("COLORTAGS_UTG",			4);
define("COLORTAGS_TUG",			5);

define("RRD_LEGEND_POS_NORTH",	"north");
define("RRD_LEGEND_POS_SOUTH",	"south");
define("RRD_LEGEND_POS_WEST",	"west");
define("RRD_LEGEND_POS_EAST",	"east");

define("RRD_ALIGN_LEFT", 		"left");
define("RRD_ALIGN_RIGHT",		"right");
define("RRD_ALIGN_JUSTIFIED",	"justified");
define("RRD_ALIGN_CENTER",		"center");

define("RRD_LEGEND_DIR_TOPDOWN",	"topdown");
define("RRD_LEGEND_DIR_BOTTOMUP",	"bottomup");

define("GRAPH_ALT_AUTOSCALE",			1);
define("GRAPH_ALT_AUTOSCALE_MIN",		2);
define("GRAPH_ALT_AUTOSCALE_MAX",		3);
define("GRAPH_ALT_AUTOSCALE_LIMITS",	4);

define("GRAPH_ITEM_TYPE_COMMENT",		1);
define("GRAPH_ITEM_TYPE_HRULE",			2);
define("GRAPH_ITEM_TYPE_VRULE",			3);
define("GRAPH_ITEM_TYPE_LINE1",			4);
define("GRAPH_ITEM_TYPE_LINE2",			5);
define("GRAPH_ITEM_TYPE_LINE3",			6);
define("GRAPH_ITEM_TYPE_AREA",			7);
define("GRAPH_ITEM_TYPE_AREASTACK",		8);
define("GRAPH_ITEM_TYPE_GPRINT",		9);
define("GRAPH_ITEM_TYPE_LINESTACK",		10);
define("GRAPH_ITEM_TYPE_TICK",			11);
define("GRAPH_ITEM_TYPE_TEXTALIGN",		12);
define("GRAPH_ITEM_TYPE_LEGEND", 		98);
define("GRAPH_ITEM_TYPE_CUSTOM_LEGEND",	99);

define("IMAGE_TYPE_PNG", 1);
define("IMAGE_TYPE_GIF", 2);
define("IMAGE_TYPE_SVG", 3);
