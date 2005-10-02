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

var _block_row = '';
var _init_drag_drop = new Array();
var _current_action_type = new Array();
var _elm_selected_rows = new Array();

/* display row functions */

function display_row_hover(row) {
	if (document.getElementById(row).className == 'content-row') {
		document.getElementById(row).className = 'content-row-hover';
	}
}

function display_row_clear(row) {
	if (document.getElementById(row).className == 'content-row-hover') {
		document.getElementById(row).className = 'content-row';
	}
}

function display_row_select(box_id, parent_form, row, checkbox) {
	if (_block_row == row) {
		_block_row = '';
	}else{
		if (document.getElementById(row).className == 'content-row-select') {
			document.getElementById(row).className = 'content-row';
			document.getElementById(checkbox).checked = false;
		}else{
			document.getElementById(row).className = 'content-row-select';
			document.getElementById(checkbox).checked = true;
		}

		/* is the actions box currently being displayed? */
		if ((document.getElementById('box-' + box_id + '-action-area-frame').style.visibility == 'visible') && (_current_action_type[box_id])) {
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
}

function display_row_block(id) {
	_block_row = id;
}

/* action bar functions */

function action_bar_button_mouseover(object_name) {
	document.getElementById(object_name).className = 'action-bar-button-hover';
}

function action_bar_button_mouseout(object_name) {
	document.getElementById(object_name).className = 'action-bar-button-out';
}

function action_bar_menu_mouseover(object_name) {
	document.getElementById(object_name).className = 'action-bar-menu-hover';
}

function action_bar_menu_mouseout(object_name) {
	document.getElementById(object_name).className = 'action-bar-menu-out';
}

function action_bar_button_menu_mouseover(box_id) {
	if (document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility == 'hidden') {
		action_bar_button_mouseover('box-' + box_id + '-button-menu');
	}
}

function action_bar_button_menu_mouseout(box_id) {
	if (document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility == 'hidden') {
		action_bar_button_mouseout('box-' + box_id + '-button-menu');
	}
}

function action_bar_button_menu_click(box_id) {
	action_bar_menu = document.getElementById('box-' + box_id + '-action-bar-menu');
	action_bar_button_container = document.getElementById('box-' + box_id + '-button-menu-container');
	action_bar_button_menu = document.getElementById('box-' + box_id + '-button-menu');

	if (action_bar_menu.style.visibility == 'visible') {
		action_bar_menu.style.visibility = 'hidden';
		action_bar_button_menu.className = 'action-bar-button-hover';
		action_bar_button_container.style.backgroundColor = '#ffffff';
	}else{
		action_bar_menu.style.visibility = 'visible';
		action_bar_button_menu.className = 'action-bar-button-click';
		action_bar_button_container.style.backgroundColor = '#e0e0ff';

	}
}

/* action area functions */

function action_area_show(box_id, parent_form, type) {
	/* parent div container for all action box items */
	parent_div = document.getElementById('box-' + box_id + '-action-area-items');

	/* make sure that the drag and drop code is initialized */
	//if (!_init_drag_drop[box_id]) {
	//	_init_drag_drop[box_id] = true;

		//SET_DHTML("box-" + box_id + "-action-area-frame", "box-" + box_id + "-action-area-header"+DRAG, "box-" + box_id + "-action-area-items");
		//SET_DHTML("box-1-action-area-frame", "box-1-action-area-header"+DRAG, "box-1-action-area-items");

		/* force position because of ie weirdness */
		//dd.elements["box-" + box_id + "-action-area-frame"].moveTo((get_browser_width() / 2) - 200, '150');
	//}

	/* clear the box */
	parent_div.innerHTML = '';

	action_area_handle_type(box_id, type, parent_div, parent_form);

	/* hide the action bar menu */
	document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility = 'hidden';
	document.getElementById('box-' + box_id + '-button-menu-container').style.backgroundColor = '#ffffff';
	document.getElementById('box-' + box_id + '-button-menu').className = 'action-bar-button-out';

	/* re-adjust div heights and display it */
	document.getElementById('box-' + box_id + '-action-area-items').style.height = 'auto';
	document.getElementById('box-' + box_id + '-action-area-menu').style.height = 'auto';

	/* ie requires this because of the drag & drop control */
	if (get_browser_type() == "ie") {
		document.getElementById('box-' + box_id + '-action-area-menu').style.width = '386';
		document.getElementById('box-' + box_id + '-action-area-header').style.width = '386';
		document.getElementById('box-' + box_id + '-action-area-items').style.width = '386';
	}

	/* show the area box */
	document.getElementById('box-' + box_id + '-action-area-frame').style.visibility = 'visible';

	/* keep a cache of the active actions box type */
	_current_action_type[box_id] = type;
}

function action_area_hide(box_id) {
	document.getElementById('box-' + box_id + '-action-area-frame').style.visibility = 'hidden';
}

function action_area_generate_selected_rows(box_id) {
	if (!_elm_selected_rows[box_id]) {
		_elm_selected_rows[box_id] = document.createElement('p');
	}

	return _elm_selected_rows[box_id];
}

function action_area_update_selected_rows(box_id, parent_form) {
	if (_elm_selected_rows[box_id]) {
		_elm_selected_rows[box_id].innerHTML = '';

		for (var i = 0; i < parent_form.elements.length; i++) {
			if ((parent_form.elements[i].name.substr(0, box_id.length + 8) == 'box-' + box_id + '-chk') && (parent_form.elements[i].checked == true)) {
				_elm_list_item = document.createElement('li');
				_txt_list_text = document.createTextNode(document.getElementById('box-' + box_id + '-text' + parent_form.elements[i].name.substr(box_id.length + 8)).innerHTML);

				_elm_list_item.appendChild(_txt_list_text);
				_elm_selected_rows[box_id].appendChild(_elm_list_item);
			}
		}
	}
}

function action_area_update_submit_caption(box_id, value) {
	document.getElementById('box-' + box_id + '-action-area-button').value = value;
}

function action_area_update_header_caption(box_id, value) {
	document.getElementById('box-' + box_id + '-action-area-header-caption').innerHTML = value;
}

function action_area_generate_input(type, name, value) {
	_elm_object = document.createElement('input');
	_elm_object.type = type;
	_elm_object.name = name;
	_elm_object.value = value;

	return _elm_object;
}

function action_area_update_input(box_id, parent_form) {
	_elm_form_container = document.getElementById('box-' + box_id + '-action-area-items');

	fields = _elm_form_container.getElementsByTagName('input');

	for (var i=0; i<fields.length; i++) {
		parent_form.appendChild(action_area_generate_input('hidden', fields[i].name, fields[i].value));
	}

	/* store the current action type in a form variable for later access */
	parent_form.appendChild(action_area_generate_input('hidden', 'box-' + box_id + '-action-area-type', _current_action_type[box_id]));

	/* make a list of all selected rows inside of this box */
	var selected_rows = '';
	for (var i = 0; i < parent_form.elements.length; i++) {
		if ((parent_form.elements[i].name.substr(0, box_id.length + 8) == 'box-' + box_id + '-chk') && (parent_form.elements[i].checked == true)) {
			selected_rows += parent_form.elements[i].name.substr(box_id.length + 9) + ':';
		}
	}

	/* add a colon delimited list of selected rows for easy parsing */
	parent_form.appendChild(action_area_generate_input('hidden', 'box-' + box_id + '-action-area-selected-rows', selected_rows.substr(0, selected_rows.length - 1)));

	/* make sure the POST is registered a 'save' action to Cacti */
	if (document.getElementsByName('action').length == 0) {
		parent_form.appendChild(action_area_generate_input('hidden', 'action', 'save'));
	}else{
		document.getElementById('action').value = 'save';
	}
}
