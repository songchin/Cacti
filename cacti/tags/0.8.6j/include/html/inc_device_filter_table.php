	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_devices">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20">
						Type:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host_template_id" onChange="window.location=document.form_devices.cbo_host_template_id.options[document.form_devices.cbo_host_template_id.selectedIndex].value">
							<option value="host.php?host_template_id=-1&host_status=<?php print $_REQUEST["host_status"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_template_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="host.php?host_template_id=0&host_status=<?php print $_REQUEST["host_status"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_template_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							$host_templates = db_fetch_assoc("select id,name from host_template order by name");

							if (sizeof($host_templates) > 0) {
							foreach ($host_templates as $host_template) {
								print "<option value='host.php?host_template_id=" . $host_template["id"] . "&host_status=" . $_REQUEST["host_status"] . "&filter=" . $_REQUEST["filter"] . "&page=1'"; if ($_REQUEST["host_template_id"] == $host_template["id"]) { print " selected"; } print ">" . $host_template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td width="5"></td>
    				<td width="20">
						Status:&nbsp;
					</td>
					<td width="1">
						<select name="cbo_host_status" onChange="window.location=document.form_devices.cbo_host_status.options[document.form_devices.cbo_host_status.selectedIndex].value">
							<option value="host.php?host_status=-1&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="host.php?host_status=-3&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "-3") {?> selected<?php }?>>Enabled</option>
							<option value="host.php?host_status=-2&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "-2") {?> selected<?php }?>>Disabled</option>
							<option value="host.php?host_status=3&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "3") {?> selected<?php }?>>Up</option>
							<option value="host.php?host_status=1&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "1") {?> selected<?php }?>>Down</option>
							<option value="host.php?host_status=2&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "2") {?> selected<?php }?>>Recovering</option>
							<option value="host.php?host_status=0&host_template_id=<?php print $_REQUEST["host_template_id"];?>&filter=<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_status"] == "0") {?> selected<?php }?>>Unknown</option>
						</select>
					</td>
					<td width="5"></td>
					<td width="20">
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td>
						&nbsp;<input type="image" src="images/button_go.gif" alt="Go" border="0" align="absmiddle">
						<input type="image" src="images/button_clear.gif" name="clear" alt="Clear" border="0" align="absmiddle">
					</td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>
