<?php
$permission = check_trivia_permission(); 
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oGrid = ffGrid::factory($cm->oPage);  
$oGrid->full_ajax = true;
$oGrid->id = "Level";
$oGrid->title = ffTemplate::_get_word_by_code("trivia_level_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_trivia_level.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_trivia_level
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LevelModify";
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
                "resource_id" =>  "mod_trivia_level"
                , "service_path" => $cm->oPage->site_path . VG_SITE_SERVICES . "/sort"
            )
            , "ID"
        )
    );

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_level_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>