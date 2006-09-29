/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

function navigation_select(name) {
	var header_container = document.getElementById('tabs');

	for (var i = 0; i < header_container.childNodes[1].childNodes.length; i++) {
		if (header_container.childNodes[1].childNodes[i].id) {
			if (header_container.childNodes[1].childNodes[i].id == 'tab_' + name) {
				header_container.childNodes[1].childNodes[i].className = 'selected';
			}else{
				header_container.childNodes[1].childNodes[i].className = 'notselected';
			}
		}
	}

	var navigation_container = document.getElementById('navigation');

	for (var i = 0; i < navigation_container.childNodes.length; i++) {
		if (navigation_container.childNodes[i].id) {
			if (navigation_container.childNodes[i].id == 'nav_' + name) {
				navigation_container.childNodes[i].style.display = 'block';
			}else{
				navigation_container.childNodes[i].style.display = 'none';
			}
		}
	}
}

sidebar_state = 'close';
function navigation_sidebar_toggle() {
	if (sidebar_state == 'open') {
		document.getElementById('page_body').className = 'sidebar_closed';
		document.getElementById('sidebar_content').style.display = 'none';
		sidebar_state = 'closed';
	}else{
		document.getElementById('page_body').className = 'sidebar_open';
		document.getElementById('sidebar_content').style.display = 'block';
		sidebar_state = 'open';
	}
}
