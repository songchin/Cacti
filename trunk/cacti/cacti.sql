-- MySQL dump 8.22
--
-- Host: localhost    Database: dev_cacti_2
---------------------------------------------------------
-- Server version	3.23.54

--
-- Table structure for table 'cdef'
--

CREATE TABLE cdef (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'cdef'
--


INSERT INTO cdef VALUES (3,'Make Stack Negative');
INSERT INTO cdef VALUES (4,'Make Per 5 Minutes');
INSERT INTO cdef VALUES (12,'Total All Data Sources');
INSERT INTO cdef VALUES (2,'Turn Bytes into Bits');
INSERT INTO cdef VALUES (14,'Multiply by 1024');
INSERT INTO cdef VALUES (15,'Total All Data Sources, Multiply by 1024');

--
-- Table structure for table 'cdef_items'
--

CREATE TABLE cdef_items (
  id mediumint(8) NOT NULL auto_increment,
  cdef_id mediumint(8) NOT NULL default '0',
  sequence mediumint(8) NOT NULL default '0',
  type tinyint(2) NOT NULL default '0',
  value char(150) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY cdef_id (cdef_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'cdef_items'
--


INSERT INTO cdef_items VALUES (7,2,1,4,'CURRENT_DATA_SOURCE');
INSERT INTO cdef_items VALUES (9,2,2,6,'8');
INSERT INTO cdef_items VALUES (8,2,3,2,'3');
INSERT INTO cdef_items VALUES (10,3,1,4,'CURRENT_DATA_SOURCE');
INSERT INTO cdef_items VALUES (11,3,3,2,'3');
INSERT INTO cdef_items VALUES (12,3,2,6,'-1');
INSERT INTO cdef_items VALUES (13,4,1,4,'CURRENT_DATA_SOURCE');
INSERT INTO cdef_items VALUES (14,4,3,2,'3');
INSERT INTO cdef_items VALUES (15,4,2,6,'300');
INSERT INTO cdef_items VALUES (16,12,1,4,'ALL_DATA_SOURCES_NODUPS');
INSERT INTO cdef_items VALUES (18,14,1,4,'CURRENT_DATA_SOURCE');
INSERT INTO cdef_items VALUES (19,14,2,6,'1024');
INSERT INTO cdef_items VALUES (20,14,3,2,'3');
INSERT INTO cdef_items VALUES (21,15,1,4,'ALL_DATA_SOURCES_NODUPS');
INSERT INTO cdef_items VALUES (22,15,2,6,'1024');
INSERT INTO cdef_items VALUES (23,15,3,2,'3');

--
-- Table structure for table 'colors'
--

CREATE TABLE colors (
  id mediumint(8) NOT NULL auto_increment,
  hex varchar(6) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'colors'
--


INSERT INTO colors VALUES (1,'000000');
INSERT INTO colors VALUES (2,'FFFFFF');
INSERT INTO colors VALUES (4,'FAFD9E');
INSERT INTO colors VALUES (5,'C0C0C0');
INSERT INTO colors VALUES (6,'74C366');
INSERT INTO colors VALUES (7,'6DC8FE');
INSERT INTO colors VALUES (8,'EA8F00');
INSERT INTO colors VALUES (9,'FF0000');
INSERT INTO colors VALUES (10,'4444FF');
INSERT INTO colors VALUES (11,'FF00FF');
INSERT INTO colors VALUES (12,'00FF00');
INSERT INTO colors VALUES (13,'8D85F3');
INSERT INTO colors VALUES (14,'AD3B6E');
INSERT INTO colors VALUES (15,'EACC00');
INSERT INTO colors VALUES (16,'12B3B5');
INSERT INTO colors VALUES (17,'157419');
INSERT INTO colors VALUES (18,'C4FD3D');
INSERT INTO colors VALUES (19,'817C4E');
INSERT INTO colors VALUES (20,'002A97');
INSERT INTO colors VALUES (21,'0000FF');
INSERT INTO colors VALUES (22,'00CF00');
INSERT INTO colors VALUES (24,'F9FD5F');
INSERT INTO colors VALUES (25,'FFF200');
INSERT INTO colors VALUES (26,'CCBB00');
INSERT INTO colors VALUES (27,'837C04');
INSERT INTO colors VALUES (28,'EAAF00');
INSERT INTO colors VALUES (29,'FFD660');
INSERT INTO colors VALUES (30,'FFC73B');
INSERT INTO colors VALUES (31,'FFAB00');
INSERT INTO colors VALUES (33,'FF7D00');
INSERT INTO colors VALUES (34,'ED7600');
INSERT INTO colors VALUES (35,'FF5700');
INSERT INTO colors VALUES (36,'EE5019');
INSERT INTO colors VALUES (37,'B1441E');
INSERT INTO colors VALUES (38,'FFC3C0');
INSERT INTO colors VALUES (39,'FF897C');
INSERT INTO colors VALUES (40,'FF6044');
INSERT INTO colors VALUES (41,'FF4105');
INSERT INTO colors VALUES (42,'DA4725');
INSERT INTO colors VALUES (43,'942D0C');
INSERT INTO colors VALUES (44,'FF3932');
INSERT INTO colors VALUES (45,'862F2F');
INSERT INTO colors VALUES (46,'FF5576');
INSERT INTO colors VALUES (47,'562B29');
INSERT INTO colors VALUES (48,'F51D30');
INSERT INTO colors VALUES (49,'DE0056');
INSERT INTO colors VALUES (50,'ED5394');
INSERT INTO colors VALUES (51,'B90054');
INSERT INTO colors VALUES (52,'8F005C');
INSERT INTO colors VALUES (53,'F24AC8');
INSERT INTO colors VALUES (54,'E8CDEF');
INSERT INTO colors VALUES (55,'D8ACE0');
INSERT INTO colors VALUES (56,'A150AA');
INSERT INTO colors VALUES (57,'750F7D');
INSERT INTO colors VALUES (58,'8D00BA');
INSERT INTO colors VALUES (59,'623465');
INSERT INTO colors VALUES (60,'55009D');
INSERT INTO colors VALUES (61,'3D168B');
INSERT INTO colors VALUES (62,'311F4E');
INSERT INTO colors VALUES (63,'D2D8F9');
INSERT INTO colors VALUES (64,'9FA4EE');
INSERT INTO colors VALUES (65,'6557D0');
INSERT INTO colors VALUES (66,'4123A1');
INSERT INTO colors VALUES (67,'4668E4');
INSERT INTO colors VALUES (68,'0D006A');
INSERT INTO colors VALUES (69,'00004D');
INSERT INTO colors VALUES (70,'001D61');
INSERT INTO colors VALUES (71,'00234B');
INSERT INTO colors VALUES (72,'002A8F');
INSERT INTO colors VALUES (73,'2175D9');
INSERT INTO colors VALUES (74,'7CB3F1');
INSERT INTO colors VALUES (75,'005199');
INSERT INTO colors VALUES (76,'004359');
INSERT INTO colors VALUES (77,'00A0C1');
INSERT INTO colors VALUES (78,'007283');
INSERT INTO colors VALUES (79,'00BED9');
INSERT INTO colors VALUES (80,'AFECED');
INSERT INTO colors VALUES (81,'55D6D3');
INSERT INTO colors VALUES (82,'00BBB4');
INSERT INTO colors VALUES (83,'009485');
INSERT INTO colors VALUES (84,'005D57');
INSERT INTO colors VALUES (85,'008A77');
INSERT INTO colors VALUES (86,'008A6D');
INSERT INTO colors VALUES (87,'00B99B');
INSERT INTO colors VALUES (88,'009F67');
INSERT INTO colors VALUES (89,'00694A');
INSERT INTO colors VALUES (90,'00A348');
INSERT INTO colors VALUES (91,'00BF47');
INSERT INTO colors VALUES (92,'96E78A');
INSERT INTO colors VALUES (93,'00BD27');
INSERT INTO colors VALUES (94,'35962B');
INSERT INTO colors VALUES (95,'7EE600');
INSERT INTO colors VALUES (96,'6EA100');
INSERT INTO colors VALUES (97,'CAF100');
INSERT INTO colors VALUES (98,'F5F800');
INSERT INTO colors VALUES (99,'CDCFC4');
INSERT INTO colors VALUES (100,'BCBEB3');
INSERT INTO colors VALUES (101,'AAABA1');
INSERT INTO colors VALUES (102,'8F9286');
INSERT INTO colors VALUES (103,'797C6E');
INSERT INTO colors VALUES (104,'2E3127');

--
-- Table structure for table 'data_input'
--

CREATE TABLE data_input (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(200) NOT NULL default '',
  input_string varchar(255) default NULL,
  output_string varchar(255) default NULL,
  type_id tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_input'
--


INSERT INTO data_input VALUES (1,'Get SNMP Data','','',2);
INSERT INTO data_input VALUES (2,'Get SNMP Data (Indexed)','','',3);
INSERT INTO data_input VALUES (3,'Unix - Get Free Disk Space','perl <path_cacti>/scripts/diskfree.pl <partition>','',1);
INSERT INTO data_input VALUES (4,'Unix - Get Load Average','perl <path_cacti>/scripts/loadavg_multi.pl','',1);
INSERT INTO data_input VALUES (5,'Unix - Get Logged In Users','perl <path_cacti>/scripts/unix_users.pl <username>','',1);
INSERT INTO data_input VALUES (6,'Linux - Get Memory Usage','perl <path_cacti>/scripts/linux_memory.pl <grepstr>','',1);
INSERT INTO data_input VALUES (7,'Unix - Get System Processes','perl <path_cacti>/scripts/unix_processes.pl','',1);
INSERT INTO data_input VALUES (8,'Unix - Get TCP Connections','perl <path_cacti>/scripts/unix_tcp_connections.pl <grepstr>','',1);
INSERT INTO data_input VALUES (9,'Unix - Get Web Hits','perl <path_cacti>/scripts/webhits.pl <log_path>','',1);
INSERT INTO data_input VALUES (10,'Unix - Ping Host','perl <path_cacti>/scripts/ping.pl <ip>','',1);
INSERT INTO data_input VALUES (11,'Get Script Data (Indexed)','','',4);

--
-- Table structure for table 'data_input_data'
--

CREATE TABLE data_input_data (
  data_input_field_id mediumint(8) NOT NULL default '0',
  data_template_data_id mediumint(8) NOT NULL default '0',
  t_value char(2) default NULL,
  value text,
  PRIMARY KEY  (data_input_field_id,data_template_data_id),
  KEY data_input_field_id (data_input_field_id,data_template_data_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_input_data'
--


INSERT INTO data_input_data VALUES (14,1,'on','');
INSERT INTO data_input_data VALUES (13,1,'on','');
INSERT INTO data_input_data VALUES (12,1,'on','');
INSERT INTO data_input_data VALUES (14,2,'on','');
INSERT INTO data_input_data VALUES (13,2,'on','');
INSERT INTO data_input_data VALUES (12,2,'on','');
INSERT INTO data_input_data VALUES (14,3,'on','');
INSERT INTO data_input_data VALUES (13,3,'on','');
INSERT INTO data_input_data VALUES (12,3,'on','');
INSERT INTO data_input_data VALUES (6,4,'','.1.3.6.1.4.1.2021.11.52.0');
INSERT INTO data_input_data VALUES (6,5,'','.1.3.6.1.4.1.2021.11.50.0');
INSERT INTO data_input_data VALUES (6,6,'','.1.3.6.1.4.1.2021.11.51.0');
INSERT INTO data_input_data VALUES (14,7,'on','');
INSERT INTO data_input_data VALUES (13,7,'on','');
INSERT INTO data_input_data VALUES (12,7,'on','');
INSERT INTO data_input_data VALUES (14,8,'on','');
INSERT INTO data_input_data VALUES (13,8,'on','');
INSERT INTO data_input_data VALUES (12,8,'on','');
INSERT INTO data_input_data VALUES (14,9,'on','');
INSERT INTO data_input_data VALUES (13,9,'on','');
INSERT INTO data_input_data VALUES (12,9,'on','');
INSERT INTO data_input_data VALUES (14,10,'on','');
INSERT INTO data_input_data VALUES (13,10,'on','');
INSERT INTO data_input_data VALUES (12,10,'on','');
INSERT INTO data_input_data VALUES (22,12,'','Buffers:');
INSERT INTO data_input_data VALUES (22,13,'','MemFree:');
INSERT INTO data_input_data VALUES (22,14,'','^Cached:');
INSERT INTO data_input_data VALUES (22,15,'','SwapFree:');
INSERT INTO data_input_data VALUES (29,18,'on','');
INSERT INTO data_input_data VALUES (6,19,'','.1.3.6.1.4.1.23.2.28.3.1');
INSERT INTO data_input_data VALUES (6,20,'','.1.3.6.1.4.1.23.2.28.3.2');
INSERT INTO data_input_data VALUES (6,21,'','.1.3.6.1.2.1.25.3.3.1.2.1');
INSERT INTO data_input_data VALUES (6,27,'','.1.3.6.1.4.1.9.9.109.1.1.1.1.5.1');
INSERT INTO data_input_data VALUES (6,28,'','.1.3.6.1.4.1.9.9.109.1.1.1.1.3.1');
INSERT INTO data_input_data VALUES (6,29,'','.1.3.6.1.4.1.9.9.109.1.1.1.1.4.1');
INSERT INTO data_input_data VALUES (6,30,'','.1.3.6.1.4.1.2021.10.1.3.1');
INSERT INTO data_input_data VALUES (6,31,'','.1.3.6.1.4.1.2021.10.1.3.2');
INSERT INTO data_input_data VALUES (6,32,'','.1.3.6.1.4.1.2021.10.1.3.3');
INSERT INTO data_input_data VALUES (6,33,'','.1.3.6.1.4.1.2021.4.14.0');
INSERT INTO data_input_data VALUES (6,34,'','.1.3.6.1.4.1.2021.4.6.0');
INSERT INTO data_input_data VALUES (14,35,'on','');
INSERT INTO data_input_data VALUES (13,35,'on','');
INSERT INTO data_input_data VALUES (12,35,'on','');
INSERT INTO data_input_data VALUES (14,36,'on','');
INSERT INTO data_input_data VALUES (13,36,'on','');
INSERT INTO data_input_data VALUES (12,36,'on','');
INSERT INTO data_input_data VALUES (6,22,'','.1.3.6.1.4.1.23.2.28.2.1.0');
INSERT INTO data_input_data VALUES (6,23,'','.1.3.6.1.4.1.23.2.28.2.2.0');
INSERT INTO data_input_data VALUES (6,24,'','.1.3.6.1.4.1.23.2.28.2.5.0');
INSERT INTO data_input_data VALUES (6,25,'','.1.3.6.1.4.1.23.2.28.2.6.0');
INSERT INTO data_input_data VALUES (6,26,'','.1.3.6.1.4.1.23.2.28.2.7.0');
INSERT INTO data_input_data VALUES (33,37,'on','');
INSERT INTO data_input_data VALUES (32,37,'on','');
INSERT INTO data_input_data VALUES (31,37,'on','');
INSERT INTO data_input_data VALUES (14,38,'on','');
INSERT INTO data_input_data VALUES (13,38,'on','');
INSERT INTO data_input_data VALUES (12,38,'on','');
INSERT INTO data_input_data VALUES (14,39,'on','');
INSERT INTO data_input_data VALUES (13,39,'on','');
INSERT INTO data_input_data VALUES (12,39,'on','');
INSERT INTO data_input_data VALUES (14,40,'on','');
INSERT INTO data_input_data VALUES (13,40,'on','');
INSERT INTO data_input_data VALUES (12,40,'on','');
INSERT INTO data_input_data VALUES (12,41,'on','');
INSERT INTO data_input_data VALUES (13,41,'on','');
INSERT INTO data_input_data VALUES (14,41,'on','');

--
-- Table structure for table 'data_input_data_cache'
--

CREATE TABLE data_input_data_cache (
  local_data_id mediumint(8) NOT NULL default '0',
  data_input_id mediumint(8) NOT NULL default '0',
  action tinyint(2) NOT NULL default '1',
  command varchar(255) NOT NULL default '',
  management_ip varchar(15) NOT NULL default '',
  snmp_community varchar(100) NOT NULL default '',
  snmp_version tinyint(1) NOT NULL default '0',
  snmp_username varchar(50) NOT NULL default '',
  snmp_password varchar(50) NOT NULL default '',
  rrd_name varchar(19) NOT NULL default '',
  rrd_path varchar(255) NOT NULL default '',
  arg1 varchar(255) default NULL,
  arg2 varchar(255) default NULL,
  arg3 varchar(255) default NULL,
  PRIMARY KEY  (local_data_id,rrd_name),
  KEY local_data_id (local_data_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_input_data_cache'
--



--
-- Table structure for table 'data_input_data_fcache'
--

CREATE TABLE data_input_data_fcache (
  local_data_id mediumint(8) NOT NULL default '0',
  data_input_field_name varchar(100) NOT NULL default '',
  rrd_data_source_name varchar(19) NOT NULL default '',
  PRIMARY KEY  (local_data_id,rrd_data_source_name)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_input_data_fcache'
--



--
-- Table structure for table 'data_input_fields'
--

CREATE TABLE data_input_fields (
  id mediumint(8) NOT NULL auto_increment,
  data_input_id mediumint(8) NOT NULL default '0',
  name varchar(200) NOT NULL default '',
  data_name varchar(50) NOT NULL default '',
  input_output char(3) NOT NULL default '',
  update_rra char(2) default '0',
  sequence smallint(5) NOT NULL default '0',
  type_code varchar(40) default NULL,
  regexp_match varchar(200) default NULL,
  allow_nulls char(2) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id),
  KEY data_input_id (data_input_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_input_fields'
--


INSERT INTO data_input_fields VALUES (1,1,'SNMP IP Address','management_ip','in','',0,'management_ip','','');
INSERT INTO data_input_fields VALUES (2,1,'SNMP Community','snmp_community','in','',0,'snmp_community','','');
INSERT INTO data_input_fields VALUES (3,1,'SNMP Username','snmp_username','in','',0,'snmp_username','','on');
INSERT INTO data_input_fields VALUES (4,1,'SNMP Password','snmp_password','in','',0,'snmp_password','','on');
INSERT INTO data_input_fields VALUES (5,1,'SNMP Version (1, 2, or 3)','snmp_version','in','',0,'snmp_version','','on');
INSERT INTO data_input_fields VALUES (6,1,'OID','oid','in','',0,'snmp_oid','','');
INSERT INTO data_input_fields VALUES (7,2,'SNMP IP Address','management_ip','in','',0,'management_ip','','');
INSERT INTO data_input_fields VALUES (8,2,'SNMP Community','snmp_community','in','',0,'snmp_community','','');
INSERT INTO data_input_fields VALUES (9,2,'SNMP Username (v3)','snmp_username','in','',0,'snmp_username','','on');
INSERT INTO data_input_fields VALUES (10,2,'SNMP Password (v3)','snmp_password','in','',0,'snmp_password','','on');
INSERT INTO data_input_fields VALUES (11,2,'SNMP Version (1, 2, or 3)','snmp_version','in','',0,'snmp_version','','');
INSERT INTO data_input_fields VALUES (12,2,'Index Type','index_type','in','',0,'index_type','','');
INSERT INTO data_input_fields VALUES (13,2,'Index Value','index_value','in','',0,'index_value','','');
INSERT INTO data_input_fields VALUES (14,2,'Output Type ID','output_type','in','',0,'output_type','','');
INSERT INTO data_input_fields VALUES (15,3,'Disk Partition','partition','in','',1,'','','');
INSERT INTO data_input_fields VALUES (16,3,'Kilobytes Free','kilobytes','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (17,4,'1 Minute Average','1min','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (18,4,'5 Minute Average','5min','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (19,4,'10 Minute Average','10min','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (20,5,'Username (Optional)','username','in','',1,'','','on');
INSERT INTO data_input_fields VALUES (21,5,'Logged In Users','users','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (22,6,'Grep String','grepstr','in','',1,'','','');
INSERT INTO data_input_fields VALUES (23,6,'Result (in Kilobytes)','kilobytes','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (24,7,'Number of Processes','proc','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (25,8,'Grep String','grepstr','in','',1,'','','on');
INSERT INTO data_input_fields VALUES (26,8,'Connections','connections','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (27,9,'(Optional) Log Path','log_path','in','',1,'','','on');
INSERT INTO data_input_fields VALUES (28,9,'Web Hits','webhits','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (29,10,'IP Address','ip','in','',1,'management_ip','','');
INSERT INTO data_input_fields VALUES (30,10,'Milliseconds','out_ms','out','on',0,'','','');
INSERT INTO data_input_fields VALUES (31,11,'Index Type','index_type','in','',0,'index_type','','');
INSERT INTO data_input_fields VALUES (32,11,'Index Value','index_value','in','',0,'index_value','','');
INSERT INTO data_input_fields VALUES (33,11,'Output Type ID','output_type','in','',0,'output_type','','');
INSERT INTO data_input_fields VALUES (34,11,'Output Value','output','out','on',0,'','','');

--
-- Table structure for table 'data_local'
--

CREATE TABLE data_local (
  id mediumint(8) NOT NULL auto_increment,
  data_template_id mediumint(8) NOT NULL default '0',
  host_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_local'
--



--
-- Table structure for table 'data_template'
--

CREATE TABLE data_template (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(150) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_template'
--


INSERT INTO data_template VALUES (1,'Interface - Traffic - In');
INSERT INTO data_template VALUES (2,'Interface - Traffic - Out');
INSERT INTO data_template VALUES (3,'ucd/net - Hard Drive Space');
INSERT INTO data_template VALUES (4,'ucd/net - CPU Usage - System');
INSERT INTO data_template VALUES (5,'ucd/net - CPU Usage - User');
INSERT INTO data_template VALUES (6,'ucd/net - CPU Usage - Nice');
INSERT INTO data_template VALUES (7,'Karlnet - Noise Level');
INSERT INTO data_template VALUES (8,'Karlnet - Signal Level');
INSERT INTO data_template VALUES (9,'Karlnet - Wireless Transmits');
INSERT INTO data_template VALUES (10,'Karlnet - Wireless Re-Transmits');
INSERT INTO data_template VALUES (11,'Unix - Load Average');
INSERT INTO data_template VALUES (12,'Linux - Memory - Buffers');
INSERT INTO data_template VALUES (13,'Linux - Memory - Free');
INSERT INTO data_template VALUES (14,'Linux - Memory - Cache');
INSERT INTO data_template VALUES (15,'Linux - Memory - Free Swap');
INSERT INTO data_template VALUES (16,'Unix - Processes');
INSERT INTO data_template VALUES (17,'Unix - Logged in Users');
INSERT INTO data_template VALUES (18,'Unix - Ping Host');
INSERT INTO data_template VALUES (19,'Netware - Total Users');
INSERT INTO data_template VALUES (20,'Netware - Total Logins');
INSERT INTO data_template VALUES (21,'Netware - CPU Usage');
INSERT INTO data_template VALUES (22,'Netware - File System Reads');
INSERT INTO data_template VALUES (23,'Netware - File System Writes');
INSERT INTO data_template VALUES (24,'Netware - Cache Checks');
INSERT INTO data_template VALUES (25,'Netware - Cache Hits');
INSERT INTO data_template VALUES (26,'Netware - Open Files');
INSERT INTO data_template VALUES (27,'Cisco Router - 5 Minute CPU');
INSERT INTO data_template VALUES (28,'Cisco Router - 5 Second CPU');
INSERT INTO data_template VALUES (29,'Cisco Router - 1 Minute CPU');
INSERT INTO data_template VALUES (35,'Netware - Volumes');
INSERT INTO data_template VALUES (30,'ucd/net - Load Average - 1 Minute');
INSERT INTO data_template VALUES (31,'ucd/net - Load Average - 5 Minute');
INSERT INTO data_template VALUES (32,'ucd/net - Load Average - 15 Minute');
INSERT INTO data_template VALUES (33,'ucd/net - Memory - Buffers');
INSERT INTO data_template VALUES (34,'ucd/net - Memory - Free');
INSERT INTO data_template VALUES (36,'Netware - Directory Entries');
INSERT INTO data_template VALUES (37,'Unix - Hard Drive Space');
INSERT INTO data_template VALUES (38,'Interface - Errors/Discards');
INSERT INTO data_template VALUES (39,'Interface - Unicast Packets');
INSERT INTO data_template VALUES (40,'Interface - Non-Unicast Packets');
INSERT INTO data_template VALUES (41,'Interface - Traffic');

--
-- Table structure for table 'data_template_data'
--

CREATE TABLE data_template_data (
  id mediumint(8) NOT NULL auto_increment,
  local_data_template_data_id mediumint(8) NOT NULL default '0',
  local_data_id mediumint(8) NOT NULL default '0',
  data_template_id mediumint(8) NOT NULL default '0',
  data_input_id mediumint(8) NOT NULL default '0',
  t_name char(2) default NULL,
  name varchar(250) NOT NULL default '',
  data_source_path varchar(255) default NULL,
  t_active char(2) default NULL,
  active char(2) default NULL,
  t_rrd_step char(2) default NULL,
  rrd_step smallint(5) NOT NULL default '0',
  t_rra_id char(2) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id),
  KEY local_data_id (local_data_id),
  KEY data_template_id (data_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_template_data'
--


INSERT INTO data_template_data VALUES (1,0,0,1,2,'on','|host_description| - Traffic - In',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (2,0,0,2,2,'on','|host_description| - Traffic - Out',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (3,0,0,3,2,'on','|host_description| - Hard Drive Space',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (4,0,0,4,1,'on','|host_description| - CPU Usage - System',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (5,0,0,5,1,'on','|host_description| - CPU Usage - User',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (6,0,0,6,1,'on','|host_description| - CPU Usage - Nice',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (7,0,0,7,2,'on','|host_description| - Noise Level',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (8,0,0,8,2,'on','|host_description| - Signal Level',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (9,0,0,9,2,'on','|host_description| - Wireless Transmits',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (10,0,0,10,2,'on','|host_description| - Wireless Re-Transmits',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (11,0,0,11,4,'on','|host_description| - Load Average',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (12,0,0,12,6,'on','|host_description| - Memory - Buffers',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (13,0,0,13,6,'on','|host_description| - Memory - Free',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (14,0,0,14,6,'on','|host_description|- Memory - Cache',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (15,0,0,15,6,'on','Linux - Memory - Free Swap',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (16,0,0,16,7,'on','|host_description| - Processes',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (17,0,0,17,5,'on','|host_description| - Logged in Users',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (18,0,0,18,10,'on','|host_description| - Ping Host',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (19,0,0,19,1,'on','|host_description| - Total Users',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (20,0,0,20,1,'on','|host_description| - Total Logins',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (21,0,0,21,1,'on','|host_description| - CPU Usage',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (22,0,0,22,1,'on','|host_description| - File System Reads',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (23,0,0,23,1,'on','|host_description| - File System Writes',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (24,0,0,24,1,'on','|host_description| - Cache Checks',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (25,0,0,25,1,'on','|host_description| - Cache Hits',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (26,0,0,26,1,'on','|host_description| - Open Files',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (27,0,0,27,1,'on','|host_description| - 5 Minute CPU',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (28,0,0,28,1,'on','|host_description| - 5 Second CPU',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (29,0,0,29,1,'on','|host_description| - 1 Minute CPU',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (30,0,0,30,1,'on','|host_description| - Load Average - 1 Minute',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (31,0,0,31,1,'on','|host_description| - Load Average - 5 Minute',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (32,0,0,32,1,'on','|host_description| - Load Average - 15 Minute',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (33,0,0,33,1,'on','|host_description| - Memory - Buffers',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (34,0,0,34,1,'on','|host_description| - Memory - Free',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (35,0,0,35,2,'on','|host_description| - Volumes',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (36,0,0,36,2,'on','|host_description| - Directory Entries',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (37,0,0,37,11,'on','|host_description| - Hard Drive Space',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (38,0,0,38,2,'on','|host_description| - Errors/Discards',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (39,0,0,39,2,'on','|host_description| - Unicast Packets',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (40,0,0,40,2,'on','|host_description| - Non-Unicast Packets',NULL,'','on','',300,'');
INSERT INTO data_template_data VALUES (41,0,0,41,2,'on','|host_description| - Traffic',NULL,'','on','',300,'');

--
-- Table structure for table 'data_template_data_rra'
--

CREATE TABLE data_template_data_rra (
  data_template_data_id mediumint(8) NOT NULL default '0',
  rra_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (data_template_data_id,rra_id),
  KEY data_template_data_id (data_template_data_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_template_data_rra'
--


INSERT INTO data_template_data_rra VALUES (1,1);
INSERT INTO data_template_data_rra VALUES (1,2);
INSERT INTO data_template_data_rra VALUES (1,3);
INSERT INTO data_template_data_rra VALUES (1,4);
INSERT INTO data_template_data_rra VALUES (2,1);
INSERT INTO data_template_data_rra VALUES (2,2);
INSERT INTO data_template_data_rra VALUES (2,3);
INSERT INTO data_template_data_rra VALUES (2,4);
INSERT INTO data_template_data_rra VALUES (3,1);
INSERT INTO data_template_data_rra VALUES (3,2);
INSERT INTO data_template_data_rra VALUES (3,3);
INSERT INTO data_template_data_rra VALUES (3,4);
INSERT INTO data_template_data_rra VALUES (4,1);
INSERT INTO data_template_data_rra VALUES (4,2);
INSERT INTO data_template_data_rra VALUES (4,3);
INSERT INTO data_template_data_rra VALUES (4,4);
INSERT INTO data_template_data_rra VALUES (5,1);
INSERT INTO data_template_data_rra VALUES (5,2);
INSERT INTO data_template_data_rra VALUES (5,3);
INSERT INTO data_template_data_rra VALUES (5,4);
INSERT INTO data_template_data_rra VALUES (6,1);
INSERT INTO data_template_data_rra VALUES (6,2);
INSERT INTO data_template_data_rra VALUES (6,3);
INSERT INTO data_template_data_rra VALUES (6,4);
INSERT INTO data_template_data_rra VALUES (7,1);
INSERT INTO data_template_data_rra VALUES (7,2);
INSERT INTO data_template_data_rra VALUES (7,3);
INSERT INTO data_template_data_rra VALUES (7,4);
INSERT INTO data_template_data_rra VALUES (8,1);
INSERT INTO data_template_data_rra VALUES (8,2);
INSERT INTO data_template_data_rra VALUES (8,3);
INSERT INTO data_template_data_rra VALUES (8,4);
INSERT INTO data_template_data_rra VALUES (9,1);
INSERT INTO data_template_data_rra VALUES (9,2);
INSERT INTO data_template_data_rra VALUES (9,3);
INSERT INTO data_template_data_rra VALUES (9,4);
INSERT INTO data_template_data_rra VALUES (10,1);
INSERT INTO data_template_data_rra VALUES (10,2);
INSERT INTO data_template_data_rra VALUES (10,3);
INSERT INTO data_template_data_rra VALUES (10,4);
INSERT INTO data_template_data_rra VALUES (11,1);
INSERT INTO data_template_data_rra VALUES (11,2);
INSERT INTO data_template_data_rra VALUES (11,3);
INSERT INTO data_template_data_rra VALUES (11,4);
INSERT INTO data_template_data_rra VALUES (12,1);
INSERT INTO data_template_data_rra VALUES (12,2);
INSERT INTO data_template_data_rra VALUES (12,3);
INSERT INTO data_template_data_rra VALUES (12,4);
INSERT INTO data_template_data_rra VALUES (13,1);
INSERT INTO data_template_data_rra VALUES (13,2);
INSERT INTO data_template_data_rra VALUES (13,3);
INSERT INTO data_template_data_rra VALUES (13,4);
INSERT INTO data_template_data_rra VALUES (14,1);
INSERT INTO data_template_data_rra VALUES (14,2);
INSERT INTO data_template_data_rra VALUES (14,3);
INSERT INTO data_template_data_rra VALUES (14,4);
INSERT INTO data_template_data_rra VALUES (15,1);
INSERT INTO data_template_data_rra VALUES (15,2);
INSERT INTO data_template_data_rra VALUES (15,3);
INSERT INTO data_template_data_rra VALUES (15,4);
INSERT INTO data_template_data_rra VALUES (16,1);
INSERT INTO data_template_data_rra VALUES (16,2);
INSERT INTO data_template_data_rra VALUES (16,3);
INSERT INTO data_template_data_rra VALUES (16,4);
INSERT INTO data_template_data_rra VALUES (17,1);
INSERT INTO data_template_data_rra VALUES (17,2);
INSERT INTO data_template_data_rra VALUES (17,3);
INSERT INTO data_template_data_rra VALUES (17,4);
INSERT INTO data_template_data_rra VALUES (18,1);
INSERT INTO data_template_data_rra VALUES (18,2);
INSERT INTO data_template_data_rra VALUES (18,3);
INSERT INTO data_template_data_rra VALUES (18,4);
INSERT INTO data_template_data_rra VALUES (19,1);
INSERT INTO data_template_data_rra VALUES (19,2);
INSERT INTO data_template_data_rra VALUES (19,3);
INSERT INTO data_template_data_rra VALUES (19,4);
INSERT INTO data_template_data_rra VALUES (20,1);
INSERT INTO data_template_data_rra VALUES (20,2);
INSERT INTO data_template_data_rra VALUES (20,3);
INSERT INTO data_template_data_rra VALUES (20,4);
INSERT INTO data_template_data_rra VALUES (21,1);
INSERT INTO data_template_data_rra VALUES (21,2);
INSERT INTO data_template_data_rra VALUES (21,3);
INSERT INTO data_template_data_rra VALUES (21,4);
INSERT INTO data_template_data_rra VALUES (22,1);
INSERT INTO data_template_data_rra VALUES (22,2);
INSERT INTO data_template_data_rra VALUES (22,3);
INSERT INTO data_template_data_rra VALUES (22,4);
INSERT INTO data_template_data_rra VALUES (23,1);
INSERT INTO data_template_data_rra VALUES (23,2);
INSERT INTO data_template_data_rra VALUES (23,3);
INSERT INTO data_template_data_rra VALUES (23,4);
INSERT INTO data_template_data_rra VALUES (24,1);
INSERT INTO data_template_data_rra VALUES (24,2);
INSERT INTO data_template_data_rra VALUES (24,3);
INSERT INTO data_template_data_rra VALUES (24,4);
INSERT INTO data_template_data_rra VALUES (25,1);
INSERT INTO data_template_data_rra VALUES (25,2);
INSERT INTO data_template_data_rra VALUES (25,3);
INSERT INTO data_template_data_rra VALUES (25,4);
INSERT INTO data_template_data_rra VALUES (26,1);
INSERT INTO data_template_data_rra VALUES (26,2);
INSERT INTO data_template_data_rra VALUES (26,3);
INSERT INTO data_template_data_rra VALUES (26,4);
INSERT INTO data_template_data_rra VALUES (27,1);
INSERT INTO data_template_data_rra VALUES (27,2);
INSERT INTO data_template_data_rra VALUES (27,3);
INSERT INTO data_template_data_rra VALUES (27,4);
INSERT INTO data_template_data_rra VALUES (28,1);
INSERT INTO data_template_data_rra VALUES (28,2);
INSERT INTO data_template_data_rra VALUES (28,3);
INSERT INTO data_template_data_rra VALUES (28,4);
INSERT INTO data_template_data_rra VALUES (29,1);
INSERT INTO data_template_data_rra VALUES (29,2);
INSERT INTO data_template_data_rra VALUES (29,3);
INSERT INTO data_template_data_rra VALUES (29,4);
INSERT INTO data_template_data_rra VALUES (30,1);
INSERT INTO data_template_data_rra VALUES (30,2);
INSERT INTO data_template_data_rra VALUES (30,3);
INSERT INTO data_template_data_rra VALUES (30,4);
INSERT INTO data_template_data_rra VALUES (31,1);
INSERT INTO data_template_data_rra VALUES (31,2);
INSERT INTO data_template_data_rra VALUES (31,3);
INSERT INTO data_template_data_rra VALUES (31,4);
INSERT INTO data_template_data_rra VALUES (32,1);
INSERT INTO data_template_data_rra VALUES (32,2);
INSERT INTO data_template_data_rra VALUES (32,3);
INSERT INTO data_template_data_rra VALUES (32,4);
INSERT INTO data_template_data_rra VALUES (33,1);
INSERT INTO data_template_data_rra VALUES (33,2);
INSERT INTO data_template_data_rra VALUES (33,3);
INSERT INTO data_template_data_rra VALUES (33,4);
INSERT INTO data_template_data_rra VALUES (34,1);
INSERT INTO data_template_data_rra VALUES (34,2);
INSERT INTO data_template_data_rra VALUES (34,3);
INSERT INTO data_template_data_rra VALUES (34,4);
INSERT INTO data_template_data_rra VALUES (35,1);
INSERT INTO data_template_data_rra VALUES (35,2);
INSERT INTO data_template_data_rra VALUES (35,3);
INSERT INTO data_template_data_rra VALUES (35,4);
INSERT INTO data_template_data_rra VALUES (36,1);
INSERT INTO data_template_data_rra VALUES (36,2);
INSERT INTO data_template_data_rra VALUES (36,3);
INSERT INTO data_template_data_rra VALUES (36,4);
INSERT INTO data_template_data_rra VALUES (37,1);
INSERT INTO data_template_data_rra VALUES (37,2);
INSERT INTO data_template_data_rra VALUES (37,3);
INSERT INTO data_template_data_rra VALUES (37,4);
INSERT INTO data_template_data_rra VALUES (38,1);
INSERT INTO data_template_data_rra VALUES (38,2);
INSERT INTO data_template_data_rra VALUES (38,3);
INSERT INTO data_template_data_rra VALUES (38,4);
INSERT INTO data_template_data_rra VALUES (39,1);
INSERT INTO data_template_data_rra VALUES (39,2);
INSERT INTO data_template_data_rra VALUES (39,3);
INSERT INTO data_template_data_rra VALUES (39,4);
INSERT INTO data_template_data_rra VALUES (40,1);
INSERT INTO data_template_data_rra VALUES (40,2);
INSERT INTO data_template_data_rra VALUES (40,3);
INSERT INTO data_template_data_rra VALUES (40,4);
INSERT INTO data_template_data_rra VALUES (41,1);
INSERT INTO data_template_data_rra VALUES (41,2);
INSERT INTO data_template_data_rra VALUES (41,3);
INSERT INTO data_template_data_rra VALUES (41,4);

--
-- Table structure for table 'data_template_rrd'
--

CREATE TABLE data_template_rrd (
  id mediumint(8) NOT NULL auto_increment,
  local_data_template_rrd_id mediumint(8) NOT NULL default '0',
  local_data_id mediumint(8) NOT NULL default '0',
  data_template_id mediumint(8) NOT NULL default '0',
  t_rrd_maximum char(2) default NULL,
  rrd_maximum bigint(20) NOT NULL default '0',
  t_rrd_minimum char(2) default NULL,
  rrd_minimum bigint(20) NOT NULL default '0',
  t_rrd_heartbeat char(2) default NULL,
  rrd_heartbeat mediumint(6) NOT NULL default '0',
  t_data_source_type_id char(2) default NULL,
  data_source_type_id smallint(5) NOT NULL default '0',
  t_data_source_name char(2) default NULL,
  data_source_name varchar(19) NOT NULL default '',
  t_data_input_field_id char(2) default NULL,
  data_input_field_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id),
  KEY local_data_id (local_data_id),
  KEY data_template_id (data_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'data_template_rrd'
--


INSERT INTO data_template_rrd VALUES (1,0,0,1,'on',100000000,'',0,'',600,'',2,'','traffic_in','',0);
INSERT INTO data_template_rrd VALUES (2,0,0,2,'on',100000000,'',0,'',600,'',2,'','traffic_out','',0);
INSERT INTO data_template_rrd VALUES (3,0,0,3,'',10000000000,'',0,'',600,'',1,'','hdd_free','',0);
INSERT INTO data_template_rrd VALUES (4,0,0,3,'',10000000000,'',0,'',600,'',1,'','hdd_used','',0);
INSERT INTO data_template_rrd VALUES (5,0,0,4,'',100,'',0,'',600,'',2,'','cpu_system','',0);
INSERT INTO data_template_rrd VALUES (6,0,0,5,'',100,'',0,'',600,'',2,'','cpu_user','',0);
INSERT INTO data_template_rrd VALUES (7,0,0,6,'',100,'',0,'',600,'',2,'','cpu_nice','',0);
INSERT INTO data_template_rrd VALUES (8,0,0,7,'',100,'',0,'',600,'',1,'','wrls_noise','',0);
INSERT INTO data_template_rrd VALUES (9,0,0,8,'',100,'',0,'',600,'',1,'','wrls_signal','',0);
INSERT INTO data_template_rrd VALUES (10,0,0,9,'',1000000,'',0,'',600,'',2,'','wrls_transmits','',0);
INSERT INTO data_template_rrd VALUES (11,0,0,10,'',1000000,'',0,'',600,'',2,'','wrls_retransmits','',0);
INSERT INTO data_template_rrd VALUES (12,0,0,11,'',500,'',0,'',600,'',1,'','load_1min','',17);
INSERT INTO data_template_rrd VALUES (13,0,0,11,'',500,'',0,'',600,'',1,'','load_5min','',18);
INSERT INTO data_template_rrd VALUES (14,0,0,11,'',500,'',0,'',600,'',1,'','load_15min','',19);
INSERT INTO data_template_rrd VALUES (15,0,0,12,'',10000000,'',0,'',600,'',1,'','mem_buffers','',23);
INSERT INTO data_template_rrd VALUES (16,0,0,13,'',10000000,'',0,'',600,'',1,'','mem_buffers','',23);
INSERT INTO data_template_rrd VALUES (17,0,0,14,'',1000000,'',0,'',600,'',1,'','mem_cache','',23);
INSERT INTO data_template_rrd VALUES (18,0,0,15,'',1000000,'',0,'',600,'',1,'','mem_swap','',23);
INSERT INTO data_template_rrd VALUES (19,0,0,16,'',1000,'',0,'',600,'',1,'','proc','',24);
INSERT INTO data_template_rrd VALUES (20,0,0,17,'',500,'',0,'',600,'',1,'','users','',21);
INSERT INTO data_template_rrd VALUES (21,0,0,18,'',5000,'',0,'',600,'',1,'','ping','',30);
INSERT INTO data_template_rrd VALUES (22,0,0,19,'',100000,'',0,'',600,'',1,'','total_users','',0);
INSERT INTO data_template_rrd VALUES (23,0,0,20,'',100000,'',0,'',600,'',1,'','total_logins','',0);
INSERT INTO data_template_rrd VALUES (24,0,0,21,'',100,'',0,'',600,'',1,'','cpu','',0);
INSERT INTO data_template_rrd VALUES (25,0,0,22,'',10000000,'',0,'',600,'',2,'','fs_reads','',0);
INSERT INTO data_template_rrd VALUES (26,0,0,23,'',10000000,'',0,'',600,'',2,'','fs_writes','',0);
INSERT INTO data_template_rrd VALUES (27,0,0,24,'',10000000,'',0,'',600,'',2,'','cache_checks','',0);
INSERT INTO data_template_rrd VALUES (28,0,0,25,'',1000000,'',0,'',600,'',2,'','cache_hits','',0);
INSERT INTO data_template_rrd VALUES (29,0,0,26,'',100000,'',0,'',600,'',1,'','open_files','',0);
INSERT INTO data_template_rrd VALUES (30,0,0,27,'',100,'',0,'',600,'',1,'','5min_cpu','',0);
INSERT INTO data_template_rrd VALUES (31,0,0,28,'',100,'',0,'',600,'',1,'','5sec_cpu','',0);
INSERT INTO data_template_rrd VALUES (32,0,0,29,'',100,'',0,'',600,'',1,'','1min_cpu','',0);
INSERT INTO data_template_rrd VALUES (33,0,0,30,'',500,'',0,'',600,'',1,'','load_1min','',0);
INSERT INTO data_template_rrd VALUES (34,0,0,31,'',500,'',0,'',600,'',1,'','load_5min','',0);
INSERT INTO data_template_rrd VALUES (35,0,0,32,'',500,'',0,'',600,'',1,'','load_15min','',0);
INSERT INTO data_template_rrd VALUES (36,0,0,33,'',10000000,'',0,'',600,'',1,'','mem_buffers','',0);
INSERT INTO data_template_rrd VALUES (37,0,0,34,'',10000000,'',0,'',600,'',1,'','mem_free','',0);
INSERT INTO data_template_rrd VALUES (38,0,0,35,'',1000000000000,'',0,'',600,'',1,'','vol_total','',0);
INSERT INTO data_template_rrd VALUES (39,0,0,35,'',1000000000000,'',0,'',600,'',1,'','vol_free','',0);
INSERT INTO data_template_rrd VALUES (40,0,0,35,'',1000000000000,'',0,'',600,'',1,'','vol_freeable','',0);
INSERT INTO data_template_rrd VALUES (42,0,0,36,'',100000000000,'',0,'',600,'',1,'','dir_total','',0);
INSERT INTO data_template_rrd VALUES (43,0,0,36,'',100000000000,'',0,'',600,'',1,'','dir_used','',0);
INSERT INTO data_template_rrd VALUES (44,0,0,37,'on',10000000000,'',0,'',600,'',1,'','hdd_free','',0);
INSERT INTO data_template_rrd VALUES (54,0,0,41,'on',100000000,'',0,'',600,'',2,'','traffic_in','',0);
INSERT INTO data_template_rrd VALUES (46,0,0,38,'',10000000,'',0,'',600,'',2,'','errors_in','',0);
INSERT INTO data_template_rrd VALUES (47,0,0,38,'',10000000,'',0,'',600,'',2,'','discards_in','',0);
INSERT INTO data_template_rrd VALUES (48,0,0,39,'',1000000000,'',0,'',600,'',2,'','unicast_in','',0);
INSERT INTO data_template_rrd VALUES (49,0,0,39,'',1000000000,'',0,'',600,'',2,'','unicast_out','',0);
INSERT INTO data_template_rrd VALUES (50,0,0,38,'',10000000,'',0,'',600,'',2,'','discards_out','',0);
INSERT INTO data_template_rrd VALUES (51,0,0,38,'',10000000,'',0,'',600,'',2,'','errors_out','',0);
INSERT INTO data_template_rrd VALUES (52,0,0,40,'',1000000000,'',0,'',600,'',2,'','nonunicast_out','',0);
INSERT INTO data_template_rrd VALUES (53,0,0,40,'',1000000000,'',0,'',600,'',2,'','nonunicast_in','',0);
INSERT INTO data_template_rrd VALUES (55,0,0,41,'on',100000000,'',0,'',600,'',2,'','traffic_out','',0);
INSERT INTO data_template_rrd VALUES (56,0,0,37,'on',10000000000,'',0,'',600,'',1,'','hdd_used','',0);

--
-- Table structure for table 'graph_local'
--

CREATE TABLE graph_local (
  id mediumint(8) NOT NULL auto_increment,
  graph_template_id mediumint(8) NOT NULL default '0',
  host_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM COMMENT='Creates a relationship for each item in a custom graph.';

--
-- Dumping data for table 'graph_local'
--



--
-- Table structure for table 'graph_template_input'
--

CREATE TABLE graph_template_input (
  id mediumint(8) NOT NULL auto_increment,
  graph_template_id mediumint(8) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  description text,
  column_name varchar(50) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id),
  KEY id_3 (id)
) TYPE=MyISAM COMMENT='Stores the names for graph item input groups.';

--
-- Dumping data for table 'graph_template_input'
--


INSERT INTO graph_template_input VALUES (1,1,'Inbound Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (2,1,'Outbound Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (3,2,'Inbound Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (4,2,'Outbound Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (5,3,'Available Disk Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (6,3,'Used Disk Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (7,5,'Signal Level Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (8,5,'Noise Level Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (9,5,'Signal Level Color','','color_id');
INSERT INTO graph_template_input VALUES (10,5,'Noise Level Color','','color_id');
INSERT INTO graph_template_input VALUES (11,6,'Transmissions Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (12,6,'Re-Transmissions Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (13,6,'Transmissions Color','','color_id');
INSERT INTO graph_template_input VALUES (14,6,'Re-Transmissions Color','','color_id');
INSERT INTO graph_template_input VALUES (15,7,'Ping Host Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (16,7,'Legend Color','','color_id');
INSERT INTO graph_template_input VALUES (17,7,'Legend Text','','text_format');
INSERT INTO graph_template_input VALUES (18,8,'Processes Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (19,8,'Legend Color','','color_id');
INSERT INTO graph_template_input VALUES (20,9,'1 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (21,9,'5 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (22,9,'15 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (23,10,'Logged in Users Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (24,10,'Legend Color','','color_id');
INSERT INTO graph_template_input VALUES (25,11,'1 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (26,11,'5 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (27,11,'15 Minute Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (28,12,'Buffers Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (29,12,'Cache Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (30,12,'Free Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (31,12,'Swap Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (32,4,'System CPU Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (33,4,'User CPU Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (34,4,'Nice CPU Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (35,13,'Memory Free Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (36,13,'Memory Buffers Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (37,14,'Cache Hits Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (38,14,'Cache Checks Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (39,15,'CPU Usage Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (40,16,'File System Reads Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (41,16,'File System Writes Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (42,17,'Current Logins Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (43,17,'Open Files Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (44,15,'Legend Color','','color_id');
INSERT INTO graph_template_input VALUES (45,18,'CPU Usage Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (46,18,'Legend Color','','color_id');
INSERT INTO graph_template_input VALUES (47,19,'Free Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (48,19,'Total Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (49,19,'Freeable Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (53,21,'Available Disk Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (51,20,'Used Directory Entries Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (52,20,'Available Directory Entries Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (54,21,'Used Disk Space Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (55,22,'Discards In Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (56,22,'Errors In Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (57,23,'Unicast Packets Out Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (58,23,'Unicast Packets In Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (59,24,'Non-Unicast Packets In Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (60,24,'Non-Unicast Packets Out Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (61,22,'Discards Out Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (62,22,'Errors Out Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (63,25,'Inbound Data Source','','task_item_id');
INSERT INTO graph_template_input VALUES (64,25,'Outbound Data Source','','task_item_id');

--
-- Table structure for table 'graph_template_input_defs'
--

CREATE TABLE graph_template_input_defs (
  graph_template_input_id mediumint(8) NOT NULL default '0',
  graph_template_item_id int(12) NOT NULL default '0',
  PRIMARY KEY  (graph_template_input_id,graph_template_item_id),
  KEY graph_template_input_id (graph_template_input_id)
) TYPE=MyISAM COMMENT='Stores the relationship for what graph iitems are associated';

--
-- Dumping data for table 'graph_template_input_defs'
--


INSERT INTO graph_template_input_defs VALUES (1,1);
INSERT INTO graph_template_input_defs VALUES (1,2);
INSERT INTO graph_template_input_defs VALUES (1,3);
INSERT INTO graph_template_input_defs VALUES (1,4);
INSERT INTO graph_template_input_defs VALUES (2,5);
INSERT INTO graph_template_input_defs VALUES (2,6);
INSERT INTO graph_template_input_defs VALUES (2,7);
INSERT INTO graph_template_input_defs VALUES (2,8);
INSERT INTO graph_template_input_defs VALUES (3,9);
INSERT INTO graph_template_input_defs VALUES (3,10);
INSERT INTO graph_template_input_defs VALUES (3,11);
INSERT INTO graph_template_input_defs VALUES (3,12);
INSERT INTO graph_template_input_defs VALUES (4,13);
INSERT INTO graph_template_input_defs VALUES (4,14);
INSERT INTO graph_template_input_defs VALUES (4,15);
INSERT INTO graph_template_input_defs VALUES (4,16);
INSERT INTO graph_template_input_defs VALUES (5,21);
INSERT INTO graph_template_input_defs VALUES (5,22);
INSERT INTO graph_template_input_defs VALUES (5,23);
INSERT INTO graph_template_input_defs VALUES (5,24);
INSERT INTO graph_template_input_defs VALUES (6,17);
INSERT INTO graph_template_input_defs VALUES (6,18);
INSERT INTO graph_template_input_defs VALUES (6,19);
INSERT INTO graph_template_input_defs VALUES (6,20);
INSERT INTO graph_template_input_defs VALUES (7,45);
INSERT INTO graph_template_input_defs VALUES (7,46);
INSERT INTO graph_template_input_defs VALUES (7,47);
INSERT INTO graph_template_input_defs VALUES (7,48);
INSERT INTO graph_template_input_defs VALUES (8,49);
INSERT INTO graph_template_input_defs VALUES (8,50);
INSERT INTO graph_template_input_defs VALUES (8,51);
INSERT INTO graph_template_input_defs VALUES (8,52);
INSERT INTO graph_template_input_defs VALUES (9,45);
INSERT INTO graph_template_input_defs VALUES (10,49);
INSERT INTO graph_template_input_defs VALUES (11,53);
INSERT INTO graph_template_input_defs VALUES (11,54);
INSERT INTO graph_template_input_defs VALUES (11,55);
INSERT INTO graph_template_input_defs VALUES (11,56);
INSERT INTO graph_template_input_defs VALUES (12,57);
INSERT INTO graph_template_input_defs VALUES (12,58);
INSERT INTO graph_template_input_defs VALUES (12,59);
INSERT INTO graph_template_input_defs VALUES (12,60);
INSERT INTO graph_template_input_defs VALUES (13,53);
INSERT INTO graph_template_input_defs VALUES (14,57);
INSERT INTO graph_template_input_defs VALUES (15,61);
INSERT INTO graph_template_input_defs VALUES (15,62);
INSERT INTO graph_template_input_defs VALUES (15,63);
INSERT INTO graph_template_input_defs VALUES (15,64);
INSERT INTO graph_template_input_defs VALUES (16,61);
INSERT INTO graph_template_input_defs VALUES (17,61);
INSERT INTO graph_template_input_defs VALUES (18,65);
INSERT INTO graph_template_input_defs VALUES (18,66);
INSERT INTO graph_template_input_defs VALUES (18,67);
INSERT INTO graph_template_input_defs VALUES (18,68);
INSERT INTO graph_template_input_defs VALUES (19,65);
INSERT INTO graph_template_input_defs VALUES (20,69);
INSERT INTO graph_template_input_defs VALUES (20,70);
INSERT INTO graph_template_input_defs VALUES (21,71);
INSERT INTO graph_template_input_defs VALUES (21,72);
INSERT INTO graph_template_input_defs VALUES (22,73);
INSERT INTO graph_template_input_defs VALUES (22,74);
INSERT INTO graph_template_input_defs VALUES (23,76);
INSERT INTO graph_template_input_defs VALUES (23,77);
INSERT INTO graph_template_input_defs VALUES (23,78);
INSERT INTO graph_template_input_defs VALUES (23,79);
INSERT INTO graph_template_input_defs VALUES (24,76);
INSERT INTO graph_template_input_defs VALUES (25,80);
INSERT INTO graph_template_input_defs VALUES (25,81);
INSERT INTO graph_template_input_defs VALUES (26,82);
INSERT INTO graph_template_input_defs VALUES (26,83);
INSERT INTO graph_template_input_defs VALUES (27,84);
INSERT INTO graph_template_input_defs VALUES (27,85);
INSERT INTO graph_template_input_defs VALUES (28,87);
INSERT INTO graph_template_input_defs VALUES (28,88);
INSERT INTO graph_template_input_defs VALUES (28,89);
INSERT INTO graph_template_input_defs VALUES (28,90);
INSERT INTO graph_template_input_defs VALUES (29,91);
INSERT INTO graph_template_input_defs VALUES (29,92);
INSERT INTO graph_template_input_defs VALUES (29,93);
INSERT INTO graph_template_input_defs VALUES (29,94);
INSERT INTO graph_template_input_defs VALUES (30,95);
INSERT INTO graph_template_input_defs VALUES (30,96);
INSERT INTO graph_template_input_defs VALUES (30,97);
INSERT INTO graph_template_input_defs VALUES (30,98);
INSERT INTO graph_template_input_defs VALUES (31,99);
INSERT INTO graph_template_input_defs VALUES (31,100);
INSERT INTO graph_template_input_defs VALUES (31,101);
INSERT INTO graph_template_input_defs VALUES (31,102);
INSERT INTO graph_template_input_defs VALUES (32,29);
INSERT INTO graph_template_input_defs VALUES (32,30);
INSERT INTO graph_template_input_defs VALUES (32,31);
INSERT INTO graph_template_input_defs VALUES (32,32);
INSERT INTO graph_template_input_defs VALUES (33,33);
INSERT INTO graph_template_input_defs VALUES (33,34);
INSERT INTO graph_template_input_defs VALUES (33,35);
INSERT INTO graph_template_input_defs VALUES (33,36);
INSERT INTO graph_template_input_defs VALUES (34,37);
INSERT INTO graph_template_input_defs VALUES (34,38);
INSERT INTO graph_template_input_defs VALUES (34,39);
INSERT INTO graph_template_input_defs VALUES (34,40);
INSERT INTO graph_template_input_defs VALUES (35,103);
INSERT INTO graph_template_input_defs VALUES (35,104);
INSERT INTO graph_template_input_defs VALUES (35,105);
INSERT INTO graph_template_input_defs VALUES (35,106);
INSERT INTO graph_template_input_defs VALUES (36,107);
INSERT INTO graph_template_input_defs VALUES (36,108);
INSERT INTO graph_template_input_defs VALUES (36,109);
INSERT INTO graph_template_input_defs VALUES (36,110);
INSERT INTO graph_template_input_defs VALUES (37,111);
INSERT INTO graph_template_input_defs VALUES (37,112);
INSERT INTO graph_template_input_defs VALUES (37,113);
INSERT INTO graph_template_input_defs VALUES (37,114);
INSERT INTO graph_template_input_defs VALUES (38,115);
INSERT INTO graph_template_input_defs VALUES (38,116);
INSERT INTO graph_template_input_defs VALUES (38,117);
INSERT INTO graph_template_input_defs VALUES (38,118);
INSERT INTO graph_template_input_defs VALUES (39,119);
INSERT INTO graph_template_input_defs VALUES (39,120);
INSERT INTO graph_template_input_defs VALUES (39,121);
INSERT INTO graph_template_input_defs VALUES (39,122);
INSERT INTO graph_template_input_defs VALUES (40,123);
INSERT INTO graph_template_input_defs VALUES (40,124);
INSERT INTO graph_template_input_defs VALUES (40,125);
INSERT INTO graph_template_input_defs VALUES (40,126);
INSERT INTO graph_template_input_defs VALUES (41,127);
INSERT INTO graph_template_input_defs VALUES (41,128);
INSERT INTO graph_template_input_defs VALUES (41,129);
INSERT INTO graph_template_input_defs VALUES (41,130);
INSERT INTO graph_template_input_defs VALUES (42,131);
INSERT INTO graph_template_input_defs VALUES (42,132);
INSERT INTO graph_template_input_defs VALUES (42,133);
INSERT INTO graph_template_input_defs VALUES (42,134);
INSERT INTO graph_template_input_defs VALUES (43,135);
INSERT INTO graph_template_input_defs VALUES (43,136);
INSERT INTO graph_template_input_defs VALUES (43,137);
INSERT INTO graph_template_input_defs VALUES (43,138);
INSERT INTO graph_template_input_defs VALUES (44,119);
INSERT INTO graph_template_input_defs VALUES (45,139);
INSERT INTO graph_template_input_defs VALUES (45,140);
INSERT INTO graph_template_input_defs VALUES (45,141);
INSERT INTO graph_template_input_defs VALUES (45,142);
INSERT INTO graph_template_input_defs VALUES (46,139);
INSERT INTO graph_template_input_defs VALUES (47,143);
INSERT INTO graph_template_input_defs VALUES (47,144);
INSERT INTO graph_template_input_defs VALUES (47,145);
INSERT INTO graph_template_input_defs VALUES (47,146);
INSERT INTO graph_template_input_defs VALUES (48,147);
INSERT INTO graph_template_input_defs VALUES (48,148);
INSERT INTO graph_template_input_defs VALUES (48,149);
INSERT INTO graph_template_input_defs VALUES (48,150);
INSERT INTO graph_template_input_defs VALUES (49,151);
INSERT INTO graph_template_input_defs VALUES (49,152);
INSERT INTO graph_template_input_defs VALUES (49,153);
INSERT INTO graph_template_input_defs VALUES (49,154);
INSERT INTO graph_template_input_defs VALUES (51,159);
INSERT INTO graph_template_input_defs VALUES (51,160);
INSERT INTO graph_template_input_defs VALUES (51,161);
INSERT INTO graph_template_input_defs VALUES (51,162);
INSERT INTO graph_template_input_defs VALUES (52,163);
INSERT INTO graph_template_input_defs VALUES (52,164);
INSERT INTO graph_template_input_defs VALUES (52,165);
INSERT INTO graph_template_input_defs VALUES (52,166);
INSERT INTO graph_template_input_defs VALUES (53,172);
INSERT INTO graph_template_input_defs VALUES (53,173);
INSERT INTO graph_template_input_defs VALUES (53,174);
INSERT INTO graph_template_input_defs VALUES (53,175);
INSERT INTO graph_template_input_defs VALUES (54,167);
INSERT INTO graph_template_input_defs VALUES (54,169);
INSERT INTO graph_template_input_defs VALUES (54,170);
INSERT INTO graph_template_input_defs VALUES (54,171);
INSERT INTO graph_template_input_defs VALUES (55,180);
INSERT INTO graph_template_input_defs VALUES (55,181);
INSERT INTO graph_template_input_defs VALUES (55,182);
INSERT INTO graph_template_input_defs VALUES (55,183);
INSERT INTO graph_template_input_defs VALUES (56,184);
INSERT INTO graph_template_input_defs VALUES (56,185);
INSERT INTO graph_template_input_defs VALUES (56,186);
INSERT INTO graph_template_input_defs VALUES (56,187);
INSERT INTO graph_template_input_defs VALUES (57,188);
INSERT INTO graph_template_input_defs VALUES (57,189);
INSERT INTO graph_template_input_defs VALUES (57,190);
INSERT INTO graph_template_input_defs VALUES (57,191);
INSERT INTO graph_template_input_defs VALUES (58,192);
INSERT INTO graph_template_input_defs VALUES (58,193);
INSERT INTO graph_template_input_defs VALUES (58,194);
INSERT INTO graph_template_input_defs VALUES (58,195);
INSERT INTO graph_template_input_defs VALUES (59,196);
INSERT INTO graph_template_input_defs VALUES (59,197);
INSERT INTO graph_template_input_defs VALUES (59,198);
INSERT INTO graph_template_input_defs VALUES (59,199);
INSERT INTO graph_template_input_defs VALUES (60,200);
INSERT INTO graph_template_input_defs VALUES (60,201);
INSERT INTO graph_template_input_defs VALUES (60,202);
INSERT INTO graph_template_input_defs VALUES (60,203);
INSERT INTO graph_template_input_defs VALUES (61,204);
INSERT INTO graph_template_input_defs VALUES (61,205);
INSERT INTO graph_template_input_defs VALUES (61,206);
INSERT INTO graph_template_input_defs VALUES (61,207);
INSERT INTO graph_template_input_defs VALUES (62,208);
INSERT INTO graph_template_input_defs VALUES (62,209);
INSERT INTO graph_template_input_defs VALUES (62,210);
INSERT INTO graph_template_input_defs VALUES (62,211);
INSERT INTO graph_template_input_defs VALUES (63,212);
INSERT INTO graph_template_input_defs VALUES (63,213);
INSERT INTO graph_template_input_defs VALUES (63,214);
INSERT INTO graph_template_input_defs VALUES (63,215);
INSERT INTO graph_template_input_defs VALUES (64,216);
INSERT INTO graph_template_input_defs VALUES (64,217);
INSERT INTO graph_template_input_defs VALUES (64,218);
INSERT INTO graph_template_input_defs VALUES (64,219);

--
-- Table structure for table 'graph_templates'
--

CREATE TABLE graph_templates (
  id mediumint(8) NOT NULL auto_increment,
  name char(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM COMMENT='Contains each graph template name.';

--
-- Dumping data for table 'graph_templates'
--


INSERT INTO graph_templates VALUES (1,'Interface - Traffic (bytes/sec) - 0.6.x');
INSERT INTO graph_templates VALUES (2,'Interface - Traffic (bits/sec)');
INSERT INTO graph_templates VALUES (3,'ucd/net - Available Disk Space');
INSERT INTO graph_templates VALUES (4,'ucd/net - CPU Usage');
INSERT INTO graph_templates VALUES (5,'Karlnet - Wireless Levels');
INSERT INTO graph_templates VALUES (6,'Karlnet - Wireless Transmissions');
INSERT INTO graph_templates VALUES (7,'Unix - Ping Latency');
INSERT INTO graph_templates VALUES (8,'Unix - Processes');
INSERT INTO graph_templates VALUES (9,'Unix - Load Average');
INSERT INTO graph_templates VALUES (10,'Unix - Logged in Users');
INSERT INTO graph_templates VALUES (11,'ucd/net - Load Average');
INSERT INTO graph_templates VALUES (12,'Linux - Memory Usage');
INSERT INTO graph_templates VALUES (13,'ucd/net - Memory Usage');
INSERT INTO graph_templates VALUES (14,'Netware - File System Cache');
INSERT INTO graph_templates VALUES (15,'Netware - CPU Usage');
INSERT INTO graph_templates VALUES (16,'Netware - File System Activity');
INSERT INTO graph_templates VALUES (17,'Netware - User Processes');
INSERT INTO graph_templates VALUES (18,'Cisco - CPU Usage');
INSERT INTO graph_templates VALUES (19,'Netware - Volume Information');
INSERT INTO graph_templates VALUES (20,'Netware - Directory Information');
INSERT INTO graph_templates VALUES (21,'Unix - Available Disk Space');
INSERT INTO graph_templates VALUES (22,'Interface - Errors/Discards');
INSERT INTO graph_templates VALUES (23,'Interface - Unicast Packets');
INSERT INTO graph_templates VALUES (24,'Interface - Non-Unicast Packets');
INSERT INTO graph_templates VALUES (25,'Interface - Traffic (bytes/sec)');

--
-- Table structure for table 'graph_templates_gprint'
--

CREATE TABLE graph_templates_gprint (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  gprint_text varchar(255) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'graph_templates_gprint'
--


INSERT INTO graph_templates_gprint VALUES (2,'Normal','%8.2lf %s');
INSERT INTO graph_templates_gprint VALUES (3,'Exact Numbers','%8.0lf');
INSERT INTO graph_templates_gprint VALUES (4,'Load Average','%8.2lf');

--
-- Table structure for table 'graph_templates_graph'
--

CREATE TABLE graph_templates_graph (
  id mediumint(8) NOT NULL auto_increment,
  local_graph_template_graph_id mediumint(8) NOT NULL default '0',
  local_graph_id mediumint(8) NOT NULL default '0',
  graph_template_id mediumint(8) NOT NULL default '0',
  t_image_format_id char(2) default '0',
  image_format_id tinyint(1) NOT NULL default '0',
  t_title char(2) default '0',
  title varchar(255) NOT NULL default '',
  t_height char(2) default '0',
  height mediumint(8) NOT NULL default '0',
  t_width char(2) default '0',
  width mediumint(8) NOT NULL default '0',
  t_upper_limit char(2) default '0',
  upper_limit bigint(12) NOT NULL default '0',
  t_lower_limit char(2) default '0',
  lower_limit bigint(12) NOT NULL default '0',
  t_vertical_label char(2) default '0',
  vertical_label varchar(200) default NULL,
  t_auto_scale char(2) default '0',
  auto_scale char(2) default NULL,
  t_auto_scale_opts char(2) default '0',
  auto_scale_opts tinyint(1) NOT NULL default '0',
  t_auto_scale_log char(2) default '0',
  auto_scale_log char(2) default NULL,
  t_auto_scale_rigid char(2) default '0',
  auto_scale_rigid char(2) default NULL,
  t_auto_padding char(2) default '0',
  auto_padding char(2) default NULL,
  t_base_value char(2) default '0',
  base_value mediumint(8) NOT NULL default '0',
  t_grouping char(2) default '0',
  grouping char(2) NOT NULL default '',
  t_export char(2) default '0',
  export char(2) default NULL,
  t_unit_value char(2) default '0',
  unit_value varchar(20) default NULL,
  t_unit_exponent_value char(2) default '0',
  unit_exponent_value smallint(5) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY local_graph_id (local_graph_id),
  KEY id_2 (id),
  KEY graph_template_id (graph_template_id)
) TYPE=MyISAM COMMENT='Stores the actual graph data.';

--
-- Dumping data for table 'graph_templates_graph'
--


INSERT INTO graph_templates_graph VALUES (1,0,0,1,'',1,'on','|host_description| - Traffic','',120,'',500,'',100,'',0,'','bytes per second','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (2,0,0,2,'',1,'on','|host_description| - Traffic','',120,'',500,'',100,'',0,'','bits per second','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (3,0,0,3,'',1,'on','|host_description| - Hard Drive Space','',120,'',500,'',100,'',0,'','bytes','','on','',2,'','','','on','','on','',1024,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (4,0,0,4,'',1,'on','|host_description| - CPU Usage','',120,'',500,'',100,'',0,'','percent','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (5,0,0,5,'',1,'on','|host_description| - Wireless Levels','',120,'',500,'',100,'',0,'','percent','','','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (6,0,0,6,'',1,'on','|host_description| - Wireless Transmissions','',120,'',500,'',100,'',0,'','transmissions','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (7,0,0,7,'',1,'on','|host_description| - Ping Latency','',120,'',500,'',100,'',0,'','milliseconds','','on','',2,'','','','','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (8,0,0,8,'',1,'on','|host_description| - Processes','',120,'',500,'',100,'',0,'','processes','','on','',2,'','','','','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (9,0,0,9,'',1,'on','|host_description| - Load Average','',120,'',500,'',100,'',0,'','processes in the run queue','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (10,0,0,10,'',1,'on','|host_description| - Logged in Users','',120,'',500,'',100,'',0,'','users','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (11,0,0,11,'',1,'on','|host_description| - Load Average','',120,'',500,'',100,'',0,'','processes in the run queue','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (12,0,0,12,'',1,'on','|host_description| - Memory Usage','',120,'',500,'',100,'',0,'','kilobytes','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (13,0,0,13,'',1,'on','|host_description| - Memory Usage','',120,'',500,'',100,'',0,'','bytes','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (14,0,0,14,'',1,'on','|host_description| - File System Cache','',120,'',500,'',100,'',0,'','cache checks/hits','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (15,0,0,15,'',1,'on','|host_description| - CPU Usage','',120,'',500,'',100,'',0,'','percent','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (16,0,0,16,'',1,'on','|host_description| - File System Activity','',120,'',500,'',100,'',0,'','reads/writes per sec','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (17,0,0,17,'',1,'on','|host_description| - User Processes','',120,'',500,'',100,'',0,'','users/files open','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (18,0,0,18,'',1,'on','|host_description| - CPU Usage','',120,'',500,'',100,'',0,'','percent','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (19,0,0,19,'',1,'on','|host_description| - Volume Information','',120,'',500,'',100,'',0,'','bytes','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (20,0,0,20,'',1,'on','|host_description| - Directory Information','',120,'',500,'',100,'',0,'','directory entries','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (21,0,0,21,'',1,'on','|host_description| - Available Disk Space','',120,'',500,'',100,'',0,'','bytes','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (22,0,0,22,'',1,'on','|host_description| - Errors/Discards','',120,'',500,'',100,'',0,'','errors/sec','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (23,0,0,23,'',1,'on','|host_description| - Unicast Packets','',120,'',500,'',100,'',0,'','packets/sec','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (24,0,0,24,'',1,'on','|host_description| - Non-Unicast Packets','',120,'',500,'',100,'',0,'','packets/sec','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);
INSERT INTO graph_templates_graph VALUES (25,0,0,25,'',1,'on','|host_description| - Traffic','',120,'',500,'',100,'',0,'','bytes per second','','on','',2,'','','','on','','on','',1000,'0','','','on','','','',0);

--
-- Table structure for table 'graph_templates_item'
--

CREATE TABLE graph_templates_item (
  id int(12) NOT NULL auto_increment,
  local_graph_template_item_id int(12) NOT NULL default '0',
  local_graph_id mediumint(8) NOT NULL default '0',
  graph_template_id mediumint(8) NOT NULL default '0',
  task_item_id mediumint(8) NOT NULL default '0',
  color_id mediumint(8) NOT NULL default '0',
  graph_type_id tinyint(3) NOT NULL default '0',
  cdef_id mediumint(8) NOT NULL default '0',
  consolidation_function_id tinyint(2) NOT NULL default '0',
  text_format varchar(255) default NULL,
  value varchar(255) default NULL,
  hard_return char(2) default NULL,
  gprint_id mediumint(8) NOT NULL default '0',
  sequence mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY graph_template_id (graph_template_id),
  KEY id_2 (id),
  KEY local_graph_id (local_graph_id)
) TYPE=MyISAM COMMENT='Stores the actual graph item data.';

--
-- Dumping data for table 'graph_templates_item'
--


INSERT INTO graph_templates_item VALUES (1,0,0,1,1,22,7,0,1,'Inbound','','',2,1);
INSERT INTO graph_templates_item VALUES (2,0,0,1,1,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (3,0,0,1,1,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (4,0,0,1,1,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (5,0,0,1,2,20,4,0,1,'Outbound','','',2,5);
INSERT INTO graph_templates_item VALUES (6,0,0,1,2,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (7,0,0,1,2,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (8,0,0,1,2,0,9,0,3,'Maximum:','','',2,8);
INSERT INTO graph_templates_item VALUES (9,0,0,2,54,22,7,2,1,'Inbound','','',2,1);
INSERT INTO graph_templates_item VALUES (10,0,0,2,54,0,9,2,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (11,0,0,2,54,0,9,2,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (12,0,0,2,54,0,9,2,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (13,0,0,2,55,20,4,2,1,'Outbound','','',2,5);
INSERT INTO graph_templates_item VALUES (14,0,0,2,55,0,9,2,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (15,0,0,2,55,0,9,2,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (16,0,0,2,55,0,9,2,3,'Maximum:','','',2,8);
INSERT INTO graph_templates_item VALUES (17,0,0,3,4,48,7,14,1,'Used','','',2,1);
INSERT INTO graph_templates_item VALUES (18,0,0,3,4,0,9,14,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (19,0,0,3,4,0,9,14,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (20,0,0,3,4,0,9,14,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (21,0,0,3,3,20,8,14,1,'Available','','',2,5);
INSERT INTO graph_templates_item VALUES (22,0,0,3,3,0,9,14,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (23,0,0,3,3,0,9,14,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (24,0,0,3,3,0,9,14,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (25,0,0,3,0,1,5,15,1,'Total','','',2,9);
INSERT INTO graph_templates_item VALUES (26,0,0,3,0,0,9,15,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (27,0,0,3,0,0,9,15,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (28,0,0,3,0,0,9,15,3,'Maximum:','','',2,12);
INSERT INTO graph_templates_item VALUES (29,0,0,4,5,9,7,0,1,'System','','',2,1);
INSERT INTO graph_templates_item VALUES (30,0,0,4,5,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (31,0,0,4,5,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (32,0,0,4,5,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (33,0,0,4,6,21,8,0,1,'User','','',2,5);
INSERT INTO graph_templates_item VALUES (34,0,0,4,6,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (35,0,0,4,6,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (36,0,0,4,6,0,9,0,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (37,0,0,4,7,12,8,0,1,'Nice','','',2,9);
INSERT INTO graph_templates_item VALUES (38,0,0,4,7,0,9,0,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (39,0,0,4,7,0,9,0,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (40,0,0,4,7,0,9,0,3,'Maximum:','','on',2,12);
INSERT INTO graph_templates_item VALUES (41,0,0,4,0,1,4,12,1,'Total','','',2,13);
INSERT INTO graph_templates_item VALUES (42,0,0,4,0,0,9,12,4,'Current:','','',2,14);
INSERT INTO graph_templates_item VALUES (43,0,0,4,0,0,9,12,1,'Average:','','',2,15);
INSERT INTO graph_templates_item VALUES (44,0,0,4,0,0,9,12,3,'Maximum:','','',2,16);
INSERT INTO graph_templates_item VALUES (45,0,0,5,9,48,7,0,1,'Signal Level','','',2,1);
INSERT INTO graph_templates_item VALUES (46,0,0,5,9,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (47,0,0,5,9,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (48,0,0,5,9,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (49,0,0,5,8,25,5,0,1,'Noise Level','','',2,5);
INSERT INTO graph_templates_item VALUES (50,0,0,5,8,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (51,0,0,5,8,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (52,0,0,5,8,0,9,0,3,'Maximum:','','',2,8);
INSERT INTO graph_templates_item VALUES (53,0,0,6,10,48,7,0,1,'Transmissions','','',2,1);
INSERT INTO graph_templates_item VALUES (54,0,0,6,10,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (55,0,0,6,10,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (56,0,0,6,10,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (57,0,0,6,11,25,5,0,1,'Re-Transmissions','','',2,5);
INSERT INTO graph_templates_item VALUES (58,0,0,6,11,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (59,0,0,6,11,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (60,0,0,6,11,0,9,0,3,'Maximum:','','',2,8);
INSERT INTO graph_templates_item VALUES (61,0,0,7,21,25,7,0,1,'','','',2,1);
INSERT INTO graph_templates_item VALUES (62,0,0,7,21,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (63,0,0,7,21,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (64,0,0,7,21,0,9,0,3,'Maximum:','','',2,4);
INSERT INTO graph_templates_item VALUES (65,0,0,8,19,48,1,0,1,'Running Processes','','',2,1);
INSERT INTO graph_templates_item VALUES (66,0,0,8,19,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (67,0,0,8,19,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (68,0,0,8,19,0,9,0,3,'Maximum:','','',3,4);
INSERT INTO graph_templates_item VALUES (69,0,0,9,12,15,7,0,1,'1 Minute Average','','',2,1);
INSERT INTO graph_templates_item VALUES (70,0,0,9,12,0,9,0,4,'Current:','','on',4,2);
INSERT INTO graph_templates_item VALUES (71,0,0,9,13,8,8,0,1,'5 Minute Average','','',2,3);
INSERT INTO graph_templates_item VALUES (72,0,0,9,13,0,9,0,4,'Current:','','on',4,4);
INSERT INTO graph_templates_item VALUES (73,0,0,9,14,9,8,0,1,'15 Minute Average','','',2,5);
INSERT INTO graph_templates_item VALUES (74,0,0,9,14,0,9,0,4,'Current:','','on',4,6);
INSERT INTO graph_templates_item VALUES (75,0,0,9,0,1,4,12,1,'','','',2,7);
INSERT INTO graph_templates_item VALUES (76,0,0,10,20,67,7,0,1,'Users','','',2,1);
INSERT INTO graph_templates_item VALUES (77,0,0,10,20,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (78,0,0,10,20,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (79,0,0,10,20,0,9,0,3,'Maximum:','','',3,4);
INSERT INTO graph_templates_item VALUES (80,0,0,11,33,15,7,0,1,'1 Minute Average','','',2,1);
INSERT INTO graph_templates_item VALUES (81,0,0,11,33,0,9,0,4,'Current:','','on',3,2);
INSERT INTO graph_templates_item VALUES (82,0,0,11,34,8,8,0,1,'5 Minute Average','','',2,3);
INSERT INTO graph_templates_item VALUES (83,0,0,11,34,0,9,0,4,'Current:','','on',3,4);
INSERT INTO graph_templates_item VALUES (84,0,0,11,35,9,8,0,1,'15 Minute Average','','',2,5);
INSERT INTO graph_templates_item VALUES (85,0,0,11,35,0,9,0,4,'Current:','','on',3,6);
INSERT INTO graph_templates_item VALUES (86,0,0,11,0,1,4,12,1,'Total','','',2,7);
INSERT INTO graph_templates_item VALUES (87,0,0,12,15,34,7,0,1,'Buffers','','',2,1);
INSERT INTO graph_templates_item VALUES (88,0,0,12,15,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (89,0,0,12,15,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (90,0,0,12,15,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (91,0,0,12,17,42,8,0,1,'Cache','','',2,5);
INSERT INTO graph_templates_item VALUES (92,0,0,12,17,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (93,0,0,12,17,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (94,0,0,12,17,0,9,0,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (95,0,0,12,16,41,8,0,1,'Free','','',2,9);
INSERT INTO graph_templates_item VALUES (96,0,0,12,16,0,9,0,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (97,0,0,12,16,0,9,0,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (98,0,0,12,16,0,9,0,3,'Maximum:','','on',2,12);
INSERT INTO graph_templates_item VALUES (99,0,0,12,18,30,8,0,1,'Swap','','',2,13);
INSERT INTO graph_templates_item VALUES (100,0,0,12,18,0,9,0,4,'Current:','','',2,14);
INSERT INTO graph_templates_item VALUES (101,0,0,12,18,0,9,0,1,'Average:','','',2,15);
INSERT INTO graph_templates_item VALUES (102,0,0,12,18,0,9,0,3,'Maximum:','','',2,16);
INSERT INTO graph_templates_item VALUES (103,0,0,13,37,52,7,14,1,'Memory Free','','',2,1);
INSERT INTO graph_templates_item VALUES (104,0,0,13,37,0,9,14,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (105,0,0,13,37,0,9,14,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (106,0,0,13,37,0,9,14,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (107,0,0,13,36,35,8,14,1,'Memory Buffers','','',2,5);
INSERT INTO graph_templates_item VALUES (108,0,0,13,36,0,9,14,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (109,0,0,13,36,0,9,14,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (110,0,0,13,36,0,9,14,3,'Maximum:','','',2,8);
INSERT INTO graph_templates_item VALUES (111,0,0,14,28,41,7,0,1,'Cache Hits','','',2,1);
INSERT INTO graph_templates_item VALUES (112,0,0,14,28,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (113,0,0,14,28,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (114,0,0,14,28,0,9,0,3,'Maximum:','','on',3,4);
INSERT INTO graph_templates_item VALUES (115,0,0,14,27,66,8,0,1,'Cache Checks','','',2,5);
INSERT INTO graph_templates_item VALUES (116,0,0,14,27,0,9,0,4,'Current:','','',3,6);
INSERT INTO graph_templates_item VALUES (117,0,0,14,27,0,9,0,1,'Average:','','',3,7);
INSERT INTO graph_templates_item VALUES (118,0,0,14,27,0,9,0,3,'Maximum:','','',3,8);
INSERT INTO graph_templates_item VALUES (119,0,0,15,24,9,7,0,1,'CPU Usage','','',2,1);
INSERT INTO graph_templates_item VALUES (120,0,0,15,24,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (121,0,0,15,24,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (122,0,0,15,24,0,9,0,3,'Maximum:','','',3,4);
INSERT INTO graph_templates_item VALUES (123,0,0,16,25,67,7,0,1,'File System Reads','','',2,1);
INSERT INTO graph_templates_item VALUES (124,0,0,16,25,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (125,0,0,16,25,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (126,0,0,16,25,0,9,0,3,'Maximum:','','on',3,4);
INSERT INTO graph_templates_item VALUES (127,0,0,16,26,93,8,0,1,'File System Writes','','',2,5);
INSERT INTO graph_templates_item VALUES (128,0,0,16,26,0,9,0,4,'Current:','','',3,6);
INSERT INTO graph_templates_item VALUES (129,0,0,16,26,0,9,0,1,'Average:','','',3,7);
INSERT INTO graph_templates_item VALUES (130,0,0,16,26,0,9,0,3,'Maximum:','','',3,8);
INSERT INTO graph_templates_item VALUES (131,0,0,17,23,30,7,0,1,'Current Logins','','',2,1);
INSERT INTO graph_templates_item VALUES (132,0,0,17,23,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (133,0,0,17,23,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (134,0,0,17,23,0,9,0,3,'Maximum:','','on',3,4);
INSERT INTO graph_templates_item VALUES (135,0,0,17,29,89,8,0,1,'Open Files','','',2,5);
INSERT INTO graph_templates_item VALUES (136,0,0,17,29,0,9,0,4,'Current:','','',3,6);
INSERT INTO graph_templates_item VALUES (137,0,0,17,29,0,9,0,1,'Average:','','',3,7);
INSERT INTO graph_templates_item VALUES (138,0,0,17,29,0,9,0,3,'Maximum:','','',3,8);
INSERT INTO graph_templates_item VALUES (139,0,0,18,30,9,7,0,1,'CPU Usage','','',2,1);
INSERT INTO graph_templates_item VALUES (140,0,0,18,30,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (141,0,0,18,30,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (142,0,0,18,30,0,9,0,3,'Maximum:','','',3,4);
INSERT INTO graph_templates_item VALUES (143,0,0,19,39,25,7,14,1,'Free Space','','',2,5);
INSERT INTO graph_templates_item VALUES (144,0,0,19,39,0,9,14,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (145,0,0,19,39,0,9,14,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (146,0,0,19,39,0,9,14,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (147,0,0,19,38,69,7,14,1,'Total Space','','',2,1);
INSERT INTO graph_templates_item VALUES (148,0,0,19,38,0,9,14,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (149,0,0,19,38,0,9,14,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (150,0,0,19,38,0,9,14,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (151,0,0,19,40,95,5,14,1,'Freeable Space','','',2,9);
INSERT INTO graph_templates_item VALUES (152,0,0,19,40,0,9,14,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (153,0,0,19,40,0,9,14,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (154,0,0,19,40,0,9,14,3,'Maximum:','','on',2,12);
INSERT INTO graph_templates_item VALUES (171,0,0,21,56,0,9,14,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (170,0,0,21,56,0,9,14,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (169,0,0,21,56,0,9,14,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (167,0,0,21,56,48,7,14,1,'Used','','',2,1);
INSERT INTO graph_templates_item VALUES (159,0,0,20,43,77,7,0,1,'Used Directory Entries','','',2,1);
INSERT INTO graph_templates_item VALUES (160,0,0,20,43,0,9,0,4,'Current:','','',3,2);
INSERT INTO graph_templates_item VALUES (161,0,0,20,43,0,9,0,1,'Average:','','',3,3);
INSERT INTO graph_templates_item VALUES (162,0,0,20,43,0,9,0,3,'Maximum:','','on',3,4);
INSERT INTO graph_templates_item VALUES (163,0,0,20,42,1,5,0,1,'Available Directory Entries','','',2,5);
INSERT INTO graph_templates_item VALUES (164,0,0,20,42,0,9,0,4,'Current:','','',3,6);
INSERT INTO graph_templates_item VALUES (165,0,0,20,42,0,9,0,1,'Average:','','',3,7);
INSERT INTO graph_templates_item VALUES (166,0,0,20,42,0,9,0,3,'Maximum:','','',3,8);
INSERT INTO graph_templates_item VALUES (172,0,0,21,44,20,8,14,1,'Available','','',2,5);
INSERT INTO graph_templates_item VALUES (173,0,0,21,44,0,9,14,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (174,0,0,21,44,0,9,14,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (175,0,0,21,44,0,9,14,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (176,0,0,21,0,1,5,15,1,'Total','','',2,9);
INSERT INTO graph_templates_item VALUES (177,0,0,21,0,0,9,15,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (178,0,0,21,0,0,9,15,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (179,0,0,21,0,0,9,15,3,'Maximum:','','on',2,12);
INSERT INTO graph_templates_item VALUES (180,0,0,22,47,31,4,0,1,'Discards In','','',2,1);
INSERT INTO graph_templates_item VALUES (181,0,0,22,47,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (182,0,0,22,47,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (183,0,0,22,47,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (184,0,0,22,46,48,4,0,1,'Errors In','','',2,5);
INSERT INTO graph_templates_item VALUES (185,0,0,22,46,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (186,0,0,22,46,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (187,0,0,22,46,0,9,0,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (188,0,0,23,49,71,4,0,1,'Unicast Packets Out','','',2,5);
INSERT INTO graph_templates_item VALUES (189,0,0,23,49,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (190,0,0,23,49,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (191,0,0,23,49,0,9,0,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (192,0,0,23,48,25,7,0,1,'Unicast Packets In','','',2,1);
INSERT INTO graph_templates_item VALUES (193,0,0,23,48,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (194,0,0,23,48,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (195,0,0,23,48,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (196,0,0,24,53,25,7,0,1,'Non-Unicast Packets In','','',2,1);
INSERT INTO graph_templates_item VALUES (197,0,0,24,53,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (198,0,0,24,53,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (199,0,0,24,53,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (200,0,0,24,52,71,4,0,1,'Non-Unicast Packets Out','','',2,5);
INSERT INTO graph_templates_item VALUES (201,0,0,24,52,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (202,0,0,24,52,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (203,0,0,24,52,0,9,0,3,'Maximum:','','on',2,8);
INSERT INTO graph_templates_item VALUES (204,0,0,22,50,18,4,0,1,'Discards Out','','',2,9);
INSERT INTO graph_templates_item VALUES (205,0,0,22,50,0,9,0,4,'Current:','','',2,10);
INSERT INTO graph_templates_item VALUES (206,0,0,22,50,0,9,0,1,'Average:','','',2,11);
INSERT INTO graph_templates_item VALUES (207,0,0,22,50,0,9,0,3,'Maximum:','','on',2,12);
INSERT INTO graph_templates_item VALUES (208,0,0,22,51,89,4,0,1,'Errors Out','','',2,13);
INSERT INTO graph_templates_item VALUES (209,0,0,22,51,0,9,0,4,'Current:','','',2,14);
INSERT INTO graph_templates_item VALUES (210,0,0,22,51,0,9,0,1,'Average:','','',2,15);
INSERT INTO graph_templates_item VALUES (211,0,0,22,51,0,9,0,3,'Maximum:','','on',2,16);
INSERT INTO graph_templates_item VALUES (212,0,0,25,54,22,7,0,1,'Inbound','','',2,1);
INSERT INTO graph_templates_item VALUES (213,0,0,25,54,0,9,0,4,'Current:','','',2,2);
INSERT INTO graph_templates_item VALUES (214,0,0,25,54,0,9,0,1,'Average:','','',2,3);
INSERT INTO graph_templates_item VALUES (215,0,0,25,54,0,9,0,3,'Maximum:','','on',2,4);
INSERT INTO graph_templates_item VALUES (216,0,0,25,55,20,4,0,1,'Outbound','','',2,5);
INSERT INTO graph_templates_item VALUES (217,0,0,25,55,0,9,0,4,'Current:','','',2,6);
INSERT INTO graph_templates_item VALUES (218,0,0,25,55,0,9,0,1,'Average:','','',2,7);
INSERT INTO graph_templates_item VALUES (219,0,0,25,55,0,9,0,3,'Maximum:','','',2,8);

--
-- Table structure for table 'graph_tree'
--

CREATE TABLE graph_tree (
  id smallint(5) NOT NULL auto_increment,
  user_id tinyint(4) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'graph_tree'
--



--
-- Table structure for table 'graph_tree_items'
--

CREATE TABLE graph_tree_items (
  id smallint(5) NOT NULL auto_increment,
  graph_tree_id smallint(5) NOT NULL default '0',
  local_graph_id mediumint(8) NOT NULL default '0',
  rra_id smallint(5) NOT NULL default '0',
  title varchar(255) default NULL,
  order_key varchar(60) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id),
  KEY graph_tree_id (graph_tree_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'graph_tree_items'
--



--
-- Table structure for table 'host'
--

CREATE TABLE host (
  id mediumint(8) NOT NULL auto_increment,
  host_template_id mediumint(8) NOT NULL default '0',
  description varchar(150) NOT NULL default '',
  hostname varchar(250) default NULL,
  management_ip varchar(15) default NULL,
  snmp_community varchar(100) default NULL,
  snmp_version tinyint(1) NOT NULL default '1',
  snmp_username varchar(50) default NULL,
  snmp_password varchar(50) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host'
--



--
-- Table structure for table 'host_snmp_cache'
--

CREATE TABLE host_snmp_cache (
  host_id mediumint(8) NOT NULL default '0',
  snmp_query_id mediumint(8) NOT NULL default '0',
  field_name varchar(50) NOT NULL default '',
  field_value varchar(255) default NULL,
  snmp_index varchar(60) NOT NULL default '',
  oid varchar(255) NOT NULL default '',
  PRIMARY KEY  (host_id,field_name,snmp_index),
  KEY host_id (host_id,field_name),
  KEY snmp_index (snmp_index)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_snmp_cache'
--



--
-- Table structure for table 'host_snmp_query'
--

CREATE TABLE host_snmp_query (
  host_id mediumint(8) NOT NULL default '0',
  snmp_query_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (host_id,snmp_query_id),
  KEY host_id (host_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_snmp_query'
--



--
-- Table structure for table 'host_template'
--

CREATE TABLE host_template (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_template'
--


INSERT INTO host_template VALUES (1,'Generic SNMP-enabled Host');
INSERT INTO host_template VALUES (3,'ucd/net SNMP Host');
INSERT INTO host_template VALUES (4,'Karlnet Wireless Bridge');
INSERT INTO host_template VALUES (5,'Cisco Router');
INSERT INTO host_template VALUES (6,'Netware 4/5 Server');

--
-- Table structure for table 'host_template_data_sv'
--

CREATE TABLE host_template_data_sv (
  host_template_id mediumint(8) NOT NULL default '0',
  data_template_id mediumint(8) NOT NULL default '0',
  graph_template_id mediumint(8) NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (host_template_id,data_template_id,graph_template_id,field_name),
  KEY host_template_id (host_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_template_data_sv'
--


INSERT INTO host_template_data_sv VALUES (6,21,15,'name','|host_description| - CPU Usage');
INSERT INTO host_template_data_sv VALUES (6,22,16,'name','|host_description| - File System Reads');
INSERT INTO host_template_data_sv VALUES (6,23,16,'name','|host_description| - File System Writes');
INSERT INTO host_template_data_sv VALUES (6,24,14,'name','|host_description| - Cache Checks');
INSERT INTO host_template_data_sv VALUES (6,25,14,'name','|host_description| - Cache Hits');
INSERT INTO host_template_data_sv VALUES (6,26,17,'name','|host_description| - Open Files');
INSERT INTO host_template_data_sv VALUES (6,20,17,'name','|host_description| - Total Logins');
INSERT INTO host_template_data_sv VALUES (5,27,18,'name','|host_description| - 5 Minute CPU');
INSERT INTO host_template_data_sv VALUES (3,6,4,'name','|host_description| - CPU Usage - Nice');
INSERT INTO host_template_data_sv VALUES (3,4,4,'name','|host_description| - CPU Usage - System');
INSERT INTO host_template_data_sv VALUES (3,30,11,'name','|host_description| - Load Average - 1 Minute');
INSERT INTO host_template_data_sv VALUES (3,32,11,'name','|host_description| - Load Average - 15 Minute');
INSERT INTO host_template_data_sv VALUES (3,31,11,'name','|host_description| - Load Average - 5 Minute');
INSERT INTO host_template_data_sv VALUES (3,33,13,'name','|host_description| - Memory - Buffers');
INSERT INTO host_template_data_sv VALUES (3,34,13,'name','|host_description| - Memory - Free');
INSERT INTO host_template_data_sv VALUES (3,5,4,'name','|host_description| - CPU Usage - User');

--
-- Table structure for table 'host_template_graph'
--

CREATE TABLE host_template_graph (
  host_template_id mediumint(8) NOT NULL default '0',
  graph_template_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (host_template_id,graph_template_id),
  KEY host_template_id (host_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_template_graph'
--


INSERT INTO host_template_graph VALUES (3,4);
INSERT INTO host_template_graph VALUES (3,11);
INSERT INTO host_template_graph VALUES (3,13);
INSERT INTO host_template_graph VALUES (5,18);
INSERT INTO host_template_graph VALUES (6,14);
INSERT INTO host_template_graph VALUES (6,15);
INSERT INTO host_template_graph VALUES (6,16);
INSERT INTO host_template_graph VALUES (6,17);

--
-- Table structure for table 'host_template_graph_sv'
--

CREATE TABLE host_template_graph_sv (
  host_template_id mediumint(8) NOT NULL default '0',
  graph_template_id mediumint(8) NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (host_template_id,graph_template_id,field_name)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_template_graph_sv'
--


INSERT INTO host_template_graph_sv VALUES (5,18,'title','|host_description| - CPU Usage');
INSERT INTO host_template_graph_sv VALUES (6,15,'title','|host_description| - CPU Usage');
INSERT INTO host_template_graph_sv VALUES (6,16,'title','|host_description| - File System Activity');
INSERT INTO host_template_graph_sv VALUES (6,14,'title','|host_description| - File System Cache');
INSERT INTO host_template_graph_sv VALUES (6,17,'title','|host_description| - User Processes');
INSERT INTO host_template_graph_sv VALUES (3,4,'title','|host_description| - CPU Usage');
INSERT INTO host_template_graph_sv VALUES (3,11,'title','|host_description| - Load Average');
INSERT INTO host_template_graph_sv VALUES (3,13,'title','|host_description| - Memory Usage');

--
-- Table structure for table 'host_template_snmp_query'
--

CREATE TABLE host_template_snmp_query (
  host_template_id mediumint(8) NOT NULL default '0',
  snmp_query_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (host_template_id,snmp_query_id),
  KEY host_template_id (host_template_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'host_template_snmp_query'
--


INSERT INTO host_template_snmp_query VALUES (1,1);
INSERT INTO host_template_snmp_query VALUES (3,1);
INSERT INTO host_template_snmp_query VALUES (3,2);
INSERT INTO host_template_snmp_query VALUES (4,1);
INSERT INTO host_template_snmp_query VALUES (4,3);
INSERT INTO host_template_snmp_query VALUES (5,1);
INSERT INTO host_template_snmp_query VALUES (6,1);
INSERT INTO host_template_snmp_query VALUES (6,4);

--
-- Table structure for table 'rra'
--

CREATE TABLE rra (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  x_files_factor double NOT NULL default '0.1',
  steps mediumint(8) default '1',
  rows int(12) NOT NULL default '600',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'rra'
--


INSERT INTO rra VALUES (1,'Daily (5 Minute Average)',0.5,1,600);
INSERT INTO rra VALUES (2,'Weekly (30 Minute Average)',0.5,6,700);
INSERT INTO rra VALUES (4,'Yearly (1 Day Average)',0.5,288,797);
INSERT INTO rra VALUES (3,'Monthly (2 Hour Average)',0.5,24,775);

--
-- Table structure for table 'rra_cf'
--

CREATE TABLE rra_cf (
  rra_id mediumint(8) NOT NULL default '0',
  consolidation_function_id smallint(5) NOT NULL default '0',
  PRIMARY KEY  (rra_id,consolidation_function_id),
  KEY rra_id (rra_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'rra_cf'
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
-- Table structure for table 'settings'
--

CREATE TABLE settings (
  name varchar(50) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (name),
  UNIQUE KEY Name (name),
  KEY name_2 (name)
) TYPE=MyISAM;

--
-- Dumping data for table 'settings'
--


INSERT INTO settings VALUES ('path_webcacti','/cacti/cacti-0.8');
INSERT INTO settings VALUES ('path_webroot','/var/www/html/users/iberry');
INSERT INTO settings VALUES ('path_snmpwalk','/usr/local/bin/snmpwalk');
INSERT INTO settings VALUES ('path_rrdtool','/usr/local/rrdtool/bin/rrdtool');
INSERT INTO settings VALUES ('log','');
INSERT INTO settings VALUES ('log_graph','');
INSERT INTO settings VALUES ('log_create','on');
INSERT INTO settings VALUES ('log_update','on');
INSERT INTO settings VALUES ('log_snmp','on');
INSERT INTO settings VALUES ('full_view_data_source','on');
INSERT INTO settings VALUES ('global_auth','on');
INSERT INTO settings VALUES ('path_snmpget','/usr/local/bin/snmpget');
INSERT INTO settings VALUES ('path_html_export','');
INSERT INTO settings VALUES ('guest_user','guest');
INSERT INTO settings VALUES ('path_html_export_skip','1');
INSERT INTO settings VALUES ('path_html_export_ctr','');
INSERT INTO settings VALUES ('remove_verification','on');
INSERT INTO settings VALUES ('use_polling_zones','on');
INSERT INTO settings VALUES ('full_view_graph_template','on');
INSERT INTO settings VALUES ('full_view_graph','on');
INSERT INTO settings VALUES ('full_view_user_admin','on');
INSERT INTO settings VALUES ('full_view_data_template','on');
INSERT INTO settings VALUES ('smnp_version','ucd-snmp');
INSERT INTO settings VALUES ('ldap_enabled','');
INSERT INTO settings VALUES ('ldap_server','');
INSERT INTO settings VALUES ('ldap_dn','');
INSERT INTO settings VALUES ('ldap_template','');

--
-- Table structure for table 'settings_graphs'
--

CREATE TABLE settings_graphs (
  user_id smallint(8) NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (user_id,name),
  KEY user_id (user_id,name)
) TYPE=MyISAM;

--
-- Dumping data for table 'settings_graphs'
--



--
-- Table structure for table 'settings_tree'
--

CREATE TABLE settings_tree (
  user_id mediumint(8) NOT NULL default '0',
  graph_tree_item_id mediumint(8) NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (user_id,graph_tree_item_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'settings_tree'
--



--
-- Table structure for table 'snmp_query'
--

CREATE TABLE snmp_query (
  id mediumint(8) NOT NULL auto_increment,
  xml_path varchar(255) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  description varchar(255) default NULL,
  graph_template_id mediumint(8) NOT NULL default '0',
  data_input_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query'
--


INSERT INTO snmp_query VALUES (1,'<path_cacti>/resource/snmp_queries/interface.xml','SNMP - Interface Statistics','Queries a host for a list of monitorable interfaces',0,2);
INSERT INTO snmp_query VALUES (2,'<path_cacti>/resource/snmp_queries/net-snmp_disk.xml','ucd/net -  Get Monitored Partitions','Retrieves a list of monitored partitions/disks from a net-snmp enabled host.',0,2);
INSERT INTO snmp_query VALUES (3,'<path_cacti>/resource/snmp_queries/kbridge.xml','Karlnet - Wireless Bridge Statistics','Gets information about the wireless connectivity of each station from a Karlnet bridge.',0,2);
INSERT INTO snmp_query VALUES (4,'<path_cacti>/resource/snmp_queries/netware_disk.xml','Netware - Get Available Volumes','Retrieves a list of volumes from a Netware server.',0,2);
INSERT INTO snmp_query VALUES (6,'<path_cacti>/resource/script_queries/unix_disk.xml','Unix - Get Mounted Partitions','Queries a list of mounted partitions on a unix-based host with the',0,11);

--
-- Table structure for table 'snmp_query_field'
--

CREATE TABLE snmp_query_field (
  snmp_query_id mediumint(8) NOT NULL default '0',
  data_input_field_id mediumint(8) NOT NULL default '0',
  action_id tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (snmp_query_id,data_input_field_id),
  KEY snmp_query_id (snmp_query_id),
  KEY data_input_field_id (data_input_field_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query_field'
--


INSERT INTO snmp_query_field VALUES (1,14,3);
INSERT INTO snmp_query_field VALUES (1,13,2);
INSERT INTO snmp_query_field VALUES (1,12,1);
INSERT INTO snmp_query_field VALUES (2,14,3);
INSERT INTO snmp_query_field VALUES (2,13,2);
INSERT INTO snmp_query_field VALUES (2,12,1);
INSERT INTO snmp_query_field VALUES (3,14,3);
INSERT INTO snmp_query_field VALUES (3,13,2);
INSERT INTO snmp_query_field VALUES (3,12,1);
INSERT INTO snmp_query_field VALUES (4,14,3);
INSERT INTO snmp_query_field VALUES (4,13,2);
INSERT INTO snmp_query_field VALUES (4,12,1);
INSERT INTO snmp_query_field VALUES (6,33,3);
INSERT INTO snmp_query_field VALUES (6,32,2);
INSERT INTO snmp_query_field VALUES (6,31,1);

--
-- Table structure for table 'snmp_query_graph'
--

CREATE TABLE snmp_query_graph (
  id mediumint(8) NOT NULL auto_increment,
  snmp_query_id mediumint(8) NOT NULL default '0',
  name varchar(100) NOT NULL default '',
  graph_template_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query_graph'
--


INSERT INTO snmp_query_graph VALUES (1,1,'In/Out Bytes (0.6.x Style)',1);
INSERT INTO snmp_query_graph VALUES (2,1,'In/Out Errors/Discarded Packets',22);
INSERT INTO snmp_query_graph VALUES (3,1,'In/Out Non-Unicast Packets',24);
INSERT INTO snmp_query_graph VALUES (4,1,'In/Out Unicast Packets',23);
INSERT INTO snmp_query_graph VALUES (15,6,'Available Disk Space',21);
INSERT INTO snmp_query_graph VALUES (6,2,'Available/Used Disk Space',3);
INSERT INTO snmp_query_graph VALUES (7,3,'Wireless Levels',5);
INSERT INTO snmp_query_graph VALUES (8,3,'Wireless Transmissions',6);
INSERT INTO snmp_query_graph VALUES (9,1,'In/Out Bytes (64-bit Counters)',25);
INSERT INTO snmp_query_graph VALUES (10,4,'Volume Information (free, freeable space)',19);
INSERT INTO snmp_query_graph VALUES (11,4,'Directory Information (total/available entries)',20);
INSERT INTO snmp_query_graph VALUES (13,1,'In/Out Bits',2);
INSERT INTO snmp_query_graph VALUES (14,1,'In/Out Bits (64-bit Counters)',2);
INSERT INTO snmp_query_graph VALUES (16,1,'In/Out Bytes',25);

--
-- Table structure for table 'snmp_query_graph_rrd'
--

CREATE TABLE snmp_query_graph_rrd (
  snmp_query_graph_id mediumint(8) NOT NULL default '0',
  data_template_id mediumint(8) NOT NULL default '0',
  data_template_rrd_id mediumint(8) NOT NULL default '0',
  snmp_field_name varchar(50) NOT NULL default '0',
  PRIMARY KEY  (snmp_query_graph_id,data_template_id,data_template_rrd_id),
  KEY snmp_query_graph_id (snmp_query_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query_graph_rrd'
--


INSERT INTO snmp_query_graph_rrd VALUES (1,2,2,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (2,38,47,'ifInDiscards');
INSERT INTO snmp_query_graph_rrd VALUES (3,40,52,'ifOutNUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (3,40,53,'ifInNUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (4,39,48,'ifInUcastPkts');
INSERT INTO snmp_query_graph_rrd VALUES (2,38,51,'ifOutErrors');
INSERT INTO snmp_query_graph_rrd VALUES (6,3,3,'dskAvail');
INSERT INTO snmp_query_graph_rrd VALUES (6,3,4,'dskUsed');
INSERT INTO snmp_query_graph_rrd VALUES (7,7,8,'kbWirelessStationExclHellos');
INSERT INTO snmp_query_graph_rrd VALUES (7,8,9,'kbWirelessStationExclHellos');
INSERT INTO snmp_query_graph_rrd VALUES (8,10,11,'kbWirelessStationExclHellos');
INSERT INTO snmp_query_graph_rrd VALUES (8,9,10,'kbWirelessStationExclHellos');
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
INSERT INTO snmp_query_graph_rrd VALUES (1,1,1,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (15,37,44,'dskAvailable');
INSERT INTO snmp_query_graph_rrd VALUES (16,41,54,'ifInOctets');
INSERT INTO snmp_query_graph_rrd VALUES (16,41,55,'ifOutOctets');
INSERT INTO snmp_query_graph_rrd VALUES (15,37,56,'dskUsed');

--
-- Table structure for table 'snmp_query_graph_rrd_sv'
--

CREATE TABLE snmp_query_graph_rrd_sv (
  id mediumint(8) NOT NULL auto_increment,
  snmp_query_graph_id mediumint(8) NOT NULL default '0',
  data_template_id mediumint(8) NOT NULL default '0',
  sequence mediumint(8) NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY id (id),
  KEY id_2 (id),
  KEY snmp_query_graph_id (snmp_query_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query_graph_rrd_sv'
--


INSERT INTO snmp_query_graph_rrd_sv VALUES (1,1,1,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (2,1,1,2,'name','|host_description| - Traffic - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (3,1,1,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (4,1,1,4,'name','|host_description| - Traffic - |squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (5,1,2,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias| (Out)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (6,1,2,2,'name','|host_description| - Traffic - |squery_ifAlias| (Out)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (7,1,2,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc| (Out)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (8,1,2,4,'name','|host_description| - Traffic - |squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (10,6,3,1,'name','|host_description| - Partition - |squery_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (11,7,7,1,'name','|host_description| - Wireless Noise Level - |squery_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (12,7,8,1,'name','|host_description| - Wireless Signal Level - |squery_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (13,8,10,1,'name','|host_description| - Wireless Re-Transmissions - |squery_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (14,8,9,1,'name','|host_description| - Wireless Transmissions - |squery_kbWirelessStationName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (88,9,41,2,'name','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (86,9,41,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (82,14,41,2,'name','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (81,14,41,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (23,1,1,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (24,1,2,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (79,16,41,4,'name','|host_description| - Traffic - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (30,11,36,1,'name','|host_description| - Directories - |squery_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (29,10,35,1,'name','|host_description| - Volumes - |squery_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (80,16,41,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (85,14,41,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (84,14,41,4,'name','|host_description| - Traffic - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (83,14,41,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (32,12,37,1,'name','|host_description| - Partition - |squery_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (33,13,1,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (34,13,1,2,'name','|host_description| - Traffic - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (35,13,1,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (36,13,1,4,'name','|host_description| - Traffic - |squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (78,16,41,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (77,16,41,2,'name','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (76,16,41,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (75,13,41,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (41,14,1,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias| (In-64)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (42,14,1,2,'name','|host_description| - Traffic - |squery_ifAlias| (In-64)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (43,14,1,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc| (In-64)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (44,14,1,4,'name','|host_description| - Traffic - |squery_ifDesc| (In-64)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (74,13,41,4,'name','|host_description| - Traffic - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (72,13,41,2,'name','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (73,13,41,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (49,2,38,1,'name','|host_description| - Errors - |squery_ifIP| - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (50,2,38,2,'name','|host_description| - Errors - |squery_ifAlias| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (51,2,38,3,'name','|host_description| - Errors - |squery_ifIP|/|squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (52,2,38,4,'name','|host_description| - Errors - |squery_ifDesc| (In)');
INSERT INTO snmp_query_graph_rrd_sv VALUES (54,3,40,1,'name','|host_description| - Non-Unicast Packets - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (55,3,40,2,'name','|host_description| - Non-Unicast Packets - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (56,3,40,3,'name','|host_description| - Non-Unicast Packets - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (57,3,40,4,'name','|host_description| - Non-Unicast Packets - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (59,4,39,1,'name','|host_description| - Unicast Packets - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (60,4,39,2,'name','|host_description| - Unicast Packets - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (61,4,39,3,'name','|host_description| - Unicast Packets - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (62,4,39,4,'name','|host_description| - Unicast Packets - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (63,13,1,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (65,14,1,1,'rrd_maximum','|squery_ifSpeed|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (70,13,41,1,'name','|host_description| - Traffic - |squery_ifIP| - |squery_ifAlias|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (69,15,37,1,'name','|host_description| - Free Space - |squery_dskDevice|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (89,9,41,3,'name','|host_description| - Traffic - |squery_ifIP|/|squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (90,9,41,4,'name','|host_description| - Traffic - |squery_ifDesc|');
INSERT INTO snmp_query_graph_rrd_sv VALUES (91,9,41,1,'rrd_maximum','|squery_ifSpeed|');

--
-- Table structure for table 'snmp_query_graph_sv'
--

CREATE TABLE snmp_query_graph_sv (
  id mediumint(8) NOT NULL auto_increment,
  snmp_query_graph_id mediumint(8) NOT NULL default '0',
  sequence mediumint(8) NOT NULL default '0',
  field_name varchar(100) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY id (id),
  KEY snmp_query_graph_id (snmp_query_graph_id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'snmp_query_graph_sv'
--


INSERT INTO snmp_query_graph_sv VALUES (1,1,1,'title','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (2,1,2,'title','|host_description| - Traffic - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (3,1,3,'title','|host_description| - Traffic - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (7,6,1,'title','|host_description| - Disk Space - |squery_dskPath|');
INSERT INTO snmp_query_graph_sv VALUES (5,7,1,'title','|host_description| - Wireless Levels (|squery_kbWirelessStationName|)');
INSERT INTO snmp_query_graph_sv VALUES (6,8,1,'title','|host_description| - Wireless Transmissions (|squery_kbWirelessStationName|)');
INSERT INTO snmp_query_graph_sv VALUES (33,16,3,'title','|host_description| - Traffic - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (32,16,2,'title','|host_description| - Traffic - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (31,16,1,'title','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (11,10,1,'title','|host_description| - Volume Information - |squery_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_sv VALUES (12,11,1,'title','|host_description| - Directory Information - |squery_nwVolPhysicalName|');
INSERT INTO snmp_query_graph_sv VALUES (14,12,1,'title','|host_description| - Disk Space - |squery_dskDevice|');
INSERT INTO snmp_query_graph_sv VALUES (15,13,1,'title','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (16,13,2,'title','|host_description| - Traffic - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (17,13,3,'title','|host_description| - Traffic - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (18,14,1,'title','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (19,14,2,'title','|host_description| - Traffic - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (20,14,3,'title','|host_description| - Traffic - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (21,2,1,'title','|host_description| - Errors - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (22,2,2,'title','|host_description| - Errors - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (23,2,3,'title','|host_description| - Errors - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (24,3,1,'title','|host_description| - Non-Unicast Packets - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (25,3,2,'title','|host_description| - Non-Unicast Packets - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (26,3,3,'title','|host_description| - Non-Unicast Packets - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (27,4,1,'title','|host_description| - Unicast Packets - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (28,4,2,'title','|host_description| - Unicast Packets - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (29,4,3,'title','|host_description| - Unicast Packets - |squery_ifDesc|/|squery_ifIndex|');
INSERT INTO snmp_query_graph_sv VALUES (30,15,1,'title','|host_description| - Disk Space - |squery_dskDevice|');
INSERT INTO snmp_query_graph_sv VALUES (34,9,1,'title','|host_description| - Traffic - |squery_ifAlias|');
INSERT INTO snmp_query_graph_sv VALUES (35,9,2,'title','|host_description| - Traffic - |squery_ifIP| (|squery_ifDesc|)');
INSERT INTO snmp_query_graph_sv VALUES (36,9,3,'title','|host_description| - Traffic - |squery_ifDesc|/|squery_ifIndex|');

--
-- Table structure for table 'user_auth'
--

CREATE TABLE user_auth (
  id mediumint(8) NOT NULL auto_increment,
  username varchar(50) NOT NULL default '0',
  password varchar(50) NOT NULL default '0',
  full_name varchar(100) default '0',
  must_change_password char(2) default NULL,
  show_tree char(2) default 'on',
  show_list char(2) default 'on',
  show_preview char(2) NOT NULL default 'on',
  graph_settings char(2) default NULL,
  login_opts tinyint(1) NOT NULL default '1',
  graph_policy tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_auth'
--


INSERT INTO user_auth VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3','Administrator','','on','on','on','on',1,1);
INSERT INTO user_auth VALUES (3,'guest','43e9a4ab75570f5b','Guest Account','on','on','on','on','on',3,1);

--
-- Table structure for table 'user_auth_graph'
--

CREATE TABLE user_auth_graph (
  user_id mediumint(8) NOT NULL default '0',
  local_graph_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (user_id,local_graph_id),
  KEY user_id (user_id,local_graph_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_auth_graph'
--



--
-- Table structure for table 'user_auth_hosts'
--

CREATE TABLE user_auth_hosts (
  user_id mediumint(8) NOT NULL default '0',
  hostname varchar(100) NOT NULL default '',
  policy tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (user_id,hostname),
  KEY user_id (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_auth_hosts'
--



--
-- Table structure for table 'user_auth_realm'
--

CREATE TABLE user_auth_realm (
  realm_id mediumint(8) NOT NULL default '0',
  user_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (realm_id,user_id),
  KEY user_id (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_auth_realm'
--


INSERT INTO user_auth_realm VALUES (1,1);
INSERT INTO user_auth_realm VALUES (2,1);
INSERT INTO user_auth_realm VALUES (3,1);
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

--
-- Table structure for table 'user_auth_tree'
--

CREATE TABLE user_auth_tree (
  user_id mediumint(8) NOT NULL default '0',
  tree_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (user_id,tree_id),
  KEY user_id (user_id,tree_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_auth_tree'
--



--
-- Table structure for table 'user_log'
--

CREATE TABLE user_log (
  username varchar(50) NOT NULL default '0',
  time timestamp(14) NOT NULL,
  result tinyint(1) NOT NULL default '0',
  ip varchar(15) NOT NULL default '',
  PRIMARY KEY  (username,time)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_log'
--



--
-- Table structure for table 'user_realm'
--

CREATE TABLE user_realm (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(50) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY ID (id),
  KEY id_2 (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_realm'
--


INSERT INTO user_realm VALUES (1,'User Administration');
INSERT INTO user_realm VALUES (2,'Data Input');
INSERT INTO user_realm VALUES (3,'Update Data Sources');
INSERT INTO user_realm VALUES (5,'Update Graphs');
INSERT INTO user_realm VALUES (7,'View Graphs');
INSERT INTO user_realm VALUES (8,'Console Access');
INSERT INTO user_realm VALUES (9,'Update Round Robin Archives');
INSERT INTO user_realm VALUES (10,'Update Graph Templates');
INSERT INTO user_realm VALUES (11,'Update Data Templates');
INSERT INTO user_realm VALUES (12,'Update Host Templates');
INSERT INTO user_realm VALUES (13,'SNMP Queries');
INSERT INTO user_realm VALUES (14,'Update CDEF\'s');
INSERT INTO user_realm VALUES (15,'Global Settings');

--
-- Table structure for table 'user_realm_filename'
--

CREATE TABLE user_realm_filename (
  realm_id smallint(5) NOT NULL default '0',
  filename varchar(100) NOT NULL default '',
  PRIMARY KEY  (realm_id,filename),
  KEY realm_id (realm_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_realm_filename'
--


INSERT INTO user_realm_filename VALUES (1,'user_admin.php');
INSERT INTO user_realm_filename VALUES (2,'data_input.php');
INSERT INTO user_realm_filename VALUES (3,'data_sources.php');
INSERT INTO user_realm_filename VALUES (3,'host.php');
INSERT INTO user_realm_filename VALUES (5,'color.php');
INSERT INTO user_realm_filename VALUES (5,'gprint_presets.php');
INSERT INTO user_realm_filename VALUES (5,'graphs.php');
INSERT INTO user_realm_filename VALUES (5,'tree.php');
INSERT INTO user_realm_filename VALUES (7,'graph.php');
INSERT INTO user_realm_filename VALUES (7,'graph_image.php');
INSERT INTO user_realm_filename VALUES (7,'graph_settings.php');
INSERT INTO user_realm_filename VALUES (7,'graph_view.php');
INSERT INTO user_realm_filename VALUES (8,'about.php');
INSERT INTO user_realm_filename VALUES (8,'index.php');
INSERT INTO user_realm_filename VALUES (9,'rra.php');
INSERT INTO user_realm_filename VALUES (10,'graph_templates.php');
INSERT INTO user_realm_filename VALUES (11,'data_templates.php');
INSERT INTO user_realm_filename VALUES (12,'host_templates.php');
INSERT INTO user_realm_filename VALUES (13,'snmp.php');
INSERT INTO user_realm_filename VALUES (14,'cdef.php');
INSERT INTO user_realm_filename VALUES (15,'settings.php');
INSERT INTO user_realm_filename VALUES (15,'utilities.php');

--
-- Table structure for table 'version'
--

CREATE TABLE version (
  cacti char(15) default NULL
) TYPE=MyISAM;

--
-- Dumping data for table 'version'
--


INSERT INTO version VALUES ('0.8');

