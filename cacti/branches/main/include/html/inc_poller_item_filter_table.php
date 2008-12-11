	<tr class='rowAlternate2'>
		<td>
			<form name="form_pollercache">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="50">
						Host:&nbsp;
					</td>
					<td width="1">
						<select name="host_id" onChange="applyPItemFilterChange(document.form_pollercache)">
							<option value="-1"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							$hosts = db_fetch_assoc("select id,description,hostname from host order by description");

							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='" . $host["id"] . "'"; if ($_REQUEST["host_id"] == $host["id"]) { print " selected"; } print ">" . $host["description"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Action:&nbsp;
					</td>
					<td width="1">
						<select name="poller_action" onChange="applyPItemFilterChange(document.form_pollercache)">
							<option value="-1"<?php if ($_REQUEST['poller_action'] == '-1') {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST['poller_action'] == '0') {?> selected<?php }?>>SNMP</option>
							<option value="1"<?php if ($_REQUEST['poller_action'] == '1') {?> selected<?php }?>>Script</option>
							<option value="2"<?php if ($_REQUEST['poller_action'] == '2') {?> selected<?php }?>>Script Server</option>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" Value="Go" name="go" align="middle">
						<input type="submit" Value="Clear" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_poller_cache'></div>
			</form>
		</td>
	</tr>