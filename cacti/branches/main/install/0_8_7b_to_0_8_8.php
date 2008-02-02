<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

function upgrade_to_0_8_8() {
	/* add --alt-y-grid as an option */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` ADD COLUMN `t_alt_y_grid` CHAR(2) DEFAULT 0 AFTER `auto_scale_rigid`, ADD COLUMN `alt_y_grid` CHAR(2) DEFAULT '' AFTER `t_alt_y_grid`;");
	/* increase size for upper/lower limit for use with |query_*| variables */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `lower_limit` VARCHAR(255)");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `upper_limit` VARCHAR(255)"); 
}
?>
