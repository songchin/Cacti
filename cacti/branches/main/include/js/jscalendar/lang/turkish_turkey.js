
// Calendar language file
// Lanuage: Turkish
// Author: Nuri AKMAN, <nuriakman@hotmail.com>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Pazar",
							"Pazartesi",
							"Salý",
							"Çarþamba",
							"Perþembe",
							"Cuma",
							"Cumartesi",
							"Pazar");

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
Calendar._MN 	= new Array("Ocak",
							"Þubat",
							"Mart",
							"Nisan",
							"Mayýs",
							"Haziran",
							"Temmuz",
							"Aðustos",
							"Eylül",
							"Ekim",
							"Kasým",
							"Aralýk");

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
Calendar._TT["PREV_YEAR"] 		= "Önceki Yýl (Menü için basýlý tutunuz)";
Calendar._TT["PREV_MONTH"] 		= "Önceki Ay (Menü için basýlý tutunuz)";
Calendar._TT["GO_TODAY"] 		= "Bugün'e git";
Calendar._TT["NEXT_MONTH"] 		= "Sonraki Ay (Menü için basýlý tutunuz)";
Calendar._TT["NEXT_YEAR"] 		= "Sonraki Yýl (Menü için basýlý tutunuz)";
Calendar._TT["SEL_DATE"] 		= "Tarih seçiniz";
Calendar._TT["DRAG_TO_MOVE"] 	= "Taþýmak için sürükleyiniz";
Calendar._TT["PART_TODAY"] 		= " (bugün)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Kapat";
Calendar._TT["TODAY"] 			= "Bugün";
Calendar._TT["TIME_PART"] 		= "(Shift-)Click or drag to change value";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "dd-mm-y";
Calendar._TT["TT_DATE_FORMAT"] 	= "%d %M %y";

Calendar._TT["WK"]				= "Hafta";
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