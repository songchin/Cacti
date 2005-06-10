/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2002-2005 The Cacti Group                                 |
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
 |                                                                         | 
 | In addition, as a special exception, the copyright holders give         |
 | permission to link the code of portions of this program with the        |
 | OpenSSL library under certain conditions as described in each           |
 | individual source file, and distribute linked combinations              |
 | including the two.                                                      |
 |                                                                         |
 | You must obey the GNU General Public License in all respects            |
 | for all of the code used other than OpenSSL.  If you modify             |
 | file(s) with this exception, you may extend this exception to your      |
 | version of the file(s), but you are not obligated to do so.  If you     |
 | do not wish to do so, delete this exception statement from your         |
 | version.  If you delete this exception statement from all source        |
 | files in the program, then also delete it here.                         |
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

#include <sys/stat.h>
#include <sys/socket.h>
#include <netdb.h>
#include <syslog.h>
#include <errno.h>
#include "common.h"
#include "cactid.h"
#include "util.h"
#include "snmp.h"
#include "sql.h"

/******************************************************************************/
/*  read_config_options() - load default values from the database for poller  */
/*                          processing.                                       */
/******************************************************************************/
int read_config_options(config_t *set) {
	MYSQL mysql;
	MYSQL_RES *result;
	MYSQL_ROW mysql_row;
	int num_rows;
	char logmessage[LOGSIZE];
	char web_root[BUFSIZE];

	db_connect(set->dbdb, &mysql);

	/* get logging level from database - overrides cactid.conf */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='log_verbosity'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		if (atoi(mysql_row[0])) {
			set->verbose = atoi(mysql_row[0]);
		}
	}

	/* determine script server path operation and default log file processing */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='path_webroot'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		strncpy(set->path_php_server,mysql_row[0], sizeof(set->path_php_server));
		strncpy(web_root, mysql_row[0], sizeof(web_root));
		strncat(set->path_php_server,"/script_server.php", sizeof(set->path_php_server));
	}

	/* determine logfile path */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='path_cactilog'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		if (strlen(mysql_row[0]) != 0) {
			strncpy(set->path_logfile,mysql_row[0], sizeof(set->path_logfile));
		} else {
			if (strlen(web_root) != 0) {
				strncpy(set->path_logfile, strcat(web_root, "/log/cacti.log"), sizeof(set->path_logfile));
			} else {
				strncpy(set->path_logfile, "", sizeof(set->path_logfile));
			}
		}
	} else {
		strncpy(set->path_logfile, strcat(web_root, "/log/cacti.log"), sizeof(set->path_logfile));
 	}

	/* log the path_webroot variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The path_php_server variable is %s\n" ,set->path_php_server);
		cacti_log(logmessage);
	}

	/* log the path_cactilog variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The path_cactilog variable is %s\n" ,set->path_logfile);
		cacti_log(logmessage);
	}

	/* determine log file, syslog or both, default is 1 or log file only */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='log_destination'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);
		set->log_destination = atoi(mysql_row[0]);
	}else{
		set->log_destination = 1;
	}

	/* log the log_destination variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The log_destination variable is %i\n" ,set->log_destination);
		cacti_log(logmessage);
	}

	/* get PHP Path Information for Scripting */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='path_php_binary'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);
		strncpy(set->path_php,mysql_row[0], sizeof(set->path_php));
	}

	/* log the path_php variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The path_php variable is %s\n" ,set->path_php);
		cacti_log(logmessage);
	}

	/* set availability_method */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='availability_method'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->availability_method = atoi(mysql_row[0]);
	}

	/* log the availability_method variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The availability_method variable is %i\n" ,set->availability_method);
		cacti_log(logmessage);
	}

	/* set ping_recovery_count */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='ping_recovery_count'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->ping_recovery_count = atoi(mysql_row[0]);
	}

	/* log the ping_recovery_count variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The ping_recovery_count variable is %i\n" ,set->ping_recovery_count);
		cacti_log(logmessage);
	}

	/* set ping_failure_count */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='ping_failure_count'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->ping_failure_count = atoi(mysql_row[0]);
	}

	/* log the ping_failure_count variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The ping_failure_count variable is %i\n" ,set->ping_failure_count);
		cacti_log(logmessage);
	}

	/* set ping_method */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='ping_method'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->ping_method = atoi(mysql_row[0]);
	}

	/* log the ping_method variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The ping_method variable is %i\n" ,set->ping_method);
		cacti_log(logmessage);
	}

	/* set ping_retries */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='ping_retries'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->ping_retries = atoi(mysql_row[0]);
	}

	/* log the ping_retries variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The ping_retries variable is %i\n" ,set->ping_retries);
		cacti_log(logmessage);
	}

	/* set ping_timeout */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='ping_timeout'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		set->ping_timeout = atoi(mysql_row[0]);
	}

	/* log the ping_timeout variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The ping_timeout variable is %i\n" ,set->ping_timeout);
		cacti_log(logmessage);
	}

	/* set logging option for errors */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='log_perror'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		if (!strcmp(mysql_row[0],"on")) {
			set->log_perror = 1;
		}else {
			set->log_perror = 0;
		}
	}

	/* log the log_perror variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The log_perror variable is %i\n" ,set->log_perror);
		cacti_log(logmessage);
	}

	/* set logging option for errors */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='log_pwarn'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		if (!strcmp(mysql_row[0],"on")) {
			set->log_pwarn = 1;
		}else {
			set->log_pwarn = 0;
		}
	}

	/* log the log_pwarn variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The log_pwarn variable is %i\n" ,set->log_pwarn);
		cacti_log(logmessage);
	}

	/* set logging option for statistics */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='log_pstats'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);

		if (!strcmp(mysql_row[0],"on")) {
			set->log_pstats = 1;
		}else {
			set->log_pstats = 0;
		}
	}

	/* log the log_pstats variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The log_pstats variable is %i\n" ,set->log_pstats);
		cacti_log(logmessage);
	}

	/* get Cacti defined max threads override cactid.conf */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='max_threads'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);
		set->threads = atoi(mysql_row[0]);
		if (set->threads > 30) {
			set->threads = 30;
		}
	}

	/* log the threads variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: The threads variable is %i\n" ,set->threads);
		cacti_log(logmessage);
	}

	/* get the poller_interval for those who have elected to go with a 1 minute polling interval */
	result = db_query(&mysql, "SELECT value FROM settings WHERE name='poller_interval'");
	num_rows = (int)mysql_num_rows(result);

	if (num_rows > 0) {
		mysql_row = mysql_fetch_row(result);
		set->poller_interval = atoi(mysql_row[0]);
	}else{
		set->poller_interval = 0;
	}

	/* log the threads variable */
	if (set->verbose == POLLER_VERBOSITY_DEBUG) {
		if (set->poller_interval == 0) {
			snprintf(logmessage, LOGSIZE, "DEBUG: The polling interval is the system default\n" ,set->poller_interval);
		}else{
			snprintf(logmessage, LOGSIZE, "DEBUG: The polling interval is %i seconds\n" ,set->poller_interval);
		}
		cacti_log(logmessage);
	}

	mysql_free_result(result);
	db_disconnect(&mysql);
}

/******************************************************************************/
/*  read_cactid_config() - read the CACTID configuration files to obtain      */
/*                         environment settings.                              */
/******************************************************************************/
int read_cactid_config(char *file, config_t *set) {
	FILE *fp;
	char buff[BUFSIZE];
	char p1[BUFSIZE];
	char p2[BUFSIZE];

	if ((fp = fopen(file, "rb")) == NULL) {
		if (set->verbose == POLLER_VERBOSITY_DEBUG) {
			printf("ERROR: Could not open config file [%s]\n", file);
		}
		return (-1);
	}else{
		printf("CACTID: Using cactid config file [%s]\n", file);
		while(!feof(fp)) {
			fgets(buff, BUFSIZE, fp);
			if (!feof(fp) && *buff != '#' && *buff != ' ' && *buff != '\n') {
				sscanf(buff, "%15s %255s", p1, p2);

				if (!strcasecmp(p1, "DB_Host")) strncpy(set->dbhost, p2, sizeof(set->dbhost));
				else if (!strcasecmp(p1, "DB_Database")) strncpy(set->dbdb, p2, sizeof(set->dbdb));
				else if (!strcasecmp(p1, "DB_User")) strncpy(set->dbuser, p2, sizeof(set->dbuser));
				else if (!strcasecmp(p1, "DB_Pass")) strncpy(set->dbpass, p2, sizeof(set->dbpass));
                else if (!strcasecmp(p1, "DB_Port")) set->dbport = atoi(p2);
				else {
					printf("WARNING: Unrecongized directive: %s=%s in %s\n", p1, p2, file);
				}
			}
		}

		return (0);
	}
}

/******************************************************************************/
/*  config_defaults() - populate system variables with default values.        */
/******************************************************************************/
void config_defaults(config_t * set) {
	set->snmp_ver = DEFAULT_SNMP_VER;
	set->threads = DEFAULT_THREADS;
    set->dbport = DEFAULT_DB_PORT;

	strncpy(set->dbhost, DEFAULT_DB_HOST, sizeof(set->dbhost));
	strncpy(set->dbdb, DEFAULT_DB_DB, sizeof(set->dbhost));
	strncpy(set->dbuser, DEFAULT_DB_USER, sizeof(set->dbhost));
	strncpy(set->dbpass, DEFAULT_DB_PASS, sizeof(set->dbhost));

	strncpy(config_paths[0], CONFIG_PATH_1, sizeof(config_paths[0]));
	strncpy(config_paths[1], CONFIG_PATH_2, sizeof(config_paths[1]));
	strncpy(config_paths[2], CONFIG_PATH_3, sizeof(config_paths[2]));
	strncpy(config_paths[3], CONFIG_PATH_4, sizeof(config_paths[3]));
	strncpy(config_paths[4], CONFIG_PATH_5, sizeof(config_paths[4]));

	return;
}

/******************************************************************************/
/*  exit_cactid() - if there is a serious error and cactid can't continue     */
/*                  make sure that the php script server is shut down first.  */
/******************************************************************************/
void exit_cactid() {
	if (set.php_sspid) {
		if (set.parent_fork == CACTID_PARENT) {
			php_close();
			cacti_log("ERROR: Cactid Parent Process Encountered a Serious Error and Must Exit\n");
		} else {
			cacti_log("ERROR: Cactid Fork Process Encountered a Serious Error and Must Exit\n");			
		}
	}

	exit(-1);
}

/******************************************************************************/
/*  cacti_log() - output user messages to the Cacti logfile facility.         */
/*                Can also output to the syslog facility if desired.          */
/******************************************************************************/
void cacti_log(char *logmessage) {
	FILE *log_file = NULL;

	/* Variables for Time Display */
	time_t nowbin;
	const struct tm *nowstruct;
	char logprefix[40]; /* Formatted Log Prefix */
	char flogmessage[LOGSIZE];	/* Formatted Log Message */
	extern config_t set;
	int fileopen = 0;

	/* log message prefix */
	snprintf(logprefix, sizeof(logprefix), "CACTID: Poller[%i] ", set.poller_id);

	if (((set.log_destination == 1) || (set.log_destination == 2)) && (set.verbose != POLLER_VERBOSITY_NONE) && (strlen(set.path_logfile) != 0)) {
		while (!fileopen) {
			if (!file_exists(set.path_logfile)) {
				log_file = fopen(set.path_logfile, "w");
			}else {
				log_file = fopen(set.path_logfile, "a");
			}

			if (log_file != NULL) {
				fileopen = 1;
			}else {
				if (set.verbose == POLLER_VERBOSITY_DEBUG) {
					printf("ERROR: Could not open Logfile will not be logging\n");
				}
				break;
			}
		}
	}

	/* get time for logfile */
	if (time(&nowbin) == (time_t) - 1)
		printf("ERROR: Could not get time of day from time()\n");

	nowstruct = localtime(&nowbin);

	if (strftime(flogmessage, 50, "%m/%d/%Y %I:%M:%S %p - ", nowstruct) == (size_t) 0)
		printf("ERROR: Could not get string from strftime()\n");

	strcat(flogmessage, logprefix);
	strcat(flogmessage, logmessage);

	if (fileopen != 0) {
		fputs(flogmessage, log_file);
		fclose(log_file);
	}

	/* output to syslog/eventlog */
	if ((set.log_destination == 2) || (set.log_destination == 3)) {
		openlog("Cacti Logging", LOG_NDELAY | LOG_PID, LOG_SYSLOG);
		if ((strstr(flogmessage,"ERROR")) && (set.log_perror)) {
			syslog(LOG_CRIT,"%s\n", flogmessage);
		}
		if ((strstr(flogmessage,"WARNING")) && (set.log_pwarn)){
			syslog(LOG_WARNING,"%s\n", flogmessage);
		}
		if ((strstr(flogmessage,"STATS")) && (set.log_pstats)){
				syslog(LOG_NOTICE,"%s\n", flogmessage);
		}
		closelog();
	}

	if (set.verbose >= POLLER_VERBOSITY_MEDIUM) {
	    snprintf(flogmessage, LOGSIZE, "CACTID: %s", logmessage);
		printf(flogmessage);
	}
}

/******************************************************************************/
/*  file_exists - check for the existance of a file.                          */
/******************************************************************************/
int file_exists(char *filename) {
	struct stat file_stat;

	if (stat(filename, &file_stat)) {
		return 0;
	}else{
		return 1;
	}
}

/******************************************************************************/
/*  is_numeric() - check to see if a string is long or double.                */
/******************************************************************************/
int is_numeric(char *string)
{
	extern int errno;
	long local_lval;
	double local_dval;
	char *end_ptr_long, *end_ptr_double;
	int conv_base=10;
	int length;

	length = strlen(string);

	if (!length) {
		return 0;
	}

 	/* check for an integer */
	errno = 0;
	local_lval = strtol(string, &end_ptr_long, conv_base);
	if (errno!=ERANGE) {
		if (end_ptr_long == string + length) { /* integer string */
			return 1;
		} else if (end_ptr_long == string &&
				*end_ptr_long != '\0') { /* ignore partial string matches */
			return 0;
		}
	} else {
		end_ptr_long=NULL;
	}

	errno=0;
	local_dval = strtod(string, &end_ptr_double);
	if (errno != ERANGE) {
		if (end_ptr_double == string + length) { /* floating point string */
			return 1;
		}
	} else {
		end_ptr_double=NULL;
	}

	if (!errno) {
		return 1;
	} else {
		return 0;
 	}
}

/******************************************************************************/
/*  string_to_argv() - convert a string to an argc/argv combination           */
/******************************************************************************/
char **string_to_argv(char *argstring, int *argc){
	char *p, **argv;
	char *last;
	int i = 0;

	for((*argc)=1, i=0; i<strlen(argstring); i++) if(argstring[i]==' ') (*argc)++;

	argv = (char **)malloc((*argc) * sizeof(char**));
	for((p = strtok_r(argstring, " ", &last)), i=0; p; (p = strtok_r(NULL, " ", &last)), i++) argv[i] = p;
	argv[i] = NULL;

	return argv;
}

/******************************************************************************/
/*  strip_alpha() - remove trailing alpha characters from a string.           */
/******************************************************************************/
char *strip_alpha(char *string)
{
	int i;
	
	i = strlen(string);

	while (i >= 0) {
		if ((string[i] > 47) && (string[i] < 58)) {
			break;
		}else{
			string[i] = '\0';
		}
		i--;
	}

	return string;
}

/******************************************************************************/
/*  add_slashes() - compensate for back slashes in arguments for scripts.     */
/******************************************************************************/
char *add_slashes(char *string, int arguments_2_strip) {
	int length;
	int space_count;
	int position;
	int new_position;
	
	char *return_str = (char *) malloc(BUFSIZE);

	length = strlen(string);
	space_count = 0;
	position = 0;
	new_position = position;
	
	/* simply return on blank string */
	if (!length) {
		return string;
	}

	while (position < length) {
		/* backslash detected, change to forward slash */
		if (string[position] == '\\') {	
			/* only add slashes for first x arguments */
			if (space_count < arguments_2_strip) {
				return_str[new_position] = '/';
			} else {
				return_str[new_position] = string[position];
			}
		/* end of argument detected */
		} else if (string[position] == ' ') {
			return_str[new_position] = ' ';
			space_count++;
		/* normal character detected */
		} else {
			return_str[new_position] = string[position];
		}
		new_position++;
		position++;
	}
	return_str[new_position] = '\0';

	return(return_str);
}

/******************************************************************************/
/*  strip_quotes() - remove beginning and ending quotes from a string         */
/******************************************************************************/
char *strip_quotes(char *string) {
	int length;
	char *posptr, *startptr;
	char type;

	length = strlen(string);

	/* simply return on blank string */
	if (!length) {
		return string;
	}

	/* set starting postion of string */
	startptr = string;

	/* find first quote in the string, determine type */
	if ((posptr = strchr(string, '"')) != NULL) {
		type = '"';
	} else if ((posptr = strchr(string, '\'')) != NULL) {
		type = '\'';
	} else {
		return string;
	}

	posptr = strchr(string,type);

	/* if the first character is a string, then we are ok */
	if (startptr == posptr) {
		/* remove leading quote */
		memmove(startptr, posptr+1, strlen(string) - 1);
		string[length] = '\0';

		/* remove trailing quote */
		posptr = strchr(string,type);
		if (posptr != NULL) {
			*posptr = '\0';
		}
 	}

	return string;
}
