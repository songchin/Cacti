
// Calendar language file
// Lanuage: Dutch
// Author: unknown
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Zondag",
							"Maandag",
							"Dinsdag",
							"Woensdag",
							"Donderdag",
							"Vrijdag",
							"Zaterdag",
							"Zondag");

// short day names
Calendar._SDN_len = 2;

// full month names
Calendar._MN 	= new Array("Januari",
							"Februari",
							"Maart",
							"April",
							"Mei",
							"Juni",
							"Juli",
							"Augustus",
							"September",
							"Oktober",
							"November",
							"December");

// short month names
Calendar._SMN_len = 3

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Info";
Calendar._TT["PREV_YEAR"] 		= "Vorig jaar (ingedrukt voor menu)";
Calendar._TT["PREV_MONTH"] 		= "Vorige maand (ingedrukt voor menu)";
Calendar._TT["GO_TODAY"] 		= "Ga naar Vandaag";
Calendar._TT["NEXT_MONTH"] 		= "Volgende maand (ingedrukt voor menu)";
Calendar._TT["NEXT_YEAR"] 		= "Volgend jaar (ingedrukt voor menu)";
Calendar._TT["SEL_DATE"] 		= "Selecteer datum";
Calendar._TT["DRAG_TO_MOVE"] 	= "Klik en sleep om te verplaatsen";
Calendar._TT["PART_TODAY"] 		= " (vandaag)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Toon %s eerst";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Sluiten";
Calendar._TT["TODAY"] 			= "(vandaag)";
Calendar._TT["TIME_PART"] 		= "(Shift-)Klik of sleep om de waarde te veranderen";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%Y";
Calendar._TT["TT_DATE_FORMAT"] 	= "%a, %e %b %Y";

Calendar._TT["WK"] 				= "wk";
Calendar._TT["TIME"] 			= "Tijd:";


Calendar._TT["ABOUT"] 			=
	"DHTML Datum/Tijd Selector\n" +
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" +
	"Ga voor de meest recente versie naar: http://www.dynarch.com/projects/calendar/\n" +
	"Verspreid onder de GNU LGPL. Zie http://gnu.org/licenses/lgpl.html voor details." +
	"\n\n" +
	"Datum selectie:\n" +
	"- Gebruik de \xab \xbb knoppen om een jaar te selecteren\n" +
	"- Gebruik de " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " knoppen om een maand te selecteren\n" +
	"- Houd de muis ingedrukt op de genoemde knoppen voor een snellere selectie.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Tijd selectie:\n" +
	"- Klik op een willekeurig onderdeel van het tijd gedeelte om het te verhogen\n" +
	"- of Shift-klik om het te verlagen\n" +
	"- of klik en sleep voor een snellere selectie.";