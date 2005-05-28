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
#include "sql.h"
#include "snmp.h"
#include "util.h"
#include "php.h"
#include "locks.h"
#include "poller.h"
#include "nft_popen.h"
#include <errno.h>

/******************************************************************************/
/*  child() - called for every host.  Is a forked process to initiate poll.   */
/******************************************************************************/
void *child(void * arg) {
	extern int active_threads;
	int host_id = *(int *) arg;
	char logmessage[LOGSIZE];

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("In Poller, About to Start Polling of Host", SEV_DEBUG, 0);
	}

	poll_host(host_id);

	thread_mutex_lock(LOCK_THREAD);
	active_threads--;
	thread_mutex_unlock(LOCK_THREAD);

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "The Value of Active Threads is %i" ,active_threads);
		cacti_log(logmessage, SEV_DEBUG, 0);
	}

	pthread_exit(0);
}

/******************************************************************************/
/*  poll_host() - Poll a host.                                                */
/******************************************************************************/
void poll_host(int host_id) {
	char query1[BUFSIZE];
	char query2[BUFSIZE];
	char *query3;
	char query4[BUFSIZE];
	char query5[BUFSIZE];
	char query6[BUFSIZE];
	char query7[BUFSIZE];
	char errstr[512];
	int num_rows;
	int assert_fail = 0;
	int spike_kill = 0;
	char *poll_result = NULL;
	char logmessage[LOGSIZE];
	char update_sql[BUFSIZE];

	reindex_t *reindex;
	target_t *entry;
	host_t *host;
	ping_t *ping;

	MYSQL mysql;
	MYSQL_RES *result;
	MYSQL_ROW row;

	/* allocate host and ping structures with appropriate values */
	host = (host_t *) malloc(sizeof(host_t));
	ping = (ping_t *) malloc(sizeof(ping_t));

	#ifndef OLD_MYSQL   
	mysql_thread_init();
	#endif 

	snprintf(query1, sizeof(query1), "select action,hostname,snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_port,snmp_timeout,availability_method,ping_method,rrd_name,rrd_path,arg1,arg2,arg3,local_data_id,rrd_num from poller_item where host_id=%i order by rrd_path,rrd_name", host_id);
	snprintf(query2, sizeof(query2), "select id,hostname,snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_port,snmp_timeout,availability_method,ping_method,status,status_event_count,status_fail_date,status_rec_date,status_last_error,min_time,max_time,cur_time,avg_time,total_polls,failed_polls,availability from host where id=%i", host_id);
	snprintf(query4, sizeof(query4), "select data_query_id,action,op,assert_value,arg1 from poller_reindex where host_id=%i", host_id);
	snprintf(query5, sizeof(query6), "select action,hostname,snmp_community,snmp_version,snmpv3_auth_username,snmpv3_auth_password,snmpv3_auth_protocol,snmpv3_priv_passphrase,snmpv3_priv_protocol,snmp_port,snmp_timeout,availability_method,ping_method,rrd_name,rrd_path,arg1,arg2,arg3,local_data_id,rrd_num from poller_item where (host_id=%i and rrd_next_step <=0) order by rrd_path,rrd_name", host_id);
	snprintf(query6, sizeof(query6), "update poller_item SET rrd_next_step=rrd_next_step-%i where host_id=%i", set.poller_interval, host_id);
	snprintf(query7, sizeof(query7), "update poller_item SET rrd_next_step=rrd_step-%i where (rrd_next_step < 0 and host_id=%i)", set.poller_interval, host_id);

	db_connect(set.dbdb, &mysql);

	if (host_id) {
		/* get data about this host */
		result = db_query(&mysql, query2);
		num_rows = (int)mysql_num_rows(result);

		if (num_rows != 1) {
			cacti_log("Unknown Host ID", SEV_ERROR, host_id);
			return;
		}

		row = mysql_fetch_row(result);

		/* populate host structure */
		host->ignore_host = 0;
		host->id = atoi(row[0]);
		if (row[1] != NULL) snprintf(host->hostname, sizeof(host->hostname), "%s", row[1]);
		if (row[2] != NULL) {
			snprintf(host->snmp_community, sizeof(host->snmp_community), "%s", row[2]);
		} else {
			snprintf(host->snmp_community, sizeof(host->snmp_community), "%s", "");
		}
		host->snmp_version = atoi(row[3]);
		if (row[4] != NULL) snprintf(host->snmpv3_auth_username, sizeof(host->snmpv3_auth_username), "%s", row[4]);
		if (row[5] != NULL) snprintf(host->snmpv3_auth_password, sizeof(host->snmpv3_auth_password), "%s", row[5]);
		if (row[6] != NULL) snprintf(host->snmpv3_auth_protocol, sizeof(host->snmpv3_auth_protocol), "%s", row[6]);
		if (row[7] != NULL) snprintf(host->snmpv3_priv_passphrase, sizeof(host->snmpv3_priv_passphrase), "%s", row[7]);
		if (row[8] != NULL) snprintf(host->snmpv3_priv_protocol, sizeof(host->snmpv3_priv_protocol), "%s", row[8]);
		host->snmp_port = atoi(row[9]);
		host->snmp_timeout = atoi(row[10]);
		host->availability_method = atoi(row[11]);
		host->ping_method = atoi(row[12]);
		if (row[13] != NULL) host->status = atoi(row[13]);
		host->status_event_count = atoi(row[14]);
		snprintf(host->status_fail_date, sizeof(host->status_fail_date), "%s", row[15]);
		snprintf(host->status_rec_date, sizeof(host->status_rec_date), "%s", row[16]);
		snprintf(host->status_last_error, sizeof(host->status_last_error), "%s", row[17]);
		host->min_time = atof(row[18]);
		host->max_time = atof(row[19]);
		host->cur_time = atof(row[20]);
		host->avg_time = atof(row[21]);
		host->total_polls = atoi(row[22]);
		host->failed_polls = atoi(row[23]);
		host->availability = atof(row[24]);
		
		/* initialize SNMP */
		snmp_host_init(host);

		/* perform a check to see if the host is alive by polling it's SysDesc
		 * if the host down from an snmp perspective, don't poll it.
		 * function sets the ignore_host bit */
		if ((host->availability_method == AVAIL_SNMP) && (host->snmp_community == "") || 
			(host->availability_method == AVAIL_NONE)) {
			update_host_status(HOST_UP, host, ping, host->availability_method);	

			if (set.verbose >= POLLER_VERBOSITY_MEDIUM) {
				snprintf(logmessage, LOGSIZE, "Availability Checking Disabled for Host '%s'", host->hostname);
				cacti_log(logmessage, SEV_NOTICE, host->id);
			}
		}else{
			if (ping_host(host, ping) == HOST_UP) {
				update_host_status(HOST_UP, host, ping, host->availability_method);
			}else{
				host->ignore_host = 1;
				update_host_status(HOST_DOWN, host, ping, host->availability_method);
			}
		}

		/* update host table */
		snprintf(update_sql, sizeof(update_sql), "update host set status='%i',status_event_count='%i', status_fail_date='%s',status_rec_date='%s',status_last_error='%s',min_time='%f',max_time='%f',cur_time='%f',avg_time='%f',total_polls='%i',failed_polls='%i',availability='%.4f' where id='%i'\n",
			host->status,
			host->status_event_count,
			host->status_fail_date,
			host->status_rec_date,
			host->status_last_error,
			host->min_time,
			host->max_time,
			host->cur_time,
			host->avg_time,
			host->total_polls,
			host->failed_polls,
			host->availability,
			host->id);

		db_insert(&mysql, update_sql);
	} else {
		host->id = 0;
		host->ignore_host = 0;
	}


	/* do the reindex check for this host if not script based */
	if ((!host->ignore_host) && (host_id)) {
		reindex = (reindex_t *) malloc(sizeof(reindex_t));

		result = db_query(&mysql, query4);
		num_rows = (int)mysql_num_rows(result);

		if (num_rows > 0) {
			if (set.verbose == POLLER_VERBOSITY_DEBUG) {
				snprintf(logmessage, LOGSIZE, "RECACHE: Processing %i items in the auto reindex cache for '%s'", num_rows, host->hostname);
				cacti_log(logmessage, SEV_DEBUG, host->id);
			}

			while ((row = mysql_fetch_row(result))) {
				assert_fail = 0;

				reindex->data_query_id = atoi(row[0]);
				reindex->action = atoi(row[1]);
				if (row[2] != NULL) snprintf(reindex->op, sizeof(reindex->op), "%s", row[2]);
				if (row[3] != NULL) snprintf(reindex->assert_value, sizeof(reindex->assert_value), "%s", row[3]);
				if (row[4] != NULL) snprintf(reindex->arg1, sizeof(reindex->arg1), "%s", row[4]);

				switch(reindex->action) {
				case POLLER_ACTION_SNMP: /* snmp */
					poll_result = snmp_get(host, reindex->arg1);
					break;
				case POLLER_ACTION_SCRIPT: /* script (popen) */
					poll_result = exec_poll(host, reindex->arg1);
					break;
				}

				/* assume ok if host is up and result wasn't obtained */
				if (!strcmp(poll_result,"U")) {
					assert_fail = 0;
				}else if ((!strcmp(reindex->op, "=")) && (strcmp(reindex->assert_value,poll_result) != 0)) {
					snprintf(logmessage, LOGSIZE, "ASSERT: '%s=%s' failed. Recaching host '%s', data query #%i", reindex->assert_value, poll_result, host->hostname, reindex->data_query_id);
					cacti_log(logmessage, SEV_NOTICE, host->id);

					query3 = (char *)malloc(256);
					snprintf(query3, 256, "insert into poller_command (poller_id,time,action,command) values (0,NOW(),%i,'%i:%i')", POLLER_COMMAND_REINDEX, host_id, reindex->data_query_id);
					db_insert(&mysql, query3);
					free(query3);

					assert_fail = 1;
				}else if ((!strcmp(reindex->op, ">")) && (strtoll(reindex->assert_value, (char **)NULL, 10) <= strtoll(poll_result, (char **)NULL, 10))) {
					snprintf(logmessage, LOGSIZE, "ASSERT: '%s>%s' failed. Recaching host '%s', data query #%i", reindex->assert_value, poll_result, host->hostname, reindex->data_query_id);
					cacti_log(logmessage, SEV_NOTICE, host->id);

					query3 = (char *)malloc(256);
					snprintf(query3, 256, "insert into poller_command (poller_id,time,action,command) values (0,NOW(),%i,'%i:%i')", POLLER_COMMAND_REINDEX, host_id, reindex->data_query_id);
					db_insert(&mysql, query3);
					free(query3);

					assert_fail = 1;
				}else if ((!strcmp(reindex->op, "<")) && (strtoll(reindex->assert_value, (char **)NULL, 10) >= strtoll(poll_result, (char **)NULL, 10))) {
					snprintf(logmessage, LOGSIZE, "ASSERT: '%s<%s' failed. Recaching host '%s', data query #%i", reindex->assert_value, poll_result, host->hostname, reindex->data_query_id);
					cacti_log(logmessage, SEV_NOTICE, host->id);

					query3 = (char *)malloc(256);
					snprintf(query3, 256, "insert into poller_command (poller_id,time,action,command) values (0,NOW(),%i,'%i:%i')", POLLER_COMMAND_REINDEX, host_id, reindex->data_query_id);
					db_insert(&mysql, query3);
					free(query3);

					assert_fail = 1;
				}

				/* update 'poller_reindex' with the correct information if:
				 * 1) the assert fails
				 * 2) the OP code is > or < meaning the current value could have changed without causing
				 *     the assert to fail */
				if ((assert_fail == 1) || (!strcmp(reindex->op, ">")) || (!strcmp(reindex->op, ">"))) {
					query3 = (char *)malloc(255);
					snprintf(query3, 255, "update poller_reindex set assert_value='%s' where host_id='%i' and data_query_id='%i' and arg1='%s'", poll_result, host_id, reindex->data_query_id, reindex->arg1);
					db_insert(&mysql, query3);
					free(query3);
					if (!strcmp(reindex->arg1,".1.3.6.1.2.1.1.3.0")) {
						spike_kill = 1;
						if (set.verbose == POLLER_VERBOSITY_DEBUG) {
							snprintf(logmessage, LOGSIZE, "Host[%i] NOTICE: Spike Kill in Effect for '%s'", host_id, host->hostname);
							cacti_log(logmessage, SEV_DEBUG, 0);
						}
					}
				}

				free(poll_result);
			}
		}
	}

	/* retreive each hosts polling items from poller cache */
	entry = (target_t *) malloc(sizeof(target_t));

	if (set.poller_interval == 0) {
		result = db_query(&mysql, query1);
		num_rows = (int)mysql_num_rows(result);
	}else{
		result = db_query(&mysql, query5);
		num_rows = (int)mysql_num_rows(result);
		
		/* update poller_items table for next polling interval */
		db_query(&mysql, query6);
		db_query(&mysql, query7);
	}

	while ((row = mysql_fetch_row(result)) && (!host->ignore_host)) {
		/* initialize monitored object */
		entry->target_id = 0;
		entry->action = atoi(row[0]);
		if (row[1] != NULL) snprintf(entry->hostname, sizeof(entry->hostname), "%s", row[1]);
		if (row[2] != NULL) {
			snprintf(entry->snmp_community, sizeof(entry->snmp_community), "%s", row[2]);
		} else {
			snprintf(entry->snmp_community, sizeof(entry->snmp_community), "%s", "");
		}
		entry->snmp_version = atoi(row[3]);
   		if (row[4] != NULL) snprintf(entry->snmpv3_auth_username, sizeof(entry->snmpv3_auth_username), "%s", row[4]);
		if (row[5] != NULL) snprintf(entry->snmpv3_auth_password, sizeof(entry->snmpv3_auth_password), "%s", row[5]);
        if (row[6] != NULL) snprintf(entry->snmpv3_auth_protocol, sizeof(entry->snmpv3_auth_protocol), "%s", row[6]);
        if (row[7] != NULL) snprintf(entry->snmpv3_priv_passphrase, sizeof(entry->snmpv3_priv_passphrase), "%s", row[7]);
        if (row[8] != NULL) snprintf(entry->snmpv3_priv_protocol, sizeof(entry->snmpv3_priv_protocol), "%s", row[8]);
		entry->snmp_port = atoi(row[9]);
		entry->snmp_timeout = atoi(row[10]);
        entry->availability_method = atoi(row[11]);
        entry->ping_method = atoi(row[12]);
		if (row[13] != NULL) snprintf(entry->rrd_name, sizeof(entry->rrd_name), "%s", row[13]);
		if (row[14] != NULL) snprintf(entry->rrd_path, sizeof(entry->rrd_path), "%s", row[14]);
		if (row[15] != NULL) snprintf(entry->arg1, sizeof(entry->arg1), "%s", row[15]);
		if (row[16] != NULL) snprintf(entry->arg2, sizeof(entry->arg2), "%s", row[16]);
		if (row[17] != NULL) snprintf(entry->arg3, sizeof(entry->arg3), "%s", row[17]);
		entry->local_data_id = atoi(row[18]);
		entry->rrd_num = atoi(row[19]);
		snprintf(entry->result, sizeof(entry->result), "%s", "U");

		if (!host->ignore_host) {
			switch(entry->action) {
			case POLLER_ACTION_SNMP: /* raw SNMP poll */
				poll_result = snmp_get(host, entry->arg1);
				snprintf(entry->result, sizeof(entry->result), "%s", poll_result);
				free(poll_result);

				if (host->ignore_host) {
					snprintf(logmessage, LOGSIZE, "SNMP timeout detected [%i milliseconds], ignoring host '%s'", host->snmp_timeout, host->hostname);
					cacti_log(logmessage, SEV_ERROR, host->id);
					snprintf(entry->result, sizeof(entry->result), "%s", "U");
				} else {
					/* remove double or single quotes from string */
					strncpy(entry->result, strip_quotes(entry->result), sizeof(entry->result));

					/* detect erroneous non-numeric result */
					if (!is_numeric(entry->result)) {
						strncpy(errstr, entry->result,sizeof(errstr));
						snprintf(logmessage, LOGSIZE, "Result from SNMP not valid. Partial Result: %.20s...", errstr);
						cacti_log(logmessage, SEV_WARNING, host->id);
						strncpy(entry->result, "U", sizeof(entry->result));
					}
				}

				if (set.verbose >= POLLER_VERBOSITY_MEDIUM) {
					snprintf(logmessage, LOGSIZE, "SNMP: v%i: %s, dsname: %s, oid: %s, value: %s", host->snmp_version, host->hostname, entry->rrd_name, entry->arg1, entry->result);
					cacti_log(logmessage, SEV_INFO, host->id);
				}

				break;
			case POLLER_ACTION_SCRIPT: /* execute script file */
				poll_result = exec_poll(host, entry->arg1);
				snprintf(entry->result, sizeof(entry->result), "%s", poll_result);
				free(poll_result);

				/* remove double or single quotes from string */
				strncpy(entry->result, strip_quotes(entry->result), sizeof(entry->result));

				/* detect erroneous result. can be non-numeric */
				if (!validate_result(entry->result)) {
					strncpy(errstr, (char *) strip_string_crlf(entry->result),sizeof(errstr));
					snprintf(logmessage, LOGSIZE, "Result from SCRIPT not valid. Partial Result: %.20s...", errstr);
					cacti_log(logmessage, SEV_WARNING, host->id);
					strncpy(entry->result, "U", sizeof(entry->result));
				}

				if (set.verbose >= POLLER_VERBOSITY_MEDIUM) {
					snprintf(logmessage, LOGSIZE, "SCRIPT: %s, output: %s", entry->arg1, entry->result);
					cacti_log(logmessage, SEV_INFO, host->id);
				}

				break;
			case POLLER_ACTION_PHP_SCRIPT_SERVER: /* execute script server */
				poll_result = php_cmd(entry->arg1);
				snprintf(entry->result, sizeof(entry->result), "%s", poll_result);
				free(poll_result);

				/* remove double or single quotes from string */
				strncpy(entry->result, strip_quotes(entry->result), sizeof(entry->result));

				/* detect erroneous result. can be non-numeric */
				if (!validate_result(entry->result)) {
					strncpy(errstr, entry->result, sizeof(errstr));
					snprintf(logmessage, LOGSIZE, "Result from SERVER not valid.  Partial Result: %.20s...", errstr);
					cacti_log(logmessage, SEV_WARNING, host_id);
					strncpy(entry->result, "U", sizeof(entry->result));
				}

				if (set.verbose >= POLLER_VERBOSITY_MEDIUM) {
					snprintf(logmessage, LOGSIZE, "SERVER: %s, output: %s", entry->arg1, entry->result);
					cacti_log(logmessage, SEV_INFO, host_id);
				}

				break;
			default: /* unknown action, generate error */
				snprintf(logmessage, LOGSIZE, "Unknown Poller Action: %s", entry->arg1);
				cacti_log(logmessage, SEV_ERROR, host_id);

				break;
			}
		}

		if (entry->result != NULL) {
			/* insert a NaN in place of the actual value if the snmp agent restarts */
			if ((spike_kill) && (!strstr(entry->result,":"))) {
				strncpy(entry->result, "U", sizeof(entry->result));				
			}
			/* format database insert string */
			query3 = (char *)malloc(sizeof(entry->result) + sizeof(entry->local_data_id) + 128);
			snprintf(query3, (sizeof(entry->result) + sizeof(entry->local_data_id) + 128), "insert into poller_output (local_data_id,rrd_name,time,output) values (%i,'%s','%s','%s')", entry->local_data_id, entry->rrd_name, start_datetime, entry->result);
			db_insert(&mysql, query3);
			free(query3);
		}
	}

	/* cleanup memory and prepare for function exit */
	if (host_id) {
		snmp_host_cleanup(host);
	}

	free(entry);
	free(host);
	free(ping);

	mysql_free_result(result);

	#ifndef OLD_MYSQL   
	mysql_thread_end();
	#endif 

	db_disconnect(&mysql);

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("HOST COMPLETE: About to Exit Host Polling Thread Function", SEV_DEBUG, host_id);
	}
}

/******************************************************************************/
/*  validate_result() - Make sure that Cacti results are accurate before      */
/*                      placing in mysql database and/or logfile.             */
/******************************************************************************/
int validate_result(char * result) {
	int space_cnt = 0;
	int delim_cnt = 0;
	int i;

	/* remove control characters from string */
	strncpy(result, strip_string_crlf(result), sizeof(result));

	/* remove trailing white space from string */
	strncpy(result, rtrim(result), sizeof(result));

	/* check the easy cases first */
	/* it has no delimiters, and no space, therefore, must be numeric */
	if ((strstr(result, ":") == 0) && (strstr(result, "!") == 0) && (strstr(result, " ") == 0)) {
		if (is_numeric(result)) {
			return(1);
		} else {
			return(0);
		}
	}

	/* it has delimiters */
	if (((strstr(result, ":") != 0) || (strstr(result, "!") != 0))) {
		if (strstr(result, " ") == 0) {
			return(1);
		}

		if (strstr(result, " ") != 0) {
			for(i=0; i<strlen(result); i++) {
				if ((result[i] == ':') || (result[i] == '!')) {
					delim_cnt = delim_cnt + 1;
				} else if (result[i] == ' ') {
					space_cnt = space_cnt + 1;
				}
			}

			if (space_cnt+1 == delim_cnt) {
				return(1);
			} else {
				return(0);
			}
		}
	}

	/* default handling */
	if (is_numeric(result)) {
		return(1);
	} else {
		return(0);
	}
}


/******************************************************************************/
/*  exec_poll() - Poll a host by running an external program utilizing the    */
/*                popen command.                                              */
/******************************************************************************/
char *exec_poll(host_t *current_host, char *command) {
	extern int errno;
	int cmd_fd;
	int bytes_read;
	char logmessage[LOGSIZE];

	fd_set fds;
	int numfds;
	struct timeval timeout;

	char *result_string = (char *) malloc(BUFSIZE);
	char *proc_command = (char *) malloc(BUFSIZE);

	/* establish timeout of x seconds for pipe response */
	timeout.tv_sec = set.max_script_runtime;
	timeout.tv_usec = 0;

	/* compensate for back slashes in arguments */
	proc_command = add_slashes(command, 2);
	cmd_fd = nft_popen((char *)proc_command, "r");
	free(proc_command);

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		snprintf(logmessage, LOGSIZE, "The POPEN returned the following File Descriptor %i", cmd_fd);
		cacti_log(logmessage, SEV_DEBUG, current_host->id);
	}

	if (cmd_fd >= 0) {
		/* Initialize File Descriptors to Review for Input/Output */
		FD_ZERO(&fds);
		FD_SET(cmd_fd,&fds);

		numfds = cmd_fd + 1;

		/* wait 5 seonds for pipe response */
		switch (select(numfds, &fds, NULL, NULL, &timeout)) {
		case -1:
			switch (errno) {
				case EBADF:
					cacti_log("One or more of the file descriptor sets specified a file descriptor that is not a valid open file descriptor.", SEV_ERROR, current_host->id);
					snprintf(result_string, BUFSIZE, "%s", "U");
					break;
				case EINTR:
					cacti_log("The function was interrupted before any of the selected events occurred and before the timeout interval expired.", SEV_ERROR, current_host->id);
					snprintf(result_string, BUFSIZE, "%s", "U");
					break;
				case EINVAL:
					cacti_log("Possible invalid timeout specified in select() statement.", SEV_ERROR, current_host->id);
					snprintf(result_string, BUFSIZE, "%s", "U");
					break;
				default:
					cacti_log("The script/command select() failed", SEV_ERROR, current_host->id);
					snprintf(result_string, BUFSIZE, "%s", "U");
					break;
			}
		case 0:
			cacti_log("The POPEN timed out", SEV_ERROR, current_host->id);
			snprintf(result_string, BUFSIZE, "%s", "U");
			break;
		default:
			/* get only one line of output, we will ignore the rest */
			bytes_read = read(cmd_fd, result_string, BUFSIZE-1);
			if (bytes_read > 0) {
				result_string[bytes_read] = '\0';
				strip_string_crlf(result_string); 
			} else {
				snprintf(logmessage, LOGSIZE, "Empty result [%s]: '%s'", current_host->hostname, command);
				cacti_log(logmessage, SEV_ERROR, current_host->id);
				snprintf(result_string, BUFSIZE, "%s", "U");
			}
		}

		/* close pipe */
		nft_pclose(cmd_fd);
	}else{
		snprintf(logmessage, LOGSIZE, "Problem executing POPEN [%s]: '%s'", current_host->hostname, command);
		cacti_log(logmessage, SEV_ERROR, current_host->id);
		snprintf(result_string, BUFSIZE, "%s", "U");
	}

	return result_string;
}
