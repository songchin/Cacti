	<tr class='rowAlternate2'>
		<td>
			<form name="form_graph_id" autocomplete="off">
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td width="50">
						Host:&nbsp;
					</td>
					<td width="1">
						<?php
						if (isset($_REQUEST["host_id"])) {
							$hostname = db_fetch_cell("SELECT description as name FROM host WHERE id=".$_REQUEST["host_id"]." ORDER BY description,hostname");
						} else {
							$hostname = "";
						}
						?>
						<input class="ac_field" type="text" id="host" size="30" value="<?php print $hostname; ?>">
						<input type="hidden" id="host_id">
					<td width="70">
						&nbsp;Template:&nbsp;
					</td>
					<td width="1">
						<select name="template_id" onChange="applyGraphsFilterChange(document.form_graph_id)">
							<option value="-1"<?php if ($_REQUEST["template_id"] == "-1") {?> selected<?php }?>>Any</option>
							<option value="0"<?php if ($_REQUEST["template_id"] == "0") {?> selected<?php }?>>None</option>
							<?php
							if (read_config_option("auth_method") != 0) {
								$templates = db_fetch_assoc("SELECT DISTINCT graph_templates.id, graph_templates.name
									FROM (graph_templates_graph,graph_local)
									LEFT JOIN host ON (host.id=graph_local.host_id)
									LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id)
									LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPHS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_HOSTS . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=" . PERM_GRAPH_TEMPLATES . " and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . "))
									WHERE graph_templates_graph.local_graph_id=graph_local.id
									AND graph_templates.id IS NOT NULL
									" . (empty($sql_where) ? "" : "AND $sql_where") . "
									ORDER BY name");
							}else{
								$templates = db_fetch_assoc("SELECT DISTINCT graph_templates.id, graph_templates.name
									FROM graph_templates
									ORDER BY name");
							}

							if (sizeof($templates) > 0) {
							foreach ($templates as $template) {
								print "<option value=' " . $template["id"] . "'"; if ($_REQUEST["template_id"] == $template["id"]) { print " selected"; } print ">" . title_trim($template["name"], 40) . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td width="120" nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" Value="Go" name="go" border="0" align="middle">
						<input type="submit" Value="Clear" name="clear_x" border="0" align="middle">
					</td>
				</tr>
			</table>
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="50">
						Rows:&nbsp;
					</td>
					<td width="1">
						<select name="rows" onChange="applyGraphsFilterChange(document.form_graph_id)">
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
					<td width="50">
						&nbsp;Search:&nbsp;
					</td>
					<td>
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
