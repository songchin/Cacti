<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2005 The Cacti Group                                      |
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

include("./include/config.php");
include("./include/auth.php");
include_once("./lib/utility.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'clear_poller_cache':
		include_once("./include/top_header.php");

		repopulate_poller_cache();

		utilities();
		utilities_view_poller_cache();

		include_once("./include/bottom_footer.php");
		break;
	case 'view_snmp_cache':
		include_once("./include/top_header.php");

		utilities();
		utilities_view_snmp_cache();

		include_once("./include/bottom_footer.php");
		break;
	case 'view_poller_cache':
		include_once("./include/top_header.php");

		utilities();
		utilities_view_poller_cache();

		include_once("./include/bottom_footer.php");
		break;
	case 'view_syslog':
		include_once("./include/top_header.php");

		utilities_view_syslog();

		include_once("./include/bottom_footer.php");
		break;
	case 'clear_syslog':
		include_once("./include/top_header.php");

		api_syslog_clear();
		utilities();

		include_once("./include/bottom_footer.php");
		break;
	default:
		include_once("./include/top_header.php");

		utilities();

		include_once("./include/bottom_footer.php");
		break;
}

/* -----------------------
    Utilities Functions
   ----------------------- */

function utilities_view_syslog() {
	global $colors, $device_actions;

    define("MAX_DISPLAY_PAGES", 21);

	/* if the user pushed the 'clear' button */
	if (isset($_REQUEST["clear_x"])) {
		kill_session_var("sess_device_current_page");
		kill_session_var("sess_device_filter");
		kill_session_var("sess_facility");
		kill_session_var("sess_severity");
		kill_session_var("sess_poller");
		kill_session_var("sess_host");

		unset($_REQUEST["page"]);
		unset($_REQUEST["filter"]);
		unset($_REQUEST["facility"]);
		unset($_REQUEST["severity"]);
		unset($_REQUEST["poller"]);
		unset($_REQUEST["host"]);
	}

	/* remember these search fields in session vars so we don't have to keep passing them around */
	load_current_session_value("page", "sess_device_current_page", "1");
	load_current_session_value("filter", "sess_device_filter", "");
	load_current_session_value("facility", "sess_facility", "ALL");
	load_current_session_value("severity", "sess_severity", "ALL");
	load_current_session_value("poller", "sess_poller", "ALL");
	load_current_session_value("host", "sess_host", "ALL");

	html_start_box("<strong>Cacti System Log</strong>", "98%", $colors["header_background"], "3", "center", "");

	include("./include/html/inc_syslog_filter_table.php");

	html_end_box();

	/* form the 'where' clause for our main sql query */
	$sql_where = "where syslog.message like '%%" . $_REQUEST["filter"] . "%%'";

	if ($_REQUEST["facility"] != "ALL") {
		$sql_where .= " and syslog.facility='" . $_REQUEST["facility"] . "'";
	}

	if ($_REQUEST["severity"] != "ALL") {
		$sql_where .= " and syslog.severity='" . $_REQUEST["severity"] . "'";
	}

	if ($_REQUEST["poller"] != "ALL") {
		$sql_where .= " and poller.id='" . $_REQUEST["poller"] . "'";
	}

	if ($_REQUEST["host"] != "ALL") {
		$sql_where .= " and host.id='" . $_REQUEST["host"] . "'";
	}

	html_start_box("", "98%", $colors["header_background"], "3", "center", "");

	$total_rows = db_fetch_cell("select
		COUNT(syslog.id)
		from syslog
		$sql_where");

	$syslog_entries = db_fetch_assoc("SELECT
		syslog.id,
		syslog.logdate,
		syslog.facility,
		syslog.severity,
		poller.name as poller_name,
		poller.id as poller_id,
		host.description as host,
		syslog.username,
		syslog.message
		FROM (syslog LEFT JOIN host ON syslog.host_id = host.id)
		LEFT JOIN poller ON syslog.poller_id = poller.id
		$sql_where
		order by syslog.logdate
		limit " . (read_config_option("num_rows_device")*($_REQUEST["page"]-1)) . "," . read_config_option("num_rows_device"));

	/* generate page list */
	$url_page_select = get_page_list($_REQUEST["page"], MAX_DISPLAY_PAGES, read_config_option("num_rows_device"), $total_rows, "utilities.php?action=view_syslog&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&severity=" . $_REQUEST["severity"]);

	$nav = "<tr bgcolor='#" . $colors["header_background"] . "'>
			<td colspan='7'>
				<table width='100%' cellspacing='0' cellpadding='0' border='0'>
					<tr>
						<td align='left' class='textHeaderDark'>
							<strong>&lt;&lt; "; if ($_REQUEST["page"] > 1) { $nav .= "<a class='linkOverDark' href='utilities.php?action=view_syslog&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&page=" . ($_REQUEST["page"]-1) . "'>"; } $nav .= "Previous"; if ($_REQUEST["page"] > 1) { $nav .= "</a>"; } $nav .= "</strong>
						</td>\n
						<td align='center' class='textHeaderDark'>
							Showing Rows " . ((read_config_option("num_rows_device")*($_REQUEST["page"]-1))+1) . " to " . ((($total_rows < read_config_option("num_rows_device")) || ($total_rows < (read_config_option("num_rows_device")*$_REQUEST["page"]))) ? $total_rows : (read_config_option("num_rows_device")*$_REQUEST["page"])) . " of $total_rows [$url_page_select]
						</td>\n
						<td align='right' class='textHeaderDark'>
							<strong>"; if (($_REQUEST["page"] * read_config_option("num_rows_device")) < $total_rows) { $nav .= "<a class='linkOverDark' href='utilities.php?action=view_syslog&filter=" . $_REQUEST["filter"] . "&facility=" . $_REQUEST["facility"] . "&page=" . ($_REQUEST["page"]+1) . "'>"; } $nav .= "Next"; if (($_REQUEST["page"] * read_config_option("num_rows_device")) < $total_rows) { $nav .= "</a>"; } $nav .= " &gt;&gt;</strong>
						</td>\n
					</tr>
				</table>
			</td>
		</tr>\n";

	print $nav;

	html_header(array("Logdate", "Facility", "Severity", "Poller", "Host", "User", "Log Message"));

	$i = 0;
	if (sizeof($syslog_entries) > 0) {
		foreach ($syslog_entries as $syslog_entry) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
				?>
				<td>
					<a class="linkEditMain" href="utilities_viewsyslog.php?action=view&id=<?php print $syslog_entry["id"];?>"><?php print eregi_replace("(" . preg_quote($_REQUEST["filter"]) . ")", "<span style='background-color: #F8D93D;'>\\1</span>", $syslog_entry["logdate"]);?></a>
				</td>
				<td>
					<?php print $syslog_entry["facility"];?>
				</td>
				<td>
					<?php print $syslog_entry["severity"];?>
				</td>
				<td>
					<?php print $syslog_entry["poller_name"];?>
				</td>
				<td>
					<?php print $syslog_entry["host"];?>
				</td>
				<td>
					<?php print $syslog_entry["username"];?>
				</td>
				<td>
					<?php print $syslog_entry["message"];?>
				</td>
			</tr>
			<?php
		}

		/* put the nav bar on the bottom as well */
		print $nav;
	}else{
		print "<tr><td bgcolor='#" . $colors["form_alternate1"] . "' colspan=7><em>No Entries</em></td></tr>";
	}
	html_end_box(false);
}

function utilities_view_snmp_cache() {
	global $colors;

	$snmp_cache = db_fetch_assoc("select host_snmp_cache.*,
		host.description,
		snmp_query.name
		from host_snmp_cache,snmp_query,host
		where host_snmp_cache.host_id=host.id
		and host_snmp_cache.snmp_query_id=snmp_query.id
		order by host_snmp_cache.host_id,host_snmp_cache.snmp_query_id,host_snmp_cache.snmp_index");

	html_start_box("<strong>View SNMP Cache</strong> [" . sizeof($snmp_cache) . " Item" . ((sizeof($snmp_cache) > 0) ? "s" : "") . "]", "98%", $colors["header_background"], "3", "center", "");

	$i = 0;
	if (sizeof($snmp_cache) > 0) {
	foreach ($snmp_cache as $item) {
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
			?>
				<td>
					Host: <?php print $item["description"];?>, SNMP Query: <?php print $item["name"];?>
				</td>
			</tr>
			<?php
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
			?>
				<td>
					Index: <?php print $item["snmp_index"];?>, Field Name: <?php print $item["field_name"];?>, Field Value: <?php print $item["field_value"];?>
				</td>
			</tr>
			<?php
			form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
			?>
				<td>
					OID: <?php print $item["oid"];?>
				</td>
			</tr>
			<?php
	}
	}

	html_end_box();
}

function utilities_view_poller_cache() {
	global $colors;

	$poller_cache = db_fetch_assoc("select
		poller_item.*,
		data_template_data.name_cache,
		data_local.host_id
		from poller_item,data_template_data,data_local
		where poller_item.local_data_id=data_template_data.local_data_id
		and data_template_data.local_data_id=data_local.id");

	html_start_box("<strong>View Poller Cache</strong> [" . sizeof($poller_cache) . " Item" . ((sizeof($poller_cache) > 0) ? "s" : "") . "]", "98%", $colors["header_background"], "3", "center", "");

	$i = 0;
	if (sizeof($poller_cache) > 0) {
	foreach ($poller_cache as $item) {
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
		?>
			<td>
				Data Source: <?php print $item["name_cache"];?>
			</td>
		</tr>
		<?php
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i);
		?>
			<td>
				RRD: <?php print $item["rrd_path"];?>
			</td>
		</tr>
		<?php
		form_alternate_row_color($colors["form_alternate1"],$colors["form_alternate2"],$i); $i++;
		?>
			<td>
				Action: <?php print $item["action"];?>, <?php print ((($item["action"] == "1") || ($item["action"] == "2")) ? "Script: " . $item["arg1"] : "OID: " . $item["arg1"] . " (Host: " . $item["hostname"] . ", Community: " . $item["snmp_community"] . ")");?>
			</td>
		</tr>
		<?php
	}
	}

	html_end_box();
}
function utilities_rrd_oprhan_detection() {
}

function utilities_rrd_resize() {
}

function utilities_rrd_rename() {
}

function utilities() {
	global $colors;

	html_start_box("<strong>Cacti System Utilities</strong>", "98%", $colors["header_background"], "3", "center", "");

	html_header(array("Poller Cache Administration"), 2);

	?>
	<colgroup span="3">
		<col valign="top" width="20"></col>
		<col valign="top" width="10"></col>
	</colgroup>

	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_poller_cache'>View Poller Cache</a></p>
		</td>
		<td class="textArea">
			<p>This is the data that is being passed to the poller each time it runs. This data is then in turn executed/interpreted and the results are fed into the rrd files for graphing or the database for display.</p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_snmp_cache'>View SNMP Cache</a></p>
		</td>
		<td class="textArea">
			<p>The SNMP cache stores information gathered from SNMP queries. It is used by cacti to determine the OID to use when gathering information from an SNMP-enabled host.</p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=clear_poller_cache'>Clear Poller Cache</a></p>
		</td>
		<td class="textArea">
			<p>The poller cache will be cleared and re-generated if you select this option. Sometimes host/data source data can get out of sync with the cache in which case it makes sense to clear the cache and start over.</p>
		</td>
	</tr>

	<?php html_header(array("System Log Administration"), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_syslog'>View Cacti Log File</a></p>
		</td>
		<td class="textArea">
			<p>The Cacti Log File stores statistic, error and other message depending on system settings.  This information can be used to identify problems with the poller and application.</p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=clear_syslog'>Clear Cacti Log File</a></p>
		</td>
		<td class="textArea">
			<p>This action will reset the Cacti Log File.  Please note that if you are using the Syslog/Eventlog only, this action will have no effect.</p>
		</td>
	</tr>

	<?php html_header(array("RRD File Utilities"), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=rrd_resize'>RRD Resize Utility</a></p>
		</td>
		<td class="textArea">
			<p>This action will allow for the resizing of RRD files.  This is helpful if you want additional or less data to be included in these files.</p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=rrd_rename'>RRD Rename Utility</a></p>
		</td>
		<td class="textArea">
			<p>This action will allow for the renaming of RRD files based upon the current naming conventions set for for the data source.</p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=rrd_orphan_detection'>RRD Orphan Detection</a></p>
		</td>
		<td class="textArea">
			<p>This action will search the RRA directory for orphaned RRD files and provide the option to delete them.</p>
		</td>
	</tr>

	<?php html_header(array("General Utilities"), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='php_info.php' target="_blank">PHP Information</a></p>
		</td>
		<td class="textArea">
			<p>This utility will retreive PHP version information to your browser.  This utility can aid in determining sources of problems and verification of your operating environment.</p>
		</td>
	</tr>

	<?php

	html_end_box();
}

?>