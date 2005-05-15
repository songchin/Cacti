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
include("./include/top_header.php");

html_start_box("<strong>" . _("About Cacti") . "</strong>", "98%", $colors["header_background"], "3", "center", "");
?>

<tr>
	<td bgcolor="#<?php print $colors["header_panel_background"];?>" colspan="2">
		<strong><font color="#<?php print $colors["header_text"];?>">Version <?php print $config["cacti_version"];?></font></strong>
	</td>
</tr>
<tr>
	<td valign="top" bgcolor="#<?php print $colors["form_alternate2"];?>" class="textArea">
		<br>
		<a href="http://www.cacti.net/" target="_blank"><img align="right" src="<?php print html_get_theme_images_path('cacti_logo_about.gif');?>" border="0" alt="Cacti"></a>

		<?php echo _("Cacti is designed to be a complete graphing solution for your network. Its goal is to make the
		network administrator's job easier by taking care of all the necessary details necessary to create
		meaningful network graphs."); ?>

		<p><?php echo _("The design of Cacti took many hours of SQL and PHP coding, so I hope you find it very useful."); ?></p>

		<p><strong><?php echo _("Developer Thanks"); ?></strong><br>
		<ul type="disc">
			<li><a href="http://blyler.cc"><?php echo _("Andy Blyler</a>, for ideas, code, and that much needed overall support
				during really lengthy coding sessions.");?>
			</li>
			<li><?php echo _("Rivo Nurges, for that c-based poller that was talked so long about. This <em>really</em> fast poller
				is what will enable Cacti to make its way into larger and larger networks.");?>
			</li>
			<li><?php echo _("Larry Adams, for providing insight, time, superb support, and personal sanity. I could not have pulled
				off a release of this magnitude without your help.");?>
			</li>
		</ul>
		</p>

		<p><strong><?php echo _("Thanks");?></a></strong><br>
		<ul type="disc">
			<li><?php echo _("A very special thanks to <a href='http://ee-staff.ethz.ch/~oetiker/'>Tobi Oetiker</a>,
				the creator of <a href='http://www.mrtg.org/'>RRDTool</a> and the very popular
				<a href='http://www.mrtg.org'>MRTG</a>");?>
			</li>
			<li><?php echo _("Brady Alleman, creator of NetMRG and
				<a href='http://www.thtech.net'>Treehouse Technolgies</a> for questions and ideas. Just
				as a note, NetMRG is a complete Network Monitoring solution also written in PHP/MySQL. His
				product also makes use of RRDTool's graphing capabilities, I encourage you to check it out.");?>
			</li>
			<li><?php echo _("The users of Cacti! Especially anyone who has taken the time to create a bug report,
				or otherwise help me fix a Cacti-related problem. Also to anyone who has purchased an item from my
				amazon.com wishlist or donated money via Paypal.");?>
			</li>
		</ul>
		</p>

		<p><strong><?php echo _("License");?></strong><br>

		<p><?php echo _("Cacti is licensed under the GNU GPL:");?></p>

		<p><tt><?php echo _("This program is free software; you can redistribute it and/or modify it under the terms of the
			GNU General	Public License as published by the Free Software Foundation; either version 2 of the License, or
			(at your option) any later version.");?>
		</tt></p>

		<p><tt><?php echo _("This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
			without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			GNU General Public License for more details.");?>
		</tt></p>

		<p><strong>Cacti Variables</a></strong><span style="font-family: monospace; font-size: 10px;"><br>
		<strong><?php echo _("Cacti OS:"); ?></strong> <?php print $config["cacti_server_os"];?><br>
		<strong><?php echo _("PHP SNMP Support:"); ?></strong> <?php print $config["php_snmp_support"] ? "yes" : "no";?><br>
		<strong><?php echo _("PHP OS:"); ?></strong> <?php print PHP_OS ?><br>
		<img src="<?php print html_get_php_os_icon();?>"><br>
		</span></p>
	</td>
</tr>

<?php
html_end_box();
