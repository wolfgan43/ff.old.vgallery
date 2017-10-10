<?php

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if (!isset($_REQUEST["keys"]["registercnf-ID"])) {
    $db_gallery->query("SELECT module_register.*
                            FROM 
                                module_register
                            WHERE 
                                module_register.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
    );
    if ($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["registercnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } else {
        if ($_REQUEST["keys"]["ID"] > 0) {
            $db_gallery->execute("DELETE
		                            FROM 
		                                modules
		                            WHERE 
		                                modules.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
            );
            if ($_REQUEST["XHR_DIALOG_ID"]) {
                die(ffCommon_jsonenc(array("resources" => array("modules"), "close" => true, "refresh" => true), true));
            } else {
                ffRedirect($_REQUEST["ret_url"]);
            }
        }
    }
}

if (isset($_REQUEST["keys"]["registercnf-ID"])) {
    $module_register_title = ffTemplate::_get_word_by_code("modify_module_register");
    $sSQL = "SELECT module_register.name
				FROM module_register
				WHERE module_register.ID = " . $db_gallery->toSql($_REQUEST["keys"]["registercnf-ID"], "Number");
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
        $module_register_title .= ": " . $db_gallery->getField("name", "Text", true);
    }
} else {
    $module_register_title = ffTemplate::_get_word_by_code("addnew_module_register");
}

$oRecord->id = "RegisterConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_register";
$oRecord->addEvent("on_do_action", "RegisterConfigModify_on_do_action");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "register")) . $module_register_title . '</h1>';

if (check_function("MD_general_on_done_action"))
    $oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "registercnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("register_config_name");
if (basename($module_vars["path_info"])) {
    $oField->default_value = new ffData(basename($module_vars["path_info"]));
    $oField->control_type = "label";
}
$oField->required = true;
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("register_config_name");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->required = true;
$oField->container_class = "hidden";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "default";
$oField->label = ffTemplate::_get_word_by_code("register_config_default");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField);

$oRecord->addTab("Template");
$oRecord->setTabTitle("Template", ffTemplate::_get_word_by_code("register_config_template"));

$oRecord->addContent(null, true, "Template");
$oRecord->groups["Template"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_template")
    , "cols" => 1
    , "tab" => "Template"
);



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
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT  ID, name FROM email";
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/utility/email/modify";
$oField->actex_dialog_edit_params = array("keys[email-ID]" => $oRecord->id . "_" . $oField->id);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailModify_confirmdelete";
$oField->resources[] = "EmailModify";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_email_activation";
$oField->label = ffTemplate::_get_word_by_code("register_config_email_activation");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT  ID, name FROM email";
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/utility/email/modify";
$oField->actex_dialog_edit_params = array("keys[email-ID]" => $oRecord->id . "_" . $oField->id);
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

$oRecord->addTab("Account");
$oRecord->setTabTitle("Account", ffTemplate::_get_word_by_code("register_config_account"));

$oRecord->addContent(null, true, "Account");
$oRecord->groups["Account"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_account")
    , "cols" => 1
    , "tab" => "Account"
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
    array(new ffData(0, "Number"), new ffData(ffTemplate::_get_word_by_code("activation_no"))),
    array(new ffData(1, "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_user"))),
    array(new ffData(2, "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_admin"))),
    array(new ffData(4, "Number"), new ffData(ffTemplate::_get_word_by_code("activation_by_user_admin")))
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

if (defined("VG_SITE_MANAGE") && strlen(VG_SITE_MANAGE)) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_anagraph_type";
    $oField->label = ffTemplate::_get_word_by_code("register_config_anagraph_type");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT ID, name FROM anagraph_type ORDER BY name";
    $oField->widget = "activecomboex";
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
$oDetailGroup->title = ffTemplate::_get_word_by_code("register_config_groups");
$oDetailGroup->src_table = "module_register_rel_gid";
$oDetailGroup->order_default = "ID";
$oDetailGroup->fields_relationship = array("ID_module_register" => "registercnf-ID");
$oDetailGroup->display_new = false;
$oDetailGroup->display_delete = FALSE;
$oDetailGroup->auto_populate_insert = true;
//$oDetail->put_fixed_post = true;
$oDetailGroup->populate_insert_SQL = "SELECT  
                                        0 AS value 
                                        , " . CM_TABLE_PREFIX . "mod_security_groups.gid AS gid
                                    FROM 
                                        " . CM_TABLE_PREFIX . "mod_security_groups
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name <> '" . MOD_SEC_GUEST_GROUP_NAME . "'
                                    ORDER BY 
                                        " . CM_TABLE_PREFIX . "mod_security_groups.name";
$oDetailGroup->auto_populate_edit = true;
$oDetailGroup->populate_edit_SQL = "SELECT  
                                        (SELECT module_register_rel_gid.ID FROM module_register_rel_gid WHERE module_register_rel_gid.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid AND module_register_rel_gid.ID_module_register = [registercnf-ID_FATHER] ) AS ID
                                        , [registercnf-ID_FATHER] AS ID_module_register 
                                        , (SELECT module_register_rel_gid.value FROM module_register_rel_gid WHERE module_register_rel_gid.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid AND module_register_rel_gid.ID_module_register = [registercnf-ID_FATHER] ) AS value 
                                        , " . CM_TABLE_PREFIX . "mod_security_groups.gid AS gid
                                    FROM 
                                        " . CM_TABLE_PREFIX . "mod_security_groups
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name <> '" . MOD_SEC_GUEST_GROUP_NAME . "' 
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

if ($_REQUEST["keys"]["registercnf-ID"]) {
    
$oRecord->addTab("SEO");
$oRecord->setTabTitle("SEO", ffTemplate::_get_word_by_code("register_config_SEO"));

$oRecord->addContent(null, true, "SEO");
$oRecord->groups["SEO"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_SEO")
    , "cols" => 1
    , "tab" => "SEO"
);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_smart_url");
$oField->extended_type = "Selection";
$oField->widget = "autocompletetoken";
$oField->required = true;
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = "";
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare = "name";
$oField->source_SQL = "SELECT module_register_fields.ID
                            , module_register_fields.name
                        FROM module_register_fields
                        WHERE module_register_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["registercnf-ID"], "Number") . "
                        [AND][WHERE]
                        [HAVING]
                        ORDER BY name
                        [LIMIT]";
$oRecord->addContent($oField, "SEO");

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_description";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_meta_description");
$oField->extended_type = "Selection";
$oField->widget = "autocompletetoken";
$oField->required = true;
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = "";
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare = "name";
$oField->source_SQL = "SELECT module_register_fields.ID
                            , module_register_fields.name
                        FROM module_register_fields
                        WHERE module_register_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["registercnf-ID"], "Number") . "
                        [AND][WHERE]
                        [HAVING]
                        ORDER BY name
                        [LIMIT]";
$oRecord->addContent($oField, "SEO");


$oRecord->addTab("field");
$oRecord->setTabTitle("field", ffTemplate::_get_word_by_code("register_config_fields"));



    $oRecord->addContent(null, true, "field");
    $oRecord->groups["field"] = array(
        "title" => ffTemplate::_get_word_by_code("register_config_fields")
        , "cols" => 1
        , "tab" => "field"
    );

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
//$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
    $oGrid->id = "RegisterFields";
    $oGrid->source_SQL = "SELECT module_register_fields.*  
						FROM module_register_fields
						WHERE module_register_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["registercnf-ID"], "Number") . "
						[AND] [WHERE] 
						[HAVING] 
						[ORDER]";
    $oGrid->order_default = "registercnfield-ID";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->use_paging = false;
    $oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/modules/register/extra/modify";
    $oGrid->record_id = "RegisterExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "registercnfield-ID";
    $oField->base_type = "Number";
    $oField->data_source = "ID";
    $oField->order_SQL = " `order`";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_name");
    $oField->required = true;
    $oGrid->addContent($oField);

    $oRecord->addContent($oGrid, "field");
    $cm->oPage->addContent($oGrid);
}

$oRecord->addTab("form");
$oRecord->setTabTitle("form", ffTemplate::_get_word_by_code("register_config_forms"));

$oRecord->addContent(null, true, "form");
$oRecord->groups["form"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_forms")
    , "cols" => 1
    , "tab" => "form"
);

$oDetailForm = ffDetails::factory($cm->oPage);
$oDetailForm->id = "RegisterForm";
$oDetailForm->title = ffTemplate::_get_word_by_code("register_config_forms");
$oDetailForm->src_table = "module_register_rel_form";
$oDetailForm->order_default = "order";
$oDetailForm->fields_relationship = array("ID_module_register" => "registercnf-ID");
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
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/content/modules/form/config/modify";
$oField->actex_dialog_edit_params = array("keys[formcnf-ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigModify_confirmdelete";
$oField->resources[] = "FormConfigModify";

//$oField->extended_type = "Selection";
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

$oRecord->addTab("vgallery");
$oRecord->setTabTitle("vgallery", ffTemplate::_get_word_by_code("register_config_vgallery"));

$oRecord->addContent(null, true, "vgallery");
$oRecord->groups["vgallery"] = array(
    "title" => ffTemplate::_get_word_by_code("register_config_vgallery")
    , "cols" => 1
    , "tab" => "vgallery"
);

$oDetailVGallery = ffDetails::factory($cm->oPage);
$oDetailVGallery->id = "RegisterVGallery";
$oDetailVGallery->title = ffTemplate::_get_word_by_code("register_config_vgallery");
$oDetailVGallery->src_table = "module_register_rel_vgallery";
$oDetailVGallery->order_default = "order";
$oDetailVGallery->fields_relationship = array("ID_module_register" => "registercnf-ID");
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
$oField->widget = "activecomboex";
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
