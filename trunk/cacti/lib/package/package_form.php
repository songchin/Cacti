<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006 The Cacti Group                                      |
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

/* form validation functions */

function api_package_field_validate(&$_fields_package, $package_field_name_format = "|field|") {
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");

	if (sizeof($_fields_package) == 0) {
		return array();
	}

	/* array containing errored fields */
	$error_fields = array();

	/* get a complete field list */
	$fields_package = api_package_form_list();

	/* base fields */
	while (list($_field_name, $_field_array) = each($fields_package)) {
		if ((isset($_fields_package[$_field_name])) && (isset($_field_array["validate_regexp"])) && (isset($_field_array["validate_empty"]))) {
			$form_field_name = str_replace("|field|", $_field_name, $package_field_name_format);

			if (!form_input_validate($_fields_package[$_field_name], $form_field_name, $_field_array["validate_regexp"], $_field_array["validate_empty"])) {
				$error_fields[] = $form_field_name;
			}
		}
	}

	return $error_fields;
}

/* new package fields */

function _package_field__create_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("New Package Type");?></span><br>
			<?php echo _("How this package should be created.");?>
		</td>
		<td class="field-row" colspan="2">
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $field_value, "new", "", "new", "click_create_type_radio()");?>
					</td>
					<td>
						Create entirely new package
					</td>
				</tr>
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $field_value, "existing", "", "new", "click_create_type_radio()");?>
					</td>
					<td>
						Create based on an existing package
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php form_dropdown($field_name . "_id", api_package_list(), "name", "id", "", "", "");?>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	function click_create_type_radio() {
		if (get_radio_value(document.forms[0].<?php echo $field_name;?>) == 'new') {
			select_radio_create_type_new();
		}else{
			select_radio_create_type_existing();
		}
	}

	function select_radio_create_type_new() {
		document.getElementById('<?php echo $field_name;?>_id').disabled = true;
	}

	function select_radio_create_type_existing() {
		document.getElementById('<?php echo $field_name;?>_id').disabled = false;
	}

	click_create_type_radio();
	-->
	</script>

	<?php
}

/* base package fields */

function _package_field__name($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A short name for this package");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
		<td align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _package_field__description($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Description");?></span><br>
			<?php echo _("A longer, more descriptive name for this package");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 40, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_field__description_install($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Installation Instructions");?></span><br>
			<?php echo _("Detailed instructions for this package, if required after the package is imported");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_area($field_name, $field_value, "5", "30", "");?>
		</td>
	</tr>
	<?php
}

function _package_field__category($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_package_info.php");

	/* obtain a list of preset categories for the dropdown */
	$category_list = api_data_preset_package_category_list();

	/* try to be smart about whether to select the "new" or "existing" radio box */
	if (($field_value == "") || (in_array($field_value, $category_list))) {
		$radio_value = "existing";
	}else{
		$radio_value = "new";
	}

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Category");?></span><br>
			<?php echo _("The generic category that can be used to describe the purpose of this package");?>
		</td>
		<td class="field-row">
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "existing", "", "new", "click_category_radio()");?>
					</td>
					<td>
						Use existing category
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_drp";?>">
					<td>
					</td>
					<td>
						<?php form_dropdown($field_name . "_drp", $category_list, "", "", array_search($field_value, $category_list), "", "");?>
					</td>
				</tr>
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "new", "", "new", "click_category_radio()");?>
					</td>
					<td>
						Specify new category
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_txt";?>">
					<td>
					</td>
					<td>
						<?php form_text_box($field_name . "_txt", $field_value, "", 100, 30, "text", $field_id);?>
					</td>
				</tr>
			</table>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	function click_category_radio() {
		if (get_radio_value(document.forms[0].<?php echo $field_name;?>) == 'new') {
			select_radio_category_new();
		}else{
			select_radio_category_existing();
		}
	}

	function select_radio_category_new() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'table-row';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'none';
	}

	function select_radio_category_existing() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'none';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'table-row';
	}

	click_category_radio();
	-->
	</script>

	<?php
}

function _package_field__subcategory($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_package_info.php");

	/* obtain a list of preset subcategories for the dropdown */
	$subcategory_list = api_data_preset_package_subcategory_list();

	/* try to be smart about whether to select the "new" or "existing" radio box */
	if (($field_value == "") || (in_array($field_value, $subcategory_list))) {
		$radio_value = "existing";
	}else{
		$radio_value = "new";
	}

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Subcategory");?></span><br>
			<?php echo _("A more descriptive category that can be used to describe the purpose of this package.");?>
		</td>
		<td class="field-row">
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "existing", "", "new", "click_subcategory_radio()");?>
					</td>
					<td>
						Use existing subcategory
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_drp";?>">
					<td>
					</td>
					<td>
						<?php form_dropdown($field_name . "_drp", $subcategory_list, "", "", array_search($field_value, $subcategory_list), "", "");?>
					</td>
				</tr>
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "new", "", "new", "click_subcategory_radio()");?>
					</td>
					<td>
						Specify new subcategory
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_txt";?>">
					<td>
					</td>
					<td>
						<?php form_text_box($field_name . "_txt", $field_value, "", 100, 30, "text", $field_id);?>
					</td>
				</tr>
			</table>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	function click_subcategory_radio() {
		if (get_radio_value(document.forms[0].<?php echo $field_name;?>) == 'new') {
			select_radio_subcategory_new();
		}else{
			select_radio_subcategory_existing();
		}
	}

	function select_radio_subcategory_new() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'table-row';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'none';
	}

	function select_radio_subcategory_existing() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'none';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'table-row';
	}

	click_subcategory_radio();
	-->
	</script>

	<?php
}

function _package_field__vendor($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/data_preset/data_preset_package_info.php");

	/* obtain a list of preset vendors for the dropdown */
	$vendor_list = api_data_preset_package_vendor_list();

	/* try to be smart about whether to select the "new" or "existing" radio box */
	if (($field_value == "") || (in_array($field_value, $vendor_list))) {
		$radio_value = "existing";
	}else{
		$radio_value = "new";
	}

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Vendor");?></span><br>
			<?php echo _("The vendor or manufactuer of what this package is graphing.");?>
		</td>
		<td class="field-row" colspan="2">
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "existing", "", "new", "click_vendor_radio()");?>
					</td>
					<td>
						Use existing vendor
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_drp";?>">
					<td>
					</td>
					<td>
						<?php form_dropdown($field_name . "_drp", $vendor_list, "", "", array_search($field_value, $vendor_list), "", "");?>
					</td>
				</tr>
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "new", "", "new", "click_vendor_radio()");?>
					</td>
					<td>
						Specify new vendor
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_txt";?>">
					<td>
					</td>
					<td>
						<?php form_text_box($field_name . "_txt", $field_value, "", 100, 30, "text", $field_id);?>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	function click_vendor_radio() {
		if (get_radio_value(document.forms[0].<?php echo $field_name;?>) == 'new') {
			select_radio_vendor_new();
		}else{
			select_radio_vendor_existing();
		}
	}

	function select_radio_vendor_new() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'table-row';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'none';
	}

	function select_radio_vendor_existing() {
		document.getElementById('<?php echo $field_name;?>_tr_txt').style.display = 'none';
		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'table-row';
	}

	click_vendor_radio();
	-->
	</script>

	<?php
}

function _package_field__model($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Model/Version");?></span><br>
			<?php echo _("The model or version of what this package is graphing.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

/* author specific fields */

function _package_field__author_hdr() {
	?>
	<tr id="row_field_script_header">
		<td class="content-header-sub" colspan="3">
			Author Information
		</td>
	</tr>
	<?php
}

function _package_field__author_type($field_name, $field_value = "", $field_id = 0) {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/lib/package/package_info.php");

	/* obtain a list of existing authors for the dropdown */
	$author_list = api_package_author_list();

	/* try to be smart about whether to select the "new" or "existing" radio box */
	if (($field_value == "") || (in_array($field_value, $author_list))) {
		$radio_value = "existing";
	}else{
		$radio_value = "new";
	}

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Author Type");?></span><br>
			<?php echo _("Whether to generate a new or use an existing author");?>
		</td>
		<td class="field-row" colspan="2">
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "existing", "", "new", "click_author_type_radio()");?>
					</td>
					<td>
						Use existing author
					</td>
				</tr>
				<tr id="<?php echo $field_name . "_tr_drp";?>">
					<td>
					</td>
					<td>
						<?php form_dropdown($field_name . "_drp", $author_list, "name", "id", "", "", "");?>
					</td>
				</tr>
				<tr>
					<td width="1%">
						<?php form_radio_button($field_name, $radio_value, "new", "", "new", "click_author_type_radio()");?>
					</td>
					<td>
						Specify new author
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	function click_author_type_radio() {
		if (get_radio_value(document.forms[0].<?php echo $field_name;?>) == 'new') {
			select_radio_author_type_new();
		}else{
			select_radio_author_type_existing();
		}
	}

	function select_radio_author_type_new() {
		document.getElementById('row_field_package_author_name').style.display = 'table-row';
		document.getElementById('row_field_package_author_email').style.display = 'table-row';
		document.getElementById('row_field_package_author_user_forum').style.display = 'table-row';
		document.getElementById('row_field_package_author_user_repository').style.display = 'table-row';

		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'none';
	}

	function select_radio_author_type_existing() {
		document.getElementById('row_field_package_author_name').style.display = 'none';
		document.getElementById('row_field_package_author_email').style.display = 'none';
		document.getElementById('row_field_package_author_user_forum').style.display = 'none';
		document.getElementById('row_field_package_author_user_repository').style.display = 'none';

		document.getElementById('<?php echo $field_name;?>_tr_drp').style.display = 'table-row';
	}
	-->
	</script>
	<?php
}

function _package_author_type_js() {
	?>
	<script language="JavaScript">
	<!--
	click_author_type_radio();
	-->
	</script>
	<?php
}

function _package_author_field__name($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_author_name">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("The full name of the author of this package");?>
		</td>
		<td class="field-row">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
		<td class="field-row" align="right">
			<span class="field-required">(required)</span>
		</td>
	</tr>
	<?php
}

function _package_author_field__email($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_author_email">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Email Address");?></span><br>
			<?php echo _("A valid email address for author of this package");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_author_field__user_forum($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_author_user_forum">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Forum User");?></span><br>
			<?php echo _("The author's username on the Cacti forums");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_author_field__user_repository($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_author_user_repository">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Template Repository User");?></span><br>
			<?php echo _("The author's username on the template repository site");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 20, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

/* meta data specific fields */

function _package_metadata_field__type($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");
	require_once(CACTI_BASE_PATH . "/include/package/package_constants.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Type");?></span><br>
			<?php echo _("Used to categorize the type of metadata that is being attached with this package");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_dropdown($field_name, api_package_metadata_type_list(), "", "", $field_value, "", "", "", "0", "select_metadata_type_dropdown()");?>
		</td>
	</tr>
	<?php
	?>
	<script language="JavaScript">
	<!--
	function select_metadata_type_dropdown() {
		if (document.getElementById('<?php echo $field_name;?>').value == <?php echo PACKAGE_METADATA_TYPE_SCREENSHOT;?>) {
			document.getElementById('row_field_package_metadata_description_install').style.display = 'none';
			document.getElementById('row_field_package_metadata_required').style.display = 'none';

			if (document.getElementById('row_field_package_payload_attach')) {
				document.getElementById('row_field_package_payload_attach').style.display = 'table-row';
			}

			if (document.getElementById('row_field_package_payload_paste')) {
				document.getElementById('row_field_package_payload_paste').style.display = 'none';
			}
		}else if (document.getElementById('<?php echo $field_name;?>').value == <?php echo PACKAGE_METADATA_TYPE_SCRIPT;?>) {
			document.getElementById('row_field_package_metadata_description_install').style.display = 'table-row';
			document.getElementById('row_field_package_metadata_required').style.display = 'table-row';

			if (document.getElementById('row_field_package_payload_attach')) {
				document.getElementById('row_field_package_payload_attach').style.display = 'none';
			}

			if (document.getElementById('row_field_package_payload_paste')) {
				document.getElementById('row_field_package_payload_paste').style.display = 'table-row';
			}
		}
	}
	-->
	</script>
	<?php
}

function _package_metadata_field__type_js() {
	?>
	<script language="JavaScript">
	<!--
	select_metadata_type_dropdown();
	-->
	</script>
	<?php
}

function _package_metadata_field__name($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Name");?></span><br>
			<?php echo _("A short name used to identify this piece of meta data");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 30, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_metadata_field__description($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Description");?></span><br>
			<?php echo _("A more descriptive explaination of the purpose of this meta data");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_box($field_name, $field_value, "", 100, 40, "text", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_metadata_field__description_install($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_metadata_description_install">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Installation Instructions");?></span><br>
			<?php echo _("A complete explaination of how to install or use this piece of meta data");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_area($field_name, $field_value, "5", "30", "");?>
		</td>
	</tr>
	<?php
}

function _package_metadata_field__required($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>" id="row_field_package_metadata_required">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Required");?></span><br>
			<?php echo _("Whether this piece of meta data is required for the package to function");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_checkbox($field_name, $field_value, "Required", "", $field_id);?>
		</td>
	</tr>
	<?php
}

function _package_metadata_field__payload($field_name, $field_value = "", $field_id = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	$row_style = field_get_row_style();

	?>
	<tr class="<?php echo $row_style;?>" id="row_field_package_payload_attach">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Payload");?></span><br>
			<?php echo _("Select a screenshot to attach to this package. Only GIF, PNG, and JPEG images are currently supported. The image can be no larger than 600x600.");?>
		</td>
		<td class="field-row" colspan="2">
			<input type="file" size="40" name="<?php echo $field_name;?>_upl">
		</td>
	</tr>
	<tr class="<?php echo $row_style;?>" id="row_field_package_payload_paste">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Payload");?></span><br>
			<?php echo _("Paste the contents of your script in this box. If this script has any external dependencies, be sure they are explained in the installation instructions.");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_area($field_name . "_txt", $field_value, "7", "40", "");?>
		</td>
	</tr>
	<?php
}

/* import package fields */

function _package_import_field__file($field_name, $field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Import Package from File");?></span><br>
			<?php echo _("Browse to the package XML file to import it into Cacti");?>
		</td>
		<td class="field-row" colspan="2">
			<input type="file" size="40" name="<?php echo $field_name;?>">
		</td>
	</tr>
	<?php
}

function _package_import_field__text($field_name, $field_value = "") {
	require_once(CACTI_BASE_PATH . "/lib/sys/html_form.php");

	?>
	<tr class="<?php echo field_get_row_style();?>">
		<td width="50%" class="field-row">
			<span class="textEditTitle"><?php echo _("Import Package from File");?></span><br>
			<?php echo _("Browse to the package XML file to import it into Cacti");?>
		</td>
		<td class="field-row" colspan="2">
			<?php form_text_area($field_name, $field_value, "10", "50", "");?>
		</td>
	</tr>
	<?php
}

?>
