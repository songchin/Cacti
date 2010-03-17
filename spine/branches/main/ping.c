/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2002-2008 The Cacti Group                                 |
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

/*! \fn int ping_device(device_t *device, ping_t *ping)
 *  \brief ping a device to determine if it is reachable for polling
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *
 *  This function pings a device using the method specified within the system
 *  configuration and then returns the device status to the calling function.
 *
 *  \return DEVICE_UP if the device is reachable, DEVICE_DOWN otherwise.
 */
int ping_device(device_t *device, ping_t *ping) {
	int ping_result;
	int snmp_result;

	/* snmp pinging has been selected at a minimum */
	ping_result = 0;
	snmp_result = 0;

	/* icmp/tcp/udp ping test */
	if ((device->availability_method == AVAIL_SNMP_AND_PING) ||
		(device->availability_method == AVAIL_PING) ||
		(device->availability_method == AVAIL_SNMP_OR_PING)) {
		if (device->ping_method == PING_ICMP) {
			/* set and then test for asroot */
			#ifndef __CYGWIN__
			#ifdef SOLAR_PRIV
			priv_set_t *privset;
			char *p;

			/* Get the basic set */
			privset = priv_str_to_set("basic", ",", NULL);
			if (privset == NULL) {
				die("ERROR: Could not get basic privset from priv_str_to_set().");
			} else {
				p = priv_set_to_str(privset, ',', 0);
				SPINE_LOG_DEBUG(("DEBUG: Basic privset is: '%s'.", p != NULL ? p : "Unknown"));
			}

			/* Remove exec from the basic set */
			if (priv_delset(privset, PRIV_PROC_EXEC) < 0 ) {
				SPINE_LOG_DEBUG(("Warning: Deletion of PRIV_PROC_EXEC from privset failed: '%s'.", strerror(errno)));
			}

			/* Add priviledge to send/receive ICMP packets */
			if (priv_addset(privset, PRIV_NET_ICMPACCESS) < 0 ) {
				SPINE_LOG_DEBUG(("Warning: Addition of PRIV_NET_ICMPACCESS to privset failed: '%s'.", strerror(errno)));
			}

			/* Compute the set of privileges that are never needed */
			priv_inverse(privset);

			/* Remove the set of unneeded privs from Permitted (and by
			 * implication from Effective) */
			if (setppriv(PRIV_OFF, PRIV_PERMITTED, privset) < 0) {
				SPINE_LOG_DEBUG(("Warning: Dropping privileges from PRIV_PERMITTED failed: '%s'.", strerror(errno)));
			}

			/* Remove unneeded priv set from Limit to be safe */
			if (setppriv(PRIV_OFF, PRIV_LIMIT, privset) < 0) {
				SPINE_LOG_DEBUG(("Warning: Dropping privileges from PRIV_LIMIT failed: '%s'.", strerror(errno)));
			}

			boolean_t pe = priv_ineffect(PRIV_NET_ICMPACCESS);
			SPINE_LOG_DEBUG(("DEBUG: Privilege PRIV_NET_ICMPACCESS is: '%s'.", pe != 0 ? "Enabled" : "Disabled"));

			/* Free the privset */
			priv_freeset(privset);
			free(p);
			#else
			seteuid(0);

			if (geteuid() != 0) {
				device->ping_method = PING_UDP;
				SPINE_LOG_DEBUG(("WARNING: Falling back to UDP Ping due to not running asroot.  Please use \"chmod xxx0 /usr/bin/spine\" to resolve."));
			}
			#endif
			#endif
		}

		if (!strstr(device->hostname, "localhost")) {
			if (device->ping_method == PING_ICMP) {
				ping_result = ping_icmp(device, ping);

				/* give up root privileges */
				#if !(defined(__CYGWIN__) || defined(SOLAR_PRIV))
				seteuid(getuid());
				#endif
			}else if (device->ping_method == PING_UDP) {
				ping_result = ping_udp(device, ping);
			}else if (device->ping_method == PING_TCP) {
				ping_result = ping_tcp(device, ping);
			}
		}else{
			snprintf(ping->ping_status, 50, "0.000");
			snprintf(ping->ping_response, SMALL_BUFSIZE, "PING: Device does not require ping");
			ping_result = DEVICE_UP;
		}
	}

	/* snmp test */
	if ((device->availability_method == AVAIL_SNMP) ||
		(device->availability_method == AVAIL_SNMP_AND_PING) ||
		((device->availability_method == AVAIL_SNMP_OR_PING) && (ping_result != DEVICE_UP))) {
		snmp_result = ping_snmp(device, ping);
	}

	switch (device->availability_method) {
		case AVAIL_SNMP_AND_PING:
			if ((strlen(device->snmp_community) == 0) && (device->snmp_version < 3)) {
				if (ping_result == DEVICE_UP) {
					return DEVICE_UP;
				}else{
					return DEVICE_DOWN;
				}
			}

			if ((snmp_result == DEVICE_UP) && (ping_result == DEVICE_UP)) {
				return DEVICE_UP;
			}else{
				return DEVICE_DOWN;
			}
		case AVAIL_SNMP_OR_PING:
			if ((strlen(device->snmp_community) == 0) && (device->snmp_version < 3)) {
				if (ping_result == DEVICE_UP) {
					return DEVICE_UP;
				}else{
					return DEVICE_DOWN;
				}
			}

			if (snmp_result == DEVICE_UP) {
				return DEVICE_UP;
			}

			if (ping_result == DEVICE_UP) {
				return DEVICE_UP;
			}else{
				return DEVICE_DOWN;
			}
		case AVAIL_SNMP:
			if (snmp_result == DEVICE_UP) {
				return DEVICE_UP;
			}else{
				return DEVICE_DOWN;
			}
		case AVAIL_PING:
			if (ping_result == DEVICE_UP) {
				return DEVICE_UP;
			}else{
				return DEVICE_DOWN;
			}
		case AVAIL_NONE:
			return DEVICE_UP;
		default:
			return DEVICE_DOWN;
	}
}

/*! \fn int ping_snmp(device_t *device, ping_t *ping)
 *  \brief ping a device using snmp sysUptime
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *
 *  This function pings a device using snmp.  It polls sysUptime by default.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return DEVICE_UP if the device is reachable, DEVICE_DOWN otherwise.
 *
 */
int ping_snmp(device_t *device, ping_t *ping) {
	char *poll_result;
	char *oid;
	int num_oids_checked = 0;
	double begin_time, end_time, total_time;
	double one_thousand = 1000.00;

	if (device->snmp_session) {
		if ((strlen(device->snmp_community) != 0) || (device->snmp_version == 3)) {
			/* by default, we look at sysUptime */
			if ((oid = strdup(".1.3")) == NULL) {
				die("ERROR: malloc(): strdup() oid ping.c failed");
			}

			/* record start time */
			retry:
			begin_time = get_time_as_double();

			poll_result = snmp_getnext(device, oid);

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
					snprintf(ping->snmp_response, SMALL_BUFSIZE, "Device did not respond to SNMP");
					free(poll_result);
					return DEVICE_DOWN;
				}
			}else{
				snprintf(ping->snmp_response, SMALL_BUFSIZE, "Device responded to SNMP");
				snprintf(ping->snmp_status, 50, "%.5f", total_time);
				free(poll_result);
				return DEVICE_UP;
			}
		}else{
			snprintf(ping->snmp_status, 50, "0.00");
			snprintf(ping->snmp_response, SMALL_BUFSIZE, "Device does not require SNMP");
			return DEVICE_UP;
		}
	}else{
		snprintf(ping->snmp_status, 50, "0.00");
		snprintf(ping->snmp_response, SMALL_BUFSIZE, "Invalid SNMP Session");
		return DEVICE_DOWN;
	}
}

/*! \fn int ping_icmp(device_t *device, ping_t *ping)
 *  \brief ping a device using an ICMP packet
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *
 *  This function pings a device using ICMP.  The ICMP packet contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return DEVICE_UP if the device is reachable, DEVICE_DOWN otherwise.
 *
 */
int ping_icmp(device_t *device, ping_t *ping) {
	int    icmp_socket;

	double begin_time, end_time, total_time;
	double device_timeout;
	double one_thousand = 1000.00;
	struct timeval timeout;

	struct sockaddr_in recvname;
	struct sockaddr_in fromname;
	char   socket_reply[BUFSIZE];
	int    retry_count;
	char   *cacti_msg = "cacti-monitoring-system\0";
	int    packet_len;
	socklen_t    fromlen;
	ssize_t    return_code;
	fd_set socket_fds;

	static   unsigned int seq = 0;
	struct   icmp  *icmp;
	struct   ip    *ip;
	struct   icmp  *pkt;
	unsigned char  *packet;
	char     *new_hostname;

	/* remove "tcp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(device->hostname);

	/* get ICMP socket */
	retry_count = 0;
	while ( TRUE ) {
		if ((icmp_socket = socket(AF_INET, SOCK_RAW, IPPROTO_ICMP)) == -1) {
			usleep(500000);
			retry_count++;

			if (retry_count > 4) {
				snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Ping unable to create ICMP Socket");
				snprintf(ping->ping_status, 50, "down");
				free(new_hostname);
				return DEVICE_DOWN;
	
				break;
			}
		}else{
			break;
		}
	}

	/* convert the device timeout to a double precision number in seconds */
	device_timeout = device->ping_timeout;

	/* allocate the packet in memory */
	packet_len = ICMP_HDR_SIZE + strlen(cacti_msg);

	if (!(packet = malloc(packet_len))) {
		die("ERROR: Fatal malloc error: ping.c ping_icmp!");
	}
	memset(packet, 0, packet_len);

	/* set the memory of the ping address */
	memset(&fromname, 0, sizeof(struct sockaddr_in));
	memset(&recvname, 0, sizeof(struct sockaddr_in));

	icmp = (struct icmp*) packet;

	icmp->icmp_type = ICMP_ECHO;
	icmp->icmp_code = 0;
	icmp->icmp_id   = getpid() & 0xFFFF;

	/* lock set/get the sequence and unlock */
	thread_mutex_lock(LOCK_GHBN);
	icmp->icmp_seq = seq++;
	thread_mutex_unlock(LOCK_GHBN);

	icmp->icmp_cksum = 0;
	memcpy(packet+ICMP_HDR_SIZE, cacti_msg, strlen(cacti_msg));
	icmp->icmp_cksum = get_checksum(packet, packet_len);

	/* hostname must be nonblank */
	if ((strlen(device->hostname) != 0) && (icmp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* get address of hostname */
		if (init_sockaddr(&fromname, new_hostname, 7)) {
			retry_count = 0;
			total_time  = 0;
			begin_time  = 0;

			/* initialize file descriptor to review for input/output */
			FD_ZERO(&socket_fds);
			FD_SET(icmp_socket,&socket_fds);

			while (1) {
				if (retry_count > device->ping_retries) {
					snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Ping timed out");
					snprintf(ping->ping_status, 50, "down");
					free(new_hostname);
					free(packet);
					close(icmp_socket);
					return DEVICE_DOWN;
				}

				/* record start time */
				if (total_time == 0) {
					/* establish timeout value */
					timeout.tv_sec  = 0;
					timeout.tv_usec = device->ping_timeout * 1000;

					/* set the socket send and receive timeout */
					setsockopt(icmp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));
					setsockopt(icmp_socket, SOL_SOCKET, SO_SNDTIMEO, (char*)&timeout, sizeof(timeout));

					begin_time = get_time_as_double();
				}else{
					/* decrement the timeout value by the total time */
					timeout.tv_usec = (device->ping_timeout - total_time) * 1000;
				}

				/* send packet to destination */
				return_code = sendto(icmp_socket, packet, packet_len, 0, (struct sockaddr *) &fromname, sizeof(fromname));

				fromlen = sizeof(fromname);

				/* wait for a response on the socket */
				keep_listening:
				return_code = select(FD_SETSIZE, &socket_fds, NULL, NULL, &timeout);

				/* record end time */
				end_time = get_time_as_double();

				/* caculate total time */
				total_time = (end_time - begin_time) * one_thousand;

				/* check to see which socket talked */
				if (total_time < device_timeout) {
					return_code = recvfrom(icmp_socket, socket_reply, BUFSIZE, MSG_WAITALL, (struct sockaddr *) &recvname, &fromlen);

					if (return_code < 0) {
						if (errno == EINTR) {
							/* call was interrupted by some system event */
							goto keep_listening;
						}
					}else{
						ip  = (struct ip *) socket_reply;
						pkt = (struct icmp *)  (socket_reply + (ip->ip_hl << 2));

						if (fromname.sin_addr.s_addr == recvname.sin_addr.s_addr) {
							if ((pkt->icmp_type == ICMP_ECHOREPLY)) {
								SPINE_LOG_DEBUG(("Device[%i] DEBUG: ICMP Device Alive, Try Count:%i, Time:%.4f ms", device->id, retry_count+1, (total_time)));
								snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Device is Alive");
								snprintf(ping->ping_status, 50, "%.5f", total_time);
								free(new_hostname);
								free(packet);
								close(icmp_socket);
								return DEVICE_UP;
							}else{
								/* received a response other than an echo reply */
								if (total_time > device_timeout) {
									retry_count++;
									total_time = 0;
								}

								continue;
							}
						}else{
							/* another device responded */
							goto keep_listening;
						}
					}
				}

				total_time = 0;
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
			return DEVICE_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "ICMP: Destination address not specified");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		free(packet);
		if (icmp_socket != -1) close(icmp_socket);
		return DEVICE_DOWN;
	}
}

/*! \fn int ping_udp(device_t *device, ping_t *ping)
 *  \brief ping a device using an UDP datagram
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *
 *  This function pings a device using UDP.  The UDP datagram contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return DEVICE_UP if the device is reachable, DEVICE_DOWN otherwise.
 *
 */
int ping_udp(device_t *device, ping_t *ping) {
	double begin_time, end_time, total_time;
	double device_timeout;
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

	/* set total time */
	total_time = 0;

	/* remove "udp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(device->hostname);

	/* convert the device timeout to a double precision number in seconds */
	device_timeout = device->ping_timeout;

	/* initilize the socket */
	udp_socket = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP);

	/* hostname must be nonblank */
	if ((strlen(device->hostname) != 0) && (udp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* set the socket timeout */
		setsockopt(udp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));
		setsockopt(udp_socket, SOL_SOCKET, SO_SNDTIMEO, (char*)&timeout, sizeof(timeout));

		/* get address of hostname */
		if (init_sockaddr(&servername, new_hostname, device->ping_port)) {
			if (connect(udp_socket, (struct sockaddr *) &servername, sizeof(servername)) < 0) {
				snprintf(ping->ping_status, 50, "down");
				snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Cannot connect to device");
				free(new_hostname);
				close(udp_socket);
				return DEVICE_DOWN;
			}

			/* format packet */
			snprintf(request, BUFSIZE, "cacti-monitoring-system"); /* the actual test data */
			request_len = strlen(request);

			retry_count = 0;

			/* initialize file descriptor to review for input/output */
			FD_ZERO(&socket_fds);
			FD_SET(udp_socket,&socket_fds);

			while (1) {
				if (retry_count > device->ping_retries) {
					snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Ping timed out");
					snprintf(ping->ping_status, 50, "down");
					free(new_hostname);
					close(udp_socket);
					return DEVICE_DOWN;
				}

				/* record start time */
				begin_time = get_time_as_double();

				/* establish timeout value */
				if (device->ping_timeout >= 1000) {
					timeout.tv_sec  = rint(floor(device_timeout / 1000));
					timeout.tv_usec = (timeout.tv_sec * 1000000) - (device->ping_timeout * 1000);
				}else{
					timeout.tv_sec  = 0;
					timeout.tv_usec = (device->ping_timeout * 1000);
				}

				/* send packet to destination */
				send(udp_socket, request, request_len, 0);

				/* wait for a response on the socket */
				wait_more:
				return_code = select(FD_SETSIZE, &socket_fds, NULL, NULL, &timeout);

				/* record end time */
				end_time = get_time_as_double();

				/* caculate total time */
				total_time = (end_time - begin_time) * one_thousand;

				/* check to see which socket talked */
				if (return_code > 0) {
					if (FD_ISSET(udp_socket, &socket_fds)) {
						return_code = read(udp_socket, socket_reply, BUFSIZE);

						if ((return_code == -1) && ((errno == ECONNRESET) || (errno == ECONNREFUSED))) {
							SPINE_LOG_DEBUG(("Device[%i] DEBUG: UDP Device Alive, Try Count:%i, Time:%.4f ms", device->id, retry_count+1, (total_time)));
							snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Device is Alive");
							snprintf(ping->ping_status, 50, "%.5f", total_time);
							free(new_hostname);
							close(udp_socket);
							return DEVICE_UP;
						}
					}
				}else if (return_code == -1) {
					if (errno == EINTR) {
						/* interrupted, try again */
						goto wait_more;
					}else{
						snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Device is Down");
						snprintf(ping->ping_status, 50, "%.5f", total_time);
						free(new_hostname);
						close(udp_socket);
						return DEVICE_DOWN;
					}
				}else{
					/* timeout */
				}

				SPINE_LOG_DEBUG(("Device[%i] DEBUG: UDP Timeout, Try Count:%i, Time:%.4f ms", device->id, retry_count+1, (total_time)));

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
			return DEVICE_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "UDP: Destination address invalid or unable to create socket");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		if (udp_socket != -1) close(udp_socket);
		return DEVICE_DOWN;
	}
}


/*! \fn int ping_tcp(device_t *device, ping_t *ping)
 *  \brief ping a device using an TCP syn
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *
 *  This function pings a device using TCP.  The TCP socket contains a marker
 *  to the "Cacti" application so that firewall's can be configured to allow.
 *  It will modify the ping structure to include the specifics of the ping results.
 *
 *  \return DEVICE_UP if the device is reachable, DEVICE_DOWN otherwise.
 *
 */
int ping_tcp(device_t *device, ping_t *ping) {
	double begin_time, end_time, total_time;
	double device_timeout;
	double one_thousand = 1000.00;
	struct timeval timeout;
	int    tcp_socket;
	struct sockaddr_in servername;
	int    retry_count;
	int    return_code;
	char   *new_hostname;

	/* remove "tcp:" from hostname */
	new_hostname = remove_tcp_udp_from_hostname(device->hostname);

	/* convert the device timeout to a double precision number in seconds */
	device_timeout = device->ping_timeout;

	/* establish timeout value */
	if (device->ping_timeout >= 1000) {
		timeout.tv_sec  = rint(floor(device_timeout / 1000));
		timeout.tv_usec = (timeout.tv_sec * 1000000) - (device->ping_timeout * 1000);
	}else{
		timeout.tv_sec  = 0;
		timeout.tv_usec = (device->ping_timeout * 1000);
	}

	/* initilize the socket */
	tcp_socket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

	/* hostname must be nonblank */
	if ((strlen(device->hostname) != 0) && (tcp_socket != -1)) {
		/* initialize variables */
		snprintf(ping->ping_status, 50, "down");
		snprintf(ping->ping_response, SMALL_BUFSIZE, "default");

		/* set the socket timeout */
		setsockopt(tcp_socket, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout));
		setsockopt(tcp_socket, SOL_SOCKET, SO_SNDTIMEO, (char*)&timeout, sizeof(timeout));

		/* get address of hostname */
		if (init_sockaddr(&servername, new_hostname, device->ping_port)) {
			/* first attempt a connect */
			retry_count = 0;

			while (1) {
				/* record start time */
				begin_time  = get_time_as_double();

				/* make the connection */
				return_code = connect(tcp_socket, (struct sockaddr *) &servername, sizeof(servername));

				/* record end time */
				end_time = get_time_as_double();

				/* caculate total time */
				total_time = (end_time - begin_time) * one_thousand;

				if (((return_code == -1) && (errno == ECONNREFUSED)) ||
					(return_code == 0)) {
					SPINE_LOG_DEBUG(("Device[%i] DEBUG: TCP Device Alive, Try Count:%i, Time:%.4f ms", device->id, retry_count+1, (total_time)));
					snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Device is Alive");
					snprintf(ping->ping_status, 50, "%.5f", total_time);
					free(new_hostname);
					close(tcp_socket);
					return DEVICE_UP;
				}else{
               		#if defined(__CYGWIN__)
					snprintf(ping->ping_status, 50, "down");
					snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Cannot connect to device");
					free(new_hostname);
					close(tcp_socket);
					return DEVICE_DOWN;
					#else
					if (retry_count > device->ping_retries) {
						snprintf(ping->ping_status, 50, "down");
						snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Cannot connect to device");
						free(new_hostname);
						close(tcp_socket);
						return DEVICE_DOWN;
					}else{
						retry_count++;
					}
					#endif
				}
			}
		}else{
			snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Destination hostname invalid");
			snprintf(ping->ping_status, 50, "down");
			free(new_hostname);
			close(tcp_socket);
			return DEVICE_DOWN;
		}
	}else{
		snprintf(ping->ping_response, SMALL_BUFSIZE, "TCP: Destination address invalid or unable to create socket");
		snprintf(ping->ping_status, 50, "down");
		free(new_hostname);
		if (tcp_socket != -1) close(tcp_socket);
		return DEVICE_DOWN;
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
	#if !defined(H_ERRNO_DECLARED) && !defined(_AIX)
	extern int h_errno;
	#endif

	name->sin_family = AF_INET;
	name->sin_port   = htons (port);

	#ifdef HAVE_THREADSAFE_GETHOSTBYNAME
	retry:
	hostinfo = gethostbyname(hostname);

	if (!hostinfo) {
		if (h_errno == TRY_AGAIN) {
			goto retry;
		}else{
			return NULL;
		}
	}else{
		name->sin_addr = *(struct in_addr *) hostinfo->h_addr;
	}

	#else
	#ifdef HAVE_GETHOSTBYNAME_R_GLIBC
	struct hostent result_buf;
	size_t len = 1024;
	char   *buf;
	int    herr;
	int    rv;

	buf = malloc(len*sizeof(char));
	memset(buf, 0, sizeof(buf));

	while (1) {
		rv = gethostbyname_r(hostname, &result_buf, buf, len,
		&hostinfo, &herr);

		if (!hostinfo) {
			if (rv == ERANGE) {
				len *= 2;
				buf = realloc(buf, len*sizeof(char));

				continue;
			}else if (herr == TRY_AGAIN) {
				continue;
			}else{
				free(buf);
				return;
			}
		}else{
			break;
		}
	}

	name->sin_addr = *(struct in_addr *) hostinfo->h_addr;

	free(buf);
	#else
	#ifdef HAVE_GETHOSTBYNAME_R_SOLARIS
	size_t  len = 8192;
	char   *buf = NULL;
	struct hostent result;

	buf = malloc(len*sizeof(char));
	memset(buf, 0, sizeof(buf));

	while (1) {
		hostinfo = gethostbyname_r(hostname, &result, buf, len, &h_errno);
		if (!hostinfo) {
			if (errno == ERANGE) {
				len += 1024;
				buf = realloc(buf, len*sizeof(char));
				memset(buf, 0, sizeof(buf));

				continue;
			}else if (h_errno == TRY_AGAIN) {
				continue;
			}else{
				free(buf);
				return NULL;
			}
		}else{
			break;
		}
	}

	name->sin_addr = *(struct in_addr *) hostinfo->h_addr;

	free(buf);
	#else
	#ifdef HAVE_GETHOSTBYNAME_R_HPUX
	struct hostent hostent;
	struct hostent_data buf;
	int rv;

	rv = gethostbyname_r(hostname, &hostent, &buf);
	if (!rv) {
		name->sin_addr = *(struct in_addr *) hostent->h_addr;
	}

	#else
	retry:
	thread_mutex_lock(LOCK_GHBN);
	hostinfo = gethostbyname(hostname);
	if (!hostinfo) {
		thread_mutex_unlock(LOCK_GHBN);
		if (h_errno == TRY_AGAIN) {
			goto retry;
		}else{
			hostinfo = NULL;
		}
	}else{
		name->sin_addr = *(struct in_addr *) hostinfo->h_addr;
		thread_mutex_unlock(LOCK_GHBN);
	}
	#endif
	#endif
	#endif
	#endif

	if (hostinfo == NULL) {
		SPINE_LOG(("WARNING: Unknown device %s", hostname));
		return FALSE;
	}else{
		return TRUE;
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

/*! \fn unsigned short int get_checksum(void* buf, int len)
 *  \brief calculates a 16bit checksum of a packet buffer
 *  \param buf the input buffer to calculate the checksum of
 *  \param len the size of the input buffer
 *
 *  \return 16bit checksum of an input buffer of size len.
 *
 */
unsigned short int get_checksum(void* buf, int len) {
	int      nleft = len;
	int32_t  sum   = 0;
	unsigned short int answer;
	unsigned short int* w = (unsigned short int*)buf;
	unsigned short int odd_byte = 0;

	while (nleft > 1) {
		sum += *w++;
		nleft -= 2;
	}

	if (nleft == 1) {
   		*(unsigned char*)(&odd_byte) = *(unsigned char*)w;
   		sum += odd_byte;
	}

	sum    = (sum >> 16) + (sum & 0xffff);
	sum   += (sum >> 16);
	answer = ~sum;				/* truncate to 16 bits */

	return answer;
}

/*! \fn void update_device_status(int status, device_t *device, ping_t *ping, int availability_method)
 *  \brief update the device table in Cacti with the result of the ping of the device.
 *  \param status the current poll status of the device, either DEVICE_UP, or DEVICE_DOWN
 *  \param device a pointer to the current device structure
 *  \param ping a pointer to the current devices ping structure
 *  \param availability_method the method that was used to poll the device
 *
 *  This function will determine if the device is UP, DOWN, or RECOVERING based upon
 *  the ping result and it's current status.  It will update the Cacti database
 *  with the calculated status.
 *
 */
void update_device_status(int status, device_t *device, ping_t *ping, int availability_method) {
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

	/* device is down */
	if (status == DEVICE_DOWN) {
		/* update total polls, failed polls and availability */
		device->failed_polls = device->failed_polls + 1;
		device->total_polls = device->total_polls + 1;
		device->availability = hundred_percent * (device->total_polls - device->failed_polls) / device->total_polls;

		/*determine the error message to display */
		switch (availability_method) {
		case AVAIL_SNMP_OR_PING:
		case AVAIL_SNMP_AND_PING:
			if ((strlen(device->snmp_community) == 0) && (device->snmp_version < 3)) {
				snprintf(device->status_last_error, SMALL_BUFSIZE, "%s", ping->ping_response);
			}else {
				snprintf(device->status_last_error, SMALL_BUFSIZE,"%s, %s",ping->snmp_response,ping->ping_response);
			}
			break;
		case AVAIL_SNMP:
			if ((strlen(device->snmp_community) == 0) && (device->snmp_version < 3)) {
				snprintf(device->status_last_error, SMALL_BUFSIZE, "%s", "Device does not require SNMP");
			}else {
				snprintf(device->status_last_error, SMALL_BUFSIZE, "%s", ping->snmp_response);
			}
			break;
		default:
			snprintf(device->status_last_error, SMALL_BUFSIZE, "%s", ping->ping_response);
		}

		/* determine if to send an alert and update remainder of statistics */
		if (device->status == DEVICE_UP) {
			/* increment the event failure count */
			device->status_event_count++;

			/* if it's time to issue an error message, indicate so */
			if (device->status_event_count >= set.ping_failure_count) {
				/* device is now down, flag it that way */
				device->status = DEVICE_DOWN;

				issue_log_message = TRUE;

				/* update the failure date only if the failure count is 1 */
				if (set.ping_failure_count == 1) {
					snprintf(device->status_fail_date, 40, "%s", current_date);
				}
			/* device is down, but not ready to issue log message */
			}else{
				/* device down for the first time, set event date */
				if (device->status_event_count == 1) {
					snprintf(device->status_fail_date, 40, "%s", current_date);
				}
			}
		/* device is recovering, put back in failed state */
		}else if (device->status == DEVICE_RECOVERING) {
			device->status_event_count = 1;
			device->status = DEVICE_DOWN;

		/* device was unknown and now is down */
		}else if (device->status == DEVICE_UNKNOWN) {
			device->status = DEVICE_DOWN;
			device->status_event_count = 0;
		}else{
			device->status_event_count++;
		}
	/* device is up!! */
	}else{
		/* update total polls and availability */
		device->total_polls = device->total_polls + 1;
		device->availability = hundred_percent * (device->total_polls - device->failed_polls) / device->total_polls;

		/* determine the ping statistic to set and do so */
		if (availability_method == AVAIL_SNMP_AND_PING) {
			if (strlen(device->snmp_community) == 0) {
				ping_time = atof(ping->ping_status);
			}else {
				/* calculate the average of the two times */
				ping_time = (atof(ping->snmp_status) + atof(ping->ping_status)) / 2;
			}
		}else if (availability_method == AVAIL_SNMP) {
			if (strlen(device->snmp_community) == 0) {
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
		device->cur_time = ping_time;

		/* maximum time */
		if (ping_time > device->max_time)
			device->max_time = ping_time;

		/* minimum time */
		if (ping_time < device->min_time)
			device->min_time = ping_time;

		/* average time */
		device->avg_time = (((device->total_polls-1-device->failed_polls)
			* device->avg_time) + ping_time) / (device->total_polls-device->failed_polls);

		/* the device was down, now it's recovering */
		if ((device->status == DEVICE_DOWN) || (device->status == DEVICE_RECOVERING )) {
			/* just up, change to recovering */
			if (device->status == DEVICE_DOWN) {
				device->status = DEVICE_RECOVERING;
				device->status_event_count = 1;
			}else{
				device->status_event_count++;
			}

			/* if it's time to issue a recovery message, indicate so */
			if (device->status_event_count >= set.ping_recovery_count) {
				/* device is up, flag it that way */
				device->status = DEVICE_UP;

				issue_log_message = TRUE;

				/* update the recovery date only if the recovery count is 1 */
				if (set.ping_recovery_count == 1) {
					snprintf(device->status_rec_date, 40, "%s", current_date);
				}

				/* reset the event counter */
				device->status_event_count = 0;
			/* device is recovering, but not ready to issue log message */
			}else{
				/* device recovering for the first time, set event date */
				if (device->status_event_count == 1) {
					snprintf(device->status_rec_date, 40, "%s", current_date);
				}
			}
		}else{
		/* device was unknown and now is up */
			device->status = DEVICE_UP;
			device->status_event_count = 0;
		}
	}
	/* if the user wants a flood of information then flood them */
	if (set.log_level >= POLLER_VERBOSITY_HIGH) {
		if ((device->status == DEVICE_UP) || (device->status == DEVICE_RECOVERING)) {
			/* log ping result if we are to use a ping for reachability testing */
			if (availability_method == AVAIL_SNMP_AND_PING) {
				SPINE_LOG_HIGH(("Device[%i] PING Result: %s", device->id, ping->ping_response));
				SPINE_LOG_HIGH(("Device[%i] SNMP Result: %s", device->id, ping->snmp_response));
			}else if (availability_method == AVAIL_SNMP_OR_PING) {
				SPINE_LOG_HIGH(("Device[%i] PING Result: %s", device->id, ping->ping_response));
				SPINE_LOG_HIGH(("Device[%i] SNMP Result: %s", device->id, ping->snmp_response));
			}else if (availability_method == AVAIL_SNMP) {
				if ((strlen(device->snmp_community) == 0) && (device->snmp_version < 3)) {
					SPINE_LOG_HIGH(("Device[%i] SNMP Result: Device does not require SNMP", device->id));
				}else{
					SPINE_LOG_HIGH(("Device[%i] SNMP Result: %s", device->id, ping->snmp_response));
				}
			}else if (availability_method == AVAIL_NONE) {
				SPINE_LOG_HIGH(("Device[%i] No Device Availability Method Selected", device->id));
			}else{
				SPINE_LOG_HIGH(("Device[%i] PING: Result %s", device->id, ping->ping_response));
			}
		}else{
			if (availability_method == AVAIL_SNMP_AND_PING) {
				SPINE_LOG_HIGH(("Device[%i] PING Result: %s", device->id, ping->ping_response));
				SPINE_LOG_HIGH(("Device[%i] SNMP Result: %s", device->id, ping->snmp_response));
			}else if (availability_method == AVAIL_SNMP) {
				SPINE_LOG_HIGH(("Device[%i] SNMP Result: %s", device->id, ping->snmp_response));
			}else if (availability_method == AVAIL_NONE) {
				SPINE_LOG_HIGH(("Device[%i] No Device Availability Method Selected", device->id));
			}else{
				SPINE_LOG_HIGH(("Device[%i] PING Result: %s", device->id, ping->ping_response));
			}
		}
	}

	/* if there is supposed to be an event generated, do it */
	if (issue_log_message) {
		if (device->status == DEVICE_DOWN) {
			SPINE_LOG(("Device[%i] Hostname[%s] ERROR: DEVICE EVENT: Device is DOWN Message: %s", device->id, device->hostname, device->status_last_error));
		}else{
			SPINE_LOG(("Device[%i] Hostname[%s] NOTICE: DEVICE EVENT: Device Returned from DOWN State", device->id, device->hostname));
		}
	}
}
