	<tr bgcolor="<?php print $colors["filter_background"];?>">
		<form name="form_syslog_id">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20">
						Facility:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_facility" onChange="window.location=document.form_syslog_id.cbo_facility.options[document.form_syslog_id.cbo_facility.selectedIndex].value">
							<option value="utilities.php?action=view_syslog&page=1&facility=ALL<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "ALL") {?> selected<?php }?>>All</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=POLLER<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "POLLER") {?> selected<?php }?>>Poller</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=CMDPHP<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "CMDPHP") {?> selected<?php }?>>Cmdphp</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=CACTID<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "CACTID") {?> selected<?php }?>>Cactid</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=SCPTSVR<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "SCPTSVR") {?> selected<?php }?>>Scptsvr</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=AUTH<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "AUTH") {?> selected<?php }?>>Auth</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=WEBUI<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "WEBUI") {?> selected<?php }?>>WebUI</option>
							<option value="utilities.php?action=view_syslog&page=1&facility=EXPORT<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["facility"] == "EXPORT") {?> selected<?php }?>>Export</option>
						</select>
					</td>
					<td width="1">
						&nbsp;Severity:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_severity" onChange="window.location=document.form_syslog_id.cbo_severity.options[document.form_syslog_id.cbo_severity.selectedIndex].value">
							<option value="utilities.php?action=view_syslog&page=1&severity=ALL<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "ALL") {?> selected<?php }?>>All</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=EMERGENCY<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "EMERGENCY") {?> selected<?php }?>>Emergency</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=ALERT<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "ALERT") {?> selected<?php }?>>Alert</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=CRITICAL<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "CRITICAL") {?> selected<?php }?>>Critical</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=ERROR<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "ERROR") {?> selected<?php }?>>Error</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=WARNING<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "WARNING") {?> selected<?php }?>>Warning</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=NOTICE<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "NOTICE") {?> selected<?php }?>>Notice</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=INFO<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "INFO") {?> selected<?php }?>>Info</option>
							<option value="utilities.php?action=view_syslog&page=1&severity=DEBUG<?php print '&poller=' . $_REQUEST['poller'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "DEBUG") {?> selected<?php }?>>Debug</option>
						</select>
					</td>
					<td width="20">
						&nbsp;Poller:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_poller" onChange="window.location=document.form_syslog_id.cbo_poller.options[document.form_syslog_id.cbo_poller.selectedIndex].value">
							<option value="utilities.php?action=view_syslog&poller=ALL&severity=ALL<?php print '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["severity"] == "ALL") {?> selected<?php }?>>All</option>
							<?php
							$pollers = db_fetch_assoc("select id,name from poller order by name");

							if (sizeof($pollers) > 0) {
							foreach ($pollers as $poller) {
								print "<option value='utilities.php?action=view_syslog&poller=" . $poller[id] . "&severity=ALL&facility=" . $_REQUEST['facility'] . "&filter=" . $_REQUEST['filter'] . "&page=1'"; if ($_REQUEST["poller"] == $poller["id"]) { print " selected"; } print ">" . $poller["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td width="1">
						&nbsp;Host:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host" onChange="window.location=document.form_syslog_id.cbo_host.options[document.form_syslog_id.cbo_host.selectedIndex].value">
							<option value="utilities.php?action=view_syslog&page=1&host=ALL<?php print '&poller=' . $_REQUEST['poller'] . '&severity=' . $_REQUEST['severity'] . '&facility=' . $_REQUEST['facility'] . '&filter=' . $_REQUEST['filter'];?>"<?php if ($_REQUEST["host"] == "ALL") {?> selected<?php }?>>All</option>
							<?php
							$hosts = db_fetch_assoc("select id,description from host order by description");

							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='utilities.php?action=view_syslog&page=1&host=" . $host[id] . "&poller=" . $_REQUEST['poller'] . "&severity=" . $_REQUEST['severity'] . "&facility=" . $_REQUEST['facility'] . "&filter=" . $_REQUEST['filter'] . "&page=1'"; if ($_REQUEST["host"] == $host["id"]) { print " selected"; } print ">" . $host["description"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td>
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_go.gif');?>" alt="Go" border="0" align="absmiddle" action="submit">
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_clear.gif');?>" name="clear" alt="Clear" border="0" align="absmiddle" action="submit">
					</td>
				</tr>
			</table>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td width="10">
						Search:&nbsp;
					</td>
					<td width="90">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>