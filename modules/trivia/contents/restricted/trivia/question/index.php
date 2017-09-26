<?php
$permission = check_trivia_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Question";
$oGrid->title = ffTemplate::_get_word_by_code("trivia_question_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_trivia_question.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_trivia_question
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "QuestionModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_category");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_category.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_category";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_level";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_level");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_level.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_level";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>
