/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2003 Ian Berry                                            |
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
 | cactid: a backend data gatherer for cacti                               |
 +-------------------------------------------------------------------------+
 | This poller would not have been possible without:                       |
 |   - Rivo Nurges (rrd support, mysql poller cache, misc functions)       |
 |   - RTG (core poller code, pthreads, snmp, autoconf examples)           |
 |   - Brady Alleman/Doug Warner (threading ideas, implimentation details) |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

#ifndef _CACTID_H_
#define _CACTID_H_ 1

/* Defines */
#ifndef FALSE
# define FALSE 0
#endif
#ifndef TRUE
# define TRUE !FALSE
#endif

/* Constants */
#define MAX_THREADS 100
#define BUFSIZE 512
#define BITSINBYTE 8
#define THIRTYTWO 4294967295ul
#define SIXTYFOUR 18446744073709551615ul

#define CONFIG_PATHS 2
#define CONFIG_PATH_1 ""
#define CONFIG_PATH_2 "/etc/"

/* Defaults */
#define DEFAULT_CONF_FILE "cactid.conf"
#define DEFAULT_THREADS 5
#define DEFAULT_INTERVAL 300
#define DEFAULT_OUT_OF_RANGE 93750000000
#define DEFAULT_DB_HOST "localhost"
#define DEFAULT_DB_DB "cacti"
#define DEFAULT_DB_USER "cactiuser"
#define DEFAULT_DB_PASS "cactiuser"
#define DEFAULT_Log_File "/wwwroot/cacti/log/rrd.log"
#define DEFAULT_SNMP_VER 1

/* Verbosity levels LOW=info HIGH=info+SQL DEBUG=info+SQL+junk */
#define LOW 1
#define HIGH 2
#define DEBUG 3
#define DEVELOP 4

#define LOCK_SNMP 0
#define LOCK_THREAD 1
#define LOCK_MYSQL 2
#define LOCK_RRDTOOL 3
#define LOCK_PIPE 4
#define LOCK_SYSLOG 5

#define LOCK_SNMP_O 0
#define LOCK_THREAD_O 1
#define LOCK_MYSQL_O 2
#define LOCK_RRDTOOL_O 3
#define LOCK_PIPE_O 4
#define LOCK_SYSLOG_O 5

#define STAT_DESCRIP_ERROR 99

/* Typedefs */
typedef struct config_struct {
	int interval;
	long out_of_range;
	char dbhost[80];
	char dbdb[80];
	char dbuser[80];
	char dbpass[80];
	char logfile[250];
	char phppath[250];
	int log_destination;
	int log_perror;
	int log_pstats;
	int verbose;
	int dboff;
	int snmp_ver;
	int threads;
} config_t;

typedef struct target_struct {
	int target_id;
	char result[512];
	int local_data_id;
	int rrd_num;
	int action;
	char command[256];
	char hostname[250];
	char snmp_community[100];
	int snmp_version;
	char snmp_username[50];
	char snmp_password[50];
	int snmp_port;
	int snmp_timeout;
	char rrd_name[30];
	char rrd_path[255];
	char arg1[255];
	char arg2[255];
	char arg3[255];
} target_t;

typedef struct host_struct {
	char hostname[250];
	char snmp_community[100];
	int snmp_version;
	int snmp_port;
	int snmp_timeout;
	int ignore_host;
 	void *snmp_session;
} host_t;

/* Globals */
config_t set;
char config_paths[CONFIG_PATHS][BUFSIZE];

#endif /* not _CACTID_H_ */
