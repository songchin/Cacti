	<tr class='rowAlternate2'>
		<td>
			<form name="form_logfile">
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						Tail Lines:&nbsp;
					</td>
					<td width="1">
						<select name="tail_lines" onChange="applyViewLogFilterChange(document.form_logfile)">
							<?php
							foreach($log_tail_lines AS $tail_lines => $display_text) {
								print "<option value='" . $tail_lines . "'"; if ($_REQUEST["tail_lines"] == $tail_lines) { print " selected"; } print ">" . $display_text . "</option>\n";
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:100px;'>
						&nbsp;Message Type:&nbsp;
					</td>
					<td width="1">
						<select name="message_type" onChange="applyViewLogFilterChange(document.form_logfile)">
							<option value="-1"<?php if ($_REQUEST['message_type'] == '-1') {?> selected<?php }?>>All</option>
							<option value="1"<?php if ($_REQUEST['message_type'] == '1') {?> selected<?php }?>>Stats</option>
							<option value="2"<?php if ($_REQUEST['message_type'] == '2') {?> selected<?php }?>>Warnings</option>
							<option value="3"<?php if ($_REQUEST['message_type'] == '3') {?> selected<?php }?>>Errors</option>
							<option value="4"<?php if ($_REQUEST['message_type'] == '4') {?> selected<?php }?>>Debug</option>
							<option value="5"<?php if ($_REQUEST['message_type'] == '5') {?> selected<?php }?>>SQL Calls</option>
						</select>
					</td>
					<td style='white-space:nowrap;width:180px;'>
						&nbsp;<input type="submit" Value="Go" name="go" align="middle">
						<input type="submit" Value="Clear" name="clear_x" align="middle">
						<input type="submit" Value="Purge" name="purge_x" align="middle">
					</td>
				</tr>
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						Refresh:&nbsp;
					</td>
					<td width="1">
						<select name="refresh" onChange="applyViewLogFilterChange(document.form_logfile)">
							<?php
							foreach($page_refresh_interval AS $seconds => $display_text) {
								print "<option value='" . $seconds . "'"; if ($_REQUEST["refresh"] == $seconds) { print " selected"; } print ">" . $display_text . "</option>\n";
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:100px;'>
						&nbsp;Display Order:&nbsp;
					</td>
					<td width="1">
						<select name="reverse" onChange="applyViewLogFilterChange(document.form_logfile)">
							<option value="1"<?php if ($_REQUEST['reverse'] == '1') {?> selected<?php }?>>Newest First</option>
							<option value="2"<?php if ($_REQUEST['reverse'] == '2') {?> selected<?php }?>>Oldest First</option>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:80px;'>
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="75" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			<div><input type='hidden' name='action' value='view_logfile'></div>
			</form>
		</td>
	</tr>
