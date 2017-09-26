<?php

$permission = check_trivia_permission(); 
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oGrid = ffGrid::factory($cm->oPage);  
$oGrid->full_ajax = true;
$oGrid->id = "Achievement";
$oGrid->title = ffTemplate::_get_word_by_code("trivia_achievement_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_trivia_achievement.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_trivia_achievement
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "AchievementModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" =>  "mod_trivia_achievement"
                , "service_path" => $cm->oPage->site_path . VG_SITE_SERVICES . "/sort"
            )
            , "ID"
        )
    );

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->order_SQL = "`order`, name"; 
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->display_label = false;
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/trivia/achievement/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/trivia/achievement";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->control_type = "picture_no_link";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_category");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_category.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_category";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_level";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_level");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_level.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_level";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("trivia_achievement_status");
$oField->extended_type = "Selection";
$oField->multi_select_one  = false;
$oField->multi_pairs = array (
	                        array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes")))
		               );
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);

?>