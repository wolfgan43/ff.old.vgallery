<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->title = ffTemplate::_get_word_by_code("search_config");
$oGrid->id = "SearchConfig";
$oGrid->source_SQL = "SELECT * FROM module_search [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "SearchConfigModify";
$oGrid->resources[] = $oGrid->record_id;
//$oGrid->display_labels = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "searchcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("search_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_search/[SearchConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview_search");
$oButton->template_file = "ffButton_link_image.html";                           
$oGrid->addGridButton($oButton);
                       
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_config"))); 


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->title = ffTemplate::_get_word_by_code("search_group");
$oGrid->id = "SearchConfigGroup";
$oGrid->source_SQL = "SELECT * FROM module_search_fields_group [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url =  $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify";
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
                        
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("search_group"))); 
?>
