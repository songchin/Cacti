<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/include/top_header.php");

?>
<table width="98%" align="center">
	<tr>
		<td class="textArea">
			<strong><?php echo _("You are now logged into ") . "<a href='about.php'> " . _("Cacti") . "</a>. " . _("You can follow these basic steps to get started.") . "</strong>";?>

			<ul>
				<li><a href="devices.php"><?php echo _("Create devices") . "</a> " . _("for network");?></li>
				<li><a href="graphs_new.php"><?php echo _("Create graphs") . "</a> " . _("for your new devices");?></li>
				<li><a href="graph_view.php"><?php echo _("View") . "</a> " . _("your new graphs");?></li>
			</ul>
		</td>
	</tr>
</table>
<?php

require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");

?>
