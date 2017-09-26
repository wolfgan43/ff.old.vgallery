<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "EventModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("photo_event_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_photo_event";
$oRecord->addEvent("on_done_action", "PhotoEventModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("photo_event_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  


$cm->oPage->addContent($oRecord);   

function PhotoEventModify_on_done_action($component, $action) {
    
   
}
?>