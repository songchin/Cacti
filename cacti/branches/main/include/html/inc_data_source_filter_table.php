	<tr class='rowAlternate2'>
		<form name="form_data_sources">
		<td>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="50">
						Host:&nbsp;
					</td>
					<td>
						<select name="host_id" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							$hosts = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");

							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value='" . $host["id"] . "'"; if ($_REQUEST["host_id"] == $host["id"]) { print " selected"; } print ">" . title_trim($host["name"], 40) . "</option>\n";
							}
							}
							?>

						</select>
					</td>
					<td width="50">
						&nbsp;Template:&nbsp;
					</td>
					<td width="1">
						<select name="template_id" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if ($_REQUEST["template_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["template_id"] == "0") {?> selected<?php }?>>None</option>
							<?php

							$templates = db_fetch_assoc("SELECT DISTINCT data_template.id, data_template.name
								FROM data_template
								INNER JOIN data_template_data
								ON data_template.id=data_template_data.data_template_id
								WHERE data_template_data.local_data_id>0
								ORDER BY data_template.name");

							if (sizeof($templates) > 0) {
							foreach ($templates as $template) {
								print "<option value='" . $template["id"] . "'"; if ($_REQUEST["template_id"] == $template["id"]) { print " selected"; } print ">" . title_trim($template["name"], 40) . "</option>\n";
							}
							}
							?>

						</select>
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" Value="Go" name="go" border="0" align="middle">
						<input type="submit" Value="Clear" name="clear_x" border="0" align="middle">
					</td>
				</tr>
				<tr>
					<td width="50">
						Method:&nbsp;
					</td>
					<td width="1">
						<select name="method_id" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if ($_REQUEST["method_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["method_id"] == "0") {?> selected<?php }?>>None</option>
							<?php

							$methods = db_fetch_assoc("SELECT DISTINCT data_input.id, data_input.name
								FROM data_input
								INNER JOIN data_template_data
								ON data_input.id=data_template_data.data_input_id
								WHERE data_template_data.local_data_id>0
								ORDER BY data_input.name");

							if (sizeof($methods) > 0) {
							foreach ($methods as $method) {
								print "<option value='" . $method["id"] . "'"; if ($_REQUEST["method_id"] == $method["id"]) { print " selected"; } print ">" . title_trim($method["name"], 40) . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td nowrap style='white-space: nowrap;' width="50">
						&nbsp;Rows:&nbsp;
					</td>
					<td width="1">
						<select name="ds_rows" onChange="applyDSFilterChange(document.form_data_sources)">
							<option value="-1"<?php if ($_REQUEST["ds_rows"] == "-1") {?> selected<?php }?>>Default</option>
							<?php
							if (sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if ($_REQUEST["ds_rows"] == $key) { print " selected"; } print ">" . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="50">
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
		</td>
		<div><input type='hidden' name='page' value='1'></div>
		</form>
	</tr>
