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
#include "locks.h"
#include "sql.h"

int db_insert(MYSQL *mysql, char *query) {
	char logmessage[255];
	if (set.verbose >= HIGH) {
		printf("SQLCMD: %s\n", query);
	}

	if (mysql_query(mysql, query)) {
		sprintf(logmessage, "ERROR: Problem with MySQL: %s\n", mysql_error(mysql));
		cacti_log(logmessage,"e");
		return (FALSE);
	}else{
		return (TRUE);
	}
}

MYSQL_RES *db_query(MYSQL *mysql, char *query) {
	MYSQL_RES *mysql_res;
	
/*	thread_mutex_lock(LOCK_MYSQL);*/
 	mysql_query(mysql, query);
	mysql_res = mysql_store_result(mysql);
/*	thread_mutex_unlock(LOCK_MYSQL);*/
 	
	return mysql_res;
}


int db_connect(char *database, MYSQL *mysql) {
	char logmessage[255];    
	if (set.verbose >= HIGH) {
		printf("MYSQL: Connecting to MySQL database '%s' on '%s'...\n", database, set.dbhost);
	}

/*	thread_mutex_lock(LOCK_MYSQL);*/

	mysql_init(mysql);

	if (!mysql_real_connect(mysql, set.dbhost, set.dbuser, set.dbpass, database, 0, NULL, 0)) {
		sprintf(logmessage, "ERROR: MySQL Connection Failed: %s\n", mysql_error(mysql));
		cacti_log(logmessage,"e");
		thread_mutex_unlock(LOCK_MYSQL);
		exit(0);
	}else{
	    thread_mutex_unlock(LOCK_MYSQL);
		return (0);
	}
/*	thread_mutex_unlock(LOCK_MYSQL);*/
}

void db_disconnect(MYSQL *mysql) {
/*    thread_mutex_lock(LOCK_MYSQL);*/
	mysql_close(mysql);
/*	thread_mutex_unlock(LOCK_MYSQL);*/
}
