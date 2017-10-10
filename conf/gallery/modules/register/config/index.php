<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "RegisterConfig";
$oGrid->source_SQL = "SELECT * FROM module_register [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "RegisterConfigModify";
$oGrid->resources[] = $oGrid->record_id;
//$oGrid->display_labels = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "registercnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("register_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_register/[RegisterConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview_register");
$oButton->template_file = "ffButton_link_image.html";                           
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview-email";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_email/[RegisterConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview_mail");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);
?>