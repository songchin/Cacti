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

require(dirname(__FILE__) . "/include/global.php");
require_once(CACTI_BASE_PATH . "/include/package/package_constants.php");
require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");
require_once(CACTI_BASE_PATH . "/lib/package/package_form.php");
require_once(CACTI_BASE_PATH . "/lib/package/package_update.php");
require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_info.php");
require_once(CACTI_BASE_PATH . "/lib/graph_template/graph_template_info.php");
require_once(CACTI_BASE_PATH . "/lib/sys/package_export.php");

/* set default action */
if (!isset($_REQUEST["action"])) { $_REQUEST["action"] = ""; }

switch ($_REQUEST["action"]) {
	case 'save':
		form_save();

		break;
	case 'new':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_new();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'edit':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_edit();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_view();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'view_metadata_screenshot':
		package_view_metadata_screenshot();

		break;
	case 'view_metadata_script':
		package_view_metadata_script();

		break;
	case 'edit_metadata':
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package_edit_metadata();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
	case 'remove_graph_template':
		package_remove_graph_template();

		break;
	case 'remove_metadata':
		package_remove_metadata();

		break;
	default:
		require_once(CACTI_BASE_PATH . "/include/top_header.php");

		package();

		require_once(CACTI_BASE_PATH . "/include/bottom_footer.php");
		break;
}

function form_save() {
	if ($_POST["action_post"] == "package_new") {
		header("Location: packages.php?action=edit");
	}else if ($_POST["action_post"] == "package_edit") {
		/* the "Add" associated graph template button was pressed */
		if (isset($_POST["assoc_graph_template_add_y"])) {
			api_package_package_template_add($_POST["package_id"], $_POST["assoc_graph_template_id"]);
			header("Location: packages.php?action=edit&id=" . $_POST["package_id"]);
			exit;
		}

		/* cache all post field values */
		init_post_field_cache();

		/* step #2: field validation */
		$form_package["id"] = $_POST["package_id"];
		$form_package["name"] = $_POST["name"];
		$form_package["description"] = $_POST["description"];
		$form_package["description_install"] = $_POST["description_install"];
		$form_package["category"] = ($_POST["category"] == "new" ? $_POST["category_txt"] : api_data_preset_package_category_get($_POST["category_drp"]));
		$form_package["subcategory"] = ($_POST["subcategory"] == "new" ? $_POST["subcategory_txt"] : api_data_preset_package_subcategory_get($_POST["subcategory_drp"]));
		$form_package["vendor"] = ($_POST["vendor"] == "new" ? $_POST["vendor_txt"] : api_data_preset_package_vendor_get($_POST["vendor_drp"]));
		$form_package["model"] = $_POST["model"];

		/* the author field values may either come from the form or from the database */
		if ($_POST["author_type"] == "new") {
			$form_package["author_name"] = $_POST["author_name"];
			$form_package["author_email"] = $_POST["author_email"];
			$form_package["author_user_repository"] = $_POST["author_user_repository"];
			$form_package["author_user_forum"] = $_POST["author_user_forum"];
		}else if ($_POST["author_type"] == "existing") {
			$package_author = api_package_author_get($_POST["author_type_drp"]);

			$form_package["author_name"] = $package_author["name"];
			$form_package["author_email"] = $package_author["email"];
			$form_package["author_user_repository"] = $package_author["user_repository"];
			$form_package["author_user_forum"] = $package_author["user_forum"];
		}

		field_register_error(api_package_field_validate($form_package, "|field|"));

		/* the custom category textbox goes by a different name on the form */
		if (field_error_isset("category")) {
			field_register_error("category_txt");
		}

		/* the custom subcategory textbox goes by a different name on the form */
		if (field_error_isset("subcategory")) {
			field_register_error("subcategory_txt");
		}

		/* step #3: field save */
		$package_id = false;
		if (is_error_message()) {
			api_log_log("User input validation error for package [ID#" . $_POST["package_id"] . "]", SEV_DEBUG);
		}else{
			$package_id = api_package_save($_POST["package_id"], $form_package);

			if ($package_id === false) {
				api_log_log("Save error for package [ID#" . $_POST["package_id"] . "]", SEV_ERROR);
			}
		}

		if (($package_id === false) || (empty($_POST["package_id"]))) {
			header("Location: packages.php?action=edit" . (empty($_POST["package_id"]) ? "" : "&id=" . $_POST["package_id"]));
		}else{
			header("Location: packages.php");
		}
	}else if ($_POST["action_post"] == "package_edit_metadata") {
		/* cache all post field values */
		init_post_field_cache();

		/* step #2: field validation */
		$form_package_metadata["id"] = $_POST["package_metadata_id"];
		$form_package_metadata["package_id"] = $_POST["package_id"];
		$form_package_metadata["type"] = $_POST["type"];
		$form_package_metadata["name"] = $_POST["name"];
		$form_package_metadata["description"] = $_POST["description"];

		if ($_POST["type"] == PACKAGE_METADATA_TYPE_SCREENSHOT) {
			/* make sure there is a valid file that was uploaded via an HTTP POST */
			if ((isset($_FILES["payload_upl"])) && (is_uploaded_file($_FILES["payload_upl"]["tmp_name"]))) {
				$fp = fopen($_FILES["payload_upl"]["tmp_name"], "r");
				$raw_data = fread($fp, $_FILES["payload_upl"]["size"]);
				fclose($fp);

				$form_package_metadata["mime_type"] = $_FILES["payload_upl"]["type"];
				$form_package_metadata["payload"] = $raw_data;
			}
		}else if ($_POST["type"] == PACKAGE_METADATA_TYPE_SCRIPT) {
			$form_package_metadata["description_install"] = $_POST["description_install"];
			$form_package_metadata["required"] = html_boolean(isset($_POST["required"]) ? $_POST["required"] : "");
			$form_package_metadata["mime_type"] = "text/plain";
			$form_package_metadata["payload"] = $_POST["payload_txt"];
		}

		field_register_error(api_package_field_validate($form_package_metadata, "|field|"));

		/* step #3: field save */
		$package_metadata_id = false;
		if (is_error_message()) {
			api_log_log("User input validation error for package metadata [ID#" . $_POST["package_metadata_id"] . "], package [ID#" . $_POST["package_id"] . "]", SEV_DEBUG);
		}else{
			$package_metadata_id = api_package_metadata_save($_POST["package_metadata_id"], $form_package_metadata);

			if ($package_metadata_id === false) {
				api_log_log("Save error for package metadata [ID#" . $_POST["package_metadata_id"] . "], package [ID#" . $_POST["package_id"] . "]", SEV_ERROR);
			}
		}

		if ($package_metadata_id === false) {
			header("Location: packages.php?action=edit_metadata&package_id=" . $_POST["package_id"] . (empty($_POST["package_metadata_id"]) ? "" : "&id=" . $_POST["package_metadata_id"]));
		}else{
			/* the cache will not be purged in time unless to do it here */
			kill_post_field_cache();

			header("Location: packages.php?action=edit&id=" . $_POST["package_id"]);
		}
	}
}

function package_remove_graph_template() {
	api_package_graph_template_remove($_GET["id"], $_GET["graph_template_id"]);

	header("Location: packages.php?action=edit&id=" . $_GET["id"]);
}

function package_remove_metadata() {
	api_package_metadata_remove($_GET["package_metadata_id"]);

	header("Location: packages.php?action=edit&id=" . $_GET["id"]);
}

function package_new() {
	form_start("packages.php", "form_package");

	html_start_box("<strong>" . _("Template Packages") . "</strong> [new]");

	_package_field__create_type("create_type", "new");

	html_end_box();

	form_hidden_box("action_post", "package_new");

	form_save_button("packages.php", "save_package");
}

function package_edit_metadata() {
	$_package_id = get_get_var_number("package_id");
	$_package_metadata_id = get_get_var_number("id");

	if (empty($_package_metadata_id)) {
		$header_label = "[new]";
	}else{
		$package_metadata = api_package_metadata_get($_package_metadata_id);

		$header_label = "[edit: " . $package_metadata["name"] . "]";
	}

	form_start("packages.php", "form_package", true);

	/* ==================== Box: Template Package Metadata ==================== */

	html_start_box("<strong>" . _("Template Package Metadata") . "</strong> $header_label");

	_package_metadata_field__type("type", (isset($package_metadata["type"]) ? $package_metadata["type"] : ""), "0");
	_package_metadata_field__name("name", (isset($package_metadata["name"]) ? $package_metadata["name"] : ""), "0");
	_package_metadata_field__description("description", (isset($package_metadata["description"]) ? $package_metadata["description"] : ""), "0");
	_package_metadata_field__description_install("description_install", (isset($package_metadata["description_install"]) ? $package_metadata["description_install"] : ""), "0");
	_package_metadata_field__required("required", (isset($package_metadata["required"]) ? $package_metadata["required"] : ""), "0");
	_package_metadata_field__payload("payload", (isset($package_metadata["payload"]) ? $package_metadata["payload"] : ""), "0");
	_package_metadata_field__type_js();

	html_end_box();

	form_hidden_box("action_post", "package_edit_metadata");
	form_hidden_box("package_id", $_package_id);
	form_hidden_box("package_metadata_id", $_package_metadata_id);

	form_save_button("packages.php?action=edit&id=" . $_GET["package_id"], "save_package");
}

function package_edit() {
	$_package_id = get_get_var_number("id");

	if (empty($_package_id)) {
		$header_label = "[new]";
	}else{
		$package = api_package_get($_package_id);

		/* get a list of each graph template that is associated with this package */
		$graph_templates = api_package_graph_template_list($_package_id);

		/* get a list of all of the metadata associated with a particular package */
		$metadata_items = api_package_metadata_list($_package_id);

		$header_label = "[edit: " . $package["name"] . "]";
	}

	form_start("packages.php", "form_package");

	/* ==================== Box: Template Packages ==================== */

	html_start_box("<strong>" . _("Template Packages") . "</strong> $header_label");

	_package_field__name("name", (isset($package["name"]) ? $package["name"] : ""), "0");
	_package_field__description("description", (isset($package["description"]) ? $package["description"] : ""), "0");
	_package_field__description_install("description_install", (isset($package["description_install"]) ? $package["description_install"] : ""), "0");
	_package_field__category("category", (isset($package["category"]) ? $package["category"] : ""), "0");
	_package_field__subcategory("subcategory", (isset($package["subcategory"]) ? $package["subcategory"] : ""), "0");
	_package_field__vendor("vendor", (isset($package["vendor"]) ? $package["vendor"] : ""), "0");
	_package_field__model("model", (isset($package["model"]) ? $package["model"] : ""), "0");
	_package_field__author_hdr();
	_package_field__author_type("author_type", (isset($package) ? "existing" : "new"), "0");
	_package_author_field__name("author_name", (isset($package["author_name"]) ? $package["author_name"] : ""), "0");
	_package_author_field__email("author_email", (isset($package["author_email"]) ? $package["author_email"] : ""), "0");
	_package_author_field__user_forum("author_user_forum", (isset($package["author_user_forum"]) ? $package["author_user_forum"] : ""), "0");
	_package_author_field__user_repository("author_user_repository", (isset($package["author_user_repository"]) ? $package["author_user_repository"] : ""), "0");
	_package_author_type_js();

	html_end_box();

	if (!empty($_package_id)) {
		/* ==================== Box: Associated Graph Templates ==================== */

		html_start_box("<strong>" . _("Associated Graph Templates") . "</strong>");
		html_header(array(_("Template Title")), 2);

		if (sizeof($graph_templates) > 0) {
			foreach ($graph_templates as $graph_template) {
				?>
				<tr class="content-row">
					<td class="content-row" style="padding: 4px;">
						<?php echo $graph_template["template_name"];?>
					</td>
					<td class="content-row" align="right" style="padding: 4px;">
						<a href="packages.php?action=remove_graph_template&id=<?php echo $_package_id;?>&graph_template_id=<?php echo $graph_template["id"];?>"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Graph Template Association");?>" border="0" align="absmiddle"></a>
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr>
				<td class="content-list-empty" colspan="2">
					No graph templates have been associated with this package.
				</td>
			</tr>
			<?php
		}

		?>
		<tr>
			<td style="border-top: 1px solid #b5b5b5; padding: 1px;" colspan="2">
				<table width="100%" cellpadding="2" cellspacing="0">
					<tr>
						<td>
							Add graph template:
							<?php form_dropdown("assoc_graph_template_id", api_graph_template_list(), "template_name", "id", "", "", "");?>
						</td>
						<td align="right">
							&nbsp;<input type="image" src="<?php echo html_get_theme_images_path('button_add.gif');?>" alt="<?php echo _('Add');?>" name="assoc_graph_template_add" align="absmiddle">
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<?php

		html_end_box();

		/* ==================== Box: Associated Meta Data ==================== */

		html_start_box("<strong>" . _("Associated Meta Data") . "</strong>", "packages.php?action=edit_metadata&package_id=$_package_id");
		html_header(array(_("Name"), _("Type")), 2);

		if (sizeof($metadata_items) > 0) {
			$metadata_types = api_package_metadata_type_list();

			foreach ($metadata_items as $metadata_item) {
				?>
				<tr class="content-row">
					<td class="content-row" style="padding: 4px;">
						<a class="linkEditMain" href="packages.php?action=edit_metadata&id=<?php echo $metadata_item["id"];?>&package_id=<?php echo $_package_id;?>"><?php echo $metadata_item["name"];?></a>
					</td>
					<td class="content-row" style="padding: 4px;">
						<?php echo $metadata_types{$metadata_item["type"]};?>
					</td>
					<td class="content-row" align="right" style="padding: 4px;">
						<a href="packages.php?action=remove_metadata&id=<?php echo $_package_id;?>&package_metadata_id=<?php echo $metadata_item["id"];?>"><img src="<?php echo html_get_theme_images_path("delete_icon_large.gif");?>" alt="<?php echo _("Delete Package Metadata Item");?>" border="0" align="absmiddle"></a>
					</td>
				</tr>
				<?php
			}
		}else{
			?>
			<tr>
				<td class="content-list-empty" colspan="2">
					No metadata items have been associated with this package.
				</td>
			</tr>
			<?php
		}

		html_end_box();
	}

	form_hidden_box("action_post", "package_edit");
	form_hidden_box("package_id", $_package_id);

	form_save_button("packages.php", "save_package");
}

function package_view_metadata_screenshot() {
	$_package_metadata_id = get_get_var_number("id");

	if (!empty($_package_metadata_id)) {
		$metadata = api_package_metadata_get($_package_metadata_id);

		header("Content-type: " . $metadata["mime_type"]);
		header("Content-size: " . strlen($metadata["payload"]));
		echo $metadata["payload"];
	}
}

function package_view_metadata_script() {
	$_package_metadata_id = get_get_var_number("id");

	if (!empty($_package_metadata_id)) {
		$metadata = api_package_metadata_get($_package_metadata_id);

		header("Content-type: text/plain");
		header("Content-size: " . strlen($metadata["payload"]));
		echo $metadata["payload"];
	}
}

function package_view() {
	$_package_id = get_get_var_number("id");

	if (!empty($_package_id)) {
		/* get information about this package */
		$package = api_package_get($_package_id);

		/* get a list of scripts associated with this package */
		$package_scripts = api_package_metadata_list($_package_id, PACKAGE_METADATA_TYPE_SCRIPT);

		/* get a list of screenshots associated with this package */
		$package_screenshots = api_package_metadata_list($_package_id, PACKAGE_METADATA_TYPE_SCREENSHOT);

		/* get a list of all graph templates associated with this package */
		$package_templates = api_package_graph_template_list($_package_id);

		?>
		<table width="98%" align="center" cellspacing="0" cellpadding="3">
			<tr>
				<td valign="top">
					<span class="textInfo"><?php echo htmlspecialchars($package["name"]);?></span><br>
					<span class="textArea"><?php echo nl2br(htmlspecialchars($package["description"]));?></a>
				</td>
			</tr>
		</table>
		<br>
		<table width="98%" align="center" cellspacing="1" cellpadding="3">
			<tr>
				<td style="background-color: #9C9C9C; color: white;" colspan="2">
					<strong>Basic Information</strong>
				</td>
			</tr>
			<tr>
				<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Category</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["category"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Sub Category</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["subcategory"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Vendor</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["vendor"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Model/Version</strong>
				</td>
				<td>
					<?php echo $package["model"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #9C9C9C; color: white;" colspan="2">
					<strong>Author Information</strong>
				</td>
			</tr>
			<tr>
				<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Author Name</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["author_name"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Author Email</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["author_email"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Forum User</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["author_user_forum"];?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
					<strong>Template Repository User</strong>
				</td>
				<td style="border-bottom: 1px solid #f7f7f7;">
					<?php echo $package["author_user_repository"];?>
				</td>
			</tr>
		</table>
		<?php
		if (sizeof($package_templates) > 0) {
			?>
			<br>
			<table width="98%" align="center" cellspacing="0" cellpadding="3">
				<tr>
					<td>
						<p class="textInfo">Associated Graph Templates</p>
					</td>
				</tr>
				<tr>
					<td>
						<ul style="list-style-type: disc; font-size: 12px;">
						<?php
						foreach ($package_templates as $template) {
							echo "<li><a href=\"graph_templates.php?action=edit&id=" . $template["id"] . "\">" . $template["template_name"] . "</a></li>\n";
						}
						?>
						</ul>
					</td>
				</tr>
			</table>
			<?php
		}
		?>
		<br>
		<table width="98%" align="center" cellspacing="0" cellpadding="3">
			<tr>
				<td valign="top">
					<p class="textInfo">Installation Instructions</p>
					<p style="font-family: monospace;"><?php echo nl2br(htmlspecialchars($package["description_install"]));?>
				</td>
			</tr>
		</table>
		<?php
		if (sizeof($package_scripts) > 0) {
			?>
			<br>
			<table width="98%" align="center" cellspacing="0" cellpadding="3">
				<tr>
					<td>
						<p class="textInfo">Scripts</p>
					</td>
				</tr>
			</table>
			<br>
			<table width="98%" align="center" cellspacing="1" cellpadding="3">
				<?php
				foreach ($package_scripts as $script) {
					?>
					<tr>
						<td style="background-color: #9C9C9C; color: white;" colspan="2">
							<strong><?php echo $script["name"];?></strong>
						</td>
					</tr>
					<tr>
						<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
							<strong>Description</strong>
						</td>
						<td style="border-bottom: 1px solid #f7f7f7;">
							<?php echo nl2br(htmlspecialchars($script["description"]));?>
						</td>
					</tr>
					<tr>
						<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
							<strong>Required</strong>
						</td>
						<td style="border-bottom: 1px solid #f7f7f7;">
							<?php echo (empty($script["required"]) ? "No" : "Yes");?>
						</td>
					</tr>
					<tr>
						<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
							<strong>Installation Instructions</strong>
						</td>
						<td style="border-bottom: 1px solid #f7f7f7;">
							<?php echo nl2br(htmlspecialchars($script["description_install"]));?>
						</td>
					</tr>
					<tr>
						<td width="200" style="background-color: #f5f5f5; border-right: 1px dashed #d1d1d1;">
							<strong>Payload</strong>
						</td>
						<td style="border-bottom: 1px solid #f7f7f7;">
							<a href="packages.php?action=view_metadata_script&id=<?php echo $script["id"];?>" target="_new">Download Script</a>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		<?php
		}

		if (sizeof($package_screenshots) > 0) {
			?>
			<br>
			<table width="98%" align="center" cellspacing="0" cellpadding="3">
				<tr>
					<td>
						<p class="textInfo">Screenshots</p>
					</td>
				</tr>
				<tr>
					<td>
						<ul style="list-style-type: disc; font-size: 12px;">
						<?php
						$js_id_list = "";
						$js_title_list = "";
						$js_description_list = "";
						$i = 0;
						foreach ($package_screenshots as $screenshot) {
							echo "<li><a href=\"javascript:view_screenshot($i)\">" . $screenshot["name"] . "</a></li>\n";

							$js_id_list .= "\"" . $screenshot["id"] . "\"" . ($i < sizeof($package_screenshots) - 1 ? "," : "");
							$js_title_list .= "\"" . addslashes(htmlspecialchars($screenshot["name"])) . "\"" . ($i < sizeof($package_screenshots) - 1 ? "," : "");
							$js_description_list .= "\"" . addslashes(htmlspecialchars($screenshot["description"])) . "\"" . ($i < sizeof($package_screenshots) - 1 ? "," : "");

							$i++;
						}
						?>
						</ul>
					</td>
				</tr>
				<tr>
					<td>
					</td>
				</tr>
				<tr>
					<td style="background-color: #9C9C9C; color: white;" colspan="2">
						<strong><span id="screenshot_title"></span></strong>
					</td>
				</tr>
				<tr>
					<td>
						<span id="screenshot_description"></span>
					</td>
				</tr>
				<tr>
					<td>
						<img id="screenshot_image" src="" alt="">
					</td>
				</tr>
			</table>

			<script language="JavaScript">
			<!--
			var screenshot_ids = new Array(<?php echo $js_id_list;?>);
			var screenshot_titles = new Array(<?php echo $js_title_list;?>);
			var screenshot_descriptions = new Array(<?php echo $js_description_list;?>);

			function view_screenshot(id) {
				document.getElementById('screenshot_title').innerHTML = screenshot_titles[id];
				document.getElementById('screenshot_description').innerHTML = screenshot_descriptions[id];
				document.getElementById('screenshot_image').src = 'packages.php?action=view_metadata_screenshot&id=' + screenshot_ids[id];
			}

			view_screenshot(0);
			-->
			</script>
			<?php
		}
	}
}

function package() {
	$menu_items = array(
		"remove" => "Remove",
		"duplicate" => "Duplicate"
		);

	$filter_array = array();

	/* search field: filter (searches package name) */
	if (isset_get_var("search_filter")) {
		$filter_array["name"] = get_get_var("search_filter");
	}

	/* get a list of all packages on this page */
	$packages = api_package_list($filter_array);

	form_start("packages.php");

	$box_id = "1";
	html_start_box("<strong>" . _("Template Packages") . "</strong>", "packages.php?action=new");
	html_header_checkbox(array(_("Name"), _("Author"), _("Category")), $box_id);

	$i = 0;
	if (sizeof($packages) > 0) {
		foreach ($packages as $package) {
			?>
			<tr class="content-row" id="box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>" onClick="display_row_select('<?php echo $box_id;?>',document.forms[0],'box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>', 'box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>')" onMouseOver="display_row_hover('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')" onMouseOut="display_row_clear('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')">
				<td class="content-row">
					<a class="linkEditMain" onClick="display_row_block('box-<?php echo $box_id;?>-row-<?php echo $package["id"];?>')" href="packages.php?action=view&id=<?php echo $package["id"];?>"><span id="box-<?php echo $box_id;?>-text-<?php echo $package["id"];?>"><?php echo html_highlight_words(get_get_var("search_filter"), $package["name"]);?></span></a>
				</td>
				<td class="content-row">
					Ian Berry
				</td>
				<td class="content-row">
					<?php echo $package["category"];?>
				</td>
				<td class="content-row" width="1%" align="center" style="border-left: 1px solid #b5b5b5; border-top: 1px solid #b5b5b5; background-color: #e9e9e9; <?php echo get_checkbox_style();?>">
					<input type='checkbox' style='margin: 0px;' name='box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>' id='box-<?php echo $box_id;?>-chk-<?php echo $package["id"];?>' title="<?php echo $package["name"];?>">
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr>
			<td class="content-list-empty" colspan="6">
				No template packages found.
			</td>
		</tr>
		<?php
	}
	html_box_toolbar_draw($box_id, "0", "3", HTML_BOX_SEARCH_NONE);
	html_end_box(false);

	html_box_actions_menu_draw($box_id, "0", $menu_items);
	html_box_actions_area_draw($box_id, "0");

	form_hidden_box("action_post", "package_list");
	form_end();

	echo "<pre>" . htmlspecialchars(package_payload_export("1")) . "</pre>";

	?>

	<script language="JavaScript">
	<!--
	function action_area_handle_type(box_id, type, parent_div, parent_form) {
		if (type == 'remove') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to remove these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));

			action_area_update_header_caption(box_id, 'Remove Data Template');
			action_area_update_submit_caption(box_id, 'Remove');
			action_area_update_selected_rows(box_id, parent_form);
		}else if (type == 'duplicate') {
			parent_div.appendChild(document.createTextNode('Are you sure you want to duplicate these data templates?'));
			parent_div.appendChild(action_area_generate_selected_rows(box_id));
			parent_div.appendChild(action_area_generate_input('text', 'box-' + box_id + '-action-area-txt1', ''));

			action_area_update_header_caption(box_id, 'Duplicate Data Templates');
			action_area_update_submit_caption(box_id, 'Duplicate');
			action_area_update_selected_rows(box_id, parent_form);
		}
	}
	-->
	</script>

	<?php
}

?>
