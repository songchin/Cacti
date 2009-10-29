
// Calendar language file
// Lanuage: Albanian
// Author: Rigels Gordani, <rige@hotmail.com>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("E Diele",
							"E Hene",
							"E Marte",
							"E Merkure",
							"E Enjte",
							"E Premte",
							"E Shtune",
							"E Diele");

// short day names
Calendar._SDN 	= new Array("Die",
							"Hen",
							"Mar",
							"Mer",
							"Enj",
							"Pre",
							"Sht",
							"Die");

// full month names
Calendar._MN 	= new Array("Janar",
							"Shkurt",
							"Mars",
							"Prill",
							"Maj",
							"Qeshor",
							"Korrik",
							"Gusht",
							"Shtator",
							"Tetor",
							"Nentor",
							"Dhjetor");

// short month names
Calendar._SMN 	= new Array("Jan",
							"Shk",
							"Mar",
							"Pri",
							"Maj",
							"Qes",
							"Kor",
							"Gus",
							"Sht",
							"Tet",
							"Nen",
							"Dhj");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Per kalendarin";
Calendar._TT["PREV_YEAR"] 		= "Viti i shkuar (prit per menune)";
Calendar._TT["PREV_MONTH"] 		= "Muaji i shkuar (prit per menune)";
Calendar._TT["GO_TODAY"] 		= "Sot";
Calendar._TT["NEXT_MONTH"] 		= "Muaji i ardhshem (prit per menune)";
Calendar._TT["NEXT_YEAR"] 		= "Viti i ardhshem (prit per menune)";
Calendar._TT["SEL_DATE"] 		= "Zgjidh daten";
Calendar._TT["DRAG_TO_MOVE"] 	= "Terhiqe per te levizur";
Calendar._TT["PART_TODAY"] 		= " (sot)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Trego te %s te paren";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Mbyll";
Calendar._TT["TODAY"] 			= "Sot";
Calendar._TT["TIME_PART"] 		= "Kliko me (Shift-)ose terhiqe per te ndryshuar vleren";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] 	= "%a, %b %e";

Calendar._TT["WK"] 				= "Java";
Calendar._TT["TIME"] 			= "Koha:";


Calendar._TT["ABOUT"] 			=
	"Zgjedhes i ores/dates ne DHTML \n" +
	"\n\n" +"Zgjedhja e Dates:\n" +
	"- Perdor butonat \xab, \xbb per te zgjedhur vitin\n" +
	"- Perdor  butonat" + String.fromCharCode(0x2039) + ", " + 
	String.fromCharCode(0x203a) +
	" per te  zgjedhur muajin\n" +
	"- Mbani shtypur butonin e mousit per nje zgjedje me te shpejte.";

Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Zgjedhja e kohes:\n" +
	"- Kliko tek ndonje nga pjeset e ores per ta rritur ate\n" +
	"- ose kliko me Shift per ta zvogeluar ate\n" +
	"- ose cliko dhe terhiq per zgjedhje me te shpejte.";