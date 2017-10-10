<?php
if (!AREA_UPDATER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "exclude";
$oGrid->title = ffTemplate::_get_word_by_code("exclude_title");
$oGrid->source_SQL = "SELECT * FROM updater_exclude [WHERE] [ORDER]";
$oGrid->order_default = "path";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "ExcludeModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("exclude_path");
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("exclude_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))),
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("disactive")))
                       );
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>