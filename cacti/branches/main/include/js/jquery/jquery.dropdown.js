/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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

(function($){  
 $.fn.DropDownMenu = function(options) {

	var defaults = {
		title: '',
		subtitle: '',
		name: 'myName',
		maxHeight: 300,
		width: 150,
		timeout: 500,
		html: '<h6>empty</h6>',
		offsetX: 0,
		offsetY: 15,
		simultaneous: false,
		rel: ''
	};
	
	var timerref 		= null;
	var menu 			= null;
	var menuHeight 		= 0;
	var options 		= $.extend(defaults, options);
	var contentHeight	= 0;
	
	// do nothing if requested menu is still loaded
	if($('#' + options.name).is(":visible")) {
		return;
	}

	// remove all open menus from DOM if they should not stay in front at the same time
	var oldMenus = $(".cacti_dd_menu");
	if(options.simultaneous == false) {
	    oldMenus.css({'overflow-y':'hidden'}).slideUp('200');
	    oldMenus.queue(function () {
			oldMenus.remove();
			oldMenus.dequeue();
		});
	}

	return this.each(function() {  
		obj = $(this);
		newMenu = _init_menu(obj.offset());
		_open_menu(newMenu);
	});
	
	
	function _init_menu(initiator_position){

		// integrate a base frame
		$("<div id='" + options.name + "' style='display: none;' class='cacti_dd_menu'>"
			+ "<div id='" + options.name + "_title' class='title'><h6>" + options.title + "</h6></div>"
			+ "<div id='" + options.name + "_back' class='back'></div>"
			+ "<div id='" + options.name + "_content' class='content'></div>"
			+ "<div id='" + options.name + "_subtitle' class='subtitle'><h6>" + options.subtitle + "</h6></div>"
			+ "<div id='" + options.name + "_html' class='html'></div>"
		+ "</div>").appendTo("body");

		// define a reference to the menu and the different sections
		menu 			= $('#' + options.name);
		menu_head 		= $('#' + options.name + '_title');
		menu_content 	= $('#' + options.name + '_content');
		menu_back 		= $('#' + options.name + '_back');
		menu_subhead 	= $('#' + options.name + '_subtitle');
		menu_html 		= $('#' + options.name + '_html');

		// position menu container
		menu.css({	'left' 			: initiator_position.left + options.offsetX + 'px',	// x-position in relation to the initiator
					'top' 			: initiator_position.top + options.offsetY + 'px'		// y-position in relation to the initiator
				});

		// "_html" holds the raw data
		menu_html.append(options.html);
		i=1;
		menu_html.find("h6:has(div)").each(function() {
			var subMenu = $(this);
			var subMenuID = options.name + '_' + i;
			var subMenuTitle = subMenu.find('a:first').html();
			subMenu.attr('id', subMenuID);	
			subMenu.click( function() {
				 _toggle_subMenu( subMenuID);
			} );
			subMenu.children("div").hide();
			subMenu.find('a:first').html(subMenuTitle + '&nbsp;<img src="' + options.rel + '/images/tw_close.gif" class="icon">');
			i++;
		});

		// "_content" holds the visible menu data
		menu_content.append(options.html);
		
		// hide every submenu and its items
		i=1;
		menu_content.find("h6:has(div)").each(function() {
			var subMenu = $(this);
			var subMenuID = options.name + '_' + i;
			var subMenuTitle = subMenu.find('a:first').html();		
			subMenu.attr('id', subMenuID);	
			subMenu.click( function() {
				 _toggle_subMenu( subMenuID);
			} );
			subMenu.children("div").hide();
			subMenu.find('a:first').html(subMenuTitle + '&nbsp;<img src="' + options.rel + '/images/tw_close.gif" class="icon">');
			i++;
		});
		
		// if necessary show the title, subtitle ...
		if(options.title != '') { menu_head.show(); }
		if(options.subtitle != '') { menu_subhead.show(); }
		
		// make content visible
		menu_content.show();
		
		//reduce height to a minimum for best fit
		menuHeight = (menu.height() > options.maxHeight) ? options.maxHeight : menu.height();

		//IE5/6 does not support css option "min-width", so a workaround is required
		if(menu.width() != options.width) {
			menu.css({'min-width' : options.width + 'px'});
			menu.width(options.width);
		}
		
		
		menu.css({'height':0});
		menu.bind('mouseover', _cancel_timer);
		menu.bind('mouseout', _set_timer);
		return menu;		
	}
	
	
	function _toggle_subMenu(subMenuID){

		if(subMenuID == null) {
		    var content = menu_html;
		    menu_back.empty().hide();
		    menu_content.height(contentHeight);
		}else {
		    var content = menu_html.find('#' + subMenuID).find("div").eq(0);
		    menu_back.show();
		}

		menu_back.empty().append(menu_html.find('#' + subMenuID).find('a:first').html());
		menu_back.find('img').remove();
		menu_back.unbind('click');

		parentID = menu_html.find('#' + subMenuID).parents('h6').attr('id');

		menu_back.click( function() { _toggle_subMenu( parentID); });

		menu_content.empty().append(content.html());

		menu_content.find("h6:has(div)").each(function() {
			var subMenu = $(this)
			var subsubMenuID = subMenu.attr('id');
			subMenu.click( function() {
				_toggle_subMenu( subsubMenuID); 
			} );
			subMenu.children("div").hide();
		});
		
		//re-calculate content height if back-button is hidden
		if(subMenuID != null) {
		    menu_content.height(menu.height() - menu_head.height() - menu_back.height() - menu_subhead.height() - 16);
		}

		//return false to suppress unwanted click events
		return false;
	}
	
	
	function _set_timer(){
		timerref = window.setTimeout( _close_menu, options.timeout);
	}
	
	function _cancel_timer() {  
		if(timerref) {  
			window.clearTimeout(timerref);
			timerref = null;
		}
	}
	
	function _close_menu(){
		menu = $('#' + options.name);
		menu.slideUp();
		menu.queue(function () {
			    menu.remove();
			    menu.dequeue();
			});
	}
	
	function _open_menu(obj){
		//wait until oldMenu is completey closed before opening a new one
		var wait = setInterval(function() {
		    if( !oldMenus.is(":animated") ) {
				clearInterval(wait);
				obj.animate({height: menuHeight}, 600);
		
				//setup contentHeight;
				contentHeight = $('#' + options.name + '_content').height();
				$('#' + options.name + '_content').css({'overflow-y':'auto'});

				obj.find('h6').eq(0).focus();
				_cancel_timer();
		    }
		}, 200);

	}

 };  
})(jQuery); 




$(document).ready(function(){

	// Ajax request for language menu
	$('#menu_languages').click(
		function () {
			var url_path = this.rel;
			$.ajax({
					method: "get",url: url_path + "lib/ajax/get_languages.php",
					beforeSend: function(){$("#loading").fadeIn(0);},
					complete: function(){$("#loading").fadeOut(1000); },
					success: function(html){$('#menu_languages').DropDownMenu({timeout: 500, 
												    name: 'dd_languages', 
												    html: html, 
												    title: 'Languages',
												    rel: url_path
												    });}
				 });
		}
	);
	
	// Ajax request for timezone menu
	$('#menu_timezones').click( 
		function () {
			var url_path = this.rel;
			$.ajax({
					method: "get",url: url_path + "lib/ajax/get_timezones.php",
					beforeSend: function(){$("#loading").fadeIn(0);},
					complete: function(){$("#loading").fadeOut(1000);},
					success: function(html){$('#menu_timezones').DropDownMenu({timeout: 500, 
												    name: 'dd_timezones',
													html: html,
													title: 'Time zones',
													rel: url_path
													});}
			 });
		}
	);


});