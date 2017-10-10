<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_USERS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "UserModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("user_account");
$oRecord->src_table = "anagraph";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oRecord->addContent($oField);


$cm->oPage->addContent($oRecord);

?>
