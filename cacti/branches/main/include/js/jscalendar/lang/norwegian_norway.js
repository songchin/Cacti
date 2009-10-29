
// Calendar language file
// Lanuage: Norwegian
// Author: Daniel Holmen, <daniel.holmen@ciber.no>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Søndag",
							"Mandag",
							"Tirsdag",
							"Onsdag",
							"Torsdag",
							"Fredag",
							"Lørdag",
							"Søndag");

// short day names
Calendar._SDN 	= new Array("Søn",
							"Man",
							"Tir",
							"Ons",
							"Tor",
							"Fre",
							"Lør",
							"Søn");

// full month names
Calendar._MN 	= new Array("Januar",
							"Februar",
							"Mars",
							"April",
							"Mai",
							"Juni",
							"Juli",
							"August",
							"September",
							"Oktober",
							"November",
							"Desember");

// short month names
Calendar._SMN 	= new Array("Jan",
							"Feb",
							"Mar",
							"Apr",
							"Mai",
							"Jun",
							"Jul",
							"Aug",
							"Sep",
							"Okt",
							"Nov",
							"Des");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Om kalenderen";
Calendar._TT["PREV_YEAR"] 		= "Forrige. år (hold for meny)";
Calendar._TT["PREV_MONTH"] 		= "Forrige. måned (hold for meny)";
Calendar._TT["GO_TODAY"] 		= "Gå til idag";
Calendar._TT["NEXT_MONTH"] 		= "Neste måned (hold for meny)";
Calendar._TT["NEXT_YEAR"] 		= "Neste år (hold for meny)";
Calendar._TT["SEL_DATE"] 		= "Velg dato";
Calendar._TT["DRAG_TO_MOVE"] 	= "Dra for å flytte";
Calendar._TT["PART_TODAY"] 		= " (idag)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Lukk";
Calendar._TT["TODAY"] 			= "Idag";
Calendar._TT["TIME_PART"]		= "(Shift-)Klikk eller dra for å endre verdi";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d.%m.%Y";
Calendar._TT["TT_DATE_FORMAT"] 	= "%a, %b %e";

Calendar._TT["WK"] 				= "uke";
Calendar._TT["TIME"] 			= "Time:";


Calendar._TT["ABOUT"] 			=
	"DHTML Dato-/Tidsvelger\n" +
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
	"For nyeste versjon, gå til: http://www.dynarch.com/projects/calendar/\n" +
	"Distribuert under GNU LGPL.  Se http://gnu.org/licenses/lgpl.html for detaljer." +
	"\n\n" +
	"Datovalg:\n" +
	"- Bruk knappene \xab og \xbb for å velge år\n" +
	"- Bruk knappene " + String.fromCharCode(0x2039) + " og " + String.fromCharCode(0x203a) + " for å velge måned\n" +
	"- Hold inne musknappen eller knappene over for raskere valg.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Tidsvalg:\n" +
	"- Klikk på en av tidsdelene for å øke den\n" +
	"- eller Shift-klikk for å senke verdien\n" +
	"- eller klikk-og-dra for raskere valg.";