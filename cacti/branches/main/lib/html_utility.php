<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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

/* inject_form_variables - replaces all variables contained in $form_array with
     their actual values
   @arg $form_array - an array that contains all of the information needed to draw
     the html form. see the arrays contained in include/global_settings.php
     for the extact syntax of this array
   @arg $arg1 - an array that represents the |arg1:| variable (see
     include/global_form.php for more details)
   @arg $arg2 - an array that represents the |arg2:| variable (see
     include/global_form.php for more details)
   @arg $arg3 - an array that represents the |arg3:| variable (see
     include/global_form.php for more details)
   @arg $arg4 - an array that represents the |arg4:| variable (see
     include/global_form.php for more details)
   @returns - $form_array with all available variables substituted with their
     proper values */
function inject_form_variables(&$form_array, $arg1 = array(), $arg2 = array(), $arg3 = array(), $arg4 = array()) {
	$check_fields = array("value", "array", "friendly_name", "description", "sql", "sql_print", "form_id", "items");

	/* loop through each available field */
	while (list($field_name, $field_array) = each($form_array)) {
		/* loop through each sub-field that we are going to check for variables */
		foreach ($check_fields as $field_to_check) {
			if (isset($field_array[$field_to_check]) && (is_array($form_array[$field_name][$field_to_check]))) {
				/* if the field/sub-field combination is an array, resolve it recursively */
				$form_array[$field_name][$field_to_check] = inject_form_variables($form_array[$field_name][$field_to_check], $arg1);
			}elseif (isset($field_array[$field_to_check]) && (!is_array($field_array[$field_to_check])) && (ereg("\|(arg[123]):([a-zA-Z0-9_]*)\|", $field_array[$field_to_check], $matches))) {
				/* an empty field name in the variable means don't treat this as an array */
				if ($matches[2] == "") {
					if (is_array(${$matches[1]})) {
						/* the existing value is already an array, leave it alone */
						$form_array[$field_name][$field_to_check] = ${$matches[1]};
					}else{
						/* the existing value is probably a single variable */
						$form_array[$field_name][$field_to_check] = str_replace($matches[0], ${$matches[1]}, $field_array[$field_to_check]);
					}
				}else{
					/* copy the value down from the array/key specified in the variable */
					$form_array[$field_name][$field_to_check] = str_replace($matches[0], ((isset(${$matches[1]}{$matches[2]})) ? ${$matches[1]}{$matches[2]} : ""), $field_array[$field_to_check]);
				}
			}
		}
	}

	return $form_array;
}

/* form_alternate_row_color - starts an HTML row with an alternating color scheme
   @arg $row_id 	- used to allow js and ajax actions on this object
   @arg $hover		- use onMouse events
   @arg $row_class 	- additional class attributes, e.g. "nodrag nodrop"
   @returns 		- the background color used for this particular row */
function form_alternate_row_color($row_id = "", $hover = false, $row_class = "") {
	static $class_int  = 1;
	static $alt_row_id = 0;

	if (strlen($row_class)) $row_class = " " . $row_class;

	if ($class_int == 1) {
		$class = "rowAlternate1" . $row_class;
	}else{
		$class = "rowAlternate2" . $row_class;
	}

	$class_hover = "rowSelected" . $row_class;

	$class_int = ($class_int + 1) % 2;

	if (is_numeric($row_id)) {
		if ($hover) {
			print "<tr id='row_$row_id' class='$class' onMouseOver='(this.className=\"$class_hover\")' onMouseOut='(this.className=\"$class\")'>\n";
		}else{
			print "<tr id='row_$row_id' class='$class'>\n";
		}
	}else{
		if (strlen($row_id)) {
			if ($hover) {
				print "<tr id='$row_id' class='$class' onMouseOver='(this.className=\"$class_hover\")' onMouseOut='(this.className=\"$class\")'>\n";
			}else{
				print "<tr id='$row_id' class='$class'>\n";
			}
		}else{
			if ($hover) {
				print "<tr id='row_$alt_row_id' class='$class' onmouseover='this.className=\"$class_hover\"' onmouseout='(this.className=\"$class\")'>\n";
			}else{
				print "<tr id='row_$alt_row_id' class='$class'>\n";
			}
		}
	}
}

/* form_selectable_cell - format's a table row such that it can be highlighted using cacti's js actions
   @arg $contents - the readable portion of the
   @arg $id - the id of the object that will be highlighted
   @arg $width - the width of the table element
   @arg $style - the style to apply to the table element */
function form_selectable_cell($contents, $id, $width="", $style="") {
	print "\t<td onClick='select_line($id)'" . (strlen($width) ? " width='$width'" : "") . (strlen($style) ? " style='$style;'" : "") . ">" . $contents . "</td>\n";
}

/* form_checkbox_cell - format's a tables checkbox form element so that the cacti js actions work on it
   @arg $title - the text that will be displayed if your hover over the checkbox */
function form_checkbox_cell($title, $id, $checked = false) {
	print "\t<td onClick='select_line($id,true)' style='" . get_checkbox_style() . "' width='1%' align='center'>\n";
	print "\t\t<input type='checkbox' title='$title' style='margin: 0px;' id='chk_" . $id . "' name='chk_" . $id . "'" . ($checked ? " checked" : "") . ">\n";
	print "\t</td>\n";
}

/* form_end_row - ends a table row that is started with form_alternate_row */
function form_end_row() {
	print "</tr>\n";
}

/* form_end_table - properly ends a table and starts the next navigation */
function form_end_table() {
	print "</table>\n";
	print "<table cellspacing='0' cellpadding='0' width='100%'>\n";
}

/* html_boolean - returns the boolean equivalent of an HTML checkbox value
   @arg $html_boolean - the value of the HTML checkbox
   @returns - true or false based on the value of the HTML checkbox */
function html_boolean($html_boolean) {
	if ($html_boolean == "on") {
		return true;
	}else{
		return false;
	}
}

/* html_boolean_friendly - returns the natural language equivalent of an HTML
     checkbox value
   @arg $html_boolean - the value of the HTML checkbox
   @returns - 'Selected' or 'Not Selected' based on the value of the HTML
     checkbox */
function html_boolean_friendly($html_boolean) {
	if ($html_boolean == "on") {
		return __("Selected");
	}else{
		return __("Not Selected");
	}
}

/* get_checkbox_style - finds the proper CSS padding to apply based on the
     current client browser in use
   @returns - a CSS style string which should be used with an HTML checkbox
     control */
function get_checkbox_style() {
	if (get_web_browser() == "moz") {
		return "padding: 4px; margin: 4px;";
	}elseif (get_web_browser() == "ie") {
		return "padding: 0px; margin: 0px;";
	}elseif (get_web_browser() == "other") {
		return "padding: 4px; margin: 4px;";
	}
}

/* get_request_var - returns the current value of a PHP $_GET variable, optionally
     returning a default value if the request variable does not exist
   @arg $name - the name of the request variable. this should be a valid key in the
     $_GET array
   @arg $default - the value to return if the specified name does not exist in the
     $_GET array
   @returns - the value of the request variable */
function get_request_var($name, $default = "") {
	if (isset($_GET[$name])) {
		if (isset($_POST[$name])) {
			unset($_POST[$name]);
			$_REQUEST[$name] = $_GET[$name];
		}

		return $_GET[$name];
	}else{
		return $default;
	}
}

/* get_request_var_post - returns the current value of a PHP $_POST variable, optionally
     returning a default value if the request variable does not exist
   @arg $name - the name of the request variable. this should be a valid key in the
     $_POST array
   @arg $default - the value to return if the specified name does not exist in the
     $_POST array
   @returns - the value of the request variable */
function get_request_var_post($name, $default = "") {
	if (isset($_POST[$name])) {
		if (isset($_GET[$name])) {
			unset($_GET[$name]);
			$_REQUEST[$name] = $_POST[$name];
		}

		return $_POST[$name];
	}else{
		return $default;
	}
}

/* get_request_var_request - returns the current value of a PHP $_REQUEST variable, optionally
     returning a default value if the request variable does not exist
   @arg $name - the name of the request variable. this should be a valid key in the
     $_REQUEST array
   @arg $default - the value to return if the specified name does not exist in the
     $_REQUEST array
   @returns - the value of the request variable */
function get_request_var_request($name, $default = "") {
	if (isset($_REQUEST[$name])) {
		return $_REQUEST[$name];
	}else{
		return $default;
	}
}


/* load_current_session_value - finds the correct value of a variable that is being
     cached as a session variable on an HTML form
   @arg $request_var_name - the array index name for the request variable
   @arg $session_var_name - the array index name for the session variable
   @arg $default_value - the default value to use if values cannot be obtained using
     the session or request array */
function load_current_session_value($request_var_name, $session_var_name, $default_value) {
	if (isset($_REQUEST[$request_var_name])) {
		$_SESSION[$session_var_name] = $_REQUEST[$request_var_name];
	}elseif (isset($_SESSION[$session_var_name])) {
		$_REQUEST[$request_var_name] = $_SESSION[$session_var_name];
	}else{
		$_REQUEST[$request_var_name] = $default_value;
		$_SESSION[$session_var_name] = $_REQUEST[$request_var_name];
	}
}

/* get_colored_device_status - given a device's status, return the colored text in HTML
     format suitable for display
   @arg $disabled (bool) - true if the device is disabled, false is it is not
   @arg $status - the status type of the device as defined in global_constants.php
   @returns - a string containing html that represents the device's current status */
function get_colored_device_status($disabled, $status) {
	$disabled_color = "a1a1a1";

	$status_colors = array(
		HOST_DOWN => "ff0000",
		HOST_ERROR => "750F7D",
		HOST_RECOVERING => "ff8f1e",
		HOST_UP => "198e32"
		);

	if ($disabled) {
		return "<span style='color: #$disabled_color'>" . __("Disabled") . "</span>";
	}else{
		switch ($status) {
			case HOST_DOWN:
				return "<span style='color: #" . $status_colors[HOST_DOWN] . "'>" . __("Down") . "</span>"; break;
			case HOST_RECOVERING:
				return "<span style='color: #" . $status_colors[HOST_RECOVERING] . "'>" . __("Recovering") . "</span>"; break;
			case HOST_UP:
				return "<span style='color: #" . $status_colors[HOST_UP] . "'>" . __("Up") . "</span>"; break;
			case HOST_ERROR:
				return "<span style='color: #" . $status_colors[HOST_ERROR] . "'>" . __("Error")  . "</span>"; break;
			default:
				return "<span style='color: #0000ff'>" . __("Unknown") . "</span>"; break;
		}
	}
}

/* get_current_graph_start - determine the correct graph start time selected using
     the timespan selector
   @returns - the number of seconds relative to now where the graph should begin */
function get_current_graph_start() {
	if (isset($_SESSION["sess_current_timespan_begin_now"])) {
		return $_SESSION["sess_current_timespan_begin_now"];
	}else{
		return "-" . DEFAULT_TIMESPAN;
	}
}

/* get_current_graph_end - determine the correct graph end time selected using
     the timespan selector
   @returns - the number of seconds relative to now where the graph should end */
function get_current_graph_end() {
	if (isset($_SESSION["sess_current_timespan_end_now"])) {
		return $_SESSION["sess_current_timespan_end_now"];
	}else{
		return "0";
	}
}

/* get_page_list - generates the html necessary to present the user with a list of pages limited
     in length and number of rows per page
   @arg $current_page - the current page number
   @arg $pages_per_screen - the maximum number of pages allowed on a single screen. odd numbered
     values for this argument are prefered for equality reasons
   @arg $current_page - the current page number
   @arg $total_rows - the total number of available rows
   @arg $url - the url string to prepend to each page click
   @returns - a string containing html that represents the a page list */
function get_page_list($current_page, $pages_per_screen, $rows_per_page, $total_rows, $url, $page_var = "page") {
	$url_page_select = "";

	$total_pages = ceil($total_rows / $rows_per_page);

	$start_page = max(1, ($current_page - floor(($pages_per_screen - 1) / 2)));
	$end_page = min($total_pages, ($current_page + floor(($pages_per_screen - 1) / 2)));

	/* adjust if we are close to the beginning of the page list */
	if ($current_page <= ceil(($pages_per_screen) / 2)) {
		$end_page += ($pages_per_screen - $end_page);
	}else{
		$url_page_select .= "...";
	}

	/* adjust if we are close to the end of the page list */
	if (($total_pages - $current_page) < ceil(($pages_per_screen) / 2)) {
		$start_page -= (($pages_per_screen - ($end_page - $start_page)) - 1);
	}

	/* stay within limits */
	$start_page = max(1, $start_page);
	$end_page = min($total_pages, $end_page);

	//print "start: $start_page, end: $end_page, total: $total_pages<br>";

	for ($page_number=0; (($page_number+$start_page) <= $end_page); $page_number++) {
		if ($page_number < $pages_per_screen) {
			if ($current_page == ($page_number + $start_page)) {
				if (substr_count($url, ".php")) {
					$url_page_select .= "<strong><a class='linkOverDark' href='" . htmlspecialchars("$url&" . $page_var . "=" . ($page_number + $start_page)) . "'>" . ($page_number + $start_page) . "</a></strong>";
				}else{
					$url_page_select .= "<strong><a class='linkOverDark' href='#' onClick='" . $url . "(" . ($page_number + $start_page) . ")'>" . ($page_number + $start_page) . "</a></strong>";
				}
			}else{
				if (substr_count($url, ".php")) {
					$url_page_select .= "<a class='linkOverDark' href='" . htmlspecialchars("$url&" . $page_var . "=" . ($page_number + $start_page)) . "'>" . ($page_number + $start_page) . "</a>";
				}else{
					$url_page_select .= "<a class='linkOverDark' href='#' onClick='" . $url . "(" . ($page_number + $start_page) . ")'>" . ($page_number + $start_page) . "</a>";
				}
			}
		}

		if (($page_number+$start_page) < $end_page) {
			$url_page_select .= ",";
		}
	}

	if (($total_pages - $current_page) >= ceil(($pages_per_screen) / 2)) {
		$url_page_select .= "...";
	}

	return $url_page_select;
}


/* clean_html_output - Remove all known cross site script (xss) attack vectors from user input
   @arg $data - User input to process
   @returns - Cleaned user input
*/
function clean_html_output($data) {
	/* remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	this prevents some character re-spacing such as <java\0script>
	note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs */
	$data = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $data);

	/* straight replacements, the user should never need these since they're normal characters
	this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29> */
	$search = "abcdefghijklmnopqrstuvwxyz";
	$search .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$search .= "1234567890!@#$%^&*()";
	$search .= "~`\";:?+/={}[]-_|\'\\";
	for ($i = 0; $i < strlen($search); $i++) {
		/* ;? matches the ;, which is optional
		0{0,7} matches any padded zeros, which are optional and go up to 8 chars */
		/* &#x0040 @ search for the hex values */
		$data = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $data);
		/* &#00064 @ 0{0,7} matches '0' zero to seven times */
		$data = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $data);
	}

	/* defined keywords to process for removal */
	$keywords = Array(
		"javascript",
		"vbscript",
		"expression",
		"applet",
		"meta",
		"xml",
		"blink",
		"link",
		"style",
		"script",
		"embed",
		"object",
		"iframe",
		"frame",
		"frameset",
		"ilayer",
		"layer",
		"bgsound",
		"title",
		"base",
		"onabort",
		"onactivate",
		"onafterprint",
		"onafterupdate",
		"onbeforeactivate",
		"onbeforecopy",
		"onbeforecut",
		"onbeforedeactivate",
		"onbeforeeditfocus",
		"onbeforepaste",
		"onbeforeprint",
		"onbeforeunload",
		"onbeforeupdate",
		"onblur",
		"onbounce",
		"oncellchange",
		"onchange",
		"onclick",
		"oncontextmenu",
		"oncontrolselect",
		"oncopy",
		"oncut",
		"ondataavailable",
		"ondatasetchanged",
		"ondatasetcomplete",
		"ondblclick",
		"ondeactivate",
		"ondrag",
		"ondragend",
		"ondragenter",
		"ondragleave",
		"ondragover",
		"ondragstart",
		"ondrop",
		"onerror",
		"onerrorupdate",
		"onfilterchange",
		"onfinish",
		"onfocus",
		"onfocusin",
		"onfocusout",
		"onhelp",
		"onkeydown",
		"onkeypress",
		"onkeyup",
		"onlayoutcomplete",
		"onload",
		"onlosecapture",
		"onmousedown",
		"onmouseenter",
		"onmouseleave",
		"onmousemove",
		"onmouseout",
		"onmouseover",
		"onmouseup",
		"onmousewheel",
		"onmove",
		"onmoveend",
		"onmovestart",
		"onpaste",
		"onpropertychange",
		"onreadystatechange",
		"onreset",
		"onresize",
		"onresizeend",
		"onresizestart",
		"onrowenter",
		"onrowexit",
		"onrowsdelete",
		"onrowsinserted",
		"onscroll",
		"onselect",
		"onselectionchange",
		"onselectstart",
		"onstart",
		"onstop",
		"onsubmit",
		"onunload"
	);

	$found = true;
	while ($found == true) {
		$data_before = $data;
		for ($i = 0; $i < sizeof($keywords); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($keywords[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $keywords[$i][$j];
			}
			$pattern .= '/i';
			/* add in <> to nerf the tag */
			$replacement = substr($keywords[$i], 0, 2).'<x>'.substr($keywords[$i], 2);
			$data = preg_replace($pattern, $replacement, $data);
			/* filter out the hex tags */
			if ($data_before == $data) {
				/* no replacements were made, so exit the loop */
				$found = false;
			}
		}
	}
	return $data;
}

?>
