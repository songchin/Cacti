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

require(dirname(__FILE__) . "/include/config.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/lib/poller.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'rebuild_poller_cache':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		repopulate_poller_cache();

		utilities();
		utilities_view_poller_cache();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_poller_cache':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities();
		utilities_view_poller_cache();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		utilities();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

/* -----------------------
    Utilities Functions
   ----------------------- */
function utilities_view_poller_cache() {
	global $colors;

	$poller_cache = db_fetch_assoc("select
		poller_item.*,
		data_source.name_cache,
		data_source.host_id
		from poller_item,data_source
		where poller_item.local_data_id=data_source.id");

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
				Action: <?php print $item["action"] . ", ";?><?php print ((($item["action"] == "1") || ($item["action"] == "2")) ? "Script: " . $item["arg1"] : "OID: " . $item["arg1"] . " (Host: " . $item["hostname"] . ", SNMP Version: " . $item["snmp_version"])?><?php if ($item["snmp_version"] == 3) { print ", User: " . $item["snmpv3_auth_username"] . ", AuthProto: " . $item["snmpv3_auth_protocol"] . ", PrivProto: " . $item["snmpv3_priv_protocol"] . ")";} else { print ", Community: " . $item["snmp_community"] . ")";}?>
			</td>
		</tr>
		<?php
	}
	}

	html_end_box();
}


function utilities() {
	global $colors;

	html_start_box("<strong>Cacti System Utilities</strong>", "98%", $colors["header_background"], "3", "center", "");

	html_header(array(_("Poller Cache Administration")), 2);

	?>
	<colgroup span="3">
		<col valign="top" width="20"></col>
		<col valign="top" width="10"></col>
	</colgroup>

	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=view_poller_cache'><?php echo _("View Poller Cache"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("This is the data that is being passed to the poller each time it runs. This data is then in turn executed/interpreted and the results are fed into the rrd files for graphing or the database for display."); ?></p>
		</td>
	</tr>
	<tr bgcolor="#<?php print $colors["form_alternate1"];?>">
		<td class="textArea">
			<p><a href='utilities.php?action=rebuild_poller_cache'><?php echo _("Rebuild Poller Cache"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("The poller cache will be cleared and re-generated if you select this option. Sometimes host/data source data can get out of sync with the cache in which case it makes sense to clear the cache and start over."); ?></p>
		</td>
	</tr>

	<?php html_header(array(_("General Utilities")), 2);?>

	<tr bgcolor="#<?php print $colors["form_alternate2"];?>">
		<td class="textArea">
			<p><a href='php_info.php' target="_blank"><?php echo _("PHP Information"); ?></a></p>
		</td>
		<td class="textArea">
			<p><?php echo _("This utility will retreive PHP version information to your browser.  This utility can aid in determining sources of problems and verification of your operating environment."); ?></p>
		</td>
	</tr>

	<?php

	html_end_box();
}

?>
