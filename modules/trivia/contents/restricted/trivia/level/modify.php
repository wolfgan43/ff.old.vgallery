<?php
$permission = check_trivia_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);  
$oRecord->id = "LevelModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("trivia_level_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_trivia_level";
$oRecord->addEvent("on_done_action", "LevelModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_categories_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("trivia_categories_modify_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "name";
$oField->properties["readonly"] = "readonly";
$oRecord->addContent($oField);
$cm->oPage->addContent($oRecord);       

function LevelModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    }

    
}
?>