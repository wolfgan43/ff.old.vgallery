<?php
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "TagsGroup";
$oGrid->source_SQL = "SELECT search_tags_group.*
                        FROM search_tags_group 
                        [WHERE]
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = false; 
$oGrid->use_paging = false;
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->addEvent("on_before_parse_row", "TagsGroup_on_before_parse_row");

//$oGrid->addit_record_param = "extype=general";

$oGrid->record_id = "TagsGroupModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
            "resource_id" => "search_tags_group"
            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
        )
        , "ID"
    )
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "`order`, ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("tags_page_title");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "status";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("status");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);  

function TagsGroup_on_before_parse_row($component) {
    
    if(isset($component->grid_buttons["status"])) {
        if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit"; 
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=0";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["status"]->action_type = "gotourl";
                //$component->grid_buttons["status"]->url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=0&frmAction=setstatus";
            }   
        } else {
            $component->grid_buttons["status"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["status"]->icon = null;
            $component->grid_buttons["status"]->action_type = "submit";     
            $component->grid_buttons["status"]->form_action_url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=1";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["status"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatus', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["status"]->action_type = "gotourl";
                //$component->grid_buttons["status"]->url = $component->grid_buttons["status"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setstatus=1&frmAction=setstatus";
            }    
        }
    }
    
    
}