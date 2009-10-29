
// Calendar language file
// Lanuage: Swedish
// Author: Leonard Norrgård, <leonard.norrgard@refactor.fi>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("söndag",
							"måndag",
							"tisdag",
							"onsdag",
							"torsdag",
							"fredag",
							"lördag",
							"söndag");

// short day names
Calendar._SDN 	= new Array("sö",
							"må",
							"ti",
							"on",
							"to",
							"fr",
							"lö",
							"sö");

// full month names
Calendar._MN 	= new Array("januari",
							"februari",
							"mars",
							"april",
							"maj",
							"juni",
							"juli",
							"augusti",
							"september",
							"oktober",
							"november",
							"december");

// short month names
Calendar._SMN 	= new Array("jan",
							"feb",
							"mar",
							"apr",
							"maj",
							"jun",
							"jul",
							"aug",
							"sep",
							"okt",
							"nov",
							"dec");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Om kalendern";
Calendar._TT["PREV_YEAR"] 		= "Föregående år (håll för menu)";
Calendar._TT["PREV_MONTH"] 		= "Föregående månad (håll för menu)";
Calendar._TT["GO_TODAY"] 		= "Gå till dagens datum";
Calendar._TT["NEXT_MONTH"] 		= "Följande månad (håll för menu)";
Calendar._TT["NEXT_YEAR"] 		= "Följande år (håll för menu)";
Calendar._TT["SEL_DATE"] 		= "Välj datum";
Calendar._TT["DRAG_TO_MOVE"] 	= "Drag för att flytta";
Calendar._TT["PART_TODAY"] 		= " (idag)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Stäng";
Calendar._TT["TODAY"] 			= "Idag";
Calendar._TT["TIME_PART"] 		= "(Skift-)klicka eller drag för att ändra tid";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] 	= "%A %d %b %Y";

Calendar._TT["WK"] 				= "vecka";
Calendar._TT["TIME"] 			= "Time:";


Calendar._TT["ABOUT"] 			=
	"DHTML Datum/tid-väljare\n" +
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
	"För senaste version gå till: http://www.dynarch.com/projects/calendar/\n" +
	"Distribueras under GNU LGPL.  Se http://gnu.org/licenses/lgpl.html för detaljer." +
	"\n\n" +
	"Val av datum:\n" +
	"- Använd knapparna \xab, \xbb för att välja år\n" +
	"- Använd knapparna " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " för att välja månad\n" +
	"- Håll musknappen nedtryckt på någon av ovanstående knappar för snabbare val.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Val av tid:\n" +
	"- Klicka på en del av tiden för att öka den delen\n" +
	"- eller skift-klicka för att minska den\n" +
	"- eller klicka och drag för snabbare val.";