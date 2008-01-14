	<tr class='rowAlternate2'>
		<form name="form_userlog">
		<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="50">
						Username:&nbsp;
					</td>
					<td width="1">
						<select name="username" onChange="applyViewLogFilterChange(document.form_userlog)">
							<option value="-1"<?php if ($_REQUEST["username"] == "-1") {?> selected<?php }?>>All</option>
							<option value="-2"<?php if ($_REQUEST["username"] == "-2") {?> selected<?php }?>>Deleted/Invalid</option>
							<?php
							$users = db_fetch_assoc("SELECT DISTINCT username FROM user_auth ORDER BY username");

							if (sizeof($users) > 0) {
							foreach ($users as $user) {
								print "<option value='" . $user["username"] . "'"; if ($_REQUEST["username"] == $user["username"]) { print " selected"; } print ">" . $user["username"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Result:&nbsp;
					</td>
					<td width="1">
						<select name="result" onChange="applyViewLogFilterChange(document.form_userlog)">
							<option value="-1"<?php if ($_REQUEST['result'] == '-1') {?> selected<?php }?>>Any</option>
							<option value="1"<?php if ($_REQUEST['result'] == '1') {?> selected<?php }?>>Success</option>
							<option value="0"<?php if ($_REQUEST['result'] == '0') {?> selected<?php }?>>Failed</option>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" Value="Go" name="go" border="0" align="middle">
						<input type="submit" Value="Clear" name="clear_x" border="0" align="middle">
						<input type="submit" Value="Purge" name="purge_x" border="0" align="middle">
					</td>
				</tr>
			</table>
		</td>
		<div><input type='hidden' name='page' value='1'></div>
		<div><input type='hidden' name='action' value='view_user_log'></div>
		</form>
	</tr>