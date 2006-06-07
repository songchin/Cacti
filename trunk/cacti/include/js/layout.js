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

/* functions for the graph templates box on the New Graphs page */

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
						lineid.className = 'jsRowSelect';
					}else{
						lineid.className = 'jsRowUnSelect';
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
			lineid.className = 'jsRowUnavailable';
		}

		chkbx = document.getElementById('cg_' + gt_created_graphs[i]);
		chkbx.style.visibility = 'hidden';
		chkbx.checked = false;

		lineid = document.getElementById('gt_line' + gt_created_graphs[i]);
		lineid.className = 'jsRowUnavailable';
	}
}

function gt_reset_deps(num_columns) {
	var prefix = 'cg_'

	for (var i = 0; i < document.chk.elements.length; i++) {
		if (document.chk.elements[i].name.substr( 0, prefix.length ) == prefix) {
			for (var j = 0; j < num_columns; j++) {
				lineid = document.getElementById('gt_text' + document.chk.elements[i].name.substr(prefix.length) + '_' + j);
				lineid.className = 'jsRowUnSelect';
			}

			chkbx = document.getElementById('cg_' + document.chk.elements[i].name.substr(prefix.length));
			chkbx.style.visibility = 'visible';
		}
	}
}

/* functions for the data queries box(es) on the New Graphs page */

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
					lineid.className = 'jsRowSelect';
				}else{
					lineid.className = 'jsRowUnSelect';
				}
			}
		}
	}
}

function dq_select_line(snmp_query_id, snmp_index, update) {
	if (dq_is_disabled(snmp_query_id, snmp_index)) { return; }

	if (document.getElementById) {
		msgid = document.getElementById('sg_' + snmp_query_id + '_' + snmp_index);
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
			if (lineid) {
				lineid.className = 'jsRowUnavailable';
			}
		}

		chkbx = document.getElementById('sg_' + snmp_query_id + '_' + created_graphs[snmp_query_graph_id][i]);
		if ( chkbx ) {
			chkbx.style.visibility = 'hidden';
			chkbx.checked = false;
		}

		lineid = document.getElementById('line' + snmp_query_id + '_' + created_graphs[snmp_query_graph_id][i]);
		if (lineid) {
			lineid.className = 'jsRowUnavailable';
		}
	}
}

function dq_reset_deps(snmp_query_id, num_columns) {
	var prefix = 'sg_' + snmp_query_id + '_'

	for (var i = 0; i < document.chk.elements.length; i++) {
		if (document.chk.elements[i].name.substr( 0, prefix.length ) == prefix) {
			for (var j = 0; j < num_columns; j++) {
				lineid = document.getElementById('text' + snmp_query_id + '_' + document.chk.elements[i].name.substr(prefix.length) + '_' + j);
				lineid.className = 'jsRowUnSelect';
			}

			chkbx = document.getElementById('sg_' + snmp_query_id + '_' + document.chk.elements[i].name.substr(prefix.length));
			chkbx.style.visibility = 'visible';
		}
	}
}

/* generic selection functions */

function graph_item_rows_selection(checkbox_state) {
	for (var i = 0; i < item_rows.length; i++) {
		row_chk = document.getElementById('row_chk_' + item_rows[i]);

		row_chk.checked = checkbox_state;

		graph_item_row_selection(item_rows[i]);
	}
}

function graph_item_row_selection(row_id) {
	row_chk = document.getElementById('row_chk_' + row_id);

	for (var i = 0; i < item_row_list[row_id].length; i++) {
		chk = document.getElementById('chk_' + item_row_list[row_id][i]);

		if (row_chk.checked == true) {
			chk.checked = true;
			//chk.disabled = true;
		}else{

			chk.checked = false;
			//chk.disabled = false;
		}
	}
}

function graph_item_row_visibility(row_id) {
	image = document.getElementById('img_' + row_id);

	if (image.src.indexOf('hide.gif') > 0) {
		image.src = image.src.replace('hide.gif', 'show.gif');
	}else{
		image.src = image.src.replace('show.gif', 'hide.gif');
	}

	for (var i = 0; i < item_row_list[row_id].length; i++) {
		row = document.getElementById('tr_' + item_row_list[row_id][i]);

		if (row.style.display == 'none') {
			row.style.display = 'table-row';
		}else{
			row.style.display = 'none';
		}
	}
}

/* miscellaneous form-related functions */

function SelectAllX(prefix, checkbox_state) {
	for (var i = 0; i < document.form_graph_template.elements.length; i++) {
		if ((document.form_graph_template.elements[i].name.substr(0, prefix.length) == prefix) && (document.form_graph_template.elements[i].style.visibility != 'hidden')) {
			document.form_graph_template.elements[i].checked = checkbox_state;
		}
	}
}

function SelectAll(prefix, checkbox_state) {
	for (var i = 0; i < document.chk.elements.length; i++) {
		if ((document.chk.elements[i].name.substr(0, prefix.length) == prefix) && (document.chk.elements[i].style.visibility != 'hidden')) {
			document.chk.elements[i].checked = checkbox_state;
		}
	}
}

function submit_redirect(form_index, redirect_url, field_value) {
	redirect = document.getElementById('cacti_js_dropdown_redirect_x');

	redirect.value = redirect_url.replace("|dropdown_value|", field_value);
	redirect.name = 'cacti_js_dropdown_redirect';

	document.forms[form_index].submit();
}

function template_checkbox_status(field_name, t_field_name) {
	var field_method = '';

	if ((field_method == 'drop_multi') || (field_method == 'drop_multi_rra')) {
		if (document.getElementById(t_field_name).checked == true) {
			document.getElementById(field_name + '[]').disabled = true;
		}else{
			document.getElementById(field_name + '[]').disabled = false;
		}
	}else{
		if (document.getElementById(t_field_name).checked == true) {
			document.getElementById(field_name).disabled = true;
		}else{
			document.getElementById(field_name).disabled = false;
		}
	}
}

function set_data_template_override_device_field(field_name) {
	t_field = document.getElementById('t_' + field_name);
	o_field = document.getElementById('o_' + field_name);
	field = document.getElementById(field_name);
	chk_caption = document.getElementById('chk_caption_t_' + field_name);

	if (o_field.checked == true) {
		t_field.disabled = false;
		chk_caption.className = 'txtEnabledText';

		if (t_field.checked == true) {
			field.disabled = true;
		}else{
			field.disabled = false;
		}
	}else{
		t_field.disabled = true;
		field.disabled = true;
		chk_caption.className = 'txtDisabledText';
	}
}

function get_radio_value(radioObj) {
	if (!radioObj) {
		return "";
	}

	var radioLength = radioObj.length;
	if (radioLength == undefined) {
		if (radioObj.checked) {
			return radioObj.value;
		}else{
			return "";
		}
	}

	for (var i = 0; i < radioLength; i++) {
		if (radioObj[i].checked) {
			return radioObj[i].value;
		}
	}

	return "";
}

function toggle_visibility(object_name) {
	if (document.getElementById(object_name).style.visibility == 'visible') {
		document.getElementById(object_name).style.visibility = 'hidden';
	}else{
		document.getElementById(object_name).style.visibility = 'visible';
	}
}

function loadXMLFile(url) {
	if (window.XMLHttpRequest) { // code for Mozilla, Safari, etc
		xmlhttp=new XMLHttpRequest();
		xmlhttp.onreadystatechange=xmlhttpReady;
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
	}else if (window.ActiveXObject) { //IE
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

		if (xmlhttp) {
			xmlhttp.onreadystatechange=xmlhttpReady;
			xmlhttp.open("GET",url,true);
			xmlhttp.send();
		}
	}
}

function xmlhttpReady() {
	if (xmlhttp.readyState==4) {
		if (xmlhttp.status==200) {
			alert("got: " + xmlhttp.responseText);
		}
	}
}

if ((get_browser_type() == "ie") && (window.attachEvent)) {
	window.attachEvent("onload", alphaBackgrounds);
}

function alphaBackgrounds(){
	var rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
	var itsAllGood = (rslt != null && Number(rslt[1]) >= 5.5);
	for (i=0; i<document.all.length; i++){
		var bg = document.all[i].currentStyle.backgroundImage;
		if (itsAllGood && bg){
			if (bg.match(/\.png/i) != null){
				var mypng = bg.substring(5,bg.length-2);
				document.all[i].style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+mypng+"', sizingMethod='scale')";
				document.all[i].style.backgroundImage = "url('themes/classic/images/transparent_pixel.gif')";
			}
		}
	}
}

function get_browser_type() {
	if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent) {
		return "ie";
	}else{
		return "ns";
	}
}

function get_browser_width() {
	if (get_browser_type() == "ie") {
		return document.body.offsetWidth;
	}else{
		return window.innerWidth;
	}
}

function get_browser_height() {
	if (get_browser_type() == "ie") {
		return document.body.offsetHeight;
	}else{
		return window.innerHeight;
	}
}

function strip_html_tags(string) {
	return string.replace(/(<([^>]+)>)/ig, "");
}
