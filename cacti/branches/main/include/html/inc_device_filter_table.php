	<tr class='rowAlternate2'>
		<td>
			<form name="form_devices">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="55">
						Type:&nbsp;
					</td>
					<td width="1">
						<select name="template_id" onChange="applyViewDeviceFilterChange(document.form_devices)">
							<option value="-1"<?php if ($_REQUEST["template_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["template_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							$host_templates = db_fetch_assoc("select id,name from host_template order by name");

							if (sizeof($host_templates) > 0) {
							foreach ($host_templates as $host_template) {
								print "<option value='" . $host_template["id"] . "'"; if ($_REQUEST["template_id"] == $host_template["id"]) { print " selected"; } print ">" . $host_template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Status:&nbsp;
					</td>
					<td width="1">
						<select name="status" onChange="applyViewDeviceFilterChange(document.form_devices)">
							<option value="-1"<?php if ($_REQUEST["status"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="-3"<?php if ($_REQUEST["status"] == "-3") {?> selected<?php }?>>Enabled</option>
							<option value="-2"<?php if ($_REQUEST["status"] == "-2") {?> selected<?php }?>>Disabled</option>
							<option value="-4"<?php if ($_REQUEST["status"] == "-4") {?> selected<?php }?>>Not Up</option>
							<option value="3"<?php if ($_REQUEST["status"] == "3") {?> selected<?php }?>>Up</option>
							<option value="1"<?php if ($_REQUEST["status"] == "1") {?> selected<?php }?>>Down</option>
							<option value="2"<?php if ($_REQUEST["status"] == "2") {?> selected<?php }?>>Recovering</option>
							<option value="0"<?php if ($_REQUEST["status"] == "0") {?> selected<?php }?>>Unknown</option>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Rows:&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyViewDeviceFilterChange(document.form_devices)">
							<option value="-1"<?php if ($_REQUEST["rows"] == "-1") {?> selected<?php }?>>Default</option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if ($_REQUEST["rows"] == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="55">
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td nowrap>
						&nbsp;<input type="submit" Value="Go" name="go" style='border-width:0px;' align="middle">
						<input type="submit" Value="Clear" name="clear_x" style='border-width:0px;' align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
