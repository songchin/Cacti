<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function graph_view_filter_table() {
	global $current_user;

	html_graph_start_box(3, FALSE);
	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_graph_view" method="post" action="graph_view.php">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr class="rowGraphFilter noprint">
					<td style='white-space:nowrap;width:1px;'>
						&nbsp;<strong><?php print __("Host:");?></strong>&nbsp;
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
					</td>
					<td style='white-space:nowrap;width:1px;'>
						&nbsp;<strong><?php print __("Template:");?></strong>&nbsp;
					</td>
					<td width="1">
						<select name="graph_template_id" onChange="applyGraphPreviewFilterChange(document.form_graph_view)">
							<option value="0"<?php if ($_REQUEST["graph_template_id"] == "0") {?> selected<?php }?>><?php print __("Any");?></option><?php
							if (read_config_option("auth_method") != 0) {
								$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.* " .
										"FROM (graph_templates_graph,graph_local) " .
										"LEFT JOIN host ON (host.id=graph_local.host_id) " .
										"LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id) " .
										"LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=" . $_SESSION["sess_user_id"] . ")) " .
										"WHERE graph_templates_graph.local_graph_id=graph_local.id " .
										"AND graph_templates_graph.graph_template_id > 0 " .
										(($_REQUEST["host_id"] > 0) ? " and graph_local.host_id=" . $_REQUEST["host_id"] :" and graph_local.host_id > 0 ") .
										(empty($sql_where) ? "" : "and $sql_where") .
										" ORDER BY name");
							}else{
								$graph_templates = db_fetch_assoc("SELECT DISTINCT graph_templates.* " .
										"FROM graph_templates " .
										"INNER JOIN graph_local " .
										"ON graph_templates.id=graph_local.graph_template_id" .
										(($_REQUEST["host_id"] > 0) ? " WHERE host_id=" . $_REQUEST["host_id"] :"") .
										" GROUP BY graph_templates.name " .
										" ORDER BY name");
							}

							if (sizeof($graph_templates) > 0) {
							foreach ($graph_templates as $template) {
								print "\t\t\t\t\t\t\t<option value='" . $template["id"] . "'"; if ($_REQUEST["graph_template_id"] == $template["id"]) { print " selected"; } print ">" . $template["name"] . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:50px;'>
						&nbsp;<strong><?php print __("Search:");?></strong>&nbsp;
					</td>
					<td width="1">
						<input type="text" name="filter" size="40" value="<?php print $_REQUEST["filter"];?>">
					</td>
					<td>
						&nbsp;<input type="submit" Value="<?php print __("Go");?>" name="go" align="middle">
						<input type="submit" Value="<?php print __("Clear");?>" name="clear_x" align="middle">
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<?php
	html_graph_end_box(FALSE);
}

function graph_view_timespan_selector() {
	global $graph_timespans, $graph_timeshifts, $colors, $config;

	?>
	<script type='text/javascript'>
	<!--
	// Initialize the calendar
	calendar=null;

	// This function displays the calendar associated to the input field 'id'
	function showCalendar(id) {
		var el = document.getElementById(id);
		if (calendar != null) {
			// we already have some calendar created
			calendar.hide();  // so we hide it first.
		} else {
			// first-time call, create the calendar.
			var cal = new Calendar(true, null, selected, closeHandler);
			cal.weekNumbers = false;  // Do not display the week number
			cal.showsTime = true;     // Display the time
			cal.time24 = true;        // Hours have a 24 hours format
			cal.showsOtherMonths = false;    // Just the current month is displayed
			calendar = cal;                  // remember it in the global var
			cal.setRange(1900, 2070);        // min/max year allowed.
			cal.create();
		}

		calendar.setDateFormat('%Y-%m-%d %H:%M');    // set the specified date format
		calendar.parseDate(el.value);                // try to parse the text in field
		calendar.sel = el;                           // inform it what input field we use

		// Display the calendar below the input field
		calendar.showAtElement(el, "Br");        // show the calendar

		return false;
	}

	// This function update the date in the input field when selected
	function selected(cal, date) {
		cal.sel.value = date;      // just update the date in the input field.
	}

	// This function gets called when the end-user clicks on the 'Close' button.
	// It just hides the calendar without destroying it.
	function closeHandler(cal) {
		cal.hide();                        // hide the calendar
		calendar = null;
	}

	function applyTimespanFilterChange(objForm) {
		strURL = '?predefined_timespan=' + objForm.predefined_timespan.value;
		strURL = strURL + '&predefined_timeshift=' + objForm.predefined_timeshift.value;
		document.location = strURL;
	}
	-->
	</script>
	<?php
	html_graph_start_box(3, FALSE);
	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_timespan_selector" method="post" action="graph_view.php">
			<table cellpadding="0" cellspacing="0">
				<tr class="rowGraphFilter">
					<td style='white-space:nowrap;width:55px;'>
						&nbsp;<strong><?php print __("Presets:");?></strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:130px;'>
						<select name='predefined_timespan' onChange="applyTimespanFilterChange(document.form_timespan_selector)"><?php
							if ($_SESSION["custom"]) {
								$graph_timespans[GT_CUSTOM] = __("Custom");
								$start_val = 0;
								$end_val = sizeof($graph_timespans);
							} else {
								if (isset($graph_timespans[GT_CUSTOM])) {
									asort($graph_timespans);
									array_shift($graph_timespans);
								}
								$start_val = 1;
								$end_val = sizeof($graph_timespans)+1;
							}

							if (sizeof($graph_timespans) > 0) {
								for ($value=$start_val; $value < $end_val; $value++) {
									print "\t\t\t\t\t\t\t<option value='$value'"; if ($_SESSION["sess_current_timespan"] == $value) { print " selected"; } print ">" . title_trim($graph_timespans[$value], 40) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:30px;'>
						&nbsp;<strong><?php print __("From:");?></strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:140px;'>
						<input type='text' name='date1' id='date1' title='<?php print __("Graph Begin Timestamp");?>' size='14' value='<?php print (isset($_SESSION["sess_current_date1"]) ? $_SESSION["sess_current_date1"] : "");?>'>
						&nbsp;<input type='image' style='border-width:0px;vertical-align:middle;align:middle;padding-bottom:5px;' src='images/calendar.gif' alt='<?php print __("Start");?>' title='<?php print __("Start Date Selector");?>' onclick='return showCalendar("date1");'>&nbsp;
					</td>
					<td style='white-space:nowrap;width:20px;'>
						&nbsp;<strong><?php print __("To:");?></strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:140px;'>
						<input type='text' name='date2' id='date2' title='<?php print __("Graph End Timestamp");?>' size='14' value='<?php print (isset($_SESSION["sess_current_date2"]) ? $_SESSION["sess_current_date2"] : "");?>'>
						&nbsp;<input type='image' style='border-width:0px;vertical-align:middle;align:middle;padding-bottom:5px;' src='images/calendar.gif' alt='<?php print __("End");?>' title='<?php print __("End Date Selector");?>' onclick='return showCalendar("date2");'>
					</td>
					<td style='white-space:nowrap;width:120px;'>
						&nbsp;&nbsp;<input style='border-width:0px;vertical-align:middle;align:middle;padding-bottom:5px;' type='image' name='move_left' src='images/move_left.gif' alt='<?php print __("Left");?>' title='<?php print __("Shift Left");?>'>
						<select name='predefined_timeshift' title='<?php print __("Define Shifting Interval");?>' onChange="applyTimespanFilterChange(document.form_timespan_selector)"><?php
							$start_val = 1;
							$end_val = sizeof($graph_timeshifts)+1;
							if (sizeof($graph_timeshifts) > 0) {
								for ($shift_value=$start_val; $shift_value < $end_val; $shift_value++) {
									print "\t\t\t\t\t\t\t<option value='$shift_value'"; if ($_SESSION["sess_current_timeshift"] == $shift_value) { print " selected"; } print ">" . title_trim($graph_timeshifts[$shift_value], 40) . "</option>\n";
								}
							}
							?>
						</select>
						<input style='border-width:0px;vertical-align:middle;align:middle;padding-bottom:5px;' type='image' name='move_right' src='images/move_right.gif' alt='<?php print __("Right");?>' title='<?php print __("Shift Right");?>'>
					</td>
					<td style='white-space:nowrap;width:130px;'>
						&nbsp;<input type='submit' value='<?php print __("Refresh");?>' name='button_refresh'>
						<input type='submit' value='<?php print __("Clear");?>' name='button_clear_x'>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<?php

	html_graph_end_box(FALSE);
}

function graph_view_search_filter() {
	global $graphs_per_page;

	html_graph_start_box(3, FALSE);
	?>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_graph_view" method="post" action="graph_view.php">
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td width="55" style="white-space:nowrap;">
							<strong>&nbsp;<?php print __("Search:");?></strong>&nbsp;
						</td>
						<td width="130" style="white-space: nowrap;">
							<input size='30' style='width:100;' name='filter' value='<?php print clean_html_output(get_request_var_request("filter"));?>'>
						</td>
						<td style='white-space:nowrap;width:80px;'>
							&nbsp;<strong><?php print __("Graphs/Page:");?></strong>&nbsp;
						</td>
						<td width="1">
							<select name="graphs" onChange="submit()">
								<?php
								if (sizeof($graphs_per_page) > 0) {
								foreach ($graphs_per_page as $key => $value) {
									print "\t\t\t\t\t\t\t<option value='" . $key . "'"; if ($_REQUEST["graphs"] == $key) { print " selected"; } print ">" . $value . "</option>\n";
								}
								}
								?>
							</select>
						</td>
						<td width="40">
							<label for="thumbnails"><strong>&nbsp;<?php print __("Thumbnails:");?>&nbsp;</strong></label>
						</td>
						<td>
							<input type="checkbox" name="thumbnails" id="thumbnails" onChange="if (this.checked == true) this.value='dogs'; else this.value='cats';submit()" <?php print (($_REQUEST['thumbnails'] == "dogs") ? "checked":"");?>>
						</td>
						<td style='white-space:nowrap;' nowrap>
							&nbsp;<input type='submit' value='<?php print __("Refresh");?>' name='refresh'>
							<input type='submit' value='<?php print __("Clear");?>' name='clear_x'>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
	<?php

	html_graph_end_box();
}
?>
