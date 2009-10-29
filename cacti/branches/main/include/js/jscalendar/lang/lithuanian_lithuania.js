
// Calendar language file
// Lanuage: Lithuanian
// Author: Martynas Majeris, <martynas@solmetra.lt>
// Encoding: UTF-8
// Modified by: The Cacti Group
// Distributed under the same terms as the calendar itself.

// full day names
Calendar._DN 	= new Array("Sekmadienis",
							"Pirmadienis",
							"Antradienis",
							"Trečiadienis",
							"Ketvirtadienis",
							"Pentadienis",
							"Šeštadienis",
							"Sekmadienis");

// short day names
Calendar._SDN 	= new Array("Sek",
							"Pir",
							"Ant",
							"Tre",
							"Ket",
							"Pen",
							"Šeš",
							"Sek");

// full month names
Calendar._MN 	= new Array("Sausis",
							"Vasaris",
							"Kovas",
							"Balandis",
							"Gegužė",
							"Birželis",
							"Liepa",
							"Rugpjūtis",
							"Rugsėjis",
							"Spalis",
							"Lapkritis",
							"Gruodis");

// short month names
Calendar._SMN 	= new Array("Sau",
							"Vas",
							"Kov",
							"Bal",
							"Geg",
							"Bir",
							"Lie",
							"Rgp",
							"Rgs",
							"Spa",
							"Lap",
							"Gru");

// First day of the week. "0" means display Sunday first, "1" means display Monday first
Calendar._FD = 0;

// Tooltips, About page and date format
Calendar._TT 					= {};
Calendar._TT["INFO"] 			= "Apie kalendorių";
Calendar._TT["PREV_YEAR"] 		= "Ankstesni metai (laikykite, jei norite meniu)";
Calendar._TT["PREV_MONTH"] 		= "Ankstesnis mėnuo (laikykite, jei norite meniu)";
Calendar._TT["GO_TODAY"] 		= "Pasirinkti šiandieną";
Calendar._TT["NEXT_MONTH"] 		= "Kitas mėnuo (laikykite, jei norite meniu)";
Calendar._TT["NEXT_YEAR"] 		= "Kiti metai (laikykite, jei norite meniu)";
Calendar._TT["SEL_DATE"] 		= "Pasirinkite datą";
Calendar._TT["DRAG_TO_MOVE"] 	= "Tempkite";
Calendar._TT["PART_TODAY"] 		= " (šiandien)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] 		= "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] 		= "0,6";

Calendar._TT["CLOSE"] 			= "Uždaryti";
Calendar._TT["TODAY"] 			= "Šiandien";
Calendar._TT["TIME_PART"] 		= "Spustelkite arba tempkite jei norite pakeisti";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] 	= "%A, %Y-%m-%d";

Calendar._TT["WK"]				= "sav";
Calendar._TT["TIME"] 			= "Time:";


Calendar._TT["ABOUT"] 			=
	"DHTML Date/Time Selector\n" +
	"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
	"Naujausią versiją rasite: http://www.dynarch.com/projects/calendar/\n" +
	"Platinamas pagal GNU LGPL licenciją. Aplankykite http://gnu.org/licenses/lgpl.html" +
	"\n\n" +
	"Datos pasirinkimas:\n" +
	"- Metų pasirinkimas: \xab, \xbb\n" +
	"- Mėnesio pasirinkimas: " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + "\n" +
	"- Nuspauskite ir laikykite pelės klavišą greitesniam pasirinkimui.";
	
Calendar._TT["ABOUT_TIME"] =
	"\n\n" +
	"Laiko pasirinkimas:\n" +
	"- Spustelkite ant valandų arba minučių - skaičius padidės vienetu.\n" +
	"- Jei spausite kartu su Shift, skaičius sumažės.\n" +
	"- Greitam pasirinkimui spustelkite ir pajudinkite pelę.";