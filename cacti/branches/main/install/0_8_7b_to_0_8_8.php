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
	/* add --alt-y-grid as an option */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` ADD COLUMN `t_alt_y_grid` CHAR(2) DEFAULT 0 AFTER `auto_scale_rigid`, ADD COLUMN `alt_y_grid` CHAR(2) DEFAULT '' AFTER `t_alt_y_grid`;");
	/* increase size for upper/lower limit for use with |query_*| variables */
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `lower_limit` VARCHAR(255)");
	db_install_execute("0.8.8", "ALTER TABLE `graph_templates_graph` MODIFY `upper_limit` VARCHAR(255)"); 
	
	/* 
	 * add some fields required for hosts to table host_template 
	 * */
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
	$snmp_community				= read_config_option("snmp_community");
	$snmp_version				= read_config_option("snmp_ver");
	$snmp_username				= read_config_option("snmp_username");
	$snmp_password				= read_config_option("snmp_password");
	$snmp_auth_protocol			= read_config_option("snmp_auth_protocol");
	$snmp_priv_passphrase		= read_config_option("snmp_priv_passphrase");
	$snmp_priv_protocol			= read_config_option("snmp_priv_protocol");
	$snmp_context				= read_config_option("snmp_context");
	$snmp_port					= read_config_option("snmp_port");
	$snmp_timeout				= read_config_option("snmp_timeout");
	$availability_method		= read_config_option("availability_method");
	$ping_method				= read_config_option("ping_method");
	$ping_port					= read_config_option("ping_port");
	$ping_timeout				= read_config_option("ping_timeout");
	$ping_retries				= read_config_option("ping_retries");
	$max_oids					= read_config_option("max_get_size");

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
	
	/* 
	 * add reindexing to host_template_snmp_query 
	 * */
	db_install_execute("0.8.8", "ALTER TABLE `host_template_snmp_query` ADD COLUMN `reindex_method` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `snmp_query_id`");
	db_install_execute("0.8.8", "UPDATE `host_template_snmp_query` SET `reindex_method` = '1'");
}
?>
