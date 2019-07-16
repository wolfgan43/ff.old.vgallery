<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_EMAIL_ADDRESS_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "EmailAddress";
$oGrid->title = ffTemplate::_get_word_by_code("email_address");
$oGrid->source_SQL = "SELECT
                            email_address.*
                        FROM
                            email_address
                        [WHERE]
                        [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "EmailAddressModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "email-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("email_address_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("email_address_email");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "uid";
$oField->label = ffTemplate::_get_word_by_code("email_address_uid");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, username FROM " . CM_TABLE_PREFIX . "mod_security_users";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>
