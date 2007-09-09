	<script type="text/javascript">
	<!--
	function applyFilterChange(objForm) {
		strURL = '?action=edit';
		strURL = strURL + objForm.host_id[objForm.host_id.selectedIndex].value;
		document.location = strURL;
	}
	-->
	</script>

	<tr bgcolor="<?php print $colors["panel"];?>">
		<form name="form_graph_items">
		<td>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						Select a host:&nbsp;
					</td>
					<td>
						<select name="host_id" onChange="applyFilterChange(document.form_graph_items)">
							<option value=<?php echo "&graph_id=" . $_REQUEST["graph_id"] . "&host_id=0"; if ($_REQUEST["host_id"] == "0") {?> selected<?php }?>>Any</option>
							<option value=<?php echo "&graph_id=" . $_REQUEST["graph_id"] . "&host_id=-1"; if ($_REQUEST["host_id"] == "-1") {?> selected<?php }?>>None</option>
							<?php
							$hosts = db_fetch_assoc("select id,CONCAT_WS('',description,' (',hostname,')') as name from host order by description,hostname");

							if (sizeof($hosts) > 0) {
							foreach ($hosts as $host) {
								print "<option value=&graph_id=" . $_REQUEST["graph_id"] . "&host_id=" . $host["id"]; if ($_REQUEST["host_id"] == $host["id"]) { print " selected"; } print ">" . title_trim($host["name"], 40) . "</option>\n";
							}
							}
							?>

						</select>
					</td>
				</tr>
			</table>
		</td>
		</form>
	</tr>