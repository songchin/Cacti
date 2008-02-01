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
	filter = readCookie("fs_" + id);

	if (filter == "open") {
		if (initialize != null) {
			/* do nothing we want to stay the same */
		}else{
			createCookie("fs_" + id, "closed");
			filter = "closed";
		}
	}else{
		if (initialize != null) {
			if (filter == "closed") {
				/* do nothing we want to stay the same */
			}else{
				createCookie("fs_" + id, "open");
				filter = "open";
			}
		}else{
			createCookie("fs_" + id, "open");
			filter = "open";
		}
	}

	if (filter == "closed") {
		document.getElementById(id).style.display  = "none";
		document.getElementById(id+'_twisty').src = "images/tw_close.gif";
	}else{
		document.getElementById(id).style.display  = "";
		document.getElementById(id+'_twisty').src = "images/tw_open.gif";
	}
}

function changeMenuState(id, initialize) {
	filter = readCookie("menu_" + id);
	object = document.getElementById("ul_"+id);

	if (filter == "open") {
		if (initialize != null) {
			createCookie("menu_" + id, "open");

			/* set the display properly */
			document.getElementById("tw_"+id).src = "images/tw_open.gif";
			object.style.height = object.scrollHeight + "px";
		}else{
			createCookie("menu_" + id, "closed");
			closeMenu(id);
		}
	}else{
		if (initialize != null) {
			if (filter == "closed") {
				createCookie("menu_" + id, "closed");

				/* set the display properly */
				document.getElementById("tw_"+id).src = "images/tw_close.gif";
				object.style.height = "0px";
			}else{
				createCookie("menu_" + id, "open");

				/* set the display properly */
				document.getElementById("tw_"+id).src = "images/tw_open.gif";
				object.style.height = object.scrollHeight + "px";
			}
		}else{
			createCookie("menu_" + id, "open");
			openMenu(id);
		}
	}
}

function closeMenu(id) {
	element = document.getElementById("ul_"+id);

	closeMe = setInterval(function() { moveUp(element) }, 10);

	document.getElementById("tw_"+id).src = "images/tw_close.gif";
}

function openMenu(id) {
	element = document.getElementById("ul_"+id);

	openMe  = setInterval(function() { moveDown(element) }, 10);

	document.getElementById("tw_"+id).src = "images/tw_open.gif";
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

var objTh          = null;
var objDiv         = null;
var overColumn     = false;
var overVSplit     = false;
var iEdgeThreshold = 10;

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

	if(isOnBorderRight(object, event)) {
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
	overVSplit = false;
}

function doDivResize(object, event){
	if(!event) event = window.event;

	if(isOnBorderRight(object, event)) {
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
}

function MouseDown(event) {
	if(!event) event = window.event;

	MOUSTSTART_X = event.clientX;
	MOUSTSTART_Y = event.clientY;

	if (overColumn) {
		if (event.srcElement)objTh = event.srcElement;
		else if(event.target)objTh = event.target;
		else return;

		objTh = getParentNode(objTh,"TH");

		if(objTh == null) return;
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
	if(!event) event = window.event;

	if (objTh) {
		var thSt    = event.clientX - MOUSTSTART_X + objThWidth;
		var tableSt = event.clientX - MOUSTSTART_X + objTableWidth;

		/* check for minimum width */
		if (thSt >= 10){
			objTh.style.width    = thSt + "px";
		}

		if(document.selection) {
			document.selection.empty();
		}else if(window.getSelection) {
			window.getSelection().removeAllRanges();
		}
	} else if (objDiv){
		var divSt   = event.clientX - MOUSTSTART_X + objDivWidth;

		/* check for minimum height */
		if (divSt >=70 ) {
			objDiv.style.marginLeft                             = divSt + "px";
			document.getElementById("menu").style.width         = parseInt(divSt - 5) + "px";
			document.getElementById("content").style.marginLeft = parseInt(divSt + 2) + "px";
		}
		if(document.selection) document.selection.empty();
		else if(window.getSelection)window.getSelection().removeAllRanges();
	}
}

function MouseUp(event) {
	if(!event) event = window.event;
	if(objTh){
		if(document.selection) document.selection.empty();
		else if(window.getSelection)window.getSelection().removeAllRanges();
		objTh = null;
	}
	else if( objDiv ){
		if(document.selection) document.selection.empty();
		else if(window.getSelection)window.getSelection().removeAllRanges();
		objDiv = null;
	}
}

document.onmousedown = MouseDown;
document.onmousemove = MouseMove;
document.onmouseup   = MouseUp;

/* page load functions */
function initializePage() {
	inputs = document.getElementsByTagName("input");
	found  = false;
	hfound = false;

	for (var i=0; i < inputs.length; i++) {
		switch (inputs[i].type) {
		case "image":
		case "text":
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

	if ((!found) && (hfound)) {
		inputs[hid_count].focus();
	}
}

/* Cookie Functions */
function createCookie(name, value, days) {
	if (days) {
		var date    = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	} else {
		var expires = "";
	}

	document.cookie  = name + "=" + value + expires + "; path=/";
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
