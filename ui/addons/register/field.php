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

$db = ffDB_Sql::factory();

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$system_field = array(
    "email" => "important"
    , "password" => "important"
    , "username" => "important"
    , "avatar" => "default"
    , "privacy" => "default"
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
$db->query($sSQL);
if ($db->nextRecord()) {
    do {
        $arrControlType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
        $arrControlTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
    } while ($db->nextRecord());
}

$sSQL = "SELECT ID, name FROM extended_type WHERE 1";
$db->query($sSQL);
if ($db->nextRecord()) {
    do {
        $arrExtType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
        $arrExtTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
    } while ($db->nextRecord());
}



check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record(array(
	"table" => "module_register_fields"
	, "key" => "ID"
	, "primary" => array(
		"table" => "module_register"
		, "key" => "ID"
		, "fields" => array(
			"ID_module" => "ID"
		)
	)
	, "if_request" => array(
		"field" => array(
			"table" => "module_register_fields"
			, "key" => "ID"
			, "fields" => array(
				"copy_field" => "ID"
				, "name" => "IF(display_name = ''
										, REPLACE(name, '-', ' ')
										, display_name
									)"
			)
		)
	)
), array(
	"name" => "IF(module_register_fields.display_name = ''
					, REPLACE(module_register_fields.name, '-', ' ')
					, module_register_fields.display_name
				)"
	, "ID_module" => null
	, "ID_extended_type" => null
));


if(strlen($record["copy_field"]) && !array_key_exists($record["copy_field"], $system_field)) {
    $sSQL = "SELECT anagraph_fields.* 
                    FROM anagraph_fields
                    WHERE anagraph_fields.ID = " . $db->toSql($record["copy_field"], "Number");
    $db->query($sSQL);
    if ($db->nextRecord()) {
        $record["name"] = $db->getField("name", "Text", true);
        $record["ID_extended_type"] = $arrExtTypeRev[$db->getField("ID_extended_type", "Number", true)];
    }
    $oRecord->insert_additional_fields["ID_anagraph_fields"] = new ffData($record["copy_field"], "Number");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "RegisterExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_register_fields";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_register_fields.*
									, IF(module_register_fields.display_name = ''
										, REPLACE(module_register_fields.name, '-', ' ')
										, module_register_fields.display_name
									) AS display_name
								FROM module_register_fields 
								WHERE module_register_fields.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->insert_additional_fields["ID_module"] = new ffData($record["ID_module"], "Number");
$oRecord->addEvent("on_do_action", "RegisterExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "RegisterExtraFieldModify_on_done_action");
$oRecord->user_vars["system_field"] = $system_field;

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

if ($record["noentry"]) {
    if (is_array($system_field) && count($system_field)) {
        foreach ($system_field AS $name => $group) {
            $sSQL_string .=
                    " ( 
					SELECT " . $db->toSql($name, "text", true) . "  AS ID
					, " . $db->toSql($name, "text", true) . " AS name
					, " . $db->toSql($group, "text", true) . " AS grp_name
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
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
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
	$oField->widget = "slug";
	$oField->slug_title_field = "display_name";
	$oField->container_class = "hidden";
	$oRecord->addContent($oField);	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("register_config_fields_name");
	$oField->required = true;
	$oRecord->addContent($oField);    
    /*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_name");
    $oField->required = true;
    $oField->default_value = new ffData($field_name);
    if(strlen($field_name)) { 
        $oField->properties["disabled"] = "disabled";
    }
    $oRecord->addContent($oField);*/
    

    $oField = ffField::factory($cm->oPage);
    $oField->id = "require";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_require");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oRecord->addContent($oField);
    
    if (!array_key_exists($record["name"], $system_field)) { 
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_extended_type";
        $oField->label = ffTemplate::_get_word_by_code("register_config_fields_extended_type");
        $oField->extended_type = "Selection";
        if (check_function("set_field_extended_type"))
            $oField = set_field_extended_type($oField);
        $oField->required = true;
        $oField->default_value = new ffData($field_default["ID_extended_type"], "Number");
        if ($_REQUEST["XHR_CTX_ID"])
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); 
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
            $record["ID_extended_type"] = (array_key_exists($_REQUEST[$oRecord->id . "_ID_extended_type"], $arrExtTypeRev) ? $arrExtTypeRev[$_REQUEST[$oRecord->id . "_ID_extended_type"]] : ""
                    );
        }

        if ($record["ID_extended_type"] == "Selection" || $record["ID_extended_type"] == "Group" || $record["ID_extended_type"] == "Option" || $record["ID_extended_type"] == "Autocompletetoken") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_selection";
            $oField->label = ffTemplate::_get_word_by_code("register_config_fields_selection");
            $oField->base_type = "Number";
            $oField->source_SQL = "SELECT ID, name FROM anagraph_fields_selection ORDER BY name";
            $oField->widget = "actex";
			//$oField->widget = "activecomboex";
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
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->description = "account, accountinfo";
    $oField->actex_update_from_db = true;
    $oField->actex_dialog_url = get_path_by_rule("addons") . "/form/group/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=FormConfigGroupModify_confirmdelete";
    $oField->resources[] = "FormConfigGroupModify";
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "custom_placeholder";
    $oField->label = ffTemplate::_get_word_by_code("register_config_fields_custom_placeholder");
    $oRecord->addContent($oField);

	$framework_css = cm_getFrameworkCss();	
    $framework_css_name = $framework_css["name"];

    if (strlen($framework_css_name)) {
        if ($framework_css_name == "bootstrap")
            $columns = array(3, 3, 3, 3);
        elseif ($framework_css_name == "foundation")
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

        if ($framework_css_name == "bootstrap") {
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

        if ($framework_css_name == "bootstrap") {
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
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    switch ($action) {
        case "insert":
            if (isset($component->form_fields["copy-from"])) {
				ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . $cm->real_path_info . "?field=" . $component->form_fields["copy-from"]->getValue());
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