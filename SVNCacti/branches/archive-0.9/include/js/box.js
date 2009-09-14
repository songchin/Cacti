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

/* box state variables */
var _block_row = '';
var _init_drag_drop = new Array();
var _current_action_type = new Array();
var _elm_selected_rows = new Array();

/* ======== display row functions ======== */

/* display_row_hover - called when a user hovers their mouse over a row
   @arg row - (object) the object representing the row that the user's mouse is
	hovering over */
function display_row_hover(row) {
	css_add_class(document.getElementById(row), 'hover');
}

/* display_row_clear - called when a user moves their mouse out of a row
   @arg row - (object) the object representing the row that the user's mouse moved
	from */
function display_row_clear(row) {
	css_remove_class(document.getElementById(row), 'hover');
}

/* display_row_select_all - called when a user clicks the "select all" checkbox
   @arg box_id - (string) the unique identifier for the container box
   @arg parent_form - (object) a reference to the container form object */
function display_row_select_all(box_id, parent_form) {
	checkbox_state = document.getElementById('box-' + box_id + '-allchk').checked;

	for (var i = 0; i < parent_form.elements.length; i++) {
		if (parent_form.elements[i].name.substr(0, box_id.length + 8) == 'box-' + box_id + '-chk') {
			if (checkbox_state == true) {
				css_add_class(document.getElementById('box-' + box_id + '-row' + parent_form.elements[i].name.substr(box_id.length + 8)), 'selected');
				document.getElementById('box-' + box_id + '-chk-' + parent_form.elements[i].name.substr(box_id.length + 9)).checked = true;
			}else{
				css_remove_class(document.getElementById('box-' + box_id + '-row' + parent_form.elements[i].name.substr(box_id.length + 8)), 'selected');
				document.getElementById('box-' + box_id + '-chk-' + parent_form.elements[i].name.substr(box_id.length + 9)).checked = false;
			}
		}
	}
}

/* display_row_select - called when a user clicks a row with the mouse
   @arg box_id - (string) the unique identifier for the container box
   @arg parent_form - (object) a reference to the container form object
   @arg row - (object) the object representing the row that has been clicked
   @arg checkbox (string) the name of the checkbox field corresponding to the row */
function display_row_select(box_id, parent_form, row, checkbox) {
	if (_block_row == row) {
		_block_row = '';
	}else{
		if (css_has_class(document.getElementById(row), 'selected')) {
			css_remove_class(document.getElementById(row), 'selected');
			document.getElementById(checkbox).checked = false;
		}else{
			css_add_class(document.getElementById(row), 'selected');
			document.getElementById(checkbox).checked = true;
		}
	}
}

/* display_row_block - blocks all display_row_select() operations. this is often used
	when the user performs a mousedown on a link inside of a row so a row click
	event is not registered
   @arg id - (string) the name of the row object to block mousedown events for */
function display_row_block(id) {
	_block_row = id;
}

/* ======== action bar functions ======== */

/* action_bar_button_mouseover - called when a user hovers their mouse over a button
	in the actions toolbar
   @arg object_name - (object) the object representing the button that the user's mouse
	is hovering over */
function action_bar_button_mouseover(object_name) {
	document.getElementById(object_name).className = 'button_mouseover';
}

/* action_bar_button_mouseout - called when a user moves their mouse out of an actions
	toolbar button
   @arg object_name - (object) the object representing the button that the user's mouse
	moved from */
function action_bar_button_mouseout(object_name) {
	document.getElementById(object_name).className = 'button_mouseout';
}

/* action_bar_menu_mouseover - called when a user hovers their mouse over a row in the
	actions dropdown menu
   @arg object_name - (object) the object representing the row that the user's mouse
	is hovering over */
function action_bar_menu_mouseover(object_name) {
	if (document.getElementById(object_name).className.match('item_spacer')) {
		document.getElementById(object_name).className = 'mouseover item_spacer';
	}else{
		document.getElementById(object_name).className = 'mouseover';
	}
}

/* action_bar_menu_mouseout - called when a user moves their mouse out of a row in the
	actions dropdown menu
   @arg object_name - (object) the object representing the row that the user's mouse
	moved from */
function action_bar_menu_mouseout(object_name) {
	if (document.getElementById(object_name).className.match('item_spacer')) {
		document.getElementById(object_name).className = 'mouseout item_spacer';
	}else{
		document.getElementById(object_name).className = 'mouseout';
	}
}

/* action_bar_button_menu_mouseover - called when a user hovers their mouse over the
	menu dropdown button in the actions toolbar. this is used to prevent events from
	firing on this button when the actions area box is displayed
   @arg box_id - (string) the unique identifier for the container box */
function action_bar_button_menu_mouseover(box_id) {
	if (document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility != 'visible') {
		action_bar_button_mouseover('box-' + box_id + '-button-menu');
	}
}

/* action_bar_button_menu_mouseout - called when a user moves their mouse out of the
	menu dropdown button in the actions toolbar. this is used to prevent events from
	firing on this button when the actions area box is displayed
   @arg box_id - (string) the unique identifier for the container box */
function action_bar_button_menu_mouseout(box_id) {
	if (document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility != 'visible') {
		action_bar_button_mouseout('box-' + box_id + '-button-menu');
	}
}

/* action_bar_button_menu_click - called when a user clicks the menu dropdown
	button in the actions toolbar. this takes care of hiding or showing the actions menu
	dropdown as well as changing the appearance of the button
   @arg box_id - (string) the unique identifier for the container box */
function action_bar_button_menu_click(box_id) {
	action_bar_menu = document.getElementById('box-' + box_id + '-action-bar-menu');
	action_bar_button_container = document.getElementById('box-' + box_id + '-button-menu-container');
	action_bar_button_menu = document.getElementById('box-' + box_id + '-button-menu');

	if (action_bar_menu.style.visibility == 'visible') {
		action_bar_menu.style.visibility = 'hidden';
		action_bar_button_menu.className = 'button_mouseover';
		action_bar_button_container.style.backgroundColor = '#ffffff';
	}else{
		action_bar_menu.style.visibility = 'visible';
		action_bar_button_menu.className = 'button_mouseclick';
		action_bar_button_container.style.backgroundColor = '#e0e0ff';
	}
}

/* ======== action area box functions ======== */

function action_area_box_create(id) {
	_elm_div_backdrop = document.getElementById('backdrop');
	_elm_div_backdrop.style.height = get_browser_height() + "px";
	_elm_div_backdrop.style.width = '100%';

	_elm_div_container = document.createElement('div');
	_elm_div_container.id = 'box-' + id + '-action-area';
	_elm_div_container.className = 'action_box';

	_elm_div_header = document.createElement('div');
	_elm_div_header.id = 'box-' + id + '-action-area-header';
	_elm_div_header.className = 'header';
	_elm_div_container.appendChild(_elm_div_header);

	_elm_div_body = document.createElement('div');
	_elm_div_body.id = 'box-' + id + '-action-area-body';
	_elm_div_body.className = 'body';
	_elm_div_container.appendChild(_elm_div_body);

	_elm_div_footer = document.createElement('div');
	_elm_div_footer.id = 'box-' + id + '-action-area-footer';
	_elm_div_footer.className = 'footer';
	_elm_div_container.appendChild(_elm_div_footer);

	_elm_div_button_close = action_area_generate_input('reset', 'box-' + id + '-action-area-button-close', 'Close');
	_elm_div_button_close.id = 'box-' + id + '-action-area-button-close';
	_elm_div_button_close.onclick = new Function('e', 'action_area_box_hide("' + id + '")');
	_elm_div_footer.appendChild(_elm_div_button_close);

	_elm_div_button_submit = action_area_generate_input('button', 'box-' + id + '-action-area-button-submit', 'Submit');
	_elm_div_button_submit.id = 'box-' + id + '-action-area-button-submit';
	_elm_div_button_submit.onclick = new Function('e', 'action_area_update_input("' + id + '",document.forms[0]);document.forms[0].submit()');
	_elm_div_footer.appendChild(_elm_div_button_submit);

	document.getElementById('page_body').appendChild(_elm_div_container);
}

/* action_area_show - called when a user performs a defined action (remove, duplicate, etc).
	this function takes care of rendering objects in the actions are box, hiding the actions
	menu dropdown, and adjusting the dimensions/position of the actions area box
   @arg box_id - (string) the unique identifier for the container box
   @arg parent_form - (object) a reference to the container form object
   @arg type - (string) the unique identifier for the selected action type */
function action_area_box_show(box_id, parent_form, type, width) {

	/* set width */
	if (!width) {
		width = 400;
	}

	/* parent div container for all action box items */
	parent_div = document.getElementById('box-' + box_id + '-action-area-body');

	/* clear the box */
	parent_div.innerHTML = '';

	/* this can be used by the post handler to determine which box submitted the form */
	parent_div.appendChild(action_area_generate_input('hidden', 'action_post', 'box-' + box_id));

	/* hide the action bar menu */
	if (document.getElementById('box-' + box_id + '-action-bar-menu') != null) {
		document.getElementById('box-' + box_id + '-action-bar-menu').style.visibility = 'hidden';
		document.getElementById('box-' + box_id + '-button-menu-container').style.backgroundColor = '#ffffff';
		document.getElementById('box-' + box_id + '-button-menu').className = 'button_mouseout';
	}

	/* show the area box */
	document.getElementById('box-' + box_id + '-action-area').style.top = getScrollY() + 150 + 'px';
	document.getElementById('box-' + box_id + '-action-area').style.left = get_browser_width() / 2 - width / 2 + 'px';
	document.getElementById('box-' + box_id + '-action-area').style.width = width + 'px';
	document.getElementById('box-' + box_id + '-action-area').style.display = 'block';
	document.getElementById('backdrop').style.display = 'block';

	action_area_handle_type(box_id, type, parent_div, parent_form);

	/* keep a cache of the active actions box type */
	_current_action_type[box_id] = type;
}

/* action_bar_button_menu_click - hides the actions area box
   @arg box_id - (string) the unique identifier for the container box */
function action_area_box_hide(box_id) {
	document.getElementById('backdrop').style.display = 'none';
	document.getElementById('box-' + box_id + '-action-area').style.display = 'none';
}

/* action_area_generate_search_field - creates an complete search field container
   @arg field - (object) the object reprenting the HTML form field
   @arg caption - (string) the text to print with the field for the user
   @arg is_first - (boolean) whether this field comes first
   @arg is_last - (boolean) whether this field comes last */
function action_area_generate_search_field(field, caption, is_first, is_last, width) {
	_elm_dt_container = document.createElement('div');

	if (is_first == true) {
		_elm_dt_container.style.paddingTop = '1px';
	}else{
		_elm_dt_container.style.paddingTop = '3px';
	}

	if (! width) {
		width = 400;
	}

	_elm_dt_container.style.paddingBottom = '3px';
	_elm_dt_container.style.width = width;

		/* container for the caption */
		_elm_dt_container_txt = document.createElement('div');
		_elm_dt_container_txt.style.paddingBottom = '5px';
		_elm_dt_container_txt.appendChild(document.createTextNode(caption));

	_elm_dt_container.appendChild(_elm_dt_container_txt);

		/* container for the actual field object */
		_elm_dt_container_fld = document.createElement('div');
		_elm_dt_container_fld.style.paddingLeft = '10px';
		_elm_dt_container_fld.appendChild(field);

	_elm_dt_container.appendChild(_elm_dt_container_fld);

	if (is_last == false) {
		_elm_dt_container.style.borderBottom = '1px solid #f1f1f1';
	}

	return _elm_dt_container;
}

/* action_area_generate_text_field - creates an complete text field container
   @arg field - (string) the text to appear
   @arg caption - (string) the text to print with the field for the user
   @arg is_first - (boolean) whether this field comes first
   @arg is_last - (boolean) whether this field comes last */
function action_area_generate_text_field(field, caption, is_first, is_last, is_split, width) {
	_elm_dt_container = document.createElement('div');

	if (! width) {
		width = 400;
	}

	if (is_first == true) {
		_elm_dt_container.style.paddingTop = '1px';
	}else{
		_elm_dt_container.style.paddingTop = '3px';
	}

	_elm_dt_container.style.paddingBottom = '3px';
	_elm_dt_container.style.width = width;
	if (is_split) {
		/* container for the caption */
		_elm_dt_container_txt = document.createElement('div');
		_elm_dt_container_txt.style.paddingBottom = '5px';
		_elm_dt_container_txt.appendChild(document.createTextNode(caption));
		_elm_dt_container.appendChild(_elm_dt_container_txt);

		/* container for the actual field */
		_elm_dt_container_fld = document.createElement('div');
		_elm_dt_container_fld.style.paddingLeft = '10px';
		_elm_dt_container_fld.appendChild(document.createTextNode(field));
		_elm_dt_container.appendChild(_elm_dt_container_fld);
	}else{
		_elm_dt_container_txt = document.createElement('div');
		_elm_dt_container_txt.style.paddingBottom = '5px';
		_elm_dt_container_txt.appendChild(document.createTextNode(caption + ' ' + field));
		_elm_dt_container.appendChild(_elm_dt_container_txt);
	}

	if (is_last == false) {
		_elm_dt_container.style.borderBottom = '1px solid #f1f1f1';
	}

	return _elm_dt_container;
}

/* action_area_generate_selected_rows - creates the container object that is used to hold
	the selected rows list
   @arg box_id - (string) the unique identifier for the container box */
function action_area_generate_selected_rows(box_id) {
	if (!_elm_selected_rows[box_id]) {
		_elm_selected_rows[box_id] = document.createElement('p');
	}

	return _elm_selected_rows[box_id];
}

/* action_area_generate_selected_rows - creates the container object that is used to hold
	the selected rows list
   @arg box_id - (string) the unique identifier for the container box */
function action_area_generate_break() {
	return document.createElement('div');
}

/* action_area_update_selected_rows - updates the list of selected row by iterating through
	each row in the current box. requires that action_area_generate_selected_rows() has been
	called first to initialize the container object
   @arg box_id - (string) the unique identifier for the container box
   @arg parent_form - (object) a reference to the container form object */
function action_area_update_selected_rows(box_id, parent_form) {
	if (_elm_selected_rows[box_id]) {
		_elm_selected_rows[box_id].innerHTML = '';

		for (var i = 0; i < parent_form.elements.length; i++) {
			if ((parent_form.elements[i].name.substr(0, box_id.length + 8) == 'box-' + box_id + '-chk') && (parent_form.elements[i].checked == true)) {
				_elm_list_item = document.createElement('li');
				_txt_list_text = document.createTextNode(strip_html_tags(document.getElementById('box-' + box_id + '-text' + parent_form.elements[i].name.substr(box_id.length + 8)).innerHTML));

				_elm_list_item.appendChild(_txt_list_text);
				_elm_selected_rows[box_id].appendChild(_elm_list_item);
			}
		}
	}
}

/* action_area_update_submit_caption - updates the caption of the submit button in the actions
	area box
   @arg box_id - (string) the unique identifier for the container box
   @arg value - (string) the caption to set */
function action_area_update_submit_caption(box_id, value) {
	document.getElementById('box-' + box_id + '-action-area-button-submit').value = value;
}

/* action_area_update_header_caption - updates the caption of the box header in the actions
	area box
   @arg box_id - (string) the unique identifier for the container box
   @arg value - (string) the caption to set */
function action_area_update_header_caption(box_id, value) {
	document.getElementById('box-' + box_id + '-action-area-header').innerHTML = value;
}

/* action_area_generate_input - generates a form element for the actions area box
   @arg type - (string) the html input type of the object
   @arg name - (string) the name of the object
   @arg value - (string) the initial value of the object */
function action_area_generate_input(type, name, value) {
	_elm_object = document.createElement('input');
	_elm_object.type = type;
	_elm_object.name = name;
	_elm_object.value = value;

	return _elm_object;
}

/* action_area_generate_select - generates a select form element for the actions area box
   @arg name - (string) the name of the object
   @arg value - (string) the initial value of the object */
function action_area_generate_select(name) {
	_elm_object = document.createElement('select');
	_elm_object.id = name;
	_elm_object.name = name;

	return _elm_object;
}

/* action_area_update_input - called whenever the user clicks on the actions are box submit
	button. takes care of updating the parent form with each input element contained within
	the actions area box
   @arg box_id - (string) the unique identifier for the container box
   @arg parent_form - (object) a reference to the container form object */
function action_area_update_input(box_id, parent_form) {
	_elm_form_container = document.getElementById('box-' + box_id + '-action-area-body');

	fields = _elm_form_container.getElementsByTagName('input');

	for (var i=0; i<fields.length; i++) {
		/* radio buttons deserve special handling since they operate in a group */
		if ((fields[i].type == 'radio') && (fields[i].checked == true)) {
			parent_form.appendChild(action_area_generate_input('hidden', fields[i].name, fields[i].value));
		}else if (fields[i].type != 'radio') {
			parent_form.appendChild(action_area_generate_input('hidden', fields[i].name, fields[i].value));
		}
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


function getScrollY() {
	var scrOfY = 0;
	if( typeof( window.pageYOffset ) == 'number' ) {
		//Netscape compliant
		scrOfY = window.pageYOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		//DOM compliant
		scrOfY = document.body.scrollTop;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		//IE6 standards compliant mode
		scrOfY = document.documentElement.scrollTop;
	}
	return scrOfY;
}
