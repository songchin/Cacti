<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

class Net_Ping
{
	var $socket;
	var $host;
	var $ping_status;
	var $ping_response;
	var $snmp_status;
	var $snmp_response;
	var $packet_loss;
	var $request;
	var $request_len;
	var $reply;
	var $timeout;
	var $retries;
	var $precision;
	var $time;
	var $timer_start_time;

	function Net_Ping()
	{
	}

	function close_socket()
	{
		socket_close($this->socket);
	}

	function start_time()
	{
		$this->timer_start_time = microtime();
	}

	function get_time($acc=2)
	{
		// format start time
		$start_time = explode (" ", $this->timer_start_time);
		$start_time = $start_time[1] + $start_time[0];
		// get and format end time
		$end_time = explode (" ", microtime());
		$end_time = $end_time[1] + $end_time[0];
		return number_format ($end_time - $start_time, $acc);
	}

	function build_udp_packet() {
		$data  = "cacti-monitoring-system"; // the actual test data

		// now lets build the actual icmp packet
		$this->request = chr(0) . chr(1) . chr(0) . $data . chr(0);
		$this->request_len = strlen($this->request);
	}

	function build_icmp_packet() {
		$data = "cacti-monitoring-system"; // the actual test data
		$type = "\x08"; // 8 echo message; 0 echo reply message
		$code = "\x00"; // always 0 for this program
		$chksm = "\xCE\x96"; // generate checksum for icmp request
		$id = "\x40\x00"; // we will have to work with this later
		$sqn = "\x00\x00"; // we will have to work with this later

		// now lets build the actual icmp packet
		$this->request = $type.$code.$chksm.$id.$sqn.$data;
		$this->request_len = strlen($this->request);
	}

	function ping_icmp()	{
		/* ping me */
		if ($this->host["hostname"]) {
			/* initialize variables */
			$this->ping_status = "down";
			$this->ping_response = _("ICMP Ping timed out");
			$this->packet_loss = 1;

			/* initialize the socket */
			$this->socket = socket_create(AF_INET, SOCK_RAW, 1);
			socket_set_block($this->socket);

			/* set the timeout */
			socket_set_option($this->socket,
				SOL_SOCKET,  // socket level
				SO_RCVTIMEO, // timeout option
				array(
					"sec"=>$this->timeout, // Timeout in seconds
					"usec"=>0  // I assume timeout in microseconds
				));

			if (@socket_connect($this->socket, $this->host["hostname"], NULL)) {
				// do nothing
			} else {
				$this->ping_response = _("Cannot connect to host");
				$this->ping_status   = "down";
				return false;
			}

			/* build the packet */
			$this->build_icmp_packet();

			$retry_count = 0;
			$success_count = 0;
			$total_time = 0;
			while (1) {
				if ($retry_count >= $this->retries) {
					$this->packet_loss = 1 - ($success_count / $retry_count);

					if ($this->packet_loss == 1) {
						$this->ping_status = "down";
						$this->ping_response = _("ICMP ping Timed out");
						return false;
					} else {
						$this->ping_response = _("Device is alive");
						$this->ping_status = $total_time / $success_count;
						return true;
					}
				}

				/* get start time */
				$this->start_time();

				socket_write($this->socket, $this->request, $this->request_len);
				$code = @socket_recv($this->socket, $this->reply, 256, 0);

				/* get the end time */
				$this->time = $this->get_time($this->precision);

				if ($code) {
					$total_time = $total_time + $this->time * 1000;
					$success_count = $success_count + 1;
				}

	            $retry_count++;
			}
			$this->close_socket();
		} else {
			$this->ping_status = "down";
			$this->ping_response = _("Destination address not specified");
			return false;
		}
	}

	function ping_snmp() {
		require_once(CACTI_BASE_PATH . "/lib/sys/snmp.php");

		/* initialize variables */
		$this->snmp_status = "down";
		$this->snmp_response = _("Device did not respond to SNMP");
		$this->packet_loss = 1;
		$output = "";

		/* get start time */
		$this->start_time();

		/* poll sysUptime for status */
		$retry_count = 0;
		$success_count = 0;
		$total_time = 0;
		while (1) {
			if ($retry_count >= $this->retries) {
				$this->packet_loss = 1 - ($success_count / $retry_count);
				if ($this->packet_loss == 1) {
					$this->snmp_status   = "down";
					$this->snmp_response = _("Device did not respond to SNMP");
					return false;
				} else {
					$this->snmp_response = _("Device responded to SNMP");
					$this->snmp_status = $total_time / $success_count;
					return true;
				}
			}

			$output = cacti_snmp_get($this->host["hostname"],
				$this->host["snmp_community"],
				".1.3.6.1.2.1.1.3.0" ,
				$this->host["snmp_version"],
				$this->host["snmpv3_auth_username"],
				$this->host["snmpv3_auth_password"],
				$this->host["snmpv3_auth_protocol"],
				$this->host["snmpv3_priv_passphrase"],
				$this->host["snmpv3_priv_protocol"],
				$this->host["snmp_port"],
				$this->host["snmp_timeout"],
				SNMP_CMDPHP);

			/* determine total time +- ~10% */
			$this->time = $this->get_time($this->precision);

			/* check result for uptime */
			if (!empty($output)) {
				/* calculte total time */
				$total_time = $total_time + $this->time * 1000;
				$success_count = $success_count + 1;
			}

			$retry_count++;
		}
	} /* ping_snmp */

	function ping_udp() {
		/* Device must be nonblank */
		if ($this->host["hostname"]) {
			/* initialize variables */
			$this->ping_status   = "down";
			$this->ping_response = "default";
			$this->packet_loss = 1;

			/* initilize the socket */
			$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			socket_set_block($this->socket);

			/* set the socket timeout */
			socket_set_option($this->socket,
				SOL_SOCKET,  // socket level
				SO_RCVTIMEO, // timeout option
				array(
					"sec"=>$this->timeout, // Timeout in seconds
					"usec"=>0  // I assume timeout in microseconds
				));

			if (@socket_connect($this->socket, $this->host["hostname"], 33439)) {
					// do nothing
			} else {
				$this->ping_status = "down";
				$this->ping_response = _("Cannot connect to host");
				return false;
			}

			/* format packet */
			$this->build_udp_packet();

			$retry_count = 0;
			$success_count = 0;
			$total_time = 0;
			while (1) {
				if ($retry_count >= $this->retries) {
					$this->packet_loss = 1 - ($success_count / $retry_count);

					if ($this->packet_loss == 1) {
						$this->ping_response = _("UDP ping timed out");
						$this->ping_status   = "down";
						return false;
					} else {
						$this->ping_response = _("Device is alive");
						$this->ping_status = $total_time / $success_count;
						return true;
					}
				}

				/* set start time */
				$this->start_time();

				/* send packet to destination */
				socket_write($this->socket, $this->request, $this->request_len);

				/* get packet response */
				$code = @socket_recv($this->socket, $this->reply, 256, 0);

				/* caculate total time */
				$this->time = $this->get_time($this->precision);

				if (($code) || (empty($code))) {
					if (($this->time*1000) <= $this->timeout) {
						$total_time = $total_time + $this->time * 1000;
						$success_count = $success_count + 1;
					}
				}
				$retry_count++;
			}
			$this->close_socket();
		} else {
			$this->ping_response = _("Destination address not specified");
			$this->ping_status   = "down";
			return false;
		}
	} /* end ping_udp */

	function ping($avail_method = AVAIL_SNMP_AND_PING, $ping_type = ICMP_PING, $timeout=500, $retries=3)
	{
		/* initialize variables */
		$ping_ping = true;
		$ping_snmp = true;

		$this->ping_status   = "down";
		$this->ping_response = _("Ping not performed due to setting.");
		$this->snmp_status   = "down";
		$this->snmp_response = _("SNMP not performed due to setting or ping result.");

		/* do parameter checking before call */
		/* apply defaults if parameters are spooky */
		if ((int)$avail_method <= 0) $avail_method=AVAIL_SNMP;
		if ((int)$ping_type <= 0) $ping_type=PING_UDP;

		if (((int)$retries <= 0) || ((int)$retries > 5))
			$this->retries = 2;
		else
			$this->retries = $retries;

		if ((int)$timeout <= 0)
			$this->timeout=500;
		else
			$this->timeout=$timeout;

		/* decimal precision is 0.0000 */
		$this->precision = 5;

		/* snmp pinging has been selected at a minimum */
		$ping_result = false;
		$snmp_result = false;

		/* icmp/udp ping test */
		if (($avail_method == AVAIL_SNMP_AND_PING) || ($avail_method == AVAIL_PING)) {
			if ($ping_type == PING_ICMP) {
				$ping_result = $this->ping_icmp();
			}else if ($ping_type == PING_UDP) {
				$ping_result = $this->ping_udp();
			}else if ($ping_type == PING_NONE) {
				$ping_result = true;
			}
		}

		/* snmp test */
		if (($avail_method == AVAIL_SNMP) || (($avail_method == AVAIL_SNMP_AND_PING) && ($ping_result == true))) {
			if ($this->host["snmp_community"] != "") {
				$snmp_result = $this->ping_snmp();
			}else{
				$snmp_result = true;
			}
		}else if (($avail_method == AVAIL_SNMP_AND_PING) && ($ping_result == false)) {
			$snmp_result = false;
		}

		switch ($avail_method) {
			case AVAIL_SNMP_AND_PING:
				if ($snmp_result)
					return true;
				if (!$ping_result)
					return false;
				else
					return false;
			case AVAIL_SNMP:
				if ($snmp_result)
					return true;
				else
					return false;
			case AVAIL_NONE:
				return true;
			case AVAIL_PING:
				if ($ping_result)
					return true;
				else
					return false;
			default:
				return false;
		}
	} /* end_ping */
}

?>