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

function wizard_render($wizard) {

	$next_page = wizard_history() + 1;

	wizard_header($wizard,"90%");

	wizard_start_area();

	print "<br><br><br><b>" . _("Previous Page:") . "</b> " . wizard_history("prev") . "<br><b>" . _("Current Page:") . "</b> " . wizard_history() . "<br><b>" . _("Next Page:") . "</b> " . $next_page . "<br><br><br><br>";

	print "<input type='hidden' name='next_page' value='" . $next_page . "'>\n";

	wizard_end_area();

	wizard_footer(true,false,false,true,"90%");

}

?>
