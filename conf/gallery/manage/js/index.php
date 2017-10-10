<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_JS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "js";
$oGrid->title = ffTemplate::_get_word_by_code("js_title");
$oGrid->source_SQL = "SELECT * FROM js [WHERE] [ORDER]";
$oGrid->order_default = "order";
$oGrid->use_search = false;
$oGrid->use_paging = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "JsModify" . "plugin";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
            "resource_id" => "javascript"
            , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
        )
        , "ID"
    )
);
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("js_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("js_order");
$oField->display = false;
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
        
?>