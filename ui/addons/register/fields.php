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

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_register");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "RegisterExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("register_modify");
$oRecord->src_table = "module_register";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_register.*
                                    , module_register.name AS display_name
                                FROM module_register 
                                WHERE module_register.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");

$oRecord->addEvent("on_do_action", "RegisterExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "RegisterExtraFieldModify_on_done_action");

$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false; 


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["keys"]["ID"])) {
	$module_form_title = ffTemplate::_get_word_by_code("module_register_fields_title");

	/* Title Block */
	system_ffcomponent_set_title(
		$module_form_title
		, true
		, false
		, false
		, $oRecord
	);     
    
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->dialog_action_button = true;
    $oGrid->id = "RegisterConfigField";
    $oGrid->source_SQL = "SELECT module_register_fields.* 
                                , module_form_fields_group.`order` AS group_order
                                , IFNULL( module_form_fields_group.`name`
                                    , ''
                                ) AS group_name
                            FROM module_register_fields
                                LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_register_fields.ID_form_fields_group
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
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "register_fields"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );
    //$oGrid->addEvent("on_before_parse_row", "RegisterConfigField_on_before_parse_row");
    //$oGrid->addEvent("on_do_action", "RegisterExtraFieldModify_on_do_action");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oField->order_SQL = " IF(group_order, group_order, 999), `order`, name";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->container_class = "name";
    $oField->label = ffTemplate::_get_word_by_code("register_field_name");
    $oField->base_type = "Text";
    $oGrid->addContent($oField); 
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "group_name";
    $oField->container_class = "group-name";
    $oField->label = ffTemplate::_get_word_by_code("register_field_group_name");
    $oField->base_type = "Text";
    $oGrid->addContent($oField);     

    $cm->oPage->addContent($oGrid);  
} else {  
    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("register_copy");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT module_register.ID
                                , module_register.name AS name
                            FROM module_register
                            WHERE 1
                            ORDER BY module_register.name";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);  
}    

$cm->oPage->addContent($oRecord);


function RegisterExtraFieldModify_on_do_action($component, $action) {
    switch($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];
            if(isset($component->form_fields["copy-from"])) {
                if(check_function("MD_register_on_done_action")) {
                    $res = MD_register_clone($component->form_fields["copy-from"]->getValue(), $_REQUEST["clonename"]);
                    if($res["ID"] > 0) {
    //, "callback" => "ff.ffField.activecomboex.dialog_success('VGalleryNodesModifyDetail_recordset[0][46]', 'FormExtraFieldModify')"
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[ID]=" . $res["ID"] . "&noredirect&ret_url=" . urlencode($ret_url) , "close" => false, "refresh" => true, "insert_id" => $res["name"], "resources" => array("RegisterExtraFieldModify")), true));
                        //ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[formcnf-ID]=" . $ID_form . "&noredirect&ret_url=" . urlencode($ret_url));                
                    }
                }
            }
        break;
        default:
    }
    
}


function RegisterExtraFieldModify_on_done_action($component, $action) {
    switch($action) {
        case "update":
            if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("RegisterExtraFieldModify")), true));
            } else {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
            }
            
            break;
        case "confirmdelete":
            if(check_function("MD_register_delete"))
                MD_register_delete($component->key_fields["ID"]->getValue());

            if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("RegisterExtraFieldModify")), true));
            } else {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
            }
            break;
        default:
    }
    return true;
}





