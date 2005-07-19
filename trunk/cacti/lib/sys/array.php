<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

/* array_rekey - changes an array in the form:
     '$arr[0] = array("id" => 23, "name" => "blah")'
     to the form
     '$arr = array(23 => "blah")'
   @arg $array - (array) the original array to manipulate
   @arg $key - the name of the key
   @arg $key_value - the name of the key value
   @returns - the modified array */
function array_rekey($array, $key, $key_value) {
	$ret_array = array();
	$k = 0;

	if (sizeof($array) > 0) {
		foreach ($array as $item) {
			if ($key == "") {
				$item_key = $k;
				$k++;
			}else{
				$item_key = $item[$key];
			}

			if (is_array($key_value)) {
				for ($i=0; $i<count($key_value); $i++) {
					$ret_array[$item_key]{$key_value[$i]} = $item{$key_value[$i]};
				}
			}else{
				$ret_array[$item_key] = $item[$key_value];
			}
		}
	}

	return $ret_array;
}

/* array_merge_recursive_replace - merges $paArray2 into $paArray1 recursively even if they keys do
     not match between the two arrays
   @arg $paArray1 - the array that data will be merged into
   @arg $paArray2 - the array that will be merged into paArray1
   @returns - a new array containing the merged two original two arrays  */
function array_merge_recursive_replace($paArray1, $paArray2) {
	if (!is_array($paArray1) || !is_array($paArray2)) {
		return $paArray2;
	}

	foreach ($paArray2 as $sKey2 => $sValue2) {
		$paArray1[$sKey2] = array_merge_recursive_replace(@$paArray1[$sKey2], $sValue2);
	}

	return $paArray1;
}

function print_a($arr) {
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

?>
