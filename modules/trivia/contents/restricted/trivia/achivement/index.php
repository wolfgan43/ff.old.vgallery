<?php

$permission = check_trivia_permission(); 
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID"); 

$oGrid = ffGrid::factory($cm->oPage);  
$oGrid->full_ajax = true;
$oGrid->id = "Achivement";
$oGrid->title = ffTemplate::_get_word_by_code("trivia_achivement_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_trivia_achivement.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_trivia_achivement
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "AchivementModify";
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
                "resource_id" =>  CM_TABLE_PREFIX . "mod_trivia_achivement"
                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
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
$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->display_label = false;
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/trivia/achivement/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/trivia/achivement";
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
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->base_type = "Number";
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("trivia_achivement_status");
$oField->base_type = "Number";
$oField->base_type = "Selection";
$oField->multi_pairs = array (
	                        array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes")))
		               );
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>