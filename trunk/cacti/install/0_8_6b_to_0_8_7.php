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

function upgrade_to_0_8_7() {
	// ALTER TABLE `data_input_fields` DROP `sequence`;
	db_install_execute("0.8.7", "ALTER TABLE `host` CHANGE `min_time` `min_time` decimal(9,5) default '99999.99', CHANGE `max_time` `max_time` decimal(9,5) default '0.00000', CHANGE `cur_time` `cur_time` decimal(9,5) default '0.00000', CHANGE `avg_time` `avg_time` decimal(9,5) default '0.00000';");
	db_install_execute("0.8.7", "ALTER TABLE `host` ADD `poller_id` smallint(5) unsigned NOT NULL default '0' AFTER `id`;");
	db_install_execute("0.8.7", "ALTER TABLE `host` ADD `availability_method` smallint(5) unsigned NOT NULL default '1' AFTER `snmp_timeout`;");
	db_install_execute("0.8.7", "ALTER TABLE `host` ADD `ping_method` smallint(5) unsigned default '' AFTER `availability_method`;");
	db_install_execute("0.8.7", "ALTER TABLE `host` CHANGE `snmp_username` `snmpv3_auth_username` varchar(50), CHANGE `snmp_password` `snmpv3_auth_password` varchar(50);");
	db_install_execute("0.8.7", "ALTER TABLE `host` ADD `snmpv3_auth_protocol` varchar(5) AFTER `snmpv3_auth_password`, ADD `snmpv3_priv_passphrase` varchar(200) AFTER `snmpv3_auth_protocol`, ADD `snmpv3_priv_protocol` varchar(5) AFTER `snmpv3_priv_passphrase`;");
	db_install_execute("0.8.7", "ALTER TABLE `data_input` ADD `reserved` tinyint unsigned NOT NULL default '0' AFTER `id`;");
	db_install_execute("0.8.7", "UPDATE `data_input` set reserved = '1' where hash = '3eb92bb845b9660a7445cf9740726522' or hash = 'bf566c869ac6443b0c75d1c32b5a350e' or hash = '80e9e4c4191a5da189ae26d0e237f015' or hash = '332111d8b54ac8ce939af87a7eac0c06';");
	db_install_execute("0.8.7", "ALTER TABLE `poller` ADD `active` varchar(5) default 'On' AFTER `id`;");
	db_install_execute("0.8.7", "ALTER TABLE `poller` CHANGE `ip_address` `name` varchar(150) NOT NULL default 'Description';");
	db_install_execute("0.8.7", "ALTER TABLE `user_auth` ADD column `enabled` tinyint(1) unsigned NOT NULL default '1';");
	db_install_execute("0.8.7", "ALTER TABLE `poller_item` ADD `availability_method` smallint(5) unsigned NOT NULL default '1' AFTER `snmp_timeout`;");
	db_install_execute("0.8.7", "ALTER TABLE `poller_item` ADD `ping_method` smallint(5) unsigned default '' AFTER `availability_method`;");
	db_install_execute("0.8.7", "ALTER TABLE `poller_item` CHANGE `snmp_username` `snmpv3_auth_username` varchar(50), CHANGE `snmp_password` `snmpv3_auth_password` varchar(50);");
	db_install_execute("0.8.7", "ALTER TABLE `poller_item` ADD `snmpv3_auth_protocol` varchar(5) AFTER `snmpv3_auth_password`, ADD `snmpv3_priv_passphrase` varchar(200) AFTER `snmpv3_auth_protocol`, ADD `snmpv3_priv_protocol` varchar(5) AFTER `snmpv3_priv_passphrase`;");
	db_install_execute("0.8.7", "ALTER TABLE `user_auth` ADD `password_expire_length` int(4) unsigned NOT NULL default '0', ADD `password_change_last` datetime NOT NULL;");
	db_install_execute("0.8.7", "ALTER TABLE `graph_templates_graph` ADD `t_x_grid` char(2) default '0' AFTER `width`, ADD `x_grid` varchar(50) default NULL AFTER `t_x_grid`;");
	db_install_execute("0.8.7", "ALTER TABLE `graph_templates_graph` ADD `t_y_grid` char(2) default '0' AFTER `x_grid`, ADD `y_grid` varchar(50) default NULL AFTER `t_y_grid`;");
	db_install_execute("0.8.7", "ALTER TABLE `graph_templates_graph` ADD `t_y_grid_alt` char(2) default '0' AFTER `y_grid`, ADD `y_grid_alt` char(2) default NULL AFTER `t_y_grid_alt`;");
	db_install_execute("0.8.7", "ALTER TABLE `graph_templates_graph` ADD `t_no_minor` char(2) default '0' AFTER `y_grid_alt`, ADD `no_minor` char(2) default NULL AFTER `t_no_minor`;");
	db_install_execute("0.8.7", "ALTER TABLE `graph_templates_graph` ADD `t_unit_length` char(2) default '0' AFTER `unit_value`, ADD `unit_length` varchar(5) default '' AFTER `t_unit_length`;");
	db_install_execute("0.8.7", "ALTER TABLE `user_auth` ADD `created` datetime NOT NULL;");
	db_install_execute("0.8.7", "UPDATE `user_auth` SET `created` = NOW() WHERE `created` = `0000-00-00 00:00:00`;");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `name` = 'SNMP Password (v3)' WHERE `name` = 'SNMP Password';");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `name` = 'SNMP Username (v3)' WHERE `name` = 'SNMP Username';");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `data_name` = 'snmpv3_auth_username' WHERE `data_name` = 'snmp_username';");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `data_name` = 'snmpv3_auth_password' WHERE `data_name` = 'snmp_password';");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `type_code` = 'snmpv3_auth_username' WHERE `type_code` = 'snmp_username';");
	db_install_execute("0.8.7", "UPDATE `data_input_fields` SET `type_code` = 'snmpv3_auth_password' WHERE `type_code` = 'snmp_password';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('0979c0fb287db7db9fa9adddbb399aa3','1','SNMP Authority Protocol','snmpv3_auth_protocol','in','','0','snmpv3_auth_protocol','','on';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('a5dfa3c1fe626393994a4f28d83d0c63','1','SNMP Privacy Passphrase','snmpv3_priv_passphrase','in','','0','snmpv3_priv_passphrase','','on';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('f986f1acfd61582c3bf035ecd985b49f','1','SNMP Privacy Protocol','snmpv3_priv_protocol','in','','0','snmpv3_priv_protocol','','on';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('aa9632293ac20ecd87f5e4691fc244f6','2','SNMP Authority Protocol','snmpv3_auth_protocol','in','','0','snmpv3_auth_protocol','','on';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('b9a06e0ff7c042506a0adf013db5a533','2','SNMP Privacy Passphrase','snmpv3_priv_passphrase','in','','0','snmpv3_priv_passphrase','','on';");
	db_indtall_execute("0.8.7", "INSERT INTO `data_input_fields` FIELDS (hash,data_input_id,name,data_name,input_output,update_rra,sequence,type_code,regexp_match,allow_nulls) VALUES ('7c3011fb886b6345ed761a173dffd120','2','SNMP Privacy Protocol','snmpv3_priv_protocol','in','','0','snmpv3_priv_protocol','','on';");
}
?>