<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_LANGUAGES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "languagesPanel";
$oGrid->title = ffTemplate::_get_word_by_code("charset_title");
$oGrid->source_SQL = "SELECT * FROM " . FF_PREFIX . "languages [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LanguagesModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->label = ffTemplate::_get_word_by_code("languages_code");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("languages_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("not_active"))),
                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active")))
                       );
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tiny_code";
$oField->label = ffTemplate::_get_word_by_code("languages_tiny_code");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>