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
#include "util.h"
#include "snmp.h"

extern char **environ;

#ifdef USE_NET_SNMP
 #include <net-snmp-config.h>
 #include <net-snmp-includes.h>
#else
 #include <ucd-snmp/ucd-snmp-config.h>
 #include <ucd-snmp/ucd-snmp-includes.h>
 #include <ucd-snmp/transform_oids.h>
 #include <ucd-snmp/system.h>
 #include <mib.h>
#endif

#define OIDSIZE(p) (sizeof(p)/sizeof(oid))

void snmp_init(int host_id) {
	char cactid_session[10];

	#ifdef USE_NET_SNMP
	netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_PRINT_BARE_VALUE, 1);
	netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_QUICK_PRINT, 1);
	netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_NUMERIC_TIMETICKS, 1);
	#else
	ds_set_boolean(DS_LIBRARY_ID, DS_LIB_QUICK_PRINT, 1);
	ds_set_boolean(DS_LIBRARY_ID, DS_LIB_PRINT_BARE_VALUE, 1);
	ds_set_boolean(DS_LIBRARY_ID, DS_LIB_NUMERIC_TIMETICKS, 1);
	#endif
}

void snmp_host_init(host_t *current_host) {
	char logmessage[LOGSIZE];
	void *sessp = NULL;
	struct snmp_session session;
	oid anOID[MAX_OID_LEN];
	size_t anOID_len = MAX_OID_LEN;

	char hostname[BUFSIZE];

	/* initialize SNMP */
	snmp_init(current_host->id);
 	thread_mutex_lock(LOCK_SNMP);
  	snmp_sess_init(&session);
	thread_mutex_unlock(LOCK_SNMP);

	if (current_host->snmp_version == 2) {
		session.version = SNMP_VERSION_2c;
	}else if (current_host->snmp_version == 1) {
		session.version = SNMP_VERSION_1;
	}else {
		session.version = SNMP_VERSION_3;
	}

	/* net-snmp likes the hostname in 'host:port' format */
	snprintf(hostname, BUFSIZE, "%s:%i", current_host->hostname, current_host->snmp_port);

	session.peername = hostname;
	session.retries = 3;
	session.timeout = (current_host->snmp_timeout * 1000); /* net-snmp likes microseconds */

	if ((current_host->snmp_version == 2) || (current_host->snmp_version == 1)) {
		session.community = current_host->snmp_community;
		session.community_len = strlen(current_host->snmp_community);
	}else {
	    /* set the SNMPv3 user name */
	    session.securityName = strdup(current_host->snmpv3_auth_username);
	    session.securityNameLen = strlen(session.securityName);

		session.securityAuthKeyLen = USM_AUTH_KU_LEN;

	    /* set the authentication method to MD5 */
		if (!strcmp(current_host->snmpv3_auth_protocol,"MD5")) {
		    session.securityAuthProto = snmp_duplicate_objid(usmHMACMD5AuthProtocol, OIDSIZE(usmHMACMD5AuthProtocol));
		    session.securityAuthProtoLen = OIDSIZE(usmHMACMD5AuthProtocol);
		}else if (!strcmp(current_host->snmpv3_auth_protocol,"SHA")){
		    session.securityAuthProto = snmp_duplicate_objid(usmHMACSHA1AuthProtocol, OIDSIZE(usmHMACSHA1AuthProtocol));
		    session.securityAuthProtoLen = OIDSIZE(usmHMACSHA1AuthProtocol);
		}else {
			cacti_log("ERROR: SNMP: Error with SNMPv3 autorization protocol setting.\n");
		}

		if (!strcmp(current_host->snmpv3_priv_protocol,"[None]")) {
			session.securityPrivProto = usmNoPrivProtocol;
			session.securityPrivProtoLen = OIDSIZE(usmNoPrivProtocol);
		}else {
			if (!strcmp(current_host->snmpv3_priv_protocol,"DES")) {
				session.securityPrivProto = usmDESPrivProtocol;
				session.securityPrivProtoLen = OIDSIZE(usmDESPrivProtocol);
			#ifdef HAVE_AES
			} else if ((!strcmp(current_host->snmpv3_priv_protocol,"AES128"))
			#ifdef SNMP_VALIDATE_ERR
			/* 
			* In Net-SNMP before 5.2, the following symbols exist:
			* usmAES128PrivProtocol, usmAES192PrivProtocol, usmAES256PrivProtocol
			* In an effort to be more standards-compliant, 5.2 removed the last two.
			* As of 5.2, the symbols are:
			* usmAESPrivProtocol, usmAES128PrivProtocol
			* 
			* As we want this extension to compile on both versions, we use the latter
			* symbol on purpose, as it's defined to be the same as the former.
			*/
			|| (!strcmp(current_host->snmpv3_priv_protocol, "AES"))) {
				session.securityPrivProto = usmAESPrivProtocol;
				session.securityPrivProtoLen = OIDSIZE(usmAESPrivProtocol);
			#else
			) {
				session.securityPrivProto = usmAES128PrivProtocol;
				session.securityPrivProtoLen = OIDSIZE(usmAES128PrivProtocol);
			} else if (!strcmp(current_host->snmpv3_priv_protocol,"AES192")) {
				session.securityPrivProto = usmAES192PrivProtocol;
				session.securityPrivProtoLen = OIDSIZE(usmAES192PrivProtocol);
			} else if (!strcmp(current_host->snmpv3_priv_protocol,"AES256")) {
				session.securityPrivProto = usmAES256PrivProtocol;
				session.securityPrivProtoLen = OIDSIZE(usmAES256PrivProtocol);
			#endif
			#endif
			}else {			
				cacti_log("ERROR: SNMP: Error with SNMPv3 privacy protocol setting.\n");
			}
		}

		session.securityPrivProtoLen = sizeof(session.securityPrivProto)/sizeof(oid);
		session.securityPrivKeyLen = USM_PRIV_KU_LEN;

	    /* set the security level to authenticate, but not encrypted */
		if (!strcmp(current_host->snmpv3_priv_protocol,"[None]")) {
			session.securityLevel = SNMP_SEC_LEVEL_AUTHNOPRIV;
		}else {
			session.securityLevel = SNMP_SEC_LEVEL_AUTHPRIV;
		}

	    /* set the authentication key to the hashed version. The password must me at least 8 char */
	    if (generate_Ku(session.securityAuthProto, 
						session.securityAuthProtoLen,
						(u_char *) current_host->snmpv3_auth_password,
						strlen(current_host->snmpv3_auth_password),
	                    session.securityAuthKey,
	                    &(session.securityAuthKeyLen)) != SNMPERR_SUCCESS) {
	        cacti_log("ERROR: SNMP: Error generating SNMPv3 Ku from authentication pass phrase.\n");
		}
		/* set the privacy key to the correct hashed version */
		if (strcmp(current_host->snmpv3_priv_protocol,"[None]") != 0) {
		    if (generate_Ku(session.securityAuthProto, 
							session.securityAuthProtoLen,
							(u_char *) current_host->snmpv3_priv_passphrase,
							strlen(current_host->snmpv3_priv_passphrase),
	            	        session.securityPrivKey,
	                	    &session.securityPrivKeyLen) != SNMPERR_SUCCESS) {
		        cacti_log("ERROR: SNMP: Error generating SNMPv3 Ku from privacy pass phrase.\n");
			}
		}
	}

 	thread_mutex_lock(LOCK_SNMP);

	/* windows socket call */
	SOCK_STARTUP;

	/* open SNMP Session */
	sessp = snmp_sess_open(&session);

	thread_mutex_unlock(LOCK_SNMP);

	if (!sessp) {
		snprintf(logmessage, LOGSIZE, "ERROR: Problem initializing SNMP session '%s'\n", current_host->hostname);
		cacti_log(logmessage);
		current_host->snmp_session = NULL;
	}else{
		current_host->snmp_session = sessp;
	}
}

void snmp_host_cleanup(host_t *current_host) {
	snmp_sess_close(current_host->snmp_session);
	SOCK_CLEANUP;
}

char *snmp_get(host_t *current_host, char *snmp_oid) {
	struct snmp_pdu *pdu = NULL;
	struct snmp_pdu *response = NULL;
	oid anOID[MAX_OID_LEN];
	size_t anOID_len = MAX_OID_LEN;
	struct variable_list *vars = NULL;
	char logmessage[LOGSIZE];

	int status;

	char storedoid[BUFSIZE];

	char *result_string = (char *) malloc(BUFSIZE);

	anOID_len = MAX_OID_LEN;
	pdu = snmp_pdu_create(SNMP_MSG_GET);
	read_objid(snmp_oid, anOID, &anOID_len);

	strncpy(storedoid, snmp_oid, sizeof(storedoid));

	snmp_add_null_var(pdu, anOID, anOID_len);

	if (current_host->snmp_session != NULL) {
		status = snmp_sess_synch_response(current_host->snmp_session, pdu, &response);
	}else {
		status = STAT_DESCRIP_ERROR;
	}

	/* liftoff, successful poll, process it!! */
	if (status == STAT_SUCCESS && response->errstat == SNMP_ERR_NOERROR) {
		vars = response->variables;

		#ifdef USE_NET_SNMP
		snprint_value(result_string, BUFSIZE, anOID, anOID_len, vars);
		#else
		sprint_value(result_string, anOID, anOID_len, vars);
		#endif
	}

	if ((status == STAT_TIMEOUT) || (status != STAT_SUCCESS)) {
		current_host->ignore_host = 1;
		strncpy(result_string, "SNMP ERROR", BUFSIZE);
	}else if (!(status == STAT_SUCCESS && response->errstat == SNMP_ERR_NOERROR)) {
		snprintf(result_string, BUFSIZE, "%s", "U");
	}

	if (current_host->snmp_session != NULL) {
		if (response != NULL) {
			snmp_free_pdu(response);
		}
	}

	return result_string;
}
