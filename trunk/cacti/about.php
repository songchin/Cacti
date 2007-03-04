<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2007 The Cacti Group                                      |
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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/auth/validate.php");
require_once(CACTI_BASE_PATH . "/include/top_header.php");

html_start_box("<strong>" . _("About Cacti") . "</strong>") ;
?>

<tr>
	<td bgcolor="#<?php print $colors["header_panel_background"];?>" colspan="2">
		<strong><font color="#<?php print $colors["header_text"];?>">Version <?php print CACTI_VERSION;?></font></strong>
	</td>
</tr>



<tr>
	<td valign="top" bgcolor="#<?php print $colors["form_alternate2"];?>" class="textArea">
		<a href="http://www.cacti.net/"><img align="right" src="<?php print html_get_theme_images_path('cacti_logo_about.gif'); ?>" border="0" alt="Cacti"></a>

		Cacti is designed to be a complete graphing solution based on the RRDTool's framework. Its goal is to make a
		network administrator's job easier by taking care of all the necessary details necessary to create
		meaningful graphs.

		<p>Please see the <a href="http://www.cacti.net/">official Cacti website</a> for information, support, and updates.</p>

		<p><strong>Current Cacti Developers</strong><br>
		<ul type="disc">
			<li><strong>Ian Berry</strong> (raX) is original creator of Cacti which was first released to the world in 2001. He remained the sole
				developer for over two years, writing code, supporting users, and keeping the project active. Today, Ian continues
				to actively develop Cacti, focusing on backend components such as templates, data queries, and graph management.</li>
			<li><strong>Larry Adams</strong> (TheWitness) joined the Cacti team in June of 2004 right before the major 0.8.6 release. He helped bring the new poller
				architecture to life by providing ideas, writing code, and managing an active group of beta testers. Larry continues
				to focus on the poller as well as RRDTool integration and SNMP in a Windows environment.</li>
			<li><strong>Tony Roman</strong> (rony) joined the Cacti team in October of 2004 offering years of programming and system administration
				experience to the project. He is contributing a great deal to the upcoming 0.9 release of Cacti by providing many usability
				and documentation changes in addition to revamping Cacti's user management component.</li>
		</ul>
		</p>

		<p><strong>Thanks</a></strong><br>
		<ul type="disc">
			<li>A very special thanks to <a href="http://ee-staff.ethz.ch/~oetiker/"><strong>Tobi Oetiker</strong></a>,
				the creator of <a href="http://www.mrtg.org/">RRDTool</a> and the very popular
				<a href="http://www.mrtg.org">MRTG</a>.</li>
			<li><strong>Brady Alleman</strong>, creator of NetMRG and
				<a href="http://www.thtech.net">Treehouse Technologies</a> for questions and ideas. Just
				as a note, NetMRG is a complete Network Monitoring solution also written in PHP/MySQL. His
				product also makes use of RRDTool's graphing capabilities, I encourage you to check it out.</li>
			<li><strong>Andy Blyler</strong>, for ideas, code, and that much needed overall support
				during really lengthy coding sessions.</li>
			<li><strong>The users of Cacti</strong>! Especially anyone who has taken the time to create a bug report, or otherwise
				help me fix a Cacti-related problem. Also to anyone who has purchased an item from my amazon.com
				wishlist or donated money to the project.</li>

		</ul>
		</p>

		<p><strong>License</strong><br>

		<p>Cacti is licensed under the GNU GPL:</p>

		<p><tt>This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.</tt></p>

<p><tt>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.</tt></p>

		<p><strong>Cacti Variables</a></strong><span style="font-family: monospace; font-size: 10px;"><br>
		<strong><?php echo _("Cacti OS:"); ?></strong> <?php print CACTI_SERVER_OS;?><br>
		<strong><?php echo _("PHP SNMP Support:"); ?></strong> <?php print function_exists("snmpget") ? _("yes") : _("no");?><br>
		<strong><?php echo _("PHP OS:"); ?></strong> <?php print PHP_OS ?><br>
		<img src="<?php print html_get_php_os_icon();?>"><br>
		</span></p>
	</td>
</tr>

<?php
html_end_box();

require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");

?>
