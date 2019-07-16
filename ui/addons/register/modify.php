<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_register", array(
	"name" => "IF(module_register.display_name = ''
				        , REPLACE(module_register.name, '-', ' ')
				        , module_register.display_name
				    )"
));

if(isset($_REQUEST["repair"])) {
	$sSQL = "UPDATE module_register_fields SET
				display_name = REPLACE(name, '-', ' ')
			WHERE display_name = '' AND name <> ''";
	$db->execute($sSQL);

	$sSQL = "SELECT module_register_fields.*
			FROM module_register_fields
			WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrVgalleryField[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		} while($db->nextRecord());

		if(is_array($arrVgalleryField) && count($arrVgalleryField)) {
			foreach($arrVgalleryField AS $ID_field => $smart_url) {
				$sSQL = "UPDATE module_register_fields SET
							name = " . $db->toSql($smart_url) . "
						WHERE module_register_fields.ID = " . $db->toSql($ID_field, "Number");
				$db->execute($sSQL);				
			}
		}
	}
}



$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "RegisterConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_register";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_register.*
									, IF(module_register.display_name = ''
										, REPLACE(module_register.name, '-', ' ')
										, module_register.display_name
									) AS display_name
								FROM module_register 
								WHERE module_register.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->addEvent("on_do_action", "RegisterConfigModify_on_do_action");
if (check_function("MD_general_on_done_action"))
    $oRecord->addEvent("on_done_action", "MD_general_on_done_action");
$oRecord->tab = true;

/* Title Block */
system_ffcomponent_set_title(
    $record["name"]
    , true
    , false
    , false
    , $oRecord
);     
    
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oRecord->addContent(null, true, "Template");
$oRecord->groups["Template"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_template")
);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("register_config_name");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("register_config_name");
$oField->required = true;
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "default";
$oField->label = ffTemplate::_get_word_by_code("register_config_default");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_title";
$oField->label = ffTemplate::_get_word_by_code("register_config_show_title");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_default_tip";
$oField->label = ffTemplate::_get_word_by_code("register_config_default_tip");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_require_note";
$oField->label = ffTemplate::_get_word_by_code("register_config_require_note");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_pre_content";
$oField->label = ffTemplate::_get_word_by_code("register_config_fixed_pre_content");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_post_content";
$oField->label = ffTemplate::_get_word_by_code("register_config_fixed_post_content");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Template");


$oField = ffField::factory($cm->oPage);
$oField->id = "enable_newsletter";
$oField->label = ffTemplate::_get_word_by_code("register_config_newsletter");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "force_redirect";
$oField->label = ffTemplate::_get_word_by_code("register_config_force_redirect");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode";
$oField->label = ffTemplate::_get_word_by_code("register_config_display_view_mode");
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_email";
$oField->label = ffTemplate::_get_word_by_code("register_config_email");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT  ID, name FROM email";
$oField->actex_dialog_url = $cm->oPage->site_path . STAGE_ADMIN . "/utility/email/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailModify_confirmdelete";
$oField->resources[] = "EmailModify";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_email_activation";
$oField->label = ffTemplate::_get_word_by_code("register_config_email_activation");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT  ID, name FROM email";
$oField->actex_dialog_url = $cm->oPage->site_path . STAGE_ADMIN . "/utility/email/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailModify_confirmdelete";
$oField->resources[] = "EmailModify";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "default_show_label";
$oField->label = ffTemplate::_get_word_by_code("register_show_label");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField, "Template");


$oRecord->addContent(null, true, "Account");
$oRecord->groups["Account"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_account")
);

$oField = ffField::factory($cm->oPage);
$oField->id = "disable_account_registration";
$oField->label = ffTemplate::_get_word_by_code("register_config_disable_account_registration");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "simple_registration";
$oField->label = ffTemplate::_get_word_by_code("register_config_simple_registration");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "activation";
$oField->label = ffTemplate::_get_word_by_code("register_config_activation");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
    array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("activation_no"))),
    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_user"))),
    array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_admin"))),
    array(new ffData("4", "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_user_admin")))
);
$oField->required = true;
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_user_menu";
$oField->label = ffTemplate::_get_word_by_code("enable_user_menu");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_general_data";
$oField->label = ffTemplate::_get_word_by_code("register_config_general_data");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");
/*
  $oField = ffField::factory($cm->oPage);
  $oField->id = "enable_bill_data";
  $oField->label = ffTemplate::_get_word_by_code("register_config_bill_data");
  $oField->base_type = "Number";
  $oField->extended_type = "Boolean";
  $oField->control_type = "checkbox";
  $oField->checked_value = new ffData("1", "Number");
  $oField->unchecked_value = new ffData("0", "Number");
  $oRecord->addContent($oField, "Account");

  $oField = ffField::factory($cm->oPage);
  $oField->id = "enable_ecommerce_data";
  $oField->label = ffTemplate::_get_word_by_code("register_config_ecommerce_data");
  $oField->base_type = "Number";
  $oField->extended_type = "Boolean";
  $oField->control_type = "checkbox";
  $oField->checked_value = new ffData("1", "Number");
  $oField->unchecked_value = new ffData("0", "Number");
  $oRecord->addContent($oField, "Account");
 */
$oField = ffField::factory($cm->oPage);
$oField->id = "enable_manage_account";
$oField->label = ffTemplate::_get_word_by_code("register_config_manage_account");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "public";
$oField->label = ffTemplate::_get_word_by_code("register_config_public");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

$oField = ffField::factory($cm->oPage);
$oField->id = "generate_password";
$oField->label = ffTemplate::_get_word_by_code("register_config_generate_password");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, "Account");

if (defined("VG_WS_ECOMMERCE") && strlen(VG_WS_ECOMMERCE)) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_anagraph_type";
    $oField->label = ffTemplate::_get_word_by_code("register_config_anagraph_type");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT ID, name FROM anagraph_type ORDER BY name";
    $oField->widget = "actex";
    $oField->actex_update_from_db = true;
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/ecommerce/anagraph/type/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=AnagraphTypeModify_confirmdelete";
    $oField->resources[] = "AnagraphTypeModify";
    $oField->multi_select_noone = true;
    $oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
    $oField->multi_select_noone_val = new ffData("0", "Number");
    $oField->required = true;
    $oRecord->addContent($oField, "Account");
}

$cm->oPage->addContent($oRecord);

$oField = ffField::factory($cm->oPage);
$oField->id = "primary_gid";
$oField->label = ffTemplate::_get_word_by_code("register_config_primary_gid");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT gid, name FROM " . CM_TABLE_PREFIX . "mod_security_groups";
$oField->required = true;
$oRecord->addContent($oField, "Account");

$oDetailGroup = ffDetails::factory($cm->oPage);
$oDetailGroup->id = "RegisterGroup";
$oDetailGroup->src_table = "module_register_rel_gid";
$oDetailGroup->order_default = "ID";
$oDetailGroup->fields_relationship = array("ID_module_register" => "ID");
$oDetailGroup->display_new = false;
$oDetailGroup->display_delete = FALSE;
$oDetailGroup->auto_populate_insert = true;
$oDetailGroup->populate_insert_SQL = "SELECT  
                                        0 AS value 
                                        , " . CM_TABLE_PREFIX . "mod_security_groups.gid AS gid
                                    FROM 
                                        " . CM_TABLE_PREFIX . "mod_security_groups
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name <> '" . Cms::env("MOD_AUTH_GUEST_GROUP_NAME") . "'
                                    ORDER BY 
                                        " . CM_TABLE_PREFIX . "mod_security_groups.name";
$oDetailGroup->auto_populate_edit = true;
$oDetailGroup->populate_edit_SQL = "SELECT  
                                        (SELECT module_register_rel_gid.ID FROM module_register_rel_gid WHERE module_register_rel_gid.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid AND module_register_rel_gid.ID_module_register = [ID_FATHER] ) AS ID
                                        , [ID_FATHER] AS ID_module_register 
                                        , (SELECT module_register_rel_gid.value FROM module_register_rel_gid WHERE module_register_rel_gid.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid AND module_register_rel_gid.ID_module_register = [ID_FATHER] ) AS value 
                                        , " . CM_TABLE_PREFIX . "mod_security_groups.gid AS gid
                                    FROM 
                                        " . CM_TABLE_PREFIX . "mod_security_groups
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name <> '" . Cms::env("MOD_AUTH_GUEST_GROUP_NAME") . "' 
                                    ORDER BY 
                                        " . CM_TABLE_PREFIX . "mod_security_groups.name";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetailGroup->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = "Sel";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailGroup->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "gid";
$oField->label = ffTemplate::_get_word_by_code("register_config_group_name");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT gid, name FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
$oField->control_type = "label";
$oDetailGroup->addContent($oField);

$oRecord->addContent($oDetailGroup, "Account");
$cm->oPage->addContent($oDetailGroup);

if ($_REQUEST["keys"]["ID"]) {
    

$oRecord->addContent(null, true, "SEO");
$oRecord->groups["SEO"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_SEO")
);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_smart_url");
$oField->extended_type = "Selection";
$oField->required = true;
/*$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = "";
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare = "name";*/
$oField->widget = "actex";
$oField->actex_autocomp = true;	
$oField->actex_multi = true;
$oField->actex_update_from_db = true;	
$oField->source_SQL = "SELECT module_register_fields.ID
                            , module_register_fields.name
                        FROM module_register_fields
                        WHERE module_register_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                        [AND][WHERE]
                        [HAVING]
                        ORDER BY name
                        [LIMIT]";
$oRecord->addContent($oField, "SEO");

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_description";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_meta_description");
$oField->extended_type = "Selection";
$oField->required = true;
/*$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = "";
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare = "name";*/
$oField->widget = "actex";
$oField->actex_autocomp = true;	
$oField->actex_multi = true;
$oField->actex_update_from_db = true;	
$oField->source_SQL = "SELECT module_register_fields.ID
                            , module_register_fields.name
                        FROM module_register_fields
                        WHERE module_register_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                        [AND][WHERE]
                        [HAVING]
                        ORDER BY name
                        [LIMIT]";
$oRecord->addContent($oField, "SEO");





    $oRecord->addContent(null, true, "field");
    $oRecord->groups["field"] = array(
        "title" => ffTemplate::_get_word_by_code("register_config_fields")
    );

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->id = "RegisterFields";
    $oGrid->source_SQL = "SELECT module_register_fields.*  
						FROM module_register_fields
						WHERE module_register_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
						[AND] [WHERE] 
						[HAVING] 
						[ORDER]";
    $oGrid->order_default = "ID";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->use_paging = false;
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/add";
    $oGrid->record_id = "RegisterExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_name");
    $oField->required = true;
    $oGrid->addContent($oField);

    $oRecord->addContent($oGrid, "field");
    $cm->oPage->addContent($oGrid);
}


$oRecord->addContent(null, true, "form");
$oRecord->groups["form"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_forms")
);

$oDetailForm = ffDetails::factory($cm->oPage);
$oDetailForm->id = "RegisterForm";
$oDetailForm->src_table = "module_register_rel_form";
$oDetailForm->order_default = "order";
$oDetailForm->fields_relationship = array("ID_module_register" => "ID");
$oDetailForm->display_new = true;
$oDetailForm->display_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetailForm->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_module";
$oField->label = ffTemplate::_get_word_by_code("register_config_forms_name");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . STAGE_ADMIN . "/addons/form/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigModify_confirmdelete";
$oField->resources[] = "FormConfigModify";
$oField->source_SQL = "SELECT ID, name FROM module_form ORDER BY name";
$oDetailForm->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "request";
$oField->label = ffTemplate::_get_word_by_code("register_config_forms_request");
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailForm->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "public";
$oField->label = ffTemplate::_get_word_by_code("register_config_forms_public");
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailForm->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("register_config_forms_order");
$oDetailForm->addContent($oField);

$oRecord->addContent($oDetailForm, "form");
$cm->oPage->addContent($oDetailForm);

$oRecord->addContent(null, true, "vgallery");
$oRecord->groups["vgallery"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_vgallery")
);

$oDetailVGallery = ffDetails::factory($cm->oPage);
$oDetailVGallery->id = "RegisterVGallery";
$oDetailVGallery->src_table = "module_register_rel_vgallery";
$oDetailVGallery->order_default = "order";
$oDetailVGallery->fields_relationship = array("ID_module_register" => "ID");
$oDetailVGallery->display_new = true;
$oDetailVGallery->display_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetailVGallery->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_vgallery_nodes";
$oField->label = ffTemplate::_get_word_by_code("register_config_vgallery_name");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path FROM vgallery_nodes WHERE vgallery_nodes.is_dir > 0 ORDER BY path";
$oDetailVGallery->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = ffTemplate::_get_word_by_code("register_config_vgallery_visible");
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailVGallery->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("register_config_vgallery_cascading");
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailVGallery->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "request";
$oField->label = ffTemplate::_get_word_by_code("register_config_vgallery_request");
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oDetailVGallery->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("register_config_vgallery_order");
$oDetailVGallery->addContent($oField);

$oRecord->addContent($oDetailVGallery, "vgallery");
$cm->oPage->addContent($oDetailVGallery);

function RegisterConfigModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    switch ($action) {
        case "insert":
        case "update":
            if (check_function("process_mail")) {
                if (!$component->form_fields["ID_email"]->getValue()) {
                    $res = email_system("registration " . $component->form_fields["name"]->getValue());
                    $component->form_fields["ID_email"]->setValue($res["ID"]);
                }
                if (!$component->form_fields["ID_email_activation"]->getValue()) {
                    $res = email_system("activation " . $component->form_fields["name"]->getValue());
                    $component->form_fields["ID_email_activation"]->setValue($res["ID"]);
                }
            }
            break;
        default:
    }
}
