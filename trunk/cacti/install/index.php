<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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

header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");

include_once ("../include/form.php");
include ("../include/config.php");
include ("../include/config_settings.php");

$cacti_versions = array("0.8", "0.8.1", "0.8.2");

$old_cacti_version = db_fetch_cell("select cacti from version");

/* do a version check */
if ($old_cacti_version == $config["cacti_version"]) {
	print "	<p style='font-family: Verdana, Arial; font-size: 16px; font-weight: bold; color: red;'>Error</p>
		<p style='font-family: Verdana, Arial; font-size: 12px;'>You can only run this for new installs and 
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

/* cacti Web Root */
$input["path_webcacti"]["default"] = str_replace("/install", "", dirname($_SERVER["PHP_SELF"]));
$input["path_webcacti"]["type"] = "textbox";

/* Web Server Document Root */
$input["path_webroot"]["default"] = str_replace("\\\\", "/", $current_document_root);
$input["path_webroot"]["check"] = "";
$input["path_webroot"]["type"] = "textbox";

/* rrdtool Binary Path */
$input["path_rrdtool"]["check"] = "";
$input["path_rrdtool"]["type"] = "textbox";

if ($config["cacti_server_os"] == "unix") {
	$which_rrdtool = trim(exec("which rrdtool"));
	
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

/* php Binary Path */
$input["path_php_binary"]["check"] = "";
$input["path_php_binary"]["type"] = "textbox";

if ($config["cacti_server_os"] == "unix") {
	$which_php = trim(exec("which php"));
	
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
	$input["path_snmpwalk"]["check"] = "";
	$input["path_snmpwalk"]["type"] = "textbox";
	
	$which_snmpwalk = trim(exec("which snmpwalk"));
	
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
	$input["path_snmpget"]["check"] = "";
	$input["path_snmpget"]["type"] = "textbox";
	
	$which_snmpwalk = trim(exec("which snmpget"));
	
	if (!empty($which_snmpwalk)) {
		$input["path_snmpget"]["default"] = $which_snmpwalk;
	}elseif (read_config_option("path_snmpget") != "<DEFAULT>") {
		$input["path_snmpget"]["default"] = read_config_option("path_snmpget");
	}else{
		$input["path_snmpget"]["default"] = "/usr/local/bin/snmpget";
	}
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
	}elseif (($_REQUEST["step"] == "2") && (($_REQUEST["install_type"] == "1") || ($_REQUEST["install_type"] == "3"))) {
		$_REQUEST["step"] = "3";
	}elseif (($_REQUEST["step"] == "2") && ($_REQUEST["install_type"] == "2")) {
		$_REQUEST["step"] = "9";
	}elseif (($_REQUEST["step"] == "2") && ($_REQUEST["install_type"] == "2")) {
		$_REQUEST["step"] = "3";
	}elseif ($_REQUEST["step"] == "3") {
		$_REQUEST["step"] = "4";
	}elseif ($_REQUEST["step"] == "9") {
		$_REQUEST["step"] = "10";
	}elseif ($_REQUEST["step"] == "10") {
		$_REQUEST["step"] = "11";
	}elseif ($_REQUEST["step"] == "11") {
		$_REQUEST["step"] = "3";
	}
}

if ($_REQUEST["step"] == "4") {
	include_once("../include/snmp_functions.php");
	include_once("../include/utility_functions.php");
	
	$i = 0;
	
	/* get all items on the form and write values for them  */
	while (list($name, $array) = each($input)) {
		if (isset($_POST[$name])) {
			db_execute("update settings set value='" . $_POST[$name] . "' where name='$name'");
		}
	}
	
	setcookie(session_name(),"",time() - 3600,"/");
	
	/* just in case we have hard drive graphs to deal with */
	data_query(db_fetch_cell("select id from host where management_ip='127.0.0.1'"), 6);
	
	/* it's always a good idea to re-populate the poller cache to make sure everything is refreshed and
	up-to-date */
	repopulate_poller_cache();
	
	db_execute("delete from version");
	db_execute("insert into version (cacti) values ('" . $config["cacti_version"] . "')");
	
	header ("Location: ../index.php");
	exit;
}elseif ($_REQUEST["step"] == "11") {
	include ("update_to_0_8.php");
	
	$status_array = update_database($_REQUEST["db_name"], $_REQUEST["db_user"], $_REQUEST["db_pass"]);
}elseif (($_REQUEST["step"] == "3") && ($_REQUEST["install_type"] == "3")) {
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
			db_execute("alter table user_log add user_id mediumint(8) not null after username");
			db_execute("alter table user_log change time time datetime not null");
			db_execute("alter table user_log drop primary key");
			db_execute("alter table user_log add primary key (username, user_id, time)");
			db_execute("alter table user_auth add realm mediumint(8) not null after password");
			db_execute("update user_auth set realm = 1 where full_name='ldap user'");
			
		        $_src = db_fetch_assoc("select id, username from user_auth");
			
		        if (sizeof($_src) > 0) {
			        foreach ($_src as $item) {
                			db_execute("update user_log set user_id = " . $item["id"] . " where username = '" . $item["username"] . "'");
				}
			}
		}elseif ($cacti_versions[$i] == "0.8.2") {
			db_execute("ALTER TABLE `data_input_data_cache` ADD `host_id` MEDIUMINT( 8 ) NOT NULL AFTER `local_data_id`");
			db_execute("ALTER TABLE `host` ADD `disabled` CHAR( 2 ) , ADD `status` TINYINT( 2 ) NOT NULL");
			db_execute("update host_snmp_cache set field_name='ifName' where field_name='ifAlias' and snmp_query_id=1");
			db_execute("update snmp_query_graph_rrd_sv set text=REPLACE(text,'ifAlias','ifName') where (snmp_query_graph_id=1 or snmp_query_graph_id=13 or snmp_query_graph_id=14 or snmp_query_graph_id=16 or snmp_query_graph_id=9 or snmp_query_graph_id=2 or snmp_query_graph_id=3 or snmp_query_graph_id=4)");
			db_execute("update snmp_query_graph_sv set text=REPLACE(text,'ifAlias','ifName') where (snmp_query_graph_id=1 or snmp_query_graph_id=13 or snmp_query_graph_id=14 or snmp_query_graph_id=16 or snmp_query_graph_id=9 or snmp_query_graph_id=2 or snmp_query_graph_id=3 or snmp_query_graph_id=4)");
			db_execute("update host set disabled=''");
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
					<td bgcolor="#104075" class="header-text">cacti Installation Guide</td>
				</tr>
				<tr>
					<td width="100%" style="font-size: 12px;">
						<?php if ($_REQUEST["step"] == "1") { ?>
						
						<p>Thanks for taking the time to download and install cacti, the complete graphing 
						solution for your network. Before you can start making cool graphs, there are a few 
						pieces of data that cacti needs to know.</p>
						
						<p>Make sure you have read and followed the required steps needed to install cacti
						before continuing. Install information can be found for 
						<a href="docs/INSTALL.htm">Unix</a> and <a href="docs/INSTALL-WIN32.htm">Win32</a>-based operating systems.</p>
						
						<p>Also, if this is an upgrade, be sure to reading the <a href="docs/UPGRADE.htm">Upgrade</a> information file.</p>
						
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
							<option value="2"<?php print ($default_install_type == "2") ? " selected" : "";?>>Upgrade from cacti 0.6.8</option>
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
								
								if (isset($array["check"])) {
									if (@file_exists($current_value . $array["check"])) {
										$form_check_string = "<font color='#008000'>[FOUND]</font> ";
									}else{
										$form_check_string = "<font color='#FF0000'>[NOT FOUND]</font> ";
									}
								}
								
								/* draw the acual header and textbox on the form */
								print "<p><strong>" . $form_check_string . $settings[$name]["friendly_name"] . "</strong>";
								
								if (!empty($settings[$name]["friendly_name"])) {
									print ": " . $settings[$name]["description"];
								}else{
									print "<strong>" . $settings[$name]["description"] . "</strong>";
								}
								
								print "<br>";
								
								switch ($array["type"]) {
									case 'textbox':
										form_base_text_box($name, $current_value, "", "", "40", "text");
										print "<br></p>";
										break;
									case 'checkbox':
										form_base_checkbox($name,$current_value,$settings[$name]["description"],"");
										break;
								}
							}
							
							$i++;
						}?>
						
						<p><strong><font color="#FF0000">NOTE:</font></strong> Once you click "Finish",
						all of your settings will be saved and your database will be upgraded if this
						is an upgrade. You can change any of the settings on this screen at a later
						time by going to "cacti Settings" from within cacti.</p>
						
						<?php }elseif ($_REQUEST["step"] == "9") { ?>
						
						<p style='color: red; font-weight: bold;'>Make sure to read important upgrade notes below before continuing!</p>
						
						<p><strong>Script Output Syntax</strong></p>
				
						<p>The output syntax for scripts with multiple outputs has been changed! For instance, the following
						syntax was acceptable in 0.6.x:</p>
						
						<p><pre>0.12:0.05:0.01</pre></p>
						
						<p>Because of changes in the poller architecture, the following syntax is now <strong>required</strong>:</p>
						
						<p><pre>1min:0.12 5min:0.05 10min:0.01</pre></p>
						
						<p>The field names <em>1min</em>, <em>5min</em>, and <em>10min</em> derive directly from the
						output field names under "Data Input Methods". The output field names specified in cacti and the
						field names used in the output strings must match <strong>exactly</strong>, or your data will 
						not end up in the RRD file. Please note that none of this is required for a script that 
						only outputs one value.
						
						<p><strong>New Permissions</strong></p>
						
						<p>Because new permissions have been added in 0.8, you may find that 0.6.8 users such 
						as 'admin' get "Access Denied" messages to certain areas. As a precautionary measure, you
						must go into "User Administration" and manually give trusted users rights to the new areas.</p>
						
						<p><strong>Required PHP Version</strong></p>
						
						<p>Cacti 0.8 now requires PHP 4.1 or higher. This is because cacti makes use of PHP's super-global
						arrays.</p>
						
						<?php }elseif ($_REQUEST["step"] == "10") { ?>
						
						<p>You have chosen to upgrade from an old 0.6.8 installation to 0.8. Since 0.8 has
						a new database structure, your old data must be ported to the new table format. For
						the most part, everything from your previous installation should be ported. Keep in
						mind however that some things may need to be ported manually if the script does not
						import it correctly.</p>
						
						<p>To begin the import, you must specify the database hostname, username, password,
						and name of your old 0.6.8 database. The database user <strong>must</strong> have
						permissions to the old and new Cacti databases. The data will be copied from the old 
						database to the new leaving the old database completely unchanged.</p>
						
						<p><strong>As always, make sure you have database backups!</strong></p>
						
						<table>
							<tr>
								<td>
									Database Username:&nbsp;
								</td>
								<td>
									<input type="text" name="db_user" size="25" value="root">
								</td>
							</tr>
							<tr>
								<td>
									Database Password:&nbsp;
								</td>
								<td>
									<input type="text" name="db_pass" size="25" value="">
								</td>
							</tr>
							<tr>
								<td>
									Database Name:&nbsp;
								</td>
								<td>
									<input type="text" name="db_name" size="25" value="cacti_old">
								</td>
							</tr>
						</table>
						
						<p>The import process will begin when you click "Next". Please be patient as all of your
						current SNMP devices will be recached during this process. The results of the import will
						be displayed on the following screen.</p>
						
						<?php }elseif ($_REQUEST["step"] == "11") { ?>
						
						<p>Below is the status of your 0.6.8 -> 0.8 database import. Please make sure to take note
						of any errors, as those items might have to be individually imported.</p>
						
						<?php
						for ($i=0;($i<count($status_array));$i++) {
							while (list($type, $arr) = each($status_array[$i])) {
								$spew = false;
								
								if (isset($arr[0])) {
									$current_status = 0;
									$status_text = "... <span style='font-weight: bold; color: red;'>Fail</span><br>\n";
								}else{
									$current_status = 1;
									$status_text = "... <span style='color: navy;'>Success</span><br>\n";
								}
								
								if ($type == "user") {
									$spew = true;
									$current_message = "<strong>User</strong>: " . $arr[$current_status];
								}elseif ($type == "version") {
									$spew = true;
									$current_message = "<strong>Version Check</strong>";
								}elseif ($type == "user_acl") {
									$spew = true;
									$current_message = "<strong>User Permissions</strong>";
								}elseif ($type == "user_host") {
									$spew = true;
									$current_message = "<strong>Host Permissions</strong>";
								}elseif ($type == "user_log") {
									$spew = true;
									$current_message = "<strong>User Login Log</strong>";
								}elseif ($type == "data_input") {
									$spew = true;
									$current_message = "<strong>Data Input Source</strong>: " . $arr[$current_status];
								}elseif ($type == "data_input_field") {
									$error_spew_array{count($error_spew_array)} = "<strong>Field</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "host") {
									$spew = true;
									$current_message = "<strong>Host</strong>: " . $arr[$current_status];
								}elseif ($type == "host_recache") {
									$error_spew_array{count($error_spew_array)} = "<strong>Re-Cache SNMP Data</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "data_local") {
									$spew = true;
									$current_message = "<strong>Data Source</strong>: " . $arr[$current_status];
								}elseif ($type == "data_source") {
									$error_spew_array{count($error_spew_array)} = "<strong>Data Source Entry</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "data_source_item") {
									$error_spew_array{count($error_spew_array)} = "<strong>Data Source Item</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "data_source_data") {
									$error_spew_array{count($error_spew_array)} = "<strong>Data Source Data</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "data_source_rra") {
									$error_spew_array{count($error_spew_array)} = "<strong>Data Source -> RRA Mapping</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "cdef") {
									$spew = true;
									$current_message = "<strong>CDEF</strong>: " . $arr[$current_status];
								}elseif ($type == "cdef_item") {
									$error_spew_array{count($error_spew_array)} = "<strong>CDEF Item</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "graph_local") {
									$spew = true;
									$current_message = "<strong>Graph</strong>: " . $arr[$current_status];
								}elseif ($type == "graph") {
									$error_spew_array{count($error_spew_array)} = "<strong>Graph Entry</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "graph_item") {
									$error_spew_array{count($error_spew_array)} = "<strong>Graph Item</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}elseif ($type == "tree") {
									$spew = true;
									$current_message = "<strong>Graph Tree</strong>: " . $arr[$current_status];
								}elseif ($type == "tree_item") {
									$error_spew_array{count($error_spew_array)} = "<strong>Item</strong>: " . $arr[$current_status] . $status_text;
									if ($current_status == 0) { $spew_errors = true; }
								}
								
								if ($spew == true) {
									if ((count($error_spew_array) > 0) && ($spew_errors == true)) {
										for ($j=0;($j<count($error_spew_array));$j++) {
											print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $error_spew_array[$j];
										}
									}
									
									$error_spew_array = array();
									$spew_errors = false;
									
									print $current_message . $status_text;
								}
							}
						}
						
						?>
						
						<p>When you are finished examining your import results, click "Next" to proceed with the
						installation procedure.</p>
						
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
