	<script type='text/javascript'>
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
</script>
<script type="text/javascript">
<!--

	function applyTimespanFilterChange(objForm) {
		strURL = '?predefined_timespan=' + objForm.predefined_timespan.value;
		strURL = strURL + '&predefined_timeshift=' + objForm.predefined_timeshift.value;
		document.location = strURL;
	}

-->
</script>
	<tr class="rowGraphFilter noprint">
		<td class="noprint">
			<form name="form_timespan_selector" method="post">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr class="rowGraphFilter">
					<td style='white-space:nowrap;width:55px;'>
						&nbsp;<strong>Presets:</strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:130px;'>
						<select name='predefined_timespan' onChange="applyTimespanFilterChange(document.form_timespan_selector)">
							<?php
							if ($_SESSION["custom"]) {
								$graph_timespans[GT_CUSTOM] = "Custom";
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
									print "<option value='$value'"; if ($_SESSION["sess_current_timespan"] == $value) { print " selected"; } print ">" . title_trim($graph_timespans[$value], 40) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td style='white-space:nowrap;width:30px;'>
						&nbsp;<strong>From:</strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:150px;'>
						<input type='text' name='date1' id='date1' title='Graph Begin Timestamp' size='14' value='<?php print (isset($_SESSION["sess_current_date1"]) ? $_SESSION["sess_current_date1"] : "");?>'>
						&nbsp;<input style='border-width:0px;padding-bottom:4px;' type='image' src='images/calendar.gif' alt='Start' title='Start date selector' align='middle' onclick="return showCalendar('date1');">&nbsp;
					</td>
					<td style='white-space:nowrap;width:20px;'>
						&nbsp;<strong>To:</strong>&nbsp;
					</td>
					<td style='white-space:nowrap;width:150px;'>
						<input type='text' name='date2' id='date2' title='Graph End Timestamp' size='14' value='<?php print (isset($_SESSION["sess_current_date2"]) ? $_SESSION["sess_current_date2"] : "");?>'>
						&nbsp;<input style='border-width:0px;padding-bottom:4px;' type='image' src='images/calendar.gif' alt='End date selector' title='End date selector' align='middle' onclick="return showCalendar('date2');">
					</td>
					<td style='white-space:nowrap;width:130px;'>
						&nbsp;&nbsp;<input style='border-width:0px;padding-bottom:4px;' type='image' name='move_left' src='images/move_left.gif' alt='Left' align='middle' title='Shift Left'>
						<select name='predefined_timeshift' title='Define Shifting Interval' onChange="applyTimespanFilterChange(document.form_timespan_selector)">
							<?php
							$start_val = 1;
							$end_val = sizeof($graph_timeshifts)+1;
							if (sizeof($graph_timeshifts) > 0) {
								for ($shift_value=$start_val; $shift_value < $end_val; $shift_value++) {
									print "<option value='$shift_value'"; if ($_SESSION["sess_current_timeshift"] == $shift_value) { print " selected"; } print ">" . title_trim($graph_timeshifts[$shift_value], 40) . "</option>\n";
								}
							}
							?>
						</select>
						<input style='border-width:0px;padding-bottom:4px;' type='image' name='move_right' src='images/move_right.gif' alt='Right' align='middle' title='Shift Right'>
					</td>
					<td style='white-space:nowrap;width:130px;'>
						&nbsp;<input type='submit' value='Refresh' name='button_refresh'>
						<input type='submit' value='Clear' name='button_clear_x'>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
