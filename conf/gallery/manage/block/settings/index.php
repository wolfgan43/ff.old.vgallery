<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "layoutSettings";
$oGrid->title = ffTemplate::_get_word_by_code("layout_settings_title");
$oGrid->source_SQL = "SELECT * FROM layout_type [WHERE] [ORDER]";
$oGrid->order_default = "description";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LayoutSettingsModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("layout_settings_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
        
?>