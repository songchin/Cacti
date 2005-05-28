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

#include "common.h"
#include "cactid.h"
#include <pthread.h>
#include <errno.h>
#include <stdio.h>
#include <unistd.h>
#include "poller.h"
#include "locks.h"
#include "snmp.h"
#include "php.h"
#include "sql.h"
#include "util.h"
#include "nft_popen.h"

/* Global Variables */
int entries = 0;
int num_hosts = 0;
int active_threads = 0;

int main(int argc, char *argv[]) {
	struct timeval now;
	char *conf_file = NULL;
	double begin_time, end_time;
	int num_rows;
	int device_counter = 0;
	int last_active_threads = 0;
	long int THREAD_SLEEP = 100000;
	time_t nowbin;
	const struct tm *nowstruct;

	pthread_t* threads = NULL;
	pthread_attr_t attr;
	pthread_mutexattr_t mutexattr;

	int* ids = NULL;
	MYSQL mysql;
	MYSQL_RES *result = NULL;
	MYSQL_ROW mysql_row;
	int canexit = 0;
	int host_id;
	int i;
	int loop_count = 0;
	int max_loops;
	char *first_host_ptr, *last_host_ptr, *poller_id_ptr;
	int first_host, last_host, poller_id;
	int mutex_status = 0;
	int thread_status = 0;
	char sql_string[BUFSIZE];
	char result_string[BUFSIZE] = "";
	char logmessage[LOGSIZE];
	char timetxt[20];

	/* make sure we note that php script server is not running */
	set.php_running = 0;

	/* tell cactid that it is parent */
	set.parent_fork = CACTID_PARENT;

	/* set start time for cacti */
	gettimeofday(&now, NULL);
	begin_time = (double) now.tv_usec / 1000000 + now.tv_sec;

	/* get time for poller_output table */
	if (time(&nowbin) == (time_t) - 1)
		printf("ERROR: Could not get time of day from time()\n");

	nowstruct = localtime(&nowbin);

	if (strftime(start_datetime, sizeof(start_datetime), "%Y-%m-%d %H:%M:%S", nowstruct) == (size_t) 0)
		printf("ERROR: Could not get string from strftime()\n");

	/* set default verbosity */
	set.verbose = POLLER_VERBOSITY_HIGH;

	/* set the default poller ID */
	set.poller_id = 0;

	/* get static defaults for system */
	config_defaults(&set);

	/* scan arguments for errors */
	if ((argc != 1) && (argc !=2) && (argc != 4)) {
		printf("ERROR: Cactid requires either 0 or 3 input parameters\n");
		printf("USAGE: <cactidpath>/cactid [-f=first_host -l=last_host -p=poller_id] | [-p=poller_id]\n");
		exit_cactid();
	}

	/* return error if the first arg is greater than the second */
	if (argc == 4) {
	    for(i=1;i<argc;i++) {
			if (strstr(argv[i],"-l=") != NULL) {
			    last_host_ptr = strtok(argv[i],"=");
			    last_host_ptr = strtok(NULL, "=");
				last_host = atoi(last_host_ptr);
			} else if (strstr(argv[i],"-f=") != NULL) {
      			first_host_ptr = strtok(argv[i],"=");
			    first_host_ptr = strtok(NULL, "=");
				first_host = atoi(first_host_ptr);
			} else if (strstr(argv[i],"-p=") != NULL) {
	         	poller_id_ptr = strtok(argv[i],"=");
			    poller_id_ptr = strtok(NULL, "=");
				set.poller_id = atoi(poller_id_ptr);
    		} else {
				printf("ERROR: Invalid calling parameters.  First row must be less than the second row\n");
				printf("USAGE: <cactidpath>/cactid [-f=first_host -l=last_host -p=poller_id] | [-p=poller_id]\n");
				exit_cactid();
			}
		}

		if (first_host > last_host) {
			printf("ERROR: Invalid row specifications.  First host_id must be less than the second host_id\n");
			exit_cactid();
		}
	}
	
	if (argc == 2) {
		if (strstr(argv[1],"-p=") != NULL) {
	    	poller_id_ptr = strtok(argv[1],"=");
			poller_id_ptr = strtok(NULL, "=");
			set.poller_id = atoi(poller_id_ptr);
		}else if (argc == 2) { 
			/* return version */ 
			if ((strcmp(argv[1], "--version") == 0) || (strcmp(argv[1], "--help") == 0)){ 
				printf("CACTID %s  Copyright 2002-2005 by The Cacti Group\n\n", VERSION); 
				printf("Usage: cactid [start_host_id end_host_id]\n\n");
				printf("If you do not specify [start_host_id end_host_id], Cactid will poll all hosts.\n\n");
				printf("Cactid relies on the cactid.conf file that can exist in multiple locations.\n");
				printf("The first location checked is the current directory.  Optionally, it can be\n");
				printf("placed in the '/etc' directory.\n\n");
				printf("Cactid is distributed under the Terms of the GNU General\n");
				printf("Public License Version 2. (www.gnu.org/copyleft/gpl.html)\n\n");
				printf("For more information, see http://www.cacti.net\n");
				exit_cactid(); 
			} 

			exit_cactid();
    	} else {
			printf("ERROR: Invalid calling parameters.  First row must be less than the second row\n");
			printf("USAGE: <cactidpath>/cactid [-f=first_host -l=last_host -p=poller_id] | [-p=poller_id]\n");
			exit_cactid();
		}
	}

	/* read configuration file to establish local environment */
	if (conf_file) {
		if ((read_cactid_config(conf_file, &set)) < 0) {
			printf("ERROR: Could not read config file: %s\n", conf_file);
			exit_cactid();
		}
	}else{
		conf_file = malloc(BUFSIZE);

		if (!conf_file) {
			printf("ERROR: Fatal malloc error!\n");
			exit_cactid();
		}

		for(i=0;i<CONFIG_PATHS;i++) {
			snprintf(conf_file, BUFSIZE, "%s%s", config_paths[i], DEFAULT_CONF_FILE);

			if (read_cactid_config(conf_file, &set) >= 0) {
				break;
			}

			if (i == CONFIG_PATHS-1) {
				snprintf(conf_file, BUFSIZE, "%s%s", config_paths[0], DEFAULT_CONF_FILE);
			}
		}
	}

	/* read settings table from the database to further establish environment */
	read_config_options(&set);

	/* find out how many loops we can perform before terminating */
	max_loops = DEFAULT_TIMEOUT / THREAD_SLEEP;

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "CACTID: Version %s starting", VERSION);
		cacti_log(logmessage, SEV_DEBUG, 0);
	} else {
		printf("CACTID: Version %s starting\n", VERSION);
	}

	/* connect to database */
	db_connect(set.dbdb, &mysql);

	/* initialize SNMP */
	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "CACTID: Initializing Net-SNMP API\n", VERSION);
		cacti_log(logmessage, SEV_DEBUG, 0);
	}
	init_snmp("cactid");

	/* initialize PHP */
	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "CACTID: Initializing PHP Script Server\n", VERSION);
		cacti_log(logmessage, SEV_DEBUG, 0);
	}

	if (!php_init()) {
		exit_cactid();
	} else {
		set.php_running = 1;
	}

	/* get the id's to poll */
	switch (argc) {
		case 1:
			result = db_query(&mysql, "SELECT id FROM host WHERE disabled='' ORDER BY id");

			break;
		case 2:
			snprintf(result_string, sizeof(result_string), "SELECT id FROM host WHERE (disabled='' and poller_id = %s) ORDER BY id", poller_id_ptr);
			result = db_query(&mysql, result_string);

			break;
		case 4:
			snprintf(result_string, sizeof(result_string), "SELECT id FROM host WHERE (disabled='' and (id >= %s and id <= %s) and poller_id = %s) ORDER BY id\0", first_host_ptr, last_host_ptr, poller_id_ptr);
			result = db_query(&mysql, result_string);

			break;
		default:
			break;
	}

	num_rows = mysql_num_rows(result) + 1; /* add 1 for host = 0 */
	threads = (pthread_t *)malloc(num_rows * sizeof(pthread_t));
	ids = (int *)malloc(num_rows * sizeof(int));

	/* initialize threads and mutexes */
	pthread_attr_init(&attr);
	pthread_attr_setdetachstate(&attr, PTHREAD_CREATE_DETACHED);

	init_mutexes();

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "DEBUG: Initial Value of Active Threads is %i", active_threads);
		cacti_log(logmessage, SEV_DEBUG, 0);
	}

	/* tell fork processes that they are now active */
	set.parent_fork = CACTID_FORK;
	
	/* loop through devices until done */
	while ((device_counter < num_rows) && (canexit == 0)) {
		mutex_status = thread_mutex_trylock(LOCK_THREAD);

		switch (mutex_status) {
		case 0:
			if (last_active_threads != active_threads) {
				last_active_threads = active_threads;
			}

			while ((active_threads < set.threads) && (device_counter < num_rows)) {
				if (device_counter > 0) {
					mysql_row = mysql_fetch_row(result);
					host_id = atoi(mysql_row[0]);
					ids[device_counter] = host_id;
				} else {
					ids[device_counter] = 0;
				}

				/* create child process */
				thread_status = pthread_create(&threads[device_counter], &attr, child, &ids[device_counter]);

				switch (thread_status) {
					case 0:
						if (set.verbose == POLLER_VERBOSITY_DEBUG) {
							snprintf(logmessage, LOGSIZE, "DEBUG: Valid Thread to be Created");
							cacti_log(logmessage, SEV_DEBUG, 0);
						}

						device_counter++;
						active_threads++;

						if (set.verbose == POLLER_VERBOSITY_DEBUG) {
							snprintf(logmessage, LOGSIZE, "DEBUG: The Value of Active Threads is %i", active_threads);
							cacti_log(logmessage, SEV_DEBUG, 0);
						}

						break;
					case EAGAIN:
						snprintf(logmessage, LOGSIZE, "ERROR: The System Lacked the Resources to Create a Thread");
						cacti_log(logmessage, SEV_ERROR, 0);
						break;
					case EFAULT:
						snprintf(logmessage, LOGSIZE, "ERROR: The Thread or Attribute Was Invalid");
						cacti_log(logmessage, SEV_ERROR, 0);
						break;
					case EINVAL:
						snprintf(logmessage, LOGSIZE, "ERROR: The Thread Attribute is Not Initialized");
						cacti_log(logmessage, SEV_ERROR, 0);
						break;
					default:
						snprintf(logmessage, LOGSIZE, "ERROR: Unknown Thread Creation Error");
						cacti_log(logmessage, SEV_ERROR, 0);
						break;
				}
				usleep(THREAD_SLEEP);

				loop_count++;
				if (loop_count > max_loops) {
					cacti_log("ERROR: Cactid Timed Out While Processing Hosts Internal", SEV_CRITICAL, 0);
					canexit = 1;
					break;
				}
			}

			thread_mutex_unlock(LOCK_THREAD);

			break;
		case EBUSY:
			snprintf(logmessage, LOGSIZE, "ERROR: Deadlock Occured");
			cacti_log(logmessage, SEV_ERROR, 0);
			break;
		case EINVAL:
			snprintf(logmessage, LOGSIZE, "ERROR: Attempt to Unlock an Uninitialized Mutex");
			cacti_log(logmessage, SEV_ERROR, 0);
			break;
		case EFAULT:
			snprintf(logmessage, LOGSIZE, "ERROR: Attempt to Unlock an Invalid Mutex");
			cacti_log(logmessage, SEV_ERROR, 0);
			break;
		default:
			snprintf(logmessage, LOGSIZE, "ERROR: Unknown Mutex Lock Error Code Returned");
			cacti_log(logmessage, SEV_ERROR, 0);
			break;
		}

		usleep(THREAD_SLEEP);

		loop_count++;
		if (loop_count > max_loops) {
			cacti_log("ERROR: Cactid Timed Out While Processing Hosts External", SEV_CRITICAL, 0);
			canexit = 1;
			break;
		}
	}

	/* wait for all threads to complete */
	while (canexit == 0) {
		if (thread_mutex_trylock(LOCK_THREAD) != EBUSY) {
			if (last_active_threads != active_threads) {
				last_active_threads = active_threads;
			}

			if (active_threads == 0) {
				canexit = 1;
			}

			thread_mutex_unlock(LOCK_THREAD);
		}

		usleep(THREAD_SLEEP);

		loop_count++;
		if (loop_count > max_loops) {
			cacti_log("ERROR: Cactid Timed Out While Processing Hosts", SEV_CRITICAL, 0);
			break;
		}
	}

	/* tell cactid that it is now parent */
	set.parent_fork = CACTID_PARENT;
	
	/* print out stats */
	gettimeofday(&now, NULL);

	/* update the db for |data_time| on graphs */
	db_insert(&mysql, "replace into settings (name,value) values ('date',NOW())");
	snprintf(sql_string,sizeof(sql_string),"insert into poller_time (poller_id, start_time, end_time) values (%i, '%s', NOW())",set.poller_id,start_datetime);
	db_insert(&mysql, sql_string);

	/* cleanup and exit program */
	pthread_attr_destroy(&attr);

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("Thread Cleanup Complete", SEV_DEBUG, 0);
	}

	/* close the php script server */
	php_close();

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("PHP Script Server Pipes Closed", SEV_DEBUG, 0);
	}

	/* free malloc'd variables */
	free(threads);
	free(ids);
	free(conf_file);

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("Allocated Variable Memory Freed", SEV_DEBUG, 0);
	}

	/* finally add some statistics to the log and exit */
	end_time = (double) now.tv_usec / 1000000 + now.tv_sec;

	if ((set.verbose >= POLLER_VERBOSITY_MEDIUM) && (argc != 1)) {
		snprintf(logmessage, LOGSIZE, "Execution Time: %.4f s, Threads: %i, Hosts: %i", (end_time - begin_time), set.threads, num_rows);
		cacti_log(logmessage, SEV_NOTICE, 0);
	} else {
		printf("CACTID: Execution Time: %.4f s, Threads: %i, Hosts: %i\n", (end_time - begin_time), set.threads, num_rows);
	}

	/* close mysql */
	mysql_free_result(result);
	db_disconnect(&mysql);

	exit(0);
}

