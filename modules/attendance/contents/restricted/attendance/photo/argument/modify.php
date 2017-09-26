<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PhotoArgumentModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("photo_argument_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_photo_argument";
$oRecord->addEvent("on_done_action", "PhotoArgumentModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("photo_argument_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  


$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "PhotoArgumentDetailModify";
$oDetail->title = ffTemplate::_get_word_by_code("photo_argument_detail_modify_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_photo_argument_detail";
$oDetail->order_default = "name";
$oDetail->fields_relationship = array ("ID_argument" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("photo_argument_detail_modify_name");
$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);   




function PhotoArgumentModify_on_done_action($component, $action) {
    
   
}
?>