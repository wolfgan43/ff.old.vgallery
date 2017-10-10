<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_SELECTION_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGallerySelectionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("vgallery_selection_modify");
$oRecord->src_table = "vgallery_fields_selection";
$oRecord->display_required_note = FALSE;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_modify_name");
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "field-detail";
//$oDetail->title = ffTemplate::_get_word_by_code("vgallery_selection_modify_fields");
$oDetail->src_table = "vgallery_fields_selection_value";
$oDetail->order_default = "order";
$oDetail->fields_relationship = array ("ID_selection" => "ID");
$oDetail->display_new = true;
$oDetail->display_delete = true;
//$oDetail->tab = true;
//$oDetail->tab_label = "name";
$oDetail->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oDetail
            , array(
                "resource_id" =>  "vgallery_selection"
                , "service_path" => $cm->oPage->site_path . VG_SITE_SERVICES . "/sort"
            )
            , "ID"
        )
    );

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_selection_modify_fields_value");
$oField->required = true;
$oDetail->addContent($oField);   

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
?>
