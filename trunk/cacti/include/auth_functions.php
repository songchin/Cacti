<?/* 
+-------------------------------------------------------------------------+
| Copyright (C) 2002 Ian Berry                                            |
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
| cacti: the rrdtool frontend [php-auth, php-tree, php-form]              |
+-------------------------------------------------------------------------+
| This code is currently maintained and debugged by Ian Berry, any        |
| questions or comments regarding this code should be directed to:        |
| - iberry@raxnet.net                                                     |
+-------------------------------------------------------------------------+
| - raXnet - http://www.raxnet.net/                                       |
+-------------------------------------------------------------------------+
*/?>
<?
function DrawMenu($userid, $menuid) {
    global $config,$paths,$colors;
	
    include_once ("include/database.php");
    include_once ("include/config.php");
    
    /* get the current use logged in (if there is one) */
    if ($userid=="COOKIE") {
		$userid = $HTTP_COOKIE_VARS[$conf_cookiename];
    }
    
    /* set up the available menu headers */
    $menu = array("Graph Setup"    => array(
					    "graphs.php" => "Graph Management",
					    "graph_templates.php" => "Graph Templates",
					    "tree.php" => "Graph Hierarchy",
					    "color.php" => "Colors"
					    ),
		  "Data Gathering" => array(
					    "ds.php" => "Data Sources",
					    "show_equip_profiles.php" => 'Equipment Profiles',
					    "rra.php" => "Available RRA's",
#					    "snmp.php" => "SNMP Interfaces",
					    "data.php" => "Data Input Methods",
					    "pzones.php" => "Polling Zones",
					    "cdef.php" => "CDEF's"
					    ),
		  "Configuration"  => array(
					    "cron.php" => "Cron Printout",
					    "settings.php" => "Cacti Settings"
					    ),
		  "Utilities"      => array(
					    "user_admin.php" => "User Management",
					    "logout.php" => "Logout User"
					    )
		  );
    
    
    /* NOTICE: we will have to come back and re-impliment "custom auth menus" at some point */
    $user_perms = db_fetch_assoc("select
				   auth_sections.Section
				   from auth_sections left join auth_acl on auth_acl.SectionID=auth_sections.ID
				   where auth_acl.UserID=$userid");
    
    print "<tr><td width='100%'><table cellpadding=3 cellspacing=0 border=0 width='100%'>\n";
    
    foreach (array_keys($menu) as $header) {
	print "<tr><td class='textMenuHeader'>$header</td></tr>\n";
	if (sizeof($menu[$header]) > 0) {
	    foreach (array_keys($menu[$header]) as $url) {
		print "<tr><td class='textMenuItem' background='images/menu_line.gif'><a href='$url'>".$menu[$header][$url]."</a></td></tr>\n";
	    }
	}
    }
    print '</table></td></tr>';
}
?>
