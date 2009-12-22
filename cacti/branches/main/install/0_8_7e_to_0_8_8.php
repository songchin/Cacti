<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
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
 | This code is designed, written, and maINTained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function upgrade_to_0_8_8() {

	/*
	 * Authenication System upgrade
	 */
	/* Create new tables */
	db_install_execute("0.8.8","
		CREATE TABLE `auth_control` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `name` varchar(100) NOT NULL default '',
		  `description` varchar(255) default NULL,
		  `object_type` int(8) unsigned NOT NULL default '0',
		  `enabled` int(1) unsigned NOT NULL default '1',
		  `updated_when` datetime NOT NULL default '0000-00-00 00:00:00',
		  `updated_by` varchar(100) NOT NULL default '',
		  `created_when` datetime NOT NULL default '0000-00-00 00:00:00',
		  `created_by` varchar(100) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `constraint_index` (`name`,`object_type`),
		  KEY `name` (`name`),
		  KEY `enabled` (`enabled`),
		  KEY `object_type` (`object_type`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `auth_data` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `control_id` mediumint(8) unsigned NOT NULL default '0',
		  `plugin_id` mediumint(8) unsigned NOT NULL default '0',
		  `category` varchar(25) NOT NULL default 'SYSTEM',
		  `name` varchar(100) NOT NULL default '',
		  `value` varchar(255) default NULL,
		  `enable_user_edit` int(1) unsigned NOT NULL default '0',
		  `updated_when` datetime NOT NULL default '0000-00-00 00:00:00',
		  `updated_by` varchar(100) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `constraint_index` (`control_id`,`plugin_id`,`category`,`name`),
		  KEY `control_id` (`control_id`),
		  KEY `name` (`name`),
		  KEY `plugin_id` (`plugin_id`),
		  KEY `category` (`category`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `auth_graph_perms` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `item_id` mediumint(8) unsigned NOT NULL default '0',
		  `type` mediumint(8) unsigned NOT NULL default '0',
		  `control_id` mediumint(8) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `item_id` (`item_id`),
		  KEY `type` (`type`),
		  KEY `control_id` (`control_id`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `auth_link` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `control_id` mediumint(8) unsigned NOT NULL default '0',
		  `parent_id` mediumint(8) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `control_id` (`control_id`),
		  KEY `parent_id` (`parent_id`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `auth_perm` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `name` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `category` varchar(100) default NULL,
		  `plugin_id` mediumint(8) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `plugin_id` (`plugin_id`),
		  KEY `category` (`category`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `auth_perm_link` (
		  `id` mediumint(8) unsigned NOT NULL auto_increment,
		  `control_id` mediumint(8) unsigned NOT NULL default '0',
		  `perm_id` mediumint(8) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `control_id` (`control_id`),
		  KEY `perm_id` (`perm_id`)
		) TYPE=MyISAM");
	/* Upgrade current users and permissions */

	/* add the poller id for hosts to allow for multiple pollers */
	db_install_execute("0.8.8", "ALTER TABLE `host`, ADD COLUMN `poller_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER id, ADD INDEX `poller_id`(`poller_id`);");

	/* add the poller id for poller_output to allow for multiple pollers */
	db_install_execute("0.8.8", "ALTER TABLE poller_output, ADD COLUMN `poller_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `time`, ADD INDEX `poller_id`(`poller_id`);");

	/* add the poller id for hosts to allow for multiple pollers */
	db_install_execute("0.8.8", "ALTER TABLE `poller`, ADD COLUMN `disabled` CHAR(2) DEFAULT '' AFTER `id`, ADD COLUMN `description` VARCHAR(45) NOT NULL DEFAULT '' AFTER `disabled`;");

	/* add rrd_compute_rpn for data source items */
	db_install_execute("0.8.8", "ALTER TABLE `data_template_rrd` ADD COLUMN `t_rrd_compute_rpn` CHAR(2) DEFAULT NULL AFTER `rrd_minimum`, ADD COLUMN `rrd_compute_rpn` VARCHAR(150) DEFAULT '' AFTER `t_rrd_compute_rpn`;");

	/* add --alt-y-grid as an option */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` ADD COLUMN `t_alt_y_grid` CHAR(2) DEFAULT 0 AFTER `auto_scale_rigid`, ADD COLUMN `alt_y_grid` CHAR(2) DEFAULT '' AFTER `t_alt_y_grid`;");

	/* increase size for upper/lower limit for use with |query_*| variables */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `lower_limit` VARCHAR(255)");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `upper_limit` VARCHAR(255)");

	/* add some fields required for hosts to table host_template */
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_community` VARCHAR(100) DEFAULT NULL AFTER `name`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_version` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `snmp_community`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_username` VARCHAR(50) DEFAULT NULL AFTER `snmp_version`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_password` VARCHAR(50) DEFAULT NULL AFTER `snmp_username`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_auth_protocol` CHAR(5) DEFAULT '' AFTER `snmp_password`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_priv_passphrase` VARCHAR(200) DEFAULT '' AFTER `snmp_auth_protocol`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_priv_protocol` CHAR(6) DEFAULT '' AFTER `snmp_priv_passphrase`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_context` VARCHAR(64) DEFAULT '' AFTER `snmp_priv_protocol`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_port` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '161' AFTER `snmp_context`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `snmp_timeout` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '500' AFTER `snmp_port`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `availability_method` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' AFTER `snmp_timeout`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `ping_method` SMALLINT(5) UNSIGNED DEFAULT '0' AFTER `availability_method`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `ping_port` INT(12) UNSIGNED DEFAULT '0' AFTER `ping_method`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `ping_timeout` INT(12) UNSIGNED DEFAULT '500' AFTER `ping_port`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `ping_retries` INT(12) UNSIGNED DEFAULT '2' AFTER `ping_timeout`");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `max_oids` INT(12) UNSIGNED DEFAULT '10' AFTER `ping_retries`");

	/* create a sites table */
	db_install_execute("0.8.8", "CREATE TABLE  `sites` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`name` varchar(100) NOT NULL default '',
			`address1` varchar(100) default '',
			`address2` varchar(100) default '',
			`city` varchar(50) default '',
			`state` varchar(20) default '',
			`postal_code` varchar(20) default '',
			`country` varchar(30) default '',
			`timezone` varchar(40) default '',
			`alternate_id` varchar(30) default '',
			`notes` text,
			PRIMARY KEY  (`id`),
			KEY `name` (`name`),
			KEY `city` (`city`),
			KEY `state` (`state`),
			KEY `postal_code` (`postal_code`),
			KEY `country` (`country`),
			KEY `alternate_id` (`alternate_id`)
			) ENGINE=MyISAM;");

	/* add a site column to the host table */
	db_install_execute("0.8.8", "ALTER TABLE `host`, ADD COLUMN `site_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER id, ADD INDEX `site_id`(`site_id`);");

	/*
	 * now update current entries of table host_template
	 * make sure to use current global default settings in order not to change
	 * current behaviour when creating new hosts from those templates
	 */
	$snmp_community	= read_config_option("snmp_community", true);
	$snmp_version = read_config_option("snmp_ver", true);
	$snmp_username = read_config_option("snmp_username", true);
	$snmp_password = read_config_option("snmp_password", true);
	$snmp_auth_protocol = read_config_option("snmp_auth_protocol", true);
	$snmp_priv_passphrase = read_config_option("snmp_priv_passphrase", true);
	$snmp_priv_protocol = read_config_option("snmp_priv_protocol", true);
	$snmp_context = read_config_option("snmp_context", true);
	$snmp_port = read_config_option("snmp_port", true);
	$snmp_timeout = read_config_option("snmp_timeout", true);
	$availability_method = read_config_option("availability_method", true);
	$ping_method = read_config_option("ping_method", true);
	$ping_port = read_config_option("ping_port", true);
	$ping_timeout = read_config_option("ping_timeout", true);
	$ping_retries = read_config_option("ping_retries", true);
	$max_oids = read_config_option("max_get_size", true);

	db_install_execute("0.8.8", "UPDATE `host_template` " .
			"SET  `snmp_community` = '" . $snmp_community . "' ," .
				" `snmp_version` = $snmp_version," .
				" `snmp_username` = '" . $snmp_username . "' ," .
				" `snmp_password` = '" . $snmp_password . "' ," .
				" `snmp_auth_protocol` = '" . $snmp_auth_protocol . "' ," .
				" `snmp_priv_passphrase` = '" . $snmp_priv_passphrase . "' ," .
				" `snmp_priv_protocol` = '" . $snmp_priv_protocol . "' ," .
				" `snmp_context` = '" . $snmp_context . "' ," .
				" `snmp_port` = $snmp_port," .
				" `snmp_timeout` = $snmp_timeout," .
				" `availability_method` = $availability_method," .
				" `ping_method` = $ping_method," .
				" `ping_port` = $ping_port," .
				" `ping_timeout` = $ping_timeout," .
				" `ping_retries` = $ping_retries," .
				" `max_oids` = $max_oids");

	/* add reindexing to host_template_snmp_query */
	db_install_execute("0.8.8", "ALTER TABLE `host_template_snmp_query` ADD COLUMN `reindex_method` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `snmp_query_id`");
	db_install_execute("0.8.8", "UPDATE `host_template_snmp_query` SET `reindex_method` = '1'");
	/*
	 * Plugin Architecture
	 */
	/* Create new tables */
	db_install_execute("0.8.8","
		CREATE TABLE `plugin_config` (
		  `id` int(8) NOT NULL auto_increment,
		  `directory` varchar(32) NOT NULL default '',
		  `name` varchar(64) NOT NULL default '',
		  `status` tinyint(2) NOT NULL default '0',
		  `author` varchar(64) NOT NULL default '',
		  `webpage` varchar(255) NOT NULL default '',
		  `version` varchar(8) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `directory` (`directory`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `plugin_db_changes` (
		  `id` int(10) NOT NULL auto_increment,
		  `plugin` varchar(16) NOT NULL default '',
		  `table` varchar(64) NOT NULL default '',
		  `column` varchar(64) NOT NULL default '',
		  `method` varchar(16) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  KEY `plugin` (`plugin`),
		  KEY `method` (`method`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8","
		CREATE TABLE `plugin_hooks` (
		  `id` int(8) NOT NULL auto_increment,
		  `name` varchar(32) NOT NULL default '',
		  `hook` varchar(64) NOT NULL default '',
		  `file` varchar(255) NOT NULL default '',
		  `function` varchar(128) NOT NULL default '',
		  `status` int(8) NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `hook` (`hook`),
		  KEY `status` (`status`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8", "INSERT INTO `plugin_hooks` VALUES (1, 'internal', 'config_arrays', '', 'plugin_config_arrays', 1)");
	db_install_execute("0.8.8", "INSERT INTO `plugin_hooks` VALUES (2, 'internal', 'draw_navigation_text', '', 'plugin_draw_navigation_text', 1)");

	db_install_execute("0.8.8","
		CREATE TABLE `plugin_realms` (
		  `id` int(8) NOT NULL auto_increment,
		  `plugin` varchar(32) NOT NULL default '',
		  `file` text NOT NULL default '',
		  `display` varchar(64) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  KEY `plugin` (`plugin`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8", "INSERT INTO `plugin_realms` VALUES (1, 'internal', 'plugins.php', 'Plugin Management')");

	/* wrong lower limit for generic OID graph template */
	db_install_execute("0.8.8", "UPDATE graph_templates_graph SET lower_limit='0', vertical_label='' WHERE id=47");

	/* rename templates */
	/* graph templates */
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='UCD-SNMP - diskTable - Hard Drive Space' where name='ucd/net - Available Disk Space'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='UCD-SNMP - systemStats - CPU Usage' where name='ucd/net - CPU Usage'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - ICMP - Ping Host' where name='Unix - Ping Latency'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - ps ax - Processes' where name='Unix - Processes'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - Uptime - Load Average' where name='Unix - Load Average'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - who - Logged in Users' where name='Unix - Logged in Users'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='UCD-SNMP - loadTable - Load Average' where name='ucd/net - Load Average'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - meminfo - Memory' where name='Linux - Memory Usage'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='UCD-SNMP - memory - Memory Usage' where name='ucd/net - Memory Usage'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='Linux Localhost - df - Hard Drive Space' where name='Unix - Available Disk Space'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='HOST-RESSOURCES - hrStorageTable - Hard Drive Space' where name='Host MIB - Available Disk Space'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='HOST-RESSOURCES - hrProcessorTable - CPU Utilization' where name='Host MIB - CPU Utilization'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='HOST-RESSOURCES - hrSystemNumUsers - Logged in Users' where name='Host MIB - Logged in Users'");
	db_install_execute("0.8.8", "UPDATE graph_templates SET name='HOST-RESSOURCES - hrSystemProcesses - Processes' where name='Host MIB - Processes'");
	/* data templates */
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - diskTable - Hard Drive Space'  WHERE name='ucd/net - Hard Drive Space'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - systemStats - CPU Usage: System'  WHERE name='ucd/net - CPU Usage - System'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - systemStats - CPU Usage: User'  WHERE name='ucd/net - CPU Usage - User'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - systemStats - CPU Usage: Nice'  WHERE name='ucd/net - CPU Usage - Nice'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - Uptime - Load Average'  WHERE name='Unix - Load Average'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - meminfo - Memory: Free'  WHERE name='Linux - Memory - Free'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - meminfo - Memory: Free Swap'  WHERE name='Linux - Memory - Free Swap'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - ps ax - Processes'  WHERE name='Unix - Processes'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - who - Logged in Users'  WHERE name='Unix - Logged in Users'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - ICMP - Ping Host'  WHERE name='Unix - Ping Host'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - loadTable - Load Average: 1 Minute'  WHERE name='ucd/net - Load Average - 1 Minute'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - loadTable - Load Average: 5 Minutes'  WHERE name='ucd/net - Load Average - 5 Minute'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - loadTable - Load Average: 15 Minutes'  WHERE name='ucd/net - Load Average - 15 Minute'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - memory - Buffers'  WHERE name='ucd/net - Memory - Buffers'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - memory - Free'  WHERE name='ucd/net - Memory - Free'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='Linux Localhost - df - Hard Drive Space'  WHERE name='Unix - Hard Drive Space'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='HOST-RESSOURCES - hrStorageTable - Hard Drive Space'  WHERE name='Host MIB - Hard Drive Space'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='HOST-RESSOURCES - hrProcessorTable - CPU Utilization'  WHERE name='Host MIB - CPU Utilization'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='HOST-RESSOURCES - hrSystemProcesses - Processes'  WHERE name='Host MIB - Processes'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='HOST-RESSOURCES - hrSystemNumUsers - Logged in Users'  WHERE name='Host MIB - Logged in Users'");
	db_install_execute("0.8.8", "UPDATE data_template SET name='UCD-SNMP - memory - Cache'  WHERE name='ucd/net - Memory - Cache'");
	/* data queries */
	db_install_execute("0.8.8", "UPDATE snmp_query SET name='UCD-SNMP - diskTable - Hard Drive Space' where name='ucd/net -  Get Monitored Partitions'");
	db_install_execute("0.8.8", "UPDATE snmp_query SET name='Linux Localhost - df - Hard Drive Space' where name='Unix - Get Mounted Partitions'");
	db_install_execute("0.8.8", "UPDATE snmp_query SET name='HOST-RESSOURCES - hrStorageTable - Hard Drive Space' where name='SNMP - Get Mounted Partitions'");
	db_install_execute("0.8.8", "UPDATE snmp_query SET name='HOST-RESSOURCES - hrProcessorTable - CPU Utilization' where name='SNMP - Get Processor Information'");

	/* enable lossless reindexing in Cacti */
	db_install_execute("0.8.8", "ALTER TABLE host_snmp_cache ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `oid`, ADD INDEX present USING BTREE (present)");
	db_install_execute("0.8.8", "ALTER TABLE poller_item ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `action`, ADD INDEX present USING BTREE (present)");
	db_install_execute("0.8.8", "ALTER TABLE poller_reindex ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `action`, ADD INDEX present USING BTREE (present)");

	/* add image storage to graph templates, data queries, and host templates */
	db_install_execute("0.8.8", "ALTER TABLE `data_template` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`");
	db_install_execute("0.8.8", "ALTER TABLE `snmp_query` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`;");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`;");


	/* Add SNMPv3 Context to SNMP Input Methods */
	/* first we must see if the user was smart enough to add it themselves */
	$context1 = db_fetch_row("SELECT id FROM data_input_fields WHERE data_input_id=1 AND data_name='snmp_context' AND input_output='in' AND type_code='snmp_context'");
	if ($context1 > 0) {
		# nop
	} else {
		db_install_execute("0.8.8", "INSERT INTO data_input_fields VALUES (DEFAULT, '8e42450d52c46ebe76a57d7e51321d36',1,'SNMP Context (v3)','snmp_context','in','',0,'snmp_context','','')");
	}
	$context2 = db_fetch_row("SELECT id FROM data_input_fields WHERE data_input_id=2 AND data_name='snmp_context' AND input_output='in' AND type_code='snmp_context'");
	if ($context2 > 0) {
		# nop
	} else {
		db_install_execute("0.8.8", "INSERT INTO data_input_fields VALUES (DEFAULT, 'b5ce68ca4e9e36d221459758ede01484',2,'SNMP Context (v3)','snmp_context','in','',0,'snmp_context','','')");
	}

	db_install_execute("0.8.8", "UPDATE data_input_fields SET name='SNMP Authentication Protocol (v3)' WHERE name='SNMP Authenticaion Protocol (v3)'");

	db_install_execute("0.8.8", "ALTER TABLE host ADD COLUMN polling_time decimal(10,5) NOT NULL DEFAULT '0.00000' AFTER `avg_time`");

	# graph_templates_graph
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_right_axis char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN right_axis varchar(20) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_right_axis_label char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN right_axis_label varchar(200) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_right_axis_format char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN right_axis_format varchar(200) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_only_graph char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN only_graph char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_full_size_mode char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN full_size_mode char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_no_gridfit char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN no_gridfit char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_x_grid char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN x_grid varchar(31) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_unit_length char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN unit_length varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_back char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_back char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_canvas char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_canvas char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_shadea char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_shadea char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_shadeb char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_shadeb char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_grid char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_grid char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_mgrid char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_mgrid char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_font char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_font char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_axis char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_axis char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_frame char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_frame char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_colortag_arrow char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN colortag_arrow char(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_font_render_mode char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN font_render_mode char(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_font_smoothing_threshold char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN font_smoothing_threshold int(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_graph_render_mode char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN graph_render_mode char(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_pango_markup char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN pango_markup varchar(255) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_interlaced char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN interlaced char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_tab_width char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN tab_width mediumint(4) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_watermark char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN watermark varchar(255) DEFAULT NULL");
}

