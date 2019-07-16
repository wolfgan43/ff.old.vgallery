<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_CHARSET_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "charset";
$oGrid->title = ffTemplate::_get_word_by_code("charset_title");
$oGrid->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "charset_decode [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "CharsetModify";
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
$oField->label = ffTemplate::_get_word_by_code("charset_code");
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("charset_value");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>