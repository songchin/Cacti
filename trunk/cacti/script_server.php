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

$no_http_headers = true;

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
	exit(-1);
}

/* define STDOUT/STDIN file descriptors if not running under CLI */
if (php_sapi_name() != "cli") {
	define("STDIN", fopen('php://stdin', 'r'));
	define("STDOUT", fopen('php://stdout', 'w'));

	ini_set("max_execution_time", "296");
}

/* used for includes */
include_once(dirname(__FILE__) . "/include/config.php");
include_once(dirname(__FILE__) . "/lib/poller.php");

/* Record the calling environment */
if ($_SERVER["argc"] >= 2) {
	if ($_SERVER["argv"][1] == "cactid")
		$environ = "cactid";
	else
		if (($_SERVER["argv"][1] == "cmd.php") || ($_SERVER["argv"][1] == "cmd"))
			$environ = "cmd";
		else
			$environ = "other";

	if ($_SERVER["argc"] == 3)
		$poller_id = $_SERVER["argv"][2];
	else
		$poller_id = 1;
} else {
	$environ = "cmd";
	$poller_id = 1;
}

/* PHP Bug.  Not yet logged */
if ($config["cacti_server_os"] == "win32") {
	$guess = substr(__FILE__,0,2);
	if ($guess == strtoupper($guess)) {
		cacti_log("The PHP Script Server MUST be started using the full path to the file and in lower case.  This is a PHP Bug!!!", SEV_ERROR, $poller_id, 0, 0, false, FACIL_SCPTSVR);
		exit(-1);
	}
}

if(read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
	cacti_log("SERVER: " . $environ, SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
	cacti_log("GETCWD: " . strtolower(strtr(getcwd(),"\\","/")), SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
	cacti_log("DIRNAM: " . strtolower(strtr(dirname(__FILE__),"\\","/")), SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
	cacti_log("FILENM: " . __FILE__, SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
}

/* send status back to the server */
if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_HIGH) {
	cacti_log("PHP Script Server has Started - Parent is " . $environ, SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
}

fputs(STDOUT, "PHP Script Server has Started - Parent is " . $environ . "\n");
fflush(STDOUT);

/* process waits for input and then calls functions as required */
while (1) {
	$result = "";
	$in_string = fgets(STDIN,255);
	$in_string = rtrim(strtr(strtr($in_string,'\r',''),'\n',''));
	if (strlen($in_string)>0) {
		if (($in_string != "quit") && ($in_string != "")) {
			/* get file to be included */
			$inc = substr($in_string,0,strpos($in_string," "));
			$remainder = substr($in_string,strpos($in_string," ")+1);

			/* parse function from command */
			if (!strpos($remainder," ")) {
				$cmd = $remainder;
				$parm = "";
				$preparm = "";
			} else {
				$cmd = substr($remainder,0,strpos($remainder," "));

				// parse parameters from remainder of command
				$preparm = substr($remainder,strpos($remainder," ")+1);
				$parm = explode(" ",$preparm);
			}

			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("INCLUDE: '". $inc . "' SCRIPT: '" .$cmd . "' CMD: '" . $preparm . "'", SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
			}

			/* check for existance of function.  If exists call it */
			if ($cmd != "") {
				if (!function_exists($cmd)) {
					if (file_exists($inc)) {
						/* quirk in php R5.0RC3, believe it or not.... */
						/* path must be lower case */
						$inc = strtolower($inc);

						/* set this variable so the calling script can determine if it was called
						 * by the script server or stand-alone */
						$called_by_script_server = true;

						include_once($inc);
					} else {
						cacti_log("PHP Script File to be included, does not exist", SEV_WARNING, $poller_id, 0, 0, false, FACIL_SCPTSVR);
					}
				}
			} else {
				cacti_log("PHP Script Server encountered errors parsing the command", SEV_WARNING, $poller_id, 0, 0, false, FACIL_SCPTSVR);
			}

			if (function_exists($cmd)) {
				if ($parm == "") {
					$result = call_user_func($cmd);
				} else {
					$result = call_user_func_array($cmd, $parm);
				}

				if (!validate_result($result)) {
					$result = "U";
				}

				if (strpos($result,"\n") != 0) {
					fputs(STDOUT, $result);
					fflush(STDOUT);
				} else {
					fputs(STDOUT, $result . "\n");
					fflush(STDOUT);
				}

				if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
					cacti_log("SERVER: " . $in_string . " output " . $result, SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
				}
			} else {
				cacti_log("Function does not exist", SEV_WARNING, $poller_id, 0, 0, false, FACIL_SCPTSVR);
				fputs(STDOUT, "WARNING: Function does not exist\n");
			}
		}elseif ($in_string == "quit") {
			fputs(STDOUT, "PHP Script Server Shutdown request received, exiting\n");
			if (read_config_option("log_verbosity") == POLLER_VERBOSITY_DEBUG) {
				cacti_log("PHP Script Server Shutdown request received, exiting", SEV_DEBUG, $poller_id, 0, 0, false, FACIL_SCPTSVR);
			}
			break;
		}else {
			cacti_log("Problems with input, command ingnored", SEV_WARNING, $poller_id, 0, 0, false, FACIL_SCPTSVR);
			fputs(STDOUT, "ERROR: Problems with input\n");
		}
	}else {
		cacti_log("Input Expected, Script Server Terminating", SEV_ERROR, $poller_id, 0, 0, false, FACIL_SCPTSVR);
		fputs(STDOUT, "ERROR: Input Expected, Script Server Terminating\n");
		exit (-1);
	}
}
?>
