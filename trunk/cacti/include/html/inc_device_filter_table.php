	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?host_template_id=' + objForm.host_template_id[objForm.host_template_id.selectedIndex].value;
		strURL = strURL + '&host_status=' + objForm.host_status[objForm.host_status.selectedIndex].value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	-->
	</script>

	<tr bgcolor="<?php print $colors["filter_background"];?>">
		<form name="form_devices">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20">
						<?php echo _("Type:");?>&nbsp;
					</td>
					<td width="1">
						<select name="host_template_id" onChange="applyFilterChange(document.form_devices)">
							<option value="-1"<?php if ($_REQUEST["host_template_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["host_template_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							$host_templates = db_fetch_assoc("select id,name from host_template order by name");

							if (sizeof($host_templates) > 0) {
							foreach ($host_templates as $host_template) {
								print "<option value=" . $host_template["id"]; if ($_REQUEST["host_template_id"] == $host_template["id"]) { print " selected"; } print ">" . $host_template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td width="5"></td>
					<td width="20">
						<?php echo _("Status:");?>&nbsp;
					</td>
					<td width="1">
						<select name="host_status" onChange="applyFilterChange(document.form_devices)">
							<option value="-1"<?php if ($_REQUEST["host_status"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="3"<?php if ($_REQUEST["host_status"] == "3") {?> selected<?php }?>>Up</option>
							<option value="-2"<?php if ($_REQUEST["host_status"] == "-2") {?> selected<?php }?>>Disabled</option>
							<option value="1"<?php if ($_REQUEST["host_status"] == "1") {?> selected<?php }?>>Down</option>
							<option value="2"<?php if ($_REQUEST["host_status"] == "2") {?> selected<?php }?>>Recovering</option>
							<option value="0"<?php if ($_REQUEST["host_status"] == "0") {?> selected<?php }?>>Unknown</option>
						</select>
					</td>
					<td width="5"></td>
					<td width="20">
						<?php echo _("Search:");?>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="20" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td>
						&nbsp;<input type="image" src="<?php print html_get_theme_images_path('button_go.gif');?>" alt="<?php echo _('Go');?>" border="0" align="absmiddle">
						<input type="image" src="<?php print html_get_theme_images_path('button_clear.gif');?>" name="clear" alt="<?php echo _('Clear');?>" border="0" align="absmiddle">
					</td>
				</tr>
			</table>
		</td>
		<input type='hidden' name='page' value='1'>
		</form>
	</tr>