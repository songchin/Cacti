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

include("./include/config.php");
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


/* Includes */

if (isset($_REQUEST["wizard"])) {

	include_once("include/top_header.php");

	if (wizard_include($_REQUEST["wizard"])) {
		/* wizard_render must exist in the included wizard file.  All actions should occur from there. */
		wizard_render();
	}

	include_once("include/bottom_footer.php");

}else{
	include_once("include/top_header.php");

	wizard_intro();
	
	include_once("include/bottom_footer.php");
}

/* 
#########################################
# Functions
#########################################
*/

function wizard_include($wizard) {
	global $wizard_array;

	if (isset($wizard_array[$wizard]["include"])) {
		$file = $wizard_array[$wizard]["include"];
		if (file_exists($file)) {
			include_once($file);
		}else{
			display_custom_error_message("Unable to include Wizard File for wizard \"" . $wizard . "\"");
			return false;
		}
	}else{
		display_custom_error_message("Invalid wizard \"" . $wizard . "\"");
		return false;
	}

	return true;

}

function wizard_intro() {
	global $colors, $wizard_array;

	/* create html and javascript lists for use in the html output */
	$javascript = "";
	$html = "";
	while (list($field_name, $field_array) = each($wizard_array)) {
		if (($field_name != "default") && ($field_name != "intro") && ($field_name != "title")) {
			$selected = "";
			if ($wizard_array["default"] == $field_name) {
				$selected = " checked";
			}
			$html .= "\t\t<input type='radio' name='wizard' value='" . $field_name . "' onClick='applyDescription(\"" . $field_name . "\");'" . $selected . "><i><span onClick='setSelect(document.forms[0].wizard,\"" . $field_name . "\");' style='cursor:pointer;cursor:hand'>" . $field_array["friendly_name"] . "</span></i><br>\n";
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
	print "function setSelect(objSelect,strIndex) {\n";
	print "  applyDescription(strIndex);\n";
	print "  for(x=0; x < objSelect.length; x++) {\n";
	print "    if (objSelect[x].value == strIndex) {\n";
	print "      objSelect[x].checked = true;\n";
	print "    }\n";
	print "  }\n";
	print "}\n";
	print "-->\n";
	print "</script>\n";

	/* html output */
	print "<form method='POST'>\n";
	print "<input type='hidden' name='slide' value='0'>\n";
	html_start_box("<strong>" . $wizard_array["title"] . "</strong>", "70%", $colors["header_background"], "3", "center", "");
	wizard_sub_header("Introduction");
	wizard_start_area();
	print "<br><blockquote>";
	print $wizard_array["intro"];
	print "</blockquote><br>\n";
	wizard_end_area();
	wizard_sub_header("Available Wizards");
	wizard_start_area();
	print "<table border='0' width='100%'>\n";
	print "\t<tr>\n";
	print "\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t<td width='25%' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b>Wizard</b><br><br>\n";
	print $html;
	print "\t\t</td>\n";
	print "\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t<td valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b>Description</b><br><br>\n";
	print "\t\t\t<div id='wizardarea' style='height:150px; overflow:auto;'>None</div><br>\n";
	print "\t\t</td>\n";
	print "\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t</tr>\n";
	print "</table>\n";
	wizard_end_area();
	wizard_footer(false,false,false,true);

	/* javascript function to change description box to default */
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "applyDescription('" . $wizard_array["default"] . "');\n";
	print "-->\n";
	print "</script>\n";

}

function wizard_header($wizard) {
	global $colors, $wizard_array;

	/* html output */
	print "<form method='POST'>\n";
	print "<input type='hidden' name='wizard' value='" . $wizard . "'>\n";
	if (isset($_REQUEST["slide_prev"])) {
		print "<input type='hidden' name='slide_prev' value='" . $_REQUEST["slide_prev"] . "'>\n";
	}

	html_start_box("<strong>" . $wizard_array[$wizard]["friendly_name"] . " Wizard</strong>", "70%", $colors["header_background"], "3", "center", "");

}

function wizard_footer($button_back = true,$button_cancel = false,$button_save = false,$button_next = false) {
	global $colors;

	html_end_box();

	print "\n<table align='center' width='70%' style='background-color: " . $colors['buttonbar_background'] . "; border: 1px solid #" . $colors["buttonbar_border"] . ";'>\n";
	print "\t<tr>\n";
	print "\t\t<td bgcolor='" . $colors['buttonbar_background'] . "' align='right'>\n";

	if ($button_back) {
		if (isset($_REQUEST["slide_prev"])) {
			print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_back.gif") . "' name='wizard_action' value='back' alt='Back' align='absmiddle'>\n";
		}else{
			print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_back.gif") . "' alt='Back' align='absmiddle' onClick=\"history.back(); return false;\">\n";
		}
	}
	if ($button_cancel) {
		print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_cancel2.gif") . "' name='wizard_action' value='cancel' alt='Cancel' align='absmiddle'>\n";
	}	
	if ($button_save) {
		print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_save.gif") . "' name='wizard_action' value='save' alt='Save' align='absmiddle'>\n";
	}
	if ($button_next) {
		print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_next.gif") . "' name='wizard_action' value='next' alt='Next' align='absmiddle'>\n";
	}

	print "\t\t</td>\n";
	print "\t</tr>\n";
	print "</table>\n";
	print "</form>\n";

}

function wizard_sub_header($title) {
	global $colors;

	print "<tr bgcolor='" . $colors["header_panel_background"] . "'><td class='textSubHeaderDark'>" . $title . "</td></tr>\n";

}

function wizard_start_area() {
	global $colors;

	print "<tr>\n";
	print "\t<td bgcolor='" . $colors["form_alternate1"] . "' class='textArea'>\n";

}

function wizard_end_area() {
	global $colors;

	print "\t</td>\n";
	print "</tr>\n"; 

}

?>
