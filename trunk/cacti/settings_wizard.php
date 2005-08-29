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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");

/* settings */
$wizard_array = array(
	"general" => array(
		"friendly_name" => _("General"),
		"description" => _("General wizard aids you in setting up Cacti's general settings for your enviroment."),
		"include" => "include/wizards/settings_general.php"
		),
	"snmp" => array(
		"friendly_name" => _("SNMP"),
		"description" => _("SNMP wizard aids in setting up the default SNMP settings of Cacti."),
		"include" => "include/wizards/settings_snmp.php"
		),
	"paths" => array(
		"friendly_name" => _("Paths"),
		"description" => _("Paths wizard aids in setting up the paths to required executables and logs for cacti."),
		"include" => "include/wizards/settings_paths.php"
		),
	"poller" => array(
		"friendly_name" => _("Poller"),
		"description" => _("Poller wizard aids in setting up the Poller Type, Poller Execution, Default Host Availability and Default Host Up/Down Settings."),
		"include" => "include/wizards/settings_poller.php"
		),
	"visual" => array(
		"friendly_name" => _("Visual"),
		"description" => _("Visual wizard aids in setting up the visual settings of Cacti, including the Default Theme, Rows per Page and other settings."),
		"include" => "include/wizards/settings_visual.php"
		),
	"auth" => array(
		"friendly_name" => _("Authenication"),
		"description" => _("Authenication wizard aids in setting up Cacti's Authenication system for your enviroment.  Be prepared to answer questions and have configuration settings that are appropriate to you enviroment.  If you plan on using LDAP, please have ready your LDAP server parameters, and if needed an authorzied binding for searching the LDAP server."),
		"include" => "include/wizards/settings_auth.php"
		),
	"graphexport" => array(
		"friendly_name" => _("Graph Export"),
		"description" => _("Graph Export wizard aids in setting up exporting of graphs to static pages, either locally or FTP to a remote server."),
		"include" => "include/wizards/settings_graphexport.php"
		),
	"default" => "general",
	"intro" => "<b>" . _("Welcome to the Settings Wizard")."</b><br><br>"._("This wizard is designed to help you setup features in Cacti.  Please select the section you would like to setup."),
	"title" => _("Setup Wizard"),
	"debug" => true
	);


/* Wizard processing */
wizard_process_action();

if (! is_null(wizard_read_var("wizard"))) {

	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	if (wizard_include(wizard_read_var("wizard"))) {
		/* wizard_render must exist in the included wizard file.  All actions should occur from there. */
		if (function_exists("wizard_render")) {
			wizard_render(wizard_read_var("wizard"));
		}else{
			display_custom_error_message(_("Invalid wizard \"") . $_REQUEST["wizard"] . _("\" unable to execute wizard_render function."));
			wizard_clear_vars();
		}
	}else{
		wizard_clear_vars();
	}

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");

}else{
	require_once(CACTI_BASE_PATH . "/include/top_header.php");

	wizard_intro();

	require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
}

/*
#########################################
# Wizard Functions
#########################################
*/

function wizard_include($wizard) {
	global $wizard_array;

	if (isset($wizard_array[$wizard]["include"])) {
		$file = $wizard_array[$wizard]["include"];
		if (file_exists($file)) {
			require_once($file);
		}else{
			display_custom_error_message(_("Unable to include Wizard File for wizard \"") . $wizard . "\"");
			return false;
		}
	}else{
		display_custom_error_message(_("Invalid wizard \"") . $wizard . "\"");
		return false;
	}

	return true;

}

function wizard_intro() {
	global $colors, $wizard_array;

	wizard_clear_vars();
	wizard_history("next","intro");

	/* create html and javascript lists for use in the html output */
	$javascript = "";
	$html = "";
	while (list($field_name, $field_array) = each($wizard_array)) {
		if (($field_name != "default") && ($field_name != "intro") && ($field_name != "title") && ($field_name != "debug")) {
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
	html_start_box("<strong>" . $wizard_array["title"] . "</strong>", "70%", $colors["header_background"], "3", "center", "");
	wizard_sub_header(_("Introduction"));
	wizard_start_area();
	print "<br><blockquote>";
	print $wizard_array["intro"];
	print "</blockquote><br>\n";
	wizard_end_area();
	wizard_sub_header(_("Available Wizards"));
	wizard_start_area();
	print "<table border='0' width='100%'>\n";
	print "\t<tr>\n";
	print "\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t<td width='25%' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b>" . _("Wizard") . "</b><br><br>\n";
	print $html;
	print "\t\t</td>\n";
	print "\t\t<td width='15' valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'></td>\n";
	print "\t\t<td valign='top' bgcolor='" . $colors["form_alternate1"] . "' class='textArea'><b>" . _("Description") . "</b><br><br>\n";
	print "\t\t\t<div id='wizardarea' style='height:150px; overflow:auto;'>" . _("None") . "</div><br>\n";
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

function wizard_header($wizard,$width = "70%") {
	global $colors, $wizard_array;

	/* html output */
	print "<form method='POST'>\n";
	html_start_box("<strong>" . $wizard_array[$wizard]["friendly_name"] . " " . _("Wizard") . "</strong>", $width, $colors["header_background"], "3", "center", "");


}

function wizard_footer($button_back = true,$button_cancel = false,$button_save = false,$button_next = false, $width = "70%") {
	global $colors, $wizard_array;

	html_end_box();

	print "\n<table align='center' width='" . $width . "' style='background-color: " . $colors['buttonbar_background'] . "; border: 1px solid #" . $colors["buttonbar_border"] . ";'>\n";
	print "\t<tr>\n";
	print "\t\t<td bgcolor='" . $colors['buttonbar_background'] . "' align='right'>\n";

	if ($button_back) {
		print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_back.gif") . "' name='wizard_action' value='back' alt='Back' align='absmiddle'>\n";
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

	/* debug output */
	wizard_print_debug();

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

function wizard_read_var($varname) {

	if (isset($_SESSION["wizard_vars"][$varname])) {
		return $_SESSION["wizard_vars"][$varname];
	}else{
		return NULL;
	}

}

function wizard_erase_var($varname) {

	if (isset($_SESSION["wizard_vars"][$varname])) {
		unset($_SESSION["wizard_vars"][$varname]);
	}

}

function wizard_save_var($varname,$value) {

	$_SESSION["wizard_vars"][$varname] = $value;

	if (isset($_SESSION["wizard_vars"][$varname])) {
		if ($_SESSION["wizard_vars"][$varname] == $value) {
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}

}

function wizard_clear_vars() {

	if (isset($_SESSION["wizard_vars"])) {
		unset($_SESSION["wizard_vars"]);
	}
	$_SESSION["wizard_vars"]["page_history"] = array();

}

function wizard_history($action = "current",$page = "") {

	if ($action == "next") {
		if (end($_SESSION["wizard_vars"]["page_history"]) != $page) {
			array_push($_SESSION["wizard_vars"]["page_history"],$page);
		}
		return $page;
	}elseif ($action == "back") {
		return array_pop($_SESSION["wizard_vars"]["page_history"]);
	}elseif ($action == "prev") {
		$count = count($_SESSION["wizard_vars"]["page_history"]);
		if ($count > 0) {
			$count = $count - 2;
		}
		if ($count < 0) {
			return "intro";
		}
		return $_SESSION["wizard_vars"]["page_history"][$count];
	}else{
		if (isset($_SESSION["wizard_vars"]["page_history"])) {
			$count = count($_SESSION["wizard_vars"]["page_history"]) - 1;
			if ($count < 0) {
				return "intro";
			}
			return $_SESSION["wizard_vars"]["page_history"][$count];
		}else{
			return "intro";
		}
	}

}

function wizard_process_action() {

	/* If the user leaves wizard and returns, prompt to continue */
	if (isset($_SERVER["HTTP_REFERER"])) {
		if (basename($_SERVER["HTTP_REFERER"]) != basename(__FILE__)) {
			if (wizard_history() != "intro") {
				require_once(CACTI_BASE_PATH . "/include/top_header.php");
				wizard_continue_prompt();
				require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
				exit;
			}
		}
	}

	/* Process buttons */
	if (isset($_REQUEST["wizard_action"])) {
		switch ($_REQUEST["wizard_action"]) {
			case "next":
				if (wizard_history() == "intro") {
					wizard_save_var("wizard",$_REQUEST["wizard"]);
					wizard_history("next","0");
				}
				if (isset($_REQUEST["next_page"])) {
					wizard_history("next",$_REQUEST["next_page"]);
				}
				break;

			case "back":
				if (wizard_history("prev") == "intro") {
					wizard_clear_vars();
				}
				if (isset($_REQUEST["back_page"])) {
					wizard_history("next",$_REQUEST["back_page"]);
				}else{
					wizard_history("back");
				}
				break;

			case "reset":
				wizard_clear_vars();
				break;
		}
	}

}

function wizard_continue_prompt() {
	global $colors;

	html_start_box("<strong>" . _("Wizard Continuation") . "</strong>", "50%", $colors["header_background"], "3", "center", "");
	print "<form method='POST'>\n";
	wizard_start_area();
	print "<br><blockquote>" . _("It has been detected that you have left the wizard and returned.<br><br>Would you like to continue where you left off?") . "</blockquote><br>";
	wizard_end_area();
	html_end_box();

	print "\n<table align='center' width='50%' style='background-color: " . $colors['buttonbar_background'] . "; border: 1px solid #" . $colors["buttonbar_border"] . ";'>\n";
	print "\t<tr>\n";
	print "\t\t<td bgcolor='" . $colors['buttonbar_background'] . "' align='right'>\n";
	print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_no.gif") . "' name='wizard_action' value='reset' alt='No' align='absmiddle'>\n";
	print "\t\t\t<input type='image' src='" . html_get_theme_images_path("button_yes.gif") . "' name='wizard_action' value='continue_yes' alt='Yes' align='absmiddle'>\n";
	print "\t\t</td>\n";
	print "\t</tr>\n";
	print "</table>\n";
	print "</form>\n";

}

function wizard_print_debug() {

	global $wizard_array;
	if (isset($wizard_array["debug"])) {
		if ($wizard_array["debug"]) {
			print "<br><br><br>";
			print "<hr noshade>";
			print "<table border='1' cellpadding='4' cellspacing='0' align='center'>\n";
			print "<tr><td align='center' colspan='2'><b>" . _("WIZARD DEBUG") . "</b></td></tr>\n";
			print "<tr><td valign='top'><pre>";
			print _("Wizard Session Variables:") . "\n";
			print_r($_SESSION["wizard_vars"]);
			print "</pre></td><td valign='top'><pre>";
			print "\n" . _("Form Variables:") . "\n";
			print_r($_REQUEST);
			print "</pre></td></tr></table>\n";
		}
	}

}

?>
