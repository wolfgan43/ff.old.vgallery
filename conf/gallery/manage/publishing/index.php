<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_PUBLISHING_SHOW_ADDNEW || AREA_PUBLISHING_SHOW_MODIFY || AREA_PUBLISHING_SHOW_DELETE || AREA_PUBLISHING_SHOW_DETAIL || AREA_PUBLISHING_SHOW_PREVIEW)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
} 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "publishing";
$oGrid->title = ffTemplate::_get_word_by_code("publishing");
$oGrid->source_SQL = "SELECT
                            publishing.*
                        FROM
                            publishing
                        [WHERE]
                        [HAVING]	
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PublishingModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addit_insert_record_param = "extype=publishing&";
$oGrid->addit_record_param = "extype=publishing&";
$oGrid->display_new = AREA_PUBLISHING_SHOW_ADDNEW;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_PUBLISHING_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_PUBLISHING_SHOW_DELETE;

/*if($permission["drafts"] < 2) {
    $oGrid->display_new = false;
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = false;
}*/
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca


// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("publishing_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("publishing_area");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oGrid->addContent($oField);


if(AREA_PROPERTIES_SHOW_MODIFY) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "properties"; 
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/modify?path=/[publishing_name_VALUE]&" . $oGrid->addit_record_param . "ret_url=" . urlencode($cm->oPage->getRequestUri());
    $oButton->aspect = "link";
	//$oButton->image = "layout_setting.png";
	$oButton->label = ffTemplate::_get_word_by_code("layout_setting");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

if(AREA_PUBLISHING_SHOW_DETAIL) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "addnew";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/detail/[publishing_name_VALUE]?[KEYS][GLOBALS]ret_url=" . urlencode($cm->oPage->getRequestUri());
    $oButton->aspect = "link";
	//$oButton->image = "add.png";
	$oButton->label = ffTemplate::_get_word_by_code("publishing_add");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

if(AREA_PUBLISHING_SHOW_PREVIEW) {
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "preview";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview/[publishing_name_VALUE]?[KEYS][GLOBALS]ret_url=" . urlencode($cm->oPage->getRequestUri());
    $oButton->aspect = "link";
	//$oButton->image = "preview.png";
	$oButton->label = ffTemplate::_get_word_by_code("preview");
	$oButton->template_file = "ffButton_link_fixed.html";                           
    $oGrid->addGridButton($oButton);
}

$cm->oPage->addContent($oGrid);

?>
