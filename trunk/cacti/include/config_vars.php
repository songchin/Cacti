<?

include_once("database.php");

if ($do_not_read_config != true) {
	if (isset($config) == false) {
		/* make a connection to the database */
		$db_settings = db_fetch_assoc("select * from settings");
		
		if (sizeof($db_settings) > 0) {
		foreach ($db_settings as $setting) {
			$name = $setting[name];
			$config[$name][value] = db_fetch_cell("select value from settings where name='$name'");
		}
		}
	}
}

/* make sure this variable reflects your operating system type: 'unix' or 'win32' */
$config[cacti_server_os] = "unix";

/* reset this variable */
$do_not_read_config = false;

/* built-in snmp support */
$config["php_snmp_support"] = function_exists("snmpget");

/* make sure this variable reflects your operating system type: 'unix' or 'win32' */
$config[cacti_server_os] = "unix";

/* reset this variable */
$do_not_read_config = false;

/* colors */
$colors[dark_outline] = "454E53";
$colors[dark_bar] = "AEB4B7";
$colors[panel] = "E5E5E5";
$colors[panel_text] = "000000";
$colors[panel_link] = "000000";
$colors[light] = "F5F5F5";
$colors[alternate] = "E7E9F2";
$colors[panel_dark] = "C5C5C5";

$colors[header] = "00438C";
$colors[header_panel] = "6d88ad";
$colors[header_text] = "ffffff";
$colors[form_background_dark] = "E1E1E1";

$colors[form_alternate1] = "E5E5E5";
$colors[form_alternate2] = "F5F5F5";

/* path variables */
$paths[cacti] = $config["path_webroot"]["value"] . $config["path_webcacti"]["value"];
$paths[images] = "$paths[cacti]/graphs";
$paths[rra] = "$paths[cacti]/rra";
$paths[log] = "$paths[cacti]/log/rrd.log";

/* current cacti version */
$config[cacti_version] = "0.6.8";

?>
