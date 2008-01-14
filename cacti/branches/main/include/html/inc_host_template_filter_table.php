	<tr class='rowAlternate2'>
		<form name="form_host_template">
		<td>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td nowrap style='white-space: nowrap;' width="50">
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td nowrap style='white-space: nowrap;'>
						&nbsp;<input type="submit" Value="Go" name="go" border="0" align="middle">
						<input type="submit" Value="Clear" name="clear_x" border="0" align="middle">
					</td>
				</tr>
			</table>
		</td>
		<div><input type='hidden' name='page' value='1'></div>
		</form>
	</tr>
