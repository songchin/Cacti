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
	db_install_execute("0.8.8", "ALTER TABLE `host` ADD COLUMN `poller_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER id, ADD INDEX `poller_id`(`poller_id`);");

	/* add the poller id for poller_output to allow for multiple pollers */
	db_install_execute("0.8.8", "ALTER TABLE poller_output ADD COLUMN `poller_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `time`, ADD INDEX `poller_id`(`poller_id`);");

	/* add the poller id for hosts to allow for multiple pollers */
	db_install_execute("0.8.8", "ALTER TABLE `poller` ADD COLUMN `disabled` CHAR(2) DEFAULT '' AFTER `id`, ADD COLUMN `description` VARCHAR(45) NOT NULL DEFAULT '' AFTER `disabled`;");

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
	db_install_execute("0.8.8", "ALTER TABLE `host` ADD COLUMN `site_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER id, ADD INDEX `site_id`(`site_id`);");

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

	/* add image storage to graph templates, data queries, and device templates */
	db_install_execute("0.8.8", "ALTER TABLE `data_template` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`");
	db_install_execute("0.8.8", "ALTER TABLE `snmp_query` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`;");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `description` varchar(255) NOT NULL AFTER `name`;");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `image` varchar(64) NOT NULL AFTER `description`;");

	/* changes for template propagation */
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `override_defaults` CHAR(2) NOT NULL DEFAULT '' AFTER `image`;");
	db_install_execute("0.8.8", "ALTER TABLE `host_template` ADD COLUMN `override_permitted` CHAR(2) NOT NULL DEFAULT 'on' AFTER `override_defaults`;");
	db_install_execute("0.8.8", "ALTER TABLE `host` ADD COLUMN `template_enabled` CHAR(2) NOT NULL DEFAULT '' AFTER `host_template_id`;");

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
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN right_axis_format mediumint(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_only_graph char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN only_graph char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_full_size_mode char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN full_size_mode char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_no_gridfit char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN no_gridfit char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_x_grid char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN x_grid mediumint(8) unsigned NOT NULL default '0'");
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
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN font_render_mode varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_font_smoothing_threshold char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN font_smoothing_threshold int(8) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_graph_render_mode char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN graph_render_mode varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_pango_markup char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN pango_markup char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_interlaced char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN interlaced char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_tab_width char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN tab_width mediumint(4) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_watermark char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN watermark varchar(255) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_force_rules_legend char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN force_rules_legend char(2) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_legend_position char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN legend_position varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_legend_direction char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN legend_direction varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_grid_dash char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN grid_dash varchar(10) DEFAULT NULL");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN t_border char(2) DEFAULT '0'");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_graph ADD COLUMN border char(2) DEFAULT NULL");
	# create new table graph_templates_xaxis
	db_install_execute("0.8.8","
		CREATE TABLE `graph_templates_xaxis` (
		  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Table Id',
		  `hash` varchar(32) NOT NULL DEFAULT '' COMMENT 'Unique Hash',
		  `name` varchar(100) NOT NULL DEFAULT '' COMMENT 'Name of X-Axis Preset',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  COMMENT='X-Axis Presets'");
	db_install_execute("0.8.8", "INSERT INTO `graph_templates_xaxis` VALUES(1, 'a09c5cab07a6e10face1710cec45e82f', 'Default')");
	# create new table graph_templates_xaxis_items
	db_install_execute("0.8.8","
		CREATE TABLE `graph_templates_xaxis_items` (
		  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Row Id',
		  `hash` varchar(32) NOT NULL DEFAULT '' COMMENT 'Unique Hash',
		  `item_name` varchar(100) NOT NULL COMMENT 'Name of this Item',
		  `xaxis_id` int(12) unsigned NOT NULL DEFAULT '0' COMMENT 'Id of related X-Axis Preset',
		  `timespan` int(12) unsigned NOT NULL DEFAULT '0' COMMENT 'Graph Timespan that shall match this Item',
		  `gtm` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'Global Grid Timespan',
		  `gst` smallint(4) unsigned NOT NULL COMMENT 'Global Grid Timespan Steps',
		  `mtm` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'Major Grid Timespan',
		  `mst` smallint(4) unsigned NOT NULL COMMENT 'Major Grid Timespan Steps',
		  `ltm` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'Label Grid Timespan',
		  `lst` smallint(4) unsigned NOT NULL COMMENT 'Label Grid Timespan Steps',
		  `lpr` int(12) unsigned NOT NULL COMMENT 'Label Placement Relative',
		  `lfm` varchar(100) NOT NULL COMMENT 'Label Format',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM COMMENT='Items for X-Axis Presets'");
	db_install_execute("0.8.8", "INSERT INTO `graph_templates_xaxis_items` VALUES(1, '60c2066a1c45fab021d32fe72cbf4f49', 'Day', 1, 86400, 'HOUR', 4, 'HOUR', 2, 'HOUR', 2, 23200, '%H')");
	db_install_execute("0.8.8", "INSERT INTO `graph_templates_xaxis_items` VALUES(2, 'd867f8fc2730af212d0fd6708385cf89', 'Week', 1, 604800, 'DAY', 1, 'DAY', 1, 'DAY', 1, 259200, '%d')");
	db_install_execute("0.8.8", "INSERT INTO `graph_templates_xaxis_items` VALUES(3, '06304a1840da88f3e0438ac147219003', 'Month', 1, 2678400, 'WEEK', 1, 'WEEK', 1, 'WEEK', 1, 1296000, '%W')");
	db_install_execute("0.8.8", "INSERT INTO `graph_templates_xaxis_items` VALUES(4, '33ac10e60fd855e74736bee43bda4134', 'Year', 1, 31622400, 'MONTH', 2, 'MONTH', 1, 'MONTH', 2, 15811200, '%m')");

	/* upgrade to the graph trees */
	db_install_execute("0.8.8", "ALTER TABLE `graph_tree_items`
		ADD COLUMN `parent_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
		ADD COLUMN `site_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0' AFTER `title`;");

	/* make tree's a per user object.  System tree's have a user_id of 0 */
	db_install_execute("0.8.8", "ALTER TABLE `graph_tree` ADD COLUMN `user_id` INTEGER UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `user_id`(`user_id`);");

	/* get all nodes whose parent_id is not 0 */
	$tree_items = db_fetch_assoc("SELECT * FROM graph_tree_items WHERE order_key NOT LIKE '___000%';");
	if (sizeof($tree_items)) {
	foreach($tree_items AS $item) {
		$translated_key = rtrim($item["order_key"], "0\r\n");
		$missing_len    = strlen($translated_key) % CHARS_PER_TIER;
		if ($missing_len > 0) {
			$translated_key .= substr("000", 0, $missing_len);
		}
		$parent_key_len = strlen($translated_key) - CHARS_PER_TIER;
		$parent_key     = substr($translated_key, 0, $parent_key_len);
		$parent_id      = db_fetch_cell("SELECT id FROM graph_tree_items WHERE graph_tree_id=" . $item["graph_tree_id"] . " AND order_key LIKE '" . $parent_key . "000%'");
		if ($parent_id != "") {
			db_execute("UPDATE graph_tree_items SET parent_id=$parent_id WHERE id=" . $item["id"]);
		}else{
			cacti_log("Some error occurred processing children", false);
		}
	}
	}

	/* make the poller's ip address varchar() */
	db_install_execute("0.8.8", "ALTER TABLE poller CHANGE COLUMN ip_address varchar(30) not null default ''");

	/* insert the default poller into the database */
	db_install_execute("0.8.8", "INSERT INTO `poller` VALUES (1,'','Main Poller','localhost','127.0.0.1','0000-00-00 00:00:00');");

	/* update all devices to use poller 1, or the main poller */
	db_install_execute("0.8.8", "UPDATE host SET poller_id=1 WHERE poller_id=0");

	/* update the poller_items table to reflect the host change */
	db_install_execute("0.8.8", "UPDATE poller_item SET poller_id=1 WHERE poller_id=0");

	/* rename host -> device for tables and columns
	 * we have some updates to those tables in this file already
	 * so please take care not to change sequence */
	db_install_execute("0.8.8", "ALTER TABLE data_local DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` )");
	db_install_execute("0.8.8", "ALTER TABLE graph_local DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` )");
	db_install_execute("0.8.8", "ALTER TABLE graph_tree_items DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` ), CHANGE `host_grouping_type` `device_grouping_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1");
	db_install_execute("0.8.8", "RENAME TABLE `host`  TO `device`");
	db_install_execute("0.8.8", "RENAME TABLE `host_graph`  TO `device_graph`");
	db_install_execute("0.8.8", "ALTER TABLE device_graph CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL");
	db_install_execute("0.8.8", "RENAME TABLE `host_snmp_cache`  TO `device_snmp_cache`");
	db_install_execute("0.8.8", "ALTER TABLE device_snmp_cache DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` )");
	db_install_execute("0.8.8", "RENAME TABLE `host_snmp_query`  TO `device_snmp_query`");
	db_install_execute("0.8.8", "ALTER TABLE device_snmp_query DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` )");
	db_install_execute("0.8.8", "RENAME TABLE `host_template`  TO `device_template`");
	db_install_execute("0.8.8", "RENAME TABLE `host_template_graph`  TO `device_template_graph`");
	db_install_execute("0.8.8", "ALTER TABLE device_template_graph DROP INDEX `host_template_id`, CHANGE `host_template_id` `device_template_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_template_id` ( `device_template_id` )");
	db_install_execute("0.8.8", "RENAME TABLE `host_template_snmp_query`  TO `device_template_snmp_query`");
	db_install_execute("0.8.8", "ALTER TABLE device_template_snmp_query DROP INDEX `host_template_id`, CHANGE `host_template_id` `device_template_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_template_id` ( `device_template_id` )");
	db_install_execute("0.8.8", "ALTER TABLE poller_item DROP INDEX `host_id`, CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL, ADD INDEX `device_id` ( `device_id` )");
	db_install_execute("0.8.8", "ALTER TABLE poller_reindex CHANGE `host_id` `device_id` MEDIUMINT(8) UNSIGNED NOT NULL");
	db_install_execute("0.8.8", "ALTER TABLE user_auth CHANGE `policy_hosts` `policy_devices` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1");

	# table column updates using REPLACE
	db_install_execute("0.8.8", "UPDATE data_template_data SET name=REPLACE(name,'|host_','|device_') WHERE name like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE graph_templates_graph SET title=REPLACE(title,'|host_','|device_') WHERE title like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE graph_templates_graph SET upper_limit=REPLACE(upper_limit,'|host_','|device_') WHERE upper_limit like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE graph_templates_graph SET lower_limit=REPLACE(lower_limit,'|host_','|device_') WHERE lower_limit like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE graph_templates_graph SET vertical_label=REPLACE(vertical_label,'|host_','|device_') WHERE vertical_label like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE snmp_query_graph_rrd_sv SET `text`=REPLACE(`text`,'|host_','|device_') WHERE `text` like '%%|host_%%'");
	db_install_execute("0.8.8", "UPDATE snmp_query_graph_sv SET `text`=REPLACE(`text`,'|host_','|device_') WHERE `text` like '%%|host_%%'");

	/* New Indexes */
	db_install_execute("0.8.8","ALTER TABLE `data_input_data` 		ADD INDEX `data_template_data_id` 				(`data_template_data_id`)");
	db_install_execute("0.8.8","ALTER TABLE `data_local` 			ADD INDEX `device_id_snmp_query_id_snmp_index` 	(`device_id`,`snmp_query_id`,`snmp_index`)");
	db_install_execute("0.8.8","ALTER TABLE `data_template_data` 	ADD INDEX `data_source_path` 					(`data_source_path`)");
	db_install_execute("0.8.8","ALTER TABLE `data_template_rrd` 	ADD INDEX `local_data_id_data_source_name`  	(`local_data_id`,`data_source_name`)");
	db_install_execute("0.8.8","ALTER TABLE `device_snmp_cache` 	ADD INDEX `device_id_snmp_query_id_snmp_index` 	(`device_id`,`snmp_query_id`,`snmp_index`)");
	db_install_execute("0.8.8","ALTER TABLE `device_snmp_cache` 	ADD INDEX `device_id_snmp_query_id` 			(`device_id`,`snmp_query_id`)");
	db_install_execute("0.8.8","ALTER TABLE `graph_templates_item` 	ADD INDEX `graph_template_id_local_graph_id`  	(`graph_template_id`,`local_graph_id`)");
	db_install_execute("0.8.8","ALTER TABLE `graph_templates_item` 	ADD INDEX `local_graph_template_item_id` 		(`local_graph_template_item_id`)");
	db_install_execute("0.8.8","ALTER TABLE `poller_item` 			ADD INDEX `local_data_id_rrd_path` 				(`local_data_id`,`rrd_path`)");
	db_install_execute("0.8.8","ALTER TABLE `poller_item` 			ADD INDEX `device_id_rrd_next_step` 			(`device_id`,`rrd_next_step`)");
	db_install_execute("0.8.8","ALTER TABLE `poller_item` 			ADD INDEX `device_id_snmp_port` 				(`device_id`,`snmp_port`)");
	db_install_execute("0.8.8","ALTER TABLE `user_log` 				ADD INDEX `user_id`								(`user_id`)");

	/* Create new tables */
	db_install_execute("0.8.8","CREATE TABLE `log` (
			`id` bigint(20) unsigned NOT NULL default '0',
			`timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
			`facility` tinyint(1) unsigned NOT NULL default '0',
			`severity` int(1) NOT NULL default '0',
			`poller_id` smallint(5) unsigned NOT NULL default '0',
			`device_id` mediumint(8) unsigned NOT NULL default '0',
			`data_id` mediumint(8) unsigned NOT NULL default '0',
			`username` varchar(100) NOT NULL default 'system',
			`source` varchar(50) NOT NULL default 'localhost',
			`plugin_name` varchar(64) NOT NULL default '',
			`message` text NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `facility` (`facility`),
			KEY `severity` (`severity`),
			KEY `device_id` (`device_id`),
			KEY `data_id` (`data_id`),
			KEY `poller_id` (`poller_id`),
			KEY `username` (`username`),
			KEY `timestamp` (`timestamp`),
			KEY `plugin_name` (`plugin_name`)
			) TYPE=MyISAM");

	/* changes to insert VDEF into table graph_templates_item just behind CDEF */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_item` ADD `vdef_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 AFTER `cdef_id`");

	/* create new table VDEF */
	db_install_execute("0.8.8", "CREATE TABLE vdef (
  		id mediumint(8) unsigned NOT NULL auto_increment,
  		hash varchar(32) NOT NULL default '',
  		name varchar(255) NOT NULL default '',
  		PRIMARY KEY  (id)
		) TYPE=MyISAM;");

	/* fill table VDEF */
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (1, 'e06ed529238448773038601afb3cf278', 'Maximum');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (2, 'e4872dda82092393d6459c831a50dc3b', 'Minimum');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (3, '5ce1061a46bb62f36840c80412d2e629', 'Average');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (4, '06bd3cbe802da6a0745ea5ba93af554a', 'Last (Current)');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (5, '631c1b9086f3979d6dcf5c7a6946f104', 'First');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (6, '6b5335843630b66f858ce6b7c61fc493', 'Total: Current Data Source');");
	db_install_execute("0.8.8", "INSERT INTO `vdef` VALUES (7, 'c80d12b0f030af3574da68b28826cd39', '95th Percentage: Current Data Source');");

	/* create new table VDEF_ITEMS */
	db_install_execute("0.8.8", "CREATE TABLE vdef_items (
  		id mediumint(8) unsigned NOT NULL auto_increment,
  		hash varchar(32) NOT NULL default '',
  		vdef_id mediumint(8) unsigned NOT NULL default 0,
  		sequence mediumint(8) unsigned NOT NULL default 0,
  		type tinyint(2) NOT NULL default 0,
  		value varchar(150) NOT NULL default '',
  		PRIMARY KEY  (id),
  		KEY vdef_id (vdef_id)
		) TYPE=MyISAM;");

	/* fill table VDEF */
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (1, '88d33bf9271ac2bdf490cf1784a342c1', 1, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (2, 'a307afab0c9b1779580039e3f7c4f6e5', 1, 2, 1, '1');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (3, '0945a96068bb57c80bfbd726cf1afa02', 2, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (4, '95a8df2eac60a89e8a8ca3ea3d019c44', 2, 2, 1, '2');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (5, 'cc2e1c47ec0b4f02eb13708cf6dac585', 3, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (6, 'a2fd796335b87d9ba54af6a855689507', 3, 2, 1, '3');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (7, 'a1d7974ee6018083a2053e0d0f7cb901', 4, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (8, '26fccba1c215439616bc1b83637ae7f3', 4, 2, 1, '5');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (9, 'a8993b265f4c5398f4a47c44b5b37a07', 5, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (10, '5a380d469d611719057c3695ce1e4eee', 5, 2, 1, '6');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (11, '65cfe546b17175fad41fcca98c057feb', 6, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (12, 'f330b5633c3517d7c62762cef091cc9e', 6, 2, 1, '7');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (13, 'f1bf2ecf54ca0565cf39c9c3f7e5394b', 7, 1, 4, 'CURRENT_DATA_SOURCE');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (14, '11a26f18feba3919be3af426670cba95', 7, 2, 6, '95');");
	db_install_execute("0.8.8", "INSERT INTO `vdef_items` VALUES (15, 'e7ae90275bc1efada07c19ca3472d9db', 7, 3, 1, '8');");

	# graph_templates_items: split LINEx into LINE and a line_width of x
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_item ADD COLUMN line_width DECIMAL(4,2) DEFAULT 0 AFTER graph_type_id");
	db_install_execute("0.8.8", "UPDATE graph_templates_item SET `line_width`=1 WHERE `graph_type_id`=4"); # LINE1
	db_install_execute("0.8.8", "UPDATE graph_templates_item SET `line_width`=2 WHERE `graph_type_id`=5"); # LINE2
	db_install_execute("0.8.8", "UPDATE graph_templates_item SET `line_width`=3 WHERE `graph_type_id`=6"); # LINE3

	# graph_templates_items: add DASHES and DASH-OFFSET
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_item ADD COLUMN dashes varchar(20) DEFAULT NULL AFTER line_width");
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_item ADD COLUMN dash_offset mediumint(4) DEFAULT NULL AFTER dashes");

	# graph_templates_items: add TEXTALIGN
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_item ADD COLUMN textalign varchar(10) default NULL AFTER consolidation_function_id");

	# graph_templates_items: add SHIFT
	db_install_execute("0.8.8", "ALTER TABLE graph_templates_item ADD COLUMN shift char(2) default NULL AFTER vdef_id");

	/* implement per device threads setting for spine */
	db_install_execute("0.8.8", "ALTER TABLE device ADD COLUMN device_threads tinyint(2) unsigned NOT NULL default '1' AFTER max_oids");
	db_install_execute("0.8.8", "ALTER TABLE device_template ADD COLUMN device_threads tinyint(2) unsigned NOT NULL default '1' AFTER max_oids");

	/* new cdef's for background colorization */
	$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='2544acefc5fef30366c71336166ed141';");
	if ($cdef_id == 0) {
		db_install_execute("0.8.8", "INSERT INTO `cdef` VALUES(DEFAULT, '2544acefc5fef30366c71336166ed141', 'Time: Daytime')");
		$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='2544acefc5fef30366c71336166ed141';");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'ac0dea239ef3279c9b5ee04990fd4ec0', $cdef_id, 1, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '12f2bd71d5cbc078b9712c54d21c4f59', $cdef_id, 2, 6, '86400')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'bf35d7e5ae6df56398ea0f34a77311fc', $cdef_id, 3, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '31a9b3ff3b402f0446e6f6454b4d47c2', $cdef_id, 4, 4, 'TIME_SHIFT_START')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '996b718fc70353deb676e9037af9eadd', $cdef_id, 5, 1, '23')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '9c48bd2133670fd5158264ac25df6bb6', $cdef_id, 6, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '50c205e8bd5bb19b7fbee0ec2dee44cb', $cdef_id, 7, 6, '86400')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '14ee4ad2c7f91ab6406e1ecec6f4bcdc', $cdef_id, 8, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '38023f18060f2586e3504bbdd2634cc3', $cdef_id, 9, 4, 'TIME_SHIFT_END')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '1dbfee1b96a11492e58128ee8de93925', $cdef_id, 10, 1, '21')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6979b0680858c8d153530d9390f6a4e9', $cdef_id, 11, 1, '37')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'f9d37c6480c3555c9d6d2d8910ef2da7', $cdef_id, 12, 1, '36')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6c2604fd53780532c93c16d82c0337fd', $cdef_id, 13, 4, 'CURRENT_DATA_SOURCE')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'c2652379ba1c6523dc036e0a312536c4', $cdef_id, 14, 2, '3')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '63bf07a965b64fc41faa4bf01ae8a39d', $cdef_id, 15, 1, '29')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '2a9dea57a4f5d12cd0e2e66a31186a35', $cdef_id, 16, 1, '36')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '014839ebf8261c501d1da6c2c5217a0c', $cdef_id, 17, 4, 'CURRENT_DATA_SOURCE')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '01c946b79d68fad871e6e9437cba924f', $cdef_id, 18, 2, '3')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '4d0879e3c65c5af4e35d41a1631dcbe5', $cdef_id, 19, 1, '29')");
	}

	$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='8bd388f585b624a7bbad97101a2b7ee9';");
	if ($cdef_id == 0) {
		db_install_execute("0.8.8", "INSERT INTO `cdef` VALUES(DEFAULT, '8bd388f585b624a7bbad97101a2b7ee9', 'Time: Nighttime')");
		$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='8bd388f585b624a7bbad97101a2b7ee9';");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '1c9452055499efaddded29c74ee21880', $cdef_id, 1, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '36af4d7c5a8acf09bda1a3a5f1409979', $cdef_id, 2, 6, '86400')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '74cf8897d5ada9da271c64e82a1384ac', $cdef_id, 3, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '053c5efacd6787b6e41ed109043ba256', $cdef_id, 4, 4, 'TIME_SHIFT_START')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'da39b6410ab37833842511f46182717d', $cdef_id, 5, 1, '21')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '652afbee7025a256b8dc3c49e75b27fc', $cdef_id, 6, 1, '37')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '26a63ba997e1f904c71bb7c9eb5e76e5', $cdef_id, 7, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6f83ed61e0743176f03dd790f31521ea', $cdef_id, 8, 6, '86400')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6b49d9dc72576a7ada160f0befc77c85', $cdef_id, 9, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '22f0dd9a5e0e189424ea29fe1383e29d', $cdef_id, 10, 4, 'TIME_SHIFT_END')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'd3f3a319e8fcfac10bd06fb247d236af', $cdef_id, 11, 1, '23')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '1cf7208bfa84c61f788f327500b712a6', $cdef_id, 12, 1, '37')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'c29025779a287d2f7b946e9ffbba3c24', $cdef_id, 13, 1, '36')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '690852ea78bf45796ef21947e27528be', $cdef_id, 14, 4, 'CURRENT_DATA_SOURCE')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '09061dcd9762280ffd3994c8274b19f8', $cdef_id, 15, 2, '3')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '60be0afe23bef9fdb7e6cabd9067eb32', $cdef_id, 16, 1, '29')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'f4a6609839d199ecb12c2f05b5d3a7b6', $cdef_id, 17, 1, '29')");
	}

	$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='b4ef0a1c5e471dc6bae6a13ace5c57e7';");
	if ($cdef_id == 0) {
		db_install_execute("0.8.8", "INSERT INTO `cdef` VALUES(DEFAULT, 'b4ef0a1c5e471dc6bae6a13ace5c57e7', 'Time: Weekend')");
		$cdef_id = 	db_fetch_cell("SELECT id FROM cdef WHERE hash='b4ef0a1c5e471dc6bae6a13ace5c57e7';");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'd4f93d57657e6c3ae2053a4a760a0c7b', $cdef_id, 1, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '00a793341980c41728c6ee665718001c', $cdef_id, 2, 6, '604800')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '0a7eaf7192e5e44a425f5e8986850190', $cdef_id, 3, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'ceb07e26bf15c561b12004c5e32d7f1f', $cdef_id, 4, 6, '172800')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '3a3bfafebd173fdbbd8c07d2e2dd661f', $cdef_id, 5, 1, '23')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '4c080ecaaa7260886ea148869d4d0456', $cdef_id, 6, 1, '42')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'bd57afcd9879e29e29bb796ba8d6188d', $cdef_id, 7, 6, '604800')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'cd14cd9adfbae04973a75b90880e7d64', $cdef_id, 8, 2, '5')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '3bed46dd43a64d54acc4f0723cff0bc7', $cdef_id, 9, 6, '345600')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6fa62ee12bb8ba8936e39ea4303f92fd', $cdef_id, 10, 1, '21')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'f26848c08c2fb385126f90107494ce64', $cdef_id, 11, 1, '37')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'b8a5dde83327cac6705cdaa58300153b', $cdef_id, 12, 1, '36')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'f6aa118b35e269101ca3049cc4a323db', $cdef_id, 13, 4, 'CURRENT_DATA_SOURCE')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '967beb159b1ea744460ff3439ab205eb', $cdef_id, 14, 2, '3')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'f30028a71a1f4333703c70f8e499b03a', $cdef_id, 15, 1, '29')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '6888be191630a0964fdb9eaeb01cecaf', $cdef_id, 16, 1, '36')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '77c456204e43a9053c68b51750d5df75', $cdef_id, 17, 4, 'CURRENT_DATA_SOURCE')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, 'ce271b7a9809646a1fe4a7cd286fd98a', $cdef_id, 18, 2, '3')");
		db_install_execute("0.8.8", "INSERT INTO `cdef_items` VALUES(DEFAULT, '8bcd193850b37953ffe940fdf2a26aa6', $cdef_id, 19, 1, '29')");
	}

}

