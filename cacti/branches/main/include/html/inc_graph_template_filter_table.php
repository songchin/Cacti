	<tr class='rowAlternate2'>
		<td>
			<form name="form_graph_template">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td style='white-space:nowrap;width:50px;'>
						Search:&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;<input type="submit" Value="Go" name="go" align="middle">
						<input type="submit" Value="Clear" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			<div><input type='hidden' name='page' value='1'></div>
			</form>
		</td>
	</tr>
