<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ReportTypeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("report_type_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_report_type";
$oRecord->addEvent("on_done_action", "ReportTypeModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("report_type_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_by_groups";
$oField->label = ffTemplate::_get_word_by_code("form_config_groups");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) 
                                                FROM " . CM_TABLE_PREFIX . "mod_security_groups 
                                                WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name IN('" . MOD_ATTENDANCE_GROUP_OFFICE . "', '" . MOD_ATTENDANCE_GROUP_EMPLOYEE . "')
                                                ORDER BY name";
$oField->control_type = "input";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);   

function ReportTypeModify_on_done_action($component, $action) 
{
    if(strlen($action))
    {
        $db = ffDB_Sql::factory();

        switch($action) {
            case "insert":
            case "update":
                break;
            case "confirmdelete":
                $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_report
                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_report.ID_type = " . $db->toSql($component->key_fields["ID"]->getValue(), "Number");
                $db->execute($sSQL);
                break;
            default:
        }
    }
}  
?>