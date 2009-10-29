
// Calendar language file
// Lanuage: German
// Author: Jack (tR), <jack@jtr.de>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Sonntag",
							"Montag",
							"Dienstag",
							"Mittwoch",
							"Donnerstag",
							"Freitag",
							"Samstag",
							"Sonntag");

// short day names
Calendar._SDN 	= new Array("So",
							"Mo",
							"Di",
							"Mi",
							"Do",
							"Fr",
							"Sa",
							"So");

// full month names
Calendar._MN 	= new Array("Januar",
							"Februar",
							"März",
							"April",
							"Mai",
							"Juni",
							"Juli",
							"August",
							"September",
							"Oktober",
							"November",
							"Dezember");

// short month names
Calendar._SMN 	= new Array("Jan",
							"Feb",
							"Mär",
							"Apr",
							"May",
							"Jun",
							"Jul",
							"Aug",
							"Sep",
							"Okt",
							"Nov",
							"Dez");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 1;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Über dieses Kalendarmodul";
Calendar._TT["PREV_YEAR"] 		= "Voriges Jahr";
Calendar._TT["PREV_MONTH"] 		= "Voriger Monat";
Calendar._TT["GO_TODAY"] 		= "Heute auswählen";
Calendar._TT["NEXT_MONTH"] 		= "Nächster Monat";
Calendar._TT["NEXT_YEAR"] 		= "Nächstes Jahr";
Calendar._TT["SEL_DATE"] 		= "Datum auswählen";
Calendar._TT["DRAG_TO_MOVE"] 	= "Zum Bewegen festhalten";
Calendar._TT["PART_TODAY"] 		= " (Heute)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Woche beginnt mit %s ";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Schliessen";
Calendar._TT["TODAY"] 			= "Heute";
Calendar._TT["TIME_PART"] 		= "(Shift-)Klick oder Festhalten und Ziehen um den Wert zu ändern";

// date formats
Calendar._TT["DEF_DATE_FORMAT"]	= "%d.%m.%Y";
Calendar._TT["TT_DATE_FORMAT"]	= "%a, %b %e";

Calendar._TT["WK"] 				= "wk";
Calendar._TT["TIME"] 			= "Zeit:";


Calendar._TT["ABOUT"] 			=
	"DHTML Date/Time Selector\n" +							// Do not translate this this
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + 	// Do not translate this this
	"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
	"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
	"\n\n" +
	"Datum auswählen:\n" +
	"- Benutzen Sie die \xab, \xbb Buttons um das Jahr zu wählen\n" +
	"- Benutzen Sie die " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " Buttons um den Monat zu wählen\n" +
	"- Für eine Schnellauswahl halten Sie die Maustaste über diesen Buttons fest.";

Calendar._TT["ABOUT_TIME"] = 
	"\n\n" +
	"Zeit auswählen:\n" +
	"- Klicken Sie auf die Teile der Uhrzeit, um diese zu erhöhen\n" +
	"- oder klicken Sie mit festgehaltener Shift-Taste um diese zu verringern\n" +
	"- oder klicken und festhalten für Schnellauswahl.";