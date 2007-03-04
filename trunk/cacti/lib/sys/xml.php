<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2007 The Cacti Groupi                                |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function xml_array_get($data) {
	/* mvo voncken@mailandnews.com
	 * original ripped from  on the gdemartini@bol.com.br
	 * to be used for data retrieval(result-structure is Data oriented) */
	$p = xml_parser_create();
	xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
	xml_parse_into_struct($p, $data, $vals, $index);
	xml_parser_free($p);

	$tree = array();
	$i = 0;
	$tree = _xml_array_children_get($vals, $i);

	return $tree;
}

function xml_character_encode($text) {
	return htmlentities($text, ENT_QUOTES);
}

/* borrowed from html_entity_decode() manual page */
function xml_character_decode($text) {
	/* replace numeric entities */
	$text = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text);
	$text = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $text);
	/* replace literal entities */
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($text, $trans_tbl);
}

function _xml_array_children_get($vals, &$i) {
	$children = array();

	if (isset($vals[$i]['value'])) {
		if ($vals[$i]['value']) array_push($children, $vals[$i]['value']);
	}

	$prevtag = ""; $j = 0;

	while (++$i < count($vals)) {
		switch ($vals[$i]['type']) {
		case 'cdata':
			array_push($children, $vals[$i]['value']);
			break;
		case 'complete':
			/* if the value is an empty string, php doesn't include the 'value' key
			 * in its array, so we need to check for this first */
			if (isset($vals[$i]['value'])) {
				$children{($vals[$i]['tag'])} = $vals[$i]['value'];
			}else{
				$children{($vals[$i]['tag'])} = "";
			}

			break;
		case 'open':
			$j++;

			if ($prevtag <> $vals[$i]['tag']) {
				$j = 0;
				$prevtag = $vals[$i]['tag'];
			}

			$children{($vals[$i]['tag'])} = _xml_array_children_get($vals,$i);
			break;
		case 'close':
			return $children;
		}
	}
}

?>
