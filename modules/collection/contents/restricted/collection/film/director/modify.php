<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "filmDirectorModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_film_director_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_film_director";
$oRecord->addEvent("on_done_action", "filmDirectorModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_director_avatar");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/collector/film/director/";
$oField->file_temp_path = DISK_UPDIR . "/collector/film/director/";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/70x70/[_FILENAME_]";
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->widget = "uploadify"; 
if(check_function("set_field_uploader")) { 
		$oField = set_field_uploader($oField);
}
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COVER");
//$oField->uploadify_model = "horizzontal";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_director_name");
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function filmDirectorModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        switch ($action) 
		{
				case "insert":
				case "update":
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_film_director SET
												" . CM_TABLE_PREFIX . "mod_collection_film_director.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
												WHERE " . CM_TABLE_PREFIX . "mod_collection_film_director.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
				break;
		}
    }
}