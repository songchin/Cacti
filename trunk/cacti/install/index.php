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
 | This program is distributed in the hope that it will be useful,         |
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

include("../include/config.php");
include("../include/config_settings.php");

$cacti_versions = array("0.8", "0.8.1", "0.8.2", "0.8.2a", "0.8.3", "0.8.3a", "0.8.4", "0.8.5");

$old_cacti_version = db_fetch_cell("select cacti from version");

/* do a version check */
if ($old_cacti_version == $config["cacti_version"]) {
	print "	<p style='font-family: Verdana, Arial; font-size: 16px; font-weight: bold; color: red;'>Error</p>
		upgrades, this installation is already up-to-date. Click <a href='../index.php'>here</a> to use cacti.</p>";
	exit;
}elseif (ereg("^0\.6", $old_cacti_version)) {
	print "	<p style='font-family: Verdana, Arial; font-size: 16px; font-weight: bold; color: red;'>Error</p>
		<p style='font-family: Verdana, Arial; font-size: 12px;'>You are attempting to install cacti " . $config["cacti_version"] . "
		onto a 0.6.x database. To continue, you must create a new database, import 'cacti.sql' into it, and
		update 'include/config.php' to point to the new database.</p>";
	exit;
}elseif (empty($old_cacti_version)) {
	print "	<p style='font-family: Verdana, Arial; font-size: 16px; font-weight: bold; color: red;'>Error</p>
		<p style='font-family: Verdana, Arial; font-size: 12px;'>You have created a new database, but have not yet imported
		the 'cacti.sql' file. At the command line, execute the following to continue:</p>
		<p><pre>mysql -u $database_username -p $database_default < cacti.sql</pre></p>";
	exit;
}

function db_install_execute($cacti_version, $sql) {
	$sql_install_cache = (isset($_SESSION["sess_sql_install_cache"]) ? $_SESSION["sess_sql_install_cache"] : array());
	
	if (db_execute($sql)) {
		$sql_install_cache{sizeof($sql_install_cache)}[$cacti_version][1] = $sql;
	}else{
		$sql_install_cache{sizeof($sql_install_cache)}[$cacti_version][0] = $sql;
	}
	
	$_SESSION["sess_sql_install_cache"] = $sql_install_cache;
}

function find_best_path($binary_name) {
	$search_paths = array("/bin", "/sbin", "/usr/bin", "/usr/sbin", "/usr/local/bin", "/usr/local/sbin");
	
	for ($i=0; $i<count($search_paths); $i++) {
		if ((file_exists($search_paths[$i] . "/" . $binary_name)) && (is_executable($search_paths[$i] . "/" . $binary_name))) {
			return $search_paths[$i] . "/" . $binary_name;
		}
	}
}

$current_document_root = "";
$current_document = str_replace("/install", "", dirname($_SERVER["PHP_SELF"]));

/* find the current document root depending on if we're using apache or iis */
if ((stristr($_SERVER["SERVER_SOFTWARE"], "IIS")) && (isset($_SERVER["PATH_TRANSLATED"]))) {
	$current_document_root = str_replace($_SERVER["PHP_SELF"], "", str_replace("\\", "/", $_SERVER["PATH_TRANSLATED"]));
}elseif (isset($_SERVER["DOCUMENT_ROOT"])) {
	$current_document_root = $_SERVER["DOCUMENT_ROOT"];
}

/* Here, we define each name, default value, type, and path check for each value
we want the user to input. The "name" field must exist in the 'settings' table for
this to work. Cacti also uses different default values depending on what OS it is
running on. */

/* RRDTool Binary Path */
$input["path_rrdtool"] = $settings["path"]["path_rrdtool"];

if ($config["cacti_server_os"] == "unix") {
	$which_rrdtool = find_best_path("rrdtool");
	
	if (!empty($which_rrdtool)) {
		$input["path_rrdtool"]["default"] = $which_rrdtool;
	}elseif (read_config_option("path_rrdtool") != "<DEFAULT>") {
		$input["path_rrdtool"]["default"] = read_config_option("path_rrdtool");
	}else{
		$input["path_rrdtool"]["default"] = "/usr/local/bin/rrdtool";
	}
}elseif ($config["cacti_server_os"] == "win32") {
	$input["path_rrdtool"]["default"] = "c:/rrdtool/rrdtool.exe";
}

/* PHP Binary Path */
$input["path_php_binary"] = $settings["path"]["path_php_binary"];

if ($config["cacti_server_os"] == "unix") {
	$which_php = find_best_path("php");
	
	if (!empty($which_php)) {
		$input["path_php_binary"]["default"] = $which_php;
	}elseif (read_config_option("path_php_binary") != "<DEFAULT>") {
		$input["path_php_binary"]["default"] = read_config_option("path_php_binary");
	}else{
		$input["path_php_binary"]["default"] = "/usr/bin/php";
	}
}elseif ($config["cacti_server_os"] == "win32") {
	$input["path_php_binary"]["default"] = "c:/php/php.exe";
}

/* snmpwalk Binary Path */
if ($config["cacti_server_os"] == "unix") {
	$input["path_snmpwalk"] = $settings["path"]["path_snmpwalk"];
	
	$which_snmpwalk = find_best_path("snmpwalk");
	
	if (!empty($which_snmpwalk)) {
		$input["path_snmpwalk"]["default"] = $which_snmpwalk;
	}elseif (read_config_option("path_snmpwalk") != "<DEFAULT>") {
		$input["path_snmpwalk"]["default"] = read_config_option("path_snmpwalk");
	}else{
		$input["path_snmpwalk"]["default"] = "/usr/local/bin/snmpwalk";
	}
}

/* snmpget Binary Path */
if ($config["cacti_server_os"] == "unix") {
	$input["path_snmpget"] = $settings["path"]["path_snmpget"];
	
	$which_snmpget = find_best_path("snmpget");
	
	if (!empty($which_snmpget)) {
		$input["path_snmpget"]["default"] = $which_snmpget;
	}elseif (read_config_option("path_snmpget") != "<DEFAULT>") {
		$input["path_snmpget"]["default"] = read_config_option("path_snmpget");
	}else{
		$input["path_snmpget"]["default"] = "/usr/local/bin/snmpget";
	}
}

/* SNMP Version */
if ($config["cacti_server_os"] == "unix") {
	$input["snmp_version"] = $settings["general"]["snmp_version"];
	$input["snmp_version"]["default"] = "net-snmp";
}

/* default value for this variable */
if (!isset($_REQUEST["install_type"])) {
	$_REQUEST["install_type"] = 0;
}

/* defaults for the install type dropdown */
if ($old_cacti_version == "new_install") {
	$default_install_type = "1";
}else{
	$default_install_type = "3";
}

/* pre-processing that needs to be done for each step */
if (empty($_REQUEST["step"])) {
	$_REQUEST["step"] = 1;
}else{
	if ($_REQUEST["step"] == "1") {
		$_REQUEST["step"] = "2";
	}elseif (($_REQUEST["step"] == "2") && ($_REQUEST["install_type"] == "1")) {
		$_REQUEST["step"] = "3";
	}elseif (($_REQUEST["step"] == "2") && ($_REQUEST["install_type"] == "3")) {
		$_REQUEST["step"] = "8";
	}elseif ($_REQUEST["step"] == "8") {
		$_REQUEST["step"] = "3";
	}elseif ($_REQUEST["step"] == "3") {
		$_REQUEST["step"] = "4";
	}
}

if ($_REQUEST["step"] == "4") {
	include_once("../lib/data_query.php");
	include_once("../lib/utility.php");
	
	$i = 0;
	
	/* get all items on the form and write values for them  */
	while (list($name, $array) = each($input)) {
		if (isset($_POST[$name])) {
			db_execute("update settings set value='" . $_POST[$name] . "' where name='$name'");
		}
	}
	
	setcookie(session_name(),"",time() - 3600,"/");
	
	kill_session_var("sess_config_array");
	kill_session_var("sess_host_cache_array");
	
	/* just in case we have hard drive graphs to deal with */
	data_query(db_fetch_cell("select id from host where hostname='127.0.0.1'"), 6);
	
	/* it's always a good idea to re-populate the poller cache to make sure everything is refreshed and
	up-to-date */
	repopulate_poller_cache();
	
	db_execute("delete from version");
	db_execute("insert into version (cacti) values ('" . $config["cacti_version"] . "')");
	
	header ("Location: ../index.php");
	exit;
}elseif (($_REQUEST["step"] == "8") && ($_REQUEST["install_type"] == "3")) {
	/* try to find current (old) version in the array */
	$version_index = array_search($old_cacti_version, $cacti_versions);
	
	/* if the version is not found, die */
	if (!is_int(array_search($old_cacti_version, $cacti_versions))) {
		print "	<p style='font-family: Verdana, Arial; font-size: 16px; font-weight: bold; color: red;'>Error</p>
			<p style='font-family: Verdana, Arial; font-size: 12px;'>Invalid Cacti version 
			<strong>$old_cacti_version</strong>, cannot upgrade to <strong>" . $config["cacti_version"] . "
			</strong></p>";
		exit;
	}
	
	/* loop from the old version to the current, performing updates for each version in between */
	for ($i=($version_index+1); $i<count($cacti_versions); $i++) {
		if ($cacti_versions[$i] == "0.8.1") {
			include ("0_8_to_0_8_1.php");
			upgrade_to_0_8_1();
		}elseif ($cacti_versions[$i] == "0.8.2") {
			include ("0_8_1_to_0_8_2.php");
			upgrade_to_0_8_2();
		}elseif ($cacti_versions[$i] == "0.8.2a") {
			include ("0_8_2_to_0_8_2a.php");
			upgrade_to_0_8_2a();
		}elseif ($cacti_versions[$i] == "0.8.3") {
			include ("0_8_2a_to_0_8_3.php");
			include_once("../lib/utility.php");
			upgrade_to_0_8_3();
		}elseif ($cacti_versions[$i] == "0.8.4") {
			include ("0_8_3_to_0_8_4.php");
			upgrade_to_0_8_4();
		}elseif ($cacti_versions[$i] == "0.8.5") {
			include ("0_8_4_to_0_8_5.php");
			upgrade_to_0_8_5();
		}
	}
}

?>
<html>
<head>
	<title>cacti</title>
	<style>
	<!--
		BODY,TABLE,TR,TD
		{
			font-size: 10pt;
			font-family: Verdana, Arial, sans-serif;
		}
		
		.code
		{
			font-family: Courier New, Courier;
		}
		
		.header-text
		{
			color: white;
			font-weight: bold;
		}
	-->
	</style>
</head>

<body>

<form method="post" action="index.php">

<table width="500" align="center" cellpadding="1" cellspacing="0" border="0" bgcolor="#104075">
	<tr bgcolor="#FFFFFF" height="10">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td width="100%">
			<table cellpadding="3" cellspacing="0" border="0" bgcolor="#E6E6E6" width="100%">
				<tr>
					<td bgcolor="#104075" class="header-text">Cacti Installation Guide</td>
				</tr>
				<tr>
					<td width="100%" style="font-size: 12px;">
						<?php if ($_REQUEST["step"] == "1") { ?>
						
						<p>Thanks for taking the time to download and install cacti, the complete graphing 
						solution for your network. Before you can start making cool graphs, there are a few 
						pieces of data that cacti needs to know.</p>
						
						<p>Make sure you have read and followed the required steps needed to install cacti
						before continuing. Install information can be found for 
						<a href="../docs/html/install_unix.html">Unix</a> and <a href="../docs/html/install_windows.html">Win32</a>-based operating systems.</p>
						
						<p>Also, if this is an upgrade, be sure to reading the <a href="../docs/html/upgrade.html">Upgrade</a> information file.</p>
						
						<p>Cacti is licensed under the GNU General Public License, you must agree
						to its provisions before continuing:</p>
						
						<p class="code">This program is free software; you can redistribute it and/or
						modify it under the terms of the GNU General Public License
						as published by the Free Software Foundation; either version 2
						of the License, or (at your option) any later version.</p>
						
						<p class="code">This program is distributed in the hope that it will be useful,
						but WITHOUT ANY WARRANTY; without even the implied warranty of
						MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
						GNU General Public License for more details.</p>
						
						<?php }elseif ($_REQUEST["step"] == "2") { ?>
						
						<p>Please select the type of installation</p>
						
						<p>
						<select name="install_type">
							<option value="1"<?php print ($default_install_type == "1") ? " selected" : "";?>>New Install</option>
							<option value="3"<?php print ($default_install_type == "3") ? " selected" : "";?>>Upgrade from cacti 0.8.x</option>
						</select>
						</p>
						
						<p>The following information has been determined from cacti's configuration file.
						If it is not correct, please edit 'include/config.php' before continuing.</p>
						
						<p class="code">
						<?php	print "Database User: $database_username<br>";
							print "Database Hostname: $database_hostname<br>";
							print "Database: $database_default<br>";
							print "Server Operating System Type: " . $config["cacti_server_os"] . "<br>"; ?>
						</p>
						
						<?php }elseif ($_REQUEST["step"] == "3") { ?>
						
						<p>Make sure make sure all of these values are correct before continuing.</p>
						<?php
						$i = 0;
						/* find the appropriate value for each 'config name' above by config.php, database,
						or a default for fall back */
						while (list($name, $array) = each($input)) {
							if (isset($input[$name])) {
								$current_value = $array["default"];
								
								/* run a check on the path specified only if specified above, then fill a string with
								the results ('FOUND' or 'NOT FOUND') so they can be displayed on the form */
								$form_check_string = "";
								
								if ($array["method"] == "textbox") {
									if (@file_exists($current_value)) {
										$form_check_string = "<font color='#008000'>[FOUND]</font> ";
									}else{
										$form_check_string = "<font color='#FF0000'>[NOT FOUND]</font> ";
									}
								}
								
								/* draw the acual header and textbox on the form */
								print "<p><strong>" . $form_check_string . $array["friendly_name"] . "</strong>";
								
								if (!empty($array["friendly_name"])) {
									print ": " . $array["description"];
								}else{
									print "<strong>" . $array["description"] . "</strong>";
								}
								
								print "<br>";
								
								switch ($array["method"]) {
								case 'textbox':
									form_text_box($name, $current_value, "", "", "40", "text");
									break;
								case 'drop_array':
									form_dropdown($name, $array["array"], "", "", $current_value, "", "");
									break;
								}
								
								print "<br></p>";
							}
							
							$i++;
						}?>
						
						<p><strong><font color="#FF0000">NOTE:</font></strong> Once you click "Finish",
						all of your settings will be saved and your database will be upgraded if this
						is an upgrade. You can change any of the settings on this screen at a later
						time by going to "Cacti Settings" from within Cacti.</p>
						
						<?php }elseif ($_REQUEST["step"] == "8") { ?>
						
						<p>Upgrade results:</p>
						
						<?php
						$current_version  = "";
						
						$fail_text = "<span style='color: red; font-weight: bold; font-size: 12px;'>[Fail]</span>&nbsp;";
						$success_text = "<span style='color: green; font-weight: bold; font-size: 12px;'>[Success]</span>&nbsp;";
						
						if (isset($_SESSION["sess_sql_install_cache"])) {
							while (list($index, $arr1) = each($_SESSION["sess_sql_install_cache"])) {
								while (list($version, $arr2) = each($arr1)) {
									while (list($status, $sql) = each($arr2)) {
										if ($current_version != $version) {
											$version_index = array_search($version, $cacti_versions);
											print "<p><strong>" . $cacti_versions{$version_index-1}  . " -> " . $cacti_versions{$version_index} . "</strong></p>\n";
										}
										
										print "<p class='code'>" . (($status == 0) ? $fail_text : $success_text) . nl2br($sql) . "</p>\n";
										
										$current_version = $version;
									}
								}
							}
						
							kill_session_var("sess_sql_install_cache");
						}else{
							print "<em>No SQL queries have been executed.</em>";
						}
						?>
						
						<?php }?>
						
						<p align="right"><input type="image" src="install_<?php if ($_REQUEST["step"] == "3") {?>finish<?php }else{?>next<?php }?>.gif" alt="<?php if ($_REQUEST["step"] == "3"){?>Finish<?php }else{?>Next<?php }?>"></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<input type="hidden" name="step" value="<?php print $_REQUEST["step"];?>">

</form>

</body>
</html>
