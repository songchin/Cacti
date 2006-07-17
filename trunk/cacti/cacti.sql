-- 
-- Table structure for table `auth_control`
-- 

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
  KEY `name` (`name`),
  KEY `enabled` (`enabled`),
  KEY `object_type` (`object_type`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_control`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `auth_data`
-- 

CREATE TABLE `auth_data` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `control_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `enable_user_edit` int(1) unsigned NOT NULL default '0',
  `plugin_id` mediumint(8) unsigned NOT NULL default '0',
  `value` varchar(255) default NULL,
  `updated_when` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by` varchar(100) NOT NULL default '',
  `created_when` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `control_id` (`control_id`),
  KEY `name` (`name`),
  KEY `plugin_id` (`plugin_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_data`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `auth_graph_perms`
-- 

CREATE TABLE `auth_graph_perms` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `item_id` mediumint(8) unsigned NOT NULL default '0',
  `type` mediumint(8) unsigned NOT NULL default '0',
  `control_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `type` (`type`),
  KEY `control_id` (`control_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_graph_perms`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `auth_link`
-- 

CREATE TABLE `auth_link` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `control_id` mediumint(8) unsigned NOT NULL default '0',
  `parent_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `control_id` (`control_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_link`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `auth_perm`
-- 

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
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_perm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `auth_perm_link`
-- 

CREATE TABLE `auth_perm_link` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `control_id` mediumint(8) unsigned NOT NULL default '0',
  `perm_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `control_id` (`control_id`),
  KEY `perm_id` (`perm_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `auth_perm_link`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_input`
-- 

CREATE TABLE `data_input` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(200) NOT NULL default '',
  `input_string` varchar(255) default NULL,
  `type_id` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_input`
-- 

INSERT INTO `data_input` VALUES (3, 274, 'Unix - Get Free Disk Space', '<path_cacti>/scripts/diskfree.sh <partition>', 1);
INSERT INTO `data_input` VALUES (5, 79, 'Unix - Get Logged In Users', 'perl <path_cacti>/scripts/unix_users.pl <username>', 1);
INSERT INTO `data_input` VALUES (6, 362, 'Linux - Get Memory Usage', 'perl <path_cacti>/scripts/linux_memory.pl <grepstr> <blah>', 1);
INSERT INTO `data_input` VALUES (7, 0, 'Unix - Get System Processes', 'perl <path_cacti>/scripts/unix_processes.pl', 1);
INSERT INTO `data_input` VALUES (8, 47, 'Unix - Get TCP Connections', 'perl <path_cacti>/scripts/unix_tcp_connections.pl <grepstr>', 1);
INSERT INTO `data_input` VALUES (9, 0, 'Unix - Get Web Hits', 'perl <path_cacti>/scripts/webhits.pl <log_path>', 1);
INSERT INTO `data_input` VALUES (10, 8, 'Unix - Ping Host', 'perl <path_cacti>/scripts/ping.pl <ip>', 1);
INSERT INTO `data_input` VALUES (11, 97, 'Unix - Get Load Average', 'perl <path_cacti>/scripts/loadavg_multi.pl', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `data_input_fields`
-- 

CREATE TABLE `data_input_fields` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `hash` varchar(32) NOT NULL default '',
  `data_input_id` mediumint(8) unsigned NOT NULL default '0',
  `field_input_type` tinyint(1) unsigned NOT NULL default '1',
  `field_input_value` varchar(100) NOT NULL default '',
  `name` varchar(200) NOT NULL default '',
  `data_name` varchar(50) NOT NULL default '',
  `input_output` char(3) NOT NULL default '',
  `update_rrd` tinyint(1) unsigned NOT NULL default '0',
  `regexp_match` varchar(100) NOT NULL default '',
  `allow_empty` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `u_field_name` (`data_input_id`,`data_name`,`input_output`),
  KEY `data_input_id` (`data_input_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_input_fields`
-- 

INSERT INTO `data_input_fields` VALUES (15, 'edfd72783ad02df128ff82fc9324b4b9', 3, 1, '', 'Disk Partition', 'partition', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (16, '8b75fb61d288f0b5fc0bd3056af3689b', 3, 1, '', 'Kilobytes Free', 'kilobytes', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (20, 'c0cfd0beae5e79927c5a360076706820', 5, 1, '', 'Username (Optional)', 'username', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (21, '52c58ad414d9a2a83b00a7a51be75a53', 5, 1, '', 'Logged In Users', 'users', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (22, '05eb5d710f0814871b8515845521f8d7', 6, 1, '', 'Grep String', 'grepstr', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (23, '86cb1cbfde66279dbc7f1144f43a3219', 6, 1, '', 'Result (in Kilobytes)', 'kilobytes', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (24, 'd5a8dd5fbe6a5af11667c0039af41386', 7, 1, '', 'Number of Processes', 'proc', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (25, '8848cdcae831595951a3f6af04eec93b', 8, 1, '', 'Grep String', 'grepstr', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (26, '3d1288d33008430ce354e8b9c162f7ff', 8, 1, '', 'Connections', 'connections', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (27, 'c6af570bb2ed9c84abf32033702e2860', 9, 1, '', '(Optional) Log Path', 'log_path', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (28, 'f9389860f5c5340c9b27fca0b4ee5e71', 9, 1, '', 'Web Hits', 'webhits', 'out', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (29, '5fbadb91ad66f203463c1187fe7bd9d5', 10, 1, 'sdfsdfs', 'IP Address', 'ip', 'in', 1, '', 0);
INSERT INTO `data_input_fields` VALUES (30, '6ac4330d123c69067d36a933d105e89a', 10, 0, '', 'Milliseconds', 'out_ms', 'out', 1, '', 0);
INSERT INTO `data_input_fields` VALUES (50, 'b5159c77608386cfa608fc99c2bd0430', 6, 1, '', 'BLAH', 'blah', 'in', 0, '', 0);
INSERT INTO `data_input_fields` VALUES (52, '9d39f6c3a93abf8d9ab9526fb01daa92', 10, 0, '', 'sdf', 'sd', 'out', 1, '', 0);
INSERT INTO `data_input_fields` VALUES (53, '57260bd55ea09df0f4d40cda38c7544f', 11, 0, '', '1 Minute Average', '1min', 'out', 1, '', 0);
INSERT INTO `data_input_fields` VALUES (54, '8efb3511c85e33cbec5b43f4e5ddfb03', 11, 0, '', '5 Minute Average', '5min', 'out', 1, '', 0);
INSERT INTO `data_input_fields` VALUES (55, '24188537860a5bd4b599e5e403006add', 11, 0, '', '15 Minute Average', '15min', 'out', 1, '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `data_query`
-- 

CREATE TABLE `data_query` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `input_type` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `index_order` varchar(255) NOT NULL default '',
  `index_order_type` tinyint(3) unsigned NOT NULL default '0',
  `index_title_format` varchar(100) NOT NULL default '',
  `index_field_id` mediumint(8) unsigned NOT NULL default '0',
  `snmp_oid_num_rows` varchar(100) NOT NULL default '',
  `script_path` varchar(255) NOT NULL default '',
  `script_server_function` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_query`
-- 

INSERT INTO `data_query` VALUES (1, 0, 1, 'SNMP - Interfaces', 'ifDescr:ifName:ifHwAddr:ifIndex', 2, '|chosen_order_field|', 4, '.1.3.6.1.2.1.2.1.0', '', '');
INSERT INTO `data_query` VALUES (2, 0, 3, 'Host-MIB - Disk Partitions', 'hrStorageDescr:hrStorageIndex', 2, '|chosen_order_field|', 25, '', '|path_cacti|/scripts/ss_host_disk.php', 'ss_host_disk');
INSERT INTO `data_query` VALUES (3, 0, 3, 'Host-MIB - Processor Information', 'hrProcessorFrwID', 2, 'CPU#|chosen_order_field|', 31, '', '|path_cacti|/scripts/ss_host_cpu.php', 'ss_host_cpu');
INSERT INTO `data_query` VALUES (4, 0, 1, 'Net-SNMP - Disk Partitions', 'hrStorageDescr:hrStorageIndex', 2, '|chosen_order_field|', 33, '', '', '');
INSERT INTO `data_query` VALUES (5, 0, 2, 'Unix - Disk Partitions', 'dskDevice', 2, '|chosen_order_field|', 40, '', 'perl |path_cacti|/scripts/query_unix_partitions.pl', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `data_query_field`
-- 

CREATE TABLE `data_query_field` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_query_id` mediumint(8) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `name_desc` varchar(100) NOT NULL default '',
  `source` varchar(100) NOT NULL default '',
  `method_type` tinyint(3) unsigned NOT NULL default '0',
  `method_value` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unq_name` (`name`,`data_query_id`),
  KEY `data_query_id` (`data_query_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_query_field`
-- 

INSERT INTO `data_query_field` VALUES (4, 1, 1, 'ifIndex', 'Index', '.1.3.6.1.2.1.2.2.1.1', 1, '');
INSERT INTO `data_query_field` VALUES (5, 1, 1, 'ifOperStatus', 'Status', '.1.3.6.1.2.1.2.2.1.8', 1, '');
INSERT INTO `data_query_field` VALUES (6, 1, 1, 'ifDescr', 'Description', '.1.3.6.1.2.1.2.2.1.2', 1, '');
INSERT INTO `data_query_field` VALUES (7, 1, 1, 'ifName', 'Name (IF-MIB)', '.1.3.6.1.2.1.31.1.1.1.1', 1, '');
INSERT INTO `data_query_field` VALUES (8, 1, 1, 'ifAlias', 'Alias (IF-MIB)', '.1.3.6.1.2.1.31.1.1.1.18', 1, '');
INSERT INTO `data_query_field` VALUES (9, 1, 1, 'ifType', 'Type', '.1.3.6.1.2.1.2.2.1.3', 1, '');
INSERT INTO `data_query_field` VALUES (10, 1, 1, 'ifSpeed', 'Speed', '.1.3.6.1.2.1.2.2.1.5', 1, '');
INSERT INTO `data_query_field` VALUES (11, 1, 1, 'ifHwAddr', 'Hardware Address', '.1.3.6.1.2.1.2.2.1.6', 1, '');
INSERT INTO `data_query_field` VALUES (12, 1, 2, 'ifInOctets', 'Bytes In', '.1.3.6.1.2.1.2.2.1.10', 1, '');
INSERT INTO `data_query_field` VALUES (13, 1, 2, 'ifOutOctets', 'Bytes Out', '.1.3.6.1.2.1.2.2.1.16', 1, '');
INSERT INTO `data_query_field` VALUES (14, 1, 2, 'ifHCInOctets', 'Bytes In (64-bit Counters)', '.1.3.6.1.2.1.31.1.1.1.6', 1, '');
INSERT INTO `data_query_field` VALUES (15, 1, 2, 'ifHCOutOctets', 'Bytes Out (64-bit Counters)', '.1.3.6.1.2.1.31.1.1.1.10', 1, '');
INSERT INTO `data_query_field` VALUES (16, 1, 2, 'ifInDiscards', 'Discarded Packets In', '.1.3.6.1.2.1.2.2.1.13', 1, '');
INSERT INTO `data_query_field` VALUES (17, 1, 2, 'ifOutDiscards', 'Discarded Packets Out', '.1.3.6.1.2.1.2.2.1.19', 1, '');
INSERT INTO `data_query_field` VALUES (18, 1, 2, 'ifInNUcastPkts', 'Non-Unicast Packets In', '.1.3.6.1.2.1.2.2.1.12', 1, '');
INSERT INTO `data_query_field` VALUES (19, 1, 2, 'ifOutNUcastPkts', 'Non-Unicast Packets Out', '.1.3.6.1.2.1.2.2.1.18', 1, '');
INSERT INTO `data_query_field` VALUES (20, 1, 2, 'ifInUcastPkts', 'Unicast Packets In', '.1.3.6.1.2.1.2.2.1.11', 1, '');
INSERT INTO `data_query_field` VALUES (21, 1, 2, 'ifOutUcastPkts', 'Unicast Packets Out', '.1.3.6.1.2.1.2.2.1.17', 1, '');
INSERT INTO `data_query_field` VALUES (22, 1, 2, 'ifInErrors', 'Errors In', '.1.3.6.1.2.1.2.2.1.14', 1, '');
INSERT INTO `data_query_field` VALUES (23, 1, 2, 'ifOutErrors', 'Errors Out', '.1.3.6.1.2.1.2.2.1.20', 1, '');
INSERT INTO `data_query_field` VALUES (24, 1, 1, 'ifIP', 'IP Address', '.1.3.6.1.2.1.4.20.1.2', 1, '4');
INSERT INTO `data_query_field` VALUES (25, 2, 1, 'hrStorageIndex', 'Index', 'index', 0, '');
INSERT INTO `data_query_field` VALUES (26, 2, 1, 'hrStorageDescr', 'Description', 'description', 0, '');
INSERT INTO `data_query_field` VALUES (27, 2, 1, 'hrStorageAllocationUnits', 'Storage Allocation Units', 'sau', 0, '');
INSERT INTO `data_query_field` VALUES (28, 2, 2, 'hrStorageSize', 'Total Size', 'total', 0, '');
INSERT INTO `data_query_field` VALUES (29, 2, 2, 'hrStorageUsed', 'Total Used', 'used', 0, '');
INSERT INTO `data_query_field` VALUES (30, 2, 2, 'hrStorageAllocationFailures', 'Allocation Failures', 'failures', 0, '');
INSERT INTO `data_query_field` VALUES (31, 3, 1, 'hrProcessorFrwID', 'Processor Index Number', 'index', 0, '');
INSERT INTO `data_query_field` VALUES (32, 3, 2, 'hrProcessorLoad', 'Processor Usage', 'usage', 0, '');
INSERT INTO `data_query_field` VALUES (33, 4, 1, 'dskIndex', 'Index', '.1.3.6.1.4.1.2021.9.1.1', 1, '');
INSERT INTO `data_query_field` VALUES (34, 4, 1, 'dskPath', 'Mount Point', '.1.3.6.1.4.1.2021.9.1.2', 1, '');
INSERT INTO `data_query_field` VALUES (35, 4, 1, 'dskDevice', 'Device Name', '.1.3.6.1.4.1.2021.9.1.3', 1, '');
INSERT INTO `data_query_field` VALUES (36, 4, 2, 'dskTotal', 'Total Space', '.1.3.6.1.4.1.2021.9.1.6', 1, '');
INSERT INTO `data_query_field` VALUES (37, 4, 2, 'dskAvail', 'Available Space', '.1.3.6.1.4.1.2021.9.1.7', 1, '');
INSERT INTO `data_query_field` VALUES (38, 4, 2, 'dskUsed', 'Used Space', '.1.3.6.1.4.1.2021.9.1.8', 1, '');
INSERT INTO `data_query_field` VALUES (39, 4, 2, 'dskPercent', 'Percent Available', '.1.3.6.1.4.1.2021.9.1.9', 1, '');
INSERT INTO `data_query_field` VALUES (40, 5, 1, 'dskDevice', 'Device Name', 'device', 0, '');
INSERT INTO `data_query_field` VALUES (41, 5, 1, 'dskMount', 'Mount Point', 'mount', 0, '');
INSERT INTO `data_query_field` VALUES (42, 5, 2, 'dskTotal', 'Total Blocks', 'total', 0, '');
INSERT INTO `data_query_field` VALUES (43, 5, 2, 'dskUsed', 'Used Blocks', 'used', 0, '');
INSERT INTO `data_query_field` VALUES (44, 5, 2, 'dskAvailable', 'Available Blocks', 'available', 0, '');
INSERT INTO `data_query_field` VALUES (45, 5, 2, 'dskPercentUsed', 'Percent Used', 'percent', 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `data_source`
-- 

CREATE TABLE `data_source` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `data_template_id` mediumint(8) unsigned NOT NULL default '0',
  `data_input_type` tinyint(1) unsigned NOT NULL default '3',
  `name` varchar(255) NOT NULL default '',
  `name_cache` varchar(255) NOT NULL default '',
  `active` tinyint(1) unsigned NOT NULL default '1',
  `rrd_path` varchar(255) NOT NULL default '',
  `polling_interval` mediumint(8) unsigned NOT NULL default '300',
  PRIMARY KEY  (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_template_id` (`data_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_source`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_source_field`
-- 

CREATE TABLE `data_source_field` (
  `data_source_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`data_source_id`,`name`),
  KEY `data_source_id` (`data_source_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_source_field`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_source_item`
-- 

CREATE TABLE `data_source_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_source_id` mediumint(8) unsigned NOT NULL default '0',
  `data_template_item_id` mediumint(8) unsigned NOT NULL default '0',
  `rrd_maximum` varchar(20) NOT NULL default '0',
  `rrd_minimum` varchar(20) NOT NULL default '0',
  `rrd_heartbeat` mediumint(5) unsigned NOT NULL default '600',
  `data_source_type` tinyint(1) unsigned NOT NULL default '1',
  `data_source_name` varchar(19) NOT NULL default '',
  `field_input_value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `data_source_name` (`data_source_id`,`data_source_name`),
  KEY `data_source_id` (`data_source_id`),
  KEY `data_input_field_name` (`field_input_value`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_source_item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_source_rra_item`
-- 

CREATE TABLE `data_source_rra_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_source_id` mediumint(8) unsigned NOT NULL default '0',
  `consolidation_function` tinyint(3) unsigned NOT NULL default '0',
  `steps` smallint(5) unsigned NOT NULL default '0',
  `rows` int(11) unsigned NOT NULL default '0',
  `x_files_factor` decimal(5,4) NOT NULL default '0.0000',
  `hw_alpha` decimal(5,4) NOT NULL default '0.0000',
  `hw_beta` decimal(5,4) NOT NULL default '0.0000',
  `hw_gamma` decimal(5,4) NOT NULL default '0.0000',
  `hw_seasonal_period` int(11) unsigned NOT NULL default '0',
  `hw_rra_num` tinyint(3) unsigned NOT NULL default '0',
  `hw_threshold` tinyint(3) unsigned NOT NULL default '0',
  `hw_window_length` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_source_rra_item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_template`
-- 

CREATE TABLE `data_template` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `template_name` varchar(150) NOT NULL default '',
  `data_input_type` tinyint(1) unsigned NOT NULL default '3',
  `t_name` tinyint(1) unsigned NOT NULL default '0',
  `t_active` tinyint(1) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '1',
  `t_polling_interval` tinyint(1) unsigned NOT NULL default '0',
  `polling_interval` mediumint(8) unsigned NOT NULL default '300',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_template`
-- 

INSERT INTO `data_template` VALUES (1, 870, 'Host MIB - CPU Utilization', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (2, 93, 'Host MIB - Disk Space', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (3, 0, 'Host MIB - Logged in Users', 4, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (4, 141, 'Host MIB - Processes', 4, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (5, 0, 'Interface - Errors/Discards', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (6, 0, 'Interface - Traffic (32-bit)', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (7, 0, 'Interface - Traffic (64-bit)', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (8, 0, 'Interface - Unicast Packets', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (9, 0, 'Local Linux - Memory', 3, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (10, 0, 'Net-SNMP - CPU Usage', 4, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (11, 1, 'Net-SNMP - Disk Space', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (12, 0, 'Net-SNMP - Load Average', 4, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (13, 59, 'Net-SNMP - Memory', 4, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (14, 67, 'Local Unix - Disk Space', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (15, 5, 'Local Unix - Logged In Users', 3, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (16, 9, 'Local Unix - Processes', 3, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (17, 0, 'Local Unix - Ping Host', 3, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (18, 52, 'Interface - Non-Unicast Packets', 2, 0, 0, 1, 0, 300);
INSERT INTO `data_template` VALUES (19, 0, 'Local Unix - Load Average', 3, 0, 0, 1, 0, 300);

-- --------------------------------------------------------

-- 
-- Table structure for table `data_template_field`
-- 

CREATE TABLE `data_template_field` (
  `data_template_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `t_value` tinyint(1) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`data_template_id`,`name`),
  KEY `data_template_id` (`data_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_template_field`
-- 

INSERT INTO `data_template_field` VALUES (1, 'data_query_id', 0, '9');
INSERT INTO `data_template_field` VALUES (2, 'data_query_id', 0, '8');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_auth_protocol', 0, 'MD5');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_auth_password_confirm', 0, '');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_auth_password', 0, '');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_auth_username', 0, '');
INSERT INTO `data_template_field` VALUES (3, 'snmp_community', 0, 'public');
INSERT INTO `data_template_field` VALUES (3, 'snmp_version', 0, '1');
INSERT INTO `data_template_field` VALUES (3, 'snmp_timeout', 0, '500');
INSERT INTO `data_template_field` VALUES (3, 'snmp_port', 0, '161');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_priv_passphrase', 0, '');
INSERT INTO `data_template_field` VALUES (3, 'snmpv3_priv_protocol', 0, 'DES');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_auth_password', 0, '');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_auth_password_confirm', 0, '');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_auth_username', 0, '');
INSERT INTO `data_template_field` VALUES (4, 'snmp_community', 0, 'public');
INSERT INTO `data_template_field` VALUES (4, 'snmp_port', 0, '161');
INSERT INTO `data_template_field` VALUES (4, 'snmp_timeout', 0, '500');
INSERT INTO `data_template_field` VALUES (4, 'snmp_version', 0, '1');
INSERT INTO `data_template_field` VALUES (5, 'data_query_id', 0, '1');
INSERT INTO `data_template_field` VALUES (6, 'data_query_id', 0, '1');
INSERT INTO `data_template_field` VALUES (7, 'data_query_id', 0, '1');
INSERT INTO `data_template_field` VALUES (8, 'data_query_id', 0, '1');
INSERT INTO `data_template_field` VALUES (9, 'script_id', 0, '6');
INSERT INTO `data_template_field` VALUES (9, 'blah', 0, 'X');
INSERT INTO `data_template_field` VALUES (9, 'grepstr', 0, 'X');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_priv_passphrase', 0, '');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_auth_protocol', 0, 'MD5');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_auth_password_confirm', 0, '');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_auth_password', 0, '');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_auth_username', 0, '');
INSERT INTO `data_template_field` VALUES (10, 'snmp_community', 0, 'public');
INSERT INTO `data_template_field` VALUES (10, 'snmp_version', 0, '1');
INSERT INTO `data_template_field` VALUES (10, 'snmp_timeout', 0, '500');
INSERT INTO `data_template_field` VALUES (10, 'snmp_port', 0, '161');
INSERT INTO `data_template_field` VALUES (10, 'snmpv3_priv_protocol', 0, 'DES');
INSERT INTO `data_template_field` VALUES (11, 'data_query_id', 0, '2');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_priv_passphrase', 0, '');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_priv_protocol', 0, 'DES');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_auth_protocol', 0, 'MD5');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_auth_password_confirm', 0, '');
INSERT INTO `data_template_field` VALUES (12, 'snmp_port', 0, '161');
INSERT INTO `data_template_field` VALUES (12, 'snmp_timeout', 0, '500');
INSERT INTO `data_template_field` VALUES (12, 'snmp_version', 0, '1');
INSERT INTO `data_template_field` VALUES (12, 'snmp_community', 0, 'public');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_auth_username', 0, '');
INSERT INTO `data_template_field` VALUES (12, 'snmpv3_auth_password', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_auth_password_confirm', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmp_port', 0, '161');
INSERT INTO `data_template_field` VALUES (13, 'snmp_timeout', 0, '500');
INSERT INTO `data_template_field` VALUES (13, 'snmp_version', 0, '1');
INSERT INTO `data_template_field` VALUES (13, 'snmp_community', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_auth_username', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_auth_password', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_auth_protocol', 0, 'MD5');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_priv_passphrase', 0, '');
INSERT INTO `data_template_field` VALUES (13, 'snmpv3_priv_protocol', 0, 'DES');
INSERT INTO `data_template_field` VALUES (14, 'data_query_id', 0, '6');
INSERT INTO `data_template_field` VALUES (15, 'username', 0, 'X');
INSERT INTO `data_template_field` VALUES (15, 'script_id', 0, '5');
INSERT INTO `data_template_field` VALUES (16, 'script_id', 0, '7');
INSERT INTO `data_template_field` VALUES (17, 'script_id', 0, '10');
INSERT INTO `data_template_field` VALUES (18, 'data_query_id', 0, '1');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_auth_protocol', 0, 'MD5');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_priv_passphrase', 0, '');
INSERT INTO `data_template_field` VALUES (4, 'snmpv3_priv_protocol', 0, 'DES');
INSERT INTO `data_template_field` VALUES (19, 'script_id', 0, '11');

-- --------------------------------------------------------

-- 
-- Table structure for table `data_template_item`
-- 

CREATE TABLE `data_template_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_template_id` mediumint(8) unsigned NOT NULL default '0',
  `t_rrd_maximum` tinyint(1) unsigned NOT NULL default '0',
  `rrd_maximum` varchar(20) NOT NULL default '0',
  `t_rrd_minimum` tinyint(1) unsigned NOT NULL default '0',
  `rrd_minimum` varchar(20) NOT NULL default '0',
  `t_rrd_heartbeat` tinyint(1) unsigned NOT NULL default '0',
  `rrd_heartbeat` mediumint(5) unsigned NOT NULL default '600',
  `t_data_source_type` tinyint(1) unsigned NOT NULL default '0',
  `data_source_type` tinyint(1) unsigned NOT NULL default '1',
  `t_data_source_name` tinyint(1) unsigned NOT NULL default '0',
  `data_source_name` varchar(19) NOT NULL default '',
  `field_input_value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `data_source_name` (`data_template_id`,`data_source_name`),
  KEY `data_template_id` (`data_template_id`),
  KEY `data_input_field_name` (`field_input_value`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_template_item`
-- 

INSERT INTO `data_template_item` VALUES (1, 1, 0, '100', 0, '0', 0, 600, 0, 1, 0, 'cpu', 'hrProcessorLoad');
INSERT INTO `data_template_item` VALUES (2, 2, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_total', 'hrStorageSize');
INSERT INTO `data_template_item` VALUES (3, 2, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_used', 'hrStorageUsed');
INSERT INTO `data_template_item` VALUES (4, 3, 0, '10000', 0, '0', 0, 600, 0, 1, 0, 'users', '.1.3.6.1.2.1.25.1.5.0');
INSERT INTO `data_template_item` VALUES (5, 4, 0, '10000', 0, '0', 0, 600, 0, 1, 0, 'proc', '.1.3.6.1.2.1.25.1.6');
INSERT INTO `data_template_item` VALUES (6, 5, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'discards_in', 'ifInDiscards');
INSERT INTO `data_template_item` VALUES (7, 5, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'discards_out', 'ifOutDiscards');
INSERT INTO `data_template_item` VALUES (8, 5, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'errors_in', 'ifInErrors');
INSERT INTO `data_template_item` VALUES (9, 5, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'errors_out', 'ifOutErrors');
INSERT INTO `data_template_item` VALUES (10, 6, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'traffic_in', 'ifInOctets');
INSERT INTO `data_template_item` VALUES (11, 6, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'traffic_out', 'ifOutOctets');
INSERT INTO `data_template_item` VALUES (12, 7, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'traffic_in', 'ifHCInOctets');
INSERT INTO `data_template_item` VALUES (13, 7, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'traffic_out', 'ifHCOutOctets');
INSERT INTO `data_template_item` VALUES (14, 8, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'unicast_in', 'ifInUcastPkts');
INSERT INTO `data_template_item` VALUES (15, 8, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'unicast_out', 'ifOutUcastPkts');
INSERT INTO `data_template_item` VALUES (16, 9, 0, '10000000', 0, '0', 0, 600, 0, 1, 0, 'mem_buffers', 'kilobytes');
INSERT INTO `data_template_item` VALUES (17, 10, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'cpu_nice', '.1.3.6.1.4.1.2021.11.51.0');
INSERT INTO `data_template_item` VALUES (18, 10, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'cpu_system', '.1.3.6.1.4.1.2021.11.52.0');
INSERT INTO `data_template_item` VALUES (19, 10, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'cpu_user', '.1.3.6.1.4.1.2021.11.50.0');
INSERT INTO `data_template_item` VALUES (20, 11, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_free', 'dskAvail');
INSERT INTO `data_template_item` VALUES (21, 11, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_used', 'dskUsed');
INSERT INTO `data_template_item` VALUES (22, 12, 0, '2000', 0, '0', 0, 600, 0, 1, 0, 'load_1min', '.1.3.6.1.4.1.2021.10.1.3.1');
INSERT INTO `data_template_item` VALUES (23, 12, 0, '2000', 0, '0', 0, 600, 0, 1, 0, 'load_5min', '.1.3.6.1.4.1.2021.10.1.3.2');
INSERT INTO `data_template_item` VALUES (24, 12, 0, '2000', 0, '0', 0, 600, 0, 1, 0, 'load_15min', '.1.3.6.1.4.1.2021.10.1.3.3');
INSERT INTO `data_template_item` VALUES (25, 13, 0, 'U', 0, '0', 0, 600, 1, 1, 0, 'mem_buffers', '.1.3.6.1.4.1.2021.4.14.0');
INSERT INTO `data_template_item` VALUES (26, 13, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'mem_free', '.1.3.6.1.4.1.2021.4.6.0');
INSERT INTO `data_template_item` VALUES (27, 14, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_free', 'dskAvailable');
INSERT INTO `data_template_item` VALUES (28, 14, 0, 'U', 0, '0', 0, 600, 0, 1, 0, 'hdd_used', 'dskUsed');
INSERT INTO `data_template_item` VALUES (29, 15, 0, '1000', 0, '0', 0, 600, 0, 1, 0, 'users', 'users');
INSERT INTO `data_template_item` VALUES (30, 16, 0, '2000', 0, '0', 0, 600, 0, 1, 0, 'proc', 'proc');
INSERT INTO `data_template_item` VALUES (31, 17, 0, '5000', 0, '0', 0, 600, 0, 1, 0, 'ping', 'out_ms');
INSERT INTO `data_template_item` VALUES (32, 18, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'nonunicast_in', 'ifInNUcastPkts');
INSERT INTO `data_template_item` VALUES (33, 18, 0, 'U', 0, '0', 0, 600, 0, 2, 0, 'nonunicast_out', 'ifOutNUcastPkts');
INSERT INTO `data_template_item` VALUES (34, 19, 0, '2000', 0, '0', 0, 600, 0, 1, 0, '1min', '1min');
INSERT INTO `data_template_item` VALUES (35, 19, 0, '2000', 0, '0', 0, 600, 0, 1, 0, '5min', '5min');
INSERT INTO `data_template_item` VALUES (36, 19, 0, '2000', 0, '0', 0, 600, 0, 1, 0, '15min', '15min');

-- --------------------------------------------------------

-- 
-- Table structure for table `data_template_rra_item`
-- 

CREATE TABLE `data_template_rra_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_template_id` mediumint(8) unsigned NOT NULL default '0',
  `consolidation_function` tinyint(3) unsigned NOT NULL default '0',
  `steps` smallint(5) unsigned NOT NULL default '0',
  `rows` int(11) unsigned NOT NULL default '0',
  `x_files_factor` decimal(5,4) NOT NULL default '0.0000',
  `hw_alpha` decimal(5,4) NOT NULL default '0.0000',
  `hw_beta` decimal(5,4) NOT NULL default '0.0000',
  `hw_gamma` decimal(5,4) NOT NULL default '0.0000',
  `hw_seasonal_period` int(11) unsigned NOT NULL default '0',
  `hw_rra_num` tinyint(3) unsigned NOT NULL default '0',
  `hw_threshold` tinyint(3) unsigned NOT NULL default '0',
  `hw_window_length` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_template_rra_item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `data_template_suggested_value`
-- 

CREATE TABLE `data_template_suggested_value` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `data_template_id` mediumint(8) unsigned NOT NULL default '0',
  `field_name` varchar(30) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `sequence` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `data_template_id` (`data_template_id`,`field_name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `data_template_suggested_value`
-- 

INSERT INTO `data_template_suggested_value` VALUES (1, 1, 'name', '|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|', 1);
INSERT INTO `data_template_suggested_value` VALUES (2, 2, 'name', '|host_description| - Disk Space - |query_hrStorageDescr|', 1);
INSERT INTO `data_template_suggested_value` VALUES (3, 3, 'name', '|host_description| - Logged in Users', 1);
INSERT INTO `data_template_suggested_value` VALUES (4, 4, 'name', '|host_description| - Processes', 1);
INSERT INTO `data_template_suggested_value` VALUES (5, 5, 'name', '|host_description| - Errors - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `data_template_suggested_value` VALUES (6, 6, 'name', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `data_template_suggested_value` VALUES (7, 7, 'name', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `data_template_suggested_value` VALUES (8, 8, 'name', '|host_description| - Unicast Packets - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `data_template_suggested_value` VALUES (9, 9, 'name', '|host_description| - Memory', 1);
INSERT INTO `data_template_suggested_value` VALUES (10, 10, 'name', '|host_description| - CPU Usage', 1);
INSERT INTO `data_template_suggested_value` VALUES (11, 11, 'name', '|host_description| - Disk Space - |query_dskPath|', 1);
INSERT INTO `data_template_suggested_value` VALUES (12, 12, 'name', '|host_description| - Load Average', 1);
INSERT INTO `data_template_suggested_value` VALUES (13, 13, 'name', '|host_description| - Memory', 1);
INSERT INTO `data_template_suggested_value` VALUES (14, 14, 'name', '|host_description| - Disk Space - |query_dskMount|', 1);
INSERT INTO `data_template_suggested_value` VALUES (15, 15, 'name', '|host_description| - Logged in Users', 1);
INSERT INTO `data_template_suggested_value` VALUES (16, 16, 'name', '|host_description| - Processes', 1);
INSERT INTO `data_template_suggested_value` VALUES (17, 17, 'name', '|host_description| - Ping Host', 1);
INSERT INTO `data_template_suggested_value` VALUES (18, 18, 'name', '|host_description| - Non-Unicast Packets - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `data_template_suggested_value` VALUES (19, 6, 'name', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `data_template_suggested_value` VALUES (20, 6, 'name', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `data_template_suggested_value` VALUES (21, 6, 'name', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `data_template_suggested_value` VALUES (22, 7, 'name', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `data_template_suggested_value` VALUES (23, 7, 'name', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `data_template_suggested_value` VALUES (24, 7, 'name', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `data_template_suggested_value` VALUES (25, 5, 'name', '|host_description| - Errors - |query_ifName|', 2);
INSERT INTO `data_template_suggested_value` VALUES (26, 5, 'name', '|host_description| - Errors - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `data_template_suggested_value` VALUES (27, 5, 'name', '|host_description| - Errors - |query_ifDescr|', 4);
INSERT INTO `data_template_suggested_value` VALUES (28, 18, 'name', '|host_description| - Non-Unicast Packets - |query_ifName|', 2);
INSERT INTO `data_template_suggested_value` VALUES (29, 18, 'name', '|host_description| - Non-Unicast Packets - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `data_template_suggested_value` VALUES (30, 18, 'name', '|host_description| - Non-Unicast Packets - |query_ifDescr|', 4);
INSERT INTO `data_template_suggested_value` VALUES (31, 8, 'name', '|host_description| - Unicast Packets - |query_ifName|', 2);
INSERT INTO `data_template_suggested_value` VALUES (32, 8, 'name', '|host_description| - Unicast Packets - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `data_template_suggested_value` VALUES (33, 8, 'name', '|host_description| - Unicast Packets - |query_ifDescr|', 4);
INSERT INTO `data_template_suggested_value` VALUES (34, 19, 'name', '|host_description| - Load Average', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `graph`
-- 

CREATE TABLE `graph` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  `image_format` tinyint(1) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `title_cache` varchar(255) NOT NULL default '',
  `height` smallint(5) unsigned NOT NULL default '120',
  `width` smallint(5) unsigned NOT NULL default '500',
  `x_grid` varchar(50) NOT NULL default '',
  `y_grid` varchar(50) NOT NULL default '',
  `y_grid_alt` tinyint(1) unsigned NOT NULL default '0',
  `no_minor` tinyint(1) unsigned NOT NULL default '0',
  `upper_limit` bigint(20) NOT NULL default '0',
  `lower_limit` bigint(20) NOT NULL default '0',
  `vertical_label` varchar(200) NOT NULL default '',
  `auto_scale` tinyint(1) unsigned NOT NULL default '1',
  `auto_scale_opts` tinyint(1) unsigned NOT NULL default '2',
  `auto_scale_log` tinyint(1) unsigned NOT NULL default '0',
  `auto_scale_rigid` tinyint(1) unsigned NOT NULL default '0',
  `auto_padding` tinyint(1) unsigned NOT NULL default '1',
  `base_value` smallint(4) unsigned NOT NULL default '1000',
  `export` tinyint(1) unsigned NOT NULL default '1',
  `unit_value` varchar(20) NOT NULL default '',
  `unit_length` tinyint(3) unsigned NOT NULL default '9',
  `unit_exponent_value` tinyint(4) NOT NULL default '0',
  `force_rules_legend` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `graph_template_id` (`graph_template_id`),
  KEY `host_id` (`host_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `graph_item`
-- 

CREATE TABLE `graph_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `graph_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_template_item_id` mediumint(8) unsigned NOT NULL default '0',
  `sequence` smallint(5) unsigned NOT NULL default '0',
  `data_source_item_id` mediumint(8) unsigned NOT NULL default '0',
  `color` varchar(6) NOT NULL default '000000',
  `graph_item_type` tinyint(2) unsigned NOT NULL default '1',
  `cdef` varchar(255) NOT NULL default '',
  `consolidation_function` tinyint(1) unsigned NOT NULL default '0',
  `gprint_format` varchar(30) NOT NULL default '%8.2lf %s',
  `legend_format` varchar(255) NOT NULL default '',
  `legend_value` varchar(255) NOT NULL default '',
  `hard_return` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `graph_id` (`graph_id`),
  KEY `graph_template_item_id` (`graph_template_item_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `graph_template`
-- 

CREATE TABLE `graph_template` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `template_name` varchar(150) NOT NULL default '',
  `t_image_format` tinyint(1) unsigned NOT NULL default '0',
  `image_format` tinyint(1) unsigned NOT NULL default '1',
  `t_title` tinyint(1) unsigned NOT NULL default '0',
  `t_height` tinyint(1) unsigned NOT NULL default '0',
  `height` smallint(5) unsigned NOT NULL default '120',
  `t_width` tinyint(1) unsigned NOT NULL default '0',
  `width` smallint(5) unsigned NOT NULL default '500',
  `t_x_grid` tinyint(1) unsigned NOT NULL default '0',
  `x_grid` varchar(50) NOT NULL default '',
  `t_y_grid` tinyint(1) unsigned NOT NULL default '0',
  `y_grid` varchar(50) NOT NULL default '',
  `t_y_grid_alt` tinyint(1) unsigned NOT NULL default '0',
  `y_grid_alt` tinyint(1) unsigned NOT NULL default '0',
  `t_no_minor` tinyint(1) unsigned NOT NULL default '0',
  `no_minor` tinyint(1) unsigned NOT NULL default '0',
  `t_upper_limit` tinyint(1) unsigned NOT NULL default '0',
  `upper_limit` bigint(20) NOT NULL default '0',
  `t_lower_limit` tinyint(1) unsigned NOT NULL default '0',
  `lower_limit` bigint(20) NOT NULL default '0',
  `t_vertical_label` tinyint(1) unsigned NOT NULL default '0',
  `vertical_label` varchar(200) NOT NULL default '',
  `t_auto_scale` tinyint(1) unsigned NOT NULL default '0',
  `auto_scale` tinyint(1) unsigned NOT NULL default '1',
  `t_auto_scale_opts` tinyint(1) unsigned NOT NULL default '0',
  `auto_scale_opts` tinyint(1) unsigned NOT NULL default '2',
  `t_auto_scale_log` tinyint(1) unsigned NOT NULL default '0',
  `auto_scale_log` tinyint(1) unsigned NOT NULL default '0',
  `t_auto_scale_rigid` tinyint(1) unsigned NOT NULL default '0',
  `auto_scale_rigid` tinyint(1) unsigned NOT NULL default '0',
  `t_auto_padding` tinyint(1) unsigned NOT NULL default '0',
  `auto_padding` tinyint(1) unsigned NOT NULL default '1',
  `t_base_value` tinyint(1) unsigned NOT NULL default '0',
  `base_value` smallint(4) unsigned NOT NULL default '1000',
  `t_export` tinyint(1) unsigned NOT NULL default '0',
  `export` tinyint(1) unsigned NOT NULL default '1',
  `t_unit_value` tinyint(1) unsigned NOT NULL default '0',
  `unit_value` varchar(20) NOT NULL default '',
  `t_unit_length` tinyint(1) unsigned NOT NULL default '0',
  `unit_length` tinyint(3) unsigned NOT NULL default '9',
  `t_unit_exponent_value` tinyint(1) unsigned NOT NULL default '0',
  `unit_exponent_value` tinyint(4) NOT NULL default '0',
  `t_force_rules_legend` tinyint(1) unsigned NOT NULL default '0',
  `force_rules_legend` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_template`
-- 

INSERT INTO `graph_template` VALUES (1, 98, 'Host MIB - Disk Space', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 1000, 0, 0, 0, 'bytes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1024, 0, 1, 0, '', 0, 9, 0, 6, 0, 0);
INSERT INTO `graph_template` VALUES (2, 0, 'Host MIB - CPU Utilization', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'percent', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (3, 0, 'Host MIB - Logged in Users', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'users', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (4, 879, 'Interface - Errors/Discards', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'errors/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (5, 5, 'Interface - Non-Unicast Packets', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'packets/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (6, 66, 'Interface - Traffic (bits/sec)', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bits/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (7, 567, 'Interface - Traffic (bits/sec, 95th Percentile)', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bits/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (8, 7682, 'Interface - Traffic (bytes/sec)', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bytes/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (9, 0, 'Interface - Traffic (bytes/sec, Total Bandwidth)', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bytes/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (10, 3, 'Interface - Traffic (bits/sec, 64-bit counters)', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bits/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (11, 47, 'Interface - Unicast Packets', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'packets/sec', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (12, 6779, 'Net-SNMP - Disk Space', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bytes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1024, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (13, 324, 'Net-SNMP - CPU Usage', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'percent', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (14, 2, 'Net-SNMP - Load Average', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'procs in the run queue', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (15, 8, 'Net-SNMP - Memory Usage', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bytes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1024, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (16, 0, 'Local Unix - Disk Space', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'bytes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1024, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (17, 75, 'Local Unix - Load Average', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'procs in the run queue', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (18, 16777215, 'Local Unix - Logged in Users', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'users', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (19, 7, 'Local Unix - Processes', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'processes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (20, 7, 'Local Unix - Ping Latency', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'milliseconds', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);
INSERT INTO `graph_template` VALUES (21, 9, 'Host MIB - Processes', 0, 1, 0, 0, 120, 0, 500, 0, '', 0, '', 0, 0, 0, 0, 0, 100, 0, 0, 0, 'processes', 0, 1, 0, 2, 0, 0, 0, 0, 0, 1, 0, 1000, 0, 1, 0, '', 0, 9, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_template_item`
-- 

CREATE TABLE `graph_template_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  `sequence` smallint(5) unsigned NOT NULL default '0',
  `data_template_item_id` mediumint(8) unsigned NOT NULL default '0',
  `color` varchar(6) NOT NULL default '000000',
  `graph_item_type` tinyint(2) unsigned NOT NULL default '1',
  `cdef` varchar(255) NOT NULL default '',
  `consolidation_function` tinyint(1) unsigned NOT NULL default '1',
  `gprint_format` varchar(30) NOT NULL default '%8.2lf %s',
  `legend_format` varchar(255) NOT NULL default '',
  `legend_value` varchar(255) NOT NULL default '',
  `hard_return` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `graph_template_id` (`graph_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_template_item`
-- 

INSERT INTO `graph_template_item` VALUES (27, 2, 3, 1, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (26, 2, 2, 1, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (25, 2, 1, 1, 'FF0000', 7, '', 1, '%8.2lf %s', 'CPU Utilization', '', 0);
INSERT INTO `graph_template_item` VALUES (24, 1, 8, 3, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (23, 1, 7, 3, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (22, 1, 6, 3, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (21, 1, 5, 3, 'F51D30', 7, '', 1, '%8.2lf %s', 'Used', '', 0);
INSERT INTO `graph_template_item` VALUES (20, 1, 4, 2, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (19, 1, 3, 2, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (18, 1, 2, 2, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (17, 1, 1, 2, '002A97', 7, '', 1, '%8.2lf %s', 'Total', '', 0);
INSERT INTO `graph_template_item` VALUES (28, 2, 4, 1, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (29, 3, 1, 4, '4668E4', 7, '', 1, '%8.2lf %s', 'Users', '', 0);
INSERT INTO `graph_template_item` VALUES (30, 3, 2, 4, '', 9, '', 4, '%8.0lf', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (31, 3, 3, 4, '', 9, '', 1, '%8.0lf', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (32, 3, 4, 4, '', 9, '', 3, '%8.0lf', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (33, 4, 1, 6, 'FFAB00', 4, '', 1, '%8.2lf %s', 'Discards In', '', 0);
INSERT INTO `graph_template_item` VALUES (34, 4, 2, 6, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (35, 4, 3, 6, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (36, 4, 4, 6, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (37, 4, 5, 8, 'F51D30', 4, '', 1, '%8.2lf %s', 'Errors In', '', 0);
INSERT INTO `graph_template_item` VALUES (38, 4, 6, 8, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (39, 4, 7, 8, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (40, 4, 8, 8, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (41, 4, 9, 7, 'C4FD3D', 4, '', 1, '%8.2lf %s', 'Discards Out', '', 0);
INSERT INTO `graph_template_item` VALUES (42, 4, 10, 7, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (43, 4, 11, 7, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (44, 4, 12, 7, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (45, 4, 13, 9, '00694A', 4, '', 1, '%8.2lf %s', 'Errors Out', '', 0);
INSERT INTO `graph_template_item` VALUES (46, 4, 14, 9, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (47, 4, 15, 9, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (48, 4, 16, 9, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (49, 5, 1, 32, 'FFF200', 7, '', 1, '%8.2lf %s', 'Non-Unicast Packets In', '', 0);
INSERT INTO `graph_template_item` VALUES (50, 5, 2, 32, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (51, 5, 3, 32, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (52, 5, 4, 32, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (53, 5, 5, 33, '00234B', 7, '', 1, '%8.2lf %s', 'Non-Unicast Packets Out', '', 0);
INSERT INTO `graph_template_item` VALUES (54, 5, 6, 33, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (55, 5, 7, 33, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (56, 5, 8, 33, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (57, 6, 1, 10, '00CF00', 7, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Inbound', '', 0);
INSERT INTO `graph_template_item` VALUES (58, 6, 2, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (59, 6, 3, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (60, 6, 4, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (61, 6, 5, 11, '002A97', 4, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Outbound', '', 0);
INSERT INTO `graph_template_item` VALUES (62, 6, 6, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (63, 6, 7, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (64, 6, 8, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (65, 7, 1, 10, '00CF00', 7, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Inbound', '', 0);
INSERT INTO `graph_template_item` VALUES (66, 7, 2, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (67, 7, 3, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (68, 7, 4, 10, '', 9, 'CURRENT_DATA_SOURCE,8,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (69, 7, 5, 11, '002A97', 4, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Outbound', '', 0);
INSERT INTO `graph_template_item` VALUES (70, 7, 6, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (71, 7, 7, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (72, 7, 8, 11, '', 9, 'CURRENT_DATA_SOURCE,8,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (73, 7, 9, 0, '', 1, '', 1, '%8.2lf %s', '', '', 1);
INSERT INTO `graph_template_item` VALUES (74, 7, 10, 0, 'FF0000', 2, '', 1, '%8.2lf %s', '95th Percentile', '|95:bits:0:total:2|', 0);
INSERT INTO `graph_template_item` VALUES (75, 7, 11, 0, '', 1, '', 1, '%8.2lf %s', '(|95:bits:6:total:2| mbit in+out)', '', 0);
INSERT INTO `graph_template_item` VALUES (76, 8, 1, 10, '00CF00', 7, '', 1, '%8.2lf %s', 'Inbound', '', 0);
INSERT INTO `graph_template_item` VALUES (77, 8, 2, 10, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (78, 8, 3, 10, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (79, 8, 4, 10, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (80, 8, 5, 11, '002A97', 4, '', 1, '%8.2lf %s', 'Outbound', '', 0);
INSERT INTO `graph_template_item` VALUES (81, 8, 6, 11, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (82, 8, 7, 11, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (83, 8, 8, 11, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (84, 9, 1, 10, '00CF00', 7, '', 1, '%8.2lf %s', 'Inbound', '', 0);
INSERT INTO `graph_template_item` VALUES (85, 9, 2, 10, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (86, 9, 3, 10, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (87, 9, 4, 10, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 0);
INSERT INTO `graph_template_item` VALUES (88, 9, 6, 11, '002A97', 4, '', 1, '%8.2lf %s', 'Outbound', '', 0);
INSERT INTO `graph_template_item` VALUES (89, 9, 7, 11, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (90, 9, 8, 11, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (91, 9, 9, 11, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 0);
INSERT INTO `graph_template_item` VALUES (92, 9, 5, 10, '', 1, '', 1, '%8.2lf %s', 'Total In:  |sum:auto:current:2:auto|', '', 1);
INSERT INTO `graph_template_item` VALUES (93, 9, 10, 11, '', 1, '', 1, '%8.2lf %s', 'Total Out: |sum:auto:current:2:auto|', '', 1);
INSERT INTO `graph_template_item` VALUES (94, 10, 1, 12, '00CF00', 7, '', 1, '%8.2lf %s', 'Inbound', '', 0);
INSERT INTO `graph_template_item` VALUES (95, 10, 2, 12, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (96, 10, 3, 12, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (97, 10, 4, 12, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (98, 10, 5, 13, '002A97', 4, '', 1, '%8.2lf %s', 'Outbound', '', 0);
INSERT INTO `graph_template_item` VALUES (99, 10, 6, 13, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (100, 10, 7, 13, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (101, 10, 8, 13, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (102, 11, 1, 14, 'FFF200', 7, '', 1, '%8.2lf %s', 'Unicast Packets In', '', 0);
INSERT INTO `graph_template_item` VALUES (103, 11, 2, 14, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (104, 11, 3, 14, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (105, 11, 4, 14, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (106, 11, 5, 15, '00234B', 4, '', 1, '%8.2lf %s', 'Unicast Packets Out', '', 0);
INSERT INTO `graph_template_item` VALUES (107, 11, 6, 15, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (108, 11, 7, 15, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (109, 11, 8, 15, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (110, 12, 1, 21, 'F51D30', 7, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Used', '', 0);
INSERT INTO `graph_template_item` VALUES (111, 12, 2, 21, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (112, 12, 3, 21, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (113, 12, 4, 21, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (114, 12, 5, 20, '002A97', 8, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Available', '', 0);
INSERT INTO `graph_template_item` VALUES (115, 12, 6, 20, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (116, 12, 7, 20, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (117, 12, 8, 20, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (118, 12, 9, 0, '000000', 5, 'ALL_DATA_SOURCES_NODUPS,1024,*', 1, '%8.2lf %s', 'Total', '', 0);
INSERT INTO `graph_template_item` VALUES (119, 12, 10, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (120, 12, 11, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (121, 12, 12, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (122, 13, 1, 18, 'FF0000', 7, '', 1, '%8.2lf %s', 'System', '', 0);
INSERT INTO `graph_template_item` VALUES (123, 13, 2, 18, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (124, 13, 3, 18, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (125, 13, 4, 18, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (126, 13, 5, 19, '0000FF', 7, '', 1, '%8.2lf %s', 'User', '', 0);
INSERT INTO `graph_template_item` VALUES (127, 13, 6, 19, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (128, 13, 7, 19, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (129, 13, 8, 19, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (130, 13, 9, 17, '00FF00', 7, '', 1, '%8.2lf %s', 'Nice', '', 0);
INSERT INTO `graph_template_item` VALUES (131, 13, 10, 17, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (132, 13, 11, 17, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (133, 13, 12, 17, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (134, 13, 13, 0, '000000', 7, 'ALL_DATA_SOURCES_NODUPS', 1, '%8.2lf %s', 'Total', '', 0);
INSERT INTO `graph_template_item` VALUES (135, 13, 14, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (136, 13, 15, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (137, 13, 16, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (138, 14, 1, 22, 'EACC00', 5, '', 1, '%8.2lf %s', '1 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (139, 14, 2, 22, '', 9, '', 4, '%8.2lf', 'Current:', '', 1);
INSERT INTO `graph_template_item` VALUES (140, 14, 3, 23, 'EA8F00', 5, '', 1, '%8.2lf %s', '5 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (141, 14, 4, 23, '', 9, '', 4, '%8.2lf', 'Current:', '', 1);
INSERT INTO `graph_template_item` VALUES (142, 14, 5, 24, 'FF0000', 5, '', 1, '%8.2lf %s', '15 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (143, 14, 6, 24, '', 9, '', 4, '%8.2lf', 'Current:', '', 1);
INSERT INTO `graph_template_item` VALUES (145, 15, 1, 26, '8F005C', 7, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Memory Free', '', 0);
INSERT INTO `graph_template_item` VALUES (146, 15, 2, 26, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (147, 15, 3, 26, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (148, 15, 4, 26, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (149, 15, 5, 25, 'FF5700', 8, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Memory Buffers', '', 0);
INSERT INTO `graph_template_item` VALUES (150, 15, 6, 25, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (151, 15, 7, 25, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (152, 15, 8, 25, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (153, 16, 1, 28, 'F51D30', 7, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Used', '', 0);
INSERT INTO `graph_template_item` VALUES (154, 16, 2, 28, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (155, 16, 3, 28, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (156, 16, 4, 28, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (157, 16, 5, 27, '002A97', 8, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Available', '', 0);
INSERT INTO `graph_template_item` VALUES (158, 16, 6, 27, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (159, 16, 7, 27, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (160, 16, 8, 27, '', 9, 'CURRENT_DATA_SOURCE,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (161, 16, 9, 0, '000000', 5, 'ALL_DATA_SOURCES_NODUPS,1024,*', 1, '%8.2lf %s', 'Total', '', 0);
INSERT INTO `graph_template_item` VALUES (162, 16, 10, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (163, 16, 11, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (164, 16, 12, 0, '', 9, 'ALL_DATA_SOURCES_NODUPS,1024,*', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (165, 18, 1, 29, '4668E4', 7, '', 1, '%8.0lf', 'Users', '', 0);
INSERT INTO `graph_template_item` VALUES (166, 18, 2, 29, '', 9, '', 4, '%8.0lf', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (167, 18, 3, 29, '', 9, '', 1, '%8.0lf', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (168, 18, 4, 29, '', 9, '', 3, '%8.0lf', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (169, 19, 1, 30, 'F51D30', 7, '', 1, '%8.2lf %s', 'Processes', '', 0);
INSERT INTO `graph_template_item` VALUES (170, 19, 2, 30, '', 9, '', 4, '%8.0lf', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (171, 19, 3, 30, '', 9, '', 1, '%8.0lf', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (172, 19, 4, 30, '', 9, '', 3, '%8.0lf', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (173, 20, 1, 31, 'FFF200', 7, '', 1, '%8.2lf %s', 'Latency', '', 0);
INSERT INTO `graph_template_item` VALUES (174, 20, 2, 31, '', 9, '', 4, '%8.2lf %s', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (175, 20, 3, 31, '', 9, '', 1, '%8.2lf %s', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (176, 20, 4, 31, '', 9, '', 3, '%8.2lf %s', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (177, 21, 1, 5, 'F51D30', 7, '', 1, '%8.2lf %s', 'Processes', '', 0);
INSERT INTO `graph_template_item` VALUES (178, 21, 2, 5, '', 9, '', 4, '%8.0lf', 'Current:', '', 0);
INSERT INTO `graph_template_item` VALUES (179, 21, 3, 5, '', 9, '', 1, '%8.0lf', 'Average:', '', 0);
INSERT INTO `graph_template_item` VALUES (180, 21, 4, 5, '', 9, '', 3, '%8.0lf', 'Maximum:', '', 1);
INSERT INTO `graph_template_item` VALUES (181, 17, 1, 34, 'EACC00', 5, '', 1, '%8.2lf %s', '1 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (182, 17, 2, 34, '', 9, '', 1, '%8.2lf', 'Current:', '', 1);
INSERT INTO `graph_template_item` VALUES (183, 17, 3, 35, 'EA8F00', 5, '', 1, '%8.2lf %s', '5 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (184, 17, 4, 35, '', 9, '', 1, '%8.2lf', 'Current:', '', 1);
INSERT INTO `graph_template_item` VALUES (185, 17, 5, 36, 'FF0000', 5, '', 1, '%8.2lf %s', '15 Minute Average', '', 0);
INSERT INTO `graph_template_item` VALUES (186, 17, 6, 36, '', 9, '', 1, '%8.2lf', 'Current:', '', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_template_item_input`
-- 

CREATE TABLE `graph_template_item_input` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `field_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `graph_template_id` (`graph_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_template_item_input`
-- 

INSERT INTO `graph_template_item_input` VALUES (1, 20, 'Legend Color', 'color');

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_template_item_input_item`
-- 

CREATE TABLE `graph_template_item_input_item` (
  `graph_template_item_input_id` mediumint(8) NOT NULL default '0',
  `graph_template_item_id` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`graph_template_item_input_id`,`graph_template_item_id`),
  KEY `graph_template_item_input_id` (`graph_template_item_input_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_template_item_input_item`
-- 

INSERT INTO `graph_template_item_input_item` VALUES (1, 173);

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_template_suggested_value`
-- 

CREATE TABLE `graph_template_suggested_value` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  `field_name` varchar(30) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `sequence` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `graph_template_id` (`graph_template_id`,`field_name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_template_suggested_value`
-- 

INSERT INTO `graph_template_suggested_value` VALUES (1, 1, 'title', '|host_description| - Disk Space - |query_hrStorageDescr|', 1);
INSERT INTO `graph_template_suggested_value` VALUES (2, 2, 'title', '|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|', 1);
INSERT INTO `graph_template_suggested_value` VALUES (3, 3, 'title', '|host_description| - Logged in Users', 1);
INSERT INTO `graph_template_suggested_value` VALUES (4, 4, 'title', '|host_description| - Errors/Discards - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (5, 5, 'title', '|host_description| - Non-Unicast Packets - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (6, 6, 'title', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (7, 7, 'title', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (8, 8, 'title', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (9, 9, 'title', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (10, 10, 'title', '|host_description| - Traffic - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (11, 11, 'title', '|host_description| - Unicast Packets - |query_ifName| (|query_ifIP|)', 1);
INSERT INTO `graph_template_suggested_value` VALUES (12, 12, 'title', ' |host_description| - Disk Space - |query_dskPath|', 1);
INSERT INTO `graph_template_suggested_value` VALUES (13, 13, 'title', '|host_description| - CPU Usage', 1);
INSERT INTO `graph_template_suggested_value` VALUES (14, 14, 'title', '|host_description| - Load Average', 1);
INSERT INTO `graph_template_suggested_value` VALUES (15, 15, 'title', '|host_description| - Memory Usage', 1);
INSERT INTO `graph_template_suggested_value` VALUES (17, 17, 'title', '|host_description| - Load Average', 1);
INSERT INTO `graph_template_suggested_value` VALUES (18, 18, 'title', '|host_description| - Logged in Users', 1);
INSERT INTO `graph_template_suggested_value` VALUES (19, 19, 'title', '|host_description| - Processes', 1);
INSERT INTO `graph_template_suggested_value` VALUES (20, 20, 'title', '|host_description| - Ping Latency', 1);
INSERT INTO `graph_template_suggested_value` VALUES (21, 21, 'title', '|host_description| - Processes', 1);
INSERT INTO `graph_template_suggested_value` VALUES (22, 16, 'title', '|host_description| - Disk Space - |query_dskMount|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (23, 4, 'title', '|host_description| - Errors/Discards - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (24, 4, 'title', '|host_description| - Errors/Discards - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (25, 4, 'title', '|host_description| - Errors/Discards - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (26, 5, 'title', '|host_description| - Non-Unicast Packets - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (27, 5, 'title', '|host_description| - Non-Unicast Packets - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (28, 5, 'title', '|host_description| - Non-Unicast Packets - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (29, 6, 'title', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (30, 6, 'title', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (31, 6, 'title', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (32, 10, 'title', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (33, 10, 'title', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (34, 10, 'title', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (35, 7, 'title', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (36, 7, 'title', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (37, 7, 'title', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (38, 8, 'title', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (39, 8, 'title', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (40, 8, 'title', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (41, 9, 'title', '|host_description| - Traffic - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (42, 9, 'title', '|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (43, 9, 'title', '|host_description| - Traffic - |query_ifDescr|', 4);
INSERT INTO `graph_template_suggested_value` VALUES (44, 11, 'title', '|host_description| - Unicast Packets - |query_ifName|', 2);
INSERT INTO `graph_template_suggested_value` VALUES (45, 11, 'title', '|host_description| - Unicast Packets - |query_ifDescr| (|query_ifIP|)', 3);
INSERT INTO `graph_template_suggested_value` VALUES (46, 11, 'title', '|host_description| - Unicast Packets - |query_ifDescr|', 4);

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_tree`
-- 

CREATE TABLE `graph_tree` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `sort_type` tinyint(3) unsigned NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_tree`
-- 

INSERT INTO `graph_tree` VALUES (1, 1, 'Default Tree');

-- --------------------------------------------------------

-- 
-- Table structure for table `graph_tree_items`
-- 

CREATE TABLE `graph_tree_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `graph_tree_id` smallint(5) unsigned NOT NULL default '0',
  `order_key` varchar(100) NOT NULL default '0',
  `device_grouping_type` tinyint(3) unsigned NOT NULL default '1',
  `sort_children_type` tinyint(3) unsigned NOT NULL default '1',
  `item_type` tinyint(3) unsigned NOT NULL default '0',
  `item_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `graph_tree_id` (`graph_tree_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `graph_tree_items`
-- 

INSERT INTO `graph_tree_items` VALUES (1, 1, '001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000', 1, 1, 3, '1');

-- --------------------------------------------------------

-- 
-- Table structure for table `host`
-- 

CREATE TABLE `host` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `poller_id` smallint(5) unsigned NOT NULL default '0',
  `host_template_id` mediumint(8) unsigned NOT NULL default '0',
  `description` varchar(150) NOT NULL default '',
  `hostname` varchar(250) default NULL,
  `snmp_community` varchar(100) default NULL,
  `snmp_version` tinyint(1) unsigned NOT NULL default '1',
  `snmpv3_auth_username` varchar(50) default NULL,
  `snmpv3_auth_password` varchar(50) default NULL,
  `snmpv3_auth_protocol` varchar(5) default NULL,
  `snmpv3_priv_passphrase` varchar(200) default NULL,
  `snmpv3_priv_protocol` varchar(6) default NULL,
  `snmp_port` mediumint(5) unsigned NOT NULL default '161',
  `snmp_timeout` mediumint(8) unsigned NOT NULL default '500',
  `availability_method` smallint(5) unsigned NOT NULL default '1',
  `ping_method` smallint(5) unsigned default '0',
  `disabled` char(2) default NULL,
  `status` tinyint(2) NOT NULL default '0',
  `status_event_count` mediumint(8) unsigned NOT NULL default '0',
  `status_fail_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_rec_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_last_error` varchar(50) default '',
  `min_time` decimal(9,5) default '99999.99000',
  `max_time` decimal(9,5) default '0.00000',
  `cur_time` decimal(9,5) default '0.00000',
  `avg_time` decimal(9,5) default '0.00000',
  `cur_pkt_loss` decimal(9,5) default '0.00000',
  `avg_pkt_loss` decimal(9,5) default '0.00000',
  `total_polls` int(12) unsigned default '0',
  `failed_polls` int(12) unsigned default '0',
  `availability` decimal(8,5) NOT NULL default '100.00000',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host`
-- 

INSERT INTO `host` VALUES (1, 1, 3, 'Localhost', 'localhost', '', 1, '', '', 'MD5', '', 'DES', 161, 500, 2, 2, 'on', 3, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 0.00000, 0.00000, 0.00000, 0.00000, 0.00000, 0.00000, 0, 0, 100.00000);

-- --------------------------------------------------------

-- 
-- Table structure for table `host_data_query`
-- 

CREATE TABLE `host_data_query` (
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `data_query_id` mediumint(8) unsigned NOT NULL default '0',
  `sort_field` varchar(50) NOT NULL default '',
  `title_format` varchar(50) NOT NULL default '',
  `reindex_method` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_id`,`data_query_id`),
  KEY `host_id` (`host_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_data_query`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `host_data_query_cache`
-- 

CREATE TABLE `host_data_query_cache` (
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `data_query_id` mediumint(8) unsigned NOT NULL default '0',
  `field_name` varchar(50) NOT NULL default '',
  `field_value` varchar(255) default NULL,
  `index_value` varchar(60) NOT NULL default '',
  `oid` text NOT NULL default '',
  PRIMARY KEY  (`host_id`,`data_query_id`,`field_name`,`index_value`),
  KEY `host_id` (`host_id`,`field_name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_data_query_cache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `host_graph`
-- 

CREATE TABLE `host_graph` (
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_id`,`graph_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_graph`
-- 

INSERT INTO `host_graph` VALUES (1, 17);
INSERT INTO `host_graph` VALUES (1, 18);
INSERT INTO `host_graph` VALUES (1, 19);

-- --------------------------------------------------------

-- 
-- Table structure for table `host_template`
-- 

CREATE TABLE `host_template` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_template`
-- 

INSERT INTO `host_template` VALUES (1, 'Net-SNMP Enabled Host');
INSERT INTO `host_template` VALUES (2, 'Windows 2000/XP Host');
INSERT INTO `host_template` VALUES (3, 'Local Linux Machine');

-- --------------------------------------------------------

-- 
-- Table structure for table `host_template_data_query`
-- 

CREATE TABLE `host_template_data_query` (
  `host_template_id` mediumint(8) unsigned NOT NULL default '0',
  `data_query_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_template_id`,`data_query_id`),
  KEY `host_template_id` (`host_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_template_data_query`
-- 

INSERT INTO `host_template_data_query` VALUES (1, 1);
INSERT INTO `host_template_data_query` VALUES (1, 2);
INSERT INTO `host_template_data_query` VALUES (2, 1);
INSERT INTO `host_template_data_query` VALUES (2, 8);
INSERT INTO `host_template_data_query` VALUES (2, 9);
INSERT INTO `host_template_data_query` VALUES (3, 6);

-- --------------------------------------------------------

-- 
-- Table structure for table `host_template_graph`
-- 

CREATE TABLE `host_template_graph` (
  `host_template_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_template_id`,`graph_template_id`),
  KEY `host_template_id` (`host_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_template_graph`
-- 

INSERT INTO `host_template_graph` VALUES (1, 13);
INSERT INTO `host_template_graph` VALUES (1, 14);
INSERT INTO `host_template_graph` VALUES (1, 15);
INSERT INTO `host_template_graph` VALUES (2, 3);
INSERT INTO `host_template_graph` VALUES (2, 21);
INSERT INTO `host_template_graph` VALUES (3, 17);
INSERT INTO `host_template_graph` VALUES (3, 18);
INSERT INTO `host_template_graph` VALUES (3, 19);

-- --------------------------------------------------------

-- 
-- Table structure for table `host_template_sysdesc`
-- 

CREATE TABLE `host_template_sysdesc` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `host_template_id` mediumint(11) unsigned NOT NULL default '0',
  `snmp_sysdesc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `host_template_id` (`host_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `host_template_sysdesc`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

CREATE TABLE `log` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `logdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `facility` tinyint(1) unsigned NOT NULL default '0',
  `severity` int(1) NOT NULL default '0',
  `poller_id` smallint(5) unsigned NOT NULL default '0',
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(50) NOT NULL default 'system',
  `source` varchar(50) NOT NULL default 'localhost',
  `plugin` varchar(30) NOT NULL default 'N/A',
  `message` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `facility` (`facility`),
  KEY `severity` (`severity`),
  KEY `host_id` (`host_id`),
  KEY `poller_id` (`poller_id`),
  KEY `username` (`username`),
  KEY `logdate` (`logdate`),
  KEY `plugin` (`plugin`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `package`
-- 

CREATE TABLE `package` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `hash_package` varchar(32) NOT NULL default '',
  `author_name` varchar(50) NOT NULL default '',
  `author_email` varchar(100) NOT NULL default '',
  `author_user_forum` varchar(50) NOT NULL default '',
  `author_user_repository` varchar(50) NOT NULL default '',
  `version` smallint(5) unsigned NOT NULL default '0',
  `version_cacti` smallint(5) unsigned NOT NULL default '0',
  `date_create` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `description_install` text NOT NULL,
  `category` varchar(50) NOT NULL default '',
  `subcategory` varchar(50) NOT NULL default '',
  `vendor` varchar(50) NOT NULL default '',
  `model` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `package`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `package_author`
-- 

CREATE TABLE `package_author` (
  `id` smallint(5) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `user_forum` varchar(50) NOT NULL default '',
  `user_repository` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `package_author`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `package_graph_template`
-- 

CREATE TABLE `package_graph_template` (
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_template_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`package_id`,`graph_template_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `package_graph_template`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `package_metadata`
-- 

CREATE TABLE `package_metadata` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `type` smallint(5) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `description_install` text NOT NULL,
  `required` tinyint(1) NOT NULL default '0',
  `mime_type` varchar(100) NOT NULL default '',
  `payload` blob NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `package_metadata`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poller`
-- 

CREATE TABLE `poller` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `run_state` varchar(20) default 'Wait',
  `active` varchar(5) default 'On',
  `hostname` varchar(250) NOT NULL default '',
  `name` varchar(150) default NULL,
  `hosts` smallint(5) unsigned NOT NULL default '0',
  `num_total` mediumint(8) unsigned NOT NULL default '0',
  `num_snmp` mediumint(8) unsigned NOT NULL default '0',
  `num_script` mediumint(8) unsigned NOT NULL default '0',
  `num_script_ss_php` mediumint(8) unsigned NOT NULL default '0',
  `num_internal` mediumint(8) unsigned NOT NULL default '0',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_event_count` mediumint(8) NOT NULL default '0',
  `status_fail_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_rec_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_last_error` varchar(50) NOT NULL default '',
  `min_time` decimal(9,5) NOT NULL default '9999.99000',
  `max_time` decimal(9,5) NOT NULL default '0.00000',
  `cur_time` decimal(9,5) NOT NULL default '0.00000',
  `avg_time` decimal(9,5) NOT NULL default '0.00000',
  `total_polls` bigint(20) NOT NULL default '0',
  `failed_polls` bigint(20) NOT NULL default '0',
  `availability` decimal(7,5) NOT NULL default '100.00000',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller`
-- 

INSERT INTO `poller` VALUES (1, 'Wait', 'on', 'locahost', 'Main Cacti System', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 0.00000, 0.00000, 0.00000, 0.00000, 0, 0, 0.00000);

-- --------------------------------------------------------

-- 
-- Table structure for table `poller_command`
-- 

CREATE TABLE `poller_command` (
  `poller_id` smallint(5) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `action` tinyint(3) unsigned NOT NULL default '0',
  `command` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`poller_id`,`action`,`command`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller_command`
-- 

INSERT INTO `poller_command` VALUES (0, '2005-04-10 00:52:41', 1, '2:1');
INSERT INTO `poller_command` VALUES (0, '2005-04-10 00:52:41', 1, '2:8');
INSERT INTO `poller_command` VALUES (0, '2005-04-10 00:52:41', 1, '2:9');

-- --------------------------------------------------------

-- 
-- Table structure for table `poller_item`
-- 

CREATE TABLE `poller_item` (
  `local_data_id` mediumint(8) unsigned NOT NULL default '0',
  `poller_id` smallint(5) unsigned NOT NULL default '0',
  `host_id` mediumint(8) NOT NULL default '0',
  `action` tinyint(2) unsigned NOT NULL default '1',
  `hostname` varchar(250) NOT NULL default '',
  `snmp_community` varchar(100) NOT NULL default '',
  `snmp_version` tinyint(1) unsigned NOT NULL default '0',
  `snmpv3_auth_username` varchar(50) default NULL,
  `snmpv3_auth_password` varchar(50) default NULL,
  `snmpv3_auth_protocol` varchar(5) default NULL,
  `snmpv3_priv_passphrase` varchar(200) default NULL,
  `snmpv3_priv_protocol` varchar(6) default NULL,
  `snmp_port` mediumint(5) unsigned NOT NULL default '161',
  `snmp_timeout` mediumint(8) unsigned NOT NULL default '0',
  `availability_method` smallint(5) unsigned NOT NULL default '1',
  `ping_method` smallint(5) unsigned default '0',
  `rrd_name` varchar(19) NOT NULL default '',
  `rrd_path` varchar(255) NOT NULL default '',
  `rrd_num` tinyint(2) unsigned NOT NULL default '0',
  `rrd_step` mediumint(8) unsigned NOT NULL default '0',
  `rrd_next_step` mediumint(9) NOT NULL default '0',
  `arg1` text default NULL,
  `arg2` varchar(255) default NULL,
  `arg3` varchar(255) default NULL,
  PRIMARY KEY  (`local_data_id`,`rrd_name`),
  KEY `local_data_id` (`local_data_id`),
  KEY `host_id` (`host_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller_item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poller_output`
-- 

CREATE TABLE `poller_output` (
  `local_data_id` mediumint(8) unsigned NOT NULL default '0',
  `rrd_name` varchar(19) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `output` text NOT NULL,
  PRIMARY KEY  (`local_data_id`,`rrd_name`,`time`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller_output`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poller_reindex`
-- 

CREATE TABLE `poller_reindex` (
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  `data_query_id` mediumint(8) unsigned NOT NULL default '0',
  `action` tinyint(3) unsigned NOT NULL default '0',
  `op` char(1) NOT NULL default '',
  `assert_value` varchar(100) NOT NULL default '',
  `arg1` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`host_id`,`data_query_id`,`arg1`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller_reindex`
-- 

INSERT INTO `poller_reindex` VALUES (1, 6, 0, '<', '4566094', '.1.3.6.1.2.1.1.3.0');
INSERT INTO `poller_reindex` VALUES (2, 1, 0, '<', '712854', '.1.3.6.1.2.1.1.3.0');
INSERT INTO `poller_reindex` VALUES (2, 8, 0, '<', '712854', '.1.3.6.1.2.1.1.3.0');
INSERT INTO `poller_reindex` VALUES (2, 9, 0, '<', '712856', '.1.3.6.1.2.1.1.3.0');

-- --------------------------------------------------------

-- 
-- Table structure for table `poller_time`
-- 

CREATE TABLE `poller_time` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `poller_id` smallint(5) unsigned NOT NULL default '0',
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poller_time`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `preset_cdef`
-- 

CREATE TABLE `preset_cdef` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `cdef_string` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cdef_string` (`cdef_string`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_cdef`
-- 

INSERT INTO `preset_cdef` VALUES (1, 'Make Stack Negative', 'CURRENT_DATA_SOURCE,-1,*');
INSERT INTO `preset_cdef` VALUES (2, 'Multiply by 1024', 'CURRENT_DATA_SOURCE,1024,*');
INSERT INTO `preset_cdef` VALUES (3, 'Total All Data Sources', 'ALL_DATA_SOURCES_NODUPS');
INSERT INTO `preset_cdef` VALUES (4, 'Total All Data Sources, Multiply by 1024', 'ALL_DATA_SOURCES_NODUPS,1024,*');
INSERT INTO `preset_cdef` VALUES (5, 'Turn Bytes into Bits', 'CURRENT_DATA_SOURCE,8,*');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_color`
-- 

CREATE TABLE `preset_color` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `hex` char(6) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hex` (`hex`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_color`
-- 

INSERT INTO `preset_color` VALUES (1, '000000');
INSERT INTO `preset_color` VALUES (2, 'FFFFFF');
INSERT INTO `preset_color` VALUES (4, 'FAFD9E');
INSERT INTO `preset_color` VALUES (5, 'C0C0C0');
INSERT INTO `preset_color` VALUES (6, '74C366');
INSERT INTO `preset_color` VALUES (7, '6DC8FE');
INSERT INTO `preset_color` VALUES (8, 'EA8F00');
INSERT INTO `preset_color` VALUES (9, 'FF0000');
INSERT INTO `preset_color` VALUES (10, '4444FF');
INSERT INTO `preset_color` VALUES (11, 'FF00FF');
INSERT INTO `preset_color` VALUES (12, '00FF00');
INSERT INTO `preset_color` VALUES (13, '8D85F3');
INSERT INTO `preset_color` VALUES (14, 'AD3B6E');
INSERT INTO `preset_color` VALUES (15, 'EACC00');
INSERT INTO `preset_color` VALUES (16, '12B3B5');
INSERT INTO `preset_color` VALUES (17, '157419');
INSERT INTO `preset_color` VALUES (18, 'C4FD3D');
INSERT INTO `preset_color` VALUES (19, '817C4E');
INSERT INTO `preset_color` VALUES (20, '002A97');
INSERT INTO `preset_color` VALUES (21, '0000FF');
INSERT INTO `preset_color` VALUES (22, '00CF00');
INSERT INTO `preset_color` VALUES (24, 'F9FD5F');
INSERT INTO `preset_color` VALUES (25, 'FFF200');
INSERT INTO `preset_color` VALUES (26, 'CCBB00');
INSERT INTO `preset_color` VALUES (27, '837C04');
INSERT INTO `preset_color` VALUES (28, 'EAAF00');
INSERT INTO `preset_color` VALUES (29, 'FFD660');
INSERT INTO `preset_color` VALUES (30, 'FFC73B');
INSERT INTO `preset_color` VALUES (31, 'FFAB00');
INSERT INTO `preset_color` VALUES (33, 'FF7D00');
INSERT INTO `preset_color` VALUES (34, 'ED7600');
INSERT INTO `preset_color` VALUES (35, 'FF5700');
INSERT INTO `preset_color` VALUES (36, 'EE5019');
INSERT INTO `preset_color` VALUES (37, 'B1441E');
INSERT INTO `preset_color` VALUES (38, 'FFC3C0');
INSERT INTO `preset_color` VALUES (39, 'FF897C');
INSERT INTO `preset_color` VALUES (40, 'FF6044');
INSERT INTO `preset_color` VALUES (41, 'FF4105');
INSERT INTO `preset_color` VALUES (42, 'DA4725');
INSERT INTO `preset_color` VALUES (43, '942D0C');
INSERT INTO `preset_color` VALUES (44, 'FF3932');
INSERT INTO `preset_color` VALUES (45, '862F2F');
INSERT INTO `preset_color` VALUES (46, 'FF5576');
INSERT INTO `preset_color` VALUES (47, '562B29');
INSERT INTO `preset_color` VALUES (48, 'F51D30');
INSERT INTO `preset_color` VALUES (49, 'DE0056');
INSERT INTO `preset_color` VALUES (50, 'ED5394');
INSERT INTO `preset_color` VALUES (51, 'B90054');
INSERT INTO `preset_color` VALUES (52, '8F005C');
INSERT INTO `preset_color` VALUES (53, 'F24AC8');
INSERT INTO `preset_color` VALUES (54, 'E8CDEF');
INSERT INTO `preset_color` VALUES (55, 'D8ACE0');
INSERT INTO `preset_color` VALUES (56, 'A150AA');
INSERT INTO `preset_color` VALUES (57, '750F7D');
INSERT INTO `preset_color` VALUES (58, '8D00BA');
INSERT INTO `preset_color` VALUES (59, '623465');
INSERT INTO `preset_color` VALUES (60, '55009D');
INSERT INTO `preset_color` VALUES (61, '3D168B');
INSERT INTO `preset_color` VALUES (62, '311F4E');
INSERT INTO `preset_color` VALUES (63, 'D2D8F9');
INSERT INTO `preset_color` VALUES (64, '9FA4EE');
INSERT INTO `preset_color` VALUES (65, '6557D0');
INSERT INTO `preset_color` VALUES (66, '4123A1');
INSERT INTO `preset_color` VALUES (67, '4668E4');
INSERT INTO `preset_color` VALUES (70, '001D61');
INSERT INTO `preset_color` VALUES (71, '00234B');
INSERT INTO `preset_color` VALUES (72, '002A8F');
INSERT INTO `preset_color` VALUES (73, '2175D9');
INSERT INTO `preset_color` VALUES (74, '7CB3F1');
INSERT INTO `preset_color` VALUES (75, '005199');
INSERT INTO `preset_color` VALUES (76, '004359');
INSERT INTO `preset_color` VALUES (77, '00A0C1');
INSERT INTO `preset_color` VALUES (78, '007283');
INSERT INTO `preset_color` VALUES (79, '00BED9');
INSERT INTO `preset_color` VALUES (80, 'AFECED');
INSERT INTO `preset_color` VALUES (81, '55D6D3');
INSERT INTO `preset_color` VALUES (82, '00BBB4');
INSERT INTO `preset_color` VALUES (83, '009485');
INSERT INTO `preset_color` VALUES (84, '005D57');
INSERT INTO `preset_color` VALUES (85, '008A77');
INSERT INTO `preset_color` VALUES (86, '008A6D');
INSERT INTO `preset_color` VALUES (87, '00B99B');
INSERT INTO `preset_color` VALUES (88, '009F67');
INSERT INTO `preset_color` VALUES (89, '00694A');
INSERT INTO `preset_color` VALUES (90, '00A348');
INSERT INTO `preset_color` VALUES (91, '00BF47');
INSERT INTO `preset_color` VALUES (92, '96E78A');
INSERT INTO `preset_color` VALUES (93, '00BD27');
INSERT INTO `preset_color` VALUES (94, '35962B');
INSERT INTO `preset_color` VALUES (95, '7EE600');
INSERT INTO `preset_color` VALUES (96, '6EA100');
INSERT INTO `preset_color` VALUES (97, 'CAF100');
INSERT INTO `preset_color` VALUES (98, 'F5F800');
INSERT INTO `preset_color` VALUES (99, 'CDCFC4');
INSERT INTO `preset_color` VALUES (100, 'BCBEB3');
INSERT INTO `preset_color` VALUES (101, 'AAABA1');
INSERT INTO `preset_color` VALUES (102, '8F9286');
INSERT INTO `preset_color` VALUES (103, '797C6E');
INSERT INTO `preset_color` VALUES (104, '2E3127');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_gprint`
-- 

CREATE TABLE `preset_gprint` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `gprint_text` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gprint_text` (`gprint_text`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_gprint`
-- 

INSERT INTO `preset_gprint` VALUES (2, 'Normal', '%8.2lf %s');
INSERT INTO `preset_gprint` VALUES (3, 'Exact Numbers', '%8.0lf');
INSERT INTO `preset_gprint` VALUES (4, 'Load Average', '%8.2lf');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_package_category`
-- 

CREATE TABLE `preset_package_category` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_package_category`
-- 

INSERT INTO `preset_package_category` VALUES (1, 'Application');
INSERT INTO `preset_package_category` VALUES (2, 'Operating System');
INSERT INTO `preset_package_category` VALUES (3, 'Networking Equipment');
INSERT INTO `preset_package_category` VALUES (4, 'Sensor Equipment');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_package_subcategory`
-- 

CREATE TABLE `preset_package_subcategory` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_package_subcategory`
-- 

INSERT INTO `preset_package_subcategory` VALUES (1, 'DNS');
INSERT INTO `preset_package_subcategory` VALUES (2, 'Mail');
INSERT INTO `preset_package_subcategory` VALUES (3, 'Chat');
INSERT INTO `preset_package_subcategory` VALUES (4, 'Router');
INSERT INTO `preset_package_subcategory` VALUES (5, 'Switch');
INSERT INTO `preset_package_subcategory` VALUES (6, 'Database');
INSERT INTO `preset_package_subcategory` VALUES (7, 'Disk');
INSERT INTO `preset_package_subcategory` VALUES (8, 'Weather');
INSERT INTO `preset_package_subcategory` VALUES (9, 'Temperature');
INSERT INTO `preset_package_subcategory` VALUES (10, 'Memory');
INSERT INTO `preset_package_subcategory` VALUES (11, 'CPU');
INSERT INTO `preset_package_subcategory` VALUES (12, 'Traffic');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_package_vendor`
-- 

CREATE TABLE `preset_package_vendor` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_package_vendor`
-- 

INSERT INTO `preset_package_vendor` VALUES (1, 'Cisco');
INSERT INTO `preset_package_vendor` VALUES (2, 'Juniper');
INSERT INTO `preset_package_vendor` VALUES (3, 'Microsoft');
INSERT INTO `preset_package_vendor` VALUES (4, 'Novell');
INSERT INTO `preset_package_vendor` VALUES (5, 'VMware');
INSERT INTO `preset_package_vendor` VALUES (6, 'Linksys');
INSERT INTO `preset_package_vendor` VALUES (7, '3Com');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_rra`
-- 

CREATE TABLE `preset_rra` (
  `id` mediumint(8) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `fingerprint` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_rra`
-- 

INSERT INTO `preset_rra` VALUES (1, 'Default', '0002:20e8|0003:8ead|0005:728e|0007:382b|0001:56f8|0004:6c2c|0006:1ac7|0008:a5ec');

-- --------------------------------------------------------

-- 
-- Table structure for table `preset_rra_item`
-- 

CREATE TABLE `preset_rra_item` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `preset_rra_id` mediumint(8) unsigned NOT NULL default '0',
  `consolidation_function` tinyint(3) unsigned NOT NULL default '0',
  `steps` smallint(5) unsigned NOT NULL default '0',
  `rows` int(11) unsigned NOT NULL default '0',
  `x_files_factor` decimal(5,4) NOT NULL default '0.0000',
  `hw_alpha` decimal(5,4) NOT NULL default '0.0000',
  `hw_beta` decimal(5,4) NOT NULL default '0.0000',
  `hw_gamma` decimal(5,4) NOT NULL default '0.0000',
  `hw_seasonal_period` int(11) unsigned NOT NULL default '0',
  `hw_rra_num` tinyint(3) unsigned NOT NULL default '0',
  `hw_threshold` tinyint(3) unsigned NOT NULL default '0',
  `hw_window_length` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `preset_rra_item`
-- 

INSERT INTO `preset_rra_item` VALUES (1, 1, 3, 1, 86400, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (2, 1, 1, 1, 86400, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (6, 1, 3, 24, 2592000, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (5, 1, 1, 24, 2592000, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (3, 1, 1, 6, 604800, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (4, 1, 3, 6, 604800, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (7, 1, 1, 288, 31536000, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);
INSERT INTO `preset_rra_item` VALUES (8, 1, 3, 288, 31536000, 0.5000, 0.0000, 0.0000, 0.0000, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `rra`
-- 

CREATE TABLE `rra` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `package_id` mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `x_files_factor` double NOT NULL default '0.1',
  `steps` mediumint(8) default '1',
  `rows` int(12) NOT NULL default '600',
  `timespan` int(12) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `rra`
-- 

INSERT INTO `rra` VALUES (1, 0, 'Daily (5 Minute Average)', 0.5, 1, 600, 86400);
INSERT INTO `rra` VALUES (2, 0, 'Weekly (30 Minute Average)', 0.5, 6, 700, 604800);
INSERT INTO `rra` VALUES (4, 0, 'Yearly (1 Day Average)', 0.5, 288, 797, 33053184);
INSERT INTO `rra` VALUES (3, 6, 'Monthly (2 Hour Average)', 0.5, 24, 775, 2678400);

-- --------------------------------------------------------

-- 
-- Table structure for table `rra_cf`
-- 

CREATE TABLE `rra_cf` (
  `rra_id` mediumint(8) unsigned NOT NULL default '0',
  `consolidation_function_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rra_id`,`consolidation_function_id`),
  KEY `rra_id` (`rra_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `rra_cf`
-- 

INSERT INTO `rra_cf` VALUES (1, 1);
INSERT INTO `rra_cf` VALUES (1, 3);
INSERT INTO `rra_cf` VALUES (2, 1);
INSERT INTO `rra_cf` VALUES (2, 3);
INSERT INTO `rra_cf` VALUES (3, 1);
INSERT INTO `rra_cf` VALUES (3, 3);
INSERT INTO `rra_cf` VALUES (4, 1);
INSERT INTO `rra_cf` VALUES (4, 3);

-- --------------------------------------------------------

-- 
-- Table structure for table `settings`
-- 

CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `settings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `settings_graphs`
-- 

CREATE TABLE `settings_graphs` (
  `user_id` smallint(8) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `settings_graphs`
-- 

INSERT INTO `settings_graphs` VALUES (1, 'general', '');
INSERT INTO `settings_graphs` VALUES (1, 'tree', '');
INSERT INTO `settings_graphs` VALUES (1, 'default_rra_id', '1');
INSERT INTO `settings_graphs` VALUES (1, 'default_view_mode', '1');
INSERT INTO `settings_graphs` VALUES (1, 'default_timespan', '7');
INSERT INTO `settings_graphs` VALUES (1, 'timespan_sel', 'on');
INSERT INTO `settings_graphs` VALUES (1, 'default_date_format', '4');
INSERT INTO `settings_graphs` VALUES (1, 'default_datechar', '1');
INSERT INTO `settings_graphs` VALUES (1, 'page_refresh', '300');
INSERT INTO `settings_graphs` VALUES (1, 'default_height', '100');
INSERT INTO `settings_graphs` VALUES (1, 'default_width', '300');
INSERT INTO `settings_graphs` VALUES (1, 'num_columns', '2');
INSERT INTO `settings_graphs` VALUES (1, 'thumbnail_section_preview', 'on');
INSERT INTO `settings_graphs` VALUES (1, 'thumbnail_section_tree_1', 'on');
INSERT INTO `settings_graphs` VALUES (1, 'thumbnail_section_tree_2', '');
INSERT INTO `settings_graphs` VALUES (1, 'default_tree_id', '1');
INSERT INTO `settings_graphs` VALUES (1, 'default_tree_view_mode', '2');
INSERT INTO `settings_graphs` VALUES (1, 'expand_hosts', 'on');
INSERT INTO `settings_graphs` VALUES (1, 'preview_graphs_per_page', '10');
INSERT INTO `settings_graphs` VALUES (1, 'list_graphs_per_page', '10');

-- --------------------------------------------------------

-- 
-- Table structure for table `settings_tree`
-- 

CREATE TABLE `settings_tree` (
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `graph_tree_item_id` mediumint(8) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`graph_tree_item_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `settings_tree`
-- 

INSERT INTO `settings_tree` VALUES (3, 87, 0);
INSERT INTO `settings_tree` VALUES (3, 63, 0);
INSERT INTO `settings_tree` VALUES (3, 90, 0);
INSERT INTO `settings_tree` VALUES (3, 85, 0);
INSERT INTO `settings_tree` VALUES (3, 109, 0);
INSERT INTO `settings_tree` VALUES (3, 92, 1);
INSERT INTO `settings_tree` VALUES (3, 7, 0);
INSERT INTO `settings_tree` VALUES (3, 84, 0);
INSERT INTO `settings_tree` VALUES (3, 88, 0);
INSERT INTO `settings_tree` VALUES (3, 94, 0);
INSERT INTO `settings_tree` VALUES (3, 91, 0);
INSERT INTO `settings_tree` VALUES (3, 89, 0);
INSERT INTO `settings_tree` VALUES (3, 93, 0);
INSERT INTO `settings_tree` VALUES (3, 83, 0);
INSERT INTO `settings_tree` VALUES (3, 82, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `snmp_template`
-- 

CREATE TABLE `snmp_template` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `snmp_template`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `snmp_template_auth`
-- 

CREATE TABLE `snmp_template_auth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `snmp_id` int(11) unsigned NOT NULL default '0',
  `snmp_version` tinyint(4) unsigned NOT NULL default '0',
  `snmp_community` varchar(100) NOT NULL default '',
  `snmpv3_auth_username` varchar(50) NOT NULL default '',
  `snmpv3_auth_password` varchar(50) NOT NULL default '',
  `snmpv3_authproto` varchar(5) NOT NULL default '',
  `snmpv3_priv_passphrase` varchar(200) NOT NULL default '',
  `snmpv3_priv_proto` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `snmp_id` (`snmp_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `snmp_template_auth`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_auth`
-- 

CREATE TABLE `user_auth` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '0',
  `password` varchar(50) NOT NULL default '0',
  `realm` mediumint(8) NOT NULL default '0',
  `full_name` varchar(100) default '0',
  `must_change_password` char(2) default NULL,
  `show_tree` char(2) default 'on',
  `show_list` char(2) default 'on',
  `show_preview` char(2) NOT NULL default 'on',
  `graph_settings` char(2) default NULL,
  `login_opts` tinyint(1) NOT NULL default '1',
  `policy_graphs` tinyint(1) unsigned NOT NULL default '1',
  `policy_trees` tinyint(1) unsigned NOT NULL default '1',
  `policy_hosts` tinyint(1) unsigned NOT NULL default '1',
  `policy_graph_templates` tinyint(1) unsigned NOT NULL default '1',
  `enabled` tinyint(1) unsigned NOT NULL default '1',
  `password_expire_length` int(4) unsigned NOT NULL default '0',
  `password_change_last` datetime NOT NULL default '0000-00-00 00:00:00',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `current_theme` varchar(25) NOT NULL default 'default',
  `email_address_primary` varchar(255) NOT NULL default '',
  `email_address_secondary` varchar(255) NOT NULL default '',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login_ip` varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `user_auth`
-- 

INSERT INTO `user_auth` VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 0, 'Administrator', '', 'on', 'on', 'on', 'on', 1, 1, 1, 1, 1, 1, 0, '0000-00-00 00:00:00', '2004-12-29 20:59:45', 'classic', '', '', '2006-04-24 21:40:02', '192.168.1.101');
INSERT INTO `user_auth` VALUES (3, 'guest', '43e9a4ab75570f5b', 0, 'Guest Account', 'on', 'on', 'on', 'on', 'on', 3, 1, 1, 1, 1, 1, 0, '0000-00-00 00:00:00', '2004-12-29 20:59:45', 'default', '', '', '0000-00-00 00:00:00', '0.0.0.0');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_auth_perms`
-- 

CREATE TABLE `user_auth_perms` (
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `item_id` mediumint(8) unsigned NOT NULL default '0',
  `type` tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`item_id`,`type`),
  KEY `user_id` (`user_id`,`type`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `user_auth_perms`
-- 

INSERT INTO `user_auth_perms` VALUES (1, 11, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `user_auth_realm`
-- 

CREATE TABLE `user_auth_realm` (
  `realm_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`realm_id`,`user_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `user_auth_realm`
-- 

INSERT INTO `user_auth_realm` VALUES (1, 1);
INSERT INTO `user_auth_realm` VALUES (2, 1);
INSERT INTO `user_auth_realm` VALUES (3, 1);
INSERT INTO `user_auth_realm` VALUES (4, 1);
INSERT INTO `user_auth_realm` VALUES (5, 1);
INSERT INTO `user_auth_realm` VALUES (7, 1);
INSERT INTO `user_auth_realm` VALUES (7, 3);
INSERT INTO `user_auth_realm` VALUES (8, 1);
INSERT INTO `user_auth_realm` VALUES (9, 1);
INSERT INTO `user_auth_realm` VALUES (10, 1);
INSERT INTO `user_auth_realm` VALUES (11, 1);
INSERT INTO `user_auth_realm` VALUES (12, 1);
INSERT INTO `user_auth_realm` VALUES (13, 1);
INSERT INTO `user_auth_realm` VALUES (14, 1);
INSERT INTO `user_auth_realm` VALUES (15, 1);
INSERT INTO `user_auth_realm` VALUES (16, 1);
INSERT INTO `user_auth_realm` VALUES (17, 1);
INSERT INTO `user_auth_realm` VALUES (18, 1);
INSERT INTO `user_auth_realm` VALUES (19, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `version`
-- 

CREATE TABLE `version` (
  `cacti` char(20) default NULL
) TYPE=MyISAM;

-- 
-- Dumping data for table `version`
-- 

INSERT INTO `version` VALUES ('0.9-dev');
