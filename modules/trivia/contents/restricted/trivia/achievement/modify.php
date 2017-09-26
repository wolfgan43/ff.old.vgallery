<?php

$permission = check_trivia_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
 
$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);  
$oRecord->id = "AchievementModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("trivia_achievement_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_trivia_achievement";
$oRecord->addEvent("on_do_action", "AchievementModify_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_modify_smart_url");
$oField->properties["readonly"] = "readonly";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->container_class = "ID-category";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_modify_category");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_category.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_category
							WHERE 1";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_level";
$oField->container_class = "ID-level";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_modify_level");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_level.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_level
							WHERE 1";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_modify_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/trivia/achievement/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/trivia/achievement";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . CM_SHOWFILES . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oField->required = false;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);       

function AchievementModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();

	switch($action) {
		case "insert":
		case "update":
			$smart_url = "";
			if(!$component->form_fields["ID_category"]->getValue() && !$component->form_fields["ID_level"]->getValue()) {
				$smart_url = ffCommon_url_rewrite($component->form_fields["smart_url"]->getValue());
			} else if($component->form_fields["ID_category"]->getValue() && !$component->form_fields["ID_level"]->getValue()) {
				$smart_url = ffCommon_url_rewrite($component->form_fields["ID_category"]->getDisplayValue() . " " . "completed");
			} else {
				$smart_url = ffCommon_url_rewrite($component->form_fields["ID_category"]->getDisplayValue() . " " . $component->form_fields["ID_level"]->getDisplayValue() . " " . $component->form_fields["name"]->getValue());
			}
			$component->form_fields["smart_url"]->setValue($smart_url);
		
			break;
		default:
	}
    
    if(strlen($action)) {
    	
    	
    	
    }

    
}
?>