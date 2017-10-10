<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
//$oGrid->full_ajax = true;
$oGrid->title = ffTemplate::_get_word_by_code("calendar_config");
$oGrid->id = "CalendarConfig";
$oGrid->source_SQL = "SELECT * FROM module_calendar [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "CalendarConfigModify";
$oGrid->resources[] = $oGrid->record_id;

$oField = ffField::factory($cm->oPage);
$oField->id = "calendarcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("calendar_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = "https://www.google.com/calendar";
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview");
$oButton->template_file = "ffButton_link_image.html";                           
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);
?>