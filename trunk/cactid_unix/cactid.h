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
#define CONFIG1 "cactid.conf"

/* Defaults */
#define DEFAULT_THREADS 5
#define DEFAULT_INTERVAL 300
#define DEFAULT_OUT_OF_RANGE 93750000000
#define DEFAULT_DB_HOST "localhost"
#define DEFAULT_DB_DB "cacti"
#define DEFAULT_DB_USER "cactiuser"
#define DEFAULT_DB_PASS "cactiuser"
#define DEFAULT_SNMP_VER 1

/* Verbosity levels LOW=info HIGH=info+SQL DEBUG=info+SQL+junk */
#define LOW 1
#define HIGH 2
#define DEBUG 3
#define DEVELOP 4

#define LOCK_CREW 0
#define LOCK_THREAD 1
#define LOCK_MYSQL 2
#define LOCK_RRDTOOL 3

#define STAT_DESCRIP_ERROR 99

/* Typedefs */
typedef struct worker_struct {
    int index;
    pthread_t thread;
    struct crew_struct *crew;
} worker_t;

typedef struct config_struct {
    int interval;
    long long out_of_range;
    char dbhost[80];
    char dbdb[80];
    char dbuser[80];
    char dbpass[80];
    int verbose;
    int dboff;
    int snmp_ver;
    int threads;
} config_t;

typedef struct target_struct{
  int target_id;
  char result[255];
  int local_data_id;
  int rrd_num;
  int action;
  char command[256];
  char management_ip[16];
  char snmp_community[100];
  int snmp_version;
  char snmp_username[50];
  char snmp_password[50];
  char rrd_name[30];
  char rrd_path[255];
  char arg1[255];
  char arg2[255];
  char arg3[255];
  struct target_struct *next;
  struct target_struct *prev;
  struct target_struct *head;
}target_t;

typedef struct crew_struct {
    int work_count;
    worker_t member[MAX_THREADS];
    pthread_mutex_t mutex;
    pthread_cond_t done;
    pthread_cond_t go;
} crew_t;

typedef struct rrd_struct{
  char rrdcmd[512];
}rrd_t;

typedef struct host_struct {
  int host_id;
  int status;
}host_t;

typedef struct multi_rrd_struct{
  char rrd_name[19];
  char rrd_path[255];
  char result[255];
}multi_rrd_t;

/* Precasts: cactid.c */
int get_host_list();
int get_targets();

/* Precasts: poller.c */
void *poller(void *);
char *snmp_get(char *snmp_host, char *snmp_comm, int ver, char *snmp_oid, int host_id);
int get_host_status(int host_id);
void set_host_status(int host_id, int new_status);

/* Precasts: mysql.c */
int db_insert(char *query, MYSQL *mysql);
MYSQL_RES *db_query(MYSQL *mysql, char *query);
int db_connect(char *database, MYSQL *mysql);
void db_disconnect(MYSQL *mysql);

/* Precasts: util.c */
int init_config(char *, config_t *);
void config_defaults(config_t *);
void timestamp(char *);
int file_exists(char *filename);
int is_number(char *string);

/* Precasts: locks.c */
void mutex_lock(int);
void mutex_unlock(int);
int mutex_trylock(int mutex);
pthread_mutex_t* get_lock(int lock);

/* Globals */
config_t set;
int lock;
int waiting;

#endif /* not _CACTID_H_ */
