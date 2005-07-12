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

#include <sys/wait.h>
#include <sys/types.h>
#include <sys/time.h>
#include <sys/select.h>
#include <stdio.h>
#include <unistd.h>
#include <assert.h>
#include <errno.h>
#include <stdlib.h>
#include <string.h>
#include "php.h"
#include "common.h"
#include "cactid.h"
#include "locks.h"
#include "util.h"

extern char **environ;

/******************************************************************************/
/*  php_cmd() - send a command to the script server                           */
/******************************************************************************/
char *php_cmd(char *php_command) {
	char *result_string;
	char command[BUFSIZE];
	int write_status;

	/* pad command with CR-LF */
	snprintf(command, sizeof(command)-1, "%s\r\n", php_command);

	thread_mutex_lock(LOCK_PHP);
	/* send command to the script server */
	write_status = write(php_pipes.php_write_fd, command, strlen(command));

	/* if write status is <= 0 then the script server may be hung */
	if (write_status <= 0) {
		cacti_log("ERROR: PHP Script Server communications lost, attempting to close and restart", SEV_ERROR, 0);
		php_close();
		if (!php_init()) {
			cacti_log("ERROR: The PHP Script Server could not be restarted, Script Server command to be ingnored for remainder of polling cycle", SEV_ERROR, 0);
		}
	}

	/* read the result from the php_command */
	result_string = php_readpipe();

	thread_mutex_unlock(LOCK_PHP);

	return result_string;
}

/******************************************************************************/
/*  php_readpipe - read a line from the PHP script server                     */
/******************************************************************************/
char *php_readpipe() {
	fd_set fds;
	int rescode, numfds;
	struct timeval timeout;
	char logmessage[LOGSIZE];
	char *result_string = (char *) malloc(BUFSIZE);

	/* set the result_string to all zeros */
	memset(result_string, 0, BUFSIZE);

	/* initialize file descriptors to review for input/output */
	FD_ZERO(&fds);
	FD_SET(php_pipes.php_read_fd,&fds);
	FD_SET(php_pipes.php_write_fd,&fds);

	if (php_pipes.php_read_fd > php_pipes.php_write_fd)
		numfds = php_pipes.php_read_fd + 1;
	else
		numfds = php_pipes.php_write_fd + 1;

	/* establish timeout of x seconds to have PHP script server respond */
	timeout.tv_sec = set.script_timeout;
	timeout.tv_usec = 0;

	/* check to see which pipe talked and take action
	 * should only be the READ pipe */
	switch (select(numfds, &fds, NULL, NULL, &timeout)) {
	case -1:
		cacti_log("Fatal select() error", SEV_ERROR, 0);
		snprintf(result_string, BUFSIZE-1, "%s", "U");
		break;
	case 0:
		cacti_log("The PHP Script Server Did not Respond in Time", SEV_ERROR, 0);
		snprintf(result_string, BUFSIZE-1, "%s", "U");

		/* restart the script server because of error */
		php_close();
		php_init();

		break;
	default:
		rescode = read(php_pipes.php_read_fd, result_string, BUFSIZE);
		if (rescode == 0) {
			snprintf(result_string, BUFSIZE-1, "%s", "U");
		}
	}

	return result_string;
}

/******************************************************************************/
/*  php_init() - initialize the PHP script server                             */
/******************************************************************************/
int php_init() {
	int  cacti2php_pdes[2];
	int  php2cacti_pdes[2];
	pid_t  pid;
	char logmessage[LOGSIZE];
	char poller_id[11];
	char *argv[5];
	int  cancel_state;
	char *result_string;

	/* initialize the php process id */
	set.php_sspid = 0;
	
	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("PHP Script Server Routine Started", SEV_DEBUG, 0);
	}

	/* create the output pipes from cactid to php*/
	if (pipe(cacti2php_pdes) < 0) {
		cacti_log("Could not allocate php server pipes", SEV_ERROR, 0);
		return FALSE;
	}

	/* create the input pipes from php to cactid */
	if (pipe(php2cacti_pdes) < 0) {
		cacti_log("Could not allocate php server pipes", SEV_ERROR, 0);
		return FALSE;
	}

	/* disable thread cancellation from this point forward. */
	pthread_setcancelstate(PTHREAD_CANCEL_DISABLE, &cancel_state);

	/* establish arguments for script server execution */
	argv[0] = set.path_php;
	argv[1] = set.path_php_server;
	argv[2] = "cactid";
	snprintf(poller_id, sizeof(poller_id)-1, "%d", set.poller_id);
	argv[3] = poller_id;
	argv[4] = NULL;

	/* fork a child process */
	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("PHP Script Server About to FORK Child Process", SEV_DEBUG, 0);
	}

	pid = fork();

	/* check the pid status and process as required */
	switch (pid) {
		case -1: /* ERROR: Could not fork() */
			close(php2cacti_pdes[0]);
			close(php2cacti_pdes[1]);
			close(cacti2php_pdes[0]);
			close(cacti2php_pdes[1]);

			cacti_log("Could not fork php script server", SEV_ERROR, 0);
			pthread_setcancelstate(cancel_state, NULL);

			return FALSE;
			/* NOTREACHED */
		case 0:	/* SUCCESS: I am now the child */
			/* set the standard input/output channels of the new process.  */
			dup2(cacti2php_pdes[0], STDIN_FILENO);
			dup2(php2cacti_pdes[1], STDOUT_FILENO);

			/* close unneeded Pipes */
			(void)close(php2cacti_pdes[0]);
			(void)close(php2cacti_pdes[1]);
			(void)close(cacti2php_pdes[0]);
			(void)close(cacti2php_pdes[1]);

			/* start the php script server process */
			execve(argv[0], argv, environ);
			_exit(127);
			/* NOTREACHED */
		default: /* I am the parent process */
			if (set.verbose >= POLLER_VERBOSITY_DEBUG) {
				cacti_log("PHP Script Server Child FORK Success", SEV_DEBUG, 0);
			}
			set.php_sspid = pid;
	}

	/* Parent */
	/* close unneeded pipes */
	close(cacti2php_pdes[0]);
	close(php2cacti_pdes[1]);

	php_pipes.php_write_fd = cacti2php_pdes[1];
	php_pipes.php_read_fd = php2cacti_pdes[0];

	/* restore caller's cancellation state. */
	pthread_setcancelstate(cancel_state, NULL);

	/* check pipe to insure startup took place */
	result_string = php_readpipe();

	if ((set.verbose >= POLLER_VERBOSITY_DEBUG) && (strstr(result_string, "Started"))) {
		cacti_log("Confirmed PHP Script Server Running", SEV_DEBUG, 0);
	}

	free(result_string);

	return TRUE;
}

/******************************************************************************/
/*  php_close - close the pipes and wait for the status of the child.         */
/******************************************************************************/
void php_close() {
	char logmessage[LOGSIZE];

	if (set.verbose == POLLER_VERBOSITY_DEBUG) {
		cacti_log("PHP Script Server Shutdown Started", SEV_DEBUG, 0);
	}

	if (set.php_sspid) {
		/* tell the script server to close */
		write(php_pipes.php_write_fd, "quit\r\n", sizeof("quit\r\n"));

		/* end the php script server process */
		kill(set.php_sspid, SIGKILL);

		/* close file descriptors */
		close(php_pipes.php_write_fd);
		close(php_pipes.php_read_fd);
	}
}
