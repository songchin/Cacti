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

include("./include/auth.php");

/* settings */
$wizard_array = array(
	"auth" => array(
		"friendly_name" => "Authenication",
		"description" => "Authenication wizard aids you in setting up Cacti's Authenication system for your enviroment.",
		"include" => "include/wizards/settings_auth.php"
		),
	"default" => "auth"
	);



/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':

		break;
	default:
		default_menu();
		break;
}


function default_menu() {

	$value_array = array("auth" => "Authenication");


	$form_array = array(
		"wizard" => array(
			"method" => "drop_array",
			"friendly_name" => "Settings Section",
			"description" => "Please select the settings wizard you would like to run.",
			"value" => "",
			"array" => $value_array
			),
		"description" => array(
			"method" => "",
			"friendly_name" => "",
			"description" => "",
			"default" => "boo",
			"value" => ""
			)
		);


	include_once("include/top_header.php");

	html_start_box("<strong>Settings Wizard</strong>", "98%", $colors["header_background"], "3", "center", "");

	draw_edit_form( array( "fields" => $form_array ) );



	html_end_box();

	include_once("include/bottom_footer.php");


}










?>
