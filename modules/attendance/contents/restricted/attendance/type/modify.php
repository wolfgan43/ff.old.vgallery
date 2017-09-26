<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_ATTENDANCE_GROUP_ATTENDANCE])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "TypeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("type_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_type";
$oRecord->addEvent("on_done_action", "TypeModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("type_modify_name");
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "approval";
$oField->label = ffTemplate::_get_word_by_code("type_modify_approval");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sheet";
$oField->label = ffTemplate::_get_word_by_code("type_modify_enable_sheet");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sheet_grid";
$oField->label = ffTemplate::_get_word_by_code("type_modify_enable_sheet_grid");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_tool";
$oField->label = ffTemplate::_get_word_by_code("type_modify_enable_tool");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "default";
$oField->label = ffTemplate::_get_word_by_code("type_modify_default");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField);  


$oField = ffField::factory($cm->oPage);
$oField->id = "ID_force_type";
$oField->label = ffTemplate::_get_word_by_code("type_modify_force_type");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_type.ID AS ID
							, " . CM_TABLE_PREFIX . "mod_attendance_type.name AS name
						FROM " . CM_TABLE_PREFIX . "mod_attendance_type
						WHERE NOT(" . CM_TABLE_PREFIX . "mod_attendance_type.approval > 0)
						ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "um";
$oField->label = ffTemplate::_get_word_by_code("type_modify_um");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
							array(new ffData("th"), new ffData(ffTemplate::_get_word_by_code("type_change_hour_diff"))),
                            array(new ffData("ch"), new ffData(ffTemplate::_get_word_by_code("type_change_hour"))),
                            array(new ffData("cd"), new ffData(ffTemplate::_get_word_by_code("type_change_day"))),
                            array(new ffData("se"), new ffData(ffTemplate::_get_word_by_code("type_switch_employee"))),
                            array(new ffData("st"), new ffData(ffTemplate::_get_word_by_code("type_switch_time")))
                       );
$oField->required = true;
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_request";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_request");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_employee";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_employee");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_customer";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_customer");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "mail_response_office";
$oField->label = ffTemplate::_get_word_by_code("type_modify_mail_response_office");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT email.name AS ID
							, email.name AS name
						FROM email
						WHERE 1
						ORDER BY email.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
$oRecord->addContent($oField); 

$cm->oPage->addContent($oRecord);   

function TypeModify_on_done_action($component, $action) {
    
   
}
?>
