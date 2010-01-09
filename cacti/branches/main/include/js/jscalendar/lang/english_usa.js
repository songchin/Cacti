
// Calendar language file
// Lanuage: English
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Sunday",
							"Monday",
							"Tuesday",
							"Wednesday",
							"Thursday",
							"Friday",
							"Saturday",
							"Sunday");

// short day names
Calendar._SDN 	= new Array("Sun",
							"Mon",
							"Tue",
							"Wed",
							"Thu",
							"Fri",
							"Sat",
							"Sun");

// full month names
Calendar._MN 	= new Array("January",
							"February",
							"March",
							"April",
							"May",
							"June",
							"July",
							"August",
							"September",
							"October",
							"November",
							"December");

// short month names
Calendar._SMN 	= new Array("Jan",
							"Feb",
							"Mar",
							"Apr",
							"May",
							"Jun",
							"Jul",
							"Aug",
							"Sep",
							"Oct",
							"Nov",
							"Dec");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "About the calendar";
Calendar._TT["PREV_YEAR"] 		= "Prev. year (hold for menu)";
Calendar._TT["PREV_MONTH"] 		= "Prev. month (hold for menu)";
Calendar._TT["GO_TODAY"] 		= "Go Today";
Calendar._TT["NEXT_MONTH"] 		= "Next month (hold for menu)";
Calendar._TT["NEXT_YEAR"] 		= "Next year (hold for menu)";
Calendar._TT["SEL_DATE"] 		= "Select date";
Calendar._TT["DRAG_TO_MOVE"]	= "Drag to move";
Calendar._TT["PART_TODAY"] 		= " (today)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Close";
Calendar._TT["TODAY"] 			= "Today";
Calendar._TT["TIME_PART"] 		= "(Shift-)Click or drag to change value";

// date formats
Calendar._TT["DEF_DATE_FORMAT"]	= "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"]	= "%a, %b %e";

Calendar._TT["WK"] 				= "wk";
Calendar._TT["TIME"] 			= "Time:";


Calendar._TT["ABOUT"] 			=
	"DHTML Date/Time Selector\n" +							// Do not translate this this
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + 	// Do not translate this this
	"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
	"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
	"\n\n" +
	"Date selection:\n" +
	"- Use the \xab, \xbb buttons to select year\n" +
	"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
	"- Hold mouse button on any of the above buttons for faster selection.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Time selection:\n" +
	"- Click on any of the time parts to increase it\n" +
	"- or Shift-click to decrease it\n" +
	"- or click and drag for faster selection.";