<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "ExtrasImage";
$oGrid->resources[] = "ExtrasImage";
$oGrid->title = ffTemplate::_get_word_by_code("extras_image_title");
$oGrid->source_SQL = "SELECT 
                            " . CM_TABLE_PREFIX . "showfiles_modes.*
                        FROM
                            " . CM_TABLE_PREFIX . "showfiles_modes
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "ExtrasImageModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("extras_image_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);


?>
