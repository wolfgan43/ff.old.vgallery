<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(!isset($_REQUEST["keys"]["searchcnf-ID"])) {
    if(!strlen(basename($cm->real_path_info)) && isset($_REQUEST["name"]))
    $cm->real_path_info = "/" . $_REQUEST["name"];

    $db_gallery->query("SELECT module_search.*
                            FROM module_search
                            WHERE module_search.name = " . $db_gallery->toSql(new ffData( basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["searchcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } 
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SearchExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("search_modify");
$oRecord->src_table = "module_search";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_search.*
                                    , module_search.name AS display_name
                                FROM module_search 
                                WHERE module_search.ID =" . $db_gallery->toSql($_REQUEST["keys"]["searchcnf-ID"], "Number");

$oRecord->addEvent("on_do_action", "SearchExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "SearchExtraFieldModify_on_done_action");

$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "searchcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["keys"]["searchcnf-ID"])) 
{
    $module_search_title = ffTemplate::_get_word_by_code("modify_module_search");
    $sSQL = "SELECT module_search.name
                            FROM module_search
                            WHERE module_search.ID = " . $db_gallery->toSql($_REQUEST["keys"]["searchcnf-ID"], "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord())
    {
            $module_search_title .= ": " . $db_gallery->getField("name", "Text", true);
    }
    $oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . cm_getClassByFrameworkCss("vg-modules", "icon-tag", array("2x", "module", "search")) . $module_search_title . '</h1>';
	
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->dialog_action_button = true;
    $oGrid->id = "SearchConfigField";
    $oGrid->source_SQL = "SELECT module_search_fields.* 
							FROM module_search_fields
                            WHERE module_search_fields.ID_module = " . $db_gallery->toSql($_REQUEST["keys"]["searchcnf-ID"], "Number") . "
                                [AND] [WHERE] 
                            [HAVING] 
                            [ORDER]";
    $oGrid->order_default = "searchcnfield-ID";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->use_paging = false;
    $oGrid->record_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/modules/search/extra/modify";
    $oGrid->record_id = "SearchExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "search_fields"
                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
            )
            , "searchcnfield-ID"
        )
    );
    //$oGrid->addEvent("on_before_parse_row", "RegisterConfigField_on_before_parse_row");
    //$oGrid->addEvent("on_do_action", "RegisterExtraFieldModify_on_do_action");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "searchcnfield-ID";
    $oField->base_type = "Number";
    $oField->data_source = "ID";
    $oField->order_SQL = " `order`, name";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->container_class = "name";
    $oField->label = ffTemplate::_get_word_by_code("search_field_name");
    $oField->base_type = "Text";
    $oGrid->addContent($oField); 

    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);  
} else {  
    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("search_copy");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT module_search.ID
                                , module_search.name AS name
                            FROM module_search
                            WHERE 1
                            ORDER BY module_search.name";
    $oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);  
}    

$cm->oPage->addContent($oRecord);


function SearchExtraFieldModify_on_do_action($component, $action) {
    switch($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];
            if(isset($component->form_fields["copy-from"])) {
                if(check_function("MD_search_on_done_action")) {
                    $res = MD_search_clone($component->form_fields["copy-from"]->getValue(), $_REQUEST["clonename"]);
                    if($res["ID"] > 0) {
    //, "callback" => "ff.ffField.activecomboex.dialog_success('VGalleryNodesModifyDetail_recordset[0][46]', 'FormExtraFieldModify')"
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[searchcnf-ID]=" . $res["ID"] . "&noredirect&ret_url=" . urlencode($ret_url) , "close" => false, "refresh" => true, "insert_id" => $res["name"], "resources" => array("SearchExtraFieldModify")), true));
                        //ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "?keys[formcnf-ID]=" . $ID_form . "&noredirect&ret_url=" . urlencode($ret_url));                
                    }
                }
            }
        break;
        default:
    }
    
}


function SearchExtraFieldModify_on_done_action($component, $action) {
    switch($action) {
        case "update":
            if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("SearchExtraFieldModify")), true));
            } else {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
            }
            
            break;
        case "confirmdelete":
            if(check_function("MD_search_delete"))
                MD_search_delete($component->key_fields["searchcnf-ID"]->getValue());

            if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("SearchExtraFieldModify")), true));
            } else {
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
            }
            break;
        default:
    }
    return true;
}





