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
	"general" => array(
		"friendly_name" => "General",
		"description" => "General wizard aids you in setting up Cacti's general settings for your enviroment.",
		"include" => "include/wizards/settings_general.php"
		),
	"snmp" => array(
		"friendly_name" => "SNMP",
		"description" => "SNMP wizard aids in setting up the default SNMP settings of Cacti.",
		"include" => "include/wizards/settings_snmp.php"
		),
	"paths" => array(
		"friendly_name" => "Paths",
		"description" => "Paths wizard aids in setting up the paths to required executables and logs for cacti.",
		"include" => "include/wizards/settings_paths.php"
		),
	"poller" => array(
		"friendly_name" => "Poller",
		"description" => "Poller wizard aids in setting up the Poller Type, Poller Execution, Default Host Availability and Default Host Up/Down Settings.",
		"include" => "include/wizards/settings_poller.php"
		),
	"visual" => array(
		"friendly_name" => "Visual",
		"description" => "Visual wizard aids in setting up the visual settings of Cacti, including the Default Theme, Rows per Page and other settings.",
		"include" => "include/wizards/settings_visual.php"
		),
	"auth" => array(
		"friendly_name" => "Authenication",
		"description" => "Authenication wizard aids in setting up Cacti's Authenication system for your enviroment.  Be prepared to answer questions and have configuration settings that are appropriate to you enviroment.  If you plan on using LDAP, please have ready your LDAP server parameters, and if needed an authorzied binding for searching the LDAP server.",
		"include" => "include/wizards/settings_auth.php"
		),
	"graphexport" => array(
		"friendly_name" => "Graph Export",
		"description" => "Graph Export wizard aids in setting up exporting of graphs to static pages, either locally or FTP to a remote server.",
		"include" => "include/wizards/settings_graphexport.php"
		),
	"default" => "general",
	"intro" => "<b>Welcome to the Settings Wizard</b><br><br>This wizard is designed to help you setup features in Cacti.  Please select the section you would like to setup.",
	"title" => "Setup Wizard"
	);



if (isset($_REQUEST["wizard"])) {

	include_once("include/top_header.php");

	include_wizard($_REQUEST["wizard"]);

	include_once("include/bottom_footer.php");

}else{
	include_once("include/top_header.php");

	intro();
	
	include_once("include/bottom_footer.php");
}

/* 
#########################################
# Functions
#########################################
*/

function include_wizard($wizard) {
	global $wizard_array;
	
	$file = $wizard_array[$wizard]["include"];
	
	if (file_exists($file)) {
		include_once($file);
	}else{
		display_custom_error_message("Unable to include Wizard File for wizard \"" . $wizard . "\"");
	}

}

function intro() {
	global $wizard_array,$colors;

	/* create html and javascript lists for use in the html output */
	$javascript = "";
	$html = "";
	while (list($field_name, $field_array) = each($wizard_array)) {
		if (($field_name != "default") && ($field_name != "intro") && ($field_name != "title")) {
			if ($wizard_array["default"] == $field_name) {
				$html .= "\t\t\t\t\t\t\t<input type='radio' name='wizard' value='" . $field_name . "' checked onClick='applyDescription(this.value)'><i>" . $field_array["friendly_name"] . "</i><br>\n";
			}else{
				$html .= "\t\t\t\t\t\t\t<input type='radio' name='wizard' value='" . $field_name . "' onClick='applyDescription(this.value)'><i>" . $field_array["friendly_name"] . "</i><br>\n";
			}
			$javascript .= "  arrayValues['" . $field_name . "'] = '" . str_replace("'","\\'",$field_array["description"]) . "';\n";
		}
	}

	/* javascript function to change decription box */
	print "\n";
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "function applyDescription(strIndex) {\n";
	print "  var arrayValues = new Array();\n";
	print $javascript;
	print "  obj = document.getElementById('wizardarea');\n";
	print "  obj.innerHTML = arrayValues[strIndex];\n";
	print "}\n";
	print "-->\n";
	print "</script>\n";

	/* html output */
	print "\t<form method='POST'>\n";

	html_start_box("<strong>" . $wizard_array["title"] . "</strong>", "70%", $colors["header_background"], "3", "center", "");

	print "\t\t\t\t\t<tr>\n";
	print "\t\t\t\t\t\t<td colspan='5' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><br><blockquote>";
	print $wizard_array["intro"];
	print "</blockquote><br><hr noshade></td>\n";
	print "\t\t\t\t\t</tr>\n"; 
	print "\t\t\t\t\t<tr>\n";
	print "\t\t\t\t\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t\t\t\t\t<td width='25%' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b> Section</b><br><br>\n";
	print $html;
	print "\t\t\t\t\t\t</td>\n";
	print "\t\t\t\t\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t\t\t\t\t<td valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b>Description</b><br><br>\n";
	print "\t\t\t\t\t\t\t\t<div id='wizardarea' style='height:150px; overflow:auto;'>None</div><br>\n";
	print "\t\t\t\t\t\t</td>\n";
	print "\t\t\t\t\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t\t\t\t</tr>\n";

	html_end_box();

	print "\n\t<table align='center' width='70%' style='background-color: " . $colors['buttonbar_background'] . "; border: 1px solid #" . $colors["buttonbar_border"] . ";'>\n";
	print "\t\t<tr>\n";
	print "\t\t\t<td bgcolor='" . $colors['buttonbar_background'] . "' align='right'>\n";
	print "\t\t\t\t<input type='image' src='" . html_get_theme_images_path("button_next.gif") . "' alt='Save' align='absmiddle'>\n";
	print "\t\t\t</td>\n";
	print "\t\t</tr>\n";
	print "\t</table>\n";
	print "\t</form>\n";

	/* javascript function to change description box to default */
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "applyDescription('" . $wizard_array["default"] . "');\n";
	print "-->\n";
	print "</script>\n";

}






?>
