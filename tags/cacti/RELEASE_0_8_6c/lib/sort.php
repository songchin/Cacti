<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,        
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

/* usort_data_query_index - attempts to sort a data query index either numerically
     or alphabetically depending on which seems best. it also trys to strip out
     extra characters before sorting to improve accuracy when sorting things like
     switch ifNames, etc
   @arg $a - the first string to compare
   @arg $b - the second string to compare
   @returns - '1' if $a is greater than $b, '-1' if $a is less than $b, or '0' if
     $b is equal to $b */
function usort_data_query_index($a, $b) {
	$arr_a = split("\/", $a);
	$arr_b = split("\/", $b);
	
	for ($i=0; $i<min(count($arr_a), count($arr_b)); $i++) {
		if ((is_numeric($arr_a[$i])) && (is_numeric($arr_b[$i]))) {
			if (intval($arr_a[$i]) > intval($arr_b[$i])) {
				return 1;
			}elseif (intval($arr_a[$i]) < intval($arr_b[$i])) {
				return -1;
			}
		}else{
			$cmp = strcmp(strval($arr_a[$i]), strval($arr_b[$i]));
			
			if (($cmp > 0) || ($cmp < 0)) {
				return $cmp;
			}
		}
	}
	
	if (count($arr_a) < count($arr_b)) {
		return 1;
	}elseif (count($arr_a) > count($arr_b)) {
		return -1;
	}
	
	return 0;
}

/* usort_numeric - sorts two values numerically (ie. 1, 34, 36, 76)
   @arg $a - the first string to compare
   @arg $b - the second string to compare
   @returns - '1' if $a is greater than $b, '-1' if $a is less than $b, or '0' if
     $b is equal to $b */
function usort_numeric($a, $b) {
	if (intval($a) > intval($b)) {
		return 1;
	}elseif (intval($a) < intval($b)) {
		return -1;
	}else{
		return 0;
	}
}

/* usort_alphabetic - sorts two values alphabetically (ie. ab, by, ef, xy)
   @arg $a - the first string to compare
   @arg $b - the second string to compare
   @returns - '1' if $a is greater than $b, '-1' if $a is less than $b, or '0' if
     $b is equal to $b */
function usort_alphabetic($a, $b) {
	return strcmp($a, $b);
}

?>