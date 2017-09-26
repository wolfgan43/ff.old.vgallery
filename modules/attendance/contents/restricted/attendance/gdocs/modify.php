<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ReportModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("report_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_report";
$oRecord->addEvent("on_done_action", "ReportModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("report_modify_title");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "link";
$oField->label = ffTemplate::_get_word_by_code("report_modify_link");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->container_class = "type";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("report_modify_type");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_attendance_report_type ORDER BY name";
$oField->required = true;
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->resources[] = "ReportTypeModify";
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/type/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ReportTypeModify_confirmdelete";
$oRecord->addContent($oField);

$oRecord->insert_additional_fields = array("owner" =>  new ffData($UserNID, "Number"));
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);   

function ReportModify_on_done_action($component, $action) {
    
   
}
?>