
// Calendar language file
// Lanuage: Slovenian
// Author: David Milost, <mercy@volja.net>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Nedelja",
							"Ponedeljek",
							"Torek",
							"Sreda",
							"Četrtek",
							"Petek",
							"Sobota",
							"Nedelja");

// short day names
Calendar._SDN 	= new Array("Ned",
							"Pon",
							"Tor",
							"Sre",
							"Čet",
							"Pet",
							"Sob",
							"Ned");

// full month names
Calendar._MN 	= new Array("Januar",
							"Februar",
							"Marec",
							"April",
							"Maj",
							"Junij",
							"Julij",
							"Avgust",
							"September",
							"Oktober",
							"November",
							"December");

// short month names
Calendar._SMN 	= new Array("Jan",
							"Feb",
							"Mar",
							"Apr",
							"Maj",
							"Jun",
							"Jul",
							"Avg",
							"Sep",
							"Okt",
							"Nov",
							"Dec");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "O koledarju";
Calendar._TT["PREV_YEAR"] 		= "Predhodnje leto (dolg klik za meni)";
Calendar._TT["PREV_MONTH"] 		= "Predhodnji mesec (dolg klik za meni)";
Calendar._TT["GO_TODAY"] 		= "Pojdi na tekoći dan";
Calendar._TT["NEXT_MONTH"] 		= "Naslednji mesec (dolg klik za meni)";
Calendar._TT["NEXT_YEAR"] 		= "Naslednje leto (dolg klik za meni)";
Calendar._TT["SEL_DATE"] 		= "Izberite datum";
Calendar._TT["DRAG_TO_MOVE"] 	= "Pritisni in povleci za spremembo pozicije";
Calendar._TT["PART_TODAY"] 		= " (danes)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Zapri";
Calendar._TT["TODAY"] 			= "Danes";
Calendar._TT["TIME_PART"] 		= "(Shift-)Click or drag to change value";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] 	= "%a, %b %e";

Calendar._TT["WK"] 				= "Ted";
Calendar._TT["TIME"] 			= "Time:";


Calendar._TT["ABOUT"] 			=
	"DHTML Date/Time Selector\n" +
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
	"Za zadnjo verzijo pojdine na naslov: http://www.dynarch.com/projects/calendar/\n" +
	"Distribuirano pod GNU LGPL.  Poglejte http://gnu.org/licenses/lgpl.html za podrobnosti." +
	"\n\n" +
	"Izbor datuma:\n" +
	"- Uporabite \xab, \xbb gumbe za izbor leta\n" +
	"- Uporabite " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " gumbe za izbor meseca\n" +
	"- Zadržite klik na kateremkoli od zgornjih gumbov za hiter izbor.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Izbor ćasa:\n" +
	"- Kliknite na katerikoli del ćasa za poveć. le-tega\n" +
	"- ali Shift-click za zmanj. le-tega\n" +
	"- ali kliknite in povlecite za hiter izbor.";