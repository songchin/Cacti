-- MySQL dump 9.09
--
-- Host: localhost    Database: cacti_dev_MAIN
---------------------------------------------------------
-- Server version	4.0.15-standard

--
-- Table structure for table `data_input`
--

CREATE TABLE data_input (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  name varchar(200) NOT NULL default '',
  input_string varchar(255) default NULL,
  type_id tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_input`
--

INSERT INTO data_input VALUES (3,'274f4685461170b9eb1b98d22567ab5e','Unix - Get Free Disk Space','<path_cacti>/scripts/diskfree.sh <partition>',1);
INSERT INTO data_input VALUES (5,'79a284e136bb6b061c6f96ec219ac448','Unix - Get Logged In Users','perl <path_cacti>/scripts/unix_users.pl <username>',1);
INSERT INTO data_input VALUES (6,'362e6d4768937c4f899dd21b91ef0ff8','Linux - Get Memory Usage','perl <path_cacti>/scripts/linux_memory.pl <grepstr> <blah>',1);
INSERT INTO data_input VALUES (7,'a637359e0a4287ba43048a5fdf202066','Unix - Get System Processes','perl <path_cacti>/scripts/unix_processes.pl',1);
INSERT INTO data_input VALUES (8,'47d6bfe8be57a45171afd678920bd399','Unix - Get TCP Connections','perl <path_cacti>/scripts/unix_tcp_connections.pl <grepstr>',1);
INSERT INTO data_input VALUES (9,'cc948e4de13f32b6aea45abaadd287a3','Unix - Get Web Hits','perl <path_cacti>/scripts/webhits.pl <log_path>',1);
INSERT INTO data_input VALUES (10,'8bd153aeb06e3ff89efc73f35849a7a0','Unix - Ping Host','perl <path_cacti>/scripts/ping.pl <ip>',1);

--
-- Table structure for table `data_input_fields`
--

CREATE TABLE data_input_fields (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  data_input_id mediumint(8) unsigned NOT NULL default '0',
  field_input_type tinyint(1) unsigned NOT NULL default '1',
  field_input_value varchar(100) NOT NULL default '',
  name varchar(200) NOT NULL default '',
  data_name varchar(50) NOT NULL default '',
  input_output char(3) NOT NULL default '',
  update_rrd tinyint(1) unsigned NOT NULL default '0',
  regexp_match varchar(100) NOT NULL default '',
  allow_empty tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY u_field_name (data_input_id,data_name,input_output),
  KEY data_input_id (data_input_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_input_fields`
--

INSERT INTO data_input_fields VALUES (15,'edfd72783ad02df128ff82fc9324b4b9',3,1,'','Disk Partition','partition','in',0,'',0);
INSERT INTO data_input_fields VALUES (16,'8b75fb61d288f0b5fc0bd3056af3689b',3,1,'','Kilobytes Free','kilobytes','out',0,'',0);
INSERT INTO data_input_fields VALUES (20,'c0cfd0beae5e79927c5a360076706820',5,1,'','Username (Optional)','username','in',0,'',0);
INSERT INTO data_input_fields VALUES (21,'52c58ad414d9a2a83b00a7a51be75a53',5,1,'','Logged In Users','users','out',0,'',0);
INSERT INTO data_input_fields VALUES (22,'05eb5d710f0814871b8515845521f8d7',6,1,'','Grep String','grepstr','in',0,'',0);
INSERT INTO data_input_fields VALUES (23,'86cb1cbfde66279dbc7f1144f43a3219',6,1,'','Result (in Kilobytes)','kilobytes','out',0,'',0);
INSERT INTO data_input_fields VALUES (24,'d5a8dd5fbe6a5af11667c0039af41386',7,1,'','Number of Processes','proc','out',0,'',0);
INSERT INTO data_input_fields VALUES (25,'8848cdcae831595951a3f6af04eec93b',8,1,'','Grep String','grepstr','in',0,'',0);
INSERT INTO data_input_fields VALUES (26,'3d1288d33008430ce354e8b9c162f7ff',8,1,'','Connections','connections','out',0,'',0);
INSERT INTO data_input_fields VALUES (27,'c6af570bb2ed9c84abf32033702e2860',9,1,'','(Optional) Log Path','log_path','in',0,'',0);
INSERT INTO data_input_fields VALUES (28,'f9389860f5c5340c9b27fca0b4ee5e71',9,1,'','Web Hits','webhits','out',0,'',0);
INSERT INTO data_input_fields VALUES (29,'5fbadb91ad66f203463c1187fe7bd9d5',10,1,'sdfsdfs','IP Address','ip','in',1,'',0);
INSERT INTO data_input_fields VALUES (30,'6ac4330d123c69067d36a933d105e89a',10,0,'','Milliseconds','out_ms','out',1,'',0);
INSERT INTO data_input_fields VALUES (50,'b5159c77608386cfa608fc99c2bd0430',6,1,'','BLAH','blah','in',0,'',0);
INSERT INTO data_input_fields VALUES (52,'9d39f6c3a93abf8d9ab9526fb01daa92',10,0,'','sdf','sd','out',1,'',0);

--
-- Table structure for table `data_source`
--

CREATE TABLE data_source (
  id mediumint(8) unsigned NOT NULL auto_increment,
  host_id mediumint(8) unsigned NOT NULL default '0',
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  data_input_type tinyint(1) unsigned NOT NULL default '3',
  name varchar(255) NOT NULL default '',
  name_cache varchar(255) NOT NULL default '',
  active tinyint(1) unsigned NOT NULL default '1',
  rrd_path varchar(255) NOT NULL default '',
  rrd_step smallint(5) unsigned NOT NULL default '300',
  PRIMARY KEY  (id),
  KEY host_id (host_id),
  KEY data_template_id (data_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_source`
--


--
-- Table structure for table `data_source_field`
--

CREATE TABLE data_source_field (
  data_source_id mediumint(8) unsigned NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  value text NOT NULL,
  PRIMARY KEY  (data_source_id,name),
  KEY data_source_id (data_source_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_source_field`
--


--
-- Table structure for table `data_source_item`
--

CREATE TABLE data_source_item (
  id mediumint(8) unsigned NOT NULL auto_increment,
  data_source_id mediumint(8) unsigned NOT NULL default '0',
  rrd_maximum varchar(20) NOT NULL default '0',
  rrd_minimum varchar(20) NOT NULL default '0',
  rrd_heartbeat mediumint(5) unsigned NOT NULL default '600',
  data_source_type tinyint(1) unsigned NOT NULL default '1',
  data_source_name varchar(19) NOT NULL default '',
  field_input_value varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY data_source_name (data_source_id,data_source_name),
  KEY data_source_id (data_source_id),
  KEY data_input_field_name (field_input_value)
) TYPE=MyISAM;

--
-- Dumping data for table `data_source_item`
--


--
-- Table structure for table `data_source_rra`
--

CREATE TABLE data_source_rra (
  data_source_id mediumint(8) unsigned NOT NULL default '0',
  rra_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (data_source_id,rra_id),
  KEY data_source_id (data_source_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_source_rra`
--


--
-- Table structure for table `data_template`
--

CREATE TABLE data_template (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  template_name varchar(150) NOT NULL default '',
  data_input_type tinyint(1) unsigned NOT NULL default '3',
  t_name tinyint(1) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  t_active tinyint(1) unsigned NOT NULL default '0',
  active tinyint(1) unsigned NOT NULL default '1',
  t_rrd_step tinyint(1) unsigned NOT NULL default '0',
  rrd_step smallint(5) unsigned NOT NULL default '300',
  t_rra_id tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_template`
--

INSERT INTO data_template VALUES (1,'0870b096cb0697781665080bb1c7d4c0','Host MIB - CPU Utilization',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (2,'93ada7c0c04552dc78bc7ff5d38d2bb0','Host MIB - Disk Space',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (3,'ee358ecd645efa889e2377a90ec5bb42','Host MIB - Logged in Users',4,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (4,'141cf7d6757ba7d9fbc688c88f4a69d2','Host MIB - Processes',4,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (5,'f1bb22a7d6babf285c782cf7cc52dd73','Interface - Errors/Discards',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (6,'db798a898d1455771d315acac2fa14a2','Interface - Traffic (32-bit)',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (7,'dbe2e723adee33f711f865d14c98e254','Interface - Traffic (64-bit)',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (8,'acae87ae460427904c4c1118ce7a4aa4','Interface - Unicast Packets',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (9,'a7130a77505b0e4856e16abf35fc1c2c','Local Linux - Memory',3,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (10,'a4a625b625c5694d5bee69c977f3c4a7','Net-SNMP - CPU Usage',4,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (11,'01e312edf8ed3294941881c975b730a9','Net-SNMP - Disk Space',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (12,'cdc7accc12f0196534016016df35efb6','Net-SNMP - Load Average',4,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (13,'059c05f8e22ad00646a091706bd18c9d','Net-SNMP - Memory',4,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (14,'67dceb4eedd7a089ae89ae3173fc35b3','Local Unix - Disk Space',2,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (15,'5f586b98218af4c08655beefffb21eca','Local Unix - Logged In Users',3,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (16,'9ab46aa83ef47dae76141e4a2f0fae54','Local Unix - Processes',3,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (17,'0c346ccdea06ecdf663f07a62ef69414','Local Unix - Ping Host',3,0,'',0,1,0,300,0);
INSERT INTO data_template VALUES (18,'52bba296bef06e3da46ec54fb75bebf3','Interface - Non-Unicast Packets',2,0,'',0,1,0,300,0);

--
-- Table structure for table `data_template_field`
--

CREATE TABLE data_template_field (
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  t_value tinyint(1) unsigned NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (data_template_id,name),
  KEY data_template_id (data_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_template_field`
--

INSERT INTO data_template_field VALUES (1,'data_query_id',0,'9');
INSERT INTO data_template_field VALUES (2,'data_query_id',0,'8');
INSERT INTO data_template_field VALUES (3,'snmpv3_auth_protocol',0,'MD5');
INSERT INTO data_template_field VALUES (3,'snmpv3_auth_password_confirm',0,'');
INSERT INTO data_template_field VALUES (3,'snmpv3_auth_password',0,'');
INSERT INTO data_template_field VALUES (3,'snmpv3_auth_username',0,'');
INSERT INTO data_template_field VALUES (3,'snmp_community',0,'public');
INSERT INTO data_template_field VALUES (3,'snmp_version',0,'1');
INSERT INTO data_template_field VALUES (3,'snmp_timeout',0,'500');
INSERT INTO data_template_field VALUES (3,'snmp_port',0,'161');
INSERT INTO data_template_field VALUES (3,'snmpv3_priv_passphrase',0,'');
INSERT INTO data_template_field VALUES (3,'snmpv3_priv_protocol',0,'DES');
INSERT INTO data_template_field VALUES (4,'snmpv3_auth_protocol',0,'MD5');
INSERT INTO data_template_field VALUES (4,'snmpv3_auth_password_confirm',0,'');
INSERT INTO data_template_field VALUES (4,'snmpv3_auth_password',0,'');
INSERT INTO data_template_field VALUES (4,'snmpv3_auth_username',0,'');
INSERT INTO data_template_field VALUES (4,'snmp_community',0,'public');
INSERT INTO data_template_field VALUES (4,'snmp_version',0,'1');
INSERT INTO data_template_field VALUES (4,'snmp_timeout',0,'500');
INSERT INTO data_template_field VALUES (4,'snmp_port',0,'161');
INSERT INTO data_template_field VALUES (4,'snmpv3_priv_passphrase',0,'');
INSERT INTO data_template_field VALUES (4,'snmpv3_priv_protocol',0,'DES');
INSERT INTO data_template_field VALUES (5,'data_query_id',0,'1');
INSERT INTO data_template_field VALUES (6,'data_query_id',0,'1');
INSERT INTO data_template_field VALUES (7,'data_query_id',0,'1');
INSERT INTO data_template_field VALUES (8,'data_query_id',0,'1');
INSERT INTO data_template_field VALUES (9,'script_id',0,'6');
INSERT INTO data_template_field VALUES (9,'blah',0,'X');
INSERT INTO data_template_field VALUES (9,'grepstr',0,'X');
INSERT INTO data_template_field VALUES (10,'snmpv3_priv_passphrase',0,'');
INSERT INTO data_template_field VALUES (10,'snmpv3_auth_protocol',0,'MD5');
INSERT INTO data_template_field VALUES (10,'snmpv3_auth_password_confirm',0,'');
INSERT INTO data_template_field VALUES (10,'snmpv3_auth_password',0,'');
INSERT INTO data_template_field VALUES (10,'snmpv3_auth_username',0,'');
INSERT INTO data_template_field VALUES (10,'snmp_community',0,'public');
INSERT INTO data_template_field VALUES (10,'snmp_version',0,'1');
INSERT INTO data_template_field VALUES (10,'snmp_timeout',0,'500');
INSERT INTO data_template_field VALUES (10,'snmp_port',0,'161');
INSERT INTO data_template_field VALUES (10,'snmpv3_priv_protocol',0,'DES');
INSERT INTO data_template_field VALUES (11,'data_query_id',0,'2');
INSERT INTO data_template_field VALUES (12,'snmpv3_priv_passphrase',0,'');
INSERT INTO data_template_field VALUES (12,'snmpv3_priv_protocol',0,'DES');
INSERT INTO data_template_field VALUES (12,'snmpv3_auth_protocol',0,'MD5');
INSERT INTO data_template_field VALUES (12,'snmpv3_auth_password_confirm',0,'');
INSERT INTO data_template_field VALUES (12,'snmp_port',0,'161');
INSERT INTO data_template_field VALUES (12,'snmp_timeout',0,'500');
INSERT INTO data_template_field VALUES (12,'snmp_version',0,'1');
INSERT INTO data_template_field VALUES (12,'snmp_community',0,'public');
INSERT INTO data_template_field VALUES (12,'snmpv3_auth_username',0,'');
INSERT INTO data_template_field VALUES (12,'snmpv3_auth_password',0,'');
INSERT INTO data_template_field VALUES (13,'snmpv3_auth_password_confirm',0,'');
INSERT INTO data_template_field VALUES (13,'snmp_port',0,'161');
INSERT INTO data_template_field VALUES (13,'snmp_timeout',0,'500');
INSERT INTO data_template_field VALUES (13,'snmp_version',0,'1');
INSERT INTO data_template_field VALUES (13,'snmp_community',0,'');
INSERT INTO data_template_field VALUES (13,'snmpv3_auth_username',0,'');
INSERT INTO data_template_field VALUES (13,'snmpv3_auth_password',0,'');
INSERT INTO data_template_field VALUES (13,'snmpv3_auth_protocol',0,'MD5');
INSERT INTO data_template_field VALUES (13,'snmpv3_priv_passphrase',0,'');
INSERT INTO data_template_field VALUES (13,'snmpv3_priv_protocol',0,'DES');
INSERT INTO data_template_field VALUES (14,'data_query_id',0,'6');
INSERT INTO data_template_field VALUES (15,'username',0,'X');
INSERT INTO data_template_field VALUES (15,'script_id',0,'5');
INSERT INTO data_template_field VALUES (16,'script_id',0,'7');
INSERT INTO data_template_field VALUES (17,'script_id',0,'10');
INSERT INTO data_template_field VALUES (18,'data_query_id',0,'1');

--
-- Table structure for table `data_template_item`
--

CREATE TABLE data_template_item (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  t_rrd_maximum tinyint(1) unsigned NOT NULL default '0',
  rrd_maximum varchar(20) NOT NULL default '0',
  t_rrd_minimum tinyint(1) unsigned NOT NULL default '0',
  rrd_minimum varchar(20) NOT NULL default '0',
  t_rrd_heartbeat tinyint(1) unsigned NOT NULL default '0',
  rrd_heartbeat mediumint(5) unsigned NOT NULL default '600',
  t_data_source_type tinyint(1) unsigned NOT NULL default '0',
  data_source_type tinyint(1) unsigned NOT NULL default '1',
  t_data_source_name tinyint(1) unsigned NOT NULL default '0',
  data_source_name varchar(19) NOT NULL default '',
  field_input_value varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY data_source_name (data_template_id,data_source_name),
  KEY data_template_id (data_template_id),
  KEY data_input_field_name (field_input_value)
) TYPE=MyISAM;

--
-- Dumping data for table `data_template_item`
--

INSERT INTO data_template_item VALUES (1,'0c713174755b4b8e0ada1db724d05d25',1,0,'100',0,'0',0,600,0,1,0,'cpu','hrProcessorLoad');
INSERT INTO data_template_item VALUES (2,'2a4a31b6a7119ef22113ffbc92569d4f',2,0,'U',0,'0',0,600,0,1,0,'hdd_total','hrStorageSize');
INSERT INTO data_template_item VALUES (3,'7edaa66dfc3d3fe50b46d716c46e9dc6',2,0,'U',0,'0',0,600,0,1,0,'hdd_used','hrStorageUsed');
INSERT INTO data_template_item VALUES (4,'fb358a5a204bbcc6102b4658181b179e',3,0,'10000',0,'0',0,600,0,1,0,'users','.1.3.6.1.2.1.25.1.5.0');
INSERT INTO data_template_item VALUES (5,'ee4ded417c2d5b24c8bab7b0d9644053',4,0,'10000',0,'0',0,600,1,1,0,'proc','.1.3.6.1.2.1.25.1.6');
INSERT INTO data_template_item VALUES (6,'5dfa7deb6dbab32b61006a1b8cda8991',5,0,'U',0,'0',0,600,0,2,0,'discards_in','ifInDiscards');
INSERT INTO data_template_item VALUES (7,'17447cc6f4f9e0f09963537306100df6',5,0,'U',0,'0',0,600,0,2,0,'discards_out','ifOutDiscards');
INSERT INTO data_template_item VALUES (8,'49dd388e3de9a14285dbd66614a117df',5,0,'U',0,'0',0,600,0,2,0,'errors_in','ifInErrors');
INSERT INTO data_template_item VALUES (9,'7fc8f828bd980ada8c3416e2f96d6560',5,0,'U',0,'0',0,600,0,2,0,'errors_out','ifOutErrors');
INSERT INTO data_template_item VALUES (10,'21e72c6fbcd014d8cc7c70c65a653cc3',6,0,'U',0,'0',0,600,0,2,0,'traffic_in','ifInOctets');
INSERT INTO data_template_item VALUES (11,'b3608ecf381876af2516846ca2a34972',6,0,'U',0,'0',0,600,0,2,0,'traffic_out','ifOutOctets');
INSERT INTO data_template_item VALUES (12,'497cf6d33719f149cf8c97aed07a0d83',7,0,'U',0,'0',0,600,0,2,0,'traffic_in','ifHCInOctets');
INSERT INTO data_template_item VALUES (13,'c2d52f551ea9fcf0b448374e081642f9',7,0,'U',0,'0',0,600,0,2,0,'traffic_out','ifHCOutOctets');
INSERT INTO data_template_item VALUES (14,'3c83fcf292338976eccda483abfbf00a',8,0,'U',0,'0',0,600,0,2,0,'unicast_in','ifInUcastPkts');
INSERT INTO data_template_item VALUES (15,'b437881af5399d42a63d46b939576eb9',8,0,'U',0,'0',0,600,0,2,0,'unicast_out','ifOutUcastPkts');
INSERT INTO data_template_item VALUES (16,'2fcfda0d2fcefca23509139b7e6cd08e',9,0,'10000000',0,'0',0,600,0,1,0,'mem_buffers','kilobytes');
INSERT INTO data_template_item VALUES (17,'c850c1f411e48e4f36c54d60f2dff3de',10,0,'U',0,'0',0,600,0,2,0,'cpu_nice','.1.3.6.1.4.1.2021.11.51.0');
INSERT INTO data_template_item VALUES (18,'f6ddf36dbf3a4241073e5036ca98df85',10,0,'U',0,'0',0,600,0,2,0,'cpu_system','.1.3.6.1.4.1.2021.11.52.0');
INSERT INTO data_template_item VALUES (19,'9fad2ce6cde9d37e796d1a3169af8fb1',10,0,'U',0,'0',0,600,0,2,0,'cpu_user','.1.3.6.1.4.1.2021.11.50.0');
INSERT INTO data_template_item VALUES (20,'4d949e88adfa122db67e26ba1ecbeac6',11,0,'U',0,'0',0,600,0,1,0,'hdd_free','dskAvail');
INSERT INTO data_template_item VALUES (21,'f2c8f799d696c9a49c24237b281169a2',11,0,'U',0,'0',0,600,0,1,0,'hdd_used','dskUsed');
INSERT INTO data_template_item VALUES (22,'008c20b0b12e271e0e8977dc4597a957',12,0,'2000',0,'0',0,600,0,1,0,'load_1min','.1.3.6.1.4.1.2021.10.1.3.1');
INSERT INTO data_template_item VALUES (23,'2ceb8f56380052b75cf778546fa77cef',12,0,'2000',0,'0',0,600,0,1,0,'load_5min','.1.3.6.1.4.1.2021.10.1.3.2');
INSERT INTO data_template_item VALUES (24,'4eae7ee9929390e0c33fac1801260e2c',12,0,'2000',0,'0',0,600,0,1,0,'load_15min','.1.3.6.1.4.1.2021.10.1.3.3');
INSERT INTO data_template_item VALUES (25,'aa5ff4c237794dee0f973a523e2d1e53',13,0,'U',0,'0',0,600,1,1,0,'mem_buffers','.1.3.6.1.4.1.2021.4.14.0');
INSERT INTO data_template_item VALUES (26,'d0325fbce008f12110ed063b3c0dd717',13,0,'U',0,'0',0,600,0,1,0,'mem_free','.1.3.6.1.4.1.2021.4.6.0');
INSERT INTO data_template_item VALUES (27,'eebcb44ee8bcdc8eab533d2d618e2dea',14,0,'U',0,'0',0,600,0,1,0,'hdd_free','dskAvailable');
INSERT INTO data_template_item VALUES (28,'9b921838fb56846b1538da1745887593',14,0,'U',0,'0',0,600,0,1,0,'hdd_used','dskUsed');
INSERT INTO data_template_item VALUES (29,'cf6fac36856ced70460236ce699a618f',15,0,'1000',0,'0',0,600,0,1,0,'users','users');
INSERT INTO data_template_item VALUES (30,'ec57a538ba2c370490c13e8d01c95863',16,0,'2000',0,'0',0,600,0,1,0,'proc','proc');
INSERT INTO data_template_item VALUES (31,'2abc77b95eb175be26fe319c984ce2c2',17,0,'5000',0,'0',0,600,0,1,0,'ping','out_ms');
INSERT INTO data_template_item VALUES (32,'c44795808ddbd37eec97e16a52517234',18,0,'U',0,'0',0,600,0,2,0,'nonunicast_in','ifInNUcastPkts');
INSERT INTO data_template_item VALUES (33,'13ca317296739b37c4a1f8da91c7eec7',18,0,'U',0,'0',0,600,0,2,0,'nonunicast_out','ifOutNUcastPkts');

--
-- Table structure for table `data_template_rra`
--

CREATE TABLE data_template_rra (
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  rra_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (data_template_id,rra_id),
  KEY data_template_id (data_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `data_template_rra`
--

INSERT INTO data_template_rra VALUES (1,1);
INSERT INTO data_template_rra VALUES (1,2);
INSERT INTO data_template_rra VALUES (1,3);
INSERT INTO data_template_rra VALUES (1,4);
INSERT INTO data_template_rra VALUES (2,1);
INSERT INTO data_template_rra VALUES (2,2);
INSERT INTO data_template_rra VALUES (2,3);
INSERT INTO data_template_rra VALUES (2,4);
INSERT INTO data_template_rra VALUES (3,1);
INSERT INTO data_template_rra VALUES (3,2);
INSERT INTO data_template_rra VALUES (3,3);
INSERT INTO data_template_rra VALUES (3,4);
INSERT INTO data_template_rra VALUES (4,1);
INSERT INTO data_template_rra VALUES (4,2);
INSERT INTO data_template_rra VALUES (4,3);
INSERT INTO data_template_rra VALUES (4,4);
INSERT INTO data_template_rra VALUES (5,1);
INSERT INTO data_template_rra VALUES (5,2);
INSERT INTO data_template_rra VALUES (5,3);
INSERT INTO data_template_rra VALUES (5,4);
INSERT INTO data_template_rra VALUES (6,1);
INSERT INTO data_template_rra VALUES (6,2);
INSERT INTO data_template_rra VALUES (6,3);
INSERT INTO data_template_rra VALUES (6,4);
INSERT INTO data_template_rra VALUES (7,1);
INSERT INTO data_template_rra VALUES (7,2);
INSERT INTO data_template_rra VALUES (7,3);
INSERT INTO data_template_rra VALUES (7,4);
INSERT INTO data_template_rra VALUES (8,1);
INSERT INTO data_template_rra VALUES (8,2);
INSERT INTO data_template_rra VALUES (8,3);
INSERT INTO data_template_rra VALUES (8,4);
INSERT INTO data_template_rra VALUES (9,1);
INSERT INTO data_template_rra VALUES (9,2);
INSERT INTO data_template_rra VALUES (9,3);
INSERT INTO data_template_rra VALUES (9,4);
INSERT INTO data_template_rra VALUES (10,1);
INSERT INTO data_template_rra VALUES (10,2);
INSERT INTO data_template_rra VALUES (10,3);
INSERT INTO data_template_rra VALUES (10,4);
INSERT INTO data_template_rra VALUES (11,1);
INSERT INTO data_template_rra VALUES (11,2);
INSERT INTO data_template_rra VALUES (11,3);
INSERT INTO data_template_rra VALUES (11,4);
INSERT INTO data_template_rra VALUES (12,1);
INSERT INTO data_template_rra VALUES (12,2);
INSERT INTO data_template_rra VALUES (12,3);
INSERT INTO data_template_rra VALUES (12,4);
INSERT INTO data_template_rra VALUES (13,1);
INSERT INTO data_template_rra VALUES (13,2);
INSERT INTO data_template_rra VALUES (13,3);
INSERT INTO data_template_rra VALUES (13,4);
INSERT INTO data_template_rra VALUES (14,1);
INSERT INTO data_template_rra VALUES (14,2);
INSERT INTO data_template_rra VALUES (14,3);
INSERT INTO data_template_rra VALUES (14,4);
INSERT INTO data_template_rra VALUES (15,1);
INSERT INTO data_template_rra VALUES (15,2);
INSERT INTO data_template_rra VALUES (15,3);
INSERT INTO data_template_rra VALUES (15,4);
INSERT INTO data_template_rra VALUES (16,1);
INSERT INTO data_template_rra VALUES (16,2);
INSERT INTO data_template_rra VALUES (16,3);
INSERT INTO data_template_rra VALUES (16,4);
INSERT INTO data_template_rra VALUES (17,1);
INSERT INTO data_template_rra VALUES (17,2);
INSERT INTO data_template_rra VALUES (17,3);
INSERT INTO data_template_rra VALUES (17,4);
INSERT INTO data_template_rra VALUES (18,1);
INSERT INTO data_template_rra VALUES (18,2);
INSERT INTO data_template_rra VALUES (18,3);
INSERT INTO data_template_rra VALUES (18,4);

--
-- Table structure for table `data_template_suggested_value`
--

CREATE TABLE data_template_suggested_value (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  field_name varchar(30) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  sequence smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY data_template_id (data_template_id,field_name)
) TYPE=MyISAM;

--
-- Dumping data for table `data_template_suggested_value`
--

INSERT INTO data_template_suggested_value VALUES (1,'',1,'name','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|',1);
INSERT INTO data_template_suggested_value VALUES (2,'',2,'name','|host_description| - Disk Space - |query_hrStorageDescr|',1);
INSERT INTO data_template_suggested_value VALUES (3,'',3,'name','|host_description| - Logged in Users',1);
INSERT INTO data_template_suggested_value VALUES (4,'',4,'name','proc',1);
INSERT INTO data_template_suggested_value VALUES (5,'',5,'name','|host_description| - Errors - |query_ifName| (|query_ifIP|)',1);
INSERT INTO data_template_suggested_value VALUES (6,'',6,'name','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO data_template_suggested_value VALUES (7,'',7,'name','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO data_template_suggested_value VALUES (8,'',8,'name','|host_description| - Unicast Packets - |query_ifName| (|query_ifIP|)',1);
INSERT INTO data_template_suggested_value VALUES (9,'',9,'name','|host_description| - Memory',1);
INSERT INTO data_template_suggested_value VALUES (10,'',10,'name','|host_description| - CPU Usage',1);
INSERT INTO data_template_suggested_value VALUES (11,'',11,'name','|host_description| - Disk Space - |query_dskPath|',1);
INSERT INTO data_template_suggested_value VALUES (12,'',12,'name','|host_description| - Load Average',1);
INSERT INTO data_template_suggested_value VALUES (13,'',13,'name','|host_description| - Memory',1);
INSERT INTO data_template_suggested_value VALUES (14,'',14,'name','|host_description| - Disk Space - |query_dskMount|',1);
INSERT INTO data_template_suggested_value VALUES (15,'',15,'name','|host_description| - Logged in Users',1);
INSERT INTO data_template_suggested_value VALUES (16,'',16,'name','|host_description| - Processes',1);
INSERT INTO data_template_suggested_value VALUES (17,'',17,'name','|host_description| - Ping Host',1);
INSERT INTO data_template_suggested_value VALUES (18,'',18,'name','|host_description| - Non-Unicast Packets - |query_ifName| (|query_ifIP|)',1);
INSERT INTO data_template_suggested_value VALUES (19,'',6,'name','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO data_template_suggested_value VALUES (20,'',6,'name','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO data_template_suggested_value VALUES (21,'',6,'name','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO data_template_suggested_value VALUES (22,'',7,'name','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO data_template_suggested_value VALUES (23,'',7,'name','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO data_template_suggested_value VALUES (24,'',7,'name','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO data_template_suggested_value VALUES (25,'',5,'name','|host_description| - Errors - |query_ifName|',2);
INSERT INTO data_template_suggested_value VALUES (26,'',5,'name','|host_description| - Errors - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO data_template_suggested_value VALUES (27,'',5,'name','|host_description| - Errors - |query_ifDescr|',4);
INSERT INTO data_template_suggested_value VALUES (28,'',18,'name','|host_description| - Non-Unicast Packets - |query_ifName|',2);
INSERT INTO data_template_suggested_value VALUES (29,'',18,'name','|host_description| - Non-Unicast Packets - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO data_template_suggested_value VALUES (30,'',18,'name','|host_description| - Non-Unicast Packets - |query_ifDescr|',4);
INSERT INTO data_template_suggested_value VALUES (31,'',8,'name','|host_description| - Unicast Packets - |query_ifName|',2);
INSERT INTO data_template_suggested_value VALUES (32,'',8,'name','|host_description| - Unicast Packets - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO data_template_suggested_value VALUES (33,'',8,'name','|host_description| - Unicast Packets - |query_ifDescr|',4);

--
-- Table structure for table `graph`
--

CREATE TABLE graph (
  id mediumint(8) unsigned NOT NULL auto_increment,
  host_id mediumint(8) unsigned NOT NULL default '0',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  image_format tinyint(1) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  title_cache varchar(255) NOT NULL default '',
  height smallint(5) unsigned NOT NULL default '120',
  width smallint(5) unsigned NOT NULL default '500',
  x_grid varchar(50) NOT NULL default '',
  y_grid varchar(50) NOT NULL default '',
  y_grid_alt tinyint(1) unsigned NOT NULL default '0',
  no_minor tinyint(1) unsigned NOT NULL default '0',
  upper_limit bigint(20) NOT NULL default '0',
  lower_limit bigint(20) NOT NULL default '0',
  vertical_label varchar(200) NOT NULL default '',
  auto_scale tinyint(1) unsigned NOT NULL default '1',
  auto_scale_opts tinyint(1) unsigned NOT NULL default '2',
  auto_scale_log tinyint(1) unsigned NOT NULL default '0',
  auto_scale_rigid tinyint(1) unsigned NOT NULL default '0',
  auto_padding tinyint(1) unsigned NOT NULL default '1',
  base_value smallint(4) unsigned NOT NULL default '1000',
  export tinyint(1) unsigned NOT NULL default '1',
  unit_value varchar(20) NOT NULL default '',
  unit_length tinyint(3) unsigned NOT NULL default '9',
  unit_exponent_value char(3) NOT NULL default '',
  force_rules_legend tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (id),
  KEY host_id (host_id),
  KEY graph_template_id (graph_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph`
--


--
-- Table structure for table `graph_item`
--

CREATE TABLE graph_item (
  id mediumint(8) unsigned NOT NULL auto_increment,
  graph_id mediumint(8) unsigned NOT NULL default '0',
  graph_template_item_id mediumint(8) unsigned NOT NULL default '0',
  sequence smallint(5) unsigned NOT NULL default '0',
  data_source_item_id mediumint(8) unsigned NOT NULL default '0',
  color varchar(6) NOT NULL default '000000',
  graph_item_type tinyint(2) unsigned NOT NULL default '1',
  cdef varchar(255) NOT NULL default '',
  consolidation_function tinyint(1) unsigned NOT NULL default '0',
  gprint_format varchar(30) NOT NULL default '%8.2lf %s',
  legend_format varchar(255) NOT NULL default '',
  legend_value varchar(255) NOT NULL default '',
  hard_return tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY graph_id (graph_id),
  KEY graph_template_item_id (graph_template_item_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_item`
--


--
-- Table structure for table `graph_template`
--

CREATE TABLE graph_template (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  template_name varchar(150) NOT NULL default '',
  t_image_format tinyint(1) unsigned NOT NULL default '0',
  image_format tinyint(1) unsigned NOT NULL default '1',
  t_title tinyint(1) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  t_height tinyint(1) unsigned NOT NULL default '0',
  height smallint(5) unsigned NOT NULL default '120',
  t_width tinyint(1) unsigned NOT NULL default '0',
  width smallint(5) unsigned NOT NULL default '500',
  t_x_grid tinyint(1) unsigned NOT NULL default '0',
  x_grid varchar(50) NOT NULL default '',
  t_y_grid tinyint(1) unsigned NOT NULL default '0',
  y_grid varchar(50) NOT NULL default '',
  t_y_grid_alt tinyint(1) unsigned NOT NULL default '0',
  y_grid_alt tinyint(1) unsigned NOT NULL default '0',
  t_no_minor tinyint(1) unsigned NOT NULL default '0',
  no_minor tinyint(1) unsigned NOT NULL default '0',
  t_upper_limit tinyint(1) unsigned NOT NULL default '0',
  upper_limit bigint(20) NOT NULL default '0',
  t_lower_limit tinyint(1) unsigned NOT NULL default '0',
  lower_limit bigint(20) NOT NULL default '0',
  t_vertical_label tinyint(1) unsigned NOT NULL default '0',
  vertical_label varchar(200) NOT NULL default '',
  t_auto_scale tinyint(1) unsigned NOT NULL default '0',
  auto_scale tinyint(1) unsigned NOT NULL default '1',
  t_auto_scale_opts tinyint(1) unsigned NOT NULL default '0',
  auto_scale_opts tinyint(1) unsigned NOT NULL default '2',
  t_auto_scale_log tinyint(1) unsigned NOT NULL default '0',
  auto_scale_log tinyint(1) unsigned NOT NULL default '0',
  t_auto_scale_rigid tinyint(1) unsigned NOT NULL default '0',
  auto_scale_rigid tinyint(1) unsigned NOT NULL default '0',
  t_auto_padding tinyint(1) unsigned NOT NULL default '0',
  auto_padding tinyint(1) unsigned NOT NULL default '1',
  t_base_value tinyint(1) unsigned NOT NULL default '0',
  base_value smallint(4) unsigned NOT NULL default '1000',
  t_export tinyint(1) unsigned NOT NULL default '0',
  export tinyint(1) unsigned NOT NULL default '1',
  t_unit_value tinyint(1) unsigned NOT NULL default '0',
  unit_value varchar(20) NOT NULL default '',
  t_unit_length tinyint(1) unsigned NOT NULL default '0',
  unit_length tinyint(3) unsigned NOT NULL default '9',
  t_unit_exponent_value tinyint(1) unsigned NOT NULL default '0',
  unit_exponent_value char(3) NOT NULL default '',
  t_force_rules_legend tinyint(1) unsigned NOT NULL default '0',
  force_rules_legend tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_template`
--

INSERT INTO graph_template VALUES (1,'8396202bfa724498a57a7e22bd40f7f0','Host MIB - Disk Space',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes',0,1,0,2,0,0,0,0,0,1,0,1024,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (2,'a42973098983b215a9d62047f323d4ad','Host MIB - CPU Utilization',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'percent',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (3,'f2d7f5f843a2301252894ad369e05055','Host MIB - Logged in Users',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'users',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (4,'879bad31e96010709cce2f5db36bf24f','Interface - Errors/Discards',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'errors/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (5,'5e51f9d2a0c77ee5fae12336e94ff220','Interface - Non-Unicast Packets',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'packets/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (6,'66caefa056e2d071c7b2c591037b9d47','Interface - Traffic (bits/sec)',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bits/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (7,'567bc9646f3093393bb43fc2ba4d9eda','Interface - Traffic (bits/sec, 95th Percentile)',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bits/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (8,'7682fbd3f059258292135a6fcf75c14b','Interface - Traffic (bytes/sec)',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (9,'f916a697a25b03f9483d881af39a94d7','Interface - Traffic (bytes/sec, Total Bandwidth)',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (10,'3f6e3af5a40f2cd0132d3c0f28a78019','Interface - Traffic (bits/sec, 64-bit counters)',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bits/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (11,'47abb5e29927146f9e419cfa505f0fcf','Interface - Unicast Packets',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'packets/sec',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (12,'6779afe50b735f09c1a42e6dea49da82','Net-SNMP - Disk Space',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes',0,1,0,2,0,0,0,0,0,1,0,1024,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (13,'324ff1fdb007e4e70c6e0a7e15aaffb8','Net-SNMP - CPU Usage',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'percent',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (14,'79f0776ec68ee57b60d317d20723c7b5','Net-SNMP - Load Average',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'procs in the run queue',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (15,'8e6f66adfe14769dba1f8b3909676eb0','Net-SNMP - Memory Usage',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes',0,1,0,2,0,0,0,0,0,1,0,1024,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (16,'cdcbdaeb736c784ae5ac41eee7d72789','Local Unix - Disk Space',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'bytes',0,1,0,2,0,0,0,0,0,1,0,1024,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (17,'75cd439feb02e74e11313df7da84364d','Local Unix - Load Average',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'procs in the run queue',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (18,'17938161982ddbe819626b23780bd524','Local Unix - Logged in Users',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'users',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (19,'7e0319c8000de2a2fe52df6879dfcdaa','Local Unix - Processes',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'processes',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (20,'7b62b3c2d4e9b3c8f6a03ddeee8870cc','Local Unix - Ping Latency',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'milliseconds',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);
INSERT INTO graph_template VALUES (21,'9c25c63241e5605213921d4cc1774f1f','Host MIB - Processes',0,1,0,'',0,120,0,500,0,'',0,'',0,0,0,0,0,100,0,0,0,'processes',0,1,0,2,0,0,0,0,0,1,0,1000,0,1,0,'',0,9,0,'0',0,0);

--
-- Table structure for table `graph_template_item`
--

CREATE TABLE graph_template_item (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  sequence smallint(5) unsigned NOT NULL default '0',
  data_template_item_id mediumint(8) unsigned NOT NULL default '0',
  color varchar(6) NOT NULL default '000000',
  graph_item_type tinyint(2) unsigned NOT NULL default '1',
  cdef varchar(255) NOT NULL default '',
  consolidation_function tinyint(1) unsigned NOT NULL default '1',
  gprint_format varchar(30) NOT NULL default '%8.2lf %s',
  legend_format varchar(255) NOT NULL default '',
  legend_value varchar(255) NOT NULL default '',
  hard_return tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY graph_template_id (graph_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_template_item`
--

INSERT INTO graph_template_item VALUES (27,'22a679c7612ed0ab191f0c16628a5f71',2,3,1,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (26,'c7a4dc8c5b3100a70edc0b472eefdb39',2,2,1,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (25,'afe1319147ad6b0e683e04d77ab5f6b7',2,1,1,'FF0000',7,'',1,'%8.2lf %s','CPU Utilization','',0);
INSERT INTO graph_template_item VALUES (24,'26411857608cd3ebe5719bed5e393a88',1,8,3,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (23,'c6d242ee3b9216a822c6e4c9d550100a',1,7,3,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (22,'67229d563f1ce5adb65d4602d3160fe0',1,6,3,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (21,'8b4001a0a8f84ce1bac72ebd876b03ce',1,5,3,'F51D30',7,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Used','',0);
INSERT INTO graph_template_item VALUES (20,'3c12d8c9986ede8828dad1538cfdf6fb',1,4,2,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (19,'cd04d5be08f6be4111a89f4650f250a0',1,3,2,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (18,'11498685ae526049ea5116441672c29d',1,2,2,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (17,'3c06f9fc82a620492b85de63a1efe081',1,1,2,'002A97',7,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Total','',0);
INSERT INTO graph_template_item VALUES (28,'b618de35d145df278041bc621afa7043',2,4,1,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (29,'5524f2a7ba8a9943fca979ed45a72576',3,1,4,'4668E4',7,'',1,'%8.2lf %s','Users','',0);
INSERT INTO graph_template_item VALUES (30,'6861222f6adcaee2ddf9c9c36b031510',3,2,4,'',9,'',4,'%8.0lf','Current:','',0);
INSERT INTO graph_template_item VALUES (31,'f5a62e4ceafdb56afd5fc36b1c4722dd',3,3,4,'',9,'',1,'%8.0lf','Average:','',0);
INSERT INTO graph_template_item VALUES (32,'2a85cddb42a3e9f009954b5e694f078e',3,4,4,'',9,'',3,'%8.0lf','Maximum:','',1);
INSERT INTO graph_template_item VALUES (33,'3d06f5fcaec5b3ac28c44133c7b7a108',4,1,6,'FFAB00',4,'',1,'%8.2lf %s','Discards In','',0);
INSERT INTO graph_template_item VALUES (34,'285e2c59e81a65cbd65a28af685f08d3',4,2,6,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (35,'47b2992247825f86dc63c86e29b840b9',4,3,6,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (36,'b09da62d6740cb512ca07f6940b30b0a',4,4,6,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (37,'cacf0a5e59361c879f0384b0359d3f8c',4,5,8,'F51D30',4,'',1,'%8.2lf %s','Errors In','',0);
INSERT INTO graph_template_item VALUES (38,'f9f28b6fc2c789c0188cd8eedf457596',4,6,8,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (39,'6e09a1bc39e6d46295b28aa58ce10632',4,7,8,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (40,'808b879303c0b19f1ff4435f5a5348b2',4,8,8,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (41,'bef83df54c693e9ad23325d31f6e4b06',4,9,7,'C4FD3D',4,'',1,'%8.2lf %s','Discards Out','',0);
INSERT INTO graph_template_item VALUES (42,'8317c1c9b83ab584c533eddbf6cec1f0',4,10,7,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (43,'1cbf6cf5e65296c653855127c8afa443',4,11,7,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (44,'ffdd2f798faefdac4367d9a31d34c0dc',4,12,7,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (45,'fcccdedeae9bf79a0f398fec93ce20bd',4,13,9,'00694A',4,'',1,'%8.2lf %s','Errors Out','',0);
INSERT INTO graph_template_item VALUES (46,'d8fe622e6b07fe2f900affb432691ef8',4,14,9,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (47,'3e6c3a88555eb88a7322c94676a9e45d',4,15,9,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (48,'8aae14e4be2b8c47c638fe7aba61007c',4,16,9,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (49,'ec8268fc89390e1cc76a746f2eed549c',5,1,32,'FFF200',7,'',1,'%8.2lf %s','Non-Unicast Packets In','',0);
INSERT INTO graph_template_item VALUES (50,'559210b17c085ce1595892ac5d9274fa',5,2,32,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (51,'c60806ff1d87d2dbe4a77de83f0be614',5,3,32,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (52,'ad4cf2637e806254145da28f7dfbcec4',5,4,32,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (53,'d35898e3b377608f1dca8736a82b9cba',5,5,33,'00234B',7,'',1,'%8.2lf %s','Non-Unicast Packets Out','',0);
INSERT INTO graph_template_item VALUES (54,'db8d94da333bc6bf71694943cad8542e',5,6,33,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (55,'1276b75514327c02a7bee11b067813b9',5,7,33,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (56,'b2a4f18438413dbe3a279ee09d86448f',5,8,33,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (57,'a8b75b2017b7ad83987d180538bdae0d',6,1,10,'00CF00',7,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Inbound','',0);
INSERT INTO graph_template_item VALUES (58,'d7b8591551d5410a2c7c3a50aa83630c',6,2,10,'',9,'CURRENT_DATA_SOURCE,8,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (59,'e79448e19bc61513c2f8f4098dffb173',6,3,10,'',9,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (60,'2b1532138956709e16190c773d77f012',6,4,10,'',9,'CURRENT_DATA_SOURCE,8,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (61,'f1fc3bfcfbb71d3001709e7e09bdf47f',6,5,11,'002A97',4,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Outbound','',0);
INSERT INTO graph_template_item VALUES (62,'c55385a0aede07d259d78b7f6fe40d1d',6,6,11,'',9,'CURRENT_DATA_SOURCE,8,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (63,'46bf6a31b6865f9ae402030373a853fa',6,7,11,'',9,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (64,'91435e8f2751f4cae58f7b1108145b50',6,8,11,'',9,'CURRENT_DATA_SOURCE,8,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (65,'c5f9450cfdf4840119a7ad393611bce7',7,1,10,'00CF00',7,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Inbound','',0);
INSERT INTO graph_template_item VALUES (66,'fd2ac7642226f1e6d3d4abee34de498d',7,2,10,'',9,'CURRENT_DATA_SOURCE,8,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (67,'8214f603282c4bfb896d1d6f6e4d55c0',7,3,10,'',9,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (68,'7c5410ee8f2bce1cce51f5298df2df8c',7,4,10,'',9,'CURRENT_DATA_SOURCE,8,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (69,'3b9403db0d253d474d1bf83cc85a3a1c',7,5,11,'002A97',4,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Outbound','',0);
INSERT INTO graph_template_item VALUES (70,'de925205baced5f5e623a6d5b8ada203',7,6,11,'',9,'CURRENT_DATA_SOURCE,8,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (71,'ca6789532284740a230d20384e8a4203',7,7,11,'',9,'CURRENT_DATA_SOURCE,8,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (72,'52aba03641b00d8cc39e66f2949471f9',7,8,11,'',9,'CURRENT_DATA_SOURCE,8,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (73,'61a09a6c57b413da868f07f40bd74a4e',7,9,0,'',1,'',1,'%8.2lf %s','','',1);
INSERT INTO graph_template_item VALUES (74,'71e1799f099e1bc28f60885735430091',7,10,0,'FF0000',2,'',1,'%8.2lf %s','95th Percentile','|95:bits:0:total:2|',0);
INSERT INTO graph_template_item VALUES (75,'20af5b0ee540bdbfde920d3c8ecbd282',7,11,0,'',1,'',1,'%8.2lf %s','(|95:bits:6:total:2| mbit in+out)','',0);
INSERT INTO graph_template_item VALUES (76,'bada246616d79e0a17ce2e59ae43c2c3',8,1,10,'00CF00',7,'',1,'%8.2lf %s','Inbound','',0);
INSERT INTO graph_template_item VALUES (77,'3c4167bc3370a34667e4a17d3b6763d8',8,2,10,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (78,'92e851341cf9581fd8e9c4618b954b94',8,3,10,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (79,'31b6093432da0f29975edfa584dd9c26',8,4,10,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (80,'1fde95a377ef7126d352dce290bc0b56',8,5,11,'002A97',4,'',1,'%8.2lf %s','Outbound','',0);
INSERT INTO graph_template_item VALUES (81,'382f6cf5d6b8abaa4014d7ef4bb4a9f8',8,6,11,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (82,'52b93a11219f42778f4f1e2fbe1f6184',8,7,11,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (83,'ca5988428890dddf90ac5f313d85d742',8,8,11,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (84,'8c094c9e6fd40e5dbc43ffb590676ec8',9,1,10,'00CF00',7,'',1,'%8.2lf %s','Inbound','',0);
INSERT INTO graph_template_item VALUES (85,'054d9842689a95a131f390100f2d6b8e',9,2,10,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (86,'a393a8f1381873005862f8ed6a7ff820',9,3,10,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (87,'6ba87bd0078e884ac402f22021425af4',9,4,10,'',9,'',3,'%8.2lf %s','Maximum:','',0);
INSERT INTO graph_template_item VALUES (88,'c96bcb8b22e14f67629207f946300cbd',9,6,11,'002A97',4,'',1,'%8.2lf %s','Outbound','',0);
INSERT INTO graph_template_item VALUES (89,'cf48c23bcf0488e9f3841d5642a629c0',9,7,11,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (90,'a0ded75a7cef632081c3d5f60680c869',9,8,11,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (91,'fa0bd2a74a25d37df09b8c27af6022f3',9,9,11,'',9,'',3,'%8.2lf %s','Maximum:','',0);
INSERT INTO graph_template_item VALUES (92,'653ac630736cb9e1c1961c17916cdbff',9,5,10,'',1,'',1,'%8.2lf %s','Total In:  |sum:auto:current:2:auto|','',1);
INSERT INTO graph_template_item VALUES (93,'6854451ffdc4443875b0fd642e8af6fa',9,10,11,'',1,'',1,'%8.2lf %s','Total Out: |sum:auto:current:2:auto|','',1);
INSERT INTO graph_template_item VALUES (94,'e7f64b2072de05432e6cd8551268e716',10,1,12,'00CF00',7,'',1,'%8.2lf %s','Inbound','',0);
INSERT INTO graph_template_item VALUES (95,'a11ebc857da0f08d90f7ab5075a36235',10,2,12,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (96,'597427e52d5d040e0aa236acb383dbca',10,3,12,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (97,'b462abbb6da3120c13906f2a391af0a9',10,4,12,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (98,'5ec439f5d0d42b1dba74e6cdfe8c665c',10,5,13,'002A97',4,'',1,'%8.2lf %s','Outbound','',0);
INSERT INTO graph_template_item VALUES (99,'c91e2de3e11fd91c3ff5377af043a248',10,6,13,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (100,'03ad712cc979ae9b69a76771ba940acd',10,7,13,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (101,'fb586178b021712fdbc97f9e72a173d2',10,8,13,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (102,'3f11acab78742eeba6cf66a6093fedcb',11,1,14,'FFF200',7,'',1,'%8.2lf %s','Unicast Packets In','',0);
INSERT INTO graph_template_item VALUES (103,'bcc281e0ef44600e30a5d76ad97f6ce1',11,2,14,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (104,'b26a86750d8800137960ce1db4d916fd',11,3,14,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (105,'ee4aada525cf0d0a7e31429fdd6dae77',11,4,14,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (106,'3e82cb1d1a5b66a979f1415dbb3a6401',11,5,15,'00234B',4,'',1,'%8.2lf %s','Unicast Packets Out','',0);
INSERT INTO graph_template_item VALUES (107,'903ecc216c036b6f13932b2f40667a7c',11,6,15,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (108,'67c29eadbec03b6e4967b4a90120d209',11,7,15,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (109,'e3e6aa11584416eafaf1a14105453330',11,8,15,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (110,'1b4b82733aaff5686224e9839ca8800a',12,1,21,'F51D30',7,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Used','',0);
INSERT INTO graph_template_item VALUES (111,'3b659c3232d2acfc3708b16e81511c17',12,2,21,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (112,'9a478d5fe3ee05b8ee8b994f290d0931',12,3,21,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (113,'9012f6ae52f41dfd33647b6d7c6bc823',12,4,21,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (114,'b0a82b42adfe999086b5a9f783a97515',12,5,20,'002A97',8,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Available','',0);
INSERT INTO graph_template_item VALUES (115,'79f6ac4d82fb69da6791d06ab54f4add',12,6,20,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (116,'22cdac273fbf382685e61d778d74d8be',12,7,20,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (117,'8ff7b9986b88123199975ea376df83d8',12,8,20,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (118,'441c93d2ac4580f5cc2d68091f50b647',12,9,0,'000000',5,'ALL_DATA_SOURCES_NODUPS,1024,*',1,'%8.2lf %s','Total','',0);
INSERT INTO graph_template_item VALUES (119,'ea0d5d1a1098fd80b271698371f31b45',12,10,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (120,'79ee958fa77e2466f9a18ebc81994807',12,11,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (121,'928371cae892a4a3764092ebaecec92c',12,12,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (122,'5a2ed93095bcf302e0de9f658c80f66d',13,1,18,'FF0000',7,'',1,'%8.2lf %s','System','',0);
INSERT INTO graph_template_item VALUES (123,'061b2407d3564fd6790cae9e471b80bd',13,2,18,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (124,'33ef8052823b34ed3896988344b00ff1',13,3,18,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (125,'65eb60e1c96a46bf443b41067b2248cb',13,4,18,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (126,'0c26e471ea7694e0a49e340691ce5fa2',13,5,19,'0000FF',7,'',1,'%8.2lf %s','User','',0);
INSERT INTO graph_template_item VALUES (127,'47cfa1f56ae7964613a49afcf16b1301',13,6,19,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (128,'9b17be5953db885589e5ee9a44555ec3',13,7,19,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (129,'e03291a23d213351c1c90fcef5269d34',13,8,19,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (130,'d62f2cd2c54f5df09aa9e364005ba535',13,9,17,'00FF00',7,'',1,'%8.2lf %s','Nice','',0);
INSERT INTO graph_template_item VALUES (131,'8824fe448e9add49c433b62660ce4da4',13,10,17,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (132,'3a57ad41f779b628d539b10fc6a924fa',13,11,17,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (133,'bcfad3293e66c292aa049ab06964b1af',13,12,17,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (134,'5bba2fe767cfa99ab6e4d9ef0f0b30f1',13,13,0,'000000',7,'ALL_DATA_SOURCES_NODUPS',1,'%8.2lf %s','Total','',0);
INSERT INTO graph_template_item VALUES (135,'b99edd3efe227908227f0e9685ea1e8c',13,14,0,'',9,'ALL_DATA_SOURCES_NODUPS',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (136,'3d1050e5d86364cdbd96fb9387454443',13,15,0,'',9,'ALL_DATA_SOURCES_NODUPS',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (137,'28df9d5e1c4ceca8b0109c2a76d351b1',13,16,0,'',9,'ALL_DATA_SOURCES_NODUPS',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (138,'a6b81147b21170ee93fce6c49ccfcc73',14,1,22,'EACC00',7,'',1,'%8.2lf %s','1 Minute Average','',0);
INSERT INTO graph_template_item VALUES (139,'4f862b47c4f0b5db654a83bcf78c6ef3',14,2,22,'',9,'',4,'%8.2lf','Current:','',1);
INSERT INTO graph_template_item VALUES (140,'c6a164fd230a0bb9f8776ea6ebf249a5',14,3,23,'EA8F00',8,'',1,'%8.2lf %s','5 Minute Average','',0);
INSERT INTO graph_template_item VALUES (141,'e6dcfc4fd70dc7d29f008ee07144207c',14,4,23,'',9,'',4,'%8.2lf','Current:','',1);
INSERT INTO graph_template_item VALUES (142,'2e5aef05fdafdb3ab8025ac3d6d31f77',14,5,24,'FF0000',8,'',1,'%8.2lf %s','15 Minute Average','',0);
INSERT INTO graph_template_item VALUES (143,'dfe6b1c2ef1f176f5041bb1f0436450f',14,6,24,'',9,'',4,'%8.2lf','Current:','',1);
INSERT INTO graph_template_item VALUES (144,'3a99569ef01cfc3fbc958d8d5d2d2269',14,7,0,'',4,'ALL_DATA_SOURCES_NODUPS',1,'%8.2lf %s','Total','',0);
INSERT INTO graph_template_item VALUES (145,'00b382aa678fcf587d65d36cf18b9aa8',15,1,26,'8F005C',7,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Memory Free','',0);
INSERT INTO graph_template_item VALUES (146,'a96a9fc8002d1bff9ac176a803793b65',15,2,26,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (147,'18382f2c27b575b2f8826ea2638fca92',15,3,26,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (148,'7340c4c31e1d69e59c593a35da10a1e9',15,4,26,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (149,'3f3d99f26d60802aed2fd86ec4e5109d',15,5,25,'FF5700',8,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Memory Buffers','',0);
INSERT INTO graph_template_item VALUES (150,'7a8a228b694661c726afd531e1e10eff',15,6,25,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (151,'4b984bebbab0f80462449dd9aaadf8ef',15,7,25,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (152,'dde64d9ad8f52f2a4b11f4ab68ef0bc4',15,8,25,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (153,'6ea4b6e866b2a3b9b22cbbedaf185c03',16,1,28,'F51D30',7,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Used','',0);
INSERT INTO graph_template_item VALUES (154,'3caf27443b51fdf4c1156a381dcfa015',16,2,28,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (155,'225b9b23417ffa69ab71914bd917e747',16,3,28,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (156,'4b7d683e4ea5a576521cf4ac9249fa43',16,4,28,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (157,'fd82937a7fc9e4db085e4685ba00ead6',16,5,27,'002A97',8,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Available','',0);
INSERT INTO graph_template_item VALUES (158,'fb1774642c760d5c355981e7dc337e43',16,6,27,'',9,'CURRENT_DATA_SOURCE,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (159,'27bb4b40df054731823bc8cf72c95402',16,7,27,'',9,'CURRENT_DATA_SOURCE,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (160,'7f1200783852e38a782e83b6f8b5c64e',16,8,27,'',9,'CURRENT_DATA_SOURCE,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (161,'467d50ae06958e04fce0c9e18b3d2bcf',16,9,0,'000000',5,'ALL_DATA_SOURCES_NODUPS,1024,*',1,'%8.2lf %s','Total','',0);
INSERT INTO graph_template_item VALUES (162,'6cf4d5a2a78fb41b065ad2332a2c0c0e',16,10,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (163,'95d0f4cae4bea6d6a1945aa4446f870a',16,11,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (164,'c7e341fafdc5889c13045cf116238df4',16,12,0,'',9,'ALL_DATA_SOURCES_NODUPS,1024,*',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (165,'5807419de9e9e25d2fa726e914536475',18,1,29,'4668E4',7,'',1,'%8.0lf','Users','',0);
INSERT INTO graph_template_item VALUES (166,'3ef13f0d8d5cb7f7304986cd4bafc1c9',18,2,29,'',9,'',4,'%8.0lf','Current:','',0);
INSERT INTO graph_template_item VALUES (167,'b7ae826ff25bb53605a77c51e0d89238',18,3,29,'',9,'',1,'%8.0lf','Average:','',0);
INSERT INTO graph_template_item VALUES (168,'6216a8fca24bc2d8ec9065f3966f88ce',18,4,29,'',9,'',3,'%8.0lf','Maximum:','',1);
INSERT INTO graph_template_item VALUES (169,'bae5b207b03179b667d85af1528dbfb5',19,1,30,'F51D30',7,'',1,'%8.2lf %s','Processes','',0);
INSERT INTO graph_template_item VALUES (170,'6d42292a9ed84ddf58e876d6a1c652d2',19,2,30,'',9,'',4,'%8.0lf','Current:','',0);
INSERT INTO graph_template_item VALUES (171,'fa51f9d942cb075e9cf61d2130a9f639',19,3,30,'',9,'',1,'%8.0lf','Average:','',0);
INSERT INTO graph_template_item VALUES (172,'73eb2c21c1b72833ded9bd6e88b8b0fb',19,4,30,'',9,'',3,'%8.0lf','Maximum:','',1);
INSERT INTO graph_template_item VALUES (173,'8f500fcc378af80ff5c23b360ce1e026',20,1,31,'FFF200',7,'',1,'%8.2lf %s','Latency','',0);
INSERT INTO graph_template_item VALUES (174,'ee900d8a5b0484a73185b314d0096ffd',20,2,31,'',9,'',4,'%8.2lf %s','Current:','',0);
INSERT INTO graph_template_item VALUES (175,'2901e60dfe1d370c0de45c453c498243',20,3,31,'',9,'',1,'%8.2lf %s','Average:','',0);
INSERT INTO graph_template_item VALUES (176,'febb44e9b6aca62b103f26b22d9475ad',20,4,31,'',9,'',3,'%8.2lf %s','Maximum:','',1);
INSERT INTO graph_template_item VALUES (177,'217e54ac3bf68d818d360f092ed8743f',21,1,5,'F51D30',7,'',1,'%8.2lf %s','Processes','',0);
INSERT INTO graph_template_item VALUES (178,'3f227d5f1f5b2a5c88b74542521d271a',21,2,5,'',9,'',4,'%8.0lf','Current:','',0);
INSERT INTO graph_template_item VALUES (179,'8f56dd5dd1ec84c0b84159f9784ddc15',21,3,5,'',9,'',1,'%8.0lf','Average:','',0);
INSERT INTO graph_template_item VALUES (180,'9a5762424c623c00016f247192867ef1',21,4,5,'',9,'',3,'%8.0lf','Maximum:','',1);

--
-- Table structure for table `graph_template_item_input`
--

CREATE TABLE graph_template_item_input (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  field_name varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY graph_template_id (graph_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_template_item_input`
--

INSERT INTO graph_template_item_input VALUES (1,'c74e7ade00a03ff8fd7d0f746752943b',20,'Legend Color','color');

--
-- Table structure for table `graph_template_item_input_item`
--

CREATE TABLE graph_template_item_input_item (
  graph_template_item_input_id mediumint(8) NOT NULL default '0',
  graph_template_item_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (graph_template_item_input_id,graph_template_item_id),
  KEY graph_template_item_input_id (graph_template_item_input_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_template_item_input_item`
--

INSERT INTO graph_template_item_input_item VALUES (1,173);

--
-- Table structure for table `graph_template_suggested_value`
--

CREATE TABLE graph_template_suggested_value (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  field_name varchar(30) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  sequence smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY graph_template_id (graph_template_id,field_name)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_template_suggested_value`
--

INSERT INTO graph_template_suggested_value VALUES (1,'',1,'title','|host_description| - Disk Space - |query_hrStorageDescr|',1);
INSERT INTO graph_template_suggested_value VALUES (2,'',2,'title','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|',1);
INSERT INTO graph_template_suggested_value VALUES (3,'',3,'title','|host_description| - Logged in Users',1);
INSERT INTO graph_template_suggested_value VALUES (4,'',4,'title','|host_description| - Errors/Discards - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (5,'',5,'title','|host_description| - Non-Unicast Packets - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (6,'',6,'title','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (7,'',7,'title','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (8,'',8,'title','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (9,'',9,'title','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (10,'',10,'title','|host_description| - Traffic - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (11,'',11,'title','|host_description| - Unicast Packets - |query_ifName| (|query_ifIP|)',1);
INSERT INTO graph_template_suggested_value VALUES (12,'',12,'title',' |host_description| - Disk Space - |query_dskPath|',1);
INSERT INTO graph_template_suggested_value VALUES (13,'',13,'title','|host_description| - CPU Usage',1);
INSERT INTO graph_template_suggested_value VALUES (14,'',14,'title','|host_description| - Load Average',1);
INSERT INTO graph_template_suggested_value VALUES (15,'',15,'title','|host_description| - Memory Usage',1);
INSERT INTO graph_template_suggested_value VALUES (17,'',17,'title','|host_description| - Load Average',1);
INSERT INTO graph_template_suggested_value VALUES (18,'',18,'title','|host_description| - Logged in Users',1);
INSERT INTO graph_template_suggested_value VALUES (19,'',19,'title','|host_description| - Processes',1);
INSERT INTO graph_template_suggested_value VALUES (20,'',20,'title','|host_description| - Ping Latency',1);
INSERT INTO graph_template_suggested_value VALUES (21,'',21,'title','|host_description| - Processes',1);
INSERT INTO graph_template_suggested_value VALUES (22,'',16,'title','|host_description| - Disk Space - |query_dskMount|',2);
INSERT INTO graph_template_suggested_value VALUES (23,'',4,'title','|host_description| - Errors/Discards - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (24,'',4,'title','|host_description| - Errors/Discards - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (25,'',4,'title','|host_description| - Errors/Discards - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (26,'',5,'title','|host_description| - Non-Unicast Packets - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (27,'',5,'title','|host_description| - Non-Unicast Packets - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (28,'',5,'title','|host_description| - Non-Unicast Packets - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (29,'',6,'title','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (30,'',6,'title','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (31,'',6,'title','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (32,'',10,'title','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (33,'',10,'title','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (34,'',10,'title','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (35,'',7,'title','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (36,'',7,'title','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (37,'',7,'title','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (38,'',8,'title','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (39,'',8,'title','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (40,'',8,'title','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (41,'',9,'title','|host_description| - Traffic - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (42,'',9,'title','|host_description| - Traffic - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (43,'',9,'title','|host_description| - Traffic - |query_ifDescr|',4);
INSERT INTO graph_template_suggested_value VALUES (44,'',11,'title','|host_description| - Unicast Packets - |query_ifName|',2);
INSERT INTO graph_template_suggested_value VALUES (45,'',11,'title','|host_description| - Unicast Packets - |query_ifDescr| (|query_ifIP|)',3);
INSERT INTO graph_template_suggested_value VALUES (46,'',11,'title','|host_description| - Unicast Packets - |query_ifDescr|',4);

--
-- Table structure for table `graph_tree`
--

CREATE TABLE graph_tree (
  id smallint(5) unsigned NOT NULL auto_increment,
  sort_type tinyint(3) unsigned NOT NULL default '1',
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_tree`
--

INSERT INTO graph_tree VALUES (1,1,'Default Tree');

--
-- Table structure for table `graph_tree_items`
--

CREATE TABLE graph_tree_items (
  id smallint(5) unsigned NOT NULL auto_increment,
  graph_tree_id smallint(5) unsigned NOT NULL default '0',
  local_graph_id mediumint(8) unsigned NOT NULL default '0',
  rra_id smallint(8) unsigned NOT NULL default '0',
  title varchar(255) default NULL,
  host_id mediumint(8) unsigned NOT NULL default '0',
  order_key varchar(100) NOT NULL default '0',
  host_grouping_type tinyint(3) unsigned NOT NULL default '1',
  sort_children_type tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (id),
  KEY graph_tree_id (graph_tree_id)
) TYPE=MyISAM;

--
-- Dumping data for table `graph_tree_items`
--

INSERT INTO graph_tree_items VALUES (1,1,0,0,'',1,'001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',1,1);

--
-- Table structure for table `host`
--

CREATE TABLE host (
  id mediumint(8) unsigned NOT NULL auto_increment,
  poller_id smallint(5) unsigned NOT NULL default '0',
  host_template_id mediumint(8) unsigned NOT NULL default '0',
  description varchar(150) NOT NULL default '',
  hostname varchar(250) default NULL,
  snmp_community varchar(100) default NULL,
  snmp_version tinyint(1) unsigned NOT NULL default '1',
  snmpv3_auth_username varchar(50) default NULL,
  snmpv3_auth_password varchar(50) default NULL,
  snmpv3_auth_protocol varchar(5) default NULL,
  snmpv3_priv_passphrase varchar(200) default NULL,
  snmpv3_priv_protocol varchar(5) default NULL,
  snmp_port mediumint(5) unsigned NOT NULL default '161',
  snmp_timeout mediumint(8) unsigned NOT NULL default '500',
  availability_method smallint(5) unsigned NOT NULL default '1',
  ping_method smallint(5) unsigned default '0',
  disabled char(2) default NULL,
  status tinyint(2) NOT NULL default '0',
  status_event_count mediumint(8) unsigned NOT NULL default '0',
  status_fail_date datetime NOT NULL default '0000-00-00 00:00:00',
  status_rec_date datetime NOT NULL default '0000-00-00 00:00:00',
  status_last_error varchar(50) default '',
  min_time decimal(9,5) default '99999.99000',
  max_time decimal(9,5) default '0.00000',
  cur_time decimal(9,5) default '0.00000',
  avg_time decimal(9,5) default '0.00000',
  total_polls int(12) unsigned default '0',
  failed_polls int(12) unsigned default '0',
  availability decimal(7,5) NOT NULL default '100.00000',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `host`
--

INSERT INTO host VALUES (1,1,3,'Localhost','localhost','public',1,'','','MD5','','DES',161,500,2,2,'',3,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','',7.69000,7.77000,7.72000,7.72599,5,0,100.00000);

--
-- Table structure for table `host_graph`
--

CREATE TABLE host_graph (
  host_id mediumint(8) unsigned NOT NULL default '0',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (host_id,graph_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `host_graph`
--

INSERT INTO host_graph VALUES (1,17);
INSERT INTO host_graph VALUES (1,18);
INSERT INTO host_graph VALUES (1,19);

--
-- Table structure for table `host_snmp_cache`
--

CREATE TABLE host_snmp_cache (
  host_id mediumint(8) unsigned NOT NULL default '0',
  snmp_query_id mediumint(8) unsigned NOT NULL default '0',
  field_name varchar(50) NOT NULL default '',
  field_value varchar(255) default NULL,
  snmp_index varchar(60) NOT NULL default '',
  oid varchar(255) NOT NULL default '',
  PRIMARY KEY  (host_id,snmp_query_id,field_name,snmp_index),
  KEY host_id (host_id,field_name)
) TYPE=MyISAM;

--
-- Dumping data for table `host_snmp_cache`
--

INSERT INTO host_snmp_cache VALUES (1,6,'dskDevice','/dev/hda1','/dev/hda1','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskDevice','/dev/hda5','/dev/hda5','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskDevice','/dev/hda7','/dev/hda7','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskDevice','/dev/hdb1','/dev/hdb1','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskDevice','/dev/hdc1','/dev/hdc1','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskMount','/','/dev/hda1','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskMount','/home','/dev/hda5','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskMount','/var/fs/fs1','/dev/hda7','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskMount','/var/fs/fs2','/dev/hdb1','');
INSERT INTO host_snmp_cache VALUES (1,6,'dskMount','/var/fs/fs3','/dev/hdc1','');

--
-- Table structure for table `host_snmp_query`
--

CREATE TABLE host_snmp_query (
  host_id mediumint(8) unsigned NOT NULL default '0',
  snmp_query_id mediumint(8) unsigned NOT NULL default '0',
  sort_field varchar(50) NOT NULL default '',
  title_format varchar(50) NOT NULL default '',
  reindex_method tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (host_id,snmp_query_id),
  KEY host_id (host_id)
) TYPE=MyISAM;

--
-- Dumping data for table `host_snmp_query`
--

INSERT INTO host_snmp_query VALUES (1,6,'dskDevice','|query_dskDevice|',1);

--
-- Table structure for table `host_template`
--

CREATE TABLE host_template (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `host_template`
--

INSERT INTO host_template VALUES (1,'c15ea28c3db9d0c2fb4fa93f91cc9e50','Net-SNMP Enabled Host');
INSERT INTO host_template VALUES (2,'4051a939aad7eef6617666c0742a803a','Windows 2000/XP Host');
INSERT INTO host_template VALUES (3,'7a81401ab9621da368914acc536d3e3b','Local Linux Machine');

--
-- Table structure for table `host_template_graph`
--

CREATE TABLE host_template_graph (
  host_template_id mediumint(8) unsigned NOT NULL default '0',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (host_template_id,graph_template_id),
  KEY host_template_id (host_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `host_template_graph`
--

INSERT INTO host_template_graph VALUES (1,13);
INSERT INTO host_template_graph VALUES (1,14);
INSERT INTO host_template_graph VALUES (1,15);
INSERT INTO host_template_graph VALUES (2,3);
INSERT INTO host_template_graph VALUES (2,21);
INSERT INTO host_template_graph VALUES (3,17);
INSERT INTO host_template_graph VALUES (3,18);
INSERT INTO host_template_graph VALUES (3,19);

--
-- Table structure for table `host_template_snmp_query`
--

CREATE TABLE host_template_snmp_query (
  host_template_id mediumint(8) unsigned NOT NULL default '0',
  snmp_query_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (host_template_id,snmp_query_id),
  KEY host_template_id (host_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table `host_template_snmp_query`
--

INSERT INTO host_template_snmp_query VALUES (1,1);
INSERT INTO host_template_snmp_query VALUES (1,2);
INSERT INTO host_template_snmp_query VALUES (2,1);
INSERT INTO host_template_snmp_query VALUES (2,8);
INSERT INTO host_template_snmp_query VALUES (2,9);
INSERT INTO host_template_snmp_query VALUES (3,6);

--
-- Table structure for table `poller`
--

CREATE TABLE poller (
  id smallint(5) unsigned NOT NULL auto_increment,
  active varchar(5) default 'On',
  hostname varchar(250) NOT NULL default '',
  name varchar(150) default NULL,
  last_update datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `poller`
--


--
-- Table structure for table `poller_command`
--

CREATE TABLE poller_command (
  poller_id smallint(5) unsigned NOT NULL default '0',
  time datetime NOT NULL default '0000-00-00 00:00:00',
  action tinyint(3) unsigned NOT NULL default '0',
  command varchar(200) NOT NULL default '',
  PRIMARY KEY  (poller_id,action,command)
) TYPE=MyISAM;

--
-- Dumping data for table `poller_command`
--


--
-- Table structure for table `poller_item`
--

CREATE TABLE poller_item (
  local_data_id mediumint(8) unsigned NOT NULL default '0',
  poller_id smallint(5) unsigned NOT NULL default '0',
  host_id mediumint(8) NOT NULL default '0',
  action tinyint(2) unsigned NOT NULL default '1',
  hostname varchar(250) NOT NULL default '',
  snmp_community varchar(100) NOT NULL default '',
  snmp_version tinyint(1) unsigned NOT NULL default '0',
  snmpv3_auth_username varchar(50) default NULL,
  snmpv3_auth_password varchar(50) default NULL,
  snmpv3_auth_protocol varchar(5) default NULL,
  snmpv3_priv_passphrase varchar(200) default NULL,
  snmpv3_priv_protocol varchar(5) default NULL,
  snmp_port mediumint(5) unsigned NOT NULL default '161',
  snmp_timeout mediumint(8) unsigned NOT NULL default '0',
  availability_method smallint(5) unsigned NOT NULL default '1',
  ping_method smallint(5) unsigned default '0',
  rrd_name varchar(19) NOT NULL default '',
  rrd_path varchar(255) NOT NULL default '',
  rrd_num tinyint(2) unsigned NOT NULL default '0',
  arg1 varchar(255) default NULL,
  arg2 varchar(255) default NULL,
  arg3 varchar(255) default NULL,
  PRIMARY KEY  (local_data_id,rrd_name),
  KEY local_data_id (local_data_id),
  KEY host_id (host_id)
) TYPE=MyISAM;

--
-- Dumping data for table `poller_item`
--


--
-- Table structure for table `poller_output`
--

CREATE TABLE poller_output (
  local_data_id mediumint(8) unsigned NOT NULL default '0',
  rrd_name varchar(19) NOT NULL default '',
  time datetime NOT NULL default '0000-00-00 00:00:00',
  output text NOT NULL,
  PRIMARY KEY  (local_data_id,rrd_name,time)
) TYPE=MyISAM;

--
-- Dumping data for table `poller_output`
--


--
-- Table structure for table `poller_reindex`
--

CREATE TABLE poller_reindex (
  host_id mediumint(8) unsigned NOT NULL default '0',
  data_query_id mediumint(8) unsigned NOT NULL default '0',
  action tinyint(3) unsigned NOT NULL default '0',
  op char(1) NOT NULL default '',
  assert_value varchar(100) NOT NULL default '',
  arg1 varchar(100) NOT NULL default '',
  PRIMARY KEY  (host_id,data_query_id,arg1)
) TYPE=MyISAM;

--
-- Dumping data for table `poller_reindex`
--


--
-- Table structure for table `poller_time`
--

CREATE TABLE poller_time (
  id mediumint(8) unsigned NOT NULL auto_increment,
  poller_id smallint(5) unsigned NOT NULL default '0',
  start_time datetime NOT NULL default '0000-00-00 00:00:00',
  end_time datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `poller_time`
--


--
-- Table structure for table `preset_cdef`
--

CREATE TABLE preset_cdef (
  id mediumint(8) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  cdef_string varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY cdef_string (cdef_string)
) TYPE=MyISAM;

--
-- Dumping data for table `preset_cdef`
--

INSERT INTO preset_cdef VALUES (1,'Make Stack Negative','CURRENT_DATA_SOURCE,-1,*');
INSERT INTO preset_cdef VALUES (2,'Multiply by 1024','CURRENT_DATA_SOURCE,1024,*');
INSERT INTO preset_cdef VALUES (3,'Total All Data Sources','ALL_DATA_SOURCES_NODUPS');
INSERT INTO preset_cdef VALUES (4,'Total All Data Sources, Multiply by 1024','ALL_DATA_SOURCES_NODUPS,1024,*');
INSERT INTO preset_cdef VALUES (5,'Turn Bytes into Bits','CURRENT_DATA_SOURCE,8,*');

--
-- Table structure for table `preset_color`
--

CREATE TABLE preset_color (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hex char(6) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY hex (hex)
) TYPE=MyISAM;

--
-- Dumping data for table `preset_color`
--

INSERT INTO preset_color VALUES (1,'000000');
INSERT INTO preset_color VALUES (2,'FFFFFF');
INSERT INTO preset_color VALUES (4,'FAFD9E');
INSERT INTO preset_color VALUES (5,'C0C0C0');
INSERT INTO preset_color VALUES (6,'74C366');
INSERT INTO preset_color VALUES (7,'6DC8FE');
INSERT INTO preset_color VALUES (8,'EA8F00');
INSERT INTO preset_color VALUES (9,'FF0000');
INSERT INTO preset_color VALUES (10,'4444FF');
INSERT INTO preset_color VALUES (11,'FF00FF');
INSERT INTO preset_color VALUES (12,'00FF00');
INSERT INTO preset_color VALUES (13,'8D85F3');
INSERT INTO preset_color VALUES (14,'AD3B6E');
INSERT INTO preset_color VALUES (15,'EACC00');
INSERT INTO preset_color VALUES (16,'12B3B5');
INSERT INTO preset_color VALUES (17,'157419');
INSERT INTO preset_color VALUES (18,'C4FD3D');
INSERT INTO preset_color VALUES (19,'817C4E');
INSERT INTO preset_color VALUES (20,'002A97');
INSERT INTO preset_color VALUES (21,'0000FF');
INSERT INTO preset_color VALUES (22,'00CF00');
INSERT INTO preset_color VALUES (24,'F9FD5F');
INSERT INTO preset_color VALUES (25,'FFF200');
INSERT INTO preset_color VALUES (26,'CCBB00');
INSERT INTO preset_color VALUES (27,'837C04');
INSERT INTO preset_color VALUES (28,'EAAF00');
INSERT INTO preset_color VALUES (29,'FFD660');
INSERT INTO preset_color VALUES (30,'FFC73B');
INSERT INTO preset_color VALUES (31,'FFAB00');
INSERT INTO preset_color VALUES (33,'FF7D00');
INSERT INTO preset_color VALUES (34,'ED7600');
INSERT INTO preset_color VALUES (35,'FF5700');
INSERT INTO preset_color VALUES (36,'EE5019');
INSERT INTO preset_color VALUES (37,'B1441E');
INSERT INTO preset_color VALUES (38,'FFC3C0');
INSERT INTO preset_color VALUES (39,'FF897C');
INSERT INTO preset_color VALUES (40,'FF6044');
INSERT INTO preset_color VALUES (41,'FF4105');
INSERT INTO preset_color VALUES (42,'DA4725');
INSERT INTO preset_color VALUES (43,'942D0C');
INSERT INTO preset_color VALUES (44,'FF3932');
INSERT INTO preset_color VALUES (45,'862F2F');
INSERT INTO preset_color VALUES (46,'FF5576');
INSERT INTO preset_color VALUES (47,'562B29');
INSERT INTO preset_color VALUES (48,'F51D30');
INSERT INTO preset_color VALUES (49,'DE0056');
INSERT INTO preset_color VALUES (50,'ED5394');
INSERT INTO preset_color VALUES (51,'B90054');
INSERT INTO preset_color VALUES (52,'8F005C');
INSERT INTO preset_color VALUES (53,'F24AC8');
INSERT INTO preset_color VALUES (54,'E8CDEF');
INSERT INTO preset_color VALUES (55,'D8ACE0');
INSERT INTO preset_color VALUES (56,'A150AA');
INSERT INTO preset_color VALUES (57,'750F7D');
INSERT INTO preset_color VALUES (58,'8D00BA');
INSERT INTO preset_color VALUES (59,'623465');
INSERT INTO preset_color VALUES (60,'55009D');
INSERT INTO preset_color VALUES (61,'3D168B');
INSERT INTO preset_color VALUES (62,'311F4E');
INSERT INTO preset_color VALUES (63,'D2D8F9');
INSERT INTO preset_color VALUES (64,'9FA4EE');
INSERT INTO preset_color VALUES (65,'6557D0');
INSERT INTO preset_color VALUES (66,'4123A1');
INSERT INTO preset_color VALUES (67,'4668E4');
INSERT INTO preset_color VALUES (70,'001D61');
INSERT INTO preset_color VALUES (71,'00234B');
INSERT INTO preset_color VALUES (72,'002A8F');
INSERT INTO preset_color VALUES (73,'2175D9');
INSERT INTO preset_color VALUES (74,'7CB3F1');
INSERT INTO preset_color VALUES (75,'005199');
INSERT INTO preset_color VALUES (76,'004359');
INSERT INTO preset_color VALUES (77,'00A0C1');
INSERT INTO preset_color VALUES (78,'007283');
INSERT INTO preset_color VALUES (79,'00BED9');
INSERT INTO preset_color VALUES (80,'AFECED');
INSERT INTO preset_color VALUES (81,'55D6D3');
INSERT INTO preset_color VALUES (82,'00BBB4');
INSERT INTO preset_color VALUES (83,'009485');
INSERT INTO preset_color VALUES (84,'005D57');
INSERT INTO preset_color VALUES (85,'008A77');
INSERT INTO preset_color VALUES (86,'008A6D');
INSERT INTO preset_color VALUES (87,'00B99B');
INSERT INTO preset_color VALUES (88,'009F67');
INSERT INTO preset_color VALUES (89,'00694A');
INSERT INTO preset_color VALUES (90,'00A348');
INSERT INTO preset_color VALUES (91,'00BF47');
INSERT INTO preset_color VALUES (92,'96E78A');
INSERT INTO preset_color VALUES (93,'00BD27');
INSERT INTO preset_color VALUES (94,'35962B');
INSERT INTO preset_color VALUES (95,'7EE600');
INSERT INTO preset_color VALUES (96,'6EA100');
INSERT INTO preset_color VALUES (97,'CAF100');
INSERT INTO preset_color VALUES (98,'F5F800');
INSERT INTO preset_color VALUES (99,'CDCFC4');
INSERT INTO preset_color VALUES (100,'BCBEB3');
INSERT INTO preset_color VALUES (101,'AAABA1');
INSERT INTO preset_color VALUES (102,'8F9286');
INSERT INTO preset_color VALUES (103,'797C6E');
INSERT INTO preset_color VALUES (104,'2E3127');

--
-- Table structure for table `preset_gprint`
--

CREATE TABLE preset_gprint (
  id mediumint(8) unsigned NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  gprint_text varchar(255) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY gprint_text (gprint_text)
) TYPE=MyISAM;

--
-- Dumping data for table `preset_gprint`
--

INSERT INTO preset_gprint VALUES (2,'Normal','%8.2lf %s');
INSERT INTO preset_gprint VALUES (3,'Exact Numbers','%8.0lf');
INSERT INTO preset_gprint VALUES (4,'Load Average','%8.2lf');

--
-- Table structure for table `rra`
--

CREATE TABLE rra (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  x_files_factor double NOT NULL default '0.1',
  steps mediumint(8) default '1',
  rows int(12) NOT NULL default '600',
  timespan int(12) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `rra`
--

INSERT INTO rra VALUES (1,'c21df5178e5c955013591239eb0afd46','Daily (5 Minute Average)',0.5,1,600,86400);
INSERT INTO rra VALUES (2,'0d9c0af8b8acdc7807943937b3208e29','Weekly (30 Minute Average)',0.5,6,700,604800);
INSERT INTO rra VALUES (4,'e36f3adb9f152adfa5dc50fd2b23337e','Yearly (1 Day Average)',0.5,288,797,33053184);
INSERT INTO rra VALUES (3,'6fc2d038fb42950138b0ce3e9874cc60','Monthly (2 Hour Average)',0.5,24,775,2678400);

--
-- Table structure for table `rra_cf`
--

CREATE TABLE rra_cf (
  rra_id mediumint(8) unsigned NOT NULL default '0',
  consolidation_function_id smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (rra_id,consolidation_function_id),
  KEY rra_id (rra_id)
) TYPE=MyISAM;

--
-- Dumping data for table `rra_cf`
--

INSERT INTO rra_cf VALUES (1,1);
INSERT INTO rra_cf VALUES (1,3);
INSERT INTO rra_cf VALUES (2,1);
INSERT INTO rra_cf VALUES (2,3);
INSERT INTO rra_cf VALUES (3,1);
INSERT INTO rra_cf VALUES (3,3);
INSERT INTO rra_cf VALUES (4,1);
INSERT INTO rra_cf VALUES (4,3);

--
-- Table structure for table `settings`
--

CREATE TABLE settings (
  name varchar(50) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (name)
) TYPE=MyISAM;

--
-- Dumping data for table `settings`
--

INSERT INTO settings VALUES ('log_destination','1');
INSERT INTO settings VALUES ('log_snmp','');
INSERT INTO settings VALUES ('log_graph','');
INSERT INTO settings VALUES ('log_export','');
INSERT INTO settings VALUES ('log_verbosity','1');
INSERT INTO settings VALUES ('log_pstats','on');
INSERT INTO settings VALUES ('log_pwarn','on');
INSERT INTO settings VALUES ('log_perror','on');
INSERT INTO settings VALUES ('snmp_version','net-snmp');
INSERT INTO settings VALUES ('snmp_timeout','500');
INSERT INTO settings VALUES ('snmp_retries','3');
INSERT INTO settings VALUES ('remove_verification','on');
INSERT INTO settings VALUES ('path_snmpwalk','/usr/bin/snmpwalk');
INSERT INTO settings VALUES ('path_snmpget','/usr/bin/snmpget');
INSERT INTO settings VALUES ('path_rrdtool','/usr/bin/rrdtool');
INSERT INTO settings VALUES ('path_php_binary','/usr/bin/php');
INSERT INTO settings VALUES ('path_cactilog','/var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/log/cacti.log');
INSERT INTO settings VALUES ('path_cactid','');
INSERT INTO settings VALUES ('poller_enabled','on');
INSERT INTO settings VALUES ('poller_type','1');
INSERT INTO settings VALUES ('concurrent_processes','1');
INSERT INTO settings VALUES ('max_threads','1');
INSERT INTO settings VALUES ('availability_method','2');
INSERT INTO settings VALUES ('ping_method','2');
INSERT INTO settings VALUES ('ping_timeout','400');
INSERT INTO settings VALUES ('ping_retries','1');
INSERT INTO settings VALUES ('ping_failure_count','2');
INSERT INTO settings VALUES ('ping_recovery_count','3');
INSERT INTO settings VALUES ('export_type','disabled');
INSERT INTO settings VALUES ('path_html_export','');
INSERT INTO settings VALUES ('export_timing','disabled');
INSERT INTO settings VALUES ('path_html_export_skip','');
INSERT INTO settings VALUES ('export_hourly','');
INSERT INTO settings VALUES ('export_daily','');
INSERT INTO settings VALUES ('export_ftp_sanitize','');
INSERT INTO settings VALUES ('export_ftp_host','');
INSERT INTO settings VALUES ('export_ftp_port','');
INSERT INTO settings VALUES ('export_ftp_passive','');
INSERT INTO settings VALUES ('export_ftp_user','');
INSERT INTO settings VALUES ('export_ftp_password','');
INSERT INTO settings VALUES ('num_rows_graph','30');
INSERT INTO settings VALUES ('max_title_graph','80');
INSERT INTO settings VALUES ('max_data_query_field_length','15');
INSERT INTO settings VALUES ('max_data_query_javascript_rows','96');
INSERT INTO settings VALUES ('num_rows_data_source','30');
INSERT INTO settings VALUES ('max_title_data_source','45');
INSERT INTO settings VALUES ('num_rows_device','30');
INSERT INTO settings VALUES ('global_auth','on');
INSERT INTO settings VALUES ('ldap_enabled','');
INSERT INTO settings VALUES ('guest_user','guest');
INSERT INTO settings VALUES ('ldap_server','');
INSERT INTO settings VALUES ('ldap_dn','');
INSERT INTO settings VALUES ('ldap_template','');
INSERT INTO settings VALUES ('db_pconnections','on');
INSERT INTO settings VALUES ('db_retries','20');
INSERT INTO settings VALUES ('max_memory','32');
INSERT INTO settings VALUES ('max_execution_time','10');
INSERT INTO settings VALUES ('show_hidden','on');
INSERT INTO settings VALUES ('default_theme','classic');
INSERT INTO settings VALUES ('path_webroot','/var/cvsexport/cvs.querx.com/cacti/cacti_MAIN');
INSERT INTO settings VALUES ('date','2005-04-02 17:12:35');
INSERT INTO settings VALUES ('syslog_destination','1');
INSERT INTO settings VALUES ('syslog_size','1024k');
INSERT INTO settings VALUES ('syslog_control','1');
INSERT INTO settings VALUES ('syslog_maxdays','7');

--
-- Table structure for table `settings_graphs`
--

CREATE TABLE settings_graphs (
  user_id smallint(8) unsigned NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (user_id,name)
) TYPE=MyISAM;

--
-- Dumping data for table `settings_graphs`
--

INSERT INTO settings_graphs VALUES (1,'general','');
INSERT INTO settings_graphs VALUES (1,'tree','');
INSERT INTO settings_graphs VALUES (1,'default_rra_id','1');
INSERT INTO settings_graphs VALUES (1,'default_view_mode','1');
INSERT INTO settings_graphs VALUES (1,'default_timespan','7');
INSERT INTO settings_graphs VALUES (1,'timespan_sel','on');
INSERT INTO settings_graphs VALUES (1,'default_date_format','4');
INSERT INTO settings_graphs VALUES (1,'default_datechar','1');
INSERT INTO settings_graphs VALUES (1,'page_refresh','300');
INSERT INTO settings_graphs VALUES (1,'default_height','100');
INSERT INTO settings_graphs VALUES (1,'default_width','300');
INSERT INTO settings_graphs VALUES (1,'num_columns','2');
INSERT INTO settings_graphs VALUES (1,'thumbnail_section_preview','on');
INSERT INTO settings_graphs VALUES (1,'thumbnail_section_tree_1','on');
INSERT INTO settings_graphs VALUES (1,'thumbnail_section_tree_2','');
INSERT INTO settings_graphs VALUES (1,'default_tree_id','1');
INSERT INTO settings_graphs VALUES (1,'default_tree_view_mode','2');
INSERT INTO settings_graphs VALUES (1,'expand_hosts','on');
INSERT INTO settings_graphs VALUES (1,'preview_graphs_per_page','10');
INSERT INTO settings_graphs VALUES (1,'list_graphs_per_page','10');

--
-- Table structure for table `settings_tree`
--

CREATE TABLE settings_tree (
  user_id mediumint(8) unsigned NOT NULL default '0',
  graph_tree_item_id mediumint(8) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (user_id,graph_tree_item_id)
) TYPE=MyISAM;

--
-- Dumping data for table `settings_tree`
--

INSERT INTO settings_tree VALUES (3,87,0);
INSERT INTO settings_tree VALUES (3,63,0);
INSERT INTO settings_tree VALUES (3,90,0);
INSERT INTO settings_tree VALUES (3,85,0);
INSERT INTO settings_tree VALUES (3,109,0);
INSERT INTO settings_tree VALUES (3,92,1);
INSERT INTO settings_tree VALUES (3,7,0);
INSERT INTO settings_tree VALUES (3,84,0);
INSERT INTO settings_tree VALUES (3,88,0);
INSERT INTO settings_tree VALUES (3,94,0);
INSERT INTO settings_tree VALUES (3,91,0);
INSERT INTO settings_tree VALUES (3,89,0);
INSERT INTO settings_tree VALUES (3,93,0);
INSERT INTO settings_tree VALUES (3,83,0);
INSERT INTO settings_tree VALUES (3,82,0);

--
-- Table structure for table `snmp_query`
--

CREATE TABLE snmp_query (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  xml_path varchar(255) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  description varchar(255) default NULL,
  input_type tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `snmp_query`
--

INSERT INTO snmp_query VALUES (1,'d75e406fdeca4fcef45b8be3a9a63cbc','<path_cacti>/resource/snmp_queries/interface.xml','SNMP - Interface Statistics','Queries a host for a list of monitorable interfaces',1);
INSERT INTO snmp_query VALUES (2,'3c1b27d94ad208a0090f293deadde753','<path_cacti>/resource/snmp_queries/net-snmp_disk.xml','ucd/net -  Get Monitored Partitions','Retrieves a list of monitored partitions/disks from a net-snmp enabled host.',2);
INSERT INTO snmp_query VALUES (4,'ad06f46e22e991cb47c95c7233cfaee8','<path_cacti>/resource/snmp_queries/netware_disk.xml','Netware - Get Available Volumes','Retrieves a list of volumes from a Netware server.',2);
INSERT INTO snmp_query VALUES (6,'8ffa36c1864124b38bcda2ae9bd61f46','<path_cacti>/resource/script_queries/unix_disk.xml','Unix - Get Mounted Partitions','Queries a list of mounted partitions on a unix-based host with the',2);
INSERT INTO snmp_query VALUES (7,'30ec734bc0ae81a3d995be82c73f46c1','<path_cacti>/resource/snmp_queries/netware_cpu.xml','Netware - Get Processor Information','Gets information about running processors in a Netware server',2);
INSERT INTO snmp_query VALUES (8,'9343eab1f4d88b0e61ffc9d020f35414','<path_cacti>/resource/script_server/host_disk.xml','Host MIB - Get Mounted Partitions','Gets a list of partitions using SNMP',1);
INSERT INTO snmp_query VALUES (9,'0d1ab53fe37487a5d0b9e1d3ee8c1d0d','<path_cacti>/resource/script_server/host_cpu.xml','Host MIB - Get Processor Information','Gets usage for each processor in the system using the host MIB.',1);

--
-- Table structure for table `snmp_query_graph`
--

CREATE TABLE snmp_query_graph (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  snmp_query_id mediumint(8) unsigned NOT NULL default '0',
  name varchar(100) NOT NULL default '',
  graph_template_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `snmp_query_graph`
--

INSERT INTO snmp_query_graph VALUES (2,'a4b829746fb45e35e10474c36c69c0cf',1,'In/Out Errors/Discarded Packets',22);
INSERT INTO snmp_query_graph VALUES (3,'01e33224f8b15997d3d09d6b1bf83e18',1,'In/Out Non-Unicast Packets',24);
INSERT INTO snmp_query_graph VALUES (4,'1e6edee3115c42d644dbd014f0577066',1,'In/Out Unicast Packets',23);
INSERT INTO snmp_query_graph VALUES (15,'a0b3e7b63c2e66f9e1ea24a16ff245fc',6,'Available Disk Space',21);
INSERT INTO snmp_query_graph VALUES (6,'da43655bf1f641b07579256227806977',2,'Available/Used Disk Space',3);
INSERT INTO snmp_query_graph VALUES (9,'ab93b588c29731ab15db601ca0bc9dec',1,'In/Out Bytes (64-bit Counters)',25);
INSERT INTO snmp_query_graph VALUES (10,'5a5ce35edb4b195cbde99fd0161dfb4e',4,'Volume Information (free, freeable space)',19);
INSERT INTO snmp_query_graph VALUES (11,'c1c2cfd33eaf5064300e92e26e20bc56',4,'Directory Information (total/available entries)',20);
INSERT INTO snmp_query_graph VALUES (13,'ae34f5f385bed8c81a158bf3030f1089',1,'In/Out Bits',2);
INSERT INTO snmp_query_graph VALUES (14,'1e16a505ddefb40356221d7a50619d91',1,'In/Out Bits (64-bit Counters)',2);
INSERT INTO snmp_query_graph VALUES (16,'d1e0d9b8efd4af98d28ce2aad81a87e7',1,'In/Out Bytes',25);
INSERT INTO snmp_query_graph VALUES (17,'f6db4151aa07efa401a0af6c9b871844',7,'Get Processor Utilization',15);
INSERT INTO snmp_query_graph VALUES (18,'46c4ee688932cf6370459527eceb8ef3',8,'Available Disk Space',26);
INSERT INTO snmp_query_graph VALUES (19,'4a515b61441ea5f27ab7dee6c3cb7818',9,'Get Processor Utilization',27);
INSERT INTO snmp_query_graph VALUES (20,'ed7f68175d7bb83db8ead332fc945720',1,'In/Out Bits with 95th Percentile',31);
INSERT INTO snmp_query_graph VALUES (21,'f85386cd2fc94634ef167c7f1e5fbcd0',1,'In/Out Bits with Total Bandwidth',32);
INSERT INTO snmp_query_graph VALUES (22,'7d309bf200b6e3cdb59a33493c2e58e0',1,'In/Out Bytes with Total Bandwidth',33);

--
-- Table structure for table `snmp_query_graph_rrd`
--

CREATE TABLE snmp_query_graph_rrd (
  snmp_query_graph_id mediumint(8) unsigned NOT NULL default '0',
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  data_template_rrd_id mediumint(8) unsigned NOT NULL default '0',
  snmp_field_name varchar(50) NOT NULL default '0',
  PRIMARY KEY  (snmp_query_graph_id,data_template_id,data_template_rrd_id),
  KEY snmp_query_graph_id (snmp_query_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table `snmp_query_graph_rrd`
--

INSERT INTO snmp_query_graph_rrd VALUES (2,38,47,'ifInDiscards');
INSERT INTO snmp_query_graph_rrd VALUES (3,40,52,'ifOutNUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (3,40,53,'ifInNUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (4,39,48,'ifInUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (2,38,51,'ifOutErrors');
INSERT INTO snmp_query_graph_rrd VALUES (6,3,3,'dskAvail');
INSERT INTO snmp_query_graph_rrd VALUES (6,3,4,'dskUsed');
INSERT INTO snmp_query_graph_rrd VALUES (9,41,55,'ifHCOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (9,41,54,'ifHCInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (10,35,38,'nwVolSize');
INSERT INTO snmp_query_graph_rrd VALUES (10,35,40,'nwVolFreeable');
INSERT INTO snmp_query_graph_rrd VALUES (10,35,39,'nwVolFree');
INSERT INTO snmp_query_graph_rrd VALUES (11,36,42,'nwVolTotalDirEntries');
INSERT INTO snmp_query_graph_rrd VALUES (11,36,43,'nwVolUsedDirEntries');
INSERT INTO snmp_query_graph_rrd VALUES (2,38,50,'ifOutDiscards');
INSERT INTO snmp_query_graph_rrd VALUES (2,38,46,'ifInErrors');
INSERT INTO snmp_query_graph_rrd VALUES (13,41,54,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (14,41,54,'ifHCInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (14,41,55,'ifHCOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (13,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (4,39,49,'ifOutUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (15,37,44,'dskAvailable');
INSERT INTO snmp_query_graph_rrd VALUES (16,41,54,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (16,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (15,37,56,'dskUsed');
INSERT INTO snmp_query_graph_rrd VALUES (17,42,76,'nwhrProcessorUtilization');
INSERT INTO snmp_query_graph_rrd VALUES (18,43,78,'hrStorageUsed');
INSERT INTO snmp_query_graph_rrd VALUES (18,43,92,'hrStorageSize');
INSERT INTO snmp_query_graph_rrd VALUES (19,44,79,'hrProcessorLoad');
INSERT INTO snmp_query_graph_rrd VALUES (20,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (20,41,54,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (21,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (21,41,54,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (22,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (22,41,54,'ifInOctets');

--
-- Table structure for table `snmp_query_graph_rrd_sv`
--

CREATE TABLE snmp_query_graph_rrd_sv (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  snmp_query_graph_id mediumint(8) unsigned NOT NULL default '0',
  data_template_id mediumint(8) unsigned NOT NULL default '0',
  sequence mediumint(8) unsigned NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY snmp_query_graph_id (snmp_query_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table `snmp_query_graph_rrd_sv`
--

INSERT INTO snmp_query_graph_rrd_sv VALUES (10,'5d3a8b2f4a454e5b0a1494e00fe7d424',6,3,1,'name','|host_description| - Partition - |query_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (11,'d0b49af67a83c258ef1eab3780f7b3dc',7,7,1,'name','|host_description| - Wireless Noise Level - |query_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (12,'bf6b966dc369f3df2ea640a90845e94c',7,8,1,'name','|host_description| - Wireless Signal Level - |query_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (13,'5c3616603a7ac9d0c1cb9556b377a74f',8,10,1,'name','|host_description| - Wireless Re-Transmissions - |query_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (14,'080f0022f77044a512b083e3a8304e8b',8,9,1,'name','|host_description| - Wireless Transmissions - |query_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (88,'e1be83d708ed3c0b8715ccb6517a0365',9,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (86,'c582d3b37f19e4a703d9bf4908dc6548',9,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (82,'8d820d091ec1a9683cfa74a462f239ee',14,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (81,'2e8b27c63d98249096ad5bc320787f43',14,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (79,'2347e9f53564a54d43f3c00d4b60040d',16,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (30,'8132fa9c446e199732f0102733cb1714',11,36,1,'name','|host_description| - Directories - |query_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (29,'8fc9a94a5f6ef902a3de0fa7549e7476',10,35,1,'name','|host_description| - Volumes - |query_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (80,'27eb220995925e1a5e0e41b2582a2af6',16,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (85,'e85ddc56efa677b70448f9e931360b77',14,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (84,'37bb8c5b38bb7e89ec88ea7ccacf44d4',14,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (83,'62a47c18be10f273a5f5a13a76b76f54',14,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (32,'',12,37,1,'name','|host_description| - Partition - |query_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (78,'3a0f707d1c8fd0e061b70241541c7e2e',16,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (77,'8ef8ae2ef548892ab95bb6c9f0b3170e',16,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (76,'c7ee2110bf81639086d2da03d9d88286',16,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (75,'7e093c535fa3d810fa76fc3d8c80c94b',13,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (74,'084efd82bbddb69fb2ac9bd0b0f16ac6',13,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (72,'14aa2dead86bbad0f992f1514722c95e',13,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (73,'70390712158c3c5052a7d830fb456489',13,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (49,'6537b3209e0697fbec278e94e7317b52',2,38,1,'name','|host_description| - Errors - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (50,'6d3f612051016f48c951af8901720a1c',2,38,2,'name','|host_description| - Errors - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (51,'62bc981690576d0b2bd0041ec2e4aa6f',2,38,3,'name','|host_description| - Errors - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (52,'adb270d55ba521d205eac6a21478804a',2,38,4,'name','|host_description| - Errors - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (54,'77065435f3bbb2ff99bc3b43b81de8fe',3,40,1,'name','|host_description| - Non-Unicast Packets - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (55,'240d8893092619c97a54265e8d0b86a1',3,40,2,'name','|host_description| - Non-Unicast Packets - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (56,'4b200ecf445bdeb4c84975b74991df34',3,40,3,'name','|host_description| - Non-Unicast Packets - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (57,'d6da3887646078e4d01fe60a123c2179',3,40,4,'name','|host_description| - Non-Unicast Packets - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (59,'ce7769b97d80ca31d21f83dc18ba93c2',4,39,1,'name','|host_description| - Unicast Packets - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (60,'1ee1f9717f3f4771f7f823ca5a8b83dd',4,39,2,'name','|host_description| - Unicast Packets - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (61,'a7dbd54604533b592d4fae6e67587e32',4,39,3,'name','|host_description| - Unicast Packets - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (62,'b148fa7199edcf06cd71c89e5c5d7b63',4,39,4,'name','|host_description| - Unicast Packets - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (70,'87a659326af8c75158e5142874fd74b0',13,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (69,'cb09784ba05e401a3f1450126ed1e395',15,37,1,'name','|host_description| - Free Space - |query_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (89,'57a9ae1f197498ca8dcde90194f61cbc',9,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (90,'0110e120981c7ff15304e4a85cb42cbe',9,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (91,'ce0b9c92a15759d3ddbd7161d26a98b7',9,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (92,'42277993a025f1bfd85374d6b4deeb60',17,42,1,'name','|host_description| - CPU Utilization - CPU|query_nwhrProcessorNum|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (93,'a3f280327b1592a1a948e256380b544f',18,43,1,'name','|host_description| - Used Space - |query_hrStorageDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (94,'b5a724edc36c10891fa2a5c370d55b6f',19,44,1,'name','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (95,'7e87efd0075caba9908e2e6e569b25b0',20,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (96,'dd28d96a253ab86846aedb25d1cca712',20,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (97,'ce425fed4eb3174e4f1cde9713eeafa0',20,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (98,'d0d05156ddb2c65181588db4b64d3907',20,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (99,'3b018f789ff72cc5693ef79e3a794370',20,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (100,'b225229dbbb48c1766cf90298674ceed',21,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (101,'c79248ddbbd195907260887b021a055d',21,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (102,'12a6750d973b7f14783f205d86220082',21,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (103,'25b151fcfe093812cb5c208e36dd697e',21,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (104,'e9ab404a294e406c20fdd30df766161f',21,41,1,'rrd_maximum','|query_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (105,'119578a4f01ab47e820b0e894e5e5bb3',22,41,1,'name','|host_description| - Traffic - |query_ifIP| - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (106,'940e57d24b2623849c77b59ed05931b9',22,41,2,'name','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (107,'0f045eab01bbc4437b30da568ed5cb03',22,41,3,'name','|host_description| - Traffic - |query_ifIP|/|query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (108,'bd70bf71108d32f0bf91b24c85b87ff0',22,41,4,'name','|host_description| - Traffic - |query_ifDescr|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (109,'fdc4cb976c4b9053bfa2af791a21c5b5',22,41,1,'rrd_maximum','|query_ifSpeed|');

--
-- Table structure for table `snmp_query_graph_sv`
--

CREATE TABLE snmp_query_graph_sv (
  id mediumint(8) unsigned NOT NULL auto_increment,
  hash varchar(32) NOT NULL default '',
  snmp_query_graph_id mediumint(8) unsigned NOT NULL default '0',
  sequence mediumint(8) unsigned NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY snmp_query_graph_id (snmp_query_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table `snmp_query_graph_sv`
--

INSERT INTO snmp_query_graph_sv VALUES (7,'437918b8dcd66a64625c6cee481fff61',6,1,'title','|host_description| - Disk Space - |query_dskPath|');
INSERT INTO snmp_query_graph_sv VALUES (5,'2ddc61ff4bd9634f33aedce9524b7690',7,1,'title','|host_description| - Wireless Levels (|query_kbWirelessStationName|)');
INSERT INTO snmp_query_graph_sv VALUES (6,'c72e2da7af2cdbd6b44a5eb42c5b4758',8,1,'title','|host_description| - Wireless Transmissions (|query_kbWirelessStationName|)');
INSERT INTO snmp_query_graph_sv VALUES (33,'809c2e80552d56b65ca496c1c2fff398',16,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (32,'e403f5a733bf5c8401a110609683deb3',16,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (31,'7fb4a267065f960df81c15f9022cd3a4',16,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (11,'a412c5dfa484b599ec0f570979fdbc9e',10,1,'title','|host_description| - Volume Information - |query_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_sv VALUES (12,'48f4792dd49fefd7d640ec46b1d7bdb3',11,1,'title','|host_description| - Directory Information - |query_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_sv VALUES (14,'',12,1,'title','|host_description| - Disk Space - |query_dskDevice|');
INSERT INTO snmp_query_graph_sv VALUES (15,'49dca5592ac26ff149a4fbd18d690644',13,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (16,'bda15298139ad22bdc8a3b0952d4e3ab',13,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (17,'29e48483d0471fcd996bfb702a5960aa',13,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (18,'3f42d358965cb94ce4f708b59e04f82b',14,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (19,'45f44b2f811ea8a8ace1cbed8ef906f1',14,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (20,'69c14fbcc23aecb9920b3cdad7f89901',14,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (21,'299d3434851fc0d5c0e105429069709d',2,1,'title','|host_description| - Errors - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (22,'8c8860b17fd67a9a500b4cb8b5e19d4b',2,2,'title','|host_description| - Errors - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (23,'d96360ae5094e5732e7e7496ceceb636',2,3,'title','|host_description| - Errors - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (24,'750a290cadc3dc60bb682a5c5f47df16',3,1,'title','|host_description| - Non-Unicast Packets - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (25,'bde195eecc256c42ca9725f1f22c1dc0',3,2,'title','|host_description| - Non-Unicast Packets - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (26,'d9e97d22689e4ffddaca23b46f2aa306',3,3,'title','|host_description| - Non-Unicast Packets - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (27,'48ceaba62e0c2671a810a7f1adc5f751',4,1,'title','|host_description| - Unicast Packets - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (28,'d6258884bed44abe46d264198adc7c5d',4,2,'title','|host_description| - Unicast Packets - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (29,'6eb58d9835b2b86222306d6ced9961d9',4,3,'title','|host_description| - Unicast Packets - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (30,'f21b23df740bc4a2d691d2d7b1b18dba',15,1,'title','|host_description| - Disk Space - |query_dskDevice|');
INSERT INTO snmp_query_graph_sv VALUES (34,'0a5eb36e98c04ad6be8e1ef66caeed3c',9,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (35,'4c4386a96e6057b7bd0b78095209ddfa',9,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (36,'fd3a384768b0388fa64119fe2f0cc113',9,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (40,'d99f8db04fd07bcd2260d246916e03da',17,1,'title','|host_description| - CPU Utilization - CPU|query_nwhrProcessorNum|');
INSERT INTO snmp_query_graph_sv VALUES (38,'9852782792ede7c0805990e506ac9618',18,1,'title','|host_description| - Used Space - |query_hrStorageDescr|');
INSERT INTO snmp_query_graph_sv VALUES (39,'fa2f07ab54fce72eea684ba893dd9c95',19,1,'title','|host_description| - CPU Utilization - CPU|query_hrProcessorFrwID|');
INSERT INTO snmp_query_graph_sv VALUES (41,'f434ec853c479d424276f367e9806a75',20,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (42,'9b085245847444c5fb90ebbf4448e265',20,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (43,'5977863f28629bd8eb93a2a9cbc3e306',20,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (44,'37b6711af3930c56309cf8956d8bbf14',21,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (45,'cc435c5884a75421329a9b08207c1c90',21,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (46,'82edeea1ec249c9818773e3145836492',21,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (47,'87522150ee8a601b4d6a1f6b9e919c47',22,1,'title','|host_description| - Traffic - |query_ifName|');
INSERT INTO snmp_query_graph_sv VALUES (48,'993a87c04f550f1209d689d584aa8b45',22,2,'title','|host_description| - Traffic - |query_ifIP| (|query_ifDescr|)');
INSERT INTO snmp_query_graph_sv VALUES (49,'183bb486c92a566fddcb0585ede37865',22,3,'title','|host_description| - Traffic - |query_ifDescr|/|query_ifIndex|');

--
-- Table structure for table `syslog`
--

CREATE TABLE syslog (
  id bigint(20) unsigned NOT NULL auto_increment,
  logdate datetime NOT NULL default '0000-00-00 00:00:00',
  facility enum('POLLER','CMDPHP','CACTID','SCPTSVR','AUTH','WEBUI','EXPORT','SMTP','UNKNOWN') NOT NULL default 'POLLER',
  severity enum('EMERGENCY','ALERT','CRITICAL','ERROR','WARNING','NOTICE','INFO','DEBUG') NOT NULL default 'EMERGENCY',
  poller_id smallint(5) unsigned NOT NULL default '0',
  host_id mediumint(8) unsigned NOT NULL default '0',
  user_id mediumint(8) unsigned NOT NULL default '0',
  username varchar(50) NOT NULL default 'system',
  source varchar(50) NOT NULL default 'localhost',
  message varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY facility (facility),
  KEY severity (severity),
  KEY host_id (host_id),
  KEY poller_id (poller_id),
  KEY user_id (user_id),
  KEY username (username),
  KEY logdate (logdate)
) TYPE=MyISAM;

--
-- Dumping data for table `syslog`
--

INSERT INTO syslog VALUES (1,'2005-02-12 15:25:01','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','About to Spawn a Remote Process [CMD: /usr/bin/php, ARGS: -q /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/cmd.php -f=0 -l=3 -p=1]');
INSERT INTO syslog VALUES (2,'2005-02-12 15:25:04','CMDPHP','NOTICE',1,0,0,'SYSTEM','SYSTEM','PHP version 4.3 or above is recommended for performance considerations.');
INSERT INTO syslog VALUES (3,'2005-02-12 15:25:05','POLLER','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: Host responded to SNMP');
INSERT INTO syslog VALUES (4,'2005-02-12 15:25:05','CMDPHP','DEBUG',1,3,0,'SYSTEM','SYSTEM','RECACHE: Processing 1 items in the auto reindex cache for \'172.16.100.1\'.');
INSERT INTO syslog VALUES (5,'2005-02-12 15:25:05','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.1, output: 727733077');
INSERT INTO syslog VALUES (6,'2005-02-12 15:25:05','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.1, output: 726127814');
INSERT INTO syslog VALUES (7,'2005-02-12 15:25:05','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.2, output: 0');
INSERT INTO syslog VALUES (8,'2005-02-12 15:25:05','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.2, output: 0');
INSERT INTO syslog VALUES (9,'2005-02-12 15:25:06','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_1.rrd --template out:in 1108239903:726127814:727733077');
INSERT INTO syslog VALUES (10,'2005-02-12 15:25:06','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_2.rrd --template in:out 1108239903:0:0');
INSERT INTO syslog VALUES (11,'2005-02-12 15:25:05','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.3, output: 0');
INSERT INTO syslog VALUES (12,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.3, output: 0');
INSERT INTO syslog VALUES (13,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.5, output: 0');
INSERT INTO syslog VALUES (14,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.5, output: 0');
INSERT INTO syslog VALUES (15,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.6, output: 0');
INSERT INTO syslog VALUES (16,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.6, output: 0');
INSERT INTO syslog VALUES (17,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.7, output: 0');
INSERT INTO syslog VALUES (18,'2005-02-12 15:25:06','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.7, output: 0');
INSERT INTO syslog VALUES (19,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.8, output: 0');
INSERT INTO syslog VALUES (20,'2005-02-12 15:25:07','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_3.rrd --template out:in 1108239903:0:0');
INSERT INTO syslog VALUES (21,'2005-02-12 15:25:07','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_4.rrd --template in:out 1108239903:0:0');
INSERT INTO syslog VALUES (22,'2005-02-12 15:25:07','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_5.rrd --template in:out 1108239903:0:0');
INSERT INTO syslog VALUES (23,'2005-02-12 15:25:07','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_6.rrd --template in:out 1108239903:0:0');
INSERT INTO syslog VALUES (24,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.8, output: 0');
INSERT INTO syslog VALUES (25,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.9, output: 0');
INSERT INTO syslog VALUES (26,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.9, output: 0');
INSERT INTO syslog VALUES (27,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.26, output: 836234351');
INSERT INTO syslog VALUES (28,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.26, output: 0');
INSERT INTO syslog VALUES (29,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.27, output: 0');
INSERT INTO syslog VALUES (30,'2005-02-12 15:25:07','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.27, output: 0');
INSERT INTO syslog VALUES (31,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.28, output: 792964838');
INSERT INTO syslog VALUES (32,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.28, output: 3193288664');
INSERT INTO syslog VALUES (33,'2005-02-12 15:25:08','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_11.rrd --template in:out 1108239903:792964838:3193288664');
INSERT INTO syslog VALUES (34,'2005-02-12 15:25:08','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_10.rrd --template out:in 1108239903:0:0');
INSERT INTO syslog VALUES (35,'2005-02-12 15:25:08','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_9.rrd --template out:in 1108239903:0:836234351');
INSERT INTO syslog VALUES (36,'2005-02-12 15:25:08','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_8.rrd --template out:in 1108239903:0:0');
INSERT INTO syslog VALUES (37,'2005-02-12 15:25:08','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_7.rrd --template in:out 1108239903:0:0');
INSERT INTO syslog VALUES (38,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.29, output: 143198691');
INSERT INTO syslog VALUES (39,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.29, output: 3218709866');
INSERT INTO syslog VALUES (40,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.46, output: 2093983728');
INSERT INTO syslog VALUES (41,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.46, output: 3058854154');
INSERT INTO syslog VALUES (42,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.47, output: 1698361016');
INSERT INTO syslog VALUES (43,'2005-02-12 15:25:08','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.47, output: 2427449790');
INSERT INTO syslog VALUES (44,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.48, output: 734913882');
INSERT INTO syslog VALUES (45,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.48, output: 896101972');
INSERT INTO syslog VALUES (46,'2005-02-12 15:25:09','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_15.rrd --template out:in 1108239903:896101972:734913882');
INSERT INTO syslog VALUES (47,'2005-02-12 15:25:09','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_12.rrd --template out:in 1108239903:3218709866:143198691');
INSERT INTO syslog VALUES (48,'2005-02-12 15:25:09','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_13.rrd --template in:out 1108239903:2093983728:3058854154');
INSERT INTO syslog VALUES (49,'2005-02-12 15:25:09','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_14.rrd --template in:out 1108239903:1698361016:2427449790');
INSERT INTO syslog VALUES (50,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.49, output: 3092116073');
INSERT INTO syslog VALUES (51,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.49, output: 1566320789');
INSERT INTO syslog VALUES (52,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.50, output: 605115247');
INSERT INTO syslog VALUES (53,'2005-02-12 15:25:09','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.50, output: 2084731942');
INSERT INTO syslog VALUES (54,'2005-02-12 15:25:10','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.51, output: 3717280288');
INSERT INTO syslog VALUES (55,'2005-02-12 15:25:10','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.51, output: 2216866217');
INSERT INTO syslog VALUES (56,'2005-02-12 15:25:10','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.52, output: 353736295');
INSERT INTO syslog VALUES (57,'2005-02-12 15:25:10','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.52, output: 867175405');
INSERT INTO syslog VALUES (58,'2005-02-12 15:25:10','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_19.rrd --template out:in 1108239903:867175405:353736295');
INSERT INTO syslog VALUES (59,'2005-02-12 15:25:10','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_18.rrd --template out:in 1108239903:2216866217:3717280288');
INSERT INTO syslog VALUES (60,'2005-02-12 15:25:10','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.53, output: 3838524671');
INSERT INTO syslog VALUES (61,'2005-02-12 15:25:10','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_17.rrd --template out:in 1108239903:2084731942:605115247');
INSERT INTO syslog VALUES (62,'2005-02-12 15:25:10','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_16.rrd --template out:in 1108239903:1566320789:3092116073');
INSERT INTO syslog VALUES (63,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.53, output: 3014650852');
INSERT INTO syslog VALUES (64,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.54, output: 66');
INSERT INTO syslog VALUES (65,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.54, output: 66');
INSERT INTO syslog VALUES (66,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.55, output: 66');
INSERT INTO syslog VALUES (67,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.55, output: 66');
INSERT INTO syslog VALUES (68,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.56, output: 66');
INSERT INTO syslog VALUES (69,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.56, output: 66');
INSERT INTO syslog VALUES (70,'2005-02-12 15:25:11','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.57, output: 66');
INSERT INTO syslog VALUES (71,'2005-02-12 15:25:12','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_23.rrd --template out:in 1108239903:66:66');
INSERT INTO syslog VALUES (72,'2005-02-12 15:25:12','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_22.rrd --template out:in 1108239903:66:66');
INSERT INTO syslog VALUES (73,'2005-02-12 15:25:12','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_21.rrd --template out:in 1108239903:66:66');
INSERT INTO syslog VALUES (74,'2005-02-12 15:25:12','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_20.rrd --template out:in 1108239903:3014650852:3838524671');
INSERT INTO syslog VALUES (75,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.57, output: 66');
INSERT INTO syslog VALUES (76,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.58, output: 66');
INSERT INTO syslog VALUES (77,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.58, output: 66');
INSERT INTO syslog VALUES (78,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.59, output: 2907172857');
INSERT INTO syslog VALUES (79,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.59, output: 1152049028');
INSERT INTO syslog VALUES (80,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.60, output: 4162470308');
INSERT INTO syslog VALUES (81,'2005-02-12 15:25:12','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.60, output: 2801751639');
INSERT INTO syslog VALUES (82,'2005-02-12 15:25:13','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.61, output: 2764947604');
INSERT INTO syslog VALUES (83,'2005-02-12 15:25:13','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_24.rrd --template in:out 1108239903:66:66');
INSERT INTO syslog VALUES (84,'2005-02-12 15:25:13','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_27.rrd --template out:in 1108239903:2801751639:4162470308');
INSERT INTO syslog VALUES (85,'2005-02-12 15:25:13','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_26.rrd --template out:in 1108239903:1152049028:2907172857');
INSERT INTO syslog VALUES (86,'2005-02-12 15:25:13','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_25.rrd --template out:in 1108239903:66:66');
INSERT INTO syslog VALUES (87,'2005-02-12 15:25:13','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.61, output: 2090609844');
INSERT INTO syslog VALUES (88,'2005-02-12 15:25:13','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: in, oid: .1.3.6.1.2.1.2.2.1.10.62, output: 0');
INSERT INTO syslog VALUES (89,'2005-02-12 15:25:13','CMDPHP','INFO',1,3,0,'SYSTEM','SYSTEM','SNMP: v1: 172.16.100.1, dsname: out, oid: .1.3.6.1.2.1.2.2.1.16.62, output: 0');
INSERT INTO syslog VALUES (90,'2005-02-12 15:25:13','CMDPHP','NOTICE',1,0,0,'SYSTEM','SYSTEM','Run Time: 8.7611 s, Theads: N/A, Hosts: 3');
INSERT INTO syslog VALUES (91,'2005-02-12 15:25:14','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_28.rrd --template in:out 1108239903:2764947604:2090609844');
INSERT INTO syslog VALUES (92,'2005-02-12 15:25:14','POLLER','DEBUG',0,0,0,'SYSTEM','SYSTEM','CACTI2RRD: /usr/bin/rrdtool update /var/cvsexport/cvs.querx.com/cacti/cacti_MAIN/rra/6509_in_29.rrd --template out:in 1108239903:0:0');
INSERT INTO syslog VALUES (93,'2005-02-12 15:25:14','POLLER','NOTICE',1,0,0,'SYSTEM','SYSTEM','System Time: 12.9692 s, Total Pollers: 1, Method: cmd.php, Processes: 1, Threads: N/A, Hosts: 4, Hosts/Process: 4');

--
-- Table structure for table `user_auth`
--

CREATE TABLE user_auth (
  id mediumint(8) unsigned NOT NULL auto_increment,
  username varchar(50) NOT NULL default '0',
  password varchar(50) NOT NULL default '0',
  realm mediumint(8) NOT NULL default '0',
  full_name varchar(100) default '0',
  must_change_password char(2) default NULL,
  show_tree char(2) default 'on',
  show_list char(2) default 'on',
  show_preview char(2) NOT NULL default 'on',
  graph_settings char(2) default NULL,
  login_opts tinyint(1) NOT NULL default '1',
  policy_graphs tinyint(1) unsigned NOT NULL default '1',
  policy_trees tinyint(1) unsigned NOT NULL default '1',
  policy_hosts tinyint(1) unsigned NOT NULL default '1',
  policy_graph_templates tinyint(1) unsigned NOT NULL default '1',
  enabled tinyint(1) unsigned NOT NULL default '1',
  password_expire_length int(4) unsigned NOT NULL default '0',
  password_change_last datetime NOT NULL default '0000-00-00 00:00:00',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  current_theme varchar(25) NOT NULL default 'default',
  email_address_primary varchar(255) NOT NULL default '',
  email_address_secondary varchar(255) NOT NULL default '',
  last_login datetime NOT NULL default '0000-00-00 00:00:00',
  last_login_ip varchar(15) NOT NULL default '0.0.0.0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `user_auth`
--

INSERT INTO user_auth VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',0,'Administrator','','on','on','on','on',1,1,1,1,1,1,0,'0000-00-00 00:00:00','2004-12-29 20:59:45','default','','','2005-04-02 16:06:21','192.168.1.101');
INSERT INTO user_auth VALUES (3,'guest','43e9a4ab75570f5b',0,'Guest Account','on','on','on','on','on',3,1,1,1,1,1,0,'0000-00-00 00:00:00','2004-12-29 20:59:45','default','','','0000-00-00 00:00:00','0.0.0.0');

--
-- Table structure for table `user_auth_perms`
--

CREATE TABLE user_auth_perms (
  user_id mediumint(8) unsigned NOT NULL default '0',
  item_id mediumint(8) unsigned NOT NULL default '0',
  type tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id,item_id,type),
  KEY user_id (user_id,type)
) TYPE=MyISAM;

--
-- Dumping data for table `user_auth_perms`
--

INSERT INTO user_auth_perms VALUES (1,11,1);

--
-- Table structure for table `user_auth_realm`
--

CREATE TABLE user_auth_realm (
  realm_id mediumint(8) unsigned NOT NULL default '0',
  user_id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (realm_id,user_id),
  KEY user_id (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table `user_auth_realm`
--

INSERT INTO user_auth_realm VALUES (1,1);
INSERT INTO user_auth_realm VALUES (2,1);
INSERT INTO user_auth_realm VALUES (3,1);
INSERT INTO user_auth_realm VALUES (4,1);
INSERT INTO user_auth_realm VALUES (5,1);
INSERT INTO user_auth_realm VALUES (7,1);
INSERT INTO user_auth_realm VALUES (7,3);
INSERT INTO user_auth_realm VALUES (8,1);
INSERT INTO user_auth_realm VALUES (9,1);
INSERT INTO user_auth_realm VALUES (10,1);
INSERT INTO user_auth_realm VALUES (11,1);
INSERT INTO user_auth_realm VALUES (12,1);
INSERT INTO user_auth_realm VALUES (13,1);
INSERT INTO user_auth_realm VALUES (14,1);
INSERT INTO user_auth_realm VALUES (15,1);
INSERT INTO user_auth_realm VALUES (16,1);
INSERT INTO user_auth_realm VALUES (17,1);
INSERT INTO user_auth_realm VALUES (18,1);
INSERT INTO user_auth_realm VALUES (19,1);

--
-- Table structure for table `user_log`
--

CREATE TABLE user_log (
  username varchar(50) NOT NULL default '0',
  user_id mediumint(8) NOT NULL default '0',
  time datetime NOT NULL default '0000-00-00 00:00:00',
  result tinyint(1) NOT NULL default '0',
  ip varchar(15) NOT NULL default '',
  PRIMARY KEY  (username,user_id,time)
) TYPE=MyISAM;

--
-- Dumping data for table `user_log`
--


--
-- Table structure for table `version`
--

CREATE TABLE version (
  cacti char(20) default NULL
) TYPE=MyISAM;

--
-- Dumping data for table `version`
--

INSERT INTO version VALUES ('0.9-dev');

