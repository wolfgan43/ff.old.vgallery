<?php

$permission = check_trivia_permission();
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
 
$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);  
$oRecord->id = "AchivementModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("trivia_achivement_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_trivia_achivement";
$oRecord->addEvent("on_done_action", "AchivementModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_modify_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "name";
$oField->properties["readonly"] = "readonly";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_modify_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/trivia/achivement/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/trivia/achivement";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . CM_SHOWFILES . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
$oField->required = false;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);       

function AchivementModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    }

    
}
?>