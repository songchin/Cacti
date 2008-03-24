<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2008 The Cacti Group                                 |
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

	/*
	 * now update current entries of table host_template
	 * make sure to use current global default settings in order not to change
	 * current behaviour when creating new hosts from those templates
	 */
	$snmp_community	= read_config_option("snmp_community");
	$snmp_version = read_config_option("snmp_ver");
	$snmp_username = read_config_option("snmp_username");
	$snmp_password = read_config_option("snmp_password");
	$snmp_auth_protocol = read_config_option("snmp_auth_protocol");
	$snmp_priv_passphrase = read_config_option("snmp_priv_passphrase");
	$snmp_priv_protocol = read_config_option("snmp_priv_protocol");
	$snmp_context = read_config_option("snmp_context");
	$snmp_port = read_config_option("snmp_port");
	$snmp_timeout = read_config_option("snmp_timeout");
	$availability_method = read_config_option("availability_method");
	$ping_method = read_config_option("ping_method");
	$ping_port = read_config_option("ping_port");
	$ping_timeout = read_config_option("ping_timeout");
	$ping_retries = read_config_option("ping_retries");
	$max_oids = read_config_option("max_get_size");

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
	db_install_execute("0.8.8", "INSERT INTO `plugin_hooks` VALUES (1, 'internal', 'config_arrays', '', 'plugin_config_arrays', 1");
	db_install_execute("0.8.8", "INSERT INTO `plugin_hooks` VALUES (2, 'internal', 'draw_navigation_text', '', 'plugin_draw_navigation_text', 1");

	db_install_execute("0.8.8","
		CREATE TABLE `plugin_realms` (
		  `id` int(8) NOT NULL auto_increment,
		  `plugin` varchar(32) NOT NULL default '',
		  `file` text NOT NULL default '',
		  `display` varchar(64) NOT NULL default '',
		  PRIMARY KEY  (`id`),
		  KEY `plugin` (`plugin`)
		) TYPE=MyISAM");
	db_install_execute("0.8.8", "INSERT INTO `plugin_realms` VALUES (1, 'internal', 'plugins.php', 'Plugin Management'");

}
?>
