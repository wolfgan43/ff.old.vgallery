<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "SearchConfigGroup";
$oGrid->source_SQL = "SELECT * FROM module_search_fields_group [WHERE] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url =  $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "SearchConfigGroupModify";
$oGrid->resources[] = $oGrid->record_id;

//$oGrid->display_labels = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "searchgrp-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("search_group_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 
                        
$cm->oPage->addContent($oGrid);
?>
