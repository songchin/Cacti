<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'rebuild_poller_cache':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		repopulate_poller_cache();

		utilities();
		utilities_view_poller_cache();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_poller_cache':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities();
		utilities_view_poller_cache();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_logs':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities_view_logs();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'clear_logs':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		api_syslog_clear();
		utilities();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* -----------------------
    Utilities Functions
   ----------------------- */
function utilities_view_logs() {
	global $colors, $device_actions;

    define("MAX_DISPLAY_PAGES", 21);

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_syslog_current_page");
		kill_session_var("sess_syslog_filter");
		kill_session_var("sess_syslog_facility");
		kill_session_var("sess_syslog_severity");
		kill_session_var("sess_syslog_poller");
		kill_session_var("sess_syslog_host");
		kill_session_var("sess_syslog_username");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["facility"]);
		unset($_REQUEST["severity"]);
		unset($_REQUEST["poller"]);
		unset($_REQUEST["host"]);
		unset($_REQUEST["username"]);
	}

	if (isset($_REQUEST["clear_log_x"])) {
		api_syslog_clear();
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_syslog_current_page", "1");
	load_current_session_value("filter", "sess_syslog_filter", "");
	load_current_session_value("facility", "sess_syslog_facility", "-1");
	load_current_session_value("severity", "sess_syslog_severity", "-10");
	load_current_session_value("poller", "sess_syslog_poller", "-1");
	load_current_session_value("host", "sess_syslog_host", "-1");
	load_current_session_value("username", "sess_syslog_username", "-1");

	html_start_box("<strong>"._("Cacti Log Filters")."</strong>", "98%", $colors["header_background"], "3", "center", "");

	?>
	<tr bgcolor="<?php print $colors["filter_background"];?>">
		<form name="form_syslog_id">
		<input type="hidden" name="action" value="view_logs">
		<td>
			<table cellpadding="1" cellspacing="1">
				<tr>
					<td width="45">
						<?php echo _("Facility:");?>&nbsp;
					</td>
					<td width="1">
						<select name="facility">
							<option value="-1"<?php if ($_REQUEST["facility"] == -1) {?> selected<?php }?>>All</option>
							<option value="<?php echo FACIL_POLLER;?>"<?php if ($_REQUEST["facility"] == FACIL_POLLER) {?> selected<?php }?>>Poller</option>
							<option value="<?php echo FACIL_CMDPHP;?>"<?php if ($_REQUEST["facility"] == FACIL_CMDPHP) {?> selected<?php }?>>Cmdphp</option>
							<option value="<?php echo FACIL_CACTID;?>"<?php if ($_REQUEST["facility"] == FACIL_CACTID) {?> selected<?php }?>>Cactid</option>
							<option value="<?php echo FACIL_SCPTSVR;?>"<?php if ($_REQUEST["facility"] == FACIL_SCPTSVR) {?> selected<?php }?>>Scptsvr</option>
							<option value="<?php echo FACIL_AUTH;?>"<?php if ($_REQUEST["facility"] == FACIL_AUTH) {?> selected<?php }?>>Auth</option>
							<option value="<?php echo FACIL_WEBUI;?>"<?php if ($_REQUEST["facility"] == FACIL_WEBUI) {?> selected<?php }?>>WebUI</option>
							<option value="<?php echo FACIL_EXPORT;?>"<?php if ($_REQUEST["facility"] == FACIL_EXPORT) {?> selected<?php }?>>Export</option>
							<option value="<?php echo FACIL_UNKNOWN;?>"<?php if ($_REQUEST["facility"] == FACIL_UNKNOWN) {?> selected<?php }?>>Unknown</option>
						</select>
					</td>
					<td width="1">
						&nbsp;Severity:&nbsp;
					</td>
					<td width="1">
						<select name="severity">
							<option value="-10"<?php if ($_REQUEST["severity"] == -10) {?> selected<?php }?>>All</option>
							<option value="<?php echo SEV_INFO;?>"<?php if ($_REQUEST["severity"] == SEV_INFO) {?> selected<?php }?>>Info</option>
							<option value="<?php echo SEV_NOTICE;?>"<?php if ($_REQUEST["severity"] == SEV_NOTICE) {?> selected<?php }?>>Notice</option>
							<option value="<?php echo SEV_WARNING;?>"<?php if ($_REQUEST["severity"] == SEV_WARNING) {?> selected<?php }?>>Warning</option>
							<option value="<?php echo SEV_ERROR;?>"<?php if ($_REQUEST["severity"] == SEV_ERROR) {?> selected<?php }?>>Error</option>
							<option value="<?php echo SEV_CRITICAL;?>"<?php if ($_REQUEST["severity"] == SEV_CRITICAL) {?> selected<?php }?>>Critical</option>
							<option value="<?php echo SEV_ALERT;?>"<?php if ($_REQUEST["severity"] == SEV_ALERT) {?> selected<?php }?>>Alert</option>
							<option value="<?php echo SEV_EMERGENCY;?>"<?php if ($_REQUEST["severity"] == SEV_EMERGENCY) {?> selected<?php }?>>Emergency</option>
							<option value="<?php echo SEV_DEBUG;?>"<?php if ($_REQUEST["severity"] == SEV_DEBUG) {?> selected<?php }?>>Debug</option>
							<option value="<?php echo SEV_DEV;?>"<?php if ($_REQUEST["severity"] == SEV_DEV) {?> selected<?php }?>>Developer Debug</option>
						</select>
					</td>
					<td width="1">
						&nbsp;Username:&nbsp;
					</td>
					<td width="1">
						<select name="username">
							<option value="-1"<?php if ($_REQUEST["username"] == -1) {?> selected<?php }?>>All</option>
							<?php
							$usernames = db_fetch_assoc("select distinct username from syslog order by username");

							if ($usernames) {
								foreach ($usernames as $username) {
									print "<option value=\"" . $username['username'] . "\"";
									if ($_REQUEST["username"] == $username["username"]) {
										print " selected";
									}
									print ">" . $username["username"] . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td nowrap>
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_go.gif');?>" alt="Go" border="0" align="absmiddle" action="submit">
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_clear.gif');?>" name="clear" alt="Clear" border="0" align="absmiddle" action="submit">
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="1">
				<tr>
					<td width="45">
						Poller:&nbsp;
					</td>
					<td>
						<select name="poller">
							<option value="-1"<?php if ($_REQUEST["poller"] == -1) {?> selected<?php }?>>All</option>
							<option value="0"<?php if ($_REQUEST["poller"] == "0") {?> selected<?php }?>>System</option>
							<?php
							$pollers = db_fetch_assoc("select id,name from poller order by name");

							if ($pollers) {
								foreach ($pollers as $poller) {
									print "<option value=\"" . $poller['id'] . "\"";
									if ($_REQUEST["poller"] == $poller["id"]) {
										print " selected";
									}
									print ">" . $poller["name"] . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						&nbsp;Host:&nbsp;
					</td>
					<td>
						<select name="host">
							<option value="-1"<?php if ($_REQUEST["host"] == -1) {?> selected<?php }?>>All</option>
							<option value="0"<?php if ($_REQUEST["host"] == "0") {?> selected<?php }?>>System</option>
							<?php
							$hosts = db_fetch_assoc("select id,description from host order by description");

							if ($hosts) {
								foreach ($hosts as $host) {
									print "<option value=\"" . $host['id'] . "\"";
									if ($_REQUEST["host"] == $host["id"]) {
										print " selected";
									}
									print ">" . $host["description"] . "</option>\n";
								}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="1">
				<tr>
					<td width="45">
						Search:&nbsp;
					</td>
					<td colspan="2">
						<input type="text" name="filter" size="50" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
        $sql_where = "";
        if ($_REQUEST["filter"] != "") {
		$sql_where .= "where syslog.message like '%" . $_REQUEST["filter"] . "%'";
	}
	if ($_REQUEST["facility"] != -1) {
		$sql_where .= " and syslog.facility='" . $_REQUEST["facility"] . "'";
	}

	if ($_REQUEST["severity"] != -10) {
		$sql_where .= " and syslog.severity='" . $_REQUEST["severity"] . "'";
	}

	if ($_REQUEST["poller"] != -1) {
		$sql_where .= " and syslog.poller_id='" . $_REQUEST["poller"] . "'";
	}

	if ($_REQUEST["host"] != -1) {
		$sql_where .= " and syslog.host_id='" . $_REQUEST["host"] . "'";
	}

	if ($_REQUEST["username"] != -1) {
		$sql_where .= " and syslog.username='" . $_REQUEST["username"] . "'";
	}

	html_start_box("<strong>"._("Cacti Log Operations")."</strong>", "98%", $colors["header_background"], "3", "center", "");

	print "<form name='syslog_actions'>";
	print "<input type='hidden' name='action' value='view_logs'>";
	print "<td bgcolor='#" . $colors["console_menu_background"] . "'>";
	print "<input type='image' src='" . html_get_theme_images_path('button_clear_log.gif') . "' name='clear_log' alt='Clear Log' border='0' align='absmiddle' action='submit'>";
	print "&nbsp;<input type='image' src='" . html_get_theme_images_path('button_export.gif') . "' name='export' alt='Export Log' border='0' align='absmiddle' action='submit'>";
	print "</td>";
	print "<input type='hidden' name='page' value='1'>";
	print "</form>";

  	html_end_box();

	html_start_box("", "98%", $colors["header_background"], "3", "center", "");

	$total_rows = db_fetch_cell("select
		COUNT(syslog.id)
		from syslog
		$sql_where");

	$syslog_entries = db_fetch_assoc("SELECT
		syslog.id,
		syslog.logdate,
		syslog.facility,
		syslog.severity,
		poller.name as poller_name,
		poller.id as poller_id,
		host.description as host,
		syslog.username,
		syslog.message
		FROM (syslog LEFT JOIN host ON (syslog.host_id = host.id))
		LEFT JOIN poller ON (syslog.poller_id = poller.id)
		$sql_where
		order by syslog.logdate
		limit " . (read_config_option("num_rows_log")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_log"));

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_log"), $total_rows, "utilities.php?action=view_logs&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&severity=" . $_REQUEST["severity"]);

	$nav = "<tr bgcolor='#" . $colors["header_background"] . "'>
			<td colspan='7'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='utilities.php?action=view_logs&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= _("Previous"); if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
						</td>\n
						<td align='center' class='textHeaderDark'>
							Showing Rows " . ((read_config_option("num_rows_device")*($_REQUEST["page"]-1))+1) . " to " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device")*$_REQUEST["page"]))) ? $total_rows : (read_config_option("num_rows_device")*$_REQUEST["page"])) . " of $total_rows [$url_page_select]
						</td>\n
						<td align='right' class='textHeaderDark'>
							<strong>"; if (($_REQUEST["page"] * read_config_option("num_rows_log")) < $total_rows) { $nav .= "<a class='linkOverDark' href='utilities.php?action=view_logs&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= _("Next"); if (($_REQUEST["page"] * read_config_option("num_rows_log")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	print $nav;

	html_header(array(_("Date"), _("Facility"), _("Severity"), _("Poller"), _("Host"), _("User"), _("Message")));

	$i = 0;
	if (sizeof($syslog_entries) > 0) {
		foreach ($syslog_entries as $syslog_entry) {
			api_syslog_color(api_syslog_get_severity($syslog_entry["severity"]));
				?>
				<td><?php print $syslog_entry["logdate"]; ?></td>
				<td><?php print api_syslog_get_facility($syslog_entry["facility"]); ?></td>
				<td><?php print api_syslog_get_severity($syslog_entry["severity"]); ?></td>
				<td nowrap><?php if ($syslog_entry["poller_name"] != "") { print $syslog_entry["poller_name"]; } else { print "SYSTEM"; } ?>	</td>
				<td nowrap><?php if ($syslog_entry["host"] != "") { print $syslog_entry["host"]; } else { print "SYSTEM"; } ?></td>
				<td nowrap><?php if ($syslog_entry["username"] != "") { print $syslog_entry["username"]; } else { print "SYSTEM"; } ?></td>
				<td><?php print $syslog_entry["message"]; ?></td>
			</tr>
			<?php
		}

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>"._("No Entries")."</em></td></tr>";
	}
	html_end_box();
}

function utilities_view_poller_cache() {
	global $colors;

	$poller_cache = db_fetch_assoc("select
		poller_item.*,
		data_source.name_cache,
		data_source.host_id
		from poller_item,data_source
		where poller_item.local_data_id=data_source.id");

	html_start_box("<strong>View Poller Cache</strong> [" . sizeof($poller_cache) . " Item" . ((sizeof($poller_cache) > 0) ? "s" : "") . "]", "98%", $colors["header_background"], "3", "center", "");

	$i = 0;
	if (sizeof($poller_cache) > 0) {
	foreach ($poller_cache as $item) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
		?>
			<td>
				Data Source: <?php print $item["name_cache"];?>
			</td>
		</tr>
		<?php
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
		?>
			<td>
				RRD: <?php print $item["rrd_path"];?>
			</td>
		</tr>
		<?php
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
		?>
			<td>
				Action: <?php print $item["action"] . ", ";?><?php print ((($item["action"] == "1") || ($item["action"] == "2")) ? "Script: " . $item["arg1"] : "OID: " . $item["arg1"] . " (Host: " . $item["hostname"] . ", SNMP Version: " . $item["snmp_version"])?><?php if ($item["snmp_version"] == 3) { print ", User: " . $item["snmpv3_auth_username"] . ", AuthProto: " . $item["snmpv3_auth_protocol"] . ", PrivProto: " . $item["snmpv3_priv_protocol"] . ")";} else { print ", Community: " . $item["snmp_community"] . ")";}?>
			</td>
		</tr>
		<?php
	}
	}

	html_end_box();
}
function utilities_rrd_oprhan_detection() {
}

function utilities_rrd_resize() {
}

function utilities_rrd_rename() {
}

function utilities() {
	global $colors;

	html_start_box("<strong>Cacti System Utilities</strong>", "98%", $colors["header_background"], "3", "center", "");

	html_header(array(_("Poller Cache Administration")), 2);

	?>
	<colgroup span="3">
		<col valign="top" width="20"></col>
		<col valign="top" width="10"></col>
	</colgroup>

	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_poller_cache'><?php echo _("View Poller Cache"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("This is the data that is being passed to the poller each time it runs. This data is then in turn executed/interpreted and the results are fed into the rrd files for graphing or the database for display."); ?></p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=rebuild_poller_cache'><?php echo _("Rebuild Poller Cache"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("The poller cache will be cleared and re-generated if you select this option. Sometimes host/data source data can get out of sync with the cache in which case it makes sense to clear the cache and start over."); ?></p>
		</td>
	</tr>

	<?php html_header(array(_("System Log Administration")), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_logs'><?php echo _("View Cacti Logs"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("The Cacti Syslog stores statistics, errors, warnings and other message depending on system settings.  This information can be used to identify problems with the poller and application."); ?></p>
		</td>
	</tr>

	<?php html_header(array(_("General Utilities")), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='php_info.php' target="_blank"><?php echo _("PHP Information"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("This utility will retreive PHP version information to your browser.  This utility can aid in determining sources of problems and verification of your operating environment."); ?></p>
		</td>
	</tr>

	<?php

	html_end_box();
}

?>