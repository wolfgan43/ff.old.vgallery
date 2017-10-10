<?php

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$db = ffDB_Sql::factory();

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$display_addnew = false;
if (!isset($_REQUEST["keys"]["registercnfield-ID"])) {
    if (isset($_REQUEST["field"])) {
        $copy_field = $_REQUEST["field"];
    } else {
        $display_addnew = true;
    }
}



$system_field = array(
    "email" => "important"
    , "password" => "important"
    , "username" => "important"
    , "avatar" => "default"
    , "privacy" => "default"
    , "privacy-html" => "default"
    , "newsletter" => "default"
    , "newsletter_DEM" => "default"
    , "tel" => "default"
    , "name" => "default"
    , "surname" => "default"
    , "degree" => "default"
    , "reference" => "bill"
    , "cf" => "bill"
    , "piva" => "bill"
    , "address" => "bill"
    , "cap" => "bill"
    , "town" => "bill"
    , "province" => "bill"
    , "state" => "bill"
    , "shippingreference" => "shipping"
    , "shippingaddress" => "shipping"
    , "shippingcap" => "shipping"
    , "shippingtown" => "shipping"
    , "shippingprovince" => "shipping"
    , "shippingstate" => "shipping"
);
$sSQL = "SELECT ID, name FROM check_control WHERE 1";
$db_gallery->query($sSQL);
if ($db_gallery->nextRecord()) {
    do {
        $arrControlType[$db_gallery->getField("name", "Text", true)] = $db_gallery->getField("ID", "Number", true);
        $arrControlTypeRev[$db_gallery->getField("ID", "Number", true)] = $db_gallery->getField("name", "Text", true);
    } while ($db_gallery->nextRecord());
}

$sSQL = "SELECT ID, name FROM extended_type WHERE 1";
$db_gallery->query($sSQL);
if ($db_gallery->nextRecord()) {
    do {
        $arrExtType[$db_gallery->getField("name", "Text", true)] = $db_gallery->getField("ID", "Number", true);
        $arrExtTypeRev[$db_gallery->getField("ID", "Number", true)] = $db_gallery->getField("name", "Text", true);
    } while ($db_gallery->nextRecord());
}


if (isset($_REQUEST["keys"]["registercnf-ID"])) {
    $module_register_title = ffTemplate::_get_word_by_code("modify_module_register");
    $sSQL = "SELECT module_register.name
                FROM module_register
                WHERE module_register.ID = " . $db_gallery->toSql($_REQUEST["keys"]["registercnf-ID"], "Number");
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord())
        $module_register_title .= ": " . $db_gallery->getField("name", "Text", true);
} else {
    $module_register_title = ffTemplate::_get_word_by_code("addnew_module_register");
}

if(strlen($copy_field) && !array_key_exists($copy_field, $system_field)) {
    $sSQL = "SELECT anagraph_fields.* 
                    FROM anagraph_fields
                    WHERE anagraph_fields.ID = " . $db_gallery->toSql($copy_field, "Number");
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
        $field_name = $db_gallery->getField("name", "Text", true);
        $actual_control_type = $arrControlTypeRev[$db_gallery->getField("ID_check_control", "Number", true)];
        $actual_ext_type = $arrExtTypeRev[$db_gallery->getField("ID_extended_type", "Number", true)];
        $actual_disable_free_input = $db_gallery->getField("disable_free_input", "Number", true);
    }
    $oRecord->insert_additional_fields["ID_anagraph_fields"] = new ffData($copy_field, "Number");
}elseif(isset($_REQUEST["keys"]["registercnfield-ID"]) && $_REQUEST["keys"]["registercnfield-ID"] > 0) {
    
    $sSQL = "SELECT module_register_fields.ID
                    , module_register_fields.name
                    , module_register_fields.ID_extended_type
                FROM module_register_fields
                WHERE module_register_fields.ID = " . $db_gallery->toSql($_REQUEST["keys"]["registercnfield-ID"], "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) { 
        $field_name = $db_gallery->getField("name", "Text", true);
        $actual_ext_type = $arrExtTypeRev[$db_gallery->getField("ID_extended_type", "Number", true)];
        
    }
} elseif(strlen($copy_field) && array_key_exists($copy_field, $system_field)) {
    $field_name = $copy_field;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "RegisterExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_register_fields";
$oRecord->insert_additional_fields["ID_module"] = new ffData($_REQUEST["keys"]["registercnf-ID"], "Number");
$oRecord->addEvent("on_do_action", "RegisterExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "RegisterExtraFieldModify_on_done_action");
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "register")) . $module_register_title . '</h1>';
$oRecord->user_vars["system_field"] = $system_field;

$oField = ffField::factory($cm->oPage);
$oField->id = "registercnfield-ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if ($display_addnew) {
    if (is_array($system_field) && count($system_field)) {
        foreach ($system_field AS $name => $group) {
            $sSQL_string .=
                    " ( 
					SELECT " . $db_gallery->toSql($name, "text", true) . "  AS ID
					, " . $db_gallery->toSql($name, "text", true) . " AS name
					, " . $db_gallery->toSql($group, "text", true) . " AS grp_name
				) UNION ";
        }
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("form_fields_copy");
    $oField->source_SQL = $sSQL_string .
            "(SELECT anagraph_fields.ID
                        , anagraph_fields.name
                        , 'Campi anagrafica' AS grp_name
                FROM anagraph_fields 
                ORDER BY anagraph_fields.name)";
    $oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->actex_group = "grp_name";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("register_fields_addnew");
    $oField->store_in_db = false;
    $oRecord->addContent($oField);
} else 
{
    $field_default["ID_extended_type"] = $arrExtType["String"];
    $field_default["ID_fields_selection"] = 0;
    
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_name");
    $oField->required = true;
    $oField->default_value = new ffData($field_name);
    if(strlen($field_name)) { 
        $oField->properties["disabled"] = "disabled";
    }
    $oRecord->addContent($oField);
    

    $oField = ffField::factory($cm->oPage);
    $oField->id = "require";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_require");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oRecord->addContent($oField);
    
    if (!array_key_exists($field_name, $system_field)) { 
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_extended_type";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_extended_type");
        $oField->extended_type = "Selection";
        if (check_function("set_field_extended_type"))
            $oField = set_field_extended_type($oField);
        $oField->required = true;
        $oField->default_value = new ffData($field_default["ID_extended_type"], "Number");
        if ($_REQUEST["XHR_DIALOG_ID"])
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ffPage.dialog.doRequest('" . $_REQUEST["XHR_DIALOG_ID"] . "', {'action' : 'refresh'}); 
	            }
	        }";
        else
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ajax.doRequest({'action' : 'refresh'}); 
            	}
            }";
        $oRecord->addContent($oField);

        if (isset($_REQUEST[$oRecord->id . "_ID_extended_type"])) {
            $actual_ext_type = (array_key_exists($_REQUEST[$oRecord->id . "_ID_extended_type"], $arrExtTypeRev) ? $arrExtTypeRev[$_REQUEST[$oRecord->id . "_ID_extended_type"]] : ""
                    );
        }

        if ($actual_ext_type == "Selection" || $actual_ext_type == "Group" || $actual_ext_type == "Option" || $actual_ext_type == "Autocompletetoken") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_selection";
            $oField->label = ffTemplate::_get_word_by_code("register_config_fields_selection");
            $oField->base_type = "Number";
            $oField->source_SQL = "SELECT ID, name FROM anagraph_fields_selection ORDER BY name";
            $oField->widget = "activecomboex";
            $oField->actex_update_from_db = true;
            $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/ecommerce/anagraph/type/selection/modify";
            $oField->actex_dialog_edit_params = array("keys[ID]" => null);
            $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=AnagraphSelectionModify_confirmdelete";
            $oField->resources[] = "AnagraphSelectionModify";
            $oRecord->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "disable_select_one";
            $oField->label = ffTemplate::_get_word_by_code("register_config_fields_disable_select_one");
            $oField->base_type = "Number";
            $oField->extended_type = "Boolean";
            $oField->control_type = "checkbox";
            $oField->unchecked_value = new ffData("0", "Number");
            $oField->checked_value = new ffData("1", "Number");
            $oRecord->addContent($oField);
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_check_control";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_check_control");
        $oField->extended_type = "Selection";
        $oField->source_SQL = "SELECT ID, name FROM check_control ORDER BY name";
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "unic_value";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_unic_value");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "enable_in_mail";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_enable_in_mail");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "enable_in_grid";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_enable_in_grid");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "enable_in_menu";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_enable_in_menu");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "enable_in_document";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_enable_in_document");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "enable_tip";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_enable_tip");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "writable";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_writable");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "hide";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_hide");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "hide_register";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_hide_register");
        $oField->base_type = "Number";
        $oField->control_type = "checkbox";
        $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
        $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
        $oRecord->addContent($oField);
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_form_fields_group";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_group");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT ID
									, name 
								FROM module_form_fields_group ORDER BY name";
    $oField->widget = "activecomboex";
    $oField->description = "account, accountinfo";
    $oField->actex_update_from_db = true;
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/modules/form/extra/group/modify";
    $oField->actex_dialog_edit_params = array("keys[formgrp-ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigGroupModify_confirmdelete";
    $oField->resources[] = "FormConfigGroupModify";
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "custom_placeholder";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_custom_placeholder");
    $oRecord->addContent($oField);

    $sSQL = "SELECT cm_layout.* 
		        FROM cm_layout 
		        WHERE cm_layout.path = " . $db_gallery->toSql("/");
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
        $framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
        $template_framework = $framework_css["name"];
    }

    if (strlen($template_framework)) {
        if ($template_framework == "bootstrap")
            $columns = array(3, 3, 3, 3);
        elseif ($template_framework == "foundation")
            $columns = array(4, 4, 4);
        else
            $columns = null;

        $oField = ffField::factory($cm->oPage);
        $oField->id = "default_grid";
        $oField->label = ffTemplate::_get_word_by_code("field_default_grid");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "grid_md";
        $oField->label = ffTemplate::_get_word_by_code("field_grid_md");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "grid_sm";
        $oField->label = ffTemplate::_get_word_by_code("field_grid_sm");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        if ($template_framework == "bootstrap") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "grid_xs";
            $oField->label = ffTemplate::_get_word_by_code("field_grid_xs");
            $oField->base_type = "Number";
            $oField->widget = "slider";
            $oField->min_val = "0";
            $oField->max_val = "12";
            $oField->step = "1";
            $oField->setWidthComponent($columns);
            $oField->default_value = new ffData(12, "Number");
            $oRecord->addContent($oField);
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_default_grid";
        $oField->label = ffTemplate::_get_word_by_code("label_group_default_grid");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_grid_md";
        $oField->label = ffTemplate::_get_word_by_code("label_group_grid_md");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_grid_sm";
        $oField->label = ffTemplate::_get_word_by_code("label_group_grid_sm");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        if ($template_framework == "bootstrap") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "label_grid_xs";
            $oField->label = ffTemplate::_get_word_by_code("label_group_grid_xs");
            $oField->base_type = "Number";
            $oField->widget = "slider";
            $oField->min_val = "0";
            $oField->max_val = "12";
            $oField->step = "1";
            $oField->setWidthComponent($columns);
            $oField->default_value = new ffData(3, "Number");
            $oRecord->addContent($oField);
        }
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "hide_label";
    $oField->label = ffTemplate::_get_word_by_code("register_config_hide_label");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField);
}
        
$cm->oPage->addContent($oRecord);

function RegisterExtraFieldModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();

    switch ($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];
            if (isset($component->form_fields["copy-from"])) {
                ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?field=" . $component->form_fields["copy-from"]->getValue() . "&keys[registercnf-ID]=" . $_REQUEST["keys"]["registercnf-ID"] . "&ret_url=" . urlencode($ret_url));
            }
            break;
        default:
            break;
    }
    return false;
}

function RegisterExtraFieldModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    if (strlen($action)) {
        switch ($action) {
            case "insert":
                if (isset($component->form_fields["name"]) && !array_key_exists($component->form_fields["name"]->getValue(), $component->user_vars["system_field"])) {
                    $sSQL = "SELECT anagraph_fields.*
                                FROM anagraph_fields
                                WHERE anagraph_fields.name = " . $db->toSql($component->form_fields["name"]->getValue(), "Text");
                                $db->query($sSQL);
                                if(!$db->nextRecord())
                                {
                                    if(isset($component->form_fields["ID_selection"])) {
                                            $ID_selection = $component->form_fields["ID_selection"]->getValue();
                                    } else {
                                            $ID_selection = 0;
                                    }
                                    $sSQL = "INSERT INTO anagraph_fields
                                            (
                                                ID
                                                , ID_type
                                                , name
                                                , ID_extended_type
                                                , ID_selection
                                                , ID_group_backoffice
                                                , require
                                                , ID_check_control
                                                , unic_value
                                                , send_mail
                                                , enable_in_mail
                                                , enable_in_grid
                                                , enable_in_menu
                                                , enable_in_document
                                                , writable
                                                , order
                                                , hide
                                                , fixed_pre_content
                                                , fixed_post_content
                                            ) VALUES
                                            (
                                                null
                                                , " . $db->toSql($component->insert_additional_fields["ID_module"]->value) . "
                                                , " . $db->toSql($component->form_fields["name"]->value) . "
                                                , " . $db->toSql($component->form_fields["ID_extended_type"]->value) . "
                                                , " . $db->toSql($ID_selection, "Number") . "
                                                , " . $db->toSql($component->form_fields["ID_form_fields_group"]->value) . "
                                                , " . $db->toSql($component->form_fields["require"]->value) . "
                                                , " . $db->toSql($component->form_fields["ID_check_control"]->value) . "
                                                , " . $db->toSql($component->form_fields["unic_value"]->value) . "
                                                , 0
                                                , " . $db->toSql($component->form_fields["enable_in_mail"]->value) . "
                                                , " . $db->toSql($component->form_fields["enable_in_grid"]->value) . "
                                                , " . $db->toSql($component->form_fields["enable_in_menu"]->value) . "
                                                , " . $db->toSql($component->form_fields["enable_in_document"]->value) . "
                                                , " . $db->toSql($component->form_fields["writable"]->value) . "
                                                , " . $db->toSql($component->form_fields["order"]->value) . "
                                                , " . $db->toSql($component->form_fields["hide"]->value) . "
                                                , 0
                                                , 0
                                            )";
                        $db->execute($sSQL);
                        $ID_anagraph_field = $db->getInsertID(true);
                        
                        $sSQL = "UPDATE module_register_fields SET
                                        ID_anagraph_fields = " . $db->toSql($ID_anagraph_field, "Number") . "
                                    WHERE ID = " . $db->toSql($component->key_fields["ID"]->value);
                        $db->execute($sSQL);
                            
                        
                    }
                }
                break;
            case "confirmdelete":
            default:
                break;
        }
    }
}