/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2002-2007 The Cacti Group                                 |
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
 | spine: a backend data gatherer for cacti                                |
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

#include "common.h"
#include "spine.h"

/*! \fn int ping_host(host_t *host, ping_t *ping)
 *  \brief ping a host to determine if it is reachable for polling
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *
 *  This function pings a host using the method specified within the system
 *  configuration and then returns the host status to the calling function.
 *
 *  \return HOST_UP if the host is reachable, HOST_DOWN otherwise.
 */
int ping_host(host_t *host, ping_t *ping) {
	int ping_result;
	int snmp_result;

	/* snmp pinging has been selected at a minimum */
	ping_result = 0;
	snmp_result = 0;

	/* icmp/tcp/udp ping test */
	if ((host->availability_method == AVAIL_SNMP_AND_PING) || (host->availability_method == AVAIL_PING)) {
		if (host->ping_method == PING_ICMP) {
			/* set and then test for asroot */
			#ifndef __CYGWIN__
			seteuid(0);

			if (geteuid() != 0) {
				host->ping_method = PING_UDP;
				SPINE_LOG_DEBUG(("WARNING: Falling back to UDP Ping due to not running asroot.  Please use \"chmod xxx0 /usr/bin/spine\" to resolve.\n"));
			}
			#endif
		}

		if (!strstr(host->hostname, "localhost")) {
			if (host->ping_method == PING_ICMP) {
				ping_result = ping_icmp(host, ping);

				/* give up root privileges */
				#ifndef __CYGWIN__
				seteuid(getuid());
				#endif
			}else if (host->ping_method == PING_UDP) {
				ping_result = ping_udp(host, ping);
			}else if (host->ping_method == PING_TCP) {
				ping_result = ping_tcp(host, ping);
			}
		}else{
			snprintf(ping->ping_status, 50, "0.000");
			snprintf(ping->ping_response, SMALL_BUFSIZE, "PING: Host does not require ping");
			ping_result = HOST_UP;
		}
	}

	/* snmp test */
	if ((host->availability_method == AVAIL_SNMP) ||
		((host->availability_method == AVAIL_SNMP_AND_PING) && (ping_result != HOST_UP))) {
		snmp_result = ping_snmp(host, ping);
	}

	switch (host->availability_method) {
		case AVAIL_SNMP_AND_PING:
			if (strlen(host->snmp_community) == 0) {
				if (ping_result == HOST_UP) {
					return HOST_UP;
				}else{
					return HOST_DOWN;
				}
			}

			if (snmp_result == HOST_UP) {
				return HOST_UP;
			}

			if (ping_result == HOST_UP) {
				return HOST_UP;
			}else{
				return HOST_DOWN;
			}
		case AVAIL_SNMP:
			if (snmp_result == HOST_UP) {
				return HOST_UP;
			}else{
				return HOST_DOWN;
			}
		case AVAIL_PING:
			if (ping_result == HOST_UP) {
				return HOST_UP;
			}else{
				return HOST_DOWN;
			}
		case AVAIL_NONE:
			return HOST_UP;
		default:
			return HOST_DOWN;
	}
}

/*! \fn int ping_snmp(host_t *host, ping_t *ping)
 *  \brief ping a host using snmp sysUptime
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *
 *  This function pings a host using snmp.  It polls sysUptime by default.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return HOST_UP if the host is reachable, HOST_DOWN otherwise.
 *
 */
int ping_snmp(host_t *host, ping_t *ping) {
	char *poll_result;
	char *oid;
	int num_oids_checked = 0;
	double begin_time, end_time, total_time;
	double one_thousand = 1000.00;

	if (strlen(host->snmp_community) != 0) {
		/* by default, we look at sysUptime */
		if ((oid = strdup(".1")) == NULL) {
			die("ERROR: malloc(): strdup() oid ping.c failed");
		}

		/* record start time */
		retry:
		begin_time = get_time_as_double();

		poll_result = snmp_getnext(host, oid);

		/* record end time */
		end_time = get_time_as_double();

		free(oid);

		total_time = (end_time - begin_time) * one_thousand;

		if ((strlen(poll_result) == 0) || IS_UNDEFINED(poll_result)) {
			if (num_oids_checked > 1) {
				if (num_oids_checked == 0) {
					/* use sysUptime as a backup if the generic OID fails */
					if ((oid = strdup(".1.3.6.1.2.1.1.3.0")) == NULL) {
						die("ERROR: malloc(): strdup() oid ping.c failed");
					}
				}else{
					/* use sysDescription as a backup if sysUptime fails */
					if ((oid = strdup(".1.3.6.1.2.1.1.1.0")) == NULL) {
						die("ERROR: malloc(): strdup() oid ping.c failed");
					}
				}

				free(poll_result);
				num_oids_checked++;
				goto retry;
			}else{
				snprintf(ping->snmp_response, SMALL_BUFSIZE, "Host did not respond to SNMP");
				free(poll_result);
				return HOST_DOWN;
			}
		}else{
			snprintf(ping->snmp_response, SMALL_BUFSIZE, "Host responded to SNMP");
			snprintf(ping->snmp_status, 50, "%.5f", total_time);
			free(poll_result);
			return HOST_UP;
		}
	}else{
		snprintf(ping->snmp_status, 50, "0.00");
		snprintf(ping->snmp_response, SMALL_BUFSIZE, "Host does not require SNMP");
		return HOST_UP;
	}
}

/*! \fn int ping_icmp(host_t *host, ping_t *ping)
 *  \brief ping a host using an ICMP packet
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *
 *  This function pings a host using ICMP.  The ICMP packet contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return HOST_UP if the host is reachable, HOST_DOWN otherwise.
 *
 */
int ping_icmp(host_t *host, ping_t *ping) {
	int    icmp_socket;

	double begin_time, end_time, total_time;
	double one_thousand = 1000.00;
	struct timeval timeout;

	struct sockaddr_in recvname;
	struct sockaddr_in fromname;
	char   socket_reply[BUFSIZE];
	int    retry_count;
	char   *cacti_msg = "cacti-monitoring-system";
	int    packet_len;
	int    fromlen;
	int    return_code;
	fd_set socket_fds;

	static   unsigned int seq = 0;
	struct   icmphdr *icmp;
	struct   icmp *pkt;
	unsigned char *packet;
	char     *new_hostname;

	/* remove "tcp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(host->hostname);

	/* get ICMP socket */
	if ((icmp_socket = socket(AF_INET, SOCK_RAW, IPPROTO_ICMP)) == -1) {
		SPINE_LOG(("ERROR: ping_icmp: cannot open an ICMP socket\n"));
	}

	/* establish timeout value */
	timeout.tv_sec  = 0;
	timeout.tv_usec = host->ping_timeout * 1000;

	/* allocate the packet in memory */
	packet_len = ICMP_HDR_SIZE + strlen(cacti_msg);

	if (!(packet = malloc(packet_len))) {
		die("ERROR: Fatal malloc error: ping.c ping_icmp!");
	}
	memset(packet, 0, packet_len);

	/* set the memory of the ping address */
	memset(&fromname, 0, sizeof(struct sockaddr_in));
	memset(&recvname, 0, sizeof(struct sockaddr_in));

	icmp = (struct icmphdr*)packet;

	icmp->type = ICMP_ECHO;
	icmp->code = 0;
	icmp->un.echo.id = getpid() & 0xFFFF;

	/* lock set/get the sequence and unlock */
	thread_mutex_lock(LOCK_GHBN);
	icmp->un.echo.sequence = seq++;
	thread_mutex_unlock(LOCK_GHBN);
	
	if (gettimeofday((struct timeval*)(icmp+1), NULL) == -1) {
		die("ERROR: Function gettimeofday failed.  Exiting spine");
	}

	icmp->checksum = 0;
	memcpy(packet+ICMP_HDR_SIZE, cacti_msg, strlen(cacti_msg));
	icmp->checksum = get_checksum(packet, packet_len);

	/* hostname must be nonblank */
	if ((strlen(host->hostname) != 0) && (icmp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* set the socket send and receive timeout */
		setsockopt(icmp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));
		setsockopt(icmp_socket, SOL_SOCKET, SO_SNDTIMEO, (char*)&timeout, sizeof(timeout));

		/* get address of hostname */
		if (init_sockaddr(&fromname, new_hostname, 7)) {
			retry_count = 0;

			/* initialize file descriptor to review for input/output */
			FD_ZERO(&socket_fds);
			FD_SET(icmp_socket,&socket_fds);

			while (1) {
				if (retry_count >= host->ping_retries) {
					snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Ping timed out");
					snprintf(ping->ping_status, 50, "down");
					free(new_hostname);
					free(packet);
					close(icmp_socket);
					return HOST_DOWN;
				}

				/* record start time */
				begin_time = get_time_as_double();

				/* send packet to destination */
				return_code = sendto(icmp_socket, packet, packet_len, 0, (struct sockaddr *) &fromname, sizeof(fromname));

	   			fromlen = sizeof(fromname);

				/* check to see which socket talked */
				return_code = recvfrom(icmp_socket, socket_reply, BUFSIZE, MSG_WAITALL, (struct sockaddr *) &recvname, &fromlen);

				if (return_code < 0) {
					if (errno == EINTR) {
						/* call was interrupted by some system event */
						continue;
					}
				}else{
					/* record end time */
					end_time = get_time_as_double();

					/* caculate total time */
					total_time = (end_time - begin_time) * one_thousand;

					struct iphdr *iphdr = (struct iphdr *) socket_reply;

					pkt = (struct icmp *) (socket_reply + (iphdr->ihl << 2));

					if (fromname.sin_addr.s_addr == recvname.sin_addr.s_addr) {
						if ((pkt->icmp_type == ICMP_ECHOREPLY)) {
							if (total_time < host->ping_timeout) {
								snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Host is Alive");
								snprintf(ping->ping_status, 50, "%.5f", total_time);
								free(new_hostname);
								free(packet);
								close(icmp_socket);
								return HOST_UP;
							}
						}else{
							/* received a response other than an echo reply */
							continue;
						}
					}else{
						continue;
						/* another host responded */
					}
				}

				retry_count++;
				#ifndef SOLAR_THREAD
				usleep(1000);
				#endif
			}
		}else{
			snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Destination hostname invalid");
			snprintf(ping->ping_status, 50, "down");
			free(new_hostname);
			free(packet);
			close(icmp_socket);
			return HOST_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Destination address not specified");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		free(packet);
  		if (icmp_socket != -1) close(icmp_socket);
		return HOST_DOWN;
	}
}

/*! \fn int ping_udp(host_t *host, ping_t *ping)
 *  \brief ping a host using an UDP datagram
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *
 *  This function pings a host using UDP.  The UDP datagram contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return HOST_UP if the host is reachable, HOST_DOWN otherwise.
 *
 */
int ping_udp(host_t *host, ping_t *ping) {
	double begin_time, end_time, total_time;
	double one_thousand = 1000.00;
	struct timeval timeout;
	int    udp_socket;
	struct sockaddr_in servername;
	char   socket_reply[BUFSIZE];
	int    retry_count;
	char   request[BUFSIZE];
	int    request_len;
	int    return_code;
	fd_set socket_fds;
	char   *new_hostname;

	/* remove "udp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(host->hostname);

	/* establish timeout value */
	timeout.tv_sec  = 0;
	timeout.tv_usec = host->ping_timeout * 1000;

	/* initilize the socket */
	udp_socket = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP);

	/* hostname must be nonblank */
	if ((strlen(host->hostname) != 0) && (udp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* set the socket timeout */
		setsockopt(udp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));

		/* get address of hostname */
		if (init_sockaddr(&servername, new_hostname, host->ping_port)) {
			if (connect(udp_socket, (struct sockaddr *) &servername, sizeof(servername)) < 0) {
				snprintf(ping->ping_status, 50, "down");
				snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Cannot connect to host");
				free(new_hostname);
				close(udp_socket);
				return HOST_DOWN;
			}

			/* format packet */
			snprintf(request, BUFSIZE, "cacti-monitoring-system"); /* the actual test data */
			request_len = strlen(request);

			retry_count = 0;

			/* initialize file descriptor to review for input/output */
			FD_ZERO(&socket_fds);
			FD_SET(udp_socket,&socket_fds);

			while (1) {
				if (retry_count >= host->ping_retries) {
					snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Ping timed out");
					snprintf(ping->ping_status, 50, "down");
					free(new_hostname);
					close(udp_socket);
					return HOST_DOWN;
				}

				/* record start time */
				begin_time = get_time_as_double();

				/* send packet to destination */
				send(udp_socket, request, request_len, 0);

				/* wait for a response on the socket */
				select(FD_SETSIZE, &socket_fds, NULL, NULL, &timeout);

				/* record end time */
				end_time = get_time_as_double();

				/* check to see which socket talked */
				if (FD_ISSET(udp_socket, &socket_fds)) {
					return_code = read(udp_socket, socket_reply, BUFSIZE);
				}else{
					return_code = -10;
				}

				/* caculate total time */
				total_time = (end_time - begin_time) * one_thousand;

				SPINE_LOG_DEBUG(("DEBUG: The UDP Ping return_code was %i, errno was %i, total_time was %.4f\n", return_code, errno, (total_time*1000)));

				if ((return_code >= 0) || ((return_code == -1) && ((errno == ECONNRESET) || (errno == ECONNREFUSED)))) {
					if (total_time <= host->ping_timeout) {
						snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Host is Alive");
						snprintf(ping->ping_status, 50, "%.5f", total_time);
						free(new_hostname);
						close(udp_socket);
						return HOST_UP;
					}
				}

				retry_count++;
				#ifndef SOLAR_THREAD
				usleep(1000);
				#endif
			}
		}else{
			snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Destination hostname invalid");
			snprintf(ping->ping_status, 50, "down");
			free(new_hostname);
			close(udp_socket);
			return HOST_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Destination address invalid or unable to create socket");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		if (udp_socket != -1) close(udp_socket);
		return HOST_DOWN;
	}
}


/*! \fn int ping_tcp(host_t *host, ping_t *ping)
 *  \brief ping a host using an TCP syn
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *
 *  This function pings a host using TCP.  The TCP socket contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return HOST_UP if the host is reachable, HOST_DOWN otherwise.
 *
 */
int ping_tcp(host_t *host, ping_t *ping) {
	double begin_time, end_time, total_time;
	double one_thousand = 1000.00;
	struct timeval timeout;
	int    tcp_socket;
	struct sockaddr_in servername;
	int    retry_count;
	char   request[BUFSIZE];
	int    request_len;
	int    return_code;
	fd_set socket_fds;
	char   *new_hostname;

	/* remove "tcp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(host->hostname);

	/* establish timeout value */
	timeout.tv_sec  = 0;
	timeout.tv_usec = host->ping_timeout * 1000;

	/* initilize the socket */
	tcp_socket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

	/* hostname must be nonblank */
	if ((strlen(host->hostname) != 0) && (tcp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* set the socket timeout */
		setsockopt(tcp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));
		setsockopt(tcp_socket, SOL_SOCKET, SO_SNDTIMEO, (char*)&timeout, sizeof(timeout));

		/* get address of hostname */
		if (init_sockaddr(&servername, new_hostname, host->ping_port)) {
			/* first attempt a connect */
			retry_count = 0;

			while (1) {
				return_code = connect(tcp_socket, (struct sockaddr *) &servername, sizeof(servername));
				if (return_code < 0) {
					if (retry_count > host->ping_retries) { 
						snprintf(ping->ping_status, 50, "down");
						snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Cannot connect to host");
						free(new_hostname);
						close(tcp_socket);
						return HOST_DOWN;
					}else{
						retry_count++;
					}
				}else{
					break;
				}
			}

			/* format packet */
			snprintf(request, BUFSIZE, "cacti-monitoring-system"); /* the actual test data */
			request_len = strlen(request);

			retry_count = 0;

			/* initialize file descriptor to review for input/output */
			FD_ZERO(&socket_fds);
			FD_SET(tcp_socket,&socket_fds);

			while (1) {
				if (retry_count >= host->ping_retries) {
					snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Ping timed out");
					snprintf(ping->ping_status, 50, "down");
					free(new_hostname);
					close(tcp_socket);
					return HOST_DOWN;
				}

				/* record start time */
				begin_time = get_time_as_double();

				/* send packet to destination */
				return_code = send(tcp_socket, request, request_len, 0);

				/* record end time */
				end_time = get_time_as_double();

				/* caculate total time */
				total_time = (end_time - begin_time) * one_thousand;

				SPINE_LOG_DEBUG(("DEBUG: The TCP Ping return_code was %i, errno was %i, total_time was %.4f\n", return_code, errno, (total_time*1000)));

				if (return_code >= 0) {
					if (total_time <= host->ping_timeout) {
						snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Host is Alive");
						snprintf(ping->ping_status, 50, "%.5f", total_time);
						free(new_hostname);
						close(tcp_socket);
						return HOST_UP;
					}
				}

				retry_count++;
				#ifndef SOLAR_THREAD
				usleep(1000);
				#endif
			}
		}else{
			snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Destination hostname invalid");
			snprintf(ping->ping_status, 50, "down");
			free(new_hostname);
			close(tcp_socket);
			return HOST_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Destination address invalid or unable to create socket");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		if (tcp_socket != -1) close(tcp_socket);
		return HOST_DOWN;
	}
}

/*! \fn int init_sockaddr(struct sockaddr_in *name, const char *hostname, unsigned short int port)
 *  \brief converts a hostname to an internet address
 *
 *  \return TRUE if successful, FALSE otherwise.
 *
 */
int init_sockaddr(struct sockaddr_in *name, const char *hostname, unsigned short int port) {
	struct hostent *hostinfo;
	int    i;

	name->sin_family = AF_INET;
	name->sin_port   = htons (port);

	/* retry 3 times to contact host */
	i = 0;
	while (1) {
		thread_mutex_lock(LOCK_GHBN);
		hostinfo = gethostbyname(hostname);
		if (hostinfo == NULL) {
			SPINE_LOG(("WARNING: Unknown host %s\n", hostname));

			if (i > 3) {
				thread_mutex_unlock(LOCK_GHBN);
				return FALSE;
			}
			i++;
			#ifndef SOLAR_THREAD
			usleep(1000);
			#endif
		}else{
			name->sin_addr = *(struct in_addr *) hostinfo->h_addr;
			thread_mutex_unlock(LOCK_GHBN);
			return TRUE;
		}
	}
}

/*! \fn char *remove_tcp_udp_from_hostname(char *hostname)
 *  \brief removes 'TCP:' or 'UDP:' from a hostname required to ping
 *
 *  \return char hostname a trimmed hostname
 *
 */
char *remove_tcp_udp_from_hostname(char *hostname) {
	char *cleaned_hostname;

	if (!(cleaned_hostname = (char *) malloc(strlen(hostname)+1))) {
		die("ERROR: Fatal malloc error: ping.c remove_tcp_udp_from_hostname");
	}

	if (!strncasecmp(hostname, "TCP:", 4) ||
		!strncasecmp(hostname, "UDP:", 4)) {
		memcpy(cleaned_hostname, hostname+4, strlen(hostname)-4);
		cleaned_hostname[strlen(hostname)-4] = '\0';
	}else{
		strcpy(cleaned_hostname, hostname);
	}

	return(cleaned_hostname);
}

/*! \fn unsigned short get_checksum(void* buf, int len)
 *  \brief calculates a 16bit checksum of a packet buffer
 *  \param buf the input buffer to calculate the checksum of
 *  \param len the size of the input buffer
 *
 *  \return 16bit checksum of an input buffer of size len.
 *
 */
unsigned short get_checksum(void* buf, int len) {
	int nleft = len;
	int sum = 0;
	unsigned short answer;
	unsigned short* w = (unsigned short*)buf;

	while (nleft > 1) {
		sum += *w++;
		nleft -= 2;
	}
	if (nleft == 1)
		sum += *(unsigned char*)w;
	sum = (sum >> 16) + (sum & 0xffff);
	sum += (sum >> 16);
	answer = ~sum;				/* truncate to 16 bits */
	return answer;
}

/*! \fn void update_host_status(int status, host_t *host, ping_t *ping, int availability_method)
 *  \brief update the host table in Cacti with the result of the ping of the host.
 *  \param status the current poll status of the host, either HOST_UP, or HOST_DOWN
 *  \param host a pointer to the current host structure
 *  \param ping a pointer to the current hosts ping structure
 *  \param availability_method the method that was used to poll the host
 *
 *  This function will determine if the host is UP, DOWN, or RECOVERING based upon
 *  the ping result and it's current status.  It will update the Cacti database
 *  with the calculated status.
 *
 */
void update_host_status(int status, host_t *host, ping_t *ping, int availability_method) {
	int    issue_log_message = FALSE;
	double ping_time;
 	double hundred_percent = 100.00;
	char   current_date[40];

	time_t nowbin;
	struct tm now_time;
	struct tm *now_ptr;

	/* get time for poller_output table */
	if (time(&nowbin) == (time_t) - 1) {
		die("ERROR: Could not get time of day from time()");
	}
	localtime_r(&nowbin,&now_time);
	now_ptr = &now_time;

	strftime(current_date, 40, "%Y-%m-%d %H:%M", now_ptr);

	/* host is down */
	if (status == HOST_DOWN) {
		/* update total polls, failed polls and availability */
		host->failed_polls = host->failed_polls + 1;
		host->total_polls = host->total_polls + 1;
		host->availability = hundred_percent * (host->total_polls - host->failed_polls) / host->total_polls;

		/*determine the error message to display */
		switch (availability_method) {
		case AVAIL_SNMP_AND_PING:
			if (strlen(host->snmp_community) == 0) {
				snprintf(host->status_last_error, SMALL_BUFSIZE, "%s", ping->ping_response);
			}else {
				snprintf(host->status_last_error, SMALL_BUFSIZE,"%s, %s",ping->snmp_response,ping->ping_response);
			}
			break;
		case AVAIL_SNMP:
			if (strlen(host->snmp_community) == 0) {
				snprintf(host->status_last_error, SMALL_BUFSIZE, "%s", "Device does not require SNMP");
			}else {
				snprintf(host->status_last_error, SMALL_BUFSIZE, "%s", ping->snmp_response);
			}
			break;
		default:
			snprintf(host->status_last_error, SMALL_BUFSIZE, "%s", ping->ping_response);
		}

		/* determine if to send an alert and update remainder of statistics */
		if (host->status == HOST_UP) {
			/* increment the event failure count */
			host->status_event_count++;

			/* if it's time to issue an error message, indicate so */
			if (host->status_event_count >= set.ping_failure_count) {
				/* host is now down, flag it that way */
				host->status = HOST_DOWN;

				issue_log_message = TRUE;

				/* update the failure date only if the failure count is 1 */
				if (set.ping_failure_count == 1) {
					snprintf(host->status_fail_date, 40, "%s", current_date);
				}
			/* host is down, but not ready to issue log message */
			}else{
				/* host down for the first time, set event date */
				if (host->status_event_count == 1) {
					snprintf(host->status_fail_date, 40, "%s", current_date);
				}
			}
		/* host is recovering, put back in failed state */
		}else if (host->status == HOST_RECOVERING) {
			host->status_event_count = 1;
			host->status = HOST_DOWN;

		/* host was unknown and now is down */
		}else if (host->status == HOST_UNKNOWN) {
			host->status = HOST_DOWN;
			host->status_event_count = 0;
		}else{
			host->status_event_count++;
		}
	/* host is up!! */
	}else{
		/* update total polls and availability */
		host->total_polls = host->total_polls + 1;
		host->availability = hundred_percent * (host->total_polls - host->failed_polls) / host->total_polls;

		/* determine the ping statistic to set and do so */
		if (availability_method == AVAIL_SNMP_AND_PING) {
			if (strlen(host->snmp_community) == 0) {
				ping_time = atof(ping->ping_status);
			}else {
				/* calculate the average of the two times */
				ping_time = (atof(ping->snmp_status) + atof(ping->ping_status)) / 2;
			}
		}else if (availability_method == AVAIL_SNMP) {
			if (strlen(host->snmp_community) == 0) {
				ping_time = 0.000;
			}else {
				ping_time = atof(ping->snmp_status);
			}
		}else if (availability_method == AVAIL_NONE) {
			ping_time = 0.000;
		}else {
			ping_time = atof(ping->ping_status);
		}

		/* update times as required */
		host->cur_time = ping_time;

		/* maximum time */
		if (ping_time > host->max_time)
			host->max_time = ping_time;

		/* minimum time */
		if (ping_time < host->min_time)
			host->min_time = ping_time;

		/* average time */
		host->avg_time = (((host->total_polls-1-host->failed_polls)
			* host->avg_time) + ping_time) / (host->total_polls-host->failed_polls);

		/* the host was down, now it's recovering */
		if ((host->status == HOST_DOWN) || (host->status == HOST_RECOVERING )) {
			/* just up, change to recovering */
			if (host->status == HOST_DOWN) {
				host->status = HOST_RECOVERING;
				host->status_event_count = 1;
			}else{
				host->status_event_count++;
			}

			/* if it's time to issue a recovery message, indicate so */
			if (host->status_event_count >= set.ping_recovery_count) {
				/* host is up, flag it that way */
				host->status = HOST_UP;

				issue_log_message = TRUE;

				/* update the recovery date only if the recovery count is 1 */
				if (set.ping_recovery_count == 1) {
					snprintf(host->status_rec_date, 40, "%s", current_date);
				}

				/* reset the event counter */
				host->status_event_count = 0;
			/* host is recovering, but not ready to issue log message */
			}else{
				/* host recovering for the first time, set event date */
				if (host->status_event_count == 1) {
					snprintf(host->status_rec_date, 40, "%s", current_date);
				}
			}
		}else{
		/* host was unknown and now is up */
			host->status = HOST_UP;
			host->status_event_count = 0;
		}
	}
	/* if the user wants a flood of information then flood them */
	if (set.log_level >= POLLER_VERBOSITY_HIGH) {
		if ((host->status == HOST_UP) || (host->status == HOST_RECOVERING)) {
			/* log ping result if we are to use a ping for reachability testing */
			if (availability_method == AVAIL_SNMP_AND_PING) {
				SPINE_LOG_HIGH(("Host[%i] PING Result: %s\n", host->id, ping->ping_response));
				SPINE_LOG_HIGH(("Host[%i] SNMP Result: %s\n", host->id, ping->snmp_response));
			}else if (availability_method == AVAIL_SNMP) {
				if (strlen(host->snmp_community) == 0) {
					SPINE_LOG_HIGH(("Host[%i] SNMP Result: Device does not require SNMP\n", host->id));
				}else{
					SPINE_LOG_HIGH(("Host[%i] SNMP Result: %s\n", host->id, ping->snmp_response));
				}
			}else if (availability_method == AVAIL_NONE) {
				SPINE_LOG_HIGH(("Host[%i] No Host Availability Method Selected\n", host->id));
			}else{
				SPINE_LOG_HIGH(("Host[%i] PING: Result %s\n", host->id, ping->ping_response));
			}
		}else{
			if (availability_method == AVAIL_SNMP_AND_PING) {
				SPINE_LOG_HIGH(("Host[%i] PING Result: %s\n", host->id, ping->ping_response));
				SPINE_LOG_HIGH(("Host[%i] SNMP Result: %s\n", host->id, ping->snmp_response));
			}else if (availability_method == AVAIL_SNMP) {
				SPINE_LOG_HIGH(("Host[%i] SNMP Result: %s\n", host->id, ping->snmp_response));
			}else if (availability_method == AVAIL_NONE) {
				SPINE_LOG_HIGH(("Host[%i] No Host Availability Method Selected\n", host->id));
			}else{
				SPINE_LOG_HIGH(("Host[%i] PING Result: %s\n", host->id, ping->ping_response));
			}
		}
	}

	/* if there is supposed to be an event generated, do it */
	if (issue_log_message) {
		if (host->status == HOST_DOWN) {
			SPINE_LOG(("Host[%i] Hostname[%s] ERROR: HOST EVENT: Host is DOWN Message: %s\n", host->id, host->hostname, host->status_last_error));
		}else{
			SPINE_LOG(("Host[%i] Hostnanme[%s] NOTICE: HOST EVENT: Host Returned from DOWN State\n", host->id, host->hostname));
		}
	}
}

