<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_GROUP_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "vgalleryGroup";
$oGrid->title = ffTemplate::_get_word_by_code("vgallery_group");
$oGrid->source_SQL = "SELECT 
							vgallery_groups.ID
                            , vgallery_groups.name
                        FROM 
                            vgallery_groups
                        [WHERE]
                        [HAVING]
                        [ORDER] ";

$oGrid->order_default = "ID_menu, sort, name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "VGalleryGroupModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_VGALLERY_GROUP_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_VGALLERY_GROUP_SHOW_MODIFY;
$oGrid->display_new = AREA_VGALLERY_GROUP_SHOW_MODIFY;

// Chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Visualizzazione
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_groups_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
?>