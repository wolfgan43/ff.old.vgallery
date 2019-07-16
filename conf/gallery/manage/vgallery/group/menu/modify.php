<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_VGALLERY_GROUP_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryGroupMenuModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("vgallery_group_menu_modify");
$oRecord->src_table = "vgallery_groups_menu";
$oRecord->display_required_note = FALSE;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_menu_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_type";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_menu_modify_limit_type");
$oField->extended_type = "Selection";
$oField->base_type = "Text";
$oField->source_SQL = "SELECT ID, name FROM vgallery_type WHERE " . (OLD_VGALLERY ? "vgallery_type.name <> 'System'" : "1") . " ORDER BY name";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField);

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);

?>
