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

/* set default action */
if (!isset($_REQUEST["action"])) { 
	$action = ""; 
}else{
	$action = $_REQUEST["action"];
}


/* Get the username */
if (read_config_option("auth_method") == "2") { 
	/* Get the Web Basic Auth username and set action so we login right away */
	$action = "login";
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$username = $_SERVER["PHP_AUTH_USER"];
	} else {
		/* No user - Bad juju! */
		$username = "";
		auth_display_custom_error_message("Web Basic Authentication configured, but no username was passed from the web server.  Please make sure you have authentication enabled on the web server.");
		exit;
	}
}else{
	/* LDAP and Builtin get username from Form */
	if (isset($_POST["username"])) {
		$username = $_POST["username"];
	}else{
		$username = "";
	}
}


/* process login */
$copy_user = false;
$user_auth = false;
if ($action == 'login') {

	switch (read_config_option("auth_method")) {
	case "0":
		/* No auth, no action, also shouldn't get here */
		exit;
		break;
	case "2":
		/* Web Basic Auth */
		$copy_user = true;
		$user_auth = true;	
		$realm = 0;
		/* Locate user in database */
		$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 0");
		break;
	case "3": 
		/* LDAP Auth */ 
 		if (($_POST["realm"] == "ldap") && (strlen($_POST["password"]) > 0)) {
			/* Connect to LDAP server */
			$ldap_conn = ldap_connect(read_config_option("ldap_server"));
			if ($ldap_conn) {
				/* Create DN */
				$ldap_dn = str_replace("<username>",$username,read_config_option("ldap_dn"));
				/* Query LDAP directory */
				$ldap_response = @ldap_bind($ldap_conn,$ldap_dn,$_POST["password"]);
				$realm = 1;
				if ($ldap_response) {
					/* Auth ok */
					$user_auth = true;
					$copy_user = true;
					/* Locate user in database */
					$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = 1");
					/* Close LDAP connection */
					ldap_close($ldap_conn);
				}else{
					/* Error LDAP Connection - This bad because this is also a bind error.
					which means auth failure, not able to connect.  This will get fixed when
					the LDAP code is updated. */
					auth_display_custom_error_message("Unable to connect to LDAP Server.");
					exit;	
				}
			}else{
				/* Error intializing LDAP */
				auth_display_custom_error_message("Unable to initialize LDAP Server.");
				exit;
			}
		}

	case "1":
		/* Builtin Auth */
		if (!$user_auth) {
			/* if auth has not occured process for builtin - AKA Ldap fall through */
			$user = db_fetch_row("select * from user_auth where username='" . $username . "' and password = '" . md5($_POST["password"]) . "' and realm = 0");
		}
	}
	/* Create user from template if requested */
	if ((!sizeof($user)) && ($copy_user) && (read_config_option("user_template") != "0") && (strlen($username) > 0)) {
		/* check that template user exists */
		if (sizeof(db_fetch_assoc("select * from user_auth where username='" . read_config_option("user_template") . "' and realm = 0")) != 0) {
			/* template user found */
			user_copy(read_config_option("user_template"), $username, $realm);
			/* requery newly created user */
			$user = db_fetch_row("select * from user_auth where username='" . $username . "' and realm = " . $realm);
		}else{
			/* error */
			auth_display_custom_error_message("Template user \"" . read_config_option("user_template") . "\" does not exist.");
			exit;
		}
	}
	
	/* Guest account checking - Not for builtin */
	$guest_user = false;
	if ((!sizeof($user)) && ($user_auth) && (read_config_option("guest_user") != "0")) {
		/* Locate guest user record */
		$user = db_fetch_row("select * from user_auth where username='" . read_config_option("guest_user") . "'");
		if (sizeof($user)) {
			$guest_user = true;
		}else{
			/* error */
			auth_display_custom_error_message("Guest user \"" . read_config_option("guest_user") . "\" does not exist.");	
			exit;
		}
	}

	/* Process the user  */
	if (sizeof($user)) {
		/* make entry in the transactions log */
		if ($guest_user) {
			/* We know this is a guest, let's log it so everyone knows,
			username format is <guest user>(logged in Web Basic or LDAP user>) */
			$log_username = read_config_option("guest_user") . "(" . $username .")";
		}else{
			$log_username = $username;
		}
		db_execute("insert into user_log (username,user_id,result,ip,time) values('" . $log_username ."'," . $user["id"] . ",1,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");

		/* set the php session */
		$_SESSION["sess_user_id"] = $user["id"];

		/* handle "force change password" */
		if ($user["must_change_password"] == "on") {
			$_SESSION["sess_change_password"] = true;
		}

		/* ok, at the point the user has been sucessfully authenticated; so we must
		decide what to do next */
		switch ($user["login_opts"]) {
			case '1': /* referer */
				if (sizeof(db_fetch_assoc("select realm_id from user_auth_realm where realm_id=8 and user_id=" . $_SESSION["sess_user_id"])) == 0) {
					header("Location: graph_view.php");
				}else{
					header("Location: " . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php"));
				}
				break;
			case '2': /* default console page */
				header("Location: index.php"); break;
			case '3': /* default graph page */
				header("Location: graph_view.php"); break;
		}

		exit;
	}else{
		if ((!$guest_user) && ($user_auth)) {
			/* No guest account defined */
			auth_display_custom_error_message("Access Denied, please contact you Cacti Administrator.");
			exit;	
		}else{
			/* BAD username/password builtin and LDAP */
			db_execute("insert into user_log (username,user_id,result,ip,time) values('" . $username . "',0,0,'" . $_SERVER["REMOTE_ADDR"] . "',NOW())");		
		}
	}
}

/* auth_display_custom_error_message - displays a custom error message to the browser that looks like
     the pre-defined error messages
   @arg $message - the actual text of the error message to display */
function auth_display_custom_error_message($message) {
	/* kill the session */
	setcookie(session_name(),"",time() - 3600,"/");	
	/* print error */
	print "<html>\n<head>\n";
        print "     <title>cacti</title>\n";
        print "     <link href=\"include/main.css\" rel=\"stylesheet\">";
	print "</head>\n";
	print "<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">\n<br><br>\n";
	display_custom_error_message($message);
        print "</body>\n</html>\n";
}

?>


<html>
<head>
	<title>Login to Cacti</title>
	<STYLE TYPE="text/css">
	<!--
		BODY, TABLE, TR, TD {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;}
		A {text-decoration: none;}
		A:active { text-decoration: none;}
		A:hover {text-decoration: underline; color: #333333;}
		A:visited {color: Blue;}
	-->
	</style>
</head>
<body onload="document.login.username.focus()">
<form name="login" method="post" action="<?php print basename($_SERVER["PHP_SELF"]);?>">
<table align="center">
	<tr>
		<td colspan="2"><img src="images/auth_login.gif" border="0" alt=""></td>
	</tr>
	<?php
	if ($action == "login") {?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2"><font color="#FF0000"><strong>Invalid User Name/Password Please Retype:</strong></font></td>
	</tr>
	<?php }?>
	<tr height="10"><td></td></tr>
	<tr>
		<td colspan="2">Please enter your Cacti user name and password below:</td>
	</tr>
	<tr height="10"><td></td></tr>
	<tr>
		<td>User Name:</td>
		<td><input type="text" name="username" size="40" style="width: 295px;" value="<?php print $username; ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password" size="40" style="width: 295px;"></td>
	</tr>
	<?php
	if (read_config_option("auth_method") == "3") {?>
        <tr>
                <td>Realm:</td>
                <td>
			<select name="realm" style="width: 295px;">
				<option value="local">Local</option>
				<option value="ldap" selected>LDAP</option>
			</select>
		</td>
        </tr>
	<?php }?>
	<tr height="10"><td></td></tr>
	<tr>
		<td><input type="submit" value="Login"></td>
	</tr>
</table>
<input type="hidden" name="action" value="login">
</form>
</body>
</html>

