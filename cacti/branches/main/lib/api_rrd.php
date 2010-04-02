<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
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
define("RRD_FILE_VERSION1", "0001");
define("RRD_FILE_VERSION3", "0003");

/**
 * add a (list of) datasource(s) to an (array of) rrd file(s)
 * @param array $file_array	- array of rrd files
 * @param array $ds_array	- array of datasouce parameters
 * @param bool $debug		- debug mode
 * returns mixed			- success (bool) or error message (array)
 */
function api_rrd_datasource_add($file_array, $ds_array, $debug) {
	require_once(CACTI_BASE_PATH."/lib/rrd.php");
	require(CACTI_BASE_PATH . "/include/data_source/data_source_arrays.php");
	#print_r($ds_array);
	$rrd_struc = array();

	/* iterate all given rrd files */
	foreach($file_array as $file) {
		/* create a DOM object from an rrdtool dump */
		$dom = new domDocument;
		$dom->loadXML(rrdtool_execute("dump $file", false, RRDTOOL_OUTPUT_STDOUT, $rrd_struc, 'UTIL'));
		if (!$dom) {
			$check["err_msg"] = __('Error while parsing the XML of rrdtool dump');
			return $check;
		}

		/* rrdtool dump depends on rrd file version:
		 * version 0001 => RRDTool 1.0.x
		 * version 0003 => RRDTool 1.2.x, 1.3.x, 1.4.x
		 */
		$version = trim($dom->getElementsByTagName('version')->item(0)->nodeValue);

		/* now start XML processing */
		foreach($ds_array as $ds) {
			/* first, append the <DS> strcuture in the rrd header */
			if ($ds['type'] === $data_source_types[DATA_SOURCE_TYPE_COMPUTE]) {
				append_COMPUTE_DS($dom, $version, $ds['name'], $ds['type'], $ds['cdef']);
			}else {
				append_DS($dom, $version, $ds['name'], $ds['type'], $ds['heartbeat'], $ds['min'], $ds['max']);
			}
			/* now work on the <DS> structure as part of the <cdp_prep> tree */
			append_CDP_Prep_DS($dom, $version);
			/* add <V>alues to the <database> tree */
			append_Value($dom);
		}

		if($debug) {
			echo $dom->saveXML();
		}else {
			/* for rrdtool restore, we need a file, so write the XML to disk */
			$xml_file = $file . '.xml';
			$rc = $dom->save($xml_file);
			/* verify, if write was successful */
			if ($rc === false) {
				$check["err_msg"] = __('ERROR while writing XML file: %s', $xml_file);
				return $check;
			}else {
				/* are we allowed to write the rrd file? */
				if (is_writable($file)) {
					/* restore the modified XML to rrd */
					rrdtool_execute("restore -f $xml_file $file", false, RRDTOOL_OUTPUT_STDOUT, $rrd_struc, 'UTIL');
					/* scratch that XML file to avoid filling up the disk */
					unlink($xml_file);
					cacti_log(__("Added datasource(s) to rrd file: %s", $file), false, 'UTIL');
				}else {
					$check["err_msg"] = __('ERROR: RRD file %s not writeable', $file);
					return $check;
				}
			}
		}
	}
	return true;
}

/**
 * appends a <DS> subtree to an RRD XML structure
 * @param object $dom	- the DOM object, where the RRD XML is stored
 * @param string $name	- name of the new ds
 * @param string $type	- type of the new ds
 * @param int $min_hb	- heartbeat of the new ds
 * @param string $min	- min value of the new ds or [NaN|U]
 * @param string $max	- max value of the new ds or [NaN|U]
 * return object		- modified DOM
 */
function append_DS($dom, $version, $name, $type, $min_hb, $min, $max) {

	/* rrdtool version dependencies */
	if ($version === RRD_FILE_VERSION1) {
		$last_ds = "U";
	}elseif ($version === RRD_FILE_VERSION3) {
		$last_ds = "UNKN";
	}

	/* create <DS> subtree */
	$new_dom = new DOMDocument;
	/* pretty print */
	$new_dom->formatOutput = true;
	/* this defines the new node structure */
	$new_dom->loadXML("
		<ds>
			<name> $name </name>
			<type> $type </type>
			<minimal_heartbeat> $min_hb </minimal_heartbeat>
			<min> $min </min>
			<max> $max </max>

			<!-- PDP Status -->
			<last_ds> $last_ds </last_ds>
			<value> 0.0000000000e+00 </value>
			<unknown_sec> 0 </unknown_sec>
		</ds>");
	/* create a node element from new document */
	$new_node = $new_dom->getElementsByTagName("ds")->item(0);
	#echo $new_dom->saveXML();	# print new node

	/* get XPATH notation required for positioning */
	$xpath = new DOMXPath($dom);
	/* get XPATH for entry where new node will be inserted
	 * which is the <rra> entry */
	$insert = $xpath->query('/rrd/rra')->item(0);

	/* import the new node */
	$new_node = $dom->importNode($new_node, true);
	/* and insert it at the correct place */
	$insert->parentNode->insertBefore($new_node, $insert);
}

function append_COMPUTE_DS($dom, $version, $name, $type, $cdef) {

	/* rrdtool version dependencies */
	if ($version === RRD_FILE_VERSION1) {
		$last_ds = "U";
	}elseif ($version === RRD_FILE_VERSION3) {
		$last_ds = "UNKN";
	}

	/* create <DS> subtree */
	$new_dom = new DOMDocument;
	/* pretty print */
	$new_dom->formatOutput = true;
	/* this defines the new node structure */
	$new_dom->loadXML("
		<ds>
			<name> $name </name>
			<type> $type </type>
			<cdef> $cdef </cdef>

			<!-- PDP Status -->
			<last_ds> $last_ds </last_ds>
			<value> 0.0000000000e+00 </value>
			<unknown_sec> 0 </unknown_sec>
		</ds>");
	/* create a node element from new document */
	$new_node = $new_dom->getElementsByTagName("ds")->item(0);
	#echo $new_dom->saveXML();	# print new node

	/* get XPATH notation required for positioning */
	$xpath = new DOMXPath($dom);
	/* get XPATH for entry where new node will be inserted
	 * which is the <rra> entry */
	$insert = $xpath->query('/rrd/rra')->item(0);

	/* import the new node */
	$new_node = $dom->importNode($new_node, true);
	/* and insert it at the correct place */
	$insert->parentNode->insertBefore($new_node, $insert);
	#echo $dom->saveXML();	# print modified document
}

/**
 * append a <DS> subtree to the <CDP_PREP> subtrees of a RRD XML structure
 * @param $dom		- the DOM object, where the RRD XML is stored
 * return object	- the modified DOM object
 */
function append_CDP_Prep_DS($dom, $version) {

	/* get XPATH notation required for positioning */
	$xpath = new DOMXPath($dom);

	/* get XPATH for source <ds> entry */
	$src_ds = $xpath->query('/rrd/rra/cdp_prep/ds')->item(0);

	/* get all <cdp_prep><ds> entries */
	$itemList = $xpath->query('/rrd/rra/cdp_prep');
	/* iterate all entries found, equals "number of <rra>" times "number of <ds>" */
	if($itemList->length){
		foreach ($itemList as $item) {
			/* $item now points to the next <cdp_prep> XML Element */

			/* clone the source ds entry to preserve RRDTool notation */
			$new_ds = $src_ds->cloneNode(true);


			/* rrdtool version dependencies */
			if ($version === RRD_FILE_VERSION3) {
				$new_ds->getElementsByTagName("primary_value")->item(0)->nodeValue = " NaN ";
				$new_ds->getElementsByTagName("secondary_value")->item(0)->nodeValue = " NaN ";
			}

			/* the new node always has default entries */
			$new_ds->getElementsByTagName("value")->item(0)->nodeValue = " NaN ";
			$new_ds->getElementsByTagName("unknown_datapoints")->item(0)->nodeValue = " 0 ";

			/* append new ds entry at end of <cdp_prep> child list */
			$item->appendChild($new_ds);
		}
	}
}

/**
 * append a <V>alue element to the <DATABASE> subtrees of a RRD XML structure
 * @param $dom		- the DOM object, where the RRD XML is stored
 * return object	- the modified DOM object
 */
function append_Value($dom) {

	/* get XPATH notation required for positioning */
	$xpath = new DOMXPath($dom);

	/* get all <cdp_prep><ds> entries */
	$itemList = $xpath->query('/rrd/rra/database/row');
	/* iterate all entries found, equals "number of <rra>" times "number of <ds>" */
	if($itemList->length){
		foreach ($itemList as $item) {
			/* $item now points to the next <cdp_prep> XML Element */

			/* create <V> entry to preserve RRDTool notation */
			$new_v = $dom->createElement("v", " NaN ");

			/* append new ds entry at end of <cdp_prep> child list */
			$item->appendChild($new_v);
		}
	}
}

/**
 * get all rrd files related to the given data-template-id
 * @param int $data_template_id	- the id of the data template
 * @param bool $debug			- debug mode requested
 * return array					- all rrd files
 */
function get_data_template_rrd($data_template_id) {
	$files = array();
	/* fetch all rrd file names that are related to the given data template */
	$raw_files = db_fetch_assoc("SELECT " .
					"data_source_path " .
					"FROM data_template_data " .
					"WHERE data_template_id=" . $data_template_id . " " .
					"AND local_data_id > 0");	# do NOT fetch a template!

	if(sizeof($raw_files)) {
		foreach($raw_files as $file) {
			/* build /full/qualified/file/names */
			$files[] = str_replace('<path_rra>', CACTI_RRA_PATH, $file['data_source_path']);
		}
	}
	return $files;
}

/**
 * get all rrd files related to the given data-template-id
 * @param int $data_source_id	- the id of the data template
 * @param bool $debug			- debug mode requested
 * return array					- the rrd file
 */
function get_data_source_rrd($data_source_id) {
	$files[] = str_replace('<path_rra>', CACTI_RRA_PATH, db_fetch_cell("SELECT data_source_path FROM data_template_data WHERE local_data_id=" . $data_source_id));
	return $files;
}