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


function graph_export() {
	if (file_exists(read_config_option("path_html_export"))) {
		if (read_config_option("path_html_export_skip") == read_config_option("path_html_export_ctr")) {
			db_execute("update settings set value='1' where name='path_html_export_ctr'");
			export();
		}else{
			if (read_config_option("path_html_export_ctr") == "") {
				db_execute("delete from settings where name='path_html_export_ctr' or name='path_html_export_skip'");
				db_execute("insert into settings (name,value) values ('path_html_export_ctr','1')");
				db_execute("insert into settings (name,value) values ('path_html_export_skip','1')");
			}else{
				db_execute("update settings set value='" . (read_config_option("path_html_export_ctr") + 1) . "' where name='path_html_export_ctr'");
			}
		}
	}
}

function export() {
	print "export: running graph export\n";
	
	$cacti_root_path = read_config_option("path_webroot") . read_config_option("path_webcacti");
	$cacti_export_path = read_config_option("path_html_export");
	
	/* copy the css/images on the first time */
	if (file_exists(read_config_option("path_html_export") . "$cacti_export_path/main.css") == false) {
		copy("$cacti_root_path/include/main.css", "$cacti_export_path/main.css");
		copy("$cacti_root_path/images/top_tabs_cacti.gif", "$cacti_export_path/top_tabs_cacti.gif");
		copy("$cacti_root_path/images/top_tabs_graphs.gif", "$cacti_export_path/top_tabs_graphs.gif");
		copy("$cacti_root_path/images/top_tabs_graph_preview.gif", "$cacti_export_path/top_tabs_graph_preview.gif");
		copy("$cacti_root_path/images/top_tabs_graph_preview_down.gif", "$cacti_export_path/top_tabs_graph_preview_down.gif");
		copy("$cacti_root_path/images/transparent_line.gif", "$cacti_export_path/transparent_line.gif");
	}
	
	/* if the index file already exists, delete it */
	check_remove($cacti_export_path . "/index.html");
	
	/* open pointer to the new index file */
	$fp_index = fopen($cacti_export_path . "/index.html", "w");
	
	/* get a list of all graphs that need exported */
	$graphs = db_fetch_assoc("select 
		graph_templates_graph.id,
		graph_templates_graph.local_graph_id,
		graph_templates_graph.height,
		graph_templates_graph.width,
		graph_templates_graph.title,
		graph_templates.name,
		graph_local.host_id
		from graph_templates_graph left join graph_templates on graph_templates_graph.graph_template_id=graph_templates.id
		left join graph_local on graph_templates_graph.local_graph_id=graph_local.id
		where graph_templates_graph.local_graph_id!=0
		order by graph_templates_graph.title");
	$rras = db_fetch_assoc("select
		rra.id,
		rra.name
		from rra");
	
	/* write the html header data to the index file */
	fwrite($fp_index, HTML_HEADER);
	fwrite($fp_index, HTML_GRAPH_HEADER_ONE);
	fwrite($fp_index, "<strong>Displaying " . sizeof($graphs) . " Exported Graph" . ((sizeof($graphs) > 1) ? "s" : "") . "</strong>");
	fwrite($fp_index, HTML_GRAPH_HEADER_TWO);
	
	/* for each graph... */
	$i = 0; $k = 0;
	if ((sizeof($graphs) > 0) && (sizeof($rras) > 0)) {
	foreach ($graphs as $graph) {
		check_remove($cacti_export_path . "/thumb_" . $graph["local_graph_id"] . ".png");
		check_remove($cacti_export_path . "/graph_" . $graph["local_graph_id"] . ".html");
		
		/* settings for preview graphs */
		$graph_data_array["use"] = true;
		$graph_data_array["graph_start"] = "-60000";
		$graph_data_array["graph_height"] = "100";
		$graph_data_array["graph_width"] = "300";
		$graph_data_array["graph_nolegend"] = true;
		$graph_data_array["export"] = true;
		$graph_data_array["export_filename"] = "thumb_" . $graph["local_graph_id"] . ".png";
		rrdtool_function_graph($graph["local_graph_id"], 0, $graph_data_array);
		
		/* generate html files for each graph */
		$fp_graph_index = fopen($cacti_export_path . "/graph_" . $graph["local_graph_id"] . ".html", "w");
		
		fwrite($fp_graph_index, HTML_HEADER);
		fwrite($fp_graph_index, HTML_GRAPH_HEADER_ONE);
		fwrite($fp_graph_index, "<strong>Graph - " . get_graph_title($graph["local_graph_id"]) . "</strong>");
		fwrite($fp_graph_index, HTML_GRAPH_HEADER_TWO);
		fwrite($fp_graph_index, "<td>");
		
		/* reset vars for actual graph image creation */
		reset($rras);
		unset($graph_data_array);
		
		/* generate graphs for each rra */
		foreach ($rras as $rra) {
			$graph_data_array["graph_nolegend"] = false;
			$graph_data_array["export"] = true;
			$graph_data_array["export_filename"] = "graph_" . $graph["local_graph_id"] . "_" . $rra["id"] . ".png";
			
			rrdtool_function_graph($graph["local_graph_id"], $rra["id"], $graph_data_array);
			
			/* write image related html */
			fwrite($fp_graph_index, "<div align=center><img src='graph_" . $graph["local_graph_id"] . "_" . $rra["id"] . ".png' border=0></div>\n
				<div align=center><strong>" . $rra["name"] . "</strong></div><br>");
		}
		
		fwrite($fp_graph_index, "</td>");
		fwrite($fp_graph_index, HTML_GRAPH_FOOTER);
		fwrite($fp_graph_index, HTML_FOOTER);
		fclose($fp_graph_index);
		
		/* main graph page html */
		fwrite($fp_index, "<td align='center' width='" . (98 / 2) . "%'><a href='graph_" . $graph["local_graph_id"] . ".html'><img src='thumb_" . $graph["local_graph_id"] . ".png' border='0' alt='" . get_graph_title($graph["local_graph_id"]) . "'></a></td>\n");
		
		$i++;
		$k++;
		
		if (($i == 2) && ($k < count($graphs))) {
			$i = 0;
			fwrite($fp_index, "</tr><tr>");
		}
		
	}
	}else{ fwrite($fp_index, "<td><em>No Graphs Found.</em></td>");
	}
	
	fwrite($fp_index, HTML_GRAPH_FOOTER);
	fwrite($fp_index, HTML_FOOTER);
	fclose($fp_index);
}

function check_remove($filename) {
	if (file_exists($filename) == true) {
		unlink($filename);
	}
}

define("HTML_GRAPH_HEADER_ONE", "
	<table width='98%' style='background-color: #f5f5f5; border: 1px solid #bbbbbb;' align='center'>
		<tr bgcolor='#" . $colors["header_panel"] . "'>
			<td colspan='2'>
				<table width='100%' cellspacing='0' cellpadding='3' border='0'>
					<tr>
						<td align='center' class='textHeaderDark'>");

define("HTML_GRAPH_HEADER_TWO", "
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>");

define("HTML_GRAPH_FOOTER", "
	</tr></table>\n
	<br><br>");

define("HTML_HEADER", "
	<html>
	<head>
		<title>cacti</title>
		<link href='main.css' rel='stylesheet'>
		<meta http-equiv=refresh content='300'; url='index.html'>
		<meta http-equiv=Pragma content=no-cache>
		<meta http-equiv=cache-control content=no-cache>
	</head>
	
	<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
	
	<table width='100%' cellspacing='0' cellpadding='0'>
		<tr>
			<td bgcolor='#454E53' colspan='2' nowrap>
				<a href='http://www.raxnet.net/products/cacti/'><img src='top_tabs_cacti.gif' border='0' width='79' height='32' align='absmiddle'></a>
				<a href='index.html'><img src='top_tabs_graphs.gif' border='0' width='79' height='32' align='absmiddle'></a>
			</td>\n
			<td bgcolor='#454E53' align='right' nowrap>
				<a href='index.html'><img src='top_tabs_graph_preview.gif' border='0' alt='Preview View'></a><br>
			</td>
		</tr>
		<tr>
			<td colspan='3' bgcolor='#" . $colors["panel"] . "'>
				<img src='transparent_line.gif' width='170' height='5' border='0'><br>
			</td>
		</tr>
		<tr>
			<td height='5' colspan='3' bgcolor='#" . $colors["panel"] . "'>
				
			</td>
		</tr>
	</table>
	
	<br>");

define("HTML_FOOTER", "
	<br>
	
	</body>
	</html>");

?>
