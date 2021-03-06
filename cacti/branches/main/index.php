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

include("./include/auth.php");
include(CACTI_BASE_PATH . "/include/top_header.php");

api_plugin_hook('console_before');

?>
<table width="100%" align="center">
	<tr>
		<td class="textHeader">
			<strong><?php print __('You are now logged into <a href="about.php">Cacti</a>. You can follow these basic steps to get
			started.');?></strong>

			<ul>
				<li><strong><?php print '<a href="devices.php">' . __('Create devices') . " </a>" . __('for your network');?></strong></li>
				<li><strong><?php print '<a href="graphs_new.php">' . __('Create graphs') . " </a>" . __('for your new devices');?></strong></li>
				<li><strong><?php print '<a href="graph_view.php">' . __('View') . " </a>" . __('your new graphs');?></strong></li>
			</ul>
			<strong>
			<?php print __('Find help for each page when clicking the');?>
			<a href="<?php echo cacti_wiki_url();?>" target="_blank">
			<img src='images/help.gif' title="<?php print __("Help");?>" alt="<?php print __("Help");?>" align="top">
			</a>
			<?php print __('icon on the upper right.');?>
			</strong>
		</td>
		<td class="textHeader" align="right" valign="top">
			<strong><?php print __('Version') . " " . CACTI_VERSION;?></strong>
		</td>
	</tr>
</table>

<?php

api_plugin_hook('console_after');

include(CACTI_BASE_PATH . "/include/bottom_footer.php");
