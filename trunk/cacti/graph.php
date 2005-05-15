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

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = "view"; }
if (!isset($_REQUEST["view_type"])) { $_REQUEST["view_type"] = ""; }

$guest_account = true;
include("./include/config.php");
include("./include/auth.php");
include("./include/top_graph_header.php");

if ($_GET["rra_id"] == "all") {
	$sql_where = " where id is not null";
}else{
	$sql_where = " where id=" . $_GET["rra_id"];
}

/* make sure the graph requested exists (sanity) */
if (!(db_fetch_cell("select id from graph where id = " . $_GET["graph_id"]))) {
	print "<strong><font size='+1' color='FF0000'>" . _("GRAPH DOES NOT EXIST") . "</font></strong>"; exit;
}

/* take graph permissions into account here, if the user does not have permission
give an "access denied" message */
if (read_config_option("auth_method") != "0") {
	$access_denied = !(is_graph_allowed($_GET["graph_id"]));

	if ($access_denied == true) {
		print "<strong><font size='+1' color='FF0000'>" . _("ACCESS DENIED") . "</font></strong>"; exit;
	}
}

$graph_title = get_graph_title($_GET["graph_id"]);

if ($_REQUEST["view_type"] == "tree") {
	print "<table width='98%' style='background-color: #" . $colors["graph_menu_background"] . "; border: 1px solid #" . $colors["graph_menu_border"] . ";' align='center' cellpadding='3'>";
}else{
	print "<br><table width='98%' style='background-color: #" . $colors["console_menu_background"] . "; border: 1px solid #" . $colors["console_menu_border"] . ";' align='center' cellpadding='3'>";
}

$rras = get_associated_rras($_GET["graph_id"]);

switch ($_REQUEST["action"]) {
case 'view':
	?>
	<tr bgcolor='#<?php print $colors["header_panel_background"];?>'>
		<td colspan='3' class='textHeaderDark'>
			<strong><?php echo _("Viewing Graph");?></strong> '<?php print $graph_title;?>'
		</td>
	</tr>
	<?php

	$i = 0;
	if (sizeof($rras) > 0) {
	foreach ($rras as $rra) {
		?>
		<tr>
			<td align='center'>
				<table width='1' cellpadding='0'>
					<tr>
						<td>
							<img src='graph_image.php?graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $rra["id"];?>' border='0' alt='<?php print $graph_title;?>'>
						</td>
						<td valign='top' style='padding: 3px;' class='noprint'>
							<a href='graph.php?action=zoom&graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $rra["id"];?>&view_type=<?php print $_REQUEST["view_type"];?>'><img src='images/graph_zoom.gif' border='0' alt='Zoom Graph' title='Zoom Graph' style='padding: 3px;'></a><br>
							<?php if (! $using_guest_account) { ?><a href='graph.php?action=properties&graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $rra["id"];?>&view_type=<?php print $_REQUEST["view_type"];?>'><img src='images/graph_properties.gif' border='0' alt='<?php echo _("Graph Source/Properties");?>' title='<?php echo _("Graph Source/Properties");?>' style='padding: 3px;'></a> <?php } ?>
						</td>
					</tr>
					<tr>
						<td colspan='2' align='center'>
							<strong><?php print $rra["name"];?></strong>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
		$i++;
	}
	}

	break;
case 'zoom':
	/* find the maximum time span a graph can show */
	$max_timespan=1;
	if (sizeof($rras) > 0) {
		foreach ($rras as $rra) {
			if ($rra["steps"] * $rra["rows"] * $rra["rrd_step"] > $max_timespan) {
				$max_timespan = $rra["steps"] * $rra["rows"] * $rra["rrd_step"];
			}
		}
	}

	/* fetch information for the current RRA */
	$rra = db_fetch_row("select timespan,steps,name from rra where id=" . $_GET["rra_id"]);

	/* define the time span, which decides which rra to use */
	$timespan = -($rra["timespan"]);

	/* find the step and how often this graph is updated with new data */
	$ds_step = db_fetch_cell("select
		data_source.rrd_step
		from data_source,data_source_item,graph_item
		where graph_item.data_source_item_id=data_source_item.id
		and data_source_item.data_source_id=data_source.id
		and graph_item.graph_id = " . $_GET["graph_id"] .
		"limit 0,1");
	$ds_step = empty($ds_step) ? 300 : $ds_step;
	$seconds_between_graph_updates = ($ds_step * $rra["steps"]);

	$now = time();

	if (isset($_GET["graph_end"]) && ($_GET["graph_end"] <= $now - $seconds_between_graph_updates)) {
		$graph_end = $_GET["graph_end"];
	}else{
		$graph_end = $now - $seconds_between_graph_updates;
	}

	if (isset($_GET["graph_start"])) {
		if (($graph_end - $_GET["graph_start"])>$max_timespan) {
			$graph_start = $now - $max_timespan;
		}else {
			$graph_start = $_GET["graph_start"];
		}
	}else{
		$graph_start = $now + $timespan;
	}

	/* required for zoom out function */
	if ($graph_start == $graph_end) {
		$graph_start--;
	}

	$graph = db_fetch_row("select
		graph.height,
		graph.width
		from graph
		where graph.id = " . $_GET["graph_id"]);

	$graph_height = $graph["height"];
	$graph_width = $graph["width"];

	?>
	<tr bgcolor='#<?php print $colors["header_panel"];?>'>
		<td colspan='3' class='textHeaderDark'>
			<strong><?php echo _("Zooming Graph");?></strong> '<?php print $graph_title;?>'
		</td>
	</tr>
	<div id='zoomBox' style='position:absolute; overflow:none; left:0px; top:0px; width:0px; height:0px; visibility:visible; background:red; filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity:.5'></div>
	<div id='zoomSensitiveZone' style='position:absolute; overflow:none; left:0px; top:0px; width:0px; height:0px; visibility:visible; cursor:crosshair; background:blue; filter:alpha(opacity=0); -moz-opacity:0; -khtml-opacity:0;' oncontextmenu='return false'></div>
	<tr>
		<td align='center'>
			<table width='1' cellpadding='0'>
				<tr>
					<td>
						<img id='zoomGraphImage' src='graph_image.php?graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $_GET["rra_id"];?>&view_type=<?php print $_REQUEST["view_type"];?>&graph_start=<?php print $graph_start;?>&graph_end=<?php print $graph_end;?>&graph_height=<?php print $graph_height;?>&graph_width=<?php print $graph_width;?>' border='0' alt='<?php print $graph_title;?>'>
					</td>
					<td valign='top' style='padding: 3px;' class='noprint'>
						<?php if (! $using_guest_account) { ?><a href='graph.php?action=properties&graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $_GET["rra_id"];?>&view_type=<?php print $_REQUEST["view_type"];?>&graph_start=<?php print $graph_start;?>&graph_end=<?php print $graph_end;?>'><img src='images/graph_properties.gif' border='0' alt='<?php echo _("Graph Source/Properties");?>' title='<?php echo _("Graph Source/Properties");?>' style='padding: 3px;'></a><?php } ?>
					</td>
				</tr>
				<tr>
					<td colspan='2' align='center'>
						<strong><?php print $rra["name"];?></strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php

	include("./include/zoom.js");

	break;
case 'properties':
	?>
	<tr bgcolor='#<?php print $colors["header_panel"];?>'>
		<td colspan='3' class='textHeaderDark'>
			<strong><?php echo _("Viewing Graph Properties");?></strong> '<?php print $graph_title;?>'
		</td>
	</tr>
	<tr>
		<td align='center'>
			<table width='1' cellpadding='0'>
				<tr>
					<td>
						<img src='graph_image.php?graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $_GET["rra_id"];?>&graph_start=<?php print (isset($_GET["graph_start"]) ? $_GET["graph_start"] : 0);?>&graph_end=<?php print (isset($_GET["graph_end"]) ? $_GET["graph_end"] : 0);?>' border='0' alt='<?php print $graph_title;?>'>
					</td>
					<td valign='top' style='padding: 3px;' class="noprint">
						<a href='graph.php?action=zoom&graph_id=<?php print $_GET["graph_id"];?>&rra_id=<?php print $_GET["rra_id"];?>&view_type=<?php print $_REQUEST["view_type"];?><?php print (isset($_GET["graph_start"]) ? print "&graph_start=" . $_GET["graph_start"] : "");?><?php print (isset($_GET["graph_end"]) ? print "&graph_end=" . $_GET["graph_end"] : "");?>'><img src='images/graph_zoom.gif' border='0' alt='<?php echo _("Zoom Graph");?>' title='<?php echo _("Zoom Graph");?>' style='padding: 3px;'></a><br>
					</td>
				</tr>
				<tr>
					<td colspan='2' align='center'>
						<strong><?php print db_fetch_cell("select name from rra where id=" . $_GET["rra_id"]);?></strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php

	break;
}

print "</table>";
print "<br><br>";

include_once("./include/bottom_footer.php");

?>
