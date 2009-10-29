
// Calendar language file
// Lanuage: Croatian
// Author: Krunoslav Zubrinic <krunoslav.zubrinic@vip.hr>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Nedjelja",
							"Ponedjeljak",
							"Utorak",
							"Srijeda",
							"Četvrtak",
							"Petak",
							"Subota",
							"Nedjelja");

// full month names
Calendar._MN 	= new Array("Siječanj",
							"Veljača",
							"Ožujak",
							"Travanj",
							"Svibanj",
							"Lipanj",
							"Srpanj",
							"Kolovoz",
							"Rujan",
							"Listopad",
							"Studeni",
							"Prosinac");


// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "About the calendar";
Calendar._TT["PREV_YEAR"] 		= "Prethodna godina (dugi pritisak za meni)";
Calendar._TT["PREV_MONTH"] 		= "Prethodni mjesec (dugi pritisak za meni)";
Calendar._TT["GO_TODAY"] 		= "Idi na tekući dan";
Calendar._TT["NEXT_MONTH"] 		= "Slijedeći mjesec (dugi pritisak za meni)";
Calendar._TT["NEXT_YEAR"] 		= "Slijedeća godina (dugi pritisak za meni)";
Calendar._TT["SEL_DATE"] 		= "Izaberite datum";
Calendar._TT["DRAG_TO_MOVE"] 	= "Pritisni i povuci za promjenu pozicije";
Calendar._TT["PART_TODAY"] 		= " (today)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Zatvori";
Calendar._TT["TODAY"] 			= "Danas";
Calendar._TT["TIME_PART"] 		= "(Shift-)Click or drag to change value";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "dd-mm-y";
Calendar._TT["TT_DATE_FORMAT"] 	= "DD, dd.mm.y";

Calendar._TT["WK"] 				= "Tje";
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