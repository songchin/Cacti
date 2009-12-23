<?php

function do_hook ($name) {
	$data = func_get_args();
	$data = api_plugin_hook ($name, $data);
	return $data;
}

function do_hook_function($name,$parm=NULL) {
	return api_plugin_hook_function ($name, $parm);
}

function api_user_realm_auth ($filename = '') {
	return api_plugin_user_realm_auth ($filename);
}

/**
 * This function executes a hook.
 * @param string $name Name of hook to fire
 * @return mixed $data
 */
function api_plugin_hook ($name) {
	global $config;

	$data = func_get_args();

	$result = db_fetch_assoc("SELECT name, file, function FROM plugin_hooks WHERE status = 1 AND hook = '$name'", false);
	if (count($result)) {
		foreach ($result as $hdata) {
			$p[] = $hdata['name'];
			if (file_exists(CACTI_BASE_PATH . '/plugins/' . $hdata['name'] . '/' . $hdata['file'])) {
				include_once(CACTI_BASE_PATH . '/plugins/' . $hdata['name'] . '/' . $hdata['file']);
			}
			$function = $hdata['function'];
			if (function_exists($function)) {
				if ($name == 'top_header_tabs' || $name == 'top_graph_header_tabs') {
					ob_start();
					$function($data);
					$output = ob_get_contents();
					$new_output = "";

					if (substr_count(strtolower($output), "<li")) {
						/* revised content */
						ob_end_flush();
					}else{
						$out_array = explode("<", $output);

						$selected  = false;
						$alt_text  = "";
						$href_text = "";
						$anchor    = false;
						$img       = false;
						if (sizeof($out_array)) {
						foreach($out_array as $element) {
							$attribs   = explode(" ", $element);
							if (sizeof($attribs)) {
								switch(strtolower($attribs[0])) {
								case "a":
									$anchor = true;
									$img    = false;
									break;
								case "img":
									$anchor = false;
									$img    = true;
									break;
								case "/a>":
									if ($href_text) {
										$new_output .= "<li class=\"" . ($selected ? "selected":"notselected") . "\"><a title=\"" . $alt_text . "\" href=\"" . $href_text . "\">" . $alt_text . "</a></li>";
										$alt_text  = "";
										$href_text = "";
										$selected  = false;
										$img       = false;
										$anchor    = false;
									}
									break;
								}

								if ($img || $anchor) {
									if (substr_count(strtolower($element), "src=") && $img) {
										if (substr_count($element, "_down.")) {
											$selected = true;
										}
									}

									if (substr_count(strtolower($element), "alt=") && $img) {
										$pos      = strpos($element, "alt=");
										$alt_text = substr($element,$pos+4);
										$delim    = substr($alt_text,0,1);
										$pos      = strpos($alt_text,$delim,2);
										$alt_text = substr($alt_text,1,$pos-1);
									}

									if (substr_count(strtolower($element), "href=") && $anchor) {
										$pos = strpos($element, "href=");
										$href_text = substr($element,$pos+5);
										$delim     = substr($href_text,0,1);
										$pos       = strpos($href_text,$delim,2);
										$href_text = substr($href_text,1,$pos-1);
									}
								}
							}
						}
						}

						if ($href_text) {
							$new_output .= "<li class=\"" . ($selected ? "selected":"notselected") . "\"><a title=\"" . $alt_text . "\" href=\"" . $href_text . "\">" . $alt_text . "</a></li>";
						}

						ob_clean();
						if ($new_output != "") {
							echo $new_output;
						}
					}
				}else{
					$function($data);
				}
			}
		}
	}

	/* Variable-length argument lists have a slight problem when */
	/* passing values by reference. Pity. This is a workaround.  */
	return $data;
}

function api_plugin_hook_function ($name, $parm=NULL) {
	global $config;

	$ret    = $parm;
	$result = db_fetch_assoc("SELECT name, file, function FROM plugin_hooks WHERE status = 1 AND hook = '$name'", false);

	if (count($result)) {
		foreach ($result as $hdata) {
			$p[] = $hdata['name'];
			if (file_exists(CACTI_BASE_PATH . '/plugins/' . $hdata['name'] . '/' . $hdata['file'])) {
				include_once(CACTI_BASE_PATH . '/plugins/' . $hdata['name'] . '/' . $hdata['file']);
			}
			$function = $hdata['function'];
			if (function_exists($function)) {
				$ret = $function($ret);
			}
		}
	}

	/* Variable-length argument lists have a slight problem when */
	/* passing values by reference. Pity. This is a workaround.  */
	return $ret;
}

function api_plugin_db_table_create ($plugin, $table, $data) {
	global $config, $database_default;
	include_once(CACTI_BASE_PATH . "/lib/database.php");

	$result = db_fetch_assoc("show tables from `" . $database_default . "`") or die (mysql_error());
	$tables = array();
	foreach($result as $index => $arr) {
		foreach ($arr as $t) {
			$tables[] = $t;
		}
	}
	if (!in_array($table, $tables)) {
		$c = 0;
		$sql = 'CREATE TABLE `' . $table . "` (\n";
		foreach ($data['columns'] as $column) {
			if (isset($column['name'])) {
				if ($c > 0)
					$sql .= ",\n";
				$sql .= '`' . $column['name'] . '`';
				if (isset($column['type']))
					$sql .= ' ' . $column['type'];
				if (isset($column['unsigned']))
					$sql .= ' unsigned';
				if (isset($column['NULL']) && $column['NULL'] == false)
					$sql .= ' NOT NULL';
				if (isset($column['NULL']) && $column['NULL'] == true && !isset($column['default']))
					$sql .= ' default NULL';
				if (isset($column['default']))
					$sql .= ' default ' . (is_numeric($column['default']) ? $column['default'] : "'" . $column['default'] . "'");
				if (isset($column['auto_increment']))
					$sql .= ' auto_increment';
				$c++;
			}
		}

		/* primary keys, multi-key columns are allowed */
		if (isset($data['primary'])) {
			$sql .= ",\n PRIMARY KEY (`";
			/* remove blanks */
			$no_blanks = str_replace(" ", "", $data['primary']);
			/* add tics to columns names */
			$sql .=  str_replace(",", "`, `", $no_blanks) . '`)';
		}

		/* "normal" keys, multi-key columns are allowed, multiple keys per run are allowed as well */
		if (isset($data['keys'])) {
			foreach ($data['keys'] as $key) {
				if (isset($key['name'])) {
					$sql .= ",\n KEY `" . $key['name'] . '` (`';
					if (isset($key['columns'])) {
						/* remove blanks */
						$no_blanks = str_replace(" ", "", $key['columns']);
						/* add tics to columns names */
						$sql .=  str_replace(",", "`, `", $no_blanks) . '`)';
					}
				}
			}
		}

		/* "unique" keys, multi-key columns are allowed, multiple keys per run are allowed as well */
		if (isset($data['unique'])) {
			foreach ($data['unique'] as $unique) {
				if (isset($unique['name'])) {
					$sql .= ",\n UNIQUE KEY `" . $unique['name'] . '` (`';
					if (isset($unique['columns'])) {
						/* remove blanks */
						$no_blanks = str_replace(" ", "", $unique['columns']);
						/* add tics to columns names */
						$sql .=  str_replace(",", "`, `", $no_blanks) . '`)';
					}
				}
			}
		}

		$sql .= ') TYPE = ' . $data['type'];

		if (isset($data['comment'])) {
			$sql .= " COMMENT = '" . $data['comment'] . "'";
		}
		if (db_execute($sql)) {
			db_execute("INSERT INTO plugin_db_changes (plugin, `table`, method) VALUES ('$plugin', '$table', 'create')");
		}
	} else {
		db_execute("INSERT INTO plugin_db_changes (plugin, `table`, method) VALUES ('$plugin', '$table', 'create')");
	}
}

function api_plugin_db_changes_remove ($plugin) {
	// Example: api_plugin_db_changes_remove ('thold');

	$tables = db_fetch_assoc("SELECT `table` FROM plugin_db_changes WHERE plugin = '$plugin' AND method ='create'", false);
	if (count($tables)) {
		foreach ($tables as $table) {
			db_execute("DROP TABLE `" . $table['table'] . "`;");
		}
		db_execute("DELETE FROM plugin_db_changes where plugin = '$plugin' AND method ='create'", false);
	}
	$columns = db_fetch_assoc("SELECT `table`, `column` FROM plugin_db_changes WHERE plugin = '$plugin' AND method ='addcolumn'", false);
	if (count($columns)) {
		foreach ($columns as $column) {
			db_execute('ALTER TABLE `' . $column['table'] . '` DROP `' . $column['column'] . '`');
		}
		db_execute("DELETE FROM plugin_db_changes where plugin = '$plugin' AND method = 'addcolumn'", false);
	}
}

function api_plugin_db_add_column ($plugin, $table, $column) {
	// Example: api_plugin_db_add_column ('thold', 'plugin_config', array('name' => 'test' . rand(1, 200), 'type' => 'varchar (255)', 'NULL' => false));

	global $config, $database_default;
	include_once(CACTI_BASE_PATH . '/lib/database.php');

	$result = db_fetch_assoc('show columns from `' . $table . '`') or die (mysql_error());
	$columns = array();
	foreach($result as $index => $arr) {
		foreach ($arr as $t) {
			$columns[] = $t;
		}
	}
	if (isset($column['name']) && !in_array($column['name'], $columns)) {
		$sql = 'ALTER TABLE `' . $table . '` ADD `' . $column['name'] . '`';
		if (isset($column['type']))
			$sql .= ' ' . $column['type'];
		if (isset($column['unsigned']))
			$sql .= ' unsigned';
		if (isset($column['NULL']) && $column['NULL'] == false)
			$sql .= ' NOT NULL';
		if (isset($column['NULL']) && $column['NULL'] == true && !isset($column['default']))
			$sql .= ' default NULL';
		if (isset($column['default']))
			$sql .= ' default ' . (is_numeric($column['default']) ? $column['default'] : "'" . $column['default'] . "'");
		if (isset($column['auto_increment']))
			$sql .= ' auto_increment';
		if (isset($column['after']))
			$sql .= ' AFTER ' . $column['after'];

		if (db_execute($sql)) {
			db_execute("INSERT INTO plugin_db_changes (plugin, `table`, `column`, `method`) VALUES ('$plugin', '$table', '" . $column['name'] . "', 'addcolumn')");
		}
	}
}

function api_plugin_install ($plugin) {
	global $config;
	include_once(CACTI_BASE_PATH . "/plugins/$plugin/setup.php");

	$exists = db_fetch_assoc("SELECT id FROM plugin_config WHERE directory = '$plugin'", false);
	if (!count($exists)) {
		db_execute("DELETE FROM plugin_config WHERE directory = '$plugin'");
	}

	$name = $author = $webpage = $version = '';
	$function = 'plugin_' . $plugin . '_version';
	if (function_exists($function)){
		$info = $function();
		$name = $info['longname'];
		$webpage = $info['homepage'];
		$author = $info['author'];
		$version = $info['version'];
	}

	db_execute("INSERT INTO plugin_config (directory, name, author, webpage, version) VALUES ('$plugin', '$name', '$author', '$webpage', '$version')");

	$function = 'plugin_' . $plugin . '_install';
	if (function_exists($function)){
		$function();
		$ready = api_plugin_check_config ($plugin);
		if ($ready) {
			// Set the plugin as "disabled" so it can go live
			db_execute("UPDATE plugin_config SET status = 4 WHERE directory = '$plugin'");
		} else {
			// Set the plugin as "needs configuration"
			db_execute("UPDATE plugin_config SET status = 2 WHERE directory = '$plugin'");
		}
	}
}

function api_plugin_uninstall ($plugin) {
	global $config;
	include_once(CACTI_BASE_PATH . "/plugins/$plugin/setup.php");
	// Run the Plugin's Uninstall Function first
	$function = 'plugin_' . $plugin . '_uninstall';
	if (function_exists($function)) {
		$function();
	}
	api_plugin_remove_hooks ($plugin);
	api_plugin_remove_realms ($plugin);
	db_execute("DELETE FROM plugin_config WHERE directory = '$plugin'");
	api_plugin_db_changes_remove ($plugin);
}

function api_plugin_check_config ($plugin) {
	global $config;
	include_once(CACTI_BASE_PATH . "/plugins/$plugin/setup.php");
	$function = 'plugin_' . $plugin . '_check_config';
	if (function_exists($function)) {
		return $function();
	}
	return TRUE;
}

function api_plugin_enable ($plugin) {
	$ready = api_plugin_check_config ($plugin);
	if ($ready) {
		api_plugin_enable_hooks ($plugin);
		db_execute("UPDATE plugin_config SET status = 1 WHERE directory = '$plugin'");
	}
}

function api_plugin_is_enabled ($plugin) {
	$status = db_fetch_cell("SELECT status FROM plugin_config WHERE directory = '$plugin'", false);
	if ($status == '1')
		return true;
	return false;
}

function api_plugin_disable ($plugin) {
	api_plugin_disable_hooks ($plugin);
	db_execute("UPDATE plugin_config SET status = 4 WHERE directory = '$plugin'");
}

function api_plugin_register_hook ($plugin, $hook, $function, $file) {
	$exists = db_fetch_assoc("SELECT id FROM plugin_hooks WHERE name = '$plugin' AND hook = '$hook'", false);
	if (!count($exists)) {
		$settings = array('config_settings', 'config_arrays', 'config_form');
		if (!in_array($hook, $settings)) {
			db_execute("INSERT INTO plugin_hooks (name, hook, function, file) VALUES ('$plugin', '$hook', '$function', '$file')");
		} else {
			db_execute("INSERT INTO plugin_hooks (name, hook, function, file, status) VALUES ('$plugin', '$hook', '$function', '$file', 1)");
		}
	}
}

function api_plugin_remove_hooks ($plugin) {
	db_execute("DELETE FROM plugin_hooks WHERE name = '$plugin'");
}

function api_plugin_enable_hooks ($plugin) {
	db_execute("UPDATE plugin_hooks SET status = 1 WHERE name = '$plugin'");
}

function api_plugin_disable_hooks ($plugin) {
	db_execute("UPDATE plugin_hooks SET status = 0 WHERE name = '$plugin' AND hook != 'config_settings' AND hook != 'config_arrays' AND hook != 'config_form'");
}

function api_plugin_register_realm ($plugin, $file, $display, $admin = false) {
	$exists = db_fetch_assoc("SELECT id FROM plugin_realms WHERE plugin = '$plugin' AND file = '$file'", false);
	if (!count($exists)) {
		db_execute("INSERT INTO plugin_realms (plugin, file, display) VALUES ('$plugin', '$file', '$display')");
		if ($admin) {
			$realm_id = db_fetch_assoc("SELECT id FROM plugin_realms WHERE plugin = '$plugin' AND file = '$file'", false);
			$realm_id = $realm_id[0]['id'] + 100;
			$user_id = db_fetch_assoc("SELECT id FROM user_auth WHERE username = 'admin'", false);
			if (count($user_id)) {
				$user_id = $user_id[0]['id'];
				$exists = db_fetch_assoc("SELECT realm_id FROM user_auth_realm WHERE user_id = $user_id and realm_id = $realm_id", false);
				if (!count($exists)) {
					db_execute("INSERT INTO user_auth_realm (user_id, realm_id) VALUES ($user_id, $realm_id)");
				}
			}
		}
	}
}

function api_plugin_remove_realms ($plugin) {
	$realms = db_fetch_assoc("SELECT id FROM plugin_realms WHERE plugin = '$plugin'", false);
	foreach ($realms as $realm) {
		$id = $realm['id'] + 100;
		db_execute("DELETE FROM user_auth_realm WHERE realm_id = '$id'");
	}
	db_execute("DELETE FROM plugin_realms WHERE plugin = '$plugin'");
}

function api_plugin_load_realms () {
	global $user_auth_realms, $user_auth_realm_filenames;
	$plugin_realms = db_fetch_assoc("SELECT * FROM plugin_realms ORDER BY plugin, display", false);
	if (count($plugin_realms)) {
		foreach ($plugin_realms as $plugin_realm) {
			$plugin_files = explode(',', $plugin_realm['file']);
			foreach($plugin_files as $plugin_file) {
				$user_auth_realm_filenames[$plugin_file] = $plugin_realm['id'] + 100;
			}
			$user_auth_realms[$plugin_realm['id'] + 100] = $plugin_realm['display'];
		}
	}
}

function api_plugin_user_realm_auth ($filename = '') {
	global $user_realms, $user_auth_realms, $user_auth_realm_filenames;
	/* list all realms that this user has access to */
	if (!isset($user_realms)) {
		if (read_config_option('global_auth') == 'on' || read_config_option('auth_method') != 0) {
			$user_realms = db_fetch_assoc("select realm_id from user_auth_realm where user_id=" . $_SESSION["sess_user_id"], false);
			$user_realms = array_rekey($user_realms, "realm_id", "realm_id");
		}else{
			$user_realms = $user_auth_realms;
		}
	}
	if ($filename != '') {
		if (isset($user_realms[$user_auth_realm_filenames{basename($filename)}]))
			return TRUE;
	}
	return FALSE;
}

function plugin_config_arrays () {
	global $menu;
	$menu[__('Configuration')]['plugins.php'] = __('Plugin Management');
	api_plugin_load_realms ();
}

function plugin_draw_navigation_text ($nav) {
	$nav["plugins.php:"] = array("title" => __("Plugin Management"), "mapping" => "index.php:", "url" => "plugins.php", "level" => "1");
	return $nav;
}

function api_plugin_upgrade_table ($plugin, $table, $data) {
	global $config, $database_default;
	include_once($config['library_path'] . '/database.php');
	$result = db_fetch_assoc('SHOW tables FROM `' . $database_default . '`') or die (mysql_error());
	$tables = array();
	foreach($result as $index => $arr) {
		foreach ($arr as $t) {
			$tables[] = $t;
		}
	}
	if (in_array($table, $tables)) {
		$cols = array();
		$result = db_fetch_assoc("SHOW columns FROM $table") or die ('ERROR: Can not display columns!');
		foreach($result as $index => $t) {
			$cols[$t['Field']] = $t;
		}
		foreach ($data['columns'] as $column) {
			if (isset($column['name'])) {
				if (isset($cols[$column['name']])) {
					$c = $cols[$column['name']];
					$ok = true;
					if (strstr($c['Type'], 'unsigned')) {
						$c['unsigned'] = true;
						$c['Type'] = trim(str_replace('unsigned', '', $c['Type']));
					}
					$c['Type'] = str_replace(' ', '', $c['Type']);
					$column['type'] = str_replace(' ', '', $column['type']);
					if (strtolower($column['type']) != strtolower($c['Type'])) {
						$ok = FALSE;
					}
					if (($column['NULL'] == FALSE && $c['Null'] != 'NO') || ($column['NULL'] == TRUE && $c['Null'] != 'YES')) {
						$ok = FALSE;
					}
					if (isset($column['auto_increment']) && ($column['auto_increment'] == 1 && isset($c['Extra']) && $c['Extra'] != 'auto_increment')) {
						$ok = FALSE;
					} else if (isset($c['Extra']) && $c['Extra'] == 'auto_increment' && !isset($column['auto_increment'])) {
						$ok = FALSE;
					}
					if (isset($column['unsigned']) && $column['unsigned'] != $c['unsigned']) {
						$ok = FALSE;
					}
					if (isset($column['default']) && $column['default'] != $c['Default']) {
						$ok = FALSE;
					}
					if (!$ok) {
						$sql = 'ALTER TABLE `' . $table . '` CHANGE `' . $column['name'] . '` `' . $column['name'] . '`';
						if (isset($column['type']))
							$sql .= ' ' . $column['type'];
						if (isset($column['unsigned']))
							$sql .= ' unsigned';
						if (isset($column['NULL']) && $column['NULL'] == FALSE)
							$sql .= ' NOT NULL';
						if (isset($column['NULL']) && $column['NULL'] == true && !isset($column['default']))
							$sql .= ' default NULL';
						if (isset($column['default']))
							$sql .= ' default ' . (is_numeric($column['default']) ? $column['default'] : "'" . $column['default'] . "'");
						if (isset($column['auto_increment']))
							$sql .= ' auto_increment';
						if (isset($column['after']))
							$sql .= ' AFTER ' . $column['after'];
						db_execute($sql);
					}
				} else {
					// Column does not exist
					api_plugin_db_add_column ($plugin, $table, $column);
				}
			}
		}
		// Find extra columns in the Database
		foreach ($cols as $c) {
			$found = FALSE;
			foreach ($data['columns'] as $d) {
				if ($c['Field'] == $d['name']) {
					$found = true;
				}
			}
			if ($found == FALSE) {
				// Extra Column in the Table
				//db_execute('ALTER TABLE `' . $table . '` DROP `' . $c['Field'] . '`');
			}
		}
		// Check for Primary
		$result = db_fetch_assoc('SHOW INDEX FROM `' . $table . '`') or die (mysql_error());
		if (isset($data['primary'])) {
			foreach ($data['keys'] as $d) {
				$found = FALSE;
				foreach($result as $index) {
					if ($index['Column_name'] == $d['name'] && $index['Key_name'] == 'PRIMARY') {
						$found = true;
					}
				}
				if (!$found) {
					db_execute('ALTER TABLE `' . $table . '` ADD PRIMARY KEY ( `' . $d['name'] . '` )');
				}
			}
		}
		// Check Indexes
		foreach ($data['keys'] as $d) {
			$found = FALSE;
			foreach($result as $index) {
				if ($index['Column_name'] == $d['name'] && $d['name'] == $index['Key_name']) {
					// INDEX exists, and its not PRIMARY
					$found = true;
				}
			}
			if (!$found) {
				if (isset($d['unique']) && $d['unique']) {
					db_execute('ALTER TABLE `' . $table . '` ADD UNIQUE ( `' . $d['name'] . '` )');
				} else {
					db_execute('ALTER TABLE `' . $table . '` ADD INDEX ( `' . $d['name'] . '` )');
				}
			}
		}
		// Check Type
		$result = db_fetch_row('SHOW TABLE STATUS FROM `' . $database_default . '` WHERE Name LIKE \'' . $table . '\'') or die (mysql_error());
		if (isset($result['Engine']) && strtolower($data['type']) != strtolower($result['Engine'])) {
			// Wrong Type
			db_execute('ALTER TABLE `' . $table . '` ENGINE = ' . $data['type']);
		}
	} else {
		// Table does not exist, so create it
		api_plugin_db_table_create ($plugin, $table, $data);
	}
}
