/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2002-2005 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU Lesser General Public              |
 | License as published by the Free Software Foundation; either            |
 | version 2.1 of the License, or (at your option) any later version. 	   |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU Lesser General Public License for more details.                     |
 |                                                                         | 
 | You should have received a copy of the GNU Lesser General Public        |
 | License along with this library; if not, write to the Free Software     |
 | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA           |
 | 02110-1301, USA                                                         |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | cactid: a backend data gatherer for cacti                               |
 +-------------------------------------------------------------------------+
 | This poller would not have been possible without:                       |
 |   - Larry Adams (current development and enhancements)                  |
 |   - Rivo Nurges (rrd support, mysql poller cache, misc functions)       |
 |   - RTG (core poller code, pthreads, snmp, autoconf examples)           |
 |   - Brady Alleman/Doug Warner (threading ideas, implimentation details) |
 +-------------------------------------------------------------------------+
 | - Cacti - http://www.cacti.net/                                         |
 +-------------------------------------------------------------------------+
*/

#ifndef _CACTID_H_
#define _CACTID_H_

/* Defines */
#ifndef FALSE
#define FALSE 0
#endif
#ifndef TRUE
#define TRUE 1
#endif

#ifndef __GNUC__
# define __attribute__(x)  /* NOTHING */
#endif

/* if a host is legal, return TRUE */
#define HOSTID_DEFINED(x)	((x) >= 0)

/* warning-suppression macros
 *
 * There are times when we cannot avoid using a parameter or variable which
 * is not used, and these correctly generate compiler warnings. But when we
 * *know* that the variable is actually intended to be unused, we can use one
 * of these macros inside the function to suppress it. This has the effect
 * of suppressing the warning (a good thing), plus documenting to the reader
 * that this is intentional.
 *
 * Both do the same thing - they're just for different semantics.
 */

#define UNUSED_VARIABLE(p)      (void)(p)
#define UNUSED_PARAMETER(p)     (void)(p)


/* logging macros
 *
 * These all perform conditional logging based on the current runtime logging
 * level, and it relies on a bit of tricky (but entirely portable) preprocessor
 * techniques.
 *
 * Standard C does not support variadic macros (macros with a variable number
 * of parameters), and though GNU C does, it's not at all portable. So we instead
 * rely on the fact that putting parens around something turn multiple params
 * into one:
 *
 *	CACTID_LOG_DEBUG(("n=%d string=%s foo=%f", n, string, foo));
 *
 * This macros has *one* parameter:
 *
 *		("n=%d string=%s foo=%f", n, string, foo)
 *
 * and the parentheses are part of it. When we call this macro, we pass the
 * "single" parameter unadorned, so that
 *
 *		cacti_log args
 *
 * expands to
 *
 *		cacti_log ("n=%d string=%s foo=%f", n, string, foo)
 *
 * Voila: it's a normal printf-like call.
 *
 * The second part of this is the conditional test, and the obvious approach
 * of using an "if" statement is exceptionally bad form: there are all kinds
 * of pitfalls which arise in this case. Instead, we should try to use an
 * *expression*, which has none of these problems.
 *
 * The conditional tests are modelled after the assert() mechanism, which
 * checks the first parameter, and if it's true, it evaluates the second
 * paramater. If the test is not true, then the second part is *guaranteed*
 * not to be evaluated.
 *
 * The (void) prefix is to forestall compiler warnings about expressions
 * not being used.
 */
#define CACTID_LOG(format_and_args)        (cacti_log format_and_args)
#define CACTID_LOG_LOW(format_and_args)    (void)(set.log_level >= POLLER_VERBOSITY_LOW && cacti_log format_and_args)
#define CACTID_LOG_MEDIUM(format_and_args) (void)(set.log_level >= POLLER_VERBOSITY_MEDIUM && cacti_log format_and_args)
#define CACTID_LOG_HIGH(format_and_args)   (void)(set.log_level >= POLLER_VERBOSITY_HIGH && cacti_log format_and_args)
#define CACTID_LOG_DEBUG(format_and_args)  (void)(set.log_level >= POLLER_VERBOSITY_DEBUG && cacti_log format_and_args)

/* general constants */
#define MAX_THREADS 100
#define BUFSIZE 1024
#define LOGSIZE 4096
#define BITSINBYTE 8
#define THIRTYTWO 4294967295ul
#define SIXTYFOUR 18446744073709551615ul
#define STAT_DESCRIP_ERROR 99
#define CACTID_PARENT 1
#define CACTID_FORK 0
#define MAX_MYSQL_BUF_SIZE 1400

/* locations to search for the config file */
#define CONFIG_PATHS 2
#define CONFIG_PATH_1 ""
#define CONFIG_PATH_2 "/etc/"

/* config file defaults */
#define DEFAULT_CONF_FILE "cactid.conf"
#define DEFAULT_THREADS 5
#define DEFAULT_DB_HOST "localhost"
#define DEFAULT_DB_DB "cacti"
#define DEFAULT_DB_USER "cactiuser"
#define DEFAULT_DB_PASS "cactiuser"
#define DEFAULT_DB_PORT 3306
#define DEFAULT_LOGFILE "/wwwroot/cacti/log/cactid.log"
#define DEFAULT_TIMEOUT 294000000

/* threads constants */
#define LOCK_SNMP 0
#define LOCK_THREAD 1
#define LOCK_MYSQL 2
#define LOCK_GHBN 3
#define LOCK_PIPE 4
#define LOCK_SYSLOG 5
#define LOCK_PHP 6
#define LOCK_PHP_PROC_0 7
#define LOCK_PHP_PROC_1 8
#define LOCK_PHP_PROC_2 9
#define LOCK_PHP_PROC_3 10
#define LOCK_PHP_PROC_4 11
#define LOCK_PHP_PROC_5 12
#define LOCK_PHP_PROC_6 13
#define LOCK_PHP_PROC_7 14
#define LOCK_PHP_PROC_8 15
#define LOCK_PHP_PROC_9 16

#define LOCK_SNMP_O 0
#define LOCK_THREAD_O 1
#define LOCK_MYSQL_O 2
#define LOCK_GHBN_O 3
#define LOCK_PIPE_O 4
#define LOCK_SYSLOG_O 5
#define LOCK_PHP_O 6
#define LOCK_PHP_PROC_0_O 7
#define LOCK_PHP_PROC_1_O 8
#define LOCK_PHP_PROC_2_O 9
#define LOCK_PHP_PROC_3_O 10
#define LOCK_PHP_PROC_4_O 11
#define LOCK_PHP_PROC_5_O 12
#define LOCK_PHP_PROC_6_O 13
#define LOCK_PHP_PROC_7_O 14
#define LOCK_PHP_PROC_8_O 15
#define LOCK_PHP_PROC_9_O 16

/* poller actions */
#define POLLER_ACTION_SNMP 0
#define POLLER_ACTION_SCRIPT 1
#define POLLER_ACTION_PHP_SCRIPT_SERVER 2

/* reindex constants */
#define POLLER_COMMAND_REINDEX 1

/* log destinations */
#define LOGDEST_FILE   1
#define LOGDEST_BOTH   2
#define LOGDEST_SYSLOG 3
#define LOGDEST_STDOUT 4

#define IS_LOGGING_TO_FILE()   ((set.log_destination) == LOGDEST_FILE   || (set.log_destination) == LOGDEST_BOTH)
#define IS_LOGGING_TO_SYSLOG() ((set.log_destination) == LOGDEST_SYSLOG || (set.log_destination) == LOGDEST_BOTH)
#define IS_LOGGING_TO_STDOUT() ((set.log_destination) == LOGDEST_STDOUT )

/* logging levels */
#define POLLER_VERBOSITY_NONE 1
#define POLLER_VERBOSITY_LOW 2
#define POLLER_VERBOSITY_MEDIUM 3
#define POLLER_VERBOSITY_HIGH 4
#define POLLER_VERBOSITY_DEBUG 5

/* host availability statics */
#define AVAIL_SNMP_AND_PING 1
#define AVAIL_SNMP 2
#define AVAIL_PING 3

#define PING_ICMP 1
#define PING_UDP 2

#define HOST_UNKNOWN 0
#define HOST_DOWN 1
#define HOST_RECOVERING 2
#define HOST_UP 3

/* required for ICMP and UDP ping */
#define ICMP_ECHO 8
#define ICMP_HDR_SIZE 8

/* required for PHP Script Server */
#define MAX_PHP_SERVERS 10
#define PHP_READY 0
#define PHP_BUSY 1
#define PHP_INIT 999
#define PHP_ERROR 99

/* required for validation of script results */
#define RESULT_INIT 0
#define RESULT_ARGX 1
#define RESULT_VALX 2
#define RESULT_SEPARATOR 3
#define RESULT_SPACE 4
#define RESULT_ALPHA 5
#define RESULT_DIGIT 6

/* snmp session status */
#define SNMP_1 0
#define SNMP_2c 1
#define SNMP_3 3
#define SNMP_NONE 4

/* These are used to perform string matches, returning TRUE/VALUE values.
 * For strcmp() this is not really that useful, but the case-insensitive
 * one has slight portability issues. Better to abstract them here.
 */
#define STRMATCH(a,b)	(strcmp((a),(b)) == 0)
#define STRIMATCH(a,b)	(strcasecmp((a),(b)) == 0)


/* convert timeval to double (rounding to the nearest microsecond) */
#define TIMEVAL_TO_DOUBLE(tv)	( (tv).tv_sec + ((double) (tv).tv_usec / 1000000))

/* When any kind of poller wants to set an undefined value; this particular
 * value used ('U') springs from the requirements of rrdupdate. We also
 * include the corresponding test macro which looks for the literal string
 * "U". This *could* use strcmp(), but this is more efficient.
 */
#define SET_UNDEFINED(buf)	( (buf)[0] = 'U', (buf)[1] = '\0' )
#define IS_UNDEFINED(buf)	( (buf)[0] == 'U'  && (buf)[1] == '\0')

/*! Config Structure
 *
 * This structure holds Cactid database configuration information and/or override values
 * obtained via either accessing the database or reading the runtime options.  In addition,
 * it contains runtime status information.
 *
 */
typedef struct config_struct {
	/* general configuration/runtime settings */
	int poller_id;
	int poller_interval;
	int parent_fork;
	int num_parent_processes;
	int script_timeout;
	int threads;
	int logfile_processed;
	/* debugging options */
	int snmponly;
	int SQL_readonly;
	/* host range to be poller with this cactid process */
	int start_host_id;
	int end_host_id;
	/* database connection information */
	char dbhost[80];
	char dbdb[80];
	char dbuser[80];
	char dbpass[80];
	unsigned int dbport;
	/* path information */
	char path_logfile[250];
	char path_php[250];
	char path_php_server[250];
	/* logging options */
	int log_level;
	int log_destination;
	int log_perror;
	int log_pwarn;
	int log_pstats;
	/* ping settings */
	int availability_method;
	int ping_method;
	int ping_retries;
	int ping_timeout;
	int ping_failure_count;
	int ping_recovery_count;
	/* snmp options */
	int snmp_max_get_size;
	int snmp_retries;
	/* PHP Script Server Options */
	int php_required;
	int php_initialized;
	int php_servers;
	int php_current_server;
} config_t;

/*! Target Structure
 *
 * This structure holds the contents of the Poller Items table and the results
 * of each polling action.
 *
 */
typedef struct target_struct {
	int target_id;
	char result[512];
	int local_data_id;
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
	int rrd_num;
	char arg1[255];
	char arg2[255];
	char arg3[255];
} target_t;

/*! SNMP OID's Structure
 *
 * This structure holds SNMP get results temporarily while polling is taking place.
 *
 */
typedef struct snmp_oids {
	int array_position;
	char oid[255];
	char result[255];
} snmp_oids_t;

/*! PHP Script Server Structure
 *
 * This structure holds status and PID information for all the running
 * PHP Script Server processes.
 *
 */
typedef struct php_processes {
	int php_state;
	pid_t php_pid;
	int php_write_fd;
	int php_read_fd;
} php_t;

/*! Host Structure
 *
 * This structure holds host information from the host table and is used throughout
 * the application.
 *
 */
typedef struct host_struct {
	int id;
	char hostname[250];
	char snmp_community[100];
	char snmp_username[50];
	char snmp_password[50];
	int snmp_version;
	int snmp_port;
	int snmp_timeout;
	int status;
	int status_event_count;
	char status_fail_date[40];
	char status_rec_date[40];
	char status_last_error[100];
	double min_time;
	double max_time;
	double cur_time;
	double avg_time;
	int total_polls;
	int failed_polls;
	double availability;
	int ignore_host;
	void *snmp_session;
} host_t;

/*! Host Reindex Structure
 *
 * This structure holds the results of the host re-index checks and values.
 *
 */
typedef struct host_reindex_struct {
	char op[4];
	char assert_value[100];
	char arg1[100];
	int data_query_id;
	int action;
} reindex_t;

/*! Ping Result Struction
 *
 * This structure holds the results of a host ping.
 *
 */
typedef struct ping_results {
	char hostname[255];
	char ping_status[50];
	char ping_response[50];
	char snmp_status[50];
	char snmp_response[50];
} ping_t;

/*! ICMP Ping Structure
 *
 * This structure is required to craft a raw ICMP packet in order to ping a host.
 *
 */
struct icmphdr {
	char type;
	char code;
	unsigned short checksum;
	union {
		struct {
			unsigned short id;
			unsigned short sequence;
		} echo;
		unsigned int gateway;
		struct {
			unsigned short unused;
    		unsigned short mtu;
		} frag;
	} un;
};


/* Globals */
extern config_t   set;
extern php_t      *php_processes;
extern char       start_datetime[20];
extern char       config_paths[CONFIG_PATHS][BUFSIZE];
extern int        active_threads;

#endif /* not _CACTID_H_ */
