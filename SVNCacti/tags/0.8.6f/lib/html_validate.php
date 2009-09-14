<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
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

function input_validate_input_equals($value, $c_value) {
	if ($value != $c_value) {
		die_html_input_error();
	}
}

function input_validate_input_number($value) {
	if ((!is_numeric($value)) && ($value != "")) {
		die_html_input_error();
	}
}

function input_validate_input_regex($value, $regex) {
	if ((!ereg($regex, $value)) && ($value != "")) {
		die_html_input_error();
	}
}

function die_html_input_error() {
	global $config;

	?>
	<table width="98%" align="center">
		<tr>
			<td>
				Validation error.
			</td>
		</tr>
	</table>
	<?php

	include_once("./include/bottom_footer.php");
	exit;
}

?>
