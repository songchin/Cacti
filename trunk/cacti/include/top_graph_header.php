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

include ('include/config.php');
include ('include/config_arrays.php');

include_once ("include/auth.php");

session_start();

/* at this point this user is good to go... so get some setting about this
user and put them into variables to save excess SQL in the future */
$current_user = db_fetch_row("select * from user_auth where id=" . $_SESSION["sess_user_id"]);

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

/* set the default action if none has been set */
if (!ereg('^(tree|list|preview)$', $_REQUEST["action"])) {
	if (read_graph_config_option("default_view_mode") == "1") {
		$_REQUEST["action"] = "tree";
	}elseif (read_graph_config_option("default_view_mode") == "2") {
		$_REQUEST["action"] = "list";
	}elseif (read_graph_config_option("default_view_mode") == "2") {
		$_REQUEST["action"] = "preview";
	}
}

?>
<html>
<head>
	<title>cacti</title>
	<?php print "<meta http-equiv=refresh content='" . read_graph_config_option("page_refresh") . "'; url='" . $_SERVER["SCRIPT_NAME"] . "'>\r\n";?>
	<link href="include/main.css" rel="stylesheet">
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<map name="tabs">
	<area alt="Console" coords="7,5,87,35" href="index.php">
	<area alt="Graphs" coords="88,5,165,32" href="graph_view.php?action=tree" shape="RECT">
</map>

<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td bgcolor="#454E53" nowrap>
			<table border=0 cellpadding=0 cellspacing=0 width='100%'><tr><td valign=bottom width=36></td><td width=250 valign=bottom><img src="images/top_tabs_main.gif" border="0" width=250 height=32 usemap="#tabs"></td></tr></table></td>
		<td bgcolor="#454E53" align="right" nowrap width='99%'>
			<?php if (isset($_SESSION["sess_user_id"])){?><a href="logout.php"><img src="images/top_tabs_logout.gif" border="0" alt="Logout"></a><?php }?><a href="graph_settings.php"><img src="images/top_tabs_graph_settings<?php if (basename($_SERVER["SCRIPT_FILENAME"]) == "graph_settings.php") { print "_down"; }?>.gif" border="0" alt="Settings"></a><a href="graph_view.php?action=tree"><img src="images/top_tabs_graph_tree<?php if ($_REQUEST == "tree") { print "_down"; }?>.gif" border="0" alt="Tree View"></a><a href="graph_view.php?action=list"><img src="images/top_tabs_graph_list<?php if ($_REQUEST == "list") { print "_down"; }?>.gif" border="0" alt="List View"></a><a href="graph_view.php?action=preview"><img src="images/top_tabs_graph_preview<?php if ($_REQUEST == "preview") { print "_down"; }?>.gif" border="0" alt="Preview View"></a><br>
		</td>
	</tr>
	<tr>
		<td colspan="3" bgcolor="#<?php print $colors["panel"];?>">
			<img src="images/transparent_line.gif" width="170" height="5" border="0"><br>
		</td>
	</tr>
	<tr>
	<?php
	if ($_REQUEST["action"] == "tree") {
		
	}else{
		print "<td height='5' colspan='3' bgcolor='#" . $colors["panel"] . "'></td>\n";
	}
	?>
	</tr>
	<?php if (!empty($_GET["show_source"])) {?>
	<tr>
		<td valign="top" height="1" colspan="3" bgcolor="#<?php print $colors["panel"];?>">
			<?php
			$graph_data_array["print_source"] = true;
			print trim(rrdtool_function_graph($_GET["local_graph_id"], $_GET["rra_id"], $graph_data_array));
			?>
		</td>
	</tr>
	<?php }?>
</table>

<table width="100%" cellspacing="0" cellpadding="0">
	<tr height="5"><td>&nbsp;</td></tr>
	<tr>
		<td valign="top">
