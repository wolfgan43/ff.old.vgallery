<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_HTMLTAG_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(isset($_REQUEST["keys"]["ID"]))
	$title = ffTemplate::_get_word_by_code("vgallery_htmltag_modify");
else
	$title = ffTemplate::_get_word_by_code("vgallery_htmltag_addnew");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryHtmlTagModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "vgallery_fields_htmltag";
$oRecord->display_required_note = FALSE;

$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-menu">' . cm_getClassByFrameworkCss("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $title . '</h1>';


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tag";
$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_modify_tag");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "attr";
$oField->label = ffTemplate::_get_word_by_code("vgallery_htmltag_modify_attr");
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

?>
