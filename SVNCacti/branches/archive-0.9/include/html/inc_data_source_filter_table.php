	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?host_id=' + objForm.host_id[objForm.host_id.selectedIndex].value;
		strURL = strURL + '&filter=' + objForm.filter.value;
		document.location = strURL;
	}
	-->
	</script>

	<tr bgcolor="<?php print $colors["filter_background"];?>">
		<form name="form_data_sources">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="100">
						<?php echo _("Select a host:");?>&nbsp;
					</td>
					<td width="1">
						<select name="host_id" onChange="applyFilterChange(document.form_data_sources)">
							<option value="-1"<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>><?php echo _("Any");?></option>
							<option value="0"<?php print $_REQUEST["filter"];?>"<?php if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>><?php echo _("None");?></option>
							<?php
							$hosts = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");

							if (sizeof($hosts) > 0) {
								foreach ($hosts as $host) {
									print "<option value=" . $host["id"] . "&page=1"; if ($_REQUEST["host_id"] == $host["id"]) { print _(" selected"); } print ">" . title_trim($host["name"], 40) . "</option>\n";
								}
							}
							?>

						</select>
					</td>
					<td width="30"></td>
					<td width="60">
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