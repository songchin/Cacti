	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?action=view_syslog';
		strURL = strURL + '&facility=' + objForm.facility[objForm.facility.selectedIndex].value;
		strURL = strURL + '&severity=' + objForm.severity[objForm.severity.selectedIndex].value;
		strURL = strURL + '&poller=' + objForm.poller[objForm.poller.selectedIndex].value;
		strURL = strURL + '&host=' + objForm.host[objForm.host.selectedIndex].value;
		strURL = strURL + '&username=' + objForm.username[objForm.username.selectedIndex].value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	-->
	</script>

	<tr bgcolor="<?php print $colors["filter_background"];?>">
		<form name="form_syslog_id">
		<input type="hidden" name="action" value="view_syslog">
		<td>
			<table cellpadding="1" cellspacing="1">
				<tr>
					<td width="45">
						<?php echo _("Facility:");?>&nbsp;
					</td>
					<td width="1">
						<select name="facility" onChange="applyFilterChange(document.form_syslog_id)">
							<option value="ALL"<?php if ($_REQUEST["facility"] == "ALL") {?> selected<?php }?>>All</option>
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
						<select name="severity" onChange="applyFilterChange(document.form_syslog_id)">
							<option value="ALL"<?php if ($_REQUEST["severity"] == "ALL") {?> selected<?php }?>>All</option>
							<option value="<?php echo SEV_EMERGENCY;?>"<?php if ($_REQUEST["severity"] == SEV_EMERGENCY) {?> selected<?php }?>>Emergency</option>
							<option value="<?php echo SEV_ALERT;?>"<?php if ($_REQUEST["severity"] == SEV_ALERT) {?> selected<?php }?>>Alert</option>
							<option value="<?php echo SEV_CRITICAL;?>"<?php if ($_REQUEST["severity"] == SEV_CRITICAL) {?> selected<?php }?>>Critical</option>
							<option value="<?php echo SEV_ERROR;?>"<?php if ($_REQUEST["severity"] == SEV_ERROR) {?> selected<?php }?>>Error</option>
							<option value="<?php echo SEV_WARNING;?>"<?php if ($_REQUEST["severity"] == SEV_WARNING) {?> selected<?php }?>>Warning</option>
							<option value="<?php echo SEV_NOTICE;?>"<?php if ($_REQUEST["severity"] == SEV_NOTICE) {?> selected<?php }?>>Notice</option>
							<option value="<?php echo SEV_INFO;?>"<?php if ($_REQUEST["severity"] == SEV_INFO) {?> selected<?php }?>>Info</option>
							<option value="<?php echo SEV_DEBUG;?>"<?php if ($_REQUEST["severity"] == SEV_DEBUG) {?> selected<?php }?>>Debug</option>
						</select>
					</td>
					<td width="1">
						&nbsp;Username:&nbsp;
					</td>
					<td width="1">
						<select name="username" onChange="applyFilterChange(document.form_syslog_id)">
							<option value="ALL"<?php if ($_REQUEST["username"] == "ALL") {?> selected<?php }?>>All</option>
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
						<select name="poller" onChange="applyFilterChange(document.form_syslog_id)">
							<option value="ALL"<?php if ($_REQUEST["poller"] == "ALL") {?> selected<?php }?>>All</option>
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
						<select name="host" onChange="applyFilterChange(document.form_syslog_id)">
							<option value="ALL"<?php if ($_REQUEST["host"] == "ALL") {?> selected<?php }?>>All</option>
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