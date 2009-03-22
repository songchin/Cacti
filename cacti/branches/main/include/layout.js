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

/* graph template stuff */
function gt_update_selection_indicators() {
	if (document.getElementById) {
		there_are_any_unchecked_ones = false;

		for (var j = 0; j < document.chk.elements.length; j++) {
			if (document.chk.elements[j].name.substr(0,3) == 'cg_') {
				if (document.chk.elements[j].checked == false) {
					there_are_any_unchecked_ones = true;
				}

				if (!isNaN(document.chk.elements[j].name.substr(3))) {
					lineid = document.getElementById('gt_line' + document.chk.elements[j].name.substr(3));

					if (document.chk.elements[j].checked) {
						lineid.style.backgroundColor = 'khaki';
					}else{
						lineid.style.backgroundColor = '';
					}
				}
			}
		}
	}
}

function gt_select_line(graph_template_id, update) {
	if (gt_is_disabled(graph_template_id)) { return; }

	if (document.getElementById) {
		msgid = document.getElementById('cg_' + graph_template_id);
		lineid = document.getElementById('gt_line'+ graph_template_id);

		if (!update) msgid.checked = !msgid.checked;

		gt_update_selection_indicators();
	}
}

function gt_is_disabled(graph_template_id) {
	for (var i = 0; i < gt_created_graphs.length; i++) {
		if (gt_created_graphs[i] == graph_template_id) {
			return true;
		}
	}

	return false;
}

function gt_update_deps(num_columns) {
	gt_reset_deps(num_columns);

	for (var i = 0; i < gt_created_graphs.length; i++) {
		for (var j = 0; j < num_columns; j++) {
			lineid = document.getElementById('gt_text' + gt_created_graphs[i] + '_' + j);
			lineid.style.color = '999999';
		}

		chkbx = document.getElementById('cg_' + gt_created_graphs[i]);
		chkbx.style.visibility = 'hidden';
		chkbx.checked = false;

		lineid = document.getElementById('gt_line' + gt_created_graphs[i]);
		lineid.style.backgroundColor = '';
	}
}

function gt_reset_deps(num_columns) {
	var prefix = 'cg_'

	for (var i = 0; i < document.chk.elements.length; i++) {
		if (document.chk.elements[i].name.substr( 0, prefix.length ) == prefix) {
			for (var j = 0; j < num_columns; j++) {
				lineid = document.getElementById('gt_text' + document.chk.elements[i].name.substr(prefix.length) + '_' + j);
				lineid.style.color = '000000';
			}

			chkbx = document.getElementById('cg_' + document.chk.elements[i].name.substr(prefix.length));
			chkbx.style.visibility = 'visible';
		}
	}
}

/* general id based selects */
function update_selection_indicators() {
	if (document.getElementById) {
		there_are_any_unchecked_ones = false;

		for (var j = 0; j < document.chk.elements.length; j++) {
			if( document.chk.elements[j].name.substr( 0, 4 ) == 'chk_') {
				if (document.chk.elements[j].checked == false) {
					there_are_any_unchecked_ones = true;
				}

				lineid = document.getElementById('line'+ document.chk.elements[j].name.substr(4));

				if (document.chk.elements[j].checked) {
					lineid.style.backgroundColor = 'khaki';
				}else{
					lineid.style.backgroundColor = '';
				}
			}
		}
	}
}

function select_line(id, update) {
	if (document.getElementById) {
		msgid  = document.getElementById('chk_' + id);
		lineid = document.getElementById('line'+ id);

		if (!update) msgid.checked = !msgid.checked;

		update_selection_indicators();
	}
}

/* data query stuff */
function dq_update_selection_indicators() {
	if (document.getElementById) {
		there_are_any_unchecked_ones = false;

		for (var j = 0; j < document.chk.elements.length; j++) {
			if( document.chk.elements[j].name.substr( 0, 3 ) == 'sg_') {
				if (document.chk.elements[j].checked == false) {
					there_are_any_unchecked_ones = true;
				}

				lineid = document.getElementById('line'+ document.chk.elements[j].name.substr(3));

				if (document.chk.elements[j].checked) {
					lineid.style.backgroundColor = 'khaki';
				}else{
					lineid.style.backgroundColor = '';
				}
			}
		}
	}
}

function dq_select_line(snmp_query_id, snmp_index, update) {
	if (dq_is_disabled(snmp_query_id, snmp_index)) { return; }

	if (document.getElementById) {
		msgid  = document.getElementById('sg_' + snmp_query_id + '_' + snmp_index);
		lineid = document.getElementById('line'+ snmp_query_id + '_' + snmp_index);

		if (!update) msgid.checked = !msgid.checked;

		dq_update_selection_indicators();
	}
}

function dq_is_disabled(snmp_query_id, snmp_index) {
	dropdown = document.getElementById('sgg_' + snmp_query_id);
	var snmp_query_graph_id = dropdown.value

	for (var i = 0; i < created_graphs[snmp_query_graph_id].length; i++) {
		if (created_graphs[snmp_query_graph_id][i] == snmp_index) {
			return true;
		}
	}

	return false;
}

function dq_update_deps(snmp_query_id, num_columns) {
	dq_reset_deps(snmp_query_id, num_columns);

	dropdown = document.getElementById('sgg_' + snmp_query_id);
	var snmp_query_graph_id = dropdown.value

	for (var i = 0; i < created_graphs[snmp_query_graph_id].length; i++) {
		for (var j = 0; j < num_columns; j++) {
			lineid = document.getElementById('text' + snmp_query_id + '_' + created_graphs[snmp_query_graph_id][i] + '_' + j);
			if ( lineid ) { lineid.style.color = '999999' };
		}

		chkbx = document.getElementById('sg_' + snmp_query_id + '_' + created_graphs[snmp_query_graph_id][i]);
		if ( chkbx ) {
			chkbx.style.visibility = 'hidden';
			chkbx.checked          = false;
		}

		lineid = document.getElementById('line' + snmp_query_id + '_' + created_graphs[snmp_query_graph_id][i]);
		if ( lineid ) { lineid.style.backgroundColor = '' };
	}
}

function dq_reset_deps(snmp_query_id, num_columns) {
	var prefix = 'sg_' + snmp_query_id + '_'

	for (var i = 0; i < document.chk.elements.length; i++) {
		if (document.chk.elements[i].name.substr( 0, prefix.length ) == prefix) {
			for (var j = 0; j < num_columns; j++) {
				lineid = document.getElementById('text' + snmp_query_id + '_' + document.chk.elements[i].name.substr(prefix.length) + '_' + j);
				lineid.style.color = '000000';
			}

			chkbx = document.getElementById('sg_' + snmp_query_id + '_' + document.chk.elements[i].name.substr(prefix.length));
			chkbx.style.visibility = 'visible';
		}
	}
}

function SelectAll(prefix, checkbox_state) {
	for (var i = 0; i < document.chk.elements.length; i++) {
		if ((document.chk.elements[i].name.substr(0, prefix.length) == prefix) && (document.chk.elements[i].style.visibility != 'hidden')) {
			document.chk.elements[i].checked = checkbox_state;
		}

		if (prefix == "chk_") {
			lineid = document.getElementById('line'+ document.chk.elements[i].name.substr(4));

			if (document.chk.elements[i].checked) {
				if ( lineid ) { lineid.style.backgroundColor = 'khaki'; }
			}else{
				if ( lineid ) { lineid.style.backgroundColor = ''; }
			}
		}
	}
}

function SetSelections() {
	for (var i = 0; i < document.chk.elements.length; i++) {
		lineid = document.getElementById('line'+ document.chk.elements[i].name.substr(4));

		if (document.chk.elements[i].checked) {
			if ( lineid ) { lineid.style.backgroundColor = 'khaki'; }
		}else{
			if ( lineid ) { lineid.style.backgroundColor = ''; }
		}
	}
}

function SelectAllGraphs(prefix, checkbox_state) {
	for (var i = 0; i < document.graphs.elements.length; i++) {
		if ((document.graphs.elements[i].name.substr(0, prefix.length) == prefix) &&
			(document.graphs.elements[i].style.visibility != 'hidden')) {
			document.graphs.elements[i].checked = checkbox_state;
		}
	}
}

function navigation_select(name, location) {
	createCookie("navbar_id", name);

	document.location = location;
}

function htmlStartBoxFilterChange(id, initialize) {
	filter = readCookieElement("fs", id);

	if (filter == "o") {
		if (initialize != null) {
			/* do nothing we want to stay the same */
		}else{
			createCookieElement("fs", id, "c");
			filter = "c";
		}
	}else{
		if (initialize != null) {
			if (filter == "c") {
				/* do nothing we want to stay the same */
			}else{
				createCookieElement("fs", id, "o");
				filter = "o";
			}
		}else{
			createCookieElement("fs", id, "o");
			filter = "o";
		}
	}

	if (filter == "c") {
		document.getElementById(id).style.display  = "none";
		document.getElementById(id+'_twisty').src = "images/tw_close.gif";
	}else{
		document.getElementById(id).style.display  = "";
		document.getElementById(id+'_twisty').src = "images/tw_open.gif";
	}
}

function changeMenuState(id, initialize) {
	filter = readCookieElement("menu", id);
	object = document.getElementById("ul_"+id);

	if (filter == "o") {
		if (initialize != null) {
			createCookieElement("menu", id, "o");

			/* set the display properly */
			object.style.height = object.scrollHeight + "px";
		}else{
			createCookieElement("menu", id, "c");
			closeMenu(id);
		}
	}else{
		if (initialize != null) {
			if (filter == "c") {
				createCookieElement("menu", id, "c");

				/* set the display properly */
				object.style.height = "0px";
			}else{
				createCookieElement("menu", id, "o");

				/* set the display properly */
				object.style.height = object.scrollHeight + "px";
			}
		}else{
			createCookieElement("menu", id, "o");
			openMenu(id);
		}
	}
}

function closeMenu(id) {
	element = document.getElementById("ul_"+id);

	if (!aniInProgress) {
		aniInProgress = true;
		closeMe = setInterval(function() { moveUp(element) }, 10);
		aniInProgress = false;
	}
}

function openMenu(id) {
	element = document.getElementById("ul_"+id);

	if (!aniInProgress) {
		aniInProgress = true;
		openMe  = setInterval(function() { moveDown(element) }, 10);
		aniInProgress = false;
	}
}

function moveUp(object) {
	newEM = parseInt(object.style.height);

	if ((newEM - 15) < 0) {
		newEM = 0;
	}else{
		newEM = newEM - 15;
	}

	object.style.height = newEM + "px";

	if (newEM <= 0) {
		clearInterval(closeMe);
	}
}

function moveDown(object) {
	newEM = parseInt(object.style.height);

	if ((newEM + 15) > object.scrollHeight) {
		newEM = object.scrollHeight;
	}else{
		newEM = newEM + 15;
	}

	object.style.height  = newEM + "px";

	if (newEM >= object.scrollHeight) {
		clearInterval(openMe);
	}
}

var objTh           = null;
var objDiv          = null;
var overColumn      = false;
var overVSplit      = false;
var resizedColumn   = false;
var resizedDiv      = false;
var iEdgeThreshold  = 10;
var aniInProgress   = false;
var vSplitterClosed = false;
var creatingCookie  = false;
var browser         = "Unknwn";
var windowOnLoadReg = new Array();
var windowOnLoadCt  = 0;

/* tells if on the right border or not */
function isOnBorderRight(object, event) {
	var width    = object.offsetWidth;
	var pos      = findPos(object);
	var absRight = pos[0] + width;

	if (event.clientX > (absRight - iEdgeThreshold)) {
		return true;
	}

	return false;
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft;
		curtop  = obj.offsetTop;

		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft;
			curtop  += obj.offsetTop;
		}
	}

	return [curleft,curtop];
}

/* tells if on the bottom border or not */
function isOnBorderBottom(object, event) {
	var height = object.offsetHeight;
	var pos    = findPos(object);
	var absTop = pos[1];

	if (event.clientY > (absTop + object.offsetHeight - iEdgeThreshold)) {
		return true;
	}

	return false;
}

function getParentNode(objReference, nodeName, className) {
	var oElement = objReference;
	while (oElement != null && oElement.tagName != null && oElement.tagName != "BODY") {
		if (oElement.tagName.toUpperCase() == nodeName &&
			(className == null || oElement.className.search("\b"+className+"\b") != 1)) {
			return oElement;
		}

		oElement = oElement.parentNode;
	}

	return null;
}

function doColResize(object, event){
	if(!event) event = window.event;

	if (isOnBorderRight(object, event)) {
		overColumn          = true;
		object.style.cursor = "e-resize";
	}else{
		overColumn          = false;
		object.style.cursor = "";
	}

	return overColumn;
}

function doneColResize(){
	overColumn = false;

	if (resizedColumn) {
		saveColumnWidths();
		resizedColumn = false;
	}
}

function initColumnWidths() {
	columns = document.getElementsByTagName("TH");

	pathname = getBaseName();

	if (columns.length > 0) {
		for (i = 0; i < columns.length; i++) {
			cur_value = readCookieElement(pathname, columns[i].id);

			if (cur_value) {
				columns[i].style.width = cur_value + "px";
			}
		}
	}
}

function getBaseName() {
	pathname = location.pathname;

	while (pathname.indexOf("/") >= 0) {
		pathname = pathname.substring(pathname.indexOf("/")+1);
	}

	return pathname.replace(".php", "");
}

function saveColumnWidths() {
	columns = document.getElementsByTagName("TH");

	pathname = getBaseName();

	for (i = 0; i < columns.length; i++) {
		createCookieElement(pathname, columns[i].id, parseInt(columns[i].style.width));
	}
}

function doDivResize(object, event){
	if (!event) event = window.event;

	if (isOnBorderRight(object, event)) {
		overVSplit          = true;
		object.style.cursor = "e-resize";
	}else{
		overVSplit          = false;
		object.style.cursor = "";
	}

	return overColumn;
}

function doneDivResize(){
	overVSplit = false;

	if (resizedDiv == true) {
		if (document.getElementById("vsplitter")) {
			createCookieElement("menu", "vsplitter_last", parseInt(document.getElementById("vsplitter").style.marginLeft));
			resizedDiv = false;
		}
	}

}

function vSplitterToggle() {
	if (vSplitterClosed) {
		vSplitterClosed = false;
		createCookieElement("menu", "vsplitter_status", "0");
	}else{
		vSplitterClosed = true;
		createCookieElement("menu", "vsplitter_status", "1");
	}

	vSplitterPos();
}

function MouseDown(event) {
	if (!event) event = window.event;

	MOUSTSTART_X = event.clientX;
	MOUSTSTART_Y = event.clientY;

	if (overColumn) {
		if (event.srcElement)objTh = event.srcElement;
		else if (event.target)objTh = event.target;
		else return;

		objTh = getParentNode(objTh,"TH");

		if (objTh == null) return;

		objTable      = getParentNode(objTh,"TABLE");
		objThWidth    = parseInt(objTh.style.width);

		if (objThWidth > 0) {
		}else{
			objThWidth = parseInt(objTh.scrollWidth);
		}

		objTableWidth = parseInt(objTable.offsetWidth);
	} else if (overVSplit) {
		if (event.srcElement)objDiv = event.srcElement;
		else if (event.target)objDiv = event.target;
		else return;

		objDiv = getParentNode(objDiv,"DIV");

		if (objDiv == null) return;

		objDivWidth   = objDiv.offsetLeft;
	}
}

function MouseMove(event) {
	if (!event) event = window.event;

	/* let's see how wide the page is */
	var clWidth = document.getElementById("wrapper").clientWidth;

	if (objTh) {
		thSt    = event.clientX - MOUSTSTART_X + objThWidth;
		tableSt = event.clientX - MOUSTSTART_X + objTableWidth;

		resizedColumn = true;

		/* check for minimum width */
		if (thSt >= 10) {
			objTh.style.width    = thSt + "px";
		}

		if ((browser == 'IE') && (document.selection)) {
			document.selection.empty();
		}else if (window.getSelection) {
			window.getSelection().removeAllRanges();
		}
	}else if (objDiv) {
		var divSt   = event.clientX - MOUSTSTART_X + objDivWidth;

		resizedDiv = true;

		/* check for minimum height */
		if (divSt >=30 ) {
			objDiv.style.marginLeft = divSt + "px";

			if (document.getElementById("menu") != null) {
				document.getElementById("menu").style.width         = parseInt(divSt - 5) + "px";
				document.getElementById("menu").style.marginLeft    = "0px";
				document.getElementById("content").style.width      = parseInt(clWidth - divSt - 20) + "px";
				document.getElementById("content").style.left       = parseInt(divSt + 2) + "px";
			}else{
				document.getElementById("graph_tree").style.width         = parseInt(divSt - 5) + "px";
				document.getElementById("graph_tree").style.marginLeft    = "0px";
				document.getElementById("graph_tree_content").style.width = parseInt(clWidth - divSt - 20) + "px";
				document.getElementById("graph_tree_content").style.left  = parseInt(divSt + 2) + "px";
			}

			vSplitterClosed = false;
		}else{
			vSplitterClosed = true;

			if (document.getElementById("menu") != null) {
				document.getElementById("vsplitter").style.marginLeft = "0px";
				document.getElementById("menu").style.width           = "0px";
				document.getElementById("menu").style.marginLeft      = "-200px";
				document.getElementById("content").style.left         = "2px";
				document.getElementById("content").style.width        = parseInt(clWidth + 200) + "px"
			}else{
				document.getElementById("vsplitter").style.marginLeft     = "0px";
				document.getElementById("graph_tree").style.width         = "0px";
				document.getElementById("graph_tree").style.marginLeft    = "-200px";
				document.getElementById("graph_tree_content").style.width = parseInt(clWidth + 200) + "px"
				document.getElementById("graph_tree_content").style.left  = "2px";
			}
		}

		if ((browser == 'IE') && (document.selection)) {
			document.selection.empty();
		}else if (window.getSelection) {
			window.getSelection().removeAllRanges();
		}
	}
}

function MouseUp(event) {
	if (!event) event = window.event;

	if (objTh) {
		if ((browser == 'IE') && (document.selection)) {
			document.selection.empty();
		} else if(window.getSelection) {
			window.getSelection().removeAllRanges();
		}

		objTh = null;
	} else if (objDiv) {
		if ((browser == 'IE') && (document.selection)) {
			document.selection.empty();
		}else if (window.getSelection) {
			window.getSelection().removeAllRanges();
		}

		objDiv = null;
	}
}

/* page load functions */
function setFocus() {
	inputs = document.getElementsByTagName("input");
	found  = false;
	hfound = false;
	x      = 0;

	while (true) {
		if (x == 0) {
			for (var i=0; i < inputs.length; i++) {
				switch (inputs[i].type) {
				case "text":
					inputs[i].focus();
					found = true;

					break;
				}

				if (found) {
					break;
				}
			}
		}else{
			for (var i=0; i < inputs.length; i++) {
				switch (inputs[i].type) {
				case "image":
				case "password":
				case "file":
				case "button":
					inputs[i].focus();
					found = true;

					break;
				case "hidden":
					hid_count = i;
					hfound    = true;

					break;
				default:
				}

				if (found) {
					break;
				}
			}
		}

		x++;
		if (x > 1 || found) {
			break;
		}
	}

	if ((!found) && (hfound)) {
		inputs[hid_count].focus();
	}
}

function vSplitterEm() {
	document.getElementById("vsplitter_toggle").style.backgroundColor = 'yellow';
}

function vSplitterUnEm() {
	document.getElementById("vsplitter_toggle").style.backgroundColor = 'white';
}

function vSplitterPos() {
	var divSt   = parseInt(readCookieElement("menu", "vsplitter_last"), 10);

	/* let's see how wide the page is */
	var clWidth = document.getElementById("wrapper").clientWidth;

	vSplitterClosed = parseInt(readCookieElement("menu", "vsplitter_status"), 10);

	if (!divSt) {
		divSt = 165;
	}

	menuWidth  = divSt - 5;
	marginLeft = divSt + 2;

	if (document.getElementById("vsplitter")) {
		if (vSplitterClosed == 1) {
			if (document.getElementById("menu") != null) {
				document.getElementById("vsplitter").style.marginLeft = "0px";
				document.getElementById("menu").style.width           = "0px";
				document.getElementById("menu").style.marginLeft      = "-200px";
				document.getElementById("content").style.left         = "2px";
				document.getElementById("content").style.width        = parseInt(clWidth - 20) + "px"
			}else{
				document.getElementById("vsplitter").style.marginLeft     = "0px";
				document.getElementById("graph_tree").style.width         = "0px";
				document.getElementById("graph_tree").style.marginLeft    = "-200px";
				document.getElementById("graph_tree_content").style.left  = "2px";
				document.getElementById("graph_tree_content").style.width = parseInt(clWidth - 20) + "px"
			}
		}else{
			if (document.getElementById("menu") != null) {
				document.getElementById("vsplitter").style.marginLeft = divSt      + "px";
				document.getElementById("menu").style.width           = menuWidth  + "px";
				document.getElementById("menu").style.marginLeft      = "0px";
				document.getElementById("content").style.left         = marginLeft + "px";
				document.getElementById("content").style.width        = parseInt(clWidth - divSt - 20) + "px"
			}else{
				document.getElementById("vsplitter").style.marginLeft     = divSt      + "px";
				document.getElementById("graph_tree").style.width         = menuWidth  + "px";
				document.getElementById("graph_tree").style.marginLeft    = "0px";
				document.getElementById("graph_tree_content").style.left  = marginLeft + "px";
				document.getElementById("graph_tree_content").style.width = parseInt(clWidth - divSt - 20) + "px"
			}
		}
	}

	if (document.getElementById('vsplitter_toggle')) {
		if (document.getElementById('content')) {
			vertical_pos = parseInt(document.getElementById('content').clientHeight) / 2;

			document.getElementById('vsplitter_toggle').style.marginTop = vertical_pos + "px";
		}else if (document.getElementById('graph_tree_content')) {
			vertical_pos = parseInt(document.getElementById('graph_tree_content').clientHeight) / 2;

			document.getElementById('vsplitter_toggle').style.marginTop = vertical_pos + "px";
		}
	}
}

function pageResize() {
	/* initialize the page splitter as required */
	vSplitterPos();

	/* fix browser quirks */
	fixBrowserQuirks();

	/* size the content divs */
	sizeContentDivs();
}

function pageInitialize() {
	/* detect the browser type */
	detectBrowser();

	/* initialize mouse functions */
	document.onmousedown = MouseDown;
	document.onmousemove = MouseMove;
	document.onmouseup   = MouseUp;

	/* initialize the page splitter as required */
	vSplitterPos();

	/* set document focus */
	setFocus();

	/* fix browser quirks */
	fixBrowserQuirks();

	/* run page onLoad functions */
	runOnLoadFunctions();

	/* size the content divs */
	sizeContentDivs();

	/* restore the page visibility */
	transitionPage();
}

function sizeContentDivs() {
	var top    = document.getElementById("wrapper").offsetTop;
	var bottom = document.getElementById("wrapper").clientHeight;

	if (document.getElementById("content")) {
		document.getElementById("content").style.height = parseInt(bottom-top) + "px";
	}else if(document.getElementById("graph_tree_content")) {
		document.getElementById("graph_tree_content").style.height = parseInt(bottom-top) + "px";
	}else{
		document.getElementById("graph_content").style.height = parseInt(bottom-top) + "px";
	}
}

function transitionPage() {
	if (browser != "IE") {
		if (document.getElementById("graph_tree")) {
			document.getElementById("graph_tree").style.opacity         = 1;
			document.getElementById("graph_tree_content").style.opacity = 0;
			document.getElementById("wrapper").style.opacity            = 1;
			transition = setInterval(function() { fadeIn(document.getElementById("graph_tree_content")) }, 25);
		}else if (document.getElementById("graph_content")) {
			document.getElementById("graph_content").style.opacity      = 0;
			document.getElementById("wrapper").style.opacity            = 1;
			transition = setInterval(function() { fadeIn(document.getElementById("graph_content")) }, 25);
		}else {
			document.getElementById("menu").style.opacity    = 1;
			document.getElementById("content").style.opacity = 0;
			document.getElementById("wrapper").style.opacity = 1;
			transition = setInterval(function() { fadeIn(document.getElementById("content")) }, 25);
		}
	}
}

function fadeIn(object) {
	if (browser != "IE") {
		newEM = Number(object.style.opacity);

		if ((newEM + 0.2) > 1) {
			newEM = 1;
		}else{
			newEM = newEM + 0.4;
		}

		object.style.opacity = newEM;

		if (newEM >= 1) {
			clearInterval(transition);
		}
	}
}

function registerOnLoadFunction(page, function_name) {
	windowOnLoadReg[windowOnLoadCt] = page + ":" + function_name;
	windowOnLoadCt++;
}

function runOnLoadFunctions() {
	myPage = getBaseName();

	for (i = 0; i < windowOnLoadCt; i++) {
		valArray = windowOnLoadReg[i].split(":");

		if (myPage == valArray[0]) {
			eval(valArray[1]);
		}
	}
}

function fixBrowserQuirks() {
	window_height = document.getElementById("wrapper").clientHeight;

	if (browser == "IE") {
		if (document.getElementById("content") != null) {
			myDiv = document.getElementById("content");
		}else if (document.getElementById("graph_tree")) {
			myDiv = document.getElementById("graph_tree_content");
		}else if (document.getElementById("graph_content")) {
			myDiv = document.getElementById("graph_content");
		}

		if (myDiv.scrollHeight > window_height) {
			myDiv.style.paddingRight = "30px";
			myDiv.style.overflowX   = "hidden";
		}
	}else if (browser == "FF") {
		if (document.getElementById("content") != null) {
			myDiv = document.getElementById("content");
		}else if (document.getElementById("graph_tree")) {
			myDiv = document.getElementById("graph_tree_content");
		}else if (document.getElementById("graph_content")) {
			myDiv = document.getElementById("graph_content");
		}

		if (myDiv.scrollHeight <= window_height) {
			myDiv.style.paddingRight = "10px";
			myDiv.style.overflowX   = "hidden";
		}
	}
}

function detectBrowser() {
	if (navigator.userAgent.indexOf('MSIE') >= 0) {
		browser = "IE";
	}else if (navigator.userAgent.indexOf('Mozilla') >= 0) {
		browser = "FF";
	}else if (navigator.userAgent.indexOf('Opera') >= 0) {
		browser = "Opera";
	}else{
		browser = "Other";
	}
}

/* Cookie Functions */
function createCookie(name, value, days) {
	if (!creatingCookie) {
		creatingCookie = true;

		if (days) {
			var date    = new Date();
			date.setTime(date.getTime() + (days*24*60*60*1000));
			var expires = "; expires=" + date.toGMTString();
		} else {
			var expires = "";
		}

		document.cookie  = name + "=" + value + expires + "; path=/";

		creatingCookie = false;
	}
}

function readCookie(name) {
	var nameEQ = name + "=";

	var ca     = document.cookie.split(';');

	for (var i=0; i < ca.length; i++) {
		var c = ca[i];

		while (c.charAt(0)==' ') {
			c = c.substring(1, c.length);
		}

		if (c.indexOf(nameEQ) == 0) {
			return c.substring(nameEQ.length, c.length);
		}
	}

	return null;
}

function eraseCookie(name) {
	createCookie(name, "", -1);
}

/* cookie container functions */
function readCookieElement(name, element) {
	var elements       = readCookie(name);
	var search_for     = element + "@@";
	var return_value   = null;

	if (elements) {
		var start_location = elements.indexOf(search_for);

		if (start_location >= 0) {
			end_location = elements.indexOf("!", start_location);

			if (end_location > 0) {
				return_value = elements.substring(start_location + search_for.length, end_location);
			}else{
				return_value = elements.substring(start_location + search_for.length);
			}
		}
	}

	return return_value;
}

function createCookieElement(name, element, value) {
	if (readCookieElement(name, element)) {
		updateCookieElement(name, element, value);
	}else{
		appendCookieElement(name, element, value);
	}
}

function appendCookieElement(name, element, value) {
	elements = readCookie(name);

	if (elements) {
		elements = elements + "!" + element + "@@" + value;
	}else{
		elements = element + "@@" + value;
	}

	createCookie(name, elements);
}

function updateCookieElement(name, element, value) {
	var elements     = readCookie(name);
	var new_elements = "";

	if (elements) {
		start_location = elements.indexOf(element + "@@");

		if (start_location >= 0) {
			new_elements = new_elements + elements.substring(0, start_location);

			if (new_elements.substring(new_elements.length - 1) == "!") {
				new_elements = new_elements.substring(0, new_elements.length - 1);
			}

			remainder = elements.indexOf("!", start_location);

			if (remainder > 0) {
				new_elements = new_elements + elements.substring(remainder);
			}

			if (new_elements.substring(new_elements.length, 1) == "!") {
				new_elements = new_elements + element + "@@" + value;
			}else{
				new_elements = new_elements + "!" + element + "@@" + value;
			}
		}else{
			new_elements = elements + "!" + element + "@@" + value;
		}
	}else{
		new_elements = element + "@@" + value;
	}

	createCookie(name, new_elements);
}
